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
            { id: 'user_address', weight: 10, selector: 'input[name="user_address_line1"]' },
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
            'section_localisation': ['input[name="user_address_line1"]'],
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
       4. Password Toggle
       ========================================================================== */

    $('.show_pass').on('click', function () {
        var $input = $(this).siblings('input');
        var $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        }
    });

    /* ==========================================================================
       5. Image Upload Handlers (Cover & Logo)
       ========================================================================== */

    // Cover Image
    $('#cover_dropzone').on('click', function (e) {
        if (!$(e.target).is('button')) {
            $(this).find('.cover_file_input').click();
        }
    });

    $('#cover_dropzone .cover_file_input').on('change', function () {
        if (this.files && this.files[0]) {
            uploadImage(this.files[0], 'cover');
        }
    });

    $('.btn_change_cover').on('click', function (e) {
        e.stopPropagation();
        $('#cover_dropzone .cover_file_input').click();
    });

    $('.btn_remove_cover').on('click', function (e) {
        e.stopPropagation();
        removeCoverImage();
    });

    // Logo
    $('#logo_dropzone').on('click', function (e) {
        if (!$(e.target).is('button')) {
            $(this).find('.logo_file_input').click();
        }
    });

    $('#logo_dropzone .logo_file_input').on('change', function () {
        if (this.files && this.files[0]) {
            uploadImage(this.files[0], 'logo');
        }
    });

    $('.btn_change_logo').on('click', function (e) {
        e.stopPropagation();
        $('#logo_dropzone .logo_file_input').click();
    });

    $('.btn_remove_logo').on('click', function (e) {
        e.stopPropagation();
        removeLogoImage();
    });

    // Upload function
    function uploadImage(file, type) {
        // Validate file type
        if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/i)) {
            alert('Format non supporté. Utilisez JPG, PNG, GIF ou WebP.');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('L\'image est trop volumineuse. Maximum 10 Mo.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'el_upload_vendor_media');
        formData.append('file', file);
        formData.append('nonce', el_vendor_media_params ? el_vendor_media_params.nonce : '');

        var $dropzone = type === 'cover' ? $('#cover_dropzone') : $('#logo_dropzone');
        $dropzone.addClass('uploading');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $dropzone.removeClass('uploading');

                if (response.success && response.data) {
                    if (type === 'cover') {
                        updateCoverImage(response.data.id, response.data.url);
                    } else {
                        updateLogoImage(response.data.id, response.data.url);
                    }
                } else {
                    alert(response.data?.message || 'Erreur lors du téléversement');
                }
            },
            error: function () {
                $dropzone.removeClass('uploading');
                alert('Erreur de connexion');
            }
        });
    }

    function updateCoverImage(id, url) {
        var $dropzone = $('#cover_dropzone');
        $dropzone.addClass('has_image');
        $dropzone.find('.dropzone_placeholder').addClass('hidden');
        $dropzone.find('.dropzone_preview').removeClass('hidden').find('img').attr('src', url);
        $dropzone.closest('.vendor_field').find('.org_cover_image_id').val(id);
        updateCompletionGauge();
    }

    function removeCoverImage() {
        var $dropzone = $('#cover_dropzone');
        $dropzone.removeClass('has_image');
        $dropzone.find('.dropzone_placeholder').removeClass('hidden');
        $dropzone.find('.dropzone_preview').addClass('hidden').find('img').attr('src', '');
        $dropzone.closest('.vendor_field').find('.org_cover_image_id').val('');
        updateCompletionGauge();
    }

    function updateLogoImage(id, url) {
        var $dropzone = $('#logo_dropzone');
        $dropzone.addClass('has_image');
        $dropzone.find('.dropzone_placeholder').addClass('hidden');
        $dropzone.find('.dropzone_preview').removeClass('hidden').find('img').attr('src', url);
        $dropzone.closest('.vendor_field').find('.org_logo_id').val(id);
        updateCompletionGauge();
    }

    function removeLogoImage() {
        var $dropzone = $('#logo_dropzone');
        $dropzone.removeClass('has_image');
        $dropzone.find('.dropzone_placeholder').removeClass('hidden');
        $dropzone.find('.dropzone_preview').addClass('hidden').find('img').attr('src', '');
        $dropzone.closest('.vendor_field').find('.org_logo_id').val('');
        updateCompletionGauge();
    }

    /* ==========================================================================
       6. Drag & Drop Support
       ========================================================================== */

    var dropzones = ['#cover_dropzone', '#logo_dropzone'];

    dropzones.forEach(function (selector) {
        var $zone = $(selector);
        var type = selector === '#cover_dropzone' ? 'cover' : 'logo';

        $zone.on('dragover dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('is_dragover');
        });

        $zone.on('dragleave dragend', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is_dragover');
        });

        $zone.on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is_dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                uploadImage(files[0], type);
            }
        });
    });

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

});
