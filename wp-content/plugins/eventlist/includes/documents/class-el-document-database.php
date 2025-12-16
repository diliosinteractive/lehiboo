<?php
/**
 * Class EL_Document_Database
 *
 * Gestion de la base de donnees pour le systeme de documents partenaires
 * V1 Le Hiboo - Gestion des documents securises
 *
 * Tables creees :
 * - wp_el_document_types : Types de documents definis par l'admin
 * - wp_el_vendor_documents : Documents uploades par les partenaires
 * - wp_el_document_audit_log : Journal d'audit des actions
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_Database {

    /**
     * Version de la base de donnees
     * Incrementer lors de changements de schema
     */
    const DB_VERSION = '1.0.0';

    /**
     * Option name pour stocker la version
     */
    const DB_VERSION_OPTION = 'el_documents_db_version';

    /**
     * Noms des tables
     */
    const TABLE_DOCUMENT_TYPES = 'el_document_types';
    const TABLE_VENDOR_DOCUMENTS = 'el_vendor_documents';
    const TABLE_AUDIT_LOG = 'el_document_audit_log';

    /**
     * Initialisation
     */
    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'maybe_create_tables' ) );
    }

    /**
     * Verifie si les tables doivent etre creees ou mises a jour
     */
    public static function maybe_create_tables() {
        $installed_version = get_option( self::DB_VERSION_OPTION, '0' );

        if ( version_compare( $installed_version, self::DB_VERSION, '<' ) ) {
            self::create_tables();
            self::insert_default_document_types();
            update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        }
    }

    /**
     * Cree les tables necessaires
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des types de documents (definis par l'admin)
        $table_types = $wpdb->prefix . self::TABLE_DOCUMENT_TYPES;
        $sql_types = "CREATE TABLE $table_types (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            allowed_extensions varchar(255) NOT NULL DEFAULT 'pdf,jpg,jpeg,png',
            max_file_size bigint(20) UNSIGNED NOT NULL DEFAULT 5242880,
            is_required tinyint(1) NOT NULL DEFAULT 0,
            required_at_registration tinyint(1) NOT NULL DEFAULT 0,
            sort_order int(11) NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY is_required (is_required),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        // Table des documents uploades par les partenaires
        $table_documents = $wpdb->prefix . self::TABLE_VENDOR_DOCUMENTS;
        $sql_documents = "CREATE TABLE $table_documents (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) UNSIGNED NOT NULL,
            document_type_id bigint(20) UNSIGNED NOT NULL,
            original_filename varchar(255) NOT NULL,
            stored_filename varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            mime_type varchar(100) NOT NULL,
            file_size bigint(20) UNSIGNED NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            rejection_reason text,
            uploaded_at datetime NOT NULL,
            reviewed_at datetime DEFAULT NULL,
            reviewed_by bigint(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY vendor_doctype (vendor_id, document_type_id),
            KEY vendor_id (vendor_id),
            KEY document_type_id (document_type_id),
            KEY status (status)
        ) $charset_collate;";

        // Table du journal d'audit
        $table_audit = $wpdb->prefix . self::TABLE_AUDIT_LOG;
        $sql_audit = "CREATE TABLE $table_audit (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id bigint(20) UNSIGNED NOT NULL,
            action varchar(50) NOT NULL,
            performed_by bigint(20) UNSIGNED NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text,
            details text,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY document_id (document_id),
            KEY action (action),
            KEY performed_by (performed_by),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_types );
        dbDelta( $sql_documents );
        dbDelta( $sql_audit );
    }

    /**
     * Insere les types de documents par defaut
     */
    public static function insert_default_document_types() {
        global $wpdb;

        $table_types = $wpdb->prefix . self::TABLE_DOCUMENT_TYPES;

        // Verifier si des types existent deja
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_types" );
        if ( $count > 0 ) {
            return;
        }

        $now = current_time( 'mysql' );

        // Types de documents par defaut
        $default_types = array(
            array(
                'name' => 'KBIS / Extrait K-bis',
                'description' => 'Extrait K-bis de moins de 3 mois pour les entreprises, ou equivalent pour les associations (recepisse de declaration en prefecture).',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5242880, // 5MB
                'is_required' => 1,
                'required_at_registration' => 0,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array(
                'name' => 'Piece d\'identite du representant legal',
                'description' => 'Copie recto-verso d\'une piece d\'identite valide (carte d\'identite ou passeport) du representant legal de l\'organisation.',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5242880, // 5MB
                'is_required' => 1,
                'required_at_registration' => 0,
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array(
                'name' => 'RIB / IBAN',
                'description' => 'Releve d\'identite bancaire (RIB) ou IBAN au nom de l\'organisation pour le versement des paiements.',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2097152, // 2MB
                'is_required' => 1,
                'required_at_registration' => 0,
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array(
                'name' => 'Attestation d\'assurance RC Pro',
                'description' => 'Attestation d\'assurance responsabilite civile professionnelle en cours de validite.',
                'allowed_extensions' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5242880, // 5MB
                'is_required' => 1,
                'required_at_registration' => 0,
                'sort_order' => 4,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ),
        );

        foreach ( $default_types as $type ) {
            $wpdb->insert( $table_types, $type );
        }
    }

    /**
     * Recupere le nom de la table avec prefixe
     *
     * @param string $table_name Nom de la table sans prefixe
     * @return string Nom complet de la table
     */
    public static function get_table_name( $table_name ) {
        global $wpdb;
        return $wpdb->prefix . $table_name;
    }

    /**
     * Supprime les tables (pour desinstallation)
     * A utiliser avec precaution !
     */
    public static function drop_tables() {
        global $wpdb;

        $table_types = $wpdb->prefix . self::TABLE_DOCUMENT_TYPES;
        $table_documents = $wpdb->prefix . self::TABLE_VENDOR_DOCUMENTS;
        $table_audit = $wpdb->prefix . self::TABLE_AUDIT_LOG;

        $wpdb->query( "DROP TABLE IF EXISTS $table_audit" );
        $wpdb->query( "DROP TABLE IF EXISTS $table_documents" );
        $wpdb->query( "DROP TABLE IF EXISTS $table_types" );

        delete_option( self::DB_VERSION_OPTION );
    }

    /**
     * Recupere les statistiques de la base de donnees
     *
     * @return array Statistiques
     */
    public static function get_stats() {
        global $wpdb;

        $table_types = $wpdb->prefix . self::TABLE_DOCUMENT_TYPES;
        $table_documents = $wpdb->prefix . self::TABLE_VENDOR_DOCUMENTS;

        return array(
            'document_types_total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_types" ),
            'document_types_active' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_types WHERE is_active = 1" ),
            'documents_total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_documents" ),
            'documents_pending' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_documents WHERE status = 'pending'" ),
            'documents_approved' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_documents WHERE status = 'approved'" ),
            'documents_rejected' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_documents WHERE status = 'rejected'" ),
        );
    }
}

// Initialiser
EL_Document_Database::init();
