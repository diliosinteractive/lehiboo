/**
 * JavaScript - Single Event Airbnb Style
 *
 * Gère les interactions pour la page événement style Airbnb:
 * - Widget sticky (desktop)
 * - Lightbox galerie
 * - Compteur invités (+/-)
 * - Calcul total prix
 * - CTA mobile scroll
 * - Accordéons FAQ
 *
 * @package LeHiboo
 * @version 1.0.0
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
		 * Galerie Lightbox
		 */
		galleryLightbox: function() {
			// Si PrettyPhoto ou autre plugin lightbox existe
			if ( typeof $.fn.prettyPhoto !== 'undefined' ) {
				$('.gallery_lightbox').prettyPhoto({
					social_tools: false,
					slideshow: 5000,
					theme: 'pp_default',
					horizontal_padding: 20,
					opacity: 0.80,
					allow_resize: true,
					default_width: 800,
					default_height: 600,
					counter_separator_label: '/',
					keyboard_shortcuts: true
				});
			}

			// Sinon, lightbox simple avec fancybox ou magnific popup
			else if ( typeof $.fn.magnificPopup !== 'undefined' ) {
				$('.gallery_lightbox').magnificPopup({
					type: 'image',
					gallery: {
						enabled: true
					},
					image: {
						titleSrc: 'data-title'
					}
				});
			}

			// Fallback: ouverture dans nouvelle fenêtre
			else {
				$('.gallery_lightbox').on('click', function(e) {
					if ( !$(this).hasClass('no-lightbox') ) {
						// Laisser le comportement par défaut (nouvelle fenêtre)
					}
				});
			}

			// Bouton "Voir toutes les photos" mobile
			$('.btn_view_all_photos').on('click', function(e) {
				e.preventDefault();

				// Trigger le premier lien de la lightbox
				$('.gallery_lightbox').first().trigger('click');
			});

			// Overlay "Voir toutes les photos" sur la 4ème image
			$('.view_all_photos_overlay').parent().on('click', function(e) {
				e.preventDefault();

				// Ouvrir la lightbox
				$(this).find('.gallery_lightbox').trigger('click');
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
		 * Note: Le code est aussi dans faq.php pour éviter dépendance JS
		 */
		faqAccordions: function() {
			$('.faq_question').on('click', function(e) {
				e.preventDefault();

				var $button = $(this);
				var $item = $button.closest('.faq_item');
				var $answer = $item.find('.faq_answer');
				var isExpanded = $button.attr('aria-expanded') === 'true';

				// Fermer tous les autres accordéons
				$('.faq_item').not($item).each(function() {
					$(this).find('.faq_question')
						.attr('aria-expanded', 'false')
						.find('.faq_icon')
						.removeClass('icon_minus')
						.addClass('icon_plus');

					$(this).find('.faq_answer').slideUp(300);
				});

				// Toggle l'accordéon actuel
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

	/**
	 * Document Ready
	 */
	$(document).ready(function() {
		// Vérifier si on est sur une page événement avec le nouveau template
		if ( $('.event_single_airbnb').length ) {
			EventAirbnb.init();
		}
	});

	/**
	 * Window Resize - Réinitialiser certains comportements
	 */
	var resizeTimer;
	$(window).on('resize', function() {
		clearTimeout(resizeTimer);

		resizeTimer = setTimeout(function() {
			// Réinitialiser si passage mobile <-> desktop
			if ( $('.event_single_airbnb').length ) {
				EventAirbnb.stickyWidget();
				EventAirbnb.mobileCtaScroll();
			}
		}, 250);
	});

})(jQuery);
