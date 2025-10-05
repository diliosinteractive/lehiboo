<?php
defined( 'ABSPATH' ) || exit;


if ( ! function_exists('el_package_get_payment_active') ) {
	function el_package_get_payment_active(){
		$woo_active = EL()->options->package->get('woo_active','yes');
		$paypal_active = EL()->options->package->get('paypal_active','no');
		$stripe_active = EL()->options->package->get('stripe_active','no');
		$list_payment = array(
			'woo' => $woo_active,
			'stripe' => $stripe_active,
			'paypal' => $paypal_active,
		);
		$filter_list = array_filter( $list_payment,function($value){
			return $value === 'yes';
		});
		$list_payment_active = array_keys( $filter_list );
		return $list_payment_active;
	}
}

if ( ! function_exists('el_package_check_paypal_completed') ) {
	function el_package_check_paypal_completed(){
		$public_key = EL()->options->checkout->get('paypal_public_key','');
		$secret_key = EL()->options->checkout->get('paypal_secret_key','');
		if ( empty( $public_key ) || empty( $secret_key ) ) {
			return false;
		} else {
			return true;
		}
	}
}

if ( ! function_exists('el_package_get_membership_ids_by_order_id') ) {
	function el_package_get_membership_ids_by_order_id( $order_id ){
		$args = array(
			'post_type' 	=> 'manage_membership',
			'post_status' 	=> 'any',
			'meta_key' 		=> OVA_METABOX_EVENT.'transaction_id',
			'meta_value' 	=> $order_id,
			'fields' 		=> 'ids',
		);
		$membership_ids = get_posts( $args );
		return $membership_ids;
	}
}