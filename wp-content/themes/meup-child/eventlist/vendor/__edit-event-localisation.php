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

// Services de l'événement (peuvent être hérités du profil ou personnalisés)
$event_services_enabled = get_post_meta( $post_id, $_prefix.'services_enabled', true) ?: '';
$event_parking = get_post_meta( $post_id, $_prefix.'event_parking', true) ?: '';
$event_parking_info = get_post_meta( $post_id, $_prefix.'event_parking_info', true) ?: '';
$event_transport = get_post_meta( $post_id, $_prefix.'event_transport', true) ?: '';
$event_transport_info = get_post_meta( $post_id, $_prefix.'event_transport_info', true) ?: '';
$event_pmr = get_post_meta( $post_id, $_prefix.'event_pmr', true) ?: '';
$event_pmr_info = get_post_meta( $post_id, $_prefix.'event_pmr_info', true) ?: '';
$event_wifi = get_post_meta( $post_id, $_prefix.'event_wifi', true) ?: '';
$event_wifi_info = get_post_meta( $post_id, $_prefix.'event_wifi_info', true) ?: '';
$event_animals = get_post_meta( $post_id, $_prefix.'event_animals', true) ?: '';
$event_animals_info = get_post_meta( $post_id, $_prefix.'event_animals_info', true) ?: '';
$event_baby = get_post_meta( $post_id, $_prefix.'event_baby', true) ?: '';
$event_baby_info = get_post_meta( $post_id, $_prefix.'event_baby_info', true) ?: '';
$event_restau = get_post_meta( $post_id, $_prefix.'event_restau', true) ?: '';
$event_restau_info = get_post_meta( $post_id, $_prefix.'event_restau_info', true) ?: '';
$event_boisson = get_post_meta( $post_id, $_prefix.'event_boisson', true) ?: '';
$event_boisson_info = get_post_meta( $post_id, $_prefix.'event_boisson_info', true) ?: '';

// Services du profil de l'entité courante
$user_services = array(
    'parking' => get_user_meta( $user_id, 'org_stationnement', true ) === 'yes',
    'parking_info' => get_user_meta( $user_id, 'org_stationnement_infos', true ) ?: '',
    'transport' => false,
    'transport_info' => '',
    'pmr' => get_user_meta( $user_id, 'org_pmr', true ) === 'yes',
    'pmr_info' => get_user_meta( $user_id, 'org_pmr_infos', true ) ?: '',
    'wifi' => false,
    'wifi_info' => '',
    'animals' => false,
    'animals_info' => '',
    'baby' => false,
    'baby_info' => '',
    'restau' => get_user_meta( $user_id, 'org_restauration', true ) === 'yes',
    'restau_info' => get_user_meta( $user_id, 'org_restauration_infos', true ) ?: '',
    'boisson' => get_user_meta( $user_id, 'org_boisson', true ) === 'yes',
    'boisson_info' => get_user_meta( $user_id, 'org_boisson_infos', true ) ?: '',
);

// Ajouter les services aux partenaires
foreach ( $partners as $key => $partner ) {
    $pid = $partner['id'];
    $partners[$key]['services'] = array(
        'parking' => get_user_meta( $pid, 'org_stationnement', true ) === 'yes',
        'parking_info' => get_user_meta( $pid, 'org_stationnement_infos', true ) ?: '',
        'transport' => false,
        'transport_info' => '',
        'pmr' => get_user_meta( $pid, 'org_pmr', true ) === 'yes',
        'pmr_info' => get_user_meta( $pid, 'org_pmr_infos', true ) ?: '',
        'wifi' => false,
        'wifi_info' => '',
        'animals' => false,
        'animals_info' => '',
        'baby' => false,
        'baby_info' => '',
        'restau' => get_user_meta( $pid, 'org_restauration', true ) === 'yes',
        'restau_info' => get_user_meta( $pid, 'org_restauration_infos', true ) ?: '',
        'boisson' => get_user_meta( $pid, 'org_boisson', true ) === 'yes',
        'boisson_info' => get_user_meta( $pid, 'org_boisson_infos', true ) ?: '',
    );
}

