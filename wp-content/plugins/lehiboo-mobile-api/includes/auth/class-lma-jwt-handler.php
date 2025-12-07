<?php
/**
 * JWT Handler
 * Gestion des tokens JWT pour l'authentification mobile
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include Firebase JWT library
require_once LMA_PLUGIN_DIR . 'vendor/firebase-jwt/JWT.php';
require_once LMA_PLUGIN_DIR . 'vendor/firebase-jwt/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Class LMA_JWT_Handler
 */
class LMA_JWT_Handler {

    /**
     * Get JWT secret key
     */
    private static function get_secret_key() {
        $secret = get_option('lma_jwt_secret');

        if (empty($secret)) {
            $secret = wp_generate_password(64, true, true);
            update_option('lma_jwt_secret', $secret);
        }

        return $secret;
    }

    /**
     * Generate access token
     *
     * @param WP_User $user
     * @return string
     */
    public static function generate_access_token($user) {
        $issued_at = time();
        $expiration = $issued_at + LMA_ACCESS_TOKEN_EXPIRY;

        $payload = array(
            'iss' => get_site_url(),
            'iat' => $issued_at,
            'exp' => $expiration,
            'uid' => $user->ID,
            'email' => $user->user_email,
            'role' => self::get_user_role($user),
            'type' => 'access',
        );

        return JWT::encode($payload, self::get_secret_key(), LMA_JWT_ALGO);
    }

