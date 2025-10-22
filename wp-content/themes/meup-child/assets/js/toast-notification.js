/**
 * Toast Notification System - V1 Le Hiboo
 * Système de notifications toast moderne pour remplacer les alert()
 *
 * @package LeHiboo
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Gestionnaire de Toasts
     */
    window.ToastNotification = {

        /**
         * Configuration par défaut
         */
        defaults: {
            duration: 4000,        // Durée d'affichage en ms
            position: 'top-right', // Position: top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
            closeButton: true,     // Afficher le bouton de fermeture
            progressBar: true,     // Afficher la barre de progression
            pauseOnHover: true,    // Mettre en pause au survol
            animation: 'slide'     // Animation: slide, fade, bounce
        },

        /**
         * Conteneur des toasts
         */
        container: null,

        /**
         * Initialiser le système
         */
        init: function() {
            if (this.container) return;

            // Créer le conteneur principal
            this.container = $('<div class="toast-container"></div>');
            $('body').append(this.container);

            console.log('Toast Notification System initialized');
        },

        /**
         * Afficher un toast de succès
         */
        success: function(message, options) {
            return this.show(message, 'success', options);
        },

        /**
         * Afficher un toast d'erreur
         */
        error: function(message, options) {
            return this.show(message, 'error', options);
        },

        /**
         * Afficher un toast d'avertissement
         */
        warning: function(message, options) {
            return this.show(message, 'warning', options);
        },

        /**
         * Afficher un toast d'information
         */
        info: function(message, options) {
            return this.show(message, 'info', options);
        },

        /**
         * Afficher un toast
         */
        show: function(message, type, options) {
            var self = this;
            var settings = $.extend({}, this.defaults, options);

            // Initialiser si nécessaire
            if (!this.container) {
                this.init();
            }

            // Créer le toast
            var $toast = this.createToast(message, type, settings);

            // Ajouter le toast au conteneur
            this.container.append($toast);

            // Animation d'entrée
            setTimeout(function() {
                $toast.addClass('toast-show');
            }, 10);

            // Gestion de la fermeture automatique
            var timeoutId;
            if (settings.duration > 0) {
                timeoutId = setTimeout(function() {
                    self.hide($toast);
                }, settings.duration);
            }

            // Pause au survol
            if (settings.pauseOnHover && settings.duration > 0) {
                var remainingTime = settings.duration;
                var startTime = Date.now();

                $toast.on('mouseenter', function() {
                    clearTimeout(timeoutId);
                    remainingTime -= (Date.now() - startTime);
                    $toast.find('.toast-progress-bar').css('animation-play-state', 'paused');
                });

                $toast.on('mouseleave', function() {
                    startTime = Date.now();
                    timeoutId = setTimeout(function() {
                        self.hide($toast);
                    }, remainingTime);
                    $toast.find('.toast-progress-bar').css('animation-play-state', 'running');
                });
            }

            // Bouton de fermeture
            $toast.find('.toast-close').on('click', function() {
                clearTimeout(timeoutId);
                self.hide($toast);
            });

            return $toast;
        },

        /**
         * Créer l'élément toast
         */
        createToast: function(message, type, settings) {
            var icon = this.getIcon(type);

            var $toast = $('<div class="toast toast-' + type + ' toast-' + settings.animation + '"></div>');

            // Icône
            var $icon = $('<div class="toast-icon">' + icon + '</div>');
            $toast.append($icon);

            // Contenu
            var $content = $('<div class="toast-content"></div>');
            var $message = $('<div class="toast-message">' + message + '</div>');
            $content.append($message);
            $toast.append($content);

            // Bouton de fermeture
            if (settings.closeButton) {
                var $closeBtn = $('<button class="toast-close" aria-label="Fermer">' +
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
                    '<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>' +
                    '</svg>' +
                    '</button>');
                $toast.append($closeBtn);
            }

            // Barre de progression
            if (settings.progressBar && settings.duration > 0) {
                var $progressBar = $('<div class="toast-progress">' +
                    '<div class="toast-progress-bar" style="animation-duration: ' + settings.duration + 'ms;"></div>' +
                    '</div>');
                $toast.append($progressBar);
            }

            return $toast;
        },

        /**
         * Masquer un toast
         */
        hide: function($toast) {
            $toast.removeClass('toast-show');

            setTimeout(function() {
                $toast.remove();
            }, 400);
        },

        /**
         * Obtenir l'icône selon le type
         */
        getIcon: function(type) {
            var icons = {
                success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                    '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>' +
                    '<polyline points="22 4 12 14.01 9 11.01"></polyline>' +
                    '</svg>',
                error: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                    '<circle cx="12" cy="12" r="10"></circle>' +
                    '<line x1="15" y1="9" x2="9" y2="15"></line>' +
                    '<line x1="9" y1="9" x2="15" y2="15"></line>' +
                    '</svg>',
                warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                    '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>' +
                    '<line x1="12" y1="9" x2="12" y2="13"></line>' +
                    '<line x1="12" y1="17" x2="12.01" y2="17"></line>' +
                    '</svg>',
                info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                    '<circle cx="12" cy="12" r="10"></circle>' +
                    '<line x1="12" y1="16" x2="12" y2="12"></line>' +
                    '<line x1="12" y1="8" x2="12.01" y2="8"></line>' +
                    '</svg>'
            };

            return icons[type] || icons.info;
        },

        /**
         * Fermer tous les toasts
         */
        closeAll: function() {
            var self = this;
            this.container.find('.toast').each(function() {
                self.hide($(this));
            });
        }
    };

    /**
     * Alias pour compatibilité avec alert()
     */
    window.showToast = function(message, type, options) {
        type = type || 'info';
        return ToastNotification[type](message, options);
    };

    /**
     * Initialisation automatique au chargement
     */
    $(document).ready(function() {
        ToastNotification.init();
    });

})(jQuery);
