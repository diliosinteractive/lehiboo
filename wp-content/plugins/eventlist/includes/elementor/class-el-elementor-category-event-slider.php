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

class EL_Elementor_Category_Event_Slider extends EL_Abstract_Elementor {

	protected $name 	= 'el_category_event_slider';
	protected $title 	= 'Category Event Slider';
	protected $icon 	= 'eicon-post-slider';

	
	public function get_title(){
		return __('Event Category Slider', 'eventlist');
	}

	public function get_script_depends() {
		wp_enqueue_style( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.css' );
		wp_enqueue_script( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'meupicon-lamp',
					'library' => 'all',
				],
			]
		);

		$repeater->add_control(
			'category',
			[
				'label' => __( 'Category', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'options' => $cate_array,
			]
		);

		$repeater->add_control(
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

		$repeater->add_control(
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

		$repeater->add_control(
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

		$repeater->add_control(
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

		$repeater->add_control(
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
			'list',
			[
				'label' => esc_html__( 'Items', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_additional_options',
			[
				'label' => esc_html__( 'Additional Options', 'eventlist' ),
			]
		);

		/***************************  VERSION 1 ***********************/
			$this->add_control(
				'margin_items',
				[
					'label'   => esc_html__( 'Margin Right Items', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::NUMBER,
					'default' => 43,
				]
				
			);

			$this->add_control(
				'item_number',
				[
					'label'       => esc_html__( 'Item Number', 'eventlist' ),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'description' => esc_html__( 'Number Item', 'eventlist' ),
					'default'     => 5,
				]
			);

			$this->add_control(
				'slides_to_scroll',
				[
					'label'       => esc_html__( 'Slides to Scroll', 'eventlist' ),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'eventlist' ),
					'default'     => 1,
				]
			);

			$this->add_control(
				'pause_on_hover',
				[
					'label'   => esc_html__( 'Pause on Hover', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'options' => [
						'yes' => esc_html__( 'Yes', 'eventlist' ),
						'no'  => esc_html__( 'No', 'eventlist' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'infinite',
				[
					'label'   => esc_html__( 'Infinite Loop', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'options' => [
						'yes' => esc_html__( 'Yes', 'eventlist' ),
						'no'  => esc_html__( 'No', 'eventlist' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label'   => esc_html__( 'Autoplay', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'options' => [
						'yes' => esc_html__( 'Yes', 'eventlist' ),
						'no'  => esc_html__( 'No', 'eventlist' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'autoplay_speed',
				[
					'label'     => esc_html__( 'Autoplay Speed', 'eventlist' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 3000,
					'step'      => 500,
					'condition' => [
						'autoplay' => 'yes',
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'smartspeed',
				[
					'label'   => esc_html__( 'Smart Speed', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
				]
			);

			$this->add_control(
				'dot_control',
				[
					'label'   => esc_html__( 'Show Dots', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'no',
					'options' => [
						'yes' => esc_html__( 'Yes', 'eventlist' ),
						'no'  => esc_html__( 'No', 'eventlist' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'nav_control',
				[
					'label'   => esc_html__( 'Show Nav', 'eventlist' ),
					'type'    => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'options' => [
						'yes' => esc_html__( 'Yes', 'eventlist' ),
						'no'  => esc_html__( 'No', 'eventlist' ),
					],
					'frontend_available' => true,
				]
			);

		$this->end_controls_section();

		/* Items */
		$this->start_controls_section(
				'items_style_section',
				[
					'label' => esc_html__( 'Items', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'items_padding',
				[
					'label' => esc_html__( 'Padding', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'items_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'items_border',
					'selector' => '{{WRAPPER}} .el-event-category-slider .el-event-category-item',
				]
			);

			$this->start_controls_tabs(
					'items_style_tabs'
				);

				$this->start_controls_tab(
						'items_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'eventlist' ),
						]
					);

					$this->add_control(
						'items_bg_color',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-slider .el-event-category-item' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'items_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'eventlist' ),
						]
					);

					$this->add_control(
						'items_bg_color_hover',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-slider .el-event-category-item:hover' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/* Icon */
		$this->start_controls_section(
				'icon_style_section',
				[
					'label' => esc_html__( 'Icon', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'icon_margin',
				[
					'label' => esc_html__( 'Margin', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Size', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 5,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media i' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'icon_color',
				[
					'label' => esc_html__( 'Color', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media svg' => 'fill: {{VALUE}};',
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media svg path' => 'fill: {{VALUE}};',
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .el-media i' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

		/* Name */
		$this->start_controls_section(
				'name_style_section',
				[
					'label' => esc_html__( 'Name', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'name_margin',
				[
					'label' => esc_html__( 'Margin', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .cate-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'name_typography',
					'selector' => '{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .cate-name',
				]
			);

			$this->add_control(
				'name_color',
				[
					'label' => esc_html__( 'Color', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .cate-name' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

		/* Event Number */
		$this->start_controls_section(
				'event_num_style_section',
				[
					'label' => esc_html__( 'Event Number', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'event_num_margin',
				[
					'label' => esc_html__( 'Margin', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .count-event' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'event_num_typography',
					'selector' => '{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .count-event',
				]
			);

			$this->add_control(
				'event_num_color',
				[
					'label' => esc_html__( 'Color', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .el-event-category-slider .el-event-category-item .content-cat .count-event' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_category_slider', 'elementor/event_category_slider.php' );

	
		
		el_get_template( $template, $settings );
		
	
		
	}

}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Category_Event_Slider() );
