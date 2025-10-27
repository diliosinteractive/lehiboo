<?php
/**
 * Plugin Name: Le Hiboo AI Assistant
 * Plugin URI: https://lehiboo.com
 * Description: Assistant conversationnel IA pour aider les utilisateurs à trouver l'activité parfaite
 * Version: 1.0.0
 * Author: Le Hiboo
 * Author URI: https://lehiboo.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lehiboo-ai-assistant
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('LEHIBOO_AI_VERSION', '1.0.0');
define('LEHIBOO_AI_PLUGIN_FILE', __FILE__);
define('LEHIBOO_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEHIBOO_AI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEHIBOO_AI_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Lehiboo_AI_Assistant {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Security handler
     */
    public $security;

    /**
     * Rate limiter
     */
    public $rate_limiter;

    /**
     * Chat handler
     */
    public $chat_handler;

    /**
     * Get instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once LEHIBOO_AI_PLUGIN_DIR . 'includes/class-security.php';
        require_once LEHIBOO_AI_PLUGIN_DIR . 'includes/class-rate-limiter.php';
        require_once LEHIBOO_AI_PLUGIN_DIR . 'includes/class-chat-handler.php';
        require_once LEHIBOO_AI_PLUGIN_DIR . 'includes/class-prompt-manager.php';
        require_once LEHIBOO_AI_PLUGIN_DIR . 'includes/class-age-validator.php';

        // API endpoints
        require_once LEHIBOO_AI_PLUGIN_DIR . 'api/chat-endpoint.php';

        // Initialize core components
        $this->security = new Lehiboo_AI_Security();
        $this->rate_limiter = new Lehiboo_AI_Rate_Limiter();
        $this->chat_handler = new Lehiboo_AI_Chat_Handler();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Init
        add_action('init', array($this, 'init'), 0);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Security headers
        add_action('send_headers', array($this->security, 'add_security_headers'));

        // Admin menu (if needed)
        if (is_admin()) {
            require_once LEHIBOO_AI_PLUGIN_DIR . 'admin/class-admin-settings.php';
            new Lehiboo_AI_Admin_Settings();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        $this->set_default_options();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Conversations analytics table (anonymized)
        $table_name = $wpdb->prefix . 'lehiboo_conversations';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            conversation_id varchar(100) NOT NULL,
            age_range varchar(20) DEFAULT NULL,
            group_type varchar(50) DEFAULT NULL,
            budget_range varchar(50) DEFAULT NULL,
            interests text DEFAULT NULL,
            stage_reached varchar(50) DEFAULT NULL,
            outcome varchar(50) DEFAULT NULL,
            duration_seconds int DEFAULT NULL,
            messages_count int DEFAULT NULL,
            events_shown int DEFAULT NULL,
            events_clicked int DEFAULT NULL,
            bookings_initiated int DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY conversation_id (conversation_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Rate limiting table
        $table_name = $wpdb->prefix . 'lehiboo_rate_limits';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            identifier varchar(255) NOT NULL,
            request_count int DEFAULT 1,
            window_start datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY identifier (identifier),
            KEY window_start (window_start)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'lehiboo_ai_enabled' => 'yes',
            'lehiboo_ai_rate_limit_messages' => 10,
            'lehiboo_ai_rate_limit_window' => 60,
            'lehiboo_ai_max_message_length' => 2000,
            'lehiboo_ai_debug_mode' => 'no',
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('lehiboo-ai-assistant', false, dirname(LEHIBOO_AI_PLUGIN_BASENAME) . '/languages');

        // Check if plugin is enabled
        if (get_option('lehiboo_ai_enabled') !== 'yes') {
            return;
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Check if enabled
        if (get_option('lehiboo_ai_enabled') !== 'yes') {
            return;
        }

        // CSS
        wp_enqueue_style(
            'lehiboo-ai-chat',
            LEHIBOO_AI_PLUGIN_URL . 'assets/css/chat-interface.css',
            array(),
            LEHIBOO_AI_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'lehiboo-ai-chat',
            LEHIBOO_AI_PLUGIN_URL . 'assets/js/chat-interface.js',
            array(),
            LEHIBOO_AI_VERSION,
            true
        );

        // Localize script with config
        wp_localize_script('lehiboo-ai-chat', 'lehibooChatConfig', array(
            'apiEndpoint' => rest_url('lehiboo/v1/chat'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
            'debug' => get_option('lehiboo_ai_debug_mode') === 'yes',
            'maxMessageLength' => intval(get_option('lehiboo_ai_max_message_length', 2000)),
            'rateLimit' => array(
                'maxMessages' => intval(get_option('lehiboo_ai_rate_limit_messages', 10)),
                'timeWindow' => intval(get_option('lehiboo_ai_rate_limit_window', 60)) * 1000, // Convert to ms
            ),
            'i18n' => array(
                'errorGeneric' => __('Une erreur est survenue. Veuillez réessayer.', 'lehiboo-ai-assistant'),
                'errorNetwork' => __('Impossible de se connecter au serveur.', 'lehiboo-ai-assistant'),
                'errorRateLimit' => __('Trop de messages envoyés. Veuillez patienter.', 'lehiboo-ai-assistant'),
                'errorMessageTooLong' => __('Message trop long.', 'lehiboo-ai-assistant'),
                'errorMessageEmpty' => __('Le message ne peut pas être vide.', 'lehiboo-ai-assistant'),
            ),
        ));
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Chat endpoint
        register_rest_route('lehiboo/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this->chat_handler, 'handle_chat_request'),
            'permission_callback' => array($this->security, 'check_chat_permission'),
        ));

        // Health check endpoint
        register_rest_route('lehiboo/v1', '/health', array(
            'methods' => 'GET',
            'callback' => function() {
                return array(
                    'status' => 'ok',
                    'version' => LEHIBOO_AI_VERSION,
                    'timestamp' => current_time('mysql'),
                );
            },
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Get plugin version
     */
    public function get_version() {
        return LEHIBOO_AI_VERSION;
    }
}

/**
 * Main instance
 */
function lehiboo_ai() {
    return Lehiboo_AI_Assistant::instance();
}

// Initialize plugin
lehiboo_ai();
