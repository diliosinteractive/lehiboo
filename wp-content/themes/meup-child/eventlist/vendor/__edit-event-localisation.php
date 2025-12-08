<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Localisation de l'événement
 * Design selon maquette avec OSM (Leaflet)
 * Autocomplétion adresse avec Nominatim
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;
$user_id = get_current_user_id();

// Récupérer les données existantes
$venue           = get_post_meta( $post_id, $_prefix.'venue', true) ? get_post_meta( $post_id, $_prefix.'venue', true) : '';
$address         = get_post_meta( $post_id, $_prefix.'address', true) ? get_post_meta( $post_id, $_prefix.'address', true) : '';
$map_name        = get_post_meta( $post_id, $_prefix.'map_name', true) ? get_post_meta( $post_id, $_prefix.'map_name', true) : '';
$map_address     = get_post_meta( $post_id, $_prefix.'map_address', true) ? get_post_meta( $post_id, $_prefix.'map_address', true) : '';
$address_source  = get_post_meta( $post_id, $_prefix.'address_source', true) ? get_post_meta( $post_id, $_prefix.'address_source', true) : 'entity';
$coorg_entity_id = get_post_meta( $post_id, $_prefix.'coorg_entity_id', true) ? get_post_meta( $post_id, $_prefix.'coorg_entity_id', true) : '';

// Coordonnées par défaut (Paris)
if ( $post_id !== '' ) {
    $map_lat = get_post_meta( $post_id, $_prefix.'map_lat', true) ? get_post_meta( $post_id, $_prefix.'map_lat', true) : '';
    $map_lng = get_post_meta( $post_id, $_prefix.'map_lng', true) ? get_post_meta( $post_id, $_prefix.'map_lng', true) : '';
} else {
    $EL_Setting_Event = EL()->options->event;
    $map_lat = $EL_Setting_Event->get('latitude_map_default') != '' ? $EL_Setting_Event->get('latitude_map_default') : '48.8566';
    $map_lng = $EL_Setting_Event->get('longitude_map_default') != '' ? $EL_Setting_Event->get('longitude_map_default') : '2.3522';
}

// Type d'événement
$event_type = get_post_meta( $post_id, $_prefix.'event_type', true) ? get_post_meta( $post_id, $_prefix.'event_type', true) : 'classic';

// Récupérer l'adresse de l'entité courante (profil utilisateur)
$user_address = get_user_meta( $user_id, 'user_address', true ) ? get_user_meta( $user_id, 'user_address', true ) : '';
$user_venue   = get_user_meta( $user_id, 'organisation_name', true ) ? get_user_meta( $user_id, 'organisation_name', true ) : get_the_author_meta('display_name', $user_id);
$user_lat     = get_user_meta( $user_id, 'user_lat', true ) ? get_user_meta( $user_id, 'user_lat', true ) : $map_lat;
$user_lng     = get_user_meta( $user_id, 'user_lng', true ) ? get_user_meta( $user_id, 'user_lng', true ) : $map_lng;

// Récupérer les partenaires acceptés
$partners = array();
if ( class_exists('EL_Partnership') ) {
    EL_Partnership::init();
    $partnerships = EL_Partnership::get_for_organisation( $user_id, 'acceptee' );

    foreach ( $partnerships as $partnership ) {
        // Déterminer l'ID du partenaire
        $partner_id = ($partnership->organisation_principale_id == $user_id)
            ? $partnership->organisation_invitee_id
            : $partnership->organisation_principale_id;

        if ( $partner_id ) {
            $partner_name = get_user_meta( $partner_id, 'organisation_name', true );
            if ( !$partner_name ) {
                $partner_name = get_the_author_meta('display_name', $partner_id);
            }
            $partner_address = get_user_meta( $partner_id, 'user_address', true );
            $partner_venue   = get_user_meta( $partner_id, 'organisation_venue_name', true );
            $partner_lat     = get_user_meta( $partner_id, 'user_lat', true );
            $partner_lng     = get_user_meta( $partner_id, 'user_lng', true );

            $partners[] = array(
                'id'      => $partner_id,
                'name'    => $partner_name,
                'address' => $partner_address,
                'venue'   => $partner_venue ? $partner_venue : $partner_name,
                'lat'     => $partner_lat,
                'lng'     => $partner_lng,
            );
        }
    }
}

// URL événement en ligne
$event_online_url = get_post_meta( $post_id, $_prefix.'event_online_url', true);
$event_online_notes = get_post_meta( $post_id, $_prefix.'event_online_notes', true);

// Valeur actuelle du venue
$current_venue = is_array($venue) ? (isset($venue[0]) ? $venue[0] : '') : $venue;
?>

