/**
 * Event Form Progress Navigation
 * Gère le smooth scroll et l'état actif de la navigation par ancres
 */

(function($) {
	'use strict';

	// Configuration
	const config = {
		navSelector: '.form_progress_navigation',
		navItemSelector: '.progress_nav_item',
		cardSelector: '.form_card',
		activeClass: 'active',
		offset: 100, // Offset pour le sticky header
		scrollDuration: 800
	};

	/**
	 * Initialise la navigation avec ancres
	 */
	function initProgressNavigation() {
		if (!$(config.navSelector).length) {
			return;
		}

		// Smooth scroll au clic sur les ancres
		$(config.navSelector).on('click', 'a[href^="#"]', function(e) {
			e.preventDefault();

			const targetId = $(this).attr('href');
			const $target = $(targetId);

			if ($target.length) {
				smoothScrollTo($target);
			}
		});

		// Mettre à jour l'état actif en scrollant
		initScrollSpy();
	}

	/**
	 * Smooth scroll vers une section
	 */
	function smoothScrollTo($target) {
		const targetTop = $target.offset().top - config.offset;

		$('html, body').animate({
			scrollTop: targetTop
		}, config.scrollDuration, 'swing');
	}

	/**
	 * Initialise le scroll spy pour mettre à jour la navigation
	 */
	function initScrollSpy() {
		let ticking = false;

		$(window).on('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					updateActiveNavItem();
					ticking = false;
				});
				ticking = true;
			}
		});

		// Initial update
		updateActiveNavItem();
	}

	/**
	 * Met à jour l'item actif dans la navigation
	 */
	function updateActiveNavItem() {
		const scrollPos = $(window).scrollTop() + config.offset + 50;
		const $cards = $(config.cardSelector);
		const $navItems = $(config.navItemSelector);

		let currentCard = null;

		// Trouver la card actuellement visible
		$cards.each(function() {
			const $card = $(this);
			const cardTop = $card.offset().top;
			const cardBottom = cardTop + $card.outerHeight();

			if (scrollPos >= cardTop && scrollPos < cardBottom) {
				currentCard = $card.attr('id');
				return false; // Break loop
			}
		});

		// Si on n'a pas trouvé de card, prendre la première ou la dernière
		if (!currentCard) {
			if (scrollPos < $cards.first().offset().top) {
				currentCard = $cards.first().attr('id');
			} else {
				currentCard = $cards.last().attr('id');
			}
		}

		// Mettre à jour la classe active
		if (currentCard) {
			$navItems.removeClass(config.activeClass);
			$navItems.filter(`[data-anchor="${currentCard}"]`).addClass(config.activeClass);
		}
	}

	/**
	 * Animation d'apparition des cards au scroll
	 */
	function initCardAnimations() {
		if (!window.IntersectionObserver) {
			return; // Pas de support, pas d'animation
		}

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					observer.unobserve(entry.target);
				}
			});
		}, {
			threshold: 0.1,
			rootMargin: '0px 0px -50px 0px'
		});

		document.querySelectorAll(config.cardSelector).forEach(function(card) {
			card.classList.add('fade-in-card');
			observer.observe(card);
		});
	}

	/**
	 * Ajoute des indicateurs de complétion
	 */
	function initCompletionIndicators() {
		const $navItems = $(config.navItemSelector);

		$navItems.each(function() {
			const $item = $(this);
			const anchor = $item.data('anchor');
			const $card = $(`#${anchor}`);

			if (!$card.length) return;

			// Vérifier si la section est complète
			const isComplete = checkSectionCompletion($card);

			if (isComplete) {
				$item.addClass('completed');
				$item.find('a').prepend('<i class="icon_check_alt2 completion-check"></i>');
			}
		});
	}

	/**
	 * Vérifie si une section est complète
	 */
	function checkSectionCompletion($card) {
		// Vérifier les champs requis
		const $requiredFields = $card.find('[required], .required');

		if ($requiredFields.length === 0) {
			return false; // Pas de champs requis = pas de validation
		}

		let allFilled = true;

		$requiredFields.each(function() {
			const $field = $(this);

			if ($field.is('input, textarea, select')) {
				if (!$field.val() || $field.val().length === 0) {
					allFilled = false;
					return false; // Break
				}
			}
		});

		return allFilled;
	}

	/**
	 * Initialisation au chargement du DOM
	 */
	$(document).ready(function() {
		// Vérifier si on est sur la page de création d'événement
		if (!$('.event_form_single_page').length) {
			return;
		}

		initProgressNavigation();
		initCardAnimations();

		// Indicateurs de complétion (optionnel, à activer si souhaité)
		// initCompletionIndicators();

		// Re-vérifier la complétion lors des changements
		$(document).on('change blur', 'input, textarea, select', function() {
			// Optionnel: re-calculer les indicateurs
			// initCompletionIndicators();
		});
	});

})(jQuery);
