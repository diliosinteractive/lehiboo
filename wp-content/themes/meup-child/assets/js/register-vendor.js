/**
 * Vendor Registration JavaScript
 * Version 5.0 - Refonte UX/UI avec animations
 * @version 5.0.0
 */

console.log('VendorRegister: Script chargé');

(function ($) {
	'use strict';

	console.log('VendorRegister: IIFE exécutée, jQuery disponible:', typeof $ !== 'undefined');

	const VendorRegister = {

		currentStep: 1,
		totalSteps: 5,
		storageKey: 'lehiboo_vendor_registration_draft',
		autoSaveTimer: null,
		emailVerified: false,
		selectedPlan: null,

		/**
		 * Initialisation
		 */
		init: function () {
			console.log('VendorRegister: init() appelé');
			this.bindEvents();
			this.initAPIs();
			this.initOTPInputs();
			this.restoreFormData();
			this.initAutoSave();
			this.updateProgressBar(); // Initialize progress bar
			console.log('VendorRegister: init() terminé');
		},

		/**
		 * Bind events
		 */
		bindEvents: function () {
			console.log('VendorRegister: bindEvents() appelé');
			// Navigation entre étapes
			$(document).on('click', '.btn_next', this.nextStep.bind(this));
			$(document).on('click', '.btn_prev', this.prevStep.bind(this));

			// OTP: Envoi du code
			$(document).on('click', '#btn_send_otp', this.sendOTP.bind(this));
			console.log('VendorRegister: Event #btn_send_otp lié');

			// OTP: Vérification du code
			$(document).on('click', '#btn_verify_otp', this.verifyOTP.bind(this));

			// OTP: Renvoi du code
			$(document).on('click', '#btn_resend_otp', this.resendOTP.bind(this));

			// Toggle password visibility
			$(document).on('click', '.toggle_password', this.togglePassword);

			// Upload files
			$(document).on('click', '.upload_area', this.triggerFileUpload);
			$(document).on('click', '.btn_upload_trigger', this.triggerFileUploadFromButton);
			$(document).on('change', 'input[type="file"]', this.handleFileSelect);

			// Form submission
			$('#vendor_register_form').on('submit', this.handleSubmit.bind(this));

			// Password validation
			$('#vendor_password').on('input', this.validatePassword);
			$('#vendor_password_confirm').on('input', this.checkPasswordMatch);

			// Subscription plan selection (Step 5)
			$(document).on('click', '.subscription_card, .btn_select_plan', this.selectPlan.bind(this));

			// Step 5: Update submit button state when checkboxes change
			$(document).on('change', '#vendor_terms, #vendor_privacy', this.updateSubmitButtonState.bind(this));
		},

		/**
		 * Select subscription plan
		 */
		selectPlan: function (e) {
			e.preventDefault();
			e.stopPropagation();

			const $target = $(e.currentTarget);
			const $card = $target.hasClass('subscription_card') ? $target : $target.closest('.subscription_card');
			const plan = $card.data('plan');

			if (!plan) return;

			// Update selected state
			$('.subscription_card').removeClass('selected');
			$card.addClass('selected');

			// Store selected plan
			this.selectedPlan = plan;
			$('#vendor_subscription_plan').val(plan);

			// Hide subscription error message if shown
			$('#subscription_error').slideUp(200);

			// Update button text
			$('.subscription_card .btn_select_plan').each(function () {
				const cardPlan = $(this).data('plan');
				const $icon = $(this).find('i');
				if (cardPlan === plan) {
					$(this).html('<i class="fas fa-check"></i> Sélectionné');
				} else {
					if (cardPlan === 'premium') {
						$(this).html('<i class="fas fa-crown"></i> Sélectionner');
					} else {
						$(this).html('<i class="fas fa-check-circle"></i> Sélectionner');
					}
				}
			});

			// Update submit button state
			this.updateSubmitButtonState();

			console.log('Plan sélectionné:', plan);
		},

		/**
		 * Update submit button state based on plan + checkboxes
		 */
		updateSubmitButtonState: function () {
			const planSelected = this.selectedPlan !== null;
			const termsChecked = $('#vendor_terms').is(':checked');
			const privacyChecked = $('#vendor_privacy').is(':checked');

			const canSubmit = planSelected && termsChecked && privacyChecked;

			$('.btn_submit').prop('disabled', !canSubmit);
		},

		/**
		 * Initialiser la sauvegarde automatique
		 */
		initAutoSave: function () {
			const self = this;

			// Sauvegarder automatiquement lors de la saisie (debounced)
			$('#vendor_register_form').on('input change', 'input:not([type="file"]), textarea, select', function () {
				clearTimeout(self.autoSaveTimer);
				self.autoSaveTimer = setTimeout(function () {
					self.saveFormData();
				}, 1000); // Sauvegarde 1s après la dernière modification
			});

			// Sauvegarder aussi les checkboxes/radios
			$('#vendor_register_form').on('change', 'input[type="checkbox"], input[type="radio"]', function () {
				self.saveFormData();
			});

		},

		/**
		 * Sauvegarder les données du formulaire dans localStorage
		 */
		saveFormData: function () {
			const formData = {
				step: this.currentStep,
				timestamp: Date.now(),
				fields: {}
			};

			// Sauvegarder tous les champs sauf les fichiers et mots de passe
			$('#vendor_register_form').find('input:not([type="file"]):not([type="password"]), textarea, select').each(function () {
				const $field = $(this);
				const name = $field.attr('name');

				if (!name) return;

				if ($field.attr('type') === 'checkbox') {
					formData.fields[name] = $field.is(':checked');
				} else if ($field.attr('type') === 'radio') {
					if ($field.is(':checked')) {
						formData.fields[name] = $field.val();
					}
				} else {
					formData.fields[name] = $field.val();
				}
			});

			// Sauvegarder les catégories sélectionnées
			const categories = [];
			$('input[name="vendor_categories[]"]:checked').each(function () {
				categories.push($(this).val());
			});
			if (categories.length > 0) {
				formData.fields['vendor_categories'] = categories;
			}

			// Sauvegarder les rôles sélectionnés
			const roles = [];
			$('input[name="vendor_org_roles[]"]:checked').each(function () {
				roles.push($(this).val());
			});
			if (roles.length > 0) {
				formData.fields['vendor_org_roles'] = roles;
			}

			try {
				localStorage.setItem(this.storageKey, JSON.stringify(formData));
			} catch (e) {
				console.error('Vendor: Failed to save form data', e);
			}
		},

		/**
		 * Restaurer les données du formulaire depuis localStorage
		 */
		restoreFormData: function () {
			try {
				const savedData = localStorage.getItem(this.storageKey);

				if (!savedData) {
					return;
				}

				const formData = JSON.parse(savedData);

				// Vérifier si les données ne sont pas trop anciennes (7 jours)
				const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 jours en millisecondes
				if (Date.now() - formData.timestamp > maxAge) {
					localStorage.removeItem(this.storageKey);
					return;
				}

				// Restaurer les champs
				$.each(formData.fields, function (name, value) {
					const $field = $('[name="' + name + '"]');

					if ($field.length === 0) return;

					if ($field.attr('type') === 'checkbox') {
						if (Array.isArray(value)) {
							// Pour les checkboxes multiples (catégories, rôles)
							value.forEach(function (val) {
								$('[name="' + name + '"][value="' + val + '"]').prop('checked', true);
							});
						} else {
							$field.prop('checked', value);
						}
					} else if ($field.attr('type') === 'radio') {
						$('[name="' + name + '"][value="' + value + '"]').prop('checked', true);
					} else {
						$field.val(value);
					}
				});

				// Restaurer les catégories
				if (formData.fields.vendor_categories && Array.isArray(formData.fields.vendor_categories)) {
					formData.fields.vendor_categories.forEach(function (cat) {
						$('input[name="vendor_categories[]"][value="' + cat + '"]').prop('checked', true);
					});
				}

				// Restaurer les rôles
				if (formData.fields.vendor_org_roles && Array.isArray(formData.fields.vendor_org_roles)) {
					formData.fields.vendor_org_roles.forEach(function (role) {
						$('input[name="vendor_org_roles[]"][value="' + role + '"]').prop('checked', true);
					});
				}

				// Restaurer l'étape
				if (formData.step && formData.step !== this.currentStep) {
					this.goToStep(formData.step);
				}

				// Afficher une notification
				this.showNotification('success', '<i class="fas fa-sync"></i> Vos données ont été restaurées. Vous pouvez continuer où vous vous êtes arrêté.');

			} catch (e) {
				console.error('Vendor: Failed to restore form data', e);
				localStorage.removeItem(this.storageKey);
			}
		},

		/**
		 * Nettoyer les données sauvegardées
		 */
		clearSavedData: function () {
			try {
				localStorage.removeItem(this.storageKey);
			} catch (e) {
				console.error('Vendor: Failed to clear saved data', e);
			}
		},

		/**
		 * Initialize APIs (Entreprise + Adresse)
		 */
		initAPIs: function () {
			// Initialiser Select2 pour les multi-select
			this.initSelect2();

			// API Recherche d'entreprise (SIREN/SIRET)
			let orgSearchTimeout;
			$('#vendor_org_name').on('input', function () {
				clearTimeout(orgSearchTimeout);
				const query = $(this).val().trim();

				if (query.length < 3) {
					$('#org_suggestions').hide();
					return;
				}

				orgSearchTimeout = setTimeout(function () {
					VendorRegister.searchEntreprise(query);
				}, 300);
			});

			// Auto-fill display name when org name is manually entered
			$('#vendor_org_name').on('change', function () {
				const orgName = $(this).val().trim();
				const displayName = $('#vendor_org_display_name').val().trim();
				if (orgName && !displayName) {
					$('#vendor_org_display_name').val(orgName);
				}
			});

			// API Adresse gouv.fr
			let addressSearchTimeout;
			$('#vendor_org_address').on('input', function () {
				clearTimeout(addressSearchTimeout);
				const query = $(this).val().trim();

				if (query.length < 5) {
					$('#address_suggestions').hide();
					return;
				}

				addressSearchTimeout = setTimeout(function () {
					VendorRegister.searchAddress(query);
				}, 300);
			});

			// Click en dehors pour fermer les suggestions
			$(document).on('click', function (e) {
				if (!$(e.target).closest('#vendor_org_name, #org_suggestions').length) {
					$('#org_suggestions').hide();
				}
				if (!$(e.target).closest('#vendor_org_address, #address_suggestions').length) {
					$('#address_suggestions').hide();
				}
			});
		},

		/**
		 * Initialiser Select2 pour les multi-select
		 */
		initSelect2: function () {
			if (typeof $.fn.select2 === 'function') {
				$('.select2_multi').select2({
					placeholder: 'Sélectionnez une ou plusieurs options',
					allowClear: true,
					width: '100%',
					language: {
						noResults: function () {
							return 'Aucun résultat trouvé';
						}
					}
				});
			}
		},

		/**
		 * API Recherche d'entreprise via API Entreprise/Annuaire
		 */
		searchEntreprise: function (query) {
			const $suggestions = $('#org_suggestions');
			$suggestions.html('<div class="suggestion_loading"><i class="fas fa-spinner fa-spin"></i> Recherche en cours...</div>').show();

			// Utiliser l'API Recherche Entreprise (données publiques)
			$.ajax({
				url: 'https://recherche-entreprises.api.gouv.fr/search',
				method: 'GET',
				data: {
					q: query,
					per_page: 5
				},
				dataType: 'json',
				success: function (response) {
					if (response.results && response.results.length > 0) {
						let html = '';
						response.results.forEach(function (entreprise) {
							const nom = entreprise.nom_complet || entreprise.nom_raison_sociale;
							const siren = entreprise.siren;
							const siege = entreprise.siege || {};
							const adresse = siege ?
								`${siege.numero_voie || ''} ${siege.type_voie || ''} ${siege.libelle_voie || ''}, ${siege.code_postal || ''} ${siege.libelle_commune || ''}`.trim()
								: '';

							// Données supplémentaires
							const dateCreation = entreprise.date_creation || '';
							const effectifs = siege.tranche_effectif_salarie || '';
							const natureJuridique = entreprise.nature_juridique || '';
							const codePostal = siege.code_postal || '';
							const ville = siege.libelle_commune || '';
							const rue = siege ? `${siege.numero_voie || ''} ${siege.type_voie || ''} ${siege.libelle_voie || ''}`.trim() : '';

							html += `
								<div class="suggestion_item"
									data-siren="${siren}"
									data-nom="${nom}"
									data-adresse="${adresse}"
									data-rue="${rue}"
									data-code-postal="${codePostal}"
									data-ville="${ville}"
									data-date-creation="${dateCreation}"
									data-effectifs="${effectifs}"
									data-nature-juridique="${natureJuridique}">
									<div class="suggestion_name">${nom}</div>
									<div class="suggestion_details">
										<span class="suggestion_siren">SIREN: ${siren}</span>
										${adresse ? `<span class="suggestion_address">${adresse}</span>` : ''}
									</div>
								</div>
							`;
						});
						$suggestions.html(html).show();

						// Click sur une suggestion
						$('.suggestion_item').on('click', function () {
							const $item = $(this);
							const nom = $item.data('nom');
							const siren = $item.data('siren');
							const rue = $item.data('rue');
							const codePostal = $item.data('code-postal');
							const ville = $item.data('ville');
							const dateCreation = $item.data('date-creation');
							const effectifs = $item.data('effectifs');
							const natureJuridique = $item.data('nature-juridique');

							// Remplir les champs
							$('#vendor_org_name').val(nom);
							$('#vendor_org_display_name').val(nom);
							$('#vendor_org_siret').val(siren);

							// Adresse
							if (rue) $('#vendor_org_address').val(rue);
							if (codePostal) $('#vendor_org_zipcode').val(codePostal);
							if (ville) $('#vendor_org_city').val(ville);

							// Date de création
							if (dateCreation) {
								// Format API: YYYY-MM-DD
								$('#vendor_org_creation_date').val(dateCreation);
							}

							// Effectifs (convertir la tranche en nombre approximatif)
							if (effectifs) {
								const effectifsMap = {
									'00': 0, '01': 1, '02': 3, '03': 6,
									'11': 10, '12': 20, '21': 50, '22': 100,
									'31': 200, '32': 250, '41': 500, '42': 1000,
									'51': 2000, '52': 5000, '53': 10000
								};
								const nbEffectifs = effectifsMap[effectifs] || '';
								if (nbEffectifs) {
									$('#vendor_org_effectifs').val(nbEffectifs);
								}
							}

							// Nature juridique (mapper vers nos statuts)
							if (natureJuridique) {
								VendorRegister.mapNatureJuridique(natureJuridique);
							}

							$suggestions.hide();
						});
					} else {
						$suggestions.html('<div class="suggestion_empty">Aucune entreprise trouvée</div>').show();
					}
				},
				error: function () {
					$suggestions.html('<div class="suggestion_error">Erreur de recherche</div>').show();
				}
			});
		},

		/**
		 * Mapper la nature juridique INSEE vers nos statuts
		 */
		mapNatureJuridique: function (codeNature) {
			// Codes INSEE des natures juridiques
			const mapping = {
				// Associations
				'9220': 'association_1901',
				'9221': 'association_1901',
				'9222': 'association_1901',
				'9223': 'association_1901',
				'9224': 'association_utilite_publique',
				// SARL
				'5499': 'sarl',
				'5498': 'sarl',
				'5485': 'sarl',
				// SAS
				'5710': 'sas',
				'5720': 'sas',
				// SA
				'5599': 'sa',
				'5505': 'sa',
				// Auto-entrepreneur
				'1000': 'auto_entrepreneur',
				// EI
				'1100': 'ei',
				'1200': 'ei',
				'1300': 'ei',
				// Collectivités
				'7100': 'collectivite',
				'7200': 'collectivite',
				'7300': 'collectivite',
				'7400': 'collectivite',
			};

			// Chercher le code ou son préfixe
			let statutKey = mapping[codeNature];
			if (!statutKey) {
				// Essayer avec le préfixe (2 premiers chiffres)
				const prefix = codeNature.substring(0, 2);
				if (prefix === '92') statutKey = 'association_1901';
				else if (prefix === '54') statutKey = 'sarl';
				else if (prefix === '57') statutKey = 'sas';
				else if (prefix === '55') statutKey = 'sa';
				else if (prefix === '10' || prefix === '11' || prefix === '12' || prefix === '13') statutKey = 'ei';
				else if (prefix === '71' || prefix === '72' || prefix === '73' || prefix === '74') statutKey = 'collectivite';
			}

			if (statutKey) {
				$('#vendor_org_type').val(statutKey);
			}
		},

		/**
		 * API Adresse gouv.fr
		 */
		searchAddress: function (query) {
			const $suggestions = $('#address_suggestions');
			$suggestions.html('<div class="suggestion_loading"><i class="fas fa-spinner fa-spin"></i> Recherche en cours...</div>').show();

			$.ajax({
				url: 'https://api-adresse.data.gouv.fr/search/',
				method: 'GET',
				data: {
					q: query,
					limit: 5
				},
				dataType: 'json',
				success: function (response) {
					if (response.features && response.features.length > 0) {
						let html = '';
						response.features.forEach(function (feature) {
							const address = feature.properties.label;
							const postcode = feature.properties.postcode;
							const city = feature.properties.city;
							const street = feature.properties.name;

							html += `
								<div class="suggestion_item" data-address="${street}" data-postcode="${postcode}" data-city="${city}" data-full="${address}">
									<div class="suggestion_address_full">${address}</div>
								</div>
							`;
						});
						$suggestions.html(html).show();

						// Click sur une suggestion
						$('.suggestion_item').on('click', function () {
							const address = $(this).data('address');
							const postcode = $(this).data('postcode');
							const city = $(this).data('city');
							const full = $(this).data('full');

							$('#vendor_org_address').val(address);
							$('#vendor_org_zipcode').val(postcode);
							$('#vendor_org_city').val(city);
							$suggestions.hide();
						});
					} else {
						$suggestions.html('<div class="suggestion_empty">Aucune adresse trouvée</div>').show();
					}
				},
				error: function () {
					$suggestions.html('<div class="suggestion_error">Erreur de recherche</div>').show();
				}
			});
		},

		/**
		 * Parser et remplir l'adresse depuis une chaîne
		 */
		parseAndFillAddress: function (adresseComplete) {
			const parts = adresseComplete.split(',');
			if (parts.length >= 2) {
				const street = parts[0].trim();
				const cityPart = parts[1].trim();
				const cityMatch = cityPart.match(/(\d{5})\s+(.+)/);

				if (cityMatch) {
					$('#vendor_org_address').val(street);
					$('#vendor_org_zipcode').val(cityMatch[1]);
					$('#vendor_org_city').val(cityMatch[2]);
				}
			}
		},

		/**
		 * Next step
		 */
		nextStep: function (e) {
			e.preventDefault();
			const nextStep = parseInt($(e.currentTarget).data('next'));

			if (!this.validateStep(this.currentStep)) {
				return;
			}

			this.goToStep(nextStep);
		},

		/**
		 * Previous step
		 */
		prevStep: function (e) {
			e.preventDefault();
			const prevStep = parseInt($(e.currentTarget).data('prev'));
			this.goToStep(prevStep);
		},

		/**
		 * Go to specific step
		 */
		goToStep: function (step) {
			// Hide current step
			$(`.form_step[data-step="${this.currentStep}"]`).removeClass('active');

			// Show new step
			$(`.form_step[data-step="${step}"]`).addClass('active');

			// Update navigation (new design with .step_item)
			$('.step_item').removeClass('active completed');
			for (let i = 1; i < step; i++) {
				$(`.step_item[data-step="${i}"]`).addClass('completed');
			}
			$(`.step_item[data-step="${step}"]`).addClass('active');

			// Update current step
			this.currentStep = step;

			// Update progress bar
			this.updateProgressBar();

			// Sauvegarder l'étape actuelle
			this.saveFormData();

			// Scroll to top with smooth animation
			$('html, body').animate({ scrollTop: 0 }, 400);
		},

		/**
		 * Update progress bar based on current step
		 */
		updateProgressBar: function () {
			// Calculate progress percentage (0% at step 1, 100% at step 5)
			const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
			$('#progress_fill').css('width', progress + '%');
		},

		/**
		 * Validate current step
		 */
		validateStep: function (step) {
			console.log('validateStep: Validation étape', step);
			const $currentStep = $(`.form_step[data-step="${step}"]`);
			const $required = $currentStep.find('[required]');
			let isValid = true;

			$required.each(function () {
				const $field = $(this);

				// Skip Select2 multi-selects - they're validated separately below
				if ($field.hasClass('select2_multi')) {
					return true; // continue to next field
				}

				const rawValue = $field.val();
				let isEmpty = false;

				if (Array.isArray(rawValue)) {
					isEmpty = rawValue.length === 0;
				} else {
					isEmpty = !rawValue || !rawValue.trim();
				}

				if (isEmpty) {
					isValid = false;
					$field.addClass('error');
					console.log('validateStep: Champ requis vide:', $field.attr('id') || $field.attr('name'));
					VendorRegister.showNotification('error', 'Veuillez remplir tous les champs obligatoires.');
					return false;
				} else {
					$field.removeClass('error');
				}
			});

			// Validation spécifique step 1 (Informations personnelles)
			if (step === 1 && isValid) {
				const password = $('#vendor_password').val();
				const passwordConfirm = $('#vendor_password_confirm').val();

				if (password !== passwordConfirm) {
					isValid = false;
					console.log('validateStep: Mots de passe différents');
					VendorRegister.showNotification('error', 'Les mots de passe ne correspondent pas.');
				}

				if (isValid && !VendorRegister.isPasswordStrong(password)) {
					isValid = false;
					console.log('validateStep: Mot de passe pas assez fort');
					VendorRegister.showNotification('error', 'Le mot de passe ne respecte pas les critères de sécurité.');
				}
			}

			// Validation spécifique step 2 (OTP)
			if (step === 2) {
				if (!this.emailVerified) {
					isValid = false;
					VendorRegister.showNotification('error', 'Veuillez vérifier votre adresse email.');
				}
			}

			// Validation spécifique step 3 (Mon Organisation)
			if (step === 3) {
				// Vérifier Type de structure (Select2 multi-select)
				const typesStructure = $('#vendor_types_structure').val();
				if (!typesStructure || typesStructure.length === 0) {
					isValid = false;
					$('#vendor_types_structure').next('.select2-container').addClass('select2-error');
					VendorRegister.showNotification('error', 'Veuillez sélectionner au moins un type de structure.');
					return isValid;
				} else {
					$('#vendor_types_structure').next('.select2-container').removeClass('select2-error');
				}

				// Vérifier Rôle de l'organisation (Select2 multi-select)
				const orgRoles = $('#vendor_org_roles').val();
				if (!orgRoles || orgRoles.length === 0) {
					isValid = false;
					$('#vendor_org_roles').next('.select2-container').addClass('select2-error');
					VendorRegister.showNotification('error', 'Veuillez sélectionner au moins un rôle pour votre organisation.');
					return isValid;
				} else {
					$('#vendor_org_roles').next('.select2-container').removeClass('select2-error');
				}

				// Vérifier Statut juridique (Select2 simple)
				const legalStatus = $('#vendor_org_type').val();
				if (!legalStatus || legalStatus === '') {
					isValid = false;
					$('#vendor_org_type').next('.select2-container').addClass('select2-error');
					VendorRegister.showNotification('error', 'Veuillez sélectionner votre statut juridique.');
					return isValid;
				} else {
					$('#vendor_org_type').next('.select2-container').removeClass('select2-error');
				}
			}

			// Validation spécifique step 4 (Documents - optionnels)
			if (step === 4) {
				console.log('validateStep: Validation étape 4 - Documents (optionnels)');

				// Vérifier les uploads requis (si data-required="true")
				const $requiredUploads = $('.upload_area[data-required="true"]');
				console.log('validateStep: Nombre uploads requis trouvés:', $requiredUploads.length);

				$requiredUploads.each(function () {
					const inputId = $(this).data('input');
					const $fileInput = $('#' + inputId);
					console.log('validateStep: Vérification upload', inputId, '- Files:', $fileInput[0]?.files?.length || 0);

					if (!$fileInput[0] || !$fileInput[0].files || $fileInput[0].files.length === 0) {
						isValid = false;
						$(this).addClass('upload_error');
						console.log('validateStep: Upload manquant:', inputId);
					} else {
						$(this).removeClass('upload_error');
					}
				});

				if (!isValid) {
					VendorRegister.showNotification('error', 'Veuillez télécharger tous les documents obligatoires.');
					return isValid;
				}
			}

			// Validation spécifique step 5 (Abonnement + CGU + Privacy + Cloudflare)
			if (step === 5) {
				console.log('validateStep: Validation étape 5 - Abonnement');

				// Vérifier la sélection d'un plan
				const selectedPlan = $('#vendor_subscription_plan').val();
				console.log('validateStep: Plan sélectionné:', selectedPlan);
				if (!selectedPlan) {
					isValid = false;
					$('#subscription_error').slideDown(300);
					VendorRegister.showNotification('error', 'Veuillez sélectionner un abonnement.');
					return isValid;
				} else {
					$('#subscription_error').slideUp(200);
				}

				// Vérifier CGU
				const cguChecked = $('#vendor_terms').is(':checked');
				console.log('validateStep: CGU acceptées:', cguChecked);
				if (!cguChecked) {
					isValid = false;
					VendorRegister.showNotification('error', 'Veuillez accepter les conditions générales d\'utilisation.');
					return isValid;
				}

				// Vérifier Politique de confidentialité
				const privacyChecked = $('#vendor_privacy').is(':checked');
				console.log('validateStep: Politique confidentialité acceptée:', privacyChecked);
				if (!privacyChecked) {
					isValid = false;
					VendorRegister.showNotification('error', 'Veuillez accepter la politique de confidentialité.');
					return isValid;
				}

				// Vérifier Cloudflare Turnstile
				const turnstileResponse = $('input[name="cf-turnstile-response"]').val();
				console.log('validateStep: Turnstile response:', turnstileResponse ? 'OK' : 'MANQUANT');
				if (!turnstileResponse || turnstileResponse.length === 0) {
					isValid = false;
					VendorRegister.showNotification('error', 'Veuillez valider le CAPTCHA.');
					return isValid;
				}
			}

			return isValid;
		},

		/**
		 * Initialiser les inputs OTP (6 chiffres)
		 */
		initOTPInputs: function () {
			const self = this;

			// Auto-focus sur le premier input
			$(document).on('focus', '.otp_input:first', function () {
				$(this).select();
			});

			// Gestion de la saisie des chiffres OTP
			$(document).on('input', '.otp_input', function (e) {
				const $input = $(this);
				const value = $input.val();
				const index = parseInt($input.data('index'));

				// Ne garder que les chiffres
				$input.val(value.replace(/[^0-9]/g, ''));

				// Passer au champ suivant si un chiffre est saisi
				if (value.length === 1 && index < 5) {
					$(`.otp_input[data-index="${index + 1}"]`).focus();
				}

				// Mettre à jour le code OTP complet
				self.updateOTPCode();
			});

			// Gestion du copier-coller
			$(document).on('paste', '.otp_input', function (e) {
				e.preventDefault();
				const pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
				const digits = pastedData.replace(/[^0-9]/g, '').substring(0, 6);

				if (digits.length > 0) {
					digits.split('').forEach(function (digit, i) {
						$(`.otp_input[data-index="${i}"]`).val(digit);
					});
					self.updateOTPCode();

					// Focus sur le dernier champ rempli ou le suivant
					const focusIndex = Math.min(digits.length, 5);
					$(`.otp_input[data-index="${focusIndex}"]`).focus();
				}
			});

			// Gestion de la touche Backspace
			$(document).on('keydown', '.otp_input', function (e) {
				const $input = $(this);
				const index = parseInt($input.data('index'));

				if (e.key === 'Backspace' && $input.val() === '' && index > 0) {
					$(`.otp_input[data-index="${index - 1}"]`).focus().val('');
					self.updateOTPCode();
				}
			});
		},

		/**
		 * Mettre à jour le code OTP complet
		 */
		updateOTPCode: function () {
			let code = '';
			$('.otp_input').each(function () {
				code += $(this).val();
			});
			$('#vendor_otp_code').val(code);
		},

		/**
		 * Envoyer le code OTP
		 */
		sendOTP: function (e) {
			e.preventDefault();
			console.log('sendOTP: Début de la validation étape 1');

			// Valider l'étape 1 avant d'envoyer l'OTP
			if (!this.validateStep(1)) {
				console.log('sendOTP: Validation échouée');
				return;
			}
			console.log('sendOTP: Validation réussie, envoi OTP...');

			const $btn = $('#btn_send_otp');
			const email = $('#vendor_email').val();
			const firstname = $('#vendor_firstname').val();

			// Désactiver le bouton
			$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Envoi en cours...');

			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_send_registration_otp',
					vendor_register_nonce: $('#vendor_register_nonce').val(),
					vendor_email: email,
					vendor_firstname: firstname
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						VendorRegister.showNotification('success', response.data.message);
						// Passer à l'étape 2 (OTP)
						VendorRegister.goToStep(2);
						// Focus sur le premier input OTP
						setTimeout(function () {
							$('.otp_input[data-index="0"]').focus();
						}, 400);
					} else {
						VendorRegister.showNotification('error', response.data.message);
					}
					$btn.prop('disabled', false).html('Continuer <i class="fas fa-arrow-right"></i>');
				},
				error: function () {
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$btn.prop('disabled', false).html('Continuer <i class="fas fa-arrow-right"></i>');
				}
			});
		},

		/**
		 * Vérifier le code OTP
		 */
		verifyOTP: function (e) {
			e.preventDefault();

			const $btn = $('#btn_verify_otp');
			const otpCode = $('#vendor_otp_code').val();
			const email = $('#vendor_email').val();

			if (otpCode.length !== 6) {
				VendorRegister.showNotification('error', 'Veuillez entrer le code à 6 chiffres.');
				return;
			}

			// Désactiver le bouton
			$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Vérification...');

			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_verify_registration_otp',
					vendor_register_nonce: $('#vendor_register_nonce').val(),
					vendor_email: email,
					otp_code: otpCode
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						VendorRegister.showNotification('success', response.data.message);
						VendorRegister.emailVerified = true;
						$('#vendor_email_verified').val('1');

						// Masquer le message d'erreur OTP s'il est visible
						$('.otp_error_message').hide();

						// Afficher le succès
						$('.otp_verification_wrapper').addClass('verified');

						// Passer à l'étape 3 après un court délai
						setTimeout(function () {
							VendorRegister.goToStep(3);
						}, 1500);
					} else {
						$('.otp_error_message').html(response.data.message).show();
						VendorRegister.showNotification('error', response.data.message);
						$btn.prop('disabled', false).html('Vérifier le code');

						// Ajouter l'animation d'erreur
						$('.otp_inputs_wrapper').addClass('shake');
						setTimeout(function () {
							$('.otp_inputs_wrapper').removeClass('shake');
						}, 500);
					}
				},
				error: function () {
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$btn.prop('disabled', false).html('Vérifier le code');
				}
			});
		},

		/**
		 * Renvoyer le code OTP
		 */
		resendOTP: function (e) {
			e.preventDefault();

			const $btn = $('#btn_resend_otp');
			const email = $('#vendor_email').val();
			const firstname = $('#vendor_firstname').val();

			// Désactiver le bouton
			$btn.prop('disabled', true).text('Envoi en cours...');

			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_resend_registration_otp',
					vendor_register_nonce: $('#vendor_register_nonce').val(),
					vendor_email: email,
					vendor_firstname: firstname
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						VendorRegister.showNotification('success', response.data.message);
						// Vider les inputs OTP
						$('.otp_input').val('');
						$('#vendor_otp_code').val('');
						$('.otp_input[data-index="0"]').focus();
					} else {
						VendorRegister.showNotification('error', response.data.message);
					}
					$btn.prop('disabled', false).text('Renvoyer le code');
				},
				error: function () {
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$btn.prop('disabled', false).text('Renvoyer le code');
				}
			});
		},

		/**
		 * Toggle password visibility
		 */
		togglePassword: function (e) {
			e.preventDefault();
			const $button = $(this);
			const $input = $button.siblings('input');
			const $icon = $button.find('i');

			if ($input.attr('type') === 'password') {
				$input.attr('type', 'text');
				$icon.removeClass('fa-eye-slash').addClass('fa-eye');
			} else {
				$input.attr('type', 'password');
				$icon.removeClass('fa-eye').addClass('fa-eye-slash');
			}
		},

		/**
		 * Validate password strength
		 */
		validatePassword: function () {
			const password = $(this).val();
			const isStrong = VendorRegister.isPasswordStrong(password);
			const $wrapper = $(this).closest('.form_group');

			if (password.length > 0) {
				if (isStrong) {
					$wrapper.removeClass('password_weak').addClass('password_strong');
				} else {
					$wrapper.removeClass('password_strong').addClass('password_weak');
				}
			} else {
				$wrapper.removeClass('password_weak password_strong');
			}
		},

		/**
		 * Check if password is strong
		 */
		isPasswordStrong: function (password) {
			if (password.length < 8) return false;
			if (!/[A-Z]/.test(password)) return false;
			if (!/[a-z]/.test(password)) return false;
			if (!/[0-9]/.test(password)) return false;
			if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) return false;
			return true;
		},

		/**
		 * Check password match
		 */
		checkPasswordMatch: function () {
			const password = $('#vendor_password').val();
			const passwordConfirm = $(this).val();
			const $wrapper = $(this).closest('.form_group');

			if (passwordConfirm.length > 0) {
				if (password === passwordConfirm) {
					$wrapper.removeClass('password_mismatch').addClass('password_match');
				} else {
					$wrapper.removeClass('password_match').addClass('password_mismatch');
				}
			} else {
				$wrapper.removeClass('password_match password_mismatch');
			}
		},

		/**
		 * Trigger file upload
		 */
		triggerFileUpload: function (e) {
			if ($(e.target).is('input[type="file"]')) return;
			const inputId = $(this).data('input');
			$(`#${inputId}`).click();
		},

		/**
		 * Trigger file upload from button
		 */
		triggerFileUploadFromButton: function (e) {
			e.preventDefault();
			const inputId = $(this).data('target');
			$(`#${inputId}`).click();
		},

		/**
		 * Handle file select
		 */
		handleFileSelect: function (e) {
			const file = this.files[0];
			if (!file) return;

			const $uploadArea = $(this).closest('.upload_area');
			const $preview = $uploadArea.find('.upload_preview');

			// Check file size
			const maxSize = $(this).attr('accept').includes('image') ? 5 * 1024 * 1024 : 5 * 1024 * 1024;
			if (file.size > maxSize) {
				VendorRegister.showNotification('error', 'Le fichier est trop volumineux.');
				return;
			}

			// Show preview
			if (file.type.startsWith('image/')) {
				const reader = new FileReader();
				reader.onload = function (e) {
					$preview.html(`<img src="${e.target.result}" alt="Preview"><button type="button" class="remove_file"><i class="fas fa-times"></i></button>`).show();
				};
				reader.readAsDataURL(file);
			} else {
				$preview.html(`<div class="file_name"><i class="fas fa-file-pdf"></i> ${file.name}</div><button type="button" class="remove_file"><i class="fas fa-times"></i></button>`).show();
			}

			$uploadArea.addClass('has_file');

			// Remove file event
			$preview.find('.remove_file').on('click', function (e) {
				e.stopPropagation();
				$(this).closest('.upload_area').find('input[type="file"]').val('');
				$preview.hide().html('');
				$uploadArea.removeClass('has_file');
			});
		},

		/**
		 * Handle form submission
		 */
		handleSubmit: function (e) {
			e.preventDefault();
			console.log('handleSubmit: Début, currentStep =', this.currentStep);

			if (!this.validateStep(this.currentStep)) {
				console.log('handleSubmit: Validation échouée');
				return;
			}
			console.log('handleSubmit: Validation OK, envoi du formulaire...');

			const $form = $('#vendor_register_form');
			const $submitBtn = $form.find('.btn_submit');
			const formData = new FormData($form[0]);
			const selectedPlan = this.selectedPlan;

			// Add action
			formData.append('action', 'lehiboo_vendor_register');

			// Disable submit button
			$submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création du compte...');

			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						VendorRegister.showNotification('success', response.data.message);

						// Nettoyer les données sauvegardées (inscription réussie)
						VendorRegister.clearSavedData();

						// Redirection selon le plan choisi
						let redirectUrl = response.data.redirect_url || '/member-account/';

						// Si plan premium, rediriger vers la page de paiement
						if (selectedPlan === 'premium' && response.data.payment_url) {
							redirectUrl = response.data.payment_url;
						}

						setTimeout(function () {
							window.location.href = redirectUrl;
						}, 2000);
					} else {
						VendorRegister.showNotification('error', response.data.message);
						$submitBtn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Créer le compte');
					}
				},
				error: function (xhr, status, error) {
					console.error('Erreur AJAX:', error);
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Créer le compte');
				}
			});
		},

		/**
		 * Charger le script OTP dynamiquement
		 */
		loadOTPScript: function () {
			// Vérifier si le script est déjà chargé
			if ($('script[src*="otp-verification.js"]').length) {
				if (typeof OTPVerification !== 'undefined' && typeof OTPVerification.init === 'function') {
					OTPVerification.init();
				}
				return;
			}

			// Construire l'URL du script
			let scriptUrl = lehiboo_register_ajax.otp_script_url;

			// Fallback si l'URL n'est pas définie
			if (!scriptUrl || scriptUrl === 'undefined') {
				console.warn('Vendor: OTP script URL not defined, using fallback');
				const themeUrl = window.location.origin + '/wp-content/themes/meup-child';
				scriptUrl = themeUrl + '/assets/js/otp-verification.js';
			}

			const script = document.createElement('script');
			script.src = scriptUrl;
			script.onload = function () {
				if (typeof OTPVerification !== 'undefined' && typeof OTPVerification.init === 'function') {
					OTPVerification.init();
				}
			};
			script.onerror = function () {
				console.error('Vendor: Failed to load OTP script from:', scriptUrl);
			};
			document.head.appendChild(script);
		},

		/**
		 * Afficher le formulaire OTP
		 */
		showOTPForm: function (userId) {
			// Créer le conteneur OTP s'il n'existe pas
			let $otpContainer = $('#otp_verification_container');
			if ($otpContainer.length === 0) {
				$otpContainer = $('<div id="otp_verification_container"></div>');
				$('.lehiboo_register_form_wrapper .container').append($otpContainer);
			}

			// Générer le HTML du formulaire OTP
			const otpFormHTML = `
				<div class="otp_verification_wrapper">
					<div class="otp_header">
						<div class="otp_icon">
							<i class="fas fa-envelope"></i>
						</div>
						<h2 class="otp_title">Vérification de votre email</h2>
						<p class="otp_subtitle">Entrez le code à 6 chiffres envoyé à votre adresse email</p>
					</div>

					<form id="otp_verification_form" class="otp_form">
						<input type="hidden" name="user_id" value="${userId}" id="otp_user_id">

						<div class="otp_inputs_wrapper">
							<input type="text" class="otp_digit" data-index="0" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code">
							<input type="text" class="otp_digit" data-index="1" maxlength="1" pattern="[0-9]" inputmode="numeric">
							<input type="text" class="otp_digit" data-index="2" maxlength="1" pattern="[0-9]" inputmode="numeric">
							<input type="text" class="otp_digit" data-index="3" maxlength="1" pattern="[0-9]" inputmode="numeric">
							<input type="text" class="otp_digit" data-index="4" maxlength="1" pattern="[0-9]" inputmode="numeric">
							<input type="text" class="otp_digit" data-index="5" maxlength="1" pattern="[0-9]" inputmode="numeric">
						</div>

						<button type="submit" class="otp_submit_btn">
							<span>Vérifier le code</span>
						</button>

						<div class="otp_resend_wrapper">
							<p class="otp_resend_text">
								Vous n'avez pas reçu le code ?
								<button type="button" id="resend_otp_btn" class="otp_resend_btn">
									Renvoyer le code
								</button>
							</p>
						</div>
					</form>

					<div class="otp_notification success" style="display:none;"></div>
					<div class="otp_notification error" style="display:none;"></div>
				</div>
			`;

			// Masquer la notification de chargement
			$('.register_notification').slideUp(200);

			// Injecter le formulaire et l'afficher avec animation
			$otpContainer.html(otpFormHTML).hide().slideDown(400);

			// Charger le script OTP si pas encore fait
			if (!$('script[src*="otp-verification.js"]').length) {
				this.loadOTPScript();
			} else {
				// Si déjà chargé, réinitialiser
				if (typeof OTPVerification !== 'undefined' && typeof OTPVerification.init === 'function') {
					OTPVerification.init();
				}
			}

			// Scroll vers le formulaire OTP après un court délai
			setTimeout(function () {
				$('html, body').animate({
					scrollTop: $otpContainer.offset().top - 100
				}, 400);
			}, 200);
		},

		/**
		 * Show notification
		 */
		showNotification: function (type, message) {
			const $notification = $(`.register_notification.${type}`);

			// S'assurer que l'élément existe
			if ($notification.length === 0) {
				console.warn('Notification element not found for type:', type);
				alert(message); // Fallback
				return;
			}

			// Cacher les autres notifications
			$('.register_notification').hide();

			// Afficher la notification
			$notification.html(message).slideDown(300);

			// Scroll vers le haut du formulaire pour voir la notification
			$('html, body').animate({
				scrollTop: $('.register_form_container').offset().top - 120
			}, 300);

			// Auto-hide après 6 secondes
			setTimeout(function () {
				$notification.slideUp(300);
			}, 6000);
		}
	};

	// Initialize on document ready
	$(document).ready(function () {
		if ($('#vendor_register_form').length) {
			VendorRegister.init();
		}
	});

})(jQuery);
