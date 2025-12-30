<?php
/**
 * Admin View: Documents Partenaires
 * V1 Le Hiboo - Gestion des documents securises
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Parametres de filtres
$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$current_vendor = isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0;
$current_type = isset( $_GET['document_type_id'] ) ? absint( $_GET['document_type_id'] ) : 0;
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;

// Recuperer les documents
$args = array(
    'status' => $current_status,
    'vendor_id' => $current_vendor,
    'document_type_id' => $current_type,
    'search' => $search,
    'limit' => $per_page,
    'offset' => ( $paged - 1 ) * $per_page,
);

$documents = EL_Vendor_Documents::search( $args );
$total = EL_Vendor_Documents::search_count( $args );
$total_pages = ceil( $total / $per_page );

// Stats
$stats = array(
    'pending' => EL_Vendor_Documents::count_by_status( 'pending' ),
    'approved' => EL_Vendor_Documents::count_by_status( 'approved' ),
    'rejected' => EL_Vendor_Documents::count_by_status( 'rejected' ),
);

// Tous les types pour le filtre
$document_types = EL_Document_Types::get_all( false );

// URL de base
$base_url = admin_url( 'admin.php?page=el_vendor_documents' );
?>

<div class="wrap el-vendor-documents-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-media-document" style="font-size:30px;margin-right:10px;"></span>
        <?php esc_html_e( 'Documents Partenaires', 'eventlist' ); ?>
    </h1>
    <hr class="wp-header-end">

    <!-- Stats Cards -->
    <div class="el-stats-cards" style="display:flex;gap:15px;margin:20px 0;">
        <a href="<?php echo esc_url( add_query_arg( 'status', 'pending', $base_url ) ); ?>" class="el-stat-card <?php echo $current_status === 'pending' ? 'active' : ''; ?>" style="flex:1;background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;text-decoration:none;<?php echo $current_status === 'pending' ? 'border-color:#ffc107;' : ''; ?>">
            <div style="font-size:36px;font-weight:bold;color:#ffc107;"><?php echo esc_html( $stats['pending'] ); ?></div>
            <div style="color:#666;"><?php esc_html_e( 'En attente', 'eventlist' ); ?></div>
        </a>
        <a href="<?php echo esc_url( add_query_arg( 'status', 'approved', $base_url ) ); ?>" class="el-stat-card <?php echo $current_status === 'approved' ? 'active' : ''; ?>" style="flex:1;background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;text-decoration:none;<?php echo $current_status === 'approved' ? 'border-color:#28a745;' : ''; ?>">
            <div style="font-size:36px;font-weight:bold;color:#28a745;"><?php echo esc_html( $stats['approved'] ); ?></div>
            <div style="color:#666;"><?php esc_html_e( 'Approuves', 'eventlist' ); ?></div>
        </a>
        <a href="<?php echo esc_url( add_query_arg( 'status', 'rejected', $base_url ) ); ?>" class="el-stat-card <?php echo $current_status === 'rejected' ? 'active' : ''; ?>" style="flex:1;background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;text-decoration:none;<?php echo $current_status === 'rejected' ? 'border-color:#dc3545;' : ''; ?>">
            <div style="font-size:36px;font-weight:bold;color:#dc3545;"><?php echo esc_html( $stats['rejected'] ); ?></div>
            <div style="color:#666;"><?php esc_html_e( 'Rejetes', 'eventlist' ); ?></div>
        </a>
        <a href="<?php echo esc_url( $base_url ); ?>" class="el-stat-card <?php echo empty( $current_status ) ? 'active' : ''; ?>" style="flex:1;background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;text-decoration:none;<?php echo empty( $current_status ) ? 'border-color:#2196F3;' : ''; ?>">
            <div style="font-size:36px;font-weight:bold;color:#2196F3;"><?php echo esc_html( $stats['pending'] + $stats['approved'] + $stats['rejected'] ); ?></div>
            <div style="color:#666;"><?php esc_html_e( 'Total', 'eventlist' ); ?></div>
        </a>
    </div>

    <!-- Filtres -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="el_vendor_documents">

            <select name="status">
                <option value=""><?php esc_html_e( 'Tous les statuts', 'eventlist' ); ?></option>
                <option value="pending" <?php selected( $current_status, 'pending' ); ?>><?php esc_html_e( 'En attente', 'eventlist' ); ?></option>
                <option value="approved" <?php selected( $current_status, 'approved' ); ?>><?php esc_html_e( 'Approuves', 'eventlist' ); ?></option>
                <option value="rejected" <?php selected( $current_status, 'rejected' ); ?>><?php esc_html_e( 'Rejetes', 'eventlist' ); ?></option>
            </select>

            <select name="document_type_id">
                <option value=""><?php esc_html_e( 'Tous les types', 'eventlist' ); ?></option>
                <?php foreach ( $document_types as $type ) : ?>
                    <option value="<?php echo esc_attr( $type->id ); ?>" <?php selected( $current_type, $type->id ); ?>>
                        <?php echo esc_html( $type->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher...', 'eventlist' ); ?>">

            <button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'eventlist' ); ?></button>

            <?php if ( $current_status || $current_type || $search ) : ?>
                <a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php esc_html_e( 'Reinitialiser', 'eventlist' ); ?></a>
            <?php endif; ?>
        </form>

        <div class="tablenav-pages">
            <span class="displaying-num"><?php printf( esc_html__( '%d elements', 'eventlist' ), $total ); ?></span>
        </div>
    </div>

    <!-- Tableau des documents -->
    <table class="wp-list-table widefat fixed striped" id="vendor_documents_table">
        <thead>
            <tr>
                <th style="width:200px;"><?php esc_html_e( 'Partenaire', 'eventlist' ); ?></th>
                <th><?php esc_html_e( 'Type', 'eventlist' ); ?></th>
                <th><?php esc_html_e( 'Fichier', 'eventlist' ); ?></th>
                <th style="width:120px;"><?php esc_html_e( 'Taille', 'eventlist' ); ?></th>
                <th style="width:150px;"><?php esc_html_e( 'Date', 'eventlist' ); ?></th>
                <th style="width:120px;"><?php esc_html_e( 'Statut', 'eventlist' ); ?></th>
                <th style="width:180px;"><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $documents ) ) : ?>
                <tr class="no-items">
                    <td colspan="7" style="text-align:center;padding:20px;">
                        <?php esc_html_e( 'Aucun document trouve.', 'eventlist' ); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $documents as $doc ) : ?>
                    <tr data-doc-id="<?php echo esc_attr( $doc->id ); ?>">
                        <td>
                            <a href="#" class="btn_view_vendor_profile" data-vendor-id="<?php echo esc_attr( $doc->vendor_id ); ?>" style="text-decoration:none;color:inherit;">
                                <strong style="color:#2196F3;cursor:pointer;"><?php echo esc_html( $doc->vendor_name ); ?></strong>
                                <span class="dashicons dashicons-external" style="font-size:14px;vertical-align:middle;color:#2196F3;margin-left:3px;"></span>
                            </a>
                            <br><small style="color:#666;"><?php echo esc_html( $doc->vendor_email ); ?></small>
                        </td>
                        <td><?php echo esc_html( $doc->type_name ); ?></td>
                        <td>
                            <span class="dashicons dashicons-media-document" style="color:#666;vertical-align:middle;"></span>
                            <?php echo esc_html( $doc->original_filename ); ?>
                        </td>
                        <td><?php echo esc_html( size_format( $doc->file_size ) ); ?></td>
                        <td>
                            <?php echo esc_html( date_i18n( 'j M Y H:i', strtotime( $doc->uploaded_at ) ) ); ?>
                        </td>
                        <td>
                            <?php
                            $status_class = '';
                            $status_label = '';
                            switch ( $doc->status ) {
                                case 'pending':
                                    $status_class = 'background:#ffc107;color:#000;';
                                    $status_label = __( 'En attente', 'eventlist' );
                                    break;
                                case 'approved':
                                    $status_class = 'background:#28a745;color:#fff;';
                                    $status_label = __( 'Approuve', 'eventlist' );
                                    break;
                                case 'rejected':
                                    $status_class = 'background:#dc3545;color:#fff;';
                                    $status_label = __( 'Rejete', 'eventlist' );
                                    break;
                            }
                            ?>
                            <span class="el-status-badge" style="display:inline-block;padding:3px 10px;border-radius:3px;font-size:12px;<?php echo $status_class; ?>">
                                <?php echo esc_html( $status_label ); ?>
                            </span>
                            <?php if ( $doc->status === 'rejected' && $doc->rejection_reason ) : ?>
                                <br><small style="color:#dc3545;" title="<?php echo esc_attr( $doc->rejection_reason ); ?>">
                                    <span class="dashicons dashicons-info-outline" style="font-size:14px;vertical-align:middle;"></span>
                                    <?php echo esc_html( substr( $doc->rejection_reason, 0, 30 ) ); ?>...
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // Determiner si le fichier peut etre previsualise
                            $previewable_mimes = array( 'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
                            $can_preview = in_array( $doc->mime_type, $previewable_mimes );
                            ?>
                            <?php if ( $can_preview ) : ?>
                                <button type="button" class="button button-small btn_preview_doc" data-doc-id="<?php echo esc_attr( $doc->id ); ?>" data-filename="<?php echo esc_attr( $doc->original_filename ); ?>" data-mime="<?php echo esc_attr( $doc->mime_type ); ?>" title="<?php esc_attr_e( 'Previsualiser', 'eventlist' ); ?>" style="color:#2196F3;">
                                    <span class="dashicons dashicons-visibility" style="vertical-align:middle;"></span>
                                </button>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'el_admin_download_document', 'document_id' => $doc->id, 'nonce' => wp_create_nonce( 'el_admin_document_nonce' ) ), admin_url( 'admin-ajax.php' ) ) ); ?>" class="button button-small" title="<?php esc_attr_e( 'Telecharger', 'eventlist' ); ?>" target="_blank">
                                <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
                            </a>

                            <?php if ( $doc->status === 'pending' ) : ?>
                                <button type="button" class="button button-small button-primary btn_approve_doc" data-doc-id="<?php echo esc_attr( $doc->id ); ?>" title="<?php esc_attr_e( 'Approuver', 'eventlist' ); ?>">
                                    <span class="dashicons dashicons-yes" style="vertical-align:middle;"></span>
                                </button>
                                <button type="button" class="button button-small btn_reject_doc" data-doc-id="<?php echo esc_attr( $doc->id ); ?>" title="<?php esc_attr_e( 'Rejeter', 'eventlist' ); ?>" style="color:#dc3545;">
                                    <span class="dashicons dashicons-no" style="vertical-align:middle;"></span>
                                </button>
                            <?php elseif ( $doc->status === 'approved' ) : ?>
                                <button type="button" class="button button-small btn_reject_doc" data-doc-id="<?php echo esc_attr( $doc->id ); ?>" title="<?php esc_attr_e( 'Rejeter', 'eventlist' ); ?>" style="color:#dc3545;">
                                    <span class="dashicons dashicons-no" style="vertical-align:middle;"></span>
                                </button>
                            <?php elseif ( $doc->status === 'rejected' ) : ?>
                                <button type="button" class="button button-small button-primary btn_approve_doc" data-doc-id="<?php echo esc_attr( $doc->id ); ?>" title="<?php esc_attr_e( 'Approuver', 'eventlist' ); ?>">
                                    <span class="dashicons dashicons-yes" style="vertical-align:middle;"></span>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $paged,
                ) );
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Rejet -->
<div id="reject_modal" class="el-modal" style="display:none;">
    <div class="el-modal-overlay"></div>
    <div class="el-modal-content" style="width:500px;">
        <div class="el-modal-header">
            <h2><?php esc_html_e( 'Rejeter le document', 'eventlist' ); ?></h2>
            <button type="button" class="el-modal-close">&times;</button>
        </div>
        <form id="reject_form">
            <input type="hidden" name="document_id" id="reject_doc_id" value="">
            <div class="el-modal-body">
                <p><?php esc_html_e( 'Veuillez indiquer le motif du rejet. Ce message sera envoye au partenaire.', 'eventlist' ); ?></p>
                <textarea name="reason" id="reject_reason" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Motif du rejet (optionnel mais recommande)...', 'eventlist' ); ?>"></textarea>
            </div>
            <div class="el-modal-footer">
                <button type="button" class="button el-modal-cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                <button type="submit" class="button" style="background:#dc3545;color:#fff;border-color:#dc3545;">
                    <span class="dashicons dashicons-no" style="vertical-align:middle;"></span>
                    <?php esc_html_e( 'Confirmer le rejet', 'eventlist' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Profil Partenaire -->
<div id="vendor_profile_modal" class="el-modal" style="display:none;">
    <div class="el-modal-overlay"></div>
    <div class="el-modal-content" style="width:650px;">
        <div class="el-modal-header" style="background:#FF6600;">
            <h2 style="color:#fff;">
                <span class="dashicons dashicons-businessperson" style="vertical-align:middle;margin-right:8px;"></span>
                <?php esc_html_e( 'Fiche Partenaire', 'eventlist' ); ?>
            </h2>
            <button type="button" class="el-modal-close" style="color:#fff;">&times;</button>
        </div>
        <div class="el-modal-body" id="vendor_profile_content">
            <!-- Loader -->
            <div id="vendor_profile_loader" style="text-align:center;padding:40px;">
                <span class="spinner is-active" style="float:none;margin:0 auto;"></span>
                <p style="margin-top:10px;color:#666;"><?php esc_html_e( 'Chargement...', 'eventlist' ); ?></p>
            </div>
            <!-- Content will be loaded here -->
            <div id="vendor_profile_data" style="display:none;"></div>
        </div>
        <div class="el-modal-footer">
            <a href="#" id="vendor_edit_link" class="button button-primary" target="_blank" style="float:left;">
                <span class="dashicons dashicons-edit" style="vertical-align:middle;"></span>
                <?php esc_html_e( 'Modifier dans WordPress', 'eventlist' ); ?>
            </a>
            <button type="button" class="button el-modal-cancel"><?php esc_html_e( 'Fermer', 'eventlist' ); ?></button>
        </div>
    </div>
</div>

<!-- Modal Prévisualisation Document -->
<div id="preview_modal" class="el-modal" style="display:none;">
    <div class="el-modal-overlay"></div>
    <div class="el-modal-content el-preview-modal-content">
        <div class="el-modal-header" style="background:#2196F3;">
            <h2 style="color:#fff;">
                <span class="dashicons dashicons-visibility" style="vertical-align:middle;margin-right:8px;"></span>
                <span id="preview_modal_title"><?php esc_html_e( 'Prévisualisation', 'eventlist' ); ?></span>
            </h2>
            <button type="button" class="el-modal-close" style="color:#fff;">&times;</button>
        </div>
        <div class="el-modal-body el-preview-body">
            <!-- Loader -->
            <div id="preview_loader" style="text-align:center;padding:40px;">
                <span class="spinner is-active" style="float:none;margin:0 auto;"></span>
                <p style="margin-top:10px;color:#666;"><?php esc_html_e( 'Chargement...', 'eventlist' ); ?></p>
            </div>
            <!-- Preview content -->
            <div id="preview_content" style="display:none;">
                <!-- PDF iframe or image will be inserted here -->
            </div>
        </div>
        <div class="el-modal-footer">
            <a href="#" id="preview_download_link" class="button button-primary" target="_blank" style="float:left;">
                <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
                <?php esc_html_e( 'Télécharger', 'eventlist' ); ?>
            </a>
            <button type="button" class="button el-modal-cancel"><?php esc_html_e( 'Fermer', 'eventlist' ); ?></button>
        </div>
    </div>
</div>

<style>
.el-vendor-documents-admin .wp-heading-inline {
    display: flex;
    align-items: center;
}
.el-stat-card:hover {
    border-color: #2196F3 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.el-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
}
.el-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
}
.el-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 8px;
    max-width: 90%;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
}
.el-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: #f8f9fa;
}
.el-modal-header h2 {
    margin: 0;
    font-size: 18px;
}
.el-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}
.el-modal-close:hover {
    color: #dc3545;
}
.el-modal-body {
    padding: 20px;
}
.el-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    text-align: right;
    background: #f8f9fa;
}
.el-modal-footer .button {
    margin-left: 10px;
}
/* Styles pour le modal profil partenaire */
.vendor-profile-section {
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}
.vendor-profile-section:last-child {
    margin-bottom: 0;
}
.vendor-profile-section-header {
    background: #f8f9fa;
    padding: 12px 15px;
    border-bottom: 1px solid #e0e0e0;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}
.vendor-profile-section-header .dashicons {
    color: #FF6600;
}
.vendor-profile-section-content {
    padding: 15px;
}
.vendor-profile-row {
    display: flex;
    margin-bottom: 10px;
}
.vendor-profile-row:last-child {
    margin-bottom: 0;
}
.vendor-profile-label {
    width: 140px;
    font-weight: 500;
    color: #666;
    flex-shrink: 0;
}
.vendor-profile-value {
    flex: 1;
    color: #333;
    word-break: break-word;
}
.vendor-profile-value a {
    color: #2196F3;
    text-decoration: none;
}
.vendor-profile-value a:hover {
    text-decoration: underline;
}
.vendor-profile-value.empty {
    color: #999;
    font-style: italic;
}
.vendor-profile-logo {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #ddd;
}
.vendor-profile-header-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}
.vendor-profile-header-text h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    color: #333;
}
.vendor-profile-header-text .vendor-id {
    color: #999;
    font-size: 12px;
}
.btn_view_vendor_profile:hover strong {
    text-decoration: underline;
}
/* Styles pour le modal de previsualisation */
.el-preview-modal-content {
    width: 90%;
    max-width: 1000px;
    height: 85vh;
    display: flex;
    flex-direction: column;
}
.el-preview-body {
    flex: 1;
    overflow: hidden;
    padding: 0 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
}
#preview_content {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
#preview_content iframe {
    width: 100%;
    height: 100%;
    border: none;
}
#preview_content img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>

