<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Err_Message") ) {
	
	class Ova_Login_Err_Message {
		public function __construct(){
			
		}
		/**
		* Finds and returns a matching error message for the given error code.
		*
		* @param string $error_code    The error code to look up.
		*
		* @return string               An error message.
		*/
		public function get_error_message( $error_code, $label = null ) {

			switch ( $error_code ) {

				case 'empty_username':
				return __( 'You do have an email address, right?', 'ova-login' );

				case 'empty_password':
				return __( 'You need to enter a password to login.', 'ova-login' );

				case 'invalid_username':
				return __(
					"We don't have any users with that email address. Maybe you used a different one when signing up?",
					'ova-login'
				);

				case 'incorrect_password':
				$err = __(
					"The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
					'ova-login'
				);
				return sprintf( $err, site_url('/wp-login.php?action=lostpassword') );

        	// Registration errors

				case 'username':
				return __( 'The username you entered is not valid.', 'ova-login' );

				case 'username_exists':
				return __( 'An account exists with this username. please try again', 'ova-login' );

				case 'email':
				return __( 'The email address you entered is not valid.', 'ova-login' );

				case 'email_confirm':
				return __( 'The email confirm not match.', 'ova-login' );

				case 'email_exists':
				return __( 'Email address exists. Please try again', 'ova-login' );

				case 'existing_user_email':
				return __( 'Sorry, that email address is already used!', 'ova-login' );
				

				case 'password_format':
				return __( 'Password is greater than 8 characters and must include at least one number and must include at least one letter. Please try again', 'ova-login' );

				case 'password_not_match':
				return __( 'Password not match. Please try again', 'ova-login' );

				case 'closed':
				return __( 'Registering new users is currently not allowed.', 'ova-login' );

				case 'invalid_email':
				case 'invalidcombo':
				return __( 'There are no users registered with this email address.', 'ova-login' );

				case 'expiredkey':
				case 'invalidkey':
				return __( 'The password reset link you used is not valid anymore.', 'ova-login' );

				case 'password_reset_mismatch':
				return __( "The two passwords you entered don't match.", 'ova-login' );

				case 'password_reset_empty':
				return __( "Sorry, we don't accept empty passwords.", 'ova-login' );

				case 'dcma':
				return __( "You have to tick terms and conditions", 'ova-login' );

				case 'empty':
				return __( 'The field is required.', 'ova-login' );

				case 'empty_has_label':
				return sprintf( __( 'The %s field is required.', 'ova-login' ), $label );

				case 'file_large':
				return __( 'Sorry, your file is too large.', 'ova-login' );

				case 'file_format':
				return __( 'Sorry, only JPG, JPEG, PNG, PDF and DOC files are allowed.', 'ova-login' );

				case 'file_err':
				return __( 'Sorry, there was an error uploading your file.', 'ova-login' );

				case 'recapcha':
				return __( 'Google reCAPTCHA verification failed.', 'ova-login' );

				default:
				break;
			}

			return __( 'An unknown error occurred. Please try again later.', 'ova-login' );
		}
	}
}