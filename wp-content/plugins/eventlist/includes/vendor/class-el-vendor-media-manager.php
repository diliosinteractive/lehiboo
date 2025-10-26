<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Gestionnaire de médias pour les vendors
 */
class EL_Vendor_Media_Manager {

    /**
     * Table des images
     */
    private static $table_name = 'vendor_gallery_images';

    /**
     * Récupérer le nom complet de la table
     */
    private static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    /**
     * Upload d'une ou plusieurs images
     */
    public static function upload_images( $files, $folder_id = 0, $user_id = null ) {
        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
        }

        $uploaded_files = array();
        $errors = array();

        // Gérer les uploads multiples
        if ( isset( $files['name'] ) && is_array( $files['name'] ) ) {
            $file_count = count( $files['name'] );

            for ( $i = 0; $i < $file_count; $i++ ) {
                $file = array(
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                );

                $result = self::process_single_upload( $file, $folder_id, $user_id );

                if ( is_wp_error( $result ) ) {
                    $errors[] = array(
                        'file' => $file['name'],
                        'error' => $result->get_error_message(),
                    );
                } else {
                    $uploaded_files[] = $result;
                }
            }
        } else {
            // Upload unique
            $result = self::process_single_upload( $files, $folder_id, $user_id );

            if ( is_wp_error( $result ) ) {
                $errors[] = array(
                    'file' => $files['name'],
                    'error' => $result->get_error_message(),
                );
            } else {
                $uploaded_files[] = $result;
            }
        }

