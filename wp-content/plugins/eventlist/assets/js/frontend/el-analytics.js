/**
 * EventList Analytics Tracking
 * Tracks user interactions with events
 */

(function($) {
    'use strict';

    var EL_Analytics = {

        /**
         * Initialize analytics tracking
         */
        init: function() {
            this.trackPageView();
            this.trackWishlistAdd();
            this.trackContactClick();
            this.trackBookingClick();
            this.trackShareClick();
            this.trackPhoneClick();
            this.trackEmailClick();
            this.trackWebsiteClick();
        },

        /**
         * Get event ID from page
         */
        getEventId: function() {
            // Try multiple methods to get event ID
            var eventId = $('body').data('event-id') ||
                         $('[data-event-id]').first().data('event-id') ||
                         $('.event-single').data('event-id') ||
                         $('#event_id').val();

            return eventId ? parseInt(eventId) : 0;
        },

        /**
         * Send tracking data to server
         */
        trackEvent: function(eventType, metaData) {
            var eventId = this.getEventId();

            if (!eventId) {
                console.log('EL Analytics: No event ID found');
                return;
            }

            if (typeof el_analytics_obj === 'undefined') {
                console.error('EL Analytics: el_analytics_obj is not defined');
                return;
            }

            var data = {
                action: 'el_track_event',
                nonce: el_analytics_obj.nonce,
                event_id: eventId,
                event_type: eventType,
                page_url: window.location.href,
                meta_data: metaData || {}
            };

            console.log('EL Analytics: Tracking', eventType, 'for event', eventId);

            $.ajax({
                url: el_analytics_obj.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('EL Analytics: Success', eventType, response);
                },
                error: function(xhr, status, error) {
                    console.error('EL Analytics: Error', eventType, xhr.responseText);
                }
            });
        },

        /**
         * Track page view
         */
        trackPageView: function() {
            var self = this;

            // Only track on single event pages (WordPress uses 'single-event' class with hyphen)
            if ($('body').hasClass('single-event') || $('body').hasClass('single') && $('body').hasClass('postid-' + self.getEventId())) {
                // Track after a short delay to ensure page is loaded
                setTimeout(function() {
                    self.trackEvent('view', {
                        referrer: document.referrer,
                        scroll_depth: 0
                    });
                }, 1000);

                // Track scroll depth
                var maxScroll = 0;
                var scrollTimer = null;

                $(window).on('scroll', function() {
                    clearTimeout(scrollTimer);

                    scrollTimer = setTimeout(function() {
                        var scrollPercent = Math.round(
                            ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100
                        );

                        if (scrollPercent > maxScroll) {
                            maxScroll = scrollPercent;

                            // Track at 25%, 50%, 75%, 100%
                            if (scrollPercent >= 25 && maxScroll < 50) {
                                self.trackEvent('scroll_25', { scroll_depth: 25 });
                            } else if (scrollPercent >= 50 && maxScroll < 75) {
                                self.trackEvent('scroll_50', { scroll_depth: 50 });
                            } else if (scrollPercent >= 75 && maxScroll < 100) {
                                self.trackEvent('scroll_75', { scroll_depth: 75 });
                            } else if (scrollPercent >= 100) {
                                self.trackEvent('scroll_100', { scroll_depth: 100 });
                            }
                        }
                    }, 100);
                });

                // Track time spent on page
                var startTime = Date.now();
                var timeTracked = false;

                // Track when user leaves or after 30 seconds
                $(window).on('beforeunload', function() {
                    var timeSpent = Math.round((Date.now() - startTime) / 1000);
                    if (!timeTracked) {
                        self.trackEvent('time_spent', {
                            seconds: timeSpent,
                            scroll_depth: maxScroll
                        });
                        timeTracked = true;
                    }
                });

                setTimeout(function() {
                    if (!timeTracked) {
                        var timeSpent = Math.round((Date.now() - startTime) / 1000);
                        self.trackEvent('time_spent', {
                            seconds: timeSpent,
                            scroll_depth: maxScroll
                        });
                        timeTracked = true;
                    }
                }, 30000); // Track after 30 seconds
            }
        },

        /**
         * Track wishlist/favorite add
         */
        trackWishlistAdd: function() {
            var self = this;

            $(document).on('click', '.add-to-wishlist, .wishlist-button, .favorite-button, [data-action="wishlist"]', function(e) {
                var $btn = $(this);
                var action = $btn.hasClass('active') || $btn.hasClass('added') ? 'remove' : 'add';

                if (action === 'add') {
                    self.trackEvent('wishlist_add', {
                        button_text: $btn.text().trim(),
                        button_class: $btn.attr('class')
                    });
                }
            });
        },

        /**
         * Track contact button clicks
         */
        trackContactClick: function() {
            var self = this;

            $(document).on('click', '.contact-button, .contact-organizer, [href*="contact"], .btn-contact', function(e) {
                self.trackEvent('contact_click', {
                    button_text: $(this).text().trim(),
                    button_type: 'contact'
                });
            });
        },

        /**
         * Track booking/reserve button clicks
         */
        trackBookingClick: function() {
            var self = this;

            $(document).on('click', '.booking-button, .reserve-button, .buy-ticket, .el-book-now, [data-action="book"], .btn-book', function(e) {
                self.trackEvent('booking_click', {
                    button_text: $(this).text().trim(),
                    button_class: $(this).attr('class')
                });
            });

            // Track when booking form is opened
            $(document).on('click', '[data-toggle="booking-form"], .open-booking-form', function(e) {
                self.trackEvent('booking_form_open', {
                    trigger: 'button'
                });
            });
        },

        /**
         * Track share button clicks
         */
        trackShareClick: function() {
            var self = this;

            $(document).on('click', '.share-button, .social-share, [data-action="share"], .share-facebook, .share-twitter, .share-linkedin', function(e) {
                var $btn = $(this);
                var network = 'unknown';

                // Detect social network
                if ($btn.hasClass('share-facebook') || $btn.attr('href').indexOf('facebook') > -1) {
                    network = 'facebook';
                } else if ($btn.hasClass('share-twitter') || $btn.attr('href').indexOf('twitter') > -1) {
                    network = 'twitter';
                } else if ($btn.hasClass('share-linkedin') || $btn.attr('href').indexOf('linkedin') > -1) {
                    network = 'linkedin';
                } else if ($btn.hasClass('share-whatsapp') || $btn.attr('href').indexOf('whatsapp') > -1) {
                    network = 'whatsapp';
                }

                self.trackEvent('share_click', {
                    network: network,
                    button_text: $btn.text().trim()
                });
            });
        },

        /**
         * Track phone click
         */
        trackPhoneClick: function() {
            var self = this;

            $(document).on('click', 'a[href^="tel:"], .phone-link', function(e) {
                var phone = $(this).attr('href').replace('tel:', '');
                self.trackEvent('phone_click', {
                    phone: phone
                });
            });
        },

        /**
         * Track email click
         */
        trackEmailClick: function() {
            var self = this;

            $(document).on('click', 'a[href^="mailto:"], .email-link', function(e) {
                var email = $(this).attr('href').replace('mailto:', '').split('?')[0];
                self.trackEvent('email_click', {
                    email: email
                });
            });
        },

        /**
         * Track website click
         */
        trackWebsiteClick: function() {
            var self = this;

            $(document).on('click', '.website-link, .external-link, [data-action="visit-website"]', function(e) {
                var url = $(this).attr('href');
                self.trackEvent('website_click', {
                    url: url
                });
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        EL_Analytics.init();
    });

})(jQuery);
