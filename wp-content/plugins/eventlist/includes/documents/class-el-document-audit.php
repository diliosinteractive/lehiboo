<?php
/**
 * Class EL_Document_Audit
 *
 * Journalisation des actions sur les documents
 * V1 Le Hiboo - Gestion des documents securises
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Document_Audit {

    /**
     * Nom de la table
     */
    private static $table_name = 'el_document_audit_log';

    /**
     * Actions possibles
     */
    const ACTION_UPLOADED = 'uploaded';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_DOWNLOADED = 'downloaded';
    const ACTION_DELETED = 'deleted';
    const ACTION_REPLACED = 'replaced';
    const ACTION_VIEWED = 'viewed';

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
     * Enregistre une action dans le journal
     *
     * @param int $document_id ID du document
     * @param string $action Type d'action
     * @param string|null $details Details supplementaires
     * @param int|null $user_id ID de l'utilisateur (defaut: utilisateur courant)
     * @return int|false ID de l'entree ou false
     */
    public static function log( $document_id, $action, $details = null, $user_id = null ) {
        global $wpdb;

        $document_id = absint( $document_id );
        if ( ! $document_id ) {
            return false;
        }

        // Valider l'action
        $valid_actions = array(
            self::ACTION_UPLOADED,
            self::ACTION_APPROVED,
            self::ACTION_REJECTED,
            self::ACTION_DOWNLOADED,
            self::ACTION_DELETED,
            self::ACTION_REPLACED,
            self::ACTION_VIEWED,
        );

        if ( ! in_array( $action, $valid_actions ) ) {
            return false;
        }

        $data = array(
            'document_id' => $document_id,
            'action' => $action,
            'performed_by' => $user_id ? absint( $user_id ) : get_current_user_id(),
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) ) : '',
            'details' => $details ? sanitize_textarea_field( $details ) : null,
            'created_at' => current_time( 'mysql' ),
        );

        $result = $wpdb->insert( self::get_table(), $data );

        if ( $result === false ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Recupere l'adresse IP du client
     *
     * @return string
     */
    private static function get_client_ip() {
        $ip = '';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // Prendre la premiere IP de la liste
            $ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $ips[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Valider l'IP
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return sanitize_text_field( $ip );
        }

        return '';
    }

    /**
     * Recupere l'historique d'un document
     *
     * @param int $document_id ID du document
     * @param int $limit Limite
     * @return array
     */
    public static function get_document_history( $document_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, u.display_name as user_name
             FROM " . self::get_table() . " a
             LEFT JOIN " . $wpdb->prefix . "users u ON a.performed_by = u.ID
             WHERE a.document_id = %d
             ORDER BY a.created_at DESC",
            absint( $document_id )
        ) );
    }

    /**
     * Recupere l'activite d'un utilisateur
     *
     * @param int $user_id ID de l'utilisateur
     * @param int $limit Limite
     * @return array
     */
    public static function get_user_activity( $user_id, $limit = 50 ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, d.original_filename, t.name as type_name
             FROM " . self::get_table() . " a
             LEFT JOIN " . $wpdb->prefix . "el_vendor_documents d ON a.document_id = d.id
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             WHERE a.performed_by = %d
             ORDER BY a.created_at DESC
             LIMIT %d",
            absint( $user_id ),
            absint( $limit )
        ) );
    }

    /**
     * Recupere l'activite recente
     *
     * @param int $limit Limite
     * @return array
     */
    public static function get_recent_activity( $limit = 100 ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, u.display_name as user_name, d.original_filename, d.vendor_id,
                    v.display_name as vendor_name, t.name as type_name
             FROM " . self::get_table() . " a
             LEFT JOIN " . $wpdb->prefix . "users u ON a.performed_by = u.ID
             LEFT JOIN " . $wpdb->prefix . "el_vendor_documents d ON a.document_id = d.id
             LEFT JOIN " . $wpdb->prefix . "users v ON d.vendor_id = v.ID
             LEFT JOIN " . $wpdb->prefix . "el_document_types t ON d.document_type_id = t.id
             ORDER BY a.created_at DESC
             LIMIT %d",
            absint( $limit )
        ) );
    }

    /**
     * Recupere les statistiques d'activite
     *
     * @param string $period Periode (day, week, month)
     * @return array
     */
    public static function get_activity_stats( $period = 'week' ) {
        global $wpdb;

        switch ( $period ) {
            case 'day':
                $date_from = date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) );
                break;
            case 'month':
                $date_from = date( 'Y-m-d 00:00:00', strtotime( '-30 days' ) );
                break;
            case 'week':
            default:
                $date_from = date( 'Y-m-d 00:00:00', strtotime( '-7 days' ) );
                break;
        }

        $stats = $wpdb->get_results( $wpdb->prepare(
            "SELECT action, COUNT(*) as count
             FROM " . self::get_table() . "
             WHERE created_at >= %s
             GROUP BY action",
            $date_from
        ), OBJECT_K );

        return array(
            'uploaded' => isset( $stats['uploaded'] ) ? $stats['uploaded']->count : 0,
            'approved' => isset( $stats['approved'] ) ? $stats['approved']->count : 0,
            'rejected' => isset( $stats['rejected'] ) ? $stats['rejected']->count : 0,
            'downloaded' => isset( $stats['downloaded'] ) ? $stats['downloaded']->count : 0,
            'deleted' => isset( $stats['deleted'] ) ? $stats['deleted']->count : 0,
        );
    }

    /**
     * Nettoie les anciennes entrees du journal
     *
     * @param int $days_to_keep Nombre de jours a conserver
     * @return int Nombre d'entrees supprimees
     */
    public static function cleanup( $days_to_keep = 365 ) {
        global $wpdb;

        $date_limit = date( 'Y-m-d H:i:s', strtotime( '-' . absint( $days_to_keep ) . ' days' ) );

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM " . self::get_table() . " WHERE created_at < %s",
            $date_limit
        ) );
    }

    /**
     * Formate une action pour l'affichage
     *
     * @param string $action Action
     * @return string Label traduit
     */
    public static function get_action_label( $action ) {
        $labels = array(
            self::ACTION_UPLOADED => __( 'Document uploade', 'eventlist' ),
            self::ACTION_APPROVED => __( 'Document approuve', 'eventlist' ),
            self::ACTION_REJECTED => __( 'Document rejete', 'eventlist' ),
            self::ACTION_DOWNLOADED => __( 'Document telecharge', 'eventlist' ),
            self::ACTION_DELETED => __( 'Document supprime', 'eventlist' ),
            self::ACTION_REPLACED => __( 'Document remplace', 'eventlist' ),
            self::ACTION_VIEWED => __( 'Document consulte', 'eventlist' ),
        );

        return isset( $labels[ $action ] ) ? $labels[ $action ] : $action;
    }

    /**
     * Recupere la classe CSS pour une action
     *
     * @param string $action Action
     * @return string Classe CSS
     */
    public static function get_action_class( $action ) {
        $classes = array(
            self::ACTION_UPLOADED => 'action-upload',
            self::ACTION_APPROVED => 'action-success',
            self::ACTION_REJECTED => 'action-danger',
            self::ACTION_DOWNLOADED => 'action-info',
            self::ACTION_DELETED => 'action-warning',
            self::ACTION_REPLACED => 'action-info',
            self::ACTION_VIEWED => 'action-default',
        );

        return isset( $classes[ $action ] ) ? $classes[ $action ] : 'action-default';
    }
}
