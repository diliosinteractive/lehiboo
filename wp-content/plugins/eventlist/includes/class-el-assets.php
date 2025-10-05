<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EL_Assets', false ) ) {
	return EL_Assets::instance();
}

/**
 * Admin Assets classes
 */
class EL_Assets{

	protected static $_instance = null;

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
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'ova_admin_enqueue_scripts' ) );
	}

	/**
	 * Add menu items.
	 */
	public function enqueue_scripts() {

		if( ( isset( $_GET['vendor'] ) && $_GET['vendor'] != '' ) || ( is_user_logged_in() ) ){

			wp_enqueue_media();
			
			wp_enqueue_script( 'tiny_mce' );
			
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
			
			/* color picker */
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, true);
			

			$colorpicker = array(
				'clear' 		=> __( 'Clear', 'eventlist' ),
				'defaultString' => __( 'Default', 'eventlist' ),
				'pick' 			=> __( 'Select Color', 'eventlist' ),
				'current' 		=> __( 'Current Color', 'eventlist' ),
			);
			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker );

			wp_enqueue_style( 'pretty-checkbox', EL_PLUGIN_URI.'assets/libs/pretty-checkbox/pretty-checkbox.min.css', 'all' );

			/* Validate */
			wp_enqueue_script('el_validate', EL_PLUGIN_URI.'assets/libs/jquery.validate.min.js', array('jquery'),false,true);


			/* Chart */
			wp_enqueue_script( 'el_flot', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.js', array('jquery'), null, true );
			wp_enqueue_script( 'el_flot_pie', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.pie.js', array('jquery'), null, true );
			wp_enqueue_script( 'el_flot_resize', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.resize.js', array('jquery'), null, true );
			wp_enqueue_script( 'el_flot_stack', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.stack.js', array('jquery'), null, true );
			wp_enqueue_script( 'el_flot_time', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.time.js', array('jquery'), null, true );

			/* Add variable in wallet */
			if( isset( $_GET['vendor'] ) && $_GET['vendor'] == 'wallet' ){
				wp_add_inline_script( 'el_validate', 'withdraw_amount_null = "'.esc_html__('Insert Amount need to withdraw', 'eventlist').'"; withdraw_amount_num="'.esc_html__('Amount must be a number and more than 0', 'eventlist').'"; withdraw_amount_validate = "'.esc_html__( 'Amount must be less than', 'eventlist' ).'"; ', 'after' );
			}

		} else if ( is_user_logged_in() && current_user_can( 'subscriber' ) ){

			wp_enqueue_media();

		}

		/* Icon */
		wp_enqueue_style('v4-shims', EL_PLUGIN_URI.'/assets/libs/fontawesome/css/v4-shims.min.css', array(), null);
		wp_enqueue_style('fontawesome', EL_PLUGIN_URI.'assets/libs/fontawesome/css/all.min.css', array(), null);
		wp_enqueue_style( 'flaticon', EL_PLUGIN_URI.'assets/libs/flaticon/font/flaticon.css', array(), null );
		wp_enqueue_style('elegant-font', EL_PLUGIN_URI.'assets/libs/elegant_font/ele_style.css', array(), null);

		$date_format = EL()->options->general->get('cal_date_format', 'dd-mm-yy');
		/* Datepicker */
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_localize_script( 'jquery-ui-datepicker', 'el_datepicker_obj', array(
			'date_format' => $date_format,
		) );
		if ( $cal_lang = el_calendar_language() ) {
			wp_enqueue_script('datepicker-lang', EL_PLUGIN_URI.'assets/libs/datepicker-lang/datepicker-'.$cal_lang.'.js', array('jquery'), false, true);
		}
		// Stripe
        $stripe_public_key  = EL()->options->checkout->get('stripe_public_key','');
        $stripe_secret_key  = EL()->options->checkout->get('stripe_secret_key','');

        if ( $stripe_public_key && $stripe_secret_key ) {
            wp_enqueue_script('stripe', 'https://js.stripe.com/v3/', array('jquery'), null, false);
        }

        // Paypal
        $paypal_public_key  = EL()->options->checkout->get('paypal_public_key','');
        $paypal_secret_key  = EL()->options->checkout->get('paypal_secret_key','');
        $currency 			= EL()->options->general->get('currency','USD');
        if ( $paypal_public_key && $paypal_secret_key ) {
        	wp_enqueue_script('paypal', 'https://www.paypal.com/sdk/js?client-id='.$paypal_public_key.'&currency='.$currency, array('jquery'), null, false);
        }
        
		
		/* Jquery UI */
		wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
		wp_enqueue_style( 'jquery-ui', EL_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-slider' );

		wp_enqueue_script('jquery-block-ui', EL_PLUGIN_URI.'assets/libs/jquery.blockUI.js', array('jquery'), false, true );

		wp_enqueue_style( 'nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.css', 'all' );
		wp_enqueue_script('nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.js', array(), false, false);
		wp_enqueue_script('wnumb', EL_PLUGIN_URI.'assets/libs/nouislider/wNumb.min.js', array(), false, false);

		wp_enqueue_script('popper', EL_PLUGIN_URI.'assets/libs/tippy/popper.min.js', array('jquery'), false, true );
		wp_enqueue_script('tippy', EL_PLUGIN_URI.'assets/libs/tippy/tippy-bundle.umd.min.js', array('jquery'), false, true );

		wp_enqueue_script('jquery-zoom', EL_PLUGIN_URI.'assets/libs/jquery.zoom.min.js', array('jquery'), false, true );

		$event_settings 	= EL()->options->general;
		$currency_position 	= $event_settings->get('currency_position','left');
		$thousand_separator = $event_settings->get('thousand_separator',',');
		$decimals = $event_settings->get('number_decimals',2);
		wp_localize_script( 'wnumb', 'currency_object', array( 'currency' => _el_symbol_price(), 'thousand' => $thousand_separator, 'decimals' => $decimals, 'position' => $currency_position ) );

		/* Jquery Timepicker */
		wp_enqueue_script('jquery-timepicker', EL_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.min.js', array('jquery'), false, true);
		wp_enqueue_style('jquery-timepicker', EL_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.min.css' );

		/* Script Admin */
		if( isset( $_GET['vendor'] ) && $_GET['vendor'] != '' && is_user_logged_in() ){
			wp_enqueue_script('el_script_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', array('jquery'),false,true);
			wp_localize_script( 'el_script_admin', 'el_admin_object', array(
				'media_title' 	=> esc_html__( 'Select media', 'eventlist' ),
				'media_button' 	=> esc_html__( 'Select', 'eventlist' ),
				'prefix' 		=> OVA_METABOX_EVENT
			) );

			
		}

		/* Select2 */
        wp_enqueue_script( 'select2', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js' , array( 'jquery' ), null, true );
        wp_enqueue_style( 'select2', EL_PLUGIN_URI. 'assets/libs/select2/select2.min.css', array(), null );

		/* Fancybox */
		wp_enqueue_script( 'fancybox', EL_PLUGIN_URI.'assets/libs/fancybox/fancybox.umd.js' , array( 'jquery' ), null, true );
		wp_enqueue_style( 'fancybox', EL_PLUGIN_URI. 'assets/libs/fancybox/fancybox.css', array(), null );

		/* xregexp */
		wp_enqueue_script('xregexp', EL_PLUGIN_URI.'assets/libs/xregexp/xregexp-all.js', array(), false, false);

		/* Owl Carousel */
			wp_enqueue_style('owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.css', array(), null);

			wp_enqueue_style('owl-carousel-theme', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.theme.default.min.css', array(), null);

			wp_enqueue_script('owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true);
		

		/****** Single Event ******/
		if ( is_singular( 'event' ) || el_can_preview_event() ) {
			
			/*add slick in single event*/
			wp_enqueue_style('slick-style', EL_PLUGIN_URI.'assets/libs/slick/slick/slick.css', array(), null);
			wp_enqueue_style('slick-theme-style', EL_PLUGIN_URI.'assets/libs/slick/slick/slick-theme.css', array(), null);
			wp_enqueue_script('slick-script', EL_PLUGIN_URI.'assets/libs/slick/slick/slick.min.js', array('jquery'), false, true );

			 // Fullcalendar
		    wp_enqueue_script('el_fullcalendar', EL_PLUGIN_URI.'/assets/libs/fullcalendar/main.min.js', array('jquery'),null,true);
		    wp_enqueue_script('el_locale', EL_PLUGIN_URI.'/assets/libs/fullcalendar/locales-all.min.js', array('jquery'),null,true);
		    wp_enqueue_style('el_fullcalendar', EL_PLUGIN_URI.'/assets/libs/fullcalendar/main.min.css', array(), null);

		}


		/* Google Maps */
		$map_language = apply_filters( 'el_google_map_language', 'en' );

		$lat = EL()->options->general->get('event_lat', 40.6976312 );
		$lng = EL()->options->general->get('event_lng', -74.1444847 );
		if ( empty( $lat ) ) {
			$lat = 40.6976312;
		}
		if ( empty( $lng ) ) {
			$lng = -74.1444847;
		}
		wp_enqueue_script( 'google','https://maps.googleapis.com/maps/api/js?key='.EL()->options->general->get('event_google_key_map','').'&libraries=places,marker&callback=Function.prototype&language='.$map_language, array(), false, false );
		wp_localize_script( 'google', 'el_google_obj', array(
			'lat' => $lat,
			'lng' => $lng,
		) );

		wp_enqueue_script( 'place-autocomplete-element', EL_PLUGIN_URI . 'assets/js/frontend/place-autocomplete-element.js', array('google', 'jquery'), false, true );

		wp_enqueue_script( 'google-marker',EL_PLUGIN_URI.'assets/libs/markerclusterer.js', array('jquery' ), false, true);

		$event_type = EL()->options->event->get('archive_type', 'type1');
		
		wp_enqueue_script('el_frontend', EL_PLUGIN_URI.'assets/js/frontend/script.min.js', array('jquery', 'owl-carousel'),false,true );
		wp_localize_script( 'el_frontend', 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce( 'el_nonce' ) ) );

		wp_localize_script( 'el_frontend', 'event_object', array(
			'event_type' => $event_type,
		) );
		
		if ( did_action( 'elementor/loaded' ) ) {
			wp_enqueue_script( 'script-eventlist-elementor', EL_PLUGIN_URI. 'assets/js/frontend/script-elementor.js', [ 'jquery' ], false, true );
			wp_localize_script( 'script-eventlist-elementor', 'event_element_object', array( 'get_location' => apply_filters('el_get_location', true ) ) );
		}

		wp_enqueue_style('el_frontend', EL_PLUGIN_URI.'assets/css/frontend/style.css' );
	}

	public function ova_admin_enqueue_scripts(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'select2', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js' , array( 'jquery' ), null, true );
		wp_enqueue_style( 'select2', EL_PLUGIN_URI. 'assets/libs/select2/select2.min.css', array(), null );
		wp_enqueue_script( 'event-setting', EL_PLUGIN_URI. 'assets/js/admin/setting.js', array('jquery'), false, true );
	}
	
}

EL_Assets::instance();
