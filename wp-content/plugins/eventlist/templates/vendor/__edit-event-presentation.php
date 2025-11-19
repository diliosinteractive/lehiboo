<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Présentation de l'événement
 * Contient: Description, Image à la une, Galerie, Vidéo, Réseaux sociaux
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$social_organizer = get_post_meta( $post_id, $_prefix.'social_organizer', true) ? get_post_meta( $post_id, $_prefix.'social_organizer', true) : '';
$event_video = get_post_meta( $post_id, $_prefix.'event_video', true) ? get_post_meta( $post_id, $_prefix.'event_video', true) : '';

?>

<!-- Description -->
<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Description', 'eventlist' ); ?></h4>

	<div class="vendor_field">
		<label class="ova_desc">
			<?php esc_html_e( 'Description de l\'événement', 'eventlist' ); ?>
			<span class="el_req">*</span>
		</label>
		<p class="field_description">
			<?php esc_html_e( 'Pour garantir une description complète et percutante, nous vous conseillons vivement d\'atteindre un minimum de 500 caractères.', 'eventlist' ); ?>
		</p>
		<?php

		$settings_editor = array(
			'textarea_name' => 'el_content_event',
			'media_buttons' => apply_filters( 'el_vendor_add_media_content_event', false ),
			'textarea_rows' => 10,
			'editor_height' => 230,
			'wpautop' 		=> false,
			'tinymce'       => array(
				'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,undo,redo',
				'toolbar2' => '',
			),
		);
		?>
		<?php if ( $post_id != '' ) {
			wp_editor( wpautop( get_post_field( 'post_content', $post_id ) ), 'content_event', $settings_editor );
		} else {
			wp_editor( wpautop( '' ), 'content_event', $settings_editor );
		} ?>
		<div class="char_counter_wrapper">
			<span id="desc_char_count">0</span> <?php esc_html_e( 'caractères', 'eventlist' ); ?>
		</div>
	</div>

</div>

<!-- Image Feature -->
<div class="image_feature event_basic_block">

	<h4 class="heading_section">
		<?php esc_html_e( 'Image à la une', 'eventlist' ); ?>
		<span class="el_req">*</span>
		<span class="el_icon_help dashicons dashicons-editor-help"
		data-tippy-content="<?php esc_attr_e( 'Taille recommandée: 1920x739px', 'eventlist' ); ?>"></span>
	</h4>

	<div class="wrap">
		<?php if ( get_the_post_thumbnail_url($post_id) ) { ?>
			<div class="image_box">
				<img class="image-preview" src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id ) ); ?>" alt="#">
				<a class="button remove_image" href="#"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
			</div>
		<?php } ?>
	</div>

	<div class="vendor_field">
		<a class="button add_image el_btn_add" href="#" data-uploader-title="<?php esc_attr_e( "Ajouter une image", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Ajouter", 'eventlist' ); ?>"><?php esc_html_e( "Ajouter une image", 'eventlist' ); ?></a>
		<input type="hidden" name="img_thumbnail" class="img_thumbnail" id="img_thumbnail" value="<?php echo esc_attr( get_post_thumbnail_id( $post_id ) ); ?>">
	</div>

</div>

<!-- Gallery -->
<div id="mb_gallery" class="event_basic_block">
	<h4 class="heading_section">
		<?php esc_html_e( 'Galerie d\'images', 'eventlist' ); ?>
		<span class="el_icon_help dashicons dashicons-editor-help"
		data-tippy-content="<?php esc_attr_e( 'Taille recommandée: 710x480px', 'eventlist' ); ?>"></span>
	</h4>

	<?php echo el_get_template( '/vendor/__edit-event-gallery.php', $args ); ?>
</div>

<!-- Video -->
<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Page Vidéo', 'eventlist' ); ?></h4>
	<div class="vendor_field">
		<label for="event_video"><?php esc_html_e( 'Lien URL d’une vidéo', 'eventlist' ); ?></label>
		<input type="url" id="event_video" name="<?php echo esc_attr( $_prefix.'event_video' ); ?>" value="<?php echo esc_url( $event_video ); ?>" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
		<p class="field_hint"><?php esc_html_e( 'La vidéo sera visible dans la galerie d’image.', 'eventlist' ); ?></p>
	</div>
</div>

<!-- Social Networks -->
<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Réseaux sociaux', 'eventlist' ); ?></h4>
	
	<div id="social_organizer">
		<div id="social_list">
			<?php if ($social_organizer) {
				foreach ($social_organizer as $key => $value) {
					if ($value['link_social'] != '') { ?>
						<div class="social_item vendor_field">
							<input type="text" name="<?php echo esc_attr( OVA_METABOX_EVENT.'social_organizer['.$key.'][link_social]' ); ?>" value="<?php echo esc_attr($value['link_social']); ?>" class="link_social" placeholder="<?php echo esc_attr( 'https://' ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">

							<select name="<?php echo esc_attr( OVA_METABOX_EVENT.'social_organizer['.$key.'][icon_social]' ); ?>" class="icon_social">
								<?php foreach (el_get_social() as $key_icon_social => $value_icon_social) { ?>
									<option value="<?php echo esc_attr($key_icon_social); ?>" <?php echo esc_attr($value['icon_social'] == $key_icon_social ? 'selected' : ''); ?>><?php esc_html_e( $value_icon_social, 'eventlist' ); ?></option>
								<?php } ?>
							</select>
							<a href="#" class="button remove_social"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
						</div>
						<?php
					}
				}
			} ?>
		</div>
		<a href="#" class="button add_social el_btn_add"><i class="icon_plus"></i>&nbsp;<?php esc_html_e( 'Ajouter un réseau social', 'eventlist' ); ?></a>
	</div>
</div>
