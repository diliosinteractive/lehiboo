<?php
/**
 * Class EL_Coorg_Ajax
 *
 * Gère tous les handlers AJAX pour le module co-organisateurs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Coorg_Ajax {

    /**
     * Initialisation
     */
    public static function init() {
        // Partenariats
        add_action( 'wp_ajax_el_search_organisations', array( __CLASS__, 'search_organisations' ) );
        add_action( 'wp_ajax_el_invite_partner', array( __CLASS__, 'invite_partner' ) );
        add_action( 'wp_ajax_el_accept_partnership', array( __CLASS__, 'accept_partnership' ) );
        add_action( 'wp_ajax_el_refuse_partnership', array( __CLASS__, 'refuse_partnership' ) );
        add_action( 'wp_ajax_el_retire_partnership', array( __CLASS__, 'retire_partnership' ) );

        // Co-organisations d'événements
        add_action( 'wp_ajax_el_add_event_coorganiser', array( __CLASS__, 'add_event_coorganiser' ) );
        add_action( 'wp_ajax_el_remove_event_coorganiser', array( __CLASS__, 'remove_event_coorganiser' ) );
        add_action( 'wp_ajax_el_accept_event_coorganisation', array( __CLASS__, 'accept_event_coorganisation' ) );
        add_action( 'wp_ajax_el_refuse_event_coorganisation', array( __CLASS__, 'refuse_event_coorganisation' ) );
        add_action( 'wp_ajax_el_retire_event_coorganisation', array( __CLASS__, 'retire_event_coorganisation' ) );
    }

    /**
     * Recherche d'organisations (autocomplete)
     */
    public static function search_organisations() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

        if ( empty( $search ) || strlen( $search ) < 2 ) {
            wp_send_json_success( array( 'organisations' => array() ) );
        }

        global $wpdb;

        // Rechercher dans les organisations (user_meta)
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.user_email,
                    COALESCE(um2.meta_value, um1.meta_value) as org_name
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} ur ON u.ID = ur.user_id AND ur.meta_key = 'wp_capabilities' AND ur.meta_value LIKE %s
            LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'org_name'
            LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'org_display_name'
            WHERE (um1.meta_value LIKE %s OR um2.meta_value LIKE %s OR u.user_email LIKE %s)
              AND u.ID != %d
            LIMIT 10",
            '%el_event_vendor%',
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%',
            get_current_user_id()
        ) );

        $organisations = array();
        foreach ( $results as $result ) {
            $organisations[] = array(
                'id' => $result->ID,
                'name' => $result->org_name,
                'email' => $result->user_email,
            );
        }

        wp_send_json_success( array( 'organisations' => $organisations ) );
    }

    /**
     * Invite un partenaire
     */
    public static function invite_partner() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $org_id = isset( $_POST['org_id'] ) ? intval( $_POST['org_id'] ) : 0;
        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        $current_user_id = get_current_user_id();

        // Vérifier qu'au moins un ID ou un email est fourni
        if ( empty( $org_id ) && empty( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Veuillez sélectionner une organisation ou saisir un email', 'eventlist' ) ) );
        }

        // Si aucun org_id n'est fourni mais qu'on a un email, vérifier si cet email existe déjà comme vendor
        if ( empty( $org_id ) && ! empty( $email ) ) {
            $user = get_user_by( 'email', $email );
            if ( $user && user_can( $user, 'el_event_vendor' ) ) {
                // L'utilisateur existe et est un vendor, utiliser son ID
                $org_id = $user->ID;
            }
        }

        // Créer l'invitation
        $partnership_id = EL_Partnership::create_invitation(
            $current_user_id,
            $org_id > 0 ? $org_id : null,
            $email,
            $current_user_id
        );

        if ( ! $partnership_id ) {
            wp_send_json_error( array( 'message' => __( 'Impossible de créer l\'invitation. Un partenariat existe peut-être déjà.', 'eventlist' ) ) );
        }

        // Envoyer la notification appropriée
        if ( $org_id > 0 ) {
            // Utilisateur existant = email standard
            EL_Coorg_Notifications::send_partnership_invitation( $partnership_id );
        } else {
            // Nouvel utilisateur = email "créer compte"
            EL_Coorg_Notifications::send_partnership_invitation_new_user( $partnership_id, $email );
        }

        wp_send_json_success( array(
            'message' => __( 'Invitation envoyée avec succès', 'eventlist' ),
            'partnership_id' => $partnership_id,
        ) );
    }

    /**
     * Accepte une invitation de partenariat
     */
    public static function accept_partnership() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $partnership_id = isset( $_POST['partnership_id'] ) ? intval( $_POST['partnership_id'] ) : 0;

        if ( empty( $partnership_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID de partenariat manquant', 'eventlist' ) ) );
        }

        $current_user_id = get_current_user_id();
        $current_user = wp_get_current_user();

        // Vérifier que l'utilisateur est bien l'invité
        $partnership = EL_Partnership::get( $partnership_id );

        // L'utilisateur peut accepter si :
        // 1. Il est l'invité direct (organisation_invitee_id correspond)
        // 2. C'est une invitation par email et l'email correspond
        $can_accept = false;
        if ( $partnership ) {
            if ( $partnership->organisation_invitee_id == $current_user_id ) {
                $can_accept = true;
            } elseif ( $partnership->organisation_invitee_id === null && $partnership->email_invite === $current_user->user_email ) {
                $can_accept = true;

                // Mettre à jour l'organisation_invitee_id avec le user_id actuel
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'el_organisation_partnerships',
                    array( 'organisation_invitee_id' => $current_user_id ),
                    array( 'id' => $partnership_id ),
                    array( '%d' ),
                    array( '%d' )
                );
            }
        }

        if ( ! $can_accept ) {
            wp_send_json_error( array( 'message' => __( 'Partenariat introuvable ou accès refusé', 'eventlist' ) ) );
        }

        // Accepter
        $result = EL_Partnership::accept( $partnership_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'acceptation', 'eventlist' ) ) );
        }

        // Notifier l'organisation principale
        EL_Coorg_Notifications::send_partnership_accepted( $partnership_id );

        wp_send_json_success( array( 'message' => __( 'Partenariat accepté', 'eventlist' ) ) );
    }

    /**
     * Refuse une invitation de partenariat
     */
    public static function refuse_partnership() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $partnership_id = isset( $_POST['partnership_id'] ) ? intval( $_POST['partnership_id'] ) : 0;

        if ( empty( $partnership_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID de partenariat manquant', 'eventlist' ) ) );
        }

        $current_user_id = get_current_user_id();
        $current_user = wp_get_current_user();

        // Vérifier que l'utilisateur est bien l'invité
        $partnership = EL_Partnership::get( $partnership_id );

        // L'utilisateur peut refuser si :
        // 1. Il est l'invité direct (organisation_invitee_id correspond)
        // 2. C'est une invitation par email et l'email correspond
        $can_refuse = false;
        if ( $partnership ) {
            if ( $partnership->organisation_invitee_id == $current_user_id ) {
                $can_refuse = true;
            } elseif ( $partnership->organisation_invitee_id === null && $partnership->email_invite === $current_user->user_email ) {
                $can_refuse = true;

                // Mettre à jour l'organisation_invitee_id avec le user_id actuel
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'el_organisation_partnerships',
                    array( 'organisation_invitee_id' => $current_user_id ),
                    array( 'id' => $partnership_id ),
                    array( '%d' ),
                    array( '%d' )
                );
            }
        }

        if ( ! $can_refuse ) {
            wp_send_json_error( array( 'message' => __( 'Partenariat introuvable ou accès refusé', 'eventlist' ) ) );
        }

        // Refuser
        $result = EL_Partnership::refuse( $partnership_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors du refus', 'eventlist' ) ) );
        }

        // Notifier l'organisation principale
        EL_Coorg_Notifications::send_partnership_refused( $partnership_id );

        wp_send_json_success( array( 'message' => __( 'Partenariat refusé', 'eventlist' ) ) );
    }

    /**
     * Met fin à un partenariat
     */
    public static function retire_partnership() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $partnership_id = isset( $_POST['partnership_id'] ) ? intval( $_POST['partnership_id'] ) : 0;

        if ( empty( $partnership_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID de partenariat manquant', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur fait partie du partenariat
        $partnership = EL_Partnership::get( $partnership_id );
        $current_user_id = get_current_user_id();

        if ( ! $partnership ||
             ( $partnership->organisation_principale_id != $current_user_id &&
               $partnership->organisation_invitee_id != $current_user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Partenariat introuvable ou accès refusé', 'eventlist' ) ) );
        }

        // Retirer
        $result = EL_Partnership::retire( $partnership_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors du retrait', 'eventlist' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Partenariat clôturé', 'eventlist' ) ) );
    }

    /**
     * Ajoute un co-organisateur à un événement
     */
    public static function add_event_coorganiser() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
        $org_id = isset( $_POST['org_id'] ) ? intval( $_POST['org_id'] ) : 0;
        $role = isset( $_POST['role'] ) ? sanitize_text_field( $_POST['role'] ) : EL_Event_Coorganisation::ROLE_CO_ORGANISATEUR;

        if ( empty( $event_id ) || empty( $org_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Paramètres manquants', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur est l'auteur de l'événement
        $event = get_post( $event_id );
        if ( ! $event || $event->post_author != get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Vous n\'êtes pas autorisé à modifier cet événement', 'eventlist' ) ) );
        }

        // Vérifier qu'il existe un partenariat accepté
        $partnership = EL_Partnership::get_between_orgs( get_current_user_id(), $org_id );
        if ( ! $partnership || $partnership->statut !== EL_Partnership::STATUS_ACCEPTEE ) {
            wp_send_json_error( array( 'message' => __( 'Vous devez d\'abord établir un partenariat avec cette organisation', 'eventlist' ) ) );
        }

        // Créer la co-organisation
        $coorg_id = EL_Event_Coorganisation::create_invitation(
            $event_id,
            get_current_user_id(),
            $org_id,
            $role
        );

        if ( ! $coorg_id ) {
            wp_send_json_error( array( 'message' => __( 'Impossible d\'ajouter ce co-organisateur. Il est peut-être déjà ajouté.', 'eventlist' ) ) );
        }

        // Envoyer la notification
        EL_Coorg_Notifications::send_event_coorganisation_invitation( $coorg_id );

        wp_send_json_success( array(
            'message' => __( 'Co-organisateur ajouté avec succès', 'eventlist' ),
            'coorg_id' => $coorg_id,
        ) );
    }

    /**
     * Retire un co-organisateur d'un événement (côté organisateur principal)
     */
    public static function remove_event_coorganiser() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $coorg_id = isset( $_POST['coorg_id'] ) ? intval( $_POST['coorg_id'] ) : 0;

        if ( empty( $coorg_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID manquant', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur est l'organisateur principal
        $coorg = EL_Event_Coorganisation::get( $coorg_id );
        if ( ! $coorg || $coorg->organisation_principale_id != get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Accès refusé', 'eventlist' ) ) );
        }

        // Supprimer ou marquer comme retiré
        $result = EL_Event_Coorganisation::delete( $coorg_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors de la suppression', 'eventlist' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Co-organisateur retiré', 'eventlist' ) ) );
    }

    /**
     * Accepte une invitation de co-organisation d'événement
     */
    public static function accept_event_coorganisation() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $coorg_id = isset( $_POST['coorg_id'] ) ? intval( $_POST['coorg_id'] ) : 0;

        if ( empty( $coorg_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID manquant', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur est le co-organisateur invité
        $coorg = EL_Event_Coorganisation::get( $coorg_id );
        if ( ! $coorg || $coorg->organisation_coorganisatrice_id != get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Accès refusé', 'eventlist' ) ) );
        }

        // Accepter
        $result = EL_Event_Coorganisation::accept( $coorg_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'acceptation', 'eventlist' ) ) );
        }

        // Notifier l'organisateur principal
        EL_Coorg_Notifications::send_event_coorganisation_accepted( $coorg_id );

        wp_send_json_success( array( 'message' => __( 'Vous êtes maintenant co-organisateur de cet événement', 'eventlist' ) ) );
    }

    /**
     * Refuse une invitation de co-organisation d'événement
     */
    public static function refuse_event_coorganisation() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $coorg_id = isset( $_POST['coorg_id'] ) ? intval( $_POST['coorg_id'] ) : 0;

        if ( empty( $coorg_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID manquant', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur est le co-organisateur invité
        $coorg = EL_Event_Coorganisation::get( $coorg_id );
        if ( ! $coorg || $coorg->organisation_coorganisatrice_id != get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Accès refusé', 'eventlist' ) ) );
        }

        // Refuser
        $result = EL_Event_Coorganisation::refuse( $coorg_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors du refus', 'eventlist' ) ) );
        }

        // Notifier l'organisateur principal
        EL_Coorg_Notifications::send_event_coorganisation_refused( $coorg_id );

        wp_send_json_success( array( 'message' => __( 'Invitation refusée', 'eventlist' ) ) );
    }

    /**
     * Se retire d'une co-organisation d'événement
     */
    public static function retire_event_coorganisation() {
        check_ajax_referer( 'el_coorg_nonce', 'nonce' );

        if ( ! el_is_vendor() ) {
            wp_send_json_error( array( 'message' => __( 'Action non autorisée', 'eventlist' ) ) );
        }

        $coorg_id = isset( $_POST['coorg_id'] ) ? intval( $_POST['coorg_id'] ) : 0;

        if ( empty( $coorg_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID manquant', 'eventlist' ) ) );
        }

        // Vérifier que l'utilisateur est le co-organisateur
        $coorg = EL_Event_Coorganisation::get( $coorg_id );
        if ( ! $coorg || $coorg->organisation_coorganisatrice_id != get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Accès refusé', 'eventlist' ) ) );
        }

        // Se retirer
        $result = EL_Event_Coorganisation::retire( $coorg_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Erreur lors du retrait', 'eventlist' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Vous vous êtes retiré de cet événement', 'eventlist' ) ) );
    }
}

// Initialiser
EL_Coorg_Ajax::init();
