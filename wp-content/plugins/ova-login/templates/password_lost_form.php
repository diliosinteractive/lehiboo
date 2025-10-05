<?php  $attributes = $args['attributes']; ?>

<div id="password-lost-form" class="ova-login-form-container">

	<h3 class="title"><?php esc_html_e( 'Forgot Your Password?', 'ova-login' ); ?></h3>
	<p>
		<?php esc_html_e( "Enter your email address and we'll send you a link you can use to pick a new password.", 'ova-login'); ?>
	</p>

	<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
		<?php foreach ( $attributes['errors'] as $error ) : ?>
			<p class="login-error">
				<?php echo $error; ?>
			</p>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php 
		
		$lang = '';
		if( defined( 'ICL_LANGUAGE_CODE' ) ){

			global $sitepress;
			if ( $sitepress->get_default_language() != ICL_LANGUAGE_CODE ){
				$lang = '&lang='.ICL_LANGUAGE_CODE;
			}

		}


	?>

	<form id="lostpasswordform" action="<?php echo site_url( '/wp-login.php?action=lostpassword'.$lang ); ?>" method="post">

		<p class="login-username">
			<label for="user_pass"><?php esc_html_e( 'Email', 'ova-login' ); ?></label>
			<input type="text" name="user_login" id="user_pass">
		</p>
		<?php echo apply_filters( 'meup_lost_password_recapcha', '' ); ?>
		<p class="resetpass-submit login-submit">
			<input type="submit" name="submit" class="button button-primary lostpassword-button" id="wp-submit"
			value="<?php esc_attr_e( 'Reset Password', 'ova-login' ); ?>"/>
		</p>

	</form>
</div>