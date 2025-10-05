<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Color;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;

class EL_Elementor_Event_Near_Me extends EL_Abstract_Elementor {

	protected $name 	= 'ova_event_near_me';
	protected $title 	= 'Event Near Me';
	protected $icon 	= 'eicon-map-pin';

	
	public function get_title(){
		return __('Event Near Me', 'eventlist');
	}

	public function get_script_depends() {
		/* Google Maps */
		if( EL()->options->general->get('event_google_key_map') ){
			$map_language = apply_filters( 'el_google_map_language', 'en' );
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?key='.EL()->options->general->get('event_google_key_map').'&libraries=geometry,places&callback=Function.prototype&language='.$map_language, array('jquery'), false, true);
		}else{
			$map_language = apply_filters( 'el_google_map_language', 'en' );
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry,places&callback=Function.prototype&language='.$map_language, array('jquery'), false, true);
		}
		wp_enqueue_style( 'baron-style', EL_PLUGIN_URI.'assets/libs/baron/baron.css' );
		wp_enqueue_script( 'baron-script', EL_PLUGIN_URI.'assets/libs/baron/baron.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
	}

	protected function get_event_categories(){
		$args = array(
			'taxonomy' 		=> 'event_cat',
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'hide_empty' 	=> false,
		);

		$categories = get_terms($args);
		$cate_array = array();
		if ( $categories ) {
			foreach ( $categories as $cate ) {
				$cate_array[$cate->term_id] = esc_html( $cate->name );
			}
		}
		return $cate_array;
	}
	
	protected function register_controls() {


		$this->start_controls_section(
			'section_setting',
			[
				'label' => __( 'Settings', 'eventlist' ),
			]
		);

		$this->add_control(
			'filter_event',
			[
				'label' => __( 'Filter events', 'eventlist' ),
				'type' 	=> \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' 				=> __( 'All', 'eventlist' ),
					'upcoming' 			=> __( 'Upcoming', 'eventlist' ),
					'selling' 			=> __( 'Selling', 'eventlist' ),
					'upcoming_selling' 	=> __( 'Upcoming & Selling', 'eventlist' ),
					'closed' 			=> __( 'Closed', 'eventlist' ),
					'feature' 			=> __( 'Featured', 'eventlist' ),
					
				],
			]
		);
		

		$this->add_control(
			'total_count',
			[
				'label' 	=> __( 'Total', 'eventlist' ),
				'type' 		=> \Elementor\Controls_Manager::NUMBER,
				'default' 	=> 5,
			]
		);

		$this->add_control(
			'order_by',
			[
				'label' => __( 'Order by', 'eventlist' ),
				'type' 	=> \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' 			=> __( 'Date', 'eventlist' ),
					'id' 			=> __( 'ID', 'eventlist' ),
					'title' 		=> __( 'Title', 'eventlist' ),
					'start_date' 	=> __( 'Start Date', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'eventlist' ),
				'type' 	=> \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' 	=> __( 'Ascending', 'eventlist' ),
					'DESC'  => __( 'Descending', 'eventlist' ),
				],
			]
		);

		
		$this->add_control(
			'heading_setting_post',
			[
				'label' => __( 'Template', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);




			$this->add_control(
				'title',
				[
					'label' 		=> esc_html__( 'Title', 'eventlist' ),
					'type' 			=> \Elementor\Controls_Manager::TEXT,
					'default' 		=> esc_html__( 'Events Near Me', 'eventlist' ),
					'placeholder' 	=> esc_html__( 'Type your title here', 'eventlist' ),
				]
			);

			$this->add_control(
				'type_event',
				[
					'label' => __('Type Event', 'eventlist'),
					'type' 	=> Controls_Manager::SELECT,
					'default' => 'type1',
					'options' => [
						'type1' => __( 'Type 1', 'eventlist' ),
						'type2' => __( 'Type 2', 'eventlist' ),
						'type4' => __( 'Type 4', 'eventlist' ),
						'type5' => __( 'Type 5', 'eventlist' ),
						'type6' => __( 'Type 6', 'eventlist' ),
					]
				]
			);

			$this->add_control(
				'column',
				[
					'label' => __( 'Column', 'eventlist' ),
					'type' 	=> \Elementor\Controls_Manager::SELECT,
					'default' => 'three_column',
					'options' => [
						'two_column' 	=> __( '2 Columns', 'eventlist' ),
						'three_column' 	=> __( '3 Columns', 'eventlist' ),
					],
				]
			);


			

			$this->add_control(
				'show_button',
				[
					'label' 		=> esc_html__( 'Show Choose Location Button', 'eventlist' ),
					'type' 			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on' 		=> esc_html__( 'Show', 'eventlist' ),
					'label_off' 	=> esc_html__( 'Hide', 'eventlist' ),
					'return_value' 	=> 'yes',
					'default' 		=> 'no',
				]
			);

			$this->add_control(
				'radius',
				[
					'label' 	=> esc_html__( 'Radius (meter)', 'eventlist' ),
					'description' => esc_html__( 'Search around X meters', 'eventlist' ),
					'type' 		=> \Elementor\Controls_Manager::NUMBER,
					'default' 	=> 5000,
				]
			);

			$this->add_control(
				'filter_options',
				[
					'label' 	=> esc_html__( 'Filter Navigation', 'eventlist' ),
					'type' 		=> \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_control(
					'show_filter',
					[
						'label' 		=> esc_html__( 'Show Filter', 'eventlist' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'eventlist' ),
						'label_off' 	=> esc_html__( 'Hide', 'eventlist' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'time_categories',
					[
						'label' 		=> esc_html__( 'Choose Time', 'eventlist' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT2,
						'label_block' 	=> true,
						'multiple' 		=> true,
						'options' 		=> get_list_event_time_categories(),
						'condition' => [
							'show_filter' => 'yes',
						],
					]
				);

				$this->add_control(
					'event_categories',
					[
						'label' 		=> esc_html__( 'Event Categories', 'eventlist' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT2,
						'label_block' 	=> true,
						'multiple' 		=> true,
						'options' 		=> $this->get_event_categories(),
						'condition' => [
							'show_filter' => 'yes',
						],
					]
				);
		

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_near_me', 'elementor/event_near_me.php' );

	
		
		el_get_template( $template, $settings );
		
	
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Near_Me() );
