<?php
/**
 * Template Part: Meta Line
 *
 * Affiche la ligne de métadonnées: Ville • Catégorie • Durée • Langues • Note
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();
$meta_items = array();

// Ville / Adresse
$address = get_post_meta( $event_id, OVA_METABOX_EVENT . 'address', true );
$venue = get_post_meta( $event_id, OVA_METABOX_EVENT . 'venue', true );
$event_type = get_post_meta( $event_id, OVA_METABOX_EVENT . 'event_type', true );

if( $event_type == 'online' ) {
	$meta_items[] = '<span class="meta_item meta_location"><i class="icon_pin_alt"></i>' . esc_html__( 'En ligne', 'eventlist' ) . '</span>';
} else {
	if ( !empty($venue) ) {
		if (is_array($venue)) {
			$venue = implode(', ', $venue);
		}
		$meta_items[] = '<span class="meta_item meta_location"><i class="icon_pin_alt"></i>' . esc_html( stripslashes_deep($venue) ) . '</span>';
	} elseif ( !empty($address) ) {
		// Extraire juste la ville de l'adresse
		$address_parts = explode(',', $address);
		$city = trim(end($address_parts));
		$meta_items[] = '<span class="meta_item meta_location"><i class="icon_pin_alt"></i>' . esc_html( $city ) . '</span>';
	}
}

// Catégories
$categories = get_the_terms( $event_id, 'event_cat' );
if ( $categories && !is_wp_error($categories) ) {
	$cat_names = array();
	foreach( $categories as $category ) {
		$cat_names[] = $category->name;
	}
	if( !empty($cat_names) ) {
		$meta_items[] = '<span class="meta_item meta_category">' . esc_html( implode(', ', array_slice($cat_names, 0, 2)) ) . '</span>';
	}
}

// Durée (calculée depuis start/end date)
$start_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'start_date_str', true );
$end_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'end_date_str', true );

if( $start_date_str && $end_date_str ) {
	$duration_hours = round(($end_date_str - $start_date_str) / 3600, 1);

	if( $duration_hours < 24 ) {
		$duration_text = $duration_hours . ' ' . esc_html__( 'heures', 'eventlist' );
	} else {
		$duration_days = round($duration_hours / 24);
		$duration_text = $duration_days . ' ' . esc_html__( 'jours', 'eventlist' );
	}

	$meta_items[] = '<span class="meta_item meta_duration"><i class="icon_clock_alt"></i>' . $duration_text . '</span>';
}

// Langues (à adapter selon vos métadonnées)
// Si vous avez un champ custom pour les langues, l'ajouter ici
// Exemple:
// $languages = get_post_meta( $event_id, OVA_METABOX_EVENT . 'languages', true );
// if( $languages ) {
//     $meta_items[] = '<span class="meta_item meta_languages"><i class="icon_comment_alt"></i>' . esc_html( $languages ) . '</span>';
// }

// Note moyenne (si commentaires activés)
if( comments_open( $event_id ) ) {
	$comments_count = get_comments_number( $event_id );
	$average_rating = get_post_meta( $event_id, 'average_rating', true );

	if( $comments_count > 0 ) {
		$rating_display = $average_rating ? number_format($average_rating, 1) : '5.0';
		$meta_items[] = '<span class="meta_item meta_rating"><i class="icon_star"></i>' . $rating_display . ' (' . $comments_count . ' ' . esc_html__( 'avis', 'eventlist' ) . ')</span>';
	}
}

// Affichage
if( !empty($meta_items) ) : ?>
	<div class="event_meta_line_wrapper">
		<?php echo implode( ' <span class="meta_separator">•</span> ', $meta_items ); ?>
	</div>
<?php endif; ?>
