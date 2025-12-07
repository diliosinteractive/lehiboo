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
