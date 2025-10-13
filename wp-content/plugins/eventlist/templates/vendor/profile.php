<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$user_id = wp_get_current_user()->ID;

$OVALG_Settings = '';
if ( class_exists("OVALG_Settings") ) {
	$OVALG_Settings = new OVALG_Settings();
}

$admin_approve_vendor = OVALG_Settings::admin_approve_vendor();

$author_id_image = get_user_meta( $user_id, 'author_id_image', true ) ? get_user_meta( $user_id, 'author_id_image', true ) : '';
$display_name    = get_user_meta( $user_id, 'display_name', true ) ? get_user_meta( $user_id, 'display_name', true ) : get_the_author_meta('display_name', $user_id);

$first_name    = get_user_meta( $user_id, 'first_name', true ) ? get_user_meta( $user_id, 'first_name', true ) : get_the_author_meta('first_name', $user_id);
$last_name     = get_user_meta( $user_id, 'last_name', true ) ? get_user_meta( $user_id, 'last_name', true ) : get_the_author_meta('last_name', $user_id);

$user_job        = get_user_meta( $user_id, 'user_job', true ) ? get_user_meta( $user_id, 'user_job', true ) : '';
$user_phone      = get_user_meta( $user_id, 'user_phone', true ) ? get_user_meta( $user_id, 'user_phone', true ) : '';
$user_address    = get_user_meta( $user_id, 'user_address', true ) ? get_user_meta( $user_id, 'user_address', true ) : '';
$description     = get_user_meta( $user_id, 'description', true ) ? get_user_meta( $user_id, 'description', true ) : '';

$user_profile_social 	= get_user_meta( $user_id, 'user_profile_social', true ) ? get_user_meta( $user_id, 'user_profile_social', true ) : '';

$user_old_pass     		= get_user_meta( $user_id, 'user_old_pass', true ) ? get_user_meta( $user_id, 'user_old_pass', true ) : '';

$user_bank_owner 		= get_user_meta( $user_id, 'user_bank_owner', true ) ? get_user_meta( $user_id, 'user_bank_owner', true ) : '';
$user_bank_number 		= get_user_meta( $user_id, 'user_bank_number', true ) ? get_user_meta( $user_id, 'user_bank_number', true ) : '';
$user_bank_name 		= get_user_meta( $user_id, 'user_bank_name', true ) ? get_user_meta( $user_id, 'user_bank_name', true ) : '';
$user_bank_branch 		= get_user_meta( $user_id, 'user_bank_branch', true ) ? get_user_meta( $user_id, 'user_bank_branch', true ) : '';
$user_bank_routing 		= get_user_meta( $user_id, 'user_bank_routing', true ) ? get_user_meta( $user_id, 'user_bank_routing', true ) : '';
$user_bank_paypal_email = get_user_meta( $user_id, 'user_bank_paypal_email', true ) ? get_user_meta( $user_id, 'user_bank_paypal_email', true ) : '';
$user_bank_stripe_account 	= get_user_meta( $user_id, 'user_bank_stripe_account', true ) ? get_user_meta( $user_id, 'user_bank_stripe_account', true ) : '';
$user_bank_iban 			= get_user_meta( $user_id, 'user_bank_iban', true ) ? get_user_meta( $user_id, 'user_bank_iban', true ) : '';
$user_bank_swift_code 		= get_user_meta( $user_id, 'user_bank_swift_code', true ) ? get_user_meta( $user_id, 'user_bank_swift_code', true ) : '';
$user_bank_ifsc_code 		= get_user_meta( $user_id, 'user_bank_ifsc_code', true ) ? get_user_meta( $user_id, 'user_bank_ifsc_code', true ) : '';

$user_meta_field = get_option( 'ova_register_form' );
?>

