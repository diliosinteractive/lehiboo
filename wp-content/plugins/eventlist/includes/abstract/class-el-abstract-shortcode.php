<?php

if ( !defined( 'ABSPATH' ) ) {

	exit();
}

abstract class EL_Shortcode {

	// shortcode name
	protected $shortcode = null;

	function __construct() {
		add_shortcode( $this->shortcode, array( $this, 'add_shortcode' ) );
		
	}

	function add_shortcode( $atts, $content = null ) {
		
	}

}