<?php

defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Basic extends EL_Abstract_Metabox {
	
	public function __construct(){
		$this->_id = 'metabox';
		$this->_title = esc_html__( 'Basic Settings','eventlist' );
		$this->_screen = array( 'event' );
		$this->_output = EL_PLUGIN_INC . 'admin/views/metaboxes/metabox.php';
		$this->_prefix = OVA_METABOX_EVENT;

		parent::__construct();

		add_action( 'el_mb_proccess_update_meta', array( $this, 'update' ), 11, 2 );
	}
	

	public function update( $post_id, $post_data ){

		if( empty($post_data) ) exit();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( !isset( $post_data['ova_metaboxes'] ) || !wp_verify_nonce( $post_data['ova_metaboxes'], 'ova_metaboxes' ) )
			exit();

		if ( isset( $post_data['post_type'] ) && $post_data['post_type'] === 'event' ) {
			/* Checkbox Overwrite Profile */
			if( array_key_exists($this->_prefix.'info_organizer', $post_data) == false ){
				$post_data[$this->_prefix.'info_organizer'] = '';
			}else{
				$post_data[$this->_prefix.'info_organizer'] = 'checked';
			}

			/* Edit Full Address */
			if( array_key_exists($this->_prefix.'edit_full_address', $post_data) == false ){
				$post_data[$this->_prefix.'edit_full_address'] = '';
			}else{
				$post_data[$this->_prefix.'edit_full_address'] = 'checked';
			}

			if( !isset( $post_data[$this->_prefix.'time_zone'] ) ){
				$post_data[$this->_prefix.'time_zone'] = '';
			}
			

			/* Check Social exits */
			if( !isset( $post_data[$this->_prefix.'social_organizer'] ) ){
				$post_data[$this->_prefix.'social_organizer'] = array();
			}

			/* Check Gallery exits */
			if( !isset( $post_data[$this->_prefix.'gallery'] ) ){
				$post_data[$this->_prefix.'gallery'] = array();
			}

			/* Check Gallery exits */
			if( !isset( $post_data[$this->_prefix.'venue'] ) ){
				$post_data[$this->_prefix.'venue'] = array();
			}

			/* Check Ticket exits */
			if( !isset( $post_data[$this->_prefix.'ticket'] ) ){
				$post_data[$this->_prefix.'ticket'] = array();
			}

			/* Check Ticket exits */
			if( !isset( $post_data[$this->_prefix.'ticket_map']['seat'] ) ){
				$post_data[$this->_prefix.'ticket_map']['seat'] = array();
			}

			/* Check Calendar exits */
			if( !isset( $post_data[$this->_prefix.'calendar'] ) ){
				$post_data[$this->_prefix.'calendar'] = array();
			}

			/* Check Disable Date exits */
			if( !isset( $post_data[$this->_prefix.'disable_date'] ) ){
				$post_data[$this->_prefix.'disable_date'] = array();
			}

			/* Check Disable Time Slot exits */
			if( !isset( $post_data[$this->_prefix.'disable_date_time_slot'] ) ){
				$post_data[$this->_prefix.'disable_date_time_slot'] = array();
			}

			/* Check schedules Date exits */
			if( !isset( $post_data[$this->_prefix.'schedules_time'] ) ){
				$post_data[$this->_prefix.'schedules_time'] = array();
			}


			/* Check Coupon exits */
			if( !isset( $post_data[$this->_prefix.'coupon'] ) ){
				$post_data[$this->_prefix.'coupon'] = array();
			}

			/* Check recurrence bydays exits */
			if( !isset( $post_data[$this->_prefix.'recurrence_bydays'] ) ){
				$post_data[$this->_prefix.'recurrence_bydays'] = array('0');
			}

			/* Check recurrence interval exits */
			if( !$post_data[$this->_prefix.'recurrence_interval'] ){
				$post_data[$this->_prefix.'recurrence_interval'] = '1';
			}

			/* Check Calendar Auto */ 
			$recurrence_days = get_recurrence_days(
				$post_data[$this->_prefix.'recurrence_frequency'], 
				$post_data[$this->_prefix.'recurrence_interval'], 
				$post_data[$this->_prefix.'recurrence_bydays'], 
				$post_data[$this->_prefix.'recurrence_byweekno'], 
				$post_data[$this->_prefix.'recurrence_byday'], 
				$post_data[$this->_prefix.'calendar_start_date'], 
				$post_data[$this->_prefix.'calendar_end_date']
			);

			$post_data[$this->_prefix.'calendar_recurrence'] = array();
			$post_data[$this->_prefix.'ts_start'] 	= isset( $post_data[$this->_prefix.'ts_start'] ) && $post_data[$this->_prefix.'ts_start'] ? $post_data[$this->_prefix.'ts_start'] : '';
			$post_data[$this->_prefix.'ts_end'] 	= isset( $post_data[$this->_prefix.'ts_end'] ) && $post_data[$this->_prefix.'ts_end'] ? $post_data[$this->_prefix.'ts_end'] : '';

			$recurrence_time_slot = array();

			foreach ( $recurrence_days as $key => $value ) {
				if ( isset( $post_data[$this->_prefix.'schedules_time'] ) ) {
					foreach ( $post_data[$this->_prefix.'schedules_time'] as $key_schedule => $value_schedule ) {

						$post_data[$this->_prefix.'calendar_recurrence'][] = [
							'calendar_id' 	=> $value.$key_schedule,
							'date' 			=> gmdate('Y-m-d', $value),
							'start_time' 	=> $value_schedule['start_time'],
							'end_time' 		=> $value_schedule['end_time'],
							'book_before' 	=> $value_schedule['book_before'],
						];
					}
				}

				$post_data[$this->_prefix.'calendar_recurrence'][] = [
					'calendar_id' 	=> $value,
					'date' 			=> gmdate('Y-m-d', $value),
					'start_time' 	=> $post_data[$this->_prefix.'calendar_recurrence_start_time'],
					'end_time' 		=> $post_data[$this->_prefix.'calendar_recurrence_end_time'],
					'book_before' 	=> $post_data[$this->_prefix.'calendar_recurrence_book_before'],
				];

				if ( $post_data[$this->_prefix.'option_calendar'] == 'auto' && $post_data[$this->_prefix.'recurrence_frequency'] == 'weekly' && isset( $post_data[$this->_prefix.'recurrence_bydays'] ) && ! empty( $post_data[$this->_prefix.'recurrence_bydays'] ) ) {

					$weekday = gmdate( 'N', $value );

					if ( $weekday == 7 ) {
						$weekday = 0;
					}

					foreach ( $post_data[$this->_prefix.'recurrence_bydays'] as $k_bydays => $v_bydays ) {
						if ( $weekday == $v_bydays && isset( $post_data[$this->_prefix.'ts_start'][$v_bydays] ) && isset( $post_data[$this->_prefix.'ts_end'][$v_bydays] ) && ! empty( $post_data[$this->_prefix.'ts_start'][$v_bydays] ) && ! empty( $post_data[$this->_prefix.'ts_end'][$v_bydays] ) ) {

							foreach ( $post_data[$this->_prefix.'ts_start'][$v_bydays] as $k_ts_start => $v_ts_start ) {
								if ( isset( $post_data[$this->_prefix.'ts_end'][$v_bydays][$k_ts_start] ) && $post_data[$this->_prefix.'ts_end'][$v_bydays][$k_ts_start] ) {

									$recurrence_time_slot[] = [
										'calendar_id' 	=> $value.$v_bydays.$k_ts_start,
										'date' 			=> gmdate('Y-m-d', $value),
										'start_time' 	=> $v_ts_start,
										'end_time' 		=> $post_data[$this->_prefix.'ts_end'][$v_bydays][$k_ts_start],
										'book_before' 	=> apply_filters( 'el_tf_time_slot_book_before', 0, $post_id ),
									];
								}
							}
						}
					}
				}
			}

			if ( ! empty( $recurrence_time_slot ) && is_array( $recurrence_time_slot ) ) {
				$post_data[$this->_prefix.'calendar_recurrence'] = $recurrence_time_slot;
			}

			/* Disable Date */
			$arr_disable_date 		= array();
			$total_key_disable_date = 0;

			if ( isset( $post_data[$this->_prefix.'disable_date'] ) && ! empty( $post_data[$this->_prefix.'disable_date'] ) ) {
				foreach ($post_data[$this->_prefix.'disable_date'] as $key => $value) {

					if ( $value['start_date'] == '' && $value['end_date'] != '' ) {
						$post_data[$this->_prefix.'disable_date'][$key]['start_date'] =  $post_data[$this->_prefix.'disable_date'][$key]['end_date'];
					}

					if ( $value['start_date'] != '' && $value['end_date'] == '' ) {
						$post_data[$this->_prefix.'disable_date'][$key]['end_date'] =  $post_data[$this->_prefix.'disable_date'][$key]['start_date'];
					}

					if ( $value['start_date'] == '' && $value['end_date'] == '' ) {
						unset( $post_data[$this->_prefix.'disable_date'][$key] );
					}

					$total_key_disable_date = $key;
				}

				if( isset($total_key_disable_date) ){
					for ($i = 0; $i <= $total_key_disable_date; $i++) {

						$number_date = ( strtotime( $post_data[$this->_prefix.'disable_date'][$i]['end_date'] ) - strtotime( $post_data[$this->_prefix.'disable_date'][$i]['start_date'] ) ) / 86400;

						for ( $x = 0; $x <= $number_date; $x++ ) {
							$arr_disable_date []= [
								'date' => strtotime( ($x).' days' , strtotime( $post_data[$this->_prefix.'disable_date'][$i]['start_date'] ) ),
								'time' => isset( $post_data[$this->_prefix.'disable_date'][$i]['schedules_time'] ) ? $post_data[$this->_prefix.'disable_date'][$i]['schedules_time'] : '',
							];
						}
						
					}
				}
			}

			/* Disable Time Slot */
			if ( isset( $post_data[$this->_prefix.'disable_date_time_slot'] ) && ! empty( $post_data[$this->_prefix.'disable_date_time_slot'] ) ) {
				foreach ( $post_data[$this->_prefix.'disable_date_time_slot'] as $k => $ts_item ) {

					if ( $ts_item['start_date'] == '' && $ts_item['end_date'] != '' ) {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['start_date'] = $post_data[$this->_prefix.'disable_date_time_slot'][$k]['end_date'];
					}

					if ( $ts_item['start_date'] != '' && $ts_item['end_date'] == '' ) {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['end_date'] = $post_data[$this->_prefix.'disable_date_time_slot'][$k]['start_date'];
					}

					if ( $ts_item['start_time'] ) {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['start_time'] = $ts_item['start_time'];
					} else {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['start_time'] = '';
					}

					if ( $ts_item['end_time'] ) {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['end_time'] = $ts_item['end_time'];
					} else {
						$post_data[$this->_prefix.'disable_date_time_slot'][$k]['end_time'] = '';
					}
				}
			}

			/* Remove date disabled */
			if ( ! empty( $recurrence_time_slot ) && is_array( $recurrence_time_slot ) ) {
				if ( isset( $post_data[$this->_prefix.'disable_date_time_slot'] ) && ! empty( $post_data[$this->_prefix.'disable_date_time_slot'] ) ) {
					foreach ( $post_data[$this->_prefix.'calendar_recurrence'] as $key => $value ) {
						foreach ( $post_data[$this->_prefix.'disable_date_time_slot'] as $ts_item ) {
							$cal_start 	= strtotime( $value['date'] . ' ' . $value['start_time'] ) - absint( $value['book_before'] * 60 );
							$cal_end 	= strtotime( $value['date'] . ' ' . $value['end_time'] );

							$ts_start 	= strtotime( $ts_item['start_date'] . ' ' . $ts_item['start_time'] );
							$ts_end 	= strtotime( $ts_item['end_date'] . ' ' . $ts_item['end_time'] );

							if ( ! ( $ts_start >= $cal_end || $ts_end <= $cal_start ) ) {
								unset( $post_data[$this->_prefix.'calendar_recurrence'][$key] );
							}
						}
					}
				}
			} else {
				if ( ! empty( $arr_disable_date ) && is_array( $arr_disable_date ) ) {
					foreach ( $post_data[$this->_prefix.'calendar_recurrence'] as $key => $value ) {
						foreach ( $arr_disable_date as $v_date ) {
							if ( $v_date['date'].$v_date['time'] == ( $value['calendar_id'] ) )	{
								unset($post_data[$this->_prefix.'calendar_recurrence'][$key]);
							}
						}
					}
				}
			}

			$ticket_prices 	= array();
			$seat_option 	= isset( $post_data[$this->_prefix.'seat_option'] ) ? $post_data[$this->_prefix.'seat_option'] : '';
			

			$decimal_separator 	= EL()->options->general->get('decimal_separator','.');
			$k = 0;
			if ( isset( $post_data[$this->_prefix.'ticket'] ) ) {
				foreach ($post_data[$this->_prefix.'ticket'] as $key => $value) {

					if ($value['ticket_id'] == '') {

						$post_data[$this->_prefix.'ticket'][$key]['ticket_id'] = FLOOR(microtime(true)) + $k;

						$k++;
					}

					if ( $value['setup_seat'] == '' ) {

						$post_data[$this->_prefix.'ticket'][$key]['setup_seat'] =  'yes';

					}

					$post_data[$this->_prefix.'ticket'][$key]['private_desc_ticket'] = wp_kses_post( $post_data[$this->_prefix.'ticket'][$key]['private_desc_ticket'] );

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
						$post_data[$this->_prefix.'ticket'][$key]['seat_list'] = implode(", ", $seat_list );
					}

					if ( isset( $value['price_ticket'] ) && $value['price_ticket'] ) {
						$price = $value['price_ticket'];
						$new_price = str_replace( $decimal_separator, ".", $price );
						if ( $price !== $new_price ) {
							$post_data[$this->_prefix.'ticket'][$key]['price_ticket'] = $new_price;
						}
						$ticket_prices['none'][] = (float) $new_price;
						$ticket_prices['simple'][] = (float) $new_price;
					}
				}
			}

			// external link
			$ticket_link = isset( $post_data[$this->_prefix.'ticket_link'] ) ? $post_data[$this->_prefix.'ticket_link'] : '';

			if ( $ticket_link !== 'ticket_internal_link' ) {
				if ( isset( $post_data[$this->_prefix.'ticket_external_link_price'] )  ) {
					$price = $post_data[$this->_prefix.'ticket_external_link_price'] ? $post_data[$this->_prefix.'ticket_external_link_price'] : '';
					if ( $price ) {
						$price = preg_replace('/[^0-9]/', '', $price);  
						$ticket_prices['ticket_external_link'][] = (float) $price;
					}
				}
			}
			

			if ( isset( $post_data[$this->_prefix.'calendar'] ) ) {
				foreach ($post_data[$this->_prefix.'calendar'] as $key => $value) {
					if ($value['calendar_id'] == '') {

						$post_data[$this->_prefix.'calendar'][$key]['calendar_id'] = FLOOR(microtime(true)) + $k;

						$k++;
					}
					if ($value['date'] == '') {
						unset($post_data[$this->_prefix.'calendar'][$key]);
					}
				}
			}


			if ( isset( $post_data[$this->_prefix.'coupon'] ) ) {
				foreach ($post_data[$this->_prefix.'coupon'] as $key => $value) {
					if ($value['coupon_id'] == '') {

						$post_data[$this->_prefix.'coupon'][$key]['coupon_id'] = FLOOR(microtime(true)) + $k;

						$k++;
					}
				}
			}
			

			if ( isset( $post_data[$this->_prefix.'venue'] ) ) {
				foreach ($post_data[$this->_prefix.'venue'] as $value) {
					$value = str_replace('\\', '', $value);
					if (!el_get_page_by_title( $value, OBJECT, 'venue' )) {
						$venue_info = array(
							'post_author' 	=> get_current_user_id(),
							'post_title' 	=>  $value,
							'post_content' 	=> '',
							'post_type' 	=> 'venue',
							'post_status' 	=> 'publish',
							'_thumbnail_id' => '',
						);
						wp_insert_post( $venue_info, true ); 
					}
				}
			}

			$arr_start_date = array();
			$event_days = '';
			$arr_end_date = array();
			if ($post_data[$this->_prefix.'option_calendar'] == 'manual') {
				if ( isset( $post_data[$this->_prefix.'calendar'] ) ) {
					foreach ($post_data[$this->_prefix.'calendar'] as $value) {
						$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
						$arr_end_date[] = strtotime( $value['end_date'] .' '. $value['end_time'] );

						$all_date_betweens_day = el_getDatesFromRange( gmdate( 'Y-m-d', strtotime( $value['date'] ) ), gmdate( 'Y-m-d', strtotime( $value['end_date'] )+24*60*60 ) );
						foreach ($all_date_betweens_day as $v) {
							$event_days .= $v.'-';
						}
						
					}
				}
			} else {
				if ( isset( $post_data[$this->_prefix.'calendar_recurrence'] ) ) {
					foreach ($post_data[$this->_prefix.'calendar_recurrence'] as $value) {
						$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
						$arr_end_date[] = strtotime( $value['date'] .' '. $value['end_time'] );
						$event_days .= strtotime( $value['date'] ).'-';
					}
				}
			}

			// store all days of event
			$post_data[$this->_prefix.'event_days'] = $event_days;

			if ( $arr_start_date != array() )  {
				$post_data[$this->_prefix.'start_date_str'] = min($arr_start_date);
			} else {
				$post_data[$this->_prefix.'start_date_str'] = '';
			}

			if ( $arr_end_date != array() ) {
				$post_data[$this->_prefix.'end_date_str'] = max($arr_end_date);
			} else {
				$post_data[$this->_prefix.'end_date_str'] = '';
			}

			$post_data[$this->_prefix.'ticket_map']['private_desc_ticket_map'] = isset( $post_data[$this->_prefix.'ticket_map']['private_desc_ticket_map'] ) ? wp_kses_post( $post_data[$this->_prefix.'ticket_map']['private_desc_ticket_map'] ) : '';

			/* Remove empty field seat map */
			if ( isset( $post_data[$this->_prefix.'ticket_map']['seat'] ) ) {
				foreach ($post_data[$this->_prefix.'ticket_map']['seat'] as $key => $value) {
					if ( $value['id'] == '' || ( $value['price'] == '' && empty( $value['person_price'] ) ) ) {
						unset($post_data[$this->_prefix.'ticket_map']['seat'][$key]);
					} else {
						// add ticket price
						if ( ! empty( $value['person_price'] ) ) {
							$person_price = wp_unslash( $value['person_price'] );
							foreach ( json_decode( $person_price ) as $price ) {
								$ticket_prices['map'][] = (float) $price;
							}
							
						} else {
							$ticket_prices['map'][] = (float)$value['price'];
						}
						
					}
				}
			}
				

			if ( isset( $post_data[$this->_prefix.'ticket_map']['area'] ) ) {
				foreach ($post_data[$this->_prefix.'ticket_map']['area'] as $key => $value) {
					if ( isset( $value['price'] ) ) {
						$ticket_prices['map'][] = (float) $value['price'];
					} elseif ( isset( $value['person_price'] ) && is_array( json_decode( $person_price ) ) ) {
						$person_price = wp_unslash( $value['person_price'] );
						if ( is_array( $person_price ) ) {
							foreach ( json_decode( $person_price ) as $price ) {
								$ticket_prices['map'][] = (float) $price;
							}
						}
					
					}
				}
			}

			/* Remove empty field description seat map */
			if( isset($post_data[$this->_prefix.'ticket_map']['desc_seat']) && $post_data[$this->_prefix.'ticket_map']['desc_seat'] ){
				foreach ($post_data[$this->_prefix.'ticket_map']['desc_seat'] as $key => $value) {
					if ( $value['map_price_type_seat'] == '' || $value['map_type_seat'] == '' ) {
						unset($post_data[$this->_prefix.'ticket_map']['desc_seat'][$key]);
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

			$min_price = '';
			$max_price = '';

			if ( $min_max_price != '' ) {
				$min_max_price = explode("-", $min_max_price);
				$min_max_price = array_map('floatval', $min_max_price);
				$min_price = min($min_max_price);
				$max_price = max($min_max_price);
			}

			$post_data[$this->_prefix.'min_price'] = $min_price;
			$post_data[$this->_prefix.'max_price'] = $max_price;


			// extra services
			$sanitized_extra_services = [];
			if ( ! empty( $post_data[$this->_prefix.'extra_service'] ) ) {
				foreach ( $post_data[$this->_prefix.'extra_service'] as $k => $val ) {
					if ( ! isset( $val['id'] ) || empty( $val['id'] ) ) {
						$val['id'] = uniqid();
					}
					if ( isset( $val['qty'] ) && ! empty( $val['qty'] ) && isset( $val['max_qty'] ) && ! empty( $val['max_qty'] ) ) {
						$qty = (int)$val['qty'];
						$max_qty = (int)$val['max_qty'];
						if ( $qty < $max_qty ) {
							$val['max_qty'] = $qty;
						}
					}
					$sanitized_extra_services[] = $val;
				}
				$post_data[$this->_prefix.'extra_service'] = $sanitized_extra_services;
			} else {
				$post_data[$this->_prefix.'extra_service'] = [];
			}

			foreach ( $post_data as $name => $val ) {

				if ( strpos( $name, 'ova_mb_event' ) !== false ) {

					if ( $name === 'ova_mb_event_ticket_external_link' ) {
						update_post_meta( $post_id, $name, sanitize_url( $val ) );
						continue;
					}

					if ( $name === 'ova_mb_event_ticket' || $name === 'ova_mb_event_ticket_map' ) {
						update_post_meta( $post_id, $name, $val );
						continue;
					}

					if ( is_array( $val ) ) {
			            $val = recursive_sanitize_text_field($val);
			        } else {
			            $val = sanitize_text_field( $val );
			        }
					update_post_meta( $post_id, $name, $val );
				}
			}

			/* Check admin active event */
			$status_event = get_post_status( $post_id );
			if( $status_event == 'publish'  ){
				$event_active = '1';
			}else{
				$event_active = '0';
			}
			update_post_meta( $post_id, $this->_prefix.'event_active', $event_active );

			do_action( 'el_after_save_event_metabox', $post_id );
		}
	}
}