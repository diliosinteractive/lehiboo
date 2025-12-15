<?php
/**
 * ============================================================
 * PATCH DE SECURITE COMPLET - Plugin EventList
 * ============================================================
 *
 * Version: 2.0
 * Date: 2025-12-15
 *
 * Ce fichier corrige TOUTES les vulnerabilites AJAX du plugin eventlist
 * qui expose des actions sensibles aux utilisateurs non authentifies.
 *
 * INSTALLATION :
 * Copiez ce fichier dans wp-content/mu-plugins/
 * Les mu-plugins sont charges automatiquement par WordPress.
 *
 * ============================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ===========================================
 * PARTIE 1 : BLOQUER LES ACTIONS NON-PRIV
 * ===========================================
 */
add_action('init', 'lehiboo_secure_eventlist_ajax', 1);

function lehiboo_secure_eventlist_ajax() {

    // =====================================================
    // LISTE COMPLETE DES ACTIONS SENSIBLES - class-el-ajax.php
    // =====================================================
    $sensitive_actions_main = array(
        // === PROFIL UTILISATEUR ===
        'el_update_profile',
        'el_update_organisation',
        'el_update_presentation',
        'el_update_localisation',
        'el_add_social',
        'el_save_social',
        'el_check_password',
        'el_change_password',
        'el_update_role',
        'el_check_vendor_field_required',
        'el_update_payout_method',

        // === GESTION EVENEMENTS ===
        'el_pending_post',
        'el_publish_post',
        'el_trash_post',
        'el_duplicate_post',
        'el_delete_post',
        'el_bulk_action',
        'el_save_edit_event',
        'el_delete_gallery_image',

        // === CHECKOUT & PAIEMENT ===
        'el_process_checkout',
        'el_countdown_checkout',
        'el_add_package',
        'el_add_withdrawal',
        'el_payment_countdown',

        // === TICKETS & BOOKINGS ===
        'el_update_ticket_status',
        'el_cancel_booking',
        'el_ticket_transfer',
        'el_cancel_check_in',
        'el_update_ticket_max',
        'el_create_tickets_save',
        'el_create_tickets_show_calendar',
        'el_create_tickets_show_tickets',
        'el_multiple_customers_ticket',
        'el_show_data_booking',
        'el_show_column_tickets',

        // === TICKET MANAGER ===
        'el_ticket_manager_download_ticket',
        'el_ticket_manager_remove_ticket_pdf',
        'el_ticket_manager_send_ticket',
        'el_ticket_manager_download_tickets',
        'el_ticket_manager_remove_all_ticket',
        'el_ticket_manager_create_tickets',
        'el_ticket_received_download',
        'el_fe_unlink_download_ticket',
        'el_ticket_list',

        // === EXPORT (DONNEES SENSIBLES) ===
        'el_export_csv',
        'export_csv_ticket',
        'el_export_booking_split_multi_file',
        'el_export_ticket_split_multi_file',
        'el_export_page_item',
        'el_export_ticket_page_item',
        'el_booking_download_all_in_one',
        'el_ticket_download_all_in_one',
        'el_download_invoice',
        'el_download_tickets',

        // === UPLOAD FICHIERS ===
        'el_upload_files',

        // === AI ===
        'el_generate_ai_description',

        // === CALENDRIER / SCHEDULES ===
        'el_load_schedules',
        'el_choose_calendar',
        'el_load_edit_ticket_calendar',
        'el_check_date_search_ticket',
    );

    // =====================================================
    // LISTE COMPLETE DES ACTIONS ADMIN - class-el-admin-ajax.php
    // Ces actions sont TOUTES pour l'admin, aucune ne devrait etre nopriv!
    // =====================================================
    $sensitive_actions_admin = array(
        'mb_add_social',
        'mb_add_ticket',
        'add_seat_map',
        'add_area_map',
        'add_desc_seat_map',
        'mb_add_calendar',
        'mb_add_disable_date',
        'mb_add_disable_time_slot',
        'mb_add_schedules_time',
        'mb_add_coupon',
        'mb_add_services',
        'el_load_venue',
        'el_load_checklist_venue',
        'create_ticket_send_mail',
        'create_invoice',
        'send_invoice',
        'download_ticket',
        'unlink_download_ticket',
        'update_status_proccess',
        'add_custom_booking',
        'el_get_idcal_seatopt',
        'el_check_book_before_minutes',
        'el_check_schedules_time_book',
        'el_check_calendar_recurrence_time_book',
        'el_replace_get_tickets',
        'el_replace_ticket_date',
        'el_replace_ticket_date_posts_per_page',
        'el_replace_ticket_date_pagination',
        'el_replace_ticket_date_export_email',
        'el_replace_ticket_date_send_email',
        'el_update_event_status',
        'el_ticket_table_send_ticket',
        'el_ticket_table_download_ticket',
        'el_ticket_table_remove_ticket_pdf',
        'el_sync_data_package',
        'el_add_seat_code_row',
    );

    // =====================================================
    // ACTIONS DE PAIEMENT (Stripe, PayPal)
    // Note: Ces actions DOIVENT rester nopriv pour le checkout
    // mais on ajoute des verifications supplementaires
    // =====================================================
    $payment_actions = array(
        'el_payment_stripe',
        'el_add_package_stripe',
        'el_payment_paypal',
        'el_add_package_paypal',
        'el_payment_woo',
        'el_add_package_woo',
    );

    // Fusionner toutes les actions sensibles
    $all_sensitive_actions = array_merge($sensitive_actions_main, $sensitive_actions_admin);

    // Bloquer les actions sensibles pour les utilisateurs non authentifies
    foreach ($all_sensitive_actions as $action) {
        add_action('wp_ajax_nopriv_' . $action, 'lehiboo_block_sensitive_ajax', 1);
    }

    // Pour les actions de paiement, on ajoute une verification de session
    foreach ($payment_actions as $action) {
        add_action('wp_ajax_nopriv_' . $action, 'lehiboo_verify_payment_session', 1);
    }
}

