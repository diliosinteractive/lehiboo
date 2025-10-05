<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EL_Roles' ) ) {

	/**
	 * Class EL_Roles
	 */
	class EL_Roles {

		/**
		 * EL_Roles constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'add_roles' ) );
			
		}

		/**
		 * Add user roles.
		 */
		public static function add_roles() {


			/**
			 * Owner of event
			 */
			add_role(
				'el_event_manager',
				__( 'Event Manager', 'eventlist' ),
				array()
			);

			$event_cap			= 'el_event';
			$event_booking_cap	= 'el_manage_booking';
			$event_ticket_cap	= 'el_manage_ticket';
			

			$el_event_manager = get_role( 'el_event_manager' );

			$role_opt = EL()->options->role;

			// Cap Add Event
			if( $role_opt->get( 'add_event', 1 ) ){
				$el_event_manager->add_cap( 'add_' . $event_cap );
			}else{
				$el_event_manager->remove_cap( 'add_' . $event_cap );
			}
			
			// Cap Edit Event
			if( $role_opt->get( 'edit_event', 1 ) ){
				$el_event_manager->add_cap( 'edit_' . $event_cap );
			}else{
				$el_event_manager->remove_cap( 'edit_' . $event_cap );
			}

			// Cap Publish Event
			if( $role_opt->get( 'publish_event', 1 ) ){
				$el_event_manager->add_cap( 'publish_' . $event_cap );
			}else{
				$el_event_manager->remove_cap( 'publish_' . $event_cap );
			}

			// Cap Delete Event
			if( $role_opt->get( 'delete_event', 1 ) ){
				$el_event_manager->add_cap( 'delete_' . $event_cap );
			}else{
				$el_event_manager->remove_cap( 'delete_' . $event_cap );
			}


			// Cap Add Files
			if( $role_opt->get( 'upload_files', 1 ) ){
				$el_event_manager->add_cap( 'upload_files' );
			}else{
				$el_event_manager->remove_cap( 'upload_files' );
			}

			// Cap Manage Booking
			if( $role_opt->get( 'manage_booking', 1 ) ){
				$el_event_manager->add_cap( $event_booking_cap );
			}else{
				$el_event_manager->remove_cap( $event_booking_cap );
			}
			
			// Cap Manage Ticket
			if( $role_opt->get( 'manage_ticket', 1 ) ){
				$el_event_manager->add_cap( $event_ticket_cap );
			}else{
				$el_event_manager->remove_cap( $event_ticket_cap );
			}

			// Cap Create Tickets

			if ( $role_opt->get('create_tickets', '' ) ) {
				$el_event_manager->add_cap( 'el_create_tickets' );
			} else {
				$el_event_manager->remove_cap( 'el_create_tickets' );
			}
			

			if( $role_opt->get( 'user_upload_files', 1 ) ){
				$role = get_role( 'subscriber' );
				$role->add_cap( 'upload_files' ); 
			}else{
				$role = get_role( 'subscriber' );
				$role->remove_cap( 'upload_files' ); 
			}
		    
			
		}

		
		
	}
}




new EL_Roles();