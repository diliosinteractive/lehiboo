<?php
/**
 * REST Partner Controller
 * Endpoints partenaires (scan tickets, stats)
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Partner {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // Scan ticket (QR code)
        register_rest_route($this->namespace, '/partner/scan', array(
            'methods' => 'POST',
            'callback' => array($this, 'scan_ticket'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // Validate ticket info (without scanning)
        register_rest_route($this->namespace, '/partner/tickets/(?P<code>[a-zA-Z0-9_-]+)/validate', array(
            'methods' => 'GET',
            'callback' => array($this, 'validate_ticket'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // List partner events
        register_rest_route($this->namespace, '/partner/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_partner_events'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // Get event stats
        register_rest_route($this->namespace, '/partner/events/(?P<id>\d+)/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event_stats'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // Get event attendees (scanned)
        register_rest_route($this->namespace, '/partner/events/(?P<id>\d+)/attendees', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event_attendees'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // Get partner dashboard stats
        register_rest_route($this->namespace, '/partner/dashboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));

        // Get scan history
        register_rest_route($this->namespace, '/partner/scans', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_scan_history'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate_partner'),
        ));
    }

    /**
     * Scan a ticket QR code
     */
    public function scan_ticket($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $qr_code = sanitize_text_field($params['qr_code'] ?? '');
        $event_id = absint($params['event_id'] ?? 0);

        if (empty($qr_code)) {
            return LMA_Response::error(
                'missing_qr_code',
                __('Code QR requis', 'lehiboo-mobile-api'),
                400
            );
        }

        // Find ticket by QR code
        global $wpdb;

        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT p.ID, p.post_title
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_qr_code'
             WHERE p.post_type = 'el_tickets'
             AND pm.meta_value = %s",
            $qr_code
        ));

        if (!$ticket) {
            // Log failed scan
            $this->log_scan($user_id, null, $event_id, 'invalid', $qr_code);

            return LMA_Response::error(
                'ticket_not_found',
                __('Billet introuvable', 'lehiboo-mobile-api'),
                404,
                array('scan_result' => 'invalid')
            );
        }

        $ticket_id = $ticket->ID;
        $ticket_event_id = get_post_meta($ticket_id, 'el_event_id', true);
        $ticket_status = get_post_meta($ticket_id, 'el_status', true) ?: 'valid';

        // Verify partner has access to this event
        if (!$this->can_manage_event($user_id, $ticket_event_id)) {
            $this->log_scan($user_id, $ticket_id, $event_id, 'unauthorized', $qr_code);

            return LMA_Response::error(
                'unauthorized_event',
                __('Vous n\'êtes pas autorisé à scanner les billets de cet événement', 'lehiboo-mobile-api'),
                403,
                array('scan_result' => 'unauthorized')
            );
        }

        // If event_id provided, verify ticket belongs to it
        if ($event_id && (int)$ticket_event_id !== $event_id) {
            $this->log_scan($user_id, $ticket_id, $event_id, 'wrong_event', $qr_code);

            $event = get_post($ticket_event_id);

            return LMA_Response::error(
                'wrong_event',
                sprintf(__('Ce billet est pour l\'événement "%s"', 'lehiboo-mobile-api'), $event ? $event->post_title : 'Inconnu'),
                400,
                array(
                    'scan_result' => 'wrong_event',
                    'ticket_event' => array(
                        'id' => (int)$ticket_event_id,
                        'title' => $event ? $event->post_title : null,
                    ),
                )
            );
        }

        // Check ticket status
        if ($ticket_status === 'used') {
            $used_at = get_post_meta($ticket_id, 'el_used_at', true);
            $used_by = get_post_meta($ticket_id, 'el_used_by', true);

            $this->log_scan($user_id, $ticket_id, $ticket_event_id, 'already_used', $qr_code);

            return LMA_Response::error(
                'ticket_already_used',
                __('Ce billet a déjà été utilisé', 'lehiboo-mobile-api'),
                400,
                array(
                    'scan_result' => 'already_used',
                    'used_at' => $used_at,
                    'ticket' => $this->format_ticket_info($ticket_id),
                )
            );
        }

        if ($ticket_status === 'cancelled') {
            $this->log_scan($user_id, $ticket_id, $ticket_event_id, 'cancelled', $qr_code);

            return LMA_Response::error(
                'ticket_cancelled',
                __('Ce billet a été annulé', 'lehiboo-mobile-api'),
                400,
                array('scan_result' => 'cancelled')
            );
        }

        // Check event date
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $event_date = get_post_meta($ticket_event_id, $meta_prefix . 'date_start', true);
        $event_date_end = get_post_meta($ticket_event_id, $meta_prefix . 'date_end', true);

        $now = time();
        $event_day_start = strtotime('today', $event_date);
        $event_day_end = $event_date_end ? strtotime('tomorrow', $event_date_end) : strtotime('tomorrow', $event_date);

        // Allow scanning from event day start to next day
        if ($now < $event_day_start) {
            $this->log_scan($user_id, $ticket_id, $ticket_event_id, 'too_early', $qr_code);

            return LMA_Response::error(
                'event_not_started',
                __('L\'événement n\'a pas encore commencé', 'lehiboo-mobile-api'),
                400,
                array(
                    'scan_result' => 'too_early',
                    'event_date' => date('Y-m-d', $event_date),
                )
            );
        }

        if ($now > $event_day_end) {
            $this->log_scan($user_id, $ticket_id, $ticket_event_id, 'expired', $qr_code);

            return LMA_Response::error(
                'event_ended',
                __('L\'événement est terminé', 'lehiboo-mobile-api'),
                400,
                array('scan_result' => 'expired')
            );
        }

        // Mark ticket as used
        update_post_meta($ticket_id, 'el_status', 'used');
        update_post_meta($ticket_id, 'el_used_at', current_time('mysql'));
        update_post_meta($ticket_id, 'el_used_by', $user_id);

        // Log successful scan
        $this->log_scan($user_id, $ticket_id, $ticket_event_id, 'success', $qr_code);

        // Get ticket and attendee info
        $ticket_info = $this->format_ticket_info($ticket_id);

        return LMA_Response::success(array(
            'scan_result' => 'success',
            'message' => __('Billet validé avec succès', 'lehiboo-mobile-api'),
            'ticket' => $ticket_info,
        ));
    }

    /**
     * Validate ticket without scanning
     */
    public function validate_ticket($request) {
        $user_id = get_current_user_id();
        $code = sanitize_text_field($request->get_param('code'));

        global $wpdb;

        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_qr_code'
             WHERE p.post_type = 'el_tickets'
             AND pm.meta_value = %s",
            $code
        ));

        if (!$ticket) {
            return LMA_Response::error(
                'ticket_not_found',
                __('Billet introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $ticket_id = $ticket->ID;
        $ticket_event_id = get_post_meta($ticket_id, 'el_event_id', true);

        // Verify partner has access
        if (!$this->can_manage_event($user_id, $ticket_event_id)) {
            return LMA_Response::error(
                'unauthorized',
                __('Accès non autorisé', 'lehiboo-mobile-api'),
                403
            );
        }

        $ticket_info = $this->format_ticket_info($ticket_id, true);

        return LMA_Response::success($ticket_info);
    }

    /**
     * Get partner events
     */
    public function get_partner_events($request) {
        $user_id = get_current_user_id();
        $status = sanitize_text_field($request->get_param('status') ?? 'all');
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Get events where user is author or coorganizer
        $args = array(
            'post_type' => 'event',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'author',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        // Admin sees all
        if (!current_user_can('administrator')) {
            $args['author'] = $user_id;

            // Also include events where user is coorganizer
            $args['meta_query'][] = array(
                'key' => $meta_prefix . 'coorganizers',
                'value' => '"' . $user_id . '"',
                'compare' => 'LIKE',
            );
        }

        // Filter by status
        $now = time();
        if ($status === 'upcoming') {
            $args['meta_query'][] = array(
                'key' => $meta_prefix . 'date_start',
                'value' => $now,
                'compare' => '>=',
                'type' => 'NUMERIC',
            );
        } elseif ($status === 'past') {
            $args['meta_query'][] = array(
                'key' => $meta_prefix . 'date_start',
                'value' => $now,
                'compare' => '<',
                'type' => 'NUMERIC',
            );
        } elseif ($status === 'today') {
            $today_start = strtotime('today');
            $today_end = strtotime('tomorrow');

            $args['meta_query'][] = array(
                'relation' => 'AND',
                array(
                    'key' => $meta_prefix . 'date_start',
                    'value' => $today_end,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => $meta_prefix . 'date_end',
                    'value' => $today_start,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ),
            );
        }

        $query = new WP_Query($args);
        $events = array();

        foreach ($query->posts as $event) {
            $event_date = get_post_meta($event->ID, $meta_prefix . 'date_start', true);
            $tickets_stats = $this->get_tickets_stats($event->ID);

            $events[] = array(
                'id' => $event->ID,
                'title' => $event->post_title,
                'date' => $event_date ? date('Y-m-d', $event_date) : null,
                'time' => get_post_meta($event->ID, $meta_prefix . 'time_start', true) ?: null,
                'venue' => get_post_meta($event->ID, $meta_prefix . 'venue_name', true) ?: null,
                'thumbnail' => get_the_post_thumbnail_url($event->ID, 'medium'),
                'is_today' => $event_date && date('Y-m-d', $event_date) === date('Y-m-d'),
                'is_upcoming' => $event_date && $event_date >= $now,
                'tickets' => $tickets_stats,
            );
        }

        // Sort by date
        usort($events, function($a, $b) {
            $date_a = $a['date'] ? strtotime($a['date']) : 0;
            $date_b = $b['date'] ? strtotime($b['date']) : 0;
            return $date_b - $date_a;
        });

        return LMA_Response::success(array(
            'events' => $events,
            'count' => count($events),
        ));
    }

    /**
     * Get event stats
     */
    public function get_event_stats($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('id'));

        // Verify access
        if (!$this->can_manage_event($user_id, $event_id)) {
            return LMA_Response::error(
                'unauthorized',
                __('Accès non autorisé', 'lehiboo-mobile-api'),
                403
            );
        }

        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'event') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $event_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);

        // Get detailed stats
        $tickets_stats = $this->get_tickets_stats($event_id);
        $bookings_stats = $this->get_bookings_stats($event_id);
        $revenue_stats = $this->get_revenue_stats($event_id);
        $hourly_scans = $this->get_hourly_scans($event_id);

        return LMA_Response::success(array(
            'event' => array(
                'id' => $event_id,
                'title' => $event->post_title,
                'date' => $event_date ? date('Y-m-d', $event_date) : null,
                'time' => get_post_meta($event_id, $meta_prefix . 'time_start', true) ?: null,
            ),
            'tickets' => $tickets_stats,
            'bookings' => $bookings_stats,
            'revenue' => $revenue_stats,
            'scans_by_hour' => $hourly_scans,
        ));
    }

    /**
     * Get event attendees (scanned)
     */
    public function get_event_attendees($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('id'));
        $status = sanitize_text_field($request->get_param('status') ?? 'all');

        if (!$this->can_manage_event($user_id, $event_id)) {
            return LMA_Response::error(
                'unauthorized',
                __('Accès non autorisé', 'lehiboo-mobile-api'),
                403
            );
        }

        global $wpdb;

        $where = array(
            "p.post_type = 'el_tickets'",
            $wpdb->prepare("pm_event.meta_value = %d", $event_id),
        );

        if ($status === 'scanned') {
            $where[] = "pm_status.meta_value = 'used'";
        } elseif ($status === 'pending') {
            $where[] = "(pm_status.meta_value = 'valid' OR pm_status.meta_value IS NULL)";
        }

        $where_sql = implode(' AND ', $where);

        $tickets = $wpdb->get_results(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = 'el_event_id'
             LEFT JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id AND pm_status.meta_key = 'el_status'
             WHERE {$where_sql}
             ORDER BY p.post_date DESC"
        );

        $attendees = array();
        foreach ($tickets as $ticket) {
            $attendees[] = $this->format_ticket_info($ticket->ID, false);
        }

        return LMA_Response::success(array(
            'attendees' => $attendees,
            'count' => count($attendees),
        ));
    }

    /**
     * Get partner dashboard
     */
    public function get_dashboard($request) {
        $user_id = get_current_user_id();
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Get partner events count
        $events_count = count_user_posts($user_id, 'event', true);

        // Today's events
        $today_start = strtotime('today');
        $today_end = strtotime('tomorrow');

        $today_events = new WP_Query(array(
            'post_type' => 'event',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => $meta_prefix . 'date_start',
                    'value' => array($today_start, $today_end),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC',
                ),
            ),
        ));

        // Recent scans
        global $wpdb;
        $scans_table = $wpdb->prefix . 'lma_scan_logs';

        $today_scans = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$scans_table'") === $scans_table) {
            $today_scans = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $scans_table
                 WHERE user_id = %d
                 AND result = 'success'
                 AND DATE(scanned_at) = CURDATE()",
                $user_id
            ));
        }

        // Total revenue (this month)
        $month_start = date('Y-m-01');
        $revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(CAST(pm_total.meta_value AS DECIMAL(10,2)))
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->posts} e ON p.ID = pm_event.meta_value
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = 'el_event_id'
             INNER JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = 'el_total'
             WHERE p.post_type = 'el_bookings'
             AND p.post_status = 'confirmed'
             AND e.post_author = %d
             AND p.post_date >= %s",
            $user_id,
            $month_start
        ));

        return LMA_Response::success(array(
            'summary' => array(
                'total_events' => (int) $events_count,
                'today_events' => $today_events->found_posts,
                'today_scans' => (int) $today_scans,
                'month_revenue' => (float) ($revenue ?: 0),
            ),
            'today_events' => array_map(function($event) use ($meta_prefix) {
                $tickets_stats = $this->get_tickets_stats($event->ID);
                return array(
                    'id' => $event->ID,
                    'title' => $event->post_title,
                    'time' => get_post_meta($event->ID, $meta_prefix . 'time_start', true) ?: null,
                    'venue' => get_post_meta($event->ID, $meta_prefix . 'venue_name', true) ?: null,
                    'tickets' => $tickets_stats,
                );
            }, $today_events->posts),
        ));
    }

    /**
     * Get scan history
     */
    public function get_scan_history($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('event_id') ?? 0);
        $limit = absint($request->get_param('limit') ?? 50);

        global $wpdb;
        $table = $wpdb->prefix . 'lma_scan_logs';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return LMA_Response::success(array(
                'scans' => array(),
                'count' => 0,
            ));
        }

        $where = array($wpdb->prepare("user_id = %d", $user_id));

        if ($event_id) {
            $where[] = $wpdb->prepare("event_id = %d", $event_id);
        }

        $where_sql = implode(' AND ', $where);

        $scans = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE {$where_sql}
             ORDER BY scanned_at DESC
             LIMIT %d",
            $limit
        ));

        $formatted = array();
        foreach ($scans as $scan) {
            $ticket_info = null;
            if ($scan->ticket_id) {
                $attendee_name = get_post_meta($scan->ticket_id, 'el_attendee_name', true);
                $ticket_number = get_post_meta($scan->ticket_id, 'el_ticket_number', true);
                $ticket_info = array(
                    'id' => (int) $scan->ticket_id,
                    'number' => $ticket_number ?: 'T-' . str_pad($scan->ticket_id, 8, '0', STR_PAD_LEFT),
                    'attendee' => $attendee_name ?: null,
                );
            }

            $event = $scan->event_id ? get_post($scan->event_id) : null;

            $formatted[] = array(
                'id' => (int) $scan->id,
                'result' => $scan->result,
                'result_label' => $this->get_scan_result_label($scan->result),
                'scanned_at' => $scan->scanned_at,
                'ticket' => $ticket_info,
                'event' => $event ? array(
                    'id' => $event->ID,
                    'title' => $event->post_title,
                ) : null,
            );
        }

        return LMA_Response::success(array(
            'scans' => $formatted,
            'count' => count($formatted),
        ));
    }

    /**
     * Check if user can manage an event
     */
    private function can_manage_event($user_id, $event_id) {
        // Admin can manage all
        if (user_can($user_id, 'administrator')) {
            return true;
        }

        $event = get_post($event_id);
        if (!$event) {
            return false;
        }

        // Author check
        if ((int)$event->post_author === $user_id) {
            return true;
        }

        // Coorganizer check
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $coorganizers = get_post_meta($event_id, $meta_prefix . 'coorganizers', true);

        if (is_array($coorganizers) && in_array($user_id, $coorganizers)) {
            return true;
        }

        return false;
    }

    /**
     * Log a scan attempt
     */
    private function log_scan($user_id, $ticket_id, $event_id, $result, $qr_code) {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_scan_logs';

        // Create table if not exists
        $this->maybe_create_scan_table();

        $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'ticket_id' => $ticket_id,
                'event_id' => $event_id,
                'qr_code' => $qr_code,
                'result' => $result,
                'scanned_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s')
        );
    }

    /**
     * Format ticket info
     */
    private function format_ticket_info($ticket_id, $include_event = true) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $status = get_post_meta($ticket_id, 'el_status', true) ?: 'valid';
        $ticket_number = get_post_meta($ticket_id, 'el_ticket_number', true);
        $ticket_type = get_post_meta($ticket_id, 'el_ticket_type', true);
        $attendee_name = get_post_meta($ticket_id, 'el_attendee_name', true);
        $attendee_email = get_post_meta($ticket_id, 'el_attendee_email', true);
        $used_at = get_post_meta($ticket_id, 'el_used_at', true);

        $info = array(
            'id' => $ticket_id,
            'ticket_number' => $ticket_number ?: 'T-' . str_pad($ticket_id, 8, '0', STR_PAD_LEFT),
            'type' => $ticket_type ?: 'standard',
            'status' => $status,
            'status_label' => $this->get_status_label($status),
            'attendee' => array(
                'name' => $attendee_name ?: null,
                'email' => $attendee_email ?: null,
            ),
            'used_at' => $used_at ?: null,
        );

        if ($include_event) {
            $event_id = get_post_meta($ticket_id, 'el_event_id', true);
            $event = get_post($event_id);

            if ($event) {
                $event_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);

                $info['event'] = array(
                    'id' => $event->ID,
                    'title' => $event->post_title,
                    'date' => $event_date ? date('Y-m-d', $event_date) : null,
                    'time' => get_post_meta($event_id, $meta_prefix . 'time_start', true) ?: null,
                    'venue' => get_post_meta($event_id, $meta_prefix . 'venue_name', true) ?: null,
                );
            }
        }

        return $info;
    }

    /**
     * Get tickets stats for an event
     */
    private function get_tickets_stats($event_id) {
        global $wpdb;

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_event_id'
             WHERE p.post_type = 'el_tickets' AND pm.meta_value = %d",
            $event_id
        ));

        $scanned = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = 'el_event_id'
             INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id AND pm_status.meta_key = 'el_status'
             WHERE p.post_type = 'el_tickets'
             AND pm_event.meta_value = %d
             AND pm_status.meta_value = 'used'",
            $event_id
        ));

        return array(
            'total' => (int) $total,
            'scanned' => (int) $scanned,
            'remaining' => (int) $total - (int) $scanned,
            'percentage' => $total > 0 ? round(($scanned / $total) * 100, 1) : 0,
        );
    }

    /**
     * Get bookings stats for an event
     */
    private function get_bookings_stats($event_id) {
        global $wpdb;

        $confirmed = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_event_id'
             WHERE p.post_type = 'el_bookings'
             AND p.post_status = 'confirmed'
             AND pm.meta_value = %d",
            $event_id
        ));

        $pending = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_event_id'
             WHERE p.post_type = 'el_bookings'
             AND p.post_status = 'pending'
             AND pm.meta_value = %d",
            $event_id
        ));

        $cancelled = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'el_event_id'
             WHERE p.post_type = 'el_bookings'
             AND p.post_status = 'cancelled'
             AND pm.meta_value = %d",
            $event_id
        ));

        return array(
            'confirmed' => (int) $confirmed,
            'pending' => (int) $pending,
            'cancelled' => (int) $cancelled,
            'total' => (int) $confirmed + (int) $pending,
        );
    }

    /**
     * Get revenue stats for an event
     */
    private function get_revenue_stats($event_id) {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_revenue,
                COUNT(*) as transaction_count
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_event ON p.ID = pm_event.post_id AND pm_event.meta_key = 'el_event_id'
             INNER JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = 'el_total'
             WHERE p.post_type = 'el_bookings'
             AND p.post_status = 'confirmed'
             AND pm_event.meta_value = %d",
            $event_id
        ));

        return array(
            'total' => (float) ($result->total_revenue ?: 0),
            'transactions' => (int) ($result->transaction_count ?: 0),
            'currency' => 'FCFA',
        );
    }

    /**
     * Get hourly scans for an event
     */
    private function get_hourly_scans($event_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lma_scan_logs';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return array();
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT HOUR(scanned_at) as hour, COUNT(*) as count
             FROM $table
             WHERE event_id = %d AND result = 'success'
             GROUP BY HOUR(scanned_at)
             ORDER BY hour ASC",
            $event_id
        ));

        $hourly = array();
        foreach ($results as $row) {
            $hourly[] = array(
                'hour' => sprintf('%02d:00', $row->hour),
                'scans' => (int) $row->count,
            );
        }

        return $hourly;
    }

    /**
     * Get status label
     */
    private function get_status_label($status) {
        $labels = array(
            'valid' => __('Valide', 'lehiboo-mobile-api'),
            'used' => __('Utilisé', 'lehiboo-mobile-api'),
            'cancelled' => __('Annulé', 'lehiboo-mobile-api'),
        );

        return $labels[$status] ?? $status;
    }

    /**
     * Get scan result label
     */
    private function get_scan_result_label($result) {
        $labels = array(
            'success' => __('Succès', 'lehiboo-mobile-api'),
            'invalid' => __('Code invalide', 'lehiboo-mobile-api'),
            'already_used' => __('Déjà utilisé', 'lehiboo-mobile-api'),
            'cancelled' => __('Annulé', 'lehiboo-mobile-api'),
            'wrong_event' => __('Mauvais événement', 'lehiboo-mobile-api'),
            'unauthorized' => __('Non autorisé', 'lehiboo-mobile-api'),
            'too_early' => __('Trop tôt', 'lehiboo-mobile-api'),
            'expired' => __('Expiré', 'lehiboo-mobile-api'),
        );

        return $labels[$result] ?? $result;
    }

    /**
     * Create scan logs table if not exists
     */
    private function maybe_create_scan_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'lma_scan_logs';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                ticket_id bigint(20) DEFAULT NULL,
                event_id bigint(20) DEFAULT NULL,
                qr_code varchar(255) DEFAULT NULL,
                result varchar(50) NOT NULL,
                scanned_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY event_id (event_id),
                KEY ticket_id (ticket_id),
                KEY scanned_at (scanned_at)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
