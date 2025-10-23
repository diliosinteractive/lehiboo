<?php
/**
 * Template Formulaire d'Inscription Partenaire
 * Version 2.1 - Corrections: Orange #FF601F, API, Cloudflare, Options profil
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="lehiboo_register_form_wrapper vendor_register">
	<div class="container">

		<!-- Steps Navigation (style capture) -->
		<div class="steps_navigation">
			<div class="step_nav_item active" data-step="1">
				<div class="step_nav_bar"></div>
				<div class="step_nav_content">
					<span class="step_nav_label">Mes informations professionnelles</span>
				</div>
			</div>
			<div class="step_nav_item" data-step="2">
				<div class="step_nav_bar"></div>
				<div class="step_nav_content">
					<span class="step_nav_label">Mon Organisation</span>
				</div>
			</div>
			<div class="step_nav_item" data-step="3">
				<div class="step_nav_bar"></div>
				<div class="step_nav_content">
					<span class="step_nav_label">Documents</span>
				</div>
			</div>
		</div>

		<!-- Formulaire -->
		<div class="register_form_container">

			<!-- Notifications -->
			<div class="register_notification success" style="display: none;"></div>
			<div class="register_notification error" style="display: none;"></div>

			<!-- Formulaire d'inscription partenaire -->
			<form id="vendor_register_form" class="register_form multi_step_form" method="post" enctype="multipart/form-data">

				<!-- ============================================
				     ÉTAPE 1 : INFORMATIONS PROFESSIONNELLES
				============================================= -->
				<div class="form_step active" data-step="1">
					<h2 class="form_step_title">Mes informations professionnelles</h2>
					<p class="form_step_subtitle">Ces informations sont nécessaires pour gérer votre profil Administrateur du compte professionnel de votre organisation.</p>

					<div class="form_row">
						<div class="form_group">
							<label for="vendor_firstname" class="form_label">
								Prénom <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_firstname"
								name="vendor_firstname"
								class="form_input"
								placeholder="Mettez votre prénom"
								required
							>
						</div>

						<div class="form_group">
							<label for="vendor_lastname" class="form_label">
								Nom <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_lastname"
								name="vendor_lastname"
								class="form_input"
								placeholder="Mettez votre nom"
								required
							>
						</div>
					</div>

					<div class="form_group">
						<label for="vendor_email" class="form_label">
							Email de connexion <span class="required">*</span>
						</label>
						<input
							type="email"
							id="vendor_email"
							name="vendor_email"
							class="form_input"
							placeholder="Mettez votre e-mail professionnel"
							required
						>
					</div>

					<div class="form_group">
						<label for="vendor_phone" class="form_label">
							Téléphone
						</label>
						<input
							type="tel"
							id="vendor_phone"
							name="vendor_phone"
							class="form_input"
							placeholder="Mettez les chiffres sans espaces"
						>
					</div>

					<div class="form_row">
						<div class="form_group">
							<label for="vendor_password" class="form_label">
								Mot de passe <span class="required">*</span>
							</label>
							<div class="password_input_wrapper">
								<input
									type="password"
									id="vendor_password"
									name="vendor_password"
									class="form_input"
									placeholder="••••••••"
									required
								>
								<button type="button" class="toggle_password">
									<i class="fas fa-eye-slash"></i>
								</button>
							</div>
							<div class="password_requirements">
								Votre nouveau mot de passe doit contenir 8 caractères minimum, au moins 1 lettre Majuscule, au moins 1 minuscule, au moins 1 chiffre, et au moins 1 caractère spécial
							</div>
						</div>

						<div class="form_group">
							<label for="vendor_password_confirm" class="form_label">
								Confirmer votre mot de passe <span class="required">*</span>
							</label>
							<div class="password_input_wrapper">
								<input
									type="password"
									id="vendor_password_confirm"
									name="vendor_password_confirm"
									class="form_input"
									placeholder="Mettez les chiffres sans espaces"
									required
								>
								<button type="button" class="toggle_password">
									<i class="fas fa-eye-slash"></i>
								</button>
							</div>
						</div>
					</div>

					<div class="form_group">
						<label for="vendor_poste" class="form_label">
							Poste <span class="required">*</span>
						</label>
						<select id="vendor_poste" name="vendor_poste" class="form_input" required>
							<option value="">Indiquez le poste que vous occupez au sein de la structure qui organise les activités</option>
							<option value="presente">Présidente</option>
							<option value="president">Président</option>
							<option value="directrice">Directrice</option>
							<option value="directeur">Directeur</option>
							<option value="responsable">Responsable</option>
							<option value="animatrice">Animatrice</option>
							<option value="animateur">Animateur</option>
							<option value="benevole">Bénévole</option>
							<option value="autre">Autre</option>
						</select>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary" onclick="window.location.href='<?php echo home_url('/inscription'); ?>'">Retour</button>
						<button type="button" class="btn btn_primary btn_next" data-next="2">Suivant</button>
					</div>
				</div>

				<!-- ============================================
				     ÉTAPE 2 : MON ORGANISATION
				============================================= -->
				<div class="form_step" data-step="2">
					<h2 class="form_step_title">Mon Organisation</h2>
					<p class="form_step_subtitle">Ces informations administratives sont nécessaires pour identifier votre structure.</p>

					<div class="form_group" style="position: relative;">
						<label for="vendor_org_name" class="form_label">
							Nom de l'Organisation <span class="required">*</span>
							<button type="button" class="btn_icon_help" title="Ce nom sera affiché publiquement">
								<i class="fas fa-eye"></i>
							</button>
						</label>
						<input
							type="text"
							id="vendor_org_name"
							name="vendor_org_name"
							class="form_input"
							placeholder="Nom de la structure qui organise"
							required
							autocomplete="off"
						>
						<div id="org_suggestions" class="autocomplete_suggestions" style="display: none;"></div>
					</div>

					<div class="form_row">
						<div class="form_group">
							<label for="vendor_org_type" class="form_label">
								Statut de juridique <span class="required">*</span>
							</label>
							<select id="vendor_org_type" name="vendor_org_type" class="form_input" required>
								<option value="">Sélectionnez votre statut juridique</option>
								<option value="association">Association</option>
								<option value="entreprise">Entreprise</option>
								<option value="autoentrepreneur">Auto-entrepreneur</option>
								<option value="collectivite">Collectivité</option>
								<option value="autre">Autre</option>
							</select>
						</div>

						<div class="form_group">
							<label for="vendor_org_siret" class="form_label">
								SIREN <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_org_siret"
								name="vendor_org_siret"
								class="form_input"
								placeholder="Indiquez les 9 chiffres de votre numéro SIREN"
								maxlength="14"
								required
							>
							<div class="field_hint">Recherchez votre entreprise ci-dessus pour pré-remplir</div>
						</div>
					</div>

					<h3 class="section_subtitle">Rôle de l'Organisation <span class="required">*</span></h3>
					<p class="section_description">Plusieurs choix possibles</p>

					<div class="checkbox_group_wrapper">
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_org_roles[]" value="organisateur">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Organisateur</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_org_roles[]" value="lieu_accueil">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Lieu d'accueil</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_org_roles[]" value="prestataire">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Prestataire</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_org_roles[]" value="autre">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Autre</span>
						</label>
					</div>

					<h3 class="section_subtitle">Type de structure <span class="required">*</span></h3>
					<p class="section_description">Plusieurs choix possibles</p>

					<div class="checkbox_group_wrapper">
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="cinema">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Cinéma</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="centre_culturel">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Centre culturel</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="theatre">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Théâtre</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="musee">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Musée</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="salle_concert">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Salle de concert</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="galerie_art">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Galerie d'art</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="bibliotheque">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Bibliothèque</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="espace_sportif">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Espace sportif</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="parc_loisirs">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Parc de loisirs</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="association">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Association</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="collectivite">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Collectivité</span>
						</label>
						<label class="checkbox_label">
							<input type="checkbox" name="vendor_categories[]" value="autre">
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">Autre</span>
						</label>
					</div>

					<div class="form_group" style="position: relative;">
						<label for="vendor_org_address" class="form_label">
							Adresse <span class="required">*</span>
						</label>
						<input
							type="text"
							id="vendor_org_address"
							name="vendor_org_address"
							class="form_input"
							placeholder="Tapez votre adresse"
							required
							autocomplete="off"
						>
						<div id="address_suggestions" class="autocomplete_suggestions" style="display: none;"></div>
					</div>

					<div class="form_row">
						<div class="form_group">
							<label for="vendor_org_zipcode" class="form_label">
								Code postal <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_org_zipcode"
								name="vendor_org_zipcode"
								class="form_input"
								placeholder="75001"
								maxlength="5"
								required
							>
						</div>

						<div class="form_group">
							<label for="vendor_org_city" class="form_label">
								Ville <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_org_city"
								name="vendor_org_city"
								class="form_input"
								placeholder="Paris"
								required
							>
						</div>
					</div>

					<div class="form_group">
						<label for="vendor_org_website" class="form_label">
							Site web
						</label>
						<input
							type="url"
							id="vendor_org_website"
							name="vendor_org_website"
							class="form_input"
							placeholder="https://www.exemple.com"
						>
					</div>

					<div class="form_group">
						<label for="vendor_org_description" class="form_label">
							Description de l'activité <span class="required">*</span>
						</label>
						<textarea
							id="vendor_org_description"
							name="vendor_org_description"
							class="form_textarea"
							rows="5"
							placeholder="Décrivez brièvement votre organisation et vos activités..."
							required
						></textarea>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary btn_prev" data-prev="1">Retour</button>
						<button type="button" class="btn btn_primary btn_next" data-next="3">Suivant</button>
					</div>
				</div>

				<!-- ============================================
				     ÉTAPE 3 : DOCUMENTS
				============================================= -->
				<div class="form_step" data-step="3">
					<h2 class="form_step_title">Documents</h2>
					<p class="form_step_subtitle">Téléchargez les documents requis pour valider votre profil</p>

					<div class="upload_group">
						<label class="upload_label">Logo de l'organisation <span class="required">*</span></label>
						<div class="upload_area" data-input="vendor_logo">
							<i class="fas fa-cloud-upload-alt"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PNG, JPG (max 2 MB)</span>
							<input type="file" id="vendor_logo" name="vendor_logo" accept="image/*" hidden required>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="upload_group">
						<label class="upload_label">Image de couverture</label>
						<div class="upload_area" data-input="vendor_cover">
							<i class="fas fa-cloud-upload-alt"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PNG, JPG (max 5 MB)</span>
							<input type="file" id="vendor_cover" name="vendor_cover" accept="image/*" hidden>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="upload_group">
						<label class="upload_label">Kbis ou statuts association <span class="required">*</span></label>
						<div class="upload_area" data-input="vendor_kbis">
							<i class="fas fa-file-pdf"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PDF (max 5 MB)</span>
							<input type="file" id="vendor_kbis" name="vendor_kbis" accept=".pdf" hidden required>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="upload_group">
						<label class="upload_label">Assurance RC Pro <span class="required">*</span></label>
						<div class="upload_area" data-input="vendor_insurance">
							<i class="fas fa-file-pdf"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PDF (max 5 MB)</span>
							<input type="file" id="vendor_insurance" name="vendor_insurance" accept=".pdf" hidden required>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="form_group checkbox_group">
						<label class="checkbox_label">
							<input type="checkbox" id="vendor_terms" name="vendor_terms" required>
							<span class="checkbox_custom"></span>
							<span class="checkbox_text">
								J'accepte les <a href="/cgu" target="_blank">conditions générales d'utilisation</a> et la <a href="/charte" target="_blank">charte qualité</a>
							</span>
						</label>
					</div>

					<!-- Cloudflare Turnstile -->
					<div class="captcha_wrapper">
						<div class="cf-turnstile" data-sitekey="0x4AAAAAAB75T9T-6xfs5mqd"></div>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary btn_prev" data-prev="2">Retour</button>
						<button type="submit" class="btn btn_primary btn_submit">
							<i class="fas fa-check"></i>
							Soumettre ma demande
						</button>
					</div>
				</div>

				<!-- Nonce WordPress -->
				<?php wp_nonce_field( 'vendor_register_nonce', 'vendor_register_nonce' ); ?>

			</form>
		</div>
	</div>
</div>
