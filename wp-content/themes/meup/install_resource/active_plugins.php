<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @see http://tgmpluginactivation.com/configuration/ for detailed documentation.
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.6.1 for parent theme omytheme for publication on ThemeForest
 * @author     Thomas Griffin, Gary Jones, Juliette Reinders Folmer
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/TGMPA/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 *
 * Depending on your implementation, you may want to change the include call:
 *
 * Parent Theme:
 * require_once get_template_directory() . '/path/to/class-tgm-plugin-activation.php';
 *
 * Child Theme:
 * require_once get_stylesheet_directory() . '/path/to/class-tgm-plugin-activation.php';
 */
require_once (MEUP_URL.'/inc/vendor/class-tgm-plugin-activation.php');

// Register required plugins
add_action( 'tgmpa_register', 'meup_register_required_plugins' );
function meup_register_required_plugins() {
    $plugins = array(
        array(
            'name'                     => esc_html__('Elementor','meup'),
            'slug'                     => 'elementor',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Contact Form 7','meup'),
            'slug'                     => 'contact-form-7',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Widget importer exporter','meup'),
            'slug'                     => 'widget-importer-exporter',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Metabox','meup'),
            'slug'                     => 'cmb2',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Woocommerce','meup'),
            'slug'                     => 'woocommerce',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Mailchimp for wp','meup'),
            'slug'                     => 'mailchimp-for-wp',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('One click demo import','meup'),
            'slug'                     => 'one-click-demo-import',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('Recent post widget','meup'),
            'slug'                     => 'recent-posts-widget-with-thumbnails',
            'required'                 => true,
        ),
        array(
            'name'                     => esc_html__('OvaTheme Framework','meup'),
            'slug'                     => 'ova-framework',
            'required'                 => true,
            'source'                   => get_template_directory() . '/install_resource/plugins/ova-framework.zip',
            'version'                   => '1.2.6'
        ),
        array(
            'name'                     => esc_html__('Event List','meup'),
            'slug'                     => 'eventlist',
            'required'                 => true,
            'source'                   => get_template_directory() . '/install_resource/plugins/eventlist.zip',
            'version'                   => '2.0.6'
        ),
        array(
            'name'                     => esc_html__('Image Map Pro WordPress','meup'),
            'slug'                     => 'image-map-pro-wordpress',
            'required'                 => true,
            'source'                   => get_template_directory() . '/install_resource/plugins/image-map-pro-wordpress.zip',
            'version'                   => '6.0.35'
        ),
        array(
            'name'                     => esc_html__('Ovatheme Login','meup'),
            'slug'                     => 'ova-login',
            'required'                 => true,
            'source'                   => get_template_directory() . '/install_resource/plugins/ova-login.zip',
            'version'                   => '1.2.6'
        ),
    );

    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * TGMPA will start providing localized text strings soon. If you already have translations of our standard
     * strings available, please help us make TGMPA even better by giving us access to these translations or by
     * sending in a pull-request with .po file(s) with the translations.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = array(
        'id'           => 'meup',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.        
    );

    tgmpa( $plugins, $config );
}

// After import
add_action( 'ocdi/after_import', function() {
    // Assign menus to their locations.
    $primary = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
    if ( !is_wp_error( $primary ) ) {
        set_theme_mod( 'nav_menu_locations', [
            'primary' => $primary->term_id
        ]);
    }

    // Assign front page and posts page (blog page).
    $front_page_id = meup_get_page_by_title( 'Home 1' );
    $blog_page_id  = meup_get_page_by_title( 'Blog' );

    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $front_page_id->ID );
    update_option( 'page_for_posts', $blog_page_id->ID );
    update_option( 'users_can_register', 1 );

    // Event settings
    meup_update_event_setting_after_import();

    // Login settings
    meup_update_login_setting_after_import();

    // Image Map Pro import
    meup_image_map_pro_import();

    // Update customize
    meup_replace_url_in_customize();

    // Replace URLs
    meup_replace_url_after_import();

    // Replace image URLs
    $upload_dir = wp_get_upload_dir();
    $base_url   = $upload_dir['baseurl'];
    meup_replace_url_after_import( $base_url, 'https://ovatheme.nyc3.cdn.digitaloceanspaces.com/meup' );
});

