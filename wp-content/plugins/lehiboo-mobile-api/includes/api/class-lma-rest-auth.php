<?php
/**
 * REST Auth Controller
 * Endpoints d'authentification
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Auth {

    /**
     * Namespace
     */
    protected $namespace = 'lehiboo/v2';

    /**
     * Register routes
     */
    public function register_routes() {
        // Register
        register_rest_route($this->namespace, '/auth/register', array(
            'methods' => 'POST',
            'callback' => array($this, 'register'),
            'permission_callback' => '__return_true',
        ));

        // Login
        register_rest_route($this->namespace, '/auth/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'login'),
            'permission_callback' => '__return_true',
        ));

        // Refresh token
        register_rest_route($this->namespace, '/auth/refresh', array(
            'methods' => 'POST',
            'callback' => array($this, 'refresh'),
            'permission_callback' => '__return_true',
        ));

        // Forgot password
        register_rest_route($this->namespace, '/auth/forgot-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'forgot_password'),
            'permission_callback' => '__return_true',
        ));

        // Reset password
        register_rest_route($this->namespace, '/auth/reset-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'reset_password'),
            'permission_callback' => '__return_true',
        ));

        // Logout
        register_rest_route($this->namespace, '/auth/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'logout'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // AI Backend Token - pour obtenir la cle API du backend AI
        register_rest_route($this->namespace, '/auth/ai-token', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_ai_token'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Verify OTP
        register_rest_route($this->namespace, '/auth/verify-otp', array(
            'methods' => 'POST',
            'callback' => array($this, 'verify_otp'),
            'permission_callback' => '__return_true',
        ));

        // Resend OTP
        register_rest_route($this->namespace, '/auth/resend-otp', array(
            'methods' => 'POST',
            'callback' => array($this, 'resend_otp'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Register new user
     */
    public function register($request) {
        // Rate limit
        $rate_check = LMA_Rate_Limiter::enforce('auth/register');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $data = array(
            'email' => LMA_Security::sanitize_email($request->get_param('email')),
            'password' => $request->get_param('password'),
            'first_name' => LMA_Security::sanitize_string($request->get_param('first_name')),
            'last_name' => LMA_Security::sanitize_string($request->get_param('last_name')),
            'phone' => LMA_Security::sanitize_phone($request->get_param('phone')),
        );

        // Validate
        $validation = LMA_Validator::validate_registration($data);
        if (is_wp_error($validation)) {
            return LMA_Response::from_error($validation);
        }

        // Create user
        $user_id = wp_create_user(
            $data['email'], // Use email as username
            $data['password'],
            $data['email']
        );

        if (is_wp_error($user_id)) {
            return LMA_Response::error(
                'registration_failed',
                $user_id->get_error_message(),
                400
            );
        }

        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'display_name' => $data['first_name'] . ' ' . $data['last_name'],
        ));

        if (!empty($data['phone'])) {
            update_user_meta($user_id, 'phone', $data['phone']);
        }

        // Set role to subscriber
        $user = get_user_by('ID', $user_id);
        $user->set_role('subscriber');

        // Mark user as NOT verified (email_verified = false)
        update_user_meta($user_id, 'email_verified', false);

        // Generate and store OTP
        $otp = LMA_OTP_Handler::create_otp($user_id);

        if (is_wp_error($otp)) {
            // If OTP creation failed, delete the user and return error
            wp_delete_user($user_id);
            return LMA_Response::from_error($otp);
        }

        // Send OTP email
        $email_sent = LMA_OTP_Handler::send_otp_email($user_id, $otp);

        if (!$email_sent) {
            // Log the error but don't fail registration
            error_log('[LeHiboo] Failed to send OTP email to user ' . $user_id);
        }

        // Return pending verification response (NO tokens at this stage)
        return LMA_Response::success(array(
            'pending_verification' => true,
            'user_id' => (string) $user_id,
            'email' => $data['email'],
            'message' => __('Un code de vérification a été envoyé à votre adresse email', 'lehiboo-mobile-api'),
        ), 201);
    }

    /**
     * Login user
     */
    public function login($request) {
        // Rate limit
        $rate_check = LMA_Rate_Limiter::enforce('auth/login');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $data = array(
            'email' => LMA_Security::sanitize_email($request->get_param('email')),
            'password' => $request->get_param('password'),
        );

        // Validate
        $validation = LMA_Validator::validate_login($data);
        if (is_wp_error($validation)) {
            return LMA_Response::from_error($validation);
        }

        // Get user by email
        $user = get_user_by('email', $data['email']);

        if (!$user) {
            return LMA_Response::error(
                'invalid_credentials',
                __('Email ou mot de passe incorrect', 'lehiboo-mobile-api'),
                401
            );
        }

        // Check password
        if (!wp_check_password($data['password'], $user->user_pass, $user->ID)) {
            return LMA_Response::error(
                'invalid_credentials',
                __('Email ou mot de passe incorrect', 'lehiboo-mobile-api'),
                401
            );
        }

        // Check if account is active
        if ($user->user_status == 1) {
            return LMA_Response::error(
                'account_disabled',
                __('Votre compte a été désactivé', 'lehiboo-mobile-api'),
                403
            );
        }

        // Check if email is verified
        if (!LMA_OTP_Handler::is_user_verified($user->ID)) {
            // Generate new OTP and send email
            $otp = LMA_OTP_Handler::create_otp($user->ID);

            if (!is_wp_error($otp)) {
                LMA_OTP_Handler::send_otp_email($user->ID, $otp);
            }

            return LMA_Response::error(
                'email_not_verified',
                __('Votre email n\'est pas vérifié. Un nouveau code de vérification a été envoyé.', 'lehiboo-mobile-api'),
                403,
                array(
                    'pending_verification' => true,
                    'user_id' => (string) $user->ID,
                    'email' => $user->user_email,
                )
            );
        }

        // Generate tokens
        $tokens = LMA_JWT_Handler::generate_tokens($user, $request->get_header('User-Agent'));

        $response_data = array(
            'user' => $this->format_user($user),
            'tokens' => $tokens,
        );

        // Add partner info if applicable
        if (in_array('el_event_manager', (array) $user->roles)) {
            $response_data['partner_info'] = $this->get_partner_info($user);
        }

        return LMA_Response::success($response_data);
    }

    /**
     * Refresh token
     */
    public function refresh($request) {
        $refresh_token = $request->get_param('refresh_token');

        if (empty($refresh_token)) {
            return LMA_Response::error(
                'missing_token',
                __('Refresh token requis', 'lehiboo-mobile-api'),
                400
            );
        }

        // Validate refresh token
        $decoded = LMA_JWT_Handler::validate_refresh_token($refresh_token);

        if (is_wp_error($decoded)) {
            return LMA_Response::from_error($decoded);
        }

        // Get user
        $user = get_user_by('ID', $decoded->uid);

        if (!$user) {
            return LMA_Response::error(
                'user_not_found',
                __('Utilisateur introuvable', 'lehiboo-mobile-api'),
                401
            );
        }

        // Revoke old refresh token
        LMA_JWT_Handler::revoke_refresh_token($refresh_token);

        // Generate new tokens
        $tokens = LMA_JWT_Handler::generate_tokens($user, $request->get_header('User-Agent'));

        return LMA_Response::success(array(
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => 'Bearer',
            'expires_in' => $tokens['expires_in'],
        ));
    }

    /**
     * Forgot password
     */
    public function forgot_password($request) {
        // Rate limit
        $rate_check = LMA_Rate_Limiter::enforce('auth/forgot-password');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $email = LMA_Security::sanitize_email($request->get_param('email'));

        // Always return success to prevent email enumeration
        $success_response = LMA_Response::success(array(
            'message' => __('Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.', 'lehiboo-mobile-api'),
        ));

        if (empty($email)) {
            return $success_response;
        }

        $user = get_user_by('email', $email);

        if (!$user) {
            return $success_response;
        }

        // Generate reset key
        $reset_key = get_password_reset_key($user);

        if (is_wp_error($reset_key)) {
            return $success_response;
        }

        // Send email
        $reset_url = add_query_arg(array(
            'action' => 'rp',
            'key' => $reset_key,
            'login' => rawurlencode($user->user_login),
        ), wp_login_url());

        $message = sprintf(
            __("Bonjour %s,\n\nVous avez demandé la réinitialisation de votre mot de passe LeHiboo.\n\nCliquez sur le lien ci-dessous pour créer un nouveau mot de passe:\n\n%s\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\nL'équipe LeHiboo", 'lehiboo-mobile-api'),
            $user->display_name,
            $reset_url
        );

        wp_mail(
            $email,
            __('[LeHiboo] Réinitialisation de votre mot de passe', 'lehiboo-mobile-api'),
            $message
        );

        return $success_response;
    }

    /**
     * Reset password
     */
    public function reset_password($request) {
        $email = LMA_Security::sanitize_email($request->get_param('email'));
        $key = $request->get_param('key');
        $new_password = $request->get_param('new_password');

        if (empty($email) || empty($key) || empty($new_password)) {
            return LMA_Response::error(
                'missing_params',
                __('Paramètres manquants', 'lehiboo-mobile-api'),
                400
            );
        }

        // Validate password
        $password_check = LMA_Security::validate_password($new_password);
        if (!$password_check['valid']) {
            return LMA_Response::error(
                'weak_password',
                implode('. ', $password_check['errors']),
                400
            );
        }

        $user = get_user_by('email', $email);

        if (!$user) {
            return LMA_Response::error(
                'invalid_key',
                __('Lien de réinitialisation invalide', 'lehiboo-mobile-api'),
                400
            );
        }

        // Check reset key
        $check = check_password_reset_key($key, $user->user_login);

        if (is_wp_error($check)) {
            return LMA_Response::error(
                'invalid_key',
                __('Lien de réinitialisation invalide ou expiré', 'lehiboo-mobile-api'),
                400
            );
        }

        // Reset password
        reset_password($user, $new_password);

        // Revoke all tokens
        LMA_JWT_Handler::revoke_all_user_tokens($user->ID);

        return LMA_Response::success(array(
            'message' => __('Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter.', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Logout
     */
    public function logout($request) {
        $token = LMA_JWT_Handler::get_token_from_request($request);

        // Note: Access tokens can't be revoked (they'll expire naturally)
        // But we can revoke all refresh tokens for this user
        $user = wp_get_current_user();

        if ($user->ID) {
            LMA_JWT_Handler::revoke_all_user_tokens($user->ID);
        }

        return LMA_Response::success(array(
            'message' => __('Déconnexion réussie', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Get AI Backend Token
     * Retourne la cle API pour acceder au backend AI (Petit Boo)
     */
    public function get_ai_token($request) {
        $user = wp_get_current_user();

        if (!$user || !$user->ID) {
            return LMA_Response::error(
                'unauthorized',
                __('Authentification requise', 'lehiboo-mobile-api'),
                401
            );
        }

        // Recuperer la cle API stockee dans les options
        $api_key = get_option('lma_ai_backend_api_key', '');

        if (empty($api_key)) {
            return LMA_Response::error(
                'api_key_not_configured',
                __('La cle API du backend AI n\'est pas configuree', 'lehiboo-mobile-api'),
                500
            );
        }

        return LMA_Response::success(array(
            'api_key' => $api_key,
            'backend_url' => get_option('lma_ai_backend_url', 'https://preprod.lehiboo.com/api-planner'),
            'expires_in' => 86400, // 24h (informatif, la cle n'expire pas vraiment)
            'user' => array(
                'id' => $user->ID,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
            ),
        ));
    }

    /**
     * Verify OTP code
     */
    public function verify_otp($request) {
        // Rate limit
        $rate_check = LMA_Rate_Limiter::enforce('auth/verify-otp');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $user_id = absint($request->get_param('user_id'));
        $email = LMA_Security::sanitize_email($request->get_param('email'));
        $otp = sanitize_text_field($request->get_param('otp'));

        // Validate required fields
        if (empty($user_id) || empty($email) || empty($otp)) {
            return LMA_Response::error(
                'missing_params',
                __('Paramètres manquants (user_id, email, otp requis)', 'lehiboo-mobile-api'),
                400
            );
        }

        // Get user and verify email matches
        $user = get_user_by('ID', $user_id);

        if (!$user) {
            return LMA_Response::error(
                'user_not_found',
                __('Utilisateur non trouvé', 'lehiboo-mobile-api'),
                404
            );
        }

        if ($user->user_email !== $email) {
            return LMA_Response::error(
                'email_mismatch',
                __('L\'email ne correspond pas à l\'utilisateur', 'lehiboo-mobile-api'),
                400
            );
        }

        // Check if already verified
        if (LMA_OTP_Handler::is_user_verified($user_id)) {
            return LMA_Response::error(
                'user_already_verified',
                __('Ce compte est déjà vérifié', 'lehiboo-mobile-api'),
                400
            );
        }

        // Verify OTP
        $verification = LMA_OTP_Handler::verify_otp($user_id, $otp);

        if (is_wp_error($verification)) {
            return LMA_Response::from_error($verification);
        }

        // Mark user as verified
        LMA_OTP_Handler::mark_user_verified($user_id);

        // Generate tokens now that user is verified
        $tokens = LMA_JWT_Handler::generate_tokens($user, $request->get_header('User-Agent'));

        return LMA_Response::success(array(
            'user' => $this->format_user($user),
            'tokens' => $tokens,
        ));
    }

    /**
     * Resend OTP code
     */
    public function resend_otp($request) {
        // Rate limit - more restrictive
        $rate_check = LMA_Rate_Limiter::enforce('auth/resend-otp');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $user_id = absint($request->get_param('user_id'));
        $email = LMA_Security::sanitize_email($request->get_param('email'));

        // Validate required fields
        if (empty($user_id) || empty($email)) {
            return LMA_Response::error(
                'missing_params',
                __('Paramètres manquants (user_id, email requis)', 'lehiboo-mobile-api'),
                400
            );
        }

        // Get user and verify email matches
        $user = get_user_by('ID', $user_id);

        if (!$user) {
            return LMA_Response::error(
                'user_not_found',
                __('Utilisateur non trouvé', 'lehiboo-mobile-api'),
                404
            );
        }

        if ($user->user_email !== $email) {
            return LMA_Response::error(
                'email_mismatch',
                __('L\'email ne correspond pas à l\'utilisateur', 'lehiboo-mobile-api'),
                400
            );
        }

        // Check if already verified
        if (LMA_OTP_Handler::is_user_verified($user_id)) {
            return LMA_Response::error(
                'user_already_verified',
                __('Ce compte est déjà vérifié', 'lehiboo-mobile-api'),
                400
            );
        }

        // Generate new OTP
        $otp = LMA_OTP_Handler::create_otp($user_id);

        if (is_wp_error($otp)) {
            return LMA_Response::from_error($otp);
        }

        // Send OTP email
        $email_sent = LMA_OTP_Handler::send_otp_email($user_id, $otp);

        if (!$email_sent) {
            error_log('[LeHiboo] Failed to resend OTP email to user ' . $user_id);
            return LMA_Response::error(
                'email_failed',
                __('Impossible d\'envoyer l\'email. Veuillez réessayer.', 'lehiboo-mobile-api'),
                500
            );
        }

        return LMA_Response::success(array(
            'message' => __('Un nouveau code a été envoyé', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Format user for response
     */
    private function format_user($user) {
        $roles = (array) $user->roles;
        $role = in_array('el_event_manager', $roles) ? 'el_event_manager' :
                (in_array('administrator', $roles) ? 'administrator' : 'subscriber');

        return array(
            'id' => $user->ID,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => get_user_meta($user->ID, 'phone', true) ?: null,
            'role' => $role,
            'is_verified' => LMA_OTP_Handler::is_user_verified($user->ID),
            'avatar_url' => get_avatar_url($user->ID),
            'capabilities' => array(
                'can_book' => true,
                'can_scan_tickets' => in_array($role, array('el_event_manager', 'administrator')),
                'can_manage_events' => in_array($role, array('el_event_manager', 'administrator')),
            ),
        );
    }

    /**
     * Get partner info
     */
    private function get_partner_info($user) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Count events
        $events = get_posts(array(
            'post_type' => 'event',
            'author' => $user->ID,
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids',
        ));

        $upcoming = 0;
        foreach ($events as $event_id) {
            $start_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);
            if ($start_date && (is_numeric($start_date) ? $start_date : strtotime($start_date)) > time()) {
                $upcoming++;
            }
        }

        return array(
            'events_count' => count($events),
            'upcoming_events' => $upcoming,
            'organization_name' => get_user_meta($user->ID, 'organization_name', true) ?: $user->display_name,
        );
    }
}
