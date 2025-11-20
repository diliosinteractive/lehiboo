<?php
/**
 * Class EL_Event_Coorganisation
 *
 * Gère les co-organisations d'événements (niveau événement)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Event_Coorganisation {

    /**
     * Statuts possibles
     */
    const STATUS_EN_COURS = 'en_cours';
    const STATUS_ACCEPTEE = 'acceptee';
    const STATUS_REFUSEE = 'refusee';
    const STATUS_RETIREE = 'retiree';

    /**
     * Rôles possibles
     */
    const ROLE_CO_ORGANISATEUR = 'co-organisateur';
    const ROLE_PARTENAIRE = 'partenaire';
    const ROLE_SPONSOR = 'sponsor';

    /**
     * Nom de la table
     */
    private static $table_name = null;

    /**
     * Initialisation
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'el_event_coorganisations';
    }

    /**
     * Crée une invitation de co-organisation pour un événement
     *
     * @param int $event_id ID de l'événement
     * @param int $org_principale_id ID de l'organisation principale (créatrice)
     * @param int $org_coorg_id ID de l'organisation co-organisatrice
     * @param string $role Rôle (co-organisateur, partenaire, sponsor)
     * @param int $invited_by_user_id ID de l'utilisateur qui fait l'invitation
     * @return int|false ID de la co-organisation créée ou false en cas d'erreur
     */
    public static function create_invitation( $event_id, $org_principale_id, $org_coorg_id, $role = self::ROLE_CO_ORGANISATEUR, $invited_by_user_id = null ) {
        global $wpdb;

        // Validation
        if ( empty( $event_id ) || empty( $org_principale_id ) || empty( $org_coorg_id ) ) {
            return false;
        }

        // Vérifier si une co-organisation existe déjà pour cet événement et cette organisation
        if ( self::exists( $event_id, $org_coorg_id ) ) {
            return false;
        }

        // User ID par défaut
        if ( ! $invited_by_user_id ) {
            $invited_by_user_id = get_current_user_id();
        }

        $data = array(
            'event_id' => $event_id,
            'organisation_principale_id' => $org_principale_id,
            'organisation_coorganisatrice_id' => $org_coorg_id,
            'statut' => self::STATUS_EN_COURS,
            'date_invitation' => current_time( 'mysql' ),
            'invited_by_user_id' => $invited_by_user_id,
            'role' => $role,
            'can_edit' => 0,
        );

        $result = $wpdb->insert( self::$table_name, $data );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Vérifie si une co-organisation existe pour un événement et une organisation
     */
    public static function exists( $event_id, $org_id ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . "
            WHERE event_id = %d AND organisation_coorganisatrice_id = %d",
            $event_id, $org_id
        ) );

        return $count > 0;
    }

    /**
     * Récupère une co-organisation par ID
     */
    public static function get( $coorg_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $coorg_id
        ) );
    }

    /**
     * Récupère toutes les co-organisations d'un événement
     *
     * @param int $event_id ID de l'événement
     * @param string|null $statut Filtrer par statut (optionnel)
     * @return array
     */
    public static function get_for_event( $event_id, $statut = null ) {
        global $wpdb;

        $where = $wpdb->prepare( "event_id = %d", $event_id );

        if ( $statut ) {
            $where .= $wpdb->prepare( " AND statut = %s", $statut );
        }

        return $wpdb->get_results(
            "SELECT * FROM " . self::$table_name . "
            WHERE $where
            ORDER BY date_invitation DESC"
        );
    }

    /**
     * Récupère tous les événements où une organisation est co-organisatrice
     *
     * @param int $org_id ID de l'organisation
     * @param string|null $statut Filtrer par statut (optionnel)
     * @return array
     */
    public static function get_for_organisation( $org_id, $statut = null ) {
        global $wpdb;

        $where = $wpdb->prepare( "organisation_coorganisatrice_id = %d", $org_id );

        if ( $statut ) {
            $where .= $wpdb->prepare( " AND statut = %s", $statut );
        }

        return $wpdb->get_results(
            "SELECT * FROM " . self::$table_name . "
            WHERE $where
            ORDER BY date_invitation DESC"
        );
    }

    /**
     * Récupère les invitations en attente pour une organisation
     */
    public static function get_pending_invitations( $org_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . "
            WHERE organisation_coorganisatrice_id = %d AND statut = %s
            ORDER BY date_invitation DESC",
            $org_id, self::STATUS_EN_COURS
        ) );
    }

    /**
     * Récupère les co-organisateurs acceptés d'un événement
     * Utilisé pour l'affichage public
     */
    public static function get_accepted_coorganisers( $event_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . "
            WHERE event_id = %d AND statut = %s
            ORDER BY date_invitation ASC",
            $event_id, self::STATUS_ACCEPTEE
        ) );
    }

    /**
     * Accepte une invitation de co-organisation
     */
    public static function accept( $coorg_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_ACCEPTEE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $coorg_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Refuse une invitation de co-organisation
     */
    public static function refuse( $coorg_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_REFUSEE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $coorg_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Se retire d'une co-organisation (événement)
     */
    public static function retire( $coorg_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_RETIREE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $coorg_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Supprime définitivement une co-organisation
     */
    public static function delete( $coorg_id ) {
        global $wpdb;

        return $wpdb->delete(
            self::$table_name,
            array( 'id' => $coorg_id ),
            array( '%d' )
        );
    }

    /**
     * Supprime toutes les co-organisations d'un événement
     * Utilisé lors de la suppression d'un événement
     */
    public static function delete_for_event( $event_id ) {
        global $wpdb;

        return $wpdb->delete(
            self::$table_name,
            array( 'event_id' => $event_id ),
            array( '%d' )
        );
    }

    /**
     * Met à jour le rôle d'un co-organisateur
     */
    public static function update_role( $coorg_id, $role ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array( 'role' => $role ),
            array( 'id' => $coorg_id ),
            array( '%s' ),
            array( '%d' )
        );
    }

    /**
     * Compte les co-organisations par statut pour une organisation
     */
    public static function count_by_status( $org_id ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT statut, COUNT(*) as total
            FROM " . self::$table_name . "
            WHERE organisation_coorganisatrice_id = %d
            GROUP BY statut",
            $org_id
        ), OBJECT_K );

        return array(
            'en_cours' => isset( $results[ self::STATUS_EN_COURS ] ) ? (int) $results[ self::STATUS_EN_COURS ]->total : 0,
            'acceptee' => isset( $results[ self::STATUS_ACCEPTEE ] ) ? (int) $results[ self::STATUS_ACCEPTEE ]->total : 0,
            'refusee' => isset( $results[ self::STATUS_REFUSEE ] ) ? (int) $results[ self::STATUS_REFUSEE ]->total : 0,
            'retiree' => isset( $results[ self::STATUS_RETIREE ] ) ? (int) $results[ self::STATUS_RETIREE ]->total : 0,
        );
    }

    /**
     * Compte le nombre d'événements co-organisés entre deux organisations
     */
    public static function count_events_between_orgs( $org_id_1, $org_id_2 ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . "
            WHERE ((organisation_principale_id = %d AND organisation_coorganisatrice_id = %d)
               OR (organisation_principale_id = %d AND organisation_coorganisatrice_id = %d))
              AND statut = %s",
            $org_id_1, $org_id_2, $org_id_2, $org_id_1, self::STATUS_ACCEPTEE
        ) );
    }
}

// Initialiser
EL_Event_Coorganisation::init();
