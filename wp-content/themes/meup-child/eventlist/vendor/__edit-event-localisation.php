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

// Récupérer le nom public de l'entité courante
if ( class_exists('EL_Coorg_Helpers') ) {
    $user_public_name = EL_Coorg_Helpers::get_organisation_name( $user_id );
} else {
    $org_display_name = get_user_meta( $user_id, 'org_display_name', true );
    $org_name = get_user_meta( $user_id, 'org_name', true );
    $user_public_name = !empty($org_display_name) ? $org_display_name : (!empty($org_name) ? $org_name : get_the_author_meta('display_name', $user_id));
}

// Adresse complète de l'entité
$user_address_line1 = get_user_meta( $user_id, 'user_address_line1', true );
$user_city = get_user_meta( $user_id, 'user_city', true );
$user_postcode = get_user_meta( $user_id, 'user_postcode', true );
$user_address_parts = array_filter([$user_address_line1, $user_postcode, $user_city]);
$user_address = !empty($user_address_parts) ? implode(', ', $user_address_parts) : get_user_meta( $user_id, 'user_address', true );

$user_lat = get_user_meta( $user_id, 'user_lat', true ) ? get_user_meta( $user_id, 'user_lat', true ) : $map_lat;
$user_lng = get_user_meta( $user_id, 'user_lng', true ) ? get_user_meta( $user_id, 'user_lng', true ) : $map_lng;

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
            // Nom public du partenaire
            if ( class_exists('EL_Coorg_Helpers') ) {
                $partner_name = EL_Coorg_Helpers::get_organisation_name( $partner_id );
            } else {
                $p_display = get_user_meta( $partner_id, 'org_display_name', true );
                $p_org = get_user_meta( $partner_id, 'org_name', true );
                $partner_name = !empty($p_display) ? $p_display : (!empty($p_org) ? $p_org : get_the_author_meta('display_name', $partner_id));
            }

            // Adresse complète du partenaire
            $p_address_line1 = get_user_meta( $partner_id, 'user_address_line1', true );
            $p_city = get_user_meta( $partner_id, 'user_city', true );
            $p_postcode = get_user_meta( $partner_id, 'user_postcode', true );
            $p_address_parts = array_filter([$p_address_line1, $p_postcode, $p_city]);
            $partner_address = !empty($p_address_parts) ? implode(', ', $p_address_parts) : get_user_meta( $partner_id, 'user_address', true );

            $partner_venue = get_user_meta( $partner_id, 'organisation_venue_name', true );
            $partner_lat = get_user_meta( $partner_id, 'user_lat', true );
            $partner_lng = get_user_meta( $partner_id, 'user_lng', true );

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

// Champs supplémentaires pour lieu physique
$event_location_type = get_post_meta( $post_id, $_prefix.'event_location_type', true);
$parking_info = get_post_meta( $post_id, $_prefix.'parking_info', true) ?: '';
$parking_image = get_post_meta( $post_id, $_prefix.'parking_image', true) ?: '';
$access_info = get_post_meta( $post_id, $_prefix.'access_info', true) ?: '';
$access_image = get_post_meta( $post_id, $_prefix.'access_image', true) ?: '';
$pmr_accessible = get_post_meta( $post_id, $_prefix.'pmr_accessible', true) ?: '';
$pmr_notes = get_post_meta( $post_id, $_prefix.'pmr_notes', true) ?: '';
$catering_available = get_post_meta( $post_id, $_prefix.'catering_available', true) ?: '';
$catering_notes = get_post_meta( $post_id, $_prefix.'catering_notes', true) ?: '';
$drinks_available = get_post_meta( $post_id, $_prefix.'drinks_available', true) ?: '';
$drinks_notes = get_post_meta( $post_id, $_prefix.'drinks_notes', true) ?: '';

