<?php
/**
 * Setup menus in WP admin.
 *
 * @package EventList\Admin
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

class EL_Admin {

	/**
	 * Constructor
	 */
	public function __construct(){
		add_action( 'init', array( $this, 'includes' ) );
	}

	public function includes(){

		

		// Menus class
		require_once EL_PLUGIN_INC . 'admin/class-el-admin-menus.php';

		// Assets class
		require_once EL_PLUGIN_INC . 'admin/class-el-admin-assets.php';

		
		// Metabox class
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-basic.php';
		
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-booking.php';
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-ticket.php';
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-package.php';
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-membership.php';
		require_once EL_PLUGIN_INC . 'admin/metabox/class-el-admin-metabox-payout.php';

		require_once EL_PLUGIN_INC . 'admin/class-el-admin-metaboxes.php';

		// Ajax
		require_once EL_PLUGIN_INC . 'admin/class-el-admin-ajax.php';

		// User
		require_once EL_PLUGIN_INC . 'admin/class-el-admin-user.php';

		// Manage Event
		require_once EL_PLUGIN_INC . 'event/class-el-column-event.php';

		require_once EL_PLUGIN_INC . 'admin/class-el-payout-method.php';
		

	}
	
}

return new EL_Admin();