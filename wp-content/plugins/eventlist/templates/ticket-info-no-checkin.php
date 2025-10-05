<?php if( ! defined( 'ABSPATH' ) ) exit();

$ticket_info = EL_Ticket::customer_check_qrcode( $_REQUEST );

get_header();

?>
<div class="ticket_info">
	<div class="container">
		<?php if ( $ticket_info['status'] == 'error' ): ?>
			<div class="message">
				<h3 class="<?php echo esc_attr($ticket_info['status']); ?>">

					<?php echo esc_html( $ticket_info['mess'] ); ?>

				</h3>
			</div>
		<?php else: ?>
		
		<div class="info">
			<ul>
				<li>
					<label>
						<?php esc_html_e( 'Customer', 'eventlist' ); ?>
					</label>
					<div class="value">
						<?php echo esc_html( $ticket_info['name_customer'] ); ?>
					</div>
				</li>
				<li>
					<label>
						<?php esc_html_e( 'Event', 'eventlist' ); ?>
					</label>
					<div class="value">
						<?php echo esc_html( $ticket_info['name_event'] ); ?>
					</div>
				</li>
				<li>
					<label>
						<?php esc_html_e( 'Date time', 'eventlist' ); ?>
					</label>
					<div class="value">
						<?php echo esc_html( $ticket_info['e_cal'] ); ?>
					</div>
				</li>

				<?php if( $ticket_info['seat'] ){ ?>
				<li>
					<label>
						<?php esc_html_e( 'Seat', 'eventlist' ); ?>
					</label>
					<div class="value">
						<?php echo esc_html( $ticket_info['seat'] ); ?>
					</div>
				</li>
				<?php } ?>

				<?php if ( ! empty( $ticket_info['ticket_type'] ) ): ?>
					<li>
						<label>
							<?php esc_html_e( 'Ticket', 'eventlist' ); ?>
						</label>
						<div class="value">
							<?php echo esc_html( $ticket_info['ticket_type'] ); ?>
						</div>
					</li>
				<?php endif; ?>

				<?php if ( ! empty( $ticket_info['extra_service'] ) ): ?>
					<li>
						<label>
							<?php esc_html_e( 'Extra Services', 'eventlist' ); ?>
						</label>
						<div class="value">
							<?php echo esc_html( $ticket_info['extra_service'] ); ?>
						</div>
					</li>
				<?php endif; ?>

				<?php if ( $ticket_info['venue_address'] ): ?>
					
					<li>
						<label>
							<?php esc_html_e( 'Venue & Address', 'eventlist' ); ?>
						</label>
						<div class="value">
							<?php echo esc_html( $ticket_info['venue_address'] ); ?>
						</div>
					</li>

				<?php endif; ?>

				<?php if ( $ticket_info['ticket_status'] ): ?>
					
					<li>
						<label>
							<?php esc_html_e( 'Checked In', 'eventlist' ); ?>
						</label>
						<div class="value ticket_status <?php echo esc_attr( $ticket_info['status_class'] ); ?>">
							<?php echo esc_html( $ticket_info['ticket_status'] ); ?>
						</div>
					</li>

				<?php endif; ?>

			</ul>
		</div>

		<?php endif; ?>

	</div>
</div>
<?php get_footer();