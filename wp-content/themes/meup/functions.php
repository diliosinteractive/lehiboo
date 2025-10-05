<?php
	if(defined('MEUP_URL') 	== false) 	define('MEUP_URL', get_template_directory());
	if(defined('MEUP_URI') 	== false) 	define('MEUP_URI', get_template_directory_uri());

	load_theme_textdomain( 'meup', MEUP_URL . '/languages' );
	
	// require libraries, function
	require( MEUP_URL.'/inc/init.php' );

	// Add js, css
	require( MEUP_URL.'/extend/add_js_css.php' );
	
	// require walker menu
	require_once (MEUP_URL.'/inc/ova_walker_nav_menu.php');
	

	// register menu, widget
	require( MEUP_URL.'/extend/register_menu_widget.php' );

	// require content
	require_once (MEUP_URL.'/content/define_blocks_content.php');
	
	// require breadcrumbs
	require( MEUP_URL.'/extend/breadcrumbs.php' );

	// Hooks
	require( MEUP_URL.'/inc/class_hook.php' );


	
	/* Customize */
	//  include plugin.php to use is_plugin_active()
	if( current_user_can('customize') ){
	    require_once MEUP_URL.'/customize/custom-control/google-font.php';
	    require_once MEUP_URL.'/customize/custom-control/heading.php';
	    require_once MEUP_URL.'/customize/class-customize.php';
	}
	
    require_once MEUP_URL.'/customize/render-style.php';
    
	// Require metabox
	if( is_admin() ){
		// Require TGM
		require_once ( MEUP_URL.'/install_resource/active_plugins.php' );
	}
	// If not active child theme
	add_filter( 'register_taxonomy_el_1', function ($params){ return array( 'slug' => 'eljob', 'name' => esc_html__( 'Job2', 'meup-child' ) ); }, 5 );
	add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eltime', 'name' => esc_html__( 'Time', 'meup-child' ) ); }, 5 );

