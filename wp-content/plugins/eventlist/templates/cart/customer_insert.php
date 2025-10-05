<?php if( ! defined( 'ABSPATH' ) ) exit();
	$seat_option 	= isset( $args['seat_option'] ) && $args['seat_option'] ? $args['seat_option'] : 'no_seat';
	$cart 			= isset( $args['cart'] ) && $args['cart'] ? $args['cart'] : array();
	$event_id 		= isset( $args['event_id'] ) && $args['event_id'] ? $args['event_id'] : '';
	$flag 			= 1;
	$seat_names = [];
?>

<?php if ( ! empty( $cart ) && is_array( $cart ) ): ?>
	<?php foreach( $cart as $k => $ticket ): ?>
		<?php if ( 'map' === $seat_option ):
			$qty 		= isset( $ticket['qty'] ) ? absint( $ticket['qty'] ) : 0;
			$ticket_id 	= isset( $ticket['id'] ) ? $ticket['id'] : '';
			$person_type = isset( $ticket['person_type'] ) ? $ticket['person_type'] : '';

			if ( ! empty( $person_type ) ) {
				$ticket_id .= ' - '.$person_type;
			}

			if ( isset( $ticket['data_person'] ) ) {
				foreach ( $ticket['data_person'] as $key => $value ) {
					$qty+= (int) $value['qty'];
					for ($i=0; $i < (int) $value['qty']; $i++) { 
						$seat_names[] = $ticket_id.' - '.$value['name'];
					}
				}
			} else {
				if ( $qty ) {
					for ($i=0; $i < absint( $qty ); $i++) { 
						$seat_names[] = $ticket_id;
					}
				} else {
					$seat_names[] = $ticket_id;
				}
				
			}

			if ( $qty ):
				for ( $j = 0; $j < $qty; $j++ ):
					// first skip
					if ( $k == 0 && $j == 0 ) continue;
			?>
					<ul class="input_ticket_receiver input_mult_ticket el_ticket_<?php echo esc_attr( $flag ); ?>" data-ticket="<?php echo esc_attr( $flag ); ?>">
						<?php el_get_seat_html_form_cart( $seat_names, $flag ); ?>
						<?php el_get_template( 'cart/customer_fields.php', array( 'seat_option' => $seat_option, 'index' => $flag, 'event_id' => $event_id ) ); ?>
					</ul>
				<?php $flag++; endfor; ?>
			<?php else:
				if ( $k == 0 ) continue;
			?>
				<ul class="input_ticket_receiver input_mult_ticket el_ticket_<?php echo esc_attr( $flag ); ?>" data-ticket="<?php echo esc_attr( $flag ); ?>">
					<?php el_get_seat_html_form_cart( $seat_names, $flag ); ?>
					<?php el_get_template( 'cart/customer_fields.php', array( 'seat_option' => $seat_option, 'index' => $flag, 'event_id' => $event_id ) ); ?>
				</ul>
			<?php $flag++; endif; ?>
		<?php else: 
			$qty = isset( $ticket['qty'] ) && $ticket['qty'] ? absint( $ticket['qty'] ) : 0;
			$ticket_name = isset( $ticket['name'] ) && $ticket['name'] ? $ticket['name'] : esc_html__( 'No Name', 'eventlist' );
		?>
			<?php if ( $k == 0 && $qty <= 1 ) continue; ?>
			<?php if ( 'simple' === $seat_option ): ?>
				<?php for( $i = 0; $i < $qty; $i++ ): ?>
					<?php if ( $k == 0 && $i == 0 ) continue; ?>
					<ul class="input_ticket_receiver input_mult_ticket el_ticket_<?php echo esc_attr( $flag ); ?>" data-ticket="<?php echo esc_attr( $flag ); ?>">
						<?php el_get_ticket_type_html_form_cart( $cart, $ticket_name ); ?>
						<?php el_get_template( 'cart/customer_fields.php', array( 'seat_option' => $seat_option, 'index' => $flag, 'event_id' => $event_id ) ); ?>
					</ul>
				<?php $flag++; endfor; ?>
			<?php else: ?>
				<?php for( $i = 0; $i < $qty; $i++ ): ?>
					<?php if ( $k == 0 && $i == 0 ) continue; ?>
					<ul class="input_ticket_receiver input_mult_ticket el_ticket_<?php echo esc_attr( $flag ); ?>" data-ticket="<?php echo esc_attr( $flag ); ?>">
						<?php el_get_template( 'cart/customer_fields.php', array( 'seat_option' => $seat_option, 'index' => $flag, 'event_id' => $event_id ) ); ?>
					</ul>
				<?php $flag++; endfor; ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>