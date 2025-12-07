/**
 * JavaScript - Single Event Airbnb Style
 *
 * Gère les interactions pour la page événement style Airbnb:
 * - Widget sticky (desktop)
 * - Lightbox galerie avec Fancybox
 * - CTA mobile scroll
 * - Accordéons FAQ
 *
 * @package LeHiboo
 * @version 3.3.1
 */

(function ($) {
	'use strict';

	var EventAirbnb = {

		/**
		 * Initialisation
		 */
		init: function () {
			this.stickyWidget();
			this.mobileCtaScroll();
			this.galleryLightbox();
			this.smoothScroll();
			this.faqAccordions();
			this.contactPopup();
		},

		/**
		 * Widget Réservation Sticky
		 */
		stickyWidget: function () {
			var $widget = $('#booking_sticky_widget');

			if (!$widget.length || $(window).width() < 1024) {
				return;
			}

			var widgetTop = $widget.offset().top;
			var headerHeight = 80;

			$(window).on('scroll', function () {
				var scrollTop = $(window).scrollTop();

				if (scrollTop > (widgetTop - headerHeight)) {
					$widget.addClass('is-sticky');
				} else {
					$widget.removeClass('is-sticky');
				}
			});
		},

		/**
		 * Compteur d'invités (+/-)
		 */

		/**
		 * CTA Mobile - Afficher/Masquer au scroll
		 */
		mobileCtaScroll: function () {
			var $mobileCta = $('#mobile_booking_cta');

			if (!$mobileCta.length || $(window).width() >= 768) {
				return;
			}

			var lastScroll = 0;

			$(window).on('scroll', function () {
				var currentScroll = $(window).scrollTop();

				if (currentScroll > 200) {
					// Afficher le CTA
					$mobileCta.addClass('visible');

					// Masquer si scroll vers le haut
					if (currentScroll < lastScroll) {
						$mobileCta.removeClass('hidden');
					} else {
						$mobileCta.addClass('hidden');
					}
				} else {
					$mobileCta.removeClass('visible');
				}

				lastScroll = currentScroll;
			});

			// Clic sur le bouton mobile -> scroll vers calendrier
			$('.btn_book_mobile[data-scroll-to]').on('click', function (e) {
				var target = $(this).data('scroll-to');

				if ($(target).length) {
					e.preventDefault();

					$('html, body').animate({
						scrollTop: $(target).offset().top - 80
					}, 600);
				}
			});
		},

		/**
		 * Galerie Lightbox - Fancybox (évite double initialisation)
		 */
		galleryLightbox: function () {
			// Vérifier que Fancybox est disponible
			if (typeof Fancybox === 'undefined') {
				console.warn('Fancybox not loaded');
				return;
			}

			// Désactiver l'auto-bind de Fancybox
			Fancybox.unbind('.gallery_lightbox');
			Fancybox.close();

			// Collecter toutes les images de la galerie
			var galleryImages = [];

			$('.gallery_lightbox').each(function () {
				var $link = $(this);
				var imageUrl = $link.attr('href');
				var title = $link.data('title') || $link.find('img').attr('alt') || '';

				galleryImages.push({
					src: imageUrl,
					type: 'image',
					caption: title
				});
			});

			// Options Fancybox
			var fancyboxOptions = {
				Toolbar: {
					display: {
						left: [],
						middle: [],
						right: ['close']
					}
				},

				Thumbs: {
					type: 'classic'
				},

				keyboard: {
					Escape: 'close',
					Delete: 'close',
					Backspace: 'close',
					PageUp: 'next',
					PageDown: 'prev',
					ArrowUp: 'prev',
					ArrowDown: 'next',
					ArrowRight: 'next',
					ArrowLeft: 'prev'
				},

				animated: true,
				showClass: 'f-fadeIn',
				hideClass: 'f-fadeOut',
				preload: 1,

				l10n: {
					CLOSE: 'Fermer',
					NEXT: 'Suivant',
					PREV: 'Précédent',
					MODAL: 'Vous pouvez fermer cette fenêtre avec la touche ESC',
					ERROR: 'Impossible de charger l\'image',
					IMAGE_ERROR: 'Image introuvable'
				}
			};

			// Gestion des clics
			$('.gallery_lightbox').off('click').on('click', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if (Fancybox.getInstance()) {
					Fancybox.close();
				}

				var clickedIndex = $('.gallery_lightbox').index(this);

				setTimeout(function () {
					Fancybox.show(galleryImages, $.extend({}, fancyboxOptions, {
						startIndex: clickedIndex
					}));
				}, 50);

				return false;
			});

			// Bouton "Voir toutes les photos"
			$('.btn_view_all_photos').off('click').on('click', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if (Fancybox.getInstance()) {
					Fancybox.close();
				}

				setTimeout(function () {
					Fancybox.show(galleryImages, $.extend({}, fancyboxOptions, {
						startIndex: 0
					}));
				}, 50);

				return false;
			});

			// Overlay miniature
			$('.view_all_photos_overlay').closest('.gallery_thumbnail_item').off('click').on('click', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if (Fancybox.getInstance()) {
					Fancybox.close();
				}

				var $link = $(this).find('.gallery_lightbox');
				var clickedIndex = $('.gallery_lightbox').index($link);

				setTimeout(function () {
					Fancybox.show(galleryImages, $.extend({}, fancyboxOptions, {
						startIndex: clickedIndex
					}));
				}, 50);

				return false;
			});
		},

		/**
		 * Smooth Scroll pour les liens d'ancre
		 */
		smoothScroll: function () {
			$('a[href^="#"]').on('click', function (e) {
				var target = $(this).attr('href');

				if (target === '#' || target === '') {
					return;
				}

				if ($(target).length) {
					e.preventDefault();

					$('html, body').animate({
						scrollTop: $(target).offset().top - 80
					}, 600);
				}
			});
		},

		/**
		 * Accordéons FAQ
		 */
		faqAccordions: function () {
			$('.faq_question').on('click', function (e) {
				e.preventDefault();

				var $button = $(this);
				var $item = $button.closest('.faq_item');
				var $answer = $item.find('.faq_answer');
				var isExpanded = $button.attr('aria-expanded') === 'true';

				// Fermer tous les autres
				$('.faq_item').not($item).each(function () {
					$(this).find('.faq_question')
						.attr('aria-expanded', 'false')
						.find('.faq_icon')
						.removeClass('icon_minus')
						.addClass('icon_plus');

					$(this).find('.faq_answer').slideUp(300);
				});

				// Toggle
				if (isExpanded) {
					$button.attr('aria-expanded', 'false');
					$button.find('.faq_icon').removeClass('icon_minus').addClass('icon_plus');
					$answer.slideUp(300);
				} else {
					$button.attr('aria-expanded', 'true');
					$button.find('.faq_icon').removeClass('icon_plus').addClass('icon_minus');
					$answer.slideDown(300);
				}
			});
		},

		/**
		 * Popup Formulaire de Contact
		 */
		contactPopup: function () {

			var $popup = $('#contact_organizer_popup');
			var $openBtn = $('#open_contact_form');
			var $closeBtn = $('.contact_popup_close');

			if (!$popup.length) {
				console.warn('Popup element not found!');
				return;
			}

			if (!$openBtn.length) {
				console.warn('Open button not found!');
				return;
			}

			// Ouvrir le popup
			$openBtn.on('click', function (e) {
				e.preventDefault();
				EventAirbnb.openContactPopup();
			});

			// Fermer avec le bouton X
			$closeBtn.on('click', function (e) {
				e.preventDefault();
				EventAirbnb.closeContactPopup();
			});

			// Fermer au clic sur l'overlay
			$popup.on('click', function (e) {
				if ($(e.target).is('.contact_popup_overlay')) {
					EventAirbnb.closeContactPopup();
				}
			});

			// Fermer avec Escape
			$(document).on('keydown', function (e) {
				if (e.key === 'Escape' && $popup.hasClass('is-open')) {
					EventAirbnb.closeContactPopup();
				}
			});

			// Gérer la soumission du formulaire (AJAX)
			$('#contact_organizer_form').on('submit', function (e) {
				e.preventDefault();
				EventAirbnb.submitContactForm($(this));
			});
		},

		/**
		 * Ouvrir le popup de contact
		 */
		openContactPopup: function () {
			var $popup = $('#contact_organizer_popup');

			$popup.addClass('is-open');
			$popup.show(); // Force display
			$('body').css('overflow', 'hidden');
		},

		/**
		 * Fermer le popup de contact
		 */
		closeContactPopup: function () {
			var $popup = $('#contact_organizer_popup');
			$popup.removeClass('is-open');
			$('body').css('overflow', '');
		},

		/**
		 * Soumettre le formulaire de contact via AJAX
		 */
		submitContactForm: function ($form) {
			var $submitBtn = $form.find('.contact_submit_btn');
			var originalText = $submitBtn.text();

			// Récupérer le token Turnstile
			var turnstileResponse = $form.find('[name="cf-turnstile-response"]').val();

			// Vérifier le CAPTCHA
			if (!turnstileResponse) {
				if (typeof ToastNotification !== 'undefined') {
					ToastNotification.warning('Veuillez valider le CAPTCHA.');
				} else {
					alert('Veuillez valider le CAPTCHA.');
				}
				return;
			}

			// Désactiver le bouton
			$submitBtn.prop('disabled', true).text('Envoi en cours...');

			// Vérifier que el_ajax_object existe
			var ajaxUrl = (typeof el_ajax_object !== 'undefined' && el_ajax_object.ajax_url)
				? el_ajax_object.ajax_url
				: '/wp-admin/admin-ajax.php';

			// Préparer les données
			var formData = $form.serialize();

			// Envoyer via AJAX
			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: formData,
				success: function (response) {
					if (response.success) {
						// Succès
						if (typeof ToastNotification !== 'undefined') {
							ToastNotification.success(response.data.message || 'Message envoyé avec succès!');
						} else {
							alert(response.data.message || 'Message envoyé avec succès!');
						}
						$form[0].reset();
						// Reset Turnstile
						if (typeof turnstile !== 'undefined') {
							turnstile.reset();
						}
						EventAirbnb.closeContactPopup();
					} else {
						// Erreur
						var errorMsg = response.data.message || 'Erreur lors de l\'envoi du message.';
						if (response.data.errors) {
							errorMsg += '\n\nDétails: ' + response.data.errors.join(', ');
						}
						if (typeof ToastNotification !== 'undefined') {
							ToastNotification.error(errorMsg);
						} else {
							alert(errorMsg);
						}
					}
				},
				error: function (xhr, status, error) {
					console.error('AJAX Error:', status, error);
					console.error('Response:', xhr.responseText);
					if (typeof ToastNotification !== 'undefined') {
						ToastNotification.error('Erreur de connexion. Veuillez réessayer.');
					} else {
						alert('Erreur de connexion. Veuillez réessayer.');
					}
				},
				complete: function () {
					// Réactiver le bouton
					$submitBtn.prop('disabled', false).text(originalText);
				}
			});
		}

	};

	// Document Ready
	$(document).ready(function () {
		if ($('.event_single_airbnb').length) {
			EventAirbnb.init();
		}
	});

	// Window Resize
	var resizeTimer;
	$(window).on('resize', function () {
		clearTimeout(resizeTimer);

		resizeTimer = setTimeout(function () {
			if ($('.event_single_airbnb').length) {
				EventAirbnb.stickyWidget();
				EventAirbnb.mobileCtaScroll();
			}
		}, 250);
	});

})(jQuery);
