<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Gestion des dossiers de la galerie vendor
 */
class EL_Vendor_Folders {

    /**
     * Table des dossiers
     */
    private static $table_name = 'vendor_gallery_folders';

    /**
     * Récupérer le nom complet de la table
     */
    private static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    /**
     * Créer un nouveau dossier
     */
    public static function create_folder( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'user_id'     => get_current_user_id(),
            'parent_id'   => 0,
            'name'        => '',
            'description' => '',
            'color'       => '#FF6B35',
            'icon'        => 'folder',
            'order_num'   => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        // Validation
        if ( empty( $args['name'] ) ) {
            return new WP_Error( 'empty_name', __( 'Le nom du dossier est requis', 'eventlist' ) );
        }

        // Générer le slug
        $slug = sanitize_title( $args['name'] );

        // Vérifier si le slug existe déjà pour cet utilisateur
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . self::get_table_name() . " WHERE user_id = %d AND slug = %s AND parent_id = %d",
            $args['user_id'],
            $slug,
            $args['parent_id']
        ) );

        if ( $exists ) {
            $slug = $slug . '-' . time();
        }

        // Insérer le dossier
        $inserted = $wpdb->insert(
            self::get_table_name(),
            array(
                'user_id'     => $args['user_id'],
                'parent_id'   => $args['parent_id'],
                'name'        => sanitize_text_field( $args['name'] ),
                'slug'        => $slug,
                'description' => sanitize_textarea_field( $args['description'] ),
                'color'       => sanitize_hex_color( $args['color'] ),
                'icon'        => sanitize_text_field( $args['icon'] ),
                'order_num'   => absint( $args['order_num'] ),
                'created_at'  => current_time( 'mysql' ),
                'updated_at'  => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
        );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return new WP_Error( 'insert_failed', __( 'Impossible de créer le dossier', 'eventlist' ) );
    }

    /**
     * Mettre à jour un dossier
     */
    public static function update_folder( $folder_id, $args = array() ) {
        global $wpdb;

        $folder = self::get_folder( $folder_id );

        if ( ! $folder ) {
            return new WP_Error( 'folder_not_found', __( 'Dossier introuvable', 'eventlist' ) );
        }

        // Vérifier les permissions
        if ( $folder->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Permission refusée', 'eventlist' ) );
        }

        $update_data = array(
            'updated_at' => current_time( 'mysql' ),
        );

        if ( isset( $args['name'] ) && ! empty( $args['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $args['name'] );
            $update_data['slug'] = sanitize_title( $args['name'] );
        }

        if ( isset( $args['parent_id'] ) ) {
            $update_data['parent_id'] = absint( $args['parent_id'] );
        }

        if ( isset( $args['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $args['description'] );
        }

        if ( isset( $args['color'] ) ) {
            $update_data['color'] = sanitize_hex_color( $args['color'] );
        }

        if ( isset( $args['icon'] ) ) {
            $update_data['icon'] = sanitize_text_field( $args['icon'] );
        }

        if ( isset( $args['order_num'] ) ) {
            $update_data['order_num'] = absint( $args['order_num'] );
        }

        $updated = $wpdb->update(
            self::get_table_name(),
            $update_data,
            array( 'id' => $folder_id ),
            null,
            array( '%d' )
        );

        if ( $updated !== false ) {
            return true;
        }

        return new WP_Error( 'update_failed', __( 'Impossible de mettre à jour le dossier', 'eventlist' ) );
    }

    /**
     * Supprimer un dossier
     */
    public static function delete_folder( $folder_id, $move_to_parent = true ) {
        global $wpdb;

        $folder = self::get_folder( $folder_id );

        if ( ! $folder ) {
            return new WP_Error( 'folder_not_found', __( 'Dossier introuvable', 'eventlist' ) );
        }

        // Vérifier les permissions
        if ( $folder->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Permission refusée', 'eventlist' ) );
        }

        // Gérer les sous-dossiers
        $subfolders = self::get_subfolders( $folder_id );
        if ( ! empty( $subfolders ) ) {
            if ( $move_to_parent ) {
                // Déplacer les sous-dossiers vers le parent
                foreach ( $subfolders as $subfolder ) {
                    self::update_folder( $subfolder->id, array( 'parent_id' => $folder->parent_id ) );
                }
            } else {
                // Supprimer récursivement
                foreach ( $subfolders as $subfolder ) {
                    self::delete_folder( $subfolder->id, false );
                }
            }
        }

        // Déplacer ou supprimer les images
        $table_images = $wpdb->prefix . 'vendor_gallery_images';
        if ( $move_to_parent ) {
            $wpdb->update(
                $table_images,
                array( 'folder_id' => $folder->parent_id ),
                array( 'folder_id' => $folder_id ),
                array( '%d' ),
                array( '%d' )
            );
        } else {
            // Déplacer vers la racine (folder_id = 0)
            $wpdb->update(
                $table_images,
                array( 'folder_id' => 0 ),
                array( 'folder_id' => $folder_id ),
                array( '%d' ),
                array( '%d' )
            );
        }

        // Supprimer le dossier
        $deleted = $wpdb->delete(
            self::get_table_name(),
            array( 'id' => $folder_id ),
            array( '%d' )
        );

        if ( $deleted ) {
            return true;
        }

        return new WP_Error( 'delete_failed', __( 'Impossible de supprimer le dossier', 'eventlist' ) );
    }

    /**
     * Récupérer un dossier par ID
     */
    public static function get_folder( $folder_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::get_table_name() . " WHERE id = %d",
            $folder_id
        ) );
    }

    /**
     * Récupérer tous les dossiers d'un utilisateur
     */
    public static function get_user_folders( $user_id = null, $parent_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $where = $wpdb->prepare( "WHERE user_id = %d", $user_id );

        if ( ! is_null( $parent_id ) ) {
            $where .= $wpdb->prepare( " AND parent_id = %d", $parent_id );
        }

        $query = "SELECT * FROM " . self::get_table_name() . " {$where} ORDER BY order_num ASC, name ASC";

        return $wpdb->get_results( $query );
    }

    /**
     * Récupérer les sous-dossiers d'un dossier
     */
    public static function get_subfolders( $folder_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::get_table_name() . " WHERE parent_id = %d ORDER BY order_num ASC, name ASC",
            $folder_id
        ) );
    }

    /**
     * Récupérer l'arborescence complète d'un utilisateur
     */
    public static function get_folder_tree( $user_id = null, $parent_id = 0 ) {
        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $folders = self::get_user_folders( $user_id, $parent_id );
        $tree = array();

        foreach ( $folders as $folder ) {
            $folder->children = self::get_folder_tree( $user_id, $folder->id );
            $folder->image_count = self::get_folder_image_count( $folder->id );
            $tree[] = $folder;
        }

        return $tree;
    }

    /**
     * Compter les images dans un dossier
     */
    public static function get_folder_image_count( $folder_id ) {
        global $wpdb;

        $table_images = $wpdb->prefix . 'vendor_gallery_images';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_images} WHERE folder_id = %d",
            $folder_id
        ) );
    }

    /**
     * Déplacer un dossier
     */
    public static function move_folder( $folder_id, $new_parent_id ) {
        global $wpdb;

        // Vérifier que le nouveau parent n'est pas un enfant du dossier à déplacer
        if ( self::is_ancestor( $folder_id, $new_parent_id ) ) {
            return new WP_Error( 'invalid_parent', __( 'Impossible de déplacer un dossier dans un de ses sous-dossiers', 'eventlist' ) );
        }

        return self::update_folder( $folder_id, array( 'parent_id' => $new_parent_id ) );
    }

    /**
     * Vérifier si un dossier est l'ancêtre d'un autre
     */
    private static function is_ancestor( $ancestor_id, $child_id ) {
        $folder = self::get_folder( $child_id );

        if ( ! $folder || $folder->parent_id == 0 ) {
            return false;
        }

        if ( $folder->parent_id == $ancestor_id ) {
            return true;
        }

        return self::is_ancestor( $ancestor_id, $folder->parent_id );
    }

    /**
     * Récupérer le chemin complet d'un dossier
     */
    public static function get_folder_path( $folder_id ) {
        $path = array();
        $folder = self::get_folder( $folder_id );

        while ( $folder && $folder->parent_id != 0 ) {
            array_unshift( $path, $folder );
            $folder = self::get_folder( $folder->parent_id );
        }

        if ( $folder && $folder->parent_id == 0 ) {
            array_unshift( $path, $folder );
        }

        return $path;
    }
}
