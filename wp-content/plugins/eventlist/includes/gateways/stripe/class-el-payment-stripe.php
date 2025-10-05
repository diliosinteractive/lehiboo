<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

class EL_Payment_Stripe extends EL_Abstract_Payment{

	public $id = 'stripe';
    protected $public_key;
    protected $currency;
    protected $redirect_url;
    protected $themes;

	function __construct(){
        parent::__construct();

        $this->public_key   = EL()->options->checkout->get('stripe_public_key','');
        $this->currency     = EL()->options->general->get('currency','USD');
        $this->redirect_url = get_thanks_page();
        $this->themes       = EL()->options->checkout->get('stripe_themes','stripe');

        $this->_title       = esc_html__( 'Stripe', 'eventlist' );
        // AJAX
        $hooks = array(
            'el_payment_stripe',
        );
        foreach ( $hooks as $hook ) {
            add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
            add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
        }
        // Redirect thanks page
        add_action( 'template_redirect' , array( $this, 'el_stripe_redirect_url' ) );
    }

    function fields(){
    	return array(
            'title' => esc_html__('Stripe','eventlist'), // tab title
            'fields' => array(
                'fields' => array(
                    array(
                        'type' => 'select',
                        'label' => __( 'Active', 'eventlist' ),
                        'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
                        'atts' => array(
                            'id' => 'stripe_active',
                            'class' => 'stripe_active'
                        ),
                        'default' => 'no',
                        'name' => 'stripe_active',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                    array(
                        'type'  => 'input',
                        'label' => __( 'Public Key', 'eventlist' ),
                        'desc'  => esc_html__( 'Find here: https://dashboard.stripe.com/account/apikeys', 'eventlist' ),
                        'name'  => 'stripe_public_key'
                    ),
                    array(
                        'type'  => 'input',
                        'label' => __( 'Secret Key', 'eventlist' ),
                        'desc'  => esc_html__( 'Find here: https://dashboard.stripe.com/account/apikeys', 'eventlist' ),
                        'name'  => 'stripe_secret_key'
                    ),
                    array(
                        'type' => 'select',
                        'label' => __( 'Send Tickets after registering successfully', 'eventlist' ),
                        'atts' => array(
                            'id' => 'stripe_send_ticket',
                            'class' => 'stripe_send_ticket'
                        ),
                        'name' => 'stripe_send_ticket',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => __( 'Themes', 'eventlist' ),
                        'atts' => array(
                            'id' => 'stripe_themes',
                            'class' => 'stripe_themes'
                        ),
                        'name' => 'stripe_themes',
                        'options' => array(
                            'stripe' => __( 'Stripe', 'eventlist' ),
                            'night' => __( 'Night', 'eventlist' ),
                            'flat' => __( 'Flat', 'eventlist' )
                        )
                    ),
                   
                ),
            )
        );
		
    }

    public function el_payment_stripe(){
        require_once EL_PLUGIN_INC.'gateways/stripe/vendor/autoload.php';
        $secret_key = EL()->options->checkout->get('stripe_secret_key','');
        $stripe = new \Stripe\StripeClient( $secret_key );

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_payment_stripe' ) ) {
            echo json_encode( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
            wp_die();
        }

        $amount = isset( $_POST['amount'] ) ? (int) sanitize_text_field( $_POST['amount'] ) : 0;
        $currency = strtolower( EL()->options->general->get('currency','USD') );
        $booking_id = isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';

        try {
            // Create a PaymentIntent with amount and currency
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'payment_method_types' => ['card'],
            ]);

            $client_secret = $paymentIntent->client_secret;
            $transaction_id = $paymentIntent->id;

            $redirect_url = apply_filters( 'el_redirect_url_stripe', get_thanks_page(), $booking_id );

            $output = [
                'client_secret' => $client_secret,
                'redirect_url'  => $redirect_url,
            ];

            update_post_meta( $booking_id, OVA_METABOX_EVENT.'client_secret', $client_secret );
            update_post_meta( $booking_id, OVA_METABOX_EVENT.'transaction_id', $transaction_id );

            echo json_encode($output);
            wp_die();
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            wp_die();
        }
    }

    function render_form(){

        ?>
        <span><?php esc_html_e( 'When you select this payment method, after submitting the form, please enter the correct payment information to complete the booking process.', 'eventlist' ); ?></span>
        <div class="modal fade" id="payment_stripe_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e( 'Please enter payment information.','eventlist' ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="el_payment_stripe_error"></div>
                        <div id="el_payment_stripe"
                        data-key="<?php echo esc_attr( $this->public_key ); ?>"
                        data-currency="<?php echo esc_attr( strtolower($this->currency) );?>"
                        data-url="<?php echo esc_url( $this->redirect_url ); ?>"
                        data-themes="<?php echo esc_attr( $this->themes ); ?>"
                        ></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary el_loading" type="button" disabled>
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        <?php esc_html_e( 'Loading...', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><?php esc_html_e( 'Close', 'eventlist' ); ?></button>
                        <button type="button"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_payment_stripe' ) ); ?>"
                        class="btn btn-success"
                        id="el_payment_stripe_submit"><?php esc_html_e( 'Submit', 'eventlist' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
   
    }

    function process( ){

        $booking_id = EL()->cart_session->get( 'booking_id' );
        return array(
            'status'            => 'success',
            'payment_method'    => 'stripe',
            'url'               => get_thanks_page(),
        );
    }

    function el_stripe_redirect_url(){
        $thanks_page_id     = EL()->options->general->get('thanks_page_id','');
        $stripe_send_ticket = EL()->options->checkout->get('stripe_send_ticket','no');

        if ( is_page( $thanks_page_id ) ) {

            $payment_intent_client_secret = isset( $_GET['payment_intent_client_secret'] ) ? sanitize_text_field( $_GET['payment_intent_client_secret'] ) : '';
            $redirect_status = isset( $_GET['redirect_status'] ) ? sanitize_text_field( $_GET['redirect_status'] ) : '';

            if ( $payment_intent_client_secret && $redirect_status === 'succeeded' ) {
                $booking_ids = el_get_booking_id_by_client_secret( $payment_intent_client_secret );

                if ( $booking_ids ) {

                    foreach ( $booking_ids as $booking_id ) {

                        if( $stripe_send_ticket === 'yes' ){
                            EL_Booking::instance()->booking_success( $booking_id, $this->_title );    
                        } else {
                            EL_Booking::instance()->booking_hold( $booking_id );
                        }
                    }
                }
            }
        }
    }
}