<?php if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Template: Créneaux de l'événement
 * Design selon maquette avec checkboxes carrés oranges
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$time       = el_calendar_time_format();
$format     = el_date_time_format_js();
$first_day  = el_first_day_of_week();

$placeholder_dateformat = el_placeholder_dateformat();
$placeholder_timeformat = el_placeholder_timeformat();

// Données existantes
$calendar           = get_post_meta( $post_id, $_prefix.'calendar', true) ?: array();
$disable_date       = get_post_meta( $post_id, $_prefix.'disable_date', true) ?: array();
$disable_time_slot  = get_post_meta( $post_id, $_prefix.'disable_date_time_slot', true) ?: array();
$schedules_time     = get_post_meta( $post_id, $_prefix.'schedules_time', true) ?: array();
$option_calendar    = get_post_meta( $post_id, $_prefix.'option_calendar', true) ?: 'manual';

// Récurrence
$calendar_recurrence_id     = get_post_meta( $post_id, $_prefix.'calendar_recurrence_id', true) ?: '';
$recurrence_bydays          = get_post_meta( $post_id, $_prefix.'recurrence_bydays', true) ?: array();
$recurrence_byweekno        = get_post_meta( $post_id, $_prefix.'recurrence_byweekno', true) ?: '1';
$recurrence_byday           = get_post_meta( $post_id, $_prefix.'recurrence_byday', true) ?: '0';
$recurrence_frequency       = get_post_meta( $post_id, $_prefix.'recurrence_frequency', true) ?: 'daily';
$recurrence_interval        = get_post_meta( $post_id, $_prefix.'recurrence_interval', true) ?: '1';
$recurrence_days            = get_post_meta( $post_id, $_prefix.'recurrence_days', true) ?: '0';

$calendar_recurrence_start_time = get_post_meta( $post_id, $_prefix.'calendar_recurrence_start_time', true) ?: '';
$calendar_recurrence_end_time   = get_post_meta( $post_id, $_prefix.'calendar_recurrence_end_time', true) ?: '';
$calendar_recurrence_book_before = get_post_meta( $post_id, $_prefix.'calendar_recurrence_book_before', true) ?: '0';
$calendar_start_date            = get_post_meta( $post_id, $_prefix.'calendar_start_date', true) ?: '';
$calendar_end_date              = get_post_meta( $post_id, $_prefix.'calendar_end_date', true) ?: '';

$start_date_str = get_post_meta( $post_id, $_prefix.'start_date_str', true) ?: '';
$end_date_str   = get_post_meta( $post_id, $_prefix.'end_date_str', true) ?: '';
$ts_start       = get_post_meta( $post_id, $_prefix.'ts_start', true) ?: array();
$ts_end         = get_post_meta( $post_id, $_prefix.'ts_end', true) ?: array();

// Jours de la semaine
$days_of_the_week = array(
    '1' => __('Lundi', 'eventlist'),
    '2' => __('Mardi', 'eventlist'),
    '3' => __('Mercredi', 'eventlist'),
    '4' => __('Jeudi', 'eventlist'),
    '5' => __('Vendredi', 'eventlist'),
    '6' => __('Samedi', 'eventlist'),
    '0' => __('Dimanche', 'eventlist')
);

// Options pour le modificateur mensuel
$arr_recurrence_byweekno = array(
    '1'  => __('premier', 'eventlist'),
    '2'  => __('deuxième', 'eventlist'),
    '3'  => __('troisième', 'eventlist'),
    '4'  => __('quatrième', 'eventlist'),
    '5'  => __('cinquième', 'eventlist'),
    '-1' => __('dernier', 'eventlist')
);
?>

