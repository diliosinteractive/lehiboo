<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
$_prefix = OVA_METABOX_EVENT;

// Récupération des données existantes
$is_free_event = get_post_meta( $post_id, $_prefix.'is_free_event', true );
$ticket_global_type = get_post_meta( $post_id, $_prefix.'ticket_global_type', true );
$entry_type = get_post_meta( $post_id, $_prefix.'entry_type', true );
$contact_email = get_post_meta( $post_id, $_prefix.'contact_email', true );
$contact_phone = get_post_meta( $post_id, $_prefix.'contact_phone', true );

// Ne pas pré-remplir l'email/téléphone pour les nouveaux événements
// L'utilisateur doit les saisir explicitement

// Mode billetterie
$ticket_link = get_post_meta( $post_id, $_prefix.'ticket_link', true );
if ( empty( $ticket_link ) ) {
    $ticket_link = apply_filters( 'el_ticket_link_default', '' );
}

// Lien externe
$ticket_external_link = get_post_meta( $post_id, $_prefix.'ticket_external_link', true );
$external_prices = get_post_meta( $post_id, $_prefix.'ticket_external_prices', true );
if ( ! is_array( $external_prices ) ) {
    $external_prices = array();
}

// Créneaux associés
$slots_mode = get_post_meta( $post_id, $_prefix.'slots_mode', true );
if ( empty( $slots_mode ) ) {
    $slots_mode = 'all';
}
$selected_slots = get_post_meta( $post_id, $_prefix.'selected_slots', true );
if ( ! is_array( $selected_slots ) ) {
    $selected_slots = array();
}

// Billets
$tickets = get_post_meta( $post_id, $_prefix.'ticket', true );
if ( ! is_array( $tickets ) ) {
    $tickets = array();
}

// Options Gratuit/Payant
$price_types = array(
    '' => __( 'gratuit ou payant ?', 'eventlist' ),
    'free' => __( 'Gratuit', 'eventlist' ),
    'paid' => __( 'Payant', 'eventlist' ),
);

// Types d'entrée disponibles - depuis la taxonomie event_entry_type
$entry_types_terms = get_terms( array(
    'taxonomy'   => 'event_entry_type',
    'hide_empty' => false,
    'orderby'    => 'term_order',
    'order'      => 'ASC',
) );

$entry_types = array(
    '' => __( 'Sélectionnez le type d\'entrée', 'eventlist' ),
);

if ( ! is_wp_error( $entry_types_terms ) && ! empty( $entry_types_terms ) ) {
    foreach ( $entry_types_terms as $term ) {
        $entry_types[ $term->slug ] = $term->name;
    }
} else {
    // Fallback si la taxonomie est vide - valeurs par défaut
    $entry_types['acces_libre'] = __( 'Accès libre', 'eventlist' );
    $entry_types['acces_libre_reservation_conseillee'] = __( 'Accès libre avec réservation conseillée', 'eventlist' );
    $entry_types['sur_reservation_obligatoire'] = __( 'Sur réservation obligatoire', 'eventlist' );
    $entry_types['billetterie_sur_place_uniquement'] = __( 'Billetterie sur place uniquement', 'eventlist' );
    $entry_types['sur_invitation_uniquement'] = __( 'Sur invitation uniquement', 'eventlist' );
    $entry_types['non_specifie'] = __( 'Non spécifié', 'eventlist' );
}

// Récupérer les créneaux de l'événement pour la sélection
// V1 Le Hiboo - Utiliser le calendar_id natif ou en générer un
$event_slots = array();
$calendar_data = get_post_meta( $post_id, $_prefix.'calendar', true );
if ( ! empty( $calendar_data ) && is_array( $calendar_data ) ) {
    foreach ( $calendar_data as $slot ) {
        if ( isset( $slot['date'] ) || isset( $slot['start_date'] ) ) {
            $date = isset( $slot['date'] ) ? $slot['date'] : $slot['start_date'];
            $start_time = isset( $slot['start_time'] ) ? $slot['start_time'] : '';
            $end_time = isset( $slot['end_time'] ) ? $slot['end_time'] : '';

            // Utiliser le calendar_id existant ou en générer un
            $slot_id = isset( $slot['calendar_id'] ) && ! empty( $slot['calendar_id'] )
                ? $slot['calendar_id']
                : ( function_exists( 'el_generate_slot_id' ) ? el_generate_slot_id( $date, $start_time, $end_time ) : md5( $date . $start_time . $end_time ) );

            // Formater le label d'affichage
            $label = date_i18n( 'D j M Y', strtotime( $date ) );
            if ( $start_time ) {
                $label .= ' - ' . $start_time;
                if ( $end_time ) {
                    $label .= ' → ' . $end_time;
                }
            }

            $event_slots[] = array(
                'id'         => $slot_id,
                'label'      => $label,
                'date'       => $date,
                'start_time' => $start_time,
                'end_time'   => $end_time,
                'source'     => isset( $slot['source'] ) ? $slot['source'] : 'manual',
            );
        }
    }
}
?>

