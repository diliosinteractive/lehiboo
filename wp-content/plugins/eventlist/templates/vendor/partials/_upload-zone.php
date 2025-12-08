<?php
/**
 * Zone d'upload drag & drop
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$max_file_size = apply_filters( 'el_vendor_media_max_size', 10 * 1024 * 1024 ); // 10MB
$max_file_size_mb = $max_file_size / ( 1024 * 1024 );
?>

<!-- Zone d'upload en cours (visible pendant l'upload) -->
<div class="upload_zone" style="display: none;">
    <!-- Preview des fichiers en cours d'upload -->
    <div class="upload_preview">
        <div class="preview_header">
            <h4><?php esc_html_e( 'Upload en cours...', 'eventlist' ); ?></h4>
            <button type="button" class="btn_close_upload">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="preview_list">
            <!-- Sera rempli par JavaScript -->
        </div>
    </div>
</div>
