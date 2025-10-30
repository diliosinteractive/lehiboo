<?php
/**
 * Security Handler
 * Gestion de toute la sécurité du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Security {

    /**
     * Dangerous patterns for XSS/injection detection
     */
    private $dangerous_patterns = array(
        '/<script/i',
        '/javascript:/i',
        '/onerror=/i',
        '/onclick=/i',
        '/<iframe/i',
        '/eval\(/i',
        '/document\.write/i',
        '/<object/i',
        '/<embed/i',
    );

    /**
     * Prompt injection patterns
     */
    private $prompt_injection_patterns = array(
        '/ignore (previous|all) (instructions|prompts)/i',
        '/you are now/i',
        '/forget (everything|all previous)/i',
        '/system prompt/i',
        '/\[SYSTEM\]/i',
        '/\[ADMIN\]/i',
        '/\[OVERRIDE\]/i',
        '/act as/i',
        '/pretend (to be|you are)/i',
        '/jailbreak/i',
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Add security headers
     */
    public function add_security_headers() {
        // Only add headers on our plugin pages
        if (!$this->is_plugin_request()) {
            return;
        }

        // Content Security Policy
        header("Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self'; " .
            "font-src 'self'; " .
            "frame-ancestors 'none';"
        );

        // Additional security headers
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }

    /**
     * Check if current request is for our plugin
     */
    private function is_plugin_request() {
        return (
            isset($_SERVER['REQUEST_URI']) &&
            strpos($_SERVER['REQUEST_URI'], 'lehiboo/v1') !== false
        );
    }

    /**
     * Check permission for chat endpoint
     */
    public function check_chat_permission($request) {
        // Verify nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'rest_forbidden',
                __('Nonce de sécurité invalide.', 'lehiboo-ai-assistant'),
                array('status' => 403)
            );
        }

        // Check if plugin is enabled
        if (get_option('lehiboo_ai_enabled') !== 'yes') {
            return new WP_Error(
                'rest_forbidden',
                __('L\'assistant IA est désactivé.', 'lehiboo-ai-assistant'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Sanitize message input
     */
    public function sanitize_message($message) {
        if (!is_string($message)) {
            return '';
        }

        // Trim whitespace
        $message = trim($message);

        // Sanitize text field (removes scripts, etc.)
        $message = sanitize_text_field($message);

        // Additional sanitization
        $message = wp_kses($message, array());

        return $message;
    }

    /**
     * Validate message
     */
    public function validate_message($message) {
        // Empty check
        if (empty($message) || strlen($message) === 0) {
            return new WP_Error(
                'empty_message',
                __('Le message ne peut pas être vide.', 'lehiboo-ai-assistant')
            );
        }

        // Length check
        $max_length = intval(get_option('lehiboo_ai_max_message_length', 2000));
        if (strlen($message) > $max_length) {
            return new WP_Error(
                'message_too_long',
                sprintf(
                    __('Le message est trop long (maximum %d caractères).', 'lehiboo-ai-assistant'),
                    $max_length
                )
            );
        }

        // Check for dangerous content
        foreach ($this->dangerous_patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                $this->log_security_event('xss_attempt', array(
                    'message' => substr($message, 0, 100),
                    'pattern' => $pattern,
                    'ip' => $this->get_client_ip(),
                ));

                return new WP_Error(
                    'suspicious_content',
                    __('Contenu non autorisé détecté.', 'lehiboo-ai-assistant')
                );
            }
        }

        // Check for prompt injection
        $injection_check = $this->detect_prompt_injection($message);
        if ($injection_check['detected']) {
            $this->log_security_event('prompt_injection_attempt', array(
                'message' => substr($message, 0, 100),
                'pattern' => $injection_check['pattern'],
                'ip' => $this->get_client_ip(),
            ));

            return new WP_Error(
                'prompt_injection',
                __('Message non autorisé.', 'lehiboo-ai-assistant')
            );
        }

        return true;
    }

    /**
     * Detect prompt injection attempts
     */
    public function detect_prompt_injection($message) {
        foreach ($this->prompt_injection_patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return array(
                    'detected' => true,
                    'pattern' => $pattern,
                );
            }
        }

        return array('detected' => false);
    }

    /**
     * Validate conversation data
     */
    public function validate_conversation_data($data) {
        $errors = array();

        // Validate conversation ID
        if (isset($data['conversationId'])) {
            if (!preg_match('/^conv_[a-z0-9_]+$/', $data['conversationId'])) {
                $errors[] = 'Invalid conversation ID format';
            }
        }

        // Validate user context
        if (isset($data['userContext'])) {
            if (!is_array($data['userContext'])) {
                $errors[] = 'User context must be an array';
            }

            // Validate age
            if (isset($data['userContext']['age'])) {
                $age = intval($data['userContext']['age']);
                if ($age < 1 || $age > 120) {
                    $errors[] = 'Invalid age';
                }
            }

            // Validate group type
            if (isset($data['userContext']['groupType'])) {
                $allowed = array('solo', 'couple', 'family', 'friends', 'group');
                if (!in_array($data['userContext']['groupType'], $allowed)) {
                    $errors[] = 'Invalid group type';
                }
            }
        }

        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode(', ', $errors));
        }

        return true;
    }

    /**
     * Sanitize conversation data
     */
    public function sanitize_conversation_data($data) {
        $sanitized = array();

        if (isset($data['conversationId'])) {
            $sanitized['conversationId'] = sanitize_text_field($data['conversationId']);
        }

        if (isset($data['userContext']) && is_array($data['userContext'])) {
            $sanitized['userContext'] = array();

            if (isset($data['userContext']['age'])) {
                $sanitized['userContext']['age'] = absint($data['userContext']['age']);
            }

            if (isset($data['userContext']['groupType'])) {
                $sanitized['userContext']['groupType'] = sanitize_text_field($data['userContext']['groupType']);
            }

            if (isset($data['userContext']['budget'])) {
                $sanitized['userContext']['budget'] = sanitize_text_field($data['userContext']['budget']);
            }

            if (isset($data['userContext']['interests']) && is_array($data['userContext']['interests'])) {
                $sanitized['userContext']['interests'] = array_map('sanitize_text_field', $data['userContext']['interests']);
            }
        }

        if (isset($data['currentStage'])) {
            $sanitized['currentStage'] = sanitize_text_field($data['currentStage']);
        }

        // ✅ CRITICAL: Sanitize history array (conversation messages)
        if (isset($data['history']) && is_array($data['history'])) {
            $sanitized['history'] = array();
            foreach ($data['history'] as $message) {
                if (is_array($message) && isset($message['role']) && isset($message['content'])) {
                    $sanitized['history'][] = array(
                        'role' => sanitize_text_field($message['role']),
                        'content' => sanitize_textarea_field($message['content']),
                    );
                }
            }
        }

        return $sanitized;
    }

    /**
     * Get client IP (safely)
     */
    public function get_client_ip() {
        $ip = '';

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            // Cloudflare
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
    }

    /**
     * Log security event
     */
    private function log_security_event($event_type, $data = array()) {
        // Log to error_log
        error_log(sprintf(
            '[Lehiboo AI Security] %s - IP: %s - Data: %s',
            $event_type,
            isset($data['ip']) ? $data['ip'] : 'unknown',
            json_encode($data)
        ));

        // Could also save to database for audit trail
        do_action('lehiboo_ai_security_event', $event_type, $data);
    }

    /**
     * Check if IP is blocked
     */
    public function is_ip_blocked($ip) {
        // Check transient for blocked IPs
        $blocked_ips = get_transient('lehiboo_ai_blocked_ips');
        if (!$blocked_ips) {
            $blocked_ips = array();
        }

        return in_array($ip, $blocked_ips);
    }

    /**
     * Block IP temporarily
     */
    public function block_ip($ip, $duration = 3600) {
        $blocked_ips = get_transient('lehiboo_ai_blocked_ips');
        if (!$blocked_ips) {
            $blocked_ips = array();
        }

        if (!in_array($ip, $blocked_ips)) {
            $blocked_ips[] = $ip;
            set_transient('lehiboo_ai_blocked_ips', $blocked_ips, $duration);

            $this->log_security_event('ip_blocked', array(
                'ip' => $ip,
                'duration' => $duration,
            ));
        }
    }

    /**
     * Escape output for safe display
     */
    public function escape_output($content) {
        return wp_kses_post($content);
    }
}
