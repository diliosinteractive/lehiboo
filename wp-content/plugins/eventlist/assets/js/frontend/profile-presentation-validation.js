/**
 * EventList - Profile Presentation Validation
 * V1 Le Hiboo - Validation minimum 500 caractères pour présentation organisateur
 * @version 1.0.3
 *
 * Changelog:
 * - v1.0.3: Simplification - priorité textarea avec fallback TinyMCE + logs debugging
 * - v1.0.2: Ajout logs de debugging + stratégie multi-tentatives + fix sélecteur bouton
 * - v1.0.1: Ajout compatibilité TinyMCE (wp_editor) pour le champ Description
 * - v1.0.0: Version initiale (textarea simple uniquement)
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Profile Presentation Validation - Script chargé');

        // Vérifier si on est sur la page profil avec onglet présentation
        if (!$('.vendor_profile_wrapper').length) {
            console.log('Pas de .vendor_profile_wrapper trouvé - sortie');
            return;
        }

        console.log('Page profil détectée');

        const MIN_DESCRIPTION_LENGTH = 500;

        // Flag global pour indiquer si la validation a échoué
        window.el_presentation_validation_failed = false;

        /**
         * Compter les caractères dans le textarea de description
         * Compatible avec TinyMCE et textarea simple
         */
        function getDescriptionLength() {
            let content = '';

            // Essayer d'abord avec TinyMCE
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('description');
                if (editor && !editor.isHidden()) {
                    content = editor.getContent({format: 'text'}) || '';
                    console.log('Contenu récupéré depuis TinyMCE:', content.length + ' chars');
                    // Retirer les balises HTML et compter
                    const textOnly = content.replace(/<[^>]*>/g, '').trim();
                    console.log('Longueur finale après nettoyage:', textOnly.length);
                    return textOnly.length;
                }
            }

            // Sinon, utiliser directement le textarea
            const $textarea = $('#description');
            if ($textarea.length) {
                content = $textarea.val() || '';
                console.log('Contenu récupéré depuis textarea (#description):', content.length + ' chars');
                // Retirer les balises HTML et compter
                const textOnly = content.replace(/<[^>]*>/g, '').trim();
                console.log('Longueur finale après nettoyage:', textOnly.length);
                return textOnly.length;
            }

            console.log('Aucun champ description trouvé');
            return 0;
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
                // Chercher le wrapper WYSIWYG qui contient l'éditeur
                const $descriptionField = $('.vendor_field.wysiwyg');
                if ($descriptionField.length) {
                    // Insérer après le div.wysiwyg-wrapper (après l'éditeur)
                    const $wrapper = $descriptionField.find('.wysiwyg-wrapper');
                    if ($wrapper.length) {
                        $wrapper.after(
                            '<div id="presentation-char-counter" class="description-counter"></div>'
                        );
                    } else {
                        // Fallback : insérer à la fin du champ
                        $descriptionField.append(
                            '<div id="presentation-char-counter" class="description-counter"></div>'
                        );
                    }
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
                const $wysiwyg = $('.vendor_field.wysiwyg');
                if ($wysiwyg.length) {
                    $('html, body').animate({
                        scrollTop: $wysiwyg.offset().top - 100
                    }, 500);

                    // Focus sur TinyMCE si disponible
                    if (typeof tinymce !== 'undefined' && tinymce.get('description')) {
                        setTimeout(function() {
                            tinymce.get('description').focus();
                        }, 600);
                    } else {
                        // Fallback sur textarea
                        const $descriptionField = $('#description');
                        if ($descriptionField.length) {
                            $descriptionField.focus();
                        }
                    }
                }

                return false;
            }

            return true;
        }

        // Initialiser le compteur de caractères
        function initCharacterCounter() {
            console.log('initCharacterCounter() appelé');

            // Chercher le textarea
            const $textarea = $('#description');
            console.log('Textarea #description trouvé:', $textarea.length);

            if (!$textarea.length) {
                console.log('Aucun textarea #description trouvé');
                return false;
            }

            // Afficher le compteur immédiatement
            updateCharacterCounter();
            console.log('Compteur initialisé');

            // Écouter les événements sur le textarea
            $textarea.on('keyup change input blur', function() {
                console.log('Événement textarea détecté');
                updateCharacterCounter();
            });

            // Si TinyMCE existe, écouter aussi ses événements
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('description');
                if (editor) {
                    console.log('TinyMCE détecté, ajout listeners TinyMCE');
                    editor.on('keyup change input NodeChange blur', function() {
                        console.log('Événement TinyMCE détecté');
                        updateCharacterCounter();
                    });
                }
            }

            return true;
        }

        // Stratégie multi-tentatives pour initialiser
        let initAttempts = 0;
        const maxAttempts = 5;

        function tryInit() {
            initAttempts++;
            console.log('Tentative d\'initialisation #' + initAttempts);

            if (initCharacterCounter()) {
                console.log('✅ Initialisation réussie');
                return;
            }

            if (initAttempts < maxAttempts) {
                console.log('⏳ Nouvelle tentative dans 300ms...');
                setTimeout(tryInit, 300);
            } else {
                console.error('❌ Échec initialisation après ' + maxAttempts + ' tentatives');
            }
        }

        // Démarrer les tentatives d'initialisation rapidement
        console.log('Démarrage tentatives d\'initialisation');
        setTimeout(tryInit, 500);

        // Intercepter les clics sur le bouton de sauvegarde
        // Utiliser capture phase pour être exécuté en premier
        console.log('Recherche bouton de sauvegarde présentation...');
        const $saveButton = $('input[name="el_update_presentation"]');
        console.log('Bouton trouvé:', $saveButton.length);

        $saveButton.each(function() {
            const button = this;
            button.addEventListener('click', function(e) {
                console.log('Click intercepté sur bouton save presentation');

                // Valider avant soumission
                if (!validateBeforeSubmit()) {
                    console.log('Validation présentation échouée - blocage');
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }

                console.log('Validation présentation réussie');
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
                    console.log('Soumission présentation bloquée : description trop courte');
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
