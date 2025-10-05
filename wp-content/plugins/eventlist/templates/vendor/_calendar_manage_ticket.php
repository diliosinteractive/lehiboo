<?php if( ! defined( 'ABSPATH' ) ) exit();	
if( !isset( $_POST['data'] ) ) wp_die();
$post_data 	= $_POST['data'];
$cal_id 	= isset( $post_data['cal_id'] ) ? sanitize_text_field( $post_data['cal_id'] ) : '';
$id 		= isset( $post_data['eid'] ) ? sanitize_text_field( $post_data['eid'] ) : '';


$seat_option 			= get_post_meta( $id, OVA_METABOX_EVENT.'seat_option', true );
$list_type_ticket 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true);
$ticket_map 			= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_map', true);
$list_calendar_ticket 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar', true);
$date_format 	= get_option('date_format');
$time_format 	= get_option('time_format');
$id_cal 		= explode('_', $cal_id);

if ( ! empty( $list_calendar_ticket ) && is_array( $list_calendar_ticket ) ){ ?>
	<div class="ticket-calendar event_section_white">

	
		<?php
		foreach ( $list_calendar_ticket as $calendar ) {

			foreach ( $id_cal as $value_cal ) {

				if( $calendar['calendar_id'] == $value_cal ) {
					if( isset($i) ){
						$i = 0;
					}else{
						$i = 1;
					}
					$start_time = isset( $calendar['date'] ) ? el_get_time_int_by_date_and_hour($calendar['date'], $calendar['start_time']) : '';
					$end_time = isset( $calendar['end_date'] ) ? el_get_time_int_by_date_and_hour($calendar['end_date'], $calendar['end_time']) : '';
					$number_time = isset( $calendar['book_before_minutes'] ) ? floatval($calendar['book_before_minutes'])*60 : '0';


					$status = false;


					if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id ) ) {
						$status = true;
					}


					$total_number_ticket_rest = 0;

					switch ( $seat_option ) {
						case 'none':
							foreach ( $list_type_ticket as $ticket ) {
								$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest( $id, $calendar['calendar_id'],  $ticket['ticket_id'] );

								$total_number_ticket_rest += $number_ticket_rest;
							}
							break;

						case 'simple':
							foreach ( $list_type_ticket as $ticket ) {
								$total_number_ticket_rest += count( EL_Booking::instance()->get_list_seat_rest( $id, $calendar['calendar_id'], $ticket['ticket_id'] ) );
							}
							break;

						case 'map':
							$total_number_ticket_rest += EL_Booking::instance()->get_number_ticket_map_rest( $id, $calendar['calendar_id'] );
							break;					
						default:
							break;
					}


					$event = new EL_event();

					
					$ticket_text = ( $total_number_ticket_rest == 1 ) ? esc_html__( 'ticket', 'eventlist' ) : esc_html__( 'tickets', 'eventlist' );

					?>

					
					<?php if( $i ==1 ){?>
						<?php if ( isset($calendar['end_date']) && ( $calendar['date'] && $calendar['end_date'] && $calendar['date'] != $calendar['end_date'] ) ) { ?>
							<div class="date-time">
								<div class="date">
									<span class="day">
										<?php echo esc_html($event->get_date_by_format_and_date_time( "l", $calendar['date'] )) ?>, 
									</span>
									<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $calendar['date'] )) ?>
									-
									<span class="day">
										<?php echo esc_html($event->get_date_by_format_and_date_time( "l", $calendar['end_date'] )) ?>, 
									</span>
									<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $calendar['end_date'] )) ?>
								</div>
							</div>
						<?php } else { ?>
							<div class="date-time">
								<div class="date">
									<span class="day">
										<?php echo esc_html($event->get_date_by_format_and_date_time( "l", $calendar['date'] )) ?>, 
									</span>
									<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $calendar['date'] )) ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>

					
					<div class="item-calendar-ticket">


							<div class="time">
								<span class="start-time"><?php echo isset( $calendar['date'] ) ? esc_html($event->get_date_by_format_and_date_time( $time_format, $calendar['date'], $calendar['start_time'] )) : '' ?></span>
								<span class="separator">-</span>
								<span class="start-time"><?php echo  isset( $calendar['end_date'] ) ? esc_html($event->get_date_by_format_and_date_time( $time_format, $calendar['end_date'], $calendar['end_time'] )) : '' ?></span>
								<span class="timezone">
									<?php echo '&nbsp;'.el_get_timezone_event( $id ); ?>
								</span>
							</div>

							<div class="ticket-text">

								<div class="text_ticket">
									<?php echo esc_html('Remaining Tickets', 'eventlist'); ?>
									<div class="ticket_rest">
										<?php echo esc_html( $total_number_ticket_rest.'&nbsp;' ); ?>
										<span><?php echo esc_html( $ticket_text ); ?> </span>
									</div>
								</div>

							</div>

							<?php if ( $seat_option == 'none' ): ?>
								
							
								<div class="button_ticket">


									<?php if ($status) { ?>

										<input type="button" name="edit_ticket_calendar" class="edit_ticket" data-eid="<?php echo esc_attr($id); ?>"  data-cal_id="<?php echo esc_attr($calendar['calendar_id']); ?>" value = "<?php esc_html_e('Edit','eventlist')?>" />
										<div class="submit-load-more sendmail">
											<div class="load-more">
												<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
											</div>
										</div>
											
										<?php


									} else {
										?>
										<span class="close-booking"><?php echo $event->get_status_event_calendar($start_time, $end_time, $number_time, $id ); ?></span>
										<?php
									}

									?>

								</div>

							<?php endif; ?>

							<div class="content_edit_ticket" data-name= "<?php echo esc_attr($calendar['calendar_id']); ?>"></div>
					</div>
					
					<?php
					
				}
			} 
		}

		?>


	</div>
<?php } ?>