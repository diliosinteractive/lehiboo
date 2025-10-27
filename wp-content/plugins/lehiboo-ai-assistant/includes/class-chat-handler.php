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
        // Demo mode - return canned response based on conversation flow
        $stage = isset($data['currentStage']) ? $data['currentStage'] : 'greeting';
        $user_context = isset($data['userContext']) ? $data['userContext'] : array();

        $message_lower = strtolower($message);

        // Detect user intent from message
        if (preg_match('/\b(solo|seul|moi)\b/i', $message_lower)) {
            $user_context['groupType'] = 'solo';
            $stage = 'age_collection';
        } elseif (preg_match('/\b(couple|deux|amoureux)\b/i', $message_lower)) {
            $user_context['groupType'] = 'couple';
            $stage = 'age_collection';
        } elseif (preg_match('/\b(famille|enfant|kids)\b/i', $message_lower)) {
            $user_context['groupType'] = 'famille';
            $stage = 'age_collection';
        } elseif (preg_match('/\b(amis|groupe|potes)\b/i', $message_lower)) {
            $user_context['groupType'] = 'amis';
            $stage = 'age_collection';
        }

        // Detect age
        if (preg_match('/\b(\d{1,2})\s*(ans?|years?)\b/i', $message_lower, $matches)) {
            $user_context['age'] = intval($matches[1]);
            $stage = 'dates_weather';
        } elseif (preg_match('/\b(\d{1,2})\s*-\s*(\d{1,2})\b/', $message_lower, $matches)) {
            $user_context['ageRange'] = $matches[1] . '-' . $matches[2];
            $stage = 'dates_weather';
        }

        // Detect dates
        if (preg_match('/\b(weekend|samedi|dimanche|week-end)\b/i', $message_lower)) {
            $user_context['dates'] = 'ce-weekend';
            $stage = 'preferences';
        } elseif (preg_match('/\b(prochain|suivant)\b/i', $message_lower)) {
            $user_context['dates'] = 'weekend-prochain';
            $stage = 'preferences';
        }

        // Detect interests
        if (preg_match('/\b(sport|sportif|actif)\b/i', $message_lower)) {
            $user_context['interests'] = array('sport');
            $stage = 'recommendations';
        } elseif (preg_match('/\b(culture|culturel|musée|art)\b/i', $message_lower)) {
            $user_context['interests'] = array('culture');
            $stage = 'recommendations';
        } elseif (preg_match('/\b(gastronomie|restaurant|manger|cuisine)\b/i', $message_lower)) {
            $user_context['interests'] = array('gastronomie');
            $stage = 'recommendations';
        }

        // Demo banner for all responses
        $demo_notice = '<div style="background: #FFF3CD; border: 1px solid #FFE69C; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px;">' .
                      '⚠️ <strong>Mode Démo</strong> : Réponses simulées. ' .
                      '<a href="/wp-admin/admin.php?page=lehiboo-ai-settings" style="color: #FF601F; text-decoration: underline;">Configurez l\'API</a> pour activer l\'IA réelle.' .
                      '</div>';

        switch ($stage) {
            case 'greeting':
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "Bonjour ! Je suis l'assistant Le Hiboo 👋<br><br>" .
                                "Je vais vous aider à trouver l'activité parfaite.<br><br>" .
                                "Pour commencer, vous cherchez une activité pour :",
                    'conversationStage' => 'group_type',
                    'userContext' => $user_context,
                    'quickChips' => array(
                        array('text' => '🧍 Solo', 'value' => 'solo'),
                        array('text' => '💑 En couple', 'value' => 'couple'),
                        array('text' => '👨‍👩‍👧 En famille', 'value' => 'famille'),
                        array('text' => '👥 Entre amis', 'value' => 'amis'),
                    ),
                );

            case 'age_collection':
                $group_label = $this->get_group_label($user_context);
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "Super ! Une activité <strong>{$group_label}</strong> 👍<br><br>" .
                                "Pour vous proposer des activités adaptées, quel âge avez-vous ?",
                    'conversationStage' => 'age_collection',
                    'userContext' => $user_context,
                    'quickChips' => array(
                        array('text' => '18-25 ans', 'value' => '18-25'),
                        array('text' => '25-35 ans', 'value' => '25-35'),
                        array('text' => '35-50 ans', 'value' => '35-50'),
                        array('text' => '50+ ans', 'value' => '50+'),
                    ),
                );

            case 'dates_weather':
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "Parfait ! 😊<br><br>" .
                                "Quand souhaitez-vous faire cette activité ?",
                    'conversationStage' => 'dates_weather',
                    'userContext' => $user_context,
                    'quickChips' => array(
                        array('text' => '📅 Ce week-end', 'value' => 'ce-weekend'),
                        array('text' => '📅 Week-end prochain', 'value' => 'weekend-prochain'),
                        array('text' => '📅 Dates précises', 'value' => 'dates-precises'),
                        array('text' => '🤷 Flexible', 'value' => 'flexible'),
                    ),
                    'weatherAlert' => array(
                        'icon' => '🌤️',
                        'message' => 'Météo prévue : Partiellement nuageux (démo)',
                    ),
                );

            case 'preferences':
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "Excellent ! Dernières questions pour affiner mes recommandations :<br><br>" .
                                "Quel type d'activité vous intéresse ?",
                    'conversationStage' => 'preferences',
                    'userContext' => $user_context,
                    'quickChips' => array(
                        array('text' => '⚽ Sportif', 'value' => 'sport'),
                        array('text' => '🎨 Culturel', 'value' => 'culture'),
                        array('text' => '🍽️ Gastronomie', 'value' => 'gastronomie'),
                        array('text' => '🌳 Nature', 'value' => 'nature'),
                        array('text' => '🧘 Détente', 'value' => 'detente'),
                    ),
                );

            case 'recommendations':
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "🔍 Parfait ! Voici mes meilleures recommandations pour vous :",
                    'conversationStage' => 'recommendations',
                    'userContext' => $user_context,
                    'events' => $this->get_demo_events($user_context),
                    'quickChips' => array(
                        array('text' => '🔄 Modifier critères', 'value' => 'modifier'),
                        array('text' => '📦 Créer un package', 'value' => 'package'),
                    ),
                );

            default:
                return array(
                    'success' => true,
                    'message' => $demo_notice .
                                "Merci pour votre message ! En mode démo, je peux simuler une conversation basique.<br><br>" .
                                "Essayez de me dire :<br>" .
                                "• \"Je cherche une activité en couple\"<br>" .
                                "• \"J'ai 30 ans\"<br>" .
                                "• \"Ce week-end\"<br>" .
                                "• \"Quelque chose de sportif\"",
                    'conversationStage' => $stage,
                    'userContext' => $user_context,
                );
        }
    }

    /**
     * Get group type label
     */
    private function get_group_label($user_context) {
        if (!isset($user_context['groupType'])) {
            return 'pour vous';
        }

        $labels = array(
            'solo' => 'solo',
            'couple' => 'en couple',
            'famille' => 'en famille',
            'amis' => 'entre amis',
        );

        return isset($labels[$user_context['groupType']])
            ? $labels[$user_context['groupType']]
            : 'pour vous';
    }

    /**
     * Get demo events based on user context
     */
    private function get_demo_events($user_context) {
        $interests = isset($user_context['interests']) ? $user_context['interests'] : array();
        $interest = !empty($interests) ? $interests[0] : 'general';

        // Demo events based on interest
        $events_by_interest = array(
            'sport' => array(
                array(
                    'id' => 'demo-1',
                    'title' => 'Escalade Indoor',
                    'image' => 'https://images.unsplash.com/photo-1522163182402-834f871fd851?w=400',
                    'price' => '35€/pers',
                    'date' => 'Samedi 10h-13h',
                    'location' => 'Zone Nord (12 min)',
                    'duration' => '3h',
                    'rating' => '4.9',
                    'reviews' => '234',
                    'badges' => array(
                        array('type' => 'indoor', 'icon' => '🏠', 'text' => 'Indoor'),
                        array('type' => 'sport', 'icon' => '💪', 'text' => 'Actif'),
                    ),
                ),
            ),
            'culture' => array(
                array(
                    'id' => 'demo-2',
                    'title' => 'Visite Musée d\'Art Moderne',
                    'image' => 'https://images.unsplash.com/photo-1499781350541-7783f6c6a0c8?w=400',
                    'price' => '12€/pers',
                    'date' => 'Dimanche 14h-17h',
                    'location' => 'Centre-ville (5 min)',
                    'duration' => '3h',
                    'rating' => '4.7',
                    'reviews' => '189',
                    'badges' => array(
                        array('type' => 'culture', 'icon' => '🎨', 'text' => 'Culturel'),
                        array('type' => 'family', 'icon' => '👨‍👩‍👧', 'text' => 'Famille'),
                    ),
                ),
            ),
            'gastronomie' => array(
                array(
                    'id' => 'demo-3',
                    'title' => 'Atelier Cuisine Italienne',
                    'image' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=400',
                    'price' => '55€/pers',
                    'date' => 'Samedi 18h-21h',
                    'location' => 'Centre-ville (8 min)',
                    'duration' => '3h',
                    'rating' => '4.8',
                    'reviews' => '156',
                    'badges' => array(
                        array('type' => 'food', 'icon' => '🍝', 'text' => 'Gastronomie'),
                        array('type' => 'indoor', 'icon' => '🏠', 'text' => 'Indoor'),
                    ),
                ),
            ),
        );

        // Return events for interest, or default sport event
        $events = isset($events_by_interest[$interest])
            ? $events_by_interest[$interest]
            : $events_by_interest['sport'];

        // Add 2 more generic events
        $events[] = array(
            'id' => 'demo-4',
            'title' => 'Atelier Poterie',
            'image' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?w=400',
            'price' => '45€/pers',
            'date' => 'Samedi 14h-16h30',
            'location' => 'Centre-ville (8 min)',
            'duration' => '2h30',
            'rating' => '4.8',
            'reviews' => '127',
            'badges' => array(
                array('type' => 'art', 'icon' => '🎨', 'text' => 'Créatif'),
                array('type' => 'relax', 'icon' => '😌', 'text' => 'Détente'),
            ),
        );

        $events[] = array(
            'id' => 'demo-5',
            'title' => 'Dégustation Vins & Fromages',
            'image' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400',
            'price' => '60€/pers',
            'date' => 'Dimanche 16h-18h',
            'location' => 'Périphérie (15 min)',
            'duration' => '2h',
            'rating' => '4.7',
            'reviews' => '89',
            'badges' => array(
                array('type' => 'food', 'icon' => '🍷', 'text' => 'Œnologie'),
                array('type' => 'age', 'icon' => '🔞', 'text' => '18+'),
            ),
        );

        return $events;
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
