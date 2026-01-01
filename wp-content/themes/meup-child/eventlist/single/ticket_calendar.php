<?php
/**
 * Template Override: Ticket Calendar - V1 Le Hiboo
 *
 * Nouvelle UI avec:
 * - Mini calendrier pour sélection de date
 * - Dropdown pour sélection d'horaire
 * - Filtrage des billets par créneau
 *
 * @package LeHiboo
 * @version 2.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;

$id = get_the_ID();

$show_remaining_tickets = EL()->options->event->get('show_remaining_tickets', 'yes');
$list_type_ticket       = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket', true );
$list_calendar_ticket   = get_post_meta( $id, OVA_METABOX_EVENT . 'calendar', true );
$option_calendar        = get_post_meta( $id, OVA_METABOX_EVENT . 'option_calendar', true );
$check_tiket_selling    = $event->check_ticket_in_event_selling( $id );
$ticket_link            = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_link', true );
$ticket_external_link   = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_link', true );
$external_description   = get_post_meta( $id, OVA_METABOX_EVENT . 'ticket_external_description', true );
$seat_option            = get_post_meta( $id, OVA_METABOX_EVENT . 'seat_option', true );
$timezone               = get_post_meta( $id, OVA_METABOX_EVENT . 'time_zone', true );
$time_now               = current_time('Y-m-d H:i');

if ( $timezone ) {
    $tz_string = el_get_timezone_string( $timezone );
    $datetime  = new DateTime('now', new DateTimeZone( $tz_string ) );
    $time_now  = $datetime->format('Y-m-d H:i');
}

// V1 Le Hiboo - Si billetterie externe, afficher simplement le lien
if ( $ticket_link === 'ticket_external_link' && $ticket_external_link ) : ?>
    <div class="external_booking_section" id="booking_event">
        <h3 class="booking_section_title">
            <?php esc_html_e( "Réservation", "eventlist" ); ?>
        </h3>

        <?php if ( $external_description ) : ?>
        <div class="external_booking_description">
            <?php echo wp_kses_post( nl2br( $external_description ) ); ?>
        </div>
        <?php endif; ?>

        <a href="<?php echo esc_url( $ticket_external_link ); ?>"
           class="external_booking_btn"
           target="_blank"
           rel="noopener noreferrer">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            <?php esc_html_e( "Réserver sur la billetterie externe", "eventlist" ); ?>
        </a>
    </div>

<?php else :
    // V1 Le Hiboo - Préparer les données des créneaux
    $slots_by_date = array();
    $available_dates = array();

    if ( ! empty( $list_calendar_ticket ) && is_array( $list_calendar_ticket ) ) {
        foreach ( $list_calendar_ticket as $slot ) {
            $slot_date = isset( $slot['date'] ) ? $slot['date'] : '';
            if ( empty( $slot_date ) ) continue;

            $start_time  = isset( $slot['date'] ) ? el_get_time_int_by_date_and_hour( $slot['date'], $slot['start_time'] ) : '';
            $end_time    = isset( $slot['end_date'] ) ? el_get_time_int_by_date_and_hour( $slot['end_date'], $slot['end_time'] ) : '';
            $number_time = isset( $slot['book_before_minutes'] ) ? floatval( $slot['book_before_minutes'] ) * 60 : 0;

            // Vérifier si le créneau est encore disponible
            $is_available = el_validate_selling_ticket( $start_time, $end_time, $number_time, $id );

            // Compter les places disponibles
            $total_rest = 0;
            if ( $is_available && ! empty( $list_type_ticket ) ) {
                foreach ( $list_type_ticket as $ticket ) {
                    // V1 Le Hiboo - Vérifier si ce billet est disponible pour ce créneau
                    $ticket_available = true;
                    if ( function_exists( 'el_ticket_available_for_slot' ) ) {
                        $slot_id = isset( $slot['calendar_id'] ) ? $slot['calendar_id'] : '';
                        $ticket_available = el_ticket_available_for_slot( $id, $ticket['ticket_id'], $slot_id );
                    }

                    if ( $ticket_available ) {
                        if ( $seat_option === 'none' ) {
                            $rest = EL_Booking::instance()->get_number_ticket_rest( $id, $slot['calendar_id'], $ticket['ticket_id'] );
                        } else {
                            $rest = count( EL_Booking::instance()->get_list_seat_rest( $id, $slot['calendar_id'], $ticket['ticket_id'] ) );
                        }
                        $total_rest += $rest;
                    }
                }
            }

            // Grouper par date
            if ( ! isset( $slots_by_date[ $slot_date ] ) ) {
                $slots_by_date[ $slot_date ] = array();
            }

            $slots_by_date[ $slot_date ][] = array(
                'calendar_id' => $slot['calendar_id'],
                'start_time'  => isset( $slot['start_time'] ) ? $slot['start_time'] : '',
                'end_time'    => isset( $slot['end_time'] ) ? $slot['end_time'] : '',
                'available'   => $is_available,
                'places_rest' => $total_rest,
            );

            // Ajouter aux dates disponibles si au moins un créneau est disponible
            if ( $is_available && $total_rest > 0 ) {
                $available_dates[ $slot_date ] = true;
            }
        }
    }

    // Trier les dates
    ksort( $slots_by_date );
    $available_dates_list = array_keys( $available_dates );

    if ( ! empty( $slots_by_date ) ) : ?>
    <div class="lehiboo_booking_section" id="booking_event" data-event-id="<?php echo esc_attr( $id ); ?>">

        <h3 class="booking_section_title">
            <?php esc_html_e( "Réserver", "eventlist" ); ?>
        </h3>

        <!-- Étape 1: Sélection de la date -->
        <div class="booking_step step_date">
            <div class="step_label">
                <span class="step_number">1</span>
                <?php esc_html_e( "Choisissez votre date", "eventlist" ); ?>
            </div>

            <div class="mini_calendar_wrapper">
                <div class="mini_calendar"
                     id="booking_mini_calendar"
                     data-available-dates='<?php echo esc_attr( json_encode( $available_dates_list ) ); ?>'
                     data-slots='<?php echo esc_attr( json_encode( $slots_by_date ) ); ?>'>
                </div>
            </div>
        </div>

        <!-- Étape 2: Sélection de l'horaire (caché jusqu'à sélection date) -->
        <div class="booking_step step_time" style="display: none;">
            <div class="step_label">
                <span class="step_number">2</span>
                <?php esc_html_e( "Choisissez votre horaire", "eventlist" ); ?>
            </div>

            <div class="time_slots_dropdown">
                <select id="time_slot_select" class="time_slot_select">
                    <option value=""><?php esc_html_e( "Sélectionnez un horaire", "eventlist" ); ?></option>
                </select>
            </div>
        </div>

        <!-- Étape 3: Sélection des billets (caché jusqu'à sélection horaire) -->
        <div class="booking_step step_tickets" style="display: none;">
            <div class="step_label">
                <span class="step_number">3</span>
                <?php esc_html_e( "Choisissez vos billets", "eventlist" ); ?>
            </div>

            <div class="tickets_list" id="available_tickets">
                <!-- Les billets seront chargés dynamiquement -->
            </div>

            <a href="#" class="booking_submit_btn" id="proceed_to_cart" style="display: none;">
                <?php esc_html_e( "Continuer", "eventlist" ); ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

    </div>

    <!-- Template pour les billets -->
    <script type="text/template" id="ticket_item_template">
        <div class="ticket_item" data-ticket-id="{{ticket_id}}">
            <div class="ticket_info">
                <div class="ticket_name">{{name}}</div>
                <div class="ticket_price">{{price}}</div>
            </div>
            <div class="ticket_availability">{{availability}}</div>
        </div>
    </script>

    <?php endif;
endif; ?>

<style>
/* V1 Le Hiboo - Booking Section Styles */
.lehiboo_booking_section,
.external_booking_section {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.booking_section_title {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 24px 0;
}

/* External Booking */
.external_booking_description {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 10px;
}

.external_booking_btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    background: #FF6600;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.external_booking_btn:hover {
    background: #e55a00;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
}