<div class="billetterie_section">
    <!-- En-tête -->
    <div class="section_header">
        <h4 class="section_title"><?php esc_html_e( 'Billetterie', 'eventlist' ); ?></h4>
        <p class="section_subtitle"><?php esc_html_e( 'Configurez les billets et tarifs pour votre événement', 'eventlist' ); ?></p>
    </div>

    <!-- Ligne 1 : Gratuit/Payant + Type d'entrée -->
    <div class="billetterie_row_2cols">
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'L\'événement est', 'eventlist' ); ?> :</strong> <span class="required">*</span></label>
            <div class="select_wrapper">
                <select name="<?php echo esc_attr( $_prefix.'ticket_global_type' ); ?>" class="billetterie_select" id="ticket_global_type_select" required>
                    <?php foreach ( $price_types as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $ticket_global_type, $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'Le type d\'entrée', 'eventlist' ); ?> :</strong> <span class="required">*</span></label>
            <div class="select_wrapper">
                <select name="<?php echo esc_attr( $_prefix.'entry_type' ); ?>" class="billetterie_select" id="entry_type_select" required>
                    <?php foreach ( $entry_types as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $entry_type, $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Ligne 2 : Email + Téléphone de contact -->
    <div class="billetterie_row_2cols">
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'E-mail de contact', 'eventlist' ); ?> :</strong></label>
            <input type="email"
                   name="<?php echo esc_attr( $_prefix.'contact_email' ); ?>"
                   value="<?php echo esc_attr( $contact_email ); ?>"
                   class="billetterie_input"
                   placeholder="email@exemple.com">
        </div>
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'Téléphone de contact', 'eventlist' ); ?> :</strong></label>
            <div class="phone_input_wrapper">
                <div class="phone_flag">
                    <span class="flag_icon">🇫🇷</span>
                    <span class="flag_arrow">▼</span>
                </div>
                <input type="tel"
                       name="<?php echo esc_attr( $_prefix.'contact_phone' ); ?>"
                       value="<?php echo esc_attr( $contact_phone ); ?>"
                       class="billetterie_input phone_input"
                       placeholder="06 12 34 56 78">
            </div>
        </div>
    </div>

    <!-- Texte explicatif -->
    <div class="billetterie_info_text">
        <p><?php esc_html_e( 'Gérez la billetterie (prochainement) ou les inscriptions directement sur LeHiboo, ou redirigez vos utilisateurs vers une plateforme externe si vous utilisez un outil tiers pour la billetterie.', 'eventlist' ); ?></p>
    </div>

    <!-- Options de billetterie -->
    <div class="billetterie_options">
        <label class="billetterie_option_card <?php echo $ticket_link === 'ticket_internal_link' || empty($ticket_link) ? 'selected' : ''; ?>">
            <input type="radio"
                   name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>"
                   value="ticket_internal_link"
                   class="option_radio"
                   <?php checked( $ticket_link, 'ticket_internal_link' ); ?>
                   <?php if ( empty($ticket_link) ) echo 'checked'; ?>>
            <span class="option_checkmark"></span>
            <div class="option_content">
                <span class="option_title"><?php esc_html_e( 'Utiliser le module de réservation', 'eventlist' ); ?></span>
                <span class="option_desc"><?php esc_html_e( 'Les utilisateurs vont pouvoir s\'inscrire gratuitement à votre événement. Gérez les inscriptions depuis votre espace LeHiboo.', 'eventlist' ); ?></span>
            </div>
        </label>

        <label class="billetterie_option_card <?php echo $ticket_link === 'ticket_external_link' ? 'selected' : ''; ?>">
            <input type="radio"
                   name="<?php echo esc_attr( $_prefix.'ticket_link' ); ?>"
                   value="ticket_external_link"
                   class="option_radio"
                   <?php checked( $ticket_link, 'ticket_external_link' ); ?>>
            <span class="option_checkmark"></span>
            <div class="option_content">
                <span class="option_title"><?php esc_html_e( 'Afficher un lien externe pour réserver', 'eventlist' ); ?></span>
                <span class="option_desc"><?php esc_html_e( 'Insérer un lien vers une plateforme de billetterie', 'eventlist' ); ?></span>
            </div>
        </label>
    </div>

    <!-- Section Module de réservation interne -->
    <div class="billetterie_internal_section" style="<?php echo ($ticket_link === 'ticket_internal_link' || empty($ticket_link)) ? '' : 'display: none;'; ?>">

        <!-- Toolbar accordéons -->
        <?php if ( ! empty( $tickets ) ) : ?>
        <div class="tickets_accordion_toolbar">
            <span class="toolbar_label"><?php esc_html_e( 'Billets', 'eventlist' ); ?> (<?php echo count( $tickets ); ?>)</span>
            <div class="toolbar_buttons">
                <button type="button" class="btn_expand_all toolbar_btn" title="<?php esc_attr_e( 'Tout déplier', 'eventlist' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="7 13 12 18 17 13"></polyline>
                        <polyline points="7 6 12 11 17 6"></polyline>
                    </svg>
                    <span><?php esc_html_e( 'Tout déplier', 'eventlist' ); ?></span>
                </button>
                <button type="button" class="btn_collapse_all toolbar_btn" title="<?php esc_attr_e( 'Tout replier', 'eventlist' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="17 11 12 6 7 11"></polyline>
                        <polyline points="17 18 12 13 7 18"></polyline>
                    </svg>
                    <span><?php esc_html_e( 'Tout replier', 'eventlist' ); ?></span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des billets -->
        <div class="tickets_list_wrapper" data-prefix="<?php echo esc_attr( $_prefix ); ?>">
            <?php
            $ticket_index = 0;
            foreach ( $tickets as $key => $ticket ) :
                if ( ! isset( $ticket['name_ticket'] ) || empty( $ticket['name_ticket'] ) ) continue;

                // Récupération des données du billet
                $ticket_name = isset( $ticket['name_ticket'] ) ? $ticket['name_ticket'] : '';
                $ticket_desc = isset( $ticket['desc_ticket'] ) ? $ticket['desc_ticket'] : '';
                $number_total = isset( $ticket['number_total_ticket'] ) ? $ticket['number_total_ticket'] : '';
                $number_min = isset( $ticket['number_min_ticket'] ) ? $ticket['number_min_ticket'] : 1;
                $number_max = isset( $ticket['number_max_ticket'] ) ? $ticket['number_max_ticket'] : '';
                $registration_mode = isset( $ticket['registration_mode'] ) ? $ticket['registration_mode'] : 'before_start';
                $minutes_before = isset( $ticket['minutes_before'] ) ? $ticket['minutes_before'] : 0;
                $reg_start_date = isset( $ticket['start_ticket_date'] ) ? $ticket['start_ticket_date'] : '';
                $reg_start_time = isset( $ticket['start_ticket_time'] ) ? $ticket['start_ticket_time'] : '00:00';
                $reg_end_date = isset( $ticket['close_ticket_date'] ) ? $ticket['close_ticket_date'] : '';
                $reg_end_time = isset( $ticket['close_ticket_time'] ) ? $ticket['close_ticket_time'] : '23:59';
                $is_active = isset( $ticket['is_active'] ) ? $ticket['is_active'] : 'yes';

                // V1 Le Hiboo - Mapping Ticket ↔ Créneau
                $ticket_slots_mode = isset( $ticket['slots_mode'] ) ? $ticket['slots_mode'] : 'all';
                $ticket_slots = isset( $ticket['slots'] ) && is_array( $ticket['slots'] ) ? $ticket['slots'] : array();
            ?>
                <div class="ticket_form_item" data-index="<?php echo esc_attr( $key ); ?>">
                    <div class="ticket_form_content">
                        <!-- Header avec titre et badge - Cliquable pour accordéon -->
                        <div class="ticket_form_header ticket_accordion_header" role="button" aria-expanded="false" tabindex="0">
                            <div class="ticket_header_left">
                                <button type="button" class="ticket_accordion_toggle" aria-label="<?php esc_attr_e( 'Déplier/Replier', 'eventlist' ); ?>">
                                    <svg class="accordion_chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                                <h4 class="ticket_title"><?php echo esc_html( $ticket_name ); ?></h4>
                            </div>
                            <?php
                            // Calculer le label du badge
                            if ( $ticket_slots_mode === 'all' ) {
                                $slots_badge_label = __( 'Tous les créneaux', 'eventlist' );
                            } else {
                                $slots_count = count( $ticket_slots );
                                $slots_badge_label = sprintf( _n( '%d créneau sélectionné', '%d créneaux sélectionnés', $slots_count, 'eventlist' ), $slots_count );
                            }
                            ?>
                            <span class="ticket_slots_badge"><?php echo esc_html( $slots_badge_label ); ?></span>
                        </div>

                        <!-- Contenu accordéon (replié par défaut) -->
                        <div class="ticket_accordion_body" style="display: none;">

                        <!-- Nom du billet -->
                        <div class="ticket_form_field">
                            <label class="field_label"><strong><?php esc_html_e( 'Nom du billet', 'eventlist' ); ?></strong> <span class="required">*</span> :</label>
                            <input type="text"
                                   name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][name_ticket]' ); ?>"
                                   value="<?php echo esc_attr( $ticket_name ); ?>"
                                   class="billetterie_input ticket_name_input"
                                   placeholder="<?php esc_attr_e( 'Réservation des Petits Pouces du 5 Décembre', 'eventlist' ); ?>"
                                   required>
                        </div>

                        <!-- Description du billet -->
                        <div class="ticket_form_field">
                            <label class="field_label"><strong><?php esc_html_e( 'Description du billet', 'eventlist' ); ?></strong></label>
                            <p class="field_hint"><?php esc_html_e( 'Cette description sera affichée sur la page de l\'activité au niveau du billet, et également sur la version PDF du billet.', 'eventlist' ); ?> :</p>
                            <textarea name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][desc_ticket]' ); ?>"
                                      class="billetterie_textarea"
                                      rows="3"
                                      placeholder="<?php esc_attr_e( 'Description du billet...', 'eventlist' ); ?>"><?php echo esc_textarea( $ticket_desc ); ?></textarea>
                        </div>

                        <!-- Nombre de places -->
                        <div class="ticket_form_row_3cols">
                            <div class="ticket_form_field">
                                <label class="field_label"><strong><?php esc_html_e( 'Nombre total de places', 'eventlist' ); ?></strong> :</label>
                                <input type="number"
                                       name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_total_ticket]' ); ?>"
                                       value="<?php echo esc_attr( $number_total ); ?>"
                                       class="billetterie_input"
                                       min="1"
                                       placeholder="20">
                            </div>
                            <div class="ticket_form_field">
                                <label class="field_label"><strong><?php esc_html_e( 'Nombre minimum de place autorisé par réservation', 'eventlist' ); ?></strong> :</label>
                                <input type="number"
                                       name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_min_ticket]' ); ?>"
                                       value="<?php echo esc_attr( $number_min ); ?>"
                                       class="billetterie_input"
                                       min="1"
                                       placeholder="1">
                            </div>
                            <div class="ticket_form_field">
                                <label class="field_label"><strong><?php esc_html_e( 'Nombre maximum de places autorisé par réservation', 'eventlist' ); ?></strong> :</label>
                                <input type="number"
                                       name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][number_max_ticket]' ); ?>"
                                       value="<?php echo esc_attr( $number_max ); ?>"
                                       class="billetterie_input"
                                       min="1"
                                       placeholder="">
                            </div>
                        </div>

                        <!-- Période d'inscription -->
                        <div class="ticket_form_field">
                            <label class="field_label"><strong><?php esc_html_e( 'Période d\'inscription', 'eventlist' ); ?></strong> :</label>

                            <div class="registration_period_options">
                                <label class="registration_option <?php echo $registration_mode === 'before_start' ? 'selected' : ''; ?>">
                                    <input type="radio"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][registration_mode]' ); ?>"
                                           value="before_start"
                                           class="registration_radio"
                                           <?php checked( $registration_mode, 'before_start' ); ?>>
                                    <span class="registration_checkmark"></span>
                                    <span class="registration_text">
                                        <?php esc_html_e( 'Les réservations sont ouvertes jusqu\'à', 'eventlist' ); ?>
                                        <input type="number"
                                               name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][minutes_before]' ); ?>"
                                               value="<?php echo esc_attr( $minutes_before ); ?>"
                                               class="minutes_input"
                                               min="0"
                                               placeholder="0">
                                        <?php esc_html_e( 'minute(s) avant le début de l\'activité', 'eventlist' ); ?>
                                    </span>
                                </label>

                                <label class="registration_option <?php echo $registration_mode === 'date_range' ? 'selected' : ''; ?>">
                                    <input type="radio"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][registration_mode]' ); ?>"
                                           value="date_range"
                                           class="registration_radio"
                                           <?php checked( $registration_mode, 'date_range' ); ?>>
                                    <span class="registration_checkmark"></span>
                                    <span class="registration_text"><?php esc_html_e( 'Les réservations sont ouvertes à partir du', 'eventlist' ); ?></span>
                                </label>
                            </div>

                            <div class="registration_date_range" style="<?php echo $registration_mode === 'date_range' ? '' : 'display: none;'; ?>">
                                <div class="date_range_row">
                                    <input type="date"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][start_ticket_date]' ); ?>"
                                           value="<?php echo esc_attr( $reg_start_date ); ?>"
                                           class="billetterie_input date_input"
                                           placeholder="JJ/MM/AAAA">
                                    <input type="time"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][start_ticket_time]' ); ?>"
                                           value="<?php echo esc_attr( $reg_start_time ); ?>"
                                           class="billetterie_input time_input">
                                </div>
                                <span class="date_range_separator"><?php esc_html_e( 'jusqu\'au', 'eventlist' ); ?></span>
                                <div class="date_range_row">
                                    <input type="date"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][close_ticket_date]' ); ?>"
                                           value="<?php echo esc_attr( $reg_end_date ); ?>"
                                           class="billetterie_input date_input"
                                           placeholder="JJ/MM/AAAA">
                                    <input type="time"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][close_ticket_time]' ); ?>"
                                           value="<?php echo esc_attr( $reg_end_time ); ?>"
                                           class="billetterie_input time_input">
                                </div>
                            </div>
                        </div>

                        <!-- V1 Le Hiboo - Sélection des créneaux -->
                        <?php if ( ! empty( $event_slots ) ) : ?>
                        <div class="ticket_form_field ticket_slots_section">
                            <label class="field_label"><strong><?php esc_html_e( 'Créneaux associés', 'eventlist' ); ?></strong> :</label>
                            <p class="field_hint"><?php esc_html_e( 'Ce billet est disponible pour quels créneaux ?', 'eventlist' ); ?></p>

                            <div class="slots_mode_options">
                                <label class="slots_mode_option <?php echo $ticket_slots_mode === 'all' ? 'selected' : ''; ?>">
                                    <input type="radio"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][slots_mode]' ); ?>"
                                           value="all"
                                           class="slots_mode_radio"
                                           <?php checked( $ticket_slots_mode, 'all' ); ?>>
                                    <span class="slots_mode_checkmark"></span>
                                    <span class="slots_mode_text"><?php esc_html_e( 'Tous les créneaux', 'eventlist' ); ?></span>
                                </label>

                                <label class="slots_mode_option <?php echo $ticket_slots_mode === 'selected' ? 'selected' : ''; ?>">
                                    <input type="radio"
                                           name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][slots_mode]' ); ?>"
                                           value="selected"
                                           class="slots_mode_radio"
                                           <?php checked( $ticket_slots_mode, 'selected' ); ?>>
                                    <span class="slots_mode_checkmark"></span>
                                    <span class="slots_mode_text"><?php esc_html_e( 'Créneaux spécifiques', 'eventlist' ); ?></span>
                                </label>
                            </div>

                            <div class="slots_checkboxes_wrapper" style="<?php echo $ticket_slots_mode === 'selected' ? '' : 'display: none;'; ?>">
                                <!-- Barre de recherche -->
                                <div class="ticket_slots_search_bar">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                    <input type="text" class="ticket_slots_search_input" placeholder="<?php esc_attr_e( 'Rechercher un créneau...', 'eventlist' ); ?>" data-ticket-index="<?php echo esc_attr( $key ); ?>">
                                </div>

                                <!-- Filtres de date -->
                                <div class="ticket_slots_date_filters">
                                    <button type="button" class="ticket_slots_filter_btn active" data-filter="all" data-ticket-index="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Tout', 'eventlist' ); ?></button>
                                    <button type="button" class="ticket_slots_filter_btn" data-filter="this_week" data-ticket-index="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Cette semaine', 'eventlist' ); ?></button>
                                    <button type="button" class="ticket_slots_filter_btn" data-filter="this_month" data-ticket-index="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Ce mois', 'eventlist' ); ?></button>
                                    <button type="button" class="ticket_slots_filter_btn" data-filter="next_month" data-ticket-index="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Mois prochain', 'eventlist' ); ?></button>
                                </div>

                                <div class="slots_checkboxes_grid">
                                    <?php foreach ( $event_slots as $slot ) :
                                        $is_checked = in_array( $slot['id'], $ticket_slots, true );
                                    ?>
                                    <label class="slot_checkbox_item <?php echo $is_checked ? 'is_checked' : ''; ?>" data-slot-date="<?php echo esc_attr( $slot['date'] ); ?>">
                                        <input type="checkbox"
                                               name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][slots][]' ); ?>"
                                               value="<?php echo esc_attr( $slot['id'] ); ?>"
                                               class="slot_checkbox"
                                               <?php checked( $is_checked ); ?>>
                                        <span class="slot_checkbox_mark"></span>
                                        <span class="slot_checkbox_label"><?php echo esc_html( $slot['label'] ); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>

                                <p class="slots_no_results" style="display: none;">
                                    <?php esc_html_e( 'Aucun créneau ne correspond aux critères.', 'eventlist' ); ?>
                                </p>

                                <p class="slots_validation_hint" style="display: none; color: #dc3545; margin-top: 8px;">
                                    <?php esc_html_e( 'Veuillez sélectionner au moins un créneau.', 'eventlist' ); ?>
                                </p>
                            </div>
                        </div>
                        <?php else : ?>
                        <!-- Pas de créneaux définis, mode "all" par défaut -->
                        <input type="hidden"
                               name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][slots_mode]' ); ?>"
                               value="all">
                        <?php endif; ?>

                        <!-- Champ caché pour le statut actif -->
                        <input type="hidden"
                               name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][is_active]' ); ?>"
                               value="<?php echo esc_attr( $is_active ); ?>"
                               class="ticket_is_active">

                        </div><!-- Fin ticket_accordion_body -->

                        <!-- Boutons d'action -->
                        <div class="ticket_form_actions">
                            <div class="ticket_actions_left">
                                <button type="button" class="btn_save_ticket el_btn_save">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    <span class="btn_text"><?php esc_html_e( 'Sauvegarder ce billet', 'eventlist' ); ?></span>
                                </button>
                                <button type="button" class="btn_stop_reservation el_btn_warning" data-index="<?php echo esc_attr( $key ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="10" y1="15" x2="10" y2="9"></line>
                                        <line x1="14" y1="15" x2="14" y2="9"></line>
                                    </svg>
                                    <span class="btn_text"><?php esc_html_e( 'Stopper la réservation', 'eventlist' ); ?></span>
                                </button>
                            </div>
                            <button type="button" class="btn_delete_ticket el_btn_danger" data-index="<?php echo esc_attr( $key ); ?>" data-ticket-name="<?php echo esc_attr( $ticket_name ); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span class="btn_text"><?php esc_html_e( 'Supprimer', 'eventlist' ); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                $ticket_index++;
            endforeach;
            ?>
        </div>

        <!-- Bouton Ajouter un billet -->
        <button type="button" class="btn_add_ticket el_btn_add">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span class="btn_text"><?php esc_html_e( 'Ajouter un autre billet', 'eventlist' ); ?></span>
        </button>

        <!-- Modal Wizard Ajout Billet -->
        <div class="ticket_wizard_overlay" style="display: none;">
            <div class="ticket_wizard_modal">
                <button type="button" class="wizard_close_btn" aria-label="<?php esc_attr_e( 'Fermer', 'eventlist' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <div class="wizard_header">
                    <h3 class="wizard_title"><?php esc_html_e( 'Créer un billet', 'eventlist' ); ?></h3>

                    <!-- Barre de progression -->
                    <div class="wizard_progress">
                        <div class="progress_step active" data-step="1">
                            <span class="step_number">1</span>
                            <span class="step_label"><?php esc_html_e( 'Infos', 'eventlist' ); ?></span>
                        </div>
                        <div class="progress_line"></div>
                        <div class="progress_step" data-step="2">
                            <span class="step_number">2</span>
                            <span class="step_label"><?php esc_html_e( 'Capacité', 'eventlist' ); ?></span>
                        </div>
                        <div class="progress_line"></div>
                        <div class="progress_step" data-step="3">
                            <span class="step_number">3</span>
                            <span class="step_label"><?php esc_html_e( 'Période', 'eventlist' ); ?></span>
                        </div>
                        <div class="progress_line"></div>
                        <div class="progress_step" data-step="4">
                            <span class="step_number">4</span>
                            <span class="step_label"><?php esc_html_e( 'Créneaux', 'eventlist' ); ?></span>
                        </div>
                    </div>
                </div>

                <div class="wizard_body">
                    <!-- Étape 1 : Informations -->
                    <div class="wizard_step active" data-step="1">
                        <div class="step_header">
                            <span class="step_indicator"><?php esc_html_e( 'Étape 1/4', 'eventlist' ); ?></span>
                            <h4 class="step_title"><?php esc_html_e( 'Informations essentielles', 'eventlist' ); ?></h4>
                        </div>

                        <div class="wizard_field">
                            <label class="wizard_label">
                                <?php esc_html_e( 'Nom du billet', 'eventlist' ); ?> <span class="required">*</span>
                            </label>
                            <input type="text" class="wizard_input wizard_ticket_name" placeholder="<?php esc_attr_e( 'Ex: Entrée adulte, Pass famille...', 'eventlist' ); ?>" required>
                        </div>

                        <div class="wizard_field">
                            <label class="wizard_label">
                                <?php esc_html_e( 'Description', 'eventlist' ); ?>
                                <span class="label_hint"><?php esc_html_e( '(optionnel)', 'eventlist' ); ?></span>
                            </label>
                            <textarea class="wizard_textarea wizard_ticket_desc" rows="3" placeholder="<?php esc_attr_e( 'Décrivez ce billet...', 'eventlist' ); ?>"></textarea>
                            <p class="field_help"><?php esc_html_e( 'Affiché sur la page et le PDF du billet', 'eventlist' ); ?></p>
                        </div>
                    </div>

                    <!-- Étape 2 : Capacité -->
                    <div class="wizard_step" data-step="2">
                        <div class="step_header">
                            <span class="step_indicator"><?php esc_html_e( 'Étape 2/4', 'eventlist' ); ?></span>
                            <h4 class="step_title"><?php esc_html_e( 'Capacité & limites', 'eventlist' ); ?></h4>
                        </div>

                        <div class="wizard_fields_row">
                            <div class="wizard_field">
                                <label class="wizard_label"><?php esc_html_e( 'Places totales', 'eventlist' ); ?></label>
                                <input type="number" class="wizard_input wizard_input_centered wizard_total_places" min="1" placeholder="20">
                                <p class="field_help"><?php esc_html_e( 'Vide = illimité', 'eventlist' ); ?></p>
                            </div>
                            <div class="wizard_field">
                                <label class="wizard_label"><?php esc_html_e( 'Min / réservation', 'eventlist' ); ?></label>
                                <input type="number" class="wizard_input wizard_input_centered wizard_min_places" min="1" value="1">
                            </div>
                            <div class="wizard_field">
                                <label class="wizard_label"><?php esc_html_e( 'Max / réservation', 'eventlist' ); ?></label>
                                <input type="number" class="wizard_input wizard_input_centered wizard_max_places" min="1" placeholder="">
                                <p class="field_help"><?php esc_html_e( 'Vide = pas de limite', 'eventlist' ); ?></p>
                            </div>
                        </div>

                        <div class="wizard_info_box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span><?php esc_html_e( 'Ces limites s\'appliquent à chaque réservation individuelle', 'eventlist' ); ?></span>
                        </div>
                    </div>

                    <!-- Étape 3 : Période -->
                    <div class="wizard_step" data-step="3">
                        <div class="step_header">
                            <span class="step_indicator"><?php esc_html_e( 'Étape 3/4', 'eventlist' ); ?></span>
                            <h4 class="step_title"><?php esc_html_e( 'Période d\'inscription', 'eventlist' ); ?></h4>
                        </div>

                        <div class="wizard_period_options">
                            <label class="wizard_radio_option selected">
                                <input type="radio" name="wizard_registration_mode" value="before_start" checked>
                                <span class="radio_mark"></span>
                                <span class="radio_content">
                                    <span class="radio_title"><?php esc_html_e( 'Jusqu\'au début de l\'activité', 'eventlist' ); ?></span>
                                    <span class="radio_inline">
                                        <?php esc_html_e( 'Fermer', 'eventlist' ); ?>
                                        <input type="number" class="wizard_inline_input wizard_minutes_before" value="0" min="0">
                                        <?php esc_html_e( 'minute(s) avant', 'eventlist' ); ?>
                                    </span>
                                </span>
                            </label>

                            <label class="wizard_radio_option">
                                <input type="radio" name="wizard_registration_mode" value="date_range">
                                <span class="radio_mark"></span>
                                <span class="radio_content">
                                    <span class="radio_title"><?php esc_html_e( 'Période personnalisée', 'eventlist' ); ?></span>
                                </span>
                            </label>
                        </div>

                        <div class="wizard_date_range" style="display: none;">
                            <div class="date_range_group">
                                <label><?php esc_html_e( 'Ouverture', 'eventlist' ); ?></label>
                                <div class="date_time_row">
                                    <input type="date" class="wizard_input wizard_start_date">
                                    <input type="time" class="wizard_input wizard_start_time" value="00:00">
                                </div>
                            </div>
                            <div class="date_range_arrow">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </div>
                            <div class="date_range_group">
                                <label><?php esc_html_e( 'Fermeture', 'eventlist' ); ?></label>
                                <div class="date_time_row">
                                    <input type="date" class="wizard_input wizard_end_date">
                                    <input type="time" class="wizard_input wizard_end_time" value="23:59">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Étape 4 : Créneaux -->
                    <div class="wizard_step" data-step="4">
                        <div class="step_header">
                            <span class="step_indicator"><?php esc_html_e( 'Étape 4/4', 'eventlist' ); ?></span>
                            <h4 class="step_title"><?php esc_html_e( 'Créneaux associés', 'eventlist' ); ?></h4>
                        </div>

                        <div class="wizard_slots_mode">
                            <label class="wizard_radio_option selected">
                                <input type="radio" name="wizard_slots_mode" value="all" checked>
                                <span class="radio_mark"></span>
                                <span class="radio_content">
                                    <span class="radio_title"><?php esc_html_e( 'Tous les créneaux', 'eventlist' ); ?></span>
                                </span>
                            </label>

                            <label class="wizard_radio_option">
                                <input type="radio" name="wizard_slots_mode" value="selected">
                                <span class="radio_mark"></span>
                                <span class="radio_content">
                                    <span class="radio_title"><?php esc_html_e( 'Créneaux spécifiques', 'eventlist' ); ?></span>
                                </span>
                            </label>
                        </div>

                        <!-- Multi-select créneaux -->
                        <div class="wizard_slots_picker" style="display: none;">
                            <?php if ( ! empty( $event_slots ) ) : ?>
                            <div class="slots_search_bar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input type="text" class="slots_search_input" placeholder="<?php esc_attr_e( 'Rechercher un créneau...', 'eventlist' ); ?>">
                            </div>

                            <!-- Filtres par période -->
                            <div class="slots_date_filters">
                                <button type="button" class="slots_filter_btn active" data-filter="all">
                                    <?php esc_html_e( 'Tout', 'eventlist' ); ?>
                                </button>
                                <button type="button" class="slots_filter_btn" data-filter="this_week">
                                    <?php esc_html_e( 'Cette semaine', 'eventlist' ); ?>
                                </button>
                                <button type="button" class="slots_filter_btn" data-filter="this_month">
                                    <?php esc_html_e( 'Ce mois', 'eventlist' ); ?>
                                </button>
                                <button type="button" class="slots_filter_btn" data-filter="next_month">
                                    <?php esc_html_e( 'Mois prochain', 'eventlist' ); ?>
                                </button>
                            </div>

                            <div class="slots_checklist">
                                <?php foreach ( $event_slots as $slot ) : ?>
                                <div class="slot_check_item" data-slot-id="<?php echo esc_attr( $slot['id'] ); ?>" data-slot-date="<?php echo esc_attr( $slot['date'] ); ?>">
                                    <input type="checkbox" class="slot_checkbox_input" value="<?php echo esc_attr( $slot['id'] ); ?>" id="slot_<?php echo esc_attr( $slot['id'] ); ?>">
                                    <label class="slot_check_label" for="slot_<?php echo esc_attr( $slot['id'] ); ?>">
                                        <span class="slot_checkbox_custom"></span>
                                        <span class="slot_date"><?php echo esc_html( date_i18n( 'D j M Y', strtotime( $slot['date'] ) ) ); ?></span>
                                        <span class="slot_time"><?php echo esc_html( $slot['start_time'] . ' → ' . $slot['end_time'] ); ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="slots_quick_actions">
                                <button type="button" class="slots_action_btn slots_select_all">
                                    <?php esc_html_e( 'Tout sélectionner', 'eventlist' ); ?>
                                </button>
                                <button type="button" class="slots_action_btn slots_deselect_all">
                                    <?php esc_html_e( 'Tout désélectionner', 'eventlist' ); ?>
                                </button>
                            </div>

                            <div class="slots_chips_container">
                                <span class="chips_label"><?php esc_html_e( 'Sélectionnés', 'eventlist' ); ?> (<span class="chips_count">0</span>) :</span>
                                <div class="slots_chips_list"></div>
                            </div>
                            <?php else : ?>
                            <div class="slots_empty_message">
                                <p><?php esc_html_e( 'Aucun créneau défini pour cet événement.', 'eventlist' ); ?></p>
                                <p class="hint"><?php esc_html_e( 'Ajoutez d\'abord des créneaux dans la section Calendrier.', 'eventlist' ); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="wizard_footer">
                    <button type="button" class="wizard_btn wizard_btn_cancel">
                        <?php esc_html_e( 'Annuler', 'eventlist' ); ?>
                    </button>
                    <div class="wizard_nav_buttons">
                        <button type="button" class="wizard_btn wizard_btn_prev" style="display: none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            <?php esc_html_e( 'Précédent', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="wizard_btn wizard_btn_next wizard_btn_primary">
                            <?php esc_html_e( 'Suivant', 'eventlist' ); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                        <button type="button" class="wizard_btn wizard_btn_submit wizard_btn_primary" style="display: none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <?php esc_html_e( 'Créer le billet', 'eventlist' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Lien externe - V1 Le Hiboo Simplifiée -->
    <?php
    // V1 Le Hiboo - Récupérer la description externe
    $external_description = get_post_meta( $post_id, $_prefix.'ticket_external_description', true );
    ?>
    <div class="billetterie_external_section" style="<?php echo $ticket_link === 'ticket_external_link' ? '' : 'display: none;'; ?>">

        <!-- Lien URL -->
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'Lien URL de réservation', 'eventlist' ); ?></strong> <span class="required">*</span></label>
            <p class="field_hint"><?php esc_html_e( 'Insérez le lien vers votre billetterie externe (Billetweb, Weezevent, Eventbrite, etc.)', 'eventlist' ); ?></p>
            <input type="url"
                   name="<?php echo esc_attr( $_prefix.'ticket_external_link' ); ?>"
                   value="<?php echo esc_url( $ticket_external_link ); ?>"
                   class="billetterie_input"
                   placeholder="https://billetweb.fr/votre-evenement">
        </div>

        <!-- Description / Explication -->
        <div class="billetterie_field">
            <label class="field_label"><strong><?php esc_html_e( 'Informations complémentaires', 'eventlist' ); ?></strong></label>
            <p class="field_hint"><?php esc_html_e( 'Expliquez aux visiteurs comment réserver (tarifs indicatifs, modalités, etc.)', 'eventlist' ); ?></p>
            <textarea name="<?php echo esc_attr( $_prefix.'ticket_external_description' ); ?>"
                      class="billetterie_textarea"
                      rows="4"
                      placeholder="<?php esc_attr_e( 'Ex: Billets disponibles sur notre partenaire Billetweb. Tarif adulte : 15€, Tarif enfant : 8€. Réservation conseillée.', 'eventlist' ); ?>"><?php echo esc_textarea( $external_description ); ?></textarea>
        </div>

        <div class="external_info_box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <p><?php esc_html_e( 'Les visiteurs seront redirigés vers votre billetterie externe pour effectuer leur réservation. Vous gérez les inscriptions et paiements directement sur cette plateforme.', 'eventlist' ); ?></p>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Billetterie Section - Styles
   ========================================================================== */

