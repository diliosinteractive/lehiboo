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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p><strong>' . esc_html( $inviter_org_name ) . '</strong> souhaite vous ajouter comme organisation partenaire sur Le Hiboo.</p>';
        $message .= '<p>Vous pouvez accepter ou refuser cette invitation dans votre espace partenaire :</p>';
        $message .= '<p><a href="' . esc_url( home_url( '/member-account/?vendor=partenariats' ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir mes partenariats</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        // Headers pour HTML
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p><strong>' . esc_html( $inviter_org_name ) . '</strong> souhaite collaborer avec vous sur Le Hiboo, la plateforme de gestion d\'événements.</p>';
        $message .= '<p>Pour accepter cette invitation, veuillez créer un compte organisation sur Le Hiboo :</p>';
        $message .= '<p><a href="' . esc_url( home_url( '/inscription-partenaire/' ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Créer mon compte</a></p>';
        $message .= '<p>Une fois votre compte créé, vous pourrez accepter ou refuser le partenariat.</p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        // Headers pour HTML
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p style="color: #10b981; font-weight: bold;">Bonne nouvelle ! <strong>' . esc_html( $invitee_org_name ) . '</strong> a accepté votre invitation de partenariat.</p>';
        $message .= '<p>Vous pouvez maintenant ajouter cette organisation comme co-organisateur sur vos événements.</p>';
        $message .= '<p><a href="' . esc_url( home_url( '/member-account/?vendor=partenariats' ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir mes partenariats</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p><strong>' . esc_html( $invitee_org_name ) . '</strong> a refusé votre invitation de partenariat.</p>';
        $message .= '<p>Vous pouvez consulter vos partenariats dans votre espace :</p>';
        $message .= '<p><a href="' . esc_url( home_url( '/member-account/?vendor=partenariats' ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir mes partenariats</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p><strong>' . esc_html( $inviter_org_name ) . '</strong> vous invite à co-organiser l\'événement <strong>"' . esc_html( $event->post_title ) . '"</strong>.</p>';
        $message .= '<p><strong>Rôle :</strong> ' . esc_html( $coorg->role ) . '</p>';
        $message .= '<p>Vous pouvez accepter ou refuser cette invitation dans votre espace :</p>';
        $message .= '<p><a href="' . esc_url( home_url( '/member-account/?vendor=coorganisations' ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir mes co-organisations</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        // Headers pour HTML
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p style="color: #10b981; font-weight: bold;">Bonne nouvelle ! <strong>' . esc_html( $invitee_org_name ) . '</strong> a accepté votre invitation à co-organiser l\'événement <strong>"' . esc_html( $event->post_title ) . '"</strong>.</p>';
        $message .= '<p>Vous pouvez consulter l\'événement ici :</p>';
        $message .= '<p><a href="' . esc_url( get_permalink( $event->ID ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir l\'événement</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
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

        // Email HTML
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<p>Bonjour,</p>';
        $message .= '<p><strong>' . esc_html( $invitee_org_name ) . '</strong> a refusé votre invitation à co-organiser l\'événement <strong>"' . esc_html( $event->post_title ) . '"</strong>.</p>';
        $message .= '<p>Vous pouvez consulter l\'événement ici :</p>';
        $message .= '<p><a href="' . esc_url( get_permalink( $event->ID ) ) . '" style="display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Voir l\'événement</a></p>';
        $message .= '<p>Bien cordialement,<br>L\'équipe Le Hiboo</p>';
        $message .= '</body></html>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
    }
}
