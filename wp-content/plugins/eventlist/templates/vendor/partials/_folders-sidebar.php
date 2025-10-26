<?php
/**
 * Sidebar: Arborescence des dossiers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Fonction récursive pour afficher l'arborescence
function el_render_folder_tree( $folders, $current_folder = 0, $level = 0 ) {
    if ( empty( $folders ) ) return;

    echo '<ul class="folders_list level_' . $level . '">';

    foreach ( $folders as $folder ) {
        $is_active = ( $current_folder == $folder->id );
        $has_children = ! empty( $folder->children );

        ?>
        <li class="folder_item <?php echo $is_active ? 'active' : ''; ?>"
            data-folder-id="<?php echo esc_attr( $folder->id ); ?>"
            data-parent-id="<?php echo esc_attr( $folder->parent_id ); ?>">

            <div class="folder_header">
                <?php if ( $has_children ) : ?>
                    <button type="button" class="folder_toggle">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                <?php else : ?>
                    <span class="folder_toggle_spacer"></span>
                <?php endif; ?>

                <a href="?vendor=media-manager&folder=<?php echo esc_attr( $folder->id ); ?>" class="folder_link">
                    <span class="folder_icon" style="color: <?php echo esc_attr( $folder->color ); ?>;">
                        <i class="fa fa-folder"></i>
                    </span>
                    <span class="folder_name"><?php echo esc_html( $folder->name ); ?></span>
                    <span class="folder_count">(<?php echo esc_html( $folder->image_count ); ?>)</span>
                </a>

                <div class="folder_actions">
                    <button type="button" class="folder_action_btn btn_folder_menu" title="<?php esc_attr_e( 'Actions', 'eventlist' ); ?>">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="folder_menu" style="display: none;">
                        <button type="button" class="menu_item btn_edit_folder">
                            <i class="fa fa-edit"></i>
                            <?php esc_html_e( 'Renommer', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="menu_item btn_add_subfolder">
                            <i class="fa fa-folder-plus"></i>
                            <?php esc_html_e( 'Nouveau sous-dossier', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="menu_item btn_delete_folder">
                            <i class="fa fa-trash"></i>
                            <?php esc_html_e( 'Supprimer', 'eventlist' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ( $has_children ) : ?>
                <div class="folder_children" style="display: none;">
                    <?php el_render_folder_tree( $folder->children, $current_folder, $level + 1 ); ?>
                </div>
            <?php endif; ?>

        </li>
        <?php
    }

    echo '</ul>';
}
?>

<div class="folders_sidebar_header">
    <h3><?php esc_html_e( 'Mes dossiers', 'eventlist' ); ?></h3>
</div>

<div class="folders_tree">

    <!-- Toutes les images -->
    <ul class="folders_list level_0">
        <li class="folder_item folder_all <?php echo $current_folder === null ? 'active' : ''; ?>"
            data-folder-id="-1">
            <div class="folder_header">
                <span class="folder_toggle_spacer"></span>
                <a href="?vendor=media-manager" class="folder_link">
                    <span class="folder_icon">
                        <i class="fa fa-home"></i>
                    </span>
                    <span class="folder_name"><?php esc_html_e( 'Toutes les images', 'eventlist' ); ?></span>
                    <span class="folder_count" id="all_count">(0)</span>
                </a>
            </div>
        </li>
    </ul>

    <!-- Dossiers utilisateur -->
    <?php
    if ( ! empty( $folders_tree ) ) {
        el_render_folder_tree( $folders_tree, $current_folder, 0 );
    }
    ?>

</div>

<!-- Empty state -->
<?php if ( empty( $folders_tree ) ) : ?>
    <div class="folders_empty">
        <p><?php esc_html_e( 'Aucun dossier créé', 'eventlist' ); ?></p>
        <button type="button" class="el_button el_button_small btn_create_first_folder">
            <i class="fa fa-folder-plus"></i>
            <?php esc_html_e( 'Créer un dossier', 'eventlist' ); ?>
        </button>
    </div>
<?php endif; ?>
