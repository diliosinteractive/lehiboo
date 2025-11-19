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

        // Disable button during save
        $saveBtn.prop('disabled', true).addClass('loading');

        // Serialize form data
        var formData = $form.serializeArray();

        // Prepare data objects
        var postData = {};
        var metaData = {};

        // Process each field
        formData.forEach(function (field) {
            var name = field.name;
            var value = field.value;

            // Post-level fields
            if (name === 'post_id' || name === 'event_id' || name === 'el_edit_event_nonce' ||
                name === 'event_status' || name === 'event_password') {
                postData[name] = value;
            }
            // Special post data fields without prefix
            else if (name === 'post_title' || name === 'name_event') {
                postData['name_event'] = value;
            }
            else if (name === 'content_event') {
                postData['content_event'] = value;
            }
            else if (name === 'event_cat') {
                postData['event_cat'] = value;
            }
            else if (name === '_thumbnail_id' || name === 'img_thumbnail') {
                postData['img_thumbnail'] = value;
            }
            // All other fields go to meta_data
            else {
                // Remove 'event_' or 'ova_mb_event_' prefix if present
                var cleanName = name.replace(/^event_/, '').replace(/^ova_mb_event_/, '');

                // Handle array fields (those with [])
                if (name.includes('[')) {
                    // Keep the full structure for arrays
                    if (!metaData[name]) {
                        metaData[name] = value;
                    }
                } else {
                    metaData[cleanName] = value;
                }
            }
        });

        // Debug: log what we're sending
        console.log('POST DATA:', postData);
        console.log('META DATA:', metaData);

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
                console.log('AJAX Response:', response);
                $saveBtn.prop('disabled', false).removeClass('loading');

                if (response.url) {
                    // Show success toast then redirect
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement sauvegardé avec succès !');
                    }
                    setTimeout(function () {
                        window.location.href = response.url;
                    }, 1000);
                } else if (response.status === 'error') {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(response.message || 'Une erreur est survenue lors de la sauvegarde.');
                    } else {
                        alert(response.message || 'Une erreur est survenue lors de la sauvegarde.');
                    }
                } else {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement sauvegardé avec succès !');
                    } else {
                        alert('Événement sauvegardé avec succès!');
                    }
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

});