// Taxonomie type événement
$event_types_taxonomy = get_terms(array(
    'taxonomy' => 'event_location_type',
    'hide_empty' => false,
));
if (is_wp_error($event_types_taxonomy)) {
    $event_types_taxonomy = array();
}
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
                    <label class="address_source_option <?php echo ($address_source == 'entity') ? 'active' : ''; ?>" for="address_source_entity">
                        <input type="radio"
                               value="entity"
                               name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                               id="address_source_entity"
                               class="address_source_radio"
                               data-venue="<?php echo esc_attr($user_public_name); ?>"
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
                    <label class="address_source_option <?php echo ($address_source == 'coorg') ? 'active' : ''; ?>" for="address_source_coorg">
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
                                class="coorg_entity_select"
                                <?php echo ($address_source != 'coorg') ? 'disabled' : ''; ?>>
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
                    <label class="address_source_option <?php echo ($address_source == 'new') ? 'active' : ''; ?>" for="address_source_new">
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
                <!-- Adresse de l'activité - Input avec autocomplétion -->
                <div class="vendor_field address_autocomplete_wrapper">
                    <label for="location_address"><?php esc_html_e( 'Adresse de l\'activité', 'eventlist' ); ?></label>
                    <div class="address_input_container">
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'address'); ?>"
                               id="location_address"
                               class="location_address_input <?php echo ($address_source != 'new') ? 'readonly' : ''; ?>"
                               value="<?php echo esc_attr($address); ?>"
                               placeholder="<?php esc_attr_e( 'Tapez une adresse...', 'eventlist' ); ?>"
                               autocomplete="off"
                               <?php echo ($address_source != 'new') ? 'readonly' : ''; ?>>
                        <div class="address_suggestions" id="address_suggestions"></div>
                    </div>
                </div>

                <!-- Coordonnées GPS (indicatif, non modifiable) -->
                <div class="vendor_field">
                    <label for="map_gps"><?php esc_html_e( 'Coordonnées GPS', 'eventlist' ); ?></label>
                    <input type="text"
                           id="map_gps"
                           value="<?php echo ($map_lat && $map_lng) ? esc_attr($map_lat . ', ' . $map_lng) : ''; ?>"
                           class="location_gps_input"
                           placeholder="48.8566, 2.3522"
                           readonly>
                    <!-- Champs cachés pour la sauvegarde -->
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_lat'); ?>" id="map_lat" value="<?php echo esc_attr($map_lat); ?>">
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_lng'); ?>" id="map_lng" value="<?php echo esc_attr($map_lng); ?>">
                </div>

                <!-- Champs cachés -->
                <input type="hidden" name="<?php echo esc_attr($_prefix.'map_name'); ?>" id="map_name" value="<?php echo esc_attr($map_name); ?>">
                <input type="hidden" name="<?php echo esc_attr($_prefix.'map_address'); ?>" id="map_address" value="<?php echo esc_attr($map_address); ?>">
                <!-- Venue storage - le backend attend venue[] -->
                <input type="hidden" name="<?php echo esc_attr($_prefix.'venue[0]'); ?>" id="venue_storage" value="<?php echo esc_attr($current_venue); ?>">
            </div>

            <div class="location_fields_right">
                <!-- Carte OSM -->
                <div class="location_map_container">
                    <div id="location_osm_map" class="osm_map"></div>
                </div>
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="el_separator location_separator">

        <!-- Champs supplémentaires pour lieu physique -->
        <div class="location_extra_fields">

            <!-- Stationnement -->
            <div class="vendor_field location_extra_field location_extra_field_with_image">
                <div class="field_header">
                    <label class="field_label"><?php esc_html_e( 'Stationnement', 'eventlist' ); ?></label>
                    <p class="field_hint"><?php esc_html_e( 'Donnez aux visiteurs véhiculés toutes les informations sur le stationnement, vous pouvez importer une image du parking ou du plan pour se garer', 'eventlist' ); ?></p>
                </div>
                <div class="field_content_row">
                    <div class="field_input_wrapper">
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'parking_info'); ?>"
                               id="parking_info"
                               value="<?php echo esc_attr($parking_info); ?>"
                               placeholder="<?php esc_attr_e( 'Champ texte', 'eventlist' ); ?>">
                    </div>
                    <div class="field_image_wrapper">
                        <input type="hidden" name="<?php echo esc_attr($_prefix.'parking_image'); ?>" id="parking_image" value="<?php echo esc_attr($parking_image); ?>">
                        <button type="button" class="btn_add_location_image" data-target="parking_image">
                            <i class="fa fa-image"></i> <?php esc_html_e( 'Ajouter une image', 'eventlist' ); ?>
                        </button>
                        <?php if ($parking_image) : ?>
                        <div class="location_image_preview" id="parking_image_preview">
                            <img src="<?php echo esc_url(wp_get_attachment_image_url($parking_image, 'thumbnail')); ?>" alt="">
                            <button type="button" class="btn_remove_location_image" data-target="parking_image"><i class="fa fa-times"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Accès & Transports -->
            <div class="vendor_field location_extra_field location_extra_field_with_image">
                <div class="field_header">
                    <label class="field_label"><?php esc_html_e( 'Accès & Transports', 'eventlist' ); ?></label>
                    <p class="field_hint"><?php esc_html_e( 'Donnez aux visiteurs toutes les informations pour accéder au lieu.', 'eventlist' ); ?></p>
                </div>
                <div class="field_content_row">
                    <div class="field_input_wrapper">
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'access_info'); ?>"
                               id="access_info"
                               value="<?php echo esc_attr($access_info); ?>"
                               placeholder="<?php esc_attr_e( 'Champ texte', 'eventlist' ); ?>">
                    </div>
                    <div class="field_image_wrapper">
                        <input type="hidden" name="<?php echo esc_attr($_prefix.'access_image'); ?>" id="access_image" value="<?php echo esc_attr($access_image); ?>">
                        <button type="button" class="btn_add_location_image" data-target="access_image">
                            <i class="fa fa-image"></i> <?php esc_html_e( 'Ajouter une image', 'eventlist' ); ?>
                        </button>
                        <?php if ($access_image) : ?>
                        <div class="location_image_preview" id="access_image_preview">
                            <img src="<?php echo esc_url(wp_get_attachment_image_url($access_image, 'thumbnail')); ?>" alt="">
                            <button type="button" class="btn_remove_location_image" data-target="access_image"><i class="fa fa-times"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Accessibilité PMR -->
            <div class="vendor_field location_extra_field location_checkbox_field">
                <div class="field_header">
                    <label class="field_label"><?php esc_html_e( 'Accessibilité PMR', 'eventlist' ); ?></label>
                    <p class="field_hint"><?php esc_html_e( 'Donnez aux visiteurs des informations pour les Personnes à Mobilité Réduite, et cochez si votre structure permet leur accès.', 'eventlist' ); ?></p>
                </div>
                <div class="field_content_row field_checkbox_row">
                    <label class="location_checkbox_option <?php echo ($pmr_accessible == '1') ? 'active' : ''; ?>" for="pmr_accessible">
                        <input type="checkbox"
                               name="<?php echo esc_attr($_prefix.'pmr_accessible'); ?>"
                               id="pmr_accessible"
                               value="1"
                               <?php checked($pmr_accessible, '1'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Accessible PMR', 'eventlist' ); ?></span>
                    </label>
                    <div class="field_notes_wrapper">
                        <label><?php esc_html_e( 'Notes :', 'eventlist' ); ?></label>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'pmr_notes'); ?>"
                               id="pmr_notes"
                               value="<?php echo esc_attr($pmr_notes); ?>"
                               placeholder="<?php esc_attr_e( 'Champ texte', 'eventlist' ); ?>">
                    </div>
                </div>
            </div>

            <!-- Restauration sur place -->
            <div class="vendor_field location_extra_field location_checkbox_field">
                <div class="field_header">
                    <label class="field_label"><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></label>
                    <p class="field_hint"><?php esc_html_e( 'Donnez aux visiteurs des informations sur la possibilité de se restaurer sur place', 'eventlist' ); ?></p>
                </div>
                <div class="field_content_row field_checkbox_row">
                    <label class="location_checkbox_option <?php echo ($catering_available == '1') ? 'active' : ''; ?>" for="catering_available">
                        <input type="checkbox"
                               name="<?php echo esc_attr($_prefix.'catering_available'); ?>"
                               id="catering_available"
                               value="1"
                               <?php checked($catering_available, '1'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></span>
                    </label>
                    <div class="field_notes_wrapper">
                        <label><?php esc_html_e( 'Notes :', 'eventlist' ); ?></label>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'catering_notes'); ?>"
                               id="catering_notes"
                               value="<?php echo esc_attr($catering_notes); ?>"
                               placeholder="<?php esc_attr_e( 'Champ texte', 'eventlist' ); ?>">
                    </div>
                </div>
            </div>

            <!-- Boisson sur place -->
            <div class="vendor_field location_extra_field location_checkbox_field">
                <div class="field_header">
                    <label class="field_label"><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></label>
                    <p class="field_hint"><?php esc_html_e( 'Donnez aux visiteurs des informations sur la possibilité d\'avoir des rafraîchissements sur place', 'eventlist' ); ?></p>
                </div>
                <div class="field_content_row field_checkbox_row">
                    <label class="location_checkbox_option <?php echo ($drinks_available == '1') ? 'active' : ''; ?>" for="drinks_available">
                        <input type="checkbox"
                               name="<?php echo esc_attr($_prefix.'drinks_available'); ?>"
                               id="drinks_available"
                               value="1"
                               <?php checked($drinks_available, '1'); ?>>
                        <span class="option_checkbox"></span>
                        <span class="option_label"><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></span>
                    </label>
                    <div class="field_notes_wrapper">
                        <label><?php esc_html_e( 'Notes :', 'eventlist' ); ?></label>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'drinks_notes'); ?>"
                               id="drinks_notes"
                               value="<?php echo esc_attr($drinks_notes); ?>"
                               placeholder="<?php esc_attr_e( 'Champ texte', 'eventlist' ); ?>">
                    </div>
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
        isFieldsReadonly: <?php echo ($address_source != 'new') ? 'true' : 'false'; ?>,

        init: function() {
            this.initMap();
            this.initAddressAutocomplete();
            this.bindEvents();
            this.initializeFromSource();
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
                if (self.isFieldsReadonly) return;
                var pos = e.target.getLatLng();
                self.updateCoordinates(pos.lat, pos.lng);
                self.reverseGeocode(pos.lat, pos.lng);
            });

            // Clic sur la carte pour déplacer le marqueur
            this.map.on('click', function(e) {
                if (self.isFieldsReadonly) return;
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
                if (self.isFieldsReadonly) return;

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
                if (!self.isFieldsReadonly && $suggestions.children().length > 0) {
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

            // Mettre à jour le champ caché map_address
            $('#map_address').val(address);
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
                    var address = $selected.data('address') || '';
                    var lat = $selected.data('lat') || '';
                    var lng = $selected.data('lng') || '';

                    self.fillLocationFields(address, lat, lng, true);
                }
            });

            // Checkboxes de lieu
            $('.location_checkbox_option input[type="checkbox"]').on('change', function() {
                var $label = $(this).closest('.location_checkbox_option');
                if ($(this).is(':checked')) {
                    $label.addClass('active');
                } else {
                    $label.removeClass('active');
                }
            });

            // Bouton ajouter image
            $(document).on('click', '.btn_add_location_image', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                self.openMediaPicker(targetId);
            });

            // Bouton supprimer image
            $(document).on('click', '.btn_remove_location_image', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                $('#' + targetId).val('');
                $('#' + targetId + '_preview').remove();
            });

        },

        initializeFromSource: function() {
            var source = $('.address_source_radio:checked').val();

            // Si c'est entity ou coorg, pré-remplir les champs
            if (source === 'entity') {
                var $entity = $('#address_source_entity');
                var address = $entity.data('address') || '';
                var lat = $entity.data('lat') || '';
                var lng = $entity.data('lng') || '';

                // Ne pré-remplir que si l'adresse est vide
                if (!$('#location_address').val() && address) {
                    this.fillLocationFields(address, lat, lng, true);
                }
            } else if (source === 'coorg') {
                var $selected = $('#coorg_entity_select').find(':selected');
                if ($selected.val()) {
                    var address = $selected.data('address') || '';
                    var lat = $selected.data('lat') || '';
                    var lng = $selected.data('lng') || '';

                    if (!$('#location_address').val() && address) {
                        this.fillLocationFields(address, lat, lng, true);
                    }
                }
            }
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
                        $selected.data('address') || '',
                        $selected.data('lat') || '',
                        $selected.data('lng') || '',
                        true
                    );
                }
                this.setFieldsReadonly(true);
            } else {
                $('#coorg_entity_select').prop('disabled', true);
            }

            // Remplir avec les données de l'entité
            if (source === 'entity') {
                var $entity = $('#address_source_entity');
                var address = $entity.data('address') || '';
                var lat = $entity.data('lat') || '';
                var lng = $entity.data('lng') || '';
                this.fillLocationFields(address, lat, lng, true);
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
            var $address = $('#location_address');

            if (readonly) {
                $address.prop('readonly', true).addClass('readonly');
                this.marker.dragging.disable();
            } else {
                $address.prop('readonly', false).removeClass('readonly');
                this.marker.dragging.enable();
            }
        },

        fillLocationFields: function(address, lat, lng, updateMap) {
            // Adresse
            $('#location_address').val(address || '');

            // Coordonnées et carte
            if (lat && lng) {
                this.updateCoordinates(lat, lng);
                if (updateMap) {
                    this.updateMapPosition(parseFloat(lat), parseFloat(lng));
                }
            }
        },

        clearLocationFields: function() {
            $('#location_address').val('');
            // Garder les coordonnées par défaut pour la carte
        },

        updateCoordinates: function(lat, lng) {
            var latVal = parseFloat(lat).toFixed(6);
            var lngVal = parseFloat(lng).toFixed(6);
            $('#map_lat').val(latVal);
            $('#map_lng').val(lngVal);
            // Mettre à jour le champ GPS combiné (affichage)
            $('#map_gps').val(latVal + ', ' + lngVal);
        },

        updateMapPosition: function(lat, lng) {
            if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                this.marker.setLatLng([lat, lng]);
                this.map.setView([lat, lng], 15);
            }
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
                        $('#map_address').val(data.display_name);
                    }
                }
            });
        },

        openMediaPicker: function(targetId) {
            var self = this;

            // Utiliser le MediaPicker si disponible
            if (typeof window.EL_MediaPicker !== 'undefined') {
                window.EL_MediaPicker.open({
                    mode: 'single',
                    title: 'Sélectionner une image',
                    callback: function(images) {
                        if (images && images.length > 0) {
                            var image = images[0];
                            $('#' + targetId).val(image.id);

                            // Créer ou mettre à jour l'aperçu
                            var $wrapper = $('#' + targetId).closest('.field_image_wrapper');
                            var $preview = $wrapper.find('.location_image_preview');

                            if ($preview.length === 0) {
                                $wrapper.append('<div class="location_image_preview" id="' + targetId + '_preview"><img src="' + image.url + '" alt=""><button type="button" class="btn_remove_location_image" data-target="' + targetId + '"><i class="fa fa-times"></i></button></div>');
                            } else {
                                $preview.find('img').attr('src', image.url);
                            }
                        }
                    }
                });
            } else {
                // Fallback - WordPress Media Library
                var mediaFrame = wp.media({
                    title: 'Sélectionner une image',
                    button: { text: 'Utiliser cette image' },
                    multiple: false
                });

                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(attachment.id);

                    var $wrapper = $('#' + targetId).closest('.field_image_wrapper');
                    var $preview = $wrapper.find('.location_image_preview');
                    var thumbUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                    if ($preview.length === 0) {
                        $wrapper.append('<div class="location_image_preview" id="' + targetId + '_preview"><img src="' + thumbUrl + '" alt=""><button type="button" class="btn_remove_location_image" data-target="' + targetId + '"><i class="fa fa-times"></i></button></div>');
                    } else {
                        $preview.find('img').attr('src', thumbUrl);
                    }
                });

                mediaFrame.open();
            }
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
.location_address_input.readonly {
    background-color: #f9f9f9;
    color: #666;
    cursor: not-allowed;
}

