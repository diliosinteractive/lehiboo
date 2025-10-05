<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class EL_Abstract_Elementor extends Elementor\Widget_Base {

	protected $name = '';
	protected $title = '';
	protected $icon = '';
	protected $categories = array( OVA_ELEMENTOR_CAT );


	public function get_name() {
		return $this->name;
	}

	public function get_title() {
		return $this->title;	
	}

	public function get_icon() {
		return $this->icon;
	}

	public function get_categories() {
		return $this->categories;
	}

	public function get_script_depends() {
		// wp_enqueue_script( 'slick-script', OVAPO_PLUGIN_URI.'assets/libs/slick/slick/slick.min.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
	}

	protected function register_controls() {

	}

	protected function render() {
		
	}

}
