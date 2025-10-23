/**
 * Popup Authentification - Connexion/Inscription
 * @version 1.0.0
 */

(function($) {
	'use strict';

	const AuthPopup = {

		/**
		 * Initialisation
		 */
		init: function() {
			this.bindEvents();
			this.checkButtonsRequireLogin();
		},

		/**
		 * Événements
		 */
		bindEvents: function() {
			// Fermer le popup
			$(document).on('click', '.auth_popup_close, .auth_popup_overlay', function(e) {
				if (e.target === this) {
					AuthPopup.closePopup();
				}
			});

			// Échap pour fermer
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('#auth_popup_modal').is(':visible')) {
					AuthPopup.closePopup();
				}
			});

			// Switch tabs
			$(document).on('click', '.auth_tab_btn', function() {
				const tab = $(this).data('tab');
				AuthPopup.switchTab(tab);
			});

			// Toggle password visibility
			$(document).on('click', '.toggle_password', function() {
				AuthPopup.togglePassword($(this));
			});

			// Formulaire de connexion
			$(document).on('submit', '#auth_login_form', function(e) {
				e.preventDefault();
				AuthPopup.handleLogin($(this));
			});

			// Formulaire d'inscription
			$(document).on('submit', '#auth_register_form', function(e) {
				e.preventDefault();
				AuthPopup.handleRegister($(this));
			});
		},

		/**
		 * Vérifier les boutons nécessitant une connexion
		 */
		checkButtonsRequireLogin: function() {
			// Intercepter les boutons "Envoyer un message" - avec capture priority
			// Utiliser l'événement direct sur les boutons pour être sûr de capter en premier
			$(document).on('click', '#open_contact_modal, #open_contact_form, .btn_send_message, .organizer_contact_btn', function(e) {
				const requireLogin = $(this).data('require-login');

				if (requireLogin === true || requireLogin === 'true') {
					// Bloquer TOUS les événements
					e.preventDefault();
					e.stopPropagation();
					e.stopImmediatePropagation();

					// Ouvrir la popup d'authentification
					AuthPopup.openPopup('login');
					return false;
				}
			});

			// Backup: désactiver complètement les handlers existants sur ces boutons
			// Cela garantit que même si notre handler ne s'exécute pas en premier,
			// les anciens handlers seront supprimés
			setTimeout(function() {
				$('#open_contact_modal, #open_contact_form, .btn_send_message, .organizer_contact_btn').each(function() {
					const $btn = $(this);
					const requireLogin = $btn.data('require-login');

					if (requireLogin === true || requireLogin === 'true') {
						// Retirer TOUS les événements click existants
						const originalHandlers = $._data($btn[0], 'events');
						if (originalHandlers && originalHandlers.click) {
							// Sauvegarder et nettoyer
							$btn.off('click');

							// Ajouter SEULEMENT notre handler
							$btn.on('click', function(e) {
								e.preventDefault();
								e.stopPropagation();
								e.stopImmediatePropagation();
								AuthPopup.openPopup('login');
								return false;
							});
						}
					}
				});
			}, 100);
		},

		/**
		 * Ouvrir le popup
		 */
		openPopup: function(tab = 'login') {
			// Charger le template si nécessaire
			if ($('#auth_popup_modal').length === 0) {
				AuthPopup.loadPopupTemplate(function() {
					AuthPopup.showPopup(tab);
				});
			} else {
				AuthPopup.showPopup(tab);
			}
		},

		/**
		 * Afficher le popup
		 */
		showPopup: function(tab) {
			$('#auth_popup_modal').fadeIn(300);
			$('body').addClass('modal-open').css('overflow', 'hidden');
			this.switchTab(tab);
			this.clearForms();
			this.clearNotifications();
		},

		/**
		 * Fermer le popup
		 */
		closePopup: function() {
			$('#auth_popup_modal').fadeOut(300);
			$('body').removeClass('modal-open').css('overflow', '');
			this.clearForms();
			this.clearNotifications();
		},

		/**
		 * Charger le template du popup via AJAX
		 */
		loadPopupTemplate: function(callback) {
			$.ajax({
				url: lehiboo_auth_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'load_auth_popup_template'
				},
				success: function(response) {
					if (response.success) {
						$('body').append(response.data.html);
						if (typeof callback === 'function') {
							callback();
						}
					}
				}
			});
		},

		/**
		 * Changer d'onglet
		 */
		switchTab: function(tab) {
			$('.auth_tab_btn').removeClass('active');
			$(`.auth_tab_btn[data-tab="${tab}"]`).addClass('active');

			$('.auth_tab_content').removeClass('active');
			$(`#auth_tab_${tab}`).addClass('active');

			this.clearNotifications();
		},

		/**
		 * Toggle password visibility
		 */
		togglePassword: function($button) {
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
		 * Gérer la connexion
		 */
		handleLogin: function($form) {
			const $submitBtn = $form.find('.auth_submit_btn');
			const $btnText = $submitBtn.find('.btn_text');
			const $btnLoader = $submitBtn.find('.btn_loader');

			// Désactiver le bouton
			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnLoader.show();

			this.clearNotifications('login');

			$.ajax({
				url: lehiboo_auth_ajax.ajax_url,
				type: 'POST',
				data: $form.serialize() + '&action=lehiboo_ajax_login',
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						AuthPopup.showNotification('login', 'success', response.data.message);

						// Attendre 1 seconde puis recharger la page
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						AuthPopup.showNotification('login', 'error', response.data.message);
						$submitBtn.prop('disabled', false);
						$btnText.show();
						$btnLoader.hide();
					}
				},
				error: function() {
					AuthPopup.showNotification('login', 'error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnLoader.hide();
				}
			});
		},

		/**
		 * Gérer l'inscription
		 */
		handleRegister: function($form) {
			const $submitBtn = $form.find('.auth_submit_btn');
			const $btnText = $submitBtn.find('.btn_text');
			const $btnLoader = $submitBtn.find('.btn_loader');

			// Désactiver le bouton
			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnLoader.show();

			this.clearNotifications('register');

			$.ajax({
				url: lehiboo_auth_ajax.ajax_url,
				type: 'POST',
				data: $form.serialize() + '&action=lehiboo_ajax_register',
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						AuthPopup.showNotification('register', 'success', response.data.message);

						// Si OTP requis, charger le formulaire OTP dans le popup
						if (response.data.show_otp_form) {
							setTimeout(function() {
								AuthPopup.loadOTPForm(response.data.user_id);
							}, 1500);
						} else if (response.data.otp_required && response.data.redirect_url) {
							// Redirection vers page OTP externe
							setTimeout(function() {
								window.location.href = response.data.redirect_url;
							}, 2000);
						} else {
							// Connexion automatique réussie (pas d'OTP)
							setTimeout(function() {
								window.location.reload();
							}, 1500);
						}
					} else {
						AuthPopup.showNotification('register', 'error', response.data.message);
						$submitBtn.prop('disabled', false);
						$btnText.show();
						$btnLoader.hide();
					}
				},
				error: function() {
					AuthPopup.showNotification('register', 'error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnLoader.hide();
				}
			});
		},

		/**
		 * Afficher une notification
		 */
		showNotification: function(tab, type, message) {
			const $notification = $(`#auth_tab_${tab} .auth_notification.${type}`);
			$notification.html(message).fadeIn(300);
		},

		/**
		 * Effacer les notifications
		 */
		clearNotifications: function(tab = null) {
			if (tab) {
				$(`#auth_tab_${tab} .auth_notification`).hide().html('');
			} else {
				$('.auth_notification').hide().html('');
			}
		},

		/**
		 * Effacer les formulaires
		 */
		clearForms: function() {
			$('#auth_login_form')[0].reset();
			$('#auth_register_form')[0].reset();
		},

		/**
		 * Charger le formulaire OTP dans le popup
		 */
		loadOTPForm: function(userId) {
			// Masquer les onglets
			$('.auth_tabs_nav').hide();
			$('.auth_tab_content').hide();

			// Charger le template OTP via AJAX
			$.ajax({
				url: lehiboo_auth_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'load_otp_template',
					user_id: userId
				},
				success: function(response) {
					if (response.success) {
						// Injecter le HTML OTP dans le body du popup
						$('.auth_popup_body').html(response.data.html);

						// Charger le script OTP si pas déjà chargé
						if (typeof OTPVerification === 'undefined') {
							AuthPopup.loadOTPScript();
						} else {
							// Si déjà chargé, réinitialiser
							if (typeof OTPVerification.init === 'function') {
								OTPVerification.init();
							}
						}

						// Changer le titre du popup
						$('.auth_popup_title').text('Vérification de votre email');
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
				script.src = lehiboo_auth_ajax.otp_script_url;
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
		AuthPopup.init();
	});

})(jQuery);
