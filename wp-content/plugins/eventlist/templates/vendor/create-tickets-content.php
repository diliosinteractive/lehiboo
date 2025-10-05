<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<form id="el_create_tickets_form" action="" method="POST"
	data-nonce="<?php echo esc_attr( wp_create_nonce('el_create_tickets_nonce') ); ?>">
	<!-- Show messages -->
	<div class="el_ticket_message"></div>
	
	<div class="form-group el_select_event">
		<label for="el_create_tickets_select_event" class="col-form-label">
			<?php esc_html_e( 'Select event *', 'eventlist' ); ?>
		</label>

		<select class="form-control el_border_radius_5px" id="el_create_tickets_select_event">
			<option value=""><?php esc_html_e( 'Select event', 'eventlist' ); ?></option>
			<?php if ( ! empty( $event_titles ) ): ?>
				<?php foreach ( $event_titles as $id => $title ): ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $event_id, $id ); ?> >
						<?php echo esc_html( $title ); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>

		<div class="invalid-feedback"><?php esc_html_e( 'You have not selected an event.', 'eventlist' ); ?></div>
	</div>

	<!-- Event Calendar -->
	<div class="el_calendar_event_container"></div>

	<div class="form-group">
		<label for="el_create_tickets_name" class="col-form-label">
			<?php esc_html_e( 'Name', 'eventlist' ); ?>
		</label>
		<input type="text" class="form-control el_border_radius_5px" id="el_create_tickets_name">
	</div>

	<div class="form-group">
		<label for="el_create_tickets_phone" class="col-form-label">
			<?php esc_html_e( 'Phone', 'eventlist' ); ?>
		</label>
		<input type="text" class="form-control el_border_radius_5px" id="el_create_tickets_phone">
	</div>

	<div class="form-group el_email_address">

		<label for="el_create_tickets_email" class="col-form-label">
			<?php esc_html_e( 'Email address *', 'eventlist' ); ?>
		</label>
		<input type="email" class="form-control el_border_radius_5px" id="el_create_tickets_email" placeholder="name@example.com">
		<div class="invalid-feedback">
			<?php esc_html_e( 'Email address is not valid.', 'eventlist' ); ?>				
		</div>

	</div>

	<div class="form-group">
		<label for="el_create_tickets_address" class="col-form-label">
			<?php esc_html_e( 'Address', 'eventlist' ); ?>
		</label>
		<input type="text" class="form-control el_border_radius_5px" id="el_create_tickets_address">
	</div>

</form>
