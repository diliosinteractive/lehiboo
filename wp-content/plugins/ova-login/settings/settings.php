<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class OVALG_Settings {

	public static function login_page(){
		$ops = get_option('ovalg_options');
		return isset( $ops['login_page'] ) ? $ops['login_page'] : '';
	}

	public static function login_success_page(){
		$ops = get_option('ovalg_options');
		return isset( $ops['login_success_page'] ) ? $ops['login_success_page'] : '';
	}
	

	public static function register_page(){
		$ops = get_option('ovalg_options');
		return isset( $ops['register_page'] ) ? $ops['register_page'] : '';
	}

	public static function forgot_password_page(){
		$ops = get_option('ovalg_options');
		return isset( $ops['forgot_password_page'] ) ? $ops['forgot_password_page'] : '';
	}

	public static function pick_new_password_page(){
		$ops = get_option('ovalg_options');
		return isset( $ops['pick_new_password_page'] ) ? $ops['pick_new_password_page'] : '';
	}

	public static function term_condition_page_id(){
		$ops = get_option('ovalg_options');
		return isset( $ops['term_condition_page_id'] ) ? $ops['term_condition_page_id'] : '';
	}

	public static function admin_approve_vendor(){
		$ops = get_option('ovalg_options');
		return isset( $ops['admin_approve_vendor'] ) ? $ops['admin_approve_vendor'] : 'no';
	}
	
	public static function show_email_confirm(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_email_confirm'] ) ? $ops['show_email_confirm'] : 'yes';
	}

	public static function show_first_name(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_first_name'] ) ? $ops['show_first_name'] : 'yes';
	}

	public static function show_last_name(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_last_name'] ) ? $ops['show_last_name'] : 'yes';
	}

	public static function show_password(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_password'] ) ? $ops['show_password'] : 'yes';
	}

	public static function show_phone(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_phone'] ) ? $ops['show_phone'] : 'yes';
	}

	public static function show_website(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_website'] ) ? $ops['show_website'] : 'no';
	}

	public static function show_job(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_job'] ) ? $ops['show_job'] : 'yes';
	}

	public static function show_address(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_address'] ) ? $ops['show_address'] : 'yes';
	}

	public static function show_description(){
		$ops = get_option('ovalg_options');
		return isset( $ops['show_description'] ) ? $ops['show_description'] : 'yes';
	}

	public static function recapcha_type(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_type'] ) ? $ops['recapcha_type'] : 'v2';
	}

	public static function recapcha_site_key(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_site_key'] ) ? $ops['recapcha_site_key'] : '';
	}

	public static function recapcha_secret_key(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_secret_key'] ) ? $ops['recapcha_secret_key'] : '';
	}

	public static function recapcha_enable_login(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_login'] ) ? $ops['recapcha_enable_login'] : '';
	}

	public static function recapcha_enable_register(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_register'] ) ? $ops['recapcha_enable_register'] : '';
	}

	public static function recapcha_enable_lost_password(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_lost_password'] ) ? $ops['recapcha_enable_lost_password'] : '';
	}

	public static function recapcha_enable_reset_password(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_reset_password'] ) ? $ops['recapcha_enable_reset_password'] : '';
	}

	public static function recapcha_enable_comment_form(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_comment_form'] ) ? $ops['recapcha_enable_comment_form'] : '';
	}

	public static function recapcha_enable_send_mail_vendor(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_send_mail_vendor'] ) ? $ops['recapcha_enable_send_mail_vendor'] : '';
	}

	public static function recapcha_enable_create_event(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_create_event'] ) ? $ops['recapcha_enable_create_event'] : '';
	}

	public static function recapcha_enable_cart_event(){
		$ops = get_option('ovalg_options');
		return isset( $ops['recapcha_enable_cart_event'] ) ? $ops['recapcha_enable_cart_event'] : '';
	}

	public static function enable_send_new_vendor_email(){
		$ops = get_option('ovalg_options');
		return isset( $ops['enable_send_new_vendor_email'] ) ? $ops['enable_send_new_vendor_email'] : '';
	}
	
	public static function mail_new_vendor_subject(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_subject'] ) ? $ops['mail_new_vendor_subject'] : esc_attr__( 'Notice of new registrar', 'ova-login' );
	}

	public static function mail_new_vendor_from_name(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_from_name'] ) ? $ops['mail_new_vendor_from_name'] : esc_attr__( 'Notice of new registrar', 'ova-login' );
	}

	public static function mail_new_vendor_from_email(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_from_email'] ) ? $ops['mail_new_vendor_from_email'] : get_option( 'admin_email' );
	}

	public static function mail_new_vendor_recipient(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_recipient'] ) ? $ops['mail_new_vendor_recipient'] : get_option( 'admin_email' );
	}
	
	public static function mail_new_vendor_send_admin(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_send_admin'] ) ? $ops['mail_new_vendor_send_admin'] : '';
	}
	
	public static function mail_new_vendor_content(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_new_vendor_content'] ) ? $ops['mail_new_vendor_content'] : esc_html__( 'The account with email address [user_email] has requested to become a vendor at [your_website], please visit the [approve_url] link for approval.', 'ova-login' );
	}

	// Reject

	public static function mail_vendor_reject_subject(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_reject_subject'] ) ? $ops['mail_vendor_reject_subject'] : esc_html__( 'Reject registration Vendor', 'ova-login' );
	}

	public static function mail_vendor_reject_from_name(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_reject_from_name'] ) ? $ops['mail_vendor_reject_from_name'] : esc_html__( 'Reject registration Vendor', 'ova-login' );
	}

	public static function mail_vendor_reject_from_email(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_reject_from_email'] ) ? $ops['mail_vendor_reject_from_email'] : get_option( 'admin_email' );
	}
	
	public static function mail_vendor_reject_content(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_reject_content'] ) ? $ops['mail_vendor_reject_content'] : esc_html__( 'Vendor registration has been denied. Here are some reasons: [reason]', 'ova-login' );
	}

	// Approve

	public static function mail_vendor_approve_subject(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_approve_subject'] ) ? $ops['mail_vendor_approve_subject'] : esc_html__( 'Approved registration Vendor', 'ova-login' );
	}

	public static function mail_vendor_approve_from_name(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_approve_from_name'] ) ? $ops['mail_vendor_approve_from_name'] : esc_html__( 'Approved registration Vendor', 'ova-login' );
	}

	public static function mail_vendor_approve_from_email(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_approve_from_email'] ) ? $ops['mail_vendor_approve_from_email'] : get_option( 'admin_email' );
	}
	
	public static function mail_vendor_approve_content(){
		$ops = get_option('ovalg_options');
		return isset( $ops['mail_vendor_approve_content'] ) ? $ops['mail_vendor_approve_content'] : esc_html__( 'Congratulations, you have successfully registered a vendor. You have your besiness setup now. [my_account]', 'ova-login' );
	}
	

}

new OVALG_Settings();