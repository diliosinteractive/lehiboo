<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Role extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'role';

	/**
     * _title
     * @var null
     */
	public $_title = null;

	/**
     * $_position
     * @var integer
     */
	public $_position = 15;


	public function __construct()
	{
		$this->_title = __('Role', 'eventlist');
		parent::__construct();
	}

   // render fields
	public function load_field() {
		return
		array(
			array(
				'title' => __( 'Vendor Role Settings', 'eventlist' ),
				'desc' => __( 'Set up permission for Event Manager (Vendors)', 'eventlist' ),
				'fields' => array(
					
					array(
						'type' => 'checkbox',
						'label' => __( 'Add Event', 'eventlist' ),
						'desc' => '',
						'name' => 'add_event',
						'default' => '1',
					),

					array(
						'type' => 'checkbox',
						'label' => __( 'Edit Event', 'eventlist' ),
						'desc' => '',
						'name' => 'edit_event',
						'default' => '1',
					),

					array(
						'type' => 'checkbox',
						'label' => __( 'Publish Event', 'eventlist' ),
						'desc' => __( 'If Yes: Auto Publish<br/>If No: the Admin will review events before Publishing', 'eventlist' ),
						'name' => 'publish_event',
						'default' => '1',
					),
					

					array(
						'type' => 'checkbox',
						'label' => __( 'Delete Event', 'eventlist' ),
						'desc' => '',
						'name' => 'delete_event',
						'default' => '1',
					),
					

					array(
						'type' => 'checkbox',
						'label' => __( 'Upload Image', 'eventlist' ),
						'desc' => '',
						'name' => 'upload_files',
						'default' => '1',
					),

					array(
	                  'type' => 'select',
	                  'label' => esc_html__('Allow to sell tickets', 'eventlist'),
	                  'desc'   => esc_html__( 'Choose No: the vendor can\'t make ticket, coupon, staff member, cancel booking tab when create event', 'eventlist' ),
	                  'name' => 'allow_to_selling_ticket',
	                  'options' => array(
	                     'yes' => esc_html__('Yes', 'eventlist'),
	                     'no' => esc_html__('No', 'eventlist'),
	                  ),
	                  'default' => 'yes',
	               ),

					array(
						'type' => 'checkbox',
						'label' => esc_html__( 'Manage Bookings', 'eventlist' ),
						'desc' => esc_html__( 'May override by Package of vendor', 'eventlist' ),
						'name' => 'manage_booking',
						'default' => '1',
					),

					array(
						'type' => 'checkbox',
						'label' => esc_html__( 'Manage Tickets', 'eventlist' ),
						'desc' => esc_html__( 'May override by Package of vendor', 'eventlist' ),
						'name' => 'manage_ticket',
						'default' => '1',
					),

					array(
						'type' => 'checkbox',
						'label' => esc_html__( 'Create Tickets', 'eventlist' ),
						'desc' => esc_html__( 'Vendors can create tickets manually', 'eventlist' ),
						'name' => 'create_tickets',
						'default' => '',
					),

					array(
						'type' => 'input',
						'label' => esc_html__( 'Max Day', 'eventlist' ),
						'desc' => esc_html__( 'Only allow search tickets in date range', 'eventlist' ),
						'atts' => array(
							'id'          => 'day_search_ticket',
							'class'       => 'day_search_ticket',
							'type'        => 'number',
							'placeholder' => '7',
						),
						'name' => 'day_search_ticket',
						'default'	=> '7'
					),
					

				)
			),
			array(
				'title' => esc_html__( 'User Role Settings', 'eventlist' ),
				'desc' => esc_html__( 'Set up permission for Users', 'eventlist' ),
				'fields' => array(
					array(
						'type' => 'checkbox',
						'label' => esc_html__( 'Upload Image', 'eventlist' ),
						'desc' => '',
						'name' => 'user_upload_files',
						'default' => '1',
					),
				)
			),



		);
	}

}

$GLOBALS['role_settings'] = new EL_Setting_Role();