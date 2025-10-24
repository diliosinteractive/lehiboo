/**
 * OTP Verification JavaScript
 * @version 1.1.0
 */

(function($) {
	'use strict';

	const OTPVerification = {

		countdown: 60,
		countdownInterval: null,

		/**
		 * Récupérer les données AJAX
		 * Support pour le script chargé dynamiquement ou via WordPress
		 */
		getAjaxData: function() {
			// Priorité 1: lehiboo_otp_ajax (si le script est chargé via wp_enqueue_script)
			if (typeof lehiboo_otp_ajax !== 'undefined') {
				return {
					ajax_url: lehiboo_otp_ajax.ajax_url,
					nonce: lehiboo_otp_ajax.nonce
				};
			}
			// Priorité 2: lehiboo_auth_ajax (données OTP incluses, pour chargement dynamique)
			else if (typeof lehiboo_auth_ajax !== 'undefined') {
				return {
					ajax_url: lehiboo_auth_ajax.otp_ajax_url || lehiboo_auth_ajax.ajax_url,
					nonce: lehiboo_auth_ajax.otp_nonce || lehiboo_auth_ajax.nonce
				};
			}
			// Fallback: utiliser les URLs par défaut WordPress
			else {
				console.error('OTP: Aucune donnée AJAX disponible');
				return {
					ajax_url: '/wp-admin/admin-ajax.php',
					nonce: ''
				};
			}
		},

		/**
		 * Initialisation
		 */
		init: function() {
			console.log('OTP Verification: Initializing...');
			// Nettoyer les anciens événements pour éviter les doublons
			$(document).off('input', '.otp_digit');
			$(document).off('keydown', '.otp_digit');
			$(document).off('paste', '.otp_digit');
			$(document).off('submit', '#otp_verification_form');
			$(document).off('click', '#resend_otp_btn');

			this.bindEvents();
			setTimeout(() => {
				this.focusFirstInput();
			}, 100);
		},

		/**
		 * Événements
		 */
		bindEvents: function() {
			// Auto-focus et navigation entre les inputs
			$(document).on('input', '.otp_digit', function(e) {
				OTPVerification.handleInput($(this), e);
			});

			// Gestion backspace
			$(document).on('keydown', '.otp_digit', function(e) {
				if (e.key === 'Backspace' && !$(this).val()) {
					OTPVerification.focusPreviousInput($(this));
				}
			});

			// Gestion paste (coller)
			$(document).on('paste', '.otp_digit', function(e) {
				e.preventDefault();
				OTPVerification.handlePaste(e.originalEvent);
			});

			// Submit form
			$(document).on('submit', '#otp_verification_form', function(e) {
				e.preventDefault();
				OTPVerification.verifyOTP($(this));
			});

			// Resend OTP
			$(document).on('click', '#resend_otp_btn', function(e) {
				e.preventDefault();
				OTPVerification.resendOTP($(this).data('user-id'));
			});
		},

		/**
		 * Gérer l'input d'un chiffre
		 */
		handleInput: function($input, e) {
			const value = $input.val();
			console.log('OTP: Input event on index', $input.data('index'), 'value:', value);

			// Ne garder que le dernier chiffre si plusieurs sont saisis
			if (value.length > 1) {
				$input.val(value.slice(-1));
			}

			// Valider que c'est un chiffre
			if (!/^[0-9]$/.test($input.val())) {
				$input.val('');
				return;
			}

			// Marquer comme rempli
			$input.addClass('filled');

			// Passer au champ suivant
			this.focusNextInput($input);

			// Vérifier si tous les champs sont remplis
			if (this.isCodeComplete()) {
				console.log('OTP: Code complete, submitting');
				setTimeout(function() {
					$('#otp_verification_form').submit();
				}, 100);
			}
		},

		/**
		 * Gérer le paste (collage)
		 */
		handlePaste: function(e) {
			console.log('OTP: Paste event triggered');
			const pastedData = (e.clipboardData || window.clipboardData).getData('text');
			const code = pastedData.replace(/[^0-9]/g, '').slice(0, 6);
			console.log('OTP: Pasted code:', code);

			if (code.length > 0) {
				// Remplir tous les champs à partir du code
				$('.otp_digit').each(function(index) {
					if (code[index]) {
						$(this).val(code[index]).addClass('filled');
					}
				});

				// Focus sur le dernier champ rempli
				if (code.length === 6) {
					$('.otp_digit').last().focus();
					// Auto-submit si code complet
					setTimeout(function() {
						console.log('OTP: Auto-submitting form');
						$('#otp_verification_form').submit();
					}, 100);
				} else {
					// Focus sur le prochain champ vide
					$(`.otp_digit[data-index="${code.length}"]`).focus();
				}
			}
		},

		/**
		 * Focus sur le champ suivant
		 */
		focusNextInput: function($input) {
			const index = parseInt($input.data('index'));
			const $nextInput = $(`.otp_digit[data-index="${index + 1}"]`);
			console.log('OTP: Focusing next input from', index, 'to', index + 1, 'found:', $nextInput.length);

			if ($nextInput.length) {
				$nextInput.focus().select();
			}
		},

		/**
		 * Focus sur le champ précédent
		 */
		focusPreviousInput: function($input) {
			const index = parseInt($input.data('index'));
			const $prevInput = $(`.otp_digit[data-index="${index - 1}"]`);

			if ($prevInput.length) {
				$prevInput.focus().select();
			}
		},

		/**
		 * Focus sur le premier input
		 */
		focusFirstInput: function() {
			$('.otp_digit[data-index="0"]').focus();
		},

		/**
		 * Vérifier si le code est complet
		 */
		isCodeComplete: function() {
			let complete = true;
			$('.otp_digit').each(function() {
				if (!$(this).val()) {
					complete = false;
					return false;
				}
			});
			return complete;
		},

		/**
		 * Récupérer le code OTP
		 */
		getCode: function() {
			let code = '';
			$('.otp_digit').each(function() {
				code += $(this).val();
			});
			return code;
		},

		/**
		 * Vérifier l'OTP via AJAX
		 */
		verifyOTP: function($form) {
			const code = this.getCode();

			if (code.length !== 6) {
				this.showNotification('error', 'Veuillez entrer les 6 chiffres du code.');
				return;
			}

			const $submitBtn = $form.find('.otp_submit_btn');
			const $btnText = $submitBtn.find('.btn_text');
			const $btnLoader = $submitBtn.find('.btn_loader');

			// Désactiver le bouton
			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnLoader.show();

			this.clearNotifications();

			// Récupérer les données AJAX
			const ajaxData = this.getAjaxData();

			$.ajax({
				url: ajaxData.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_verify_otp',
					user_id: $form.find('[name="user_id"]').val(),
					otp_code: code,
					otp_nonce: $form.find('[name="otp_nonce"]').val()
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						OTPVerification.showNotification('success', response.data.message);

						// Connexion automatique et redirection vers le compte
						setTimeout(function() {
							// Rediriger vers la page mon compte au lieu de recharger
							window.location.href = '/member-account/';
						}, 1500);
					} else {
						OTPVerification.showNotification('error', response.data.message);
						OTPVerification.clearInputs();
						$submitBtn.prop('disabled', false);
						$btnText.show();
						$btnLoader.hide();
					}
				},
				error: function() {
					OTPVerification.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					OTPVerification.clearInputs();
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnLoader.hide();
				}
			});
		},

		/**
		 * Renvoyer le code OTP
		 */
		resendOTP: function(userId) {
			const $btn = $('#resend_otp_btn');

			$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Envoi en cours...');
			this.clearNotifications();

			// Récupérer les données AJAX
			const ajaxData = this.getAjaxData();

			$.ajax({
				url: ajaxData.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_resend_otp',
					user_id: userId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						OTPVerification.showNotification('success', response.data.message);
						OTPVerification.startCountdown();
					} else {
						OTPVerification.showNotification('error', response.data.message);
						$btn.prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Renvoyer le code');
					}
				},
				error: function() {
					OTPVerification.showNotification('error', 'Erreur lors du renvoi du code.');
					$btn.prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Renvoyer le code');
				}
			});
		},

		/**
		 * Démarrer le compte à rebours
		 */
		startCountdown: function() {
			const $btn = $('#resend_otp_btn');
			const $countdown = $('#resend_countdown');
			const $timer = $('#countdown_timer');

			$btn.hide();
			$countdown.show();
			this.countdown = 60;
			$timer.text(this.countdown);

			this.countdownInterval = setInterval(function() {
				OTPVerification.countdown--;
				$timer.text(OTPVerification.countdown);

				if (OTPVerification.countdown <= 0) {
					clearInterval(OTPVerification.countdownInterval);
					$countdown.hide();
					$btn.show().prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Renvoyer le code');
				}
			}, 1000);
		},

		/**
		 * Afficher une notification
		 */
		showNotification: function(type, message) {
			const $notification = $(`.otp_notification.${type}`);
			$notification.html(message).fadeIn(300);
		},

		/**
		 * Effacer les notifications
		 */
		clearNotifications: function() {
			$('.otp_notification').hide().html('');
		},

		/**
		 * Effacer les inputs
		 */
		clearInputs: function() {
			$('.otp_digit').val('').removeClass('filled');
			this.focusFirstInput();
		}
	};

	// Initialiser au chargement du DOM
	$(document).ready(function() {
		if ($('#otp_verification_form').length) {
			OTPVerification.init();
		}
	});

})(jQuery);
