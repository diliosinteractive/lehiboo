<?php

/**
 * EventList Setup
 * @package EventList
 * @since 1.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Main Class EventList
 */
final class EventList{

	/**
	 * EventList Version
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * instance
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * options
	 * @var options
	 */
	public $options = null;

	/**
	 * object
	 * @var null
	 */
	public $payment_gateways = null;
	
	/**
	 * Checkout
	 */
	public $checkout = null;

	/**
	 * Session
	 */
	public $msg_session = null;
	
	/**
	 * Cart Session
	 */
	public $cart_session = null;	
	

	/**
	 * EventList Constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'includes' ), -1 );
		
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
	}


	public function includes(){

		/**
		 * Class autoloader
		 */
		
		require_once EL_PLUGIN_INC . 'class-el-setting.php';
		/**
		 * Auto Load files in  abstract,shortcode,setting
		 * Note: Name class like class-el-folder-*.php
		 */
		
		$folders = array( 'abstract', 'setting' );
		$this->autoload( $folders );

		if ( $this->is_request( 'frontend' ) ) {
			$folders = array( 'shortcode' );
			$this->autoload( $folders );
		}


		$this->options = EL_Setting::instance();


		/**
		 * Roles
		 */
		require_once EL_PLUGIN_INC . 'class-el-roles.php';
		
		/**
		 * User
		 */
		require_once EL_PLUGIN_INC . 'user/class-el-user.php';		
		require_once EL_PLUGIN_INC . 'user/el-user-functions.php';		
		
		/**
		 * Admin classes
		 */
		if ( $this->is_request( 'admin' ) ) {
			require_once EL_PLUGIN_INC . 'admin/class-el-admin.php';
		}


		// Ajax FrontEnd
		require_once EL_PLUGIN_INC . 'class-el-ajax.php';
		
		
		// Core Functions
		require_once EL_PLUGIN_INC . 'el-core-functions.php';

		// Post Type
		require_once EL_PLUGIN_INC . 'class-el-post-types.php';


		// Template Loader
		require_once EL_PLUGIN_INC . 'class-el-template-loader.php';
		require_once EL_PLUGIN_INC . 'el-template-functions.php';
		require_once EL_PLUGIN_INC . 'el-template-hooks.php';

		// Assets
		require_once EL_PLUGIN_INC . 'class-el-assets.php';

		// Session
		require_once EL_PLUGIN_INC . 'class-el-sessions.php';

		// // Cart
		require_once EL_PLUGIN_INC . 'cart/class-el-cart.php';
		require_once EL_PLUGIN_INC . 'cart/el-cart-functions.php';

		// // Booking
		require_once EL_PLUGIN_INC . 'booking/class-el-booking.php';
		require_once EL_PLUGIN_INC . 'booking/class-el-column-manager-booking.php';


		// // Payment
		require_once EL_PLUGIN_INC . 'payout/class-el-payout.php';
		require_once EL_PLUGIN_INC . 'payout/class-el-column-manager-payout.php';
		require_once EL_PLUGIN_INC . 'payout/class-el-payout-mail.php';

		// // Checkout
		require_once EL_PLUGIN_INC . 'class-el-checkout.php';

		// Event
		require_once EL_PLUGIN_INC . 'event/class-el-event.php';
		require_once EL_PLUGIN_INC . 'event/el-event-functions.php';

		// Vendor
		require_once EL_PLUGIN_INC . 'vendor/class-el-vendor.php';
		require_once EL_PLUGIN_INC . 'vendor/el-vendor-functions.php';

		// Payment Gateways
		require_once EL_PLUGIN_INC . 'class-el-payment-gateways.php';
		
		// // Ticket
		require_once EL_PLUGIN_INC . 'ticket/class-el-zip-archive.php';
		require_once EL_PLUGIN_INC . 'ticket/class-el-ticket.php';
		require_once EL_PLUGIN_INC . 'ticket/class-el-column-ticket-manager.php';

		// Cookie
		require_once EL_PLUGIN_INC . 'class-el-cookie.php';
		
		// Elementor
		if ( did_action( 'elementor/loaded' ) ) {
			require_once EL_PLUGIN_INC. 'class-el-elementor.php';
		}

