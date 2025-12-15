<?php
/**
 * REST Cities Controller
 * Endpoints for city filtering
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Cities {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List cities with events
        register_rest_route($this->namespace, '/cities', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cities'),
            'permission_callback' => '__return_true',
        ));

        // Get city details
        register_rest_route($this->namespace, '/cities/(?P<name>[^/]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_city'),
            'permission_callback' => '__return_true',
        ));

        // Admin: Trigger geocoding migration
        register_rest_route($this->namespace, '/admin/geocode-events', array(
            'methods' => 'POST',
            'callback' => array($this, 'geocode_events'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
    }

    /**
     * Get list of cities with events
     * GET /cities
     */
    public function get_cities($request) {
        // Try cache
        $cached = LMA_Cache::get('cities', 'cities_geocoded');
        if ($cached !== false) {
            return LMA_Response::success($cached);
        }

        // Get from database
        $cities = LMA_Geocoder::get_cities_with_events();

        // If empty, return default Hauts-de-France cities
        if (empty($cities)) {
            $cities = $this->get_default_cities();
        }

        $response_data = array(
            'cities' => $cities,
            'total' => count($cities),
        );

        // Cache the response
        LMA_Cache::set('cities', 'cities_geocoded', $response_data);

        return LMA_Response::success($response_data);
    }

    /**
     * Get city details with coordinates
     * GET /cities/{name}
     */
    public function get_city($request) {
        $city_name = urldecode($request->get_param('name'));

        global $wpdb;
        $table = $wpdb->prefix . 'lma_locations';

        $city = $wpdb->get_row($wpdb->prepare(
            "SELECT
                city as name,
                department,
                region,
                COUNT(*) as event_count,
                AVG(latitude) as center_lat,
                AVG(longitude) as center_lng,
                MIN(latitude) as min_lat,
                MAX(latitude) as max_lat,
                MIN(longitude) as min_lng,
                MAX(longitude) as max_lng
            FROM $table
            WHERE city = %s
            GROUP BY city, department, region",
            $city_name
        ));

        if (!$city) {
            // Try to find in default cities
            $default = $this->find_default_city($city_name);
            if ($default) {
                return LMA_Response::success($default);
            }

            return LMA_Response::error(
                'city_not_found',
                __('Ville introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        return LMA_Response::success(array(
            'name' => $city->name,
            'department' => $city->department,
            'region' => $city->region,
            'event_count' => intval($city->event_count),
            'coordinates' => array(
                'lat' => floatval($city->center_lat),
                'lng' => floatval($city->center_lng),
            ),
            'bounding_box' => array(
                'north_east' => array(
                    'lat' => floatval($city->max_lat),
                    'lng' => floatval($city->max_lng),
                ),
                'south_west' => array(
                    'lat' => floatval($city->min_lat),
                    'lng' => floatval($city->min_lng),
                ),
            ),
        ));
    }

    /**
     * Trigger geocoding for all events (admin only)
     * POST /admin/geocode-events
     */
    public function geocode_events($request) {
        $batch_size = absint($request->get_param('batch_size')) ?: 50;

        $stats = LMA_Geocoder::geocode_all_events($batch_size);

        return LMA_Response::success(array(
            'message' => sprintf(
                'Geocoded %d/%d events (%d failed)',
                $stats['success'],
                $stats['total'],
                $stats['failed']
            ),
            'stats' => $stats,
        ));
    }

    /**
     * Check admin permission
     */
    public function check_admin_permission($request) {
        // Check for API key or admin user
        $api_key = $request->get_header('X-API-Key');
        $expected_key = get_option('lehiboo_ai_api_key');

        if ($api_key && $expected_key && $api_key === $expected_key) {
            return true;
        }

        return current_user_can('manage_options');
    }

    /**
     * Get default Hauts-de-France cities
     */
    private function get_default_cities() {
        return array(
            array(
                'name' => 'Lille',
                'department' => 'Nord',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.6292, 'lng' => 3.0573),
            ),
            array(
                'name' => 'Valenciennes',
                'department' => 'Nord',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.3577, 'lng' => 3.5235),
            ),
            array(
                'name' => 'Amiens',
                'department' => 'Somme',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 49.8941, 'lng' => 2.2958),
            ),
            array(
                'name' => 'Dunkerque',
                'department' => 'Nord',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 51.0343, 'lng' => 2.3768),
            ),
            array(
                'name' => 'Arras',
                'department' => 'Pas-de-Calais',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.2916, 'lng' => 2.7775),
            ),
            array(
                'name' => 'Calais',
                'department' => 'Pas-de-Calais',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.9513, 'lng' => 1.8587),
            ),
            array(
                'name' => 'Douai',
                'department' => 'Nord',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.3715, 'lng' => 3.0803),
            ),
            array(
                'name' => 'Lens',
                'department' => 'Pas-de-Calais',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 50.4289, 'lng' => 2.8333),
            ),
            array(
                'name' => 'Beauvais',
                'department' => 'Oise',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 49.4295, 'lng' => 2.0807),
            ),
            array(
                'name' => 'Saint-Quentin',
                'department' => 'Aisne',
                'region' => 'Hauts-de-France',
                'event_count' => 0,
                'coordinates' => array('lat' => 49.8467, 'lng' => 3.2875),
            ),
        );
    }

    /**
     * Find a city in default list
     */
    private function find_default_city($name) {
        $defaults = $this->get_default_cities();
        $name_lower = strtolower($name);

        foreach ($defaults as $city) {
            if (strtolower($city['name']) === $name_lower) {
                return $city;
            }
        }

        return null;
    }
}
