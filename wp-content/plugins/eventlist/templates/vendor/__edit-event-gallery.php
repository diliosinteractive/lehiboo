<?php if ( !defined( 'ABSPATH' ) ) exit();


$post_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';

$_prefix = OVA_METABOX_EVENT;

$gallery = get_post_meta( $post_id, $_prefix.'gallery', true) ? get_post_meta( $post_id, $_prefix.'gallery', true) : '';


$link_video = get_post_meta( $post_id, $_prefix.'link_video', true) ? get_post_meta( $post_id, $_prefix.'link_video', true) : '';


$single_banner = get_post_meta( $post_id, $_prefix.'single_banner', true) ? get_post_meta( $post_id, $_prefix.'single_banner', true) : 'thumbnail';
$image_banner = get_post_meta( $post_id, $_prefix.'image_banner', true) ? get_post_meta( $post_id, $_prefix.'image_banner', true) : '';
?>

<!-- Image Gallery -->
<div class="event_gallery_wrapper">
    <!-- Zone de dépôt des images triables -->
    <div class="gallery_grid_sortable <?php echo empty($gallery) ? 'is_empty' : ''; ?>" id="gallery_sortable">
        <?php if ( $gallery ) : foreach ( $gallery as $key => $value ) :
            $image = wp_get_attachment_image_src( $value, 'medium' );
            $full_image = wp_get_attachment_image_src( $value, 'full' );
            if (!$image) continue;
        ?>
            <div class="gallery_item" data-id="<?php echo esc_attr($value); ?>">
                <input type="hidden" name="<?php echo esc_attr($_prefix); ?>gallery[]" value="<?php echo esc_attr($value); ?>">
                <div class="gallery_item_thumb">
                    <img src="<?php echo esc_url($image[0]); ?>" alt="">
                    <div class="gallery_item_overlay">
                        <button type="button" class="btn_gallery_remove" title="<?php esc_attr_e('Supprimer', 'eventlist'); ?>">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="gallery_item_drag">
                        <i class="fa fa-grip-vertical"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>

        <!-- État vide -->
        <div class="gallery_empty_state">
            <i class="fa fa-images"></i>
            <p><?php esc_html_e( 'Aucune image dans la galerie', 'eventlist' ); ?></p>
            <span><?php esc_html_e( 'Ajoutez des images pour créer votre galerie', 'eventlist' ); ?></span>
        </div>
    </div>

    <!-- Bouton d'ajout -->
    <button type="button" class="el_button btn_pick_gallery_images gallery_add_btn">
        <i class="fa fa-plus"></i> <?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
    </button>

    <p class="gallery_hint">
        <i class="fa fa-info-circle"></i>
        <?php esc_html_e( 'Glissez-déposez les images pour les réorganiser', 'eventlist' ); ?>
    </p>
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