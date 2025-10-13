<?php
/**
 * V1 Le Hiboo - Filters and Hooks
 *
 * Filtres et hooks personnalisés pour l'espace partenaire V1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendre la thématique obligatoire dans le formulaire de création d'événement
 */
add_filter( 'el_custom_taxonomy_required', 'el_v1_lehiboo_required_taxonomies' );
function el_v1_lehiboo_required_taxonomies( $required_taxonomies ) {
	$required_taxonomies[] = 'event_thematique';
	return $required_taxonomies;
}

/**
 * Améliorer les labels des nouvelles taxonomies
 */
add_filter( 'el_taxonomy_labels', 'el_v1_lehiboo_taxonomy_labels', 10, 2 );
function el_v1_lehiboo_taxonomy_labels( $labels, $taxonomy_slug ) {

	switch ( $taxonomy_slug ) {
		case 'event_thematique':
			$labels['placeholder'] = __( 'Sélectionnez une thématique principale', 'eventlist' );
			$labels['description'] = __( 'Choisissez LA thématique principale de votre événement', 'eventlist' );
			break;

		case 'event_saison':
			$labels['placeholder'] = __( 'Sélectionnez une saison', 'eventlist' );
			$labels['description'] = __( 'Période de l\'année où se déroule l\'événement', 'eventlist' );
			break;

		case 'event_special':
			$labels['placeholder'] = __( 'Sélectionnez un ou plusieurs événements spéciaux', 'eventlist' );
			$labels['description'] = __( 'Associez votre événement à des dates spéciales (optionnel)', 'eventlist' );
			break;
	}

	return $labels;
}

/**
 * Afficher les taxonomies V1 sur la page single event
 */
add_action( 'el_single_event_address', 'el_v1_lehiboo_display_taxonomies', 15 );
function el_v1_lehiboo_display_taxonomies() {
	if ( ! is_singular( 'event' ) ) {
		return;
	}

	$post_id = get_the_ID();
	$taxonomies_to_display = array(
		'event_thematique' => array(
			'label' => __( 'Thématique', 'eventlist' ),
			'icon'  => 'fas fa-palette',
			'color' => '#FF6B35'
		),
		'event_saison' => array(
			'label' => __( 'Saison', 'eventlist' ),
			'icon'  => 'fas fa-calendar-alt',
			'color' => '#4ECDC4'
		),
		'event_special' => array(
			'label' => __( 'Événements spéciaux', 'eventlist' ),
			'icon'  => 'fas fa-star',
			'color' => '#FFD93D'
		),
	);

	echo '<div class="event_v1_taxonomies">';

	foreach ( $taxonomies_to_display as $taxonomy_slug => $taxonomy_data ) {
		$terms = get_the_terms( $post_id, $taxonomy_slug );

		if ( $terms && ! is_wp_error( $terms ) ) {
			echo '<div class="event_taxonomy_item event_taxonomy_' . esc_attr( $taxonomy_slug ) . '">';
			echo '<span class="taxonomy_label">';
			echo '<i class="' . esc_attr( $taxonomy_data['icon'] ) . '"></i> ';
			echo esc_html( $taxonomy_data['label'] ) . ' : ';
			echo '</span>';
			echo '<span class="taxonomy_terms">';

			$term_names = array();
			foreach ( $terms as $term ) {
				$term_link = get_term_link( $term );
				if ( ! is_wp_error( $term_link ) ) {
					$term_names[] = '<a href="' . esc_url( $term_link ) . '">' . esc_html( $term->name ) . '</a>';
				} else {
					$term_names[] = esc_html( $term->name );
				}
			}
			echo implode( ', ', $term_names );

			echo '</span>';
			echo '</div>';
		}
	}

	echo '</div>';
}