/* Booking Steps */
.booking_step {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #eee;
}

.booking_step:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.step_label {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 16px;
}

.step_number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #FF6600;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    border-radius: 50%;
}

/* Mini Calendar */
.mini_calendar_wrapper {
    max-width: 320px;
}

.mini_calendar {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
}

.mini_calendar .cal_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.mini_calendar .cal_title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.mini_calendar .cal_nav {
    display: flex;
    gap: 8px;
}

.mini_calendar .cal_nav button {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.mini_calendar .cal_nav button:hover {
    border-color: #FF6600;
    color: #FF6600;
}

.mini_calendar .cal_weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}

.mini_calendar .cal_weekdays span {
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: #888;
    padding: 4px 0;
}

.mini_calendar .cal_days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.mini_calendar .cal_day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border-radius: 8px;
    cursor: default;
    color: #ccc;
}

.mini_calendar .cal_day.available {
    cursor: pointer;
    background: #fff;
    color: #333;
    font-weight: 500;
    border: 1px solid #e0e0e0;
    transition: all 0.2s;
}

.mini_calendar .cal_day.available:hover {
    border-color: #FF6600;
    background: #fff8f5;
}

.mini_calendar .cal_day.selected {
    background: #FF6600;
    color: #fff;
    border-color: #FF6600;
}

