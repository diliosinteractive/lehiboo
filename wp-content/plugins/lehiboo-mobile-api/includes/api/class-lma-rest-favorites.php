<?php
/**
 * REST Favorites Controller
 * Endpoints favoris utilisateur
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Favorites {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List favorites
        register_rest_route($this->namespace, '/me/favorites', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_favorites'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Add favorite
        register_rest_route($this->namespace, '/me/favorites', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_favorite'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Remove favorite
        register_rest_route($this->namespace, '/me/favorites/(?P<event_id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'remove_favorite'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Check if favorited
        register_rest_route($this->namespace, '/me/favorites/(?P<event_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_favorite'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Toggle favorite
        register_rest_route($this->namespace, '/me/favorites/(?P<event_id>\d+)/toggle', array(
            'methods' => 'POST',
            'callback' => array($this, 'toggle_favorite'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Get user favorites
     */
    public function get_favorites($request) {
        $user_id = get_current_user_id();

        $favorites = get_user_meta($user_id, 'lma_favorites', true);

        if (!is_array($favorites) || empty($favorites)) {
            return LMA_Response::success(array(
                'favorites' => array(),
                'count' => 0,
            ));
        }

        // Get events data
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';
        $now = time();

        $events = array();
        foreach ($favorites as $event_id) {
            $event = get_post($event_id);

            if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
                continue;
            }

            // Check if event is still upcoming
            $event_date = get_post_meta($event_id, $meta_prefix . 'date_start', true);

            $events[] = array(
                'id' => $event->ID,
                'title' => $event->post_title,
                'slug' => $event->post_name,
                'thumbnail' => get_the_post_thumbnail_url($event->ID, 'medium'),
                'date' => $event_date ? date('Y-m-d', $event_date) : null,
                'time' => get_post_meta($event_id, $meta_prefix . 'time_start', true) ?: null,
                'venue' => get_post_meta($event_id, $meta_prefix . 'venue_name', true) ?: null,
                'city' => get_post_meta($event_id, $meta_prefix . 'city', true) ?: null,
                'price' => $this->get_price_info($event_id),
                'is_upcoming' => $event_date ? ($event_date >= $now) : true,
                'favorited_at' => get_user_meta($user_id, 'lma_favorite_' . $event_id . '_at', true) ?: null,
            );
        }

        // Sort by favorited_at descending
        usort($events, function($a, $b) {
            $time_a = $a['favorited_at'] ? strtotime($a['favorited_at']) : 0;
            $time_b = $b['favorited_at'] ? strtotime($b['favorited_at']) : 0;
            return $time_b - $time_a;
        });

        return LMA_Response::success(array(
            'favorites' => $events,
            'count' => count($events),
        ));
    }

    /**
     * Add event to favorites
     */
    public function add_favorite($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $event_id = absint($params['event_id'] ?? 0);

        if (!$event_id) {
            return LMA_Response::error(
                'missing_event_id',
                __('ID de l\'événement requis', 'lehiboo-mobile-api'),
                400
            );
        }

        // Verify event exists
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'event') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Get current favorites
        $favorites = get_user_meta($user_id, 'lma_favorites', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }

        // Check if already favorited
        if (in_array($event_id, $favorites)) {
            return LMA_Response::success(array(
                'message' => __('Événement déjà dans vos favoris', 'lehiboo-mobile-api'),
                'is_favorited' => true,
            ));
        }

        // Add to favorites
        $favorites[] = $event_id;
        update_user_meta($user_id, 'lma_favorites', $favorites);
        update_user_meta($user_id, 'lma_favorite_' . $event_id . '_at', current_time('mysql'));

        return LMA_Response::success(array(
            'message' => __('Événement ajouté aux favoris', 'lehiboo-mobile-api'),
            'is_favorited' => true,
        ));
    }

    /**
     * Remove event from favorites
     */
    public function remove_favorite($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('event_id'));

        // Get current favorites
        $favorites = get_user_meta($user_id, 'lma_favorites', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }

        // Check if in favorites
        $key = array_search($event_id, $favorites);
        if ($key === false) {
            return LMA_Response::success(array(
                'message' => __('Événement non présent dans vos favoris', 'lehiboo-mobile-api'),
                'is_favorited' => false,
            ));
        }

        // Remove from favorites
        unset($favorites[$key]);
        $favorites = array_values($favorites); // Re-index
        update_user_meta($user_id, 'lma_favorites', $favorites);
        delete_user_meta($user_id, 'lma_favorite_' . $event_id . '_at');

        return LMA_Response::success(array(
            'message' => __('Événement retiré des favoris', 'lehiboo-mobile-api'),
            'is_favorited' => false,
        ));
    }

    /**
     * Check if event is favorited
     */
    public function check_favorite($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('event_id'));

        $favorites = get_user_meta($user_id, 'lma_favorites', true);
        $is_favorited = is_array($favorites) && in_array($event_id, $favorites);

        return LMA_Response::success(array(
            'is_favorited' => $is_favorited,
            'favorited_at' => $is_favorited ? get_user_meta($user_id, 'lma_favorite_' . $event_id . '_at', true) : null,
        ));
    }

    /**
     * Toggle favorite status
     */
    public function toggle_favorite($request) {
        $user_id = get_current_user_id();
        $event_id = absint($request->get_param('event_id'));

        // Verify event exists
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'event') {
            return LMA_Response::error(
                'event_not_found',
                __('Événement introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Get current favorites
        $favorites = get_user_meta($user_id, 'lma_favorites', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }

        $key = array_search($event_id, $favorites);

        if ($key !== false) {
            // Remove
            unset($favorites[$key]);
            $favorites = array_values($favorites);
            update_user_meta($user_id, 'lma_favorites', $favorites);
            delete_user_meta($user_id, 'lma_favorite_' . $event_id . '_at');

            return LMA_Response::success(array(
                'message' => __('Événement retiré des favoris', 'lehiboo-mobile-api'),
                'is_favorited' => false,
            ));
        } else {
            // Add
            $favorites[] = $event_id;
            update_user_meta($user_id, 'lma_favorites', $favorites);
            update_user_meta($user_id, 'lma_favorite_' . $event_id . '_at', current_time('mysql'));

            return LMA_Response::success(array(
                'message' => __('Événement ajouté aux favoris', 'lehiboo-mobile-api'),
                'is_favorited' => true,
            ));
        }
    }

    /**
     * Get price info for event
     */
    private function get_price_info($event_id) {
        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $price = get_post_meta($event_id, $meta_prefix . 'price', true);
        $price_max = get_post_meta($event_id, $meta_prefix . 'price_max', true);

        if (empty($price) || $price == 0) {
            return array(
                'is_free' => true,
                'display' => __('Gratuit', 'lehiboo-mobile-api'),
            );
        }

        $formatted = number_format((float)$price, 0, ',', ' ') . ' FCFA';

        if ($price_max && $price_max > $price) {
            $formatted = number_format((float)$price, 0, ',', ' ') . ' - ' . number_format((float)$price_max, 0, ',', ' ') . ' FCFA';
        }

        return array(
            'is_free' => false,
            'min' => (float)$price,
            'max' => $price_max ? (float)$price_max : null,
            'display' => $formatted,
        );
    }
}
