<?php
/**
 * Template Part: Gallery Mosaic
 *
 * Affiche la galerie en mode mosaïque:
 * - 1 grande image (16/9) à gauche
 * - 4 miniatures en grille 2x2 à droite
 * - Lightbox au clic
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;
$event_id = get_the_ID();

// Récupérer la galerie
$list_gallery_large = $event->get_gallery_single_event('el_large_gallery');
$list_gallery = $event->get_gallery_single_event('el_thumbnail_gallery');

// Si pas de galerie, utiliser l'image à la une
if ( empty($list_gallery) && has_post_thumbnail() ) {
	$thumbnail_url = get_the_post_thumbnail_url( $event_id, 'full' );
	$list_gallery = array( $thumbnail_url );
	$list_gallery_large = array( $thumbnail_url );
}

// Placeholder si aucune image
$placeholder = EL_PLUGIN_URI . 'assets/img/placeholder.jpg';

if ( empty($list_gallery) ) {
	$list_gallery = array( $placeholder );
	$list_gallery_large = array( $placeholder );
}

// Prendre les 2 premières images pour les grandes images (format A4)
$main_images = array_slice( !empty($list_gallery_large) ? $list_gallery_large : $list_gallery, 0, 2 );

// Remplir avec placeholder si moins de 2 images
while( count($main_images) < 2 ) {
	$main_images[] = $placeholder;
}

// Miniatures : prendre les images restantes (max 4)
$thumbnail_images = array_slice( $list_gallery, 2, 4 );

$total_images = count($list_gallery);
?>

<?php if ( !empty($list_gallery) ) : ?>
	<div class="event_gallery_mosaic_wrapper">

		<!-- Deux grandes images format A4 en haut -->
		<div class="gallery_main_images_row">
			<?php foreach( $main_images as $index => $image_url ) : ?>
				<div class="gallery_main_image gallery_main_image_<?php echo $index + 1; ?>">
					<a href="<?php echo esc_url( $image_url ); ?>"
					   class="gallery_lightbox"
					   data-lightbox="event-gallery"
					   data-title="<?php echo esc_attr( get_the_title() ); ?>">
						<img src="<?php echo esc_url( $image_url ); ?>"
						     alt="<?php echo esc_attr( get_the_title() ); ?>"
						     class="main_image" />
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Miniatures en dessous (si disponibles) -->
		<?php if ( !empty($thumbnail_images) ) : ?>
			<div class="gallery_thumbnails_row">
				<?php foreach( $thumbnail_images as $index => $image_url ) : ?>
					<div class="gallery_thumbnail_item">
						<a href="<?php echo esc_url( $image_url ); ?>"
						   class="gallery_lightbox"
						   data-lightbox="event-gallery"
						   data-title="<?php echo esc_attr( get_the_title() ); ?>">
							<img src="<?php echo esc_url( $image_url ); ?>"
							     alt="<?php echo esc_attr( get_the_title() ); ?>" />

							<!-- Badge "Voir toutes les photos" sur la dernière miniature -->
							<?php if( $index === count($thumbnail_images) - 1 && $total_images > 6 ) : ?>
								<div class="view_all_photos_overlay">
									<i class="icon_image"></i>
									<span>+<?php echo $total_images - 6; ?></span>
								</div>
							<?php endif; ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Images cachées pour la lightbox (si plus de 6 images) -->
		<?php if( $total_images > 6 ) : ?>
			<div class="gallery_hidden_images" style="display:none;">
				<?php
				$remaining_images = array_slice( $list_gallery, 6 );
				foreach( $remaining_images as $image_url ) :
				?>
					<a href="<?php echo esc_url( $image_url ); ?>"
					   class="gallery_lightbox"
					   data-lightbox="event-gallery"></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Bouton "Voir toutes les photos" (mobile) -->
		<button class="btn_view_all_photos mobile_only" data-lightbox-trigger="event-gallery">
			<i class="icon_image"></i>
			<?php echo sprintf( esc_html__( 'Voir les %s photos', 'eventlist' ), $total_images ); ?>
		</button>

	</div>
<?php endif; ?>
