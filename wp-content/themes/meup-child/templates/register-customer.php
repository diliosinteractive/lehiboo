<?php
/**
 * Template Formulaire d'Inscription Utilisateur
 * Formulaire simple pour inscription client
 * @version 1.0.0
 */

// Sécurité : empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="lehiboo_register_form_wrapper customer_register">
	<div class="container">

		<!-- Header avec retour -->
		<div class="register_form_header">
			<a href="<?php echo esc_url( remove_query_arg( 'type' ) ); ?>" class="back_link">
				<i class="fas fa-arrow-left"></i>
				<span>Retour au choix</span>
			</a>

			<div class="register_header_content">
				<div class="register_icon customer_icon">
					<i class="fas fa-user"></i>
				</div>
				<h1 class="register_title">Créer un compte Utilisateur</h1>
				<p class="register_subtitle">Rejoignez Le Hiboo et découvrez des activités passionnantes</p>
			</div>
		</div>

		<!-- Formulaire -->
		<div class="register_form_container">
			<div class="register_form_inner">

				<!-- Notifications -->
				<div class="register_notification success" style="display: none;"></div>
				<div class="register_notification error" style="display: none;"></div>

				<!-- Formulaire d'inscription -->
				<form id="customer_register_form" class="register_form" method="post">

					<!-- Prénom -->
					<div class="form_group">
						<label for="customer_firstname" class="form_label">
							Prénom <span class="required">*</span>
						</label>
						<div class="input_wrapper">
							<i class="fas fa-user input_icon"></i>
							<input
								type="text"
								id="customer_firstname"
								name="customer_firstname"
								class="form_input"
								placeholder="Votre prénom"
								required
								autocomplete="given-name"
							>
						</div>
					</div>

					<!-- Nom -->
					<div class="form_group">
						<label for="customer_lastname" class="form_label">
							Nom <span class="required">*</span>
						</label>
						<div class="input_wrapper">
							<i class="fas fa-user input_icon"></i>
							<input
								type="text"
								id="customer_lastname"
								name="customer_lastname"
								class="form_input"
								placeholder="Votre nom"
								required
								autocomplete="family-name"
							>
						</div>
					</div>

					<!-- Email -->
					<div class="form_group">
						<label for="customer_email" class="form_label">
							Adresse email <span class="required">*</span>
						</label>
						<div class="input_wrapper">
							<i class="fas fa-envelope input_icon"></i>
							<input
								type="email"
								id="customer_email"
								name="customer_email"
								class="form_input"
								placeholder="votre@email.com"
								required
								autocomplete="email"
							>
						</div>
						<p class="field_help">Nous vous enverrons un code de vérification à cette adresse</p>
					</div>

					<!-- Mot de passe -->
					<div class="form_group">
						<label for="customer_password" class="form_label">
							Mot de passe <span class="required">*</span>
						</label>
						<div class="input_wrapper password_wrapper">
							<i class="fas fa-lock input_icon"></i>
							<input
								type="password"
								id="customer_password"
								name="customer_password"
								class="form_input"
								placeholder="Minimum 8 caractères"
								required
								autocomplete="new-password"
								minlength="8"
							>
							<button type="button" class="toggle_password" aria-label="Afficher le mot de passe">
								<i class="fas fa-eye"></i>
							</button>
						</div>
						<div class="password_strength">
							<div class="strength_bar">
								<div class="strength_bar_fill"></div>
							</div>
							<p class="strength_text"></p>
						</div>
					</div>

					<!-- Confirmation mot de passe -->
					<div class="form_group">
						<label for="customer_password_confirm" class="form_label">
							Confirmer le mot de passe <span class="required">*</span>
						</label>
						<div class="input_wrapper password_wrapper">
							<i class="fas fa-lock input_icon"></i>
							<input
								type="password"
								id="customer_password_confirm"
								name="customer_password_confirm"
								class="form_input"
								placeholder="Répétez votre mot de passe"
								required
								autocomplete="new-password"
								minlength="8"
							>
							<button type="button" class="toggle_password" aria-label="Afficher le mot de passe">
								<i class="fas fa-eye"></i>
							</button>
						</div>
					</div>

					<!-- CGU -->
					<div class="form_group checkbox_group">
						<label class="checkbox_label">
							<input
								type="checkbox"
								id="customer_terms"
								name="customer_terms"
								class="checkbox_input"
								required
							>
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">
								J'accepte les
								<a href="<?php echo home_url('/conditions-generales'); ?>" target="_blank">Conditions Générales d'Utilisation</a>
								et la
								<a href="<?php echo home_url('/politique-confidentialite'); ?>" target="_blank">Politique de Confidentialité</a>
								<span class="required">*</span>
							</span>
						</label>
					</div>

					<!-- Newsletter (optionnel) -->
					<div class="form_group checkbox_group">
						<label class="checkbox_label">
							<input
								type="checkbox"
								id="customer_newsletter"
								name="customer_newsletter"
								class="checkbox_input"
							>
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">
								Je souhaite recevoir les actualités et offres exclusives par email
							</span>
						</label>
					</div>

					<!-- Nonce de sécurité -->
					<?php wp_nonce_field( 'customer_register_nonce', 'customer_register_nonce' ); ?>

					<!-- Bouton de soumission -->
					<div class="form_group submit_group">
						<button type="submit" class="submit_button customer_submit">
							<span class="button_text">Créer mon compte</span>
							<span class="button_loader" style="display: none;">
								<i class="fas fa-spinner fa-spin"></i>
							</span>
						</button>
					</div>

				</form>

				<!-- Footer du formulaire -->
				<div class="register_form_footer">
					<p class="footer_text">
						Vous avez déjà un compte ?
						<a href="<?php echo esc_url( wp_login_url() ); ?>" class="login_link">
							Se connecter
						</a>
					</p>
				</div>

				<!-- Avantages -->
				<div class="register_benefits">
					<h3 class="benefits_title">En rejoignant Le Hiboo, vous pourrez :</h3>
					<ul class="benefits_list">
						<li>
							<i class="fas fa-check-circle"></i>
							<span>Réserver vos activités favorites en quelques clics</span>
						</li>
						<li>
							<i class="fas fa-check-circle"></i>
							<span>Sauvegarder vos événements préférés</span>
						</li>
						<li>
							<i class="fas fa-check-circle"></i>
							<span>Suivre l'historique de vos réservations</span>
						</li>
						<li>
							<i class="fas fa-check-circle"></i>
							<span>Partager vos expériences et laisser des avis</span>
						</li>
					</ul>
				</div>

			</div>
		</div>

	</div>
</div>
