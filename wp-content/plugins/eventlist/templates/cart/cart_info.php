<?php if( ! defined( 'ABSPATH' ) ) exit();
global $el_message_cart;
$cookie_ide = isset( $_COOKIE['id_event'] ) ? ( $_COOKIE['id_event'] ) : '';
$ide 		= isset( $_GET['ide'] ) ? $_GET['ide'] : $cookie_ide;


$seat_option = get_post_meta($ide, OVA_METABOX_EVENT . 'seat_option', true) ? get_post_meta($ide, OVA_METABOX_EVENT . 'seat_option', true) : '';

?>

<?php if ( $el_message_cart == "" ):
	if ( $seat_option != 'map' ): ?>
		<div class="cart-info">
			<div class="wp-cart-info">
				<h3 class="cart_title">
					<span><?php esc_html_e("Booking Information", "eventlist"); ?></span>
					<span class="edit"><?php esc_html_e("Edit", "eventlist"); ?></span>
				</h3>
				<div class="content-cart-info">
					<span class="placeholder"><?php esc_html_e("Please Select Your Ticket", "eventlist"); ?></span>
					<div class="item-info header">
						<span><?php esc_html_e("Ticket Type", "eventlist"); ?></span>
						<span><?php esc_html_e("Quantity", "eventlist"); ?></span>
					</div>
					<div class="wp-content-item"></div>
					<div class="total-discount">
						<p class="text"><?php esc_html_e("Discount", "eventlist"); ?></p>
						<p class="discount-number"></p>
					</div>
					<div class="total-tax">
						<p class="text"><?php esc_html_e("Tax", "eventlist"); ?></p>
						<p class="tax-number"></p>
					</div>
					<div class="system-fee">
						<p class="text"><?php esc_html_e("System Fee", "eventlist"); ?></p>
						<p class="system-fee-number"></p>
					</div>

					<!-- end wp-content-item -->
				</div>
			</div>
			<div class="total-cart-info">
				<span class="text"><?php esc_html_e("Total","eventlist" ); ?></span>
				<span class="total-price"><?php echo esc_html__("0", "eventlist"); ?></span>
			</div>
		</div>
	<?php else: ?>
		<div class="cart-info">
			<div class="wp-cart-info">
				<h3 class="cart_title">
					<span><?php esc_html_e("Booking Information", "eventlist"); ?></span>
					<span class="edit"><?php esc_html_e("Edit", "eventlist"); ?></span>
				</h3>
				<div class="content-cart-info">
					<span class="placeholder"><?php esc_html_e("Please Select Your Seat", "eventlist"); ?></span>
					<div class="item-info header">
						<span><?php esc_html_e("Seat", "eventlist"); ?></span>
						<span><?php esc_html_e("Price", "eventlist"); ?></span>
					</div>
					<div class="wp-content-item"></div>
					<ul class="extra-services" data-total="0"></ul>
					<div class="total-discount">
						<p class="text"><?php esc_html_e("Discount", "eventlist"); ?></p>
						<p class="discount-number"></p>
					</div>
					<div class="total-tax">
						<p class="text"><?php esc_html_e("Tax", "eventlist"); ?></p>
						<p class="tax-number"></p>
					</div>
					<div class="system-fee">
						<p class="text"><?php esc_html_e("System Fee", "eventlist"); ?></p>
						<p class="system-fee-number"></p>
					</div>
					<!-- end wp-content-item -->
				</div>
			</div>
			<div class="total-cart-info">
				<span class="text"><?php esc_html_e("Total","eventlist" ); ?></span>
				<span class="total-price"><?php echo esc_html__("0", "eventlist"); ?></span>
			</div>
		</div>
	<?php endif;
endif; ?>
