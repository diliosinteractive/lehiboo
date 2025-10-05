<?php if( ! defined( 'ABSPATH' ) ) exit();

if ( ! isset( $_POST['data'] ) ) wp_die();

$post_data 		= $_POST['data'];
$time_value 	= isset( $post_data['time_value'] ) ? sanitize_text_field( $post_data['time_value'] ) : '';
$id 			= isset( $post_data['ide'] ) ? sanitize_text_field( $post_data['ide'] ) : '';
$date_format 	= get_option('date_format');

?>

<div id="popup_schedule_time" class="modal schedules_form">
 	<div class="modal-content">
 		<span class="close"><span class="icon_close_alt2"></span></span>
 		<form  class="form-schedules">
 			<h3 class="second_font heading title_form-schedules">
 				<?php esc_html_e( 'Schedules Time', 'eventlist' ); ?>
 			</h3>
 			<div class="time_form-schedules">
 				<?php echo wp_kses_post( date_i18n('l', $time_value).', '.date_i18n( $date_format, $time_value ) ); ?>
 			</div>
 			<?php
	 			$show_remaining_tickets = EL()->options->event->get('show_remaining_tickets', 'yes');
	 			$schedules_time 		= get_post_meta( $id, OVA_METABOX_EVENT . 'schedules_time', true );
	 			$list_type_ticket 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true );
	 			$calendar_recurrence 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence', true );
	 			$seat_option 			= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true );
	 			$recurrence_frequency 	= get_post_meta( $id, OVA_METABOX_EVENT . 'recurrence_frequency', true );
				$ts_start 				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_start', true );
				$ts_end 				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_end', true );
				$ticket_link 			= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true );
				$ticket_external_link 	= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link', true );

				// Time Slot
				$is_timeslot = false;

				if ( $recurrence_frequency === 'weekly' && ! empty( $ts_start ) && ! empty( $ts_end ) ) {
					$is_timeslot = true;
				}

				// External link
				$target = '';

				if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) {
					$target = ' target="_blank"';
				}

	 			if ( $calendar_recurrence ) {
	 				foreach ( $calendar_recurrence as $key_rec => $value_rec ) {
	 					if ( $is_timeslot ) {
	 						foreach ( $ts_start as $ts_key => $ts_value ) {
								if ( ! empty( $ts_value ) && is_array( $ts_value ) ) {
									foreach ( $ts_value as $ts_key_time => $ts_time ) {
										if ( $value_rec['calendar_id'] == $time_value.$ts_key.$ts_key_time ) {
											$start_time = isset( $value_rec['start_time'] ) ? el_get_time_int_by_date_and_hour( gmdate('d-m-Y', $time_value), $value_rec['start_time'] ) : '';
			 								$end_time 	= isset( $value_rec['end_time'] ) ? el_get_time_int_by_date_and_hour( gmdate('d-m-Y', $time_value), $value_rec['end_time']) : '';
			 								$number_time = isset( $value_rec['book_before'] ) ? floatval( $value_rec['book_before'] )*60 : '0';

			 								$status = false;

			 								if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id ) ) {
			 									$status = true;
			 								}

			 								?>

			 								<div class="content_schedules" data-id_schedules="<?php echo esc_attr( $value_rec['calendar_id'] ); ?>">
			 									<div class="content_time">
			 										<h6 class = "time_schedules">
			 											<?php echo esc_html( gmdate( get_option('time_format') , strtotime( $value_rec['start_time'] ) ) ); ?>
				 										<span class="to">
				 											&nbsp;&nbsp;<?php echo esc_html__( 'to', 'eventlist' )?>&nbsp;&nbsp;
				 										</span>
				 										<?php echo esc_html( gmdate(get_option('time_format'), strtotime( $value_rec['end_time'] ) ) ); ?>
			 									    </h6>
			 									    <?php if( apply_filters( 'el_show_ticket_remaining_popup', true ) ){ ?>
				 									    <div class="number_ticket_remaning " >
				 									    	<div class="submit-load-more">
				 									    		<div class="load-more">
				 									    			<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
				 									    		</div>
				 									    	</div>
				 									    </div>
			 										<?php } ?>
			 								    </div>
			 								<?php

			 								$link = add_query_arg( array( 'ide' => $id, 'idcal' => $time_value.$ts_key.$ts_key_time ), get_cart_page() );

			 								if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) {
												$link = esc_url( $ticket_external_link );
											}

			 								if ( $status ) { ?>
			 									<a class='booking_schedules_time' href="<?php echo esc_url( $link ); ?>"<?php echo esc_attr($target); ?>>
			 										<?php esc_html_e( 'Book Now', 'eventlist'); ?>
			 									</a>
			 								<?php } else { ?>
			 									<span class="booking_schedules_time close_schedules_time">
			 									<?php 
			 										$event = new EL_Event();
			 										echo wp_kses_post( $event->get_status_event_calendar( $start_time, $end_time, $number_time, $id ) );
			 									?>
			 									</span>
			 								<?php } ?>
			 								</div>
			 								<?php
										}
									}
								}
							}
	 					} else {
	 						if ( $schedules_time ) {
		 						foreach ( $schedules_time as $key => $value ) {
		 							$total_number_ticket_rest = 0;

		 							if ( $total_number_ticket_rest == 1 ) {
		 								$ticket_text = esc_html__( 'ticket', 'eventlist' );
		 							} else {
		 								$ticket_text = esc_html__( 'tickets', 'eventlist' );
		 							}

		 							if ( $value_rec['calendar_id'] == $time_value.$key ) {
		 								$start_time = isset( $value['start_time'] ) ? el_get_time_int_by_date_and_hour( gmdate('d-m-Y', $time_value), $value['start_time']) : '';
		 								$end_time 	= isset( $value['end_time'] ) ? el_get_time_int_by_date_and_hour( gmdate('d-m-Y', $time_value), $value['end_time']) : '';
		 								$number_time = isset( $value['book_before'] ) ? floatval($value['book_before'])*60 : '0';

		 								$status = false;

		 								if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id ) ) {
		 									$status = true;
		 								}

		 								?>

		 								<div class="content_schedules" data-id_schedules="<?php echo esc_attr($value_rec['calendar_id']); ?>">
		 									<div class="content_time">
		 										<h6 class = "time_schedules">
		 											<?php echo esc_html( gmdate( get_option('time_format') , strtotime($value['start_time']) ) );?>
			 										<span class="to">
			 											&nbsp;&nbsp;<?php echo esc_html__( 'to', 'eventlist' )?>&nbsp;&nbsp;
			 										</span>
			 										<?php echo esc_html( gmdate( get_option('time_format'), strtotime($value['end_time'] ) ) );?>
		 									    </h6>
		 									    <?php if( apply_filters( 'el_show_ticket_remaining_popup', true ) ){ ?>
			 									    <div class="number_ticket_remaning " >
			 									    	<div class="submit-load-more">
			 									    		<div class="load-more">
			 									    			<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
			 									    		</div>
			 									    	</div>
			 									    </div>
		 										<?php } ?>
		 								    </div>
		 								<?php

		 								$link = add_query_arg( array( 'ide' => $id, 'idcal' => $time_value.$key ), get_cart_page() );

		 								if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) {
											$link = esc_url( $ticket_external_link );
										}

		 								if ( $status ) { ?>
		 									<a class='booking_schedules_time' href="<?php echo esc_url( $link ); ?>"<?php echo esc_attr( $target ); ?>>
		 										<?php esc_html_e( 'Book Now', 'eventlist' ); ?>
		 									</a>
		 								<?php } else { ?>
		 									<span class="booking_schedules_time close_schedules_time">
		 									<?php 
		 										$event = new EL_Event();
		 										echo wp_kses_post( $event->get_status_event_calendar( $start_time, $end_time, $number_time, $id ) );
		 									?>

		 									</span>
		 								<?php } ?>
		 								</div>
		 								<?php
		 							}
		 						}
		 					}
	 					}
	 				}
	 			}
			?>
		</form>
	</div>
</div>