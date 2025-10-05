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

class EL_Elementor_Location_Event_Thumb extends EL_Abstract_Elementor {

	protected $name 	= 'el_location_event_thumb';
	protected $title 	= 'Location Event Thumbnail';
	protected $icon 	= 'eicon-gallery-grid';

	public function get_title(){
		return __('Location Event Thumbnail', 'eventlist');
	}

	protected function register_controls() {

		$args = array(
			'taxonomy' => 'event_loc',
			'orderby' => 'name',
			'order' => 'ASC'
		);

		$locations = get_terms($args);
		$loc_array = array();
		if ($locations) {
			foreach ( $locations as $loc ) {
				$loc_array[$loc->term_id] = $loc->name;
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
				'label' => esc_html__( 'Columns', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'four_columns',
				'options' => [
					'two_columns' => esc_html__( '2', 'eventlist' ),
					'three_columns' => esc_html__( '3', 'eventlist' ),
					'four_columns'  => esc_html__( '4', 'eventlist' ),
				],
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
				],
				
			]
		);


		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'location',
			[
				'label' => __( 'Location', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'options' => $loc_array,
			]
		);

		$repeater->add_control(
			'image',
			[
				'label'   => __( 'Image', 'eventlist' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'custom_link',
			[
				'label' => __( 'Redirect link', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'eventlist' ),
					'half_map' => __( 'Half Map', 'eventlist' ),
				]
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __( 'Link', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'eventlist' ),
				'show_external' => false,
				'description' => esc_html__( 'You should insert half map page', 'eventlist' ),
				'default' => [
					'url' => '#',
				],
				'condition' => [
					'custom_link' => 'half_map',
				],
			]
		);


		$this->add_control(
			'tabs',
			[
				'label'       => __( 'Items', 'eventlist' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
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
						'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
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
								'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue' => 'background: {{VALUE}}',
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
								'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue:hover' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/* Image */
		$this->start_controls_section(
				'image_style_section',
				[
					'label' => esc_html__( 'Image', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'image_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-media a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
						'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .venue-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'name_typography',
					'selector' => '{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .venue-name a',
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
								'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .venue-name a' => 'color: {{VALUE}};',
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
								'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .venue-name a:hover' => 'color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

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
						'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .count-event' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'event_num_typography',
					'selector' => '{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .count-event',
				]
			);

			$this->add_control(
				'event_num_color',
				[
					'label' => esc_html__( 'Color', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .el-event-venue-thumb .el-event-venue-thumb-grid .item-venue .el-content .count-event' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_location_thumb', 'elementor/event_location_thumb.php' );
		
		el_get_template( $template, $settings );

		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Location_Event_Thumb() );
