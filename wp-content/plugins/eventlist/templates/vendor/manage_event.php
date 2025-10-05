<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$id_event = isset($_GET['eid']) ? sanitize_text_field($_GET['eid']) : "";
$current_range = isset( $_GET['range'] ) ? $_GET['range'] : '7_day';
$option_calendar = get_post_meta( $id_event, OVA_METABOX_EVENT . 'option_calendar', true);

$format = el_date_time_format_js();
$first_day = el_first_day_of_week();
$placeholder = date( el_date_time_format_js_reverse($format), current_time('timestamp') );

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/manage_event_sidebar.php' ); ?>

	<div class="contents">

		<?php 

		if ( empty($id_event) || !verify_current_user_post( $id_event ) ){
			esc_html_e( 'You don\'t have permission view tickets', 'eventlist' );
			exit();
		}

		echo el_get_template( '/vendor/heading.php' );
		
		echo el_get_template( '/vendor/__event_info.php' ); 

		?>
		<ul class="info-sales" data-id-event="<?php echo esc_attr( $id_event ); ?>">
			<li>
				<label for="">
					<?php echo esc_html__("Total before tax", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_before_tax' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>
			<li>
				<label for="">
					<?php echo esc_html__("Total after tax", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_after_tax' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>
			<li>
				<label for="">
					<?php echo esc_html__("Profit", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_profit' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>
			
			<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_commission', false ) === true ){ ?>
				<li>
					<label for="">
						<?php echo esc_html__("Commission", "eventlist"); ?>
					</label>
					<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_commission' ); ?>">
						<i class="fas fa-eye-slash"></i>
					</div>
				</li>
			<?php } ?>

			<li>
				<label for="">
					<?php echo esc_html__("System Fees", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr('total_system_fee'); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>

			<li>
				<label for="">
					<?php echo esc_html__("Ticket Fees", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_ticket_fee' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>

			<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_tax', true ) ){ ?>
				<li>
					<label for="">
						<?php echo esc_html__("Tax", "eventlist"); ?>
					</label>
					<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_tax' ); ?>">
						<i class="fas fa-eye-slash"></i>
					</div>
				</li>
			<?php } ?>

			<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_coupon', true ) ){ ?>
				<li>
					<label for="">
						<?php echo esc_html__("Coupon", "eventlist"); ?>
					</label>
					<div class="value el_show_data" data-name="<?php echo esc_attr( 'total_coupon' ); ?>">
						<i class="fas fa-eye-slash"></i>
					</div>
				</li>
			<?php } ?>
			

			<li>
				<label for="">
					<?php echo esc_html__("Bookings", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'number_booking' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>

			<li>
				<label for="">
					<?php echo esc_html__("Tickets", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'number_ticket' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>

			<li>
				<label for="">
					<?php echo esc_html__("Check In", "eventlist"); ?>
				</label>
				<div class="value el_show_data" data-name="<?php echo esc_attr( 'number_ticket_checkin' ); ?>">
					<i class="fas fa-eye-slash"></i>
				</div>
			</li>
		</ul>

		

		<!-- Manage Tickets -->
		<div class="manage_tickets">

			<h3>
				<span><?php esc_html_e( 'Manage Tickets', 'eventlist' ); ?></span>
				<a href="#" class="el_show_column_tickets" data-id-event="<?php echo esc_attr( $id_event ); ?>">
					<i class="fas fa-eye-slash"></i>
				</a>
			</h3>

			<div class="column-tickets">
				<?php
					// ob_start();
			
					// el_get_template( '/vendor/__events_table_tickets.php', array( 'post_id' => $id_event ) );
					
					// echo ob_get_clean();
					// wp_reset_postdata();
				?>
			</div>

			<!-- Check if is Recuring Calendar -->
			<?php if ( $option_calendar == 'auto' ) { ?>
				
					<form method="GET">
						<div class="form_date_time_search_ticket">
							<input type="hidden" name="vendor" value="manage_event" />
							<input type="hidden" name="eid" value="<?php echo esc_attr($id_event); ?>" />
							
							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['start_date_2'] ) ) ? esc_attr( wp_unslash( $_GET['start_date_2'] ) ) : ''; ?>" 
							name="start_date_2" 
							class="range_datepicker_2 from" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" required />
						 
							<span>&ndash;</span>

							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['end_date_2'] ) ) ? esc_attr( wp_unslash( $_GET['end_date_2'] ) ) : ''; ?>" 
							name="end_date_2"
							class="range_datepicker_2 to" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" required/>
							<input type="hidden" name="eid" value="<?php echo esc_attr($id_event); ?>" />

							<button type="submit" name = "check_date_search_ticket" class="button" ><?php esc_html_e( 'Go', 'eventlist' ); ?></button>

						</div>
					</form>

					<?php 

					$calendar_recurrence = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar_recurrence', true);
					$start_date_2 = isset( $_GET['start_date_2'] ) ? sanitize_text_field( $_GET['start_date_2'] ) : '';
					$end_date_2 = isset( $_GET['end_date_2'] )  ? sanitize_text_field( $_GET['end_date_2'] ) : '';

					$str_start_date_2 = $start_date_2 ? strtotime($start_date_2) : '';
					$str_end_date_2 = $end_date_2 ? strtotime($end_date_2) : '';

					$date_format = get_option('date_format');
					$time_format = get_option('time_format');
					?>

					<?php if ($calendar_recurrence && $str_start_date_2 && $str_end_date_2 ) { ?>
					    <div class="manage_sale_recurrence">
							<?php
							$events_date = array();
							$schedules_time = get_post_meta( $id_event, OVA_METABOX_EVENT . 'schedules_time', true);
							$list_type_ticket = get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket', true);

							foreach ($calendar_recurrence as $value) {


								if( !in_array( $value['date'], $events_date )){

									array_push($events_date, $value['date'] );

									if(($str_start_date_2 <= (strtotime($value['date']))) && ((strtotime($value['date'])) <= $str_end_date_2 )){

										if ($schedules_time) {
											?>
											<div class='date_time_schedules'>
												<div class='date_time'>
													<div class="date">
														<?php echo date_i18n('l', strtotime($value['date'])).', '.date_i18n($date_format, strtotime($value['date']));?>
													</div>

														<?php
														foreach ($schedules_time as $key => $value_schedules_time) {
															$total_number_ticket_rest = 0;
															foreach ( $list_type_ticket as $ticket ) {
																$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest($id_event, (strtotime($value['date'])).$key,  $ticket['ticket_id']);

																$total_number_ticket_rest += $number_ticket_rest;
															}
															if( $total_number_ticket_rest == 1 ){
																$ticket_text = esc_html__( 'ticket', 'eventlist' );
															}else{
																$ticket_text = esc_html__( 'tickets', 'eventlist' );
															}


															$start_time = isset( $value_schedules_time['start_time'] ) ? el_get_time_int_by_date_and_hour(date('d-m-Y', strtotime($value['date'])), $value_schedules_time['start_time']) : '';
															$end_time = isset( $value_schedules_time['end_time'] ) ? el_get_time_int_by_date_and_hour(date('d-m-Y',strtotime($value['date'])), $value_schedules_time['end_time']) : '';
															$number_time = isset( $value_schedules_time['book_before'] ) ? floatval( $value_schedules_time['book_before'] )*60 : '0';

															$status = false;

															if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id_event ) ) {
																$status = true;
															}

				                                            ?>
					                                            <div class="content_time">
					                                            	<div class = "time_schedules">
					                                            		<?php echo date( get_option('time_format') , strtotime($value_schedules_time['start_time']))?>
					                                            		<span class="to">&nbsp;<?php echo esc_html__( '-', 'eventlist' )?>&nbsp;</span>
					                                            		<?php echo date(get_option('time_format'), strtotime($value_schedules_time['end_time']))?>
					                                                </div>
						                                            <div class="ticket_text">
						                                            	<div class="text-ticket"><?php echo esc_html__('Remaining Tickets', 'eventlist'); ?>
						                                            	<div class="ticket_rest">
						                                            		<span class="calendar_ticket_rest"><?php echo $total_number_ticket_rest?>&nbsp;
						                                            			<span><?php echo $ticket_text;?></span></span>
						                                            	</div>
						                                            	</div>
						                                            </div>
						                                            <div class='button_ticket'>
																		<?php

																		if($status){
																			?>
																			<input type="button" name="edit_ticket_calendar" class="edit_ticket" data-eid="<?php echo esc_attr($id_event); ?>"  data-cal_id="<?php echo esc_attr((strtotime($value['date'])).$key); ?>" value = "<?php esc_html_e('Edit','eventlist')?>" />
																				<div class="submit-load-more sendmail">
																					<div class="load-more">
																						<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
																					</div>
																				</div>
																			<?php

																		}else {
																			?>
																			<span class="close_booking">

																				<?php $event = new EL_Event();
																				echo $event->get_status_event_calendar($start_time, $end_time, $number_time, $id_event ); ?>

																			</span>
																		<?php } ?>	

																	</div>
																	<div class="content_edit_ticket" data-name= "<?php echo esc_attr((strtotime($value['date'])).$key); ?>"></div>
																</div>

																

														<?php } ?>

												</div>
											</div>

										<?php } else { ?>
											<div class="date">
												<?php echo date_i18n('l', strtotime($value['date'])).', '.date_i18n($date_format, strtotime($value['date'])); ?>
											</div>
										  	<div class='date_time_ticket'>
										  		<div class='date_time'>
													  	<?php

														$calendar_recurrence_start_time = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar_recurrence_start_time', true);
														$calendar_recurrence_end_time = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar_recurrence_end_time', true);
														$calendar_recurrence_book_before = floatval(get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar_recurrence_book_before', true));

														$total_number_ticket_rest = 0;
														foreach ( $list_type_ticket as $ticket ) {
															$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest($id_event, (strtotime($value['date'])),  $ticket['ticket_id']);

															$total_number_ticket_rest += $number_ticket_rest;
														}
														if( $total_number_ticket_rest == 1 ){
															$ticket_text = esc_html__( 'ticket', 'eventlist' );
														}else{
															$ticket_text = esc_html__( 'tickets', 'eventlist' );
														}


														$start_time = isset( $calendar_recurrence_start_time) ? el_get_time_int_by_date_and_hour(date('d-m-Y',strtotime($value['date'])), $calendar_recurrence_start_time ): '';

														$end_time = isset( $calendar_recurrence_end_time ) ? el_get_time_int_by_date_and_hour(date('d-m-Y', strtotime($value['date'])), $calendar_recurrence_end_time ): '';
														$number_time = isset( $calendar_recurrence_book_before ) ? floatval($calendar_recurrence_book_before)*60 : '0';


														$status = false;

														if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $id_event ) ) {
															$status = true;
														}
														
														?>

														<div class="time">
															<?php echo date( get_option('time_format') , strtotime($calendar_recurrence_start_time))?>
															<span class="to">
																<?php echo esc_html__( ' - ', 'eventlist' )?>
															</span>
															<?php echo date(get_option('time_format'), strtotime($calendar_recurrence_end_time))?>
													    </div>
										    	</div>


												<div class= "ticket_text_rest">
													<div class="text-ticket"><?php echo esc_html__('Remaining Tickets', 'eventlist')?></div>
													<div class = "ticket_rest">
														<span class="calendar_ticket_rest"><?php echo $total_number_ticket_rest ?>&nbsp;<span><?php echo $ticket_text ?></span></span>
													</div>
												</div>

												<div class='button_ticket'>

													<?php 
													if($status){
													?>

														<input type="button" name="edit_ticket_calendar" class="edit_ticket" data-eid="<?php echo esc_attr($id_event); ?>"  data-cal_id="<?php echo esc_attr((strtotime($value['date']))); ?>" value = "<?php esc_html_e('Edit','eventlist')?>" />
														<div class="submit-load-more sendmail">
															<div class="load-more">
																<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
															</div>
														</div>

													<?php }else { ?>

														<span class="close_booking">

															<?php $event = new EL_Event();
															echo $event->get_status_event_calendar($start_time, $end_time, $number_time, $id_event ); ?>

														</span>
													<?php } ?>
												</div>

												<div class="content_edit_ticket" data-name= "<?php echo esc_attr((strtotime($value['date']))); ?>"></div>

											</div>



											<?php
										}
										
									}
								}
							} ?>
						</div>
					<?php } ?>

			<!-- Check if is Manual Calendar -->		
			<?php }else{

	            $calendar = get_post_meta( $id_event, OVA_METABOX_EVENT . 'calendar', true);

				if (!empty( $calendar) && is_array($calendar) ) { ?>

						<input type="hidden" name="eid" value="<?php echo esc_attr($id_event); ?>" />
						<h4>
							<?php esc_html_e( 'Select Special Date', 'eventlist' ); ?>
						</h4>

						<?php
                       		$arr = get_id_manage_ticket($calendar);
						?>
						<select name="manage_sale_calendar" class="manage_sale_calendar">

							<option value="">--------------------</option>
							
							<?php
							$date_format = get_option('date_format');
							$event = new EL_Event();
							foreach ($arr as $key => $value) {


								$start_time= $value['start_time'];
								$end_time = $value['end_time'];
	


								?>
								<option value="<?php echo $value['id']; ?>" >
									<?php if (  strtotime($start_time) != strtotime($end_time) ) { ?>
										<p class="date">
											<span class="day"><?php echo esc_html($event->get_date_by_format_and_date_time( "l", $start_time )) ?>, </span>
											<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $start_time )) ?>
											-
											<span class="day"><?php echo esc_html($event->get_date_by_format_and_date_time( "l", $end_time )) ?>, </span>
											<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $end_time )) ?>
										</p>
									<?php } else { ?>
										<p class="date">
											<span class="day"><?php echo esc_html($event->get_date_by_format_and_date_time( "l", $start_time )) ?>, </span>
											<?php echo esc_html($event->get_date_by_format_and_date_time( $date_format, $start_time )) ?>
										</p>
									<?php } ?>
								</option>


								<?php

							}

							?>

						</select>
						
						<div class="submit-load-more load">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>

						<div class="desc_calendar_sale">
						</div>
					
				<?php
				}

			} ?>

		</div>


		<!-- Report Sales -->
		<div class="accounting">
			<h3 class="heading"><?php esc_html_e('Report Sales', 'eventlist'); ?></h3>
			<ul class="filter">
				<li class="<?php echo ( 'year' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'manage_event', 'eid' => $id_event, 'range' => 'year' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Year', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( 'last_month' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'manage_event', 'eid' => $id_event, 'range' => 'last_month' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Last Month', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( 'month' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'manage_event', 'eid' => $id_event, 'range' => 'month' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'This Month', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( '7_day' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'manage_event', 'eid' => $id_event, 'range' => '7_day' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Last 7 days', 'eventlist' ); ?>
					</a>
				</li>

				<li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
					<span><?php esc_html_e( 'Custom:', 'eventlist' ); ?></span>
					<form method="GET">
						<div>
							<input type="hidden" name="vendor" value="manage_event" />
							<input type="hidden" name="eid" value="<?php echo esc_attr($id_event); ?>" />
							<input type="hidden" name="range" value="custom" />

							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" 
							name="start_date" 
							class="range_datepicker from" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" />
							<span>&ndash;</span>

							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" 
							name="end_date"
							class="range_datepicker to" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" />

							<button type="submit" class="button" ><?php esc_html_e( 'Go', 'eventlist' ); ?></button>

						</div>
					</form>
				</li>
			</ul>

			<div class="chart">
				<div class="chart-sidebar">

					<?php
						$chart = el_get_chart( $_GET );
					?>

				</div>

				<?php 
				
				?>
				<div id="main_chart" 
					style="width: 100%; height: 400px;" 
					data-chart="<?php echo $chart['chart']; ?>" 
					data-currency_position="<?php echo esc_attr($chart['currency_position']); ?>" 
					data-currency="<?php echo esc_attr($chart['currency']); ?>" 
					data-name_month="<?php echo wp_json_encode($chart['name_month']); ?>" 
					data-monthnames="<?php echo $chart['monthnames'] ?>"
					data-chart_groupby="<?php echo $chart['chart_groupby']; ?>"
					data-timeformat="<?php echo $chart['timeformat']; ?>"
					data-chart_color="<?php echo $chart['chart_color']; ?>"
				>
				</div>
			</div>
		</div>


	</div>
	
</div>

