<?php
/**
 * Template pour la galerie du partenaire
 * Affiche toutes les images uploadées par le partenaire dans la médiathèque WordPress
 */
if ( ! defined( 'ABSPATH' ) ) exit();

$user_id = get_current_user_id();

// Paramètres de pagination
$paged = isset( $_GET['gallery_page'] ) ? absint( $_GET['gallery_page'] ) : 1;
$per_page = 24;

// Filtre de recherche
$search = isset( $_GET['gallery_search'] ) ? sanitize_text_field( $_GET['gallery_search'] ) : '';

// Arguments pour récupérer les images du partenaire depuis la médiathèque
$args = array(
	'post_type'      => 'attachment',
	'post_mime_type' => 'image',
	'post_status'    => 'inherit',
	'author'         => $user_id,
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC'
);

// Ajouter recherche si présente
if ( ! empty( $search ) ) {
	$args['s'] = $search;
}

// Récupérer les images
$query = new WP_Query( $args );
$total_images = $query->found_posts;
$total_pages = $query->max_num_pages;

?>

<div class="vendor_wrap">
	<?php echo el_get_template( 'vendor/sidebar.php' ); ?>

	<div class="contents">

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_galerie">

			<div class="galerie_header">
				<h2><?php esc_html_e( 'Ma galerie', 'eventlist' ); ?></h2>
				<p class="description">
					<?php
					printf(
						esc_html__( 'Toutes les images que vous avez uploadées dans WordPress. Total : %s', 'eventlist' ),
						'<strong>' . number_format( $total_images ) . ' ' . _n( 'image', 'images', $total_images, 'eventlist' ) . '</strong>'
					);
					?>
				</p>
			</div>

			<!-- Barre d'outils : Recherche + Upload -->
			<div class="galerie_toolbar">
				<div class="galerie_search">
					<form method="get" action="" class="galerie_search_form">
						<?php
						// Préserver les paramètres GET existants
						if ( isset( $_GET['vendor'] ) ) {
							echo '<input type="hidden" name="vendor" value="' . esc_attr( $_GET['vendor'] ) . '">';
						}
						?>
						<i class="icon_search"></i>
						<input type="text"
							name="gallery_search"
							placeholder="<?php esc_attr_e( 'Rechercher une image...', 'eventlist' ); ?>"
							value="<?php echo esc_attr( $search ); ?>"
							class="galerie_search_input">
						<button type="submit" class="galerie_search_btn">
							<?php esc_html_e( 'Rechercher', 'eventlist' ); ?>
						</button>
						<?php if ( ! empty( $search ) ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'vendor', 'galerie', get_myaccount_page() ) ); ?>" class="galerie_search_clear">
								<i class="icon_close"></i>
							</a>
						<?php endif; ?>
					</form>
				</div>

				<button type="button" class="button button_primary add_gallery_images"
					data-uploader-title="<?php esc_attr_e( 'Ajouter des images', 'eventlist' ); ?>"
					data-uploader-button-text="<?php esc_attr_e( 'Ajouter', 'eventlist' ); ?>">
					<i class="icon_plus"></i>
					<?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
				</button>
			</div>

			<!-- Galerie Grid -->
			<?php if ( $query->have_posts() ) : ?>
				<div class="galerie_grid" id="gallery_items_grid">
					<?php while ( $query->have_posts() ) : $query->the_post();
						$image_id = get_the_ID();
						$image_url = wp_get_attachment_image_url( $image_id, 'large' );
						$image_thumb = wp_get_attachment_image_url( $image_id, 'thumbnail' );
						$image_title = get_the_title();
						$image_date = get_the_date( 'd/m/Y' );
						$image_size = size_format( filesize( get_attached_file( $image_id ) ), 2 );
					?>
						<div class="galerie_item" data-image-id="<?php echo esc_attr( $image_id ); ?>">
							<div class="galerie_item_inner">
								<img src="<?php echo esc_url( $image_thumb ); ?>"
									alt="<?php echo esc_attr( $image_title ); ?>"
									loading="lazy"
									width="150"
									height="150">
								<div class="galerie_item_overlay">
									<div class="galerie_item_info">
										<span class="image_title"><?php echo esc_html( wp_trim_words( $image_title, 5 ) ); ?></span>
										<span class="image_meta"><?php echo esc_html( $image_date ); ?> • <?php echo esc_html( $image_size ); ?></span>
									</div>
									<div class="galerie_item_actions">
										<button type="button" class="galerie_action_btn view_image_btn"
											data-image-url="<?php echo esc_url( $image_url ); ?>"
											title="<?php esc_attr_e( 'Voir l\'image', 'eventlist' ); ?>">
											<i class="icon_search"></i>
										</button>
										<button type="button" class="galerie_action_btn edit_image_btn"
											data-image-id="<?php echo esc_attr( $image_id ); ?>"
											title="<?php esc_attr_e( 'Modifier les métadonnées', 'eventlist' ); ?>">
											<i class="icon_pencil"></i>
										</button>
										<button type="button" class="galerie_action_btn delete_image_btn"
											data-image-id="<?php echo esc_attr( $image_id ); ?>"
											title="<?php esc_attr_e( 'Supprimer définitivement', 'eventlist' ); ?>">
											<i class="icon_trash_alt"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>

				<!-- Pagination -->
				<?php if ( $total_pages > 1 ) : ?>
					<div class="galerie_pagination">
						<?php
						$current_page = max( 1, $paged );

						// Page précédente
						if ( $current_page > 1 ) {
							$prev_link = add_query_arg( array(
								'vendor' => 'galerie',
								'gallery_page' => $current_page - 1,
								'gallery_search' => $search
							), get_myaccount_page() );
							echo '<a href="' . esc_url( $prev_link ) . '" class="galerie_page_btn galerie_page_prev">
								<i class="icon_arrow_left"></i> ' . esc_html__( 'Précédent', 'eventlist' ) . '
							</a>';
						}

						// Numéros de pages
						echo '<div class="galerie_page_numbers">';
						echo '<span class="current_page">Page ' . $current_page . ' / ' . $total_pages . '</span>';
						echo '</div>';

						// Page suivante
						if ( $current_page < $total_pages ) {
							$next_link = add_query_arg( array(
								'vendor' => 'galerie',
								'gallery_page' => $current_page + 1,
								'gallery_search' => $search
							), get_myaccount_page() );
							echo '<a href="' . esc_url( $next_link ) . '" class="galerie_page_btn galerie_page_next">
								' . esc_html__( 'Suivant', 'eventlist' ) . ' <i class="icon_arrow_right"></i>
							</a>';
						}
						?>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="galerie_empty" id="gallery_empty_state">
					<div class="galerie_empty_icon">
						<i class="icon_images"></i>
					</div>
					<?php if ( ! empty( $search ) ) : ?>
						<h3><?php esc_html_e( 'Aucune image trouvée', 'eventlist' ); ?></h3>
						<p><?php esc_html_e( 'Essayez une autre recherche ou ajoutez de nouvelles images.', 'eventlist' ); ?></p>
					<?php else : ?>
						<h3><?php esc_html_e( 'Votre galerie est vide', 'eventlist' ); ?></h3>
						<p><?php esc_html_e( 'Cliquez sur "Ajouter des images" pour uploader vos premières photos dans WordPress.', 'eventlist' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

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