// Import files
add_filter( 'ocdi/import_files', function() {
    return array(
        array(
            'import_file_name'             => 'Demo Import',
            'categories'                   => array( 'Category 1', 'Category 2' ),
            'local_import_file'            => trailingslashit( get_template_directory() ) . 'install_resource/demo_import/demo-content.xml',
            'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'install_resource/demo_import/widgets.wie',
            'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'install_resource/demo_import/customize.dat',
        )
    );
});

// Get Page by Title
if ( !function_exists( 'meup_get_page_by_title' ) ) {
    function meup_get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
        global $wpdb;

        if ( is_array( $post_type ) ) {
            $post_type           = esc_sql( $post_type );
            $post_type_in_string = "'" . implode( "','", $post_type ) . "'";
            $sql                 = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_title = %s
                AND post_type IN ($post_type_in_string)
            ",
                $page_title
            );
        } else {
            $sql = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_title = %s
                AND post_type = %s
            ",
                $page_title,
                $post_type
            );
        }

        $page = $wpdb->get_var( $sql );

        if ( $page ) {
            return get_post( $page, $output );
        }

        return null;
    }
}

// Replace url in customize
if ( !function_exists( 'meup_replace_url_in_customize' ) ) {
    function meup_replace_url_in_customize() {
        $demo_url = apply_filters( 'meup_demo_url', 'https://demo.ovathemewp.com/meup' );

        // Get theme mods
        $theme_mods = get_theme_mods();

        if ( !empty( $theme_mods ) ) {
            foreach ( $theme_mods as $key => $val ) {
                if ( is_string( $val ) && str_contains( $val, $demo_url ) ) {
                    $val = str_replace( $demo_url, get_site_url(), $val );

                    // Update theme mod
                    set_theme_mod( $key, $val );
                }
            }
        }
    }
}

// Replace url after import demo data
if ( !function_exists('meup_replace_url_after_import') ) {
    function meup_replace_url_after_import( $site_url = '', $demo_url = '' ) {
        global $wpdb;

        // Site URL
        if ( !$site_url ) {
            $site_url = apply_filters( 'meup_site_url', get_site_url() );
        }

        // Demo URL
        if ( !$demo_url ) {
            $demo_url = apply_filters( 'meup_demo_url', 'https://demo.ovathemewp.com/meup' );
        }

        // Replace in option value
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->options} " .
                "SET `option_value` = REPLACE(`option_value`, %s, %s);",
                $demo_url,
                $site_url
            )
        );

        // Replace in posts
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} " .
                "SET `post_content` = REPLACE(`post_content`, %s, %s), `guid` = REPLACE(`guid`, %s, %s);",
                $demo_url,
                $site_url,
                $demo_url,
                $site_url
            )
        );

        // Replace in meta value
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} " .
                "SET `meta_value` = REPLACE(`meta_value`, %s, %s) " .
                "WHERE `meta_key` <> '_elementor_data';",
                $demo_url,
                $site_url
            )
        );

        // Elementor Data
        $escaped_from       = str_replace( '/', '\\/', $demo_url );
        $escaped_to         = str_replace( '/', '\\/', $site_url );
        $meta_value_like    = '[%'; // meta_value LIKE '[%' are json formatted

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} " .
                'SET `meta_value` = REPLACE(`meta_value`, %s, %s) ' .
                "WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE %s;",
                $escaped_from,
                $escaped_to,
                $meta_value_like
            )
        );
    }
}