<div class="event_basic_block creneaux_section">
    <h4 class="heading_section"><?php esc_html_e( 'Créneaux', 'eventlist' ); ?></h4>
    <p class="field_description">
        <?php esc_html_e( 'Paramétrez un créneau ou une période pour l\'événement.', 'eventlist' ); ?>
    </p>

    <!-- Hidden fields pour dates -->
    <input type="hidden" class="event_start_date_str" name="<?php echo esc_attr( $_prefix.'start_date_str' ); ?>" value="<?php echo esc_attr( $start_date_str ); ?>">
    <input type="hidden" class="event_end_date_str" name="<?php echo esc_attr( $_prefix.'end_date_str' ); ?>" value="<?php echo esc_attr( $end_date_str ); ?>">

    <!-- Type de créneau: Ponctuel ou Récurrent -->
    <div class="vendor_field creneaux_type_field">
        <label class="field_label"><?php esc_html_e( 'L\'événement est :', 'eventlist' ); ?></label>

        <div class="creneaux_type_options">
            <label class="creneaux_type_option <?php echo ($option_calendar == 'manual') ? 'active' : ''; ?>" for="option_calendar_manual">
                <input type="radio"
                       value="manual"
                       name="<?php echo esc_attr($_prefix.'option_calendar'); ?>"
                       id="option_calendar_manual"
                       class="option_calendar_radio"
                       <?php checked($option_calendar, 'manual'); ?>>
                <span class="option_checkbox"></span>
                <span class="option_label"><?php esc_html_e( 'Ponctuel ou annuel', 'eventlist' ); ?></span>
            </label>

            <label class="creneaux_type_option <?php echo ($option_calendar == 'auto') ? 'active' : ''; ?>" for="option_calendar_auto">
                <input type="radio"
                       value="auto"
                       name="<?php echo esc_attr($_prefix.'option_calendar'); ?>"
                       id="option_calendar_auto"
                       class="option_calendar_radio"
                       <?php checked($option_calendar, 'auto'); ?>>
                <span class="option_checkbox"></span>
                <span class="option_label"><?php esc_html_e( 'Récurrent', 'eventlist' ); ?></span>
            </label>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- SECTION PONCTUEL/ANNUEL (manual) -->
    <!-- ================================================== -->
    <div class="creneaux_manual_section" style="<?php echo ($option_calendar == 'manual') ? 'display: block;' : 'display: none;'; ?>">

        <!-- Formulaire d'ajout de créneau -->
        <div class="creneaux_add_form">
            <label class="creneaux_add_form_title"><?php esc_html_e( 'Ajout d\'un créneau', 'eventlist' ); ?></label>
            <div class="creneaux_form_inline">
                <!-- Date et horaire de début -->
                <div class="creneaux_form_group">
                    <span class="form_group_label"><?php esc_html_e( 'Date et horaire de début', 'eventlist' ); ?></span>
                    <div class="form_group_inputs">
                        <input type="text"
                               class="creneaux_input creneaux_new_start_date"
                               placeholder="JJ/MM/AAAA"
                               data-format="dd/mm/yy"
                               data-firstday="<?php echo esc_attr( $first_day ); ?>"
                               autocomplete="off">
                        <input type="time"
                               class="creneaux_input creneaux_time_native creneaux_new_start_time"
                               step="900">
                    </div>
                </div>
                <!-- Date et horaire de fin -->
                <div class="creneaux_form_group">
                    <span class="form_group_label"><?php esc_html_e( 'Date et horaire de fin', 'eventlist' ); ?></span>
                    <div class="form_group_inputs">
                        <input type="text"
                               class="creneaux_input creneaux_new_end_date"
                               placeholder="JJ/MM/AAAA"
                               data-format="dd/mm/yy"
                               data-firstday="<?php echo esc_attr( $first_day ); ?>"
                               autocomplete="off">
                        <input type="time"
                               class="creneaux_input creneaux_time_native creneaux_new_end_time"
                               step="900">
                    </div>
                </div>
                <!-- Bouton Ajouter -->
                <button type="button" class="btn_add_creneaux_manual">
                    <?php esc_html_e( 'Ajouter', 'eventlist' ); ?>
                </button>
            </div>
        </div>

        <!-- Liste des créneaux -->
        <div class="creneaux_list_section">
            <!-- Titre de section -->
            <div class="creneaux_list_title">
                <?php esc_html_e( 'Les créneaux', 'eventlist' ); ?>
            </div>

            <!-- Filtre par date -->
            <div class="creneaux_filter_row">
                <div class="creneaux_filter">
                    <span class="filter_label"><?php esc_html_e( 'Filtrer par date, de', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_filter_input creneaux_filter_start"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>">
                    <span class="filter_separator"><?php esc_html_e( 'à', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_filter_input creneaux_filter_end"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>">
                    <button type="button" class="btn_filter_creneaux">
                        <?php esc_html_e( 'Filtrer', 'eventlist' ); ?>
                    </button>
                    <button type="button" class="btn_reset_filter_creneaux" style="display: none;">
                        <?php esc_html_e( 'Réinitialiser', 'eventlist' ); ?>
                    </button>
                </div>
            </div>

            <!-- Barre d'actions groupées (cachée par défaut) -->
            <div class="creneaux_bulk_actions" style="display: none;">
                <span class="bulk_count"><span class="count_number">0</span> <?php esc_html_e( 'sélectionné(s)', 'eventlist' ); ?></span>
                <button type="button" class="btn_bulk_delete">
                    <i class="fa fa-trash"></i>
                    <?php esc_html_e( 'Supprimer la sélection', 'eventlist' ); ?>
                </button>
            </div>

            <!-- En-têtes de colonnes -->
            <div class="creneaux_table_header">
                <label class="creneaux_select_all_label">
                    <input type="checkbox" class="creneaux_select_all">
                    <span class="option_checkbox"></span>
                </label>
                <span class="header_col header_date"><?php esc_html_e( 'Date de début', 'eventlist' ); ?></span>
                <div class="header_times_wrapper">
                    <span class="header_col header_start_time"><?php esc_html_e( 'Horaire de début', 'eventlist' ); ?></span>
                    <span class="header_col header_end_time"><?php esc_html_e( 'Horaire de fin', 'eventlist' ); ?></span>
                </div>
                <span class="header_col header_actions"></span>
            </div>

            <div class="creneaux_list list_calendar">
                <?php if ( $calendar ):
                    foreach ( $calendar as $key => $value ):
                        if ( !empty($value['date']) ):
                            // Formater la date de manière lisible
                            $date_timestamp = strtotime($value['date']);
                            $formatted_date = date_i18n('l j M Y', $date_timestamp);
                            ?>
                            <div class="creneaux_item item_calendar" data-key="<?php echo esc_attr($key); ?>">
                                <label class="creneaux_item_select">
                                    <input type="checkbox" class="creneaux_item_checkbox">
                                    <span class="option_checkbox"></span>
                                </label>

                                <input type="hidden"
                                       class="calendar_id"
                                       name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][calendar_id]' ); ?>"
                                       value="<?php echo esc_attr( isset( $value['calendar_id'] ) ? $value['calendar_id'] : '' ); ?>">

                                <!-- Date affichée en texte lisible -->
                                <div class="creneaux_item_date_display">
                                    <span class="date_text"><?php echo esc_html( ucfirst($formatted_date) ); ?></span>
                                    <input type="hidden"
                                           class="calendar_date"
                                           value="<?php echo esc_attr( $value['date'] ); ?>"
                                           name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][date]' ); ?>">
                                </div>

                                <div class="creneaux_item_time">
                                    <span class="time_label"><?php esc_html_e( 'De', 'eventlist' ); ?></span>
                                    <input type="time"
                                           class="creneaux_input creneaux_time_native calendar_start_time readonly"
                                           value="<?php echo esc_attr( $value['start_time'] ); ?>"
                                           name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][start_time]' ); ?>"
                                           step="900"
                                           readonly>
                                    <span class="time_label"><?php esc_html_e( 'À', 'eventlist' ); ?></span>
                                    <input type="time"
                                           class="creneaux_input creneaux_time_native calendar_end_time readonly"
                                           value="<?php echo esc_attr( $value['end_time'] ); ?>"
                                           name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_time]' ); ?>"
                                           step="900"
                                           readonly>
                                </div>

                                <!-- Date de fin cachée -->
                                <input type="hidden"
                                       class="calendar_end_date"
                                       value="<?php echo esc_attr( isset($value['end_date']) ? $value['end_date'] : '' ); ?>"
                                       name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_date]' ); ?>">

                                <!-- Booking before cachée -->
                                <input type="hidden"
                                       name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][book_before_minutes]' ); ?>"
                                       value="<?php echo esc_attr( isset($value['book_before_minutes']) ? $value['book_before_minutes'] : '0' ); ?>">

                                <div class="creneaux_item_actions">
                                    <button type="button" class="btn_edit_creneaux" title="<?php esc_attr_e( 'Modifier', 'eventlist' ); ?>">
                                        <i class="fa fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn_remove_creneaux remove_calendar" title="<?php esc_attr_e( 'Supprimer', 'eventlist' ); ?>">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif;
                    endforeach;
                endif; ?>
            </div>

            <!-- État vide -->
            <div class="creneaux_empty_state" <?php echo !empty($calendar) ? 'style="display:none;"' : ''; ?>>
                <i class="fa fa-calendar-alt"></i>
                <p><?php esc_html_e( 'Aucun créneau configuré', 'eventlist' ); ?></p>
                <span><?php esc_html_e( 'Utilisez le formulaire ci-dessus pour ajouter des créneaux', 'eventlist' ); ?></span>
            </div>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- SECTION RÉCURRENT (auto) -->
    <!-- ================================================== -->
    <div class="creneaux_auto_section auto" style="<?php echo ($option_calendar == 'auto') ? 'display: block;' : 'display: none;'; ?>">

        <!-- ÉTAPE 1 : Sélection de la période -->
        <div class="creneaux_step_section creneaux_periode_field">
            <div class="step_header">
                <span class="step_number">1</span>
                <label class="step_label">
                    <?php esc_html_e( 'Définissez la période de récurrence', 'eventlist' ); ?>
                    <span class="required">*</span>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'La période définit les dates de début et de fin pendant lesquelles votre événement sera programmé. Par exemple, du 1er janvier au 31 mars pour un trimestre.', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">
                <div class="creneaux_periode_row">
                    <div class="creneaux_periode_col">
                        <span class="periode_label"><?php esc_html_e( 'Date de début', 'eventlist' ); ?></span>
                        <input type="text"
                               class="creneaux_input calendar_start_date calendar_auto_start_date"
                               name="<?php echo esc_attr( $_prefix.'calendar_start_date' ); ?>"
                               value="<?php echo esc_attr( $calendar_start_date ); ?>"
                               placeholder="JJ/MM/AAAA"
                               data-format="dd/mm/yy"
                               data-firstday="<?php echo esc_attr( $first_day ); ?>"
                               autocomplete="off"
                               required>
                    </div>
                    <div class="creneaux_periode_col">
                        <span class="periode_label"><?php esc_html_e( 'Date de fin', 'eventlist' ); ?></span>
                        <input type="text"
                               class="creneaux_input calendar_end_date calendar_auto_end_date"
                               name="<?php echo esc_attr( $_prefix.'calendar_end_date' ); ?>"
                               value="<?php echo esc_attr( $calendar_end_date ); ?>"
                               placeholder="JJ/MM/AAAA"
                               data-format="dd/mm/yy"
                               data-firstday="<?php echo esc_attr( $first_day ); ?>"
                               autocomplete="off"
                               required>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÉTAPE 2 : Sélection de la fréquence -->
        <div class="creneaux_step_section creneaux_frequence_field">
            <div class="step_header">
                <span class="step_number">2</span>
                <label class="step_label">
                    <?php esc_html_e( 'Choisissez la fréquence de répétition', 'eventlist' ); ?>
                    <span class="required">*</span>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'La fréquence détermine à quel rythme votre événement se répète : tous les jours, toutes les semaines ou tous les mois. L\'intervalle permet d\'espacer les répétitions (ex: tous les 2 jours).', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">
                <div class="creneaux_frequence_row">
                    <span class="frequence_label"><?php esc_html_e( 'Chaque', 'eventlist' ); ?></span>
                    <select id="recurrence-frequency" name="<?php echo esc_attr( $_prefix.'recurrence_frequency' ); ?>" class="creneaux_select">
                        <option value="daily" <?php selected( $recurrence_frequency, 'daily' ); ?>><?php esc_html_e( 'jour', 'eventlist' ); ?></option>
                        <option value="weekly" <?php selected( $recurrence_frequency, 'weekly' ); ?>><?php esc_html_e( 'semaine', 'eventlist' ); ?></option>
                        <option value="monthly" <?php selected( $recurrence_frequency, 'monthly' ); ?>><?php esc_html_e( 'mois', 'eventlist' ); ?></option>
                    </select>
                    <span class="frequence_label"><?php esc_html_e( 'tous les', 'eventlist' ); ?></span>
                    <input type="number"
                           id="recurrence-interval"
                           name="<?php echo esc_attr( $_prefix.'recurrence_interval' ); ?>"
                           class="creneaux_input creneaux_interval_input"
                           value="<?php echo esc_attr( $recurrence_interval ); ?>"
                           min="1">
                    <span class="interval_desc" id="interval-daily"><?php esc_html_e( 'jour(s)', 'eventlist' ); ?></span>
                    <span class="interval_desc" id="interval-weekly"><?php esc_html_e( 'semaine(s)', 'eventlist' ); ?></span>
                    <span class="interval_desc" id="interval-monthly"><?php esc_html_e( 'mois', 'eventlist' ); ?></span>
                </div>
            </div>
        </div>

        <!-- ÉTAPE 3 : Section hebdomadaire - Design en cartes par jour -->
        <div class="creneaux_step_section creneaux_weekly_section alternate-selector" id="weekly-selector" style="<?php echo ($recurrence_frequency == 'weekly') ? 'display: block;' : 'display: none;'; ?>">
            <div class="step_header">
                <span class="step_number">3</span>
                <label class="step_label">
                    <?php esc_html_e( 'Sélectionnez les jours et horaires', 'eventlist' ); ?>
                    <span class="required">*</span>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'Activez les jours où votre événement aura lieu, puis ajoutez les créneaux horaires pour chaque jour. Vous pouvez avoir plusieurs horaires différents par jour.', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">

            <div class="weekly_days_grid ts-weekly">
                <?php foreach ( $days_of_the_week as $day_key => $day_name ):
                    $has_slots = isset( $ts_start[$day_key] ) && is_array( $ts_start[$day_key] ) && !empty( $ts_start[$day_key] );
                    $is_active = $has_slots || in_array( $day_key, $recurrence_bydays );
                ?>
                    <div class="weekly_day_card ts_recurrence_bydays <?php echo $is_active ? 'is-active' : ''; ?>" data-day="<?php echo esc_attr($day_key); ?>">
                        <!-- En-tête du jour avec toggle -->
                        <div class="day_card_header">
                            <label class="day_toggle">
                                <input type="checkbox"
                                       id="day_toggle_<?php echo $day_key; ?>"
                                       class="day_toggle_checkbox"
                                       <?php echo $is_active ? 'checked' : ''; ?>>
                                <span class="day_toggle_slider"></span>
                            </label>
                            <span class="day_card_name"><?php echo esc_html($day_name); ?></span>
                            <span class="day_slots_count"><?php
                                if ($has_slots) {
                                    $count = count($ts_start[$day_key]);
                                    echo $count . ' ' . _n('horaire', 'horaires', $count, 'eventlist');
                                }
                            ?></span>
                        </div>

                        <!-- Corps de la carte (caché si inactif) -->
                        <div class="day_card_body" style="<?php echo $is_active ? '' : 'display: none;'; ?>">
                            <!-- Liste des créneaux existants -->
                            <div class="day_slots_list ts-list">
                                <?php if ( $has_slots ):
                                    foreach ( $ts_start[$day_key] as $k_ts => $v_ts_start ):
                                        if ( isset( $ts_end[$day_key][$k_ts] ) && $ts_end[$day_key][$k_ts] ): ?>
                                            <div class="day_slot_item" data-key="<?php echo esc_attr($day_key); ?>">
                                                <input type="hidden"
                                                       name="<?php echo esc_attr( $_prefix.'recurrence_bydays[]' ); ?>"
                                                       value="<?php echo esc_attr($day_key); ?>">
                                                <div class="slot_time_display">
                                                    <input type="time"
                                                           class="slot_time_input calendar_recurrence_ts_start"
                                                           value="<?php echo esc_attr( $v_ts_start ); ?>"
                                                           name="<?php echo esc_attr( $_prefix.'ts_start['.$day_key.']['.$k_ts.']' ); ?>"
                                                           step="900">
                                                    <span class="slot_time_separator">-</span>
                                                    <input type="time"
                                                           class="slot_time_input calendar_recurrence_ts_end"
                                                           value="<?php echo esc_attr( $ts_end[$day_key][$k_ts] ); ?>"
                                                           name="<?php echo esc_attr( $_prefix.'ts_end['.$day_key.']['.$k_ts.']' ); ?>"
                                                           step="900">
                                                </div>
                                                <button type="button" class="slot_remove_btn btn_remove_time_slot">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endif;
                                    endforeach;
                                endif; ?>
                            </div>

                            <!-- Formulaire d'ajout inline -->
                            <div class="day_add_slot_form">
                                <input type="time"
                                       class="slot_time_input new_ts_start"
                                       step="900"
                                       placeholder="--:--">
                                <span class="slot_time_separator">-</span>
                                <input type="time"
                                       class="slot_time_input new_ts_end"
                                       step="900"
                                       placeholder="--:--">
                                <button type="button" class="slot_add_btn btn_add_time_slot" data-key="<?php echo esc_attr($day_key); ?>">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            </div><!-- End .step_content for weekly -->
        </div>

        <!-- ÉTAPE 3 (alternative) : Section mensuelle - Design en carte -->
        <div class="creneaux_step_section creneaux_monthly_section alternate-selector" id="monthly-selector" style="<?php echo ($recurrence_frequency == 'monthly') ? 'display: block;' : 'display: none;'; ?>">
            <div class="step_header">
                <span class="step_number">3</span>
                <label class="step_label">
                    <?php esc_html_e( 'Configurez la récurrence mensuelle', 'eventlist' ); ?>
                    <span class="required">*</span>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'Choisissez quel jour du mois votre événement se répète (ex: le 1er lundi, le 2ème mardi, etc.) et ajoutez les horaires souhaités.', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">
            <div class="monthly_config_card">
                <!-- En-tête avec sélection du jour -->
                <div class="monthly_config_header">
                    <span class="monthly_config_label"><?php esc_html_e( 'Répéter le', 'eventlist' ); ?></span>
                    <div class="monthly_selectors">
                        <select id="monthly-modifier" name="<?php echo esc_attr( $_prefix.'recurrence_byweekno' ); ?>" class="monthly_select">
                            <?php foreach ( $arr_recurrence_byweekno as $key => $value ): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byweekno, $key ); ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="recurrence-weekday" name="<?php echo esc_attr( $_prefix.'recurrence_byday' ); ?>" class="monthly_select">
                            <?php foreach ( $days_of_the_week as $key => $value ): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byday, $key ); ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="monthly_config_suffix"><?php esc_html_e( 'de chaque mois', 'eventlist' ); ?></span>
                    </div>
                </div>

                <!-- Formulaire d'ajout d'horaire -->
                <div class="monthly_add_slot">
                    <span class="monthly_add_label"><?php esc_html_e( 'Ajouter un horaire', 'eventlist' ); ?></span>
                    <div class="monthly_add_form">
                        <input type="time"
                               class="slot_time_input monthly_start_time"
                               step="900"
                               placeholder="--:--">
                        <span class="slot_time_separator">-</span>
                        <input type="time"
                               class="slot_time_input monthly_end_time"
                               step="900"
                               placeholder="--:--">
                        <button type="button" class="slot_add_btn btn_add_monthly_slot">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Message de confirmation -->
                <div class="monthly_rule_display" style="display: none;"></div>
            </div>
            </div><!-- End .step_content for monthly -->
        </div>

        <!-- ÉTAPE 3 (alternative) : Section Daily - Carte horaires (pour daily) -->
        <div class="creneaux_step_section creneaux_daily_section" id="daily-selector" style="<?php echo ($recurrence_frequency == 'daily') ? 'display: block;' : 'display: none;'; ?>">
            <div class="step_header">
                <span class="step_number">3</span>
                <label class="step_label">
                    <?php esc_html_e( 'Définissez les horaires quotidiens', 'eventlist' ); ?>
                    <span class="required">*</span>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'Ajoutez un ou plusieurs créneaux horaires qui se répéteront chaque jour de la période définie.', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">
        <div class="daily_schedules_card">
            <div class="daily_card_header">
                <span class="daily_card_title"><?php esc_html_e( 'Horaires programmés', 'eventlist' ); ?></span>
                <span class="daily_slots_count"><?php
                    if ($schedules_time && is_array($schedules_time)) {
                        $count = count($schedules_time);
                        echo $count . ' ' . _n('horaire', 'horaires', $count, 'eventlist');
                    }
                ?></span>
            </div>

            <div class="daily_card_body">
                <!-- Liste des horaires existants -->
                <div class="daily_slots_list wrap_schedules_time">
                    <?php if ( $schedules_time ):
                        foreach ( $schedules_time as $key => $value ):
                            if ( !empty($value['start_time']) ): ?>
                                <div class="daily_slot_item item_schedules_time" data-key="<?php echo esc_attr($key); ?>">
                                    <div class="slot_time_display">
                                        <input type="time"
                                               class="slot_time_input start_time"
                                               name="<?php echo esc_attr( $_prefix.'schedules_time['.$key.'][start_time]' ); ?>"
                                               value="<?php echo esc_attr( $value['start_time'] ); ?>"
                                               step="900">
                                        <span class="slot_time_separator">-</span>
                                        <input type="time"
                                               class="slot_time_input end_time"
                                               name="<?php echo esc_attr( $_prefix.'schedules_time['.$key.'][end_time]' ); ?>"
                                               value="<?php echo esc_attr( $value['end_time'] ); ?>"
                                               step="900">
                                    </div>
                                    <input type="hidden"
                                           name="<?php echo esc_attr( $_prefix.'schedules_time['.$key.'][book_before]' ); ?>"
                                           value="<?php echo esc_attr( isset($value['book_before']) ? $value['book_before'] : '0' ); ?>">
                                    <button type="button" class="slot_remove_btn remove_schedules_time">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            <?php endif;
                        endforeach;
                    endif; ?>
                </div>

                <!-- Formulaire d'ajout d'horaire -->
                <div class="daily_add_slot_form creneaux_horaire_field">
                    <input type="time"
                           class="slot_time_input calendar_recurrence_start_time"
                           name="<?php echo esc_attr( $_prefix.'calendar_recurrence_start_time' ); ?>"
                           value="<?php echo esc_attr( $calendar_recurrence_start_time ); ?>"
                           step="900"
                           placeholder="--:--">
                    <span class="slot_time_separator">-</span>
                    <input type="time"
                           class="slot_time_input calendar_recurrence_end_time"
                           name="<?php echo esc_attr( $_prefix.'calendar_recurrence_end_time' ); ?>"
                           value="<?php echo esc_attr( $calendar_recurrence_end_time ); ?>"
                           step="900"
                           placeholder="--:--">
                    <button type="button" class="slot_add_btn add_schedules_time">
                        <i class="fa fa-plus"></i>
                    </button>

                    <!-- Hidden field pour book before -->
                    <input type="hidden"
                           name="<?php echo esc_attr($_prefix.'calendar_recurrence_book_before' ); ?>"
                           class="calendar_recurrence_time_book_before"
                           value="<?php echo esc_attr( $calendar_recurrence_book_before ); ?>">
                </div>

                <!-- État vide -->
                <div class="daily_empty_state" <?php echo !empty($schedules_time) ? 'style="display:none;"' : ''; ?>>
                    <i class="fa fa-clock"></i>
                    <span><?php esc_html_e( 'Ajoutez des horaires avec le formulaire ci-dessus', 'eventlist' ); ?></span>
                </div>
            </div>
        </div>
            </div><!-- End .step_content for daily -->
        </div>

        <!-- ÉTAPE 4 : Prévisualisation des créneaux générés -->
        <div class="creneaux_step_section creneaux_preview_section">
            <div class="step_header">
                <span class="step_number">4</span>
                <label class="step_label">
                    <?php esc_html_e( 'Prévisualisation des créneaux', 'eventlist' ); ?>
                </label>
                <button type="button" class="step_info_btn" data-tooltip="<?php esc_attr_e( 'Visualisez tous les créneaux qui seront générés selon votre configuration. Cliquez sur \"Générer\" pour actualiser la prévisualisation.', 'eventlist' ); ?>">
                    <i class="fa fa-info-circle"></i>
                </button>
            </div>
            <div class="step_content">

            <!-- Filtre par date -->
            <div class="creneaux_filter_row">
                <div class="creneaux_filter">
                    <span class="filter_label"><?php esc_html_e( 'Filtrer par date, de', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_filter_input creneaux_preview_filter_start"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>">
                    <span class="filter_separator"><?php esc_html_e( 'à', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_filter_input creneaux_preview_filter_end"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>">
                    <button type="button" class="btn_filter_creneaux btn_filter_preview">
                        <?php esc_html_e( 'Filtrer', 'eventlist' ); ?>
                    </button>
                    <button type="button" class="btn_reset_filter_creneaux btn_reset_preview_filter" style="display: none;">
                        <?php esc_html_e( 'Réinitialiser', 'eventlist' ); ?>
                    </button>
                </div>
            </div>

            <!-- En-têtes de colonnes -->
            <div class="creneaux_table_header">
                <label class="creneaux_select_all_label">
                    <input type="checkbox" class="creneaux_preview_select_all">
                    <span class="option_checkbox"></span>
                </label>
                <span class="header_col header_date"><?php esc_html_e( 'Date de début', 'eventlist' ); ?></span>
                <div class="header_times_wrapper">
                    <span class="header_col header_start_time"><?php esc_html_e( 'Horaire de début', 'eventlist' ); ?></span>
                    <span class="header_col header_end_time"><?php esc_html_e( 'Horaire de fin', 'eventlist' ); ?></span>
                </div>
                <span class="header_col header_actions"></span>
            </div>

            <!-- Liste des créneaux générés -->
            <div class="creneaux_list creneaux_preview_list">
                <!-- Peuplé par JavaScript -->
            </div>

            <!-- État vide -->
            <div class="creneaux_preview_empty_state">
                <i class="fa fa-calendar-alt"></i>
                <p><?php esc_html_e( 'Aucun créneau généré', 'eventlist' ); ?></p>
                <span><?php esc_html_e( 'Configurez la période, la fréquence et les horaires ci-dessus pour voir les créneaux générés', 'eventlist' ); ?></span>
            </div>

            <!-- Bouton générer/rafraîchir -->
            <div class="creneaux_preview_actions">
                <button type="button" class="btn_generate_preview">
                    <i class="fa fa-sync-alt"></i>
                    <?php esc_html_e( 'Générer la prévisualisation', 'eventlist' ); ?>
                </button>
                <span class="preview_count"></span>
            </div>
            </div><!-- End .step_content for preview -->
        </div>

        <!-- ÉTAPE 5 : Désactivation de créneaux -->
        <div class="vendor_field creneaux_disable_field disable_date">
            <label class="field_label"><?php esc_html_e( 'Désactivez un créneau :', 'eventlist' ); ?></label>

            <div class="creneaux_disable_form">
                <div class="creneaux_disable_row">
                    <span class="disable_label"><?php esc_html_e( 'Du', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_input new_disable_start_date"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>"
                           autocomplete="off">
                    <span class="disable_label"><?php esc_html_e( 'au', 'eventlist' ); ?></span>
                    <input type="text"
                           class="creneaux_input new_disable_end_date"
                           placeholder="JJ/MM/AAAA"
                           data-format="dd/mm/yy"
                           data-firstday="<?php echo esc_attr( $first_day ); ?>"
                           autocomplete="off">
                    <span class="disable_label"><?php esc_html_e( 'Créneau', 'eventlist' ); ?></span>
                    <select class="creneaux_select new_disable_schedule">
                        <option value=""><?php esc_html_e( 'Tous', 'eventlist' ); ?></option>
                        <?php if ( $schedules_time ):
                            foreach ( $schedules_time as $key => $value ): ?>
                                <option value="<?php echo esc_attr($key); ?>">
                                    <?php echo esc_html($value['start_time'] . ' - ' . $value['end_time']); ?>
                                </option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                    <button type="button" class="btn_add_disable add_disable_date"><?php esc_html_e( 'Ajouter', 'eventlist' ); ?></button>
                </div>
            </div>

            <!-- Liste des dates désactivées -->
            <div class="creneaux_disable_list wrap_disable_date">
                <?php if ( $disable_date ):
                    foreach ( $disable_date as $key => $value ):
                        if ( !empty($value['start_date']) ): ?>
                            <div class="creneaux_disable_item item_disable_date">
                                <span class="disable_info">
                                    <?php esc_html_e( 'Du', 'eventlist' ); ?>
                                    <input type="text"
                                           class="creneaux_input start_date"
                                           name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][start_date]' ); ?>"
                                           value="<?php echo esc_attr( $value['start_date'] ); ?>"
                                           placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
                                           data-format="<?php echo esc_attr( $format ); ?>"
                                           data-firstday="<?php echo esc_attr( $first_day ); ?>"
                                           autocomplete="off"
                                           readonly>
                                    <?php esc_html_e( 'au', 'eventlist' ); ?>
                                    <input type="text"
                                           class="creneaux_input end_date"
                                           name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][end_date]' ); ?>"
                                           value="<?php echo esc_attr( isset($value['end_date']) ? $value['end_date'] : '' ); ?>"
                                           placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
                                           data-format="<?php echo esc_attr( $format ); ?>"
                                           data-firstday="<?php echo esc_attr( $first_day ); ?>"
                                           autocomplete="off"
                                           readonly>
                                </span>
                                <?php if ( $schedules_time ): ?>
                                    <select name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][schedules_time]' ); ?>" class="creneaux_select schedules_time">
                                        <option value=""><?php esc_html_e( 'Tous', 'eventlist' ); ?></option>
                                        <?php foreach ( $schedules_time as $s_key => $s_value ):
                                            $disable_time = isset( $value['schedules_time'] ) ? $value['schedules_time'] : ''; ?>
                                            <option value="<?php echo esc_attr($s_key); ?>" <?php selected( $s_key, $disable_time ); ?>>
                                                <?php echo esc_html($s_value['start_time'] . ' - ' . $s_value['end_time']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                                <button type="button" class="btn_remove_disable remove_disable_date">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        <?php endif;
                    endforeach;
                endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
/* ==========================================================================
   Créneaux Section - Design compact et horizontal
   ========================================================================== */

/* Forcer le masquage du jQuery timepicker */
.creneaux_section .ui-timepicker-wrapper,
.creneaux_section .ui-timepicker-list,
.ui-timepicker-wrapper,
.ui-timepicker-list {
    display: none !important;
    visibility: hidden !important;
}

/* ==========================================================================
   Step Sections - Configuration par étapes
   ========================================================================== */

.creneaux_step_section {
    margin-bottom: 28px;
    padding: 24px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.creneaux_step_section:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* Step Header */
.step_header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

/* Step Number Badge */
.step_number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #FF6600 0%, #e55a00 100%);
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(255, 102, 0, 0.3);
}

/* Step Label */
.step_label {
    flex: 1;
    font-weight: 600;
    font-size: 15px;
    color: #1f2937;
    margin: 0;
    line-height: 1.4;
}

.step_label .required {
    color: #ef4444;
    font-weight: 700;
    margin-left: 4px;
}

/* Step Info Button (Tooltip) */
.step_info_btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    position: relative;
}

