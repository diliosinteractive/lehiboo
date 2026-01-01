<?php
/**
 * REST Events Controller
 * Endpoints événements
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Events {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List events
        register_rest_route($this->namespace, '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true',
        ));

        // Single event
        register_rest_route($this->namespace, '/events/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => '__return_true',
        ));

        // Event availability (slots + tickets)
        register_rest_route($this->namespace, '/events/(?P<id>\d+)/availability', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_availability'),
            'permission_callback' => '__return_true',
        ));

        // V1 Le Hiboo - Get event slots
        register_rest_route($this->namespace, '/events/(?P<id>\d+)/slots', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_slots'),
            'permission_callback' => '__return_true',
        ));

        // V1 Le Hiboo - Get tickets for a specific slot
        register_rest_route($this->namespace, '/events/(?P<id>\d+)/slots/(?P<slot_id>[a-zA-Z0-9_-]+)/tickets', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_slot_tickets'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Get events list
     */
    public function get_events($request) {
        $filters = LMA_Validator::sanitize_event_filters($request);
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Build cache key from request parameters
        $cache_params = array(
            'page', 'per_page', 'orderby', 'order', 'search', 'category', 'thematique',
            'location', 'city', 'date_from', 'date_to', 'price_min', 'price_max',
            'free_only', 'indoor', 'outdoor', 'family_friendly', 'age_min',
            'lat', 'lng', 'radius', 'lightweight', 'include_past',
            'north_east_lat', 'north_east_lng', 'south_west_lat', 'south_west_lng'
        );
        $cache_key = LMA_Cache::key_from_request($request, $cache_params);

        // Try to get from cache (only for unauthenticated requests)
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if (!$token) {
            $cached = LMA_Cache::get('events_list', $cache_key);
            if ($cached !== false) {
                return LMA_Response::success($cached);
            }
        }

        // Build query args
        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => $filters['per_page'],
            'paged' => $filters['page'],
        );

        // Meta query
        $meta_query = array('relation' => 'AND');

        // Date filters (only future events by default, unless include_past=true)
        // Note: eventlist plugin uses 'start_date_str' for the timestamp, not 'date_start'
        $include_past = isset($filters['include_past']) && $filters['include_past'];
        $date_meta_key = $meta_prefix . 'start_date_str';

        if (!empty($filters['date_from'])) {
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => strtotime($filters['date_from']),
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        } elseif (!$include_past) {
            // Default: future events only (unless include_past is true)
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => time(),
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        }

        if (!empty($filters['date_to'])) {
            $meta_query[] = array(
                'key' => $date_meta_key,
                'value' => strtotime($filters['date_to']) + 86400,
                'compare' => '<=',
                'type' => 'NUMERIC',
            );
        }

        // Price filters
        if ($filters['free_only']) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => $meta_prefix . 'price',
                    'value' => 0,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'price',
                    'compare' => 'NOT EXISTS',
                ),
            );
        } else {
            if ($filters['price_min'] > 0) {
                $meta_query[] = array(
                    'key' => $meta_prefix . 'price',
                    'value' => $filters['price_min'],
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                );
            }
            if ($filters['price_max'] > 0) {
                $meta_query[] = array(
                    'key' => $meta_prefix . 'price',
                    'value' => $filters['price_max'],
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                );
            }
        }

        // City filter
        if (!empty($filters['city'])) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'city',
                'value' => $filters['city'],
                'compare' => 'LIKE',
            );
        }

        // Indoor/Outdoor
        if ($filters['indoor'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'indoor',
                'value' => $filters['indoor'] ? '1' : '0',
            );
        }
        if ($filters['outdoor'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'outdoor',
                'value' => $filters['outdoor'] ? '1' : '0',
            );
        }

        // Family friendly
        if ($filters['family_friendly'] !== null) {
            $meta_query[] = array(
                'key' => $meta_prefix . 'family_friendly',
                'value' => '1',
            );
        }

        // Age filters
        if ($filters['age_min'] > 0) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => $meta_prefix . 'min_age',
                    'value' => $filters['age_min'],
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'min_age',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        // Tax query
        $tax_query = array('relation' => 'AND');

        if (!empty($filters['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_cat',
                'field' => is_numeric($filters['category']) ? 'term_id' : 'slug',
                'terms' => $filters['category'],
            );
        }

        if (!empty($filters['thematique'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_thematique',
                'field' => 'slug',
                'terms' => $filters['thematique'],
            );
        }

        // Location filter (event_loc taxonomy)
        if (!empty($filters['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'event_loc',
                'field' => is_numeric($filters['location']) ? 'term_id' : 'slug',
                'terms' => $filters['location'],
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Search
        if (!empty($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        // Order
        switch ($filters['orderby']) {
            case 'price':
                $args['meta_key'] = $meta_prefix . 'price';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rating':
                $args['meta_key'] = $meta_prefix . 'rating';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'date':
            default:
                $args['meta_key'] = $meta_prefix . 'start_date_str';
                $args['orderby'] = 'meta_value_num';
                break;
        }
        $args['order'] = strtoupper($filters['order']);

        // Execute query
        $query = new WP_Query($args);

        // Get current user ID if authenticated
        $user_id = null;
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if ($token) {
            $decoded = LMA_JWT_Handler::validate_access_token($token);
            if (!is_wp_error($decoded)) {
                $user_id = $decoded->uid;
            }
        }

        // Format events
        $events = array_map(function($post) use ($filters, $user_id) {
            return LMA_Event_Formatter::format_list($post, array(
                'lat' => $filters['lat'],
                'lng' => $filters['lng'],
                'user_id' => $user_id,
            ));
        }, $query->posts);

        // Filter by bounding box if provided (for map navigation)
        $has_bounding_box = $filters['north_east_lat'] !== null
            && $filters['north_east_lng'] !== null
            && $filters['south_west_lat'] !== null
            && $filters['south_west_lng'] !== null;

        if ($has_bounding_box) {
            $events = array_filter($events, function($event) use ($filters) {
                $event_lat = $event['location']['lat'] ?? null;
                $event_lng = $event['location']['lng'] ?? null;

                // Include events without coordinates (they can't be filtered geographically)
                if ($event_lat === null || $event_lng === null) {
                    return true;
                }

                // Check if event is within bounding box
                $lat_in_bounds = $event_lat >= $filters['south_west_lat'] && $event_lat <= $filters['north_east_lat'];
                $lng_in_bounds = $event_lng >= $filters['south_west_lng'] && $event_lng <= $filters['north_east_lng'];

                return $lat_in_bounds && $lng_in_bounds;
            });
            $events = array_values($events);
        }
        // Filter by distance if coordinates provided (radius search)
        elseif ($filters['lat'] && $filters['lng'] && $filters['radius']) {
            $events = array_filter($events, function($event) use ($filters) {
                if (!isset($event['location']['distance_km'])) {
                    return true;
                }
                return $event['location']['distance_km'] <= $filters['radius'];
            });

            // Re-sort by distance if requested
            if ($filters['orderby'] === 'distance') {
                usort($events, function($a, $b) use ($filters) {
                    $dist_a = $a['location']['distance_km'] ?? PHP_INT_MAX;
                    $dist_b = $b['location']['distance_km'] ?? PHP_INT_MAX;
                    return $filters['order'] === 'asc' ? $dist_a - $dist_b : $dist_b - $dist_a;
                });
            }

            $events = array_values($events);
        }

        // Build filters applied
        $filters_applied = array_filter(array(
            'city' => $filters['city'],
            'location' => $filters['location'],
            'category' => $filters['category'],
            'thematique' => $filters['thematique'],
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'price_max' => $filters['price_max'] > 0 ? $filters['price_max'] : null,
            'free_only' => $filters['free_only'] ?: null,
            'search' => $filters['search'],
            'bounding_box' => $has_bounding_box ? array(
                'north_east' => array('lat' => $filters['north_east_lat'], 'lng' => $filters['north_east_lng']),
                'south_west' => array('lat' => $filters['south_west_lat'], 'lng' => $filters['south_west_lng']),
            ) : null,
        ));

        // Lightweight mode: return minimal data for map pins
        if ($filters['lightweight']) {
            $pins = array_map(function($event) {
                return array(
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'lat' => $event['location']['lat'] ?? null,
                    'lng' => $event['location']['lng'] ?? null,
                    'category_icon' => $event['category']['slug'] ?? null,
                    'price' => $event['price'] ?? null,
                );
            }, $events);

            // Filter out events without coordinates
            $pins = array_values(array_filter($pins, function($pin) {
                return $pin['lat'] !== null && $pin['lng'] !== null;
            }));

            $response_data = array(
                'pins' => $pins,
                'total_count' => count($pins),
                'filters_applied' => $filters_applied,
            );

            // Cache the response (only for unauthenticated requests)
            if (!$token) {
                LMA_Cache::set('events_list', $cache_key, $response_data);
            }

            return LMA_Response::success($response_data);
        }

        $response_data = array(
            'events' => $events,
            'pagination' => array(
                'current_page' => $filters['page'],
                'per_page' => $filters['per_page'],
                'total_items' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
                'has_next' => $filters['page'] < $query->max_num_pages,
                'has_prev' => $filters['page'] > 1,
            ),
            'filters_applied' => $filters_applied,
        );

        // Cache the response (only for unauthenticated requests)
        if (!$token) {
            LMA_Cache::set('events_list', $cache_key, $response_data);
        }

        return LMA_Response::success($response_data);
    }

    /**
     * Get single event
     */
    public function get_event($request) {
        $event_id = absint($request->get_param('id'));
        $cache_key = 'event_' . $event_id;

        // Get current user ID if authenticated
        $user_id = null;
        $token = LMA_JWT_Handler::get_token_from_request($request);
        if ($token) {
            $decoded = LMA_JWT_Handler::validate_access_token($token);
            if (!is_wp_error($decoded)) {
                $user_id = $decoded->uid;
            }
        }

        // Try cache for unauthenticated requests
        if (!$token) {
            $cached = LMA_Cache::get('event_detail', $cache_key);
            if ($cached !== false) {
                return LMA_Response::success($cached);
            }
        }

        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $formatted = LMA_Event_Formatter::format_detail($event, array(
            'user_id' => $user_id,
        ));

        // Cache for unauthenticated requests
        if (!$token) {
            LMA_Cache::set('event_detail', $cache_key, $formatted);
        }

        return LMA_Response::success($formatted);
    }

    /**
     * Get event availability (slots + tickets)
     * GET /events/{id}/availability
     */
    public function get_availability($request) {
        $event_id = absint($request->get_param('id'));
        $date = sanitize_text_field($request->get_param('date') ?? '');

        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Calendar type
        $option_calendar = get_post_meta($event_id, $meta_prefix . 'option_calendar', true) ?: 'manual';

        // Get slots
        $slots = $this->get_event_slots($event_id, $meta_prefix, $option_calendar, $date);

        // Get tickets
        $tickets = $this->get_event_tickets($event_id, $meta_prefix);

        // Get recurrence info
        $recurrence = $this->get_recurrence_info($event_id, $meta_prefix, $option_calendar);

        // Booking settings
        $booking_settings = array(
            'book_before_minutes' => absint(get_post_meta($event_id, $meta_prefix . 'calendar_recurrence_book_before', true)) ?: 0,
            'max_tickets_per_booking' => absint(get_post_meta($event_id, $meta_prefix . 'max_tickets_booking', true)) ?: 10,
            'requires_approval' => get_post_meta($event_id, $meta_prefix . 'requires_approval', true) === 'yes',
        );

        return LMA_Response::success(array(
            'event_id' => $event_id,
            'calendar_type' => $option_calendar,
            'slots' => $slots,
            'tickets' => $tickets,
            'recurrence' => $recurrence,
            'booking_settings' => $booking_settings,
        ));
    }

    /**
     * Get event slots (créneaux)
     */
    private function get_event_slots($event_id, $prefix, $calendar_type, $filter_date = '') {
        $slots = array();

        if ($calendar_type === 'manual') {
            // Manual calendar - specific dates
            $calendar_manual = get_post_meta($event_id, $prefix . 'calendar', true);

            if (!empty($calendar_manual) && is_array($calendar_manual)) {
                foreach ($calendar_manual as $cal) {
                    $date = isset($cal['calendar_date']) ? $cal['calendar_date'] : null;

                    // Filter by date if specified
                    if ($filter_date && $date !== $filter_date) {
                        continue;
                    }

                    // Skip past dates
                    if ($date && strtotime($date) < strtotime('today')) {
                        continue;
                    }

                    $slot = array(
                        'id' => isset($cal['calendar_id']) ? $cal['calendar_id'] : 'slot_' . md5($date),
                        'date' => $date,
                        'start_time' => isset($cal['calendar_start_time']) ? $cal['calendar_start_time'] : null,
                        'end_time' => isset($cal['calendar_end_time']) ? $cal['calendar_end_time'] : null,
                        'spots_total' => isset($cal['calendar_number']) ? absint($cal['calendar_number']) : null,
                        'spots_remaining' => null, // Will be calculated
                        'is_available' => true,
                    );

                    // Calculate spots remaining
                    if ($slot['spots_total'] !== null) {
                        $booked = $this->get_booked_spots($event_id, $date, $slot['start_time']);
                        $slot['spots_remaining'] = max(0, $slot['spots_total'] - $booked);
                        $slot['is_available'] = $slot['spots_remaining'] > 0;
                    }

                    $slots[] = $slot;
                }
            }
        } elseif ($calendar_type === 'auto') {
            // Auto/recurring calendar
            $calendar_recurrence = get_post_meta($event_id, $prefix . 'calendar_recurrence', true);
            $start_date = get_post_meta($event_id, $prefix . 'calendar_start_date', true);
            $end_date = get_post_meta($event_id, $prefix . 'calendar_end_date', true);

            if (!empty($calendar_recurrence) && is_array($calendar_recurrence)) {
                // Generate dates for next 30 days (or filter_date)
                $from_date = $filter_date ? strtotime($filter_date) : strtotime('today');
                $to_date = $filter_date ? strtotime($filter_date) : strtotime('+30 days');

                if ($start_date && strtotime($start_date) > $from_date) {
                    $from_date = strtotime($start_date);
                }
                if ($end_date && strtotime($end_date) < $to_date) {
                    $to_date = strtotime($end_date);
                }

                // Disabled dates
                $disabled_dates = get_post_meta($event_id, $prefix . 'disable_date', true) ?: array();
                $disabled_date_list = array();
                if (is_array($disabled_dates)) {
                    foreach ($disabled_dates as $dd) {
                        if (isset($dd['date'])) {
                            $disabled_date_list[] = $dd['date'];
                        }
                    }
                }

                // Generate slots
                for ($day = $from_date; $day <= $to_date; $day = strtotime('+1 day', $day)) {
                    $day_of_week = strtolower(date('l', $day));
                    $date_str = date('Y-m-d', $day);

                    // Check if disabled
                    if (in_array($date_str, $disabled_date_list)) {
                        continue;
                    }

                    foreach ($calendar_recurrence as $cal) {
                        $recur_day = isset($cal['calendar_day']) ? strtolower($cal['calendar_day']) : '';

                        if ($recur_day === $day_of_week) {
                            $slot = array(
                                'id' => 'slot_' . $date_str . '_' . ($cal['calendar_id'] ?? ''),
                                'date' => $date_str,
                                'start_time' => isset($cal['calendar_start_time']) ? $cal['calendar_start_time'] : null,
                                'end_time' => isset($cal['calendar_end_time']) ? $cal['calendar_end_time'] : null,
                                'spots_total' => isset($cal['calendar_number']) ? absint($cal['calendar_number']) : null,
                                'spots_remaining' => null,
                                'is_available' => true,
                            );

                            // Calculate spots remaining
                            if ($slot['spots_total'] !== null) {
                                $booked = $this->get_booked_spots($event_id, $date_str, $slot['start_time']);
                                $slot['spots_remaining'] = max(0, $slot['spots_total'] - $booked);
                                $slot['is_available'] = $slot['spots_remaining'] > 0;
                            }

                            $slots[] = $slot;
                        }
                    }
                }
            }
        }

        // Sort by date and time
        usort($slots, function($a, $b) {
            $date_cmp = strcmp($a['date'] ?? '', $b['date'] ?? '');
            if ($date_cmp !== 0) return $date_cmp;
            return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
        });

        return $slots;
    }

    /**
     * Get event tickets
     */
    private function get_event_tickets($event_id, $prefix) {
        $tickets_raw = get_post_meta($event_id, $prefix . 'ticket', true);

        if (empty($tickets_raw) || !is_array($tickets_raw)) {
            return array();
        }

        $tickets = array();
        foreach ($tickets_raw as $ticket) {
            $qty_total = isset($ticket['qty_ticket']) ? absint($ticket['qty_ticket']) : 0;
            $qty_sold = $this->get_tickets_sold($event_id, $ticket['ticket_id'] ?? '');
            $qty_remaining = $qty_total > 0 ? max(0, $qty_total - $qty_sold) : null;

            $tickets[] = array(
                'id' => isset($ticket['ticket_id']) ? $ticket['ticket_id'] : null,
                'name' => isset($ticket['name_ticket']) ? $ticket['name_ticket'] : '',
                'price' => isset($ticket['price_ticket']) ? floatval($ticket['price_ticket']) : 0,
                'currency' => 'EUR',
                'description' => isset($ticket['desc_ticket']) ? $ticket['desc_ticket'] : '',
                'min_per_booking' => isset($ticket['min_qty_ticket']) ? absint($ticket['min_qty_ticket']) : 1,
                'max_per_booking' => isset($ticket['max_qty_ticket']) ? absint($ticket['max_qty_ticket']) : 10,
                'quantity_total' => $qty_total > 0 ? $qty_total : null,
                'quantity_remaining' => $qty_remaining,
                'available' => $qty_remaining === null || $qty_remaining > 0,
                'person_types' => isset($ticket['person_type']) ? $this->format_person_types($ticket['person_type']) : array(),
            );
        }

        return $tickets;
    }

    /**
     * Format person types for a ticket
     */
    private function format_person_types($person_types) {
        if (empty($person_types) || !is_array($person_types)) {
            return array();
        }

        $formatted = array();
        foreach ($person_types as $pt) {
            $formatted[] = array(
                'id' => isset($pt['type_id']) ? $pt['type_id'] : null,
                'name' => isset($pt['name_type']) ? $pt['name_type'] : '',
                'price' => isset($pt['price_type']) ? floatval($pt['price_type']) : 0,
                'min' => isset($pt['min_type']) ? absint($pt['min_type']) : 0,
                'max' => isset($pt['max_type']) ? absint($pt['max_type']) : 10,
            );
        }

        return $formatted;
    }

    /**
     * Get recurrence info
     */
    private function get_recurrence_info($event_id, $prefix, $calendar_type) {
        if ($calendar_type !== 'auto') {
            return null;
        }

        $calendar_recurrence = get_post_meta($event_id, $prefix . 'calendar_recurrence', true);
        $days = array();

        if (!empty($calendar_recurrence) && is_array($calendar_recurrence)) {
            foreach ($calendar_recurrence as $cal) {
                if (isset($cal['calendar_day']) && !in_array($cal['calendar_day'], $days)) {
                    $days[] = strtolower($cal['calendar_day']);
                }
            }
        }

        return array(
            'frequency' => 'weekly',
            'days' => $days,
            'start_date' => get_post_meta($event_id, $prefix . 'calendar_start_date', true) ?: null,
            'end_date' => get_post_meta($event_id, $prefix . 'calendar_end_date', true) ?: null,
            'default_start_time' => get_post_meta($event_id, $prefix . 'calendar_recurrence_start_time', true) ?: null,
            'default_end_time' => get_post_meta($event_id, $prefix . 'calendar_recurrence_end_time', true) ?: null,
        );
    }

    /**
     * Get booked spots for a specific slot
     */
    private function get_booked_spots($event_id, $date, $time = null) {
        global $wpdb;

        // Query bookings for this event/date/time
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $query = $wpdb->prepare(
            "SELECT SUM(pm_qty.meta_value) as total
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = %s
             INNER JOIN {$wpdb->postmeta} pm_date ON p.ID = pm_date.post_id AND pm_date.meta_key = %s
             INNER JOIN {$wpdb->postmeta} pm_qty ON p.ID = pm_qty.post_id AND pm_qty.meta_key = %s
             WHERE p.post_type = 'el_bookings'
             AND p.post_status IN ('publish', 'el-booked', 'el-hold')
             AND pm_event.meta_value = %d
             AND pm_date.meta_value = %s",
            $meta_prefix . 'event_id',
            $meta_prefix . 'order_date',
            $meta_prefix . 'total_tickets',
            $event_id,
            $date
        );

        // Add time filter if specified
        if ($time) {
            $query = $wpdb->prepare(
                "SELECT SUM(pm_qty.meta_value) as total
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = %s
                 INNER JOIN {$wpdb->postmeta} pm_date ON p.ID = pm_date.post_id AND pm_date.meta_key = %s
                 INNER JOIN {$wpdb->postmeta} pm_time ON p.ID = pm_time.post_id AND pm_time.meta_key = %s
                 INNER JOIN {$wpdb->postmeta} pm_qty ON p.ID = pm_qty.post_id AND pm_qty.meta_key = %s
                 WHERE p.post_type = 'el_bookings'
                 AND p.post_status IN ('publish', 'el-booked', 'el-hold')
                 AND pm_event.meta_value = %d
                 AND pm_date.meta_value = %s
                 AND pm_time.meta_value = %s",
                $meta_prefix . 'event_id',
                $meta_prefix . 'order_date',
                $meta_prefix . 'order_time',
                $meta_prefix . 'total_tickets',
                $event_id,
                $date,
                $time
            );
        }

        $result = $wpdb->get_var($query);

        return absint($result);
    }

    /**
     * Get total tickets sold for a ticket type
     */
    private function get_tickets_sold($event_id, $ticket_id) {
        global $wpdb;

        if (empty($ticket_id)) {
            return 0;
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Query to count tickets sold
        // Note: This is a simplified version - actual implementation may vary based on how tickets are stored
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(pm_qty.meta_value) as total
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = %s
             INNER JOIN {$wpdb->postmeta} pm_qty ON p.ID = pm_qty.post_id AND pm_qty.meta_key = %s
             WHERE p.post_type = 'el_bookings'
             AND p.post_status IN ('publish', 'el-booked', 'el-hold')
             AND pm_event.meta_value = %d",
            $meta_prefix . 'event_id',
            $meta_prefix . 'total_tickets',
            $event_id
        ));

        return absint($result);
    }

    /**
     * V1 Le Hiboo - Get event slots
     * GET /events/{id}/slots
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_slots($request) {
        $event_id = absint($request->get_param('id'));
        $date = sanitize_text_field($request->get_param('date') ?? '');

        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Get calendar data
        $calendar = get_post_meta($event_id, $meta_prefix . 'calendar', true);
        $seat_option = get_post_meta($event_id, $meta_prefix . 'seat_option', true);
        $tickets = get_post_meta($event_id, $meta_prefix . 'ticket', true);

        $slots = array();
        $available_dates = array();

        if (!empty($calendar) && is_array($calendar)) {
            foreach ($calendar as $cal) {
                $slot_id = isset($cal['calendar_id']) ? $cal['calendar_id'] : '';
                $slot_date = isset($cal['date']) ? $cal['date'] : '';

                // Filter by date if specified
                if ($date && $slot_date !== $date) {
                    continue;
                }

                // Skip past dates
                if ($slot_date && strtotime($slot_date) < strtotime('today')) {
                    continue;
                }

                // Check availability (using existing function from el-core-functions.php)
                $start_time = isset($cal['date']) ? el_get_time_int_by_date_and_hour($cal['date'], $cal['start_time'] ?? '') : '';
                $end_time = isset($cal['end_date']) ? el_get_time_int_by_date_and_hour($cal['end_date'], $cal['end_time'] ?? '') : '';
                $book_before = isset($cal['book_before_minutes']) ? floatval($cal['book_before_minutes']) * 60 : 0;
                $is_available = function_exists('el_validate_selling_ticket')
                    ? el_validate_selling_ticket($start_time, $end_time, $book_before, $event_id)
                    : true;

                // Calculate total remaining tickets for this slot
                $total_remaining = 0;
                $tickets_count = 0;

                if (!empty($tickets) && is_array($tickets)) {
                    foreach ($tickets as $ticket) {
                        $ticket_id = isset($ticket['ticket_id']) ? $ticket['ticket_id'] : '';

                        // Check if ticket is available for this slot
                        $ticket_available = true;
                        if (function_exists('el_ticket_available_for_slot')) {
                            $ticket_available = el_ticket_available_for_slot($event_id, $ticket_id, $slot_id);
                        }

                        if ($ticket_available) {
                            $tickets_count++;
                            if ($seat_option === 'none') {
                                $remaining = class_exists('EL_Booking')
                                    ? EL_Booking::instance()->get_number_ticket_rest($event_id, $slot_id, $ticket_id)
                                    : 0;
                            } else {
                                $remaining = class_exists('EL_Booking')
                                    ? count(EL_Booking::instance()->get_list_seat_rest($event_id, $slot_id, $ticket_id))
                                    : 0;
                            }
                            $total_remaining += $remaining;
                        }
                    }
                }

                // Format day name in French
                $day_names = array(
                    'Monday' => 'Lundi',
                    'Tuesday' => 'Mardi',
                    'Wednesday' => 'Mercredi',
                    'Thursday' => 'Jeudi',
                    'Friday' => 'Vendredi',
                    'Saturday' => 'Samedi',
                    'Sunday' => 'Dimanche',
                );
                $day_name = isset($day_names[date('l', strtotime($slot_date))])
                    ? $day_names[date('l', strtotime($slot_date))]
                    : date('l', strtotime($slot_date));

                $slot = array(
                    'id' => $slot_id,
                    'date' => $slot_date,
                    'day_name' => $day_name,
                    'start_time' => isset($cal['start_time']) ? $cal['start_time'] : null,
                    'end_time' => isset($cal['end_time']) ? $cal['end_time'] : null,
                    'available_tickets' => $total_remaining,
                    'tickets_count' => $tickets_count,
                    'status' => ($is_available && $total_remaining > 0) ? 'available' : 'sold_out',
                );

                $slots[] = $slot;

                // Track available dates
                if ($is_available && $total_remaining > 0 && !in_array($slot_date, $available_dates)) {
                    $available_dates[] = $slot_date;
                }
            }
        }

        // Sort by date and time
        usort($slots, function($a, $b) {
            $date_cmp = strcmp($a['date'] ?? '', $b['date'] ?? '');
            if ($date_cmp !== 0) return $date_cmp;
            return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
        });

        sort($available_dates);

        return LMA_Response::success(array(
            'event_id' => $event_id,
            'slots' => $slots,
            'available_dates' => $available_dates,
        ));
    }

    /**
     * V1 Le Hiboo - Get tickets for a specific slot
     * GET /events/{id}/slots/{slot_id}/tickets
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_slot_tickets($request) {
        $event_id = absint($request->get_param('id'));
        $slot_id = sanitize_text_field($request->get_param('slot_id'));

        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Verify slot exists
        $calendar = get_post_meta($event_id, $meta_prefix . 'calendar', true);
        $slot_found = false;
        $slot_info = null;

        if (!empty($calendar) && is_array($calendar)) {
            foreach ($calendar as $cal) {
                if (isset($cal['calendar_id']) && $cal['calendar_id'] === $slot_id) {
                    $slot_found = true;
                    $slot_info = $cal;
                    break;
                }
            }
        }

        if (!$slot_found) {
            return LMA_Response::error(
                'slot_not_found',
                __('Créneau introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Get tickets
        $tickets_raw = get_post_meta($event_id, $meta_prefix . 'ticket', true);
        $seat_option = get_post_meta($event_id, $meta_prefix . 'seat_option', true);

        $available_tickets = array();

        if (!empty($tickets_raw) && is_array($tickets_raw)) {
            foreach ($tickets_raw as $ticket) {
                $ticket_id = isset($ticket['ticket_id']) ? $ticket['ticket_id'] : '';

                // Check if ticket is available for this slot (mapping ticket ↔ slot)
                $ticket_available = true;
                if (function_exists('el_ticket_available_for_slot')) {
                    $ticket_available = el_ticket_available_for_slot($event_id, $ticket_id, $slot_id);
                }

                if (!$ticket_available) {
                    continue;
                }

                // Calculate remaining
                if ($seat_option === 'none') {
                    $remaining = class_exists('EL_Booking')
                        ? EL_Booking::instance()->get_number_ticket_rest($event_id, $slot_id, $ticket_id)
                        : 0;
                } else {
                    $remaining = class_exists('EL_Booking')
                        ? count(EL_Booking::instance()->get_list_seat_rest($event_id, $slot_id, $ticket_id))
                        : 0;
                }

                // Skip sold out tickets
                if ($remaining <= 0) {
                    continue;
                }

                $price = isset($ticket['price_ticket']) ? floatval($ticket['price_ticket']) : 0;
                $min_qty = isset($ticket['min_per_ticket']) ? absint($ticket['min_per_ticket']) : 1;
                $max_qty = isset($ticket['max_per_ticket']) ? absint($ticket['max_per_ticket']) : 10;

                if ($max_qty > $remaining) {
                    $max_qty = $remaining;
                }

                $available_tickets[] = array(
                    'id' => $ticket_id,
                    'name' => isset($ticket['name_ticket']) ? $ticket['name_ticket'] : '',
                    'description' => isset($ticket['desc_ticket']) ? $ticket['desc_ticket'] : '',
                    'price' => $price,
                    'price_formatted' => function_exists('el_get_price_format')
                        ? el_get_price_format($price)
                        : number_format($price, 2, ',', ' ') . ' €',
                    'currency' => 'EUR',
                    'remaining' => $remaining,
                    'min_qty' => max(1, $min_qty),
                    'max_qty' => $max_qty,
                );
            }
        }

        return LMA_Response::success(array(
            'event_id' => $event_id,
            'slot_id' => $slot_id,
            'slot' => array(
                'date' => isset($slot_info['date']) ? $slot_info['date'] : null,
                'start_time' => isset($slot_info['start_time']) ? $slot_info['start_time'] : null,
                'end_time' => isset($slot_info['end_time']) ? $slot_info['end_time'] : null,
            ),
            'tickets' => $available_tickets,
        ));
    }
}
