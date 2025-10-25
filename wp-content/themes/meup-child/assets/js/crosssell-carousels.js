/**
 * Crosssell Carousels
 * Gestion des carousels d'activités vues et de l'organisateur
 */

(function($) {
	'use strict';

	const CrosssellCarousels = {

		/**
		 * Initialiser les carousels
		 */
		init: function() {
			this.initOrganizerCarousel();
			this.initViewedCarousel();
		},

		/**
		 * Carousel des activités de l'organisateur
		 */
		initOrganizerCarousel: function() {
			const $section = $('.organizer_activities_section');
			if (!$section.length) return;

			const $carousel = $section.find('.organizer_carousel');
			this.setupCarouselNavigation($carousel, $section);
		},

		/**
		 * Carousel des activités vues
		 */
		initViewedCarousel: function() {
			const $section = $('.viewed_activities_section');
			if (!$section.length) return;

			const $carousel = $section.find('.viewed_carousel');
			const currentEventId = parseInt($carousel.data('current-event'), 10);

			// Vérifier si ViewedActivities existe
			if (typeof window.ViewedActivities === 'undefined') {
				console.error('ViewedActivities tracker not loaded');
				$section.hide();
				return;
			}

			// Récupérer les IDs des activités vues
			const viewedIds = window.ViewedActivities.getViewedExceptCurrent(currentEventId);

			if (viewedIds.length === 0) {
				$section.hide();
				return;
			}

			// Charger les activités via AJAX
			this.loadViewedActivities(viewedIds, $carousel, $section);
		},

		/**
		 * Charger les activités vues via AJAX
		 */
		loadViewedActivities: function(eventIds, $carousel, $section) {
			const self = this;

			$.ajax({
				url: el_ajax_object.ajax_url,
				type: 'POST',
				data: {
					action: 'get_viewed_activities',
					event_ids: eventIds,
					nonce: el_ajax_object.nonce
				},
				success: function(response) {
					if (response.success && response.data.html) {
						$carousel.html(response.data.html);
						self.setupCarouselNavigation($carousel, $section);
					} else {
						$section.hide();
					}
				},
				error: function() {
					$section.hide();
				}
			});
		},

		/**
		 * Configuration de la navigation du carousel
		 */
		setupCarouselNavigation: function($carousel, $section) {
			const $prevBtn = $section.find('.carousel_prev');
			const $nextBtn = $section.find('.carousel_next');

			const scrollStep = 300; // Pixels à scroller

			// Bouton Précédent
			$prevBtn.on('click', function(e) {
				e.preventDefault();
				$carousel.scrollLeft($carousel.scrollLeft() - scrollStep);
				updateButtons();
			});

			// Bouton Suivant
			$nextBtn.on('click', function(e) {
				e.preventDefault();
				$carousel.scrollLeft($carousel.scrollLeft() + scrollStep);
				updateButtons();
			});

			// Mettre à jour l'état des boutons
			function updateButtons() {
				const scrollLeft = $carousel.scrollLeft();
				const scrollWidth = $carousel[0].scrollWidth;
				const clientWidth = $carousel[0].clientWidth;

				// Désactiver le bouton prev si au début
				if (scrollLeft <= 0) {
					$prevBtn.prop('disabled', true);
				} else {
					$prevBtn.prop('disabled', false);
				}

				// Désactiver le bouton next si à la fin
				if (scrollLeft + clientWidth >= scrollWidth - 5) {
					$nextBtn.prop('disabled', true);
				} else {
					$nextBtn.prop('disabled', false);
				}
			}

			// Initialiser l'état des boutons
			updateButtons();

			// Mettre à jour lors du scroll
			$carousel.on('scroll', function() {
				updateButtons();
			});

			// Support du swipe sur mobile
			this.setupSwipe($carousel);
		},

		/**
		 * Support du swipe tactile
		 */
		setupSwipe: function($carousel) {
			let isDown = false;
			let startX;
			let scrollLeft;

			$carousel.on('mousedown touchstart', function(e) {
				isDown = true;
				startX = (e.pageX || e.originalEvent.touches[0].pageX) - $carousel.offset().left;
				scrollLeft = $carousel.scrollLeft();
			});

			$carousel.on('mouseleave mouseup touchend', function() {
				isDown = false;
			});

			$carousel.on('mousemove touchmove', function(e) {
				if (!isDown) return;
				e.preventDefault();
				const x = (e.pageX || e.originalEvent.touches[0].pageX) - $carousel.offset().left;
				const walk = (x - startX) * 2;
				$carousel.scrollLeft(scrollLeft - walk);
			});
		}
	};

	// Initialiser au chargement de la page
	$(document).ready(function() {
		CrosssellCarousels.init();
	});

})(jQuery);
