<?php
/**
 * Template Override: Author Info Card
 *
 * Version optimisée pour Single Event, version classique pour page Author.
 *
 * @package LeHiboo
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit();

$author_id = get_query_var( 'author' );
$eid = get_the_ID();

if( is_singular( 'event' ) ){
	$author_id = get_the_author_meta('ID');
}

// Si on est sur la page author, charger notre template personnalisé pour la sidebar
if( is_author() ) {
	// Inclure notre template sidebar avec boutons révélation
	include( get_stylesheet_directory() . '/eventlist/author_info_sidebar.php' );
	return;
}

// Sinon, afficher le bloc optimisé pour single event
if( $author_id ){

	$author_data = get_userdata( $author_id );

	// Avatar
	$author_id_image = get_user_meta( $author_id, 'author_id_image', true ) ? get_user_meta( $author_id, 'author_id_image', true ) : '';
	if ( $author_id_image ) {
		$img_path = wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : wp_get_attachment_image_url($author_id_image, 'full');
	} else {
		$img_path = get_avatar_url($author_id);
	}

	// Données utilisateur
	$display_name = get_user_meta( $author_id, 'display_name', true ) ? get_user_meta( $author_id, 'display_name', true ) : get_the_author_meta( 'display_name', $author_id );
	$user_description = get_user_meta( $author_id, 'description', true ) ? get_user_meta( $author_id, 'description', true ) : '';
	$user_phone = get_user_meta( $author_id, 'user_phone', true ) ? get_user_meta( $author_id, 'user_phone', true ) : '';
	$user_email = get_user_meta( $author_id, 'user_email', true ) ? get_user_meta( $author_id, 'user_email', true ) : get_the_author_meta( 'user_email', $author_id );
	$user_professional_email = get_user_meta( $author_id, 'user_professional_email', true ) ? get_user_meta( $author_id, 'user_professional_email', true ) : '';
	$user_profile_social = get_user_meta( $author_id, 'user_profile_social', true ) ? get_user_meta( $author_id, 'user_profile_social', true ) : '';

	// Organisation
	$org_name = get_user_meta( $author_id, 'org_name', true ) ? get_user_meta( $author_id, 'org_name', true ) : '';
	$org_display_name = get_user_meta( $author_id, 'org_display_name', true );
	$org_public_name = ! empty( $org_display_name ) ? $org_display_name : $org_name;
	$org_web = get_user_meta( $author_id, 'org_web', true ) ? get_user_meta( $author_id, 'org_web', true ) : '';
	$org_cover_image = get_user_meta( $author_id, 'org_cover_image', true ) ? get_user_meta( $author_id, 'org_cover_image', true ) : '';

	// Localisation
	$user_city = get_user_meta( $author_id, 'user_city', true ) ? get_user_meta( $author_id, 'user_city', true ) : '';
	$user_country = get_user_meta( $author_id, 'user_country', true ) ? get_user_meta( $author_id, 'user_country', true ) : '';
	$user_postcode = get_user_meta( $author_id, 'user_postcode', true ) ? get_user_meta( $author_id, 'user_postcode', true ) : '';
	$user_address = get_user_meta( $author_id, 'user_address', true ) ? get_user_meta( $author_id, 'user_address', true ) : '';

	// Infos pratiques organisateur
	$org_event_type = get_user_meta( $author_id, 'org_event_type', true );
	$org_stationnement = get_user_meta( $author_id, 'org_stationnement', true );
	$org_pmr = get_user_meta( $author_id, 'org_pmr', true );
	$org_restauration = get_user_meta( $author_id, 'org_restauration', true );
	$org_boisson = get_user_meta( $author_id, 'org_boisson', true );
	$org_pmr_infos = get_user_meta( $author_id, 'org_pmr_infos', true );
	$org_restauration_infos = get_user_meta( $author_id, 'org_restauration_infos', true );
	$org_boisson_infos = get_user_meta( $author_id, 'org_boisson_infos', true );

	// Info organisateur custom de l'event
	$info_organizer = get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'info_organizer', true ) : '';
	$name_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'name_organizer', true ) : '' );
	$phone_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'phone_organizer', true ) : '' );
	$mail_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'mail_organizer', true ) : '' );
	$social_organizer = ( get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'social_organizer', true ) : array() );

	// Tronquer la description pour l'aperçu (2 lignes max, environ 25 mots)
	$short_description = '';
	if ( $user_description ) {
		$short_description = wp_trim_words( $user_description, 25, '...' );
	}

	// Lien vers la page organisateur
	$organizer_page_url = get_author_posts_url( $author_id );

	// Construire l'adresse complète pour l'affichage
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

	// Contexte tracking pour page single event
	$tracking_context = 'single_event_card';
	$tracking_context_popup = 'single_event_popup';

	?>

	<!-- Bloc Organisateur Optimisé UX -->
	<div class="organizer_card_optimized event_section_white">

		<!-- En-tête avec avatar et nom -->
		<div class="organizer_header">
			<div class="organizer_avatar">
				<img src="<?php echo esc_url( $img_path ); ?>" alt="<?php echo esc_attr( $org_public_name ? $org_public_name : $display_name ); ?>">
			</div>
			<div class="organizer_identity">
				<h3 class="organizer_name">
					<?php echo esc_html( $org_public_name ? $org_public_name : $display_name ); ?>
				</h3>
				<?php if ( $user_city || $user_country ) : ?>
					<p class="organizer_location">
						<i class="icon_pin_alt"></i>
						<?php
						$location_parts = array();
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
						echo esc_html( implode( ', ', $location_parts ) );
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Description courte -->
		<?php if ( $short_description ) : ?>
			<div class="organizer_description">
				<p><?php echo esc_html( $short_description ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Informations essentielles -->
		<div class="organizer_quick_info">

			<!-- Téléphone avec bouton révélation -->
			<?php if( apply_filters( 'el_show_phone_info', true ) ){ ?>
				<?php
				// Déterm iner le numéro de téléphone à afficher
				$display_phone = '';
				if (is_singular('event') && $info_organizer == 'checked' && $phone_organizer) {
					$display_phone = $phone_organizer;
				} elseif ( $user_phone ) {
					$display_phone = $user_phone;
				}
				?>
				<?php if( $display_phone ){ ?>
					<div class="quick_info_item contact_reveal_container">
						<i class="icon_phone"></i>
						<div class="contact_reveal_content">
							<span class="contact_label"><?php esc_html_e( 'Téléphone', 'eventlist' ); ?></span>
							<button class="btn_reveal_phone btn_reveal_contact"
								data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
								data-event-id="<?php echo esc_attr( $eid ); ?>"
								data-context="<?php echo esc_attr( $tracking_context ); ?>"
								data-phone="<?php echo esc_attr( $display_phone ); ?>">
								<i class="fas fa-eye"></i>
								<span><?php esc_html_e( 'Voir le numéro', 'eventlist' ); ?></span>
							</button>
							<div class="contact_hidden_value"></div>
						</div>
					</div>
				<?php } ?>
			<?php } ?>

			<!-- Adresse avec bouton révélation -->
			<?php if( apply_filters( 'el_show_address_info', true ) && $full_address ){ ?>
				<div class="quick_info_item contact_reveal_container">
					<i class="icon_pin_alt"></i>
					<div class="contact_reveal_content">
						<span class="contact_label"><?php esc_html_e( 'Adresse', 'eventlist' ); ?></span>
						<button class="btn_reveal_address btn_reveal_contact"
							data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
							data-event-id="<?php echo esc_attr( $eid ); ?>"
							data-context="<?php echo esc_attr( $tracking_context ); ?>"
							data-address="<?php echo esc_attr( $full_address ); ?>">
							<i class="fas fa-map-marker-alt"></i>
							<span><?php esc_html_e( 'Voir l\'adresse', 'eventlist' ); ?></span>
						</button>
						<div class="contact_hidden_value"></div>
					</div>
				</div>
			<?php } ?>

		</div>

		<!-- Infos pratiques organisateur -->
		<?php if ( ($org_pmr && $org_pmr === 'oui') || ($org_restauration && $org_restauration === 'oui') || ($org_boisson && $org_boisson === 'oui') || $org_stationnement || $org_event_type ) : ?>
			<div class="organizer_practical_info">
				<?php if ( $org_pmr && $org_pmr === 'oui' ) : ?>
					<div class="practical_badge" data-tooltip="<?php echo esc_attr( $org_pmr_infos ? $org_pmr_infos : __( 'Établissement accessible aux personnes à mobilité réduite', 'eventlist' ) ); ?>">
						<i class="fas fa-wheelchair"></i>
						<span><?php esc_html_e( 'PMR', 'eventlist' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $org_restauration && $org_restauration === 'oui' ) : ?>
					<div class="practical_badge" data-tooltip="<?php echo esc_attr( $org_restauration_infos ? $org_restauration_infos : __( 'Restauration disponible sur place', 'eventlist' ) ); ?>">
						<i class="fas fa-utensils"></i>
						<span><?php esc_html_e( 'Restauration', 'eventlist' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $org_boisson && $org_boisson === 'oui' ) : ?>
					<div class="practical_badge" data-tooltip="<?php echo esc_attr( $org_boisson_infos ? $org_boisson_infos : __( 'Boissons disponibles sur place', 'eventlist' ) ); ?>">
						<i class="fas fa-glass-martini-alt"></i>
						<span><?php esc_html_e( 'Boissons', 'eventlist' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $org_stationnement ) : ?>
					<div class="practical_badge" data-tooltip="<?php echo esc_attr( $org_stationnement ); ?>">
						<i class="fas fa-parking"></i>
						<span><?php esc_html_e( 'Parking', 'eventlist' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $org_event_type ) : ?>
					<?php if ( $org_event_type === 'interieur' ) : ?>
						<div class="practical_badge" data-tooltip="<?php esc_attr_e( 'Événements en intérieur', 'eventlist' ); ?>">
							<i class="fas fa-door-open"></i>
							<span><?php esc_html_e( 'Intérieur', 'eventlist' ); ?></span>
						</div>
					<?php elseif ( $org_event_type === 'exterieur' ) : ?>
						<div class="practical_badge" data-tooltip="<?php esc_attr_e( 'Événements en extérieur', 'eventlist' ); ?>">
							<i class="fas fa-sun"></i>
							<span><?php esc_html_e( 'Extérieur', 'eventlist' ); ?></span>
						</div>
					<?php elseif ( $org_event_type === 'mix' ) : ?>
						<div class="practical_badge" data-tooltip="<?php esc_attr_e( 'Événements en intérieur et extérieur', 'eventlist' ); ?>">
							<i class="fas fa-random"></i>
							<span><?php esc_html_e( 'Mixte', 'eventlist' ); ?></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Boutons côte à côte: En savoir plus + Contacter -->
		<div class="organizer_actions_row">
			<button class="btn_learn_more_half" id="open_organizer_details_popup" data-author-id="<?php echo esc_attr( $author_id ); ?>">
				<i class="fas fa-info-circle"></i>
				<span><?php esc_html_e( 'En savoir plus', 'eventlist' ); ?></span>
			</button>

			<?php if( apply_filters( 'el_single_event_show_send_message_btn', true ) ){ ?>
				<button class="btn_contact_half" id="open_contact_modal" data-require-login="<?php echo is_user_logged_in() ? 'false' : 'true'; ?>">
					<i class="icon_mail_alt"></i>
					<span><?php esc_html_e( 'Contacter', 'eventlist' ); ?></span>
				</button>
			<?php } ?>
		</div>

	</div><!-- .organizer_card_optimized -->

	<!-- Popup Détails Organisateur -->
	<div id="organizer_details_popup" class="organizer_popup_modal" style="display: none;">
		<div class="organizer_popup_overlay"></div>
		<div class="organizer_popup_container">

			<!-- Header -->
			<div class="organizer_popup_header">
				<?php if ( $org_cover_image ) : ?>
					<div class="popup_cover_image">
						<img src="<?php echo esc_url( wp_get_attachment_image_url($org_cover_image, 'large') ); ?>" alt="<?php echo esc_attr( $org_public_name ? $org_public_name : $display_name ); ?>">
						<div class="cover_overlay"></div>
					</div>
				<?php endif; ?>

				<div class="popup_header_content">
					<div class="popup_avatar">
						<img src="<?php echo esc_url( $img_path ); ?>" alt="<?php echo esc_attr( $org_public_name ? $org_public_name : $display_name ); ?>">
					</div>
					<div class="popup_identity">
						<h3><?php echo esc_html( $org_public_name ? $org_public_name : $display_name ); ?></h3>
						<?php if ( $user_city || $user_country ) : ?>
							<p class="popup_location">
								<i class="icon_pin_alt"></i>
								<?php echo esc_html( implode( ', ', $location_parts ) ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<button class="organizer_popup_close" aria-label="<?php esc_attr_e('Fermer', 'eventlist'); ?>">
					<i class="fas fa-times"></i>
				</button>
			</div>

			<!-- Body -->
			<div class="organizer_popup_body">

				<!-- Description complète -->
				<?php if ( $user_description ) : ?>
					<div class="popup_section popup_description">
						<h4 class="popup_section_title">
							<i class="fas fa-info-circle"></i>
							<?php esc_html_e( 'À propos', 'eventlist' ); ?>
						</h4>
						<div class="popup_section_content">
							<?php echo wpautop( wp_kses_post( $user_description ) ); ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Informations de contact -->
				<div class="popup_section popup_contact">
					<h4 class="popup_section_title">
						<i class="fas fa-address-card"></i>
						<?php esc_html_e( 'Contact', 'eventlist' ); ?>
					</h4>
					<div class="popup_contact_list">

						<!-- Téléphone avec bouton révélation -->
						<?php if( apply_filters( 'el_show_phone_info', true ) && $display_phone ){ ?>
							<div class="popup_contact_item contact_reveal_container">
								<i class="icon_phone"></i>
								<div class="contact_reveal_content">
									<span class="contact_label"><?php esc_html_e( 'Téléphone', 'eventlist' ); ?></span>
									<button class="btn_reveal_phone btn_reveal_contact"
										data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
										data-event-id="<?php echo esc_attr( $eid ); ?>"
										data-context="<?php echo esc_attr( $tracking_context_popup ); ?>"
										data-phone="<?php echo esc_attr( $display_phone ); ?>">
										<i class="fas fa-eye"></i>
										<span><?php esc_html_e( 'Voir le numéro', 'eventlist' ); ?></span>
									</button>
									<div class="contact_hidden_value"></div>
								</div>
							</div>
						<?php } ?>

						<?php if( apply_filters( 'el_show_mail_info', true ) ){ ?>
							<div class="popup_contact_item">
								<i class="icon_mail"></i>
								<div>
									<span class="contact_label"><?php esc_html_e( 'Email', 'eventlist' ); ?></span>
									<?php if (is_singular('event') && $info_organizer == 'checked') { ?>
										<a href="<?php echo esc_attr('mailto:'.$mail_organizer); ?>"><?php echo esc_html( $mail_organizer ); ?></a>
									<?php } elseif ( !is_singular('event') && $user_professional_email ) { ?>
										<a href="<?php echo esc_attr('mailto:'.$user_professional_email); ?>"><?php echo esc_html( $user_professional_email ); ?></a>
									<?php } else { ?>
										<a href="<?php echo esc_attr('mailto:'.$user_email); ?>"><?php echo esc_html( $user_email ); ?></a>
									<?php } ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( apply_filters( 'el_show_website_info', true ) ): ?>
							<?php if ( !is_singular('event') && $org_web ) : ?>
								<div class="popup_contact_item">
									<i class="fas fa-link"></i>
									<div>
										<span class="contact_label"><?php esc_html_e( 'Site web', 'eventlist' ); ?></span>
										<a href="<?php echo esc_url( $org_web ); ?>" rel="nofollow" target="_blank"><?php echo esc_html( $org_web ); ?></a>
									</div>
								</div>
							<?php elseif ( $author_data->user_url ): ?>
								<div class="popup_contact_item">
									<i class="fas fa-link"></i>
									<div>
										<span class="contact_label"><?php esc_html_e( 'Site web', 'eventlist' ); ?></span>
										<a href="<?php echo esc_url( $author_data->user_url ); ?>" rel="nofollow" target="_blank"><?php echo esc_html( $author_data->user_url ); ?></a>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<!-- Adresse avec bouton révélation -->
						<?php if( apply_filters( 'el_show_address_info', true ) && $full_address ){ ?>
							<div class="popup_contact_item contact_reveal_container">
								<i class="icon_pin_alt"></i>
								<div class="contact_reveal_content">
									<span class="contact_label"><?php esc_html_e( 'Adresse', 'eventlist' ); ?></span>
									<button class="btn_reveal_address btn_reveal_contact"
										data-organizer-id="<?php echo esc_attr( $author_id ); ?>"
										data-event-id="<?php echo esc_attr( $eid ); ?>"
										data-context="<?php echo esc_attr( $tracking_context_popup ); ?>"
										data-address="<?php echo esc_attr( $full_address ); ?>">
										<i class="fas fa-map-marker-alt"></i>
										<span><?php esc_html_e( 'Voir l\'adresse', 'eventlist' ); ?></span>
									</button>
									<div class="contact_hidden_value"></div>
								</div>
							</div>
						<?php } ?>

					</div>
				</div>

				<!-- Réseaux sociaux -->
				<?php
				$has_social = false;
				if ( is_singular('event') ) {
					$has_social = ( $social_organizer && $info_organizer == 'checked' ) || ( $user_profile_social && $info_organizer == '' );
				} elseif ( !is_singular('event') && $user_profile_social ) {
					$has_social = true;
				}
				?>

				<?php if ( $has_social ) : ?>
					<div class="popup_section popup_social">
						<h4 class="popup_section_title">
							<i class="fas fa-share-nodes"></i>
							<?php esc_html_e( 'Réseaux sociaux', 'eventlist' ); ?>
						</h4>
						<div class="popup_social_links">
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

				<!-- CTA vers page organisateur -->
				<div class="popup_footer_cta">
					<a href="<?php echo esc_url( $organizer_page_url ); ?>" class="btn_view_full_profile">
						<i class="fas fa-user-circle"></i>
						<?php esc_html_e( 'Voir la page organisateur', 'eventlist' ); ?>
					</a>
				</div>

			</div><!-- .organizer_popup_body -->

		</div><!-- .organizer_popup_container -->
	</div><!-- #organizer_details_popup -->

	<!-- Modal Popup pour Contact Form (existant - gardé tel quel) -->
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

<?php } ?>
