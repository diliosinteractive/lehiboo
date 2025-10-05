<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



if( !function_exists( 'is_current_user_check_in' ) ){
	function is_current_user_check_in(){
		return EL_User::is_current_user_check_in();
	}
}

if( !function_exists( 'el_get_current_user' ) ){
	function el_get_current_user(){
		return EL_User::el_get_current_user();
	}
}

// Check current user is author of posts
function verify_current_user_post( $post_id = '' ){

	$post = get_post( $post_id );
	$post_author = $post ? $post->post_author : '';
	$user_id = get_current_user_id();

	$cap_manage_event = apply_filters( 'el_cap_manage_event', 'administrator' );

	if( $user_id == $post_author || current_user_can( $cap_manage_event ) ) return true;

	return false;
}

/**
 * permission Add or Admin
 * @return true, false
 */
function el_can_add_event(){
	$add_el_event = ( current_user_can( 'add_el_event' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_add_event', $add_el_event );
}

/**
 * permission Edit or Admin
 * @return true, false
 */
function el_can_edit_event(){
	$edit_el_event = ( current_user_can( 'edit_el_event' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_edit_event', $edit_el_event );
}

if ( ! function_exists('el_can_create_tickets') ) {
	function el_can_create_tickets(){
		return apply_filters( 'el_can_create_tickets', current_user_can( 'el_create_tickets' ) );
	}
}

/**
 * permission publish or Admin
 * @return true, false
 */
function el_can_publish_event(){
	$publish_el_event = ( current_user_can( 'publish_el_event' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_publish_event', $publish_el_event );
}



/**
 * permission delete or Admin
 * @return true, false
 */
function el_can_delete_event(){
	$delete_el_event = ( current_user_can( 'delete_el_event' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_delete_event', $delete_el_event );
}


/**
 * permission upload file or Admin
 * @return true, false
 */
function el_can_upload_files(){
	$upload_files = ( current_user_can( 'upload_files' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_upload_files', $upload_files );
}


/**
 * permission manage booking or Admin
 * @return true, false
 */
function el_can_manage_booking(){
	$el_manage_booking = ( current_user_can( 'el_manage_booking' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_manage_booking', $el_manage_booking );
}

/**
 * permission manage ticket or Admin
 * @return true, false
 */
function el_can_manage_ticket(){
	$el_manage_ticket = ( current_user_can( 'el_manage_ticket' ) || current_user_can( 'administrator' ) );
	return apply_filters( 'el_can_manage_ticket', $el_manage_ticket );
}


/**
 * Check user or vendor
 */
function el_is_vendor(){

	$user = wp_get_current_user();
	$allowed_roles = array( 'administrator', 'el_event_manager' );

	$vendor = false;
	if( array_intersect( $allowed_roles, $user->roles ) ){
		$vendor = true;
	}
	return apply_filters( 'el_is_vendor', $vendor );
	
}

if ( ! function_exists("el_is_administrator") ) {
	function el_is_administrator(){
		$user = wp_get_current_user();
		$allowed_roles = array( 'administrator' );
		return array_intersect( $allowed_roles, $user->roles );
	}
}

/**
 * get all vendors
 */
function el_get_all_authors( $role = 'el_event_manager', $paged = 1, $name = '' ){
	// WP_User_Query arguments
	$args = array (
	    'role' => $role,
	    'order' => 'ASC',
	    'orderby' => 'display_name',
	    'paged' => $paged,
	    'number' => apply_filters( 'number_authors_per_page', 18 )
	);

	if ( $name ) {
		$args['search'] 	= '*'. esc_attr( $name ) .'*';
		$args['meta_query'] = array(
        	'relation' => 'OR',
	        array(
	            'key'     => 'first_name',
	            'value'   => $name,
	            'compare' => 'LIKE'
	        ),
	        array(
	            'key'     => 'last_name',
	            'value'   => $name,
	            'compare' => 'LIKE'
	        ),
	    );
	}

	// Create the WP_User_Query object
	$wp_user_query = new WP_User_Query($args);

	return $wp_user_query;

}

/**
 * get info of author
 */
function el_get_author_info( $author_id ){

	$author_id_image = get_user_meta( $author_id, 'author_id_image', true ) ? get_user_meta( $author_id, 'author_id_image', true ) : '';
	$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'large') ) ? wp_get_attachment_image_url($author_id_image, 'large') : EL_PLUGIN_URI.'assets/img/unknow_user.png';

	$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );
	$user_phone = get_user_meta( $author_id, 'user_phone', true ) ? get_user_meta( $author_id, 'user_phone', true ) : '';
	$user_profile_social = get_user_meta( $author_id, 'user_profile_social', true ) ? get_user_meta( $author_id, 'user_profile_social', true ) : '';
	$user_description = get_user_meta( $author_id, 'description', true ) ? get_user_meta( $author_id, 'description', true ) : '';
	$user_address = get_user_meta( $author_id, 'user_address', true ) ? get_user_meta( $author_id, 'user_address', true ) : '';

	$user_email = get_user_meta( $author_id, 'user_email', true ) ? get_user_meta( $author_id, 'user_email', true ) : get_the_author_meta( 'user_email', $author_id );

	$user_job = get_user_meta( $author_id, 'user_job', true ) ? get_user_meta( $author_id, 'user_job', true ) : get_the_author_meta( 'user_job', $author_id );

	

	return array(
		'img_path'	=> $img_path, 
		'display_name' => $display_name,
		'user_phone'	=> $user_phone,
		'user_profile_social'	=> $user_profile_social,
		'user_description'	=> $user_description,
		'user_address'	=> $user_address,
		'user_email'	=> $user_email,
		'user_job'	=> $user_job

	);

}



