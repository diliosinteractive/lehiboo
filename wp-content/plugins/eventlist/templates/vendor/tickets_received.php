<?php 
if ( !defined( 'ABSPATH' ) ) exit();

?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<?php
		$list_tickets = EL_Ticket::instance()->el_ticket_received();


	
		
		?>
		<div class="table-list-ticket-received">
			<div class="el-notify">
				<p class="success status"></p>
				<p class="error status"></p>
			</div>
			<table>
				<thead>
					<tr>
						<td class="id"><?php esc_html_e("ID", "eventlist"); ?></td>
						<td><?php esc_html_e("Event", "eventlist"); ?></td>
						<td><?php esc_html_e("Ticket Type", "eventlist"); ?></td>
						<td><?php esc_html_e("Status", "eventlist"); ?></td>
						<td><?php esc_html_e("Seat", "eventlist"); ?></td>
						<td><?php esc_html_e("Venue & Address", "eventlist"); ?></td>
						<td><?php esc_html_e("Qr code", "eventlist"); ?></td>
						<td><?php esc_html_e("Start date", "eventlist"); ?></td>
						<td><?php esc_html_e("End date", "eventlist"); ?></td>
						<td><?php esc_html_e("Action", "eventlist"); ?></td>
					</tr>
				</thead>
				<tbody class="event_body">
					<?php if ( $list_tickets ){ ?>
						
						<?php foreach ( $list_tickets as $key => $ticket ):
							setup_postdata( $ticket );
							$ticket_id = $ticket->ID;

							$event_id 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'event_id', true );
							$ticket_status 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'ticket_status', true );
							$seat 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'seat', true );
							$arr_venue 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'venue', true );
							$address 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'address', true );

							$person_type = get_post_meta( $ticket_id, OVA_METABOX_EVENT.'person_type', true );
							if ( $person_type ) {
								$seat.= ' - '.$person_type;
							}

							$venue = is_array( $arr_venue ) ? implode(", ", $arr_venue) : $arr_venue;
							$venue_address = '';
							if( ! empty( $venue ) ){
								$venue_address .= sprintf( esc_html__( 'Venue: %s', 'eventlist' ), $venue );
							}
							if( $address ){
								if ( $venue_address ) {
									$venue_address .= ';';
								}
								$venue_address .= sprintf( esc_html__( 'Address: %s', 'eventlist' ), $address );
							}
							$ticket_qr 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'qr_code', true );
							$start_date 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'date_start', true );
							$end_date 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'date_end', true );
							$date_format 	= get_option('date_format');
							$time_format 	= get_option('time_format');

							$start_date_time = date_i18n($date_format, $start_date) . ' - ' . date_i18n($time_format, $start_date);
							$end_date_time = date_i18n($date_format, $end_date) . ' - ' . date_i18n($time_format, $end_date);
							?>
							<tr>
								<td><?php echo esc_html( $ticket_id ); ?></td>
								<td><a href="<?php echo esc_url( get_permalink( $event_id ) ); ?>"><?php echo esc_html( get_the_title( $event_id ) ); ?></a></td>
								<td><?php echo esc_html( get_the_title( $ticket_id ) ); ?></td>
								<td><?php echo esc_html( $ticket_status ); ?></td>
								<td><?php echo esc_html( $seat ); ?></td>
								<td><?php echo esc_html( $venue_address ); ?></td>
								<td>
									<div class="ticket_qr_wrap">
										<button class="ticket_qr_toggle"><i class="fas fa-eye"></i></button>
										<span class="ticket_qr"><?php echo esc_html( $ticket_qr ); ?></span>
									</div>
								</td>
								<td><?php echo esc_html( $start_date_time ); ?></td>
								<td><?php echo esc_html( $end_date_time ); ?></td>
								<td>
									<div class="button-download-ticket-received">
										<div class="submit-load-more download-ticket-received">
											<div class="load-more">
												<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
											</div>
										</div>
										<button class="button download-ticket-received"
										data-nonce="<?php echo wp_create_nonce( 'el_ticket_received_download_nonce' ) ?>"
										data-id="<?php echo esc_attr( $ticket_id ); ?>"
											><?php esc_html_e( 'Download', 'eventlist' ); ?></button>
									</div>
									<?php
									$site_url = get_bloginfo( 'url' );
									$url = add_query_arg( 'post_type', 'event', $site_url );
									$url = add_query_arg( 'id_ticket', $ticket_id, $url );
									$url = add_query_arg( 'qr_code', $ticket_qr, $url );
									$url = add_query_arg( 'customer_check_qrcode', 'true', $url );
									$url = add_query_arg( '_nonce', wp_create_nonce( 'el_check_qrcode' ), $url );
									?>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button customer-check-qrcode"><?php esc_html_e( 'Check Ticket', 'eventlist' ); ?></a>
								</td>
							</tr>
						<?php endforeach;wp_reset_postdata(); ?>
						
					<?php } else { ?>
					<tr>
						<td colspan="10"><?php esc_html_e( 'Ticket not found', 'eventlist' ); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			
		</div>

	</div>
	
</div>
