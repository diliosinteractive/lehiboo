<?php
/**
 * Setup menus in WP admin.
 *
 * @package EventList\Admin
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'El_Admin_Menus', false ) ) {
	return new El_Admin_Menus();
}

class El_Admin_Menus{

	/**
	 * instance
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * menu
	 * @var array
	 */
	public $_menus = array();



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

		// Add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );


		add_action( 'admin_menu', array( $this, 'el_add_menu_page' ) );
	}


	public function el_add_menu_page(){

		global $menu, $submenu;

		$manage_profit = EL()->options->tax_fee->get('manage_profit');


		add_menu_page( esc_html__( 'Events', 'eventlist' ), esc_html__( 'Events', 'eventlist' ), 'manage_options', 'eventlist', array( $this, 'get_home' ), 'dashicons-calendar-alt', 5 );


		add_submenu_page( 'eventlist', esc_html__( 'Events', 'eventlist' ), esc_html__( 'Events', 'eventlist' ), 'manage_options', 'edit.php?post_type=event' );

		add_submenu_page( 'eventlist', esc_html__( 'Categories', 'eventlist' ), esc_html__( 'Categories', 'eventlist' ), 'manage_options', 'edit-tags.php?taxonomy=event_cat&post_type=event' );

		add_submenu_page( 'eventlist', esc_html__( 'Tags', 'eventlist' ), esc_html__( 'Tags', 'eventlist' ), 'manage_options', 'edit-tags.php?taxonomy=event_tag&post_type=event' );

		add_submenu_page( 'eventlist', esc_html__( 'Locations', 'eventlist' ), esc_html__( 'Locations', 'eventlist' ), 'manage_options', 'edit-tags.php?taxonomy=event_loc&post_type=event' );

		add_submenu_page( 'eventlist', esc_html__( 'Venues', 'eventlist' ), esc_html__( 'Venues', 'eventlist' ), 'manage_options', 'edit.php?post_type=venue' );


		add_submenu_page( 'eventlist', esc_html__( 'Custom Taxonomy', 'eventlist' ), esc_html__( 'Custom Taxonomy', 'eventlist' ), 'manage_options', 'custom-taxonomy', array( $this, 'get_custom_taxonomy_page' ) );

		add_submenu_page( 'eventlist', esc_html__( 'Manage Bookings', 'eventlist' ), esc_html__( 'Manage Bookings', 'eventlist' ), 'manage_options', 'edit.php?post_type=el_bookings' );


		if ( EL()->options->role->get('allow_to_selling_ticket', 'yes') == 'yes' ) {
			add_submenu_page( 'eventlist', esc_html__( 'Manage Tickets', 'eventlist' ), esc_html__( 'Manage Tickets', 'eventlist' ), 'manage_options', 'edit.php?post_type=el_tickets' );
		}	


		add_submenu_page( 'eventlist', esc_html__( 'Reports', 'eventlist' ), esc_html__( 'Reports', 'eventlist' ), 'manage_options', 'ova_el_display_report_sales', array( $this, 'register_report_sales_page' ) );

		add_submenu_page( 'eventlist', esc_html__( 'Report Users', 'eventlist' ), esc_html__( 'Report Users', 'eventlist' ), 'manage_options', 'ova_el_display_report_user', array( $this, 'register_report_user_page' ) );

		if ( $manage_profit == 'profit_1' ) {
			add_submenu_page( 'eventlist', esc_html__( 'Manage Payouts', 'eventlist' ), esc_html__( 'Manage Payouts', 'eventlist' ), 'manage_options', 'ova_el_display_profit_event', array( $this, 'register_display_profit_event' ) );
		} else {
			add_submenu_page( 'eventlist', esc_html__( 'Manage Payouts', 'eventlist' ), esc_html__( 'Manage Payouts', 'eventlist' ), 'manage_options', 'edit.php?post_type=payout' );
		}

		add_submenu_page( 'eventlist', esc_html__( 'All Payout Method', 'eventlist' ), esc_html__( 'All Payout Method', 'eventlist' ), 'manage_options', 'edit.php?post_type=payout_method' );

		if ( EL()->options->package->get( 'enable_package', 'no' ) == 'yes' ) {

			add_submenu_page( 'eventlist', esc_html__( 'Manage Packages', 'eventlist' ), esc_html__( 'Manage Packages', 'eventlist' ), 'manage_options', 'edit.php?post_type=package' );

			add_submenu_page( 'eventlist', esc_html__( 'Manage Membership', 'eventlist' ), esc_html__( 'Manage Membership', 'eventlist' ), 'manage_options', 'edit.php?post_type=manage_membership' );
		}
		

		
		

		add_submenu_page( 'eventlist', esc_html__( 'Replace Date', 'eventlist' ), esc_html__( 'Replace Date', 'eventlist' ), 'manage_options', 'el_replace_ticket_date', array( $this, 'el_replace_ticket_date' ) );

		add_submenu_page( 'eventlist', esc_html__( 'Custom Checkout Field', 'eventlist' ), esc_html__( 'Custom Checkout Field', 'eventlist' ), 'manage_options', 'ova_el_custom_checkout_field', array( $this, 'el_register_custom_checkout_field' ) );





		do_action( 'el_add_submenu_page' );


		add_submenu_page( 'eventlist', esc_html__( 'Settings', 'eventlist' ), esc_html__( 'Settings', 'eventlist' ), 'manage_options', 'ova_el_setting', array( $this, 'register_options_page' ) );


		// Custom
		unset( $submenu['eventlist'][0] );

	}

	public function get_custom_taxonomy_page(){
		EL()->_include( EL_PLUGIN_INC . 'admin/views/pages/custom-taxonomy.php' );
	}

	public function get_home(){

	}

	public function el_replace_ticket_date() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/el_replace_ticket_date.php' );
	}

	public function register_options_page() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/settings.php' );
	}

	public function el_register_custom_checkout_field() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/el_custom_checkout_field.php' );
	}

	public function register_display_profit_event() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/display_profit.php' );
	}

	public function register_report_sales_page() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/report_sales.php' );
	}

	public function register_report_user_page() {
		EL()->_include( EL_PLUGIN_INC . 'admin/views/settings/report_user.php' );
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		

		
		/**
         * menus
         * @var
         */
		$menus = apply_filters( 'el_admin_menus', $this->_menus );
		foreach ( $menus as $menu ) {
			call_user_func_array( 'add_submenu_page', $menu );
		}


	}

	/**
     * add menu item
     * @param $params
     */
	public function add_menu( $params ) {
		$this->_menus[] = $params;
	}
	
}

El_Admin_Menus::instance();