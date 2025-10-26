/**
 * EventList - Event Description Validation
 * V1 Le Hiboo - Validation minimum 500 caractères pour publication
 * @version 1.0.2
 *
 * Changelog:
 * - v1.0.2: Suppression styles inline - styles maintenant dans _validation-counter.scss
 * - v1.0.1: Version avec double blocage (capture phase + AJAX hook)
 * - v1.0.0: Version initiale
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Vérifier si on est sur la page de création/édition d'événement
        if (!$('.vendor_edit_event .event_form_wrapper').length) {
            return;
        }

        const MIN_DESCRIPTION_LENGTH = 500;

        // Flag global pour indiquer si la validation a échoué
        window.el_description_validation_failed = false;

        /**
         * Compter les caractères dans l'éditeur (en retirant les balises HTML)
         */
        function getDescriptionLength() {
            let content = '';

            // Vérifier si TinyMCE est actif
            if (typeof tinymce !== 'undefined' && tinymce.get('content_event')) {
                content = tinymce.get('content_event').getContent({format: 'text'});
            } else {
                // Fallback sur textarea
                const $textarea = $('#content_event');
                if ($textarea.length) {
                    const html = $textarea.val();
                    const tmp = document.createElement('div');
                    tmp.innerHTML = html;
                    content = tmp.textContent || tmp.innerText || '';
                }
            }

            return content.trim().length;
        }

        /**
         * Afficher le compteur de caractères
         */
        function updateCharacterCounter() {
            const currentLength = getDescriptionLength();
            const remaining = MIN_DESCRIPTION_LENGTH - currentLength;

            let $counter = $('#description-char-counter');

            // Créer le compteur s'il n'existe pas
            if (!$counter.length) {
                const $descriptionField = $('#content_event').closest('.vendor_field');
                if ($descriptionField.length) {
                    $descriptionField.append(
                        '<div id="description-char-counter" class="description-counter"></div>'
                    );
                    $counter = $('#description-char-counter');
                }
            }

            if ($counter.length) {
                if (remaining > 0) {
                    $counter.html(
                        '<span class="counter-warning">' +
                        '<i class="icon_info_alt"></i> ' +
                        'Il manque <strong>' + remaining + ' caractères</strong> pour pouvoir publier l\'activité. ' +
                        '(Minimum requis : 500 caractères pour publication)' +
                        '</span>'
                    );
                    $counter.removeClass('counter-valid').addClass('counter-invalid');
                } else {
                    $counter.html(
                        '<span class="counter-success">' +
                        '<i class="icon_check"></i> ' +
                        'Description valide (' + currentLength + ' caractères)' +
                        '</span>'
                    );
                    $counter.removeClass('counter-invalid').addClass('counter-valid');
                }
            }
        }

        /**
         * Valider avant soumission
         */
        function validateBeforeSubmit() {
            // Reset du flag
            window.el_description_validation_failed = false;

            const eventStatus = $('input[name="event_status"]:checked').val();

            // Validation uniquement si statut = publish
            if (eventStatus !== 'publish') {
                return true;
            }

            const currentLength = getDescriptionLength();

            if (currentLength < MIN_DESCRIPTION_LENGTH) {
                const remaining = MIN_DESCRIPTION_LENGTH - currentLength;

                // Marquer la validation comme échouée
                window.el_description_validation_failed = true;

                alert(
                    'La description doit contenir au minimum 500 caractères pour publier l\'activité.\n\n' +
                    'Actuellement : ' + currentLength + ' caractères\n' +
                    'Il manque : ' + remaining + ' caractères'
                );

                // Scroller vers la description
                const $descriptionSection = $('#presentation');
                if ($descriptionSection.length) {
                    $('html, body').animate({
                        scrollTop: $descriptionSection.offset().top - 100
                    }, 500);

                    // Activer l'onglet présentation
                    $('.profile_tab_item[data-section="presentation"]').trigger('click');
                }

                return false;
            }

            return true;
        }

        /**
         * Afficher une alerte si on sélectionne "Publier" avec une description trop courte
         */
        function checkPublishStatusChange() {
            const $publishRadio = $('#event_status_publish');

            $publishRadio.on('change', function() {
                if ($(this).is(':checked')) {
                    const currentLength = getDescriptionLength();

                    if (currentLength < MIN_DESCRIPTION_LENGTH) {
                        const remaining = MIN_DESCRIPTION_LENGTH - currentLength;

                        // Afficher un message d'avertissement (non bloquant)
                        const $publicationSection = $(this).closest('.event_basic_block');

                        let $warning = $publicationSection.find('.publication-warning');
                        if (!$warning.length) {
                            $publicationSection.prepend(
                                '<div class="publication-warning alert alert-warning">' +
                                '<i class="icon_error-circle_alt"></i> ' +
                                '<strong>Attention :</strong> La description ne contient que <strong>' + currentLength + ' caractères</strong>. ' +
                                'Il manque <strong>' + remaining + ' caractères</strong> pour pouvoir publier l\'activité.' +
                                '</div>'
                            );
                        } else {
                            $warning.html(
                                '<i class="icon_error-circle_alt"></i> ' +
                                '<strong>Attention :</strong> La description ne contient que <strong>' + currentLength + ' caractères</strong>. ' +
                                'Il manque <strong>' + remaining + ' caractères</strong> pour pouvoir publier l\'activité.'
                            );
                        }
                    }
                }
            });
        }

        // Initialiser le compteur de caractères
        updateCharacterCounter();

        // Mettre à jour le compteur à chaque modification
        if (typeof tinymce !== 'undefined') {
            // Pour TinyMCE
            $(document).on('tinymce-editor-init', function(_event, editor) {
                if (editor.id === 'content_event') {
                    editor.on('keyup change', function() {
                        updateCharacterCounter();
                    });
                }
            });
        }

        // Pour textarea (fallback)
        $('#content_event').on('keyup change', function() {
            updateCharacterCounter();
        });

        // Vérifier lors du changement de statut
        checkPublishStatusChange();

        // Intercepter les clics sur les boutons AVANT tout autre handler
        // Utiliser capture phase pour être exécuté en premier
        $('.el_edit_event_submit, #trigger_save_event').each(function() {
            const button = this;
            button.addEventListener('click', function(e) {
                console.log('Click intercepté sur bouton submit');

                // Valider avant soumission
                if (!validateBeforeSubmit()) {
                    console.log('Validation échouée - blocage du submit');
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    // Empêcher tous les événements suivants
                    return false;
                }

                console.log('Validation réussie - autorisation du submit');
            }, true); // true = capture phase (exécuté en premier)
        });

        // Hook AJAX beforeSend pour bloquer si validation échouée
        $(document).ajaxSend(function(_event, jqxhr, settings) {
            // Vérifier si c'est une requête de sauvegarde d'événement
            if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('el_save_edit_event') !== -1) {
                if (window.el_description_validation_failed === true) {
                    // Annuler la requête AJAX
                    jqxhr.abort();
                    window.el_description_validation_failed = false;
                    console.log('Soumission bloquée : description trop courte');
                    return false;
                }
            }
        });

        // Note: Les styles CSS sont maintenant dans /assets/css/frontend/vendor/_validation-counter.scss

    });

})(jQuery);
