<?php
/**
 * REST Bookings Controller
 * Endpoints réservations
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Bookings {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // Create booking
        register_rest_route($this->namespace, '/bookings', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_booking'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Confirm booking
        register_rest_route($this->namespace, '/bookings/(?P<id>\d+)/confirm', array(
            'methods' => 'POST',
            'callback' => array($this, 'confirm_booking'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // User's bookings
        register_rest_route($this->namespace, '/me/bookings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_bookings'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Single booking
        register_rest_route($this->namespace, '/me/bookings/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_booking'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Cancel booking
        register_rest_route($this->namespace, '/me/bookings/(?P<id>\d+)/cancel', array(
            'methods' => 'POST',
            'callback' => array($this, 'cancel_booking'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Create a new booking
     */
    public function create_booking($request) {
        // Rate limit
        $rate_check = LMA_Rate_Limiter::enforce('bookings');
        if (is_wp_error($rate_check)) {
            return LMA_Response::from_error($rate_check);
        }

        $user = wp_get_current_user();
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $data = array(
            'event_id' => absint($request->get_param('event_id')),
            'tickets' => $request->get_param('tickets'),
            'buyer_info' => $request->get_param('buyer_info'),
            'coupon_code' => sanitize_text_field($request->get_param('coupon_code')),
            'notes' => sanitize_textarea_field($request->get_param('notes')),
        );

        // Validate
        $validation = LMA_Validator::validate_booking($data);
        if (is_wp_error($validation)) {
            return LMA_Response::from_error($validation);
        }

        // Get event
        $event = get_post($data['event_id']);

        // Check availability
        $total_capacity = absint(get_post_meta($event->ID, $meta_prefix . 'total_capacity', true));
        $spots_remaining = absint(get_post_meta($event->ID, $meta_prefix . 'spots_remaining', true));

        if (!$spots_remaining) {
            $spots_remaining = $total_capacity;
        }

        // Calculate total tickets
        $total_tickets = 0;
        foreach ($data['tickets'] as $ticket) {
            $total_tickets += absint($ticket['quantity']);
        }

        if ($total_tickets > $spots_remaining) {
            return LMA_Response::error(
                'insufficient_spots',
                __('Places insuffisantes pour cette réservation', 'lehiboo-mobile-api'),
                400
            );
        }

        // Calculate pricing
        $pricing = $this->calculate_pricing($event, $data['tickets'], $data['coupon_code'], $meta_prefix);

        // Create booking post
        $booking_data = array(
            'post_type' => 'el_bookings',
            'post_status' => 'publish',
            'post_title' => sprintf('Booking - %s - %s', $event->post_title, $user->display_name),
            'post_author' => $user->ID,
        );

        $booking_id = wp_insert_post($booking_data);

        if (is_wp_error($booking_id)) {
            return LMA_Response::error(
                'booking_failed',
                __('Erreur lors de la création de la réservation', 'lehiboo-mobile-api'),
                500
            );
        }

        // Generate reference
        $reference = 'LH-' . date('Y') . '-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);

        // Save booking meta
        update_post_meta($booking_id, $meta_prefix . 'event_id', $data['event_id']);
        update_post_meta($booking_id, $meta_prefix . 'user_id', $user->ID);
        update_post_meta($booking_id, $meta_prefix . 'reference', $reference);
        update_post_meta($booking_id, $meta_prefix . 'status', 'pending_payment');
        update_post_meta($booking_id, $meta_prefix . 'tickets_data', $data['tickets']);
        update_post_meta($booking_id, $meta_prefix . 'buyer_info', $data['buyer_info']);
        update_post_meta($booking_id, $meta_prefix . 'pricing', $pricing);
        update_post_meta($booking_id, $meta_prefix . 'total', $pricing['total']);
        update_post_meta($booking_id, $meta_prefix . 'currency', 'EUR');
        update_post_meta($booking_id, $meta_prefix . 'coupon_code', $data['coupon_code']);
        update_post_meta($booking_id, $meta_prefix . 'notes', $data['notes']);
        update_post_meta($booking_id, $meta_prefix . 'expires_at', time() + 900); // 15 min

        // Format response
        $tickets_summary = $this->format_tickets_summary($data['tickets'], $event, $meta_prefix);

        $response = array(
            'booking' => array(
                'id' => $booking_id,
                'reference' => $reference,
                'status' => 'pending_payment',
                'expires_at' => date('c', time() + 900),
            ),
            'event' => array(
                'id' => $event->ID,
                'title' => $event->post_title,
                'date' => date('Y-m-d', get_post_meta($event->ID, $meta_prefix . 'date_start', true)),
                'time' => get_post_meta($event->ID, $meta_prefix . 'start_time', true) . ' - ' .
                         get_post_meta($event->ID, $meta_prefix . 'end_time', true),
                'venue' => get_post_meta($event->ID, $meta_prefix . 'venue', true),
            ),
            'tickets_summary' => $tickets_summary,
            'pricing' => $pricing,
        );

        // Add payment info if not free
        if ($pricing['total'] > 0) {
            $response['payment'] = array(
                'required' => true,
                'methods_available' => array('stripe'),
                'stripe' => $this->create_stripe_payment_intent($pricing['total'], $booking_id, $reference),
            );
        } else {
            // Auto-confirm free bookings
            $this->confirm_booking_internal($booking_id, $user->ID);
            $response['booking']['status'] = 'confirmed';
            $response['payment'] = array('required' => false);
        }

        return LMA_Response::success($response, 201);
    }

    /**
     * Confirm booking after payment
     */
    public function confirm_booking($request) {
        $user = wp_get_current_user();
        $booking_id = absint($request->get_param('id'));
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Get booking
        $booking = get_post($booking_id);

        if (!$booking || $booking->post_type !== 'el_bookings') {
            return LMA_Response::error('booking_not_found', __('Réservation introuvable', 'lehiboo-mobile-api'), 404);
        }

        // Check ownership
        $booking_user_id = get_post_meta($booking_id, $meta_prefix . 'user_id', true);
        if ($booking_user_id != $user->ID) {
            return LMA_Response::error('booking_not_yours', __('Cette réservation ne vous appartient pas', 'lehiboo-mobile-api'), 403);
        }

        // Check status
        $status = get_post_meta($booking_id, $meta_prefix . 'status', true);
        if ($status === 'confirmed') {
            return LMA_Response::error('already_confirmed', __('Réservation déjà confirmée', 'lehiboo-mobile-api'), 400);
        }

        // Verify payment (simplified - in production, verify with Stripe)
        $payment_intent_id = $request->get_param('payment_intent_id');

        // Confirm booking
        $tickets = $this->confirm_booking_internal($booking_id, $user->ID);

        $reference = get_post_meta($booking_id, $meta_prefix . 'reference', true);

        return LMA_Response::success(array(
            'booking' => array(
                'id' => $booking_id,
                'reference' => $reference,
                'status' => 'confirmed',
                'confirmed_at' => date('c'),
            ),
            'tickets' => $tickets,
            'downloads' => array(
                'all_tickets_pdf' => home_url("/api/bookings/{$booking_id}/tickets.pdf"),
                'receipt_pdf' => home_url("/api/bookings/{$booking_id}/receipt.pdf"),
            ),
            'email_sent' => true,
        ));
    }

    /**
     * Internal booking confirmation
     */
    private function confirm_booking_internal($booking_id, $user_id) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Update status
        update_post_meta($booking_id, $meta_prefix . 'status', 'confirmed');
        update_post_meta($booking_id, $meta_prefix . 'confirmed_at', time());

        // Get booking data
        $event_id = get_post_meta($booking_id, $meta_prefix . 'event_id', true);
        $tickets_data = get_post_meta($booking_id, $meta_prefix . 'tickets_data', true);

        // Create tickets
        $tickets = array();

        foreach ($tickets_data as $ticket_group) {
            $attendees = isset($ticket_group['attendees']) ? $ticket_group['attendees'] : array();

            for ($i = 0; $i < $ticket_group['quantity']; $i++) {
                $attendee = isset($attendees[$i]) ? $attendees[$i] : array();

                $ticket_code = 'LH-T-' . $booking_id . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
                $qr_data = $ticket_code . '-' . hash('sha256', $ticket_code . get_option('lma_jwt_secret'));

                // Create ticket post
                $ticket_post = array(
                    'post_type' => 'el_tickets',
                    'post_status' => 'publish',
                    'post_title' => $ticket_code,
                    'post_author' => $user_id,
                );

                $ticket_id = wp_insert_post($ticket_post);

                if (!is_wp_error($ticket_id)) {
                    update_post_meta($ticket_id, $meta_prefix . 'booking_id', $booking_id);
                    update_post_meta($ticket_id, $meta_prefix . 'event_id', $event_id);
                    update_post_meta($ticket_id, $meta_prefix . 'user_id', $user_id);
                    update_post_meta($ticket_id, $meta_prefix . 'ticket_code', $ticket_code);
                    update_post_meta($ticket_id, $meta_prefix . 'qr_code', $qr_data);
                    update_post_meta($ticket_id, $meta_prefix . 'ticket_type_id', $ticket_group['ticket_type_id']);
                    update_post_meta($ticket_id, $meta_prefix . 'attendee', $attendee);
                    update_post_meta($ticket_id, $meta_prefix . 'ticket_status', ''); // empty = valid

                    $attendee_name = '';
                    if (!empty($attendee['first_name'])) {
                        $attendee_name = $attendee['first_name'] . ' ' . ($attendee['last_name'] ?? '');
                    }

                    $tickets[] = array(
                        'id' => $ticket_id,
                        'code' => $ticket_code,
                        'qr_code_data' => $qr_data,
                        'qr_code_image' => home_url("/api/qr/{$ticket_code}.png"),
                        'attendee' => array(
                            'name' => trim($attendee_name),
                            'age' => isset($attendee['age']) ? absint($attendee['age']) : null,
                        ),
                        'ticket_type' => $this->get_ticket_type_name($event_id, $ticket_group['ticket_type_id'], $meta_prefix),
                        'status' => 'valid',
                    );
                }
            }
        }

        // Update event capacity
        $spots_remaining = absint(get_post_meta($event_id, $meta_prefix . 'spots_remaining', true));
        $total_tickets = count($tickets);
        update_post_meta($event_id, $meta_prefix . 'spots_remaining', max(0, $spots_remaining - $total_tickets));

        return $tickets;
    }

    /**
     * Get user's bookings
     */
    public function get_user_bookings($request) {
        $user = wp_get_current_user();
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $status_filter = sanitize_text_field($request->get_param('status')) ?: 'all';
        $page = absint($request->get_param('page')) ?: 1;
        $per_page = min(absint($request->get_param('per_page')) ?: 20, 100);

        $args = array(
            'post_type' => 'el_bookings',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => array(
                array(
                    'key' => $meta_prefix . 'user_id',
                    'value' => $user->ID,
                ),
            ),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        // Status filter
        if ($status_filter === 'upcoming') {
            $args['meta_query'][] = array(
                'key' => $meta_prefix . 'status',
                'value' => 'confirmed',
            );
        } elseif ($status_filter === 'cancelled') {
            $args['meta_query'][] = array(
                'key' => $meta_prefix . 'status',
                'value' => 'cancelled',
            );
        }

        $query = new WP_Query($args);

        $bookings = array_map(function($booking) use ($meta_prefix) {
            return $this->format_booking_list($booking, $meta_prefix);
        }, $query->posts);

        // Count by status
        $summary = $this->get_bookings_summary($user->ID, $meta_prefix);

        return LMA_Response::success(array(
            'bookings' => $bookings,
            'pagination' => array(
                'current_page' => $page,
                'total_items' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
            ),
            'summary' => $summary,
        ));
    }

    /**
     * Get single booking
     */
    public function get_booking($request) {
        $user = wp_get_current_user();
        $booking_id = absint($request->get_param('id'));
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $booking = get_post($booking_id);

        if (!$booking || $booking->post_type !== 'el_bookings') {
            return LMA_Response::error('booking_not_found', __('Réservation introuvable', 'lehiboo-mobile-api'), 404);
        }

        $booking_user_id = get_post_meta($booking_id, $meta_prefix . 'user_id', true);
        if ($booking_user_id != $user->ID) {
            return LMA_Response::error('booking_not_yours', __('Cette réservation ne vous appartient pas', 'lehiboo-mobile-api'), 403);
        }

        return LMA_Response::success($this->format_booking_detail($booking, $meta_prefix));
    }

    /**
     * Cancel booking
     */
    public function cancel_booking($request) {
        $user = wp_get_current_user();
        $booking_id = absint($request->get_param('id'));
        $reason = sanitize_textarea_field($request->get_param('reason'));
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $booking = get_post($booking_id);

        if (!$booking || $booking->post_type !== 'el_bookings') {
            return LMA_Response::error('booking_not_found', __('Réservation introuvable', 'lehiboo-mobile-api'), 404);
        }

        $booking_user_id = get_post_meta($booking_id, $meta_prefix . 'user_id', true);
        if ($booking_user_id != $user->ID) {
            return LMA_Response::error('booking_not_yours', __('Cette réservation ne vous appartient pas', 'lehiboo-mobile-api'), 403);
        }

        $status = get_post_meta($booking_id, $meta_prefix . 'status', true);
        if ($status === 'cancelled') {
            return LMA_Response::error('already_cancelled', __('Réservation déjà annulée', 'lehiboo-mobile-api'), 400);
        }

        // Update status
        update_post_meta($booking_id, $meta_prefix . 'status', 'cancelled');
        update_post_meta($booking_id, $meta_prefix . 'cancelled_at', time());
        update_post_meta($booking_id, $meta_prefix . 'cancel_reason', $reason);

        // Cancel tickets
        $tickets = get_posts(array(
            'post_type' => 'el_tickets',
            'numberposts' => -1,
            'meta_query' => array(
                array('key' => $meta_prefix . 'booking_id', 'value' => $booking_id),
            ),
        ));

        foreach ($tickets as $ticket) {
            update_post_meta($ticket->ID, $meta_prefix . 'ticket_status', 'cancelled');
        }

        // Restore event capacity
        $event_id = get_post_meta($booking_id, $meta_prefix . 'event_id', true);
        $spots_remaining = absint(get_post_meta($event_id, $meta_prefix . 'spots_remaining', true));
        update_post_meta($event_id, $meta_prefix . 'spots_remaining', $spots_remaining + count($tickets));

        $reference = get_post_meta($booking_id, $meta_prefix . 'reference', true);
        $total = floatval(get_post_meta($booking_id, $meta_prefix . 'total', true));

        return LMA_Response::success(array(
            'booking' => array(
                'id' => $booking_id,
                'reference' => $reference,
                'status' => 'cancelled',
                'cancelled_at' => date('c'),
            ),
            'refund' => array(
                'eligible' => $total > 0,
                'amount' => $total,
                'currency' => 'EUR',
                'status' => 'processing',
                'estimated_date' => date('Y-m-d', strtotime('+3 days')),
            ),
        ));
    }

    // Helper methods

    private function calculate_pricing($event, $tickets, $coupon_code, $meta_prefix) {
        $subtotal = 0;
        $ticket_types = get_post_meta($event->ID, $meta_prefix . 'ticket_types', true);

        foreach ($tickets as $ticket) {
            $type_id = $ticket['ticket_type_id'];
            $quantity = absint($ticket['quantity']);

            $price = 0;
            if (!empty($ticket_types) && is_array($ticket_types)) {
                foreach ($ticket_types as $type) {
                    if (isset($type['id']) && $type['id'] == $type_id) {
                        $price = floatval($type['price']);
                        break;
                    }
                }
            }

            if (!$price) {
                $price = floatval(get_post_meta($event->ID, $meta_prefix . 'price', true));
            }

            $subtotal += $price * $quantity;
        }

        $discount = 0;
        $discount_info = null;

        // Apply coupon if valid (simplified)
        if (!empty($coupon_code)) {
            // Here you would validate the coupon against your coupon system
            // For now, we'll just apply a flat 10% for demo purposes if code starts with "PROMO"
            if (stripos($coupon_code, 'PROMO') === 0) {
                $discount = $subtotal * 0.10;
                $discount_info = array(
                    'code' => $coupon_code,
                    'amount' => $discount,
                    'type' => 'percentage',
                    'value' => 10,
                );
            }
        }

        return array(
            'subtotal' => round($subtotal, 2),
            'discount' => $discount_info,
            'fees' => 0,
            'total' => round($subtotal - $discount, 2),
            'currency' => 'EUR',
        );
    }

    private function format_tickets_summary($tickets, $event, $meta_prefix) {
        $ticket_types = get_post_meta($event->ID, $meta_prefix . 'ticket_types', true);
        $summary = array();

        foreach ($tickets as $ticket) {
            $type_id = $ticket['ticket_type_id'];
            $quantity = absint($ticket['quantity']);

            $type_name = 'Standard';
            $price = floatval(get_post_meta($event->ID, $meta_prefix . 'price', true));

            if (!empty($ticket_types) && is_array($ticket_types)) {
                foreach ($ticket_types as $type) {
                    if (isset($type['id']) && $type['id'] == $type_id) {
                        $type_name = $type['name'];
                        $price = floatval($type['price']);
                        break;
                    }
                }
            }

            $summary[] = array(
                'type' => $type_name,
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $price * $quantity,
            );
        }

        return $summary;
    }

    private function get_ticket_type_name($event_id, $type_id, $meta_prefix) {
        $ticket_types = get_post_meta($event_id, $meta_prefix . 'ticket_types', true);

        if (!empty($ticket_types) && is_array($ticket_types)) {
            foreach ($ticket_types as $type) {
                if (isset($type['id']) && $type['id'] == $type_id) {
                    return $type['name'];
                }
            }
        }

        return 'Standard';
    }

    private function create_stripe_payment_intent($amount, $booking_id, $reference) {
        // This would integrate with Stripe API
        // For now, return placeholder data
        return array(
            'payment_intent_id' => 'pi_' . md5($booking_id . time()),
            'client_secret' => 'pi_' . md5($booking_id . time()) . '_secret_' . substr(md5(uniqid()), 0, 8),
            'publishable_key' => get_option('el_stripe_publishable_key', 'pk_test_xxx'),
        );
    }

    private function format_booking_list($booking, $meta_prefix) {
        $event_id = get_post_meta($booking->ID, $meta_prefix . 'event_id', true);
        $event = get_post($event_id);

        $start_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);
        $is_upcoming = $start_date && (is_numeric($start_date) ? $start_date : strtotime($start_date)) > time();

        return array(
            'id' => $booking->ID,
            'reference' => get_post_meta($booking->ID, $meta_prefix . 'reference', true),
            'status' => get_post_meta($booking->ID, $meta_prefix . 'status', true),
            'event' => array(
                'id' => $event_id,
                'title' => $event ? $event->post_title : '',
                'thumbnail' => get_the_post_thumbnail_url($event_id, 'thumbnail'),
                'date' => $start_date ? date('Y-m-d', is_numeric($start_date) ? $start_date : strtotime($start_date)) : '',
                'time' => get_post_meta($event_id, $meta_prefix . 'start_time', true) . ' - ' .
                         get_post_meta($event_id, $meta_prefix . 'end_time', true),
                'venue' => get_post_meta($event_id, $meta_prefix . 'venue', true),
            ),
            'tickets_count' => $this->count_booking_tickets($booking->ID, $meta_prefix),
            'total_paid' => floatval(get_post_meta($booking->ID, $meta_prefix . 'total', true)),
            'currency' => 'EUR',
            'booked_at' => $booking->post_date_gmt,
            'is_upcoming' => $is_upcoming,
            'can_cancel' => $is_upcoming && get_post_meta($booking->ID, $meta_prefix . 'status', true) === 'confirmed',
        );
    }

    private function format_booking_detail($booking, $meta_prefix) {
        $data = $this->format_booking_list($booking, $meta_prefix);

        // Add full event details
        $event_id = get_post_meta($booking->ID, $meta_prefix . 'event_id', true);
        $event = get_post($event_id);

        $data['event']['description'] = $event ? wp_trim_words($event->post_content, 30) : '';
        $data['event']['featured_image'] = get_the_post_thumbnail_url($event_id, 'large');
        $data['event']['venue'] = array(
            'name' => get_post_meta($event_id, $meta_prefix . 'venue', true),
            'address' => get_post_meta($event_id, $meta_prefix . 'address', true),
            'lat' => floatval(get_post_meta($event_id, $meta_prefix . 'lat', true)),
            'lng' => floatval(get_post_meta($event_id, $meta_prefix . 'lng', true)),
        );

        // Get tickets
        $tickets = get_posts(array(
            'post_type' => 'el_tickets',
            'numberposts' => -1,
            'meta_query' => array(
                array('key' => $meta_prefix . 'booking_id', 'value' => $booking->ID),
            ),
        ));

        $data['tickets'] = array_map(function($ticket) use ($meta_prefix, $event_id) {
            $attendee = get_post_meta($ticket->ID, $meta_prefix . 'attendee', true);
            $ticket_code = get_post_meta($ticket->ID, $meta_prefix . 'ticket_code', true);

            return array(
                'id' => $ticket->ID,
                'code' => $ticket_code,
                'qr_code_data' => get_post_meta($ticket->ID, $meta_prefix . 'qr_code', true),
                'qr_code_image' => home_url("/api/qr/{$ticket_code}.png"),
                'attendee' => array(
                    'name' => is_array($attendee) ? trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) : '',
                    'age' => is_array($attendee) && isset($attendee['age']) ? absint($attendee['age']) : null,
                ),
                'ticket_type' => $this->get_ticket_type_name($event_id, get_post_meta($ticket->ID, $meta_prefix . 'ticket_type_id', true), $meta_prefix),
                'status' => get_post_meta($ticket->ID, $meta_prefix . 'ticket_status', true) ?: 'valid',
            );
        }, $tickets);

        // Add buyer info
        $data['buyer'] = get_post_meta($booking->ID, $meta_prefix . 'buyer_info', true);

        // Add pricing
        $data['pricing'] = get_post_meta($booking->ID, $meta_prefix . 'pricing', true);

        // Add notes
        $data['notes'] = get_post_meta($booking->ID, $meta_prefix . 'notes', true);

        // Add downloads
        $data['downloads'] = array(
            'tickets_pdf' => home_url("/api/bookings/{$booking->ID}/tickets.pdf"),
            'receipt_pdf' => home_url("/api/bookings/{$booking->ID}/receipt.pdf"),
        );

        return $data;
    }

    private function count_booking_tickets($booking_id, $meta_prefix) {
        return count(get_posts(array(
            'post_type' => 'el_tickets',
            'numberposts' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array('key' => $meta_prefix . 'booking_id', 'value' => $booking_id),
            ),
        )));
    }

    private function get_bookings_summary($user_id, $meta_prefix) {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT pm.meta_value as status, COUNT(*) as count
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
            JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = %s AND pm2.meta_value = %d
            WHERE p.post_type = 'el_bookings' AND p.post_status = 'publish'
            GROUP BY pm.meta_value
        ", $meta_prefix . 'status', $meta_prefix . 'user_id', $user_id));

        $summary = array('upcoming' => 0, 'past' => 0, 'cancelled' => 0);

        foreach ($results as $row) {
            if ($row->status === 'cancelled') {
                $summary['cancelled'] = absint($row->count);
            } elseif ($row->status === 'confirmed') {
                // Would need to check dates to split into upcoming/past
                $summary['upcoming'] = absint($row->count);
            }
        }

        return $summary;
    }
}
