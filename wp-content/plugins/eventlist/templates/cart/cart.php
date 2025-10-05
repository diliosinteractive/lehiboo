<?php if ( ! defined( 'ABSPATH' ) ) exit(); ?>

<?php
    $cookie_ide 	= isset( $_COOKIE['id_event'] ) ? ( $_COOKIE['id_event'] ) : '';
    $cookie_idcal 	= isset( $_COOKIE['id_cal'] ) ? ( $_COOKIE['id_cal'] ) : '';
	$id_event 		= isset( $_GET['ide'] ) ? $_GET['ide'] : $cookie_ide;
	$id_calendar 	= isset( $_GET['idcal'] ) ? $_GET['idcal'] : $cookie_idcal;

	if ( $id_event == "" && $id_calendar == "" ) { ?>
		<article id="el_cart" class="cart_detail">
			<div class="cart_empty">
				<?php 
					esc_html_e( 'The Cart is empty', 'eventlist' );
					return; 
				?>	
			</div>
		</article>
	<?php }
	
	$current_time 	= current_time('timestamp');
	$url_img 		= get_the_post_thumbnail_url( $id_event, 'full' );
	$status_event 	= get_post_status($id_event);
	$data_calendar 	= el_get_calendar_core( $id_event, $id_calendar );

	global $el_message_cart;
	
	$el_message_cart = "";

	if ( is_array( $data_calendar ) && ! empty( $data_calendar ) ) {
		$start_time = el_get_time_int_by_date_and_hour($data_calendar['date'], $data_calendar['start_time']);
		$end_time 	= isset( $data_calendar['end_date'] ) ? el_get_time_int_by_date_and_hour($data_calendar['end_date'], $data_calendar['end_time']) : el_get_time_int_by_date_and_hour($data_calendar['date'], $data_calendar['end_time']);
		$number_time = isset( $data_calendar['book_before'] ) ? floatval($data_calendar['book_before'])*60 : '0';

		if ( ! el_validate_selling_ticket( $start_time, $end_time, $number_time, $id_event ) ) {
			$el_message_cart = esc_html__("Time out. Please choose another day.", "eventlist");
		}
	} else {
		$el_message_cart = esc_html__("The Event isn't exists", "eventlist");
	}

	if ( $status_event != 'publish' && $status_event != 'private' && apply_filters( 'el_show_private_event', true ) === true ) {
		$el_message_cart = esc_html__("The Event isn't available", "eventlist");
	}
?>

<div class="wp-cart-header" style="background-image:url(<?php echo esc_url( $url_img ); ?>)">
	<?php
		/**
		 * Display Header Cart Page
		 * Hook: el_header_cart
		 * @hooked: el_header_cart - 10
		 */
		do_action( 'el_header_cart' );
	?>
</div>

<article id="el_cart" class="cart_detail">

	<?php if ( ! post_password_required( $id_event ) ): ?>

	
		<div class="cart-content">
			<div class="step-1 step-1-hide-step-2">
				<?php

				/**
				 * Display Ticket Type
				 * Hook: el_cart_ticket_type
				 * @hooked: el_cart_ticket_type - 10
				 */
				do_action( 'el_cart_ticket_type' );
				?>
			</div>
			<!-- end step-1 -->
			<div class="step-2">
				<?php
				/**
				 * Input Information of Receiver
				 * Hook: el_customer_input
				 * @hooked: el_customer_input - 10
				 */
				do_action( 'el_customer_input' );

				/**
				 * List Payment Gateways
				 */
				do_action( 'el_payment_gateways' );

				/**
				 * Terms and Condition
				 */
				if( EL()->options->checkout->get( 'show_terms_condition', 1 ) ){
					do_action( 'el_terms_condition' );
				}
				// recapcha
				do_action( 'meup_cart_event_recapcha' );

				?>
			</div>
			<!-- end step-2 -->
		</div>
		<div class="cart-sidebar">
			<div class="step-2">
				<?php
				/**
				 * Display Customer Infomation
				 * Hook: el_customer_info
				 * @hooked: el_customer_info - 10
				 */
				do_action( 'el_customer_info' );
				
				/**
				 * Payment method choosed
				 * Hook: el_payment_method
				 * @hooked: el_payment_method
				 * 
				 */
				do_action( 'el_payment_method' );
				?>
			</div>
			<!-- end step-2 -->
			<div class="step-1">
				<?php
				/**
				 * Calculate to display total amount
				 * Hook: el_cart_info - 10
				 * @hooked:  el_cart_info - 10
				 */
				do_action( 'el_cart_info' );

				/**
				 * Hook: el_cart_discount - 10
				 * @hooked:  el_cart_discount - 10
				 */
				do_action( 'el_cart_discount' );

				/**
				 * Hook: el_cart_next_step_button - 10
				 * @hooked:  el_cart_next_step_button - 10
				 */
				do_action( 'el_cart_next_step_button' );
				?>
			</div>
			<div class="step-2">
				<?php
				/**
				 * Hook: el_cart_checkout_button - 10
				 * @hooked:  el_cart_checkout_button - 10
				 */
				do_action( 'el_cart_checkout_button' );
				?>
			</div>
			<!-- end step-1 -->
		</div>
	<?php else: ?>
		<?php get_the_password_form(); ?>
	<?php endif; ?>
</article>