<?php  if ( !defined( 'ABSPATH' ) ) exit();

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/manage_event_sidebar.php' ); ?>
	<div class="contents">
		<!-- Check capacity of user -->
		<?php
		$id_event = isset($_GET['eid']) ? sanitize_text_field($_GET['eid']) : "";

		if ( ! el_can_manage_booking() || !verify_current_user_post( $id_event ) || empty( $id_event ) ) {
			esc_html_e( 'You don\'t have permission view bookings', 'eventlist' );
			exit();
		}
		
		echo el_get_template( '/vendor/heading.php' );
		echo el_get_template( '/vendor/__event_info.php' );

		$slug_event = get_post_field( 'post_name', $id_event);
		$paged 		= get_query_var('paged') ? get_query_var('paged') : 1;

		// Check capacity of event
		$check_allow_get_list_attendees = check_allow_get_list_attendees_by_event($id_event);
		$check_allow_export_attendees 	= check_allow_export_attendees_by_event($id_event);

		?>

		<?php if ( $check_allow_get_list_attendees == 'yes' ):
			if( empty( $id_event ) || ! verify_current_user_post( $id_event ) ) return;

			//get list booking by id event
			if ( is_array( apply_filters( 'el_manage_bookings_show_status_vendor', array( 'Completed', 'Pending', 'Canceled' ) ) ) ){
				$agrs = [
					'post_type' => 'el_bookings',
					'post_status' => 'publish',
					"meta_query" => [
						'relation' => 'AND',
						[
							"key" => OVA_METABOX_EVENT . 'id_event',
							"value" => $id_event,
						],
						[
							"key" => OVA_METABOX_EVENT . 'status',
							"value" => apply_filters( 'el_manage_bookings_show_status_vendor', array( 'Completed', 'Pending', 'Canceled' ) ),
							'compare' => 'IN'
						]
					],
					"paged" => $paged,
				];	
			}else{
				$agrs = [
					'post_type' => 'el_bookings',
					'post_status' => 'publish',
					"meta_query" => [
						'relation' => 'AND',
						[
							"key" => OVA_METABOX_EVENT . 'id_event',
							"value" => $id_event,
						],
						[
							"key" => OVA_METABOX_EVENT . 'status',
							"value" => apply_filters( 'el_manage_bookings_show_status_vendor', array( 'Completed', 'Pending', 'Canceled' ) ),
							'compare' => '='
						]
					],
					"paged" => $paged,
				];
			}
			
			
			$list_booking_by_id_event 	= new WP_Query( $agrs );
			$list_ckf_output 			= get_option( 'ova_booking_form', array() );
			
			?>
			<div class="table-list-booking">
				<?php if ( $check_allow_export_attendees == 'yes' ) : ?>
					<div class="el-export-csv">
						<a href="javascript:void(0)" id="export-csv-extra"><?php esc_html_e("Export Bookings", "eventlist"); ?></a>
						<div class="list-check-export-csv">
							<ul>
								<li>
									<label for="id-booking" class="el_input_checkbox">
										<?php esc_html_e("ID Booking", "eventlist"); ?>
										<input name="id_booking" value="id_booking" type="checkbox" id="id-booking" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									<label for="id-event" class="el_input_checkbox">
										<?php esc_html_e("Event", "eventlist"); ?>
										<input name="event" value="event" type="checkbox" id="id-event" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									<label for="calendar" class="el_input_checkbox">
										<?php esc_html_e("Calendar", "eventlist"); ?>
										<input name="calendar" value="calendar" type="checkbox" id="calendar" checked="checked">
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
									
									<label for="phone" class="el_input_checkbox">
										<?php esc_html_e("Phone", "eventlist"); ?>
										<input name="phone" value="phone" type="checkbox" id="phone" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="email" class="el_input_checkbox">
										<?php esc_html_e("Email", "eventlist"); ?>
										<input name="email" value="email" type="checkbox" id="email" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="total_before_tax" class="el_input_checkbox">
										<?php esc_html_e("Total before tax", "eventlist"); ?>
										<input name="total_before_tax" value="total_before_tax" type="checkbox" id="total_before_tax" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								
								<li>
									
									<label for="total" class="el_input_checkbox">
										<?php esc_html_e("Total after tax", "eventlist"); ?>
										<input name="total" value="total_after_tax" type="checkbox" id="total" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="profit" class="el_input_checkbox">
										<?php esc_html_e("Profit", "eventlist"); ?>
										<input name="profit" value="profit" type="checkbox" id="profit" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
									
								<li>
									
									<label for="system_fee" class="el_input_checkbox">
										<?php esc_html_e("System Fee", "eventlist"); ?>
										<input name="system_fee" value="system_fee" type="checkbox" id="system_fee" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="ticket_fee" class="el_input_checkbox">
										<?php esc_html_e("Ticket Fee", "eventlist"); ?>
										<input name="ticket_fee" value="ticket_fee" type="checkbox" id="ticket_fee" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>


								<?php if ( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_tax', false ) ){ ?>
									<li>
										
										<label for="tax" class="el_input_checkbox">
											<?php esc_html_e("Tax", "eventlist"); ?>
											<input name="tax" value="tax" type="checkbox" id="tax" checked="checked">
											<span class="checkmark"></span>
										</label>
									</li>
								<?php } ?>

								<li>
									
									<label for="coupon" class="el_input_checkbox">
										<?php esc_html_e("Coupon", "eventlist"); ?>
										<input name="coupon" value="coupon" type="checkbox" id="coupon" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>

								<li>
									
									<label for="status" class="el_input_checkbox">
										<?php esc_html_e("Status", "eventlist"); ?>
										<input name="status" value="status" type="checkbox" id="status" checked="checked">
										<span class="checkmark"></span>
									</label>
								</li>
								<li>
									
									<label for="type-ticket" class="el_input_checkbox">
										<?php esc_html_e("Ticket Type", "eventlist"); ?>
										<input name="ticket_type" value="ticket_type" type="checkbox" id="type-ticket" checked="checked">
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
									
									<label for="date-created" class="el_input_checkbox">
										<?php esc_html_e("Date created", "eventlist"); ?>
										<input name="date_create" value="date_create" type="checkbox" id="date-created" checked="checked">
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
												<label for="<?php echo esc_attr( $key_1 ) ?>">
													<?php echo esc_html( $val['label'] ) ?>
												</label>
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
							<input type="hidden" name="id_event" value="<?php echo esc_attr($id_event) ?>">
							<input type="hidden" name="el_list_ckf" id="el_list_ckf" value="<?php echo esc_attr( json_encode( $list_name_ckf ) ) ?>" />

							<div class="el_export_booking_wrapper">
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

								<button id="button-submit-export-csv" data-slug-event="<?php echo esc_attr( $slug_event ) ?>" data-id-event="<?php echo esc_attr($id_event) ?>"  name="export" class="export-csv-extra">
									<i class="fas fa-file-download"></i>
									<?php esc_html_e("Export CSV", "eventlist") ?>
								</button>
							</div>

						</div>
					</div>
				<?php endif; ?>

				<table>
					<thead class="event_head">
						<tr>
							<td class="id"><?php esc_html_e("ID", "eventlist") ?></td>
							<td><?php esc_html_e("Event", "eventlist") ?></td>
							<td><?php esc_html_e("Calendar Date", "eventlist") ?></td>
							<td><?php esc_html_e("Info", "eventlist") ?></td>
							<td><?php esc_html_e("Total before tax", "eventlist") ?></td>
							<td><?php esc_html_e("Total after tax", "eventlist") ?></td>
							<td><?php esc_html_e("Profit", "eventlist") ?></td>

							<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_commission', true ) === true ){ ?>

								<td><?php esc_html_e( "System Fees", "eventlist" ); ?></td>
								<td><?php esc_html_e( "Ticket Fees", "eventlist" ); ?></td>
							<?php } ?>



							<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_tax', true ) ){ ?>
								<td><?php esc_html_e("Tax", "eventlist") ?></td>
							<?php } ?>

							<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_coupon', true ) ){ ?>
								<td><?php esc_html_e("Coupon", "eventlist") ?></td>
							<?php } ?>

							<td><?php esc_html_e("Ticket Type", "eventlist") ?></td>
							<td><?php esc_html_e("Status", "eventlist") ?></td>
							<td><?php esc_html_e("Date Created", "eventlist") ?></td>
							
							
						</tr>
					</thead>
					<tbody class="event_body">
						<?php 
						if($list_booking_by_id_event->have_posts() ) : while ( $list_booking_by_id_event->have_posts() ) : $list_booking_by_id_event->the_post();

							$profit = '';

							$id_booking = get_the_id();
							$status_post = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'status', true );

							$create_manually = get_post_meta( $id_booking, OVA_METABOX_EVENT.'create_manually', true );

							switch( $status_post ) {

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
									$status = $status_post;
									break;
								}
							}

							if($status_post == "Completed" || $status_post == "Pending"){

								if( get_post_meta( $id_booking, OVA_METABOX_EVENT . 'profit', true ) ){ // Use from version 1.3.7
									$profit = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'profit', true );
								}else{
									$profit = EL_Booking::instance()->get_profit_by_id_booking($id_booking);	
								}

								if( get_post_meta( $id_booking, OVA_METABOX_EVENT . 'tax', true ) ){ // Use from version 1.3.7
									$tax = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'tax', true );
								}else{
									$tax = EL_Booking::instance()->get_tax_by_id_booking($id_booking);	
								}
								
							}

							?>
							<tr>
								<td data-colname="<?php esc_attr_e('ID', 'eventlist'); ?>" class="id" >
									<?php echo $id_booking; ?>
								</td>
								<td data-colname="<?php esc_attr_e('Event', 'eventlist'); ?>" >
									<?php echo get_post_meta( $id_booking, OVA_METABOX_EVENT . 'title_event', true ); ?>
								</td>
								<td data-colname="<?php esc_attr_e('Calendar Date', 'eventlist'); ?>" >
									<?php echo get_post_meta( $id_booking, OVA_METABOX_EVENT . 'date_cal', true ); ?>
								</td>
								<td data-colname="<?php esc_attr_e('Info', 'eventlist'); ?>" >
									<?php
									$html = esc_html__("Name: ", "eventlist") . get_post_meta( $id_booking, OVA_METABOX_EVENT . 'name', true ) . '<br>';
									$html .= esc_html__("Phone: ", "eventlist") . get_post_meta( $id_booking, OVA_METABOX_EVENT . 'phone', true ) . '<br>';
									$html .= esc_html__("Email: ", "eventlist") . get_post_meta( $id_booking, OVA_METABOX_EVENT . 'email', true ) . '<br>';
									echo $html;
									?>
								</td>

								<td data-colname="<?php esc_attr_e('Total before tax', 'eventlist'); ?>" >
									<?php echo el_price(get_post_meta( $id_booking, OVA_METABOX_EVENT . 'total', true )); ?>
								</td>

								<td data-colname="<?php esc_attr_e('Total', 'eventlist'); ?>" >
									<?php echo el_price(get_post_meta( $id_booking, OVA_METABOX_EVENT . 'total_after_tax', true )); ?>
								</td>

								<td data-colname="<?php esc_attr_e('Profit', 'eventlist'); ?>" >

									<?php 
									if ( $create_manually === "yes" ) {
										echo esc_html( el_price( 0 ) );
									} else {
										echo esc_html( el_price( $profit ) );
									}
									?>
									
								</td>



								<?php
									$system_fee = 0;
									$ticket_fee = 0;
									$commission = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'commission', true ) ?? 0;
									if ( $create_manually !== "yes" ) {
										if( ! $commission ){
											$commission = EL_Booking::instance()->get_commission_by_id_booking($id_booking);    
										}
										$system_fee = get_post_meta( $id_booking, OVA_METABOX_EVENT."system_fee", true );
										$ticket_fee = is_numeric( $commission ) ? (float)$commission - $system_fee : 0;
									}
									
								?>

								<td>
									<?php echo esc_html( el_price( $system_fee ) ); ?>
								</td>
								<td>
									<?php
										echo esc_html( el_price( $ticket_fee ) );
									?>
								</td>

								
								<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_tax', true ) ){ ?>
									<td data-colname="<?php esc_attr_e('Tax', 'eventlist'); ?>" >
										<?php echo esc_html(el_price($tax)) ?>
									</td>
								<?php } ?>

								<?php if( current_user_can( 'manage_options' ) || apply_filters( 'el_vendor_view_coupon', true ) ){ ?>
									<td data-colname="<?php esc_attr_e('Coupon', 'eventlist'); ?>" >
										<?php 
											echo get_post_meta( $id_booking, OVA_METABOX_EVENT . 'coupon', true );
										 ?>
									</td>									
								<?php } ?>

								<td data-colname="<?php esc_attr_e('Ticket Type', 'eventlist'); ?>" >
									<?php

									$seat_option = get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true);

									if ( $seat_option !== "map" ) {
										$list_ticket_in_event = get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket', true);

										$list_ticket = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'list_id_ticket', true );
										$list_ticket = json_decode($list_ticket);

										$list_qty_ticket_by_id_ticket = get_post_meta( $id_booking, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true );
										
										$html = "";
										if ( ! empty($list_ticket_in_event) && is_array($list_ticket_in_event) ) {
											foreach ($list_ticket_in_event as $ticket) {
												if ( in_array($ticket['ticket_id'], $list_ticket) ) {
													
													$html .= $ticket['name_ticket'] .' - '.$list_qty_ticket_by_id_ticket[ $ticket['ticket_id'] ].' '.esc_html__( 'ticket(s)', 'eventlist' ). '<br>';
												}
											}
										}
										echo $html;
									} else {
										$cart 			= get_post_meta( $id_booking, OVA_METABOX_EVENT . 'cart', true);
										$ticket_type 	= el_ticket_type_seat_map_cart( $cart );
										echo $ticket_type;
									}
									
									?>
								</td>

								<td data-colname="<?php esc_attr_e('Status', 'eventlist'); ?>" >
									<?php echo $status ?>
								</td>

								
								<td data-colname="<?php esc_attr_e('Date Created', 'eventlist'); ?>" class="last-colname">
									<?php
									$date_format = get_option('date_format');
									$time_format = get_option('time_format');
									echo get_the_date($date_format, $id_booking) . " - " . get_the_date($time_format, $id_booking);
									?>
								</td>
								
								
							</tr>
						<?php endwhile; else : ?> 
						<td colspan="14"><?php esc_html_e( 'Not Found Bookings', 'eventlist' ); ?></td> 
						<?php ; endif; wp_reset_postdata(); ?>

						
					</tbody>
				</table>
				<?php 
				$total = $list_booking_by_id_event->max_num_pages;
				if ( $total > 1 ) {
					echo pagination_vendor($total);
				}
				?>
			</div>

		<?php endif; ?>

	</div>
	
</div>