    /**
     * Generate refresh token
     *
     * @param WP_User $user
     * @param string|null $device_info
     * @return string
     */
    public static function generate_refresh_token($user, $device_info = null) {
        global $wpdb;

        $issued_at = time();
        $expiration = $issued_at + LMA_REFRESH_TOKEN_EXPIRY;
        $token_id = wp_generate_uuid4();

        $payload = array(
            'iss' => get_site_url(),
            'iat' => $issued_at,
            'exp' => $expiration,
            'uid' => $user->ID,
            'type' => 'refresh',
            'jti' => $token_id,
        );

        $token = JWT::encode($payload, self::get_secret_key(), LMA_JWT_ALGO);

        // Store refresh token hash in database
        $table = $wpdb->prefix . 'lma_refresh_tokens';
        $wpdb->insert(
            $table,
            array(
                'user_id' => $user->ID,
                'token_hash' => hash('sha256', $token),
                'device_info' => $device_info,
                'ip_address' => self::get_client_ip(),
                'expires_at' => date('Y-m-d H:i:s', $expiration),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        return $token;
    }

    /**
     * Validate access token
     *
     * @param string $token
     * @return object|WP_Error
     */
    public static function validate_access_token($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::get_secret_key(), LMA_JWT_ALGO));

            // Check token type
            if (!isset($decoded->type) || $decoded->type !== 'access') {
                return new WP_Error(
                    'invalid_token_type',
                    __('Type de token invalide', 'lehiboo-mobile-api'),
                    array('status' => 401)
                );
            }

            // Verify user exists
            $user = get_user_by('ID', $decoded->uid);
            if (!$user) {
                return new WP_Error(
                    'user_not_found',
                    __('Utilisateur introuvable', 'lehiboo-mobile-api'),
                    array('status' => 401)
                );
            }

            return $decoded;

        } catch (ExpiredException $e) {
            return new WP_Error(
                'token_expired',
                __('Token expiré', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        } catch (SignatureInvalidException $e) {
            return new WP_Error(
                'token_invalid',
                __('Signature du token invalide', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        } catch (Exception $e) {
            return new WP_Error(
                'token_invalid',
                __('Token invalide', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        }
    }

    /**
     * Validate refresh token
     *
     * @param string $token
     * @return object|WP_Error
     */
    public static function validate_refresh_token($token) {
        global $wpdb;

        try {
            $decoded = JWT::decode($token, new Key(self::get_secret_key(), LMA_JWT_ALGO));

            // Check token type
            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                return new WP_Error(
                    'invalid_token_type',
                    __('Type de token invalide', 'lehiboo-mobile-api'),
                    array('status' => 401)
                );
            }

            // Check if token exists and not revoked
            $table = $wpdb->prefix . 'lma_refresh_tokens';
            $token_hash = hash('sha256', $token);

            $stored_token = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE token_hash = %s AND user_id = %d AND revoked = 0",
                $token_hash,
                $decoded->uid
            ));

            if (!$stored_token) {
                return new WP_Error(
                    'token_revoked',
                    __('Token révoqué ou invalide', 'lehiboo-mobile-api'),
                    array('status' => 401)
                );
            }

            // Check if expired in DB
            if (strtotime($stored_token->expires_at) < time()) {
                return new WP_Error(
                    'token_expired',
                    __('Token expiré', 'lehiboo-mobile-api'),
                    array('status' => 401)
                );
            }

            // Update last used
            $wpdb->update(
                $table,
                array('last_used_at' => current_time('mysql')),
                array('id' => $stored_token->id),
                array('%s'),
                array('%d')
            );

            return $decoded;

        } catch (ExpiredException $e) {
            return new WP_Error(
                'token_expired',
                __('Refresh token expiré', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        } catch (Exception $e) {
            return new WP_Error(
                'token_invalid',
                __('Refresh token invalide', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        }
    }

    /**
     * Revoke refresh token
     *
     * @param string $token
     * @return bool
     */
    public static function revoke_refresh_token($token) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_refresh_tokens';
        $token_hash = hash('sha256', $token);

        return $wpdb->update(
            $table,
            array('revoked' => 1),
            array('token_hash' => $token_hash),
            array('%d'),
            array('%s')
        ) !== false;
    }

    /**
     * Revoke all user tokens
     *
     * @param int $user_id
     * @return bool
     */
    public static function revoke_all_user_tokens($user_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_refresh_tokens';

        return $wpdb->update(
            $table,
            array('revoked' => 1),
            array('user_id' => $user_id),
            array('%d'),
            array('%d')
        ) !== false;
    }

    /**
     * Clean expired tokens
     */
    public static function clean_expired_tokens() {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_refresh_tokens';

        $wpdb->query(
            "DELETE FROM $table WHERE expires_at < NOW() OR revoked = 1"
        );
    }

    /**
     * Extract token from Authorization header
     *
     * @param WP_REST_Request $request
     * @return string|null
     */
    public static function get_token_from_request($request) {
        $auth_header = $request->get_header('Authorization');

        if (!$auth_header) {
            return null;
        }

        // Check for Bearer token
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get user from request
     *
     * @param WP_REST_Request $request
     * @return WP_User|WP_Error
     */
    public static function get_user_from_request($request) {
        $token = self::get_token_from_request($request);

        if (!$token) {
            return new WP_Error(
                'no_token',
                __('Token d\'authentification requis', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        }

        $decoded = self::validate_access_token($token);

        if (is_wp_error($decoded)) {
            return $decoded;
        }

        $user = get_user_by('ID', $decoded->uid);

        if (!$user) {
            return new WP_Error(
                'user_not_found',
                __('Utilisateur introuvable', 'lehiboo-mobile-api'),
                array('status' => 401)
            );
        }

        return $user;
    }

    /**
     * Permission callback for authenticated routes
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public static function authenticate($request) {
        $user = self::get_user_from_request($request);

        if (is_wp_error($user)) {
            return $user;
        }

        // Set current user for the request
        wp_set_current_user($user->ID);

        return true;
    }

    /**
     * Permission callback for partner routes
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public static function authenticate_partner($request) {
        $user = self::get_user_from_request($request);

        if (is_wp_error($user)) {
            return $user;
        }

        // Check if user is partner (el_event_manager)
        if (!in_array('el_event_manager', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            return new WP_Error(
                'insufficient_permissions',
                __('Accès réservé aux partenaires', 'lehiboo-mobile-api'),
                array('status' => 403)
            );
        }

        // Set current user for the request
        wp_set_current_user($user->ID);

        return true;
    }

    /**
     * Get primary user role
     *
     * @param WP_User $user
     * @return string
     */
    private static function get_user_role($user) {
        $roles = (array) $user->roles;

        // Priority order
        $priority = array('administrator', 'el_event_manager', 'subscriber');

        foreach ($priority as $role) {
            if (in_array($role, $roles)) {
                return $role;
            }
        }

        return !empty($roles) ? $roles[0] : 'subscriber';
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
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
     * Generate both tokens for user
     *
     * @param WP_User $user
     * @param string|null $device_info
     * @return array
     */
    public static function generate_tokens($user, $device_info = null) {
        return array(
            'access_token' => self::generate_access_token($user),
            'refresh_token' => self::generate_refresh_token($user, $device_info),
            'token_type' => 'Bearer',
            'expires_in' => LMA_ACCESS_TOKEN_EXPIRY,
        );
    }
}
