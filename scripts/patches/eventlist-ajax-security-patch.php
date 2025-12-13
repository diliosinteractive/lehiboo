<?php
/**
 * ============================================================
 * PATCH DE SECURITE - Plugin EventList
 * ============================================================
 *
 * Ce fichier corrige les vulnerabilites AJAX du plugin eventlist
 * qui expose des actions sensibles aux utilisateurs non authentifies.
 *
 * INSTALLATION :
 * 1. Sauvegardez le fichier original :
 *    cp wp-content/plugins/eventlist/includes/class-el-ajax.php \
 *       wp-content/plugins/eventlist/includes/class-el-ajax.php.backup
 *
 * 2. Appliquez ce patch en ajoutant le code dans functions.php du theme enfant
 *    ou creez un mu-plugin dans wp-content/mu-plugins/
 *
 * ============================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Securiser les actions AJAX du plugin EventList
 * Supprime les hooks wp_ajax_nopriv_ pour les actions sensibles
 */
add_action('init', 'lehiboo_secure_eventlist_ajax', 1);

function lehiboo_secure_eventlist_ajax() {

    // Liste des actions AJAX qui NE DOIVENT PAS etre accessibles sans authentification
    $sensitive_actions = array(
        // Actions de modification de profil/utilisateur
        'el_update_profile',
        'el_update_organisation',
        'el_update_presentation',
        'el_update_localisation',
        'el_add_social',
        'el_save_social',
        'el_check_password',
        'el_change_password',
        'el_update_role',
        'el_update_payout_method',

        // Actions de gestion des evenements
        'el_pending_post',
        'el_publish_post',
        'el_trash_post',
        'el_duplicate_post',
        'el_delete_post',
        'el_bulk_action',
        'el_save_edit_event',
        'el_delete_gallery_image',

        // Actions de gestion des tickets/bookings
        'el_update_ticket_status',
        'el_cancel_booking',
        'el_ticket_transfer',
        'el_cancel_check_in',
        'el_update_ticket_max',
        'el_create_tickets_save',
        'el_ticket_manager_download_ticket',
        'el_ticket_manager_remove_ticket_pdf',
        'el_ticket_manager_send_ticket',
        'el_ticket_manager_download_tickets',
        'el_ticket_manager_remove_all_ticket',
        'el_ticket_manager_create_tickets',

        // Actions d'export (donnees sensibles)
        'el_export_csv',
        'export_csv_ticket',
        'el_export_booking_split_multi_file',
        'el_export_ticket_split_multi_file',
        'el_booking_download_all_in_one',
        'el_ticket_download_all_in_one',
        'el_download_invoice',
        'el_download_tickets',

        // Actions de paiement
        'el_process_checkout',
        'el_add_withdrawal',
        'el_add_package',

        // Actions d'upload
        'el_upload_files',

        // Actions AI
        'el_generate_ai_description',
    );

    // Supprimer les hooks nopriv pour les actions sensibles
    foreach ($sensitive_actions as $action) {
        // On ne peut pas remove_action directement car la classe n'est pas accessible
        // On utilise une priorite tres basse pour intercepter avant l'execution
        add_action('wp_ajax_nopriv_' . $action, 'lehiboo_block_sensitive_ajax', 1);
    }
}

/**
 * Bloquer les requetes AJAX non authentifiees pour les actions sensibles
 */
