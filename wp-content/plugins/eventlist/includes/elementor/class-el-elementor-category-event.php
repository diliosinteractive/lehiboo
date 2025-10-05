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

class EL_Elementor_Category_Event extends EL_Abstract_Elementor {

	protected $name 	= 'el_category_event';
	protected $title 	= 'Category Event';
	protected $icon 	= 'eicon-info-box';

	
	public function get_title(){
		return __('Event Category', 'eventlist');
	}
	
	protected function register_controls() {

		$args = array(
			'taxonomy' 		=> 'event_cat',
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'hide_empty' 	=> false,
		);

		$categories = get_terms($args);
		$cate_array = array();
		if ($categories) {
			foreach ( $categories as $cate ) {
				$cate_array[$cate->slug] = $cate->name;
			}
		}


		$this->start_controls_section(
			'section_setting',
			[
				'label' => __( 'Settings', 'eventlist' ),
			]
		);


		$this->add_control(
			'type',
			[
				'label'   => __( 'Type', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => __('Icon', 'eventlist'),
					'image' => __('Image', 'eventlist'),
				],
			]
		);

		$this->add_control(
			'icon',
			[
				'label'   => __( 'Class Icons', 'eventlist' ),
				'type'    => Controls_Manager::ICON,
				'default' => 'fa fa-address-card',
				'condition' => [
					'type' => 'icon',
				]
			]
		);

		$this->add_control(
			'image',
			[
				'label'   => __( 'Image', 'eventlist' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'type' => 'image',
				]
			]
		);


		$this->add_control(
			'category',
			[
				'label' => __( 'Category', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'options' => $cate_array,
			]
		);

		$this->add_control(
			'search_result',
			[
				'label' => __( 'Search results page', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'eventlist' ),
					'half_map' => __( 'Half Map', 'eventlist' ),
				]
			]
		);

		$this->add_control(
			'filter_event',
			[
				'label' => __( 'Filter events', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => [
					'upcoming' => __( 'Upcoming', 'eventlist' ),
					'selling' => __( 'Selling', 'eventlist' ),
					'closed'  => __( 'Closed', 'eventlist' ),
					'feature'  => __( 'Featured', 'eventlist' ),
					'all' => __( 'All', 'eventlist' ),
				],
				'condition' => [
					'search_result' => 'default',
				],
			]
		);

		$this->add_control(
			'link_result',
			[
				'label' => __( 'Link', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'eventlist' ),
				'show_external' => false,
				'default' => [
					'url' => '#',
				],
				'condition' => [
					'search_result' => 'half_map',
				],
			]
		);

		$this->add_control(
			'show_count_event',
			[
				'label' => __( 'Show number event', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'eventlist' ),
					'no' => __( 'No', 'eventlist' ),
				]
			]
		);

		$this->add_control(
			'size_icon',
			[
				'label' => __( 'Icon Size', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .el-event-category .el-media i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'type' => 'icon',
				]
			]
		);

		$this->add_control(
			'width_img',
			[
				'label' => __( 'Width Image', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .el-event-category .el-media img' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'type' => 'image',
				]
			]
		);

		$this->add_control(
			'height_img',
			[
				'label' => __( 'Height Image', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .el-event-category .el-media img' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'type' => 'image',
				]
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_category', 'elementor/event_category.php' );

	
		
		el_get_template( $template, $settings );
		

	}

}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Category_Event() );
