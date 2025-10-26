/**
 * EventList - Profile Presentation Validation
 * V1 Le Hiboo - Validation minimum 500 caractères pour présentation organisateur
 * @version 1.0.8
 *
 * Changelog:
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

(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('🔵 Profile Presentation Validation - Script chargé');
        console.log('🔵 Recherche .vendor_profile:', $('.vendor_profile').length);
        console.log('🔵 Recherche .vendor_wrap:', $('.vendor_wrap').length);

        // Vérifier si on est sur la page profil
        if (!$('.vendor_profile').length && !$('.vendor_wrap').length) {
            console.log('❌ Pas de .vendor_profile ou .vendor_wrap - sortie');
            return;
        }

        console.log('✅ Page profil détectée');

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
            console.log('📊 updateCharacterCounter - currentLength:', currentLength, 'remaining:', remaining);

            let $counter = $('#presentation-char-counter');
            console.log('🔍 Compteur existant:', $counter.length);

            // Créer le compteur s'il n'existe pas
            if (!$counter.length) {
                const $descriptionField = $('#description').closest('.vendor_field');
                console.log('🔍 Champ description parent (.vendor_field):', $descriptionField.length);

                if ($descriptionField.length) {
                    console.log('➕ Création du compteur');
                    $descriptionField.append(
                        '<div id="presentation-char-counter" class="description-counter"></div>'
                    );
                    $counter = $('#presentation-char-counter');
                    console.log('✅ Compteur créé:', $counter.length);
                } else {
                    console.log('❌ Impossible de trouver .vendor_field parent');
                }
            }

            if ($counter.length) {
                if (remaining > 0) {
                    $counter.html(
                        '<span class="counter-warning">' +
                        '<i class="icon_info_alt"></i> ' +
                        'Il manque <strong>' + remaining + ' caractères</strong> pour pouvoir enregistrer la présentation. ' +
                        '(Minimum requis : 500 caractères pour enregistrement)' +
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
        console.log('🚀 Initialisation du compteur...');
        console.log('🔍 Recherche textarea #description:', $('#description').length);
        updateCharacterCounter();

        // Mettre à jour le compteur à chaque modification
        if (typeof tinymce !== 'undefined') {
            console.log('✅ TinyMCE disponible');
            // Pour TinyMCE
            $(document).on('tinymce-editor-init', function(_event, editor) {
                console.log('📝 TinyMCE editor init:', editor.id);
                if (editor.id === 'description') {
                    console.log('✅ Écoute événements TinyMCE sur description');
                    editor.on('keyup change', function() {
                        console.log('⌨️ Événement TinyMCE détecté');
                        updateCharacterCounter();
                    });
                }
            });
        } else {
            console.log('⚠️ TinyMCE non disponible');
        }

        // Pour textarea (fallback)
        console.log('➕ Ajout listener sur textarea #description');
        $('#description').on('keyup change', function() {
            console.log('⌨️ Événement textarea détecté');
            updateCharacterCounter();
        });

        // Intercepter les clics sur le bouton de sauvegarde
        // Utiliser capture phase pour être exécuté en premier
        console.log('🔍 Recherche bouton el_update_presentation:', $('input[name="el_update_presentation"]').length);
        $('input[name="el_update_presentation"]').each(function() {
            console.log('➕ Ajout listener sur bouton sauvegarde');
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

        // Note: Les styles CSS sont maintenant dans /assets/css/frontend/vendor/_validation-counter.scss

    });

})(jQuery);
