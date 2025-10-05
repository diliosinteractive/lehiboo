<?php if( ! defined( 'ABSPATH' ) ) exit();
get_header();

$ticket_info = EL_Ticket::validate_qrcode( $_REQUEST ); 


?>
<div class="ticket_info">
	<div class="container">

		<div class="message">
			<h3 class="<?php echo esc_attr( $ticket_info['status'] ); ?>">

				<?php echo esc_html( $ticket_info['msg'] ); ?>

				<?php if( $ticket_info['status'] == 'checked-in' ){ ?>
						<?php echo ' '.esc_html_e( 'at', 'eventlist' ).' '.esc_html( $ticket_info['checkin_time'] ); ?>
				<?php } ?>
			</h3>
		</div>
		
		

		<!-- if the qrcode is valid -->
		<?php 
			if( $ticket_info['status'] == 'valid' || $ticket_info['status'] == 'checked-in' ){ ?>

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

						<?php if ( ! empty( $ticket_info['extra_service'] ) ): ?>
							<li>
								<label>
									<?php esc_html_e( 'Extra Services', 'eventlist' ); ?>
								</label>
								<div class="value">
									<?php echo wp_kses_post( $ticket_info['extra_service'] ); ?>
								</div>
							</li>
						<?php endif; ?>
						
					</ul>
				</div>
				
			<?php }
		 ?>

	</div>
</div>
<?php get_footer();