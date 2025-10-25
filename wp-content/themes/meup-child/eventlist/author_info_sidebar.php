<?php if( ! defined( 'ABSPATH' ) ) exit();  ?>

<?php
$author_id = get_query_var( 'author' );
$eid = get_the_ID();

if( is_singular( 'event' ) ){
	$author_id = get_the_author_meta('ID');
}



if( $author_id ){

	$author_data = get_userdata( $author_id );

	$author_id_image = get_user_meta( $author_id, 'author_id_image', true ) ? get_user_meta( $author_id, 'author_id_image', true ) : '';
	if ( $author_id_image ) {
		$img_path = wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : wp_get_attachment_image_url($author_id_image, 'full');
	} else {

		$img_path = get_avatar_url($author_id);

	}

	$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );
	$user_phone = get_user_meta( $author_id, 'user_phone', true ) ? get_user_meta( $author_id, 'user_phone', true ) : '';
	$user_profile_social = get_user_meta( $author_id, 'user_profile_social', true ) ? get_user_meta( $author_id, 'user_profile_social', true ) : '';
	$user_description = get_user_meta( $author_id, 'description', true ) ? get_user_meta( $author_id, 'description', true ) : '';
	$user_address = get_user_meta( $author_id, 'user_address', true ) ? get_user_meta( $author_id, 'user_address', true ) : '';

	$user_email = get_user_meta( $author_id, 'user_email', true ) ? get_user_meta( $author_id, 'user_email', true ) : get_the_author_meta( 'user_email', $author_id );

	$user_job = get_user_meta( $author_id, 'user_job', true ) ? get_user_meta( $author_id, 'user_job', true ) : '';

	// V1 Le Hiboo - Nouvelles données organisation
	$org_name = get_user_meta( $author_id, 'org_name', true ) ? get_user_meta( $author_id, 'org_name', true ) : '';
	// V1 Le Hiboo - Nom à afficher publiquement (priorité sur org_name)
	$org_display_name = get_user_meta( $author_id, 'org_display_name', true );
	$org_public_name = ! empty( $org_display_name ) ? $org_display_name : $org_name;

	$org_cover_image = get_user_meta( $author_id, 'org_cover_image', true ) ? get_user_meta( $author_id, 'org_cover_image', true ) : '';
	$org_web = get_user_meta( $author_id, 'org_web', true ) ? get_user_meta( $author_id, 'org_web', true ) : '';
	$user_professional_email = get_user_meta( $author_id, 'user_professional_email', true ) ? get_user_meta( $author_id, 'user_professional_email', true ) : '';
	$user_country = get_user_meta( $author_id, 'user_country', true ) ? get_user_meta( $author_id, 'user_country', true ) : '';
	$user_city = get_user_meta( $author_id, 'user_city', true ) ? get_user_meta( $author_id, 'user_city', true ) : '';
	$user_postcode = get_user_meta( $author_id, 'user_postcode', true ) ? get_user_meta( $author_id, 'user_postcode', true ) : '';

	$info_organizer = get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) : '';
	$name_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) : '' );
	$phone_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) : '' );
	$mail_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) : '' );
	$job_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'job_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'job_organizer', true ) : '' );
	$social_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) : array() );

	?>

	<!-- Sidebar Info Card -->
	<div class="sidebar_info_card event_section_white">

		<!-- Contact Section -->
		<div class="info_section contact_section">
			<h3 class="section_title_sidebar">
				<i class="fas fa-address-card"></i>
				<?php esc_html_e( 'Informations de contact', 'eventlist' ); ?>
			</h3>

			<div class="contact_list">
				<?php if( apply_filters( 'el_show_phone_info', true ) ){ ?>
					<?php
					// Déterminer le numéro de téléphone à afficher
					$display_phone = '';
					if (is_singular('event') && $info_organizer == 'checked' && isset($phone_organizer) && $phone_organizer) {
						$display_phone = $phone_organizer;
					} elseif ( $user_phone ) {
						$display_phone = $user_phone;
					}
					?>
					<?php if( $display_phone ){ ?>
						<div class="contact_item contact_reveal_container">
							<div class="contact_icon">
								<i class="icon_phone"></i>
							</div>
							<div class="contact_content contact_reveal_content">
								<span class="contact_label"><?php esc_html_e( 'Téléphone', 'eventlist' ); ?></span>
								<button class="btn_reveal_phone btn_reveal_contact"
									data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
									data-event-id="0"
									data-context="author_page"
									data-phone="<?php echo esc_attr( $display_phone ); ?>">
									<i class="fas fa-eye"></i>
									<span><?php esc_html_e( 'Voir le numéro', 'eventlist' ); ?></span>
								</button>
								<div class="contact_hidden_value"></div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>

				<?php if( apply_filters( 'el_show_mail_info', true ) ){ ?>
					<div class="contact_item">
						<div class="contact_icon">
							<i class="icon_mail"></i>
						</div>
						<div class="contact_content">
							<span class="contact_label"><?php esc_html_e( 'Email', 'eventlist' ); ?></span>
							<?php if (is_singular('event') && $info_organizer == 'checked') { ?>
								<a href="<?php echo esc_attr('mailto:'.$mail_organizer); ?>" class="contact_value"><?php echo esc_html( $mail_organizer ); ?></a>
							<?php } elseif ( !is_singular('event') && $user_professional_email ) { ?>
								<a href="<?php echo esc_attr('mailto:'.$user_professional_email); ?>" class="contact_value"><?php echo esc_html( $user_professional_email ); ?></a>
							<?php } else { ?>
								<a href="<?php echo esc_attr('mailto:'.$user_email); ?>" class="contact_value"><?php echo esc_html( $user_email ); ?></a>
							<?php	} ?>
						</div>
					</div>
				<?php } ?>

				<?php if ( apply_filters( 'el_show_website_info', true ) ): ?>
					<?php if ( !is_singular('event') && $org_web ) : ?>
						<div class="contact_item">
							<div class="contact_icon">
								<i class="fas fa-link"></i>
							</div>
							<div class="contact_content">
								<span class="contact_label"><?php esc_html_e( 'Site web', 'eventlist' ); ?></span>
								<a href="<?php echo esc_url( $org_web ); ?>" rel="nofollow" target="_blank" class="contact_value"><?php echo esc_html( $org_web ); ?></a>
							</div>
						</div>
					<?php elseif ( $author_data->user_url ): ?>
						<div class="contact_item">
							<div class="contact_icon">
								<i class="fas fa-link"></i>
							</div>
							<div class="contact_content">
								<span class="contact_label"><?php esc_html_e( 'Site web', 'eventlist' ); ?></span>
								<a href="<?php echo esc_url( $author_data->user_url ); ?>" rel="nofollow" target="_blank" class="contact_value"><?php echo esc_html( $author_data->user_url ); ?></a>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( is_author() && apply_filters( 'el_show_address_info', true ) ) { ?>
					<?php
					// Construire l'adresse complète
					$full_address = '';
					if ( $user_city || $user_country || $user_postcode ) {
						$location_parts = array();
						if ( $user_postcode ) $location_parts[] = $user_postcode;
						if ( $user_city ) $location_parts[] = $user_city;
						if ( $user_country ) {
							$countries = array(
								'FR' => __( 'France', 'eventlist' ),
								'BE' => __( 'Belgique', 'eventlist' ),
								'CH' => __( 'Suisse', 'eventlist' ),
								'CA' => __( 'Canada', 'eventlist' ),
								'LU' => __( 'Luxembourg', 'eventlist' ),
								'MC' => __( 'Monaco', 'eventlist' ),
							);
							$location_parts[] = isset( $countries[$user_country] ) ? $countries[$user_country] : $user_country;
						}
						$full_address = implode( ', ', $location_parts );
					} elseif ( $user_address ) {
						$full_address = $user_address;
					}
					?>
					<?php if ( $full_address ) : ?>
						<div class="contact_item contact_reveal_container">
							<div class="contact_icon">
								<i class="icon_pin_alt"></i>
							</div>
							<div class="contact_content contact_reveal_content">
								<span class="contact_label"><?php esc_html_e( 'Adresse', 'eventlist' ); ?></span>
								<button class="btn_reveal_address btn_reveal_contact"
									data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
									data-event-id="0"
									data-context="author_page"
									data-address="<?php echo esc_attr( $full_address ); ?>">
									<i class="fas fa-map-marker-alt"></i>
									<span><?php esc_html_e( 'Voir l\'adresse', 'eventlist' ); ?></span>
								</button>
								<div class="contact_hidden_value"></div>
							</div>
						</div>
					<?php endif; ?>
				<?php } ?>
			</div>
		</div>
		<!-- Social Media Section -->
		<?php
		$has_social = false;
		if ( is_singular('event') ) {
			$has_social = ( $social_organizer && $info_organizer == 'checked' ) || ( $user_profile_social && $info_organizer == '' );
		} elseif ( !is_singular('event') && $user_profile_social ) {
			$has_social = true;
		}
		?>

		<?php if ( $has_social ) : ?>
			<div class="info_section social_section">
				<h3 class="section_title_sidebar">
					<i class="fas fa-share-nodes"></i>
					<?php esc_html_e( 'Réseaux sociaux', 'eventlist' ); ?>
				</h3>
				<div class="social_links">
					<?php if ( is_singular('event') ) : ?>
						<?php if ( $social_organizer && $info_organizer == 'checked' ) : ?>
							<?php foreach ($social_organizer as $k_social => $v_social) :
								if ($v_social['link_social'] != '') : ?>
									<a href="<?php echo esc_attr($v_social['link_social']); ?>" target="_blank" class="social_link" rel="nofollow">
										<i class="<?php echo esc_html($v_social['icon_social']); ?>"></i>
									</a>
								<?php endif;
							endforeach; ?>
						<?php elseif ( $user_profile_social && $info_organizer == '' ) : ?>
							<?php foreach ($user_profile_social as $k_social => $v_social) :
								if ($v_social[0] != '') : ?>
									<a href="<?php echo esc_attr($v_social[0]); ?>" target="_blank" class="social_link" rel="nofollow">
										<i class="<?php echo esc_html($v_social[1]); ?>"></i>
									</a>
								<?php endif;
							endforeach; ?>
						<?php endif; ?>
					<?php elseif ( !is_singular('event') && $user_profile_social ) : ?>
						<?php foreach ($user_profile_social as $k_social => $v_social) :
							if ($v_social[0] != '') : ?>
								<a href="<?php echo esc_attr($v_social[0]); ?>" target="_blank" class="social_link" rel="nofollow">
									<i class="<?php echo esc_html($v_social[1]); ?>"></i>
								</a>
							<?php endif;
						endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Contact Form Section -->
		<?php if( apply_filters( 'el_single_event_show_send_message_btn', true ) ){ ?>
			<div class="info_section contact_form_section" id="contact-form">
				<h3 class="section_title_sidebar">
					<i class="fas fa-envelope"></i>
					<?php esc_html_e( 'Envoyer un message', 'eventlist' ); ?>
				</h3>
				<button class="btn_send_message" id="open_contact_modal" data-require-login="<?php echo is_user_logged_in() ? 'false' : 'true'; ?>">
					<i class="icon_mail_alt"></i>
					<?php esc_html_e( 'Ouvrir le formulaire', 'eventlist' ); ?>
				</button>
			</div>
		<?php } ?>

	</div><!-- .sidebar_info_card -->

	<!-- Modal Popup pour Contact Form -->
	<?php
	$current_user_email = $current_user_name = $current_user_phone = '';

	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$current_user_id = $current_user->ID;

		$current_user_email = $current_user->user_email;
		$current_user_name = get_user_meta( $current_user_id, 'display_name', true );
		$current_user_phone = get_user_meta( $current_user_id, 'user_phone', true );
	}
	?>
	<div id="contact_modal_author" class="contact_modal" style="display: none;">
		<div class="contact_modal_overlay"></div>
		<div class="contact_modal_container">
			<div class="contact_modal_header">
				<h3><?php esc_html_e('Contacter l\'organisateur', 'eventlist'); ?></h3>
				<button class="contact_modal_close" aria-label="<?php esc_attr_e('Fermer', 'eventlist'); ?>">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="contact_modal_body">
				<form class="el-sendmail-author author-contact-form" id="author_contact_form">
					<div class="form_field">
						<label for="name_customer"><?php esc_html_e('Votre nom', 'eventlist') ?> *</label>
						<input type="text" id="name_customer" name="name_customer" value="<?php echo esc_attr($current_user_name); ?>" placeholder="<?php esc_attr_e('Votre nom complet', 'eventlist') ?>" required />
					</div>

					<div class="form_field">
						<label for="email_customer"><?php esc_html_e('Votre email', 'eventlist') ?> *</label>
						<input type="email" id="email_customer" name="email_customer" placeholder="<?php esc_attr_e('votre.email@example.com', 'eventlist') ?>" value="<?php echo esc_attr($current_user_email); ?>" required />
					</div>

					<div class="form_field">
						<label for="phone_customer"><?php esc_html_e('Téléphone', 'eventlist') ?> *</label>
						<input type="tel" id="phone_customer" name="phone_customer" value="<?php echo esc_attr($current_user_phone); ?>" placeholder="<?php esc_attr_e('+33 6 00 00 00 00', 'eventlist') ?>" required />
					</div>

					<div class="form_field">
						<label for="subject_customer"><?php esc_html_e('Objet de la demande', 'eventlist') ?> *</label>
						<input type="text" id="subject_customer" name="subject_customer" placeholder="<?php esc_attr_e('Sujet du message', 'eventlist') ?>" required />
					</div>

					<div class="form_field">
						<label for="content_customer"><?php esc_html_e('Message', 'eventlist') ?> *</label>
						<textarea id="content_customer" name="content" rows="6" placeholder="<?php esc_attr_e('Écrivez votre message ici...', 'eventlist') ?>" required></textarea>
					</div>

					<!-- Cloudflare Turnstile CAPTCHA -->
					<div class="form_field">
						<div class="cf-turnstile" data-sitekey="0x4AAAAAAB75T9T-6xfs5mqd" data-theme="light"></div>
					</div>

					<input type="hidden" name="author_id" value="<?php echo esc_attr( $author_id ); ?>">
					<input type="hidden" name="action" value="send_author_message">
					<?php wp_nonce_field( 'contact_author_nonce', 'contact_nonce' ); ?>

					<div class="form_actions">
						<button type="submit" class="contact_submit_btn">
							<?php esc_html_e('Envoyer', 'eventlist'); ?>
						</button>
					</div>
				</form>
				<div class="el-notify">
					<p class="success"><i class="fas fa-check-circle"></i> <?php esc_html_e('Message envoyé avec succès !', 'eventlist') ?></p>
					<p class="error"><i class="fas fa-exclamation-circle"></i> <?php esc_html_e('Échec de l\'envoi du message. Veuillez réessayer.', 'eventlist') ?></p>
					<p class="error-require"><i class="fas fa-exclamation-triangle"></i> <?php esc_html_e('Veuillez remplir tous les champs requis', 'eventlist') ?></p>
					<p class="recapcha-vetify"><i class="fas fa-shield-alt"></i> <?php esc_html_e('La vérification CAPTCHA a échoué. Veuillez réessayer.', 'eventlist') ?></p>
				</div>
			</div>
		</div>
	</div>

<?php

	}

?>