.step_info_btn:hover {
    background: #FF6600;
    border-color: #FF6600;
    color: #fff;
}

.step_info_btn i {
    font-size: 14px;
}

/* Tooltip Popover */
.step_info_btn::after {
    content: attr(data-tooltip);
    position: absolute;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-right: 12px;
    padding: 12px 16px;
    background: #1f2937;
    color: #fff;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
    border-radius: 8px;
    width: 280px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    pointer-events: none;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.step_info_btn::before {
    content: '';
    position: absolute;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-right: 4px;
    border: 6px solid transparent;
    border-left-color: #1f2937;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 101;
}

.step_info_btn:hover::after,
.step_info_btn:hover::before {
    opacity: 1;
    visibility: visible;
}

/* Step Content */
.step_content {
    /* Contenu des étapes */
}

/* Responsive - Tooltip en dessous sur mobile */
@media (max-width: 768px) {
    .step_header {
        flex-wrap: wrap;
    }

    .step_label {
        flex: 0 0 calc(100% - 80px);
        font-size: 14px;
    }

    .step_info_btn::after {
        right: auto;
        left: 50%;
        top: 100%;
        transform: translateX(-50%);
        margin-right: 0;
        margin-top: 12px;
        width: 250px;
    }

    .step_info_btn::before {
        right: auto;
        left: 50%;
        top: 100%;
        transform: translateX(-50%);
        margin-right: 0;
        margin-top: 0;
        border: 6px solid transparent;
        border-bottom-color: #1f2937;
        border-left-color: transparent;
    }
}

/* Support de l'ancienne structure .ts-item avant conversion */
.creneaux_day_slots .ts-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
}

.creneaux_day_slots .ts-item input {
    height: 42px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    width: 130px;
}

.creneaux_day_slots .ts-item .close {
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 6px;
    background: #e74c3c;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-left: auto;
}

.creneaux_section {
    padding: 0;
}

.creneaux_section .field_description {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
}

/* Type de créneau - Checkboxes carrés oranges */
.creneaux_type_field {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.creneaux_type_field .field_label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: #222;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.creneaux_type_options {
    display: flex;
    gap: 32px;
}

.creneaux_type_option {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 8px 0;
    position: relative;
}

.creneaux_type_option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.creneaux_type_option .option_checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 4px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    flex-shrink: 0;
}

.creneaux_type_option:hover .option_checkbox {
    border-color: #FF6600;
}

.creneaux_type_option.active .option_checkbox,
.creneaux_type_option input:checked + .option_checkbox {
    background: #FF6600;
    border-color: #FF6600;
}

