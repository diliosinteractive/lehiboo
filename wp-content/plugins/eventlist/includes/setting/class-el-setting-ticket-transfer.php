<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Ticket_Transfer extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'ticket_transfer';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 17;


	public function __construct()
	{
		$this->_title = __('Ticket Transfer', 'eventlist');
		parent::__construct();
	}

   // render fields
	public function load_field() {
		return
		array(
			array(
				'title' => __( 'Ticket Transfer Settings', 'eventlist' ),
				// 'desc' => __( '', 'eventlist' ),
				'fields' => array(
					array(
	                    'type'      => 'checkbox',
	                    'label'     => __( 'Allow Transfer Tickets', 'eventlist' ),
	                    'desc'      => esc_html__( 'The customer can transfer(resale) Tickets', 'eventlist' ),
	                    'name'      => 'allow_transfer_ticket',
	                    'default'   => '',
	                ),

					array(
	                    'type'      => 'checkbox',
	                    'label'     => __( 'Create an user account', 'eventlist' ),
	                    'desc'      => esc_html__( 'Allows creating user accounts when transferring tickets.', 'eventlist' ),
	                    'name'      => 'ticket_transfer_create_user',
	                    'default'   => '',
	                ),

	                array(
	                    'type'      => 'checkbox',
	                    'label'     => __( 'Allows changing customer name', 'eventlist' ),
	                    'desc'      => esc_html__( 'Allows changing customer name when transferring tickets.', 'eventlist' ),
	                    'name'      => 'ticket_transfer_change_customer_name',
	                    'default'   => '',
	                ),

	                array(
	                    'type'      => 'checkbox',
	                    'label'     => __( 'Add (transfer) after the customer name', 'eventlist' ),
	                    'desc'      => esc_html__( 'Add (transfer) after the customer name when transferring tickets.', 'eventlist' ),
	                    'name'      => 'ticket_transfer_add_transfer',
	                    'default'   => '',
	                ),

				)
			)

		);
	}

}

$GLOBALS['ticket_transfer_settings'] = new EL_Setting_Ticket_Transfer();