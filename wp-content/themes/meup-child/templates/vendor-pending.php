<?php
/**
 * Template: Compte Partenaire en Attente d'Approbation
 * Affiché aux vendors avec status = pending_approval
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Enqueue le CSS spécifique
wp_enqueue_style( 'vendor-pending', get_stylesheet_directory_uri() . '/assets/css/vendor-pending.css', array(), '2.0.0' );

get_header();

$current_user = wp_get_current_user();
$vendor_status = get_user_meta( $current_user->ID, 'vendor_status', true );
$org_name = get_user_meta( $current_user->ID, 'org_display_name', true );
$application_date = get_user_meta( $current_user->ID, 'vendor_application_date', true );
?>

<div class="vendor_pending_wrapper">
	<div class="vendor_pending_container">
		<div class="pending_icon">
			<svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="60" cy="60" r="58" stroke="#FFA500" stroke-width="4" stroke-dasharray="8 8"/>
				<path d="M60 30V60L75 75" stroke="#FFA500" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</div>

		<h1 class="pending_title">Demande en cours d'examen</h1>

		<div class="pending_message">
			<p class="message_main">
				Merci pour votre demande de partenariat, <strong><?php echo esc_html( $org_name ?: $current_user->first_name ); ?></strong> !
			</p>
			<p class="message_secondary">
				Notre équipe examine actuellement votre dossier. Vous recevrez une réponse par email dans les plus brefs délais.
			</p>
		</div>

		<div class="pending_timeline">
			<div class="timeline_step completed">
				<div class="step_icon">
					<i class="fas fa-check-circle"></i>
				</div>
				<div class="step_content">
					<h3>Demande soumise</h3>
					<p><?php echo $application_date ? date_i18n( 'j F Y à H:i', strtotime( $application_date ) ) : 'Récemment'; ?></p>
				</div>
			</div>

			<div class="timeline_step current">
				<div class="step_icon">
					<i class="fas fa-hourglass-half"></i>
				</div>
				<div class="step_content">
					<h3>Examen en cours</h3>
					<p>Notre équipe vérifie vos informations</p>
				</div>
			</div>

			<div class="timeline_step">
				<div class="step_icon">
					<i class="fas fa-paper-plane"></i>
				</div>
				<div class="step_content">
					<h3>Décision finale</h3>
					<p>Vous recevrez un email de confirmation</p>
				</div>
			</div>
		</div>

		<div class="pending_info_box">
			<div class="info_box_header">
				<i class="fas fa-info-circle"></i>
				<h3>Informations importantes</h3>
			</div>
			<ul class="info_box_list">
				<li>
					<i class="fas fa-clock"></i>
					Le délai d'examen est généralement de <strong>24 à 48 heures</strong>
				</li>
				<li>
					<i class="fas fa-envelope"></i>
					Vérifiez vos emails régulièrement, y compris vos spams
				</li>
				<li>
					<i class="fas fa-lock"></i>
					Votre compte est créé mais la publication d'activités nécessite une approbation
				</li>
				<li>
					<i class="fas fa-edit"></i>
					Une fois approuvé, vous pourrez créer et publier vos événements librement
				</li>
			</ul>
		</div>

		<div class="pending_actions">
			<a href="<?php echo home_url(); ?>" class="btn_action btn_primary">
				<i class="fas fa-home"></i>
				Retour à l'accueil
			</a>
			<a href="<?php echo wp_logout_url( home_url() ); ?>" class="btn_action btn_secondary">
				<i class="fas fa-sign-out-alt"></i>
				Se déconnecter
			</a>
		</div>

		<div class="pending_help">
			<p>
				<i class="fas fa-question-circle"></i>
				Une question ? <a href="<?php echo home_url('/contact'); ?>">Contactez-nous</a>
			</p>
		</div>
	</div>
</div>

<?php
get_footer();
?>