.creneaux_type_option .option_label {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

/* Inputs généraux */
.creneaux_input {
    height: 42px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    color: #333;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.creneaux_input:hover {
    border-color: #bbb;
}

.creneaux_input:focus {
    border-color: #333;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
}

.creneaux_input::placeholder {
    color: #aaa;
}

.creneaux_interval_input {
    width: 55px;
    text-align: center;
}

/* Input time natif HTML5 */
.creneaux_time_native {
    width: 130px;
    text-align: center;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.creneaux_time_native::-webkit-calendar-picker-indicator {
    cursor: pointer;
    padding: 4px;
    margin-right: -4px;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.creneaux_time_native::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

/* Select */
.creneaux_select {
    height: 42px;
    padding: 0 32px 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    color: #333;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 10px center;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    transition: border-color 0.2s;
}

.creneaux_select:hover {
    border-color: #bbb;
}

.creneaux_select:focus {
    border-color: #333;
    outline: none;
}

/* Boutons d'action */
.btn_add_horaire,
.btn_add_time_slot,
.btn_add_monthly_rule,
.btn_add_disable {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #FF6600;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn_add_horaire:hover,
.btn_add_time_slot:hover,
.btn_add_monthly_rule:hover,
.btn_add_disable:hover {
    background: #e55b00;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
}

/* Bouton Ajouter créneau - Style outline vert */
/* !important pour overrider le sélecteur générique [class*=add_] du thème */
.btn_add_creneaux_manual {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 12px 24px;
    background: #ff601f !important;
    border: 2px solid #ff601f !important;
    border-radius: 8px;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn_add_creneaux_manual:hover {
    background: #ff601f !important;
    border: 2px solid #ff601f !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn_add_creneaux_manual i {
    font-size: 13px;
}

/* Boutons modifier/supprimer */
/* Bouton éditer/valider - style avec bordure comme la maquette */
.btn_edit_creneaux {
    width: 42px;
    height: 42px;
    border: 2px solid #222;
    border-radius: 8px;
    background: #fff;
    color: #222;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.2s;
}

.btn_edit_creneaux:hover {
    background: #f5f5f5;
    border-color: #000;
}

/* Bouton en mode édition (affiche check) - fond vert */
.btn_edit_creneaux .fa-check {
    color: #10B981;
}

/* Inputs readonly - style grisé/validé */
.creneaux_input.readonly {
    background: #f5f5f5;
    color: #666;
    cursor: default;
}

/* Bouton supprimer - rouge comme la maquette */
.btn_remove_creneaux {
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 8px;
    background: #e74c3c;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.2s;
}

.btn_remove_creneaux:hover {
    background: #c0392b;
}

/* Autres boutons de suppression */
.btn_remove_time_slot,
.btn_remove_schedule,
.btn_remove_disable {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 6px;
    background: #e74c3c;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.2s;
}

.btn_remove_time_slot:hover,
.btn_remove_schedule:hover,
.btn_remove_disable:hover {
    background: #c0392b;
}

/* ==========================================================================
   Section Ponctuel - Formulaire d'ajout
   ========================================================================== */

.creneaux_add_form {
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 24px;
}

.creneaux_add_form_title {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 14px;
}

/* Layout inline: tout sur une ligne */
.creneaux_form_inline {
    display: flex;
    align-items: flex-end;
    gap: 24px;
    flex-wrap: wrap;
    justify-content: space-between;
}

.creneaux_form_group {
    display: flex;
    flex-direction: column;
    gap: 8px;
        width: 40%;
}

.form_group_label {
    font-size: 13px;
    font-weight: 500;
    color: #555;
}

.form_group_inputs {
    display: flex;
    gap: 8px;
    align-items: center;
}

.form_group_inputs .creneaux_input {
    width: 130px;
}

.form_group_inputs .creneaux_time_native {
    width: 100px;
}

/* Ancien style pour compatibilité */
.creneaux_form_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.creneaux_form_col label {
    display: block;
    font-weight: 600;
    font-size: 12px;
    color: #222;
    margin-bottom: 6px;
}

.creneaux_form_col .creneaux_input {
    width: 100%;
}

.creneaux_form_col .creneaux_time_native {
    width: 100%;
}

/* Liste des créneaux */
.creneaux_list_section {
    margin-top: 24px;
}

/* Titre de section */
.creneaux_list_title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 16px;
}

/* Ligne de filtre - aligné à droite */
.creneaux_filter_row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 16px;
}

.creneaux_filter {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #666;
}

.creneaux_filter .filter_label {
    font-weight: 500;
    color: #555;
    white-space: nowrap;
}

.creneaux_filter .filter_separator {
    color: #888;
}

.creneaux_filter_input {
    width: 120px;
    height: 38px;
    padding: 0 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    background: #fff;
}

.creneaux_filter_input:focus {
    border-color: #FF6600;
    outline: none;
}

/* Bouton Filtrer - Orange */
.btn_filter_creneaux {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    background: #FF6600;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn_filter_creneaux:hover {
    background: #e55b00;
}

/* Bouton Réinitialiser - Gris */
.btn_reset_filter_creneaux {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #64748b;
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn_reset_filter_creneaux:hover {
    background: #e2e8f0;
    color: #475569;
}

/* Barre d'actions groupées */
.creneaux_bulk_actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 8px;
    margin-bottom: 12px;
    animation: slideDown 0.2s ease-out;
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

.creneaux_bulk_actions .bulk_count {
    font-size: 14px;
    font-weight: 600;
    color: #92400e;
}

.creneaux_bulk_actions .count_number {
    font-weight: 700;
    color: #d97706;
}

.btn_bulk_delete {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #ef4444;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn_bulk_delete:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn_bulk_delete i {
    font-size: 14px;
}

/* En-têtes de colonnes du tableau - utiliser grid pour alignement parfait */
.creneaux_table_header {
    display: grid;
    grid-template-columns: 40px 200px 1fr 100px;
    align-items: center;
    gap: 16px;
    padding: 12px 20px;
    background: #f8f9fa;
    border: 1px solid #e8e8e8;
    border-radius: 8px 8px 0 0;
    border-bottom: none;
}

.creneaux_table_header .header_col {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}

/* Header date */
.creneaux_table_header .header_date {
    /* Occupera la 2ème colonne de la grid */
}

/* Header des horaires - aligné avec les inputs des items */
.creneaux_table_header .header_times_wrapper {
    display: flex;
    align-items: center;
        justify-content: space-around;
}

.creneaux_table_header .header_start_time {
    /* Position: "De" (20px) + gap (12px) + demi-input (55px) = 87px du début */
    /* Largeur totale section start: "De" + gap + input + gap = 20 + 12 + 110 + 12 = 154px */
    width: 154px;
    text-align: center;
    padding-left: 20px; /* Décalage pour centrer sur l'input, pas sur "De" */
}

.creneaux_table_header .header_end_time {
    /* Position après start section: "À" (10px) + gap (12px) + input (110px) */
    width: 132px;
    text-align: center;
    padding-left: 10px; /* Décalage pour centrer sur l'input, pas sur "À" */
}

.creneaux_table_header .header_actions {
    /* Occupera la dernière colonne */
}

.creneaux_select_all_label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    color: #333;
    flex-shrink: 0;
}

.creneaux_select_all_label input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.creneaux_select_all_label .option_checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #ccc;
    border-radius: 4px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.creneaux_select_all_label:hover .option_checkbox {
    border-color: #FF6600;
}

.creneaux_select_all_label input:checked + .option_checkbox {
    background: #FF6600;
    border-color: #FF6600;
}

/* Items de créneau */
.creneaux_list {
    display: flex;
    flex-direction: column;
    border: 1px solid #e8e8e8;
    border-radius: 0 0 8px 8px;
    overflow: hidden;
}

.creneaux_item {
    display: grid;
    grid-template-columns: 40px 200px 1fr 100px;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #fff;
    border-bottom: 1px solid #e8e8e8;
    transition: all 0.2s;
}

.creneaux_item:last-child {
    border-bottom: none;
}

.creneaux_item:hover {
    background: #fafafa;
}

.creneaux_item_select {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.creneaux_item_select input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.creneaux_item_select .option_checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #ccc;
    border-radius: 4px;
    background: #fff;
    transition: all 0.15s ease;
}

.creneaux_item_select:hover .option_checkbox {
    border-color: #FF6600;
}

.creneaux_item_select input:checked + .option_checkbox {
    background: #FF6600;
    border-color: #FF6600;
}

/* Date affichée en texte lisible */
.creneaux_item_date_display {
    /* Grid colonne 2 */
}

.creneaux_item_date_display .date_text {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

/* Ancien style pour compatibilité */
.creneaux_item_date {
    flex: 0 0 150px;
}

.creneaux_item_date .creneaux_input {
    width: 100%;
    height: 38px;
    background: #f9f9f9;
}

.creneaux_item_time {
    display: flex;
    align-items: center;
    gap: 12px;
    /* Grid colonne 3 (1fr) */
}

.creneaux_item_time .creneaux_time_native {
    width: 110px;
    height: 42px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0 10px;
    font-size: 14px;
    text-align: center;
}

.time_label {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.creneaux_item_actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    /* Grid colonne 4 */
}

/* État vide */
.creneaux_empty_state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
    background: #fafafa;
    border: 2px dashed #ddd;
    border-radius: 10px;
}

.creneaux_empty_state i {
    font-size: 40px;
    color: #ccc;
    margin-bottom: 12px;
}

.creneaux_empty_state p {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin: 0 0 6px;
}

.creneaux_empty_state span {
    font-size: 13px;
    color: #888;
}

/* ==========================================================================
   Section Récurrent - Layout horizontal compact
   ========================================================================== */

.creneaux_auto_section {
    overflow: visible;
}

.creneaux_auto_section .field_label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: #222;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Période - Horizontal sur une ligne */
.creneaux_periode_field {
    /* Margin géré par creneaux_step_section */
}

.creneaux_periode_row {
    display: flex;
    align-items: center;
    gap: 20px;
}

.creneaux_periode_col {
    display: flex;
    align-items: center;
    gap: 10px;
}

.periode_label {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.creneaux_periode_col .creneaux_input {
    width: 140px;
}

/* Fréquence - Tout sur une ligne */
.creneaux_frequence_field {
    /* Margin géré par creneaux_step_section */
}

.creneaux_frequence_row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.frequence_label {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.interval_desc {
    display: none;
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.interval_desc.active {
    display: inline;
}

/* ==========================================================================
   NOUVEAU DESIGN - Sections Récurrentes (Weekly/Monthly/Daily)
   ========================================================================== */

/* Sections alternatives (weekly/monthly) */
.alternate-selector {
    overflow: visible;
}

/* ==========================================================================
   Section hebdomadaire - Design en cartes
   ========================================================================== */
.creneaux_weekly_section {
    /* Margin géré par creneaux_step_section */
}

.weekly_days_grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

/* Carte de jour */
.weekly_day_card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.weekly_day_card:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.weekly_day_card.is-active {
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

/* En-tête de carte jour */
.day_card_header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f9fafb;
    border-bottom: 1px solid transparent;
    cursor: pointer;
    transition: background 0.2s;
}

.weekly_day_card.is-active .day_card_header {
    border-bottom-color: #e5e7eb;
}

.day_card_header:hover {
    background: #f3f4f6;
}

/* Toggle switch jour */
.day_toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.day_toggle_checkbox {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.day_toggle_slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    border-radius: 24px;
    transition: all 0.3s ease;
}

.day_toggle_slider::before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.day_toggle_checkbox:checked + .day_toggle_slider {
    background-color: #FF6600;
}

.day_toggle_checkbox:checked + .day_toggle_slider::before {
    transform: translateX(20px);
}

/* Nom du jour */
.day_card_name {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    flex: 1;
}

/* Compteur d'horaires */
.day_slots_count {
    font-size: 12px;
    color: #6b7280;
    background: #e5e7eb;
    padding: 2px 8px;
    border-radius: 10px;
}

.weekly_day_card.is-active .day_slots_count {
    background: #fff2e8;
    color: #FF6600;
}

/* Corps de la carte */
.day_card_body {
    padding: 12px 16px 16px;
}

/* Liste des créneaux */
.day_slots_list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

/* Item créneau */
.day_slot_item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.slot_time_display {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.slot_time_input {
    width: 100px;
    height: 38px;
    padding: 0 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    color: #1f2937;
    background: #fff;
    text-align: center;
    transition: all 0.2s;
}

.slot_time_input:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

/* États d'erreur pour les horaires invalides */
.slot_time_input.time_invalid,
.creneaux_input.time_invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}

.has_invalid_time {
    border-color: #fca5a5 !important;
    background-color: #fef2f2 !important;
}

.day_slot_item.has_invalid_time,
.daily_slot_item.has_invalid_time {
    animation: shake 0.3s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.slot_time_separator {
    color: #9ca3af;
    font-size: 14px;
    font-weight: 500;
}

/* Bouton supprimer */
.slot_remove_btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 6px;
    background: #fef2f2;
    color: #ef4444;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.slot_remove_btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Formulaire d'ajout inline */
.day_add_slot_form {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #fff;
    border: 1px dashed #d1d5db;
    border-radius: 8px;
}

/* Bouton ajouter */
.slot_add_btn {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 6px;
    background: #FF6600;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.slot_add_btn:hover {
    background: #e55b00;
    transform: translateY(-1px);
}

/* ==========================================================================
   Section mensuelle - Design en carte
   ========================================================================== */
.creneaux_monthly_section {
    margin-bottom: 24px;
    overflow: visible;
}

.monthly_config_card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
}

.monthly_config_header {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.monthly_config_label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.monthly_selectors {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.monthly_select {
    height: 42px;
    padding: 0 36px 0 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    color: #1f2937;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
    cursor: pointer;
    -webkit-appearance: none;
    transition: all 0.2s;
    min-width: 130px;
}

.monthly_select:focus {
    outline: none;
    border-color: #FF6600;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

.monthly_config_suffix {
    font-size: 14px;
    color: #6b7280;
}

/* Formulaire d'ajout horaire mensuel */
.monthly_add_slot {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.monthly_add_label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.monthly_add_form {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px dashed #d1d5db;
}

/* Confirmation de règle mensuelle */
.monthly_rule_display {
    margin-top: 16px;
    padding: 12px 16px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    color: #166534;
    font-size: 13px;
    display: flex;
    align-items: center;
}

.monthly_rule_display i {
    margin-right: 10px;
    color: #22c55e;
}

/* ==========================================================================
   Section Daily - Carte horaires programmés
   ========================================================================== */
.daily_schedules_card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
}

.daily_card_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.daily_card_title {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
}

.daily_slots_count {
    font-size: 12px;
    color: #6b7280;
    background: #e5e7eb;
    padding: 3px 10px;
    border-radius: 10px;
}

.daily_card_body {
    padding: 16px 20px;
}

/* Liste des horaires daily */
.daily_slots_list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}

.daily_slot_item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

/* Formulaire d'ajout daily */
.daily_add_slot_form {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #fff;
    border: 1px dashed #d1d5db;
    border-radius: 8px;
}

/* État vide daily */
.daily_empty_state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px;
    color: #9ca3af;
    text-align: center;
}

.daily_empty_state i {
    font-size: 28px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.daily_empty_state span {
    font-size: 13px;
}

/* Compatibilité anciennes classes */
.creneaux_horaire_field {
    margin-bottom: 0;
}

.wrap_schedules_time {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.creneaux_schedules_section {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .weekly_days_grid {
        grid-template-columns: 1fr;
    }

    .monthly_selectors {
        flex-direction: column;
        align-items: flex-start;
    }

    .monthly_select {
        width: 100%;
    }

    .day_add_slot_form,
    .monthly_add_form,
    .daily_add_slot_form {
        flex-wrap: wrap;
    }

    .slot_time_input {
        flex: 1;
        min-width: 80px;
    }
}

/* Désactivation de créneaux - Tout sur une ligne */
.creneaux_disable_field {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.creneaux_disable_form {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 12px;
}

.creneaux_disable_row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.creneaux_disable_row .creneaux_input {
    width: 130px;
}

.creneaux_disable_row .creneaux_select {
    min-width: 120px;
}

.disable_label {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.creneaux_disable_list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.creneaux_disable_item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #fff8f5;
    border: 1px solid #ffe4d9;
    border-radius: 6px;
}

.disable_info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #666;
}

.disable_info .creneaux_input {
    width: 120px;
    height: 36px;
    background: #fff;
}

/* ==========================================================================
   Responsive - Uniquement pour très petits écrans
   ========================================================================== */

@media (max-width: 992px) {
    .creneaux_form_grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .creneaux_type_options {
        flex-direction: column;
        gap: 8px;
    }

    .creneaux_form_inline {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }

    .creneaux_form_group {
        width: 100%;
    }

    .form_group_inputs {
        flex-wrap: wrap;
    }

    .form_group_inputs .creneaux_input,
    .form_group_inputs .creneaux_time_native {
        flex: 1;
        min-width: 100px;
    }

    .btn_add_creneaux_manual {
        width: 100%;
        justify-content: center;
    }

    .creneaux_form_grid {
        grid-template-columns: 1fr;
    }

    .creneaux_list_header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .creneaux_filter {
        flex-wrap: wrap;
    }

    .creneaux_item {
        flex-wrap: wrap;
    }

    .creneaux_item_date {
        flex: 1 1 100%;
    }

    .creneaux_item_time {
        flex: 1 1 100%;
        flex-wrap: wrap;
    }

    .creneaux_periode_row {
        flex-wrap: wrap;
    }

    .creneaux_frequence_row {
        flex-wrap: wrap;
    }

    .creneaux_horaire_row {
        flex-wrap: wrap;
    }

    .creneaux_monthly_row {
        flex-wrap: wrap;
    }

    .creneaux_day_row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .creneaux_day_checkbox {
        min-width: auto;
    }

    .creneaux_time_slot,
    .creneaux_add_time_slot {
        flex-wrap: wrap;
    }

    .creneaux_disable_row {
        flex-wrap: wrap;
    }

    .creneaux_disable_item {
        flex-wrap: wrap;
    }

    .disable_info {
        flex-wrap: wrap;
    }
}

/* ==========================================================================
   Section Prévisualisation des créneaux récurrents
   ========================================================================== */
.creneaux_preview_section {
    /* Styles gérés par creneaux_step_section */
}

.creneaux_preview_section .creneaux_list_title {
    display: none; /* Maintenant dans step_header */
}

.creneaux_preview_section .creneaux_table_header,
.creneaux_preview_section .creneaux_list {
    margin-top: 0;
}

.creneaux_preview_empty_state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
    background: #fafafa;
    border: 2px dashed #ddd;
    border-radius: 10px;
    margin-top: 16px;
}

.creneaux_preview_empty_state i {
    font-size: 40px;
    color: #ccc;
    margin-bottom: 12px;
}

.creneaux_preview_empty_state p {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin: 0 0 6px;
}

.creneaux_preview_empty_state span {
    font-size: 13px;
    color: #888;
}

.creneaux_preview_actions {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
}

.btn_generate_preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #FF6600;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn_generate_preview:hover {
    background: #e55b00;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
}

.btn_generate_preview i {
    font-size: 14px;
}

.btn_generate_preview.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.preview_count {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.preview_count strong {
    color: #FF6600;
}

/* Masquer le tableau et état vide selon le contexte */
.creneaux_preview_section.has-items .creneaux_preview_empty_state {
    display: none;
}

.creneaux_preview_section:not(.has-items) .creneaux_table_header,
.creneaux_preview_section:not(.has-items) .creneaux_preview_list,
.creneaux_preview_section:not(.has-items) .creneaux_filter_row {
    display: none;
}

/* Item de prévisualisation avec style légèrement différent */
.creneaux_preview_list .creneaux_item {
    background: #fefefe;
}

.creneaux_preview_list .creneaux_item:hover {
    background: #f8f9fa;
}
</style>

<script>
(function($) {
    'use strict';

    var CreneauxManager = {
        calendarIndex: <?php echo !empty($calendar) ? max(array_keys($calendar)) + 1 : 0; ?>,
        scheduleIndex: <?php echo !empty($schedules_time) ? max(array_keys($schedules_time)) + 1 : 0; ?>,
        disableIndex: <?php echo !empty($disable_date) ? max(array_keys($disable_date)) + 1 : 0; ?>,

        // Noms des jours de la semaine pour le lookup
        dayNames: {
            '0': '<?php echo esc_js(__('Dimanche', 'eventlist')); ?>',
            '1': '<?php echo esc_js(__('Lundi', 'eventlist')); ?>',
            '2': '<?php echo esc_js(__('Mardi', 'eventlist')); ?>',
            '3': '<?php echo esc_js(__('Mercredi', 'eventlist')); ?>',
            '4': '<?php echo esc_js(__('Jeudi', 'eventlist')); ?>',
            '5': '<?php echo esc_js(__('Vendredi', 'eventlist')); ?>',
            '6': '<?php echo esc_js(__('Samedi', 'eventlist')); ?>'
        },

        init: function() {
            var self = this;
            this.disableJqueryTimepicker();
            this.fixExistingDayNames();
            this.bindEvents();
            this.updateIntervalDesc();
            this.updateEmptyState();
            this.initWeeklySlots();

            // Réappliquer la désactivation après un délai (au cas où d'autres scripts s'initialisent)
            setTimeout(function() {
                self.disableJqueryTimepicker();
            }, 500);
            setTimeout(function() {
                self.disableJqueryTimepicker();
            }, 1500);
        },

        // Corriger les noms de jours "undefined" et convertir l'ancienne structure
        fixExistingDayNames: function() {
            var self = this;
            var prefix = '<?php echo $_prefix; ?>';

            // Gérer les deux structures : nouvelle (.creneaux_time_slot) et ancienne (.ts-item)
            $('.creneaux_day_slots .ts-item, .creneaux_time_slot, .creneaux_add_time_slot').each(function() {
                var $row = $(this);
                var $dayName = $row.find('.day_name');
                var dayKey = $row.data('key') || $row.closest('.creneaux_day_block, .ts_recurrence_bydays').data('day');

                // Si c'est l'ancienne structure sans .day_name, la reconstruire
                if ($dayName.length === 0 && dayKey !== undefined) {
                    var dayNameText = self.dayNames[String(dayKey)] || '';

                    // Vérifier si c'est un .ts-item (ancienne structure)
                    if ($row.hasClass('ts-item') && !$row.hasClass('creneaux_time_slot')) {
                        // Récupérer les valeurs des inputs existants
                        var $startInput = $row.find('input[name*="ts_start"]');
                        var $endInput = $row.find('input[name*="ts_end"]');
                        var startVal = $startInput.val() || '';
                        var endVal = $endInput.val() || '';
                        var startName = $startInput.attr('name') || '';
                        var endName = $endInput.attr('name') || '';

                        // Reconstruire la ligne avec la nouvelle structure
                        var newHtml = `
                            <label class="creneaux_day_checkbox">
                                <input type="checkbox" name="${prefix}recurrence_bydays[]" value="${dayKey}" checked class="slot_day_checkbox">
                                <span class="option_checkbox"></span>
                                <span class="day_name">${dayNameText}</span>
                            </label>
                            <span class="time_label">De :</span>
                            <input type="time" class="creneaux_input creneaux_time_native calendar_recurrence_ts_start" value="${startVal}" name="${startName}" step="900">
                            <span class="time_label">À :</span>
                            <input type="time" class="creneaux_input creneaux_time_native calendar_recurrence_ts_end" value="${endVal}" name="${endName}" step="900">
                            <button type="button" class="btn_remove_time_slot close"><i class="fa fa-times"></i></button>
                        `;

                        $row.html(newHtml);
                        $row.addClass('creneaux_day_row creneaux_time_slot').removeClass('ts-item');
                    }
                } else if ($dayName.length > 0) {
                    // Structure existe, juste corriger le texte si nécessaire
                    var currentText = $dayName.text().trim();
                    if ((currentText === 'undefined' || currentText === '') && dayKey !== undefined) {
                        $dayName.text(self.dayNames[String(dayKey)] || '');
                    }
                }
            });

            // Convertir les inputs text en time pour ceux qui ont été modifiés par le timepicker
            $('.creneaux_section input[data-time="undefined"], .creneaux_section input.calendar_recurrence_ts_start[type="text"], .creneaux_section input.calendar_recurrence_ts_end[type="text"]').each(function() {
                var $input = $(this);
                var val = $input.val();
                var name = $input.attr('name');
                var classes = $input.attr('class');

                // Créer un nouvel input time
                var $newInput = $('<input type="time">')
                    .attr('name', name)
                    .attr('class', classes + ' creneaux_time_native')
                    .attr('step', '900')
                    .val(val);

                $input.replaceWith($newInput);
            });
        },

        // Initialiser les classes has_slots pour les jours avec créneaux existants
        initWeeklySlots: function() {
            $('.creneaux_day_block, .ts_recurrence_bydays').each(function() {
                var $block = $(this);
                // Détecter les deux structures : nouvelle et ancienne
                var slotsCount = $block.find('.creneaux_time_slot, .ts-item').length;
                if (slotsCount > 0) {
                    $block.addClass('has_slots');
                }
            });
        },

        // Désactive le jQuery timepicker sur nos inputs HTML5 natifs
        disableJqueryTimepicker: function() {
            var $timeInputs = $('.creneaux_section input[type="time"]');

            // Supprimer tous les attributs qui pourraient déclencher le timepicker
            $timeInputs.removeAttr('data-time').removeAttr('data-timepicker');
            $timeInputs.removeClass('hasTimepicker ui-timepicker-input');

            // Si le timepicker jQuery est déjà initialisé, le détruire
            if (typeof $.fn.timepicker !== 'undefined') {
                $timeInputs.each(function() {
                    var $input = $(this);
                    try {
                        if ($input.data('timepicker')) {
                            $input.timepicker('destroy');
                        }
                    } catch(e) {}
                    try {
                        $input.timepicker('remove');
                    } catch(e) {}
                });
            }

            // Supprimer les éléments UI du timepicker s'ils existent
            $('.ui-timepicker-wrapper, .ui-timepicker-list').remove();

            // Empêcher les futurs événements timepicker sur ces éléments
            $timeInputs.off('focus.timepicker click.timepicker showTimepicker');

            // Supprimer les données jQuery associées
            $timeInputs.removeData('timepicker').removeData('ui-timepicker');
        },

        bindEvents: function() {
            var self = this;

            // Changement de type (ponctuel/récurrent)
            $('.option_calendar_radio').on('change', function() {
                var value = $(this).val();

                $('.creneaux_type_option').removeClass('active');
                $(this).closest('.creneaux_type_option').addClass('active');

                if (value === 'manual') {
                    $('.creneaux_manual_section').slideDown(200);
                    $('.creneaux_auto_section').slideUp(200);
                } else {
                    $('.creneaux_manual_section').slideUp(200);
                    $('.creneaux_auto_section').slideDown(200);
                }
            });

            // Changement de fréquence
            $('#recurrence-frequency').on('change', function() {
                var freq = $(this).val();

                // Cacher toutes les sections étape 3 alternatives
                $('#weekly-selector, #monthly-selector, #daily-selector').hide();

                // Afficher la section appropriée
                if (freq === 'weekly') {
                    $('#weekly-selector').slideDown(200);
                } else if (freq === 'monthly') {
                    $('#monthly-selector').slideDown(200);
                } else {
                    // Mode daily - afficher la section daily
                    $('#daily-selector').slideDown(200);
                }

                self.updateIntervalDesc();
            });

            // Toggle jour en mode weekly (nouveau design en cartes)
            $(document).on('change', '.day_toggle_checkbox', function() {
                var $card = $(this).closest('.weekly_day_card');
                var $body = $card.find('.day_card_body');

                if ($(this).is(':checked')) {
                    $card.addClass('is-active');
                    $body.slideDown(200);
                } else {
                    $card.removeClass('is-active');
                    $body.slideUp(200);
                }
            });

            // Clic sur l'en-tête de carte jour pour toggle
            $(document).on('click', '.day_card_header', function(e) {
                // Éviter le double déclenchement si on clique sur le toggle
                if ($(e.target).closest('.day_toggle').length) {
                    return;
                }
                var $checkbox = $(this).find('.day_toggle_checkbox');
                $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
            });

            // Changement d'intervalle
            $('#recurrence-interval').on('input', function() {
                self.updateIntervalDesc();
            });

            // Ajout de créneau manuel
            $('.btn_add_creneaux_manual').on('click', function() {
                self.addManualSlot();
            });

            // Suppression de créneau
            $(document).on('click', '.btn_remove_creneaux, .remove_calendar', function() {
                $(this).closest('.creneaux_item, .item_calendar').fadeOut(200, function() {
                    $(this).remove();
                    self.updateEmptyState();
                });
            });

            // Édition de créneau
            $(document).on('click', '.btn_edit_creneaux', function() {
                var $item = $(this).closest('.creneaux_item');
                var $inputs = $item.find('.creneaux_input');

                if ($inputs.first().prop('readonly')) {
                    $inputs.prop('readonly', false).removeClass('readonly');
                    $(this).html('<i class="fa fa-check"></i>');
                } else {
                    $inputs.prop('readonly', true).addClass('readonly');
                    $(this).html('<i class="fa fa-pencil-alt"></i>');
                }
            });

            // Sélection checkbox jour
            $('.creneaux_day_checkbox input[type="checkbox"]').on('change', function() {
                var $row = $(this).closest('.creneaux_day_row');
                if ($(this).is(':checked')) {
                    $row.addClass('active');
                } else {
                    $row.removeClass('active');
                }
            });

            // Ajout de time slot hebdomadaire
            $(document).on('click', '.btn_add_time_slot', function() {
                self.addTimeSlot($(this));
            });

            // Suppression de time slot weekly (via btn_remove_time_slot)
            $(document).on('click', '.btn_remove_time_slot', function() {
                var $slot = $(this).closest('.day_slot_item, .creneaux_time_slot, .ts-item');
                var $card = $slot.closest('.weekly_day_card');

                $slot.fadeOut(200, function() {
                    $(this).remove();

                    // Mettre à jour le compteur
                    if ($card.length) {
                        var remainingSlots = $card.find('.day_slot_item').length;
                        var $counter = $card.find('.day_slots_count');
                        if (remainingSlots > 0) {
                            $counter.text(remainingSlots + ' ' + (remainingSlots > 1 ? 'horaires' : 'horaire'));
                        } else {
                            $counter.text('');
                        }
                    }
                });
            });

            // Ajout d'horaire programmé
            $(document).on('click', '.btn_add_horaire, .add_schedules_time', function() {
                self.addSchedule();
            });

            // Suppression d'horaire programmé daily/monthly (via remove_schedules_time)
            $(document).on('click', '.btn_remove_schedule, .remove_schedules_time', function() {
                var $item = $(this).closest('.daily_slot_item, .creneaux_schedule_item, .item_schedules_time');
                $item.fadeOut(200, function() {
                    $(this).remove();

                    // Mettre à jour le compteur
                    var slotsCount = $('.daily_slots_list .daily_slot_item').length;
                    if (slotsCount > 0) {
                        $('.daily_slots_count').text(slotsCount + ' ' + (slotsCount > 1 ? 'horaires' : 'horaire'));
                    } else {
                        $('.daily_slots_count').text('');
                        $('.daily_empty_state').show();
                    }

                    // Mettre à jour le select des désactivations
                    self.updateDisableSelect();
                });
            });

            // Ajout de date désactivée
            $(document).on('click', '.btn_add_disable, .add_disable_date', function() {
                self.addDisableDate();
            });

            // Ajout de règle mensuelle (ancien bouton - gardé pour compatibilité)
            $(document).on('click', '.btn_add_monthly_rule', function() {
                self.addMonthlyRule();
            });

            // Ajout de créneau mensuel (nouveau bouton avec horaire intégré)
            $(document).on('click', '.btn_add_monthly_slot', function() {
                self.addMonthlySlot();
            });

            // Suppression de date désactivée
            $(document).on('click', '.btn_remove_disable, .remove_disable_date', function() {
                $(this).closest('.creneaux_disable_item, .item_disable_date').fadeOut(200, function() {
                    $(this).remove();
                });
            });

            // Sélectionner tout
            $('.creneaux_select_all').on('change', function() {
                var checked = $(this).is(':checked');
                $('.creneaux_item_checkbox').prop('checked', checked);
                self.updateBulkActionsBar();
            });

            // Sélection individuelle - mettre à jour la barre d'actions
            $(document).on('change', '.creneaux_item_checkbox', function() {
                self.updateBulkActionsBar();
                // Mettre à jour le "sélectionner tout" si nécessaire
                var allChecked = $('.creneaux_item_checkbox:checked').length === $('.creneaux_item_checkbox').length;
                $('.creneaux_select_all').prop('checked', allChecked && $('.creneaux_item_checkbox').length > 0);
            });

            // Suppression groupée
            $(document).on('click', '.btn_bulk_delete', function() {
                var selectedCount = $('.creneaux_item_checkbox:checked').length;
                if (selectedCount === 0) return;

                var confirmMsg = selectedCount === 1
                    ? '<?php echo esc_js( __("Êtes-vous sûr de vouloir supprimer ce créneau ?", "eventlist") ); ?>'
                    : '<?php echo esc_js( __("Êtes-vous sûr de vouloir supprimer ces", "eventlist") ); ?> ' + selectedCount + ' <?php echo esc_js( __("créneaux ?", "eventlist") ); ?>';

                if (confirm(confirmMsg)) {
                    $('.creneaux_item_checkbox:checked').each(function() {
                        $(this).closest('.creneaux_item').fadeOut(200, function() {
                            $(this).remove();
                            self.updateBulkActionsBar();
                            self.updateEmptyState();
                        });
                    });
                    // Décocher "sélectionner tout"
                    $('.creneaux_select_all').prop('checked', false);
                }
            });

            // Validation en temps réel des horaires existants
            $(document).on('change', '.slot_time_input, .calendar_start_time, .calendar_end_time, .calendar_recurrence_ts_start, .calendar_recurrence_ts_end', function() {
                var $container = $(this).closest('.day_slot_item, .daily_slot_item, .creneaux_item, .creneaux_time_slot');
                var $startInput = $container.find('.calendar_recurrence_ts_start, .calendar_start_time, .start_time').first();
                var $endInput = $container.find('.calendar_recurrence_ts_end, .calendar_end_time, .end_time').first();

                var startTime = $startInput.val();
                var endTime = $endInput.val();

                // Valider seulement si les deux valeurs sont remplies
                if (startTime && endTime) {
                    var isValid = self.validateTimeSlot(startTime, endTime, false);

                    // Mettre en surbrillance rouge si invalide
                    if (!isValid) {
                        $startInput.addClass('time_invalid');
                        $endInput.addClass('time_invalid');
                        $container.addClass('has_invalid_time');
                    } else {
                        $startInput.removeClass('time_invalid');
                        $endInput.removeClass('time_invalid');
                        $container.removeClass('has_invalid_time');
                    }
                }
            });

            // Auto-remplir date de fin quand date de début est sélectionnée
            $(document).on('change', '.creneaux_new_start_date', function() {
                var startDateVal = $(this).val();
                var $endDate = $('.creneaux_new_end_date');

                // Si date de fin est vide, copier automatiquement la date de début
                if (startDateVal && !$endDate.val()) {
                    $endDate.val(startDateVal);
                }
            });

            // Filtrer les créneaux (manuel)
            $(document).on('click', '.btn_filter_creneaux:not(.btn_filter_preview)', function() {
                self.filterSlots();
            });

            // Réinitialiser le filtre (manuel)
            $(document).on('click', '.btn_reset_filter_creneaux:not(.btn_reset_preview_filter)', function() {
                self.resetFilter();
            });

            // Générer la prévisualisation des créneaux récurrents
            $(document).on('click', '.btn_generate_preview', function() {
                self.generatePreview();
            });

            // Filtrer la prévisualisation
            $(document).on('click', '.btn_filter_preview', function() {
                self.filterPreview();
            });

            // Réinitialiser le filtre de prévisualisation
            $(document).on('click', '.btn_reset_preview_filter', function() {
                self.resetPreviewFilter();
            });

            // Sélectionner tout dans la prévisualisation
            $(document).on('change', '.creneaux_preview_select_all', function() {
                var checked = $(this).is(':checked');
                $('.creneaux_preview_list .creneaux_item_checkbox').prop('checked', checked);
            });

            // Auto-régénérer la prévisualisation quand la config change
            $(document).on('change', '.calendar_auto_start_date, .calendar_auto_end_date, #recurrence-frequency, #recurrence-interval', function() {
                // Régénérer après un délai pour éviter les appels multiples
                clearTimeout(self.previewTimeout);
                self.previewTimeout = setTimeout(function() {
                    if ($('.creneaux_auto_section').is(':visible')) {
                        self.generatePreview();
                    }
                }, 500);
            });

            // Init date/time pickers si disponibles
            this.initPickers();
        },

        addManualSlot: function() {
            var startDateInput = $('.creneaux_new_start_date').val();
            var startTime = $('.creneaux_new_start_time').val();
            var endDateInput = $('.creneaux_new_end_date').val();
            var endTime = $('.creneaux_new_end_time').val();

            // Vérifier que tous les champs sont remplis
            if (!startDateInput || !startTime || !endDateInput || !endTime) {
                this.showTimeError('<?php esc_html_e("Veuillez remplir tous les champs (date et heure)", "eventlist"); ?>');
                return;
            }

            // Convertir les dates pour comparaison
            var startDate = this.convertToISODate(startDateInput);
            var endDate = this.convertToISODate(endDateInput);

            // Vérifier que la date de fin n'est pas avant la date de début
            if (endDate < startDate) {
                this.showTimeError('<?php esc_html_e("La date de fin ne peut pas être avant la date de début", "eventlist"); ?>');
                return;
            }

            // Si même jour, valider les horaires
            if (startDate === endDate) {
                if (!this.validateTimeSlot(startTime, endTime)) {
                    return;
                }
            }

            var prefix = '<?php echo $_prefix; ?>';
            var key = this.calendarIndex++;

            // Formater la date de manière lisible (pour l'affichage)
            var formattedDate = this.formatDateReadable(startDateInput);

            // Convertir les dates au format ISO pour PHP (YYYY-MM-DD)
            var startDate = this.convertToISODate(startDateInput);
            var endDate = this.convertToISODate(endDateInput);

            var html = `
                <div class="creneaux_item item_calendar" data-key="${key}">
                    <label class="creneaux_item_select">
                        <input type="checkbox" class="creneaux_item_checkbox">
                        <span class="option_checkbox"></span>
                    </label>
                    <input type="hidden" class="calendar_id" name="${prefix}calendar[${key}][calendar_id]" value="">
                    <div class="creneaux_item_date_display">
                        <span class="date_text">${formattedDate}</span>
                        <input type="hidden" class="calendar_date" value="${startDate}" name="${prefix}calendar[${key}][date]">
                    </div>
                    <div class="creneaux_item_time">
                        <span class="time_label"><?php esc_html_e("De", "eventlist"); ?></span>
                        <input type="time" class="creneaux_input creneaux_time_native calendar_start_time" value="${startTime}" name="${prefix}calendar[${key}][start_time]" step="900">
                        <span class="time_label"><?php esc_html_e("À", "eventlist"); ?></span>
                        <input type="time" class="creneaux_input creneaux_time_native calendar_end_time" value="${endTime}" name="${prefix}calendar[${key}][end_time]" step="900">
                    </div>
                    <input type="hidden" class="calendar_end_date" value="${endDate}" name="${prefix}calendar[${key}][end_date]">
                    <input type="hidden" name="${prefix}calendar[${key}][book_before_minutes]" value="0">
                    <div class="creneaux_item_actions">
                        <button type="button" class="btn_edit_creneaux" title="<?php esc_attr_e("Valider", "eventlist"); ?>">
                            <i class="fa fa-check"></i>
                        </button>
                        <button type="button" class="btn_remove_creneaux remove_calendar" title="<?php esc_attr_e("Supprimer", "eventlist"); ?>">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            $('.creneaux_list').append(html);

            // Réinitialiser le formulaire
            $('.creneaux_new_start_date, .creneaux_new_start_time, .creneaux_new_end_date, .creneaux_new_end_time').val('');

            this.updateEmptyState();
            this.initPickers();
        },

        formatDateReadable: function(dateInput) {
            // Convertir une date (format variable) en format lisible
            var date;

            // Si c'est déjà un objet Date
            if (dateInput instanceof Date) {
                date = dateInput;
            } else if (typeof dateInput === 'string') {
                // Essayer différents formats de parsing
                if (dateInput.includes('/')) {
                    var parts = dateInput.split('/');
                    if (parts[2] && parts[2].length === 4) {
                        // Format jj/mm/aaaa ou mm/jj/aaaa
                        date = new Date(parts[2], parts[1] - 1, parts[0]);
                    } else {
                        date = new Date(dateInput);
                    }
                } else {
                    date = new Date(dateInput);
                }
            } else {
                return String(dateInput);
            }

            if (isNaN(date.getTime())) {
                return String(dateInput); // Retourner la date originale si parsing échoue
            }

            var days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            var months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

            var dayName = days[date.getDay()];
            var day = date.getDate();
            var month = months[date.getMonth()];
            var year = date.getFullYear();

            return dayName + ' ' + day + ' ' + month + ' ' + year;
        },

        // Convertir JJ/MM/AAAA vers YYYY-MM-DD (format ISO pour PHP strtotime)
        convertToISODate: function(dateStr) {
            if (!dateStr) return '';

            // Si déjà au format ISO (YYYY-MM-DD), retourner tel quel
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                return dateStr;
            }

            // Convertir JJ/MM/AAAA vers YYYY-MM-DD
            if (dateStr.includes('/')) {
                var parts = dateStr.split('/');
                if (parts.length === 3 && parts[2].length === 4) {
                    var day = parts[0].padStart(2, '0');
                    var month = parts[1].padStart(2, '0');
                    var year = parts[2];
                    return year + '-' + month + '-' + day;
                }
            }

            return dateStr; // Retourner tel quel si format non reconnu
        },

        // Validation des horaires
        validateTimeSlot: function(startTime, endTime, showAlert) {
            if (showAlert === undefined) showAlert = true;

            // Vérifier que les deux champs sont remplis
            if (!startTime || !endTime) {
                if (showAlert) this.showTimeError('<?php esc_html_e("Veuillez remplir les horaires de début et de fin", "eventlist"); ?>');
                return false;
            }

            // Convertir en minutes pour comparaison
            var startParts = startTime.split(':');
            var endParts = endTime.split(':');
            var startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            var endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);

            // L'heure de fin doit être après l'heure de début
            if (endMinutes <= startMinutes) {
                if (showAlert) this.showTimeError('<?php esc_html_e("L\'heure de fin doit être après l\'heure de début", "eventlist"); ?>');
                return false;
            }

            // Durée minimale de 15 minutes
            if (endMinutes - startMinutes < 15) {
                if (showAlert) this.showTimeError('<?php esc_html_e("La durée minimale d\'un créneau est de 15 minutes", "eventlist"); ?>');
                return false;
            }

            // Durée maximale de 24h (éviter les erreurs de saisie)
            if (endMinutes - startMinutes > 1440) {
                if (showAlert) this.showTimeError('<?php esc_html_e("La durée d\'un créneau ne peut pas dépasser 24 heures", "eventlist"); ?>');
                return false;
            }

            return true;
        },

        showTimeError: function(message) {
            // Utiliser ToastNotification si disponible, sinon alert
            if (typeof ToastNotification !== 'undefined') {
                ToastNotification.error(message);
            } else {
                alert(message);
            }
        },

        addTimeSlot: function($button) {
            // Nouveau design en cartes
            var $card = $button.closest('.weekly_day_card, .ts_recurrence_bydays');
            var dayKey = $button.data('key');
            if (dayKey === undefined || dayKey === null) {
                dayKey = $card.data('day');
            }

            // Trouver le formulaire et la liste des slots
            var $addForm = $card.find('.day_add_slot_form');
            var $slotsList = $card.find('.day_slots_list, .ts-list');
            var startTime = $addForm.find('.new_ts_start').val();
            var endTime = $addForm.find('.new_ts_end').val();

            // Validation des horaires
            if (!this.validateTimeSlot(startTime, endTime)) {
                return;
            }

            var prefix = '<?php echo $_prefix; ?>';
            var tsKey = Date.now();

            // Nouveau HTML pour le design en cartes
            var html = `
                <div class="day_slot_item" data-key="${dayKey}">
                    <input type="hidden" name="${prefix}recurrence_bydays[]" value="${dayKey}">
                    <div class="slot_time_display">
                        <input type="time" class="slot_time_input calendar_recurrence_ts_start" value="${startTime}" name="${prefix}ts_start[${dayKey}][${tsKey}]" step="900">
                        <span class="slot_time_separator">-</span>
                        <input type="time" class="slot_time_input calendar_recurrence_ts_end" value="${endTime}" name="${prefix}ts_end[${dayKey}][${tsKey}]" step="900">
                    </div>
                    <button type="button" class="slot_remove_btn btn_remove_time_slot">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `;

            $slotsList.append(html);
            $addForm.find('.new_ts_start, .new_ts_end').val('');

            // Mettre à jour le compteur
            var slotsCount = $slotsList.find('.day_slot_item').length;
            var $counter = $card.find('.day_slots_count');
            $counter.text(slotsCount + ' ' + (slotsCount > 1 ? 'horaires' : 'horaire'));

            // Désactiver le timepicker jQuery sur les nouveaux éléments
            this.disableJqueryTimepicker();
        },

        addSchedule: function() {
            var startTime = $('.calendar_recurrence_start_time').val();
            var endTime = $('.calendar_recurrence_end_time').val();

            // Validation des horaires
            if (!this.validateTimeSlot(startTime, endTime)) {
                return;
            }

            var prefix = '<?php echo $_prefix; ?>';
            var key = this.scheduleIndex++;

            var html = `
                <div class="daily_slot_item item_schedules_time" data-key="${key}">
                    <div class="slot_time_display">
                        <input type="time" class="slot_time_input start_time" name="${prefix}schedules_time[${key}][start_time]" value="${startTime}" step="900">
                        <span class="slot_time_separator">-</span>
                        <input type="time" class="slot_time_input end_time" name="${prefix}schedules_time[${key}][end_time]" value="${endTime}" step="900">
                    </div>
                    <input type="hidden" name="${prefix}schedules_time[${key}][book_before]" value="0">
                    <button type="button" class="slot_remove_btn remove_schedules_time"><i class="fa fa-times"></i></button>
                </div>
            `;

            $('.daily_slots_list').append(html);
            $('.calendar_recurrence_start_time, .calendar_recurrence_end_time').val('');

            // Cacher l'état vide
            $('.daily_empty_state').hide();

            // Mettre à jour le compteur
            var slotsCount = $('.daily_slots_list .daily_slot_item').length;
            $('.daily_slots_count').text(slotsCount + ' ' + (slotsCount > 1 ? 'horaires' : 'horaire'));

            // Mettre à jour le select des désactivations
            this.updateDisableSelect();
        },

        addMonthlyRule: function() {
            var byweekno = $('#monthly-modifier').val();
            var byday = $('#recurrence-weekday').val();
            var byweeknoText = $('#monthly-modifier option:selected').text();
            var bydayText = $('#recurrence-weekday option:selected').text();

            if (!byweekno || !byday) {
                alert('<?php esc_html_e("Veuillez sélectionner le jour et la semaine", "eventlist"); ?>');
                return;
            }

            // Afficher un message de confirmation
            var message = '<?php esc_html_e("Règle appliquée : Le", "eventlist"); ?> ' + byweeknoText + ' ' + bydayText + ' <?php esc_html_e("de chaque mois", "eventlist"); ?>';

            // Afficher la section horaire si pas encore visible
            var $horaireField = $('.creneaux_horaire_field');
            if ($horaireField.is(':hidden')) {
                $horaireField.slideDown(200);
            }

            // Créer ou mettre à jour l'affichage de la règle mensuelle
            var $monthlySection = $('.creneaux_monthly_section');
            if (!$monthlySection.find('.monthly_rule_display').length) {
                $monthlySection.append('<div class="monthly_rule_display" style="margin-top: 10px; padding: 10px 14px; background: #f0f9f0; border: 1px solid #d4edda; border-radius: 6px; color: #155724; font-size: 13px;"><i class="fa fa-check-circle" style="margin-right: 8px;"></i><span class="rule_text"></span></div>');
            }
            $monthlySection.find('.monthly_rule_display .rule_text').text(message);
        },

        // Nouvelle fonction pour ajouter un créneau mensuel avec horaire intégré
        addMonthlySlot: function() {
            var startTime = $('.monthly_start_time').val();
            var endTime = $('.monthly_end_time').val();

            // Validation des horaires
            if (!this.validateTimeSlot(startTime, endTime)) {
                return;
            }

            var prefix = '<?php echo $_prefix; ?>';
            var key = this.scheduleIndex++;

            var html = `
                <div class="daily_slot_item item_schedules_time" data-key="${key}">
                    <div class="slot_time_display">
                        <input type="time" class="slot_time_input start_time" name="${prefix}schedules_time[${key}][start_time]" value="${startTime}" step="900">
                        <span class="slot_time_separator">-</span>
                        <input type="time" class="slot_time_input end_time" name="${prefix}schedules_time[${key}][end_time]" value="${endTime}" step="900">
                    </div>
                    <input type="hidden" name="${prefix}schedules_time[${key}][book_before]" value="0">
                    <button type="button" class="slot_remove_btn remove_schedules_time"><i class="fa fa-times"></i></button>
                </div>
            `;

            // Ajouter à la liste des horaires programmés (utilise la même liste que daily)
            $('.daily_slots_list').append(html);

            // Afficher la carte des horaires programmés si cachée
            $('.daily_schedules_card').show();

            // Cacher l'état vide
            $('.daily_empty_state').hide();

            // Mettre à jour le compteur
            var slotsCount = $('.daily_slots_list .daily_slot_item').length;
            $('.daily_slots_count').text(slotsCount + ' ' + (slotsCount > 1 ? 'horaires' : 'horaire'));

            // Vider les champs
            $('.monthly_start_time, .monthly_end_time').val('');

            // Mettre à jour le select des désactivations
            this.updateDisableSelect();

            // Afficher un message de confirmation
            var byweeknoText = $('#monthly-modifier option:selected').text();
            var bydayText = $('#recurrence-weekday option:selected').text();
            var message = '<?php esc_html_e("Horaire ajouté :", "eventlist"); ?> ' + startTime + ' - ' + endTime + ' (<?php esc_html_e("le", "eventlist"); ?> ' + byweeknoText + ' ' + bydayText + ')';

            var $monthlySection = $('.creneaux_monthly_section');
            var $ruleDisplay = $monthlySection.find('.monthly_rule_display');
            $ruleDisplay.html('<i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + message).show();
        },

        addDisableDate: function() {
            var startDate = $('.new_disable_start_date').val();
            var endDate = $('.new_disable_end_date').val();
            var scheduleTime = $('.new_disable_schedule').val();

            if (!startDate || !endDate) {
                alert('<?php esc_html_e("Veuillez remplir les dates", "eventlist"); ?>');
                return;
            }

            var prefix = '<?php echo $_prefix; ?>';
            var key = this.disableIndex++;
            var format = '<?php echo $format; ?>';
            var firstDay = '<?php echo $first_day; ?>';

            var html = `
                <div class="creneaux_disable_item item_disable_date">
                    <span class="disable_info">
                        <?php esc_html_e("Du", "eventlist"); ?>
                        <input type="text" class="creneaux_input start_date" name="${prefix}disable_date[${key}][start_date]" value="${startDate}" data-format="${format}" data-firstday="${firstDay}" autocomplete="off" readonly>
                        <?php esc_html_e("au", "eventlist"); ?>
                        <input type="text" class="creneaux_input end_date" name="${prefix}disable_date[${key}][end_date]" value="${endDate}" data-format="${format}" data-firstday="${firstDay}" autocomplete="off" readonly>
                    </span>
                    <input type="hidden" name="${prefix}disable_date[${key}][schedules_time]" value="${scheduleTime}">
                    <button type="button" class="btn_remove_disable remove_disable_date"><i class="fa fa-times"></i></button>
                </div>
            `;

            $('.creneaux_disable_list').append(html);
            $('.new_disable_start_date, .new_disable_end_date').val('');
            $('.new_disable_schedule').val('');
        },

        updateIntervalDesc: function() {
            var freq = $('#recurrence-frequency').val();

            // Cacher tous les descripteurs
            $('.interval_desc').removeClass('active').hide();

            // Afficher le bon descripteur
            $('#interval-' + freq).addClass('active').show();
        },

        updateEmptyState: function() {
            var hasItems = $('.creneaux_list .creneaux_item').length > 0;

            if (hasItems) {
                $('.creneaux_empty_state').hide();
            } else {
                $('.creneaux_empty_state').show();
            }
        },

        // Mettre à jour la barre d'actions groupées
        updateBulkActionsBar: function() {
            var selectedCount = $('.creneaux_item_checkbox:checked').length;
            var $bulkBar = $('.creneaux_bulk_actions');

            if (selectedCount > 0) {
                $bulkBar.find('.count_number').text(selectedCount);
                $bulkBar.slideDown(200);
            } else {
                $bulkBar.slideUp(200);
            }
        },

        // Filtrer les créneaux par plage de dates
        filterSlots: function() {
            var self = this;
            var startDateStr = $('.creneaux_filter_start').val();
            var endDateStr = $('.creneaux_filter_end').val();

            // Si aucun filtre, tout afficher
            if (!startDateStr && !endDateStr) {
                $('.creneaux_item').show();
                $('.btn_reset_filter_creneaux').hide();
                return;
            }

            // Convertir les dates du filtre (format jj/mm/aaaa) en objets Date
            var filterStart = null;
            var filterEnd = null;

            if (startDateStr) {
                filterStart = this.parseDateFromFilter(startDateStr);
            }
            if (endDateStr) {
                filterEnd = this.parseDateFromFilter(endDateStr);
            }

            var visibleCount = 0;

            // Parcourir tous les créneaux et filtrer
            $('.creneaux_item').each(function() {
                var $item = $(this);
                var slotDateStr = $item.find('.calendar_date').val(); // Format YYYY-MM-DD

                if (!slotDateStr) {
                    $item.hide();
                    return;
                }

                var slotDate = new Date(slotDateStr);

                var show = true;

                // Vérifier la date de début du filtre
                if (filterStart && slotDate < filterStart) {
                    show = false;
                }

                // Vérifier la date de fin du filtre
                if (filterEnd && slotDate > filterEnd) {
                    show = false;
                }

                if (show) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                }
            });

            // Afficher le bouton reset
            $('.btn_reset_filter_creneaux').show();

            // Mettre à jour le checkbox "sélectionner tout" pour ne considérer que les éléments visibles
            this.updateSelectAllForVisibleItems();
        },

        // Parser une date au format jj/mm/aaaa
        parseDateFromFilter: function(dateStr) {
            if (!dateStr) return null;

            var parts = dateStr.split('/');
            if (parts.length !== 3) return null;

            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10) - 1; // Les mois commencent à 0
            var year = parseInt(parts[2], 10);

            return new Date(year, month, day);
        },

        // Réinitialiser le filtre
        resetFilter: function() {
            // Vider les champs de filtre
            $('.creneaux_filter_start').val('');
            $('.creneaux_filter_end').val('');

            // Afficher tous les créneaux
            $('.creneaux_item').show();

            // Cacher le bouton reset
            $('.btn_reset_filter_creneaux').hide();

            // Mettre à jour le checkbox "sélectionner tout"
            this.updateSelectAllForVisibleItems();
        },

        // Mettre à jour le comportement de "sélectionner tout" pour les éléments visibles
        updateSelectAllForVisibleItems: function() {
            var visibleCheckboxes = $('.creneaux_item:visible .creneaux_item_checkbox');
            var allVisibleChecked = visibleCheckboxes.length > 0 &&
                                    visibleCheckboxes.filter(':checked').length === visibleCheckboxes.length;
            $('.creneaux_select_all').prop('checked', allVisibleChecked);
        },

        updateDisableSelect: function() {
            var $select = $('.new_disable_schedule');
            $select.find('option:not(:first)').remove();

            // Utiliser les nouveaux sélecteurs pour les horaires daily/monthly
            $('.daily_slot_item, .creneaux_schedule_item').each(function() {
                var key = $(this).data('key');
                var startTime = $(this).find('.start_time').val();
                var endTime = $(this).find('.end_time').val();

                if (startTime && endTime) {
                    $select.append('<option value="' + key + '">' + startTime + ' - ' + endTime + '</option>');
                }
            });
        },

        // ========================================
        // Prévisualisation des créneaux récurrents
        // ========================================

        previewTimeout: null,
        previewData: [],

        generatePreview: function() {
            var self = this;
            var $section = $('.creneaux_preview_section');
            var $list = $('.creneaux_preview_list');
            var $btn = $('.btn_generate_preview');
            var $count = $('.preview_count');

            // Récupérer les paramètres de récurrence
            var startDateStr = $('.calendar_auto_start_date').val();
            var endDateStr = $('.calendar_auto_end_date').val();
            var frequency = $('#recurrence-frequency').val();
            var interval = parseInt($('#recurrence-interval').val()) || 1;

            // Vérifier que les dates sont remplies
            if (!startDateStr || !endDateStr) {
                $section.removeClass('has-items');
                $count.html('');
                return;
            }

            // Animation de chargement
            $btn.addClass('loading');

            // Récupérer les horaires selon la fréquence
            var timeSlots = this.getRecurringTimeSlots(frequency);

            // Si pas d'horaires configurés
            if (timeSlots.length === 0) {
                $section.removeClass('has-items');
                $list.empty();
                $count.html('<?php echo esc_js(__("Ajoutez des horaires pour voir les créneaux", "eventlist")); ?>');
                $btn.removeClass('loading');
                return;
            }

            // Convertir les dates
            var startDate = this.parseDate(startDateStr);
            var endDate = this.parseDate(endDateStr);

            if (!startDate || !endDate || startDate > endDate) {
                $section.removeClass('has-items');
                $count.html('<?php echo esc_js(__("Dates invalides", "eventlist")); ?>');
                $btn.removeClass('loading');
                return;
            }

            // Générer les dates selon la fréquence
            var generatedDates = [];

            switch (frequency) {
                case 'daily':
                    generatedDates = this.generateDailyDates(startDate, endDate, interval, timeSlots);
                    break;
                case 'weekly':
                    generatedDates = this.generateWeeklyDates(startDate, endDate, interval, timeSlots);
                    break;
                case 'monthly':
                    generatedDates = this.generateMonthlyDates(startDate, endDate, interval, timeSlots);
                    break;
            }

            // Stocker les données pour le filtrage
            this.previewData = generatedDates;

            // Afficher les créneaux
            this.renderPreviewSlots(generatedDates);

            // Mettre à jour le compteur
            var total = generatedDates.length;
            $count.html('<strong>' + total + '</strong> <?php echo esc_js(__("créneau(x) généré(s)", "eventlist")); ?>');

            $btn.removeClass('loading');
        },

        getRecurringTimeSlots: function(frequency) {
            var slots = [];

            if (frequency === 'weekly') {
                // Pour weekly, récupérer les horaires par jour
                $('.weekly_day_card.is-active').each(function() {
                    var dayKey = $(this).data('day');
                    $(this).find('.day_slot_item').each(function() {
                        var startTime = $(this).find('.calendar_recurrence_ts_start').val();
                        var endTime = $(this).find('.calendar_recurrence_ts_end').val();
                        if (startTime && endTime) {
                            slots.push({
                                day: parseInt(dayKey),
                                start: startTime,
                                end: endTime
                            });
                        }
                    });
                });
            } else {
                // Pour daily et monthly, utiliser les horaires programmés
                $('.daily_slot_item').each(function() {
                    var startTime = $(this).find('.start_time').val();
                    var endTime = $(this).find('.end_time').val();
                    if (startTime && endTime) {
                        slots.push({
                            start: startTime,
                            end: endTime
                        });
                    }
                });
            }

            return slots;
        },

        parseDate: function(dateStr) {
            if (!dateStr) return null;

            // Format JJ/MM/AAAA
            if (dateStr.includes('/')) {
                var parts = dateStr.split('/');
                if (parts.length === 3 && parts[2].length === 4) {
                    return new Date(parts[2], parts[1] - 1, parts[0]);
                }
            }

            // Format YYYY-MM-DD
            if (dateStr.includes('-')) {
                return new Date(dateStr);
            }

            return null;
        },

        generateDailyDates: function(startDate, endDate, interval, timeSlots) {
            var dates = [];
            var current = new Date(startDate);
            var maxDates = 365; // Limite de sécurité
            var count = 0;

            while (current <= endDate && count < maxDates) {
                timeSlots.forEach(function(slot) {
                    dates.push({
                        date: new Date(current),
                        startTime: slot.start,
                        endTime: slot.end
                    });
                });

                current.setDate(current.getDate() + interval);
                count++;
            }

            return dates;
        },

        generateWeeklyDates: function(startDate, endDate, interval, timeSlots) {
            var dates = [];
            var current = new Date(startDate);
            var maxDates = 365;
            var count = 0;
            var weekCount = 0;
            var lastWeek = -1;

            while (current <= endDate && count < maxDates) {
                var dayOfWeek = current.getDay(); // 0 = Dimanche

                // Vérifier si on a un slot pour ce jour
                var daySlots = timeSlots.filter(function(slot) {
                    return slot.day === dayOfWeek;
                });

                if (daySlots.length > 0) {
                    // Calculer le numéro de semaine depuis le début
                    var weekNum = Math.floor((current - startDate) / (7 * 24 * 60 * 60 * 1000));

                    // N'ajouter que si on est dans la bonne semaine selon l'intervalle
                    if (weekNum % interval === 0) {
                        daySlots.forEach(function(slot) {
                            dates.push({
                                date: new Date(current),
                                startTime: slot.start,
                                endTime: slot.end
                            });
                        });
                    }
                }

                current.setDate(current.getDate() + 1);
                count++;
            }

            return dates;
        },

        generateMonthlyDates: function(startDate, endDate, interval, timeSlots) {
            var dates = [];
            var byweekno = parseInt($('#monthly-modifier').val()) || 1;
            var byday = parseInt($('#recurrence-weekday').val()) || 0;
            var maxDates = 52;
            var count = 0;

            var current = new Date(startDate.getFullYear(), startDate.getMonth(), 1);

            while (current <= endDate && count < maxDates) {
                // Trouver le jour correspondant dans ce mois
                var targetDate = this.findMonthlyOccurrence(current.getFullYear(), current.getMonth(), byweekno, byday);

                if (targetDate && targetDate >= startDate && targetDate <= endDate) {
                    var self = this;
                    timeSlots.forEach(function(slot) {
                        dates.push({
                            date: new Date(targetDate),
                            startTime: slot.start,
                            endTime: slot.end
                        });
                    });
                }

                // Passer au mois suivant selon l'intervalle
                current.setMonth(current.getMonth() + interval);
                count++;
            }

            return dates;
        },

        findMonthlyOccurrence: function(year, month, weekno, dayOfWeek) {
            var date;

            if (weekno === -1) {
                // Dernier jour du type dans le mois
                date = new Date(year, month + 1, 0); // Dernier jour du mois

                while (date.getDay() !== dayOfWeek) {
                    date.setDate(date.getDate() - 1);
                }
            } else {
                // N-ième jour du type dans le mois
                date = new Date(year, month, 1);

                // Trouver le premier jour du type
                while (date.getDay() !== dayOfWeek) {
                    date.setDate(date.getDate() + 1);
                }

                // Avancer au N-ième
                date.setDate(date.getDate() + (weekno - 1) * 7);

                // Vérifier qu'on est toujours dans le bon mois
                if (date.getMonth() !== month) {
                    return null;
                }
            }

            return date;
        },

        renderPreviewSlots: function(dates) {
            var self = this;
            var $section = $('.creneaux_preview_section');
            var $list = $('.creneaux_preview_list');

            $list.empty();

            if (dates.length === 0) {
                $section.removeClass('has-items');
                return;
            }

            $section.addClass('has-items');

            dates.forEach(function(item, index) {
                var formattedDate = self.formatDateReadable(item.date);
                var isoDate = self.formatDateISO(item.date);

                var html = `
                    <div class="creneaux_item preview_item" data-index="${index}" data-date="${isoDate}">
                        <label class="creneaux_item_select">
                            <input type="checkbox" class="creneaux_item_checkbox">
                            <span class="option_checkbox"></span>
                        </label>
                        <div class="creneaux_item_date_display">
                            <span class="date_text">${formattedDate}</span>
                        </div>
                        <div class="creneaux_item_time">
                            <span class="time_label"><?php esc_html_e("De", "eventlist"); ?></span>
                            <input type="time" class="creneaux_input creneaux_time_native" value="${item.startTime}" readonly step="900">
                            <span class="time_label"><?php esc_html_e("À", "eventlist"); ?></span>
                            <input type="time" class="creneaux_input creneaux_time_native" value="${item.endTime}" readonly step="900">
                        </div>
                        <div class="creneaux_item_actions">
                            <button type="button" class="btn_edit_creneaux" title="<?php esc_attr_e("Modifier", "eventlist"); ?>">
                                <i class="fa fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn_remove_creneaux btn_remove_preview_item" title="<?php esc_attr_e("Supprimer", "eventlist"); ?>">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;

                $list.append(html);
            });
        },

        formatDateISO: function(date) {
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        },

        filterPreview: function() {
            var startDateStr = $('.creneaux_preview_filter_start').val();
            var endDateStr = $('.creneaux_preview_filter_end').val();

            if (!startDateStr && !endDateStr) {
                return;
            }

            var startFilter = startDateStr ? this.parseDate(startDateStr) : null;
            var endFilter = endDateStr ? this.parseDate(endDateStr) : null;

            $('.creneaux_preview_list .creneaux_item').each(function() {
                var dateStr = $(this).data('date');
                var itemDate = new Date(dateStr);

                var show = true;

                if (startFilter && itemDate < startFilter) {
                    show = false;
                }

                if (endFilter && itemDate > endFilter) {
                    show = false;
                }

                $(this).toggle(show);
            });

            // Afficher le bouton reset
            $('.btn_reset_preview_filter').show();
        },

        resetPreviewFilter: function() {
            // Vider les champs de filtre
            $('.creneaux_preview_filter_start').val('');
            $('.creneaux_preview_filter_end').val('');

            // Afficher tous les créneaux
            $('.creneaux_preview_list .creneaux_item').show();

            // Cacher le bouton reset
            $('.btn_reset_preview_filter').hide();
        },

        initPickers: function() {
            var self = this;

            // Initialiser les date pickers seulement
            if (typeof $.fn.datepicker !== 'undefined') {
                // Initialiser le date picker pour la date de début avec auto-remplissage de la date de fin
                $('.creneaux_new_start_date').each(function() {
                    if (!$(this).hasClass('hasDatepicker')) {
                        var format = $(this).attr('data-format') || 'dd/mm/yy';
                        var firstDay = parseInt($(this).attr('data-firstday')) || 1;

                        $(this).datepicker({
                            dateFormat: format,
                            firstDay: firstDay,
                            onSelect: function(dateText) {
                                var $endDate = $('.creneaux_new_end_date');
                                // Auto-remplir date de fin si vide
                                if (!$endDate.val()) {
                                    $endDate.val(dateText);
                                }
                            }
                        });
                    }
                });

                // Initialiser les autres date pickers normalement
                $('.creneaux_input[data-format]:not(.creneaux_new_start_date)').each(function() {
                    if (!$(this).hasClass('hasDatepicker')) {
                        var format = $(this).attr('data-format') || 'dd/mm/yy';
                        var firstDay = parseInt($(this).attr('data-firstday')) || 1;

                        $(this).datepicker({
                            dateFormat: format,
                            firstDay: firstDay
                        });
                    }
                });

                // Initialiser les date pickers pour les champs de filtre
                $('.creneaux_filter_input').each(function() {
                    if (!$(this).hasClass('hasDatepicker')) {
                        var format = $(this).attr('data-format') || 'dd/mm/yy';
                        var firstDay = parseInt($(this).attr('data-firstday')) || 1;

                        $(this).datepicker({
                            dateFormat: format,
                            firstDay: firstDay
                        });
                    }
                });
            }

            // Les time pickers utilisent le type="time" HTML5 natif
            // Désactiver le jQuery timepicker sur les nouveaux éléments
            self.disableJqueryTimepicker();
        }
    };

    // Initialiser au chargement
    $(document).ready(function() {
        CreneauxManager.init();
    });

})(jQuery);
</script>
