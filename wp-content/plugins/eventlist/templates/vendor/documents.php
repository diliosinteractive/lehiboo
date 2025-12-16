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

// Compter les statuts
$count_approved = 0;
$count_pending = 0;
$count_rejected = 0;
$count_missing = 0;

foreach ( $document_types as $type ) {
    $doc = isset( $docs_by_type[ $type->id ] ) ? $docs_by_type[ $type->id ] : null;
    if ( $doc ) {
        if ( $doc->status === 'approved' ) $count_approved++;
        elseif ( $doc->status === 'pending' ) $count_pending++;
        elseif ( $doc->status === 'rejected' ) $count_rejected++;
    } else {
        $count_missing++;
    }
}
?>

<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-documents.css?v=' . time(); ?>">

<div class="vendor_wrap el-vendor-documents-page">

    <?php echo el_get_template( 'vendor/sidebar.php' ); ?>

    <div class="contents">

        <!-- Heading cache -->
        <div style="display: none;"><?php echo el_get_template( '/vendor/heading.php' ); ?></div>

        <!-- Sticky Header -->
        <div class="documents_sticky_bar">
            <div class="sticky_bar_inner">
                <div class="sticky_bar_left">
                    <h3><?php esc_html_e( 'Mes Documents', 'eventlist' ); ?></h3>
                    <span class="documents_subtitle">
                        <?php printf(
                            __( '%d document(s) requis', 'eventlist' ),
                            count( array_filter( $document_types, function($t) { return $t->is_required; } ) )
                        ); ?>
                    </span>
                </div>
                <div class="sticky_bar_right">
                    <div class="documents_stats">
                        <?php if ( $count_approved > 0 ) : ?>
                            <span class="stat stat-approved" title="<?php esc_attr_e( 'Approuves', 'eventlist' ); ?>">
                                <i class="fas fa-check-circle"></i> <?php echo $count_approved; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $count_pending > 0 ) : ?>
                            <span class="stat stat-pending" title="<?php esc_attr_e( 'En attente', 'eventlist' ); ?>">
                                <i class="fas fa-clock"></i> <?php echo $count_pending; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $count_rejected > 0 ) : ?>
                            <span class="stat stat-rejected" title="<?php esc_attr_e( 'A modifier', 'eventlist' ); ?>">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $count_rejected; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $count_missing > 0 ) : ?>
                            <span class="stat stat-missing" title="<?php esc_attr_e( 'Non soumis', 'eventlist' ); ?>">
                                <i class="fas fa-upload"></i> <?php echo $count_missing; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="documents_content">

            <?php // Bandeau d'avertissement si documents manquants ?>
            <?php if ( ! $all_approved && ! empty( $missing_required ) ) : ?>
                <div class="el-alert el-alert-warning">
                    <div class="el-alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="el-alert-content">
                        <strong><?php esc_html_e( 'Documents requis en attente', 'eventlist' ); ?></strong>
                        <p><?php esc_html_e( 'Certains documents obligatoires sont manquants ou en attente de validation. Sans ces documents approuves, vous ne pourrez pas creer ou publier d\'activites.', 'eventlist' ); ?></p>
                    </div>
                </div>
            <?php elseif ( $all_approved ) : ?>
                <div class="el-alert el-alert-success">
                    <div class="el-alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="el-alert-content">
                        <strong><?php esc_html_e( 'Tous vos documents sont valides !', 'eventlist' ); ?></strong>
                        <p><?php esc_html_e( 'Vous pouvez creer et publier vos activites.', 'eventlist' ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Liste des documents -->
            <div class="documents_grid">
                <?php foreach ( $document_types as $type ) :
                    $doc = isset( $docs_by_type[ $type->id ] ) ? $docs_by_type[ $type->id ] : null;
                    $status = $doc ? $doc->status : 'missing';
                    $extensions = EL_Document_Types::get_allowed_extensions( $type->id );
                    $max_size = EL_Document_Types::get_max_file_size( $type->id );
                ?>
                    <div class="document_card document_card--<?php echo esc_attr( $status ); ?>" data-type-id="<?php echo esc_attr( $type->id ); ?>">

                        <!-- Card Header -->
                        <div class="document_card_header">
                            <div class="document_card_icon">
                                <?php if ( $status === 'approved' ) : ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php elseif ( $status === 'pending' ) : ?>
                                    <i class="fas fa-clock"></i>
                                <?php elseif ( $status === 'rejected' ) : ?>
                                    <i class="fas fa-exclamation-circle"></i>
                                <?php else : ?>
                                    <i class="fas fa-file-upload"></i>
                                <?php endif; ?>
                            </div>
                            <div class="document_card_title">
                                <h4>
                                    <?php echo esc_html( $type->name ); ?>
                                    <?php if ( $type->is_required ) : ?>
                                        <span class="required_star">*</span>
                                    <?php endif; ?>
                                </h4>
                                <span class="document_card_status">
                                    <?php
                                    switch ( $status ) {
                                        case 'approved':
                                            esc_html_e( 'Approuve', 'eventlist' );
                                            break;
                                        case 'pending':
                                            esc_html_e( 'En attente de validation', 'eventlist' );
                                            break;
                                        case 'rejected':
                                            esc_html_e( 'A modifier', 'eventlist' );
                                            break;
                                        default:
                                            esc_html_e( 'Non soumis', 'eventlist' );
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="document_card_body">
                            <?php if ( $type->description ) : ?>
                                <p class="document_card_description"><?php echo esc_html( $type->description ); ?></p>
                            <?php endif; ?>

                            <div class="document_card_specs">
                                <span><i class="fas fa-file-alt"></i> <?php echo esc_html( strtoupper( implode( ', ', $extensions ) ) ); ?></span>
                                <span><i class="fas fa-database"></i> <?php printf( __( 'Max %s', 'eventlist' ), size_format( $max_size ) ); ?></span>
                            </div>

                            <?php if ( $doc ) : ?>
                                <!-- Document existant -->
                                <div class="document_file_preview">
                                    <div class="file_icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="file_info">
                                        <span class="file_name"><?php echo esc_html( $doc->original_filename ); ?></span>
                                        <span class="file_meta">
                                            <?php echo esc_html( size_format( $doc->file_size ) ); ?>
                                            &bull;
                                            <?php echo esc_html( date_i18n( 'j M Y', strtotime( $doc->uploaded_at ) ) ); ?>
                                        </span>
                                    </div>
                                    <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'el_download_document', 'document_id' => $doc->id, 'nonce' => wp_create_nonce( 'el_document_nonce' ) ), admin_url( 'admin-ajax.php' ) ) ); ?>" class="file_download" title="<?php esc_attr_e( 'Telecharger', 'eventlist' ); ?>">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>

                                <?php if ( $doc->status === 'rejected' && $doc->rejection_reason ) : ?>
                                    <div class="document_rejection_reason">
                                        <i class="fas fa-info-circle"></i>
                                        <div>
                                            <strong><?php esc_html_e( 'Motif du rejet', 'eventlist' ); ?></strong>
                                            <p><?php echo nl2br( esc_html( $doc->rejection_reason ) ); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php else : ?>
                                <!-- Zone d'upload -->
                                <div class="document_upload_zone" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p><?php esc_html_e( 'Glissez votre fichier ici', 'eventlist' ); ?></p>
                                    <span><?php esc_html_e( 'ou', 'eventlist' ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer -->
                        <div class="document_card_footer">
                            <?php if ( $doc ) : ?>
                                <?php if ( $doc->status === 'rejected' || $doc->status === 'pending' ) : ?>
                                    <?php if ( $doc->status === 'rejected' ) : ?>
                                        <button type="button" class="btn_document btn_document--primary btn_replace_document" data-type-id="<?php echo esc_attr( $type->id ); ?>" data-doc-id="<?php echo esc_attr( $doc->id ); ?>">
                                            <i class="fas fa-sync-alt"></i>
                                            <?php esc_html_e( 'Remplacer le document', 'eventlist' ); ?>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ( $doc->status === 'pending' ) : ?>
                                        <button type="button" class="btn_document btn_document--outline btn_delete_document" data-doc-id="<?php echo esc_attr( $doc->id ); ?>">
                                            <i class="fas fa-trash-alt"></i>
                                            <?php esc_html_e( 'Annuler', 'eventlist' ); ?>
                                        </button>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="document_validated">
                                        <i class="fas fa-shield-alt"></i>
                                        <?php esc_html_e( 'Document valide', 'eventlist' ); ?>
                                    </span>
                                <?php endif; ?>
                            <?php else : ?>
                                <button type="button" class="btn_document btn_document--primary btn_upload_document" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                                    <i class="fas fa-upload"></i>
                                    <?php esc_html_e( 'Choisir un fichier', 'eventlist' ); ?>
                                </button>
                                <input type="file" class="document_file_input" data-type-id="<?php echo esc_attr( $type->id ); ?>" accept="<?php echo esc_attr( '.' . implode( ',.', $extensions ) ); ?>" style="display:none;">
                            <?php endif; ?>
                        </div>

                        <!-- Progress Bar -->
                        <div class="document_upload_progress" style="display:none;">
                            <div class="progress_bar_container">
                                <div class="progress_bar_fill"></div>
                            </div>
                            <span class="progress_bar_text"><?php esc_html_e( 'Upload en cours...', 'eventlist' ); ?></span>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Info box -->
            <div class="documents_info_box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong><?php esc_html_e( 'Informations importantes', 'eventlist' ); ?></strong>
                    <ul>
                        <li><?php esc_html_e( 'Les documents marques d\'un asterisque (*) sont obligatoires.', 'eventlist' ); ?></li>
                        <li><?php esc_html_e( 'Chaque document sera examine par notre equipe sous 24-48h.', 'eventlist' ); ?></li>
                        <li><?php esc_html_e( 'Vous serez notifie par email du statut de validation.', 'eventlist' ); ?></li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var documentNonce = '<?php echo wp_create_nonce( 'el_document_nonce' ); ?>';
    var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    // Helper pour ouvrir le file input de maniere native
    function openFileInput(typeId) {
        var input = document.querySelector('.document_file_input[data-type-id="' + typeId + '"]');
        if (input) {
            input.click();
        }
    }

    // Click sur bouton upload
    $(document).on('click', '.btn_upload_document', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var typeId = $(this).attr('data-type-id');
        openFileInput(typeId);
    });

    // Click sur zone upload
    $(document).on('click', '.document_upload_zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var typeId = $(this).attr('data-type-id');
        openFileInput(typeId);
    });

    // Drag & Drop
    $(document).on('dragover', '.document_upload_zone', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    $(document).on('dragleave', '.document_upload_zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });

    $(document).on('drop', '.document_upload_zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        var typeId = $(this).attr('data-type-id');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadDocument(typeId, files[0], null);
        }
    });

    // Selection fichier
    $(document).on('change', '.document_file_input', function() {
        var typeId = $(this).attr('data-type-id');
        var files = this.files;
        if (files.length > 0) {
            uploadDocument(typeId, files[0], null);
        }
    });

    // Remplacer document
    $(document).on('click', '.btn_replace_document', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var typeId = $(this).attr('data-type-id');
        var docId = $(this).attr('data-doc-id');

        var input = document.createElement('input');
        input.type = 'file';
        input.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx';
        input.style.display = 'none';
        input.onchange = function() {
            if (this.files.length > 0) {
                uploadDocument(typeId, this.files[0], docId);
            }
            document.body.removeChild(input);
        };
        document.body.appendChild(input);
        input.click();
    });

    // Supprimer document
    $(document).on('click', '.btn_delete_document', function() {
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
        var $card = $('.document_card').filter('[data-type-id="' + typeId + '"]');
        var $progress = $card.find('.document_upload_progress');
        var $progressFill = $progress.find('.progress_bar_fill');
        var $progressText = $progress.find('.progress_bar_text');

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
                    $progressFill.css('width', '100%');
                    $progressText.text('<?php esc_html_e( 'Upload reussi !', 'eventlist' ); ?>');
                    setTimeout(function() {
                        location.reload();
                    }, 800);
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
