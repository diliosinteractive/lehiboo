<?php
defined( 'ABSPATH' ) || exit;
if( !function_exists('get_total') ){
	function get_total( $id_event = null, $cart = [], $coupon = null ){
		return EL_Cart::instance()->get_total( $id_event, $cart, $coupon );
	}
}

if( !function_exists('get_total_after_tax') ){
	function get_total_after_tax( $total_before_tax = 0, $id_event  = null ){
		return EL_Cart::instance()->get_total_after_tax( $total_before_tax, $id_event );
	}
}

if( !function_exists('sanitize_cart') ){
	function sanitize_cart( $cart = [] ){
		return EL_Cart::instance()->sanitize_cart( $cart );
	}
}

if( !function_exists('sanitize_cart_map') ){
	function sanitize_cart_map( $cart = [] ){
		return EL_Cart::instance()->sanitize_cart_map( $cart );
	}
}

if( !function_exists('sanitize_list_checkout_field') ){
	function sanitize_list_checkout_field( $arr_list_ckf = [] ){
		return EL_Cart::instance()->sanitize_list_checkout_field( $arr_list_ckf );
	}
}

if( !function_exists('sanitize_data_customers') ){
	function sanitize_data_customers( $data_customers = [] ){
		return EL_Cart::instance()->sanitize_data_customers( $data_customers );
	}
}