.billetterie_section {
    padding: 0;
}

.billetterie_section .section_header {
    margin-bottom: 30px;
}

.billetterie_section .section_title {
    font-size: 24px;
    font-weight: 700;
    color: #1a365d;
    margin: 0 0 8px;
}

.billetterie_section .section_subtitle {
    font-size: 15px;
    color: #64748b;
    margin: 0;
}

/* Row Layout - 2 columns */
.billetterie_row_2cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

/* Fields */
.billetterie_field {
    margin-bottom: 0;
}

.billetterie_field .field_label {
    display: block;
    font-size: 14px;
    color: #334155;
    margin-bottom: 10px;
}

.billetterie_field .field_label .required {
    color: #ef4444;
    font-weight: 600;
}

.billetterie_field .field_hint,
.billetterie_section .field_hint {
    font-size: 13px;
    color: #94a3b8;
    font-style: italic;
    margin: 0 0 8px;
}

/* Inputs */
.billetterie_input,
.billetterie_select,
.billetterie_textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #334155;
    background: #fff;
    transition: all 0.2s;
}

.billetterie_input:focus,
.billetterie_select:focus,
.billetterie_textarea:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.billetterie_input::placeholder,
.billetterie_textarea::placeholder {
    color: #94a3b8;
    font-style: italic;
}

.select_wrapper {
    position: relative;
}

.select_wrapper::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

.billetterie_select {
    appearance: none;
    padding-right: 40px;
    cursor: pointer;
}

/* Phone Input with Flag */
.phone_input_wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    transition: all 0.2s;
}

