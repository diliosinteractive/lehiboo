<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	$data = $args['data'];
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo esc_html( $data['title'] ); ?></title>
		<style type="text/css">
			<?php
				echo $data['css'];
			?>
		</style>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<table class="wrapper" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
			<tr>
				<td class="header">
					<!-- Header -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%">
								<?php if ( $data['logo_url'] ): ?>
									<img src="<?php echo esc_url( $data['logo_url'] ); ?>" alt="<?php echo esc_attr( $data['logo_alt'] ); ?>" style="max-width: 100%;">
								<?php endif; ?>
							</td>
							<td>
								<div class="shop-name">
									<h2><?php echo esc_html( $data['shop_name'] ); ?></h2>
								</div>
								<div class="shop-address"><?php echo esc_html( $data['shop_address'] ); ?></div>
							</td>
						</tr>
					</table>
			
				</td>
			</tr>
			
			<tr>
				<td class="document-type-label">
					<?php if ( $data['title'] ): ?>
						<h1>
							<?php echo esc_html( $data['title'] ); ?>
						</h1>
					<?php endif; ?>
					<!-- Booking -->
				</td>
			</tr>
			
			<tr>
				<td class="booking_info">
	
					<table border="0" cellpadding="0" cellspacing="0" width="100%">

						<tr>
							<td width="60%">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
								
									<?php if ( $data['customer_name'] ): ?>
										<tr>
											<td>
												<?php echo esc_html( $data['customer_name'] ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['customer_phone'] ): ?>
										<tr>
											<td>
												<a href="tel:<?php echo esc_attr( preg_replace( "/[^0-9]/", "", $data['customer_phone'] )); ?>">
													<?php echo esc_html( $data['customer_phone'] ); ?>
												</a>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['customer_email'] ): ?>
										<tr>
											<td>
												<a href="mailto:<?php echo esc_attr( $data['customer_email'] ); ?>">
													<?php echo esc_html( $data['customer_email'] ); ?>
												</a>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['customer_address'] ): ?>
										<tr>
											<td>
												<?php echo esc_html( $data['customer_address'] ); ?>
											</td>
										</tr>
									<?php endif; ?>
								</table>
							</td>

							<td>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<?php if ( $data['booking_number'] ) : ?>
										<tr>
											<td>
												<?php esc_html_e( 'Booking Number:', 'eventlist' ); ?>
											</td>
											<td>
												<?php echo esc_html('#'.$data['booking_number'] ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['event_calendar'] ) : ?>
										<tr>
											<td>
												<?php esc_html_e( 'Event Calendar: ', 'eventlist' ); ?>
											</td>
											<td>
												<?php echo esc_html( $data['event_calendar'] ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['payment_method'] ) : ?>
										<tr>
											<td><?php esc_html_e( 'Payment Method:', 'eventlist' ); ?></td>
											<td><?php echo esc_html( $data['payment_method'] ); ?></td>
										</tr>
									<?php endif; ?>

									<?php if ( $data['booking_status'] ) : ?>
										<tr>
											<td><?php esc_html_e( 'Booking Status:', 'eventlist' ); ?></td>
											<td><?php echo esc_html( $data['booking_status'] ); ?></td>
										</tr>
									<?php endif; ?>

								</table>
							</td>
						</tr>
						
					</table>
	
				</td>
			</tr>
			
			<tr>
				<td class="event">
					<?php if ( $data['event_name'] && $data['event_link'] ): ?>
					<h3>
						<span class="label"><?php esc_html_e( 'Event Name:', 'eventlist' ); ?></span>
						<span class="link">
							<a href="<?php echo esc_url( $data['event_link'] ); ?>" target="_blank">
								<?php echo esc_html( $data['event_name'] ); ?>
							</a>
						</span>
					</h3>
					<?php endif; ?>
				</td>
			</tr>
			
			<tr>
				<td class="cart">
					<table border="1" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<th><?php esc_html_e( 'Ticket', 'eventlist' ); ?></th>
							<th><?php esc_html_e( 'Quantity', 'eventlist' ); ?></th>
							<th><?php esc_html_e( 'Price', 'eventlist' ); ?></th>
							<th><?php esc_html_e( 'Total', 'eventlist' ); ?></th>
						</tr>

						<?php if ( ! empty( $data['cart_details'] ) && is_array( $data['cart_details'] ) ): ?>
							<?php foreach ( $data['cart_details'] as $cart_item ) :
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
				</td>
			</tr>

			<tr>
				<td class="total_wrapper">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%">

							</td>
							<td class="total_inner">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<?php if ( $data['subtotal'] ): ?>
										<tr>
											<th><?php esc_html_e( 'Subtotal', 'eventlist' ); ?></th>
											<td>
												<span><?php echo wp_kses_post( el_pdf_price( $data['subtotal'] + floatval( $data['discount'] ) - floatval( $data['system_fee'] ) ) ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ( $data['discount'] ): ?>
										<tr>
											<th><?php esc_html_e( 'Discount', 'eventlist' ); ?></th>
											<td>
												<span><?php echo esc_html( '-'.el_pdf_price($data['discount'] ).' ('.$data['coupon'].')' ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ( $data['tax'] ): ?>
										<tr>
											<th><?php esc_html_e( 'Tax', 'eventlist' ); ?></th>
											<td>
												<span><?php echo wp_kses_post( el_pdf_price( $data['tax'] ) ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ( $data['system_fee'] ): ?>
										<tr>
											<th><?php esc_html_e( 'System fee', 'eventlist' ); ?></th>
											<td>
												<span><?php echo wp_kses_post( el_pdf_price( $data['system_fee'] ) ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
			
									<tr>
										<th><?php esc_html_e( 'Total', 'eventlist' ); ?></th>
										<td>
											<span><?php echo wp_kses_post( el_pdf_price( $data['total'] ) ); ?></span>
										</td>
									</tr>
			
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td>
					<?php if ( $data['footer'] ): ?>
						<div id="footer">
							<hr>
							<?php echo $data['footer']; ?>
						</div>
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</body>
</html>