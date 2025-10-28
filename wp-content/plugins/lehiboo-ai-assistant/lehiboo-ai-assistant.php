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

        // User conversations history table (pour utilisateurs connectés)
        $table_name = $wpdb->prefix . 'lehiboo_user_conversations';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            conversation_id varchar(100) NOT NULL,
            messages longtext NOT NULL,
            user_context text DEFAULT NULL,
            current_stage varchar(50) DEFAULT 'greeting',
            last_message_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY conversation_id (conversation_id),
            KEY last_message_at (last_message_at),
            KEY created_at (created_at)
        ) $charset_collate;";

        dbDelta($sql);

        // User favorites table (événements favoris)
        $table_name = $wpdb->prefix . 'lehiboo_user_favorites';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            event_id bigint(20) NOT NULL,
            added_from_conversation_id varchar(100) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_event (user_id, event_id),
            KEY user_id (user_id),
            KEY event_id (event_id),
            KEY created_at (created_at)
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

        // Google Fonts - Montserrat
        wp_enqueue_style(
            'lehiboo-ai-montserrat',
            'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
            array(),
            null
        );

        // CSS
        wp_enqueue_style(
            'lehiboo-ai-chat',
            LEHIBOO_AI_PLUGIN_URL . 'assets/css/chat-interface.css',
            array('lehiboo-ai-montserrat'),
            LEHIBOO_AI_VERSION
        );

        // CSS - Onboarding
        wp_enqueue_style(
            'lehiboo-ai-onboarding',
            LEHIBOO_AI_PLUGIN_URL . 'assets/css/chat-onboarding.css',
            array(),
            LEHIBOO_AI_VERSION
        );

        // JavaScript - Persistence & Onboarding (charger en premier)
        wp_enqueue_script(
            'lehiboo-ai-persistence',
            LEHIBOO_AI_PLUGIN_URL . 'assets/js/chat-persistence.js',
            array(),
            LEHIBOO_AI_VERSION,
            true
        );

        // JavaScript - Chat Interface (dépend de persistence)
        wp_enqueue_script(
            'lehiboo-ai-chat',
            LEHIBOO_AI_PLUGIN_URL . 'assets/js/chat-interface.js',
            array('lehiboo-ai-persistence'),
            LEHIBOO_AI_VERSION,
            true
        );

        $user_id = get_current_user_id();
        $is_logged_in = is_user_logged_in();

        // Localize script with config
        wp_localize_script('lehiboo-ai-chat', 'lehibooChatConfig', array(
            'apiEndpoint' => rest_url('lehiboo/v1/chat'),
            'apiBaseUrl' => rest_url('lehiboo/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => $user_id,
            'isLoggedIn' => $is_logged_in,
            'userDisplayName' => $is_logged_in ? wp_get_current_user()->display_name : '',
            'loginUrl' => wp_login_url(),
            'registerUrl' => wp_registration_url(),
            'debug' => get_option('lehiboo_ai_debug_mode') === 'yes',
            'maxMessageLength' => intval(get_option('lehiboo_ai_max_message_length', 2000)),
            'rateLimit' => array(
                'maxMessages' => intval(get_option('lehiboo_ai_rate_limit_messages', 10)),
                'timeWindow' => intval(get_option('lehiboo_ai_rate_limit_window', 60)) * 1000, // Convert to ms
            ),
            'persistence' => array(
                'enabled' => true,
                'autoSaveInterval' => 30000, // 30 secondes
            ),
            'onboarding' => array(
                'enabled' => !$is_logged_in,
                'triggerAfterMessages' => 3,
            ),
            'i18n' => array(
                'errorGeneric' => __('Une erreur est survenue. Veuillez réessayer.', 'lehiboo-ai-assistant'),
                'errorNetwork' => __('Impossible de se connecter au serveur.', 'lehiboo-ai-assistant'),
                'errorRateLimit' => __('Trop de messages envoyés. Veuillez patienter.', 'lehiboo-ai-assistant'),
                'errorMessageTooLong' => __('Message trop long.', 'lehiboo-ai-assistant'),
                'errorMessageEmpty' => __('Le message ne peut pas être vide.', 'lehiboo-ai-assistant'),
                'conversationSaved' => __('Conversation sauvegardée', 'lehiboo-ai-assistant'),
                'addedToFavorites' => __('Ajouté aux favoris', 'lehiboo-ai-assistant'),
                'removedFromFavorites' => __('Retiré des favoris', 'lehiboo-ai-assistant'),
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

        // Save conversation (authenticated users only)
        register_rest_route('lehiboo/v1', '/conversation/save', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_conversation'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Load conversation (authenticated users only)
        register_rest_route('lehiboo/v1', '/conversation/load', array(
            'methods' => 'GET',
            'callback' => array($this, 'load_conversation'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Load all user conversations (authenticated users only)
        register_rest_route('lehiboo/v1', '/conversations', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_conversations'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Delete conversation (authenticated users only)
        register_rest_route('lehiboo/v1', '/conversation/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_conversation'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Add to favorites (authenticated users only)
        register_rest_route('lehiboo/v1', '/favorites/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_favorite'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Remove from favorites (authenticated users only)
        register_rest_route('lehiboo/v1', '/favorites/remove', array(
            'methods' => 'POST',
            'callback' => array($this, 'remove_favorite'),
            'permission_callback' => 'is_user_logged_in',
        ));

        // Get user favorites (authenticated users only)
        register_rest_route('lehiboo/v1', '/favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_favorites'),
            'permission_callback' => 'is_user_logged_in',
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
     * Save conversation to database
     */
    public function save_conversation($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $conversation_id = $request->get_param('conversationId');
        $messages = $request->get_param('messages');
        $user_context = $request->get_param('userContext');
        $current_stage = $request->get_param('currentStage');

        if (!$conversation_id || !$messages) {
            return new WP_Error('missing_data', 'Missing required data', array('status' => 400));
        }

        $table = $wpdb->prefix . 'lehiboo_user_conversations';

        // Check if conversation already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND conversation_id = %s",
            $user_id,
            $conversation_id
        ));

        if ($existing) {
            // Update existing conversation
            $result = $wpdb->update(
                $table,
                array(
                    'messages' => wp_json_encode($messages),
                    'user_context' => wp_json_encode($user_context),
                    'current_stage' => $current_stage,
                    'last_message_at' => current_time('mysql'),
                ),
                array('id' => $existing->id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new conversation
            $result = $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'conversation_id' => $conversation_id,
                    'messages' => wp_json_encode($messages),
                    'user_context' => wp_json_encode($user_context),
                    'current_stage' => $current_stage,
                    'last_message_at' => current_time('mysql'),
                    'created_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save conversation', array('status' => 500));
        }

        return array(
            'success' => true,
            'message' => 'Conversation saved successfully',
        );
    }

    /**
     * Load conversation from database
     */
    public function load_conversation($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $conversation_id = $request->get_param('conversationId');

        if (!$conversation_id) {
            return new WP_Error('missing_data', 'Missing conversation ID', array('status' => 400));
        }

        $table = $wpdb->prefix . 'lehiboo_user_conversations';

        $conversation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND conversation_id = %s",
            $user_id,
            $conversation_id
        ));

        if (!$conversation) {
            return new WP_Error('not_found', 'Conversation not found', array('status' => 404));
        }

        return array(
            'success' => true,
            'conversation' => array(
                'conversationId' => $conversation->conversation_id,
                'messages' => json_decode($conversation->messages, true),
                'userContext' => json_decode($conversation->user_context, true),
                'currentStage' => $conversation->current_stage,
                'lastMessageAt' => $conversation->last_message_at,
                'createdAt' => $conversation->created_at,
            ),
        );
    }

    /**
     * Get all user conversations
     */
    public function get_user_conversations($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'lehiboo_user_conversations';

        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT id, conversation_id, current_stage, last_message_at, created_at
             FROM $table
             WHERE user_id = %d
             ORDER BY last_message_at DESC
             LIMIT 20",
            $user_id
        ));

        $result = array();
        foreach ($conversations as $conv) {
            $result[] = array(
                'id' => $conv->id,
                'conversationId' => $conv->conversation_id,
                'currentStage' => $conv->current_stage,
                'lastMessageAt' => $conv->last_message_at,
                'createdAt' => $conv->created_at,
            );
        }

        return array(
            'success' => true,
            'conversations' => $result,
        );
    }

    /**
     * Delete conversation
     */
    public function delete_conversation($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $conversation_id = $request['id'];

        $table = $wpdb->prefix . 'lehiboo_user_conversations';

        $result = $wpdb->delete(
            $table,
            array(
                'id' => $conversation_id,
                'user_id' => $user_id,
            ),
            array('%d', '%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete conversation', array('status' => 500));
        }

        return array(
            'success' => true,
            'message' => 'Conversation deleted successfully',
        );
    }

    /**
     * Add event to favorites
     */
    public function add_favorite($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $event_id = $request->get_param('eventId');
        $conversation_id = $request->get_param('conversationId');
        $notes = $request->get_param('notes');

        if (!$event_id) {
            return new WP_Error('missing_data', 'Missing event ID', array('status' => 400));
        }

        $table = $wpdb->prefix . 'lehiboo_user_favorites';

        $result = $wpdb->replace(
            $table,
            array(
                'user_id' => $user_id,
                'event_id' => $event_id,
                'added_from_conversation_id' => $conversation_id,
                'notes' => $notes,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to add favorite', array('status' => 500));
        }

        return array(
            'success' => true,
            'message' => 'Added to favorites',
        );
    }

    /**
     * Remove event from favorites
     */
    public function remove_favorite($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $event_id = $request->get_param('eventId');

        if (!$event_id) {
            return new WP_Error('missing_data', 'Missing event ID', array('status' => 400));
        }

        $table = $wpdb->prefix . 'lehiboo_user_favorites';

        $result = $wpdb->delete(
            $table,
            array(
                'user_id' => $user_id,
                'event_id' => $event_id,
            ),
            array('%d', '%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to remove favorite', array('status' => 500));
        }

        return array(
            'success' => true,
            'message' => 'Removed from favorites',
        );
    }

    /**
     * Get user favorites
     */
    public function get_favorites($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'lehiboo_user_favorites';

        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));

        $result = array();
        foreach ($favorites as $fav) {
            $result[] = array(
                'eventId' => $fav->event_id,
                'conversationId' => $fav->added_from_conversation_id,
                'notes' => $fav->notes,
                'createdAt' => $fav->created_at,
            );
        }

        return array(
            'success' => true,
            'favorites' => $result,
        );
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
