<?php
/**
 * OTP Handler Class
 * Gestion des codes OTP pour la vérification email
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_OTP_Handler {

    /**
     * OTP length
     */
    const OTP_LENGTH = 6;

    /**
     * OTP validity duration in minutes
     */
    const OTP_VALIDITY_MINUTES = 10;

    /**
     * Max verification attempts before blocking
     */
    const MAX_ATTEMPTS = 5;

    /**
     * Block duration in minutes after max attempts
     */
    const BLOCK_DURATION_MINUTES = 15;

    /**
     * Generate a new OTP code
     *
     * @return string
     */
    public static function generate_otp() {
        $otp = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    /**
     * Create and store OTP for a user
     *
     * @param int $user_id
     * @return string|WP_Error OTP code on success, WP_Error on failure
     */
    public static function create_otp($user_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_user_otp';

        // Check if user is blocked
        $blocked = self::is_user_blocked($user_id);
        if (is_wp_error($blocked)) {
            return $blocked;
        }

        // Delete any existing OTP for this user
        $wpdb->delete($table, array('user_id' => $user_id), array('%d'));

        // Generate new OTP
        $otp = self::generate_otp();
        $expires_at = gmdate('Y-m-d H:i:s', strtotime('+' . self::OTP_VALIDITY_MINUTES . ' minutes'));

        // Store OTP
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'otp_code' => $otp,
                'expires_at' => $expires_at,
                'attempts' => 0,
                'blocked_until' => null,
                'created_at' => current_time('mysql', true),
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result === false) {
            return new WP_Error(
                'otp_creation_failed',
                __('Impossible de créer le code de vérification', 'lehiboo-mobile-api'),
                array('status' => 500)
            );
        }

        return $otp;
    }

    /**
     * Verify OTP code for a user
     *
     * @param int $user_id
     * @param string $otp
     * @return true|WP_Error True on success, WP_Error on failure
     */
    public static function verify_otp($user_id, $otp) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_user_otp';

        // Check if user is blocked
        $blocked = self::is_user_blocked($user_id);
        if (is_wp_error($blocked)) {
            return $blocked;
        }

        // Get OTP record
        $record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
                $user_id
            )
        );

        if (!$record) {
            return new WP_Error(
                'otp_not_found',
                __('Aucun code de vérification trouvé. Veuillez en demander un nouveau.', 'lehiboo-mobile-api'),
                array('status' => 400)
            );
        }

        // Check if expired
        if (strtotime($record->expires_at) < time()) {
            // Delete expired OTP
            $wpdb->delete($table, array('id' => $record->id), array('%d'));

            return new WP_Error(
                'otp_expired',
                __('Le code a expiré. Veuillez en demander un nouveau.', 'lehiboo-mobile-api'),
                array('status' => 400)
            );
        }

        // Check OTP code
        if ($record->otp_code !== $otp) {
            // Increment attempts
            $new_attempts = $record->attempts + 1;

            if ($new_attempts >= self::MAX_ATTEMPTS) {
                // Block user
                $blocked_until = gmdate('Y-m-d H:i:s', strtotime('+' . self::BLOCK_DURATION_MINUTES . ' minutes'));
                $wpdb->update(
                    $table,
                    array(
                        'attempts' => $new_attempts,
                        'blocked_until' => $blocked_until,
                    ),
                    array('id' => $record->id),
                    array('%d', '%s'),
                    array('%d')
                );

                return new WP_Error(
                    'too_many_attempts',
                    sprintf(
                        __('Trop de tentatives. Réessayez dans %d minutes.', 'lehiboo-mobile-api'),
                        self::BLOCK_DURATION_MINUTES
                    ),
                    array('status' => 429)
                );
            }

            // Update attempts count
            $wpdb->update(
                $table,
                array('attempts' => $new_attempts),
                array('id' => $record->id),
                array('%d'),
                array('%d')
            );

            $remaining = self::MAX_ATTEMPTS - $new_attempts;

            return new WP_Error(
                'invalid_otp',
                sprintf(
                    __('Code de vérification invalide. %d tentative(s) restante(s).', 'lehiboo-mobile-api'),
                    $remaining
                ),
                array('status' => 400, 'remaining_attempts' => $remaining)
            );
        }

        // OTP is valid - delete it
        $wpdb->delete($table, array('id' => $record->id), array('%d'));

        return true;
    }

    /**
     * Check if user is blocked from OTP verification
     *
     * @param int $user_id
     * @return true|WP_Error True if not blocked, WP_Error if blocked
     */
    public static function is_user_blocked($user_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_user_otp';

        $record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT blocked_until FROM $table WHERE user_id = %d AND blocked_until IS NOT NULL ORDER BY created_at DESC LIMIT 1",
                $user_id
            )
        );

        if ($record && strtotime($record->blocked_until) > time()) {
            $remaining_minutes = ceil((strtotime($record->blocked_until) - time()) / 60);

            return new WP_Error(
                'too_many_attempts',
                sprintf(
                    __('Trop de tentatives. Réessayez dans %d minute(s).', 'lehiboo-mobile-api'),
                    $remaining_minutes
                ),
                array('status' => 429, 'retry_after' => $remaining_minutes * 60)
            );
        }

        return true;
    }

    /**
     * Send OTP email to user
     *
     * @param int $user_id
     * @param string $otp
     * @return bool
     */
    public static function send_otp_email($user_id, $otp) {
        $user = get_user_by('ID', $user_id);

        if (!$user) {
            return false;
        }

        $first_name = $user->first_name ?: $user->display_name;

        $subject = __('Votre code de vérification LeHiboo', 'lehiboo-mobile-api');

        $message = sprintf(
            __(
                "Bonjour %s,\n\n" .
                "Votre code de vérification est :\n\n" .
                "    %s\n\n" .
                "Ce code expire dans %d minutes.\n\n" .
                "Si vous n'avez pas créé de compte sur LeHiboo, ignorez cet email.\n\n" .
                "L'équipe LeHiboo",
                'lehiboo-mobile-api'
            ),
            $first_name,
            $otp,
            self::OTP_VALIDITY_MINUTES
        );

        // Set content type to HTML for better formatting
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        return wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * Cleanup expired OTP codes (called by cron)
     */
    public static function cleanup_expired_otp() {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_user_otp';

        // Delete expired OTP codes older than 24 hours
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE expires_at < %s",
                gmdate('Y-m-d H:i:s', strtotime('-24 hours'))
            )
        );

        // Clear blocks that have expired
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET blocked_until = NULL WHERE blocked_until IS NOT NULL AND blocked_until < %s",
                gmdate('Y-m-d H:i:s')
            )
        );
    }

    /**
     * Mark user as verified
     *
     * @param int $user_id
     * @return bool
     */
    public static function mark_user_verified($user_id) {
        return update_user_meta($user_id, 'email_verified', 'yes');
    }

    /**
     * Mark user as pending verification (not verified)
     *
     * @param int $user_id
     * @return bool
     */
    public static function mark_user_pending($user_id) {
        return update_user_meta($user_id, 'email_verified', 'pending');
    }

    /**
     * Check if user email is verified
     *
     * Backward compatibility: Users created before OTP feature
     * (without email_verified meta) are considered verified.
     *
     * @param int $user_id
     * @return bool
     */
    public static function is_user_verified($user_id) {
        $meta = get_user_meta($user_id, 'email_verified', true);

        // If meta is 'pending', user needs to verify
        if ($meta === 'pending') {
            return false;
        }

        // If meta is 'yes', user is verified
        if ($meta === 'yes') {
            return true;
        }

        // If meta doesn't exist (empty string) or has legacy value,
        // user was created before OTP feature - consider them verified
        return true;
    }
}
