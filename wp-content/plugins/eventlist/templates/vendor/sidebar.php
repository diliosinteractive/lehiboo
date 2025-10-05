<?php if ( !defined( 'ABSPATH' ) ) exit();  
$vendor = isset( $_GET['vendor'] ) ? $_GET['vendor'] :  apply_filters( 'el_manage_vendor_default_page', 'general' );

$user_id = wp_get_current_user()->ID;


$author_id_image = get_user_meta( $user_id, 'author_id_image', true ) ? get_user_meta( $user_id, 'author_id_image', true ) : '';

$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ) ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : EL_PLUGIN_URI.'assets/img/unknow_user.png';

$display_name = get_user_meta( $user_id, 'display_name', true ) ? get_user_meta( $user_id, 'display_name', true ) : get_the_author_meta( 'display_name', $user_id );

$allow_transfer_ticket = EL()->options->ticket_transfer->get('allow_transfer_ticket','');


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
			<a href="javascript:void(0)"><i class="fa fa-bars" ></i></a>
		</div>

		<ul class="dashboard_nav">

			<?php if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_general', true ) ){ ?>
				<li class="menu_vendor_general <?php if ($vendor == 'general') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg( 
									array( 
										'vendor' => 'general'
									),
									get_myaccount_page() ); ?>">
						<i class="icon_house_alt"></i>
						<?php esc_html_e( 'General', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			<?php if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_my_listing', true ) ){ ?>
				<li class="menu_vendor_mylisting <?php if ($vendor == 'listing' || $vendor == 'listing-edit' ) echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg( 
									array( 
										'vendor' => 'listing',
										'listing_type' => 'any'
									),
									get_myaccount_page() ); ?>">
						<i class="icon_document"></i>
						<?php esc_html_e( 'My Listings', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			<?php if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_create_event', true ) ){ ?>
				<li class="menu_vendor_create_event <?php if ($vendor == 'create-event') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg( 
										array( 
											'vendor' => 'create-event'
										),
										get_myaccount_page() ); ?>">
						<i class="icon_plus_alt"></i>
						<?php esc_html_e( 'Create Event', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			<?php if( EL()->options->package->get('enable_package', 'no') == 'yes' && el_is_vendor() && apply_filters( 'el_manage_vendor_show_package', true ) && ! el_is_administrator() && ! el_hide_package_menu_item() ){ ?>
				<li class="menu_vendor_package <?php if ($vendor == 'package') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg( 
									array( 
										'vendor' => 'package',
									),
									get_myaccount_page() ); ?>">
						<i class="icon_gift"></i>
						<?php esc_html_e( 'Package', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			
		
			<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' && apply_filters( 'el_manage_vendor_show_mybooking', true ) ) { ?>
				<li class="menu_vendor_mybookings <?php if ($vendor == 'mybookings') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg(
									array( 
										'vendor' => 'mybookings'
									),
									get_myaccount_page() ); ?>">
						<i class="icon_archive"></i><?php esc_html_e( 'My Bookings', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			<?php if ( $allow_transfer_ticket ): ?>
				
				<li class="menu_vendor_tickets_received <?php if ($vendor == 'tickets_received') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg(
									array( 
										'vendor' => 'tickets_received'
									),
									get_myaccount_page() ); ?>">
						<i class="icon_archive"></i><?php esc_html_e( 'Tickets Received', 'eventlist' ); ?>
					</a>
				</li>

			<?php endif; ?>

			<?php if ( EL()->options->tax_fee->get('manage_profit', 'profit_1') == 'profit_2' && el_is_vendor() && apply_filters( 'el_manage_vendor_show_wallet', true) ) { ?>
					<li class="menu_vendor_wallet <?php if ($vendor == 'wallet') echo esc_attr('active');  ?>">
						<a href="<?php echo add_query_arg(
										array( 
											'vendor' => 'wallet'
										),
										get_myaccount_page() ); ?>">
							<i class=" icon_wallet_alt"></i><?php esc_html_e( 'Wallet', 'eventlist' ); ?>
						</a>
					</li>
			<?php } ?>

			
			<?php if( apply_filters( 'el_manage_vendor_show_wishlist', true ) ){ ?>
				<li class="menu_vendor_mywishlist <?php if ($vendor == 'wishlist') echo esc_attr('active');  ?>">
					<a href="<?php echo add_query_arg( 
									array( 
										'vendor' => 'wishlist',
									),
									get_myaccount_page() ); ?>">
						<i class="icon_heart"></i><?php esc_html_e( 'My WishList', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

			<li class="menu_vendor_myprofile <?php if ($vendor == 'profile') echo esc_attr('active');  ?>">
				<a href="<?php echo add_query_arg(
								array( 
									'vendor' => 'profile'
								),
								get_myaccount_page() ); ?>">
					<i class="icon_profile"></i><?php esc_html_e( 'My Profile', 'eventlist' ); ?>
				</a>
			</li>
			
			<?php if( is_user_logged_in() ) { ?>
				<li class="menu_vendor_logout">
					<a href="<?php echo apply_filters( 'el_logout_url' ,wp_logout_url() ); ?>">
						<i class="icon_lock-open"></i>
						<?php esc_html_e( 'Logout', 'eventlist' ); ?>
					</a>
				</li>
			<?php } ?>

		</ul>
	</div>
</div>