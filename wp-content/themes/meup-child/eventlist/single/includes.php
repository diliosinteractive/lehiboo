<?php
/**
 * Template Part: Inclus / Non inclus
 *
 * Affiche ce qui est inclus et non inclus dans l'événement
 *
 * Note: Pour l'instant utilise des métadonnées custom.
 * À adapter selon vos besoins (créer des metabox si nécessaire).
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();

// Récupérer les données (à adapter selon vos metabox)
$includes = get_post_meta( $event_id, OVA_METABOX_EVENT . 'includes', true );
$excludes = get_post_meta( $event_id, OVA_METABOX_EVENT . 'excludes', true );

// Par défaut, essayer d'extraire du contenu de l'événement si pas de metabox
if( empty($includes) && empty($excludes) ) {
	// Exemple de données par défaut basées sur le type d'événement
	$event_type = get_post_meta( $event_id, OVA_METABOX_EVENT . 'event_type', true );

	if( $event_type == 'online' ) {
		$includes = array(
			esc_html__( 'Lien de connexion envoyé par email', 'eventlist' ),
			esc_html__( 'Support numérique', 'eventlist' ),
		);
	} else {
		// Essayer de détecter depuis les extra services
		$extra_services = get_post_meta( $event_id, OVA_METABOX_EVENT . 'extra_services', true );

		if( !empty($extra_services) && is_array($extra_services) ) {
			$includes = array();
			foreach( $extra_services as $service ) {
				if( isset($service['service_name']) ) {
					$includes[] = $service['service_name'];
				}
			}
		}
	}
}

// Si toujours vide, ne rien afficher
if( empty($includes) && empty($excludes) ) {
	return;
}
?>

<div class="event_includes event_section_white">
	<h3 class="includes_title second_font"><?php esc_html_e( 'Ce qui est inclus', 'eventlist' ); ?></h3>

	<div class="includes_grid">

		<!-- Liste Inclus -->
		<?php if( !empty($includes) ) : ?>
			<div class="includes_column">
				<h4 class="column_title"><?php esc_html_e( 'Inclus', 'eventlist' ); ?></h4>
				<ul class="includes_list">
					<?php
					if( is_string($includes) ) {
						$includes = explode("\n", $includes);
					}

					foreach( (array)$includes as $item ) :
						$item = trim($item);
						if( empty($item) ) continue;
					?>
						<li class="include_item included">
							<i class="icon_check"></i>
							<span><?php echo esc_html( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<!-- Liste Non inclus -->
		<?php if( !empty($excludes) ) : ?>
			<div class="includes_column">
				<h4 class="column_title"><?php esc_html_e( 'Non inclus', 'eventlist' ); ?></h4>
				<ul class="includes_list">
					<?php
					if( is_string($excludes) ) {
						$excludes = explode("\n", $excludes);
					}

					foreach( (array)$excludes as $item ) :
						$item = trim($item);
						if( empty($item) ) continue;
					?>
						<li class="include_item excluded">
							<i class="icon_close"></i>
							<span><?php echo esc_html( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

	</div>
</div>
