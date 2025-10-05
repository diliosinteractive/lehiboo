<?php
if ( ! defined( 'ABSPATH' ) ) exit();

class EL_Setting_Invoice extends EL_Abstract_Setting {
	/**
	* setting id
	* @var string
	*/
	public $_id = 'invoice';

	/**
	* _title
	* @var null
	*/
	public $_title = null;
	public $_tab = true;

	/**
	* $_position
	* @var integer
	*/
	public $_position = 16;

	public function __construct() {
		$this->_title = __('PDF Invoice', 'eventlist');

		add_filter( 'el_admin_setting_fields', array( $this, 'generate_fields_booking' ), 10, 2 );
		parent::__construct();
	}

	public function generate_fields_booking( $groups, $id = "invoice" ) {
		if ( $id == 'invoice' ) {
			$groups[$id . '_mail_invoices'] = apply_filters( 'el_setting_mail_invoices', $this->el_setting_mail_invoices(), $this->id );
			$groups[$id . '_templace_invoices'] = apply_filters( 'el_setting_templace_invoices', $this->el_setting_templace_invoices(), $this->id );
		}

		return $groups;
	}
   
	public function el_setting_mail_invoices() {
		return array(
			'title' => __( 'Invoice Mail', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'name'  => 'invoice_mail_enable',
						'type'  => 'select',
						'label' => __( 'Enable', 'eventlist' ),
						'desc'  => __( 'Attach PDF invoice in email', 'eventlist' ),
						'options' => array(
							'yes'   => __('Yes', 'eventlist'),
							'no'    => __('No', 'eventlist'),
						),
						'default' => 'no',
					),
					array(
                        'name'  => 'invoice_mail_subject',
                        'type'  => 'input',
                        'label' => __('Subject', 'eventlist'),
                        'desc'  => __('The subject displays in mail list', 'eventlist'),
                        'atts'  => array(
                            'type'  => 'text',
                            'id'    => 'invoice_mail_subject',
                            'class' => 'invoice_mail_subject',
                            'placeholder' => esc_html__( 'Booking Invoice', 'eventlist' ),
                        ),
                        'default' => esc_html__( 'Booking Invoice', 'eventlist' ),
                    ),
                    array(
                        'name'  => 'invoice_mail_from_name',
                        'type'  => 'input',
                        'label' => __('From name', 'eventlist'),
                        'desc'  => __('The subject displays in mail detail', 'eventlist'),
                        'atts'  => array(
                            'type'  => 'text',
                            'id'    => 'invoice_mail_from_name',
                            'class' => 'invoice_mail_from_name',
                            'placeholder' => esc_html__( 'Booking Invoice', 'eventlist' ),
                        ),
                        'default' => esc_html__( 'Booking Invoice', 'eventlist' ),
                    ),
                    array(
                        'name'  => 'invoice_mail_from_email',
                        'type'  => 'input',
                        'label' => __('From name', 'eventlist'),
                        'desc'  => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                        'atts'  => array(
                            'type'  => 'text',
                            'id'    => 'invoice_mail_from_email',
                            'class' => 'invoice_mail_from_email',
                            'placeholder' => get_option('admin_email'),
                        ),
                        'default' => get_option('admin_email'),
                    ),
                    array(
                        'name'  => 'invoice_mail_content',
                        'type'  => 'editor',
                        'desc'  => __('Invoice for booking: #[booking_id]', 'eventlist'),
                        'atts'  => array(
                            'id'    => 'invoice_mail_content',
                            'class' => 'invoice_mail_content',
                            'type'  => 'text',
                        ),
                        'label'     => __( 'Content', 'eventlist' ),
                        'default'   => __('Invoice for booking: #[booking_id]', 'eventlist'),
                    ),
				),
			),
		);
	}

	public function el_setting_templace_invoices() {
		return array(
			'title' => __( 'PDF Template', 'eventlist' ),
			array(
				'fields' => array(
					array(
						'name'  => 'invoice_pdf_title',
						'type'  => 'input',
						'atts' => array(
							'id'    => 'invoice_pdf_title',
							'class' => 'invoice_pdf_title',
							'type'  => 'text',
						),
						'label'     => __( 'Title', 'eventlist' ),
						'default'   => esc_html__( 'Invoice', 'eventlist' ),
					),
					array(
						'name'  => 'invoice_pdf_logo',
						'type'  => 'image',
						'atts' => array(
							'id'    => 'invoice_pdf_logo',
							'class' => 'invoice_pdf_logo',
							'type'  => 'hidden',
						),
						'label' => __( 'Logo', 'eventlist' ),
					),
					array(
						'name'  => 'invoice_shop_name',
						'type'  => 'input',
						'atts' => array(
							'id'    => 'invoice_shop_name',
							'class' => 'invoice_shop_name',
							'type'  => 'text',
						),
						'label'     => __( 'Shop Name', 'eventlist' ),
						'default'   => get_bloginfo( 'name' ),
					),
					array(
						'name'  => 'invoice_shop_address',
						'type'  => 'textarea',
						'atts'  => array(
							'id'    => 'invoices_shop_address',
							'class' => 'invoices_shop_address',
							'cols'  => 50,
							'rows'  => 5,
						),
						'label'     => __( 'Shop Address', 'eventlist' ),
						'default'   => '',
					),
					array(
						'name'  => 'invoice_pdf_footer',
						'type'  => 'editor',
						'atts'  => array(
							'id'    => 'invoice_pdf_footer',
							'class' => 'invoice_pdf_footer',
							'type'  => 'text',
						),
						'label'     => __( 'Footer', 'eventlist' ),
						'default'   => '© 2023 <a href="https://ovatheme.com/">ovatheme.com</a>. All Rights Reserved.',
					),
				),
			),
		);
	}
}

$GLOBALS['invoice_settings'] = new EL_Setting_Invoice();