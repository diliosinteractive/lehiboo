<?php
/**
 * Le Hiboo - Vendor Restrictions
 * Gestion des restrictions pour les partenaires non approuvés
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LeHiboo_Vendor_Restrictions {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

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
		// Bloquer la publication d'événements si non approuvé
		add_filter( 'wp_insert_post_data', array( $this, 'check_vendor_status_before_publish' ), 10, 2 );

		// Rediriger les vendors pending vers la page d'attente
		add_action( 'template_redirect', array( $this, 'redirect_pending_vendors' ) );

		// Masquer le bouton "Créer événement" si pas approuvé
		add_filter( 'ova_events_show_create_event_button', array( $this, 'hide_create_button_if_not_approved' ) );

		// Ajouter un message dans l'interface de création d'événement
		add_action( 'el_before_submit_event_form', array( $this, 'show_restriction_notice' ) );

		// Bloquer les soumissions AJAX d'événements
		add_filter( 'el_ajax_before_insert_event', array( $this, 'block_event_submission_if_not_approved' ) );

		// Ajouter un indicateur de statut dans le menu admin bar
		add_action( 'admin_bar_menu', array( $this, 'add_status_indicator_to_admin_bar' ), 999 );
	}

	/**
	 * Obtenir le statut d'un vendor
	 */
	private function get_vendor_status( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );
		if ( ! $user || ! in_array( 'el_event_vendor', $user->roles ) ) {
			return null;
		}

		return get_user_meta( $user_id, 'vendor_status', true );
	}

	/**
	 * Vérifier si un vendor est approuvé
	 */
	private function is_vendor_approved( $user_id = null ) {
		$status = $this->get_vendor_status( $user_id );
		return $status === 'approved';
	}

	/**
	 * Bloquer la publication d'événements si vendor non approuvé
	 */
	public function check_vendor_status_before_publish( $data, $postarr ) {
		// Vérifier si c'est un post de type 'event'
		if ( $data['post_type'] !== 'event' ) {
			return $data;
		}

		// Vérifier si c'est une publication (pas un brouillon)
		if ( $data['post_status'] !== 'publish' ) {
			return $data;
		}

		// Obtenir l'auteur
		$author_id = isset( $postarr['post_author'] ) ? intval( $postarr['post_author'] ) : get_current_user_id();

		// Vérifier le statut du vendor
		$vendor_status = $this->get_vendor_status( $author_id );

		// Si c'est un vendor et qu'il n'est pas approuvé
		if ( $vendor_status && $vendor_status !== 'approved' ) {
			// Forcer le statut en brouillon
			$data['post_status'] = 'draft';

			// Ajouter un message d'erreur
			add_filter( 'redirect_post_location', function( $location ) use ( $vendor_status ) {
				$message = '';
				if ( $vendor_status === 'pending_approval' ) {
					$message = 'Votre compte est en attente d\'approbation. Vous ne pouvez pas publier d\'événements pour le moment.';
				} elseif ( $vendor_status === 'rejected' ) {
					$message = 'Votre demande de partenariat a été rejetée. Contactez-nous pour plus d\'informations.';
				} elseif ( $vendor_status === 'suspended' ) {
					$message = 'Votre compte a été suspendu. Contactez l\'administration.';
				}

				return add_query_arg( array(
					'vendor_restriction' => 'not_approved',
					'message' => urlencode( $message )
				), $location );
			} );
		}

		return $data;
	}

	/**
	 * Rediriger les vendors pending vers la page d'attente
	 */
	public function redirect_pending_vendors() {
		// Ne pas rediriger si on est déjà sur la page pending
		if ( is_page_template( 'templates/vendor-pending.php' ) ) {
			return;
		}

		// Ne pas rediriger sur certaines pages système
		if ( is_admin() || is_page( array( 'logout', 'connexion', 'inscription' ) ) ) {
			return;
		}

		// Vérifier si l'utilisateur est connecté
		if ( ! is_user_logged_in() ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Vérifier si c'est un vendor
		if ( ! in_array( 'el_event_vendor', $current_user->roles ) ) {
			return;
		}

		$vendor_status = $this->get_vendor_status();

		// Si le vendor est pending et qu'il essaie d'accéder à certaines pages
		if ( $vendor_status === 'pending_approval' ) {
			// Pages à bloquer pour les vendors pending
			$blocked_pages = array(
				'create-event',
				'submit-event',
				'my-events',
				'add-event',
				'edit-event'
			);

			$current_slug = get_post_field( 'post_name', get_queried_object_id() );

			// Si on essaie d'accéder à une page bloquée ou à une page d'édition d'event
			if ( in_array( $current_slug, $blocked_pages ) || is_singular( 'event' ) && get_query_var( 'edit' ) ) {
				// Rediriger vers la page pending
				$pending_page = get_page_by_path( 'vendor-pending' );
				if ( $pending_page ) {
					wp_redirect( get_permalink( $pending_page ) );
					exit;
				} else {
					// Si la page n'existe pas, afficher le template directement
					include get_stylesheet_directory() . '/templates/vendor-pending.php';
					exit;
				}
			}
		}

		// Si le vendor est rejeté ou suspendu
		if ( in_array( $vendor_status, array( 'rejected', 'suspended' ) ) ) {
			// Afficher un message et empêcher l'accès aux fonctionnalités vendor
			if ( is_singular( 'event' ) && get_query_var( 'edit' ) ) {
				wp_die(
					'Votre compte partenaire a été ' . ( $vendor_status === 'rejected' ? 'rejeté' : 'suspendu' ) . '. Contactez l\'administration pour plus d\'informations.',
					'Accès refusé',
					array( 'back_link' => true )
				);
			}
		}
	}

	/**
	 * Masquer le bouton "Créer événement" si pas approuvé
	 */
	public function hide_create_button_if_not_approved( $show ) {
		if ( ! is_user_logged_in() ) {
			return $show;
		}

		$vendor_status = $this->get_vendor_status();

		// Si c'est un vendor non approuvé, masquer le bouton
		if ( $vendor_status && $vendor_status !== 'approved' ) {
			return false;
		}

		return $show;
	}

	/**
	 * Afficher un message de restriction dans l'interface de création
	 */
	public function show_restriction_notice() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$vendor_status = $this->get_vendor_status();

		if ( ! $vendor_status || $vendor_status === 'approved' ) {
			return;
		}

		$messages = array(
			'pending_approval' => array(
				'type' => 'warning',
				'icon' => 'fa-clock',
				'title' => 'Compte en attente d\'approbation',
				'text' => 'Votre demande de partenariat est en cours d\'examen. Vous recevrez un email dès que votre compte sera validé.'
			),
			'rejected' => array(
				'type' => 'error',
				'icon' => 'fa-times-circle',
				'title' => 'Demande rejetée',
				'text' => 'Votre demande de partenariat a été rejetée. Veuillez contacter l\'administration pour plus d\'informations.'
			),
			'suspended' => array(
				'type' => 'error',
				'icon' => 'fa-ban',
				'title' => 'Compte suspendu',
				'text' => 'Votre compte a été temporairement suspendu. Contactez l\'administration.'
			)
		);

		$message = isset( $messages[ $vendor_status ] ) ? $messages[ $vendor_status ] : null;

		if ( ! $message ) {
			return;
		}

		?>
		<div class="lehiboo_vendor_restriction_notice notice_<?php echo esc_attr( $message['type'] ); ?>">
			<div class="notice_icon">
				<i class="fas <?php echo esc_attr( $message['icon'] ); ?>"></i>
			</div>
			<div class="notice_content">
				<h3><?php echo esc_html( $message['title'] ); ?></h3>
				<p><?php echo esc_html( $message['text'] ); ?></p>
			</div>
		</div>
		<style>
		.lehiboo_vendor_restriction_notice {
			display: flex;
			align-items: center;
			gap: 20px;
			padding: 20px;
			border-radius: 12px;
			margin-bottom: 30px;
		}
		.notice_warning {
			background: #fff3cd;
			border-left: 4px solid #ffc107;
		}
		.notice_error {
			background: #f8d7da;
			border-left: 4px solid #dc3545;
		}
		.notice_icon {
			font-size: 32px;
			flex-shrink: 0;
		}
		.notice_warning .notice_icon {
			color: #856404;
		}
		.notice_error .notice_icon {
			color: #721c24;
		}
		.notice_content h3 {
			margin: 0 0 8px 0;
			font-size: 18px;
			font-weight: 600;
		}
		.notice_content p {
			margin: 0;
			font-size: 14px;
			line-height: 1.6;
		}
		</style>
		<?php
	}

	/**
	 * Bloquer les soumissions AJAX si non approuvé
	 */
	public function block_event_submission_if_not_approved( $allowed ) {
		if ( ! is_user_logged_in() ) {
			return $allowed;
		}

		$vendor_status = $this->get_vendor_status();

		if ( $vendor_status && $vendor_status !== 'approved' ) {
			wp_send_json_error( array(
				'message' => 'Votre compte partenaire doit être approuvé avant de pouvoir publier des événements.'
			) );
			return false;
		}

		return $allowed;
	}

	/**
	 * Ajouter un indicateur de statut dans l'admin bar
	 */
	public function add_status_indicator_to_admin_bar( $wp_admin_bar ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$vendor_status = $this->get_vendor_status();

		if ( ! $vendor_status ) {
			return;
		}

		$statuses = array(
			'pending_approval' => array(
				'label' => 'En attente',
				'color' => '#ffc107',
				'icon' => 'fa-clock'
			),
			'approved' => array(
				'label' => 'Approuvé',
				'color' => '#4caf50',
				'icon' => 'fa-check-circle'
			),
			'rejected' => array(
				'label' => 'Rejeté',
				'color' => '#dc3545',
				'icon' => 'fa-times-circle'
			),
			'suspended' => array(
				'label' => 'Suspendu',
				'color' => '#ff5722',
				'icon' => 'fa-ban'
			)
		);

		$status_info = isset( $statuses[ $vendor_status ] ) ? $statuses[ $vendor_status ] : null;

		if ( ! $status_info ) {
			return;
		}

		$wp_admin_bar->add_node( array(
			'id'    => 'vendor_status',
			'title' => '<span style="color: ' . esc_attr( $status_info['color'] ) . ';"><i class="fas ' . esc_attr( $status_info['icon'] ) . '"></i> Statut: ' . esc_html( $status_info['label'] ) . '</span>',
			'href'  => $vendor_status === 'pending_approval' ? get_permalink( get_page_by_path( 'vendor-pending' ) ) : false,
			'meta'  => array(
				'class' => 'vendor-status-indicator'
			)
		) );

		// Ajouter Font Awesome si pas déjà chargé
		add_action( 'wp_head', function() {
			echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
		} );
	}
}

// Initialize
LeHiboo_Vendor_Restrictions::get_instance();
