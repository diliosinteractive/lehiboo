<?php
/**
 * Le Hiboo AI Assistant - Events API
 *
 * Endpoint REST API pour recherche d'événements
 * Utilisé par le backend Node.js pour les tools IA
 *
 * @package LehibooAIAssistant
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_Events_API {

    /**
     * Namespace pour l'API
     */
    const NAMESPACE = 'lehiboo/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Enregistre les routes REST API
     */
    public function register_routes() {
        // POST /wp-json/lehiboo/v1/events/search
        register_rest_route(self::NAMESPACE, '/events/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'search_events'),
            'permission_callback' => array($this, 'check_api_key'),
            'args' => $this->get_search_params_schema()
        ));

        // GET /wp-json/lehiboo/v1/events/{id}
        register_rest_route(self::NAMESPACE, '/events/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => array($this, 'check_api_key'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'ID de l\'événement'
                )
            )
        ));
    }

    /**
     * Schema des paramètres de recherche
     */
    private function get_search_params_schema() {
        return array(
            'city' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Ville de recherche'
            ),
            'radius' => array(
                'required' => false,
                'type' => 'integer',
                'default' => 20,
                'description' => 'Rayon de recherche en km'
            ),
            'startDate' => array(
                'required' => false,
                'type' => 'string',
                'format' => 'date',
                'description' => 'Date de début (YYYY-MM-DD)'
            ),
            'endDate' => array(
                'required' => false,
                'type' => 'string',
                'format' => 'date',
                'description' => 'Date de fin (YYYY-MM-DD)'
            ),
            'maxPrice' => array(
                'required' => false,
                'type' => 'number',
                'description' => 'Prix maximum (STRICT)'
            ),
            'minPrice' => array(
                'required' => false,
                'type' => 'number',
                'description' => 'Prix minimum'
            ),
            'category' => array(
                'required' => false,
                'type' => 'string',
                'enum' => array('sport', 'culture', 'gastronomie', 'nature', 'detente'),
                'description' => 'Catégorie d\'activité'
            ),
            'minAge' => array(
                'required' => false,
                'type' => 'integer',
                'description' => 'Âge minimum de l\'utilisateur (pour filtrage restrictions)'
            ),
            'indoor' => array(
                'required' => false,
                'type' => 'boolean',
                'description' => 'Filtrer activités indoor'
            ),
            'tags' => array(
                'required' => false,
                'type' => 'array',
                'items' => array('type' => 'string'),
                'description' => 'Tags pour affiner'
            ),
            'limit' => array(
                'required' => false,
                'type' => 'integer',
                'default' => 5,
                'description' => 'Nombre de résultats'
            ),
            'sortBy' => array(
                'required' => false,
                'type' => 'string',
                'enum' => array('relevance', 'price', 'rating', 'distance'),
                'default' => 'relevance',
                'description' => 'Tri des résultats'
            )
        );
    }

    /**
     * Vérifie la clé API
     */
    public function check_api_key($request) {
        $auth_header = $request->get_header('authorization');

        if (empty($auth_header)) {
            return new WP_Error('no_auth', 'Authorization header missing', array('status' => 401));
        }

        // Format attendu: "Bearer sk-xxxxx"
        $parts = explode(' ', $auth_header);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return new WP_Error('invalid_auth', 'Invalid authorization format', array('status' => 401));
        }

        $provided_key = $parts[1];
        $stored_key = get_option('lehiboo_ai_api_key');

        if (empty($stored_key)) {
            // Pas de clé configurée = accès autorisé en dev
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
     * Endpoint principal: Recherche d'événements
     */
    public function search_events($request) {
        $params = $request->get_json_params();

        // Construire la query WordPress
        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => isset($params['limit']) ? intval($params['limit']) : 5,
            'meta_query' => array('relation' => 'AND'),
            'tax_query' => array('relation' => 'AND')
        );

        // Filtre par ville
        if (!empty($params['city'])) {
            $args['meta_query'][] = array(
                'key' => 'event_city',
                'value' => $params['city'],
                'compare' => 'LIKE'
            );
        }

        // Filtre par prix MAX (STRICT!)
        if (isset($params['maxPrice'])) {
            $args['meta_query'][] = array(
                'key' => 'event_price',
                'value' => floatval($params['maxPrice']),
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }

        // Filtre par prix MIN
        if (isset($params['minPrice'])) {
            $args['meta_query'][] = array(
                'key' => 'event_price',
                'value' => floatval($params['minPrice']),
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        }

        // Filtre par dates (événements disponibles entre startDate et endDate)
        if (!empty($params['startDate'])) {
            $args['meta_query'][] = array(
                'key' => 'event_end_date',
                'value' => $params['startDate'],
                'type' => 'DATE',
                'compare' => '>='
            );
        }

        if (!empty($params['endDate'])) {
            $args['meta_query'][] = array(
                'key' => 'event_start_date',
                'value' => $params['endDate'],
                'type' => 'DATE',
                'compare' => '<='
            );
        }

        // Filtre par catégorie
        if (!empty($params['category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'event_category',
                'field' => 'slug',
                'terms' => $params['category']
            );
        }

        // Filtre par âge minimum (restrictions d'âge)
        if (isset($params['minAge'])) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'event_min_age',
                    'compare' => 'NOT EXISTS' // Pas de restriction
                ),
                array(
                    'key' => 'event_min_age',
                    'value' => intval($params['minAge']),
                    'type' => 'NUMERIC',
                    'compare' => '<='
                )
            );
        }

        // Filtre indoor/outdoor
        if (isset($params['indoor'])) {
            $args['meta_query'][] = array(
                'key' => 'event_indoor',
                'value' => $params['indoor'] ? '1' : '0',
                'compare' => '='
            );
        }

        // Tri
        if (isset($params['sortBy'])) {
            switch ($params['sortBy']) {
                case 'price':
                    $args['meta_key'] = 'event_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                case 'rating':
                    $args['meta_key'] = 'event_rating';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                // 'relevance' et 'distance' gérés côté backend
            }
        }

        // Exécuter la query
        $query = new WP_Query($args);
        $events = array();

        foreach ($query->posts as $post) {
            $events[] = $this->format_event($post);
        }

        // Réponse
        return new WP_REST_Response(array(
            'success' => true,
            'events' => $events,
            'totalFound' => $query->found_posts,
            'query' => array(
                'city' => $params['city'] ?? null,
                'maxPrice' => $params['maxPrice'] ?? null,
                'category' => $params['category'] ?? null,
                'dateRange' => array(
                    'start' => $params['startDate'] ?? null,
                    'end' => $params['endDate'] ?? null
                )
            )
        ), 200);
    }

    /**
     * Endpoint: Récupérer un événement par ID
     */
    public function get_event($request) {
        $event_id = $request->get_param('id');
        $post = get_post($event_id);

        if (!$post || $post->post_type !== 'event') {
            return new WP_Error('not_found', 'Event not found', array('status' => 404));
        }

        return new WP_REST_Response(array(
            'success' => true,
            'event' => $this->format_event($post)
        ), 200);
    }

    /**
     * Formate un événement pour l'API
     */
    private function format_event($post) {
        $event_id = $post->ID;

        // Récupérer meta fields
        $price = floatval(get_post_meta($event_id, 'event_price', true));
        $city = get_post_meta($event_id, 'event_city', true);
        $address = get_post_meta($event_id, 'event_address', true);
        $start_date = get_post_meta($event_id, 'event_start_date', true);
        $end_date = get_post_meta($event_id, 'event_end_date', true);
        $duration = get_post_meta($event_id, 'event_duration', true);
        $rating = floatval(get_post_meta($event_id, 'event_rating', true));
        $reviews = intval(get_post_meta($event_id, 'event_reviews_count', true));
        $min_age = intval(get_post_meta($event_id, 'event_min_age', true));
        $max_age = intval(get_post_meta($event_id, 'event_max_age', true));
        $group_size_min = intval(get_post_meta($event_id, 'event_group_size_min', true));
        $group_size_max = intval(get_post_meta($event_id, 'event_group_size_max', true));
        $indoor = get_post_meta($event_id, 'event_indoor', true) === '1';

        // Catégories
        $categories = wp_get_post_terms($event_id, 'event_category', array('fields' => 'slugs'));
        $category = !empty($categories) ? $categories[0] : 'multi';

        // Tags
        $tags = wp_get_post_terms($event_id, 'event_tag', array('fields' => 'names'));

        // Image
        $image_url = get_the_post_thumbnail_url($event_id, 'large');

        // Formater l'événement
        return array(
            'id' => (string)$event_id,
            'title' => $post->post_title,
            'description' => wp_trim_words($post->post_content, 30),
            'price' => $price,
            'currency' => 'EUR',
            'location' => array(
                'city' => $city,
                'address' => $address,
                'distance' => null // Calculé côté backend si coordonnées fournies
            ),
            'dates' => array_filter(array($start_date, $end_date)),
            'duration' => $duration ?: null,
            'category' => $category,
            'tags' => $tags,
            'rating' => $rating > 0 ? $rating : null,
            'reviews' => $reviews,
            'imageUrl' => $image_url ?: null,
            'bookingUrl' => get_permalink($event_id),
            'ageRestriction' => array(
                'min' => $min_age > 0 ? $min_age : null,
                'max' => $max_age > 0 ? $max_age : null
            ),
            'groupSize' => array(
                'min' => $group_size_min > 0 ? $group_size_min : 1,
                'max' => $group_size_max > 0 ? $group_size_max : null
            ),
            'indoor' => $indoor
        );
    }
}

// Initialiser l'API
new Lehiboo_Events_API();
