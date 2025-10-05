<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists('ova_admin_send_mail_vendor_reject') ) {
	function ova_admin_send_mail_vendor_reject( $user_email, $message = '' ){
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";

		$body = OVALG_Settings::mail_vendor_reject_content();
		$body = str_replace('[reason]', $message, $body);

		$subject = OVALG_Settings::mail_vendor_reject_subject();

		$mail_to = $user_email;

		add_filter( 'wp_mail_from', 'wp_mail_from_reject_vendor_email' );
		add_filter( 'wp_mail_from_name', 'wp_mail_from_reject_vendor' );

		if ( wp_mail( $mail_to, $subject, $body, $headers ) ) {
			$result = true;
		} else {
			$result = false;
		}

		remove_filter( 'wp_mail_from', 'wp_mail_from_reject_vendor_email' );
		remove_filter( 'wp_mail_from_name','wp_mail_from_reject_vendor' );

		return $result;
	}
}

if ( ! function_exists('wp_mail_from_reject_vendor') ) {
	function wp_mail_from_reject_vendor(){
		return OVALG_Settings::mail_vendor_reject_from_name();
	}
}

if ( ! function_exists('wp_mail_from_reject_vendor_email') ) {
	function wp_mail_from_reject_vendor_email(){
		return OVALG_Settings::mail_vendor_reject_from_email();
	}
}

if ( ! function_exists('ova_admin_send_mail_vendor_approve') ) {
	function ova_admin_send_mail_vendor_approve( $user_email ){
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";

		$member_account = wp_login_url();
		if ( function_exists('EL') ) {
			$member_account_page_id = EL()->options->general->get('myaccount_page_id','');
			$member_account = get_permalink( $member_account_page_id );
		}
		$body = OVALG_Settings::mail_vendor_approve_content();
		if ( $member_account ) {
			$body = str_replace('[my_account]', $member_account, $body);
		}

		$subject = OVALG_Settings::mail_vendor_approve_subject();

		$mail_to = $user_email;

		add_filter( 'wp_mail_from', 'wp_mail_from_approve_vendor_email' );
		add_filter( 'wp_mail_from_name', 'wp_mail_from_approve_vendor' );

		if ( wp_mail( $mail_to, $subject, $body, $headers ) ) {
			$result = true;
		} else {
			$result = false;
		}

		remove_filter( 'wp_mail_from', 'wp_mail_from_approve_vendor_email' );
		remove_filter( 'wp_mail_from_name','wp_mail_from_approve_vendor' );

		return $result;
	}
}

if ( ! function_exists('wp_mail_from_approve_vendor') ) {
	function wp_mail_from_approve_vendor(){
		return OVALG_Settings::mail_vendor_approve_from_name();
	}
}

if ( ! function_exists('wp_mail_from_approve_vendor_email') ) {
	function wp_mail_from_approve_vendor_email(){
		return OVALG_Settings::mail_vendor_approve_from_email();
	}
}

// Send mail to User
function ova_register_mailto_user( $mail_to ) {

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";

	// Body
	if ( function_exists( 'EL' ) ) {

		$body = EL()->options->mail->get( 'mail_new_acocunt_content', esc_html__( 'You registered user [el_link_profile] successfully at [el_link_home_page]', 'ova-login' ) );

	} else {

		$body = esc_html__( 'You registered user [el_link_profile] successfully at [el_link_home_page]', 'ova-login' );

	}

	$user = get_userdata( get_user_by('email', $mail_to)->ID  );
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return;
	}

	$body = str_replace( '[el_link_home_page]', get_site_url(), $body);
	$body = str_replace( '[el_link_profile]', '<a href="'.esc_url( get_author_posts_url( get_user_by('email', $mail_to)->ID ) ).'">'.esc_html( get_author_posts_url( get_user_by('email', $mail_to)->ID ) ).'</a>', $body)."<br>";
	if ( OVALG_Settings::show_password() !== 'yes' ) {
		$body  .= sprintf( __( 'Username: %s' ), $user->user_login ) . "<br>";
		$body .= __( 'To set your password, visit the following address:' ) . "<br>";
		$body .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "<br>";

		$body .= wp_login_url() . "\r\n";
	}

	$subject = esc_html__( 'Register user successlly', 'ova-login' );


	add_filter( 'wp_mail_from', 'wp_mail_from_new_account' );
	add_filter( 'wp_mail_from_name', 'wp_mail_from_name_new_account_user' );

	if ( wp_mail( $mail_to, $subject, $body, $headers ) ) {
		$result = true;
	} else {
		$result = false;
	}

	remove_filter( 'wp_mail_from', 'wp_mail_from_new_account' );
	remove_filter( 'wp_mail_from_name','wp_mail_from_name_new_account_user' );

	return $result;

}



