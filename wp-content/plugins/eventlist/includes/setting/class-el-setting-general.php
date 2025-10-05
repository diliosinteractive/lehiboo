<?php
if (!defined('ABSPATH')) {
   exit();
}

class EL_Setting_General extends EL_Abstract_Setting {
   /**
    * setting id
    * @var string
    */
   public $_id = 'general';

   /**
    * _title
    * @var null
    */
   public $_title = null;

   /**
    * $_position
    * @var integer
    */
   public $_position = 10;

   public $_tab = true;

   public function __construct() {

      $this->_title = __('General', 'eventlist');
      add_filter( 'el_admin_setting_fields', array( $this, 'el_generate_fields_event' ), 10, 2 );
      parent::__construct();
   }

   public function el_generate_fields_event( $groups, $id="general" ){

       if( $id == 'general' ){

        $groups[$id . '_general_event'] = apply_filters( 'el_admin_setting_fields_general_event', $this->el_admin_setting_fields_general_event(), $this->id );

        $groups[$id . '_map_event'] = apply_filters( 'el_admin_setting_fields_map_event', $this->el_admin_setting_fields_map_event(), $this->id );

        $groups[$id . '_currency_event'] = apply_filters( 'el_admin_setting_fields_currency_event', $this->el_admin_setting_fields_currency_event(), $this->id );

        $groups[$id . '_calendar_event'] = apply_filters( 'el_admin_setting_fields_calendar_event', $this->el_admin_setting_fields_calendar_event(), $this->id );

        $groups[$id . '_cron_event'] = apply_filters( 'el_admin_setting_fields_cron_event', $this->el_admin_setting_fields_cron_event(), $this->id );


      }

        return $groups;

   }

   public function el_admin_setting_fields_general_event(){

      return array(
         
            'title' => __('General Settings', 'eventlist'),
            array(
               'fields' => array(

                  array(
                     'type' => 'select_page',
                     'label' => __('Cart page', 'eventlist'),
                     'desc' => __('Page contents: [el_cart/]', 'eventlist'),
                     'atts' => array(
                        'id' => 'cart_page',
                        'class' => 'cart_page',
                     ),
                     'name' => 'cart_page_id',
                  ),

                  array(
                     'type' => 'select_page',
                     'label' => __('Thank you page', 'eventlist'),
                     'desc' => __('Redirect after booking successfully. You should add shortcode [el_booking_detail/] in page to display booking detail.', 'eventlist'),
                     'atts' => array(
                        'id' => 'thanks_page',
                        'class' => 'thanks_page',
                     ),
                     'name' => 'thanks_page_id',
                  ),

                  array(
                     'type' => 'select_page',
                     'label' => __('Search Result page', 'eventlist'),
                     'desc' => __('This page included content: Shortcode ( [el_search_form/] and [el_search_result/]) or Elements Search Form and Search Result or Search Map', 'eventlist'),
                     'atts' => array(
                        'id' => 'search_result_page',
                        'class' => 'search_result_page',
                     ),
                     'name' => 'search_result_page_id',
                  ),

                  array(
                     'type' => 'select_page',
                     'label' => __('My Account page', 'eventlist'),
                     'desc' => __('Page contents: [el_member_account/]', 'eventlist'),
                     'atts' => array(
                        'id' => 'myaccount_page',
                        'class' => 'myaccount_page',
                     ),
                     'name' => 'myaccount_page_id',
                  ),

                  array(
                     'type' => 'input',
                     'label' => __('Secret Key QR Code', 'eventlist'),
                     'desc' => __('This key will attach to string to make QR Code', 'eventlist'),
                     'name' => 'serect_key_qrcode',
                     'default' => 'ovatheme.com',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Scan qr code with', 'eventlist'),
                     'desc' => __('Scan qr code using application or website', 'eventlist'),
                     'name' => 'scan_qr_code_with',
                     'options' => array(
                        'app' => __('Application', 'eventlist'),
                        'web' => __('Website', 'eventlist'),
                     ),
                     'default' => 'app',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Remove default image size', 'eventlist'),
                     'desc' => __('These image size doesn\'t use in plugin', 'eventlist'),
                     'name' => 'remove_img_size',
                     'options' => array(
                        'yes' => __('Yes', 'eventlist'),
                        'no' => __('No', 'eventlist'),
                     ),
                     'default' => 'yes',
                  ),

                  array(
                     'type' => 'select',
                     'label' => esc_html__('Remove Woocommerce image size', 'eventlist'),
                     'desc' => esc_html__('These image size doesn\'t use in plugin', 'eventlist'),
                     'name' => 'remove_woo_img_size',
                     'options' => array(
                        'yes' => esc_html__('Yes', 'eventlist'),
                        'no' => esc_html__('No', 'eventlist'),
                     ),
                     'default' => 'yes',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Add Additional File Types to be Uploaded', 'eventlist'),
                     'desc' => __('List Additional File Types <a target="_blank" href="https://codex.wordpress.org/Function_Reference/get_allowed_mime_types">here</a><br/>Example: zip, pdf', 'eventlist'),
                     'name' => 'event_upload_file',
                     'default' => '',
                  ),
                  
                  array(
                     'type' => 'select_key',
                     'label' => __('Allows downloading tickets as zip files', 'eventlist'),
                     'desc' => __('Downloading tickets as zip files', 'eventlist'),
                     'name' => 'ticket_download_zip',
                     'options' => array(
                        'yes' => __('Yes', 'eventlist'),
                        'no' => __('No', 'eventlist'),
                     ),
                     'default' => 'no',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Total Custom Taxonomy', 'eventlist'),
                     'name' => 'el_total_taxonomy',
                     'default' => 2,
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Cookie Expired(seconds)', 'eventlist'),
                     'desc' => esc_html__('Used in Recent View Event Feature', 'eventlist'),
                     'atts' => array(
                        'id' => 'cookie_expired',
                        'class' => 'cookie_expired',
                        'placeholder' => '86400',
                        'type' => 'number',
                     ),
                     'name' => 'cookie_expired',
                     'default' => '604800',
                  ),

               )
            )

      );
   }

