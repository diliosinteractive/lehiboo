<?php
/**
 * REST Home Feed Controller
 * Endpoint agrégé pour la homepage mobile
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Home_Feed {

    protected $namespace = 'lehiboo/v2';

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/home-feed', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_home_feed'),
            'permission_callback' => '__return_true',
            'args' => array(
                'lat' => array(
                    'type' => 'number',
                    'required' => false,
                    'description' => 'User latitude',
                ),
                'lng' => array(
                    'type' => 'number',
                    'required' => false,
                    'description' => 'User longitude',
                ),
                'radius' => array(
                    'type' => 'integer',
                    'default' => 20,
                    'description' => 'Search radius in km for today/tomorrow',
                ),
                'limit' => array(
                    'type' => 'integer',
                    'default' => 10,
                    'description' => 'Max items per section',
                ),
            ),
        ));
    }

    /**
     * Get aggregated home feed
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_home_feed($request) {
        $lat = $request->get_param('lat');
        $lng = $request->get_param('lng');
        $radius = absint($request->get_param('radius')) ?: 20;
        $limit = min(absint($request->get_param('limit')) ?: 10, 20); // Max 20

        $has_location = $lat !== null && $lng !== null;

        // Build cache key (round coordinates for better hit rate)
        $cache_key = $this->build_cache_key($lat, $lng, $radius, $limit);

        // Try to get from cache (only for unauthenticated requests)
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if (!$token) {
            $cached = LMA_Cache::get('home_feed', $cache_key);
            if ($cached !== false) {
                return LMA_Response::success($cached);
            }
        }

        // Get current user ID if authenticated
        $user_id = null;
        if ($token) {
            $decoded = LMA_JWT_Handler::validate_access_token($token);
            if (!is_wp_error($decoded)) {
                $user_id = $decoded->uid;
            }
        }

        // Fetch all sections
        $today = $this->get_events_for_date_range(
            strtotime('today 00:00:00'),
            strtotime('today 23:59:59'),
            $lat,
            $lng,
            $radius,
            $limit,
            $user_id,
            'distance'
        );

        $tomorrow = $this->get_events_for_date_range(
            strtotime('tomorrow 00:00:00'),
            strtotime('tomorrow 23:59:59'),
            $lat,
            $lng,
            $radius,
            $limit,
            $user_id,
            'distance'
        );

        $recommended = $this->get_recommended_events(
            $lat,
            $lng,
            $has_location ? 50 : null, // 50km radius if location, else global
            $limit,
            $user_id
        );

        $response_data = array(
            'today' => $today,
            'tomorrow' => $tomorrow,
            'recommended' => $recommended,
            'location_provided' => $has_location,
        );

        // Cache the response (only for unauthenticated requests)
        if (!$token) {
            LMA_Cache::set('home_feed', $cache_key, $response_data);
        }

        return LMA_Response::success($response_data);
    }

    /**
     * Get events for a specific date range
     *
     * @param int $start_timestamp
     * @param int $end_timestamp
     * @param float|null $lat
     * @param float|null $lng
     * @param int $radius
     * @param int $limit
     * @param int|null $user_id
     * @param string $orderby
     * @return array
     */
    private function get_events_for_date_range($start_timestamp, $end_timestamp, $lat, $lng, $radius, $limit, $user_id, $orderby = 'date') {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $has_location = $lat !== null && $lng !== null;

        // Query more than needed to allow for geo-filtering
        $query_limit = $has_location ? $limit * 3 : $limit;

        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => $query_limit,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $start_timestamp,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $end_timestamp,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ),
            ),
            'meta_key' => $meta_prefix . 'start_date_str',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        );

        $query = new WP_Query($args);

        // Format events
        $events = array_map(function($post) use ($lat, $lng, $user_id) {
            return LMA_Event_Formatter::format_list($post, array(
                'lat' => $lat,
                'lng' => $lng,
                'user_id' => $user_id,
            ));
        }, $query->posts);

        // Filter by radius if location provided
        if ($has_location && $radius > 0) {
            $events = array_filter($events, function($event) use ($radius) {
                if (!isset($event['location']['distance_km'])) {
                    return true; // Include events without coordinates
                }
                return $event['location']['distance_km'] <= $radius;
            });

            // Sort by distance
            if ($orderby === 'distance') {
                usort($events, function($a, $b) {
                    $dist_a = $a['location']['distance_km'] ?? PHP_INT_MAX;
                    $dist_b = $b['location']['distance_km'] ?? PHP_INT_MAX;
                    return $dist_a - $dist_b;
                });
            }

            $events = array_values($events);
        }

        // Limit results
        return array_slice($events, 0, $limit);
    }

    /**
     * Get recommended events based on popularity and rating
     *
     * @param float|null $lat
     * @param float|null $lng
     * @param int|null $radius
     * @param int $limit
     * @param int|null $user_id
     * @return array
     */
    private function get_recommended_events($lat, $lng, $radius, $limit, $user_id) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $has_location = $lat !== null && $lng !== null;

        // Query more than needed for geo-filtering
        $query_limit = ($has_location && $radius) ? $limit * 3 : $limit;

        // Events in the next 14 days
        $start_timestamp = strtotime('today 00:00:00');
        $end_timestamp = strtotime('+14 days 23:59:59');

        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => $query_limit,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $start_timestamp,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $end_timestamp,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ),
            ),
        );

        // Sort by popularity (post views) or rating
        // Try post_views_count first, fallback to rating
        $args['meta_key'] = 'post_views_count';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';

        $query = new WP_Query($args);

        // If no results with views, try with rating
        if ($query->post_count === 0) {
            $args['meta_key'] = $meta_prefix . 'rating';
            $query = new WP_Query($args);
        }

        // If still no results, just get by date
        if ($query->post_count === 0) {
            unset($args['meta_key']);
            $args['orderby'] = 'date';
            $query = new WP_Query($args);
        }

        // Format events
        $events = array_map(function($post) use ($lat, $lng, $user_id) {
            return LMA_Event_Formatter::format_list($post, array(
                'lat' => $lat,
                'lng' => $lng,
                'user_id' => $user_id,
            ));
        }, $query->posts);

        // Filter by radius if location and radius provided
        if ($has_location && $radius > 0) {
            $events = array_filter($events, function($event) use ($radius) {
                if (!isset($event['location']['distance_km'])) {
                    return true; // Include events without coordinates
                }
                return $event['location']['distance_km'] <= $radius;
            });
            $events = array_values($events);
        }

        // Limit results
        return array_slice($events, 0, $limit);
    }

    /**
     * Build cache key from parameters
     * Rounds coordinates for better cache hit rate
     *
     * @param float|null $lat
     * @param float|null $lng
     * @param int $radius
     * @param int $limit
     * @return string
     */
    private function build_cache_key($lat, $lng, $radius, $limit) {
        // Round coordinates to 1 decimal place (~11km precision)
        $lat_rounded = $lat !== null ? round($lat, 1) : 'null';
        $lng_rounded = $lng !== null ? round($lng, 1) : 'null';

        // Include current date to ensure cache refreshes daily
        $date = date('Y-m-d');

        return sprintf(
            'feed_%s_%s_%s_%d_%d',
            $lat_rounded,
            $lng_rounded,
            $date,
            $radius,
            $limit
        );
    }
}
