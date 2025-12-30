<?php
/**
 * Le Hiboo - Vendor Documents Management
 * Gestion des documents obligatoires pour les partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Alias pour compatibilité avec le code existant qui utilise EL_Vendor_Documents
class_alias( 'LeHiboo_Vendor_Documents', 'EL_Vendor_Documents' );

class LeHiboo_Vendor_Documents {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Option name for document settings
	 */
	const OPTION_NAME = 'lehiboo_vendor_documents_settings';

	/**
	 * Liste des types de documents disponibles
	 */
	private static $document_types = array(
		'kbis' => array(
			'label' => 'Kbis / Statuts',
			'description' => 'Extrait Kbis ou statuts de l\'association',
			'icon' => 'fa-file-contract',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		),
		'assurance_rc' => array(
			'label' => 'Attestation RC Pro',
			'description' => 'Attestation de responsabilité civile professionnelle',
			'icon' => 'fa-shield-alt',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		),
		'piece_identite' => array(
			'label' => 'Pièce d\'identité',
			'description' => 'CNI ou passeport du représentant légal',
			'icon' => 'fa-id-card',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		),
		'rib' => array(
			'label' => 'RIB',
			'description' => 'Relevé d\'identité bancaire pour les paiements',
			'icon' => 'fa-university',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		),
		'certification' => array(
			'label' => 'Certifications / Diplômes',
			'description' => 'Certifications professionnelles ou diplômes',
			'icon' => 'fa-certificate',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		),
		'autre' => array(
			'label' => 'Autre document',
			'description' => 'Document complémentaire',
			'icon' => 'fa-file-alt',
			'accept' => '.pdf,.jpg,.jpeg,.png'
		)
	);

	/**
	 * Statuts des documents
	 */
	const STATUS_PENDING = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';

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
		// Admin settings page
		add_action( 'admin_menu', array( $this, 'add_settings_submenu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// AJAX handlers
		add_action( 'wp_ajax_lehiboo_upload_vendor_document', array( $this, 'ajax_upload_document' ) );
		add_action( 'wp_ajax_lehiboo_delete_vendor_document', array( $this, 'ajax_delete_document' ) );
		add_action( 'wp_ajax_lehiboo_approve_document', array( $this, 'ajax_approve_document' ) );
		add_action( 'wp_ajax_lehiboo_reject_document', array( $this, 'ajax_reject_document' ) );

		// Vendor documents page
		add_filter( 'el_vendor_pages', array( $this, 'add_documents_page' ) );
	}

	/**
	 * Ajouter le sous-menu de configuration
	 */
	public function add_settings_submenu() {
		add_submenu_page(
			'lehiboo-vendor-applications',
			'Documents requis',
			'Documents requis',
			'manage_options',
			'lehiboo-vendor-documents-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enregistrer les paramètres
	 */
	public function register_settings() {
		register_setting( 'lehiboo_vendor_documents', self::OPTION_NAME );
	}

	/**
	 * Page de configuration des documents requis
	 */
	public function render_settings_page() {
		$settings = self::get_settings();

		// Sauvegarder si formulaire soumis
		if ( isset( $_POST['lehiboo_save_document_settings'] ) && check_admin_referer( 'lehiboo_document_settings' ) ) {
			$new_settings = array(
				'required_documents' => isset( $_POST['required_documents'] ) ? array_map( 'sanitize_text_field', $_POST['required_documents'] ) : array(),
				'block_without_documents' => isset( $_POST['block_without_documents'] ) ? 1 : 0,
				'auto_approve' => isset( $_POST['auto_approve'] ) ? 1 : 0,
				'notify_admin_upload' => isset( $_POST['notify_admin_upload'] ) ? 1 : 0
			);
			update_option( self::OPTION_NAME, $new_settings );
			$settings = $new_settings;
			echo '<div class="notice notice-success"><p>Paramètres enregistrés.</p></div>';
		}

		?>
		<div class="wrap">
			<h1><i class="fas fa-file-alt"></i> Configuration des documents requis</h1>

			<form method="post">
				<?php wp_nonce_field( 'lehiboo_document_settings' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">Documents obligatoires</th>
						<td>
							<fieldset>
								<?php foreach ( self::$document_types as $key => $doc ) : ?>
									<label style="display: block; margin-bottom: 10px;">
										<input type="checkbox"
											   name="required_documents[]"
											   value="<?php echo esc_attr( $key ); ?>"
											   <?php checked( in_array( $key, $settings['required_documents'] ?? array() ) ); ?>>
										<i class="fas <?php echo esc_attr( $doc['icon'] ); ?>"></i>
										<strong><?php echo esc_html( $doc['label'] ); ?></strong>
										<span style="color: #666;"> - <?php echo esc_html( $doc['description'] ); ?></span>
									</label>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row">Blocage publication</th>
						<td>
							<label>
								<input type="checkbox"
									   name="block_without_documents"
									   value="1"
									   <?php checked( $settings['block_without_documents'] ?? 0 ); ?>>
								Bloquer la publication d'activités tant que les documents obligatoires ne sont pas validés
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">Validation automatique</th>
						<td>
							<label>
								<input type="checkbox"
									   name="auto_approve"
									   value="1"
									   <?php checked( $settings['auto_approve'] ?? 0 ); ?>>
								Approuver automatiquement les documents uploadés (non recommandé)
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">Notifications</th>
						<td>
							<label>
								<input type="checkbox"
									   name="notify_admin_upload"
									   value="1"
									   <?php checked( $settings['notify_admin_upload'] ?? 1, 1 ); ?>>
								Notifier l'administrateur quand un document est uploadé
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="lehiboo_save_document_settings" class="button button-primary" value="Enregistrer les paramètres">
				</p>
			</form>
		</div>

		<style>
		.wrap h1 i { margin-right: 10px; color: #FF601F; }
		</style>
		<?php
	}

	/**
	 * Obtenir les paramètres
	 */
	public static function get_settings() {
		$defaults = array(
			'required_documents' => array( 'kbis', 'assurance_rc' ),
			'block_without_documents' => 1,
			'auto_approve' => 0,
			'notify_admin_upload' => 1
		);
		return wp_parse_args( get_option( self::OPTION_NAME, array() ), $defaults );
	}

	/**
	 * Obtenir les documents requis
	 */
	public static function get_required_documents() {
		$settings = self::get_settings();
		return $settings['required_documents'] ?? array();
	}

	/**
	 * Obtenir les infos d'un type de document
	 */
	public static function get_document_type_info( $type ) {
		return self::$document_types[ $type ] ?? null;
	}

	/**
	 * Obtenir tous les types de documents
	 */
	public static function get_all_document_types() {
		return self::$document_types;
	}

	/**
	 * Obtenir les documents d'un vendor
	 */
	public static function get_vendor_documents( $user_id ) {
		$documents = get_user_meta( $user_id, 'lehiboo_vendor_documents', true );
		return is_array( $documents ) ? $documents : array();
	}

	/**
	 * Sauvegarder un document pour un vendor
	 */
	public static function save_vendor_document( $user_id, $type, $attachment_id, $status = self::STATUS_PENDING ) {
		$documents = self::get_vendor_documents( $user_id );

		$documents[ $type ] = array(
			'attachment_id' => $attachment_id,
			'status' => $status,
			'uploaded_at' => current_time( 'mysql' ),
			'reviewed_at' => null,
			'reviewed_by' => null,
			'rejection_reason' => null
		);

		update_user_meta( $user_id, 'lehiboo_vendor_documents', $documents );

		// Notifier admin si configuré
		$settings = self::get_settings();
		if ( $settings['notify_admin_upload'] ) {
			self::notify_admin_new_document( $user_id, $type );
		}

		// Auto-approve si configuré
		if ( $settings['auto_approve'] ) {
			self::approve_document( $user_id, $type );
		}

		return true;
	}

	/**
	 * Approuver un document
	 */
	public static function approve_document( $user_id, $type, $reviewer_id = null ) {
		$documents = self::get_vendor_documents( $user_id );

		if ( ! isset( $documents[ $type ] ) ) {
			return false;
		}

		$documents[ $type ]['status'] = self::STATUS_APPROVED;
		$documents[ $type ]['reviewed_at'] = current_time( 'mysql' );
		$documents[ $type ]['reviewed_by'] = $reviewer_id ?: get_current_user_id();
		$documents[ $type ]['rejection_reason'] = null;

		update_user_meta( $user_id, 'lehiboo_vendor_documents', $documents );

		// Vérifier si tous les documents requis sont approuvés
		if ( self::vendor_has_all_required_approved( $user_id ) ) {
			// Optionnel : envoyer email de félicitations
			self::notify_vendor_all_approved( $user_id );
		}

		return true;
	}

	/**
	 * Rejeter un document
	 */
	public static function reject_document( $user_id, $type, $reason = '', $reviewer_id = null ) {
		$documents = self::get_vendor_documents( $user_id );

		if ( ! isset( $documents[ $type ] ) ) {
			return false;
		}

		$documents[ $type ]['status'] = self::STATUS_REJECTED;
		$documents[ $type ]['reviewed_at'] = current_time( 'mysql' );
		$documents[ $type ]['reviewed_by'] = $reviewer_id ?: get_current_user_id();
		$documents[ $type ]['rejection_reason'] = $reason;

		update_user_meta( $user_id, 'lehiboo_vendor_documents', $documents );

		// Notifier le vendor
		self::notify_vendor_document_rejected( $user_id, $type, $reason );

		return true;
	}

	/**
	 * Vérifier si un vendor a tous les documents requis approuvés
	 */
	public static function vendor_has_all_required_approved( $user_id ) {
		$required = self::get_required_documents();

		if ( empty( $required ) ) {
			return true; // Aucun document requis
		}

		$documents = self::get_vendor_documents( $user_id );

		foreach ( $required as $type ) {
			if ( ! isset( $documents[ $type ] ) || $documents[ $type ]['status'] !== self::STATUS_APPROVED ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Obtenir les documents requis manquants
	 */
	public static function get_missing_required_documents( $user_id ) {
		$required = self::get_required_documents();
		$documents = self::get_vendor_documents( $user_id );
		$missing = array();

		foreach ( $required as $type ) {
			if ( ! isset( $documents[ $type ] ) ) {
				$missing[ $type ] = 'not_uploaded';
			} elseif ( $documents[ $type ]['status'] === self::STATUS_PENDING ) {
				$missing[ $type ] = 'pending';
			} elseif ( $documents[ $type ]['status'] === self::STATUS_REJECTED ) {
				$missing[ $type ] = 'rejected';
			}
		}

		return $missing;
	}

	/**
	 * Vérifier si le vendor peut publier
	 */
	public static function vendor_can_publish( $user_id ) {
		$settings = self::get_settings();

		// Si le blocage n'est pas activé, autoriser
		if ( ! $settings['block_without_documents'] ) {
			return true;
		}

		// Vérifier les documents
		return self::vendor_has_all_required_approved( $user_id );
	}

	/**
	 * AJAX - Upload document
	 */
	public function ajax_upload_document() {
		check_ajax_referer( 'lehiboo_vendor_documents_nonce', 'nonce' );

		$user_id = get_current_user_id();
		$type = isset( $_POST['document_type'] ) ? sanitize_text_field( $_POST['document_type'] ) : '';

		if ( ! $user_id || ! $type || ! isset( self::$document_types[ $type ] ) ) {
			wp_send_json_error( array( 'message' => 'Données invalides.' ) );
		}

		if ( empty( $_FILES['document'] ) ) {
			wp_send_json_error( array( 'message' => 'Aucun fichier reçu.' ) );
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$attachment_id = media_handle_upload( 'document', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
		}

		self::save_vendor_document( $user_id, $type, $attachment_id );

		wp_send_json_success( array(
			'message' => 'Document uploadé avec succès. Il sera vérifié par notre équipe.',
			'attachment_id' => $attachment_id,
			'url' => wp_get_attachment_url( $attachment_id )
		) );
	}

	/**
	 * AJAX - Delete document
	 */
	public function ajax_delete_document() {
		check_ajax_referer( 'lehiboo_vendor_documents_nonce', 'nonce' );

		$user_id = get_current_user_id();
		$type = isset( $_POST['document_type'] ) ? sanitize_text_field( $_POST['document_type'] ) : '';

		$documents = self::get_vendor_documents( $user_id );

		if ( isset( $documents[ $type ] ) ) {
			// Supprimer l'attachment
			wp_delete_attachment( $documents[ $type ]['attachment_id'], true );

			// Supprimer de la liste
			unset( $documents[ $type ] );
			update_user_meta( $user_id, 'lehiboo_vendor_documents', $documents );
		}

		wp_send_json_success( array( 'message' => 'Document supprimé.' ) );
	}

	/**
	 * AJAX - Approve document (admin)
	 */
	public function ajax_approve_document() {
		check_ajax_referer( 'lehiboo_vendor_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission refusée.' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$type = isset( $_POST['document_type'] ) ? sanitize_text_field( $_POST['document_type'] ) : '';

		if ( self::approve_document( $user_id, $type ) ) {
			wp_send_json_success( array( 'message' => 'Document approuvé.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Erreur lors de l\'approbation.' ) );
		}
	}

	/**
	 * AJAX - Reject document (admin)
	 */
	public function ajax_reject_document() {
		check_ajax_referer( 'lehiboo_vendor_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission refusée.' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$type = isset( $_POST['document_type'] ) ? sanitize_text_field( $_POST['document_type'] ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

		if ( self::reject_document( $user_id, $type, $reason ) ) {
			wp_send_json_success( array( 'message' => 'Document rejeté.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Erreur lors du rejet.' ) );
		}
	}

	/**
	 * Notifier l'admin d'un nouveau document
	 */
	private static function notify_admin_new_document( $user_id, $type ) {
		$admin_email = get_option( 'admin_email' );
		$user = get_userdata( $user_id );
		$org_name = get_user_meta( $user_id, 'org_display_name', true );
		$doc_info = self::get_document_type_info( $type );

		$subject = '[Le Hiboo] Nouveau document à vérifier';
		$message = "Un partenaire a uploadé un nouveau document.\n\n";
		$message .= "Organisation : {$org_name}\n";
		$message .= "Type de document : {$doc_info['label']}\n";
		$message .= "Email : {$user->user_email}\n\n";
		$message .= "Connectez-vous à l'administration pour le vérifier.";

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Notifier le vendor que son document est rejeté
	 */
	private static function notify_vendor_document_rejected( $user_id, $type, $reason ) {
		$user = get_userdata( $user_id );
		$doc_info = self::get_document_type_info( $type );

		$subject = '[Le Hiboo] Document refusé';
		$message = "Bonjour,\n\n";
		$message .= "Votre document \"{$doc_info['label']}\" a été refusé.\n\n";

		if ( $reason ) {
			$message .= "Motif : {$reason}\n\n";
		}

		$message .= "Veuillez vous connecter et uploader un nouveau document conforme.\n\n";
		$message .= "Cordialement,\nL'équipe Le Hiboo";

		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Notifier le vendor que tous ses documents sont approuvés
	 */
	private static function notify_vendor_all_approved( $user_id ) {
		$user = get_userdata( $user_id );
		$firstname = $user->first_name ?: $user->display_name;

		$subject = '[Le Hiboo] Tous vos documents sont validés !';
		$message = "Bonjour {$firstname},\n\n";
		$message .= "Bonne nouvelle ! Tous vos documents ont été vérifiés et approuvés.\n\n";
		$message .= "Vous pouvez maintenant publier vos activités sur Le Hiboo.\n\n";
		$message .= "Connectez-vous dès maintenant pour créer votre première activité !\n\n";
		$message .= "Cordialement,\nL'équipe Le Hiboo";

		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Ajouter la page documents au menu vendor
	 */
	public function add_documents_page( $pages ) {
		$pages['documents'] = array(
			'label' => 'Mes Documents',
			'template' => get_stylesheet_directory() . '/templates/vendor/documents.php'
		);
		return $pages;
	}
}

// Initialize
LeHiboo_Vendor_Documents::get_instance();
