<?php defined( 'ABSPATH' ) || exit;

/**
 * Output the start of the page wrapper.
 */
if( !function_exists('el_output_content_wrapper') ){
	function el_output_content_wrapper(){
		el_get_template( 'global/wrapper-start.php' );
	}
}


/**
 * Output the end of the page wrapper.
 */
if( !function_exists('el_output_content_wrapper_end') ){
	function el_output_content_wrapper_end(){
		el_get_template( 'global/wrapper-end.php' );
	}
}


/**
 * Output the breadcrumb
 */
if( !function_exists('el_breadcrumb') ){
	function el_breadcrumb(){
		el_get_template( 'global/breadcrumb.php' );
	}
}


/**
 * Output the pagination
 */
if( !function_exists('el_pagination') ){
	function el_pagination(){
		el_get_template( 'pagination.php' );
	}
}


/**
 * Output Taxonomy Archive description
 */
if( !function_exists('el_taxonomy_archive_description') ){
	function el_taxonomy_archive_description(){
		if ( is_tax( get_object_taxonomies( 'event' ) ) && 0 === absint( get_query_var( 'paged' ) ) || is_tax( get_object_taxonomies( 'event_loc' ) ) ) {
			$term = get_queried_object();

			if ( $term && ! empty( $term->description ) ) {
				echo wp_kses_post( '<div class="term-description">' . $term->description . '</div>' ); // WPCS: XSS ok.
			}
		}
	}
}


/**
 * Output Event Archive description
 */
if( !function_exists('el_event_archive_description') ){
	function el_event_archive_description(){

		
		echo do_shortcode( '[el_search_form type="type3" pos1="name_event" pos2="cat" pos3="loc_state" pos4="loc_city" pos5="all_time" pos6="start_event" pos7="end_event" pos8="venue" pos9="event_type" pos10="range_slider"  /]' );

	}
}


/**
 * Output excerpt
 */
if( !function_exists('el_loop_event_excerpt') ){
	function el_loop_event_excerpt(){
		el_get_template( 'loop/excerpt.php' );
	}
}

/**
 * Display event remove
 */
if( !function_exists('el_loop_event_remove') ){
	function el_loop_event_remove(){
		el_get_template( 'loop/remove.php' );
	}
}


/**
 * Display event thumbnail
 */
if( !function_exists('el_loop_event_thumbnail') ){
	function el_loop_event_thumbnail( $args ){
		el_get_template( 'loop/thumbnail.php', $args );
	}
}

/**
 * Display event author
 */
if( !function_exists('el_loop_event_author') ){
	function el_loop_event_author(){
		el_get_template( 'loop/author.php' );
	}
}

/**
 * Display event favourite
 */
if( !function_exists( 'el_loop_event_favourite' ) ){
	function el_loop_event_favourite(){
		el_get_template( 'loop/favourite.php' );
	}
}

/**
 * Display event el_loop_event_share
 */
if( !function_exists( 'el_loop_event_share' ) ){
	function el_loop_event_share(){
		el_get_template( 'loop/share.php' );
	}
}

/**
 * Display event el_loop_event_share
 */
if( !function_exists( 'el_loop_event_date' ) ){
	function el_loop_event_date(){
		el_get_template( 'loop/date.php' );
	}
}

/**
 * Display event el_loop_event_date_4
 */
if( !function_exists( 'el_loop_event_date_4' ) ){
	function el_loop_event_date_4(){
		el_get_template( 'loop/date4.php' );
	}
}

/**
 * Display event rattings
 */
if( !function_exists( 'el_loop_event_ratting' ) ){
	function el_loop_event_ratting(){
		el_get_template( 'loop/ratting.php' );
	}
}


/**
 * Output title in loop
 */
if( !function_exists('el_loop_event_title') ){
	function el_loop_event_title(){
		el_get_template( 'loop/title.php' );
	}
}

/**
 * Oupt cat in loop
 */
if( !function_exists('el_loop_event_cat') ){
	function el_loop_event_cat(){
		el_get_template( 'loop/cat.php' );
	}
}


/**
 * Oupt cat in loop
 */
if( !function_exists('el_loop_event_cat_3') ){
	function el_loop_event_cat_3(){
		el_get_template( 'loop/cat_3.php' );
	}
}


/**
 * Oupt price in loop
 */
if( !function_exists( 'el_loop_event_price' ) ){
	function el_loop_event_price( $args ){
		el_get_template( 'loop/price.php', $args );
	}
}

/**
 *Output location in loop
 */
if ( ! function_exists( 'el_loop_event_location' ) ) {
	function el_loop_event_location() {
		el_get_template( 'loop/location.php' );
	}
}

/**
 * Output time in loop
 */