/* Coordonnées GPS en lecture seule */
.location_gps_input {
    background-color: #f9f9f9 !important;
    color: #666 !important;
    cursor: not-allowed;
}

/* Séparateur localisation */
.location_separator {
    margin: 40px 0 32px;
}

/* Champs supplémentaires */
.location_extra_fields {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.location_extra_field {
    margin: 0 !important;
}

.location_extra_field .field_header {
    margin-bottom: 16px;
}

.location_extra_field .field_label {
    display: block;
    font-weight: 600;
    font-size: 15px;
    color: #333;
    margin-bottom: 6px;
}

.location_extra_field .field_hint {
    color: #666;
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
}

/* Champs avec image */
.location_extra_field_with_image .field_content_row {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.location_extra_field_with_image .field_input_wrapper {
    flex: 1;
}

.location_extra_field_with_image .field_input_wrapper input {
    width: 100%;
    height: 48px;
    padding: 0 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    background: #fff;
}

.location_extra_field_with_image .field_input_wrapper input:focus {
    border-color: #333;
    outline: none;
}

.location_extra_field_with_image .field_image_wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn_add_location_image {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #fff;
    border: 2px dashed #FF6600;
    border-radius: 8px;
    color: #FF6600;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn_add_location_image:hover {
    background: #fff5f0;
    border-style: solid;
}

.location_image_preview {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
}

.location_image_preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn_remove_location_image {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    border: none;
    background: #e74c3c;
    color: #fff;
    border-radius: 50%;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Champs checkbox */
.location_checkbox_field .field_content_row {
    display: flex;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
}

.location_checkbox_option {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 0;
    position: relative;
}

.location_checkbox_option input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.location_checkbox_option .option_checkbox {
    width: 22px;
    height: 22px;
    border: 2px solid #ccc;
    border-radius: 4px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.location_checkbox_option:hover .option_checkbox {
    border-color: #FF6600;
}

.location_checkbox_option.active .option_checkbox,
.location_checkbox_option input:checked + .option_checkbox {
    background: #FF6600;
    border-color: #FF6600;
}

.location_checkbox_option .option_label {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.field_notes_wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.field_notes_wrapper label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}

.field_notes_wrapper input {
    flex: 1;
    height: 44px;
    padding: 0 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    background: #fff;
}

.field_notes_wrapper input:focus {
    border-color: #333;
    outline: none;
}

/* Select type événement */
.location_type_select {
    width: 100%;
    max-width: 300px;
    height: 48px;
    padding: 0 40px 0 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 14px center;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.location_type_select:focus {
    border-color: #333;
    outline: none;
}

/* Responsive */
@media (max-width: 768px) {
    .location_extra_field_with_image .field_content_row {
        flex-direction: column;
        gap: 12px;
    }

    .location_extra_field_with_image .field_image_wrapper {
        width: 100%;
    }

    .btn_add_location_image {
        width: 100%;
        justify-content: center;
    }

    .location_checkbox_field .field_content_row {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .field_notes_wrapper {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .field_notes_wrapper input {
        width: 100%;
    }
}
</style>
