<?php
/**
 * Template Formulaire d'Inscription Partenaire
 * Formulaire complet pour inscription organisateur avec documents
 * @version 1.0.0
 */

// Sécurité : empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="lehiboo_register_form_wrapper vendor_register">
	<div class="container">

		<!-- Header avec retour -->
		<div class="register_form_header">
			<a href="<?php echo esc_url( remove_query_arg( 'type' ) ); ?>" class="back_link">
				<i class="fas fa-arrow-left"></i>
				<span>Retour au choix</span>
			</a>

			<div class="register_header_content">
				<div class="register_icon vendor_icon">
					<i class="fas fa-calendar-alt"></i>
				</div>
				<h1 class="register_title">Devenir Partenaire Organisateur</h1>
				<p class="register_subtitle">Créez votre profil professionnel et publiez vos activités</p>
			</div>

			<!-- Progress bar -->
			<div class="progress_bar_wrapper">
				<div class="progress_bar">
					<div class="progress_bar_fill" style="width: 33%;"></div>
				</div>
				<div class="progress_steps">
					<div class="progress_step active" data-step="1">
						<span class="step_number">1</span>
						<span class="step_label">Informations</span>
					</div>
					<div class="progress_step" data-step="2">
						<span class="step_number">2</span>
						<span class="step_label">Organisation</span>
					</div>
					<div class="progress_step" data-step="3">
						<span class="step_number">3</span>
						<span class="step_label">Documents</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Formulaire -->
		<div class="register_form_container">
			<div class="register_form_inner">

				<!-- Notifications -->
				<div class="register_notification success" style="display: none;"></div>
				<div class="register_notification error" style="display: none;"></div>

				<!-- Formulaire d'inscription partenaire -->
				<form id="vendor_register_form" class="register_form multi_step_form" method="post" enctype="multipart/form-data">

					<!-- ============================================
					     ÉTAPE 1 : INFORMATIONS PERSONNELLES
					============================================= -->
					<div class="form_step" data-step="1">
						<h2 class="step_title">
							<i class="fas fa-user-circle"></i>
							Informations personnelles
						</h2>
						<p class="step_description">Ces informations servent uniquement à la gestion de votre compte</p>

						<!-- Prénom -->
						<div class="form_group">
							<label for="vendor_firstname" class="form_label">
								Prénom du contact <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-user input_icon"></i>
								<input
									type="text"
									id="vendor_firstname"
									name="vendor_firstname"
									class="form_input"
									placeholder="Votre prénom"
									required
									autocomplete="given-name"
								>
							</div>
						</div>

						<!-- Nom -->
						<div class="form_group">
							<label for="vendor_lastname" class="form_label">
								Nom du contact <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-user input_icon"></i>
								<input
									type="text"
									id="vendor_lastname"
									name="vendor_lastname"
									class="form_input"
									placeholder="Votre nom"
									required
									autocomplete="family-name"
								>
							</div>
						</div>

						<!-- Email professionnel -->
						<div class="form_group">
							<label for="vendor_email" class="form_label">
								Email professionnel <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-envelope input_icon"></i>
								<input
									type="email"
									id="vendor_email"
									name="vendor_email"
									class="form_input"
									placeholder="contact@organisation.com"
									required
									autocomplete="email"
								>
							</div>
							<p class="field_help">Cet email servira pour vos notifications et communications</p>
						</div>

						<!-- Téléphone -->
						<div class="form_group">
							<label for="vendor_phone" class="form_label">
								Téléphone <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-phone input_icon"></i>
								<input
									type="tel"
									id="vendor_phone"
									name="vendor_phone"
									class="form_input"
									placeholder="06 12 34 56 78"
									required
									autocomplete="tel"
								>
							</div>
						</div>

						<!-- Mot de passe -->
						<div class="form_group">
							<label for="vendor_password" class="form_label">
								Mot de passe <span class="required">*</span>
							</label>
							<div class="input_wrapper password_wrapper">
								<i class="fas fa-lock input_icon"></i>
								<input
									type="password"
									id="vendor_password"
									name="vendor_password"
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
							<label for="vendor_password_confirm" class="form_label">
								Confirmer le mot de passe <span class="required">*</span>
							</label>
							<div class="input_wrapper password_wrapper">
								<i class="fas fa-lock input_icon"></i>
								<input
									type="password"
									id="vendor_password_confirm"
									name="vendor_password_confirm"
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

						<!-- Navigation -->
						<div class="form_navigation">
							<button type="button" class="btn_next" data-next="2">
								<span>Continuer</span>
								<i class="fas fa-arrow-right"></i>
							</button>
						</div>
					</div>

					<!-- ============================================
					     ÉTAPE 2 : INFORMATIONS ORGANISATION
					============================================= -->
					<div class="form_step" data-step="2" style="display: none;">
						<h2 class="step_title">
							<i class="fas fa-building"></i>
							Votre organisation
						</h2>
						<p class="step_description">Informations visibles sur votre profil public</p>

						<!-- Nom de l'organisation -->
						<div class="form_group">
							<label for="vendor_org_name" class="form_label">
								Nom de l'organisation <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-building input_icon"></i>
								<input
									type="text"
									id="vendor_org_name"
									name="vendor_org_name"
									class="form_input"
									placeholder="Ex: Atelier de Yoga Zen"
									required
								>
							</div>
							<p class="field_help">Ce nom sera affiché publiquement sur vos événements</p>
						</div>

						<!-- Type d'organisation -->
						<div class="form_group">
							<label for="vendor_org_type" class="form_label">
								Type d'organisation <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-briefcase input_icon"></i>
								<select id="vendor_org_type" name="vendor_org_type" class="form_input" required>
									<option value="">Sélectionnez...</option>
									<option value="association">Association</option>
									<option value="entreprise">Entreprise (SAS, SARL, etc.)</option>
									<option value="auto-entrepreneur">Auto-entrepreneur</option>
									<option value="collectivite">Collectivité / Organisme public</option>
									<option value="particulier">Particulier</option>
									<option value="autre">Autre</option>
								</select>
							</div>
						</div>

						<!-- SIRET/SIREN -->
						<div class="form_group">
							<label for="vendor_org_siret" class="form_label">
								SIRET / SIREN <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-id-card input_icon"></i>
								<input
									type="text"
									id="vendor_org_siret"
									name="vendor_org_siret"
									class="form_input"
									placeholder="123 456 789 00012"
									required
									pattern="[0-9\s]{9,18}"
								>
							</div>
							<p class="field_help">Numéro à 9 chiffres (SIREN) ou 14 chiffres (SIRET)</p>
						</div>

						<!-- Adresse -->
						<div class="form_group">
							<label for="vendor_org_address" class="form_label">
								Adresse complète <span class="required">*</span>
							</label>
							<div class="input_wrapper">
								<i class="fas fa-map-marker-alt input_icon"></i>
								<input
									type="text"
									id="vendor_org_address"
									name="vendor_org_address"
									class="form_input"
									placeholder="Numéro, rue"
									required
									autocomplete="street-address"
								>
							</div>
						</div>

						<!-- Ville -->
						<div class="form_row">
							<div class="form_group">
								<label for="vendor_org_city" class="form_label">
									Ville <span class="required">*</span>
								</label>
								<div class="input_wrapper">
									<i class="fas fa-city input_icon"></i>
									<input
										type="text"
										id="vendor_org_city"
										name="vendor_org_city"
										class="form_input"
										placeholder="Ex: Paris"
										required
										autocomplete="address-level2"
									>
								</div>
							</div>

							<div class="form_group">
								<label for="vendor_org_zipcode" class="form_label">
									Code postal <span class="required">*</span>
								</label>
								<div class="input_wrapper">
									<i class="fas fa-mail-bulk input_icon"></i>
									<input
										type="text"
										id="vendor_org_zipcode"
										name="vendor_org_zipcode"
										class="form_input"
										placeholder="75001"
										required
										pattern="[0-9]{5}"
										autocomplete="postal-code"
									>
								</div>
							</div>
						</div>

						<!-- Site web -->
						<div class="form_group">
							<label for="vendor_org_website" class="form_label">
								Site web
							</label>
							<div class="input_wrapper">
								<i class="fas fa-globe input_icon"></i>
								<input
									type="url"
									id="vendor_org_website"
									name="vendor_org_website"
									class="form_input"
									placeholder="https://www.votre-site.com"
									autocomplete="url"
								>
							</div>
						</div>

						<!-- Description -->
						<div class="form_group">
							<label for="vendor_org_description" class="form_label">
								Présentation de votre organisation <span class="required">*</span>
							</label>
							<textarea
								id="vendor_org_description"
								name="vendor_org_description"
								class="form_textarea"
								placeholder="Présentez votre organisation, votre expérience et les types d'activités que vous proposez..."
								required
								rows="5"
								minlength="100"
							></textarea>
							<p class="field_help">Minimum 100 caractères - Cette description sera visible sur votre profil</p>
						</div>

						<!-- Catégories d'activités -->
						<div class="form_group">
							<label class="form_label">
								Catégories d'activités proposées <span class="required">*</span>
							</label>
							<div class="categories_grid">
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="sport">
									<span class="category_item">
										<i class="fas fa-running"></i>
										<span>Sport & Fitness</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="culture">
									<span class="category_item">
										<i class="fas fa-theater-masks"></i>
										<span>Culture & Arts</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="loisirs">
									<span class="category_item">
										<i class="fas fa-gamepad"></i>
										<span>Loisirs</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="bien-etre">
									<span class="category_item">
										<i class="fas fa-spa"></i>
										<span>Bien-être</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="nature">
									<span class="category_item">
										<i class="fas fa-leaf"></i>
										<span>Nature & Outdoor</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="gastronomie">
									<span class="category_item">
										<i class="fas fa-utensils"></i>
										<span>Gastronomie</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="famille">
									<span class="category_item">
										<i class="fas fa-child"></i>
										<span>Famille & Enfants</span>
									</span>
								</label>
								<label class="category_checkbox">
									<input type="checkbox" name="vendor_categories[]" value="formation">
									<span class="category_item">
										<i class="fas fa-graduation-cap"></i>
										<span>Formation & Ateliers</span>
									</span>
								</label>
							</div>
							<p class="field_help">Sélectionnez au moins une catégorie</p>
						</div>

						<!-- Navigation -->
						<div class="form_navigation">
							<button type="button" class="btn_prev" data-prev="1">
								<i class="fas fa-arrow-left"></i>
								<span>Précédent</span>
							</button>
							<button type="button" class="btn_next" data-next="3">
								<span>Continuer</span>
								<i class="fas fa-arrow-right"></i>
							</button>
						</div>
					</div>

					<!-- ============================================
					     ÉTAPE 3 : DOCUMENTS ET VALIDATION
					============================================= -->
					<div class="form_step" data-step="3" style="display: none;">
						<h2 class="step_title">
							<i class="fas fa-file-upload"></i>
							Documents justificatifs
						</h2>
						<p class="step_description">Ces documents permettent de vérifier votre identité et votre activité</p>

						<!-- Logo -->
						<div class="form_group">
							<label for="vendor_logo" class="form_label">
								Logo de votre organisation
							</label>
							<div class="file_upload_wrapper">
								<input
									type="file"
									id="vendor_logo"
									name="vendor_logo"
									accept="image/jpeg,image/png,image/jpg"
									class="file_input"
								>
								<label for="vendor_logo" class="file_upload_label">
									<i class="fas fa-cloud-upload-alt"></i>
									<span class="upload_text">Cliquez pour choisir un fichier</span>
									<span class="upload_filename"></span>
								</label>
								<p class="field_help">Format JPG ou PNG - Max 2 Mo - Ratio carré recommandé</p>
							</div>
							<div class="file_preview" id="preview_logo" style="display: none;">
								<img src="" alt="Aperçu logo">
								<button type="button" class="remove_file" data-target="vendor_logo">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>

						<!-- Photo de couverture -->
						<div class="form_group">
							<label for="vendor_cover" class="form_label">
								Photo de couverture
							</label>
							<div class="file_upload_wrapper">
								<input
									type="file"
									id="vendor_cover"
									name="vendor_cover"
									accept="image/jpeg,image/png,image/jpg"
									class="file_input"
								>
								<label for="vendor_cover" class="file_upload_label">
									<i class="fas fa-cloud-upload-alt"></i>
									<span class="upload_text">Cliquez pour choisir un fichier</span>
									<span class="upload_filename"></span>
								</label>
								<p class="field_help">Format JPG ou PNG - Max 5 Mo - Format paysage 16:9 recommandé</p>
							</div>
							<div class="file_preview" id="preview_cover" style="display: none;">
								<img src="" alt="Aperçu couverture">
								<button type="button" class="remove_file" data-target="vendor_cover">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>

						<!-- Kbis ou statuts -->
						<div class="form_group">
							<label for="vendor_kbis" class="form_label">
								Kbis ou Statuts de l'association <span class="required">*</span>
							</label>
							<div class="file_upload_wrapper">
								<input
									type="file"
									id="vendor_kbis"
									name="vendor_kbis"
									accept=".pdf,.jpg,.jpeg,.png"
									class="file_input"
									required
								>
								<label for="vendor_kbis" class="file_upload_label">
									<i class="fas fa-file-pdf"></i>
									<span class="upload_text">Cliquez pour choisir un fichier</span>
									<span class="upload_filename"></span>
								</label>
								<p class="field_help">PDF, JPG ou PNG - Max 10 Mo - Document de moins de 3 mois</p>
							</div>
						</div>

						<!-- Assurance -->
						<div class="form_group">
							<label for="vendor_insurance" class="form_label">
								Assurance Responsabilité Civile Professionnelle <span class="required">*</span>
							</label>
							<div class="file_upload_wrapper">
								<input
									type="file"
									id="vendor_insurance"
									name="vendor_insurance"
									accept=".pdf,.jpg,.jpeg,.png"
									class="file_input"
									required
								>
								<label for="vendor_insurance" class="file_upload_label">
									<i class="fas fa-shield-alt"></i>
									<span class="upload_text">Cliquez pour choisir un fichier</span>
									<span class="upload_filename"></span>
								</label>
								<p class="field_help">PDF, JPG ou PNG - Max 10 Mo - Attestation en cours de validité</p>
							</div>
						</div>

						<!-- Certifications (optionnel) -->
						<div class="form_group">
							<label for="vendor_certifications" class="form_label">
								Diplômes et certifications (optionnel)
							</label>
							<div class="file_upload_wrapper">
								<input
									type="file"
									id="vendor_certifications"
									name="vendor_certifications[]"
									accept=".pdf,.jpg,.jpeg,.png"
									class="file_input"
									multiple
								>
								<label for="vendor_certifications" class="file_upload_label">
									<i class="fas fa-certificate"></i>
									<span class="upload_text">Cliquez pour choisir des fichiers</span>
									<span class="upload_filename"></span>
								</label>
								<p class="field_help">Plusieurs fichiers possibles - PDF, JPG ou PNG - Max 10 Mo par fichier</p>
							</div>
						</div>

						<!-- CGU et Charte -->
						<div class="form_group checkbox_group">
							<label class="checkbox_label">
								<input
									type="checkbox"
									id="vendor_terms"
									name="vendor_terms"
									class="checkbox_input"
									required
								>
								<span class="checkbox_custom"></span>
								<span class="checkbox_text">
									J'accepte les
									<a href="<?php echo home_url('/conditions-generales-partenaires'); ?>" target="_blank">Conditions Générales Partenaires</a>
									et la
									<a href="<?php echo home_url('/charte-qualite'); ?>" target="_blank">Charte Qualité</a>
									<span class="required">*</span>
								</span>
							</label>
						</div>

						<!-- Consentement données -->
						<div class="form_group checkbox_group">
							<label class="checkbox_label">
								<input
									type="checkbox"
									id="vendor_data_consent"
									name="vendor_data_consent"
									class="checkbox_input"
									required
								>
								<span class="checkbox_custom"></span>
								<span class="checkbox_text">
									J'autorise Le Hiboo à traiter mes données conformément à la
									<a href="<?php echo home_url('/politique-confidentialite'); ?>" target="_blank">Politique de Confidentialité</a>
									<span class="required">*</span>
								</span>
							</label>
						</div>

						<!-- Info validation -->
						<div class="info_box">
							<i class="fas fa-info-circle"></i>
							<div class="info_content">
								<strong>Validation de votre compte</strong>
								<p>Votre demande sera examinée par notre équipe sous 48h ouvrées. Vous recevrez un email dès que votre compte sera validé et que vous pourrez publier vos premières activités.</p>
							</div>
						</div>

						<!-- Nonce de sécurité -->
						<?php wp_nonce_field( 'vendor_register_nonce', 'vendor_register_nonce' ); ?>

						<!-- Navigation et soumission -->
						<div class="form_navigation">
							<button type="button" class="btn_prev" data-prev="2">
								<i class="fas fa-arrow-left"></i>
								<span>Précédent</span>
							</button>
							<button type="submit" class="submit_button vendor_submit">
								<span class="button_text">Envoyer ma demande</span>
								<span class="button_loader" style="display: none;">
									<i class="fas fa-spinner fa-spin"></i>
								</span>
							</button>
						</div>
					</div>

				</form>

			</div>
		</div>

	</div>
</div>
