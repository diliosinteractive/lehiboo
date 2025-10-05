<?php  $attributes = $args['attributes']; ?>

<div id="password-reset-form" class="ova-login-form-container">

	<h3 class="title"><?php esc_html_e( 'Pick a New Password', 'ova-login' ); ?></h3>

	<?php 
		
		$lang = '';
		if( defined( 'ICL_LANGUAGE_CODE' ) ){

			global $sitepress;
			if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE ){
				$lang = '&lang='.ICL_LANGUAGE_CODE;
			}

		}

	?>

	<form name="resetpassform" id="resetpassform" action="<?php echo site_url( '/wp-login.php?action=resetpass'.$lang ); ?>" method="post" autocomplete="off">

		<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
		<input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />

		<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
			<?php foreach ( $attributes['errors'] as $error ) : ?>
				<p class="login-error">
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>

		<p class="form-user_pass">
			<label for="user_pass"><?php esc_html_e( 'New password', 'ova-login' ) ?></label>
			<input type="password" name="pass1" id="user_pass" class="input_password" size="20" value="" autocomplete="off" />
			<i class="fas fa-key"></i>
		</p>
		<p class="form-user_pass">
			<label for="user_confirm_pass"><?php esc_html_e( 'Repeat new password', 'ova-login' ) ?></label>
			<input type="password" name="pass2" id="user_confirm_pass" class="input_password" size="20" value="" autocomplete="off" />
			<i class="fas fa-key"></i>
		</p>

		<p class="description"><?php echo wp_get_password_hint(); ?></p>

		<?php echo apply_filters( 'meup_reset_password_recapcha', '' ); ?>

		<p class="resetpass-submit login-submit">
			<input type="submit" name="submit" id="wp-submit"
			class="button button-primary" value="<?php esc_html_e( 'Reset Password', 'ova-login' ); ?>" />
		</p>
	</form>
</div>