if ( ! function_exists( 'el_loop_event_time' )) {
	function el_loop_event_time( $args ) {
		el_get_template( 'loop/time.php', $args );
	}
}

if ( ! function_exists('el_loop_event_feature') ) {
	function el_loop_event_feature( $args ){
		el_get_template( 'loop/feature.php', $args );
	}
}

/**
 * Output status in loop
 */
if ( ! function_exists( 'el_loop_event_status' )) {
	function el_loop_event_status() {
		el_get_template( 'loop/status.php' );
	}
}

/**
 * Output button in loop
 */
if ( ! function_exists( 'el_loop_event_button' )) {
	function el_loop_event_button() {
		el_get_template( 'loop/button.php' );
	}
}



/**
 * Output thumbnail in single
 */
if( !function_exists('el_single_event_thumbnail') ){
	function el_single_event_thumbnail(){
		el_get_template( 'single/thumbnail.php' );
	}
}


/**
 * Oupt number view in single
 */
if( !function_exists('el_single_event_number_view') ){
	function el_single_event_number_view(){
		el_get_template( 'single/number_view.php' );
	}
}

/**
 * Oupt favourite in single
 */
if( !function_exists('el_single_event_favourite') ){
	function el_single_event_favourite(){
		el_get_template( 'single/favourite.php' );
	}
}

/**
 * Oupt bookmark in single
 */
if( !function_exists('el_single_event_bookmark') ){
	function el_single_event_bookmark(){
		el_get_template( 'single/bookmark.php' );
	}
}

/**
 * Oupt share in single
 */
if( !function_exists('el_single_event_share') ){
	function el_single_event_share(){
		el_get_template( 'single/share.php' );
	}
}

/**
 * Oupt title in single
 */
if( !function_exists('el_single_event_title') ){
	function el_single_event_title(){
		el_get_template( 'single/title.php' );
	}
}

/**
 * Oupt act booking in single
 */
if( !function_exists('el_single_act_booking') ){
	function el_single_act_booking(){
		el_get_template( 'single/act_booking.php' );
	}
}

/**
 * Oupt share social in single
 */
if( !function_exists('el_single_share_social') ){
	function el_single_share_social(){
		el_get_template( 'single/share_social.php' );
	}
}

/**
 * Oupt share social in single
 */
if( !function_exists('el_single_report') ){
	function el_single_report(){
		el_get_template( 'single/report.php' );
	}
}

/**
 * Oupt share social in single
 */
if( !function_exists('el_single_calenda_export') ){
	function el_single_calenda_export(){
		el_get_template( 'single/calendar_export.php' );
	}
}

/**
 * Oupt add calendar in single
 */
if( !function_exists('el_single_add_calendar') ){
	function el_single_add_calendar(){
		el_get_template( 'single/add_calendar.php' );
	}
}

/**
 * Oupt export ical in single
 */
if( !function_exists('el_single_export_ical') ){
	function el_single_export_ical(){
		el_get_template( 'single/export_ical.php' );
	}
}

/**
 * Oupt author in single
 */
if( !function_exists('el_single_event_author') ){
	function el_single_event_author(){
		el_get_template( 'single/author.php' );
	}
}

/**
 * Output content
 */
if( !function_exists('el_single_event_content') ){
	function el_single_event_content(){
		el_get_template( 'single/content.php' );
	}
}

/**
 * Output tag
 */
if( !function_exists('el_single_event_tag') ){
	function el_single_event_tag(){
		el_get_template( 'single/tag.php' );
	}
}

/**
 * Output taxonomy
 */
if( !function_exists('el_single_event_taxonomy') ){
	function el_single_event_taxonomy(){
		el_get_template( 'single/taxonomy.php' );
	}
}

/**
 * Output ticket info
 */
if( !function_exists('el_single_event_ticket_info') ){
	function el_single_event_ticket_info(){
		el_get_template( 'single/ticket_info.php' );
	}
}

/**
 * Output ticket calendar
 */
if( !function_exists('el_single_event_ticket_calendar') ){
	function el_single_event_ticket_calendar(){
		el_get_template( 'single/ticket_calendar.php' );
	}
}


/**
 * Output schedules_time
 */
if( !function_exists('el_single_event_schedules_time') ){
	function el_single_event_schedules_time(){
		el_get_template( 'single/schedules_time.php' );
	}
}
/**
 * Output video
 */
if( !function_exists('el_single_event_video') ){
	function el_single_event_video(){
		el_get_template( 'single/video.php' );
	}
}

/**
 * Output video
 */
if( !function_exists('el_single_event_comment') ){
	function el_single_event_comment(){
		el_get_template( 'single/comment.php' );
	}
}

/**
 * Output gallery
 */
if( !function_exists('el_single_event_gallery') ){
	function el_single_event_gallery(){
		el_get_template( 'single/gallery.php' );
	}
}



