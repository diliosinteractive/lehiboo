<?php  $attributes = $args['attributes']; ?>

<div class="ova-account-info">

	<?php if($attributes){ ?>
		<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
			<?php foreach ( $attributes['errors'] as $error ) : ?>
				<p class="login-error">
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php } ?>

	<div class="woocommerce-account">
		<?php 
		if( class_exists('woocommerce') ){
			echo do_shortcode( '[woocommerce_my_account]' );
		}else{
			$user_info = get_userdata(1);
			echo esc_html__( 'Hello: ', 'ova-login' ). $user_info->user_login;
		}
		?>
	</div>
</div>