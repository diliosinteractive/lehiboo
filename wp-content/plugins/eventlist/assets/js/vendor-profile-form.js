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
       2. Completion Gauge Logic
       ========================================================================== */

    function updateCompletionGauge() {
        var totalWeight = 0;
        var filledWeight = 0;

        // Helper to check if field has value
        function hasValue(selector) {
            var el = $(selector);
            if (el.length === 0) return false;
            if (el.is(':checkbox') || el.is(':radio')) return el.is(':checked');
            return $.trim(el.val()) !== '';
        }

        // Define weighted sections for profile
        var sections = [
            // Personal Info (20%)
            { id: 'first_name', weight: 5, selector: 'input[name="first_name"]' },
            { id: 'last_name', weight: 5, selector: 'input[name="last_name"]' },
            { id: 'user_email', weight: 5, selector: 'input[name="user_email"]' },
            { id: 'user_phone', weight: 5, selector: 'input[name="user_phone"]' },

            // Organization (30%)
            { id: 'org_name', weight: 10, selector: 'input[name="org_name"]' },
            { id: 'org_display_name', weight: 10, selector: 'input[name="org_display_name"]' },
            { id: 'org_siret', weight: 5, selector: 'input[name="org_siret"]' },
            { id: 'org_type_structure', weight: 5, selector: 'select[name="org_type_structure"]' },

            // Location (20%)
            { id: 'user_address', weight: 10, selector: 'select[name="user_address"]' },
            { id: 'user_city', weight: 5, selector: 'input[name="user_city"]' },
            { id: 'user_postcode', weight: 5, selector: 'input[name="user_postcode"]' },

            // Presentation (30%)
            { id: 'org_description', weight: 10, selector: 'textarea[name="org_description"]' },
            { id: 'org_cover_image', weight: 10, selector: 'input[name="org_cover_image"]' },
            { id: 'org_logo', weight: 10, selector: 'input[name="org_logo"]' }
        ];

        // Calculate
        sections.forEach(function (section) {
            totalWeight += section.weight;
            var isFilled = false;

            if (section.check) {
                isFilled = section.check();
            } else {
                isFilled = hasValue(section.selector);
            }

            if (isFilled) {
                filledWeight += section.weight;
            }
        });

        var percent = Math.round((filledWeight / totalWeight) * 100);
        if (percent > 100) percent = 100;

        // Update UI (Sidebar Widget)
        $('#el-completion-fill-sidebar').css('width', percent + '%');
        $('#el-completion-percent-sidebar').text(percent + '%');

        // Update navigation status icons
        updateNavigationStatus();
    }

    // Update section completion status in navigation
    function updateNavigationStatus() {
        // Check each section
        var sections = {
            'section_profile': ['input[name="first_name"]', 'input[name="last_name"]', 'input[name="user_email"]'],
            'section_organisation': ['input[name="org_name"]', 'input[name="org_display_name"]'],
            'section_localisation': ['select[name="user_address"]'],
            'section_presentation': ['textarea[name="org_description"]'],
            'section_password': [], // Always optional
            'section_bank': [], // Optional
            'section_stripe': [] // Optional
        };

        $.each(sections, function(sectionId, requiredFields) {
            var isComplete = true;

            requiredFields.forEach(function(field) {
                if ($(field).length && $.trim($(field).val()) === '') {
                    isComplete = false;
                }
            });

            var $navItem = $('.el-anchor-nav a[href="#' + sectionId + '"]').parent('li');
            if (isComplete && requiredFields.length > 0) {
                $navItem.addClass('section-complete');
            } else {
                $navItem.removeClass('section-complete');
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
       7. Social Networks Management
       ========================================================================== */

    var socialIndex = $('#social_list .social_item').length;

    // Add Social Network
    $(document).on('click', '.add_social', function(e) {
        e.preventDefault();

        var socialOptions = '';
        var $existingSelect = $('#social_list .social_item:first .icon_social');
        if ($existingSelect.length) {
            socialOptions = $existingSelect.html();
        } else {
            socialOptions = `
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="twitter">Twitter/X</option>
                <option value="linkedin">LinkedIn</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
                <option value="website">Site web</option>
            `;
        }

        var html = `
            <div class="social_item vendor_field">
                <select name="user_profile_social[${socialIndex}][icon]" class="icon_social">
                    ${socialOptions}
                </select>
                <input type="text" name="user_profile_social[${socialIndex}][link]" value="" class="link_social" placeholder="https://">
                <button type="button" class="button remove_social">x</button>
            </div>
        `;

        $('#social_list, .social_list').append(html);
        socialIndex++;
    });

    // Remove Social Network
    $(document).on('click', '.remove_social', function(e) {
        e.preventDefault();
        $(this).closest('.social_item').fadeOut(200, function() {
            $(this).remove();
        });
    });

    /* ==========================================================================
       8. Location Fields - Select2 with Nominatim (OpenStreetMap)
       ========================================================================== */

    // Initialize Select2 for address (search only - no tags)
    if ($('#profile_address').length && $.fn.select2) {
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
                    return 'Recherche...';
                },
                noResults: function() {
                    return 'Aucun résultat trouvé';
                }
            },
            ajax: {
                url: 'https://nominatim.openstreetmap.org/search',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term,
                        format: 'json',
                        addressdetails: 1,
                        limit: 10,
                        countrycodes: 'fr',
                        'accept-language': 'fr'
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.display_name,
                                text: item.display_name,
                                lat: item.lat,
                                lon: item.lon,
                                address: item.address || {}
                            };
                        })
                    };
                }
            },
            templateResult: formatAddressResult
        });

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

    // Show/Hide password toggle
    $('.show_pass').on('click', function() {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
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

});