function lehiboo_block_sensitive_ajax() {
    // Logger la tentative
    if (function_exists('error_log')) {
        error_log(sprintf(
            '[SECURITY] Blocked unauthenticated AJAX request: %s from IP: %s',
            isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'unknown',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }

    wp_send_json_error(array(
        'message' => __('Authentication required', 'eventlist'),
        'code' => 'authentication_required'
    ), 401);

    wp_die();
}

/**
 * Ajouter une verification supplementaire pour les actions critiques
 * Verifie que l'utilisateur a les capacites necessaires
 */
add_action('init', 'lehiboo_add_capability_checks', 2);

function lehiboo_add_capability_checks() {

    // Actions qui necessitent d'etre vendor
    $vendor_actions = array(
        'el_update_profile',
        'el_update_organisation',
        'el_save_edit_event',
        'el_pending_post',
        'el_publish_post',
        'el_export_csv',
    );

    foreach ($vendor_actions as $action) {
        add_action('wp_ajax_' . $action, 'lehiboo_check_vendor_capability', 1);
    }

    // Actions qui necessitent d'etre admin
    $admin_actions = array(
        'el_bulk_action',
        'el_update_role',
    );

    foreach ($admin_actions as $action) {
        add_action('wp_ajax_' . $action, 'lehiboo_check_admin_capability', 1);
    }
}

/**
 * Verifier si l'utilisateur est un vendor
 */
function lehiboo_check_vendor_capability() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in'), 401);
        wp_die();
    }

    $user = wp_get_current_user();
    // Rôles autorisés à gérer les événements
    $allowed_roles = array(
        'el_event_manager', // Rôle EventList (le vrai slug!)
        'administrator',    // Admin WordPress
        'editor',           // Éditeur WordPress
        'author',           // Auteur WordPress
        'vendor',           // Rôle vendor générique
        'contributor',      // Contributeur WordPress
    );

    // Permettre aussi si l'utilisateur a la capability edit_el_events (EventList)
    if (!array_intersect($allowed_roles, $user->roles) && !current_user_can('edit_el_events')) {
        error_log(sprintf(
            '[SECURITY] Unauthorized vendor action attempt by user %d (%s) with roles: %s',
            $user->ID,
            $user->user_login,
            implode(', ', $user->roles)
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
            '[SECURITY] Unauthorized admin action attempt from IP: %s',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        wp_send_json_error(array('message' => 'Admin access required'), 403);
        wp_die();
    }
}

/**
 * Rate limiting pour les actions AJAX
 */
add_action('init', 'lehiboo_ajax_rate_limiting');

function lehiboo_ajax_rate_limiting() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

    // Actions a limiter (10 requetes par minute)
    $rate_limited_actions = array(
        'el_process_checkout',
        'el_upload_files',
        'el_save_edit_event',
        'el_generate_ai_description',
    );

    if (in_array($action, $rate_limited_actions)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $transient_key = 'el_rate_limit_' . md5($ip . $action);
        $count = get_transient($transient_key);

        if ($count === false) {
            set_transient($transient_key, 1, 60); // 1 minute
        } elseif ($count >= 10) {
            error_log(sprintf('[SECURITY] Rate limit exceeded for %s from IP: %s', $action, $ip));
            wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
            wp_die();
        } else {
            set_transient($transient_key, $count + 1, 60);
        }
    }
}

/**
 * Valider et sanitizer les uploads de fichiers
 */
add_filter('el_upload_file_validation', 'lehiboo_validate_upload', 10, 2);

function lehiboo_validate_upload($file, $allowed_types) {
    // Verifier l'extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Extensions interdites
    $forbidden_extensions = array(
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'com',
        'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py',
        'htaccess', 'htpasswd'
    );

    if (in_array($ext, $forbidden_extensions)) {
        return new WP_Error('forbidden_extension', 'This file type is not allowed');
    }

    // Verifier le contenu du fichier (pas de code PHP)
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php|<\?=|<%|<script/i', $content)) {
        error_log(sprintf('[SECURITY] Blocked malicious file upload: %s', $file['name']));
        return new WP_Error('malicious_content', 'File contains potentially malicious content');
    }

    // Taille maximale (10 MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'File is too large (max 10MB)');
    }

    return $file;
}

/**
 * Logger toutes les actions AJAX pour audit
 */
if (WP_DEBUG) {
    add_action('wp_ajax_nopriv_*', 'lehiboo_log_ajax_requests', 0);
    add_action('wp_ajax_*', 'lehiboo_log_ajax_requests', 0);

    function lehiboo_log_ajax_requests() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'unknown';
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        error_log(sprintf(
            '[AJAX AUDIT] Action: %s | User: %d | IP: %s | Time: %s',
            $action,
            $user_id,
            $ip,
            current_time('mysql')
        ));
    }
}
