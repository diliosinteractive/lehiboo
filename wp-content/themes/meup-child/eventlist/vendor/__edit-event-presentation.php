<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Présentation de l'événement
 * V1 Le Hiboo - Override avec éditeur réduit et compteur de caractères fonctionnel
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
            <span class="el_req">*</span>
        </label>

        <p class="field_hint">
            <?php esc_html_e( "Pour garantir une description complète et percutante, nous vous conseillons vivement d'atteindre un minimum de 500 caractères. Plus votre description sera détaillée, plus elle sera efficace.", 'eventlist' ); ?>
        </p>

        <!-- V1 Le Hiboo - AI Description Generator Button -->
        <div class="el_ai_generator_wrapper">
            <button type="button" id="el-generate-ai-description" class="el_ai_generate_btn">
                <svg class="ai-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
                <svg class="ai-loader" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20"/>
                </svg>
                <span class="btn-text"><?php esc_html_e( 'Générer avec l\'IA', 'eventlist' ); ?></span>
                <span class="btn-loading-text"><?php esc_html_e( 'Génération...', 'eventlist' ); ?></span>
            </button>
            <span class="el_ai_hint"><?php esc_html_e( 'Remplissez d\'abord les informations générales pour une meilleure génération', 'eventlist' ); ?></span>
        </div>

        <?php
        // V1 Le Hiboo - WYSIWYG réduit (hauteur diminuée)
        $settings_editor = array(
            'textarea_name' => 'el_content_event',
            'media_buttons' => false,
            'textarea_rows' => 8,
            'editor_height' => 180,
            'wpautop'       => false,
            'tinymce'       => array(
                'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,alignleft,aligncenter,alignright,alignjustify,|,link,unlink,|,undo,redo',
                'toolbar2' => '',
                'block_formats' => 'Paragraphe=p;Titre 2=h2;Titre 3=h3;Titre 4=h4',
                'setup' => 'function(editor) {
                    editor.on("keyup change", function() {
                        window.updateDescCharCount && window.updateDescCharCount();
                    });
                }',
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

    <div class="el_row el_images_row">

        <!-- Image Feature -->
        <div class="el_col_6 image_feature vendor_field">
            <label>
                <?php esc_html_e( 'Image de présentation', 'eventlist' ); ?>
                <span class="el_req">*</span>
            </label>
            <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 1080x1920px (format portrait 9/16)', 'eventlist' ); ?></p>

            <?php
            $thumbnail_id = get_post_thumbnail_id( $post_id );
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium_large');
            ?>
            <div class="featured_image_zone featured_image_portrait <?php echo $thumbnail_url ? 'has_image' : ''; ?>">
                <!-- Zone de dépôt / sélection -->
                <div class="featured_dropzone btn_pick_featured_image">
                    <div class="dropzone_inner">
                        <i class="fa fa-cloud-upload-alt"></i>
                        <p><?php esc_html_e( "Cliquez ou glissez une image ici", 'eventlist' ); ?></p>
                        <span><?php esc_html_e( "JPG, PNG, WebP - Format portrait 9/16", 'eventlist' ); ?></span>
                    </div>
                </div>

                <!-- Aperçu de l'image -->
                <div class="featured_image_preview">
                    <?php if ( $thumbnail_url ) { ?>
                        <img class="image-preview" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="#">
                    <?php } ?>
                    <div class="featured_preview_overlay">
                        <button type="button" class="btn_change_featured btn_pick_featured_image" title="<?php esc_attr_e( 'Changer', 'eventlist' ); ?>">
                            <i class="fa fa-sync-alt"></i> <?php esc_html_e( 'Changer', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="btn_remove_featured" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="img_thumbnail" class="img_thumbnail" id="img_thumbnail" value="<?php echo esc_attr( $thumbnail_id ); ?>">
            </div>
        </div>

        <!-- Séparation verticale -->
        <div class="el_col_separator"></div>

        <!-- Gallery -->
        <div id="mb_gallery" class="el_col_6 vendor_field">
            <div class="gallery_title_row">
                <label>
                    <?php esc_html_e( 'Galerie', 'eventlist' ); ?>
                </label>
                <button type="button" class="el_button btn_pick_gallery_images gallery_add_btn_inline">
                    <i class="fa fa-plus"></i> <?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
                </button>
            </div>
            <p class="field_hint"><?php esc_html_e( 'Taille recommandée: 710x480px', 'eventlist' ); ?></p>

            <?php echo el_get_template( '/vendor/__edit-event-gallery.php', array('post_id' => $post_id) ); ?>
        </div>

    </div>



    <hr class="el_separator">

    <!-- Video -->
    <div class="vendor_field">
        <label for="event_video"><?php esc_html_e( "Lien URL d'une vidéo sur une plateforme streaming.", 'eventlist' ); ?></label>
        <p class="field_hint"><?php esc_html_e( "La vidéo sera visible dans la galerie d'image.", 'eventlist' ); ?></p>
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

<!-- V1 Le Hiboo - Character Counter + AI Generator Scripts -->
<script>
jQuery(document).ready(function($) {
    'use strict';

    /**
     * Character Counter - Update function
     */
    window.updateDescCharCount = function() {
        var charCount = 0;

        // Try TinyMCE first
        if (typeof tinymce !== 'undefined' && tinymce.get('content_event')) {
            var editor = tinymce.get('content_event');
            var content = editor.getContent({ format: 'text' });
            charCount = content.replace(/\s+/g, ' ').trim().length;
        } else {
            // Fallback to textarea
            var content = $('#content_event').val() || '';
            // Strip HTML tags for plain text count
            content = content.replace(/<[^>]*>/g, '');
            charCount = content.replace(/\s+/g, ' ').trim().length;
        }

        $('#desc_char_count').text(charCount);

        // Visual feedback based on count
        var $counter = $('.char_counter_wrapper');
        if (charCount >= 500) {
            $counter.css('color', '#16a34a');
        } else if (charCount >= 250) {
            $counter.css('color', '#d97706');
        } else {
            $counter.css('color', '#717171');
        }
    };

    /**
     * Initialize character counter when TinyMCE is ready
     */
    function initCharCounter() {
        if (typeof tinymce !== 'undefined') {
            tinymce.on('AddEditor', function(e) {
                if (e.editor.id === 'content_event') {
                    e.editor.on('init', function() {
                        window.updateDescCharCount();
                    });
                    e.editor.on('keyup change input NodeChange', function() {
                        window.updateDescCharCount();
                    });
                }
            });

            // Also check if editor already exists
            var existingEditor = tinymce.get('content_event');
            if (existingEditor) {
                existingEditor.on('keyup change input NodeChange', function() {
                    window.updateDescCharCount();
                });
                window.updateDescCharCount();
            }
        }

        // Also listen to textarea changes (when in Text mode)
        $('#content_event').on('keyup change input', function() {
            window.updateDescCharCount();
        });
    }

    // Initialize after a short delay to ensure TinyMCE is loaded
    setTimeout(initCharCounter, 500);

    // Also try immediately
    initCharCounter();

    /**
     * AI Description Generator
     */
    var $generateBtn = $('#el-generate-ai-description');
    var isGenerating = false;

    function collectFormData() {
        var data = {};
        data.title = $('input[name="name_event"]').val() || '';
        var $categorySelect = $('select[name="event_cat"]');
        data.category = $categorySelect.find('option:selected').text() || '';

        var eventTypes = [];
        $('select[name="event_tag[]"] option:selected').each(function() {
            var text = $(this).text().trim();
            if (text && text !== '--- Sélectionner ---') eventTypes.push(text);
        });
        data.event_types = eventTypes.join(', ');

        var targetAudience = [];
        $('select[name="event_public[]"] option:selected').each(function() {
            var text = $(this).text().trim();
            if (text && text !== '--- Sélectionner ---') targetAudience.push(text);
        });
        data.target_audience = targetAudience.join(', ');

        var themes = [];
        $('select[name="event_thematique[]"] option:selected').each(function() {
            var text = $(this).text().trim();
            if (text && text !== '--- Sélectionner ---') themes.push(text);
        });
        data.themes = themes.join(', ');

        var events = [];
        $('select[name="event_special[]"] option:selected').each(function() {
            var text = $(this).text().trim();
            if (text && text !== '--- Sélectionner ---') events.push(text);
        });
        data.events = events.join(', ');

        var emotions = [];
        $('select[name="event_emotion[]"] option:selected').each(function() {
            var text = $(this).text().trim();
            if (text && text !== '--- Sélectionner ---') emotions.push(text);
        });
        data.emotions = emotions.join(', ');

        var locationParts = [];
        var address = $('input[name="ova_mb_event_map_address"]').val() || $('input[name="map_address"]').val();
        var city = $('input[name="ova_mb_event_map_city"]').val() || $('input[name="map_city"]').val();
        if (address) locationParts.push(address);
        if (city) locationParts.push(city);
        data.location = locationParts.join(', ');

        var dates = [];
        $('.calendar_item, .schedule-item').each(function() {
            var date = $(this).find('input[type="date"], .date-display').first().val() || $(this).find('.date-display').text();
            if (date) dates.push(date);
        });
        data.dates = dates.slice(0, 3).join(', ') + (dates.length > 3 ? '...' : '');

        var prices = [];
        $('.ticket_item, .ticket-row').each(function() {
            var name = $(this).find('input[name*="name_ticket"]').val() || $(this).find('.ticket-name').text();
            var price = $(this).find('input[name*="price_ticket"]').val() || $(this).find('.ticket-price').text();
            if (name && price) {
                prices.push(name + ': ' + price + '€');
            }
        });
        data.prices = prices.slice(0, 3).join(', ');

        var services = [];
        if ($('input[name*="el_handicap"]:checked').length) services.push('Accessible handicap');
        if ($('input[name*="el_animal"]:checked').length) services.push('Animaux acceptés');
        if ($('input[name*="el_baby"]:checked').length) services.push('Adapté aux bébés');
        if ($('input[name*="el_wifi"]:checked').length) services.push('Wifi gratuit');
        if ($('input[name*="el_parking"]:checked').length) services.push('Parking');
        if ($('input[name*="el_restau"]:checked').length) services.push('Restauration');
        data.services = services.join(', ');

        return data;
    }

    function insertIntoEditor(content) {
        if (typeof tinymce !== 'undefined' && tinymce.get('content_event')) {
            var editor = tinymce.get('content_event');
            editor.setContent(content);
            editor.fire('change');
        } else {
            $('#content_event').val(content);
        }
        setTimeout(function() {
            window.updateDescCharCount();
        }, 100);
    }

    $generateBtn.on('click', function(e) {
        e.preventDefault();
        if (isGenerating) return;

        var formData = collectFormData();

        if (!formData.title) {
            alert('<?php echo esc_js( __( "Veuillez d\'abord renseigner le nom de l\'activité dans les Informations générales.", "eventlist" ) ); ?>');
            return;
        }

        isGenerating = true;
        $generateBtn.addClass('loading');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'el_generate_ai_description',
                nonce: ajax_object.nonce,
                title: formData.title,
                category: formData.category,
                event_types: formData.event_types,
                target_audience: formData.target_audience,
                themes: formData.themes,
                events: formData.events,
                emotions: formData.emotions,
                location: formData.location,
                dates: formData.dates,
                prices: formData.prices,
                services: formData.services
            },
            success: function(response) {
                if (response.success) {
                    insertIntoEditor(response.data.description);
                    $generateBtn.addClass('success');
                    setTimeout(function() {
                        $generateBtn.removeClass('success');
                    }, 2000);
                } else {
                    alert(response.data.message || '<?php echo esc_js( __( "Erreur lors de la génération", "eventlist" ) ); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js( __( "Erreur de connexion. Veuillez réessayer.", "eventlist" ) ); ?>');
            },
            complete: function() {
                isGenerating = false;
                $generateBtn.removeClass('loading');
            }
        });
    });
});
</script>
