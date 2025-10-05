<?php
$attributes = $args['attributes'];
?>

<div class="ova-login-form-container">

	<?php if( $attributes ){ ?>

		<?php if ( isset( $attributes['registered'] ) && $attributes['registered'] ) : ?>
			<p class="login-info">
				<?php
				if( OVALG_Settings::show_password() !== 'yes' ){
					esc_html_e( 'You have successfully registered. Check your email for a link to reset your password.', 'ova-login' );
				}else{
					printf(
						__( 'You have successfully registered to <strong>%s</strong>.', 'ova-login' ),
						get_bloginfo( 'name' )
					);	
				}
				
				?>
			</p>
		<?php endif; ?>

		<?php if ( isset( $attributes['mail_err'] ) && $attributes['mail_err'] ) : ?>
			<p class="login-info"><?php esc_html_e( 'Sending email failed. Please try again later.', 'ova-login' ); ?></p>
		<?php endif; ?>

		<!-- Show notification reset passsword in email -->
		<?php if ( $attributes['lost_password_sent'] ) : ?>
			<p class="login-info">
				<?php _e( 'Check your email for a link to reset your password.', 'ova-login' ); ?>
			</p>
		<?php endif; ?>
	

		<!-- Show notification password updated -->
		<?php if ( $attributes['password_updated'] ) : ?>
			<p class="login-info">
				<?php _e( 'Your password has been changed. You can sign in now.', 'ova-login' ); ?>
			</p>
		<?php endif; ?>


		<!-- Show logged out message if user just logged out -->
		<?php if ( isset($attributes['logged_out'] ) && $attributes['logged_out'] ) : ?>
			<p class="login-info">
				<?php _e( 'You have signed out. Would you like to sign in again?', 'ova-login' ); ?>
			</p>
		<?php endif; ?>


		<!-- Show errors if there are any -->
		<?php if ( isset($attributes['errors']) && count( $attributes['errors'] ) > 0 ) : ?>
			<?php foreach ( $attributes['errors'] as $error ) : ?>
				<p class="login-error">
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>

	<?php } ?>

	<h3 class="title"><?php _e( 'Sign In', 'ova-login' ); ?></h3>

	<div class="login-form-container">

		<?php

		// Customize for login before booking
		if( isset( $_GET['idcal'] ) && $_GET['idcal'] ){

			$attributes['redirect'] = add_query_arg( array(
					    'idcal' => $_GET['idcal'],
					), $attributes['redirect'] );
		}
		
		wp_login_form(
			array(
				'remember'       => true,
				'label_username' => __( 'Username', 'ova-login' ),
				'label_log_in' => __( 'Sign In', 'ova-login' ),
				'label_password' => __( 'Password', 'ova-login' ),
				'label_remember' => __( 'Remember Me', 'ova-login' ),
				'label_log_in'   => __( 'Log In','ova-login' ),
				'redirect' => $attributes['redirect'],
			)
		);

		?>

	</div>

	<?php 
		$register_url = ovalg_register_url();
		$lost_pw_url = ovalg_password_lost_url();

	 ?>
	<a href="<?php echo $register_url; ?>" class="forgot-password">
		<?php esc_html_e( 'Register', 'ova-login' ); ?>
	</a> 
	<span class="slash">|</span>
	<a class="forgot-password" href="<?php echo $lost_pw_url; ?>">
		<?php  _e( 'Forgot your password?', 'ova-login' ); ?>
	</a>

</div>
