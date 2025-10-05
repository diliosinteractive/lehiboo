<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="cart-customer-input">
	<h3 class="cart_title">
		<?php esc_html_e( 'Ticket Receiver', 'eventlist' ); ?>
	</h3>
	<?php if ( apply_filters( 'el_show_multiple_ticket_checkout_form', true ) ): ?>
		<div class="el_ask_ticket">
			<h5 class="ask_ticket_title">
				<?php esc_html_e( 'Do you want to insert multiple customer information?', 'eventlist' ); ?>
			</h5>
			<div class="ask_ticket_checked">
				<div class="label-checked">
					<input 
						id="ask_ticket_no"
						class="radio_ask_ticket"
						type="radio"
						checked="checked"
						name="radio_ask"
						value="no"
					/>
					<label for="ask_ticket_no">
						<?php esc_html_e( 'No', 'eventlist' ); ?>
					</label>
  					<span class="checkmark"></span>
				</div>
				<div class="label-checked">
					<input 
						id="ask_ticket_yes"
						class="radio_ask_ticket"
						type="radio"
						name="radio_ask"
						value="yes"
					/>
					<label for="ask_ticket_yes">
						<?php esc_html_e( 'Yes', 'eventlist' ); ?>
					</label>
	  				<span class="checkmark"></span>
				</div>
				<div class="label-checked ask_loading">
					<i aria-hidden="true" class="icon_loading"></i>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php 
	$first_name = $last_name = $email = $user_phone = $user_address = '';

	if ( is_user_logged_in() ) {
		$user_id 		= wp_get_current_user()->ID;
		$first_name 	= get_user_meta( $user_id, 'first_name', true ) ? get_user_meta( $user_id, 'first_name', true ) : get_the_author_meta('first_name', $user_id);
		$last_name 		= get_user_meta( $user_id, 'last_name', true ) ? get_user_meta( $user_id, 'last_name', true ) : get_the_author_meta('last_name', $user_id);
		$email 			= wp_get_current_user()->user_email;
		$user_phone 	= get_user_meta( $user_id, 'user_phone', true ) ? get_user_meta( $user_id, 'user_phone', true ) : '';
		$user_address 	= get_user_meta( $user_id, 'user_address', true ) ? get_user_meta( $user_id, 'user_address', true ) : '';
	}

	?>
	<ul class="input_ticket_receiver">

		<!-- First Name -->
		<div class="error-empty-input error-first_name">
			<span><?php esc_html_e("field is required", "eventlist"); ?></span>
		</div>

		<li class="first_name">
			<div class="label">
				<label for="first_name">
					<?php esc_html_e( 'First Name','eventlist' ); ?>
				</label>
			</div>
			<div class="span first_name">
				<input
					id="first_name"
					type="text"
					name="ticket_receiver_first_name"
					value="<?php echo esc_attr( $first_name ); ?>"
				/>
			</div>
		</li>

		<!-- Last Name -->
		<?php if ( apply_filters( 'el_show_last_name_checkout_form', true ) ) { ?>
			<div class="error-empty-input error-last_name">
				<span><?php esc_html_e("field is required", "eventlist"); ?></span>
			</div>
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
						name="ticket_receiver_last_name"
						value="<?php echo esc_attr( $last_name ); ?>"
					/>
				</div>
			</li>
		<?php } ?>


		<!-- Email -->
		<div class="error-empty-input error-email">
			<span>
				<?php esc_html_e("field is required", "eventlist"); ?>
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
					name="ticket_receiver_email"
					value="<?php echo esc_attr( $email ); ?>"
				/>
			</div>
		</li>

		
		<!-- Email Confirm -->
		<?php if ( apply_filters( 'el_checkout_show_email_confirm', true ) ): ?>
			<div class="error-empty-input error-email-confirm-require">
				<span>
					<?php esc_html_e("field is required", "eventlist"); ?>
				</span>
			</div>
			<div class="error-empty-input error-email-confirm-not-match">
				<span>
					<?php esc_html_e("The email doesn't match", "eventlist"); ?>
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
						name="ticket_receiver_email_confirm"
						value="<?php echo esc_attr( $email ); ?>"
					/>
				</div>
			</li>
		<?php endif; ?>


		<!-- Phone -->
		<?php if ( apply_filters( 'el_checkout_show_phone', true ) ): ?>
			<div class="error-empty-input error-phone">
				<span>
					<?php esc_html_e("field is required", "eventlist"); ?>
				</span>
			</div>
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
						name="ticket_receiver_phone"
						value="<?php echo esc_attr( $user_phone ); ?>"
					/>
				</div>
			</li>
		<?php endif; ?>


		<!-- Address -->
		<?php if ( apply_filters( 'el_checkout_show_address', true ) ): ?>
			<div class="error-empty-input error-address">
				<span>
					<?php esc_html_e("field is required ", "eventlist"); ?>
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
						name="ticket_receiver_address"
						value="<?php echo esc_attr( $user_address ); ?>"
					/>
				</div>
			</li>
		<?php endif; ?>


		<?php
			$id_event = isset( $_GET['ide'] ) ? $_GET['ide'] : '';
			$list_ckf_output = get_option( 'ova_booking_form', array() );

			$terms 				= get_the_terms( $id_event, 'event_cat' );
			$term_id 			= 0;
			if ( $terms && $terms[0] ) {
				$term_id = $terms[0]->term_id;
			}

			$category_ckf_type = get_term_meta( $term_id, '_category_ckf_type', true ) ? get_term_meta( $term_id, '_category_ckf_type', true) : 'all';
			$category_checkout_field = get_term_meta( $term_id, '_category_checkout_field', true) ? get_term_meta( $term_id, '_category_checkout_field', true) : array();

			$flag = 0;

			foreach ( $list_ckf_output as $key => $field ) {
				if ( array_key_exists( 'enabled', $field ) && $field['enabled'] == 'on' ) {
					$flag++;
				}
			}

			$list_key_checkout_field 	= [];
			$list_type_checkout_field 	= [];
			$i = 0;

			if ( is_array( $list_ckf_output ) && ! empty( $list_ckf_output ) ) {
				$special_fields = [ 'textarea', 'select', 'radio', 'checkbox', 'file' ];

				foreach ( $list_ckf_output as $key => $field ) {
					$i++;

					if ( $category_ckf_type === 'special' && ! in_array( $key, $category_checkout_field ) ) continue;

					if ( array_key_exists('enabled', $field) &&  $field['enabled'] == 'on' ) {
						$list_key_checkout_field[] 		= $key;
						$list_type_checkout_field[$key] = $field['type'];

						if ( array_key_exists('required', $field) && $field['required'] == 'on' ) {
							$class_required = 'required';
						} else {
							$class_required = '';
						}

						$class_last = ( $i == $flag ) ? 'ova-last' : '';
				?>
					<div class="error-empty-input error-<?php echo esc_attr( $key ); ?>">
						<span>
							<?php esc_html_e("field is required", "eventlist"); ?>
						</span>
					</div>
					<li class="rental_item <?php echo esc_attr( $class_last ); ?>">
						<div class="label">
							<label for="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
							</label>
						</div>
						<?php if ( ! in_array( $field['type'] , $special_fields ) ) { ?>
							<input
								id="<?php echo esc_attr( $key ); ?>"
								type="<?php echo esc_attr( $field['type'] ); ?>"
								name="<?php echo esc_attr( $key ); ?>"
								class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>"
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								value="<?php echo esc_attr( $field['default'] ); ?>"
							/>
						<?php } ?>

						<?php if ( $field['type'] === 'textarea' ) { ?>
							<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" class=" <?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" cols="10" rows="5"><?php echo esc_html( $field['default'] ); ?></textarea>
						<?php } ?>

						<?php if ( $field['type'] === 'select' ) { 
							$ova_options_key = $ova_options_text = [];

							if ( array_key_exists( 'ova_options_key', $field ) ) {
								$ova_options_key = $field['ova_options_key'];
							}

							if ( array_key_exists( 'ova_options_text', $field ) ) {
								$ova_options_text = $field['ova_options_text'];
							}

							?>
							<select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" data-global-name="<?php echo esc_attr( $key ); ?>" class=" ova_select <?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>" data-placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>">
								<?php 
								if ( ! empty( $ova_options_text ) && is_array( $ova_options_text ) ) { 
									foreach ( $ova_options_text as $k => $value ) { 	
								?>
										<option value="<?php echo esc_attr( $ova_options_key[$k] ); ?>"<?php selected( $ova_options_key[$k], $field['default'] ); ?>>
											<?php echo esc_html( $value ); ?>
										</option>
								<?php 

									} //end foreach
								}//end if
							?>
							</select>
						<?php } ?>

						<?php if ( $field['type'] === 'radio' ) {
							$radio_key = $radio_text = [];

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
											id="<?php echo esc_attr( 'el-ckf-radio'.$k ); ?>" 
											name="<?php echo esc_attr( $key ); ?>"
											data-global-name="<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr( $val ); ?>" 
											<?php echo esc_html( $checked ); ?>
										/>
										<label for="<?php echo esc_attr( 'el-ckf-radio'.$k ); ?>">
											<?php echo isset( $radio_text[$k] ) ? esc_html( $radio_text[$k] ) : ''; ?>
										</label>
									</div>
								<?php }
							}
						} ?>

						<?php if ( $field['type'] === 'checkbox' ) {
							$checkbox_key = $checkbox_text = [];

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
											id="<?php echo esc_attr( 'el-ckf-checkbox'.$k ); ?>" 
											name="<?php echo esc_attr( $key.'['.$val.']' ); ?>" 
											value="<?php echo esc_attr( $val ); ?>"
											data-name="<?php echo esc_attr( $key ); ?>"
											data-global-name="<?php echo esc_attr( $key ); ?>"
											<?php echo esc_html( $checked ); ?>
										/>
										<label for="<?php echo esc_attr( 'el-ckf-checkbox'.$k ); ?>">
											<?php echo isset( $checkbox_text[$k] ) ? esc_html( $checkbox_text[$k] ) : ''; ?>
										</label>
									</div>
								<?php }
							}
						} ?>

						<?php if ( $field['type'] === 'file' ) {
							$mimes = apply_filters( 'el_ckf_ft_file_mimes', [
			                    'jpg'   => 'image/jpeg',
			                    'jpeg'  => 'image/pjpeg',
			                    'png'   => 'image/png',
			                    'pdf'   => 'application/pdf',
			                    'doc'   => 'application/msword',
			                ]);
						?>
							<div class="el-ckf-file">
								<label for="<?php echo esc_attr( 'el-ckf-file-'.$key ); ?>">
									<span class="el-ckf-file-choose">
										<?php esc_html_e( 'Choose File', 'eventlist' ); ?>
									</span>
									<span class="el-ckf-file-name"></span>
								</label>
								<input 
									type="<?php echo esc_attr( $field['type'] ); ?>" 
									id="<?php echo esc_attr( 'el-ckf-file-'.$key ); ?>" 
									name="<?php echo esc_attr( $key ); ?>" 
									class="<?php echo esc_attr( $field['class'] . $class_required ); ?>" 
									data-max-file-size="<?php echo esc_attr( $field['max_file_size'] ); ?>" 
									data-file-mimes="<?php echo esc_attr( json_encode( $mimes ) ); ?>"
									data-required="<?php esc_attr_e( 'field is required', 'eventlist' ); ?>"
									data-max-file-size-msg="<?php echo sprintf( esc_html__( 'Maximum file size: %sMB', 'eventlist' ), esc_attr( $field['max_file_size'] ) ); ?>" 
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
		<input 
			type="hidden"
			id="el_list_key_checkout_field"
			data-type="<?php echo esc_attr( json_encode( $list_type_checkout_field ) ); ?>"
			value="<?php echo esc_attr( json_encode( $list_key_checkout_field ) ); ?>" 
		/>
		<?php if( EL()->options->checkout->get( 'checkout_create_account', 1 ) && get_current_user_id() == null ){ ?>
			<li class="create_account">
				<div class="span create_account_content">
					<input
						type="checkbox"
						name="create-account"
						value="1" 
						<?php echo esc_attr( apply_filters( 'el_checkout_create_account_default', '' ) ); ?>
					/>
					<span class="label">
						<?php esc_html_e( 'Create an account to manage booking', 'eventlist' ); ?>
					</span>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>