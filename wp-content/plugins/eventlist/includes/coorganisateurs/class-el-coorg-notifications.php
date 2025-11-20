<?php
/**
 * Class EL_Coorg_Notifications
 *
 * Gère l'envoi des notifications email pour le module co-organisateurs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Coorg_Notifications {

    /**
     * Envoie une notification d'invitation de partenariat
     */
    public static function send_partnership_invitation( $partnership_id ) {
        $partnership = EL_Partnership::get( $partnership_id );

        if ( ! $partnership ) {
            return false;
        }

        $invitee = get_userdata( $partnership->organisation_invitee_id );
        $inviter = get_userdata( $partnership->organisation_principale_id );

        if ( ! $invitee || ! $inviter ) {
            return false;
        }

        $inviter_org_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_principale_id );

        $to = $invitee->user_email;
        $subject = sprintf( __( 'Invitation à devenir partenaire de %s', 'eventlist' ), $inviter_org_name );

        $message = sprintf(
            __( 'Bonjour,%s%s%s%s souhaite vous ajouter comme organisation partenaire sur Le Hiboo.%s%sVous pouvez accepter ou refuser cette invitation dans votre espace partenaire :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            $inviter_org_name,
            "\n\n",
            "\n",
            "\n\n",
            "\n",
            home_url( '/vendor/partenariats/' ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Envoie une invitation de partenariat à un email (nouvel utilisateur)
     */
    public static function send_partnership_invitation_new_user( $partnership_id, $email ) {
        $partnership = EL_Partnership::get( $partnership_id );

        if ( ! $partnership ) {
            return false;
        }

        $inviter = get_userdata( $partnership->organisation_principale_id );
        if ( ! $inviter ) {
            return false;
        }

        $inviter_org_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_principale_id );

        $to = $email;
        $subject = sprintf( __( 'Invitation à rejoindre Le Hiboo en tant que partenaire de %s', 'eventlist' ), $inviter_org_name );

        $message = sprintf(
            __( 'Bonjour,%s%s%s%s souhaite collaborer avec vous sur Le Hiboo, la plateforme de gestion d\'événements.%s%sPour accepter cette invitation, veuillez créer un compte organisation sur Le Hiboo :%s%s%sUne fois votre compte créé, vous pourrez accepter ou refuser le partenariat.%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            $inviter_org_name,
            "\n\n",
            "\n",
            "\n\n",
            "\n",
            home_url( '/inscription-partenaire/' ),
            "\n\n",
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Notifie que le partenariat a été accepté
     */
    public static function send_partnership_accepted( $partnership_id ) {
        $partnership = EL_Partnership::get( $partnership_id );

        if ( ! $partnership ) {
            return false;
        }

        $inviter = get_userdata( $partnership->organisation_principale_id );
        $invitee = get_userdata( $partnership->organisation_invitee_id );

        if ( ! $inviter || ! $invitee ) {
            return false;
        }

        $invitee_org_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_invitee_id );

        $to = $inviter->user_email;
        $subject = sprintf( __( '%s a accepté votre invitation de partenariat', 'eventlist' ), $invitee_org_name );

        $message = sprintf(
            __( 'Bonjour,%s%sBonne nouvelle ! %s a accepté votre invitation de partenariat.%s%sVous pouvez maintenant ajouter cette organisation comme co-organisateur sur vos événements.%s%sVoir mes partenariats :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            "\n",
            $invitee_org_name,
            "\n\n",
            "\n",
            "\n\n",
            "\n",
            home_url( '/vendor/partenariats/' ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Notifie que le partenariat a été refusé
     */
    public static function send_partnership_refused( $partnership_id ) {
        $partnership = EL_Partnership::get( $partnership_id );

        if ( ! $partnership ) {
            return false;
        }

        $inviter = get_userdata( $partnership->organisation_principale_id );
        $invitee = get_userdata( $partnership->organisation_invitee_id );

        if ( ! $inviter || ! $invitee ) {
            return false;
        }

        $invitee_org_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_invitee_id );

        $to = $inviter->user_email;
        $subject = sprintf( __( '%s a refusé votre invitation de partenariat', 'eventlist' ), $invitee_org_name );

        $message = sprintf(
            __( 'Bonjour,%s%s%s a refusé votre invitation de partenariat.%s%sVous pouvez consulter vos partenariats dans votre espace :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            "\n",
            $invitee_org_name,
            "\n\n",
            "\n",
            home_url( '/vendor/partenariats/' ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Envoie une invitation de co-organisation d'événement
     */
    public static function send_event_coorganisation_invitation( $coorg_id ) {
        $coorg = EL_Event_Coorganisation::get( $coorg_id );

        if ( ! $coorg ) {
            return false;
        }

        $event = get_post( $coorg->event_id );
        $invitee = get_userdata( $coorg->organisation_coorganisatrice_id );
        $inviter = get_userdata( $coorg->organisation_principale_id );

        if ( ! $event || ! $invitee || ! $inviter ) {
            return false;
        }

        $inviter_org_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_principale_id );

        $to = $invitee->user_email;
        $subject = sprintf( __( 'Invitation à co-organiser "%s"', 'eventlist' ), $event->post_title );

        $message = sprintf(
            __( 'Bonjour,%s%s%s vous invite à co-organiser l\'événement "%s".%s%sRôle : %s%s%sVous pouvez accepter ou refuser cette invitation dans votre espace :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            "\n",
            $inviter_org_name,
            $event->post_title,
            "\n\n",
            "\n",
            $coorg->role,
            "\n\n",
            "\n",
            home_url( '/vendor/coorganisations/' ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Notifie que la co-organisation a été acceptée
     */
    public static function send_event_coorganisation_accepted( $coorg_id ) {
        $coorg = EL_Event_Coorganisation::get( $coorg_id );

        if ( ! $coorg ) {
            return false;
        }

        $event = get_post( $coorg->event_id );
        $inviter = get_userdata( $coorg->organisation_principale_id );
        $invitee = get_userdata( $coorg->organisation_coorganisatrice_id );

        if ( ! $event || ! $inviter || ! $invitee ) {
            return false;
        }

        $invitee_org_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_coorganisatrice_id );

        $to = $inviter->user_email;
        $subject = sprintf( __( '%s a accepté de co-organiser "%s"', 'eventlist' ), $invitee_org_name, $event->post_title );

        $message = sprintf(
            __( 'Bonjour,%s%sBonne nouvelle ! %s a accepté votre invitation à co-organiser l\'événement "%s".%s%sVoir l\'événement :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            "\n",
            $invitee_org_name,
            $event->post_title,
            "\n\n",
            "\n",
            get_permalink( $event->ID ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }

    /**
     * Notifie que la co-organisation a été refusée
     */
    public static function send_event_coorganisation_refused( $coorg_id ) {
        $coorg = EL_Event_Coorganisation::get( $coorg_id );

        if ( ! $coorg ) {
            return false;
        }

        $event = get_post( $coorg->event_id );
        $inviter = get_userdata( $coorg->organisation_principale_id );
        $invitee = get_userdata( $coorg->organisation_coorganisatrice_id );

        if ( ! $event || ! $inviter || ! $invitee ) {
            return false;
        }

        $invitee_org_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_coorganisatrice_id );

        $to = $inviter->user_email;
        $subject = sprintf( __( '%s a refusé de co-organiser "%s"', 'eventlist' ), $invitee_org_name, $event->post_title );

        $message = sprintf(
            __( 'Bonjour,%s%s%s a refusé votre invitation à co-organiser l\'événement "%s".%s%sVoir l\'événement :%s%s%sBien cordialement,%sL\'équipe Le Hiboo', 'eventlist' ),
            "\n\n",
            "\n",
            $invitee_org_name,
            $event->post_title,
            "\n\n",
            "\n",
            get_permalink( $event->ID ),
            "\n\n",
            "\n",
            "\n"
        );

        return wp_mail( $to, $subject, $message );
    }
}
