/**
 * Organizer Details Popup - Gestion des interactions
 *
 * Gère l'ouverture/fermeture du popup de détails de l'organisateur
 * sur la page de détail d'activité.
 *
 * @package LeHiboo
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // ============================================
        // Popup Détails Organisateur
        // ============================================

        // Ouvrir le popup détails organisateur
        $(document).on('click', '#open_organizer_details_popup', function(e) {
            e.preventDefault();

            const popup = $('#organizer_details_popup');

            if (popup.length) {
                popup.fadeIn(300);
                $('body').addClass('organizer-popup-open');

                // Animation du container
                popup.find('.organizer_popup_container').css({
                    'transform': 'translateY(20px)',
                    'opacity': '0'
                }).animate({
                    'opacity': '1'
                }, 300).css({
                    'transform': 'translateY(0)'
                });
            }
        });

        // Fermer le popup détails organisateur
        function closeOrganizerPopup() {
            const popup = $('#organizer_details_popup');

            if (popup.length) {
                popup.fadeOut(300);
                $('body').removeClass('organizer-popup-open');
            }
        }

        // Clic sur le bouton close
        $(document).on('click', '.organizer_popup_close', function(e) {
            e.preventDefault();
            closeOrganizerPopup();
        });

        // Clic sur l'overlay
        $(document).on('click', '.organizer_popup_overlay', function(e) {
            e.preventDefault();
            closeOrganizerPopup();
        });

        // Fermeture avec la touche Échap
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                if ($('#organizer_details_popup').is(':visible')) {
                    closeOrganizerPopup();
                }
            }
        });

        // ============================================
        // Bouton Contact Organisateur (Modal existant)
        // ============================================

        // Ouvrir le modal de contact
        $(document).on('click', '.btn_contact_organizer, #open_contact_modal', function(e) {
            e.preventDefault();

            const requireLogin = $(this).data('require-login');

            // Si login requis et utilisateur non connecté
            if (requireLogin === true || requireLogin === 'true') {
                // Afficher un message ou rediriger vers la page de connexion
                if (typeof show_auth_popup === 'function') {
                    show_auth_popup();
                } else {
                    alert('Vous devez être connecté pour contacter l\'organisateur.');
                }
                return;
            }

            const modal = $('#contact_modal_author');

            if (modal.length) {
                modal.fadeIn(300).addClass('active');
                $('body').addClass('modal-open organizer-popup-open');

                // Animation du container
                modal.find('.contact_modal_container').css({
                    'transform': 'translate(-50%, -50%) scale(0.9)',
                    'opacity': '0'
                }).animate({
                    'opacity': '1'
                }, 300).css({
                    'transform': 'translate(-50%, -50%) scale(1)'
                });
            }
        });

        // Fermer le modal de contact
        function closeContactModal() {
            const modal = $('#contact_modal_author');

            if (modal.length) {
                modal.fadeOut(300).removeClass('active');
                $('body').removeClass('modal-open organizer-popup-open');
            }
        }

        // Clic sur le bouton close du modal
        $(document).on('click', '.contact_modal_close', function(e) {
            e.preventDefault();
            closeContactModal();
        });

        // Clic sur l'overlay du modal
        $(document).on('click', '.contact_modal_overlay', function(e) {
            e.preventDefault();
            closeContactModal();
        });

        // Fermeture avec la touche Échap (modal contact)
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                if ($('#contact_modal_author').is(':visible')) {
                    closeContactModal();
                }
            }
        });

        // ============================================
        // Empêcher la propagation des clics à l'intérieur des popups
        // ============================================

        $(document).on('click', '.organizer_popup_container, .contact_modal_container', function(e) {
            e.stopPropagation();
        });

        // ============================================
        // Gestion du scroll dans les popups
        // ============================================

        // Bloquer le scroll de la page quand un popup est ouvert
        $('body.organizer-popup-open, body.modal-open').css({
            'overflow': 'hidden',
            'padding-right': getScrollbarWidth() + 'px'
        });

        // Calculer la largeur de la scrollbar
        function getScrollbarWidth() {
            const outer = document.createElement('div');
            outer.style.visibility = 'hidden';
            outer.style.overflow = 'scroll';
            document.body.appendChild(outer);

            const inner = document.createElement('div');
            outer.appendChild(inner);

            const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
            outer.parentNode.removeChild(outer);

            return scrollbarWidth;
        }

        // Restaurer le scroll quand les popups se ferment
        $(document).on('click', '.organizer_popup_close, .organizer_popup_overlay, .contact_modal_close, .contact_modal_overlay', function() {
            setTimeout(function() {
                if (!$('#organizer_details_popup').is(':visible') && !$('#contact_modal_author').is(':visible')) {
                    $('body').css({
                        'overflow': '',
                        'padding-right': ''
                    });
                }
            }, 300);
        });

    });

})(jQuery);
