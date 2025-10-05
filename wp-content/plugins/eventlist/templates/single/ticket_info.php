<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
global $event;

$id 					= get_the_ID();
$list_type_ticket 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true);
$seat_option 			= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true);
$list_calendar_ticket 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar', true);
$option_calendar 		= get_post_meta( $id, OVA_METABOX_EVENT . 'option_calendar', true);
$calendar_recurrence 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence', true);
$ticket_link 			= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true );
$ticket_link_price 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link_price', true );

?>

	<?php if ( $ticket_link != 'ticket_external_link' && ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) && ( ( ! empty( $list_calendar_ticket ) && $option_calendar == "manual") || ( ! empty( $calendar_recurrence ) && $option_calendar == "auto") ) && $seat_option != 'map' ): ?>

		<div class="ticket-info event_section_white">
			<h3 class="heading second_font">
				<?php esc_html_e("Ticket Information", "eventlist"); ?>
			</h3>
			

				<?php foreach ( $list_type_ticket as $ticket ):
					$html_price = "";

					if ( is_array( $ticket ) && array_key_exists( 'type_price', $ticket ) ) {
						switch ( $ticket['type_price'] ) {
							case 'paid' : {
								$html_price = ( $ticket['price_ticket'] == 0 ) ? esc_html__( 'Free', 'eventlist' ) : el_price( $ticket['price_ticket'] );
								break;
							}
							case 'free' : {
								$html_price = esc_html__( 'Free', 'eventlist' );
								break;
							}
						}
					}
					$data_empty_desc 	= empty($ticket['desc_ticket']) ? 'true' : 'false';
					$class_empty 		= empty($ticket['desc_ticket']) ? 'empty-desc' : '';

					?>
					<div class="item-info-ticket">
						<div class="heading-ticket <?php echo esc_attr( $class_empty ); ?>" data-desc="<?php echo esc_attr( $data_empty_desc ); ?>">
							<div class="coupon_tool_tip">
								<p class="title-ticket">
									<?php if ( isset( $ticket['desc_ticket'] ) && $ticket['desc_ticket'] ): ?>
										<i class="arrow_carrot-down"></i>
									<?php endif; ?>
									<?php echo isset( $ticket['name_ticket'] ) ? esc_html( $ticket['name_ticket'] ) : ''; ?>
								</p>
								<?php if ( apply_filters( 'el_show_coupon_ticket_single', true ) ):

									$coupon_data = el_get_data_coupon( $id );

									if ( $coupon_data ): ?>
										<?php 
											$ticket_ids = array_column($coupon_data, 'id', 'id'); 
											if( in_array($ticket['ticket_id'], $ticket_ids) ){ ?>
												<label>&nbsp;&nbsp;<?php esc_html_e(' - Code:', 'eventlist'); ?>&nbsp;</label>		
											<?php }
										?>

										<?php foreach ( $coupon_data as $key => $value_coupon ):
											if ( $value_coupon['id']==$ticket['ticket_id'] ):
												$title = sprintf( esc_attr__( 'Remaining: %1$s, Discount: %2$s', 'eventlist' ), $value_coupon['reamaing'], $value_coupon['discount'] );
												?>
												<span
													class="wrap_info"
													data-tippy-content="<?php echo esc_attr( $title ); ?>">
													<span class="coupon">
														<?php echo esc_html( $value_coupon['name'] ); ?>
													</span>
													&nbsp;
													&nbsp;
												</span>
										<?php endif;
										endforeach;
									endif;
								endif; ?>
							</div>
							<div class="wp-price-status">
								<p class="price">
									<?php echo wp_kses_post( $html_price ); ?>
								</p>
								<?php if ( isset( $ticket['start_ticket_date'] ) && isset( $ticket['start_ticket_time'] ) && isset( $ticket['close_ticket_date'] ) && isset( $ticket['close_ticket_time'] ) ): ?>
									<span class="stattus">
										<?php echo esc_html( $event->get_status_ticket_info_by_date_and_time( $ticket['start_ticket_date'], $ticket['start_ticket_time'], $ticket['close_ticket_date'], $ticket['close_ticket_time'], $id ) ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="desc-ticket" >
							<div class="desc">
								<p><?php echo esc_html( $ticket['desc_ticket'] ); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			
		</div>
	<?php else:
		$seat_info = el_seat_ticket_info( $id );
	?>
		<div class="ticket-info event_section_white">
			<h3 class="heading second_font"><?php esc_html_e("Ticket Information", "eventlist"); ?></h3>

			<?php if ( ! empty( $seat_info ) && is_array( $seat_info ) ): ?>
				<?php foreach ( $seat_info as $item_seat_info ): ?>
					<div class="item-info-ticket">
					<div class="heading-ticket empty-desc" data-desc="<?php echo $item_seat_info['desc_seat'] ? 'false' : 'true'; ?>">
						<div class="coupon_tool_tip">
							<p class="title-ticket">
								<?php if ( $item_seat_info['desc_seat'] ): ?>
									<i class="arrow_carrot-down"></i>
								<?php endif; ?>
								<?php echo esc_html( $item_seat_info['type_seat'] ); ?>
							</p>
						</div>
						<div class="wp-price-status">
							<p class="price"><?php echo esc_html( $ticket_link_price ); ?></p>
							<span class="stattus">
								<?php echo esc_html( $event->get_status_ticket_info_by_date_and_time( $item_seat_info['start_date'], $item_seat_info['start_time'], $item_seat_info['end_date'], $item_seat_info['end_time'], $id ) ); ?>
							</span>
						</div>
					</div>
					<div class="desc-ticket" >
						<div class="desc">
							<p><?php echo esc_html( $item_seat_info['desc_seat'] ); ?></p>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			<?php else:
				$ticket_map = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_map', true );
				$start_date = isset( $ticket_map['start_ticket_date'] ) ? $ticket_map['start_ticket_date'] : '';
				$start_time = isset( $ticket_map['start_ticket_time'] ) ? $ticket_map['start_ticket_time'] : '';
				$close_date = isset( $ticket_map['close_ticket_date'] ) ? $ticket_map['close_ticket_date'] : '';
				$close_time = isset( $ticket_map['close_ticket_time'] ) ? $ticket_map['close_ticket_time'] : '';
			?>
				<div class="item-info-ticket">
					<div class="heading-ticket empty-desc" data-desc="true">
						<div class="coupon_tool_tip">
							<p class="title-ticket"><?php esc_html_e('Tickets', 'eventlist'); ?></p>
						</div>
						<div class="wp-price-status">
							<p class="price"><?php echo esc_html( $ticket_link_price ); ?></p>
							<span class="stattus">
								<?php echo esc_html( $event->get_status_ticket_info_by_date_and_time( $start_date, $start_time, $close_date, $close_time, $id ) ); ?>
							</span>
						</div>
					</div>
				</div>
			<?php endif; ?>
		
		</div>
	<?php endif; ?>
