<?php
/**
 * Admin View: Types de Documents
 * V1 Le Hiboo - Gestion des documents securises
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Recuperer tous les types
$document_types = EL_Document_Types::get_all( false );
?>

<div class="wrap el-document-types-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-media-document" style="font-size:30px;margin-right:10px;"></span>
        <?php esc_html_e( 'Types de Documents', 'eventlist' ); ?>
    </h1>
    <button type="button" class="page-title-action" id="btn_add_document_type">
        <span class="dashicons dashicons-plus-alt" style="vertical-align:middle;"></span>
        <?php esc_html_e( 'Ajouter un type', 'eventlist' ); ?>
    </button>
    <hr class="wp-header-end">

    <div class="el-admin-notice info" style="background:#e7f3fe;border-left:4px solid #2196F3;padding:12px;margin:20px 0;">
        <p style="margin:0;">
            <strong><?php esc_html_e( 'Information :', 'eventlist' ); ?></strong>
            <?php esc_html_e( 'Les types de documents definis ici seront demandes aux partenaires dans leur espace "Mes Documents". Les documents marques comme "Requis" doivent etre approuves avant que le partenaire puisse creer ou publier des activites.', 'eventlist' ); ?>
        </p>
    </div>

    <!-- Liste des types de documents -->
    <table class="wp-list-table widefat fixed striped" id="document_types_table">
        <thead>
            <tr>
                <th style="width:30px;"><?php esc_html_e( 'Ordre', 'eventlist' ); ?></th>
                <th><?php esc_html_e( 'Nom', 'eventlist' ); ?></th>
                <th><?php esc_html_e( 'Extensions', 'eventlist' ); ?></th>
                <th><?php esc_html_e( 'Taille max', 'eventlist' ); ?></th>
                <th style="width:100px;text-align:center;"><?php esc_html_e( 'Requis', 'eventlist' ); ?></th>
                <th style="width:100px;text-align:center;"><?php esc_html_e( 'Actif', 'eventlist' ); ?></th>
                <th style="width:150px;"><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
            </tr>
        </thead>
        <tbody id="document_types_list">
            <?php if ( empty( $document_types ) ) : ?>
                <tr class="no-items">
                    <td colspan="7" style="text-align:center;padding:20px;">
                        <?php esc_html_e( 'Aucun type de document defini.', 'eventlist' ); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $document_types as $type ) : ?>
                    <tr data-type-id="<?php echo esc_attr( $type->id ); ?>">
                        <td class="drag-handle" style="cursor:move;text-align:center;">
                            <span class="dashicons dashicons-menu"></span>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $type->name ); ?></strong>
                            <?php if ( $type->description ) : ?>
                                <br><small style="color:#666;"><?php echo esc_html( substr( $type->description, 0, 80 ) ); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo esc_html( $type->allowed_extensions ); ?></code>
                        </td>
                        <td>
                            <?php echo esc_html( size_format( $type->max_file_size ) ); ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ( $type->is_required ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color:#28a745;" title="<?php esc_attr_e( 'Requis', 'eventlist' ); ?>"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-minus" style="color:#999;" title="<?php esc_attr_e( 'Optionnel', 'eventlist' ); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ( $type->is_active ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color:#28a745;" title="<?php esc_attr_e( 'Actif', 'eventlist' ); ?>"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss" style="color:#dc3545;" title="<?php esc_attr_e( 'Inactif', 'eventlist' ); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small btn_edit_type" data-type-id="<?php echo esc_attr( $type->id ); ?>">
                                <span class="dashicons dashicons-edit" style="vertical-align:middle;"></span>
                            </button>
                            <button type="button" class="button button-small btn_delete_type" data-type-id="<?php echo esc_attr( $type->id ); ?>" data-type-name="<?php echo esc_attr( $type->name ); ?>">
                                <span class="dashicons dashicons-trash" style="vertical-align:middle;color:#dc3545;"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajout/Edition -->
<div id="document_type_modal" class="el-modal" style="display:none;">
    <div class="el-modal-overlay"></div>
    <div class="el-modal-content">
        <div class="el-modal-header">
            <h2 id="modal_title"><?php esc_html_e( 'Ajouter un type de document', 'eventlist' ); ?></h2>
            <button type="button" class="el-modal-close">&times;</button>
        </div>
        <form id="document_type_form">
            <input type="hidden" name="type_id" id="type_id" value="">

            <div class="el-modal-body">
                <table class="form-table">
                    <tr>
                        <th><label for="type_name"><?php esc_html_e( 'Nom', 'eventlist' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="text" name="name" id="type_name" class="regular-text" required>
                            <p class="description"><?php esc_html_e( 'Ex: KBIS, Piece d\'identite, RIB...', 'eventlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="type_description"><?php esc_html_e( 'Description', 'eventlist' ); ?></label></th>
                        <td>
                            <textarea name="description" id="type_description" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Instructions pour le partenaire sur ce document.', 'eventlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="type_extensions"><?php esc_html_e( 'Extensions autorisees', 'eventlist' ); ?></label></th>
                        <td>
                            <input type="text" name="allowed_extensions" id="type_extensions" class="regular-text" value="pdf,jpg,jpeg,png">
                            <p class="description"><?php esc_html_e( 'Separez par des virgules: pdf,jpg,jpeg,png,doc,docx', 'eventlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="type_max_size"><?php esc_html_e( 'Taille maximum (Mo)', 'eventlist' ); ?></label></th>
                        <td>
                            <input type="number" name="max_file_size_mb" id="type_max_size" class="small-text" value="5" min="1" max="50">
                            <p class="description"><?php esc_html_e( 'Taille maximum en megaoctets.', 'eventlist' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Options', 'eventlist' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_required" id="type_required" value="1">
                                <?php esc_html_e( 'Document requis', 'eventlist' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Le partenaire ne pourra pas creer d\'activites sans ce document approuve.', 'eventlist' ); ?></p>
                            <br>
                            <label>
                                <input type="checkbox" name="required_at_registration" id="type_required_registration" value="1">
                                <?php esc_html_e( 'Requis a l\'inscription', 'eventlist' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Ce document sera demande des l\'inscription du partenaire.', 'eventlist' ); ?></p>
                            <br>
                            <label>
                                <input type="checkbox" name="is_active" id="type_active" value="1" checked>
                                <?php esc_html_e( 'Actif', 'eventlist' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Desactiver pour masquer ce type sans le supprimer.', 'eventlist' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="el-modal-footer">
                <button type="button" class="button el-modal-cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                <button type="submit" class="button button-primary" id="btn_save_type">
                    <span class="dashicons dashicons-saved" style="vertical-align:middle;"></span>
                    <?php esc_html_e( 'Enregistrer', 'eventlist' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.el-document-types-admin .wp-heading-inline {
    display: flex;
    align-items: center;
}
.el-document-types-admin .page-title-action {
    margin-left: 10px;
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
    width: 600px;
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
.required {
    color: #dc3545;
}
#document_types_table .drag-handle {
    color: #999;
}
#document_types_table .drag-handle:hover {
    color: #333;
}
</style>

<script>
jQuery(document).ready(function($) {
    var adminNonce = '<?php echo wp_create_nonce( 'el_admin_document_nonce' ); ?>';
    var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

    // Ouvrir modal ajout
    $('#btn_add_document_type').on('click', function() {
        $('#modal_title').text('<?php esc_html_e( 'Ajouter un type de document', 'eventlist' ); ?>');
        $('#document_type_form')[0].reset();
        $('#type_id').val('');
        $('#type_active').prop('checked', true);
        $('#document_type_modal').fadeIn(200);
    });

    // Fermer modal
    $('.el-modal-close, .el-modal-cancel, .el-modal-overlay').on('click', function() {
        $('#document_type_modal').fadeOut(200);
    });

    // ESC pour fermer
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#document_type_modal').fadeOut(200);
        }
    });

    // Editer un type
    $(document).on('click', '.btn_edit_type', function() {
        var typeId = $(this).data('type-id');

        // Charger les donnees via AJAX
        $.post(ajaxUrl, {
            action: 'el_admin_get_doc_types',
            nonce: adminNonce,
            active_only: false
        }, function(response) {
            if (response.success) {
                var type = response.data.types.find(function(t) { return t.id == typeId; });
                if (type) {
                    $('#modal_title').text('<?php esc_html_e( 'Modifier le type de document', 'eventlist' ); ?>');
                    $('#type_id').val(type.id);
                    $('#type_name').val(type.name);
                    $('#type_description').val(type.description);
                    $('#type_extensions').val(type.allowed_extensions);
                    $('#type_max_size').val(Math.round(type.max_file_size / 1048576));
                    $('#type_required').prop('checked', type.is_required);
                    $('#type_required_registration').prop('checked', type.required_at_registration);
                    $('#type_active').prop('checked', type.is_active);
                    $('#document_type_modal').fadeIn(200);
                }
            }
        });
    });

    // Sauvegarder
    $('#document_type_form').on('submit', function(e) {
        e.preventDefault();

        var typeId = $('#type_id').val();
        var action = typeId ? 'el_admin_update_doc_type' : 'el_admin_create_doc_type';

        var data = {
            action: action,
            nonce: adminNonce,
            type_id: typeId,
            name: $('#type_name').val(),
            description: $('#type_description').val(),
            allowed_extensions: $('#type_extensions').val(),
            max_file_size: $('#type_max_size').val() * 1048576,
            is_required: $('#type_required').is(':checked') ? 1 : 0,
            required_at_registration: $('#type_required_registration').is(':checked') ? 1 : 0,
            is_active: $('#type_active').is(':checked') ? 1 : 0
        };

        $('#btn_save_type').prop('disabled', true).text('<?php esc_html_e( 'Enregistrement...', 'eventlist' ); ?>');

        $.post(ajaxUrl, data, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Erreur', 'eventlist' ); ?>');
                $('#btn_save_type').prop('disabled', false).html('<span class="dashicons dashicons-saved" style="vertical-align:middle;"></span> <?php esc_html_e( 'Enregistrer', 'eventlist' ); ?>');
            }
        });
    });

    // Supprimer un type
    $(document).on('click', '.btn_delete_type', function() {
        var typeId = $(this).data('type-id');
        var typeName = $(this).data('type-name');

        if (!confirm('<?php esc_html_e( 'Etes-vous sur de vouloir supprimer le type', 'eventlist' ); ?> "' + typeName + '" ?')) {
            return;
        }

        $.post(ajaxUrl, {
            action: 'el_admin_delete_doc_type',
            nonce: adminNonce,
            type_id: typeId
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Erreur lors de la suppression', 'eventlist' ); ?>');
            }
        });
    });
});
</script>