// Send mail to Admin
function ova_register_mailto_admin ( $type_user, $mail_to ) {


	$headers[] = "MIME-Version: 1.0\r\n";
	$headers[] = "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";

	// Body
	if( function_exists( 'EL' ) ){

		$body = EL()->options->mail->get( 'mail_new_acocunt_content', esc_html__( 'You registered user [el_link_profile] successfully at [el_link_home_page]', 'ova-login' ) );

	}else{

		$body = esc_html__( 'You registered user [el_link_profile] successfully at [el_link_home_page]', 'ova-login' );

	}

	$body = str_replace( '[el_link_home_page]', get_site_url(), $body);
	$body = str_replace( '[el_link_profile]', '<a href="'.esc_url( get_author_posts_url( get_user_by('email', $mail_to)->ID ) ).'">'.esc_html( get_author_posts_url( get_user_by('email', $mail_to)->ID ) ).'</a>', $body);


	// Subject
	if ($type_user == 'vendor') {
		$subject = esc_html__( 'New Vendor', 'ova-login' );
	}else{
		$subject = esc_html__( 'New User', 'ova-login' );
	}

	// Mail To
	$mails = wp_mail_from_new_account();

	$mail_new_account_recipient = function_exists( 'EL' ) ? EL()->options->mail->get('mail_new_account_recipient') : '';

	if ( ! empty( $mail_new_account_recipient ) ) {
		$mail_new_account_recipient = array_map('trim', explode( ",", $mail_new_account_recipient ) );
		if ( ! empty( $mail_new_account_recipient ) && is_array( $mail_new_account_recipient ) ) {
			foreach ( $mail_new_account_recipient as $email ) {
				$headers[] = 'Bcc: '.$email ."\r\n";
			}
		}
	}

	add_filter( 'wp_mail_from', 'wp_mail_from_new_account' );
	if ($type_user == 'vendor') {
		add_filter( 'wp_mail_from_name', 'wp_mail_from_name_new_account_type_vendor' );
	}else{
		add_filter( 'wp_mail_from_name', 'wp_mail_from_name_new_account_type_user' );	
	}


	if( wp_mail( $mails, $subject, $body, $headers ) ){
		$result = true;
	}else{
		$result = false;
	}


	remove_filter( 'wp_mail_from', 'wp_mail_from_new_account' );
	if ($type_user == 'vendor') {
		remove_filter( 'wp_mail_from_name', 'wp_mail_from_name_new_account_type_vendor' );
	} else {
		remove_filter( 'wp_mail_from_name','wp_mail_from_name_new_account_type_user' );
	}

	return $result;
}

function wp_mail_from_name_new_account_type_vendor(){
	return esc_html__( 'New Vendor', 'ova-login' );
}

function wp_mail_from_name_new_account_type_user(){
	return esc_html__( 'New User', 'ova-login' );	
}


function wp_mail_from_new_account(){

	if( function_exists( 'EL' ) && EL()->options->mail->get('mail_new_acocunt_from_email') ){
		return EL()->options->mail->get('mail_new_acocunt_from_email');
	} else {
		return get_option('admin_email');	
	}

}


function wp_mail_from_name_new_account_user(){

	return get_bloginfo( 'name' );	

}

