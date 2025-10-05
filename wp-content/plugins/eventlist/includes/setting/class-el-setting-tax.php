<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Tax_Fee extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'tax_fee';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 14;


	public function __construct()
	{
		$this->_title = __('Tax & Profit', 'eventlist');
		parent::__construct();
	}

   // render fields
	public function load_field() {

		return
		array(
			array(
				'title' => __( 'Tax', 'eventlist' ),
				'desc' => __( 'Set up Tax for customers', 'eventlist' ),
				'fields' => array(
					array(
						'type' => 'select',
						'label' => __( 'Enable', 'eventlist' ),
						'desc' => __( 'Allow to calculate tax in order', 'eventlist' ),
						'atts' => array(
							'id' => 'enable_tax',
							'class' => 'enable_tax'
						),
						'name' => 'enable_tax',
						'options' => array(
							'yes' => __( 'Yes', 'eventlist' ),
							'no' => __( 'No', 'eventlist' )
						),
						'default' => 'yes'
					),
					array(
						'type' => 'input',
						'label' => __( 'Tax percentage(%)', 'eventlist' ),
						'desc' => __( 'Some packages may change tax in per event', 'eventlist' ),
						'atts' => array(
							'id'          => 'pecent_tax',
							'class'       => 'pecent_tax',
							'type'        => 'text',
							'placeholder' => '10',
						),
						'name' => 'pecent_tax',
						'default' => '10'
					),
					array(
						'type' => 'select',
						'label' => __( 'Vendor\'s Profit included tax', 'eventlist' ),
						'desc' => __( 'Yes: The Verdor will do tax payment procedures with the tax authority. No: Admin will do this. ', 'eventlist' ),
						'name' => 'profit_included_tax',
						'options' => array(
							'no' => __( 'No', 'eventlist' ),
							'yes' => __( 'Yes', 'eventlist' )
						),
						'default' => 'no'
					),
					
				)
			),

			array(
				'title' => __( 'Profit', 'eventlist' ),
				'desc' => __( 'List & send profit to vendor<br/>Check in Manage wallet', 'eventlist' ),
				'fields' => array(

					array(
						'type' => 'select',
						'label' => __( 'Manage Payout by', 'eventlist' ),
						'atts' => array(
							'id' => 'manage_profit',
							'class' => 'manage_profit'
						),
						'name' => 'manage_profit',
						'options' => array(
							'profit_1' => __( 'Closed event', 'eventlist' ),
							'profit_2' => __( 'Any time', 'eventlist' )
						),
						'default' => 'profit_1'
					),

					array(
						'type' => 'input',
						'label' => __( 'X Day', 'eventlist' ),
						'desc' => __( 'Allow to send profit to vendor about X days after the closed event', 'eventlist' ),
						'atts' => array(
							'id'          => 'x_day_profit',
							'class'       => 'x_day_profit',
							'type'        => 'number',
							'placeholder' => '5',
						),
						'name' => 'x_day_profit',
						'default' => '5'
					),
					
				)
			),
			
			array(
				'title' => __( 'System Fee', 'eventlist' ),
				'desc' => __( 'Customers have to pay each booking(exclusive of tax). This fee will added for the owner site (admin).', 'eventlist' ),
				'fields' => array(
					array(
						'type' 	=> 'select',
						'label' => __( 'Type', 'eventlist' ),
						'atts' 	=> array(
							'id' 	=> 'type_system_fee',
							'class' => 'type_system_fee'
						),
						'name' => 'type_system_fee',
						'options' => array(
							'percent' 	=> __( 'Percent', 'eventlist' ),
							'amount' 	=> __( 'Amount', 'eventlist' ),
							'both' 		=> __( 'Both(Percent, Fixed)', 'eventlist' ),
						),
						'default' => 'percent'
					),

					array(
						'type' => 'input',
						'label' => __( 'Percentage each order(%)', 'eventlist' ),
						'atts' => array(
							'id'          => 'percent_system_fee',
							'class'       => 'percent_system_fee',
							'type'        => 'number',
							'placeholder' => '10',
							'step' 		  => 'any'
						),
						'name' => 'percent_system_fee',
					),

					array(
						'type' => 'input',
						'label' => __( 'Fixed Amount', 'eventlist' ),
						'atts' => array(
							'id'          => 'fixed_system_fee',
							'class'       => 'fixed_system_fee',
							'type'        => 'number',
							'placeholder' => '0.5',
							'step' 		  => 'any'
						),
						'name' => 'fixed_system_fee',
					),
				)
			),
		);
	}

}

$GLOBALS['event_settings'] = new EL_Setting_Tax_Fee();