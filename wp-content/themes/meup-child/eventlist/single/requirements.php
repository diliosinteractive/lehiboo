<?php
/**
 * Template Part: Exigences
 *
 * Affiche les exigences pour participer à l'événement:
 * - Âge minimum/maximum
 * - Tenue vestimentaire
 * - Documents requis
 * - Autres conditions
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();

// Récupérer les exigences (à adapter selon vos metabox)
$requirements = get_post_meta( $event_id, OVA_METABOX_EVENT . 'requirements', true );

// Si pas de metabox, essayer de construire depuis les taxonomies public
if( empty($requirements) ) {
	$requirements = array();

	// Âge depuis taxonomie public
	$public_terms = get_the_terms( $event_id, 'el_public' );
	if ( $public_terms && !is_wp_error($public_terms) ) {
		foreach( $public_terms as $term ) {
			// Détecter les termes contenant "ans", "enfants", etc.
			if( stripos($term->name, 'ans') !== false || stripos($term->name, 'enfant') !== false ) {
				$requirements[] = $term->name;
			}
		}
	}
}

// Si toujours vide, ne rien afficher
if( empty($requirements) ) {
	return;
}
?>

<div class="event_requirements event_section_white">
	<h3 class="requirements_title second_font"><?php esc_html_e( 'Conditions requises', 'eventlist' ); ?></h3>

	<ul class="requirements_list">
		<?php
		if( is_string($requirements) ) {
			$requirements = explode("\n", $requirements);
		}

		foreach( (array)$requirements as $requirement ) :
			$requirement = trim($requirement);
			if( empty($requirement) ) continue;
		?>
			<li class="requirement_item">
				<i class="icon_document_alt"></i>
				<span><?php echo esc_html( $requirement ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Message par défaut si liste vide -->
	<?php if( count($requirements) == 0 ) : ?>
		<p class="no_requirements">
			<?php esc_html_e( 'Aucune condition particulière requise.', 'eventlist' ); ?>
		</p>
	<?php endif; ?>
</div>