// Get woo pages
if ( !function_exists( 'meup_get_woo_pages' ) ) {
    function meup_get_woo_pages(){
        global $wpdb;

        $sql = $wpdb->prepare( "
            SELECT ID, post_title FROM $wpdb->posts
            WHERE $wpdb->posts.post_type = %s AND $wpdb->posts.post_status = %s
            GROUP BY $wpdb->posts.post_name
            ", 'product', 'publish' );
        $pages = $wpdb->get_results( $sql );

        return $pages;
    }
}

// Event settings
if ( !function_exists( 'meup_update_event_setting_after_import' ) ) {
    function meup_update_event_setting_after_import() {
        $ova_eventlist      = get_option( 'ova_eventlist') ? get_option( 'ova_eventlist') : array();
        $pages              = get_pages();
        $woo_pages          = meup_get_woo_pages();
        // General
        foreach ( $pages as $page ) {

            $elementor_data = get_post_meta( $page->ID, '_elementor_data', true );

            if ( str_contains( $page->post_content, '[el_cart/]' ) ) {
                $ova_eventlist['general']['cart_page_id'] = $page->ID;
            } elseif ( str_contains( $elementor_data, 'el_search_form' ) && str_contains( $elementor_data, 'el_search_result' ) ) {
                $ova_eventlist['general']['search_result_page_id'] = $page->ID;
            } elseif ( str_contains( $page->post_content, '[el_member_account/]' ) ) {
                $ova_eventlist['general']['myaccount_page_id'] = $page->ID;
            } elseif ( $page->post_title == "Thank you" ) {
                $ova_eventlist['general']['thanks_page_id'] = $page->ID;
            } elseif ( $page->post_title == "term and condition" ) {
                $ova_eventlist['checkout']['terms_condition_page'] = $page->ID;
            }

        }
        // Package
        $ova_eventlist['package']['enable_package']                 = "yes";
        $ova_eventlist['package']['package']                        = "default";
        $ova_eventlist['package']['allow_active_package_by_order']  = array( 'wc-completed', 'wc-processing' );
        foreach ( $woo_pages as $page ) {
            if ( $page->post_title == "Payment Package" ) {
                $ova_eventlist['package']['product_payment_package'] = $page->ID;
            } elseif ( $page->post_title == "Booking Event" ) {
                $ova_eventlist['checkout']['temp_product_page'] = $page->ID;
            }
        }
        // Checkout
        $ova_eventlist['checkout']['woo_active']                = "yes";
        $ova_eventlist['checkout']['allow_add_ticket_by_order'] = array( 'wc-completed', 'wc-processing' );
        $ova_eventlist['checkout']['free_active']               = "yes";
        $ova_eventlist['checkout']['free_send_ticket']          = "yes";

        update_option( 'ova_eventlist', $ova_eventlist );
    }
}

// Login settings
if ( !function_exists( 'meup_update_login_setting_after_import' ) ) {
    function meup_update_login_setting_after_import() {
        $ova_login_setting  = get_option( 'ovalg_options' ) ? get_option( 'ovalg_options' ) : array();
        $pages              = get_pages();
        foreach ($pages as $page) {
            if ( str_contains( $page->post_content, '[custom-login-form]') ) {
                $ova_login_setting['login_page'] = $page->ID;
            } elseif ( str_contains( $page->post_content, '[el_member_account/]') ) {
                $ova_login_setting['login_success_page'] = $page->ID;
            } elseif ( str_contains( $page->post_content, '[custom-register-form]') ) {
                $ova_login_setting['register_page'] = $page->ID;
            } elseif ( str_contains( $page->post_content, '[custom-password-lost-form]') ) {
                $ova_login_setting['forgot_password_page'] = $page->ID;
            } elseif ( str_contains( $page->post_content, '[custom-password-reset-form]') ) {
                $ova_login_setting['pick_new_password_page'] = $page->ID;
            } elseif ( $page->post_title == "term and condition" ) {
                $ova_login_setting['term_condition_page_id'] = $page->ID;
            }
        }
        update_option( 'ovalg_options', $ova_login_setting );
    }
}

// Image Map Pro import
if ( !function_exists( 'meup_image_map_pro_import' ) ) {
    function meup_image_map_pro_import() {
        global $wpdb;
        $ovatheme_url   = 'https://demo.ovathemewp.com/meup/';
        $site_url       = trailingslashit( get_site_url() );
        $json_file      = file_get_contents( MEUP_URL.'/install_resource/demo_import/image_map_pro.json' );
        $json_data      = str_replace($ovatheme_url, $site_url, $json_file);
        $json_data      = json_decode( $json_data, true );
        $table_name     = $wpdb->prefix.'image_map_pro_projects';

        if ( ! empty( $json_data ) ) {

            $id = isset( $json_data['id'] ) ? sanitize_text_field( $json_data['id'] ) : '';
            $name = isset( $json_data['general']['name'] ) ? sanitize_text_field( $json_data['general']['name'] ) : '';
            $shortcode = isset( $json_data['general']['shortcode'] ) ? sanitize_text_field( $json_data['general']['shortcode'] ) : '';

            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id = '{$id}'");
            if ( ! empty( $result ) && count( $result ) > 0 ) {
                return;
            }

            $wpdb->insert(
                $table_name,
                array(
                    'id' => $id,
                    'name' => $name,
                    'shortcode' => $shortcode,
                    'json' => json_encode( $json_data ),
                )
            );

        }
    }
}
