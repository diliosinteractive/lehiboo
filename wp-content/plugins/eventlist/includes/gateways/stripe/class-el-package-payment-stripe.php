<?php
if ( ! defined('ABSPATH') ) {
	exit();
}

if ( ! class_exists('EL_Package_Payment_Stripe') ) {
	
	class EL_Package_Payment_Stripe {

		public function __construct(){
	        // AJAX
	        $hooks = array(
	            'el_add_package_stripe',
	        );
	        foreach ( $hooks as $hook ) {
	            add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
	            add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
	        }
	        // Redirect thanks page
        	add_action( 'template_redirect' , array( $this, 'el_package_stripe_redirect_url' ) );
		}

		public function el_add_package_stripe(){
			require_once EL_PLUGIN_INC.'gateways/stripe/vendor/autoload.php';

			$secret_key = EL()->options->checkout->get('stripe_secret_key','');
			$stripe = new \Stripe\StripeClient( $secret_key );

			$membership_id = isset( $_POST['membership_id'] ) ? sanitize_text_field( $_POST['membership_id'] ) : '';
			$amount = isset( $_POST['amount'] ) ? sanitize_text_field( $_POST['amount'] ) : '';
			$currency = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_payment_stripe' ) ) {
	            echo json_encode( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
	            wp_die();
	        }

	        try {
	            // Create a PaymentIntent with amount and currency
	            $paymentIntent = $stripe->paymentIntents->create([
	                'amount' => $amount,
	                'currency' => $currency,
	                'payment_method_types' => ['card'],
	            ]);

	            $client_secret = $paymentIntent->client_secret;
	            $transaction_id = $paymentIntent->id;
	            $output = [
	                'client_secret' => $client_secret,
	            ];

	            update_post_meta( $membership_id, OVA_METABOX_EVENT.'transaction_id', $transaction_id );

	            echo json_encode($output);
	            wp_die();
	        } catch (Error $e) {
	            http_response_code(500);
	            echo json_encode(['error' => $e->getMessage()]);
	            wp_die();
	        }

		}

		public function el_package_stripe_redirect_url(){
			$redirect_page_id 	= EL()->options->general->get('redirect_page_id','');
			$vendor_page 		= isset( $_GET['vendor'] ) ? sanitize_text_field( $_GET['vendor'] ) : '';

			if ( is_page( $redirect_page_id ) && $vendor_page == 'package' ) {

				$payment_intent = isset( $_GET['payment_intent'] ) ? sanitize_text_field( $_GET['payment_intent'] ) : '';
            	$redirect_status = isset( $_GET['redirect_status'] ) ? sanitize_text_field( $_GET['redirect_status'] ) : '';

            	if ( $payment_intent && $redirect_status === 'succeeded' ) {
            		$membership_ids = el_package_get_membership_ids_by_order_id( $payment_intent );
            		if ( ! empty( $membership_ids ) ) {
            			foreach ( $membership_ids as $membership_id ) {
            				$current_user_id = get_post_meta( $membership_id, OVA_METABOX_EVENT.'membership_user_id', true );
				            $package = get_post_meta( $membership_id, OVA_METABOX_EVENT.'membership_package_id', true );
				            update_user_meta( $current_user_id, 'package', $package );
				            $membership = array(
								'ID'           => $membership_id,
								'post_status'   => 'Publish',
								'meta_input'	=> array(
									OVA_METABOX_EVENT.'payment' => 'stripe',
								)
							);
							wp_update_post( $membership );
            			}
            		}
            	}
			}
		}
		
	}
	new EL_Package_Payment_Stripe();
}