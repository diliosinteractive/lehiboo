<?php if( ! defined( 'ABSPATH' ) ) exit();

	$archive_type 	= EL_Setting::instance()->event->get( 'archive_type', 'type1' );
	$archive_column = EL_Setting::instance()->event->get( 'archive_column', 'two-column' );

	$archive_type = isset ( $_GET['type_event'] ) ? sanitize_text_field( $_GET['type_event'] ) : $archive_type;

	$archive_column = isset ( $_GET['layout_event'] ) ? sanitize_text_field( $_GET['layout_event'] ) : $archive_column;

	if ( $archive_type === 'type1' ||  $archive_type === 'type2' || $archive_type === 'type3' || $archive_type === 'type6' ) {
		$no_img_tmb = apply_filters( 'el_img_no_tmb', EL_PLUGIN_URI.'assets/img/no_tmb_square.png' );
		$img_tmb = apply_filters( 'el_img_tmb_squa', 'el_img_squa' );
	}else if ( $archive_type === 'type4' || $archive_type === 'type5' ) {
		$no_img_tmb = apply_filters( 'el_img_no_tmb', EL_PLUGIN_URI.'assets/img/no_tmb_rec.png' );
		$img_tmb = apply_filters( 'el_img_tmb_rec', 'el_img_rec' );
	}

	if( $archive_column == 'two-column' ){
		$no_img_tmb = apply_filters( 'el_img_no_tmb', EL_PLUGIN_URI.'assets/img/no_tmb_rec.png' );
		$img_tmb = apply_filters( 'el_img_tmb_rec', 'el_img_rec' );
	}
	
	
	$id = get_the_ID();

	$thumbnail 	= get_post_thumbnail_id( $id );
	$gallery 	= get_post_meta( $id, 'ova_mb_event_gallery', true );
	if ( ! empty( $thumbnail ) ) {
		array_unshift( $gallery, $thumbnail );
	}


	if( has_post_thumbnail() && get_the_post_thumbnail() ){

		$post_thumbnail_url = get_the_post_thumbnail_url( get_the_id(), $img_tmb );
	}else{
		$post_thumbnail_url = $no_img_tmb;
	}

	$display_image_thumbnail = apply_filters( 'el_display_image_thumbnail', EL()->options->event->get('display_image_opt', 'thumbnail'), $args );
?>

	<?php if ( $display_image_thumbnail == 'slider' && ! is_singular( 'event' ) ): ?>
		
		<!-- Event Slider -->
		<div class="event_slider">

			<?php if ( ! empty( $gallery ) ): ?>
				<div class="owl-carousel owl-theme">
					<?php foreach ( $gallery as $thumbnail_id ):
						?>
						<div class="item">
							<?php echo wp_get_attachment_image( $thumbnail_id, 'el_img_rec' ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="owl-carousel owl-theme">
					<div class="item">
						<img src="<?php echo esc_url( $post_thumbnail_url ); ?> " alt="<?php the_title(); ?>" />
					</div>
				</div>
			<?php endif; ?>	
		</div>

	<?php else: ?>

		<div class="thumbnail_figure">

			<!-- Thumbnail -->
			<a href="<?php the_permalink(); ?>">
				<img src="<?php echo esc_url( $post_thumbnail_url ); ?> " alt="<?php the_title(); ?>" />
			</a>
			
		</div>

<?php endif; ?>



	


	
	