/**
 * Bloquer les requetes AJAX non authentifiees pour les actions sensibles
 */
function lehiboo_block_sensitive_ajax() {
    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Logger la tentative d'intrusion
    error_log(sprintf(
        '[SECURITY ALERT] Blocked unauthenticated AJAX: action=%s | IP=%s | UA=%s | Time=%s',
        $action,
        $ip,
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        current_time('mysql')
    ));

    // Incrementer le compteur de tentatives suspectes pour cette IP
    lehiboo_track_suspicious_activity($ip, $action);

    wp_send_json_error(array(
        'message' => __('Authentication required', 'eventlist'),
        'code' => 'authentication_required'
    ), 401);

    wp_die();
}

/**
 * Verifier la session pour les actions de paiement
 */
function lehiboo_verify_payment_session() {
    // Les paiements necessitent une session de checkout valide
    if (!class_exists('EL') || !method_exists(EL(), 'cart_session')) {
        return; // Plugin pas charge, laisser passer
    }

    $booking_id = EL()->cart_session->get('booking_id');

    if (empty($booking_id)) {
        error_log(sprintf(
            '[SECURITY] Payment attempt without valid session: IP=%s',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        wp_send_json_error(array(
            'message' => __('Invalid checkout session', 'eventlist'),
            'code' => 'invalid_session'
        ), 403);

        wp_die();
    }
}

/**
 * ===========================================
 * PARTIE 2 : VERIFICATION DES CAPABILITIES
 * ===========================================
 */
add_action('init', 'lehiboo_add_capability_checks', 2);

function lehiboo_add_capability_checks() {

    // Actions qui necessitent d'etre un vendor/organisateur
    $vendor_actions = array(
        'el_update_profile',
        'el_update_organisation',
        'el_update_presentation',
        'el_update_localisation',
        'el_save_edit_event',
        'el_pending_post',
        'el_publish_post',
        'el_trash_post',
        'el_duplicate_post',
        'el_delete_post',
        'el_export_csv',
        'export_csv_ticket',
        'el_export_booking_split_multi_file',
        'el_export_ticket_split_multi_file',
        'el_ticket_manager_download_ticket',
        'el_ticket_manager_send_ticket',
        'el_ticket_manager_create_tickets',
        'el_add_withdrawal',
        'el_update_payout_method',
        'el_generate_ai_description',
    );

    foreach ($vendor_actions as $action) {
        add_action('wp_ajax_' . $action, 'lehiboo_check_vendor_capability', 1);
    }

    // Actions qui necessitent d'etre admin WordPress
    $admin_actions = array(
        'el_bulk_action',
        'el_update_role',
        'el_sync_data_package',
        'el_update_event_status',
        'mb_add_ticket',
        'add_seat_map',
        'add_area_map',
        'mb_add_calendar',
        'mb_add_coupon',
        'create_invoice',
        'add_custom_booking',
    );

    foreach ($admin_actions as $action) {
        add_action('wp_ajax_' . $action, 'lehiboo_check_admin_capability', 1);
    }
}

/**
 * Verifier si l'utilisateur est un vendor/organisateur
 */
function lehiboo_check_vendor_capability() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in'), 401);
        wp_die();
    }

    $user = wp_get_current_user();

    // Roles autorises a gerer les evenements
    $allowed_roles = array(
        'el_event_manager',  // Role EventList
        'administrator',
        'editor',
        'author',
        'vendor',
        'contributor',
    );

    // Verifier les roles OU la capability EventList
    if (!array_intersect($allowed_roles, $user->roles) && !current_user_can('edit_el_events')) {
        error_log(sprintf(
            '[SECURITY] Unauthorized vendor action: user_id=%d | login=%s | roles=%s | action=%s',
            $user->ID,
            $user->user_login,
            implode(', ', $user->roles),
            $_REQUEST['action'] ?? 'unknown'
        ));

        wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
        wp_die();
    }
}

