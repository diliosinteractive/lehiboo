<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'EL_Cart', false ) ) {
	return new EL_Cart();
}

/**
 * Admin Assets classes
 */
class EL_Cart{

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Constructor
	 */
	public function __construct(){

		

	}

	/**
	 * Add menu items.
	 */
	public function get_template_cart( $get_data ) {


			$template = apply_filters( 'el_shortcode_cart_template', 'cart/cart.php' );
		
		

		return $template;
	}

	public function get_setting_price() {
		$settingGeneral = EL()->options->general->general;

		$currency = _el_symbol_price();
		$currency_position = $settingGeneral->get( 'currency_position' );
		$thousand_separator = $settingGeneral->get( 'thousand_separator' );
		$decimal_separator = $settingGeneral->get( 'decimal_separator' );
		$number_decimals = $settingGeneral->get( 'number_decimals' );
		$data = [
			'currency' => $currency,
			'currency_position' => $currency_position,
			'thousand_separator' => $thousand_separator,
			'decimal_separator' => $decimal_separator,
			'number_decimals' => $number_decimals
		];
		return json_encode($data);
	}

	public function check_code_discount( $id_event = null, $input_discount = null ) {
		if ( $id_event == null || $input_discount == null ) return false;

		$event_coupons 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'coupon', true);
		$seat_option 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true);

		if ( ! empty( $event_coupons ) && is_array( $event_coupons ) ) {
			foreach ( $event_coupons as $coupon ) {
				$number_coupon_used 	= EL_Booking::instance()->get_number_coupon_code_used( $id_event, $input_discount );
				$time_start_discount 	= el_get_time_int_by_date_and_hour( $coupon['start_date'], $coupon['start_time'] );
				$time_end_discount 		= el_get_time_int_by_date_and_hour( $coupon['end_date'], $coupon['end_time'] );
				$current_time 			= current_time('timestamp');

				if ( $time_start_discount < $current_time && $current_time < $time_end_discount && $coupon['discount_code'] == $input_discount && $coupon['quantity'] > 0  && $coupon['quantity'] > $number_coupon_used ) {
					if ( $seat_option != 'map' ) {
						$data_counpon = [
							'discount_number' 	=> $coupon['discount_amout_number'], 
							'discount_percenr' 	=> $coupon['discount_amount_percent'],
							'quantity' 			=> $coupon['quantity'],
							'id_ticket' 		=> $coupon['list_ticket'],
						];
					} else {
						$data_counpon = [
							'discount_number' 	=> $coupon['discount_amout_number'],
							'discount_percenr' 	=> $coupon['discount_amount_percent'],
							'quantity' 			=> $coupon['quantity'],
							'id_ticket' 		=> '',
						];
					}
					
					return json_encode($data_counpon);
				}
			}
		}
		
		return false;
	}


	public function el_get_calendar( $id_event, $id_cal ){
		if( ! $id_event || ! $id_cal ) return;
		$list_calendar = get_arr_list_calendar_by_id_event($id_event);

		if( is_array($list_calendar) && !empty($list_calendar) ){
			foreach ($list_calendar as $key => $cal) {
				if( (string)$cal['calendar_id'] === $id_cal ) {
					return $cal;
				}
			}
		}

		return;
	}

	public function is_booking_ticket_by_date_time( $start_date = 0, $start_time = 0, $end_date = 0, $end_time = 0, $event_id = null ) {
		$start_time_all = el_get_time_int_by_date_and_hour( $start_date, $start_time);
		$end_time_all 	= el_get_time_int_by_date_and_hour( $end_date, $end_time);
		$current_time 	= current_time('timestamp');

		if ( $event_id ) {
			$timezone = get_post_meta( $event_id, OVA_METABOX_EVENT . 'time_zone', true );

			if ( $timezone ) {
				$tz_string 	= el_get_timezone_string( $timezone );
				$datetime 	= new DateTime('now', new DateTimeZone( $tz_string ) );
				$time_now 	= $datetime->format('Y-m-d H:i');

				if ( strtotime( $time_now ) ) {
					$current_time = strtotime( $time_now );
				}
			}
		}

		if ( $start_time_all < $current_time && $current_time <  $end_time_all) {
			return true;
		} else {
			return false;
		}
	}

	public function get_total( $id_event = null, $cart = [], $coupon = null ) {
		if ( ! $id_event || ! $cart || ! is_array ($cart)) return;

		$list_type_ticket 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket', true );
		$ticket_map 		= get_post_meta( $id_event, OVA_METABOX_EVENT . 'ticket_map', true );
		$seat_option 		= get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true );
		$list_price_ticket 	= [];

		if ( $seat_option != 'map' ) {
			if ( ! empty( $list_type_ticket ) && is_array( $list_type_ticket ) ) {
				foreach ( $list_type_ticket as $tiket ) {
					$list_price_ticket[$tiket['ticket_id']] = isset( $tiket['price_ticket'] ) ? $tiket['price_ticket'] : 0;
				}
			}
		}

		$data_counpon = [];

		if ( $coupon != null ) {
			$data_counpon = self::check_code_discount ($id_event, $coupon);
			$data_counpon = $data_counpon ? json_decode($data_counpon, true) : [];
		}

		$total = 0;

		if ( $seat_option != 'map' ) {
			foreach ( $cart as $item ) {
				$sub_total 			= 0;
				$price_unit_ticket 	= floatval( $list_price_ticket[$item['id']] );
				$qty 				= absint( $item['qty'] );
				$sub_total 			= $price_unit_ticket * $qty;
				$price_dicount 		= $sub_total;
				$sub_total_dicount 	= 0;
				$qty_counpon 		= isset( $data_counpon['quantity'] ) ? absint( $data_counpon['quantity'] ) : 0;
				$extra_service = isset( $item['extra_service'] ) ? $item['extra_service'] : [];
				
				if ( $qty_counpon && $qty > $qty_counpon ) {
					$price_dicount 	= $price_unit_ticket * $qty_counpon;
					$qty 			= $qty_counpon;
				}

				if ( $coupon != null ) {
					if ( in_array( $item['id'], $data_counpon['id_ticket'] ) ) {

						if ( ! empty( $data_counpon['discount_percenr'] ) ) {
							$sub_total_dicount = ( $price_dicount * $data_counpon['discount_percenr'] ) / 100;
						} 

						if ( ! empty( $data_counpon['discount_number'] ) ) {
							$sub_total_dicount = $qty * $data_counpon['discount_number'];
						}
					}
				}

				$sub_total 	= $sub_total - $sub_total_dicount;
				$total 		+= $sub_total;
			}
		} else {
			$sub_total 			= 0;
			$sub_total_dicount 	= 0;
			$qty 				= 0;

			foreach ( $cart as $cart_item ) {

				$item_qty 	= isset( $cart_item['qty'] ) ? absint( $cart_item['qty'] ) : 1;

				if ( ! empty( $cart_item['data_person'] ) ) {
					foreach ( (array)$cart_item['data_person'] as $value ) {
						$qty += absint($value['qty']);
						$sub_total += (float)$value['price'] * absint($value['qty']);
					}
				} else {
					$qty 		+= $item_qty;
					$sub_total 	+= $cart_item['price'] * $item_qty;
				}
				
			}

			if ( ! empty( $data_counpon['discount_percenr'] ) ) {
				$sub_total_dicount = ( $sub_total * $data_counpon['discount_percenr'] ) / 100;
			} 

			if ( ! empty( $data_counpon['discount_number'] ) ) {
				$sub_total_dicount = $qty * $data_counpon['discount_number'];
			}
			
			$sub_total 	= $sub_total - $sub_total_dicount;
			$total 		+= $sub_total;
		}

		return $total;
	}

	public function get_total_after_tax( $total_before_tax = 0, $id_event = null ) {

		$enable_tax = EL()->options->tax_fee->get('enable_tax');
		$percent_tax = EL()->options->tax_fee->get('pecent_tax');

		if ($enable_tax !== 'yes') return $total_before_tax;

		if ( empty($id_event) ) return;

		$check_allow_change_tax = check_allow_change_tax_by_event( $id_event );

		$event_tax = get_post_meta( $id_event, OVA_METABOX_EVENT . 'event_tax', true );

		if ( $check_allow_change_tax == "yes" && ( !empty($event_tax) || $event_tax === '0' ) ) {
			$tax = ( $total_before_tax * floatval( $event_tax ) ) / 100;
		} else {
			$tax = ( $total_before_tax * floatval( $percent_tax ) ) / 100;
		}

		$total_after_tax = $total_before_tax + $tax;

		return $total_after_tax;
	}

	public function sanitize_list_checkout_field ( $arr_list_ckf = [] ) {
		$arr_sanitize_list_ckf = [];
		if( $arr_list_ckf && is_array( $arr_list_ckf ) ) {
			foreach($arr_list_ckf as $key_ckf => $value_ckf) {
				$arr_sanitize_list_ckf[$key_ckf] = sanitize_text_field($value_ckf);
			}
		}
		return $arr_sanitize_list_ckf;
	}

	public function sanitize_data_customers( $data_customers = [] ) {
		if ( !empty( $data_customers ) ) {
			if ( is_array( $data_customers ) ) {
				foreach ( $data_customers as $key => $value ) {
	        		$data_customers[$key] = sanitize_data_customers( $value );
	    		}
			} else {
				return sanitize_text_field( $data_customers );
			}
		}

	    return $data_customers;
	}

	public function sanitize_cart ( $cart = [] ) {
		
		if ( ! empty($cart)  && ! is_array($cart) ) return [];
		foreach ($cart as $key => $item) {
			$cart[$key]['name'] = sanitize_text_field($item['name']);
			$cart[$key]['qty'] = (int)$item['qty'];
			$cart[$key]['price'] = floatval( $item['price'] );

			$arr_sanitize_seat = [];
			if ( array_key_exists('seat', $item) && is_array($item['seat']) ) {
				foreach($item['seat'] as $value_seat) {
					$arr_sanitize_seat[] = sanitize_text_field($value_seat);
				}
			}
			$cart[$key]['seat'] = $arr_sanitize_seat;
		}

		return $cart;
	}

	public function sanitize_cart_map ( $cart = [] ) {
		
		if ( ! empty($cart)  && ! is_array($cart) ) return [];
		foreach ($cart as $key => $item) {

			$cart[$key]['id'] = sanitize_text_field($item['id']);
			
			if ( isset( $item['data_person'] ) ) {
				$cart[$key]['data_person'] = $item['data_person'];
				$price = 0;
				$qty = 0;
				foreach ( $item['data_person'] as $k => $val ) {
					$price += (float) $val['price'] * (int) $val['qty'];
					$qty += (int) $val['qty'];
				}
				$cart[$key]['price'] = $price;
				$cart[$key]['person_qty'] = $qty;
			} else {
				
				$cart[$key]['price'] = floatval( $item['price'] );
			}
		}
		return $cart;
	}

	// Get total discount
	public function el_get_total_discount( $event_id = null, $cart = [], $coupon = null ) {
		if ( ! $event_id || ! $cart || ! is_array ( $cart ) || ! $coupon ) return;

		$total_discount = 0;
		$data_counpon 	= [];

		if ( $coupon ) {
			$data_counpon = self::check_code_discount( $event_id, $coupon );
			$data_counpon = $data_counpon ? json_decode( $data_counpon, true) : [];
		}

		if ( ! empty( $data_counpon ) && is_array( $data_counpon ) ) {
			$seat_option = get_post_meta( $event_id, OVA_METABOX_EVENT . 'seat_option', true );

			if ( $seat_option != 'map' ) {
				foreach ( $cart as $item ) {
					$qty 			= absint( $item['qty'] );
					$price 			= floatval( $item['price'] );
					$total 			= $price * $qty;
					$qty_counpon 	= isset( $data_counpon['quantity'] ) ? absint( $data_counpon['quantity'] ) : 0;

					if ( $qty_counpon && $qty > $qty_counpon ) {
						$qty 	= $qty_counpon;
						$total 	= $price * $qty;
					}

					if ( in_array( $item['id'], $data_counpon['id_ticket'] ) ) {
						if ( ! empty( $data_counpon['discount_number'] ) ) {
							$total_discount += $qty * $data_counpon['discount_number'];
						} else {
							if ( ! empty( $data_counpon['discount_percenr'] ) ) {
								$total_discount += ( $total * $data_counpon['discount_percenr'] ) / 100;
							}
						}
					}
				}
			} else {
				$total 	= 0;
				$qty 	= 0;

				foreach ( $cart as $item ) {
					$item_qty 	= isset( $item['qty'] ) ? absint( $item['qty'] ) : 1;

					if ( ! empty( $item['data_person'] ) ) {
						foreach ( $item['data_person'] as $person_item ) {
							$total += (float)$person_item['price'] * absint( $person_item['qty'] );
							$qty += absint( $person_item['qty'] );
						}
					} else {
						$qty += $item_qty;
						$total += $item['price'] * $item_qty;
					}

				}

				if ( ! empty( $data_counpon['discount_number'] ) ) {
					$total_discount += $qty * $data_counpon['discount_number'];
				} else {
					if ( ! empty( $data_counpon['discount_percenr'] ) ) {
						$total_discount += ( $total * $data_counpon['discount_percenr'] ) / 100;
					}
				}
			}
		}

		return floatval( $total_discount );
	}
}

EL_Cart::instance();