.mini_calendar .cal_day.other_month {
    opacity: 0.3;
}

/* Time Slots Dropdown */
.time_slots_dropdown {
    max-width: 320px;
}

.time_slot_select {
    width: 100%;
    height: 48px;
    padding: 0 16px;
    font-size: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}

.time_slot_select:focus {
    border-color: #FF6600;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
}

/* Tickets List */
.tickets_list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ticket_item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e8e8e8;
}

.ticket_info {
    flex: 1;
    min-width: 0;
}

.ticket_name {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.ticket_price {
    font-size: 14px;
    color: #FF6600;
    font-weight: 600;
    margin-top: 4px;
}

.ticket_desc {
    font-size: 12px;
    color: #888;
    margin-top: 4px;
}

.ticket_qty {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.qty_btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.qty_btn:hover {
    border-color: #FF6600;
    color: #FF6600;
}

.qty_btn:active {
    background: #f0f0f0;
}

.qty_input {
    width: 40px;
    height: 32px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    -moz-appearance: textfield;
}

.qty_input::-webkit-outer-spin-button,
.qty_input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.ticket_availability {
    font-size: 12px;
    color: #888;
    flex-shrink: 0;
    min-width: 60px;
    text-align: right;
}

/* Loading & Error States */
.tickets_loading,
.tickets_error,
.tickets_empty {
    padding: 24px;
    text-align: center;
    color: #888;
    font-size: 14px;
}

.tickets_error {
    color: #dc3545;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.tickets_loading {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Submit Button */
.booking_submit_btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px 24px;
    margin-top: 20px;
    background: #FF6600;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.booking_submit_btn:hover {
    background: #e55a00;
}

/* Responsive */
@media (max-width: 480px) {
    .mini_calendar_wrapper,
    .time_slots_dropdown {
        max-width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';

    var BookingCalendar = {
        eventId: 0,
        currentMonth: new Date(),
        availableDates: [],
        slotsByDate: {},
        selectedDate: null,
        selectedSlot: null,

        init: function() {
            var $calendar = $('#booking_mini_calendar');
            if (!$calendar.length) return;

            this.eventId = $('#booking_event').data('event-id');
            this.availableDates = $calendar.data('available-dates') || [];
            this.slotsByDate = $calendar.data('slots') || {};

            this.renderCalendar();
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Navigation du calendrier
            $(document).on('click', '.cal_nav .cal_prev', function() {
                self.currentMonth.setMonth(self.currentMonth.getMonth() - 1);
                self.renderCalendar();
            });

            $(document).on('click', '.cal_nav .cal_next', function() {
                self.currentMonth.setMonth(self.currentMonth.getMonth() + 1);
                self.renderCalendar();
            });

            // Sélection d'une date
            $(document).on('click', '.cal_day.available', function() {
                var date = $(this).data('date');
                self.selectDate(date);
            });

            // Sélection d'un horaire
            $(document).on('change', '#time_slot_select', function() {
                var slotId = $(this).val();
                if (slotId) {
                    self.selectSlot(slotId);
                }
            });
        },

        renderCalendar: function() {
            var self = this;
            var $calendar = $('#booking_mini_calendar');

            var year = this.currentMonth.getFullYear();
            var month = this.currentMonth.getMonth();

            var monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                              'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            var dayNames = ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'];

            var firstDay = new Date(year, month, 1);
            var lastDay = new Date(year, month + 1, 0);
            var startDay = (firstDay.getDay() + 6) % 7; // Lundi = 0

            var html = '<div class="cal_header">';
            html += '<button type="button" class="cal_prev"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg></button>';
            html += '<span class="cal_title">' + monthNames[month] + ' ' + year + '</span>';
            html += '<button type="button" class="cal_next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></button>';
            html += '</div>';

            // Jours de la semaine
            html += '<div class="cal_weekdays">';
            dayNames.forEach(function(day) {
                html += '<span>' + day + '</span>';
            });
            html += '</div>';

            // Jours du mois
            html += '<div class="cal_days">';

            // Jours du mois précédent
            var prevMonth = new Date(year, month, 0);
            for (var i = startDay - 1; i >= 0; i--) {
                html += '<div class="cal_day other_month">' + (prevMonth.getDate() - i) + '</div>';
            }

            // Jours du mois courant
            for (var day = 1; day <= lastDay.getDate(); day++) {
                var dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                var isAvailable = this.availableDates.indexOf(dateStr) !== -1;
                var isSelected = this.selectedDate === dateStr;

                var classes = 'cal_day';
                if (isAvailable) classes += ' available';
                if (isSelected) classes += ' selected';

                html += '<div class="' + classes + '" data-date="' + dateStr + '">' + day + '</div>';
            }

            // Jours du mois suivant
            var remainingDays = 42 - (startDay + lastDay.getDate());
            for (var i = 1; i <= remainingDays && i <= 14; i++) {
                html += '<div class="cal_day other_month">' + i + '</div>';
            }

            html += '</div>';

            $calendar.html(html);
        },

        selectDate: function(date) {
            this.selectedDate = date;
            this.selectedSlot = null;

            // Mettre à jour le calendrier
            $('.cal_day').removeClass('selected');
            $('.cal_day[data-date="' + date + '"]').addClass('selected');

            // Afficher les horaires pour cette date
            this.showTimeSlots(date);
        },

        showTimeSlots: function(date) {
            var slots = this.slotsByDate[date] || [];
            var $step = $('.step_time');
            var $select = $('#time_slot_select');

            $select.empty().append('<option value="">' + 'Sélectionnez un horaire' + '</option>');

            slots.forEach(function(slot) {
                if (slot.available && slot.places_rest > 0) {
                    var label = slot.start_time;
                    if (slot.end_time) {
                        label += ' → ' + slot.end_time;
                    }
                    label += ' (' + slot.places_rest + ' places)';

                    $select.append('<option value="' + slot.calendar_id + '">' + label + '</option>');
                }
            });

            $step.slideDown(200);
            $('.step_tickets').hide();
            $('#proceed_to_cart').hide();
        },

        selectSlot: function(slotId) {
            var self = this;
            this.selectedSlot = slotId;

            // Afficher l'étape billets avec loader
            $('.step_tickets').slideDown(200);
            $('#available_tickets').html('<div class="tickets_loading"><?php esc_html_e( "Chargement des billets...", "eventlist" ); ?></div>');
            $('#proceed_to_cart').hide();

            // Charger les billets disponibles pour ce créneau via AJAX
            $.ajax({
                url: el_ajax.url,
                type: 'POST',
                data: {
                    action: 'el_get_tickets_for_slot',
                    event_id: this.eventId,
                    slot_id: slotId
                },
                success: function(response) {
                    if (response.success && response.data.tickets) {
                        self.renderTickets(response.data.tickets);
                    } else {
                        $('#available_tickets').html('<div class="tickets_error"><?php esc_html_e( "Aucun billet disponible pour ce créneau", "eventlist" ); ?></div>');
                    }
                },
                error: function() {
                    $('#available_tickets').html('<div class="tickets_error"><?php esc_html_e( "Erreur de chargement", "eventlist" ); ?></div>');
                }
            });
        },

        renderTickets: function(tickets) {
            var self = this;
            var $container = $('#available_tickets');
            $container.empty();

            if (!tickets.length) {
                $container.html('<div class="tickets_empty"><?php esc_html_e( "Aucun billet disponible pour ce créneau", "eventlist" ); ?></div>');
                return;
            }

            tickets.forEach(function(ticket) {
                var $item = $('<div class="ticket_item" data-ticket-id="' + ticket.ticket_id + '">' +
                    '<div class="ticket_info">' +
                        '<div class="ticket_name">' + ticket.name + '</div>' +
                        '<div class="ticket_price">' + ticket.price_formatted + '</div>' +
                        (ticket.description ? '<div class="ticket_desc">' + ticket.description + '</div>' : '') +
                    '</div>' +
                    '<div class="ticket_qty">' +
                        '<button type="button" class="qty_btn qty_minus" data-ticket="' + ticket.ticket_id + '">−</button>' +
                        '<input type="number" class="qty_input" name="qty[' + ticket.ticket_id + ']" value="0" min="0" max="' + ticket.max_qty + '" data-ticket="' + ticket.ticket_id + '" readonly />' +
                        '<button type="button" class="qty_btn qty_plus" data-ticket="' + ticket.ticket_id + '">+</button>' +
                    '</div>' +
                    '<div class="ticket_availability">' + ticket.remaining + ' <?php esc_html_e( "places", "eventlist" ); ?></div>' +
                '</div>');

                $container.append($item);
            });

            // Bind quantity buttons
            self.bindQuantityButtons();
        },

        bindQuantityButtons: function() {
            var self = this;

            $(document).off('click', '.qty_minus, .qty_plus').on('click', '.qty_minus, .qty_plus', function() {
                var $btn = $(this);
                var ticketId = $btn.data('ticket');
                var $input = $('.qty_input[data-ticket="' + ticketId + '"]');
                var current = parseInt($input.val()) || 0;
                var min = parseInt($input.attr('min')) || 0;
                var max = parseInt($input.attr('max')) || 10;

                if ($btn.hasClass('qty_minus')) {
                    if (current > min) {
                        $input.val(current - 1);
                    }
                } else {
                    if (current < max) {
                        $input.val(current + 1);
                    }
                }

                self.updateProceedButton();
            });
        },

        updateProceedButton: function() {
            var total = 0;
            var ticketParams = [];

            $('.qty_input').each(function() {
                var qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    total += qty;
                    ticketParams.push($(this).data('ticket') + ':' + qty);
                }
            });

            if (total > 0) {
                var cartUrl = '<?php echo esc_url( get_cart_page() ); ?>';
                var fullUrl = cartUrl + '?ide=' + this.eventId + '&idcal=' + this.selectedSlot;
                if (ticketParams.length) {
                    fullUrl += '&tickets=' + ticketParams.join(',');
                }
                $('#proceed_to_cart').attr('href', fullUrl).show();
            } else {
                $('#proceed_to_cart').hide();
            }
        }
    };

    BookingCalendar.init();
});
</script>
