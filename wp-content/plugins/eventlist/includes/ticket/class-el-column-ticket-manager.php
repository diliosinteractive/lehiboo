<?php
defined( 'ABSPATH' ) || exit();

if( !class_exists( 'EL_Column_Ticket_Manager' ) ){

	class EL_Column_Ticket_Manager{

		public function __construct(){

			add_action( 'manage_el_tickets_posts_custom_column', array( $this, 'event_tickets_posts_custom_column' ), 10, 2  );

			add_filter( 'manage_edit-el_tickets_sortable_columns', array( $this, 'posts_column_register_sortable_ticket') , 10 ,1 );

			add_filter( 'manage_edit-el_tickets_columns',array($this, 'event_tickets_replace_column_title_method_a' ) );

			add_action( 'pre_get_posts', array( $this, 'el_ticket_admin_search' ) );

			add_action( 'restrict_manage_posts', array( $this, 'el_filter_status_column_ticket' ) );

			add_filter( 'parse_query', array( $this, 'el_filter_status_column_ticket_update' ) );
		}
		

		public function event_tickets_posts_custom_column( $column_name, $post_id ) {

			
			if( $column_name == 'ticket_id' ){
				echo wp_kses_post( '<a href="'.get_edit_post_link( $post_id, 'edit' ).'">'.$post_id.'</a>' );
			}

			if( $column_name == 'booking_id' ){
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'booking_id', true ) );
			}

			if( $column_name == 'name_event' ){
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'name_event', true ) );
			}

			

			if ($column_name == 'name_customer') {
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'name_customer', true ) );		    	
			}

			if ($column_name == 'ticket_status') {
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'ticket_status', true ) );		    	
			}

			if ($column_name == 'seat') {
				$seat 			= get_post_meta( $post_id, OVA_METABOX_EVENT . 'seat', true );
				$person_type 	= get_post_meta( $post_id, OVA_METABOX_EVENT.'person_type', true );

				if ( $seat ) {
					if ( $person_type ) {
						$seat .= ' - '.$person_type;
					}
				} else {
					$seat = esc_html__("none", "eventlist");
				}
				
				echo esc_html( $seat );
			}

			if ( $column_name == "action" ) {
				?>
				<div class="el_ticket_action"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_ticket_nonce' ) ); ?>"
					data-ticket-id="<?php echo esc_attr( $post_id ); ?>">
					<a href="#" class="el_download_ticket"
						data-tippy-content="<?php esc_attr_e( 'Download', 'eventlist' ); ?>"
					>
						<span class="dashicons dashicons-download"></span>
					</a>

					<a href="#" class="el_send_ticket"
						data-tippy-content="<?php esc_attr_e( 'Send ticket', 'eventlist' ); ?>">
						<span class="dashicons dashicons-email-alt"></span>
					</a>
				</div>
				<?php
			}

			if ($column_name == 'address') {
				$arr_venue = get_post_meta( $post_id, OVA_METABOX_EVENT . 'venue', true );
				$address = get_post_meta( $post_id, OVA_METABOX_EVENT . 'address', true );

				$venue = is_array( $arr_venue ) ? implode(", ", $arr_venue) : $arr_venue;
				if( !empty( $venue ) ){
					echo wp_kses_post( esc_html__("Venue: ", "eventlist") . $venue . '<br>' );
				}
				if( $address ){
					echo wp_kses_post( esc_html__("Address: ", "eventlist") . $address . '<br>' );
				}
			}

			if ($column_name == 'qr_code') {
				echo esc_html( get_post_meta( $post_id, OVA_METABOX_EVENT . 'qr_code', true ) );
			}

			if ($column_name == 'start_date') {
				$start_date = get_post_meta( $post_id, OVA_METABOX_EVENT . 'date_start', true );
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');

				echo esc_html( date_i18n($date_format, $start_date) . ' - ' . date_i18n($time_format, $start_date) );
			}

			if ($column_name == 'end_date') {
				$end_date = get_post_meta( $post_id, OVA_METABOX_EVENT . 'date_end', true );
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');

				echo esc_html( date_i18n($date_format, $end_date) . ' - ' . date_i18n($time_format, $end_date) );
			}

		}

		public function event_tickets_replace_column_title_method_a( $columns ) {

			$columns = array(
				'cb' 			=> "<input type ='checkbox' />",
				'ticket_id' 	=> esc_html__( 'Ticket Number', "eventlist" ),	
				'booking_id' 	=> esc_html__( 'Booking ID', "eventlist" ),	
				'name_event' 	=> esc_html__( 'Event Name', "eventlist" ),
				'title' 		=> esc_html__( 'Ticket Type', "eventlist" ),
				'name_customer' => esc_html__( 'Customer Name', 'eventlist' ),
				'ticket_status' => esc_html__( 'Status', 'eventlist' ),
				'seat' 			=> esc_html__( 'Seat', 'eventlist' ),
				'action' 		=> esc_html__( 'Action', 'eventlist' ),
				'address' 		=> esc_html__( 'Venue & Address', 'eventlist' ),
				'qr_code' 		=> esc_html__( 'Qr code', 'eventlist' ),
				'start_date' 	=> esc_html__( "Start date", "eventlist" ),
				'end_date' 		=> esc_html__( "End date", "eventlist" ),
				'date' 			=> esc_html__( 'Date', 'eventlist' )
				
			);

			return $columns;  
		}

		
		function posts_column_register_sortable_ticket( $columns ) {
			$columns['ticket_id'] = 'ticket_id';
			return $columns;
		}


		/* Update search */
		

		public function el_ticket_admin_search( $query ) {
			

			global $pagenow, $wpdb;

		    // use your post type
		    $post_type = 'el_tickets';
		    // Use your Custom fields/column name to search for
		    $custom_fields = apply_filters( 'el_admin_ticket_search' , array(
		        OVA_METABOX_EVENT."booking_id",
		        OVA_METABOX_EVENT.'name_event',
		        OVA_METABOX_EVENT.'ticket_id',
		        OVA_METABOX_EVENT.'name_customer'
		    ), 10 );

		    if( ! is_admin() )
		        return;
		    

		    if ( !( 'edit.php' === $pagenow && isset($_GET['post_type']) && $post_type === $_GET['post_type'] && ! empty( $_GET['s'] ) ) )
		    	return;

		    $search_term = $query->query_vars['s'];

		    // Set to empty, otherwise it won't find anything
		    $query->query_vars['s'] = '';

		    if ( $search_term != '' ) {
		        $meta_query = array( 'relation' => 'OR' );

		        foreach( $custom_fields as $custom_field ) {
		            array_push( $meta_query, array(
		                'key' => $custom_field,
		                'value' => $search_term,
		                'compare' => 'LIKE'
		            ));
		        }

		        $query->set( 'meta_query', $meta_query );
		    };

		}

		public function el_filter_status_column_ticket(){
			global $typenow;
			global $wp_query;
			if ( $typenow == 'el_tickets' ) {
				$status = array(
					'not_checked' => __( 'Not Checked', 'eventlist' ),
					'checked' => __( 'Checked', 'eventlist' ),
				);
				$current_status = isset( $_GET['ticket_status'] ) ? sanitize_text_field( $_GET['ticket_status'] ) : '';
				?>
				<select name="ticket_status" id="ticket_status">
					<option value=""><?php esc_html_e( 'All status', 'eventlist' ); ?></option>
					<?php foreach( $status as $key => $value ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $current_status ); ?>><?php echo esc_html( $value ); ?></option>
					<?php } ?>
				</select>
				<?php
			}
		}

		public function el_filter_status_column_ticket_update( $query ){
			global $pagenow;
			// Get the post type
			$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
			if ( is_admin() && $pagenow =='edit.php' && $post_type == 'el_tickets' && isset( $_GET['ticket_status'] ) && $_GET['ticket_status'] !='' ) {

				$query->query_vars['meta_key'] = OVA_METABOX_EVENT.'ticket_status';
				$query->query_vars['meta_value'] = 'checked';

				if ( $_GET['ticket_status'] == 'checked' ) {
					$query->query_vars['meta_compare'] = '=';
				} else {
					$query->query_vars['meta_compare'] = '!=';
				}
				
			}
		}


	}
	new EL_Column_Ticket_Manager();

}