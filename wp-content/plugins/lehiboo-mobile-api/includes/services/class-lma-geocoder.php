<?php
/**
 * Geocoder Service
 * Geocodes addresses using Nominatim (OpenStreetMap)
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Geocoder {

    /**
     * Nominatim API endpoint
     */
    const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    /**
     * Rate limit: 1 request per second (Nominatim policy)
     */
    const RATE_LIMIT_SECONDS = 1;

    /**
     * Geocode an event when saved
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     */
    public static function geocode_event_on_save($post_id, $post, $update) {
        // Don't run on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Don't run on revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Get the address from event meta
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $address = get_post_meta($post_id, $meta_prefix . 'address', true);

        if (empty($address)) {
            return;
        }

        // Check if already geocoded with same address
        global $wpdb;
        $table = $wpdb->prefix . 'lma_locations';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d",
            $post_id
        ));

        if ($existing && $existing->raw_address === $address && $existing->latitude) {
            // Already geocoded with same address
            return;
        }

        // Schedule geocoding (to avoid blocking save)
        wp_schedule_single_event(time() + 2, 'lma_geocode_event', array($post_id, $address));
    }

    /**
     * Actually perform geocoding (called by cron)
     *
     * @param int $event_id
     * @param string $address
     */
    public static function do_geocode($event_id, $address) {
        $result = self::geocode_address($address);

        if ($result) {
            self::save_location($event_id, $address, $result);
        }
    }

    /**
     * Geocode an address using Nominatim
     *
     * @param string $address
     * @return array|null
     */
    public static function geocode_address($address) {
        // Respect rate limiting
        $last_request = get_transient('lma_nominatim_last_request');
        if ($last_request && (time() - $last_request) < self::RATE_LIMIT_SECONDS) {
            sleep(self::RATE_LIMIT_SECONDS);
        }

        $url = add_query_arg(array(
            'q' => $address,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 1,
            'countrycodes' => 'fr', // Limit to France
        ), self::NOMINATIM_URL);

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'LeHiboo/1.0 (https://lehiboo.com)',
            ),
        ));

        // Update rate limit timestamp
        set_transient('lma_nominatim_last_request', time(), 60);

        if (is_wp_error($response)) {
            error_log('[LMA Geocoder] Request failed: ' . $response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !is_array($data)) {
            error_log('[LMA Geocoder] No results for: ' . $address);
            return null;
        }

        $result = $data[0];
        $addr = $result['address'] ?? array();

        return array(
            'latitude' => floatval($result['lat']),
            'longitude' => floatval($result['lon']),
            'address_line' => self::build_address_line($addr),
            'postal_code' => $addr['postcode'] ?? null,
            'city' => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['municipality'] ?? null,
            'department' => $addr['county'] ?? null,
            'region' => $addr['state'] ?? null,
            'country' => $addr['country'] ?? 'France',
        );
    }

    /**
     * Build address line from components
     */
    private static function build_address_line($addr) {
        $parts = array();

        if (!empty($addr['house_number'])) {
            $parts[] = $addr['house_number'];
        }
        if (!empty($addr['road'])) {
            $parts[] = $addr['road'];
        }

        return implode(' ', $parts) ?: null;
    }

    /**
     * Save location to database
     *
     * @param int $event_id
     * @param string $raw_address
     * @param array $data
     */
    public static function save_location($event_id, $raw_address, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'lma_locations';

        // Check if exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE event_id = %d",
            $event_id
        ));

        $location_data = array(
            'event_id' => $event_id,
            'raw_address' => $raw_address,
            'address_line' => $data['address_line'],
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'department' => $data['department'],
            'region' => $data['region'],
            'country' => $data['country'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'geocoded_at' => current_time('mysql'),
            'geocode_source' => 'nominatim',
            'updated_at' => current_time('mysql'),
        );

        if ($existing) {
            $wpdb->update($table, $location_data, array('event_id' => $event_id));
        } else {
            $location_data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $location_data);
        }

        // Also update post meta for backwards compatibility
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        update_post_meta($event_id, $meta_prefix . 'lat', $data['latitude']);
        update_post_meta($event_id, $meta_prefix . 'lng', $data['longitude']);
        update_post_meta($event_id, $meta_prefix . 'city', $data['city']);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[LMA Geocoder] Geocoded event %d: %s -> %s (%f, %f)',
                $event_id,
                $raw_address,
                $data['city'],
                $data['latitude'],
                $data['longitude']
            ));
        }
    }

    /**
     * Get location for an event
     *
     * @param int $event_id
     * @return object|null
     */
    public static function get_location($event_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lma_locations';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d",
            $event_id
        ));
    }

    /**
     * Get all cities with events
     *
     * @return array
     */
    public static function get_cities_with_events() {
        global $wpdb;
        $table = $wpdb->prefix . 'lma_locations';

        $results = $wpdb->get_results(
            "SELECT
                city,
                department,
                region,
                COUNT(*) as event_count,
                AVG(latitude) as center_lat,
                AVG(longitude) as center_lng
            FROM $table
            WHERE city IS NOT NULL AND city != ''
            GROUP BY city, department, region
            ORDER BY event_count DESC, city ASC"
        );

        return array_map(function($row) {
            return array(
                'name' => $row->city,
                'department' => $row->department,
                'region' => $row->region,
                'event_count' => intval($row->event_count),
                'coordinates' => array(
                    'lat' => floatval($row->center_lat),
                    'lng' => floatval($row->center_lng),
                ),
            );
        }, $results);
    }

    /**
     * Geocode all existing events (batch migration)
     *
     * @param int $batch_size
     * @return array Stats
     */
    public static function geocode_all_events($batch_size = 50) {
        global $wpdb;
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $table = $wpdb->prefix . 'lma_locations';

        // Get events without location data
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, pm.meta_value as address
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
            LEFT JOIN $table l ON p.ID = l.event_id
            WHERE p.post_type = 'event'
            AND p.post_status = 'publish'
            AND pm.meta_value IS NOT NULL
            AND pm.meta_value != ''
            AND (l.id IS NULL OR l.latitude IS NULL)
            LIMIT %d",
            $meta_prefix . 'address',
            $batch_size
        ));

        $stats = array(
            'total' => count($events),
            'success' => 0,
            'failed' => 0,
        );

        foreach ($events as $event) {
            $result = self::geocode_address($event->address);

            if ($result) {
                self::save_location($event->ID, $event->address, $result);
                $stats['success']++;
            } else {
                $stats['failed']++;
            }

            // Rate limiting
            sleep(self::RATE_LIMIT_SECONDS);
        }

        return $stats;
    }
}

// Register cron action for deferred geocoding
add_action('lma_geocode_event', array('LMA_Geocoder', 'do_geocode'), 10, 2);
