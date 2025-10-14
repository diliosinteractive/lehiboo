<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Présentation de l'événement
 * Contient: Description, Image à la une, Galerie, Vidéo, Contact/Organisateur
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$info_organizer   = get_post_meta( $post_id, $_prefix.'info_organizer', true) ? get_post_meta( $post_id, $_prefix.'info_organizer', true) : '';
$name_organizer   = get_post_meta( $post_id, $_prefix.'name_organizer', true) ? get_post_meta( $post_id, $_prefix.'name_organizer', true) : '';
$phone_organizer  = get_post_meta( $post_id, $_prefix.'phone_organizer', true) ? get_post_meta( $post_id, $_prefix.'phone_organizer', true) : '';
$mail_organizer   = get_post_meta( $post_id, $_prefix.'mail_organizer', true) ? get_post_meta( $post_id, $_prefix.'mail_organizer', true) : '';
$job_organizer    = get_post_meta( $post_id, $_prefix.'job_organizer', true) ? get_post_meta( $post_id, $_prefix.'job_organizer', true) : '';
$social_organizer = get_post_meta( $post_id, $_prefix.'social_organizer', true) ? get_post_meta( $post_id, $_prefix.'social_organizer', true) : '';

?>

<!-- Description -->
<div class="event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Description', 'eventlist' ); ?></h4>

	<div class="vendor_field">
		<label class="ova_desc">
			<?php esc_html_e( 'Description de l\'événement', 'eventlist' ); ?>

			<?php if ( apply_filters( 'el_description_req', false, $args ) == true ): ?>
				<span class="el_req">*</span>
			<?php endif ?>
		</label>
		<?php

		$settings_editor = array(
			'textarea_name' => 'el_content_event',
			'media_buttons' => apply_filters( 'el_vendor_add_media_content_event', false ),
			'textarea_rows' => 10,
			'editor_height' => 230,
			'wpautop' 		=> false,
		);
		?>
		<?php if ( $post_id != '' ) {

			wp_editor( wpautop( get_post_field( 'post_content', $post_id ) ), 'content_event', $settings_editor );
		} else {
			wp_editor( wpautop( '' ), 'content_event', $settings_editor );
		} ?>
	</div>

</div>

<!-- Image Feature -->
<div class="image_feature event_basic_block">

	<h4 class="heading_section">
		<?php esc_html_e( 'Image à la une', 'eventlist' ); ?>

		<?php if ( apply_filters( 'el_image_feature_req', false, $args ) == true ): ?>
			<span class="el_req">*</span>
		<?php endif; ?>

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

		<?php if ( apply_filters( 'el_gallery_req', false, $args ) == true ): ?>
			<span class="el_req">*</span>
		<?php endif; ?>

		<span class="el_icon_help dashicons dashicons-editor-help"
		data-tippy-content="<?php esc_attr_e( 'Taille recommandée: 710x480px', 'eventlist' ); ?>"></span>
	</h4>

	<?php echo el_get_template( '/vendor/__edit-event-gallery.php', $args ); ?>
</div>

<!-- Contact -->
<div class="contact event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Informations de contact', 'eventlist' ); ?></h4>

	<div class="vendor_field">
		<label for="info_organizer" class="el_input_checkbox">
			<?php esc_html_e( 'Remplacer les infos de votre profil', 'eventlist' ); ?>
			<input type="checkbox" id="info_organizer" class="info_organizer" name="<?php echo esc_attr( $_prefix.'info_organizer' ) ?>" value="<?php echo esc_attr( $info_organizer ); ?>" <?php echo esc_attr( $info_organizer ); ?> >
			<span class="checkmark"></span>
		</label>

	</div>


	<div id="show_rewrite" style="display: none">
		<div class="info">
			<div class="name vendor_field">
				<label for="name_organizer" ><?php esc_html_e( 'Nom:', 'eventlist' ); ?></label>
				<input type="text" id="name_organizer" class="name_organizer" name="<?php echo esc_attr( $_prefix.'name_organizer' ) ?>" value="<?php echo esc_attr( $name_organizer ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			</div>

			<div class="phone vendor_field">
				<label for="phone_organizer" ><?php esc_html_e( 'Téléphone:', 'eventlist' ); ?></label>
				<input type="text" id="phone_organizer" class="phone_organizer" name="<?php echo esc_attr( $_prefix.'phone_organizer' ) ?>" value="<?php echo esc_attr( $phone_organizer ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			</div>

			<div class="mail vendor_field">
				<label for="mail_organizer" ><?php esc_html_e( 'E-mail:', 'eventlist' ); ?></label>
				<input type="text" id="mail_organizer" class="mail_organizer" name="<?php echo esc_attr( $_prefix.'mail_organizer' ) ?>" value="<?php echo esc_attr( $mail_organizer ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			</div>

			<div class="job vendor_field">
				<label for="job_organizer" ><?php esc_html_e( 'Fonction:', 'eventlist' ); ?></label>
				<input type="text" id="job_organizer" class="job_organizer" name="<?php echo esc_attr( $_prefix.'job_organizer' ) ?>" value="<?php echo esc_attr( $job_organizer ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			</div>
		</div>

		<div id="social_organizer">
			<label class="label"><strong><?php esc_html_e( 'Réseaux sociaux', 'eventlist' ); ?>: </strong></label>
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
</div>
