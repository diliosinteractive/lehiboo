<?php if ( !defined( 'ABSPATH' ) ) { exit(); }

class EL_Payment_Woo extends EL_Abstract_Payment{
	public $id = 'woo';

	public $booking_id = null;
	
	function __construct(){
		$this->_title = esc_html__( 'Woocommerce', 'eventlist' );

		$this->booking_id = EL()->cart_session->get( 'booking_id' );

		if ( $this->booking_id ) {
	        // Add Extra Fields to cart
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'el_add_extra_data_to_cart_item' ), 10, 3 );
		}
		
		// Display Extra fields to cart
		add_filter( 'woocommerce_get_item_data', array( $this, 'el_display_extra_data_cart' ), 10, 2 );

		// Add Extra fields to Order in woo
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'el_add_extra_data_to_order_items' ), 10, 4 );


		// Update some fields in Billing detail
		add_filter( 'woocommerce_checkout_fields', array( $this, 'el_override_checkout_fields' ) );
		add_filter( 'woocommerce_billing_fields', array( $this, 'el_default_value_billing_fields' ) );
		

		// Show/hide Billing detail
		if( apply_filters( 'el_hidden_fields_checkout_woo', false ) ){
			add_action( 'wp_head', array( $this, 'el_hook_checkout_hide' ) );
		}

		// Validate checkout
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'el_validate_checkout_woo' ), 10, 2 );

		// Change before calculate cart
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'el_woo_before_calculate_totals' ), 10, 1 );

		// Payment success
		$allow_add_ticket_by_order = EL()->options->checkout->get( 'allow_add_ticket_by_order', array( 'wc-completed', 'wc-processing' ) );

		if ( in_array( 'wc-completed', $allow_add_ticket_by_order ) ) {
			add_action( 'woocommerce_order_status_completed', array( $this, 'el_order_status_completed' ), 10, 1 );
		}

		if ( in_array( 'wc-processing', $allow_add_ticket_by_order ) ) {
			add_action( 'woocommerce_order_status_processing', array( $this, 'el_order_status_completed' ), 10, 1 );
		}

		if ( in_array( 'wc-on-hold', $allow_add_ticket_by_order ) ) {
			add_action( 'woocommerce_order_status_on-hold', array( $this, 'el_order_status_completed' ), 10, 1 );    
		}

		// Update status Holding Ticket
		if ( EL()->options->checkout->get('checkout_holding_ticket', 'no') === 'yes' ) {
			add_action( 'woocommerce_order_status_completed', array( $this, 'el_order_update_status_holding_ticket' ), 10, 1 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'el_order_update_status_holding_ticket' ), 10, 1 );
			add_action( 'woocommerce_order_status_on-hold', array( $this, 'el_order_update_status_holding_ticket' ), 10, 1 );
		}

		// Attachment file to email
		add_filter( 'woocommerce_email_attachments', array( $this, 'el_woocommerce_attachments' ), 10, 3 );

		// Send mail to recipient
		add_filter( 'woocommerce_email_recipient_customer_on_hold_order', array( $this, 'el_woocommerce_email_recipient' ), 10, 2);
		add_filter( 'woocommerce_email_recipient_customer_processing_order', array( $this, 'el_woocommerce_email_recipient' ), 10, 2);
		add_filter( 'woocommerce_email_recipient_customer_completed_order', array( $this, 'el_woocommerce_email_recipient' ), 10, 2);


     	// Filter key in Order Frontend & Backend
		add_filter( 'woocommerce_display_item_meta', array( $this, 'el_filter_woocommerce_display_item_meta' ), 10, 3 ); 
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'el_change_order_item_meta_title' ), 20, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'el_change_order_item_meta_value' ), 20, 3 );
		add_filter( 'woocommerce_display_item_meta', array( $this, 'el_woocommerce_display_item_meta' ), 10, 3 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'el_woocommerce_hidden_order_itemmeta' ), 10, 1 );

		// Countdown check-out
		add_action( 'woocommerce_after_checkout_form', array( $this, 'el_countdown_checkout' ) );


		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'el_woocommerce_checkout_cart_item_quantity' ), 10, 3 );

		// Thank you page after booking succesfully
		if( apply_filters( 'el_booking_event_thankyou', true ) &&  EL()->options->general->get( 'thanks_page_id' ) != '' ){

			add_action( 'woocommerce_thankyou', array( $this, 'el_booking_event_thankyou' ) );
			
		}

		parent::__construct();
	}

	public function el_woocommerce_checkout_cart_item_quantity( $product_quantity, $cart_item, $cart_item_key ){
		
		if ( isset( $cart_item['booking_id'] ) ) {
			$product_quantity = '';
		}

		return apply_filters( 'el_woocommerce_checkout_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
	}

	public function el_booking_event_thankyou( $order_id ){
		$order = wc_get_order( $order_id );
	    $thank_page_id = EL()->options->general->get( 'thanks_page_id' );

	    $bookingid = null;

      // Loop through order line items
		foreach( $order->get_items() as $item ){

			$bookingid = $item->get_meta( 'booking_id', true );
		}

		if( (int)$bookingid ){

			if ( ! $order->has_status( 'failed' ) ) {

				// order key
				$order_key = get_post_meta( $bookingid, OVA_METABOX_EVENT.'order_key', true );
				$url = add_query_arg( 'key', $order_key, get_permalink( $thank_page_id ) );

		        wp_safe_redirect( apply_filters( 'el_woocommerce_booking_event_url_thankyou', $url, $order_id, $bookingid ) );
		        exit;
		    }
			
		}
	}

	function el_validate_checkout_woo( $fields, $errors ){
		$booking_id = null;
		
		$cart = WC()->cart->get_cart();

		if( WC()->cart && WC()->cart->get_cart() ){
			foreach ( $cart as $cart_item_key => $cart_item ) {
				if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){
					$booking_id 	= $cart_item['booking_id'];
					break;	
				}
			}
		}

		if( !$booking_id ) return;

		$id_event 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );
		$id_cal 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_cal', true );
		$coupon 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'coupon', true );
		$seat_option 	= get_post_meta( $id_event, OVA_METABOX_EVENT.'seat_option', true );
		

		$cart_item = [];
		foreach ( $cart as $cart_item_key => $cart_item ) {
			$cart_item 	= $cart_item['cart'];
		}

		if ( $seat_option != 'map' ) {
			$validate = is_ticket_type_exist( $id_event, $id_cal, $cart_item, $coupon );
		} else {
			$validate = is_seat_map_exist( $id_event, $id_cal, $cart_item, $coupon );
		}

		if( ! $validate ) {
			$errors->add( 'validation', esc_html__( 'The ticket or seat code isn\'t available' , 'eventlist' ) );
		}
	}

	function fields(){
		return array(
			'title' => esc_html__('Woocommerce','eventlist'), // tab title
			'fields' => array(
				'fields' => array(

					array(
						'type' => 'select',
						'label' => __( 'Active', 'eventlist' ),
						'desc' => __( 'You have to active to use this gateway', 'eventlist' ),
						'atts' => array(
							'id' => 'woo_active',
							'class' => 'woo_active'
						),
						'name' => 'woo_active',
						'options' => array(
							'no' => __( 'No', 'eventlist' ),
							'yes' => __( 'Yes', 'eventlist' )
						)
					),

					array(
						'type' => 'select',
						'label' => __( 'Allow to Add ticket when Order status: ', 'eventlist' ),
						'desc' => __( 'Allow to add ticket, send ticket to customer\'s email', 'eventlist' ),
						'name' => 'allow_add_ticket_by_order',
						'atts' => array(
							'id' => 'allow_add_ticket_by_order',
							'class' => 'allow_add_ticket_by_order',
							'multiple' => 'multiple'
						),
						'options' => array(
							'wc-completed' => __( 'Completed', 'eventlist' ),
							'wc-processing' => __( 'Processing', 'eventlist' ),
							'wc-on-hold' => __( 'Hold-on', 'eventlist' )
						),
						'default' => array( 'wc-completed', 'wc-processing' )
					),
					

					array(
						'type' => 'select_woo_page',
						'label' => __( 'Choose a hidden product in Woocommerce', 'eventlist' ),
						'desc' => __( 'This allow to booking a event via WooCommerce', 'eventlist' ),
						'atts' => array(
							'id' => 'temp_product_page',
							'class' => 'temp_product_page'
						),
						'name' => 'temp_product_page',
					),

					
				),
			)
		);
	}

	function render_form(){
		echo esc_html__( 'Payment via Woocommerce', 'eventlist' );
	}

	function process(){

		WC()->cart->empty_cart();

     	// a product to cart in woocommerce
		$product_id = EL()->options->checkout->get('temp_product_page'); //replace with your own product id
		$found = false;
     	//check if product already in cart
		if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];
				if ( $_product->get_id() == $product_id )
					$found = true;
			}
         // if product not found, add it
			if ( ! $found )
				WC()->cart->add_to_cart( $product_id );
		} else {
         // if no products in cart, add it
			WC()->cart->add_to_cart( $product_id );
		}

    	// Return about checkout page in woocommerce
		$checkout_page = get_checkout_woo_page();
		return array(
			'status'    => 'success',
			'url'       => $checkout_page
		);
	}

	function el_add_extra_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
      	// Get booking id in session
		$booking_id = $this->booking_id;

		if ( $booking_id ) {
			$cart 					= get_post_meta( $booking_id, OVA_METABOX_EVENT.'cart', true );
			$event_id 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'event', true );
			$id_cal 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_cal', true );
			$date_cal 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'date_cal', true );
			$name 					= get_post_meta( $booking_id, OVA_METABOX_EVENT.'name', true );
			$first_name 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'first_name', true );
			$last_name 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'last_name', true );
			$phone 					= get_post_meta( $booking_id, OVA_METABOX_EVENT.'phone', true );
			$email 					= get_post_meta( $booking_id, OVA_METABOX_EVENT.'email', true );
			$address 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'address', true );
			$payment_method 		= get_post_meta( $booking_id, OVA_METABOX_EVENT.'payment_method', true );
			$data_checkout_field  	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'data_checkout_field', true );

			// Custom Checkoout Fields
			$list_ckf 					= get_option( 'ova_booking_form', array() );
			$data_list_ckf_extra_fields = [];
			$data_checkout_field 		= json_decode( $data_checkout_field, true );

			if ( $list_ckf && is_array( $list_ckf ) ) {
				foreach ( $list_ckf as $key => $field ) {
					$special_fields = [ 'select', 'radio', 'checkbox', 'file' ];

					if ( ! empty( $data_checkout_field ) && is_array( $data_checkout_field ) && array_key_exists( $key, $data_checkout_field ) ) {
						// Input, Textarea
						if ( ! in_array( $field['type'] , $special_fields ) ) {
							$data_list_ckf_extra_fields[$key] = array( 
								'label' => $field['label'],
								'value' => $data_checkout_field[$key],
							);
						}

						// Select
						if ( $field['type'] === 'select' ) {
							$ova_options_key = $ova_options_text = [];

							if ( array_key_exists( 'ova_options_key', $field ) ) {
								$ova_options_key = $field['ova_options_key'];
							}

							if ( array_key_exists( 'ova_options_text', $field ) ) {
								$ova_options_text = $field['ova_options_text'];
							}

							if ( ! empty( $ova_options_key ) && is_array( $ova_options_key ) ) {
								$op_k = array_search( $data_checkout_field[$key], $ova_options_key );

								if ( ! is_bool( $op_k ) ) {
                                    if ( isset( $ova_options_text[$op_k] ) && $ova_options_text[$op_k] ) {
                                    	$data_list_ckf_extra_fields[$key] = array( 
											'label' => $field['label'],
											'value' => $ova_options_text[$op_k],
										);
                                    }
                                }
							}
						}

						// Radio
						if ( $field['type'] === 'radio' ) {
							$ova_radio_key = $ova_radio_text = [];

							if ( array_key_exists( 'ova_radio_key', $field ) ) {
								$ova_radio_key = $field['ova_radio_key'];
							}

							if ( array_key_exists( 'ova_radio_text', $field ) ) {
								$ova_radio_text = $field['ova_radio_text'];
							}

							if ( ! empty( $ova_radio_key ) && is_array( $ova_radio_key ) ) {
								$radio_k = array_search( $data_checkout_field[$key], $ova_radio_key );

								if ( ! is_bool( $radio_k ) ) {
                                    if ( isset( $ova_radio_text[$radio_k] ) && $ova_radio_text[$radio_k] ) {
                                    	$data_list_ckf_extra_fields[$key] = array( 
											'label' => $field['label'],
											'value' => $ova_radio_text[$radio_k],
										);
                                    }
                                }
							}
						}

						// Checkbox
						if ( $field['type'] === 'checkbox' ) {
							$ova_checkbox_key = $ova_checkbox_text = [];

							if ( array_key_exists( 'ova_checkbox_key', $field ) ) {
								$ova_checkbox_key = $field['ova_checkbox_key'];
							}

							if ( array_key_exists( 'ova_checkbox_text', $field ) ) {
								$ova_checkbox_text = $field['ova_checkbox_text'];
							}

							if ( ! empty( $ova_checkbox_key ) && is_array( $ova_checkbox_key ) ) {
								$checkbox_key = $data_checkout_field[$key] ? explode( ', ', $data_checkout_field[$key] ) : [];

								if ( ! empty( $checkbox_key ) && is_array( $checkbox_key ) ) {
									$ckbox_val = [];

									foreach ( $checkbox_key as $ckbox_key ) {
										$checkbox_k = array_search( $ckbox_key, $ova_checkbox_key );

										if ( ! is_bool( $checkbox_k ) ) {
		                                    if ( isset( $ova_checkbox_text[$checkbox_k] ) && $ova_checkbox_text[$checkbox_k] ) {
		                                    	array_push( $ckbox_val, $ova_checkbox_text[$checkbox_k] );
		                                    }
		                                }
									}

									if ( ! empty( $ckbox_val ) && is_array( $ckbox_val ) ) {
										$data_list_ckf_extra_fields[$key] = array( 
											'label' => $field['label'],
											'value' => implode( ', ', $ckbox_val ),
										);
									}
								}
							}
						}

						// File
						if ( $field['type'] === 'file' ) {
							if ( $data_checkout_field[$key] ) {
								$data_list_ckf_extra_fields[$key] = array( 
									'label' => $field['label'],
									'value' => '<a href="'.esc_url( $data_checkout_field[$key] ).'" target="_blank">'.wp_basename( $data_checkout_field[$key] ).'</a>',
								);
							}
						}
					}
				}
			}
			
			$cart_extra_fields = array(
				'date_cal'   		=> $date_cal, 
				'name'       		=> $name,
				'first_name' 		=> $first_name,
				'last_name' 		=> $last_name,
				'phone'      		=> $phone, 
				'email'      		=> $email,
				'address'    		=> $address,
				'booking_id' 		=> $booking_id,
				'event_id' 			=> $event_id,
				'id_cal' 			=> $id_cal,
				'custom_checkout' 	=> json_encode( $data_list_ckf_extra_fields ),
				'cart'       		=> $cart
			);

			$cart_extra_fields = apply_filters( 'el_cart_extra_fields', $cart_extra_fields );

			foreach ( $cart_extra_fields as $key => $value ) {
				if ($value == '') {
					unset($cart_item_data[$key]);
				}
				$cart_item_data[$key] = $value;
			}
		}

		return $cart_item_data;
	}

	function el_display_extra_data_cart( $item_data, $cart_item ) {
		if ( empty( $cart_item['booking_id'] ) ) {
	        return $item_data;
	    }

		$booking_id = $cart_item['booking_id'];

		if ( $booking_id ) {
			$cart     				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'cart', true ) ;
			$date_cal 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'date_cal', true );
			$name     				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'name', true );
			$phone    				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'phone', true );
			$email    				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'email', true );
			$address  				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'address', true );
			$data_checkout_field  	= get_post_meta( $booking_id, OVA_METABOX_EVENT.'data_checkout_field', true );
			$id_event 				= get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );
			$seat_option 			= get_post_meta( $id_event, OVA_METABOX_EVENT.'seat_option', true );
			$extra_service 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'extra_service', true );
			$cart_ticket_type = array();

			if ( $seat_option != 'map' ) {
				if ( $cart ) {
					foreach ( $cart as $key => $value ) {
						$cart_ticket_type[$key]['name'] = $value['name'];
						$cart_ticket_type[$key]['qty']  = '<strong>'.esc_html__('Qty:', 'eventlist').'</strong>'.' '.$value['qty'];
						$cart_ticket_type[$key]['seat'] = '<strong>'.esc_html__('Seat:', 'eventlist').'</strong>'.' ';

						$seats = '';

						if ( isset($value['seat']) && is_array( $value['seat'] ) ) {
							$seats = implode(', ', $value['seat']);
						}
						$seats = !empty($seats) ? $seats : esc_html__('auto', 'eventlist');

						$cart_ticket_type[$key]['seat'] .= $seats;
					}
				}
			} else {
				$seats = [];
				$cart_ticket_type['seat'] = '';

				foreach ( $cart as $value ) {
					if ( isset( $value['data_person'] ) ) {
						$data_person = [];
						foreach ( $value['data_person'] as $k => $val ) {
							if ( (int) $val['qty'] != 0 ) {
								$data_person[] = $val['name'].' x'.$val['qty'];
							}
						}
						$seats[] = $value['id'].': '. implode(', ', $data_person);
					} else {
						$person_type = isset( $value['person_type'] ) ? $value['person_type'] : '';
						$qty = isset( $value['qty'] ) ? $value['qty'] : '';
						if ( ! empty( $person_type ) ) {
							$seats[] = $value['id'].' - '.$person_type;
						} else {
							
							if ( $qty ) {
								$seats[] = $value['id'].' x'.$qty;
							} else {
								$seats[] = $value['id'];
							}
						}
						
					}
				}

				$seats = implode(', ', $seats);
				$cart_ticket_type['seat'] .= $seats;
			}
			
			$list_ckf 					= get_option( 'ova_booking_form', array() );
			$data_list_ckf_extra_fields = [];
			$data_checkout_field 		= json_decode( $data_checkout_field, true );

			if ( $list_ckf && is_array( $list_ckf ) ) {
				$special_fields = [ 'select', 'radio', 'checkbox', 'file' ];

				foreach ( $list_ckf as $key => $field ) {
					if ( ! empty( $data_checkout_field ) && is_array( $data_checkout_field ) && array_key_exists( $key, $data_checkout_field ) ) {

						// Input, Textarea
						if ( ! in_array( $field['type'] , $special_fields ) ) {
							$data_list_ckf_extra_fields[$field['label']] = $data_checkout_field[$key];
						}

						// Select
						if ( $field['type'] === 'select' ) {
							$ova_options_key = $ova_options_text = [];

							if ( array_key_exists( 'ova_options_key', $field ) ) {
								$ova_options_key = $field['ova_options_key'];
							}

							if ( array_key_exists( 'ova_options_text', $field ) ) {
								$ova_options_text = $field['ova_options_text'];
							}

							if ( ! empty( $ova_options_key ) && is_array( $ova_options_key ) ) {
								$op_k = array_search( $data_checkout_field[$key], $ova_options_key );

								if ( ! is_bool( $op_k ) ) {
                                    if ( isset( $ova_options_text[$op_k] ) && $ova_options_text[$op_k] ) {
                                    	$data_list_ckf_extra_fields[$field['label']] = $ova_options_text[$op_k];
                                    }
                                }
							}
						}

						// Radio
						if ( $field['type'] === 'radio' ) {
							$ova_radio_key = $ova_radio_text = [];

							if ( array_key_exists( 'ova_radio_key', $field ) ) {
								$ova_radio_key = $field['ova_radio_key'];
							}

							if ( array_key_exists( 'ova_radio_text', $field ) ) {
								$ova_radio_text = $field['ova_radio_text'];
							}

							if ( ! empty( $ova_radio_key ) && is_array( $ova_radio_key ) ) {
								$radio_k = array_search( $data_checkout_field[$key], $ova_radio_key );

								if ( ! is_bool( $radio_k ) ) {
                                    if ( isset( $ova_radio_text[$radio_k] ) && $ova_radio_text[$radio_k] ) {
                                    	$data_list_ckf_extra_fields[$field['label']] = $ova_radio_text[$radio_k];
                                    }
                                }
							}
						}

						// Checkbox
						if ( $field['type'] === 'checkbox' ) {
							$ova_checkbox_key = $ova_checkbox_text = [];

							if ( array_key_exists( 'ova_checkbox_key', $field ) ) {
								$ova_checkbox_key = $field['ova_checkbox_key'];
							}

							if ( array_key_exists( 'ova_checkbox_text', $field ) ) {
								$ova_checkbox_text = $field['ova_checkbox_text'];
							}

							if ( ! empty( $ova_checkbox_key ) && is_array( $ova_checkbox_key ) ) {
								$checkbox_key = $data_checkout_field[$key] ? explode( ', ', $data_checkout_field[$key] ) : [];

								if ( ! empty( $checkbox_key ) && is_array( $checkbox_key ) ) {
									$ckbox_val = [];

									foreach ( $checkbox_key as $ckbox_key ) {
										$checkbox_k = array_search( $ckbox_key, $ova_checkbox_key );

										if ( ! is_bool( $checkbox_k ) ) {
		                                    if ( isset( $ova_checkbox_text[$checkbox_k] ) && $ova_checkbox_text[$checkbox_k] ) {
		                                    	array_push( $ckbox_val, $ova_checkbox_text[$checkbox_k] );
		                                    }
		                                }
									}

									if ( ! empty( $ckbox_val ) && is_array( $ckbox_val ) ) {
										$data_list_ckf_extra_fields[$field['label']] = implode( ', ', $ckbox_val );
									}
								}
							}
						}

						// File
						if ( $field['type'] === 'file' ) {
							if ( $data_checkout_field[$key] ) {
								$data_list_ckf_extra_fields[$field['label']] = '<a href="'.esc_url( $data_checkout_field[$key] ).'" target="_blank">'.wp_basename( $data_checkout_field[$key] ).'</a>';
							}
						}
					}
				}
			}

			$cart_extra_fields = array(
				esc_html__('Date','eventlist')  	=> $date_cal, 
				esc_html__('Name','eventlist')  	=> $name, 
				esc_html__('Phone','eventlist') 	=> $phone, 
				esc_html__('Email','eventlist') 	=> $email, 
				esc_html__('Address','eventlist') 	=> $address, 
			);

			if ( ! empty( $data_list_ckf_extra_fields ) && is_array( $data_list_ckf_extra_fields ) ) {
				$cart_extra_fields = array_merge_recursive( $cart_extra_fields, $data_list_ckf_extra_fields );
			}

			$cart_extra_fields = apply_filters( 'el_cart_extra_fields', $cart_extra_fields );

			foreach ( $cart_extra_fields as $key => $value ) {
				$item_data[] = array(
					'key'     => $key,
					'value'   => ! empty( $value ) && is_array( $value ) ? $value[0] : wc_clean( $value),
					'display' => '',
				);
			}

			foreach ( $item_data as $key => $value) {
				if ($value['value'] == '') {
					unset($item_data[$key]);
				}
			}

			if ( $seat_option != 'map' ) {
				$data_ticket_type = [];

				foreach ( $cart_ticket_type as $key => $value) {
					if ( $seat_option == 'none' ) {
						$type = $value['name'].' - '.$value['qty'];
					} else {
						$type = $value['name'].' - '.$value['qty'].' - '.$value['seat'].' ';
					}
					
					array_push( $data_ticket_type, $type );
				}

				$item_data[] = array(
					'key'     => esc_html__('Ticket Type', 'eventlist'),
					'value'   => implode( ', ', $data_ticket_type ),
					'display' => '',
				);
			} else {
				foreach ( $cart_ticket_type as $key => $value ) {
					$item_data[] = array(
						'key'     => esc_html__('Seat', 'eventlist'),
						'value'   => $value,
						'display' => '',
					);
				}
			}

			$extra_service_display = el_extra_sv_get_info_booking( $extra_service );
			
			if ( ! empty( $extra_service_display ) ) {
				$item_data[] = array(
					'key' => __( 'Extra Services', 'eventlist' ),
					'value' => $extra_service_display,
				);
			}

		}

		return $item_data;
	}

	function el_override_checkout_fields( $fields ) {
		$booking_id 	= null;
		$order_comments = '';

		if ( WC()->cart && WC()->cart->get_cart() ) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){

					$custom_checkout 	= isset( $cart_item['custom_checkout'] ) ? json_decode( $cart_item['custom_checkout'], true ) : '';
					if( is_array( $custom_checkout ) && ! empty( $custom_checkout ) ) {
						foreach( $custom_checkout as $key => $value ) {
							if( $key == 'order_comments' ) $order_comments = $value['value'];
						}
					}

					$booking_id 	= $cart_item['booking_id'];
					break;	
				}
			}
		}

		if( !$booking_id ) return $fields;

		$fields['order']['order_comments']['default'] = $order_comments;
		
		if( apply_filters( 'el_wce_unset_billing_first_name', false ) ){
			unset($fields['billing']['billing_first_name']);	
		}

		if( apply_filters( 'el_wce_unset_billing_last_name', false ) ){
			unset($fields['billing']['billing_last_name']);	
		}

		if( apply_filters( 'el_wce_unset_billing_phone', false ) ){
			unset($fields['billing']['billing_phone']);	
		}
		
		
		if( apply_filters( 'el_wce_unset_billing_company', false ) ){
			unset($fields['billing']['billing_company']);
		}
		if( apply_filters( 'el_wce_unset_billing_address_1', false ) ){
			unset($fields['billing']['billing_address_1']);
		}

		if( apply_filters( 'el_wce_unset_billing_address_2', false ) ){
			unset($fields['billing']['billing_address_2']);
		}
		if( apply_filters( 'el_wce_unset_billing_city', false ) ){
			unset($fields['billing']['billing_city']);
		}
		if( apply_filters( 'el_wce_unset_billing_postcode', false ) ){
			unset($fields['billing']['billing_postcode']);
		}
		if( apply_filters( 'el_wce_unset_billing_country', false ) ){
			unset($fields['billing']['billing_country']);
		}

		if( apply_filters( 'el_wce_unset_billing_state', false ) ){
			unset($fields['billing']['billing_state']);
		}
		if( apply_filters( 'el_wce_unset_order_comments', false ) ){
			unset($fields['order']['order_comments']);
		}
		if( apply_filters( 'el_wce_unset_account_username', false ) ){
			unset($fields['account']['account_username']);
		}
		if( apply_filters( 'el_wce_unset_account_password', false ) ){
			unset($fields['account']['account_password']);
		}
		if( apply_filters( 'el_wce_unset_account_password_2', false ) ){
			unset($fields['account']['account_password-2']);
		}

		return $fields;
	}

	function el_default_value_billing_fields($fields) {
		$first_name = $last_name = $phone = $email = $address = '';
		$booking_id = null;
		
		if ( WC()->cart && WC()->cart->get_cart() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$first_name = isset( $cart_item['first_name'] ) ? $cart_item['first_name'] : '';
				$last_name 	= isset( $cart_item['last_name'] ) ? $cart_item['last_name'] : '';
				$phone 		= isset( $cart_item['phone'] ) ? $cart_item['phone'] : '' ;
				$email 		= isset( $cart_item['email'] ) ? $cart_item['email'] : '';
				$address 	= isset( $cart_item['address'] ) ? $cart_item['address'] : '';
				$booking_id = isset( $cart_item['booking_id'] ) ? $cart_item['booking_id'] : '';
			}
		}

		if ( ! $booking_id ) return $fields;
		
		$fields['billing_first_name']['default'] 	= $first_name;
		$fields['billing_last_name']['default'] 	= $last_name;
		$fields['billing_phone']['default'] 		= $phone;
		$fields['billing_email']['default'] 		= $email;
		$fields['billing_address_1']['default'] 	= $address;

		return $fields;
	}

	function el_add_extra_data_to_order_items( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['booking_id'] ) ) {
			return;
		}

		$booking_id 		= $values['booking_id'];
		$id_event 			= get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );
		$seat_option 		= get_post_meta( $id_event, OVA_METABOX_EVENT.'seat_option', true );
		$cart_ticket_type 	= array();
		$data_extra_service = get_post_meta( $booking_id, OVA_METABOX_EVENT.'extra_service', true );

		if ( $seat_option != 'map' ) {
			foreach ( $values['cart'] as $key => $value ) {
				$cart_ticket_type[$key]['name'] = $value['name'];
				$cart_ticket_type[$key]['qty']  = '<strong>'.esc_html__('Qty:', 'eventlist').'</strong>'.' '.$value['qty'];
				$cart_ticket_type[$key]['seat'] = '<strong>'.esc_html__('Seat:', 'eventlist').'</strong>'.' ';

				$seats = '';
				if( isset($value['seat']) && is_array( $value['seat'] ) ){
					$seats = implode(', ', $value['seat']);
				}
				$seats = !empty($seats) ? $seats : esc_html__('auto', 'eventlist');
				$cart_ticket_type[$key]['seat'] .= $seats;
			}
		} else {
			$seats = [];
			$cart_ticket_type['seat'] = '';
			foreach ($values['cart'] as $value ) {
				
				if ( isset( $value['data_person'] ) ) {
					$data_person = [];
					foreach ( $value['data_person'] as $k => $val ) {
						if ( (int) $val['qty'] != 0 ) {
							$data_person[] = $val['name'].' x'.$val['qty'];
						}
					}
					$seats[] = $value['id'].': '. implode(', ', $data_person);
				} else {
					$person_type = isset( $value['person_type'] ) ? $value['person_type'] : '';
					if ( ! empty( $person_type ) ) {
						$seats[] = $value['id'].' - '.$person_type;
					} else {
						$seats[] = $value['id'];
					}
				}
			}
			$seats = implode(', ', $seats);
			$cart_ticket_type['seat'] .= $seats;
		}

		if( $cart_ticket_type ){
			$cart = '';
			if ($seat_option != 'map') {
				foreach ($cart_ticket_type as $key => $value) {
					if ($seat_option == 'none') {
						$cart .= $value['name'].' '.$value['qty'].'<br/> ';
					} else {
						$cart .= $value['name'].' '.$value['qty'].' '.$value['seat'].'<br/> ';
					}
				}
			} else {
				$cart .= $seats.'<br/> ';
			}
		}

		$list_custom_checkout = [];
		if( isset( $values['custom_checkout'] ) ) {
			$list_custom_checkout = json_decode( $values['custom_checkout'], true );
		}

		$item->add_meta_data( 'order_md_date', $values['date_cal'] );
		$item->add_meta_data( 'order_md_name', $values['name'] );
		$item->add_meta_data( 'order_md_phone', $values['phone'] );
		$item->add_meta_data( 'order_md_email', $values['email'] );
		$item->add_meta_data( 'order_md_address', $values['address'] );
		$item->add_meta_data( 'order_md_custom_checkout', $values['custom_checkout'] );

		if( is_array( $list_custom_checkout ) && ! empty( $list_custom_checkout ) ) {
			foreach( $list_custom_checkout as $key => $value ) {
				$item->add_meta_data( 'order_md_' . $key, $value['value'] );
			}
		}

		$item->add_meta_data( 'order_md_cart', $cart );

		$extra_service_display = el_extra_sv_get_info_booking( $data_extra_service );

		if ( ! empty( $extra_service_display ) ) {
			$item->add_meta_data(__( 'Extra Services', 'eventlist' ),$extra_service_display,true);
		}

		$item->add_meta_data( 'booking_id', $values['booking_id'] );
	}

	function el_woo_before_calculate_totals( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;

		$booking_id = '';

		if( WC()->cart && WC()->cart->get_cart() ){
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){
					$booking_id 	= $cart_item['booking_id'];
					break;	
				}
			}
		}	

		if( $booking_id ){

			$event_id = get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );
			$event_obj = el_get_event( $event_id );
			$total_price = get_post_meta( $booking_id, OVA_METABOX_EVENT.'total_after_tax', true );


         // Loop through cart items
			foreach ( $cart->get_cart() as $cart_item ) {

          	// Get an instance of the WC_Product object
				$product = $cart_item['data'];

          	// Get the product name (Added Woocommerce 3+ compatibility)
				$original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;

          	// SET THE NEW NAME
				if( is_object( $event_obj ) && isset( $event_obj->post_title ) ){

					$new_name = $event_obj->post_title;    
               // Set the new name (WooCommerce versions 2.5.x to 3+)
					if( method_exists( $product, 'set_name' ) )
						$product->set_name( $new_name );
					else
						$product->post->post_title = $new_name;
				}

				$cart_item['data']->set_price( $total_price );

			}

		}
	}

	function el_order_status_completed( $orderid ){
		$bookingid = null;

		$order = wc_get_order( $orderid );

      	// Loop through order line items
		foreach( $order->get_items() as $item ){
         	// get order item data (in an unprotected array)
			$item_data = $item->get_data();

         	// get order item meta data (in an unprotected array)
			$item_meta_data = $item->get_meta_data();

         	// get only additional meta data (formatted in an unprotected array)
			$formatted_meta_data = $item->get_formatted_meta_data();

			$bookingid = $item->get_meta( 'booking_id', true );
		}

		if ( (int)$bookingid ) {
			if ( apply_filters( 'el_new_order_use_system_mail', true ) ) {
				add_action( 'woocommerce_email', array( $this, 'el_unhook_those_pesky_emails' ) );
			}

			EL_Booking::instance()->booking_success( $bookingid, 'woo', $orderid );

			add_action( 'woocommerce_email_before_order_table', array( $this, 'el_email_before_order_table' ), 10, 4 );
		}
	}

	/* Update Status Holding Ticket */
	function el_order_update_status_holding_ticket( $orderid ) {
		$booking_id = null;

		$order = wc_get_order( $orderid );

      	// Loop through order line items
		foreach( $order->get_items() as $item ){
         	// get order item data (in an unprotected array)
			$item_data = $item->get_data();

         	// get order item meta data (in an unprotected array)
			$item_meta_data = $item->get_meta_data();

         	// get only additional meta data (formatted in an unprotected array)
			$formatted_meta_data = $item->get_formatted_meta_data();

			$booking_id = $item->get_meta( 'booking_id', true );
		}

		if ( (int)$booking_id ) {
			// Update Status in booking
			if ( apply_filters( 'el_ft_order_update_status_holding_ticket', true ) ) {
				update_post_meta( $booking_id, OVA_METABOX_EVENT.'status_holding_ticket', 'Completed', 'Pending' );
			}

			// Remove holding ticket when Complete payment
			$agrs = [
				'post_type' 		=> 'holding_ticket',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'fields' 			=> 'ids',
				'meta_query' 		=> array(
					array(
						'key' 		=> OVA_METABOX_EVENT.'booking_id',
						'value' 	=> $booking_id,
						'compare' 	=> '='	
					),
				),
			];

			$holding_tickets = get_posts( $agrs );

			if ( ! empty( $holding_tickets ) && is_array( $holding_tickets ) ) {
				foreach ( $holding_tickets as $ht_id ) {
					wp_delete_post( $ht_id );
				}
			}
		}
	}

	/* Change at frontend */

	function el_filter_woocommerce_display_item_meta( $html, $item, $args ) { 

		$html = str_replace('order_md_date', esc_html__('Date', 'eventlist') , $html );
		$html = str_replace('order_md_name', esc_html__('Name', 'eventlist') , $html );
		$html = str_replace('order_md_phone', esc_html__('Phone', 'eventlist') , $html );
		$html = str_replace('order_md_email', esc_html__('Email', 'eventlist') , $html );
		$html = str_replace('order_md_address', esc_html__('Address', 'eventlist') , $html );
		$html = str_replace('order_md_cart', esc_html__('Ticket Type', 'eventlist') , $html );
		$html = str_replace('booking_id', esc_html__('Booking ID', 'eventlist') , $html );

		return $html;
	}

	/* Change Order at backend */
	/**
     * Changing a meta title
     * @param  string        $key  The meta key
     * @param  WC_Meta_Data  $meta The meta object
     * @param  WC_Order_Item $item The order item object
     * @return string        The title
     */
	function el_change_order_item_meta_title( $key, $meta, $item ) {

     	// By using $meta-key we are sure we have the correct one.

		$str_list_custom_checkout = $item->get_meta('order_md_custom_checkout');
		$arr_list_custom_checkout = json_decode( $str_list_custom_checkout, true );

		if( is_array( $arr_list_custom_checkout ) && ! empty( $arr_list_custom_checkout ) ) {
			foreach( $arr_list_custom_checkout as $name => $val ) {
				if( 'order_md_' . $name === $meta->key ) { $key = esc_html($val['label']); } 
			}
		}

		if ( 'order_md_date' === $meta->key ) { $key = esc_html__('Date', 'eventlist'); }
		if ( 'order_md_name' === $meta->key ) { $key = esc_html__('Name', 'eventlist'); }
		if ( 'order_md_phone' === $meta->key ) { $key = esc_html__('Phone', 'eventlist'); }
		if ( 'order_md_email' === $meta->key ) { $key = esc_html__('Email', 'eventlist'); }
		if ( 'order_md_address' === $meta->key ) { $key = esc_html__('Address', 'eventlist'); }	
		if ( 'order_md_cart' === $meta->key ) { $key = esc_html__('Ticket Type', 'eventlist'); }
		if ( 'booking_id' === $meta->key ) { $key = esc_html__('Booking ID', 'eventlist'); }


		return $key;
	}

	//Hide order_md_custom_checkout
	function el_woocommerce_display_item_meta( $html, $item, $args ){
		
		$strings = array();

	    foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
	        $value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );

	        if ( ! ( $meta->display_key === 'order_md_custom_checkout'  ) ){
	            $strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;    
	        }

	    }
	    if ( $strings ) {
	        $html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
	    }

	    return $html;
	}

	// Hide order_md_custom_checkout in Order
	function el_woocommerce_hidden_order_itemmeta( $meta_keys ) {
        $meta_keys[] = 'order_md_custom_checkout';
        return $meta_keys;
    }

	/**
     * Changing a meta value
     * @param  string        $value  The meta value
     * @param  WC_Meta_Data  $meta   The meta object
     * @param  WC_Order_Item $item   The order item object
     * @return string        The title
     */
	/* Change in mail */
	function el_change_order_item_meta_value( $value, $meta, $item ) {

        // By using $meta-key we are sure we have the correct one.
		if ( 'order_md_date' === $meta->key ) { $key = esc_html__('Date', 'eventlist'); }
		if ( 'order_md_name' === $meta->key ) { $key = esc_html__('Name', 'eventlist'); }
		if ( 'order_md_phone' === $meta->key ) { $key = esc_html__('Phone', 'eventlist'); }
		if ( 'order_md_email' === $meta->key ) { $key = esc_html__('Email', 'eventlist'); }
		if ( 'order_md_address' === $meta->key ) { $key = esc_html__('Address', 'eventlist'); }
		if ( 'order_md_cart' === $meta->key ) { $key = esc_html__('Ticket Type', 'eventlist'); }
		if ( 'booking_id' === $meta->key ) { $key = esc_html__('Booking ID', 'eventlist'); }


		return $value;
	}

	function el_woocommerce_attachments($attachments, $email_id, $order){

		$booking_id = null;

		if ( empty( $order ) ) {
	        return $attachments;
	    }

	    if ( empty( WC()->cart ) )  return $attachments;

      	// Loop through order line items
	    if( $order ){
			foreach( $order->get_items() as $item ){
	         // get order item data (in an unprotected array)
				$item_data = $item->get_data();

	         // get order item meta data (in an unprotected array)
				$item_meta_data = $item->get_meta_data();

	         // get only additional meta data (formatted in an unprotected array)
				$formatted_meta_data = $item->get_formatted_meta_data();

				$booking_id = $item->get_meta( 'booking_id', true );
			}

		}

		if( $booking_id == null ) return $attachments;

		$list_ticket_pdf_png = apply_filters( 'el_booking_mail_attachments', EL_Ticket::instance()->make_pdf_ticket_by_booking_id( $booking_id ) );

		if( is_array( $list_ticket_pdf_png ) && count( $list_ticket_pdf_png ) ){
			$attachments = array_merge( $attachments, $list_ticket_pdf_png );    
		}

		return $attachments;
	}

	function el_woocommerce_email_recipient( $recipient, $object ){
		$booking_id = '';

		if ( empty( WC()->cart ) )  return $recipient;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){
				$booking_id 	= $cart_item['booking_id'];
				break;	
			}
		}

		if( $booking_id ){

			
			$setting_mail_to = EL()->options->mail->get('new_booking_sendmail', array( 'administrator', 'event_manager' ,'customer' ) );

			$email_customer = get_post_meta( $booking_id, OVA_METABOX_EVENT.'email', true );


			$id_event = get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );
			$id_author = get_post_field( 'post_author', $id_event);
			$email_vendor = get_the_author_meta( 'user_email', $id_author );

			if (is_array($setting_mail_to) && in_array('event_manager', $setting_mail_to) ) {
				$recipient .= ', '.$email_vendor;				
			}

			if (is_array($setting_mail_to) && in_array('customer', $setting_mail_to) ) {
				$recipient .= ', '.$email_customer;
			}

			
		}

		return $recipient;
	}

	function el_hook_checkout_hide(){
		$booking_id = null;

		if( WC()->cart && WC()->cart->get_cart() ){
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){
					$booking_id = $cart_item['booking_id'];
					break;	
				}
			}
		}

		if( $booking_id ){ ?>
			<style>
				body .checkout.woocommerce-checkout #customer_details {
					display: none!important;
				}
			</style>

		<?php }
	}

	function el_email_before_order_table( $order, $sent_to_admin, $plain_text, $email ) { 
		$booking_id = null;
		$html_type_ticket_string = '';

      	// Loop through order line items
		foreach( $order->get_items() as $item ){
         // get order item data (in an unprotected array)
			$item_data = $item->get_data();

         // get order item meta data (in an unprotected array)
			$item_meta_data = $item->get_meta_data();

         // get only additional meta data (formatted in an unprotected array)
			$formatted_meta_data = $item->get_formatted_meta_data();

			$booking_id = $item->get_meta( 'booking_id', true );
		}

		$booking_status = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'status', true );

		if( $booking_id != null && $booking_status == 'Completed' ) {

			$list_qty_ticket_by_id_ticket = get_post_meta( $booking_id, OVA_METABOX_EVENT . 'list_qty_ticket_by_id_ticket', true );
			$id_event 				= get_post_meta($booking_id, OVA_METABOX_EVENT . 'id_event', true );
			$list_ticket_in_event 	= get_post_meta($id_event, OVA_METABOX_EVENT . 'ticket', true);
			$event_type 			= get_post_meta($id_event, OVA_METABOX_EVENT . 'event_type', true);

			$list_type_ticket = get_post_meta($booking_id, OVA_METABOX_EVENT . 'list_id_ticket', true);

			$seat_option = get_post_meta($id_event, OVA_METABOX_EVENT . 'seat_option', true);

			$list_name_ticket = $list_id_ticket = [];
			if ( is_array( $list_ticket_in_event ) && !empty( $list_ticket_in_event ) ) {
				foreach ( $list_ticket_in_event as $ticket ) {

					$online_info = '';
					if( $event_type == 'online' ){
						$online_info = esc_html__( 'Link:', 'eventlist' ).' '.$ticket['online_link'].' <br>'.esc_html__( 'Password:', 'eventlist' ).' '.$ticket['online_password'].' <br>'.esc_html__( 'Other info:', 'eventlist' ).' '.$ticket['online_other'];
					}

					if ( isset( $list_qty_ticket_by_id_ticket[ $ticket['ticket_id'] ] ) ) {
						$list_name_ticket[$ticket['ticket_id']] = '<br/><strong>'.$ticket['name_ticket'].'</strong>'.' - '.$list_qty_ticket_by_id_ticket[ $ticket['ticket_id'] ].' '.esc_html__( 'ticket(s)', 'eventlist' ).' <br/>'.$online_info;
					}
					
					$list_id_ticket[] = $ticket['ticket_id'];
				}
			}


			$list_id_ticket_booked = json_decode($list_type_ticket);
			$html_type_ticket = [];
			if (is_array($list_id_ticket_booked) && !empty($list_id_ticket_booked)) {
				foreach ($list_id_ticket_booked as $id_ticket) {
					if ($seat_option != 'map') {
						if (in_array($id_ticket, $list_id_ticket)) {
							$html_type_ticket[] = $list_name_ticket[$id_ticket];
						}
					}
					
				}
			}

			if( $event_type == 'online' ){
				$html_type_ticket_string = implode('<br/>', $html_type_ticket);
			}

			echo wp_kses_post( $html_type_ticket_string.'<br/><br/>' );
		} else{
			echo '';
		}
	}

	// Remove send mail in WooComemrce when booking event
	function el_unhook_those_pesky_emails( $email_class ) {

			/**
			 * Hooks for sending emails during store events
			 **/
			remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
			remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
			remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
			
			// New order emails
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
			
			// Processing order emails
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			
			// Completed order emails
			remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
				
			// Note emails
			remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
	}

	// Countdown check-out
	function el_countdown_checkout() {
		$checkout_holding_ticket = EL()->options->checkout->get('checkout_holding_ticket', 'no');

		if ( $checkout_holding_ticket === 'yes' ) {
			$product_id = EL()->options->checkout->get('temp_product_page');
			$time_countdown_checkout = intval( EL()->options->checkout->get('max_time_complete_checkout', 600) );
			$booking_id = $event_id = $id_cal = '';
			$redirect = home_url();

			if( WC()->cart && WC()->cart->get_cart() ){
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					if( isset( $cart_item['booking_id'] ) && $cart_item['booking_id'] ){
						$booking_id = $cart_item['booking_id'];
						$event_id 	= $cart_item['event_id'];
						$id_cal 	= $cart_item['id_cal'];
						break;	
					}
				}
			}

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
					if ( WC()->cart ) {
						WC()->cart->empty_cart();
					}

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
			}
		}
	}
}