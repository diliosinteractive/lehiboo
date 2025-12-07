<?php
/**
 * EventList REST API
 * Endpoint pour permettre au backend IA d'accéder aux événements
 *
 * @package EventList
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EventList_REST_API {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Route pour lister les événements
        register_rest_route('eventlist/v1', '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true',
            'args' => $this->get_events_args(),
        ));

        // Route pour un événement spécifique
        register_rest_route('eventlist/v1', '/events/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'Event ID',
                ),
            ),
        ));

        // Route pour les catégories
        register_rest_route('eventlist/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true',
        ));

        // Route pour les configurations de l'application mobile
        register_rest_route('eventlist/v1', '/mobile-app/config', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_mobile_app_config'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Get events list with filters
     */
    public function get_events($request) {
        $params = $request->get_params();

        // Construire les arguments de query
        $args = array(
            'post_type' => 'event', // Adapter selon votre custom post type
            'post_status' => 'publish',
            'posts_per_page' => isset($params['per_page']) ? intval($params['per_page']) : 10,
            'paged' => isset($params['page']) ? intval($params['page']) : 1,
            'orderby' => 'meta_value',
            'meta_key' => 'event_start_date',
            'order' => 'ASC',
        );

        // Filtre par catégorie
        if (!empty($params['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_category', // Adapter selon votre taxonomie
                    'field' => 'slug',
                    'terms' => sanitize_text_field($params['category']),
                ),
            );
        }

        // Filtre par date de début
        $meta_query = array('relation' => 'AND');

        if (!empty($params['start_date'])) {
            $meta_query[] = array(
                'key' => 'event_start_date',
                'value' => sanitize_text_field($params['start_date']),
                'compare' => '>=',
                'type' => 'DATE',
            );
        }

        // Filtre par date de fin
        if (!empty($params['end_date'])) {
            $meta_query[] = array(
                'key' => 'event_start_date',
                'value' => sanitize_text_field($params['end_date']),
                'compare' => '<=',
                'type' => 'DATE',
            );
        }

        // Filtre par localisation
        if (!empty($params['location'])) {
            $meta_query[] = array(
                'key' => 'event_location',
                'value' => sanitize_text_field($params['location']),
                'compare' => 'LIKE',
            );
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        // Exécuter la query
        $query = new WP_Query($args);

        // Formater les résultats
        $events = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $events[] = $this->format_event(get_post());
            }
            wp_reset_postdata();
        }

        // Retourner la réponse
        return rest_ensure_response(array(
            'events' => $events,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ));
    }

    /**
     * Get single event
     */
    public function get_event($request) {
        $id = $request->get_param('id');
        $post = get_post($id);

        if (!$post || $post->post_type !== 'event') {
            return new WP_Error(
                'event_not_found',
                'Event not found',
                array('status' => 404)
            );
        }

        return rest_ensure_response($this->format_event($post));
    }

    /**
     * Get mobile app configuration
     */
    public function get_mobile_app_config($request) {
        // Récupérer les options depuis la base de données
        $options = get_option('ova_eventlist', array());
        $mobile_app_options = isset($options['mobile_app']) ? $options['mobile_app'] : array();

        // Image hero
        $hero_image_id = isset($mobile_app_options['hero_image']) ? $mobile_app_options['hero_image'] : '';
        $hero_image_url = $hero_image_id ? wp_get_attachment_url($hero_image_id) : '';

        // Bannières publicitaires
        $ads_banner_1_id = isset($mobile_app_options['ads_banner_1']) ? $mobile_app_options['ads_banner_1'] : '';
        $ads_banner_2_id = isset($mobile_app_options['ads_banner_2']) ? $mobile_app_options['ads_banner_2'] : '';
        $ads_banner_3_id = isset($mobile_app_options['ads_banner_3']) ? $mobile_app_options['ads_banner_3'] : '';

        $banners = array();

        if ($ads_banner_1_id) {
            $banners[] = array(
                'image' => wp_get_attachment_url($ads_banner_1_id),
                'url' => isset($mobile_app_options['ads_banner_1_url']) ? $mobile_app_options['ads_banner_1_url'] : '',
            );
        }
        if ($ads_banner_2_id) {
            $banners[] = array(
                'image' => wp_get_attachment_url($ads_banner_2_id),
                'url' => isset($mobile_app_options['ads_banner_2_url']) ? $mobile_app_options['ads_banner_2_url'] : '',
            );
        }
        if ($ads_banner_3_id) {
            $banners[] = array(
                'image' => wp_get_attachment_url($ads_banner_3_id),
                'url' => isset($mobile_app_options['ads_banner_3_url']) ? $mobile_app_options['ads_banner_3_url'] : '',
            );
        }

        $config = array(
            'hero' => array(
                'image' => $hero_image_url,
                'title' => isset($mobile_app_options['hero_title']) ? $mobile_app_options['hero_title'] : 'Trouvez votre prochaine aventure locale',
                'subtitle' => isset($mobile_app_options['hero_subtitle']) ? $mobile_app_options['hero_subtitle'] : 'Découvrez les meilleurs événements près de chez vous',
                'show_search' => isset($mobile_app_options['hero_show_search']) && !empty($mobile_app_options['hero_show_search']),
                'show_quick_filters' => isset($mobile_app_options['hero_show_quick_filters']) && !empty($mobile_app_options['hero_show_quick_filters']),
            ),
            'ads' => array(
                'enabled' => isset($mobile_app_options['ads_enabled']) && !empty($mobile_app_options['ads_enabled']),
                'banners' => $banners,
            ),
            'texts' => array(
                'events_section_title' => isset($mobile_app_options['events_section_title']) ? $mobile_app_options['events_section_title'] : 'Retrouvez tous vos événements',
                'events_section_description' => isset($mobile_app_options['events_section_description']) ? $mobile_app_options['events_section_description'] : 'Explorez notre sélection d\'événements locaux',
                'thematiques_section_title' => isset($mobile_app_options['thematiques_section_title']) ? $mobile_app_options['thematiques_section_title'] : 'Explorez par thématique',
                'cities_section_title' => isset($mobile_app_options['cities_section_title']) ? $mobile_app_options['cities_section_title'] : 'Événements par ville',
                'explore_button_text' => isset($mobile_app_options['explore_button_text']) ? $mobile_app_options['explore_button_text'] : 'Explorer les activités',
            ),
        );

        return rest_ensure_response($config);
    }

    /**
     * Get event categories
     */
    public function get_categories($request) {
        $categories = get_terms(array(
            'taxonomy' => 'event_category', // Adapter selon votre taxonomie
            'hide_empty' => true,
        ));

        if (is_wp_error($categories)) {
            return rest_ensure_response(array());
        }

        $formatted = array_map(function($cat) {
            return array(
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'count' => $cat->count,
            );
        }, $categories);

        return rest_ensure_response($formatted);
    }

    /**
     * Format event for API response
     */
    private function format_event($post) {
        $event_id = $post->ID;

        // Récupérer les meta fields (adapter selon vos champs)
        $start_date = get_post_meta($event_id, 'event_start_date', true);
        $end_date = get_post_meta($event_id, 'event_end_date', true);
        $start_time = get_post_meta($event_id, 'event_start_time', true);
        $end_time = get_post_meta($event_id, 'event_end_time', true);
        $location = get_post_meta($event_id, 'event_location', true);
        $venue = get_post_meta($event_id, 'event_venue', true);
        $price = get_post_meta($event_id, 'event_price', true);
        $age_restriction = get_post_meta($event_id, 'event_age_restriction', true);
        $min_age = get_post_meta($event_id, 'event_min_age', true);
        $max_age = get_post_meta($event_id, 'event_max_age', true);
        $indoor = get_post_meta($event_id, 'event_indoor', true);
        $outdoor = get_post_meta($event_id, 'event_outdoor', true);
        $family_friendly = get_post_meta($event_id, 'event_family_friendly', true);
        $rating = get_post_meta($event_id, 'event_rating', true);
        $reviews_count = get_post_meta($event_id, 'event_reviews_count', true);
        $availability = get_post_meta($event_id, 'event_availability', true);
        $spots_remaining = get_post_meta($event_id, 'event_spots_remaining', true);

        // Catégories
        $categories = wp_get_post_terms($event_id, 'event_category');
        $category = !empty($categories) ? $categories[0]->name : null;

        // Image à la une
        $thumbnail_id = get_post_thumbnail_id($event_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : null;

        return array(
            'id' => $event_id,
            'title' => array(
                'rendered' => get_the_title($event_id),
            ),
            'content' => array(
                'rendered' => apply_filters('the_content', $post->post_content),
            ),
            'excerpt' => array(
                'rendered' => get_the_excerpt($post),
            ),
            'featured_image_url' => $thumbnail_url,
            'link' => get_permalink($event_id),

            // Dates et horaires
            'start_date' => $start_date,
            'end_date' => $end_date,
            'start_time' => $start_time,
            'end_time' => $end_time,

            // Localisation
            'location' => $location,
            'venue' => $venue,

            // Prix et disponibilité
            'price' => $price,
            'availability' => $availability ?: 'available',
            'spots_remaining' => intval($spots_remaining),

            // Restrictions
            'age_restriction' => $age_restriction,
            'min_age' => intval($min_age),
            'max_age' => intval($max_age),

            // Caractéristiques
            'indoor' => (bool) $indoor,
            'outdoor' => (bool) $outdoor,
            'family_friendly' => (bool) $family_friendly,

            // Catégorie
            'category' => $category,
            'categories' => array_map(function($cat) {
                return array(
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                );
            }, $categories),

            // Avis
            'rating' => $rating ? floatval($rating) : null,
            'reviews_count' => intval($reviews_count),

            // Meta
            'published' => get_the_date('Y-m-d H:i:s', $event_id),
            'modified' => get_the_modified_date('Y-m-d H:i:s', $event_id),
        );
    }

    /**
     * Get arguments for events endpoint
     */
    private function get_events_args() {
        return array(
            'category' => array(
                'type' => 'string',
                'description' => 'Filter by category slug (sport, culture, gastronomie, etc.)',
                'required' => false,
            ),
            'start_date' => array(
                'type' => 'string',
                'description' => 'Filter by start date (YYYY-MM-DD)',
                'required' => false,
                'format' => 'date',
            ),
            'end_date' => array(
                'type' => 'string',
                'description' => 'Filter by end date (YYYY-MM-DD)',
                'required' => false,
                'format' => 'date',
            ),
            'location' => array(
                'type' => 'string',
                'description' => 'Filter by location (partial match)',
                'required' => false,
            ),
            'per_page' => array(
                'type' => 'integer',
                'description' => 'Number of events per page',
                'required' => false,
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100,
            ),
            'page' => array(
                'type' => 'integer',
                'description' => 'Page number',
                'required' => false,
                'default' => 1,
                'minimum' => 1,
            ),
        );
    }
}

// Initialiser l'API
new EventList_REST_API();
