<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Search_Result extends EL_Shortcode {

	public $shortcode = 'el_search_result';

	public function __construct() {
		parent::__construct();
	}

	function add_shortcode( $args, $content = null ) {

		$type = 'type1';
		$column = 'two-column';
		
		$args = shortcode_atts( array(
			'type' 		=> $type,
			'column' 	=> $column,
		), $args );

		$template = apply_filters( 'el_shortcode_search_result_template', 'search/search-result.php' );

		ob_start();

		el_get_template( $template, $args );

		return ob_get_clean();
	}

}

new El_Shortcode_Search_Result();