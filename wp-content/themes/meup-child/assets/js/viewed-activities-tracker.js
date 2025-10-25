/**
 * Viewed Activities Tracker
 * Track les activités vues par l'utilisateur dans localStorage
 */

(function($) {
	'use strict';

	const ViewedActivities = {
		storageKey: 'lehiboo_viewed_activities',
		maxItems: 20, // Maximum 20 activités stockées

		/**
		 * Initialiser le tracker
		 */
		init: function() {
			// Si on est sur une page single event, enregistrer la vue
			if (typeof el_ajax_object !== 'undefined' && el_ajax_object.current_event_id) {
				this.trackView(el_ajax_object.current_event_id);
			}
		},

		/**
		 * Enregistrer une vue d'activité
		 */
		trackView: function(eventId) {
			let viewed = this.getViewed();

			// Retirer l'ID s'il existe déjà (pour le mettre en premier)
			viewed = viewed.filter(id => id !== eventId);

			// Ajouter en premier
			viewed.unshift(eventId);

			// Limiter à maxItems
			if (viewed.length > this.maxItems) {
				viewed = viewed.slice(0, this.maxItems);
			}

			// Sauvegarder
			localStorage.setItem(this.storageKey, JSON.stringify(viewed));
		},

		/**
		 * Récupérer les activités vues
		 */
		getViewed: function() {
			const stored = localStorage.getItem(this.storageKey);

			if (!stored) {
				return [];
			}

			try {
				const parsed = JSON.parse(stored);
				return Array.isArray(parsed) ? parsed : [];
			} catch (e) {
				console.error('Error parsing viewed activities:', e);
				return [];
			}
		},

		/**
		 * Récupérer les activités vues (sans l'activité courante)
		 */
		getViewedExceptCurrent: function(currentId) {
			return this.getViewed().filter(id => id !== currentId);
		}
	};

	// Initialiser au chargement de la page
	$(document).ready(function() {
		ViewedActivities.init();
	});

	// Exposer globalement
	window.ViewedActivities = ViewedActivities;

})(jQuery);
