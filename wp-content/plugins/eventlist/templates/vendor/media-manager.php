<?php
/**
 * Gestionnaire de médias pour vendors
 * Template: member-account/?vendor=media-manager
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
$current_folder = isset( $_GET['folder'] ) ? absint( $_GET['folder'] ) : 0;

// Récupérer l'arborescence des dossiers
$folders_tree = EL_Vendor_Folders::get_folder_tree( $user_id );

// Récupérer le dossier actuel si spécifié
$current_folder_data = null;
if ( $current_folder > 0 ) {
    $current_folder_data = EL_Vendor_Folders::get_folder( $current_folder );

    // Vérifier les permissions
    if ( ! $current_folder_data || $current_folder_data->user_id != $user_id ) {
        $current_folder = 0;
        $current_folder_data = null;
    }
}

// Récupérer le chemin (breadcrumb)
$folder_path = $current_folder > 0 ? EL_Vendor_Folders::get_folder_path( $current_folder ) : array();

// Nonce pour AJAX
$nonce = wp_create_nonce( 'el_vendor_media_nonce' );
?>

<div class="vendor_wrap">

    <?php echo el_get_template( '/vendor/sidebar.php' ); ?>

    <div class="contents">

        <?php echo el_get_template( '/vendor/heading.php' ); ?>

        <div class="vendor_media_manager" data-nonce="<?php echo esc_attr( $nonce ); ?>">

    <!-- Header -->
    <div class="media_manager_header">
        <div class="header_left">
            <h2><?php esc_html_e( 'Gestionnaire de Médias', 'eventlist' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Organisez vos images dans des dossiers et sous-dossiers', 'eventlist' ); ?></p>
        </div>
        <div class="header_right">
            <button type="button" class="el_button btn_create_folder">
                <i class="fa fa-folder-plus"></i>
                <?php esc_html_e( 'Nouveau dossier', 'eventlist' ); ?>
            </button>
            <button type="button" class="el_button el_button_primary btn_upload_images">
                <i class="fa fa-cloud-upload"></i>
                <?php esc_html_e( 'Ajouter des images', 'eventlist' ); ?>
            </button>
        </div>
    </div>

    <div class="media_manager_content">

        <!-- Sidebar: Arborescence des dossiers -->
        <aside class="media_sidebar">
            <?php include EL_PLUGIN_PATH . 'templates/vendor/partials/_folders-sidebar.php'; ?>
        </aside>

        <!-- Zone principale -->
        <main class="media_main">

            <!-- Breadcrumb -->
            <?php if ( ! empty( $folder_path ) || $current_folder === 0 ) : ?>
                <nav class="media_breadcrumb">
                    <a href="?vendor=media-manager" class="breadcrumb_item <?php echo $current_folder === 0 ? 'active' : ''; ?>">
                        <i class="fa fa-home"></i>
                        <?php esc_html_e( 'Racine', 'eventlist' ); ?>
                    </a>
                    <?php foreach ( $folder_path as $folder ) : ?>
                        <span class="separator">/</span>
                        <a href="?vendor=media-manager&folder=<?php echo esc_attr( $folder->id ); ?>"
                           class="breadcrumb_item <?php echo $current_folder == $folder->id ? 'active' : ''; ?>">
                            <?php echo esc_html( $folder->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="media_toolbar">
                <div class="toolbar_left">
                    <div class="search_box">
                        <input type="text" class="media_search" placeholder="<?php esc_attr_e( 'Rechercher des images...', 'eventlist' ); ?>">
                        <i class="fa fa-search"></i>
                    </div>
                    <div class="view_mode">
                        <button type="button" class="btn_view_grid active" data-view="grid" title="<?php esc_attr_e( 'Vue grille', 'eventlist' ); ?>">
                            <i class="fa fa-th"></i>
                        </button>
                        <button type="button" class="btn_view_list" data-view="list" title="<?php esc_attr_e( 'Vue liste', 'eventlist' ); ?>">
                            <i class="fa fa-list"></i>
                        </button>
                    </div>
                </div>
                <div class="toolbar_right">
                    <div class="bulk_actions" style="display: none;">
                        <select class="bulk_action_select">
                            <option value=""><?php esc_html_e( 'Actions groupées', 'eventlist' ); ?></option>
                            <option value="move"><?php esc_html_e( 'Déplacer vers...', 'eventlist' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></option>
                        </select>
                        <button type="button" class="el_button btn_bulk_apply"><?php esc_html_e( 'Appliquer', 'eventlist' ); ?></button>
                    </div>
                    <span class="images_count">
                        <span class="count_number">0</span>
                        <?php esc_html_e( 'images', 'eventlist' ); ?>
                    </span>
                </div>
            </div>

            <!-- Zone d'upload drag & drop -->
            <?php include EL_PLUGIN_PATH . 'templates/vendor/partials/_upload-zone.php'; ?>

            <!-- Grille d'images -->
            <?php include EL_PLUGIN_PATH . 'templates/vendor/partials/_media-grid.php'; ?>

            <!-- Pagination -->
            <div class="media_pagination">
                <!-- Sera géré par JavaScript -->
            </div>

        </main>

    </div>

    <!-- Modal: Créer/Éditer dossier -->
    <div class="media_modal modal_folder" style="display: none;">
        <div class="modal_overlay"></div>
        <div class="modal_content">
            <div class="modal_header">
                <h3 class="modal_title"><?php esc_html_e( 'Nouveau dossier', 'eventlist' ); ?></h3>
                <button type="button" class="modal_close">&times;</button>
            </div>
            <div class="modal_body">
                <form class="folder_form">
                    <input type="hidden" name="folder_id" value="">
                    <input type="hidden" name="parent_id" value="<?php echo esc_attr( $current_folder ); ?>">

                    <div class="form_field">
                        <label><?php esc_html_e( 'Nom du dossier', 'eventlist' ); ?> <span class="required">*</span></label>
                        <input type="text" name="name" required autocomplete="off">
                    </div>

                    <div class="form_field">
                        <label><?php esc_html_e( 'Description', 'eventlist' ); ?></label>
                        <textarea name="description" rows="3"></textarea>
                    </div>

                    <div class="form_field">
                        <label><?php esc_html_e( 'Couleur', 'eventlist' ); ?></label>
                        <div class="color_picker">
                            <input type="color" name="color" value="#FF6B35">
                            <div class="color_presets">
                                <button type="button" class="color_preset" data-color="#FF6B35" style="background: #FF6B35;"></button>
                                <button type="button" class="color_preset" data-color="#4A90E2" style="background: #4A90E2;"></button>
                                <button type="button" class="color_preset" data-color="#7CB342" style="background: #7CB342;"></button>
                                <button type="button" class="color_preset" data-color="#FFA726" style="background: #FFA726;"></button>
                                <button type="button" class="color_preset" data-color="#AB47BC" style="background: #AB47BC;"></button>
                                <button type="button" class="color_preset" data-color="#EC407A" style="background: #EC407A;"></button>
                            </div>
                        </div>
                    </div>

                    <div class="form_actions">
                        <button type="button" class="el_button btn_cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                        <button type="submit" class="el_button el_button_primary"><?php esc_html_e( 'Créer', 'eventlist' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Déplacer vers -->
    <div class="media_modal modal_move" style="display: none;">
        <div class="modal_overlay"></div>
        <div class="modal_content">
            <div class="modal_header">
                <h3 class="modal_title"><?php esc_html_e( 'Déplacer vers...', 'eventlist' ); ?></h3>
                <button type="button" class="modal_close">&times;</button>
            </div>
            <div class="modal_body">
                <div class="folder_tree_select">
                    <!-- Sera généré par JavaScript -->
                </div>
                <div class="form_actions">
                    <button type="button" class="el_button btn_cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                    <button type="button" class="el_button el_button_primary btn_move_confirm"><?php esc_html_e( 'Déplacer', 'eventlist' ); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Viewer d'image -->
    <div class="media_modal modal_viewer" style="display: none;">
        <div class="modal_overlay"></div>
        <div class="modal_content modal_content_large">
            <button type="button" class="modal_close">&times;</button>
            <div class="viewer_content">
                <img src="" alt="" class="viewer_image">
            </div>
            <div class="viewer_info">
                <h4 class="viewer_title"></h4>
                <div class="viewer_meta">
                    <span class="meta_size"></span>
                    <span class="meta_date"></span>
                </div>
            </div>
        </div>
    </div>

<script>
// Configuration pour JavaScript
window.EL_MediaManager = {
    currentFolder: <?php echo absint( $current_folder ); ?>,
    nonce: '<?php echo esc_js( $nonce ); ?>',
    ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
    i18n: {
        confirmDelete: '<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette image ?', 'eventlist' ); ?>',
        confirmDeleteFolder: '<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer ce dossier ?', 'eventlist' ); ?>',
        uploadSuccess: '<?php esc_html_e( 'Upload réussi !', 'eventlist' ); ?>',
        uploadError: '<?php esc_html_e( 'Erreur lors de l\'upload', 'eventlist' ); ?>',
        loading: '<?php esc_html_e( 'Chargement...', 'eventlist' ); ?>',
        noImages: '<?php esc_html_e( 'Aucune image dans ce dossier', 'eventlist' ); ?>',
        dropHere: '<?php esc_html_e( 'Déposez vos fichiers ici', 'eventlist' ); ?>',
    }
};
</script>

        </div><!-- /.vendor_media_manager -->

    </div><!-- /.contents -->

</div><!-- /.vendor_wrap -->
