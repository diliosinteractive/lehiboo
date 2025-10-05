<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EL_Mail' ) ) {

	/**
	 * Class EL_Mail
	 */
	class EL_Mail {

		/**
		 * EL_Mail constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'el_mail_include' ) );
		}

		/**
		 * Add user roles.
		 */
		public static function el_mail_include() {

			require_once EL_PLUGIN_INC . 'email/el-mail-functions.php';
			
		}
	}
}

new EL_Mail();