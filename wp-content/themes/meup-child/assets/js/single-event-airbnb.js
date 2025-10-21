/**
 * JavaScript - Single Event Airbnb Style
 *
 * Gère les interactions pour la page événement style Airbnb:
 * - Widget sticky (desktop)
 * - Lightbox galerie avec Fancybox
 * - Compteur invités (+/-)
 * - Calcul total prix
 * - CTA mobile scroll
 * - Accordéons FAQ
 *
 * @package LeHiboo
 * @version 2.1.0
 */

(function($) {
	'use strict';

	var EventAirbnb = {

		/**
		 * Initialisation
		 */
		init: function() {
			this.stickyWidget();
			this.guestCounter();
			this.mobileCtaScroll();
			this.galleryLightbox();
			this.smoothScroll();
			this.faqAccordions();
		},

		/**
		 * Widget Réservation Sticky
		 */
		stickyWidget: function() {
			var $widget = $('#booking_sticky_widget');

			if ( !$widget.length || $(window).width() < 1024 ) {
				return;
			}

			var widgetTop = $widget.offset().top;
			var headerHeight = 80;

			$(window).on('scroll', function() {
				var scrollTop = $(window).scrollTop();

				if ( scrollTop > (widgetTop - headerHeight) ) {
					$widget.addClass('is-sticky');
				} else {
					$widget.removeClass('is-sticky');
				}
			});
		},

		/**
		 * Compteur d'invités (+/-)
		 */
		guestCounter: function() {
			var $input = $('#booking_guests');
			var $totalAmount = $('#booking_total_amount');
			var basePrice = $('#booking_sticky_widget').data('price') || 0;

			// Boutons +/-
			$('.guests_btn').on('click', function(e) {
				e.preventDefault();

				var action = $(this).data('action');
				var currentVal = parseInt( $input.val() ) || 1;
				var newVal = currentVal;

				if ( action === 'increase' ) {
					newVal = Math.min(currentVal + 1, 20);
				} else if ( action === 'decrease' ) {
					newVal = Math.max(currentVal - 1, 1);
				}

				$input.val(newVal);

				// Mettre à jour le nombre d'invités affiché
				$('.guests_count').text(newVal);

				// Recalculer le total
				EventAirbnb.updateTotal(newVal, basePrice);
			});

			// Empêcher saisie manuelle
			$input.on('keydown', function(e) {
				e.preventDefault();
				return false;
			});
		},

		/**
		 * Mise à jour du total
		 */
		updateTotal: function(guests, basePrice) {
			var total = guests * basePrice;
			var currency = $('#booking_total_amount').text().replace(/[0-9.,]/g, '').trim();

			if ( !currency ) {
				currency = '€';
			}

			var formattedTotal = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

			$('#booking_total_amount').text( currency + formattedTotal );
		},

		/**
		 * CTA Mobile - Afficher/Masquer au scroll
		 */
		mobileCtaScroll: function() {
			var $mobileCta = $('#mobile_booking_cta');

			if ( !$mobileCta.length || $(window).width() >= 768 ) {
				return;
			}

			var lastScroll = 0;

			$(window).on('scroll', function() {
				var currentScroll = $(window).scrollTop();

				if ( currentScroll > 200 ) {
					// Afficher le CTA
					$mobileCta.addClass('visible');

					// Masquer si scroll vers le haut
					if ( currentScroll < lastScroll ) {
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
			$('.btn_book_mobile[data-scroll-to]').on('click', function(e) {
				var target = $(this).data('scroll-to');

				if ( $(target).length ) {
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
		galleryLightbox: function() {
			// Vérifier que Fancybox est disponible
			if ( typeof Fancybox === 'undefined' ) {
				console.warn('Fancybox not loaded');
				return;
			}

			// Désactiver l'auto-bind de Fancybox
			Fancybox.unbind('.gallery_lightbox');
			Fancybox.close();

			// Collecter toutes les images de la galerie
			var galleryImages = [];

			$('.gallery_lightbox').each(function() {
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
			$('.gallery_lightbox').off('click').on('click', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if ( Fancybox.getInstance() ) {
					Fancybox.close();
				}

				var clickedIndex = $('.gallery_lightbox').index(this);

				setTimeout(function() {
					Fancybox.show(galleryImages, $.extend({}, fancyboxOptions, {
						startIndex: clickedIndex
					}));
				}, 50);

				return false;
			});

			// Bouton "Voir toutes les photos"
			$('.btn_view_all_photos').off('click').on('click', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if ( Fancybox.getInstance() ) {
					Fancybox.close();
				}

				setTimeout(function() {
					Fancybox.show(galleryImages, $.extend({}, fancyboxOptions, {
						startIndex: 0
					}));
				}, 50);

				return false;
			});

			// Overlay miniature
			$('.view_all_photos_overlay').closest('.gallery_thumbnail_item').off('click').on('click', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				if ( Fancybox.getInstance() ) {
					Fancybox.close();
				}

				var $link = $(this).find('.gallery_lightbox');
				var clickedIndex = $('.gallery_lightbox').index($link);

				setTimeout(function() {
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
		smoothScroll: function() {
			$('a[href^="#"]').on('click', function(e) {
				var target = $(this).attr('href');

				if ( target === '#' || target === '' ) {
					return;
				}

				if ( $(target).length ) {
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
		faqAccordions: function() {
			$('.faq_question').on('click', function(e) {
				e.preventDefault();

				var $button = $(this);
				var $item = $button.closest('.faq_item');
				var $answer = $item.find('.faq_answer');
				var isExpanded = $button.attr('aria-expanded') === 'true';

				// Fermer tous les autres
				$('.faq_item').not($item).each(function() {
					$(this).find('.faq_question')
						.attr('aria-expanded', 'false')
						.find('.faq_icon')
						.removeClass('icon_minus')
						.addClass('icon_plus');

					$(this).find('.faq_answer').slideUp(300);
				});

				// Toggle
				if( isExpanded ) {
					$button.attr('aria-expanded', 'false');
					$button.find('.faq_icon').removeClass('icon_minus').addClass('icon_plus');
					$answer.slideUp(300);
				} else {
					$button.attr('aria-expanded', 'true');
					$button.find('.faq_icon').removeClass('icon_plus').addClass('icon_minus');
					$answer.slideDown(300);
				}
			});
		}

	};

	// Document Ready
	$(document).ready(function() {
		if ( $('.event_single_airbnb').length ) {
			EventAirbnb.init();
		}
	});

	// Window Resize
	var resizeTimer;
	$(window).on('resize', function() {
		clearTimeout(resizeTimer);

		resizeTimer = setTimeout(function() {
			if ( $('.event_single_airbnb').length ) {
				EventAirbnb.stickyWidget();
				EventAirbnb.mobileCtaScroll();
			}
		}, 250);
	});

})(jQuery);
