<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Controls_Manager;

class EL_Elementor_Search_Result extends EL_Abstract_Elementor {

	protected $name 	= 'el_search_result';
	protected $title 	= '';
	protected $icon 	= 'eicon-search-results';

	
	public function get_title(){
		return __('Search Result', 'eventlist');
	}
	
	protected function register_controls() {

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
					'type4' => __('Type 4', 'eventlist'),
					'type5' => __('Type 5', 'eventlist'),
					'type6' => __('Type 6', 'eventlist')
				]
			]
		);

		$this->add_control(
			'column',
			[
				'label'   => __( 'Column', 'eventlist' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'three-column',
				'options' => [
					'two-column' => __('Two Column', 'eventlist'),
					'three-column' => __('Three Column', 'eventlist')
				],
			]
		);

		



		$this->end_controls_section();

	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_shortcode_search_result_template', 'elementor/search_result.php' );


		el_get_template( $template, $args );

		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Search_Result() );