/**
 * Verifier si l'utilisateur est admin
 */
function lehiboo_check_admin_capability() {
    if (!current_user_can('manage_options')) {
        error_log(sprintf(
            '[SECURITY] Unauthorized admin action: IP=%s | user_id=%d | action=%s',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            get_current_user_id(),
            $_REQUEST['action'] ?? 'unknown'
        ));

        wp_send_json_error(array('message' => 'Admin access required'), 403);
        wp_die();
    }
}

/**
 * ===========================================
 * PARTIE 3 : RATE LIMITING
 * ===========================================
 */
add_action('init', 'lehiboo_ajax_rate_limiting', 0);

function lehiboo_ajax_rate_limiting() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // Actions a limiter strictement (5 requetes par minute)
    $strict_rate_limited = array(
        'el_process_checkout',
        'el_upload_files',
        'el_generate_ai_description',
        'el_add_package',
        'el_add_withdrawal',
    );

    // Actions a limiter moderement (20 requetes par minute)
    $moderate_rate_limited = array(
        'el_save_edit_event',
        'el_export_csv',
        'export_csv_ticket',
        'el_single_send_mail_vendor',
        'el_single_send_mail_report',
    );

    if (in_array($action, $strict_rate_limited)) {
        lehiboo_check_rate_limit($ip, $action, 5, 60);
    } elseif (in_array($action, $moderate_rate_limited)) {
        lehiboo_check_rate_limit($ip, $action, 20, 60);
    }
}

/**
 * Verifier le rate limit
 */
function lehiboo_check_rate_limit($ip, $action, $max_requests, $window_seconds) {
    $transient_key = 'el_rate_' . md5($ip . $action);
    $count = get_transient($transient_key);

    if ($count === false) {
        set_transient($transient_key, 1, $window_seconds);
    } elseif ($count >= $max_requests) {
        error_log(sprintf(
            '[SECURITY] Rate limit exceeded: action=%s | IP=%s | count=%d',
            $action, $ip, $count
        ));

        wp_send_json_error(array(
            'message' => 'Too many requests. Please try again later.',
            'code' => 'rate_limit_exceeded'
        ), 429);

        wp_die();
    } else {
        set_transient($transient_key, $count + 1, $window_seconds);
    }
}

/**
 * ===========================================
 * PARTIE 4 : VALIDATION DES UPLOADS
 * ===========================================
 */
add_action('wp_ajax_el_upload_files', 'lehiboo_validate_upload_security', 0);

function lehiboo_validate_upload_security() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Authentication required'), 401);
        wp_die();
    }

    if (empty($_FILES)) {
        return; // Pas de fichier, continuer normalement
    }

    foreach ($_FILES as $file_key => $file) {
        if (is_array($file['name'])) {
            // Multiple files
            foreach ($file['name'] as $i => $name) {
                $single_file = array(
                    'name' => $file['name'][$i],
                    'tmp_name' => $file['tmp_name'][$i],
                    'size' => $file['size'][$i],
                );
                $result = lehiboo_validate_single_file($single_file);
                if (is_wp_error($result)) {
                    wp_send_json_error(array('message' => $result->get_error_message()), 400);
                    wp_die();
                }
            }
        } else {
            $result = lehiboo_validate_single_file($file);
            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $result->get_error_message()), 400);
                wp_die();
            }
        }
    }
}

/**
 * Valider un fichier uploade
 */
