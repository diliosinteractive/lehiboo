<?php
/**
 * Template: Compte Partenaire en Attente d'Approbation
 * Affiché aux vendors avec status = pending_approval
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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

<style>
.vendor_pending_wrapper {
	min-height: 80vh;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
	background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.vendor_pending_container {
	max-width: 700px;
	width: 100%;
	background: white;
	border-radius: 20px;
	padding: 50px 40px;
	box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
	text-align: center;
}

.pending_icon {
	margin-bottom: 30px;
	animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
	0%, 100% { transform: scale(1); opacity: 1; }
	50% { transform: scale(1.05); opacity: 0.8; }
}

.pending_title {
	font-size: 32px;
	font-weight: 700;
	color: #1d2327;
	margin-bottom: 20px;
}

.pending_message {
	margin-bottom: 40px;
}

.message_main {
	font-size: 18px;
	color: #1d2327;
	margin-bottom: 12px;
	line-height: 1.6;
}

.message_secondary {
	font-size: 15px;
	color: #646970;
	line-height: 1.6;
}

/* Timeline */
.pending_timeline {
	display: flex;
	justify-content: space-between;
	margin: 40px 0;
	padding: 0 20px;
	position: relative;
}

.pending_timeline::before {
	content: '';
	position: absolute;
	top: 24px;
	left: 80px;
	right: 80px;
	height: 3px;
	background: linear-gradient(90deg, #4caf50 0%, #4caf50 33%, #FFA500 33%, #FFA500 66%, #ddd 66%, #ddd 100%);
}

.timeline_step {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
	z-index: 1;
}

.step_icon {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background: #ddd;
	color: white;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20px;
	margin-bottom: 12px;
	border: 4px solid white;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.timeline_step.completed .step_icon {
	background: #4caf50;
}

.timeline_step.current .step_icon {
	background: #FFA500;
	animation: pulse 2s ease-in-out infinite;
}

.step_content h3 {
	font-size: 14px;
	font-weight: 600;
	color: #1d2327;
	margin-bottom: 6px;
}

.step_content p {
	font-size: 12px;
	color: #646970;
}

/* Info Box */
.pending_info_box {
	background: #f6f7f7;
	border-radius: 12px;
	padding: 24px;
	margin: 30px 0;
	text-align: left;
}

.info_box_header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 20px;
}

.info_box_header i {
	font-size: 24px;
	color: #2271b1;
}

.info_box_header h3 {
	font-size: 18px;
	font-weight: 600;
	color: #1d2327;
	margin: 0;
}

.info_box_list {
	list-style: none;
	margin: 0;
	padding: 0;
}

.info_box_list li {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	padding: 12px 0;
	font-size: 14px;
	color: #50575e;
	line-height: 1.6;
}

.info_box_list li:not(:last-child) {
	border-bottom: 1px solid #dcdcde;
}

.info_box_list li i {
	flex-shrink: 0;
	color: #FFA500;
	font-size: 16px;
	margin-top: 2px;
}

/* Actions */
.pending_actions {
	display: flex;
	gap: 16px;
	justify-content: center;
	margin: 30px 0 20px;
	flex-wrap: wrap;
}

.btn_action {
	display: inline-flex;
	align-items: center;
	gap: 10px;
	padding: 14px 28px;
	border-radius: 10px;
	font-size: 15px;
	font-weight: 600;
	text-decoration: none;
	transition: all 0.3s ease;
	cursor: pointer;
}

.btn_primary {
	background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
	color: white;
	border: none;
}

.btn_primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

.btn_secondary {
	background: white;
	color: #50575e;
	border: 2px solid #dcdcde;
}

.btn_secondary:hover {
	background: #f6f7f7;
	border-color: #c3c4c7;
}

/* Help */
.pending_help {
	font-size: 14px;
	color: #646970;
	margin-top: 20px;
}

.pending_help i {
	color: #FFA500;
	margin-right: 6px;
}

.pending_help a {
	color: #2271b1;
	font-weight: 600;
	text-decoration: none;
}

.pending_help a:hover {
	text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
	.vendor_pending_container {
		padding: 40px 24px;
	}

	.pending_title {
		font-size: 24px;
	}

	.pending_timeline {
		flex-direction: column;
		gap: 24px;
		padding: 0;
	}

	.pending_timeline::before {
		display: none;
	}

	.timeline_step {
		flex-direction: row;
		text-align: left;
		gap: 16px;
	}

	.step_content {
		flex: 1;
	}

	.pending_actions {
		flex-direction: column;
	}

	.btn_action {
		width: 100%;
		justify-content: center;
	}
}
</style>

<?php
get_footer();
?>
