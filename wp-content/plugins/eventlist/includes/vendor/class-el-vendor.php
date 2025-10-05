<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EL_Vendor {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/* Get template vendor */
	public function get_template_vendor( $get_data ){

		// $args_vendor = isset( $get_data['vendor'] ) ? (string)$get_data['vendor'] : apply_filters( 'el_manage_vendor_default_page', 'general' );

		// $post_id = isset( $get_data['id'] ) ? (string)$get_data['id'] : '';

		$args_vendor = isset( $get_data['vendor'] ) && !is_array( $get_data['vendor'] ) ? (string)$get_data['vendor'] : apply_filters( 'el_manage_vendor_default_page', 'general' );

		$post_id = isset( $get_data['id'] ) && !is_array( $get_data['id'] ) ? (string)$get_data['id'] : '';



		
		$current_user_id = wp_get_current_user()->ID;
		$author_id = get_post_field( 'post_author', $post_id );

		if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_general', true ) ){
			$template = apply_filters( 'el_shortcode_myaccount_template_general', 'vendor/general.php' );	
		}else{
			$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
		}
		$msg = '';

		switch ($args_vendor) {

			case 'general':
			if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_general', true ) ){
				$template = apply_filters( 'el_shortcode_myaccount_template_general', 'vendor/general.php' );
			}
			break;

			case 'listing':
			if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_my_listing', true ) ){
				if( el_is_vendor() ){
					$template = apply_filters( 'el_shortcode_myaccount_template_events', 'vendor/events.php' );
				}else{
					$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
				}
			}
			break;

			case 'profile':
			$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			break;

			case 'listing-edit':
			if( el_is_vendor() ){
				if ( $current_user_id == $author_id || el_is_administrator() ) {
					$template = apply_filters( 'el_shortcode_myaccount_template_edit_event', 'vendor/edit-event.php' );
				} else {
					$template = apply_filters( 'el_shortcode_myaccount_template_events', 'vendor/events.php' );
				}
			}else{
				$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			}
			break;

			case 'package':
			if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_package', true ) ){
				$template = apply_filters( 'el_shortcode_package_template_package', 'vendor/package.php' );
			}else{
				$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			}
			break;

			case 'wishlist':
			if( apply_filters( 'el_manage_vendor_show_wishlist', true ) ){
				$template = apply_filters( 'el_shortcode_wishlist_template_wishlist', 'vendor/wishlist.php' );
			}
			break;

			case 'mybookings':
			if( apply_filters( 'el_manage_vendor_show_mybooking', true ) ){
				$template = apply_filters( 'el_shortcode_mybookings_template_mybookings', 'vendor/mybookings.php' );
			}
			break;

			case 'tickets_received':
			$template = apply_filters( 'el_shortcode_tickets_received_template_tickets_received', 'vendor/tickets_received.php' );
			break;

			case 'wallet':
			if(  el_is_vendor() && apply_filters( 'el_manage_vendor_show_wallet', true ) ){
				$template = apply_filters( 'el_shortcode_mybookings_template_wallet', 'vendor/wallet.php' );
			}else{
				$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			}
			break;

			case 'create-event':
			
			if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_create_event', true ) ){
				
				$check_create_event = el_check_create_event();

				if ( el_is_administrator() ) {
					$template = apply_filters( 'el_shortcode_myaccount_template_edit_event', 'vendor/edit-event.php' );
				} else {

					switch ( $check_create_event['status'] ) {

						case 'false_total_event':
							$template = apply_filters( 'el_shortcode_package_template_package', 'vendor/package.php' );
							$msg = esc_html__( 'Please register a package or upgrade to high package because your current package is limit number events', 'eventlist' );
							break;

						case 'false_time_membership':
							$template = apply_filters( 'el_shortcode_package_template_package', 'vendor/package.php' );
							$msg = esc_html__( 'Your package time is expired', 'eventlist' );
							break;
							
						case 'error':
							$template = apply_filters( 'el_shortcode_package_template_package', 'vendor/package.php');
							$msg = esc_html__( 'You don\'t have permission add new event', 'eventlist' );
							break;		
						
						default:
							$template = apply_filters( 'el_shortcode_myaccount_template_edit_event', 'vendor/edit-event.php' );
							break;
					}
				}
				
			} else {
				$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			}
			break;

			case 'manage_event':
			
			if( el_is_vendor() ){
				$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
				if( $tab && $tab == 'bookings' ){
					$template = apply_filters( 'el_shortcode_myaccount_template_bookings', 'vendor/bookings.php' );
				}elseif( $tab && $tab == 'tickets' ){
					$template = apply_filters( 'el_shortcode_myaccount_template_tickets', 'vendor/tickets.php' );
				}else{
					$template = apply_filters( 'el_shortcode_myaccount_template_manage_event', 'vendor/manage_event.php' );
				}
			}else{
				$template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
			}
			break;
			

		}

		return array( 'template' => $template, 'msg' => $msg );

	}
	