function lehiboo_validate_single_file($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Extensions INTERDITES (execution de code)
    $forbidden_extensions = array(
        // PHP
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'inc',
        // Executables
        'exe', 'sh', 'bash', 'bat', 'cmd', 'com', 'msi', 'dll', 'so',
        // Scripts web
        'js', 'jsp', 'jspx', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
        // Configuration serveur
        'htaccess', 'htpasswd', 'ini', 'conf',
        // Autres
        'svg', // Peut contenir du JS
    );

    if (in_array($ext, $forbidden_extensions)) {
        error_log(sprintf(
            '[SECURITY] Blocked forbidden file upload: file=%s | ext=%s | user=%d',
            $file['name'], $ext, get_current_user_id()
        ));
        return new WP_Error('forbidden_extension', 'This file type is not allowed: .' . $ext);
    }

    // Verifier le contenu (detection de code malveillant)
    if (!empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
        $content = file_get_contents($file['tmp_name'], false, null, 0, 1024); // Premier 1KB

        $malicious_patterns = array(
            '/<\?php/i',
            '/<\?=/i',
            '/<%/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/shell_exec/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',
            '/passthru/i',
        );

        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                error_log(sprintf(
                    '[SECURITY] Blocked malicious file content: file=%s | pattern=%s | user=%d',
                    $file['name'], $pattern, get_current_user_id()
                ));
                return new WP_Error('malicious_content', 'File contains potentially malicious content');
            }
        }
    }

    // Taille max: 10 MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'File is too large (max 10MB)');
    }

    return true;
}

/**
 * ===========================================
 * PARTIE 5 : TRACKING ACTIVITE SUSPECTE
 * ===========================================
 */
function lehiboo_track_suspicious_activity($ip, $action) {
    $transient_key = 'el_suspicious_' . md5($ip);
    $data = get_transient($transient_key);

    if ($data === false) {
        $data = array('count' => 1, 'actions' => array($action));
    } else {
        $data['count']++;
        $data['actions'][] = $action;
    }

    set_transient($transient_key, $data, 3600); // 1 heure

    // Si plus de 10 tentatives suspectes, alerter
    if ($data['count'] >= 10) {
        error_log(sprintf(
            '[SECURITY CRITICAL] Potential attack detected: IP=%s | attempts=%d | actions=%s',
            $ip,
            $data['count'],
            implode(', ', array_unique($data['actions']))
        ));

        // Optionnel: Bloquer l'IP temporairement
        if ($data['count'] >= 50) {
            set_transient('el_blocked_ip_' . md5($ip), true, 3600);
        }
    }
}

/**
 * Bloquer les IPs suspectes
 */
add_action('init', 'lehiboo_check_blocked_ip', 0);

function lehiboo_check_blocked_ip() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $is_blocked = get_transient('el_blocked_ip_' . md5($ip));

    if ($is_blocked) {
        http_response_code(403);
        die('Access denied');
    }
}

/**
 * ===========================================
 * PARTIE 6 : NONCE VERIFICATION GLOBALE
 * ===========================================
 */
add_action('init', 'lehiboo_enforce_nonce_verification', 1);

function lehiboo_enforce_nonce_verification() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

    // Actions qui DOIVENT avoir un nonce valide
    $nonce_required_actions = array(
        'el_save_edit_event',
        'el_process_checkout',
        'el_upload_files',
        'el_update_profile',
        'el_change_password',
        'el_add_package',
        'el_add_withdrawal',
    );

    if (in_array($action, $nonce_required_actions)) {
        $nonce = $_REQUEST['nonce'] ?? $_REQUEST['_wpnonce'] ?? '';

        // Verifier differents nonces possibles
        $valid_nonce = wp_verify_nonce($nonce, $action)
            || wp_verify_nonce($nonce, 'el_nonce')
            || wp_verify_nonce($nonce, 'el_payment_stripe');

        if (!$valid_nonce) {
            error_log(sprintf(
                '[SECURITY] Invalid nonce for action: %s | IP=%s',
                $action,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));

            wp_send_json_error(array(
                'message' => 'Security verification failed',
                'code' => 'invalid_nonce'
            ), 403);

            wp_die();
        }
    }
}

/**
 * ===========================================
 * PARTIE 7 : AUDIT LOG (optionnel, en mode debug)
 * ===========================================
 */
if (defined('WP_DEBUG') && WP_DEBUG && defined('LEHIBOO_AJAX_AUDIT') && LEHIBOO_AJAX_AUDIT) {
    add_action('wp_ajax_nopriv_*', 'lehiboo_audit_ajax_requests', 0);
    add_action('wp_ajax_*', 'lehiboo_audit_ajax_requests', 0);

    function lehiboo_audit_ajax_requests() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'unknown';

        error_log(sprintf(
            '[AJAX AUDIT] action=%s | user=%d | IP=%s | method=%s | time=%s',
            $action,
            get_current_user_id(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            current_time('mysql')
        ));
    }
}

/**
 * ===========================================
 * FIN DU PATCH DE SECURITE
 * ===========================================
 */
