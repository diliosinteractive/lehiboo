<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'El_Admin_Assets', false ) ) {
	return new El_Admin_Assets();
}

/**
 * Admin Assets classes
 */
class El_Admin_Assets{

	

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

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 2 );
		
	}

	/**
	 * Add menu items.
	 */
	public function enqueue_scripts() {
		$lat = EL()->options->general->get('event_lat', 40.6976312 );
		$lng = EL()->options->general->get('event_lng', -74.1444847 );
		if ( empty( $lat ) ) {
			$lat = 40.6976312;
		}
		if ( empty( $lng ) ) {
			$lng = -74.1444847;
		}

		wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?key='.EL()->options->general->get('event_google_key_map','').'&libraries=places,marker&callback=Function.prototype&v=weekly&v=beta', array('jquery'), false, true );
		wp_localize_script( 'google', 'el_google_obj', array(
			'lat' => $lat,
			'lng' => $lng,
		) );
		
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



		/* Jquery UI */
		wp_enqueue_style( 'jquery-ui', EL_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_script('popper', EL_PLUGIN_URI.'assets/libs/tippy/popper.min.js', array('jquery'), false, true );
		wp_enqueue_script('tippy', EL_PLUGIN_URI.'assets/libs/tippy/tippy-bundle.umd.min.js', array('jquery'), false, true );

		/* Datepicker */
		wp_enqueue_script( 'jquery-ui-datepicker' );
		if ( $cal_lang = el_calendar_language() ) {
			wp_enqueue_script('datepicker-lang', EL_PLUGIN_URI.'assets/libs/datepicker-lang/datepicker-'.$cal_lang.'.js', array('jquery'), false, true);
		}
		
		/* Select2 */
		wp_enqueue_script( 'select2', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js' , array( 'jquery' ), null, true );
		wp_enqueue_style( 'select2', EL_PLUGIN_URI. 'assets/libs/select2/select2.min.css', array(), null );

		/* Jquery Timepicker */
		wp_enqueue_script('jquery-timepicker', EL_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.min.js', array('jquery'), false, true);
		wp_enqueue_style('jquery-timepicker', EL_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.min.css' );


		/* Elegant Font */
		wp_enqueue_style('elegant-font', EL_PLUGIN_URI.'assets/libs/elegant_font/ele_style.css', array(), null);

		wp_enqueue_style('v4-shims', EL_PLUGIN_URI.'/assets/libs/fontawesome/css/v4-shims.min.css', array(), null);
		wp_enqueue_style('fontawesome', EL_PLUGIN_URI.'assets/libs/fontawesome/css/all.min.css', array(), null);


		/* Validate */
		wp_enqueue_script('validate', EL_PLUGIN_URI.'assets/libs/jquery.validate.min.js', array('jquery'), false, true);
		
		wp_enqueue_script('jquery-block-ui', EL_PLUGIN_URI.'assets/libs/jquery.blockUI.js', array('jquery'), false, true );
		
		/* Chart */
		wp_enqueue_script( 'el_flot', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.js', array('jquery'), null, true );
		wp_enqueue_script( 'el_flot_pie', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.pie.js', array('jquery'), null, true );
		wp_enqueue_script( 'el_flot_resize', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.resize.js', array('jquery'), null, true );
		wp_enqueue_script( 'el_flot_stack', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.stack.js', array('jquery'), null, true );
		wp_enqueue_script( 'el_flot_time', EL_PLUGIN_URI.'assets/libs/flot/jquery.flot.time.js', array('jquery'), null, true );

		wp_enqueue_script('el_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', array('jquery'), false, true);
		wp_localize_script( 'el_admin', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php') ) );
		wp_localize_script( 'el_admin', 'el_admin_object', array(
			'media_title' 	=> esc_html__( 'Select media', 'eventlist' ),
			'media_button' 	=> esc_html__( 'Select', 'eventlist' ),
			'prefix' 		=> OVA_METABOX_EVENT
		) );
		wp_localize_script( 'el_admin', 'el_custom_tax_slug', el_get_custom_taxonomy_slug_arr() );

		wp_localize_script( 'el_admin', 'el_btn_obj', array(
			'custom_checkout_field' => esc_html__( 'Custom Checkout Fields', 'eventlist' ),
			'reschedule_ticket' => esc_html__( 'Reschedule Tickets', 'eventlist' ),
			'all_membership' => esc_html__( 'All Membership', 'eventlist' ),
			'payout_method' => esc_html__( 'Payout Method', 'eventlist' ),
		) );

		wp_enqueue_style('el_admin', EL_PLUGIN_URI.'assets/css/admin/admin.css' );
	}

	
}

El_Admin_Assets::instance();