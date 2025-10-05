<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
$author_id = get_the_author_meta('ID');

?>
<?php 
if( $author_id ): 

$author_id_image = get_user_meta( $author_id, 'author_id_image', true ) ? get_user_meta( $author_id, 'author_id_image', true ) : '';
$img_path = ( $author_id_image && wp_get_attachment_image_url($author_id_image, 'el_thumbnail') ) ? wp_get_attachment_image_url($author_id_image, 'el_thumbnail') : EL_PLUGIN_URI.'assets/img/unknow_user.png';

?>

	<div class="img-author">

		<!-- Thumbnail -->
		<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?> ">
			<img src="<?php echo esc_url($img_path) ?>" alt="<?php esc_attr_e('author vendor', 'eventlist') ?>">
		</a>
		
	</div>

<?php endif; ?>