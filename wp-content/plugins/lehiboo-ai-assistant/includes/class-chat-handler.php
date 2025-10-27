<?php
/**
 * Chat Handler
 * Gestion des requêtes chat et communication avec l'IA
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Chat_Handler {

    /**
     * Handle chat request from REST API
     */
    public function handle_chat_request($request) {
        // Get security and rate limiter instances
        $security = new Lehiboo_AI_Security();
        $rate_limiter = new Lehiboo_AI_Rate_Limiter();

        // Check rate limit
        $rate_check = $rate_limiter->enforce_limit();
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        // Get parameters
        $message = $request->get_param('message');
        $conversation_id = $request->get_param('conversationId');
        $user_context = $request->get_param('userContext');
        $current_stage = $request->get_param('currentStage');

        // Validate message
        if (empty($message)) {
            return new WP_Error(
                'empty_message',
                __('Le message ne peut pas être vide.', 'lehiboo-ai-assistant'),
                array('status' => 400)
            );
        }

        // Sanitize message
        $message = $security->sanitize_message($message);

        // Validate message
        $validation = $security->validate_message($message);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Sanitize conversation data
        $data = array(
            'conversationId' => $conversation_id,
            'userContext' => $user_context,
            'currentStage' => $current_stage,
        );
        $sanitized_data = $security->sanitize_conversation_data($data);

        // Validate conversation data
        $validation = $security->validate_conversation_data($sanitized_data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Call backend API
        $response = $this->call_backend_api($message, $sanitized_data);

        if (is_wp_error($response)) {
            return $response;
        }

        // Track conversation analytics (anonymized)
        $this->track_conversation($sanitized_data, $response);

        // Return response
        return rest_ensure_response($response);
    }

    /**
     * Call backend API (Node.js server)
     */
    private function call_backend_api($message, $data) {
        // Get backend URL from settings
        $backend_url = get_option('lehiboo_ai_backend_url');

        if (empty($backend_url)) {
            // Backend not configured yet - return demo response
            return $this->get_demo_response($message, $data);
        }

        // Get API key
        $api_key = get_option('lehiboo_ai_api_key');

        // Prepare request
        $body = array(
            'message' => $message,
            'conversationId' => $data['conversationId'],
            'userContext' => $data['userContext'],
            'currentStage' => $data['currentStage'],
        );

        // Call backend
        $response = wp_remote_post($backend_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => !empty($api_key) ? 'Bearer ' . $api_key : '',
            ),
            'body' => json_encode($body),
            'timeout' => 30,
        ));

        // Check for errors
        if (is_wp_error($response)) {
            error_log('[Lehiboo AI] Backend API error: ' . $response->get_error_message());
            return new WP_Error(
                'backend_error',
                __('Impossible de se connecter au serveur IA. Veuillez réessayer.', 'lehiboo-ai-assistant'),
                array('status' => 503)
            );
        }

        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $parsed = json_decode($response_body, true);

        if ($response_code !== 200 || empty($parsed)) {
            error_log('[Lehiboo AI] Backend API returned error: ' . $response_code);
            return new WP_Error(
                'backend_error',
                __('Le serveur IA a rencontré une erreur.', 'lehiboo-ai-assistant'),
                array('status' => 503)
            );
        }

        return $parsed;
    }

    /**
     * Get demo response (when backend not configured)
     */
    private function get_demo_response($message, $data) {
        // Demo mode - return canned response
        $stage = isset($data['currentStage']) ? $data['currentStage'] : 'greeting';

        switch ($stage) {
            case 'greeting':
                return array(
                    'success' => true,
                    'message' => "Bonjour ! Je suis l'assistant Le Hiboo 👋<br><br>" .
                                "Je vais vous aider à trouver l'activité parfaite.<br><br>" .
                                "<strong>⚠️ Mode démo</strong> : Le backend IA n'est pas encore configuré. " .
                                "Consultez <a href='/wp-admin/admin.php?page=lehiboo-ai-settings'>les paramètres</a> pour configurer l'API.",
                    'conversationStage' => 'demo',
                    'quickChips' => array(
                        array('text' => '🧍 Solo', 'value' => 'solo'),
                        array('text' => '💑 En couple', 'value' => 'couple'),
                        array('text' => '👨‍👩‍👧 En famille', 'value' => 'famille'),
                    ),
                );

            default:
                return array(
                    'success' => true,
                    'message' => "Merci pour votre message ! <br><br>" .
                                "<strong>⚠️ Mode démo</strong> : Pour activer l'intelligence artificielle, " .
                                "veuillez configurer le backend dans les paramètres du plugin.",
                    'conversationStage' => 'demo',
                );
        }
    }

    /**
     * Track conversation analytics (anonymized)
     */
    private function track_conversation($data, $response) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'lehiboo_conversations';

        // Check if conversation exists
        $conversation_id = isset($data['conversationId']) ? $data['conversationId'] : '';
        if (empty($conversation_id)) {
            return;
        }

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE conversation_id = %s",
            $conversation_id
        ));

        $user_context = isset($data['userContext']) ? $data['userContext'] : array();

        // Anonymize age (range instead of exact)
        $age_range = null;
        if (isset($user_context['age'])) {
            $age = intval($user_context['age']);
            if ($age < 18) $age_range = '0-18';
            elseif ($age < 25) $age_range = '18-25';
            elseif ($age < 35) $age_range = '25-35';
            elseif ($age < 50) $age_range = '35-50';
            elseif ($age < 65) $age_range = '50-65';
            else $age_range = '65+';
        }

        if (!$existing) {
            // Insert new conversation
            $wpdb->insert(
                $table_name,
                array(
                    'conversation_id' => $conversation_id,
                    'age_range' => $age_range,
                    'group_type' => isset($user_context['groupType']) ? $user_context['groupType'] : null,
                    'budget_range' => isset($user_context['budget']) ? $user_context['budget'] : null,
                    'interests' => isset($user_context['interests']) ? json_encode($user_context['interests']) : null,
                    'stage_reached' => isset($data['currentStage']) ? $data['currentStage'] : null,
                    'messages_count' => 1,
                    'created_at' => current_time('mysql'),
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
            );
        } else {
            // Update existing conversation
            $updates = array(
                'messages_count' => $existing->messages_count + 1,
            );

            if ($age_range) {
                $updates['age_range'] = $age_range;
            }

            if (isset($user_context['groupType'])) {
                $updates['group_type'] = $user_context['groupType'];
            }

            if (isset($data['currentStage'])) {
                $updates['stage_reached'] = $data['currentStage'];
            }

            $wpdb->update(
                $table_name,
                $updates,
                array('conversation_id' => $conversation_id),
                array_fill(0, count($updates), '%s'),
                array('%s')
            );
        }
    }
}
