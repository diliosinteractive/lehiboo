/**
 * JavaScript pour le module Co-organisateurs
 * Le Hiboo V1
 */

(function($) {
    'use strict';

    /**
     * Gestionnaire principal du module co-organisateurs
     */
    var EL_Coorg = {

        /**
         * Initialisation
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Lie les événements
         */
        bindEvents: function() {
            // Les événements spécifiques aux pages sont gérés dans les templates
            // Ce fichier peut être étendu pour des fonctionnalités globales
        },

        /**
         * Affiche un message de notification
         */
        showNotification: function(message, type) {
            type = type || 'info';

            var notification = $('<div class="el_coorg_notification el_coorg_notification_' + type + '"></div>');
            notification.text(message);

            $('body').append(notification);

            setTimeout(function() {
                notification.addClass('el_coorg_notification_show');
            }, 100);

            setTimeout(function() {
                notification.removeClass('el_coorg_notification_show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        },

        /**
         * Affiche un spinner de chargement
         */
        showLoader: function(element) {
            var loader = $('<div class="el_coorg_loader"><div class="el_coorg_spinner"></div></div>');
            $(element).append(loader);
            return loader;
        },

        /**
         * Masque le spinner de chargement
         */
        hideLoader: function(loader) {
            $(loader).remove();
        }
    };

    /**
     * Initialisation au chargement du document
     */
    $(document).ready(function() {
        EL_Coorg.init();
    });

    // Exposer globalement
    window.EL_Coorg = EL_Coorg;

})(jQuery);
