/**
 * Event Location & Ticketing Enhanced Features
 * Gère les interactions pour les améliorations de localisation et billetterie
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        /**
         * ========================================
         * SECTION LOCALISATION
         * ========================================
         */

        // Gestion de l'affichage des sections selon le type d'événement sélectionné
        $('.event_type_radio').on('change', function () {
            const selectedType = $(this).val();

            // Masquer toutes les sections
            $('.physical_location_section, .home_location_section').hide();

            // Afficher la section appropriée
            if (selectedType === 'classic') {
                $('.physical_location_section').show();
            } else if (selectedType === 'home') {
                $('.home_location_section').show();
            }
        });

        // Gestion de la checkbox "Afficher dans toutes les villes"
        $('#show_all_cities').on('change', function () {
            if ($(this).is(':checked')) {
                $('.city_selector').hide();
            } else {
                $('.city_selector').show();
            }
        });

        // Gestion des images pour le stationnement
        $(document).on('click', '.el_add_parking_image', function (e) {
            e.preventDefault();
            const button = $(this);
            const imageWrap = button.prev('.image-wrap-parking');

            const mediaUploader = wp.media({
                title: 'Choisir une image de stationnement',
                button: {
                    text: 'Utiliser cette image'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                imageWrap.html(`
                    <div class="item">
                        <img src="${attachment.url}" class="image" />
                        <input type="hidden" name="${button.data('prefix')}venue_parking_image" value="${attachment.id}"/>
                    </div>
                    <a href="#" class="el_remove_parking_image">
                        <span class="dashicons dashicons-no"></span>
                    </a>
                `);
            });

            mediaUploader.open();
        });

        // Suppression de l'image de stationnement
        $(document).on('click', '.el_remove_parking_image', function (e) {
            e.preventDefault();
            $(this).siblings('.item').remove();
            $(this).remove();
        });

        // Gestion des images pour l'accès
        $(document).on('click', '.el_add_access_image', function (e) {
            e.preventDefault();
            const button = $(this);
            const imageWrap = button.prev('.image-wrap-access');

            const mediaUploader = wp.media({
                title: 'Choisir une image d\'accès',
                button: {
                    text: 'Utiliser cette image'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                imageWrap.html(`
                    <div class="item">
                        <img src="${attachment.url}" class="image" />
                        <input type="hidden" name="${button.data('prefix')}venue_access_image" value="${attachment.id}"/>
                    </div>
                    <a href="#" class="el_remove_access_image">
                        <span class="dashicons dashicons-no"></span>
                    </a>
                `);
            });

            mediaUploader.open();
        });

        // Suppression de l'image d'accès
        $(document).on('click', '.el_remove_access_image', function (e) {
            e.preventDefault();
            $(this).siblings('.item').remove();
            $(this).remove();
        });

        /**
         * ========================================
         * SECTION BILLETTERIE
         * ========================================
         */

        // Gestion de l'affichage des sections de billetterie
        $('input[name*="ticket_link"]').on('change', function () {
            const selectedValue = $(this).val();

            // Masquer toutes les sections
            $('.ticket_internal_link, .ticket_external_link').hide();

            // Afficher la section appropriée
            if (selectedValue === 'ticket_internal_link') {
                $('.ticket_internal_link').show();
            } else if (selectedValue === 'ticket_external_link') {
                $('.ticket_external_link').show();
            }
        });

        // Déclenchement initial pour afficher la bonne section
        $('input[name*="ticket_link"]:checked').trigger('change');

        // Ajout d'un nouveau tarif externe
        $('.add_external_price').on('click', function (e) {
            e.preventDefault();

            const pricesList = $('.external_prices_list');
            const prefix = pricesList.data('prefix');
            const newIndex = pricesList.find('.external_price_item').length;

            const newPriceItem = `
                <div class="external_price_item">
                    <input
                        type="text"
                        name="${prefix}ticket_external_prices[${newIndex}][name]"
                        placeholder="Nom du tarif (ex: Tarif Adulte)"
                        class="price_name_input"
                    />
                    <input
                        type="text"
                        name="${prefix}ticket_external_prices[${newIndex}][price]"
                        placeholder="Prix (en euros)"
                        class="price_amount_input"
                    />
                    <span class="currency_symbol">€</span>
                    <button type="button" class="button remove_external_price">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
            `;

            pricesList.append(newPriceItem);
        });

        // Suppression d'un tarif externe
        $(document).on('click', '.remove_external_price', function (e) {
            e.preventDefault();
            $(this).closest('.external_price_item').remove();
        });

        // Mise à jour dynamique des styles pour le bouton de choix de billetterie
        $('.el_btn_ticket_choice input[type="radio"]').on('change', function () {
            $('.el_btn_ticket_choice').removeClass('active');
            if ($(this).is(':checked')) {
                $(this).closest('.el_btn_ticket_choice').addClass('active');
            }
        });

        // Initialisation : activer le bouton sélectionné
        $('.el_btn_ticket_choice input[type="radio"]:checked').each(function () {
            $(this).closest('.el_btn_ticket_choice').addClass('active');
        });

        /**
         * ========================================
         * VALIDATION & HELPERS
         * ========================================
         */

        // Validation du format des prix (seulement des chiffres et virgule/point)
        $(document).on('input', '.price_amount_input, .price_ticket', function () {
            let value = $(this).val();
            // Remplacer les virgules par des points
            value = value.replace(',', '.');
            // Supprimer tout ce qui n'est pas un chiffre ou un point
            value = value.replace(/[^0-9.]/g, '');
            // S'assurer qu'il n'y a qu'un seul point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            $(this).val(value);
        });

        // Helper: Affichage d'un message de succès
        function showSuccessMessage(message) {
            const successDiv = $('<div class="el-success-message"></div>').text(message);
            $('.vendor_edit_event').prepend(successDiv);
            setTimeout(function () {
                successDiv.fadeOut(function () {
                    $(this).remove();
                });
            }, 3000);
        }

        // Helper: Affichage d'un message d'erreur
        function showErrorMessage(message) {
            const errorDiv = $('<div class="el-error-message"></div>').text(message);
            $('.vendor_edit_event').prepend(errorDiv);
            setTimeout(function () {
                errorDiv.fadeOut(function () {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * ========================================
         * ÉVÉNEMENTS AU CHARGEMENT
         * ========================================
         */

        // Initialiser l'état correct des sections au chargement
        $('.event_type_radio:checked').trigger('change');

        // Déclencher les changements initiaux
        if ($('#show_all_cities').is(':checked')) {
            $('.city_selector').hide();
        }
    });

})(jQuery);
