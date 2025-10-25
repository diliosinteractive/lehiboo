<?php
/**
 * Template Part: Activités déjà vues
 *
 * Affiche un carousel des activités vues par l'utilisateur (localStorage)
 * Chargé via AJAX pour récupérer les données
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$current_event_id = get_the_ID();
?>

<section class="event_section crosssell_section viewed_activities_section">
	<div class="crosssell_header">
		<h2 class="crosssell_title">
			<svg class="title_icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
				<circle cx="12" cy="12" r="3"></circle>
			</svg>
			<?php esc_html_e( 'Activités que vous avez consultées', 'eventlist' ); ?>
		</h2>
	</div>

	<div class="crosssell_carousel_wrapper">
		<button class="carousel_nav carousel_prev" aria-label="<?php esc_attr_e( 'Précédent', 'eventlist' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<polyline points="15 18 9 12 15 6"></polyline>
			</svg>
		</button>

		<div class="crosssell_carousel viewed_carousel" data-current-event="<?php echo esc_attr( $current_event_id ); ?>">
			<!-- Les cards seront chargées via JavaScript -->
			<div class="carousel_loading">
				<div class="loading_spinner"></div>
				<p><?php esc_html_e( 'Chargement...', 'eventlist' ); ?></p>
			</div>
		</div>

		<button class="carousel_nav carousel_next" aria-label="<?php esc_attr_e( 'Suivant', 'eventlist' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<polyline points="9 18 15 12 9 6"></polyline>
			</svg>
		</button>
	</div>
</section>
