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
