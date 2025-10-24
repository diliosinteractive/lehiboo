/**
 * Vendor Registration JavaScript
 * Version 3.1 - Fix: Loader + sélecteur container + otp_script_url
 * @version 3.1.0
 */

(function($) {
	'use strict';

	const VendorRegister = {

		currentStep: 1,
		totalSteps: 3,

		/**
		 * Initialisation
		 */
		init: function() {
			this.bindEvents();
			this.initAPIs();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Navigation entre étapes
			$(document).on('click', '.btn_next', this.nextStep.bind(this));
			$(document).on('click', '.btn_prev', this.prevStep.bind(this));

			// Toggle password visibility
			$(document).on('click', '.toggle_password', this.togglePassword);

			// Upload files
			$(document).on('click', '.upload_area', this.triggerFileUpload);
			$(document).on('change', 'input[type="file"]', this.handleFileSelect);

			// Form submission
			$('#vendor_register_form').on('submit', this.handleSubmit.bind(this));

			// Password validation
			$('#vendor_password').on('input', this.validatePassword);
			$('#vendor_password_confirm').on('input', this.checkPasswordMatch);
		},

		/**
		 * Initialize APIs (Entreprise + Adresse)
		 */
		initAPIs: function() {
			// API Recherche d'entreprise (SIREN/SIRET)
			let orgSearchTimeout;
			$('#vendor_org_name').on('input', function() {
				clearTimeout(orgSearchTimeout);
				const query = $(this).val().trim();

				if (query.length < 3) {
					$('#org_suggestions').hide();
					return;
				}

				orgSearchTimeout = setTimeout(function() {
					VendorRegister.searchEntreprise(query);
				}, 300);
			});

			// API Adresse gouv.fr
			let addressSearchTimeout;
			$('#vendor_org_address').on('input', function() {
				clearTimeout(addressSearchTimeout);
				const query = $(this).val().trim();

				if (query.length < 5) {
					$('#address_suggestions').hide();
					return;
				}

				addressSearchTimeout = setTimeout(function() {
					VendorRegister.searchAddress(query);
				}, 300);
			});

			// Click en dehors pour fermer les suggestions
			$(document).on('click', function(e) {
				if (!$(e.target).closest('#vendor_org_name, #org_suggestions').length) {
					$('#org_suggestions').hide();
				}
				if (!$(e.target).closest('#vendor_org_address, #address_suggestions').length) {
					$('#address_suggestions').hide();
				}
			});
		},

		/**
		 * API Recherche d'entreprise via API Entreprise/Annuaire
		 */
		searchEntreprise: function(query) {
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
				success: function(response) {
					if (response.results && response.results.length > 0) {
						let html = '';
						response.results.forEach(function(entreprise) {
							const nom = entreprise.nom_complet || entreprise.nom_raison_sociale;
							const siren = entreprise.siren;
							const adresse = entreprise.siege ?
								`${entreprise.siege.numero_voie || ''} ${entreprise.siege.type_voie || ''} ${entreprise.siege.libelle_voie || ''}, ${entreprise.siege.code_postal || ''} ${entreprise.siege.libelle_commune || ''}`.trim()
								: '';

							html += `
								<div class="suggestion_item" data-siren="${siren}" data-nom="${nom}" data-adresse="${adresse}">
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
						$('.suggestion_item').on('click', function() {
							const nom = $(this).data('nom');
							const siren = $(this).data('siren');
							const adresse = $(this).data('adresse');

							$('#vendor_org_name').val(nom);
							$('#vendor_org_siret').val(siren);
							if (adresse) {
								VendorRegister.parseAndFillAddress(adresse);
							}
							$suggestions.hide();
						});
					} else {
						$suggestions.html('<div class="suggestion_empty">Aucune entreprise trouvée</div>').show();
					}
				},
				error: function() {
					$suggestions.html('<div class="suggestion_error">Erreur de recherche</div>').show();
				}
			});
		},

		/**
		 * API Adresse gouv.fr
		 */
		searchAddress: function(query) {
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
				success: function(response) {
					if (response.features && response.features.length > 0) {
						let html = '';
						response.features.forEach(function(feature) {
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
						$('.suggestion_item').on('click', function() {
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
				error: function() {
					$suggestions.html('<div class="suggestion_error">Erreur de recherche</div>').show();
				}
			});
		},

		/**
		 * Parser et remplir l'adresse depuis une chaîne
		 */
		parseAndFillAddress: function(adresseComplete) {
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
		nextStep: function(e) {
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
		prevStep: function(e) {
			e.preventDefault();
			const prevStep = parseInt($(e.currentTarget).data('prev'));
			this.goToStep(prevStep);
		},

		/**
		 * Go to specific step
		 */
		goToStep: function(step) {
			// Hide current step
			$(`.form_step[data-step="${this.currentStep}"]`).removeClass('active');

			// Show new step
			$(`.form_step[data-step="${step}"]`).addClass('active');

			// Update navigation
			$('.step_nav_item').removeClass('active completed');
			for (let i = 1; i < step; i++) {
				$(`.step_nav_item[data-step="${i}"]`).addClass('completed');
			}
			$(`.step_nav_item[data-step="${step}"]`).addClass('active');

			// Update current step
			this.currentStep = step;

			// Scroll to top
			$('html, body').animate({ scrollTop: 0 }, 300);
		},

		/**
		 * Validate current step
		 */
		validateStep: function(step) {
			const $currentStep = $(`.form_step[data-step="${step}"]`);
			const $required = $currentStep.find('[required]');
			let isValid = true;

			$required.each(function() {
				const $field = $(this);
				const value = $field.val().trim();

				if (!value) {
					isValid = false;
					$field.addClass('error');
					VendorRegister.showNotification('error', 'Veuillez remplir tous les champs obligatoires.');
					return false;
				} else {
					$field.removeClass('error');
				}
			});

			// Validation spécifique step 1
			if (step === 1) {
				const password = $('#vendor_password').val();
				const passwordConfirm = $('#vendor_password_confirm').val();

				if (password !== passwordConfirm) {
					isValid = false;
					VendorRegister.showNotification('error', 'Les mots de passe ne correspondent pas.');
				}

				if (!VendorRegister.isPasswordStrong(password)) {
					isValid = false;
					VendorRegister.showNotification('error', 'Le mot de passe ne respecte pas les critères de sécurité.');
				}
			}

			// Validation spécifique step 2
			if (step === 2) {
				const categoriesChecked = $('input[name="vendor_categories[]"]:checked').length;
				if (categoriesChecked === 0) {
					isValid = false;
					VendorRegister.showNotification('error', 'Veuillez sélectionner au moins une catégorie.');
				}
			}

		// Validation spécifique step 3 (fichiers + CGU + Cloudflare)
		if (step === 3) {
			// Vérifier les uploads requis
			$('.upload_area[data-required="true"]').each(function() {
				const inputId = $(this).data('input');
				const $fileInput = $('#' + inputId);
				if (!$fileInput[0].files || $fileInput[0].files.length === 0) {
					isValid = false;
					$(this).addClass('upload_error');
					VendorRegister.showNotification('error', 'Veuillez télécharger tous les documents obligatoires.');
					return false;
				} else {
					$(this).removeClass('upload_error');
				}
			});

			// Vérifier CGU
			if (!$('#vendor_terms').is(':checked')) {
				isValid = false;
				VendorRegister.showNotification('error', 'Veuillez accepter les conditions générales d\'utilisation.');
			}

			// Vérifier Cloudflare Turnstile
			const turnstileResponse = $('.cf-turnstile').find('input[name="cf-turnstile-response"]').val();
			if (!turnstileResponse || turnstileResponse.length === 0) {
				isValid = false;
				VendorRegister.showNotification('error', 'Veuillez valider le CAPTCHA.');
			}
		}

			return isValid;
		},

		/**
		 * Toggle password visibility
		 */
		togglePassword: function(e) {
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
		validatePassword: function() {
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
		isPasswordStrong: function(password) {
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
		checkPasswordMatch: function() {
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
		triggerFileUpload: function(e) {
			if ($(e.target).is('input[type="file"]')) return;
			const inputId = $(this).data('input');
			$(`#${inputId}`).click();
		},

		/**
		 * Handle file select
		 */
		handleFileSelect: function(e) {
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
				reader.onload = function(e) {
					$preview.html(`<img src="${e.target.result}" alt="Preview"><button type="button" class="remove_file"><i class="fas fa-times"></i></button>`).show();
				};
				reader.readAsDataURL(file);
			} else {
				$preview.html(`<div class="file_name"><i class="fas fa-file-pdf"></i> ${file.name}</div><button type="button" class="remove_file"><i class="fas fa-times"></i></button>`).show();
			}

			$uploadArea.addClass('has_file');

			// Remove file event
			$preview.find('.remove_file').on('click', function(e) {
				e.stopPropagation();
				$(this).closest('.upload_area').find('input[type="file"]').val('');
				$preview.hide().html('');
				$uploadArea.removeClass('has_file');
			});
		},

		/**
		 * Handle form submission
		 */
		handleSubmit: function(e) {
			e.preventDefault();

			if (!this.validateStep(this.currentStep)) {
				return;
			}

			const $form = $('#vendor_register_form');
			const $submitBtn = $form.find('.btn_submit');
			const formData = new FormData($form[0]);

			// Add action
			formData.append('action', 'lehiboo_vendor_register');

			// Disable submit button
			$submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Envoi en cours...');

			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						VendorRegister.showNotification('success', response.data.message);

						// Vérifier si l'OTP est requis
						if (response.data.otp_required && response.data.show_otp_form) {
							console.log('Vendor Registration: OTP required, loading OTP form...');

							// Afficher un loader
							setTimeout(function() {
								VendorRegister.showNotification('success', '<i class="fas fa-spinner fa-spin"></i> Chargement du formulaire de vérification...');
							}, 1500);

							// Cacher le formulaire d'inscription
							$('#vendor_register_form').slideUp(300, function() {
								$(this).hide();
							});

							// Charger le script OTP et afficher le formulaire
							setTimeout(function() {
								VendorRegister.loadOTPScript();
								VendorRegister.showOTPForm(response.data.user_id);
							}, 2500);
						} else {
							// Redirection directe si pas d'OTP (cas par défaut, ne devrait plus arriver)
							setTimeout(function() {
								window.location.href = '/vendor-pending';
							}, 2000);
						}
					} else {
						VendorRegister.showNotification('error', response.data.message);
						$submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Soumettre ma demande');
					}
				},
				error: function(xhr, status, error) {
					console.error('Erreur AJAX:', error);
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Soumettre ma demande');
				}
			});
		},

		/**
		 * Charger le script OTP dynamiquement
		 */
		loadOTPScript: function() {
			console.log('Vendor: Loading OTP script...');

			// Vérifier si le script est déjà chargé
			if ($('script[src*="otp-verification.js"]').length) {
				console.log('Vendor: OTP script already loaded, reinitializing...');
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

			console.log('Vendor: Loading OTP script from:', scriptUrl);

			const script = document.createElement('script');
			script.src = scriptUrl;
			script.onload = function() {
				console.log('Vendor: OTP script loaded successfully');
				if (typeof OTPVerification !== 'undefined' && typeof OTPVerification.init === 'function') {
					OTPVerification.init();
				}
			};
			script.onerror = function() {
				console.error('Vendor: Failed to load OTP script from:', scriptUrl);
			};
			document.head.appendChild(script);
		},

		/**
		 * Afficher le formulaire OTP
		 */
		showOTPForm: function(userId) {
			console.log('Vendor: Showing OTP form for user ID:', userId);

			// Créer le conteneur OTP s'il n'existe pas
			let $otpContainer = $('#otp_verification_container');
			if ($otpContainer.length === 0) {
				$otpContainer = $('<div id="otp_verification_container"></div>');
				$('.lehiboo_register_form_wrapper .container').append($otpContainer);
			}

			console.log('Vendor: OTP container found/created, length:', $otpContainer.length);

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
			setTimeout(function() {
				$('html, body').animate({
					scrollTop: $otpContainer.offset().top - 100
				}, 400);
			}, 200);
		},

		/**
		 * Show notification
		 */
		showNotification: function(type, message) {
			const $notification = $(`.register_notification.${type}`);
			$notification.html(message).slideDown(300);

			setTimeout(function() {
				$notification.slideUp(300);
			}, 5000);

			// Scroll to notification
			$('html, body').animate({
				scrollTop: $notification.offset().top - 100
			}, 300);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('#vendor_register_form').length) {
			VendorRegister.init();
		}
	});

})(jQuery);
