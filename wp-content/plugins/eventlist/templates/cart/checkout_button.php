<?php if( ! defined( 'ABSPATH' ) ) exit();
global $el_message_cart;
$disable_class = ! empty( $el_message_cart ) ? 'disable' : '';
?>

<input type="hidden"
	name="el_checkout_event_nonce"
	id="el_checkout_event_nonce"
	value="<?php echo esc_attr( wp_create_nonce( 'el_checkout_event_nonce' ) ); ?>">
<div class="message-error"><p></p><a onclick='window.location.reload(true);' href="" class="auto_reload"></a></div>
<div class="checkout_button <?php echo esc_attr( $disable_class ); ?>" id="checkout-button"  >
	<div class="submit-load-more">
		<div class="load-more">
			<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
		</div>
	</div>
	<a href="javascript:void()"><?php esc_html_e("Submit", "eventlist"); ?></a>
</div>