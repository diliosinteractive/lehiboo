<?php defined( 'ABSPATH' ) || exit;

if( !class_exists( 'El_Ajax' ) ){
	class El_Ajax{

		/**
		 * @var bool
		 */
		protected static $_loaded = false;

		public function __construct(){

			if ( self::$_loaded ) {
				return;
			}
			
			if (!defined('DOING_AJAX') || !DOING_AJAX)
				return;

			$this->init();

			self::$_loaded = true;
		}

		public function init(){

			// Define All Ajax function
			$arr_ajax =  array(

				// Vendor Update Profile
				'el_update_profile',

				// Vendor Update Organisation (V1 Le Hiboo)
				'el_update_organisation',

				// Vendor Update Presentation (V1 Le Hiboo)
				'el_update_presentation',

				// Vendor Update Localisation (V1 Le Hiboo)
				'el_update_localisation',

				// Vendor Gallery Operations (V1 Le Hiboo)
				'el_delete_gallery_image',

				// Vendor Add Social
				'el_add_social',

				// Vendor Save Social
				'el_save_social',

				// Check Password
				'el_check_password',

				// Update Password
				'el_change_password',

				// User Upgrade to Vendor Role
				'el_update_role',

				'el_check_vendor_field_required',

				// Process Checkout
				'el_process_checkout',

				// Countdown Checkout
				'el_countdown_checkout',

				// Check User Login
				'el_check_user_login',

				// Check Login to view report
				'el_check_login_report',

				// Vendor update a post to pending status
				'el_pending_post',

				// Vendor update a post to publish status
				'el_publish_post',

				// Vendor Move a post to trash status
				'el_trash_post',

				// Vendor clone a post 
				'el_duplicate_post',

				// Vendor delete a post
				'el_delete_post',

				// Vendor Choose Buld Action
				'el_bulk_action',

				// Booking check discount
				'el_check_discount',

				// Load Location
				'el_load_location',

				// Save an event
				'el_save_edit_event',

				// Vendor export Booking to CSV
				'el_export_csv',

				// Vendor export Ticket to CSV
				'export_csv_ticket',

				// Vendor add a package
				'el_add_package',

				// The client add a event to wishlist
				'el_add_wishlist',

				// The client remove a event to wishlist
				'el_remove_wishlist',

				// The vendor update bank
				'el_update_payout_method',

				// Load location in search
				'el_load_location_search',

				// Search Map Page
				'el_search_map',

				// Display Event by filters in Elementor
				'el_filter_elementor_grid',

				// Send mail to vendor
				'el_single_send_mail_vendor',

				// Send mail when the client report an event
				'el_single_send_mail_report',

				// Update Ticket Status
				'el_update_ticket_status',

				// The customer cancel a booking
				'el_cancel_booking',

				//  add withdraw
				'el_add_withdrawal',

				// load schdules

				'el_load_schedules',

				// load ticket rest

				// 'el_load_ticket_rest',

                // chose calendar in manage sale
				'el_choose_calendar',

                //load edit ticket calendar in manage sale
				'el_load_edit_ticket_calendar',

				//	update ticket max

				'el_update_ticket_max',

				// check date search ticket

				'el_check_date_search_ticket',

				// multiple customers ticket
				'el_multiple_customers_ticket',

				// Upload files
				'el_upload_files',

				// Geocoding API
				'el_geocode',

				// Event List Default
				'el_event_default',

				// Event List Online
				'el_event_online',

				// Event List By Time
				'el_event_by_time',

				// Event Recent
				'el_event_recent',

				// recapcha
				'el_verify_google_recapcha',

				// Download Ticket received
				'el_ticket_received_download',

				// Remove Ticket PDF
				'el_fe_unlink_download_ticket',

				// Show list ticket
				'el_ticket_list',

				// Ticket Transfer
				'el_ticket_transfer',

				// Countdown expired
				'el_payment_countdown',

				// Ticket Manager
				// Download Ticket PDF
				'el_ticket_manager_download_ticket',
				'el_ticket_manager_remove_ticket_pdf',
				'el_ticket_manager_send_ticket',
				'el_ticket_manager_download_tickets',
				'el_ticket_manager_remove_all_ticket',
				'el_ticket_manager_create_tickets',
				'el_create_tickets_show_calendar',
				'el_create_tickets_show_tickets',
				'el_create_tickets_save',
				// Vendor cancel checkin ticket
				'el_cancel_check_in',
				'el_show_data_booking',
				'el_show_column_tickets',

				'el_booking_download_all_in_one',
				'el_export_booking_split_multi_file',
				'el_export_page_item',

				'el_ticket_download_all_in_one',
				'el_export_ticket_split_multi_file',
				'el_export_ticket_page_item',
				'el_event_speacial_pagination',

				'el_download_invoice',
				'el_download_tickets',
			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function el_download_tickets(){

			$data = [];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
	
				$ticket_download_zip = EL()->options->general->get('ticket_download_zip','no');

				$id_booking = isset($_POST['booking_id']) ? sanitize_text_field($_POST['booking_id']) : "";

				$status = get_post_meta($id_booking, OVA_METABOX_EVENT . "status", true);

				if ( $status !== "Completed" ) {
					$data['status'] = 'error';
					$data['message'] = __("Please update booking status to Complete to send mail", "eventlist"); 
					echo json_encode($data);
					wp_die();
				}

				$arr_upload = wp_upload_dir();
				$base_url_upload = $arr_upload['baseurl'];
				$base_dir_upload = $arr_upload['basedir'];


				$list_ticket_pdf_png = EL_Ticket::instance()->make_pdf_ticket_by_booking_id( $id_booking );

				$list_url_ticket = [];
				$list_dir_ticket = [];
				if (is_array($list_ticket_pdf_png) && !empty($list_ticket_pdf_png)) {
					foreach($list_ticket_pdf_png as $ticket_pdf) {
						$position = strrpos($ticket_pdf, '/');
						$name = substr($ticket_pdf, $position);
						$check_is_invoice = strrpos($ticket_pdf, 'invoices');
						if ( $check_is_invoice ) {
							$list_url_ticket[] = $base_url_upload .'/invoices'. $name;
							$list_dir_ticket[] = $base_dir_upload .'/invoices'. $name;
						} else {
							$list_url_ticket[] = $base_url_upload . $name;
							$list_dir_ticket[] = $base_dir_upload . $name;
						}
					}
				}
				
				$data['status'] = 'success';
				$data['list_url_ticket'] = $list_url_ticket;

				if ( $ticket_download_zip === 'yes' ) {
					$zip = new EL_Zip_Archive();
					$file_name = 'Booked_Ticket_'.$id_booking.'_'.current_time( 'timestamp' );
					$zip->add_zip_file( $base_dir_upload.'/'.$file_name.'.zip' ,$list_dir_ticket );
					$data['list_url_ticket'] = [$base_url_upload.'/'.$file_name.'.zip'];
					// delete file
					foreach ( $list_dir_ticket as $file ) {
						wp_delete_file( $file );
					}
				}
			}

			wp_send_json( $data );
		}

		public function el_download_invoice(){

			$response = [];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$booking_id = isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';

				$pdf_url = EL_Booking::instance()->el_make_pdf_invoice_by_booking_id( $booking_id );
				$invoices_dir = trailingslashit( wp_upload_dir()['baseurl'] ) . 'invoices';

				if ( $pdf_url ){
					$position 	= strrpos( $pdf_url, '/');
					$name 		= substr( $pdf_url, $position );
					$pdf_url 	= $invoices_dir . $name;
					$response['file_name'] 	= $name;
					$response['file_url'] 	= $pdf_url;
				}
			}
			
			wp_send_json( $response );
		}

		public function el_event_speacial_pagination(){
			$response = [];

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {

				$locations 		= isset( $_POST['locations'] ) && el_is_json( wp_unslash( $_POST['locations'] ) ) ? json_decode( wp_unslash( $_POST['locations'] ) ) : [];

				$categories 	= isset( $_POST['categories'] ) && el_is_json( wp_unslash( $_POST['categories'] ) ) ? json_decode( wp_unslash( $_POST['categories'] ) ) : [];

				$show_time 		= isset( $_POST['show_time'] ) ? sanitize_text_field( $_POST['show_time'] ) : 'yes';
				$display_date 	= isset( $_POST['display_date'] ) ? sanitize_text_field( $_POST['display_date'] ) : 'start';
				$display_price 	= isset( $_POST['display_price'] ) ? sanitize_text_field( $_POST['display_price'] ) : 'min';
				$filter_event 	= isset( $_POST['filter_event'] ) ? sanitize_text_field( $_POST['filter_event'] ) : 'all';
				$order 			= isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC';
				$orderby 		= isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'date';
				$total_count 	= isset( $_POST['total_count'] ) ? sanitize_text_field( $_POST['total_count'] ) : '5';
				$type_event 	= isset( $_POST['type_event'] ) ? sanitize_text_field( $_POST['type_event'] ) : 'type1';
				$column 		= isset( $_POST['column'] ) ? sanitize_text_field( $_POST['column'] ) : 'three_column';
				$page 			= isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : '1';
				$display_img 	= isset( $_POST['display_img'] ) ? sanitize_text_field( $_POST['display_img'] ) : '';

				$args = array(
					'locations' 	=> $locations,
					'categories' 	=> $categories,
					'show_time' 	=> $show_time,
					'display_date' 	=> $display_date,
					'display_price' => $display_price,
					'filter_event' 	=> $filter_event,
					'order' 		=> $order,
					'order_by' 		=> $orderby,
					'total_count' 	=> $total_count,
					'type_event' 	=> $type_event,
					'column' 		=> $column,
					'page' 			=> $page,
					'display_img' 	=> $display_img,
				);

				$events = EL_Event::el_get_special_events( $args );
				ob_start();
				?>
				<div class="event_archive <?php echo esc_attr( $type_event ); ?> <?php echo esc_attr( $column ); ?>" >

					<div class="wrap_loader">
						<svg class="loader" width="50" height="50">
							<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
							<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
						</svg>
					</div>
					<?php
					if( $events->have_posts() ) :
						while( $events->have_posts() ) : $events->the_post();
							el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ), $args );
						endwhile; wp_reset_postdata();
					else :
						?>
						<h3 class="event-notfound"><?php esc_html_e( 'Event not found', 'eventlist' ); ?></h3>
						<?php 
					endif;
					?>
				</div>
				<?php
				$response['event_html'] = ob_get_clean();

				ob_start();
				if ( $events->have_posts() && $events->max_num_pages > 1 ) {
					
					el_get_template( 'pagination-ajax.php', array(
						'events' 	=> $events,
						'per_page' 	=> $total_count,
						'page' 		=> $page,
					) );

				}

				$response['pagination_html'] = ob_get_clean();
			}

			wp_send_json( $response );
		}

		public function el_export_ticket_page_item(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				
				EL_Ticket::export_csv();
			}

			wp_die();
		}

		public function el_export_ticket_split_multi_file(){
	
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$ticket_ids = isset( $_POST['ticket_ids'] ) ? json_decode( sanitize_text_field( $_POST['ticket_ids'] ) ) : [];
				$number_file = isset( $_POST['number_file'] ) ? absint( sanitize_text_field( $_POST['number_file'] ) ) : 1;

			
				$arr_chunk = array_chunk( $ticket_ids, $number_file );
				el_get_template( 'vendor/export-ticket-pagination.php', array( 'arr_chunk' => $arr_chunk ) );
			}
			wp_die();
		}

		public function el_ticket_download_all_in_one(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				
				EL_Ticket::export_csv();
			}

			wp_die();
		}

		public function el_export_page_item(){
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				
				EL_Booking::export_csv();
			}

			wp_die();
		}

		public function el_export_booking_split_multi_file(){
			$html = '';
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$booking_ids = isset( $_POST['booking_ids'] ) ? json_decode( sanitize_text_field( $_POST['booking_ids'] ) ) : [];
				$number_file = isset( $_POST['number_file'] ) ? absint( sanitize_text_field( $_POST['number_file'] ) ) : 1;

			
				$arr_chunk = array_chunk( $booking_ids, $number_file );
	
				el_get_template( 'vendor/export-booking-pagination.php', array( 'arr_chunk' => $arr_chunk ) );
		
	
			}

	
			wp_die();
		}

		public function el_booking_download_all_in_one(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				
				EL_Booking::export_csv();
			}

			wp_die();
		}

		public function el_show_column_tickets(){
			$html = '';
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$id_event = isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';
				el_get_template( '/vendor/__events_table_tickets.php', array( 'post_id' => $id_event ) ); 
			}

			wp_die();
		}

		public function el_show_data_booking(){
			$response = [];
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$id_event 	= isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';
				$name 		= isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
				$response['name'] = $name;
				$response['result'] = EL_Booking::instance()->get( $id_event, $name );
			}

			wp_send_json( $response );
		}

		public function el_cancel_check_in(){
			$response = array(
				'status' 	=> 'error',
				'mess' 		=> esc_html__( 'Cancel check-in failed', 'eventlist' ),
			);

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_cancel_checkin' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {
				$ticket_id = sanitize_text_field( $_POST['ticket_id'] ) ?? "";

				update_post_meta( $ticket_id, OVA_METABOX_EVENT."ticket_status", "" );
				update_post_meta( $ticket_id, OVA_METABOX_EVENT."times_checked", "" );

				$response['status'] = 'success';
				$response['mess'] = esc_html__( 'Canceled check in successfully', 'eventlist' );
			}

			echo json_encode( $response );
			wp_die();
		}

		public function el_create_tickets_save(){
			
			$response = array(
				'status' 	=> 'error',
				'mess' 		=> esc_html__( 'Creating ticket failed', 'eventlist' ),
			);

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_create_tickets_nonce' ) &&
				( el_is_administrator() || el_can_create_tickets() ) ) {
				$post_data = recursive_sanitize_text_field( $_POST );
				$response = EL_Booking::instance()->add_booking_manually( $post_data );
			}
			
			echo json_encode( $response );
			wp_die();
		}

		public function el_create_tickets_show_tickets(){

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_create_tickets_nonce' ) &&
				( el_is_administrator() || el_can_create_tickets() ) ) {
				$event_id 		= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : "";
				$calendar_id 	= isset( $_POST['calendar_id'] ) ? sanitize_text_field( $_POST['calendar_id'] ) : "";

				$seat_option 	= get_post_meta( $event_id, OVA_METABOX_EVENT."seat_option", true );
				$tickets 		= get_post_meta( $event_id, OVA_METABOX_EVENT."ticket", true );
				$ticket_map 	= get_post_meta( $event_id, OVA_METABOX_EVENT."ticket_map", true );
				$services 		= get_post_meta( $event_id, OVA_METABOX_EVENT."extra_service", true );
				$extra_service 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'extra_service', true );
				$extra_service_rest = el_extra_sv_get_rest_qty( $event_id, $calendar_id );
				$data_extra_service = el_extra_sv_get_data_rest( $extra_service, $extra_service_rest );

				$seat_booked = EL_Booking::instance()->get_list_seat_map_booked( $event_id, $calendar_id );
				$seat_holding_ticket = EL_Booking::instance()->get_list_seat_holding_ticket( $event_id, $calendar_id );

				$seat_available = EL_Booking::instance()->el_get_area_qty_available( $event_id, $calendar_id );

				$args = array(
					'seat_option' 			=> $seat_option,
					'tickets' 				=> $tickets,
					'ticket_map' 			=> $ticket_map,
					'event_id' 				=> $event_id,
					'calendar_id' 			=> $calendar_id,
					'data_extra_service' 	=> $data_extra_service,
					'seat_booked' 			=> $seat_booked,
					'seat_holding_ticket' 	=> $seat_holding_ticket,
					'seat_available' 		=> $seat_available,	
				);

				el_get_template("/vendor/create-tickets-calendar-content.php", $args );
			}

			wp_die();
		}

		public function el_create_tickets_show_calendar(){
		

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_create_tickets_nonce' ) &&
				( el_is_administrator() || el_can_create_tickets() ) ) {
				
				$date_format 		= get_option("date_format");
				$event_id 			= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : "";
				$list_calendar 		= get_arr_list_calendar_by_id_event( $event_id );
				$option_calendar 	= get_post_meta( $event_id, OVA_METABOX_EVENT . 'option_calendar', true );
				$seat_option 		= get_post_meta( $event_id, OVA_METABOX_EVENT."seat_option", true );
				
				$args = array(
					'list_calendar' 	=> $list_calendar,
					'date_format' 		=> $date_format,
					'option_calendar' 	=> $option_calendar,
					'seat_option' 		=> $seat_option,
				);

				el_get_template( "/vendor/create-tickets-calendar.php", $args );
			}


			wp_die();
		}

		public function el_ticket_manager_create_tickets(){
	

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				( el_is_administrator() || el_can_create_tickets() ) ) {
				$user 			= wp_get_current_user();
				$user_id 		= $user->ID;
				$event_titles 	= EL_Event::get_data_title_by_author( $user_id );
				$event_id 		= sanitize_text_field( $_POST['event_id'] ) ?? "";
				$args = array(
					'event_titles' 	=> $event_titles,
					'event_id' 		=> $event_id,
				);

				el_get_template( "/vendor/create-tickets-content.php", $args );
			}
	
			wp_die();
		}

		public function el_ticket_manager_remove_all_ticket(){

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {

				$file_names = isset( $_POST['file_names'] ) ? recursive_sanitize_text_field( $_POST['file_names'] ) : array();
				$upload_dir = wp_upload_dir();

				if ( is_array( $file_names ) && count( $file_names ) > 0 ) {

					foreach ( $file_names as $file_name ) {
						$file_path = trailingslashit( $upload_dir['basedir'] ).$file_name;
						if ( file_exists( $file_path ) ) {
							wp_delete_file( $file_path );
						}
					}
				}
			}

	
			wp_die();
		}

		public function el_ticket_manager_download_tickets(){

			$response = array(
				'status' 		=> 'error',
				'mess' 			=> esc_html__( 'Downloading tickets failed', 'eventlist' ),
				'file_urls'		=> array(),
				'file_names'	=> array(),
			);

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {
				$event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : "";

				$ticket_ids = isset( $_POST['ticket_ids'] ) ? recursive_sanitize_text_field( $_POST['ticket_ids'] ) : array();

				$file_urls 	= array();
				$file_names = array();
				$file_paths = array();

				$pdf = new EL_PDF();
				$upload_dir = wp_upload_dir();

				if ( count( $ticket_ids ) > 0 ) {
					foreach ( $ticket_ids as $ticket_id ) {
						try {
							$file_path = $pdf->make_pdf_ticket( $ticket_id );
							
							if ( file_exists( $file_path ) ) {
								$file_name 		= basename( $file_path );
								$file_url 		= trailingslashit( $upload_dir['baseurl'] ).$file_name;
								$file_urls[] 	= $file_url;
								$file_names[] 	= $file_name;
								$file_paths[] 	= $file_path;
							}

						} catch ( Exception $e ) {
							$response['mess'] = esc_html__( 'Error: ', 'eventlist' ).$e->getMessage()."\n";
							echo json_encode( $response );
							wp_die();
						}
					}
				}

				if ( ! empty( $file_urls ) && ! empty( $file_names ) && ! empty( $file_paths ) ) {

					$download_zip = EL()->options->general->get("ticket_download_zip","no");
					$zip_archive = new EL_Zip_Archive();

					if ( $download_zip === "yes" && count( $ticket_ids ) > 1 ) {
						$current_time = current_time( 'timestamp' );
						$zip_name 	= "Tickets_".$current_time.".zip";
						$zip_path 	= trailingslashit( $upload_dir['basedir'] ).$zip_name;
						$zip_archive->add_zip_file( $zip_path, $file_paths );

						if ( file_exists( $zip_path ) ) {
							$zip_url = trailingslashit( $upload_dir['baseurl'] ).$zip_name;
							$response['file_urls'][] 	= $zip_url;
							$response['file_names'][] 	= $zip_name;
						}

						// Delete files
						foreach ( $file_paths as $file ) {
							if ( file_exists( $file ) ) {
								wp_delete_file( $file );
							}
						}

					} else {
						$response['file_urls'] = $file_urls;
						$response['file_names'] = $file_names;
					}

					$response['status'] = 'success';
				}

			}

			echo json_encode( $response );
			wp_die();
		}

		public function el_ticket_manager_send_ticket(){
			$response = array(
				'status' 	=> 'error',
				'mess' 		=> esc_html__( 'Sending ticket email failed', 'eventlist' )
			);

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {
				$ticket_id = isset( $_POST['ticket_id'] ) ? sanitize_text_field( $_POST['ticket_id'] ) : "";

				$send_mail = el_send_ticket_mail( $ticket_id );

				if ( $send_mail ) {
					$response['status'] = 'success';
					$response['mess'] 	= esc_html__( 'Ticket sent successfully', 'eventlist' );
				}
			}

			echo json_encode( $response );
			wp_die();
		}

		public function el_ticket_manager_download_ticket(){

			$response = array(
				'status' 	=> 'error',
				'mess' 		=> '',
				'file_url'	=> '',
				'file_name'	=> '',
			);

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {
				$ticket_id = isset( $_POST['ticket_id'] ) ? sanitize_text_field( $_POST['ticket_id'] ) : "";
				
				try {
					$pdf = new EL_PDF();
					$file_path = $pdf->make_pdf_ticket( $ticket_id );
					
					if ( file_exists( $file_path) ) {
						$file_name 	= basename( $file_path );
						$upload_dir = wp_upload_dir();
						$file_url 	= trailingslashit( $upload_dir['baseurl'] ).$file_name;
					    $response['file_url'] 	= $file_url;
					    $response['file_name'] 	= $file_name;
					    $response['status'] 	= 'success';
					}

				} catch ( Exception $e ) {
					$response['mess'] = esc_html__( 'Error: ', 'eventlist' ).$e->getMessage()."\n";
				}
			}

			echo json_encode( $response );
			wp_die();
		}

		public function el_ticket_manager_remove_ticket_pdf(){

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator', 'el_event_manager' );

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_action' ) &&
				array_intersect( $allowed_roles, $user->roles ) ) {
				$file_name 	= isset( $_POST['file_name'] ) ? sanitize_file_name( $_POST['file_name']) : "";
				$upload_dir = wp_upload_dir();
				$file_path 	= trailingslashit( $upload_dir['basedir'] ).$file_name;
				if ( file_exists( $file_path ) ) {
					wp_delete_file( $file_path );
				}
			}
			echo "";
			wp_die();
		}


		//el_load_schedules

		public static function el_load_schedules() {
		    	/**
				* Hook: el_single_event_schedules_time - 10
		        * @hooked:  el_single_event_schedules_time - 10
				*/
				do_action( 'el_single_event_schedules_time' );

				wp_die();

		}

		//el_load_ticket_rest


		public static function el_load_ticket_rest() {
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$show_remaining_tickets = EL()->options->event->get('show_remaining_tickets', 'yes');

			if ( $show_remaining_tickets != 'yes' ) return;

			$post_data 				= $_POST['data'];
			$time_value 			= isset( $post_data['time_value'] ) ? sanitize_text_field( $post_data['time_value'] ) : '';
			$id 					= isset( $post_data['ide'] ) ? sanitize_text_field( $post_data['ide'] ) : '';
			$date_format 			= get_option('date_format');
			$schedules_time 		= get_post_meta( $id, OVA_METABOX_EVENT . 'schedules_time', true );
			$list_type_ticket 		= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true );
			$calendar_recurrence 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence', true );
			$seat_option 			= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true );
			$recurrence_frequency 	= get_post_meta( $id, OVA_METABOX_EVENT . 'recurrence_frequency', true );
			$ts_start 				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_start', true );
			$ts_end  				= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_end', true );

			// Time Slot
			$is_timeslot = false;

			if ( $recurrence_frequency === 'weekly' && ! empty( $ts_start ) && ! empty( $ts_end ) ) {
				$is_timeslot = true;
			}

			$ticket_rest = array();

			if ( $calendar_recurrence ) {
				foreach ( $calendar_recurrence as $key_rec => $value_rec ) {
					if ( $is_timeslot ) {
						foreach ( $ts_start as $ts_key => $ts_value ) {
							if ( ! empty( $ts_value ) && is_array( $ts_value ) ) {
								foreach ( $ts_value as $ts_key_time => $ts_time ) {
									if ( $value_rec['calendar_id'] == $time_value.$ts_key.$ts_key_time ) {
										$total_number_ticket_rest = 0;

										if ( $total_number_ticket_rest == 1 ) {
											$ticket_text = esc_html__( 'ticket', 'eventlist' );
										} else {
											$ticket_text = esc_html__( 'tickets', 'eventlist' );
										}

										if ( $show_remaining_tickets == 'yes' ) { 
											if ( $seat_option != 'map' ) {
												foreach ( $list_type_ticket as $ticket ) {
													$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest( $id, $time_value.$ts_key.$ts_key_time,  $ticket['ticket_id']);

													$total_number_ticket_rest += $number_ticket_rest;
												}
											} else {
												$total_number_ticket_rest = EL_Booking::instance()->get_number_ticket_map_rest( $id, $time_value.$ts_key.$ts_key_time );
											}

											$number_ticket_text = '<span class="calendar_ticket_rest">('.$total_number_ticket_rest.'&nbsp;<span>'.$ticket_text.'</span>)</span>';
										} else {
											$number_ticket_text = '';
										}

										$ticket_rest[] = [
											'ticket' => $number_ticket_text,
											'id_cal' => $time_value.$ts_key.$ts_key_time,
										];
									}
								}
							}
						}
					} else {
						if ( $schedules_time ) {
							foreach ( $schedules_time as $key => $value ) {
								$total_number_ticket_rest = 0;

								if ( $total_number_ticket_rest == 1 ) {
									$ticket_text = esc_html__( 'ticket', 'eventlist' );
								} else {
									$ticket_text = esc_html__( 'tickets', 'eventlist' );
								}

								if ( $show_remaining_tickets == 'yes' ) { 
									if ( $seat_option != 'map' ) {
										foreach ( $list_type_ticket as $ticket ) {
											$number_ticket_rest = EL_Booking::instance()->get_number_ticket_rest( $id, $time_value.$key,  $ticket['ticket_id']);

											$total_number_ticket_rest += $number_ticket_rest;
										}
									} else {
										$total_number_ticket_rest = EL_Booking::instance()->get_number_ticket_map_rest($id, $time_value.$key);
									}

									$number_ticket_text = '<span class="calendar_ticket_rest">('.$total_number_ticket_rest.'&nbsp;<span>'.$ticket_text.'</span>)</span>';
								} else {
									$number_ticket_text = '';
								}

								if ( $value_rec['calendar_id'] == $time_value.$key ) {
									$ticket_rest[] = [
										'ticket' => $number_ticket_text,
										'id_cal' => $time_value.$key,
									];
								}
							}
						}
					}
				}
			}

			echo json_encode( $ticket_rest );
			wp_die();
		}

		// Update Ticket Status
		public static function el_update_ticket_status() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];

			$qr_code = $post_data['qr_code'];
			$ticket_info = EL_Ticket::validate_qrcode( array( 'check_qrcode' => $qr_code ) );

			echo json_encode( $ticket_info );
			wp_die();

		}

		public static function el_update_profile(){
			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data 	= $_POST['data'];

			$user_id 	= wp_get_current_user()->ID;

			$admin_approve_vendor = OVALG_Settings::admin_approve_vendor();
			$vendor_status = get_user_meta( $user_id, 'vendor_status', true );

			if( !isset( $post_data['el_update_profile_nonce'] ) || !wp_verify_nonce( $post_data['el_update_profile_nonce'], 'el_update_profile_nonce' ) ) return ;

			$first_name = isset( $post_data['first_name'] ) ? sanitize_text_field( $post_data['first_name'] ) : '';
			$last_name = isset( $post_data['last_name'] ) ? sanitize_text_field( $post_data['last_name'] ) : '';
			$display_name = isset( $post_data['display_name'] ) ? sanitize_text_field( $post_data['display_name'] ) : '';
			$user_email = isset( $post_data['user_email'] ) ? sanitize_email( $post_data['user_email'] ) : '';
			$user_job = isset( $post_data['user_job'] ) ? sanitize_text_field( $post_data['user_job'] ) : '';
			$user_phone = isset( $post_data['user_phone'] ) ? sanitize_text_field( $post_data['user_phone'] ) : '';
			$user_professional_email = isset( $post_data['user_professional_email'] ) ? sanitize_email( $post_data['user_professional_email'] ) : ''; // V1 Le Hiboo
			$user_address = isset( $post_data['user_address'] ) ? sanitize_text_field( $post_data['user_address'] ) : '';
			$description = isset( $post_data['description'] ) ? sanitize_textarea_field( $post_data['description'] ) : '';
			$author_id_image = isset( $post_data['author_id_image'] ) ? sanitize_text_field( $post_data['author_id_image'] ) : '';

			// V1 Le Hiboo - Validation de l'email unique
			if ( !empty( $user_email ) ) {
				$current_user = get_userdata( $user_id );
				// Vérifier si l'email a changé
				if ( $current_user->user_email !== $user_email ) {
					// Vérifier si l'email existe déjà
					if ( email_exists( $user_email ) ) {
						wp_send_json_error( array(
							'message' => __( 'Cette adresse email est déjà utilisée par un autre compte.', 'eventlist' )
						) );
						wp_die();
					}
				}
			}

			wp_update_user( array( 'ID' => $user_id, 'display_name' =>  $display_name, 'user_email' => $user_email ) );

			update_user_meta( $user_id, 'first_name', $first_name );
			update_user_meta( $user_id, 'last_name', $last_name );
			update_user_meta( $user_id, 'user_job', $user_job );
			update_user_meta( $user_id, 'user_phone', $user_phone );
			update_user_meta( $user_id, 'user_professional_email', $user_professional_email ); // V1 Le Hiboo
			update_user_meta( $user_id, 'user_address', $user_address );
			update_user_meta( $user_id, 'description', $description );
			update_user_meta( $user_id, 'author_id_image', $author_id_image );


			do_action( 'el_update_meta_user', $user_id, $post_data );

			if ( $admin_approve_vendor !== 'no' ) {
				delete_user_meta( $user_id, 'vendor_status', 'reject' );
			}

			return true;
			wp_die();

		}

		/* Add Social */
		public static function el_add_social() {

			if( !isset( $_POST['data'] ) ) wp_die();
			
			$post_data = $_POST['data'];
			$index = isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';

			?>
			<div class="social_item vendor_field">
				<input type="text" name="<?php echo esc_attr('user_profile_social['.$index.'][link]'); ?>" class="link_social" value="" placeholder="<?php echo esc_attr( 'https://' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
				<select name="<?php echo esc_attr('user_profile_social['.$index.'][icon]'); ?>" class="icon_social">
					<?php foreach (el_get_social() as $key => $value) { ?>
						<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html( $value ); ?></option>
					<?php } ?>
				</select>
				<button class="button remove_social">x</button>
			</div>
			<?php

			wp_die();
		}

		/* Save Social */
		public static function el_save_social() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;
			if( !isset( $post_data['el_update_social_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_update_social_nonce'] ), 'el_update_social_nonce' ) ) return ;

			$post_data_sanitize = array();

			foreach ($post_data as $key => $value) {
				if ( is_array($value) ) {
					foreach ($value as $k1 => $v1) {
						$post_data_sanitize[$key][$k1][0] = esc_url_raw( $post_data[$key][$k1][0] );
						$post_data_sanitize[$key][$k1][1] = sanitize_text_field( $post_data[$key][$k1][1] );
					}
				} else {
					$post_data_sanitize[$key] = sanitize_text_field( $post_data[$key] );
				}
			}

			if ( !isset( $post_data_sanitize['user_profile_social'] ) ) {
				$post_data_sanitize['user_profile_social'] = array();
			}

			foreach($post_data_sanitize as $key => $value) {
				update_user_meta( $user_id, $key, $value );
			}

			wp_die();
		}

		/**
		 * V1 Le Hiboo - Update Organisation
		 * Sauvegarde les informations de l'organisation du partenaire
		 */
		public static function el_update_organisation(){
			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;

			// Vérifier le nonce
			if( !isset( $post_data['el_update_organisation_nonce'] ) ||
			    !wp_verify_nonce( $post_data['el_update_organisation_nonce'], 'el_update_organisation_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Erreur de sécurité', 'eventlist' ) ) );
				wp_die();
			}

			// Vérifier que l'utilisateur est vendor
			if( !el_is_vendor() ) {
				wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
				wp_die();
			}

			// Sanitize et enregistrer les données
			$org_name = isset( $post_data['org_name'] ) ? sanitize_text_field( $post_data['org_name'] ) : '';
			$org_display_name = isset( $post_data['org_display_name'] ) ? sanitize_text_field( $post_data['org_display_name'] ) : '';
			$org_statut_juridique = isset( $post_data['org_statut_juridique'] ) ? sanitize_text_field( $post_data['org_statut_juridique'] ) : '';
			$org_forme_juridique = isset( $post_data['org_forme_juridique'] ) ? sanitize_text_field( $post_data['org_forme_juridique'] ) : '';
			$org_siren = isset( $post_data['org_siren'] ) ? sanitize_text_field( $post_data['org_siren'] ) : '';
			$org_date_creation = isset( $post_data['org_date_creation'] ) ? sanitize_text_field( $post_data['org_date_creation'] ) : '';
			$org_nombre_effectifs = isset( $post_data['org_nombre_effectifs'] ) ? sanitize_text_field( $post_data['org_nombre_effectifs'] ) : '';

			// Adresse (fusionnée depuis Localisation)
			$user_address_line1 = isset( $post_data['user_address_line1'] ) ? sanitize_text_field( $post_data['user_address_line1'] ) : '';
			$user_address_line2 = isset( $post_data['user_address_line2'] ) ? sanitize_text_field( $post_data['user_address_line2'] ) : '';
			$user_city = isset( $post_data['user_city'] ) ? sanitize_text_field( $post_data['user_city'] ) : '';
			$user_postcode = isset( $post_data['user_postcode'] ) ? sanitize_text_field( $post_data['user_postcode'] ) : '';
			$user_country = isset( $post_data['user_country'] ) ? sanitize_text_field( $post_data['user_country'] ) : '';

			// GPS
			$org_latitude = isset( $post_data['org_latitude'] ) ? sanitize_text_field( $post_data['org_latitude'] ) : '';
			$org_longitude = isset( $post_data['org_longitude'] ) ? sanitize_text_field( $post_data['org_longitude'] ) : '';
			$org_address_visible = isset( $post_data['org_address_visible'] ) ? 'yes' : 'no';

			// Tableaux (checkboxes multiples)
			$org_role = isset( $post_data['org_role'] ) && is_array( $post_data['org_role'] )
				? array_map( 'sanitize_text_field', $post_data['org_role'] )
				: array();

			$org_type_structure = isset( $post_data['org_type_structure'] ) && is_array( $post_data['org_type_structure'] )
				? array_map( 'sanitize_text_field', $post_data['org_type_structure'] )
				: array();

			// Validation SIREN (9 chiffres)
			if( !empty( $org_siren ) && !preg_match( '/^[0-9]{9}$/', $org_siren ) ) {
				wp_send_json_error( array( 'message' => __( 'Le SIREN doit contenir exactement 9 chiffres', 'eventlist' ) ) );
				wp_die();
			}

			// Enregistrer les meta
			update_user_meta( $user_id, 'org_name', $org_name );
			update_user_meta( $user_id, 'org_display_name', $org_display_name );
			update_user_meta( $user_id, 'org_role', $org_role );
			update_user_meta( $user_id, 'org_statut_juridique', $org_statut_juridique );
			update_user_meta( $user_id, 'org_forme_juridique', $org_forme_juridique );
			update_user_meta( $user_id, 'org_type_structure', $org_type_structure );
			update_user_meta( $user_id, 'org_siren', $org_siren );
			update_user_meta( $user_id, 'org_date_creation', $org_date_creation );
			update_user_meta( $user_id, 'org_nombre_effectifs', $org_nombre_effectifs );

			// Adresse
			update_user_meta( $user_id, 'user_address_line1', $user_address_line1 );
			update_user_meta( $user_id, 'user_address_line2', $user_address_line2 );
			update_user_meta( $user_id, 'user_city', $user_city );
			update_user_meta( $user_id, 'user_postcode', $user_postcode );
			update_user_meta( $user_id, 'user_country', $user_country );

			// GPS
			update_user_meta( $user_id, 'org_latitude', $org_latitude );
			update_user_meta( $user_id, 'org_longitude', $org_longitude );
			update_user_meta( $user_id, 'org_address_visible', $org_address_visible );

			wp_send_json_success( array( 'message' => __( 'Informations de l\'organisation enregistrées avec succès', 'eventlist' ) ) );
			wp_die();
		}

		/**
		 * V1 Le Hiboo - Update Presentation
		 * Sauvegarde les informations de présentation (profil public) du partenaire
		 */
		public static function el_update_presentation(){
			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;

			// Vérifier le nonce
			if( !isset( $post_data['el_update_presentation_nonce'] ) ||
			    !wp_verify_nonce( $post_data['el_update_presentation_nonce'], 'el_update_presentation_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Erreur de sécurité', 'eventlist' ) ) );
				wp_die();
			}

			// Vérifier que l'utilisateur est vendor
			if( !el_is_vendor() ) {
				wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
				wp_die();
			}

			// Sanitize et enregistrer les données
			$description = isset( $post_data['description'] ) ? sanitize_textarea_field( $post_data['description'] ) : '';

			// Bloquer les URLs dans la description
			if( preg_match( '/(http|https|www\.)/i', $description ) ) {
				wp_send_json_error( array( 'message' => __( 'Les liens URL ne sont pas autorisés dans la description', 'eventlist' ) ) );
				wp_die();
			}

			$org_cover_image = isset( $post_data['org_cover_image'] ) ? absint( $post_data['org_cover_image'] ) : 0;
			$org_email_contact = isset( $post_data['org_email_contact'] ) ? sanitize_email( $post_data['org_email_contact'] ) : '';
			$org_phone_contact = isset( $post_data['org_phone_contact'] ) ? sanitize_text_field( $post_data['org_phone_contact'] ) : '';
			$org_web = isset( $post_data['org_web'] ) ? esc_url_raw( $post_data['org_web'] ) : '';
			$org_video_youtube = isset( $post_data['org_video_youtube'] ) ? esc_url_raw( $post_data['org_video_youtube'] ) : '';

			// Nouveaux champs CDC
			$org_event_type = isset( $post_data['org_event_type'] ) ? sanitize_text_field( $post_data['org_event_type'] ) : '';
			$org_stationnement = isset( $post_data['org_stationnement'] ) ? sanitize_textarea_field( $post_data['org_stationnement'] ) : '';
			$org_pmr = isset( $post_data['org_pmr'] ) ? sanitize_text_field( $post_data['org_pmr'] ) : '';
			$org_pmr_infos = isset( $post_data['org_pmr_infos'] ) ? sanitize_textarea_field( $post_data['org_pmr_infos'] ) : '';
			$org_restauration = isset( $post_data['org_restauration'] ) ? sanitize_text_field( $post_data['org_restauration'] ) : '';
			$org_restauration_infos = isset( $post_data['org_restauration_infos'] ) ? sanitize_textarea_field( $post_data['org_restauration_infos'] ) : '';
			$org_boisson = isset( $post_data['org_boisson'] ) ? sanitize_text_field( $post_data['org_boisson'] ) : '';
			$org_boisson_infos = isset( $post_data['org_boisson_infos'] ) ? sanitize_textarea_field( $post_data['org_boisson_infos'] ) : '';

			// Enregistrer les meta
			update_user_meta( $user_id, 'description', $description );
			update_user_meta( $user_id, 'org_cover_image', $org_cover_image );
			update_user_meta( $user_id, 'org_email_contact', $org_email_contact );
			update_user_meta( $user_id, 'org_phone_contact', $org_phone_contact );
			update_user_meta( $user_id, 'org_web', $org_web );
			update_user_meta( $user_id, 'org_video_youtube', $org_video_youtube );
			update_user_meta( $user_id, 'org_event_type', $org_event_type );
			update_user_meta( $user_id, 'org_stationnement', $org_stationnement );
			update_user_meta( $user_id, 'org_pmr', $org_pmr );
			update_user_meta( $user_id, 'org_pmr_infos', $org_pmr_infos );
			update_user_meta( $user_id, 'org_restauration', $org_restauration );
			update_user_meta( $user_id, 'org_restauration_infos', $org_restauration_infos );
			update_user_meta( $user_id, 'org_boisson', $org_boisson );
			update_user_meta( $user_id, 'org_boisson_infos', $org_boisson_infos );

			wp_send_json_success( array( 'message' => __( 'Présentation enregistrée avec succès', 'eventlist' ) ) );
			wp_die();
		}

		/**
		 * V1 Le Hiboo - Update Localisation
		 */
		public static function el_update_localisation(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;

			// Vérifier le nonce
			if( !isset( $post_data['el_update_localisation_nonce'] ) ||
				!wp_verify_nonce( $post_data['el_update_localisation_nonce'], 'el_update_localisation_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Erreur de sécurité', 'eventlist' ) ) );
				wp_die();
			}

			// Sanitize
			$user_country = isset( $post_data['user_country'] ) ? sanitize_text_field( $post_data['user_country'] ) : '';
			$user_city = isset( $post_data['user_city'] ) ? sanitize_text_field( $post_data['user_city'] ) : '';
			$user_postcode = isset( $post_data['user_postcode'] ) ? sanitize_text_field( $post_data['user_postcode'] ) : '';
			$user_address_line1 = isset( $post_data['user_address_line1'] ) ? sanitize_text_field( $post_data['user_address_line1'] ) : '';
			$user_address_line2 = isset( $post_data['user_address_line2'] ) ? sanitize_text_field( $post_data['user_address_line2'] ) : '';

			// Validation - champs obligatoires
			if( empty( $user_country ) || empty( $user_city ) || empty( $user_postcode ) ) {
				wp_send_json_error( array( 'message' => __( 'Le pays, la ville et le code postal sont obligatoires', 'eventlist' ) ) );
				wp_die();
			}

			// Enregistrer les meta
			update_user_meta( $user_id, 'user_country', $user_country );
			update_user_meta( $user_id, 'user_city', $user_city );
			update_user_meta( $user_id, 'user_postcode', $user_postcode );
			update_user_meta( $user_id, 'user_address_line1', $user_address_line1 );
			update_user_meta( $user_id, 'user_address_line2', $user_address_line2 );

			// Mettre à jour aussi le champ legacy user_address pour compatibilité
			$full_address = trim( $user_address_line1 . ' ' . $user_address_line2 . ', ' . $user_postcode . ' ' . $user_city . ', ' . $user_country );
			update_user_meta( $user_id, 'user_address', $full_address );

			wp_send_json_success( array( 'message' => __( 'Localisation enregistrée avec succès', 'eventlist' ) ) );
			wp_die();
		}

		/* Check password */
		public static function el_check_password() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;
			$password_database = wp_get_current_user()->user_pass;
			
			$old_password = isset( $post_data['old_password'] ) ? sanitize_text_field( $post_data['old_password'] ) : '';

			if( wp_check_password( $old_password, $password_database, $user_id ) == true && $old_password != '' ) {
				echo ('true');
			} else {
				echo 'false';
			}
			wp_die();
		}

		/* Change password */
		public static function el_change_password() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			
			if( !isset( $post_data['el_update_password_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_update_password_nonce'] ), 'el_update_password_nonce' ) ) return ;
			
			$user_id = wp_get_current_user()->ID;
			$password_database = wp_get_current_user()->user_pass;

			// Don't change password some user for testing.
			if( in_array( $user_id, apply_filters( 'user_id_testing', array() ) ) ){
				return;
			}
			

			$old_password = isset( $post_data['old_password'] ) ? sanitize_text_field( $post_data['old_password'] ) : '';
			$new_password = isset( $post_data['new_password'] ) ? sanitize_text_field( $post_data['new_password'] ) : '';
			
			if( wp_check_password( $old_password, $password_database, $user_id ) ) {
				wp_set_password( $new_password, $user_id );

				$redirect_url = wp_login_url();
				$redirect_url = add_query_arg( 'password', 'changed', $redirect_url );
				echo esc_url( $redirect_url );
			}
			wp_die();
		}

		/* Pending post */
		public static function el_pending_post() {

			$post_data = $_POST['data'];
			
			if( !isset( $_POST['data'] ) ) wp_die();

			if( !isset( $post_data['el_pending_post_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_pending_post_nonce'] ), 'el_pending_post_nonce' ) ) return ;
			
			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';

			if( !verify_current_user_post( $post_id ) || !el_can_edit_event() ) return false;

			$my_post = array(
				'ID'          => $post_id,
				'post_status' => 'pending',
			);
			wp_update_post( $my_post );

			return true;
		}

		/* Pending post */
		public static function el_trash_post() {

			$post_data = $_POST['data'];
			
			if( !isset( $_POST['data'] ) ) wp_die();

			if( !isset( $post_data['el_trash_post_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_trash_post_nonce'] ), 'el_trash_post_nonce' ) ) return ;

			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';

			if( !verify_current_user_post( $post_id ) || !el_can_edit_event() ) return false;

			wp_trash_post( $post_id );

			return true;
		}

		/* duplicate post */
		public static function el_duplicate_post() {


			$post_data = $_POST['data'];
			
			if( !isset( $_POST['data'] ) ) wp_die();

			if( !isset( $post_data['el_duplicate_post_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_duplicate_post_nonce'] ), 'el_duplicate_post_nonce' ) ) return ;

			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';

			$user 			= wp_get_current_user();
			$allowed_roles 	= array( 'administrator' );

			$check = "";
			if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
				$check_create_event = el_check_create_event();
				$check = $check_create_event['status'];
			}

			$publish = EL()->options->role->get('publish_event', '1') ;

			if($publish =='1'){

				$publish = "publish";
			}else{

				$publish = "pending";
			}
			if( !verify_current_user_post( $post_id )) return false;

			$member_account_id = EL()->options->general->get( 'myaccount_page_id', '' );
			$redirect_page = get_the_permalink( $member_account_id );
			$redirect_page = add_query_arg( 'vendor', 'package', $redirect_page );		

			if( $check == 'false_total_event') {

				echo json_encode( array( 'status' => 'error', 'msg' => esc_html__( 'Please register a package or upgrade to high package because your current package is limit number events. Click OK to setup package.', 'eventlist' ),  'url' => $redirect_page ) );
				wp_die();


			} else if($check == 'error'){

				echo json_encode( array( 'status' => 'error', 'msg' => esc_html__( 'You don\'t have permission add new event. Click OK to setup package.', 'eventlist' ),  'url' => $redirect_page ) );
				wp_die();

			} else if($check == 'false_time_membership'){

				echo json_encode( array( 'status' => 'error', 'msg' => esc_html__( 'Your package time is expired. Click OK to setup package.', 'eventlist' ),  'url' => $redirect_page ) );
				wp_die();				

			} else {


				$post = get_post($post_id);
	            /*
	            * if you don't want current user to be the new post author,
	            * then change next couple of lines to this: $new_post_author = $post->post_author;
	            */
	            $current_user = wp_get_current_user();
	            $new_post_author = $current_user->ID;


	            if (isset($post) && $post != null) {
	            	/*
	                * new post data array
	                */
	                $args = array(
	                     'comment_status' 	=> $post->comment_status,
	                     'ping_status' 		=> $post->ping_status,
	                     'post_author' 		=> $new_post_author,
	                     'post_content' 	=> $post->post_content,
	                     'post_excerpt' 	=> $post->post_excerpt,
	                     'post_parent' 		=> $post->post_parent,
	                     'post_password' 	=> $post->post_password,
	                     'post_status' 		=> $publish,
	                     'post_title' 		=> $post->post_title,
	                     'post_type' 		=> $post->post_type,
	                     'to_ping' 			=> $post->to_ping,
	                     'menu_order' 		=> $post->menu_order,
	                 );
	                /*
	                * insert the post by wp_insert_post() function
	                */
	                $new_post_id = wp_insert_post($args);
		            if(is_wp_error($new_post_id)){
			            return false;
		            }
	               
	                /*
	                * get all current post terms ad set them to the new post draft
	                */
	                $taxonomies = array_map('sanitize_text_field',get_object_taxonomies($post->post_type));
	                if (!empty($taxonomies) && is_array($taxonomies)):
	                 foreach ($taxonomies as $taxonomy) {
	                     $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
	                     wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	                 }
	                endif;
	                /*
	                * duplicate all post meta
	                */
	                $post_meta_keys = get_post_custom_keys( $post_id );
	                if(!empty($post_meta_keys)){
	                    foreach ( $post_meta_keys as $meta_key ) {
	                        $meta_values = get_post_custom_values( $meta_key, $post_id );
	                        foreach ( $meta_values as $meta_value ) {
	                            $meta_value = maybe_unserialize( $meta_value );
	                            update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
	                        }
	                    }
	                }

	                // Update membership id
	                $id_membership = '';
	                if ( ! el_is_administrator() ) {
	                	$id_membership = EL_Package::get_id_membership_by_current_user();
	                }
	                update_post_meta( $post_id, OVA_METABOX_EVENT.'membership_id', $id_membership );

	                /**
	                 * Elementor compatibility fixes
	                 */
					if(is_plugin_active( 'elementor/elementor.php' )){
					    $css = Elementor\Core\Files\CSS\Post::create( $new_post_id );
					    $css->update();
					}

					$href = add_query_arg( array( 'vendor' => 'listing-edit', 'id' => $new_post_id  ), get_myaccount_page() );
					echo json_encode( array( 'href' => $href, 'status' => 'success' ));
					wp_die();
					exit;
	            }

			}
			return true;
		}

		/* Pending post */
		public static function el_delete_post() {

			$post_data = $_POST['data'];
			
			if( !isset( $_POST['data'] ) ) wp_die();

			if( !isset( $post_data['el_delete_post_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_delete_post_nonce'] ), 'el_delete_post_nonce' ) ) return ;

			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';

			if( !verify_current_user_post( $post_id ) || !el_can_delete_event() ) return false;

			wp_delete_post( $post_id, false );

			return true;
		}

		/* Publish post */
		public static function el_publish_post() {

			$post_data = $_POST['data'];
			$_prefix = OVA_METABOX_EVENT;
			
			if( !isset( $_POST['data'] ) ) wp_die();

			if( !isset( $post_data['el_publish_post_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_publish_post_nonce'] ), 'el_publish_post_nonce' ) ) return ;
			
			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';
			

			if( !verify_current_user_post( $post_id ) ) return false;

			if ( el_can_publish_event() ) {

				$my_post = array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				);
				wp_update_post( $my_post );

				update_post_meta( $post_id, $_prefix.'event_active', '1' );

			} else {

				$event_active = get_post_meta( $post_id, $_prefix.'event_active', true );

				switch ( $event_active ) {
					case '1': 
					$my_post = array(
						'ID'          => $post_id,
						'post_status' => 'publish',
					);
					wp_update_post( $my_post );
					break;

					default:
					$my_post = array(
						'ID'          => $post_id,
						'post_status' => 'pending',
					);
					wp_update_post( $my_post );
					break;
				}
			}
			return true;
		}

		/* Delete post */
		public static function el_bulk_action() {

			$post_data = $_POST['data'];
			$_prefix = OVA_METABOX_EVENT;
			
			if( !isset( $_POST['data'] ) ) wp_die();
			
			if( !isset( $post_data['el_bulk_action_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_bulk_action_nonce'] ), 'el_bulk_action_nonce' ) ) return ;

			$post_id = array();
			foreach ($post_data['post_id'] as $key => $value) {
				$post_id[$key] = sanitize_text_field( $post_data['post_id'][$key] );
			}

			$value_select = isset( $post_data['value_select'] ) ? sanitize_text_field( $post_data['value_select'] ) : '';
			
			foreach ($post_id as $key => $value) {

				if( !verify_current_user_post( $value ) ) return false;

				if ( ( $value_select == 'pending' || $value_select == 'restore' ) && el_can_edit_event() ) {
					$my_post = array(
						'ID'          => $value,
						'post_status' => 'pending',
					);
					wp_update_post( $my_post );

				} elseif( $value_select == 'trash' && el_can_edit_event() ) {
					$my_post = array(
						'ID'          => $value,
						'post_status' => 'trash',
					);
					wp_update_post( $my_post );

				} elseif( $value_select == 'publish' ) {

					if ( el_can_publish_event() ) {

						$my_post = array(
							'ID'          => $value,
							'post_status' => 'publish',
						);
						wp_update_post( $my_post );

					} else {
						
						$event_active = get_post_meta( $post_id, $_prefix.'event_active', true );

						switch ( $event_active ) {
							case '1': 
							$my_post = array(
								'ID'          => $value,
								'post_status' => 'publish',
							);
							wp_update_post( $my_post );
							break;

							default:
							$my_post = array(
								'ID'          => $value,
								'post_status' => 'pending',
							);
							wp_update_post( $my_post );
							break;
						}
					}

				} elseif( $value_select == 'delete' && el_can_delete_event() ) {
					wp_delete_post( $value );
				}
			}
			return true;
		}

		/* Load location */
		public static function el_load_location() {
			
			if( !isset( $_POST['data'] ) ) wp_die();
			
			$post_data = $_POST['data'];
			$country = isset( $post_data['country'] ) ? sanitize_text_field( $post_data['country'] ) : '';
			$city_selected = isset( $post_data['city_selected'] ) ? sanitize_text_field( $post_data['city_selected'] ) : '';

			if ($country != '') {

				$country = get_term_by( 'slug', $country, 'event_loc' );
					


				$get_city = get_terms( array( 'taxonomy' => 'event_loc', 'parent' => $country->term_id, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );
				
				?>	
				<option value=""><?php esc_html_e( 'All Cities', 'eventlist' ); ?></option> 
				<?php

				foreach ($get_city as $v_city) {
					$v_city_slug = isset( $v_city->slug ) ? apply_filters( 'editable_slug', $v_city->slug, $v_city ) : '';
				?>

					<option value="<?php echo esc_attr($v_city_slug); ?>" <?php echo esc_attr( $city_selected == $v_city_slug ? 'selected' : '' ); ?> ><?php echo esc_html($v_city->name); ?></option>

				<?php }

			} else {

				$parent_terms = get_terms( array( 'taxonomy' => 'event_loc', 'parent' => 0, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) ); 
				?>	
				<option value=""><?php esc_html_e( 'All Cities', 'eventlist' ); ?></option> 
				<?php

				foreach ( $parent_terms as $pterm ) {

					$terms = get_terms( array( 'taxonomy' => 'event_loc', 'parent' => $pterm->term_id, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );
					?>

					<?php
					foreach ( $terms as $term ) { 
						$term_slug = isset( $term->slug ) ? apply_filters( 'editable_slug', $term->slug, $term ) : '';
					?>
						<option value="<?php echo esc_attr($term_slug); ?>" <?php echo esc_attr( $city_selected == $term_slug ? 'selected' : '' ); ?> ><?php echo esc_html($term->name); ?></option>

					<?php	}
				}
			}

			wp_die();
		}

		/* Save Edit Event */
		public static function el_save_edit_event() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];

			$meta_data = $_POST['meta_data'];

			$_prefix = OVA_METABOX_EVENT;

			if( !isset( $post_data['el_edit_event_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_edit_event_nonce'] ), 'el_edit_event_nonce' ) ) return ;

			$current_user = get_current_user_id();

			$post_data_sanitize = recursive_sanitize_text_field( $meta_data );
			// add prefix to keys
			foreach ( $post_data_sanitize as $key => $item ) {
				$newKey = $_prefix.$key;
		        $post_data_sanitize[$newKey] = $item;
		        unset($post_data_sanitize[$key]);
			}

			if ( isset( $meta_data['ticket_external_link'] ) ) {
				unset( $post_data_sanitize[$_prefix.'ticket_external_link'] );
				$post_data_sanitize[$_prefix.'ticket_external_link'] = sanitize_url( $meta_data['ticket_external_link'] );
			}

			$content_event 	= isset( $post_data['content_event'] ) ? wp_kses_post( $post_data['content_event'] ) : '';
			$post_id 		= isset( $post_data['post_id'] ) ? $post_data['post_id'] : '';

			$author_id 		= get_post_field( 'post_author', $post_id ) ? get_post_field( 'post_author', $post_id ) : '';
			$name_event 	= isset( $post_data['name_event'] ) ?  sanitize_text_field( $post_data['name_event'] )  : '';
			$event_cat 		= isset( $post_data['event_cat'] ) ? $post_data['event_cat'] : '';
			$data_taxonomy 	= isset( $post_data['data_taxonomy'] ) ? $post_data['data_taxonomy'] : [];
			$event_password = isset( $post_data['event_password'] ) ? sanitize_text_field( $post_data['event_password'] ) : '';
			$event_tag 		= isset( $post_data['event_tag'] ) ? $post_data['event_tag'] : [];

			$event_state 	= isset( $post_data['event_state'] ) ? $post_data['event_state'] : '';
			$event_city 	= isset( $post_data['event_city'] ) ? $post_data['event_city'] : '';

			$img_thumbnail 	= isset( $post_data['img_thumbnail'] ) ? sanitize_text_field( $post_data['img_thumbnail'] ) : '';

			$event_status 	= isset( $post_data['event_status'] ) ? sanitize_text_field( $post_data['event_status'] ) : 'publish';
			$time_zone 		= isset( $post_data_sanitize[$_prefix.'time_zone'] ) ? $post_data_sanitize[$_prefix.'time_zone'] : '';

			if ( $event_status === 'publish' ) {
				$event_password = '';
			}

			if ( $event_status === 'protected' ) {
				$event_status = 'publish';
			}
			
			if( isset( $meta_data['venue'] ) && $meta_data['venue'] ){
				foreach ( $meta_data['venue'] as $value ) {

					$value = isset( $value ) ? sanitize_text_field( $value ) : '';

					if ( ! el_get_page_by_title( $value, OBJECT, 'venue' ) ) {
						$venue_info = array(
							'post_author' 	=> $current_user,
							'post_title' 	=> sanitize_text_field( $value ),
							'post_content' 	=> '',
							'post_type' 	=> 'venue',
							'post_status' 	=> 'publish',
							'_thumbnail_id' => '',
						);

						wp_insert_post( $venue_info, true ); 
					}
				}
			}

			$check_allow_change_tax = check_allow_change_tax_by_event( $post_id );
			$check_allow_change_tax_user = check_allow_change_tax_by_user_login();
			$enable_tax = EL()->options->tax_fee->get('enable_tax');



			/* Check image thumbnail exits */
			if (!$img_thumbnail) {
				delete_post_thumbnail($post_id);
			}


			/* Check event_tax exits */
			if ( ( isset( $post_data_sanitize[$_prefix.'event_tax'] ) && !$post_data_sanitize[$_prefix.'event_tax'] ) || $check_allow_change_tax_user != 'yes' || $enable_tax != 'yes' ) {
				$post_data_sanitize[$_prefix.'event_tax'] = 0;
			}

			/* Check event_type exits */
			if ( ( isset( $post_data_sanitize[$_prefix.'event_type'] ) && !$post_data_sanitize[$_prefix.'event_type'] ) ) {
				$post_data_sanitize[$_prefix.'event_type'] = 'classic';
			}

			if ( ( isset( $post_data_sanitize[$_prefix.'ticket_link'] ) && !$post_data_sanitize[$_prefix.'ticket_link'] ) ) {
				$post_data_sanitize[$_prefix.'ticket_link'] = 'ticket_internal_link';
			}

			if ( ( isset( $post_data_sanitize[$_prefix.'ticket_external_link'] ) && !$post_data_sanitize[$_prefix.'ticket_external_link'] ) ) {
				$post_data_sanitize[$_prefix.'ticket_external_link'] = '';
			}

			if ( ( isset( $post_data_sanitize[$_prefix.'ticket_external_link_price'] ) && !$post_data_sanitize[$_prefix.'ticket_external_link_price'] ) ) {
				$post_data_sanitize[$_prefix.'ticket_external_link_price'] = '';
			}

			/* Check social exits */
			if ( !isset( $post_data_sanitize[$_prefix.'social_organizer'] ) || !$post_data_sanitize[$_prefix.'social_organizer'] ) {
				$post_data_sanitize[$_prefix.'social_organizer'] = array();
			}

			/* Check image gallery exits */
			if ( !isset( $post_data_sanitize[$_prefix.'gallery'] ) || !$post_data_sanitize[$_prefix.'gallery'] ) {
				$post_data_sanitize[$_prefix.'gallery'] = array();
			}

			/* Check image banner exits */
			if ( !isset( $post_data_sanitize[$_prefix.'image_banner'] ) || !$post_data_sanitize[$_prefix.'image_banner'] ) {
				$post_data_sanitize[$_prefix.'image_banner'] = '';
			}		

			/* Check Ticket exits */
			if( !isset( $post_data_sanitize[$_prefix.'ticket'] ) || !$post_data_sanitize[$_prefix.'ticket'] ){
				$post_data_sanitize[$_prefix.'ticket'] = array();
			}

			/* Check calendar exits */
			if ( !isset( $post_data_sanitize[$_prefix.'calendar'] ) || !$post_data_sanitize[$_prefix.'calendar'] ) {
				$post_data_sanitize[$_prefix.'calendar'] = array();
			}

			/* Check schedules_time exits */
			if ( !isset( $post_data_sanitize[$_prefix.'schedules_time'] ) || !$post_data_sanitize[$_prefix.'schedules_time'] ) {
				$post_data_sanitize[$_prefix.'schedules_time'] = array();
			}


			/* Check Disable Date exits */
			if ( !isset( $post_data_sanitize[$_prefix.'disable_date'] ) || !$post_data_sanitize[$_prefix.'disable_date'] ) {
				$post_data_sanitize[$_prefix.'disable_date'] = array();
			}

			/* Check Disable Time Slot exits */
			if ( !isset( $post_data_sanitize[$_prefix.'disable_date_time_slot'] ) || !$post_data_sanitize[$_prefix.'disable_date_time_slot'] ) {
				$post_data_sanitize[$_prefix.'disable_date_time_slot'] = array();
			}

			/* Check coupon exits */
			if ( !isset( $post_data_sanitize[$_prefix.'coupon'] ) || !$post_data_sanitize[$_prefix.'coupon'] ) {
				$post_data_sanitize[$_prefix.'coupon'] = array();
			}


			/* Check Venue exits */
			if( !isset( $post_data_sanitize[$_prefix.'venue'] ) || !$post_data_sanitize[$_prefix.'venue'] ){
				$post_data_sanitize[$_prefix.'venue'] = array();
			}

			/* Check recurrence bydays exits */
			if( !isset( $post_data_sanitize[$_prefix.'recurrence_bydays'] ) || !$post_data_sanitize[$_prefix.'recurrence_bydays'] ){
				$post_data_sanitize[$_prefix.'recurrence_bydays'] = array();
			}

			/* Check recurrence interval exits */
			if( !isset( $post_data_sanitize[$_prefix.'recurrence_interval'] ) || !$post_data_sanitize[$_prefix.'recurrence_interval'] ){
				$post_data_sanitize[$_prefix.'recurrence_interval'] = '1';
			}

			if ( ! isset( $post_data_sanitize[$_prefix.'seating_map'] ) | empty( $post_data_sanitize[$_prefix.'seating_map'] ) ) {
				$post_data_sanitize[$_prefix.'seating_map'] = '';
			}

			$ticket_prices 	= array();
			$seat_option 	= isset( $post_data_sanitize[$_prefix.'seat_option'] ) ? $post_data_sanitize[$_prefix.'seat_option'] : 'none';

			$k = 0;
			$decimal_separator 	= EL()->options->general->get('decimal_separator','.');

			if( isset( $post_data_sanitize[$_prefix.'ticket'] ) && $post_data_sanitize[$_prefix.'ticket'] ){
				foreach ($post_data_sanitize[$_prefix.'ticket'] as $key => $value) {
					if ($value['ticket_id'] == '') {
						$post_data_sanitize[$_prefix.'ticket'][$key]['ticket_id'] = FLOOR(microtime(true)) + $k;
						$k++;
					}

					// ticket private description
					$post_data_sanitize[$_prefix.'ticket'][$key]['private_desc_ticket'] = isset( $meta_data['ticket'][$key]['private_desc_ticket'] ) ? wp_kses_post( $meta_data['ticket'][$key]['private_desc_ticket'] ) : '';

					if ($value['setup_seat'] == '') {

						$post_data_sanitize[$_prefix.'ticket'][$key]['setup_seat'] =  'yes';

					}

					if ( $value['setup_mode'] == 'automatic' ) {
						$seat_code_setup = isset( $value['seat_code_setup'] ) ? recursive_sanitize_text_field( $value['seat_code_setup'] ) : [];
						
						$seat_list = array();
						if ( ! empty( $seat_code_setup ) ) {
							foreach ( $seat_code_setup as $j => $_val ) {
								$code 	= trim($_val['code']);
								$from 	= absint( $_val['from'] );
								$to 	= absint( $_val['to'] );
								while ( $from <= $to ) {
									$seat_list[] = $code.$from;
									$from += 1;
								}
							}
						}
						$post_data_sanitize[$_prefix.'ticket'][$key]['seat_list'] = implode(", ", $seat_list );
					}

					if ( $value['price_ticket'] ) {
						$price = $value['price_ticket'];
						$new_price = str_replace( $decimal_separator, ".", $price );
						if ( $price !== $new_price ) {
							$post_data_sanitize[$_prefix.'ticket'][$key]['price_ticket'] = $new_price;
						}
						$ticket_prices['none'][] = (float) $new_price;
						$ticket_prices['simple'][] = (float) $new_price;
					}
				}
			}

			$ticket_link = isset( $post_data_sanitize[$_prefix.'ticket_link'] ) ? $post_data_sanitize[$_prefix.'ticket_link'] : '';

			if ( $ticket_link !== 'ticket_internal_link' ) {
				if ( isset( $post_data_sanitize[$_prefix.'ticket_external_link_price'] )  ) {
					$price = $post_data_sanitize[$_prefix.'ticket_external_link_price'] ? (float) $post_data_sanitize[$_prefix.'ticket_external_link_price'] : '';
					if ( $price ) {
						$ticket_prices['ticket_external_link'][] = $price;
					}
				}
			}

			if( isset( $post_data_sanitize[$_prefix.'calendar'] ) && $post_data_sanitize[$_prefix.'calendar'] ){
				foreach ($post_data_sanitize[$_prefix.'calendar'] as $key => $value) {
					if ($value['calendar_id'] == '') {
						$post_data_sanitize[$_prefix.'calendar'][$key]['calendar_id'] = FLOOR(microtime(true)) + $k;
						$k++;
					}
					if ($value['date'] == '') {
						unset($post_data_sanitize[$_prefix.'calendar'][$key]);
					}
				}
			}

			if( isset( $post_data_sanitize[$_prefix.'coupon'] ) && $post_data_sanitize[$_prefix.'coupon'] ){
				foreach ($post_data_sanitize[$_prefix.'coupon'] as $key => $value) {
					if ($value['coupon_id'] == '') {
						$post_data_sanitize[$_prefix.'coupon'][$key]['coupon_id'] = FLOOR(microtime(true)) + $k;
						$k++;
					}
				}
			}

			/* Check checbox info organizer exits */
			if( !isset( $post_data_sanitize[$_prefix.'info_organizer'] ) || !$post_data_sanitize[$_prefix.'info_organizer'] ){
				$post_data_sanitize[$_prefix.'info_organizer'] = '';
			}else{
				$post_data_sanitize[$_prefix.'info_organizer'] = 'checked';
			}

			/* Check checbox info organizer exits */
			if( !isset( $post_data_sanitize[$_prefix.'edit_full_address'] ) || !$post_data_sanitize[$_prefix.'edit_full_address'] ){
				$post_data_sanitize[$_prefix.'edit_full_address'] = '';
			}else{
				$post_data_sanitize[$_prefix.'edit_full_address'] = 'checked';
			}

			// Time Slot
			$recurrence_time_slot = array();

			/* Check Calendar Auto */ 
			if ( isset( $post_data_sanitize[$_prefix.'option_calendar'] ) && $post_data_sanitize[$_prefix.'option_calendar'] == 'auto' ) {
				$recurrence_days = get_recurrence_days(
					$post_data_sanitize[$_prefix.'recurrence_frequency'], 
					$post_data_sanitize[$_prefix.'recurrence_interval'], 
					$post_data_sanitize[$_prefix.'recurrence_bydays'], 
					$post_data_sanitize[$_prefix.'recurrence_byweekno'], 
					$post_data_sanitize[$_prefix.'recurrence_byday'], 
					$post_data_sanitize[$_prefix.'calendar_start_date'], 
					$post_data_sanitize[$_prefix.'calendar_end_date'] 
				);

				$post_data_sanitize[$_prefix.'calendar_recurrence'] = array();

				$ts_start 	= [];
				$ts_end 	= [];

				if ( isset( $post_data_sanitize[$_prefix.'ts_start'] ) && $post_data_sanitize[$_prefix.'ts_start'] && is_array( $post_data_sanitize[$_prefix.'ts_start'] ) ) {
					foreach ( $post_data_sanitize[$_prefix.'ts_start'] as $item_ts_star ) {
						if ( ! empty( $item_ts_star ) && is_array( $item_ts_star ) ) {
							foreach ( $item_ts_star as $k => $item_times ) {
								if ( ! empty( $item_times ) && is_array( $item_times ) ) {
									$ts_start[$k] = $item_times;
								}
							}
						}
					}
				}

				if ( isset( $post_data_sanitize[$_prefix.'ts_end'] ) && $post_data_sanitize[$_prefix.'ts_end'] && is_array( $post_data_sanitize[$_prefix.'ts_end'] ) ) {
					foreach ( $post_data_sanitize[$_prefix.'ts_end'] as $item_ts_end ) {
						if ( ! empty( $item_ts_end ) && is_array( $item_ts_end ) ) {
							foreach ( $item_ts_end as $k => $item_times ) {
								if ( ! empty( $item_times ) && is_array( $item_times ) ) {
									$ts_end[$k] = $item_times;
								}
							}
						}
					}
				}
				
				$post_data_sanitize[$_prefix.'ts_start'] 	= $ts_start ? $ts_start : '';
				$post_data_sanitize[$_prefix.'ts_end'] 		= $ts_end ? $ts_end : '';

				foreach ( $recurrence_days as $key => $value ) {
					if ( isset( $post_data_sanitize[$_prefix.'schedules_time'] ) ) {
						foreach ($post_data_sanitize[$_prefix.'schedules_time'] as $key_schedule => $value_schedule) {
							$post_data_sanitize[$_prefix.'calendar_recurrence'][] = [
								'calendar_id' => $value.$key_schedule,
								'date' => gmdate('Y-m-d', $value),
								'start_time' => $value_schedule['start_time'],
								'end_time' => $value_schedule['end_time'],
								'book_before' => $value_schedule['book_before'],
							];
						}
					}

					$post_data_sanitize[$_prefix.'calendar_recurrence'][] = [
						'calendar_id' 	=> $value,
						'date' 			=> gmdate('Y-m-d', $value),
						'start_time' 	=> $post_data_sanitize[$_prefix.'calendar_recurrence_start_time'],
						'end_time' 		=> $post_data_sanitize[$_prefix.'calendar_recurrence_end_time'],
						'book_before' 	=> $post_data_sanitize[$_prefix.'calendar_recurrence_book_before'],
					];

					if ( $post_data_sanitize[$_prefix.'option_calendar'] == 'auto' && $post_data_sanitize[$_prefix.'recurrence_frequency'] == 'weekly' && isset( $post_data_sanitize[$_prefix.'recurrence_bydays'] ) && ! empty( $post_data_sanitize[$_prefix.'recurrence_bydays'] ) ) {

						$weekday = gmdate( 'N', $value );

						if ( $weekday == 7 ) {
							$weekday = 0;
						}

						foreach ( $post_data_sanitize[$_prefix.'recurrence_bydays'] as $k_bydays => $v_bydays ) {
							if ( $weekday == $v_bydays && isset( $post_data_sanitize[$_prefix.'ts_start'][$v_bydays] ) && isset( $post_data_sanitize[$_prefix.'ts_end'][$v_bydays] ) && ! empty( $post_data_sanitize[$_prefix.'ts_start'][$v_bydays] ) && ! empty( $post_data_sanitize[$_prefix.'ts_end'][$v_bydays] ) ) {

								foreach ( $post_data_sanitize[$_prefix.'ts_start'][$v_bydays] as $k_ts_start => $v_ts_start ) {
									if ( isset( $post_data_sanitize[$_prefix.'ts_end'][$v_bydays][$k_ts_start] ) && $post_data_sanitize[$_prefix.'ts_end'][$v_bydays][$k_ts_start] ) {

										$recurrence_time_slot[] = [
											'calendar_id' 	=> $value.$v_bydays.$k_ts_start,
											'date' 			=> gmdate('Y-m-d', $value),
											'start_time' 	=> $v_ts_start,
											'end_time' 		=> $post_data_sanitize[$_prefix.'ts_end'][$v_bydays][$k_ts_start],
											'book_before' 	=> apply_filters( 'el_tf_time_slot_book_before', 0, $post_id ),
										];
									}
								}
							}
						}
					}
				}

				if ( ! empty( $recurrence_time_slot ) && is_array( $recurrence_time_slot ) ) {
					$post_data_sanitize[$_prefix.'calendar_recurrence'] = $recurrence_time_slot;
				}
			}

			/* Disable Date */
			$arr_disable_date = array();
			$total_key_disable_date = 0;
			if ( isset( $post_data_sanitize[$_prefix.'disable_date'] ) && ! empty( $post_data_sanitize[$_prefix.'disable_date'] ) ) {
				foreach ($post_data_sanitize[$_prefix.'disable_date'] as $key => $value) {

					if ( $value['start_date'] == '' && $value['end_date'] != '' ) {
						$post_data_sanitize[$_prefix.'disable_date'][$key]['start_date'] =  $post_data_sanitize[$_prefix.'disable_date'][$key]['end_date'];
					}

					if ( $value['start_date'] != '' && $value['end_date'] == '' ) {
						$post_data_sanitize[$_prefix.'disable_date'][$key]['end_date'] =  $post_data_sanitize[$_prefix.'disable_date'][$key]['start_date'];
					}

					if ( $value['start_date'] == '' && $value['end_date'] == '' ) {
						unset( $post_data_sanitize[$_prefix.'disable_date'][$key] );
					}

					$total_key_disable_date = $key;
				}

				if( isset($total_key_disable_date) && $total_key_disable_date ){
					for ($i = 0; $i <= $total_key_disable_date; $i++) {

						$number_date = ( strtotime( $post_data_sanitize[$_prefix.'disable_date'][$i]['end_date'] ) - strtotime( $post_data_sanitize[$_prefix.'disable_date'][$i]['start_date'] ) ) / 86400;

						for ( $x = 0; $x <= $number_date; $x++ ) {
							$arr_disable_date []= [
								'date' => strtotime( ($x).' days' , strtotime( $post_data_sanitize[$_prefix.'disable_date'][$i]['start_date'] ) ),
								'time' =>  $post_data_sanitize[$_prefix.'disable_date'][$i]['schedules_time'],
							];
						}

					}
				}
			}

			/* Disable Time Slot */
			if ( isset( $post_data_sanitize[$_prefix.'disable_date_time_slot'] ) && ! empty( $post_data_sanitize[$_prefix.'disable_date_time_slot'] ) ) {
				foreach ( $post_data_sanitize[$_prefix.'disable_date_time_slot'] as $k => $ts_item ) {

					if ( $ts_item['start_date'] == '' && $ts_item['end_date'] != '' ) {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['start_date'] = $post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['end_date'];
					}

					if ( $ts_item['start_date'] != '' && $ts_item['end_date'] == '' ) {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['end_date'] = $post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['end_date'];
					}

					if ( $ts_item['start_time'] ) {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['start_time'] = $ts_item['start_time'];
					} else {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['start_time'] = '';
					}

					if ( $ts_item['end_time'] ) {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['end_time'] = $ts_item['end_time'];
					} else {
						$post_data_sanitize[$_prefix.'disable_date_time_slot'][$k]['end_time'] = '';
					}
				}
			}

			/* Remove date disabled */
			if ( isset( $post_data_sanitize[$_prefix.'calendar_recurrence'] ) && ! empty( $post_data_sanitize[$_prefix.'calendar_recurrence'] ) ) {
				if ( ! empty( $recurrence_time_slot ) && is_array( $recurrence_time_slot ) ) {
					if ( isset( $post_data_sanitize[$_prefix.'disable_date_time_slot'] ) && ! empty( $post_data_sanitize[$_prefix.'disable_date_time_slot'] ) ) {
						foreach ( $post_data_sanitize[$_prefix.'calendar_recurrence'] as $key => $value ) {
							foreach ( $post_data_sanitize[$_prefix.'disable_date_time_slot'] as $ts_item ) {
								$cal_start 	= strtotime( $value['date'] . ' ' . $value['start_time'] ) - absint( $value['book_before'] * 60 );
								$cal_end 	= strtotime( $value['date'] . ' ' . $value['end_time'] );

								$ts_start 	= strtotime( $ts_item['start_date'] . ' ' . $ts_item['start_time'] );
								$ts_end 	= strtotime( $ts_item['end_date'] . ' ' . $ts_item['end_time'] );

								if ( ! ( $ts_start >= $cal_end || $ts_end <= $cal_start ) ) {
									unset( $post_data_sanitize[$_prefix.'calendar_recurrence'][$key] );
								}
							}
						}
					}
				} else {
					if ( ! empty( $arr_disable_date ) && is_array( $arr_disable_date ) ) {
						foreach ( $post_data_sanitize[$_prefix.'calendar_recurrence'] as $key => $value ) {
							foreach ( $arr_disable_date as $v_date) {
								if ( $v_date['date'].$v_date['time'] == $value['calendar_id'] ) {
									unset($post_data_sanitize[$_prefix.'calendar_recurrence'][$key]);
								}
							}
						}
					}
				}
			}
			
			/* Date strtotime */
			$arr_start_date = array();
			$event_days = '';
			$arr_end_date = array();
			if ($post_data_sanitize[$_prefix.'option_calendar'] == 'manual') {
				if ( isset( $post_data_sanitize[$_prefix.'calendar'] ) ) {
					foreach ($post_data_sanitize[$_prefix.'calendar'] as $value) {
						$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
						$arr_end_date[] = strtotime( $value['end_date'] .' '. $value['end_time'] );
						$all_date_betweens_day = el_getDatesFromRange( gmdate( 'Y-m-d', strtotime( $value['date'] ) ), gmdate( 'Y-m-d', strtotime( $value['end_date'] )+24*60*60 ) );
						foreach ($all_date_betweens_day as $v) {
							$event_days .= $v.'-';
						}
					}
				}
			} else {
				if ( isset( $post_data_sanitize[$_prefix.'calendar_recurrence'] ) ) {
					foreach ($post_data_sanitize[$_prefix.'calendar_recurrence'] as $value) {
						$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
						$arr_end_date[] = strtotime( $value['date'] .' '. $value['end_time'] );
						$event_days .= strtotime( $value['date'] ).'-';
					}
				}
			}

			// store all days of event
			$post_data_sanitize[$_prefix.'event_days'] = $event_days;

			if ( $arr_start_date != array() )  {
				$post_data_sanitize[$_prefix.'start_date_str'] = min($arr_start_date);
			} else {
				$post_data_sanitize[$_prefix.'start_date_str'] = '';
			}
			

			if ( $arr_end_date != array() ) {
				$post_data_sanitize[$_prefix.'end_date_str'] = max($arr_end_date);
			} else {
				$post_data_sanitize[$_prefix.'end_date_str'] = '';
			}

			// Extra Service
			if ( isset( $post_data_sanitize[$_prefix.'extra_service'] ) ) {
				$extra_service = $post_data_sanitize[$_prefix.'extra_service'];
				if ( ! empty( $extra_service ) ) {
					foreach ( $extra_service as $k => $val ) {
						$id = isset( $val['id'] ) && ! empty( $val['id'] ) ? $val['id'] : uniqid();
						$extra_service[$k]['id'] = $id;
					}
					$post_data_sanitize[$_prefix.'extra_service'] = $extra_service;
				} else {
					unset( $post_data_sanitize[$_prefix.'extra_service'] );
				}
			}

			// private description ticket map
			$post_data_sanitize[$_prefix.'ticket_map']['private_desc_ticket_map'] = isset( $meta_data['ticket_map']['private_desc_ticket_map'] ) ? wp_kses_post( $meta_data['ticket_map']['private_desc_ticket_map'] ) : '';

			/* Remove empty field seat map */
			if( isset( $post_data_sanitize[$_prefix.'ticket_map']['seat'] ) && $post_data_sanitize[$_prefix.'ticket_map']['seat'] ){
				foreach ($post_data_sanitize[$_prefix.'ticket_map']['seat'] as $key => $value) {
					if ( $value['id'] == '' || ( $value['price'] == '' && empty( $value['person_price'] ) ) ) {
						unset($post_data_sanitize[$_prefix.'ticket_map']['seat'][$key]);
					} else {
						if ( $value['person_price'] ) {
							$person_price = stripslashes( $value['person_price'] );
							$person_price = json_decode( $person_price , true );
							foreach ( $person_price as $_key => $_value ) {
								$ticket_prices['map'][] = (float) $_value;
							}
						} else {
							$ticket_prices['map'][] = (float) $value['price'];
						}
					}
				}
			}



			/* Remove empty field area map */
			if ( isset( $post_data_sanitize[$_prefix.'ticket_map']['area'] ) && $post_data_sanitize[$_prefix.'ticket_map']['area'] ) {
				foreach ( $post_data_sanitize[$_prefix.'ticket_map']['area'] as $key => $value ) {

					$flag = false;
					$ticket_area_price = '';

					if ( isset( $value['person_price'] ) && ! empty( json_decode( $value['person_price'] ) ) ) {
						$person_price = stripslashes( $value['person_price'] );
						$person_price = json_decode( $person_price , true );
						foreach ( $person_price as $_key => $_value ) {
							if ( $_value == '' || (float) $_value <= 0 ) {
								$flag = true;
							} else {
								$ticket_area_price = (float) $_value;
							}
						}
					} else if ( $value['price'] == '' ) {
						$flag = true;
					} else {
						$ticket_area_price = (float) $value['price'];
					}

					if ( $value['qty'] == '' ) {
						$flag = true;
					}

					if ( $value['id'] == '' ) {
						$flag = true;
					}
					
					if ( $flag ) {
						unset( $post_data_sanitize[$_prefix.'ticket_map']['area'][$key] );
					} else {
						if ( $ticket_area_price ) {
							$ticket_prices['map'][] = $ticket_area_price;
						}
					}
				}
			}

			/* Remove empty field description seat map */
			if( isset( $post_data_sanitize[$_prefix.'ticket_map']['desc_seat'] ) && $post_data_sanitize[$_prefix.'ticket_map']['desc_seat'] ){
				foreach ($post_data_sanitize[$_prefix.'ticket_map']['desc_seat'] as $key => $value) {
					if ( $value['map_price_type_seat'] == '' || $value['map_type_seat'] == '' ) {
						unset($post_data_sanitize[$_prefix.'ticket_map']['desc_seat'][$key]);
					}
				}
			}

			// min_max_price
			$min_max_price = '';
			if ( count( $ticket_prices ) > 0 ) {
				if ( $ticket_link === 'ticket_external_link' ) {
					if ( isset( $ticket_prices['ticket_external_link'] ) ) {
						$min_max_price = implode("-", $ticket_prices['ticket_external_link']);
					} else {
						$min_max_price = '0';
					}
				} else {
					switch ( $seat_option ) {

						case 'simple':
							if ( isset( $ticket_prices['simple'] ) ) {
								$min_max_price = implode("-", $ticket_prices['simple']);
							} else {
								$min_max_price = '0';
							}
						break;
						case 'map':
							if ( isset( $ticket_prices['map'] ) ) {
								$min_max_price = implode("-", $ticket_prices['map']);
							} else {
								$min_max_price = '0';
							}
						break;
						default:
							if ( isset( $ticket_prices['none'] ) ) {
								$min_max_price = implode("-", $ticket_prices['none']);
							} else {
								$min_max_price = '0';
							}
						break;
					}
				}
			} else {
				$min_max_price = '0';
			}

			$min_price = '0';
			$max_price = '0';

			if ( $min_max_price != '' ) {
				$min_max_price = explode( "-", $min_max_price );
				$min_max_price = array_map( 'floatval', $min_max_price );
				$min_price = min( $min_max_price );
				$max_price = max( $min_max_price );
			}

			$post_data_sanitize[$_prefix.'min_price'] = $min_price;
			$post_data_sanitize[$_prefix.'max_price'] = $max_price;

			/* Save Edit Post */
			if ( $post_id != '' ) {

				if( ! el_can_edit_event() ) {
					
					wp_send_json( array( 'status' => 'error' ) );

					wp_die();
				}

				/* Location */
				$event_loc = array();
				if( $event_state && $event_state_obj = get_term_by('slug', $event_state, 'event_loc') ){
					$event_loc[] = $event_state_obj->term_id ? $event_state_obj->term_id : '';
				}

				if( $event_city && $event_city_obj = get_term_by('slug', $event_city, 'event_loc') ){
					$event_loc[] = $event_city_obj->term_id ? $event_city_obj->term_id : '';
				}
				

				if( ! empty( $event_loc ) ){
					wp_set_post_terms( $post_id, array_filter( $event_loc ) , 'event_loc' );	
				}
				

				/* Cat */
				if( ! empty( $event_cat ) ){
					wp_set_post_terms( $post_id, $event_cat , 'event_cat' );
				}


				/* Custom Taxonomy */
				if( ! empty( $data_taxonomy ) ){
					foreach( $data_taxonomy as $slug_taxonomy => $val_taxonomy ) {
						wp_set_post_terms( $post_id, $val_taxonomy , $slug_taxonomy );
					}
				}


				/* Tags */
				if( ! empty( $event_tag ) ){
					wp_set_post_terms( $post_id, $event_tag , 'event_tag' );
				}

				/* Check event_tax exits */
				if (  ( isset( $post_data_sanitize[$_prefix.'event_tax'] ) && ! $post_data_sanitize[$_prefix.'event_tax'] ) || $check_allow_change_tax != 'yes' || $enable_tax != 'yes' ) {
					$post_data_sanitize[$_prefix.'event_tax'] = 0;
				}

				/* Update Pay Status */
				$post_data_sanitize[$_prefix.'status_pay'] = get_post_meta( $post_id, $_prefix.'status_pay', true ) ? get_post_meta( $post_id, $_prefix.'status_pay', true ) : 'pending';
				

				foreach ( $post_data_sanitize as $key => $value ) {
					update_post_meta( $post_id, $key, $value );
				}

				$post_info = get_post( $post_id );

				if( ! el_can_publish_event() ){
					$event_active = get_post_meta( $post_id, OVA_METABOX_EVENT.'event_active', true );

					if ( $post_info->post_status === 'pending' && absint( $event_active ) !== 1 ) {
						$event_status = $post_info->post_status;
					}
				}

				$post_information = array(
					'ID' 			=> $post_id,
					'post_title' 	=> $name_event,
					'post_content' 	=> $content_event,
					'post_type' 	=> 'event',
					'post_status' 	=> $event_status,
					'_thumbnail_id' => $img_thumbnail,
					'post_password' => $event_password,
				);

				if( wp_update_post( $post_information ) ){

					do_action( 'el_vendor_after_update_event', $post_id );

					wp_send_json( array( 'status' => 'updated' ) );

				
				}else{

					wp_send_json( array( 'status' => 'error' ) );

				}
				
				wp_die();

			} else { // Add new post

				$user 			= wp_get_current_user();
				$allowed_roles 	= array( 'administrator' );

				if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
					// Check create event
					$check_create_event = el_check_create_event();

					switch ( $check_create_event['status'] ) {

						case 'false_total_event':
				

							wp_send_json( array( 'status' => 'false_total_event' ) );

							wp_die();
							break;

						case 'false_time_membership':
					

							wp_send_json( array( 'status' => 'false_time_membership' ) );

							wp_die();
							break;
							
						case 'error':

							wp_send_json( array( 'status' => 'error' ) );

						
							wp_die();
							break;		
						
						default:
							break;
					}
				}

				if( ! el_can_publish_event() ){
					$event_status = 'pending';
					$post_data_sanitize[$_prefix.'event_active']   = 0;
				}else{
					$post_data_sanitize[$_prefix.'event_active']   = 1;
				}
				
				$event_arr = array(
					'post_author' 	=> $current_user,
					'post_title' 	=> $name_event,
					'post_content' 	=> $content_event,
					'post_type' 	=> 'event',
					'post_status' 	=> apply_filters( 'el_admin_review_event', $event_status ),
					'_thumbnail_id' => $img_thumbnail,
					'post_password' => $event_password,
				);
				
				$new_post_id = wp_insert_post( $event_arr, true ); 

				//Cat
				if( !empty( $event_cat ) ){
					wp_set_post_terms( $new_post_id, $event_cat , 'event_cat' );
				}

				/* Custom Taxonomy */
				if( ! empty( $data_taxonomy ) ){
					foreach( $data_taxonomy as $slug_taxonomy => $val_taxonomy ) {
						wp_set_post_terms( $new_post_id, $val_taxonomy , $slug_taxonomy );
					}
				}

				// Tags
				if( !empty( $event_tag ) ){
					wp_set_post_terms( $new_post_id, $event_tag , 'event_tag' );
				}

				// Location
				$event_loc = array();
				if( $event_state && $event_state_obj = get_term_by('slug', $event_state, 'event_loc') ){
					$event_loc[] = $event_state_obj->term_id ? $event_state_obj->term_id : '';
				}

				if( $event_city && $event_city_obj = get_term_by('slug', $event_city, 'event_loc') ){
					$event_loc[] = $event_city_obj->term_id ? $event_city_obj->term_id : '';
				}

				wp_set_post_terms( $new_post_id, array_filter($event_loc) , 'event_loc' );

				/* Add New Status Pay */
				$post_data_sanitize[$_prefix.'status_pay'] = 'pending';

				foreach ($post_data_sanitize as $name => $value ) {
					update_post_meta( $new_post_id, $name, $value );
				}

				// Add Membership ID
				$membership_id = EL_Package::get_id_membership_by_current_user();
				if ( $membership_id ) {
					update_post_meta( $new_post_id, OVA_METABOX_EVENT.'membership_id', $membership_id );
				} else {
					$package_id = EL()->options->package->get('package', '');
					update_post_meta( $new_post_id, OVA_METABOX_EVENT.'package', $package_id );
				}
				

				do_action( 'el_vendor_after_create_event', $new_post_id );

				// Send Mail Create Event
				$receive_email_after_create_event = EL()->options->mail->get('receive_email_after_create_event', 'no');
				if ( $receive_email_after_create_event != 'no' ) {
					el_sendmail_create_event( $new_post_id );
				}

				$myaccount_page = get_myaccount_page();

				$redirect_link = add_query_arg( array(
				    'vendor' 	=> 'listing-edit',
				    'id' 		=> $new_post_id,
				), $myaccount_page );


				wp_send_json( array( 'url' => $redirect_link ) );

				wp_die();
			}
		}


		public function el_check_login_report(){
			$id_event = isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';
			if( is_user_logged_in() && $id_event ) {
				?>
				<div class="el_form_report">
				<form action="" >
					<div class="el_close">
						<span class="icon_close"></span>
					</div>
					<div class="el_row_input">
						<label for="el_message"><?php esc_html_e('Message', 'eventlist') ?></label>
						<textarea name="el_message" id="el_message" cols="30" rows="10"></textarea>
					</div>
					
					<div class="el-notify">
						<p class="success"><?php esc_html_e('Send mail success', 'eventlist') ?></p>
						<p class="error"><?php esc_html_e('Send mail failed', 'eventlist') ?></p>
						<p class="error-require"><?php esc_html_e('Please enter input field', 'eventlist') ?></p>
					</div>

					<div class="el_row_input">
						<button type="submit" class="submit-sendmail-report" data-id_event="<?php echo esc_attr( $id_event ) ?>" >
							<?php esc_html_e('Submit', 'eventlist') ?>
							<div class="submit-load-more">
								<div class="load-more">
									<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
								</div>
							</div>
						</button>
					</div>
				</form>
			</div>
			<?php
			} else {
				echo esc_html('false');
			}
			wp_die();
		}

		/**
		 * Process Checkout
		 */
		public function el_check_user_login(){

			if( ! isset($_POST['data']) ) return false;
			if( !isset( $_POST['data']['el_next_event_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['data']['el_next_event_nonce']), 'el_next_event_nonce' ) ) return ;

			$setting_checkout_login = EL()->options->checkout->get('el_login_booking', 'no');

			if( $setting_checkout_login == 'yes' ) {
				if( is_user_logged_in() ) {
					echo esc_html('true');
				} else {
					echo esc_html('false');
				}
			} else {
				echo esc_html('true');
			}

			wp_die();
		}

		/**
		 * Process Checkout
		 */
		public function el_process_checkout() {
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];

			if ( ! isset( $post_data['el_checkout_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field($post_data['el_checkout_event_nonce']), 'el_checkout_event_nonce' ) ) return;

			if( $post_data['create_account'] == 'true' ){

				$user_id = el_create_account( $post_data );

				if( $user_id == false ){
					
					wp_send_json( array( 'el_message' => esc_html__( 'The email is exist, you can\'t make new account', 'eventlist' ) ) );
				
				}else{

					$user = get_user_by( 'id', $user_id ); 
					wp_set_current_user($user_id);
	        		wp_set_auth_cookie($user_id, true);
	        		do_action( 'wp_login', $user->user_login, $user );

	        		// Send Mail to Reset Password
					el_mail_reset_password( $user_id );
				}

			}

			EL()->checkout->process_checkout( $_POST['data'] );
			
			wp_die();
		}

		/**
		 * Countdown Checkout
		 */
		public function el_countdown_checkout() {
			if ( !isset( $_POST['data'] ) ) wp_die();

			$post_data 	= $_POST['data'];
			$nonce 		= isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$booking_id = isset( $post_data['booking_id'] ) ? sanitize_text_field( $post_data['booking_id'] ) : '';

			if ( !$nonce || !wp_verify_nonce( $nonce, 'el_countdown_checkout_nonce' ) ) return;

			if ( WC()->cart ) {
				WC()->cart->empty_cart();
			}

			echo esc_html('success');
			wp_die();
		}

		/**
		 * Check discount
		 */
		public function el_check_discount() {
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$post_data 		= $_POST['data'];
			$code_discount 	= sanitize_text_field( $post_data['code_discount'] );
			$id_event 		= sanitize_text_field( $post_data['id_event'] );
			$data 			= EL_Cart::instance()->check_code_discount( $id_event, $code_discount );

			echo $data;
			wp_die();
		}

		public function el_export_csv() {
			$html = '';

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
			
				$id_event = isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';
				$check_allow_export_attendees = check_allow_export_attendees_by_event($id_event);

				if (!$id_event || !verify_current_user_post($id_event) || $check_allow_export_attendees != 'yes' || !el_can_manage_booking() ) wp_die();

				$from_date 	= isset( $_POST['from_date'] ) && ! empty( $_POST['from_date'] ) ? absint( strtotime($_POST['from_date']) ) : '';
				$to_date 	= isset( $_POST['to_date'] ) && ! empty( $_POST['to_date'] ) ? absint( strtotime($_POST['to_date']) ) + (3600*24) - 1 : '';

				$agrs = [
					'post_type' 	=> 'el_bookings',
					'post_status' 	=> 'publish',
					"meta_query" 	=> [
						'relation' => 'AND',
						[
							"key" => OVA_METABOX_EVENT . 'id_event',
							"value" => $id_event,
						],
						[
							"key" 		=> OVA_METABOX_EVENT . 'status',
							"value" 	=> apply_filters( 'el_export_booking_status', array( 'Completed' ) ),
							"between" 	=> 'IN'
						],
					],
					'posts_per_page' => -1,
					'fields' => 'ids',
				];

				if ( $from_date && $to_date ) {
					$agrs['date_query'] = array(
						array(
							'after' 	=> gmdate('F j, Y', $from_date),
							'before' 	=> array(
								'year' => gmdate("Y", $to_date),
								'month' => gmdate("n", $to_date),
								'day' => gmdate("j", $to_date),
							),
							'inclusive' => true,
						)
					);
				}

				$booking_ids = get_posts( $agrs );

	
				el_get_template( 'vendor/export-booking-popup.php', array( 'booking_ids' => $booking_ids ) );
		
			}

			wp_die();

			// End
		}

		public function export_csv_ticket() {
			$html = '';
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {

				$id_event 	= isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';

				if ( ! $id_event || ! verify_current_user_post( $id_event ) || ! el_can_manage_ticket() ) wp_die();

				$from_date 	= isset( $_POST['from_date'] ) && ! empty( $_POST['from_date'] ) ? absint( strtotime($_POST['from_date']) ) : '';
				$to_date 	= isset( $_POST['to_date'] ) && ! empty( $_POST['to_date'] ) ? absint( strtotime($_POST['to_date']) ) + (3600*24) - 1 : '';

				$args = [
					'post_type' => 'el_tickets',
					'post_status' => 'publish',
					"meta_query" => [
						'relation' => 'AND',
						[
							"key" 	=> OVA_METABOX_EVENT . 'event_id',
							"value" => $id_event,
						],
					],
					'posts_per_page' => -1,
					'fields' => 'ids',
				];

				if ( $from_date && $to_date ) {
					$args['date_query'] = array(
						array(
							'after' 	=> gmdate('F j, Y', $from_date),
							'before' 	=> array(
								'year' 	=> gmdate("Y", $to_date),
								'month' => gmdate("n", $to_date),
								'day' 	=> gmdate("j", $to_date),
							),
							'inclusive' => true,
						),
					);
				}

				$ticket_ids = get_posts( $args );

			
				el_get_template( 'vendor/export-ticket-popup.php', array( 'ticket_ids' => $ticket_ids ) );
	
			}
	
			wp_die();

			// End
		}

		public function el_add_package() {

			if( !isset( $_POST['data'] ) ) wp_die();


			$post_data = $_POST['data'];
			$user_id = wp_get_current_user()->ID;

			// check user login
			if( ! $user_id ) { 
				echo json_encode( array(
					'code' 		=> 0, 
					'status' 	=> esc_html__('You have to login','eventlist'),
					'url'		=> wp_login_url()
				) ); wp_die(); 
			}

			$pid = isset( $post_data['pid'] ) ? (int)$post_data['pid'] : '';
			$payment_method = isset( $post_data['payment_method'] ) ? sanitize_text_field( $post_data['payment_method'] ) : '';
			$package = get_post_meta( $pid, OVA_METABOX_EVENT.'package_id', true );

			$can_add_package = apply_filters( 'el_can_add_package', true, $pid );

			$response_can_not_add = array(
				'code' 		=> 0, 
				'status' 	=> esc_html__('Can\'t add membership' ,'eventlist'),
				'url'		=> get_myaccount_page()
			);

			// check user can register package
			if( $pid && $can_add_package ){

				// Add to membership table
				$membership_id = EL_Package::instance()->add_membership( $pid, $user_id );
				// if success
				if( $membership_id ){

					$fee_register_package = get_post_meta( $pid, OVA_METABOX_EVENT.'fee_register_package', true );

					if( $fee_register_package ){
						// get payment gateway
						if ( ! empty( $payment_method ) ) {
							$response = array(
								'payment_method' 	=> $payment_method,
								'code' 				=> $package,
								'membership_id' 	=> $membership_id,
							);
							echo json_encode( $response );
						} else {
							echo json_encode( $response_can_not_add );
						}
					// If free
					} else {
						
						update_user_meta( $user_id, 'package', $package );
						$membership = array(
							'ID'           => $membership_id,
							'post_status'   => 'Publish',
							'meta_input'	=> array(
								OVA_METABOX_EVENT.'payment' => 'free',
							)
						);

						$check_update = wp_update_post( $membership );

						if (  is_wp_error( $check_update ) ) {
							echo json_encode( array(
								'code' => 0,
								'status' => esc_html__('Update Failed','eventlist'), 
								'url'	=>  add_query_arg( array( 
									'vendor' => 'package'
								),
								get_myaccount_page() )
							) );
						} else {
							echo json_encode( array(
								'code' => $package,
								'status' => esc_html__('Update Success','eventlist'), 
								'url'	=>  add_query_arg( array( 
									'vendor' => 'package'
								),
								get_myaccount_page() )
							) );
						}

						
					}


				}else{
					echo json_encode( $response_can_not_add );
				}
			
			}else{
				wp_send_json( array(
					'code' => 0, 
					'status' => esc_html__('You dont have permission to add package','eventlist'),
					'url'	=> '#'
				) ); wp_die(); 
			}


			wp_die();
		}

		public function el_add_wishlist() {
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = isset($_POST['data']) ? $_POST['data'] : [];
			$id_event = sanitize_text_field($post_data['id_event']);
			if (empty($id_event)) wp_die();

			$cookie_name = "el_wl_event";
			$cookie_value = json_encode([$id_event]);
			$current_time = current_time("timestamp");


			if (!isset($_COOKIE['el_wl_event'])) {
				setcookie($cookie_name, $cookie_value, $current_time + (86400 * 30), "/");
			} else {
				$value_cookie = $_COOKIE['el_wl_event'];
				$value_cookie = str_replace("\\", "", $value_cookie);
				$value_cookie = json_decode($value_cookie, true);

				if (!empty($value_cookie) && is_array($value_cookie) && !in_array($id_event, $value_cookie)) {
					array_push($value_cookie, $id_event);
				}

				$cookie_value = json_encode($value_cookie);
				setcookie($cookie_name, $cookie_value, $current_time + (86400 * 30), "/");

			}

			wp_die(); 
		}

		public function el_remove_wishlist() {
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = isset($_POST['data']) ? $_POST['data'] : [];
			$id_event = sanitize_text_field($post_data['id_event']);

			$cookie_name = "el_wl_event";
			$current_time = current_time("timestamp");

			if (empty($id_event)) wp_die();

			if (isset($_COOKIE['el_wl_event'])) {

				$value_cookie = $_COOKIE['el_wl_event'];
				$value_cookie = str_replace("\\", "", $value_cookie);
				$value_cookie = json_decode($value_cookie, true);

				if (!empty($value_cookie) && is_array($value_cookie) && in_array($id_event, $value_cookie)) {
					$value_cookie = array_diff($value_cookie, [$id_event]);
				}
				if (empty($value_cookie)) {
					setcookie($cookie_name, "", -3600, "/");
				} else {
					$cookie_value = json_encode($value_cookie);
					setcookie($cookie_name, $cookie_value, $current_time + (86400 * 30), "/");
				}

			}

			wp_die();
		}

		/* Update Bank */
		public static function el_update_payout_method() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$_prefix = OVA_METABOX_EVENT;

			$post_data = $_POST['data'];

			$user_id = wp_get_current_user()->ID;

			if( !isset( $post_data['el_update_payout_method_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_update_payout_method_nonce'] ), 'el_update_payout_method_nonce' ) ) return ;


            $payout_method  = isset( $post_data['payout_method'] ) ? sanitize_text_field( $post_data['payout_method'] ) : '';
			$user_bank_owner  = isset( $post_data['user_bank_owner'] ) ? sanitize_text_field( $post_data['user_bank_owner'] ) : '';
			$user_bank_number = isset( $post_data['user_bank_number'] ) ? sanitize_user( $post_data['user_bank_number'] ) : '';
			$user_bank_name   = isset( $post_data['user_bank_name'] ) ? sanitize_text_field( $post_data['user_bank_name'] ) : '';
			$user_bank_branch = isset( $post_data['user_bank_branch'] ) ? sanitize_text_field( $post_data['user_bank_branch'] ) : '';
			$user_bank_routing = isset( $post_data['user_bank_routing'] ) ? sanitize_text_field( $post_data['user_bank_routing'] ) : '';
			$user_bank_paypal_email = isset( $post_data['user_bank_paypal_email'] ) ? sanitize_text_field( $post_data['user_bank_paypal_email'] ) : '';
			$user_bank_stripe_account = isset( $post_data['user_bank_stripe_account'] ) ? sanitize_text_field( $post_data['user_bank_stripe_account'] ) : '';
			$user_bank_iban = isset( $post_data['user_bank_iban'] ) ? sanitize_text_field( $post_data['user_bank_iban'] ) : '';
			$user_bank_swift_code = isset( $post_data['user_bank_swift_code'] ) ? sanitize_text_field( $post_data['user_bank_swift_code'] ) : '';
			$user_bank_ifsc_code = isset( $post_data['user_bank_ifsc_code'] ) ? sanitize_text_field( $post_data['user_bank_ifsc_code'] ) : '';
			$data_payout_method_field = isset( $post_data['data_payout_method_field'] ) ? sanitize_list_checkout_field( $post_data['data_payout_method_field'] ) : [];

			$post_data = array( 
				'user_bank_owner'  => $user_bank_owner,
				'user_bank_number' => $user_bank_number,
				'user_bank_name'   => $user_bank_name,
				'user_bank_branch' => $user_bank_branch,
				'user_bank_routing' => $user_bank_routing,
				'user_bank_paypal_email' => $user_bank_paypal_email,
				'user_bank_stripe_account' => $user_bank_stripe_account,
				'user_bank_iban' => $user_bank_iban,
				'user_bank_swift_code' => $user_bank_swift_code,
				'user_bank_ifsc_code' => $user_bank_ifsc_code,
				'payout_method' => $payout_method,
				'data_payout_method_field' => json_encode( $data_payout_method_field, JSON_UNESCAPED_UNICODE ),
			);

			foreach($post_data as $key => $value) {
				update_user_meta( $user_id, $key, $value );
			}
			echo esc_html('true');
			wp_die();
		}


		/* Add withdrawal */
		public static function el_add_withdrawal() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$_prefix = OVA_METABOX_EVENT;

			$post_data = $_POST['data'];

			$user_id = wp_get_current_user()->ID;
			if (empty($user_id)) die();

			if( !isset( $post_data['el_add_withdrawal_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_add_withdrawal_nonce'] ), 'el_add_withdrawal_nonce' ) ) return ;
            
			$amount  = isset( $post_data['amount'] ) ? floatval( $post_data['amount'] ) : '';
			

			$total_earning  = EL_Payout::instance()->get_total_profit( $user_id );
			$total_amount_payout = EL_Payout::instance()->get_total_amount_payout( $user_id );

			$withdrawable = $total_earning - $total_amount_payout;
     

			if( $amount == null || $amount == "") {

				wp_send_json( array( 'status' => 'error', 'msg' => esc_html__( 'First name must be filled out', 'eventlist' ) ) );
				wp_die();

			} else if( !is_numeric( $amount ) || $amount < 0 ){

				wp_send_json( array( 'status' => 'error', 'msg' => esc_html__( 'Amount must Number and more than 0', 'eventlist' ) ) );
				wp_die();

			} else if( ( $amount - $withdrawable ) > 0.00000000000001 ){
				wp_send_json( array( 'status' => 'error', 'msg' => esc_html__( 'Amount must be less than', 'eventlist' ).' '.$withdrawable ) );
				wp_die();				

			}else{

				$payout_method = get_user_meta( $user_id, 'payout_method', true );
				$meta_payout_method = [];
				switch ( $payout_method ) {
					case 'bank':
						$meta_payout_method[ $_prefix.'user_bank_owner' ] 		= get_user_meta( $user_id, 'user_bank_owner', true );
						$meta_payout_method[ $_prefix.'user_bank_number' ] 		= get_user_meta( $user_id, 'user_bank_number', true );
						$meta_payout_method[ $_prefix.'user_bank_name' ] 		= get_user_meta( $user_id, 'user_bank_name', true );
						$meta_payout_method[ $_prefix.'user_bank_branch' ] 		= get_user_meta( $user_id, 'user_bank_branch', true );
						$meta_payout_method[ $_prefix.'user_bank_routing' ] 	= get_user_meta( $user_id, 'user_bank_routing', true );
						$meta_payout_method[ $_prefix.'user_bank_iban' ] 		= get_user_meta( $user_id, 'user_bank_iban', true );
						$meta_payout_method[ $_prefix.'user_bank_swift_code' ] 	= get_user_meta( $user_id, 'user_bank_swift_code', true );
						$meta_payout_method[ $_prefix.'user_bank_ifsc_code' ] 	= get_user_meta( $user_id, 'user_bank_ifsc_code', true );
						break;
					case 'paypal':	
						$meta_payout_method[ $_prefix.'user_bank_paypal_email' ] = get_user_meta( $user_id, 'user_bank_paypal_email', true );
						break;
					default:
					    $meta_payout_method[ $_prefix.'data_payout_method_field' ] = get_user_meta( $user_id, 'data_payout_method_field', true );
					    break;
					
				}

				$post_data['post_type'] = 'payout';
				$post_data['post_status'] = 'publish';
				$post_data['post_author'] = $user_id;

				$meta_custom_fields = array(
					$_prefix.'amount'  => $amount,
					$_prefix.'time' => current_time( 'timestamp' ),
					$_prefix.'withdrawal_status' => 'Pending',
					$_prefix.'payout_method'	=> $payout_method
				);

				$meta_input = array_merge( $meta_custom_fields, $meta_payout_method );

				$post_data['meta_input'] = apply_filters( 'el_payout_metabox_input', $meta_input );

				// Get all bookings doesn't payout yet
				$bookings = EL_Booking::instance()->get_bookings_do_not_payout( $user_id );

				if( $bookings->have_posts() ) : while ( $bookings->have_posts() ) : $bookings->the_post();

					$booking_id = get_the_id();

					// Update Profit Status of booking to 'Waiting'
					update_post_meta( $booking_id, $_prefix.'profit_status', 'Waiting' );

				endwhile; endif; wp_reset_postdata();


				if( $payout_id = wp_insert_post( $post_data, true ) ){
					//update title booking
					$arr_post = [
						'ID' => $payout_id,
						'post_title' => $payout_id ,
					];
					wp_update_post($arr_post);

					// handle send mail to admin
					if ( el_enable_send_withdrawal_email() === true ) {
						$send_mail_to_admin = el_send_mail_admin_payout_request( $user_id, $payout_id, $amount, $payout_method );
						if ( ! $send_mail_to_admin ) {
							echo json_encode( array( 'status' => 'error', 'msg' => esc_html__( 'Error sending email to admin', 'eventlist' ) ) );
							wp_die();
						}
					}
					
					return $payout_id;
					wp_die();

				}else{
					return;
					wp_die();
				}

			}
		}


		/* Load Location Search */
		public static function el_load_location_search() {
			$keyword = isset($_POST['keyword']) ? sanitize_text_field( $_POST['keyword'] ) : '';

			$args = array(
				'taxonomy'   => 'event_loc',
				'orderby'    => 'id', 
				'order'      => 'ASC',
				'hide_empty' => false,
				'fields'     => 'all',
				'name__like' => $keyword,
			); 

			$terms = get_terms( $args );

			$count = count($terms);
			if($count > 0){
				$value = array();
				foreach ($terms as $term) {
					$value[] = $term->name;
				}
			}

			wp_send_json( $value );

			wp_die();
		}

		public static function el_search_map() {
			if( !isset( $_POST['data'] ) ) wp_die();
			$_prefix = OVA_METABOX_EVENT;

			$post_data = $_POST['data'];

			$map_lat = isset( $post_data['map_lat'] ) ? floatval( $post_data['map_lat'] ) : '';
			$map_lng = isset( $post_data['map_lng'] ) ? floatval( $post_data['map_lng'] ) : '';
			$radius = isset( $post_data['radius'] ) ? floatval( $post_data['radius'] ) : '';
			$radius_unit = isset( $post_data['radius_unit'] ) ? sanitize_text_field( $post_data['radius_unit'] ) : 'km';
			$show_featured = isset( $post_data['show_featured'] ) ? sanitize_text_field( $post_data['show_featured'] ) : '';

			/***** Query Radius *****/
			$args_query_radius = array(
				'post_type' => 'event',
				'posts_per_page' => -1,
			);

			/* Show Featured */
			if ($show_featured == 'yes') {
				$args_featured = array(
					'meta_key' =>  OVA_METABOX_EVENT.'event_feature',
					'meta_query'=> array(
						array(
							'key' =>  OVA_METABOX_EVENT.'event_feature',
							'compare' => '=',
							'value' => 'yes',
						)
					)
				);
			} else {
				$args_featured = array();
			}



			$args_query_radius2 = array_merge( $args_query_radius,$args_featured  );

			$the_query = new WP_Query( $args_query_radius2);

			$results = array();

			$arr_distance = array();

			$posts = $the_query->get_posts();

			if ($map_lat != '' || $map_lng != '') {
				foreach($posts as $post)  {
					/* Latitude Longitude Search */
					$lat_search = deg2rad($map_lat);
					$lng_search = deg2rad($map_lng);

					/* Latitude Longitude Post */
					$lat_post = deg2rad( floatval( get_post_meta( $post->ID, OVA_METABOX_EVENT.'map_lat', true ) ) );
					$lng_post = deg2rad( floatval( get_post_meta( $post->ID, OVA_METABOX_EVENT.'map_lng', true ) ) );

					$lat_delta = $lat_post - $lat_search;
					$lon_delta = $lng_post - $lng_search;

					// $angle = 2 * asin(sqrt(pow(sin($lat_delta / 2), 2) + cos($lat_search) * cos($lat_post) * pow(sin($lon_delta / 2), 2)));
					$angle = acos(sin($lat_search) * sin($lat_post) + cos($lat_search) * cos($lat_post) * cos($lng_search - $lng_post));

					/* 6371 = the earth's radius in km */
					/* 3959 = the earth's radius in mi */
					$distance =  6371 * $angle;

					if ( 'mi' === $radius_unit ) {
						$distance =  3959 * $angle;
					}

					if( $distance <= $radius || !$map_lat ) {
						array_push($arr_distance, $distance);
						array_push( $results, $post->ID );
					}
				}

				wp_reset_postdata();
				array_multisort($arr_distance, $results);

			} else {
				foreach($posts as $post)  {
					array_push( $results, $post->ID );
				}
			}

			if ( $map_lat && !$results ) {
				$results = array('');
			}
			/***** End Query Radius *****/


			/***** Query Post in Radius *****/
			$orderby 	= EL()->options->event->get('archive_order_by', 'ID');
			$order 		= EL()->options->event->get('archive_order', 'DESC');

			$listing_posts_per_page = EL()->options->event->get( 'listing_posts_per_page', '12' );
			$choose_week_end = EL()->options->general->get('choose_week_end') != null ? EL()->options->general->get('choose_week_end') : array('saturday', 'sunday');

			$keyword 	= isset( $post_data['keyword'] ) ? sanitize_text_field( $post_data['keyword'] ) : '';
			$cat 		= isset( $post_data['cat'] ) ? sanitize_text_field( $post_data['cat'] ) : '';
			$sort 		= isset( $post_data['sort'] ) ? sanitize_text_field( $post_data['sort'] ) : apply_filters( 'search_event_sort_default', 'date-desc' );

			$name_venue = isset( $post_data['name_venue'] ) ? esc_html( $post_data['name_venue'] ) : '' ;
			$time 		= isset( $post_data['time'] ) ? sanitize_text_field( $post_data['time'] ) : '';
			$start_date = isset( $post_data['start_date'] ) ? sanitize_text_field( $post_data['start_date'] ) : '';
			$end_date 		= isset( $post_data['end_date'] ) ? sanitize_text_field( $post_data['end_date'] ) : '';
			$event_state 	= isset( $post_data['event_state'] ) ? esc_html( $post_data['event_state'] ) : '' ;
			$event_city 	= isset( $post_data['event_city'] ) ? esc_html( $post_data['event_city'] ) : '' ;

			$type 	= isset( $post_data['type'] ) ? sanitize_text_field( $post_data['type'] ) : '';
			$column = isset( $post_data['column'] ) ? sanitize_text_field( $post_data['column'] ) : '';

			$event_type = isset( $post_data['event_type'] ) ? sanitize_text_field( $post_data['event_type'] ) : '';

			$el_data_taxonomy_custom = isset( $post_data['el_data_taxonomy_custom'] ) ? sanitize_text_field( $post_data['el_data_taxonomy_custom'] )  : '';

			$el_data_taxonomy_custom = str_replace( '\\', '',  $el_data_taxonomy_custom);
			if( $el_data_taxonomy_custom ){
				$el_data_taxonomy_custom = json_decode($el_data_taxonomy_custom, true);

			}

			$max_price = isset( $post_data['max_price'] ) ? sanitize_text_field( $post_data['max_price'] ) : '';
			$min_price = isset( $post_data['min_price'] ) ? sanitize_text_field( $post_data['min_price'] ) : '';

			$paged = isset( $post_data['paged'] ) ? (int)$post_data['paged']  : 1;

			$filter_events 	= EL()->options->event->get('filter_events', 'all');
			$current_time 	= current_time('timestamp');

			$args_base = array(
				'post_type'      => 'event',
				'post_status'    => 'publish',
				'paged'          => $paged,
				'posts_per_page' => $listing_posts_per_page,
			);

			$args_order 	=  array( 'order' => 'DESC' );
			$args_orderby 	=  array( 'orderby' => 'date' );

			switch ( $sort ) {
				
				// Filter Order
				case 'date-desc':
				$args_orderby =  array( 'orderby' => 'date' );
				break;

				case 'date-asc':
				$args_orderby = array( 'orderby' => 'date' );
				$args_order = array( 'order' => 'ASC' );
				break;

				case 'near':
				$args_orderby = array( 'orderby' => 'post__in');
				$args_order = array( 'order' => 'ASC' );
				break;

				case 'start-date':
				$args_orderby = array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'start_date_str' );
				$args_order = array( 'order' => 'ASC' );
				break;

				case 'end-date':
				$args_orderby = array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'end_date_str' );
				$args_order = array( 'order' => 'ASC' );
				break;

				case 'a-z':
				$args_orderby = array( 'orderby' => 'title');
				$args_order = array( 'order' => 'ASC' );
				break;

				case 'z-a':
				$args_orderby = array( 'orderby' => 'title');
				break;

				default:

				switch ( $orderby ) {
					case 'title':
					$args_orderby =  array( 'orderby' => 'title' );
					$args_order =  array( 'order' => $order );
					break;

					case 'start_date':
					$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'start_date_str' );
					$args_order =  array( 'order' => $order );
					break;

					case 'end_date':
					$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => $_prefix.'start_date_str' );
					$args_order =  array( 'order' => $order );
					break;

					case 'near':
					$args_orderby = array( 'orderby' => 'post__in');
					$args_order = array( 'order' => 'ASC' );
					break;

					case 'date_desc':
					$args_orderby =  array( 'orderby' => 'date' );
					break;

					case 'date_asc':
					$args_orderby = array( 'orderby' => 'date' );
					$args_order = array( 'order' => 'ASC' );
					break;
					
					default:
					$args_orderby =  array( 'orderby' => 'ID');
					$args_order =  array( 'order' => $order );
					break;
				}

				break;
			}

			$args_basic = array_merge_recursive( $args_base, $args_order, $args_orderby,$args_featured );

			$args_filter_price = array();
			if ( $max_price != '' && $min_price != '' ) {
				$decimals = (int) EL()->options->general->get('number_decimals',2);

				$max_price_format = (float) $max_price;
				$max_price_format = round($max_price_format,$decimals);

				$min_price_format = (float) $min_price;
				$min_price_format = round($min_price_format,$decimals);

				$args_filter_price = array(
					'meta_query' => array(
						array(
							'relation' => 'OR',
							array(
								'key' 		=> OVA_METABOX_EVENT.'min_price',
								'value' 	=> array($min_price_format, $max_price_format),
								'compare' 	=> 'BETWEEN',
								'type' 		=> 'DECIMAL',
							),
							array(
								'key' 		=> OVA_METABOX_EVENT.'max_price',
								'value' 	=> array($min_price_format, $max_price_format),
								'compare' 	=> 'BETWEEN',
								'type' 		=> 'DECIMAL',
							),
						),
					),
				);
			}

			$args_radius = $args_name = $args_cat = $args_time = $args_date = $args_venue = $args_state = $args_city = $args_event_type = $args_filter_events = array();

			// Query Result
			if ( $results ) {
				$args_radius = array( 'post__in' => $results );
			}

			// Query Keyword
			if( $keyword ){
				$args_name = array( 's' => $keyword );
			}

			// Query Categories
			if($cat){
				$args_cat = array(
					'tax_query' => array(
						array(
							'taxonomy' 	=> 'event_cat',
							'field'    	=> 'slug',
							'terms' 	=> $cat
						)
					)
				);
			}


			//Query Custom Taxonomy
			$arg_taxonomy_arr = [];
			if( $el_data_taxonomy_custom ) {
				$arg_taxonomy_arr = [];
			    if ( ! empty( $el_data_taxonomy_custom ) ) {
			        foreach( $el_data_taxonomy_custom as $taxo => $value_taxo ) {
			        	if( ! empty( $value_taxo ) ) {
			        		$arg_taxonomy_arr[] = array(
		                		'taxonomy' 	=> $taxo,
			                    'field' 	=> 'slug',
			                    'terms' 	=> $value_taxo
			                );
			        	}
			        }
			    }

			    if( !empty($arg_taxonomy_arr) ){
			        $arg_taxonomy_arr = array(
			            'tax_query' => $arg_taxonomy_arr
			        );
			    }
			}


			// Query Time
			if( $time ){

				$date_format = 'Y-m-d 00:00';
				$today_day = current_time( $date_format);

				// Return number of current day
				$num_day_current = gmdate('w', strtotime( $today_day ) );

				// Check start of week in wordpress
				$start_of_week = get_option('start_of_week');

				// This week
				$week_start = gmdate( 'Y-m-d', strtotime($today_day) - ( ($num_day_current - $start_of_week) *24*60*60) );
				$week_end = gmdate( 'Y-m-d', strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60 );
				$this_week = el_getDatesFromRange( $week_start, $week_end );
				$this_week_regexp = implode( '|', $this_week );
				

				// Get Saturday in this week
				$saturday = strtotime( gmdate($date_format, strtotime('this Saturday')));
				// Get Sunday in this week
				$sunday = strtotime( gmdate( $date_format, strtotime('this Sunday')));
				// Weekend
				$week_end = el_getDatesFromRange( gmdate( 'Y-m-d', $saturday ), gmdate( 'Y-m-d', $sunday ) );
				$week_end_regexp = implode('|', $week_end );
				


				// Next week Start
				$next_week_start = strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60;
				// Next week End
				$next_week_end = $next_week_start+7*24*60*60;
				
				// Next week
				$next_week = el_getDatesFromRange( gmdate( 'Y-m-d', $next_week_start ), gmdate( 'Y-m-d', $next_week_end ) );
				$next_week_regexp = implode( '|', $next_week );
				

				// Month Current
				$num_day_current = gmdate('n', strtotime( $today_day ) );

				// First day of next month
				$first_day_next_month = strtotime( gmdate( $date_format, strtotime('first day of next month') ) );
				$last_day_next_month = strtotime ( gmdate( $date_format, strtotime('last day of next month') ) )+24*60*60+1;
				// Next month
				$next_month = el_getDatesFromRange( gmdate( 'Y-m-d', $first_day_next_month ), gmdate( 'Y-m-d', $last_day_next_month ) );
				$next_month_regexp = implode( '|', $next_month );
				
				
				

				switch ( $time ) {
					case 'today':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> strtotime($today_day),
								'compare' 	=> 'LIKE'	
							),
						)
					);

					break;

					case 'tomorrow':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> strtotime($today_day) + 24*60*60,
								'compare' 	=> 'LIKE'	
							),
						)
					);
					break;

					case 'this_week':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> $this_week_regexp,
								'compare' 	=> 'REGEXP'	
							),
						)
					);
					break;

					case 'this_week_end':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> $week_end_regexp,
								'compare' 	=> 'REGEXP'	
							),
						)
					);
					break;

					case 'next_week':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> $next_week_regexp,
								'compare' 	=> 'REGEXP'	
							),
						)
					);
					break;

					case 'next_month':
					$args_time = array(
						'meta_query' => array(
							array(
								'key' 		=> $_prefix.'event_days',
								'value' 	=> $next_month_regexp,
								'compare' 	=> 'REGEXP'	
							),
						)
					);
					break;

					default:
					break;
				}
			}

			// Query Date
			if( $start_date && $end_date ){

				$between_dates = el_getDatesFromRange( gmdate( 'Y-m-d', strtotime( $start_date ) ), gmdate( 'Y-m-d', strtotime( $end_date ) + 24*60*60 ) );
				$between_dates_regexp = implode( '|', $between_dates );

				$args_date = array(
					'meta_query' => array(
						array(
							'key' => $_prefix.'event_days',
							'value' => $between_dates_regexp,
							'compare' => 'REGEXP'
						),
					)
				);

			}else if( $start_date && ! $end_date ){

				$args_date = array(
					'meta_query' => array(
						array(
							'key' => $_prefix.'event_days',
							'value' => strtotime( $start_date ),
							'compare' => 'LIKE'
						)
					)	
				);

			} else if( ! $start_date && $end_date ){
				$args_date = array(
					'meta_query' => array(
						array(
							'key' => $_prefix.'event_days',
							'value' => strtotime( $end_date ),
							'compare' => 'LIKE'
						),
					)	
				);
			}

			// Query Venue
			if($name_venue){
				$args_venue = array(
					'meta_query' => array(
						array(
							'key' => $_prefix.'venue',
							'value' => $name_venue,
							'compare' => 'LIKE'
						)
					)
				);
			}

			// Query State
			if($event_state){
				$args_state = array(
					'tax_query' => array(
						array(
							'taxonomy' => 'event_loc',
							'field'    => 'slug',
							'terms' => $event_state
						)
					)
				);
			}

			// Query City
			if($event_city){
				$args_city = array(
					'tax_query' => array(
						array(
							'taxonomy' => 'event_loc',
							'field'    => 'slug',
							'terms' => $event_city
						)
					)
				);
			}

			// Query Event Type
			if( $event_type ){
				$args_event_type = array(
					'meta_query' => array(
						array(
							'key' => $_prefix.'event_type',
							'value' => $event_type,
							'compare' => 'LIKE'	
						),
					)
				);
			}

			

			// Query filter
			$args_filter_events = el_sql_filter_status_event( $filter_events );

			$args = array_merge_recursive( $args_basic, $args_radius, $args_name, $args_cat, $args_time , $args_date, $args_venue, $args_state, $args_city, $args_event_type, $args_filter_events, $arg_taxonomy_arr, $args_filter_price );
			
			$events = new WP_Query( apply_filters( 'el_search_map_event_query', $args, $post_data  ) );

			/***** End Query Post in Radius *****/
			
			ob_start();
			
			?>

			<div class="event_archive <?php echo esc_attr($type . ' ' . $column); ?>" style="display: grid;">
				<?php
				if($events->have_posts() ) : while ( $events->have_posts() ) : $events->the_post();

					el_get_template_part( 'content', 'event-'.$type );
					$id = get_the_id();

					$lat_event = get_post_meta( $id, OVA_METABOX_EVENT.'map_lat', true );
					$lng_event = get_post_meta( $id, OVA_METABOX_EVENT.'map_lng', true );
					
					?>
					<div class="data_event" style="display: none;"
					data-link_event="<?php echo esc_attr( get_the_permalink() ); ?>"
					data-title_event="<?php echo esc_attr( get_the_title() ); ?>"
					data-date="<?php echo esc_attr(get_event_date_el()); ?>"
					data-average_rating="<?php echo esc_attr( get_average_rating_by_id_event( get_the_id() ) ); ?>"
					data-number_comment="<?php echo esc_attr( get_number_coment_by_id_event( get_the_id() ) ); ?>"

					data-map_lat_event="<?php echo esc_attr( $lat_event ); ?>"
					data-map_lng_event="<?php echo esc_attr( $lng_event ); ?>"

					data-thumbnail_event="<?php echo esc_attr( ( has_post_thumbnail() && get_the_post_thumbnail() ) ? esc_url( wp_get_attachment_image_url( get_post_thumbnail_id() , 'el_img_squa' ) ) : esc_url( EL_PLUGIN_URI.'assets/img/no_tmb_square.png' ) ); ?>"
					data-marker_price="<?php echo esc_attr( get_price_ticket_by_id_event( array('id_event' => $id) ) ); ?>"
					data-marker_date="<?php echo esc_attr( get_event_date_el() ); ?>"
					 data-show_featured="<?php echo esc_attr($show_featured); ?>"
					></div>

				<?php endwhile; wp_reset_postdata(); else: ?>

				<div class="not_found_event"> <?php esc_html_e( 'Not found event', 'eventlist' ); ?> </div>

				<?php ; endif; ?>
			</div>

			<?php 
			$total = $events->max_num_pages;

			if ( $total > 1 ) {  ?>
				<div class="el-pagination">
					<?php 
					el_pagination_event_ajax($events->found_posts, $events->query_vars['posts_per_page'], $paged);
					?>
				</div>
			<?php }
			$result = ob_get_contents(); 
			ob_end_clean();

			ob_start(); ?>
			<div class="listing_found">
				<?php if ($events->found_posts == 1) { ?>
					<span><?php echo sprintf( esc_html__( '%s Result Found', 'eventlist' ), esc_html( $events->found_posts ) ); ?></span>
				<?php } else { ?>
					<span><?php echo sprintf( esc_html__( '%s Results Found', 'eventlist' ), esc_html( $events->found_posts ) ); ?></span>
				<?php } ?>

				<?php if ( $paged == ceil($events->found_posts/$events->query_vars['posts_per_page']) ) { ?>
					<span>
						<?php echo sprintf( esc_html__( '(Showing %1$s-%2$s)', 'eventlist' ), esc_html( (($paged - 1) * $events->query_vars['posts_per_page'] + 1)), esc_html($events->found_posts) ); ?>
					</span>
				<?php } elseif( !$events->have_posts() ) { ?>
					<span></span>
				<?php } else { ?>
					<span>
						<?php echo sprintf( esc_html__( '(Showing %1$s-%2$s)', 'eventlist' ), esc_html(($paged - 1) * $events->query_vars['posts_per_page'] + 1), esc_html($paged * $events->query_vars['posts_per_page']) ); ?>
					</span>
				<?php } ?>
			</div>

			<?php
			$pagination = ob_get_contents();
			ob_end_clean();

			wp_send_json( array(
				"result" 		=> $result,
				"pagination" 	=> $pagination
			) );

			wp_die();
		}

		public function el_filter_elementor_grid () {

			if( !isset( $_POST ) ) wp_die();

			$filter 		= isset( $_POST['filter'] ) ? sanitize_text_field( $_POST['filter'] ) : "";
			$status 		= isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : "";
			$order 			= isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : "";
			$orderby 		= isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : "ID";
			$number_post 	= isset( $_POST['number_post'] ) ? sanitize_text_field( $_POST['number_post'] ) : "";
			$display_img 	= isset( $_POST['display_img'] ) ? sanitize_text_field( $_POST['display_img'] ) : '';

			$term_id_filter_string = isset( $_POST['term_id_filter_string'] ) ? sanitize_text_field( $_POST['term_id_filter_string'] ) : "";
			$type_event = isset( $_POST['type_event'] ) ? sanitize_text_field( $_POST['type_event'] ) : "";

			$term_id_filter = explode(',', $term_id_filter_string );
			$current_time 	= current_time( 'timestamp' );

			$agrs_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $number_post,
				'order' 			=> $order,
			];

			$template_args = array(
				'display_img' => $display_img,
			);

			$args_orderby = array();

			switch ( $orderby ) {
				case 'date':
				$args_orderby =  array( 'orderby' => 'date' );

				break;
				case 'title':
				$args_orderby =  array( 'orderby' => 'title' );
				break;

				case 'start_date':
				$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
				break;

				default:
				$args_orderby =  array( 'orderby' => 'ID');
				break;
			}

			if ( $filter == 'all' ) {
				$agrs_filter = [
					'tax_query' => [
						[
							'taxonomy' => 'event_cat',
							'field'    => 'id',
							'terms'    => $term_id_filter,
						]
					]
				];
			} else {
				$agrs_filter = [
					'tax_query' => [
						[
							'taxonomy' => 'event_cat',
							'field'    => 'id',
							'terms'    => $filter,
						]
					]
				];
			}

			switch ( $status ) {
				case 'feature' : {
					$agrs_status = [
						'meta_query' => [
							[
								'key' 		=> OVA_METABOX_EVENT . 'event_feature',
								'value' 	=> 'yes',
								'compare' 	=> '=',
							],
						],
					];
					break;
				}
				case 'upcoming' : {
					$agrs_status = [
						'meta_query' => 
						[
							'relation' => 'AND',
							[
								'key' 		=> OVA_METABOX_EVENT . 'end_date_str',
								'value' 	=> $current_time,
								'compare' 	=> '>',
								'type'		=> 'NUMERIC'
							],
							[
								'relation' => 'OR',
								[
									'key' 		=> OVA_METABOX_EVENT . 'start_date_str',
									'value' 	=> $current_time,
									'compare' 	=> '>',
									'type'		=> 'NUMERIC'
								],
								[
									'key' 		=> OVA_METABOX_EVENT . 'option_calendar',
									'value' 	=> 'auto',
									'compare' 	=> '='
								],
							]

						]
					];
					break;
				}
				case 'selling' : {
					$agrs_status = [
						'meta_query' => [
							'relation' => 'AND',
							[
								'key' 		=> OVA_METABOX_EVENT . 'start_date_str',
								'value' 	=> $current_time,
								'compare' 	=> '<=',
							],
							[
								'key' 		=> OVA_METABOX_EVENT . 'end_date_str',
								'value' 	=> $current_time,
								'compare' 	=> '>='
							]
						],
					];
					break;
				}

				case 'upcoming_selling': {
					$agrs_status = [
						'meta_query' => [
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>'
						],
					];
					break;
				}

				case 'closed' : {
					$agrs_status = [
						'meta_query' => [
							[
								'key' 		=> OVA_METABOX_EVENT . 'end_date_str',
								'value' 	=> $current_time,
								'compare' 	=> '<',
							]
						],
					];
					break;
				}

				default : {
					$agrs_status = [];
				}
			}

			$agrs = array_merge( $agrs_base, $agrs_filter, $agrs_status, $args_orderby );

			$events = new WP_Query( $agrs );
			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>

			<?php
			if($events->have_posts()) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ), $template_args );
				endwhile; wp_reset_postdata(); 
			else:
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;

			wp_die();
		}

		public function el_single_send_mail_report() {

			if( ! is_user_logged_in() ) {
				echo 'false';
				wp_die();
			} 

			$data = $_POST['data'];

			$id_event = (isset($data['id_event'])) ? sanitize_text_field($data['id_event']) : wp_die();
			$message = (isset($data['message'])) ? sanitize_text_field($data['message']) : "";
			
			$name_event = get_the_title($id_event);
			$link_event = get_the_permalink($id_event);

			$subject = EL()->options->mail->get( 'mail_report_event_subject', esc_html__( 'Report event', 'eventlist' ) );

			$body = EL()->options->mail->get('mail_report_event_content');

			if( !$body ) $body = 'The link event: [el_link_event]. [el_message]';

			$body = str_replace( '&lt;br&gt;', "<br>", $body );
			$body = str_replace( '[el_link_event]', '<a href="'.$link_event.'">'. $name_event . '</a><br>', $body);
			$body = str_replace( '[el_message]', esc_html( $message ) . "<br>", $body);

			if(el_submit_sendmail_report( $id_event, $subject, $body)) {
				echo 'true';
			} else {
				echo 'false';
			}

			wp_die();
		}

		public function el_single_send_mail_vendor() {
			
			if(!isset($_POST['data'])) wp_die();

			$data = $_POST['data'];

			$id_event = (isset($data['id_event'])) ? sanitize_text_field($data['id_event']) : wp_die();
			$name_event = get_the_title($id_event);
			$permalink = get_permalink( $id_event );

			$name = (isset($data['name'])) ? sanitize_text_field($data['name']) : "";
			$email = (isset($data['email'])) ? sanitize_email($data['email']) : "";
			$phone = (isset($data['phone'])) ? sanitize_text_field($data['phone']) : "";
			$subject = (isset($data['subject'])) ? sanitize_text_field($data['subject']) : esc_html__( 'The guest contact ', 'eventlist' ).$name_event;
			$content = (isset($data['content'])) ? sanitize_text_field($data['content']) : "[el_content]";
			

			$body = EL()->options->mail->get('mail_vendor_email_template');
			if( !$body ){
				$body = 'Event: [el_event_name]<br/>Name: [el_name]<br/>Email: [el_mail]<br/>Phone: [el_phone]<br/>Email: [el_content]';
			}
			$body = str_replace( '&lt;br&gt;', "<br>", $body );
			$body = str_replace( '[el_event_name]', '<a href="'.$permalink.'">'.esc_html( $name_event ).'</a>'. '<br>', $body );
			$body = str_replace( '[el_name]', esc_html( $name ) . '<br>', $body);
			$body = str_replace( '[el_mail]',esc_html( $email ) . '<br>', $body);
			$body = str_replace( '[el_phone]',esc_html( $phone ) . '<br>', $body);
			$body = str_replace( '[el_content]',esc_html( $content ) . '<br>', $body);

			if(el_custom_send_mail_vendor( $email, $id_event, $subject, $body)) {
				echo 'true';
			} else {
				echo 'false';
			}

			wp_die();
		}

		/* Change password */
		public static function el_update_role() {

			if( ! apply_filters( 'el_is_update_vendor_role', true ) ) wp_die();
			if( ! isset( $_POST['data'] ) ) wp_die();

			$post_data = $_POST['data'];
			
			if( !isset( $post_data['el_update_role_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_update_role_nonce'] ), 'el_update_role_nonce' ) ) return ;

			$role = isset( $post_data['role'] ) ? sanitize_text_field( $post_data['role'] ) : '';
		
			$user_id = wp_get_current_user()->ID;

			if ( $role == 'vendor' ) {
				
				$user = new WP_User( $user_id );
				$user->set_role( 'el_event_manager' );
				$member_account_id = EL()->options->general->get( 'myaccount_page_id', '' );
				$redirect_page = get_the_permalink( $member_account_id );

				$enable_package = EL()->options->package->get( 'enable_package', 'yes' );
				$default_package = EL()->options->package->get( 'package' );
				
				if( $enable_package == 'yes' && $default_package ){

					$pid = EL_Package::instance()->get_package( $default_package );

					if( EL_Package::instance()->add_membership( $pid['id'], $user_id, $status = 'new' ) ){
						$redirect_page = add_query_arg( 'vendor', 'package', $redirect_page );		
					}
					
				}
				
				wp_send_json(
					array( 'url' => $redirect_page )
				);

				wp_die();

			} 

			wp_die();
			
		}

		public function el_check_vendor_field_required(){
			$post_data = $_POST;

			$nonce = isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;

			$response = [];
			$response['status'] = 'error';
			$response['mess'] = '';
			$response['show_vendor_field'] = 'no';
			$response['vendor_field'] = '';
			$response['send_mail'] = '';
			$response['mail_mess'] = '';
			$response['reload_page'] = 'no';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'el_update_role_nonce' ) ) {
				$response['mess'] = esc_html__( 'Nonce is invalid', 'eventlist' );
				echo json_encode( $response );
				wp_die();
			}

			// check vendor field is not empty
			$user_meta_field = get_option( 'ova_register_form' );

			$flag = true;
			if ( ! empty( $user_meta_field ) && is_array( $user_meta_field ) ) {
				foreach ( $user_meta_field as $name => $field ) {
					if ( $field['used_for'] !== 'user' && $field['required'] == 'on' && $field['enabled'] == "on" ) {
						$user_meta_val = get_user_meta( $user_id, 'ova_'.$name, true );
						if ( empty( $user_meta_val ) ) {
							$flag = false;
						}
					}
				}
			}

			$vendor_status = get_user_meta( $user_id, 'vendor_status', true );

			switch ( $vendor_status ) {

				case 'pending':
					$response['mess'] = esc_html__( 'Your request is being approved by the administrator.', 'eventlist' );
					echo json_encode( $response );
					wp_die();
				break;

				case 'approve':
					$flag = false;
					$response['reload_page'] = 'yes';
				break;

				case 'reject':
					$flag = false;
				break;

				default:
					break;
			}

			if ( $flag ) {
				$current_time = current_time( 'timestamp' );
				update_user_meta( $user_id, 'vendor_status', 'pending');
				update_user_meta( $user_id, 'update_vendor_time', $current_time);
				// send mail to admin
				$user_email = $current_user->user_email;

				if ( ! ova_register_vendor_mailto_admin( $user_email ) ) {
					$response['send_mail'] = 'error';
					$response['mail_mess'] = esc_html__( 'An error occurred while sending notification email to the administrator.', 'eventlist' );
				}

				$response['status'] = 'success';
				$response['mess'] = esc_html__( 'You have successfully submitted your request, please wait for the administrator to approve.', 'eventlist' );
			} else {
				$response['mess'] = esc_html__( 'Please fill in all vendor information below and update your profile, then click the upgrade to Vendor Role button again.', 'eventlist' );
				$response['show_vendor_field'] = 'yes';
				$response['vendor_field'] = el_get_profile_custom_field_vendor( $user_id );
			}

			wp_send_json( $response );
			wp_die();
		}

		// Cancel Booking
		public static function el_cancel_booking(){

			if(!isset( $_POST )) wp_die();
			
			$id_booking = isset( $_POST['id_booking'] ) ? $_POST['id_booking'] : '';

			if( !isset( $_POST['el_cancel_booking_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $_POST['el_cancel_booking_nonce'] ), 'el_cancel_booking_nonce' ) ) return ;

			if( $id_booking && el_cancellation_booking_valid( $_POST['id_booking'] ) ){

				$id_customer_booking = get_post_meta( $id_booking, OVA_METABOX_EVENT.'id_customer', true );
				$current_user_id = get_current_user_id();

				// Check exactly customer who buy event
				if( $current_user_id == $id_customer_booking || current_user_can( 'administrator' ) ){
					
						$booking_update = array(
							'ID'           => $id_booking,
							'post_date'		=> current_time('mysql'),
							'meta_input'	=> array(
								OVA_METABOX_EVENT.'status' => 'Canceled',
							)
						);

						if( wp_update_post( $booking_update ) ){

							do_action( 'el_cancel_booking_succesfully', $id_booking );
							do_action( 'el_update_ticket_rest_cancel_booking_succesfully', $id_booking );
							echo json_encode( array( 'status' => 'success', 'msg' => esc_html__( 'Cancel Sucessfully', 'eventlist' ) ) );
							wp_die();
						}

				}
				
			}

			echo json_encode( array( 'status' => 'error', 'msg' => esc_html__( 'Error Cancellation', 'eventlist' ) ) );
			wp_die();
		}

        //load edit ticket in manage ticket
		public static function el_load_edit_ticket_calendar() {



				/**
				* Hook: el_vendor_edit_manage_ticket_max - 10
		        * @hooked:  el_vendor_edit_manage_ticket_max- 10
				*/
				do_action( 'el_vendor_edit_manage_ticket_max' );



		

			wp_die();



		}


		//choose_calendar in manage ticket


		public static function el_choose_calendar() {


				/**
				* Hook: el_vendor_calendar_manage_ticket - 10
		        * @hooked:  el_vendor_calendar_manage_ticket- 10
				*/
				do_action( 'el_vendor_calendar_manage_ticket' );



			wp_die();



		}


		/* 	update ticket max */
		public static function 	el_update_ticket_max() {

			if( !isset( $_POST['data'] ) ) wp_die();

			$_prefix = OVA_METABOX_EVENT;

			$post_data = $_POST['data'];

			if( !isset( $post_data['el_update_ticket_max_nonce'] ) || !wp_verify_nonce( sanitize_text_field( $post_data['el_update_ticket_max_nonce'] ), 'el_update_ticket_max_nonce' ) ) return ;

			$cal_id  = isset( $post_data['cal_id'] ) ? sanitize_text_field( $post_data['cal_id'] ) : '';
			$id = isset( $post_data['id'] ) ? sanitize_text_field( $post_data['id'] ) : '';
			$ticket_max = isset( $post_data['ticket_max'] ) ? ( $post_data['ticket_max'] ) : '';
			$max_ticket = get_post_meta( $id,  $_prefix.'ticket_max['.$cal_id.'_'. $value['ticket_id'].']', true);
            
			foreach ( $ticket_max as  $value ) {
				$number_ticket_sold = EL_Booking::instance()->get_number_ticket_booked($id, $cal_id,  $value['ticket_id']);
				if($number_ticket_sold > $value['ticket_max']  ){

					echo json_encode( array(  'msg' => esc_html__( 'Number ticket max must more than ', 'eventlist' ).' '.floatval( $number_ticket_sold ) ) );
					wp_die();

				}
				if(isset($max_ticket)){
					update_post_meta( $id,  $_prefix.'ticket_max['.$cal_id.'_'. $value['ticket_id'].']', $value['ticket_max'] );
				}else{

					add_post_meta( $id,  $_prefix.'ticket_max['.$cal_id.'_'. $value['ticket_id'].']', $value['ticket_max'] );

				}


			}
			

			echo json_encode( array( 'message' =>  esc_html__( 'Updated success!', 'eventlist' ) ) );


			
			wp_die();
		}




         
		public function el_check_date_search_ticket() {
			$start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
			$end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';
			$eid = isset($_POST['eid']) ? sanitize_text_field($_POST['eid']) : '';

			$start = isset( $start_time ) ? el_get_time_int_by_date_and_hour( $start_time,0) : '';
			$end = isset( $end_time ) ? el_get_time_int_by_date_and_hour( $end_time,0) : '';

			$check_number = floatval($end - $start);
			$number_day = EL()->options->role->get('day_search_ticket') ? EL()->options->role->get('day_search_ticket') : '7';
			$check_time = floatval($number_day)*24*60*60; 


			if($check_number > $check_time){

				echo json_encode( array(  'msg' => esc_html__( 'Number of search days must be less', 'eventlist' ).' '.floatval( $number_day ) ) );
				wp_die();

			}

			$member_account_id = EL()->options->general->get( 'myaccount_page_id', '' );
			$redirect_page = get_the_permalink( $member_account_id );
			$redirect_page = add_query_arg( 'vendor', 'manage_event&eid='.$eid.'&start_date_2='.$start_time.'&end_date_2='.$end_time, $redirect_page );

			echo json_encode( array(  'url' => $redirect_page ));	
			wp_die();
		}

		public function el_multiple_customers_ticket() {

			if ( ! isset($_POST['data']) ) return false;
			if ( ! isset( $_POST['data']['el_next_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field($_POST['data']['el_next_event_nonce']), 'el_next_event_nonce' ) ) return false;

			$post_data 		= $_POST['data'];
			$event_id 		= isset( $post_data['event_id'] ) ? $post_data['event_id'] : '';
			$seat_option 	= isset( $post_data['seat_option'] ) ? sanitize_text_field( $post_data['seat_option'] ) : 'no_seat';

			$cart 	= isset($post_data['cart']) ? (array)$post_data['cart'] : array();
			$nav 	= $result = $ticket_type = $seat_map = '';
			
			$response = [];

			if ( ! empty( $cart ) && is_array( $cart ) ) {
				$qty = 0;
				$ticket_ids = array();
				$seat_names = [];

				if ( 'map' === $seat_option ) {
					foreach ( $cart as $cart_item ) {
						if ( isset( $cart_item['qty'] ) && absint( $cart_item['qty'] ) ) {
							$qty += absint( $cart_item['qty'] );
							$seat_names[] = $cart_item['id'];

						} else if( isset( $cart_item['data_person'] ) ){
							foreach ( $cart_item['data_person'] as $k => $val ) {
								$qty += (int) $val['qty'];
								for ($i=0; $i < (int) $val['qty']; $i++) { 
									$seat_names[] = $cart_item['id'].' - '.$val['name'];
								}
								
							}
						} else {
							$qty += 1;
							// $seat_names[] = $cart_item['id'];

							$person_type = isset( $cart_item['person_type'] ) ? $cart_item['person_type'] : '';
							if ( ! empty( $person_type ) ) {
								$seat_names[] = $cart_item['id'].' - '.$person_type;
							} else {
								
								if ( $cart_item['qty'] ) {
									for ($i=0; $i < absint( $cart_item['qty'] ); $i++) { 
										$seat_names[] = $cart_item['id'];
									}
								} else {
									$seat_names[] = $cart_item['id'];
								}
							}
						}
					}
					// seat map for first ticket form
					ob_start();
					el_get_seat_html_form_cart( $seat_names );
					$seat_map = ob_get_clean();

					$ticket_ids = el_get_ticket_ids_form_cart( $cart, 'map' );
				} else {
					$qty = el_get_quantity_form_cart( $cart );

					ob_start();
					el_get_ticket_type_html_form_cart( $cart );
					$ticket_type = ob_get_clean();

					$ticket_ids = el_get_ticket_ids_form_cart( $cart );
				}

				$response['seat_map'] = $seat_map;
				$response['ticket_type'] = $ticket_type;

				// Quantity HTML
				if ( $qty && $qty > 1 ) {
					ob_start();
					?>
						<ul class="el_multiple_ticket">
					<?php
						for ( $i = 0; $i < $qty; $i++ ): 
							$active = '';

							if ( $i === 0 ) {
								$active = ' actived';
							}

							$ticket_id = isset( $ticket_ids[$i] ) && $ticket_ids[$i] ? $ticket_ids[$i] : '';
						?>
							<li 
								class="ticket_item ticket_item_<?php echo esc_attr( $i ); ?><?php echo esc_attr( $active ); ?>" 
								data-index="<?php echo esc_attr( $i ); ?>" 
								data-ticket-id="<?php echo esc_attr( $ticket_id ); ?>">
								<?php echo sprintf( esc_html__( 'Ticket #%s', 'eventlist' ), esc_html( $i + 1 ) ); ?>
							</li>
					<?php endfor; ?>
						</ul>
					<?php
					$response['nav'] = ob_get_clean();

					ob_start();
					el_get_template( 'cart/customer_insert.php', $post_data );
					$response['result'] = ob_get_clean();
				}
			}
			echo json_encode($response);
			wp_die();
		}

		public function el_geocode(){
			$ids 			= isset( $_POST['data']['ids'] ) ? $_POST['data']['ids'] : 0;
			$cate_id 		= isset( $_POST['data']['cate_id'] ) ? sanitize_text_field( $_POST['data']['cate_id'] ) : 0;
			$status 		= isset( $_POST['data']['status'] ) ? sanitize_text_field( $_POST['data']['status'] ) : '';
			$posts_per_page = sanitize_text_field( $_POST['data']['query']['posts_per_page'] );
			$order 			= sanitize_text_field( $_POST['data']['query']['order'] );
			$orderby 		= sanitize_text_field( $_POST['data']['query']['orderby'] );
			$type_event 	= sanitize_text_field( $_POST['data']['type'] );
			$type_event 	= !empty( $type_event ) ? $type_event : 'type1';
			$events 		= get_list_event_near_by_id( $order, $orderby, $posts_per_page, $ids, $cate_id, $status );

			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
			<?php
			if( $events && $events->have_posts() ) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ) );
				endwhile; wp_reset_postdata();
			else :
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;
			wp_die();
		}

		public function el_event_default(){
			$posts_per_page = sanitize_text_field( $_POST['data']['query']['posts_per_page'] );
			$order 			= sanitize_text_field( $_POST['data']['query']['order'] );
			$orderby 		= sanitize_text_field( $_POST['data']['query']['orderby'] );
			$filter_event 	= sanitize_text_field( $_POST['data']['query']['filter_event'] );
			$type_event 	= sanitize_text_field( $_POST['data']['type'] );
			$type_event 	= ! empty( $type_event ) ? $type_event : 'type1';
			$events 		= get_list_event_near_elementor( $order, $orderby, $posts_per_page, $filter_event );
			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
			<?php
			if( $events && $events->have_posts() ) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ) );
				endwhile; wp_reset_postdata();
			else :
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;
			wp_die();
		}

		public function el_event_online(){
			$posts_per_page = sanitize_text_field( $_POST['data']['query']['posts_per_page'] );
			$order 			= sanitize_text_field( $_POST['data']['query']['order'] );
			$orderby 		= sanitize_text_field( $_POST['data']['query']['orderby'] );
			$filter_event 	= sanitize_text_field( $_POST['data']['query']['filter_event'] );
			$type_event 	= sanitize_text_field( $_POST['data']['type'] );
			$type_event 	= !empty($type_event) ? $type_event : 'type1';
			$events 		= get_list_event_near_elementor($order, $orderby, $posts_per_page, $filter_event, 'online');
			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
			<?php
			if( $events && $events->have_posts() ) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ) );
				endwhile; wp_reset_postdata();
			else :
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;
			wp_die();
		}

		public function el_event_by_time(){
			$ids 			= isset( $_POST['data']['ids'] ) ? $_POST['data']['ids'] : 0;
			$status 		= isset( $_POST['data']['status'] ) ? sanitize_text_field( $_POST['data']['status'] ) : '';
			$posts_per_page = sanitize_text_field( $_POST['data']['query']['posts_per_page'] );
			$order 			= sanitize_text_field( $_POST['data']['query']['order'] );
			$orderby 		= sanitize_text_field( $_POST['data']['query']['orderby'] );
			$type_event 	= sanitize_text_field( $_POST['data']['type'] );
			$event_time 	= sanitize_text_field( $_POST['data']['time'] );
			$type_event 	= !empty($type_event) ? $type_event  : 'type1';
			$events = get_list_event_location_by_time_filter($order, $orderby, $posts_per_page, $event_time ,$ids, $status);
			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
			<?php
			if( $events && $events->have_posts() ) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ) );
				endwhile; wp_reset_postdata();
			else :
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;
			wp_die();
		}

		public function el_event_recent(){
			$id 			= isset( $_POST['data']['id'] ) ? $_POST['data']['id'] : 0;
			$ova_event_id 	= 0;
			$posts_per_page = sanitize_text_field( $_POST['data']['query']['posts_per_page'] );
			$order 			= sanitize_text_field( $_POST['data']['query']['order'] );
			$orderby 		= sanitize_text_field( $_POST['data']['query']['orderby'] );
			$type_event 	= sanitize_text_field( $_POST['data']['type'] );
			$type_event 	= !empty($type_event) ? $type_event : 'type1';

			if ( ! isset( $_COOKIE['ova_event_id'] ) ) {
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php
				wp_die();
			}
			if ( isset( $_COOKIE['ova_event_id'][$id] ) ) {
				unset( $_COOKIE['ova_event_id'][$id] );
				setcookie( 'ova_event_id['.$id.']', '', -1, '/'); 
				$ova_event_id = array_values( $_COOKIE["ova_event_id"] );
			}

			$events = get_list_event_recent_elementor( $order, $orderby, $posts_per_page , $ova_event_id );
			add_filter( 'el_ft_show_remove_btn', '__return_true' );
			?>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
			<?php
			if( $events && $events->have_posts() ) : 
				while($events->have_posts()) : $events->the_post();
					el_get_template_part( 'content', 'event-'.sanitize_file_name( $type_event ) );
				endwhile; wp_reset_postdata();
			else :
				?>
				<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
				<?php 
			endif;
			wp_die();
		}

		public function el_upload_files() {
			if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['security'] ), 'el_checkout_event_nonce' ) ) return;

			$files = [];

			if ( $_FILES && is_array( $_FILES ) ) {
				$overrides = [
                    'test_form' => false,
                    'mimes'     => apply_filters( 'ovabrw_ft_file_mimes', [
                        'jpg'   => 'image/jpeg',
                        'jpeg'  => 'image/pjpeg',
                        'png'   => 'image/png',
                        'pdf'   => 'application/pdf',
                        'doc'   => 'application/msword',
                    ]),
                ];

                require_once( ABSPATH . 'wp-admin/includes/admin.php' );

				foreach ( $_FILES as $k => $file ) {
                    $upload = wp_handle_upload( $file, $overrides );

                    if ( isset( $upload['error'] ) ) { continue; }

                    $files[$k] = array(
                        'name' => basename( $upload['file'] ),
                        'url'  => $upload['url'],
                        'mime' => $upload['type'],
                    );
				}
			}

			echo json_encode( array( 'files' => $files ));

			wp_die();
		}

		public function el_verify_google_recapcha(){

			$post_data 		= $_POST['data'];
			$secret_key 	= isset( $post_data['secret'] ) ? sanitize_text_field( $post_data['secret'] ) : '';
			$recapcha 		= isset( $post_data['response'] ) ? sanitize_text_field( $post_data['response'] ) : '';
			$check_recapcha = ova_event_verify_recapcha( $secret_key, $recapcha );

			echo esc_html($check_recapcha);
			wp_die();
		}

		public function el_ticket_received_download(){

			$post_data = $_POST;
			$data = [];
			$data['status'] = 'error';
			$nonce = isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$ticket_id = isset( $post_data['id'] ) ? sanitize_text_field( $post_data['id'] ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'el_ticket_received_download_nonce' ) ) {
				$data['mess'] = __( 'Invalid nonce, please refresh your screen and try again.', 'eventlist' );
			
				wp_send_json( $data );
				wp_die();
			} elseif ( ! $ticket_id ) {
				$data['mess'] = __( 'Invalid ticket.', 'eventlist' );
				$data = json_encode( $data );
				wp_send_json( $data );
				wp_die();
			}

			$arr_upload = wp_upload_dir();
			$base_url_upload = $arr_upload['baseurl'];

			$ticket_pdf = EL_Ticket::instance()->make_pdf_ticket_by_id( $ticket_id );
			$ticket_url = '';

			if ( ! empty( $ticket_pdf ) ) {
				$position = strrpos($ticket_pdf, '/');
				$name = substr($ticket_pdf, $position);
				$ticket_url = $base_url_upload . $name;
			}
			
			$data['status'] = 'success';
			$data['ticket_url'] = $ticket_url;

			wp_send_json( $data );
			wp_die();
		}

		public function el_fe_unlink_download_ticket() {
			
			$ticket_pdf = $_POST['data_url'];
			$arr_upload = wp_upload_dir();
			$basedir = $arr_upload['basedir'];

			$ticket_url = '';
			if ( ! empty( $ticket_pdf ) ) {
				$position = strrpos($ticket_pdf, '/');
				$name = substr($ticket_pdf, $position);
				$ticket_url = $basedir . $name;
			}

			if (file_exists($ticket_url)) wp_delete_file($ticket_url);
			wp_die();
		}

		public function el_ticket_list(){

			$post_data = $_POST;

			if ( ! isset( $post_data['nonce'] ) || ! isset( $post_data['booking_id'] ) ) {
				wp_die();
			}
			if ( ! wp_verify_nonce( $post_data['nonce'], 'el_ticket_list_nonce' ) ) {
				wp_die();
			}

			$current_user_id 		= get_current_user_id();
			$allow_transfer_ticket 	= EL()->options->ticket_transfer->get('allow_transfer_ticket','');
			$booking_id 			= isset( $post_data['booking_id'] ) ? sanitize_text_field( $post_data['booking_id'] ) : '';

			if ( $current_user_id != get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_customer', true ) ) {
				wp_die();
			}

			$list_tickets = EL_Ticket::instance()->get_list_ticket_by_id_booking( $booking_id );

	
			if ( $list_tickets ) :
				foreach ( $list_tickets as $ticket_id ) :
					$ticket_transfer 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'transfer_status', true );
					$ticket_status 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'ticket_status', true );
					$customer 			= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'name_customer', true );

					$arr_venue 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'venue', true );
					$address 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'address', true );

					$venue = is_array( $arr_venue ) ? implode(", ", $arr_venue) : $arr_venue;
					$venue_address = '';
					if( !empty( $venue ) ){
						$venue_address .= sprintf( esc_html__( 'Venue: %s', 'eventlist' ), $venue );
					}
					if( $address ){
						if ( $venue_address ) {
							$venue_address .= ';';
						}
						$venue_address .= sprintf( esc_html__( 'Address: %s', 'eventlist' ), $address );
					}

					$ticket_seat 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'seat', true );
					$ticket_qr 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT.'qr_code', true );
					$start_date 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'date_start', true );
					$end_date 		= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'date_end', true );
					$date_format 	= get_option('date_format');
					$time_format 	= get_option('time_format');

					$start_date_time = date_i18n($date_format, $start_date) . ' - ' . date_i18n($time_format, $start_date);
					$end_date_time = date_i18n($date_format, $end_date) . ' - ' . date_i18n($time_format, $end_date);

					$person_type = get_post_meta( $ticket_id, OVA_METABOX_EVENT.'person_type', true );
					if ( $person_type ) {
						$ticket_seat .= ' - '.$person_type;
					}

					?>
					<tr>
						<?php if ( $allow_transfer_ticket ): ?>
							<th scope="row">
								<?php if ( $ticket_status !== 'checked' && $ticket_transfer !== 'yes' ): ?>
									<div class="form-check">
										<input class="form-check-input position-static ticket_check" type="checkbox" value="<?php echo esc_attr( $ticket_id ); ?>" id=ticket_check_id<?php echo esc_attr( $ticket_id ); ?> aria-label="<?php esc_attr_e( 'Check ticket', 'eventlist' ); ?>">
									</div>
								<?php endif; ?>
							</th>
						<?php endif; ?>
						<td><?php echo esc_html( $ticket_id ); ?></td>
						<td><?php echo esc_html( get_the_title( $ticket_id ) ); ?></td>
						<td><?php echo esc_html( $customer ); ?></td>
						<td><?php echo esc_html( $ticket_status ); ?></td>
						<td><?php echo esc_html( $ticket_seat ); ?></td>
						<td><?php echo esc_html( $venue_address ); ?></td>
						<td>
							<div class="ticket_qr_wrap">
								<button class="ticket_qr_toggle"><i class="fas fa-eye"></i></button>
								<span class="ticket_qr"><?php echo esc_html( $ticket_qr ); ?></span>
							</div>
						</td>
						<td><?php echo esc_html( $start_date_time ); ?></td>
						<td><?php echo esc_html( $end_date_time ); ?></td>
						<?php
						$site_url = get_bloginfo( 'url' );
						$url = add_query_arg( 'post_type', 'event', $site_url );
						$url = add_query_arg( 'id_ticket', $ticket_id, $url );
						$url = add_query_arg( 'qr_code', $ticket_qr, $url );
						$url = add_query_arg( 'customer_check_qrcode', 'true', $url );
						$url = add_query_arg( '_nonce', wp_create_nonce( 'el_check_qrcode' ), $url );
						?>
						<td><a href="<?php echo esc_url( $url ); ?>" target="_blank" class="btn btn-link"><?php esc_html_e( 'Check', 'eventlist' ); ?></a></td>
					</tr>
					<?php
				endforeach;
			else :
				$colspan = $allow_transfer_ticket ? '11' : '10';
				?>
				<tr>
					<td colspan="<?php echo esc_attr( $colspan ); ?>"><?php esc_html_e( 'Ticket not found', 'eventlist' ); ?></td>
				</tr>
				<?php
			endif;
			wp_reset_postdata();
			wp_die();
		}

		public function el_ticket_transfer(){
			$current_user_id = get_current_user_id();

			$post_data = $_POST;
			$response = [];
			$response['status'] = 'error';
			$response['class'] = 'danger';
			$response['mail'] = 'false';
			$response['mail_mess'] = '';

			if ( ! $post_data['nonce'] || ! wp_verify_nonce( $post_data['nonce'], 'el_ticket_transfer_nonce' ) ) {
				$response['mess'] = esc_html__( 'Invalid nonce, please refresh your screen and try again.', 'eventlist' );
				
				wp_send_json( $response );
				wp_die();
			}
			
			$ticket_ids = $post_data['ticket_ids'] ? json_decode( stripslashes( $post_data['ticket_ids'] ) ) : '';
			$email 		= $post_data['email'] ? sanitize_text_field( $post_data['email'] ) : '';
			$name 		= $post_data['name'] ? sanitize_text_field( $post_data['name'] ) : '';
			$phone 		= $post_data['phone'] ? sanitize_text_field( $post_data['phone'] ) : '';
			$booking_id = $post_data['booking_id'] ? sanitize_text_field( $post_data['booking_id'] ) : '';

			if ( $current_user_id != get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_customer', true ) ) {
				$response['mess'] = esc_html__( 'You do not have permission to use this function.', 'eventlist' );
				
				wp_send_json( $response );
				wp_die();
			}

			if ( $ticket_ids && $email && $name && $phone ) {
				$current_user 		= wp_get_current_user();
				$ticket_recipient 	= get_user_by( 'email', $email );
				$user_name 			= get_user_by( 'login', $email );

				$allow_create_user 		= EL_Setting::instance()->ticket_transfer->get('ticket_transfer_create_user', '');
				$change_customer_name 	= EL_Setting::instance()->ticket_transfer->get('ticket_transfer_change_customer_name', '');
				$add_transfer_text 		= EL_Setting::instance()->ticket_transfer->get('ticket_transfer_add_transfer', '');
				$transfer_text 			= esc_html__( '(transfer)', 'eventlist' );

				if ( $current_user->user_email === $email ) {
					$response['mess'] = esc_html__( 'Email address is not valid.', 'eventlist' );
			

					wp_send_json( $response );
					wp_die();
				}

				// handle create new user
				if ( $allow_create_user ) {

					if ( ! $user_name && ! $ticket_recipient ) {

						$random_password = wp_generate_password();
						$user_id = wp_create_user( $email, $random_password, $email );
						update_user_meta( $user_id, 'first_name', $name );
						update_user_meta( $user_id, 'user_phone', $phone );
						
						$send_mail = el_mail_reset_password( $user_id );

						if ( ! $send_mail ) {
							$response['mess'] = esc_html__( 'An error occurred while sending email.', 'eventlist' );
						
							wp_send_json( $response );
							wp_die();
						}

						$response['mail'] = 'true';
						$response['mail_mess'] = sprintf( esc_html__( 'The password reset link has been sent to email %s', 'eventlist' ), $ticket_recipient->user_email );

					}

				} else {

					if ( ! $user_name && ! $ticket_recipient ) {
						$response['mess'] = esc_html__( 'Email is not exist. Please check surely you created an account with this email address.', 'eventlist' );
						wp_send_json( $response );
					
						wp_die();
					}
				}

				// update transfer status
				foreach ( $ticket_ids as $ticket_id ) {
					$customer_name = get_post_meta( $ticket_id, OVA_METABOX_EVENT.'name_customer', true );
					$new_name = $customer_name;

					update_post_meta( $ticket_id, OVA_METABOX_EVENT.'transfer_status', 'yes' );
					update_post_meta( $ticket_id, OVA_METABOX_EVENT.'transfer_email', $email );

					if ( $change_customer_name ) {
						$new_name = $name;
					}
					if ( $add_transfer_text ) {
						$new_name = $new_name .' '.$transfer_text;
					}
					if ( $new_name != $customer_name ) {
						update_post_meta( $ticket_id, OVA_METABOX_EVENT.'name_customer', $new_name );
					}
				}

				$response['status'] = 'success';
				$response['mess'] = esc_html__( 'Ticket transfer successful.', 'eventlist' );
				$response['class'] = 'success';

				wp_send_json( $response );
				wp_die();

			} else {

				$response['mess'] = esc_html__( 'Please complete all information.', 'eventlist' );
				
				wp_send_json( $response );
				wp_die();

			}
			wp_die();
		}

		public function el_payment_countdown(){
			
			$checkout_holding_ticket = EL()->options->checkout->get('checkout_holding_ticket', 'no');

			if ( $checkout_holding_ticket === 'yes' ) {

				$time_countdown_checkout = intval( EL()->options->checkout->get('max_time_complete_checkout', 600) );
				$booking_id = $event_id = $id_cal = '';
				$redirect = home_url();

				$booking_id = isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';
				$event_id = isset( $_POST['ide'] ) ? sanitize_text_field( $_POST['ide'] ) : '';
				$id_cal = isset( $_POST['idcal'] ) ? sanitize_text_field( $_POST['idcal'] ) : '';


				if ( $booking_id ) {
					$event_id = get_post_meta( $booking_id, 'ova_mb_event_id_event', true );

					if ( $event_id ) {
						$redirect = get_permalink( $event_id );
					}
				}

				if ( $time_countdown_checkout && $booking_id ) {
					$time_sumbit_checkout = get_post_meta( $booking_id, OVA_METABOX_EVENT.'time_countdown_checkout', true );
					$current_time = current_time( 'timestamp' );
					$past_time = absint( $current_time ) - absint( $time_sumbit_checkout );
					$time_countdown_checkout -= $past_time;

					if ( $time_countdown_checkout < 0 ) {
						$time_countdown_checkout = 0;
					}

					if ( $time_countdown_checkout == 0 ) {
						wp_redirect( $redirect );
						exit;
					}

					$minutes = absint( $time_countdown_checkout / 60 );
					$seconds = absint( $time_countdown_checkout % 60 );
					if ( $minutes < 10 ) {
						$minutes = '0'.$minutes;
					}
					if ( $seconds < 10 ) {
						$seconds = '0'.$seconds;
					}
			
					?>
					<div 
					class="countdown-checkout" 
					data-time-countdown-checkout="<?php echo esc_attr( $time_countdown_checkout ); ?>"
					data-redirect="<?php echo esc_url( $redirect ); ?>" 
					data-booking-id="<?php echo esc_attr( $booking_id ); ?>" 
					data-event-id="<?php echo esc_attr( $event_id ); ?>" 
					data-id-cal="<?php echo esc_attr( $id_cal ); ?>" 
					data-countdown-checkout-nonce="<?php echo esc_attr( wp_create_nonce( 'el_countdown_checkout_nonce' ) ); ?>">
					<div class="countdown-time">
						<span class="text"><?php echo esc_html__( 'Your remaining time is ', 'eventlist' ); ?></span>
						<span class="time"><?php echo esc_html( $minutes.':'.$seconds ); ?></span>
						<span class="unit"><?php echo esc_html__( ' minutes to complete your payment', 'eventlist' ) ?></span>
					</div>
				</div>
				<?php
	
				wp_die();
			}
		}

		wp_die();
	}

	/**
	 * Supprimer une image de la galerie du partenaire
	 * V1 Le Hiboo - Supprime définitivement l'image de WordPress
	 */
	public function el_delete_gallery_image() {
		check_ajax_referer( 'ajax_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Vous devez être connecté', 'eventlist' ) ) );
		}

		$user_id = get_current_user_id();
		$image_id = isset( $_POST['image_id'] ) ? absint( $_POST['image_id'] ) : 0;

		if ( ! $image_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Image non trouvée', 'eventlist' ) ) );
		}

		// Vérifier que l'utilisateur est propriétaire de l'image
		$attachment = get_post( $image_id );
		if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Image non trouvée', 'eventlist' ) ) );
		}

		if ( (int) $attachment->post_author !== $user_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission refusée', 'eventlist' ) ) );
		}

		// Supprimer définitivement l'image de WordPress
		$result = wp_delete_attachment( $image_id, true );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => esc_html__( 'Image supprimée définitivement de WordPress', 'eventlist' )
			) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'Erreur lors de la suppression', 'eventlist' ) ) );
		}
	}
}

	new El_Ajax();
}

?>