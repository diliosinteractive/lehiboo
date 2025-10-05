<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Member_Account extends EL_Shortcode {

	public $shortcode = 'el_member_account';

	public function __construct() {
		parent::__construct();
	}

	function add_shortcode( $args, $content = null ) {

		$args = shortcode_atts( array(
			'class' => '',
		), $args );

		$template = '';

		if( !is_user_logged_in() ) {
			
			$template = apply_filters( 'el_shortcode_member_account_login_template', 'vendor/login.php' );
			el_get_template( $template );

		} else {

			$template = EL_Vendor::instance()->get_template_vendor( $_GET );

			el_get_template( $template['template'], $template['msg'] );
			
		}
	}

}

new El_Shortcode_Member_Account();