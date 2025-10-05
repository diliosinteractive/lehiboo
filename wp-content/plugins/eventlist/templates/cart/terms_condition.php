<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<?php 

	$terms_page = EL()->options->checkout->get( 'terms_condition_page', '' );
	$terms_link = $terms_page ? get_the_permalink( $terms_page ) : '';

 ?>

<div class="error-empty-input error_terms_condition">
	<span ><?php esc_html_e("You have to agree with terms and condition", "eventlist") ?></span>
</div>
<label for="require_terms_condition" class="el_input_checkbox">
	<?php esc_html_e( 'I have read and agree to the website', 'eventlist' ); ?>
	<?php if ( $terms_link ): ?>
		<a href="<?php echo esc_url( $terms_link ); ?>" target="_blank">
	<?php endif; ?>
		<?php esc_html_e( 'terms and conditions', 'eventlist' ); ?>
	<?php if ( $terms_link ): ?>
		</a>
	<?php endif; ?>
	<input type="checkbox" class="required"
	id="require_terms_condition"
		name="require_terms_condition" value="1"
		<?php echo esc_attr( apply_filters( 'el_checkout_terms_condition_default', '' ) ); ?>
	/>

	<span class="checkmark"></span>

</label>	