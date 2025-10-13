/**
 * EventList - Event Form Navigation (Vertical Tabs)
 * Gestion de la navigation entre les onglets du formulaire de création/édition d'événement
 * Utilise exactement les mêmes sélecteurs que profile-navigation.js
 * V1 Le Hiboo - Phase 6
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Vérifier si on est sur la page de création/édition d'événement
        if (!$('.vendor_edit_event .event_form_wrapper').length) {
            return;
        }

        // Navigation entre les onglets (même code que profil)
        $('.vendor_edit_event .profile_tab_item').on('click', function(e) {
            e.preventDefault();

            var targetTab = $(this).data('tab');

            // Retirer la classe active de tous les onglets
            $('.vendor_edit_event .profile_tab_item').removeClass('active');

            // Ajouter la classe active à l'onglet cliqué
            $(this).addClass('active');

            // Masquer tous les contenus
            $('.vendor_edit_event .tab-contents').removeClass('active').hide();

            // Afficher le contenu correspondant avec animation
            $('#' + targetTab).addClass('active').fadeIn(300);

            // Mettre à jour le hash de l'URL sans scroll
            if (history.pushState) {
                history.pushState(null, null, '#' + targetTab);
            } else {
                window.location.hash = '#' + targetTab;
            }
        });

        // Gérer le hash de l'URL au chargement de la page
        function initTabFromHash() {
            var hash = window.location.hash;

            if (hash) {
                var targetTab = hash.substring(1); // Retirer le #
                var $targetTabItem = $('.vendor_edit_event .profile_tab_item[data-tab="' + targetTab + '"]');

                if ($targetTabItem.length) {
                    $targetTabItem.trigger('click');
                    return;
                }
            }

            // Par défaut, afficher le premier onglet
            $('.vendor_edit_event .profile_tab_item:first').addClass('active');
            $('.vendor_edit_event .tab-contents:first').addClass('active').show();
            $('.vendor_edit_event .tab-contents:not(:first)').hide();
        }

        initTabFromHash();

        // Gérer le bouton retour du navigateur
        $(window).on('hashchange', function() {
            initTabFromHash();
        });

        // Smooth scroll vers le formulaire sur mobile quand on change d'onglet
        if ($(window).width() <= 991) {
            $('.vendor_edit_event .profile_tab_item').on('click', function() {
                var $form = $('.vendor_edit_event .event_form_wrapper');
                if ($form.length) {
                    $('html, body').animate({
                        scrollTop: $form.offset().top - 100
                    }, 300);
                }
            });
        }

        // Ajouter classe 'is-scrolled' à la barre sticky au scroll
        var $stickyBar = $('.event_form_sticky_bar');
        if ($stickyBar.length) {
            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 50) {
                    $stickyBar.addClass('is-scrolled');
                } else {
                    $stickyBar.removeClass('is-scrolled');
                }
            });
        }

        // Le bouton de la sticky bar déclenche le bouton submit réel
        $('#trigger_save_event').on('click', function(e) {
            e.preventDefault();

            var $stickyBtn = $(this);
            var $realButton = $('.el_edit_event_submit');

            if ($realButton.length) {
                // Afficher le loader sur le bouton sticky
                $stickyBtn.addClass('loading');

                // Déclencher le clic sur le vrai bouton qui est dans le formulaire
                $realButton.trigger('click');
            }
        });

        // Enlever le loader quand la requête AJAX est terminée
        $(document).ajaxComplete(function() {
            $('#trigger_save_event').removeClass('loading');
        });

    });

})(jQuery);
