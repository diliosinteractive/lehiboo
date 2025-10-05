<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

class EL_Elementor_Event_Slideshow extends EL_Abstract_Elementor {

	protected $name 	= 'el_event_slideshow';
	protected $title 	= 'Event Slideshow';
	protected $icon 	= 'eicon-post-slider';

	
	public function get_title(){
		return __('Event Slideshow', 'eventlist');
	}

	public function get_script_depends() {
		wp_enqueue_style( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.css' );
		wp_enqueue_script( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
	}
	
	protected function register_controls() {

		$terms = get_terms( array(
			'taxonomy' => 'event_cat',
			'post_type' => 'event',
			'hide_empty' => false,
		) );

		$categories = [];
		foreach ($terms as $value) {
			$categories[$value->slug] = $value->name;
		}

		$animation_array = array(
			'bounce'  => 'bounce',
			'flash'  => 'flash',
			'pulse'  => 'pulse',
			'rubberBand'  => 'rubberBand',
			'shake'  => 'shake',
			'swing'  => 'swing',
			'tada'  => 'tada',
			'wobble'  => 'wobble',
			'jello'  => 'jello',
			'bounceIn'  => 'bounceIn',
			'bounceInDown'  => 'bounceInDown',
			'bounceInLeft'  => 'bounceInLeft',
			'bounceInRight'  => 'bounceInRight',
			'bounceInUp'  => 'bounceInUp',
			'bounceOut'  => 'bounceOut',
			'bounceOutDown'  => 'bounceOutDown',
			'bounceOutLeft'  => 'bounceOutLeft',
			'bounceOutRight'  => 'bounceOutRight',
			'bounceOutUp'  => 'bounceOutUp',
			'fadeIn'  => 'fadeIn',
			'fadeInDown'  => 'fadeInDown',
			'fadeInDownBig'  => 'fadeInDownBig',
			'fadeInLeft'  => 'fadeInLeft',
			'fadeInLeftBig'  => 'fadeInLeftBig',
			'fadeInRight'  => 'fadeInRight',
			'fadeInRightBig'  => 'fadeInRightBig',
			'fadeInUp'  => 'fadeInUp',
			'fadeInUpBig'  => 'fadeInUpBig',
			'fadeOut'  => 'fadeOut',
			'fadeOutDown'  => 'fadeOutDown',
			'fadeOutDownBig'  => 'fadeOutDownBig',
			'fadeOutLeft'  => 'fadeOutLeft',
			'fadeOutLeftBig'  => 'fadeOutLeftBig',
			'fadeOutRight'  => 'fadeOutRight',
			'fadeOutRightBig'  => 'fadeOutRightBig',
			'fadeOutUp'  => 'fadeOutUp',
			'fadeOutUpBig'  => 'fadeOutUpBig',
			'flip'  => 'flip',
			'flipInX'  => 'flipInX',
			'flipInY'  => 'flipInY',
			'flipOutX'  => 'flipOutX',
			'flipOutY'  => 'flipOutY',
			'lightSpeedIn'  => 'lightSpeedIn',
			'lightSpeedOut'  => 'lightSpeedOut',
			'rotateIn'  => 'rotateIn',
			'rotateInDownLeft'  => 'rotateInDownLeft',
			'rotateInDownRight'  => 'rotateInDownRight',
			'rotateInUpLeft'  => 'rotateInUpLeft',
			'rotateInUpRight'  => 'rotateInUpRight',
			'rotateOut'  => 'rotateOut',
			'rotateOutDownLeft'  => 'rotateOutDownLeft',
			'rotateOutDownRight'  => 'rotateOutDownRight',
			'rotateOutUpLeft'  => 'rotateOutUpLeft',
			'rotateOutUpRight'  => 'rotateOutUpRight',
			'slideInUp'  => 'slideInUp',
			'slideInDown'  => 'slideInDown',
			'slideInLeft'  => 'slideInLeft',
			'slideInRight'  => 'slideInRight',
			'slideOutUp'  => 'slideOutUp',
			'slideOutDown'  => 'slideOutDown',
			'slideOutLeft'  => 'slideOutLeft',
			'slideOutRight'  => 'slideOutRight',
			'zoomIn'  => 'zoomIn',
			'zoomInDown'  => 'zoomInDown',
			'zoomInLeft'  => 'zoomInLeft',
			'zoomInRight'  => 'zoomInRight',
			'zoomInUp'  => 'zoomInUp',
			'zoomOut'  => 'zoomOut',
			'zoomOutDown'  => 'zoomOutDown',
			'zoomOutLeft'  => 'zoomOutLeft',
			'zoomOutRight'  => 'zoomOutRight',
			'zoomOutUp'  => 'zoomOutUp',
			'hinge'  => 'hinge',
			'jackInTheBox'  => 'jackInTheBox',
			'rollIn'  => 'rollIn',
			'rollOut'  => 'rollOut'
		);

		$this->start_controls_section(
			'section_slides',
			[
				'label' => __( 'Slides', 'eventlist' ),
			]
		);

			$this->add_control(
				'event_category',
				[
					'label' => __( 'Categories', 'eventlist' ),
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => $categories,
					'default' => '',
				]
			);

			$this->add_control(
				'event_filter',
				[
					'label' => __( 'Filter events', 'eventlist' ),
					'type' => Controls_Manager::SELECT,
					'options' => [
						'' => __( 'All', 'eventlist' ),
						'upcoming_current' => __( 'Upcoming & Current', 'eventlist' ),
						'upcoming' => __( 'Upcoming', 'eventlist' ),
						'current' => __( 'Current', 'eventlist' ),
						'past' => __( 'Past', 'eventlist' ),
					],
					'default' => 'upcoming'
				]
			);

			$this->add_control(
				'event_featured',
				[
					'label' => __( 'Only Featured', 'eventlist' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'eventlist' ),
					'label_off' => __( 'No', 'eventlist' ),
				]
			);


		
			$this->add_control(
				'total_post',
				[
					'label' => __( 'Total', 'eventlist' ),
					'type' => Controls_Manager::NUMBER,
					'min' => -1,
					'max' => 50,
					'step' => 1,
					'default' => 3
				]
			);
			

			$this->add_control(
				'event_orderby',
				[
					'label' => __( 'Order By', 'eventlist' ),
					'type' => Controls_Manager::SELECT,
					'options' => [
						'start_date' => __( 'Start Date', 'eventlist' ),
						'title' => __( 'Title', 'eventlist' ),
					],
					'default' => 'start_date'
				]
			);

			$this->add_control(
				'event_order',
				[
					'label' => __( 'Order', 'eventlist' ),
					'type' => Controls_Manager::SELECT,
					'options' => [
						'ASC' => __( 'Ascending', 'eventlist' ),
						'DESC' => __( 'Descending', 'eventlist' ),
					],
					'default' => 'ASC'
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

			$this->add_responsive_control(
				'slides_height',
				[
					'label' => __( 'Height', 'eventlist' ),
					'type' => Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 100,
							'max' => 1080,
						],
						'vh' => [
							'min' => 10,
							'max' => 100,
						],
					],
					'default' => [
						'size' => 600,
					],
					'size_units' => [ 'px', 'vh', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .ova_slideshow .items' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'background_overlay',
				[
					'label' => __( 'Background Overlay', 'eventlist' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'eventlist' ),
					'label_off' => __( 'Hide', 'eventlist' ),
					'separator' => 'before',
				]
			);


			$this->add_control(
				'background_overlay_color',
				[
					'label' => __( 'Color', 'eventlist' ),
					'type' => Controls_Manager::COLOR,
					'default' => 'rgba(0,0,0,0.5)',
					'conditions' => [
						'terms' => [
							[
								'name' => 'background_overlay',
								'value' => 'yes',
							],
						],
					],
					'selectors' => [
						'{{WRAPPER}} .slide-inner .elementor-background-overlay' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'background_overlay_gradient',
				[
					'label' => __( 'Gradient', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'conditions' => [
						'terms' => [
							[
								'name' => 'background_overlay',
								'value' => 'yes',
							],
						],
					],
					'selectors' => [
						'{{WRAPPER}} .slide-inner .elementor-background-overlay:after' => 'background-image: linear-gradient({{VALUE}})',
					],
				]
			);

			$this->add_control(
				'background_overlay_gradient_opacity',
				[
					'label' => __( 'Gradient Opacity', 'eventlist' ),
					'type' => Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1,
							'step' => 0.1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 0,
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'background_overlay',
								'value' => 'yes',
							],
						],
					],
					'selectors' => [
						'{{WRAPPER}} .slide-inner .elementor-background-overlay:after' => 'opacity: {{SIZE}};',
					],
				]
			);

			$this->add_control(
				'background_overlay_blend_mode',
				[
					'label' => __( 'Blend Mode', 'eventlist' ),
					'type' => Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Normal', 'eventlist' ),
						'multiply' => 'Multiply',
						'screen' => 'Screen',
						'overlay' => 'Overlay',
						'darken' => 'Darken',
						'lighten' => 'Lighten',
						'color-dodge' => 'Color Dodge',
						'color-burn' => 'Color Burn',
						'hue' => 'Hue',
						'saturation' => 'Saturation',
						'color' => 'Color',
						'exclusion' => 'Exclusion',
						'luminosity' => 'Luminosity',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'background_overlay',
								'value' => 'yes',
							],
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ova_slideshow .slide-inner .elementor-background-overlay' => 'mix-blend-mode: {{VALUE}}',
					],
					'separator' => 'after',
				]
			);

			$this->add_control(
				'navigation',
				[
					'label' => __( 'Navigation', 'eventlist' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'both',
					'options' => [
						'both' => __( 'Arrows and Dots', 'eventlist' ),
						'arrows' => __( 'Arrows', 'eventlist' ),
						'dots' => __( 'Dots', 'eventlist' ),
						'none' => __( 'None', 'eventlist' ),
					],
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'eventlist' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => 'no',
				]
			);

			$this->add_control(
				'autoplay_speed',
				[
					'label' => __( 'Autoplay Speed (ms)', 'eventlist' ),
					'type' => Controls_Manager::NUMBER,
					'default' => 10000,
					'condition' => [
						'autoplay' => 'yes',
					],
					
				]
			);

			$this->add_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'eventlist' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

		$this->end_controls_section(); // *****End Slider Options Section*****

		// *****Style Slides*****
		$this->start_controls_section(
			'section_style_slides',
			[
				'label' => __( 'Slides', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_max_width',
			[
				'label' => __( 'Content Width', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ '%', 'px' ],
				'default' => [
					'size' => '66',
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'slides_padding',
			[
				'label' => __( 'Padding', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section(); 
		// *****End Style Slides*****



		// *****Style Title*****
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Title', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => __( 'Margin', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => __( 'Hover', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-title:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .elementor-slide-title',
			]
		);

		$this->add_control(
			'show_animation_title',
			[
				'label' => __( 'Animate', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'animation_style_title',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $animation_array,
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_title',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_dur_title',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
				
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_title',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->end_controls_section(); 
		// End Style Title


		// Style Tag
		$this->start_controls_section(
			'section_style_tag',
			[
				'label' => __( 'Tag', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'tag_margin',
			[
				'label' => __( 'Margin', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-tag' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'tag_color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-tag, {{WRAPPER}} .elementor-slide-tag a' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_control(
			'tag_color_hover',
			[
				'label' => __( 'Hover', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-tag a:hover' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tag_typography',
				'selector' => '{{WRAPPER}} .elementor-slide-tag',
			]
		);

		$this->add_control(
			'show_animation_tag',
			[
				'label' => __( 'Animate', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'animation_style_tag',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $animation_array,
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_tag',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_dur_tag',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1000,
				
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_tag',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);
		$this->end_controls_section(); 
		// End Style Tag


		// Style Venue
		$this->start_controls_section(
			'section_style_venue',
			[
				'label' => __( 'Venue', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'venue_margin',
			[
				'label' => __( 'Margin', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-venue' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'venue_padding',
			[
				'label' => __( 'Padding', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-venue span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'venue_color',
			[
				'label' => __( 'Text Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-venue span' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_control(
			'icon_venue_color',
			[
				'label' => __( 'Icon', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-venue span i' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'venue_typography',
				'selector' => '{{WRAPPER}} .elementor-slide-venue',
			]
		);

		$this->add_control(
			'show_animation_venue',
			[
				'label' => __( 'Animate', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'animation_style_venue',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $animation_array,
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_venue',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_dur_venue',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1500,
				
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_venue',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->end_controls_section(); 
		// End Style Venue


		// Style Date
		$this->start_controls_section(
			'section_style_date',
			[
				'label' => __( 'Date', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'date_margin',
			[
				'label' => __( 'Margin', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'date_padding',
			[
				'label' => __( 'Padding', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slide-inner .elementor-slide-date span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'date_color',
			[
				'label' => __( 'Text Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-date span' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_control(
			'icon_date_color',
			[
				'label' => __( 'Icon', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-slide-date span i' => 'color: {{VALUE}}',

				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'date_typography',
				'selector' => '{{WRAPPER}} .elementor-slide-date',
			]
		);

		$this->add_control(
			'show_animation_date',
			[
				'label' => __( 'Animate', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'animation_style_date',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $animation_array,
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_date',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'animation_dur_date',
			[
				'label' => __( 'Animation', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1500,
				
				'conditions' => [
					'terms' => [
						[
							'name' => 'show_animation_date',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->end_controls_section(); 
		// End Style Date


		// Style Navigation
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label' => __( 'Navigation', 'eventlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'navigation' => [ 'arrows', 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'heading_style_arrows',
			[
				'label' => __( 'Arrows', 'eventlist' ),
				'type' => Controls_Manager::HEADING,
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'auto_show_arrows',
			[
				'label' => __( 'Show Arrows', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'hover',
				'options' => [
					'always' => __( 'Always', 'eventlist' ),
					'hover' => __( 'Hover', 'eventlist' ),
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_size',
			[
				'label' => __( 'Arrows Size', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-prev i, {{WRAPPER}} .ova_slideshow .owl-next i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
				'default' => ['px' => 20,],
			]
		);

		$this->add_control(
			'arrows_color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-prev, {{WRAPPER}} .ova_slideshow .owl-next' => 'color: {{VALUE}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
				'default' => '#ffffff',
			]
		);

		$this->add_control(
			'arrows_color_hover',
			[
				'label' => __( 'Color Hover', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-prev:hover, {{WRAPPER}} .ova_slideshow .owl-next:hover' => 'color: {{VALUE}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
				'default' => '#e86c60',
				'separator' => 'after',
			]
		);

		// Dots
		$this->add_control(
			'heading_style_dots',
			[
				'label' => __( 'Dots', 'eventlist' ),
				'type' => Controls_Manager::HEADING,
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_position',
			[
				'label' => __( 'Dots Position', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					// 'outside' => __( 'Outside', 'eventlist' ),
					'bottom' => __( 'Bottom', 'eventlist' ),
					'middle' => __( 'Middle', 'eventlist' ),
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'hide_in_mobile',
			[
				'label' => __( 'Hide In Mobile', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'owl-dots',
				'options' => [
					'owl-dots' => __( 'No', 'eventlist' ),
					'owl-dots hide_in_mobile' => __( 'Yes', 'eventlist' ),
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_align',
			[
				'label' => __( 'Dots Align', 'eventlist' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'toggle' => false,
				'default' => 'center',
				'options' => [
					'left' => [
						'title' => __( 'Left', 'eventlist' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'eventlist' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'eventlist' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-dots ' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left' => 'left: 0;',
					'center' => 'left: 50%; transform: translateX(-50%); align-items: center',
					'right' => 'right: 0; align-items: flex-end',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_responsive_control(
			'dots_padding',
			[
				'label' => __( 'Padding Dots', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-dots' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_responsive_control(
			'dot_margin',
			[
				'label' => __( 'Margin Dot', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-dots .owl-dot' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-dots .active span' => 'background: {{VALUE}};',
					'{{WRAPPER}} .ova_slideshow .owl-dots span' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
				// 'default' => '#b9a171',
			]
		);

		$this->add_control(
			'dots_color_hover',
			[
				'label' => __( 'Color Hover', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_slideshow .owl-dot:hover span' => 'background: {{VALUE}};',
					'{{WRAPPER}} .ova_slideshow.line_style .owl-dot:hover span' => 'border-color: {{VALUE}};',
				],
				// 'default' => '#b9a171',
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->end_controls_section();
		// *****End Style Navigation*****
	}

	protected function render() {

		$settings = $this->get_settings();
		
		$_prefix = OVA_METABOX_EVENT;

		if ( $settings['event_category'] ) {
			$settings['event_category'] = implode(", ", $settings['event_category']);
		}

		$events = el_get_event_slideshow($settings['total_post'], $settings['event_category'], $settings['event_filter'], $settings['event_featured'], $settings['event_orderby'], $settings['event_order']);

		if ( empty( $events ) ) {
			return;
		}

		$show_animation_title = $settings['show_animation_title'];
		$show_animation_tag = $settings['show_animation_tag'];
		$show_animation_venue = $settings['show_animation_venue'];
		$show_animation_date = $settings['show_animation_date'];


		$slides = [];
		$slide_count = 0;
		$eids = array();
		
		if ( $events->have_posts() ) {
			$eids = $events->posts;
		}

		foreach ( $eids as $eid ) {

			$venue = get_post_meta($eid, $_prefix.'venue', true) ? get_post_meta($eid, $_prefix.'venue', true) : '';
			$date = get_post_meta($eid, $_prefix.'start_date_str', true) ? get_post_meta($eid, $_prefix.'start_date_str', true) : '';
			$option_calendar = get_post_meta($eid, $_prefix.'option_calendar', true) ? get_post_meta($eid, $_prefix.'option_calendar', true) : '';
			$calendar_recurrence = get_post_meta($eid, $_prefix.'calendar_recurrence', true) ? get_post_meta($eid, $_prefix.'calendar_recurrence', true) : '';

			// var_dump($option_calendar);
			$date_start = '';
			if ($option_calendar == 'auto') {
				if ( $calendar_recurrence ) {
					foreach ( $calendar_recurrence as $value ) {
						if ( ( strtotime($value['date']) - strtotime('today') ) >= 0 ) {
							$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
						}
					}
				}
				// var_dump($arr_start_date);
				if ( isset($arr_start_date) && $arr_start_date ) {
					$date_start = date_i18n( get_option( 'date_format' ), min($arr_start_date) );
				} else {
					$date_start = date_i18n( get_option( 'date_format' ), $date );
				}

			} else {

				if ( $date ) {
					$date_start = date_i18n( get_option( 'date_format' ), $date );
				}
			}

			

			$tags = get_the_terms($eid, 'event_tag' );
			$slide_html = $slide_all_html = '';
			$btn_attributes = '';

			if ( 'yes' === $settings['background_overlay'] ) {
				$slide_html .= '<div class="elementor-background-overlay"></div>';
			}

			$slide_html .= '<div class="container"><div class="row"><div class="elementor-slide-content">';

			if ( get_the_title($eid) && $show_animation_title != '' ) {
				$slide_html .= '<a href="'.get_the_permalink($eid).'" data-animation="'.$settings['animation_style_title'].'" data-animation_dur="'.trim($settings['animation_dur_title']).'"  class="second_font elementor-slide-title " style="animation-duration: '.trim($settings['animation_dur_title']).'ms">' . get_the_title($eid) . '</a>';
			}else{
				$slide_html .= '<a href="'.get_the_permalink($eid).'" class="second_font elementor-slide-title ">' . get_the_title($eid) . '</a>';
			}

			if ( $tags && $show_animation_tag != '' ) {
				$slide_html .= '<div data-animation="'.$settings['animation_style_tag'].'" data-animation_dur="'.trim($settings['animation_dur_tag']).'"  class="elementor-slide-tag " style="animation-duration: '.trim($settings['animation_dur_tag']).'ms">';
				$tag = [];
				foreach ($tags as $value) {
					$tag[] = '<a href="'.get_term_link($value->term_id).'" >' . '<span class="tag">'.esc_html__('#','eventlist').'</span>'.$value->name . '</a>';
				}
				$slide_html .= implode( ', ', $tag );
				$slide_html .= '</div>';
			} elseif ($tags) {

				$slide_html .= '<div class="elementor-slide-tag">';
				$tag = [];
				foreach ($tags as $value) {
					$tag[] = '<a href="'.get_term_link($value->term_id).'" >' . '<span class="tag">'.esc_html__('#','eventlist').'</span>'.$value->name . '</a>';
				}
				$slide_html .= implode( ', ',$tag);
				$slide_html .= '</div>';
			}

			$slide_html .= '<div class="elementor-slide-bottom">';

			if ( $venue && $show_animation_venue != '' ) {
				$slide_html .= '<div data-animation="'.$settings['animation_style_venue'].'" data-animation_dur="'.trim($settings['animation_dur_venue']).'"  class="elementor-slide-venue " style="animation-duration: '.trim($settings['animation_dur_venue']).'ms"><span><i class="icon_pin_alt"></i>';
				$arr_venue = [];
				foreach ($venue as $value) {
					$arr_venue[] = $value;
				}
				$slide_html .= implode( ', ',$arr_venue);
				$slide_html .= '</span></div>';
			} elseif ($venue) {

				$slide_html .= '<div class="elementor-slide-venue"><span><i class="icon_pin_alt"></i>';
				$arr_venue = [];
				foreach ($venue as $value) {
					$arr_venue[] = $value;
				}
				$slide_html .= implode( ', ',$arr_venue);
				$slide_html .= '</span></div>';
			}

			if ( $date_start && $show_animation_date != '' ) {
				$slide_html .= '<div class="elementor-slide-date" data-animation="'.$settings['animation_style_date'].'" data-animation_dur="'.trim($settings['animation_dur_date']).'" style="animation-duration: '.trim($settings['animation_dur_date']).'ms"><span><i class="icon_clock_alt"></i>' . $date_start . '</span></div>';
			} elseif( $date_start ) {
				$slide_html .= '<div class="elementor-slide-date"><span><i class="icon_clock_alt"></i>'. $date_start .'</span></div>';
			}

			$slide_html .= '</div>';


			$slide_html .= '</div></div></div>';
			
			$slide_all_html = '<div class=" slide-bg" style="background-image: url('.get_the_post_thumbnail_url($eid, 'thumbnail_single_page').')" ></div>
			<div class="slide-inner">'.$slide_html.'</div>';

			$slides[] = '<div class=" elementor-repeater-item-' . $slide_count . ' items" >' . $slide_all_html . '</div>';
			$slide_count++;
		}


		$is_rtl         = is_rtl() ? true : false;
		$direction      = $is_rtl ? 'rtl' : 'ltr';
		$show_dots      = ( in_array( $settings['navigation'], [ 'dots', 'both' ] ) );
		$show_arrows    = ( in_array( $settings['navigation'], [ 'arrows', 'both' ] ) );
		$autoplay_owl   = ( 'yes' === $settings['autoplay'] ) ? true : false;
		$loop_owl       = ( 'yes' === $settings['infinite'] ) ? true : false;
		// $lazyLoad_owl   = ( 'yes' === $settings['lazy_load'] ) ? true : false;
		$autoplay_speed = ( 'yes' === $settings['autoplay'] ) ? $settings['autoplay_speed'] : 9999999999999;
		$mouseDrag      = count($slides) == 1 ? false : true;
		$owl_carousel = [
			'items'           => 1,
			'singleItem'      => 1,
			'autoplayTimeout' => $autoplay_speed,
			'autoplay'        => $autoplay_owl,
			'loop'            => $loop_owl,
			// 'lazyLoad'        => $lazyLoad_owl,
			'nav'             => $show_arrows,
			'dots'            => $show_dots,
			'rtl'             => $is_rtl,
			'dotsClass'       => $settings['hide_in_mobile'],
			'mouseDrag'       => $mouseDrag,
			'navText' => [
				'<i class="arrow_left"></i>',
				'<i class="arrow_right"></i>'
			],

		];
		
		$carousel_classes = [ 'elementor-slides owl-carousel owl-theme owl-loaded' ];

		if ( $show_arrows ) {
			// $carousel_classes[] = 'arrows-' . $settings['arrows_position'];
			$carousel_classes[] = 'arrows-inside';
			$carousel_classes[] = 'arrows-show-' . $settings['auto_show_arrows'];
		}

		if ( $show_dots ) {
			$carousel_classes[] = 'dots-' . $settings['dots_position'];
		}

		$carousel_classes[] = 'animated owl-animated-out owl-animated-in';

	

		?>
		<div class="ova_slideshow elementor-slides-wrapper" dir="<?php echo esc_attr( $direction ); ?>">
			<div class="<?php echo esc_attr( implode(' ', $carousel_classes) ); ?>" data-owl_carousel="<?php echo esc_attr( wp_json_encode( $owl_carousel) ); ?>">
				<?php echo wp_kses_post( implode( '', $slides ) ); ?>
			</div>
		</div>
		<?php

		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Slideshow() );
