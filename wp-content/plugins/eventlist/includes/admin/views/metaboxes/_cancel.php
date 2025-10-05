<?php if( !defined( 'ABSPATH' ) ) exit();

$allow_cancellation_booking = $this->get_mb_value( 'allow_cancellation_booking', apply_filters( 'el_allow_cancel_booking_default', 'yes' ) );

$cancel_before_x_day = $this->get_mb_value( 'cancel_before_x_day', apply_filters( 'el_cancel_before_x_day', '0' ) );
?>

<div class="edit_event_cancel_booking">

	<label><?php esc_html_e( 'Allow Cancel Booking', 'eventlist' ); ?>:</label>

	
	<input type="radio" value="yes" name="<?php echo esc_attr( $this->get_mb_name( 'allow_cancellation_booking' ) ); ?>" <?php if ( $allow_cancellation_booking == 'yes' ) echo esc_attr('checked') ; ?> />
	<span><?php esc_html_e( 'Yes', 'eventlist' ); ?></span>

	
	<input type="radio" value="no" name="<?php echo esc_attr( $this->get_mb_name( 'allow_cancellation_booking' ) );?>" <?php if ( $allow_cancellation_booking == 'no') echo esc_attr('checked') ; ?> />
	<span><?php esc_html_e( 'No', 'eventlist' ); ?></span>
	

</div>


<div class="cancel_bk_before_x_day">
	<label><?php esc_html_e( 'Cancel booking before x days', 'eventlist' ); ?></label>: 
	<input type="text" name="<?php echo esc_attr( $this->get_mb_name( 'cancel_before_x_day' ) ); ?>" value="<?php echo esc_attr( $cancel_before_x_day ); ?>" >
</div>