<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EL_Register_Widget' ) ) {

	/**
	 * Class EL_Roles
	 */
	class EL_Register_Widget {

		/**
		 * EL_Register_Widget constructor.
		 */
		public function __construct() {
			add_action( 'widgets_init', array( $this, 'el_widgets_event' ) );
		}

		
		function el_widgets_event() {
		  
		  $args_event = array(
		    'name' => esc_html__( 'Event Sidebar', 'eventlist'),
		    'id' => "single-event-sidebar",
		    'description' => esc_html__( 'Only display in Single Event', 'eventlist' ),
		    'class' => '',
		    'before_widget' => '<div id="%1$s" class="widget %2$s">',
		    'after_widget' => "</div>",
		    'before_title' => '<h4 class="widget-title">',
		    'after_title' => "</h4>",
		  );
		  register_sidebar( $args_event );

		  

		}
	}
}




new EL_Register_Widget();