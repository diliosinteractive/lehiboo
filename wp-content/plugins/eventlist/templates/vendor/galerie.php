<?php
/**
 * Template pour la galerie du partenaire
 * Permet d'uploader et gérer les images de la galerie
 */
if ( ! defined( 'ABSPATH' ) ) exit();

$user_id = get_current_user_id();

// Récupérer les images de la galerie
$gallery_images = get_user_meta( $user_id, 'vendor_gallery_images', true );
$gallery_images = $gallery_images ? $gallery_images : array();

?>

<div class="vendor_wrap">
	<?php echo el_get_template( 'vendor/sidebar.php' ); ?>

	<div class="contents">

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_galerie">

			<div class="galerie_header">
				<h2><?php esc_html_e( 'Ma galerie', 'eventlist' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Gérez les images de votre galerie qui seront affichées sur votre profil public.', 'eventlist' ); ?></p>
			</div>

			<!-- Upload Zone -->
			<div class="galerie_upload_zone">
				<button type="button" class="button button_primary add_gallery_images"
					data-uploader-title="<?php esc_attr_e( 'Ajouter des images à la galerie', 'eventlist' ); ?>"
					data-uploader-button-text="<?php esc_attr_e( 'Ajouter les images', 'eventlist' ); ?>">
					<i class="icon_plus"></i>
					<?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
				</button>
				<small><?php esc_html_e( 'Formats acceptés : JPG, PNG, GIF. Taille maximale : 2MB par image. Dimensions recommandées : 1200x800px', 'eventlist' ); ?></small>
			</div>

			<!-- Galerie Grid -->
			<?php if ( ! empty( $gallery_images ) ) : ?>
				<div class="galerie_grid" id="gallery_items_grid">
					<?php foreach ( $gallery_images as $image_id ) :
						$image_url = wp_get_attachment_image_url( $image_id, 'large' );
						$image_thumb = wp_get_attachment_image_url( $image_id, 'medium' );
						$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

						if ( ! $image_url ) continue;
					?>
						<div class="galerie_item" data-image-id="<?php echo esc_attr( $image_id ); ?>">
							<div class="galerie_item_inner">
								<img src="<?php echo esc_url( $image_thumb ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
								<div class="galerie_item_overlay">
									<button type="button" class="galerie_action_btn view_image_btn" data-image-url="<?php echo esc_url( $image_url ); ?>" title="<?php esc_attr_e( 'Voir l\'image', 'eventlist' ); ?>">
										<i class="icon_search"></i>
									</button>
									<button type="button" class="galerie_action_btn delete_image_btn" data-image-id="<?php echo esc_attr( $image_id ); ?>" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
										<i class="icon_trash_alt"></i>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="galerie_empty" id="gallery_empty_state">
					<div class="galerie_empty_icon">
						<i class="icon_images"></i>
					</div>
					<h3><?php esc_html_e( 'Votre galerie est vide', 'eventlist' ); ?></h3>
					<p><?php esc_html_e( 'Ajoutez des images pour mettre en valeur votre organisation et vos événements.', 'eventlist' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Image count -->
			<div class="galerie_footer">
				<p class="galerie_count">
					<span id="gallery_count"><?php echo count( $gallery_images ); ?></span>
					<?php echo _n( 'image dans votre galerie', 'images dans votre galerie', count( $gallery_images ), 'eventlist' ); ?>
				</p>
			</div>

		</div> <!-- End vendor_galerie -->

	</div> <!-- End contents -->
</div> <!-- End vendor_wrap -->

<!-- Modal pour visualiser l'image -->
<div id="gallery_image_modal" class="gallery_modal" style="display: none;">
	<div class="gallery_modal_overlay"></div>
	<div class="gallery_modal_content">
		<button type="button" class="gallery_modal_close">
			<i class="icon_close"></i>
		</button>
		<img src="" alt="" id="gallery_modal_image">
	</div>
</div>
