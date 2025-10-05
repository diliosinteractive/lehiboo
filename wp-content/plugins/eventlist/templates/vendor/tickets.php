<?php  if ( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="vendor_wrap"> 
	<?php echo el_get_template( '/vendor/manage_event_sidebar.php' ); ?>

	<div class="contents">
		<?php
			
			$id_event 	= isset($_GET['eid']) ? sanitize_text_field($_GET['eid']) : "";
			$keyword 	= isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : "";

			//Check capacity of user
			if ( ! el_can_manage_ticket() || ! verify_current_user_post( $id_event ) || empty( $id_event ) ) {
				esc_html_e( 'You don\'t have permission view tickets', 'eventlist' );
				exit();
			}

			echo el_get_template( '/vendor/heading.php' );

			echo el_get_template( '/vendor/__event_info.php' );

		
			$slug_event = get_post_field( 'post_name', $id_event);
			

			$check_allow_get_list_tickets 	= check_allow_get_list_tickets_by_event($id_event);
			$check_allow_export_tickets 	= check_allow_export_tickets_by_event($id_event);

		 	if ( $check_allow_get_list_tickets === 'yes' ) :

				$list_ticket_record_by_id_event = EL_Ticket::get_ticket_pagination_by_id_event( $id_event, $keyword );

				$list_ckf_output = get_option( 'ova_booking_form', array() );
				?>
				<div class="table-list-ticket">
					
					<?php
						$current_link = add_query_arg( array(
							'vendor' 	=> 'manage_event',
							'tab' 		=> 'tickets',
							'eid' 		=> $id_event,
						), get_myaccount_page() );
					?>

					<form class="search_ticket" action="<?php echo esc_url( $current_link ); ?>" method="GET">
						
						<input type="text" value="<?php echo $keyword; ?>" placeholder="<?php esc_html_e( 'Enter name customer or some characters in QR Code', 'eventlist' ); ?>" name="keyword" style="width: 350px;" />
						
						<input type="hidden" name="vendor" value="manage_event" >
						<input type="hidden" name="tab" value="tickets" >
						<input type="hidden" name="eid" value="<?php echo $id_event; ?>" >
						<button type="submit" class="search_ticket_btn button">
							<?php esc_html_e( 'Find Ticket', 'eventlist' ); ?>
						</button>
						
					</form>

					
					<div class="el_manager_ticket_action_row"
						data-event-id="<?php echo esc_attr( $id_event ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce('el_ticket_action') ); ?>">

						
						<div class="left">
							<a href="#"
								data-empty="<?php esc_attr_e( 'You have not selected any ticket yet', 'eventlist' ); ?>"
								class="el_download_tickets el_ticket_btn">
								<?php esc_html_e( 'Download Tickets', 'eventlist' ); ?>
							</a>
						</div>
						
						<div class="right">
							<?php if ( el_can_create_tickets() || el_is_administrator() ): ?>		
								
								<a href="#" class="el_create_tickets el_ticket_btn">
									<?php esc_html_e( 'Create Tickets', 'eventlist' ); ?>
								</a>

							<?php endif; ?>

							<?php if ( $check_allow_export_tickets == 'yes' ) : ?>
								<a href="#" id="export-csv-extra-ticket" class="el_ticket_btn">
									<?php esc_html_e("Export Tickets", "eventlist"); ?>
								</a>
							<?php endif; ?>

						</div>

					</div>

					<?php if ( $check_allow_export_tickets == 'yes' ) : ?>

						<div class="list-check-export-csv">
							<ul>
								<li>
									
									<label for="id-event" class="el_input_checkbox">
										<?php esc_html_e("Event", "eventlist"); ?>
										<input name="event" value="event" type="checkbox" id="id-event" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="booking-id" class="el_input_checkbox">
										<?php esc_html_e("Booking ID", "eventlist"); ?>
										<input name="booking-id" value="booking-id" type="checkbox" id="booking-id" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="ticket-id" class="el_input_checkbox">
										<?php esc_html_e("Ticket ID", "eventlist"); ?>
										<input name="ticket-id" value="ticket-id" type="checkbox" id="ticket-id" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="ticket_type" class="el_input_checkbox">
										<?php esc_html_e("Ticket Type", "eventlist"); ?>
										<input name="ticket_type" value="ticket_type" type="checkbox" id="ticket_type" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="extra_service" class="el_input_checkbox">
										<?php esc_html_e("Extra Services", "eventlist"); ?>
										<input name="extra_service" value="extra_service" type="checkbox" id="extra_service" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="name-customer" class="el_input_checkbox">
										<?php esc_html_e("Name Customer", "eventlist"); ?>
										<input name="name" value="name" type="checkbox" id="name-customer" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="phone-customer" class="el_input_checkbox">
										<?php esc_html_e("Phone Customer", "eventlist"); ?>
										<input name="phone_customer" value="phone_customer" type="checkbox" id="phone-customer" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="email-customer" class="el_input_checkbox">
										<?php esc_html_e("Email Customer", "eventlist"); ?>
										<input name="email_customer" value="email_customer" type="checkbox" id="email-customer" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="address-customer" class="el_input_checkbox">
										<?php esc_html_e("Address Customer", "eventlist"); ?>
										<input name="address_customer" value="address_customer" type="checkbox" id="address-customer" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="venue" class="el_input_checkbox">
										<?php esc_html_e("Venue", "eventlist"); ?>
										<input name="venue" value="venue" type="checkbox" id="venue" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="address" class="el_input_checkbox">
										<?php esc_html_e("Address", "eventlist"); ?>
										<input name="address" value="address" type="checkbox" id="address" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="seat" class="el_input_checkbox">
										<?php esc_html_e("Seat", "eventlist"); ?>
										<input name="seat" value="seat" type="checkbox" id="seat" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="qr_code" class="el_input_checkbox">
										<?php esc_html_e("Qr code", "eventlist"); ?>
										<input name="qr_code" value="qr_code" type="checkbox" id="qr_code" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="start_date" class="el_input_checkbox">
										<?php esc_html_e("Start Date", "eventlist"); ?>
										<input name="start_date" value="start_date" type="checkbox" id="start_date" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="end_date" class="el_input_checkbox">
										<?php esc_html_e("End Date", "eventlist"); ?>
										<input name="end_date" value="end_date" type="checkbox" id="end_date" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="date_create" class="el_input_checkbox">
										<?php esc_html_e("Date created", "eventlist"); ?>
										<input name="date_create" value="date_create" type="checkbox" id="date_create" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="checkin_time" class="el_input_checkbox">
										<?php esc_html_e("Checkin-Time", "eventlist"); ?>
										<input name="checkin_time" value="checkin_time" type="checkbox" id="checkin_time" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="ticket_checked" class="el_input_checkbox">
										<?php esc_html_e("Ticket Checked", "eventlist"); ?>
										<input name="ticket_checked" value="ticket_checked" type="checkbox" id="ticket_checked" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="ticket_price" class="el_input_checkbox">
										<?php esc_html_e("Price", "eventlist"); ?>
										<input name="ticket_price" value="ticket_price" type="checkbox" id="ticket_price" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<?php
									$list_name_ckf = [];
									$terms 				= get_the_terms( $id_event, 'event_cat' );
									$term_id 			= 0;
									if ( $terms && $terms[0] ) {
										$term_id = $terms[0]->term_id;
									}
									$category_checkout_field = get_term_meta( $term_id, '_category_checkout_field', true) ? get_term_meta( $term_id, '_category_checkout_field', true) : array();
									if( ! empty( $list_ckf_output ) && is_array( $list_ckf_output ) ) {
										foreach( $list_ckf_output as $key_1 => $val ) {
											if( array_key_exists('enabled', $val) &&  $val['enabled'] == 'on' && in_array( $key_1, $category_checkout_field ) ) {
												$list_name_ckf[] = esc_html( $key_1 );
												?>
												<li>
													<input name="<?php echo esc_attr( $key_1 ) ?>"  value="<?php echo esc_attr( $key_1 ) ?>" type="checkbox" id="<?php echo esc_attr( $key_1 ) ?>" checked="checked">
													<label for="<?php echo esc_attr( $key_1 ) ?>"><?php echo esc_html( $val['label'] ) ?></label>
												</li>
												<?php
											}
											
										}
									}
								?>
								<li>
									<button type="submit" class="checked-field"><?php esc_html_e( 'Select All', 'eventlist' ); ?></button>
								</li>
								<li>
									<button type="submit" class="clean-field"><?php esc_html_e( 'Clean All', 'eventlist' ); ?></button>
								</li>
							</ul>
							<input type="hidden" name="id_event" value="<?php echo esc_attr( $id_event ); ?>">
							<input type="hidden" name="el_list_ckf" id="el_list_ckf" value="<?php echo esc_attr( json_encode( $list_name_ckf ) ); ?>" />

							<div class="el_export_ticket_wrapper">
								
								<div class="el_from_date_wrap">
									<label for="el_export_from_date">
										<?php esc_html_e( 'Date Created From', 'eventlist' ); ?>
									</label>
									<input type="text" name="el_export_from_date" id="el_export_from_date"  readonly="readonly" />
								</div>
								
								<div class="el_to_date_wrap">
									<label for="el_export_to_date">
										<?php esc_html_e( 'To', 'eventlist' ); ?>
									</label>
									<input type="text" name="el_export_to_date" id="el_export_to_date" readonly="readonly" />
								</div>

								<button id="button-submit-ticket-export-csv" data-slug-event="<?php echo esc_attr($slug_event); ?>" data-id-event="<?php echo esc_attr($id_event); ?>"  name="export" class="export-csv-extra"><i class="fas fa-file-download"></i><?php esc_html_e("Export Tickets", "eventlist"); ?></button>
							</div>

							
						</div>
						
					<?php endif; ?>
					<div class="el_table_responsive">
						<table>
							<thead class="event_head">
								<tr>
									<td style="width: 50px;">
										<div class="input_checkall_wrap">
											<label for="el_ticket_check_all" class="el_input_checkbox">
												<input type="checkbox" id="el_ticket_check_all" value="1" />
												<span class="checkmark"></span>
											</label>
											
										</div>
									</td>
									<td><?php esc_html_e("Event", "eventlist"); ?></td>
									<td><?php esc_html_e("Ticket Type", "eventlist"); ?></td>
									<td><?php esc_html_e("Customer", "eventlist"); ?></td>
									<td><?php esc_html_e("Seat", "eventlist"); ?></td>
									<td><?php esc_html_e( "Action", "eventlist" ); ?></td>
									<td><?php esc_html_e("Address", "eventlist"); ?></td>
									<td style="width: 120px"><?php esc_html_e("Qr code", "eventlist"); ?></td>
									<td><?php esc_html_e("Start date", "eventlist"); ?></td>
									<td><?php esc_html_e("End date", "eventlist"); ?></td>
									<td><?php esc_html_e("Created", "eventlist"); ?></td>
									<td><?php esc_html_e( "Check-in", "eventlist" ); ?></td>
								</tr>
							</thead>
							<tbody class="event_body">
								<?php 
								if ( $list_ticket_record_by_id_event->have_posts() ) : while ( $list_ticket_record_by_id_event->have_posts() ) : $list_ticket_record_by_id_event->the_post(); 
									$id_ticket_record = get_the_id();
									$qr_code = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'qr_code', true );
									?>
									<tr>
										<td class="ticket_id_col">
											<label for="ticket_id<?php echo $id_ticket_record; ?>"
												class="el_input_checkbox">
												<input type="checkbox"
													id="ticket_id<?php echo $id_ticket_record; ?>"
													name="ticket_id"
													value="<?php echo esc_attr( $id_ticket_record ); ?>"
												/>
												<span class="checkmark"></span>
											</label>
										</td>
										<td data-colname="<?php esc_attr_e('Event', 'eventlist'); ?>">
											<?php echo esc_html(get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'name_event', true )); ?>
										</td>

										<td data-colname="<?php esc_attr_e('Ticket Type', 'eventlist'); ?>">
											<?php echo esc_html(get_the_title($id_ticket_record)); ?> 
										</td>
										
										<td data-colname="<?php esc_attr_e('Customer', 'eventlist'); ?>">
											<?php echo esc_html(get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'name_customer', true )); ?>
										</td>
										
										<td data-colname="<?php esc_attr_e('Seat', 'eventlist'); ?>">
											<?php
											$seat = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'seat', true );
											$person_type = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'person_type', true );

											if ( $seat ) {
												if ( $person_type ) {
													$seat.= ' - '.$person_type;
												}
											} else {
												$seat = esc_html__("none", "eventlist");
											}
											echo $seat;
											?>
										</td>
										<td data-colname="<?php esc_attr_e( "Action", "eventlist" ); ?>">
											<div class="el_ticket_manager_action_wrap"
											data-ticket-id="<?php echo esc_attr( $id_ticket_record ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce('el_ticket_action') ); ?>">
												<a href="#"
												data-tippy-content="<?php esc_attr_e( 'Download', 'eventlist' ); ?>"
												class="el_btn_ticket el_download_ticket">
													<i class="icon_download"></i>
												</a>
												<a href="#"
												data-tippy-content="<?php esc_attr_e( 'Send Ticket', 'eventlist' ); ?>"
												class="el_btn_ticket el_send_ticket">
													<i class="icon_mail"></i>
												</a>
											</div>
											
										</td>

										<td data-colname="<?php esc_attr_e('Address', 'eventlist'); ?>">
											<?php
											$arr_venue = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'venue', true );
											$address = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'address', true );

											$venue = is_array( $arr_venue ) ? implode(", ", $arr_venue) : $arr_venue;
											if( $venue ){
												echo esc_html__("Venue: ", "eventlist") . $venue . '<br>';
											}
											if( $address ){
												echo esc_html__("Address: ", "eventlist") . $address . '<br>';
											}
											?>
										</td>

										<td data-colname="<?php esc_attr_e('Qr code', 'eventlist'); ?>" class="qr_code" style="width: 120px; word-break: break-all;">
											<?php echo $qr_code; ?>
										</td>

										<td data-colname="<?php esc_attr_e('Start date', 'eventlist'); ?>">
											<?php
											$start_date = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'date_start', true );
											$date_format = get_option('date_format');
											$time_format = get_option('time_format');

											echo date($date_format, $start_date) . ' <br/>@ ' . date($time_format, $start_date);
											?>
										</td>

										<td data-colname="<?php esc_attr_e('End date', 'eventlist'); ?>">
											<?php
											$end_date = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT . 'date_end', true );
											$date_format = get_option('date_format');
											$time_format = get_option('time_format');

											echo date($date_format, $end_date) . ' <br/>@ ' . date($time_format, $end_date);
											?>
										</td>

										<td data-colname="<?php esc_attr_e('Created', 'eventlist'); ?>">
											<?php
											$date_format = get_option('date_format');
											$time_format = get_option('time_format');
											echo get_the_date($date_format, $id_ticket_record) . " <br/>@ " . get_the_date($time_format, $id_ticket_record);
											?>
										</td>

										<td data-colname="<?php esc_attr_e('Check-in', 'eventlist'); ?>">
											<?php 
												$ticket_status = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT.'ticket_status', true );
												$checkin_time_tmp = get_post_meta( $id_ticket_record, OVA_METABOX_EVENT.'checkin_time', true ) ;
												$checkin_time =  $checkin_time_tmp ? date_i18n( get_option( 'date_format' ).' '. get_option( 'time_format' ), $checkin_time_tmp ) : '';

												if( $ticket_status == 'checked' ){ ?>
														<span class="error">

															<?php echo esc_html__( 'Check-in', 'eventlist' ); ?>
															<span class="wrap_info" data-tippy-content="<?php echo esc_attr__( 'Check-in at ', 'eventlist' ).' '.$checkin_time; ?>">
																<i class="icon_info_alt"></i>
															</span>
														</span>

														<a href="#"
															class="el_cancel_check_in"
															role="button" rel="nofollow"
															data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_cancel_checkin' ) ); ?>"
															data-ticket-id="<?php echo esc_attr( $id_ticket_record ); ?>"
															data-tippy-content="<?php esc_attr_e( 'Cancel check in', 'eventlist' ); ?>">
															<i class="fa fa-times-circle" aria-hidden="true"></i>
														</a>


												<?php }else{ ?>
														<a href="#" class="update_ticket_status"
															data-qr_code="<?php echo $qr_code; ?>"
															data-tippy-content="<?php esc_attr_e( 'Update Ticket Status', 'eventlist' ); ?>"
														>

															<i class="icon_check"></i>
														</a>
												<?php } ?>
											 
										</td>

									</tr>
									
								<?php endwhile; else : ?> 
								<td colspan="12"><?php esc_html_e( 'Not Found Tickets', 'eventlist' ); ?></td> 
								<?php ; endif; wp_reset_postdata(); ?>


							</tbody>
						</table>
					<!-- Tickets -->
					</div>
					<?php 
					$total = $list_ticket_record_by_id_event->max_num_pages;
					if ( $total > 1 ) {
						echo pagination_vendor($total);
					} ?>
				</div>

			<?php endif; ?>

		<!-- Create ticket modal -->
		<?php echo el_get_template( "/vendor/create-tickets.php" ); ?>
	</div>
	
</div>