.phone_input_wrapper:focus-within {
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.phone_flag {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 12px 12px 12px 16px;
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    cursor: pointer;
}

.phone_flag .flag_icon {
    font-size: 18px;
}

.phone_flag .flag_arrow {
    font-size: 10px;
    color: #64748b;
}

.phone_input_wrapper .phone_input {
    border: none;
    border-radius: 0;
    flex: 1;
}

.phone_input_wrapper .phone_input:focus {
    box-shadow: none;
}

/* Price Choice Cards (Gratuit/Payant) */
.billetterie_price_choice {
    display: flex;
    gap: 16px;
}

.price_choice_card {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}

.price_choice_card:hover {
    border-color: #FF6600;
    background: #fff8f5;
}

.price_choice_card.selected {
    border-color: #FF6600;
    background: #fff8f5;
}

.price_choice_card .price_choice_input {
    display: none;
}

.price_choice_card .price_choice_checkmark {
    width: 22px;
    height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.price_choice_card.selected .price_choice_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.price_choice_card.selected .price_choice_checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 12px;
    color: #fff;
}

.price_choice_label {
    font-size: 15px;
    font-weight: 500;
    color: #334155;
}

/* Contact Row */
.billetterie_contact_row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.contact_field .field_label {
    display: block;
    font-size: 14px;
    color: #334155;
    margin-bottom: 4px;
}

.contact_field .field_hint {
    font-size: 13px;
    color: #94a3b8;
    font-style: italic;
    margin: 0 0 8px;
}

/* Info Text */
.billetterie_info_text {
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 24px;
}

.billetterie_info_text p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* Option Cards (Module réservation / Lien externe) */
.billetterie_options {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.billetterie_option_card {
    flex: 1;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}

.billetterie_option_card:hover {
    border-color: #FF6600;
}

.billetterie_option_card.selected {
    border-color: #FF6600;
    background: #fff8f5;
}

.billetterie_option_card .option_radio {
    display: none;
}

.billetterie_option_card .option_checkmark {
    width: 22px;
    height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-top: 2px;
}

.billetterie_option_card.selected .option_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.billetterie_option_card.selected .option_checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 12px;
    color: #fff;
}

.option_content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.option_title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.option_desc {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}

/* Subsection */
.subsection_title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 6px;
}

.subsection_hint {
    font-size: 13px;
    color: #64748b;
    margin: 0 0 16px;
}

/* Créneaux Associés */
.creneaux_associes_wrapper {
    padding: 24px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 30px;
}

.slots_selection {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.slots_option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}

.slots_option:hover {
    border-color: #FF6600;
}

.slots_option.selected {
    border-color: #FF6600;
    background: #fff8f5;
}

.slots_option .slots_radio {
    display: none;
}

.slots_option .slots_checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.slots_option.selected .slots_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.slots_option.selected .slots_checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 10px;
    color: #fff;
}

.slots_label {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
}

/* Slots Picker */
.slots_picker {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.slots_picker_row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.slots_picker_row:last-child {
    margin-bottom: 0;
}

.slots_picker_field {
    flex: 1;
}

.slots_picker_field label {
    display: block;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 6px;
}

.slots_picker_field_select {
    flex: 2;
}

.slots_picker_field_btn {
    flex: 0 0 auto;
    display: flex;
    align-items: flex-end;
}

.slots_picker_field_btn .btn_add_slot {
    height: 44px;
    padding: 0 24px;
}

/* Selected Slots List */
.selected_slots_list {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.selected_slot_item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: #e0f2fe;
    border-radius: 6px;
}

.selected_slot_item .slot_info {
    font-size: 13px;
    color: #0369a1;
}

.selected_slot_item .btn_remove_slot {
    width: 20px;
    height: 20px;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.selected_slot_item .btn_remove_slot:hover {
    background: #f1f5f9;
    color: #ef4444;
}

/* Tickets Accordion Toolbar */
.tickets_accordion_toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 16px;
}

.toolbar_label {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.toolbar_buttons {
    display: flex;
    gap: 8px;
}

.toolbar_btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.toolbar_btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
}

.toolbar_btn svg {
    flex-shrink: 0;
}

/* Tickets List */
.tickets_list_wrapper {
    margin-bottom: 24px;
}

.ticket_form_item {
    background-color: #fff8f6;
    border: 1px solid #ff6602;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
}

.ticket_form_content {
    padding: 24px;
}

.ticket_form_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 0;
}

/* Accordion Header - Cliquable */
.ticket_accordion_header {
    cursor: pointer;
    user-select: none;
    transition: background 0.2s ease;
    margin: -24px -24px 0 -24px;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.ticket_accordion_header:hover {
    background: #f8fafc;
}

.ticket_header_left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ticket_accordion_toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.ticket_accordion_toggle:hover {
    background: #e2e8f0;
}

.accordion_chevron {
    transition: transform 0.3s ease;
    color: #64748b;
}

/* État ouvert */
.ticket_form_item.is_expanded .accordion_chevron {
    transform: rotate(180deg);
}

.ticket_form_item.is_expanded .ticket_accordion_header {
    background: #fff7ed;
    border-color: #fdba74;
}

.ticket_form_item.is_expanded .ticket_accordion_toggle {
    background: #FF6600;
}

.ticket_form_item.is_expanded .accordion_chevron {
    color: #fff;
}

/* Contenu accordéon */
.ticket_accordion_body {
    padding-top: 20px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ticket_title {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.ticket_slots_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f0fdf4;
    color: #15803d;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.ticket_slots_badge::before {
    content: '📅';
    font-size: 12px;
}

/* Slots Mode Options */
.slots_mode_options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}

.slots_mode_option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.slots_mode_option:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.slots_mode_option.selected {
    background: #fff7ed;
    border-color: #FF6600;
}

.slots_mode_option .slots_mode_radio {
    display: none;
}

.slots_mode_option .slots_mode_checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.slots_mode_option.selected .slots_mode_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.slots_mode_option.selected .slots_mode_checkmark::after {
    content: '';
    width: 8px;
    height: 8px;
    background: #fff;
    border-radius: 50%;
}

.slots_mode_text {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
}

/* Slots Checkboxes Wrapper */
.slots_checkboxes_wrapper {
    margin-top: 16px;
    padding: 16px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

/* Ticket Slots Search Bar */
.ticket_slots_search_bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 12px;
}

.ticket_slots_search_bar svg {
    color: #94a3b8;
    flex-shrink: 0;
}

.ticket_slots_search_input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14px;
    color: #334155;
    outline: none;
}

.ticket_slots_search_input::placeholder {
    color: #94a3b8;
}

/* Ticket Slots Date Filters */
.ticket_slots_date_filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.ticket_slots_filter_btn {
    padding: 6px 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.ticket_slots_filter_btn:hover {
    background: #e2e8f0;
    color: #334155;
}

.ticket_slots_filter_btn.active {
    background: linear-gradient(135deg, #FF6600 0%, #e55c00 100%);
    border-color: #FF6600;
    color: #fff;
}

/* Slots Checkboxes Grid */
.slots_checkboxes_grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 8px;
    max-height: 320px;
    overflow-y: auto;
    padding-right: 4px;
}

.slots_checkboxes_grid::-webkit-scrollbar {
    width: 6px;
}

.slots_checkboxes_grid::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.slots_checkboxes_grid::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.slots_checkboxes_grid::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Slot Checkbox Item */
.slot_checkbox_item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.slot_checkbox_item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.slot_checkbox_item.is_checked {
    background: linear-gradient(135deg, #fff8f5 0%, #fff5f0 100%);
    border-left: 3px solid #FF6600;
    border-color: #fdba74;
}

.slot_checkbox_item .slot_checkbox {
    display: none;
}

.slot_checkbox_item .slot_checkbox_mark {
    width: 18px;
    height: 18px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
    background: #fff;
}

.slot_checkbox_item.is_checked .slot_checkbox_mark {
    background: #FF6600;
    border-color: #FF6600;
}

.slot_checkbox_item.is_checked .slot_checkbox_mark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 10px;
    color: #fff;
}

.slot_checkbox_label {
    font-size: 13px;
    color: #334155;
    flex: 1;
}

.slot_checkbox_item.is_checked .slot_checkbox_label {
    color: #ea580c;
    font-weight: 500;
}

/* No Results Message */
.slots_no_results {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
    font-size: 14px;
    font-style: italic;
}

.ticket_form_field {
    margin-bottom: 20px;
}

.ticket_form_field:last-of-type {
    margin-bottom: 24px;
}

.ticket_form_field .field_label {
    display: block;
    font-size: 14px;
    color: #334155;
    margin-bottom: 8px;
}

.ticket_form_field .field_label .required {
    color: #ef4444;
}

.ticket_form_row_3cols {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

/* Registration Period */
.registration_period_options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.registration_option {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.registration_option .registration_radio {
    display: none;
}

.registration_option .registration_checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.registration_option.selected .registration_checkmark {
    background: #FF6600;
    border-color: #FF6600;
}

.registration_option.selected .registration_checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 10px;
    color: #fff;
}

.registration_text {
    font-size: 14px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.registration_text .minutes_input {
    width: 60px;
    padding: 6px 10px;
    text-align: center;
}

/* Date Range */
.registration_date_range {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-left: 32px;
    flex-wrap: wrap;
}

.date_range_row {
    display: flex;
    gap: 10px;
}

.date_range_row .date_input {
    width: 150px;
}

.date_range_row .time_input {
    width: 100px;
}

.date_range_separator {
    font-size: 14px;
    color: #64748b;
}

/* Ticket Form Actions */
.ticket_form_actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.ticket_actions_left {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

/* Base Button Styles */
.el_btn_base {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.el_btn_base svg {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.el_btn_base:hover svg {
    transform: scale(1.1);
}

.el_btn_base:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.25);
}

.el_btn_base:active {
    transform: scale(0.98);
}

/* Save Button - Primary filled */
.el_btn_save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #FF6600 0%, #e55c00 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.25);
}

.el_btn_save svg {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.el_btn_save:hover {
    background: linear-gradient(135deg, #e55c00 0%, #cc5200 100%);
    box-shadow: 0 6px 16px rgba(255, 102, 0, 0.35);
    transform: translateY(-1px);
}

.el_btn_save:hover svg {
    transform: scale(1.1);
}

.el_btn_save:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.25), 0 4px 12px rgba(255, 102, 0, 0.25);
}

.el_btn_save:active {
    transform: translateY(0) scale(0.98);
}

.el_btn_save.saved {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}

/* Danger Button - Stop reservation */
.el_btn_danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 24px;
    background: transparent;
    color: #dc2626;
    border: 2px solid #fecaca;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.el_btn_danger svg {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.el_btn_danger:hover {
    background: #fef2f2;
    border-color: #f87171;
    color: #b91c1c;
}

.el_btn_danger:hover svg {
    transform: scale(1.1);
}

.el_btn_danger:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}

.el_btn_danger:active {
    transform: scale(0.98);
}

/* Warning Button - Pause/Stop */
.el_btn_warning {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 24px;
    background: transparent;
    color: #d97706;
    border: 2px solid #fde68a;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.el_btn_warning svg {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.el_btn_warning:hover {
    background: #fffbeb;
    border-color: #fbbf24;
    color: #b45309;
}

.el_btn_warning:hover svg {
    transform: scale(1.1);
}

.el_btn_warning:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
}

.el_btn_warning:active {
    transform: scale(0.98);
}

.el_btn_warning.btn_disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Add Button - Outlined */
.el_btn_add {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    background: #fff;
    color: #FF6600;
    border: 2px solid #FF6600;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    margin-top: 20px;
}

.el_btn_add svg {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.el_btn_add:hover {
    background: #FF6600;
    color: #fff;
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.25);
    transform: translateY(-1px);
}

.el_btn_add:hover svg {
    transform: rotate(90deg);
}

.el_btn_add:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.25);
}

.el_btn_add:active {
    transform: translateY(0) scale(0.98);
}

/* Legacy button classes for compatibility */
.el_button_primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    background: #FF6600;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.el_button_primary:hover {
    background: #e55c00;
}

/* External Section */
.billetterie_external_section {
    padding-top: 10px;
}

.external_tarifs_wrapper {
    margin-top: 30px;
    padding: 24px;
    background: #f8fafc;
    border-radius: 12px;
}

.external_tarifs_list {
    margin-bottom: 20px;
}

.external_tarif_item {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 16px;
}

.tarif_row {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    margin-bottom: 16px;
}

.tarif_field {
    flex: 1;
}

.tarif_field label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 6px;
}

.tarif_name_field {
    flex: 2;
}

.tarif_price_field {
    flex: 1;
}

.tarif_price_field .price_input_wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.tarif_price_field .tarif_price_input {
    width: 100px;
}

.tarif_price_field .currency_symbol {
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
}

.tarif_info_field {
    flex: none;
    width: 100%;
}

.btn_remove_tarif {
    width: 36px;
    height: 36px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn_remove_tarif:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #ef4444;
}

/* Input error animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.input_error {
    animation: shake 0.5s ease-in-out;
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
}

/* Button disabled state */
.btn_disabled,
.el_btn_danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
}

.btn_disabled:hover,
.el_btn_danger:disabled:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Ticket inactive state */
.ticket_inactive {
    opacity: 0.7;
    position: relative;
}

.ticket_inactive::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 250, 252, 0.5);
    border-radius: 12px;
    pointer-events: none;
    z-index: 1;
}

.ticket_inactive .ticket_form_actions {
    position: relative;
    z-index: 2;
}

/* ==========================================================================
   Wizard Modal Styles
   ========================================================================== */

.ticket_wizard_overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.ticket_wizard_modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.wizard_close_btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 36px;
    height: 36px;
    border: none;
    background: #f1f5f9;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s;
    z-index: 10;
}

.wizard_close_btn:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.wizard_header {
    padding: 28px 28px 24px;
    border-bottom: 1px solid #e2e8f0;
    position: relative;
}

.wizard_title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 24px 0;
}

/* Progress Bar */
.wizard_progress {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.progress_step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}

.step_number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.progress_step.active .step_number,
.progress_step.completed .step_number {
    background: #FF6600;
    color: #fff;
}

.progress_step.completed .step_number {
    background: #22c55e;
}

