<?php
/**
 * Security Class
 * Sanitization et validation de sécurité
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Security {

    /**
     * Sanitize email
     *
     * @param string $email
     * @return string
     */
    public static function sanitize_email($email) {
        return sanitize_email(trim($email));
    }

    /**
     * Sanitize phone number
     *
     * @param string $phone
     * @return string
     */
    public static function sanitize_phone($phone) {
        // Remove all non-numeric except + at start
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure only one + at the start
        if (strpos($phone, '+') !== 0) {
            $phone = ltrim($phone, '+');
        } else {
            $phone = '+' . preg_replace('/\+/', '', substr($phone, 1));
        }

        return $phone;
    }

    /**
     * Sanitize string input
     *
     * @param string $input
     * @return string
     */
    public static function sanitize_string($input) {
        return sanitize_text_field(trim($input));
    }

    /**
     * Sanitize HTML input
     *
     * @param string $input
     * @return string
     */
    public static function sanitize_html($input) {
        return wp_kses_post(trim($input));
    }

    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public static function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone format (E.164)
     *
     * @param string $phone
     * @return bool
     */
    public static function is_valid_phone($phone) {
        // E.164 format: +[country code][subscriber number]
        // Min 8 digits, max 15 digits
        return preg_match('/^\+?[1-9]\d{7,14}$/', $phone) === 1;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validate_password($password) {
        $errors = array();

        if (strlen($password) < 8) {
            $errors[] = __('Le mot de passe doit contenir au moins 8 caractères', 'lehiboo-mobile-api');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = __('Le mot de passe doit contenir au moins une majuscule', 'lehiboo-mobile-api');
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = __('Le mot de passe doit contenir au moins un chiffre', 'lehiboo-mobile-api');
        }

        return array(
            'valid' => empty($errors),
            'errors' => $errors,
        );
    }

    /**
     * Check for suspicious content (XSS, SQL injection)
     *
     * @param string $input
     * @return bool True if suspicious
     */
    public static function is_suspicious($input) {
        $patterns = array(
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/eval\s*\(/i',
            '/document\.(cookie|write)/i',
            '/union\s+select/i',
            '/insert\s+into/i',
            '/drop\s+table/i',
            '/--\s*$/m',
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get user agent
     *
     * @return string
     */
    public static function get_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255)
            : '';
    }

    /**
     * Log API request
     *
     * @param string $endpoint
     * @param string $method
     * @param int $status_code
     * @param float $response_time
     * @param int|null $user_id
     */
    public static function log_request($endpoint, $method, $status_code, $response_time = null, $user_id = null) {
        if (get_option('lma_log_requests') !== 'yes') {
            return;
        }

        global $wpdb;

        $table = $wpdb->prefix . 'lma_api_logs';

        $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'endpoint' => substr($endpoint, 0, 100),
                'method' => $method,
                'status_code' => $status_code,
                'response_time' => $response_time,
                'ip_address' => self::get_client_ip(),
                'user_agent' => self::get_user_agent(),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%d', '%f', '%s', '%s', '%s')
        );
    }

    /**
     * Generate secure random token
     *
     * @param int $length
     * @return string
     */
    public static function generate_token($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash password reset code
     *
     * @param string $code
     * @return string
     */
    public static function hash_reset_code($code) {
        return hash('sha256', $code);
    }
}