   public function el_admin_setting_fields_map_event(){
      return array(
         
            'title' => __('Map Settings', 'eventlist'),
            array(
               'fields' => array(

                
                  array(
                     'type' => 'input',
                     'label' => __('Google API Key Map', 'eventlist'),
                     'desc' => __('You can make a API Key Map <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">here</a>', 'eventlist'),
                     'name' => 'event_google_key_map',
                     'default' => '',
                  ),

                  array(
                     'type' => 'checkbox',
                     'label' => esc_html__( 'Bounds', 'eventlist' ),
                     'desc' => esc_html__('Use for Search Map', 'eventlist'),
                     'name' => 'event_bound',
                     'atts' => array(
                        'id' => 'event_bound',
                        'class' => 'event_bound',
                     ),
                     'default' => '',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Latitude', 'eventlist'),
                     'desc' => esc_html__('Ex: 40.6976312', 'eventlist'),
                     'atts' => array(
                        'id' => 'event_lat',
                        'class' => 'event_lat',
                        'placeholder' => '',
                        'type'        => 'text',
                        'placeholder' => '40.6976312',
                     ),
                     'name' => 'event_lat',
                     'default' => '',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Longitude', 'eventlist'),
                     'desc' => esc_html__('Ex: -74.1444847', 'eventlist'),
                     'atts' => array(
                        'id' => 'event_lng',
                        'class' => 'event_lng',
                        'placeholder' => '',
                        'type'        => 'text',
                        'placeholder' => '-74.1444847',
                     ),
                     'name' => 'event_lng',
                     'default' => '',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Radius(kilometers)', 'eventlist'),
                     'desc' => esc_html__('Ex: 100', 'eventlist'),
                     'atts' => array(
                        'id' => 'event_radius',
                        'class' => 'event_radius',
                        'placeholder' => '',
                        'type' => 'number',
                        'placeholder' => '100',
                     ),
                     'name' => 'event_radius',
                     'default' => '',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Search by Country', 'eventlist'),
                     'desc' => esc_html__('Use for Search Map', 'eventlist'),
                     'atts' => array(
                        'id' => 'event_retrict',
                        'class' => 'event_retrict',
                        'multiple' => 'multiple',
                     ),
                     'name' => 'event_retrict',
                     'options' => ova_event_iso_alpha2(),
                     'default' => '',
                  ),

               )
            )

      );
   }

   public  function el_admin_setting_fields_calendar_event(){

      // Language Calendar
      $calendar_language = el_get_calendar_language();

      if ($calendar_language) {
         foreach ($calendar_language as $code => $name) {
            $calendar_language[$code] = $name;
         }
      }
      return array(
         
            'title' => __('Calendar Settings', 'eventlist'),
            array(
               'fields' => array(

                  array(
                     'type' => 'select',
                     'label' => __('Date Format', 'eventlist'),
                      'desc' => __('To be defined when choosing to input a date', 'eventlist'),
                     'name' => 'cal_date_format',
                     'options' => array(
                        'dd-mm-yy' => __('27-10-2020', 'eventlist'),
                        'mm/dd/yy' => __('10/27/2020', 'eventlist'),
                        'yy/mm/dd' => __('2020/10/27', 'eventlist'),
                        'yy-mm-dd' => __('2020-10-27', 'eventlist')
                     ),
                     'default' => 'dd-mm-yy',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Time Format', 'eventlist'),
                     'desc' => '',
                     'name' => 'calendar_time_format',
                     'options' => array(
                        '12' => __('12 Hour', 'eventlist'),
                        '24' => __('24 Hour', 'eventlist'),
                     ),
                     'default' => '12',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Calendar Language', 'eventlist'),
                     'desc' => __('This language calendar', 'eventlist'),
                     'atts' => array(
                        'id' => 'calendar_language',
                        'class' => 'calendar_language',
                     ),
                     'name' => 'calendar_language',
                     'options' => $calendar_language,
                     'default' => 'en-GB',
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('Choose weekend: ', 'eventlist'),
                     'desc' => '',
                     'name' => 'choose_week_end',
                     'atts' => array(
                        'id' => 'choose_week_end',
                        'class' => 'choose_week_end',
                        'multiple' => 'multiple',
                     ),
                     'options' => array(
                        'monday' => __('Monday', 'eventlist'),
                        'tueday' => __('Tuesday', 'eventlist'),
                        'wednesday' => __('Wednesday', 'eventlist'),
                        'thursday' => __('Thursday', 'eventlist'),
                        'friday' => __('Friday', 'eventlist'),
                        'saturday' => __('Saturday', 'eventlist'),
                        'sunday' => __('Sunday', 'eventlist'),
                     ),
                     'default' => array('saturday', 'sunday'),
                  ),

                  array(
                     'type' => 'select',
                     'label' => __('The first day of week', 'eventlist'),
                     'name' => 'first_day_of_week',
                     'atts' => array(
                        'id' => 'first_day_of_week',
                        'class' => 'first_day_of_week',
                     ),
                     'options' => array(
                        '1' => __('Monday', 'eventlist'),
                        '2' => __('Tuesday', 'eventlist'),
                        '3' => __('Wednesday', 'eventlist'),
                        '4' => __('Thursday', 'eventlist'),
                        '5' => __('Friday', 'eventlist'),
                        '6' => __('Saturday', 'eventlist'),
                        '0' => __('Sunday', 'eventlist'),
                     ),
                     'default' => '1',
                  ),

               )
            )

      );

   }

