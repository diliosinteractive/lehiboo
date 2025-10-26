/**
 * EventList - Profile Presentation Validation
 * V1 Le Hiboo - Validation minimum 500 caractères pour présentation organisateur
 * @version 1.0.4
 *
 * Changelog:
 * - v1.0.4: Simplification complète - approche identique au script event-description-validation.js
 * - v1.0.3: Simplification - priorité textarea avec fallback TinyMCE + logs debugging
 * - v1.0.2: Ajout logs de debugging + stratégie multi-tentatives + fix sélecteur bouton
 * - v1.0.1: Ajout compatibilité TinyMCE (wp_editor) pour le champ Description
 * - v1.0.0: Version initiale (textarea simple uniquement)
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Vérifier si on est sur la page profil
        if (!$('.vendor_profile_wrapper').length) {
            return;
        }

        const MIN_DESCRIPTION_LENGTH = 500;

        // Flag global pour indiquer si la validation a échoué
        window.el_presentation_validation_failed = false;

        /**
         * Compter les caractères dans l'éditeur (en retirant les balises HTML)
         */
        function getDescriptionLength() {
            let content = '';

            // Vérifier si TinyMCE est actif
            if (typeof tinymce !== 'undefined' && tinymce.get('description')) {
                content = tinymce.get('description').getContent({format: 'text'});
            } else {
                // Fallback sur textarea
                const $textarea = $('#description');
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

            let $counter = $('#presentation-char-counter');

            // Créer le compteur s'il n'existe pas
            if (!$counter.length) {
                const $descriptionField = $('#description').closest('.vendor_field');
                if ($descriptionField.length) {
                    $descriptionField.append(
                        '<div id="presentation-char-counter" class="description-counter"></div>'
                    );
                    $counter = $('#presentation-char-counter');
                }
            }

            if ($counter.length) {
                if (remaining > 0) {
                    $counter.html(
                        '<span class="counter-warning">' +
                        '<i class="icon_info_alt"></i> ' +
                        'Il manque <strong>' + remaining + ' caractères</strong> pour pouvoir enregistrer la présentation. ' +
                        '(Minimum requis : 500 caractères)' +
                        '</span>'
                    );
                    $counter.removeClass('counter-valid').addClass('counter-invalid');
                } else {
                    $counter.html(
                        '<span class="counter-success">' +
                        '<i class="icon_check"></i> ' +
                        'Présentation valide (' + currentLength + ' caractères)' +
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
            window.el_presentation_validation_failed = false;

            const currentLength = getDescriptionLength();

            if (currentLength < MIN_DESCRIPTION_LENGTH) {
                const remaining = MIN_DESCRIPTION_LENGTH - currentLength;

                // Marquer la validation comme échouée
                window.el_presentation_validation_failed = true;

                alert(
                    'La présentation doit contenir au minimum 500 caractères.\n\n' +
                    'Actuellement : ' + currentLength + ' caractères\n' +
                    'Il manque : ' + remaining + ' caractères'
                );

                // Scroller vers la description
                const $descriptionSection = $('#author_presentation');
                if ($descriptionSection.length) {
                    $('html, body').animate({
                        scrollTop: $descriptionSection.offset().top - 100
                    }, 500);

                    // Activer l'onglet présentation
                    $('.profile_tab_item[data-section="author_presentation"]').trigger('click');
                }

                return false;
            }

            return true;
        }

        // Initialiser le compteur de caractères
        updateCharacterCounter();

        // Mettre à jour le compteur à chaque modification
        if (typeof tinymce !== 'undefined') {
            // Pour TinyMCE
            $(document).on('tinymce-editor-init', function(_event, editor) {
                if (editor.id === 'description') {
                    editor.on('keyup change', function() {
                        updateCharacterCounter();
                    });
                }
            });
        }

        // Pour textarea (fallback)
        $('#description').on('keyup change', function() {
            updateCharacterCounter();
        });

        // Intercepter les clics sur le bouton de sauvegarde
        // Utiliser capture phase pour être exécuté en premier
        $('input[name="el_update_presentation"]').each(function() {
            const button = this;
            button.addEventListener('click', function(e) {
                // Valider avant soumission
                if (!validateBeforeSubmit()) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true); // true = capture phase
        });

        // Hook AJAX beforeSend pour bloquer si validation échouée
        $(document).ajaxSend(function(_event, jqxhr, settings) {
            // Vérifier si c'est une requête de sauvegarde de présentation
            if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('el_update_presentation') !== -1) {
                if (window.el_presentation_validation_failed === true) {
                    // Annuler la requête AJAX
                    jqxhr.abort();
                    window.el_presentation_validation_failed = false;
                    return false;
                }
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
        '</style>').appendTo('head');

    });

})(jQuery);
