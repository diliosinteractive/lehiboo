<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Présentation de l'événement
 * Refactored to match "Airbnb" style
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$social_organizer = get_post_meta( $post_id, $_prefix.'social_organizer', true) ? get_post_meta( $post_id, $_prefix.'social_organizer', true) : '';
$event_video = get_post_meta( $post_id, $_prefix.'event_video', true) ? get_post_meta( $post_id, $_prefix.'event_video', true) : '';

?>

<div class="event_basic_block">
    <h4 class="heading_section"><?php esc_html_e( 'Présentation', 'eventlist' ); ?></h4>
       
    <p class="field_description">
        <?php esc_html_e( 'Présentez votre événement avec du texte, des images et une vidéo.', 'eventlist' ); ?>
    </p>

    <!-- Description -->
    <div class="vendor_field">
        <label class="ova_desc" for="content_event">
            <?php esc_html_e( 'Description', 'eventlist' ); ?>
        </label>

        <p class="field_hint">
            <?php esc_html_e( "Pour garantir une description complète et percutante, nous vous conseillons vivement d'atteindre un minimum de 500 caractères. Plus votre description sera détaillée, plus elle sera efficace.", 'eventlist' ); ?>
        </p>
        <?php
        // V1 Le Hiboo - WYSIWYG amélioré avec plus d'options
        $settings_editor = array(
            'textarea_name' => 'el_content_event',
            'media_buttons' => false,
            'textarea_rows' => 15,
            'editor_height' => 350,
            'wpautop'       => false,
            'tinymce'       => array(
                'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,alignleft,aligncenter,alignright,alignjustify,|,link,unlink,|,undo,redo',
                'toolbar2' => 'forecolor,backcolor,|,hr,blockquote,|,removeformat,pastetext,|,charmap',
                'block_formats' => 'Paragraphe=p;Titre 2=h2;Titre 3=h3;Titre 4=h4;Préformaté=pre',
            ),
        );
        ?>
        <div class="el_editor_wrapper el_editor_no_border">
            <?php 
            $content = ($post_id != '') ? get_post_field('post_content', $post_id) : '';
            wp_editor( wpautop($content), 'content_event', $settings_editor ); 
            ?>
        </div>
        <div class="char_counter_wrapper" style="text-align: right; font-size: 12px; color: #717171; margin-top: 5px;">
            <span id="desc_char_count">0</span> <?php esc_html_e( 'caractères', 'eventlist' ); ?>
        </div>
    </div>

    <hr class="el_separator">
        <!-- Image Feature -->
    <div class="image_feature vendor_field">
        <label>
            <?php esc_html_e( 'Image de présentation', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 1920x739px', 'eventlist' ); ?></p>

        <div class="wrap_image_upload featured_image_wrapper">
            <?php
            $thumbnail_id = get_post_thumbnail_id( $post_id );
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium_large');
            ?>
            <div class="featured_image_preview <?php echo $thumbnail_url ? 'has_image' : ''; ?>">
                <?php if ( $thumbnail_url ) { ?>
                    <img class="image-preview" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="#">
                    <button type="button" class="btn_remove_featured" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
                        <i class="fa fa-times"></i>
                    </button>
                <?php } ?>
            </div>

            <button type="button" class="el_button btn_pick_featured_image">
                <i class="fa fa-image"></i> <?php esc_html_e( "Choisir une image", 'eventlist' ); ?>
            </button>
            <input type="hidden" name="img_thumbnail" class="img_thumbnail" id="img_thumbnail" value="<?php echo esc_attr( $thumbnail_id ); ?>">
        </div>
    </div>

    <hr class="el_separator">

    <!-- Gallery -->
    <div id="mb_gallery" class="vendor_field">
        <label>
            <?php esc_html_e( 'Image Galerie', 'eventlist' ); ?>
        </label>
        <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 710x480px', 'eventlist' ); ?></p>
        
        <?php echo el_get_template( '/vendor/__edit-event-gallery.php', array('post_id' => $post_id) ); ?>
    </div>

    <hr class="el_separator">

    <!-- Video -->
    <div class="vendor_field">
        <label for="event_video"><?php esc_html_e( 'Lien URL d’une vidéo sur une plateforme streaming.', 'eventlist' ); ?></label>
        <p class="field_hint"><?php esc_html_e( 'La vidéo sera visible dans la galerie d’image.', 'eventlist' ); ?></p>
        <input type="url" id="event_video" name="<?php echo esc_attr( $_prefix.'event_video' ); ?>" value="<?php echo esc_url( $event_video ); ?>" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
    </div>

    <hr class="el_separator">

    <!-- Social Networks -->
    <div class="vendor_field">
        <label class="co_organizer_label"><?php esc_html_e( 'Réseaux sociaux', 'eventlist' ); ?></label>
        
        <div id="social_organizer">
            <div id="social_list">
                <?php if ($social_organizer) {
                    foreach ($social_organizer as $key => $value) {
                        if ($value['link_social'] != '') { ?>
                            <div class="social_item el_row">
                                <div class="el_col_3">
                                    <select name="<?php echo esc_attr( OVA_METABOX_EVENT.'social_organizer['.$key.'][icon_social]' ); ?>" class="icon_social selectpicker">
                                        <?php foreach (el_get_social() as $key_icon_social => $value_icon_social) { ?>
                                            <option value="<?php echo esc_attr($key_icon_social); ?>" <?php echo esc_attr($value['icon_social'] == $key_icon_social ? 'selected' : ''); ?>><?php esc_html_e( $value_icon_social, 'eventlist' ); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="el_col_8">
                                    <input type="text" name="<?php echo esc_attr( OVA_METABOX_EVENT.'social_organizer['.$key.'][link_social]' ); ?>" value="<?php echo esc_attr($value['link_social']); ?>" class="link_social" placeholder="https://">
                                </div>
                                <div class="el_col_1">
                                    <a href="#" class="button remove_social">×</a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } ?>
            </div>
            <a href="#" class="button add_social btn_add_co_organizer">
                <?php esc_html_e( 'Ajouter un réseau social', 'eventlist' ); ?>
            </a>
        </div>
    </div>

</div>
