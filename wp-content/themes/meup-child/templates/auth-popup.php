<?php
/**
 * Template: Popup Connexion/Inscription
 *
 * Popup modal pour permettre aux utilisateurs non connectés
 * de se connecter ou s'inscrire avant d'envoyer un message
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- DEBUG: Template chargé à <?php echo date('H:i:s'); ?> - <?php echo wp_debug_backtrace_summary(); ?> -->
<!-- Popup Authentification -->
<div id="auth_popup_modal" class="auth_popup_overlay" style="display: none;">
	<div class="auth_popup_container">

		<!-- Header -->
		<div class="auth_popup_header">
			<h3 class="auth_popup_title"><?php esc_html_e( 'Connexion requise', 'meup-child' ); ?></h3>
			<button type="button" class="auth_popup_close" aria-label="<?php esc_attr_e( 'Fermer', 'meup-child' ); ?>">
				<i class="fas fa-times"></i>
			</button>
		</div>

		<!-- Tabs Navigation -->
		<div class="auth_tabs_nav">
			<button type="button" class="auth_tab_btn active" data-tab="login">
				<i class="fas fa-sign-in-alt"></i>
				<?php esc_html_e( 'Se connecter', 'meup-child' ); ?>
			</button>
			<button type="button" class="auth_tab_btn" data-tab="register">
				<i class="fas fa-user-plus"></i>
				<?php esc_html_e( 'S\'inscrire', 'meup-child' ); ?>
			</button>
		</div>

		<!-- Body -->
		<div class="auth_popup_body">

			<!-- Tab: Connexion -->
			<div class="auth_tab_content active" id="auth_tab_login">
				<p class="auth_intro_text">
					<?php esc_html_e( 'Connectez-vous pour envoyer un message à l\'organisateur.', 'meup-child' ); ?>
				</p>

				<form id="auth_login_form" class="auth_form" method="post">
					<div class="form_field">
						<label for="login_email">
							<i class="fas fa-envelope"></i>
							<?php esc_html_e( 'Email', 'meup-child' ); ?> *
						</label>
						<input type="email" id="login_email" name="login_email" required autocomplete="email">
					</div>

					<div class="form_field">
						<label for="login_password">
							<i class="fas fa-lock"></i>
							<?php esc_html_e( 'Mot de passe', 'meup-child' ); ?> *
						</label>
						<div class="password_field_wrapper">
							<input type="password" id="login_password" name="login_password" required autocomplete="current-password">
							<button type="button" class="toggle_password" aria-label="<?php esc_attr_e( 'Afficher/masquer le mot de passe', 'meup-child' ); ?>">
								<i class="fas fa-eye"></i>
							</button>
						</div>
					</div>

					<div class="form_field_checkbox">
						<label>
							<input type="checkbox" id="login_remember" name="login_remember" value="1">
							<span><?php esc_html_e( 'Se souvenir de moi', 'meup-child' ); ?></span>
						</label>
					</div>

					<?php wp_nonce_field( 'auth_login_nonce', 'login_nonce' ); ?>

					<button type="submit" class="auth_submit_btn">
						<span class="btn_text">
							<i class="fas fa-sign-in-alt"></i>
							<?php esc_html_e( 'Se connecter', 'meup-child' ); ?>
						</span>
						<span class="btn_loader" style="display: none;">
							<i class="fas fa-spinner fa-spin"></i>
						</span>
					</button>

					<div class="auth_links">
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="auth_link">
							<?php esc_html_e( 'Mot de passe oublié ?', 'meup-child' ); ?>
						</a>
					</div>
				</form>

				<!-- Notifications -->
				<div class="auth_notifications">
					<div class="auth_notification success" style="display: none;"></div>
					<div class="auth_notification error" style="display: none;"></div>
				</div>
			</div>

			<!-- Tab: Inscription -->
			<div class="auth_tab_content" id="auth_tab_register">
				<p class="auth_intro_text">
					<?php esc_html_e( 'Créez votre compte en quelques secondes pour envoyer un message.', 'meup-child' ); ?>
				</p>

				<form id="auth_register_form" class="auth_form" method="post">
					<div class="form_field">
						<label for="register_firstname">
							<i class="fas fa-user"></i>
							<?php esc_html_e( 'Prénom', 'meup-child' ); ?> *
						</label>
						<input type="text" id="register_firstname" name="register_firstname" required autocomplete="given-name">
					</div>

					<div class="form_field">
						<label for="register_lastname">
							<i class="fas fa-user"></i>
							<?php esc_html_e( 'Nom', 'meup-child' ); ?> *
						</label>
						<input type="text" id="register_lastname" name="register_lastname" required autocomplete="family-name">
					</div>

					<div class="form_field">
						<label for="register_email">
							<i class="fas fa-envelope"></i>
							<?php esc_html_e( 'Email', 'meup-child' ); ?> *
						</label>
						<input type="email" id="register_email" name="register_email" required autocomplete="email">
					</div>

					<div class="form_field_checkbox">
						<label>
							<input type="checkbox" id="register_terms" name="register_terms" required>
							<span>
								<?php
								printf(
									esc_html__( 'J\'accepte les %s et la %s', 'meup-child' ),
									'<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank">' . esc_html__( 'conditions d\'utilisation', 'meup-child' ) . '</a>',
									'<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank">' . esc_html__( 'politique de confidentialité', 'meup-child' ) . '</a>'
								);
								?>
							</span>
						</label>
					</div>

					<p class="auth_info_text">
						<i class="fas fa-info-circle"></i>
						<?php esc_html_e( 'Un code de vérification sera envoyé à votre adresse email.', 'meup-child' ); ?>
					</p>

					<?php wp_nonce_field( 'auth_register_nonce', 'register_nonce' ); ?>

					<button type="submit" class="auth_submit_btn">
						<span class="btn_text">
							<i class="fas fa-user-plus"></i>
							<?php esc_html_e( 'Créer mon compte', 'meup-child' ); ?>
						</span>
						<span class="btn_loader" style="display: none;">
							<i class="fas fa-spinner fa-spin"></i>
						</span>
					</button>
				</form>

				<!-- Notifications -->
				<div class="auth_notifications">
					<div class="auth_notification success" style="display: none;"></div>
					<div class="auth_notification error" style="display: none;"></div>
				</div>
			</div>

		</div>

	</div>
</div>
