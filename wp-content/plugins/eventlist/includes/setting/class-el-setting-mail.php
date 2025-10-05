<?php
if ( ! defined( 'ABSPATH' ) ) {
   exit();
}

class EL_Setting_Mail extends EL_Abstract_Setting {
    /**
    * setting id
    * @var string
    */
    public $_id = 'mail';

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
    public $_position = 13;

    public function __construct() {
        $this->_title = __('Mail', 'eventlist');

        add_filter( 'el_admin_setting_fields', array( $this, 'generate_fields_booking' ), 10, 2 );
      parent::__construct();
      
   }

   public function generate_fields_booking( $groups, $id="booking" ) {

      if( $id == 'mail' ){

        $groups[$id . '_booking_mail'] = apply_filters( 'el_setting_mail_booking', $this->el_setting_mail_booking(), $this->id );
        $groups[$id . '_new_event'] = apply_filters( 'el_setting_mail_new_event', $this->el_setting_mail_new_event(), $this->id );

        if( is_plugin_active( 'ova-login/ova-login.php' ) ){
           $groups[$id . '_new_user'] = apply_filters( 'el_setting_mail_new_user', $this->el_setting_mail_new_user(), $this->id );
        }

        $groups[$id . '_report'] = apply_filters( 'el_setting_mail_report_event', $this->el_setting_mail_report_event(), $this->id );

        $groups[$id . '_mail_vendor'] = apply_filters( 'el_setting_mail_vendor', $this->el_setting_mail_vendor(), $this->id );

        $groups[$id . '_mail_payout'] = apply_filters( 'el_setting_mail_payout', $this->el_setting_mail_payout(), $this->id );

        $groups[$id . '_mail_remind_event_time'] = apply_filters( 'el_setting_mail_remind_event_time', $this->el_setting_mail_remind_event_time(), $this->id );

        $groups[$id . '_mail_cancel'] = apply_filters( 'el_setting_mail_cancel', $this->el_setting_mail_cancel(), $this->id );
      }

        return $groups;

   }

