<?php defined( 'ABSPATH' ) || exit;
$attributes 	= $args['attributes'];
$custom_fields 	= get_option( 'ova_register_form' );
?>

<?php if ( isset( $attributes['errors'] ) && count( $attributes['errors'] ) > 0 ) : ?>
	<?php foreach ( $attributes['errors'] as $error ) : ?>
		<p>
			<?php echo $error; ?>
		</p>
	<?php endforeach; ?>
<?php endif; ?>


<div id="register-form" class="widecolumn ova_register_user">

	<h3 class="title"><?php _e( 'Register User', 'ova-login' ); ?></h3>
	
	<form id="signupform" action="<?php echo wp_registration_url(); ?>" method="post" enctype="multipart/form-data">
		<div class="ova_field_wrap">
			<p class="form-row ova-email-icon">
				<input placeholder="<?php _e( 'Email *', 'ova-login' ); ?>" type="text" name="email" id="email" class="default_field required" data-msg="<?php esc_attr_e( 'Please enter an email address.', 'ova-login' ); ?>" data-invalid="<?php esc_attr_e( 'Please enter a valid email address.', 'ova-login' ); ?>">
			</p>
			<span class="text-err"></span>
		</div>
		<?php if( OVALG_Settings::show_email_confirm() == 'yes' ){ ?>
		<div class="ova_field_wrap">
			<p class="form-row ova-email-icon">
				<input placeholder="<?php _e( 'Confirm Email *', 'ova-login' ); ?>" type="text" name="email_confirm" id="email_confirm" class="default_field required"
				data-msg="<?php esc_attr_e( 'Please enter a valid confirm email address.', 'ova-login' ); ?>"
				data-invalid="<?php esc_attr_e( 'The Confirmation Email must match your Email Address.', 'ova-login' ); ?>"
				>
			</p>
			<span class="text-err"></span>
		</div>
		<?php } ?>
		<div class="ova_field_wrap">
			<p class="form-row ova-user-icon">
				<input placeholder="<?php _e( 'Username *', 'ova-login' ); ?>" type="text" name="username" id="username" class="default_field required"
				data-msg="<?php esc_attr_e( 'Please enter Username.', 'ova-login' ); ?>"	
				data-invalid="<?php esc_attr_e( 'The username is invalid.', 'ova-login' ); ?>"
				>
			</p>
			<span class="text-err"></span>
		</div>

		<?php if ( OVALG_Settings::show_first_name() == 'yes' ): ?>

			<div class="ova_field_wrap">
				<p class="form-row ova-user-icon">
					<input placeholder="<?php _e( 'First name', 'ova-login' ); ?>" type="text" name="first_name" id="first-name" class="default_field <?php echo apply_filters( 'ovalg_register_require_first_name', false ) == true ? 'required' : ''; ?>"
					data-msg="<?php esc_attr_e( 'Please enter First name.', 'ova-login' ); ?>"
					data-invalid="<?php esc_attr_e( 'The first name is invalid.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>

		<?php endif; ?>

		<?php if ( OVALG_Settings::show_last_name() == 'yes' ): ?>

			<div class="ova_field_wrap">
				<p class="form-row ova-user-icon">
					<input placeholder="<?php _e( 'Last name', 'ova-login' ); ?>" type="text" name="last_name" id="last-name" class="default_field <?php echo apply_filters( 'ovalg_register_require_last_name', false ) == true ? 'required' : ''; ?>"
					data-msg="<?php esc_attr_e( 'Please enter Last name.', 'ova-login' ); ?>"
					data-invalid="<?php esc_attr_e( 'The last name is invalid.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>

		<?php endif; ?>
		
		<?php if( OVALG_Settings::show_password() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row password">
					<input placeholder="<?php _e( 'Password *', 'ova-login' ); ?>" type="password" name="password" id="password" autocomplete="new-password" class="default_field required"
					data-msg="<?php esc_attr_e( 'Please enter Password.', 'ova-login' ); ?>"
					data-error="<?php esc_attr_e( 'Password is greater than 8 characters and must include at least one number and must include at least one letter.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>

			<div class="ova_field_wrap">
				<p class="form-row password">
					<input placeholder="<?php _e( 'Confirm Password *', 'ova-login' ); ?>" type="password" name="password_confirm" id="password_confirm" autocomplete="new-password" class="default_field required"
					data-msg="<?php esc_attr_e( 'Please enter Confirm Password.', 'ova-login' ); ?>"
					data-error="<?php esc_attr_e( 'Password is greater than 8 characters and must include at least one number and must include at least one letter.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>

		<?php } ?>

		<?php if( OVALG_Settings::show_phone() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row ova-phone-icon">
					<input 
						placeholder="<?php _e( 'Phone', 'ova-login' ); ?>" 
						type="text" name="user_phone" 
						id="user_phone" 
						class="default_field <?php echo apply_filters( 'ovalg_register_require_phone', false ) == true ? 'required' : ''; ?>"
						data-msg="<?php esc_attr_e( 'Please enter Phone.', 'ova-login' ); ?>"
						data-invalid="<?php esc_attr_e( 'Please enter a valid phone number.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php if( OVALG_Settings::show_website() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row ova-url-icon">
					<input 
						placeholder="<?php _e( 'Website', 'ova-login' ); ?>" 
						type="text" name="user_url" 
						id="user_url" 
						class="default_field <?php echo apply_filters( 'ovalg_register_require_website', false ) == true ? 'required' : ''; ?>"
						data-msg="<?php esc_attr_e( 'Please enter website.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php if(  OVALG_Settings::show_job() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row ova-job-icon">
					<input 
						placeholder="<?php _e( 'Job', 'ova-login' ); ?>" 
						type="text" name="user_job" 
						id="user_job" 
						class="default_field <?php echo apply_filters( 'ovalg_register_require_job', false ) == true ? 'required' : ''; ?>"
						data-msg="<?php esc_attr_e( 'Please enter Job.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php if( OVALG_Settings::show_address() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row ova-address-icon">
					<input
						autocomplete="on"
						placeholder="<?php _e( 'Address', 'ova-login' ); ?>" 
						type="text" name="user_address" 
						id="user_address" 
						class="default_field <?php echo apply_filters( 'ovalg_register_require_address', false ) == true ? 'required' : ''; ?>"
						data-msg="<?php esc_attr_e( 'Please enter Address.', 'ova-login' ); ?>"
					>
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php if( OVALG_Settings::show_description() == 'yes' ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row ova-desc-icon">
					<textarea name="user_description" id="user_description" 
						class="default_field <?php echo apply_filters( 'ovalg_register_require_description', false ) == true ? 'required' : ''; ?>"
						data-msg="<?php esc_attr_e( 'Please enter Description.', 'ova-login' ); ?>" 
						placeholder="<?php esc_attr_e( 'Description', 'ova-login' ); ?>" 
						></textarea>
					
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php if ( $custom_fields ): ?>

			<?php foreach ( $custom_fields as $name => $field ):

				if ( $field['enabled'] == "on" ) {

					$class_user_for = "";

					switch ( $field['used_for'] ) {
						case 'vendor':
							$class_user_for = "used_for_vendor";
							break;

						case 'user':
							$class_user_for = "used_for_user";
							break;
						
						default:
							break;
					}
					$name = 'ova_'.$name;
					$required = $field['required'] == "on" ? "required" : "";
					?>
					<div class="ova_field_wrap ova_custom_register_field <?php echo esc_attr( $class_user_for ." ". $field['class'] ); ?>" data-required="<?php echo esc_attr( $required ); ?>">
					<?php
					switch ( $field['type'] ) {

						case 'text':
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row">
								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

								<input
									autocomplete="on"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
									type="text" name="<?php echo esc_attr( $name ); ?>" 
									id="<?php echo esc_attr( $name ); ?>" 
									class="ova_custom_field <?php echo esc_attr( $required ); ?>"
									data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
								>
							</p>
							<span class="text-err"></span>
							<?php
							break;

						case 'password':
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row">

								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

								<input
									autocomplete="off"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
									type="password" name="<?php echo esc_attr( $name ); ?>" 
									id="<?php echo esc_attr( $name ); ?>" 
									class="ova_custom_field <?php echo esc_attr( $required ); ?>"
									data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
								>
							</p>
							<span class="text-err"></span>
							<?php
							break;

						case 'email':
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row">

								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

								<input
									data-type="email"
									autocomplete="on"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
									type="email" name="<?php echo esc_attr( $name ); ?>" 
									id="<?php echo esc_attr( $name ); ?>" 
									class="ova_custom_field <?php echo esc_attr( $required ); ?>"
									data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
									data-invalid="<?php esc_attr_e( 'The field is invalid.', 'ova-login' ); ?>"
								>
							</p>
							<span class="text-err"></span>
							<?php
							break;

						case 'tel':
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row">

								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

								<input 
									data-type="tel"
									autocomplete="on"
									placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
									type="text" name="<?php echo esc_attr( $name ); ?>" 
									id="<?php echo esc_attr( $name ); ?>" 
									class="ova_custom_field <?php echo esc_attr( $required ); ?>"
									data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
									data-invalid="<?php echo sprintf( esc_html__( 'Please enter a valid %s.', 'ova-login' ), $field['label'] ); ?>"
								>
							</p>
							<span class="text-err"></span>
							<?php
							break;

						case 'textarea':
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row textarea">

								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

								<textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" 
								class="ova_custom_field <?php echo esc_attr( $required ); ?>"
								data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>" 
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
								></textarea>
							</p>
							<span class="text-err"></span>
							<?php
							break;

						case 'select':
							$ova_options_key 	= $field['ova_options_key'] 	? $field['ova_options_key'] : '';
							$ova_options_text 	= $field['ova_options_text'] 	? $field['ova_options_text'] : '';
							$placeholder 		= $field['placeholder'] ? $field['placeholder'] : $field['label'];
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row select">
								<?php if ( $field['class_icon'] ): ?>
									<i class="<?php echo esc_attr( $field['class_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>
								<select
								class="ova_custom_field <?php echo esc_attr( $required ); ?>"
								id="<?php echo esc_attr( $name ); ?>"
								name="<?php echo esc_attr( $name ); ?>"
								data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
								data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
								>
									<option value=""><?php echo esc_html( $placeholder ); ?></option>
									<?php if ( $ova_options_key ): ?>
										<?php foreach ( $ova_options_key as $key => $item ): ?>
											<option value="<?php echo esc_attr( $item ); ?>"><?php echo esc_html( $ova_options_text[$key] ); ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</p>
							<span class="text-err select"></span>
							<?php
							break;

						case 'radio':
							$ova_radio_key 	= $field['ova_radio_key'] ? $field['ova_radio_key'] : '';
							$ova_radio_text = $field['ova_radio_text'] ? $field['ova_radio_text'] : '';
							
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<p class="form-row">
								<?php if ( $ova_radio_key ): ?>
									<?php foreach ( $ova_radio_key as $key => $item ): ?>
										<?php $checked = $required == "required" ? $key : -1; ?>
										<span class="raido_input">
											<input type="radio" class="ova_custom_field" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $item ); ?>" <?php checked( 0, $checked ); ?> id="<?php echo esc_attr( $name .'_'.$item ) ?>" />
											<label for="<?php echo esc_attr( $name .'_'.$item ); ?>"><?php echo esc_html( $ova_radio_text[$key] ); ?></label>
										</span>

									<?php endforeach; ?>
								<?php endif; ?>
							</p>
							<?php

							break;

						case 'checkbox':
							$ova_checkbox_key 	= $field['ova_checkbox_key'] ? $field['ova_checkbox_key'] : '';
							$ova_checkbox_text 	= $field['ova_checkbox_text'] ? $field['ova_checkbox_text'] : '';
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<?php if ( $ova_checkbox_key ): ?>
								<div class="checkbox-field-group" data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>">
									<?php foreach ( $ova_checkbox_key as $key => $item ): ?>
										<p class="form-row checkbox-field">
								
											<input id="<?php echo esc_attr( $name .'_'.$item ); ?>" type="checkbox" name="<?php echo esc_attr( $name.'[]' ); ?>" value="<?php echo esc_attr( $item ); ?>" class="ova_custom_field <?php echo esc_attr( $required ); ?>" 
											/>
											<label for="<?php echo esc_attr( $name .'_'.$item ); ?>" class="checkbox-text">
												<?php echo esc_html( $ova_checkbox_text[$key] ); ?>
											</label>
										</p>
									<?php endforeach; ?>
									<span class="text-err"></span>
								</div>
							<?php endif; ?>
							<?php
							break;

						case 'file':
							$class_icon = $field['class_icon'] ? $field['class_icon'] : 'meupicon-download';
							?>
							<?php if ( isset( $field['label'] ) && $field['label'] ): ?>
								<label class="label"><?php echo esc_html( $field['label'] ); ?></label>
							<?php endif; ?>
							<label class="uploadfile-field" for="<?php echo esc_attr( $name ); ?>">
								<div class="box__input">
									<div class="box__icon">
										<i class="<?php echo esc_attr( $class_icon ); ?>" aria-hidden="true"></i>
									</div>
									<input type="file" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="ova_custom_field box__file <?php echo esc_attr( $required ); ?>" accept="image/png, image/jpeg, image/jpg, .pdf, .doc" data-maxsize="<?php echo esc_attr( $field['max_file_size'] ); ?>"
									data-msgsize="<?php esc_attr_e( 'File is too large!', 'ova-login' ); ?>"
									data-msgtype="<?php esc_attr_e( 'Invalid file format!', 'ova-login' ); ?>"
									data-tryagain="<?php esc_attr_e( 'Upload file again!', 'ova-login' ); ?>"
									data-msg="<?php esc_attr_e( 'The field is required.', 'ova-login' ); ?>"
									/>
									<label><strong><?php echo esc_html( $field['placeholder'] ); ?></strong><span class="box__dragndrop"><?php echo esc_html_e( ' or drag it here', 'ova-login' ); ?></span>.</label>
									<p class="file__format"><?php esc_html_e( 'Formats: .jpg, .jpeg, .png, .pdf, .doc', 'ova-login' ); ?></p>
								</div>
								<div class="text-err box__error"></div>
							</label>
							<?php
							break;
						
						default:
							break;
					} ?>
					<?php if ( isset( $field['description'] ) && $field['description'] ): ?>
						<span class="description"><?php echo esc_html( $field['description'] ); ?></span>
					<?php endif; ?>
					</div>
			<?php } endforeach; ?>
		<?php endif; ?>
			
		<div class="ova_field_wrap">
			<p class="form-row">

				<?php 
					$vendor_checked = apply_filters( 'meup_register_vendor_account_checked', false ) ? 'checked' : '' ; 
					$vendor_user = apply_filters( 'meup_register_user_account_checked', true ) ? 'checked' : '' ; 
				?>
				
				<?php if( apply_filters( 'meup_register_vendor_account', true ) ){ ?>
					<span class="raido_input">
						<input type="radio" name="type_user" value="vendor" id="vendor" <?php echo $vendor_checked; ?>>
						<label for="vendor"><?php _e( 'Vendor', 'ova-login' ); ?></label>
					</span>
				<?php } ?>

				<?php if( apply_filters( 'meup_register_user_account', true ) ){ ?>


					<span class="raido_input">
						<input type="radio" name="type_user" value="user" <?php echo $vendor_user; ?> id="user">
						<label for="user"><?php _e( 'User', 'ova-login' ); ?></label>
					</span>
				<?php } ?>

			</p>
		</div>

		<?php if( apply_filters( 'el_show_register_account_terms', true ) ){ ?>
			<div class="ova_field_wrap">
				<p class="form-row el-register-dcma">
					
					<input id="el_dcma" type="checkbox" name="el_dcma" value="dcma" class="register_term <?php echo apply_filters( 'el_register_account_require_terms', 'required' ); ?> " 
					data-msg="<?php esc_attr_e( 'Please check terms and conditions.', 'ova-login' ); ?>"
					/>

					<label class="terms-and-conditions-checkbox-text" for="el_dcma">

						<?php esc_html_e( 'I have read and agree to the website.', 'ova-login' ); ?>

						<?php if( $term_page = ovalg_term_condition_url() ){ ?>

							<a href="<?php echo $term_page; ?>" class="terms-and-conditions-link" target="<?php echo apply_filters( 'el_reg_terms_target', '_blank' ); ?>">
								<?php esc_attr_e( 'terms and conditions', 'ova-login' ); ?>
							</a>

						<?php } ?>
						
					</label>
				</p>
				<span class="text-err"></span>
			</div>
		<?php } ?>

		<?php echo apply_filters( 'meup_register_recapcha', '' ); ?>

		<p class="signup-submit">
			<input type="submit" name="submit" class="ova-btn ova-btn-main-color"
			value="<?php _e( 'Register', 'ova-login' ); ?>"/>
		</p>

		<br>
		<?php 
			if( class_exists('NextendSocialLogin') ){
				echo do_shortcode('[nextend_social_login]');
			}
		?>

	</form>
</div>
