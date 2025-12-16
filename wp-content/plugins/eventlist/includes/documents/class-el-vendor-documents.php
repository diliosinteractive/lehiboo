<?php
/**
 * Class EL_Vendor_Documents
 *
 * CRUD pour les documents uploades par les partenaires
 * V1 Le Hiboo - Gestion des documents securises
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Vendor_Documents {

    /**
     * Nom de la table
     */
    private static $table_name = 'el_vendor_documents';

    /**
     * Statuts possibles
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Recupere le nom complet de la table
     *
     * @return string
     */
    private static function get_table() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    /**
     * Cree un nouveau document
     *
     * @param array $data Donnees du document
     * @return int|WP_Error ID du document cree ou erreur
     */
    public static function create( $data ) {
        global $wpdb;

        // Validation des donnees requises
        $required = array( 'vendor_id', 'document_type_id', 'original_filename', 'stored_filename', 'file_path', 'mime_type', 'file_size' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Le champ %s est requis', 'eventlist' ), $field ) );
            }
        }

        // Verifier si un document existe deja pour ce vendor/type
        $existing = self::get_by_vendor_and_type( $data['vendor_id'], $data['document_type_id'] );
        if ( $existing ) {
            return new WP_Error(
                'already_exists',
                __( 'Un document de ce type existe deja. Veuillez le supprimer avant d\'en ajouter un nouveau.', 'eventlist' )
            );
        }

        $insert_data = array(
            'vendor_id' => absint( $data['vendor_id'] ),
            'document_type_id' => absint( $data['document_type_id'] ),
            'original_filename' => sanitize_file_name( $data['original_filename'] ),
            'stored_filename' => sanitize_file_name( $data['stored_filename'] ),
            'file_path' => sanitize_text_field( $data['file_path'] ),
            'mime_type' => sanitize_text_field( $data['mime_type'] ),
            'file_size' => absint( $data['file_size'] ),
            'status' => self::STATUS_PENDING,
            'uploaded_at' => current_time( 'mysql' ),
        );

        $result = $wpdb->insert( self::get_table(), $insert_data );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la creation du document', 'eventlist' ) );
        }

        return $wpdb->insert_id;
    }

    /**
     * Recupere un document par son ID
     *
     * @param int $id ID du document
     * @return object|null
     */
    public static function get( $id ) {
        global $wpdb;

        $id = absint( $id );
        if ( ! $id ) {
            return null;
        }

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT d.*, t.name as type_name, t.description as type_description
             FROM " . self::get_table() . " d
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             WHERE d.id = %d",
            $id
        ) );
    }

    /**
     * Recupere un document par vendor et type
     *
     * @param int $vendor_id ID du vendor
     * @param int $type_id ID du type de document
     * @return object|null
     */
    public static function get_by_vendor_and_type( $vendor_id, $type_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT d.*, t.name as type_name
             FROM " . self::get_table() . " d
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             WHERE d.vendor_id = %d AND d.document_type_id = %d",
            absint( $vendor_id ),
            absint( $type_id )
        ) );
    }

    /**
     * Recupere tous les documents d'un vendor
     *
     * @param int $vendor_id ID du vendor
     * @return array
     */
    public static function get_vendor_documents( $vendor_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT d.*, t.name as type_name, t.description as type_description, t.is_required
             FROM " . self::get_table() . " d
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             WHERE d.vendor_id = %d
             ORDER BY t.sort_order ASC, d.uploaded_at DESC",
            absint( $vendor_id )
        ) );
    }

    /**
     * Met a jour le statut d'un document
     *
     * @param int $document_id ID du document
     * @param string $status Nouveau statut
     * @param string|null $reason Motif de rejet
     * @param int|null $reviewer_id ID du reviewer
     * @return bool|WP_Error
     */
    public static function update_status( $document_id, $status, $reason = null, $reviewer_id = null ) {
        global $wpdb;

        $document_id = absint( $document_id );
        if ( ! $document_id ) {
            return new WP_Error( 'invalid_id', __( 'ID invalide', 'eventlist' ) );
        }

        // Verifier que le statut est valide
        $valid_statuses = array( self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED );
        if ( ! in_array( $status, $valid_statuses ) ) {
            return new WP_Error( 'invalid_status', __( 'Statut invalide', 'eventlist' ) );
        }

        $update_data = array(
            'status' => $status,
            'reviewed_at' => current_time( 'mysql' ),
            'reviewed_by' => $reviewer_id ? absint( $reviewer_id ) : get_current_user_id(),
        );

        if ( $status === self::STATUS_REJECTED && $reason ) {
            $update_data['rejection_reason'] = sanitize_textarea_field( $reason );
        } else {
            $update_data['rejection_reason'] = null;
        }

        $result = $wpdb->update( self::get_table(), $update_data, array( 'id' => $document_id ) );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la mise a jour', 'eventlist' ) );
        }

        return true;
    }

    /**
     * Supprime un document
     *
     * @param int $document_id ID du document
     * @return bool|WP_Error
     */
    public static function delete( $document_id ) {
        global $wpdb;

        $document_id = absint( $document_id );
        if ( ! $document_id ) {
            return new WP_Error( 'invalid_id', __( 'ID invalide', 'eventlist' ) );
        }

        $result = $wpdb->delete( self::get_table(), array( 'id' => $document_id ) );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la suppression', 'eventlist' ) );
        }

        return true;
    }

    /**
     * Recupere les documents en attente
     *
     * @param int $limit Limite
     * @param int $offset Offset
     * @return array
     */
    public static function get_pending( $limit = 50, $offset = 0 ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT d.*, t.name as type_name, u.display_name as vendor_name, u.user_email as vendor_email
             FROM " . self::get_table() . " d
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             LEFT JOIN " . $wpdb->prefix . "users u ON d.vendor_id = u.ID
             WHERE d.status = %s
             ORDER BY d.uploaded_at ASC
             LIMIT %d OFFSET %d",
            self::STATUS_PENDING,
            absint( $limit ),
            absint( $offset )
        ) );
    }

    /**
     * Recupere les documents par statut
     *
     * @param string $status Statut
     * @param int $limit Limite
     * @param int $offset Offset
     * @return array
     */
    public static function get_by_status( $status, $limit = 50, $offset = 0 ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT d.*, t.name as type_name, u.display_name as vendor_name, u.user_email as vendor_email
             FROM " . self::get_table() . " d
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             LEFT JOIN " . $wpdb->prefix . "users u ON d.vendor_id = u.ID
             WHERE d.status = %s
             ORDER BY d.uploaded_at DESC
             LIMIT %d OFFSET %d",
            sanitize_text_field( $status ),
            absint( $limit ),
            absint( $offset )
        ) );
    }

    /**
     * Compte les documents par statut
     *
     * @param string $status Statut
     * @return int
     */
    public static function count_by_status( $status ) {
        global $wpdb;

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::get_table() . " WHERE status = %s",
            sanitize_text_field( $status )
        ) );
    }

    /**
     * Compte les documents d'un vendor par statut
     *
     * @param int $vendor_id ID du vendor
     * @param string|null $status Statut (null pour tous)
     * @return int
     */
    public static function count_vendor_documents( $vendor_id, $status = null ) {
        global $wpdb;

        if ( $status ) {
            return (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::get_table() . " WHERE vendor_id = %d AND status = %s",
                absint( $vendor_id ),
                sanitize_text_field( $status )
            ) );
        }

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::get_table() . " WHERE vendor_id = %d",
            absint( $vendor_id )
        ) );
    }

    /**
     * Verifie si un vendor a complete tous les documents requis et approuves
     *
     * @param int $vendor_id ID du vendor
     * @return bool
     */
    public static function vendor_has_all_required_approved( $vendor_id ) {
        $required_types = EL_Document_Types::get_required();

        if ( empty( $required_types ) ) {
            return true; // Aucun document requis
        }

        foreach ( $required_types as $type ) {
            $doc = self::get_by_vendor_and_type( $vendor_id, $type->id );
            if ( ! $doc || $doc->status !== self::STATUS_APPROVED ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recupere les types de documents manquants pour un vendor
     *
     * @param int $vendor_id ID du vendor
     * @return array Types de documents manquants ou non approuves
     */
    public static function get_missing_required_documents( $vendor_id ) {
        $required_types = EL_Document_Types::get_required();
        $missing = array();

        foreach ( $required_types as $type ) {
            $doc = self::get_by_vendor_and_type( $vendor_id, $type->id );
            if ( ! $doc ) {
                $type->doc_status = 'missing';
                $missing[] = $type;
            } elseif ( $doc->status === self::STATUS_PENDING ) {
                $type->doc_status = 'pending';
                $missing[] = $type;
            } elseif ( $doc->status === self::STATUS_REJECTED ) {
                $type->doc_status = 'rejected';
                $type->rejection_reason = $doc->rejection_reason;
                $missing[] = $type;
            }
        }

        return $missing;
    }

    /**
     * Recherche de documents avec filtres
     *
     * @param array $args Arguments de recherche
     * @return array
     */
    public static function search( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => '',
            'vendor_id' => 0,
            'document_type_id' => 0,
            'search' => '',
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'uploaded_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'd.status = %s';
            $values[] = sanitize_text_field( $args['status'] );
        }

        if ( ! empty( $args['vendor_id'] ) ) {
            $where[] = 'd.vendor_id = %d';
            $values[] = absint( $args['vendor_id'] );
        }

        if ( ! empty( $args['document_type_id'] ) ) {
            $where[] = 'd.document_type_id = %d';
            $values[] = absint( $args['document_type_id'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $where[] = '(d.original_filename LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s)';
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode( ' AND ', $where );

        // Securiser orderby
        $allowed_orderby = array( 'uploaded_at', 'reviewed_at', 'status', 'vendor_id' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'uploaded_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT d.*, t.name as type_name, u.display_name as vendor_name, u.user_email as vendor_email
                FROM " . self::get_table() . " d
                LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
                LEFT JOIN " . $wpdb->prefix . "users u ON d.vendor_id = u.ID
                WHERE $where_sql
                ORDER BY d.$orderby $order
                LIMIT %d OFFSET %d";

        $values[] = absint( $args['limit'] );
        $values[] = absint( $args['offset'] );

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Compte les resultats de recherche
     *
     * @param array $args Arguments de recherche
     * @return int
     */
    public static function search_count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => '',
            'vendor_id' => 0,
            'document_type_id' => 0,
            'search' => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'd.status = %s';
            $values[] = sanitize_text_field( $args['status'] );
        }

        if ( ! empty( $args['vendor_id'] ) ) {
            $where[] = 'd.vendor_id = %d';
            $values[] = absint( $args['vendor_id'] );
        }

        if ( ! empty( $args['document_type_id'] ) ) {
            $where[] = 'd.document_type_id = %d';
            $values[] = absint( $args['document_type_id'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $where[] = '(d.original_filename LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s)';
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode( ' AND ', $where );

        $sql = "SELECT COUNT(*)
                FROM " . self::get_table() . " d
                LEFT JOIN " . $wpdb->prefix . "users u ON d.vendor_id = u.ID
                WHERE $where_sql";

        if ( ! empty( $values ) ) {
            return (int) $wpdb->get_var( $wpdb->prepare( $sql, $values ) );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Remplace un document existant (pour upload d'un nouveau fichier)
     *
     * @param int $document_id ID du document a remplacer
     * @param array $data Nouvelles donnees
     * @return bool|WP_Error
     */
    public static function replace( $document_id, $data ) {
        global $wpdb;

        $document_id = absint( $document_id );
        $existing = self::get( $document_id );

        if ( ! $existing ) {
            return new WP_Error( 'not_found', __( 'Document non trouve', 'eventlist' ) );
        }

        $update_data = array(
            'original_filename' => sanitize_file_name( $data['original_filename'] ),
            'stored_filename' => sanitize_file_name( $data['stored_filename'] ),
            'file_path' => sanitize_text_field( $data['file_path'] ),
            'mime_type' => sanitize_text_field( $data['mime_type'] ),
            'file_size' => absint( $data['file_size'] ),
            'status' => self::STATUS_PENDING,
            'rejection_reason' => null,
            'uploaded_at' => current_time( 'mysql' ),
            'reviewed_at' => null,
            'reviewed_by' => null,
        );

        $result = $wpdb->update( self::get_table(), $update_data, array( 'id' => $document_id ) );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors du remplacement', 'eventlist' ) );
        }

        return true;
    }
}
