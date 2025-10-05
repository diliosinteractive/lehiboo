<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Recapcha") ) {
	
	class Ova_Login_Recapcha {

		private $errors;

		private $error_message;

		private $secret_key;

		public function __construct(){

			include_once OVALGDIR . '/inc/class-ovalg-err-message.php';
			$this->error_message 	= new Ova_Login_Err_Message();
			$this->errors 			= new WP_Error();
			$this->secret_key 		= OVALG_Settings::recapcha_secret_key();

			$recapcha_type 			= OVALG_Settings::recapcha_type();
			$enable_login 			= OVALG_Settings::recapcha_enable_login();
			$enable_register 		= OVALG_Settings::recapcha_enable_register();
			$enable_lost_password 	= OVALG_Settings::recapcha_enable_lost_password();
			$enable_reset_password 	= OVALG_Settings::recapcha_enable_reset_password();
			$enable_comment_form 	= OVALG_Settings::recapcha_enable_comment_form();
			$enable_mail_vendor 	= OVALG_Settings::recapcha_enable_send_mail_vendor();
			$enable_create_event 	= OVALG_Settings::recapcha_enable_create_event();
			$enable_cart_event 		= OVALG_Settings::recapcha_enable_cart_event();

			$hooks = array(
				'login_form_middle' 				=> $enable_login,
				'meup_register_recapcha' 			=> $enable_register,
				'meup_lost_password_recapcha' 		=> $enable_lost_password,
				'meup_reset_password_recapcha' 		=> $enable_reset_password,
			);
			// add filter hook
			foreach ( $hooks as $hook => $enable ) {
				if ( ovalg_recaptcha_is_key_setup_complete() && $enable ) {
					if ( $recapcha_type == 'v3' ) {
						add_action( $hook, array( $this, 'ovalg_display_captcha_input' ) );
					} elseif ( $recapcha_type == 'v2' ) {
						add_action( $hook, array( $this, 'ovalg_recaptcha_display_wrapper' ) );
					}
				}
			}

			add_filter( 'comment_form_fields', array( $this, 'ovalg_move_comment_field_to_bottom' ) );
			add_filter( 'comment_form_fields', array( $this, 'ovalg_move_cookies_field_to_bottom' ) );

			// Comment form
			if ( ovalg_recaptcha_is_key_setup_complete() && $enable_comment_form ) {
				if ( $recapcha_type == 'v3' ) {
					add_filter( 'comment_form_default_fields', array( $this, 'ovalg_comment_display_captcha_input' ) );
				} elseif ( $recapcha_type == 'v2' ) {
					add_filter( 'comment_form_default_fields', array( $this, 'ovalg_comment_recaptcha_display_wrapper' ) );
				}
				add_filter( 'comment_form_fields', array( $this, 'ovalg_move_recapcha_field_to_bottom' ) );
				add_action( 'preprocess_comment', array( $this, 'ovalg_recaptcha_process_comment_form' ) );
			}
			// mail vendor
			if ( ovalg_recaptcha_is_key_setup_complete() && $enable_mail_vendor ) {
				if ( $recapcha_type == 'v3' ) {
					add_action( 'meup_send_mail_vendor_recapcha', array( $this, 'ovalg_display_captcha_input' ) );
				} elseif ( $recapcha_type == 'v2' ) {
					add_action( 'meup_send_mail_vendor_recapcha', array( $this, 'ovalg_event_recaptcha_display_wrapper' ) );
				}
			}
			// create event
			if ( ovalg_recaptcha_is_key_setup_complete() && $enable_create_event ) {
				if ( $recapcha_type == 'v3' ) {
					add_action( 'meup_send_create_event_recapcha', array( $this, 'ovalg_event_recapcha_input' ) );
				} elseif ( $recapcha_type == 'v2' ) {
					add_action( 'meup_send_create_event_recapcha', array( $this, 'ovalg_event_mess_recaptcha_display_wrapper' ) );
				}
			}
			// cart event
			if ( ovalg_recaptcha_is_key_setup_complete() && $enable_cart_event ) {
				if ( $recapcha_type == 'v3' ) {
					add_action( 'meup_cart_event_recapcha', array( $this, 'ovalg_event_recapcha_input' ) );
				} elseif ( $recapcha_type == 'v2' ) {
					add_action( 'meup_cart_event_recapcha', array( $this, 'ovalg_event_mess_recaptcha_display_wrapper' ) );
				}
			}
			
		}

		public function ovalg_display_captcha_input(){
			?>
			<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" data-key="<?php echo esc_attr( $this->secret_key ); ?>">
			<?php
		}

		public function ovalg_event_recapcha_input(){
			?>
			<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" data-key="<?php echo esc_attr( $this->secret_key ); ?>" data-mess="<?php echo esc_attr__( 'Google reCAPTCHA verification failed.', 'ova-login' ); ?>">
			<?php
		}

		public function ovalg_recaptcha_display_wrapper() {
			return '<div class="ovalg-recaptcha-wrapper"></div>';
		}

		public function ovalg_event_recaptcha_display_wrapper() {
			?>
			<div id="ovaevent-recaptcha-wrapper"></div><input type="hidden" data-key="<?php echo esc_attr( $this->secret_key ); ?>" id="ovaevent_recapcha_token" data-pass="no" value="">
			<?php
		}

		public function ovalg_event_mess_recaptcha_display_wrapper() {
			?>
			<div id="ovaevent-recaptcha-wrapper"></div><input type="hidden" data-key="<?php echo esc_attr( $this->secret_key ); ?>" data-mess="<?php echo esc_attr__( 'Google reCAPTCHA verification failed.', 'ova-login' ); ?>" id="ovaevent_recapcha_token" data-pass="no" value="">
			<?php
		}

		public function ovalg_comment_display_captcha_input( $fields ){
			$fields['recapcha'] = '<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response">';
    		return $fields;
		}

		public function ovalg_comment_recaptcha_display_wrapper( $fields ) {
			$fields['recapcha'] = '<div class="ovalg-recaptcha-wrapper"></div>';
    		return $fields;
		}

		public function ovalg_move_comment_field_to_bottom( $fields ){
			$comment_field = isset( $fields['comment'] ) ? $fields['comment'] : '';
			if ( $comment_field ) {
				unset( $fields['comment'] );
			}
			$fields['comment'] = $comment_field;

			return $fields;
		}

		public function ovalg_move_recapcha_field_to_bottom( $fields ) {
			$recapcha_field = isset( $fields['recapcha'] ) ? $fields['recapcha'] : '';
			if ( $recapcha_field ) {
				unset( $fields['recapcha'] );
			}
			$fields['recapcha'] = $recapcha_field;

			return $fields;
		}

		public function ovalg_move_cookies_field_to_bottom( $fields ) {
			$cookies_field = isset( $fields['cookies'] ) ? $fields['cookies'] : '';
			if ( $cookies_field ) {
				unset( $fields['cookies'] );
			}
			$fields['cookies'] = $cookies_field;

			return $fields;
		}

		public function ovalg_recaptcha_process_comment_form( $commentdata ){
			if ( absint( $commentdata['user_ID'] ) > 0 ) {
			    return $commentdata;
			}
			if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) {
				$recapcha 		= $_POST['g-recaptcha-response'];
				$check_recapcha = $this->validate_recapcha( $recapcha );
			}
			return $commentdata;
		}

		public function validate_recapcha( $recapcha ){
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
					wp_die(
						$this->errors,
						__( 'Error', 'ova-login' ),
						array(
							'response'  => 403,
							'back_link' => true,
						)
					);
				}
			} else {
				$this->errors->add( 'recapcha', $this->error_message->get_error_message( 'recapcha' ) );
				wp_die(
					$this->errors,
					__( 'Error', 'ova-login' ),
					array(
						'response'  => 403,
						'back_link' => true,
					)
				);
			}
		}
	}
	if ( apply_filters( 'meup_enable_recapcha', true ) ) {
		new Ova_Login_Recapcha();
	}
}