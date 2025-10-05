<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo sprintf( esc_html__( 'Ticket #%s', 'eventlist' ), esc_html( $ticket['ticket_id'] ) ); ?></title>
		<style type="text/css">
			<?php
				echo $ticket['css'];
			?>
		</style>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<table class="wrapper border" cellpadding="0" cellspacing="0" width="100%">
			<tr class="border">
				<td class="left border-right">
					<table class="padding" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" class="padding">
								<p class="label">
									<?php esc_html_e( 'Event', 'eventlist' ); ?>:
								</p>
								<?php if ( ! empty( $ticket['event_name'] ) ): ?>
									<p class="content">
										<?php echo esc_html( $ticket['event_name'] ); ?>
									</p>
								<?php endif; ?>
								
							</td>
						</tr>
						<tr class="border-top">
							<td class="padding border-right">
								<p class="label">
									<?php esc_html_e( 'Time', 'eventlist' ); ?>:
								</p>
								<?php if ( ! empty( $ticket['date'] ) ): ?>
									<p class="content">
										<?php echo esc_html( $ticket['date'] ); ?>
									</p>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['time'] ) ): ?>
									<p class="content">
										<?php echo esc_html( $ticket['time'] ); ?>
									</p>
								<?php endif; ?>
							</td>
							<td class="padding">
								<p class="label">
									<?php esc_html_e( 'Venue', 'eventlist' ); ?>:
								</p>
								<?php if ( ! empty( $ticket['venue'] ) ): ?>
									<p class="content">
										<?php echo esc_html( $ticket['venue'] ); ?>
									</p>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['address'] ) ): ?>
									<p class="content">
									<?php echo esc_html( $ticket['address'] ); ?>
								</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr class="border-top">
							<td colspan="2" class="padding">
								<p class="label">
									<?php esc_html_e( 'Order Info', 'eventlist' ); ?>:
								</p>
								<?php if ( ! empty( $ticket['order_info'] ) ): ?>
									<p class="content">
										<?php echo wp_kses_post( $ticket['order_info'] ); ?>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr class="border-top">
							<td colspan="2" class="padding">
								<p class="label">
									<?php esc_html_e( 'Ticket', 'eventlist' ); ?>:
								</p>
								<p class="content">
									#<?php echo esc_html( $ticket['ticket_id'] ); ?> - <?php echo esc_html( $ticket['type_ticket'] ); ?>
								</p>
							</td>
						</tr>
						<?php if ( ! empty( $ticket['extra_service'] ) ): ?>
							<tr class="border-top">
								<td colspan="2" class="padding">
									<p class="label">
										<?php esc_html_e( 'Extra Services', 'eventlist' ); ?>:
									</p>
									<p class="content">
										<?php echo wp_kses_post( $ticket['extra_service'] ); ?>
									</p>
								</td>
								
							</tr>
						<?php endif; ?>
					</table>
				</td>
				<td class="right">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">

			  			<?php if( $ticket['logo_url'] ){ ?>
							<tr>
								<td class="padding">
									<img src="<?php echo esc_url($ticket['logo_url']); ?>" width="150" />
								</td>
							</tr>
						<?php } ?>

						<tr>
							<td class="padding">
								<barcode code="<?php echo esc_attr( $ticket['qrcode'] ); ?>" type="QR" disableborder="1" size="1" />
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>

		<?php if( apply_filters( 'el_show_qrcode_pdf_ticket', true ) ){ ?>	
			<p class="content">
				<?php echo sprintf( esc_html__( 'Qr code: %s', 'eventlist' ), esc_html( $ticket['qrcode_str'] ) ); ?>
			</p>
		<?php } ?>

		<!-- Description Ticket -->
		<p style="color: <?php echo esc_attr( apply_filters( 'el_desc_ticket_pdf', '#333333' ) ); ?>">
			<?php echo wp_kses_post( $ticket['desc_ticket'] ); ?>
		</p>

		<!-- Private Ticket -->
		<p style="color: <?php echo esc_attr( apply_filters( 'el_private_desc_ticket_pdf', '#333333' ) ); ?>">
			<?php echo wp_kses_post( $ticket['private_desc_ticket'] ); ?>
		</p>
	</body>
</html>