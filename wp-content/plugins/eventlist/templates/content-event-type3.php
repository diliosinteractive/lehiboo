<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
$post_id = get_the_ID();

$url_img = wp_get_attachment_image_url( get_post_thumbnail_id() , 'el_img_squa' );

if ( has_post_thumbnail() && get_the_post_thumbnail()) {
	$url_img = wp_get_attachment_image_url( get_post_thumbnail_id() , 'el_img_squa' );
} else {
	$url_img = EL_PLUGIN_URI.'assets/img/no_tmb_square.png';
}

$display_image_thumbnail = apply_filters( 'el_display_image_thumbnail', EL()->options->event->get('display_image_opt', 'thumbnail'), $args );

$thumbnail 	= get_post_thumbnail_id( $post_id );
$gallery 	= get_post_meta( $post_id, 'ova_mb_event_gallery', true );
if ( ! empty( $thumbnail ) ) {
	array_unshift( $gallery, $thumbnail );
}


?>
<div class="event_item type3 <?php echo esc_attr( $display_image_thumbnail ); ?>">
		<?php if ( apply_filters( 'el_ft_show_remove_btn', false ) ): ?>
			<?php do_action( 'el_loop_event_remove', $args ); ?>
		<?php endif; ?>

	<div class="image_feature">

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
							<img src="<?php echo esc_url( $url_img ); ?> " alt="<?php the_title(); ?>" />
						</div>
					</div>
				<?php endif; ?>	

			</div>

			<img class="el_img_mobile" src="<?php echo esc_url($url_img) ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">

		<?php else: ?>

			<img class="el_img" src="<?php echo esc_url($url_img) ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">

		<?php endif; ?>

		<?php 
			/**
			 * Display Category
			 * Hook: el_loop_event_cat_3
			 * @hooked: el_loop_event_cat_3
			 */
			do_action( 'el_loop_event_cat_3', $args );
		?>
		

	</div>

	<div class="info_event">
		<div class="status-title">
			<?php 
				do_action( 'el_loop_event_title', $args );
				
				do_action( 'el_loop_event_status', $args );
			?>
		</div>
		<?php

		do_action( 'el_loop_event_ratting', $args );

		do_action( 'el_loop_event_time', $args );

		do_action( 'el_loop_event_location', $args );
		
		do_action( 'el_loop_event_price' , $args);

		do_action( 'el_loop_event_favourite', $args );

		?>

	</div>

	
</div>



