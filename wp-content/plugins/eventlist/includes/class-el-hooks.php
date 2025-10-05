<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EL_Hooks {

	public function __construct() {

		add_action( 'init', array( $this, 'el_prevent_admin_access' ) );

		/* Schema Event */
		$show_schema = EL_Setting::instance()->event->get( 'show_schema' ) ? EL_Setting::instance()->event->get( 'show_schema' ) : 'yes';

		if ( $show_schema === "yes" && ! is_admin() ) {
			add_action('wp_head', array( $this, 'add_schema' ), 10 );
		}


		// Update Event Status
		$check_event_status_first_time = EL()->options->general->get('event_status_first_time','');

		if ( $check_event_status_first_time ) {
			add_action( 'el_vendor_after_create_event', array( $this, 'el_update_event_status' ), 10, 1 );
			add_action( 'el_vendor_after_update_event', array( $this, 'el_update_event_status' ), 10, 1 );
			add_action( 'el_after_save_event_metabox', array( $this, 'el_update_event_status' ), 10, 1 );
		} else {
			add_action( 'el_after_update_event_status_manually', array( $this, 'el_update_event_status_first_time' ), 10, 0 );
			add_action( 'el_after_update_event_status_automatic', array( $this, 'el_update_event_status_first_time' ), 10, 0 );
		}

		// Show Private Event
		if ( apply_filters( 'el_show_private_event', true ) === true ) {
			add_action( 'pre_get_posts', array( $this, 'el_show_private_event_query' ) );
			add_filter( 'wp_insert_post_data', array( $this, 'el_event_private_save_password' ), 10 , 2 );
		}
		
		// Remove prepend
		if ( ! is_admin() ) {
			add_filter( 'protected_title_format', array( $this, 'el_title_format' ) );
			add_filter( 'private_title_format', array( $this, 'el_title_format' ) );
		}
	}

	public function el_title_format( $prepend ){
		$prepend = '%s';
		return $prepend;
	}

	public function el_event_private_save_password( $data, $postarr ){

		$post_password = isset( $postarr['post_password'] ) ? sanitize_text_field( $postarr['post_password'] ) : "";
		$data['post_password'] = $post_password;

		return apply_filters( 'el_event_private_save_password', $data, $postarr );
	}

	public function el_include_protected( $where ){
		global $wpdb;
		$find_str_multiple 	= " AND ({$wpdb->posts}.post_password = '') ";
		$find_str_single 	= " AND {$wpdb->posts}.post_password = '' ";
		$where = str_replace($find_str_single, '', $where);
		$where = str_replace($find_str_multiple, '', $where);
		return $where;
	}

	public function el_show_private_event_query( $query ){

		if( ! is_admin() && $query->get('post_type') === 'event' ) {
			add_filter( 'posts_where', array( $this, 'el_include_protected' ) );
		}

		if ( ! is_admin() && $query->is_main_query() && $query->is_singular && $query->get('post_type') === 'event' ) {
			$query->set( 'post_status', array( 'publish', 'private' ) );
			if ( $query->is_preview ) {
				$query->set( 'post_status', array( 'publish', 'private', 'pending' ) );
				if ( isset( $query->query_vars['name'] ) && ! empty( $query->query_vars['name'] ) ) {
					$query->set('name', $query->query_vars['name'] );
				} else if ( isset( $query->query_vars['p'] ) && ! empty( $query->query_vars['p'] ) ) {
					$query->set('p', $query->query_vars['p'] );
				}
			}
			
		}
	}


	public function add_schema(){
		if ( is_singular( 'event' ) ) {
			el_schema();
		}
	}

	function el_prevent_admin_access(){

		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'el_woocommerce_prevent_admin_access_customize' ), 10, 1 );
		
	}

	public function el_woocommerce_prevent_admin_access_customize( $prevent_access ){

		if( el_can_upload_files() ){
			return false; 
		}

		return $prevent_access;

	}
	
	public function el_update_event_status_first_time(){
		$ova_event_setting = get_option( 'ova_eventlist' ) ? get_option( 'ova_eventlist' ) : array();
		$ova_event_setting['general']['event_status_first_time'] = 'pass';
		update_option( 'ova_eventlist', $ova_event_setting );

	}

	public function el_update_event_status( $post_id ){
		$end_date_time 		= (int) get_post_meta( $post_id, OVA_METABOX_EVENT.'end_date_str', true );
		$start_date_time 	= (int) get_post_meta( $post_id, OVA_METABOX_EVENT.'start_date_str', true );
		$option_calendar 	= get_post_meta( $post_id, OVA_METABOX_EVENT.'option_calendar', true );
		$current_time 		= (int) current_time( 'timestamp' );
		$event_status 		= '';

		if ( $end_date_time < $current_time ) {
			$event_status = 'past';
		} elseif ( $end_date_time > $current_time && ( $start_date_time >  $current_time || $option_calendar == 'auto' ) ) {
			$event_status = 'upcoming';
		} elseif ( $start_date_time <= $current_time && $end_date_time >= $current_time ) {
			$event_status = 'opening';
		}

		update_post_meta( $post_id, OVA_METABOX_EVENT.'event_status', $event_status );
	}
}

new EL_Hooks();