<script>
jQuery(document).ready(function($) {
    var adminNonce = '<?php echo wp_create_nonce( 'el_admin_document_nonce' ); ?>';
    var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    // Approuver un document
    $(document).on('click', '.btn_approve_doc', function() {
        var docId = $(this).data('doc-id');
        var $btn = $(this);

        if (!confirm('<?php esc_html_e( 'Etes-vous sur de vouloir approuver ce document ?', 'eventlist' ); ?>')) {
            return;
        }

        $btn.prop('disabled', true);

        $.post(ajaxUrl, {
            action: 'el_admin_approve_document',
            nonce: adminNonce,
            document_id: docId
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Erreur', 'eventlist' ); ?>');
                $btn.prop('disabled', false);
            }
        });
    });

    // Ouvrir modal rejet
    $(document).on('click', '.btn_reject_doc', function() {
        var docId = $(this).data('doc-id');
        $('#reject_doc_id').val(docId);
        $('#reject_reason').val('');
        $('#reject_modal').fadeIn(200);
    });

    // Fermer modal
    $('.el-modal-close, .el-modal-cancel, .el-modal-overlay').on('click', function() {
        $('.el-modal').fadeOut(200);
    });

    // ESC pour fermer
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.el-modal').fadeOut(200);
        }
    });

    // ========================================
    // Modal Previsualisation Document
    // ========================================

    // Ouvrir modal previsualisation
    $(document).on('click', '.btn_preview_doc', function() {
        var docId = $(this).data('doc-id');
        var filename = $(this).data('filename');
        var mimeType = $(this).data('mime');

        // Construire les URLs
        var previewUrl = ajaxUrl + '?action=el_admin_preview_document&document_id=' + docId + '&nonce=' + adminNonce;
        var downloadUrl = ajaxUrl + '?action=el_admin_download_document&document_id=' + docId + '&nonce=' + adminNonce;

        // Mise a jour du titre et du lien de telechargement
        $('#preview_modal_title').text(filename);
        $('#preview_download_link').attr('href', downloadUrl);

        // Afficher le modal avec le loader
        $('#preview_loader').show();
        $('#preview_content').hide().empty();
        $('#preview_modal').fadeIn(200);

        // Charger le contenu selon le type
        if (mimeType === 'application/pdf') {
            // PDF : utiliser un iframe
            var iframe = $('<iframe>').attr('src', previewUrl);
            iframe.on('load', function() {
                $('#preview_loader').hide();
                $('#preview_content').show();
            });
            $('#preview_content').html(iframe);
        } else if (mimeType.indexOf('image/') === 0) {
            // Image : utiliser une balise img
            var img = $('<img>').attr('src', previewUrl).attr('alt', filename);
            img.on('load', function() {
                $('#preview_loader').hide();
                $('#preview_content').show();
            });
            img.on('error', function() {
                $('#preview_loader').hide();
                $('#preview_content').html('<p style="color:#dc3545;padding:20px;">Erreur lors du chargement de l\'image</p>').show();
            });
            $('#preview_content').html(img);
        } else {
            // Type non supporte
            $('#preview_loader').hide();
            $('#preview_content').html('<p style="color:#666;padding:20px;">Ce type de fichier ne peut pas etre previsualise</p>').show();
        }
    });

    // ========================================
    // Modal Profil Partenaire
    // ========================================

    // Ouvrir modal profil
    $(document).on('click', '.btn_view_vendor_profile', function(e) {
        e.preventDefault();
        var vendorId = $(this).data('vendor-id');

        // Afficher le modal avec le loader
        $('#vendor_profile_loader').show();
        $('#vendor_profile_data').hide().empty();
        $('#vendor_profile_modal').fadeIn(200);

        // Charger les donnees
        $.post(ajaxUrl, {
            action: 'el_admin_get_vendor_profile',
            nonce: adminNonce,
            vendor_id: vendorId
        }, function(response) {
            $('#vendor_profile_loader').hide();

            if (response.success && response.data.profile) {
                var profile = response.data.profile;
                var html = buildProfileHtml(profile);
                $('#vendor_profile_data').html(html).show();
                $('#vendor_edit_link').attr('href', profile.meta.edit_url);
            } else {
                $('#vendor_profile_data').html('<p style="color:#dc3545;text-align:center;">' + (response.data.message || 'Erreur lors du chargement') + '</p>').show();
            }
        }).fail(function() {
            $('#vendor_profile_loader').hide();
            $('#vendor_profile_data').html('<p style="color:#dc3545;text-align:center;">Erreur de connexion</p>').show();
        });
    });

    // Construire le HTML du profil
    function buildProfileHtml(profile) {
        var html = '';

        // Header avec logo
        html += '<div class="vendor-profile-header-info">';
        if (profile.organisation.logo_url) {
            html += '<img src="' + escapeHtml(profile.organisation.logo_url) + '" alt="" class="vendor-profile-logo">';
        } else {
            html += '<div class="vendor-profile-logo" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><span class="dashicons dashicons-building" style="font-size:30px;color:#999;"></span></div>';
        }
        html += '<div class="vendor-profile-header-text">';
        html += '<h3>' + escapeHtml(profile.organisation.display_name || profile.contact.full_name || 'Sans nom') + '</h3>';
        html += '<div class="vendor-id">ID: ' + profile.id + ' | Inscrit le ' + escapeHtml(profile.meta.registered) + '</div>';
        html += '</div>';
        html += '</div>';

        // Section Contact
        html += '<div class="vendor-profile-section">';
        html += '<div class="vendor-profile-section-header"><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Contact', 'eventlist' ); ?></div>';
        html += '<div class="vendor-profile-section-content">';
        html += buildRow('<?php esc_html_e( 'Nom complet', 'eventlist' ); ?>', profile.contact.full_name);
        html += buildRow('<?php esc_html_e( 'Email', 'eventlist' ); ?>', profile.contact.email, 'email');
        html += buildRow('<?php esc_html_e( 'Telephone', 'eventlist' ); ?>', profile.contact.phone, 'phone');
        html += '</div></div>';

        // Section Organisation
        html += '<div class="vendor-profile-section">';
        html += '<div class="vendor-profile-section-header"><span class="dashicons dashicons-building"></span> <?php esc_html_e( 'Organisation', 'eventlist' ); ?></div>';
        html += '<div class="vendor-profile-section-content">';
        html += buildRow('<?php esc_html_e( 'Nom', 'eventlist' ); ?>', profile.organisation.display_name || profile.organisation.name);
        html += buildRow('<?php esc_html_e( 'Type', 'eventlist' ); ?>', profile.organisation.type_label);
        html += buildRow('<?php esc_html_e( 'SIREN', 'eventlist' ); ?>', profile.organisation.siren);
        html += buildRow('<?php esc_html_e( 'Statut juridique', 'eventlist' ); ?>', profile.organisation.legal_status);
        html += buildRow('<?php esc_html_e( 'Telephone org.', 'eventlist' ); ?>', profile.organisation.phone, 'phone');
        if (profile.organisation.website) {
            html += buildRow('<?php esc_html_e( 'Site web', 'eventlist' ); ?>', profile.organisation.website, 'url');
        }
        html += '</div></div>';

        // Section Adresse
        html += '<div class="vendor-profile-section">';
        html += '<div class="vendor-profile-section-header"><span class="dashicons dashicons-location"></span> <?php esc_html_e( 'Adresse', 'eventlist' ); ?></div>';
        html += '<div class="vendor-profile-section-content">';
        html += buildRow('<?php esc_html_e( 'Adresse', 'eventlist' ); ?>', profile.address.line1);
        html += buildRow('<?php esc_html_e( 'Code postal', 'eventlist' ); ?>', profile.address.postcode);
        html += buildRow('<?php esc_html_e( 'Ville', 'eventlist' ); ?>', profile.address.city);
        html += buildRow('<?php esc_html_e( 'Pays', 'eventlist' ); ?>', profile.address.country);
        html += '</div></div>';

        return html;
    }

    // Construire une ligne de donnee
    function buildRow(label, value, type) {
        var displayValue = '';

        if (!value || value === '') {
            displayValue = '<span class="empty">Non renseigne</span>';
        } else if (type === 'email') {
            displayValue = '<a href="mailto:' + escapeHtml(value) + '">' + escapeHtml(value) + '</a>';
        } else if (type === 'phone') {
            displayValue = '<a href="tel:' + escapeHtml(value) + '">' + escapeHtml(value) + '</a>';
        } else if (type === 'url') {
            var url = value.indexOf('http') === 0 ? value : 'https://' + value;
            displayValue = '<a href="' + escapeHtml(url) + '" target="_blank">' + escapeHtml(value) + '</a>';
        } else {
            displayValue = escapeHtml(value);
        }

        return '<div class="vendor-profile-row"><div class="vendor-profile-label">' + label + '</div><div class="vendor-profile-value">' + displayValue + '</div></div>';
    }

    // Echapper le HTML
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Confirmer rejet
    $('#reject_form').on('submit', function(e) {
        e.preventDefault();

        var docId = $('#reject_doc_id').val();
        var reason = $('#reject_reason').val();

        $.post(ajaxUrl, {
            action: 'el_admin_reject_document',
            nonce: adminNonce,
            document_id: docId,
            reason: reason
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Erreur', 'eventlist' ); ?>');
            }
        });
    });
});
</script>
