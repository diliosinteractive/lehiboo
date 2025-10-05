<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Shortcode") ) {
	
	class Ova_Login_Shortcode {
		
		private $error_message;

		public function __construct(){

			include_once OVALGDIR . '/inc/class-ovalg-err-message.php';
			$this->error_message = new Ova_Login_Err_Message();

			add_shortcode( 'custom-login-form', array( $this, 'render_login_form' ) );
			add_shortcode( 'custom-register-form', array( $this, 'render_register_form' ) );
			add_shortcode( 'custom-password-lost-form', array( $this, 'render_password_lost_form' ) );
			add_shortcode( 'custom-password-reset-form', array( $this, 'render_password_reset_form' ) );
			add_shortcode( 'account-info', array( $this, 'account_info' ) );
		}

		private function get_template_html( $template_name, $attributes = null ) {
			if ( ! $attributes ) {
				$attributes = array();
			}

			ob_start();

			do_action( 'ova_login_before_' . $template_name );

				ovalg_get_template( $template_name.'.php', array( 'attributes' => $attributes ) );

			do_action( 'ova_login_after_' . $template_name );

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}
		/**
		* A shortcode for rendering the login form.
		*
		* @param  array   $attributes  Shortcode attributes.
		* @param  string  $content     The text content for shortcode. Not used.
		*
		* @return string  The shortcode output
		*/
		public function render_login_form( $attributes, $content = null ) {

    		// Parse shortcode attributes
			$default_attributes = array( 'show_title' => false );
			$attributes = shortcode_atts( $default_attributes, $attributes );
			$show_title = $attributes['show_title'];

			if ( is_user_logged_in() ) {
				return __( 'You are already signed in.', 'ova-login' );
			}

	    	// Pass the redirect parameter to the WordPress login functionality: by default,
	    	// don't specify a redirect, but if a valid redirect URL has been passed as
	    	// request parameter, use it.
			$attributes['redirect'] = ovalg_login_success_url();

			if ( isset( $_REQUEST['redirect_to'] ) ) {
				$attributes['redirect'] = wp_validate_redirect(  $_REQUEST['redirect_to'], $attributes['redirect'] );
			}


			$errors = array();
			if ( isset( $_REQUEST['login'] ) ) {
				$error_codes = explode( ',', $_REQUEST['login'] );

				foreach ( $error_codes as $code ) {
					$errors []= $this->error_message->get_error_message( $code );
				}
			}

			$attributes['mail_err'] = isset( $_REQUEST['mail_err'] ) ? true : false;

			$attributes['recapcha'] = isset( $_REQUEST['recapcha'] ) ? true : false;

    		// Check if user just logged out
			$attributes['logged_out'] = isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true;
			$attributes['errors'] = $errors;

    		// Check if the user just registered
			$attributes['registered'] = isset( $_REQUEST['registered'] );

			// Check if the user just requested a new password 
			$attributes['lost_password_sent'] = isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm';

			// Check if user just updated password
			$attributes['password_updated'] = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';

    		// Render the login form using an external template
			return $this->get_template_html( 'login_form', $attributes );
		}

		/**
		* A shortcode for rendering the new user registration form.
		*
		* @param  array   $attributes  Shortcode attributes.
		* @param  string  $content     The text content for shortcode. Not used.
		*
		* @return string  The shortcode output
		*/
		public function render_register_form( $attributes, $content = null ) {
    		// Parse shortcode attributes
			$default_attributes = array( 'show_title' => false );
			$attributes = shortcode_atts( $default_attributes, $attributes );

			if ( is_user_logged_in() ) {
				return __( 'You are already signed in.', 'ova-login' );
			} elseif ( ! get_option( 'users_can_register' ) ) {
				return __( 'Registering new users is currently not allowed.', 'ova-login' );
			} else {

	     	// Retrieve possible errors from request parameters
				$attributes['errors'] = array();
				if ( isset( $_REQUEST['register-errors'] ) ) {
					$error_codes = explode( ',', $_REQUEST['register-errors'] );

					foreach ( $error_codes as $error_code ) {
						$attributes['errors'] []= $this->error_message->get_error_message( $error_code );
					}
				}	


				return $this->get_template_html( 'register_form', $attributes );
			}
		}

		/**
		 * Lost Password
		 */
		public function render_password_lost_form( $attributes, $content = null ) {
	   	// Parse shortcode attributes
			$default_attributes = array( 'show_title' => false );
			$attributes = shortcode_atts( $default_attributes, $attributes );

			$attributes['errors'] = array();
			if ( isset( $_REQUEST['errors'] ) ) {
				$error_codes = explode( ',', $_REQUEST['errors'] );

				foreach ( $error_codes as $error_code ) {
					$attributes['errors'] []= $this->error_message->get_error_message( $error_code );
				}
			}

			if ( is_user_logged_in() ) {
				return __( 'You are already signed in.', 'ova-login' );
			} else {
				return $this->get_template_html( 'password_lost_form', $attributes );
			}
		}

		/**
		* Reset Password
		*/
		public function render_password_reset_form( $attributes, $content = null ) {
    	// Parse shortcode attributes
			$default_attributes = array( 'show_title' => false );
			$attributes = shortcode_atts( $default_attributes, $attributes );

			if ( is_user_logged_in() ) {
				return __( 'You are already signed in.', 'ova-login' );
			} else {
				if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
					$attributes['login'] = $_REQUEST['login'];
					$attributes['key'] = $_REQUEST['key'];

            // Error messages
					$errors = array();
					if ( isset( $_REQUEST['error'] ) ) {
						$error_codes = explode( ',', $_REQUEST['error'] );

						foreach ( $error_codes as $code ) {
							$errors []= $this->error_message->get_error_message( $code );
						}
					}
					$attributes['errors'] = $errors;

					return $this->get_template_html( 'password_reset_form', $attributes );
				} else {
					return __( 'Invalid password reset link.', 'ova-login' );
				}
			}
		}

		/**
	 * Account Info
	 */
		public function account_info( $attributes, $content = null ) {

			if ( !is_user_logged_in() ) {

	    	// Pass the redirect parameter to the WordPress login functionality: by default,
		   // don't specify a redirect, but if a valid redirect URL has been passed as
		   // request parameter, use it.
				$attributes['redirect'] = '';
				if ( isset( $_REQUEST['redirect_to'] ) ) {
					$attributes['redirect'] = wp_validate_redirect( $_REQUEST['redirect_to'], $attributes['redirect'] );
				}

				return $this->get_template_html( 'login_form', $attributes);
			}


	   // Render the login form using an external template
			return $this->get_template_html( 'account_info', $attributes );
		}
	}
	new Ova_Login_Shortcode();
}