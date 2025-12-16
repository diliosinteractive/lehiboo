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
                            <strong><?php echo esc_html( $doc->vendor_name ); ?></strong>
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
        $('#reject_modal').fadeOut(200);
    });

    // ESC pour fermer
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#reject_modal').fadeOut(200);
        }
    });

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
