/**
 * EventList - Event Description Validation
 * V1 Le Hiboo - Validation minimum 500 caractères pour publication
 * @version 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Vérifier si on est sur la page de création/édition d'événement
        if (!$('.vendor_edit_event .event_form_wrapper').length) {
            return;
        }

        const MIN_DESCRIPTION_LENGTH = 500;

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
            const eventStatus = $('input[name="event_status"]:checked').val();

            // Validation uniquement si statut = publish
            if (eventStatus !== 'publish') {
                return true;
            }

            const currentLength = getDescriptionLength();

            if (currentLength < MIN_DESCRIPTION_LENGTH) {
                const remaining = MIN_DESCRIPTION_LENGTH - currentLength;

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
            $(document).on('tinymce-editor-init', function(event, editor) {
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

        // Intercepter la soumission du formulaire
        $('.el_edit_event_submit, #trigger_save_event').on('click', function(e) {
            if (!validateBeforeSubmit()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });

        // Styles CSS inline
        $('<style>' +
        '.description-counter { ' +
        '    margin-top: 10px; ' +
        '    padding: 12px 15px; ' +
        '    border-radius: 6px; ' +
        '    font-size: 14px; ' +
        '    line-height: 1.5; ' +
        '}' +
        '.description-counter.counter-invalid { ' +
        '    background: #fff3cd; ' +
        '    border-left: 4px solid #ffc107; ' +
        '    color: #856404; ' +
        '}' +
        '.description-counter.counter-valid { ' +
        '    background: #d4edda; ' +
        '    border-left: 4px solid #28a745; ' +
        '    color: #155724; ' +
        '}' +
        '.description-counter i { ' +
        '    margin-right: 8px; ' +
        '}' +
        '.publication-warning { ' +
        '    margin-bottom: 20px; ' +
        '    padding: 15px; ' +
        '    background: #fff3cd; ' +
        '    border-left: 4px solid #ffc107; ' +
        '    color: #856404; ' +
        '    border-radius: 6px; ' +
        '}' +
        '.publication-warning i { ' +
        '    margin-right: 8px; ' +
        '}' +
        '</style>').appendTo('head');

    });

})(jQuery);
