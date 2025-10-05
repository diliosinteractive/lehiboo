<?php 
if( !defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$_prefix = OVA_METABOX_EVENT;

$allow_cancellation_booking = get_post_meta( $post_id, $_prefix.'allow_cancellation_booking', true) ? get_post_meta( $post_id, $_prefix.'allow_cancellation_booking', true) : apply_filters( 'el_allow_cancel_booking_default', 'yes' );

$cancel_before_x_day = get_post_meta( $post_id, $_prefix.'cancel_before_x_day', true) != '' ? get_post_meta( $post_id, $_prefix.'cancel_before_x_day', true) : apply_filters( 'el_cancel_before_x_day', 0 );

?>

<div class="edit_event_cancel_booking">

	<label><?php esc_html_e( 'Allow Cancel Booking', 'eventlist' ); ?>:</label>

	<label for="allow_cancellation_booking_yes" class="el_input_radio">
		<?php esc_html_e( 'Yes', 'eventlist' ); ?>
		<input type="radio"
			value="yes"
			name="<?php echo $_prefix.'allow_cancellation_booking'; ?>"
			id="allow_cancellation_booking_yes"
			<?php if ( $allow_cancellation_booking == 'yes' ) echo esc_attr('checked') ; ?>
		/>
		<span class="checkmark"></span>
	</label>
	
	<label for="allow_cancellation_booking_no" class="el_input_radio el_ml_10px">
		<?php esc_html_e( 'No', 'eventlist' ); ?>
		<input type="radio"
			value="no"
			id="allow_cancellation_booking_no"
			name="<?php echo $_prefix.'allow_cancellation_booking'; ?>"
			<?php if ( $allow_cancellation_booking == 'no') echo esc_attr('checked') ; ?>
		/>
		<span class="checkmark"></span>
	</label>

</div>


<div class="cancel_bk_before_x_day">
	<label><?php esc_html_e( 'Cancel booking before x days', 'eventlist' ); ?></label>: 
	<input type="text" name="<?php echo $_prefix.'cancel_before_x_day'; ?>" value="<?php echo esc_attr( $cancel_before_x_day ); ?>" >
</div>