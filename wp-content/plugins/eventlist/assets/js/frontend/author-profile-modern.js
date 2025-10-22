/**
 * V1 Le Hiboo - Modern Author Profile JavaScript
 * Gestion des interactions de la page de profil organisateur moderne
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Toggle Contact Form
        $('.btn_send_message, .btn_contact').on('click', function(e) {
            e.preventDefault();

            const $formWrapper = $('.contact_form_wrapper');
            const $button = $(this);

            if ($formWrapper.is(':visible')) {
                $formWrapper.slideUp(300);
                $button.find('i').removeClass('fa-times').addClass('icon_mail_alt');
            } else {
                $formWrapper.slideDown(300);
                $button.find('i').removeClass('icon_mail_alt').addClass('fa-times');

                // Scroll to form
                $('html, body').animate({
                    scrollTop: $formWrapper.offset().top - 100
                }, 500);
            }
        });

        // Share Profile Button
        $('.btn_share').on('click', function(e) {
            e.preventDefault();

            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                }).then(() => {
                    console.log('Profile shared successfully');
                }).catch((error) => {
                    console.log('Error sharing:', error);
                    fallbackCopyToClipboard();
                });
            } else {
                fallbackCopyToClipboard();
            }
        });

        // Fallback copy to clipboard
        function fallbackCopyToClipboard() {
            const url = window.location.href;
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();
            document.execCommand('copy');
            $temp.remove();

            // Show notification
            showNotification('Link copied to clipboard!', 'success');
        }

        // Show notification helper
        function showNotification(message, type) {
            const $notification = $('<div>')
                .addClass('profile-notification')
                .addClass(type)
                .text(message)
                .css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': type === 'success' ? '#4caf50' : '#f44336',
                    'color': '#fff',
                    'padding': '15px 20px',
                    'border-radius': '8px',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                    'z-index': '9999',
                    'animation': 'slideInRight 0.3s ease'
                });

            $('body').append($notification);

            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });

        // Stats animation on scroll
        const animateStats = function() {
            $('.stat_value').each(function() {
                const $this = $(this);
                const elementTop = $this.offset().top;
                const elementBottom = elementTop + $this.outerHeight();
                const viewportTop = $(window).scrollTop();
                const viewportBottom = viewportTop + $(window).height();

                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    if (!$this.hasClass('animated')) {
                        $this.addClass('animated');
                        const countTo = parseInt($this.text());
                        $({ countNum: 0 }).animate(
                            { countNum: countTo },
                            {
                                duration: 1500,
                                easing: 'swing',
                                step: function() {
                                    $this.text(Math.floor(this.countNum));
                                },
                                complete: function() {
                                    $this.text(this.countNum);
                                }
                            }
                        );
                    }
                }
            });
        };

        // Trigger stats animation
        $(window).on('scroll', animateStats);
        animateStats(); // Run on page load

        // Form validation styling
        $('.modern-form .input-field').on('blur', function() {
            const $field = $(this);
            const fieldName = $field.attr('name');
            const fieldValue = $field.val().trim();

            if ($field.prop('required') && fieldValue === '') {
                $field.addClass('field-error');
            } else {
                $field.removeClass('field-error');

                // Specific validations
                if (fieldName === 'email_customer' && fieldValue !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        $field.addClass('field-error');
                    }
                }
            }
        });

        // Remove error on focus
        $('.modern-form .input-field').on('focus', function() {
            $(this).removeClass('field-error');
        });

        // Add CSS for field errors dynamically
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .field-error {
                    border-color: #f44336 !important;
                    box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1) !important;
                }
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                .stat_value {
                    transition: all 0.3s ease;
                }
            `)
            .appendTo('head');

    });

})(jQuery);
