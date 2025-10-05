<?php

class Meup_Hooks {

	public function __construct() {
		
		// Return HTML for Header
		add_filter( 'meup_render_header', array( $this, 'meup_render_header' ) );

		// Return HTML for Footer
		add_filter( 'meup_render_footer', array( $this, 'meup_render_footer' ) );


		/* Get All Header */
		add_filter( 'meup_list_header', array( $this, 'meup_list_header' ) );

		/* Get All Footer */
		add_filter( 'meup_list_footer', array( $this,  'meup_list_footer' ) );

		/* Define Layout */
		add_filter( 'meup_define_layout', array( $this,  'meup_define_layout' ) );

		/* Define Wide */
		add_filter( 'meup_define_wide_boxed', array( $this,  'meup_define_wide_boxed' ) );

		/* Get layout */
		add_filter( 'meup_get_layout', array( $this, 'meup_get_layout' ) );

		/* Get sidebar */
		add_filter( 'meup_theme_sidebar', array( $this, 'meup_theme_sidebar' ), 10  );

		/* Wide or Boxed */
		add_filter( 'meup_width_site', array( $this, 'meup_width_site' ) );

		/* Get Blog Template */
		add_filter( 'meup_blog_template', array( $this, 'meup_blog_template' ) );
		

    }

	
	public function meup_render_header(){

		$current_id = meup_get_current_id();

		// Get header default from customizer
		$global_header = get_theme_mod('global_header','default');

		// Header in Metabox of Post, Page
	    $meta_header = get_post_meta($current_id, 'ova_met_header_version', 'true');
	  	
	    // Header use in post,page
	    if( $current_id != '' && $meta_header != 'global'  && $meta_header != '' ){
	    
	    	$header = $meta_header;
	  	
	  	}else if ( is_post_type_archive( 'event' ) || is_tax( 'event_cat' ) || is_tax( 'event_tag' ) || is_tax( 'event_loc' ) || ( function_exists( 'el_is_tax_event' ) && el_is_tax_event() ) ) {

	  		$header = get_theme_mod('archive_event_header', 'default');
	  	
	  	}else if( is_post_type_archive( 'venue' ) ){

	  		$header = get_theme_mod('archive_venue_header', 'default');

	  	}else if( is_singular('event') ){
	  	
	  		$header = get_theme_mod('single_event_header', 'default');
	  	
	  	}else if( is_singular('venue') ){

	  		$header = get_theme_mod('single_venue_header', 'default');

	  	}else if( is_author() ){

	  		$header = get_theme_mod('author_header', 'default');

	  	}else if( meup_is_blog_archive() ){ // Header use in blog
	  	
	  		$header = get_theme_mod('blog_header', 'default');
	  	
	  	}else if( is_singular('post') ){ // Header use in single post
	  	
	  		$header = get_theme_mod('single_header', 'default');
	  	
	  	}else{ // Header use in global
	  	
	  		$header = $global_header;
	  	
	  	}

		$header_split = explode(',', $header);

		if ( meup_is_elementor_active() && isset( $header_split[1] ) ) {

			$post_id_header = meup_get_id_by_slug( $header_split[1] );

			// Check WPML 
			if( function_exists( 'icl_object_id' ) ){
				$post_id_header = icl_object_id($post_id_header, 'ova_framework_hf_el', false);	
			}


			return Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post_id_header );

		}else if ( meup_is_elementor_active() && !isset( $header_split[1] ) ) {

			return get_template_part( 'header/header', $header );

		}else if ( !meup_is_elementor_active()  ) {

			return get_template_part( 'header/header', 'default' );

		}

	}


	
	public function meup_render_footer(){

		$current_id = meup_get_current_id();

		// Get Footer default from customizer
		$global_footer = get_theme_mod('global_footer', 'default' );

		// Footer in Metabox of Post, Page
	    $meta_footer =  get_post_meta( $current_id, 'ova_met_footer_version', 'true' );
		
	  	

	  	if( $current_id != '' && $meta_footer != 'global'  && $meta_footer != '' ){
	  	
	  		$footer = $meta_footer;
	  	
	  	}else if ( is_post_type_archive( 'event' ) || is_tax( 'event_cat' ) || is_tax( 'event_tag' ) || is_tax( 'event_loc' ) ) {
	  	
	  		$footer = get_theme_mod('archive_event_footer', 'default');
	  	
	  	}else if( is_post_type_archive( 'venue' ) ){

	  		$footer = get_theme_mod('archive_venue_footer', 'default');

	  	}else if( is_singular('event') ){
	  	
	  		$footer = get_theme_mod('single_event_footer', 'default');
	  	
	  	}else if( is_singular('venue') ){

	  		$footer = get_theme_mod('single_venue_footer', 'default');

	  	}else if( is_author() ){

	  		$footer = get_theme_mod('author_footer', 'default');

	  	}else if( meup_is_blog_archive() ){
	  	
	  		$footer = get_theme_mod('blog_footer', 'default');
	  	
	  	}else if( is_singular('post') ){
	  	
	  		$footer = get_theme_mod('single_footer', 'default');
	  	
	  	}else{
	  	
	  		$footer = $global_footer;
	  	
	  	}

	  	$footer_split = explode(',', $footer);

		if ( meup_is_elementor_active() && isset( $footer_split[1] ) ) {

			$post_id_footer = meup_get_id_by_slug( $footer_split[1] );

			// Check WPML 
			if( function_exists( 'icl_object_id' ) ){
				$post_id_footer = icl_object_id($post_id_footer, 'ova_framework_hf_el', false);	
			}

			
			return Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post_id_footer );
			
		}else if ( meup_is_elementor_active() && !isset( $footer_split[1] ) ) {

			get_template_part( 'footer/footer', $footer );

		}else if( !meup_is_elementor_active() ){

			get_template_part( 'footer/footer', 'default' );			
		}
	}



	function meup_list_header(){

	    $hf_header_array['default'] = esc_html__( 'Default', 'meup' );

	    if( !meup_is_elementor_active() ) return $hf_header_array;

	    $args_hf = array(
	        'post_type' => 'ova_framework_hf_el',
	        'post_status'   => 'publish',
	        'posts_per_page' => '-1',
	        'meta_query' => array(
	            array(
	                'key'     => 'hf_options',
	                'value'   => 'header',
	                'compare' => '=',
	            ),
	        )
	    );
	   

	    $hf = get_posts( $args_hf );

	    foreach ( $hf as $post ) {

	    	setup_postdata( $post );
	    	$hf_header_array[ 'ova,'.$post->post_name ] = get_the_title( $post->ID );
	    }   

	    wp_reset_postdata();

	    return $hf_header_array;
	}

	
	function meup_list_footer(){

	    $hf_footer_array['default'] = esc_html__( 'Default', 'meup' );

	    if( !meup_is_elementor_active() ) return $hf_footer_array;

	    $args_hf = array(
	        'post_type' => 'ova_framework_hf_el',
	        'post_status'   => 'publish',
	        'posts_per_page' => '-1',
	        'meta_query' => array(
	            array(
	                'key'     => 'hf_options',
	                'value'   => 'footer',
	                'compare' => '=',
	            ),
	        )
	    );

	   
	    $hf = get_posts( $args_hf );

	    foreach ( $hf as $post ) {

	    	setup_postdata( $post );
	    	$hf_footer_array[ 'ova,'.$post->post_name ] = get_the_title( $post->ID );
	    }   

	    wp_reset_postdata();

	    return $hf_footer_array;
	}


	function meup_define_layout(){
		return array(
			'layout_1c' => esc_html__('No Sidebar', 'meup'),
			'layout_2r' => esc_html__('Right Sidebar', 'meup'),
			'layout_2l' => esc_html__('Left Sidebar', 'meup'),
		);
	}


	function meup_get_layout(){
		
		$current_id = meup_get_current_id();

		//$layout = get_post_meta( $current_id, 'ova_met_main_layout', true );
		$layout = '';
		$width_sidebar = get_theme_mod( 'global_sidebar_width', '405' );

		if( is_singular( 'post' ) ){

		    $layout = get_theme_mod( 'single_layout', 'layout_2r' ); 
		    $width_sidebar = get_theme_mod( 'single_sidebar_width', '405' );

		}else if( meup_is_woo_active() && is_product_category() ){
			
			$layout = get_theme_mod( 'woo_layout', 'layout_1c' );
			$width_sidebar = get_theme_mod( 'woo_sidebar_width', '320' );
		}
		else if( meup_is_blog_archive() ){

		    $layout = get_theme_mod( 'blog_layout', 'layout_2r' );
		    $width_sidebar = get_theme_mod( 'blog_sidebar_width', '405' );
		}
		
		
		if( $current_id ){

		    $layout_in_post = get_post_meta( $current_id, 'ova_met_main_layout', true );

		    if( $layout_in_post != 'global' && $layout_in_post != '' ){
		    	$layout = $layout_in_post;
		    }

		}

		if( isset( $_GET['layout_sidebar'] ) ){
			$layout = $_GET['layout_sidebar'];
		}

		if( !$layout ){
			$layout = get_theme_mod( 'global_layout', 'layout_2r' );
		}


		return array( $layout, $width_sidebar );
	}

	function meup_width_site(){
		$current_id = meup_get_current_id();
		$width_site = get_post_meta( $current_id, 'ova_met_width_site', true );

		if( $current_id && $width_site != 'global' ){
		    $width = $width_site;
		}else{
			$width = get_theme_mod( 'global_width_site', 'wide' );
		}

		return $width;
	}

	function meup_theme_sidebar(){
		$layout_sidebar = apply_filters( 'meup_get_layout', '' );
		return $layout_sidebar[0];

	}

	function meup_define_wide_boxed(){
		return array(
			'wide' => esc_html__('Wide', 'meup'),
			'boxed' => esc_html__('Boxed', 'meup'),
		);
	}

	function meup_blog_template(){
		$blog_template = get_theme_mod( 'blog_template', 'default' );
		if( isset( $_GET['blog_template'] ) ){
			$blog_template = $_GET['blog_template'];
		}
		return $blog_template;
	}

}

new Meup_Hooks();

