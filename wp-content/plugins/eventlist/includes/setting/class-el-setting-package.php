<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Package extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'package';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 11;

	public $_tab = true;


	public function __construct()
	{
		$this->_title = __('Package', 'eventlist');
		add_filter( 'el_admin_setting_fields', array( $this, 'el_generate_fields_package' ), 10, 2 );
		parent::__construct();
	}

	public function el_generate_fields_package( $groups, $id="package" ){
		if ( $id == 'package' ) {
			$groups[$id.'_general'] = apply_filters( 'el_setting_package_general', $this->el_setting_package_general(), $this->id );
			$groups[$id.'_woocommerce'] = apply_filters( 'el_admin_setting_fields_package_woocommerce', $this->el_admin_setting_fields_package_woocommerce(), $this->id );
			$groups[$id.'_stripe'] = apply_filters( 'el_admin_setting_fields_package_stripe', $this->el_admin_setting_fields_package_stripe(), $this->id );
			if ( ! version_compare(PHP_VERSION, '8.1.0', '<') ) {
				$groups[$id.'_paypal'] = apply_filters( 'el_admin_setting_fields_package_paypal', $this->el_admin_setting_fields_package_paypal(), $this->id );
			}
		}
		return $groups;
	}

   // render fields
	public function el_setting_package_general() {
		return array(
			'title' => __( 'General', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'type' => 'select',
						'label' => __( 'Enable Package', 'eventlist' ),
						'desc' => __( 'Use package for creating event', 'eventlist' ),
						'atts' => array(
							'id' => 'enable_package',
							'class' => 'enable_package'
						),
						'name' => 'enable_package',
						'options' => array(
							'yes' => __( 'Yes', 'eventlist' ),
							'no' => __( 'No', 'eventlist' )
						),
						'default' => 'no'
					),
					array(
						'type' => 'select_package',
						'label' => __( 'Default Package', 'eventlist' ),
						'desc' => __( 'Add for new user', 'eventlist' ),
						'atts' => array(
							'id' => 'package',
							'class' => 'package'
						),
						'name' => 'package',	
					),
					array(
						'type' 		=> 'checkbox',
						'label' 	=> __( 'Hide Package', 'eventlist' ),
						'desc' 		=> esc_html__( 'Hide package information in vendor dashboard', 'eventlist' ),
						'name' 		=> 'hide_package',
						'default' 	=> '',
					),
				)
			),
		);
	}

	public function el_admin_setting_fields_package_woocommerce(){
		return array(
			'title' => __( 'WooCommerce', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'type' => 'select_key',
						'label' => __( 'Active', 'eventlist' ),
						'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
						'atts' => array(
							'id' => 'woo_active',
							'class' => 'woo_active'
						),
						'name' => 'woo_active',
						'options' => array(
							'yes' => __( 'Yes', 'eventlist' ),
							'no' => __( 'No', 'eventlist' )
						),
						'default' => 'yes',
					),
					array(
						'type' => 'select_woo_page',
						'label' => __( 'Choose a hidden product in Woocommerce', 'eventlist' ),
						'desc' => __( 'This allow to booking a event via WooCommerce', 'eventlist' ),
						'name' => 'product_payment_package',
					),
					array(
						'type' => 'select',
						'label' => __( 'Allow active package when Order status: ', 'eventlist' ),
						'desc' => '',
						'name' => 'allow_active_package_by_order',
						'atts' => array(
							'id' => 'allow_active_package_by_order',
							'class' => 'allow_active_package_by_order',
							'multiple' => 'multiple'
						),
						'options' => array(
							'wc-completed' => __( 'Completed', 'eventlist' ),
							'wc-processing' => __( 'Processing', 'eventlist' ),
							'wc-on-hold' => __( 'Hold-on', 'eventlist' )
						),
						'default' => array( 'wc-completed', 'wc-processing' )
					),
				),
			),
		);
	}

	public function el_admin_setting_fields_package_stripe(){
		return array(
			'title' => __( 'Stripe', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'type' => 'select_key',
						'label' => __( 'Active', 'eventlist' ),
						'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
						'atts' => array(
							'id' => 'stripe_active',
							'class' => 'stripe_active'
						),
						'name' => 'stripe_active',
						'options' => array(
							'yes' => __( 'Yes', 'eventlist' ),
							'no' => __( 'No', 'eventlist' )
						),
						'default' => 'no',
					),
					array(
                        'type'  => 'empty',
                        'label' => __( 'Public Key', 'eventlist' ),
                        'desc'  => sprintf( wp_kses( __( '<a href="%s">Click here to enter key</a>', 'eventlist' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( add_query_arg( array( 
								'page' => 'ova_el_setting', 
								'post_type' => 'event', 
								'tab' => 'checkout',
								'group' => 'checkout_stripe',
							), admin_url( 'edit.php' ) ) ) ),
                        'name'  => 'stripe_public_key'
                    ),
                    array(
                        'type'  => 'empty',
                        'label' => __( 'Secret Key', 'eventlist' ),
                        'desc'  => sprintf( wp_kses( __( '<a href="%s">Click here to enter key</a>', 'eventlist' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( add_query_arg( array( 
								'page' => 'ova_el_setting', 
								'post_type' => 'event', 
								'tab' => 'checkout',
								'group' => 'checkout_stripe',
							), admin_url( 'edit.php' ) ) ) ),
                        'name'  => 'stripe_secret_key'
                    ),
				),
			),
		);
	}

	public function el_admin_setting_fields_package_paypal(){
		return array(
			'title' => __( 'Paypal', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'type' => 'select_key',
						'label' => __( 'Active', 'eventlist' ),
						'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
						'atts' => array(
							'id' => 'paypal_active',
							'class' => 'paypal_active'
						),
						'name' => 'paypal_active',
						'options' => array(
							'yes' => __( 'Yes', 'eventlist' ),
							'no' => __( 'No', 'eventlist' )
						),
						'default' => 'no',
					),
                    array(
                        'type'  => 'empty',
                        'label' => __( 'Public Key', 'eventlist' ),
                        'desc'  => sprintf( wp_kses( __( '<a href="%s">Click here to enter key</a>', 'eventlist' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( add_query_arg( array( 
								'page' => 'ova_el_setting', 
								'post_type' => 'event', 
								'tab' => 'checkout',
								'group' => 'checkout_paypal',
							), admin_url( 'edit.php' ) ) ) ),
                        'name'  => 'paypal_public_key'
                    ),
                    array(
                        'type'  => 'empty',
                        'label' => __( 'Secret Key', 'eventlist' ),
                        'desc'  => sprintf( wp_kses( __( '<a href="%s">Click here to enter key</a>', 'eventlist' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( add_query_arg( array( 
								'page' => 'ova_el_setting', 
								'post_type' => 'event', 
								'tab' => 'checkout',
								'group' => 'checkout_paypal',
							), admin_url( 'edit.php' ) ) ) ),
                        'name'  => 'paypal_secret_key'
                    ),
				),
			),
		);
	}

}

$GLOBALS['package_settings'] = new EL_Setting_Package();