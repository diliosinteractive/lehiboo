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
	<a class="add_gallery_images button btn_add_co_organizer" href="#" data-uploader-title="<?php esc_attr_e( 'Ajouter des images', 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Ajouter', 'eventlist' ); ?>">
		<?php esc_html_e( 'Ajouter une galerie', 'eventlist' ); ?>
	</a>



</div>


<!-- Single Banner -->
<div class="wrap_single_banner vendor_field" style="display:none">
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