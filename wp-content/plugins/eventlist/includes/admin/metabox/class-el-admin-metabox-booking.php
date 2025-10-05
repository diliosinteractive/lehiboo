<?php

defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Booking extends EL_Abstract_Metabox {
	
	public function __construct(){
		$this->_id 		= 'metabox_Booking';
		$this->_title 	= esc_html__( 'Booking order','eventlist' );
		$this->_screen 	= array( 'el_bookings' );
		$this->_output 	= EL_PLUGIN_INC . 'admin/views/metaboxes/metabox-booking.php';
		$this->_prefix 	= OVA_METABOX_EVENT;

		parent::__construct();

		add_action( 'el_mb_proccess_update_meta', array( $this, 'update' ), 10, 2 );
	}

	public function update( $post_id, $post_data ) {
		if ( empty( $post_data ) ) exit();
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
		if ( ! isset( $post_data ) ) return;
		if ( ! isset( $post_data['ova_metaboxes'] ) || ! wp_verify_nonce( $post_data['ova_metaboxes'], 'ova_metaboxes' ) ) return;

		if ( isset( $post_data['post_type'] ) && $post_data['post_type'] === 'el_bookings' ) {
			if ( isset( $post_data[$this->_prefix.'cart'] ) ) {
				$id_booking = get_the_ID();
				$id_event 	= isset( $post_data[$this->_prefix.'id_event'] ) ? $post_data[$this->_prefix.'id_event'] : '';

				if ( ! $id_event ) {
					$id_event = get_post_meta( $id_booking,  $this->_prefix.'id_event', true );
				}

				$date_cal = el_get_calendar_date( $id_event, $post_data[$this->_prefix.'id_cal'] );

				// Get all ticket type
				$tickets_type = get_post_meta( $id_event,  $this->_prefix.'ticket', true );

				$list_id_ticket = [];
				$list_qty_ticket_by_id_ticket = [];

				$seat_option = get_post_meta( $id_event, $this->_prefix.'seat_option', true ) ? get_post_meta( $id_event, $this->_prefix.'seat_option', true ) : 'none';

				$arr_seat = [];
				$arr_area = [];

				foreach ( $post_data[$this->_prefix.'cart'] as $key => $value ) {
					$list_seat_book = [];

					if ( $seat_option === 'map') {
						$list_qty_ticket_by_id_ticket[$value['id']] = isset( $value['qty'] ) && absint( $value['qty'] ) ? absint( $value['qty'] ) : 1;
						$list_id_ticket[] = $value['id'];
						$post_data[$this->_prefix.'list_seat_book'][] = $value['id'];

						if ( isset( $value['qty'] ) && absint( $value['qty'] ) ) {
							array_push( $arr_area , trim( $value['id'] ) );
						} else {
							array_push( $arr_seat , trim( $value['id'] ) );
						}
					} else {
						if ( isset( $value['seat'] ) && $value['seat'] ) {
							$list_seat_book = explode(", ", $value['seat']);
						}

						foreach ( $tickets_type as $v ) {
							if ( strtolower( $v['name_ticket'] ) === strtolower( $value['name'] ) ) {
								$list_qty_ticket_by_id_ticket[ $v['ticket_id'] ] = $value['qty'];
								$post_data[$this->_prefix.'list_seat_book'][ $v['ticket_id'] ] = $list_seat_book;
								break;
							}
						}

						$list_id_ticket[] = strtolower($value['name']);
					}
				}

				if ( ! empty( $arr_seat ) ) $post_data[$this->_prefix.'arr_seat'] = $arr_seat;
				if ( ! empty( $arr_area ) ) $post_data[$this->_prefix.'arr_area'] = $arr_area;

				$post_data[$this->_prefix.'list_id_ticket'] = json_encode( $list_id_ticket );
				$post_data[$this->_prefix.'id_event'] 		= $id_event;
				$post_data[$this->_prefix.'list_qty_ticket_by_id_ticket'] = $list_qty_ticket_by_id_ticket;
				$post_data[$this->_prefix.'date_cal'] = date_i18n( get_option( 'date_format'), strtotime( $date_cal ) );
				$post_data[$this->_prefix.'date_cal_tmp'] = strtotime( $date_cal );
			}

			foreach ( $post_data as $name => $value ) {
				if ( strpos( $name, $this->_prefix ) !== 0 ) continue;
				
				update_post_meta( $post_id, $name, $value );

				if ( $name == $this->_prefix.'status' && $value == 'Canceled' ) {
					do_action( 'el_cancel_booking_succesfully', $post_id );
				}
			}
		}
	}
}