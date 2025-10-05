<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
	global $el_message_cart;
	
	$ide 	= isset( $_GET['ide'] ) ? $_GET['ide'] : '';
	$idcal 	= isset( $_GET['idcal'] ) ? $_GET['idcal'] : '';

	$cart_page_current = add_query_arg( 
		array(
		    'ide' => $ide,
		    'idcal' => $idcal,
		), 
		get_cart_page() 
	);

	$login_link = add_query_arg( 
		array(
			'redirect_to' => urlencode( $cart_page_current ),
		), 
		get_login_page()
	);
?>
<?php if ($el_message_cart == "") : ?>
<div class="next_step_button">
	<input 
		type="hidden" 
		name="el_next_event_nonce" 
		id="el_next_event_nonce" 
		value="<?php echo esc_attr( wp_create_nonce( 'el_next_event_nonce' ) ); ?>" />
	<input 
		type="hidden" 
		name="login_link" 
		value="<?php echo esc_url( $login_link ); ?> " />
	<a 
		id="cart-next-step" 
		href="javascript:void(0)">
		<?php esc_html_e("Next", "eventlist") ?>
	</a>
</div>
<?php endif ?>