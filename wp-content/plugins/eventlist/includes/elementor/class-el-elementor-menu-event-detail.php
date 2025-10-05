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

class EL_Elementor_Menu_Event_Detail extends EL_Abstract_Elementor {

	protected $name 	= 'el_menu_event_detail';
	protected $title 	= 'Menu Title event detail';
	protected $icon 	= 'eicon-share';
	
	public function get_title(){
		return __('Menu Title event detail', 'eventlist');
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'section_setting',
			[
				'label' => esc_html__( 'Settings', 'eventlist' ),
			]
		);
		
		$this->add_control(
			'icon_share',
			[
				'label'   => __( 'Class Icons Share', 'eventlist' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'fa fa-share-alt',
			]
		);

		$this->add_control(
			'icon_wishtlist',
			[
				'label'   => __( 'Class Icons Wishlist', 'eventlist' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'fa fa-heart-o',
			]
		);

		$this->add_control(
			'number_character',
			[
				'label'   => __( 'Number Character', 'eventlist' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 20,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$args = $this->get_settings();

		$template = apply_filters( 'el_elementor_menu_event_detail', 'elementor/menu_event_detail.php' );

		el_get_template( $template, $args );

	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Menu_Event_Detail() );
