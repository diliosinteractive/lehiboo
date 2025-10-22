/**
 * EventList - Profile Navigation (Vertical Tabs)
 * Gestion de la navigation entre les onglets du profil partenaire
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Navigation entre les onglets
        $('.profile_tab_item').on('click', function(e) {
            e.preventDefault();

            var targetTab = $(this).data('tab');

            // Retirer la classe active de tous les onglets
            $('.profile_tab_item').removeClass('active');

            // Ajouter la classe active à l'onglet cliqué
            $(this).addClass('active');

            // Masquer tous les contenus
            $('.tab-contents').removeClass('active').hide();

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
                var $targetTabItem = $('.profile_tab_item[data-tab="' + targetTab + '"]');

                if ($targetTabItem.length) {
                    $targetTabItem.trigger('click');
                    return;
                }
            }

            // Par défaut, afficher le premier onglet
            $('.profile_tab_item:first').addClass('active');
            $('.tab-contents:first').addClass('active').show();
        }

        initTabFromHash();

        // Gérer le bouton retour du navigateur
        $(window).on('hashchange', function() {
            initTabFromHash();
        });

        // Mobile: Responsive behavior
        if ($(window).width() <= 991) {
            // Sur mobile, on pourrait transformer la navigation en accordéon
            // Pour l'instant, on garde le comportement par défaut
        }

        // ===================================
        // AJAX FORMS - Organisation et Présentation
        // ===================================

        // Soumission du formulaire "Mon Organisation"
        $('#el_save_organisation').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('input[type="submit"]');
            var $loader = $form.find('.ova__loader');
            var formData = $form.serializeArray();
            var dataObj = {};

            // Convertir en objet
            $.each(formData, function(i, field) {
                if (field.name.indexOf('[]') > -1) {
                    // Gérer les champs multiples (checkboxes)
                    var fieldName = field.name.replace('[]', '');
                    if (!dataObj[fieldName]) {
                        dataObj[fieldName] = [];
                    }
                    dataObj[fieldName].push(field.value);
                } else {
                    dataObj[field.name] = field.value;
                }
            });

            // Afficher le loader
            $button.prop('disabled', true);
            $loader.show();

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_update_organisation',
                    data: dataObj
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    $loader.hide();

                    if (response.success) {
                        // Afficher message de succès
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.success(response.data.message || 'Enregistré avec succès !');
                        } else {
                            alert(response.data.message || 'Enregistré avec succès !');
                        }
                    } else {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.error(response.data.message || 'Une erreur est survenue.');
                        } else {
                            alert(response.data.message || 'Une erreur est survenue.');
                        }
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    $loader.hide();
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error('Erreur de connexion. Veuillez réessayer.');
                    } else {
                        alert('Erreur de connexion. Veuillez réessayer.');
                    }
                }
            });
        });

        // Soumission du formulaire "Présentation"
        $('#el_save_presentation').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('input[type="submit"]');
            var $loader = $form.find('.ova__loader');
            var formData = $form.serializeArray();
            var dataObj = {};

            // Convertir en objet
            $.each(formData, function(i, field) {
                dataObj[field.name] = field.value;
            });

            // Afficher le loader
            $button.prop('disabled', true);
            $loader.show();

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_update_presentation',
                    data: dataObj
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    $loader.hide();

                    if (response.success) {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.success(response.data.message || 'Enregistré avec succès !');
                        } else {
                            alert(response.data.message || 'Enregistré avec succès !');
                        }
                    } else {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.error(response.data.message || 'Une erreur est survenue.');
                        } else {
                            alert(response.data.message || 'Une erreur est survenue.');
                        }
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    $loader.hide();
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error('Erreur de connexion. Veuillez réessayer.');
                    } else {
                        alert('Erreur de connexion. Veuillez réessayer.');
                    }
                }
            });
        });

        // Soumission du formulaire "Localisation" - V1 Le Hiboo
        $('#el_save_localisation').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('input[type="submit"]');
            var $loader = $form.find('.ova__loader');
            var formData = $form.serializeArray();
            var dataObj = {};

            // Convertir en objet
            $.each(formData, function(i, field) {
                dataObj[field.name] = field.value;
            });

            // Afficher le loader
            $button.prop('disabled', true);
            $loader.show();

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_update_localisation',
                    data: dataObj
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    $loader.hide();

                    if (response.success) {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.success(response.data.message || 'Enregistré avec succès !');
                        } else {
                            alert(response.data.message || 'Enregistré avec succès !');
                        }
                    } else {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.error(response.data.message || 'Une erreur est survenue.');
                        } else {
                            alert(response.data.message || 'Une erreur est survenue.');
                        }
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    $loader.hide();
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error('Erreur de connexion. Veuillez réessayer.');
                    } else {
                        alert('Erreur de connexion. Veuillez réessayer.');
                    }
                }
            });
        });

        // Gestion de l'upload de l'image de couverture
        var coverImageFrame;

        $('body').on('click', '.add_cover_image', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $input = $button.siblings('.org_cover_image_id');
            var $preview = $button.siblings('.preview_cover_image');
            var $removeBtn = $button.siblings('.remove_cover_image');

            // Si le media frame existe déjà, le réutiliser
            if (coverImageFrame) {
                coverImageFrame.open();
                return;
            }

            // Créer le media frame
            coverImageFrame = wp.media({
                title: $button.data('uploader-title') || 'Sélectionner une image',
                button: {
                    text: $button.data('uploader-button-text') || 'Utiliser cette image'
                },
                multiple: false
            });

            // Quand une image est sélectionnée
            coverImageFrame.on('select', function() {
                var attachment = coverImageFrame.state().get('selection').first().toJSON();

                $input.val(attachment.id);

                if ($preview.length) {
                    $preview.attr('src', attachment.url).show();
                    $removeBtn.show();
                } else {
                    $button.before('<img class="preview_cover_image" src="' + attachment.url + '" style="max-width: 100%; height: auto; margin-bottom: 10px;">');
                    $button.before('<button type="button" class="button remove_cover_image">Retirer l\'image</button>');
                }
            });

            coverImageFrame.open();
        });

        // Retirer l'image de couverture
        $('body').on('click', '.remove_cover_image', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $input = $button.siblings('.org_cover_image_id');
            var $preview = $button.siblings('.preview_cover_image');

            $input.val('');
            $preview.remove();
            $button.remove();
        });

    });

})(jQuery);
