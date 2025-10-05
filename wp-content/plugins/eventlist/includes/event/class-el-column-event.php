<?php
defined( 'ABSPATH' ) || exit();

if( !class_exists( 'EL_Column_Event' ) ){

	class EL_Column_Event{

		public function __construct(){

			add_filter('manage_event_posts_columns', array( $this, 'el_event_columns_book_head' ) );
			add_action('manage_event_posts_custom_column', array( $this, 'el_columns_event_content'), 10, 2 );

		}

		// ADD TWO NEW COLUMNS
		function el_event_columns_book_head($defaults) {
		    $defaults['manage_event']  = esc_html__( 'Manage Event', 'eventlist' );
		    return $defaults;
		}
		 
		function el_columns_event_content($column_name, $post_ID) {
		    if ($column_name == 'manage_event') {
		    	
		    	$member_account_vendor = get_myaccount_page();
		    	$manage_event_link = add_query_arg( 
		    							array(
		    								'eid' => $post_ID,
		    								'vendor' => 'manage_event'
		    							), 
		    							$member_account_vendor 
		    						);
		    	
		    	if( $post_ID ){
		        	echo wp_kses_post( '<a href="'.$manage_event_link.'" target="_blank">'.esc_html__('Manage Event', 'eventlist').'</a>' );
		        }else{
		        	esc_html_e( 'Please choose Member Account Page in Event Setting', 'eventlist' );
		        }
		    }
		}


	}
	new EL_Column_Event();

}