/**
 * Output Related
 */
if( !function_exists('el_single_event_related') ){
	function el_single_event_related(){
		el_get_template( 'single/related.php' );
	}
}

/**
 * calendar_manage_ticket
 */
if( !function_exists('el_vendor_calendar_manage_ticket') ){
	function el_vendor_calendar_manage_ticket(){
		el_get_template( 'vendor/_calendar_manage_ticket.php' );
	}
}

/**
 * calendar_manage_ticket
 */
if( !function_exists('el_vendor_edit_manage_ticket_max') ){
	function el_vendor_edit_manage_ticket_max(){
		el_get_template( 'vendor/_edit_manage_ticket_max.php' );
	}
}



/**
 * Output header cart
 */
if( !function_exists('el_header_cart') ){
	function el_header_cart(){
		el_get_template( 'cart/header_cart.php' );
	}
}


/**
 * Output type ticket
 */
if( !function_exists('el_cart_ticket_type') ){
	function el_cart_ticket_type(){
		el_get_template( 'cart/ticket_type.php' );
	}
}

/**
 * Output Cart info
 */
if( !function_exists('el_cart_info') ){
	function el_cart_info(){
		el_get_template( 'cart/cart_info.php' );
	}
}

/**
 * Output button discount
 */
if( !function_exists('el_cart_discount') ){
	function el_cart_discount(){
		el_get_template( 'cart/discount_button.php' );
	}
}

/**
 * Output button next step
 */
if( !function_exists('el_cart_next_step_button') ){
	function el_cart_next_step_button(){
		el_get_template( 'cart/next_step_button.php' );
	}
}



if( !function_exists('el_customer_info') ){
	function el_customer_info(){
		el_get_template( 'cart/customer_info.php' );
	}
}

if( !function_exists('el_payment_method') ){
	function el_payment_method(){
		el_get_template( 'cart/payment_method.php' );
	}
}

if( !function_exists('el_customer_input') ){
	function el_customer_input(){
		el_get_template( 'cart/customer_input.php' );
	}
}

if( !function_exists('el_payment_gateways') ){
	function el_payment_gateways(){
		el_get_template( 'cart/payment_gateways.php' );
	}
}

if( !function_exists('el_cart_checkout_button') ){
	function el_cart_checkout_button(){
		el_get_template( 'cart/checkout_button.php' );
	}
}

/**
 * Output Venue filter
 */
if( !function_exists('el_venue_filter_first_letter') ){
	function el_venue_filter_first_letter(){
		el_get_template( 'venue/filter_first_letter.php' );
	}
}

//add filter query by first specific letter (venue)
function get_query_title_like ( $where, $query ) {
	global $wpdb;

	$starts_with = $query->get( 'starts_with' );

	if ( $starts_with ) {
		$where .= " AND $wpdb->posts.post_title LIKE '$starts_with%'";
	}

	return $where;
}
add_filter( 'posts_where', 'get_query_title_like', 10, 2 );

//pagination single venue
add_action( 'template_redirect', function() {
	if ( is_singular( 'venue' ) ) {
		global $wp_query;
		$page = ( int ) $wp_query->get( 'page' );
		if ( $page > 1 ) {
            // convert 'page' to 'paged'
			$query->set( 'page', 1 );
			$query->set( 'paged', $page );
		}
        // prevent redirect
		remove_action( 'template_redirect', 'redirect_canonical' );
	}
}, 0 ); 

if( !function_exists('el_search_result') ){
	function el_search_result(){
		el_get_template( 'search/search-result.php' );
	}
}

if( !function_exists('el_single_event_date') ){
	function el_single_event_date(){
		el_get_template( 'single/date.php' );
	}
}

if( !function_exists('el_single_event_address') ){
	function el_single_event_address(){
		el_get_template( 'single/address.php' );
	}
}

if( !function_exists('el_single_event_banner') ){
	function el_single_event_banner(){
		el_get_template( 'single/banner.php' );
	}
}

if( !function_exists('el_single_event_policy') ){
	function el_single_event_policy(){
		el_get_template( 'single/policy.php' );
	}
}

if( !function_exists('el_single_event_map') ){
	function el_single_event_map(){
		el_get_template( 'single/map.php' );
	}
}

if( !function_exists('el_author_info') ){
	function el_author_info(){
		el_get_template( 'author_info.php' );
	}
}

if( !function_exists('el_schema') ){
	function el_schema(){
		el_get_template( 'single/schema.php' );
	}
}

