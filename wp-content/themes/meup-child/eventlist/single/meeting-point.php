<?php
/**
 * Template Part: Point de RDV
 *
 * Affiche le point de rendez-vous avec bouton "Obtenir l'itinéraire"
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();

// Récupérer l'adresse
$address = get_post_meta( $event_id, OVA_METABOX_EVENT . 'address', true );
$venue = get_post_meta( $event_id, OVA_METABOX_EVENT . 'venue', true );
$map_lat = floatval( get_post_meta( $event_id, OVA_METABOX_EVENT . 'map_lat', true ) );
$map_lng = floatval( get_post_meta( $event_id, OVA_METABOX_EVENT . 'map_lng', true ) );
$event_type = get_post_meta( $event_id, OVA_METABOX_EVENT . 'event_type', true );

// Ne rien afficher pour les événements en ligne
if( $event_type == 'online' ) {
	return;
}

// Ne rien afficher si pas d'adresse
if( empty($address) && empty($venue) ) {
	return;
}

// Formater le nom du lieu
$venue_name = '';
if( is_array($venue) ) {
	$venue_name = implode(', ', $venue);
} else {
	$venue_name = $venue;
}

// URL Google Maps
$directions_url = '';
if( $map_lat && $map_lng ) {
	$directions_url = "https://maps.google.com?saddr=Current+Location&daddr={$map_lat},{$map_lng}";
} elseif( $address ) {
	$directions_url = "https://maps.google.com?daddr=" . urlencode($address);
}
?>

<div class="event_meeting_point event_section_white">
	<h3 class="meeting_point_title second_font"><?php esc_html_e( 'Lieu de rendez-vous', 'eventlist' ); ?></h3>

	<div class="meeting_point_content">

		<!-- Nom du lieu -->
		<?php if( $venue_name ) : ?>
			<h4 class="venue_name">
				<i class="icon_pin_alt"></i>
				<?php echo esc_html( stripslashes_deep($venue_name) ); ?>
			</h4>
		<?php endif; ?>

		<!-- Adresse complète -->
		<?php if( $address ) : ?>
			<p class="venue_address">
				<?php echo esc_html( stripslashes_deep($address) ); ?>
			</p>
		<?php endif; ?>

		<!-- Bouton Itinéraire -->
		<?php if( $directions_url ) : ?>
			<a href="<?php echo esc_url( $directions_url ); ?>"
			   class="btn_get_directions"
			   target="_blank"
			   rel="noopener">
				<i class="icon_cursor_alt"></i>
				<?php esc_html_e( 'Obtenir l\'itinéraire', 'eventlist' ); ?>
			</a>
		<?php endif; ?>

		<!-- Instructions additionnelles (si métabox disponible) -->
		<?php
		$meeting_instructions = get_post_meta( $event_id, OVA_METABOX_EVENT . 'meeting_instructions', true );
		if( $meeting_instructions ) :
		?>
			<div class="meeting_instructions">
				<p><?php echo wp_kses_post( $meeting_instructions ); ?></p>
			</div>
		<?php endif; ?>

	</div>
</div>
