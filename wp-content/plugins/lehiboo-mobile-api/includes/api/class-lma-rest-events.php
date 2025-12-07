<?php
/**
 * REST Events Controller
 * Endpoints événements
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Events {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List events
        register_rest_route($this->namespace, '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true',
        ));

        // Single event
        register_rest_route($this->namespace, '/events/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Get events list
     */
    public function get_events($request) {
        $filters = LMA_Validator::sanitize_event_filters($request);
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Build query args
        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => $filters['per_page'],
            'paged' => $filters['page'],
        );

        // Meta query
        $meta_query = array('relation' => 'AND');

        // Date filters (only future events by default, unless include_past=true)
        // Note: eventlist plugin uses 'start_date_str' for the timestamp, not 'date_start'
        $include_past = isset($filters['include_past']) && $filters['include_past'];
        $date_meta_key = $meta_prefix . 'start_date_str';

        if (!empty($filters['date_from'])) {
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => strtotime($filters['date_from']),
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        } elseif (!$include_past) {
            // Default: future events only (unless include_past is true)
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => time(),
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        }

        if (!empty($filters['date_to'])) {
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => strtotime($filters['date_to']) + 86400,
                'compare' => '<=',
                'type' => 'NUMERIC',
            );
        }

        // Price filters
        if ($filters['free_only']) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => $meta_prefix . 'price',
                    'value' => 0,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'price',
                    'compare' => 'NOT EXISTS',
                ),
            );
        } else {
            if ($filters['price_min'] > 0) {
                $meta_query[] = array(
                    'key' => $meta_prefix . 'price',
                    'value' => $filters['price_min'],
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                );
            }
            if ($filters['price_max'] > 0) {
                $meta_query[] = array(
                    'key' => $meta_prefix . 'price',
                    'value' => $filters['price_max'],
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                );
            }
        }

        // City filter
        if (!empty($filters['city'])) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'city',
                'value' => $filters['city'],
                'compare' => 'LIKE',
            );
        }

        // Indoor/Outdoor
        if ($filters['indoor'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'indoor',
                'value' => $filters['indoor'] ? '1' : '0',
            );
        }
        if ($filters['outdoor'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'outdoor',
                'value' => $filters['outdoor'] ? '1' : '0',
            );
        }

        // Family friendly
        if ($filters['family_friendly'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'family_friendly',
                'value' => '1',
            );
        }

        // Age filters
        if ($filters['age_min'] > 0) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => $meta_prefix . 'min_age',
                    'value' => $filters['age_min'],
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'min_age',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        // Tax query
        $tax_query = array('relation' => 'AND');

        if (!empty($filters['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_cat',
                'field' => is_numeric($filters['category']) ? 'term_id' : 'slug',
                'terms' => $filters['category'],
            );
        }

        if (!empty($filters['thematique'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_thematique',
                'field' => 'slug',
                'terms' => $filters['thematique'],
            );
        }

        // Location filter (event_loc taxonomy)
        if (!empty($filters['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_loc',
                'field' => is_numeric($filters['location']) ? 'term_id' : 'slug',
                'terms' => $filters['location'],
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Search
        if (!empty($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        // Order
        switch ($filters['orderby']) {
            case 'price':
                $args['meta_key'] = $meta_prefix . 'price';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rating':
                $args['meta_key'] = $meta_prefix . 'rating';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'date':
            default:
                $args['meta_key'] = $meta_prefix . 'start_date_str';
                $args['orderby'] = 'meta_value_num';
                break;
        }
        $args['order'] = strtoupper($filters['order']);

        // Execute query
        $query = new WP_Query($args);

        // Get current user ID if authenticated
        $user_id = null;
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if ($token) {
            $decoded = LMA_JWT_Handler::validate_access_token($token);
            if (!is_wp_error($decoded)) {
                $user_id = $decoded->uid;
            }
        }

        // Format events
        $events = array_map(function($post) use ($filters, $user_id) {
            return LMA_Event_Formatter::format_list($post, array(
                'lat' => $filters['lat'],
                'lng' => $filters['lng'],
                'user_id' => $user_id,
            ));
        }, $query->posts);

        // Filter by distance if coordinates provided
        if ($filters['lat'] && $filters['lng'] && $filters['radius']) {
            $events = array_filter($events, function($event) use ($filters) {
                if (!isset($event['location']['distance_km'])) {
                    return true;
                }
                return $event['location']['distance_km'] <= $filters['radius'];
            });

            // Re-sort by distance if requested
            if ($filters['orderby'] === 'distance') {
                usort($events, function($a, $b) use ($filters) {
                    $dist_a = $a['location']['distance_km'] ?? PHP_INT_MAX;
                    $dist_b = $b['location']['distance_km'] ?? PHP_INT_MAX;
                    return $filters['order'] === 'asc' ? $dist_a - $dist_b : $dist_b - $dist_a;
                });
            }

            $events = array_values($events);
        }

        // Build filters applied
        $filters_applied = array_filter(array(
            'city' => $filters['city'],
            'location' => $filters['location'],
            'category' => $filters['category'],
            'thematique' => $filters['thematique'],
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'price_max' => $filters['price_max'] > 0 ? $filters['price_max'] : null,
            'free_only' => $filters['free_only'] ?: null,
            'search' => $filters['search'],
        ));

        return LMA_Response::success(array(
            'events' => $events,
            'pagination' => array(
                'current_page' => $filters['page'],
                'per_page' => $filters['per_page'],
                'total_items' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
                'has_next' => $filters['page'] < $query->max_num_pages,
                'has_prev' => $filters['page'] > 1,
            ),
            'filters_applied' => $filters_applied,
        ));
    }

    /**
     * Get single event
     */
    public function get_event($request) {
        $event_id = absint($request->get_param('id'));

        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Get current user ID if authenticated
        $user_id = null;
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if ($token) {
            $decoded = LMA_JWT_Handler::validate_access_token($token);
            if (!is_wp_error($decoded)) {
                $user_id = $decoded->uid;
            }
        }

        $formatted = LMA_Event_Formatter::format_detail($event, array(
            'user_id' => $user_id,
        ));

        return LMA_Response::success($formatted);
    }
}
