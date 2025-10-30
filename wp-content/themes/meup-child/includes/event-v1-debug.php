<?php
/**
 * DEBUG LeHiboo V1 - Vérification des templates et scripts
 *
 * Ce fichier ajoute des informations de debug dans la console navigateur
 * pour vérifier que les modifications V1 sont bien chargées
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Afficher les infos de debug dans la console
 */
add_action( 'wp_footer', 'lehiboo_v1_debug_info', 999 );
function lehiboo_v1_debug_info() {
    // Seulement pour les utilisateurs connectés
    if ( ! is_user_logged_in() ) {
        return;
    }

    global $post;

    // Vérifier si on est sur une page avec le shortcode
    $has_shortcode = false;
    if ( $post && has_shortcode( $post->post_content, 'el_member_account' ) ) {
        $has_shortcode = true;
    }

    ?>
    <script>
    console.group('🔍 LeHiboo V1 - Debug Info');
    console.log('📄 Page:', '<?php echo is_page() ? "Page" : "Non-page"; ?>');
    console.log('📋 Shortcode [el_member_account]:', <?php echo $has_shortcode ? 'true' : 'false'; ?>);
    console.log('👤 User logged in:', <?php echo is_user_logged_in() ? 'true' : 'false'; ?>);
    console.log('🎨 Theme:', '<?php echo get_stylesheet(); ?>');
    console.log('📁 Templates override directory:', '<?php echo get_stylesheet_directory() . "/eventlist/templates/vendor/"; ?>');

    // Vérifier l'existence des fichiers
    <?php
    $files_to_check = [
        'calendar' => get_stylesheet_directory() . '/eventlist/templates/vendor/__edit-event-calendar.php',
        'ticket' => get_stylesheet_directory() . '/eventlist/templates/vendor/__edit-event-ticket.php',
        'localisation' => get_stylesheet_directory() . '/eventlist/templates/vendor/__edit-event-localisation.php',
        'config' => get_stylesheet_directory() . '/includes/event-v1-config.php',
        'js_ux' => get_stylesheet_directory() . '/assets/js/vendor-ticket-ux-improvements.js',
    ];

    foreach ( $files_to_check as $name => $path ) {
        $exists = file_exists( $path );
        echo "console.log('✅ Template {$name}:', " . ($exists ? 'true' : 'false') . ");\n";
    }
    ?>

    // Vérifier les filtres actifs
    console.log('🔧 Filtres WordPress:');
    console.log('  - el_show_yearly_recurrence:', <?php echo apply_filters( 'el_show_yearly_recurrence', true ) ? 'true' : 'false'; ?>);
    console.log('  - el_show_ticket_type_no_seat:', <?php echo apply_filters( 'el_show_ticket_type_no_seat', true ) ? 'true' : 'false'; ?>);
    console.log('  - el_show_ticket_paid_ticketing:', <?php echo apply_filters( 'el_show_ticket_paid_ticketing', true ) ? 'true' : 'false'; ?>);

    console.groupEnd();
    </script>
    <?php
}

/**
 * Afficher un badge de debug dans l'admin bar
 */
add_action( 'admin_bar_menu', 'lehiboo_v1_debug_admin_bar', 999 );
function lehiboo_v1_debug_admin_bar( $wp_admin_bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $config_loaded = class_exists( 'LeHiboo_OTP' ) || function_exists( 'lehiboo_validation_messages' );

    $wp_admin_bar->add_node( array(
        'id'    => 'lehiboo-v1-debug',
        'title' => $config_loaded ? '✅ LeHiboo V1' : '❌ LeHiboo V1',
        'href'  => '#',
        'meta'  => array(
            'title' => $config_loaded ? 'Configuration V1 chargée' : 'Configuration V1 NON chargée',
        ),
    ) );

    // Sous-menus
    $wp_admin_bar->add_node( array(
        'parent' => 'lehiboo-v1-debug',
        'id'     => 'lehiboo-clear-cache',
        'title'  => '🔄 Vider le cache',
        'href'   => add_query_arg( 'lehiboo_clear_cache', '1' ),
    ) );

    $wp_admin_bar->add_node( array(
        'parent' => 'lehiboo-v1-debug',
        'id'     => 'lehiboo-docs',
        'title'  => '📚 Documentation',
        'href'   => get_stylesheet_directory_uri() . '/AMELIORATIONS-V1.md',
        'meta'   => array( 'target' => '_blank' ),
    ) );
}

/**
 * Action pour vider le cache
 */
add_action( 'init', 'lehiboo_handle_clear_cache' );
function lehiboo_handle_clear_cache() {
    if ( ! isset( $_GET['lehiboo_clear_cache'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Vider tous les caches WordPress
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }

    // Vider le cache de rewrite rules
    flush_rewrite_rules();

    // Rediriger avec message
    wp_redirect( remove_query_arg( 'lehiboo_clear_cache' ) );
    exit;
}
