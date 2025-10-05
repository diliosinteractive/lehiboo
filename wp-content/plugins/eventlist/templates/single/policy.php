<?php 

$post_id = get_the_ID();
$_prefix = OVA_METABOX_EVENT;

$allow_cancellation_booking = get_post_meta( $post_id, $_prefix.'allow_cancellation_booking', true);

$cancel_before_x_day = get_post_meta( $post_id, $_prefix.'cancel_before_x_day', true);
?>

<?php if ( EL()->options->cancel->get('cancel_enable', 1 ) ) { ?>

	<div class='event_section_white'> 
		<h3 class="show_policy second_font heading">
			<?php esc_html_e( 'Refund Policy', 'eventlist' ); ?>
		</h3>

		<?php if ( $allow_cancellation_booking == 'no' ) {?> 

			<span>
				<?php esc_html_e( 'Don\'t allow cancel booking', 'eventlist' ); ?>
			</span>

		<?php } elseif ($allow_cancellation_booking == 'yes') { ?>

			<span>
				<?php echo sprintf( esc_html__( 'Cancel booking before %1$s %2$s', 'eventlist' ), esc_html($cancel_before_x_day), $cancel_before_x_day == 1 ? esc_html__('day', 'eventlist') : esc_html__('days', 'eventlist') ); ?>
			</span>

		<?php } ?>
	</div>

<?php } ?>