<?php if( ! defined( 'ABSPATH' ) ) exit();

global $event;

$id = get_the_ID();

$list_type_ticket 	= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true);
$seat_option 		= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true);
$start_date_str 	= get_post_meta( $id, OVA_METABOX_EVENT . 'start_date_str', true);
$end_date_str 		= get_post_meta( $id, OVA_METABOX_EVENT . 'end_date_str', true);

$ticket_link = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true);

$status 	= false;
$ticket_url = '#';


	$option_calendar = get_post_meta( $id, OVA_METABOX_EVENT . 'option_calendar', true);
	$count 	= 0;
	$url 	= '';

	if ( 'manual' === $option_calendar ) {
		$list_calendar_ticket = get_post_meta( $id, OVA_METABOX_EVENT . 'calendar', true);

		if ( !empty( $list_calendar_ticket ) && is_array( $list_calendar_ticket ) ) {
			foreach ( $list_calendar_ticket as $ticket ) {
				$start_time 	= isset( $ticket['date'] ) ? el_get_time_int_by_date_and_hour( $ticket['date'], $ticket['start_time'] ) : '';
				$end_time 		= isset( $ticket['end_date'] ) ? el_get_time_int_by_date_and_hour( $ticket['end_date'], $ticket['end_time'] ) : '';
				$number_time 	= isset( $ticket['book_before_minutes'] ) ? floatval( $ticket['book_before_minutes'] )*60 : 0;

				if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id ) ) {
					$count += 1;
				}

				$check_tiket_selling = $event->check_ticket_in_event_selling( $id );

				if ( $check_tiket_selling ) {
					$url =  add_query_arg( array( 'ide' => $id, 'idcal' => $ticket['calendar_id'] ), get_cart_page() );
				}
			}
		}
	} else {
		$ticket_calendar_recurrence = EL_Ticket::instance()->el_ticket_calendar_recurrence( $id );
        $array_event = $ticket_calendar_recurrence[1];

        $count = 0;
        if ( !empty( $array_event ) && is_array( $array_event ) ) {
        	foreach ( $array_event as $arr_event ) {
        		if ( isset( $arr_event['url'] ) && $arr_event['url'] ) {
        			$count += 1;
        			$url = $arr_event['url'];
        		}
        	}
        }
	}

	if ( $count === 1 && $url ) {
		$status = true;
		$ticket_url = $url;
	}


if( $ticket_link == 'ticket_external_link' ){ ?>
	<?php $external_link = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link', true); ?>
	<div class="act_booking">
		<a href="<?php echo esc_url($external_link); ?>" target="_blank" >
			<?php echo esc_html__( 'Book Now', 'eventlist' ); ?>
		</a>
	</div>	

<?php }else if ( (! empty( $list_type_ticket )  && ! empty($start_date_str)) || ( $seat_option === 'map' ) ) {
		$current_time = current_time( 'timestamp' );

		if ( $id ) {
			$timezone = get_post_meta( $id, OVA_METABOX_EVENT . 'time_zone', true );

			if ( $timezone ) {
				$tz_string 	= el_get_timezone_string( $timezone );
				$datetime 	= new DateTime('now', new DateTimeZone( $tz_string ) );
				$time_now 	= $datetime->format('Y-m-d H:i');

				if ( strtotime( $time_now ) ) {
					$current_time = strtotime( $time_now );
				}
			}
		}

		if ( (int)$end_date_str > $current_time ) { ?>
		<div class="act_booking">
			<a href="<?php echo esc_url( $ticket_url ); ?>" id="event_booking_single_button" data-url="<?php echo esc_attr( $status ); ?>">
				<?php echo esc_html__( 'Book Now', 'eventlist' ); ?>
			</a>
		</div>
	<?php } ?>

<?php } ?>