// Get full list page
if ( ! function_exists( 'ovalg_get_pages' ) ) {

	function ovalg_get_pages() {
		global $wpdb;
		$sql   = $wpdb->prepare( "
			SELECT ID, post_title FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = %s AND $wpdb->posts.post_status = %s
			GROUP BY $wpdb->posts.post_name
			", 'page', 'publish' );
		$pages = $wpdb->get_results( $sql );

		return apply_filters( 'ovalg_get_pages', $pages );
	}
}

// Get dropdown pages
if ( ! function_exists( 'ovalg_dropdown_pages' ) ) {

	function ovalg_dropdown_pages() {

		$list_page = ovalg_get_pages();
		$list_page_arr[''] = __( '---Select page---', 'ova-login' );

		foreach ( $list_page as $id => $value_page ) {

			$list_page_arr[$value_page->ID] = $value_page->post_title;
		}
		return apply_filters( 'ovalg_dropdown_pages', $list_page_arr );
	}

}

// Get list Login Page
if ( ! function_exists( 'ovalg_dropdown_pages_login' ) ) {

	function ovalg_dropdown_pages_login() {

		$list_page = ovalg_get_pages();

		$list_page_arr[''] = __( '---Select page---', 'ova-login' );

		foreach ( $list_page as $id => $value_page ) {

			$page_object = get_page( $value_page->ID );

			if ( has_shortcode( $page_object->post_content, 'custom-login-form' ) ) {

				$list_page_arr[$value_page->ID] = $value_page->post_title;	


			}


		}

		return apply_filters( 'ovalg_dropdown_pages_login', $list_page_arr );
	}

}

// Get list Register Page
if ( ! function_exists( 'ovalg_dropdown_pages_register' ) ) {

	function ovalg_dropdown_pages_register() {

		$list_page = ovalg_get_pages();

		$list_page_arr[''] = __( '---Select page---', 'ova-login' );

		foreach ( $list_page as $id => $value_page ) {

			$page_object = get_page( $value_page->ID );

			if ( has_shortcode( $page_object->post_content, 'custom-register-form' ) ) {

				$list_page_arr[$value_page->ID] = $value_page->post_title;	


			}


		}

		return apply_filters( 'ovalg_dropdown_pages_register', $list_page_arr );
	}

}

// Get list Forgot Page
if ( ! function_exists( 'ovalg_dropdown_pages_forgot_pw' ) ) {

	function ovalg_dropdown_pages_forgot_pw() {

		$list_page = ovalg_get_pages();

		$list_page_arr[''] = __( '---Select page---', 'ova-login' );

		foreach ( $list_page as $id => $value_page ) {

			$page_object = get_page( $value_page->ID );

			if ( has_shortcode( $page_object->post_content, 'custom-password-lost-form' ) ) {

				$list_page_arr[$value_page->ID] = $value_page->post_title;	


			}


		}

		return apply_filters( 'ovalg_dropdown_pages_forgot_pw', $list_page_arr );
	}

}

// Get list Reset Page
if ( ! function_exists( 'ovalg_dropdown_pages_reset_pw' ) ) {

	function ovalg_dropdown_pages_reset_pw() {

		$list_page = ovalg_get_pages();

		$list_page_arr[''] = __( '---Select page---', 'ova-login' );

		foreach ( $list_page as $id => $value_page ) {

			$page_object = get_page( $value_page->ID );

			if ( has_shortcode( $page_object->post_content, 'custom-password-reset-form' ) ) {

				$list_page_arr[$value_page->ID] = $value_page->post_title;	


			}


		}

		return apply_filters( 'ovalg_dropdown_pages_reset_pw', $list_page_arr );
	}

}


/**
* Add Setting Menu
*/
add_action( 'el_add_submenu_page',  'OVALG_register_menu' );
function OVALG_register_menu(){

	add_submenu_page( 'eventlist', esc_html__( 'Manage Account', 'ova-login' ), esc_html__( 'Manage Account', 'ova-login' ), 'manage_options', 'ovalg_general_settings', array( 'OVALG_Admin_Settings', 'create_admin_setting_page' ) );

	add_submenu_page( 'eventlist', esc_html__( 'Custom Register Field', 'ova-login' ), esc_html__( 'Custom Register Field', 'ova-login' ), 'manage_options', 'ovareg_custom_field_settings', array( 'OVALG_Admin_Settings', 'create_register_setting_page' ) );

	if ( OVALG_Settings::admin_approve_vendor() == 'yes' ) {

		add_submenu_page( 'eventlist', __( 'Manage Vendor', 'ova-login' ), __( 'Manage Vendor', 'ova-login' ), 'manage_options', 'ovalg_vendor_approve', array('Ova_Login_Vendor_Approve', 'list_table_page') );
		
	}

	
}




/* LOGIN FORM */
/*************************************************************************************/

/**
* Allow display custom login form
*/
function ovalg_allow_custom_login(){

	$allow_custom_login = false;

	if( OVALG_Settings::login_page() ){

		$allow_custom_login = true;

	}else if( get_option('permalink_structure') ){

		$member_login = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-login' ) );

		if ( $member_login && has_shortcode( $member_login[0]->post_content, 'custom-login-form' ) ) {

			$allow_custom_login = true;

		}
	}

	return $allow_custom_login;
}





// Get Member Login URL
function ovalg_login_url(){

// The rest are redirected to the login page
	if( $login_page = OVALG_Settings::login_page() ){

		$login_page_wpml = apply_filters( 'wpml_object_id', $login_page, 'page' );
		$login_url = get_permalink( $login_page_wpml );

	}else if( get_option('permalink_structure') ){

		$member_login = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-login' ) );

		if ( has_shortcode( $member_login[0]->post_content, 'custom-login-form' ) ) {

			$login_url = site_url( 'member-login' );

		}else{

			$login_url = wp_login_url();

		}

	}else{

		$login_url = wp_login_url();

	}


	return $login_url;

}



/* LOGIN FORM SUCESSFULLY */
/*************************************************************************************/

function ovalg_login_success_url(){

	$login_success_page_url = home_url('/');

// The rest are redirected to the login page
	if( $login_success_page = OVALG_Settings::login_success_page() ){

		$login_success_page_wpml = apply_filters( 'wpml_object_id', $login_success_page, 'page' );
		$login_success_page_url = get_permalink( $login_success_page_wpml );


	}else if( class_exists('EventList') ){

		$login_success_page_url = get_myaccount_page();

	}else if( get_option('permalink_structure') ){

		$member_account = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-account' ) );

		if ( has_shortcode( $member_account[0]->post_content, 'el_member_account' ) ) {	

			$login_success_page_url = site_url( 'member-account' );

		}else{

			$login_success_page_url = home_url();

		}

	}else{

		$login_success_page_url = home_url();

	}


	return $login_success_page_url;

}




/* REGISTER FORM */
/*************************************************************************************/

/**
* Allow display custom register form 
*/
function ovalg_allow_custom_register(){

	$allow_custom_register = false;

	if( OVALG_Settings::register_page() ){

		$allow_custom_register = true;

	}else if( get_option('permalink_structure') ){

		$member_register = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-register' ) );

		if ( $member_register && has_shortcode( $member_register[0]->post_content, 'custom-register-form' ) ) {

			$allow_custom_register = true;

		}
	}

	return $allow_custom_register;

}


/**
* Register URL
* @return URL
*/
function ovalg_register_url(){

// The rest are redirected to the login page
	if( $register_page = OVALG_Settings::register_page() ){

		$register_page_wpml = apply_filters( 'wpml_object_id', $register_page, 'page' );
		$register_url = get_permalink( $register_page_wpml );

	}else if( get_option('permalink_structure') ){

		$member_register = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-register' ) );

		if ( has_shortcode( $member_register[0]->post_content, 'custom-register-form' ) ) {	

			$register_url = site_url( 'member-register' );

		}else{

			$register_url = wp_registration_url();

		}

	}else{

		$register_url = wp_registration_url();

	}

	return $register_url;

}



/* FOR GOT PASSWORD */
/*************************************************************************************/

/**
* Allow display custom forgot password
*/
function ovalg_allow_custom_forgot_pw(){

	$allow_custom_forgot_pw = false;

	if( OVALG_Settings::forgot_password_page() ){

		$allow_custom_forgot_pw = true;

	}else if( get_option('permalink_structure') ){

		$member_forgot_pw = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-password-lost' ) );

		if ( $member_forgot_pw && has_shortcode( $member_forgot_pw[0]->post_content, 'custom-password-lost-form' ) ) {	

			$allow_custom_forgot_pw = true;

		}
	}

	return $allow_custom_forgot_pw;

}

/**
* Password Lost URL
* @return URL
*/
function ovalg_password_lost_url(){

// The rest are redirected to the login page
	if( $forgot_password_page = OVALG_Settings::forgot_password_page() ){

		$forgot_password_page_wpml = apply_filters( 'wpml_object_id', $forgot_password_page, 'page' );
		$forgot_password_url = get_permalink( $forgot_password_page_wpml );

	}else if( get_option('permalink_structure') ){

		$member_forgot_pw = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-password-lost' ) );

		if ( has_shortcode( $member_forgot_pw[0]->post_content, 'custom-password-lost-form' ) ) {	

			$forgot_password_url = site_url( 'member-password-lost' );

		}else{

			$forgot_password_url = wp_lostpassword_url();

		}

	}else{

		$forgot_password_url = wp_lostpassword_url();

	}


	return $forgot_password_url;

}



/* RESET PASSWORD */
/*************************************************************************************/

/**
* Allow display custom reset password
*/
function ovalg_allow_custom_reset_pw(){

	$allow_custom_reset_pw = false;

	if( OVALG_Settings::pick_new_password_page() ){

		$allow_custom_reset_pw = true;

	}else if( get_option('permalink_structure') ){

		$member_reset_pw = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-password-reset' ) );

		if ( $member_reset_pw && has_shortcode( $member_reset_pw[0]->post_content, 'custom-password-reset-form' ) ) {	

			$allow_custom_reset_pw = true;

		}
	}

	return $allow_custom_reset_pw;

}

/**
* Reset URL
* @return URL
*/
function ovalg_password_reset_url(){

// The rest are redirected to the login page
	if( $reset_pw_page = OVALG_Settings::pick_new_password_page() ){

		$reset_password_page_wpml = apply_filters( 'wpml_object_id', $reset_pw_page, 'page' );
		$reset_password_url = get_permalink( $reset_password_page_wpml );

	}else if( get_option('permalink_structure') ){

		$member_reset_pw = get_posts( array( 'post_type' => 'page', 'pagename' => 'member-password-reset' ) );

		if ( has_shortcode( $member_reset_pw[0]->post_content, 'custom-password-reset-form' ) ) {	

			$reset_password_url = site_url( 'member-password-reset' );

		}else{

			$reset_password_url = wp_lostpassword_url();

		}

	}else{

		$reset_password_url = wp_lostpassword_url();

	}


	return $reset_password_url;

}


add_filter( 'login_form_bottom', 'ovalg_login_form_bottom', 10, 1 );
function ovalg_login_form_bottom( $args ){

	$lang = '';
	if( defined( 'ICL_LANGUAGE_CODE' ) ){
		$lang = ICL_LANGUAGE_CODE;
	}

	if( $lang ){
		$args .= '<input type="hidden" value="'.$lang.'" name="lang" >';	
	}


	return $args;

}



/* Term Condition URL */
/*************************************************************************************/
function ovalg_term_condition_url(){

// The rest are redirected to the login page
	if( $term_page = OVALG_Settings::term_condition_page_id() ){

		$term_page_page_wpml = apply_filters( 'wpml_object_id', $term_page, 'page' );
		$term_page_page_url = get_permalink( $term_page_page_wpml );


	}else{

		$term_page_page_url = apply_filters( 'ovalg_term_url', '' );

	}


	return $term_page_page_url;

}



add_filter( 'register_url', 'ovalg_my_register_page', 10, 1 );
function ovalg_my_register_page( $register_url ) {

	$url = site_url( 'wp-login.php?action=register', 'login' );

	$lang = '';
	if( defined( 'ICL_LANGUAGE_CODE' ) ){
		$lang = ICL_LANGUAGE_CODE;

		global $sitepress;

		if (  $sitepress != Null && $sitepress->get_default_language() != $lang ){
			$register_url = add_query_arg( 'lang', $lang, $url );
		}	



	}





	return $register_url;
}


/* Include template in child theme */

if( !function_exists( 'ovalg_locate_template' ) ){
	function ovalg_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	// Set variable to search in ovaev-templates folder of theme.
		if ( ! $template_path ) :
			$template_path = 'ovalg-templates/';
		endif;

	// Set default plugin templates path.
		if ( ! $default_path ) :
		$default_path = plugin_dir_path( __FILE__ ) . 'templates/'; // Path to the template folder
	endif;

	// Search template file in theme folder.
	$template = locate_template( array(
		$template_path . $template_name
		// $template_name
	) );

	// Get plugins template file.
	if ( ! $template ) :
		$template = $default_path . $template_name;
	endif;

	return apply_filters( 'ovalg_locate_template', $template, $template_name, $template_path, $default_path );
}

}


