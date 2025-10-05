<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

class EL_Elementor_Search_Map extends EL_Abstract_Elementor {

	protected $name 	= 'el_search_map';
	protected $title 	= 'Search Map';
	protected $icon 	= 'eicon-google-maps';
	
	public function get_title(){
		return __('Search Map', 'eventlist');
	}

	public function get_script_depends() {

		/* Google Maps */
		if( EL()->options->general->get('event_google_key_map') ){
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?key='.EL()->options->general->get('event_google_key_map').'&libraries=places&callback=Function.prototype', array('jquery'), false, true);
		}else{
			wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?sensor=false&libraries=places&callback=Function.prototype', array('jquery'), false, true);
		}
		wp_enqueue_script( 'google-marker',EL_PLUGIN_URI.'assets/libs/markerclusterer.js', array('jquery'), false, true);
		wp_enqueue_script( 'google-richmarker', EL_PLUGIN_URI.'assets/libs/richmarker-compiled.js', array('jquery'), false, true);

		wp_enqueue_style( 'nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.css', 'all' );
		wp_enqueue_script('nouislider', EL_PLUGIN_URI.'assets/libs/nouislider/nouislider.min.js', array(), false, false);
		wp_enqueue_script('wnumb', EL_PLUGIN_URI.'assets/libs/nouislider/wNumb.min.js', array(), false, false);

		// Select2
        wp_enqueue_style( 'select2-style', EL_PLUGIN_URI.'assets/libs/select2/select2.min.css' );
        wp_enqueue_script( 'select2-script', EL_PLUGIN_URI.'assets/libs/select2/select2.min.js', array('jquery'), false, true );

		/* Override market google map when more event the same location*/
		wp_enqueue_script('oms', EL_PLUGIN_URI.'assets/libs/oms.js', array('jquery'), false, true);
		
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
			'location' => __('Location', 'eventlist'),
			'cat' => __('Categories', 'eventlist'),
			'all_time' => __('All Time', 'eventlist'),
			'start_event' => __('Start Date', 'eventlist'),
			'end_event' => __('End Date', 'eventlist'),
			'venue' => __('Venue', 'eventlist'),
			'loc_state' => __('State', 'eventlist'),
			'loc_city' => __('City', 'eventlist'),
			'event_type'		=> __('Type(Online/Offline)', 'eventlist'),
			'range_slider' => __( 'Price Range Slider', 'eventlist' ),
		);

		$this->start_controls_section(
			'section_setting',
			[
				'label' => esc_html__( 'Form Template', 'eventlist' ),
			]
		);

			$this->add_control(
				'show_filter',
				[
					'label' => __( 'Show Filters', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'eventlist' ),
					'label_off' => __( 'Hide', 'eventlist' ),
					'return_value' => 'yes',
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
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);
			
			$this->add_control(
				'start_slider',
				[
					'label' => esc_html__( 'Start', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 20,
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);

			$this->add_control(
				'end_slider',
				[
					'label' => esc_html__( 'End', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 80,
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);

			$this->add_control(
				'min_slider',
				[
					'label' => esc_html__( 'Min', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 0,
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);

			$this->add_control(
				'max_slider',
				[
					'label' => esc_html__( 'Max', 'eventlist' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 100,
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);
			
			$this->add_control(
				'pos2',
				[
					'label'   => __( 'Postition 2', 'eventlist' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => $search_fields,
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);

			$this->add_control(
				'pos3',
				[
					'label'   => __( 'Postition 3', 'eventlist' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => $search_fields,
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);
			
			$this->add_control(
				'pos4',
				[
					'label'   => __( 'Postition 4', 'eventlist' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => $search_fields,
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
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
					'condition' => [
						'show_filter' => 'yes'
					]
				]
			);
			
			$this->add_control(
				'pos11',
				[
					'label'   => __( 'Postition 11', 'eventlist' ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => $search_fields,
					'condition' => [
						'show_filter' => 'yes'
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
				'condition' => [
					'show_filter' => 'yes'
				]
			]
		);

		$this->add_control(
			'category_included',
			[
				'label' => esc_html__( 'Choose categories display in dropdown', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Empty will display all categories', 'eventlist' ),
				'label_block' => true,
				'multiple' => true,
				'options' => $this->ova_get_categories(),
				'default' => '',
				'condition' => [
					'show_filter' => 'yes'
				]
			]
		);

		
		$this->add_control(
			'search_results_page_setting_layout',
			[
				'label' => __( 'Search result page', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_featured',
			[
				'label' => __( 'Only show featured event', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'eventlist' ),
				'label_off' => __( 'Hide', 'eventlist' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);


		$this->add_control(
				'show_map',
			[
				'label' => __( 'Show Map', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'eventlist' ),
				'label_off' => __( 'Hide', 'eventlist' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->add_control(
			'radius_unit',
			[
				'label'   => __( 'Radius Unit', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'km',
				'options' => [
					'km' => __('Kilometers', 'eventlist'),
					'mi' => __('Miles', 'eventlist'),
				],
			]
		);

		$this->add_control(
			'zoom',
			[
				'label' => __( 'Zoom Map', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 20,
				'step' => 1,
				'default' => 4,
				'condition' => [
					'show_map' => 'yes'
				],
				'separator' => 'after',
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
					'type4' => __('Type 4', 'eventlist'),
					'type5' => __('Type 5', 'eventlist'),
					'type6' => __('Type 6', 'eventlist'),
				],
			]
		);

		$this->add_control(
			'column',
			[
				'label'   => __( 'Column', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'two-column',
				'options' => [
					'one-column' => __('1 Column', 'eventlist'),
					'two-column' => __('2 Columns', 'eventlist'),
					'three-column' => __('3 Columns', 'eventlist'),
				],
			]
		);
		

		$this->add_control(
			'marker_option',
			[
				'label'   => __( 'Marker Select', 'eventlist' ),
				'description' => __( 'You should use Icon to display exactly position', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => __('Icon', 'eventlist'),
					'price' => __('Price', 'eventlist'),
					'date' => __('Start Date', 'eventlist'),
				],
			]
		);

		$this->add_control(
			'marker_icon',
			[
				'label' => __( 'Choose Image', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				// 'default' => [
					// 'url' => \Elementor\Utils::get_placeholder_image_src(),
				// ],
				'condition' => [
					'marker_option' => 'icon'
				]
			]
		);

		
		



		$this->end_controls_section();

	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_elementor_search_form', 'elementor/search_map.php' );


		el_get_template( $template, $args );

	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Search_Map() );
