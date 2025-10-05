<?php if ( !defined( 'ABSPATH' ) ) { exit(); }

if ( ! class_exists('EL_Package_Payment_Woo') ) {
	
	class EL_Package_Payment_Woo {

		public function __construct(){

			// AJAX
			$hooks = array(
	            'el_add_package_woo',
	        );
	        foreach ( $hooks as $hook ) {
	            add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
	            add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
	        }
		}

		public function el_add_package_woo(){

			$membership_id 	= isset( $_POST['membership_id'] ) ? sanitize_text_field( $_POST['membership_id'] ) : '';

			EL()->cart_session->remove();
			
			$product_id = EL()->options->package->get('product_payment_package'); //replace with your own product id

			if( class_exists('WooCommerce') ){
				WC()->cart->empty_cart();
			}

			$url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'cart' ) ): home_url('/');

			echo json_encode( array(
				'url' => add_query_arg( array(
					'add-to-cart' => $product_id,
					'membership_id' => $membership_id
				),
				$url)
			) );
			wp_die();
		}
	}
	new EL_Package_Payment_Woo();
}