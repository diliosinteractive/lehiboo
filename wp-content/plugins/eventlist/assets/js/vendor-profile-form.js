/**
 * Vendor Profile Form JS
 * Handles: Scrollspy navigation, completion gauge, global save
 */

jQuery(document).ready(function ($) {

    /* ==========================================================================
       1. Navigation & ScrollSpy
       ========================================================================== */

    // Smooth scrolling for anchor links
    $('.el-anchor-nav a').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var offset = 140; // Adjust based on sticky header height

        if ($(target).length) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - offset
            }, 500);

            // Update active state manually
            $('.el-anchor-nav li').removeClass('active');
            $(this).parent('li').addClass('active');
        }
    });

    // ScrollSpy behavior
    $(window).on('scroll', function () {
        var scrollPos = $(document).scrollTop();
        var offset = 160; // Trigger point

        $('.el-anchor-nav a').each(function () {
            var currLink = $(this);
            var refElement = $(currLink.attr('href'));

            if (refElement.length && refElement.position().top - offset <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
                $('.el-anchor-nav li').removeClass('active');
                currLink.parent('li').addClass('active');
            }
        });
    });

    /* ==========================================================================
       2. Completion Gauge Logic - V1 Le Hiboo
       Total: 20 points = 100%
       - Mes informations professionnelles: 5 points (25%)
       - Mon organisation: 8 points (40%)
       - Localisation: 2 points (10%)
       - Présentation: 5 points (25%)
       ========================================================================== */

    // Configuration des champs par section avec leurs points
    var completionConfig = {
        // Section 1: Mes informations professionnelles (5 points = 25%)
        section_profile: {
            fields: [
                { name: 'first_name', selector: 'input[name="first_name"]', points: 1 },
                { name: 'last_name', selector: 'input[name="last_name"]', points: 1 },
                { name: 'user_email', selector: 'input[name="user_email"]', points: 1 },
                { name: 'user_phone', selector: 'input[name="user_phone"]', points: 1 },
                { name: 'user_job', selector: 'input[name="user_job"]', points: 1 }
            ],
            totalPoints: 5
        },
        // Section 2: Mon organisation (8 points = 40%)
        section_organisation: {
            fields: [
                { name: 'org_name', selector: 'input[name="org_name"]', points: 1 },
                { name: 'org_display_name', selector: 'input[name="org_display_name"]', points: 1 },
                { name: 'org_type_structure', selector: 'input[name="org_type_structure[]"]', type: 'checkbox', points: 1 },
                { name: 'org_role', selector: 'input[name="org_role[]"]', type: 'checkbox', points: 1 },
                { name: 'org_forme_juridique', selector: 'select[name="org_forme_juridique"]', points: 1 },
                { name: 'org_siren', selector: 'input[name="org_siren"]', points: 1 },
                { name: 'org_date_creation', selector: 'input[name="org_date_creation"]', points: 1 },
                { name: 'org_nombre_effectifs', selector: 'select[name="org_nombre_effectifs"]', points: 1 }
            ],
            totalPoints: 8
        },
        // Section 3: Localisation (2 points = 10%)
        section_localisation: {
            fields: [
                { name: 'user_address', selector: 'select[name="user_address"]', points: 1 },
                { name: 'services_enabled', selector: 'input[name="services_enabled"]', type: 'checkbox', points: 1 }
            ],
            totalPoints: 2
        },
        // Section 4: Présentation (5 points = 25%)
        section_presentation: {
            fields: [
                { name: 'org_cover_image', selector: 'input[name="org_cover_image"]', points: 1 },
                { name: 'author_id_image', selector: 'input[name="author_id_image"]', points: 1 },
                { name: 'description', selector: '#description', type: 'textarea_min500', points: 1 },
                { name: 'org_email_contact', selector: 'input[name="org_email_contact"]', points: 1 },
                { name: 'org_event_type', selector: 'select[name="org_event_type"]', points: 1 }
            ],
            totalPoints: 5
        }
    };

    // Helper: Vérifier si un champ a une valeur
    function isFieldFilled(field) {
        var $el = $(field.selector);
        if ($el.length === 0) return false;

        // Checkbox - au moins une cochée
        if (field.type === 'checkbox') {
            return $el.filter(':checked').length > 0;
        }

        // Textarea avec minimum 500 caractères
        if (field.type === 'textarea_min500') {
            var val = '';
            // Récupérer le contenu de TinyMCE si disponible
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('description')) {
                val = tinyMCE.get('description').getContent({ format: 'text' });
            } else {
                val = $el.val() || '';
            }
            return val.trim().length >= 500;
        }

        // Input/Select standard
        var val = $el.val();
        if (Array.isArray(val)) {
            return val.length > 0 && val[0] !== '';
        }
        return val !== null && val !== undefined && $.trim(val) !== '';
    }

    // Calculer les points d'une section
    function calculateSectionPoints(sectionKey) {
        var config = completionConfig[sectionKey];
        if (!config) return { filled: 0, total: 0, complete: false };

        var filledPoints = 0;
        config.fields.forEach(function(field) {
            if (isFieldFilled(field)) {
                filledPoints += field.points;
            }
        });

        return {
            filled: filledPoints,
            total: config.totalPoints,
            complete: filledPoints >= config.totalPoints
        };
    }

    // Mettre à jour la jauge de complétion
    function updateCompletionGauge() {
        var totalPoints = 20; // Total fixe
        var filledPoints = 0;
        var sectionResults = {};

        // Calculer les points pour chaque section
        Object.keys(completionConfig).forEach(function(sectionKey) {
            var result = calculateSectionPoints(sectionKey);
            sectionResults[sectionKey] = result;
            filledPoints += result.filled;
        });

        // Calculer le pourcentage (arrondi à 5% près pour plus de clarté)
        var percent = Math.round((filledPoints / totalPoints) * 100);
        if (percent > 100) percent = 100;

        // Mettre à jour l'UI (Sidebar Widget)
        $('#el-completion-fill-sidebar').css('width', percent + '%');
        $('#el-completion-percent-sidebar').text(percent + '%');

        // Mettre à jour les icônes de navigation
        updateNavigationStatus(sectionResults);

        return { percent: percent, points: filledPoints, sections: sectionResults };
    }

    // Mettre à jour les icônes de statut dans la navigation
    function updateNavigationStatus(sectionResults) {
        Object.keys(sectionResults).forEach(function(sectionKey) {
            var result = sectionResults[sectionKey];
            var $navItem = $('.el-anchor-nav a[href="#' + sectionKey + '"]').parent('li');
            var $statusIcon = $navItem.find('.status-icon');

            if (result.complete) {
                $navItem.addClass('section-complete');
                $statusIcon.addClass('completed');
            } else {
                $navItem.removeClass('section-complete');
                $statusIcon.removeClass('completed');
            }
        });
    }

    // Trigger update on input change
    $('#el-vendor-profile-form').on('change input', 'input, select, textarea', function () {
        updateCompletionGauge();
    });

    // Initial check
    setTimeout(updateCompletionGauge, 500);

    /* ==========================================================================
       3. AJAX Save Logic - Global Save
       ========================================================================== */

    // Handle Save Button Click
    $('#el-btn-save-profile').on('click', function (e) {
        e.preventDefault();
        saveProfile();
    });

    function saveProfile() {
        var $form = $('#el-vendor-profile-form');
        var $saveBtn = $('#el-btn-save-profile');

        // Disable button during save
        $saveBtn.prop('disabled', true).addClass('loading').html('<span>Enregistrement...</span>');

        // Synchronize TinyMCE with textarea if present
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        // Serialize form data
        var formData = $form.serialize();

        // Add action
        formData += '&action=el_save_profile_global';

        // Send AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $saveBtn.prop('disabled', false).removeClass('loading').html('<span>Enregistrer</span>');

                if (response.success) {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success(response.data.message || 'Profil sauvegardé avec succès !');
                    } else {
                        alert(response.data.message || 'Profil sauvegardé avec succès !');
                    }

                    // Update avatar if changed
                    if (response.data.avatar_url) {
                        $('.profile_avatar img').attr('src', response.data.avatar_url);
                    }

                    // Update display name if changed
                    if (response.data.display_name) {
                        $('.profile_user_info h3').text(response.data.display_name);
                    }

                    // Redirect to login if password was changed
                    if (response.data.password_changed) {
                        setTimeout(function() {
                            window.location.href = '/connexion/?password=changed';
                        }, 2000);
                    }

                    // Show OTP modal if email change requires verification
                    if (response.data.email_otp_required) {
                        showEmailOtpModal();
                    }

                } else {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(response.data.message || 'Une erreur est survenue lors de la sauvegarde.');
                    } else {
                        alert(response.data.message || 'Une erreur est survenue lors de la sauvegarde.');
                    }
                }
            },
            error: function (xhr, status, error) {
                $saveBtn.prop('disabled', false).removeClass('loading').html('<span>Enregistrer</span>');
                console.error('Save error:', error);
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error('Erreur lors de la sauvegarde. Veuillez réessayer.');
                } else {
                    alert('Erreur lors de la sauvegarde. Veuillez réessayer.');
                }
            }
        });
    }

    /* ==========================================================================
       4. Password Toggle (géré plus bas dans la section Password Change)
       ========================================================================== */

    /* ==========================================================================
       5. Image Selection via Media Picker (Cover & Logo)
       ========================================================================== */

    /**
     * Ouvrir le Media Picker pour sélectionner une image
     */
    function openMediaPicker(type) {
        if (typeof window.EL_MediaPicker === 'undefined') {
            console.warn('EL_MediaPicker not available');
            alert('Le gestionnaire de médias n\'est pas disponible. Veuillez recharger la page.');
            return;
        }

        var title = type === 'cover' ? 'Choisir une image de couverture' : 'Choisir un logo';

        window.EL_MediaPicker.open({
            mode: 'single',
            title: title,
            callback: function(images) {
                if (images && images.length > 0) {
                    var image = images[0];
                    if (type === 'cover') {
                        updateCoverImage(image.id, image.url);
                    } else {
                        updateLogoImage(image.id, image.url);
                    }
                }
            }
        });
    }

    // Cover Image - Click sur la zone ou le placeholder
    $('#cover_dropzone').on('click', function (e) {
        if (!$(e.target).closest('.preview_actions').length) {
            openMediaPicker('cover');
        }
    });

    // Cover Image - Bouton changer
    $(document).on('click', '.btn_pick_cover_image, .btn_change_cover', function (e) {
        e.stopPropagation();
        openMediaPicker('cover');
    });

    // Cover Image - Bouton supprimer
    $('.btn_remove_cover').on('click', function (e) {
        e.stopPropagation();
        removeCoverImage();
    });

    // Logo - Click sur la zone ou le placeholder
    $('#logo_dropzone').on('click', function (e) {
        if (!$(e.target).closest('.preview_actions').length) {
            openMediaPicker('logo');
        }
    });

    // Logo - Bouton changer
    $(document).on('click', '.btn_pick_logo_image, .btn_change_logo', function (e) {
        e.stopPropagation();
        openMediaPicker('logo');
    });

    // Logo - Bouton supprimer
    $('.btn_remove_logo').on('click', function (e) {
        e.stopPropagation();
        removeLogoImage();
    });

    /**
     * Mettre à jour l'image de couverture
     */
    function updateCoverImage(id, url) {
        var $dropzone = $('#cover_dropzone');
        $dropzone.addClass('has_image');
        $dropzone.find('.dropzone_placeholder').addClass('hidden');
        $dropzone.find('.dropzone_preview').removeClass('hidden').find('img').attr('src', url);
        $dropzone.closest('.vendor_field').find('.org_cover_image_id').val(id);
        updateCompletionGauge();
    }

    /**
     * Supprimer l'image de couverture
     */
    function removeCoverImage() {
        var $dropzone = $('#cover_dropzone');
        $dropzone.removeClass('has_image');
        $dropzone.find('.dropzone_placeholder').removeClass('hidden');
        $dropzone.find('.dropzone_preview').addClass('hidden').find('img').attr('src', '');
        $dropzone.closest('.vendor_field').find('.org_cover_image_id').val('');
        updateCompletionGauge();
    }

    /**
     * Mettre à jour le logo
     */
    function updateLogoImage(id, url) {
        var $dropzone = $('#logo_dropzone');
        $dropzone.addClass('has_image');
        $dropzone.find('.dropzone_placeholder').addClass('hidden');
        $dropzone.find('.dropzone_preview').removeClass('hidden').find('img').attr('src', url);
        // Update the correct hidden input for logo
        $dropzone.closest('.vendor_field').find('.author_id_image').val(id);
        updateCompletionGauge();
    }

    /**
     * Supprimer le logo
     */
    function removeLogoImage() {
        var $dropzone = $('#logo_dropzone');
        $dropzone.removeClass('has_image');
        $dropzone.find('.dropzone_placeholder').removeClass('hidden');
        $dropzone.find('.dropzone_preview').addClass('hidden').find('img').attr('src', '');
        $dropzone.closest('.vendor_field').find('.author_id_image').val('');
        updateCompletionGauge();
    }

    /* ==========================================================================
       6. (Reserved for future use)
       ========================================================================== */

    /* ==========================================================================
       7. Social Networks Management (moved to end of file)
       ========================================================================== */

    /* ==========================================================================
       8. Location Fields - Select2 with Nominatim (OpenStreetMap)
       ========================================================================== */

    // Initialize Select2 for address (search only - no tags)
    // Utilisation de l'API Adresse data.gouv.fr
    console.log('[Profile] Initialisation Select2 adresse...');
    console.log('[Profile] #profile_address exists:', $('#profile_address').length > 0);
    console.log('[Profile] $.fn.select2 exists:', typeof $.fn.select2 !== 'undefined');

    if ($('#profile_address').length && $.fn.select2) {
        console.log('[Profile] Initialisation Select2 en cours...');

        $('#profile_address').select2({
            placeholder: 'Rechercher une adresse...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 3,
            language: {
                inputTooShort: function() {
                    return 'Saisissez au moins 3 caractères';
                },
                searching: function() {
                    return 'Recherche en cours...';
                },
                noResults: function() {
                    return 'Aucune adresse trouvée';
                }
            },
            ajax: {
                url: 'https://api-adresse.data.gouv.fr/search/',
                dataType: 'json',
                delay: 350,
                data: function(params) {
                    console.log('[Profile] Recherche adresse:', params.term);
                    return {
                        q: params.term,
                        limit: 10,
                        autocomplete: 1
                    };
                },
                processResults: function(data) {
                    console.log('[Profile] Réponse API:', data);
                    if (!data || !data.features) {
                        console.warn('[Profile] Pas de features dans la réponse');
                        return { results: [] };
                    }
                    var results = data.features.map(function(item) {
                        var props = item.properties || {};
                        var coords = item.geometry && item.geometry.coordinates ? item.geometry.coordinates : [0, 0];
                        return {
                            id: props.label || props.name || item.properties.id,
                            text: props.label || props.name,
                            lat: coords[1],
                            lon: coords[0],
                            address: {
                                house_number: props.housenumber || '',
                                road: props.street || '',
                                city: props.city || '',
                                postcode: props.postcode || '',
                                country_code: 'FR'
                            }
                        };
                    });
                    console.log('[Profile] Résultats mappés:', results.length);
                    return { results: results };
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('[Profile] Erreur AJAX:', textStatus, errorThrown);
                }
            },
            templateResult: formatAddressResult
        });

        console.log('[Profile] Select2 initialisé avec succès');

        // When address is selected
        $('#profile_address').on('select2:select', function(e) {
            var data = e.params.data;
            if (data.lat && data.lon) {
                updateLocationCoordinates(data.lat, data.lon);

                // Update hidden fields with address components
                if (data.address) {
                    var addr = data.address;
                    var streetAddress = [addr.house_number, addr.road].filter(Boolean).join(' ');
                    $('#user_address_line1').val(streetAddress || data.text.split(',')[0]);
                    $('#user_city').val(addr.city || addr.town || addr.village || addr.municipality || '');
                    $('#user_postcode').val(addr.postcode || '');
                    $('#user_country').val(addr.country_code ? addr.country_code.toUpperCase() : 'FR');
                }
            }
        });
    }

    // Format address search results
    function formatAddressResult(item) {
        if (item.loading) {
            return $('<span>Recherche...</span>');
        }

        var $container = $(
            '<div class="select2-result-address">' +
                '<div class="select2-result-address__icon"><i class="fa fa-map-marker-alt"></i></div>' +
                '<div class="select2-result-address__text"></div>' +
            '</div>'
        );

        $container.find('.select2-result-address__text').text(item.text);

        return $container;
    }

    // Update coordinates and map
    function updateLocationCoordinates(lat, lon) {
        $('#org_latitude').val(lat);
        $('#org_longitude').val(lon);
        $('#user_lat').val(lat);
        $('#user_lng').val(lon);

        // Update Leaflet map if available
        if (profileMap && profileMarker) {
            var latLng = [parseFloat(lat), parseFloat(lon)];
            profileMarker.setLatLng(latLng);
            profileMap.setView(latLng, 15);
        }

        // Update completion gauge
        updateCompletionGauge();
    }

    /* ==========================================================================
       9. Leaflet Map Initialization for Profile
       ========================================================================== */

    var profileMap = null;
    var profileMarker = null;

    function initProfileMap() {
        var $mapContainer = $('#profile_osm_map');
        if (!$mapContainer.length || typeof L === 'undefined') {
            return;
        }

        // Éviter la double initialisation - vérifier à la fois la variable et l'élément DOM
        if (profileMap !== null) {
            return;
        }

        // Vérifier si l'élément DOM a déjà été initialisé par Leaflet
        var mapElement = $mapContainer[0];
        if (mapElement._leaflet_id) {
            return;
        }

        var lat = parseFloat($mapContainer.data('lat')) || 48.8566;
        var lng = parseFloat($mapContainer.data('lng')) || 2.3522;

        // Initialize map
        profileMap = L.map('profile_osm_map').setView([lat, lng], 15);

        // Add OSM tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(profileMap);

        // Add draggable marker
        profileMarker = L.marker([lat, lng], { draggable: true }).addTo(profileMap);

        // Handle marker drag
        profileMarker.on('dragend', function(e) {
            var pos = e.target.getLatLng();
            $('#org_latitude').val(pos.lat.toFixed(6));
            $('#org_longitude').val(pos.lng.toFixed(6));
            $('#user_lat').val(pos.lat.toFixed(6));
            $('#user_lng').val(pos.lng.toFixed(6));
            reverseGeocode(pos.lat, pos.lng);
        });

        // Handle map click
        profileMap.on('click', function(e) {
            profileMarker.setLatLng(e.latlng);
            $('#org_latitude').val(e.latlng.lat.toFixed(6));
            $('#org_longitude').val(e.latlng.lng.toFixed(6));
            $('#user_lat').val(e.latlng.lat.toFixed(6));
            $('#user_lng').val(e.latlng.lng.toFixed(6));
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        // Handle manual lat/lng input
        $('#org_latitude, #org_longitude').on('change', function() {
            var newLat = parseFloat($('#org_latitude').val());
            var newLng = parseFloat($('#org_longitude').val());
            if (!isNaN(newLat) && !isNaN(newLng)) {
                var latLng = [newLat, newLng];
                profileMarker.setLatLng(latLng);
                profileMap.setView(latLng, 15);
            }
        });
    }

    // Reverse geocode to get address from coordinates
    function reverseGeocode(lat, lng) {
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/reverse',
            data: {
                lat: lat,
                lon: lng,
                format: 'json',
                'accept-language': 'fr'
            },
            success: function(data) {
                if (data && data.display_name) {
                    var $addressSelect = $('#profile_address');
                    $addressSelect.empty();
                    $addressSelect.append(new Option(data.display_name, data.display_name, true, true));
                    $addressSelect.trigger('change');

                    // Update hidden fields
                    if (data.address) {
                        var addr = data.address;
                        var streetAddress = [addr.house_number, addr.road].filter(Boolean).join(' ');
                        $('#user_address_line1').val(streetAddress || data.display_name.split(',')[0]);
                        $('#user_city').val(addr.city || addr.town || addr.village || addr.municipality || '');
                        $('#user_postcode').val(addr.postcode || '');
                    }
                }
            }
        });
    }

    // Initialize map when Leaflet is loaded
    if (typeof L !== 'undefined') {
        initProfileMap();
    } else {
        // Wait for Leaflet to load
        $(window).on('load', function() {
            if (typeof L !== 'undefined') {
                initProfileMap();
            }
        });
    }

    // Expose function for external use (called from api-datagouv.js)
    window.updateProfileMap = function(lat, lng) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (profileMap && profileMarker && !isNaN(lat) && !isNaN(lng)) {
            var latLng = [lat, lng];
            profileMarker.setLatLng(latLng);
            profileMap.setView(latLng, 15);
        }
    };

    // Listen for custom event to update coordinates (from api-datagouv.js)
    $(document).on('el:coordinates:updated', function(e, data) {
        if (data && data.lat && data.lng) {
            window.updateProfileMap(data.lat, data.lng);
        }
    });

    /* ==========================================================================
       7. Password Change Toggle
       ========================================================================== */

    // Toggle password change block visibility
    $('#toggle_password_change').on('change', function() {
        var $passwordBlock = $('#password_change_block');
        if ($(this).is(':checked')) {
            $passwordBlock.slideDown(300);
        } else {
            $passwordBlock.slideUp(300);
            // Clear password fields when hiding
            $passwordBlock.find('input[type="password"]').val('');
        }
    });

    // Show/Hide password toggle - utiliser la délégation d'événements
    $(document).on('click', '.show_pass', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var $icon = $(this).find('i');

        if ($input.length && $input.attr('type') === 'password') {
            // Afficher le mot de passe → icône œil ouvert
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else if ($input.length) {
            // Masquer le mot de passe → icône œil barré
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    /* ==========================================================================
       Social Networks Management
       ========================================================================== */

    // Liste des réseaux sociaux disponibles (synchronisée avec PHP el_get_social())
    var socialNetworks = {
        'social_facebook_circle': 'Facebook',
        'social_twitter_circle': 'Twitter',
        'social_tiktok_circle': 'TikTok',
        'social_pinterest_circle': 'Pinterest',
        'social_instagram_circle': 'Instagram',
        'social_linkedin_circle': 'LinkedIn',
        'social_youtube_circle': 'YouTube',
        'social_vimeo_circle': 'Vimeo'
    };

    // Ajouter un nouveau réseau social
    $('#btn_add_social').on('click', function() {
        var $wrapper = $('#social_items_wrapper');
        var newIndex = $wrapper.find('.social_item').length;

        var optionsHtml = '';
        $.each(socialNetworks, function(key, value) {
            optionsHtml += '<option value="' + key + '">' + value + '</option>';
        });

        var newItem = '<div class="social_item">' +
            '<select name="user_profile_social[' + newIndex + '][icon]" class="icon_social">' +
            optionsHtml +
            '</select>' +
            '<input type="url" name="user_profile_social[' + newIndex + '][link]" class="link_social" value="" placeholder="https://..." />' +
            '<button type="button" class="btn_remove_social" title="Supprimer"><i class="fa fa-trash"></i></button>' +
            '</div>';

        $wrapper.append(newItem);
    });

    // Supprimer un réseau social
    $(document).on('click', '.btn_remove_social', function() {
        $(this).closest('.social_item').remove();
        // Réindexer les champs pour éviter les trous
        reindexSocialItems();
    });

    // Réindexer les champs de réseaux sociaux
    function reindexSocialItems() {
        $('#social_items_wrapper .social_item').each(function(index) {
            $(this).find('.icon_social').attr('name', 'user_profile_social[' + index + '][icon]');
            $(this).find('.link_social').attr('name', 'user_profile_social[' + index + '][link]');
        });
    }

    // Tooltip pour les champs URL des réseaux sociaux (afficher l'URL complète au survol)
    function updateSocialLinkTooltips() {
        $('#social_items_wrapper .link_social').each(function() {
            var val = $(this).val();
            if (val) {
                $(this).attr('title', val);
            }
        });
    }

    // Mettre à jour le tooltip quand l'URL change
    $(document).on('input change', '.link_social', function() {
        var val = $(this).val();
        $(this).attr('title', val || '');
    });

    // Initialiser les tooltips au chargement
    updateSocialLinkTooltips();

    /* ==========================================================================
       9. Email OTP Verification Modal
       ========================================================================== */

    /**
     * Show the OTP verification modal
     */
    function showEmailOtpModal() {
        // Create modal if it doesn't exist
        if ($('#email_otp_modal').length === 0) {
            var modalHtml = '<div id="email_otp_modal" class="el_modal_overlay">' +
                '<div class="el_modal">' +
                '<div class="el_modal_header">' +
                '<h3><i class="fas fa-shield-alt"></i> Vérification de l\'email</h3>' +
                '<button type="button" class="el_modal_close" aria-label="Fermer">&times;</button>' +
                '</div>' +
                '<div class="el_modal_body">' +
                '<p>Un code de vérification à 6 chiffres a été envoyé à votre adresse email actuelle.</p>' +
                '<p class="otp_info">Entrez le code pour confirmer le changement d\'adresse email.</p>' +
                '<div class="otp_input_wrapper">' +
                '<input type="text" id="otp_input_1" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus>' +
                '<input type="text" id="otp_input_2" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric">' +
                '<input type="text" id="otp_input_3" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric">' +
                '<input type="text" id="otp_input_4" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric">' +
                '<input type="text" id="otp_input_5" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric">' +
                '<input type="text" id="otp_input_6" class="otp_input" maxlength="1" pattern="[0-9]" inputmode="numeric">' +
                '</div>' +
                '<div class="otp_error" style="display:none;"></div>' +
                '</div>' +
                '<div class="el_modal_footer">' +
                '<button type="button" class="btn_resend_otp">Renvoyer le code</button>' +
                '<button type="button" class="btn_verify_otp btn_primary">Vérifier</button>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('body').append(modalHtml);
            initOtpModalEvents();
        }

        // Show modal
        $('#email_otp_modal').addClass('active');
        $('#otp_input_1').focus();
    }

    /**
     * Initialize OTP modal events
     */
    function initOtpModalEvents() {
        // Close modal
        $(document).on('click', '.el_modal_close, .el_modal_overlay', function(e) {
            if (e.target === this) {
                hideEmailOtpModal();
            }
        });

        // OTP input navigation
        $(document).on('input', '.otp_input', function() {
            var $this = $(this);
            var val = $this.val();

            // Only allow digits
            if (!/^\d*$/.test(val)) {
                $this.val('');
                return;
            }

            // Move to next input
            if (val.length === 1) {
                var $next = $this.next('.otp_input');
                if ($next.length) {
                    $next.focus();
                }
            }
        });

        // Handle backspace
        $(document).on('keydown', '.otp_input', function(e) {
            var $this = $(this);
            if (e.key === 'Backspace' && $this.val() === '') {
                var $prev = $this.prev('.otp_input');
                if ($prev.length) {
                    $prev.focus().val('');
                }
            }
        });

        // Handle paste
        $(document).on('paste', '.otp_input', function(e) {
            e.preventDefault();
            var pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            var digits = pastedData.replace(/\D/g, '').substring(0, 6);

            if (digits.length === 6) {
                for (var i = 0; i < 6; i++) {
                    $('#otp_input_' + (i + 1)).val(digits[i]);
                }
                $('#otp_input_6').focus();
            }
        });

        // Verify OTP button
        $(document).on('click', '.btn_verify_otp', function() {
            verifyEmailOtp();
        });

        // Resend OTP button
        $(document).on('click', '.btn_resend_otp', function() {
            resendEmailOtp();
        });

        // Enter key to verify
        $(document).on('keydown', '.otp_input', function(e) {
            if (e.key === 'Enter') {
                verifyEmailOtp();
            }
        });
    }

    /**
     * Hide the OTP modal
     */
    function hideEmailOtpModal() {
        $('#email_otp_modal').removeClass('active');
        // Clear inputs
        $('.otp_input').val('');
        $('.otp_error').hide().text('');
    }

    /**
     * Verify the OTP code
     */
    function verifyEmailOtp() {
        var otpCode = '';
        for (var i = 1; i <= 6; i++) {
            otpCode += $('#otp_input_' + i).val();
        }

        if (otpCode.length !== 6) {
            $('.otp_error').text('Veuillez entrer le code à 6 chiffres').show();
            return;
        }

        var $btn = $('.btn_verify_otp');
        $btn.prop('disabled', true).text('Vérification...');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'el_verify_email_otp',
                nonce: ajax_object.nonce,
                otp_code: otpCode
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Vérifier');

                if (response.success) {
                    hideEmailOtpModal();
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                    // Update email field
                    if (response.data.new_email) {
                        $('#user_email').val(response.data.new_email);
                    }
                } else {
                    $('.otp_error').text(response.data.message).show();
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Vérifier');
                $('.otp_error').text('Erreur de connexion. Veuillez réessayer.').show();
            }
        });
    }

    /**
     * Resend the OTP code
     */
    function resendEmailOtp() {
        var $btn = $('.btn_resend_otp');
        $btn.prop('disabled', true).text('Envoi...');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'el_resend_email_otp',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Renvoyer le code');

                if (response.success) {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                    // Clear inputs
                    $('.otp_input').val('');
                    $('#otp_input_1').focus();
                } else {
                    $('.otp_error').text(response.data.message).show();
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Renvoyer le code');
                $('.otp_error').text('Erreur de connexion. Veuillez réessayer.').show();
            }
        });
    }

});
