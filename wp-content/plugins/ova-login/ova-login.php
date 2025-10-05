<?php
/**
 * Plugin Name:       Ova Login
 * Description:       Customize Login/Register in WordPress. You can override template in child theme.
 * Version:           1.2.6
 * Author:            Ovatheme
 * License:           GPL-2.0+
 * Text Domain:       ova-login
 */

defined( 'ABSPATH' ) || exit;

class Ova_Login_Plugin {

	/**
     * Initializes the plugin.
     *
     * To keep the initialization fast, only add filter and action
     * hooks in the constructor.
     */
	public function __construct() {
		// load defines
		$this->defines();
		// Settings
		include_once OVALGDIR . '/settings/settings.php';
		include_once OVALGDIR . '/settings/admin_settings.php';
		// Functions
		include_once OVALGDIR . '/ova-login-functions.php';
		// Assets
		include_once OVALGDIR . '/inc/class-ovalg-assets.php';
		
		// Ajax
		include_once OVALGDIR . '/inc/class-ovalg-ajax.php';

		include_once OVALGDIR . '/inc/class-ovalg-hooks.php';
		// language
		load_plugin_textdomain( 'ova-login', false, basename( OVALGDIR ) .'/languages' ); 
		// shortcode
		include_once OVALGDIR . '/inc/class-ovalg-shortcode.php';
		// register login
		include_once OVALGDIR . '/inc/class-ovalg-register.php';
		// recapcha
		include_once OVALGDIR . '/inc/class-ovalg-recapcha.php';

		if ( is_admin() ) {
			include_once OVALGDIR . '/admin/class-ovalg-vendor-approve.php';
		}

	}

	protected function defines(){
		define('OVALGURL', plugin_dir_url( __FILE__ ) );
		define('OVALGDIR', dirname( __FILE__ ) );
	}
	
}

// Initialize the plugin
add_action('init', 'ovameup_login');
function ovameup_login(){
	$personalize_login_pages_plugin = new Ova_Login_Plugin();
}





