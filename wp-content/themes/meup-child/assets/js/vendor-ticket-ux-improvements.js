/**
 * LeHiboo V1 - Améliorations UX Billetterie
 *
 * Améliore l'expérience utilisateur du formulaire de billetterie :
 * - Mise à jour dynamique du compteur de billets
 * - Indicateurs visuels de validation
 * - Feedback utilisateur amélioré
 *
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Mise à jour du compteur de billets
     */
    function updateTicketCounter() {
        var count = 0;

        // Compter les billets qui ont un nom
        $('.list_type_ticket .item_ticket').each(function() {
            var ticketName = $(this).find('.name_ticket').val();
            if (ticketName && ticketName.trim() !== '') {
                count++;
            }
        });

        // Mettre à jour l'affichage
        var $counter = $('.ticket-count-number');
        var $text = $counter.parent();

        if ($counter.length) {
            $counter.text(count);

            // Mettre à jour le texte pluriel
            var textSingular = 'billet configuré';
            var textPlural = 'billets configurés';

            var currentText = $text.html();
            if (count > 1) {
                $text.html(currentText.replace(textSingular, textPlural));
            } else {
                $text.html(currentText.replace(textPlural, textSingular));
            }

            // Animation visuelle
            $counter.css({
                'transform': 'scale(1.2)',
                'color': '#10b981'
            });

            setTimeout(function() {
                $counter.css({
                    'transform': 'scale(1)',
                    'color': ''
                });
            }, 300);
        }
    }

    /**
     * Ajouter un indicateur visuel de validation au billet
     */
    function addTicketValidationIndicator($ticket) {
        // Vérifier si le billet a un nom
        var ticketName = $ticket.find('.name_ticket').val();
        var $header = $ticket.find('.heading_ticket');

        // Retirer les anciens indicateurs
        $header.find('.ticket-status-badge').remove();

        if (ticketName && ticketName.trim() !== '') {
            // Ajouter badge "Validé"
            var $badge = $('<span class="ticket-status-badge" style="margin-left: 10px; padding: 3px 10px; background: #10b981; color: white; border-radius: 12px; font-size: 11px; font-weight: 600;">✓ Validé</span>');
            $header.find('.left').append($badge);
        } else {
            // Ajouter badge "En cours"
            var $badge = $('<span class="ticket-status-badge" style="margin-left: 10px; padding: 3px 10px; background: #f59e0b; color: white; border-radius: 12px; font-size: 11px; font-weight: 600;">⚠ En cours</span>');
            $header.find('.left').append($badge);
        }
    }

    /**
     * Toast notification simple
     */
    function showToast(message, type) {
        type = type || 'success';

        var bgColor = type === 'success' ? '#10b981' : '#f59e0b';
        var icon = type === 'success' ? '✓' : 'ℹ';

        var $toast = $('<div class="lehiboo-toast" style="position: fixed; bottom: 30px; right: 30px; background: ' + bgColor + '; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 99999; font-weight: 600; display: flex; align-items: center; gap: 10px; animation: slideInUp 0.3s ease;">' +
            '<span style="font-size: 18px;">' + icon + '</span>' +
            '<span>' + message + '</span>' +
        '</div>');

        $('body').append($toast);

        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Améliorer le feedback du bouton "Valider ce billet"
     */
    function improveTicketSaveButton() {
        $(document).on('click', '.save_ticket', function(e) {
            e.preventDefault();

            var $ticket = $(this).closest('.item_ticket');
            var ticketName = $ticket.find('.name_ticket').val();

            // Validation
            if (!ticketName || ticketName.trim() === '') {
                showToast('Veuillez saisir un nom pour le billet', 'warning');
                $ticket.find('.name_ticket').focus();
                return false;
            }

            // Fermer le panneau
            $ticket.find('.content_ticket').slideUp();

            // Ajouter l'indicateur de validation
            addTicketValidationIndicator($ticket);

            // Mettre à jour le compteur
            updateTicketCounter();

            // Feedback
            showToast('Billet validé : ' + ticketName, 'success');
        });
    }

    /**
     * Améliorer le bouton "Ajouter un billet"
     */
    function improveAddTicketButton() {
        $(document).on('click', '.add_ticket', function() {
            // Attendre que le nouveau billet soit ajouté par le code d'origine
            setTimeout(function() {
                updateTicketCounter();
                showToast('Nouveau billet ajouté', 'success');

                // Scroll vers le nouveau billet
                var $lastTicket = $('.list_type_ticket .item_ticket').last();
                if ($lastTicket.length) {
                    $('html, body').animate({
                        scrollTop: $lastTicket.offset().top - 100
                    }, 500);

                    // Focus sur le nom du billet
                    setTimeout(function() {
                        $lastTicket.find('.name_ticket').focus();
                    }, 600);
                }
            }, 500);
        });
    }

    /**
     * Améliorer la suppression de billet
     */
    function improveDeleteTicketButton() {
        $(document).on('click', '.delete_ticket', function() {
            var $ticket = $(this).closest('.item_ticket');
            var ticketName = $ticket.find('.name_ticket').val() || 'ce billet';

            // Confirmation
            if (!confirm('Êtes-vous sûr de vouloir supprimer "' + ticketName + '" ?')) {
                return false;
            }

            // Attendre que le billet soit supprimé par le code d'origine
            setTimeout(function() {
                updateTicketCounter();
                showToast('Billet supprimé', 'success');
            }, 300);
        });
    }

    /**
     * Mise à jour dynamique du compteur quand le nom change
     */
    function watchTicketNameChanges() {
        $(document).on('input', '.name_ticket', function() {
            var $ticket = $(this).closest('.item_ticket');
            addTicketValidationIndicator($ticket);
        });

        // Mise à jour initiale au focus
        $(document).on('focus', '.name_ticket', function() {
            var $ticket = $(this).closest('.item_ticket');
            $ticket.find('.ticket-status-badge').remove();
        });
    }

    /**
     * Améliorer le message de sauvegarde de l'événement
     */
    function improveSaveEventFeedback() {
        // Intercepter le submit du formulaire principal
        $('#event_edit_form').on('submit', function() {
            var ticketCount = 0;

            $('.list_type_ticket .item_ticket').each(function() {
                var ticketName = $(this).find('.name_ticket').val();
                if (ticketName && ticketName.trim() !== '') {
                    ticketCount++;
                }
            });

            if (ticketCount > 0) {
                console.log('Sauvegarde de l\'événement avec ' + ticketCount + ' billet(s)');
            }
        });
    }

    /**
     * Ajouter des tooltips aux champs complexes
     */
    function addHelpTooltips() {
        // Tooltip pour les couleurs
        var colorFields = $('.el_color_wrap');
        if (colorFields.length) {
            colorFields.each(function() {
                var $field = $(this);
                var helpText = 'Ces couleurs seront utilisées pour le design de vos billets PDF';

                if (!$field.find('.tooltip-icon').length) {
                    var $tooltip = $('<span class="tooltip-icon" style="margin-left: 5px; cursor: help; color: #6b7280;" title="' + helpText + '">ℹ️</span>');
                    $field.find('label').first().append($tooltip);
                }
            });
        }
    }

    /**
     * Initialisation au chargement du document
     */
    $(document).ready(function() {
        console.log('LeHiboo V1: Améliorations UX Billetterie chargées');

        // Initialiser tous les billets existants avec des indicateurs
        $('.list_type_ticket .item_ticket').each(function() {
            addTicketValidationIndicator($(this));
        });

        // Activer toutes les améliorations
        improveTicketSaveButton();
        improveAddTicketButton();
        improveDeleteTicketButton();
        watchTicketNameChanges();
        improveSaveEventFeedback();
        addHelpTooltips();

        // CSS pour l'animation du toast
        if (!$('#lehiboo-toast-animation').length) {
            $('head').append('<style id="lehiboo-toast-animation">@keyframes slideInUp { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }</style>');
        }
    });

})(jQuery);
