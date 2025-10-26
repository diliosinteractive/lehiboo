<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Gestion des endpoints AJAX pour le gestionnaire de médias
 */
class EL_Vendor_Media_Ajax {

    /**
     * Initialiser les hooks AJAX
     */
    public static function init() {
        // Upload
        add_action( 'wp_ajax_el_vendor_upload_media', array( __CLASS__, 'upload_media' ) );

        // Folders
        add_action( 'wp_ajax_el_vendor_create_folder', array( __CLASS__, 'create_folder' ) );
        add_action( 'wp_ajax_el_vendor_update_folder', array( __CLASS__, 'update_folder' ) );
        add_action( 'wp_ajax_el_vendor_delete_folder', array( __CLASS__, 'delete_folder' ) );
        add_action( 'wp_ajax_el_vendor_get_folders', array( __CLASS__, 'get_folders' ) );
        add_action( 'wp_ajax_el_vendor_move_folder', array( __CLASS__, 'move_folder' ) );

        // Images
        add_action( 'wp_ajax_el_vendor_get_images', array( __CLASS__, 'get_images' ) );
        add_action( 'wp_ajax_el_vendor_move_image', array( __CLASS__, 'move_image' ) );
        add_action( 'wp_ajax_el_vendor_move_images', array( __CLASS__, 'move_images' ) );
        add_action( 'wp_ajax_el_vendor_delete_image', array( __CLASS__, 'delete_image' ) );
        add_action( 'wp_ajax_el_vendor_search_images', array( __CLASS__, 'search_images' ) );
    }

    /**
     * Upload de médias
     */
    public static function upload_media() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;

        if ( empty( $_FILES['files'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Aucun fichier reçu', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Media_Manager::upload_images( $_FILES['files'], $folder_id );

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'message' => sprintf(
                    _n( '%d image uploadée avec succès', '%d images uploadées avec succès', count( $result['success'] ), 'eventlist' ),
                    count( $result['success'] )
                ),
                'files'   => $result['success'],
                'errors'  => $result['errors'],
            ) );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Échec de l\'upload', 'eventlist' ),
                'errors'  => $result['errors'],
            ) );
        }
    }

    /**
     * Créer un dossier
     */
    public static function create_folder() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0;
        $description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
        $color = isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#FF6B35';

        $folder_id = EL_Vendor_Folders::create_folder( array(
            'name'        => $name,
            'parent_id'   => $parent_id,
            'description' => $description,
            'color'       => $color,
        ) );

        if ( is_wp_error( $folder_id ) ) {
            wp_send_json_error( array( 'message' => $folder_id->get_error_message() ) );
        }

        $folder = EL_Vendor_Folders::get_folder( $folder_id );

        wp_send_json_success( array(
            'message' => __( 'Dossier créé avec succès', 'eventlist' ),
            'folder'  => $folder,
        ) );
    }

    /**
     * Mettre à jour un dossier
     */
    public static function update_folder() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;

        if ( ! $folder_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de dossier manquant', 'eventlist' ) ) );
        }

        $args = array();

        if ( isset( $_POST['name'] ) ) {
            $args['name'] = sanitize_text_field( $_POST['name'] );
        }

        if ( isset( $_POST['parent_id'] ) ) {
            $args['parent_id'] = absint( $_POST['parent_id'] );
        }

        if ( isset( $_POST['description'] ) ) {
            $args['description'] = sanitize_textarea_field( $_POST['description'] );
        }

        if ( isset( $_POST['color'] ) ) {
            $args['color'] = sanitize_hex_color( $_POST['color'] );
        }

        $result = EL_Vendor_Folders::update_folder( $folder_id, $args );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $folder = EL_Vendor_Folders::get_folder( $folder_id );

        wp_send_json_success( array(
            'message' => __( 'Dossier mis à jour avec succès', 'eventlist' ),
            'folder'  => $folder,
        ) );
    }

    /**
     * Supprimer un dossier
     */
    public static function delete_folder() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;
        $move_to_parent = isset( $_POST['move_to_parent'] ) ? (bool) $_POST['move_to_parent'] : true;

        if ( ! $folder_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de dossier manquant', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Folders::delete_folder( $folder_id, $move_to_parent );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Dossier supprimé avec succès', 'eventlist' ),
        ) );
    }

    /**
     * Récupérer les dossiers
     */
    public static function get_folders() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $tree = isset( $_POST['tree'] ) ? (bool) $_POST['tree'] : false;

        if ( $tree ) {
            $folders = EL_Vendor_Folders::get_folder_tree();
        } else {
            $folders = EL_Vendor_Folders::get_user_folders();
        }

        wp_send_json_success( array(
            'folders' => $folders,
        ) );
    }

    /**
     * Déplacer un dossier
     */
    public static function move_folder() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;
        $new_parent_id = isset( $_POST['new_parent_id'] ) ? absint( $_POST['new_parent_id'] ) : 0;

        if ( ! $folder_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de dossier manquant', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Folders::move_folder( $folder_id, $new_parent_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Dossier déplacé avec succès', 'eventlist' ),
        ) );
    }

    /**
     * Récupérer les images d'un dossier
     */
    public static function get_images() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 24;
        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

        $result = EL_Vendor_Media_Manager::get_folder_images( $folder_id, array(
            'page'     => $page,
            'per_page' => $per_page,
            'search'   => $search,
        ) );

        wp_send_json_success( $result );
    }

    /**
     * Déplacer une image
     */
    public static function move_image() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;

        if ( ! $attachment_id ) {
            wp_send_json_error( array( 'message' => __( 'ID d\'image manquant', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Media_Manager::move_image( $attachment_id, $folder_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Image déplacée avec succès', 'eventlist' ),
        ) );
    }

    /**
     * Déplacer plusieurs images
     */
    public static function move_images() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $attachment_ids = isset( $_POST['attachment_ids'] ) ? array_map( 'absint', $_POST['attachment_ids'] ) : array();
        $folder_id = isset( $_POST['folder_id'] ) ? absint( $_POST['folder_id'] ) : 0;

        if ( empty( $attachment_ids ) ) {
            wp_send_json_error( array( 'message' => __( 'Aucune image sélectionnée', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Media_Manager::move_images( $attachment_ids, $folder_id );

        wp_send_json_success( array(
            'message' => sprintf(
                _n( '%d image déplacée', '%d images déplacées', count( $result['success'] ), 'eventlist' ),
                count( $result['success'] )
            ),
            'success' => $result['success'],
            'errors'  => $result['errors'],
        ) );
    }

    /**
     * Supprimer une image
     */
    public static function delete_image() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;

        if ( ! $attachment_id ) {
            wp_send_json_error( array( 'message' => __( 'ID d\'image manquant', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Media_Manager::delete_image( $attachment_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Image supprimée avec succès', 'eventlist' ),
        ) );
    }

    /**
     * Rechercher des images
     */
    public static function search_images() {
        check_ajax_referer( 'el_vendor_media_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez être connecté', 'eventlist' ) ) );
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 24;

        if ( empty( $search ) ) {
            wp_send_json_error( array( 'message' => __( 'Terme de recherche manquant', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Media_Manager::search_images( $search, null, array(
            'page'     => $page,
            'per_page' => $per_page,
        ) );

        wp_send_json_success( $result );
    }
}

// Initialiser les hooks AJAX
EL_Vendor_Media_Ajax::init();
