<?php

if ( !defined( 'ABSPATH' ) ) {
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

class EL_Payment_Paypal extends EL_Abstract_Payment{
	public $id = 'paypal';
    protected $client;
    protected $redirect_url;

	public function __construct(){
		parent::__construct();
		$this->_title = esc_html__( 'Paypal', 'eventlist' );
        $this->redirect_url = get_thanks_page();
        $client_id = EL()->options->checkout->get('paypal_public_key','');
        $client_secret = EL()->options->checkout->get('paypal_secret_key','');
        $paypal_mode = EL()->options->checkout->get('paypal_mode','live');
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
            'el_payment_paypal_create_order',
            'el_payment_paypal_capture_order',
        );
        foreach ( $hooks as $hook ) {
            add_action( 'wp_ajax_'.$hook, array( $this, $hook ) );
            add_action( 'wp_ajax_nopriv_'.$hook, array( $this, $hook ) );
        }
	}

	function fields(){
    	return array(
            'title' => esc_html__('Paypal','eventlist'), // tab title
            'fields' => array(
                'fields' => array(
                    array(
                        'type' => 'select',
                        'label' => __( 'Active', 'eventlist' ),
                        'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
                        'atts' => array(
                            'id' => 'paypal_active',
                            'class' => 'paypal_active'
                        ),
                        'default' => 'no',
                        'name' => 'paypal_active',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                    array(
                     'type' => 'multiradio',
                     'label' => __( 'Choose a mode', 'eventlist' ),
                     'name' => 'paypal_mode',
                     'default' => 'live',
                     'options' => array(
                        'live' => __( 'Live', 'eventlist' ),
                        'test' => __( 'Test', 'eventlist' ),
                     ),
                    ),
                    array(
                        'type'  => 'input',
                        'label' => __( 'Public Key', 'eventlist' ),
                        'desc'  => esc_html__( 'Find here: https://developer.paypal.com/developer/applications', 'eventlist' ),
                        'name'  => 'paypal_public_key'
                    ),
                    array(
                        'type'  => 'input',
                        'label' => __( 'Secret Key', 'eventlist' ),
                        'desc'  => esc_html__( 'Find here: https://developer.paypal.com/developer/applications', 'eventlist' ),
                        'name'  => 'paypal_secret_key'
                    ),
                    array(
                        'type' => 'select',
                        'label' => __( 'Send Tickets after registering successfully', 'eventlist' ),
                        'atts' => array(
                            'id' => 'paypal_send_ticket',
                            'class' => 'paypal_send_ticket'
                        ),
                        'name' => 'paypal_send_ticket',
                        'options' => array(
                            'no' => __( 'No', 'eventlist' ),
                            'yes' => __( 'Yes', 'eventlist' )
                        )
                    ),
                   
                ),
            )
        );
		
    }

    function render_form(){
    
        ?>
        <span><?php esc_html_e( 'When you select this payment method, after submitting the form, please enter the correct payment information to complete the booking process.', 'eventlist' ); ?></span>
        <div class="modal fade" id="payment_paypal_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e( 'Please enter payment information.','eventlist' ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="paypal_button_wrapper" data-url="<?php echo esc_attr( $this->redirect_url ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_payment_paypal' ) ); ?>">
                            <div id="paypal-button-container"></div>
                            <div id="result-message"></div>
                        </div>
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
            'payment_method'    => 'paypal',
            'url'               => apply_filters( 'el_paypal_booking_event_url_thankyou', get_thanks_page(), 'success', $booking_id )
        );
    }

    public function el_payment_paypal_create_order(){

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_payment_paypal' ) ) {
            echo json_encode( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
            wp_die();
        }

        $booking_id = isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';

        if ( ! $booking_id ) {
            echo json_encode( ['el_error' => esc_html__( 'An error occurred while booking tickets.', 'eventlist' ) ] );
            wp_die();
        }

        $booking_title = get_the_title( $booking_id );
        $amount = get_post_meta( $booking_id, OVA_METABOX_EVENT.'total_after_tax', true );
        $zero_decimal = array("HUF","JPY","TWD");
        $currency = EL()->options->general->get('currency','USD');
        if ( in_array( $currency, $zero_decimal ) ) {
            $amount = ceil( $amount );
        }
        // Create a purchase unit with the total amount
        $purchase_unit = new PurchaseUnit(AmountBreakdown::of($amount, $currency));
        // Create & add item to purchase unit
        $purchase_unit->addItem(Item::create( $booking_title, $amount, $currency, 1));

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
                update_post_meta( $booking_id, OVA_METABOX_EVENT.'transaction_id', $order_id );
            }
            // Parse result
            wp_send_json( $order_data );
            wp_die();
        } catch (Exception $e){
            wp_send_json( ['el_error' => $e->getMessage() ] );
            wp_die();
        }
    }

    public function el_payment_paypal_capture_order(){

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_payment_paypal' ) ) {
            echo json_encode( ['el_error' => esc_html__( 'Nonce verification failed.', 'eventlist' ) ] );
            wp_die();
        }
        $order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        if ( ! $order_id ) {
            echo json_encode( ['el_error' => esc_html__( 'An error occurred while booking tickets.', 'eventlist' ) ] );
            wp_die();
        }
        // Create an order capture http request
        $request = new OrderCaptureRequest($order_id);

        // Send request to PayPal
        $response = $this->client->send($request);
        $paypal_send_ticket = EL()->options->checkout->get('paypal_send_ticket','no');
        $order_data = json_decode((string) $response->getBody());
        $status     = $order_data->status;

        $result = [
            'data' => $order_data
        ];

        if ( $status === 'COMPLETED' ) {
            $booking_ids = el_get_booking_id_by_paypal_id( $order_id );
            if ( $booking_ids ) {
                foreach ( $booking_ids as $booking_id ){
 
                    $result['thank_url'] = apply_filters( 'el_redirect_url_paypal', get_thanks_page(), $booking_id );
                    
                    if( $paypal_send_ticket === 'yes' ){
                        EL_Booking::instance()->booking_success( $booking_id, $this->_title );   
                    } else {
                        EL_Booking::instance()->booking_hold( $booking_id );
                    }
                }
            }
        }
        // Parse result JSON string
        wp_send_json( $result );
    }
}