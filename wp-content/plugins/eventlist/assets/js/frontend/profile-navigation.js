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

            // Extraire l'ancre du href du lien enfant
            var $link = $(this).find('a');
            var href = $link.attr('href') || '';
            var targetTab = href.replace('#', '');

            // Si pas de cible valide, ignorer
            if (!targetTab) {
                return;
            }

            // Retirer la classe active de tous les onglets
            $('.profile_tab_item').removeClass('active');

            // Ajouter la classe active à l'onglet cliqué
            $(this).addClass('active');

            // Scroll vers la section correspondante
            var $targetSection = $('#' + targetTab);
            if ($targetSection.length) {
                var offset = $targetSection.offset().top - 100; // 100px de marge pour la sticky bar
                $('html, body').animate({
                    scrollTop: offset
                }, 400);
            }

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
                // Chercher le lien qui pointe vers cette ancre
                var $targetTabItem = $('.profile_tab_item').filter(function() {
                    return $(this).find('a').attr('href') === hash;
                });

                if ($targetTabItem.length) {
                    // Activer l'onglet correspondant
                    $('.profile_tab_item').removeClass('active');
                    $targetTabItem.addClass('active');

                    // Scroll vers la section après un court délai (pour le chargement de la page)
                    setTimeout(function() {
                        var $targetSection = $('#' + targetTab);
                        if ($targetSection.length) {
                            var offset = $targetSection.offset().top - 100;
                            $('html, body').animate({
                                scrollTop: offset
                            }, 400);
                        }
                    }, 300);
                    return;
                }
            }

            // Par défaut, activer le premier onglet
            $('.profile_tab_item:first').addClass('active');
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

        // ===================================
        // TOGGLE SERVICES ET ACCESSIBILITÉ
        // ===================================

        $('#services_enabled').on('change', function() {
            var $content = $('#services_content');
            if ($(this).is(':checked')) {
                $content.slideDown(300);
            } else {
                $content.slideUp(300);
            }
        });

        // ===================================
        // DROPZONE COVER IMAGE & LOGO
        // Note: Géré par vendor-profile-form.js avec EL_MediaPicker
        // ===================================

        // ===================================
        // SELECT2 MULTI-SELECT INITIALIZATION
        // ===================================

        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-multi').select2({
                placeholder: 'Sélectionnez des options',
                allowClear: true,
                width: '100%'
            });
        }

        // ===================================
        // CARTE LEAFLET (LOCALISATION)
        // ===================================

        var $mapContainer = $('#profile_map');
        if ($mapContainer.length && typeof L !== 'undefined') {
            var lat = parseFloat($mapContainer.data('lat')) || 48.8566;
            var lng = parseFloat($mapContainer.data('lng')) || 2.3522;

            var map = L.map('profile_map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker = L.marker([lat, lng]).addTo(map);

            // Mettre à jour la carte quand l'adresse change
            window.updateProfileMap = function(newLat, newLng) {
                map.setView([newLat, newLng], 15);
                marker.setLatLng([newLat, newLng]);
                $('#org_latitude').val(newLat);
                $('#org_longitude').val(newLng);
                $('#org_gps_display').val(newLat + ', ' + newLng);
            };
        }

        // ===================================
        // FORMULAIRE LOCALISATION
        // ===================================

        $('#el_save_localisation').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('input[type="submit"]');
            var $loader = $form.find('.ova__loader');
            var formData = new FormData($form[0]);

            formData.append('action', 'el_update_localisation');

            $button.prop('disabled', true);
            $loader.show();

            $.ajax({
                url: el_ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast('Localisation enregistrée avec succès', 'success');
                    } else {
                        showToast(response.data.message || 'Erreur lors de la sauvegarde', 'error');
                    }
                },
                error: function() {
                    showToast('Erreur de connexion', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $loader.hide();
                }
            });
        });

        // ===================================
        // TOAST NOTIFICATION
        // ===================================

        function showToast(message, type) {
            type = type || 'success';
            var $toast = $('<div class="el-toast el-toast-' + type + '">' + message + '</div>');

            $('body').append($toast);

            setTimeout(function() {
                $toast.addClass('show');
            }, 100);

            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 3000);
        }

    });

})(jQuery);
