<?php
/**
 * Grille d'images
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="media_grid_container">

    <!-- Loader -->
    <div class="media_loader" style="display: none;">
        <div class="loader_spinner">
            <i class="fa fa-spinner fa-spin"></i>
        </div>
        <p><?php esc_html_e( 'Chargement...', 'eventlist' ); ?></p>
    </div>

    <!-- Grille -->
    <div class="media_grid" data-view="grid">
        <!-- Sera rempli par JavaScript via AJAX -->
    </div>

    <!-- Empty state -->
    <div class="media_empty" style="display: none;">
        <div class="empty_icon">
            <i class="fa fa-image"></i>
        </div>
        <h3><?php esc_html_e( 'Aucune image', 'eventlist' ); ?></h3>
        <p><?php esc_html_e( 'Ce dossier ne contient aucune image pour le moment', 'eventlist' ); ?></p>
        <button type="button" class="el_button el_button_primary btn_upload_first">
            <i class="fa fa-cloud-upload"></i>
            <?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
        </button>
    </div>

</div>

<!-- Template pour un item image (utilisé par JavaScript) -->
<script type="text/template" id="tmpl-media-item">
    <div class="media_item" data-id="{{attachment_id}}" data-folder="{{folder_id}}">
        <div class="media_item_checkbox">
            <input type="checkbox" class="item_select" value="{{attachment_id}}">
        </div>
        <div class="media_item_thumb">
            <img src="{{thumb}}" alt="{{title}}" loading="lazy">
            <div class="media_item_overlay">
                <button type="button" class="overlay_btn btn_view" title="<?php esc_attr_e( 'Voir', 'eventlist' ); ?>">
                    <i class="fa fa-eye"></i>
                </button>
                <button type="button" class="overlay_btn btn_edit" title="<?php esc_attr_e( 'Éditer', 'eventlist' ); ?>">
                    <i class="fa fa-edit"></i>
                </button>
                <button type="button" class="overlay_btn btn_delete" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="media_item_info">
            <h5 class="item_title" title="{{title}}">{{title}}</h5>
            <div class="item_meta">
                <span class="meta_size">{{size}}</span>
            </div>
        </div>
    </div>
</script>

<!-- Template pour un item en vue liste -->
<script type="text/template" id="tmpl-media-item-list">
    <div class="media_item_list" data-id="{{attachment_id}}" data-folder="{{folder_id}}">
        <div class="item_list_checkbox">
            <input type="checkbox" class="item_select" value="{{attachment_id}}">
        </div>
        <div class="item_list_thumb">
            <img src="{{thumb}}" alt="{{title}}" loading="lazy">
        </div>
        <div class="item_list_info">
            <h5 class="item_title">{{title}}</h5>
            <span class="item_size">{{size}}</span>
            <span class="item_date">{{date}}</span>
        </div>
        <div class="item_list_actions">
            <button type="button" class="action_btn btn_view" title="<?php esc_attr_e( 'Voir', 'eventlist' ); ?>">
                <i class="fa fa-eye"></i>
            </button>
            <button type="button" class="action_btn btn_move" title="<?php esc_attr_e( 'Déplacer', 'eventlist' ); ?>">
                <i class="fa fa-arrows-alt"></i>
            </button>
            <button type="button" class="action_btn btn_delete" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</script>

<!-- Template pour le preview d'upload -->
<script type="text/template" id="tmpl-upload-preview-item">
    <div class="preview_item" data-file-index="{{index}}">
        <div class="preview_thumb">
            <img src="{{preview}}" alt="{{name}}">
        </div>
        <div class="preview_info">
            <h5 class="preview_name">{{name}}</h5>
            <span class="preview_size">{{size}}</span>
            <div class="preview_progress">
                <div class="progress_bar">
                    <div class="progress_fill" style="width: 0%;"></div>
                </div>
                <span class="progress_percent">0%</span>
            </div>
            <span class="preview_status"></span>
        </div>
        <button type="button" class="preview_remove" title="<?php esc_attr_e( 'Annuler', 'eventlist' ); ?>">
            <i class="fa fa-times"></i>
        </button>
    </div>
</script>