   public function el_setting_mail_booking(){
      return array(
        'title' => __('New Order', 'eventlist'),
       
         array(
            'fields' => array(
               array(
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allow to send an email after a customer books an event successfully', 'eventlist'),
                  'atts' => array(
                     'id' => 'enable_send_booking_email',
                     'class' => 'enable_send_booking_email',
                  ),
                  'name' => 'enable_send_booking_email',
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),
                array(
                    'type'      => 'checkbox',
                    'label'     => __( 'Sent to each customer', 'eventlist' ),
                    'desc'      => esc_html__( 'Allow send mail to each customer (multiple customer)', 'eventlist' ),
                    'name'      => 'booking_send_each_customer',
                    'default'   => '',
                ),
               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => __('Send email to Event manager, customer', 'eventlist'),
                  'atts' => array(
                     'id' => 'new_booking_sendmail',
                     'class' => 'new_booking_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'new_booking_sendmail',
                  'options' => array(
                     'administrator'   => __( 'Administrator', 'eventlist' ),
                     'event_manager'   => __( 'Vendor', 'eventlist' ),
                     'customer'        => __('Customer', 'eventlist'),
                  ),
                  'default' => array('administrator','event_manager','customer'),
               ),

               array(
                  'type' => 'input',
                  'label' => __('Recipient(s)', 'eventlist'),
                  'desc' => __('Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_vendor_recipient',
                     'class' => 'mail_new_vendor_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_vendor_recipient',
               ),

               array(
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in mail list', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_vendor_subject',
                     'class' => 'mail_new_vendor_subject',
                     'placeholder' => esc_html__( 'Booking Ticket Success', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_vendor_subject',
                  'default' => esc_html__( 'Booking Ticket Success', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('From name', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_vendor_from_name',
                     'class' => 'mail_new_vendor_from_name',
                     'placeholder' => esc_html__( 'Booking Ticket Success', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_vendor_from_name',
                  'default' => esc_html__( 'Booking Ticket Success', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'admin_email',
                     'class' => 'admin_email',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'admin_email',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'desc' => __('Use tags to generate email template<br/>
                     Event Name: [el_event]<br/>

                     Booking ID: [el_booking_id]<br/>

                     Total: [el_total]<br/>

                     Date: [el_date]<br/>

                     Ticket Type: [el_type_ticket]<br/>

                     Extra services: [el_extra_services]<br/>

                     Address: [el_address]<br/>

                     Venue: [el_venue]<br/>

                     Name: [el_name]<br/>

                     Phone: [el_phone]<br/>

                     Email: [el_email]<br/>
                     
                     [el_custom_fields]', 'eventlist'),

                  'atts' => array(
                     'id' => 'email_template',
                     'class' => 'email_template',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'email_template',
                  'default' => esc_html__( 'Event Name: [el_event]
Booking ID: [el_booking_id]
Total: [el_total]
Date: [el_date]
Ticket Type: [el_type_ticket]
Extra services: [el_extra_services]
Address: [el_address]
Venue: [el_venue]
Name: [el_name]
Phone: [el_phone]
Email: [el_email]
[el_custom_fields]', 'eventlist' ),
               ),

            )            
         )
      );
   }

   public function el_setting_mail_new_event(){
      return array(
         'title' => __('New Event', 'eventlist'),
         'desc' => __('Send mail to Admin, Recipient(s) when Vendor create Event', 'eventlist'),
         array(
            'fields' => array(

             
               array(
                  'name' => 'receive_email_after_create_event',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allow to send mail', 'eventlist'),
                  'atts' => array(
                     'id' => 'receive_email_after_create_event',
                     'class' => 'receive_email_after_create_event',
                  ),
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'no',
               ),

               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'new_event_sendmail',
                     'class' => 'new_event_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'new_event_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' )
                  ),
                  'default' => array('administrator','event_manager'),
               ),

               array(
                  'name' => 'mail_new_event_recipient',
                  'type' => 'input',
                  'label' => __('Recipient(s)', 'eventlist'),
                  'desc' => __('Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_event_recipient',
                     'class' => 'mail_new_event_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
               ),

               array(
                  'name' => 'mail_new_event_subject',
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in list mail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_event_subject',
                     'class' => 'mail_new_event_subject',
                     'placeholder' => esc_html__( 'New Event', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'default' => esc_html__( 'New Event', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('From name', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_event_from_name',
                     'class' => 'mail_new_event_from_name',
                     'placeholder' => esc_html__( 'New Event', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_event_from_name',
                  'default' => esc_html__( 'New Event', 'eventlist' ),
               ),

               array(
                  'name' => 'mail_new_event_send_from',
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_event_send_from',
                     'class' => 'mail_new_event_send_from',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'default' => get_option('admin_email'),
               ),


               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'desc' => __('Use [el_event] tags to generate email template. Example: <br/> New Event: [el_event]', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_event_content',
                     'class' => 'mail_new_event_content',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_new_event_content',
                  'default' => esc_html__(' A new event created: [el_event] ', 'eventlist' ),
               ),
            ),
         ),
      );
      
   }

   public function el_setting_mail_new_user(){
       return array(
         'title' => __('New User/Vendor', 'eventlist'),
         array(
            'fields' => array(

               array(
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allow send email when new user register', 'eventlist'),
                  'name' => 'enable_send_new_account_email',
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),

               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'new_account_sendmail',
                     'class' => 'new_account_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'new_account_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'user' => __( 'User', 'eventlist' )
                  ),
                  'default' => array('administrator','user'),
               ),

               array(
                  'type' => 'input',
                  'label' => esc_html__('Send from email', 'eventlist'),
                  'desc' => esc_html__('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_acocunt_from_email',
                     'class' => 'mail_new_acocunt_from_email',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_acocunt_from_email',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'type' => 'input',
                  'label' => esc_html__('Recipient(s)', 'eventlist'),
                  'desc'   => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'atts' => array(
                     'id' => 'mail_new_account_recipient',
                     'class' => 'mail_new_account_recipient',
                     'type' => 'text',
                  ),
                  'name' => 'mail_new_account_recipient',
                  'default' => '',
               ),
             
               array(
                  'type' => 'editor',
                  'label' => esc_html__('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_new_acocunt_content',
                     'class' => 'mail_new_acocunt_content',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_new_acocunt_content',
                  'desc' => esc_html__('Use tags to generate email template. Example: You registered user [el_link_profile] successfully at [el_link_home_page]', 'eventlist'),
                  'default' => esc_html__('You registered user [el_link_profile] successfully at [el_link_home_page]', 'eventlist'),
               ),

            ),
         ),
      );
   }

   public function el_setting_mail_report_event(){
       return array(
         'title' => __('Report Event', 'eventlist'),
         'desc' => __('The Guest report an event', 'eventlist'),
         array(
            'fields' => array(

                

               array(
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('You will see subject in list mail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_report_event_subject',
                     'class' => 'mail_report_event_subject',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_report_event_subject',
                  'default' => esc_html__( 'Report event', 'eventlist' ),
               ),

                array(
                  'type' => 'input',
                  'label' => __('From name', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_report_event_from_name',
                     'class' => 'mail_report_event_from_name',
                     'placeholder' => esc_html__( 'Report event', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_report_event_from_name',
                  'default' => esc_html__( 'Report event', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_report_event_send_from_email',
                     'class' => 'mail_report_event_send_from_email',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'mail_report_event_send_from_email',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'report_event_sendmail',
                     'class' => 'report_event_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'report_event_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' ),
                  ),
                  'default' => array( 'administrator', 'event_manager' ),
               ),

               array(
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_report_event_recipient',
                     'class' => 'mail_report_event_recipient',
                     'type' => 'text',
                  ),
                  'name' => 'mail_report_event_recipient',
                  'default' => '',
               ),
             
               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_report_event_content',
                     'class' => 'mail_report_event_content',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_report_event_content',
                  'desc' => __('Use tags to generate email template<br/>
                     Message: [el_message]<br/>
                     Link Event: [el_link_event]<br/>', 'eventlist'),
                  'default' => esc_html__( 'Message: [el_message]
Link Event: [el_link_event]', 'eventlist' ),
               ),
            ),
         ),
      );
   }

   public function el_setting_mail_vendor(){
       return array(
         'title' => __('Contact Vendor', 'eventlist'),
         'desc' => __('The Guest contact Vendor at frontend', 'eventlist'),
         array(

            'fields' => array(

               
               
               array(
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_contact_vendor_from_name',
                     'class' => 'mail_contact_vendor_from_name',
                     'placeholder' => esc_html__( 'Contact Vendor', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_contact_vendor_from_name',
                  'default' => esc_html__( 'Contact Vendor', 'eventlist' ),
               ),


               array(
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'admin_email_mail_vendor',
                     'class' => 'admin_email_mail_vendor',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'admin_email_mail_vendor',
                  'default' => get_option('admin_email'),
               ),


               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'contact_vendor_sendmail',
                     'class' => 'contact_vendor_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'contact_vendor_sendmail',
                  'options' => array(
                     'administrator'   => __( 'Administrator', 'eventlist' ),
                     'event_manager'   => __( 'Vendor', 'eventlist' ),
                     'customer'        => __( 'Customer', 'eventlist' ),
                  ),
                  'default' => array( 'administrator', 'event_manager' ,'customer' ),
               ),

               array(
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'contact_vendor_recipient',
                     'class' => 'contact_vendor_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'contact_vendor_recipient',
                  'default' => '',
               ),
             
               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_vendor_email_template',
                     'class' => 'mail_vendor_email_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_vendor_email_template',
                  'desc' => __('Use tags to generate email template<br/>
                     Event: [el_event_name]<br/>
                     Name: [el_name]<br/>
                     Email: [el_mail]<br/>
                     Phone: [el_phone]<br/>
                     Content: [el_content]<br/>', 'eventlist'),
                  'default' => __( 'Event: [el_event_name]
Name: [el_name]
Email: [el_mail]
Phone: [el_phone]
Content: [el_content]', 'eventlist'),
               ),
            ),
         ),
      );
   }

   public function el_setting_mail_payout(){
       return array(
         'title' => __('Payout', 'eventlist'),
         'desc' => __('Send email to admin when vendor requests withdrawal', 'eventlist'),

         array(
            'accordion' => array(
               'request_withdraw' => __('Request Withdraw','eventlist'),
               'payout_completed' => __( 'Payout Completed', 'eventlist' ),
               'payout_canceled' => __( 'Payout Canceled', 'eventlist' ),
            ),
            'fields' => array(

               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allows send email when a vendor requests a withdrawal', 'eventlist'),
                  'name' => 'enable_send_withdrawal_email',
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),
               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_request_from_name',
                     'class' => 'mail_payout_request_from_name',
                     'placeholder' => esc_html__( 'Request Withdraw', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_request_from_name',
                  'default' => esc_html__( 'Request Withdraw', 'eventlist' ),
               ),


               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'admin_email_payout_request',
                     'class' => 'admin_email_payout_request',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'admin_email_payout_request',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'mail_payout_request_sendmail',
                     'class' => 'mail_payout_request_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'mail_payout_request_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' ),
                  ),
                  'default' => array( 'administrator', 'event_manager' ),
               ),

               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_request_recipient',
                     'class' => 'mail_payout_request_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_request_recipient',
                  'default' => '',
               ),
             
               array(
                  'belong_to' => 'request_withdraw',
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'payout_request_email_template',
                     'class' => 'payout_request_email_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'payout_request_email_template',
                  'desc' => __('Use tags to generate email template<br/>
                     Name: [el_name]<br/>
                     Amount to withdraw: [el_amount]<br/>
                     Payment Method: [el_payment_method]<br/>
                     Payout ID: [el_payout]', 'eventlist'),
                  'default' => __( 'Name: [el_name]
Amount to withdraw: [el_amount]
Payment Method: [el_payment_method]
Payout ID: [el_payout]', 'eventlist'),
               ),
               // Payout Completed
               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allows send email when admin update withdrawal status as completed', 'eventlist'),
                  'name' => 'enable_send_payout_completed_email',
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),

               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_completed_from_name',
                     'class' => 'mail_payout_completed_from_name',
                     'placeholder' => esc_html__( 'Payout Completed', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_completed_from_name',
                  'default' => esc_html__( 'Payout Completed', 'eventlist' ),
               ),

               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'admin_email_payout_completed',
                     'class' => 'admin_email_payout_completed',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'admin_email_payout_completed',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'mail_payout_completed_sendmail',
                     'class' => 'mail_payout_completed_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'mail_payout_completed_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' )
                  ),
                  'default' => array( 'administrator', 'event_manager' ),
               ),

               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_completed_recipient',
                     'class' => 'mail_payout_completed_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_completed_recipient',
                  'default' => '',
               ),

               array(
                  'belong_to' => 'payout_completed',
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'payout_completed_email_template',
                     'class' => 'payout_completed_email_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'payout_completed_email_template',
                  'desc' => __('Use tags to generate email template<br/>
                     Payout ID: [el_payout]<br/>
                     Amount to withdraw: [el_amount]<br/>
                     Payout Method: [el_payout_method]<br/>
                     Extra Info: [el_extra_info]', 'eventlist'),
                  'default' => __( 'Payout ID: [el_payout]
Amount to withdraw: [el_amount]
Payout Method: [el_payout_method]
Extra Info: [el_extra_info]', 'eventlist'),
               ),

               // Payout Canceled
               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allows send email when admin update withdrawal status as canceled', 'eventlist'),
                  'name' => 'enable_send_payout_canceled_email',
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),
               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_canceled_from_name',
                     'class' => 'mail_payout_canceled_from_name',
                     'placeholder' => esc_html__( 'Payout Canceled', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_canceled_from_name',
                  'default' => esc_html__( 'Payout Canceled', 'eventlist' ),
               ),

               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('Customers will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'admin_email_payout_canceled',
                     'class' => 'admin_email_payout_canceled',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'admin_email_payout_canceled',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'mail_payout_canceled_sendmail',
                     'class' => 'mail_payout_canceled_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'mail_payout_canceled_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' )
                  ),
                  'default' => array( 'administrator', 'event_manager' ),
               ),

               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_payout_canceled_recipient',
                     'class' => 'mail_payout_canceled_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_payout_canceled_recipient',
                  'default' => '',
               ),

               array(
                  'belong_to' => 'payout_canceled',
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'payout_completed_email_template',
                     'class' => 'payout_completed_email_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'payout_canceled_email_template',
                  'desc' => __('Use tags to generate email template<br/>
                     Payout ID: [el_payout]<br/>
                     Amount to withdraw: [el_amount]<br/>
                     Payout Method: [el_payout_method]<br/>
                     Extra Info: [el_extra_info]', 'eventlist'),
                  'default' => __( 'Payout ID: [el_payout]
Amount to withdraw: [el_amount]
Payout Method: [el_payout_method]
Extra Info: [el_extra_info]', 'eventlist'),
               ),

            ),
         ),
      );
   }

   public function el_setting_mail_remind_event_time(){
      return array(
         'title' => __('Remind customer', 'eventlist'),
         'desc' => __('Remind customer about event start time that they registered', 'eventlist'),
         array(
            'fields' => array(

               array(
                  'name' => 'remind_mail_enable',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allow to send mail', 'eventlist'),
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),
               array(
                  'name' => 'remind_mail_before_xday',
                  'type' => 'input',
                  'label' => __('Before x day', 'eventlist'),
                  'desc' => __('Send mail x days before the event starts', 'eventlist'),
                  'default' => '3',
                  'atts' => array(
                     'type' => 'number',
                     'step' => '0.01',
                  ),
               ),

               array(
                  'name' => 'remind_mail_send_per_seconds',
                  'type' => 'input',
                  'label' => __('Send a mail every x seconds', 'eventlist'),
                  'desc' => __('You can setup 86400 to send 1 time per day', 'eventlist'),
                  'default' => '86400',
                   'atts' => array(
                     'type' => 'number',
                  ),
               ),

               array(

                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'mail_remind_sendmail',
                     'class' => 'mail_remind_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'mail_remind_sendmail',
                  'options' => array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' ),
                     'customer' => __( 'Customer', 'eventlist' )
                  ),
                  'default' => array( 'customer' ),
               ),

               array(

                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_remind_recipient',
                     'class' => 'mail_remind_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_remind_recipient',
                  'default' => '',
               ),

               array(
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in the email list', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_remind_time_subject',
                     'class' => 'mail_remind_time_subject',
                     'type' => 'text',
                  ),
                  'name' => 'mail_remind_time_subject',
                  'default' => esc_html__( 'Remind event start time', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('From name', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_remind_time_from_name',
                     'class' => 'mail_remind_time_from_name',
                     'placeholder' => esc_html__( 'Remind event start time', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_remind_time_from_name',
                  'default' => esc_html__( 'Remind event start time', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('The guest will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_sendfrom_remind_time',
                     'class' => 'mail_sendfrom_remind_time',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'mail_sendfrom_remind_time',
                  'default' => get_option('admin_email'),
               ),
             
               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_remind_time_template',
                     'class' => 'mail_remind_time_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_remind_time_template',
                  'desc' => __('Use tags to generate email template. For example: <br/>You registered event: [el_event_name] at [el_event_start_time]', 'eventlist'),
                  'default' => esc_html__( 'You registered event: [el_event_name] at [el_event_start_time]', 'eventlist' ),
               ),
            ),
         ),
      );
   }

   public function el_setting_mail_cancel(){
      return array(
         'title' => __('Cancel Booking', 'eventlist'),
         // 'desc' => __('Remind customer about event start time that they registered', 'eventlist'),
         array(
            'fields' => array(

               array(
                  'name' => 'cancel_mail_enable',
                  'type' => 'select',
                  'label' => __('Enable', 'eventlist'),
                  'desc' => __('Allow to send mail', 'eventlist'),
                  'options' => array(
                     'yes' => __('Yes', 'eventlist'),
                     'no' => __('No', 'eventlist'),
                  ),
                  'default' => 'yes',
               ),
               

               array(
                  'type' => 'input',
                  'label' => __('Subject', 'eventlist'),
                  'desc' => __('The subject displays in the email list', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_cancel_booking_time_subject',
                     'class' => 'mail_cancel_booking_time_subject',
                     'type' => 'text',
                  ),
                  'name' => 'mail_cancel_booking_time_subject',
                  'default' => esc_html__( 'Cancellation Booking', 'eventlist' ),
               ),

               array(
                  'type' => 'input',
                  'label' => __('From name', 'eventlist'),
                  'desc' => __('The subject displays in mail detail', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_cancel_booking_from_name',
                     'class' => 'mail_cancel_booking_from_name',
                     'placeholder' => esc_html__( 'Cancellation Booking', 'eventlist' ),
                     'type' => 'text',
                  ),
                  'name' => 'mail_cancel_booking_from_name',
                  'default' => esc_html__( 'Cancellation Booking', 'eventlist' ),
               ),


               array(
                  'type' => 'input',
                  'label' => __('Send from email', 'eventlist'),
                  'desc' => __('The guest will know them to receive mail from which email address is', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_sendfrom_cancel_booking',
                     'class' => 'mail_sendfrom_cancel_booking',
                     'placeholder' => get_option('admin_email'),
                     'type' => 'text',
                  ),
                  'name' => 'mail_sendfrom_cancel_booking',
                  'default' => get_option('admin_email'),
               ),

               array(
                  'type' => 'select',
                  'label' => __('Send email to', 'eventlist'),
                  'desc' => '',
                  'atts' => array(
                     'id' => 'mail_cancel_booking_sendmail',
                     'class' => 'mail_cancel_booking_sendmail',
                     'multiple' => 'multiple',
                  ),
                  'name' => 'mail_cancel_booking_sendmail',
                  'options' => apply_filters('mail_cancel_booking_sendmail',array(
                     'administrator' => __( 'Administrator', 'eventlist' ),
                     'event_manager' => __( 'Vendor', 'eventlist' ),
                     'customer' => __( 'Customer', 'eventlist' ),
                  )),
                  'default' => array( 'administrator', 'event_manager' ,'customer' ),
               ),

               array(
                  'type' => 'input',
                  'desc' => esc_html__( 'Add recipient\'s email addresses (use comma seperated to add more email addresses)', 'eventlist' ),
                  'label' => __('Recipient(s)', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_cancel_booking_recipient',
                     'class' => 'mail_cancel_booking_recipient',
                     'placeholder' => '',
                     'type' => 'text',
                  ),
                  'name' => 'mail_cancel_booking_recipient',
                  'default' => '',
               ),
             
               array(
                  'type' => 'editor',
                  'label' => __('Email Content', 'eventlist'),
                  'atts' => array(
                     'id' => 'mail_cancel_booking_template',
                     'class' => 'mail_cancel_booking_template',
                     'placeholder' => '',
                     'type' => 'text',
                     'cols' => 50,
                     'rows' => 20,
                  ),
                  'name' => 'mail_cancel_booking_template',
                  'desc' => __('Use tags to generate email template. For example: <br/>Cancel Booking #[booking_id] Successfully. You canceled event [event_name] at [event_date]', 'eventlist'),
                  'default' => esc_html__( 'Cancel Booking #[booking_id] Successfully. You canceled event [event_name] at [event_date]', 'eventlist' ),
               ),
            ),
         ),
      );
   }
}

$GLOBALS['mail_settings'] = new EL_Setting_Mail();