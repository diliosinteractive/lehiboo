<?php
/**
 * Template Override: Ticket Calendar - Airbnb Style
 *
 * Affiche le calendrier des tickets avec un style Airbnb:
 * - Cards de date élégantes
 * - Affichage clair des horaires
 * - Statut de disponibilité visible
 * - Bouton de réservation distinct
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;

$id = get_the_ID();

$show_remaining_tickets = EL()->options->event->get('show_remaining_tickets', 'yes');
$list_type_ticket 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true );
$list_calendar_ticket 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar', true );
$schedules_time 		= get_post_meta( $id, OVA_METABOX_EVENT . 'schedules_time', true ) ? get_post_meta( $id, OVA_METABOX_EVENT.'schedules_time', true ) : array();
$recurrence_frequency 	= get_post_meta( $id, OVA_METABOX_EVENT . 'recurrence_frequency', true );
$ts_start 				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_start', true );
$ts_end  				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_end', true );
$option_calendar 		= get_post_meta( $id, OVA_METABOX_EVENT . 'option_calendar', true );
$check_tiket_selling 	= $event->check_ticket_in_event_selling( $id );
$class_ticket_selling 	= 'un-selling';
$href_link 				= '';

$date_format 			= get_option('date_format');
$time_format 			= get_option('time_format');
$lang 					= el_calendar_language();
$first_day 				= el_first_day_of_week();
$ticket_link 			= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true );
$ticket_external_link 	= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link', true );
$seat_option 			= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true );
$timezone 				= get_post_meta( $id, OVA_METABOX_EVENT . 'time_zone', true );
$time_now 				= current_time('Y-m-d H:i');

if ( $timezone ) {
	$tz_string 	= el_get_timezone_string( $timezone );
	$datetime 	= new DateTime('now', new DateTimeZone( $tz_string ) );
	$time_now 	= $datetime->format('Y-m-d H:i');
}

?>

	<?php if ( $option_calendar == 'manual' ):
		if ( ! empty( $list_calendar_ticket ) && is_array( $list_calendar_ticket ) ): ?>
			<div class="airbnb_calendar_wrapper" id="booking_event" data-external-link="<?php echo esc_attr( $ticket_link ); ?>">
				<h3 class="airbnb_calendar_title">
					<?php esc_html_e( "Choisissez une date", "eventlist" ); ?>
				</h3>

				<div class="airbnb_calendar_list">
					<?php foreach ( $list_calendar_ticket as $ticket ):
						$start_time 	= isset( $ticket['date'] ) ? el_get_time_int_by_date_and_hour( $ticket['date'], $ticket['start_time'] ) : '';
						$end_time 		= isset( $ticket['end_date'] ) ? el_get_time_int_by_date_and_hour($ticket['end_date'], $ticket['end_time']) : '';
						$number_time 	= isset( $ticket['book_before_minutes'] ) ? floatval($ticket['book_before_minutes'])*60 : '0';
						$status 		= false;

						if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id ) ) {
							$status = true;
						}

						if ( $check_tiket_selling ) {
							$href_link = add_query_arg( array( 'ide' => $id, 'idcal' => $ticket['calendar_id'] ), get_cart_page()  );
							$class_ticket_selling = '';
						}

						$total_number_ticket_rest = 0;

						switch ( $seat_option ) {
							case 'none':
								foreach ( $list_type_ticket as $ticket2 ) {
									$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest( $id, $ticket['calendar_id'],  $ticket2['ticket_id'] );
									$total_number_ticket_rest += $number_ticket_rest;
								}
								break;

							case 'simple':
								foreach ( $list_type_ticket as $ticket2 ) {
									$number_ticket_rest = count( EL_Booking::instance()->get_list_seat_rest( $id, $ticket['calendar_id'],  $ticket2['ticket_id'] ) );
									$total_number_ticket_rest += $number_ticket_rest;
								}
								break;

							case 'map':
								$total_number_ticket_rest = EL_Booking::instance()->get_number_ticket_map_rest( $id, $ticket['calendar_id'] );
								break;

							default:
								break;
						}

						// Format de date plus lisible (jour complet + date courte)
						$full_day_name = $event->get_date_by_format_and_date_time( "l", $ticket['date'] );
						$short_date = $event->get_date_by_format_and_date_time( "j F", $ticket['date'] );

						// Classes conditionnelles
						$item_classes = 'airbnb_calendar_item';
						if ( ! $status ) {
							$item_classes .= ' is-unavailable';
						}
						?>
						<div class="<?php echo esc_attr( $item_classes ); ?>">

							<!-- Info principale: Date + Horaires + Disponibilité -->
							<div class="calendar_item_info">
								<!-- Date -->
								<div class="calendar_item_date">
									<?php if ( isset( $ticket['end_date'] ) && ( $ticket['date'] && $ticket['end_date'] && $ticket['date'] != $ticket['end_date'] ) ): ?>
										<!-- Événement multi-jours -->
										<div class="date_day_name"><?php echo esc_html( ucfirst($full_day_name) ); ?> <?php echo esc_html( $short_date ); ?></div>
									<?php else: ?>
										<!-- Événement d'un jour -->
										<div class="date_day_name"><?php echo esc_html( ucfirst($full_day_name) ); ?> <?php echo esc_html( $short_date ); ?></div>
									<?php endif; ?>
								</div>

								<!-- Horaires + Disponibilité -->
								<div class="calendar_item_meta">
									<?php if ( EL()->options->event->get('show_hours_single', 'yes') == 'yes' ): ?>
										<span class="time_range">
											<?php echo isset( $ticket['date'] ) ? esc_html($event->get_date_by_format_and_date_time( $time_format, $ticket['date'], $ticket['start_time'] ) ) : ''; ?>
											–
											<?php echo isset( $ticket['end_date'] ) ? esc_html( $event->get_date_by_format_and_date_time( $time_format, $ticket['end_date'], $ticket['end_time'] ) ) : ''; ?>
										</span>
									<?php endif; ?>

									<?php if ( $status && $show_remaining_tickets == 'yes' ):
										if ( $total_number_ticket_rest == 1 ) {
											$ticket_text = esc_html__( 'place disponible', 'eventlist' );
										} else {
											$ticket_text = esc_html__( 'places disponibles', 'eventlist' );
										}
									?>
										<span class="availability_count">
											<?php echo esc_html( $total_number_ticket_rest ); ?> <?php echo esc_html( $ticket_text ); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>

							<!-- Bouton Réserver -->
							<div class="calendar_item_action">
								<?php if ( $status ):
									if ( $ticket_link != 'ticket_external_link' ): ?>
										<a class="item_book_btn <?php echo esc_attr($class_ticket_selling); ?>"
										   href="<?php echo esc_url( $href_link ); ?>">
											<?php echo esc_html__( "Réserver", "eventlist" ); ?>
										</a>
									<?php else: ?>
										<a class="item_book_btn"
										   href="<?php echo esc_url( $ticket_external_link ); ?>"
										   target="_blank">
											<?php echo esc_html__( "Réserver", "eventlist" ); ?>
										</a>
									<?php endif; ?>
								<?php else: ?>
									<!-- Indisponible -->
									<span class="item_unavailable_btn">
										<?php echo esc_html__( "Complet", "eventlist" ); ?>
									</span>
								<?php endif; ?>
							</div>

						</div>
					<?php endforeach; ?>
				</div>

			</div>
		<?php endif;
	else:
        // Mode automatique avec FullCalendar
        $ticket_calendar_recurrence = EL_Ticket::instance()->el_ticket_calendar_recurrence( $id );
        $initdate 					= $ticket_calendar_recurrence[0];
        $array_event 				= $ticket_calendar_recurrence[1];

        $is_timeslot = false;
		if ( $recurrence_frequency === 'weekly' && ! empty( $ts_start ) && ! empty( $ts_end ) ) {
			$is_timeslot = true;
		}

		$is_external_link = false;
		if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) {
			$is_external_link = true;
		}

		if ( ! empty( $array_event ) && is_array( $array_event ) ): ?>
			<div class="ticket-calendar event_section_white" id="booking_event">
				<h3 class="title-ticket-calendar-single-event second_font heading">
					<?php esc_html_e("Event Calendar", "eventlist"); ?>
					<div class="sub-title ">
						<?php esc_html_e( "Choose a date to booking event", 'eventlist' ); ?>
					</div>
				</h3>

				<div
					class="fullcalendar"
					data-local="<?php echo esc_attr( $lang ); ?>"
					data-initdate='<?php echo esc_attr( $initdate ); ?>'
					data-listevent='<?php echo esc_attr( json_encode( $array_event ) ); ?>'
					data-ide="<?php echo esc_attr( $id ); ?>"
					data-schedules_time="<?php echo esc_attr( count( $schedules_time ) ); ?>"
					data-time_slot="<?php echo esc_attr( $is_timeslot ); ?>"
					data-external_link="<?php echo esc_attr( $is_external_link ); ?>"
					data-firstday='<?php echo esc_attr( $first_day ); ?>'
					data-time-now='<?php echo esc_attr( $time_now ); ?>'>
				</div>

				<div class="submit-load-more">
					<div class="load-more">
						<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
					</div>
				</div>
				<div class="schedule_popup"></div>
			</div>
		<?php endif;
	endif;
?>
