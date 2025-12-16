<?php
/**
 * Template: Mes Documents (Frontend Partenaire)
 * V1 Le Hiboo - Gestion des documents securises
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$vendor_id = get_current_user_id();

// Recuperer tous les types de documents actifs
$document_types = EL_Document_Types::get_all( true );

// Recuperer les documents du vendor
$vendor_documents = EL_Vendor_Documents::get_vendor_documents( $vendor_id );

// Indexer par type
$docs_by_type = array();
foreach ( $vendor_documents as $doc ) {
    $docs_by_type[ $doc->document_type_id ] = $doc;
}

// Verifier si tous les documents requis sont approuves
$all_approved = EL_Vendor_Documents::vendor_has_all_required_approved( $vendor_id );
$missing_required = EL_Vendor_Documents::get_missing_required_documents( $vendor_id );
?>

<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

<div class="vendor_wrap">
    <div class="contents">
        <?php echo el_get_template( '/vendor/heading.php' ); ?>

        <div class="el-vendor-documents-wrapper">

            <?php // Bandeau d'avertissement si documents manquants ?>
            <?php if ( ! $all_approved && ! empty( $missing_required ) ) : ?>
                <div class="el-documents-alert warning">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><?php esc_html_e( 'Documents requis en attente', 'eventlist' ); ?></strong>
                        <p><?php esc_html_e( 'Certains documents obligatoires sont manquants ou en attente de validation. Sans ces documents approuves, vous ne pourrez pas creer ou publier d\'activites.', 'eventlist' ); ?></p>
                    </div>
                </div>
            <?php elseif ( $all_approved ) : ?>
                <div class="el-documents-alert success">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><?php esc_html_e( 'Tous vos documents sont valides', 'eventlist' ); ?></strong>
                        <p><?php esc_html_e( 'Vous pouvez creer et publier vos activites.', 'eventlist' ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="el-documents-info">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <?php esc_html_e( 'Les documents marques d\'un asterisque (*) sont obligatoires. Ils seront examines par notre equipe avant validation.', 'eventlist' ); ?>
                </p>
            </div>

            <div class="el-documents-list">
                <?php foreach ( $document_types as $type ) :
                    $doc = isset( $docs_by_type[ $type->id ] ) ? $docs_by_type[ $type->id ] : null;
                    $status_class = '';
                    $status_label = '';
                    $can_upload = true;

                    if ( $doc ) {
                        switch ( $doc->status ) {
                            case 'pending':
                                $status_class = 'status-pending';
                                $status_label = __( 'En attente de validation', 'eventlist' );
                                $can_upload = false; // Ne peut pas re-uploader tant que pending
                                break;
                            case 'approved':
                                $status_class = 'status-approved';
                                $status_label = __( 'Approuve', 'eventlist' );
                                $can_upload = false; // Document approuve, pas besoin de re-uploader
                                break;
                            case 'rejected':
                                $status_class = 'status-rejected';
                                $status_label = __( 'A modifier', 'eventlist' );
                                $can_upload = true; // Peut re-uploader
                                break;
                        }
                    }

                    $extensions = EL_Document_Types::get_allowed_extensions( $type->id );
                    $max_size = EL_Document_Types::get_max_file_size( $type->id );
                ?>
                    <div class="el-document-item <?php echo esc_attr( $status_class ); ?>" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                        <div class="document-header">
                            <div class="document-title">
                                <h3>
                                    <?php echo esc_html( $type->name ); ?>
                                    <?php if ( $type->is_required ) : ?>
                                        <span class="required-badge">*</span>
                                    <?php endif; ?>
                                </h3>
                                <?php if ( $type->description ) : ?>
                                    <p class="document-description"><?php echo esc_html( $type->description ); ?></p>
                                <?php endif; ?>
                                <p class="document-specs">
                                    <small>
                                        <i class="fas fa-file"></i>
                                        <?php echo esc_html( strtoupper( implode( ', ', $extensions ) ) ); ?>
                                        &nbsp;|&nbsp;
                                        <i class="fas fa-weight"></i>
                                        <?php printf( __( 'Max %s', 'eventlist' ), size_format( $max_size ) ); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="document-status">
                                <?php if ( $doc ) : ?>
                                    <span class="status-badge <?php echo esc_attr( $status_class ); ?>">
                                        <?php if ( $doc->status === 'pending' ) : ?>
                                            <i class="fas fa-clock"></i>
                                        <?php elseif ( $doc->status === 'approved' ) : ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php else : ?>
                                            <i class="fas fa-times-circle"></i>
                                        <?php endif; ?>
                                        <?php echo esc_html( $status_label ); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="status-badge status-missing">
                                        <i class="fas fa-upload"></i>
                                        <?php esc_html_e( 'Non soumis', 'eventlist' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ( $doc ) : ?>
                            <div class="document-content">
                                <div class="document-file-info">
                                    <i class="fas fa-file-alt"></i>
                                    <span class="filename"><?php echo esc_html( $doc->original_filename ); ?></span>
                                    <span class="file-meta">
                                        (<?php echo esc_html( size_format( $doc->file_size ) ); ?>)
                                        - <?php printf( __( 'Soumis le %s', 'eventlist' ), date_i18n( 'j M Y a H:i', strtotime( $doc->uploaded_at ) ) ); ?>
                                    </span>
                                </div>

                                <?php if ( $doc->status === 'rejected' && $doc->rejection_reason ) : ?>
                                    <div class="rejection-reason">
                                        <strong><i class="fas fa-exclamation-circle"></i> <?php esc_html_e( 'Motif du rejet :', 'eventlist' ); ?></strong>
                                        <p><?php echo nl2br( esc_html( $doc->rejection_reason ) ); ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="document-actions">
                                    <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'el_download_document', 'document_id' => $doc->id, 'nonce' => wp_create_nonce( 'el_document_nonce' ) ), admin_url( 'admin-ajax.php' ) ) ); ?>" class="btn btn-outline btn-download" target="_blank">
                                        <i class="fas fa-download"></i>
                                        <?php esc_html_e( 'Telecharger', 'eventlist' ); ?>
                                    </a>

                                    <?php if ( $can_upload ) : ?>
                                        <button type="button" class="btn btn-primary btn-replace-document" data-type-id="<?php echo esc_attr( $type->id ); ?>" data-doc-id="<?php echo esc_attr( $doc->id ); ?>">
                                            <i class="fas fa-sync-alt"></i>
                                            <?php esc_html_e( 'Remplacer', 'eventlist' ); ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ( $doc->status === 'pending' ) : ?>
                                        <button type="button" class="btn btn-danger btn-delete-document" data-doc-id="<?php echo esc_attr( $doc->id ); ?>">
                                            <i class="fas fa-trash"></i>
                                            <?php esc_html_e( 'Supprimer', 'eventlist' ); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="document-content">
                                <div class="upload-zone" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p><?php esc_html_e( 'Glissez-deposez votre fichier ici ou', 'eventlist' ); ?></p>
                                    <button type="button" class="btn btn-primary btn-upload-document" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                                        <i class="fas fa-folder-open"></i>
                                        <?php esc_html_e( 'Parcourir', 'eventlist' ); ?>
                                    </button>
                                    <input type="file" class="document-file-input" data-type-id="<?php echo esc_attr( $type->id ); ?>" accept="<?php echo esc_attr( '.' . implode( ',.', $extensions ) ); ?>" style="display:none;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="upload-progress" style="display:none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <span class="progress-text"><?php esc_html_e( 'Upload en cours...', 'eventlist' ); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>

<style>
.el-vendor-documents-wrapper {
    max-width: 900px;
    margin: 0 auto;
}

.el-documents-alert {
    display: flex;
    align-items: flex-start;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.el-documents-alert.warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
}

.el-documents-alert.success {
    background: #d4edda;
    border: 1px solid #28a745;
}

.el-documents-alert .alert-icon {
    font-size: 24px;
    margin-right: 15px;
}

.el-documents-alert.warning .alert-icon {
    color: #856404;
}

.el-documents-alert.success .alert-icon {
    color: #155724;
}

.el-documents-alert .alert-content strong {
    display: block;
    margin-bottom: 5px;
}

.el-documents-alert.warning .alert-content {
    color: #856404;
}

.el-documents-alert.success .alert-content {
    color: #155724;
}

.el-documents-info {
    background: #e7f3fe;
    border-left: 4px solid #2196F3;
    padding: 12px 15px;
    margin-bottom: 25px;
    border-radius: 0 8px 8px 0;
}

.el-documents-info p {
    margin: 0;
    color: #0c5460;
}

.el-documents-info i {
    margin-right: 8px;
}

.el-documents-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.el-document-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.el-document-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.el-document-item.status-approved {
    border-color: #28a745;
}

.el-document-item.status-pending {
    border-color: #ffc107;
}

.el-document-item.status-rejected {
    border-color: #dc3545;
}

.document-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.document-title h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    color: #333;
}

.document-title .required-badge {
    color: #dc3545;
    margin-left: 5px;
}

.document-description {
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}

.document-specs {
    margin: 8px 0 0 0;
    color: #999;
}

.document-specs i {
    margin-right: 4px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-badge.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.status-missing {
    background: #e9ecef;
    color: #6c757d;
}

.document-content {
    padding: 20px;
}

.document-file-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.document-file-info i {
    font-size: 20px;
    color: #FF6B35;
}

.document-file-info .filename {
    font-weight: 500;
    color: #333;
}

.document-file-info .file-meta {
    color: #999;
    font-size: 13px;
}

.rejection-reason {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.rejection-reason strong {
    color: #721c24;
    display: block;
    margin-bottom: 8px;
}

.rejection-reason p {
    margin: 0;
    color: #721c24;
}

.document-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.document-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.document-actions .btn-outline {
    background: #fff;
    border: 1px solid #ddd;
    color: #333;
}

.document-actions .btn-outline:hover {
    background: #f8f9fa;
    border-color: #FF6B35;
    color: #FF6B35;
}

.document-actions .btn-primary {
    background: #FF6B35;
    color: #fff;
}

.document-actions .btn-primary:hover {
    background: #e55a25;
}

.document-actions .btn-danger {
    background: #dc3545;
    color: #fff;
}

.document-actions .btn-danger:hover {
    background: #c82333;
}

.upload-zone {
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover,
.upload-zone.drag-over {
    border-color: #FF6B35;
    background: #fff5f0;
}

.upload-zone .upload-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 15px;
}

.upload-zone:hover .upload-icon,
.upload-zone.drag-over .upload-icon {
    color: #FF6B35;
}

.upload-zone p {
    color: #666;
    margin: 0 0 15px 0;
}

.upload-zone .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    border: none;
    background: #FF6B35;
    color: #fff;
}

.upload-progress {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-fill {
    height: 100%;
    background: #FF6B35;
    width: 0;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 13px;
    color: #666;
}

@media (max-width: 768px) {
    .document-header {
        flex-direction: column;
        gap: 15px;
    }

    .document-file-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .document-actions {
        flex-direction: column;
    }

    .document-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    var documentNonce = '<?php echo wp_create_nonce( 'el_document_nonce' ); ?>';
    var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    // Click sur bouton upload
    $(document).on('click', '.btn-upload-document', function(e) {
        e.stopPropagation();
        var typeId = $(this).data('type-id');
        $('.document-file-input[data-type-id="' + typeId + '"]').trigger('click');
    });

    // Click sur zone upload
    $(document).on('click', '.upload-zone', function() {
        var typeId = $(this).data('type-id');
        $('.document-file-input[data-type-id="' + typeId + '"]').trigger('click');
    });

    // Drag & Drop
    $(document).on('dragover', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    $(document).on('dragleave', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });

    $(document).on('drop', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        var typeId = $(this).data('type-id');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadDocument(typeId, files[0], null);
        }
    });

    // Selection fichier
    $(document).on('change', '.document-file-input', function() {
        var typeId = $(this).data('type-id');
        var files = this.files;
        if (files.length > 0) {
            uploadDocument(typeId, files[0], null);
        }
    });

    // Remplacer document
    $(document).on('click', '.btn-replace-document', function() {
        var typeId = $(this).data('type-id');
        var docId = $(this).data('doc-id');

        // Creer un input file temporaire
        var $input = $('<input type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;">');
        $input.on('change', function() {
            if (this.files.length > 0) {
                uploadDocument(typeId, this.files[0], docId);
            }
            $input.remove();
        });
        $('body').append($input);
        $input.trigger('click');
    });

    // Supprimer document
    $(document).on('click', '.btn-delete-document', function() {
        var docId = $(this).data('doc-id');

        if (!confirm('<?php esc_html_e( 'Etes-vous sur de vouloir supprimer ce document ?', 'eventlist' ); ?>')) {
            return;
        }

        $.post(ajaxUrl, {
            action: 'el_delete_document',
            nonce: documentNonce,
            document_id: docId
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Erreur', 'eventlist' ); ?>');
            }
        });
    });

    // Upload document
    function uploadDocument(typeId, file, docId) {
        var $item = $('.el-document-item[data-type-id="' + typeId + '"]');
        var $progress = $item.find('.upload-progress');
        var $progressFill = $progress.find('.progress-fill');
        var $progressText = $progress.find('.progress-text');

        $progress.show();
        $progressFill.css('width', '0%');
        $progressText.text('<?php esc_html_e( 'Upload en cours...', 'eventlist' ); ?>');

        var formData = new FormData();
        formData.append('action', docId ? 'el_replace_document' : 'el_upload_document');
        formData.append('nonce', documentNonce);
        formData.append('document_type_id', typeId);
        formData.append('document', file);
        if (docId) {
            formData.append('document_id', docId);
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        $progressFill.css('width', percent + '%');
                        $progressText.text(percent + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $progressText.text('<?php esc_html_e( 'Upload reussi !', 'eventlist' ); ?>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $progressText.text(response.data.message || '<?php esc_html_e( 'Erreur', 'eventlist' ); ?>');
                    $progressFill.css('background', '#dc3545');
                    setTimeout(function() {
                        $progress.hide();
                    }, 3000);
                }
            },
            error: function() {
                $progressText.text('<?php esc_html_e( 'Erreur de connexion', 'eventlist' ); ?>');
                $progressFill.css('background', '#dc3545');
                setTimeout(function() {
                    $progress.hide();
                }, 3000);
            }
        });
    }
});
</script>
