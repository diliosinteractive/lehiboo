<?php
/**
 * REST Tickets Controller
 * Endpoints billets utilisateur
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Tickets {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List user tickets
        register_rest_route($this->namespace, '/me/tickets', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_tickets'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Single ticket detail
        register_rest_route($this->namespace, '/me/tickets/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_ticket'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Download ticket PDF
        register_rest_route($this->namespace, '/me/tickets/(?P<id>\d+)/download', array(
            'methods' => 'GET',
            'callback' => array($this, 'download_ticket'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Get user tickets
     */
    public function get_tickets($request) {
        $user_id = get_current_user_id();
        $status = sanitize_text_field($request->get_param('status') ?? 'all');
        $upcoming = $request->get_param('upcoming');

        global $wpdb;

        // Build query
        $where = array("t.post_type = 'el_tickets'");
        $where[] = $wpdb->prepare("pm_user.meta_value = %d", $user_id);

        // Status filter
        if ($status === 'valid') {
            $where[] = "pm_status.meta_value = 'valid'";
        } elseif ($status === 'used') {
            $where[] = "pm_status.meta_value = 'used'";
        } elseif ($status === 'cancelled') {
            $where[] = "pm_status.meta_value = 'cancelled'";
        }

        $where_sql = implode(' AND ', $where);

        $query = "
            SELECT t.ID, t.post_title, t.post_date
            FROM {$wpdb->posts} t
            INNER JOIN {$wpdb->postmeta} pm_user ON t.ID = pm_user.post_id AND pm_user.meta_key = 'el_user_id'
            LEFT JOIN {$wpdb->postmeta} pm_status ON t.ID = pm_status.post_id AND pm_status.meta_key = 'el_status'
            WHERE {$where_sql}
            ORDER BY t.post_date DESC
        ";

        $tickets_raw = $wpdb->get_results($query);

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $now = time();
        $tickets = array();

        foreach ($tickets_raw as $ticket_raw) {
            $ticket_id = $ticket_raw->ID;
            $event_id = get_post_meta($ticket_id, 'el_event_id', true);
            $event = get_post($event_id);

            if (!$event) {
                continue;
            }

            // Get event date
            $event_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);

            // Filter by upcoming if requested
            if ($upcoming === 'true' && $event_date < $now) {
                continue;
            } elseif ($upcoming === 'false' && $event_date >= $now) {
                continue;
            }

            $tickets[] = $this->format_ticket_list($ticket_id, $event);
        }

        return LMA_Response::success(array(
            'tickets' => $tickets,
            'count' => count($tickets),
        ));
    }

    /**
     * Get single ticket detail
     */
    public function get_ticket($request) {
        $ticket_id = absint($request->get_param('id'));
        $user_id = get_current_user_id();

        $ticket = get_post($ticket_id);

        if (!$ticket || $ticket->post_type !== 'el_tickets') {
            return LMA_Response::error(
                'ticket_not_found',
                __('Billet introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Verify ownership
        $ticket_user_id = get_post_meta($ticket_id, 'el_user_id', true);
        if ((int)$ticket_user_id !== $user_id) {
            return LMA_Response::error(
                'access_denied',
                __('Accès non autorisé à ce billet', 'lehiboo-mobile-api'),
                403
            );
        }

        // Get event
        $event_id = get_post_meta($ticket_id, 'el_event_id', true);
        $event = get_post($event_id);

        if (!$event) {
            return LMA_Response::error(
                'event_not_found',
                __('Événement associé introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $formatted = $this->format_ticket_detail($ticket_id, $event);

        return LMA_Response::success($formatted);
    }

    /**
     * Download ticket as PDF URL
     */
    public function download_ticket($request) {
        $ticket_id = absint($request->get_param('id'));
        $user_id = get_current_user_id();

        $ticket = get_post($ticket_id);

        if (!$ticket || $ticket->post_type !== 'el_tickets') {
            return LMA_Response::error(
                'ticket_not_found',
                __('Billet introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Verify ownership
        $ticket_user_id = get_post_meta($ticket_id, 'el_user_id', true);
        if ((int)$ticket_user_id !== $user_id) {
            return LMA_Response::error(
                'access_denied',
                __('Accès non autorisé à ce billet', 'lehiboo-mobile-api'),
                403
            );
        }

        // Check for existing PDF
        $pdf_url = get_post_meta($ticket_id, 'el_pdf_url', true);

        if (empty($pdf_url)) {
            // Generate PDF if function exists
            if (function_exists('el_generate_ticket_pdf')) {
                $pdf_url = el_generate_ticket_pdf($ticket_id);
                if ($pdf_url) {
                    update_post_meta($ticket_id, 'el_pdf_url', $pdf_url);
                }
            }
        }

        if (empty($pdf_url)) {
            return LMA_Response::error(
                'pdf_unavailable',
                __('PDF du billet non disponible', 'lehiboo-mobile-api'),
                404
            );
        }

        return LMA_Response::success(array(
            'download_url' => $pdf_url,
            'expires_at' => null,
        ));
    }

    /**
     * Format ticket for list view
     */
    private function format_ticket_list($ticket_id, $event) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $status = get_post_meta($ticket_id, 'el_status', true) ?: 'valid';
        $qr_code = get_post_meta($ticket_id, 'el_qr_code', true);
        $ticket_type = get_post_meta($ticket_id, 'el_ticket_type', true);
        $ticket_number = get_post_meta($ticket_id, 'el_ticket_number', true);

        // Event info
        $event_date = get_post_meta($event->ID, $meta_prefix . 'date_start', true);
        $event_time = get_post_meta($event->ID, $meta_prefix . 'time_start', true);
        $venue_name = get_post_meta($event->ID, $meta_prefix . 'venue_name', true);
        $city = get_post_meta($event->ID, $meta_prefix . 'city', true);

        return array(
            'id' => $ticket_id,
            'ticket_number' => $ticket_number ?: 'T-' . str_pad($ticket_id, 8, '0', STR_PAD_LEFT),
            'status' => $status,
            'status_label' => $this->get_status_label($status),
            'qr_code' => $qr_code,
            'ticket_type' => $ticket_type ?: 'standard',
            'event' => array(
                'id' => $event->ID,
                'title' => $event->post_title,
                'date' => $event_date ? date('Y-m-d', $event_date) : null,
                'time' => $event_time ?: null,
                'venue' => $venue_name ?: null,
                'city' => $city ?: null,
                'thumbnail' => get_the_post_thumbnail_url($event->ID, 'medium'),
            ),
            'is_upcoming' => $event_date ? ($event_date >= time()) : false,
        );
    }

    /**
     * Format ticket for detail view
     */
    private function format_ticket_detail($ticket_id, $event) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $basic = $this->format_ticket_list($ticket_id, $event);

        // Additional details
        $booking_id = get_post_meta($ticket_id, 'el_booking_id', true);
        $attendee_name = get_post_meta($ticket_id, 'el_attendee_name', true);
        $attendee_email = get_post_meta($ticket_id, 'el_attendee_email', true);
        $seat_info = get_post_meta($ticket_id, 'el_seat_info', true);
        $price = get_post_meta($ticket_id, 'el_price', true);
        $used_at = get_post_meta($ticket_id, 'el_used_at', true);
        $created_at = get_post_date($ticket_id);

        // Extended event info
        $event_date_end = get_post_meta($event->ID, $meta_prefix . 'date_end', true);
        $event_time_end = get_post_meta($event->ID, $meta_prefix . 'time_end', true);
        $address = get_post_meta($event->ID, $meta_prefix . 'address', true);
        $lat = get_post_meta($event->ID, $meta_prefix . 'latitude', true);
        $lng = get_post_meta($event->ID, $meta_prefix . 'longitude', true);

        return array_merge($basic, array(
            'booking_id' => $booking_id ? (int)$booking_id : null,
            'attendee' => array(
                'name' => $attendee_name ?: null,
                'email' => $attendee_email ?: null,
            ),
            'seat_info' => $seat_info ?: null,
            'price' => $price ? (float)$price : null,
            'used_at' => $used_at ?: null,
            'created_at' => $created_at,
            'event' => array_merge($basic['event'], array(
                'date_end' => $event_date_end ? date('Y-m-d', $event_date_end) : null,
                'time_end' => $event_time_end ?: null,
                'address' => $address ?: null,
                'location' => ($lat && $lng) ? array(
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                ) : null,
                'thumbnail_large' => get_the_post_thumbnail_url($event->ID, 'large'),
            )),
            'can_download_pdf' => true,
            'instructions' => $this->get_ticket_instructions($event->ID),
        ));
    }

    /**
     * Get status label
     */
    private function get_status_label($status) {
        $labels = array(
            'valid' => __('Valide', 'lehiboo-mobile-api'),
            'used' => __('Utilisé', 'lehiboo-mobile-api'),
            'cancelled' => __('Annulé', 'lehiboo-mobile-api'),
            'expired' => __('Expiré', 'lehiboo-mobile-api'),
        );

        return $labels[$status] ?? $status;
    }

    /**
     * Get ticket instructions for event
     */
    private function get_ticket_instructions($event_id) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $instructions = get_post_meta($event_id, $meta_prefix . 'ticket_instructions', true);

        if (empty($instructions)) {
            $instructions = __('Présentez ce QR code à l\'entrée de l\'événement. Assurez-vous que votre écran est suffisamment lumineux pour permettre le scan.', 'lehiboo-mobile-api');
        }

        return $instructions;
    }
}
