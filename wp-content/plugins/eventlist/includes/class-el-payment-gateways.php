<?php
defined( 'ABSPATH' ) || exit;

class EL_Payment_Gateways{

	public $payment_gateways = array();

	protected static $_instance = null;

	protected $check_payment = array();

	public function __construct(){

		if ( version_compare(PHP_VERSION, '8.1.0', '<') ) {
			$this->check_payment = ['paypal'];
		}

		if ( ! empty( $this->check_payment ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice__error' ) );
		}

		add_action( 'init', array( $this, 'el_include' ) );

		add_action( 'init', array( $this, 'el_payment_gateways_avaiable' ) );
		
	}

	public function admin_notice__error(){
		$payment_err = $this->check_payment;

		foreach ( $payment_err as $payment ) {
			switch ( $payment ) {
				case 'paypal':
				?>
				<div class="notice notice-error">
			        <p><?php esc_html_e( 'Please upgrade PHP version to 8.1.0 or higher to be able to use Paypal payment method.', 'eventlist' ); ?></p>
			    </div>
				<?php
					break;
				
				default:
					break;
			}
		}
	}

	public function el_include(){

		$folders = array( 'free', 'offline', 'woo', 'stripe' );
		if ( ! in_array( 'paypal', $this->check_payment ) ) {
			$folders[] = 'paypal';
		}

		foreach ( $folders as $key => $folder ) {
			$real_folder = EL_PLUGIN_INC .'gateways'.'/'. $folder;
			foreach ( (array) glob( $real_folder . '/class-el-payment-' . $folder . '.php' ) as $key => $file ) {
				require_once $file ;
			}
		}
	}

	public function el_payment_gateways_avaiable(){

		if( class_exists('WooCommerce') ){
			$default_payments = array(
				'EL_Payment_Free',
				'EL_Payment_Offline',
				'EL_Payment_Woo',
				'EL_Payment_Stripe',
			);
		}else{
			$default_payments = array(
				'EL_Payment_Free',
				'EL_Payment_Offline',
				'EL_Payment_Stripe',
			);
		}

		if ( ! in_array( 'paypal', $this->check_payment ) ) {
			$default_payments[] = 'EL_Payment_Paypal';
		}
		
		$el_payment_gateways_avaiable = apply_filters( 'el_payment_gateways_avaiable', $default_payments );

		foreach ($el_payment_gateways_avaiable as $k => $class) {
			$payment_gate = class_exists( $class ) ?  new $class : null;
			if( $payment_gate ){
				$this->payment_gateways[ $payment_gate->id ] = $payment_gate;
			}
		}
		return $this->payment_gateways;

	}

	public function el_payment_gateways_active(){
		$payment_gateways_active = array();
		if( $this->payment_gateways ){
			foreach ($this->payment_gateways as $k => $obj) {
				if( $obj->_is_active ){
					$payment_gateways_active[$k] = $obj;
				}
			}
		}
		return apply_filters( 'payment_gateways_active', $payment_gateways_active );

	}

	static function instance() {

		if ( ! empty( self::$_instance ) ) {
			return self::$_instance;
		}

		return self::$_instance = new self();
	}

}