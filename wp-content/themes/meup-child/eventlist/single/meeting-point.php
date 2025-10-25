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

<div class="meeting_point_card">
	<div class="meeting_icon">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
			<circle cx="12" cy="10" r="3"></circle>
		</svg>
	</div>

	<div class="meeting_details">
		<!-- Nom du lieu -->
		<?php if( $venue_name ) : ?>
			<h3><?php echo esc_html( stripslashes_deep($venue_name) ); ?></h3>
		<?php endif; ?>

		<!-- Adresse complète -->
		<?php if( $address ) : ?>
			<p><?php echo esc_html( stripslashes_deep($address) ); ?></p>
		<?php endif; ?>

		<!-- Instructions additionnelles -->
		<?php
		$meeting_instructions = get_post_meta( $event_id, OVA_METABOX_EVENT . 'meeting_instructions', true );
		if( $meeting_instructions ) :
		?>
			<p><?php echo wp_kses_post( $meeting_instructions ); ?></p>
		<?php endif; ?>

		<!-- Bouton Itinéraire -->
		<?php if( $directions_url ) : ?>
			<a href="<?php echo esc_url( $directions_url ); ?>"
			   class="btn_get_directions"
			   target="_blank"
			   rel="noopener"
			   style="display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; padding: 10px 16px; background: #FF5A5F; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="5" y1="12" x2="19" y2="12"></line>
					<polyline points="12 5 19 12 12 19"></polyline>
				</svg>
				<?php esc_html_e( 'Obtenir l\'itinéraire', 'eventlist' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
