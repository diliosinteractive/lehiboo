<?php
/**
 * Class EL_Coorg_Helpers
 *
 * Fonctions utilitaires pour le module co-organisateurs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Coorg_Helpers {

    /**
     * Récupère le nom d'affichage d'une organisation
     *
     * @param int $user_id ID de l'utilisateur (organisation)
     * @return string
     */
    public static function get_organisation_name( $user_id ) {
        $org_display_name = get_user_meta( $user_id, 'org_display_name', true );
        $org_name = get_user_meta( $user_id, 'org_name', true );

        return ! empty( $org_display_name ) ? $org_display_name : ( ! empty( $org_name ) ? $org_name : __( 'Organisation', 'eventlist' ) );
    }

    /**
     * Récupère les données complètes d'une organisation
     *
     * @param int $user_id ID de l'utilisateur (organisation)
     * @return array
     */
    public static function get_organisation_data( $user_id ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return array();
        }

        return array(
            'id' => $user_id,
            'name' => self::get_organisation_name( $user_id ),
            'email' => $user->user_email,
            'logo' => get_user_meta( $user_id, 'org_cover_image', true ),
            'address' => get_user_meta( $user_id, 'user_address_line1', true ),
            'city' => get_user_meta( $user_id, 'user_city', true ),
            'postcode' => get_user_meta( $user_id, 'user_postcode', true ),
            'phone' => get_user_meta( $user_id, 'org_phone_contact', true ),
            'web' => get_user_meta( $user_id, 'org_web', true ),
        );
    }

    /**
     * Récupère le badge de statut pour l'affichage
     *
     * @param string $statut Statut (en_cours, acceptee, refusee, retiree)
     * @return string HTML du badge
     */
    public static function get_status_badge( $statut ) {
        $badges = array(
            'en_cours' => '<span class="el_coorg_badge el_coorg_badge_pending">' . __( 'En cours', 'eventlist' ) . '</span>',
            'acceptee' => '<span class="el_coorg_badge el_coorg_badge_accepted">' . __( 'Acceptée', 'eventlist' ) . '</span>',
            'refusee' => '<span class="el_coorg_badge el_coorg_badge_refused">' . __( 'Refusée', 'eventlist' ) . '</span>',
            'retiree' => '<span class="el_coorg_badge el_coorg_badge_retired">' . __( 'Clôturée', 'eventlist' ) . '</span>',
        );

        return isset( $badges[ $statut ] ) ? $badges[ $statut ] : '';
    }

    /**
     * Vérifie si deux organisations ont un partenariat accepté
     *
     * @param int $org_id_1
     * @param int $org_id_2
     * @return bool
     */
    public static function has_accepted_partnership( $org_id_1, $org_id_2 ) {
        $partnership = EL_Partnership::get_between_orgs( $org_id_1, $org_id_2 );
        return $partnership && $partnership->statut === EL_Partnership::STATUS_ACCEPTEE;
    }

    /**
     * Vérifie si une organisation est co-organisatrice d'un événement (acceptée)
     *
     * @param int $event_id
     * @param int $org_id
     * @return bool
     */
    public static function is_accepted_coorganiser( $event_id, $org_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'el_event_coorganisations';

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table
            WHERE event_id = %d
              AND organisation_coorganisatrice_id = %d
              AND statut = %s",
            $event_id, $org_id, EL_Event_Coorganisation::STATUS_ACCEPTEE
        ) );

        return $count > 0;
    }

    /**
     * Récupère l'ID de l'organisation pour l'utilisateur courant
     *
     * @return int|false
     */
    public static function get_current_organisation_id() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user = wp_get_current_user();

        if ( ! in_array( 'el_event_vendor', $user->roles ) ) {
            return false;
        }

        return $user->ID;
    }

    /**
     * Formate une date pour l'affichage
     *
     * @param string $date Date au format MySQL
     * @return string
     */
    public static function format_date( $date ) {
        if ( empty( $date ) ) {
            return '';
        }

        return date_i18n( get_option( 'date_format' ), strtotime( $date ) );
    }

    /**
     * Récupère le rôle traduit
     *
     * @param string $role
     * @return string
     */
    public static function get_role_label( $role ) {
        $roles = array(
            'co-organisateur' => __( 'Co-organisateur', 'eventlist' ),
            'partenaire' => __( 'Partenaire', 'eventlist' ),
            'sponsor' => __( 'Sponsor', 'eventlist' ),
        );

        return isset( $roles[ $role ] ) ? $roles[ $role ] : $role;
    }

    /**
     * Enqueue les scripts et styles du module
     */
    public static function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'el-coorganisateurs',
            plugin_dir_url( __FILE__ ) . 'assets/css/coorganisateurs.css',
            array(),
            '1.0.0'
        );

        // JS
        wp_enqueue_script(
            'el-coorganisateurs',
            plugin_dir_url( __FILE__ ) . 'assets/js/coorganisateurs.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        // Localisation
        wp_localize_script( 'el-coorganisateurs', 'el_coorg_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'el_coorg_nonce' ),
            'i18n' => array(
                'confirm_delete' => __( 'Êtes-vous sûr de vouloir retirer ce partenariat ?', 'eventlist' ),
                'confirm_remove_coorg' => __( 'Êtes-vous sûr de vouloir retirer ce co-organisateur ?', 'eventlist' ),
                'error' => __( 'Une erreur s\'est produite', 'eventlist' ),
                'loading' => __( 'Chargement...', 'eventlist' ),
            ),
        ) );
    }
}