function ovalg_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {
	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
endif;
$template_file = ovalg_locate_template( $template_name, $tempate_path, $default_path );
if ( ! file_exists( $template_file ) ) :
	_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
	return;
endif;


include $template_file;
}

/**
* Recursive array replace \\
*/
if( !function_exists('recursive_array_replace') ){
	function recursive_array_replace( $find, $replace, $array ) {
		if ( ! is_array( $array ) ) {
			return str_replace( $find, $replace, $array );
		}

		foreach ( $array as $key => $value ) {
			$array[$key] = recursive_array_replace( $find, $replace, $value );
		}

		return $array;
	}
}

if ( ! function_exists("ovalg_sortby_position") ) {
	function ovalg_sortby_position( $list_fields ){
		$arr_sorted = array();
		$arr_name_pos = array();

		if ( $list_fields ) {
		// Add position => name to arr_name_pos
			foreach ( $list_fields as $key => $item ) {
				$position =  isset( $item['position'] ) ? $item['position'] : 0;
				if ( $position == '' ) {
					return $list_fields;
				}
				$arr_name_pos[$position] = $key;
			}

			ksort($arr_name_pos, SORT_NUMERIC);

			foreach ($arr_name_pos as $name) {
				$arr_sorted[$name] = $list_fields[$name];
			}
		}
		return $arr_sorted;
	}
}

