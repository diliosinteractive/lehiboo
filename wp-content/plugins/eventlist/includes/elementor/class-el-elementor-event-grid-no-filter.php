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

class EL_Elementor_Event_Grid_No_Filter extends EL_Abstract_Elementor {

	protected $name 	= 'ova_event_grid_no_filter';
	protected $title 	= 'Event Grid No Filter';
	protected $icon 	= 'eicon-posts-grid';

	
	public function get_title(){
		return __('Event Grid No Filter', 'eventlist');
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'section_setting',
			[
				'label' => __( 'Settings', 'eventlist' ),
			]
		);

		$this->add_control(
			'include_cat',
			[
				'label' => __( 'Include Categories', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'ID category',
				'description' => 'ID category, example: 5, 7'
			]
		);

		$this->add_control(
			'filter_event',
			[
				'label' => __( 'Filter events', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
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
				'label' 	=> __( 'Total events', 'eventlist' ),
				'type' 		=> \Elementor\Controls_Manager::NUMBER,
				'default' 	=> 5,
			]
		);

		$this->add_control(
			'order_by',
			[
				'label' => __( 'Order by', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' 			=> __( 'Date', 'eventlist' ),
					'id'  			=> __( 'ID', 'eventlist' ),
					'title' 		=> __( 'Title', 'eventlist' ),
					'start_date' 	=> __( 'Start Date', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
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
			'type_event',
			[
				'label' => __('Type Event', 'eventlist'),
				'type' => Controls_Manager::SELECT,
				'default' => 'type1',
				'options' => [
					'type1' => __('Type 1', 'eventlist'),
					'type2' => __('Type 2', 'eventlist'),
					'type4' => __('Type 4', 'eventlist'),
					'type5' => __('Type 5', 'eventlist'),
					'type6' => __('Type 6', 'eventlist'),
				]
			]
		);

		$this->add_control(
			'display_img',
			[
				'label' => esc_html__( 'Display thumbnail', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'thumbnail',
				'options' => [
					'thumbnail' => esc_html__( 'Image', 'eventlist' ),
					'slider' 	=> esc_html__( 'Slider', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'column',
			[
				'label' => __( 'Column', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'three_column',
				'options' => [
					'two_column'  	=> __( '2 Columns', 'eventlist' ),
					'three_column' 	=> __( '3 Columns', 'eventlist' ),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_grid_no_filter', 'elementor/event_grid_no_filter.php' );

	
		
		el_get_template( $template, $settings );
		


		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Grid_No_Filter() );
