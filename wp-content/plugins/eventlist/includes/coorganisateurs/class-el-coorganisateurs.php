<?php
/**
 * Class EL_Coorganisateurs
 *
 * Point d'entrée principal pour le module co-organisateurs
 * Charge tous les fichiers nécessaires et initialise les fonctionnalités
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EL_Coorganisateurs {

    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Récupère l'instance unique
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur
     */
    private function __construct() {
        $this->includes();
        $this->init();
    }

    /**
     * Inclut les fichiers nécessaires
     */
    private function includes() {
        $base_path = plugin_dir_path( __FILE__ );

        // Classes de base de données
        require_once $base_path . 'class-el-coorg-database.php';
        require_once $base_path . 'class-el-partnership.php';
        require_once $base_path . 'class-el-event-coorganisation.php';

        // Handlers AJAX
        require_once $base_path . 'class-el-coorg-ajax.php';

        // Notifications
        require_once $base_path . 'class-el-coorg-notifications.php';

        // Helpers
        require_once $base_path . 'class-el-coorg-helpers.php';
    }

    /**
     * Initialise les composants
     */
    private function init() {
        // Hook pour supprimer les co-orgs quand un événement est supprimé
        add_action( 'before_delete_post', array( $this, 'delete_event_coorganisations' ) );

        // Hook pour lier un email invité à une organisation lors de l'inscription
        add_action( 'user_register', array( $this, 'link_pending_partnerships' ), 10, 1 );

        // Enqueue assets (CSS & JS)
        add_action( 'wp_enqueue_scripts', array( 'EL_Coorg_Helpers', 'enqueue_assets' ) );
        add_action( 'admin_enqueue_scripts', array( 'EL_Coorg_Helpers', 'enqueue_assets' ) );
    }

    /**
     * Supprime les co-organisations quand un événement est supprimé
     */
    public function delete_event_coorganisations( $post_id ) {
        if ( get_post_type( $post_id ) === 'event' ) {
            EL_Event_Coorganisation::delete_for_event( $post_id );
        }
    }

    /**
     * Lie les partenariats en attente quand une organisation s'inscrit
     */
    public function link_pending_partnerships( $user_id ) {
        $user = get_userdata( $user_id );

        if ( ! $user || ! in_array( 'el_event_vendor', $user->roles ) ) {
            return;
        }

        $email = $user->user_email;

        // Lier les invitations de partenariat en attente
        EL_Partnership::link_email_to_organisation( $email, $user_id );
    }
}

// Initialiser le module
function el_coorganisateurs() {
    return EL_Coorganisateurs::get_instance();
}

el_coorganisateurs();
