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

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<?php if( $post_id ){ ?>
			<div class="preview_event">
				<a target="_blank" href="<?php echo get_preview_post_link( $post_id ); ?>">
					<?php esc_html_e( 'Preview Event','eventlist' ); ?>
				</a>
			</div>
			<br><br>
		<?php } ?>

		<div class="vendor_edit_event">

			<?php if( $post_id ){
				$action_event = el_can_edit_event();
			}else{
				$action_event = el_can_add_event();
			}?>
			<?php if( $action_event ) : ?>

				<form action="<?php echo esc_url( home_url('/') ); ?>" method="post" enctype="multipart/form-data" class="content" autocomplete="off" autocorrect="off" autocapitalize="none"
					data-required="<?php echo esc_attr( json_encode( $event_req_field ) ); ?>">
					<input type="hidden" value="<?php echo esc_attr( $post_id ); ?>" id="el_post_id" name="el_post_id"/>

					<ul class="vendor_tab">
						
						<li data-id="mb_basic">
							<a href="#mb_basic"><?php esc_html_e( 'Basic', 'eventlist' ); ?></a>
						</li>

						<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
							<li data-id="mb_ticket">
								<a href="#mb_ticket"><?php esc_html_e( 'Ticket', 'eventlist' ); ?></a>
							</li>
						<?php	} ?>

						<?php if( apply_filters( 'el_create_event_show_calendar_tab', true ) ){ ?>
							<li data-id="mb_calendar">
								<a href="#mb_calendar"><?php esc_html_e( 'Calendar', 'eventlist' ); ?></a>
							</li>
						<?php } ?>
						
						<?php if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' && apply_filters( 'el_edit_event_show_coupon', true ) ) { ?>
							<li data-id="mb_coupon">
								<a href="#mb_coupon"><?php esc_html_e( 'Coupon', 'eventlist' ); ?></a>
							</li>
						<?php } ?>
						
						<?php if( apply_filters( 'el_create_event_show_member_tab', true ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ){ ?>
							<li data-id="mb_api_key">
								<a href="#mb_api_key"><?php esc_html_e( 'Staff Member', 'eventlist' ); ?></a>
							</li>
						<?php } ?>

						<?php if ( EL()->options->cancel->get('cancel_enable', 1 ) && EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) { ?>
							<li data-id="mb_cancel_booking">
								<a href="#mb_cancel_booking"><?php esc_html_e( 'Cancel booking', 'eventlist' ); ?></a>
							</li>
						<?php } ?>
						<?php if ( apply_filters( 'el_create_event_show_extra_service_tab', true ) == true ): ?>
							<li data-id="mb_extra_service">
								<a href="#mb_extra_service"><?php esc_html_e( 'Extra Services', 'eventlist' ); ?></a>
							</li>
						<?php endif; ?>

					</ul>

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

					<div class="wrap_btn_submit">
						<input class="el_edit_event_submit el_btn_add" name="el_edit_event_submit" type="submit" value="<?php esc_html_e( 'Save Event', 'eventlist' ); ?>" />
						<?php wp_nonce_field( 'el_edit_event_nonce', 'el_edit_event_nonce' ); ?>
						<div class="submit-load-more sendmail">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>
					</div>

					<p class="error-total-event"><?php echo esc_html_e('You should upgrade to high package because your current package is limit number events', 'eventlist') ?></p>
					<p class="error-time-limit"><?php echo esc_html_e('Your package time is expired', 'eventlist') ?></p>

					
				</form>

			<?php else: 
				esc_html_e( 'You don\'t have permission add new event', 'eventlist' );
			endif; ?>

		</div>

	</div>

</div>