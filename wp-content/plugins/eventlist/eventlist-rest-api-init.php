<?php
/**
 * EventList REST API Initialization
 *
 * Instructions d'installation :
 *
 * 1. Copier ce fichier dans wp-content/plugins/eventlist/
 * 2. Ajouter cette ligne dans le fichier principal d'EventList (eventlist.php ou similaire) :
 *    require_once plugin_dir_path(__FILE__) . 'eventlist-rest-api-init.php';
 *
 * OU
 *
 * 3. Créer un fichier wp-content/mu-plugins/eventlist-api.php avec :
 *    <?php
 *    require_once WP_PLUGIN_DIR . '/eventlist/includes/class-eventlist-rest-api.php';
 *
 * @package EventList
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Charger la classe REST API
require_once plugin_dir_path(__FILE__) . 'includes/class-eventlist-rest-api.php';

/**
 * Logs pour debug (optionnel)
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('rest_api_init', function() {
        error_log('[EventList API] REST API routes registered');
    }, 999);
}

/**
 * Ajouter CORS headers pour permettre l'accès depuis le backend Node.js
 */
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        return $value;
    });
}, 15);
