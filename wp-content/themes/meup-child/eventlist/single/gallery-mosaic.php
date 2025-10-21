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

// Limiter à 5 images max (1 grande + 4 mini)
$main_image = !empty($list_gallery_large) ? $list_gallery_large[0] : $list_gallery[0];
$grid_images = array_slice( $list_gallery, 0, 4 );

// Remplir avec des placeholders si moins de 4 images
while( count($grid_images) < 4 ) {
	$grid_images[] = $placeholder;
}

$total_images = count($list_gallery);
?>

<?php if ( !empty($list_gallery) ) : ?>
	<div class="event_gallery_mosaic_wrapper">

		<!-- Image principale (gauche) -->
		<div class="gallery_main_image">
			<a href="<?php echo esc_url( $main_image ); ?>"
			   class="gallery_lightbox"
			   data-lightbox="event-gallery"
			   data-title="<?php echo esc_attr( get_the_title() ); ?>">
				<img src="<?php echo esc_url( $main_image ); ?>"
				     alt="<?php echo esc_attr( get_the_title() ); ?>"
				     class="main_image" />
			</a>
		</div>

		<!-- Grille 2x2 (droite) -->
		<div class="gallery_grid_images">
			<?php foreach( $grid_images as $index => $image_url ) : ?>
				<div class="grid_image_item <?php echo $index === 3 ? 'last_item' : ''; ?>">
					<a href="<?php echo esc_url( $image_url ); ?>"
					   class="gallery_lightbox"
					   data-lightbox="event-gallery"
					   data-title="<?php echo esc_attr( get_the_title() ); ?>">
						<img src="<?php echo esc_url( $image_url ); ?>"
						     alt="<?php echo esc_attr( get_the_title() ); ?>" />

						<!-- Badge "Voir toutes les photos" sur la 4ème image -->
						<?php if( $index === 3 && $total_images > 5 ) : ?>
							<div class="view_all_photos_overlay">
								<i class="icon_image"></i>
								<span><?php echo sprintf( esc_html__( 'Voir les %s photos', 'eventlist' ), $total_images ); ?></span>
							</div>
						<?php endif; ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Images cachées pour la lightbox (si plus de 5 images) -->
		<?php if( $total_images > 5 ) : ?>
			<div class="gallery_hidden_images" style="display:none;">
				<?php
				$remaining_images = array_slice( $list_gallery, 5 );
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
