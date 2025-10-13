<?php 
if ( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

$event_req_field = apply_filters( 'el_event_req_field', array(
	'event_name' => array(
		'required' => true,
		'message'  => __( 'Event name is required', 'eventlist' )
	),
	'event_cat' => array(
		'required' 	=> true,
		'message' 	=> __( 'Category is required', 'eventlist' ),
	),
	'description' => array(
		'required' 	=> false,
		'message' 	=> __( 'Description is required', 'eventlist' ),
	),
	'img_thumbnail' => array(
		'required' 	=> false,
		'message' 	=> __( 'Image feature is required', 'eventlist' ),
	),
	'timezone' => array(
		'required' 	=> false,
		'message' 	=> __( 'Timezone is required', 'eventlist' ),
	),
	'event_tag' => array(
		'required' 	=> false,
		'message' 	=> __( 'Tag is required', 'eventlist' ),
	),
	'event_venue' => array(
		'required' 	=> false,
		'message' 	=> __( 'Venue name is required', 'eventlist' )
	),
	'event_gallery' => array(
		'required' 	=> false,
		'message' 	=> __( 'Gallery is required', 'eventlist' )
	),
	'event_video' => array(
		'required' 	=> false,
		'message' 	=> __( 'Video is required', 'eventlist' ),
	),
) );

?>


<div class="vendor_wrap">

	<div class="sidebar">
		<?php echo el_get_template( 'vendor/sidebar.php' ); ?>
	</div>

	<div class="contents">

		<!-- Heading caché car on utilise le titre dans la sticky bar -->
		<div style="display: none;"><?php echo el_get_template( '/vendor/heading.php' ); ?></div>

		<div class="vendor_edit_event">

			<?php if( $post_id ){
				$action_event = el_can_edit_event();
			}else{
				$action_event = el_can_add_event();
			}?>
			<?php if( $action_event ) : ?>

				<!-- Barre sticky avec boutons Aperçu et Enregistrer -->
				<div class="event_form_sticky_bar">
					<div class="sticky_bar_inner">
						<div class="sticky_bar_left">
							<h3><?php echo $post_id ? esc_html__( 'Modifier l\'événement', 'eventlist' ) : esc_html__( 'Créer un événement', 'eventlist' ); ?></h3>
						</div>
						<div class="sticky_bar_right">
							<?php if( $post_id ){ ?>
								<a class="btn_preview" target="_blank" href="<?php echo get_preview_post_link( $post_id ); ?>">
									<i class="icon_search"></i>
									<span><?php esc_html_e( 'Aperçu', 'eventlist' ); ?></span>
								</a>
							<?php } ?>
							<button type="submit" form="event_edit_form" class="btn_save_event" name="el_edit_event_submit">
								<i class="icon_check"></i>
								<span><?php esc_html_e( 'Enregistrer', 'eventlist' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<form id="event_edit_form" action="<?php echo esc_url( home_url('/') ); ?>" method="post" enctype="multipart/form-data" class="event_form_wrapper" autocomplete="off" autocorrect="off" autocapitalize="none"
					data-required="<?php echo esc_attr( json_encode( $event_req_field ) ); ?>">
					<input type="hidden" value="<?php echo esc_attr( $post_id ); ?>" id="el_post_id" name="el_post_id"/>
					<?php wp_nonce_field( 'el_edit_event_nonce', 'el_edit_event_nonce' ); ?>

					<!-- Navigation verticale à gauche (comme profil) -->
					<div class="profile_navigation_sidebar">
						<nav class="profile_tabs_nav">
							<ul>
								<li class="profile_tab_item active" data-tab="mb_basic">
									<a href="#mb_basic">
										<i class="icon_document_alt"></i>
										<span><?php esc_html_e( 'Informations de base', 'eventlist' ); ?></span>
									</a>
								</li>

								<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
									<li class="profile_tab_item" data-tab="mb_ticket">
										<a href="#mb_ticket">
											<i class="icon_tag_alt"></i>
											<span><?php esc_html_e( 'Billets', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php	} ?>

								<?php if( apply_filters( 'el_create_event_show_calendar_tab', true ) ){ ?>
									<li class="profile_tab_item" data-tab="mb_calendar">
										<a href="#mb_calendar">
											<i class="icon_calendar"></i>
											<span><?php esc_html_e( 'Calendrier', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php } ?>


								<?php /*
								if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' && apply_filters( 'el_edit_event_show_coupon', true ) ) { ?>
									<li class="profile_tab_item" data-tab="mb_coupon">
										<a href="#mb_coupon">
											<i class="icon_percent"></i>
											<span><?php esc_html_e( 'Coupon', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php } ?>

								<?php if( apply_filters( 'el_create_event_show_member_tab', true ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ){ ?>
									<li class="profile_tab_item" data-tab="mb_api_key">
										<a href="#mb_api_key">
											<i class="icon_group"></i>
											<span><?php esc_html_e( 'Staff Member', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php } ?>

								<?php if ( EL()->options->cancel->get('cancel_enable', 1 ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
									<li class="profile_tab_item" data-tab="mb_cancel_booking">
										<a href="#mb_cancel_booking">
											<i class="icon_close"></i>
											<span><?php esc_html_e( 'Cancel booking', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php } */ ?>
								<?php if ( apply_filters( 'el_create_event_show_extra_service_tab', true ) == true ): ?>
									<li class="profile_tab_item" data-tab="mb_extra_service">
										<a href="#mb_extra_service">
											<i class="icon_star"></i>
											<span><?php esc_html_e( 'Services Extra', 'eventlist' ); ?></span>
										</a>
									</li>
								<?php endif; ?>
							</ul>
						</nav>
					</div>

					<!-- Contenu des onglets -->
					<div class="profile_content_area">

						<div id="mb_basic" class="tab-contents">
						<?php echo el_get_template( '/vendor/__edit-event-basic.php', array( 'event_req_field' => $event_req_field ) ); ?>
					</div>

					<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
						<div id="mb_ticket" class="tab-contents">
							<?php echo el_get_template( '/vendor/__edit-event-ticket.php' ); ?>
						</div>
					<?php	} ?>

					<?php if( apply_filters( 'el_create_event_show_calendar_tab', true ) ){ ?>
						<div id="mb_calendar" class="tab-contents">
							<?php echo el_get_template( '/vendor/__edit-event-calendar.php' ); ?>
						</div>
					<?php } ?>

					<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' && apply_filters( 'el_edit_event_show_coupon', true ) ) { ?>
						<div id="mb_coupon" class="tab-contents">
							<?php echo el_get_template( '/vendor/__edit-event-coupon.php' ); ?>
						</div>
					<?php	} ?>
					
					<?php if( apply_filters( 'el_create_event_show_member_tab', true ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ){ ?>
						<div id="mb_api_key" class="tab-contents">
							<?php  echo el_get_template( '/vendor/__edit-event-api-key.php' ); ?>
						</div>
					<?php } ?>

					<?php if ( EL()->options->cancel->get('cancel_enable', 1 ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
						<div id="mb_cancel_booking" class="tab-contents">
							<?php  echo el_get_template( '/vendor/__edit-event-cancel-booking.php' ); ?>
						</div>
					<?php } ?>
					<?php if ( apply_filters( 'el_create_event_show_extra_service_tab', true ) == true ): ?>
						<div id="mb_extra_service" class="tab-contents">
							<?php echo el_get_template( '/vendor/__edit-event-extra-service.php' ); ?>
						</div>
						<?php endif; ?>
						<?php echo apply_filters( 'meup_send_create_event_recapcha', '' ); ?>

						<!-- Loader pour la sticky bar -->
						<div class="submit-load-more sendmail" style="display: none;">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>

						<p class="error-total-event"><?php echo esc_html_e('You should upgrade to high package because your current package is limit number events', 'eventlist') ?></p>
						<p class="error-time-limit"><?php echo esc_html_e('Your package time is expired', 'eventlist') ?></p>

					</div> <!-- End profile_content_area -->
				</form>

			<?php else: 
				esc_html_e( 'You don\'t have permission add new event', 'eventlist' );
			endif; ?>

		</div>

	</div>

</div>