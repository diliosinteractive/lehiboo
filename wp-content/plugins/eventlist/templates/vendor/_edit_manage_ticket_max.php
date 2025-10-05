		<?php if( ! defined( 'ABSPATH' ) ) exit();	
		if( !isset( $_POST['data'] ) ) wp_die();
		$post_data = $_POST['data'];
		$cal_id = isset( $post_data['cal_id'] ) ? sanitize_text_field( $post_data['cal_id'] ) : '';
		$id = isset( $post_data['eid'] ) ? sanitize_text_field( $post_data['eid'] ) : '';
		$_prefix = OVA_METABOX_EVENT;

		$list_type_ticket = get_post_meta( $id, $_prefix. 'ticket', true);
		?>
		<form class="content_edit">
			<span class="close_edit_ticket"><span class="icon_close_alt2"></span></span>
			<div class="item-ticket-type">
				<div class="ticket-name">
					<label><?php esc_html_e("Ticket Type", "eventlist") ?></label>
				</div>
				<div class="sold-ticket">
					<label><?php esc_html_e("Sold", "eventlist") ?></label>
				</div>
				<div class="rest-ticket">
					<label><?php esc_html_e("Remaining Tickets", "eventlist") ?></label>
				</div>
				<div class="total-ticket">
					<label><?php esc_html_e("Max Ticket", "eventlist") ?></label>
				</div>
			</div>
			<?php
			foreach ( $list_type_ticket as $ticket ){

				$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest($id, $cal_id,  $ticket['ticket_id']);
				$number_ticket_sold = EL_Booking::instance()->get_number_ticket_booked($id, $cal_id,  $ticket['ticket_id']);
				$number_ticket_total = EL_Booking::instance()->get_number_ticket_total($id, $cal_id,  $ticket['ticket_id']);
				$ticket_max = get_post_meta( $id, $_prefix.'ticket_max[' .$cal_id.'_'. $ticket['ticket_id'].']' , true) !='' ? get_post_meta( $id, $_prefix .'ticket_max['.$cal_id .'_'. $ticket['ticket_id'] .']', true) : '';


				?>
				<div class="content-item-ticket">
					<div class="ticket-name">
						<p><?php echo esc_html( $ticket['name_ticket']) ?></p>

					</div>

					<div class="sold-ticket">

						<p class="sold_ticket"><?php echo esc_html($number_ticket_sold) ?></p>

					</div>

					<div class="max-ticket">

						<p class="number_ticket"><?php echo esc_html( $number_ticket_rest ) ?></p>

					</div>

					<div class="total-ticket">

						<?php $number_ticket_max = ($ticket_max != '') ? $ticket_max :$number_ticket_total?>
						<input 
						type="number" 
						class="number_ticket_max" 
						name="<?php echo esc_attr( $_prefix.'ticket_max['.$cal_id.'_'. $ticket['ticket_id'].']' ); ?>"  
						value="<?php echo esc_html($number_ticket_max) ?>" min="0"/>
					</div>
				</div>



				<?php

			}


			?>
			<div class="el_save_edit_ticket_max">
				<input type="submit" name="el_update_ticket_max" class="el_submit_btn el_update_ticket_max" value="<?php esc_html_e( 'Save', 'eventlist' ); ?>" />
				<input type="hidden" name="list_type_ticket" value="<?php echo esc_attr(json_encode($list_type_ticket)); ?>" />

				<input type="hidden" name="eid" value="<?php echo esc_attr($id); ?>" />
				<input type="hidden" name="cal_id" value="<?php echo esc_attr($cal_id); ?>" />

				<div class="submit-load-more sendmail">
					<div class="load-more">
						<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
					</div>
				</div>
				<?php wp_nonce_field( 'el_update_ticket_max_nonce', 'el_update_ticket_max_nonce' ); ?>
			</div>

		</form>

		<?php
		?>