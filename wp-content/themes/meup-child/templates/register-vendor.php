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
					<span class="step_nav_label">Vérification email</span>
				</div>
			</div>
			<div class="step_nav_item" data-step="3">
				<div class="step_nav_bar"></div>
				<div class="step_nav_content">
					<span class="step_nav_label">Mon Organisation</span>
				</div>
			</div>
			<div class="step_nav_item" data-step="4">
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

					<div class="form_row">
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
							Poste
						</label>
						<select id="vendor_poste" name="vendor_poste" class="form_input">
							<option value="">Indiquez le poste que vous occupez au sein de la structure qui organise les activités</option>
							<?php
							// Charger les postes depuis les options admin
							$postes = get_option( 'el_postes_list', array(
								'Directeur / Directrice',
								'Responsable événementiel',
								'Chargé(e) de communication',
								'Président(e)',
								'Gérant(e)',
							) );
							foreach ( $postes as $poste ) :
								$slug = sanitize_title( $poste );
							?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $poste ); ?></option>
							<?php endforeach; ?>
							<option value="autre">Autre</option>
						</select>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary" onclick="window.location.href='<?php echo home_url('/inscription'); ?>'">Retour</button>
						<button type="button" class="btn btn_primary btn_send_otp" id="btn_send_otp">Suivant</button>
					</div>
				</div>

				<!-- ============================================
				     ÉTAPE 2 : VÉRIFICATION EMAIL (OTP)
				============================================= -->
				<div class="form_step" data-step="2">
					<div class="otp_verification_wrapper">
						<div class="otp_icon">
							<i class="fas fa-envelope"></i>
						</div>
						<h2 class="form_step_title">Vérification de votre email</h2>
						<p class="form_step_subtitle">Entrez le code à 6 chiffres envoyé à votre adresse email</p>

						<div class="otp_inputs_wrapper">
							<input type="text" class="otp_input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
							<input type="text" class="otp_input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]">
							<input type="text" class="otp_input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]">
							<input type="text" class="otp_input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]">
							<input type="text" class="otp_input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]">
							<input type="text" class="otp_input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]">
						</div>
						<input type="hidden" id="vendor_otp_code" name="vendor_otp_code" value="">
						<input type="hidden" id="vendor_email_verified" name="vendor_email_verified" value="0">

						<div class="otp_error_message" style="display: none;"></div>

						<button type="button" class="btn btn_primary btn_verify_otp" id="btn_verify_otp">Vérifier le code</button>

						<div class="otp_resend_wrapper">
							<span>Vous n'avez pas reçu le code ?</span>
							<button type="button" class="btn_resend_otp" id="btn_resend_otp">Renvoyer le code</button>
						</div>

						<div class="otp_timer" id="otp_timer" style="display: none;">
							<span>Nouveau code disponible dans <strong id="otp_countdown">60</strong>s</span>
						</div>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary btn_prev" data-prev="1">Retour</button>
					</div>
				</div>

				<!-- ============================================
				     ÉTAPE 3 : MON ORGANISATION
				============================================= -->
				<?php
				// Charger les options depuis WP Admin
				$types_structure = get_option( 'el_types_structure_list', array(
					'culturel' => 'Centre culturel / associatif / d\'activités',
					'sportif' => 'Lieu sportif',
					'educatif' => 'Établissement éducatif',
					'loisirs' => 'Parc de loisirs',
					'artistique' => 'Lieu artistique / créatif',
					'autre' => 'Autre',
				) );

				$org_roles = get_option( 'el_org_roles_list', array(
					'prestataire_evenementiel' => 'Prestataire de services événementiels',
					'structure_culturelle' => 'Structure culturelle / artistique',
					'producteur' => 'Producteur / Porteur de projets',
					'office_tourisme' => 'Office de tourisme / Structure territoriale',
				) );

				$statuts_juridiques = get_option( 'el_statuts_juridiques_list', array(
					'association_1901' => 'Association loi 1901',
					'association_utilite_publique' => 'Association loi 1901 reconnue d\'utilité publique',
					'sarl' => 'SARL',
					'sas' => 'SAS / SASU',
					'auto_entrepreneur' => 'Auto-entrepreneur / Micro-entreprise',
					'eirl' => 'EIRL',
					'sa' => 'SA',
					'ei' => 'Entreprise Individuelle',
					'collectivite' => 'Collectivité territoriale',
					'autre' => 'Autre',
				) );
				?>
				<div class="form_step" data-step="3">
					<h2 class="form_step_title">Mon Organisation</h2>
					<p class="form_step_subtitle">Ces informations administratives sont nécessaires pour identifier et valider votre structure.</p>

					<!-- Ligne 1 : Nom de l'Organisation + Nom à afficher -->
					<div class="form_row">
						<div class="form_group" style="position: relative;">
							<label for="vendor_org_name" class="form_label">
								Nom de l'Organisation <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_org_name"
								name="vendor_org_name"
								class="form_input"
								placeholder="Recherchez votre organisation"
								required
								autocomplete="off"
							>
							<div id="org_suggestions" class="autocomplete_suggestions" style="display: none;"></div>
							<div class="field_hint">Recherchez votre organisation pour pré-remplir automatiquement les informations ci-dessous.</div>
						</div>

						<div class="form_group">
							<label for="vendor_org_display_name" class="form_label">
								Nom à afficher <span class="required">*</span>
							</label>
							<input
								type="text"
								id="vendor_org_display_name"
								name="vendor_org_display_name"
								class="form_input"
								placeholder="Ce nom sera visible sur votre profil public"
								required
							>
							<div class="field_hint">Ce nom sera visible sur votre profil public.</div>
						</div>
					</div>

					<!-- Ligne 2 : Type de structure + Rôle de l'organisation (multi-select) -->
					<div class="form_row">
						<div class="form_group">
							<label for="vendor_types_structure" class="form_label">
								Type de structure <span class="required">*</span>
							</label>
							<select
								id="vendor_types_structure"
								name="vendor_types_structure[]"
								class="form_input select2_multi"
								multiple="multiple"
								required
							>
								<?php foreach ( $types_structure as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form_group">
							<label for="vendor_org_roles" class="form_label">
								Rôle de votre organisation <span class="required">*</span>
							</label>
							<select
								id="vendor_org_roles"
								name="vendor_org_roles[]"
								class="form_input select2_multi"
								multiple="multiple"
								required
							>
								<?php foreach ( $org_roles as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<!-- Ligne 3 : Statut juridique + SIREN -->
					<div class="form_row">
						<div class="form_group">
							<label for="vendor_org_type" class="form_label">
								Statut juridique <span class="required">*</span>
							</label>
							<select id="vendor_org_type" name="vendor_org_type" class="form_input" required>
								<option value="">Sélectionnez votre statut juridique</option>
								<?php foreach ( $statuts_juridiques as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
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
						</div>
					</div>

					<!-- Ligne 4 : Date de création + Nombre d'effectifs (non obligatoires) -->
					<div class="form_row">
						<div class="form_group">
							<label for="vendor_org_creation_date" class="form_label">
								Date de création de l'entité
							</label>
							<input
								type="date"
								id="vendor_org_creation_date"
								name="vendor_org_creation_date"
								class="form_input"
							>
						</div>

						<div class="form_group">
							<label for="vendor_org_effectifs" class="form_label">
								Nombre d'effectifs
							</label>
							<input
								type="number"
								id="vendor_org_effectifs"
								name="vendor_org_effectifs"
								class="form_input"
								placeholder="Ex: 5"
								min="0"
							>
						</div>
					</div>

					<!-- Section Adresse -->
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

					<!-- Section Image de couverture + Logo (non obligatoires) -->
					<div class="org_images_row">
						<div class="upload_group">
							<label class="upload_label">Image de couverture</label>
							<div class="upload_area upload_area_cover" data-input="vendor_cover">
								<i class="fas fa-cloud-upload-alt"></i>
								<p>Glissez vos fichiers ici</p>
								<span class="upload_hint">JPG, PNG - 1200x400px recommandé - Max 10 MO</span>
								<input type="file" id="vendor_cover" name="vendor_cover" accept="image/*" hidden>
								<div class="upload_preview" style="display: none;"></div>
							</div>
						</div>

						<div class="upload_group">
							<label class="upload_label">Logo</label>
							<div class="upload_area upload_area_logo" data-input="vendor_logo">
								<i class="fas fa-image"></i>
								<p>Cliquez pour ajouter votre logo</p>
								<span class="upload_hint">JPG, PNG - Max 5 MO</span>
								<input type="file" id="vendor_logo" name="vendor_logo" accept="image/*" hidden>
								<div class="upload_preview" style="display: none;"></div>
							</div>
						</div>
					</div>

					<!-- Description (non obligatoire) -->
					<div class="form_group">
						<label for="vendor_org_description" class="form_label">
							Description de votre Organisation
						</label>
						<textarea
							id="vendor_org_description"
							name="vendor_org_description"
							class="form_textarea"
							rows="5"
							placeholder="Décrivez brièvement votre organisation et vos activités..."
						></textarea>
					</div>

					<div class="form_actions">
						<button type="button" class="btn btn_secondary btn_prev" data-prev="2">Retour</button>
						<button type="button" class="btn btn_primary btn_next" data-next="4">Suivant</button>
					</div>
				</div>

				<!-- ============================================
				     ÉTAPE 4 : DOCUMENTS
				============================================= -->
				<div class="form_step" data-step="4">
					<h2 class="form_step_title">Documents</h2>
					<p class="form_step_subtitle">Téléchargez les documents requis pour valider votre profil</p>

					<div class="upload_group">
						<label class="upload_label">Kbis ou statuts association <span class="required">*</span></label>
						<div class="upload_area" data-input="vendor_kbis" data-required="true">
							<i class="fas fa-file-pdf"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PDF (max 5 MB)</span>
							<input type="file" id="vendor_kbis" name="vendor_kbis" accept=".pdf" hidden>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="upload_group">
						<label class="upload_label">Assurance RC Pro <span class="required">*</span></label>
						<div class="upload_area" data-input="vendor_insurance" data-required="true">
							<i class="fas fa-file-pdf"></i>
							<p>Glissez-déposez ou cliquez pour télécharger</p>
							<span class="upload_hint">PDF (max 5 MB)</span>
							<input type="file" id="vendor_insurance" name="vendor_insurance" accept=".pdf" hidden>
							<div class="upload_preview" style="display: none;"></div>
						</div>
					</div>

					<div class="form_group checkbox_group">
						<label class="checkbox_label">
							<input type="checkbox" id="vendor_terms" name="vendor_terms">
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
						<button type="button" class="btn btn_secondary btn_prev" data-prev="3">Retour</button>
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
