<?php
/**
 * Template - Vendor Onboarding Page
 * Page d'accueil et progression pour les nouveaux partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
$user = get_userdata( $user_id );
$firstname = $user->first_name ?: $user->display_name;
$org_name = get_user_meta( $user_id, 'org_display_name', true ) ?: get_user_meta( $user_id, 'org_name', true );

// Obtenir le statut d'onboarding
$onboarding_status = LeHiboo_Vendor_Onboarding::get_onboarding_status( $user_id );
$steps = LeHiboo_Vendor_Onboarding::get_onboarding_steps( $user_id );
$subscription_plan = LeHiboo_Vendor_Onboarding::get_subscription_plan( $user_id );
$progress = $onboarding_status['progress_percent'];

// Vérifier si action de paiement
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
?>

<div class="onboarding_page">

	<!-- Header avec message de bienvenue -->
	<div class="onboarding_header">
		<div class="welcome_message">
			<div class="welcome_icon">
				<i class="fas fa-hand-sparkles"></i>
			</div>
			<div class="welcome_text">
				<h1>Bienvenue <?php echo esc_html( $firstname ); ?> !</h1>
				<p>
					<?php if ( $org_name ) : ?>
						Votre compte organisateur pour <strong><?php echo esc_html( $org_name ); ?></strong> est prêt.
					<?php else : ?>
						Votre compte organisateur est prêt.
					<?php endif; ?>
					Suivez les étapes ci-dessous pour commencer à publier vos activités.
				</p>
			</div>
		</div>

		<!-- Badge abonnement -->
		<div class="subscription_badge_header <?php echo esc_attr( $subscription_plan ); ?>">
			<?php if ( $subscription_plan === 'premium' ) : ?>
				<i class="fas fa-crown"></i>
				<span>Plan Premium</span>
			<?php else : ?>
				<i class="fas fa-gift"></i>
				<span>Plan Gratuit</span>
			<?php endif; ?>
		</div>
	</div>

	<!-- Barre de progression globale -->
	<div class="onboarding_progress_section">
		<div class="progress_header">
			<span class="progress_label">Progression de votre compte</span>
			<span class="progress_percent"><?php echo $progress; ?>%</span>
		</div>
		<div class="progress_bar">
			<div class="progress_fill" style="width: <?php echo $progress; ?>%;"></div>
		</div>
	</div>

	<!-- Étapes d'onboarding -->
	<div class="onboarding_steps">
		<?php foreach ( $steps as $index => $step ) : ?>
			<div class="onboarding_step step_<?php echo esc_attr( $step['status'] ); ?> <?php echo ! empty( $step['highlight'] ) ? 'step_highlight' : ''; ?>">
				<div class="step_number">
					<?php if ( $step['status'] === 'completed' ) : ?>
						<i class="fas fa-check"></i>
					<?php elseif ( $step['status'] === 'waiting' || $step['status'] === 'in_review' ) : ?>
						<i class="fas fa-clock"></i>
					<?php elseif ( $step['status'] === 'locked' ) : ?>
						<i class="fas fa-lock"></i>
					<?php else : ?>
						<?php echo $index + 1; ?>
					<?php endif; ?>
				</div>

				<div class="step_icon">
					<i class="fas <?php echo esc_attr( $step['icon'] ); ?>"></i>
				</div>

				<div class="step_content">
					<h3 class="step_title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="step_description"><?php echo esc_html( $step['description'] ); ?></p>

					<?php if ( $step['status'] === 'in_review' ) : ?>
						<span class="step_badge badge_review">
							<i class="fas fa-hourglass-half"></i> En cours de vérification
						</span>
					<?php elseif ( $step['status'] === 'waiting' ) : ?>
						<span class="step_badge badge_waiting">
							<i class="fas fa-clock"></i> En attente
						</span>
					<?php endif; ?>
				</div>

				<div class="step_action">
					<?php if ( $step['action_url'] && $step['action_label'] ) : ?>
						<a href="<?php echo esc_url( $step['action_url'] ); ?>" class="btn_step_action <?php echo $step['status'] === 'pending' ? 'btn_primary' : 'btn_secondary'; ?>">
							<?php echo esc_html( $step['action_label'] ); ?>
							<i class="fas fa-arrow-right"></i>
						</a>
					<?php elseif ( $step['status'] === 'completed' ) : ?>
						<span class="step_completed_badge">
							<i class="fas fa-check-circle"></i> Terminé
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Section Actions rapides -->
	<div class="onboarding_quick_actions">
		<h2><i class="fas fa-bolt"></i> Actions rapides</h2>

		<div class="quick_actions_grid">
			<a href="<?php echo add_query_arg( array( 'vendor' => 'profile' ), get_myaccount_page() ); ?>" class="quick_action_card">
				<div class="action_icon"><i class="fas fa-user-edit"></i></div>
				<div class="action_content">
					<h4>Modifier mon profil</h4>
					<p>Mettez à jour les informations de votre organisation</p>
				</div>
			</a>

			<a href="<?php echo add_query_arg( array( 'vendor' => 'documents' ), get_myaccount_page() ); ?>" class="quick_action_card">
				<div class="action_icon"><i class="fas fa-folder-open"></i></div>
				<div class="action_content">
					<h4>Mes documents</h4>
					<p>Gérez vos documents de vérification</p>
				</div>
			</a>

			<?php if ( $onboarding_status['can_publish'] ) : ?>
				<a href="<?php echo add_query_arg( array( 'vendor' => 'create-event' ), get_myaccount_page() ); ?>" class="quick_action_card action_highlight">
					<div class="action_icon"><i class="fas fa-plus-circle"></i></div>
					<div class="action_content">
						<h4>Créer une activité</h4>
						<p>Publiez votre première activité !</p>
					</div>
				</a>
			<?php else : ?>
				<div class="quick_action_card action_locked">
					<div class="action_icon"><i class="fas fa-lock"></i></div>
					<div class="action_content">
						<h4>Créer une activité</h4>
						<p>Disponible après validation du compte</p>
					</div>
				</div>
			<?php endif; ?>

			<a href="<?php echo add_query_arg( array( 'vendor' => 'listing' ), get_myaccount_page() ); ?>" class="quick_action_card">
				<div class="action_icon"><i class="fas fa-list"></i></div>
				<div class="action_content">
					<h4>Mes activités</h4>
					<p>Voir et gérer toutes mes activités</p>
				</div>
			</a>
		</div>
	</div>

	<!-- Section Aide -->
	<div class="onboarding_help_section">
		<div class="help_card">
			<div class="help_icon">
				<i class="fas fa-question-circle"></i>
			</div>
			<div class="help_content">
				<h3>Besoin d'aide ?</h3>
				<p>Notre équipe est là pour vous accompagner dans vos premiers pas.</p>
				<div class="help_contacts">
					<a href="mailto:support@lehiboo.com" class="help_link">
						<i class="fas fa-envelope"></i> support@lehiboo.com
					</a>
					<a href="tel:0186761414" class="help_link">
						<i class="fas fa-phone"></i> 01 86 76 14 14
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Bouton passer l'onboarding (si déjà tout fait) -->
	<?php if ( $progress >= 60 ) : ?>
		<div class="onboarding_footer">
			<button type="button" id="btn_skip_onboarding" class="btn_skip">
				Explorer mon espace <i class="fas fa-arrow-right"></i>
			</button>
		</div>
	<?php endif; ?>

</div>

<style>
/* Onboarding Page Styles */
.onboarding_page {
	max-width: 900px;
	margin: 0 auto;
	padding: 20px;
}

