<?php
/**
 * REST User Controller
 * Endpoints profil utilisateur
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_User {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // Get current user profile
        register_rest_route($this->namespace, '/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_profile'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Update profile
        register_rest_route($this->namespace, '/me', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_profile'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Update password
        register_rest_route($this->namespace, '/me/password', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_password'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Delete account
        register_rest_route($this->namespace, '/me', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_account'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Update avatar
        register_rest_route($this->namespace, '/me/avatar', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_avatar'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // User devices (for push notifications)
        register_rest_route($this->namespace, '/me/devices', array(
            'methods' => 'POST',
            'callback' => array($this, 'register_device'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        register_rest_route($this->namespace, '/me/devices/(?P<token>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'unregister_device'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Notification preferences
        register_rest_route($this->namespace, '/me/notifications', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_notification_preferences'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        register_rest_route($this->namespace, '/me/notifications', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_notification_preferences'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Get user profile
     */
    public function get_profile($request) {
        $user = wp_get_current_user();

        return LMA_Response::success($this->format_user_profile($user));
    }

    /**
     * Update user profile
     */
    public function update_profile($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $updates = array('ID' => $user_id);
        $meta_updates = array();

        // Allowed fields
        if (isset($params['first_name'])) {
            $updates['first_name'] = sanitize_text_field($params['first_name']);
        }
        if (isset($params['last_name'])) {
            $updates['last_name'] = sanitize_text_field($params['last_name']);
        }
        if (isset($params['display_name'])) {
            $updates['display_name'] = sanitize_text_field($params['display_name']);
        }

        // Meta fields
        if (isset($params['phone'])) {
            $meta_updates['phone'] = sanitize_text_field($params['phone']);
        }
        if (isset($params['city'])) {
            $meta_updates['city'] = sanitize_text_field($params['city']);
        }
        if (isset($params['bio'])) {
            $meta_updates['description'] = sanitize_textarea_field($params['bio']);
        }
        if (isset($params['birth_date'])) {
            $meta_updates['birth_date'] = sanitize_text_field($params['birth_date']);
        }

        // Update email (requires validation)
        if (isset($params['email']) && is_email($params['email'])) {
            $new_email = sanitize_email($params['email']);
            $user = wp_get_current_user();

            if ($new_email !== $user->user_email) {
                // Check if email is already taken
                if (email_exists($new_email)) {
                    return LMA_Response::error(
                        'email_exists',
                        __('Cette adresse email est déjà utilisée', 'lehiboo-mobile-api'),
                        400
                    );
                }
                $updates['user_email'] = $new_email;
            }
        }

        // Update user
        if (count($updates) > 1) {
            $result = wp_update_user($updates);
            if (is_wp_error($result)) {
                return LMA_Response::error(
                    'update_failed',
                    $result->get_error_message(),
                    400
                );
            }
        }

        // Update meta
        foreach ($meta_updates as $key => $value) {
            update_user_meta($user_id, $key, $value);
        }

        // Return updated profile
        $user = get_user_by('ID', $user_id);

        return LMA_Response::success(array(
            'message' => __('Profil mis à jour avec succès', 'lehiboo-mobile-api'),
            'user' => $this->format_user_profile($user),
        ));
    }

    /**
     * Update password
     */
    public function update_password($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $current_password = $params['current_password'] ?? '';
        $new_password = $params['new_password'] ?? '';
        $confirm_password = $params['confirm_password'] ?? '';

        // Validate
        if (empty($current_password) || empty($new_password)) {
            return LMA_Response::error(
                'missing_fields',
                __('Tous les champs sont requis', 'lehiboo-mobile-api'),
                400
            );
        }

        if ($new_password !== $confirm_password) {
            return LMA_Response::error(
                'password_mismatch',
                __('Les mots de passe ne correspondent pas', 'lehiboo-mobile-api'),
                400
            );
        }

        // Verify current password
        $user = get_user_by('ID', $user_id);
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            return LMA_Response::error(
                'invalid_password',
                __('Mot de passe actuel incorrect', 'lehiboo-mobile-api'),
                400
            );
        }

        // Validate new password strength
        if (strlen($new_password) < 8) {
            return LMA_Response::error(
                'weak_password',
                __('Le mot de passe doit contenir au moins 8 caractères', 'lehiboo-mobile-api'),
                400
            );
        }

        // Update password
        wp_set_password($new_password, $user_id);

        // Revoke all refresh tokens (security measure)
        LMA_JWT_Handler::revoke_all_user_tokens($user_id);

        // Generate new tokens
        $user = get_user_by('ID', $user_id);
        $tokens = LMA_JWT_Handler::generate_tokens($user);

        return LMA_Response::success(array(
            'message' => __('Mot de passe mis à jour avec succès', 'lehiboo-mobile-api'),
            'tokens' => $tokens,
        ));
    }

    /**
     * Delete account
     */
    public function delete_account($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $password = $params['password'] ?? '';
        $confirm = $params['confirm'] ?? false;

        if (!$confirm) {
            return LMA_Response::error(
                'confirmation_required',
                __('Veuillez confirmer la suppression du compte', 'lehiboo-mobile-api'),
                400
            );
        }

        // Verify password
        $user = get_user_by('ID', $user_id);
        if (!wp_check_password($password, $user->user_pass, $user_id)) {
            return LMA_Response::error(
                'invalid_password',
                __('Mot de passe incorrect', 'lehiboo-mobile-api'),
                400
            );
        }

        // Check for active bookings
        global $wpdb;
        $active_bookings = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_user_id'
             WHERE p.post_type = 'el_bookings'
             AND pm.meta_value = %d
             AND p.post_status IN ('confirmed', 'pending')",
            $user_id
        ));

        if ($active_bookings > 0) {
            return LMA_Response::error(
                'active_bookings',
                __('Vous avez des réservations actives. Veuillez les annuler avant de supprimer votre compte.', 'lehiboo-mobile-api'),
                400
            );
        }

        // Anonymize user data instead of deleting
        wp_update_user(array(
            'ID' => $user_id,
            'user_email' => 'deleted_' . $user_id . '@lehiboo.local',
            'display_name' => __('Utilisateur supprimé', 'lehiboo-mobile-api'),
            'first_name' => '',
            'last_name' => '',
        ));

        // Clear meta
        delete_user_meta($user_id, 'phone');
        delete_user_meta($user_id, 'city');
        delete_user_meta($user_id, 'description');

        // Revoke all tokens
        LMA_JWT_Handler::revoke_all_user_tokens($user_id);

        // Mark as deleted
        update_user_meta($user_id, 'account_deleted', true);
        update_user_meta($user_id, 'deleted_at', current_time('mysql'));

        return LMA_Response::success(array(
            'message' => __('Votre compte a été supprimé avec succès', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Update avatar
     */
    public function update_avatar($request) {
        $user_id = get_current_user_id();

        // Check for file upload
        $files = $request->get_file_params();

        if (empty($files['avatar'])) {
            return LMA_Response::error(
                'no_file',
                __('Aucun fichier envoyé', 'lehiboo-mobile-api'),
                400
            );
        }

        $file = $files['avatar'];

        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            return LMA_Response::error(
                'invalid_file_type',
                __('Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.', 'lehiboo-mobile-api'),
                400
            );
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return LMA_Response::error(
                'file_too_large',
                __('Le fichier est trop volumineux. Taille maximum: 5MB', 'lehiboo-mobile-api'),
                400
            );
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Upload file
        $attachment_id = media_handle_upload('avatar', 0);

        if (is_wp_error($attachment_id)) {
            return LMA_Response::error(
                'upload_failed',
                $attachment_id->get_error_message(),
                400
            );
        }

        // Delete old avatar if exists
        $old_avatar = get_user_meta($user_id, 'lma_avatar_id', true);
        if ($old_avatar) {
            wp_delete_attachment($old_avatar, true);
        }

        // Save new avatar
        update_user_meta($user_id, 'lma_avatar_id', $attachment_id);

        $avatar_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');

        return LMA_Response::success(array(
            'message' => __('Avatar mis à jour avec succès', 'lehiboo-mobile-api'),
            'avatar_url' => $avatar_url,
        ));
    }

    /**
     * Register device for push notifications
     */
    public function register_device($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $token = sanitize_text_field($params['token'] ?? '');
        $platform = sanitize_text_field($params['platform'] ?? '');
        $device_name = sanitize_text_field($params['device_name'] ?? '');

        if (empty($token) || empty($platform)) {
            return LMA_Response::error(
                'missing_fields',
                __('Token et plateforme requis', 'lehiboo-mobile-api'),
                400
            );
        }

        if (!in_array($platform, array('ios', 'android'))) {
            return LMA_Response::error(
                'invalid_platform',
                __('Plateforme invalide', 'lehiboo-mobile-api'),
                400
            );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'lma_devices';

        // Check if table exists, create if not
        $this->maybe_create_devices_table();

        // Check if device already registered
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE token = %s",
            $token
        ));

        if ($existing) {
            // Update existing
            $wpdb->update(
                $table,
                array(
                    'user_id' => $user_id,
                    'device_name' => $device_name,
                    'updated_at' => current_time('mysql'),
                ),
                array('token' => $token),
                array('%d', '%s', '%s'),
                array('%s')
            );
        } else {
            // Insert new
            $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'token' => $token,
                    'platform' => $platform,
                    'device_name' => $device_name,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );
        }

        return LMA_Response::success(array(
            'message' => __('Appareil enregistré avec succès', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Unregister device
     */
    public function unregister_device($request) {
        $token = sanitize_text_field($request->get_param('token'));

        global $wpdb;
        $table = $wpdb->prefix . 'lma_devices';

        $wpdb->delete($table, array('token' => $token), array('%s'));

        return LMA_Response::success(array(
            'message' => __('Appareil désenregistré', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Get notification preferences
     */
    public function get_notification_preferences($request) {
        $user_id = get_current_user_id();

        $defaults = array(
            'push_enabled' => true,
            'email_enabled' => true,
            'booking_confirmations' => true,
            'booking_reminders' => true,
            'event_updates' => true,
            'promotions' => false,
            'newsletter' => false,
        );

        $prefs = get_user_meta($user_id, 'lma_notification_prefs', true);
        $prefs = is_array($prefs) ? array_merge($defaults, $prefs) : $defaults;

        return LMA_Response::success(array(
            'preferences' => $prefs,
        ));
    }

    /**
     * Update notification preferences
     */
    public function update_notification_preferences($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $allowed_keys = array(
            'push_enabled',
            'email_enabled',
            'booking_confirmations',
            'booking_reminders',
            'event_updates',
            'promotions',
            'newsletter',
        );

        $current = get_user_meta($user_id, 'lma_notification_prefs', true) ?: array();

        foreach ($allowed_keys as $key) {
            if (isset($params[$key])) {
                $current[$key] = (bool) $params[$key];
            }
        }

        update_user_meta($user_id, 'lma_notification_prefs', $current);

        return LMA_Response::success(array(
            'message' => __('Préférences mises à jour', 'lehiboo-mobile-api'),
            'preferences' => $current,
        ));
    }

    /**
     * Format user profile
     */
    private function format_user_profile($user) {
        $avatar_id = get_user_meta($user->ID, 'lma_avatar_id', true);
        $avatar_url = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : get_avatar_url($user->ID);

        $role = 'client';
        if (in_array('administrator', (array) $user->roles)) {
            $role = 'admin';
        } elseif (in_array('el_event_manager', (array) $user->roles)) {
            $role = 'partner';
        }

        return array(
            'id' => $user->ID,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar_url' => $avatar_url,
            'phone' => get_user_meta($user->ID, 'phone', true) ?: null,
            'city' => get_user_meta($user->ID, 'city', true) ?: null,
            'bio' => get_user_meta($user->ID, 'description', true) ?: null,
            'birth_date' => get_user_meta($user->ID, 'birth_date', true) ?: null,
            'role' => $role,
            'registered_at' => $user->user_registered,
            'is_verified' => (bool) get_user_meta($user->ID, 'lma_email_verified', true),
        );
    }

    /**
     * Create devices table if not exists
     */
    private function maybe_create_devices_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_devices';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                token varchar(500) NOT NULL,
                platform varchar(20) NOT NULL,
                device_name varchar(255) DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY token (token),
                KEY user_id (user_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
