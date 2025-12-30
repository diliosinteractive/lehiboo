<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Localisation de l'activité
 * V1 Le Hiboo - Refonte complète avec Services et Accessibilité
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;
$user_id = get_current_user_id();

// Récupérer les données existantes de l'événement
$address         = get_post_meta( $post_id, $_prefix.'address', true ) ?: '';
$map_name        = get_post_meta( $post_id, $_prefix.'map_name', true ) ?: '';
$map_address     = get_post_meta( $post_id, $_prefix.'map_address', true ) ?: '';
$address_source  = get_post_meta( $post_id, $_prefix.'address_source', true ) ?: 'entity';
$coorg_entity_id = get_post_meta( $post_id, $_prefix.'coorg_entity_id', true ) ?: '';

// Type d'événement (physique/en ligne)
$event_type = get_post_meta( $post_id, $_prefix.'event_type', true ) ?: 'classic';

// Coordonnées par défaut (Paris)
if ( $post_id !== '' ) {
    $map_lat = get_post_meta( $post_id, $_prefix.'map_lat', true ) ?: '';
    $map_lng = get_post_meta( $post_id, $_prefix.'map_lng', true ) ?: '';
} else {
    $EL_Setting_Event = EL()->options->event;
    $map_lat = $EL_Setting_Event->get('latitude_map_default') != '' ? $EL_Setting_Event->get('latitude_map_default') : '48.8566';
    $map_lng = $EL_Setting_Event->get('longitude_map_default') != '' ? $EL_Setting_Event->get('longitude_map_default') : '2.3522';
}

// URL événement en ligne
$event_online_url = get_post_meta( $post_id, $_prefix.'event_online_url', true );
$event_online_notes = get_post_meta( $post_id, $_prefix.'event_online_notes', true );

// Services de l'événement (peuvent être hérités de l'org ou personnalisés)
$event_parking = get_post_meta( $post_id, $_prefix.'event_parking', true ) ?: '';
$event_parking_info = get_post_meta( $post_id, $_prefix.'event_parking_info', true ) ?: '';
$event_transport = get_post_meta( $post_id, $_prefix.'event_transport', true ) ?: '';
$event_transport_info = get_post_meta( $post_id, $_prefix.'event_transport_info', true ) ?: '';
$event_pmr = get_post_meta( $post_id, $_prefix.'event_pmr', true ) ?: '';
$event_pmr_info = get_post_meta( $post_id, $_prefix.'event_pmr_info', true ) ?: '';
$event_wifi = get_post_meta( $post_id, $_prefix.'event_wifi', true ) ?: '';
$event_wifi_info = get_post_meta( $post_id, $_prefix.'event_wifi_info', true ) ?: '';
$event_animals = get_post_meta( $post_id, $_prefix.'event_animals', true ) ?: '';
$event_animals_info = get_post_meta( $post_id, $_prefix.'event_animals_info', true ) ?: '';
$event_baby = get_post_meta( $post_id, $_prefix.'event_baby', true ) ?: '';
$event_baby_info = get_post_meta( $post_id, $_prefix.'event_baby_info', true ) ?: '';
$event_restau = get_post_meta( $post_id, $_prefix.'event_restau', true ) ?: '';
$event_restau_info = get_post_meta( $post_id, $_prefix.'event_restau_info', true ) ?: '';
$event_boisson = get_post_meta( $post_id, $_prefix.'event_boisson', true ) ?: '';
$event_boisson_info = get_post_meta( $post_id, $_prefix.'event_boisson_info', true ) ?: '';

// Toggle services activé ?
$services_enabled = get_post_meta( $post_id, $_prefix.'services_enabled', true ) ?: '';

// ========================================
// Récupérer les données de l'organisation courante
// ========================================
$org_address = get_user_meta( $user_id, 'user_address', true ) ?: '';
$org_name = get_user_meta( $user_id, 'organisation_name', true ) ?: get_the_author_meta('display_name', $user_id);
$org_lat = get_user_meta( $user_id, 'user_lat', true ) ?: $map_lat;
$org_lng = get_user_meta( $user_id, 'user_lng', true ) ?: $map_lng;

