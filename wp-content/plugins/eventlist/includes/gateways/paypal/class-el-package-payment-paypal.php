<?php
if ( ! defined('ABSPATH') ) {
	exit();
}

require_once EL_PLUGIN_INC.'gateways/paypal/vendor/autoload.php';

// import namespace
use PayPal\Http\Environment\SandboxEnvironment;
use PayPal\Http\Environment\ProductionEnvironment;
use PayPal\Http\PayPalClient;

use PayPal\Checkout\Requests\OrderCreateRequest;
use PayPal\Checkout\Orders\AmountBreakdown;
use PayPal\Checkout\Orders\Item;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;

use PayPal\Checkout\Requests\OrderCaptureRequest;

if ( ! class_exists('EL_Package_Payment_Paypal') ) {
	
	class EL_Package_Payment_Paypal {

		protected $client;

		public function __construct(){
			$client_id 		= EL()->options->checkout->get('paypal_public_key','');
	        $client_secret 	= EL()->options->checkout->get('paypal_secret_key','');
	        $paypal_mode 	= EL()->options->checkout->get('paypal_mode','live');
	        // create a new sandbox environment
	        switch ( $paypal_mode ) {
	            case 'test':
	                $environment = new SandboxEnvironment( $client_id, $client_secret );
	                break;
	            
	            default:
	                $environment = new ProductionEnvironment( $client_id, $client_secret );
	                break;
	        }
	        
	        // create a new client
	        $this->client = new PayPalClient( $environment );
	        // AJAX
	        $hooks = array(
	            'el_package_paypal_create_order',
	            'el_package_paypal_capture_order',
	        );
	        foreach ( $hooks as $hook ) {
	            add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
	            add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
	        }
		}

		public function el_package_paypal_create_order(){
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_package_paypal' ) ) {
	            echo json_encode( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
	            wp_die();
	        }
	        $membership_id = isset( $_POST['membership_id'] ) ? sanitize_text_field( $_POST['membership_id'] ) : '';
	        if ( ! $membership_id ) {
	            echo json_encode( ['el_error' => esc_html__( 'An error occurred while package registration.', 'eventlist' ) ] );
	            wp_die();
	        }

	        $package_title = get_the_title( $membership_id );
	        $amount = get_post_meta( $membership_id, OVA_METABOX_EVENT.'total', true );
	        $amount = round( $amount, 2 );
	        $zero_decimal = array("HUF","JPY","TWD");
	        $currency = EL()->options->general->get('currency','USD');
	        if ( in_array( $currency, $zero_decimal ) ) {
	        	$amount = ceil( $amount );
	        }
	        
	        // Create a purchase unit with the total amount
	        $purchase_unit = new PurchaseUnit(AmountBreakdown::of($amount, $currency));
	        // Create & add item to purchase unit
	        $purchase_unit->addItem(Item::create( $package_title, $amount, $currency, 1));

	        // Create a new order with intent to capture a payment
	        $order = new Order();

	        // Add a purchase unit to order
	        $order->addPurchaseUnit($purchase_unit);

	        // Create an order create http request
	        $request = new OrderCreateRequest($order);

	        // Send request to PayPal
	        try {
				$response = $this->client->send($request);

		        // Add order id to booking
		        $order_data = json_decode((string) $response->getBody());
		        $order_id = $order_data->id;
		        if ( $order_id ) {
		            update_post_meta( $membership_id, OVA_METABOX_EVENT.'transaction_id', $order_id );
		        }
		        // Parse result
		        wp_send_json( $order_data );
		        wp_die();
	        } catch (Exception $e){
	            wp_send_json( ['el_error' => $e->getMessage() ] );
	            wp_die();
	        }
	        
		}

		public function el_package_paypal_capture_order(){
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_package_paypal' ) ) {
	            wp_send_json( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
	            wp_die();
	        }
	        $order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
	        if ( ! $order_id ) {
	            wp_send_json( ['el_error' => esc_html__( 'An error occurred while package registration.', 'eventlist' ) ] );
	            wp_die();
	        }
	        // Create an order capture http request
	        $request = new OrderCaptureRequest($order_id);

	        // Send request to PayPal
	        $response = $this->client->send($request);
	        $order_data = json_decode((string) $response->getBody());
	        $status     = $order_data->status;

	        if ( $status === 'COMPLETED' ) {
	        	$membership_ids = el_package_get_membership_ids_by_order_id( $order_id );
	        	if ( ! empty( $membership_ids ) ) {
	        		foreach ( $membership_ids as $membership_id ) {
	        			$current_user_id = get_post_meta( $membership_id, OVA_METABOX_EVENT.'membership_user_id', true );
			            $package = get_post_meta( $membership_id, OVA_METABOX_EVENT.'membership_package_id', true );
			            update_user_meta( $current_user_id, 'package', $package );
			            $membership = array(
							'ID'           => $membership_id,
							'post_status'   => 'Publish',
							'meta_input'	=> array(
								OVA_METABOX_EVENT.'payment' => 'paypal',
							)
						);
						wp_update_post( $membership );
	        		}
	        	}
	        }
	        // Parse result
	        wp_send_json( $order_data );
	        wp_die();
		}
	}

	new EL_Package_Payment_Paypal();
}