.step_label {
    font-size: 12px;
    font-weight: 500;
    color: #94a3b8;
    transition: color 0.3s;
}

.progress_step.active .step_label {
    color: #FF6600;
    font-weight: 600;
}

.progress_step.completed .step_label {
    color: #22c55e;
}

.progress_line {
    flex: 1;
    height: 2px;
    background: #e2e8f0;
    margin: 0 8px 24px;
    position: relative;
    overflow: hidden;
}

.progress_line::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: #FF6600;
    transition: width 0.3s;
}

.progress_line.filled::after {
    width: 100%;
}

/* Wizard Body */
.wizard_body {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

.wizard_step {
    display: none;
    animation: stepFadeIn 0.3s ease;
}

.wizard_step.active {
    display: block;
}

@keyframes stepFadeIn {
    from { opacity: 0; transform: translateX(10px); }
    to { opacity: 1; transform: translateX(0); }
}

.step_header {
    margin-bottom: 24px;
}

.step_indicator {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    color: #FF6600;
    background: #fff8f5;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 8px;
}

.step_title {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

/* Wizard Fields */
.wizard_field {
    margin-bottom: 20px;
}

.wizard_field:last-child {
    margin-bottom: 0;
}

.wizard_label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

.wizard_label .required {
    color: #ef4444;
}

.label_hint {
    font-weight: 400;
    color: #94a3b8;
    font-size: 13px;
}

.wizard_input,
.wizard_textarea {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    color: #1e293b;
    background: #fff;
    transition: all 0.2s;
}

.wizard_input:focus,
.wizard_textarea:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.wizard_input::placeholder,
.wizard_textarea::placeholder {
    color: #94a3b8;
}

.wizard_input_centered {
    text-align: center;
}

.wizard_textarea {
    resize: vertical;
    min-height: 80px;
}

/* Validation Errors */
.wizard_input_error {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
    animation: wizardShake 0.4s ease;
}

@keyframes wizardShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}

.wizard_step_error {
    background: #fef2f2;
    color: #dc2626;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #fecaca;
}

.wizard_step_error::before {
    content: '⚠';
    font-size: 16px;
}

.field_help {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 6px;
    font-style: italic;
}

.wizard_fields_row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.wizard_info_box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f0f9ff;
    border-radius: 10px;
    margin-top: 20px;
}

.wizard_info_box svg {
    color: #0284c7;
    flex-shrink: 0;
}

.wizard_info_box span {
    font-size: 13px;
    color: #0369a1;
}

