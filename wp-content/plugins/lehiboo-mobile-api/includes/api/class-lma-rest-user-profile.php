<?php
/**
 * REST User Profile Controller
 * Gestion du profil intelligent (Smart Context) pour l'IA
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_User_Profile {

    /**
     * Namespace
     */
    protected $namespace = 'lehiboo/v2';

    /**
     * Meta key pour stocker le profil
     */
    const PROFILE_META_KEY = 'lma_ai_profile';

    /**
     * Register routes
     */
    public function register_routes() {
        // Get user AI profile
        register_rest_route($this->namespace, '/user/ai-profile', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_profile'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Update user AI profile
        register_rest_route($this->namespace, '/user/ai-profile', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_profile'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));

        // Reset user AI profile
        register_rest_route($this->namespace, '/user/ai-profile', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'reset_profile'),
            'permission_callback' => array('LMA_JWT_Handler', 'authenticate'),
        ));
    }

    /**
     * Get user AI profile
     */
    public function get_profile($request) {
        $user = wp_get_current_user();

        if (!$user || !$user->ID) {
            return LMA_Response::error(
                'unauthorized',
                __('Authentification requise', 'lehiboo-mobile-api'),
                401
            );
        }

        $profile = get_user_meta($user->ID, self::PROFILE_META_KEY, true);

        // Si pas de profil, retourner un profil vide
        if (empty($profile)) {
            $profile = $this->get_empty_profile($user->ID);
        }

        return LMA_Response::success(array(
            'user_context' => $profile,
        ));
    }

    /**
     * Update user AI profile
     */
    public function update_profile($request) {
        $user = wp_get_current_user();

        if (!$user || !$user->ID) {
            return LMA_Response::error(
                'unauthorized',
                __('Authentification requise', 'lehiboo-mobile-api'),
                401
            );
        }

        $new_context = $request->get_json_params();

        if (empty($new_context) || !isset($new_context['user_context'])) {
            return LMA_Response::error(
                'invalid_data',
                __('Données invalides', 'lehiboo-mobile-api'),
                400
            );
        }

        $user_context = $new_context['user_context'];

        // Valider la structure
        $validated = $this->validate_profile($user_context);

        if (is_wp_error($validated)) {
            return LMA_Response::from_error($validated);
        }

        // Ajouter les métadonnées
        $validated['userId'] = $user->ID;
        $validated['lastUpdated'] = current_time('c');

        // Sauvegarder
        $result = update_user_meta($user->ID, self::PROFILE_META_KEY, $validated);

        if ($result === false) {
            return LMA_Response::error(
                'save_failed',
                __('Erreur lors de la sauvegarde', 'lehiboo-mobile-api'),
                500
            );
        }

        return LMA_Response::success(array(
            'user_context' => $validated,
            'message' => __('Profil mis à jour', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Reset user AI profile
     */
    public function reset_profile($request) {
        $user = wp_get_current_user();

        if (!$user || !$user->ID) {
            return LMA_Response::error(
                'unauthorized',
                __('Authentification requise', 'lehiboo-mobile-api'),
                401
            );
        }

        $empty_profile = $this->get_empty_profile($user->ID);
        update_user_meta($user->ID, self::PROFILE_META_KEY, $empty_profile);

        return LMA_Response::success(array(
            'user_context' => $empty_profile,
            'message' => __('Profil réinitialisé', 'lehiboo-mobile-api'),
        ));
    }

    /**
     * Get empty profile structure
     */
    private function get_empty_profile($user_id) {
        return array(
            'version' => 1,
            'userId' => $user_id,
            'currentSearch' => new stdClass(),
            'preferences' => array(
                'likes' => array(),
                'dislikes' => array(),
                'dietaryRestrictions' => array(),
                'accessibility' => array(),
                'favoriteCities' => array(),
                'favoriteCategories' => array(),
                'typicalBudget' => null,
                'typicalGroupType' => null,
            ),
            'insights' => array(
                'totalSearches' => 0,
                'recentSearches' => array(),
                'topCategories' => new stdClass(),
                'topCities' => new stdClass(),
                'averageBudget' => null,
                'firstInteraction' => null,
                'lastInteraction' => null,
            ),
            'lastUpdated' => current_time('c'),
        );
    }

    /**
     * Validate profile structure
     */
    private function validate_profile($profile) {
        // Version
        $validated = array(
            'version' => isset($profile['version']) ? intval($profile['version']) : 1,
        );

        // Current Search (éphémère, on accepte tel quel)
        $validated['currentSearch'] = isset($profile['currentSearch']) ? $profile['currentSearch'] : new stdClass();

        // Preferences
        $validated['preferences'] = array(
            'likes' => $this->sanitize_array($profile['preferences']['likes'] ?? array()),
            'dislikes' => $this->sanitize_array($profile['preferences']['dislikes'] ?? array()),
            'dietaryRestrictions' => $this->sanitize_array($profile['preferences']['dietaryRestrictions'] ?? array()),
            'accessibility' => $this->sanitize_array($profile['preferences']['accessibility'] ?? array()),
            'favoriteCities' => $this->sanitize_array($profile['preferences']['favoriteCities'] ?? array()),
            'favoriteCategories' => $this->sanitize_array($profile['preferences']['favoriteCategories'] ?? array()),
            'typicalBudget' => isset($profile['preferences']['typicalBudget']) ? floatval($profile['preferences']['typicalBudget']) : null,
            'typicalGroupType' => isset($profile['preferences']['typicalGroupType']) ? sanitize_text_field($profile['preferences']['typicalGroupType']) : null,
        );

        // Insights
        $validated['insights'] = array(
            'totalSearches' => isset($profile['insights']['totalSearches']) ? intval($profile['insights']['totalSearches']) : 0,
            'recentSearches' => $this->sanitize_recent_searches($profile['insights']['recentSearches'] ?? array()),
            'topCategories' => isset($profile['insights']['topCategories']) ? (object) $profile['insights']['topCategories'] : new stdClass(),
            'topCities' => isset($profile['insights']['topCities']) ? (object) $profile['insights']['topCities'] : new stdClass(),
            'averageBudget' => isset($profile['insights']['averageBudget']) ? floatval($profile['insights']['averageBudget']) : null,
            'firstInteraction' => isset($profile['insights']['firstInteraction']) ? sanitize_text_field($profile['insights']['firstInteraction']) : null,
            'lastInteraction' => isset($profile['insights']['lastInteraction']) ? sanitize_text_field($profile['insights']['lastInteraction']) : null,
        );

        return $validated;
    }

    /**
     * Sanitize array of strings
     */
    private function sanitize_array($arr) {
        if (!is_array($arr)) {
            return array();
        }

        return array_values(array_filter(array_map('sanitize_text_field', $arr)));
    }

    /**
     * Sanitize recent searches
     */
    private function sanitize_recent_searches($searches) {
        if (!is_array($searches)) {
            return array();
        }

        $sanitized = array();
        foreach (array_slice($searches, 0, 10) as $search) {
            if (is_array($search)) {
                $sanitized[] = array(
                    'query' => sanitize_text_field($search['query'] ?? ''),
                    'category' => sanitize_text_field($search['category'] ?? ''),
                    'city' => sanitize_text_field($search['city'] ?? ''),
                    'date' => sanitize_text_field($search['date'] ?? ''),
                );
            }
        }

        return $sanitized;
    }
}
