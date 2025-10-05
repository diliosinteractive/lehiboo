<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EL_Ticket', false ) ) {

	class EL_Ticket{


		protected static $_instance = null;

		protected $_prefix = OVA_METABOX_EVENT;

		/**
		 * Constructor
		 */
		public function __construct(){

			require_once EL_PLUGIN_INC . 'ticket/mpdf/vendor/autoload.php';

			if( apply_filters( 'el_filter_attach_qrcode_mail', true ) ){
				require_once EL_PLUGIN_INC.'ticket/qrcode/qrcode.class.php';
			}
			
			require_once EL_PLUGIN_INC.'ticket/class-el-pdf.php';
			
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function add_ticket( $booking_id = null ) {
			if ( $booking_id == null ) return false;
			$status_booking = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'status', true);

			if ( $status_booking != 'Completed' ) return false;

			$id_event 			= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'id_event', true);
			$idcal 				= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'id_cal', true);
			$name_customer 		= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'name', true);
			$phone_customer 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'phone', true);
			$email_customer 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'email', true);
			$address_customer 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'address', true);
			$multiple_ticket 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'multiple_ticket', true);
			$data_customers 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'data_customers', true);
			$cart 				= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'cart', true);
			$seat_option 		= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'seat_option', true);

			$event_obj = el_get_event( $id_event );
			
			$list_title_ticket = $list_url_image_ticket = $list_color_ticket = $list_price_ticket = $list_color_label_ticket = $list_color_content_ticket = $list_desc_ticket = $list_private_desc_title = [];

			// Get data from Event
			$list_type_ticket 		= get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'ticket', true);
			$list_type_ticket_map 	= get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'ticket_map', true);
			if ( empty( $seat_option ) ) {
				$seat_option 		= get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'seat_option', true);
			}
			
			
			$event_type = get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'event_type', true);
			$venue 		= get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'venue', true);
			$address 	= get_post_meta( $event_obj->ID, OVA_METABOX_EVENT . 'address', true);

			if ( $event_type == 'online' ) {
				$address = esc_html__( 'Online', 'eventlist' );
			}

			$name_event = $event_obj->post_title;

			if ( $seat_option != 'map') {
				if ( !empty($list_type_ticket) && is_array($list_type_ticket) ) {
					foreach ( $list_type_ticket as $ticket ) {
						$list_title_ticket[$ticket['ticket_id']] 			= $ticket['name_ticket'];
						$list_url_image_ticket[$ticket['ticket_id']] 		= $ticket['image_ticket'];
						$list_color_ticket[$ticket['ticket_id']] 			= $ticket['color_ticket'];
						$list_color_label_ticket[$ticket['ticket_id']] 		= $ticket['color_label_ticket'];
						$list_color_content_ticket[$ticket['ticket_id']] 	= $ticket['color_content_ticket'];
						$list_desc_ticket[$ticket['ticket_id']] 			= $ticket['desc_ticket'];
						$list_private_desc_ticket[$ticket['ticket_id']] 	= $ticket['private_desc_ticket'];
						$list_price_ticket[$ticket['ticket_id']] 			= ! empty($ticket['price_ticket']) ? $ticket['price_ticket'] : 0;
					}
				}
			} else {
				if ( ! empty( $list_type_ticket_map ) && is_array( $list_type_ticket_map ) ) {
					$list_title_ticket[0] 			= esc_html__('Map', 'eventlist');
					$list_url_image_ticket[0] 		= $list_type_ticket_map['image_ticket'];
					$list_color_ticket[0] 			= $list_type_ticket_map['color_ticket'];
					$list_color_label_ticket[0] 	= $list_type_ticket_map['color_label_ticket'];
					$list_color_content_ticket[0] 	= $list_type_ticket_map['color_content_ticket'];
					$list_desc_ticket[0] 			= $list_type_ticket_map['desc_ticket'];
					$list_private_desc_ticket[0] 	= $list_type_ticket_map['private_desc_ticket_map'];
				}
			}
			
			$post_data['post_type'] = 'el_tickets';
			$post_data['post_status'] = 'publish';

			// Add Author of event for Ticket
			$booking_obj = get_post( $booking_id );
			$booking_author_id = $booking_obj->post_author;
			$post_data['post_author'] = $booking_author_id;

			$date_event 			= self::el_get_calendar_date_time($id_event, $idcal);
			$list_qty_ticket 		= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true);
			$list_seat_in_booking 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'list_seat_book', true);
			$data_checkout_field 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'data_checkout_field', true);
			$arr_seat = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'arr_seat', true );
			$arr_area = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'arr_area', true );

			$extra_service = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'extra_service', true);

			if ( empty( $arr_seat ) ) $arr_seat = [];
			if ( empty( $arr_area ) ) $arr_area = [];
			
			$arr_list_id_ticket = [];

			if ( ! empty( $list_qty_ticket ) && is_array( $list_qty_ticket ) ) {
				foreach ( $list_qty_ticket as $id => $qty ) {
					for( $i = 0 ; $i < $qty; $i++ ) {
						$mix_id = $booking_id . '-ova-el-' . $id_event . '-ova-el-' . $id . '-ova-el-' . $i . EL()->options->general->get('serect_key_qrcode');
						$qr_code = md5( $mix_id );
						
						if ( $seat_option != 'map' ) {
							$post_data['post_title'] = $list_title_ticket[strtolower($id)];

							if (  'yes' === $multiple_ticket && !empty( $data_customers ) && is_array( $data_customers ) ) {
								$data_info = isset( $data_customers[$id] ) && $data_customers[$id] ? $data_customers[$id] : array();

								if ( ! empty( $data_info ) && is_array( $data_info ) ) {
									$info = isset( $data_info[$i] ) && $data_info[$i] ? $data_info[$i] : array();
									
									if ( ! empty( $info ) && is_array( $info ) ) {
										$first_name 			= isset( $info['first_name'] ) ? $info['first_name'] : '';
										$last_name 				= isset( $info['last_name'] ) ? $info['last_name'] : '';
										$name_customer 			= $first_name . ' ' . $last_name;
										$email_customer 		= isset( $info['email'] ) ? $info['email'] : '';
										$phone_customer 		= isset( $info['phone'] ) ? $info['phone'] : '';
										$address_customer 		= isset( $info['address'] ) ? $info['address'] : '';
										$data_checkout_field 	= isset( $info['checkout_fields'] ) ? $info['checkout_fields'] : array();
										if ( $data_checkout_field ) {
											$data_checkout_field = json_encode( $data_checkout_field, JSON_UNESCAPED_UNICODE );
										}
									}
								}
							}

							$extra_service_items = isset( $extra_service[$id][$i] ) ? $extra_service[$id][$i] : '';

							$meta_input = array(
								$this->_prefix.'booking_id' 			=> $booking_id,
								$this->_prefix.'event_id' 				=> $id_event,
								$this->_prefix.'name_event' 			=> $name_event,
								$this->_prefix.'qr_code' 				=> $qr_code,
								$this->_prefix.'name_customer' 			=> $name_customer,
								$this->_prefix.'phone_customer' 		=> $phone_customer,
								$this->_prefix.'email_customer' 		=> $email_customer,
								$this->_prefix.'address_customer' 		=> $address_customer,
								$this->_prefix.'venue' 					=> $venue,
								$this->_prefix.'address' 				=> $address,
								$this->_prefix.'data_checkout_field' 	=> $data_checkout_field,
								$this->_prefix.'seat' 					=> isset( $list_seat_in_booking[$id][$i] ) ? $list_seat_in_booking[$id][$i] : '',
								$this->_prefix.'date_start' 			=> $date_event['start_time'],
								$this->_prefix.'date_end' 				=> $date_event['end_time'],
								$this->_prefix.'img' 					=> $list_url_image_ticket[$id],
								$this->_prefix.'color_ticket' 			=> $list_color_ticket[$id],
								$this->_prefix.'color_label_ticket' 	=> $list_color_label_ticket[$id],
								$this->_prefix.'color_content_ticket' 	=> $list_color_content_ticket[$id],
								$this->_prefix.'price_ticket' 			=> $list_price_ticket[$id],
								$this->_prefix.'desc_ticket' 			=> $list_desc_ticket[$id],
								$this->_prefix.'private_desc_ticket' 	=> $list_private_desc_ticket[$id],
								$this->_prefix.'ticket_status' 			=> '',
								$this->_prefix.'checkin_time' 			=> '',
								$this->_prefix.'ticket_id_event' 		=> $id,
								$this->_prefix.'extra_service' 			=> $extra_service_items,
							);
						} else {
							$post_data['post_title'] = $list_title_ticket[0];

							if (  'yes' === $multiple_ticket && !empty( $data_customers ) && is_array( $data_customers ) ) {
								$data_info = isset( $data_customers[$id] ) && $data_customers[$id] ? $data_customers[$id] : array();

								if ( ! empty( $data_info ) && is_array( $data_info ) ) {
									if ( in_array( $id, $arr_area ) ) {
										$info = isset( $data_info[$i] ) && $data_info[$i] ? $data_info[$i] : array();

										if ( ! empty( $info ) && is_array( $info ) ) {
											$first_name 			= isset( $info['first_name'] ) ? $info['first_name'] : '';
											$last_name 				= isset( $info['last_name'] ) ? $info['last_name'] : '';
											$name_customer 			= $first_name . ' ' . $last_name;
											$email_customer 		= isset( $info['email'] ) ? $info['email'] : '';
											$phone_customer 		= isset( $info['phone'] ) ? $info['phone'] : '';
											$address_customer 		= isset( $info['address'] ) ? $info['address'] : '';
											$data_checkout_field 	= isset( $info['checkout_fields'] ) ? $info['checkout_fields'] : array();
											if ( $data_checkout_field ) {
												$data_checkout_field = json_encode( $data_checkout_field, JSON_UNESCAPED_UNICODE );
											}
										}
									} else {
										$first_name 			= isset( $data_info['first_name'] ) ? $data_info['first_name'] : '';
										$last_name 				= isset( $data_info['last_name'] ) ? $data_info['last_name'] : '';
										$name_customer 			= $first_name . ' ' . $last_name;
										$email_customer 		= isset( $data_info['email'] ) ? $data_info['email'] : '';
										$phone_customer 		= isset( $data_info['phone'] ) ? $data_info['phone'] : '';
										$address_customer 		= isset( $data_info['address'] ) ? $data_info['address'] : '';
										$data_checkout_field 	= isset( $data_info['checkout_fields'] ) ? $data_info['checkout_fields'] : array();
										if ( $data_checkout_field ) {
											$data_checkout_field = json_encode( $data_checkout_field, JSON_UNESCAPED_UNICODE );
										}
									}
								}
							}

							/* add person type */
							$arr_person_type = [];
							$arr_range_person = [];
							$person_type = '';
							
							if ( ! empty( $cart ) && is_array( $cart ) ) {

								foreach ( $cart as $key => $item ) {

									if ( $item['id'] === $id ) {

										if ( isset( $item['data_person'] ) ) {
											$data_person = $item['data_person'];
											$person_qty = (int) $item['person_qty'];
											$person_qty_range = range( 0, $person_qty - 1 );
											$before_qty = 0;
											foreach ( $data_person as $k => $val ) {
												$arr_person_type[$k] = $val['name'];
												if ( $before_qty == 0 ) {
													$arr_range_person[$k] = array_slice($person_qty_range,0 ,(int) $val['qty'] );
												} else {
													$arr_range_person[$k] = array_slice($person_qty_range, $before_qty ,(int) $val['qty'] );
												}
												$before_qty += (int) $val['qty'];
											}
										}

										if ( ! empty( $item['person_type'] ) ) {
											$person_type = $item['person_type'];
										}

									}
								}
							}

							

							if ( ! empty( $arr_person_type ) && ! empty( $arr_range_person ) ) {
								foreach ( $arr_range_person as $key => $range ) {
									if ( in_array($i, $range) ) {
										if ( array_key_exists($key, $arr_person_type) ) {
											$person_type = $arr_person_type[$key];
										}
									}
								}
							}
							/* end add person type */
							$extra_service_items = isset( $extra_service[$id][$i] ) ? $extra_service[$id][$i] : '';

							$meta_input = array(
								$this->_prefix.'booking_id' 			=> $booking_id,
								$this->_prefix.'event_id' 				=> $id_event,
								$this->_prefix.'name_event' 			=> $name_event,
								$this->_prefix.'qr_code' 				=> $qr_code,
								$this->_prefix.'name_customer' 			=> $name_customer,
								$this->_prefix.'phone_customer' 		=> $phone_customer,
								$this->_prefix.'email_customer' 		=> $email_customer,
								$this->_prefix.'address_customer' 		=> $address_customer,
								$this->_prefix.'venue' 					=> $venue,
								$this->_prefix.'address' 				=> $address,
								$this->_prefix.'data_checkout_field' 	=> $data_checkout_field,
								$this->_prefix.'seat' 					=> $id ? $id : '',
								$this->_prefix.'date_start' 			=> $date_event['start_time'],
								$this->_prefix.'date_end' 				=> $date_event['end_time'],
								$this->_prefix.'img' 					=> $list_url_image_ticket[0],
								$this->_prefix.'color_ticket' 			=> $list_color_ticket[0],
								$this->_prefix.'color_label_ticket' 	=> $list_color_label_ticket[0],
								$this->_prefix.'color_content_ticket' 	=> $list_color_content_ticket[0],
								$this->_prefix.'desc_ticket' 			=> $list_desc_ticket[0],
								$this->_prefix.'private_desc_ticket' 	=> $list_private_desc_ticket[0],
								$this->_prefix.'ticket_status' 			=> '',
								$this->_prefix.'checkin_time' 			=> '',
								$this->_prefix.'ticket_id_event' 		=> $id,
								$this->_prefix.'extra_service' 			=> $extra_service_items,
							);

							if ( $person_type != '' ) {
								$meta_input[$this->_prefix.'person_type'] = $person_type;
							}
						}

						$post_data['meta_input'] = apply_filters( 'el_ticket_metabox_input', $meta_input );
						$ticket_id = wp_insert_post( $post_data, true );
						// Add Meta Ticket ID
						$metabox_ticket_id = array(
							'ID'           => $ticket_id,
							'meta_input' => array(
								$this->_prefix.'ticket_id' => $ticket_id
							)
						);

						// Update the post into the database
						wp_update_post( $metabox_ticket_id );

						$arr_list_id_ticket[] = $ticket_id;
					}
				}
			}

			return $arr_list_id_ticket;
		}

		public function el_get_calendar_date_time( $id_event, $id_cal ){
			if( !$id_event || !$id_cal ) ['start_time' => 0, 'end_time' => 0];

			$list_calendar = get_arr_list_calendar_by_id_event($id_event);

			
			if( is_array($list_calendar) && !empty($list_calendar) ){
				foreach ( $list_calendar as $cal ) {
					if( $cal['calendar_id'] == $id_cal ) {
						$date = $cal['date'];
						$end_date = (isset($cal['end_date']) && $cal['end_date']) ? $cal['end_date'] : $cal['date'];
						$time_start = $cal['start_time'];
						$end_time = $cal['end_time'];
						break;
					}
				}
			}
			$total_time_start = el_get_time_int_by_date_and_hour($date, $time_start);
			$total_time_end = el_get_time_int_by_date_and_hour($end_date, $end_time);

			return ['start_time' => $total_time_start, 'end_time' => $total_time_end];
		}


		public function make_pdf_ticket_by_booking_id ( $booking_id = null, $default_ticket_id = null ) {
			if ( $booking_id == null ) return [];

			$args = array(
				'post_type' 		=> 'el_tickets',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> '-1',
				'meta_query' 		=> array(
					'relation' => 'AND',
					array(
						'key' 		=> $this->_prefix . 'booking_id',
						'value' 	=> $booking_id,
						'compare' 	=> '='
					),
				)
			);

			$scan_qr_code_with = EL()->options->general->get("scan_qr_code_with","app");
			$tickets 	= new WP_Query( $args );
			$ticket_pdf = array();
			$k = 0;

			if ( $tickets->have_posts() ): while( $tickets->have_posts() ): $tickets->the_post();
				$ticket_id = get_the_id();

				if ( $default_ticket_id && $default_ticket_id != $ticket_id ) continue;

				if ( apply_filters( 'el_filter_attach_pdf_mail', true ) ) {
					$pdf = new EL_PDF();

					$ticket_pdf[$k] = $pdf->make_pdf_ticket( $ticket_id );	
					$k++;
				}

				if ( apply_filters( 'el_filter_attach_qrcode_mail', true ) ) {
					$qrcode_str 	= get_post_meta( $ticket_id, OVA_METABOX_EVENT . 'qr_code', true );
					$qr_code_with 	= $qrcode_str;
					if ( $scan_qr_code_with === "web" ) {
						$url = add_query_arg( array(
							'post_type' 	=> 'event',
							'check_qrcode' 	=> $qrcode_str,
						), home_url( '/' ) );
						$qr_code_with = $url;
					}
					$qrcode 	= new QRcode( $qr_code_with, 'H' );
					$qr_image 	= WP_CONTENT_DIR.'/uploads/ticket_qr_'.$qrcode_str.'.png';
					$qrcode_size = apply_filters( 'el_qrcode_size', 100 );
					$qrcode->displayPNG( $qrcode_size, array(255,255,255), array(0,0,0), $qr_image , 0);
					$ticket_pdf[$k] = $qr_image;
					$k++;
				}

			endwhile; endif; wp_reset_postdata();

			if ( apply_filters( 'el_filter_attach_pdf_invoice_mail', true ) ) {
				if ( EL()->options->invoice->get('invoice_mail_enable', 'no' ) === 'yes' ) {
					$booking_pdf = EL_Booking::instance()->el_make_pdf_invoice_by_booking_id( $booking_id );
				}
			}

			return $ticket_pdf;
		}

		public function make_pdf_ticket_by_id( $ticket_id ){

			$pdf = new EL_PDF();

			$ticket_pdf = $pdf->make_pdf_ticket( $ticket_id );	

			return $ticket_pdf;
		}

		public  function get_list_ticket_by_id_event( $id_event = null ) {
			if ($id_event == null) return;
			$agrs = [
				'post_type' => 'el_tickets',
				'post_status' => 'publish',
				"meta_query" => [
					'relation' => 'AND',
					[
						"key" => OVA_METABOX_EVENT . 'event_id',
						"value" => $id_event,
						'compare' => 'IN',
					],
				],
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
			];

			return get_posts( $agrs );

		}

		public static function get_ticket_ids_by_id_event( $id_event = null ){
			if ( empty( $id_event ) ) {
				return array();
			}

			$agrs = [
				'post_type' => 'el_tickets',
				'post_status' => 'publish',
				"meta_query" => [
					'relation' => 'AND',
					[
						"key" => OVA_METABOX_EVENT . 'event_id',
						"value" => $id_event,
					],
				],
				'posts_per_page' 	=> -1, 
				'numberposts' 		=> -1,
				'nopaging' 			=> true,
				'fields' 			=> 'ids',
			];

			return get_posts( $agrs );
		}

		public static function get_ticket_pagination_by_id_event( $id_event = null, $keyword = null ){
			$paged 		= get_query_var('paged') ? get_query_var('paged') : 1;

			$agrs_basic = $agrs_qrcode = array();
			
			$agrs_basic = array(
				'post_type' => 'el_tickets',
				'post_status' => 'publish',
				"meta_query" => array(
					'relation' => 'AND',
					array(
						"key" => OVA_METABOX_EVENT . 'event_id',
						"value" => $id_event,
					),
				),
				'paged' => $paged,
			);

			if( $keyword ){
				$agrs_qrcode = array(
					"meta_query" => array(
						array(
							'relation' => 'OR',
							array(
								"key" => OVA_METABOX_EVENT . 'qr_code',
								"value" => $keyword,
								'compare' => 'LIKE'
							),
							array(
								"key" => OVA_METABOX_EVENT . 'name_customer',
								"value" => $keyword,
								'compare' => 'LIKE'
							),
						),
					),
				);
			}

			$agrs = array_merge_recursive( $agrs_basic, $agrs_qrcode );

			return new WP_Query( $agrs );
		}

		public  function get_list_ticket_by_id_booking( $id_booking = null ) {
			if ($id_booking == null) return;
			$agrs = [
				'post_type' 	=> 'el_tickets',
				'post_status' 	=> 'publish',
				"meta_query" => [
					'relation' => 'AND',
					[
						"key" 	=> OVA_METABOX_EVENT . 'booking_id',
						"value" => $id_booking,
					],
				],
				'posts_per_page' => -1, 
				'numberposts' => -1,
				'nopaging' => true,
				'fields' => 'ids',
			];

			return get_posts( $agrs );
		}

		public  function get_number_ticket_free_by_id_event ($id_event = null) {
			if ($id_event == null) return;
			$agrs = [
				'post_type' => 'el_tickets',
				'post_status' => 'publish',
				"meta_query" => [
					'relation' => 'AND',
					[
						"key" => OVA_METABOX_EVENT . 'event_id',
						"value" => $id_event,
					],
					[
						"key" => OVA_METABOX_EVENT . 'price_ticket',
						"value" => 0,
					],
				],
				'posts_per_page' => -1, 
				'numberposts' => -1,
				'nopaging' => true,
			];

			$tickets = get_posts( $agrs );
			return count($tickets);

		}

		public  function get_number_ticket_free_by_id_booking ($id_booking = null) {
			if ($id_booking == null) return;
			$agrs = [
				'post_type' => 'el_tickets',
				'post_status' => 'publish',
				"meta_query" => [
					'relation' => 'AND',
					[
						"key" => OVA_METABOX_EVENT . 'booking_id',
						"value" => $id_booking,
					],
					[
						"key" => OVA_METABOX_EVENT . 'price_ticket',
						"value" => 0,
					],
				],
				'posts_per_page' => -1, 
				'numberposts' => -1,
				'nopaging' => true,
				'fields' => 'ids',
			];

			$tickets = get_posts( $agrs );
			return count($tickets);

		}

		public  function get_number_ticket_checkin ($id_event = null) {
			if ($id_event == null) return;
			$args = array(
				'post_type' 		=> 'el_tickets',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields'			=> 'ids',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						"key" 		=> OVA_METABOX_EVENT . 'event_id',
						"value" 	=> $id_event,
						'compare'	=> '='
					),
					array(
						"key" 		=> OVA_METABOX_EVENT . 'ticket_status',
						"value" 	=> 'checked',
						'compare'	=> '='
					)
				)
			);
			$tickets = get_posts( $args );
			return $tickets;
		}

		public static function validate_qrcode( $request ){
			$qrcode 		=  sanitize_text_field( $request['check_qrcode'] );
			$ticket_info 	= array();

			$args = array(
				'post_type' 	=> 'el_tickets',
				'post_status' 	=> 'publish',
				'numberposts' 	=> '1',
				'fields'		=> 'ids',
				'meta_query' => array(
					array(
						'key' 		=> OVA_METABOX_EVENT . 'qr_code',
						'value' 	=> $qrcode,
						'compare'	=> '=',
					)
				)
			);

			$ticket_id = get_posts ( $args );

			if ( !$ticket_id ) {
				$ticket_info['status'] 	= 'error';
				$ticket_info['msg'] 	= esc_html__( 'Not found ticket', 'eventlist' );

				return $ticket_info;
			}
			
			// Get id of event
			$event_id 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'event_id', true );
			$seat_option 	= get_post_meta( $event_id , OVA_METABOX_EVENT.'seat_option', true );
			$ticket_type 	= '#'.$ticket_id[0].' - '.get_the_title( $ticket_id[0] );

			// Get staff member of event who can check qr code
			$staff_member = get_post_meta( $event_id, OVA_METABOX_EVENT.'api_key', true );

			// Get nickname of current user
			$current_user_login = EL_User::el_get_current_user_login();

			if ( ! $current_user_login ) {
				$ticket_info['status'] 	= 'error';
				$ticket_info['msg'] 	= esc_html__( 'Please login to check QR Code', 'eventlist' );
				return $ticket_info;
			}

			// If current user can't check QR Code
			if ( !( ( $current_user_login && $current_user_login == $staff_member ) || verify_current_user_post( $event_id ) ) ){
				$ticket_info['status'] 	= 'error';
				$ticket_info['msg'] 	= esc_html__( 'You don\'t have permission to check qr code', 'eventlist' );

				return $ticket_info;
			}

			// Validate and update ticket status
			$ticket_status 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'ticket_status', true );
			$times_checked 		= absint( get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'times_checked', true ) );
			$ticket_start_date 	= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_start', true );
			$ticket_end_date 	= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_end', true );

			# Convert time
			$check_start_date 	= strtotime( wp_date('Y-m-d', $ticket_start_date) );
			$check_end_date 	= strtotime( wp_date('Y-m-d', $ticket_end_date) ) + 24*60*60 - 1;
			$between_date 		= apply_filters( 'el_filter_between_date', absint( ceil( ( $check_end_date - $check_start_date ) / 86400 ) ) );
			$checks_remaining 	= apply_filters( 'el_filter_checks_remaining', absint( $between_date - $times_checked ) );

			if ( ! $ticket_status && $checks_remaining === 1 ) {
				$ticket = array(
					'ID' 			=> $ticket_id[0],
					'meta_input' 	=> array(
						OVA_METABOX_EVENT.'ticket_status' 	=> 'checked',
						OVA_METABOX_EVENT.'checkin_time' 	=> current_time('timestamp'),
						OVA_METABOX_EVENT.'times_checked' 	=> $times_checked + 1,
					)
				);

				if ( wp_update_post( $ticket ) ) {
					$ticket_info['status'] 		= 'valid';
					$ticket_info['msg'] 		= esc_html__( 'The QR Code is Valid', 'eventlist' );
					$ticket_info['msg_show'] 	= esc_html__( 'Update successful', 'eventlist' );
				} else {
					$ticket_info['status'] 	= 'error';
					$ticket_info['msg'] 	= esc_html__( 'Can\'t update ticket status', 'eventlist' );

					return $ticket_info;
				}
			} elseif ( ! $ticket_status && $times_checked < $between_date ) {
				$ticket = array(
					'ID' 			=> $ticket_id[0],
					'meta_input' 	=> array(
						OVA_METABOX_EVENT.'checkin_time' 	=> current_time('timestamp'),
						OVA_METABOX_EVENT.'times_checked' 	=> $times_checked + 1,
					)
				);

				if ( wp_update_post( $ticket ) ) {
					$ticket_info['status'] 		= 'valid';
					$ticket_info['msg'] 		= esc_html__( 'The QR Code is Valid', 'eventlist' );
					$ticket_info['msg_show'] 	= esc_html__( 'Update successful', 'eventlist' );
				} else {
					$ticket_info['status'] 	= 'error';
					$ticket_info['msg'] 	= esc_html__( 'Can\'t update ticket status', 'eventlist' );

					return $ticket_info;
				}
			}

			$name_event = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'name_event', true ) ;
			$checkin_time_tmp = get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'checkin_time', true ) ;
			$checkin_time =  $checkin_time_tmp ? date_i18n( get_option( 'date_format' ).' '. get_option( 'time_format' ), $checkin_time_tmp ) : '';

			$name_customer 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'name_customer', true ) ;
			$seat 				= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'seat', true );
			$person_type 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'person_type', true );
			$extra_service 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT.'extra_service', true );
			$data_extra_service = el_extra_sv_ticket( $extra_service );
			if ( $person_type ) {
				$seat.= ' - '.$person_type;
			}

			// Event Calendar
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');

			$start_date 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_start', true );
			$start_date_day 	= date_i18n($date_format, $start_date);
			$start_date_time 	= date_i18n($time_format, $start_date);

			$end_date 		= get_post_meta( $ticket_id[0], OVA_METABOX_EVENT . 'date_end', true );
			$end_date_day 	= date_i18n($date_format, $end_date);
			$end_date_time 	= date_i18n($time_format, $end_date);
			
			$event_calendar = $start_date_day === $end_date_day ? $start_date_day.' '.$start_date_time.'-'.$end_date_time : $start_date_day.'-'.$end_date_day.' '.$start_date_time.'-'.$end_date_time;

			if ( !isset( $ticket_info['status'] ) ) $ticket_info['status'] = 'checked-in';

			if ( $ticket_info['status'] == 'checked-in' ) {
				$ticket_info['msg'] = esc_html__( 'Already Checked In', 'eventlist' );
			}

			$ticket_info['checkin_time'] 	= $checkin_time;
			$ticket_info['name_customer'] 	= $name_customer;
			$ticket_info['seat'] 			= $seat;
			$ticket_info['e_cal'] 			= $event_calendar;
			$ticket_info['ticket_id'] 		= $ticket_id[0];
			$ticket_info['name_event'] 		= $name_event;
			$ticket_info['extra_service'] 	= $data_extra_service;
			$ticket_info['ticket_type'] 	= $ticket_type;
			
			return $ticket_info;
		}

		public function el_ticket_calendar_recurrence( $id ) {
			global $event;
			$show_remaining_tickets 			= EL()->options->event->get('show_remaining_tickets', 'yes');
			$ticket_link 						= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true );
			$ticket_external_link 				= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link', true );
			$list_type_ticket 					= get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true );
			$seat_option 						= get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true );
			$calendar_recurrence 				= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence', true );
			$calendar_recurrence_start_time 	= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence_start_time', true );
			$calendar_recurrence_end_time 		= get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence_end_time', true );
			$calendar_recurrence_book_before 	= floatval( get_post_meta( $id, OVA_METABOX_EVENT . 'calendar_recurrence_book_before', true ) ) * 60;
			$schedules_time 					= get_post_meta( $id, OVA_METABOX_EVENT . 'schedules_time', true );
			$recurrence_frequency 				= get_post_meta( $id, OVA_METABOX_EVENT . 'recurrence_frequency', true );
			$ts_start 							= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_start', true );
			$ts_end 							= get_post_meta( $id, OVA_METABOX_EVENT . 'ts_end', true );
			$current_time 						= current_time('timestamp') + $calendar_recurrence_book_before;
			$array_event 						= array();
			$initdate 							= wp_date( 'Y-m-d', $current_time );
			$finding_initdate 					= true;
			$check_tiket_selling 				= $event->check_ticket_in_event_selling( $id );
			$i = 0;
			$events_date = array();

			// External link
			if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) {
				$check_tiket_selling = true;
			}
			
			if ( $calendar_recurrence ) {
				// Get total scheudle of spceial date
				$total_schedule_time = array();

				// Time Slot
				$is_timeslot = false;

				if ( $recurrence_frequency === 'weekly' && ! empty( $ts_start ) && ! empty( $ts_end ) ) {
					$is_timeslot = true;
				}

				$cart_url = get_cart_page();

				foreach ( $calendar_recurrence as $key => $value ) {
					$event_obj = [];
					$total_number_ticket_rest 	= 0;
					$date 		= $value['date'];
					$start_time = $value['start_time'];
					$end_time 	= $value['end_time'];
					$url 		= add_query_arg( array(
						'ide' 	=> $id,
						'idcal' => $value['calendar_id']
					), $cart_url );

					if ( $seat_option != 'map' ) {
						foreach ( $list_type_ticket as $ticket ) {
							$number_ticket_rest 		= EL_Booking::instance()->get_number_ticket_rest( $id, $value['calendar_id'],  $ticket['ticket_id'] );
							$total_number_ticket_rest 	+= $number_ticket_rest;
						}
					} else {
						$total_number_ticket_rest = EL_Booking::instance()->get_number_ticket_map_rest( $id, $value['calendar_id'] );
					}

					$ticket_number = sprintf( _n( '%s ticket', '%s tickets', $total_number_ticket_rest, 'eventlist' ), number_format_i18n( $total_number_ticket_rest ) );

					$event_obj['title'] = sprintf( __( '<a class="event_schedule" href="%4$s" data-tippy-content="%1$s to %2$s ( %3$s )">%1$s to %2$s <br/>( %3$s )</a>', 'eventlist' ), $start_time, $end_time, $ticket_number, $url );

					$event_obj['start'] = $date.'T'.wp_date("H:i:s", strtotime( $start_time ) );
					$event_obj['end'] 	= $date.'T'.wp_date("H:i:s", strtotime( $end_time ) );
					$event_obj['url'] 	= $url;
					$array_event[] = $event_obj;


				}

			}

			// return $array_event;

			return array($initdate, $array_event);
		}

		public function el_ticket_received(){
			$current_user = wp_get_current_user();
			$args = array(
				'post_type' 		=> 'el_tickets',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' => array(
					array(
						'key'   	=> $this->_prefix.'transfer_status',
						'value' 	=> 'yes',
						'compare' 	=> '=',
					),
					array(
						'key' 		=> $this->_prefix.'transfer_email',
						'value' 	=> $current_user->user_email,
						'compare' 	=> '=',
					),
				),
			);

			$tickets = get_posts( $args );
			return $tickets;
		}

		public static function customer_check_qrcode( $request ){
			$nonce 		= isset( $request['_nonce'] ) ? sanitize_text_field( $request['_nonce'] ) : '';
			$id_ticket 	= isset( $request['id_ticket'] ) ? sanitize_text_field( $request['id_ticket'] ) : '';
			$qr_code 	= isset( $request['qr_code'] ) ? sanitize_text_field( $request['qr_code'] ) : '';

			$allow_no_login = apply_filters( 'el_allow_no_login_check_qrcode', true );

			if ( ! $qr_code ) {
				$qr_code = isset( $request['info_qrcode'] ) ? sanitize_text_field( $request['info_qrcode'] ) : '';
			}

			if ( $allow_no_login == true ) {
				if ( ! $qr_code ) {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 ); exit();
				} else {
					$ticket_id = el_get_id_ticket_by_qrcode( $qr_code );
					if (  count( $ticket_id ) > 0 ) {
						$id_ticket = $ticket_id[0];
					}
				}

			} else {

				if ( ! $nonce || ! $id_ticket || ! $qr_code || ! wp_verify_nonce( $nonce, 'el_check_qrcode' ) ) {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 ); exit();
				}

			}

			$response = [];
			$response['status'] = 'error';

			$ticket = get_post( $id_ticket );

			if ( ! $ticket  ) {
				$response['mess'] = esc_html__( 'Ticket not found', 'eventlist' );
			} elseif ( $ticket->post_type != 'el_tickets' || $ticket->post_status != 'publish' ) {
				$response['mess'] = esc_html__( 'Ticket is invalid', 'eventlist' );
			} else {
				$ticket_qr_code = get_post_meta( $id_ticket, OVA_METABOX_EVENT.'qr_code', true );
				if ( $ticket_qr_code != $qr_code ) {
					$response['mess'] = esc_html__( 'QR code is invalid', 'eventlist' );
				} else {
					$ticket_status 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'ticket_status', true );
					$seat 			= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'seat', true );
					$person_type 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'person_type', true );
					$extra_service 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'extra_service', true );
					$data_extra_service = el_extra_sv_ticket( $extra_service );

					$response['ticket_type'] = '#'.$id_ticket.' - '.$ticket->post_title;

					if ( $person_type ) {
						$seat.= ' - '.$person_type;
					}
					$response['seat'] = $seat;

					$arr_venue 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT . 'venue', true );
					$address 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT . 'address', true );

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
					
					if ( ! $ticket_status ) {
						$ticket_status = esc_html__( 'No', 'eventlist' );
						$response['status_class'] = '';
					} else {
						$ticket_status = esc_html__( 'Yes', 'eventlist' );
						$response['status_class'] = 'active';
					}

					$response['venue_address'] 	= $venue_address;
					$response['name_event'] 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'name_event', true );
					$response['name_customer'] 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'name_customer', true );
					$response['ticket_status'] 	= $ticket_status;
					$response['extra_service'] 	= $data_extra_service;


					$start_date_time 	= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'date_start', true );
					$end_date_time 		= get_post_meta( $id_ticket, OVA_METABOX_EVENT.'date_end', true );
					$format 			= get_option('date_format') . ' ' . get_option('time_format');
					$start_date_time_formatted 	= date_i18n( $format, $start_date_time );
					$end_date_time_formatted 	= date_i18n( $format, $end_date_time );
					$response['e_cal'] 			= $start_date_time_formatted.' - '.$end_date_time_formatted;
					$response['status'] 		= 'success';
				}
			}

			return $response;

		}

		public static function get_ticket_ids_by_booking_id( $booking_id ){
			$args = array(
				'post_type' 		=> 'el_tickets',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
				'meta_key' 			=> OVA_METABOX_EVENT.'booking_id',
				'meta_value' 		=> $booking_id,
			);

			$ticket_ids = get_posts( $args );
			return $ticket_ids;
		}

		public static function export_csv(){
			$ticket_ids = isset( $_POST['ticket_ids'] ) ? json_decode( sanitize_text_field( $_POST['ticket_ids'] ) ) : [];
			$id_event = isset( $_POST['id_event'] ) ? sanitize_text_field( $_POST['id_event'] ) : '';
			$check_allow_export_tickets = check_allow_export_tickets_by_event( $id_event );
			
			if ( ! $id_event || ! verify_current_user_post( $id_event ) || $check_allow_export_tickets != 'yes' || ! el_can_manage_ticket() ) wp_die();

			$check_event 			= isset( $_POST['check_event'] ) ? sanitize_text_field( $_POST['check_event'] ) : false;
			$check_booking_id 		= isset( $_POST['check_booking_id'] ) ? sanitize_text_field( $_POST['check_booking_id'] ) : false;
			$check_ticket_id 		= isset( $_POST['check_ticket_id'] ) ? sanitize_text_field( $_POST['check_ticket_id'] ) : false;
			$check_ticket_type 		= isset( $_POST['check_ticket_type'] ) ? sanitize_text_field( $_POST['check_ticket_type'] ) : false;
			$check_extra_service 	= isset( $_POST['check_extra_service'] ) ? sanitize_text_field( $_POST['check_extra_service'] ) : false;
			$check_name 			= isset( $_POST['check_name'] ) ? sanitize_text_field( $_POST['check_name'] ) : false;
			$check_phone_customer 	= isset( $_POST['check_phone_customer'] ) ? sanitize_text_field( $_POST['check_phone_customer'] ) : false;
			$check_email_customer 	= isset( $_POST['check_email_customer'] ) ? sanitize_text_field( $_POST['check_email_customer'] ) : false;
			$check_address_customer = isset( $_POST['check_address_customer'] ) ? sanitize_text_field( $_POST['check_address_customer'] ) : false;

			$check_venue 		= isset( $_POST['check_venue'] ) ? sanitize_text_field( $_POST['check_venue'] ) : false;
			$check_address 		= isset( $_POST['check_address'] ) ? sanitize_text_field( $_POST['check_address'] ) : false;
			$check_seat 		= isset( $_POST['check_seat'] ) ? sanitize_text_field( $_POST['check_seat'] ) : false;
			$check_qr_code 		= isset( $_POST['check_qr_code'] ) ? sanitize_text_field( $_POST['check_qr_code'] ) : false;
			$check_start_date 	= isset( $_POST['check_start_date'] ) ? sanitize_text_field( $_POST['check_start_date'] ) : false;
			$check_end_date 	= isset( $_POST['check_end_date'] ) ? sanitize_text_field( $_POST['check_end_date'] ) : false;
			$check_date_create 	= isset( $_POST['check_date_create'] ) ? sanitize_text_field( $_POST['check_date_create'] ) : false;

			$checkin_time 	= isset( $_POST['checkin_time'] ) ? sanitize_text_field($_POST['checkin_time']) : false;
			$ticket_checked = isset( $_POST['ticket_checked'] ) ? sanitize_text_field($_POST['ticket_checked']) : false;
			$ticket_price 	= isset( $_POST['ticket_price'] ) ? sanitize_text_field($_POST['ticket_price']) : false;


			$list_ckf_check 	= isset( $_POST['list_ckf_check'] ) ? $_POST['list_ckf_check'] : [];
			$list_ckf_output 	= get_option( 'ova_booking_form', array() );

			$csv_row = [];

			if ($check_event != 'false') {
				$csv_row[0][] = esc_html__("Event", "eventlist");
			}

			if ($check_booking_id != 'false') {
				$csv_row[0][] = esc_html__("Booking ID", "eventlist");
			}

			if ( $check_ticket_id != 'false' ) {
				$csv_row[0][] = esc_html__("Ticket ID", "eventlist");
			}

			if ($check_ticket_type != 'false') {
				$csv_row[0][] = esc_html__("Ticket Type", "eventlist");
			}

			if ($check_extra_service != 'false') {
				$csv_row[0][] = esc_html__("Extra Services", "eventlist");
			}

			if ($check_name != 'false') {
				$csv_row[0][] = esc_html__("Name", "eventlist");
			}

			if ($check_phone_customer != 'false') {
				$csv_row[0][] = esc_html__("Phone", "eventlist");
			}

			if ($check_email_customer != 'false') {
				$csv_row[0][] = esc_html__("Email", "eventlist");
			}

			if ($check_address_customer != 'false') {
				$csv_row[0][] = esc_html__("Address Customer", "eventlist");
			}

			if ($check_venue != 'false') {
				$csv_row[0][] = esc_html__("Venue", "eventlist");
			}

			if ($check_address != 'false') {
				$csv_row[0][] = esc_html__("Address", "eventlist");
			}

			if ($check_seat != 'false') {
				$csv_row[0][] = esc_html__("Seat", "eventlist");
			}

			if ($check_qr_code != 'false') {
				$csv_row[0][] = esc_html__("Qr Code", "eventlist");
			}

			if ($check_start_date != 'false') {
				$csv_row[0][] = esc_html__("Start date", "eventlist");
			}

			if ($check_end_date != 'false') {
				$csv_row[0][] = esc_html__("End date", "eventlist");
			}

			if ($check_date_create != 'false') {
				$csv_row[0][] = esc_html__("Date Created", "eventlist");
			}

			if ($checkin_time != 'false') {
				$csv_row[0][] = esc_html__("Checkin time", "eventlist");
			}

			if ($ticket_checked != 'false') {
				$csv_row[0][] = esc_html__("Ticket checked", "eventlist");
			}

			if ($ticket_price != 'false') {
				$csv_row[0][] = esc_html__("Price", "eventlist");
			}

			if ( ! empty( $list_ckf_check ) && is_array( $list_ckf_check ) ) {
				foreach ( $list_ckf_check as $name_ckf ) {
					if ( isset( $list_ckf_output[$name_ckf] ) && ! empty( $list_ckf_output[$name_ckf] ) ) {
						$field = $list_ckf_output[$name_ckf];

						if ( isset( $field['enabled'] ) && $field['enabled'] == 'on' && isset( $field['label'] ) ) {
							$csv_row[0][] = html_entity_decode( $field['label'] );
						}
					}
				}
			}

			/* Write Data */
			$i = 0;
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			$str_data_ckf = '';

			if ( count( $ticket_ids ) > 0 ) {
				foreach ( $ticket_ids as $post_id ) {
					
					$i++;

					$booking_id 		= get_post_meta( $post_id, OVA_METABOX_EVENT . 'booking_id', true );
					$create_manually 	= get_post_meta( $booking_id, OVA_METABOX_EVENT . 'create_manually', true );


	    			// Event Name
					if( $check_event != 'false' ){
						$csv_row[$i][] = get_post_meta( $post_id, OVA_METABOX_EVENT . 'name_event', true );

					}

					// Booking ID
					if( $check_booking_id != 'false' ){
						$csv_row[$i][] = get_post_meta( $post_id, OVA_METABOX_EVENT . 'booking_id', true );

					}
					// Ticket ID
					if ( $check_ticket_id != 'false' ) {
						$csv_row[$i][] = $post_id;
					}

					//Ticket type
					if( $check_ticket_type != 'false' ){
						$ticket_name = html_entity_decode( get_the_title( $post_id ) );
						$ticket_name = str_replace(",", " ", $ticket_name);
						$csv_row[$i][] = $ticket_name;
					}
					// Extra service
					if ( $check_extra_service != 'false' ) {
						$extra_service 		= get_post_meta( $post_id, OVA_METABOX_EVENT.'extra_service', true );
						$data_extra_service = el_extra_sv_ticket( $extra_service );
						$data_extra_service = wp_strip_all_tags( $data_extra_service );
						$data_extra_service = str_replace(', ', '; ', $data_extra_service );
						$csv_row[$i][] = html_entity_decode( $data_extra_service );
					}

					//Name Customer
					if( $check_name != 'false' ){
						$name = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'name_customer', true ) );
						$name = str_replace(",", " ", $name);
						$name = str_replace("#", " ", $name);
						$csv_row[$i][] = $name;
					}

					//Phone Customer
					if( $check_phone_customer != 'false' ){
						$phone_customer = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'phone_customer', true ) );
						$phone_customer = str_replace(",", " ", $phone_customer);
						$csv_row[$i][] = $phone_customer;
					}

					//Email Customer
					if( $check_email_customer != 'false' ){
						$email_customer = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'email_customer', true ) );
						$email_customer = str_replace(",", " ", $email_customer);
						$csv_row[$i][] = $email_customer;
					}

					//Address Customer
					if( $check_address_customer != 'false' ){
						$address_customer = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'address_customer', true ) );
						$address_customer = str_replace(",", " ", $address_customer);
						$address_customer = str_replace("#", " ", $address_customer);
						$csv_row[$i][] = $address_customer;
					}

					//Venue
					if( $check_venue != 'false' ){
						$arr_venue = get_post_meta( $post_id, OVA_METABOX_EVENT . 'venue', true );
						$venue = is_array( $arr_venue ) ? implode("; ", $arr_venue) : $arr_venue;
						$venue = str_replace(",", " ", $venue);
						$venue = str_replace("#", " ", $venue);

						$csv_row[$i][] = html_entity_decode( $venue );
					}

					//Address
					if( $check_address != 'false' ){
						$address = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'address', true ) );
						$address = str_replace(",", " ", $address);
						$address = str_replace("#", " ", $address);
						$csv_row[$i][] = $address;
					}

					//Seat
					if( $check_seat != 'false' ){
						$seat = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'seat', true ) );
						$seat = str_replace(",", " ", $seat);
						$seat = str_replace("#", " ", $seat);
						$person_type = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'person_type', true ) );
						if ( $person_type ) {
							$seat.= ' - '.$person_type;
						}
						$csv_row[$i][] = $seat;
					}

					//Qr code
					if( $check_qr_code != 'false' ){
						$qr_code = html_entity_decode( get_post_meta( $post_id, OVA_METABOX_EVENT . 'qr_code', true ) );
						$qr_code = str_replace(",", " ", $qr_code);
						$csv_row[$i][] = $qr_code;
					}

					//Date start
					if( $check_start_date != 'false' ){
						$date_start = get_post_meta( $post_id, OVA_METABOX_EVENT . 'date_start', true );
						$time_start = date_i18n($date_format, $date_start). " - " . date_i18n($time_format, $date_start);

						$time_start = str_replace(",", " ", $time_start);
						$csv_row[$i][] = $time_start;
					}

					//Date end
					if( $check_end_date != 'false' ){
						$date_end = get_post_meta( $post_id, OVA_METABOX_EVENT . 'date_end', true );
						$time_end = date_i18n($date_format, $date_end) . " - " . date_i18n($time_format, $date_end);

						$time_end = str_replace(",", " ", $time_end);
						$csv_row[$i][] = $time_end;
					}


					if( $check_date_create != 'false' ){

						$time = get_the_date($date_format, $post_id) . " - " . get_the_date($time_format, $post_id);

						$time = str_replace(",", " ", $time);

						$csv_row[$i][] = $time;
					}

					// checkin time
					if( $checkin_time != 'false' ){

						$checkin_time = get_post_meta( $post_id, OVA_METABOX_EVENT . 'checkin_time', true );

						if( $checkin_time ){

							$time = wp_date( $date_format.' - '.$time_format, $checkin_time );
							$csv_row[$i][] = str_replace(",", " ", $time); ;
						}
						else{
							$csv_row[$i][] = $checkin_time;
						}

					}

					if ( $ticket_checked != 'false' ) {
						$ticket_checked = get_post_meta( $post_id, OVA_METABOX_EVENT.'ticket_status', true );
						if ( $ticket_checked == 'checked' ) {
							$csv_row[$i][] = str_replace(",", " ", $ticket_checked);
						} else {
							$csv_row[$i][] = "";
						}
					}

					if ( $ticket_price != 'false' ) {
						$ticket_price 	= get_post_meta( $post_id, OVA_METABOX_EVENT.'price_ticket', true );
						$booking_id 	= get_post_meta( $post_id, OVA_METABOX_EVENT.'booking_id', true );

						if ( $create_manually !== "yes" ) {

							if ( ! $ticket_price ) {
								$cart 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'cart', true );
								$seat 			= get_post_meta( $post_id, OVA_METABOX_EVENT.'ticket_id_event', true );
								$person_type 	= get_post_meta( $post_id, OVA_METABOX_EVENT.'person_type', true );

								if ( ! empty( $cart ) ) {
									foreach ($cart as $key => $value) {
										if ( $value['id'] == $seat ) {

											if ( isset( $value['data_person'] ) && ! empty( $value['data_person'] && $person_type ) ) {
												foreach  ( $value['data_person'] as $k => $val ) {
													if ( $val['name'] == $person_type ) {
														$ticket_price = $val['price'];
													}
												}	
											} else {
												$ticket_price = isset( $value['price'] ) ? $value['price'] : '';
											}

										}
									}
								}
							}

							if ( $ticket_price ) {
								$csv_row[$i][] = html_entity_decode( el_price( $ticket_price ), ENT_HTML5, 'utf-8');
							} else {
								$csv_row[$i][] = html_entity_decode( el_price( 0 ), ENT_HTML5, 'utf-8');
							}
						} else {
							$csv_row[$i][] = html_entity_decode( el_price( 0 ), ENT_HTML5, 'utf-8');
						}
					}

					if ( ! empty( $list_ckf_check ) && is_array( $list_ckf_check ) ) {
						$data_checkout_field 		= get_post_meta( $post_id, OVA_METABOX_EVENT . 'data_checkout_field', true );
						$arr_data_checkout_field 	= json_decode( $data_checkout_field, true );

						foreach ( $list_ckf_check as $name_ckf ) {
							if ( isset( $list_ckf_output[$name_ckf] ) && ! empty( $list_ckf_output[$name_ckf] ) ) {
								$field = $list_ckf_output[$name_ckf];

								if ( isset( $field['enabled'] ) && $field['enabled'] == 'on' && isset( $field['label'] ) ) {
									if ( isset( $arr_data_checkout_field[$name_ckf] ) && $arr_data_checkout_field[$name_ckf] ) {
										$csv_row[$i][] = html_entity_decode( $arr_data_checkout_field[$name_ckf] );
									} else {
										$csv_row[$i][] = '';
									}
								}
							}
						}
					}
				}
			}
			echo json_encode($csv_row);
			wp_die();
		}
	}
}