if ( ! function_exists("ovalg_get_list_fields") ) {
	function ovalg_get_list_fields( $list_fields ){
		if ( ! empty( $list_fields ) ):
			foreach ( $list_fields as $key => $field ):
				$name           = $key;
				$type           = array_key_exists( 'type', $field ) ? $field['type'] : '';
				$label          = array_key_exists( 'label', $field ) ? $field['label'] : '';
				$description    = array_key_exists( 'description', $field ) ? $field['description'] : '';
				$placeholder    = array_key_exists( 'placeholder', $field ) ? $field['placeholder'] : '';
				$default        = array_key_exists( 'default', $field ) ? $field['default'] : '';
				$class          = array_key_exists( 'class', $field ) ? $field['class'] : '';
				$class_icon     = array_key_exists( 'class_icon', $field ) ? $field['class_icon'] : '';
				$position       = array_key_exists( 'position', $field ) ? $field['position'] : '';
				$used_for       = array_key_exists( 'used_for', $field ) ? $field['used_for'] : '';
				$required       = array_key_exists( 'required', $field ) ? $field['required'] : '';
				$enabled        = array_key_exists( 'enabled', $field ) ? $field['enabled'] : '';
				$max_file_size  = array_key_exists( 'max_file_size', $field ) ? $field['max_file_size'] : 10;

                            // Select
				$ova_options_key    = array_key_exists( 'ova_options_key', $field ) ? $field['ova_options_key'] : [];
				$ova_options_text   = array_key_exists( 'ova_options_text', $field ) ? $field['ova_options_text'] : [];

                            // Radio
				$ova_radio_key      = array_key_exists( 'ova_radio_key', $field ) ? $field['ova_radio_key'] : [];
				$ova_radio_text     = array_key_exists( 'ova_radio_text', $field ) ? $field['ova_radio_text'] : [];

                            // Checkbox
				$ova_checkbox_key      = array_key_exists( 'ova_checkbox_key', $field ) ? $field['ova_checkbox_key'] : [];
				$ova_checkbox_text     = array_key_exists( 'ova_checkbox_text', $field ) ? $field['ova_checkbox_text'] : [];

				$required_status    = $required ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';
				$enabled_status     = $enabled ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';

				$class_disable  = ! $enabled ? 'class="ova-disable"' : '';
				$disable_button = ! $enabled ? 'disabled' : '';
				$value_enabled  = ( $enabled == 'on' ) ? $name : '';

				$data_edit = [
					'required'          => $required,
					'name'              => $name,
					'type'              => $type,
					'label'             => $label,
					'description'       => $description,
					'placeholder'       => $placeholder,
					'default'           => $default,
					'class'             => $class,
					'class_icon'        => $class_icon,
					'position'          => $position,
					'used_for'          => $used_for,
					'ova_options_key'   => $ova_options_key,
					'ova_options_text'  => $ova_options_text,
					'ova_radio_key'     => $ova_radio_key,
					'ova_radio_text'    => $ova_radio_text,
					'ova_checkbox_key'  => $ova_checkbox_key,
					'ova_checkbox_text' => $ova_checkbox_text,
					'max_file_size'     => $max_file_size,
				];

				$data_edit = json_encode( $data_edit );
				?>
				<tr <?php echo $class_disable; ?>>
					<input type="hidden" name="remove_field[]" value="">
					<input type="hidden" name="enable_field[]" value="<?php echo esc_attr( $value_enabled ); ?>">
					<input type="hidden" class="ova_pos_name" data-name="<?php echo esc_attr( $name ); ?>" />
					<td class="ova-checkbox">
						<input type="checkbox" name="select_field[]" value="<?php echo esc_attr( $name ); ?>" />
					</td>
					<td class="ova-name"><?php echo esc_html( $key ); ?></td>
					<td class="ova-type"><?php echo esc_html( $type ); ?></td>
					<td class="ova-label"><?php echo esc_html( $label ); ?></td>
					<td class="ova-placeholder"><?php echo esc_html( $placeholder ); ?></td>
					<td class="ova-user-for"><?php echo sprintf( esc_html__('%s', 'ova-login'),ucfirst($used_for) ); ?></td>
					<td class="ova-require status"><?php echo $required_status; ?></td>
					<td class="ova-enable status"><?php echo $enabled_status; ?></td>
					<td class="ova-edit edit">
						<button type="button" <?php echo esc_attr( $disable_button ); ?> class="button ova-button ovalg_edit_field_form" data-data_edit="<?php echo esc_attr( $data_edit ); ?>">
							<?php esc_html_e( 'Edit', 'ova-login' ) ?>
						</button>
					</td>
				</tr>
			<?php endforeach;
		endif;
	}
}

