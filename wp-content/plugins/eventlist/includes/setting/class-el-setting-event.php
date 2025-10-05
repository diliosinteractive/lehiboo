<?php
if (!defined('ABSPATH')) {
	exit();
}

class EL_Setting_Event extends EL_Abstract_Setting{
	/**
     * setting id
     * @var string
     */
	public $_id = 'event';

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
		$this->_title = __('Event', 'eventlist');

		add_filter( 'el_admin_setting_fields', array( $this, 'el_generate_fields_event' ), 10, 2 );

		parent::__construct();
	}


	public function el_generate_fields_event( $groups, $id="booking" ) {

      if( $id == 'event' ){

        $groups[$id . '_archive_event'] = apply_filters( 'el_admin_setting_fields_archive_event', $this->el_admin_setting_fields_archive_event(), $this->id );

        $groups[$id . '_single_event'] = apply_filters( 'el_admin_setting_fields_single_event', $this->el_admin_setting_fields_single_event(), $this->id );


      }

        return $groups;

   }

   public function el_admin_setting_fields_archive_event(){
   		return array(
        'title' => __('Archive Event Settings', 'eventlist'),
        'desc'	=> __('This settings used for:<br/> 
                  	Event List page: https://your-domain.com/event<br/>
                  	Category page: https://your-domain.com/event_cat/business<br/>
                  	Search Page', 'eventlist').'<br/><br/>',
         array(
         	'fields' => array(

         		

         		array(
					'type' => 'select',
					'label' => __( 'Filter events', 'eventlist' ),
					'desc' => __( 'Show events under these conditions', 'eventlist' ),
					'name' => 'filter_events',
					'options' => array(
						'all' => __( 'All events', 'eventlist' ),
						'upcoming' => __( 'Upcoming events', 'eventlist' ),
						'opening_upcoming' => __( 'Opening & Upcoming events', 'eventlist' ),
						'opening' => __( 'Opening events', 'eventlist' ),
						'past' => __( 'Past events', 'eventlist' ),
					),
					'default' => 'all'
				),


				array(
					'type' => 'select',
					'label' => __( 'Type', 'eventlist' ),
					'desc' => __( 'Choose template of event card', 'eventlist' ),
					'name' => 'archive_type',
					'options' => array(
						'type1' => __( 'Type 1', 'eventlist' ),
						'type2' => __( 'Type 2', 'eventlist' ),
						'type3' => __( 'Type 3', 'eventlist' ),
						'type4' => __( 'Type 4', 'eventlist' ),
						'type5' => __( 'Type 5', 'eventlist' ),
						'type6' => __( 'Type 6', 'eventlist' ),
					),
					'default' => 'type1'
				),
				array(
					'type' => 'select',
					'label' => __( 'Column', 'eventlist' ),
					'desc' => __( 'event number per row', 'eventlist' ),
					'name' => 'archive_column',
					'options' => array(
						'two-column' => __( '2 columns', 'eventlist' ),
						'three-column' => __( '3 columns', 'eventlist' ),
						
					),
					'default' => 'three-colum'
				),

				
				array(
					'type' => 'select',
					'label' => __( 'Display Price', 'eventlist' ),
					'desc' => __( 'Display in event card', 'eventlist' ),
					'name' => 'display_price_opt',
					'options' => array(
						'min' => __( 'Min', 'eventlist' ),
						'max' => __( 'Max', 'eventlist' ),
						'min-max' => __( 'Min to Max', 'eventlist' ),
					),
					'default' => 'min'
				),

				array(
					'type' => 'select',
					'label' => __( 'Display Date', 'eventlist' ),
					'desc' => __( 'Display in event card', 'eventlist' ),
					'name' => 'display_date_opt',
					'options' => array(
						'start' => __( 'Start Date', 'eventlist' ),
						'start_end' => __( 'Start - End Date', 'eventlist' ),
					),
					'default' => 'start'
				),

				array(
					'type' 	=> 'select',
					'label' => __( 'Display Image', 'eventlist' ),
					'desc' 	=> __( 'Display in event card', 'eventlist' ),
					'name' 	=> 'display_image_opt',
					'options' => array(
						'thumbnail' => __( 'Thumbnail', 'eventlist' ),
						'slider' 		=> __( 'Slider', 'eventlist' ),
					),
					'default' => 'thumbnail'
				),

				array(
					'type' => 'select',
					'label' => __( 'Order By', 'eventlist' ),
					'desc' => __( 'Order by these condition', 'eventlist' ),
					'atts' => array(
						'id' => 'archive_order_by',
						'class' => 'archive_order_by'
					),
					'name' => 'archive_order_by',
					'options' => array(
						'title' => __( 'Title', 'eventlist' ),
						'ID' => __( 'ID', 'eventlist' ),
						'start_date' => __( 'Start Date', 'eventlist' ),
						'end_date' => __( 'End Date', 'eventlist' ),
						'near' => __( 'Nearest', 'eventlist' ),
						'date_desc' => __( 'Newest First', 'eventlist' ),
						'date_asc' => __( 'Oldest First', 'eventlist' ),
					),
					'default' => 'title'
				),

				array(
					'type' => 'select',
					'label' => __( 'Order', 'eventlist' ),
					'desc' => __( 'Arrange an Ascending or Descending of events with the above conditions', 'eventlist' ),
					'atts' => array(
						'id' => 'archive_order',
						'class' => 'archive_order'
					),
					'name' => 'archive_order',
					'options' => array(
						'ASC' => __( 'Ascending', 'eventlist' ),
						'DESC' => __( 'Descending', 'eventlist' )
					),
					'default' => 'DESC'
				),

				

				array(
					'type' => 'input',
					'label' => __( 'Events per page', 'eventlist' ),
					'desc' => __( 'If the number of events is greater than this value, the navigation bar appears', 'eventlist' ),
					'atts' => array(
						'id'          => 'listing_posts_per_page',
						'class'       => 'listing_posts_per_page',
						'type'        => 'number',
						'placeholder' => '12',
					),
					'name' => 'listing_posts_per_page',
					'default'	=> '12'
				),
				
				
				
				array(
					'type' => 'input',
					'label' => __( 'Latitude Map default', 'eventlist' ),
					'desc' => __( 'The default latitude of map when the event do not exist', 'eventlist' ),
					'atts' => array(
						'id'          => 'latitude_map_default',
						'class'       => 'latitude_map_default',
						'type'        => 'text',
						'placeholder' => '39.177972',
					),
					'name' => 'latitude_map_default',
					'default' => '39.177972'
				),

				array(
					'type' => 'input',
					'label' => __( 'Longitude Map default', 'eventlist' ),
					'desc' => __( 'The default longitude of map when the event do not exist', 'eventlist' ),
					'atts' => array(
						'id'          => 'longitude_map_default',
						'class'       => 'longitude_map_default',
						'type'        => 'text',
						'placeholder' => '-100.363750',
					),
					'name' => 'longitude_map_default',
					'default' => '-100.363750'
				),

				array(
					'type' => 'select',
					'label' => __( 'Show Time', 'eventlist' ),
					'desc' => __( 'Display in event card. Example: 06:am, 10:00', 'eventlist' ),
					'name' => 'show_hours_archive',
					'options' => array(
						'yes' => __( 'Yes', 'eventlist' ),
						'no' => __( 'No', 'eventlist' )
					),
					'default' => 'yes'
				),
				

			)
         )
     	);
   }


   public function el_admin_setting_fields_single_event(){
   		return array(
        'title' => __('Single Event Settings', 'eventlist'),
         array(
         	'fields' => array(
				array(
					'type' => 'input',
					'label' => __( 'Zoom Map event detail', 'eventlist' ),
					'desc' => '',
					'atts' => array(
						'id'          => 'number_event_related',
						'class'       => 'number_event_related',
						'type'        => 'number',
						'placeholder' => '17',
					),
					'name' => 'event_zoom_map',
					'default' => '17'
				),

				array(
					'type' => 'select',
					'label' => __( 'Show Schema', 'eventlist' ),
					'name' => 'show_schema',
					'options' => array(
						'yes' => __( 'Yes', 'eventlist' ),
						'no' => __( 'No', 'eventlist' ),
					),
					'default' => 'yes'
				),

				array(
					'type' => 'select',
					'label' => __( 'Show Time', 'eventlist' ),
					'desc' => __( '06:am, 10:00', 'eventlist' ),
					'name' => 'show_hours_single',
					'options' => array(
						'yes' => __( 'Yes', 'eventlist' ),
						'no' => __( 'No', 'eventlist' )
						
					),
					'default' => 'yes'
				),

				array(
					'type' => 'select',
					'label' => __( 'Show More Description', 'eventlist' ),
					'name' => 'show_more_desc',
					'options' => array(
						'yes' => __( 'Yes', 'eventlist' ),
						'no' => __( 'No', 'eventlist' )
					),
					'default' => 'yes'
				),

				array(
					'type' => 'input',
					'label' => __( 'Height Description (px)', 'eventlist' ),
					'desc' => __( 'Recommend height: > 300', 'eventlist' ),
					'atts' => array(
						'id'          => 'height_description_show',
						'class'       => 'height_description_show',
						'type'        => 'number',
						'placeholder' => '580',
					),
					'name' => 'height_description_show',
					'default' => '580'
				),

				array(
					'type' => 'input',
					'label' => __( 'Number related event', 'eventlist' ),
					'desc' => '',
					'atts' => array(
						'id'          => 'number_event_related',
						'class'       => 'number_event_related',
						'type'        => 'number',
						'placeholder' => '6',
					),
					'name' => 'number_event_related',
					'default' => '6'
				),

				array(
					'type' => 'select',
					'label' => __( 'Show Remaining Ticket', 'eventlist' ),
					'name' => 'show_remaining_tickets',
					'options' => array(
						'yes' => __( 'Yes', 'eventlist' ),
						'no' => __( 'No', 'eventlist' ),
					),
					'default' => 'yes'
				),


			)
         )
     	);
   }
   

}

$GLOBALS['event_settings'] = new EL_Setting_Event();