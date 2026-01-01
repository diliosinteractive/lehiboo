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

        <!-- Créneaux Associés -->
        <div class="creneaux_associes_wrapper">
            <h5 class="subsection_title"><?php esc_html_e( 'Créneaux Associés', 'eventlist' ); ?></h5>
            <p class="subsection_hint"><?php esc_html_e( 'Sélectionnez certains ou tous les créneaux d\'activités', 'eventlist' ); ?></p>

            <div class="slots_selection">
                <label class="slots_option <?php echo $slots_mode === 'all' ? 'selected' : ''; ?>">
                    <input type="radio"
                           name="<?php echo esc_attr( $_prefix.'slots_mode' ); ?>"
                           value="all"
                           class="slots_radio"
                           <?php checked( $slots_mode, 'all' ); ?>>
                    <span class="slots_checkmark"></span>
                    <span class="slots_label"><?php esc_html_e( 'Tous les créneaux', 'eventlist' ); ?></span>
                </label>

                <label class="slots_option slots_select_option <?php echo $slots_mode === 'selected' ? 'selected' : ''; ?>">
                    <input type="radio"
                           name="<?php echo esc_attr( $_prefix.'slots_mode' ); ?>"
                           value="selected"
                           class="slots_radio"
                           <?php checked( $slots_mode, 'selected' ); ?>>
                    <span class="slots_checkmark"></span>
                    <span class="slots_label"><?php esc_html_e( 'Sélectionnez un ou plusieurs créneaux', 'eventlist' ); ?></span>
                </label>
            </div>

            <!-- Sélection de créneaux spécifiques -->
            <div class="slots_picker" style="<?php echo $slots_mode === 'selected' ? '' : 'display: none;'; ?>">
                <div class="slots_picker_row">
                    <div class="slots_picker_field">
                        <label><?php esc_html_e( 'Date de début', 'eventlist' ); ?></label>
                        <input type="date" class="billetterie_input slot_start_date" placeholder="JJ/MM/AAAA">
                    </div>
                    <div class="slots_picker_field">
                        <label><?php esc_html_e( 'Date de fin', 'eventlist' ); ?></label>
                        <input type="date" class="billetterie_input slot_end_date" placeholder="JJ/MM/AAAA">
                    </div>
                </div>
                <div class="slots_picker_row">
                    <div class="slots_picker_field slots_picker_field_select">
                        <label><?php esc_html_e( 'Sélection du créneau', 'eventlist' ); ?></label>
                        <select class="billetterie_select slot_select">
                            <option value=""><?php esc_html_e( 'Choisissez le créneau', 'eventlist' ); ?></option>
                            <?php foreach ( $event_slots as $slot ) : ?>
                                <option value="<?php echo esc_attr( $slot['id'] ); ?>"><?php echo esc_html( $slot['label'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="slots_picker_field slots_picker_field_btn">
                        <button type="button" class="btn_add_slot el_button_primary"><?php esc_html_e( 'Ajouter', 'eventlist' ); ?></button>
                    </div>
                </div>

                <!-- Liste des créneaux sélectionnés -->
                <div class="selected_slots_list">
                    <?php foreach ( $selected_slots as $index => $slot ) : ?>
                        <div class="selected_slot_item" data-slot-id="<?php echo esc_attr( $slot['id'] ); ?>">
                            <span class="slot_info"><?php echo esc_html( $slot['label'] ); ?></span>
                            <input type="hidden" name="<?php echo esc_attr( $_prefix.'selected_slots['.$index.'][id]' ); ?>" value="<?php echo esc_attr( $slot['id'] ); ?>">
                            <input type="hidden" name="<?php echo esc_attr( $_prefix.'selected_slots['.$index.'][label]' ); ?>" value="<?php echo esc_attr( $slot['label'] ); ?>">
                            <button type="button" class="btn_remove_slot"><i class="fa fa-times"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

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
                                <div class="slots_checkboxes_grid">
                                    <?php foreach ( $event_slots as $slot ) : ?>
                                    <label class="slot_checkbox_item">
                                        <input type="checkbox"
                                               name="<?php echo esc_attr( $_prefix.'ticket['.$key.'][slots][]' ); ?>"
                                               value="<?php echo esc_attr( $slot['id'] ); ?>"
                                               class="slot_checkbox"
                                               <?php checked( in_array( $slot['id'], $ticket_slots, true ) ); ?>>
                                        <span class="slot_checkbox_mark"></span>
                                        <span class="slot_checkbox_label"><?php echo esc_html( $slot['label'] ); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
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

                        <!-- Boutons d'action -->
                        <div class="ticket_form_actions">
                            <button type="button" class="btn_save_ticket el_btn_save">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                <span class="btn_text"><?php esc_html_e( 'Sauvegarder ce billet', 'eventlist' ); ?></span>
                            </button>
                            <button type="button" class="btn_stop_reservation el_btn_danger" data-index="<?php echo esc_attr( $key ); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <span class="btn_text"><?php esc_html_e( 'Stopper la réservation', 'eventlist' ); ?></span>
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

/* Tickets List */
.tickets_list_wrapper {
    margin-bottom: 24px;
}

.ticket_form_item {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
}

.ticket_form_content {
    padding: 24px;
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
    gap: 12px;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid #e2e8f0;
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
    var BilletterieManager = {
        prefix: '<?php echo esc_js( $_prefix ); ?>',
        ticketIndex: <?php echo count( $tickets ); ?>,
        tarifIndex: <?php echo count( $external_prices ); ?>,

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

            // V1 Le Hiboo - Toggle slots_mode pour chaque billet
            $(document).on('change', '.slots_mode_radio', function() {
                self.handleTicketSlotsMode($(this));
            });

            $(document).on('click', '.slots_mode_option', function() {
                var $radio = $(this).find('.slots_mode_radio');
                $radio.prop('checked', true).trigger('change');
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

            // Update UI
            $item.find('.slots_mode_option').removeClass('selected');
            $radio.closest('.slots_mode_option').addClass('selected');

            // Show/hide checkboxes
            if (mode === 'selected') {
                $item.find('.slots_checkboxes_wrapper').slideDown(200);
            } else {
                $item.find('.slots_checkboxes_wrapper').slideUp(200);
                // Optionally uncheck all checkboxes when switching to "all"
                // $item.find('.slot_checkbox').prop('checked', false);
            }
        },

        addTicket: function() {
            var self = this;
            var index = this.ticketIndex;

            var html = '<div class="ticket_form_item" data-index="' + index + '">' +
                '<div class="ticket_form_content">' +
                    '<div class="ticket_form_field">' +
                        '<label class="field_label"><strong><?php echo esc_js( __( 'Nom du billet', 'eventlist' ) ); ?></strong> <span class="required">*</span> :</label>' +
                        '<input type="text" name="' + this.prefix + 'ticket[' + index + '][name_ticket]" class="billetterie_input ticket_name_input" placeholder="<?php echo esc_js( __( 'Réservation des Petits Pouces du 5 Décembre', 'eventlist' ) ); ?>" required>' +
                    '</div>' +
                    '<div class="ticket_form_field">' +
                        '<label class="field_label"><strong><?php echo esc_js( __( 'Description du billet', 'eventlist' ) ); ?></strong></label>' +
                        '<p class="field_hint"><?php echo esc_js( __( 'Cette description sera affichée sur la page de l\'activité au niveau du billet, et également sur la version PDF du billet.', 'eventlist' ) ); ?> :</p>' +
                        '<textarea name="' + this.prefix + 'ticket[' + index + '][desc_ticket]" class="billetterie_textarea" rows="3" placeholder="<?php echo esc_js( __( 'Description du billet...', 'eventlist' ) ); ?>"></textarea>' +
                    '</div>' +
                    '<div class="ticket_form_row_3cols">' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Nombre total de places', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + this.prefix + 'ticket[' + index + '][number_total_ticket]" class="billetterie_input" min="1" placeholder="20">' +
                        '</div>' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Nombre minimum de place autorisé par réservation', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + this.prefix + 'ticket[' + index + '][number_min_ticket]" class="billetterie_input" min="1" placeholder="1" value="1">' +
                        '</div>' +
                        '<div class="ticket_form_field">' +
                            '<label class="field_label"><strong><?php echo esc_js( __( 'Nombre maximum de places autorisé par réservation', 'eventlist' ) ); ?></strong> :</label>' +
                            '<input type="number" name="' + this.prefix + 'ticket[' + index + '][number_max_ticket]" class="billetterie_input" min="1" placeholder="">' +
                        '</div>' +
                    '</div>' +
                    '<div class="ticket_form_field">' +
                        '<label class="field_label"><strong><?php echo esc_js( __( 'Période d\'inscription', 'eventlist' ) ); ?></strong> :</label>' +
                        '<div class="registration_period_options">' +
                            '<label class="registration_option selected">' +
                                '<input type="radio" name="' + this.prefix + 'ticket[' + index + '][registration_mode]" value="before_start" class="registration_radio" checked>' +
                                '<span class="registration_checkmark"></span>' +
                                '<span class="registration_text"><?php echo esc_js( __( 'Les réservations sont ouvertes jusqu\'à', 'eventlist' ) ); ?> <input type="number" name="' + this.prefix + 'ticket[' + index + '][minutes_before]" value="0" class="minutes_input" min="0" placeholder="0"> <?php echo esc_js( __( 'minute(s) avant le début de l\'activité', 'eventlist' ) ); ?></span>' +
                            '</label>' +
                            '<label class="registration_option">' +
                                '<input type="radio" name="' + this.prefix + 'ticket[' + index + '][registration_mode]" value="date_range" class="registration_radio">' +
                                '<span class="registration_checkmark"></span>' +
                                '<span class="registration_text"><?php echo esc_js( __( 'Les réservations sont ouvertes à partir du', 'eventlist' ) ); ?></span>' +
                            '</label>' +
                        '</div>' +
                        '<div class="registration_date_range" style="display: none;">' +
                            '<div class="date_range_row">' +
                                '<input type="date" name="' + this.prefix + 'ticket[' + index + '][start_ticket_date]" class="billetterie_input date_input" placeholder="JJ/MM/AAAA">' +
                                '<input type="time" name="' + this.prefix + 'ticket[' + index + '][start_ticket_time]" class="billetterie_input time_input" value="00:00">' +
                            '</div>' +
                            '<span class="date_range_separator"><?php echo esc_js( __( 'jusqu\'au', 'eventlist' ) ); ?></span>' +
                            '<div class="date_range_row">' +
                                '<input type="date" name="' + this.prefix + 'ticket[' + index + '][close_ticket_date]" class="billetterie_input date_input" placeholder="JJ/MM/AAAA">' +
                                '<input type="time" name="' + this.prefix + 'ticket[' + index + '][close_ticket_time]" class="billetterie_input time_input" value="23:59">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    // V1 Le Hiboo - Par défaut, nouveau billet disponible pour tous les créneaux
                    '<input type="hidden" name="' + this.prefix + 'ticket[' + index + '][slots_mode]" value="all">' +
                    '<input type="hidden" name="' + this.prefix + 'ticket[' + index + '][is_active]" value="yes" class="ticket_is_active">' +
                    '<div class="ticket_form_actions">' +
                        '<button type="button" class="btn_save_ticket el_btn_save">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>' +
                                '<polyline points="17 21 17 13 7 13 7 21"></polyline>' +
                                '<polyline points="7 3 7 8 15 8"></polyline>' +
                            '</svg>' +
                            '<span class="btn_text"><?php echo esc_js( __( 'Sauvegarder ce billet', 'eventlist' ) ); ?></span>' +
                        '</button>' +
                        '<button type="button" class="btn_stop_reservation el_btn_danger" data-index="' + index + '">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<circle cx="12" cy="12" r="10"></circle>' +
                                '<line x1="15" y1="9" x2="9" y2="15"></line>' +
                                '<line x1="9" y1="9" x2="15" y2="15"></line>' +
                            '</svg>' +
                            '<span class="btn_text"><?php echo esc_js( __( 'Stopper la réservation', 'eventlist' ) ); ?></span>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            $('.tickets_list_wrapper').append(html);
            this.ticketIndex++;

            // Scroll vers le nouveau billet
            $('html, body').animate({
                scrollTop: $('.ticket_form_item').last().offset().top - 100
            }, 300);
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

            // Animation de confirmation avec changement de couleur
            var originalText = $btnText.text();
            $btn.addClass('saved');
            $btnText.text('<?php echo esc_js( __( 'Sauvegardé !', 'eventlist' ) ); ?>');

            // Changer l'icône en checkmark temporairement
            var $svg = $btn.find('svg');
            var originalSvg = $svg.html();
            $svg.html('<polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>');

            setTimeout(function() {
                $btn.removeClass('saved');
                $btnText.text(originalText);
                $svg.html(originalSvg);
            }, 2500);

            if (window.ToastNotification) {
                window.ToastNotification.success('<?php echo esc_js( __( 'Billet sauvegardé', 'eventlist' ) ); ?>');
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
});
</script>
