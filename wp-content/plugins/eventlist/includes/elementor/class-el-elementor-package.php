<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EL_Elementor_Package extends EL_Abstract_Elementor {

	protected $name 	= 'el_package';
	protected $title 	= '';
	protected $icon 	= 'eicon-gallery-grid';

	
	public function get_title(){
		return __('Package', 'eventlist');
	}
	
	protected function register_controls() {

		


		$this->start_controls_section(
			'section_setting',
			[
				'label' => esc_html__( 'Settings', 'eventlist' ),
			]
		);


		
		$this->add_control(
			'class',
			[
				'label' => __('Class','eventlist'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'value' => '',
				
			]
		);



		$this->end_controls_section();

	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_elementor_package_template', 'elementor/package.php' );

		el_get_template( $template, $args );
		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Package() );
