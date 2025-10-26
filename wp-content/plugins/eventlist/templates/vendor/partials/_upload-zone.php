<?php
/**
 * Zone d'upload drag & drop
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$max_file_size = apply_filters( 'el_vendor_media_max_size', 10 * 1024 * 1024 ); // 10MB
$max_file_size_mb = $max_file_size / ( 1024 * 1024 );
?>

<div class="upload_zone" style="display: none;">
    <div class="upload_dropzone">
        <div class="dropzone_icon">
            <i class="fa fa-cloud-upload"></i>
        </div>
        <div class="dropzone_text">
            <h4><?php esc_html_e( 'Glissez vos fichiers ici', 'eventlist' ); ?></h4>
            <p><?php esc_html_e( 'ou cliquez pour parcourir', 'eventlist' ); ?></p>
        </div>
        <div class="dropzone_info">
            <span class="info_formats">
                <?php esc_html_e( 'Formats autorisés: JPG, PNG, GIF, WebP', 'eventlist' ); ?>
            </span>
            <span class="info_size">
                <?php printf( esc_html__( 'Taille max: %s MB par fichier', 'eventlist' ), $max_file_size_mb ); ?>
            </span>
        </div>
        <input type="file" class="upload_input" multiple accept="image/*" style="display: none;">
    </div>

    <!-- Preview des fichiers en cours d'upload -->
    <div class="upload_preview" style="display: none;">
        <div class="preview_header">
            <h4><?php esc_html_e( 'Fichiers en cours d\'upload', 'eventlist' ); ?></h4>
            <button type="button" class="btn_close_upload">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="preview_list">
            <!-- Sera rempli par JavaScript -->
        </div>
    </div>
</div>
