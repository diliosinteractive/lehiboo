<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
global $event;
$list_gallery_large = $event->get_gallery_single_event('el_large_gallery');
$list_gallery = $event->get_gallery_single_event( 'el_thumbnail_gallery' );
?>
<?php if ( ! empty ( $list_gallery ) ) : ?>
	<div class="event-gallery event_section_white " >
		<h3 class="second_font heading"><?php esc_html_e("Gallery", "eventlist") ?></h3>
		<div class="slide_gallery">
			<div class="wrap_slide">
				<?php if(!empty($list_gallery_large) && is_array($list_gallery_large)) : ?>
				<?php foreach($list_gallery_large as $image) : ?>
					<div>
						<img data-lazy="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
					</div>
				<?php endforeach ?>
			<?php endif ?>
		</div>
	</div>
	<div class="thumbnail_gallery">
		<?php if(!empty($list_gallery) && is_array($list_gallery)) : ?>
		<?php foreach($list_gallery as $image) : ?>
			<div>
				<img data-lazy="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
			</div>
		<?php endforeach ?>
	<?php endif ?>
</div>
</div>
<?php endif ?>
