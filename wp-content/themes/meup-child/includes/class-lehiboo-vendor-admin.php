<?php
/**
 * Interface Admin - Gestion des demandes partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LeHiboo_Vendor_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_lehiboo_approve_vendor', array( $this, 'ajax_approve_vendor' ) );
		add_action( 'wp_ajax_lehiboo_reject_vendor', array( $this, 'ajax_reject_vendor' ) );
	}

	/**
	 * Ajouter le menu admin
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Demandes Partenaires',
			'Demandes Partenaires',
			'manage_options',
			'lehiboo-vendor-applications',
			array( $this, 'render_applications_page' ),
			'dashicons-businessman',
			30
		);

		add_submenu_page(
			'lehiboo-vendor-applications',
			'Toutes les demandes',
			'Toutes les demandes',
			'manage_options',
			'lehiboo-vendor-applications',
			array( $this, 'render_applications_page' )
		);

		add_submenu_page(
			'lehiboo-vendor-applications',
			'Statistiques',
			'Statistiques',
			'manage_options',
			'lehiboo-vendor-stats',
			array( $this, 'render_stats_page' )
		);
	}

	/**
	 * Charger les assets admin
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'lehiboo-vendor' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'lehiboo-vendor-admin',
			get_stylesheet_directory_uri() . '/assets/css/vendor-admin.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'lehiboo-vendor-admin',
			get_stylesheet_directory_uri() . '/assets/js/vendor-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'lehiboo-vendor-admin', 'lehiboo_vendor_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'lehiboo_vendor_admin_nonce' )
		) );
	}

	/**
	 * Page des demandes
	 */
	public function render_applications_page() {
		// Pagination
		$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page = 20;

		// Filtres
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

		// Query
		$args = array(
			'role' => 'el_event_vendor',
			'number' => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
			'orderby' => 'registered',
			'order' => 'DESC'
		);

		if ( $status_filter !== 'all' ) {
			$args['meta_query'] = array(
				array(
					'key' => 'vendor_status',
					'value' => $status_filter,
					'compare' => '='
				)
			);
		}

		$user_query = new WP_User_Query( $args );
		$vendors = $user_query->get_results();
		$total = $user_query->get_total();

		// Stats
		$stats = $this->get_stats();

		include get_stylesheet_directory() . '/templates/admin/vendor-applications-list.php';
	}

	/**
	 * Page des statistiques
	 */
	public function render_stats_page() {
		$stats = $this->get_stats();
		include get_stylesheet_directory() . '/templates/admin/vendor-stats.php';
	}

	/**
	 * Obtenir les statistiques
	 */
	public function get_stats() {
		$all_vendors = new WP_User_Query( array( 'role' => 'el_event_vendor' ) );

		$pending = new WP_User_Query( array(
			'role' => 'el_event_vendor',
			'meta_query' => array(
				array( 'key' => 'vendor_status', 'value' => 'pending_approval' )
			)
		) );

		$approved = new WP_User_Query( array(
			'role' => 'el_event_vendor',
			'meta_query' => array(
				array( 'key' => 'vendor_status', 'value' => 'approved' )
			)
		) );

		$rejected = new WP_User_Query( array(
			'role' => 'el_event_vendor',
			'meta_query' => array(
				array( 'key' => 'vendor_status', 'value' => 'rejected' )
			)
		) );

		return array(
			'total' => $all_vendors->get_total(),
			'pending' => $pending->get_total(),
			'approved' => $approved->get_total(),
			'rejected' => $rejected->get_total()
		);
	}

	/**
	 * AJAX - Approuver un partenaire
	 */
	public function ajax_approve_vendor() {
		check_ajax_referer( 'lehiboo_vendor_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission refusée.' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'ID utilisateur manquant.' ) );
		}

		// Mettre à jour le statut
		update_user_meta( $user_id, 'vendor_status', 'approved' );
		update_user_meta( $user_id, 'vendor_approved_date', current_time( 'mysql' ) );
		update_user_meta( $user_id, 'vendor_approved_by', get_current_user_id() );

		// Email de notification
		$user = get_userdata( $user_id );
		$org_name = get_user_meta( $user_id, 'org_display_name', true );

		$subject = '[Le Hiboo] Votre compte partenaire a été approuvé !';
		$message = "Bonjour,\n\n";
		$message .= "Bonne nouvelle ! Votre demande de partenariat pour {$org_name} a été approuvée.\n\n";
		$message .= "Vous pouvez dès maintenant vous connecter et publier vos premières activités.\n\n";
		$message .= "Lien de connexion : " . home_url( '/member-account/' ) . "\n\n";
		$message .= "Cordialement,\nL'équipe Le Hiboo";

		wp_mail( $user->user_email, $subject, $message );

		wp_send_json_success( array(
			'message' => 'Partenaire approuvé avec succès ! Un email a été envoyé.'
		) );
	}

	/**
	 * AJAX - Rejeter un partenaire
	 */
	public function ajax_reject_vendor() {
		check_ajax_referer( 'lehiboo_vendor_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission refusée.' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'ID utilisateur manquant.' ) );
		}

		// Mettre à jour le statut
		update_user_meta( $user_id, 'vendor_status', 'rejected' );
		update_user_meta( $user_id, 'vendor_rejected_date', current_time( 'mysql' ) );
		update_user_meta( $user_id, 'vendor_rejected_by', get_current_user_id() );
		update_user_meta( $user_id, 'vendor_rejection_reason', $reason );

		// Email de notification
		$user = get_userdata( $user_id );
		$org_name = get_user_meta( $user_id, 'org_display_name', true );

		$subject = '[Le Hiboo] Votre demande de partenariat';
		$message = "Bonjour,\n\n";
		$message .= "Nous avons examiné votre demande de partenariat pour {$org_name}.\n\n";
		$message .= "Malheureusement, nous ne pouvons pas donner suite à votre demande pour le moment.\n\n";

		if ( $reason ) {
			$message .= "Motif : {$reason}\n\n";
		}

		$message .= "N'hésitez pas à nous contacter pour plus d'informations.\n\n";
		$message .= "Cordialement,\nL'équipe Le Hiboo";

		wp_mail( $user->user_email, $subject, $message );

		wp_send_json_success( array(
			'message' => 'Demande rejetée. Un email a été envoyé.'
		) );
	}
}

// Initialiser
new LeHiboo_Vendor_Admin();
