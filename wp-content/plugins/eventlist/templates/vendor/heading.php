<?php if ( !defined( 'ABSPATH' ) ) exit(); 

	$vendor = isset( $_GET['vendor'] ) ? sanitize_text_field( $_GET['vendor'] ) : apply_filters( 'el_manage_vendor_default_page', 'general' );
	$listing_type = isset( $_GET['listing_type'] ) ? sanitize_text_field( $_GET['listing_type'] ) : '';
	$id 	= isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : '';
	$eid 	= isset( $_GET['eid'] ) ? sanitize_text_field( $_GET['eid'] ) : '';
	$tab 	= isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';

	$title = '';

	if( $vendor == 'listing' && $listing_type == 'all' ){
		$title = esc_html__( 'My Listings', 'eventlist' );
	}else if( $vendor == 'listing' && $listing_type == 'publish' ){
		$title = esc_html__( 'My Publish Events', 'eventlist' );
	}else if( $vendor == 'listing' && $listing_type == 'pending' ){
		$title = esc_html__( 'My Pending Events', 'eventlist' );
	}else if( $vendor == 'listing' && $listing_type == 'trash' ){
		$title = esc_html__( 'My Trash Events', 'eventlist' );
	}else if( $vendor == 'listing' && $listing_type == 'pass' ){
		$title = esc_html__( 'My Pass Events', 'eventlist' );
	}else if( $vendor == 'listing-edit' && $id != '' ){
		$event 			= get_post( $id );
		$event_status 	= $event->post_status;
		$event_active 	= get_post_meta( $id, OVA_METABOX_EVENT.'event_active', true );
		$title = esc_html__( 'Edit Event', 'eventlist' );
		if ( $event_status === 'pending' && $event_active == 0 ) {
			$title = esc_html__( 'Edit Event Awaiting Review', 'eventlist' );
		}
	}else if( $vendor == 'listing-edit' && $id == '' ){
		$title = esc_html__( 'Make new an event', 'eventlist' );
	}else if( $vendor == 'create-event' ){
		$title = esc_html__( 'Make new an event', 'eventlist' );
	}else if( $vendor == 'package' ){
		$title = esc_html__( 'All Packages', 'eventlist' );
	}else if( $vendor == 'general'){
		$title = esc_html__( 'General', 'eventlist' );
	}else if( $vendor == 'mybookings' ){
		$title = esc_html__( 'My Bookings', 'eventlist' );
	}else if( $vendor == 'tickets_received' ){
		$title = esc_html__( 'Tickets Received', 'eventlist' );
	}else if( $vendor == 'wallet' ){
		$title = esc_html__( 'Wallet', 'eventlist' );
	}else if( $vendor == 'wishlist' ){
		$title = esc_html__( 'My Wishlist', 'eventlist' );
	}else if( $vendor == 'profile' ){
		$title = esc_html__( 'My Profile', 'eventlist' );
	}else if( $vendor == 'manage_event' && $eid && $tab == ''){
		$title = esc_html__( 'General', 'eventlist' );
	}else if( $vendor == 'manage_event' && $eid && $tab == 'bookings'){
		$title = esc_html__( 'Bookings', 'eventlist' );
	}else if( $vendor == 'manage_event' && $eid && $tab == 'tickets'){
		$title = esc_html__( 'Tickets', 'eventlist' );
	}else{
		$title = esc_html__( 'My Listings', 'eventlist' );
	}
	

?>
<h3 class="vendor_heading">
	<?php echo $title; ?>
</h3>

