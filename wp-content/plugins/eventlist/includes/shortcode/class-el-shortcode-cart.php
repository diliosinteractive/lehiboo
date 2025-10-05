<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Cart extends EL_Shortcode {

	public $shortcode = 'el_cart';

	public function __construct() {
		parent::__construct();
	}

	function add_shortcode( $args, $content = null ) {
		
		ob_start();

		$template = EL_Cart::instance()->get_template_cart( $_GET );
		if( $template ){
			el_get_template( $template, $_GET );
		}
		

		return ob_get_clean();
	}

}

new El_Shortcode_Cart();