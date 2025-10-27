<?php
/**
 * Prompt Manager
 * Gestion et chargement des prompts YAML
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Prompt_Manager {

    /**
     * Prompts directory
     */
    private $prompts_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->prompts_dir = LEHIBOO_AI_PLUGIN_DIR . 'prompts/';
    }

    /**
     * Get system prompt
     */
    public function get_system_prompt() {
        $file = $this->prompts_dir . 'system-prompt.yaml';
        return $this->load_yaml_file($file);
    }

    /**
     * Get specialized prompt
     */
    public function get_specialized_prompt($type) {
        $file = $this->prompts_dir . 'specialized/' . $type . '.yaml';
        return $this->load_yaml_file($file);
    }

    /**
     * Load YAML file
     * Note: Requires PHP YAML extension or fallback parser
     */
    private function load_yaml_file($file) {
        if (!file_exists($file)) {
            return array();
        }

        // Check if YAML extension is available
        if (function_exists('yaml_parse_file')) {
            return yaml_parse_file($file);
        }

        // Fallback: Basic YAML parser (simple key:value only)
        return $this->simple_yaml_parse($file);
    }

    /**
     * Simple YAML parser (fallback)
     * Only handles basic key: value structure
     */
    private function simple_yaml_parse($file) {
        $content = file_get_contents($file);
        if ($content === false) {
            return array();
        }

        $lines = explode("\n", $content);
        $result = array();
        $current_key = null;
        $current_value = '';
        $in_multiline = false;

        foreach ($lines as $line) {
            // Skip comments and empty lines
            if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
                continue;
            }

            // Check for key: value
            if (preg_match('/^(\s*)([a-zA-Z0-9_]+):\s*(.*)$/', $line, $matches)) {
                // Save previous key if in multiline
                if ($in_multiline && $current_key) {
                    $result[$current_key] = trim($current_value);
                }

                $key = $matches[2];
                $value = $matches[3];

                if (empty($value) || $value === '|') {
                    // Start multiline value
                    $current_key = $key;
                    $current_value = '';
                    $in_multiline = true;
                } else {
                    // Single line value
                    $result[$key] = $value;
                    $in_multiline = false;
                }
            } elseif ($in_multiline) {
                // Continuation of multiline value
                $current_value .= $line . "\n";
            }
        }

        // Save last multiline value
        if ($in_multiline && $current_key) {
            $result[$current_key] = trim($current_value);
        }

        return $result;
    }

    /**
     * Get prompt for conversation stage
     */
    public function get_stage_prompt($stage) {
        $system_prompt = $this->get_system_prompt();

        if (isset($system_prompt['conversation_stages'][$stage])) {
            return $system_prompt['conversation_stages'][$stage];
        }

        return null;
    }

    /**
     * Build full prompt for AI
     */
    public function build_ai_prompt($stage, $user_context = array()) {
        $system_prompt = $this->get_system_prompt();

        $prompt = '';

        // Add identity
        if (isset($system_prompt['identity'])) {
            $prompt .= $system_prompt['identity']['personality'] . "\n\n";
        }

        // Add security rules
        if (isset($system_prompt['security_rules'])) {
            $prompt .= "RÈGLES DE SÉCURITÉ :\n";
            $prompt .= $system_prompt['security_rules']['core_restrictions'] . "\n\n";
        }

        // Add mission
        if (isset($system_prompt['mission'])) {
            $prompt .= "MISSION :\n";
            $prompt .= $system_prompt['mission'] . "\n\n";
        }

        // Add stage-specific instructions
        $stage_prompt = $this->get_stage_prompt($stage);
        if ($stage_prompt && isset($stage_prompt['prompt'])) {
            $prompt .= "ÉTAPE ACTUELLE (" . strtoupper($stage) . ") :\n";
            $prompt .= $stage_prompt['prompt'] . "\n\n";
        }

        // Add user context if available
        if (!empty($user_context)) {
            $prompt .= "CONTEXTE UTILISATEUR :\n";
            foreach ($user_context as $key => $value) {
                if (!empty($value)) {
                    $prompt .= "- " . ucfirst($key) . ": " . $value . "\n";
                }
            }
            $prompt .= "\n";
        }

        return $prompt;
    }
}
