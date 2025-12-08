<?php
/**
 * Gestionnaire de médias pour vendors
 * Template: member-account/?vendor=media-manager
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
// null signifie "toutes les images", 0 = racine, >0 = dossier spécifique
$current_folder = isset( $_GET['folder'] ) ? absint( $_GET['folder'] ) : null;

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

// Taille max fichier
$max_file_size = apply_filters( 'el_vendor_media_max_size', 10 * 1024 * 1024 ); // 10MB
$max_file_size_mb = $max_file_size / ( 1024 * 1024 );
?>

<div class="vendor_wrap">

    <?php echo el_get_template( '/vendor/sidebar.php' ); ?>

    <div class="contents">

        <?php echo el_get_template( '/vendor/heading.php' ); ?>

        <div class="vendor_media_manager" data-nonce="<?php echo esc_attr( $nonce ); ?>">

    <!-- Header -->
    <div class="media_manager_header">
        <div class="header_left">
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
                    <!-- Actions de masse - visibles quand des images sont sélectionnées -->
                    <div class="bulk_actions" style="display: none;">
                        <button type="button" class="el_button el_button_outline btn_bulk_move">
                            <i class="fa fa-folder-open"></i>
                            <?php esc_html_e( 'Déplacer', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="el_button el_button_outline btn_bulk_delete">
                            <i class="fa fa-trash"></i>
                            <?php esc_html_e( 'Supprimer', 'eventlist' ); ?>
                        </button>
                    </div>
                </div>
                <div class="toolbar_right">
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
                <h3 class="modal_title"><?php esc_html_e( 'Mes dossiers', 'eventlist' ); ?></h3>
                <button type="button" class="modal_close">&times;</button>
            </div>
            <div class="modal_body">
                <div class="folder_tree_select">
                    <!-- Sera généré par JavaScript -->
                </div>
                <div class="form_actions">
                    <button type="button" class="el_button el_button_outline btn_cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                    <button type="button" class="el_button el_button_primary btn_move_confirm"><?php esc_html_e( 'Déplacer', 'eventlist' ); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmation suppression -->
    <div class="media_modal modal_delete_confirm" style="display: none;">
        <div class="modal_overlay"></div>
        <div class="modal_content modal_content_small">
            <div class="modal_header">
                <button type="button" class="modal_close">&times;</button>
            </div>
            <div class="modal_body modal_body_centered">
                <p class="confirm_text"><?php esc_html_e( 'Confirmez-vous la suppression des photos sélectionnées ?', 'eventlist' ); ?></p>
                <div class="form_actions">
                    <button type="button" class="el_button el_button_outline btn_cancel"><?php esc_html_e( 'Annuler', 'eventlist' ); ?></button>
                    <button type="button" class="el_button el_button_primary btn_delete_confirm"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></button>
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

    <!-- Modal: Upload d'image -->
    <div class="media_modal modal_upload" style="display: none;">
        <div class="modal_overlay"></div>
        <div class="modal_content modal_content_medium">
            <div class="modal_header">
                <h3 class="modal_title"><?php esc_html_e( 'Ajouter une image', 'eventlist' ); ?></h3>
                <button type="button" class="modal_close">&times;</button>
            </div>
            <div class="modal_body">
                <form class="upload_form" enctype="multipart/form-data">
                    <div class="upload_form_layout">
                        <!-- Colonne gauche: Dropzone -->
                        <div class="upload_form_left">
                            <div class="upload_dropzone">
                                <div class="dropzone_preview" style="display: none;">
                                    <img src="" alt="" class="preview_image">
                                    <button type="button" class="btn_change_image" title="<?php esc_attr_e( 'Changer d\'image', 'eventlist' ); ?>">
                                        <i class="fa fa-sync-alt"></i>
                                    </button>
                                </div>
                                <div class="dropzone_placeholder">
                                    <div class="dropzone_icon">
                                        <i class="fa fa-cloud-upload"></i>
                                    </div>
                                    <div class="dropzone_text">
                                        <p><?php esc_html_e( 'Glissez vos fichiers ici', 'eventlist' ); ?></p>
                                        <span><?php esc_html_e( 'ou cliquez pour parcourir', 'eventlist' ); ?></span>
                                    </div>
                                </div>
                                <input type="file" class="upload_file_input" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                            </div>
                            <div class="dropzone_info">
                                <span class="info_formats">
                                    <?php esc_html_e( 'Formats autorisés :', 'eventlist' ); ?>
                                    JPG, PNG, GIF, WebP
                                </span>
                                <span class="info_size">
                                    <?php esc_html_e( 'Taille max :', 'eventlist' ); ?>
                                    <?php echo esc_html( $max_file_size_mb ); ?> MO <?php esc_html_e( 'par fichier', 'eventlist' ); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Colonne droite: Champs de formulaire -->
                        <div class="upload_form_right">
                            <div class="form_field">
                                <label for="upload_title"><?php esc_html_e( 'Titre :', 'eventlist' ); ?></label>
                                <input type="text" id="upload_title" name="title" placeholder="<?php esc_attr_e( 'Par défaut, nom de votre fichier', 'eventlist' ); ?>">
                            </div>

                            <div class="form_field">
                                <label for="upload_folder"><?php esc_html_e( 'Dossier :', 'eventlist' ); ?></label>
                                <div class="folder_select_wrapper">
                                    <div class="folder_select_display" data-folder-id="<?php echo esc_attr( $current_folder ?: 0 ); ?>">
                                        <span class="folder_icon"><i class="fa fa-<?php echo $current_folder ? 'folder' : 'home'; ?>"></i></span>
                                        <span class="folder_name">
                                            <?php
                                            if ( $current_folder_data ) {
                                                echo esc_html( $current_folder_data->name );
                                            } else {
                                                esc_html_e( 'Toutes les images', 'eventlist' );
                                            }
                                            ?>
                                        </span>
                                        <span class="folder_toggle_btn"><i class="fa fa-chevron-down"></i></span>
                                        <button type="button" class="folder_clear_btn" title="<?php esc_attr_e( 'Retirer', 'eventlist' ); ?>"><i class="fa fa-times"></i></button>
                                    </div>
                                    <input type="hidden" name="folder_id" value="<?php echo esc_attr( $current_folder ?: 0 ); ?>">
                                    <div class="folder_select_dropdown" style="display: none;">
                                        <!-- Sera rempli par JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <div class="form_field">
                                <label for="upload_alt"><?php esc_html_e( 'Texte alternatif :', 'eventlist' ); ?></label>
                                <input type="text" id="upload_alt" name="alt_text" placeholder="<?php esc_attr_e( 'Par défaut, nom du titre', 'eventlist' ); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div class="upload_progress_bar" style="display: none;">
                        <div class="progress_bar">
                            <div class="progress_fill" style="width: 0%;"></div>
                        </div>
                        <span class="progress_text"><?php esc_html_e( 'Compression...', 'eventlist' ); ?></span>
                    </div>

                    <div class="form_actions">
                        <button type="submit" class="el_button el_button_primary btn_submit_upload" disabled>
                            <i class="fa fa-cloud-upload"></i>
                            <?php esc_html_e( 'Ajouter l\'image', 'eventlist' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div><!-- /.vendor_media_manager -->

    </div><!-- /.contents -->

</div><!-- /.vendor_wrap -->
