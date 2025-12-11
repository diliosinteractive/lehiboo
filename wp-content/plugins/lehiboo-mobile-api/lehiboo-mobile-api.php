<?php
/**
 * Plugin Name: LeHiboo Mobile API
 * Plugin URI: https://lehiboo.com
 * Description: API REST sécurisée pour l'application mobile LeHiboo (Flutter)
 * Version: 2.0.0
 * Author: LeHiboo
 * Author URI: https://lehiboo.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lehiboo-mobile-api
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('LMA_VERSION', '2.0.0');
define('LMA_PLUGIN_FILE', __FILE__);
define('LMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LMA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LMA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// JWT constants
define('LMA_JWT_ALGO', 'HS256');
define('LMA_ACCESS_TOKEN_EXPIRY', 7 * DAY_IN_SECONDS);  // 7 jours
define('LMA_REFRESH_TOKEN_EXPIRY', 30 * DAY_IN_SECONDS); // 30 jours

/**
 * Main Plugin Class
 */
final class LeHiboo_Mobile_API {

    /**
     * Single instance
     */
    private static $instance = null;

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
        $this->check_dependencies();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Check plugin dependencies
     */
    private function check_dependencies() {
        // Check if EventList plugin is active
        if (!class_exists('EventList')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>LeHiboo Mobile API</strong> requiert le plugin <strong>EventList</strong> pour fonctionner.';
                echo '</p></div>';
            });
            return;
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>LeHiboo Mobile API</strong> requiert PHP 7.4 ou supérieur.';
                echo '</p></div>';
            });
            return;
        }
    }

    /**
     * Include required files
     */
    private function includes() {
        // JWT Handler
        require_once LMA_PLUGIN_DIR . 'includes/auth/class-lma-jwt-handler.php';

        // Security & Helpers
        require_once LMA_PLUGIN_DIR . 'includes/security/class-lma-security.php';
        require_once LMA_PLUGIN_DIR . 'includes/security/class-lma-rate-limiter.php';
        require_once LMA_PLUGIN_DIR . 'includes/helpers/class-lma-validator.php';
        require_once LMA_PLUGIN_DIR . 'includes/helpers/class-lma-response.php';
        require_once LMA_PLUGIN_DIR . 'includes/helpers/class-lma-event-formatter.php';

        // Admin
        require_once LMA_PLUGIN_DIR . 'includes/admin/class-lma-taxonomy-image.php';

        // API Endpoints
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-auth.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-events.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-bookings.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-tickets.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-user.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-favorites.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-categories.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-partner.php';
        require_once LMA_PLUGIN_DIR . 'includes/api/class-lma-rest-posts.php';

        // Initialize taxonomy image support
        new LMA_Taxonomy_Image();
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

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // CORS Headers
        add_action('rest_api_init', array($this, 'add_cors_headers'), 15);

        // Admin menu
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Refresh tokens table
        $table_tokens = $wpdb->prefix . 'lma_refresh_tokens';
        $sql_tokens = "CREATE TABLE IF NOT EXISTS $table_tokens (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token_hash varchar(255) NOT NULL,
            device_info varchar(255) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_used_at datetime DEFAULT NULL,
            revoked tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY token_hash (token_hash),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        // Rate limiting table
        $table_rate = $wpdb->prefix . 'lma_rate_limits';
        $sql_rate = "CREATE TABLE IF NOT EXISTS $table_rate (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            identifier varchar(255) NOT NULL,
            endpoint varchar(100) NOT NULL,
            request_count int DEFAULT 1,
            window_start datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY identifier_endpoint (identifier, endpoint),
            KEY window_start (window_start)
        ) $charset_collate;";

        // API logs table (for debugging/analytics)
        $table_logs = $wpdb->prefix . 'lma_api_logs';
        $sql_logs = "CREATE TABLE IF NOT EXISTS $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            endpoint varchar(100) NOT NULL,
            method varchar(10) NOT NULL,
            status_code int NOT NULL,
            response_time float DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY endpoint (endpoint),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_tokens);
        dbDelta($sql_rate);
        dbDelta($sql_logs);
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'lma_enabled' => 'yes',
            'lma_jwt_secret' => wp_generate_password(64, true, true),
            'lma_rate_limit_enabled' => 'yes',
            'lma_log_requests' => 'no',
            'lma_cors_origins' => '*',
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
        load_plugin_textdomain('lehiboo-mobile-api', false, dirname(LMA_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Check if API is enabled
        if (get_option('lma_enabled') !== 'yes') {
            return;
        }

        // Initialize all REST controllers
        $controllers = array(
            new LMA_REST_Auth(),
            new LMA_REST_Events(),
            new LMA_REST_Bookings(),
            new LMA_REST_Tickets(),
            new LMA_REST_User(),
            new LMA_REST_Favorites(),
            new LMA_REST_Categories(),
            new LMA_REST_Partner(),
            new LMA_REST_Posts(),
        );

        foreach ($controllers as $controller) {
            $controller->register_routes();
        }
    }

    /**
     * Add CORS headers for mobile app
     */
    public function add_cors_headers() {
        // Remove default REST API CORS handling
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

        // Add custom CORS headers
        add_filter('rest_pre_serve_request', function($value) {
            $origin = get_option('lma_cors_origins', '*');

            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            // Handle preflight requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                status_header(200);
                exit();
            }

            return $value;
        });
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            __('LeHiboo Mobile API', 'lehiboo-mobile-api'),
            __('Mobile API', 'lehiboo-mobile-api'),
            'manage_options',
            'lehiboo-mobile-api',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        // Save settings
        if (isset($_POST['lma_save_settings']) && wp_verify_nonce($_POST['lma_nonce'], 'lma_settings')) {
            update_option('lma_enabled', isset($_POST['lma_enabled']) ? 'yes' : 'no');
            update_option('lma_rate_limit_enabled', isset($_POST['lma_rate_limit_enabled']) ? 'yes' : 'no');
            update_option('lma_log_requests', isset($_POST['lma_log_requests']) ? 'yes' : 'no');
            update_option('lma_cors_origins', sanitize_text_field($_POST['lma_cors_origins']));
            update_option('lma_ai_backend_url', esc_url_raw($_POST['lma_ai_backend_url']));
            update_option('lma_ai_backend_api_key', sanitize_text_field($_POST['lma_ai_backend_api_key']));

            echo '<div class="notice notice-success"><p>Parametres enregistres.</p></div>';
        }

        // Regenerate JWT secret
        if (isset($_POST['lma_regenerate_secret']) && wp_verify_nonce($_POST['lma_nonce'], 'lma_settings')) {
            update_option('lma_jwt_secret', wp_generate_password(64, true, true));
            echo '<div class="notice notice-warning"><p>Clé JWT régénérée. Tous les tokens existants sont maintenant invalides.</p></div>';
        }

        $enabled = get_option('lma_enabled', 'yes');
        $rate_limit = get_option('lma_rate_limit_enabled', 'yes');
        $log_requests = get_option('lma_log_requests', 'no');
        $cors_origins = get_option('lma_cors_origins', '*');
        $ai_backend_url = get_option('lma_ai_backend_url', 'https://preprod.lehiboo.com/api-planner');
        $ai_backend_api_key = get_option('lma_ai_backend_api_key', '');

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Configuration de l'API Mobile</h2>

                <form method="post" action="">
                    <?php wp_nonce_field('lma_settings', 'lma_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">API activée</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lma_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                                    Activer l'API Mobile LeHiboo
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Rate Limiting</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lma_rate_limit_enabled" value="yes" <?php checked($rate_limit, 'yes'); ?>>
                                    Activer la limitation de requêtes
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Logs des requêtes</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="lma_log_requests" value="yes" <?php checked($log_requests, 'yes'); ?>>
                                    Enregistrer les requêtes API (debug)
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">CORS Origins</th>
                            <td>
                                <input type="text" name="lma_cors_origins" value="<?php echo esc_attr($cors_origins); ?>" class="regular-text">
                                <p class="description">Origines autorisées (* pour tout autoriser)</p>
                            </td>
                        </tr>
                    </table>

                    <h3 style="margin-top: 30px;">Configuration Backend AI (Petit Boo)</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">URL Backend AI</th>
                            <td>
                                <input type="url" name="lma_ai_backend_url" value="<?php echo esc_attr($ai_backend_url); ?>" class="regular-text">
                                <p class="description">URL du serveur AI (ex: https://preprod.lehiboo.com/api-planner)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Cle API Backend AI</th>
                            <td>
                                <input type="text" name="lma_ai_backend_api_key" value="<?php echo esc_attr($ai_backend_api_key); ?>" class="regular-text" placeholder="lhb_xxx...">
                                <p class="description">Cle API pour authentifier les requetes vers le backend AI. Doit correspondre a API_KEY dans le .env du backend.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="lma_save_settings" class="button-primary" value="Enregistrer">
                        <input type="submit" name="lma_regenerate_secret" class="button-secondary" value="Régénérer la clé JWT" onclick="return confirm('Attention: Cela invalidera tous les tokens existants. Continuer?');">
                    </p>
                </form>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Informations API</h2>
                <table class="widefat" style="margin-top: 10px;">
                    <tr>
                        <th>Base URL</th>
                        <td><code><?php echo esc_url(rest_url('lehiboo/v2/')); ?></code></td>
                    </tr>
                    <tr>
                        <th>Version</th>
                        <td><?php echo LMA_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th>Documentation</th>
                        <td><a href="<?php echo LMA_PLUGIN_URL; ?>docs/API_MOBILE_LEHIBOO_V2.md" target="_blank">Voir la documentation</a></td>
                    </tr>
                </table>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Endpoints disponibles</h2>
                <table class="widefat striped" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Méthode</th>
                            <th>Endpoint</th>
                            <th>Description</th>
                            <th>Auth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="4"><strong>Authentification</strong></td></tr>
                        <tr><td>POST</td><td>/auth/register</td><td>Inscription client</td><td>-</td></tr>
                        <tr><td>POST</td><td>/auth/login</td><td>Connexion</td><td>-</td></tr>
                        <tr><td>POST</td><td>/auth/refresh</td><td>Rafraichir token</td><td>-</td></tr>
                        <tr><td>POST</td><td>/auth/forgot-password</td><td>Mot de passe oublie</td><td>-</td></tr>
                        <tr><td>POST</td><td>/auth/logout</td><td>Deconnexion</td><td>JWT</td></tr>
                        <tr><td>GET</td><td>/auth/ai-token</td><td>Token API Backend AI</td><td>JWT</td></tr>

                        <tr><td colspan="4"><strong>Événements</strong></td></tr>
                        <tr><td>GET</td><td>/events</td><td>Liste des événements</td><td>-</td></tr>
                        <tr><td>GET</td><td>/events/{id}</td><td>Détail événement</td><td>-</td></tr>
                        <tr><td>GET</td><td>/categories</td><td>Liste catégories</td><td>-</td></tr>

                        <tr><td colspan="4"><strong>Réservations (Client)</strong></td></tr>
                        <tr><td>POST</td><td>/bookings</td><td>Créer réservation</td><td>JWT</td></tr>
                        <tr><td>POST</td><td>/bookings/{id}/confirm</td><td>Confirmer paiement</td><td>JWT</td></tr>
                        <tr><td>GET</td><td>/me/bookings</td><td>Mes réservations</td><td>JWT</td></tr>
                        <tr><td>POST</td><td>/me/bookings/{id}/cancel</td><td>Annuler réservation</td><td>JWT</td></tr>

                        <tr><td colspan="4"><strong>Tickets (Client)</strong></td></tr>
                        <tr><td>GET</td><td>/me/tickets</td><td>Mes tickets</td><td>JWT</td></tr>
                        <tr><td>GET</td><td>/me/tickets/{id}</td><td>Détail ticket</td><td>JWT</td></tr>

                        <tr><td colspan="4"><strong>Partenaire</strong></td></tr>
                        <tr><td>GET</td><td>/partner/events</td><td>Mes événements</td><td>JWT (partner)</td></tr>
                        <tr><td>POST</td><td>/partner/scan</td><td>Scanner ticket</td><td>JWT (partner)</td></tr>
                        <tr><td>GET</td><td>/partner/stats</td><td>Statistiques</td><td>JWT (partner)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Get plugin version
     */
    public function get_version() {
        return LMA_VERSION;
    }
}

/**
 * Main instance
 */
function lma() {
    return LeHiboo_Mobile_API::instance();
}

// Initialize plugin
add_action('plugins_loaded', 'lma', 20);
