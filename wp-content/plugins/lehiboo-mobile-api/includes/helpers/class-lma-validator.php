<?php
/**
 * Validator Class
 * Validation des données d'entrée
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Validator {

    /**
     * Validate registration data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_registration($data) {
        $errors = array();

        // Email
        if (empty($data['email'])) {
            $errors['email'] = __('L\'email est requis', 'lehiboo-mobile-api');
        } elseif (!LMA_Security::is_valid_email($data['email'])) {
            $errors['email'] = __('Format d\'email invalide', 'lehiboo-mobile-api');
        } elseif (email_exists($data['email'])) {
            $errors['email'] = __('Cet email est déjà utilisé', 'lehiboo-mobile-api');
        }

        // Password
        if (empty($data['password'])) {
            $errors['password'] = __('Le mot de passe est requis', 'lehiboo-mobile-api');
        } else {
            $password_check = LMA_Security::validate_password($data['password']);
            if (!$password_check['valid']) {
                $errors['password'] = implode('. ', $password_check['errors']);
            }
        }

        // First name
        if (empty($data['first_name'])) {
            $errors['first_name'] = __('Le prénom est requis', 'lehiboo-mobile-api');
        } elseif (strlen($data['first_name']) < 2) {
            $errors['first_name'] = __('Le prénom doit contenir au moins 2 caractères', 'lehiboo-mobile-api');
        }

        // Last name
        if (empty($data['last_name'])) {
            $errors['last_name'] = __('Le nom est requis', 'lehiboo-mobile-api');
        } elseif (strlen($data['last_name']) < 2) {
            $errors['last_name'] = __('Le nom doit contenir au moins 2 caractères', 'lehiboo-mobile-api');
        }

        // Phone (optional but must be valid if provided)
        if (!empty($data['phone']) && !LMA_Security::is_valid_phone($data['phone'])) {
            $errors['phone'] = __('Format de téléphone invalide', 'lehiboo-mobile-api');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Validate login data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_login($data) {
        $errors = array();

        if (empty($data['email'])) {
            $errors['email'] = __('L\'email est requis', 'lehiboo-mobile-api');
        }

        if (empty($data['password'])) {
            $errors['password'] = __('Le mot de passe est requis', 'lehiboo-mobile-api');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Validate booking data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_booking($data) {
        $errors = array();

        // Event ID
        if (empty($data['event_id'])) {
            $errors['event_id'] = __('L\'ID de l\'événement est requis', 'lehiboo-mobile-api');
        } else {
            $event = get_post($data['event_id']);
            if (!$event || $event->post_type !== 'event') {
                $errors['event_id'] = __('Événement introuvable', 'lehiboo-mobile-api');
            }
        }

        // Tickets
        if (empty($data['tickets']) || !is_array($data['tickets'])) {
            $errors['tickets'] = __('Les tickets sont requis', 'lehiboo-mobile-api');
        } else {
            foreach ($data['tickets'] as $index => $ticket) {
                if (empty($ticket['ticket_type_id'])) {
                    $errors["tickets.{$index}.ticket_type_id"] = __('Type de ticket requis', 'lehiboo-mobile-api');
                }
                if (empty($ticket['quantity']) || $ticket['quantity'] < 1) {
                    $errors["tickets.{$index}.quantity"] = __('Quantité invalide', 'lehiboo-mobile-api');
                }
            }
        }

        // Buyer info
        if (empty($data['buyer_info'])) {
            $errors['buyer_info'] = __('Les informations de l\'acheteur sont requises', 'lehiboo-mobile-api');
        } else {
            if (empty($data['buyer_info']['email']) || !LMA_Security::is_valid_email($data['buyer_info']['email'])) {
                $errors['buyer_info.email'] = __('Email de l\'acheteur invalide', 'lehiboo-mobile-api');
            }
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Validate scan data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_scan($data) {
        $errors = array();

        if (empty($data['qr_code'])) {
            $errors['qr_code'] = __('Le QR code est requis', 'lehiboo-mobile-api');
        }

        if (empty($data['event_id'])) {
            $errors['event_id'] = __('L\'ID de l\'événement est requis', 'lehiboo-mobile-api');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Validate profile update data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_profile_update($data) {
        $errors = array();

        if (isset($data['first_name']) && strlen($data['first_name']) < 2) {
            $errors['first_name'] = __('Le prénom doit contenir au moins 2 caractères', 'lehiboo-mobile-api');
        }

        if (isset($data['last_name']) && strlen($data['last_name']) < 2) {
            $errors['last_name'] = __('Le nom doit contenir au moins 2 caractères', 'lehiboo-mobile-api');
        }

        if (isset($data['phone']) && !empty($data['phone']) && !LMA_Security::is_valid_phone($data['phone'])) {
            $errors['phone'] = __('Format de téléphone invalide', 'lehiboo-mobile-api');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Validate password change
     *
     * @param array $data
     * @param WP_User $user
     * @return true|WP_Error
     */
    public static function validate_password_change($data, $user) {
        $errors = array();

        if (empty($data['current_password'])) {
            $errors['current_password'] = __('Le mot de passe actuel est requis', 'lehiboo-mobile-api');
        } elseif (!wp_check_password($data['current_password'], $user->user_pass, $user->ID)) {
            $errors['current_password'] = __('Mot de passe actuel incorrect', 'lehiboo-mobile-api');
        }

        if (empty($data['new_password'])) {
            $errors['new_password'] = __('Le nouveau mot de passe est requis', 'lehiboo-mobile-api');
        } else {
            $password_check = LMA_Security::validate_password($data['new_password']);
            if (!$password_check['valid']) {
                $errors['new_password'] = implode('. ', $password_check['errors']);
            }
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }

    /**
     * Sanitize and validate event filters
     *
     * @param WP_REST_Request $request
     * @return array
     */
    public static function sanitize_event_filters($request) {
        return array(
            'page' => absint($request->get_param('page')) ?: 1,
            'per_page' => min(absint($request->get_param('per_page')) ?: 20, 100),
            'search' => sanitize_text_field($request->get_param('search')),
            'category' => sanitize_text_field($request->get_param('category')),
            'thematique' => sanitize_text_field($request->get_param('thematique')),
            'city' => sanitize_text_field($request->get_param('city')),
            'location' => sanitize_text_field($request->get_param('location')), // event_loc taxonomy slug
            'lat' => floatval($request->get_param('lat')),
            'lng' => floatval($request->get_param('lng')),
            'radius' => absint($request->get_param('radius')) ?: 20,
            'date_from' => sanitize_text_field($request->get_param('date_from')),
            'date_to' => sanitize_text_field($request->get_param('date_to')),
            'price_min' => floatval($request->get_param('price_min')),
            'price_max' => floatval($request->get_param('price_max')),
            'free_only' => filter_var($request->get_param('free_only'), FILTER_VALIDATE_BOOLEAN),
            'indoor' => $request->get_param('indoor') !== null ? filter_var($request->get_param('indoor'), FILTER_VALIDATE_BOOLEAN) : null,
            'outdoor' => $request->get_param('outdoor') !== null ? filter_var($request->get_param('outdoor'), FILTER_VALIDATE_BOOLEAN) : null,
            'family_friendly' => $request->get_param('family_friendly') !== null ? filter_var($request->get_param('family_friendly'), FILTER_VALIDATE_BOOLEAN) : null,
            'age_min' => absint($request->get_param('age_min')),
            'age_max' => absint($request->get_param('age_max')),
            'orderby' => in_array($request->get_param('orderby'), array('date', 'price', 'rating', 'distance')) ? $request->get_param('orderby') : 'date',
            'order' => in_array(strtolower($request->get_param('order')), array('asc', 'desc')) ? strtolower($request->get_param('order')) : 'asc',
            'include_past' => filter_var($request->get_param('include_past'), FILTER_VALIDATE_BOOLEAN),
        );
    }

    /**
     * Validate alert/saved search data
     *
     * @param array $data
     * @return true|WP_Error
     */
    public static function validate_alert($data) {
        $errors = array();

        // Name: optional but if provided, min 2 chars, max 100
        if (!empty($data['name'])) {
            if (strlen($data['name']) < 2) {
                $errors['name'] = __('Le nom doit contenir au moins 2 caractères', 'lehiboo-mobile-api');
            } elseif (strlen($data['name']) > 100) {
                $errors['name'] = __('Le nom ne peut pas dépasser 100 caractères', 'lehiboo-mobile-api');
            }
        }

        // date_type: allowed values
        $valid_date_types = array('today', 'tomorrow', 'this_week', 'this_weekend', 'custom');
        if (!empty($data['date_type']) && !in_array($data['date_type'], $valid_date_types)) {
            $errors['date_type'] = __('Type de date invalide', 'lehiboo-mobile-api');
        }

        // price_type: allowed values
        $valid_price_types = array('free', 'paid');
        if (!empty($data['price_type']) && !in_array($data['price_type'], $valid_price_types)) {
            $errors['price_type'] = __('Type de prix invalide', 'lehiboo-mobile-api');
        }

        // categories/tags: must be array if provided
        if (isset($data['categories']) && !is_array($data['categories'])) {
            $errors['categories'] = __('Les catégories doivent être un tableau', 'lehiboo-mobile-api');
        }
        if (isset($data['tags']) && !is_array($data['tags'])) {
            $errors['tags'] = __('Les tags doivent être un tableau', 'lehiboo-mobile-api');
        }

        // At least one search criteria is required
        $has_criteria = !empty($data['search_query'])
            || !empty($data['city_slug'])
            || !empty($data['latitude'])
            || !empty($data['date_type'])
            || !empty($data['price_type'])
            || !empty($data['categories'])
            || !empty($data['tags'])
            || isset($data['is_family_friendly'])
            || isset($data['is_accessible_pmr'])
            || isset($data['is_online']);

        if (!$has_criteria) {
            $errors['criteria'] = __('Au moins un critère de recherche est requis', 'lehiboo-mobile-api');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                __('Erreur de validation', 'lehiboo-mobile-api'),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        return true;
    }
}
