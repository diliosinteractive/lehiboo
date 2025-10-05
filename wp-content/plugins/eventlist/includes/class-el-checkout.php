<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EL_Checkout', false ) ) {
	return new EL_Checkout();
}

class EL_Checkout{

	protected static $_instance = null;

	protected $_prefix = OVA_METABOX_EVENT;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct(){
	}

	/**
	 * Process Checkout
	 */
	public function process_checkout( $post_data ) {
		if ( empty( $post_data ) ) $post_data = $_POST['data'];

		$cart = isset($post_data['cart']) ? $post_data['cart'] : [];

		// Validate Booking
		$validate_booking = isset( $post_data['ide'] ) ? EL_Booking::instance()->validate_before_booking() : false;

		$session_msg 	= EL()->msg_session->get( 'el_message' );
		$el_content 	= EL()->msg_session->get( 'el_content' );
		$el_option 		= EL()->msg_session->get( 'el_option' );
		$el_reload_page = EL()->msg_session->get( 'el_reload_page' );
		EL()->msg_session->remove();

		if ( ! $validate_booking ) {
			$data['el_message'] 	= $session_msg;
			$data['el_content'] 	= $el_content;
			$data['el_option'] 		= $el_option;
			$data['el_reload_page'] = $el_reload_page;
			echo json_encode($data);
			wp_die();
		}

		$checkout_holding_ticket = EL()->options->checkout->get('checkout_holding_ticket', 'no');

		if ( $checkout_holding_ticket === 'yes' ) {
			$check_holding_ticket = EL_Booking::instance()->el_check_holding_ticket( $post_data );

			if ( ! empty( $check_holding_ticket ) && is_array( $check_holding_ticket ) ) {
				$data['el_message'] 	= $check_holding_ticket['el_message'];
				$data['el_option'] 		= $check_holding_ticket['el_option'];
				$data['el_reload_page'] = $check_holding_ticket['el_reload_page'];
				echo json_encode($data);
				wp_die();
			}
		}
		
		// Add Booking
		if ( $post_data['seat_option'] != 'map' ) {
			$booking_id = EL_Booking::instance()->add_booking();

		} else {
			$booking_id = EL_Booking::instance()->add_booking_map();
		}
		
		if ( ! $booking_id ) return false;

		if ( $checkout_holding_ticket === 'yes' ) {
			EL_Booking::instance()->el_create_holding_ticket( $post_data, $booking_id );
		}

		// Setup a session for cart
		EL()->cart_session->remove();
		EL()->cart_session->set( 'booking_id', $booking_id );

		$amount = get_post_meta( $booking_id, OVA_METABOX_EVENT.'total_after_tax', true );

		$payment = EL()->payment_gateways->el_payment_gateways_avaiable();

		if ( $payment && isset( $post_data['payment_method'] ) && array_key_exists( $post_data['payment_method'] , $payment ) ) {
			if ( $amount == 0 ) {
				$result = $payment['free']->process();
			} else {
				$result = $payment[$post_data['payment_method']]->process();
			}
			
			$data['el_url'] = $result['url'];
			$data['payment_method'] = isset( $result['payment_method'] ) ? $result['payment_method'] : '';
			$data['show_countdown'] = $checkout_holding_ticket === 'yes' ? true : false;
			$data['booking_id'] = $booking_id;
			$data['amount'] = $amount;
		}
		echo json_encode( $data );
	}
}

