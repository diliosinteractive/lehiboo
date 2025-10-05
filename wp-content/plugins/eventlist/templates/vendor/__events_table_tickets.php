<?php  if ( ! defined( 'ABSPATH' ) ) exit(); ?>

<div class="general_tickets" style="text-transform: none;">
	<?php 
	$post_id 		= $args['post_id'];
	$seat_option 	= get_post_meta( $post_id, OVA_METABOX_EVENT . 'seat_option', true );

	$list_type_ticket 		= [];
	$list_total_ticket 		= [];
	$total_ticket_in_event 	= 0;
	$arr_list_seat_map 		= [];
	$arr_list_area_map 		= [];


	switch ( $seat_option ) {
		case 'none':
			$list_type_ticket = get_post_meta( $post_id, OVA_METABOX_EVENT . 'ticket', true);

			if ( ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) ) {
				foreach ( $list_type_ticket as $ticket ) {
					$list_total_ticket[$ticket['ticket_id']] = isset( $ticket['number_total_ticket'] ) ? (int)$ticket['number_total_ticket'] : 0;
					$total_ticket_in_event += isset( $ticket['number_total_ticket'] ) ? (int)$ticket['number_total_ticket'] : 0;
				}
			}
			break;

		case 'simple':
			$list_type_ticket = get_post_meta( $post_id, OVA_METABOX_EVENT . 'ticket', true);
			$total_ticket_in_event = 0;
			if ( ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) ) {
				foreach ( $list_type_ticket as $ticket ) {
					$list_total_ticket[$ticket['ticket_id']] = isset( $ticket['number_total_ticket'] ) ? (int)$ticket['number_total_ticket'] : 0;
					$total_ticket_in_event += isset( $ticket['number_total_ticket'] ) ? (int)$ticket['number_total_ticket'] : 0;

					$list_total_ticket[$ticket['ticket_id']] = isset( $ticket['seat_list'] ) ? count( explode( ",", $ticket['seat_list'] ) ) : 0;
					$total_ticket_in_event = isset( $ticket['seat_list'] ) ? count( array_map('trim', explode( ",", $ticket['seat_list'] ) ) ) : 0;
					
				}
			}

			break;

		case 'map':
			$list_ticket_map = get_post_meta( $post_id, OVA_METABOX_EVENT . 'ticket_map', true );

			// Seats
			$list_seat = isset( $list_ticket_map['seat'] ) ? $list_ticket_map['seat'] : array();

			if ( ! empty( $list_seat ) && is_array( $list_seat ) ) {
				foreach ( $list_seat as $key => $value ) {
					$str_list_id 			= $value['id'];
					$arr_list_seat_map_item = explode( ',', $str_list_id );
					$arr_list_seat_map_item = array_map( 'trim', $arr_list_seat_map_item );
					$arr_list_seat_map 		= array_merge( $arr_list_seat_map_item, $arr_list_seat_map );
				}
			}

			$total_ticket_in_event = count( $arr_list_seat_map );

			// Areas
			$list_area = isset( $list_ticket_map['area'] ) ? $list_ticket_map['area'] : array();

			if ( ! empty( $list_area ) && is_array( $list_area ) ) {
				foreach ( $list_area as $key => $area_item ) {
					if ( isset( $area_item['qty'] ) && absint( $area_item['qty'] ) ) {
						$total_ticket_in_event += absint( $area_item['qty'] );
					}
					$arr_list_area_map[] = $area_item['id'];
				}
			}
			break;
		
		default:
			break;
	}

	
	$list_calendar 	= get_arr_list_calendar_by_id_event( $post_id );
	$total_calendar = count( $list_calendar );
	$total_ticket_in_event *= $total_calendar;



	//get booking by id_event
	$event_ids = el_get_product_ids_multi_lang( $post_id );
	$agrs = [
		'post_type' 	=> 'el_bookings',
		'post_status' 	=> 'publish',
		"meta_query" 	=> [
			'relation' 	=> 'AND',
			[
				"key" 		=> OVA_METABOX_EVENT . 'id_event',
				"value" 	=> $event_ids,
				'compare' 	=> 'IN',
			],
			[
				'key' 	=> OVA_METABOX_EVENT . 'status',
				'value' => 'Completed',
			]
		],
		'posts_per_page' 	=> -1,
		'fields' 			=> 'ids',
	];

	$list_booking_by_id_event = get_posts( $agrs );

	$total_ticket_booking = 0;
	$sub_total = [];

	if ( $seat_option !== 'map'  ) {
		if ( ! empty( $list_booking_by_id_event ) && is_array( $list_booking_by_id_event ) ) {
			foreach ( $list_booking_by_id_event as $booking_id ) {
				$list_id_qty_ticket_in_booking = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true );

				if ( ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) ) {
					foreach ( $list_type_ticket as $ticket ) {
						//if quantity ticket not value set value = 0
						$sub_total[$ticket['ticket_id']] = ! empty( $sub_total[$ticket['ticket_id']] ) ? $sub_total[$ticket['ticket_id']] : 0;

						if ( is_array( $list_id_qty_ticket_in_booking ) && ( isset( $ticket['ticket_id'] ) ) && array_key_exists( (string)$ticket['ticket_id'], $list_id_qty_ticket_in_booking ) ) {
							$sub_total[$ticket['ticket_id']] += $list_id_qty_ticket_in_booking[$ticket['ticket_id']];
							$total_ticket_booking += $list_id_qty_ticket_in_booking[$ticket['ticket_id']];
						}
					}
				}
			}
		}
	} else {
		if ( ! empty( $list_booking_by_id_event ) && is_array( $list_booking_by_id_event ) ) {
			foreach ( $list_booking_by_id_event as $booking_id ) {
				$list_id_qty_ticket_in_booking = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true );
				// ticket seat
				if ( ! empty( $arr_list_seat_map ) && is_array( $arr_list_seat_map ) ) {
					foreach ( $arr_list_seat_map as $ticket ) {
						// if quantity ticket not value set value = 0
						$sub_total[$ticket] = ( ! empty( $sub_total[$ticket] ) ) ? $sub_total[$ticket] : 0;

						if ( is_array( $list_id_qty_ticket_in_booking ) && ( isset( $ticket ) ) && array_key_exists( (string)$ticket, $list_id_qty_ticket_in_booking ) ) {
							$sub_total[$ticket] 	+= $list_id_qty_ticket_in_booking[$ticket];
							$total_ticket_booking 	+= $list_id_qty_ticket_in_booking[$ticket];
						}
					}
				}
				// ticket area
				if ( ! empty( $arr_list_area_map ) && is_array( $arr_list_area_map ) ) {
					foreach ( $arr_list_area_map as $ticket ) {
						$sub_total[$ticket] = ( ! empty( $sub_total[$ticket] ) ) ? $sub_total[$ticket] : 0;

						if ( is_array( $list_id_qty_ticket_in_booking ) && ( isset( $ticket ) ) && array_key_exists( (string)$ticket, $list_id_qty_ticket_in_booking ) ) {
							$sub_total[$ticket] 	+= $list_id_qty_ticket_in_booking[$ticket];
							$total_ticket_booking 	+= $list_id_qty_ticket_in_booking[$ticket];
						}
					}
				}

			}
		}
	}

	if ( $total_ticket_in_event == 0 ) {
		$percent_sale_ticket = 0;
	} else {
		$percent_sale_ticket = round( ( $total_ticket_booking / $total_ticket_in_event ), 4 ) * 100;
	}
	
	?>
	<p class="total-ticket-sale"><?php esc_html_e("Total Tickets:", 'eventlist'); ?>&nbsp;
		<span class="active_color"><?php echo esc_html( $total_ticket_booking . "/" . $total_ticket_in_event ); ?></span>
	</p>
	<div class="el-wp-bar">
		<div style="width:<?php echo esc_attr($percent_sale_ticket); ?>%" class="skill-active"><span><?php echo esc_html( $percent_sale_ticket . "%" ); ?></span></div>
	</div>
	<div class="list-ticket-in-event">
		<ul>
			<?php

			switch ( $seat_option ) {
				case 'none':
					if ( ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) ) {
						$i = 0;
						$number_ticket = count($list_type_ticket);

						foreach ( $list_type_ticket as $ticket ) {
							$i++;
							$character = ($i != $number_ticket) ? ", " : "";
								
							if (isset($sub_total[$ticket['ticket_id']])) {
								$number_ticket_sale = $sub_total[$ticket['ticket_id']];
							} else {
								$number_ticket_sale = 0;
							}
							?>
							<?php 
							$name_ticket = isset( $ticket['name_ticket'] ) ? $ticket['name_ticket'] : ''; 
							$number_total_ticket = isset( $ticket['number_total_ticket'] ) ? (int) ($ticket['number_total_ticket']) * $total_calendar : 0;
							?>
						<li class="item-ticket"><span class="name-ticket"><?php echo esc_html($name_ticket) ?>: </span>
							<span class="active_color"><?php echo esc_html($number_ticket_sale) ?>/<?php echo esc_html($number_total_ticket) . $character ?></span> </li>
							<?php
						}
					}
					break;
				
				default:
					break;
			}

			?>
			</ul>
		</div>
	</div>