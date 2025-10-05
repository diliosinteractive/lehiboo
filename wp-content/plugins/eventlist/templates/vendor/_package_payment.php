<?php
if ( ! defined('ABSPATH') ) {
	exit();
}

$args = array('vendor' => 'package');
$redirect_url = add_query_arg( $args, get_myaccount_page() );
$list_payment_active = el_package_get_payment_active();
$currency = EL()->options->general->get('currency','USD');
$stripe_public_key = EL()->options->checkout->get('stripe_public_key','');
$themes = EL()->options->checkout->get('stripe_themes','stripe');
?>

<div class="modal fade" id="package_payment" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title package_payment_title"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if ( empty( $list_payment_active ) ): ?>
                    <h5 class="mb-3 mt-3"><?php esc_html_e( 'Payment method has not been set up yet.', 'eventlist' ); ?></h5>
                <?php else:
                    $payment_check = $list_payment_active[0];
                    ?>
                
                    <form class="package_payment_form" action="#">
                        <div class="tab tab-choose-payment-method">
                            <h5 class="mb-3 mt-3"><?php esc_html_e( 'Please choose payment method', 'eventlist' ); ?></h5>

                            <?php if ( in_array('woo', $list_payment_active ) ): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_woo" value="woo" <?php checked( $payment_check, 'woo' ); ?> />
                                    <label class="form-check-label" for="payment_woo">
                                        <?php esc_html_e( 'Woocommerce', 'eventlist' ); ?>
                                    </label>
                                </div>
                            <?php endif; ?>

                            <?php if ( in_array('stripe', $list_payment_active ) ): ?>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_stripe" value="stripe" <?php checked( $payment_check, 'stripe' ); ?> />
                                    <label class="form-check-label" for="payment_stripe">
                                        <?php esc_html_e( 'Stripe', 'eventlist' ); ?>
                                    </label>
                                </div>

                            <?php endif; ?>

                            <?php if ( in_array('paypal', $list_payment_active ) ) : ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_paypal" value="paypal" <?php checked( $payment_check, 'paypal' ); ?> />
                                    <label class="form-check-label" for="payment_paypal">
                                        <?php esc_html_e( 'Paypal', 'eventlist' ); ?>
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex mt-3 justify-content-end">
                                <button type="button" class="btn btn-primary el_package_next"><?php esc_html_e( 'Next', 'eventlist' ); ?></button>
                            </div>
                        </div>

                        <?php if ( in_array('woo', $list_payment_active ) ): ?>
                            <div class="tab tab-payment-woo">
                                <h5 class="mb-3 mt-3"><?php esc_html_e( 'Please wait a moment, the page is being redirected to another page..', 'eventlist' ); ?></h5>
                            </div>
                        <?php endif; ?>

                        <?php if ( in_array('paypal', $list_payment_active ) ): ?>
                            <div class="tab tab-payment-paypal">
                                <h5 class="mb-3 mt-3"><?php esc_html_e( 'Please enter payment information.', 'eventlist' ); ?></h5>
                                <div id="paypal_button_wrapper" data-url="<?php echo esc_url( $redirect_url ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_package_paypal' ) ); ?>">
                                    <div id="paypal-button-container"></div>
                                    <div id="result-message"></div>
                                </div>
                                
                                <?php if ( count( $list_payment_active ) > 1 ): ?>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-secondary el_package_prev"><?php esc_html_e( 'Previous', 'eventlist' ); ?></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( in_array('stripe', $list_payment_active ) ): ?>
                            <div class="tab tab-payment-stripe">
                                <h5 class="mb-3 mt-3"><?php esc_html_e( 'Please enter payment information.', 'eventlist' ); ?></h5>
                                <div id="el_payment_stripe_error"></div>
                                <div id="el_payment_stripe"
                                data-key="<?php echo esc_attr( $stripe_public_key ); ?>"
                                data-currency="<?php echo esc_attr( $currency ); ?>"
                                data-url="<?php echo esc_url( $redirect_url ); ?>"
                                data-themes="<?php echo esc_attr( $themes ); ?>"
                                ></div>
                                <div class="d-flex mt-3 justify-content-between">
                                    <div class="left">
                                        <?php if ( count( $list_payment_active ) > 1 ): ?>
                                        
                                            <button type="button" class="btn btn-secondary el_package_prev"><?php esc_html_e( 'Previous', 'eventlist' ); ?></button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="right">
                                        <button class="btn btn-primary el_loading" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        <?php esc_html_e( 'Loading...', 'eventlist' ); ?>
                                        </button>
                                        <button type="button"
                                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_payment_stripe' ) ); ?>"
                                        class="btn btn-success"
                                        id="el_payment_stripe_submit"><?php esc_html_e( 'Submit', 'eventlist' ); ?></button>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php endif; ?>

                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>