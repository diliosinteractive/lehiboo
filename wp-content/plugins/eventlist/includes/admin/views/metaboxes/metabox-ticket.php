<?php if ( !defined( 'ABSPATH' ) ) exit();

global $post;

$time 		= el_calendar_time_format();
$format 	= el_date_time_format_js();
$first_day 	= el_first_day_of_week();

$placeholder_dateformat = el_placeholder_dateformat();
$placeholder_timeformat = el_placeholder_timeformat();
$extra_services = $this->get_mb_value( 'extra_service' );

$extra_services_display = el_extra_sv_ticket( $extra_services );
?>
<div class="el_ticket_detail">
	<div class="ova_row">
		<label>
			<div class="ova_row">
				<label>
					<strong><?php esc_html_e( "Ticket Number", "eventlist" ); ?>:</strong>
					#<?php echo esc_html( $post->ID ); ?>
				</label>
				<br><br>
			</div>
		</label>
	</div>
	<div class="ova_row">
		<label>
			<div class="ova_row">
				<label>
					<strong><?php esc_html_e( "Booking ID", "eventlist" ); ?>:</strong>
					#<?php echo esc_html( $this->get_mb_value( 'booking_id' ) ); ?>
				</label>
				<br><br>
			</div>
		</label>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Ticket Type", "eventlist" ); ?>:</strong>
			<?php echo esc_html( get_the_title() ); ?>
		</label>
		<br><br>
	</div>
	<?php if ( ! empty( $extra_services_display ) ): ?>
		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Extra Services", "eventlist" ); ?>:</strong>
				<span class="extra_services">
					<?php echo wp_kses_post( $extra_services_display ); ?>
				</span>
				
			</label>
			<br><br>
		</div>
	<?php endif; ?>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Event Name", "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'name_event' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'name_event' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Customer Name", "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'name_customer' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'name_customer' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Customer Phone", "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'phone_customer' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'phone_customer' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Customer Email", "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'email_customer' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'email_customer' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Customer Address",  "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'address_customer' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'address_customer' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>

	<?php
		// Custom Checkout Field
		$list_checkout_field = get_option( 'ova_booking_form', array() );
		$data_checkout_field = $this->get_mb_value( 'data_checkout_field' );
		$data_checkout_field = ! empty( $data_checkout_field ) ? json_decode( $data_checkout_field , true) : [];

		$list_key_checkout_field 	= [];
		$list_type_checkout_field 	= [];

		if ( ! empty( $list_checkout_field ) && is_array( $list_checkout_field ) && ! empty( $data_checkout_field ) && is_array( $data_checkout_field ) ):
			$special_fields = [ 'textarea', 'select', 'radio', 'checkbox', 'file' ];

			foreach ( $list_checkout_field as $key => $field ):
				if ( array_key_exists( $key, $data_checkout_field ) && array_key_exists( 'enabled', $field ) && $field['enabled'] === 'on' ) {
					$list_key_checkout_field[] = $key;
					$list_type_checkout_field[$key] = $field['type'];

					if ( array_key_exists( 'required', $field ) && $field['required'] == 'on' ) {
						$class_required = ' required';
					} else {
						$class_required = '';
					}
				} else {
					continue;
				}
	?>
		<div class="ova_row">
			<label for="<?php echo esc_attr( $key ) ?>">
				<strong><?php echo esc_html( $field['label'] ); ?>: </strong>
				<?php if ( ! in_array( $field['type'] , $special_fields ) ): ?>
					<input 
						id="<?php echo esc_attr( $key ); ?>" 
						type="text" 
						name="<?php echo esc_attr( $key ); ?>" 
						class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'].$class_required ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
						value="<?php echo esc_attr( $data_checkout_field[$key] ); ?>" />
				<?php endif; ?>
				<?php if ( $field['type'] === 'textarea' ): ?>
					<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'].$class_required ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" cols="50" rows="8"><?php echo esc_html( $data_checkout_field[$key] ); ?></textarea>
				<?php endif; ?>

				<?php if ( $field['type'] === 'select' ): 
					$ova_options_key = $ova_options_text = [];

					if ( array_key_exists( 'ova_options_key', $field ) ) {
						$ova_options_key = $field['ova_options_key'];
					}

					if ( array_key_exists( 'ova_options_text', $field ) ) {
						$ova_options_text = $field['ova_options_text'];
					}

					?>
					<select 
						id="<?php echo esc_attr( $key ); ?>" 
						name="<?php echo esc_attr( $key ); ?>" 
						class="ova_select <?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . $class_required ); ?>">
						<?php 
						if ( ! empty( $ova_options_text ) && is_array( $ova_options_text ) ):
							foreach( $ova_options_text as $key_2 => $value ):
								$selected = '';
								if ( $ova_options_key[$key_2] == $data_checkout_field[$key] ) {
									$selected = 'selected';
								}
						?>
								<option value="<?php echo esc_attr( $ova_options_key[$key_2] ); ?>"<?php selected( $ova_options_key[$key_2], $data_checkout_field[$key] ); ?>>
									<?php echo esc_html( $value ); ?>
								</option>
						<?php endforeach; endif; ?>
					</select>
				<?php endif; ?>

				<?php if ( $field['type'] === 'radio' ) {
					$radio_key = $radio_text = [];

					if ( array_key_exists( 'ova_radio_key', $field ) ) {
						$radio_key = $field['ova_radio_key'];
					}

					if ( array_key_exists( 'ova_radio_text', $field ) ) {
						$radio_text = $field['ova_radio_text'];
					}

					if ( ! empty( $radio_key ) && is_array( $radio_key ) ) {
						$default = $data_checkout_field[$key];
					?>
						<div class="el-radio">
					<?php
						foreach ( $radio_key as $k => $val ) {
							$checked = '';
							if ( $default === $val ) $checked = 'checked';
						?>
							<div class="el-ckf-radio <?php echo esc_attr( $class_required ); ?>">
								<input 
									type="radio" 
									id="<?php echo 'el-ckf-radio'.esc_attr( $k ); ?>" 
									name="<?php echo esc_attr( $key ); ?>"
									value="<?php echo esc_attr( $val ); ?>" 
									<?php echo esc_html( $checked ); ?>
								/>
								<label for="<?php echo 'el-ckf-radio'.esc_attr( $k ); ?>">
									<?php echo isset( $radio_text[$k] ) ? esc_html( $radio_text[$k] ) : ''; ?>
								</label>
							</div>
						<?php } ?>
						</div>
				<?php } } ?>

				<?php if ( $field['type'] === 'checkbox' ) {
					$checkbox_key = $checkbox_text = [];

					if ( array_key_exists( 'ova_checkbox_key', $field ) ) {
						$checkbox_key = $field['ova_checkbox_key'];
					}

					if ( array_key_exists( 'ova_checkbox_text', $field ) ) {
						$checkbox_text = $field['ova_checkbox_text'];
					}

					if ( ! empty( $checkbox_key ) && is_array( $checkbox_key ) ) {
						$default = [];

						if ( $data_checkout_field[$key] ) {
							$default = explode( ', ', $data_checkout_field[$key] );
						}
						
					?>
						<div class="el-checkbox">
					<?php
						foreach ( $checkbox_key as $k => $val ) {
							$checked = '';

							if ( ! $default && $field['required'] === 'on' ) $default = $checkbox_key[0];

							if ( in_array( $val, $default ) ) $checked = 'checked';
						?>
							<div class="el-ckf-checkbox <?php echo esc_attr( $class_required ); ?>">
								<input 
									type="checkbox" 
									id="<?php echo 'el-ckf-checkbox'.esc_attr( $k ); ?>" 
									name="<?php echo esc_attr( $key.'['.$val.']' ); ?>"
									data-name="<?php echo esc_attr( $key ); ?>"
									value="<?php echo esc_attr( $val ); ?>"
									<?php echo esc_html( $checked ); ?>
								/>
								<label for="<?php echo 'el-ckf-checkbox'.esc_attr( $k ); ?>">
									<?php echo isset( $checkbox_text[$k] ) ? esc_html( $checkbox_text[$k] ) : ''; ?>
								</label>
							</div>
						<?php } ?>
						</div>
				<?php } } ?>

				<?php if ( $field['type'] === 'file' ) { ?>
					<span class="el-ckf-file">
						<a href="<?php echo esc_url( $data_checkout_field[$key] ); ?>" target="_blank">
							<?php echo esc_html( wp_basename( $data_checkout_field[$key] ) ); ?>
						</a>
					</span>
				<?php } ?>
			</label><br><br>
		</div>
	<?php endforeach; endif; ?>
	<input
		type="hidden"
		id="el_meta_ticket_list_key_checkout_field"
		value="<?php echo esc_attr( json_encode( $list_key_checkout_field ) ); ?>"
		data-type="<?php echo esc_attr( json_encode( $list_type_checkout_field ) ); ?>"
	/>
	<input
		type="hidden"
		id="bk_data_checkout_field"
		name="<?php echo esc_attr( $this->get_mb_name( 'data_checkout_field' ) ); ?>"
		value="<?php echo esc_attr( json_encode( $data_checkout_field ) ); ?>"
	/>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Seat",  "eventlist" ); ?>:</strong>
			<input 
				type="text" 
				value="<?php echo esc_attr( $this->get_mb_value( 'seat' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_mb_name( 'seat' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<?php if ( $this->get_mb_value( 'person_type' ) ): ?>
		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Person type",  "eventlist" ); ?>:</strong>
				<input 
					type="text" 
					value="<?php echo esc_attr( $this->get_mb_value( 'person_type' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_mb_name( 'person_type' ) ); ?>"
				/>
			</label>
			<br><br>
		</div>
	<?php endif; ?>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Venue",  "eventlist" ); ?>:</strong>
			<?php 
				$arr_venue 	= $this->get_mb_value( 'venue' ); 
				$venue 		= is_array( $arr_venue ) ? implode( ", ", $arr_venue ) : $arr_venue;
			?>
			<input
				type="text"
				value="<?php echo esc_attr( $venue ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'venue' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Address",  "eventlist" ); ?>:</strong>
			<input 
				type="text"
				value="<?php echo esc_attr( $this->get_mb_value( 'address' ) ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name( 'address' ) ); ?>"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Qr Code",  "eventlist" ); ?>:</strong>
			<?php echo esc_html( $this->get_mb_value( 'qr_code' ) ); ?>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Start Date",  "eventlist" ); ?>: </strong>
			<?php
			$start_date 	= $this->get_mb_value( 'date_start' ); 
			$date_format 	= get_option('date_format');	
			$time_format 	= get_option('time_format');

			if ( $start_date ) {
				echo esc_html( date_i18n( $date_format, $start_date ) . ' - ' . date_i18n( $time_format, $start_date ) );
			}

			?>
		</label>
		<span class="el-edit-ticket">
			<input 
				type="text"
				class="el-ticket-edit-date"
				name="el_ticket_edit_date_start"
				placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
				autocomplete="off"
				autocorrect="off"
				autocapitalize="none"
				data-date-format="<?php echo esc_attr( $format ); ?>"
				data-firstday="<?php echo esc_attr( $first_day ); ?>"
			>
			<input 
				type="text"
				class="el-ticket-edit-time"
				name="el_ticket_edit_time_start"
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
				autocomplete="off"
				autocorrect="off"
				autocapitalize="none"
				data-time="<?php echo esc_attr( $time ); ?>"
			>
			<span class="el-btn-edit"><?php esc_html_e( 'Edit', 'eventlist' ); ?></span>
		</span>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "End Date",  "eventlist" ); ?>: </strong>
			<?php 
			$end_date 		= $this->get_mb_value( 'date_end' );
			$date_format 	= get_option('date_format');
			$time_format 	= get_option('time_format');

			if ( $end_date ) {
				echo esc_html( date_i18n( $date_format, $end_date ) . ' - ' . date_i18n( $time_format, $end_date ) );
			}
			?>
		</label>
		<span class="el-edit-ticket">
			<input 
				type="text"
				class="el-ticket-edit-date"
				name="el_ticket_edit_date_end"
				placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
				autocomplete="off"
				autocorrect="off"
				autocapitalize="none"
				data-date-format="<?php echo esc_attr( $format ); ?>"
				data-firstday="<?php echo esc_attr( $first_day ); ?>"
			>
			<input 
				type="text"
				class="el-ticket-edit-time"
				name="el_ticket_edit_time_end"
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
				autocomplete="off"
				autocorrect="off"
				autocapitalize="none"
				data-time="<?php echo esc_attr( $time ); ?>"
			>
			<span class="el-btn-edit"><?php esc_html_e( 'Edit', 'eventlist' ); ?></span>
		</span>
		<br><br>
	</div>
</div>

<?php wp_nonce_field( 'ova_metaboxes', 'ova_metaboxes' ); ?>