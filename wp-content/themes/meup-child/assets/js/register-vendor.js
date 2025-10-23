/**
 * Register Vendor Form JavaScript
 * Gestion formulaire multi-étapes partenaire
 * @version 1.0.0
 */

(function($) {
	'use strict';

	const VendorRegister = {
		currentStep: 1,
		totalSteps: 3,

		init: function() {
			this.bindEvents();
			this.initPasswordStrength();
		},

		bindEvents: function() {
			// Navigation entre étapes
			$(document).on('click', '.btn_next', function() {
				const nextStep = parseInt($(this).data('next'));
				VendorRegister.goToStep(nextStep);
			});

			$(document).on('click', '.btn_prev', function() {
				const prevStep = parseInt($(this).data('prev'));
				VendorRegister.goToStep(prevStep);
			});

			// Toggle password
			$(document).on('click', '.toggle_password', function() {
				VendorRegister.togglePasswordVisibility($(this));
			});

			// Password strength
			$(document).on('input', '#vendor_password', function() {
				VendorRegister.checkPasswordStrength($(this).val());
			});

			// File uploads avec preview
			$(document).on('change', '.file_input', function() {
				VendorRegister.handleFileUpload($(this));
			});

			// Remove file
			$(document).on('click', '.remove_file', function() {
				VendorRegister.removeFile($(this).data('target'));
			});

			// Submit form
			$(document).on('submit', '#vendor_register_form', function(e) {
				e.preventDefault();
				VendorRegister.handleSubmit($(this));
			});
		},

		goToStep: function(step) {
			// Valider l'étape actuelle avant de continuer
			if (step > this.currentStep && !this.validateStep(this.currentStep)) {
				return;
			}

			// Cacher toutes les étapes
			$('.form_step').hide();
			$('.form_step[data-step="' + step + '"]').fadeIn(300);

			// Mettre à jour la progress bar
			const progress = (step / this.totalSteps) * 100;
			$('.progress_bar_fill').css('width', progress + '%');

			// Mettre à jour les steps indicators
			$('.progress_step').removeClass('active');
			$('.progress_step[data-step="' + step + '"]').addClass('active');

			this.currentStep = step;

			// Scroll to top
			$('html, body').animate({
				scrollTop: $('.register_form_container').offset().top - 100
			}, 500);
		},

		validateStep: function(step) {
			let isValid = true;
			const $step = $('.form_step[data-step="' + step + '"]');

			// Récupérer tous les champs requis de l'étape
			$step.find('[required]').each(function() {
				const $field = $(this);

				if ($field.attr('type') === 'checkbox') {
					if (!$field.is(':checked')) {
						isValid = false;
						$field.parent().css('border-color', '#FF6B6B');
					}
				} else if ($field.is('select')) {
					if (!$field.val()) {
						isValid = false;
						$field.css('border-color', '#FF6B6B');
					}
				} else {
					if (!$field.val().trim()) {
						isValid = false;
						$field.css('border-color', '#FF6B6B');
					}
				}
			});

			// Validation spécifique étape 1
			if (step === 1) {
				const password = $('#vendor_password').val();
				const confirm = $('#vendor_password_confirm').val();

				if (password.length < 8) {
					VendorRegister.showNotification('error', 'Le mot de passe doit contenir au moins 8 caractères.');
					isValid = false;
				}

				if (password !== confirm) {
					VendorRegister.showNotification('error', 'Les mots de passe ne correspondent pas.');
					isValid = false;
				}
			}

			// Validation étape 2
			if (step === 2) {
				const categoriesChecked = $('input[name="vendor_categories[]"]:checked').length;
				if (categoriesChecked === 0) {
					VendorRegister.showNotification('error', 'Veuillez sélectionner au moins une catégorie.');
					isValid = false;
				}
			}

			if (!isValid) {
				VendorRegister.showNotification('error', 'Veuillez remplir tous les champs obligatoires.');
			}

			return isValid;
		},

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

		initPasswordStrength: function() {
			$('#vendor_password').on('input', function() {
				VendorRegister.checkPasswordStrength($(this).val());
			});
		},

		checkPasswordStrength: function(password) {
			const $fill = $('.strength_bar_fill');
			const $text = $('.strength_text');

			if (password.length === 0) {
				$fill.removeClass('weak medium strong').css('width', '0');
				$text.text('');
				return;
			}

			let strength = 0;

			if (password.length >= 8) strength++;
			if (password.length >= 12) strength++;
			if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
			if (/\d/.test(password)) strength++;
			if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

			if (strength <= 2) {
				$fill.removeClass('medium strong').addClass('weak');
				$text.text('Mot de passe faible');
			} else if (strength <= 4) {
				$fill.removeClass('weak strong').addClass('medium');
				$text.text('Mot de passe moyen');
			} else {
				$fill.removeClass('weak medium').addClass('strong');
				$text.text('Mot de passe fort');
			}
		},

		handleFileUpload: function($input) {
			const file = $input[0].files[0];
			const fieldId = $input.attr('id');
			const $label = $input.siblings('.file_upload_label');

			if (file) {
				$label.find('.upload_filename').text(file.name);

				// Preview pour images (logo et cover)
				if ((fieldId === 'vendor_logo' || fieldId === 'vendor_cover') && file.type.match('image.*')) {
					const reader = new FileReader();
					reader.onload = function(e) {
						$('#preview_' + fieldId.replace('vendor_', '')).show().find('img').attr('src', e.target.result);
					};
					reader.readAsDataURL(file);
				}
			}
		},

		removeFile: function(targetId) {
			$('#' + targetId).val('');
			$('#' + targetId).siblings('.file_upload_label').find('.upload_filename').text('');
			$('#preview_' + targetId.replace('vendor_', '')).hide().find('img').attr('src', '');
		},

		handleSubmit: function($form) {
			// Valider l'étape finale
			if (!this.validateStep(3)) {
				return;
			}

			const $submitBtn = $form.find('.submit_button');
			const $btnText = $submitBtn.find('.button_text');
			const $btnLoader = $submitBtn.find('.button_loader');

			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnLoader.show();

			this.clearNotifications();

			// Créer FormData pour gérer les fichiers
			const formData = new FormData($form[0]);
			formData.append('action', 'lehiboo_vendor_register');

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

						// Redirection après 3 secondes
						setTimeout(function() {
							if (response.data.redirect_url) {
								window.location.href = response.data.redirect_url;
							} else {
								window.location.reload();
							}
						}, 3000);
					} else {
						VendorRegister.showNotification('error', response.data.message);
						$submitBtn.prop('disabled', false);
						$btnText.show();
						$btnLoader.hide();
					}
				},
				error: function() {
					VendorRegister.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnLoader.hide();
				}
			});
		},

		showNotification: function(type, message) {
			const $notification = $(`.register_notification.${type}`);
			$notification.html(message).fadeIn(300);

			if (type === 'success') {
				setTimeout(function() {
					$notification.fadeOut(300);
				}, 5000);
			}

			$('html, body').animate({
				scrollTop: $('.register_form_container').offset().top - 100
			}, 500);
		},

		clearNotifications: function() {
			$('.register_notification').hide().html('');
		}
	};

	// Init
	$(document).ready(function() {
		if ($('#vendor_register_form').length) {
			VendorRegister.init();
		}
	});

})(jQuery);
