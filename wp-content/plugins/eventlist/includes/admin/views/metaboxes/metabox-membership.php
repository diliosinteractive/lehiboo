<?php if( !defined( 'ABSPATH' ) ) exit(); ?>
<?php 
$format 		= el_date_time_format_js();
$first_day 		= el_first_day_of_week();
$placeholder 	= date_i18n( el_date_time_format_js_reverse($format), '1577664000' );

$transaction_id = $this->get_mb_value( 'transaction_id', '' );

$event_limit = $this->get_mb_value('event_limit', 0 );

$start_date = $this->get_mb_value( 'membership_start_date', '' ) ? gmdate( el_date_time_format_js_reverse( $format ), $this->get_mb_value( 'membership_start_date', '' ) ) : gmdate( el_date_time_format_js_reverse( $format ), strtotime( gmdate( 'Y-m-d' ) ) );

$end_date = $this->get_mb_value( 'membership_end_date', '' ) != '-1' ? gmdate( el_date_time_format_js_reverse( $format ), $this->get_mb_value( 'membership_end_date', strtotime( gmdate( 'Y-m-d' ) ) ) ) : '-1';

$list_package = EL_Package::instance()->el_list_packages();

 ?>

<div class="ova_row">
	<label class="label" for="info_organizer"><strong><?php esc_html_e( 'Package', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'membership_package_id' )); ?>" id="">

		<option value=""><?php esc_html_e( 'Choose Package', 'eventlist' ); ?></option>

		<?php $package_id_current = $this->get_mb_value( 'membership_package_id', '' );
			foreach ($list_package as $value) { 

			$pid = $value->ID;
			$package_id = get_post_meta( $pid, OVA_METABOX_EVENT.'package_id', true );
			$package_title = $value->post_title;
			?>
			<option value="<?php echo esc_attr( $package_id ); ?>" <?php  selected( $package_id, $package_id_current ) ?> >
				<?php echo esc_html( $package_title ); ?>
			</option>
		<?php } ?>

	</select>
</div>    	
<br>

<div class="ova_row">

	<label class="label">
		<strong><?php esc_html_e( 'Start Date: *', 'eventlist' ); ?></strong>
	</label>

	<input type="text"  
		class="membership_date" 
		value="<?php echo esc_attr( $start_date ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'membership_start_date' ) ); ?>" 
		autocomplete="off" autocorrect="off" autocapitalize="none"  
		data-format="<?php echo esc_attr( $format ); ?>" 
		data-firstday="<?php echo esc_attr( $first_day ); ?>" 
	/>
	
</div>
<br>

<div class="ova_row">

	<label class="label">
		<strong><?php esc_html_e( 'End Date: *', 'eventlist' ); ?></strong>
	</label>
	
	<input type="text" 
		class="<?php echo $end_date != '-1' ? 'membership_date' : ''; ?>" 
		value="<?php echo esc_attr( $end_date ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'membership_end_date' ) ); ?>" 
		autocomplete="off" autocorrect="off" autocapitalize="none" 
		data-format="<?php echo esc_attr( $format ); ?>" 
		data-firstday="<?php echo esc_attr( $first_day ); ?>" 
	/>
	<span><?php esc_html_e( 'Insert -1 for unlimit','eventlist' ); ?></span>
	
</div>

<br>


<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Event limit: ', 'eventlist' ); ?></strong></label>
	<input type="text" 
		class="event_limit" 
		value="<?php echo esc_attr( $this->get_mb_value( 'event_limit', '' ) ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'event_limit' ) ); ?>" 
		autocomplete="off" autocorrect="off" autocapitalize="none"  
		/>
</div>
<br>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Total: ', 'eventlist' ); ?></strong></label>
	<input type="text" 
		class="total" 
		value="<?php echo esc_attr( $this->get_mb_value( 'total', '' ) ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'total' ) ); ?>" 
		autocomplete="off" autocorrect="off" autocapitalize="none"  
		/>
	<span><?php echo esc_html( EL()->options->general->get('currency') ); ?></span>
</div>
<br>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Payment: ', 'eventlist' ); ?></strong></label>
	<input type="text" class="payment" value="<?php echo esc_attr( $this->get_mb_value( 'payment', '' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'payment' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
	
</div>
<br>

<?php if ( $transaction_id ): ?>
	<div class="ova_row">

		<label class="label"><strong><?php esc_html_e( 'Transaction ID: ', 'eventlist' ); ?></strong></label>
		<input type="text" class="transaction_id" value="<?php echo esc_attr( $transaction_id ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'transaction_id' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
		
	</div>
	<br>
<?php endif; ?>


<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'User ID: ', 'eventlist' ); ?></strong></label>
	<input type="text" 
		class="membership_user_id" 
		value="<?php echo esc_attr( $this->get_mb_value( 'membership_user_id', '' ) ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'membership_user_id' ) ); ?>" 
		autocomplete="off" autocorrect="off" autocapitalize="none"
		
		/>
	
</div>


<?php //wp_nonce_field( 'ova_metaboxes', 'ova_metaboxes' ); ?>

