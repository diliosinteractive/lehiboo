<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>
<div class="container"> 

	<div class="contents">

		<div class="vendor_login">
			<div class="ova-login-form-container">
			    
			    <h3 class="title"><?php _e( 'Sign In', 'eventlist' ); ?></h3>
			    <div class="login-form-container">

				    <?php
				        wp_login_form(
				            array(
				                'remember'       => true,
				                'label_username' => esc_html__( 'Email', 'eventlist' ),
				                'label_log_in' => esc_html__( 'Sign In', 'eventlist' ),
				                'label_password' => esc_html__( 'Password', 'eventlist' ),
				                'label_remember' => esc_html__( 'Remember Me', 'eventlist' ),
				                'label_log_in'   => esc_html__( 'Log In','eventlist' )
				            )
				        );
				    ?>

			    </div>
			   
			   <a href="<?php echo site_url( '/wp-login.php?action=register' ); ?>" class="forgot-password">
					<?php esc_html_e( 'Register', 'ova-login' ); ?>
				</a> 
				<span class="slash">|</span>
				  	
			   <a class="forgot-password" href="<?php echo home_url('/wp-login.php?action=lostpassword'); ?>">
			        <?php  _e( 'Forgot your password?', 'eventlist' ); ?>
			    </a>

			</div>
		</div>

	</div>
	
</div>