/* Header */
.onboarding_header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 20px;
	margin-bottom: 30px;
	padding: 30px;
	background: linear-gradient(135deg, #FF601F 0%, #FF8A50 100%);
	border-radius: 16px;
	color: #fff;
}

.welcome_message {
	display: flex;
	align-items: flex-start;
	gap: 20px;
}

.welcome_icon {
	width: 60px;
	height: 60px;
	background: rgba(255,255,255,0.2);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28px;
	flex-shrink: 0;
}

.welcome_text h1 {
	margin: 0 0 8px 0;
	font-size: 28px;
	font-weight: 700;
}

.welcome_text p {
	margin: 0;
	font-size: 15px;
	opacity: 0.95;
	line-height: 1.5;
}

.subscription_badge_header {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 10px 16px;
	background: rgba(255,255,255,0.2);
	border-radius: 20px;
	font-size: 13px;
	font-weight: 600;
	white-space: nowrap;
}

.subscription_badge_header.premium {
	background: rgba(255,215,0,0.3);
}

/* Progress Section */
.onboarding_progress_section {
	margin-bottom: 30px;
	padding: 20px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.progress_header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.progress_label {
	font-weight: 600;
	color: #333;
}

.progress_percent {
	font-weight: 700;
	color: #FF601F;
	font-size: 18px;
}

.progress_bar {
	height: 10px;
	background: #E5E7EB;
	border-radius: 5px;
	overflow: hidden;
}

.progress_fill {
	height: 100%;
	background: linear-gradient(90deg, #FF601F 0%, #FF8A50 100%);
	border-radius: 5px;
	transition: width 0.5s ease;
}

/* Steps */
.onboarding_steps {
	display: flex;
	flex-direction: column;
	gap: 16px;
	margin-bottom: 40px;
}

.onboarding_step {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 20px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
	border-left: 4px solid #E5E7EB;
	transition: all 0.3s ease;
}

.onboarding_step.step_completed {
	border-left-color: #27AE60;
	background: #F0FDF4;
}

.onboarding_step.step_pending {
	border-left-color: #FF601F;
}

.onboarding_step.step_waiting,
.onboarding_step.step_in_review {
	border-left-color: #F59E0B;
	background: #FFFBEB;
}

.onboarding_step.step_locked {
	opacity: 0.7;
}

.onboarding_step.step_ready {
	border-left-color: #27AE60;
	background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
}

.onboarding_step.step_highlight {
	border-left-color: #FF601F;
	background: linear-gradient(135deg, #FFF4EF 0%, #FFE4D6 100%);
	animation: pulse-highlight 2s infinite;
}

@keyframes pulse-highlight {
	0%, 100% { box-shadow: 0 2px 8px rgba(255,96,31,0.1); }
	50% { box-shadow: 0 4px 20px rgba(255,96,31,0.25); }
}

.step_number {
	width: 36px;
	height: 36px;
	background: #E5E7EB;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	color: #6B7280;
	flex-shrink: 0;
}

.step_completed .step_number {
	background: #27AE60;
	color: #fff;
}

.step_pending .step_number {
	background: #FF601F;
	color: #fff;
}

.step_waiting .step_number,
.step_in_review .step_number {
	background: #F59E0B;
	color: #fff;
}

.step_icon {
	width: 48px;
	height: 48px;
	background: #F3F4F6;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20px;
	color: #6B7280;
	flex-shrink: 0;
}

.step_completed .step_icon {
	background: #DCFCE7;
	color: #27AE60;
}

.step_pending .step_icon {
	background: #FFF4EF;
	color: #FF601F;
}

.step_content {
	flex: 1;
}

.step_title {
	margin: 0 0 4px 0;
	font-size: 16px;
	font-weight: 600;
	color: #1F2937;
}

.step_description {
	margin: 0;
	font-size: 14px;
	color: #6B7280;
}

.step_badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	margin-top: 8px;
	padding: 4px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 500;
}

.badge_review {
	background: #FEF3C7;
	color: #92400E;
}

.badge_waiting {
	background: #E5E7EB;
	color: #4B5563;
}

.step_action {
	flex-shrink: 0;
}

.btn_step_action {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 18px;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	text-decoration: none;
	transition: all 0.2s ease;
}

.btn_step_action.btn_primary {
	background: #FF601F;
	color: #fff;
}

.btn_step_action.btn_primary:hover {
	background: #E55519;
	transform: translateY(-2px);
}

.btn_step_action.btn_secondary {
	background: #F3F4F6;
	color: #374151;
}

.btn_step_action.btn_secondary:hover {
	background: #E5E7EB;
}

.step_completed_badge {
	color: #27AE60;
	font-weight: 600;
	font-size: 14px;
}

/* Quick Actions */
.onboarding_quick_actions {
	margin-bottom: 40px;
}

.onboarding_quick_actions h2 {
	display: flex;
	align-items: center;
	gap: 10px;
	margin: 0 0 20px 0;
	font-size: 18px;
	font-weight: 600;
	color: #1F2937;
}

.onboarding_quick_actions h2 i {
	color: #FF601F;
}

.quick_actions_grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 16px;
}

.quick_action_card {
	display: flex;
	align-items: center;
	gap: 16px;
	padding: 20px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
	text-decoration: none;
	transition: all 0.3s ease;
	border: 2px solid transparent;
}

.quick_action_card:hover {
	transform: translateY(-3px);
	box-shadow: 0 8px 25px rgba(0,0,0,0.1);
	border-color: #FF601F;
}

.quick_action_card.action_highlight {
	background: linear-gradient(135deg, #FFF4EF 0%, #FFE4D6 100%);
	border-color: #FF601F;
}

.quick_action_card.action_locked {
	opacity: 0.6;
	pointer-events: none;
}

.action_icon {
	width: 50px;
	height: 50px;
	background: #F3F4F6;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 22px;
	color: #FF601F;
	flex-shrink: 0;
}

.action_locked .action_icon {
	color: #9CA3AF;
}

.action_content h4 {
	margin: 0 0 4px 0;
	font-size: 15px;
	font-weight: 600;
	color: #1F2937;
}

.action_content p {
	margin: 0;
	font-size: 13px;
	color: #6B7280;
}

/* Help Section */
.onboarding_help_section {
	margin-bottom: 30px;
}

.help_card {
	display: flex;
	align-items: center;
	gap: 20px;
	padding: 24px;
	background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
	border-radius: 12px;
	border: 1px solid #C7D2FE;
}

.help_icon {
	width: 56px;
	height: 56px;
	background: #6366F1;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24px;
	color: #fff;
	flex-shrink: 0;
}

.help_content h3 {
	margin: 0 0 6px 0;
	font-size: 17px;
	font-weight: 600;
	color: #1F2937;
}

.help_content p {
	margin: 0 0 12px 0;
	font-size: 14px;
	color: #4B5563;
}

.help_contacts {
	display: flex;
	gap: 20px;
}

.help_link {
	display: flex;
	align-items: center;
	gap: 8px;
	color: #4F46E5;
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
}

.help_link:hover {
	text-decoration: underline;
}

/* Footer */
.onboarding_footer {
	text-align: center;
}

.btn_skip {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 14px 28px;
	background: #1F2937;
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 15px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s ease;
}

.btn_skip:hover {
	background: #374151;
	transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
	.onboarding_header {
		flex-direction: column;
	}

	.welcome_message {
		flex-direction: column;
		text-align: center;
	}

	.onboarding_step {
		flex-wrap: wrap;
	}

	.step_action {
		width: 100%;
		margin-top: 12px;
	}

	.btn_step_action {
		width: 100%;
		justify-content: center;
	}

	.quick_actions_grid {
		grid-template-columns: 1fr;
	}

	.help_card {
		flex-direction: column;
		text-align: center;
	}

	.help_contacts {
		flex-direction: column;
		gap: 10px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Skip onboarding
	$('#btn_skip_onboarding').on('click', function() {
		$.ajax({
			url: lehiboo_onboarding.ajax_url,
			type: 'POST',
			data: {
				action: 'lehiboo_skip_onboarding',
				nonce: lehiboo_onboarding.nonce
			},
			success: function(response) {
				if (response.success && response.data.redirect_url) {
					window.location.href = response.data.redirect_url;
				}
			}
		});
	});
});
</script>
