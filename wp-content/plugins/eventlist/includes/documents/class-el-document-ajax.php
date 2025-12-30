<?php
/**
 * Class EL_Document_Ajax
 *
 * Handlers AJAX pour le systeme de documents
 * V1 Le Hiboo - Gestion des documents securises
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_Ajax {

    /**
     * Initialisation des hooks AJAX
     */
    public static function init() {
        // Frontend - Partenaires (authentifies)
        add_action( 'wp_ajax_el_upload_document', array( __CLASS__, 'upload_document' ) );
        add_action( 'wp_ajax_el_get_vendor_documents', array( __CLASS__, 'get_vendor_documents' ) );
        add_action( 'wp_ajax_el_download_document', array( __CLASS__, 'download_document' ) );
        add_action( 'wp_ajax_el_delete_document', array( __CLASS__, 'delete_document' ) );
        add_action( 'wp_ajax_el_replace_document', array( __CLASS__, 'replace_document' ) );

        // Admin - Gestion des documents
        add_action( 'wp_ajax_el_admin_get_documents', array( __CLASS__, 'admin_get_documents' ) );
        add_action( 'wp_ajax_el_admin_approve_document', array( __CLASS__, 'admin_approve_document' ) );
        add_action( 'wp_ajax_el_admin_reject_document', array( __CLASS__, 'admin_reject_document' ) );
        add_action( 'wp_ajax_el_admin_download_document', array( __CLASS__, 'admin_download_document' ) );
        add_action( 'wp_ajax_el_admin_preview_document', array( __CLASS__, 'admin_preview_document' ) );

        // Admin - Types de documents
        add_action( 'wp_ajax_el_admin_create_doc_type', array( __CLASS__, 'admin_create_doc_type' ) );
        add_action( 'wp_ajax_el_admin_update_doc_type', array( __CLASS__, 'admin_update_doc_type' ) );
        add_action( 'wp_ajax_el_admin_delete_doc_type', array( __CLASS__, 'admin_delete_doc_type' ) );
        add_action( 'wp_ajax_el_admin_get_doc_types', array( __CLASS__, 'admin_get_doc_types' ) );
        add_action( 'wp_ajax_el_admin_reorder_doc_types', array( __CLASS__, 'admin_reorder_doc_types' ) );

        // Admin - Profil partenaire
        add_action( 'wp_ajax_el_admin_get_vendor_profile', array( __CLASS__, 'admin_get_vendor_profile' ) );
    }

    // =========================================================================
    // FRONTEND - PARTENAIRES
    // =========================================================================

    /**
     * Upload d'un document
     */
    public static function upload_document() {
        // Verifier le nonce
        check_ajax_referer( 'el_document_nonce', 'nonce' );

        // Verifier l'authentification
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez etre connecte', 'eventlist' ) ) );
        }

        // Verifier que c'est un vendor
        if ( ! function_exists( 'el_is_vendor' ) || ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez etre un partenaire', 'eventlist' ) ) );
        }

        $vendor_id = get_current_user_id();
        $document_type_id = isset( $_POST['document_type_id'] ) ? absint( $_POST['document_type_id'] ) : 0;

        // Verifier le type de document
        if ( ! $document_type_id ) {
            wp_send_json_error( array( 'message' => __( 'Type de document non specifie', 'eventlist' ) ) );
        }

        $document_type = EL_Document_Types::get( $document_type_id );
        if ( ! $document_type || ! $document_type->is_active ) {
            wp_send_json_error( array( 'message' => __( 'Type de document invalide', 'eventlist' ) ) );
        }

        // Verifier qu'un fichier a ete uploade
        if ( empty( $_FILES['document'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Aucun fichier recu', 'eventlist' ) ) );
        }

        // Stocker le fichier
        $file_data = EL_Document_File_Handler::store_file(
            $_FILES['document'],
            $vendor_id,
            $document_type_id
        );

        if ( is_wp_error( $file_data ) ) {
            wp_send_json_error( array( 'message' => $file_data->get_error_message() ) );
        }

        // Creer l'enregistrement en BDD
        $doc_data = array_merge( $file_data, array(
            'vendor_id' => $vendor_id,
            'document_type_id' => $document_type_id,
        ) );

        $document_id = EL_Vendor_Documents::create( $doc_data );

        if ( is_wp_error( $document_id ) ) {
            // Supprimer le fichier uploade
            EL_Document_File_Handler::delete_file( $file_data['file_path'] );
            wp_send_json_error( array( 'message' => $document_id->get_error_message() ) );
        }

        // Logger l'action
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_UPLOADED );

        // Envoyer notification admin
        EL_Document_Notifications::notify_document_uploaded( $document_id );

        wp_send_json_success( array(
            'message' => __( 'Document uploade avec succes. Il sera examine par notre equipe.', 'eventlist' ),
            'document_id' => $document_id,
            'filename' => $file_data['original_filename'],
        ) );
    }

    /**
     * Recupere les documents du vendor connecte
     */
    public static function get_vendor_documents() {
        check_ajax_referer( 'el_document_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez etre connecte', 'eventlist' ) ) );
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

        // Construire la reponse
        $documents = array();
        foreach ( $document_types as $type ) {
            $doc = isset( $docs_by_type[ $type->id ] ) ? $docs_by_type[ $type->id ] : null;

            $documents[] = array(
                'type_id' => $type->id,
                'type_name' => $type->name,
                'type_description' => $type->description,
                'is_required' => (bool) $type->is_required,
                'allowed_extensions' => $type->allowed_extensions,
                'max_file_size' => $type->max_file_size,
                'max_file_size_formatted' => size_format( $type->max_file_size ),
                'has_document' => ! is_null( $doc ),
                'document' => $doc ? array(
                    'id' => $doc->id,
                    'filename' => $doc->original_filename,
                    'status' => $doc->status,
                    'status_label' => self::get_status_label( $doc->status ),
                    'rejection_reason' => $doc->rejection_reason,
                    'uploaded_at' => $doc->uploaded_at,
                    'uploaded_at_formatted' => date_i18n( 'j M Y H:i', strtotime( $doc->uploaded_at ) ),
                ) : null,
            );
        }

        // Statistiques
        $stats = array(
            'total_types' => count( $document_types ),
            'submitted' => count( $vendor_documents ),
            'approved' => EL_Vendor_Documents::count_vendor_documents( $vendor_id, 'approved' ),
            'pending' => EL_Vendor_Documents::count_vendor_documents( $vendor_id, 'pending' ),
            'rejected' => EL_Vendor_Documents::count_vendor_documents( $vendor_id, 'rejected' ),
            'all_required_approved' => EL_Vendor_Documents::vendor_has_all_required_approved( $vendor_id ),
        );

        wp_send_json_success( array(
            'documents' => $documents,
            'stats' => $stats,
        ) );
    }

    /**
     * Telecharge un document (vendor)
     */
    public static function download_document() {
        // Pas de JSON pour le telechargement
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'el_document_nonce' ) ) {
            wp_die( __( 'Nonce invalide', 'eventlist' ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_die( __( 'Vous devez etre connecte', 'eventlist' ) );
        }

        $document_id = isset( $_GET['document_id'] ) ? absint( $_GET['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_die( __( 'Document non specifie', 'eventlist' ) );
        }

        EL_Document_File_Handler::serve_file( $document_id, get_current_user_id() );
    }

    /**
     * Supprime un document (vendor - seulement pending)
     */
    public static function delete_document() {
        check_ajax_referer( 'el_document_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez etre connecte', 'eventlist' ) ) );
        }

        $vendor_id = get_current_user_id();
        $document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_send_json_error( array( 'message' => __( 'Document non specifie', 'eventlist' ) ) );
        }

        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            wp_send_json_error( array( 'message' => __( 'Document introuvable', 'eventlist' ) ) );
        }

        // Verifier la propriete
        if ( $document->vendor_id != $vendor_id ) {
            wp_send_json_error( array( 'message' => __( 'Acces refuse', 'eventlist' ) ) );
        }

        // Seulement les documents pending peuvent etre supprimes
        if ( $document->status !== EL_Vendor_Documents::STATUS_PENDING ) {
            wp_send_json_error( array( 'message' => __( 'Seuls les documents en attente peuvent etre supprimes', 'eventlist' ) ) );
        }

        // Supprimer le fichier
        EL_Document_File_Handler::delete_file( $document->file_path );

        // Logger avant suppression
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_DELETED );

        // Supprimer l'enregistrement
        $result = EL_Vendor_Documents::delete( $document_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Document supprime', 'eventlist' ) ) );
    }

    /**
     * Remplace un document existant
     */
    public static function replace_document() {
        check_ajax_referer( 'el_document_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez etre connecte', 'eventlist' ) ) );
        }

        $vendor_id = get_current_user_id();
        $document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_send_json_error( array( 'message' => __( 'Document non specifie', 'eventlist' ) ) );
        }

        $existing = EL_Vendor_Documents::get( $document_id );

        if ( ! $existing ) {
            wp_send_json_error( array( 'message' => __( 'Document introuvable', 'eventlist' ) ) );
        }

        if ( $existing->vendor_id != $vendor_id ) {
            wp_send_json_error( array( 'message' => __( 'Acces refuse', 'eventlist' ) ) );
        }

        if ( empty( $_FILES['document'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Aucun fichier recu', 'eventlist' ) ) );
        }

        // Stocker le nouveau fichier
        $file_data = EL_Document_File_Handler::store_file(
            $_FILES['document'],
            $vendor_id,
            $existing->document_type_id
        );

        if ( is_wp_error( $file_data ) ) {
            wp_send_json_error( array( 'message' => $file_data->get_error_message() ) );
        }

        // Supprimer l'ancien fichier
        EL_Document_File_Handler::delete_file( $existing->file_path );

        // Mettre a jour l'enregistrement
        $result = EL_Vendor_Documents::replace( $document_id, $file_data );

        if ( is_wp_error( $result ) ) {
            EL_Document_File_Handler::delete_file( $file_data['file_path'] );
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Logger l'action
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_REPLACED );

        // Notifier l'admin
        EL_Document_Notifications::notify_document_uploaded( $document_id );

        wp_send_json_success( array(
            'message' => __( 'Document remplace avec succes. Il sera examine par notre equipe.', 'eventlist' ),
            'filename' => $file_data['original_filename'],
        ) );
    }

    // =========================================================================
    // ADMIN - GESTION DES DOCUMENTS
    // =========================================================================

    /**
     * Recupere les documents (admin)
     */
    public static function admin_get_documents() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $args = array(
            'status' => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '',
            'vendor_id' => isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0,
            'document_type_id' => isset( $_POST['document_type_id'] ) ? absint( $_POST['document_type_id'] ) : 0,
            'search' => isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '',
            'limit' => isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 50,
            'offset' => isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0,
        );

        $documents = EL_Vendor_Documents::search( $args );
        $total = EL_Vendor_Documents::search_count( $args );

        $formatted = array();
        foreach ( $documents as $doc ) {
            $formatted[] = array(
                'id' => $doc->id,
                'vendor_id' => $doc->vendor_id,
                'vendor_name' => $doc->vendor_name,
                'vendor_email' => $doc->vendor_email,
                'type_name' => $doc->type_name,
                'filename' => $doc->original_filename,
                'file_size' => size_format( $doc->file_size ),
                'status' => $doc->status,
                'status_label' => self::get_status_label( $doc->status ),
                'rejection_reason' => $doc->rejection_reason,
                'uploaded_at' => date_i18n( 'j M Y H:i', strtotime( $doc->uploaded_at ) ),
                'reviewed_at' => $doc->reviewed_at ? date_i18n( 'j M Y H:i', strtotime( $doc->reviewed_at ) ) : null,
            );
        }

        wp_send_json_success( array(
            'documents' => $formatted,
            'total' => $total,
            'stats' => array(
                'pending' => EL_Vendor_Documents::count_by_status( 'pending' ),
                'approved' => EL_Vendor_Documents::count_by_status( 'approved' ),
                'rejected' => EL_Vendor_Documents::count_by_status( 'rejected' ),
            ),
        ) );
    }

    /**
     * Approuve un document (admin)
     */
    public static function admin_approve_document() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_send_json_error( array( 'message' => __( 'Document non specifie', 'eventlist' ) ) );
        }

        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            wp_send_json_error( array( 'message' => __( 'Document introuvable', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Documents::update_status(
            $document_id,
            EL_Vendor_Documents::STATUS_APPROVED
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Logger
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_APPROVED );

        // Notifier le vendor
        EL_Document_Notifications::notify_document_approved( $document_id );

        wp_send_json_success( array( 'message' => __( 'Document approuve', 'eventlist' ) ) );
    }

    /**
     * Rejette un document (admin)
     */
    public static function admin_reject_document() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;
        $reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

        if ( ! $document_id ) {
            wp_send_json_error( array( 'message' => __( 'Document non specifie', 'eventlist' ) ) );
        }

        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            wp_send_json_error( array( 'message' => __( 'Document introuvable', 'eventlist' ) ) );
        }

        $result = EL_Vendor_Documents::update_status(
            $document_id,
            EL_Vendor_Documents::STATUS_REJECTED,
            $reason
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Logger
        EL_Document_Audit::log( $document_id, EL_Document_Audit::ACTION_REJECTED, $reason );

        // Notifier le vendor
        EL_Document_Notifications::notify_document_rejected( $document_id, $reason );

        wp_send_json_success( array( 'message' => __( 'Document rejete', 'eventlist' ) ) );
    }

    /**
     * Telecharge un document (admin)
     */
    public static function admin_download_document() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'el_admin_document_nonce' ) ) {
            wp_die( __( 'Nonce invalide', 'eventlist' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Permission refusee', 'eventlist' ) );
        }

        $document_id = isset( $_GET['document_id'] ) ? absint( $_GET['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_die( __( 'Document non specifie', 'eventlist' ) );
        }

        EL_Document_File_Handler::serve_file( $document_id, get_current_user_id() );
    }

    /**
     * Previsualisation d'un document (admin) - inline sans telechargement
     */
    public static function admin_preview_document() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'el_admin_document_nonce' ) ) {
            wp_die( __( 'Nonce invalide', 'eventlist' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Permission refusee', 'eventlist' ) );
        }

        $document_id = isset( $_GET['document_id'] ) ? absint( $_GET['document_id'] ) : 0;

        if ( ! $document_id ) {
            wp_die( __( 'Document non specifie', 'eventlist' ) );
        }

        // Servir le fichier en mode inline (preview)
        EL_Document_File_Handler::serve_file( $document_id, get_current_user_id(), false );
    }

    // =========================================================================
    // ADMIN - TYPES DE DOCUMENTS
    // =========================================================================

    /**
     * Cree un type de document (admin)
     */
    public static function admin_create_doc_type() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $data = array(
            'name' => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
            'allowed_extensions' => isset( $_POST['allowed_extensions'] ) ? sanitize_text_field( $_POST['allowed_extensions'] ) : 'pdf,jpg,jpeg,png',
            'max_file_size' => isset( $_POST['max_file_size'] ) ? absint( $_POST['max_file_size'] ) : 5242880,
            'is_required' => isset( $_POST['is_required'] ) ? absint( $_POST['is_required'] ) : 0,
            'required_at_registration' => isset( $_POST['required_at_registration'] ) ? absint( $_POST['required_at_registration'] ) : 0,
            'sort_order' => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
            'is_active' => isset( $_POST['is_active'] ) ? absint( $_POST['is_active'] ) : 1,
        );

        $result = EL_Document_Types::create( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Type de document cree', 'eventlist' ),
            'type_id' => $result,
        ) );
    }

    /**
     * Met a jour un type de document (admin)
     */
    public static function admin_update_doc_type() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $type_id = isset( $_POST['type_id'] ) ? absint( $_POST['type_id'] ) : 0;

        if ( ! $type_id ) {
            wp_send_json_error( array( 'message' => __( 'Type non specifie', 'eventlist' ) ) );
        }

        $data = array(
            'name' => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
            'allowed_extensions' => isset( $_POST['allowed_extensions'] ) ? sanitize_text_field( $_POST['allowed_extensions'] ) : '',
            'max_file_size' => isset( $_POST['max_file_size'] ) ? absint( $_POST['max_file_size'] ) : 0,
            'is_required' => isset( $_POST['is_required'] ) ? absint( $_POST['is_required'] ) : 0,
            'required_at_registration' => isset( $_POST['required_at_registration'] ) ? absint( $_POST['required_at_registration'] ) : 0,
            'sort_order' => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
            'is_active' => isset( $_POST['is_active'] ) ? absint( $_POST['is_active'] ) : 1,
        );

        $result = EL_Document_Types::update( $type_id, $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Type de document mis a jour', 'eventlist' ) ) );
    }

    /**
     * Supprime un type de document (admin)
     */
    public static function admin_delete_doc_type() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $type_id = isset( $_POST['type_id'] ) ? absint( $_POST['type_id'] ) : 0;

        if ( ! $type_id ) {
            wp_send_json_error( array( 'message' => __( 'Type non specifie', 'eventlist' ) ) );
        }

        $result = EL_Document_Types::delete( $type_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Type de document supprime', 'eventlist' ) ) );
    }

    /**
     * Recupere les types de documents (admin)
     */
    public static function admin_get_doc_types() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $active_only = isset( $_POST['active_only'] ) ? (bool) $_POST['active_only'] : false;
        $types = EL_Document_Types::get_all( $active_only );

        $formatted = array();
        foreach ( $types as $type ) {
            $formatted[] = array(
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'allowed_extensions' => $type->allowed_extensions,
                'max_file_size' => $type->max_file_size,
                'max_file_size_formatted' => size_format( $type->max_file_size ),
                'is_required' => (bool) $type->is_required,
                'required_at_registration' => (bool) $type->required_at_registration,
                'sort_order' => $type->sort_order,
                'is_active' => (bool) $type->is_active,
            );
        }

        wp_send_json_success( array( 'types' => $formatted ) );
    }

    /**
     * Reordonne les types de documents (admin)
     */
    public static function admin_reorder_doc_types() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $order = isset( $_POST['order'] ) ? $_POST['order'] : array();

        if ( ! is_array( $order ) || empty( $order ) ) {
            wp_send_json_error( array( 'message' => __( 'Ordre non specifie', 'eventlist' ) ) );
        }

        // Sanitize
        $clean_order = array();
        foreach ( $order as $type_id => $position ) {
            $clean_order[ absint( $type_id ) ] = absint( $position );
        }

        $result = EL_Document_Types::reorder( $clean_order );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors du reordonnancement', 'eventlist' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Ordre mis a jour', 'eventlist' ) ) );
    }

    // =========================================================================
    // ADMIN - PROFIL PARTENAIRE
    // =========================================================================

    /**
     * Recupere le profil d'un partenaire (admin)
     */
    public static function admin_get_vendor_profile() {
        check_ajax_referer( 'el_admin_document_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission refusee', 'eventlist' ) ) );
        }

        $vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;

        if ( ! $vendor_id ) {
            wp_send_json_error( array( 'message' => __( 'Partenaire non specifie', 'eventlist' ) ) );
        }

        $user = get_userdata( $vendor_id );

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => __( 'Partenaire introuvable', 'eventlist' ) ) );
        }

        // Recuperer les meta de l'utilisateur
        $first_name = get_user_meta( $vendor_id, 'first_name', true );
        $last_name = get_user_meta( $vendor_id, 'last_name', true );
        $user_phone = get_user_meta( $vendor_id, 'user_phone', true );

        // Organisation
        $org_display_name = get_user_meta( $vendor_id, 'org_display_name', true );
        $org_name = get_user_meta( $vendor_id, 'org_name', true );
        $org_type = get_user_meta( $vendor_id, 'org_type', true );
        $org_roles = get_user_meta( $vendor_id, 'org_roles', true );
        $org_siren = get_user_meta( $vendor_id, 'org_siren', true );
        $org_legal_status = get_user_meta( $vendor_id, 'org_legal_status', true );
        $org_phone = get_user_meta( $vendor_id, 'org_phone_contact', true );
        $org_web = get_user_meta( $vendor_id, 'org_web', true );

        // Adresse
        $address_line1 = get_user_meta( $vendor_id, 'user_address_line1', true );
        $city = get_user_meta( $vendor_id, 'user_city', true );
        $postcode = get_user_meta( $vendor_id, 'user_postcode', true );
        $country = get_user_meta( $vendor_id, 'user_country', true );

        // Formater le type d'organisation
        $org_type_labels = array(
            'association' => __( 'Association', 'eventlist' ),
            'entreprise' => __( 'Entreprise', 'eventlist' ),
            'autoentrepreneur' => __( 'Auto-entrepreneur', 'eventlist' ),
            'collectivite' => __( 'Collectivite', 'eventlist' ),
            'autre' => __( 'Autre', 'eventlist' ),
        );

        // Date inscription
        $registered = $user->user_registered;

        // Statut vendor
        $vendor_status = get_user_meta( $vendor_id, 'vendor_status', true );

        // Logo organisation
        $org_logo_id = get_user_meta( $vendor_id, 'org_logo_id', true );
        $org_logo_url = '';
        if ( $org_logo_id ) {
            $org_logo_url = wp_get_attachment_image_url( $org_logo_id, 'thumbnail' );
        }

        // Lien vers la page edit user WP
        $edit_user_url = get_edit_user_link( $vendor_id );

        wp_send_json_success( array(
            'profile' => array(
                'id' => $vendor_id,
                'contact' => array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'full_name' => trim( $first_name . ' ' . $last_name ),
                    'email' => $user->user_email,
                    'phone' => $user_phone,
                ),
                'organisation' => array(
                    'display_name' => $org_display_name ?: $org_name,
                    'name' => $org_name,
                    'type' => $org_type,
                    'type_label' => isset( $org_type_labels[ $org_type ] ) ? $org_type_labels[ $org_type ] : $org_type,
                    'roles' => $org_roles,
                    'siren' => $org_siren,
                    'legal_status' => $org_legal_status,
                    'phone' => $org_phone,
                    'website' => $org_web,
                    'logo_url' => $org_logo_url,
                ),
                'address' => array(
                    'line1' => $address_line1,
                    'city' => $city,
                    'postcode' => $postcode,
                    'country' => $country,
                    'formatted' => self::format_address( $address_line1, $postcode, $city, $country ),
                ),
                'meta' => array(
                    'registered' => date_i18n( 'j M Y', strtotime( $registered ) ),
                    'vendor_status' => $vendor_status,
                    'edit_url' => $edit_user_url,
                ),
            ),
        ) );
    }

    /**
     * Formate une adresse
     */
    private static function format_address( $line1, $postcode, $city, $country ) {
        $parts = array();

        if ( $line1 ) {
            $parts[] = $line1;
        }

        $city_parts = array();
        if ( $postcode ) {
            $city_parts[] = $postcode;
        }
        if ( $city ) {
            $city_parts[] = $city;
        }
        if ( ! empty( $city_parts ) ) {
            $parts[] = implode( ' ', $city_parts );
        }

        if ( $country ) {
            $parts[] = $country;
        }

        return implode( ', ', $parts );
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Traduit un statut en label
     *
     * @param string $status Statut
     * @return string Label
     */
    private static function get_status_label( $status ) {
        $labels = array(
            'pending' => __( 'En attente de validation', 'eventlist' ),
            'approved' => __( 'Approuve', 'eventlist' ),
            'rejected' => __( 'Rejete', 'eventlist' ),
        );

        return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
    }
}

// Initialiser
EL_Document_Ajax::init();
