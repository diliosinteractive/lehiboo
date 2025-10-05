<?php if ( ! defined( 'ABSPATH' ) ) exit();
	$index = isset( $args['index'] ) && $args['index'] ? $args['index'] : '';
	$seat_option = isset( $args['seat_option'] ) && $args['seat_option'] ? $args['seat_option'] : 'none';
	$first_name_class = 'first_name';

	if ( 'none' != $seat_option ) {
		$first_name_class = '';
	}
?>

<div class="error-empty-input error-first_name">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<li class="<?php echo esc_attr( $first_name_class ); ?>">
	<div class="label">
		<label for="first_name">
			<?php esc_html_e( 'First Name','eventlist' ); ?>
		</label>
	</div>
	<div class="span first_name">
		<input 
			id="first_name" 
			type="text" 
			name="ticket_receiver_first_name" />
	</div>
</li>
<div class="error-empty-input error-last_name">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<?php if( apply_filters( 'el_show_last_name_checkout_form', true ) ): ?>
	<li class="last_name">
		<div class="label">
			<label for="last_name">
				<?php esc_html_e( 'Last Name','eventlist' ); ?>
			</label>
		</div>
		<div class="span last_name">
			<input 
				id="last_name" 
				type="text" 
				name="ticket_receiver_last_name" />
		</div>
	</li>
<?php endif; ?>
<div class="error-empty-input error-email">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<div class="error-empty-input error-invalid-email">
	<span>
		<?php esc_html_e("field is invalid", "eventlist"); ?>
	</span>
</div>
<li>
	<div class="label">
		<label for="email">
			<?php esc_html_e( 'Email','eventlist' ); ?>
		</label>
	</div>
	<div class="span email">
		<input 
			id="email" 
			type="email" 
			name="ticket_receiver_email" />
	</div>
</li>
<div class="error-empty-input error-email-confirm-require">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<div class="error-empty-input error-email-confirm-not-match">
	<span>
		<?php esc_html_e( "The email doesn't match", "eventlist" ); ?>
	</span>
</div>
<li>
	<div class="label">
		<label for="email">
			<?php esc_html_e( 'Confirm Email','eventlist' ); ?>
		</label>
	</div>
	<div class="span email">
		<input 
			id="email_confirm" 
			type="email" 
			name="ticket_receiver_email_confirm" />
	</div>
</li>
<div class="error-empty-input error-phone">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<?php if ( apply_filters( 'el_checkout_show_phone', true ) ): ?>
	<li>
		<div class="label">
			<label for="phone">
				<?php esc_html_e( 'Phone','eventlist' ); ?>
			</label>
		</div>
		<div class="span phone">
			<input 
				id="phone" 
				type="text" 
				name="ticket_receiver_phone" />
		</div>
	</li>
<?php endif; ?>
<div class="error-empty-input error-address">
	<span>
		<?php esc_html_e( "field is required", "eventlist" ); ?>
	</span>
</div>
<li>
	<div class="label">
		<label for="address">
			<?php esc_html_e( 'Address','eventlist' ); ?>
		</label>
	</div>
	<div class="span address">
		<input 
			id="address" 
			type="text" 
			name="ticket_receiver_address" />
	</div>
