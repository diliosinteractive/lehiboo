<?php
/**
 * Event Formatter Class
 * Formatage des événements pour l'API
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Event_Formatter {

    /**
     * Format event for list view
     *
     * @param WP_Post $event
     * @param array $options
     * @return array
     */
    public static function format_list($event, $options = array()) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        return array(
            'id' => $event->ID,
            'title' => $event->post_title,
            'slug' => $event->post_name,
            'excerpt' => self::get_excerpt($event),
            'featured_image' => self::get_images($event),
            'category' => self::get_primary_category($event),
            'dates' => self::get_dates($event, $meta_prefix),
            'location' => self::get_location($event, $meta_prefix, $options),
            'pricing' => self::get_pricing($event, $meta_prefix),
            'availability' => self::get_availability($event, $meta_prefix),
            'ratings' => self::get_ratings($event, $meta_prefix),
            'organizer' => self::get_organizer_summary($event),
            'tags' => self::get_tags($event),
            'is_favorite' => isset($options['user_id']) ? self::is_favorite($event->ID, $options['user_id']) : false,
        );
    }

    /**
     * Format event for detail view
     *
     * @param WP_Post $event
     * @param array $options
     * @return array
     */
    public static function format_detail($event, $options = array()) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $data = self::format_list($event, $options);

        // Additional fields for detail view
        $data['description'] = apply_filters('the_content', $event->post_content);
        $data['gallery'] = self::get_gallery($event);
        $data['thematiques'] = self::get_thematiques($event);
        $data['event_type'] = self::get_event_type($event);
        $data['target_audience'] = self::get_target_audience($event);
        $data['full_location'] = self::get_full_location($event, $meta_prefix);
        $data['restrictions'] = self::get_restrictions($event, $meta_prefix);
        $data['environment'] = self::get_environment($event, $meta_prefix);
        $data['ticket_types'] = self::get_ticket_types($event, $meta_prefix);
        $data['tickets'] = self::get_tickets_full($event, $meta_prefix);
        $data['time_slots'] = self::get_time_slots($event, $meta_prefix);
        $data['calendar'] = self::get_calendar($event, $meta_prefix);
        $data['recurrence'] = self::get_recurrence($event, $meta_prefix);
        $data['extra_services'] = self::get_extra_services($event, $meta_prefix);
        $data['coupons'] = self::get_coupons($event, $meta_prefix);
        $data['seat_config'] = self::get_seat_config($event, $meta_prefix);
        $data['external_booking'] = self::get_external_booking($event, $meta_prefix);
        $data['organizer'] = self::get_organizer_detail($event);
        $data['reviews'] = self::get_reviews($event);
        $data['similar_events'] = self::get_similar_events($event);
        $data['booking_info'] = self::get_booking_info($event, $meta_prefix);
        $data['share_url'] = get_permalink($event->ID);
        $data['created_at'] = $event->post_date_gmt;
        $data['updated_at'] = $event->post_modified_gmt;

        return $data;
    }

    /**
     * Get excerpt
     */
    private static function get_excerpt($event) {
        if (!empty($event->post_excerpt)) {
            return wp_trim_words($event->post_excerpt, 30);
        }
        return wp_trim_words(strip_tags($event->post_content), 30);
    }

    /**
     * Get images
     */
    private static function get_images($event) {
        $thumbnail_id = get_post_thumbnail_id($event->ID);

        if (!$thumbnail_id) {
            return null;
        }

        return array(
            'thumbnail' => wp_get_attachment_image_url($thumbnail_id, 'thumbnail'),
            'medium' => wp_get_attachment_image_url($thumbnail_id, 'medium'),
            'large' => wp_get_attachment_image_url($thumbnail_id, 'large'),
            'full' => wp_get_attachment_image_url($thumbnail_id, 'full'),
        );
    }

    /**
     * Get gallery
     */
    private static function get_gallery($event) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $gallery = get_post_meta($event->ID, $meta_prefix . 'gallery', true);

        if (empty($gallery) || !is_array($gallery)) {
            return array();
        }

        return array_map(function($image_id) {
            return wp_get_attachment_image_url($image_id, 'large');
        }, $gallery);
    }

    /**
     * Get primary category
     */
    private static function get_primary_category($event) {
        $categories = wp_get_post_terms($event->ID, 'event_cat', array('number' => 1));

        if (empty($categories) || is_wp_error($categories)) {
            return null;
        }

        $cat = $categories[0];
        return array(
            'id' => $cat->term_id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'icon' => get_term_meta($cat->term_id, 'icon', true) ?: null,
        );
    }

    /**
     * Get thematiques
     */
    private static function get_thematiques($event) {
        $terms = wp_get_post_terms($event->ID, 'event_thematique');

        if (empty($terms) || is_wp_error($terms)) {
            return array();
        }

        return array_map(function($term) {
            return array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }, $terms);
    }

    /**
     * Get event type (Type d'événement - event_tag taxonomy)
     */
    private static function get_event_type($event) {
        $terms = wp_get_post_terms($event->ID, 'event_tag', array('number' => 1));

        if (empty($terms) || is_wp_error($terms)) {
            return null;
        }

        $term = $terms[0];
        return array(
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }

    /**
     * Get target audience (Public visé - event_public taxonomy)
     */
    private static function get_target_audience($event) {
        $terms = wp_get_post_terms($event->ID, 'event_public');

        if (empty($terms) || is_wp_error($terms)) {
            return array();
        }

        return array_map(function($term) {
            return array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }, $terms);
    }

    /**
     * Get tags
     */
    private static function get_tags($event) {
        $tags = wp_get_post_terms($event->ID, 'event_tag', array('fields' => 'names'));
        return is_wp_error($tags) ? array() : $tags;
    }

    /**
     * Get dates
     */
    private static function get_dates($event, $prefix) {
        // Note: eventlist plugin uses 'start_date_str' and 'end_date_str' for timestamps
        $start_date = get_post_meta($event->ID, $prefix . 'start_date_str', true);
        $end_date = get_post_meta($event->ID, $prefix . 'end_date_str', true);
        $start_time = get_post_meta($event->ID, $prefix . 'start_time', true);
        $end_time = get_post_meta($event->ID, $prefix . 'end_time', true);

        // Handle timestamps
        if (is_numeric($start_date)) {
            $start_timestamp = $start_date;
            $start_date = date('Y-m-d', $start_date);
        } else {
            $start_timestamp = strtotime($start_date);
        }

        if (is_numeric($end_date)) {
            $end_date = date('Y-m-d', $end_date);
        }

        // Calculate duration
        $duration = null;
        if ($start_time && $end_time) {
            $start_ts = strtotime($start_time);
            $end_ts = strtotime($end_time);
            if ($end_ts > $start_ts) {
                $duration = ($end_ts - $start_ts) / 60;
            }
        }

        return array(
            'start_date' => $start_date,
            'end_date' => $end_date ?: $start_date,
            'start_time' => $start_time ?: null,
            'end_time' => $end_time ?: null,
            'display' => self::format_date_display($start_date, $start_time, $end_time),
            'duration_minutes' => $duration,
            'is_recurring' => (bool) get_post_meta($event->ID, $prefix . 'is_recurring', true),
        );
    }

    /**
     * Format date for display
     */
    private static function format_date_display($date, $start_time = null, $end_time = null) {
        if (!$date) return '';

        $timestamp = strtotime($date);
        $formatted = date_i18n('D j M Y', $timestamp);

        if ($start_time) {
            $formatted .= ' • ' . date('H\hi', strtotime($start_time));
            if ($end_time) {
                $formatted .= '-' . date('H\hi', strtotime($end_time));
            }
        }

        return $formatted;
    }

    /**
     * Get location
     */
    private static function get_location($event, $prefix, $options = array()) {
        $venue_id = get_post_meta($event->ID, $prefix . 'venue_id', true);
        $venue_name = '';

        if ($venue_id) {
            $venue = get_post($venue_id);
            if ($venue) {
                $venue_name = $venue->post_title;
            }
        }

        if (!$venue_name) {
            $venue_name = get_post_meta($event->ID, $prefix . 'venue', true)
                ?: get_post_meta($event->ID, $prefix . 'location', true);
        }

        $lat = floatval(get_post_meta($event->ID, $prefix . 'lat', true));
        $lng = floatval(get_post_meta($event->ID, $prefix . 'lng', true));

        $location = array(
            'venue_name' => $venue_name,
            'city' => get_post_meta($event->ID, $prefix . 'city', true),
            'address' => get_post_meta($event->ID, $prefix . 'address', true),
            'lat' => $lat ?: null,
            'lng' => $lng ?: null,
        );

        // Calculate distance if user coordinates provided
        if (!empty($options['lat']) && !empty($options['lng']) && $lat && $lng) {
            $location['distance_km'] = self::calculate_distance(
                $options['lat'], $options['lng'],
                $lat, $lng
            );
        }

        return $location;
    }

    /**
     * Get full location details
     */
    private static function get_full_location($event, $prefix) {
        $location = self::get_location($event, $prefix);

        $location['postal_code'] = get_post_meta($event->ID, $prefix . 'postal_code', true);
        $location['country'] = get_post_meta($event->ID, $prefix . 'country', true) ?: 'France';

        if ($location['lat'] && $location['lng']) {
            $location['directions_url'] = sprintf(
                'https://www.google.com/maps/dir/?api=1&destination=%s,%s',
                $location['lat'],
                $location['lng']
            );
        }

        return $location;
    }

    /**
     * Get pricing
     */
    private static function get_pricing($event, $prefix) {
        $price_min = floatval(get_post_meta($event->ID, $prefix . 'price_min', true));
        $price_max = floatval(get_post_meta($event->ID, $prefix . 'price_max', true));
        $price = floatval(get_post_meta($event->ID, $prefix . 'price', true));

        if (!$price_min && $price) {
            $price_min = $price;
        }
        if (!$price_max) {
            $price_max = $price_min;
        }

        $is_free = $price_min == 0 && $price_max == 0;

        // Format display
        $display = $is_free ? 'Gratuit' : sprintf('%d€', $price_min);
        if ($price_max > $price_min) {
            $display = sprintf('%d€ - %d€', $price_min, $price_max);
        }

        return array(
            'is_free' => $is_free,
            'min' => $price_min,
            'max' => $price_max,
            'currency' => 'EUR',
            'display' => $display,
        );
    }

    /**
     * Get ticket types
     */
    private static function get_ticket_types($event, $prefix) {
        $types = get_post_meta($event->ID, $prefix . 'ticket_types', true);

        if (empty($types) || !is_array($types)) {
            // Fallback to simple pricing
            $pricing = self::get_pricing($event, $prefix);
            if ($pricing['is_free']) {
                return array(array(
                    'id' => 1,
                    'name' => 'Entrée gratuite',
                    'price' => 0,
                    'available' => true,
                ));
            }
            return array(array(
                'id' => 1,
                'name' => 'Tarif standard',
                'price' => $pricing['min'],
                'available' => true,
            ));
        }

        return array_map(function($type, $index) {
            return array(
                'id' => isset($type['id']) ? $type['id'] : $index + 1,
                'name' => $type['name'] ?? 'Tarif',
                'price' => floatval($type['price'] ?? 0),
                'description' => $type['description'] ?? null,
                'available' => isset($type['available']) ? (bool) $type['available'] : true,
                'spots_remaining' => isset($type['spots_remaining']) ? absint($type['spots_remaining']) : null,
            );
        }, $types, array_keys($types));
    }

    /**
     * Get availability
     */
    private static function get_availability($event, $prefix) {
        $total = absint(get_post_meta($event->ID, $prefix . 'total_capacity', true));
        $remaining = absint(get_post_meta($event->ID, $prefix . 'spots_remaining', true));

        if (!$total) {
            $total = absint(get_post_meta($event->ID, $prefix . 'capacity', true)) ?: 100;
        }

        if (!$remaining) {
            $remaining = $total;
        }

        $status = 'available';
        if ($remaining <= 0) {
            $status = 'sold_out';
        } elseif ($remaining < ($total * 0.1)) {
            $status = 'low_availability';
        }

        return array(
            'status' => $status,
            'total_capacity' => $total,
            'spots_remaining' => $remaining,
            'percentage_filled' => $total > 0 ? round((($total - $remaining) / $total) * 100) : 0,
        );
    }

    /**
     * Get restrictions
     */
    private static function get_restrictions($event, $prefix) {
        $age_min = absint(get_post_meta($event->ID, $prefix . 'min_age', true));
        $age_max = absint(get_post_meta($event->ID, $prefix . 'max_age', true));
        $family_friendly = (bool) get_post_meta($event->ID, $prefix . 'family_friendly', true);

        $age_display = null;
        if ($age_min && $age_max) {
            $age_display = sprintf('%d-%d ans', $age_min, $age_max);
        } elseif ($age_min) {
            $age_display = sprintf('%d+ ans', $age_min);
        }

        return array(
            'age_min' => $age_min ?: null,
            'age_max' => $age_max ?: null,
            'age_display' => $age_display,
            'family_friendly' => $family_friendly,
            'requirements' => get_post_meta($event->ID, $prefix . 'requirements', true) ?: null,
        );
    }

    /**
     * Get environment
     */
    private static function get_environment($event, $prefix) {
        $indoor = (bool) get_post_meta($event->ID, $prefix . 'indoor', true);
        $outdoor = (bool) get_post_meta($event->ID, $prefix . 'outdoor', true);

        return array(
            'indoor' => $indoor,
            'outdoor' => $outdoor,
        );
    }

    /**
     * Get ratings
     */
    private static function get_ratings($event, $prefix) {
        $average = floatval(get_post_meta($event->ID, $prefix . 'rating', true));
        $count = absint(get_post_meta($event->ID, $prefix . 'reviews_count', true));

        if (!$average) {
            return null;
        }

        return array(
            'average' => round($average, 1),
            'count' => $count,
        );
    }

    /**
     * Get reviews
     */
    private static function get_reviews($event, $limit = 5) {
        $comments = get_comments(array(
            'post_id' => $event->ID,
            'status' => 'approve',
            'number' => $limit,
            'orderby' => 'comment_date_gmt',
            'order' => 'DESC',
        ));

        return array_map(function($comment) {
            return array(
                'id' => $comment->comment_ID,
                'author' => $comment->comment_author,
                'rating' => absint(get_comment_meta($comment->comment_ID, 'rating', true)) ?: null,
                'comment' => $comment->comment_content,
                'date' => date('Y-m-d', strtotime($comment->comment_date)),
                'verified_booking' => (bool) get_comment_meta($comment->comment_ID, 'verified_booking', true),
            );
        }, $comments);
    }

    /**
     * Get organizer summary
     */
    private static function get_organizer_summary($event) {
        $author = get_user_by('ID', $event->post_author);

        if (!$author) {
            return null;
        }

        return array(
            'id' => $author->ID,
            'name' => $author->display_name,
            'verified' => (bool) get_user_meta($author->ID, 'verified', true),
        );
    }

    /**
     * Get organizer detail
     */
    private static function get_organizer_detail($event) {
        $author = get_user_by('ID', $event->post_author);

        if (!$author) {
            return null;
        }

        return array(
            'id' => $author->ID,
            'name' => $author->display_name,
            'description' => get_user_meta($author->ID, 'description', true),
            'logo' => get_avatar_url($author->ID),
            'verified' => (bool) get_user_meta($author->ID, 'verified', true),
            'contact' => array(
                'phone' => get_user_meta($author->ID, 'phone', true) ?: null,
                'email' => $author->user_email,
                'website' => $author->user_url ?: null,
            ),
        );
    }

    /**
     * Get similar events
     */
    private static function get_similar_events($event, $limit = 3) {
        $categories = wp_get_post_terms($event->ID, 'event_cat', array('fields' => 'ids'));

        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__not_in' => array($event->ID),
            'meta_query' => array(
                array(
                    'key' => (defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_') . 'start_date_str',
                    'value' => time(),
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ),
            ),
        );

        if (!empty($categories)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_cat',
                    'field' => 'term_id',
                    'terms' => $categories,
                ),
            );
        }

        $query = new WP_Query($args);

        return array_map(function($post) {
            return array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                'price_display' => self::get_pricing($post, defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_')['display'],
                'date_display' => self::format_date_display(
                    get_post_meta($post->ID, (defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_') . 'start_date_str', true)
                ),
            );
        }, $query->posts);
    }

    /**
     * Get booking info
     */
    private static function get_booking_info($event, $prefix) {
        return array(
            'mode' => 'online',
            'cancellation_policy' => get_post_meta($event->ID, $prefix . 'cancellation_policy', true)
                ?: 'Annulation gratuite jusqu\'à 48h avant',
            'instant_confirmation' => true,
        );
    }

    /**
     * Check if event is favorite for user
     */
    private static function is_favorite($event_id, $user_id) {
        $favorites = get_user_meta($user_id, 'lma_favorites', true);
        return is_array($favorites) && in_array($event_id, $favorites);
    }

    /**
     * Calculate distance between two points
     */
    private static function calculate_distance($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6371; // km

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $lng1 = deg2rad($lng1);
        $lng2 = deg2rad($lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlng / 2) * sin($dlng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earth_radius * $c, 1);
    }

    /**
     * Get full tickets information
     */
    private static function get_tickets_full($event, $prefix) {
        $tickets = get_post_meta($event->ID, $prefix . 'ticket', true);

        if (empty($tickets) || !is_array($tickets)) {
            return array();
        }

        return array_map(function($ticket) {
            return array(
                'id' => isset($ticket['ticket_id']) ? $ticket['ticket_id'] : null,
                'name' => isset($ticket['name_ticket']) ? $ticket['name_ticket'] : '',
                'price' => isset($ticket['price_ticket']) ? floatval($ticket['price_ticket']) : 0,
                'description' => isset($ticket['desc_ticket']) ? $ticket['desc_ticket'] : '',
                'private_description' => isset($ticket['private_desc_ticket']) ? $ticket['private_desc_ticket'] : '',
                'quantity' => isset($ticket['qty_ticket']) ? absint($ticket['qty_ticket']) : 0,
                'max_per_booking' => isset($ticket['max_qty_ticket']) ? absint($ticket['max_qty_ticket']) : 0,
                'min_per_booking' => isset($ticket['min_qty_ticket']) ? absint($ticket['min_qty_ticket']) : 0,
                'setup_seat' => isset($ticket['setup_seat']) ? $ticket['setup_seat'] : 'no',
                'setup_mode' => isset($ticket['setup_mode']) ? $ticket['setup_mode'] : 'manual',
                'seat_list' => isset($ticket['seat_list']) ? $ticket['seat_list'] : '',
                'seat_code_setup' => isset($ticket['seat_code_setup']) ? $ticket['seat_code_setup'] : array(),
                'person_types' => isset($ticket['person_type']) ? $ticket['person_type'] : array(),
            );
        }, $tickets);
    }

    /**
     * Get time slots (créneaux horaires)
     */
    private static function get_time_slots($event, $prefix) {
        $option_calendar = get_post_meta($event->ID, $prefix . 'option_calendar', true);
        $schedules_time = get_post_meta($event->ID, $prefix . 'schedules_time', true);
        $ts_start = get_post_meta($event->ID, $prefix . 'ts_start', true);
        $ts_end = get_post_meta($event->ID, $prefix . 'ts_end', true);

        $slots = array(
            'calendar_type' => $option_calendar ?: 'manual',
            'schedules' => array(),
            'weekly_slots' => array(),
        );

        // Schedules time (créneaux programmés)
        if (!empty($schedules_time) && is_array($schedules_time)) {
            foreach ($schedules_time as $schedule) {
                $slots['schedules'][] = array(
                    'start_time' => isset($schedule['start_time']) ? $schedule['start_time'] : '',
                    'end_time' => isset($schedule['end_time']) ? $schedule['end_time'] : '',
                    'book_before' => isset($schedule['book_before']) ? absint($schedule['book_before']) : 0,
                );
            }
        }

        // Time slots by day of week (pour récurrence weekly)
        if (!empty($ts_start) && is_array($ts_start)) {
            $days_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
            foreach ($ts_start as $day_index => $times) {
                if (!empty($times) && is_array($times)) {
                    $day_name = isset($days_names[$day_index]) ? $days_names[$day_index] : $day_index;
                    $slots['weekly_slots'][$day_name] = array();
                    foreach ($times as $slot_index => $start_time) {
                        $end_time = isset($ts_end[$day_index][$slot_index]) ? $ts_end[$day_index][$slot_index] : '';
                        if ($start_time && $end_time) {
                            $slots['weekly_slots'][$day_name][] = array(
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                            );
                        }
                    }
                }
            }
        }

        return $slots;
    }

    /**
     * Get calendar dates
     */
    private static function get_calendar($event, $prefix) {
        $option_calendar = get_post_meta($event->ID, $prefix . 'option_calendar', true);
        $calendar_manual = get_post_meta($event->ID, $prefix . 'calendar', true);
        $calendar_recurrence = get_post_meta($event->ID, $prefix . 'calendar_recurrence', true);
        $disable_date = get_post_meta($event->ID, $prefix . 'disable_date', true);
        $disable_date_time_slot = get_post_meta($event->ID, $prefix . 'disable_date_time_slot', true);

        $result = array(
            'type' => $option_calendar ?: 'manual',
            'dates' => array(),
            'disabled_dates' => array(),
            'disabled_time_slots' => array(),
        );

        // Manual calendar dates
        if ($option_calendar === 'manual' && !empty($calendar_manual) && is_array($calendar_manual)) {
            foreach ($calendar_manual as $cal) {
                if (empty($cal['date'])) continue;
                $result['dates'][] = array(
                    'id' => isset($cal['calendar_id']) ? $cal['calendar_id'] : null,
                    'date' => $cal['date'],
                    'end_date' => isset($cal['end_date']) ? $cal['end_date'] : $cal['date'],
                    'start_time' => isset($cal['start_time']) ? $cal['start_time'] : '',
                    'end_time' => isset($cal['end_time']) ? $cal['end_time'] : '',
                    'book_before' => isset($cal['book_before']) ? absint($cal['book_before']) : 0,
                );
            }
        }

        // Recurrence calendar (auto-generated dates)
        if ($option_calendar === 'auto' && !empty($calendar_recurrence) && is_array($calendar_recurrence)) {
            foreach ($calendar_recurrence as $cal) {
                if (empty($cal['date'])) continue;
                $result['dates'][] = array(
                    'id' => isset($cal['calendar_id']) ? $cal['calendar_id'] : null,
                    'date' => $cal['date'],
                    'start_time' => isset($cal['start_time']) ? $cal['start_time'] : '',
                    'end_time' => isset($cal['end_time']) ? $cal['end_time'] : '',
                    'book_before' => isset($cal['book_before']) ? absint($cal['book_before']) : 0,
                );
            }
        }

        // Disabled dates
        if (!empty($disable_date) && is_array($disable_date)) {
            foreach ($disable_date as $disabled) {
                if (empty($disabled['start_date']) && empty($disabled['end_date'])) continue;
                $result['disabled_dates'][] = array(
                    'start_date' => isset($disabled['start_date']) ? $disabled['start_date'] : '',
                    'end_date' => isset($disabled['end_date']) ? $disabled['end_date'] : '',
                    'schedules_time' => isset($disabled['schedules_time']) ? $disabled['schedules_time'] : '',
                );
            }
        }

        // Disabled time slots
        if (!empty($disable_date_time_slot) && is_array($disable_date_time_slot)) {
            foreach ($disable_date_time_slot as $disabled) {
                $result['disabled_time_slots'][] = array(
                    'start_date' => isset($disabled['start_date']) ? $disabled['start_date'] : '',
                    'end_date' => isset($disabled['end_date']) ? $disabled['end_date'] : '',
                    'start_time' => isset($disabled['start_time']) ? $disabled['start_time'] : '',
                    'end_time' => isset($disabled['end_time']) ? $disabled['end_time'] : '',
                );
            }
        }

        return $result;
    }

    /**
     * Get recurrence settings
     */
    private static function get_recurrence($event, $prefix) {
        $option_calendar = get_post_meta($event->ID, $prefix . 'option_calendar', true);

        if ($option_calendar !== 'auto') {
            return null;
        }

        $days_map = array(
            '0' => 'sunday',
            '1' => 'monday',
            '2' => 'tuesday',
            '3' => 'wednesday',
            '4' => 'thursday',
            '5' => 'friday',
            '6' => 'saturday',
        );

        $bydays = get_post_meta($event->ID, $prefix . 'recurrence_bydays', true);
        $bydays_names = array();
        if (!empty($bydays) && is_array($bydays)) {
            foreach ($bydays as $day) {
                if (isset($days_map[$day])) {
                    $bydays_names[] = $days_map[$day];
                }
            }
        }

        return array(
            'frequency' => get_post_meta($event->ID, $prefix . 'recurrence_frequency', true) ?: 'daily',
            'interval' => absint(get_post_meta($event->ID, $prefix . 'recurrence_interval', true)) ?: 1,
            'by_days' => $bydays_names,
            'by_week_no' => get_post_meta($event->ID, $prefix . 'recurrence_byweekno', true) ?: null,
            'by_day' => get_post_meta($event->ID, $prefix . 'recurrence_byday', true) ?: null,
            'start_date' => get_post_meta($event->ID, $prefix . 'calendar_start_date', true) ?: null,
            'end_date' => get_post_meta($event->ID, $prefix . 'calendar_end_date', true) ?: null,
            'default_start_time' => get_post_meta($event->ID, $prefix . 'calendar_recurrence_start_time', true) ?: null,
            'default_end_time' => get_post_meta($event->ID, $prefix . 'calendar_recurrence_end_time', true) ?: null,
            'book_before_minutes' => absint(get_post_meta($event->ID, $prefix . 'calendar_recurrence_book_before', true)) ?: 0,
        );
    }

    /**
     * Get extra services
     */
    private static function get_extra_services($event, $prefix) {
        $services = get_post_meta($event->ID, $prefix . 'extra_service', true);

        if (empty($services) || !is_array($services)) {
            return array();
        }

        return array_map(function($service) {
            return array(
                'id' => isset($service['id']) ? $service['id'] : null,
                'name' => isset($service['name']) ? $service['name'] : '',
                'price' => isset($service['price']) ? floatval($service['price']) : 0,
                'quantity' => isset($service['qty']) ? absint($service['qty']) : 0,
                'max_quantity' => isset($service['max_qty']) ? absint($service['max_qty']) : 0,
                'description' => isset($service['desc']) ? $service['desc'] : '',
            );
        }, $services);
    }

    /**
     * Get coupons
     */
    private static function get_coupons($event, $prefix) {
        $coupons = get_post_meta($event->ID, $prefix . 'coupon', true);

        if (empty($coupons) || !is_array($coupons)) {
            return array();
        }

        return array_map(function($coupon) {
            return array(
                'id' => isset($coupon['coupon_id']) ? $coupon['coupon_id'] : null,
                'code' => isset($coupon['code']) ? $coupon['code'] : '',
                'type' => isset($coupon['type']) ? $coupon['type'] : 'fixed', // fixed or percent
                'value' => isset($coupon['value']) ? floatval($coupon['value']) : 0,
                'min_order' => isset($coupon['min_order']) ? floatval($coupon['min_order']) : 0,
                'max_uses' => isset($coupon['max_uses']) ? absint($coupon['max_uses']) : 0,
                'uses_count' => isset($coupon['uses_count']) ? absint($coupon['uses_count']) : 0,
                'start_date' => isset($coupon['start_date']) ? $coupon['start_date'] : null,
                'end_date' => isset($coupon['end_date']) ? $coupon['end_date'] : null,
            );
        }, $coupons);
    }

    /**
     * Get seat configuration
     */
    private static function get_seat_config($event, $prefix) {
        $seat_option = get_post_meta($event->ID, $prefix . 'seat_option', true);
        $ticket_map = get_post_meta($event->ID, $prefix . 'ticket_map', true);

        $config = array(
            'type' => $seat_option ?: 'none', // none, simple, map
            'map_image' => null,
            'seats' => array(),
            'areas' => array(),
            'description' => null,
        );

        if ($seat_option !== 'map' || empty($ticket_map)) {
            return $config;
        }

        // Map image
        if (!empty($ticket_map['map_image'])) {
            $config['map_image'] = wp_get_attachment_image_url($ticket_map['map_image'], 'large');
        }

        // Description
        if (!empty($ticket_map['private_desc_ticket_map'])) {
            $config['description'] = $ticket_map['private_desc_ticket_map'];
        }

        // Seats
        if (!empty($ticket_map['seat']) && is_array($ticket_map['seat'])) {
            foreach ($ticket_map['seat'] as $seat) {
                if (empty($seat['id'])) continue;
                $config['seats'][] = array(
                    'id' => $seat['id'],
                    'name' => isset($seat['name']) ? $seat['name'] : $seat['id'],
                    'price' => isset($seat['price']) ? floatval($seat['price']) : 0,
                    'person_prices' => isset($seat['person_price']) ? json_decode($seat['person_price'], true) : null,
                    'position_x' => isset($seat['position_x']) ? floatval($seat['position_x']) : 0,
                    'position_y' => isset($seat['position_y']) ? floatval($seat['position_y']) : 0,
                    'status' => isset($seat['status']) ? $seat['status'] : 'available',
                );
            }
        }

        // Areas
        if (!empty($ticket_map['area']) && is_array($ticket_map['area'])) {
            foreach ($ticket_map['area'] as $area) {
                $config['areas'][] = array(
                    'id' => isset($area['id']) ? $area['id'] : null,
                    'name' => isset($area['name']) ? $area['name'] : '',
                    'price' => isset($area['price']) ? floatval($area['price']) : 0,
                    'person_prices' => isset($area['person_price']) ? json_decode($area['person_price'], true) : null,
                    'capacity' => isset($area['capacity']) ? absint($area['capacity']) : 0,
                    'color' => isset($area['color']) ? $area['color'] : null,
                );
            }
        }

        // Seat descriptions/legend
        if (!empty($ticket_map['desc_seat']) && is_array($ticket_map['desc_seat'])) {
            $config['legend'] = array();
            foreach ($ticket_map['desc_seat'] as $desc) {
                if (empty($desc['map_type_seat'])) continue;
                $config['legend'][] = array(
                    'type' => $desc['map_type_seat'],
                    'price' => isset($desc['map_price_type_seat']) ? floatval($desc['map_price_type_seat']) : 0,
                    'color' => isset($desc['map_color_type_seat']) ? $desc['map_color_type_seat'] : null,
                );
            }
        }

        return $config;
    }

    /**
     * Get external booking info
     */
    private static function get_external_booking($event, $prefix) {
        $ticket_link = get_post_meta($event->ID, $prefix . 'ticket_link', true);

        if ($ticket_link !== 'ticket_external_link') {
            return null;
        }

        return array(
            'enabled' => true,
            'url' => get_post_meta($event->ID, $prefix . 'ticket_external_link', true) ?: null,
            'price' => floatval(get_post_meta($event->ID, $prefix . 'ticket_external_link_price', true)) ?: null,
            'button_text' => get_post_meta($event->ID, $prefix . 'ticket_external_link_text', true) ?: 'Réserver',
        );
    }
}
