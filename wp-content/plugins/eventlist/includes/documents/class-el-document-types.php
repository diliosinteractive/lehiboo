<?php
/**
 * Class EL_Document_Types
 *
 * CRUD pour les types de documents definis par l'administrateur
 * V1 Le Hiboo - Gestion des documents securises
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_Types {

    /**
     * Nom de la table
     */
    private static $table_name = 'el_document_types';

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
     * Cree un nouveau type de document
     *
     * @param array $data Donnees du type
     * @return int|WP_Error ID du type cree ou erreur
     */
    public static function create( $data ) {
        global $wpdb;

        // Validation des donnees requises
        if ( empty( $data['name'] ) ) {
            return new WP_Error( 'missing_name', __( 'Le nom du type de document est requis', 'eventlist' ) );
        }

        $now = current_time( 'mysql' );

        $insert_data = array(
            'name' => sanitize_text_field( $data['name'] ),
            'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
            'allowed_extensions' => isset( $data['allowed_extensions'] ) ? sanitize_text_field( $data['allowed_extensions'] ) : 'pdf,jpg,jpeg,png',
            'max_file_size' => isset( $data['max_file_size'] ) ? absint( $data['max_file_size'] ) : 5242880,
            'is_required' => isset( $data['is_required'] ) ? absint( $data['is_required'] ) : 0,
            'required_at_registration' => isset( $data['required_at_registration'] ) ? absint( $data['required_at_registration'] ) : 0,
            'sort_order' => isset( $data['sort_order'] ) ? absint( $data['sort_order'] ) : 0,
            'is_active' => isset( $data['is_active'] ) ? absint( $data['is_active'] ) : 1,
            'created_at' => $now,
            'updated_at' => $now,
        );

        $result = $wpdb->insert( self::get_table(), $insert_data );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la creation du type de document', 'eventlist' ) );
        }

        return $wpdb->insert_id;
    }

    /**
     * Met a jour un type de document
     *
     * @param int $id ID du type
     * @param array $data Donnees a mettre a jour
     * @return bool|WP_Error True en cas de succes ou erreur
     */
    public static function update( $id, $data ) {
        global $wpdb;

        $id = absint( $id );
        if ( ! $id ) {
            return new WP_Error( 'invalid_id', __( 'ID invalide', 'eventlist' ) );
        }

        // Verifier que le type existe
        if ( ! self::get( $id ) ) {
            return new WP_Error( 'not_found', __( 'Type de document non trouve', 'eventlist' ) );
        }

        $update_data = array(
            'updated_at' => current_time( 'mysql' ),
        );

        // Champs modifiables
        $allowed_fields = array( 'name', 'description', 'allowed_extensions', 'max_file_size', 'is_required', 'required_at_registration', 'sort_order', 'is_active' );

        foreach ( $allowed_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                if ( in_array( $field, array( 'name', 'allowed_extensions' ) ) ) {
                    $update_data[ $field ] = sanitize_text_field( $data[ $field ] );
                } elseif ( $field === 'description' ) {
                    $update_data[ $field ] = sanitize_textarea_field( $data[ $field ] );
                } else {
                    $update_data[ $field ] = absint( $data[ $field ] );
                }
            }
        }

        $result = $wpdb->update( self::get_table(), $update_data, array( 'id' => $id ) );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la mise a jour', 'eventlist' ) );
        }

        return true;
    }

    /**
     * Supprime un type de document
     *
     * @param int $id ID du type
     * @return bool|WP_Error True en cas de succes ou erreur
     */
    public static function delete( $id ) {
        global $wpdb;

        $id = absint( $id );
        if ( ! $id ) {
            return new WP_Error( 'invalid_id', __( 'ID invalide', 'eventlist' ) );
        }

        // Verifier s'il y a des documents associes
        $table_documents = $wpdb->prefix . 'el_vendor_documents';
        $doc_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_documents WHERE document_type_id = %d",
            $id
        ) );

        if ( $doc_count > 0 ) {
            return new WP_Error(
                'has_documents',
                sprintf( __( 'Impossible de supprimer : %d document(s) associe(s) a ce type', 'eventlist' ), $doc_count )
            );
        }

        $result = $wpdb->delete( self::get_table(), array( 'id' => $id ) );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Erreur lors de la suppression', 'eventlist' ) );
        }

        return true;
    }

    /**
     * Recupere un type de document par son ID
     *
     * @param int $id ID du type
     * @return object|null Objet type ou null
     */
    public static function get( $id ) {
        global $wpdb;

        $id = absint( $id );
        if ( ! $id ) {
            return null;
        }

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::get_table() . " WHERE id = %d",
            $id
        ) );
    }

    /**
     * Recupere tous les types de documents
     *
     * @param bool $active_only Seulement les types actifs
     * @return array Liste des types
     */
    public static function get_all( $active_only = true ) {
        global $wpdb;

        $where = $active_only ? "WHERE is_active = 1" : "";

        return $wpdb->get_results(
            "SELECT * FROM " . self::get_table() . " $where ORDER BY sort_order ASC, id ASC"
        );
    }

    /**
     * Recupere les types de documents requis
     *
     * @return array Liste des types requis
     */
    public static function get_required() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM " . self::get_table() . " WHERE is_active = 1 AND is_required = 1 ORDER BY sort_order ASC"
        );
    }

    /**
     * Recupere les types requis a l'inscription
     *
     * @return array Liste des types requis a l'inscription
     */
    public static function get_required_at_registration() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM " . self::get_table() . " WHERE is_active = 1 AND required_at_registration = 1 ORDER BY sort_order ASC"
        );
    }

    /**
     * Recupere les extensions autorisees pour un type
     *
     * @param int $type_id ID du type
     * @return array Liste des extensions
     */
    public static function get_allowed_extensions( $type_id ) {
        $type = self::get( $type_id );

        if ( ! $type || empty( $type->allowed_extensions ) ) {
            return array( 'pdf', 'jpg', 'jpeg', 'png' ); // Defaut
        }

        $extensions = explode( ',', $type->allowed_extensions );
        return array_map( 'trim', array_map( 'strtolower', $extensions ) );
    }

    /**
     * Recupere la taille max pour un type
     *
     * @param int $type_id ID du type
     * @return int Taille max en octets
     */
    public static function get_max_file_size( $type_id ) {
        $type = self::get( $type_id );

        if ( ! $type || empty( $type->max_file_size ) ) {
            return 5242880; // 5MB par defaut
        }

        return absint( $type->max_file_size );
    }

    /**
     * Compte le nombre de types
     *
     * @param bool $active_only Seulement les types actifs
     * @return int Nombre de types
     */
    public static function count( $active_only = true ) {
        global $wpdb;

        $where = $active_only ? "WHERE is_active = 1" : "";

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM " . self::get_table() . " $where"
        );
    }

    /**
     * Verifie si un type est requis
     *
     * @param int $type_id ID du type
     * @return bool
     */
    public static function is_required( $type_id ) {
        $type = self::get( $type_id );
        return $type && $type->is_required == 1;
    }

    /**
     * Reordonne les types de documents
     *
     * @param array $order Tableau [type_id => new_order]
     * @return bool True en cas de succes
     */
    public static function reorder( $order ) {
        global $wpdb;

        if ( ! is_array( $order ) || empty( $order ) ) {
            return false;
        }

        foreach ( $order as $type_id => $sort_order ) {
            $wpdb->update(
                self::get_table(),
                array( 'sort_order' => absint( $sort_order ) ),
                array( 'id' => absint( $type_id ) )
            );
        }

        return true;
    }
}
