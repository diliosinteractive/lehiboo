<?php
/**
 * Le Hiboo - Vendor Onboarding
 * Système d'onboarding pour les nouveaux partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LeHiboo_Vendor_Onboarding {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Meta key pour l'état d'onboarding
	 */
	const ONBOARDING_META = 'lehiboo_onboarding_completed';

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Rediriger vers onboarding au premier login
		add_action( 'wp_login', array( $this, 'redirect_to_onboarding_on_first_login' ), 10, 2 );

		// Ajouter la page onboarding au menu vendor
		add_filter( 'el_vendor_pages', array( $this, 'add_onboarding_page' ) );

		// AJAX handlers
		add_action( 'wp_ajax_lehiboo_complete_onboarding', array( $this, 'ajax_complete_onboarding' ) );
		add_action( 'wp_ajax_lehiboo_skip_onboarding', array( $this, 'ajax_skip_onboarding' ) );

		// Scripts/styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_onboarding_assets' ) );
	}

	/**
	 * Rediriger vers l'onboarding au premier login
	 */
	public function redirect_to_onboarding_on_first_login( $user_login, $user ) {
		// Vérifier si c'est un vendor
		if ( ! in_array( 'el_event_manager', $user->roles ) ) {
			return;
		}

		// Vérifier si l'onboarding a déjà été fait
		$onboarding_completed = get_user_meta( $user->ID, self::ONBOARDING_META, true );

		if ( ! $onboarding_completed ) {
			// Marquer comme nouveau vendor (pour la redirection après login)
			set_transient( 'lehiboo_new_vendor_' . $user->ID, true, 60 );
		}
	}

	/**
	 * Obtenir le statut d'onboarding d'un vendor
	 */
	public static function get_onboarding_status( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$status = array(
			'profile_complete' => self::is_profile_complete( $user_id ),
			'documents_uploaded' => self::are_documents_uploaded( $user_id ),
			'documents_approved' => self::are_documents_approved( $user_id ),
			'account_verified' => self::is_account_verified( $user_id ),
			'can_publish' => self::can_vendor_publish( $user_id ),
			'subscription_active' => self::is_subscription_active( $user_id )
		);

		// Calculer le pourcentage de progression
		$total = count( $status );
		$completed = count( array_filter( $status ) );
		$status['progress_percent'] = round( ( $completed / $total ) * 100 );

		return $status;
	}

	/**
	 * Vérifier si le profil est complet
	 */
	public static function is_profile_complete( $user_id ) {
		$required_fields = array(
			'org_name',
			'org_display_name',
			'org_type',
			'org_siret',
			'org_address',
			'org_city',
			'org_zipcode'
		);

		foreach ( $required_fields as $field ) {
			$value = get_user_meta( $user_id, $field, true );
			if ( empty( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Vérifier si les documents requis sont uploadés
	 * Utilise les classes EL_Document_Types et EL_Vendor_Documents du plugin EventList
	 */
	public static function are_documents_uploaded( $user_id ) {
		if ( ! class_exists( 'EL_Document_Types' ) || ! class_exists( 'EL_Vendor_Documents' ) ) {
			return true; // Pas de système de documents
		}

		$required_types = EL_Document_Types::get_required();
		if ( empty( $required_types ) ) {
			return true;
		}

		$documents = EL_Vendor_Documents::get_vendor_documents( $user_id );
		$uploaded_type_ids = array();
		foreach ( $documents as $doc ) {
			$uploaded_type_ids[] = $doc->document_type_id;
		}

		foreach ( $required_types as $type ) {
			if ( ! in_array( $type->id, $uploaded_type_ids ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Vérifier si les documents sont approuvés
	 * Utilise EL_Vendor_Documents du plugin EventList
	 */
	public static function are_documents_approved( $user_id ) {
		if ( ! class_exists( 'EL_Vendor_Documents' ) ) {
			return true;
		}

		return EL_Vendor_Documents::vendor_has_all_required_approved( $user_id );
	}

	/**
	 * Vérifier si le compte est vérifié
	 */
	public static function is_account_verified( $user_id ) {
		$vendor_status = get_user_meta( $user_id, 'vendor_status', true );
		return $vendor_status === 'approved';
	}

	/**
	 * Vérifier si le vendor peut publier
	 * Utilise EL_Vendor_Documents du plugin EventList
	 */
	public static function can_vendor_publish( $user_id ) {
		// Vérifier le statut du compte
		if ( ! self::is_account_verified( $user_id ) ) {
			return false;
		}

		// Vérifier les documents requis sont approuvés
		if ( class_exists( 'EL_Vendor_Documents' ) ) {
			if ( ! EL_Vendor_Documents::vendor_has_all_required_approved( $user_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Vérifier si l'abonnement est actif
	 */
	public static function is_subscription_active( $user_id ) {
		$subscription_status = get_user_meta( $user_id, 'subscription_status', true );
		return in_array( $subscription_status, array( 'active', 'trial' ) );
	}

	/**
	 * Obtenir le plan d'abonnement
	 */
	public static function get_subscription_plan( $user_id ) {
		return get_user_meta( $user_id, 'subscription_plan', true ) ?: 'free';
	}

	/**
	 * Obtenir les étapes d'onboarding avec leur statut
	 */
	public static function get_onboarding_steps( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$status = self::get_onboarding_status( $user_id );
		$subscription_plan = self::get_subscription_plan( $user_id );

		$steps = array(
			array(
				'id' => 'account_created',
				'title' => 'Compte créé',
				'description' => 'Votre compte organisateur a été créé avec succès.',
				'icon' => 'fa-user-check',
				'status' => 'completed',
				'action_url' => null,
				'action_label' => null
			),
			array(
				'id' => 'profile_complete',
				'title' => 'Compléter mon profil',
				'description' => 'Ajoutez les informations de votre organisation.',
				'icon' => 'fa-building',
				'status' => $status['profile_complete'] ? 'completed' : 'pending',
				'action_url' => add_query_arg( array( 'vendor' => 'profile' ), get_myaccount_page() ),
				'action_label' => 'Compléter le profil'
			),
			array(
				'id' => 'documents_uploaded',
				'title' => 'Ajouter mes documents',
				'description' => 'Uploadez les documents requis pour la vérification.',
				'icon' => 'fa-file-upload',
				'status' => $status['documents_uploaded'] ? ( $status['documents_approved'] ? 'completed' : 'in_review' ) : 'pending',
				'action_url' => add_query_arg( array( 'vendor' => 'documents' ), get_myaccount_page() ),
				'action_label' => 'Gérer mes documents'
			),
			array(
				'id' => 'account_verified',
				'title' => 'Validation par Le Hiboo',
				'description' => $status['account_verified'] ? 'Votre compte a été validé.' : 'Notre équipe vérifie votre dossier.',
				'icon' => 'fa-shield-alt',
				'status' => $status['account_verified'] ? 'completed' : 'waiting',
				'action_url' => null,
				'action_label' => null
			),
			array(
				'id' => 'ready_to_publish',
				'title' => 'Publier ma première activité',
				'description' => $status['can_publish'] ? 'Vous pouvez maintenant créer et publier des activités !' : 'Disponible après validation.',
				'icon' => 'fa-rocket',
				'status' => $status['can_publish'] ? 'ready' : 'locked',
				'action_url' => $status['can_publish'] ? add_query_arg( array( 'vendor' => 'create-event' ), get_myaccount_page() ) : null,
				'action_label' => $status['can_publish'] ? 'Créer une activité' : null
			)
		);

		// Ajouter étape paiement si plan premium et pas encore payé
		if ( $subscription_plan === 'premium' ) {
			$subscription_status = get_user_meta( $user_id, 'subscription_status', true );
			if ( $subscription_status === 'pending_payment' ) {
				array_splice( $steps, 1, 0, array(
					array(
						'id' => 'payment',
						'title' => 'Finaliser le paiement Premium',
						'description' => 'Activez votre abonnement Premium pour profiter de toutes les fonctionnalités.',
						'icon' => 'fa-credit-card',
						'status' => 'pending',
						'action_url' => add_query_arg( array( 'vendor' => 'onboarding', 'action' => 'payment' ), get_myaccount_page() ),
						'action_label' => 'Payer maintenant',
						'highlight' => true
					)
				) );
			}
		}

		return $steps;
	}

	/**
	 * Marquer l'onboarding comme complété
	 */
	public static function complete_onboarding( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		update_user_meta( $user_id, self::ONBOARDING_META, current_time( 'mysql' ) );
		return true;
	}

	/**
	 * AJAX - Compléter l'onboarding
	 */
	public function ajax_complete_onboarding() {
		check_ajax_referer( 'lehiboo_onboarding_nonce', 'nonce' );

		$user_id = get_current_user_id();

		if ( self::complete_onboarding( $user_id ) ) {
			wp_send_json_success( array(
				'message' => 'Bienvenue ! Commencez à explorer votre espace organisateur.',
				'redirect_url' => add_query_arg( array( 'vendor' => 'listing' ), get_myaccount_page() )
			) );
		} else {
			wp_send_json_error( array( 'message' => 'Erreur.' ) );
		}
	}

	/**
	 * AJAX - Passer l'onboarding
	 */
	public function ajax_skip_onboarding() {
		check_ajax_referer( 'lehiboo_onboarding_nonce', 'nonce' );

		$user_id = get_current_user_id();
		self::complete_onboarding( $user_id );

		wp_send_json_success( array(
			'redirect_url' => add_query_arg( array( 'vendor' => 'listing' ), get_myaccount_page() )
		) );
	}

	/**
	 * Ajouter la page onboarding au menu vendor
	 */
	public function add_onboarding_page( $pages ) {
		$pages['onboarding'] = array(
			'label' => 'Bienvenue',
			'template' => get_stylesheet_directory() . '/templates/vendor/onboarding.php'
		);
		return $pages;
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_onboarding_assets() {
		// Vérifier si on est sur la page onboarding (vérification plus permissive)
		if ( ! isset( $_GET['vendor'] ) || $_GET['vendor'] !== 'onboarding' ) {
			return;
		}

		wp_enqueue_style(
			'lehiboo-onboarding',
			get_stylesheet_directory_uri() . '/assets/css/vendor-onboarding.css',
			array(),
			'2.0.0'
		);

		wp_localize_script( 'jquery', 'lehiboo_onboarding', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'lehiboo_onboarding_nonce' )
		) );
	}
}

// Initialize
LeHiboo_Vendor_Onboarding::get_instance();
