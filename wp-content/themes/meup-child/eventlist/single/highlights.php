<?php
/**
 * Template Part: Highlights / À savoir
 *
 * Affiche 3-6 puces d'informations clés:
 * - Niveau (si taxonomie disponible)
 * - Âge recommandé
 * - Accessibilité
 * - Politique d'annulation
 * - Type d'événement
 * - Langues
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();
$highlights = array();

// 1. Type d'événement (Physique / En ligne)
$event_type = get_post_meta( $event_id, OVA_METABOX_EVENT . 'event_type', true );
if( $event_type == 'online' ) {
	$highlights[] = array(
		'icon' => 'icon_desktop',
		'label' => esc_html__( 'Événement en ligne', 'eventlist' )
	);
} else {
	$highlights[] = array(
		'icon' => 'icon_pin_alt',
		'label' => esc_html__( 'Événement physique', 'eventlist' )
	);
}

// 2. Niveau (depuis taxonomies custom el_job ou autre)
$job_terms = get_the_terms( $event_id, 'el_job' );
if ( $job_terms && !is_wp_error($job_terms) ) {
	$job_names = array();
	foreach( $job_terms as $term ) {
		$job_names[] = $term->name;
	}
	$highlights[] = array(
		'icon' => 'icon_star',
		'label' => esc_html__( 'Niveau: ', 'eventlist' ) . implode(', ', $job_names)
	);
}

// 3. Public cible (depuis taxonomie el_public)
$public_terms = get_the_terms( $event_id, 'el_public' );
if ( $public_terms && !is_wp_error($public_terms) ) {
	$public_names = array();
	foreach( $public_terms as $term ) {
		$public_names[] = $term->name;
	}
	$highlights[] = array(
		'icon' => 'icon_group',
		'label' => implode(', ', $public_names)
	);
}

// 4. Durée (depuis taxonomie el_time)
$time_terms = get_the_terms( $event_id, 'el_time' );
if ( $time_terms && !is_wp_error($time_terms) ) {
	$time_names = array();
	foreach( $time_terms as $term ) {
		$time_names[] = $term->name;
	}
	$highlights[] = array(
		'icon' => 'icon_clock_alt',
		'label' => implode(', ', $time_names)
	);
}

// 5. Politique d'annulation
$allow_cancellation = get_post_meta( $event_id, OVA_METABOX_EVENT . 'allow_cancellation_booking', true );
$cancel_before_days = get_post_meta( $event_id, OVA_METABOX_EVENT . 'cancel_before_x_day', true );

if( $allow_cancellation == 'yes' ) {
	if( $cancel_before_days && $cancel_before_days > 0 ) {
		$cancel_text = sprintf(
			esc_html__( 'Annulation gratuite jusqu\'à %s jours avant', 'eventlist' ),
			$cancel_before_days
		);
	} else {
		$cancel_text = esc_html__( 'Annulation gratuite', 'eventlist' );
	}

	$highlights[] = array(
		'icon' => 'icon_check',
		'label' => $cancel_text
	);
} else {
	$highlights[] = array(
		'icon' => 'icon_close',
		'label' => esc_html__( 'Annulation non autorisée', 'eventlist' )
	);
}

// 6. Événement Featured
$is_featured = get_post_meta( $event_id, OVA_METABOX_EVENT . 'event_feature', true );
if( $is_featured == 'yes' ) {
	$highlights[] = array(
		'icon' => 'icon_ribbon_alt',
		'label' => esc_html__( 'Événement à la une', 'eventlist' )
	);
}

// Affichage
if( !empty($highlights) ) : ?>
	<ul class="highlight_list">
		<?php foreach( $highlights as $highlight ) : ?>
			<li class="highlight_item">
				<span class="highlight_text"><?php echo esc_html( $highlight['label'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
