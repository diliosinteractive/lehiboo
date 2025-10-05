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

class EL_Elementor_Category_Event_Grid extends EL_Abstract_Elementor {

	protected $name 	= 'el_category_event_grid';
	protected $title 	= 'Category Event Grid';
	protected $icon 	= 'eicon-posts-grid';

	
	public function get_title(){
		return __('Event Category Grid', 'eventlist');
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
			'column',
			[
				'label' => __( 'Column', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'four_columns',
				'options' => [
					'two_columns'  	=> __( '2', 'eventlist' ),
					'three_columns' => __( '3', 'eventlist' ),
					'four_columns' 	=> __( '4', 'eventlist' ),
				],
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

		$this->add_control(
			'list',
			[
				'label' => esc_html__( 'Items', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
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
				'items_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-grid .el-event-category-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'items_border',
					'selector' => '{{WRAPPER}} .el-event-category-grid .el-event-category-item',
				]
			);

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
				'icon_padding',
				[
					'label' => esc_html__( 'Padding', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
						'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media i' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs(
					'icon_style_tabs'
				);

				$this->start_controls_tab(
						'icon_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'eventlist' ),
						]
					);

					$this->add_control(
						'icon_color',
						[
							'label' => esc_html__( 'Color', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media svg' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media svg path' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media i' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'icon_bg_color',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .el-media' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'icon_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'eventlist' ),
						]
					);

					$this->add_control(
						'icon_color_hover',
						[
							'label' => esc_html__( 'Color', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .el-media svg' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .el-media svg path' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .el-media i' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'icon_bg_color_hover',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .el-media' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

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
				'name_padding',
				[
					'label' => esc_html__( 'Padding', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-category-grid .el-event-category-item .cate-name' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'name_typography',
					'selector' => '{{WRAPPER}} .el-event-category-grid .el-event-category-item .cate-name',
				]
			);

			$this->start_controls_tabs(
					'name_style_tabs'
				);

				$this->start_controls_tab(
						'name_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'eventlist' ),
						]
					);

					$this->add_control(
						'name_color',
						[
							'label' => esc_html__( 'Color', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .cate-name' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'name_bg_color',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item .cate-name' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'name_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'eventlist' ),
						]
					);

					$this->add_control(
						'name_color_hover',
						[
							'label' => esc_html__( 'Color', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .cate-name' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'name_bg_color_hover',
						[
							'label' => esc_html__( 'Background', 'eventlist' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .el-event-category-grid .el-event-category-item:hover .cate-name' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_category_grid', 'elementor/event_category_grid.php' );

		
		el_get_template( $template, $settings );
		
	

		
	}

}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Category_Event_Grid() );
