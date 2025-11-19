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

});
