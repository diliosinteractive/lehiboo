<?php if ( !defined( 'ABSPATH' ) ) exit();
global $wp_locale;
?>


<div class="form-group el_select_calendar">
	<label for="el_create_tickets_calendar_event" class="col-form-label">
		<?php esc_html_e( 'Event calendar *', 'eventlist' ); ?>
	</label>

	<select class="form-control el_border_radius_5px" id="el_create_tickets_calendar_event">
		<option value=""><?php esc_html_e( 'Select calendar', 'eventlist' ); ?></option>

		

		<?php if ( ! empty( $list_calendar ) ): ?>

			<?php if ( $option_calendar === 'auto' ): ?>
				<?php foreach ( $list_calendar as $value ): ?>
					<option value="<?php echo esc_attr( $value['calendar_id'] ); ?>">
						<?php
							$weekday_num 	= date("w", strtotime( $value['date'] ) );
							$weekday 		= $wp_locale->weekday[$weekday_num];
							$weekday_abbrev = $wp_locale->weekday_abbrev[$weekday];
						?>
						<?php echo esc_html( $weekday_abbrev.', '.date_i18n( $date_format, strtotime( $value['date'] ) ) ); ?>
						<?php echo esc_html( '('.$value['start_time'] .' - '. $value['end_time'].')' ); ?>
					</option>
				<?php endforeach; ?>
			<?php else: ?>
				<?php foreach ( $list_calendar as $value ): ?>
					<?php if ( ! empty( $value['date'] ) && ! empty( $value['end_date'] ) ): ?>
						
						<option value="<?php echo esc_attr( $value['calendar_id'] ); ?>">
							<?php
								$weekday_num 	= date("w", strtotime( $value['date'] ) );
								$weekday 		= $wp_locale->weekday[$weekday_num];
								$weekday_abbrev = $wp_locale->weekday_abbrev[$weekday];

								$weekday_num_end 	= date("w", strtotime( $value['end_date'] ) );
								$weekday_end 		= $wp_locale->weekday[$weekday_num_end];
								$weekday_abbrev_end = $wp_locale->weekday_abbrev[$weekday_end];
							?>
							<?php if ( $value['date'] === $value['end_date'] ): ?>
								<?php echo esc_html( $weekday_abbrev.', '.date_i18n( $date_format, strtotime( $value['date'] ) ) ); ?>
							<?php else: ?>
								<?php echo esc_html( $weekday_abbrev.', '.date_i18n( $date_format, strtotime( $value['date'] ) ) ); ?>
								<?php echo esc_html( ' - '.$weekday_abbrev_end.', '.date_i18n( $date_format, strtotime( $value['end_date'] ) ) ); ?>
							<?php endif; ?>
							
							<?php echo esc_html( '('.$value['start_time'] .' - '. $value['end_time'].')' ); ?>
						</option>

					<?php endif; ?>

				<?php endforeach; ?>
			<?php endif; ?>

		<?php endif; ?>
	</select>

	<div class="invalid-feedback"><?php esc_html_e( 'You have not selected an calendar.', 'eventlist' ); ?></div>
</div>

<!-- Show tickets -->
<div class="el_calendar_event_content" data-seat-option="<?php echo esc_attr( $seat_option ); ?>"></div>


