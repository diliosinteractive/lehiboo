<?php
/**
 * Rate Limiter Class
 * Limitation des requêtes API
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Rate_Limiter {

    /**
     * Rate limits configuration
     * [endpoint_pattern => [limit, window_seconds]]
     */
    private static $limits = array(
        'auth/register' => array(5, 3600),       // 5 per hour
        'auth/login' => array(10, 900),          // 10 per 15 min
        'auth/forgot-password' => array(3, 3600), // 3 per hour
        'auth/verify-otp' => array(10, 900),     // 10 per 15 min
        'auth/resend-otp' => array(3, 3600),     // 3 per hour
        'partner/scan' => array(60, 60),         // 60 per minute
        'bookings' => array(10, 60),             // 10 per minute
        'default' => array(60, 60),              // 60 per minute
    );

    /**
     * Check rate limit for endpoint
     *
     * @param string $endpoint
     * @param string|null $identifier User ID or IP
     * @return array ['allowed' => bool, 'remaining' => int, 'reset' => timestamp]
     */
    public static function check($endpoint, $identifier = null) {
        if (get_option('lma_rate_limit_enabled') !== 'yes') {
            return array(
                'allowed' => true,
                'remaining' => PHP_INT_MAX,
                'reset' => 0,
            );
        }

        global $wpdb;

        if ($identifier === null) {
            $identifier = LMA_Security::get_client_ip();
        }

        // Get limit config for endpoint
        $config = self::get_limit_config($endpoint);
        $limit = $config[0];
        $window = $config[1];

        $table = $wpdb->prefix . 'lma_rate_limits';
        $endpoint_key = self::normalize_endpoint($endpoint);

        // Get current window data
        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE identifier = %s AND endpoint = %s",
            $identifier,
            $endpoint_key
        ));

        $now = time();

        if ($record) {
            $window_start = strtotime($record->window_start);

            // Check if window has expired
            if ($now - $window_start > $window) {
                // Reset window
                $wpdb->update(
                    $table,
                    array(
                        'request_count' => 1,
                        'window_start' => current_time('mysql'),
                    ),
                    array('id' => $record->id),
                    array('%d', '%s'),
                    array('%d')
                );

                return array(
                    'allowed' => true,
                    'remaining' => $limit - 1,
                    'reset' => $now + $window,
                );
            }

            // Check if limit exceeded
            if ($record->request_count >= $limit) {
                $reset = $window_start + $window;

                return array(
                    'allowed' => false,
                    'remaining' => 0,
                    'reset' => $reset,
                    'retry_after' => $reset - $now,
                );
            }

            // Increment counter
            $wpdb->update(
                $table,
                array('request_count' => $record->request_count + 1),
                array('id' => $record->id),
                array('%d'),
                array('%d')
            );

            return array(
                'allowed' => true,
                'remaining' => $limit - $record->request_count - 1,
                'reset' => $window_start + $window,
            );

        } else {
            // Create new record
            $wpdb->insert(
                $table,
                array(
                    'identifier' => $identifier,
                    'endpoint' => $endpoint_key,
                    'request_count' => 1,
                    'window_start' => current_time('mysql'),
                ),
                array('%s', '%s', '%d', '%s')
            );

            return array(
                'allowed' => true,
                'remaining' => $limit - 1,
                'reset' => $now + $window,
            );
        }
    }

    /**
     * Enforce rate limit (returns WP_Error if exceeded)
     *
     * @param string $endpoint
     * @param string|null $identifier
     * @return true|WP_Error
     */
    public static function enforce($endpoint, $identifier = null) {
        $result = self::check($endpoint, $identifier);

        if (!$result['allowed']) {
            return new WP_Error(
                'rate_limit_exceeded',
                sprintf(
                    __('Trop de requêtes. Réessayez dans %d secondes.', 'lehiboo-mobile-api'),
                    $result['retry_after']
                ),
                array(
                    'status' => 429,
                    'retry_after' => $result['retry_after'],
                )
            );
        }

        return true;
    }

    /**
     * Add rate limit headers to response
     *
     * @param WP_REST_Response $response
     * @param string $endpoint
     * @param string|null $identifier
     * @return WP_REST_Response
     */
    public static function add_headers($response, $endpoint, $identifier = null) {
        $result = self::check($endpoint, $identifier);
        $config = self::get_limit_config($endpoint);

        $response->header('X-RateLimit-Limit', $config[0]);
        $response->header('X-RateLimit-Remaining', max(0, $result['remaining']));
        $response->header('X-RateLimit-Reset', $result['reset']);

        return $response;
    }

    /**
     * Get limit configuration for endpoint
     *
     * @param string $endpoint
     * @return array [limit, window]
     */
    private static function get_limit_config($endpoint) {
        $normalized = self::normalize_endpoint($endpoint);

        foreach (self::$limits as $pattern => $config) {
            if (strpos($normalized, $pattern) !== false) {
                return $config;
            }
        }

        return self::$limits['default'];
    }

    /**
     * Normalize endpoint for comparison
     *
     * @param string $endpoint
     * @return string
     */
    private static function normalize_endpoint($endpoint) {
        // Remove leading slash and query strings
        $endpoint = ltrim($endpoint, '/');
        $endpoint = strtok($endpoint, '?');

        // Remove numeric IDs
        $endpoint = preg_replace('/\/\d+/', '', $endpoint);

        return $endpoint;
    }

    /**
     * Clean expired rate limit records
     */
    public static function cleanup() {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_rate_limits';

        // Delete records older than 24 hours
        $wpdb->query(
            "DELETE FROM $table WHERE window_start < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
    }
}
