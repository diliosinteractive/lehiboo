<?php if( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Package ID: *', 'eventlist' ); ?></strong></label>
	<input type="text" id="package" class="package_id" value="<?php echo esc_attr( $this->get_mb_value( 'package_id', uniqid() ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'package_id' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="1" />
	
</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Registration fee', 'eventlist' ); ?>: </strong></label>
	<input type="text" id="fee_register_package" class="fee_register_package" value="<?php echo esc_attr( $this->get_mb_value( 'fee_register_package' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'fee_register_package' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="0" />
	<span><?php esc_html_e( 'USD', 'eventlist' ) ?></span>
	
</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Limited time period', 'eventlist' ); ?>: </strong></label>
	<input type="number" id="package_time" class="package_time" value="<?php echo esc_attr( $this->get_mb_value( 'package_time', 30 ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'package_time' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="10" />
	<span><?php esc_html_e( 'Day (-1 for unlimit)', 'eventlist' ) ?></span>
	
</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Event Total', 'eventlist' ); ?>: </strong></label>
	<input type="number" id="package_total_event" class="package_total_event" value="<?php echo esc_attr( $this->get_mb_value( 'package_total_event', 1 ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'package_total_event' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="10" />
	<span><?php esc_html_e( 'Day (-1 for unlimit)', 'eventlist' ) ?></span>
	
</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Fee per paid ticket', 'eventlist' ); ?>: </strong></label>
	<input type="text" id="fee_percent_paid_ticket" class="fee_percent_paid_ticket" value="<?php echo esc_attr( $this->get_mb_value( 'fee_percent_paid_ticket' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'fee_percent_paid_ticket' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="0.2" />
	<span>%</span>
	<strong>+</strong>
	<input type="text" id="fee_default_paid_ticket" class="fee_default_paid_ticket" value="<?php echo esc_attr( $this->get_mb_value( 'fee_default_paid_ticket' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'fee_default_paid_ticket' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="0.5" />
	<span></span>

</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Fee per free ticket', 'eventlist' ); ?>: </strong></label>
	<input type="text" id="fee_percent_free_ticket" class="fee_percent_free_ticket" value="<?php echo esc_attr( $this->get_mb_value( 'fee_percent_free_ticket' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'fee_percent_free_ticket' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="0" />
	<span>%</span>
	<strong>+</strong>
	<input type="text" id="fee_default_free_ticket" class="fee_default_free_ticket" value="<?php echo esc_attr( $this->get_mb_value( 'fee_default_free_ticket' ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'fee_default_free_ticket' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="0" />
	<span><?php esc_html_e( 'USD', 'eventlist' ) ?></span>

</div>

<div class="ova_row">
	<?php $list_attendees = $this->get_mb_value( 'list_attendees'); ?>
	<label class="label"><strong><?php esc_html_e( 'List of attendees', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'list_attendees' )) ?>">
				
		<option value="yes" <?php selected( 'yes', $list_attendees, 'selected' ); ?> ><?php esc_html_e( 'Yes', 'eventlist' ); ?></option>

		<option value="no" <?php selected( 'no', $list_attendees, 'selected' ); ?> ><?php esc_html_e( 'No', 'eventlist' ); ?></option>

	</select>
</div>

<div class="ova_row">
	<?php $export_attendees = $this->get_mb_value( 'export_attendees'); ?>
	<label class="label"><strong><?php esc_html_e( 'Export attendees', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'export_attendees' )) ?>">
				
		<option value="yes" <?php selected( 'yes', $export_attendees, 'selected' ); ?> ><?php esc_html_e( 'Yes', 'eventlist' ); ?></option>

		<option value="no" <?php selected( 'no', $export_attendees, 'selected' ); ?> ><?php esc_html_e( 'No', 'eventlist' ); ?></option>

	</select>
</div>


<div class="ova_row">
	<?php $list_tickets = $this->get_mb_value( 'list_tickets'); ?>
	<label class="label"><strong><?php esc_html_e( 'List of tickets', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'list_tickets' )) ?>">
				
		<option value="yes" <?php selected( 'yes', $list_tickets, 'selected' ); ?> ><?php esc_html_e( 'Yes', 'eventlist' ); ?></option>

		<option value="no" <?php selected( 'no', $list_tickets, 'selected' ); ?> ><?php esc_html_e( 'No', 'eventlist' ); ?></option>

	</select>
</div>

<div class="ova_row">
	<?php $export_tickets = $this->get_mb_value( 'export_tickets'); ?>
	<label class="label"><strong><?php esc_html_e( 'Export tickets', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'export_tickets' )) ?>">
				
		<option value="yes" <?php selected( 'yes', $export_tickets, 'selected' ); ?> ><?php esc_html_e( 'Yes', 'eventlist' ); ?></option>

		<option value="no" <?php selected( 'no', $export_tickets, 'selected' ); ?> ><?php esc_html_e( 'No', 'eventlist' ); ?></option>

	</select>
</div>

<div class="ova_row">
	<?php $change_tax = $this->get_mb_value( 'change_tax'); ?>
	<label class="label"><strong><?php esc_html_e( 'Change Tax per event', 'eventlist' ); ?>: </strong></label>
	<select name="<?php echo esc_attr($this->get_mb_name( 'change_tax' )) ?>">
				
		<option value="yes" <?php selected( 'yes', $change_tax, 'selected' ); ?> ><?php esc_html_e( 'Yes', 'eventlist' ); ?></option>

		<option value="no" <?php selected( 'no', $change_tax, 'selected' ); ?> ><?php esc_html_e( 'No', 'eventlist' ); ?></option>

	</select>
</div>

<div class="ova_row">

	<label class="label"><strong><?php esc_html_e( 'Sort package at frontend', 'eventlist' ); ?>: </strong></label>
	<input type="text" id="order_package" class="order_package" value="<?php echo esc_attr( $this->get_mb_value( 'order_package', 1 ) ); ?>" name="<?php echo esc_attr( $this->get_mb_name( 'order_package' ) ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="1" />
	
</div>

<?php wp_nonce_field( 'ova_metaboxes', 'ova_metaboxes' ); ?>

