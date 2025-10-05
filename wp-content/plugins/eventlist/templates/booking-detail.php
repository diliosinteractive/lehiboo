<?php if( ! defined( 'ABSPATH' ) ) exit();

$event_name 	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'title_event', true );
$customer_name 	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'name', true );
$phone 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'phone', true );
$address 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'address', true );
$email 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'email', true );
$cart 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'cart', true );
$discount 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'discount', true );
$subtotal 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'total', true );
$system_fee 	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'system_fee', true );
$coupon 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'coupon', true );
$tax			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'tax', true );
$total 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'total_after_tax', true );
?>


<div class="el_booking_detail_wrapper">

	<table class="el_booking_detail_table">
		<tr>
			<th>
				<?php esc_html_e( 'Booking ID', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( '#'.$booking_id ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Event', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( $event_name ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Customer Name', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( $customer_name ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Phone', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( $phone ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Email', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( $email ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Address', 'eventlist' ); ?>
			</th>
			<td>
				<?php echo esc_html( $address ); ?>
			</td>
		</tr>
	</table>

	<div class="el_booking_cart">

		<table class="el_booking_cart_table" border="1" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th><?php esc_html_e( 'Ticket', 'eventlist' ); ?></th>
				<th><?php esc_html_e( 'Quantity', 'eventlist' ); ?></th>
				<th><?php esc_html_e( 'Price', 'eventlist' ); ?></th>
				<th><?php esc_html_e( 'Total', 'eventlist' ); ?></th>
			</tr>

			<?php if ( ! empty( $cart ) && is_array( $cart ) ): ?>
				<?php foreach ( $cart as $cart_item ) :
					$item_qty = isset( $cart_item['qty'] ) ? absint( $cart_item['qty'] ) : 1;
					$person_type = isset( $cart_item['person_type'] ) ? $cart_item['person_type'] : '';
					?>
					<?php if ( isset( $cart_item['data_person'] ) && $cart_item['data_person'] ): ?>
						<?php foreach ($cart_item['data_person'] as $k => $val): ?>

							<?php if ( $val['qty'] ): ?>
								<tr>
									<td>
										<span><?php echo esc_html( $cart_item['id'] . ' - ' . $val['name'] ); ?></span>
									</td>
									<td>
										<span><?php echo esc_html( $val['qty'] ); ?></span>
									</td>
									<td>
										<?php echo wp_kses_post( el_pdf_price( (float)$val['price'] ) ); ?>
									</td>
									<td>
										<?php echo wp_kses_post( el_pdf_price( (float)$val['price'] * (int)$val['qty'] ) ); ?>
									</td>
								</tr>
							<?php endif; ?>
							
						<?php endforeach; ?>
					<?php else: ?>
					<tr>
						<td>
							<?php if ( isset( $cart_item['name'] ) && $cart_item['name'] ): ?>
								<span><?php echo esc_html( $cart_item['name'] ); ?></span>
							<?php else: ?>
								<?php if ( isset( $cart_item['id'] ) && $cart_item['id'] ): ?>

									<?php if ( empty( $person_type  ) ): ?>
										<span><?php echo esc_html( $cart_item['id'] ); ?></span>
									<?php else: ?>
										<span><?php echo esc_html( $cart_item['id'].' - '.$person_type ); ?></span>
									<?php endif; ?>

								<?php endif; ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( isset( $cart_item['qty'] ) && $cart_item['qty'] ): ?>
								<span><?php echo esc_html( $cart_item['qty'] ); ?></span>
								<?php if ( isset( $cart_item['seat'] ) && $cart_item['seat'] && is_array( $cart_item['seat'] ) ): ?>
									<span>
										<?php echo ' - '.esc_html( implode( ', ', $cart_item['seat'] ) ); ?>
									</span>
								<?php endif; ?>
							<?php else: ?>
								<span>1</span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo wp_kses_post( el_pdf_price( $cart_item['price'] ) ); ?>
						</td>
						<td>
							<?php echo wp_kses_post( el_pdf_price( (float)$cart_item['price'] * (int)$item_qty ) ); ?>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach;
			endif; ?>

			<?php if ( ! empty( $data['extra_service'] ) ): ?>
				<?php foreach ( $data['extra_service'] as $val ): ?>
					<?php if ( $val['qty'] > 0 ): ?>
						<tr>
							<td>
								<span><?php echo esc_html( $val['name'] ); ?></span>
							</td>
							<td>
								<span><?php echo esc_html( $val['qty'] ); ?></span>
							</td>
							<td>
								<span><?php echo wp_kses_post( el_pdf_price( $val['price'] ) ); ?></span>
							</td>
							<td>
								<span><?php echo wp_kses_post( el_pdf_price( $val['price'] * $val['qty'] ) ); ?></span>
							</td>
						</tr>
					<?php endif; ?>

				<?php endforeach; ?>
			<?php endif; ?>
		</table>
	</div>

	<table class="el_booking_cart_total"  style="border: 0;" cellpadding="0" cellspacing="0" width="100%">
		<tr  style="border: 0;">
			<td class="cart_total_left" style="border: 0;">

			</td>
			<td class="cart_total_right"  style="border: 0;">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">

					<?php if ( $subtotal ): ?>
						<tr>
							<th><?php esc_html_e( 'Subtotal', 'eventlist' ); ?></th>
							<td>
								<span><?php echo wp_kses_post( el_pdf_price( $subtotal + floatval( $discount ) - floatval( $system_fee ) ) ); ?></span>
							</td>
						</tr>
					<?php endif; ?>

					<?php if ( $discount ): ?>
						<tr>
							<th><?php esc_html_e( 'Discount', 'eventlist' ); ?></th>
							<td>
								<span><?php wp_kses_post( '-'.el_pdf_price( $discount ).' ('.$coupon.')' ); ?></span>
							</td>
						</tr>
					<?php endif; ?>

					<?php if ( $tax ): ?>
						<tr>
							<th><?php esc_html_e( 'Tax', 'eventlist' ); ?></th>
							<td>
								<span><?php echo wp_kses_post( el_pdf_price( $tax ) ); ?></span>
							</td>
						</tr>
					<?php endif; ?>
					
					<?php if ( $system_fee ): ?>
						<tr>
							<th><?php esc_html_e( 'System fee', 'eventlist' ); ?></th>
							<td>
								<span><?php echo wp_kses_post( el_pdf_price( $system_fee ) ); ?></span>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th><?php esc_html_e( 'Total', 'eventlist' ); ?></th>
						<td>
							<span><?php echo wp_kses_post( el_pdf_price( $total ) ); ?></span>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>

	<p class="el_booking_action">
		<a href="#" class="el_download_invoice" data-booking-id="<?php echo esc_attr( $booking_id ); ?>">
			<?php esc_html_e( 'Download Invoice', 'eventlist' ); ?>
		</a>
		<a href="#" class="el_download_tickets" data-booking-id="<?php echo esc_attr( $booking_id ); ?>">
			<?php esc_html_e( 'Download Tickets', 'eventlist' ); ?>
		</a>
	</p>
	
</div>
