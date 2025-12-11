<?php
/**
 * Le Hiboo AI Assistant - Events API
 *
 * API REST pour recherche d'activites
 * Utilisee par le backend Node.js (Petit Boo) pour les tools IA
 *
 * DOCUMENTATION POUR L'IA:
 * =======================
 *
 * POST /wp-json/lehiboo/v1/events/search
 *
 * PARAMETRES DE RECHERCHE:
 * - city (string): Ville - cherche dans l'adresse complete
 * - radius (int): Rayon en km autour des coordonnees (necessite lat/lng)
 * - lat/lng (float): Coordonnees GPS de l'utilisateur pour calcul distance
 * - category (string): Slug de categorie (sport, culture, gastronomie, nature, detente)
 * - thematique (string): Slug de thematique LeHiboo
 * - tags (array): Mots-cles a chercher
 * - maxPrice (float): Prix maximum strict
 * - freeOnly (bool): Uniquement gratuit
 * - startDate/endDate (string): Plage de dates YYYY-MM-DD
 * - indoor/outdoor (bool): Interieur/Exterieur
 * - familyFriendly (bool): Adapte aux familles
 * - limit (int): Nombre de resultats (defaut 20)
 * - sortBy (string): relevance|price|date|distance|rating
 *
 * REPONSE:
 * {
 *   success: true,
 *   events: [...],
 *   totalFound: X,
 *   query: { filtres appliques }
 * }
 *
 * CHAMPS D'UN EVENEMENT:
 * - id, title, description, excerpt
 * - price (float), priceDisplay (string "Gratuit" ou "XX€")
 * - location: { city, address, venue, lat, lng, distance_km }
 * - dates: { start, end, display, duration }
 * - category: { slug, name }
 * - thematiques: [{ slug, name }]
 * - tags: [string]
 * - imageUrl, thumbnailUrl
 * - url (lien vers la fiche)
 * - availability: { status, spotsRemaining }
 * - organizer: { name, verified }
 * - rating: { average, count } ou null
 * - restrictions: { ageMin, familyFriendly }
 * - environment: { indoor, outdoor }
 *
 * @package LehibooAIAssistant
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_Events_API {

    const NAMESPACE = 'lehiboo/v1';
    const META_PREFIX = 'ova_mb_event_';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route(self::NAMESPACE, '/events/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'search_events'),
            'permission_callback' => array($this, 'check_api_key'),
            'args' => $this->get_search_params_schema()
        ));

        register_rest_route(self::NAMESPACE, '/events/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => array($this, 'check_api_key'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer'
                )
            )
        ));

        // Endpoint pour lister les categories/thematiques (utile pour l'IA)
        register_rest_route(self::NAMESPACE, '/events/filters', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_filters'),
            'permission_callback' => array($this, 'check_api_key')
        ));
    }

    private function get_search_params_schema() {
        return array(
            'keyword' => array('type' => 'string', 'description' => 'Recherche dans titre et contenu (Hybrid Search)'),
            'city' => array('type' => 'string', 'description' => 'Ville de recherche'),
            'anyLocation' => array('type' => 'boolean', 'default' => false, 'description' => 'Ignorer le filtre de localisation (partout)'),
            'radius' => array('type' => 'integer', 'default' => 30, 'description' => 'Rayon en km'),
            'lat' => array('type' => 'number', 'description' => 'Latitude utilisateur'),
            'lng' => array('type' => 'number', 'description' => 'Longitude utilisateur'),
            'category' => array('type' => 'string', 'description' => 'Slug categorie'),
            'thematique' => array('type' => 'string', 'description' => 'Slug thematique'),
            'tags' => array('type' => 'array', 'items' => array('type' => 'string')),
            'maxPrice' => array('type' => 'number'),
            'freeOnly' => array('type' => 'boolean', 'default' => false),
            'startDate' => array('type' => 'string', 'format' => 'date'),
            'endDate' => array('type' => 'string', 'format' => 'date'),
            'indoor' => array('type' => 'boolean'),
            'outdoor' => array('type' => 'boolean'),
            'familyFriendly' => array('type' => 'boolean'),
            'limit' => array('type' => 'integer', 'default' => 20),
            'sortBy' => array('type' => 'string', 'enum' => array('relevance', 'price', 'date', 'distance', 'rating'), 'default' => 'relevance'),
            'shuffle' => array('type' => 'boolean', 'default' => true, 'description' => 'Melanger les resultats a score egal')
        );
    }

    public function check_api_key($request) {
        $auth_header = $request->get_header('authorization');

        if (empty($auth_header)) {
            return new WP_Error('no_auth', 'Authorization header missing', array('status' => 401));
        }

        $parts = explode(' ', $auth_header);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return new WP_Error('invalid_auth', 'Invalid authorization format', array('status' => 401));
        }

        $provided_key = $parts[1];
        $stored_key = get_option('lehiboo_ai_api_key');

        if (empty($stored_key)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                return true;
            }
            return new WP_Error('no_key_configured', 'API key not configured', array('status' => 500));
        }

        if ($provided_key !== $stored_key) {
            return new WP_Error('invalid_key', 'Invalid API key', array('status' => 403));
        }

        return true;
    }

    /**
     * Recherche d'evenements
     */
    public function search_events($request) {
        $params = $request->get_json_params();

        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => isset($params['limit']) ? min(intval($params['limit']), 50) : 20,
            'meta_query' => array('relation' => 'AND'),
            'tax_query' => array('relation' => 'AND')
        );

        // === HYBRID SEARCH: Recherche par mot-cle dans titre et contenu ===
        if (!empty($params['keyword'])) {
            $args['s'] = sanitize_text_field($params['keyword']);
        }

        // === FILTRE PAR VILLE/ADRESSE (sauf si anyLocation=true) ===
        $any_location = !empty($params['anyLocation']) && $params['anyLocation'];
        if (!$any_location && !empty($params['city'])) {
            $city = sanitize_text_field($params['city']);
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array('key' => self::META_PREFIX . 'map_address', 'value' => $city, 'compare' => 'LIKE'),
                array('key' => self::META_PREFIX . 'address', 'value' => $city, 'compare' => 'LIKE'),
                array('key' => self::META_PREFIX . 'map_name', 'value' => $city, 'compare' => 'LIKE')
            );
        }

        // === FILTRE PAR PRIX ===
        if (!empty($params['freeOnly']) && $params['freeOnly']) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array('key' => self::META_PREFIX . 'min_price', 'value' => 0, 'type' => 'NUMERIC', 'compare' => '='),
                array('key' => self::META_PREFIX . 'min_price', 'compare' => 'NOT EXISTS')
            );
        } elseif (isset($params['maxPrice'])) {
            $args['meta_query'][] = array(
                'key' => self::META_PREFIX . 'min_price',
                'value' => floatval($params['maxPrice']),
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }

        // === FILTRE PAR DATES ===
        if (!empty($params['startDate'])) {
            $start_ts = strtotime($params['startDate']);
            $args['meta_query'][] = array(
                'key' => self::META_PREFIX . 'end_date_str',
                'value' => $start_ts,
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        }

        if (!empty($params['endDate'])) {
            $end_ts = strtotime($params['endDate'] . ' 23:59:59');
            $args['meta_query'][] = array(
                'key' => self::META_PREFIX . 'start_date_str',
                'value' => $end_ts,
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }

        // === FILTRE PAR CATEGORIE ===
        if (!empty($params['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'event_cat',
                'field' => 'slug',
                'terms' => sanitize_title($params['category'])
            );
        }

        // === FILTRE PAR THEMATIQUE (LeHiboo) ===
        if (!empty($params['thematique'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'event_thematique',
                'field' => 'slug',
                'terms' => sanitize_title($params['thematique'])
            );
        }

        // === FILTRE PAR TAGS ===
        if (!empty($params['tags']) && is_array($params['tags'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'event_tag',
                'field' => 'name',
                'terms' => array_map('sanitize_text_field', $params['tags']),
                'operator' => 'IN'
            );
        }

        // === TRI ===
        switch ($params['sortBy'] ?? 'relevance') {
            case 'price':
                $args['meta_key'] = self::META_PREFIX . 'min_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'date':
                $args['meta_key'] = self::META_PREFIX . 'start_date_str';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        // Executer la query
        $query = new WP_Query($args);
        $events = array();
        $user_lat = isset($params['lat']) ? floatval($params['lat']) : null;
        $user_lng = isset($params['lng']) ? floatval($params['lng']) : null;

        foreach ($query->posts as $post) {
            $event = $this->format_event($post, $user_lat, $user_lng);
            if ($event) {
                $events[] = $event;
            }
        }

        // Filtrage par rayon si coordonnees fournies
        if ($user_lat && $user_lng && !empty($params['radius'])) {
            $radius = intval($params['radius']);
            $events = array_filter($events, function($e) use ($radius) {
                return !isset($e['location']['distance_km']) || $e['location']['distance_km'] <= $radius;
            });
            $events = array_values($events);

            // Tri par distance si demande
            if (($params['sortBy'] ?? '') === 'distance') {
                usort($events, function($a, $b) {
                    $da = $a['location']['distance_km'] ?? 9999;
                    $db = $b['location']['distance_km'] ?? 9999;
                    return $da <=> $db;
                });
            }
        }

        // === SHUFFLE: Melanger les resultats pour plus de diversite ===
        // Par defaut active (shuffle=true), desactivable si shuffle=false
        $should_shuffle = !isset($params['shuffle']) || $params['shuffle'] !== false;
        if ($should_shuffle && count($events) > 1) {
            shuffle($events);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'events' => $events,
            'totalFound' => count($events),
            'query' => array(
                'keyword' => $params['keyword'] ?? null,
                'city' => $any_location ? null : ($params['city'] ?? null),
                'anyLocation' => $any_location,
                'category' => $params['category'] ?? null,
                'thematique' => $params['thematique'] ?? null,
                'maxPrice' => $params['maxPrice'] ?? null,
                'freeOnly' => $params['freeOnly'] ?? false,
                'dateRange' => array(
                    'start' => $params['startDate'] ?? null,
                    'end' => $params['endDate'] ?? null
                ),
                'shuffled' => $should_shuffle
            )
        ), 200);
    }

    /**
     * Detail d'un evenement
     */
    public function get_event($request) {
        $event_id = $request->get_param('id');
        $post = get_post($event_id);

        if (!$post || $post->post_type !== 'event') {
            return new WP_Error('not_found', 'Event not found', array('status' => 404));
        }

        return new WP_REST_Response(array(
            'success' => true,
            'event' => $this->format_event_detail($post)
        ), 200);
    }

    /**
     * Liste des filtres disponibles (pour l'IA)
     */
    public function get_filters($request) {
        $categories = get_terms(array('taxonomy' => 'event_cat', 'hide_empty' => true));
        $thematiques = get_terms(array('taxonomy' => 'event_thematique', 'hide_empty' => true));

        return new WP_REST_Response(array(
            'success' => true,
            'categories' => array_map(function($t) {
                return array('slug' => $t->slug, 'name' => $t->name, 'count' => $t->count);
            }, is_array($categories) ? $categories : array()),
            'thematiques' => array_map(function($t) {
                return array('slug' => $t->slug, 'name' => $t->name, 'count' => $t->count);
            }, is_array($thematiques) ? $thematiques : array()),
            'cities' => $this->get_available_cities()
        ), 200);
    }

    /**
     * Recuperer les villes disponibles
     */
    private function get_available_cities() {
        global $wpdb;

        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
             WHERE meta_key = %s AND meta_value != ''
             ORDER BY meta_value",
            self::META_PREFIX . 'map_name'
        ));

        return array_filter(array_unique($results));
    }

    /**
     * Formater un evenement pour la liste
     */
    private function format_event($post, $user_lat = null, $user_lng = null) {
        $id = $post->ID;

        // Location
        $map_address = get_post_meta($id, self::META_PREFIX . 'map_address', true);
        $address = get_post_meta($id, self::META_PREFIX . 'address', true);
        $map_name = get_post_meta($id, self::META_PREFIX . 'map_name', true);
        $lat = floatval(get_post_meta($id, self::META_PREFIX . 'map_lat', true));
        $lng = floatval(get_post_meta($id, self::META_PREFIX . 'map_lng', true));

        // Extraire la ville
        $city = $map_name;
        if (!$city && $map_address) {
            $parts = explode(',', $map_address);
            $city = trim($parts[0]);
        }

        // Calcul distance
        $distance_km = null;
        if ($user_lat && $user_lng && $lat && $lng) {
            $distance_km = $this->calculate_distance($user_lat, $user_lng, $lat, $lng);
        }

        // Prix
        $price = floatval(get_post_meta($id, self::META_PREFIX . 'min_price', true));
        $price_display = $price == 0 ? 'Gratuit' : sprintf('%d€', $price);

        // Dates
        $start_ts = intval(get_post_meta($id, self::META_PREFIX . 'start_date_str', true));
        $end_ts = intval(get_post_meta($id, self::META_PREFIX . 'end_date_str', true));
        $start_date = $start_ts ? date('Y-m-d', $start_ts) : '';
        $end_date = $end_ts ? date('Y-m-d', $end_ts) : $start_date;

        // Duree
        $duration = null;
        if ($start_ts && $end_ts && $end_ts > $start_ts) {
            $hours = round(($end_ts - $start_ts) / 3600, 1);
            $duration = $hours . 'h';
        }

        // Categorie
        $categories = wp_get_post_terms($id, 'event_cat', array('fields' => 'all'));
        $category = null;
        if (!is_wp_error($categories) && !empty($categories)) {
            $cat = $categories[0];
            $category = array('slug' => $cat->slug, 'name' => $cat->name);
        }

        // Thematiques
        $thematiques_terms = wp_get_post_terms($id, 'event_thematique', array('fields' => 'all'));
        $thematiques = array();
        if (!is_wp_error($thematiques_terms)) {
            foreach ($thematiques_terms as $t) {
                $thematiques[] = array('slug' => $t->slug, 'name' => $t->name);
            }
        }

        // Tags
        $tags_terms = wp_get_post_terms($id, 'event_tag', array('fields' => 'names'));
        $tags = is_wp_error($tags_terms) ? array() : $tags_terms;

        // Image
        $image_url = get_the_post_thumbnail_url($id, 'large');
        $thumbnail_url = get_the_post_thumbnail_url($id, 'medium');

        // Venue
        $venues = get_post_meta($id, self::META_PREFIX . 'venue', true);
        $venue_name = '';
        if (is_array($venues) && !empty($venues)) {
            $venue_name = $venues[0];
        } elseif (is_string($venues)) {
            $venue_name = $venues;
        }

        return array(
            'id' => (string)$id,
            'title' => $post->post_title,
            'excerpt' => wp_trim_words(strip_tags($post->post_content), 25),
            'price' => $price,
            'priceDisplay' => $price_display,
            'location' => array(
                'city' => $city,
                'address' => $address ?: $map_address,
                'venue' => $venue_name,
                'lat' => $lat ?: null,
                'lng' => $lng ?: null,
                'distance_km' => $distance_km
            ),
            'dates' => array(
                'start' => $start_date,
                'end' => $end_date,
                'display' => $this->format_date_display($start_date),
                'duration' => $duration
            ),
            'category' => $category,
            'thematiques' => $thematiques,
            'tags' => $tags,
            'imageUrl' => $image_url ?: null,
            'thumbnailUrl' => $thumbnail_url ?: null,
            'url' => get_permalink($id)
        );
    }

    /**
     * Formater un evenement detail complet
     */
    private function format_event_detail($post) {
        $event = $this->format_event($post);

        $id = $post->ID;

        // Description complete
        $event['description'] = apply_filters('the_content', $post->post_content);

        // Galerie
        $gallery_ids = get_post_meta($id, self::META_PREFIX . 'gallery', true);
        $event['gallery'] = array();
        if (is_array($gallery_ids)) {
            foreach ($gallery_ids as $img_id) {
                $url = wp_get_attachment_image_url($img_id, 'large');
                if ($url) $event['gallery'][] = $url;
            }
        }

        // Restrictions
        $event['restrictions'] = array(
            'ageMin' => intval(get_post_meta($id, self::META_PREFIX . 'min_age', true)) ?: null,
            'ageMax' => intval(get_post_meta($id, self::META_PREFIX . 'max_age', true)) ?: null,
            'familyFriendly' => (bool)get_post_meta($id, self::META_PREFIX . 'family_friendly', true)
        );

        // Environment
        $event['environment'] = array(
            'indoor' => (bool)get_post_meta($id, self::META_PREFIX . 'indoor', true),
            'outdoor' => (bool)get_post_meta($id, self::META_PREFIX . 'outdoor', true)
        );

        // Organisateur
        $author = get_user_by('ID', $post->post_author);
        $event['organizer'] = $author ? array(
            'name' => $author->display_name,
            'verified' => (bool)get_user_meta($author->ID, 'verified', true)
        ) : null;

        return $event;
    }

    /**
     * Formater une date pour affichage
     */
    private function format_date_display($date) {
        if (!$date) return '';
        $ts = strtotime($date);
        return date_i18n('D j M Y', $ts);
    }

    /**
     * Calcul distance Haversine
     */
    private function calculate_distance($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6371;
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $lng1 = deg2rad($lng1);
        $lng2 = deg2rad($lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlng/2) * sin($dlng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($earth_radius * $c, 1);
    }
}

new Lehiboo_Events_API();
