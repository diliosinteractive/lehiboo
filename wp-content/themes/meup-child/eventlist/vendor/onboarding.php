<?php
/**
 * Template - Vendor Onboarding Page
 * Page d'accueil et progression pour les nouveaux partenaires
 * @version 2.1.0
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
?>

<div class="vendor_wrap">
	<?php echo el_get_template( 'vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo lehiboo_render_vendor_header_menu(); ?>
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
	</div>
</div>

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
