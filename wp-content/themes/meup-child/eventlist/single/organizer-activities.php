<?php
/**
 * Template Part: Autres activités de l'organisateur
 *
 * Affiche un carousel des autres activités du même organisateur
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$current_event_id = get_the_ID();
$author_id = get_post_field( 'post_author', $current_event_id );

// Nom public de l'organisateur (priorité: org_display_name > org_name > display_name)
$org_display_name = get_user_meta( $author_id, 'org_display_name', true );
$org_name = get_user_meta( $author_id, 'org_name', true );
$org_public_name = ! empty( $org_display_name ) ? $org_display_name : $org_name;
$author_name = $org_public_name ? $org_public_name : get_the_author_meta( 'display_name', $author_id );

// Récupérer les autres activités de l'organisateur
$args = array(
	'post_type' => 'event',
	'author' => $author_id,
	'post__not_in' => array( $current_event_id ),
	'posts_per_page' => 12,
	'post_status' => 'publish',
	'orderby' => 'date',
	'order' => 'DESC'
);

$organizer_events = new WP_Query( $args );

// Si pas d'autres activités, ne rien afficher
if ( ! $organizer_events->have_posts() ) {
	return;
}
?>

<section class="event_section crosssell_section organizer_activities_section">
	<div class="crosssell_header">
		<h2 class="crosssell_title">
			<svg class="title_icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
				<circle cx="12" cy="7" r="4"></circle>
			</svg>
			<?php printf( esc_html__( 'Autres activités de %s', 'eventlist' ), '<span class="organizer_name">' . esc_html( $author_name ) . '</span>' ); ?>
		</h2>
		<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="crosssell_view_all">
			<?php esc_html_e( 'Voir tout', 'eventlist' ); ?>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<polyline points="9 18 15 12 9 6"></polyline>
			</svg>
		</a>
	</div>

	<div class="crosssell_carousel_wrapper">
		<button class="carousel_nav carousel_prev" aria-label="<?php esc_attr_e( 'Précédent', 'eventlist' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<polyline points="15 18 9 12 15 6"></polyline>
			</svg>
		</button>

		<div class="crosssell_carousel organizer_carousel">
			<?php while ( $organizer_events->have_posts() ) : $organizer_events->the_post();
				$event_id = get_the_ID();
				$thumbnail = get_the_post_thumbnail_url( $event_id, 'medium' );
				$placeholder = get_stylesheet_directory_uri() . '/assets/images/event-placeholder.jpg';
				$event_url = get_permalink( $event_id );

				// Prix
				$list_type_ticket = get_post_meta( $event_id, OVA_METABOX_EVENT . 'ticket', true );
				$ticket_price = 0;
				$currency = el_get_currency_symbol();

				if( !empty($list_type_ticket) && is_array($list_type_ticket) ) {
					$first_ticket = $list_type_ticket[0];
					$ticket_price = isset($first_ticket['ticket_price']) ? floatval($first_ticket['ticket_price']) : 0;
				}

				// Ville
				$venue_terms = get_the_terms( $event_id, 'event_city' );
				$city = '';
				if ( $venue_terms && !is_wp_error($venue_terms) ) {
					$city = $venue_terms[0]->name;
				}

				// Note moyenne
				$comments_count = get_comments_number( $event_id );
				$rating = 5.0; // TODO: calculer la vraie note
			?>
				<div class="crosssell_card">
					<a href="<?php echo esc_url( $event_url ); ?>" class="card_link">
						<div class="card_image">
							<img src="<?php echo esc_url( $thumbnail ? $thumbnail : $placeholder ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>"
							     loading="lazy">
						</div>

						<div class="card_content">
							<?php if( $city ) : ?>
								<div class="card_location">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
										<circle cx="12" cy="10" r="3"></circle>
									</svg>
									<?php echo esc_html( $city ); ?>
								</div>
							<?php endif; ?>

							<h3 class="card_title"><?php echo esc_html( get_the_title() ); ?></h3>

							<?php if( $comments_count > 0 ) : ?>
								<div class="card_rating">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="#FFB400" stroke="#FFB400" stroke-width="2">
										<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
									</svg>
									<span class="rating_value"><?php echo esc_html( number_format($rating, 1) ); ?></span>
									<span class="rating_count">(<?php echo esc_html( $comments_count ); ?>)</span>
								</div>
							<?php endif; ?>

							<div class="card_price">
								<?php if( $ticket_price > 0 ) : ?>
									<span class="price_label"><?php esc_html_e( 'À partir de', 'eventlist' ); ?></span>
									<span class="price_amount"><?php echo esc_html( $currency . number_format($ticket_price, 0) ); ?></span>
									<span class="price_unit"><?php esc_html_e( '/ personne', 'eventlist' ); ?></span>
								<?php else : ?>
									<span class="price_free"><?php esc_html_e( 'Gratuit', 'eventlist' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</a>
				</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<button class="carousel_nav carousel_next" aria-label="<?php esc_attr_e( 'Suivant', 'eventlist' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<polyline points="9 18 15 12 9 6"></polyline>
			</svg>
		</button>
	</div>
</section>
