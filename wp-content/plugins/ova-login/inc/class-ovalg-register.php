<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Register") ) {
	
	class Ova_Login_Register {

		private $errors;

		private $error_message;

		public function __construct(){

			include_once OVALGDIR . '/inc/class-ovalg-err-message.php';
			$this->error_message = new Ova_Login_Err_Message();
			$this->errors = new WP_Error();
			// If choose Login page in Login Setting Plugin

			$allow_custom_login = ovalg_allow_custom_login();

			add_filter( 'el_logout_url', array( $this, 'redirect_logout_url' ) );


			if( apply_filters( 'ovalg_allow_custom_login', $allow_custom_login ) ){

				// add_action( 'login_form_login', array( $this, 'redirect_to_custom_login' ) );


	     		// Check Login user     	
				add_filter( 'authenticate', array( $this, 'maybe_redirect_at_authenticate' ), 101, 3 );
				// Check logout user
				// add_action( 'wp_logout', array( $this, 'redirect_after_logout' ) );
				
				// Redirect to page when logged in
				// add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 10, 3 );

				$allow_custom_register = ovalg_allow_custom_register();

				if( apply_filters( 'ovalg_allow_custom_register', $allow_custom_register ) ){

					add_action( 'login_form_register', array( $this, 'redirect_to_custom_register' ) );
					add_action( 'login_form_register', array( $this, 'do_register_user' ) );

				}

				$allow_custom_forgot_pw = ovalg_allow_custom_forgot_pw();
				if( $allow_custom_forgot_pw ){
					add_action( 'login_form_lostpassword', array( $this, 'redirect_to_custom_lostpassword' ) );
					add_action( 'login_form_lostpassword', array( $this, 'do_password_lost' ) );
				}

				add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );

				$allow_custom_reset_pw = ovalg_allow_custom_reset_pw();

				if( $allow_custom_reset_pw ){

					add_action( 'login_form_rp', array( $this, 'redirect_to_custom_password_reset' ) );
					add_action( 'login_form_resetpass', array( $this, 'redirect_to_custom_password_reset' ) );
					add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );
					add_action( 'login_form_resetpass', array( $this, 'do_password_reset' ) );
				}
			}
		}

		public function redirect_logout_url( $url ){
			$url = wp_logout_url( ovalg_login_url() );
			return apply_filters( 'redirect_logout_url', $url );
		}

		/**
	 * Redirects the user to the correct page depending on whether he / she
	 * is an admin or not.
	 *
	 * @param string $redirect_to   An optional redirect_to URL for admin users
	 */
		private function redirect_logged_in_user( $redirect_to = null ) {

			$user = wp_get_current_user();

			if ( user_can( $user, 'manage_options' ) ) {

				if ( $redirect_to ) {
					wp_safe_redirect( $redirect_to );
				} else {
					wp_redirect( admin_url() );
				}

			} else {

				$login_sucess_url = ovalg_login_success_url();

				wp_redirect( $login_sucess_url );

			}
		}

		/**
		* Redirect the user to the custom login page instead of wp-login.php.
		*/
		public function redirect_to_custom_login() {

			if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {

				$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : null;

				$password_change = isset( $_REQUEST['password'] ) ? $_REQUEST['password'] : null;

				if ( is_user_logged_in() ) {
					$this->redirect_logged_in_user( $redirect_to );
					exit;
				}

        	// Get Login Page
				$login_url = ovalg_login_url();

				if ( ! empty( $redirect_to ) ) {
					$login_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $login_url );
				}

				if ( ! empty( $password_change ) ) {
					$login_url = add_query_arg( 'password', $password_change, $login_url );
				}

				wp_redirect( $login_url );
				exit;
			}

		}

		/**
		* Redirect the user after authentication if there were any errors.
		*
		* @param Wp_User|Wp_Error  $user       The signed in user, or the errors that have occurred during login.
		* @param string            $username   The user name used to log in.
		* @param string            $password   The password used to log in.
		*
		* @return Wp_User|Wp_Error The logged in user, or error information if there were errors.
		*/
		public function maybe_redirect_at_authenticate( $user, $username, $password ) {
    	// Check if the earlier authenticate filter (most likely, 
    	// the default WordPress authentication) functions have found errors

			$redirect_post = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';

			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && $redirect_post != admin_url() ) {

				// handle error
				$redirect 		= isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
				$recapcha 		= isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';
				$check_recapcha = '';
				if ( isset( $_POST['g-recaptcha-response'] ) ) {
					$check_recapcha = $this->validate_recapcha( $recapcha );
				}

				if ( is_wp_error( $user ) || is_wp_error( $check_recapcha ) || ! empty( $check_recapcha ) ) {
					// Get Login Page
					$login_url 		= ovalg_login_url();
					// Get current language
					$current_lang 	= isset( $_POST['lang'] ) ? $_POST['lang'] : '';
					$recapcha_err 	= array();
					$user_err 		= array();
					// Add language to login url
					if ( $current_lang ) {
						$login_url = add_query_arg( 'lang', $current_lang, $login_url );
					}
					if ( $redirect ) {
						$login_url = add_query_arg( 'redirect_to', $redirect, $login_url );
					}
					if ( is_wp_error( $check_recapcha ) ) {
						$recapcha_err = array( 'recapcha' );
					}
					if ( is_wp_error( $user ) ) {
						$user_err = $user->get_error_codes();
					}

					$errors 		= array_merge( $recapcha_err, $user_err );
					$error_codes 	= join( ',', $errors );
					$redirect_url 	= add_query_arg( 'login', $error_codes, $login_url );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}

			return $user;
		}
		/**
		* Redirect to custom login page after the user has been logged out.
		*/
		public function redirect_after_logout() {

		// Get Login Page
			$login_url = ovalg_login_url();

			$redirect_url = add_query_arg( 'logged_out', true , $login_url );

			wp_safe_redirect( $redirect_url );

			exit;
		}
		/**
		* Returns the URL to which the user should be redirected after the (successful) login.
		*
		* @param string           $redirect_to           The redirect destination URL.
		* @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
		* @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
		*
		* @return string Redirect URL
		*/
		public function redirect_after_login( $redirect_to, $requested_redirect_to, $user ) {

			$redirect_url = '';

			if( isset( $_POST['redirect_to'] ) && array_key_exists('redirect_to', $_POST) ) {

				$redirect_url = wp_validate_redirect( $_POST['redirect_to'] );

			}

			
			if(  $redirect_url ) {
				return $redirect_url;
			}

			$redirect_url = site_url();

			if ( ! isset( $user->ID ) ) {
				return $redirect_url;
			}

			if ( user_can( $user, 'manage_options' ) ) {
        	// Use the redirect_to parameter if one is set, otherwise redirect to admin dashboard.
				if ( $requested_redirect_to == '' ) {
					$redirect_url = admin_url();
				} else {
					$redirect_url = $requested_redirect_to;
				}
			} else {
        	// Non-admin users always go to their account page after login
				$redirect_url = ovalg_login_success_url();
			}

			return wp_validate_redirect( $redirect_url, site_url() );

		}
		/**
		* Redirects the user to the custom registration page instead
		* of wp-login.php?action=register.
		*/
		public function redirect_to_custom_register() {

			if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {

				if ( is_user_logged_in() ) {

					$this->redirect_logged_in_user();

				} else {

					$regsiter_url = ovalg_register_url();

					wp_redirect( $regsiter_url );

				}

				exit;

			}

		}
		/**
		* Handles the registration of a new user.
		*
		* Used through the action hook "login_form_register" activated on wp-login.php
		* when accessed through the registration action.
		*/
		public function do_register_user() {

			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

				$redirect_url 	= ovalg_register_url();
				$mail_result 	= false;

				if ( ! get_option( 'users_can_register' ) ) {

            	// Registration closed, display error
					$redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );

				} else {

					$username = sanitize_text_field( $_POST['username'] ) ;
					$email = sanitize_text_field($_POST['email']);
					$email_confirm = isset( $_POST['email_confirm'] ) ? sanitize_text_field($_POST['email_confirm']) : '';
					$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
					$last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
					$password = isset($_POST['password']) ? sanitize_text_field( $_POST['password'] ) : '';
					$password_confirm = isset($_POST['password_confirm']) ? sanitize_text_field( $_POST['password_confirm'] ) : '';
					$type_user = sanitize_text_field( $_POST['type_user'] );

					$user_phone = isset($_POST['user_phone']) ? sanitize_text_field( $_POST['user_phone'] ) : '' ;
					$user_url = isset($_POST['user_url']) ? sanitize_url( $_POST['user_url'] ) : '' ;
					$user_job =  isset( $_POST['user_job'] ) ? sanitize_text_field( $_POST['user_job'] ) : '' ;
					$user_address = isset( $_POST['user_address'] ) ? sanitize_text_field( $_POST['user_address'] ) : '';
					$user_description = isset( $_POST['user_description'] ) ? sanitize_text_field( $_POST['user_description'] ) : '';

					$extra_data = array(
						'user_phone'	=> $user_phone,
						'user_job'	=> $user_job,
						'user_address'	=> $user_address,
						'user_description'	=> $user_description,
					);
					// reCAPCHA
					$recapcha = null;
					if ( isset( $_POST['g-recaptcha-response'] ) ) {
						$recapcha = $_POST['g-recaptcha-response'];
					}
					// Custom register field
					$data_custom_field = ova_register_user_custom_field( $type_user );

					$dcma = sanitize_text_field( $_POST['el_dcma'] );

					$result = $this->register_user( $username, $email, $email_confirm, $first_name, $last_name, $password, $password_confirm, $type_user, $dcma, $extra_data, $data_custom_field, $recapcha, $user_url );

					if ( is_wp_error( $result ) ) {
						
             		// Parse errors into a string and append as parameter to redirect
						$errors = join( ',', $result->get_error_codes() );
						$redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );

					} else {

						$admin_approve_vendor = OVALG_Settings::admin_approve_vendor();

						// Send email to user after resgister success
						$enable_send_mail = EL()->options->mail->get('enable_send_new_account_email', 'yes');
						$send_to_user = EL()->options->mail->get('new_account_sendmail',  array( 'administrator', 'user' ) );
						if( $enable_send_mail == 'yes' && in_array( 'user', $send_to_user ) ){
							$mail_result = ova_register_mailto_user( $email );
						}

						// Send mail notice of new registrar
						if ( $admin_approve_vendor === 'yes' &&
							OVALG_Settings::enable_send_new_vendor_email() &&
							$type_user == 'vendor' ) {
							
						$mail_result = ova_register_vendor_mailto_admin( $email );
						// send mail to admin new vendor/user
						} elseif ( apply_filters( 'el_reg_user_sendmail_admin', true ) == true && $enable_send_mail == 'yes' && in_array( 'administrator' , $send_to_user ) ){

							$mail_result = ova_register_mailto_admin( $type_user, $email );

						}

						$login_url = ovalg_login_url();
						$redirect_url = add_query_arg( 'registered', $email, $login_url );
						if ( ! $mail_result ) {
							$redirect_url = add_query_arg( 'mail_err', $email, $login_url );
						}
					}

				}
			}

			wp_redirect( $redirect_url );

			exit;
		}
		/* Redirects the user to the custom forgot password page instead of wp-login.php?action=lostpassword. */
		public function redirect_to_custom_lostpassword() {

			if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {

				if ( is_user_logged_in() ) {

					$this->redirect_logged_in_user();
					exit;

				}

				$forgot_pw_url = ovalg_password_lost_url();

				wp_redirect( $forgot_pw_url );
				exit;
			}

		}
		/* Initiates password reset. */
		public function do_password_lost() {

			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
				
				$lost_pw_url 		= ovalg_password_lost_url();
				$check_recapcha 	= '';
				// verify google recaptcha
				if ( isset( $_POST['g-recaptcha-response'] ) ) {
					$recapcha 		= $_POST['g-recaptcha-response'];
					$check_recapcha = $this->validate_recapcha( $recapcha );
				}
				if ( is_wp_error( $check_recapcha ) || $check_recapcha != '' ) {
					$redirect_url 	= add_query_arg( 'errors', join( ',', array('recapcha') ), $lost_pw_url );
					wp_redirect( $redirect_url );
					exit;
				}

				$errors = retrieve_password();

				if ( is_wp_error( $errors ) ) {
            		// Errors found
					$redirect_url 	= add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $lost_pw_url );
				} else {
            		// Email sent
					$login_url = ovalg_login_url();
					$redirect_url = add_query_arg( 'checkemail', 'confirm', $login_url );
				}

				wp_redirect( $redirect_url );
				exit;
			}

		}
		/* Create new message reset password */
		public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {

			$msg  = __( 'Hello!', 'ova-login' ) . "\r\n\r\n";
			$msg .= sprintf( __( 'You asked us to reset your password for your account using the email address %s.', 'ova-login' ), $user_login ) . "\r\n\r\n";
			$msg .= __( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'ova-login' ) . "\r\n\r\n";
			$msg .= __( 'To reset your password, visit the following address:', 'ova-login' ) . "\r\n\r\n";
			$msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
			$msg .= __( 'Thanks!', 'ova-login' ) . "\r\n";

			return $msg;
		}
		public function redirect_to_custom_password_reset() {

			if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {

        	// Verify key / login combo
				$user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

				if ( ! $user || is_wp_error( $user ) ) {

					if ( $user && $user->get_error_code() === 'expired_key' ) {

						$login_url = ovalg_login_url();
						$redirect_login_expired_url = add_query_arg( 'login', 'expiredkey', $login_url );

						wp_redirect( $redirect_login_expired_url );

					} else {

						$login_url = ovalg_login_url();
						$redirect_login_invalidkey_url = add_query_arg( 'login', 'invalidkey', $login_url );

						wp_redirect( $redirect_login_invalidkey_url );

					}

					exit;

				}

				$redirect_url = ovalg_password_reset_url();
				$redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
				$redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );

				wp_redirect( $redirect_url );
				exit;
			}
		}
		public function do_password_reset() {

			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

				$rp_key = $_REQUEST['rp_key'];
				$rp_login = $_REQUEST['rp_login'];

				$check_recapcha 	= '';
				// verify google recaptcha
				if ( isset( $_POST['g-recaptcha-response'] ) ) {
					$recapcha 		= $_POST['g-recaptcha-response'];
					$check_recapcha = $this->validate_recapcha( $recapcha );
				}
				if ( is_wp_error( $check_recapcha ) || $check_recapcha != '' ) {
					$redirect_url 	= ovalg_password_reset_url();
					$redirect_url 	= add_query_arg( 'key', esc_attr( $rp_key ), $redirect_url );
					$redirect_url 	= add_query_arg( 'login', esc_attr( $rp_login ), $redirect_url );
					$redirect_url 	= add_query_arg( 'error', 'recapcha', $redirect_url );
					wp_redirect( $redirect_url );
					exit;
				}

				$user = check_password_reset_key( $rp_key, $rp_login );

				if ( ! $user || is_wp_error( $user ) ) {

					if ( $user && $user->get_error_code() === 'expired_key' ) {

						$login_url = ovalg_login_url();
						$redirect_login_expired_url = add_query_arg( 'login', 'expiredkey', $login_url );

						wp_redirect( $redirect_login_expired_url );

					} else {

						$login_url = ovalg_login_url();
						$redirect_login_expired_url = add_query_arg( 'login', 'invalidkey', $login_url );

						wp_redirect( $redirect_login_expired_url );

					}

					exit;

				}

				if ( isset( $_POST['pass1'] ) ) {

					if ( $_POST['pass1'] != $_POST['pass2'] ) {
					// Passwords don't match
						$redirect_url = ovalg_password_reset_url();
						$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
						$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
						$redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );
						wp_redirect( $redirect_url );
						exit;
					}
					if ( empty( $_POST['pass1'] ) ) {
					// Password is empty
						$redirect_url = ovalg_password_reset_url();
						$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
						$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
						$redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );
						wp_redirect( $redirect_url );
						exit;
					}
				// Parameter checks OK, reset password
					reset_password( $user, $_POST['pass1'] );

					$login_url = ovalg_login_url();
					$login_url_changed = add_query_arg( 'password', 'changed', $login_url );

					wp_redirect( $login_url_changed );

				} else {

					echo esc_html__( 'Invalid request.', 'ova-login' );

				}

				exit;

			}

		}
		/**
		* Validates and then completes the new user signup process if all went well.
		*
		* @param string $email         The new user's email address
		* @param string $first_name    The new user's first name
		* @param string $last_name     The new user's last name
		*
		* @return int|WP_Error         The id of the user that was created, or error if failed.
		*/
		private function register_user( $username, $email, $email_confirm,  $first_name, $last_name, $password, $password_confirm, $type_user, $dcma, $extra_data, $data_custom_field = null, $recapcha = null, $user_url = null ) {

			$admin_approve_vendor = OVALG_Settings::admin_approve_vendor();

			$this->validate_default_fields( $username, $email, $email_confirm,  $first_name, $last_name, $password, $password_confirm );

			if ( $type_user !== 'vendor' && $type_user !== 'user' ) {
				$this->errors->add( 'type_user', $this->error_message->get_error_message( 'type_user') );
				return $this->errors;
			}

			if ( $type_user === 'vendor' ) {

				if ( $admin_approve_vendor === 'yes' ) {
					$role = 'subscriber';
				} else {
					$role = 'el_event_manager';
				}
				
			} else {
				$role = 'subscriber';
			}

			if ( ! $dcma && apply_filters( 'el_show_register_account_terms', true ) ) {
				$this->errors->add( 'dcma', $this->error_message->get_error_message( 'dcma') );
				return $this->errors;
			}

			$user_phone = isset( $extra_data['user_phone'] ) ? $extra_data['user_phone'] : '';

			if ( $user_phone == '' && apply_filters( 'ovalg_register_require_phone', false ) ) {
				$this->errors->add( 'user_phone', $this->error_message->get_error_message( 'user_phone') );
				return $this->errors;
			}

			if ( $user_url == '' && apply_filters( 'ovalg_register_require_website', false ) ) {
				$this->errors->add( 'user_url', $this->error_message->get_error_message( 'empty_has_label', 'website') );
				return $this->errors;
			}

			$user_job = isset( $extra_data['user_job'] ) ? $extra_data['user_job'] : '';

			if ( $user_job == '' && apply_filters( 'ovalg_register_require_job', false ) ) {
				$this->errors->add( 'user_job', $this->error_message->get_error_message( 'user_job') );
				return $this->errors;
			}

			$user_address = isset( $extra_data['user_address'] ) ? $extra_data['user_address'] : '';

			if ( $user_address == '' && apply_filters( 'ovalg_register_require_address', false ) ) {
				$this->errors->add( 'user_address', $this->error_message->get_error_message( 'user_address') );
				return $this->errors;
			}

			$user_description = isset( $extra_data['user_description'] ) ? $extra_data['user_description'] : '';

			if ( $user_description == '' && apply_filters( 'ovalg_register_require_description', false ) ) {
				$this->errors->add( 'user_description', $this->error_message->get_error_message( 'user_description') );
				return $this->errors;
			}
			// reCapcha V3 validate
			if ( ! is_null( $recapcha ) ) {
				$check_recapcha = $this->validate_recapcha( $recapcha );
				if ( is_wp_error( $check_recapcha ) && ! empty( $check_recapcha ) ) {
					$redirect_url = ovalg_register_url();
					$redirect_url = add_query_arg( 'register-errors', 'recapcha', $redirect_url );
					wp_redirect( $redirect_url );
					exit();
				}
			}
			
			// Validate custom field
			$meta_input = array();
			$attach_ids = array();

			$meta_input = $this->validate_custom_fields( $meta_input, $attach_ids, $data_custom_field );

			$user_data = array(
				'user_login'    => $username,
				'user_email'    => $email,
				'first_name'    => $first_name,
				'last_name'     => $last_name,
				'nickname'      => $first_name,
				'user_pass'		=> $password,
				'user_phone'	=> $user_phone,
				'user_url'		=> $user_url,
				'user_job'		=> $user_job,
				'user_address'	=> $user_address,
				'description'	=> $user_description,
				'role'			=> $role,
			);

			if ( $admin_approve_vendor === 'yes' && $type_user === 'vendor' ) {
				$current_time = current_time( 'timestamp' );
				$meta_input['vendor_status'] = 'pending';
				$meta_input['update_vendor_time'] = $current_time;
			}

			if ( $meta_input ) {
				$user_data['meta_input'] = $meta_input;
			}

			$user_id = wp_insert_user( $user_data );

			// Update post author for attachment
			if ( $attach_ids ) {
				foreach ( $attach_ids as $id ) {
					$arg = array(
						'ID' 			=> $id,
						'post_author' 	=> $user_id,
					);
					wp_update_post( $arg );
				}
			}

			return $user_id;
		}

		private function validate_default_fields( $username, $email, $email_confirm,  $first_name, $last_name, $password, $password_confirm ){
			// Email address is used as both username and email. It is also the only
	    	// parameter we need to validate

			if ( ! validate_username( $username ) ) {
				$this->errors->add( 'username', $this->error_message->get_error_message( 'username') );
				return $this->errors;
			}

			if ( username_exists( $username ) ) {
				$this->errors->add( 'username_exists', $this->error_message->get_error_message( 'username_exists') );
				return $this->errors;
			}

			if ( ! is_email( $email ) ) {
				$this->errors->add( 'email', $this->error_message->get_error_message( 'email' ) );
				return $this->errors;
			}

			if ( $email != $email_confirm && OVALG_Settings::show_email_confirm() == 'yes' ) {
				$this->errors->add( 'email_confirm', $this->error_message->get_error_message( 'email_confirm' ) );
				return $this->errors;
			}

			if ( email_exists( $email ) ) {
				$this->errors->add( 'email_exists', $this->error_message->get_error_message( 'email_exists') );
				return $this->errors;
			}

			if( OVALG_Settings::show_password() !== 'yes' ){

			// Generate the password so that the subscriber will have to check email...
				$password = wp_generate_password( 12, false );

			} else {

				if ( ! $this->checkPassword($password) ) {
					$this->errors->add( 'password_format', $this->error_message->get_error_message( 'password_format') );
					return $this->errors;
				}

				if ( $password !== $password_confirm ) {
					$this->errors->add( 'password_not_match', $this->error_message->get_error_message( 'pass_word_not_match') );
					return $this->errors;
				}

			}
		}

		private function validate_custom_fields( $meta_input, $attach_ids, $data_custom_field ){
			if ( $data_custom_field ) {

				foreach ( $data_custom_field as $name => $data_field) {

				// Check required field
					if ( $data_field['required'] && ! $data_field['value'] ) {
						if ( ! $data_field['label'] ) {
							$this->errors->add( 'empty', $this->error_message->get_error_message( 'empty' ) );
						} else {
							$this->errors->add( 'empty_has_label', $this->error_message->get_error_message( 'empty_has_label', $data_field['label'] ) );
						}
						return $this->errors;
					}

					switch ( $data_field['type'] ) {
						case 'email':
						if ( ! is_email( $data_field['value'] ) ) {
							$this->errors->add( 'email', $this->error_message->get_error_message( 'email' ) );
							return $this->errors;
						}
						break;

						case 'file':

						$file 			= $data_field['value'];
						$max_file_size 	= $data_field['max_file_size'] * pow(10, 6);
						// handle upload file
						if ( $file ) {
							$upload_dir = wp_upload_dir(); // Get the WordPress uploads directory
							$target_dir = $upload_dir['path'] . '/';
							$file_name 	= basename( $file['name'] );
							$file_name_checked = $file_name;
							if ( ! file_exists( $target_dir ) ) {
								wp_mkdir_p( $target_dir );
							}
							$target_file = $target_dir . $file_name;
							$upload_ok = 1;

				    		// Check if the file already exists
							if ( file_exists( $target_file ) ) {
								$current_time 		= current_time( 'timestamp' );
								$target_file 		= $target_dir .$current_time. $file_name;
								$file_name_checked 	= $current_time. $file_name;
							}

				    		// Check file size (you can set your own size limit)
							if ( $file['size'] > $max_file_size ) {
								$upload_ok = 0;
								$this->errors->add( 'file_large', $this->error_message->get_error_message( 'file_large' ) );
								return $this->errors;
							}

				    		// Allow only certain file types (you can adjust this)
							$allowed_types = array("jpg", "jpeg", "png", "pdf", "doc");
							$file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
							if ( ! in_array( $file_extension, $allowed_types ) ) {
								$upload_ok = 0;
								$this->errors->add( 'file_format', $this->error_message->get_error_message( 'file_format' ) );
								return $this->errors;
							}

				    		// If all checks pass, try to upload the file
							if ( $upload_ok ) {
								if ( ! move_uploaded_file( $file['tmp_name'], $target_file ) ) {
									$this->errors->add( 'file_err', $this->error_message->get_error_message( 'file_err' ) );
									return $this->errors;
								}
							}
							// Check the type of file. We'll use this as the 'post_mime_type'
							$filetype = wp_check_filetype( $file_name, null );
							// Prepare an array of post data for the attachment.
							$attachment = array(
								'guid'           => $upload_dir['url'] . '/' . $file_name_checked, 
								'post_mime_type' => $filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name_checked ),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);
							// Insert the attachment.
							$attach_id = wp_insert_attachment( $attachment, $target_file );
							// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							// Generate the metadata for the attachment, and update the database record.
							$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
							wp_update_attachment_metadata( $attach_id, $attach_data );
							array_push( $attach_ids, $attach_id );
							$meta_input[$name] = $attach_id;
						}
						break;
						default:
						break;
					}
					if ( $data_field['type'] != 'file' ) {
						$meta_input[$name] = $data_field['value'];
					}

				}
			}

			return $meta_input;
		}

		private function validate_recapcha( $recapcha ){
			if (  $recapcha ) {
				// Verify captcha
				$secret_key = OVALG_Settings::recapcha_secret_key();
				$post_data = http_build_query(
					array(
						'secret' => $secret_key,
						'response' => $recapcha,
						'remoteip' => $_SERVER['REMOTE_ADDR']
					)
				);
				$opts = array('http' =>
					array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => $post_data
					)
				);
				$context  = stream_context_create($opts);
				$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
				$result = json_decode($response);
				if ( ! $result->success ) {
					$this->errors->add( 'recapcha', $this->error_message->get_error_message( 'recapcha' ) );
					return $this->errors;
				}
			} else {
				$this->errors->add( 'recapcha', $this->error_message->get_error_message( 'recapcha' ) );
				return $this->errors;
			}
		}
		
		/*
		 * Function check password
		 */
		public function checkPassword($password) {
			if (strlen($password) < 8  || !preg_match("#[0-9]+#", $password) || !preg_match("#[a-zA-Z]+#", $password) ) {
				return false;
			}
			return true;
		}
	}
	new Ova_Login_Register();
}