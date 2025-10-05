<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

class EL_Elementor_Search_Form_2 extends EL_Abstract_Elementor {

	protected $name 	= 'el_search_form_2';
	protected $title 	= 'Search Form 2';
	protected $icon 	= 'eicon-search-results';
	
	public function get_title(){
		return __('Search Form 2', 'eventlist');
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
		// Select2
        wp_enqueue_style( 'select2-style', EL_PLUGIN_URI.'assets/libs/select2/select2.min.css' );
        wp_enqueue_script( 'select2-script', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js', array('jquery'), false, true );
		// Datetimepicker
		wp_enqueue_style( 'datetimepicker-style', EL_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
		wp_enqueue_script( 'datetimepicker-script', EL_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', array('jquery'), false, true );

		return [ 'script-elementor' ];
	}

	protected function ova_get_pages(){
		$pages = get_pages();
		$arr_page = [];
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$arr_page[$page->ID] = $page->post_title;
			}
		}
		return $arr_page;
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
			'time' => __('All Time', 'eventlist'),
			'start_date' => __('Start Date', 'eventlist'),
			'end_date' => __('End Date', 'eventlist'),
			'name_venue' => __('Venue', 'eventlist'),
			'event_state' => __('State', 'eventlist'),
			'event_type' => __('Event Type (Online/Offline)', 'eventlist'),
		);

		$date_format = array(
			'd-m-Y' => date_i18n( 'd-m-Y' ),
			'm/d/Y' => date_i18n( 'm/d/Y' ),
			'Y-m-d' => date_i18n( 'Y-m-d' ),
			'm-d-Y' => date_i18n( 'm-d-Y' ),
		);

		$day_of_week_start = array(
			'0' => esc_html__( "Sunday", 'eventlist' ),
			'1' => esc_html__( "Monday", 'eventlist' ),
			'2' => esc_html__( "Tuesday", 'eventlist' ),
			'3' => esc_html__( "Wednesday", 'eventlist' ),
			'4' => esc_html__( "Thursday", 'eventlist' ),
			'5' => esc_html__( "Friday", 'eventlist' ),
			'6' => esc_html__( "Saturday", 'eventlist' ),
		);

		// Settings
		$this->start_controls_section(
				'content_section',
				[
					'label' => esc_html__( 'Settings', 'eventlist' ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$this->add_control(
				'search_result',
				[
					'label' => esc_html__( 'Search Result Page', 'eventlist' ),
					'description' => esc_html__( 'Choose page included search form like half map page', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => $this->ova_get_pages(),
				]
			);


			$this->add_control(
				'show_location',
				[
					'label' => esc_html__( 'Show Location', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Show', 'eventlist' ),
					'label_off' => esc_html__( 'Hide', 'eventlist' ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);


			$this->add_control(
				'show_category',
				[
					'label' => esc_html__( 'Show Category', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Show', 'eventlist' ),
					'label_off' => esc_html__( 'Hide', 'eventlist' ),
					'return_value' => 'yes',
					'default' => 'yes',
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
					'condition' => [
						'show_category' => 'yes',
					],
				]
			);

			$this->add_control(
				'radius_unit',
				[
					'label'   => __( 'Radius Unit', 'eventlist' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'mi',
					'options' => [
						'km' => __('Kilometers', 'eventlist'),
						'mi' => __('Miles', 'eventlist'),
					],
				]
			);

			$this->add_control(
				'radius',
				[
					'label' => esc_html__( 'Radius', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'description' => esc_html__( 'Search around X Unit', 'eventlist' ),
					'default' => 50,
				]
			);
			

			$this->add_control(
				'type',
				[
					'label' => esc_html__( 'Border Style', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'type_1',
					'options' => [
						'type_1' => esc_html__( 'Type 1', 'eventlist' ),
						'type_2' => esc_html__( 'Type 2', 'eventlist' ),
					],
				]
			);
				
			$this->add_control(
				'heading_setting_layout',
				[
					'label' => __( 'Advanced Search', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$repeater_filter = new \Elementor\Repeater();

				$repeater_filter->add_control(
					'field',
					[
						'label'   => __( 'Search Fields', 'eventlist' ),
						'type'    => Controls_Manager::SELECT,
						'default' => '',
						'separator' => 'before',
						'options' => $search_fields,
					]
				);

				$this->add_control(
					'fields',
					[
						'label' => esc_html__( 'Filter List', 'eventlist' ),
						'type' => \Elementor\Controls_Manager::REPEATER,
						'fields' => $repeater_filter->get_controls(),
						'prevent_empty' => false,
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

				$this->add_control(
					'column',
					[
						'label'   => __( 'Column', 'eventlist' ),
						'type'    => Controls_Manager::SELECT,
						'default' => 'three_column',
						'options' => [
							'two_column' => __('2', 'eventlist'),
							'three_column' => __('3', 'eventlist'),
							'four_column' => __('4', 'eventlist'),
						],
					]
				);

				$this->add_control(
					'date_format',
					[
						'label' => esc_html__( 'Date Format', 'eventlist' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'd-m-Y',
						'options' => $date_format,
					]
				);

				$this->add_control(
					'day_of_week_start',
					[
						'label' => esc_html__( 'Day of week start', 'eventlist' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '0',
						'options' => $day_of_week_start,
					]
				);	

				

		$this->end_controls_section();
	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_elementor_search_form_2', 'elementor/search_form_2.php' );

	
		el_get_template( $template, $args );


		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Search_Form_2() );
