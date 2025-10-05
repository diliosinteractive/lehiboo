<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
	$id_event = (isset($_GET['ide'])) ? $_GET['ide'] : 0;
	$id_event = (int)$id_event;
	global $el_message_cart;
?>
<?php if ($el_message_cart == "") : ?>
<div class="cart-discount-button">
	<a id="cart-discount-button" href="javascript:void(0)"><?php esc_html_e("Enter Discount Code", "eventlist") ?></a>
	<div class="form-discount">
		<input type="text" placeholder="<?php esc_html_e("DISCOUNT CODE", "eventlist") ?>">
		<button data-id="<?php echo esc_attr($id_event) ?>" id="submit-code-discount"><?php esc_html_e("Apply", "eventlist") ?></button>
		<i class="fas fa-times"></i>
		<p class="error"><?php esc_html_e("Invalid Discount Code", "eventlist") ?></p>
	</div>
</div>
<?php endif ?>