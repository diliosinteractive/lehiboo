<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;


$first_name = $last_name = $phone = $email = $company = $country = $postcode = $city = $state = $address_2 = '';
$booking_id = null;

foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	
	$first_name = isset( $cart_item['first_name'] ) ? $cart_item['first_name'] : '';
	$last_name = isset( $cart_item['last_name'] ) ? $cart_item['last_name'] : '';
	$phone = isset( $cart_item['phone'] ) ? $cart_item['phone'] : '' ;
	$email = isset( $cart_item['email'] ) ? $cart_item['email'] : '';
	$address = isset( $cart_item['address'] ) ? $cart_item['address'] : '';
	$booking_id = isset( $cart_item['booking_id'] ) ? $cart_item['booking_id'] : '';

	$custom_checkout 	= isset( $cart_item['custom_checkout'] ) ? json_decode( $cart_item['custom_checkout'], true ) : '';
	if( is_array( $custom_checkout ) && ! empty( $custom_checkout ) ) {
		foreach( $custom_checkout as $key => $value ) {
			
			switch ( $key ) {

				case 'company':
					$company = $value['value'];
					break;

				case 'country':
					$country = $value['value'];
					break;	

				case 'postcode':
					$postcode = $value['value'];
					break;
				
				case 'city':
					$city = $value['value'];
					break;

				case 'state':
					$state = $value['value'];
					break;

				case 'address_2':
					$address_2 = $value['value'];
					break;
			}

		}
	}
	

	

	if( $booking_id ) break;
}


?>
<div class="woocommerce-billing-fields">
	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3>
			<?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?>
		</h3>

	<?php else : ?>

		<h3>
			<?php esc_html_e( 'Billing details', 'woocommerce' ); ?>
		</h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );

		foreach ( $fields as $key => $field ) {

			switch ( $key ) {
				case 'billing_first_name':
					$value = $first_name ? $first_name: $checkout->get_value( $key );
					break;

				case 'billing_last_name':
					$value = $last_name ? $last_name: $checkout->get_value( $key );
					break;

				case 'billing_phone':
					$value = $phone ? $phone: $checkout->get_value( $key );
					break;

				case 'billing_email':
					$value = $email ? $email: $checkout->get_value( $key );
					break;

				case 'billing_address_1':
					$value = $address ? $address: $checkout->get_value( $key );
					break;

				case 'billing_company':
					$value = $company ? $company: $checkout->get_value( $key );
					break;

				case 'billing_country':
					$value = $country ? $country: $checkout->get_value( $key );
					break;

				case 'billing_postcode':
					$value = $postcode ? $postcode: $checkout->get_value( $key );
					break;

				case 'billing_city':
					$value = $city ? $city: $checkout->get_value( $key );
					break;					
				
				case 'billing_state':
					$value = $state ? $state: $checkout->get_value( $key );
					break;	

				case 'billing_address_2':
					$value = $address_2 ? $address_2: $checkout->get_value( $key );
					break;

				default:
					$value = $checkout->get_value( $key );
					break;
			}

			woocommerce_form_field( $key, $field, $value );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