        return array(
            'success' => $uploaded_files,
            'errors'  => $errors,
        );
    }

    /**
     * Traiter un upload unique
     */
    private static function process_single_upload( $file, $folder_id, $user_id ) {
        // Validation du fichier
        $validation = self::validate_file( $file );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        // Upload via WordPress
        $upload_overrides = array(
            'test_form' => false,
            'mimes'     => self::get_allowed_mime_types(),
        );

        $uploaded_file = wp_handle_upload( $file, $upload_overrides );

        if ( isset( $uploaded_file['error'] ) ) {
            return new WP_Error( 'upload_error', $uploaded_file['error'] );
        }

        // Créer l'attachment
        $attachment_data = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_author'    => $user_id,
        );

        $attachment_id = wp_insert_attachment( $attachment_data, $uploaded_file['file'] );

        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        // Générer les métadonnées et thumbnails
        $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
        wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

        // Ajouter dans notre table
        $added = self::add_image_to_folder( $attachment_id, $folder_id, $user_id );

        if ( is_wp_error( $added ) ) {
            // Supprimer l'attachment si l'ajout échoue
            wp_delete_attachment( $attachment_id, true );
            return $added;
        }

        return array(
            'attachment_id' => $attachment_id,
            'url'           => wp_get_attachment_url( $attachment_id ),
            'thumb'         => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
            'title'         => get_the_title( $attachment_id ),
            'folder_id'     => $folder_id,
        );
    }

    /**
     * Valider un fichier avant upload
     */
    private static function validate_file( $file ) {
        // Vérifier les erreurs d'upload
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return new WP_Error( 'upload_error', self::get_upload_error_message( $file['error'] ) );
        }

        // Vérifier le type MIME
        $allowed_mimes = self::get_allowed_mime_types();
        $file_type = wp_check_filetype( $file['name'], $allowed_mimes );

        if ( ! $file_type['type'] ) {
            return new WP_Error( 'invalid_file_type', __( 'Type de fichier non autorisé', 'eventlist' ) );
        }

        // Vérifier la taille (10MB par défaut)
        $max_size = apply_filters( 'el_vendor_media_max_size', 10 * 1024 * 1024 ); // 10MB

        if ( $file['size'] > $max_size ) {
            return new WP_Error( 'file_too_large', sprintf(
                __( 'Le fichier est trop volumineux. Taille maximum: %s', 'eventlist' ),
                size_format( $max_size )
            ) );
        }

        return true;
    }

    /**
     * Messages d'erreur d'upload
     */
    private static function get_upload_error_message( $error_code ) {
        $errors = array(
            UPLOAD_ERR_INI_SIZE   => __( 'Le fichier dépasse la taille maximum autorisée', 'eventlist' ),
            UPLOAD_ERR_FORM_SIZE  => __( 'Le fichier dépasse la taille maximum du formulaire', 'eventlist' ),
            UPLOAD_ERR_PARTIAL    => __( 'Le fichier n\'a été que partiellement téléchargé', 'eventlist' ),
            UPLOAD_ERR_NO_FILE    => __( 'Aucun fichier n\'a été téléchargé', 'eventlist' ),
            UPLOAD_ERR_NO_TMP_DIR => __( 'Répertoire temporaire manquant', 'eventlist' ),
            UPLOAD_ERR_CANT_WRITE => __( 'Échec de l\'écriture sur le disque', 'eventlist' ),
            UPLOAD_ERR_EXTENSION  => __( 'Une extension PHP a arrêté l\'upload', 'eventlist' ),
        );

        return isset( $errors[ $error_code ] ) ? $errors[ $error_code ] : __( 'Erreur d\'upload inconnue', 'eventlist' );
    }

    /**
     * Types MIME autorisés
     */
    private static function get_allowed_mime_types() {
        return apply_filters( 'el_vendor_media_allowed_mimes', array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif'          => 'image/gif',
            'png'          => 'image/png',
            'webp'         => 'image/webp',
        ) );
    }

    /**
     * Ajouter une image dans un dossier
     */
    public static function add_image_to_folder( $attachment_id, $folder_id = 0, $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        // Vérifier si l'image existe déjà
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . self::get_table_name() . " WHERE attachment_id = %d",
            $attachment_id
        ) );

        if ( $exists ) {
            // Mettre à jour le dossier
            return $wpdb->update(
                self::get_table_name(),
                array( 'folder_id' => $folder_id ),
                array( 'attachment_id' => $attachment_id ),
                array( '%d' ),
                array( '%d' )
            );
        }

        // Insérer
        $inserted = $wpdb->insert(
            self::get_table_name(),
            array(
                'attachment_id' => $attachment_id,
                'folder_id'     => $folder_id,
                'user_id'       => $user_id,
                'order_num'     => 0,
                'created_at'    => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%d', '%d', '%s' )
        );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return new WP_Error( 'insert_failed', __( 'Impossible d\'ajouter l\'image', 'eventlist' ) );
    }

    /**
     * Déplacer une image vers un autre dossier
     */
    public static function move_image( $attachment_id, $new_folder_id ) {
        global $wpdb;

        // Vérifier les permissions
        $image = self::get_image( $attachment_id );

        if ( ! $image ) {
            return new WP_Error( 'image_not_found', __( 'Image introuvable', 'eventlist' ) );
        }

        if ( $image->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Permission refusée', 'eventlist' ) );
        }

        $updated = $wpdb->update(
            self::get_table_name(),
            array( 'folder_id' => $new_folder_id ),
            array( 'attachment_id' => $attachment_id ),
            array( '%d' ),
            array( '%d' )
        );

        if ( $updated !== false ) {
            return true;
        }

        return new WP_Error( 'move_failed', __( 'Impossible de déplacer l\'image', 'eventlist' ) );
    }

    /**
     * Déplacer plusieurs images
     */
    public static function move_images( $attachment_ids, $new_folder_id ) {
        $results = array(
            'success' => array(),
            'errors'  => array(),
        );

        foreach ( $attachment_ids as $attachment_id ) {
            $result = self::move_image( $attachment_id, $new_folder_id );

            if ( is_wp_error( $result ) ) {
                $results['errors'][] = array(
                    'attachment_id' => $attachment_id,
                    'error'         => $result->get_error_message(),
                );
            } else {
                $results['success'][] = $attachment_id;
            }
        }

        return $results;
    }

    /**
     * Supprimer une image
     */
    public static function delete_image( $attachment_id, $delete_file = true ) {
        global $wpdb;

        // Vérifier les permissions
        $image = self::get_image( $attachment_id );

        if ( ! $image ) {
            return new WP_Error( 'image_not_found', __( 'Image introuvable', 'eventlist' ) );
        }

        if ( $image->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'Permission refusée', 'eventlist' ) );
        }

        // Supprimer de notre table
        $wpdb->delete(
            self::get_table_name(),
            array( 'attachment_id' => $attachment_id ),
            array( '%d' )
        );

        // Supprimer le fichier physique si demandé
        if ( $delete_file ) {
            wp_delete_attachment( $attachment_id, true );
        }

        return true;
    }

    /**
     * Récupérer une image
     */
    public static function get_image( $attachment_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::get_table_name() . " WHERE attachment_id = %d",
            $attachment_id
        ) );
    }

    /**
     * Récupérer les images d'un dossier
     */
    public static function get_folder_images( $folder_id, $args = array() ) {
        global $wpdb;

        $defaults = array(
            'user_id'        => get_current_user_id(),
            'per_page'       => 24,
            'page'           => 1,
            'orderby'        => 'created_at',
            'order'          => 'DESC',
            'search'         => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $offset = ( $args['page'] - 1 ) * $args['per_page'];

        // Base query
        $where = $wpdb->prepare( "WHERE i.folder_id = %d AND i.user_id = %d", $folder_id, $args['user_id'] );

        // Search
        if ( ! empty( $args['search'] ) ) {
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where .= $wpdb->prepare( " AND p.post_title LIKE %s", $search );
        }

        // Order
        $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );

        // Query
        $query = "
            SELECT i.*, p.post_title, p.post_mime_type
            FROM " . self::get_table_name() . " i
            LEFT JOIN {$wpdb->posts} p ON i.attachment_id = p.ID
            {$where}
            ORDER BY {$orderby}
            LIMIT %d OFFSET %d
        ";

        $images = $wpdb->get_results( $wpdb->prepare( $query, $args['per_page'], $offset ) );

        // Enrichir avec les URLs
        foreach ( $images as &$image ) {
            $image->url = wp_get_attachment_url( $image->attachment_id );
            $image->thumb = wp_get_attachment_image_url( $image->attachment_id, 'thumbnail' );
            $image->medium = wp_get_attachment_image_url( $image->attachment_id, 'medium' );
            $image->full = wp_get_attachment_image_url( $image->attachment_id, 'full' );
        }

        // Total
        $total_query = "
            SELECT COUNT(*)
            FROM " . self::get_table_name() . " i
            LEFT JOIN {$wpdb->posts} p ON i.attachment_id = p.ID
            {$where}
        ";

        $total = $wpdb->get_var( $total_query );

        return array(
            'images' => $images,
            'total'  => $total,
            'pages'  => ceil( $total / $args['per_page'] ),
        );
    }

    /**
     * Rechercher des images
     */
    public static function search_images( $search, $user_id = null, $args = array() ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $defaults = array(
            'per_page' => 24,
            'page'     => 1,
        );

        $args = wp_parse_args( $args, $defaults );
        $offset = ( $args['page'] - 1 ) * $args['per_page'];

        $search_term = '%' . $wpdb->esc_like( $search ) . '%';

        $query = "
            SELECT i.*, p.post_title, p.post_mime_type
            FROM " . self::get_table_name() . " i
            LEFT JOIN {$wpdb->posts} p ON i.attachment_id = p.ID
            WHERE i.user_id = %d AND p.post_title LIKE %s
            ORDER BY i.created_at DESC
            LIMIT %d OFFSET %d
        ";

        $images = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $search_term, $args['per_page'], $offset ) );

        // Enrichir
        foreach ( $images as &$image ) {
            $image->url = wp_get_attachment_url( $image->attachment_id );
            $image->thumb = wp_get_attachment_image_url( $image->attachment_id, 'thumbnail' );
            $image->medium = wp_get_attachment_image_url( $image->attachment_id, 'medium' );
            $image->full = wp_get_attachment_image_url( $image->attachment_id, 'full' );
        }

        // Total
        $total = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM " . self::get_table_name() . " i
            LEFT JOIN {$wpdb->posts} p ON i.attachment_id = p.ID
            WHERE i.user_id = %d AND p.post_title LIKE %s",
            $user_id,
            $search_term
        ) );

        return array(
            'images' => $images,
            'total'  => $total,
            'pages'  => ceil( $total / $args['per_page'] ),
        );
    }
}
