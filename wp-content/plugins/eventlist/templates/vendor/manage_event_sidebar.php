<?php if ( !defined( 'ABSPATH' ) ) exit();  
	$active = isset( $_GET['vendor'] ) ? $_GET['vendor'] : '';
	$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : '';
	$eid = isset( $_GET['eid'] ) ? sanitize_text_field($_GET['eid']) : '';


	$user_id = wp_get_current_user()->ID;

	$author_id_image = get_user_meta( $user_id, 'author_id_image', true ) ? get_user_meta( $user_id, 'author_id_image', true ) : '';

	$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ) ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : EL_PLUGIN_URI.'assets/img/unknow_user.png';
	
	$display_name = get_user_meta( $user_id, 'display_name', true ) ? get_user_meta( $user_id, 'display_name', true ) : get_the_author_meta( 'display_name', $user_id );

	$check_allow_get_list_attendees = check_allow_get_list_attendees_by_event($eid);

	$check_allow_get_list_tickets 	= check_allow_get_list_tickets_by_event($eid);
	
?>
<div class="vendor_sidebar">
	<div class="vendor_sidebar_inner">
		<div class="vendor_user_profile">
			<div class="wrap_image">
				<img class="user_image" src="<?php echo esc_url($img_path); ?>" alt="<?php echo $display_name; ?>">
			</div>
			<div>
				<p class="display_name"><?php echo esc_html( $display_name ); ?></p>
				<a href="<?php echo add_query_arg( array( 'vendor' => 'profile' ), get_myaccount_page() ); ?>" class="edit_profile">
					<?php esc_html_e( 'Edit Profile', 'eventlist' ); ?>
				</a>
			</div>
		</div>

		<div class="el_vendor_mobile_menu">
			<a href="javascript:void(0)"><i class="fa fa-bars"></i></a>
		</div>

		<ul class="dashboard_nav">
			
			<li class="menu_event_my_listing">
				<a href="<?php echo add_query_arg( 
									array( 
										'vendor' 		=> 'listing',
										'listing_type'	=> 'all'
									),
									get_myaccount_page() ); ?>">
					<i class="icon_table"></i>
					<?php esc_html_e( 'My Listings', 'eventlist' ); ?>
				</a>
			</li>

			<li class="menu_event_general <?php if ($active == 'manage_event' && !$tab ) echo esc_attr('active');  ?>">
				<a href="<?php echo add_query_arg( 
									array( 
										'vendor' => 'manage_event',
										'eid'	=> $eid
									),
									get_myaccount_page() ); ?>">
					<i class="icon_house_alt"></i>
					<?php esc_html_e( 'General', 'eventlist' ); ?>
				</a>
			</li>
			<?php if ( $check_allow_get_list_attendees == 'yes' ) : ?>
			<li class="menu_event_bookings <?php if ($active == 'manage_event' && $tab == 'bookings' ) echo esc_attr('active');  ?>">
				<a href="<?php echo add_query_arg( 
									array( 
										'vendor' 	=> 'manage_event',
										'tab' 		=> 'bookings',
										'eid'		=> $eid
									),
									get_myaccount_page() ); ?>">
					<i class="icon_documents_alt"></i>
					<?php esc_html_e( 'Bookings', 'eventlist' ); ?>
				</a>
			</li>
			<?php endif ?>
			
			<?php if( $check_allow_get_list_tickets == 'yes' ) : ?>
			<li class="menu_event_tickets <?php if ($active == 'manage_event' && $tab == 'tickets' ) echo esc_attr('active');  ?>">
				<a href="<?php echo add_query_arg( 
									array( 
										'vendor' 	=> 'manage_event',
										'tab' 		=> 'tickets',
										'eid'		=> $eid
									),
									get_myaccount_page() ); ?>">
					<i class="icon_document_alt"></i>
					<?php esc_html_e( 'Tickets', 'eventlist' ); ?>
				</a>
			</li>
			<?php endif ?>
			
		</ul>
	</div>
</div>