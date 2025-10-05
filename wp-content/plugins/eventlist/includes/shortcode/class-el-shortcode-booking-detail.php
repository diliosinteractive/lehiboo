<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Booking_Detail extends EL_Shortcode {

	public $shortcode = 'el_booking_detail';

	public function __construct() {
		parent::__construct();
	}

	function add_shortcode( $args, $content = null ){

		$order_key 		= isset( $_GET['key'] ) ? $_GET['key'] : '';
		$order_key_arr 	= explode( '_', $order_key );
		$booking_id 	= isset( $order_key_arr[0] ) ? $order_key_arr[0] : '';
		$_order_key 	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'order_key', true );

		$html = '';
		if ( $booking_id && $order_key == $_order_key ) {
			ob_start();
			el_get_template( 'booking-detail.php', array( 'booking_id' => $booking_id ) );
			$html = ob_get_clean();
		}
		return $html;
	}
}


new El_Shortcode_Booking_Detail();