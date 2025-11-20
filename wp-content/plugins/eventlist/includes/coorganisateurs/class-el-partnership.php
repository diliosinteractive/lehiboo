<?php
/**
 * Class EL_Partnership
 *
 * Gère les partenariats entre organisations (niveau compte)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Partnership {

    /**
     * Statuts possibles
     */
    const STATUS_EN_COURS = 'en_cours';
    const STATUS_ACCEPTEE = 'acceptee';
    const STATUS_REFUSEE = 'refusee';
    const STATUS_RETIREE = 'retiree';

    /**
     * Nom de la table
     */
    private static $table_name = null;

    /**
     * Initialisation
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'el_organisation_partnerships';
    }

    /**
     * Crée une invitation de partenariat
     *
     * @param int $org_principale_id ID de l'organisation qui invite
     * @param int|null $org_invitee_id ID de l'organisation invitée (null si pas encore dans la BDD)
     * @param string|null $email_invite Email si l'organisation n'existe pas encore
     * @param int $invited_by_user_id ID de l'utilisateur qui fait l'invitation
     * @return int|false ID du partenariat créé ou false en cas d'erreur
     */
    public static function create_invitation( $org_principale_id, $org_invitee_id = null, $email_invite = null, $invited_by_user_id = null ) {
        global $wpdb;

        // Validation
        if ( empty( $org_principale_id ) ) {
            return false;
        }

        if ( empty( $org_invitee_id ) && empty( $email_invite ) ) {
            return false;
        }

        // Vérifier si un partenariat existe déjà
        if ( $org_invitee_id && self::exists( $org_principale_id, $org_invitee_id ) ) {
            return false;
        }

        // User ID par défaut
        if ( ! $invited_by_user_id ) {
            $invited_by_user_id = get_current_user_id();
        }

        $data = array(
            'organisation_principale_id' => $org_principale_id,
            'organisation_invitee_id' => $org_invitee_id,
            'email_invite' => $email_invite,
            'statut' => self::STATUS_EN_COURS,
            'date_invitation' => current_time( 'mysql' ),
            'invited_by_user_id' => $invited_by_user_id,
            'can_see_events' => 1,
            'can_edit_some_fields' => 0,
        );

        $result = $wpdb->insert( self::$table_name, $data );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Vérifie si un partenariat existe entre deux organisations
     */
    public static function exists( $org_id_1, $org_id_2 ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . "
            WHERE (organisation_principale_id = %d AND organisation_invitee_id = %d)
               OR (organisation_principale_id = %d AND organisation_invitee_id = %d)",
            $org_id_1, $org_id_2, $org_id_2, $org_id_1
        ) );

        return $count > 0;
    }

    /**
     * Récupère un partenariat par ID
     */
    public static function get( $partnership_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $partnership_id
        ) );
    }

    /**
     * Récupère un partenariat entre deux organisations
     */
    public static function get_between_orgs( $org_id_1, $org_id_2 ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . "
            WHERE (organisation_principale_id = %d AND organisation_invitee_id = %d)
               OR (organisation_principale_id = %d AND organisation_invitee_id = %d)
            LIMIT 1",
            $org_id_1, $org_id_2, $org_id_2, $org_id_1
        ) );
    }

    /**
     * Récupère tous les partenariats d'une organisation
     *
     * @param int $org_id ID de l'organisation
     * @param string|null $statut Filtrer par statut (optionnel)
     * @return array
     */
    public static function get_for_organisation( $org_id, $statut = null ) {
        global $wpdb;

        $where = $wpdb->prepare(
            "(organisation_principale_id = %d OR organisation_invitee_id = %d)",
            $org_id, $org_id
        );

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
     * Récupère les invitations en attente reçues par une organisation
     */
    public static function get_pending_invitations( $org_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . "
            WHERE organisation_invitee_id = %d AND statut = %s
            ORDER BY date_invitation DESC",
            $org_id, self::STATUS_EN_COURS
        ) );
    }

    /**
     * Accepte une invitation de partenariat
     */
    public static function accept( $partnership_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_ACCEPTEE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $partnership_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Refuse une invitation de partenariat
     */
    public static function refuse( $partnership_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_REFUSEE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $partnership_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Met fin à un partenariat (retire)
     */
    public static function retire( $partnership_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array(
                'statut' => self::STATUS_RETIREE,
                'date_reponse' => current_time( 'mysql' ),
            ),
            array( 'id' => $partnership_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Supprime définitivement un partenariat
     */
    public static function delete( $partnership_id ) {
        global $wpdb;

        return $wpdb->delete(
            self::$table_name,
            array( 'id' => $partnership_id ),
            array( '%d' )
        );
    }

    /**
     * Récupère les organisations partenaires acceptées pour une organisation
     * Utilisé pour afficher la liste des partenaires disponibles lors de l'ajout de co-orgs à un événement
     */
    public static function get_accepted_partners( $org_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::$table_name . "
            WHERE (organisation_principale_id = %d OR organisation_invitee_id = %d)
              AND statut = %s
            ORDER BY date_invitation DESC",
            $org_id, $org_id, self::STATUS_ACCEPTEE
        ) );
    }

    /**
     * Associe un email invité à une organisation existante
     * Utilisé quand une organisation créée via email rejoint Le Hiboo
     */
    public static function link_email_to_organisation( $email, $org_id ) {
        global $wpdb;

        return $wpdb->update(
            self::$table_name,
            array( 'organisation_invitee_id' => $org_id ),
            array( 'email_invite' => $email ),
            array( '%d' ),
            array( '%s' )
        );
    }

    /**
     * Compte les partenariats par statut pour une organisation
     */
    public static function count_by_status( $org_id ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT statut, COUNT(*) as total
            FROM " . self::$table_name . "
            WHERE organisation_principale_id = %d OR organisation_invitee_id = %d
            GROUP BY statut",
            $org_id, $org_id
        ), OBJECT_K );

        return array(
            'en_cours' => isset( $results[ self::STATUS_EN_COURS ] ) ? (int) $results[ self::STATUS_EN_COURS ]->total : 0,
            'acceptee' => isset( $results[ self::STATUS_ACCEPTEE ] ) ? (int) $results[ self::STATUS_ACCEPTEE ]->total : 0,
            'refusee' => isset( $results[ self::STATUS_REFUSEE ] ) ? (int) $results[ self::STATUS_REFUSEE ]->total : 0,
            'retiree' => isset( $results[ self::STATUS_RETIREE ] ) ? (int) $results[ self::STATUS_RETIREE ]->total : 0,
        );
    }
}

// Initialiser
EL_Partnership::init();