<div class="vendor_wrap">
	<?php echo el_get_template( 'vendor/sidebar.php' ); ?>

	<div class="contents">

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_profile">

			<!-- Navigation verticale à gauche -->
			<div class="profile_navigation_sidebar">
				<div class="profile_user_header">
					<div class="profile_avatar">
						<?php
						$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ) ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : EL_PLUGIN_URI.'assets/img/unknow_user.png';
						?>
						<img src="<?php echo esc_url($img_path); ?>" alt="<?php echo esc_attr($display_name); ?>">
					</div>
					<div class="profile_user_info">
						<h3><?php echo esc_html( $display_name ); ?></h3>
					</div>
				</div>

				<nav class="profile_tabs_nav">
					<ul>
						<li class="profile_tab_item active" data-tab="author_profile">
							<a href="#author_profile">
								<i class="icon_profile"></i>
								<span><?php esc_html_e( 'Informations Personnelles', 'eventlist' ); ?></span>
							</a>
						</li>

						<?php if( el_is_vendor() ){ ?>
							<li class="profile_tab_item" data-tab="author_organisation">
								<a href="#author_organisation">
									<i class="icon_building"></i>
									<span><?php esc_html_e( 'Mon Organisation', 'eventlist' ); ?></span>
								</a>
							</li>
							<li class="profile_tab_item" data-tab="author_presentation">
								<a href="#author_presentation">
									<i class="icon_documents_alt"></i>
									<span><?php esc_html_e( 'Présentation', 'eventlist' ); ?></span>
								</a>
							</li>
							<li class="profile_tab_item" data-tab="author_localisation">
								<a href="#author_localisation">
									<i class="icon_pin_alt"></i>
									<span><?php esc_html_e( 'Localisation', 'eventlist' ); ?></span>
								</a>
							</li>
						<?php } ?>

						<li class="profile_tab_item" data-tab="author_password">
							<a href="#author_password">
								<i class="icon_lock_alt"></i>
								<span><?php esc_html_e( 'Mot de passe', 'eventlist' ); ?></span>
							</a>
						</li>

						<?php if( el_is_vendor() && apply_filters( 'el_profile_show_bank', true ) ){ ?>
							<li class="profile_tab_item" data-tab="author_bank">
								<a href="#author_bank">
									<i class="icon_creditcard"></i>
									<span><?php esc_html_e( 'Informations de Paiement', 'eventlist' ); ?></span>
								</a>
							</li>
						<?php } ?>

						<?php if( el_is_vendor() && EL()->options->checkout->get( 'split_payment_stripe_active', 'no' ) == 'yes' ){ ?>
							<li class="profile_tab_item" data-tab="strip_connect">
								<a href="#strip_connect">
									<i class="icon_creditcard"></i>
									<span><?php esc_html_e( 'Stripe Connect', 'eventlist' ); ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</nav>
			</div>

			<!-- Contenu des onglets -->
			<div class="profile_content_area">

				<?php if( el_is_vendor() && EL()->options->checkout->get( 'split_payment_stripe_active', 'no' ) == 'yes' ){ ?>
					<div id="strip_connect" class="tab-contents">
						<?php do_action( 'el_extra_profile' ); ?>
					</div>
				<?php } ?>

				<!-- Profile -->
				<div id="author_profile" class="tab-contents">
					

					<?php if( !el_is_vendor() && apply_filters( 'el_is_update_vendor_role', true ) ){ ?>
						<div class="author_role">
							<div id="author_role">
								<form id="el_save_role" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">
									
										<span class="loader">
											<img src="<?php echo esc_url( includes_url() . 'js/tinymce/skins/lightgray/img//loader.gif' ); ?>" />
										</span>
										<div class="ova_result_update_to_vendor"></div>
										<span><?php esc_html_e( 'Click here:', 'eventlist' ); ?></span>
										<input type="submit" name="el_update_role" data-role="vendor" data-approve="<?php echo esc_attr( $admin_approve_vendor ); ?>" value="<?php esc_html_e( 'upgrade to Vendor Role', 'eventlist' ); ?>" />
										<br>
										<?php esc_html_e( 'After update to Vendor, you have to register a package to submit event. ', 'eventlist' ); ?>
										<br>
										<?php esc_html_e( 'Note: You can\'t downgrade after update to vendor role.', 'eventlist' ); ?>
									
									
									<?php wp_nonce_field( 'el_update_role_nonce', 'el_update_role_nonce' ); ?>

								</form>
							</div>
						</div>
					<?php } ?>


					<form id="el_save_profile" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

						<!-- Image -->
						<?php if( ( isset( $_GET['vendor'] ) && $_GET['vendor'] != '' && is_user_logged_in() ) || ( is_user_logged_in() && EL()->options->role->get( 'user_upload_files', 1 ) ) ) { ?>
							<div class="author_image">

								<div class="wrap">
									<?php if ($author_id_image !== ''){ ?>
										<img class="image-preview" src="<?php echo esc_url(wp_get_attachment_image_url($author_id_image, 'el_thumbnail')); ?>" alt="<?php esc_html_e( 'author', 'eventlist' ); ?>">
										<button class=" remove_image"><?php esc_html_e( 'Remove Image', 'eventlist' ); ?></button>
									<?php }else{ ?>
										<img class="image-preview" src="<?php echo EL_PLUGIN_URI.'assets/img/unknow_user.png'; ?>" alt="<?php esc_html_e( 'author', 'eventlist' ); ?>">
										<br><br>
									<?php } ?>
								</div>

								<button class="button add_image" data-uploader-title="<?php esc_html_e( "Add image to profile", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_html_e( "Add image", 'eventlist' ); ?>"><?php esc_html_e( "Add image", 'eventlist' ); ?></button>
								<span><?php esc_html_e( 'Recommended size: 400x400px','eventlist' ); ?></span>
								<input type="hidden" id="author_id_image" class="author_id_image" name="author_id_image" value="<?php echo esc_attr( $author_id_image ); ?>">
								
							</div>
						<?php } ?>

						<!-- username -->
						<div class="vendor_field">
							<label class="control-label" for="display_name"><?php esc_html_e( 'User Login', 'eventlist' ); ?></label>
							<?php echo the_author_meta('user_login', $user_id); ?>
						</div>

						<!-- First Name -->
						<?php
						$show_first_name = $OVALG_Settings ? $OVALG_Settings->show_first_name() : 'yes';
						if ( apply_filters( 'ovalg_register_user_show_first_name', true ) && $show_first_name == 'yes' ): ?>
							
							<div class="vendor_field">
								<label class="control-label" for="first_name">
									<?php esc_html_e( 'Prénom <sup>*</sup>', 'eventlist' ); ?>
								</label>
								<input id="first_name" value="<?php echo esc_attr( $first_name ); ?>" name="first_name" type="text" required>
							</div>

						<?php endif; ?>

						<!-- Last Name -->
						<?php
						$show_last_name = $OVALG_Settings ? $OVALG_Settings->show_last_name() : 'yes';
						if ( apply_filters( 'ovalg_register_user_show_last_name', true ) && $show_last_name == 'yes' ): ?>

							<div class="vendor_field">
								<label class="control-label" for="last_name">
									<?php esc_html_e( 'Last Name', 'eventlist' ); ?>
								</label>
								<input id="last_name" value="<?php echo esc_attr( $last_name ); ?>" name="last_name" type="text" required>
							</div>

						<?php endif; ?>

						<!-- Name -->
						<div class="vendor_field">
							<label class="control-label" for="display_name"><?php esc_html_e( 'Display Name', 'eventlist' ); ?></label>
							<input id="display_name" value="<?php echo esc_attr( $display_name ); ?>" name="display_name" type="text" placeholder="<?php esc_attr_e( 'William Smith', 'eventlist' ); ?>" required>
						</div>

						<!-- Email -->
						<div class="vendor_field">
							<label class="control-label" for="user_email"><?php esc_html_e( 'Email', 'eventlist' ); ?></label>
							<input id="user_email" value="<?php the_author_meta('user_email', $user_id) ?>" name="user_email" type="text" placeholder="<?php esc_attr_e( 'example@email.com', 'eventlist' ); ?>" disabled>
						</div>

						<!-- Job -->
						<?php
						$show_job = $OVALG_Settings ? $OVALG_Settings->show_job() : 'yes';
						if( apply_filters( 'ovalg_register_user_show_job', true ) && $show_job == 'yes' ){ ?>
							<div class="vendor_field">
								<label class="control-label" for="user_job"><?php esc_html_e( 'Poste', 'eventlist' ); ?></label>
								<input id="user_job" value="<?php echo esc_attr( $user_job ); ?>" name="user_job" type="text" placeholder="<?php esc_attr_e( 'CEO', 'eventlist' ); ?>" >
							</div>
						<?php } ?>

						<!-- Phone -->
						<?php
						$show_phone = $OVALG_Settings ? $OVALG_Settings->show_phone() : 'yes';
						if( apply_filters( 'ovalg_register_user_show_phone', true ) && $show_phone == 'yes' ){ ?>
							<div class="vendor_field">
								<label class="control-label" for="user_phone"><?php esc_html_e( 'Phone', 'eventlist' ); ?></label>
								<input id="user_phone" value="<?php echo esc_attr( $user_phone ); ?>" name="user_phone" type="text"
								data-msg="<?php esc_attr_e( 'Please enter a valid phone number', 'eventlist' ); ?>"
								placeholder="<?php esc_attr_e( '(+123) 456 7890', 'eventlist' ); ?>" />
							</div>
						<?php } ?>
						<?php
						$show_website = $OVALG_Settings ? $OVALG_Settings->show_website() : 'no';
						if ( apply_filters( 'ovalg_register_user_show_website', true ) && $show_website == 'yes' ) { ?>
							<div class="vendor_field">
								<label class="control-label" for="user_url"><?php esc_html_e( 'Website', 'eventlist' ); ?></label>
								<input id="user_url" value="<?php echo esc_url( wp_get_current_user()->user_url ); ?>" name="user_url" type="url" placeholder="<?php echo esc_attr( 'https://ovatheme.com' ); ?>" >
							</div>
						<?php } ?>
						<!-- Address -->
						<?php
						$show_address = $OVALG_Settings ? $OVALG_Settings->show_address() : 'yes';
						if( apply_filters( 'ovalg_register_user_show_address', true ) && $show_address == 'yes' ){ ?>
							<div class="vendor_field">
								<label class="control-label" for="user_address"><?php esc_html_e( 'Address', 'eventlist' ); ?></label>
								<input id="user_address" value="<?php echo esc_attr( $user_address ); ?>" name="user_address" type="text" placeholder="<?php esc_attr_e( '123 New York', 'eventlist' ); ?>" >
							</div>
						<?php } ?>
						<!-- Description -->
						<?php
						$show_description = $OVALG_Settings ? $OVALG_Settings->show_description() : 'yes';
						if( apply_filters( 'ovalg_register_user_show_description', true ) && $show_description == 'yes' ){ ?>
							<div class="vendor_field textarea">
								<label class="control-label" for="description"><?php esc_html_e( 'Description', 'eventlist' ); ?></label>
								<textarea id="description" value="<?php echo esc_attr( $description ); ?>" name="description" type="text" placeholder="<?php esc_attr_e( 'Insert Description', 'eventlist' ); ?>" class="description form-control input-md "><?php echo esc_html( $description ); ?></textarea>
							</div>
						<?php } ?>
						<!-- User Custom Field -->
						<div class="ova_profile_custom_field_wrapper">
						<?php if ( $user_meta_field ) :
								foreach ( $user_meta_field as $name => $field ):

									$check_display_field = true;
									$name = 'ova_'.$name;
									switch ( $field['used_for'] ) {
										case 'vendor':
											$check_display_field = el_is_vendor();
											break;
										case 'user':
											$check_display_field = !el_is_vendor();
											break;
										
										default:
											break;
									}
									$required = $field['required'] == "on" ? "required" : "";

									if ( $field['enabled'] == "on" && $check_display_field ) {
										$user_meta_value = get_user_meta( $user_id, $name, true );

										if ( $field['type'] == 'text' ) {
											?>
											<div class="vendor_field ova-cf">
												<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
												<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
												data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), $field['label'] ); ?>"
												/>
											</div>
											<?php  } elseif ( $field['type'] == 'tel' ) { ?>
												<div class="vendor_field ova-cf">
													<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
													<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
														data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), $field['label'] ); ?>"
														data-invalid="<?php echo sprintf( esc_html__( 'Please insert valid %s.', 'eventlist' ), $field['label'] ); ?>"
													/>
												</div>
											<?php } elseif ( $field['type'] == 'email' ) { ?>
											<div class="vendor_field ova-cf">
												<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
												<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
												data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), $field['label'] ); ?>"
												data-invalid="<?php echo sprintf( esc_html__( 'Please insert valid %s.', 'eventlist' ), $field['label'] ); ?>"
												/>
											</div>
											<?php } elseif ( $field['type'] == 'password' ) { ?>
											<div class="vendor_field ova-cf">
												<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
												<div class="ova_input_wrap">
													<input autocomplete="off" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" name="<?php echo esc_attr( $name ); ?>" type="password" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
														data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), $field['label'] ); ?>"
													/>
													<div class="show_pass">
														<i class="dashicons dashicons-hidden"></i>
													</div>
												</div>
											</div>
											<?php
										} elseif ( $field['type'] == 'textarea' ) {
											?>
											<div class="vendor_field ova-cf textarea">
												<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
												<textarea id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
												data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), $field['label'] ); ?>"
												class="description form-control input-md "><?php echo esc_html( $user_meta_value ); ?></textarea>
											</div>
											<?php
										} elseif ( $field['type'] == 'select' ) {
											$ova_options_key 	= $field['ova_options_key'];
											$ova_options_text 	= $field['ova_options_text'];
											?>
											<div class="vendor_field ova-cf">

												<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
												<select id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" data-msg="<?php echo sprintf( esc_attr__( 'The %s cannot be empty!', 'eventlist' ), $field['label'] ); ?>" >
													<option value=""><?php echo esc_html( $field['placeholder'] ); ?></option>
													<?php if ( $ova_options_key ): ?>
														<?php foreach ( $ova_options_key as $key => $item ): ?>
															<option value="<?php echo esc_attr( $item ); ?>"
																<?php selected( $user_meta_value, $item ); ?>
																><?php echo esc_html( $ova_options_text[$key] ); ?></option>
														<?php endforeach; ?>
													<?php endif; ?>
												</select>
											</div>
											<?php
										} elseif ( $field['type'] == 'radio' ) {
											$ova_radio_key 	= $field['ova_radio_key'];
											$ova_radio_text = $field['ova_radio_text'];
											?>
											<?php if ( $ova_radio_key ): ?>
												<div class="vendor_field ova-cf">
													<label><?php echo esc_html( $field['label'] ); ?></label>
												<?php foreach ( $ova_radio_key as $key => $item ): ?>											<div class="vendor_radio_field">
														<input type="radio" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $item ); ?>"
														id="<?php echo esc_attr( $name .'_'.$item ); ?>"
														name="<?php echo esc_attr( $name ); ?>"
														<?php $user_meta_value != '' ? checked( $user_meta_value, $item ) : checked( 0, $key ); ?>
														 />
														<label for="<?php echo esc_attr( $name .'_'.$item ); ?>"><?php echo esc_html( $ova_radio_text[$key] ); ?></label>
													</div>
												<?php endforeach; ?>
												</div>
											<?php endif;
										} elseif ( $field['type'] == 'checkbox' ) {
											$ova_checkbox_key 	= $field['ova_checkbox_key'];
											$ova_checkbox_text 	= $field['ova_checkbox_text'];
											?>
											<div class="vendor_field ova-cf checkbox">
												<label><?php echo esc_html( $field['label'] ); ?></label>
												<div class="checkbox_field_wrap" data-msg="<?php echo sprintf( esc_attr__( 'Please check %s.', 'eventlist' ), $field['label'] ); ?>">
												<?php
												foreach ( $ova_checkbox_key as $key => $item ):
													$checkbox_input = is_array( $user_meta_value ) ? $user_meta_value : array( $user_meta_value ) ;
													$checked 		= in_array($item, $checkbox_input) ? $item : '';
													?>
													<div class="vendor_checkbox_field">
														<input type="checkbox" class="<?php echo esc_attr( $required ); ?>" id="<?php echo esc_attr( $name .'_'.$item ); ?>"
														<?php checked( $checked, $item ); ?>
														name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $item ); ?>" />
														<label for="<?php echo esc_attr( $name .'_'.$item ); ?>"><?php echo esc_html( $ova_checkbox_text[$key] ); ?></label>
													</div>
												<?php endforeach;
												?>
												</div>
											</div>
											<?php
										} elseif ( $field['type'] == 'file' ) {
											$attachment_id 	= $user_meta_value;
											$file_name 		= basename( get_attached_file( $attachment_id ) );
											$file_url 		= wp_get_attachment_url( $attachment_id );
											?>
											<div class="vendor_field ova-cf file_field">
												<label><?php echo esc_html( $field['label'] ); ?></label>
												<div class="vendor_file_field">
													<div class="file__wrap">
														<?php if ( $attachment_id && get_post( $attachment_id ) ) {
															$mime_type = get_post_mime_type( $attachment_id );
															if ( ! str_contains($mime_type, "image") ) {
																?>
																<span class="file-name"><a href="<?php echo esc_url( $file_url ); ?>" target="_blank"><?php echo esc_html( $file_name ); ?></a></span>
																<?php
															} else {
																$image_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
																?>
																<img class="ova__thumbnail" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $file_name ); ?>">
																<?php
															}
															?>
															<a class="ova_remove_file" href="#"><i class="far fa-trash-alt"></i></a>
														<?php } ?>
													</div>
													<a class="button ova_upload_file el_btn_add" href="#" data-uploader-title="<?php echo esc_attr( $field['label'] ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Upload file', 'eventlist' ); ?>"><?php esc_html_e( 'Upload file', 'eventlist' ); ?></a>
													
													<input type="hidden" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" data-msg="<?php echo sprintf( esc_attr__( 'The %s cannot be empty!', 'eventlist' ), $field['label'] ); ?>" />
												</div>
											</div>
											<?php
										}
									}

								endforeach; ?>
								
							<?php endif; ?>
						</div>

						<div class="vendor_field">
							<input type="submit" name="el_update_profile" class="button el_submit_btn" value="<?php esc_attr_e( 'Update Profile', 'eventlist' ); ?>" />
							<span class="ova__loader">
								<img src="<?php echo esc_url( includes_url() . 'js/tinymce/skins/lightgray/img//loader.gif' ); ?>" />
							</span>
							
						</div>
						
						<?php wp_nonce_field( 'el_update_profile_nonce', 'el_update_profile_nonce' ); ?>
					</form>

				</div>


				<!-- Mon Organisation (NOUVEAU) -->
				<?php if( el_is_vendor() ){ ?>
					<div id="author_organisation" class="tab-contents">
						<h2><?php esc_html_e( 'Informations sur votre organisation', 'eventlist' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Ces informations administratives sont nécessaires pour identifier votre structure.', 'eventlist' ); ?></p>

						<form id="el_save_organisation" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

							<!-- Nom de l'organisation -->
							<div class="vendor_field">
								<label class="control-label" for="org_name">
									<?php esc_html_e( 'Nom de l\'organisation', 'eventlist' ); ?> <sup style="color: red;">*</sup>
								</label>
								<input id="org_name" name="org_name" type="text"
									value="<?php echo esc_attr( get_user_meta( $user_id, 'org_name', true ) ); ?>"
									placeholder="<?php esc_attr_e( 'Ex: Association Le Hiboo', 'eventlist' ); ?>"
									required>
							</div>

							<!-- Rôle de l'organisation -->
							<div class="vendor_field checkbox">
								<label><?php esc_html_e( 'Rôle de l\'organisation', 'eventlist' ); ?> <sup style="color: red;">*</sup></label>
								<?php
								$org_roles = get_user_meta( $user_id, 'org_role', true );
								$org_roles = is_array( $org_roles ) ? $org_roles : array();
								$available_roles = get_option( 'el_org_roles_list', array(
									'organisateur' => __( 'Organisateur d\'événements', 'eventlist' ),
									'lieu' => __( 'Lieu / Salle', 'eventlist' ),
									'prestataire' => __( 'Prestataire de services', 'eventlist' ),
									'association' => __( 'Association culturelle', 'eventlist' ),
								));
								?>
								<div class="checkbox_field_wrap">
									<?php foreach( $available_roles as $role_key => $role_label ): ?>
										<div class="vendor_checkbox_field">
											<input type="checkbox"
												id="org_role_<?php echo esc_attr($role_key); ?>"
												name="org_role[]"
												value="<?php echo esc_attr($role_key); ?>"
												<?php checked( in_array($role_key, $org_roles) ); ?> />
											<label for="org_role_<?php echo esc_attr($role_key); ?>">
												<?php echo esc_html($role_label); ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<!-- Statut juridique -->
							<div class="vendor_field">
								<label class="control-label" for="org_statut_juridique">
									<?php esc_html_e( 'Statut juridique', 'eventlist' ); ?> <sup style="color: red;">*</sup>
								</label>
								<?php
								$org_statut = get_user_meta( $user_id, 'org_statut_juridique', true );
								$statuts = get_option( 'el_statuts_juridiques_list', array(
									'association' => __( 'Association loi 1901', 'eventlist' ),
									'sarl' => __( 'SARL', 'eventlist' ),
									'sas' => __( 'SAS', 'eventlist' ),
									'auto_entrepreneur' => __( 'Auto-entrepreneur / Micro-entreprise', 'eventlist' ),
									'eirl' => __( 'EIRL', 'eventlist' ),
									'sa' => __( 'SA', 'eventlist' ),
									'ei' => __( 'Entreprise Individuelle', 'eventlist' ),
									'autre' => __( 'Autre', 'eventlist' ),
								));
								// Ajouter l'option vide au début
								$statuts = array_merge( array( '' => __( '-- Sélectionnez --', 'eventlist' ) ), $statuts );
								?>
								<select id="org_statut_juridique" name="org_statut_juridique" required>
									<?php foreach( $statuts as $key => $label ): ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected( $org_statut, $key ); ?>>
											<?php echo esc_html($label); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Type de structure -->
							<div class="vendor_field checkbox">
								<label><?php esc_html_e( 'Type de structure', 'eventlist' ); ?> <sup style="color: red;">*</sup></label>
								<?php
								$org_types = get_user_meta( $user_id, 'org_type_structure', true );
								$org_types = is_array( $org_types ) ? $org_types : array();
								$available_types = get_option( 'el_types_structure_list', array(
									'culturel' => __( 'Culturel', 'eventlist' ),
									'sportif' => __( 'Sportif', 'eventlist' ),
									'educatif' => __( 'Éducatif', 'eventlist' ),
									'loisirs' => __( 'Loisirs', 'eventlist' ),
									'artistique' => __( 'Artistique', 'eventlist' ),
									'social' => __( 'Social / Solidaire', 'eventlist' ),
								));
								?>
								<div class="checkbox_field_wrap">
									<?php foreach( $available_types as $type_key => $type_label ): ?>
										<div class="vendor_checkbox_field">
											<input type="checkbox"
												id="org_type_<?php echo esc_attr($type_key); ?>"
												name="org_type_structure[]"
												value="<?php echo esc_attr($type_key); ?>"
												<?php checked( in_array($type_key, $org_types) ); ?> />
											<label for="org_type_<?php echo esc_attr($type_key); ?>">
												<?php echo esc_html($type_label); ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<!-- SIREN -->
							<div class="vendor_field">
								<label class="control-label" for="org_siren">
									<?php esc_html_e( 'SIREN', 'eventlist' ); ?> <sup style="color: red;">*</sup>
									<span class="info-icon" title="<?php esc_attr_e( 'Numéro SIREN à 9 chiffres', 'eventlist' ); ?>">?</span>
								</label>
								<input id="org_siren" name="org_siren" type="text"
									value="<?php echo esc_attr( get_user_meta( $user_id, 'org_siren', true ) ); ?>"
									placeholder="<?php esc_attr_e( '123456789', 'eventlist' ); ?>"
									pattern="[0-9]{9}"
									maxlength="9"
									required>
								<small><?php esc_html_e( '9 chiffres uniquement', 'eventlist' ); ?></small>
							</div>

							<!-- Date de création -->
							<div class="vendor_field">
								<label class="control-label" for="org_date_creation">
									<?php esc_html_e( 'Date de création de l\'entité', 'eventlist' ); ?>
								</label>
								<input id="org_date_creation" name="org_date_creation" type="date"
									value="<?php echo esc_attr( get_user_meta( $user_id, 'org_date_creation', true ) ); ?>">
							</div>

							<div class="vendor_field">
								<input type="submit" name="el_update_organisation" class="button el_submit_btn" value="<?php esc_attr_e( 'Enregistrer', 'eventlist' ); ?>" />
								<span class="ova__loader">
									<img src="<?php echo esc_url( includes_url() . 'js/tinymce/skins/lightgray/img//loader.gif' ); ?>" />
								</span>
							</div>

							<?php wp_nonce_field( 'el_update_organisation_nonce', 'el_update_organisation_nonce' ); ?>
						</form>
					</div>
				<?php } ?>


				<!-- Présentation (Profil Public) (NOUVEAU) -->
				<?php if( el_is_vendor() ){ ?>
					<div id="author_presentation" class="tab-contents">
						<div class="profile_public_notice">
							<i class="icon_info"></i>
							<p><?php esc_html_e( 'Les informations de cette section seront visibles sur votre profil public.', 'eventlist' ); ?></p>
						</div>

						<form id="el_save_presentation" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

							<!-- Description (déplacé depuis author_profile) -->
							<div class="vendor_field textarea">
								<label class="control-label" for="description"><?php esc_html_e( 'Description', 'eventlist' ); ?></label>
								<textarea id="description" name="description" rows="8" placeholder="<?php esc_attr_e( 'Présentez votre organisation...', 'eventlist' ); ?>"><?php echo esc_textarea( get_user_meta( $user_id, 'description', true ) ); ?></textarea>
								<small><?php esc_html_e( 'Les liens URL ne sont pas autorisés dans la description.', 'eventlist' ); ?></small>
							</div>

							<!-- Image à la une -->
							<div class="vendor_field">
								<label class="control-label"><?php esc_html_e( 'Image de couverture', 'eventlist' ); ?></label>
								<?php
								$org_cover_image = get_user_meta( $user_id, 'org_cover_image', true );
								?>
								<div class="image_upload_wrap">
									<?php if( $org_cover_image ): ?>
										<img class="preview_cover_image" src="<?php echo esc_url( wp_get_attachment_image_url($org_cover_image, 'large') ); ?>" style="max-width: 100%; height: auto; margin-bottom: 10px;">
										<button type="button" class="button remove_cover_image"><?php esc_html_e( 'Retirer l\'image', 'eventlist' ); ?></button>
									<?php endif; ?>
									<button type="button" class="button add_cover_image" data-uploader-title="<?php esc_attr_e( 'Sélectionner une image de couverture', 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Utiliser cette image', 'eventlist' ); ?>">
										<?php esc_html_e( 'Ajouter une image', 'eventlist' ); ?>
									</button>
									<input type="hidden" name="org_cover_image" class="org_cover_image_id" value="<?php echo esc_attr( $org_cover_image ); ?>">
								</div>
								<small><?php esc_html_e( 'Format recommandé : 1200x400px', 'eventlist' ); ?></small>
							</div>

							<!-- Page Web -->
							<div class="vendor_field">
								<label class="control-label" for="org_web">
									<?php esc_html_e( 'Site Web de l\'organisation', 'eventlist' ); ?>
								</label>
								<input id="org_web" name="org_web" type="url"
									value="<?php echo esc_attr( get_user_meta( $user_id, 'org_web', true ) ); ?>"
									placeholder="https://www.exemple.com">
							</div>

							<!-- Vidéo YouTube -->
							<div class="vendor_field">
								<label class="control-label" for="org_video_youtube">
									<?php esc_html_e( 'Vidéo de présentation (YouTube)', 'eventlist' ); ?>
								</label>
								<input id="org_video_youtube" name="org_video_youtube" type="url"
									value="<?php echo esc_attr( get_user_meta( $user_id, 'org_video_youtube', true ) ); ?>"
									placeholder="https://www.youtube.com/watch?v=...">
							</div>

							<div class="vendor_field">
								<input type="submit" name="el_update_presentation" class="button el_submit_btn" value="<?php esc_attr_e( 'Enregistrer', 'eventlist' ); ?>" />
								<span class="ova__loader">
									<img src="<?php echo esc_url( includes_url() . 'js/tinymce/skins/lightgray/img//loader.gif' ); ?>" />
								</span>
							</div>

							<?php wp_nonce_field( 'el_update_presentation_nonce', 'el_update_presentation_nonce' ); ?>
						</form>
					</div>
				<?php } ?>


				<!-- Social (existant - on le garde pour l'instant) -->
				<?php if( el_is_vendor() ){ ?>
					<div id="author_social" class="tab-contents">
						<form id="el_save_social" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

							<div class="wrap_social">
								<div class="social_list">
									<?php if ($user_profile_social) { 
										foreach ($user_profile_social as $k_social => $v_social) { 
											if ($v_social[0] != '') { ?>
												
												<div class="social_item vendor_field">
													<input type="text" name="<?php echo esc_attr('user_profile_social['.$k_social.'][link]'); ?>" class="link_social" value="<?php echo esc_attr($v_social[0]); ?>" placeholder="<?php echo esc_attr( 'https://' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
													<select name="<?php echo esc_attr('user_profile_social['.$k_social.'][icon]'); ?>" class="icon_social">
														<?php foreach (el_get_social() as $k_icon => $v_icon) { ?>
															<option value="<?php echo esc_attr($k_icon); ?>" <?php echo esc_attr($k_icon == $v_social[1] ? 'selected' : ''); ?> ><?php echo esc_html( $v_icon ); ?></option>
														<?php } ?>
													</select>
													<button class="button remove_social">x</button>
												</div>
											<?php } 
										} 
									} ?>
								</div>
								<button class="button add_social"><i class="icon_plus"></i>&nbsp;<?php esc_html_e( 'Add Social', 'eventlist' ); ?></button>
							</div>

							<input type="submit" name="el_update_social" class="el_submit_btn" value="<?php esc_attr_e( 'Update Social', 'eventlist' ); ?>" class="el_update_social" />
							
							<?php wp_nonce_field( 'el_update_social_nonce', 'el_update_social_nonce' ); ?>
						</form>
						<div class="success_social" style="display: none;"><?php esc_html_e( 'Update Success', 'eventlist' ); ?></div>
					</div>
				<?php } ?>


				<!-- Password -->
				<div id="author_password" class="tab-contents">
					<form id="el_save_password" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

						<!-- Old Password -->
						<div class="wrap_old_password vendor_field">
							<label class="control-label" for="old_password"><?php esc_html_e( 'Old Password', 'eventlist' ); ?></label>
							<div class="show_pass">
								<i class="dashicons dashicons-hidden"></i>
							</div>
							<input autocomplete="off" id="old_password" value="" name="old_password" type="password" placeholder="<?php esc_html_e( 'Old Password', 'eventlist' ) ?>" required>
							<div class="check_old_pass" style="display: none;"><?php esc_html_e( 'Please Check Again', 'eventlist' ); ?></div>
						</div>

						<!--New Password -->
						<div class="wrap_new_password vendor_field">
							<label class="control-label" for="new_password"><?php esc_html_e( 'New Password', 'eventlist' ); ?></label>
							<div class="show_pass">
								<i class="dashicons dashicons-hidden"></i>
							</div>
							<input autocomplete="off" id="new_password" value="" name="new_password" type="password" placeholder="<?php esc_html_e( 'New Password', 'eventlist' ) ?>" required>
						</div>

						<!-- Confirm Password -->
						<div class="wrap_confirm_password vendor_field">
							<label class="control-label" for="confirm_password"><?php esc_html_e( 'Confirm Password', 'eventlist' ); ?></label>
							<div class="show_pass">
								<i class="dashicons dashicons-hidden"></i>
							</div>
							<input id="confirm_password" autocomplete="off" value="" name="confirm_password" type="password" placeholder="<?php esc_html_e( 'Confirm Password', 'eventlist' ) ?>" required>
							<div class="check"></div>
						</div>
						<input type="submit" name="el_update_password" class="el_submit_btn" value="<?php esc_html_e( 'Update Password', 'eventlist' ); ?>" class="el_update_password" />
						
						<?php wp_nonce_field( 'el_update_password_nonce', 'el_update_password_nonce' ); ?>

					</form>
				</div>
				
				<?php if( el_is_vendor() && apply_filters( 'el_profile_show_bank', true ) ){ ?>
					<div id="author_bank" class="tab-contents">
						
						<form id="el_save_bank" enctype="multipart/form-data" method="post" autocomplete="off" autocorrect="off" autocapitalize="none">

							<?php $_prefix = OVA_METABOX_EVENT; 
								$id_author = wp_get_current_user()->ID;
								$payout_method_user = get_user_meta($id_author, 'payout_method', true);

							?>

							<div class="manage_bank">

								<label>
									<strong>
										<?php esc_html_e( 'Payout Method', 'eventlist' ); ?>:
									</strong>
								</label>

								<?php if( apply_filters( 'el_profile_show_bank_info', true ) ){ ?>
									<div class="payout-method-item">
										<input type="radio" value="bank" name="<?php echo esc_attr($_prefix.'payout_method'); ?>" <?php if ( $payout_method_user == 'bank' ) echo esc_attr('checked') ; ?> data-method="bank" />
										<span>
											<?php esc_html_e( 'Bank', 'eventlist' ); ?>
										</span>
									</div>
								<?php } ?>

								<?php if( apply_filters( 'el_profile_show_paypal', true ) ){ ?>	
									<div class="payout-method-item">
										<input type="radio" value="paypal" name="<?php echo esc_attr($_prefix.'payout_method'); ?>" <?php if ( $payout_method_user== 'paypal') echo esc_attr('checked') ; ?> data-method="paypal"/>
										<span>
											<?php esc_html_e( 'Paypal', 'eventlist' ); ?>
										</span>
									</div>
								<?php } ?>

								<?php if( apply_filters( 'el_profile_show_payout_method_info', true ) ){ ?>
								<?php
								$payout_method = get_payout_method();
								$list_id = [];
								?>
								<?php if( $payout_method->have_posts() ) : while ( $payout_method->have_posts() ) : $payout_method->the_post(); ?>
									<?php
									$id = get_the_id();
									$list_id[]='method_'.$id;
									$title = get_the_title();
									?>
									<div class="payout-method-item">
										<input type="radio" value="<?php echo esc_attr($id);?>" name="<?php echo esc_attr($_prefix.'payout_method'); ?>" <?php if ( $payout_method_user == $id) echo esc_attr('checked') ; ?> data-method = "<?php echo esc_attr('method_'.$id);?>" />
										<span>
											<?php echo esc_html($title,'eventlist'); ?>
										</span>
									</div>
								<?php endwhile; endif; wp_reset_postdata(); ?>

								<input type="hidden" id="el_list_id_payout_method" value="<?php echo esc_attr( json_encode( $list_id) ) ?>" >
							<?php } ?>

							</div>
							
							<?php if( apply_filters( 'el_profile_show_bank_info', true ) ){ ?>

								<div class="bank_method">

									<p class="heading"><?php esc_html_e('Your Bank Account', 'eventlist'); ?></p>
									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_bank"><?php esc_html_e( 'Account owner *', 'eventlist' ); ?></label>
										<input id="user_bank_owner" value="<?php echo esc_attr( $user_bank_owner ); ?>" name="user_bank_owner" type="text" placeholder="<?php esc_html_e( 'John Michael Doe', 'eventlist' ); ?>" required>
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_number"><?php esc_html_e( 'Account number *', 'eventlist' ); ?></label>
										<input id="user_bank_number" value="<?php echo esc_attr( $user_bank_number ); ?>" name="user_bank_number" type="text" placeholder="<?php esc_html_e( '123456789', 'eventlist' ); ?>" required>
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_number"><?php esc_html_e( 'Bank Name *', 'eventlist' ); ?></label>
										<input id="user_bank_name" value="<?php echo esc_attr( $user_bank_name ); ?>" name="user_bank_name" type="text"  placeholder="<?php esc_html_e( 'HSBC Bank USA', 'eventlist' ); ?>" required >
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_number"><?php esc_html_e( 'Branch *', 'eventlist' ); ?></label>
										<input id="user_bank_branch" value="<?php echo esc_attr( $user_bank_branch ); ?>" name="user_bank_branch" type="text" placeholder="<?php esc_html_e( 'HSBC', 'eventlist' ); ?>" required>
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_number"><?php esc_html_e( 'Routing Number', 'eventlist' ); ?></label>
										<input id="user_bank_routing" value="<?php echo esc_attr( $user_bank_routing ); ?>" name="user_bank_routing" type="text"  required >
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_iban"><?php esc_html_e( 'IBAN', 'eventlist' ); ?></label>
										<input id="user_bank_iban" value="<?php echo esc_attr( $user_bank_iban ); ?>" name="user_bank_iban" type="text" >
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_swift_code"><?php esc_html_e( 'Swift Code', 'eventlist' ); ?></label>
										<input id="user_bank_swift_code" value="<?php echo esc_attr( $user_bank_swift_code ); ?>" name="user_bank_swift_code" type="text" >
									</div>

									<!-- Account owner -->
									<div class="vendor_field">
										<label class="control-label" for="user_ifsc_code"><?php esc_html_e( 'IFSC Code', 'eventlist' ); ?></label>
										<input id="user_bank_ifsc_code" value="<?php echo esc_attr( $user_bank_ifsc_code ); ?>" name="user_bank_ifsc_code" type="text" >
									</div>

								</div>

							<?php } ?>

							
							<?php if( apply_filters( 'el_profile_show_paypal', true ) ){ ?>

								<div class="paypal_method"> 

									<p class="heading">
										<?php esc_html_e('Your Paypal Account', 'eventlist'); ?>
									</p>

									<div class="vendor_field">

										<label class="control-label" for="user_bank">
											<?php esc_html_e( 'Paypal Email', 'eventlist' ); ?>
										</label>

										<input id="user_bank_paypal_email" value="<?php echo esc_attr( $user_bank_paypal_email ); ?>" name="user_bank_paypal_email" type="text" required >

									</div>

								</div>

							<?php } ?>
							<?php if( apply_filters( 'el_profile_show_payout_method_info', true ) ){ ?>

							<?php
							$payout_method = get_payout_method();
							$data_payout_method_field = get_user_meta($id_author, 'data_payout_method_field', true);
							$data_payout_method_field = ! empty( $data_payout_method_field ) ? json_decode( $data_payout_method_field , true) : [];
							$list_payout_method_field = [];
	
							?>
							<?php if( $payout_method->have_posts() ) : while ( $payout_method->have_posts() ) : $payout_method->the_post(); ?>
								<?php
								$id = get_the_id();
								$title = get_the_title();
								$list_field = get_post_meta( $id , 'ova_met_payout_method_group', true);
								?>

								<div class="field_payout_method <?php echo esc_attr( 'method_'.$id); ?>">
									<?php if ( ! empty( $list_field ) ):?>
										<div class="list_field_payout_method">
											<?php foreach ( $list_field as $field ):
												$label 			= isset($field['ova_met_label_method']) ? $field['ova_met_label_method'] : '';
												$name 			= isset($field['ova_met_name_method']) ? $field['ova_met_name_method'] : '';
												$placeholder 	= isset($field['ova_met_placeholder']) ? $field['ova_met_placeholder'] : '';
												$required 		= isset($field['ova_met_required']) ? $field['ova_met_required'] : '';

												if ( $required == 'yes' ) {
													$class_required = 'required';
												} else {
													$class_required = '';
												}

												$payout_method_field 		= isset($data_payout_method_field[$name]) ? $data_payout_method_field[$name] : '' ;
												$list_payout_method_field[] = $name;
											?>
												<div class="vendor_field">
													<label class="control-label" for="<?php echo esc_attr( $name ); ?>">
														<?php echo sprintf( __( '%s', 'eventlist' ), $label ); ?>
													</label>
													<input 
														id="<?php echo esc_attr( $name ); ?>"
														type="text" 
														name="<?php echo esc_attr( $name ); ?>" 
														placeholder="<?php echo esc_attr( $placeholder ); ?>" 
														value="<?php echo esc_attr( $payout_method_field ); ?>" 
														<?php echo $class_required; ?> />
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>



							<?php endwhile; endif; wp_reset_postdata(); ?>

							<input type="hidden" id="el_list_payout_method_field" value="<?php echo esc_attr( json_encode( $list_payout_method_field ) ) ?>" >

						<?php } ?>



							
							<?php if( el_is_vendor() && EL()->options->checkout->get( 'split_payment_stripe_active', 'no' ) == 'yes' ){ ?>

								<p class="heading">
									<?php esc_html_e('Your Stripe Account', 'eventlist'); ?>
								</p>

								<div class="vendor_field">
									<label class="control-label" for="user_bank"><?php esc_html_e( 'Stripe Email', 'eventlist' ); ?></label>
									<input id="user_bank_stripe_account" value="<?php echo esc_attr( $user_bank_stripe_account ); ?>" name="user_bank_stripe_account" type="text" >
								</div>

							<?php } ?>

							<input type="submit" name="el_update_payout_method" class="el_submit_btn el_update_payout_method" value="<?php esc_html_e( 'Update Payout Method', 'eventlist' ); ?>" class="el_save_bank" />
							
							
							<?php wp_nonce_field( 'el_update_payout_method_nonce', 'el_update_payout_method_nonce' ); ?>

						</form>
					</div>
				<?php } ?>


			</div> <!-- End tab-contents -->

			</div> <!-- End profile_content_area -->

		</div> <!-- End vendor_profile -->

	</div> <!-- End contents -->
</div> <!-- End vendor_wrap -->