		// API
		require_once EL_PLUGIN_INC . 'api/vendor/autoload.php';
		require_once EL_PLUGIN_INC . 'api/class-el-api.php';

		
		// Package
		require_once EL_PLUGIN_INC . 'package/el-package-functions.php';
		
		require_once EL_PLUGIN_INC . 'package/class-el-package.php';
		if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  ){
			require_once EL_PLUGIN_INC . 'package/class-el-booking-package.php';
		}
		require_once EL_PLUGIN_INC . 'package/class-el-column-manage-membership.php';

		if ( ! version_compare(PHP_VERSION, '8.1.0', '<') ) {
			require_once EL_PLUGIN_INC.'gateways/paypal/class-el-package-payment-paypal.php';
		}
		
		require_once EL_PLUGIN_INC.'gateways/woo/class-el-package-payment-woo.php';
		require_once EL_PLUGIN_INC.'gateways/stripe/class-el-package-payment-stripe.php';
		
		
		// Widget
		require_once EL_PLUGIN_INC . 'class-el-register-widget.php';

		// // Mail
		require_once EL_PLUGIN_INC . 'email/class-el-mail.php';
		
		// // Cron Job
		require_once EL_PLUGIN_INC . 'cron/class-el-cron.php';

		// Hooks
		require_once EL_PLUGIN_INC . 'class-el-hooks.php';	

		require_once EL_PLUGIN_INC . 'class-el-legacy.php';


		$this->payment_gateways = EL_Payment_Gateways::instance();

		$this->checkout = EL_Checkout::instance();
		
		$this->msg_session = EL_Sessions::instance('msg_session');
		$this->cart_session = EL_Sessions::instance('cart_session');


		do_action( 'eventlist_init' );
	}

	public function on_plugins_loaded() {
		/**
		 * Action to signal that WooCommerce has finished loading.
		 *
		 * @since 3.6.0
		 */
		do_action( 'eventlist_loaded' );
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * Legacy REST requests should still run some extra code for backwards compatibility.
	 *
	 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return apply_filters( 'eventlist_is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * Load text domain
	 */
	public function load_plugin_textdomain(){

		$prefix = basename( EL_PLUGIN_PATH );
		$locale = get_locale();
		$dir    = untrailingslashit( EL_PLUGIN_PATH ).'/languages';
		$mofile = false;

		$globalFile = WP_LANG_DIR . '/plugins/' . $prefix . '-' . $locale . '.mo';
		$pluginFile = $dir . '/' . $prefix . '-' . $locale . '.mo';

		if ( file_exists( $globalFile ) ) {
			$mofile = $globalFile;
		} else if ( file_exists( $pluginFile ) ) {
			$mofile = $pluginFile;
		}

		if ( $mofile ) {
			// In themes/plugins/mu-plugins directory
			load_textdomain( 'eventlist', $mofile );
		}
		
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( trim( $type ) ) {
			case 'admin':
			return is_admin();
			case 'ajax':
			return defined( 'DOING_AJAX' );
			case 'cron':
			return defined( 'DOING_CRON' );
			case 'frontend':
			return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}

	/**
	 * load options object class
	 * @return object class
	 */
	public function options() {
		return EL_Setting::instance();
	}

	public function _include( $file ) {
		if ( ! $file ) {
			return;
		}

		if ( is_array( $file ) ) {
			foreach ( $file as $key => $f ) {
				if ( file_exists( EL_PLUGIN_PATH . $f ) ) {
					require_once EL_PLUGIN_PATH . $f;
				}
			}
		} else {
			if ( file_exists( EL_PLUGIN_PATH . $file ) ) {
				require_once EL_PLUGIN_PATH . $file;
			} elseif ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	public function autoload( $folders ){
		foreach ( $folders as $key => $folder ) {
			$real_folder = EL_PLUGIN_INC . $folder;
			foreach ( (array) glob( $real_folder . '/class-el-' . $folder . '-*.php' ) as $key => $file ) {
				$this->_include( $file );
			}
		}
	}


	/**
	 * Main EventList Instance 
	 * Ensures only one instance of EventList is loaded or can be loaded.
	 * @return EventList - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'eventlist' ), '1.0' );
	}

}