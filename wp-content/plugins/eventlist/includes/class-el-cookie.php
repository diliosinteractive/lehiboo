<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists('EL_Cookie') ) {
	
	class EL_Cookie {
		public function __construct(){
			
			add_action( 'template_redirect', array($this, 'ova_event_setcookie') );
		}

		public function ova_event_setcookie(){

			if ( is_singular( 'event' ) ) {
				setcookie('ova_event_id['.get_the_ID().']', get_the_ID(), time() + EL_Setting::instance()->general->get( 'cookie_expired', 604800 ), "/");
			}
		}
	}

	new EL_Cookie();
}