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
    
        <!-- Image Feature -->
    <div class="image_feature vendor_field">
        <label>
            <?php esc_html_e( 'Image à la une', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 1920x739px', 'eventlist' ); ?></p>

        <div class="wrap_image_upload">
            <?php if ( get_the_post_thumbnail_url($post_id) ) { ?>
                <div class="image_box">
                    <img class="image-preview" src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id ) ); ?>" alt="#">
                    <a class="button remove_image" href="#"><i class="icon_close" aria-hidden="true"></i></a>
                </div>
            <?php } ?>
            
            <a class="button add_image el_btn_add_media" href="#" data-uploader-title="<?php esc_attr_e( "Ajouter une image", 'eventlist' ); ?>" data-uploader-button-text="<?php esc_attr_e( "Ajouter", 'eventlist' ); ?>">
                <i class="icon_camera_alt"></i> <?php esc_html_e( "Ajouter une image", 'eventlist' ); ?>
            </a>
            <input type="hidden" name="img_thumbnail" class="img_thumbnail" id="img_thumbnail" value="<?php echo esc_attr( get_post_thumbnail_id( $post_id ) ); ?>">
        </div>
    </div>

    <hr class="el_separator">

    
    
    
    <p class="field_description">
        <?php esc_html_e( 'Détaillez votre activité et ajoutez des visuels attractifs.', 'eventlist' ); ?>
    </p>

    <!-- Description -->
    <div class="vendor_field">
        <label class="ova_desc" for="content_event">
            <?php esc_html_e( 'Description de l\'événement', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
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

    <!-- Gallery -->
    <div id="mb_gallery" class="vendor_field">
        <label>
            <?php esc_html_e( 'Galerie d\'images', 'eventlist' ); ?>
        </label>
        <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 710x480px', 'eventlist' ); ?></p>
        
        <?php echo el_get_template( '/vendor/__edit-event-gallery.php', array('post_id' => $post_id) ); ?>
    </div>

    <hr class="el_separator">

    <!-- Video -->
    <div class="vendor_field">
        <label for="event_video"><?php esc_html_e( 'Lien URL d’une vidéo', 'eventlist' ); ?></label>
        <input type="url" id="event_video" name="<?php echo esc_attr( $_prefix.'event_video' ); ?>" value="<?php echo esc_url( $event_video ); ?>" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
        <p class="field_hint"><?php esc_html_e( 'La vidéo sera visible dans la galerie d’image.', 'eventlist' ); ?></p>
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
                            <div class="social_item el_row" style="margin-top: 10px; align-items: center;">
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
                                <div class="el_col_1" style="display:flex;align-items:center;">
                                    <a href="#" class="button remove_social" style="color:red;font-size:18px;">×</a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } ?>
            </div>
            <a href="#" class="button add_social btn_add_co_organizer" style="display:inline-block; text-decoration:none;">
                <?php esc_html_e( 'Ajouter un réseau social', 'eventlist' ); ?>
            </a>
        </div>
    </div>

</div>