<div class="event_basic_block localisation_section">
    <h4 class="heading_section"><?php esc_html_e( 'Localisation', 'eventlist' ); ?></h4>
    <p class="field_description">
        <?php esc_html_e( 'Sélectionnez le lieu où se déroule l\'activité', 'eventlist' ); ?>
    </p>

    <!-- Type d'événement: Physique ou En ligne -->
    <div class="vendor_field location_type_field">
        <label class="field_label"><?php esc_html_e( 'L\'événement se déroule', 'eventlist' ); ?></label>
        <div class="location_type_options">
            <label class="location_type_option <?php echo ($event_type == 'classic') ? 'active' : ''; ?>" for="event_type_physical">
                <input type="radio"
                       value="classic"
                       name="<?php echo esc_attr($_prefix.'event_type'); ?>"
                       id="event_type_physical"
                       class="event_type_radio"
                       <?php checked($event_type, 'classic'); ?>>
                <span class="option_checkbox"></span>
                <span class="option_label"><?php esc_html_e( 'Dans un lieu physique', 'eventlist' ); ?></span>
            </label>

            <label class="location_type_option <?php echo ($event_type == 'online') ? 'active' : ''; ?>" for="event_type_online">
                <input type="radio"
                       value="online"
                       name="<?php echo esc_attr($_prefix.'event_type'); ?>"
                       id="event_type_online"
                       class="event_type_radio"
                       <?php checked($event_type, 'online'); ?>>
                <span class="option_checkbox"></span>
                <span class="option_label"><?php esc_html_e( 'En ligne', 'eventlist' ); ?></span>
            </label>
        </div>
    </div>

    <!-- Section Lieu Physique -->
    <div class="physical_location_section" style="<?php echo ($event_type == 'classic') ? 'display: block;' : 'display: none;'; ?>">

        <!-- Source de l'adresse -->
        <div class="vendor_field address_source_field">
            <label class="field_label"><?php esc_html_e( 'Veuillez choisir la source de l\'adresse pour cette localisation :', 'eventlist' ); ?></label>

            <div class="address_source_options">
                <!-- Mon entité -->
                <div class="address_source_row">
                    <label class="address_source_option" for="address_source_entity">
                        <input type="radio"
                               value="entity"
                               name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                               id="address_source_entity"
                               class="address_source_radio"
                               data-venue="<?php echo esc_attr($user_venue); ?>"
                               data-address="<?php echo esc_attr($user_address); ?>"
                               data-lat="<?php echo esc_attr($user_lat); ?>"
                               data-lng="<?php echo esc_attr($user_lng); ?>"
                               <?php checked($address_source, 'entity'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Mon entité', 'eventlist' ); ?></span>
                    </label>
                </div>

                <!-- Une entité co-organisatrice -->
                <?php if ( !empty($partners) ) : ?>
                <div class="address_source_row with_select">
                    <label class="address_source_option" for="address_source_coorg">
                        <input type="radio"
                               value="coorg"
                               name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                               id="address_source_coorg"
                               class="address_source_radio"
                               <?php checked($address_source, 'coorg'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Une entité co-organisatrice', 'eventlist' ); ?></span>
                    </label>
                    <div class="coorg_select_wrapper">
                        <select name="<?php echo esc_attr($_prefix.'coorg_entity_id'); ?>"
                                id="coorg_entity_select"
                                class="coorg_entity_select">
                            <option value=""><?php esc_html_e( 'Sélectionnez...', 'eventlist' ); ?></option>
                            <?php foreach ( $partners as $partner ) : ?>
                                <option value="<?php echo esc_attr($partner['id']); ?>"
                                        data-venue="<?php echo esc_attr($partner['venue']); ?>"
                                        data-address="<?php echo esc_attr($partner['address']); ?>"
                                        data-lat="<?php echo esc_attr($partner['lat']); ?>"
                                        data-lng="<?php echo esc_attr($partner['lng']); ?>"
                                        <?php selected($coorg_entity_id, $partner['id']); ?>>
                                    <?php echo esc_html($partner['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Nouvelle adresse -->
                <div class="address_source_row">
                    <label class="address_source_option" for="address_source_new">
                        <input type="radio"
                               value="new"
                               name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                               id="address_source_new"
                               class="address_source_radio"
                               <?php checked($address_source, 'new'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Nouvelle adresse', 'eventlist' ); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Champs d'adresse et carte -->
        <div class="location_fields_wrapper">
            <div class="location_fields_left">
                <!-- Nom du lieu - Input texte libre -->
                <div class="vendor_field">
                    <label for="location_venue_name"><?php esc_html_e( 'Nom du lieu', 'eventlist' ); ?></label>
                    <input type="text"
                           name="<?php echo esc_attr($_prefix.'add_venue'); ?>"
                           id="location_venue_name"
                           class="location_venue_input"
                           value="<?php echo esc_attr($current_venue); ?>"
                           placeholder="<?php esc_attr_e( 'Ex: Salle des fêtes, Mairie, etc.', 'eventlist' ); ?>">
                </div>

                <!-- Adresse - Input avec autocomplétion -->
                <div class="vendor_field address_autocomplete_wrapper">
                    <label for="location_address"><?php esc_html_e( 'Adresse', 'eventlist' ); ?></label>
                    <div class="address_input_container">
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'address'); ?>"
                               id="location_address"
                               class="location_address_input"
                               value="<?php echo esc_attr($address); ?>"
                               placeholder="<?php esc_attr_e( 'Tapez une adresse...', 'eventlist' ); ?>"
                               autocomplete="off">
                        <div class="address_suggestions" id="address_suggestions"></div>
                    </div>
                </div>

                <!-- Latitude -->
                <div class="vendor_field">
                    <label for="map_lat"><?php esc_html_e( 'Latitude', 'eventlist' ); ?></label>
                    <input type="text"
                           name="<?php echo esc_attr($_prefix.'map_lat'); ?>"
                           id="map_lat"
                           value="<?php echo esc_attr($map_lat); ?>"
                           class="location_lat_input"
                           placeholder="48.8566"
                           readonly>
                </div>

                <!-- Longitude -->
                <div class="vendor_field">
                    <label for="map_lng"><?php esc_html_e( 'Longitude', 'eventlist' ); ?></label>
                    <input type="text"
                           name="<?php echo esc_attr($_prefix.'map_lng'); ?>"
                           id="map_lng"
                           value="<?php echo esc_attr($map_lng); ?>"
                           class="location_lng_input"
                           placeholder="2.3522"
                           readonly>
                </div>

                <!-- Champs cachés -->
                <input type="hidden" name="<?php echo esc_attr($_prefix.'map_name'); ?>" id="map_name" value="<?php echo esc_attr($map_name); ?>">
                <input type="hidden" name="<?php echo esc_attr($_prefix.'map_address'); ?>" id="map_address" value="<?php echo esc_attr($map_address); ?>">
            </div>

            <div class="location_fields_right">
                <!-- Carte OSM -->
                <div class="location_map_container">
                    <div id="location_osm_map" class="osm_map"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Section En ligne -->
    <div class="online_location_section" style="<?php echo ($event_type == 'online') ? 'display: block;' : 'display: none;'; ?>">
        <div class="vendor_field">
            <label for="event_online_url"><?php esc_html_e( 'Lien de l\'événement (URL)', 'eventlist' ); ?> <span class="el_req">*</span></label>
            <p class="field_hint"><?php esc_html_e( 'Indiquez le lien de connexion pour votre événement en ligne (Zoom, Teams, Google Meet, etc.)', 'eventlist' ); ?></p>
            <input type="url"
                   name="<?php echo esc_attr($_prefix.'event_online_url'); ?>"
                   id="event_online_url"
                   value="<?php echo esc_url($event_online_url); ?>"
                   placeholder="https://zoom.us/j/...">
        </div>

        <div class="vendor_field">
            <label for="event_online_notes"><?php esc_html_e( 'Instructions de connexion', 'eventlist' ); ?></label>
            <p class="field_hint"><?php esc_html_e( 'Informations complémentaires pour rejoindre l\'événement (mot de passe, instructions...)', 'eventlist' ); ?></p>
            <textarea name="<?php echo esc_attr($_prefix.'event_online_notes'); ?>"
                      id="event_online_notes"
                      rows="4"
                      placeholder="<?php esc_attr_e( 'Ex: Le lien de connexion vous sera envoyé par email la veille de l\'événement.', 'eventlist' ); ?>"><?php echo esc_textarea($event_online_notes); ?></textarea>
        </div>
    </div>

</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
(function($) {
    'use strict';

    var LocationManager = {
        map: null,
        marker: null,
        defaultLat: <?php echo $map_lat ? floatval($map_lat) : '48.8566'; ?>,
        defaultLng: <?php echo $map_lng ? floatval($map_lng) : '2.3522'; ?>,
        searchTimeout: null,
        isFieldsReadonly: true,

        init: function() {
            this.initMap();
            this.initAddressAutocomplete();
            this.bindEvents();
            this.handleAddressSource();
        },

        initMap: function() {
            var self = this;
            var lat = parseFloat($('#map_lat').val()) || this.defaultLat;
            var lng = parseFloat($('#map_lng').val()) || this.defaultLng;

            // Initialiser la carte
            this.map = L.map('location_osm_map').setView([lat, lng], 15);

            // Ajouter les tuiles OSM
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);

            // Ajouter le marqueur
            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);

            // Événement de drag du marqueur
            this.marker.on('dragend', function(e) {
                var pos = e.target.getLatLng();
                self.updateCoordinates(pos.lat, pos.lng);
                self.reverseGeocode(pos.lat, pos.lng);
            });

            // Clic sur la carte pour déplacer le marqueur
            this.map.on('click', function(e) {
                self.marker.setLatLng(e.latlng);
                self.updateCoordinates(e.latlng.lat, e.latlng.lng);
                self.reverseGeocode(e.latlng.lat, e.latlng.lng);
            });
        },

        initAddressAutocomplete: function() {
            var self = this;
            var $input = $('#location_address');
            var $suggestions = $('#address_suggestions');

            // Recherche lors de la saisie
            $input.on('input', function() {
                var query = $(this).val().trim();

                // Effacer le timeout précédent
                if (self.searchTimeout) {
                    clearTimeout(self.searchTimeout);
                }

                // Cacher les suggestions si moins de 3 caractères
                if (query.length < 3) {
                    $suggestions.hide().empty();
                    return;
                }

                // Attendre 300ms avant de rechercher
                self.searchTimeout = setTimeout(function() {
                    self.searchAddress(query);
                }, 300);
            });

            // Cacher les suggestions quand on clique ailleurs
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.address_autocomplete_wrapper').length) {
                    $suggestions.hide();
                }
            });

            // Focus sur l'input pour réafficher les suggestions
            $input.on('focus', function() {
                if ($suggestions.children().length > 0) {
                    $suggestions.show();
                }
            });
        },

        searchAddress: function(query) {
            var self = this;
            var $suggestions = $('#address_suggestions');

            $.ajax({
                url: 'https://nominatim.openstreetmap.org/search',
                dataType: 'json',
                data: {
                    q: query,
                    format: 'json',
                    addressdetails: 1,
                    limit: 8,
                    countrycodes: 'fr',
                    'accept-language': 'fr'
                },
                beforeSend: function() {
                    $suggestions.html('<div class="suggestion_loading"><i class="fa fa-spinner fa-spin"></i> Recherche...</div>').show();
                },
                success: function(data) {
                    $suggestions.empty();

                    if (data.length === 0) {
                        $suggestions.html('<div class="suggestion_empty">Aucune adresse trouvée</div>').show();
                        return;
                    }

                    data.forEach(function(item) {
                        var $item = $('<div class="suggestion_item"></div>')
                            .attr('data-lat', item.lat)
                            .attr('data-lng', item.lon)
                            .attr('data-address', item.display_name)
                            .html('<i class="fa fa-map-marker-alt"></i><span>' + item.display_name + '</span>');

                        $item.on('click', function() {
                            self.selectAddress($(this));
                        });

                        $suggestions.append($item);
                    });

                    $suggestions.show();
                },
                error: function() {
                    $suggestions.html('<div class="suggestion_empty">Erreur de recherche</div>').show();
                }
            });
        },

        selectAddress: function($item) {
            var address = $item.data('address');
            var lat = parseFloat($item.data('lat'));
            var lng = parseFloat($item.data('lng'));

            // Mettre à jour l'input
            $('#location_address').val(address);
            $('#address_suggestions').hide();

            // Mettre à jour les coordonnées et la carte
            this.updateCoordinates(lat, lng);
            this.updateMapPosition(lat, lng);
        },

        bindEvents: function() {
            var self = this;

            // Changement de type d'événement
            $('.event_type_radio').on('change', function() {
                var value = $(this).val();

                $('.location_type_option').removeClass('active');
                $(this).closest('.location_type_option').addClass('active');

                if (value === 'classic') {
                    $('.physical_location_section').slideDown(200);
                    $('.online_location_section').slideUp(200);
                    setTimeout(function() {
                        self.map.invalidateSize();
                    }, 250);
                } else {
                    $('.physical_location_section').slideUp(200);
                    $('.online_location_section').slideDown(200);
                }
            });

            // Changement de source d'adresse
            $('.address_source_radio').on('change', function() {
                self.handleAddressSource();
            });

            // Changement d'entité co-organisatrice
            $('#coorg_entity_select').on('change', function() {
                var $selected = $(this).find(':selected');
                if ($selected.val()) {
                    var venue = $selected.data('venue');
                    var address = $selected.data('address');
                    var lat = $selected.data('lat');
                    var lng = $selected.data('lng');

                    self.fillLocationFields(venue, address, lat, lng, true);
                }
            });
        },

        handleAddressSource: function() {
            var $checked = $('.address_source_radio:checked');
            var source = $checked.val();
            var self = this;

            // Mise à jour visuelle
            $('.address_source_option').removeClass('active');
            $checked.closest('.address_source_option').addClass('active');

            // Activer/désactiver le select co-org
            if (source === 'coorg') {
                $('#coorg_entity_select').prop('disabled', false);
                var $selected = $('#coorg_entity_select').find(':selected');
                if ($selected.val()) {
                    this.fillLocationFields(
                        $selected.data('venue'),
                        $selected.data('address'),
                        $selected.data('lat'),
                        $selected.data('lng'),
                        true
                    );
                }
                this.setFieldsReadonly(true);
            } else {
                $('#coorg_entity_select').prop('disabled', true);
            }

            // Remplir avec les données de l'entité
            if (source === 'entity') {
                var venue = $checked.data('venue');
                var address = $checked.data('address');
                var lat = $checked.data('lat');
                var lng = $checked.data('lng');
                this.fillLocationFields(venue, address, lat, lng, true);
                this.setFieldsReadonly(true);
            }

            // Nouvelle adresse - vider les champs et les rendre éditables
            if (source === 'new') {
                this.clearLocationFields();
                this.setFieldsReadonly(false);
            }
        },

        setFieldsReadonly: function(readonly) {
            this.isFieldsReadonly = readonly;
            if (readonly) {
                $('#location_venue_name, #location_address').prop('readonly', true).addClass('readonly');
            } else {
                $('#location_venue_name, #location_address').prop('readonly', false).removeClass('readonly');
            }
        },

        fillLocationFields: function(venue, address, lat, lng, updateMap) {
            // Nom du lieu
            if (venue) {
                $('#location_venue_name').val(venue);
            }

            // Adresse
            if (address) {
                $('#location_address').val(address);
            }

            // Coordonnées et carte
            if (lat && lng) {
                this.updateCoordinates(lat, lng);
                if (updateMap) {
                    this.updateMapPosition(parseFloat(lat), parseFloat(lng));
                }
            }
        },

        clearLocationFields: function() {
            $('#location_venue_name').val('');
            $('#location_address').val('');
            // Garder les coordonnées par défaut pour la carte
        },

        updateCoordinates: function(lat, lng) {
            $('#map_lat').val(parseFloat(lat).toFixed(6));
            $('#map_lng').val(parseFloat(lng).toFixed(6));
        },

        updateMapPosition: function(lat, lng) {
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], 15);
        },

        reverseGeocode: function(lat, lng) {
            var self = this;

            // Ne pas faire de reverse geocode si les champs sont en lecture seule
            if (this.isFieldsReadonly) {
                return;
            }

            $.ajax({
                url: 'https://nominatim.openstreetmap.org/reverse',
                dataType: 'json',
                data: {
                    lat: lat,
                    lon: lng,
                    format: 'json',
                    'accept-language': 'fr'
                },
                success: function(data) {
                    if (data && data.display_name) {
                        $('#location_address').val(data.display_name);
                    }
                }
            });
        }
    };

    // Initialiser au chargement
    $(document).ready(function() {
        if ($('#location_osm_map').length) {
            LocationManager.init();
        }
    });

})(jQuery);
</script>

<style>
/* Autocomplétion adresse */
.address_autocomplete_wrapper {
    position: relative;
}

.address_input_container {
    position: relative;
}

.address_suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.suggestion_item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.15s;
    border-bottom: 1px solid #f0f0f0;
}

.suggestion_item:last-child {
    border-bottom: none;
}

.suggestion_item:hover {
    background: #f8f8f8;
}

.suggestion_item i {
    color: #FF6600;
    font-size: 14px;
    margin-top: 3px;
    flex-shrink: 0;
}

.suggestion_item span {
    font-size: 14px;
    color: #333;
    line-height: 1.4;
}

.suggestion_loading,
.suggestion_empty {
    padding: 16px;
    text-align: center;
    color: #888;
    font-size: 14px;
}

.suggestion_loading i {
    margin-right: 8px;
    color: #FF6600;
}

/* Champs en lecture seule */
.location_venue_input.readonly,
.location_address_input.readonly {
    background-color: #f9f9f9;
    color: #666;
    cursor: not-allowed;
}

/* Lat/Lng en lecture seule toujours */
.location_lat_input,
.location_lng_input {
    background-color: #f9f9f9 !important;
    color: #666 !important;
}
</style>
