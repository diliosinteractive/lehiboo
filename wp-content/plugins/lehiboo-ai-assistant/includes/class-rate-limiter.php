<?php
/**
 * Rate Limiter
 * Protection contre le spam et abus
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Rate_Limiter {

    /**
     * Check rate limit for identifier (IP or user ID)
     */
    public function check_limit($identifier) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'lehiboo_rate_limits';
        $max_requests = intval(get_option('lehiboo_ai_rate_limit_messages', 10));
        $window_seconds = intval(get_option('lehiboo_ai_rate_limit_window', 60));

        // Clean old entries
        $this->clean_old_entries($window_seconds);

        // Check current count
        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE identifier = %s",
            $identifier
        ));

        if (!$current) {
            // First request, insert
            $wpdb->insert(
                $table_name,
                array(
                    'identifier' => $identifier,
                    'request_count' => 1,
                    'window_start' => current_time('mysql'),
                ),
                array('%s', '%d', '%s')
            );

            return array(
                'allowed' => true,
                'remaining' => $max_requests - 1,
            );
        }

        // Check if window expired
        $window_start = strtotime($current->window_start);
        $now = current_time('timestamp');

        if (($now - $window_start) > $window_seconds) {
            // Window expired, reset
            $wpdb->update(
                $table_name,
                array(
                    'request_count' => 1,
                    'window_start' => current_time('mysql'),
                ),
                array('identifier' => $identifier),
                array('%d', '%s'),
                array('%s')
            );

            return array(
                'allowed' => true,
                'remaining' => $max_requests - 1,
            );
        }

        // Check if limit exceeded
        if ($current->request_count >= $max_requests) {
            $wait_time = $window_seconds - ($now - $window_start);

            return array(
                'allowed' => false,
                'wait_time' => $wait_time,
                'message' => sprintf(
                    __('Trop de messages envoyés. Veuillez attendre %d secondes.', 'lehiboo-ai-assistant'),
                    $wait_time
                ),
            );
        }

        // Increment count
        $wpdb->update(
            $table_name,
            array('request_count' => $current->request_count + 1),
            array('identifier' => $identifier),
            array('%d'),
            array('%s')
        );

        return array(
            'allowed' => true,
            'remaining' => $max_requests - ($current->request_count + 1),
        );
    }

    /**
     * Clean old entries from database
     */
    private function clean_old_entries($window_seconds) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'lehiboo_rate_limits';
        $cutoff = date('Y-m-d H:i:s', current_time('timestamp') - $window_seconds);

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE window_start < %s",
            $cutoff
        ));
    }

    /**
     * Get identifier for current request
     */
    public function get_identifier() {
        $user_id = get_current_user_id();

        if ($user_id) {
            return 'user_' . $user_id;
        }

        // Use IP for non-logged users
        $security = new Lehiboo_AI_Security();
        $ip = $security->get_client_ip();

        return 'ip_' . md5($ip);
    }

    /**
     * Check and enforce rate limit
     * Returns WP_Error if limit exceeded
     */
    public function enforce_limit() {
        $identifier = $this->get_identifier();
        $check = $this->check_limit($identifier);

        if (!$check['allowed']) {
            // Log rate limit hit
            do_action('lehiboo_ai_rate_limit_exceeded', $identifier, $check);

            return new WP_Error(
                'rate_limit_exceeded',
                $check['message'],
                array(
                    'status' => 429,
                    'wait_time' => $check['wait_time'],
                )
            );
        }

        return true;
    }
}
