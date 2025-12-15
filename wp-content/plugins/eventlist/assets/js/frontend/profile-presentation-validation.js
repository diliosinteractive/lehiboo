/**
 * EventList - Profile Presentation Validation
 * V1 Le Hiboo - Recommandation 500 caractères pour présentation organisateur (non bloquant)
 * @version 1.1.0
 *
 * Changelog:
 * - v1.1.0: Description facultative - message de recommandation uniquement (non bloquant)
 * - v1.0.8: Uniformisation du texte avec la page événement (ajout "pour enregistrement")
 * - v1.0.7: Suppression styles inline - styles maintenant dans _validation-counter.scss
 * - v1.0.6: FIX CRITIQUE - Correction sélecteur page (.vendor_profile au lieu de .vendor_profile_wrapper)
 * - v1.0.5: Ajout logs complets pour debugging - identifier pourquoi compteur ne s'affiche pas
 * - v1.0.4: Simplification complète - approche identique au script event-description-validation.js
 * - v1.0.3: Simplification - priorité textarea avec fallback TinyMCE + logs debugging
 * - v1.0.2: Ajout logs de debugging + stratégie multi-tentatives + fix sélecteur bouton
 * - v1.0.1: Ajout compatibilité TinyMCE (wp_editor) pour le champ Description
 * - v1.0.0: Version initiale (textarea simple uniquement)
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Vérifier si on est sur la page profil
        if (!$('.vendor_profile').length && !$('.vendor_wrap').length) {
            return;
        }

        const RECOMMENDED_LENGTH = 500;

        /**
         * Compter les caractères dans l'éditeur (en retirant les balises HTML)
         */
        function getDescriptionLength() {
            let content = '';

            // Vérifier si TinyMCE est actif
            if (typeof tinymce !== 'undefined' && tinymce.get('description')) {
                content = tinymce.get('description').getContent({ format: 'text' });
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
         * Afficher le compteur de caractères (recommandation, non bloquant)
         */
        function updateCharacterCounter() {
            const currentLength = getDescriptionLength();
            const remaining = RECOMMENDED_LENGTH - currentLength;

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
                        'Il manque <strong>' + remaining + ' caractères</strong> pour une présentation optimisée sur votre page. ' +
                        'Nous vous recommandons 500 caractères minimum.' +
                        '</span>'
                    );
                    $counter.removeClass('counter-valid').addClass('counter-recommendation');
                } else {
                    $counter.html(
                        '<span class="counter-success">' +
                        '<i class="icon_check"></i> ' +
                        'Présentation optimisée (' + currentLength + ' caractères)' +
                        '</span>'
                    );
                    $counter.removeClass('counter-recommendation').addClass('counter-valid');
                }
            }
        }

        // Initialiser le compteur au chargement
        updateCharacterCounter();

        // Mettre à jour le compteur à chaque modification
        if (typeof tinymce !== 'undefined') {
            // Pour TinyMCE
            $(document).on('tinymce-editor-init', function (_event, editor) {
                if (editor.id === 'description') {
                    editor.on('keyup change', function () {
                        updateCharacterCounter();
                    });
                }
            });
        }

        // Pour textarea (fallback)
        $('#description').on('keyup change', function () {
            updateCharacterCounter();
        });

        // Note: Les styles CSS sont dans /assets/css/frontend/vendor/_validation-counter.scss
        // La description est facultative - pas de validation bloquante

    });

})(jQuery);