// Services de l'organisation
$org_parking = get_user_meta( $user_id, 'org_stationnement', true ) ?: '';
$org_pmr = get_user_meta( $user_id, 'org_pmr', true ) ?: '';
$org_pmr_info = get_user_meta( $user_id, 'org_pmr_infos', true ) ?: '';
$org_restau = get_user_meta( $user_id, 'org_restauration', true ) ?: '';
$org_restau_info = get_user_meta( $user_id, 'org_restauration_infos', true ) ?: '';
$org_boisson = get_user_meta( $user_id, 'org_boisson', true ) ?: '';
$org_boisson_info = get_user_meta( $user_id, 'org_boisson_infos', true ) ?: '';

// ========================================
// Récupérer les partenaires acceptés (co-organisateurs potentiels)
// ========================================
$partners = array();
if ( class_exists('EL_Partnership') ) {
    EL_Partnership::init();
    $partnerships = EL_Partnership::get_for_organisation( $user_id, 'acceptee' );

    foreach ( $partnerships as $partnership ) {
        $partner_id = ($partnership->organisation_principale_id == $user_id)
            ? $partnership->organisation_invitee_id
            : $partnership->organisation_principale_id;

        if ( $partner_id ) {
            $partner_name = get_user_meta( $partner_id, 'organisation_name', true );
            if ( !$partner_name ) {
                $partner_name = get_the_author_meta('display_name', $partner_id);
            }
            $partner_address = get_user_meta( $partner_id, 'user_address', true );
            $partner_lat = get_user_meta( $partner_id, 'user_lat', true );
            $partner_lng = get_user_meta( $partner_id, 'user_lng', true );

            // Services du partenaire
            $partner_parking = get_user_meta( $partner_id, 'org_stationnement', true );
            $partner_pmr = get_user_meta( $partner_id, 'org_pmr', true );
            $partner_pmr_info = get_user_meta( $partner_id, 'org_pmr_infos', true );
            $partner_restau = get_user_meta( $partner_id, 'org_restauration', true );
            $partner_restau_info = get_user_meta( $partner_id, 'org_restauration_infos', true );
            $partner_boisson = get_user_meta( $partner_id, 'org_boisson', true );
            $partner_boisson_info = get_user_meta( $partner_id, 'org_boisson_infos', true );

            $partners[] = array(
                'id'           => $partner_id,
                'name'         => $partner_name,
                'address'      => $partner_address,
                'lat'          => $partner_lat,
                'lng'          => $partner_lng,
                'parking'      => $partner_parking,
                'pmr'          => $partner_pmr,
                'pmr_info'     => $partner_pmr_info,
                'restau'       => $partner_restau,
                'restau_info'  => $partner_restau_info,
                'boisson'      => $partner_boisson,
                'boisson_info' => $partner_boisson_info,
            );
        }
    }
}

// ========================================
// Déterminer si services doivent être pré-remplis
// ========================================
$has_services_data = false;
if ( $address_source === 'entity' ) {
    $has_services_data = !empty($org_parking) || ($org_pmr === 'oui') || ($org_restau === 'oui') || ($org_boisson === 'oui');
} elseif ( $address_source === 'coorg' && $coorg_entity_id ) {
    foreach ( $partners as $partner ) {
        if ( $partner['id'] == $coorg_entity_id ) {
            $has_services_data = !empty($partner['parking']) || ($partner['pmr'] === 'oui') || ($partner['restau'] === 'oui') || ($partner['boisson'] === 'oui');
            break;
        }
    }
}

// Si l'événement a déjà des services propres définis
$event_has_own_services = !empty($event_parking) || !empty($event_pmr) || !empty($event_wifi) || !empty($event_animals) || !empty($event_baby) || !empty($event_restau) || !empty($event_boisson) || !empty($event_transport);

// Le toggle services est ouvert si : déjà activé OU données héritées disponibles OU données propres à l'événement
$services_toggle_open = ($services_enabled === 'yes') || $has_services_data || $event_has_own_services;
?>

