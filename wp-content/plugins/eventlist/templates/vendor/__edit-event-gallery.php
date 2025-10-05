<?php if ( !defined( 'ABSPATH' ) ) exit();


$post_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';

$_prefix = OVA_METABOX_EVENT;

$gallery = get_post_meta( $post_id, $_prefix.'gallery', true) ? get_post_meta( $post_id, $_prefix.'gallery', true) : '';


$link_video = get_post_meta( $post_id, $_prefix.'link_video', true) ? get_post_meta( $post_id, $_prefix.'link_video', true) : '';


$single_banner = get_post_meta( $post_id, $_prefix.'single_banner', true) ? get_post_meta( $post_id, $_prefix.'single_banner', true) : 'thumbnail';
$image_banner = get_post_meta( $post_id, $_prefix.'image_banner', true) ? get_post_meta( $post_id, $_prefix.'image_banner', true) : '';
?>

<!-- Image Gallery -->
<div class="image_gallery">
	
	
	<div class="gallery_box">
		<div class="gallery_list">
			<?php if ( $gallery ) : foreach ( $gallery as $key => $value ) : $image = wp_get_attachment_image_src( $value, 'el_thumbnail' ); ?>
				<div class="gallery_item">
					<div class="image_box">
						<input type="hidden" class="gallery_id" value="<?php echo esc_attr($value); ?>">
						
						<img class="image-preview" src="<?php echo esc_url($image[0]); ?>">

						<a class="remove_image" href="#">
							<i class="fa fa-times-circle" aria-hidden="true"></i>
						</a>
					</div>
				</div>
			<?php endforeach; endif; ?>
		</div>
	</div>
	<a class="add_image_gallery button" href="#" data-uploader-title="<?php esc_attr_e( "Add Gallery", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Add image(s)", 'eventlist' ); ?>"><?php esc_html_e( "Add Gallery", 'eventlist' ); ?></a>
	


</div>


<!-- Video -->
<div class="link_video vendor_field">

	<h4 class="heading_section">
		<?php esc_html_e( 'Video', 'eventlist' ); ?>

		<?php if ( apply_filters( 'el_video_req', false, $args ) == true ): ?>
			<span class="el_req">*</span>
		<?php endif; ?>

		<span class="el_icon_help dashicons dashicons-editor-help"
		data-tippy-content="<?php esc_attr_e( 'Ex: https://www.youtube.com/watch?v=5wZ9LcEbulg or Vimeo: https://player.vimeo.com/video/23534361', 'eventlist' ); ?>"></span>
	</h4>
	
	<div class="wrap_link">
		<input type="text" id="link_video" name="<?php echo esc_attr( $_prefix.'link_video' ); ?>" value="<?php echo esc_attr( $link_video ); ?>" placeholder="<?php esc_html_e( 'https://', 'eventlist' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
	</div>
</div>


<!-- Single Banner -->
<div class="wrap_single_banner vendor_field">
	<label class="label"><?php esc_html_e( 'Display Top Banner of event detailt at frontend:', 'eventlist' ); ?></label>
	
	<div class="radio_single_banner">
		<label for="single_banner_thumbnail" class="el_input_radio">
			<input type="radio" name="<?php echo esc_attr( $_prefix.'single_banner' ) ?>" id="single_banner_thumbnail" class="single_banner" value="<?php echo esc_attr('thumbnail'); ?>"  <?php if ($single_banner == 'thumbnail' || $single_banner == '') echo esc_attr('checked') ; ?>  > <?php esc_html_e( 'Image', 'eventlist' ); ?>
			<span class="checkmark"></span>
		</label>
		
		<label for="single_banner_gallery" class="el_input_radio el_ml_10px">
			<input type="radio" name="<?php echo esc_attr( $_prefix.'single_banner' ) ?>" class="single_banner" id="single_banner_gallery" value="<?php echo esc_attr('gallery'); ?>" <?php if ($single_banner == 'gallery') echo esc_attr('checked') ; ?> > <?php esc_html_e( 'Gallery', 'eventlist' ); ?>
			<span class="checkmark"></span>
		</label>
	</div>

</div>