   public function el_admin_setting_fields_currency_event(){

      // Currency
      $currency_code_options = el_get_currencies();

      if ($currency_code_options) {
         foreach ($currency_code_options as $code => $name) {
            $currency_code_options[$code] = $name . ' (' . el_get_currency_symbol($code) . ')';
         }
      }

      return array(

            'title' => esc_html__('Currency options', 'eventlist'),
            array(
               'fields' => array(

                  array(
                     'type' => 'select',
                     'label' => esc_html__('Currency', 'eventlist'),
                     'desc' => esc_html__('Choosing currency in your country', 'eventlist'),
                     'atts' => array(
                        'id' => 'currency',
                        'class' => 'currency',
                     ),
                     'name' => 'currency',
                     'options' => $currency_code_options,
                     'default' => 'USD',
                  ),

                  array(
                     'type' => 'select',
                     'label' => esc_html__('Currency Position', 'eventlist'),
                     'desc' => esc_html__('Control the position of the currency symbol', 'eventlist'),
                     'atts' => array(
                        'id' => 'currency_position',
                        'class' => 'currency_position',
                     ),
                     'name' => 'currency_position',
                     'options' => array(
                        'left' => esc_html__('Left', 'eventlist'),
                        'right' => esc_html__('Right', 'eventlist'),
                        'left_space' => esc_html__('Left with space', 'eventlist'),
                        'right_space' => esc_html__('Right with space', 'eventlist'),
                     ),
                     'default' => 'left',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Thousand Separator', 'eventlist'),
                     'desc' => '',
                     'name' => 'thousand_separator',
                     'default' => ',',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Decimal Separator', 'eventlist'),
                     'desc' => '',
                     'name' => 'decimal_separator',
                     'default' => '.',
                  ),

                  array(
                     'type' => 'input',
                     'label' => esc_html__('Number of Decimals', 'eventlist'),
                     'desc' => '',
                     'atts' => array(
                        'id' => 'number_decimals',
                        'class' => 'number_decimals',
                        'placeholder' => '2',
                        'type' => 'number',
                     ),
                     'name' => 'number_decimals',
                     'default' => '2',
                  ),

                  
               ),
            )
        

      );

   }

   public function el_admin_setting_fields_cron_event(){
      return array(
         
            'title' => __('Cron Job', 'eventlist'),
            array(
               'fields' => array(

                  array(
                     'type' => 'hidden',
                     'label' => '',
                     'desc' => '',
                     'name' => 'event_status_first_time',
                     'default' => '',
                     'atts' => array(
                        'id' => 'event_status_first_time',
                     ),
                  ),

                  array(
                     'type' => 'multiradio',
                     'label' => __( 'Choose type', 'eventlist' ),
                     'name' => 'event_cron_job_type',
                     'default' => 'automatic',
                     'options' => array(
                        'automatic' => __( 'Automatic', 'eventlist' ),
                        'manually' => __( 'Manually', 'eventlist' ),
                     ),
                  ),
                  array(
                     'type' => 'select_key',
                     'label' => esc_html__('Update status event', 'eventlist'),
                     'desc' => esc_html__('How often the event should subsequently recur.', 'eventlist'),
                     'atts' => array(
                        'id' => 'schedule_event_status',
                        'class' => 'schedule_event_status',
                     ),
                     'name' => 'schedule_event_status',
                     'options' => array(
                        'hourly' => __( 'Hourly', 'eventlist' ),
                        'twicedaily' => __( 'Twicedaily', 'eventlist' ),
                        'daily' => __( 'Daily', 'eventlist' ),
                        'weekly' => __( 'Weekly', 'eventlist' ),
                     ),
                     'default' => 'hourly',
                  ),
               )
            )

      );
   }


}

$GLOBALS['general_settings'] = new EL_Setting_General();