/* Radio Options */
.wizard_period_options,
.wizard_slots_mode {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.wizard_radio_option {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.wizard_radio_option:hover {
    border-color: #FF6600;
    background: #fffbf8;
}

.wizard_radio_option.selected {
    border-color: #FF6600;
    background: #fff8f5;
}

.wizard_radio_option input[type="radio"] {
    display: none;
}

.radio_mark {
    width: 22px;
    height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
    margin-top: 2px;
}

.wizard_radio_option.selected .radio_mark {
    border-color: #FF6600;
}

.wizard_radio_option.selected .radio_mark::after {
    content: '';
    width: 12px;
    height: 12px;
    background: #FF6600;
    border-radius: 50%;
}

.radio_content {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.radio_title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.radio_inline {
    font-size: 14px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.wizard_inline_input {
    width: 70px;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    text-align: center;
}

.wizard_inline_input:focus {
    outline: none;
    border-color: #FF6600;
}

/* Date Range */
.wizard_date_range {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 16px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
}

.date_range_group {
    flex: 1;
}

.date_range_group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.date_time_row {
    display: flex;
    gap: 8px;
}

.date_time_row .wizard_input {
    padding: 10px 12px;
}

.date_range_arrow {
    color: #94a3b8;
    flex-shrink: 0;
}

/* Slots Picker */
.wizard_slots_picker {
    margin-top: 20px;
    animation: fadeIn 0.3s ease;
}

.slots_search_bar {
    position: relative;
    margin-bottom: 12px;
}

.slots_search_bar svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.slots_search_input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    background: #fff;
}

.slots_search_input:focus {
    outline: none;
    border-color: #FF6600;
}

/* Date Filters */
.slots_date_filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.slots_filter_btn {
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.slots_filter_btn:hover {
    border-color: #FF6600;
    color: #FF6600;
    background: #fff8f5;
}

.slots_filter_btn.active {
    background: linear-gradient(135deg, #FF6600 0%, #e55c00 100%);
    border-color: #FF6600;
    color: #fff;
    box-shadow: 0 2px 8px rgba(255, 102, 0, 0.25);
}

.slots_checklist {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
}

.slots_checklist::-webkit-scrollbar {
    width: 6px;
}

.slots_checklist::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.slots_no_results {
    padding: 24px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
    font-style: italic;
}

.slot_check_item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.15s ease;
    cursor: pointer;
    position: relative;
}

.slot_check_item:last-child {
    border-bottom: none;
}

.slot_check_item:hover {
    background: #f8fafc;
}

.slot_check_item.is_checked {
    background: linear-gradient(135deg, #fff8f5 0%, #fff5f0 100%);
    border-left: 3px solid #FF6600;
    padding-left: 13px;
}

.slot_check_label {
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    flex: 1;
}

.slot_checkbox_input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.slot_checkbox_custom {
    width: 22px;
    height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.slot_check_item.is_checked .slot_checkbox_custom {
    background: #FF6600;
    border-color: #FF6600;
    transform: scale(1.05);
}

.slot_check_item.is_checked .slot_checkbox_custom::after {
    content: '✓';
    color: #fff;
    font-size: 14px;
    font-weight: bold;
}

.slot_check_item.is_checked .slot_date {
    color: #FF6600;
}

.slot_date {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    transition: color 0.15s;
}

.slot_time {
    font-size: 13px;
    color: #64748b;
    margin-left: auto;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 6px;
}

.slot_check_item.is_checked .slot_time {
    background: #FF6600;
    color: #fff;
}

.slots_quick_actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.slots_action_btn {
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.slots_action_btn:hover {
    border-color: #FF6600;
    color: #FF6600;
    background: #fff8f5;
}

.slots_action_btn.slots_select_all:hover {
    border-color: #22c55e;
    color: #22c55e;
    background: #f0fdf4;
}

.slots_action_btn.slots_deselect_all:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: #fef2f2;
}

.slots_chips_container {
    margin-top: 16px;
    padding: 16px;
    border-radius: 10px;
    background: #f8fafc;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    min-height: 52px;
}

.slots_chips_count {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-right: 8px;
}

.slots_chips_empty {
    font-size: 13px;
    color: #94a3b8;
    font-style: italic;
    display: flex;
    align-items: center;
    gap: 8px;
}

.slots_chips_empty::before {
    content: '○';
    font-size: 10px;
}

.chips_label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 10px;
    display: block;
}

.chips_label .chips_count {
    color: #FF6600;
}

.slots_chips_list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.slot_chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #FF6600 0%, #e55c00 100%);
    color: #fff;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    animation: chipIn 0.2s ease;
}

@keyframes chipIn {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.slot_chip_remove {
    width: 18px;
    height: 18px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: background 0.2s;
    color: #fff;
    line-height: 1;
}

.slot_chip_remove:hover {
    background: rgba(255, 255, 255, 0.4);
}

.slots_empty_message {
    text-align: center;
    padding: 24px;
    color: #64748b;
}

.slots_empty_message .hint {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 8px;
}

/* Wizard Footer */
.wizard_footer {
    padding: 20px 28px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.wizard_nav_buttons {
    display: flex;
    gap: 12px;
}

.wizard_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.wizard_btn_cancel {
    background: transparent;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.wizard_btn_cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.wizard_btn_prev {
    background: #fff;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.wizard_btn_prev:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.wizard_btn_primary {
    background: linear-gradient(135deg, #FF6600 0%, #e55c00 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.25);
}

.wizard_btn_primary:hover {
    background: linear-gradient(135deg, #e55c00 0%, #cc5200 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(255, 102, 0, 0.35);
}

.wizard_btn_primary:active {
    transform: translateY(0);
}

/* Responsive Wizard */
@media (max-width: 600px) {
    .ticket_wizard_modal {
        max-height: 100vh;
        border-radius: 0;
    }

    .wizard_header {
        padding: 20px;
    }

    .wizard_body {
        padding: 20px;
    }

    .wizard_fields_row {
        grid-template-columns: 1fr;
    }

    .wizard_date_range {
        flex-direction: column;
        gap: 12px;
    }

    .date_range_arrow {
        transform: rotate(90deg);
    }

    .wizard_progress {
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }

    .progress_line {
        display: none;
    }

    .step_label {
        display: none;
    }

    .wizard_footer {
        flex-direction: column;
        gap: 12px;
    }

    .wizard_btn_cancel {
        order: 1;
        width: 100%;
    }

    .wizard_nav_buttons {
        width: 100%;
        flex-direction: column;
    }

    .wizard_btn {
        width: 100%;
        justify-content: center;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .billetterie_row_2cols {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .billetterie_options {
        flex-direction: column;
    }

    .ticket_form_row_3cols {
        grid-template-columns: 1fr;
    }

    .slots_selection {
        flex-direction: column;
    }

    .slots_picker_row {
        flex-direction: column;
    }

    .registration_date_range {
        flex-direction: column;
        align-items: flex-start;
        padding-left: 32px;
    }

    .ticket_form_actions {
        flex-direction: column;
    }

    .el_btn_save,
    .el_btn_danger,
    .el_btn_add {
        width: 100%;
    }

    .tarif_row {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // V1 Le Hiboo - Slots disponibles pour le formulaire
    var availableSlots = <?php echo json_encode( $event_slots ); ?>;

    var BilletterieManager = {
        prefix: '<?php echo esc_js( $_prefix ); ?>',
        ticketIndex: <?php echo count( $tickets ); ?>,
        tarifIndex: <?php echo count( $external_prices ); ?>,
        availableSlots: availableSlots,

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Choix du mode de billetterie
            $(document).on('change', '.option_radio', function() {
                self.handleModeChoice();
            });

            // Choix des créneaux
            $(document).on('change', '.slots_radio', function() {
                self.handleSlotsChoice();
            });

            // Ajout d'un créneau
            $(document).on('click', '.btn_add_slot', function() {
                self.addSlot();
            });

            // Suppression d'un créneau
            $(document).on('click', '.btn_remove_slot', function() {
                $(this).closest('.selected_slot_item').remove();
            });

            // Choix période d'inscription
            $(document).on('change', '.registration_radio', function() {
                self.handleRegistrationChoice($(this));
            });

            // Ajout d'un billet
            $(document).on('click', '.btn_add_ticket', function() {
                self.addTicket();
            });

            // Sauvegarder un billet
            $(document).on('click', '.btn_save_ticket', function() {
                self.saveTicket($(this));
            });

            // Stopper la réservation
            $(document).on('click', '.btn_stop_reservation', function() {
                self.stopReservation($(this));
            });

            // Supprimer un billet
            $(document).on('click', '.btn_delete_ticket', function() {
                self.deleteTicket($(this));
            });

            // Ajout d'un tarif
            $(document).on('click', '.btn_add_tarif', function() {
                self.addTarif();
            });

            // Suppression d'un tarif
            $(document).on('click', '.btn_remove_tarif', function() {
                $(this).closest('.external_tarif_item').remove();
                self.reindexTarifs();
            });

            // Update UI states
            $(document).on('click', '.billetterie_option_card', function() {
                self.updateOptionCardUI($(this));
            });

            $(document).on('click', '.slots_option', function() {
                self.updateSlotsOptionUI($(this));
            });

            $(document).on('click', '.registration_option', function() {
                self.updateRegistrationOptionUI($(this));
            });

            // V1 Le Hiboo - Accordéon pour les billets
            $(document).on('click', '.ticket_accordion_header', function(e) {
                // Éviter de déclencher si on clique sur un élément interactif
                if ($(e.target).closest('input, button, select, textarea, a').length === 0) {
                    self.toggleTicketAccordion($(this));
                }
            });

            // Accessibilité clavier pour l'accordéon
            $(document).on('keydown', '.ticket_accordion_header', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    self.toggleTicketAccordion($(this));
                }
            });

            // V1 Le Hiboo - Boutons Tout déplier / Tout replier
            $(document).on('click', '.btn_expand_all', function(e) {
                e.preventDefault();
                self.expandAllTickets();
            });

            $(document).on('click', '.btn_collapse_all', function(e) {
                e.preventDefault();
                self.collapseAllTickets();
            });

            // V1 Le Hiboo - Toggle slots_mode pour chaque billet
            $(document).on('change', '.slots_mode_radio', function() {
                self.handleTicketSlotsMode($(this));
            });

            $(document).on('click', '.slots_mode_option', function() {
                var $radio = $(this).find('.slots_mode_radio');
                $radio.prop('checked', true).trigger('change');
            });

            // V1 Le Hiboo - Recherche dans les créneaux des billets existants
            $(document).on('input', '.ticket_slots_search_input', function() {
                self.filterTicketSlots($(this));
            });

            // V1 Le Hiboo - Filtres de date pour les créneaux
            $(document).on('click', '.ticket_slots_filter_btn', function(e) {
                e.preventDefault();
                self.applyTicketSlotsDateFilter($(this));
            });

            // V1 Le Hiboo - Clic sur un créneau checkbox
            $(document).on('click', '.slot_checkbox_item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggleSlotCheckbox($(this));
            });
        },

        // V1 Le Hiboo - Filtrer les créneaux par recherche textuelle
        filterTicketSlots: function($input) {
            var query = $input.val().toLowerCase().trim();
            var $wrapper = $input.closest('.slots_checkboxes_wrapper');
            var $grid = $wrapper.find('.slots_checkboxes_grid');
            var visibleCount = 0;

            $grid.find('.slot_checkbox_item').each(function() {
                var label = $(this).find('.slot_checkbox_label').text().toLowerCase();
                if (query === '' || label.indexOf(query) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Afficher/masquer le message "aucun résultat"
            if (visibleCount === 0) {
                $wrapper.find('.slots_no_results').show();
            } else {
                $wrapper.find('.slots_no_results').hide();
            }
        },

        // V1 Le Hiboo - Appliquer un filtre de date
        applyTicketSlotsDateFilter: function($btn) {
            var filter = $btn.data('filter');
            var $wrapper = $btn.closest('.slots_checkboxes_wrapper');
            var $grid = $wrapper.find('.slots_checkboxes_grid');

            // Mettre à jour l'état actif des boutons
            $wrapper.find('.ticket_slots_filter_btn').removeClass('active');
            $btn.addClass('active');

            // Calculer les dates de référence
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            var startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Lundi

            var endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6); // Dimanche

            var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            var startOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            var endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

            var visibleCount = 0;

            $grid.find('.slot_checkbox_item').each(function() {
                var slotDateStr = $(this).data('slot-date');
                if (!slotDateStr) {
                    $(this).show();
                    visibleCount++;
                    return;
                }

                var slotDate = new Date(slotDateStr);
                slotDate.setHours(0, 0, 0, 0);

                var show = true;

                switch (filter) {
                    case 'this_week':
                        show = slotDate >= startOfWeek && slotDate <= endOfWeek;
                        break;
                    case 'this_month':
                        show = slotDate >= startOfMonth && slotDate <= endOfMonth;
                        break;
                    case 'next_month':
                        show = slotDate >= startOfNextMonth && slotDate <= endOfNextMonth;
                        break;
                    case 'all':
                    default:
                        show = true;
                        break;
                }

                if (show) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Afficher/masquer le message "aucun résultat"
            if (visibleCount === 0) {
                $wrapper.find('.slots_no_results').show();
            } else {
                $wrapper.find('.slots_no_results').hide();
            }
        },

        // V1 Le Hiboo - Toggle checkbox de créneau
        toggleSlotCheckbox: function($item) {
            var $checkbox = $item.find('.slot_checkbox');
            var isChecked = $checkbox.prop('checked');

            // Toggle l'état
            $checkbox.prop('checked', !isChecked);
            $item.toggleClass('is_checked', !isChecked);

            // Mettre à jour le badge du ticket
            this.updateTicketSlotsBadge($item);
        },

        // V1 Le Hiboo - Mettre à jour le badge du billet
        updateTicketSlotsBadge: function($item) {
            var $ticketItem = $item.closest('.ticket_form_item');
            var $badge = $ticketItem.find('.ticket_slots_badge');
            var slotsMode = $ticketItem.find('.slots_mode_radio:checked').val();

            if (slotsMode === 'all') {
                $badge.text('<?php echo esc_js( __( 'Tous les créneaux', 'eventlist' ) ); ?>');
            } else {
                var selectedCount = $ticketItem.find('.slot_checkbox:checked').length;
                if (selectedCount === 0) {
                    $badge.text('<?php echo esc_js( __( 'Aucun créneau', 'eventlist' ) ); ?>');
                } else if (selectedCount === 1) {
                    $badge.text('<?php echo esc_js( __( '1 créneau sélectionné', 'eventlist' ) ); ?>');
                } else {
                    $badge.text(selectedCount + ' <?php echo esc_js( __( 'créneaux sélectionnés', 'eventlist' ) ); ?>');
                }
            }
        },

        // V1 Le Hiboo - Toggle accordéon billet
        toggleTicketAccordion: function($header) {
            var $ticketItem = $header.closest('.ticket_form_item');
            var $body = $ticketItem.find('.ticket_accordion_body');
            var isExpanded = $ticketItem.hasClass('is_expanded');

            if (isExpanded) {
                // Fermer
                $body.slideUp(250, function() {
                    $ticketItem.removeClass('is_expanded');
                    $header.attr('aria-expanded', 'false');
                });
            } else {
                // Ouvrir
                $ticketItem.addClass('is_expanded');
                $header.attr('aria-expanded', 'true');
                $body.slideDown(250);
            }
        },

        // V1 Le Hiboo - Déplier tous les billets
        expandAllTickets: function() {
            var self = this;
            $('.ticket_form_item').each(function() {
                var $ticketItem = $(this);
                if (!$ticketItem.hasClass('is_expanded')) {
                    var $header = $ticketItem.find('.ticket_accordion_header');
                    self.toggleTicketAccordion($header);
                }
            });
        },

        // V1 Le Hiboo - Replier tous les billets
        collapseAllTickets: function() {
            var self = this;
            $('.ticket_form_item').each(function() {
                var $ticketItem = $(this);
                if ($ticketItem.hasClass('is_expanded')) {
                    var $header = $ticketItem.find('.ticket_accordion_header');
                    self.toggleTicketAccordion($header);
                }
            });
        },

        handleModeChoice: function() {
            var mode = $('input[name="' + this.prefix + 'ticket_link"]:checked').val();

            $('.billetterie_option_card').removeClass('selected');
            $('input[name="' + this.prefix + 'ticket_link"]:checked').closest('.billetterie_option_card').addClass('selected');

            if (mode === 'ticket_internal_link') {
                $('.billetterie_internal_section').show();
                $('.billetterie_external_section').hide();
            } else {
                $('.billetterie_internal_section').hide();
                $('.billetterie_external_section').show();
            }
        },

        updateOptionCardUI: function($card) {
            // Handled by handleModeChoice
        },

        handleSlotsChoice: function() {
            var mode = $('input[name="' + this.prefix + 'slots_mode"]:checked').val();

            $('.slots_option').removeClass('selected');
            $('input[name="' + this.prefix + 'slots_mode"]:checked').closest('.slots_option').addClass('selected');

            if (mode === 'selected') {
                $('.slots_picker').show();
            } else {
                $('.slots_picker').hide();
            }
        },

        updateSlotsOptionUI: function($option) {
            // Handled by handleSlotsChoice
        },

        addSlot: function() {
            var $select = $('.slot_select');
            var slotId = $select.val();
            var slotLabel = $select.find('option:selected').text();

            if (!slotId) {
                alert('<?php echo esc_js( __( 'Veuillez sélectionner un créneau', 'eventlist' ) ); ?>');
                return;
            }

            // Vérifier si déjà ajouté
            if ($('.selected_slot_item[data-slot-id="' + slotId + '"]').length > 0) {
                alert('<?php echo esc_js( __( 'Ce créneau est déjà sélectionné', 'eventlist' ) ); ?>');
                return;
            }

            var index = $('.selected_slot_item').length;
            var html = '<div class="selected_slot_item" data-slot-id="' + slotId + '">' +
                '<span class="slot_info">' + slotLabel + '</span>' +
                '<input type="hidden" name="' + this.prefix + 'selected_slots[' + index + '][id]" value="' + slotId + '">' +
                '<input type="hidden" name="' + this.prefix + 'selected_slots[' + index + '][label]" value="' + slotLabel + '">' +
                '<button type="button" class="btn_remove_slot"><i class="fa fa-times"></i></button>' +
                '</div>';

            $('.selected_slots_list').append(html);
            $select.val('');
        },

        handleRegistrationChoice: function($radio) {
            var $item = $radio.closest('.ticket_form_item');
            var mode = $radio.val();

            $item.find('.registration_option').removeClass('selected');
            $radio.closest('.registration_option').addClass('selected');

            if (mode === 'date_range') {
                $item.find('.registration_date_range').show();
            } else {
                $item.find('.registration_date_range').hide();
            }
        },

        updateRegistrationOptionUI: function($option) {
            // Handled by handleRegistrationChoice
        },

        // V1 Le Hiboo - Toggle slots_mode pour chaque billet
        handleTicketSlotsMode: function($radio) {
            var $item = $radio.closest('.ticket_form_item');
            var mode = $radio.val();
            var $badge = $item.find('.ticket_slots_badge');

            // Update UI
            $item.find('.slots_mode_option').removeClass('selected');
            $radio.closest('.slots_mode_option').addClass('selected');

            // Show/hide checkboxes
            if (mode === 'selected') {
                $item.find('.slots_checkboxes_wrapper').slideDown(200);
                // Mettre à jour le badge avec le nombre de créneaux sélectionnés
                var selectedCount = $item.find('.slot_checkbox:checked').length;
                if (selectedCount === 0) {
                    $badge.text('<?php echo esc_js( __( 'Aucun créneau', 'eventlist' ) ); ?>');
                } else if (selectedCount === 1) {
                    $badge.text('<?php echo esc_js( __( '1 créneau sélectionné', 'eventlist' ) ); ?>');
                } else {
                    $badge.text(selectedCount + ' <?php echo esc_js( __( 'créneaux sélectionnés', 'eventlist' ) ); ?>');
                }
            } else {
                $item.find('.slots_checkboxes_wrapper').slideUp(200);
                $badge.text('<?php echo esc_js( __( 'Tous les créneaux', 'eventlist' ) ); ?>');
            }
        },

        addTicket: function() {
            // Ouvrir le wizard au lieu d'ajouter directement le HTML
            TicketWizard.open();
        },

        saveTicket: function($btn) {
            var $item = $btn.closest('.ticket_form_item');
            var $nameInput = $item.find('.ticket_name_input');
            var $btnText = $btn.find('.btn_text');

            if (!$nameInput.val().trim()) {
                // Shake animation for validation
                $nameInput.addClass('input_error');
                setTimeout(function() {
                    $nameInput.removeClass('input_error');
                }, 500);
                $nameInput.focus();
                return;
            }

            // V1 Le Hiboo - Déclencher la sauvegarde complète via le bouton principal
            var $mainSaveBtn = $('#el-btn-save');
            if ($mainSaveBtn.length) {
                // Animation de feedback sur le bouton du billet
                var originalText = $btnText.text();
                $btn.addClass('saved');
                $btnText.text('<?php echo esc_js( __( 'Sauvegarde...', 'eventlist' ) ); ?>');

                // Changer l'icône en loader temporairement
                var $svg = $btn.find('svg');
                var originalSvg = $svg.html();
                $svg.html('<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="60" stroke-dashoffset="15"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle>');

                // Déclencher le clic sur le bouton principal
                $mainSaveBtn.trigger('click');

                // Rétablir le bouton après un délai
                setTimeout(function() {
                    $btn.removeClass('saved');
                    $btnText.text(originalText);
                    $svg.html(originalSvg);
                }, 3000);
            } else {
                // Fallback si le bouton principal n'existe pas
                if (window.ToastNotification) {
                    window.ToastNotification.warning('<?php echo esc_js( __( 'Utilisez le bouton Enregistrer en haut de la page', 'eventlist' ) ); ?>');
                }
            }
        },

        stopReservation: function($btn) {
            var $item = $btn.closest('.ticket_form_item');
            var $btnText = $btn.find('.btn_text');

            if (confirm('<?php echo esc_js( __( 'Êtes-vous sûr de vouloir stopper la réservation pour ce billet ?', 'eventlist' ) ); ?>')) {
                $item.find('.ticket_is_active').val('no');
                $item.addClass('ticket_inactive');

                // Désactiver le bouton après l'action
                $btn.prop('disabled', true).addClass('btn_disabled');
                $btnText.text('<?php echo esc_js( __( 'Réservation stoppée', 'eventlist' ) ); ?>');

                if (window.ToastNotification) {
                    window.ToastNotification.info('<?php echo esc_js( __( 'Réservation stoppée', 'eventlist' ) ); ?>');
                }
            }
        },

        deleteTicket: function($btn) {
            var self = this;
            var $item = $btn.closest('.ticket_form_item');
            var ticketName = $btn.data('ticket-name') || '<?php echo esc_js( __( 'ce billet', 'eventlist' ) ); ?>';

            var confirmMessage = '<?php echo esc_js( __( 'Êtes-vous sûr de vouloir supprimer le billet "%s" ?', 'eventlist' ) ); ?>';
            confirmMessage = confirmMessage.replace('%s', ticketName);

            var warningMessage = '<?php echo esc_js( __( 'Attention : Cette action est irréversible.', 'eventlist' ) ); ?>';

            if (confirm(confirmMessage + '\n\n' + warningMessage)) {
                // Animation de suppression
                $item.css({
                    'transition': 'all 0.3s ease',
                    'opacity': '0',
                    'transform': 'translateX(-20px)'
                });

                setTimeout(function() {
                    $item.slideUp(200, function() {
                        $item.remove();
                        self.reindexTickets();

                        if (window.ToastNotification) {
                            window.ToastNotification.success('<?php echo esc_js( __( 'Billet supprimé', 'eventlist' ) ); ?>');
                        }
                    });
                }, 200);
            }
        },

        reindexTickets: function() {
            var self = this;
            var newIndex = 0;
            $('.ticket_form_item').each(function() {
                var $item = $(this);
                $item.attr('data-index', newIndex);

                // Update all input names with new index
                $item.find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/ticket\[\d+\]/, 'ticket[' + newIndex + ']');
                        $(this).attr('name', name);
                    }
                });

                // Update button data-index
                $item.find('[data-index]').attr('data-index', newIndex);

                newIndex++;
            });
            self.ticketIndex = newIndex;
        },

        addTarif: function() {
            var index = this.tarifIndex;

            var html = '<div class="external_tarif_item" data-index="' + index + '">' +
                '<div class="tarif_row">' +
                    '<div class="tarif_field tarif_name_field">' +
                        '<label><?php echo esc_js( __( 'Nom du tarif', 'eventlist' ) ); ?></label>' +
                        '<input type="text" name="' + this.prefix + 'ticket_external_prices[' + index + '][name]" class="billetterie_input" placeholder="<?php echo esc_js( __( 'Tarif Normal', 'eventlist' ) ); ?>">' +
                    '</div>' +
                    '<div class="tarif_field tarif_price_field">' +
                        '<label><?php echo esc_js( __( 'Prix', 'eventlist' ) ); ?></label>' +
                        '<div class="price_input_wrapper">' +
                            '<input type="number" name="' + this.prefix + 'ticket_external_prices[' + index + '][price]" class="billetterie_input tarif_price_input" min="0" step="0.01" placeholder="5">' +
                            '<span class="currency_symbol">€</span>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn_remove_tarif"><i class="fa fa-times"></i></button>' +
                '</div>' +
                '<div class="tarif_field tarif_info_field">' +
                    '<label><?php echo esc_js( __( 'Informations', 'eventlist' ) ); ?></label>' +
                    '<textarea name="' + this.prefix + 'ticket_external_prices[' + index + '][info]" class="billetterie_textarea" rows="2" placeholder="<?php echo esc_js( __( 'Type de public pour ce tarif', 'eventlist' ) ); ?>"></textarea>' +
                '</div>' +
            '</div>';

            $('.external_tarifs_list').append(html);
            this.tarifIndex++;
        },

        reindexTarifs: function() {
            var self = this;
            $('.external_tarif_item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('input, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', name);
                    }
                });
            });
            self.tarifIndex = $('.external_tarif_item').length;
        }
    };

    BilletterieManager.init();

    // ═══════════════════════════════════════════════════════════════
    // TICKET WIZARD - Gestion du wizard de création de billet
    // ═══════════════════════════════════════════════════════════════
    var TicketWizard = {
        currentStep: 1,
        totalSteps: 4,
        prefix: '<?php echo esc_js( $_prefix ); ?>',

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Fermer le wizard
            $(document).on('click', '.wizard_close_btn, .wizard_btn_cancel', function() {
                self.close();
            });

            // Clic sur l'overlay (en dehors du modal)
            $(document).on('click', '.ticket_wizard_overlay', function(e) {
                if ($(e.target).hasClass('ticket_wizard_overlay')) {
                    self.close();
                }
            });

            // Navigation
            $(document).on('click', '.wizard_btn_next', function() {
                self.nextStep();
            });

            $(document).on('click', '.wizard_btn_prev', function() {
                self.prevStep();
            });

            // Clic sur les étapes de la barre de progression
            $(document).on('click', '.progress_step', function() {
                var step = parseInt($(this).data('step'));
                if (step < self.currentStep) {
                    self.goToStep(step);
                }
            });

            // Soumission du formulaire
            $(document).on('click', '.wizard_btn_submit', function() {
                self.submitTicket();
            });

            // Mode période d'inscription
            $(document).on('change', 'input[name="wizard_registration_mode"]', function() {
                self.handleRegistrationMode($(this));
            });

            // Mode créneaux (tous / spécifiques)
            $(document).on('change', 'input[name="wizard_slots_mode"]', function() {
                self.handleSlotsMode($(this));
            });

            // Touche Escape pour fermer
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.ticket_wizard_overlay').is(':visible')) {
                    self.close();
                }
            });
        },

        open: function() {
            this.reset();
            $('.ticket_wizard_overlay').fadeIn(200);
            $('body').addClass('wizard_open');
            // Focus sur le premier champ
            setTimeout(function() {
                $('.wizard_step[data-step="1"]').find('input:first').focus();
            }, 300);
        },

        close: function() {
            $('.ticket_wizard_overlay').fadeOut(200);
            $('body').removeClass('wizard_open');
            this.reset();
        },

        reset: function() {
            this.currentStep = 1;
            // Reset tous les champs
            $('.ticket_wizard_modal').find('input[type="text"], input[type="number"], textarea').val('');
            $('.ticket_wizard_modal').find('.wizard_min_places').val('1');
            $('.ticket_wizard_modal').find('.wizard_minutes_before').val('0');
            // Reset les radios
            $('input[name="wizard_registration_mode"][value="before_start"]').prop('checked', true).trigger('change');
            $('input[name="wizard_slots_mode"][value="all"]').prop('checked', true).trigger('change');
            // Reset l'UI
            this.updateUI();
            SlotMultiSelect.reset();
        },

        goToStep: function(step) {
            if (step < 1 || step > this.totalSteps) return;

            this.currentStep = step;
            this.updateUI();
        },

        nextStep: function() {
            if (!this.validateStep(this.currentStep)) {
                return;
            }

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateUI();
            }
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateUI();
            }
        },

        validateStep: function(step) {
            var $step = $('.wizard_step[data-step="' + step + '"]');
            var isValid = true;

            // Reset les erreurs
            $step.find('.wizard_input_error').removeClass('wizard_input_error');

            switch (step) {
                case 1:
                    // Le nom est requis
                    var $nameInput = $step.find('.wizard_ticket_name');
                    if (!$nameInput.val().trim()) {
                        $nameInput.addClass('wizard_input_error');
                        $nameInput.focus();
                        this.showStepError('<?php echo esc_js( __( 'Le nom du billet est requis', 'eventlist' ) ); ?>');
                        isValid = false;
                    }
                    break;

                case 2:
                    // Vérifier min <= max si max est défini
                    var $minInput = $step.find('.wizard_min_places');
                    var $maxInput = $step.find('.wizard_max_places');
                    var min = parseInt($minInput.val()) || 1;
                    var max = parseInt($maxInput.val()) || 0;

                    if (max > 0 && min > max) {
                        $minInput.addClass('wizard_input_error');
                        $maxInput.addClass('wizard_input_error');
                        this.showStepError('<?php echo esc_js( __( 'Le minimum ne peut pas être supérieur au maximum', 'eventlist' ) ); ?>');
                        isValid = false;
                    }
                    break;

                case 3:
                    // Si mode date_range, vérifier les dates
                    var registrationMode = $step.find('input[name="wizard_registration_mode"]:checked').val();
                    if (registrationMode === 'date_range') {
                        var $startDate = $step.find('.wizard_start_date');
                        if (!$startDate.val()) {
                            $startDate.addClass('wizard_input_error');
                            this.showStepError('<?php echo esc_js( __( 'La date de début est requise', 'eventlist' ) ); ?>');
                            isValid = false;
                        }
                    }
                    break;

                case 4:
                    // Si mode selected, au moins un créneau doit être coché
                    var slotsMode = $step.find('input[name="wizard_slots_mode"]:checked').val();
                    if (slotsMode === 'selected') {
                        var checkedSlots = SlotMultiSelect.getSelectedSlots();
                        if (checkedSlots.length === 0) {
                            this.showStepError('<?php echo esc_js( __( 'Veuillez sélectionner au moins un créneau', 'eventlist' ) ); ?>');
                            isValid = false;
                        }
                    }
                    break;
            }

            return isValid;
        },

        showStepError: function(message) {
            var $errorContainer = $('.wizard_step_error');
            if ($errorContainer.length === 0) {
                $errorContainer = $('<div class="wizard_step_error"></div>');
                $('.wizard_step.active .wizard_step_content').prepend($errorContainer);
            }
            $errorContainer.text(message).fadeIn(200);
            setTimeout(function() {
                $errorContainer.fadeOut(200);
            }, 3000);
        },

        updateUI: function() {
            var self = this;

            // Mettre à jour les étapes
            $('.wizard_step').removeClass('active');
            $('.wizard_step[data-step="' + this.currentStep + '"]').addClass('active');

            // Mettre à jour la barre de progression
            $('.progress_step').each(function() {
                var step = parseInt($(this).data('step'));
                $(this).removeClass('active completed');
                if (step === self.currentStep) {
                    $(this).addClass('active');
                } else if (step < self.currentStep) {
                    $(this).addClass('completed');
                }
            });

            // Mettre à jour les lignes de progression
            $('.progress_line').each(function(index) {
                // La ligne est "filled" si l'étape suivante est déjà passée
                if (index < self.currentStep - 1) {
                    $(this).addClass('filled');
                } else {
                    $(this).removeClass('filled');
                }
            });

            // Afficher/masquer les boutons de navigation
            if (this.currentStep === 1) {
                $('.wizard_btn_prev').hide();
            } else {
                $('.wizard_btn_prev').show();
            }

            if (this.currentStep === this.totalSteps) {
                $('.wizard_btn_next').hide();
                $('.wizard_btn_submit').show();
            } else {
                $('.wizard_btn_next').show();
                $('.wizard_btn_submit').hide();
            }
        },

        handleRegistrationMode: function($radio) {
            var mode = $radio.val();
            var $container = $radio.closest('.wizard_step');

            $container.find('.wizard_period_options .wizard_radio_option').removeClass('selected');
            $radio.closest('.wizard_radio_option').addClass('selected');

            if (mode === 'date_range') {
                $container.find('.wizard_date_range').slideDown(200);
            } else {
                $container.find('.wizard_date_range').slideUp(200);
            }
        },

        handleSlotsMode: function($radio) {
            var mode = $radio.val();
            var $container = $radio.closest('.wizard_step');

            $container.find('.wizard_slots_mode .wizard_radio_option').removeClass('selected');
            $radio.closest('.wizard_radio_option').addClass('selected');

            if (mode === 'selected') {
                $container.find('.wizard_slots_picker').slideDown(200);
            } else {
                $container.find('.wizard_slots_picker').slideUp(200);
            }
        },

        submitTicket: function() {
            // Valider la dernière étape
            if (!this.validateStep(this.currentStep)) {
                return;
            }

            var self = this;
            var index = BilletterieManager.ticketIndex;
            var $modal = $('.ticket_wizard_modal');

            // Récupérer les valeurs
            var ticketData = {
                name: $modal.find('.wizard_ticket_name').val().trim(),
                description: $modal.find('.wizard_ticket_desc').val().trim(),
                total: $modal.find('.wizard_total_places').val() || '',
                min: $modal.find('.wizard_min_places').val() || '1',
                max: $modal.find('.wizard_max_places').val() || '',
                registrationMode: $modal.find('input[name="wizard_registration_mode"]:checked').val(),
                minutesBefore: $modal.find('.wizard_minutes_before').val() || '0',
                startDate: $modal.find('.wizard_start_date').val() || '',
                startTime: $modal.find('.wizard_start_time').val() || '00:00',
                closeDate: $modal.find('.wizard_end_date').val() || '',
                closeTime: $modal.find('.wizard_end_time').val() || '23:59',
                slotsMode: $modal.find('input[name="wizard_slots_mode"]:checked').val(),
                slots: SlotMultiSelect.getSelectedSlots()
            };

            // Construire le HTML du nouveau billet
            var html = this.buildTicketHTML(index, ticketData);

            // Ajouter le billet à la liste
            $('.tickets_list_wrapper').append(html);
            BilletterieManager.ticketIndex++;

            // Fermer le wizard
            this.close();

            // Scroll vers le nouveau billet
            $('html, body').animate({
                scrollTop: $('.ticket_form_item').last().offset().top - 100
            }, 300);

            // Notification
            if (window.ToastNotification) {
                window.ToastNotification.success('<?php echo esc_js( __( 'Billet créé avec succès', 'eventlist' ) ); ?>');
            }
        },

        buildTicketHTML: function(index, data) {
            var prefix = this.prefix;
            var self = this;

            // Affichage condensé du mode créneaux pour le badge
            var slotsModeLabel = data.slotsMode === 'all'
                ? '<?php echo esc_js( __( 'Tous les créneaux', 'eventlist' ) ); ?>'
                : data.slots.length + ' <?php echo esc_js( __( 'créneau(x) sélectionné(s)', 'eventlist' ) ); ?>';

            // Construire la section des créneaux si des slots sont disponibles
            var slotsSectionHtml = '';
            if (this.availableSlots && this.availableSlots.length > 0) {
                // Générer les checkboxes pour chaque slot
                var slotsCheckboxesHtml = '';
                this.availableSlots.forEach(function(slot) {
                    var isChecked = data.slots.indexOf(slot.id) > -1;
                    var checkedClass = isChecked ? 'is_checked' : '';
                    var checkedAttr = isChecked ? 'checked' : '';
                    slotsCheckboxesHtml += '<label class="slot_checkbox_item ' + checkedClass + '" data-slot-date="' + slot.date + '">' +
                        '<input type="checkbox" name="' + prefix + 'ticket[' + index + '][slots][]" value="' + slot.id + '" class="slot_checkbox" ' + checkedAttr + '>' +
                        '<span class="slot_checkbox_mark"></span>' +
                        '<span class="slot_checkbox_label">' + self.escapeHtml(slot.label) + '</span>' +
                    '</label>';
                });

                slotsSectionHtml = '<div class="ticket_form_field ticket_slots_section">' +
                    '<label class="field_label"><strong><?php echo esc_js( __( 'Créneaux associés', 'eventlist' ) ); ?></strong> :</label>' +
                    '<p class="field_hint"><?php echo esc_js( __( 'Ce billet est disponible pour quels créneaux ?', 'eventlist' ) ); ?></p>' +
                    '<div class="slots_mode_options">' +
                        '<label class="slots_mode_option ' + (data.slotsMode === 'all' ? 'selected' : '') + '">' +
                            '<input type="radio" name="' + prefix + 'ticket[' + index + '][slots_mode]" value="all" class="slots_mode_radio" ' + (data.slotsMode === 'all' ? 'checked' : '') + '>' +
                            '<span class="slots_mode_checkmark"></span>' +
                            '<span class="slots_mode_text"><?php echo esc_js( __( 'Tous les créneaux', 'eventlist' ) ); ?></span>' +
                        '</label>' +
                        '<label class="slots_mode_option ' + (data.slotsMode === 'selected' ? 'selected' : '') + '">' +
                            '<input type="radio" name="' + prefix + 'ticket[' + index + '][slots_mode]" value="selected" class="slots_mode_radio" ' + (data.slotsMode === 'selected' ? 'checked' : '') + '>' +
                            '<span class="slots_mode_checkmark"></span>' +
                            '<span class="slots_mode_text"><?php echo esc_js( __( 'Créneaux spécifiques', 'eventlist' ) ); ?></span>' +
                        '</label>' +
                    '</div>' +
                    '<div class="slots_checkboxes_wrapper" style="' + (data.slotsMode === 'selected' ? '' : 'display: none;') + '">' +
                        '<div class="ticket_slots_search_bar">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<circle cx="11" cy="11" r="8"></circle>' +
                                '<path d="m21 21-4.35-4.35"></path>' +
                            '</svg>' +
                            '<input type="text" class="ticket_slots_search_input" placeholder="<?php echo esc_js( __( 'Rechercher un créneau...', 'eventlist' ) ); ?>" data-ticket-index="' + index + '">' +
                        '</div>' +
                        '<div class="ticket_slots_date_filters">' +
                            '<button type="button" class="ticket_slots_filter_btn active" data-filter="all" data-ticket-index="' + index + '"><?php echo esc_js( __( 'Tout', 'eventlist' ) ); ?></button>' +
                            '<button type="button" class="ticket_slots_filter_btn" data-filter="this_week" data-ticket-index="' + index + '"><?php echo esc_js( __( 'Cette semaine', 'eventlist' ) ); ?></button>' +
                            '<button type="button" class="ticket_slots_filter_btn" data-filter="this_month" data-ticket-index="' + index + '"><?php echo esc_js( __( 'Ce mois', 'eventlist' ) ); ?></button>' +
                            '<button type="button" class="ticket_slots_filter_btn" data-filter="next_month" data-ticket-index="' + index + '"><?php echo esc_js( __( 'Mois prochain', 'eventlist' ) ); ?></button>' +
                        '</div>' +
                        '<div class="slots_checkboxes_grid">' + slotsCheckboxesHtml + '</div>' +
                        '<p class="slots_no_results" style="display: none;"><?php echo esc_js( __( 'Aucun créneau ne correspond aux critères.', 'eventlist' ) ); ?></p>' +
                    '</div>' +
                '</div>';
            }

            // Nouveau billet = ouvert par défaut
            var html = '<div class="ticket_form_item is_expanded" data-index="' + index + '">' +
                '<div class="ticket_form_content">' +
                    '<div class="ticket_form_header ticket_accordion_header" role="button" aria-expanded="true" tabindex="0">' +
                        '<div class="ticket_header_left">' +
                            '<button type="button" class="ticket_accordion_toggle" aria-label="<?php echo esc_js( __( 'Déplier/Replier', 'eventlist' ) ); ?>">' +
                                '<svg class="accordion_chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<polyline points="6 9 12 15 18 9"></polyline>' +
                                '</svg>' +
                            '</button>' +
                            '<h4 class="ticket_title">' + this.escapeHtml(data.name) + '</h4>' +
                        '</div>' +
                        '<span class="ticket_slots_badge">' + slotsModeLabel + '</span>' +
                    '</div>' +
                    '<div class="ticket_accordion_body">' +
                    '<div class="ticket_form_field">' +
                        '<label class="field_label"><strong><?php echo esc_js( __( 'Nom du billet', 'eventlist' ) ); ?></strong> <span class="required">*</span> :</label>' +
                        '<input type="text" name="' + prefix + 'ticket[' + index + '][name_ticket]" class="billetterie_input ticket_name_input" value="' + this.escapeHtml(data.name) + '" required>' +
                    '</div>' +
                    '<div class="ticket_form_field">' +
                        '<label class="field_label"><strong><?php echo esc_js( __( 'Description du billet', 'eventlist' ) ); ?></strong></label>' +
                        '<p class="field_hint"><?php echo esc_js( __( 'Cette description sera affichée sur la page de l\'activité au niveau du billet, et également sur la version PDF du billet.', 'eventlist' ) ); ?> :</p>' +
                        '<textarea name="' + prefix + 'ticket[' + index + '][desc_ticket]" class="billetterie_textarea" rows="3">' + this.escapeHtml(data.description) + '</textarea>' +
                    '</div>' +
                    '<div class="ticket_form_row_3cols">' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Nombre total de places', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + prefix + 'ticket[' + index + '][number_total_ticket]" class="billetterie_input" min="1" value="' + data.total + '">' +
                        '</div>' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Min / réservation', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + prefix + 'ticket[' + index + '][number_min_ticket]" class="billetterie_input" min="1" value="' + data.min + '">' +
                        '</div>' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Max / réservation', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + prefix + 'ticket[' + index + '][number_max_ticket]" class="billetterie_input" min="1" value="' + data.max + '">' +
                        '</div>' +
                    '</div>' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][registration_mode]" value="' + data.registrationMode + '">' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][minutes_before]" value="' + data.minutesBefore + '">' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][start_ticket_date]" value="' + data.startDate + '">' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][start_ticket_time]" value="' + data.startTime + '">' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][close_ticket_date]" value="' + data.closeDate + '">' +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][close_ticket_time]" value="' + data.closeTime + '">' +
                    slotsSectionHtml +
                    '<input type="hidden" name="' + prefix + 'ticket[' + index + '][is_active]" value="yes" class="ticket_is_active">' +
                    '</div>' + // Fin ticket_accordion_body
                    '<div class="ticket_form_actions">' +
                        '<div class="ticket_actions_left">' +
                            '<button type="button" class="btn_save_ticket el_btn_save">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>' +
                                    '<polyline points="17 21 17 13 7 13 7 21"></polyline>' +
                                    '<polyline points="7 3 7 8 15 8"></polyline>' +
                                '</svg>' +
                                '<span class="btn_text"><?php echo esc_js( __( 'Sauvegarder ce billet', 'eventlist' ) ); ?></span>' +
                            '</button>' +
                            '<button type="button" class="btn_stop_reservation el_btn_warning" data-index="' + index + '">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                    '<circle cx="12" cy="12" r="10"></circle>' +
                                    '<line x1="10" y1="15" x2="10" y2="9"></line>' +
                                    '<line x1="14" y1="15" x2="14" y2="9"></line>' +
                                '</svg>' +
                                '<span class="btn_text"><?php echo esc_js( __( 'Stopper la réservation', 'eventlist' ) ); ?></span>' +
                            '</button>' +
                        '</div>' +
                        '<button type="button" class="btn_delete_ticket el_btn_danger" data-index="' + index + '" data-ticket-name="' + this.escapeHtml(data.name) + '">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<polyline points="3 6 5 6 21 6"></polyline>' +
                                '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>' +
                                '<line x1="10" y1="11" x2="10" y2="17"></line>' +
                                '<line x1="14" y1="11" x2="14" y2="17"></line>' +
                            '</svg>' +
                            '<span class="btn_text"><?php echo esc_js( __( 'Supprimer', 'eventlist' ) ); ?></span>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            return html;
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    };

    // ═══════════════════════════════════════════════════════════════
    // SLOT MULTI-SELECT - Gestion de la checklist des créneaux
    // ═══════════════════════════════════════════════════════════════
    var SlotMultiSelect = {
        currentFilter: 'all',

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Toggle checkbox en cliquant sur la ligne entière
            $(document).on('click', '.slot_check_item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $checkbox = $(this).find('.slot_checkbox_input');
                $checkbox.prop('checked', !$checkbox.prop('checked'));
                self.updateItemState($(this));
                self.updateChips();
            });

            // Recherche
            $(document).on('input', '.slots_search_input', function() {
                self.filterSlots($(this).val());
            });

            // Filtres par date
            $(document).on('click', '.slots_filter_btn', function() {
                var filter = $(this).data('filter');
                $('.slots_filter_btn').removeClass('active');
                $(this).addClass('active');
                self.currentFilter = filter;
                self.applyDateFilter(filter);
            });

            // Tout sélectionner
            $(document).on('click', '.slots_select_all', function() {
                self.selectAll();
            });

            // Tout désélectionner
            $(document).on('click', '.slots_deselect_all', function() {
                self.deselectAll();
            });

            // Supprimer un chip
            $(document).on('click', '.slot_chip_remove', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var slotId = $(this).closest('.slot_chip').data('slot-id');
                self.removeSlot(slotId);
            });
        },

        updateItemState: function($item) {
            var isChecked = $item.find('.slot_checkbox_input').prop('checked');
            if (isChecked) {
                $item.addClass('is_checked');
            } else {
                $item.removeClass('is_checked');
            }
        },

        applyDateFilter: function(filter) {
            var self = this;
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            // Calculer les bornes de dates
            var startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Lundi

            var endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6); // Dimanche

            var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            var startOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            var endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

            var visibleCount = 0;

            $('.slots_checklist .slot_check_item').each(function() {
                var $item = $(this);
                var slotDateStr = $item.data('slot-date');
                var slotDate = new Date(slotDateStr);
                slotDate.setHours(0, 0, 0, 0);

                var show = true;

                switch (filter) {
                    case 'this_week':
                        show = slotDate >= startOfWeek && slotDate <= endOfWeek;
                        break;
                    case 'this_month':
                        show = slotDate >= startOfMonth && slotDate <= endOfMonth;
                        break;
                    case 'next_month':
                        show = slotDate >= startOfNextMonth && slotDate <= endOfNextMonth;
                        break;
                    case 'all':
                    default:
                        show = true;
                }

                if (show) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                }
            });

            // Afficher/masquer le message "aucun résultat"
            self.updateNoResultsMessage(visibleCount);

            // Réappliquer le filtre de recherche si actif
            var searchQuery = $('.slots_search_input').val();
            if (searchQuery) {
                self.filterSlots(searchQuery);
            }
        },

        updateNoResultsMessage: function(visibleCount) {
            var $checklist = $('.slots_checklist');
            var $noResults = $checklist.find('.slots_no_results');

            if (visibleCount === 0) {
                if ($noResults.length === 0) {
                    $checklist.append('<div class="slots_no_results"><?php echo esc_js( __( 'Aucun créneau pour cette période', 'eventlist' ) ); ?></div>');
                }
                $noResults.show();
            } else {
                $noResults.hide();
            }
        },

        filterSlots: function(query) {
            var self = this;
            query = query.toLowerCase().trim();

            // D'abord appliquer le filtre de date
            self.applyDateFilter(self.currentFilter);

            // Puis filtrer par texte parmi les visibles
            if (query !== '') {
                $('.slots_checklist .slot_check_item:visible').each(function() {
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(query) === -1) {
                        $(this).hide();
                    }
                });
            }
        },

        selectAll: function() {
            var self = this;
            $('.slots_checklist .slot_check_item:visible').each(function() {
                $(this).find('.slot_checkbox_input').prop('checked', true);
                self.updateItemState($(this));
            });
            this.updateChips();
        },

        deselectAll: function() {
            var self = this;
            $('.slots_checklist .slot_check_item').each(function() {
                $(this).find('.slot_checkbox_input').prop('checked', false);
                self.updateItemState($(this));
            });
            this.updateChips();
        },

        removeSlot: function(slotId) {
            var $item = $('.slots_checklist .slot_check_item[data-slot-id="' + slotId + '"]');
            $item.find('.slot_checkbox_input').prop('checked', false);
            this.updateItemState($item);
            this.updateChips();
        },

        updateChips: function() {
            var $container = $('.slots_chips_container');
            $container.empty();

            var selectedSlots = this.getSelectedSlots();

            if (selectedSlots.length === 0) {
                $container.html('<span class="slots_chips_empty"><?php echo esc_js( __( 'Aucun créneau sélectionné', 'eventlist' ) ); ?></span>');
                return;
            }

            var chipsHtml = '<span class="slots_chips_count">' + selectedSlots.length + ' <?php echo esc_js( __( 'sélectionné(s)', 'eventlist' ) ); ?> :</span>';

            selectedSlots.forEach(function(slotId) {
                var $item = $('.slots_checklist .slot_check_item[data-slot-id="' + slotId + '"]');
                var label = $item.find('.slot_date').text() + ' - ' + $item.find('.slot_time').text();

                chipsHtml += '<span class="slot_chip" data-slot-id="' + slotId + '">' +
                    '<span class="chip_text">' + label + '</span>' +
                    '<button type="button" class="slot_chip_remove">&times;</button>' +
                '</span>';
            });

            $container.html(chipsHtml);
        },

        getSelectedSlots: function() {
            var slots = [];
            $('.slots_checklist .slot_checkbox_input:checked').each(function() {
                slots.push($(this).val());
            });
            return slots;
        },

        reset: function() {
            this.currentFilter = 'all';
            this.deselectAll();
            $('.slots_search_input').val('');
            $('.slots_filter_btn').removeClass('active');
            $('.slots_filter_btn[data-filter="all"]').addClass('active');
            this.applyDateFilter('all');
        }
    };

    // Initialiser les modules
    TicketWizard.init();
    SlotMultiSelect.init();
});
</script>
