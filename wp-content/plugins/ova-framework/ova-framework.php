<?php
/**
Plugin Name: OvaTheme Framework
Plugin URI: https://themeforest.net/user/ovatheme/portfolio
Description: A plugin to create custom Post Type, Shortcode, Elementor
Version:  1.2.6
Author: Ovatheme
Author URI: https://themeforest.net/user/ovatheme
License:  GPL2
Text Domain: ova-framework
Domain Path: /languages 
*/

class OvaFramework {

	/**
     * OvaFramework constructor.
     */
    public function __construct() {
        $this->setup_cons();
        $this->load_textdomain();
        $this->supports();
        $this->add_scripts();
        $this->includes();
    }

    public function setup_cons() {
    	if (!defined('OVA_FRAMEWORK_VERSION')) {
            define('OVA_FRAMEWORK_VERSION', '1.0');
        }
        if (!defined('OVA_PLUGIN_PATH')) {
        	define( 'OVA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );	
        }
        if (!defined('OVA_PLUGIN_URI')) {
        	define( 'OVA_PLUGIN_URI', plugin_dir_url( __FILE__ ) );	
        }
        
    }

    public function load_textdomain() {
    	/* Load Text Domain */
		load_plugin_textdomain( 'ova-framework', false, basename( dirname( __FILE__ ) ) .'/languages' ); 
    }

    public function supports() {
        /* Make Elementors */
        if ( did_action( 'elementor/loaded' ) ) {
            include OVA_PLUGIN_PATH.'ova-elementor/class-ova-register-elementor.php';
        }

        /* Metabox Cm2 */
        if ( defined( 'CMB2_LOADED' ) ) {
            include OVA_PLUGIN_PATH.'metabox/class-metabox.php';
        }
        
        /* Custom Post Type */
        include OVA_PLUGIN_PATH.'custom-post-type/init.php';
    }

    public function add_scripts() {
        /* Admin JS */
        add_action( 'admin_enqueue_scripts', [ $this, 'ova_script_admin' ] );
        
        /* Add CSS Frontend */
        add_action( 'wp_enqueue_scripts', [ $this, 'ova_enqueue_style_elementor' ], 11 );

        // After register styles
        add_action( 'elementor/frontend/after_register_styles', [ $this, 'ova_enqueue_styles' ] );

        /* Add JS for Elementor */
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'ova_enqueue_scripts_elementor' ] );

        add_action( 'wp_print_footer_scripts', [ $this, 'ovatheme_enqueue_footer_scripts' ] );

        // load icons
        add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'ovatheme_icons_filters_new' ], 9999999, 1 );
    }

    // Add JS, CSS in Backend
    public function ova_script_admin(){
        wp_enqueue_script( 'script-admin', OVA_PLUGIN_URI.'assets/js/script-admin.js', [ 'jquery' ],false, true );
    }

    /**
     * Widget social icons style
     */
    public function ova_enqueue_styles() {
        // Widget social icons
        if ( defined( 'ELEMENTOR_ASSETS_PATH' ) && defined( 'ELEMENTOR_ASSETS_URL' ) ) {
            if ( file_exists( ELEMENTOR_ASSETS_PATH . 'css/widget-social-icons.min.css' ) ) {
                wp_enqueue_style( 'widget-social-icons', ELEMENTOR_ASSETS_URL . 'css/widget-social-icons.min.css', [], ELEMENTOR_VERSION );
            }
        }
    }
    
    // Add CSS
    public function ova_enqueue_style_elementor() {
        wp_enqueue_style( 'meupicon', OVA_PLUGIN_URI.'assets/libs/meupicon/font/meupicon.css', [], null );
        // Add Css
        wp_enqueue_style('owl-carousel', OVA_PLUGIN_URI.'assets/libs/owl-carousel/assets/owl.carousel.min.css', array(), null );

        if ( did_action( 'elementor/loaded' ) && file_exists( ABSPATH.'/wp-content/plugins/elementor/assets/css/frontend.min.css' ) ) {
            wp_enqueue_style( 'elementor-frontend', plugins_url('/elementor/assets/css/frontend.min.css'), array(), null );
        }
        wp_enqueue_style( 'style-elementor', OVA_PLUGIN_URI.'assets/css/style-elementor.css', [], null );
    }

    // Add JS
    public function ova_enqueue_scripts_elementor() {
        wp_register_script( 'owl-carousel', OVA_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', [ 'jquery' ], false, true );
        wp_register_script( 'script-elementor', OVA_PLUGIN_URI. 'assets/js/script-elementor.js', [ 'jquery' ], false, true );
    }

    public function ovatheme_enqueue_footer_scripts(){
        // Font Icon
        wp_enqueue_style( 'meupicon', OVA_PLUGIN_URI.'assets/libs/meupicon/font/meupicon.css', [], null );
    }

    public function ovatheme_icons_filters_new( $tabs = [] ) {
        $newicons = [];

        // Meup icon
        $font_data['json_url']  = OVA_PLUGIN_URI.'assets/libs/meupicon/meupicon.json';
        $font_data['name']      = 'meupicon';

        $newicons[ $font_data['name'] ] = [
            'name'          => $font_data['name'],
            'label'         => esc_html__( 'Meupicon', 'ova-framework' ),
            'url'           => '',
            'enqueue'       => '',
            'prefix'        => 'meupicon-',
            'displayPrefix' => '',
            'ver'           => '1.0',
            'fetchJson'     => $font_data['json_url']
        ];

        return array_merge( $tabs, $newicons );
    }

    public function includes() {
        // All hooks use in Theme
        include OVA_PLUGIN_PATH.'inc/hooks.php';

        include OVA_PLUGIN_PATH.'inc/shortcode.php';
        
        /* Customize Menu Struct */
        include OVA_PLUGIN_PATH.'inc/ova-walker-menu.php';
    }
}

new OvaFramework();

