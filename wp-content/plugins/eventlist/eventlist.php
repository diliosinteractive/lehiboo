<?php 
/**
 * Plugin Name: Event List
 * Description: Event List Plugin Allow to manage multiple events with ticket booking.
 * Plugin URI: https://ovatheme.com
 * Author: ovatheme.com
 * Version: 2.0.6
 * Author URI: ovatheme.com
 * Text Domain: eventlist
 * Domain Path: /languages/
*/

defined( 'ABSPATH' ) || exit;

// Define El_PLUGIN_FILE.
if ( ! defined( 'EL_PLUGIN_FILE' ) ) define( 'EL_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'EL_PLUGIN_PATH' ) ) define( 'EL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'EL_PLUGIN_INC' ) ) define( 'EL_PLUGIN_INC', EL_PLUGIN_PATH . 'includes/' );

if ( ! defined( 'EL_PLUGIN_URI' ) ) define( 'EL_PLUGIN_URI', plugins_url( '/', __FILE__ ) );



/**
 * Define prefix meta box
 */
define( 'OVA_METABOX_EVENT', 'ova_mb_event_' );

/**
 * Define categories for elementor
 */
define( 'OVA_ELEMENTOR_CAT', 'el_elementor_cat' );


// Include the main WooCommerce class.
if ( ! class_exists( 'EventList' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-eventlist.php';
}

/**
 * Returns the main instance of EL.
 *
 * @since  1.0
 * @return EventList
 */
function EL() {
	return EventList::instance();
}


$GLOBALS['eventlist'] = EL();
// Global for backwards compatibility.

/**
 * Plugin activation hook - Create analytics table
 */
register_activation_hook( __FILE__, 'el_plugin_activate' );
function el_plugin_activate() {
	require_once EL_PLUGIN_INC . 'class-el-analytics.php';
	EL_Analytics::create_table();

	// Flush rewrite rules
	flush_rewrite_rules();
}



