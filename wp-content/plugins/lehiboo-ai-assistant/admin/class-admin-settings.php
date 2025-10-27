<?php
/**
 * Admin Settings Page
 * Interface admin pour configuration du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Admin_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Le Hiboo AI Assistant', 'lehiboo-ai-assistant'),
            __('AI Assistant', 'lehiboo-ai-assistant'),
            'manage_options',
            'lehiboo-ai-settings',
            array($this, 'render_settings_page'),
            'dashicons-format-chat',
            30
        );

        add_submenu_page(
            'lehiboo-ai-settings',
            __('Paramètres', 'lehiboo-ai-assistant'),
            __('Paramètres', 'lehiboo-ai-assistant'),
            'manage_options',
            'lehiboo-ai-settings'
        );

        add_submenu_page(
            'lehiboo-ai-settings',
            __('Analytics', 'lehiboo-ai-assistant'),
            __('Analytics', 'lehiboo-ai-assistant'),
            'manage_options',
            'lehiboo-ai-analytics',
            array($this, 'render_analytics_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General settings
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_enabled');
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_backend_url');
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_api_key');

        // Rate limiting
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_rate_limit_messages');
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_rate_limit_window');

        // Validation
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_max_message_length');

        // Debug
        register_setting('lehiboo_ai_settings', 'lehiboo_ai_debug_mode');

        // Sections
        add_settings_section(
            'lehiboo_ai_general',
            __('Paramètres Généraux', 'lehiboo-ai-assistant'),
            array($this, 'render_general_section'),
            'lehiboo_ai_settings'
        );

        add_settings_section(
            'lehiboo_ai_security',
            __('Sécurité & Rate Limiting', 'lehiboo-ai-assistant'),
            array($this, 'render_security_section'),
            'lehiboo_ai_settings'
        );

        // Fields - General
        add_settings_field(
            'lehiboo_ai_enabled',
            __('Activer l\'assistant IA', 'lehiboo-ai-assistant'),
            array($this, 'render_checkbox_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_general',
            array('name' => 'lehiboo_ai_enabled', 'description' => 'Activer ou désactiver le chat')
        );

        add_settings_field(
            'lehiboo_ai_backend_url',
            __('URL Backend API', 'lehiboo-ai-assistant'),
            array($this, 'render_text_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_general',
            array(
                'name' => 'lehiboo_ai_backend_url',
                'placeholder' => 'https://api.votredomaine.com/chat',
                'description' => 'URL du serveur Node.js backend (laissez vide pour mode démo)'
            )
        );

        add_settings_field(
            'lehiboo_ai_api_key',
            __('API Key (optionnelle)', 'lehiboo-ai-assistant'),
            array($this, 'render_password_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_general',
            array(
                'name' => 'lehiboo_ai_api_key',
                'description' => 'Clé API pour authentifier les requêtes vers le backend'
            )
        );

        // Fields - Security
        add_settings_field(
            'lehiboo_ai_rate_limit_messages',
            __('Messages max par fenêtre', 'lehiboo-ai-assistant'),
            array($this, 'render_number_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_security',
            array(
                'name' => 'lehiboo_ai_rate_limit_messages',
                'min' => 1,
                'max' => 100,
                'default' => 10,
                'description' => 'Nombre max de messages autorisés par fenêtre de temps'
            )
        );

        add_settings_field(
            'lehiboo_ai_rate_limit_window',
            __('Fenêtre de temps (secondes)', 'lehiboo-ai-assistant'),
            array($this, 'render_number_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_security',
            array(
                'name' => 'lehiboo_ai_rate_limit_window',
                'min' => 10,
                'max' => 600,
                'default' => 60,
                'description' => 'Durée de la fenêtre de rate limiting'
            )
        );

        add_settings_field(
            'lehiboo_ai_max_message_length',
            __('Longueur max message (caractères)', 'lehiboo-ai-assistant'),
            array($this, 'render_number_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_security',
            array(
                'name' => 'lehiboo_ai_max_message_length',
                'min' => 100,
                'max' => 5000,
                'default' => 2000,
                'description' => 'Longueur maximale d\'un message utilisateur'
            )
        );

        add_settings_field(
            'lehiboo_ai_debug_mode',
            __('Mode Debug', 'lehiboo-ai-assistant'),
            array($this, 'render_checkbox_field'),
            'lehiboo_ai_settings',
            'lehiboo_ai_security',
            array('name' => 'lehiboo_ai_debug_mode', 'description' => 'Activer logs détaillés (dev uniquement)')
        );
    }

    /**
     * Render sections
     */
    public function render_general_section() {
        echo '<p>' . __('Configuration de base de l\'assistant IA.', 'lehiboo-ai-assistant') . '</p>';
    }

    public function render_security_section() {
        echo '<p>' . __('Paramètres de sécurité et protection anti-spam.', 'lehiboo-ai-assistant') . '</p>';
    }

    /**
     * Render field types
     */
    public function render_checkbox_field($args) {
        $name = $args['name'];
        $value = get_option($name, 'no');
        $checked = ($value === 'yes') ? 'checked' : '';

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($name) . '" value="yes" ' . $checked . '>';
        echo ' ' . esc_html($args['description']);
        echo '</label>';
    }

    public function render_text_field($args) {
        $name = $args['name'];
        $value = get_option($name, '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

        echo '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        echo 'class="regular-text" placeholder="' . esc_attr($placeholder) . '">';

        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function render_password_field($args) {
        $name = $args['name'];
        $value = get_option($name, '');

        echo '<input type="password" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="regular-text">';

        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function render_number_field($args) {
        $name = $args['name'];
        $value = get_option($name, isset($args['default']) ? $args['default'] : '');
        $min = isset($args['min']) ? $args['min'] : 0;
        $max = isset($args['max']) ? $args['max'] : 999999;

        echo '<input type="number" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        echo 'min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" class="small-text">';

        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'lehiboo_ai_messages',
                'lehiboo_ai_message',
                __('Paramètres enregistrés.', 'lehiboo-ai-assistant'),
                'updated'
            );
        }

        settings_errors('lehiboo_ai_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="notice notice-info">
                <p>
                    <strong>📚 Documentation complète :</strong>
                    Consultez les fichiers
                    <code>START_HERE.md</code>,
                    <code>ARCHITECTURE.md</code> et
                    <code>IMPLEMENTATION_GUIDE.md</code>
                    dans le dossier du plugin pour toute la documentation.
                </p>
            </div>

            <?php
            // Show backend status
            $backend_url = get_option('lehiboo_ai_backend_url');
            if (empty($backend_url)) {
                echo '<div class="notice notice-warning">';
                echo '<p><strong>⚠️ Mode Démo</strong> : Le backend IA n\'est pas configuré. ';
                echo 'Le chat affichera des réponses de démonstration. ';
                echo 'Pour activer l\'IA, renseignez l\'URL du backend Node.js ci-dessous.</p>';
                echo '</div>';
            } else {
                // Test backend connection
                $test_response = wp_remote_get($backend_url . '/health', array('timeout' => 5));
                if (is_wp_error($test_response)) {
                    echo '<div class="notice notice-error">';
                    echo '<p><strong>❌ Backend inaccessible</strong> : Impossible de se connecter à ' . esc_html($backend_url) . '</p>';
                    echo '</div>';
                } else {
                    echo '<div class="notice notice-success">';
                    echo '<p><strong>✅ Backend connecté</strong> : ' . esc_html($backend_url) . '</p>';
                    echo '</div>';
                }
            }
            ?>

            <form action="options.php" method="post">
                <?php
                settings_fields('lehiboo_ai_settings');
                do_settings_sections('lehiboo_ai_settings');
                submit_button(__('Enregistrer les paramètres', 'lehiboo-ai-assistant'));
                ?>
            </form>

            <hr>

            <h2><?php _e('Prochaines étapes', 'lehiboo-ai-assistant'); ?></h2>
            <ol>
                <li>✅ Plugin activé - Interface chat disponible en frontend</li>
                <li><?php echo empty($backend_url) ? '🔲' : '✅'; ?> Configurer le backend Node.js (<a href="<?php echo plugins_url('IMPLEMENTATION_GUIDE.md', LEHIBOO_AI_PLUGIN_FILE); ?>">Guide</a>)</li>
                <li>🔲 Déployer le backend sur Railway/Vercel/VPS</li>
                <li>🔲 Configurer OpenRouter API Key dans le backend</li>
                <li>🔲 Tester le chat en frontend</li>
            </ol>

            <h2><?php _e('Liens utiles', 'lehiboo-ai-assistant'); ?></h2>
            <ul>
                <li><a href="<?php echo plugins_url('START_HERE.md', LEHIBOO_AI_PLUGIN_FILE); ?>" target="_blank">📄 START_HERE.md - Démarrage rapide</a></li>
                <li><a href="<?php echo plugins_url('IMPLEMENTATION_GUIDE.md', LEHIBOO_AI_PLUGIN_FILE); ?>" target="_blank">📖 IMPLEMENTATION_GUIDE.md - Guide d'implémentation</a></li>
                <li><a href="<?php echo plugins_url('ARCHITECTURE.md', LEHIBOO_AI_PLUGIN_FILE); ?>" target="_blank">🏗️ ARCHITECTURE.md - Architecture technique</a></li>
                <li><a href="<?php echo plugins_url('SECURITY.md', LEHIBOO_AI_PLUGIN_FILE); ?>" target="_blank">🔒 SECURITY.md - Guide sécurité</a></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'lehiboo_conversations';

        // Get stats
        $total_conversations = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $avg_messages = $wpdb->get_var("SELECT AVG(messages_count) FROM $table_name WHERE messages_count > 0");

        ?>
        <div class="wrap">
            <h1><?php _e('Analytics - Conversations IA', 'lehiboo-ai-assistant'); ?></h1>

            <div class="notice notice-info">
                <p><strong>ℹ️ Données anonymisées</strong> : Toutes les conversations sont anonymisées conformément au RGPD.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                <div style="background: #fff; padding: 20px; border-left: 4px solid #2271b1; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Total Conversations</h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo number_format($total_conversations); ?></p>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #00a32a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Messages Moyens</h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #00a32a;"><?php echo number_format($avg_messages, 1); ?></p>
                </div>
            </div>

            <h2><?php _e('Conversations Récentes', 'lehiboo-ai-assistant'); ?></h2>

            <?php
            $recent = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10");

            if ($recent) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr>';
                echo '<th>Date</th>';
                echo '<th>Tranche d\'âge</th>';
                echo '<th>Type groupe</th>';
                echo '<th>Budget</th>';
                echo '<th>Étape atteinte</th>';
                echo '<th>Messages</th>';
                echo '</tr></thead>';
                echo '<tbody>';

                foreach ($recent as $conv) {
                    echo '<tr>';
                    echo '<td>' . esc_html($conv->created_at) . '</td>';
                    echo '<td>' . esc_html($conv->age_range ?: '-') . '</td>';
                    echo '<td>' . esc_html($conv->group_type ?: '-') . '</td>';
                    echo '<td>' . esc_html($conv->budget_range ?: '-') . '</td>';
                    echo '<td>' . esc_html($conv->stage_reached ?: '-') . '</td>';
                    echo '<td>' . esc_html($conv->messages_count) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            } else {
                echo '<p>' . __('Aucune conversation pour le moment.', 'lehiboo-ai-assistant') . '</p>';
            }
            ?>
        </div>
        <?php
    }
}