</li>
<?php
	$event_id = isset( $args['event_id'] ) && $args['event_id'] ? $args['event_id'] : '';

	if ( ! $event_id ) $event_id = isset( $_GET['ide'] ) ? $_GET['ide'] : '';

	$list_ckf_output 	= get_option( 'ova_booking_form', array() );
	$terms 				= get_the_terms( $event_id, 'event_cat' );
	$term_id 			= 0;

	if ( $terms && $terms[0] ) {
		$term_id = $terms[0]->term_id;
	}

	$category_ckf_type = get_term_meta( $term_id, '_category_ckf_type', true ) ? get_term_meta( $term_id, '_category_ckf_type', true) : 'all';
	$category_checkout_field = get_term_meta( $term_id, '_category_checkout_field', true) ? get_term_meta( $term_id, '_category_checkout_field', true) : array();

	$flag = 0;
	foreach( $list_ckf_output as $key => $field ) {
		if ( array_key_exists('enabled', $field) &&  $field['enabled'] == 'on' ) {
			$flag++;
		}
	}

	$i = 0;

	if ( is_array( $list_ckf_output ) && ! empty( $list_ckf_output ) ) {
		$special_fields = [ 'textarea', 'select', 'radio', 'checkbox', 'file' ];

		foreach( $list_ckf_output as $key => $field ) {
			$i++;

			if ( $category_ckf_type === 'special' && ! in_array( $key, $category_checkout_field ) ) continue;

			if ( array_key_exists('enabled', $field) &&  $field['enabled'] == 'on' ) {

				if ( array_key_exists('required', $field) &&  $field['required'] == 'on' ) {
					$class_required = 'required';
				} else {
					$class_required = '';
				}

				$class_last = ( $i == $flag ) ? 'ova-last' : '';
		?>
			<div class="error-empty-input error-<?php echo esc_attr( $key ); ?>">
				<span>
					<?php esc_html_e( "field is required", "eventlist" ); ?>
				</span>
			</div>
			<li class="rental_item <?php echo esc_attr( $class_last ) ?>">
				<div class="label">
					<label for="<?php echo esc_attr( $key ).'_index'.esc_attr($index); ?>">
						<?php echo esc_html( $field['label'] ); ?>
					</label>
				</div>
				<?php if ( ! in_array( $field['type'] , $special_fields ) ): ?>
					<input 
						id="<?php echo esc_attr( $key ).'_index'.esc_attr($index); ?>" 
						type="<?php echo esc_attr( $field['type'] ); ?>" 
						name="<?php echo esc_attr( $key ) ?>" 
						class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>" 
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
						value="<?php echo esc_attr( $field['default'] ); ?>" />
				<?php endif; ?>
				<?php if ( $field['type'] === 'textarea' ): ?>
					<textarea id="<?php echo esc_attr( $key ).'_index'.esc_attr( $index ); ?>" name="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" cols="10" rows="5"><?php echo esc_html( $field['default'] ); ?></textarea>
				<?php endif; ?>
				<?php if ( $field['type'] === 'select' ) { 
					$ova_options_key = $ova_options_text = [];

					if ( array_key_exists( 'ova_options_key', $field ) ) {
						$ova_options_key = $field['ova_options_key'];
					}

					if ( array_key_exists( 'ova_options_text', $field ) ) {
						$ova_options_text = $field['ova_options_text'];
					}
					?>
					<select 
						id="<?php echo esc_attr( $key ).'_index'.esc_attr($index); ?>" 
						name="<?php echo esc_attr( $key ).'_index'.esc_attr($index); ?>"
						data-global-name="<?php echo esc_attr( $key ); ?>"
						data-placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						class="ova_select <?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>">
						<?php 
						if ( ! empty( $ova_options_text ) && is_array( $ova_options_text ) ) { 
							foreach( $ova_options_text as $key => $value ) { 
								$selected = '';
								if( $ova_options_key[$key] == $field['default'] ) {
									$selected = 'selected';
								}
								?>
								<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $ova_options_key[$key] ) ?>">
									<?php echo esc_html( $value ); ?>
								</option>
						<?php 
							} //end foreach
						}//end if
					?>
					</select>
				<?php } ?>

				<?php if ( $field['type'] === 'radio' ) {
					$radio_key 	= $radio_text = [];
					$global_key = $key;
					$key .= '_index'.esc_attr($index);

					if ( array_key_exists( 'ova_radio_key', $field ) ) {
						$radio_key = $field['ova_radio_key'];
					}

					if ( array_key_exists( 'ova_radio_text', $field ) ) {
						$radio_text = $field['ova_radio_text'];
					}

					if ( ! empty( $radio_key ) && is_array( $radio_key ) ) {
						$default = isset( $field['default'] ) ? $field['default'] : '';

						foreach ( $radio_key as $k => $val ) {
							$checked = '';

							if ( ! $default && $field['required'] === 'on' ) $default = $radio_key[0];

							if ( $default === $val ) $checked = 'checked';
						?>
							<div class="el-ckf-radio <?php echo esc_attr( $class_required ); ?>">
								<input 
									type="radio" 
									id="<?php echo 'el-ckf-radio'.esc_attr( $k ).'_index'.esc_attr($index); ?>" 
									name="<?php echo esc_attr( $key ); ?>"
									data-global-name="<?php echo esc_attr( $global_key ); ?>"
									value="<?php echo esc_attr( $val ); ?>" 
									<?php echo esc_html( $checked ); ?>
								/>
								<label for="<?php echo 'el-ckf-radio'.esc_attr( $k ).'_index'.esc_attr($index); ?>">
									<?php echo isset( $radio_text[$k] ) ? esc_html( $radio_text[$k] ) : ''; ?>
								</label>
							</div>
						<?php }
					}
				} ?>

				<?php if ( $field['type'] === 'checkbox' ) {
					$checkbox_key 	= $checkbox_text = [];
					$global_key 	= $key;
					$key .= '_index'.esc_attr($index);

					if ( array_key_exists( 'ova_checkbox_key', $field ) ) {
						$checkbox_key = $field['ova_checkbox_key'];
					}

					if ( array_key_exists( 'ova_checkbox_text', $field ) ) {
						$checkbox_text = $field['ova_checkbox_text'];
					}

					if ( ! empty( $checkbox_key ) && is_array( $checkbox_key ) ) {
						$default = isset( $field['default'] ) ? $field['default'] : '';

						foreach ( $checkbox_key as $k => $val ) {
							$checked = '';

							if ( ! $default && $field['required'] === 'on' ) $default = $checkbox_key[0];

							if ( $default === $val ) $checked = 'checked';
						?>
							<div class="el-ckf-checkbox <?php echo esc_attr( $class_required ); ?>">
								<input 
									type="checkbox" 
									id="<?php echo esc_attr('el-ckf-checkbox'. $k.'_index'.$index ); ?>" 
									name="<?php echo esc_attr( $key ); ?>"
									data-name="<?php echo esc_attr( $key ); ?>"
									data-global-name="<?php echo esc_attr( $global_key ); ?>"
									value="<?php echo esc_attr( $val ); ?>" 
									<?php echo esc_html( $checked ); ?>
								/>
								<label for="<?php echo 'el-ckf-checkbox'.esc_attr( $k ).'_index'.esc_attr($index); ?>">
									<?php echo isset( $checkbox_text[$k] ) ? esc_html( $checkbox_text[$k] ) : ''; ?>
								</label>
							</div>
						<?php }
					}
				} ?>

				<?php if ( $field['type'] === 'file' ) {
					$global_key = $key;
					$key .= '_index'.esc_attr($index);

					$mimes = apply_filters( 'el_ckf_ft_file_mimes', [
	                    'jpg'   => 'image/jpeg',
	                    'jpeg'  => 'image/pjpeg',
	                    'png'   => 'image/png',
	                    'pdf'   => 'application/pdf',
	                    'doc'   => 'application/msword',
	                ]);
				?>
					<div class="el-ckf-file">
						<label for="<?php echo 'el-ckf-file-'.esc_attr( $key ); ?>">
							<span class="el-ckf-file-choose">
								<?php esc_html_e( 'Choose File', 'eventlist' ); ?>
							</span>
							<span class="el-ckf-file-name"></span>
						</label>
						<input 
							type="<?php echo esc_attr( $field['type'] ); ?>" 
							id="<?php echo esc_attr( 'el-ckf-file-'. $key ); ?>" 
							name="<?php echo esc_attr( $key ); ?>"
							data-global-name="<?php echo esc_attr( $global_key ); ?>"
							class="<?php echo esc_attr( $field['class'] . $class_required ); ?>" 
							data-max-file-size="<?php echo esc_attr( $field['max_file_size'] ); ?>" 
							data-file-mimes="<?php echo esc_attr( json_encode( $mimes ) ); ?>"
							data-required="<?php esc_attr_e( 'field is required', 'eventlist' ); ?>"
							data-max-file-size-msg="<?php echo sprintf( esc_attr__( 'Maximum file size: %sMB', 'eventlist' ), esc_attr( $field['max_file_size'] ) ); ?>" 
							data-formats="<?php esc_attr_e( 'Formats: .jpg, .jpeg, .png, .pdf, .doc', 'eventlist' ); ?>"
						/>
					</div>
				<?php } ?>
			</li>
		<?php
			}//endif
		}//end foreach
	}//end if
?>