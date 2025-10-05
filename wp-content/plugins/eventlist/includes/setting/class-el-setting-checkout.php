<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Checkout extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'checkout';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 12;

	public $_tab = true;


	public function __construct()
	{
		$this->_title = __('Checkout', 'eventlist');

		add_filter( 'el_admin_setting_fields', array( $this, 'el_generate_fields_checkout_genral' ), 10, 2 );

		parent::__construct();
	}

	public function el_generate_fields_checkout_genral( $groups, $id ) {

        if( $id == 'checkout' ){

	        $groups[$id . '_general'] = apply_filters( 'el_admin_setting_fields_checkout_general', $this->el_admin_setting_fields_checkout_general(), $this->id );


	      }

	        return $groups;
    }

     public function el_admin_setting_fields_checkout_general(){
   		return array(
        'title' => __('General', 'eventlist'),
        
         array(
         	'fields' => array(
         		
         		array(
                  'type' => 'select',
                  'label' => esc_html__('Users have to login to checkout of events.', 'eventlist'),
                  'desc'	=> esc_html__( 'Require login before checkout', 'eventlist' ),
                  'name' => 'el_login_booking',
                  'options' => array(
                    'no' => esc_html__('No', 'eventlist'),
                     'yes' => esc_html__('Yes', 'eventlist'),
                  ),
                  'default' => 'no',
               ),

         		array(
					'type' => 'checkbox',
					'label' => esc_html__( 'Allow customers to create an account during checkout', 'eventlist' ),
					 'desc' => esc_html__('Display checkbox create account in checkout form', 'eventlist'),
					'name' => 'checkout_create_account',
					'default' => '',
				),

				array(
					'type' => 'checkbox',
					'label' => esc_html__( 'Show terms and condition', 'eventlist' ),
					'name' => 'show_terms_condition',
					'desc' => esc_html__('Display checkbox in checkout form', 'eventlist'),
					'default' => '',
				),

				array(
                  'type' => 'select_page',
                  'label' => esc_html__('Terms and Condition page', 'eventlist'),
                  'desc' => esc_html__('The customer will click link to see this content page', 'eventlist'),
                  'name' => 'terms_condition_page',
               ),
				array(
						'type' 	=> 'select',
						'label'	=> __( 'Holding Ticket', 'eventlist' ),
						'desc' 	=> __( 'Allows to hold the ticket until the payment is completed after a period of time', 'eventlist' ),
						'atts' 	=> array(
							'id' 	=> 'checkout_holding_ticket',
							'class' => 'checkout_holding_ticket'
						),
						'name' => 'checkout_holding_ticket',
						'options' => array(
							'no' 	=> __( 'No', 'eventlist' ),
							'yes' 	=> __( 'Yes', 'eventlist' )
						),
						'default' => 'no'
					),

					array(
						'name' 		=> 'check_ticket_hold_per_seconds',
						'type' 		=> 'input',
						'label' 	=> __('Check every x seconds', 'eventlist'),
						'desc' 		=> __('Run Cron to check ticket hold after x seconds', 'eventlist'),
						'default' 	=> '600',
						'atts' 		=> array(
						 	'type' => 'number',
						),
               		),

               		array(
						'name' 		=> 'max_time_complete_checkout',
						'type' 		=> 'input',
						'label' 	=> __('Maximum time to complete payment', 'eventlist'),
						'desc' 		=> __('Booking will be deleted if the payment completion time is over x seconds', 'eventlist'),
						'default' 	=> '600',
						'atts' 		=> array(
						 	'type' => 'number',
						),
               		),


			)
         )
     	);
   }
	
	 
}

$GLOBALS['checkout_settings'] = new EL_Setting_Checkout();