if ( ! function_exists("ova_register_user_custom_field") ) {
	
	function ova_register_user_custom_field( $type_user ){
		$custom_fields 	= get_option( 'ova_register_form' );
		$data_fields 	= array();

		if ( $custom_fields ) {
			foreach ( $custom_fields as $name => $field ) {
				$name = 'ova_'.$name;
				if ( $field['enabled'] == "on" && ( ( $field['used_for'] == $type_user ) || ( $field['used_for'] == "both" ) ) ) {

					$required = $field['required'] == "on" 	? true : false;

					if ( $field['type'] !== 'file' ) {
						$data_field = '';
						if ( isset( $_POST[$name] ) ) {
							if ( ! is_array( $_POST[$name] ) ) {
								$data_field = sanitize_text_field( $_POST[$name] );
							} else {
								$data_field = $_POST[$name];
							}
						}
						$data_fields[$name] = array(
							'label' 	=> $field['label'],
							'type' 		=> $field['type'],
							'value' 	=> $data_field,
							'required' 	=> $required,
						);
					} else {
						$file = isset( $_FILES[$name] ) ? $_FILES[$name] : "";
						$data_fields[$name] = array(
							'label' 		=> $field['label'],
							'type' 			=> 'file',
							'max_file_size' => $field['max_file_size'],
							'value' 		=> $file,
							'required' 		=> $required,
						);
					}
				}
			}
		}
		return $data_fields;
	}
}
// reCAPCHA
if ( ! function_exists("ovalg_recaptcha_is_key_setup_complete") ) {
	function ovalg_recaptcha_is_key_setup_complete(){
		$flag = false;
		$site_key 	= OVALG_Settings::recapcha_site_key();
		$secret_key = OVALG_Settings::recapcha_secret_key();
		if ( $site_key && $secret_key ) {
			$flag = true;
		}
		return $flag;
	}
}

if ( ! function_exists('ovalg_count_user_by_vendor_status') ) {
	function ovalg_count_user_by_vendor_status( $vendor_status ){
		if ( empty( $vendor_status ) ) {
			return 0;
		}
		$args = array(
			'meta_key' => 'vendor_status',
			'meta_value' => $vendor_status,
			'count_total' => true,
		);

		$users = new WP_User_Query( $args );
		return $users->get_total();
	}
}