// Delete ticket when the customer cancel booking/order
add_action( 'el_cancel_booking_succesfully', 'el_delete_ticket', 10, 1 );
function el_delete_ticket( $booking_id ){

	$args = array(
		'post_type' 	=> 'el_tickets',
		'post_status' 	=> 'publish',
		'numberposts' 	=> '-1',
		'fields'		=> 'ids',
		'meta_query' 	=> array(
			array(
				'key' 		=> OVA_METABOX_EVENT . 'booking_id',
				'value' 	=> $booking_id,
				'compare'	=> '=',
			)
		)
		
	);

	$ticket_ids = get_posts( $args );
	if( $ticket_ids ){
		foreach ($ticket_ids as $id) {
			wp_delete_post( $id );
		}
		
	}
}


if( !function_exists('el_terms_condition') ){
	function el_terms_condition(){
		el_get_template( 'cart/terms_condition.php' );
	}
}




if ( ! function_exists('el_seating_map') ) {
	function el_seating_map(){
		el_get_template( 'cart/seating-map.php' );
	}
}


add_filter( 'el_display_image_thumbnail', 'el_display_img_card_event', 10, 2 );
if ( ! function_exists('el_display_img_card_event') ) {
	function el_display_img_card_event( $options, $args ){
		$display_img = isset( $args['display_img'] ) ? $args['display_img'] : $options;
		return apply_filters( 'el_display_img_card_event', $display_img, $args );
	}
}


add_filter( 'el_event_display_price_opt', 'el_event_display_price', 10, 2 );
if ( ! function_exists('el_event_display_price') ) {
	function el_event_display_price( $options, $args ){
		$display_price = isset( $args['display_price'] ) ? $args['display_price'] : $options;
		return apply_filters( 'el_event_display_price', $display_price, $args );
	}
}

add_filter( 'el_free_booking_event_url_thankyou', 'el_thank_url_with_order_key', 10, 3 );
add_filter( 'el_offline_booking_event_url_thankyou', 'el_thank_url_with_order_key', 10, 3 );

if ( ! function_exists('el_thank_url_with_order_key') ) {
	function el_thank_url_with_order_key( $url, $status, $booking_id ){
		$order_key = get_post_meta( $booking_id, OVA_METABOX_EVENT.'order_key', true );
		$url = add_query_arg( 'key', $order_key, $url );
		return apply_filters( 'el_thank_url_with_order_key', $url, $status, $booking_id );
	}
}

// Add order key to redirect url stripe
add_filter( 'el_redirect_url_stripe', function( $url, $booking_id ){
	$order_key = get_post_meta( $booking_id, OVA_METABOX_EVENT.'order_key', true );
	$url = add_query_arg( 'key', $order_key, $url );
	return apply_filters( 'el_redirect_url_stripe_order_key', $url, $booking_id );
}, 10, 2 );

add_filter( 'el_redirect_url_paypal', function( $url, $booking_id ){
	$order_key = get_post_meta( $booking_id, OVA_METABOX_EVENT.'order_key', true );
	$url = add_query_arg( 'key', $order_key, $url );
	return apply_filters( 'el_redirect_url_paypal_order_key', $url, $booking_id );
}, 10, 2 );

// Timezone required
add_filter( 'el_timezone_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['timezone']['required'] ) ) {
		$value = $args['event_req_field']['timezone']['required'];
	}
	return apply_filters( 'el_timezone_req_val', $value, $args );
}, 10, 2 );

// Event tag required
add_filter( 'el_event_tag_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['event_tag']['required'] ) ) {
		$value = $args['event_req_field']['event_tag']['required'];
	}
	return apply_filters( 'el_event_tag_req_val', $value, $args );
}, 10, 2 );

// Description required
add_filter( 'el_description_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['description']['required'] ) ) {
		$value = $args['event_req_field']['description']['required'];
	}
	return apply_filters( 'el_description_req_val', $value, $args );
}, 10, 2 );

// Venue required
add_filter( 'el_venue_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['event_venue']['required'] ) ) {
		$value = $args['event_req_field']['event_venue']['required'];
	}
	return apply_filters( 'el_venue_req_val', $value, $args );
}, 10, 2 );

// Image feature required
add_filter( 'el_image_feature_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['img_thumbnail']['required'] ) ) {
		$value = $args['event_req_field']['img_thumbnail']['required'];
	}
	return apply_filters( 'el_image_feature_req_val', $value, $args );
}, 10, 2 );

// Gallery required
add_filter( 'el_gallery_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['event_gallery']['required'] ) ) {
		$value = $args['event_req_field']['event_gallery']['required'];
	}
	return apply_filters( 'el_gallery_req_val', $value, $args );
}, 10, 2 );

// Video required
add_filter( 'el_video_req', function( $value, $args ){
	if ( is_array( $args ) && isset( $args['event_req_field']['event_video']['required'] ) ) {
		$value = $args['event_req_field']['event_video']['required'];
	}
	return apply_filters( 'el_video_req_val', $value, $args );
}, 10, 2 );