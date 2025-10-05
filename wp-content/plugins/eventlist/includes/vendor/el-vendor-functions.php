<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Get list event
if( !function_exists( 'get_vendor_events' ) ){

	function get_vendor_events( $order = 'DESC', $orderby = 'ID', $status = 'public', $user_id = '', $paged = '', $name = '', $cat = '' ){
		
		return EL_Vendor::instance()->get_vendor_events( $order, $orderby, $status, $user_id, $paged, $name, $cat );
		
	}

}

// Get payout method
if( !function_exists( 'get_payout_method' ) ){

	function get_payout_method( ){

		return EL_Vendor::instance()->get_payout_method( );
		
	}

}


// get id option manage ticket
if( !function_exists( 'get_id_manage_ticket' ) ){

	function get_id_manage_ticket($calendar){

		return EL_Vendor::instance()->get_id_manage_ticket($calendar );
		
	}

}


// Get taxonomy
if( !function_exists( 'el_get_taxonomy' ) ){

	function el_get_taxonomy( $taxonomy = '' ){

		return EL_Vendor::instance()->el_get_taxonomy( $taxonomy );

	}

}

// Get taxonomy2
if( !function_exists( 'el_get_taxonomy2' ) ){

	function el_get_taxonomy2( $taxonomy = '', $name='cat', $selected = '',$required = 'false', $include = array() ){

		return EL_Vendor::instance()->el_get_taxonomy2( $taxonomy, $name, $selected, $required, $include );

	}

}

// Get taxonomy3
if( !function_exists( 'el_get_taxonomy3' ) ){

	function el_get_taxonomy3( $taxonomy = '', $name='cat', $selected = '', $required = 'false' ){

		return EL_Vendor::instance()->el_get_taxonomy3( $taxonomy, $name, $selected, $required );

	}

}

// Get taxonomy4
if( !function_exists( 'el_get_taxonomy4' ) ){

	function el_get_taxonomy4( $taxonomy = '', $name='cat', $selected = '',$required = 'false', $include = array() ){

		return EL_Vendor::instance()->el_get_taxonomy4( $taxonomy, $name, $selected, $required, $include );

	}

}

// Get custom taxonomy dropdown html

if ( ! function_exists('el_get_custom_taxonomy_dropdown_html') ) {
	function el_get_custom_taxonomy_dropdown_html( $taxonomy = 'category', $name = 'cat', $selected = '', $show_option_none = '', $class = 'ova_category', $required = false, $include = array() ){
		return EL_Vendor::instance()->el_get_custom_taxonomy_dropdown_html( $taxonomy, $name, $selected, $show_option_none, $class, $required, $include);
	}
}

// Get html dropdown categories
if( !function_exists( 'el_get_dropdown_categories' ) ){

	function el_get_dropdown_categories( $args ){

		return EL_Vendor::instance()->el_get_dropdown_categories( $args );

	}

}


// Get country
if( !function_exists( 'el_get_state' ) ){

	function el_get_state( $selected = '' ){

		return EL_Vendor::instance()->el_get_state( $selected );

	}

}


// Get city
if( !function_exists( 'el_get_city' ) ){

	function el_get_city( $selected = '' ){

		return EL_Vendor::instance()->el_get_city( $selected );
		
	}

}

//check allow get list attendees
if ( !function_exists( 'check_allow_get_list_attendees_by_event' ) ) {
	function check_allow_get_list_attendees_by_event ( $id_event ) {
		return EL_Vendor::instance()->check_allow_get_list_attendees_by_event( $id_event );
	}
}

//check allow export attendees
if ( !function_exists( 'check_allow_export_attendees_by_event' ) ) {
	function check_allow_export_attendees_by_event ($id_event) {
		return EL_Vendor::instance()->check_allow_export_attendees_by_event($id_event);
	}
}

//check allow get list tickets
if ( !function_exists( 'check_allow_get_list_tickets_by_event' ) ) {
	function check_allow_get_list_tickets_by_event ($id_event) {
		return EL_Vendor::instance()->check_allow_get_list_tickets_by_event($id_event);
	}
}

//check allow export tickets
if ( !function_exists( 'check_allow_export_tickets_by_event' ) ) {
	function check_allow_export_tickets_by_event ($id_event) {
		return EL_Vendor::instance()->check_allow_export_tickets_by_event($id_event);
	}
}

//check allow change tax
if ( !function_exists( 'check_allow_change_tax_by_event' ) ) {
	function check_allow_change_tax_by_event ($id_event) {
		return EL_Vendor::instance()->check_allow_change_tax_by_event($id_event);
	}
}

if ( !function_exists( 'get_post_id_package_by_event' ) ) {
	function get_post_id_package_by_event ($id_event) {
		return EL_Vendor::instance()->get_post_id_package_by_event($id_event);
	}
}

//check allow change tax
if ( !function_exists( 'check_allow_change_tax_by_user_login' ) ) {
	function check_allow_change_tax_by_user_login () {
		return EL_Vendor::instance()->check_allow_change_tax_by_user_login();
	}
}