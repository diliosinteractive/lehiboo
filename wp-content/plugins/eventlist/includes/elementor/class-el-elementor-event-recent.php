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

class EL_Elementor_Event_Recent extends EL_Abstract_Elementor {

	protected $name 	= 'ova_event_recent';
	protected $title 	= 'Event Recent';
	protected $icon 	= 'eicon-posts-grid';

	
	public function get_title(){
		return __('Event Recent', 'eventlist');
	}

	public function get_script_depends() {
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
			'total_count',
			[
				'label' => __( 'Total', 'eventlist' ),
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
					'date' => __( 'Date', 'eventlist' ),
					'id'  => __( 'ID', 'eventlist' ),
					'title' => __( 'Title', 'eventlist' ),
					'start_date' => __( 'Start Date', 'eventlist' ),
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
					'ASC' => __( 'Ascending', 'eventlist' ),
					'DESC'  => __( 'Descending', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'heading_setting_layout',
			[
				'label' => __( 'Template', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
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
			'show_remove_btn',
			[
				'label' => esc_html__( 'Show Remove Button', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'eventlist' ),
				'label_off' => esc_html__( 'Hide', 'eventlist' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();

		$template = apply_filters( 'el_elementor_event_recent', 'elementor/event_recent.php' );

	
		
		el_get_template( $template, $settings );
		


		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Recent() );
