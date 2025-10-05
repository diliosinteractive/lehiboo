<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Assets") ) {

	class Ova_Login_Assets{

		public function __construct(){
			add_action( 'admin_enqueue_scripts', array( $this, 'login_admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
		}

		public function login_admin_scripts(){

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_style( 'register-style', OVALGURL.'assets/css/admin.css' );
			wp_enqueue_script('register-script', OVALGURL.'assets/js/admin-script.js', array('jquery'), false, true);
			wp_localize_script( 'register-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}

		public function login_enqueue_scripts(){
			$site_key 		= OVALG_Settings::recapcha_site_key();
			$recapcha_type 	= OVALG_Settings::recapcha_type();
			
			if ( ovalg_recaptcha_is_key_setup_complete() ) {
				
				wp_enqueue_script('ova-login-handle-recapcha', OVALGURL . 'assets/js/recapcha.js' , array(), false, false);
				wp_localize_script( 'ova-login-handle-recapcha', 'recapcha_object', array( 'site_key' => $site_key ) );

				if ( $recapcha_type == 'v2' ) {
					wp_enqueue_script('ova-login-recapcha', 'https://www.google.com/recaptcha/api.js?hl=' . esc_attr( get_locale() ) . '&onload=ova_lg_recapcha_v2&render=explicit', array(), false, false);
				} elseif ( $recapcha_type == 'v3' ) {
					wp_enqueue_script('ova-login-recapcha', 'https://www.google.com/recaptcha/api.js?onload=ova_lg_recapcha_v3&render='.esc_attr( $site_key ), array(), false, false);
				}
			}

			wp_enqueue_style('ova_login', OVALGURL.'assets/css/login.css' );
			wp_enqueue_script('login-script', OVALGURL.'assets/js/login-script.js', array('jquery'), false, true );
		}
	}
	new Ova_Login_Assets();
}