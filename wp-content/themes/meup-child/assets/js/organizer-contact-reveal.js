/**
 * Organizer Contact Reveal & Tracking
 *
 * Gère l'affichage progressif du téléphone et de l'adresse
 * avec tracking AJAX des vues.
 *
 * @package LeHiboo
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Track contact view via AJAX
     */
    function trackContactView(organizerId, contactType, eventId, context) {
        if (!el_ajax_object || !el_ajax_object.tracking_nonce) {
            console.warn('Tracking nonce not available');
            return;
        }

        $.ajax({
            url: el_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'track_organizer_contact_view',
                nonce: el_ajax_object.tracking_nonce,
                organizer_id: organizerId,
                contact_type: contactType,
                event_id: eventId || null,
                context: context || 'unknown'
            },
            success: function(response) {
                if (response.success) {
                    console.log('Contact view tracked:', contactType);
                }
            },
            error: function(xhr, status, error) {
                console.error('Tracking error:', error);
            }
        });
    }

    /**
     * Reveal phone number
     */
    $(document).on('click', '.btn_reveal_phone', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $container = $btn.closest('.contact_reveal_container');
        const $hiddenValue = $container.find('.contact_hidden_value');

        // Get tracking data
        const organizerId = $btn.data('organizer-id');
        const eventId = $btn.data('event-id') || null;
        const context = $btn.data('context') || 'unknown';
        const phoneNumber = $btn.data('phone');

        // Reveal phone number
        if ($hiddenValue.length && phoneNumber) {
            $hiddenValue.html('<a href="tel:' + phoneNumber.replace(/[^0-9+]/g, '') + '">' + phoneNumber + '</a>');
            $hiddenValue.addClass('revealed');
        }

        // Hide button with fade
        $btn.fadeOut(300, function() {
            $(this).remove();
        });

        // Track view
        trackContactView(organizerId, 'phone', eventId, context);
    });

    /**
     * Reveal address
     */
    $(document).on('click', '.btn_reveal_address', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $container = $btn.closest('.contact_reveal_container');
        const $hiddenValue = $container.find('.contact_hidden_value');

        // Get tracking data
        const organizerId = $btn.data('organizer-id');
        const eventId = $btn.data('event-id') || null;
        const context = $btn.data('context') || 'unknown';
        const address = $btn.data('address');

        // Reveal address
        if ($hiddenValue.length && address) {
            $hiddenValue.html(address);
            $hiddenValue.addClass('revealed');
        }

        // Hide button with fade
        $btn.fadeOut(300, function() {
            $(this).remove();
        });

        // Track view
        trackContactView(organizerId, 'address', eventId, context);
    });

})(jQuery);
