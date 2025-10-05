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

class EL_Elementor_Event_Grid extends EL_Abstract_Elementor {

	protected $name 	= 'ova_event_grid';
	protected $title 	= 'Event Grid';
	protected $icon 	= 'eicon-posts-grid';

	
	public function get_title(){
		return __('Event Grid', 'eventlist');
	}

	public function get_script_depends() {
		wp_enqueue_style( 'baron-style', EL_PLUGIN_URI.'assets/libs/baron/baron.css' );
		wp_enqueue_script( 'baron-script', EL_PLUGIN_URI.'assets/libs/baron/baron.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
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
					'label' 		=> __( 'Include Categories', 'eventlist' ),
					'type' 			=> \Elementor\Controls_Manager::TEXT,
					'placeholder' 	=> 'ID category',
					'description' 	=> 'ID category, example: 5, 7'
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
					'label' => __( 'Total events', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5,
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
				'type_event',
				[
					'label' => __('Type Event', 'eventlist'),
					'type' => Controls_Manager::SELECT,
					'default' => 'type1',
					'options' => [
						'type1' => __('Type 1', 'eventlist'),
						'type2' => __('Type 2', 'eventlist'),
						'type3' => __('Type 3', 'eventlist'),
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
						'two_column'  => __( '2 Columns', 'eventlist' ),
						'three_column' => __( '3 Columns', 'eventlist' ),
					],
				]
			);
			
		


		$this->add_control(
			'filter_setting_post',
			[
				'label' => __( 'Filter Navigation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

			$this->add_control(
				'show_filter',
				[
					'label' 		=> __( 'Show Filter Navigation', 'eventlist' ),
					'type' 			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on' 		=> __( 'Show', 'eventlist' ),
					'label_off' 	=> __( 'Hide', 'eventlist' ),
					'return_value' 	=> 'yes',
					'default' 		=> 'yes',
				]
			);

			$this->add_responsive_control(
				'filter_align',
				[
					'label' => __( 'Filter Alignment', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'label_block' => false,
					'options' => [
						'left' => [
							'title' => esc_html__( 'Left', 'eventlist' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => esc_html__( 'Center', 'eventlist' ),
							'icon' => 'eicon-text-align-center',
						],
						'right' => [
							'title' => esc_html__( 'Right', 'eventlist' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'default' => 'center',
					'selectors' => [
						'{{WRAPPER}} .ova-event-grid .el-button-filter' => 'text-align: {{VALUE}}',
					],
					'toggle' => false,
					'condition' => [
						'show_filter' => 'yes',
					],
				]
			);
			

			$this->add_control(
				'show_all',
				[
					'label' 		=> __( 'Show "All" in filter', 'eventlist' ),
					'type' 			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on' 		=> __( 'Show', 'eventlist' ),
					'label_off' 	=> __( 'Hide', 'eventlist' ),
					'return_value' 	=> 'yes',
					'default' 		=> 'yes',
					'condition' => [
						'show_filter' => 'yes',
					],
				]
			);

		
		

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_grid', 'elementor/event_grid.php' );

		
		
		el_get_template( $template, $settings );
		
	
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Grid() );
