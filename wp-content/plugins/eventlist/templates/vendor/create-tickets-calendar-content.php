<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<h5 class="el_heading"><?php esc_html_e( 'Tickets', 'eventlist' ); ?></h5>

<?php if ( $seat_option === "map" ): ?>
	<!-- Show Seats (Seat Map) -->
	<?php if ( ! empty( $ticket_map ) ):
		$ticket_map_seat = $ticket_map['seat'] ?? array();
		$ticket_map_area = $ticket_map['area'] ?? array();

		$data_seat = array_map(function( $value ){ return explode(",", $value); }, array_column($ticket_map_seat, 'id') ) ?? array();
		$data_seat = array_map('trim', call_user_func_array('array_merge', $data_seat) ) ?? array();
		$data_area = array_map('trim', array_column($ticket_map_area, 'id') ) ?? array();

		$data_person_type 	= json_decode( $ticket_map['person_type'], JSON_OBJECT_AS_ARRAY ) ?? array();

		$data_person_type_seat 	= json_decode( $ticket_map['person_type_seat'], JSON_OBJECT_AS_ARRAY ) ?? array();
		$rest_seat_map 		= array_diff( $data_seat, $seat_booked, $seat_holding_ticket );
		$rest_seat_map 		= array_unique( $rest_seat_map );

		?>
		<div class="el_seats_container"
			data-person-type-seat="<?php echo esc_attr( json_encode( $data_person_type_seat ) ); ?>"
			data-person-type="<?php echo esc_attr( json_encode( $data_person_type ) ); ?>"
			data-seat="<?php echo esc_attr( json_encode( $data_seat ) ); ?>"
			data-area-rest="<?php echo esc_attr( json_encode( $seat_available ) ); ?>"
			data-area="<?php echo esc_attr( json_encode( $data_area ) ); ?>">
			<div class="el_seats">
				<?php foreach ( $rest_seat_map as $seat ) { ?>
						<div class="pretty p-default p-round p-fill seat_item">
					        <input type="checkbox" name="seat" value="<?php echo esc_attr( $seat ); ?>" />
					        <div class="state">
					            <label><?php echo esc_html( $seat ); ?></label>
					        </div>
					    </div>
				<?php } ?>
			</div>
			<div class="el_areas">
				<?php foreach ( $ticket_map_area as $area ): ?>
					<div class="pretty p-default p-round p-fill seat_item">
				        <input type="checkbox" name="area" value="<?php echo esc_attr( trim( $area['id'] ) ); ?>" />
				        <div class="state">
				            <label><?php echo esc_html( trim( $area['id'] ) ); ?></label>
				        </div>
				    </div>
				<?php endforeach; ?>
			</div>

			<div class="empty_ticket invalid-feedback"><?php esc_html_e( 'You have not added any tickets yet', 'eventlist' ); ?></div>
			<div class="invalid_seat invalid-feedback"><?php esc_html_e( 'Seat is invalid', 'eventlist' ); ?></div>
		</div>

		<div class="el_show_seat"></div>
		<div class="el_show_area"></div>

	<?php endif; ?>
<?php else: ?>	
	<?php if ( ! empty( $tickets ) ): ?>
	<!-- Show Tickets -->
	<div class="el_tickets_container" data-tickets="<?php echo esc_attr( json_encode( $tickets ) ); ?>">
		<div class="el_tickets">
			<?php foreach ( $tickets as $ticket ):

				$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest( $event_id, $calendar_id,  $ticket['ticket_id'] );
				
				$setup_seat 		= $ticket['setup_seat'];
				$list_seat_rest 	= EL_Booking::instance()->get_list_seat_rest($event_id, $calendar_id, $ticket['ticket_id']);
				$count_seat_rest = count( $list_seat_rest );

				$max_num 	= $number_ticket_rest;

				if ( $seat_option === "none" ) {
					$setup_seat = "no";
				} else {
					$max_num = $count_seat_rest;
				}

				?>
				<div class="ticket_item">

					<div class="ticket_info">
						<span class="ticket_name">
							<?php echo esc_html( $ticket['name_ticket'], 'eventlist' ); ?>
						</span>
						<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $ticket['ticket_id'] ); ?>">
						<input type="hidden" name="ticket_name" value="<?php echo esc_attr( $ticket['name_ticket'] ); ?>">
						<input type="hidden" name="list_seat_rest" value="<?php echo esc_attr( json_encode( $list_seat_rest ) ); ?>">
					</div>
					
					<div class="ticket_seats" data-placeholder="<?php esc_attr_e( 'Choose seat', 'eventlist' ); ?>">
					</div>
	
					<div class="el_qty_ticket"
						data-setup-seat="<?php echo esc_attr( $setup_seat ); ?>"
						data-max-qty="<?php echo esc_attr( $max_num ); ?>">

						<a href="#" class="el_btn_qty el_btn_minus">
							<i class="fa fa-minus" aria-hidden="true"></i>
						</a>

						<input type="text" readonly class="form-control" min="0" name="quantity" value="0" placeholder="">

						<a href="#" class="el_btn_qty el_btn_plus">
							<i class="fa fa-plus" aria-hidden="true"></i>
						</a>

					</div>

				</div>
			<?php endforeach; ?>
		</div>

		<div class="empty_ticket invalid-feedback"><?php esc_html_e( 'You have not added any tickets yet', 'eventlist' ); ?></div>

		<div class="invalid_seat invalid-feedback"><?php esc_html_e( 'Seat is invalid', 'eventlist' ); ?></div>
	</div>
	<?php endif ?>
<?php endif; ?>

<!-- Extra services -->

<?php if ( ! empty( $data_extra_service ) ): ?>
	<h5 class="el_heading"><?php esc_html_e( 'Services', 'eventlist' ); ?></h5>
<?php endif; ?>

<div class="el_extra_services"
	data-ticket="<?php esc_attr_e( 'Ticket #', 'eventlist' ); ?>"
	data-services="<?php echo esc_attr( json_encode( $data_extra_service ) ); ?>">

</div>
