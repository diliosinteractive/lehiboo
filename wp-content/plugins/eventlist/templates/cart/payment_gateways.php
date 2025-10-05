<?php if( ! defined( 'ABSPATH' ) ) exit();

$id_event = isset( $_GET['ide'] ) && ! empty( $_GET['ide'] ) ? sanitize_text_field( $_GET['ide'] ) : '';

$ticket_total_price = el_check_ticket_price_show_payment( $id_event );

// List All Payment Gateways Actived
$payments = el_payment_gateways_active();
if( $payments ){ ?>
	
	<?php
	do_action( 'el_before_payments_checkout' );

	$count_payment = count( $payments );
	if ( array_key_exists('free', $payments ) ) {
		$count_payment -= 1; 
	}

	$classes_hide_payments = ( ! empty( $payments["free"] ) && count( $payments ) == 1 ) || $ticket_total_price == 0 || ( $count_payment === 1 && ! array_key_exists('stripe', $payments ) && ! array_key_exists('paypal', $payments ) ) ? 'hide_payments' : '';
	?>
	<div class="el_payments <?php echo esc_attr( $classes_hide_payments ); ?>">
		<h3 class="cart_title"> <?php esc_html_e( 'Payment Method', 'eventlist' ); ?> </h3>
		<div class="error-empty-input error-payment">
			<span ><?php esc_html_e("field is required ", "eventlist") ?></span>
		</div>
		<ul>
			<?php 
			$i = 0;
			foreach ( $payments as $key => $payment ) { 
				
				$checked = ( $i == 0 ) ? "checked" : "";
				if ( $ticket_total_price > 0 && $payment->id == 'free' ) {
					continue;
				}

				?>
				<li class="<?php echo esc_attr( $payment->id ); ?>">
					<div class="type-payment">
						<input class="circle-<?php echo esc_attr($i) ?>" id="payment-<?php echo esc_attr($i) ?>" type="radio" name="payment" value="<?php echo esc_attr( $payment->id ); ?>" <?php echo esc_attr($checked) ?> />
						<label for="payment-<?php echo esc_attr($i) ?>"><?php echo esc_html( $payment->get_title() ); ?></label>
						<div class="outer-circle"></div>
					</div>

					<div class="payment_form">
						<?php $payment->render_form(); ?>	
					</div>
				</li>
			<?php
				$i++;
			} ?>
		</ul>
	</div>
	<?php do_action( 'el_before_payments_checkout' ); ?>
<?php } else {
	?>
	<p><?php esc_html_e( 'No payment methods have been set up yet.', 'eventlist' ); ?></p>
	<?php
}


