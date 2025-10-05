<?php 
if ( !defined( 'ABSPATH' ) ) exit();

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<?php
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$list_bookings = EL_Booking::instance()->get_list_booking_user_current($paged);
		$allow_transfer_ticket = EL()->options->ticket_transfer->get('allow_transfer_ticket','');

	
		
		?>
		<div class="table-list-booking">
			<div class="el-notify">
				<p class="success status"></p>
				<p class="error status"></p>
			</div>
			<table>
				<thead class="event_head">
					<tr>
						<td class="id"><?php esc_html_e("ID", "eventlist"); ?></td>
						<td><?php esc_html_e("Event", "eventlist"); ?></td>
						<td><?php esc_html_e("Calendar Date", "eventlist"); ?></td>
						<td><?php esc_html_e("Total", "eventlist"); ?></td>
						<td><?php esc_html_e("Ticket Type", "eventlist"); ?></td>
						<td><?php esc_html_e("Date Created", "eventlist"); ?></td>
						<td><?php esc_html_e("Status", "eventlist"); ?></td>
						<td><?php esc_html_e("Action", "eventlist"); ?></td>
					</tr>
				</thead>
				<tbody class="event_body">
					<?php 
					if($list_bookings->have_posts() ) : while ( $list_bookings->have_posts() ) : $list_bookings->the_post();

						$id_booking = get_the_id();

						$status_booking = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'status', true );
						switch( $status_booking ) {

							case 'Completed':{
								$status = esc_html__('Completed', 'eventlist');
								break;
							}

							case 'Pending':{
								$status = esc_html__('Pending', 'eventlist');
								break;
							}

							case 'Canceled':{
								$status = esc_html__('Canceled', 'eventlist');
								break;
							}
							default : {
								$status = esc_html( $status_booking );
								break;
							}
						}

						$id_event = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'id_event', true );



						?>
						<tr class="<?php echo 'booking_'.get_the_id(); ?> ">
							<td data-colname="<?php esc_attr_e('ID', 'eventlist'); ?>" class="id"><?php echo esc_html(get_the_id()); ?></td>
							<td data-colname="<?php esc_attr_e('Event', 'eventlist'); ?>" >
								
								<a href="<?php echo get_the_permalink( $id_event ); ?>" target="_blank">
									<?php echo esc_html(get_post_meta( $id_booking, OVA_METABOX_EVENT . 'title_event', true )); ?>
								</a>
									
							</td>
							<td data-colname="<?php esc_attr_e('Calendar Date', 'eventlist'); ?>"><?php echo esc_html(get_post_meta( $id_booking, OVA_METABOX_EVENT . 'date_cal', true )); ?></td>
							
							<td data-colname="<?php esc_attr_e('Total', 'eventlist'); ?>" ><?php echo esc_html(el_price(get_post_meta( $id_booking, OVA_METABOX_EVENT . 'total_after_tax', true ))) ?></td>

							<td data-colname="<?php esc_attr_e('Ticket Type', 'eventlist'); ?>">
								<?php
								
								$id_event 		= get_post_meta( $id_booking, OVA_METABOX_EVENT . 'id_event', true );
								$seat_option 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true );
								$payment_method = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'payment_method', true );
								$list_ticket 	= get_post_meta( $id_booking, OVA_METABOX_EVENT . 'list_id_ticket', true );
								$list_ticket 	= json_decode($list_ticket);

								$list_qty_ticket_by_id_ticket = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true );

								$html = '';

								if ( $seat_option != 'map' ) {
									$list_ticket_in_event = get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket', true);

									if ( ! empty($list_ticket_in_event) && is_array($list_ticket_in_event) ) {
										foreach ($list_ticket_in_event as $ticket) {
											if ( in_array($ticket['ticket_id'], $list_ticket) ) {
												$html .= $ticket['name_ticket'] .' - '.$list_qty_ticket_by_id_ticket[$ticket['ticket_id']].' '.esc_html__( 'ticket(s)', 'eventlist' ). '<br>';
											}
										}
									}
								} else {
									$ticket_map = get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket_map', true);
									
									if ( ! empty( $ticket_map ) && is_array( $ticket_map ) && ( ! empty( $ticket_map['seat'] ) || ! empty( $ticket_map['area'] ) ) ) {
										$html = esc_html__('Map', 'eventlist');
									}
								}
								
								echo $html;
								?>
							</td>
							<td data-colname="<?php esc_attr_e('Date Created', 'eventlist'); ?>" >
								<?php
								$date_format = get_option('date_format');
								$time_format = get_option('time_format');
								echo get_the_date($date_format, $id_booking) . " - " . get_the_date($time_format, $id_booking);
								?>
							</td>
							<td data-colname="<?php esc_attr_e('Status', 'eventlist'); ?>" >
								<?php echo $status; ?>
							</td>
							<td>
								<?php if( get_post_meta( $id_booking, OVA_METABOX_EVENT.'status', true ) != 'Canceled' ) { ?>
									<div class="wp-button-my-booking">

										<div class="button-sendmail">
											<div class="submit-load-more sendmail">
												<div class="load-more">
													<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
												</div>
											</div>
											<button class="button create-ticket-send-mail" data-nonce="<?php echo wp_create_nonce( 'el_create_send_ticket_nonce' ); ?>" data-id-booking="<?php echo esc_attr($id_booking) ?>"><?php esc_html_e( "Send mail", "eventlist" ); ?></button>
										</div>

										<div class="button-dowload-ticket">
											<div class="submit-load-more dowload-ticket">
												<div class="load-more">
													<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
												</div>
											</div>
											<button class="button download-ticket" data-nonce="<?php echo wp_create_nonce( 'el_download_ticket_nonce' ); ?>" data-id-booking="<?php echo esc_attr($id_booking) ?>"><?php esc_html_e( "Download", "eventlist" ); ?></button>
										</div>
										<?php if ( EL()->options->invoice->get('invoice_mail_enable', 'no' ) === 'yes' ): ?>
										<div class="button-invoice">
											<div class="submit-load-more booking-invoice">
												<div class="load-more">
													<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
												</div>
											</div>
											<button class="button create-invoice" data-nonce="<?php echo wp_create_nonce( 'el_create_invoice_nonce' ); ?>" data-booking-id="<?php echo esc_attr($id_booking) ?>"><?php esc_html_e( "Invoice", "eventlist" ); ?></button>
										</div>
										<div class="button-send-invoice">
											<div class="submit-load-more booking-send-invoice">
												<div class="load-more">
													<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
												</div>
											</div>
											<button class="button send-invoice" data-nonce="<?php echo wp_create_nonce( 'el_send_invoice_nonce' ); ?>" data-booking-id="<?php echo esc_attr($id_booking) ?>"><?php esc_html_e( "Send Invoice", "eventlist" ); ?></button>
										</div>
										
										<?php endif; ?>

										<div class="button-ticket-list">
											<button class="button ticket-list"
											data-toggle="modal"
											data-target="#ticket_list_modal"
											data-nonce="<?php echo wp_create_nonce( 'el_ticket_list_nonce' ); ?>"
											data-booking-id="<?php echo esc_attr( $id_booking ); ?>"
												><?php esc_html_e( 'Ticket List', 'eventlist' ); ?></button>
										</div>

										<?php if( el_cancellation_booking_valid( $id_booking ) ){ ?>	
											<div class="button-cancel-booking">
												<div class="submit-load-more cancel-booking">
													<div class="load-more">
														<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
													</div>
												</div>
												<button class="button cancel-booking" data-nonce="<?php echo wp_create_nonce( 'el_cancel_booking_nonce' ); ?>" data-id-booking="<?php echo esc_attr($id_booking) ?>" data-prompt-msg="<?php esc_html_e( 'Do you want to cancel booking ?', 'eventlist' ); ?>">
													<?php esc_html_e( "Cancel", "eventlist" ); ?>
												</button>	
											</div>
										<?php } ?>	

									</div>
								<?php } ?>
							</td>
						</tr>
					<?php endwhile; else : ?> 
					<td colspan="8"><?php esc_html_e( 'Not Found Bookings', 'eventlist' ); ?></td> 
					<?php ; endif; wp_reset_postdata(); ?>

					
					<?php $total = $list_bookings->max_num_pages; ?>
					<?php if ( $total > 1 ) { ?>
						<td colspan="8">
							<?php echo pagination_vendor($total); ?>
						</td>			
					<?php } ?>

				</tbody>
			</table>
			
		</div>

		<!-- Modal -->
		<div class="modal fade" id="ticket_list_modal" tabindex="-1" aria-labelledby="ticketListModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="ticketListModalLabel"><?php esc_html_e( 'Ticket List', 'eventlist' ); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'eventlist' ); ?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="table-responsive">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<?php if ( $allow_transfer_ticket ): ?>
											<th scope="col">
												<div class="form-check">
													<input class="form-check-input position-static" type="checkbox" id="check_all_ticket" aria-label="<?php esc_attr_e( 'Check all ticket', 'eventlist' ); ?>">
												</div>
											</th>
										<?php endif; ?>
										<th scope="col"><?php esc_html_e( 'Ticket Number', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Ticket Type', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Customer', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Status', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Seat', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Venue & Address', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Qr code', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Start date', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'End date', 'eventlist' ); ?></th>
										<th scope="col"><?php esc_html_e( 'Check Ticket', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<!-- ajax load content -->
								<tbody class="ticket-list-body">
								</tbody>
							</table>
						</div>
      				</div>
					<div class="modal-footer">
						<button class="btn btn-primary ticket-list-loading" type="button" disabled>
						  <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
						  <?php esc_html_e( 'Loading...', 'eventlist' ); ?>
						</button>
						<?php if ( $allow_transfer_ticket ): ?>
							<button type="button" class="btn btn-warning btn-ticket-transfer"><?php esc_html_e( 'Transfer Tickets', 'eventlist' ); ?></button>
						<?php endif; ?>
						<button type="button" class="btn btn-danger" data-dismiss="modal"><?php esc_html_e( 'Close', 'eventlist' ); ?></button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal fade" id="ticket_transfer_modal" tabindex="-1" role="dialog" aria-labelledby="ticketTransferModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content shadow">
					<div class="modal-header">
						<h5 class="modal-title" id="ticketTransferModalLabel"><?php esc_html_e( "Receiver's information", 'eventlist' ); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="ticket_transfer_alert">
							<div class="alert alert-danger" role="alert">
								<?php esc_html_e( 'Please complete all information.', 'eventlist' ); ?>
							</div>
						</div>
						<div class="ticket_transfer_mess"></div>
						<form>
							<div class="form-group row">
								<label for="ticket_receiver_email" class="col-sm-4 col-form-label"><?php esc_html_e( 'Email', 'eventlist' ); ?></label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="ticket_receiver_email" placeholder="email@example.com">
								</div>
							</div>
							<div class="form-group row">
								<label for="ticket_receiver_name" class="col-sm-4 col-form-label"><?php esc_html_e( 'Name', 'eventlist' ); ?></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="ticket_receiver_name" placeholder="<?php echo esc_attr( 'John Doe' ); ?>">
								</div>
							</div>
							<div class="form-group row">
								<label for="ticket_receiver_phone" class="col-sm-4 col-form-label"><?php esc_html_e( 'Phone Number', 'eventlist' ); ?></label>
								<div class="col-sm-8">
									<input type="tel" class="form-control" id="ticket_receiver_phone" placeholder="<?php echo esc_attr( '(+123) 456 7890' ); ?>">
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<div class="spinner-grow ticket_receiver_loading" role="status">
							<span class="sr-only"><?php esc_html_e( 'Loading...', 'eventlist' ); ?></span>
						</div>
						<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', 'eventlist' ); ?></button>
						<button type="button" class="btn btn-primary" id="ticket_transfer_submit" data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_ticket_transfer_nonce' ) ); ?>"><?php esc_html_e( 'Submit', 'eventlist' ); ?></button>
					</div>
				</div>
			</div>
		</div>

	</div>
	
</div>
