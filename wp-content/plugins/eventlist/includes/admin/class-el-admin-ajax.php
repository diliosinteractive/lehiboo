<?php 

defined( 'ABSPATH' ) || exit();

if( !class_exists( 'El_Admin_Ajax' ) ){

	class El_Admin_Ajax{

		private $zip;
		
		public function __construct(){
			$this->init();
		}

		public function init(){

			// Define All Ajax function
			$arr_ajax =  array(
				'mb_add_social',
				'mb_add_ticket',
				'add_seat_map',
				'add_area_map',
				'add_desc_seat_map',
				'mb_add_calendar',
				'mb_add_disable_date',
				'mb_add_disable_time_slot',
				'mb_add_schedules_time',
				'mb_add_coupon',
				'mb_add_services',
				'el_load_venue',
				'el_load_checklist_venue',
				'create_ticket_send_mail',
				'create_invoice',
				'send_invoice',
				'download_ticket',
				'unlink_download_ticket',
				'update_status_proccess',
				'add_custom_booking',
				'el_get_idcal_seatopt',
				'el_check_book_before_minutes',
				'el_check_schedules_time_book',
				'el_check_calendar_recurrence_time_book',
				'el_replace_get_tickets',
				'el_replace_ticket_date',
				'el_replace_ticket_date_posts_per_page',
				'el_replace_ticket_date_pagination',
				'el_replace_ticket_date_export_email',
				'el_replace_ticket_date_send_email',
				'el_update_event_status',
				'el_ticket_table_send_ticket',
				'el_ticket_table_download_ticket',
				'el_ticket_table_remove_ticket_pdf',
				'el_sync_data_package',
				'el_add_seat_code_row',
			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function el_add_seat_code_row(){

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_add_seat_code_row' ) ) {
				$ticket = sanitize_text_field( $_POST['ticket'] ) ?? 'ova_mb_event_ticket';
				$key 	= sanitize_text_field( $_POST['key'] ) ?? 0;
				$_k 	= sanitize_text_field( $_POST['items'] ) ?? 0;

				include EL_PLUGIN_INC."admin/views/metaboxes/html-seat-code-setup-item.php";
			}
			wp_die();
		}

		public function el_sync_data_package(){
			$response = array(
				'status' 	=> 'error',
				'mess' 		=> esc_html__( 'Data sync failed', 'eventlist' )
			);
			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_setting_action' ) &&
				current_user_can( 'manage_options' ) ) {
				
				$vendor_ids = EL_User::get_vendor_ids();
				if ( count( $vendor_ids ) > 0 ) {
					foreach ( $vendor_ids as $vendor_id ) {
						$membership_id 		= EL_Package::get_id_membership_by_user_id( $vendor_id );
						$membership_start 	= get_post_meta( $membership_id, OVA_METABOX_EVENT."membership_start_date", true );
						$event_ids_after_date = array();
						// Update membership_id field for current membership
						if ( $membership_start ) {
							$event_ids_after_date = EL_Event::get_event_ids_after_date_by_author_id( $vendor_id, $membership_start );

							if ( count( $event_ids_after_date ) > 0 ) {
								foreach ( $event_ids_after_date as $event_id ) {
									update_post_meta( $event_id,OVA_METABOX_EVENT."membership_id", $membership_id );
								}
							}
						}
						// Update membership_id field for another membership
						$event_ids = EL_Event::get_event_ids_by_author_id( $vendor_id, $event_ids_after_date );

						if ( count( $event_ids ) > 0 ) {
							foreach ( $event_ids as $event_id ) {
								update_post_meta( $event_id, OVA_METABOX_EVENT."membership_id", "" );
							}
						}
					}
				}

				// Get Membership IDs to update event limit field
				$membership_ids = EL_Package::get_membership_ids();
				if ( count( $membership_ids ) > 0 ) {
					foreach ( $membership_ids as $mbs_id ) {
						$package_slug 	= get_post_meta( $mbs_id, OVA_METABOX_EVENT."membership_package_id", true );
						$package_id 	= EL_Package::get_id_package_by_id_meta( $package_slug );
						$total_event 	= get_post_meta( $package_id, OVA_METABOX_EVENT."package_total_event", true );

						update_post_meta( $mbs_id, OVA_METABOX_EVENT."event_limit", $total_event );
					}
				}

				$response['status'] = 'success';
				$response['mess'] 	= esc_html__( 'Data sync successfully', 'eventlist' );

			}

			echo json_encode( $response );
			wp_die();
		}

		public function el_ticket_table_send_ticket(){
			$response = array(
				'status' 	=> 'error',
				'mess' 		=> esc_html__( 'Sending ticket email failed', 'eventlist' )
			);

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_nonce' ) &&
				current_user_can( 'manage_options' ) ) {
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

		public function el_ticket_table_download_ticket(){

			$response = array(
				'status' 	=> 'error',
				'mess' 		=> '',
				'file_url'	=> '',
				'file_name'	=> '',
			);

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_nonce' ) &&
				current_user_can( 'manage_options' ) ) {
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

		public function el_ticket_table_remove_ticket_pdf(){

			if ( isset( $_POST['nonce'] ) &&
				wp_verify_nonce( $_POST['nonce'], 'el_ticket_nonce' ) &&
				current_user_can( 'manage_options' ) ) {
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

		public function mb_add_services(){
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mb_add_services' ) ) {
				echo '';
				wp_die();
			}
			$k = isset( $_POST['count_item'] ) ? sanitize_text_field( $_POST['count_item'] ) : 0;
			?>
			<li class="el_service_item">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Service Name', 'eventlist' ); ?></label></th>
							<td>
								<input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][name]' ); ?>" type="text" value="" placeholder="<?php esc_attr_e( 'Enter Name', 'eventlist' ); ?>" required class="extra_service_name regular-text">
								<input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][id]' ); ?>" type="hidden" class="extra_service_id" value="">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Price', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][price]' ); ?>" type="number" min="0" step="0.1" value="" placeholder="<?php echo esc_attr_e( '10', 'eventlist' ); ?>" class="extra_service_price regular-number"></td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Max Quantity/Calendar', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][qty]' ); ?>" type="number" min="0" step="1" value="" placeholder="<?php esc_attr_e( '100', 'eventlist' ); ?>" required class="extra_service_qty regular-number"></td>
						</tr>
						<tr>
							<th scope="row"><label for=""><?php esc_html_e( 'Max Quantity/Ticket', 'eventlist' ); ?></label></th>
							<td><input name="<?php echo esc_attr( OVA_METABOX_EVENT.'extra_service['.$k.'][max_qty]' ); ?>" type="number" min="0" step="1" value="" placeholder="<?php esc_attr_e( '10', 'eventlist' ); ?>" class="extra_service_max_qty regular-number"></td>
						</tr>
					</tbody>
				</table>
				<button type="button" class="el_remove_service button button-small button-secondary">&#x2715;</button>
			</li>
			<?php
			wp_die();
		}

		public function update_status_proccess() {
			$id_event = isset($_POST['id_event']) ? sanitize_text_field($_POST['id_event']) : '';
			$status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

			if (empty($id_event) || empty($status) ) wp_die();

			if ($status == 'paid') {
				$status = 'paid';
			} else {
				$status = 'pending';
			}

			$time_current = current_time('timestamp');
			if (current_user_can('administrator')) {
				update_post_meta( $id_event, OVA_METABOX_EVENT . 'status_pay', $status );
				update_post_meta( $id_event, OVA_METABOX_EVENT . 'date_update', $time_current );
			}

			echo "success";
			
			wp_die();
		}

		public function create_invoice(){
			$booking_id 	= isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';
			$invoices_nonce = isset( $_POST['invoice_nonce'] ) ? sanitize_text_field( $_POST['invoice_nonce'] ) : '';

			if ( ! $booking_id || ! $invoices_nonce || ! wp_verify_nonce( $invoices_nonce , 'el_create_invoice_nonce' ) ) wp_die();

			$data = [
				'pdf_url' 	=> '',
				'error' 	=> '',
			];

			$pdf_url = EL_Booking::instance()->el_make_pdf_invoice_by_booking_id( $booking_id );
			$invoices_dir = trailingslashit( wp_upload_dir()['baseurl'] ) . 'invoices';

			if ( $pdf_url ) {
				$position 	= strrpos( $pdf_url, '/');
				$name 		= substr( $pdf_url, $position);
				$pdf_url 	= $invoices_dir . $name;

				$data['pdf_url'] = $pdf_url;
			} else {
				$data['error'] = esc_html__( 'Make PDF invoice failed.', 'eventlist' );
			}

			echo json_encode( $data );

			wp_die();
		}

		public function send_invoice() {
			$booking_id 	= isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '';
			$invoices_nonce = isset( $_POST['invoice_nonce'] ) ? sanitize_text_field( $_POST['invoice_nonce'] ) : '';

			if ( ! $booking_id || ! $invoices_nonce || ! wp_verify_nonce( $invoices_nonce , 'el_send_invoice_nonce' ) ) wp_die();

			$data = [
				'message' 	=> '',
				'error' 	=> '',
			];

			$pdf_url = EL_Booking::instance()->el_make_pdf_invoice_by_booking_id( $booking_id );

			if ( $pdf_url ) {
				$result = el_sendmail_pdf_invoice( $booking_id, $pdf_url );

				if ( $result ) {
					$data['message'] = esc_html__( 'Send PDF invoice success.','eventlist' );
				} else {
					$data['error'] = esc_html__( 'Send PDF invoice failed.','eventlist' );
				}
			} else {
				$data['error'] = esc_html__( 'Create PDF invoice failed.','eventlist' );
			}

			echo json_encode( $data );

			wp_die();
		}

		public function download_ticket() {

			$ticket_download_zip = EL()->options->general->get('ticket_download_zip','no');

			$id_booking = isset($_POST['id_booking']) ? sanitize_text_field($_POST['id_booking']) : "";

			$data = [];

			$status = get_post_meta($id_booking, OVA_METABOX_EVENT . "status", true);
			if ($status !== "Completed") {
				$data['status'] = 'error';
				$data['message'] = __("Please update booking status to Complete to send mail", "eventlist"); 
				echo json_encode($data);
				wp_die();
			}

			$arr_upload = wp_upload_dir();
			$base_url_upload = $arr_upload['baseurl'];
			$base_dir_upload = $arr_upload['basedir'];

			if( empty($id_booking) || !isset( $_POST['el_download_ticket_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['el_download_ticket_nonce']), 'el_download_ticket_nonce' ) ) wp_die() ;

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

			echo json_encode($data);

			wp_die();
		}


		public function unlink_download_ticket() {
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'el_nonce' ) ) {
				$data = $_POST['data_url'];
				$arr_upload = wp_upload_dir();
				$basedir = $arr_upload['basedir'];

				$list_uri_ticket = [];
				if (is_array($data) && !empty($data)) {
					foreach($data as $ticket_pdf) {
						$position = strrpos($ticket_pdf, '/');
						$name = substr($ticket_pdf, $position);
						$list_uri_ticket[] = $basedir . $name;
					}
				}

				if (empty($list_uri_ticket) || !is_array($list_uri_ticket)) wp_die();
				$total_ticket_pdf = count($list_uri_ticket);
				if (!empty($list_uri_ticket) && is_array($list_uri_ticket)) {
					foreach ($list_uri_ticket as $key => $value) {
						if( $key < $total_ticket_pdf ){
							if (file_exists($value)) wp_delete_file($value);
						} 
					}
				}
			}
			wp_die();
		}


		public function create_ticket_send_mail() {
			$id_booking = isset($_POST['id_booking']) ? sanitize_text_field($_POST['id_booking']) : "";

			if ( empty( $id_booking ) || !isset( $_POST['el_create_send_ticket_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['el_create_send_ticket_nonce']), 'el_create_send_ticket_nonce' ) ) wp_die() ;

			$data 	= [];
			$status = get_post_meta( $id_booking, OVA_METABOX_EVENT . "status", true );

			if ( $status !== "Completed" ) {
				$data['status'] 	= 'error';
				$data['message'] 	= esc_html__("Please update booking status to Complete to send mail", "eventlist"); 

				echo json_encode($data);
				wp_die();
			}

			$list_id_ticket_by_booking 	= get_post_meta($id_booking, OVA_METABOX_EVENT . "list_id_ticket", true);
			$list_id_ticket_by_booking 	= json_decode($list_id_ticket_by_booking);
			$number_ticket_in_booking 	= count($list_id_ticket_by_booking);

			$args = [
				'post_type' 	=> 'el_tickets',
				'post_status' 	=> 'publish',
				'meta_query' 	=> array(
					'relation' => 'AND',
					array(
						'key' 	=> OVA_METABOX_EVENT . 'booking_id',
						'value' => $id_booking,
						'compare' => '=',
					),
				),
				'posts_per_page' 	=> -1, 
				'numberposts' 		=> -1,
				'nopaging' 			=> true,
			];

			$ticket_record 			= get_posts($args);
			$number_ticket_record 	= count($ticket_record);
			$list_id_ticket 		= [];

			if ( $number_ticket_record == 0 && $number_ticket_in_booking > 0 ) {
				$data['message'] 	= esc_html__("Add Ticket and send mail success", "eventlist");
				$list_id_ticket 	= EL_Ticket::instance()->add_ticket($id_booking);
			} else {
				$data['message'] = esc_html__("Send mail success", "eventlist");
			}

			// Update profit & commission
			$profit 	= EL_Booking::instance()->get_profit_by_id_booking( $id_booking );
			$commission = EL_Booking::instance()->get_commission_by_id_booking( $id_booking );
			
			update_post_meta( $id_booking, OVA_METABOX_EVENT.'profit', $profit );
			update_post_meta( $id_booking, OVA_METABOX_EVENT.'commission', $commission );
			update_post_meta( $id_booking, OVA_METABOX_EVENT.'profit_status', '' );

			if ( $list_id_ticket ) {
				update_post_meta( $id_booking, OVA_METABOX_EVENT.'record_ticket_ids', $list_id_ticket );
			}

			$result = el_sendmail_by_booking_id($id_booking, $order_status = "", $receiver = 'customer' );

			if ( $result ) {
				$data['status'] = "success";
			} else {
				$data['status'] 	= "error";
				$data['message'] 	= esc_html__("Send mail failed", "eventlist");
			}
			
			echo json_encode($data);
			wp_die();
		}


		/* Load Venue */
		public static function el_load_venue() {
			$keyword = isset($_POST['keyword']) ? sanitize_text_field( $_POST['keyword'] ) : '';

			$the_query = new WP_Query( array( 'post_type' => 'venue' , 's' => $keyword, 'posts_per_page'=> '10') );
			?>

			<?php
			$title = array();
			if( $the_query->have_posts() ) :
				while( $the_query->have_posts() ): $the_query->the_post();

					$title[] = get_the_title();

				endwhile;
				wp_reset_postdata();  
			endif;

			echo json_encode($title);

			wp_die();
		}


		/* Load checklist Venue */
		public static function el_load_checklist_venue() {
			$add_venue = isset( $_POST['add_venue'] ) ? sanitize_text_field( $_POST['add_venue'] ) : '';
			$list_venue = isset( $_POST['list_venue'] ) ? sanitize_text_field( $_POST['list_venue'] ) : '';

			$list_venue = substr($list_venue, 0, -1);

			$add_venue = array_map('ucwords', explode(', ', $add_venue));
			$list_venue = array_map('ucwords', explode(', ', $list_venue));

			foreach($add_venue as $key => $value) {
				$add_venue[$key] = str_replace(',', '', $value);
			}

			$result = array_unique(array_merge($add_venue, $list_venue));

			$_prefix = OVA_METABOX_EVENT;
			
			// remove empty array elements
			foreach($result as $key => $value) {
				if(empty($value)) {
					unset($result[$key]);
				}
			}

			foreach ($result as $key => $value) {
				
				?>
				<li>
					<input type="hidden" name="<?php echo esc_attr($_prefix.'venue['.$key.']'); ?>" value="<?php echo esc_attr($value); ?>">
					<i class="dashicons dashicons-dismiss remove_venue"></i>
					<span><?php echo esc_html($value); ?></span>
				</li>
				<?php
			}

			wp_die();
		}


		/* Add Social */
		public static function mb_add_social(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = $_POST['data'];
			$index = isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';
			$_prefix = OVA_METABOX_EVENT;
			?>
			<div class="social_item">
				<input type="text" name="<?php echo esc_attr($_prefix.'social_organizer['.$index.'][link_social]'); ?>" class="link_social" value="" placeholder="<?php echo esc_attr( 'https://' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
				<select name="<?php echo esc_attr($_prefix.'social_organizer['.$index.'][icon_social]'); ?>" class="icon_social">
					<?php foreach (el_get_social() as $key => $value) { ?>
						<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html( $value ); ?></option>
					<?php } ?>
				</select>
				<a href="#" class="button remove_social"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
			</div>

			<?php
			wp_die();
		}


		/* Add Ticket */
		public static function mb_add_ticket(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = $_POST['data'];

			$seat_option = isset($post_data['seat_option']) ? sanitize_text_field( $post_data['seat_option'] ) : 'none';

			$key = isset($post_data['count_tickets']) ? sanitize_text_field( $post_data['count_tickets'] ) : '1';

			$_prefix = OVA_METABOX_EVENT;
			$ticket = OVA_METABOX_EVENT.'ticket';

			

			$time = el_calendar_time_format();
			$format = el_date_time_format_js();
			$first_day = el_first_day_of_week();
			
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			$decimal_separator 	= EL()->options->general->get('decimal_separator','.');
			$number_decimals 	= EL()->options->general->get('number_decimals','2');
			$data_curency = array(
				'decimal_separator' => $decimal_separator,
				'number_decimals' => $number_decimals,
			);

			?>

			<div class="ticket_item" data-prefix="<?php echo esc_attr($_prefix); ?>">

				<!-- Headding Ticket -->
				<div class="heading_ticket">
					<div class="left">
						<i class=" fas fa-ticket-alt"></i>
						<input type="text" 
						name="<?php echo esc_attr( $ticket.'['.$key.'][name_ticket]' ); ?>" 
						class="name_ticket" 
						value="" 
						required
						placeholder="<?php esc_attr_e( 'Click to edit ticket name', 'eventlist' ); ?>" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						/>
					</div>
					<div class="right">
						<!-- <i class="dashicons dashicons-move move_ticket"></i> -->
						<i class="fas fa-edit edit_ticket"></i>
						<i class="fas fa-trash delete_ticket"></i>
					</div>
				</div>

				<!-- Content Ticket -->
				<div class="content_ticket">

					<!-- ID Ticket -->
					<div class="id_ticket">
						<label><strong><?php esc_html_e( 'SKU: *', 'eventlist' ); ?></strong></label>
						<input type="text" 
						class="ticket_id" 
						name="<?php echo esc_attr( $ticket.'['.$key.'][ticket_id]' ); ?>"
						value=""
						autocomplete="off" autocorrect="off" autocapitalize="none"
						/>
						<span><?php esc_html_e( 'Auto render if empty', 'eventlist' ); ?></span>
					</div>

					<!-- Top Ticket -->
					<div class="top_ticket">

						<div class="col_price_ticket col">
							<div class="top">
								<span><strong><?php esc_html_e( 'Price', 'eventlist' ); ?></strong></span>

								<div class="radio_type_price" data-type-price="<?php echo esc_attr( 'paid' ); ?>">
									<label for="type_price_paid<?php echo esc_attr( $key ); ?>" class="el_input_radio">
										<?php esc_html_e( 'Paid', 'eventlist' ); ?>
										<input type="radio"
											name="<?php echo esc_attr( $ticket.'['.$key.'][type_price]' ) ?>"
											class="type_price"
											id="type_price_paid<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr('paid'); ?>" <?php echo esc_attr('checked'); ?>
										/>
										<span class="checkmark el_bg_white"></span>
									</label>

									<label for="type_price_free<?php echo esc_attr( $key ); ?>" class="el_input_radio el_ml_10px">
										<?php esc_html_e( 'Free', 'eventlist' ); ?>
										<input type="radio"
											name="<?php echo esc_attr( $ticket.'['.$key.'][type_price]' ) ?>"
											class="type_price"
											id="type_price_free<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr('free'); ?>"
										/>
										<span class="checkmark el_bg_white"></span>
									</label>

								</div>
							</div>
							<div class="ova_wrap_price_ticket" data-curency="<?php echo esc_attr( json_encode( $data_curency ) ); ?>">
								<input type="text" 
								name="<?php echo esc_attr( $ticket.'['.$key.'][price_ticket]' ); ?>" 
								class="price_ticket" 
								value="<?php echo esc_attr(''); ?>" 
								placeholder ="<?php esc_attr_e( '0', 'eventlist' ); ?>" 
								autocomplete="off" autocorrect="off" autocapitalize="none" 
								/>
								<span class="ova_price_ticket_err">
									<?php printf( esc_html__( 'Please enter a value with one monetary decimal point ( %s ) without thousand separators and currency symbols.', 'eventlist' ), esc_html( $decimal_separator ) ); ?>
								</span>
							</div>

						</div>

						<?php $class_active = $seat_option == 'none' ? 'is-active' : ''; ?>
						<div class="col_total_number_ticket col <?php echo esc_attr( $class_active ); ?>">
							<div class="top">
								<strong><?php esc_html_e( 'Total ', 'eventlist' ); ?></strong>
								<span><?php esc_html_e( 'number of tickets', 'eventlist' ); ?></span>
							</div>
							<input type="number" 
							name="<?php echo esc_attr( $ticket.'['.$key.'][number_total_ticket]' ); ?>" 
							class="number_total_ticket" 
							value="<?php echo esc_attr(''); ?>" 
							placeholder="<?php echo esc_attr('10'); ?>" 
							min="0"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							/>
						</div>

						<div class="col_min_number_ticket col">
							<div class="top">
								<strong><?php esc_html_e( 'Minimum ', 'eventlist' ); ?></strong>
								<span><?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?></span>
							</div>
							<input type="number" 
							name="<?php echo esc_attr( $ticket.'['.$key.'][number_min_ticket]' ); ?>"
							class="number_min_ticket" 
							value="<?php echo esc_attr( '' ); ?>" 
							placeholder="<?php echo esc_attr( '1' ); ?>" 
							min="0"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							/>
						</div>

						<div class="col_max_number_ticket col">
							<div class="top">
								<strong><?php esc_html_e( 'Maximum ', 'eventlist' ); ?></strong>
								<span><?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?></span>
							</div>
							<input type="number" 
							name="<?php echo esc_attr( $ticket.'['.$key.'][number_max_ticket]' ); ?>"
							class="number_max_ticket"
							value="<?php echo esc_attr( '' ); ?>" 
							placeholder="<?php echo esc_attr( '10' ); ?>" 
							min="0"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							/>
						</div>
					</div>


					<!-- Middle Ticket -->
					<div class="middle_ticket">
						<div class="date_ticket">
							<div class="start_date">
								<span><?php esc_html_e( 'Start date for selling tickets', 'eventlist' ); ?></span>
								<div>
									<input type="text"
									name="<?php echo esc_attr( $ticket.'['.$key.'][start_ticket_date]' ); ?>" 
									class="start_ticket_date" 
									value="" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none"
									/>

									<input type="text" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][start_ticket_time]' ); ?>" 
									class="start_ticket_time" 
									value="" 
									data-time="<?php echo esc_attr($time); ?>" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none"
									/>
								</div>
							</div>

							<div class="end_date">
								<span><?php esc_html_e( 'End date for selling tickets', 'eventlist' ); ?></span>
								<div>
									<input type="text"
									name="<?php echo esc_attr( $ticket.'['.$key.'][close_ticket_date]' ); ?>" 
									class="close_ticket_date" 
									value="" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none"
									/>

									<input type="text" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][close_ticket_time]' ); ?>" 
									class="close_ticket_time" 
									value="" 
									data-time="<?php echo esc_attr($time); ?>" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none"
									/>
								</div>
							</div>
						</div>

						<div class="wrap_color_ticket">
							<div>
								<div class="span9">
									<span><?php esc_html_e( 'Ticket border color', 'eventlist' ); ?></span>
									<small><?php esc_html_e( '(Color border in ticket)', 'eventlist' ); ?></small>
								</div>
								<div class="span3">
									<input type="text" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][color_ticket]' ); ?>" 
		
									class="color_ticket" 
									value="<?php echo esc_attr( '#fff' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									/>
								</div>

							</div>

							<div>
								<div class="span9">
									<span><?php esc_html_e( 'Ticket label color', 'eventlist' ); ?></span>
									<small><?php esc_html_e( '(Color label in ticket)', 'eventlist' ); ?></small>
								</div>
								<div class="span3">
									<input type="text" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][color_label_ticket]' ); ?>" 
							
									class="color_label_ticket" 
									value="<?php echo esc_attr( '#fff' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									/>
								</div>

							</div>
							<div>
								<div class="span9">
									<span><?php esc_html_e( 'Ticket content color', 'eventlist' ); ?></span>
									<small><?php esc_html_e( '(Color content in ticket)', 'eventlist' ); ?></small>
								</div>
								<div class="span3">
									<input type="text" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][color_content_ticket]' ); ?>" 
						
									class="color_content_ticket" 
									value="<?php echo esc_attr( '#fff' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									/>
								</div>

							</div>

						</div>
					</div>


					<!-- Bottom Ticket -->
					<div class="bottom_ticket">
						<div class="title_add_desc">
							<small class="text_title"><?php esc_html_e( 'Description display at frontend and PDF Ticket', 'eventlist' ); ?><i class="arrow_triangle-down"></i></small>
						</div>
						<div class="content_desc">
							<textarea 
							name="<?php echo esc_attr( $ticket.'['.$key.'][desc_ticket]' ); ?>" 
							class="desc_ticket" 
							cols="30" rows="5"></textarea>

							<div class="image_ticket" data-index="<?php echo esc_attr($key); ?>">

								<div class="add_image_ticket">
									<input type="hidden" 
									name="<?php echo esc_attr($ticket.'['.$key.'][image_ticket]'); ?>" 
									class="image_ticket" 
									value="" />
									<i class="icon_plus_alt2"></i>
									<?php esc_html_e('Add ticket logo (.jpg, .png)', 'eventlist') ?>
									<br/><span><?php esc_html_e( 'Recommended size: 130x50px','eventlist' ); ?></span>
								</div>
								<div class="remove_image_ticket"></div>
							</div>
						</div>
						<div class="private_desc_ticket">
							<div class="title_add_desc">
								<small class="text_title">
									<?php esc_html_e( 'Private Description in Ticket - Only see when bought ticket', 'eventlist' ); ?>
									<i class="arrow_triangle-down"></i>
								</small>
							</div>
							<textarea 
							name="<?php echo esc_attr( $ticket.'['.$key.'][private_desc_ticket]' ); ?>" 
							class="private_desc_ticket" 
							cols="30" rows="5"></textarea>
						</div>

						<div class="setting_ticket_online">
							<div class="title_add_desc">
								<small class="text_title"><?php esc_html_e( 'These info only display in mail', 'eventlist' ); ?><i class="arrow_triangle-down"></i></small>
							</div>
							<div class="online_field link">
								<label><?php esc_html_e( 'Link', 'eventlist' ); ?></label>
								<input type="text" class="online_link" name="<?php echo esc_attr( $ticket.'['.$key.'][online_link]' ); ?>" value="" />
							</div>
							<div class="online_field password">
								<label><?php esc_html_e( 'Password', 'eventlist' ); ?></label>
								<input type="text" class="online_password" name="<?php echo esc_attr( $ticket.'['.$key.'][online_password]' ); ?>" value="" />
							</div>
							<div class="online_field other">
								<label><?php esc_html_e( 'Other info', 'eventlist' ); ?></label>
								<input type="text" class="online_other" name="<?php echo esc_attr( $ticket.'['.$key.'][online_other]' ); ?>" value="" />
							</div>
							
						</div>
					</div>


					<!-- Seat List -->
					<?php $class_active = $seat_option == 'simple' ? 'is-active' : ''; ?>
					<div class="wrap_seat_list <?php echo esc_attr( $class_active ); ?>">

						<div class="seat_setup_wrap">

							<label>
								<strong><?php esc_html_e( 'Setup Mode', 'eventlist' ); ?></strong>
							</label>

							<label for="setup_mode_manually_<?php echo esc_attr( $key ); ?>">
								<input type="radio"
								id="setup_mode_manually_<?php echo esc_attr( $key ); ?>"
								class="setup_mode_input"
								name="<?php echo esc_attr( $ticket.'['.$key.'][setup_mode]' ); ?>"
								value="manually" checked />
								<?php esc_html_e( 'Manually', 'eventlist' ); ?>
							</label>

							<label for="setup_mode_automatic_<?php echo esc_attr( $key ); ?>">
								<input type="radio"
								class="setup_mode_input"
								id="setup_mode_automatic_<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( $ticket.'['.$key.'][setup_mode]' ); ?>"
								value="automatic" />
								<?php esc_html_e( 'Automatic', 'eventlist' ); ?>
							</label>

						</div>

						<div class="seat_code_wrap">
							<label class="label">
								<strong><?php esc_html_e( 'Seat Code List:', 'eventlist' ); ?></strong>
							</label>


							<div class="seat_code_container">
								
							
								<div class="seat_code_manually is-active">
									<textarea name="<?php echo esc_attr( $ticket.'['.$key.'][seat_list]' ); ?>" class="seat_list" cols="30" rows="5" placeholder="<?php echo esc_attr( 'A1, B2, C3, ...' ); ?>"></textarea>
								</div>

								<div class="seat_code_automatic">

									<ul class="seat_code_setup"
									data-key="<?php echo esc_attr( $key ); ?>"
									data-ticket="<?php echo esc_attr( $ticket ); ?>">
									</ul>

									<a href="#"
									data-nonce="<?php echo esc_attr( wp_create_nonce('el_add_seat_code_row') ); ?>"
									class="button button-secondary add_seat_code_row">
										<?php esc_html_e( 'Add Seat', 'eventlist' ); ?>
									</a>
								</div>
							</div>

						</div>


					</div>


					<!-- The customer choose seat -->
					<div class="wrap_setup_seat" data-setup-seat="<?php echo esc_attr( 'yes' ); ?>" style="<?php if ( $seat_option == 'simple' ) echo esc_attr('display: flex;') ?>">
						<label class="label" for="setup_seat"><strong><?php esc_html_e( 'The customer choose seat:', 'eventlist' ); ?></strong></label>

						<label for="setup_seat_yes<?php echo esc_attr( $key ); ?>" class="el_input_radio">
							<?php esc_html_e( 'Yes', 'eventlist' ); ?>
							<input type="radio" 
								name="<?php echo esc_attr($ticket.'['.$key.'][setup_seat]'); ?>"
								class="setup_seat"
								id="setup_seat_yes<?php echo esc_attr( $key ); ?>"
								value="yes" 
								<?php echo esc_attr('checked'); ?>
							/>
							<span class="checkmark el_bg_white"></span>
						</label>

						<label for="setup_seat_no<?php echo esc_attr( $key ); ?>" class="el_input_radio">
							<?php esc_html_e( 'No', 'eventlist' ); ?>
							<input 
								type="radio" 
								name="<?php echo esc_attr($ticket.'['.$key.'][setup_seat]'); ?>" 
								class="setup_seat"
								id="setup_seat_no<?php echo esc_attr( $key ); ?>"
								value="no" 
							/>
							<span class="checkmark el_bg_white"></span>
						</label>
					</div>
					<div class="seat_map_ticket" style="<?php if ( $seat_option == 'simple' ) echo esc_attr('display: flex;') ?>">
						<label class="label">
							<strong>
								<?php esc_html_e( 'Sub-Regional Image:', 'eventlist' ); ?>
							</strong>
						</label>
						<div class="image_ticket_seat_map" data-index="<?php echo esc_attr($key); ?>">
							<div class="add_seat_map_ticket">
								<input type="hidden" 
									name="<?php echo esc_attr( $ticket.'['.$key.'][seat_map_ticket]' ); ?>" 
									class="seat_map_ticket" 
									value="" 
								/>
								<i class="icon_plus_alt2"></i>
								<?php esc_html_e('Add image (.jpg, .png)', 'eventlist'); ?>
							</div>
							<div class="remove_seat_map_ticket"></div>
						</div>
					</div>

					<!-- Save Ticket -->
					<a href="#" class="save_ticket"><?php esc_html_e('Done', 'eventlist') ?></a>
				</div>

			</div>

			<?php wp_die();		
		}


		/* Add Seat Map */
		public static function add_seat_map(){
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$post_data 	= $_POST['data'];
			$key 		= isset($post_data['count_seat']) ? sanitize_text_field( $post_data['count_seat'] ) : '1';
			$_prefix 	= OVA_METABOX_EVENT;
			$currency 	= _el_symbol_price();
			$time 		= el_calendar_time_format();
			$format 	= el_date_time_format_js();
			$first_day 	= el_first_day_of_week();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			?>
			<div class="item_seat" data-prefix="<?php echo esc_attr( $_prefix ); ?>">
				<div class="name_seat_map">
					<label><?php esc_html_e( 'Seat:', 'eventlist' ) ?></label>
					<input
						type="text"
						class="map_name_seat"
						value=""
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][id]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						placeholder="<?php echo esc_attr( 'A1, A2, A3, ...', 'eventlist' ); ?>" 
					/>
				</div>
				<div class="price_seat_map">
					<label><?php esc_html_e( 'Price:', 'eventlist' ) ?><?php echo esc_html( ' ('. $currency .'):' ); ?></label>
					<input
						type="text"
						class="map_price_seat"
						value=""
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][price]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						placeholder="<?php echo esc_attr( '50.00', 'eventlist' ); ?>" 
					/>
				</div>
				<input type="hidden" class="person_price" name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][person_price]' ); ?>" value="">
				<div class="type_seat_map">
					<label><?php esc_html_e( 'Type Seat:', 'eventlist' ); ?></label>
					<select
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][type_seat]' ); ?>"
						class="select_type_seat"
						data-default="<?php esc_attr_e( 'Select Type Seat', 'eventlist' ); ?>">
						<option value=""><?php esc_html_e( 'Select Type Seat', 'eventlist' ); ?></option>
					</select>
				</div>
				<div class="map_seat_start_date">
					<label><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][start_date]' ); ?>"
						class="seat_start_date"
						value=""
						data-format="<?php echo esc_attr( $format ); ?>"
						data-firstday="<?php echo esc_attr( $first_day ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_seat_start_time">
					<label><?php esc_html_e( 'Start Time:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][start_time]' ); ?>"
						class="seat_start_time"
						value=""
						data-time="<?php echo esc_attr( $time ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_seat_end_date">
					<label><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][end_date]' ); ?>"
						class="seat_end_date"
						value=""
						data-format="<?php echo esc_attr( $format ); ?>"
						data-firstday="<?php echo esc_attr( $first_day ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_seat_end_time">
					<label><?php esc_html_e( 'End Time:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[seat]['.$key.'][end_time]' ); ?>"
						class="seat_end_time"
						value=""
						data-time="<?php echo esc_attr( $time ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<a href="#" class="button remove_seat_map"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
			</div>
			<?php
			wp_die();
		}

		/* Add Area Map */
		public static function add_area_map(){
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$post_data 	= $_POST['data'];
			$key 		= isset($post_data['count_seat']) ? sanitize_text_field( $post_data['count_seat'] ) : '0';
			$_prefix 	= OVA_METABOX_EVENT;
			$currency 	= _el_symbol_price();
			$time 		= el_calendar_time_format();
			$format 	= el_date_time_format_js();
			$first_day 	= el_first_day_of_week();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			?>
			<div class="item_area" data-prefix="<?php echo esc_attr( $_prefix ); ?>">
				<div class="name_area_map">
					<label><?php esc_html_e( 'Area:', 'eventlist' ) ?></label>
					<input
						type="text"
						class="map_name_area"
						value=""
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][id]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						placeholder="<?php echo esc_attr( 'insert only an area', 'eventlist' ); ?>"
					/>
				</div>
				<div class="price_area_map">
					<label><?php esc_html_e( 'Price:', 'eventlist' ) ?><?php echo esc_html( ' ('. $currency .'):' ); ?></label>
					<input
						type="text"
						class="map_price_area"
						value=""
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][price]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						placeholder="<?php echo esc_attr( '50.00', 'eventlist' ); ?>" 
					/>
				</div>
				<input type="hidden" class="person_price" name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][person_price]' ); ?>" value="">
				<div class="qty_area_map">
					<label><?php esc_html_e( 'Quantity:', 'eventlist' ) ?></label>
					<input
						type="number"
						class="map_qty_area"
						value=""
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][qty]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						placeholder="<?php echo esc_attr( '100', 'eventlist' ); ?>"
						min="0"
					/>
				</div>
				<div class="type_area_map">
					<label><?php esc_html_e( 'Type Seat:', 'eventlist' ); ?></label>
					<select
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][type_seat]' ); ?>"
						class="select_type_area"
						data-default="<?php esc_attr_e( 'Select Type Seat', 'eventlist' ); ?>">
						<option value=""><?php esc_html_e( 'Select Type Seat', 'eventlist' ); ?></option>
					</select>
				</div>
				<div class="map_area_start_date">
					<label><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][start_date]' ); ?>"
						class="area_start_date"
						value=""
						data-format="<?php echo esc_attr( $format ); ?>"
						data-firstday="<?php echo esc_attr( $first_day ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_area_start_time">
					<label><?php esc_html_e( 'Start Time:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][start_time]' ); ?>"
						class="area_start_time"
						value=""
						data-time="<?php echo esc_attr( $time ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_area_end_date">
					<label><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][end_date]' ); ?>"
						class="area_end_date"
						value=""
						data-format="<?php echo esc_attr( $format ); ?>"
						data-firstday="<?php echo esc_attr( $first_day ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<div class="map_area_end_time">
					<label><?php esc_html_e( 'End Time:', 'eventlist' ); ?></label>
					<input
						type="text"
						name="<?php echo esc_attr( $_prefix.'ticket_map[area]['.$key.'][end_time]' ); ?>"
						class="area_end_time"
						value=""
						data-time="<?php echo esc_attr( $time ); ?>"
						placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</div>
				<a href="#" class="button remove_area_map"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
			</div>
			<?php
			wp_die();
		}

		/* Add Description Seat Map */
		public static function add_desc_seat_map(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = $_POST['data'];
			$key = isset($post_data['count_seat']) ? sanitize_text_field( $post_data['count_seat'] ) : '1';

			$_prefix = OVA_METABOX_EVENT;
			$currency = _el_symbol_price();

			?>

			<div class="item_desc_seat" data-prefix="<?php echo esc_attr( $_prefix ); ?>">
				<div class="item-col">
					<label><?php esc_html_e( 'Type Seat:', 'eventlist' ) ?></label>
					<input type="text" 
					class="map_type_seat" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'ticket_map[desc_seat]['.$key.'][map_type_seat]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr__( 'Standard', 'eventlist' ); ?>" 
					/>
				</div>

				<div class="item-col">
					<label>
						<?php esc_html_e( 'Price', 'eventlist' ) ?>
						<?php echo esc_html( ' ('. $currency .'):' ); ?>
					</label>
					<input type="text" 
					class="map_price_type_seat" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'ticket_map[desc_seat]['.$key.'][map_price_type_seat]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr__( '50.00', 'eventlist' ); ?>" 
					/>
				</div>

				<div class="item-col">
					<label><?php esc_html_e( 'Description:', 'eventlist' ) ?></label>
					<input type="text" 
					class="map_desc_type_seat" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'ticket_map[desc_seat]['.$key.'][map_desc_type_seat]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr__( 'Description of type seat', 'eventlist' ); ?>" 
					/>
				</div>

				<div class="item-col">
					<label><?php esc_html_e( 'Color:', 'eventlist' ) ?></label>
					<input type="text" 
					class="map_color_type_seat" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'ticket_map[desc_seat]['.$key.'][map_color_type_seat]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( '#ffffff', 'eventlist' ); ?>" 
					/>
				</div>
				<a href="#" class="button remove_desc_seat_map"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
			</div>

			<?php
			wp_die();
		}


		/* Add Calendar */
		public static function mb_add_calendar(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$_prefix = OVA_METABOX_EVENT;
			$post_data = $_POST['data'];
			$index = isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';
			
			$time = el_calendar_time_format();
			$format = el_date_time_format_js();
			$first_day = el_first_day_of_week();

			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			?>

			<div class="item_calendar">
				<input type="hidden" 
				class="calendar_id" 
				name="<?php echo esc_attr( $_prefix.'calendar['.$index.'][calendar_id]' ); ?>"
				value=""
				/>

				<div class="date">

					<label class="label"><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>

					<input type="text" 
					class="calendar_date" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'calendar['.$index.'][date]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
					data-format="<?php echo esc_attr( $format ); ?>" 
					data-firstday="<?php echo esc_attr( $first_day ); ?>" 
					required="required"
					/>

				</div>

				<div class="end_date">

					<label class="label"><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>

					<input type="text" 
					class="calendar_end_date" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'calendar['.$index.'][end_date]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
					data-format="<?php echo esc_attr( $format ); ?>" 
					data-firstday="<?php echo esc_attr( $first_day ); ?>" 
					required="required"
					/>

				</div>


				<div class="start_time">
					<label class="label"><?php esc_html_e( 'From:', 'eventlist' ); ?></label>

					<input type="text" 
					class="calendar_start_time" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'calendar['.$index.'][start_time]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" 
					data-time="<?php echo esc_attr( $time ); ?>"
					required="required"/>

				</div>


				<div class="end_time">
					<label class="label"><?php esc_html_e( 'To:', 'eventlist' ); ?></label>

					<input type="text" 
					class="calendar_end_time" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'calendar['.$index.'][end_time]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" 
					data-time="<?php echo esc_attr( $time ); ?>"
					required="required"
					/>


				</div>

				<div class="book_before_minutes">
					<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
					<input type="number" 
					name="<?php echo esc_attr($_prefix.'calendar['.$index.'][book_before_minutes]' ); ?>" 
					class="number_time_book_before"
					value="<?php echo esc_attr( '' ); ?>" 
					placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					/>
				</div>
				<button class="button remove_calendar">x</button>
			</div>

			<?php
			wp_die();
		}


		/* Add Disable Date */
		public static function mb_add_disable_date(){
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$_prefix 		= OVA_METABOX_EVENT;
			$post_data 		= $_POST['data'];
			$index 			= isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';
			$schedules_time = isset( $post_data['schedules_time'] ) ? ( $post_data['schedules_time'] ) : '';
			$time 			= el_calendar_time_format();
			$format 		= el_date_time_format_js();
			$first_day 		= el_first_day_of_week();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();
			?>
			<div class="item_disable_date">
				<span>
					<?php esc_html_e( 'Form:', 'eventlist' ); ?>
					<input 
						type="text" 
						class="start_date" 
						value="" 
						name="<?php echo esc_attr( $_prefix.'disable_date['.$index.'][start_date]' ); ?>" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" />
				</span>
				<span>
					<?php esc_html_e( 'To:', 'eventlist' ); ?>
					<input 
						type="text" 
						class="end_date" 
						value="" 
						name="<?php echo esc_attr( $_prefix.'disable_date['.$index.'][end_date]' ); ?>" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" />
				</span>
				<?php if ( $schedules_time ): ?>
					<span class="disable_time">
						<select name="<?php echo esc_attr( $_prefix.'disable_date['.$index.'][schedules_time]' ); ?>" class="schedules_time">
							<option value=""><?php esc_html_e( 'Choose Schedules Time', 'eventlist' ); ?></option>
							<?php foreach ( $schedules_time as $key => $value ) { ?>
								<option value="<?php echo esc_attr( $key ); ?>" >
									<?php echo esc_html( $value['start_time'].'-'.$value['end_time'] ); ?>
								</option>
							<?php } ?>
						</select>
					</span>
				<?php endif; ?>
				<button class="button remove_disable_date"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/* Add Disable Time Slot */
		public static function mb_add_disable_time_slot() {
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$_prefix 		= OVA_METABOX_EVENT;
			$post_data 		= $_POST['data'];
			$index 			= isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';
			$time 			= el_calendar_time_format();
			$format 		= el_date_time_format_js();
			$first_day 		= el_first_day_of_week();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();
			?>
				<div class="item_disable_time_slot">
					<span>
						<?php esc_html_e( 'Form:', 'eventlist' ); ?>
						<input 
							type="text" 
							class="start_date" 
							name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$index.'][start_date]' ); ?>" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" />
					</span>
					<span>
						<input 
							type="text" 
							class="start_time" 
							name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$index.'][start_time]' ); ?>" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
							data-time="<?php echo esc_attr( $time ); ?>" />
					</span>
					<span>
						<?php esc_html_e( 'To:', 'eventlist' ); ?>
						<input 
							type="text" 
							class="end_date" 
							name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$index.'][end_date]' ); ?>" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" />
					</span>
					<span>
						<input 
							type="text" 
							class="end_time" 
							name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$index.'][end_time]' ); ?>" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
							data-time="<?php echo esc_attr( $time ); ?>" />
					</span>
					<button class="button remove_disable_time_slot"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
				</div>
			<?php

			wp_die();
		}

		/* Add schedules Time */
		public static function mb_add_schedules_time(){
			if( !isset( $_POST['data'] ) ) wp_die();
			$_prefix = OVA_METABOX_EVENT;
			$post_data = $_POST['data'];
			$index = isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';

			$time = el_calendar_time_format();
			$format = el_date_time_format_js();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			?>
			<div class="item_schedules_time" data-key= '<?php echo esc_attr($index) ;?>'>

				<span>
					<?php esc_html_e( 'Form:', 'eventlist' ); ?>
					<input type="text" 
					class="start_time" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'schedules_time['.$index.'][start_time]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
					data-time="<?php echo esc_attr( $time ); ?>"
					required="required"
					/>
				</span>

				<span>
					<?php esc_html_e( 'To:', 'eventlist' ); ?>
					<input type="text" 
					class="end_time" 
					value="" 
					name="<?php echo esc_attr( $_prefix.'schedules_time['.$index.'][end_time]' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
					data-time="<?php echo esc_attr( $time ); ?>"
					required="required"
					/>
				</span>

				<span class="schedules_time_book_before">
					<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
					<input type="number" 
					name="<?php echo esc_attr( $_prefix.'schedules_time['.$index.'][book_before]' ); ?>" 
					class="schedules_time_book"
					value="<?php echo esc_attr( ''); ?>"
					placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" 
					/>
				</span>

				<button class="button remove_schedules_time"><?php esc_html_e( 'x', 'eventlist' ) ?></button>
			</div>
			<?php
			wp_die();
		}


		/* Add Coupon */
		public static function mb_add_coupon() {
			if( !isset( $_POST['data'] ) ) wp_die();
			$_prefix = OVA_METABOX_EVENT;
			$post_data = $_POST['data'];
			$index = isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : '';
			$post_id = isset( $post_data['post_id'] ) ? sanitize_text_field( $post_data['post_id'] ) : '';
			
			$time = el_calendar_time_format();
			$format = el_date_time_format_js();
			$first_day = el_first_day_of_week();
			$placeholder_dateformat = el_placeholder_dateformat();
			$placeholder_timeformat = el_placeholder_timeformat();

			?>

			<div class="item_coupon">
				<input 
					type="hidden"  
					class="coupon_id" 
					name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][coupon_id]' ); ?>" 
					value=""
				/>
				<div class="wrap_discount_code">
					<input 
						type="text"  
						class="discount_code" 
						name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][discount_code]' ); ?>" 
						value="" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						placeholder="<?php esc_attr_e( 'DISCOUNT CODE', 'eventlist' ); ?>"
					/>
					<span class="least_char"><?php esc_html_e( 'Discount code must have at least 5 characters', 'eventlist' ); ?></span>
					<small class="comment_discount_code"><?php esc_html_e( 'Only alphanumeric characters allowed (A-Z and 0-9)', 'eventlist' ); ?></small>
				</div>
				<div class="discount_amount">
					<span><strong><?php esc_html_e( 'Discount Amount', 'eventlist' ); ?></strong></span>:&nbsp;
					<input 
						type="text" 
						class="discount_amout_number" 
						name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][discount_amout_number]' ); ?>" 
						value="" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						placeholder="<?php esc_attr_e( '5', 'eventlist' ); ?>" 
					/>
					<span><?php esc_html_e( '$', 'eventlist' ); ?></span>&nbsp;
					<span><?php esc_html_e( 'or', 'eventlist' ); ?></span>&nbsp;
					<input 
						type="text" 
						class="discount_amount_percent" 
						name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][discount_amount_percent]' ); ?>" 
						value="" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						placeholder="<?php esc_attr_e( '10', 'eventlist' ); ?>"
					/>
					<span><?php esc_html_e( '% of ticket price', 'eventlist' ); ?></span>
				</div>
				<div class="discount_time">
					<div class="start_time">
						<span><strong><?php esc_html_e( 'Start', 'eventlist' ); ?></strong></span>
						<input type="text" 
							class="coupon_start_date" 
							name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][start_date]' ); ?>" 
							value=""
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						/>
						<input type="text" 
							class="coupon_start_time" 
							name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][start_time]' ); ?>" 
							value="" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
							data-time="<?php echo esc_attr( $time ); ?>" 
						/>
					</div>

					<div class="end_time">
						<span><strong><?php esc_html_e( 'End', 'eventlist' ); ?></strong></span>
						<input type="text" 
							class="coupon_end_date" 
							name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][end_date]' ); ?>" 
							value=""
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						/>
						<input type="text" 
							class="coupon_end_time" 
							name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][end_time]' ); ?>" 
							value="" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
							data-time="<?php echo esc_attr( $time ); ?>" 
						/>
					</div>
				</div>
				<div class="number_coupon_ticket">
					<span><strong><?php esc_html_e( 'Ticket types', 'eventlist' ); ?></strong></span>
					<div class="ticket">
						<div class="all_ticket">

							<label for="coupon_all_ticket<?php echo esc_attr( $index ); ?>" class="el_input_checkbox">
								<?php esc_html_e( 'All ticket types', 'eventlist' ); ?>
								<input 
									type="checkbox"
									id="coupon_all_ticket<?php echo esc_attr( $index ); ?>" 
									class="coupon_all_ticket" 
									name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][all_ticket]' ); ?>" 
									checked
									value="1"
								/>
								<span class="checkmark"></span>
							</label>


						</div>

						<div class="all_quantity vendor_field">
							<label for="coupon_quantity"><?php esc_html_e( 'Quantity', 'eventlist' ); ?></label>
							<input 
								type="number"
								id="coupon_quantity" 
								class="coupon_quantity" 
								value="" 
								min="0" 
								name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][quantity]' ); ?>" 
								placeholder="<?php echo esc_attr( '100' ); ?>" 
								autocomplete="off" autocorrect="off" autocapitalize="none"
							/>
						</div>
						<div class="wrap_list_ticket">
							<?php 
							$ticket 		= get_post_meta( $post_id, $_prefix.'ticket', true );
							$seat_option 	= get_post_meta( $post_id, $_prefix.'seat_option', true );

							if ( $ticket && $seat_option != 'map' ) {
								foreach ( $ticket as $key_ticket => $value_ticket ) { ?>
									<div class="item_ticket">

										<label for="coupon_ticket<?php echo esc_attr( $index.'_'.$key_ticket ); ?>"
											class="el_input_checkbox">
											<?php echo esc_html( $value_ticket['name_ticket'] ); ?>
											<input 
												type="checkbox"
												class="list_ticket"
												id="coupon_ticket<?php echo esc_attr( $index.'_'.$key_ticket ); ?>"
												name="<?php echo esc_attr( $_prefix.'coupon['.$index.'][ticket]['.$key_ticket.']' ); ?>" 
												value="<?php echo esc_attr( isset( $value_ticket['ticket_id'] ) ? $value_ticket['ticket_id'] : '' ); ?>" 
											/> 
											
											<span class="checkmark"></span>
										</label>
									</div>
								<?php	} 
							} ?>
						</div>
					</div>
				</div>
				<button class="button remove_coupon"><?php esc_html_e( 'Remove Coupon', 'eventlist' ); ?></button>
			</div>

			<?php

			wp_die();
		}


		/* Add Booking */
		public function add_custom_booking() {
			if ( ! isset( $_POST['data'] ) ) wp_die();

			$post_data 	= $_POST['data'];
			$type_seat 	= isset( $post_data['type_seat'] ) ? sanitize_text_field( $post_data['type_seat'] ) : 'none';
			$event_id 	= isset( $post_data['event_id'] ) ? sanitize_text_field( $post_data['event_id'] ) : '';
			$index 		= isset( $post_data['index'] ) ? sanitize_text_field( $post_data['index'] ) : 0;
			$_prefix 	= OVA_METABOX_EVENT;
		?>
			<tr class="cart-item">
				<td class="name">
					<a href="#" class="delete_item">x</a>
					<?php if ( $type_seat == 'map' ): ?>
						<input
							type="text"
							class="name"
							value=""
							name="<?php echo esc_attr( $_prefix.'cart['.$index.'][id]' ); ?>"
							placeholder="<?php esc_html_e( 'seat code', 'eventlist' ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
						/>
					<?php else: ?>
						<input
							type="text"
							class="name"
							value=""
							name="<?php echo esc_attr( $_prefix.'cart['.$index.'][name]' ); ?>"
							placeholder="<?php esc_html_e( 'ticket name', 'eventlist' ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
						/>
					<?php endif; ?>
				</td>
				<td class="qty">
					<input
						type="number"
						class="qty"
						value=""
						name="<?php echo esc_attr( $_prefix.'cart['.$index.'][qty]' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
					<input
						type="text"
						class="seat"
						value=""
						name="<?php echo esc_attr( $_prefix.'cart['.$index.'][seat]' ); ?>"
						placeholder="<?php esc_attr_e('A1, A2, A3, ...', 'eventlist'); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
						<?php if ( $type_seat == 'simple' ) echo esc_attr('required'); ?>
						<?php if ( $type_seat == 'none' || $type_seat == 'map' ) echo esc_attr( 'style=display:none;' ); ?>
					/>
				</td>
				<td class="sub-total">
					<input
						type="text"
						class="price"
						value=""
						name="<?php echo esc_attr( $_prefix.'cart['.$index.'][price]' ); ?>"
						placeholder="<?php esc_html_e( '10.5', 'eventlist' ); ?>"
						autocomplete="off" autocorrect="off" autocapitalize="none"
					/>
				</td>
			</tr>
		<?php
			wp_die();
		}

		public function el_get_idcal_seatopt(){

			if( !isset( $_POST['data'] ) ) wp_die();
			$post_data = $_POST['data'];
			$id_event = $post_data['id_event'];
			$id_cal = $post_data['id_cal'];

			$_prefix = OVA_METABOX_EVENT;

			$list_calendar =  get_arr_list_calendar_by_id_event( $id_event );

			$seat_option = get_post_meta( $id_event, $_prefix.'seat_option', true );
			
			?>
			<?php if( $list_calendar ){ ?>
				<label>
					<strong><?php esc_html_e( "Event Calendar",  "eventlist" ); ?>: </strong>
						<select name="<?php echo esc_attr( $_prefix.'id_cal' ); ?>" >
							<?php foreach ($list_calendar as $key => $value) { ?>
								<option value="<?php echo esc_attr( $value['calendar_id'] ); ?>" <?php echo selected( $value['calendar_id'], $id_cal ) ?>>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime($value['date']) ) ); ?>
								</option>
							<?php } ?>
						</select>
					</label>
				<br><br>
				<input type="hidden" name="seat_option" class="seat_option" value="<?php echo esc_attr( $seat_option ); ?>" />

				
					<?php if ( $seat_option == 'none' || $seat_option == 'simple' ) { ?>
						<div 
							class="detail_booking_head_cart" 
							data-name="<?php esc_html_e( 'Ticket', 'eventlist' ); ?>"
							data-qty="<?php esc_html_e( 'Quantity', 'eventlist' ); ?>"
							data-sub_total="<?php esc_html_e( 'Sub Total', 'eventlist' ); ?>"
						>
								
						</div>
						
					<?php } elseif ( $seat_option == 'map' ) { ?>

						<div 
							class="detail_booking_head_cart" 
							data-name="<?php esc_html_e( 'Seat Code', 'eventlist' ); ?>"
							data-sub_total="<?php esc_html_e( 'Sub Total', 'eventlist' ); ?>"
						>		
						</div>

						
					<?php } ?>
				

			<?php }else{ echo esc_html__( 'Please make Calendar for event', 'eventlist' ).'<br/><br/>'; } ?>

			<?php
			wp_die();
		}


		public function el_check_book_before_minutes() {
			$number_time = isset($_POST['number_time']) ? sanitize_text_field($_POST['number_time']) : '';
			$date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
			$end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
			$start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
			$end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';

			$start = isset( $date ) ? el_get_time_int_by_date_and_hour($date, $start_time) : '';
			$end = isset( $end_date ) ? el_get_time_int_by_date_and_hour($end_date, $end_time) : '';

			$check_number = floatval( $start - $end );
			$check_time = floatval( $number_time )*60; 


			if($check_number > $check_time){

				echo json_encode( array(  'msg' => esc_html__( 'Booking before x minutes must be more than', 'eventlist' ).' '.floatval( $check_number / 60 ) ));
				wp_die();

			}
			wp_die();

			
		}

		public function el_check_calendar_recurrence_time_book() {
			$number_time = isset($_POST['number_time']) ? sanitize_text_field($_POST['number_time']) : '';
			$start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
			$end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';

			$start = isset( $start_time ) ? el_get_time_int_by_date_and_hour( 0, $start_time) : '';
			$end = isset( $end_time ) ? el_get_time_int_by_date_and_hour( 0, $end_time) : '';

			$check_number = floatval( $start - $end );
			$check_time = floatval( $number_time )*60; 


			if($check_number > $check_time){

				echo json_encode( array(  'msg' => esc_html__( 'Booking before x minutes must be more than', 'eventlist' ).' '.floatval( $check_number / 60 ) ) );
				wp_die();

			}
			wp_die();

			
		}


		public function el_check_schedules_time_book() {
			$number_time = isset($_POST['number_time']) ? sanitize_text_field($_POST['number_time']) : '';
			$start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
			$end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';

			$start = isset( $start_time ) ? el_get_time_int_by_date_and_hour( 0, $start_time) : '';
			$end = isset( $end_time ) ? el_get_time_int_by_date_and_hour( 0, $end_time) : '';

			$check_number = floatval( $start - $end );
			$check_time = floatval( $number_time )*60; 


			if($check_number > $check_time){

				echo json_encode( array(  'msg' => esc_html__( 'Booking before x minutes must be more than', 'eventlist' ).' '.floatval( $check_number / 60 ) ) );
				wp_die();

			}

			wp_die();
		}

		public function el_replace_get_tickets() {
			$event_id 	= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$replaced 	= isset( $_POST['replaced'] ) ? sanitize_text_field( $_POST['replaced'] ) : '';
			$per_page 	= isset( $_POST['posts_per_page'] ) && absint( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 25;

			$result 	= $pagination = $error = '';
			$count 		= sprintf( esc_html__( '%s items', 'eventlist' ), 0 );

			if ( $event_id ) {
				$prefix = OVA_METABOX_EVENT;
				$start 	= $end = '';

				// Start Date
				if ( $start_date && $start_time ) {
					$start = strtotime( $start_date . ' ' . $start_time );
				} elseif ( $start_date ) {
					$start = strtotime( $start_date );
				} else {
					$start = false;
				}

				// End Date
				if ( $end_date && $end_time ) {
					$end = strtotime( $end_date . ' ' . $end_time );
				} elseif ( $end_date ) {
					$end = strtotime( $end_date );
				} else {
					$end = false;
				}

				$args = array(
					'post_type' 		=> 'el_tickets',
					'post_status' 		=> 'publish',
					'posts_per_page' 	=> $per_page,
					'meta_query' 		=> array(
						array(
							'key' 		=> $prefix . 'event_id',
							'value' 	=> $event_id,
							'compare' 	=> '='
						)
					)
				);

				if ( $start ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_start',
						'value' 	=> $start,
						'compare' 	=> '='
					);
				}

				if ( $end ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_end',
						'value' 	=> $end,
						'compare' 	=> '='
					);
				}

				if ( $replaced && $replaced === 'on' ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'replace_date_status',
						'value' 	=> '',
						'compare' 	=> '!='
					);
				}

				$tickets = new WP_Query( $args );

				ob_start();

				if ( $tickets->have_posts() ) {
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');

					if ( count( $tickets->posts ) == 1 ) {
						$count = sprintf( esc_html__( '%s item', 'eventlist' ), count( $tickets->posts ) );
					} else {
						$count = sprintf( esc_html__( '%s items', 'eventlist' ), count( $tickets->posts ) );
					}

					while ( $tickets->have_posts() ) {
						$tickets->the_post();

						$ticket_id 	= get_the_id();
						$event_name = get_post_meta( $ticket_id, $prefix.'name_event', true );
						$booking_id = get_post_meta( $ticket_id, $prefix.'booking_id', true );
						$status 	= get_post_meta( $ticket_id, $prefix.'ticket_status', true );
						$start_date = get_post_meta( $ticket_id, $prefix.'date_start', true );
						$end_date 	= get_post_meta( $ticket_id, $prefix.'date_end', true );
						$qr_code 	= get_post_meta( $ticket_id, $prefix.'qr_code', true );
						$customer 	= get_post_meta( $ticket_id, $prefix.'name_customer', true );
						$arr_venue 	= get_post_meta( $ticket_id, $prefix.'venue', true );
						$address 	= get_post_meta( $ticket_id, $prefix.'address', true );
						$venue 		= is_array( $arr_venue ) ? implode( ", ", $arr_venue ) : $arr_venue;
					?>
						<tr>
							<th scope="row" class="check-column">
								<input 
									type="checkbox"
									name="ticket_id[]"
									value="<?php echo esc_attr( $ticket_id ); ?>"
								/>
							</th>
							<th class="ticket_number">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $ticket_id ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_type">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( get_the_title( $ticket_id ) ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_status">
		                		<?php echo esc_html( $status ); ?>
		                	</th>
		                	<th class="start_date">
		                		<?php echo esc_html( date_i18n( $date_format, $start_date ) . ' - ' . date_i18n( $time_format, $start_date ) ); ?>
		                	</th>
		                	<th class="end_date">
		                		<?php echo esc_html( date_i18n( $date_format, $end_date ) . ' - ' . date_i18n( $time_format, $end_date ) ); ?>
		                	</th>
		                	<th class="ticket_qr_code">
		                		<?php echo esc_html( $qr_code ); ?>
		                	</th>
		                	<th class="customer_name">
		                		<?php echo esc_html( $customer ); ?>
		                	</th>
		                	<th class="customer_address">
		                		<?php if ( $venue ): ?>
		                			<?php echo sprintf( esc_html__( 'Venue: %s', 'eventlist' ), esc_html($venue) ); ?>
		                			<br>
		                		<?php endif; ?>
		                		<?php if ( $address ): ?>
		                			<?php echo sprintf( esc_html__( 'Address: %s', 'eventlist' ), esc_html( $address ) ); ?>
		                			<br>
		                		<?php endif; ?>
		                	</th>
		                	<th class="event">
		                		<a href="<?php echo esc_url( get_edit_post_link( $event_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $event_name ); ?>
		                		</a>
		                	</th>
		                	<th class="booking_id">
		                		<a href="<?php echo esc_url( get_edit_post_link( $booking_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $booking_id ); ?>
		                		</a>
		                	</th>
						</tr>
					<?php }
				} else {
					$error = 'yes';
					?>
					<tr class="no-items">
	            		<td class="colspanchange" colspan="11">
	            			<?php esc_html_e( 'No items found.', 'eventlist' ); ?>
	            		</td>
	            	</tr>
					<?php
				}
				wp_reset_postdata();

				$result = ob_get_contents();
				ob_end_clean();

				$max_pages 		= $tickets->max_num_pages;
				$total_items 	= $tickets->found_posts;

				if ( $max_pages > 1 ) {
					ob_start();
				?>
					<span class="total-items" title="<?php esc_attr_e( 'Total items', 'eventlist' ); ?>">
						<?php if ( $total_items == 1 ): ?>
		        			<?php echo sprintf( esc_html__( '%s item', 'eventlist' ), esc_html( $total_items ) ); ?>
		        		<?php else: ?>
		        			<?php echo sprintf( esc_html__( '%s items', 'eventlist' ), esc_html($total_items) ); ?>
		        		<?php endif; ?>
		        	</span>
		        	<span class="posts-per-page" title="<?php esc_attr_e( 'Posts per page', 'eventlist' ); ?>">
		        		<select name="el_posts_per_page" class="el_posts_per_page">
		        			<option value="25" <?php selected( 25, $per_page ); ?>>25</option>
		        			<option value="50" <?php selected( 50, $per_page ); ?>>50</option>
		        			<option value="100" <?php selected( 100, $per_page ); ?>>100</option>
		        			<option value="250" <?php selected( 250, $per_page ); ?>>250</option>
		        			<option value="500" <?php selected( 500, $per_page ); ?>>500</option>
		        		</select>
		        	</span>
		        	<span class="pagination-links">
		        		<span 
		        			class="btn-pagination el-first disable"
		        			title="<?php esc_attr_e( 'First page', 'eventlist' ); ?>"
		        			data-paged="1">«</span>
		        		<span 
		        			class="btn-pagination el-prev disable"
		        			title="<?php esc_attr_e( 'Previous page', 'eventlist' ); ?>"
		        			data-paged="1">‹</span>
		        		<span 
		        			class="el-current"
		        			title="<?php esc_attr_e( 'Current page', 'eventlist' ); ?>"
		        			data-paged="1">1</span>
		        		<span class="text-pagination" title="<?php esc_attr_e( 'Current page', 'eventlist' ); ?>">
		        			<?php esc_html_e( 'of', 'eventlist' ); ?>
		        			<span 
		        				class="total-page" 
		        				title="<?php esc_attr_e( 'Total page', 'eventlist' ); ?>"
		        				data-max-pages="<?php echo esc_attr( $max_pages ); ?>">
		        				<?php echo esc_html( $max_pages ); ?>
		        			</span>
		        		</span>
		        		<span 
		        			class="btn-pagination el-next"
		        			title="<?php esc_attr_e( 'Next page', 'eventlist' ); ?>"
		        			data-paged="2">›</span>
		        		<span 
		        			class="btn-pagination el-last"
		        			title="<?php esc_attr_e( 'Last page', 'eventlist' ); ?>"
		        			data-paged="<?php echo esc_attr( $max_pages ); ?>">»</span>
		        	</span>
				<?php
					$pagination = ob_get_contents();
					ob_end_clean();
				}
			}

			echo json_encode( array( 
				'result' 		=> $result, 
				'count' 		=> $count, 
				'error' 		=> $error, 
				'pagination' 	=> $pagination 
			));

			wp_die();
		}

		public function el_replace_ticket_date() {
			$ticket_ids = isset( $_POST['ticket_ids'] ) ? $_POST['ticket_ids'] : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$result 	= $error = '';
			
			if ( ! empty( $ticket_ids ) && is_array( $ticket_ids ) ) {
				if ( $start_date && $start_time && $end_date && $end_time ) {
					$start 	= strtotime( $start_date . ' ' . $start_time );
					$end 	= strtotime( $end_date . ' ' . $end_time );

					foreach ( $ticket_ids as $ticket_id ) {
						$replace_date_status = absint( get_post_meta( $ticket_id, OVA_METABOX_EVENT.'replace_date_status', true ) );

						update_post_meta( $ticket_id, OVA_METABOX_EVENT.'date_start', $start );
						update_post_meta( $ticket_id, OVA_METABOX_EVENT.'date_end', $end );
						update_post_meta( $ticket_id, OVA_METABOX_EVENT.'replace_date_status', $replace_date_status + 1 );
					}

					if ( count( $ticket_ids ) == 1 ) {
						$result = sprintf( esc_html__( 'Update %s item success.', 'eventlist' ), count( $ticket_ids ) );
					} else {
						$result = sprintf( esc_html__( 'Update %s items success.', 'eventlist' ), count( $ticket_ids ) );
					}

					$error 	= false;
				} else {
					$result = esc_html__( 'Please select date & time.', 'eventlist' );
					$error 	= true;
				}
			} else {
				$result = esc_html__( 'Please select ticket.', 'eventlist' );
				$error 	= true;
			}

			echo json_encode( array( 'result' => $result, 'error' => $error ) );

			wp_die();
		}

		public function el_replace_ticket_date_posts_per_page() {
			$event_id 	= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$replaced 	= isset( $_POST['replaced'] ) ? sanitize_text_field( $_POST['replaced'] ) : '';
			$per_page 	= isset( $_POST['posts_per_page'] ) && absint( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 25;

			$result 	= $pagination = '';
			$count 		= sprintf( esc_html__( '%s items', 'eventlist' ), 0 );

			if ( $event_id ) {
				$prefix = OVA_METABOX_EVENT;
				$start 	= $end = '';

				// Start Date
				if ( $start_date && $start_time ) {
					$start = strtotime( $start_date . ' ' . $start_time );
				} elseif ( $start_date ) {
					$start = strtotime( $start_date );
				} else {
					$start = false;
				}

				// End Date
				if ( $end_date && $end_time ) {
					$end = strtotime( $end_date . ' ' . $end_time );
				} elseif ( $end_date ) {
					$end = strtotime( $end_date );
				} else {
					$end = false;
				}

				$args = array(
					'post_type' 		=> 'el_tickets',
					'post_status' 		=> 'publish',
					'posts_per_page' 	=> $per_page,
					'meta_query' 		=> array(
						array(
							'key' 		=> $prefix . 'event_id',
							'value' 	=> $event_id,
							'compare' 	=> '='
						)
					)
				);

				if ( $start ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_start',
						'value' 	=> $start,
						'compare' 	=> '='
					);
				}

				if ( $end ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_end',
						'value' 	=> $end,
						'compare' 	=> '='
					);
				}

				if ( $replaced && $replaced === 'on' ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'replace_date_status',
						'value' 	=> '',
						'compare' 	=> '!='
					);
				}

				$tickets = new WP_Query( $args );

				ob_start();

				if ( $tickets->have_posts() ) {
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');

					if ( count( $tickets->posts ) == 1 ) {
						$count = sprintf( esc_html__( '%s item', 'eventlist' ), count( $tickets->posts ) );
					} else {
						$count = sprintf( esc_html__( '%s items', 'eventlist' ), count( $tickets->posts ) );
					}

					while ( $tickets->have_posts() ) {
						$tickets->the_post();

						$ticket_id 	= get_the_id();
						$event_name = get_post_meta( $ticket_id, $prefix.'name_event', true );
						$booking_id = get_post_meta( $ticket_id, $prefix.'booking_id', true );
						$status 	= get_post_meta( $ticket_id, $prefix.'ticket_status', true );
						$start_date = get_post_meta( $ticket_id, $prefix.'date_start', true );
						$end_date 	= get_post_meta( $ticket_id, $prefix.'date_end', true );
						$qr_code 	= get_post_meta( $ticket_id, $prefix.'qr_code', true );
						$customer 	= get_post_meta( $ticket_id, $prefix.'name_customer', true );
						$arr_venue 	= get_post_meta( $ticket_id, $prefix.'venue', true );
						$address 	= get_post_meta( $ticket_id, $prefix.'address', true );
						$venue 		= is_array( $arr_venue ) ? implode( ", ", $arr_venue ) : $arr_venue;
					?>
						<tr>
							<th scope="row" class="check-column">
								<input 
									type="checkbox"
									name="ticket_id[]"
									value="<?php echo esc_attr( $ticket_id ); ?>"
								/>
							</th>
							<th class="ticket_number">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $ticket_id ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_type">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( get_the_title( $ticket_id ) ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_status">
		                		<?php echo esc_html( $status ); ?>
		                	</th>
		                	<th class="start_date">
		                		<?php echo esc_html( date_i18n( $date_format, $start_date ) . ' - ' . date_i18n( $time_format, $start_date ) ); ?>
		                	</th>
		                	<th class="end_date">
		                		<?php echo esc_html( date_i18n( $date_format, $end_date ) . ' - ' . date_i18n( $time_format, $end_date ) ); ?>
		                	</th>
		                	<th class="ticket_qr_code">
		                		<?php echo esc_html( $qr_code ); ?>
		                	</th>
		                	<th class="customer_name">
		                		<?php echo esc_html( $customer ); ?>
		                	</th>
		                	<th class="customer_address">
		                		<?php if ( $venue ): ?>
		                			<?php echo sprintf( esc_html__( 'Venue: %s', 'eventlist' ), esc_html( $venue ) ); ?>
		                			<br>
		                		<?php endif; ?>
		                		<?php if ( $address ): ?>
		                			<?php echo sprintf( esc_html__( 'Address: %s', 'eventlist' ), esc_html( $address ) ); ?>
		                			<br>
		                		<?php endif; ?>
		                	</th>
		                	<th class="event">
		                		<a href="<?php echo esc_url( get_edit_post_link( $event_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $event_name ); ?>
		                		</a>
		                	</th>
		                	<th class="booking_id">
		                		<a href="<?php echo esc_url( get_edit_post_link( $booking_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $booking_id ); ?>
		                		</a>
		                	</th>
						</tr>
					<?php }
				} else {
					?>
					<tr class="no-items">
	            		<td class="colspanchange" colspan="11">
	            			<?php esc_html_e( 'No items found.', 'eventlist' ); ?>
	            		</td>
	            	</tr>
					<?php
				}
				wp_reset_postdata();

				$result = ob_get_contents();
				ob_end_clean();

				$max_pages 		= $tickets->max_num_pages;
				$total_items 	= $tickets->found_posts;

				if ( $max_pages > 1 ) {
					ob_start();
				?>
	        		<span 
	        			class="btn-pagination el-first disable"
	        			title="<?php esc_attr_e( 'First page', 'eventlist' ); ?>"
	        			data-paged="1">«</span>
	        		<span 
	        			class="btn-pagination el-prev disable"
	        			title="<?php esc_attr_e( 'Previous page', 'eventlist' ); ?>"
	        			data-paged="1">‹</span>
	        		<span 
	        			class="el-current"
	        			title="<?php esc_attr_e( 'Current page', 'eventlist' ); ?>"
	        			data-paged="1">1</span>
	        		<span class="text-pagination" title="<?php esc_attr_e( 'Current page', 'eventlist' ); ?>">
	        			<?php esc_html_e( 'of', 'eventlist' ); ?>
	        			<span 
	        				class="total-page" 
	        				title="<?php esc_attr_e( 'Total page', 'eventlist' ); ?>"
	        				data-max-pages="<?php echo esc_attr( $max_pages ); ?>">
	        				<?php echo esc_html( $max_pages ); ?>
	        			</span>
	        		</span>
	        		<span 
	        			class="btn-pagination el-next"
	        			title="<?php esc_attr_e( 'Next page', 'eventlist' ); ?>"
	        			data-paged="2">›</span>
	        		<span 
	        			class="btn-pagination el-last"
	        			title="<?php esc_attr_e( 'Last page', 'eventlist' ); ?>"
	        			data-paged="<?php echo esc_attr( $max_pages ); ?>">»</span>
				<?php
					$pagination = ob_get_contents();
					ob_end_clean();
				}
			}

			echo json_encode( array( 'result' => $result, 'count' => $count, 'pagination' => $pagination ) );

			wp_die();
		}

		public function el_replace_ticket_date_pagination() {
			$event_id 	= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$replaced 	= isset( $_POST['replaced'] ) ? sanitize_text_field( $_POST['replaced'] ) : '';
			$per_page 	= isset( $_POST['posts_per_page'] ) && absint( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 25;
			$paged 		= isset( $_POST['paged'] ) && absint( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

			$result 	= '';
			$count 		= sprintf( esc_html__( '%s items', 'eventlist' ), 0 );

			if ( $event_id ) {
				$prefix = OVA_METABOX_EVENT;
				$start 	= $end = '';

				// Start Date
				if ( $start_date && $start_time ) {
					$start = strtotime( $start_date . ' ' . $start_time );
				} elseif ( $start_date ) {
					$start = strtotime( $start_date );
				} else {
					$start = false;
				}

				// End Date
				if ( $end_date && $end_time ) {
					$end = strtotime( $end_date . ' ' . $end_time );
				} elseif ( $end_date ) {
					$end = strtotime( $end_date );
				} else {
					$end = false;
				}

				$args = array(
					'post_type' 		=> 'el_tickets',
					'post_status' 		=> 'publish',
					'posts_per_page' 	=> $per_page,
					'paged' 			=> $paged,
					'meta_query' 		=> array(
						array(
							'key' 		=> $prefix . 'event_id',
							'value' 	=> $event_id,
							'compare' 	=> '='
						)
					)
				);

				if ( $start ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_start',
						'value' 	=> $start,
						'compare' 	=> '='
					);
				}

				if ( $end ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_end',
						'value' 	=> $end,
						'compare' 	=> '='
					);
				}

				if ( $replaced && $replaced === 'on' ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'replace_date_status',
						'value' 	=> '',
						'compare' 	=> '!='
					);
				}

				$tickets = new WP_Query( $args );

				ob_start();

				if ( $tickets->have_posts() ) {
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');

					if ( count( $tickets->posts ) == 1 ) {
						$count = sprintf( esc_html__( '%s item', 'eventlist' ), count( $tickets->posts ) );
					} else {
						$count = sprintf( esc_html__( '%s items', 'eventlist' ), count( $tickets->posts ) );
					}

					while ( $tickets->have_posts() ) {
						$tickets->the_post();

						$ticket_id 	= get_the_id();
						$event_name = get_post_meta( $ticket_id, $prefix.'name_event', true );
						$booking_id = get_post_meta( $ticket_id, $prefix.'booking_id', true );
						$status 	= get_post_meta( $ticket_id, $prefix.'ticket_status', true );
						$start_date = get_post_meta( $ticket_id, $prefix.'date_start', true );
						$end_date 	= get_post_meta( $ticket_id, $prefix.'date_end', true );
						$qr_code 	= get_post_meta( $ticket_id, $prefix.'qr_code', true );
						$customer 	= get_post_meta( $ticket_id, $prefix.'name_customer', true );
						$arr_venue 	= get_post_meta( $ticket_id, $prefix.'venue', true );
						$address 	= get_post_meta( $ticket_id, $prefix.'address', true );
						$venue 		= is_array( $arr_venue ) ? implode( ", ", $arr_venue ) : $arr_venue;
					?>
						<tr>
							<th scope="row" class="check-column">
								<input 
									type="checkbox"
									name="ticket_id[]"
									value="<?php echo esc_attr( $ticket_id ); ?>"
								/>
							</th>
							<th class="ticket_number">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $ticket_id ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_type">
		                		<a href="<?php echo esc_url( get_edit_post_link( $ticket_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( get_the_title( $ticket_id ) ); ?>
		                		</a>
		                	</th>
		                	<th class="ticket_status">
		                		<?php echo esc_html( $status ); ?>
		                	</th>
		                	<th class="start_date">
		                		<?php echo esc_html( date_i18n( $date_format, $start_date ) . ' - ' . date_i18n( $time_format, $start_date ) ); ?>
		                	</th>
		                	<th class="end_date">
		                		<?php echo esc_html( date_i18n( $date_format, $end_date ) . ' - ' . date_i18n( $time_format, $end_date ) ); ?>
		                	</th>
		                	<th class="ticket_qr_code">
		                		<?php echo esc_html( $qr_code ); ?>
		                	</th>
		                	<th class="customer_name">
		                		<?php echo esc_html( $customer ); ?>
		                	</th>
		                	<th class="customer_address">
		                		<?php if ( $venue ): ?>
		                			<?php echo sprintf( esc_html__( 'Venue: %s', 'eventlist' ), esc_html( $venue ) ); ?>
		                			<br>
		                		<?php endif; ?>
		                		<?php if ( $address ): ?>
		                			<?php echo sprintf( esc_html__( 'Address: %s', 'eventlist' ), esc_html( $address ) ); ?>
		                			<br>
		                		<?php endif; ?>
		                	</th>
		                	<th class="event">
		                		<a href="<?php echo esc_url( get_edit_post_link( $event_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $event_name ); ?>
		                		</a>
		                	</th>
		                	<th class="booking_id">
		                		<a href="<?php echo esc_url( get_edit_post_link( $booking_id, 'edit' ) ); ?>" target="_blank">
		                			<?php echo esc_html( $booking_id ); ?>
		                		</a>
		                	</th>
						</tr>
					<?php }
				} else {
					?>
					<tr class="no-items">
	            		<td class="colspanchange" colspan="11">
	            			<?php esc_html_e( 'No items found.', 'eventlist' ); ?>
	            		</td>
	            	</tr>
					<?php
				}
				wp_reset_postdata();

				$result = ob_get_contents();
				ob_end_clean();
			}

			echo json_encode( array( 'result' => $result, 'count' => $count ) );

			wp_die();
		}

		public function el_replace_ticket_date_export_email() {
			$event_id 	= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$replaced 	= isset( $_POST['replaced'] ) ? sanitize_text_field( $_POST['replaced'] ) : '';

			if ( $event_id ) {
				$prefix = OVA_METABOX_EVENT;
				$start 	= $end = '';

				$csv_row[0][] = esc_html__( 'All Customer Emails', 'eventlist' );

				// Start Date
				if ( $start_date && $start_time ) {
					$start = strtotime( $start_date . ' ' . $start_time );
				} elseif ( $start_date ) {
					$start = strtotime( $start_date );
				} else {
					$start = false;
				}

				// End Date
				if ( $end_date && $end_time ) {
					$end = strtotime( $end_date . ' ' . $end_time );
				} elseif ( $end_date ) {
					$end = strtotime( $end_date );
				} else {
					$end = false;
				}

				$args = array(
					'post_type' 		=> 'el_tickets',
					'post_status' 		=> 'publish',
					'posts_per_page' 	=> -1,
					'meta_query' 		=> array(
						array(
							'key' 		=> $prefix . 'event_id',
							'value' 	=> $event_id,
							'compare' 	=> '='
						)
					),
					'fields' 			=> 'ids',
				);

				if ( $start ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_start',
						'value' 	=> $start,
						'compare' 	=> '='
					);
				}

				if ( $end ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_end',
						'value' 	=> $end,
						'compare' 	=> '='
					);
				}

				if ( $replaced && $replaced === 'on' ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'replace_date_status',
						'value' 	=> '',
						'compare' 	=> '!='
					);
				}

				$tickets = get_posts( $args );

				if ( ! empty( $tickets ) && is_array( $tickets ) ) {
					$email_exists = [];
					$i = 1;

					foreach( $tickets as $ticket_id ) {
						$email_customer = get_post_meta( $ticket_id, $prefix.'email_customer', true );

						if ( $email_customer && ! in_array( $email_customer, $email_exists ) ) {
							$csv_row[$i][] = $email_customer;

							array_push( $email_exists, $email_customer );

							$i++;
						}
					}
				}

				echo json_encode( $csv_row );
			}

			wp_die();
		}

		public function el_replace_ticket_date_send_email() {
			$event_id 	= isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '';
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$start_time = isset( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$end_time 	= isset( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : '';
			$replaced 	= isset( $_POST['replaced'] ) ? sanitize_text_field( $_POST['replaced'] ) : '';
			$subject 	= isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
			$from_name 	= isset( $_POST['from_name'] ) ? sanitize_text_field( $_POST['from_name'] ) : '';
			$send_from 	= isset( $_POST['send_from'] ) ? sanitize_text_field( $_POST['send_from'] ) : '';
			$content 	= isset( $_POST['content'] ) ? $_POST['content'] : '';
			$result 	= $error = '';
			$mail_to 	= [];

			if ( $event_id ) {
				$prefix = OVA_METABOX_EVENT;
				$start 	= $end = '';

				// Start Date
				if ( $start_date && $start_time ) {
					$start = strtotime( $start_date . ' ' . $start_time );
				} elseif ( $start_date ) {
					$start = strtotime( $start_date );
				} else {
					$start = false;
				}

				// End Date
				if ( $end_date && $end_time ) {
					$end = strtotime( $end_date . ' ' . $end_time );
				} elseif ( $end_date ) {
					$end = strtotime( $end_date );
				} else {
					$end = false;
				}

				$args = array(
					'post_type' 		=> 'el_tickets',
					'post_status' 		=> 'publish',
					'posts_per_page' 	=> -1,
					'meta_query' 		=> array(
						array(
							'key' 		=> $prefix . 'event_id',
							'value' 	=> $event_id,
							'compare' 	=> '='
						)
					),
					'fields' 			=> 'ids',
				);

				if ( $start ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_start',
						'value' 	=> $start,
						'compare' 	=> '='
					);
				}

				if ( $end ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'date_end',
						'value' 	=> $end,
						'compare' 	=> '='
					);
				}

				if ( $replaced && $replaced === 'on' ) {
					$args['meta_query'][] = array(
						'key' 		=> $prefix . 'replace_date_status',
						'value' 	=> '',
						'compare' 	=> '!='
					);
				}

				$tickets = get_posts( $args );

				if ( ! empty( $tickets ) && is_array( $tickets ) ) {
					foreach( $tickets as $ticket_id ) {
						$email_customer = get_post_meta( $ticket_id, $prefix.'email_customer', true );

						if ( $email_customer && ! in_array( $email_customer, $mail_to ) ) {
							array_push( $mail_to, $email_customer );
						}
					}
				}
			}

			if ( ! empty( $mail_to ) && is_array( $mail_to ) ) {
				$mail_to = implode( ",", $mail_to );

				if ( $subject && $from_name && $send_from ) {
					$send_result = el_ticket_replace_date_send_mail( $mail_to, $send_from, $subject, $from_name, $content );

					if ( $send_result ) {
						$result = esc_html__( 'Sent successfully.', 'eventlist' );
					}
				}
			} else {
				$error = esc_html__( 'No email found.', 'eventlist' );
			}

			echo json_encode( array( 'result' => $result, 'error' => $error ) );

			wp_die();
		}

		public function el_update_event_status(){

			$post_data 			= $_POST;
			$response 			= [];
			$response['status'] = 'error';
			$response['mess'] 	= '';

			if ( ! $post_data['nonce'] || ! wp_verify_nonce( $post_data['nonce'], 'el_update_event_status' ) ) {
				$response['mess'] = __( 'Invalid nonce, please refresh your screen and try again.', 'eventlist' );
				
				wp_send_json( $response );
			}

			$total_event = el_get_total_event();
			$chunk_record = apply_filters( 'el_cron_job_chunk_record', 100 );
			$offset = isset( $post_data['offset'] ) ? sanitize_text_field( $post_data['offset'] ) : 0;
			$posts_per_page = -1;
			if ( $total_event - $chunk_record > 0 ) {
				$posts_per_page = $chunk_record;
			}

			$args = array(
				'post_type' => 'event',
				'posts_per_page' => $posts_per_page,
				'post_status' => 'publish',
				'fields' => 'ids',
			);

			if ( $offset != 0 ) {
				$args['offset'] = (int) $offset;
			}
			$events = get_posts( $args );
			$found_posts = count( $events );

			if ( $found_posts > 0 ) {
				foreach ( $events as $event_id ) {
					
					$end_date_time 		= (int) get_post_meta( $event_id, OVA_METABOX_EVENT.'end_date_str', true );
					$start_date_time 	= (int) get_post_meta( $event_id, OVA_METABOX_EVENT.'start_date_str', true );
					$option_calendar 	= get_post_meta( $event_id, OVA_METABOX_EVENT.'option_calendar', true );
					$current_time 		= (int) current_time( 'timestamp' );
					$event_status 		= '';

					if ( $end_date_time < $current_time ) {
						$event_status = 'past';
					} elseif ( $end_date_time > $current_time && ( $start_date_time >  $current_time || $option_calendar == 'auto' ) ) {
						$event_status = 'upcoming';
					} elseif ( $start_date_time <= $current_time && $end_date_time >= $current_time ) {
						$event_status = 'opening';
					}

					update_post_meta( $event_id, OVA_METABOX_EVENT.'event_status', $event_status );
				}
			}

			$event_updated = (int) $offset + $found_posts;

			if ( $total_event - $event_updated == 0 ) {
				$response['status'] = 'success';

				do_action( 'el_after_update_event_status_manually' );
			} else {
				$response['status'] = 'pending';
				$response['offset'] = $event_updated;
			}
			$response['mess'] = sprintf( __( '%1$s/%2$s events have been updated.', 'eventlist' ),$event_updated,$total_event );

			wp_send_json( $response );
		}

		

	}

	new El_Admin_Ajax();

}
?>