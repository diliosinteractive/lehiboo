jQuery(document).ready(function ($) {

    /* ==========================================================================
       1. Navigation & ScrollSpy
       ========================================================================== */

    // Smooth scrolling for anchor links
    $('.profile_tabs_nav a').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var offset = 140; // Adjust based on sticky header height

        if ($(target).length) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - offset
            }, 500);

            // Update active state manually
            $('.profile_tabs_nav li').removeClass('active');
            $(this).parent('li').addClass('active');
        }
    });

    // ScrollSpy behavior
    $(window).on('scroll', function () {
        var scrollPos = $(document).scrollTop();
        var offset = 160; // Trigger point

        $('.profile_tabs_nav a').each(function () {
            var currLink = $(this);
            var refElement = $(currLink.attr('href'));

            if (refElement.length && refElement.position().top - offset <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
                $('.profile_tabs_nav li').removeClass('active');
                currLink.parent('li').addClass('active');
            }
        });
    });

    /* ==========================================================================
       2. Dynamic Form Logic
       ========================================================================== */

    // --- Location Section ---

    // Toggle Physical vs Online
    $('input[name*="event_type"]').on('change', function () {
        var type = $(this).val();

        // Update Active State for Cards
        $('.el_card_radio').removeClass('active');
        $(this).closest('.el_card_radio').addClass('active');

        if (type === 'online') {
            $('.physical_location_section').slideUp();
            $('.online_location_section').slideDown();
        } else {
            $('.online_location_section').slideUp();
            $('.physical_location_section').slideDown();
        }
    });

    // Toggle Address Source (Entity vs New)
    $('input[name*="address_source"]').on('change', function () {
        var source = $(this).val();
        if (source === 'entity') {
            $('.el_map_wrapper input').prop('disabled', true).addClass('disabled-input');
        } else {
            $('.el_map_wrapper input').prop('disabled', false).removeClass('disabled-input');
        }
    });

    // --- Calendar Section ---

    // Toggle Manual vs Recurring
    $('input.option_calendar').on('change', function () {
        var mode = $(this).val();

        // Update Active State for Cards
        $('.option_calendar').closest('.el_card_radio').removeClass('active');
        $(this).closest('.el_card_radio').addClass('active');

        if (mode === 'auto') {
            $('.calendar .manual').slideUp();
            $('.calendar .auto').slideDown();
        } else {
            $('.calendar .auto').slideUp();
            $('.calendar .manual').slideDown();
        }
    });

    // --- Ticket Section ---

    // Toggle Ticket Mode
    $('input[name*="ticket_link"]').on('change', function () {
        var mode = $(this).val();

        // Update Active State for Cards
        $('input[name*="ticket_link"]').closest('.el_card_radio').removeClass('active');
        $(this).closest('.el_card_radio').addClass('active');

        $('.ticket_external_link_section').hide();
        $('.ticket_internal_link_section').hide();

        if (mode === 'ticket_external_link') {
            $('.ticket_external_link_section').slideDown();
        } else if (mode === 'ticket_internal_link') {
            $('.ticket_internal_link_section').slideDown();
        }
    });

    // Add External Price Row
    $('.add_external_price').on('click', function (e) {
        e.preventDefault();
        var container = $('.external_prices_list');
        var index = container.find('.external_price_item').length;
        var prefix = 'event_';

        var html = `
            <div class="external_price_item">
                <input type="text" name="${prefix}ticket_external_prices[${index}][name]" placeholder="Nom du tarif" class="price_name_input" />
                <input type="text" name="${prefix}ticket_external_prices[${index}][price]" placeholder="Prix" class="price_amount_input" />
                <span class="currency_symbol">€</span>
                <button type="button" class="button remove_external_price">x</button>
            </div>
        `;
        container.append(html);
    });

    // Remove External Price Row
    $(document).on('click', '.remove_external_price', function () {
        $(this).closest('.external_price_item').remove();
    });

    // Toggle Organizer Info Custom Fields
    $('#info_organizer').on('change', function () {
        if ($(this).is(':checked')) {
            $('.organizer_custom_info').slideUp();
        } else {
            $('.organizer_custom_info').slideDown();
        }
    });


    // --- Publication Section ---

    // Toggle Password Field
    $('input[name="event_status"]').on('change', function () {
        var status = $(this).val();

        // Update Active State for Cards
        $('input[name="event_status"]').closest('.el_card_radio').removeClass('active');
        $(this).closest('.el_card_radio').addClass('active');

        if (status === 'protected' || status === 'private') {
            $('.wrap_event_password').slideDown().addClass('is-active');
        } else {
            $('.wrap_event_password').slideUp().removeClass('is-active');
        }
    });

    // Show/Hide Password
    $('.show_hide_password').on('click', function (e) {
        e.preventDefault();
        var input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });


    /* ==========================================================================
       3. Completion Gauge Logic
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

        // Define weighted sections
        var sections = [
            { id: 'post_title', weight: 10, selector: 'input[name="post_title"]' }, // Title is crucial
            { id: 'event_cat', weight: 10, selector: 'select[name="event_cat"]' },
            { id: 'content', weight: 10, selector: 'textarea[name="content_event"]' }, // Description
            { id: 'image', weight: 10, selector: 'input[name="_thumbnail_id"]' }, // Featured Image

            // Location (Conditional)
            {
                id: 'location',
                weight: 15,
                check: function () {
                    var type = $('input[name*="event_type"]:checked').val();
                    if (type === 'online') {
                        return hasValue('input[name*="event_online_url"]');
                    } else {
                        return hasValue('input[name*="address"]');
                    }
                }
            },

            // Date (Conditional)
            {
                id: 'date',
                weight: 15,
                check: function () {
                    var mode = $('input.option_calendar:checked').val();
                    if (mode === 'auto') {
                        return hasValue('.calendar_auto_start_date');
                    } else {
                        return $('.item_calendar').length > 0 && hasValue('.item_calendar:first .calendar_date');
                    }
                }
            },

            // Ticket (Conditional)
            {
                id: 'ticket',
                weight: 10,
                check: function () {
                    var link = $('input[name*="ticket_link"]:checked').val();
                    if (link === 'ticket_external_link') {
                        return hasValue('input[name*="ticket_external_link"]');
                    } else if (link === 'ticket_internal_link') {
                        return $('.ticket_item').length > 0;
                    }
                    return true;
                }
            },

            // Publication
            { id: 'status', weight: 5, selector: 'input[name="event_status"]:checked' }
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

        // Enable/Disable Go Live
        if (percent >= 80) {
            $('#el-btn-go-live').prop('disabled', false).removeClass('disabled');
        } else {
            $('#el-btn-go-live').prop('disabled', true).addClass('disabled');
        }
    }

    // Trigger update on input change
    $('form#el-vendor-event-form').on('change input', 'input, select, textarea', function () {
        updateCompletionGauge();
    });

    // Initial check
    setTimeout(updateCompletionGauge, 1000);


    /* ==========================================================================
       4. AJAX Save Logic
       ========================================================================== */

    // Handle Save Button Click
    $('#el-btn-save').on('click', function (e) {
        e.preventDefault();
        saveEvent();
    });

    // Handle Go Live Button Click  
    $('#el-btn-go-live').on('click', function (e) {
        e.preventDefault();
        $('#publish_event_input').val('publish');
        saveEvent();
    });

    function saveEvent() {
        var $form = $('#el-vendor-event-form');
        var $saveBtn = $('#el-btn-save');

        // V1 Le Hiboo - Validation des champs obligatoires
        var validationErrors = [];
        var firstErrorField = null;

        // Titre de l'événement
        var eventTitle = $form.find('input[name="name_event"], input[name="post_title"]').val();
        if (!eventTitle || $.trim(eventTitle) === '') {
            validationErrors.push('Le titre de l\'activité est obligatoire');
            if (!firstErrorField) firstErrorField = $form.find('input[name="name_event"], input[name="post_title"]').first();
        }

        // Catégorie
        var eventCat = $form.find('select[name="event_cat"]').val();
        if (!eventCat || eventCat === '' || eventCat === '0') {
            validationErrors.push('La catégorie est obligatoire');
            if (!firstErrorField) firstErrorField = $form.find('select[name="event_cat"]');
        }

        // Type d'événement (gratuit/payant)
        var ticketType = $form.find('select[name*="ticket_global_type"]').val();
        if (!ticketType || ticketType === '') {
            validationErrors.push('Veuillez indiquer si l\'événement est gratuit ou payant');
            if (!firstErrorField) firstErrorField = $form.find('select[name*="ticket_global_type"]');
        }

        // Type d'entrée
        var entryType = $form.find('select[name*="entry_type"]').val();
        if (!entryType || entryType === '') {
            validationErrors.push('Le type d\'entrée est obligatoire');
            if (!firstErrorField) firstErrorField = $form.find('select[name*="entry_type"]');
        }

        // Si des erreurs de validation, afficher le message et ne pas enregistrer
        if (validationErrors.length > 0) {
            var errorMessage = 'Veuillez compléter les champs obligatoires :\n\n• ' + validationErrors.join('\n• ');

            if (typeof ToastNotification !== 'undefined') {
                ToastNotification.error(errorMessage.replace(/\n/g, '<br>'));
            } else {
                alert(errorMessage);
            }

            // Scroll vers le premier champ en erreur
            if (firstErrorField && firstErrorField.length) {
                var $section = firstErrorField.closest('.event_section');
                if ($section.length) {
                    $('html, body').animate({
                        scrollTop: $section.offset().top - 150
                    }, 500);
                }
                // Mettre en surbrillance le champ
                firstErrorField.addClass('field-error');
                setTimeout(function() {
                    firstErrorField.removeClass('field-error');
                }, 3000);
            }

            return false;
        }

        // Disable button during save
        $saveBtn.prop('disabled', true).addClass('loading');

        // V1 Le Hiboo - Synchroniser TinyMCE avec le textarea avant sauvegarde
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content_event')) {
            tinyMCE.triggerSave();
        }

        // Helper function to convert form array to nested object
        function serializeObject($form) {
            var obj = {};
            var arr = $form.serializeArray();

            $.each(arr, function (i, field) {
                var name = field.name;
                var value = field.value;

                // Handle fields with [] notation (arrays like select multiple)
                if (name.endsWith('[]')) {
                    // Remove the [] and store as array
                    var cleanName = name.replace(/\[\]$/, '');
                    if (!obj[cleanName]) {
                        obj[cleanName] = [];
                    }
                    obj[cleanName].push(value);
                }
                // Handle array notation field[index][subfield]
                else if (name.indexOf('[') !== -1) {
                    var keys = name.split(/\[|\]\[|\]/).filter(function (k) { return k; });
                    var current = obj;

                    for (var j = 0; j < keys.length - 1; j++) {
                        var key = keys[j];
                        if (!current[key]) {
                            // Check if next key is a number
                            current[key] = /^\d+$/.test(keys[j + 1]) ? [] : {};
                        }
                        current = current[key];
                    }

                    var lastKey = keys[keys.length - 1];
                    if (Array.isArray(current)) {
                        current.push(value);
                    } else {
                        current[lastKey] = value;
                    }
                } else {
                    obj[name] = value;
                }
            });

            return obj;
        }

        // Get all form data as object
        var allData = serializeObject($form);

        // V1 Le Hiboo - Récupérer directement le contenu depuis TinyMCE
        var editorContent = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content_event')) {
            editorContent = tinyMCE.get('content_event').getContent();
        } else {
            // Fallback: récupérer depuis le textarea
            editorContent = $('#content_event').val() || '';
        }

        // Prepare data objects
        var postData = {
            post_id: allData.post_id || allData.event_id,
            el_edit_event_nonce: allData.el_edit_event_nonce,
            name_event: allData.name_event || allData.post_title,
            content_event: editorContent || allData.el_content_event || allData.content_event || '',
            event_cat: allData.event_cat || '',
            event_status: allData.event_status || 'publish',
            event_password: allData.event_password || '',
            img_thumbnail: allData.img_thumbnail || allData._thumbnail_id || ''
        };


        // Taxonomies - doivent être dans data_taxonomy avec leur nom complet
        var dataTaxonomy = {};
        if (allData.event_thematique && allData.event_thematique.length > 0) {
            dataTaxonomy.event_thematique = allData.event_thematique;
        }
        if (allData.event_tag && allData.event_tag.length > 0) {
            dataTaxonomy.event_tag = allData.event_tag;
        }
        if (allData.event_special && allData.event_special.length > 0) {
            dataTaxonomy.event_special = allData.event_special;
        }
        if (allData.event_public && allData.event_public.length > 0) {
            dataTaxonomy.event_public = allData.event_public;
        }
        if (allData.event_emotion && allData.event_emotion.length > 0) {
            dataTaxonomy.event_emotion = allData.event_emotion;
        }

        // Ajouter data_taxonomy à postData
        if (Object.keys(dataTaxonomy).length > 0) {
            postData.data_taxonomy = dataTaxonomy;
        }

        // All other fields go to meta_data (sans le préfixe event_)
        var metaData = {};
        for (var key in allData) {
            if (allData.hasOwnProperty(key)) {
                // Skip post-level fields and taxonomies
                if (key !== 'post_id' && key !== 'event_id' && key !== 'el_edit_event_nonce' &&
                    key !== 'name_event' && key !== 'post_title' && key !== 'content_event' &&
                    key !== 'el_content_event' && key !== 'event_cat' && key !== 'event_status' &&
                    key !== 'event_password' && key !== 'img_thumbnail' && key !== '_thumbnail_id' &&
                    key !== 'event_thematique' && key !== 'event_tag' && key !== 'event_special' &&
                    key !== 'event_public' && key !== 'event_emotion') {

                    // Remove 'event_' prefix only for meta keys, or use clean name if already prefixed with ova_mb_event_
                    var cleanKey = key;
                    if (key.startsWith('event_') && !key.startsWith('ova_mb_event_')) {
                        cleanKey = key.replace(/^event_/, '');
                    } else if (key.startsWith('ova_mb_event_')) {
                        cleanKey = key.replace(/^ova_mb_event_/, '');
                    }
                    metaData[cleanKey] = allData[key];
                }
            }
        }

        // V1 Le Hiboo - Gérer explicitement les checkboxes Services & Accessibilité
        // (les checkboxes non cochées ne sont pas incluses dans serializeArray)
        var serviceCheckboxes = [
            'ova_mb_event_el_handicap',
            'ova_mb_event_el_animal',
            'ova_mb_event_el_baby',
            'ova_mb_event_el_wifi',
            'ova_mb_event_el_parking',
            'ova_mb_event_el_restau'
        ];
        serviceCheckboxes.forEach(function(checkboxName) {
            var cleanKey = checkboxName.replace(/^ova_mb_event_/, '');
            var $checkbox = $form.find('input[name="' + checkboxName + '"]');
            if ($checkbox.length) {
                metaData[cleanKey] = $checkbox.is(':checked') ? 'yes' : '';
            }
        });

        // Send AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_save_edit_event',
                data: postData,
                meta_data: metaData
            },
            success: function (response) {
                $saveBtn.prop('disabled', false).removeClass('loading');

                // Handle successful save
                if (response.status === 'updated' || response.url) {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement sauvegardé avec succès !');
                    }

                    // If we have a URL, redirect to it
                    if (response.url) {
                        setTimeout(function () {
                            window.location.href = response.url;
                        }, 1000);
                    } else {
                        // Otherwise just reload the page
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                }
                // Handle errors
                else if (response.status === 'error' || response.status === 'error_description_too_short') {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(response.message || 'Une erreur est survenue lors de la sauvegarde.');
                    } else {
                        alert(response.message || 'Une erreur est survenue lors de la sauvegarde.');
                    }
                }
                // Fallback for any other success case
                else {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement sauvegardé avec succès !');
                    } else {
                        alert('Événement sauvegardé avec succès!');
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            },
            error: function (xhr, status, error) {
                $saveBtn.prop('disabled', false).removeClass('loading');
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
       5. Duplicate Event Logic
       ========================================================================== */

    // Handle Duplicate Button Click
    $('#el-btn-duplicate').on('click', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var postId = $btn.data('post-id');
        var nonce = $('#el_duplicate_post_nonce').val();

        // Confirm action
        if (!confirm('Voulez-vous dupliquer cette activité ?')) {
            return;
        }

        // Disable button during request
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_duplicate_post',
                data: {
                    post_id: postId,
                    el_duplicate_post_nonce: nonce
                }
            },
            success: function (response) {
                $btn.prop('disabled', false).removeClass('loading');

                if (response.status === 'success' && response.href) {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Activité dupliquée avec succès !');
                    }
                    // Redirect to the new duplicated event
                    setTimeout(function () {
                        window.location.href = response.href;
                    }, 1000);
                } else if (response.status === 'error') {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(response.msg || 'Une erreur est survenue lors de la duplication.');
                    } else {
                        alert(response.msg || 'Une erreur est survenue lors de la duplication.');
                    }
                    // If there's a redirect URL (e.g., for package upgrade), offer to redirect
                    if (response.url && confirm(response.msg)) {
                        window.location.href = response.url;
                    }
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).removeClass('loading');
                console.error('Duplicate error:', error);
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error('Erreur lors de la duplication. Veuillez réessayer.');
                } else {
                    alert('Erreur lors de la duplication. Veuillez réessayer.');
                }
            }
        });
    });


    /* ==========================================================================
       6. Media Picker Integration
       ========================================================================== */

    // ---- Featured Image Picker ----
    $(document).on('click', '.btn_pick_featured_image', function (e) {
        e.preventDefault();

        // Vérifier si le picker est disponible
        if (typeof window.EL_MediaPicker === 'undefined') {
            console.warn('EL_MediaPicker not available');
            return;
        }

        var currentId = $('#img_thumbnail').val();
        var selected = currentId ? [{ id: parseInt(currentId) }] : [];

        window.EL_MediaPicker.open({
            mode: 'single',
            title: 'Choisir l\'image de présentation',
            selected: selected,
            callback: function (images) {
                if (images && images.length > 0) {
                    updateFeaturedImage(images[0]);
                }
            }
        });
    });

    // ---- Update Featured Image ----
    function updateFeaturedImage(image) {
        var $zone = $('.featured_image_zone');
        var $preview = $zone.find('.featured_image_preview');
        var $input = $zone.find('#img_thumbnail');

        // Mettre à jour l'input
        $input.val(image.id);

        // Mettre à jour l'aperçu
        var $img = $preview.find('.image-preview');
        if ($img.length) {
            $img.attr('src', image.url);
        } else {
            $preview.prepend('<img class="image-preview" src="' + image.url + '" alt="">');
        }

        // Ajouter la classe has_image
        $zone.addClass('has_image');
    }

    // ---- Remove Featured Image ----
    $(document).on('click', '.btn_remove_featured', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $zone = $('.featured_image_zone');
        var $preview = $zone.find('.featured_image_preview');
        var $input = $zone.find('#img_thumbnail');

        // Vider l'input et supprimer l'image
        $input.val('');
        $preview.find('.image-preview').remove();
        $zone.removeClass('has_image');
    });

    // ---- Drag & Drop for Featured Image ----
    var $featuredDropzone = $('.featured_dropzone');

    $featuredDropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('is_dragover');
    });

    $featuredDropzone.on('dragleave dragend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is_dragover');
    });

    $featuredDropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is_dragover');

        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadFeaturedImage(files[0]);
        }
    });

    // ---- Upload Featured Image ----
    function uploadFeaturedImage(file) {
        // Vérifier le type
        if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/i)) {
            alert('Format non supporté. Utilisez JPG, PNG, GIF ou WebP.');
            return;
        }

        // Vérifier la taille (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('L\'image est trop volumineuse. Maximum 10 Mo.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'el_upload_vendor_media');
        formData.append('file', file);
        formData.append('nonce', el_vendor_media_params.nonce || '');

        // Afficher un état de chargement
        var $zone = $('.featured_image_zone');
        var $dropzone = $zone.find('.featured_dropzone');
        $dropzone.find('.dropzone_inner').hide();
        $dropzone.append('<div class="upload_loading"><i class="fa fa-spinner fa-spin"></i> Téléversement...</div>');

        $.ajax({
            url: el_vendor_media_params.ajax_url || ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $dropzone.find('.upload_loading').remove();
                $dropzone.find('.dropzone_inner').show();

                if (response.success && response.data) {
                    updateFeaturedImage({
                        id: response.data.id,
                        url: response.data.url
                    });
                } else {
                    alert(response.data?.message || 'Erreur lors du téléversement');
                }
            },
            error: function() {
                $dropzone.find('.upload_loading').remove();
                $dropzone.find('.dropzone_inner').show();
                alert('Erreur de connexion');
            }
        });
    }


    // ---- Gallery Picker ----
    $(document).on('click', '.btn_pick_gallery_images', function (e) {
        e.preventDefault();

        // Vérifier si le picker est disponible
        if (typeof window.EL_MediaPicker === 'undefined') {
            console.warn('EL_MediaPicker not available');
            return;
        }

        // Récupérer les images déjà sélectionnées
        var existingIds = [];
        $('#gallery_sortable .gallery_item').each(function () {
            existingIds.push({ id: parseInt($(this).data('id')) });
        });

        window.EL_MediaPicker.open({
            mode: 'multiple',
            title: 'Ajouter des images à la galerie',
            maxSelection: 20,
            selected: existingIds,
            callback: function (images) {
                if (images && images.length > 0) {
                    var $grid = $('#gallery_sortable');
                    var prefix = 'event_';

                    // Ajouter uniquement les nouvelles images
                    images.forEach(function (image) {
                        // Vérifier si l'image existe déjà
                        if ($grid.find('.gallery_item[data-id="' + image.id + '"]').length === 0) {
                            var html = `
                                <div class="gallery_item" data-id="${image.id}">
                                    <input type="hidden" name="${prefix}gallery[]" value="${image.id}">
                                    <div class="gallery_item_thumb">
                                        <img src="${image.thumb || image.url}" alt="">
                                        <div class="gallery_item_overlay">
                                            <button type="button" class="btn_gallery_remove" title="Supprimer">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="gallery_item_drag">
                                            <i class="fa fa-grip-vertical"></i>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $grid.append(html);
                        }
                    });

                    // Mettre à jour l'état vide
                    updateGalleryEmptyState();
                }
            }
        });
    });

    // ---- Remove Gallery Image ----
    $(document).on('click', '.btn_gallery_remove', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).closest('.gallery_item').fadeOut(200, function () {
            $(this).remove();
            updateGalleryEmptyState();
        });
    });

    // ---- Update Gallery Empty State ----
    function updateGalleryEmptyState() {
        var $grid = $('#gallery_sortable');
        var count = $grid.find('.gallery_item').length;

        if (count === 0) {
            $grid.addClass('is_empty');
        } else {
            $grid.removeClass('is_empty');
        }
    }

    // ---- Initialize Sortable for Gallery ----
    function initGallerySortable() {
        var el = document.getElementById('gallery_sortable');
        if (el && typeof Sortable !== 'undefined') {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.gallery_item',
                filter: '.gallery_empty_state',
                draggable: '.gallery_item',
                onEnd: function () {
                    // L'ordre est automatiquement mis à jour via les inputs hidden
                }
            });
        }
    }

    // Initialiser au chargement
    initGallerySortable();
    updateGalleryEmptyState();


    /* ==========================================================================
       7. Social Networks Management
       ========================================================================== */

    // Global counter for social items
    var socialIndex = $('#social_list .social_item').length;

    // Add Social Network
    $(document).on('click', '.add_social', function(e) {
        e.preventDefault();

        var socialOptions = '';
        // Build social options from existing select if available
        var $existingSelect = $('#social_list .social_item:first .icon_social');
        if ($existingSelect.length) {
            socialOptions = $existingSelect.html();
        } else {
            // Fallback options
            socialOptions = `
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="twitter">Twitter</option>
                <option value="linkedin">LinkedIn</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
                <option value="website">Site web</option>
            `;
        }

        var html = `
            <div class="social_item el_row">
                <div class="el_col_3">
                    <select name="ova_mb_event_social_organizer[${socialIndex}][icon_social]" class="icon_social selectpicker">
                        ${socialOptions}
                    </select>
                </div>
                <div class="el_col_8">
                    <input type="text" name="ova_mb_event_social_organizer[${socialIndex}][link_social]" value="" class="link_social" placeholder="https://">
                </div>
                <div class="el_col_1">
                    <a href="#" class="button remove_social">×</a>
                </div>
            </div>
        `;

        $('#social_list').append(html);

        // Initialize Select2 on the new select
        var $newSelect = $('#social_list .social_item:last .icon_social');
        if ($.fn.select2) {
            $newSelect.select2({
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        }

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
