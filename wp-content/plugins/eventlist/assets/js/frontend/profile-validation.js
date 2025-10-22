/**
 * Validation des onglets du profil partenaire
 * V1 Le Hiboo
 */

(function($) {
    'use strict';

    // Configuration des champs requis par onglet
    // Note: author_localisation supprimé (fusionné dans author_organisation)
    // Note: author_password et author_bank exclus du calcul (optionnels)
    const tabRequirements = {
        'author_profile': {
            required: ['first_name', 'last_name', 'user_email'],
            needed: ['user_professional_email', 'user_job', 'user_phone'],
            visible: [],
            countInScore: true // Compte dans le score de complétion
        },
        'author_organisation': {
            required: ['org_name', 'org_forme_juridique', 'org_siren'],
            needed: ['org_role[]', 'org_type_structure[]', 'user_address_line1', 'user_city', 'user_postcode'],
            visible: ['org_display_name'],
            countInScore: true
        },
        'author_presentation': {
            required: [],
            needed: ['description', 'org_cover_image'],
            visible: ['org_email_contact', 'org_phone_contact', 'org_web', 'org_video_youtube'],
            countInScore: true
        }
        // author_password et author_bank sont exclus du calcul de complétion
    };

    /**
     * Vérifier si un champ est rempli
     */
    function isFieldFilled(field) {
        const $field = $(field);
        const type = $field.attr('type');
        const tagName = $field.prop('tagName').toLowerCase();

        // Checkbox ou radio
        if (type === 'checkbox' || type === 'radio') {
            const name = $field.attr('name');
            return $('input[name="' + name + '"]:checked').length > 0;
        }

        // Select
        if (tagName === 'select') {
            return $field.val() !== '' && $field.val() !== null;
        }

        // Input normal ou textarea
        return $field.val().trim() !== '';
    }

    /**
     * Valider un onglet spécifique
     */
    function validateTab(tabId) {
        const requirements = tabRequirements[tabId];
        if (!requirements) return true;

        const $tabContent = $('#' + tabId);
        if ($tabContent.length === 0) return true;

        let requiredFilled = 0;
        let requiredTotal = 0;
        let neededFilled = 0;
        let neededTotal = 0;

        // Vérifier les champs obligatoires (*)
        requirements.required.forEach(function(fieldName) {
            requiredTotal++;
            const $field = $tabContent.find('[name="' + fieldName + '"]');
            if ($field.length > 0 && isFieldFilled($field)) {
                requiredFilled++;
                $field.closest('.vendor_field').removeClass('field-error').addClass('field-valid');
            } else {
                $field.closest('.vendor_field').removeClass('field-valid').addClass('field-error');
            }
        });

        // Vérifier les champs nécessaires (⭐)
        requirements.needed.forEach(function(fieldName) {
            neededTotal++;
            const $field = $tabContent.find('[name="' + fieldName + '"]');
            if ($field.length > 0 && isFieldFilled($field)) {
                neededFilled++;
                $field.closest('.vendor_field').removeClass('field-error').addClass('field-valid');
            }
        });

        // Un onglet est validé si TOUS les champs obligatoires sont remplis
        // ET au moins 50% des champs nécessaires
        const isValid = (requiredFilled === requiredTotal) &&
                       (neededTotal === 0 || neededFilled >= Math.ceil(neededTotal * 0.5));

        // Mettre à jour l'indicateur visuel
        const $tabNav = $('.profile_tab_item[data-tab="' + tabId + '"]');
        if (isValid) {
            $tabNav.addClass('validated').removeClass('has-errors');
        } else if (requiredFilled < requiredTotal) {
            $tabNav.removeClass('validated').addClass('has-errors');
        } else {
            $tabNav.removeClass('validated has-errors');
        }

        return isValid;
    }

    /**
     * Valider tous les onglets
     */
    window.validateAllTabs = function() {
        let totalTabs = 0;
        let validatedTabs = 0;

        Object.keys(tabRequirements).forEach(function(tabId) {
            const requirements = tabRequirements[tabId];

            // Vérifier si l'onglet existe ET doit compter dans le score
            if ($('#' + tabId).length > 0 && requirements.countInScore !== false) {
                totalTabs++;
                if (validateTab(tabId)) {
                    validatedTabs++;
                }
            }
        });

        // Calculer le score de complétion (seulement sur les onglets qui comptent)
        const completionScore = totalTabs > 0 ? Math.round((validatedTabs / totalTabs) * 100) : 0;
        updateCompletionScore(completionScore);

        return completionScore;
    };

    /**
     * Mettre à jour le score de complétion
     */
    function updateCompletionScore(score) {
        const $scoreContainer = $('.profile-completion-score');
        if ($scoreContainer.length === 0) return;

        $scoreContainer.find('.score-value').text(score + '%');
        $scoreContainer.find('.score-fill').css('width', score + '%');

        let message = '';
        if (score === 100) {
            message = 'Profil complet ! Vous pouvez maintenant publier des activités.';
        } else if (score >= 80) {
            message = 'Encore quelques informations et votre profil sera complet.';
        } else if (score >= 50) {
            message = 'Votre profil progresse bien. Continuez !';
        } else {
            message = 'Complétez votre profil pour publier des activités.';
        }

        $scoreContainer.find('.score-message').text(message);
    }

    /**
     * Validation en temps réel des champs
     */
    function setupRealtimeValidation() {
        // Écouter les changements sur tous les champs
        $(document).on('input change blur', '.vendor_field input, .vendor_field select, .vendor_field textarea', function() {
            const $field = $(this);
            const $container = $field.closest('.vendor_field');
            const $tabContent = $field.closest('.tab-contents');

            if ($tabContent.length > 0) {
                const tabId = $tabContent.attr('id');

                // Valider le champ individuel
                if ($field.attr('required') || $field.hasClass('required')) {
                    if (isFieldFilled($field)) {
                        $container.removeClass('field-error').addClass('field-valid');
                    } else {
                        $container.removeClass('field-valid').addClass('field-error');
                    }
                }

                // Re-valider l'onglet complet
                setTimeout(function() {
                    validateTab(tabId);
                    validateAllTabs();
                }, 100);
            }
        });

        // Validation spéciale pour les checkboxes
        $(document).on('change', 'input[type="checkbox"], input[type="radio"]', function() {
            const $container = $(this).closest('.vendor_field');
            const name = $(this).attr('name');
            const $allCheckboxes = $('input[name="' + name + '"]');

            if ($allCheckboxes.filter(':checked').length > 0) {
                $container.removeClass('field-error').addClass('field-valid');
            } else if ($container.find('.required').length > 0) {
                $container.removeClass('field-valid').addClass('field-error');
            }
        });
    }

    /**
     * Validation du mot de passe
     */
    function setupPasswordValidation() {
        const $newPassword = $('#new_password');
        const $confirmPassword = $('#confirm_password');
        const $submitBtn = $('.wrap_confirm_password').siblings('input[type="submit"]');

        // Règles de validation du mot de passe
        function validatePasswordStrength(password) {
            const rules = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            return rules;
        }

        // Afficher les règles de validation
        if ($newPassword.length > 0) {
            const $rulesContainer = $('<div class="password-rules"></div>');
            $rulesContainer.html(`
                <p style="margin: 8px 0 4px; font-size: 13px; color: #4a5568; font-weight: 500;">
                    Votre mot de passe doit contenir :
                </p>
                <ul style="list-style: none; margin: 0; padding: 0; font-size: 12px;">
                    <li class="rule-length" style="color: #a0aec0; margin: 4px 0;">
                        <span class="icon">○</span> 8 caractères minimum
                    </li>
                    <li class="rule-uppercase" style="color: #a0aec0; margin: 4px 0;">
                        <span class="icon">○</span> Au moins 1 lettre majuscule
                    </li>
                    <li class="rule-lowercase" style="color: #a0aec0; margin: 4px 0;">
                        <span class="icon">○</span> Au moins 1 lettre minuscule
                    </li>
                    <li class="rule-number" style="color: #a0aec0; margin: 4px 0;">
                        <span class="icon">○</span> Au moins 1 chiffre
                    </li>
                    <li class="rule-special" style="color: #a0aec0; margin: 4px 0;">
                        <span class="icon">○</span> Au moins 1 caractère spécial
                    </li>
                </ul>
            `);
            $newPassword.closest('.vendor_field').after($rulesContainer);

            // Validation en temps réel
            $newPassword.on('input', function() {
                const password = $(this).val();
                const rules = validatePasswordStrength(password);

                Object.keys(rules).forEach(function(rule) {
                    const $rule = $rulesContainer.find('.rule-' + rule);
                    if (rules[rule]) {
                        $rule.css('color', '#48bb78');
                        $rule.find('.icon').text('✓');
                    } else {
                        $rule.css('color', '#a0aec0');
                        $rule.find('.icon').text('○');
                    }
                });

                checkPasswordMatch();
            });
        }

        // Vérifier la correspondance des mots de passe
        function checkPasswordMatch() {
            const newPass = $newPassword.val();
            const confirmPass = $confirmPassword.val();

            if (confirmPass.length > 0) {
                if (newPass === confirmPass) {
                    $confirmPassword.closest('.vendor_field')
                        .removeClass('field-error')
                        .addClass('field-valid');
                    $('.check').html('<span style="color: #48bb78;">✓ Les mots de passe correspondent</span>');
                } else {
                    $confirmPassword.closest('.vendor_field')
                        .removeClass('field-valid')
                        .addClass('field-error');
                    $('.check').html('<span style="color: #f56565;">✗ Les mots de passe ne correspondent pas</span>');
                }
            } else {
                $('.check').html('');
            }
        }

        $confirmPassword.on('input', checkPasswordMatch);

        // Désactiver le bouton si les champs ne sont pas valides
        $('#el_save_password').on('submit', function(e) {
            const oldPass = $('#old_password').val();
            const newPass = $newPassword.val();
            const confirmPass = $confirmPassword.val();

            if (!oldPass || !newPass || !confirmPass) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs.');
                return false;
            }

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }

            const rules = validatePasswordStrength(newPass);
            const allValid = Object.values(rules).every(v => v === true);

            if (!allValid) {
                e.preventDefault();
                alert('Le mot de passe ne respecte pas les règles de sécurité.');
                return false;
            }
        });
    }

    /**
     * Initialisation
     */
    $(document).ready(function() {

        // Ajouter le score de complétion dans la sidebar
        if ($('.profile_user_header').length > 0) {
            const $scoreHtml = $(`
                <div class="profile-completion-score">
                    <div class="score-label">Profil complété</div>
                    <div class="score-value">0%</div>
                    <div class="score-bar">
                        <div class="score-fill" style="width: 0%;"></div>
                    </div>
                    <div class="score-message">Commencez à remplir votre profil</div>
                </div>
            `);
            $('.profile_user_header').after($scoreHtml);
        }

        // Ajouter la légende des symboles
        if ($('#author_profile').length > 0) {
            const $legendHtml = $(`
                <div class="profile-legend">
                    <h4>Légende des champs</h4>
                    <ul>
                        <li>
                            <span class="symbol required">*</span>
                            <span>Obligatoire pour créer le profil organisateur</span>
                        </li>
                        <li>
                            <span class="symbol needed">⭐</span>
                            <span>Nécessaire pour pouvoir publier une activité</span>
                        </li>
                        <li>
                            <span class="symbol visible">👁</span>
                            <span>Visible en ligne</span>
                        </li>
                    </ul>
                </div>
            `);
            $('#author_profile form').prepend($legendHtml);
        }

        // Configuration de la validation en temps réel
        setupRealtimeValidation();

        // Configuration de la validation du mot de passe
        setupPasswordValidation();

        // Validation initiale
        setTimeout(function() {
            validateAllTabs();
        }, 500);

        // Re-valider au changement d'onglet
        $('.profile_tab_item').on('click', function() {
            setTimeout(function() {
                validateAllTabs();
            }, 100);
        });

        // Gestion du bouton sticky "Enregistrer" - V1 Le Hiboo
        $('#trigger_save_profile').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);

            // Ne rien faire si déjà en cours de chargement
            if ($btn.hasClass('is-loading')) {
                return false;
            }

            // Ajouter l'état de chargement
            $btn.addClass('is-loading');
            $btn.prop('disabled', true);

            // Trouver l'onglet actif
            const activeTab = $('.profile_tab_item.active').data('tab');

            // Cliquer sur le bouton submit de l'onglet actif
            if (activeTab === 'author_profile') {
                $('input[name="el_update_profile"]').click();
            } else if (activeTab === 'author_organisation') {
                $('input[name="el_update_organisation"]').click();
            } else if (activeTab === 'author_presentation') {
                $('input[name="el_update_presentation"]').click();
            } else if (activeTab === 'author_password') {
                $('input[name="el_update_password"]').click();
            } else if (activeTab === 'author_bank') {
                $('input[name="el_update_payout_method"]').click();
            }

            // Retirer l'état de chargement après 2 secondes (sécurité)
            // Normalement, l'AJAX devrait le retirer avant
            setTimeout(function() {
                $btn.removeClass('is-loading');
                $btn.prop('disabled', false);
            }, 3000);
        });

        // Écouter les événements AJAX pour retirer le loader
        $(document).on('ajaxComplete', function(_event, _xhr, settings) {
            // Vérifier si c'est une requête de mise à jour du profil
            if (settings.data && (
                settings.data.includes('el_update_profile') ||
                settings.data.includes('el_update_organisation') ||
                settings.data.includes('el_update_presentation') ||
                settings.data.includes('el_update_password') ||
                settings.data.includes('el_update_payout_method')
            )) {
                // Retirer l'état de chargement du bouton sticky
                $('#trigger_save_profile').removeClass('is-loading');
                $('#trigger_save_profile').prop('disabled', false);
            }
        });

        // Animation scroll pour la sticky bar
        let lastScroll = 0;
        $(window).on('scroll', function() {
            const currentScroll = $(this).scrollTop();

            if (currentScroll > 100) {
                $('.profile_sticky_bar').addClass('is-scrolled');
            } else {
                $('.profile_sticky_bar').removeClass('is-scrolled');
            }

            lastScroll = currentScroll;
        });

        // Initialiser TinyMCE pour le champ description
        function initWYSIWYGEditor() {
            if (typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' && $('#description').length > 0) {
                // Vérifier si l'onglet Présentation est visible
                const $presentationTab = $('#author_presentation');

                if ($presentationTab.is(':visible')) {
                    // Retirer l'éditeur existant s'il y en a un
                    wp.editor.remove('description');

                    // Initialiser l'éditeur
                    wp.editor.initialize('description', {
                        tinymce: {
                            toolbar1: 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,link,unlink,undo,redo',
                            toolbar2: '',
                            paste_as_text: true,
                            wpautop: true,
                            plugins: 'lists,paste,wordpress,wplink',
                            height: 300,
                        },
                        quicktags: {
                            buttons: 'strong,em,ul,ol,li,link'
                        }
                    });
                }
            }
        }

        // Initialiser au chargement si l'onglet Présentation est actif
        setTimeout(function() {
            if ($('.profile_tab_item[data-tab="author_presentation"]').hasClass('active')) {
                initWYSIWYGEditor();
            }
        }, 500);

        // Réinitialiser quand on clique sur l'onglet Présentation
        $('.profile_tab_item[data-tab="author_presentation"]').on('click', function() {
            setTimeout(function() {
                initWYSIWYGEditor();
            }, 300);
        });

    });

})(jQuery);
