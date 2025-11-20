<?php
/**
 * Class EL_Coorg_Database
 *
 * Gère la création et la mise à jour des tables pour le module co-organisateurs
 *
 * Tables créées :
 * - wp_el_organisation_partnerships : Partenariats entre organisations (niveau compte)
 * - wp_el_event_coorganisations : Co-organisations d'événements (niveau événement)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Coorg_Database {

    /**
     * Version de la base de données
     * Incrémenter lors de changements de schéma
     */
    const DB_VERSION = '1.0.0';

    /**
     * Option name pour stocker la version
     */
    const DB_VERSION_OPTION = 'el_coorg_db_version';

    /**
     * Initialisation
     */
    public static function init() {
        // Hook pour créer les tables lors de l'activation du plugin
        add_action( 'admin_init', array( __CLASS__, 'maybe_create_tables' ) );
    }

    /**
     * Vérifie si les tables doivent être créées ou mises à jour
     */
    public static function maybe_create_tables() {
        $installed_version = get_option( self::DB_VERSION_OPTION, '0' );

        if ( version_compare( $installed_version, self::DB_VERSION, '<' ) ) {
            self::create_tables();
            update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        }
    }

    /**
     * Crée les tables nécessaires
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des partenariats entre organisations (niveau compte)
        $table_partnerships = $wpdb->prefix . 'el_organisation_partnerships';
        $sql_partnerships = "CREATE TABLE $table_partnerships (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            organisation_principale_id bigint(20) UNSIGNED NOT NULL,
            organisation_invitee_id bigint(20) UNSIGNED DEFAULT NULL,
            email_invite varchar(255) DEFAULT NULL,
            statut varchar(20) NOT NULL DEFAULT 'en_cours',
            date_invitation datetime NOT NULL,
            date_reponse datetime DEFAULT NULL,
            invited_by_user_id bigint(20) UNSIGNED NOT NULL,
            can_see_events tinyint(1) NOT NULL DEFAULT 1,
            can_edit_some_fields tinyint(1) NOT NULL DEFAULT 0,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY organisation_principale_id (organisation_principale_id),
            KEY organisation_invitee_id (organisation_invitee_id),
            KEY statut (statut),
            KEY email_invite (email_invite)
        ) $charset_collate;";

        // Table des co-organisations d'événements (niveau événement)
        $table_coorganisations = $wpdb->prefix . 'el_event_coorganisations';
        $sql_coorganisations = "CREATE TABLE $table_coorganisations (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id bigint(20) UNSIGNED NOT NULL,
            organisation_principale_id bigint(20) UNSIGNED NOT NULL,
            organisation_coorganisatrice_id bigint(20) UNSIGNED NOT NULL,
            statut varchar(20) NOT NULL DEFAULT 'en_cours',
            date_invitation datetime NOT NULL,
            date_reponse datetime DEFAULT NULL,
            invited_by_user_id bigint(20) UNSIGNED NOT NULL,
            role varchar(50) DEFAULT 'co-organisateur',
            can_edit tinyint(1) NOT NULL DEFAULT 0,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY event_org_unique (event_id, organisation_coorganisatrice_id),
            KEY event_id (event_id),
            KEY organisation_principale_id (organisation_principale_id),
            KEY organisation_coorganisatrice_id (organisation_coorganisatrice_id),
            KEY statut (statut)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_partnerships );
        dbDelta( $sql_coorganisations );
    }

    /**
     * Supprime les tables (pour désinstallation)
     * À utiliser avec précaution !
     */
    public static function drop_tables() {
        global $wpdb;

        $table_partnerships = $wpdb->prefix . 'el_organisation_partnerships';
        $table_coorganisations = $wpdb->prefix . 'el_event_coorganisations';

        $wpdb->query( "DROP TABLE IF EXISTS $table_partnerships" );
        $wpdb->query( "DROP TABLE IF EXISTS $table_coorganisations" );

        delete_option( self::DB_VERSION_OPTION );
    }

    /**
     * Récupère les statistiques de la base de données
     */
    public static function get_stats() {
        global $wpdb;

        $table_partnerships = $wpdb->prefix . 'el_organisation_partnerships';
        $table_coorganisations = $wpdb->prefix . 'el_event_coorganisations';

        return array(
            'partnerships_total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_partnerships" ),
            'partnerships_en_cours' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_partnerships WHERE statut = 'en_cours'" ),
            'partnerships_acceptees' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_partnerships WHERE statut = 'acceptee'" ),
            'coorganisations_total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_coorganisations" ),
            'coorganisations_en_cours' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_coorganisations WHERE statut = 'en_cours'" ),
            'coorganisations_acceptees' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_coorganisations WHERE statut = 'acceptee'" ),
        );
    }
}

// Initialiser
EL_Coorg_Database::init();
