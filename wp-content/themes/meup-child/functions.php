<?php
/**
 * Setup meup Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be filed in the /languages/ directory.
 */
function meup_child_theme_setup() {
	load_child_theme_textdomain( 'meup-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'meup_child_theme_setup' );


// Add Code is here.

// Add Parent Style
add_action( 'wp_enqueue_scripts', 'meup_child_scripts', 100 );
function meup_child_scripts() {
    wp_enqueue_style( 'meup-parent-style', get_template_directory_uri(). '/style.css' );
}

// ========================================
// TAXONOMIES PERSONNALISÉES
// ========================================
// Pour ajouter une nouvelle taxonomie, ajoutez simplement une ligne add_filter ci-dessous
// Le nombre sera automatiquement détecté et synchronisé

add_filter( 'register_taxonomy_el_1', function ($params){ return array( 'slug' => 'eljob', 'name' => esc_html__( 'Job', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eltime', 'name' => esc_html__( 'Time', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_3', function ($params){ return array( 'slug' => 'elpublic', 'name' => esc_html__( 'Public', 'meup-child' ) ); } );
// Ajoutez ici d'autres taxonomies si nécessaire :
// add_filter( 'register_taxonomy_el_4', function ($params){ return array( 'slug' => 'elniveau', 'name' => esc_html__( 'Niveau', 'meup-child' ) ); } );

// Synchronisation automatique du nombre de taxonomies
add_action( 'admin_init', 'meup_child_sync_taxonomy_count' );

function meup_child_sync_taxonomy_count() {
    global $wp_filter;

    // Compter les filtres register_taxonomy_el_* déclarés
    $max_taxonomy = 0;
    if ( isset( $wp_filter ) && is_array( $wp_filter ) ) {
        foreach ( array_keys( $wp_filter ) as $tag ) {
            if ( preg_match( '/^register_taxonomy_el_(\d+)$/', $tag, $matches ) ) {
                $num = (int) $matches[1];
                if ( $num > $max_taxonomy ) {
                    $max_taxonomy = $num;
                }
            }
        }
    }

    // Mettre à jour l'option en base de données si nécessaire
    if ( $max_taxonomy > 0 ) {
        $options = get_option( 'ova_eventlist_general', array() );

        // Mettre à jour uniquement si la valeur a changé
        if ( ! isset( $options['el_total_taxonomy'] ) || $options['el_total_taxonomy'] != $max_taxonomy ) {
            $options['el_total_taxonomy'] = $max_taxonomy;
            update_option( 'ova_eventlist_general', $options );
        }
    }
}