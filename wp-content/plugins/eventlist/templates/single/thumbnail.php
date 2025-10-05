<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<?php if( has_post_thumbnail() ):  ?>

	<div class="thumbnail_figure">
		<?php 

		if( has_post_thumbnail() && get_the_post_thumbnail() ){
			$post_thumbnail_url = has_image_size( 'thumbnail_single_page' ) ?  the_post_thumbnail( get_the_id(), 'thumbnail_single_page' ) : the_post_thumbnail( get_the_id(), 'full' );
		}

		?>
	</div>

<?php endif; ?>
