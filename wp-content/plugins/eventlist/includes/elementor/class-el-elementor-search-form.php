<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

class EL_Elementor_Search_Form extends EL_Abstract_Elementor {

	protected $name 	= 'el_search_form';
	protected $title 	= 'Search Form';
	protected $icon 	= 'eicon-search-results';
	
	public function get_title(){
		return __('Search Form', 'eventlist');
	}

	public function get_script_depends() {
		
		// Select2
        wp_enqueue_style( 'select2-style', EL_PLUGIN_URI.'assets/libs/select2/select2.min.css' );
        wp_enqueue_script( 'select2-script', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js', array('jquery'), false, true );

        wp_enqueue_style( 'nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.css', 'all' );
		wp_enqueue_script('nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.js', array(), false, true);
		wp_enqueue_script('wnumb', EL_PLUGIN_URI.'assets/libs/nouislider/wNumb.min.js', array(), false, true);
		

		return [ 'script-elementor' ];
	}

	protected function ova_get_categories(){
		$args = array (
			'taxonomy' => 'event_cat',
			'fields' => 'id=>name',
		);

		$terms = get_terms( $args );
		return $terms;
	}

	protected function register_controls() {

		$search_fields = array(
			'' => __('Select Search', 'eventlist'),
			'name_event' => __('Name Event', 'eventlist'),
			'loc_input' => __('Location Input', 'eventlist'),
			'cat' => __('Categories', 'eventlist'),
			'all_time' => __('All Time', 'eventlist'),
			'start_event' => __('Start Date', 'eventlist'),
			'end_event' => __('End Date', 'eventlist'),
			'venue' => __('Venue', 'eventlist'),
			'loc_state' => __('State', 'eventlist'),
			'loc_city' => __('City', 'eventlist'),
			'event_type' => __('Event Type (Online/Offline)', 'eventlist'),
			'range_slider' => __( 'Price Range Slider', 'eventlist' ),
		);

		$this->start_controls_section(
			'section_setting',
			[
				'label' => esc_html__( 'Settings', 'eventlist' ),
			]
		);

		$this->add_control(
			'type',
			[
				'label'   => __( 'Type', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'type1',
				'options' => [
					'type1' => __('Type 1', 'eventlist'),
					'type2' => __('Type 2', 'eventlist'),
					'type3' => __('Type 3', 'eventlist'),
				],
			]
		);

		$this->add_control(
			'category_included',
			[
				'label' => esc_html__( 'Category Included', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Default is all', 'eventlist' ),
				'label_block' => true,
				'multiple' => true,
				'options' => $this->ova_get_categories(),
				'default' => '',
			]
		);

		// Price Range Silder

		$this->add_control(
			'price_range_slider',
			[
				'label' => esc_html__( 'Price Range Slider', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		
		$this->add_control(
			'start_slider',
			[
				'label' => esc_html__( 'Start', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
			]
		);

		$this->add_control(
			'end_slider',
			[
				'label' => esc_html__( 'End', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 80,
			]
		);

		$this->add_control(
			'min_slider',
			[
				'label' => esc_html__( 'Min', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
			]
		);

		$this->add_control(
			'max_slider',
			[
				'label' => esc_html__( 'Max', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 100,
			]
		);

		$this->add_control(
			'column',
			[
				'label'   => __( 'Column', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'four_column',
				'options' => [
					'two_column' => __('2 Column', 'eventlist'),
					'three_column' => __('3 Column', 'eventlist'),
					'four_column' => __('4 Column', 'eventlist'),
					'five_column' => __('5 Column', 'eventlist'),
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos1',
			[
				'label'   => __( 'Postition 1', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'separator' => 'before',
				'options' => $search_fields,
			]
		);
		
		$this->add_control(
			'pos2',
			[
				'label'   => __( 'Postition 2', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
			]
		);

		$this->add_control(
			'pos3',
			[
				'label'   => __( 'Postition 3', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
			]
		);
		
		$this->add_control(
			'pos4',
			[
				'label'   => __( 'Postition 4', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);
		
		$this->add_control(
			'pos5',
			[
				'label'   => __( 'Postition 5', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos6',
			[
				'label'   => __( 'Postition 6', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos7',
			[
				'label'   => __( 'Postition 7', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos8',
			[
				'label'   => __( 'Postition 8', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos9',
			[
				'label'   => __( 'Postition 9', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'pos10',
			[
				'label'   => __( 'Postition 10', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $search_fields,
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type1',
							],
						],
					],
				]
			]
		);



		$list_taxonomy = EL_Post_Types::register_taxonomies_customize();

		$select_list_taxonomy[''] = esc_html__( 'Select Taxonomy', 'eventlist' );
		if( ! empty( $list_taxonomy ) && is_array( $list_taxonomy ) ) {
			foreach( $list_taxonomy as $value ) {
				$select_list_taxonomy[$value['slug']] = $value['name'];
			}
		}

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'taxonomy_custom', [
				'label' => __( 'Taxonomy Custom', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options' => $select_list_taxonomy,
			]
		);


		$this->add_control(
			'list_taxonomy_custom',
			[
				'label' => __( 'List Taxonomy Custom', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'prevent_empty' => false,
			]
		);

		$this->end_controls_section();


		/***** Section Icon *****/
		$this->start_controls_section(
			'section_icon',
			[
				'label' => esc_html__( 'Icon', 'eventlist' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'type',
							'operator' => '!in',
							'value' => [
								'type2',
							],
						],
					],
				]
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .field_search .icon_field ' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon1',
			[
				'label' => __( 'Icon 1', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
			]
		);

		$this->add_control(
			'icon2',
			[
				'label' => __( 'Icon 2', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
			]
		);

		$this->add_control(
			'icon3',
			[
				'label' => __( 'Icon 3', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
			]
		);

		$this->add_control(
			'icon4',
			[
				'label' => __( 'Icon 4', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);
		
		$this->add_control(
			'icon5',
			[
				'label' => __( 'Icon 5', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);
		
		$this->add_control(
			'icon6',
			[
				'label' => __( 'Icon 6', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);
		
		$this->add_control(
			'icon7',
			[
				'label' => __( 'Icon 7', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);

		$this->add_control(
			'icon8',
			[
				'label' => __( 'Icon 8', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);

		$this->add_control(
			'icon9',
			[
				'label' => __( 'Icon 9', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);

		$this->add_control(
			'icon10',
			[
				'label' => __( 'Icon 10', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'fa fa-facebook', 'eventlist' ),
				'condition' => [
					'type' => 'type3'
				]
			]
		);

		$this->end_controls_section();


		/***** Section Button *****/
		$this->start_controls_section(
			'section_button',
			[
				'label' => esc_html__( 'Button Search', 'eventlist' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => __( 'Text Button', 'eventlist' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Search', 'eventlist' ),
				'placeholder' => __( 'Search', 'eventlist' ),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'label' => __( 'Typography', 'eventlist' ),
				'selector' => '{{WRAPPER}} .elementor_search_form form .el_submit_search input ',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'label' => __( 'Border', 'eventlist' ),
				'selector' => '{{WRAPPER}} .elementor_search_form form .el_submit_search input',
			]
		);

		/* Button Effect */
		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'eventlist' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .el_submit_search input' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .el_submit_search input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'eventlist' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => __( 'Text Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .el_submit_search input:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => __( 'Background Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .el_submit_search input:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __( 'Border Color', 'eventlist' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor_search_form form .el_submit_search input:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();


		$this->end_controls_section();

	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_elementor_search_form', 'elementor/search_form.php' );


		el_get_template( $template, $args );


		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Search_Form() );
