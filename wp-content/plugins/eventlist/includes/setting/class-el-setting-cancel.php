<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Cancel extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'cancel';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 13;


	public function __construct()
	{
		$this->_title = __('Cancel', 'eventlist');
		parent::__construct();
	}

   // render fields
	public function load_field() {
		return
		array(
			array(
				'title' => __( 'Booking Cancellation  Settings', 'eventlist' ),
				
				'fields' => array(
					
					array(
						'type' => 'checkbox',
						'label' => __( 'Enable', 'eventlist' ),
						'desc' => esc_html__( 'Allow customers to cancel booking', 'eventlist' ),
						'name' => 'cancel_enable',
						'default' => '',
					),

					
					

				)
			),
			

		);
	}

}

$GLOBALS['role_settings'] = new EL_Setting_Cancel();