<div class="event_basic_block localisation_section">
    <h4 class="heading_section"><?php esc_html_e( 'Localisation de l\'activité', 'eventlist' ); ?></h4>
    <p class="field_description">
        <?php esc_html_e( 'Sélectionnez le lieu de l\'activité et renseignez les informations pratiques pour accueillir les participants.', 'eventlist' ); ?>
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
            <label class="field_label"><?php esc_html_e( 'Veuillez sélectionner la source de l\'adresse pour l\'activité', 'eventlist' ); ?></label>

            <select name="<?php echo esc_attr($_prefix.'address_source'); ?>"
                    id="address_source_select"
                    class="address_source_select selectpicker"
                    data-org-address="<?php echo esc_attr($org_address); ?>"
                    data-org-lat="<?php echo esc_attr($org_lat); ?>"
                    data-org-lng="<?php echo esc_attr($org_lng); ?>"
                    data-org-parking="<?php echo esc_attr($org_parking); ?>"
                    data-org-pmr="<?php echo esc_attr($org_pmr); ?>"
                    data-org-pmr-info="<?php echo esc_attr($org_pmr_info); ?>"
                    data-org-restau="<?php echo esc_attr($org_restau); ?>"
                    data-org-restau-info="<?php echo esc_attr($org_restau_info); ?>"
                    data-org-boisson="<?php echo esc_attr($org_boisson); ?>"
                    data-org-boisson-info="<?php echo esc_attr($org_boisson_info); ?>">
                <option value="entity" <?php selected($address_source, 'entity'); ?>><?php esc_html_e( 'Mon entité', 'eventlist' ); ?></option>
                <?php if ( !empty($partners) ) : ?>
                    <option value="coorg" <?php selected($address_source, 'coorg'); ?>><?php esc_html_e( 'Une entité co-organisatrice', 'eventlist' ); ?></option>
                <?php endif; ?>
                <option value="new" <?php selected($address_source, 'new'); ?>><?php esc_html_e( 'Nouvelle adresse', 'eventlist' ); ?></option>
            </select>
        </div>

        <!-- Sélection du co-organisateur (visible uniquement si "Une entité co-organisatrice" est sélectionné) -->
        <?php if ( !empty($partners) ) : ?>
        <div class="vendor_field coorg_select_field" id="coorg_select_wrapper" style="<?php echo ($address_source === 'coorg') ? 'display: block;' : 'display: none;'; ?>">
            <label for="coorg_entity_select"><?php esc_html_e( 'Sélectionnez l\'entité co-organisatrice', 'eventlist' ); ?></label>
            <select name="<?php echo esc_attr($_prefix.'coorg_entity_id'); ?>"
                    id="coorg_entity_select"
                    class="coorg_entity_select selectpicker">
                <option value=""><?php esc_html_e( 'Sélectionnez...', 'eventlist' ); ?></option>
                <?php foreach ( $partners as $partner ) : ?>
                    <option value="<?php echo esc_attr($partner['id']); ?>"
                            data-address="<?php echo esc_attr($partner['address']); ?>"
                            data-lat="<?php echo esc_attr($partner['lat']); ?>"
                            data-lng="<?php echo esc_attr($partner['lng']); ?>"
                            data-parking="<?php echo esc_attr($partner['parking']); ?>"
                            data-pmr="<?php echo esc_attr($partner['pmr']); ?>"
                            data-pmr-info="<?php echo esc_attr($partner['pmr_info']); ?>"
                            data-restau="<?php echo esc_attr($partner['restau']); ?>"
                            data-restau-info="<?php echo esc_attr($partner['restau_info']); ?>"
                            data-boisson="<?php echo esc_attr($partner['boisson']); ?>"
                            data-boisson-info="<?php echo esc_attr($partner['boisson_info']); ?>"
                            <?php selected($coorg_entity_id, $partner['id']); ?>>
                        <?php echo esc_html($partner['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Champs d'adresse et carte -->
        <div class="location_fields_wrapper">
            <div class="location_fields_left">
                <!-- Adresse de l'activité -->
                <div class="vendor_field">
                    <label for="location_address"><?php esc_html_e( 'Adresse de l\'activité', 'eventlist' ); ?></label>
                    <select name="<?php echo esc_attr($_prefix.'address'); ?>"
                            id="location_address"
                            class="location_address_select select2_address">
                        <option value=""><?php esc_html_e( 'Tapez une adresse ...', 'eventlist' ); ?></option>
                        <?php if ( $address ) : ?>
                            <option value="<?php echo esc_attr($address); ?>" selected><?php echo esc_html($address); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Coordonnées GPS (champ unique) -->
                <div class="vendor_field">
                    <label for="map_gps"><?php esc_html_e( 'Coordonnées GPS', 'eventlist' ); ?></label>
                    <div class="gps_input_wrapper">
                        <input type="text"
                               name="<?php echo esc_attr($_prefix.'map_gps'); ?>"
                               id="map_gps"
                               value="<?php echo ($map_lat && $map_lng) ? esc_attr($map_lat . ', ' . $map_lng) : ''; ?>"
                               class="location_gps_input"
                               placeholder="48.8566, 2.3522"
                               readonly>
                    </div>
                    <!-- Champs cachés pour lat/lng individuels -->
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_lat'); ?>" id="map_lat" value="<?php echo esc_attr($map_lat); ?>">
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_lng'); ?>" id="map_lng" value="<?php echo esc_attr($map_lng); ?>">
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_name'); ?>" id="map_name" value="<?php echo esc_attr($map_name); ?>">
                    <input type="hidden" name="<?php echo esc_attr($_prefix.'map_address'); ?>" id="map_address" value="<?php echo esc_attr($map_address); ?>">
                </div>
            </div>

            <div class="location_fields_right">
                <!-- Carte OSM -->
                <div class="location_map_container">
                    <div id="location_osm_map" class="osm_map"></div>
                </div>
            </div>
        </div>

        <!-- Toggle: Services et Accessibilité -->
        <div class="el_toggle_section services_toggle_section <?php echo $services_toggle_open ? 'is-open' : ''; ?>">
            <button type="button" class="el_toggle_header" aria-expanded="<?php echo $services_toggle_open ? 'true' : 'false'; ?>">
                <span class="el_toggle_title"><?php esc_html_e( 'Services et Accessibilité', 'eventlist' ); ?></span>
                <span class="el_toggle_icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </button>
            <div class="el_toggle_content" <?php echo $services_toggle_open ? 'style="display: block;"' : ''; ?>>
                <p class="services_description">
                    <?php esc_html_e( 'Informez les utilisateurs sur les services disponibles et l\'accessibilité par rapport au lieu.', 'eventlist' ); ?>
                    <br>
                    <small><?php esc_html_e( 'Ces informations pourront être reprises automatiquement sur vos fiches d\'événements qui se déroulent à cette adresse.', 'eventlist' ); ?></small>
                </p>

                <input type="hidden" name="<?php echo esc_attr($_prefix.'services_enabled'); ?>" id="services_enabled" value="<?php echo $services_toggle_open ? 'yes' : ''; ?>">

                <!-- Ligne 1: Parking + Transports -->
                <div class="el_row services_row">
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_parking'); ?>"
                                       id="service_parking"
                                       value="yes"
                                       <?php checked($event_parking, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Parking sur place', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_parking_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_parking_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Parking gratuit de 50 places ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si le stationnement est possible, et donnez toutes les informations utiles pour les visiteurs véhiculés.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_transport'); ?>"
                                       id="service_transport"
                                       value="yes"
                                       <?php checked($event_transport, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Transports en commun', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_transport_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_transport_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Bus T309, ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si le lieu est accessible par les transports en commun, et donnez toutes les informations nécessaires.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Ligne 2: PMR + Wifi -->
                <div class="el_row services_row">
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_pmr'); ?>"
                                       id="service_pmr"
                                       value="yes"
                                       <?php checked($event_pmr, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Accessible PMR', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_pmr_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_pmr_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Entrée accessible pour les Personnes ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si la structure permet l\'accès aux Personnes à Mobilité Réduite, et informez sur les services disponibles.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_wifi'); ?>"
                                       id="service_wifi"
                                       value="yes"
                                       <?php checked($event_wifi, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Wifi', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_wifi_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_wifi_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Wifi gratuit sur inscription ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si le wifi est disponible, et donnez les informations utiles.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Ligne 3: Animaux + Bébés -->
                <div class="el_row services_row">
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_animals'); ?>"
                                       id="service_animals"
                                       value="yes"
                                       <?php checked($event_animals, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Animaux acceptés', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_animals_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_animals_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Chiens et chats acceptés, ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si les animaux de compagnie sont acceptés sur place, et donnez les informations utiles.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_baby'); ?>"
                                       id="service_baby"
                                       value="yes"
                                       <?php checked($event_baby, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Adapté aux bébés', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_baby_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_baby_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Espace de change, espace de jeux ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez et informez si la structure est adaptée pour l\'accueil de bébé (espace de change, etc.).', 'eventlist' ); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Ligne 4: Restauration + Boisson -->
                <div class="el_row services_row">
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_restau'); ?>"
                                       id="service_restau"
                                       value="yes"
                                       <?php checked($event_restau, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_restau_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_restau_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Cafétéria disponible ...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si la structure permet l\'accès aux Personnes à Mobilité Réduite, et informez sur les services disponibles.', 'eventlist' ); ?></small>
                        </div>
                    </div>
                    <div class="el_col_6">
                        <div class="service_item">
                            <label class="service_toggle">
                                <input type="checkbox"
                                       name="<?php echo esc_attr($_prefix.'event_boisson'); ?>"
                                       id="service_boisson"
                                       value="yes"
                                       <?php checked($event_boisson, 'yes'); ?>>
                                <span class="service_toggle_slider"></span>
                                <span class="service_label"><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></span>
                            </label>
                            <input type="text"
                                   name="<?php echo esc_attr($_prefix.'event_boisson_info'); ?>"
                                   class="service_info_input"
                                   value="<?php echo esc_attr($event_boisson_info); ?>"
                                   placeholder="<?php esc_attr_e( 'Ex : Soft disponible à la cafétéria...', 'eventlist' ); ?>">
                            <small class="service_hint"><?php esc_html_e( 'Cochez si le lieu est accessible par les transports en commun, et donnez toutes les informations nécessaires.', 'eventlist' ); ?></small>
                        </div>
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

<style>
/* Services et Accessibilité - Styles spécifiques */
.services_toggle_section {
    margin-top: 32px;
}

.services_description {
    color: #666;
    font-size: 14px;
    margin-bottom: 24px;
    line-height: 1.6;
}

.services_description small {
    color: #888;
    font-style: italic;
}

.services_row {
    margin-bottom: 24px;
}

.services_row:last-child {
    margin-bottom: 0;
}

.service_item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 16px;
}

/* Toggle switch style */
.service_toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    margin-bottom: 12px;
}

.service_toggle input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.service_toggle_slider {
    position: relative;
    width: 44px;
    height: 24px;
    background: #e2e8f0;
    border-radius: 24px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.service_toggle_slider::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.service_toggle input:checked + .service_toggle_slider {
    background: #FF6600;
}

.service_toggle input:checked + .service_toggle_slider::before {
    transform: translateX(20px);
}

.service_label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.service_info_input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 8px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.service_info_input:focus {
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    outline: none;
}

.service_info_input::placeholder {
    color: #94a3b8;
    font-style: italic;
}

.service_hint {
    display: block;
    font-size: 12px;
    color: #94a3b8;
    line-height: 1.4;
}

/* GPS Input */
.gps_input_wrapper {
    position: relative;
}

.location_gps_input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8f9fa;
    color: #666;
}

/* Address source dropdown */
.address_source_select {
    width: 100%;
}

/* Co-org select field */
.coorg_select_field {
    margin-top: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .services_row .el_col_6 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 16px;
    }

    .services_row .el_col_6:last-child {
        margin-bottom: 0;
    }
}
</style>

<script>
(function($) {
    'use strict';

    var LocationManager = {
        map: null,
        marker: null,
        defaultLat: <?php echo $map_lat ? floatval($map_lat) : '48.8566'; ?>,
        defaultLng: <?php echo $map_lng ? floatval($map_lng) : '2.3522'; ?>,

        init: function() {
            this.initMap();
            this.initSelect2();
            this.bindEvents();
            this.handleAddressSourceChange();
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

        initSelect2: function() {
            var self = this;

            // Select2 pour l'adresse avec recherche Nominatim
            $('#location_address').select2({
                placeholder: '<?php esc_html_e( 'Tapez une adresse ...', 'eventlist' ); ?>',
                allowClear: true,
                width: '100%',
                minimumInputLength: 3,
                language: {
                    inputTooShort: function() {
                        return '<?php esc_html_e( 'Saisissez au moins 3 caractères', 'eventlist' ); ?>';
                    },
                    searching: function() {
                        return '<?php esc_html_e( 'Recherche...', 'eventlist' ); ?>';
                    },
                    noResults: function() {
                        return '<?php esc_html_e( 'Aucun résultat trouvé', 'eventlist' ); ?>';
                    }
                },
                ajax: {
                    url: 'https://nominatim.openstreetmap.org/search',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term,
                            format: 'json',
                            addressdetails: 1,
                            limit: 10,
                            countrycodes: 'fr',
                            'accept-language': 'fr'
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.display_name,
                                    text: item.display_name,
                                    lat: item.lat,
                                    lon: item.lon
                                };
                            })
                        };
                    }
                }
            });

            // Quand une adresse est sélectionnée
            $('#location_address').on('select2:select', function(e) {
                var data = e.params.data;
                if (data.lat && data.lon) {
                    self.updateMapPosition(parseFloat(data.lat), parseFloat(data.lon));
                    self.updateCoordinates(data.lat, data.lon);
                }
            });
        },

        bindEvents: function() {
            var self = this;

            // Changement de type d'événement (physique/en ligne)
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
            $('#address_source_select').on('change', function() {
                self.handleAddressSourceChange();
            });

            // Changement d'entité co-organisatrice
            $('#coorg_entity_select').on('change', function() {
                var $selected = $(this).find(':selected');
                if ($selected.val()) {
                    var address = $selected.data('address');
                    var lat = $selected.data('lat');
                    var lng = $selected.data('lng');

                    self.fillLocationFields(address, lat, lng);
                    self.fillServicesFromCoorg($selected);
                }
            });

            // Toggle services
            $('.services_toggle_section .el_toggle_header').on('click', function() {
                var $section = $(this).closest('.el_toggle_section');
                var $content = $section.find('.el_toggle_content');
                var isOpen = $section.hasClass('is-open');

                if (isOpen) {
                    $content.slideUp(200);
                    $section.removeClass('is-open');
                    $(this).attr('aria-expanded', 'false');
                    $('#services_enabled').val('');
                } else {
                    $content.slideDown(200);
                    $section.addClass('is-open');
                    $(this).attr('aria-expanded', 'true');
                    $('#services_enabled').val('yes');
                }
            });
        },

        handleAddressSourceChange: function() {
            var source = $('#address_source_select').val();
            var $select = $('#address_source_select');

            // Afficher/masquer le sélecteur de co-organisateur
            if (source === 'coorg') {
                $('#coorg_select_wrapper').slideDown(200);
                // Si un co-org est déjà sélectionné, charger ses données
                var $selected = $('#coorg_entity_select').find(':selected');
                if ($selected.val()) {
                    this.fillLocationFields(
                        $selected.data('address'),
                        $selected.data('lat'),
                        $selected.data('lng')
                    );
                    this.fillServicesFromCoorg($selected);
                }
            } else {
                $('#coorg_select_wrapper').slideUp(200);
            }

            // Remplir avec les données de l'entité
            if (source === 'entity') {
                var address = $select.data('org-address');
                var lat = $select.data('org-lat');
                var lng = $select.data('org-lng');
                this.fillLocationFields(address, lat, lng);
                this.fillServicesFromOrg($select);
            }

            // Nouvelle adresse - vider les champs
            if (source === 'new') {
                this.clearLocationFields();
                this.clearServices();
            }
        },

        fillLocationFields: function(address, lat, lng) {
            // Adresse
            if (address) {
                var $addressSelect = $('#location_address');
                if ($addressSelect.find("option[value='" + address + "']").length === 0) {
                    $addressSelect.append(new Option(address, address, true, true));
                } else {
                    $addressSelect.val(address);
                }
                $addressSelect.trigger('change');
            }

            // Coordonnées
            if (lat && lng) {
                this.updateCoordinates(lat, lng);
                this.updateMapPosition(parseFloat(lat), parseFloat(lng));
            }
        },

        fillServicesFromOrg: function($select) {
            // Remplir les services depuis l'organisation
            var parking = $select.data('org-parking');
            var pmr = $select.data('org-pmr');
            var pmrInfo = $select.data('org-pmr-info');
            var restau = $select.data('org-restau');
            var restauInfo = $select.data('org-restau-info');
            var boisson = $select.data('org-boisson');
            var boissonInfo = $select.data('org-boisson-info');

            // Parking
            if (parking) {
                $('#service_parking').prop('checked', true);
                $('input[name*="event_parking_info"]').val(parking);
            }
            // PMR
            if (pmr === 'oui') {
                $('#service_pmr').prop('checked', true);
                $('input[name*="event_pmr_info"]').val(pmrInfo || '');
            }
            // Restauration
            if (restau === 'oui') {
                $('#service_restau').prop('checked', true);
                $('input[name*="event_restau_info"]').val(restauInfo || '');
            }
            // Boisson
            if (boisson === 'oui') {
                $('#service_boisson').prop('checked', true);
                $('input[name*="event_boisson_info"]').val(boissonInfo || '');
            }

            // Ouvrir le toggle si des données existent
            if (parking || pmr === 'oui' || restau === 'oui' || boisson === 'oui') {
                var $section = $('.services_toggle_section');
                if (!$section.hasClass('is-open')) {
                    $section.find('.el_toggle_content').slideDown(200);
                    $section.addClass('is-open');
                    $section.find('.el_toggle_header').attr('aria-expanded', 'true');
                    $('#services_enabled').val('yes');
                }
            }
        },

        fillServicesFromCoorg: function($selected) {
            // Remplir les services depuis le co-organisateur
            var parking = $selected.data('parking');
            var pmr = $selected.data('pmr');
            var pmrInfo = $selected.data('pmr-info');
            var restau = $selected.data('restau');
            var restauInfo = $selected.data('restau-info');
            var boisson = $selected.data('boisson');
            var boissonInfo = $selected.data('boisson-info');

            // Reset d'abord
            this.clearServices();

            // Parking
            if (parking) {
                $('#service_parking').prop('checked', true);
                $('input[name*="event_parking_info"]').val(parking);
            }
            // PMR
            if (pmr === 'oui') {
                $('#service_pmr').prop('checked', true);
                $('input[name*="event_pmr_info"]').val(pmrInfo || '');
            }
            // Restauration
            if (restau === 'oui') {
                $('#service_restau').prop('checked', true);
                $('input[name*="event_restau_info"]').val(restauInfo || '');
            }
            // Boisson
            if (boisson === 'oui') {
                $('#service_boisson').prop('checked', true);
                $('input[name*="event_boisson_info"]').val(boissonInfo || '');
            }

            // Ouvrir le toggle si des données existent
            if (parking || pmr === 'oui' || restau === 'oui' || boisson === 'oui') {
                var $section = $('.services_toggle_section');
                if (!$section.hasClass('is-open')) {
                    $section.find('.el_toggle_content').slideDown(200);
                    $section.addClass('is-open');
                    $section.find('.el_toggle_header').attr('aria-expanded', 'true');
                    $('#services_enabled').val('yes');
                }
            }
        },

        clearLocationFields: function() {
            $('#location_address').val(null).trigger('change');
            // Reset GPS display but keep map visible
            $('#map_gps').val('');
        },

        clearServices: function() {
            // Décocher tous les services
            $('.service_toggle input[type="checkbox"]').prop('checked', false);
            // Vider les champs info
            $('.service_info_input').val('');
        },

        updateCoordinates: function(lat, lng) {
            $('#map_lat').val(parseFloat(lat).toFixed(6));
            $('#map_lng').val(parseFloat(lng).toFixed(6));
            $('#map_gps').val(parseFloat(lat).toFixed(6) + ', ' + parseFloat(lng).toFixed(6));
        },

        updateMapPosition: function(lat, lng) {
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], 15);
        },

        reverseGeocode: function(lat, lng) {
            var self = this;

            $.ajax({
                url: 'https://nominatim.openstreetmap.org/reverse',
                data: {
                    lat: lat,
                    lon: lng,
                    format: 'json',
                    'accept-language': 'fr'
                },
                success: function(data) {
                    if (data && data.display_name) {
                        var $addressSelect = $('#location_address');
                        $addressSelect.empty();
                        $addressSelect.append(new Option(data.display_name, data.display_name, true, true));
                        $addressSelect.trigger('change');
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
