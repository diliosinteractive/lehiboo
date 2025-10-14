/**
 * Event Form Progress Navigation
 * Gère le smooth scroll, l'état actif et les coches de validation
 */

(function($) {
	'use strict';

	// Configuration
	const config = {
		navSelector: '.profile_navigation_sidebar',
		navItemSelector: '.profile_tab_item',
		cardSelector: '.form_card',
		activeClass: 'active',
		completedClass: 'completed',
		incompleteClass: 'incomplete',
		offset: 100, // Offset pour navigation
		scrollDuration: 800
	};

	/**
	 * Initialise la navigation avec ancres et validation
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

		// Initialiser la validation des sections
		initSectionValidation();
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
			$navItems.filter(`[data-section="${currentCard}"]`).addClass(config.activeClass);
		}
	}

	/**
	 * Initialise la validation des sections
	 */
	function initSectionValidation() {
		// Validation initiale au chargement
		validateAllSections();

		// Re-valider quand un champ change
		$(document).on('change blur keyup', 'input, textarea, select', function() {
			// Debounce pour éviter trop d'appels
			clearTimeout(window.validationTimeout);
			window.validationTimeout = setTimeout(function() {
				validateAllSections();
			}, 300);
		});

		// Re-valider après les événements Select2
		$(document).on('select2:select select2:unselect', function() {
			setTimeout(validateAllSections, 100);
		});
	}

	/**
	 * Valide toutes les sections
	 */
	function validateAllSections() {
		const $navItems = $(config.navItemSelector);

		$navItems.each(function() {
			const $navItem = $(this);
			const sectionId = $navItem.data('section');
			const $section = $(`#${sectionId}`);

			if (!$section.length) return;

			const isComplete = checkSectionCompletion($section);

			// Mettre à jour les classes
			$navItem.removeClass(config.completedClass + ' ' + config.incompleteClass);

			if (isComplete) {
				$navItem.addClass(config.completedClass);
			} else {
				$navItem.addClass(config.incompleteClass);
			}
		});
	}

	/**
	 * Vérifie si une section est complète
	 */
	function checkSectionCompletion($section) {
		// Trouver tous les champs requis dans la section
		const $requiredFields = $section.find('input[required], textarea[required], select[required]')
			.add($section.find('input, textarea, select').filter(function() {
				// Aussi vérifier les champs avec attribut data-required ou classe required
				return $(this).data('required') || $(this).hasClass('required');
			}));

		if ($requiredFields.length === 0) {
			// Pas de champs requis = section optionnelle = complétée
			return true;
		}

		let allFilled = true;

		$requiredFields.each(function() {
			const $field = $(this);
			const fieldType = $field.attr('type');
			const tagName = $field.prop('tagName').toLowerCase();

			// Vérifier selon le type de champ
			if (fieldType === 'checkbox' || fieldType === 'radio') {
				// Pour checkbox/radio, vérifier si au moins un est coché dans le groupe
				const name = $field.attr('name');
				if (name) {
					const isChecked = $(`input[name="${name}"]:checked`).length > 0;
					if (!isChecked) {
						allFilled = false;
						return false; // Break
					}
				}
			} else if (tagName === 'select') {
				// Pour select, vérifier qu'une valeur est sélectionnée
				const value = $field.val();
				if (!value || value === '' || value === null || (Array.isArray(value) && value.length === 0)) {
					allFilled = false;
					return false; // Break
				}
			} else {
				// Pour input text, textarea, etc.
				const value = $field.val();
				if (!value || value.trim() === '') {
					allFilled = false;
					return false; // Break
				}
			}
		});

		return allFilled;
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
	 * Initialisation au chargement du DOM
	 */
	$(document).ready(function() {
		// Vérifier si on est sur la page de création d'événement
		if (!$('.event_form_single_page').length) {
			return;
		}

		initProgressNavigation();
		initCardAnimations();

		// Log pour debug
		console.log('Event Form Progress Navigation initialized');
	});

})(jQuery);