// Déterminer si le toggle services doit être ouvert par défaut
$services_toggle_open = !empty($event_services_enabled) || !empty($event_parking) || !empty($event_pmr) || !empty($event_restau) || !empty($event_boisson);
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

        <!-- Layout 2 colonnes: Champs à gauche, Carte à droite -->
        <div class="location_fields_wrapper">
            <!-- Colonne gauche: Les 3 champs -->
            <div class="location_fields_left">
                <!-- Source de l'adresse -->
                <div class="vendor_field address_source_field">
                    <label for="address_source_select" class="field_label"><?php esc_html_e( 'Veuillez choisir la source de l\'adresse pour cette localisation :', 'eventlist' ); ?></label>
                    <div class="address_source_select_wrapper">
                        <select name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                                id="address_source_select"
                                class="address_source_select">
                            <option value="entity"
                                    data-address="<?php echo esc_attr($user_address); ?>"
                                    data-lat="<?php echo esc_attr($user_lat); ?>"
                                    data-lng="<?php echo esc_attr($user_lng); ?>"
                                    <?php selected($address_source, 'entity'); ?>>
                                <?php esc_html_e( 'Mon entité', 'eventlist' ); ?>
                            </option>
                            <?php if ( !empty($partners) ) : ?>
                                <?php foreach ( $partners as $partner ) : ?>
                                    <option value="coorg_<?php echo esc_attr($partner['id']); ?>"
                                            data-address="<?php echo esc_attr($partner['address']); ?>"
                                            data-lat="<?php echo esc_attr($partner['lat']); ?>"
                                            data-lng="<?php echo esc_attr($partner['lng']); ?>"
                                            data-partner-id="<?php echo esc_attr($partner['id']); ?>"
                                            <?php echo ($address_source == 'coorg' && $coorg_entity_id == $partner['id']) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($partner['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="new" <?php selected($address_source, 'new'); ?>>
                                <?php esc_html_e( 'Nouvelle adresse', 'eventlist' ); ?>
                            </option>
                        </select>
                        <!-- Champ caché pour stocker l'ID du co-org si sélectionné -->
                        <input type="hidden" name="<?php echo esc_attr($_prefix.'coorg_entity_id'); ?>" id="coorg_entity_id" value="<?php echo esc_attr($coorg_entity_id); ?>">
                    </div>
                </div>

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

            <!-- Colonne droite: Carte OSM -->
            <div class="location_fields_right">
                <div class="location_map_container">
                    <div id="location_osm_map" class="osm_map"></div>
                </div>
            </div>
        </div>

        <!-- Toggle Services et Accessibilité -->
        <div class="services_toggle_section <?php echo $services_toggle_open ? 'is-open' : ''; ?>" id="services_toggle_section">
            <div class="services_toggle_header">
                <label class="services_toggle_switch">
                    <input type="checkbox"
                           name="<?php echo esc_attr($_prefix.'services_enabled'); ?>"
                           id="services_enabled"
                           value="1"
                           <?php checked($services_toggle_open, true); ?>>
                    <span class="toggle_slider"></span>
                </label>
                <div class="services_toggle_label">
                    <span class="toggle_title"><?php esc_html_e( 'Services et Accessibilité', 'eventlist' ); ?></span>
                </div>
            </div>

            <div class="services_toggle_content" style="<?php echo $services_toggle_open ? 'display: block;' : 'display: none;'; ?>">
                <p class="services_description">
                    <?php esc_html_e( 'Informez les utilisateurs sur les services disponibles et l\'accessibilité par rapport au lieu.', 'eventlist' ); ?><br>
                    <span class="services_note"><?php esc_html_e( 'Ces informations pourront être reprises automatiquement sur vos fiches d\'événements qui se déroulent à cette adresse.', 'eventlist' ); ?></span>
                </p>

                <!-- Grille des services (2 colonnes) -->
                <div class="services_grid">
                    <!-- Parking sur place -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_parking'); ?>"
                                       id="event_parking"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_parking, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Parking sur place', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_parking_info'); ?>"
                               id="event_parking_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_parking_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Parking gratuit de 50 places ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si le stationnement est possible, et donnez toutes les informations utiles pour les visiteurs véhiculés.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Transports en commun -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_transport'); ?>"
                                       id="event_transport"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_transport, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Transports en commun', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_transport_info'); ?>"
                               id="event_transport_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_transport_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Bus T309, ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si le lieu est accessible par les transports en commun, et donnez toutes les informations nécessaires.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Accessible PMR -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_pmr'); ?>"
                                       id="event_pmr"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_pmr, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Accessible PMR', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_pmr_info'); ?>"
                               id="event_pmr_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_pmr_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Entrée accessible pour les Personnes ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si la structure permet l\'accès aux Personnes à Mobilité Réduite, et informez sur les services disponibles.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Wifi -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_wifi'); ?>"
                                       id="event_wifi"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_wifi, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Wifi', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_wifi_info'); ?>"
                               id="event_wifi_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_wifi_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Wifi gratuit sur inscription ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si le wifi est disponible, et donnez les informations utiles.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Animaux acceptés -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_animals'); ?>"
                                       id="event_animals"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_animals, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Animaux acceptés', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_animals_info'); ?>"
                               id="event_animals_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_animals_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Chiens et chats acceptés, ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si les animaux de compagnie sont acceptés sur place, et donnez les informations utiles.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Adapté aux bébés -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_baby'); ?>"
                                       id="event_baby"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_baby, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Adapté aux bébés', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_baby_info'); ?>"
                               id="event_baby_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_baby_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Espace de change, espace de jeux ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez et informez si la structure est adaptée pour l\'accueil de bébé (espace de change, etc.);', 'eventlist' ); ?></p>
                    </div>

                    <!-- Restauration sur place -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_restau'); ?>"
                                       id="event_restau"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_restau, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_restau_info'); ?>"
                               id="event_restau_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_restau_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Cafétéria disponible ...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si la structure permet l\'accès aux Personnes à Mobilité Réduite, et informez sur les services disponibles.', 'eventlist' ); ?></p>
                    </div>

                    <!-- Boisson sur place -->
                    <div class="service_item">
                        <div class="service_header">
                            <label class="service_toggle_switch">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_boisson'); ?>"
                                       id="event_boisson"
                                       class="service_checkbox"
                                       value="yes"
                                       <?php checked($event_boisson, 'yes'); ?>>
                                <span class="toggle_slider small"></span>
                            </label>
                            <span class="service_label"><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></span>
                        </div>
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'event_boisson_info'); ?>"
                               id="event_boisson_info"
                               class="service_info_input"
                               value="<?php echo esc_attr($event_boisson_info); ?>"
                               placeholder="<?php esc_attr_e( 'Ex : Soft disponible à la cafétéria...', 'eventlist' ); ?>">
                        <p class="service_hint"><?php esc_html_e( 'Cochez si le lieu est accessible par les transports en commun, et donnez toutes les informations nécessaires.', 'eventlist' ); ?></p>
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
// Services data from PHP (entity and partners)
var EL_ServicesData = {
    entity: <?php echo json_encode($user_services); ?>,
    partners: {
        <?php foreach ( $partners as $partner ) : ?>
        '<?php echo esc_js($partner['id']); ?>': <?php echo json_encode($partner['services']); ?>,
        <?php endforeach; ?>
    }
};

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

            // Changement de source d'adresse (select)
            $('#address_source_select').on('change', function() {
                self.handleAddressSource();
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

            // Toggle Services et Accessibilité
            $('#services_enabled').on('change', function() {
                self.handleServicesToggle($(this).is(':checked'));
            });

            // Clic sur le header du toggle pour basculer
            $('.services_toggle_header').on('click', function(e) {
                // Éviter le double déclenchement si on clique sur le switch lui-même
                if ($(e.target).closest('.services_toggle_switch').length) {
                    return;
                }
                var $checkbox = $('#services_enabled');
                $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
            });

        },

        // Gestion du toggle Services
        handleServicesToggle: function(isOpen) {
            var $section = $('#services_toggle_section');
            var $content = $section.find('.services_toggle_content');

            if (isOpen) {
                $section.addClass('is-open');
                $content.slideDown(300);
            } else {
                $section.removeClass('is-open');
                $content.slideUp(300);
            }
        },

        // Pré-remplir les services depuis le profil de l'entité/partenaire
        prefillServicesFromSource: function(sourceType, partnerId) {
            var services = null;

            if (sourceType === 'entity') {
                services = EL_ServicesData.entity;
            } else if (sourceType === 'coorg' && partnerId && EL_ServicesData.partners[partnerId]) {
                services = EL_ServicesData.partners[partnerId];
            }

            // Si pas de services disponibles, ne rien faire
            if (!services) {
                return;
            }

            // Pré-remplir les checkboxes et infos
            this.setServiceValue('event_parking', services.parking, services.parking_info);
            this.setServiceValue('event_transport', services.transport, services.transport_info);
            this.setServiceValue('event_pmr', services.pmr, services.pmr_info);
            this.setServiceValue('event_wifi', services.wifi, services.wifi_info);
            this.setServiceValue('event_animals', services.animals, services.animals_info);
            this.setServiceValue('event_baby', services.baby, services.baby_info);
            this.setServiceValue('event_restau', services.restau, services.restau_info);
            this.setServiceValue('event_boisson', services.boisson, services.boisson_info);

            // Ouvrir le toggle si au moins un service est activé
            var hasAnyService = services.parking || services.pmr || services.restau || services.boisson;
            if (hasAnyService) {
                $('#services_enabled').prop('checked', true);
                this.handleServicesToggle(true);
            }
        },

        // Helper pour définir la valeur d'un service
        setServiceValue: function(fieldId, isChecked, infoValue) {
            var $checkbox = $('#' + fieldId);
            var $info = $('#' + fieldId + '_info');

            $checkbox.prop('checked', isChecked);
            if (infoValue) {
                $info.val(infoValue);
            }
        },

        initializeFromSource: function() {
            var $selected = $('#address_source_select').find(':selected');
            var sourceValue = $selected.val();

            // Si c'est entity ou coorg, pré-remplir les champs
            if (sourceValue !== 'new') {
                var address = $selected.data('address') || '';
                var lat = $selected.data('lat') || '';
                var lng = $selected.data('lng') || '';

                // Ne pré-remplir que si l'adresse est vide
                if (!$('#location_address').val() && address) {
                    this.fillLocationFields(address, lat, lng, true);
                }
            }
        },

        handleAddressSource: function() {
            var $select = $('#address_source_select');
            var $selected = $select.find(':selected');
            var sourceValue = $selected.val();
            var self = this;

            // Nouvelle adresse - vider les champs et les rendre éditables
            if (sourceValue === 'new') {
                this.clearLocationFields();
                this.setFieldsReadonly(false);
                $('#coorg_entity_id').val('');
                // Réinitialiser les services pour une nouvelle adresse
                this.clearServices();
            } else {
                // Entity ou Co-org - remplir avec les données de l'option sélectionnée
                var address = $selected.data('address') || '';
                var lat = $selected.data('lat') || '';
                var lng = $selected.data('lng') || '';

                this.fillLocationFields(address, lat, lng, true);
                this.setFieldsReadonly(true);

                // Si c'est un co-org, mettre à jour le champ caché et pré-remplir les services
                if (sourceValue.indexOf('coorg_') === 0) {
                    var partnerId = $selected.data('partner-id') || '';
                    $('#coorg_entity_id').val(partnerId);
                    // Pré-remplir les services depuis le profil du partenaire
                    this.prefillServicesFromSource('coorg', partnerId);
                } else {
                    $('#coorg_entity_id').val('');
                    // Pré-remplir les services depuis le profil de l'entité
                    this.prefillServicesFromSource('entity', null);
                }
            }
        },

        // Réinitialiser les services
        clearServices: function() {
            $('#event_parking, #event_transport, #event_pmr, #event_wifi, #event_animals, #event_baby, #event_restau, #event_boisson').prop('checked', false);
            $('#event_parking_info, #event_transport_info, #event_pmr_info, #event_wifi_info, #event_animals_info, #event_baby_info, #event_restau_info, #event_boisson_info').val('');
            $('#services_enabled').prop('checked', false);
            this.handleServicesToggle(false);
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
/* Layout des champs d'adresse et carte - côte à côte avec même hauteur */
.location_fields_wrapper {
    display: flex;
    gap: 24px;
    align-items: stretch;
    margin-bottom: 32px;
}

.location_fields_left {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.location_fields_right {
    flex: 1;
    display: flex;
    min-height: 250px;
}

.location_map_container {
    flex: 1;
    display: flex;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

#location_osm_map {
    width: 100%;
    height: 100%;
    min-height: 250px;
}

/* Toggle Services et Accessibilité */
.services_toggle_section {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 0;
    margin-top: 24px;
    overflow: hidden;
}

.services_toggle_header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: #fafafa;
    border-bottom: 1px solid transparent;
    cursor: pointer;
    transition: background 0.2s;
}

.services_toggle_section.is-open .services_toggle_header {
    border-bottom-color: #e0e0e0;
}

.services_toggle_header:hover {
    background: #f5f5f5;
}

.services_toggle_label {
    flex: 1;
}

.toggle_title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

/* Toggle Switch Style */
.services_toggle_switch,
.service_toggle_switch {
    position: relative;
    display: inline-block;
    flex-shrink: 0;
}

.services_toggle_switch input,
.service_toggle_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.toggle_slider {
    display: block;
    width: 52px;
    height: 28px;
    background-color: #ccc;
    border-radius: 28px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.toggle_slider::before {
    content: '';
    position: absolute;
    width: 22px;
    height: 22px;
    background-color: #fff;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.services_toggle_switch input:checked + .toggle_slider,
.service_toggle_switch input:checked + .toggle_slider {
    background-color: #FF6600;
}

.services_toggle_switch input:checked + .toggle_slider::before,
.service_toggle_switch input:checked + .toggle_slider::before {
    transform: translateX(24px);
}

/* Small toggle for services */
.toggle_slider.small {
    width: 44px;
    height: 24px;
}

.toggle_slider.small::before {
    width: 18px;
    height: 18px;
}

.service_toggle_switch input:checked + .toggle_slider.small::before {
    transform: translateX(20px);
}

/* Services Content */
.services_toggle_content {
    padding: 24px;
}

.services_description {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin: 0 0 24px 0;
}

.services_note {
    color: #999;
    font-style: italic;
}

/* Services Grid - 2 colonnes strictes */
.services_grid {
    display: grid;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px;
}

.service_item {
    background: #fafafa;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
}

.service_header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    margin-bottom: 10px;
}

.service_label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.service_info_input {
    width: 100%;
    height: 42px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 13px;
    color: #333;
    background: #fff;
    margin-bottom: 6px;
}

.service_info_input:focus {
    border-color: #FF6600;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.service_hint {
    font-size: 11px;
    color: #888;
    line-height: 1.4;
    margin: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .location_fields_wrapper {
        flex-direction: column;
    }

    .location_fields_right {
        min-height: 300px;
    }

    .services_grid {
        grid-template-columns: 1fr;
    }
}

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

/* Select source d'adresse */
.address_source_field {
    margin-bottom: 0;
}

.address_source_field .field_label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.address_source_select_wrapper {
    width: 100%;
}

.address_source_select {
    width: 100%;
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
    transition: border-color 0.2s, box-shadow 0.2s;
}

.address_source_select:hover {
    border-color: #999;
}

.address_source_select:focus {
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    outline: none;
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
