<?php
/**
 * Template Override: Single Event - Airbnb Experience Style
 *
 * Override du template principal pour afficher les événements
 * dans un style inspiré d'Airbnb Experiences avec layout 2 colonnes.
 *
 * @package LeHiboo
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;
$event_id = get_the_ID();
?>

<!-- AIRBNB TEMPLATE LOADED v3.3.2 - ORGANIZER CARD OPTIMIZED -->
<article id="event_<?php the_ID(); ?>" <?php post_class( 'event_single event_single_airbnb' ); ?>>

	<?php if ( ! post_password_required( $event_id ) ): ?>

		<!-- Fil d'Ariane Airbnb Style -->
		<div class="event_breadcrumb_airbnb">
			<?php
			// Breadcrumb custom style Airbnb
			$categories = get_the_terms( $event_id, 'event_cat' );
			$category_link = '';
			$category_name = '';

			if ( $categories && !is_wp_error($categories) ) {
				$first_cat = array_shift($categories);
				$category_link = get_term_link($first_cat);
				$category_name = $first_cat->name;
			}
			?>
			<nav class="breadcrumb_nav" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url('/') ); ?>" class="breadcrumb_home">Accueil</a>
				<span class="breadcrumb_separator">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link('event') ); ?>" class="breadcrumb_events">Événements</a>
				<?php if ( $category_name ) : ?>
					<span class="breadcrumb_separator">›</span>
					<a href="<?php echo esc_url( $category_link ); ?>" class="breadcrumb_category"><?php echo esc_html( $category_name ); ?></a>
				<?php endif; ?>
			</nav>
		</div>

		<!-- En-tête de l'événement -->
		<div class="event_header_airbnb">

			<!-- Date avec pastille calendrier -->
			<div class="event_date_badge">
				<?php el_get_template( 'single/date.php' ); ?>
			</div>

			<!-- Titre -->
			<div class="event_title_wrapper">
				<?php do_action( 'el_single_event_title' ); ?>
			</div>

			<!-- Méta ligne: Ville • Catégorie • Durée • Langues • Note -->
			<div class="event_meta_line">
				<?php el_get_template( 'single/meta-line.php' ); ?>
			</div>

			<!-- Actions: Partager | Enregistrer -->
			<div class="event_actions">
				<?php do_action( 'el_single_share_social' ); ?>
				<?php do_action( 'el_single_report' ); ?>
				<?php do_action( 'el_single_calenda_export' ); ?>
			</div>

		</div>

		<!-- Layout 2 colonnes: Galerie + Réservation -->
		<div class="event_gallery_booking_section">

			<!-- Galerie mosaïque (1 large + 4 mini) -->
			<div class="event_gallery_mosaic">
				<?php el_get_template( 'single/gallery-mosaic.php' ); ?>
			</div>
			<!-- Bloc Organisateur -->
			<div class="event_organizer_wrapper">
				<?php el_get_template( 'author_info.php' ); ?>
			</div>

		</div>

		<!-- Contenu principal (2 colonnes sur desktop) -->
		<div class="event_main_grid">

			<!-- Colonne de gauche: Contenu -->
			<div class="event_content_column">

				<!-- Description de l'activité -->
				<section class="event_section event_description_modern">
					<h2 class="event_section_title">À propos de cette activité</h2>
					<div class="event_section_divider"></div>
					<div class="event_description_content">
						<?php do_action( 'el_single_event_content' ); ?>
					</div>
				</section>

				<!-- Points forts / Highlights -->
				<section class="event_section event_highlights_modern">
					<h2 class="event_section_title">Points forts de l'expérience</h2>
					<div class="event_section_divider"></div>
					<?php el_get_template( 'single/highlights.php' ); ?>
				</section>

				<!-- Ce qui est inclus / Non inclus -->
				<section class="event_section event_includes_modern">
					<h2 class="event_section_title">Ce qui est inclus</h2>
					<div class="event_section_divider"></div>
					<?php el_get_template( 'single/includes.php' ); ?>
				</section>

				<!-- Exigences et informations importantes -->
				<section class="event_section event_requirements_modern">
					<h2 class="event_section_title">
						<svg class="section_icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="8" x2="12" y2="12"></line>
							<line x1="12" y1="16" x2="12.01" y2="16"></line>
						</svg>
						Informations importantes
					</h2>
					<div class="event_section_divider"></div>
					<?php el_get_template( 'single/requirements.php' ); ?>
				</section>

				<!-- Point de rendez-vous -->
				<section class="event_section event_meeting_modern">
					<h2 class="event_section_title">
						<svg class="section_icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
							<circle cx="12" cy="10" r="3"></circle>
						</svg>
						Point de rendez-vous
					</h2>
					<div class="event_section_divider"></div>
					<?php el_get_template( 'single/meeting-point.php' ); ?>
				</section>

				<!-- Carte interactive -->
				<section class="event_section event_map_modern">
					<h2 class="event_section_title">Où nous trouver</h2>
					<div class="event_section_divider"></div>
					<div class="event_map_wrapper">
						<?php do_action( 'el_single_event_map' ); ?>
					</div>
				</section>

				<!-- Avis clients -->
				<?php if( is_singular('event') && comments_open( $event_id ) ) { ?>
					<section class="event_section event_reviews_modern">
						<h2 class="event_section_title">
							<svg class="section_icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
							</svg>
							Avis des participants
						</h2>
						<div class="event_section_divider"></div>
						<div class="event_reviews_content">
							<?php do_action( 'el_single_event_comment' ); ?>
						</div>
					</section>
				<?php } ?>

				<!-- FAQ -->
				<section class="event_section event_faq_modern">
					<h2 class="event_section_title">Questions fréquentes</h2>
					<div class="event_section_divider"></div>
					<?php el_get_template( 'single/faq.php' ); ?>
				</section>

			</div><!-- .event_content_column -->

			<!-- Colonne de droite: Sidebar -->
			<div class="event_sidebar_column">

				<!-- Widget Réservation Sticky -->
				<div class="event_booking_sticky_wrapper">
					<?php el_get_template( 'single/booking-sticky.php' ); ?>
				</div>

				<!-- Taxonomies & Tags -->
				<div class="event_sidebar_taxonomies">
					<?php do_action( 'el_single_event_tag' ); ?>
					<?php do_action( 'el_single_event_taxonomy' ); ?>
				</div>

				<!-- Politique d'annulation -->
				<div class="event_sidebar_policy">
					<?php do_action( 'el_single_event_policy' ); ?>
				</div>

				<!-- Sidebar WordPress Widget Area -->
				<?php if(is_active_sidebar('single-event-sidebar')){ ?>
					<aside id="event-sidebar" class="event_sidebar">
						<div class="content-sidebar">
							<?php dynamic_sidebar('single-event-sidebar'); ?>
						</div>
					</aside>
				<?php } ?>

			</div><!-- .event_sidebar_column -->

		</div><!-- .event_main_grid -->

		<!-- Activités déjà vues (Carousel) -->
		<?php el_get_template( 'single/viewed-activities.php' ); ?>

		<!-- Autres activités de l'organisateur (Carousel) -->
		<?php el_get_template( 'single/organizer-activities.php' ); ?>

		<!-- Événements liés (full width) -->
		<div class="event_related_section">
			<?php do_action('el_single_event_related'); ?>
		</div>

		<!-- CTA Mobile Flottant (Mobile uniquement) -->
		<div class="event_mobile_cta_wrapper">
			<?php el_get_template( 'single/booking-mobile-cta.php' ); ?>
		</div>

		<!-- Popup Formulaire de Contact Organisateur -->
		<?php
		global $post;
		$event_id = $post->ID;
		$author_id = $post->post_author;
		$organizer_email = get_the_author_meta('user_email', $author_id);
		$organizer_name = get_the_author_meta('display_name', $author_id);

		// Debug
		error_log('Contact Form - Event ID: ' . $event_id);
		error_log('Contact Form - Author ID: ' . $author_id);
		error_log('Contact Form - Organizer Email: ' . $organizer_email);
		?>
		<div id="contact_organizer_popup" class="contact_popup_overlay" style="display:none;">
			<div class="contact_popup_container">
				<div class="contact_popup_header">
					<h3 class="contact_popup_title">
						<?php esc_html_e( 'Contacter l\'organisateur', 'eventlist' ); ?>
					</h3>
					<button type="button" class="contact_popup_close" aria-label="<?php esc_attr_e( 'Fermer', 'eventlist' ); ?>">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="contact_popup_body">
					<form id="contact_organizer_form" class="contact_form" method="post">
						<div class="form_field">
							<label for="contact_name"><?php esc_html_e( 'Votre nom', 'eventlist' ); ?> *</label>
							<input type="text" id="contact_name" name="contact_name" required>
						</div>
						<div class="form_field">
							<label for="contact_email"><?php esc_html_e( 'Votre email', 'eventlist' ); ?> *</label>
							<input type="email" id="contact_email" name="contact_email" required>
						</div>
						<div class="form_field">
							<label for="contact_subject"><?php esc_html_e( 'Objet de la demande', 'eventlist' ); ?> *</label>
							<input type="text" id="contact_subject" name="contact_subject" required>
						</div>
						<div class="form_field">
							<label for="contact_message"><?php esc_html_e( 'Message', 'eventlist' ); ?> *</label>
							<textarea id="contact_message" name="contact_message" rows="6" required></textarea>
						</div>

						<!-- Cloudflare Turnstile CAPTCHA -->
						<div class="form_field">
							<div class="cf-turnstile" data-sitekey="0x4AAAAAAB75T9T-6xfs5mqd" data-theme="light"></div>
						</div>

						<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
						<input type="hidden" name="action" value="send_organizer_message">
						<?php wp_nonce_field( 'contact_organizer_nonce', 'contact_nonce' ); ?>

						<div class="form_actions">
							<button type="submit" class="contact_submit_btn">
								<?php esc_html_e( 'Envoyer', 'eventlist' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	<?php endif; ?>

</article>