//get payout method
	public function get_payout_method( ) {
		

		$args_base = array(
			'post_type'      => 'payout_method',
		);

		$payout_method = new WP_Query( $args_base );
		return $payout_method;
	}


	//get id option manage ticket
	public function get_id_manage_ticket( $calendar = array() ) {
		
		$date_arr = array();

		if( $calendar ):
			foreach ($calendar as $key => $value) {

				$start_time = isset( $value['date'] ) ? strtotime($value['date']) : '';
				$end_time = isset( $value['end_date'] ) ? strtotime($value['end_date']) : '';
				$start = isset( $value['date'] ) ?($value['date']) : '';
				$end = isset( $value['end_date'] ) ? ($value['end_date']) : '';

				$date_key = $start_time.$end_time;

				$date_arr[ $date_key ][0] =  [
					'id' =>	$value['calendar_id'],
					'start_time' => $start,
					'end_time' => $end,
				];

				foreach ($calendar as $value2) {

					$start_time2 = isset( $value2['date'] ) ? strtotime($value2['date']) : '';
					$end_time2 = isset( $value2['end_date'] ) ? strtotime($value2['end_date']) : '';
					$date_key2 = $start_time2.$end_time2;

					if($date_key == $date_key2){

						$date_arr[ $date_key ][] =  [
							'id'=> $value2['calendar_id'],
							'start_time' => $start,
							'end_time' => $end,

						];

					}

				}

			}
		endif;

		$arr = [];

		if( $date_arr ):
			foreach ($date_arr as $key3 => $value3) {
				$str = '';
				$unique_val = array_unique( $value3,SORT_REGULAR );

				foreach ($unique_val as $key => $value) {
					$str .= $value['id'].'_';
					$time  = $value['start_time'];
					$time2 = $value['end_time'];
				}
				$arr[] = [
					'id'=> $str,
					'start_time'=> $time,
					'end_time'=> $time2,

				];


			}
		endif;
		
		return $arr;
	}

	/* Get all event */
	public function get_vendor_events ( $order, $orderby, $status, $user_id, $paged, $name, $cat ) {

		$args_orderby = array();

		
		$today_day = current_time('timestamp');
		$_prefix = OVA_METABOX_EVENT;

		if( $status == 'open' ){

			$args_base = array(
				'post_type'      => 'event',
				'order'          => $order,
				'author'         => $user_id,
				'paged'          => $paged,
				'fields'	=> 'ids',
				'meta_query' => array(
					array(
						'key' => $_prefix.'end_date_str',
						'value' => $today_day,
						'compare' => '>='
					)
				)
			);	

		}else if( $status == 'closed' ){

			$args_base = array(
				'post_type'      => 'event',
				'order'          => $order,
				'author'         => $user_id,
				'paged'          => $paged,
				'fields'	=> 'ids',
				'meta_query' => array(
					array(
						'key' => $_prefix.'end_date_str',
						'value' => $today_day,
						'compare' => '<'
					)
					
				)
			);

		}else if( empty( $paged ) ) {
			$args_base = array(
				'post_type'      => 'event',
				'post_status'    => $status,
				'order'          => $order,
				'author'         => $user_id,
				'posts_per_page' => '-1',
				'fields'	=> 'ids'
			);	
		}else{
			$args_base = array(
				'post_type'      => 'event',
				'post_status'    => $status,
				'order'          => $order,
				'author'         => $user_id,
				'paged'          => $paged,
				'fields'	=> 'ids'
			);	
		}



		switch ($orderby) {
			case 'title':
			$args_orderby =  array( 'orderby' => 'title' );
			break;

			case 'start_date':
			$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'start_date_str' );
			break;

			case 'end_date':
			$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'end_date_str' );
			break;
			
			default:
			$args_orderby =  array( 'orderby' => 'ID');
			break;
		}

		$posts_per_page = array();
		$posts_per_page = array( 'posts_per_page' => apply_filters( 'el_my_listing_posts_per_page', 9 ) );

		// Search by event name
		$args_name = array();
		if ( $name ) {
			$args_name = array(
				's' => esc_attr( $name )
			);
		}

		// Search by event category
		$args_cat = array();

		if ( $cat ) {
			$args_cat = array(
				'tax_query' => array(
					array(
						'taxonomy' 	=> 'event_cat',
						'field'    	=> 'slug',
						'terms' 	=> $cat
					)
				)
			);
		}

		$args = array_merge_recursive( $args_base, $args_orderby, $posts_per_page, $args_name, $args_cat );

		if ( el_is_administrator() ) {
			unset( $args['author'] );
		}

		$event = new WP_Query( $args );
		return $event;
	}


	/* Get taxonomy */
	public function el_get_taxonomy ( $taxonomy, $selected='' ) {

		$args = array(
			'taxonomy'          => $taxonomy,
			'show_option_all'   => '' ,
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => '',
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'id'                => '',
			'depth'             => 0,
			'tab_index'         => 0,
			'hide_if_empty'     => false,
			'value_field'       => 'slug',
		);

		return get_categories($args);
	}


	/* Get taxonomy2 */
	public function el_get_taxonomy2 ( $taxonomy, $name='cat', $selected='', $required = 'false', $include = array() ) {

		$args = array(
			'taxonomy'          => $taxonomy,
			'show_option_all'   => '' ,
			'show_option_none'   => esc_html__( 'Select Category', 'eventlist' ),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => $include,
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'id'                => '',
			'depth'             => 0,
			'tab_index'         => 0,
			'hide_if_empty'     => false,
			'value_field'       => 'slug',
			'name'				=> $name,
			'required'			=> $required
		);

		return wp_dropdown_categories($args);
	}

	/* Get taxonomy3 */
	public function el_get_taxonomy3 ( $taxonomy, $name='cat', $selected='', $required = 'false' ) {

		$args = array(
			'taxonomy'          => $taxonomy,
			'show_option_all'   => '' ,
			'show_option_none'   => esc_html__( 'Select Category', 'eventlist' ),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => '',
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'id'                => '',
			'depth'             => 0,
			'tab_index'         => 0,
			'hide_if_empty'     => false,
			'value_field'       => 'id',
			'name'				=> $name,
			'required'			=> $required
		);

		return wp_dropdown_categories($args);
	}

	/* Get taxonomy4 */
	public function el_get_taxonomy4 ( $taxonomy, $name='cat', $selected='', $required = 'false', $include = array() ) {

		$args = array(
			'taxonomy'          => $taxonomy,
			'show_option_all'   => '' ,
			'show_option_none'   => esc_html__( 'Select Category', 'eventlist' ),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => $include,
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'id'                => '',
			'depth'             => 0,
			'tab_index'         => 0,
			'hide_if_empty'     => false,
			'value_field'       => 'slug',
			'name'				=> $name,
			'required'			=> $required
		);

		return el_get_dropdown_categories($args);
	}

	public function el_get_custom_taxonomy_dropdown_html( $taxonomy = 'category', $name = 'cat', $selected = '', $show_option_none = '', $class = 'ova_category', $required = false, $include = array() ) {
		$args = array(
			'show_option_all'   => '',
			'show_option_none'  => sprintf(__("%s", 'eventlist'), $show_option_none),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => $include,
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'name'              => $name,
			'id'                => "",
			'class'             => $class,
			'depth'             => 0,
			'tab_index'         => 0,
			'taxonomy'          => $taxonomy,
			'hide_if_empty'     => false,
			'option_none_value' => "",
			'value_field'       => 'slug',
			'required'          => $required,
			'aria_describedby'  => '',
			'data_placeholder'	=> sprintf(__("%s", 'eventlist'), $show_option_none),
		);
		return wp_dropdown_categories( $args );
	}

	/* Get html dropdown categories */
	function el_get_dropdown_categories( $args = '' ) {
		$defaults = array(
			'show_option_all'   => '',
			'show_option_none'  => '',
			'orderby'           => 'id',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 1,
			'child_of'          => 0,
			'exclude'           => '',
			'echo'              => 1,
			'selected'          => 0,
			'hierarchical'      => 0,
			'name'              => 'cat',
			'id'                => '',
			'class'             => 'postform',
			'depth'             => 0,
			'tab_index'         => 0,
			'taxonomy'          => 'category',
			'hide_if_empty'     => false,
			'option_none_value' => -1,
			'value_field'       => 'term_id',
			'required'          => false,
		);

		$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;

		// Back compat.
		if ( isset( $args['type'] ) && 'link' === $args['type'] ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.0.0',
				sprintf(
					/* translators: 1: "type => link", 2: "taxonomy => link_category" */
					__( '%1$s is deprecated. Use %2$s instead.' ),
					'<code>type => link</code>',
					'<code>taxonomy => link_category</code>'
				)
			);
			$args['taxonomy'] = 'link_category';
		}

		$parsed_args = wp_parse_args( $args, $defaults );

		$option_none_value = $parsed_args['option_none_value'];

		if ( ! isset( $parsed_args['pad_counts'] ) && $parsed_args['show_count'] && $parsed_args['hierarchical'] ) {
			$parsed_args['pad_counts'] = true;
		}

		$tab_index = $parsed_args['tab_index'];

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}

		$get_terms_args = $parsed_args;
		unset( $get_terms_args['name'] );
		$categories = get_terms( $get_terms_args );

		$name     = esc_attr( $parsed_args['name'] );
		$class    = esc_attr( $parsed_args['class'] );
		$id       = $parsed_args['id'] ? esc_attr( $parsed_args['id'] ) : $name;
		$required = $parsed_args['required'] ? 'required' : '';

		if ( ! $parsed_args['hide_if_empty'] || ! empty( $categories ) ) {
			$output = "<select $required name='$name' id='$id' class='$class' $tab_index_attribute>\n";
		} else {
			$output = '';
		}
		if ( empty( $categories ) && ! $parsed_args['hide_if_empty'] && ! empty( $parsed_args['show_option_none'] ) ) {

			/**
			 * Filters a taxonomy drop-down display element.
			 *
			 * A variety of taxonomy drop-down display elements can be modified
			 * just prior to display via this filter. Filterable arguments include
			 * 'show_option_none', 'show_option_all', and various forms of the
			 * term name.
			 *
			 * @since 1.2.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param string       $element  Category name.
			 * @param WP_Term|null $category The category object, or null if there's no corresponding category.
			 */
			$show_option_none = apply_filters( 'list_cats', $parsed_args['show_option_none'], null );
			$output          .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>$show_option_none</option>\n";
		}

		if ( ! empty( $categories ) ) {

			if ( $parsed_args['show_option_all'] ) {

				$show_option_all = apply_filters( 'list_cats', $parsed_args['show_option_all'], null );
				$selected        = ( '0' === (string) $parsed_args['selected'] ) ? " selected='selected'" : '';
				$output         .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}

			if ( $parsed_args['show_option_none'] ) {

				/** This filter is documented in wp-includes/category-template.php */
				$show_option_none = apply_filters( 'list_cats', $parsed_args['show_option_none'], null );
				$selected         = selected( $option_none_value, $parsed_args['selected'], false );
				$output          .= "\t<option value='" . esc_attr( $option_none_value ) . "'$selected>$show_option_none</option>\n";
			}

			if ( $parsed_args['hierarchical'] ) {
				$depth = $parsed_args['depth'];  // Walk the full depth.
			} else {
				$depth = -1; // Flat.
			}

			foreach( $categories as $k => $category ) {
				$slug = isset( $category->slug ) ? apply_filters( 'editable_slug', $category->slug, $category ) : '';
				$categories[$k]->slug = $slug;
			}

			$output .= walk_category_dropdown_tree( $categories, $depth, $parsed_args );
		}

		if ( ! $parsed_args['hide_if_empty'] || ! empty( $categories ) ) {
			$output .= "</select>\n";
		}

		$output = apply_filters( 'wp_dropdown_cats', $output, $parsed_args );

		if ( $parsed_args['echo'] ) {
			echo $output;
		}

		return $output;
	}


	/* Get Country */
	public function el_get_state ( $selected='' ) {

		$args = array(
			'show_option_all'   => '' ,
			'show_option_none'   => esc_html__( 'All States', 'eventlist' ),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => '',
			'include'           => '',
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'name'              => 'event_state',
			'id'                => '',
			'class'             => 'selectpicker postform',
			'depth'             => 1,
			'tab_index'         => 0,
			'taxonomy'          => 'event_loc',
			'hide_if_empty'     => false,
			'value_field'       => 'slug',
		);

		return el_get_dropdown_categories($args);
	}


	/* Get City */
	public function el_get_city( $selected='' ){
		$args_country = array(
			'taxonomy'               => 'event_loc',
			'object_ids'             => null,
			'orderby'                => 'name',
			'order'                  => 'ASC',
			'hide_empty'             => false,
			'include'                => array(),
			'exclude'                => array(),
			'exclude_tree'           => array(),
			'number'                 => '',
			'offset'                 => '',
			'fields'                 => 'all',
			'count'                  => false,
			'name'                   => '',
			'slug'                   => '',
			'term_taxonomy_id'       => '',
			'hierarchical'           => false,
			'search'                 => '',
			'name__like'             => '',
			'description__like'      => '',
			'pad_counts'             => false,
			'get'                    => '',
			'child_of'               => 0,
			'parent'                 => 0,
			'childless'              => false,
			'cache_domain'           => 'core',
			'update_term_meta_cache' => true,
			'meta_query'             => '',
			'meta_key'               => '',
			'meta_value'             => '',
			'meta_type'              => '',
			'meta_compare'           => '',
		);

		$include_city = array();

		if( isset( $_GET['event_state']) && $_GET['event_state'] != '' ){
			$country_current = get_term_by( 'slug',  $_GET['event_state'], 'event_loc' );
			$country_info = get_term_children( $country_current->term_id, 'event_loc' );

			foreach ( $country_info as $value ) {
				$term_city = get_term_by( 'id', $value, 'event_loc' );
				$include_city[] = $term_city->term_id;
			}
		}

		$country = array();
		$tax_country = get_terms( $args_country );
		foreach ($tax_country as $key => $value) {
			$country[] = $value->term_id;
		}

		$args = array(
			'show_option_all'   => '' ,
			'show_option_none'  => esc_html__( 'All Cities', 'eventlist' ),
			'post_type'         => 'event',
			'post_status'       => 'publish',
			'posts_per_page'    => '-1',
			'option_none_value' => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'exclude'           => $country,
			'include'           => $include_city,
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'name'              => 'event_city',
			'id'                => '',
			'class'             => 'selectpicker postform',
			'depth'             => 0,
			'tab_index'         => 0,
			'taxonomy'          => 'event_loc',
			'hide_if_empty'     => false,
			'value_field'       => 'slug',
		);

		return el_get_dropdown_categories($args);
	}

	public function check_allow_get_list_attendees_by_event($id_event) {

		if( $id_event == null ) return 'no';

		if ( el_is_administrator() ) {
			$check_allow = "yes";
			return $check_allow;
		}

		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		if ( $enable_package !== 'yes') return 'yes';

		$membership_id 		= get_post_meta( $id_event, OVA_METABOX_EVENT."membership_id", true );
		if ( empty( $membership_id ) ) {
			$post_id_package = $this->get_post_id_package_by_event($id_event);
		} else {
			$package_slug 		= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_package_id", true );
			$post_id_package 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		}

		if ( empty($post_id_package) ) return 'no';
		$check_allow = get_post_meta($post_id_package, OVA_METABOX_EVENT . 'list_attendees', true);

		return apply_filters( 'check_list_attendees', $check_allow );
	}

	public function check_allow_export_attendees_by_event ($id_event) {
		if( $id_event == null ) return;
		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		if ($enable_package !== 'yes') return 'yes';

		if ( el_is_administrator() ) {
			$check_allow = "yes";
			return $check_allow;
		}

		$membership_id 		= get_post_meta( $id_event, OVA_METABOX_EVENT."membership_id", true );

		if ( empty( $membership_id ) ) {
			$post_id_package = $this->get_post_id_package_by_event($id_event);
		} else {
			$package_slug 		= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_package_id", true );
			$post_id_package 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		}

		if ( empty($post_id_package) ) return;
		$check_allow = get_post_meta($post_id_package, OVA_METABOX_EVENT . 'export_attendees', true);

		
		
		return apply_filters( 'check_export_attendees', $check_allow );
	}

	public function check_allow_get_list_tickets_by_event ($id_event) {
		if( $id_event == null ) return;
		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		if  ( $enable_package !== 'yes' ) return 'yes';

		if ( el_is_administrator() ) {
			return 'yes';
		}

		$membership_id 		= get_post_meta( $id_event, OVA_METABOX_EVENT."membership_id", true );

		if ( empty( $membership_id ) ) {

			$post_id_package = $this->get_post_id_package_by_event($id_event);
		} else {
			$package_slug 		= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_package_id", true );
			$post_id_package 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		}
	
		if ( empty($post_id_package) ) return;
		$check_allow = get_post_meta($post_id_package, OVA_METABOX_EVENT . 'list_tickets', true);

		return apply_filters( 'check_list_ticket',  $check_allow);
	}

	public function check_allow_export_tickets_by_event ( $id_event ) {
		if( $id_event == null ) return;
		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		if ($enable_package !== 'yes') return 'yes';

		if ( el_is_administrator() ) {
			$check_allow = "yes";
			return $check_allow;
		}

		$membership_id 		= get_post_meta( $id_event, OVA_METABOX_EVENT."membership_id", true );
		
		if ( empty( $membership_id ) ) {
			$post_id_package = $this->get_post_id_package_by_event($id_event);
		} else {
			$package_slug 		= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_package_id", true );
			$post_id_package 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		}

		if ( empty($post_id_package) ) return;
		$check_allow = get_post_meta($post_id_package, OVA_METABOX_EVENT . 'export_tickets', true);

		return apply_filters( 'check_export_ticket', $check_allow );
	}

	public function check_allow_change_tax_by_event ( $id_event = null ) {

		if( $id_event == null ) return;

		if ( el_is_administrator() ) {
			return 'yes';
		}

		$enable_package = EL()->options->package->get( 'enable_package', 'no' );
		if ($enable_package !== 'yes') return;

		$membership_id 		= get_post_meta( $id_event, OVA_METABOX_EVENT."membership_id", true );
		if ( empty( $membership_id ) ) {
			$post_id_package = $this->get_post_id_package_by_event($id_event);
		} else {
			$package_slug 		= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_package_id", true );
			$post_id_package 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		}

		if ( empty( $post_id_package ) ) return;
		$check_allow = get_post_meta($post_id_package, OVA_METABOX_EVENT . 'change_tax', true);
		
		return apply_filters( 'check_change_tax', $check_allow );
	}

	public function check_allow_change_tax_by_user_login() {

		$id_user 		= get_current_user_id();
		$package_slug 	= get_user_meta($id_user, 'package', true);
		$cur_user 		= wp_get_current_user();
		
		if ( in_array('administrator', $cur_user->roles ) ) {
			return 'yes';
		}

		if ( empty( $package_slug ) ) {
			$package_id = EL_Package::get_package_id_default();
		} else {
			$package_id = EL_Package::get_package_id_by_slug( $package_slug );
		}
		
		if ( empty( $package_id ) ) return;

		$check_allow = get_post_meta($package_id, OVA_METABOX_EVENT . 'change_tax', true);
		return apply_filters( 'check_change_tax', $check_allow );
	}

	public function get_post_id_package_by_event ( $id_event = null ) {
		if($id_event == null) return;

		$id_user 	= get_current_user_id();
		$package_id = get_post_meta($id_event, OVA_METABOX_EVENT . 'package', true);

		if ( empty($package_id) ) return ;
		
		$agrs = [
			'post_type' 	=> 'package',
			'post_status' 	=> 'publish',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' 	=> OVA_METABOX_EVENT . 'package_id',
					'value' => $package_id
				],
			],
			'post_per_page' => 1,
			'fields' 		=> 'ids',
		];

		$package = get_posts( $agrs );
		
		if ( empty($package) ) return;
		$post_id_package = $package[0];

		return $post_id_package;
	}

	public function display_date_event ( $start_date = '', $start_time = '', $end_date = '', $end_time = '' ) {
		$date = array();
		if( $start_date ){
			$date[] = '<span class="date">'.$start_date .'</span> <span class="slash">@</span> <span class="time">'.$start_time.'</span>';
		}
		
		if( $end_date ){
			$date[] = '<span class="date">'.$end_date .'</span> <span class="slash">@</span> <span class="time"> '.$end_time.'</span>'; 
		}
		echo implode( ' <span class="slash">-</span> ', $date );
	}

}

