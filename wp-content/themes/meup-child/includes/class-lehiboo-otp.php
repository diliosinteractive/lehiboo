<?php
/**
 * Classe de gestion OTP (One-Time Password)
 * Système de vérification email gratuit et autonome
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LeHiboo_OTP {

	/**
	 * Nom de la table OTP
	 */
	private static $table_name = 'lehiboo_otp_codes';

	/**
	 * Durée de validité d'un OTP (en minutes)
	 */
	private static $otp_validity = 10;

	/**
	 * Longueur du code OTP
	 */
	private static $otp_length = 6;

	/**
	 * Nombre maximum de tentatives
	 */
	private static $max_attempts = 3;

	/**
	 * Initialisation
	 */
	public static function init() {
		// Créer la table lors de l'activation du thème
		add_action( 'after_switch_theme', array( __CLASS__, 'create_table' ) );

		// Nettoyer les codes expirés quotidiennement
		add_action( 'lehiboo_cleanup_expired_otps', array( __CLASS__, 'cleanup_expired_otps' ) );

		if ( ! wp_next_scheduled( 'lehiboo_cleanup_expired_otps' ) ) {
			wp_schedule_event( time(), 'daily', 'lehiboo_cleanup_expired_otps' );
		}
	}

	/**
	 * Créer la table OTP
	 */
	public static function create_table() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			email varchar(255) NOT NULL,
			otp_code varchar(10) NOT NULL,
			attempts int(11) DEFAULT 0,
			created_at datetime NOT NULL,
			expires_at datetime NOT NULL,
			verified tinyint(1) DEFAULT 0,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY email (email),
			KEY expires_at (expires_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Générer un code OTP
	 *
	 * @return string Code OTP
	 */
	public static function generate_code() {
		$code = '';
		for ( $i = 0; $i < self::$otp_length; $i++ ) {
			$code .= mt_rand( 0, 9 );
		}
		return $code;
	}

	/**
	 * Créer un OTP pour un utilisateur
	 *
	 * @param int $user_id ID de l'utilisateur
	 * @param string $email Email de l'utilisateur
	 * @return string|false Code OTP ou false en cas d'erreur
	 */
	public static function create_otp( $user_id, $email ) {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;

		// Vérifier si la table existe, sinon la créer
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
		if ( $table_exists != $table ) {
			self::create_table();

			// Vérifier à nouveau après création
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
			if ( $table_exists != $table ) {
				error_log( 'LeHiboo OTP: Impossible de créer la table ' . $table );
				return false;
			}
		}

		// Supprimer les anciens codes non utilisés pour cet utilisateur
		self::delete_user_otps( $user_id );

		// Générer le code
		$otp_code = self::generate_code();

		// Calculer l'expiration
		$created_at = current_time( 'mysql' );
		$expires_at = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . self::$otp_validity . ' minutes' ) );

		// Insérer dans la base
		$inserted = $wpdb->insert(
			$table,
			array(
				'user_id'    => $user_id,
				'email'      => $email,
				'otp_code'   => $otp_code,
				'attempts'   => 0,
				'created_at' => $created_at,
				'expires_at' => $expires_at,
				'verified'   => 0,
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%d' )
		);

		if ( $inserted ) {
			return $otp_code;
		}

		// Log l'erreur pour le debug
		if ( $wpdb->last_error ) {
			error_log( 'LeHiboo OTP: Erreur insertion - ' . $wpdb->last_error );
		}

		return false;
	}

	/**
	 * Vérifier un code OTP
	 *
	 * @param int $user_id ID de l'utilisateur
	 * @param string $otp_code Code OTP à vérifier
	 * @return array Résultat de la vérification
	 */
	public static function verify_otp( $user_id, $otp_code ) {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;

		// Récupérer le code OTP
		$otp = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table
			WHERE user_id = %d
			AND verified = 0
			ORDER BY created_at DESC
			LIMIT 1",
			$user_id
		) );

		if ( ! $otp ) {
			return array(
				'success' => false,
				'message' => 'Aucun code de vérification trouvé.',
				'error_code' => 'no_otp'
			);
		}

		// Vérifier si le code a expiré
		if ( strtotime( $otp->expires_at ) < current_time( 'timestamp' ) ) {
			return array(
				'success' => false,
				'message' => 'Le code de vérification a expiré. Veuillez en demander un nouveau.',
				'error_code' => 'expired'
			);
		}

		// Incrémenter le nombre de tentatives
		$wpdb->update(
			$table,
			array( 'attempts' => $otp->attempts + 1 ),
			array( 'id' => $otp->id ),
			array( '%d' ),
			array( '%d' )
		);

		// Vérifier le nombre de tentatives
		if ( $otp->attempts >= self::$max_attempts ) {
			return array(
				'success' => false,
				'message' => 'Nombre maximal de tentatives atteint. Veuillez demander un nouveau code.',
				'error_code' => 'max_attempts'
			);
		}

		// Vérifier le code
		if ( $otp->otp_code !== $otp_code ) {
			$remaining = self::$max_attempts - ( $otp->attempts + 1 );
			return array(
				'success' => false,
				'message' => sprintf( 'Code incorrect. %d tentative(s) restante(s).', max( 0, $remaining ) ),
				'error_code' => 'invalid_code',
				'remaining_attempts' => max( 0, $remaining )
			);
		}

		// Code valide ! Marquer comme vérifié
		$wpdb->update(
			$table,
			array( 'verified' => 1 ),
			array( 'id' => $otp->id ),
			array( '%d' ),
			array( '%d' )
		);

		// Mettre à jour le statut de l'utilisateur
		update_user_meta( $user_id, 'email_verified', 1 );

		return array(
			'success' => true,
			'message' => 'Email vérifié avec succès !',
			'error_code' => null
		);
	}

	/**
	 * Envoyer l'email avec le code OTP
	 *
	 * @param int $user_id ID de l'utilisateur
	 * @param string $email Email de l'utilisateur
	 * @param string $otp_code Code OTP
	 * @param string $firstname Prénom de l'utilisateur
	 * @return bool Succès ou échec de l'envoi
	 */
	public static function send_otp_email( $user_id, $email, $otp_code, $firstname ) {
		$site_name = get_bloginfo( 'name' );

		$subject = sprintf( '[%s] Code de vérification - %s', $site_name, $otp_code );

		$message = "Bonjour {$firstname},\n\n";
		$message .= "Bienvenue sur {$site_name} !\n\n";
		$message .= "Pour finaliser votre inscription, veuillez utiliser le code de vérification suivant :\n\n";
		$message .= "═══════════════════════════════\n";
		$message .= "   CODE : {$otp_code}\n";
		$message .= "═══════════════════════════════\n\n";
		$message .= "Ce code est valide pendant " . self::$otp_validity . " minutes.\n\n";
		$message .= "Si vous n'avez pas demandé ce code, vous pouvez ignorer cet email.\n\n";
		$message .= "Cordialement,\n";
		$message .= "L'équipe {$site_name}";

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		return wp_mail( $email, $subject, $message, $headers );
	}

	/**
	 * Renvoyer un nouveau code OTP
	 *
	 * @param int $user_id ID de l'utilisateur
	 * @return array Résultat
	 */
	public static function resend_otp( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return array(
				'success' => false,
				'message' => 'Utilisateur introuvable.'
			);
		}

		// Créer un nouveau code
		$otp_code = self::create_otp( $user_id, $user->user_email );

		if ( ! $otp_code ) {
			return array(
				'success' => false,
				'message' => 'Erreur lors de la génération du code.'
			);
		}

		// Envoyer l'email
		$firstname = get_user_meta( $user_id, 'first_name', true );
		$sent = self::send_otp_email( $user_id, $user->user_email, $otp_code, $firstname );

		if ( ! $sent ) {
			return array(
				'success' => false,
				'message' => 'Erreur lors de l\'envoi de l\'email.'
			);
		}

		return array(
			'success' => true,
			'message' => 'Un nouveau code a été envoyé à votre adresse email.'
		);
	}

	/**
	 * Supprimer les OTP d'un utilisateur
	 *
	 * @param int $user_id ID de l'utilisateur
	 */
	public static function delete_user_otps( $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;

		$wpdb->delete(
			$table,
			array( 'user_id' => $user_id ),
			array( '%d' )
		);
	}

	/**
	 * Nettoyer les codes expirés (tâche cron quotidienne)
	 */
	public static function cleanup_expired_otps() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;

		$wpdb->query(
			"DELETE FROM $table
			WHERE expires_at < NOW()
			OR (verified = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY))"
		);
	}

	/**
	 * Vérifier si un utilisateur a vérifié son email
	 *
	 * @param int $user_id ID de l'utilisateur
	 * @return bool
	 */
	public static function is_email_verified( $user_id ) {
		return (bool) get_user_meta( $user_id, 'email_verified', true );
	}

	/**
	 * Obtenir les statistiques OTP (pour debug)
	 *
	 * @return array
	 */
	public static function get_stats() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table_name;

		return array(
			'total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table" ),
			'verified' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE verified = 1" ),
			'pending' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE verified = 0 AND expires_at > NOW()" ),
			'expired' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE verified = 0 AND expires_at < NOW()" ),
		);
	}
}

// Initialiser la classe
LeHiboo_OTP::init();
