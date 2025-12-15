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

        // Retirer l'image de couverture (ancien style)
        $('body').on('click', '.remove_cover_image', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $input = $button.siblings('.org_cover_image_id');
            var $preview = $button.siblings('.preview_cover_image');

            $input.val('');
            $preview.remove();
            $button.remove();
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
        // DROPZONE COVER IMAGE (NOUVEAU STYLE)
        // ===================================

        var $coverDropzone = $('#cover_dropzone');
        var $coverFileInput = $coverDropzone.find('.cover_file_input');
        var $coverPlaceholder = $coverDropzone.find('.dropzone_placeholder');
        var $coverPreview = $coverDropzone.find('.dropzone_preview');
        var $coverImageInput = $coverDropzone.siblings('.org_cover_image_id');

        // Click sur la dropzone
        $coverDropzone.on('click', function(e) {
            if (!$(e.target).closest('.preview_actions').length) {
                $coverFileInput.trigger('click');
            }
        });

        // Drag & Drop
        $coverDropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $coverDropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $coverDropzone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $coverFileInput[0].files = files;
                $coverFileInput.trigger('change');
            }
        });

        // File input change
        $coverFileInput.on('change', function() {
            var file = this.files[0];
            if (file) {
                uploadCoverImage(file);
            }
        });

        // Upload cover image via AJAX
        function uploadCoverImage(file) {
            var formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'el_upload_media');
            formData.append('nonce', el_ajax_object.nonce);

            $coverDropzone.addClass('uploading');

            $.ajax({
                url: el_ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $coverImageInput.val(response.data.id);
                        $coverPreview.find('img').remove();
                        $coverPreview.prepend('<img src="' + response.data.url + '" alt="Cover">');
                        $coverPlaceholder.addClass('hidden');
                        $coverPreview.removeClass('hidden');
                        $coverDropzone.addClass('has_image');
                    } else {
                        alert(response.data.message || 'Erreur lors de l\'upload');
                    }
                },
                error: function() {
                    alert('Erreur de connexion');
                },
                complete: function() {
                    $coverDropzone.removeClass('uploading');
                }
            });
        }

        // Changer cover image
        $coverDropzone.on('click', '.btn_change_cover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $coverFileInput.trigger('click');
        });

        // Supprimer cover image
        $coverDropzone.on('click', '.btn_remove_cover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $coverImageInput.val('');
            $coverPreview.find('img').remove();
            $coverPlaceholder.removeClass('hidden');
            $coverPreview.addClass('hidden');
            $coverDropzone.removeClass('has_image');
        });

        // ===================================
        // LOGO UPLOAD (PRÉSENTATION)
        // ===================================

        var logoFrame;
        $('.btn_change_logo').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $preview = $button.closest('.logo_preview_wrapper').find('.logo_preview_img');
            var $input = $button.closest('.vendor_field').find('.author_id_image');

            if (logoFrame) {
                logoFrame.open();
                return;
            }

            logoFrame = wp.media({
                title: 'Sélectionner le logo',
                button: { text: 'Utiliser ce logo' },
                multiple: false,
                library: { type: 'image' }
            });

            logoFrame.on('select', function() {
                var attachment = logoFrame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.attr('src', attachment.url);
            });

            logoFrame.open();
        });

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
