<?php
/**
 * REST Alerts Controller
 * Endpoints pour les alertes et recherches sauvegardées
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Alerts {

    protected $namespace = 'lehiboo/v2';

    /**
     * Maximum alerts per user
     */
    const MAX_ALERTS_PER_USER = 20;

    /**
     * Register routes
     */
    public function register_routes() {
        // List alerts
        register_rest_route($this->namespace, '/me/alerts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_alerts'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Create alert
        register_rest_route($this->namespace, '/me/alerts', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_alert'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Update alert
        register_rest_route($this->namespace, '/me/alerts/(?P<id>\d+)', array(
            'methods' => 'PATCH',
            'callback' => array($this, 'update_alert'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Delete alert
        register_rest_route($this->namespace, '/me/alerts/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_alert'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Get user alerts
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_alerts($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'lma_alerts';

        $alerts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            ),
            ARRAY_A
        );

        $formatted_alerts = array();
        foreach ($alerts as $alert) {
            $formatted_alerts[] = $this->format_alert($alert);
        }

        return LMA_Response::success(array(
            'alerts' => $formatted_alerts,
            'count' => count($formatted_alerts),
            'limit' => self::MAX_ALERTS_PER_USER,
        ));
    }

    /**
     * Create a new alert
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function create_alert($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $params = $request->get_json_params();
        $table = $wpdb->prefix . 'lma_alerts';

        // Check alerts limit
        $current_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE user_id = %d",
                $user_id
            )
        );

        if ($current_count >= self::MAX_ALERTS_PER_USER) {
            return LMA_Response::error(
                'alerts_limit_reached',
                sprintf(__('Limite d\'alertes atteinte (%d maximum)', 'lehiboo-mobile-api'), self::MAX_ALERTS_PER_USER),
                403
            );
        }

        // Validate data
        $validation = LMA_Validator::validate_alert($params);
        if (is_wp_error($validation)) {
            return LMA_Response::from_error($validation);
        }

        // Generate default name if not provided
        $name = $this->generate_alert_name($params);

        // Prepare data for insert
        $data = array(
            'user_id' => $user_id,
            'name' => $name,
            'search_query' => sanitize_text_field($params['search_query'] ?? ''),
            'city_slug' => sanitize_text_field($params['city_slug'] ?? ''),
            'latitude' => isset($params['latitude']) ? floatval($params['latitude']) : null,
            'longitude' => isset($params['longitude']) ? floatval($params['longitude']) : null,
            'radius_km' => isset($params['radius_km']) ? absint($params['radius_km']) : null,
            'date_type' => sanitize_text_field($params['date_type'] ?? ''),
            'start_date' => !empty($params['start_date']) ? sanitize_text_field($params['start_date']) : null,
            'end_date' => !empty($params['end_date']) ? sanitize_text_field($params['end_date']) : null,
            'price_type' => sanitize_text_field($params['price_type'] ?? ''),
            'price_min' => isset($params['price_min']) ? floatval($params['price_min']) : null,
            'price_max' => isset($params['price_max']) ? floatval($params['price_max']) : null,
            'categories' => isset($params['categories']) ? wp_json_encode($params['categories']) : null,
            'tags' => isset($params['tags']) ? wp_json_encode($params['tags']) : null,
            'is_family_friendly' => isset($params['is_family_friendly']) ? (int) $params['is_family_friendly'] : null,
            'is_accessible_pmr' => isset($params['is_accessible_pmr']) ? (int) $params['is_accessible_pmr'] : null,
            'is_online' => isset($params['is_online']) ? (int) $params['is_online'] : null,
            'enable_push_alert' => isset($params['enable_push_alert']) ? (int) $params['enable_push_alert'] : 1,
            'enable_email_alert' => isset($params['enable_email_alert']) ? (int) $params['enable_email_alert'] : 0,
            'created_at' => current_time('mysql'),
        );

        // Remove empty strings (convert to null for DB)
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return LMA_Response::error(
                'db_error',
                __('Erreur lors de la création de l\'alerte', 'lehiboo-mobile-api'),
                500
            );
        }

        $alert_id = $wpdb->insert_id;

        // Fetch the created alert
        $alert = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $alert_id),
            ARRAY_A
        );

        return LMA_Response::success($this->format_alert($alert), 201);
    }

    /**
     * Update an alert
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_alert($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $alert_id = absint($request->get_param('id'));
        $params = $request->get_json_params();
        $table = $wpdb->prefix . 'lma_alerts';

        // Check if alert exists and belongs to user
        $alert = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d AND user_id = %d",
                $alert_id,
                $user_id
            ),
            ARRAY_A
        );

        if (!$alert) {
            return LMA_Response::error(
                'alert_not_found',
                __('Alerte introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Prepare update data (only allowed fields)
        $update_data = array(
            'updated_at' => current_time('mysql'),
        );

        // Name validation and update
        if (isset($params['name'])) {
            $name = sanitize_text_field($params['name']);
            if (strlen($name) < 2) {
                return LMA_Response::error(
                    'validation_error',
                    __('Le nom doit contenir au moins 2 caractères', 'lehiboo-mobile-api'),
                    400
                );
            }
            if (strlen($name) > 100) {
                return LMA_Response::error(
                    'validation_error',
                    __('Le nom ne peut pas dépasser 100 caractères', 'lehiboo-mobile-api'),
                    400
                );
            }
            $update_data['name'] = $name;
        }

        // Notification settings
        if (isset($params['enable_push_alert'])) {
            $update_data['enable_push_alert'] = (int) $params['enable_push_alert'];
        }

        if (isset($params['enable_email_alert'])) {
            $update_data['enable_email_alert'] = (int) $params['enable_email_alert'];
        }

        // Update in database
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => $alert_id)
        );

        if ($result === false) {
            return LMA_Response::error(
                'db_error',
                __('Erreur lors de la mise à jour de l\'alerte', 'lehiboo-mobile-api'),
                500
            );
        }

        // Fetch updated alert
        $updated_alert = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $alert_id),
            ARRAY_A
        );

        return LMA_Response::success($this->format_alert($updated_alert));
    }

    /**
     * Delete an alert
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_alert($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $alert_id = absint($request->get_param('id'));
        $table = $wpdb->prefix . 'lma_alerts';

        // Check if alert exists and belongs to user
        $alert = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d AND user_id = %d",
                $alert_id,
                $user_id
            )
        );

        if (!$alert) {
            return LMA_Response::error(
                'alert_not_found',
                __('Alerte introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Delete
        $result = $wpdb->delete($table, array('id' => $alert_id));

        if ($result === false) {
            return LMA_Response::error(
                'db_error',
                __('Erreur lors de la suppression de l\'alerte', 'lehiboo-mobile-api'),
                500
            );
        }

        return LMA_Response::success(array(
            'message' => __('Alerte supprimée avec succès', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Generate default alert name
     *
     * @param array $params
     * @return string
     */
    private function generate_alert_name($params) {
        // If name provided, use it
        if (!empty($params['name'])) {
            return sanitize_text_field($params['name']);
        }

        $parts = array();

        // City
        if (!empty($params['city_slug'])) {
            $parts[] = ucfirst(str_replace('-', ' ', $params['city_slug']));
        }

        // Categories
        if (!empty($params['categories']) && is_array($params['categories'])) {
            $category_names = array_map(function($slug) {
                return ucfirst(str_replace('-', ' ', $slug));
            }, array_slice($params['categories'], 0, 2));
            $parts[] = implode(', ', $category_names);
        }

        // Search query
        if (!empty($params['search_query'])) {
            $parts[] = $params['search_query'];
        }

        // If we have parts, join them
        if (!empty($parts)) {
            return implode(' - ', $parts);
        }

        // Default: date-based name
        return sprintf(__('Alerte du %s', 'lehiboo-mobile-api'), wp_date('d/m/Y'));
    }

    /**
     * Format alert for API response
     *
     * @param array $alert
     * @return array
     */
    private function format_alert($alert) {
        return array(
            'id' => (int) $alert['id'],
            'name' => $alert['name'],
            'created_at' => $alert['created_at'] ? mysql_to_rfc3339($alert['created_at']) : null,
            'updated_at' => $alert['updated_at'] ? mysql_to_rfc3339($alert['updated_at']) : null,
            'search_criteria' => array(
                'search_query' => $alert['search_query'] ?: null,
                'city_slug' => $alert['city_slug'] ?: null,
                'latitude' => $alert['latitude'] ? (float) $alert['latitude'] : null,
                'longitude' => $alert['longitude'] ? (float) $alert['longitude'] : null,
                'radius_km' => $alert['radius_km'] ? (int) $alert['radius_km'] : null,
                'date_type' => $alert['date_type'] ?: null,
                'start_date' => $alert['start_date'] ?: null,
                'end_date' => $alert['end_date'] ?: null,
                'price_type' => $alert['price_type'] ?: null,
                'price_min' => $alert['price_min'] ? (float) $alert['price_min'] : null,
                'price_max' => $alert['price_max'] ? (float) $alert['price_max'] : null,
                'categories' => $alert['categories'] ? json_decode($alert['categories'], true) : null,
                'tags' => $alert['tags'] ? json_decode($alert['tags'], true) : null,
                'is_family_friendly' => $alert['is_family_friendly'] !== null ? (bool) $alert['is_family_friendly'] : null,
                'is_accessible_pmr' => $alert['is_accessible_pmr'] !== null ? (bool) $alert['is_accessible_pmr'] : null,
                'is_online' => $alert['is_online'] !== null ? (bool) $alert['is_online'] : null,
            ),
            'enable_push_alert' => (bool) $alert['enable_push_alert'],
            'enable_email_alert' => (bool) $alert['enable_email_alert'],
        );
    }
}
