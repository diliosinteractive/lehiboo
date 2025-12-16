<?php
/**
 * Class EL_Document_Notifications
 *
 * Notifications email pour le systeme de documents
 * V1 Le Hiboo - Gestion des documents securises
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_Notifications {

    /**
     * Envoie une notification quand un document est uploade (vers admin)
     *
     * @param int $document_id ID du document
     * @return bool
     */
    public static function notify_document_uploaded( $document_id ) {
        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            return false;
        }

        $vendor = get_userdata( $document->vendor_id );
        $document_type = EL_Document_Types::get( $document->document_type_id );

        if ( ! $vendor || ! $document_type ) {
            return false;
        }

        // Email admin
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );

        $subject = sprintf(
            '[%s] Nouveau document a valider - %s',
            $site_name,
            $document_type->name
        );

        $admin_url = admin_url( 'admin.php?page=el_vendor_documents&status=pending' );

        $message = self::get_email_header();
        $message .= '<h2>Nouveau document soumis</h2>';
        $message .= '<p>Un nouveau document a ete soumis et necessite votre validation.</p>';
        $message .= '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Partenaire</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $vendor->display_name ) . ' (' . esc_html( $vendor->user_email ) . ')</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Type de document</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document_type->name ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Fichier</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document->original_filename ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Date</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . date_i18n( 'j F Y a H:i', strtotime( $document->uploaded_at ) ) . '</td></tr>';
        $message .= '</table>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $admin_url ) . '" style="background:#FF6B35;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Valider le document</a>';
        $message .= '</p>';
        $message .= self::get_email_footer();

        return self::send_email( $admin_email, $subject, $message );
    }

    /**
     * Envoie une notification quand un document est approuve (vers vendor)
     *
     * @param int $document_id ID du document
     * @return bool
     */
    public static function notify_document_approved( $document_id ) {
        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            return false;
        }

        $vendor = get_userdata( $document->vendor_id );
        $document_type = EL_Document_Types::get( $document->document_type_id );

        if ( ! $vendor || ! $document_type ) {
            return false;
        }

        $site_name = get_bloginfo( 'name' );

        $subject = sprintf(
            '[%s] Votre document "%s" a ete approuve',
            $site_name,
            $document_type->name
        );

        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $message = self::get_email_header();
        $message .= '<h2>Document approuve</h2>';
        $message .= '<p>Bonjour ' . esc_html( $vendor->display_name ) . ',</p>';
        $message .= '<p>Nous avons le plaisir de vous informer que votre document a ete approuve.</p>';
        $message .= '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Type de document</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document_type->name ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Fichier</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document->original_filename ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Statut</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;color:#28a745;"><strong>Approuve</strong></td></tr>';
        $message .= '</table>';

        // Verifier si tous les documents requis sont maintenant approuves
        if ( EL_Vendor_Documents::vendor_has_all_required_approved( $document->vendor_id ) ) {
            $message .= '<div style="background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;margin:20px 0;">';
            $message .= '<p style="color:#155724;margin:0;"><strong>Felicitations !</strong> Tous vos documents requis ont ete valides. Vous pouvez maintenant creer et publier vos activites.</p>';
            $message .= '</div>';
        }

        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $profile_url ) . '" style="background:#FF6B35;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Voir mes documents</a>';
        $message .= '</p>';
        $message .= self::get_email_footer();

        return self::send_email( $vendor->user_email, $subject, $message );
    }

    /**
     * Envoie une notification quand un document est rejete (vers vendor)
     *
     * @param int $document_id ID du document
     * @param string $reason Motif du rejet
     * @return bool
     */
    public static function notify_document_rejected( $document_id, $reason = '' ) {
        $document = EL_Vendor_Documents::get( $document_id );

        if ( ! $document ) {
            return false;
        }

        $vendor = get_userdata( $document->vendor_id );
        $document_type = EL_Document_Types::get( $document->document_type_id );

        if ( ! $vendor || ! $document_type ) {
            return false;
        }

        $site_name = get_bloginfo( 'name' );

        $subject = sprintf(
            '[%s] Action requise - Votre document "%s" necessite des modifications',
            $site_name,
            $document_type->name
        );

        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $message = self::get_email_header();
        $message .= '<h2>Document a modifier</h2>';
        $message .= '<p>Bonjour ' . esc_html( $vendor->display_name ) . ',</p>';
        $message .= '<p>Nous avons examine votre document et celui-ci necessite des modifications avant de pouvoir etre approuve.</p>';
        $message .= '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Type de document</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document_type->name ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Fichier</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document->original_filename ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Statut</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;color:#dc3545;"><strong>A modifier</strong></td></tr>';
        $message .= '</table>';

        if ( ! empty( $reason ) ) {
            $message .= '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:15px;border-radius:5px;margin:20px 0;">';
            $message .= '<p style="color:#721c24;margin:0;"><strong>Motif :</strong></p>';
            $message .= '<p style="color:#721c24;margin:10px 0 0 0;">' . nl2br( esc_html( $reason ) ) . '</p>';
            $message .= '</div>';
        }

        $message .= '<p>Merci de soumettre un nouveau document conforme aux exigences.</p>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $profile_url ) . '" style="background:#FF6B35;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Soumettre un nouveau document</a>';
        $message .= '</p>';
        $message .= self::get_email_footer();

        return self::send_email( $vendor->user_email, $subject, $message );
    }

    /**
     * Envoie un rappel pour les documents manquants
     *
     * @param int $vendor_id ID du vendor
     * @return bool
     */
    public static function send_missing_documents_reminder( $vendor_id ) {
        $vendor = get_userdata( $vendor_id );

        if ( ! $vendor ) {
            return false;
        }

        $missing = EL_Vendor_Documents::get_missing_required_documents( $vendor_id );

        if ( empty( $missing ) ) {
            return false; // Pas de documents manquants
        }

        $site_name = get_bloginfo( 'name' );

        $subject = sprintf(
            '[%s] Rappel - Documents requis en attente',
            $site_name
        );

        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $message = self::get_email_header();
        $message .= '<h2>Documents requis en attente</h2>';
        $message .= '<p>Bonjour ' . esc_html( $vendor->display_name ) . ',</p>';
        $message .= '<p>Certains documents requis sont manquants ou en attente de validation sur votre compte partenaire.</p>';
        $message .= '<p><strong>Sans ces documents valides, vous ne pourrez pas creer ou publier d\'activites.</strong></p>';

        $message .= '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
        $message .= '<tr style="background:#f9f9f9;"><th style="padding:10px;border:1px solid #ddd;text-align:left;">Document</th><th style="padding:10px;border:1px solid #ddd;text-align:left;">Statut</th></tr>';

        foreach ( $missing as $doc_type ) {
            $status_color = '#6c757d';
            $status_text = 'Non soumis';

            if ( $doc_type->doc_status === 'pending' ) {
                $status_color = '#ffc107';
                $status_text = 'En attente de validation';
            } elseif ( $doc_type->doc_status === 'rejected' ) {
                $status_color = '#dc3545';
                $status_text = 'A modifier';
            }

            $message .= '<tr>';
            $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $doc_type->name ) . '</td>';
            $message .= '<td style="padding:10px;border:1px solid #ddd;color:' . $status_color . ';">' . $status_text . '</td>';
            $message .= '</tr>';
        }

        $message .= '</table>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $profile_url ) . '" style="background:#FF6B35;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Completer mes documents</a>';
        $message .= '</p>';
        $message .= self::get_email_footer();

        return self::send_email( $vendor->user_email, $subject, $message );
    }

    /**
     * Header commun des emails
     *
     * @return string
     */
    private static function get_email_header() {
        $site_name = get_bloginfo( 'name' );
        $logo_url = ''; // A personnaliser si logo disponible

        $header = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">';
        $header .= '<div style="background:#2c3e50;padding:20px;text-align:center;border-radius:5px 5px 0 0;">';

        if ( $logo_url ) {
            $header .= '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $site_name ) . '" style="max-height:50px;">';
        } else {
            $header .= '<h1 style="color:#fff;margin:0;font-size:24px;">' . esc_html( $site_name ) . '</h1>';
        }

        $header .= '</div>';
        $header .= '<div style="background:#fff;padding:30px;border:1px solid #ddd;border-top:none;">';

        return $header;
    }

    /**
     * Footer commun des emails
     *
     * @return string
     */
    private static function get_email_footer() {
        $site_name = get_bloginfo( 'name' );
        $site_url = home_url();

        $footer = '</div>';
        $footer .= '<div style="background:#f8f9fa;padding:20px;text-align:center;border:1px solid #ddd;border-top:none;border-radius:0 0 5px 5px;">';
        $footer .= '<p style="margin:0;color:#6c757d;font-size:12px;">';
        $footer .= 'Cet email a ete envoye automatiquement par <a href="' . esc_url( $site_url ) . '" style="color:#FF6B35;">' . esc_html( $site_name ) . '</a><br>';
        $footer .= 'Merci de ne pas repondre directement a cet email.';
        $footer .= '</p>';
        $footer .= '</div>';
        $footer .= '</body></html>';

        return $footer;
    }

    /**
     * Envoie un email HTML
     *
     * @param string $to Destinataire
     * @param string $subject Sujet
     * @param string $message Corps du message
     * @return bool
     */
    private static function send_email( $to, $subject, $message ) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        return wp_mail( $to, $subject, $message, $headers );
    }
}
