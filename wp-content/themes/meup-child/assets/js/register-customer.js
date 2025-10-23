/**
 * Register Customer Form JavaScript
 * Gestion du formulaire d'inscription utilisateur
 * @version 1.0.0
 */

(function($) {
	'use strict';

	const CustomerRegister = {

		/**
		 * Initialisation
		 */
		init: function() {
			this.bindEvents();
			this.initPasswordStrength();
		},

		/**
		 * Événements
		 */
		bindEvents: function() {
			// Toggle password visibility
			$(document).on('click', '.toggle_password', function() {
				CustomerRegister.togglePasswordVisibility($(this));
			});

			// Password strength
			$(document).on('input', '#customer_password', function() {
				CustomerRegister.checkPasswordStrength($(this).val());
			});

			// Password match
			$(document).on('input', '#customer_password_confirm', function() {
				CustomerRegister.checkPasswordMatch();
			});

			// Submit form
			$(document).on('submit', '#customer_register_form', function(e) {
				e.preventDefault();
				CustomerRegister.handleSubmit($(this));
			});
		},

		/**
		 * Toggle password visibility
		 */
		togglePasswordVisibility: function($button) {
			const $input = $button.siblings('input');
			const $icon = $button.find('i');

			if ($input.attr('type') === 'password') {
				$input.attr('type', 'text');
				$icon.removeClass('fa-eye').addClass('fa-eye-slash');
			} else {
				$input.attr('type', 'password');
				$icon.removeClass('fa-eye-slash').addClass('fa-eye');
			}
		},

		/**
		 * Initialiser la vérification de force du mot de passe
		 */
		initPasswordStrength: function() {
			$('#customer_password').on('input', function() {
				const password = $(this).val();
				CustomerRegister.checkPasswordStrength(password);
			});
		},

		/**
		 * Vérifier la force du mot de passe
		 */
		checkPasswordStrength: function(password) {
			const $fill = $('.strength_bar_fill');
			const $text = $('.strength_text');

			if (password.length === 0) {
				$fill.removeClass('weak medium strong').css('width', '0');
				$text.text('');
				return;
			}

			let strength = 0;
			let label = '';

			// Longueur
			if (password.length >= 8) strength++;
			if (password.length >= 12) strength++;

			// Minuscules et majuscules
			if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;

			// Chiffres
			if (/\d/.test(password)) strength++;

			// Caractères spéciaux
			if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

			// Déterminer le niveau
			if (strength <= 2) {
				$fill.removeClass('medium strong').addClass('weak');
				label = 'Mot de passe faible';
			} else if (strength <= 4) {
				$fill.removeClass('weak strong').addClass('medium');
				label = 'Mot de passe moyen';
			} else {
				$fill.removeClass('weak medium').addClass('strong');
				label = 'Mot de passe fort';
			}

			$text.text(label);
		},

		/**
		 * Vérifier si les mots de passe correspondent
		 */
		checkPasswordMatch: function() {
			const password = $('#customer_password').val();
			const confirm = $('#customer_password_confirm').val();
			const $confirmInput = $('#customer_password_confirm');

			if (confirm.length === 0) {
				$confirmInput.css('border-color', '#e0e0e0');
				return true;
			}

			if (password === confirm) {
				$confirmInput.css('border-color', '#4CAF50');
				return true;
			} else {
				$confirmInput.css('border-color', '#FF6B6B');
				return false;
			}
		},

		/**
		 * Gérer la soumission du formulaire
		 */
		handleSubmit: function($form) {
			// Validation
			if (!this.validateForm($form)) {
				return;
			}

			const $submitBtn = $form.find('.submit_button');
			const $btnText = $submitBtn.find('.button_text');
			const $btnLoader = $submitBtn.find('.button_loader');

			// Désactiver le bouton
			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnLoader.show();

			this.clearNotifications();

			// AJAX request
			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: $form.serialize() + '&action=lehiboo_customer_register',
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						CustomerRegister.showNotification('success', response.data.message);

						// Si OTP requis, charger le formulaire OTP
						if (response.data.show_otp_form) {
							setTimeout(function() {
								CustomerRegister.loadOTPForm(response.data.user_id);
							}, 1500);
						} else {
							// Connexion automatique réussie (pas d'OTP)
							setTimeout(function() {
								window.location.href = response.data.redirect_url || '/';
							}, 1500);
						}
					} else {
						CustomerRegister.showNotification('error', response.data.message);
						$submitBtn.prop('disabled', false);
						$btnText.show();
						$btnLoader.hide();
					}
				},
				error: function(xhr, status, error) {
					console.error('Erreur AJAX:', error);
					CustomerRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnLoader.hide();
				}
			});
		},

		/**
		 * Valider le formulaire
		 */
		validateForm: function($form) {
			const firstname = $('#customer_firstname').val().trim();
			const lastname = $('#customer_lastname').val().trim();
			const email = $('#customer_email').val().trim();
			const password = $('#customer_password').val();
			const passwordConfirm = $('#customer_password_confirm').val();
			const termsAccepted = $('#customer_terms').is(':checked');

			// Vérifier les champs requis
			if (!firstname || !lastname || !email || !password || !passwordConfirm) {
				this.showNotification('error', 'Veuillez remplir tous les champs obligatoires.');
				return false;
			}

			// Vérifier l'email
			if (!this.isValidEmail(email)) {
				this.showNotification('error', 'Veuillez entrer une adresse email valide.');
				return false;
			}

			// Vérifier la longueur du mot de passe
			if (password.length < 8) {
				this.showNotification('error', 'Le mot de passe doit contenir au moins 8 caractères.');
				return false;
			}

			// Vérifier que les mots de passe correspondent
			if (password !== passwordConfirm) {
				this.showNotification('error', 'Les mots de passe ne correspondent pas.');
				return false;
			}

			// Vérifier l'acceptation des CGU
			if (!termsAccepted) {
				this.showNotification('error', 'Vous devez accepter les Conditions Générales d\'Utilisation.');
				return false;
			}

			return true;
		},

		/**
		 * Valider le format email
		 */
		isValidEmail: function(email) {
			const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return regex.test(email);
		},

		/**
		 * Afficher une notification
		 */
		showNotification: function(type, message) {
			const $notification = $(`.register_notification.${type}`);
			$notification.html(message).fadeIn(300);

			// Auto-hide après 5 secondes pour les succès
			if (type === 'success') {
				setTimeout(function() {
					$notification.fadeOut(300);
				}, 5000);
			}

			// Scroll vers le haut
			$('html, body').animate({
				scrollTop: $('.register_form_container').offset().top - 100
			}, 500);
		},

		/**
		 * Effacer les notifications
		 */
		clearNotifications: function() {
			$('.register_notification').hide().html('');
		},

		/**
		 * Charger le formulaire OTP
		 */
		loadOTPForm: function(userId) {
			// Remplacer le contenu du formulaire par le formulaire OTP
			$.ajax({
				url: lehiboo_register_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'load_otp_template',
					user_id: userId
				},
				success: function(response) {
					if (response.success) {
						// Remplacer le contenu
						$('.register_form_inner').html(response.data.html);

						// Charger le script OTP si pas déjà chargé
						if (typeof OTPVerification === 'undefined') {
							CustomerRegister.loadOTPScript();
						} else {
							// Si déjà chargé, réinitialiser
							if (typeof OTPVerification.init === 'function') {
								OTPVerification.init();
							}
						}

						// Changer le titre
						$('.register_title').text('Vérification de votre email');
						$('.register_subtitle').text('Entrez le code à 6 chiffres envoyé à votre adresse email');
					}
				}
			});
		},

		/**
		 * Charger le script OTP dynamiquement
		 */
		loadOTPScript: function() {
			if (!$('script[src*="otp-verification.js"]').length) {
				const script = document.createElement('script');
				script.src = lehiboo_register_ajax.otp_script_url;
				script.onload = function() {
					if (typeof OTPVerification !== 'undefined' && typeof OTPVerification.init === 'function') {
						OTPVerification.init();
					}
				};
				document.head.appendChild(script);
			}
		}
	};

	// Initialiser au chargement du DOM
	$(document).ready(function() {
		if ($('#customer_register_form').length) {
			CustomerRegister.init();
		}
	});

})(jQuery);
