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
        $data['full_location'] = self::get_full_location($event, $meta_prefix);
        $data['restrictions'] = self::get_restrictions($event, $meta_prefix);
        $data['environment'] = self::get_environment($event, $meta_prefix);
        $data['ticket_types'] = self::get_ticket_types($event, $meta_prefix);
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
}
