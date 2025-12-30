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
     * Envoie une notification quand un document est uploade (vers support + organisateur)
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

        // Recuperer le nom de l'organisation
        $org_name = get_user_meta( $document->vendor_id, 'org_display_name', true );
        if ( empty( $org_name ) ) {
            $org_name = get_user_meta( $document->vendor_id, 'org_name', true );
        }
        if ( empty( $org_name ) ) {
            $org_name = $vendor->display_name;
        }

        // 1. Email a support@lehiboo.com
        self::send_admin_document_notification( $document, $vendor, $document_type, $org_name );

        // 2. Email a l'organisateur (confirmation reception)
        self::send_vendor_document_received( $document, $vendor, $document_type );

        return true;
    }

    /**
     * Envoie l'email de notification admin (document a verifier)
     */
    private static function send_admin_document_notification( $document, $vendor, $document_type, $org_name ) {
        $support_email = 'support@lehiboo.com';
        $admin_url = admin_url( 'admin.php?page=el_vendor_documents&status=pending' );

        $subject = sprintf(
            'Document a verifier – %s | %s',
            $document_type->name,
            $org_name
        );

        $message = self::get_email_header();
        $message .= '<p>Bonjour,</p>';
        $message .= '<p>Un nouveau document est en attente de verification sur Le Hiboo.</p>';
        $message .= '<h3 style="color:#FF6600;margin-top:25px;">Details</h3>';
        $message .= '<table style="width:100%;border-collapse:collapse;margin:15px 0;">';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;width:200px;"><strong>Organisation</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $org_name ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Type de document</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . esc_html( $document_type->name ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Date d\'import</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;">' . date_i18n( 'j F Y a H:i', strtotime( $document->uploaded_at ) ) . '</td></tr>';
        $message .= '<tr><td style="padding:10px;border:1px solid #ddd;background:#f9f9f9;"><strong>Statut actuel</strong></td>';
        $message .= '<td style="padding:10px;border:1px solid #ddd;color:#ffc107;"><strong>En attente de validation</strong></td></tr>';
        $message .= '</table>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $admin_url ) . '" style="background:#FF6600;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Acceder a l\'espace de verification</a>';
        $message .= '</p>';
        $message .= '<p style="color:#666;">Merci de proceder a la verification afin de permettre a l\'organisateur de publier ses evenements.</p>';
        $message .= self::get_email_footer( true );

        return self::send_email( $support_email, $subject, $message );
    }

    /**
     * Envoie l'email de confirmation de reception a l'organisateur
     */
    private static function send_vendor_document_received( $document, $vendor, $document_type ) {
        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $subject = 'Le Hiboo - Document recu - Validation en cours';

        $message = self::get_email_header();
        $message .= '<p>Bonjour,</p>';
        $message .= '<p>Nous avons bien recu le document suivant dans votre espace organisateur Le Hiboo :</p>';
        $message .= '<div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0;text-align:center;">';
        $message .= '<strong style="font-size:16px;">' . esc_html( $document_type->name ) . '</strong>';
        $message .= '</div>';
        $message .= '<p>Il est actuellement <strong>en cours de verification</strong> par notre equipe.</p>';
        $message .= '<p>Vous pouvez suivre l\'etat de vos documents a tout moment depuis votre espace organisateur.</p>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $profile_url ) . '" style="background:#FF6600;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Acceder a mon espace organisateur</a>';
        $message .= '</p>';
        $message .= '<p style="color:#666;font-size:13px;">Si vous avez la moindre question ou un doute sur les documents a fournir, n\'hesitez pas a nous contacter : <a href="mailto:support@lehiboo.com" style="color:#FF6600;">support@lehiboo.com</a></p>';
        $message .= self::get_email_footer();

        return self::send_email( $vendor->user_email, $subject, $message );
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

        $subject = 'Le Hiboo - Document valide 🎉';

        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $message = self::get_email_header();
        $message .= '<p>Bonjour,</p>';
        $message .= '<p>Bonne nouvelle 🎉</p>';
        $message .= '<p>Le document suivant a ete valide par l\'equipe Le Hiboo :</p>';
        $message .= '<div style="background:#d4edda;padding:15px;border-radius:5px;margin:20px 0;text-align:center;border:1px solid #c3e6cb;">';
        $message .= '<strong style="font-size:16px;color:#155724;">' . esc_html( $document_type->name ) . '</strong>';
        $message .= '</div>';

        // Verifier si tous les documents requis sont maintenant approuves
        $all_approved = EL_Vendor_Documents::vendor_has_all_required_approved( $document->vendor_id );

        $message .= '<h3 style="color:#FF6600;margin-top:25px;">Et maintenant ?</h3>';

        if ( $all_approved ) {
            $message .= '<p><strong>Tous les documents requis pour votre compte sont valides</strong>, la creation et la publication de vos evenements et activites sont desormais activees.</p>';
        } else {
            $message .= '<p>D\'autres documents sont encore en attente de validation. Une fois tous vos documents valides, vous pourrez creer et publier vos evenements.</p>';
        }

        $message .= '<p style="color:#666;font-size:13px;margin-top:25px;">Si vous avez la moindre question ou un doute sur les documents a fournir, n\'hesitez pas a nous contacter : <a href="mailto:support@lehiboo.com" style="color:#FF6600;">support@lehiboo.com</a></p>';
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

        $subject = 'Le Hiboo - Document a corriger';

        $profile_url = function_exists( 'get_myaccount_page' )
            ? add_query_arg( 'vendor', 'documents', get_myaccount_page() )
            : home_url();

        $message = self::get_email_header();
        $message .= '<p>Bonjour,</p>';
        $message .= '<p>Nous avons bien examine le document suivant dans votre espace organisateur Le Hiboo :</p>';
        $message .= '<div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0;text-align:center;">';
        $message .= '<strong style="font-size:16px;">' . esc_html( $document_type->name ) . '</strong>';
        $message .= '</div>';
        $message .= '<p>Apres verification, ce document <strong>n\'a pas pu etre valide</strong>.</p>';

        if ( ! empty( $reason ) ) {
            $message .= '<div style="background:#f8d7da;border:1px solid #f5c6cb;padding:15px;border-radius:5px;margin:20px 0;">';
            $message .= '<p style="color:#721c24;margin:0;"><strong>Motif :</strong></p>';
            $message .= '<p style="color:#721c24;margin:10px 0 0 0;">' . nl2br( esc_html( $reason ) ) . '</p>';
            $message .= '</div>';
        }

        $message .= '<p>Merci de mettre a jour ou remplacer le document concerne depuis votre espace organisateur.</p>';
        $message .= '<p>Des qu\'un nouveau document est importe, notre equipe procedera a une nouvelle verification.</p>';
        $message .= '<p style="text-align:center;margin:30px 0;">';
        $message .= '<a href="' . esc_url( $profile_url ) . '" style="background:#FF6600;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;">Acces a vos documents</a>';
        $message .= '</p>';
        $message .= '<p style="color:#666;font-size:13px;">Si vous avez la moindre question ou un doute sur les documents a fournir, n\'hesitez pas a nous contacter : <a href="mailto:support@lehiboo.com" style="color:#FF6600;">support@lehiboo.com</a></p>';
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
        $header = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">';
        $header .= '<div style="background:linear-gradient(135deg, #FF6600 0%, #FF8533 100%);padding:25px;text-align:center;border-radius:8px 8px 0 0;">';
        $header .= '<h1 style="color:#fff;margin:0;font-size:28px;font-weight:bold;">Le Hiboo</h1>';
        $header .= '</div>';
        $header .= '<div style="background:#fff;padding:30px;border:1px solid #e0e0e0;border-top:none;">';

        return $header;
    }

    /**
     * Footer commun des emails
     *
     * @param bool $is_admin_notification Si c'est une notification admin
     * @return string
     */
    private static function get_email_footer( $is_admin_notification = false ) {
        $footer = '';

        if ( ! $is_admin_notification ) {
            $footer .= '<div style="margin-top:30px;padding-top:20px;border-top:1px solid #eee;">';
            $footer .= '<p style="margin:0 0 5px 0;">A tres bientot sur Le Hiboo,</p>';
            $footer .= '<p style="margin:0;font-weight:bold;color:#FF6600;">L\'equipe Le Hiboo</p>';
            $footer .= '</div>';
        }

        $footer .= '</div>';
        $footer .= '<div style="background:#f8f9fa;padding:15px;text-align:center;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;">';
        $footer .= '<p style="margin:0;color:#999;font-size:11px;">';

        if ( $is_admin_notification ) {
            $footer .= 'Notification automatique – Le Hiboo';
        } else {
            $footer .= 'Cet email est envoye automatiquement.<br>Merci de ne pas repondre a cette adresse.';
        }

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
            'From: Lehiboo Experiences <no-reply@lehiboo.com>',
        );

        return wp_mail( $to, $subject, $message, $headers );
    }
}
