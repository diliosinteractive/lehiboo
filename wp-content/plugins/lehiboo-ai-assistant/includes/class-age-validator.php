<?php
/**
 * Age Validator
 * Validation d'âge et filtrage événements selon restrictions
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lehiboo_AI_Age_Validator {

    /**
     * Validate age
     */
    public function validate_age($age) {
        $age = intval($age);

        if ($age < 1 || $age > 120) {
            return new WP_Error(
                'invalid_age',
                __('L\'âge doit être entre 1 et 120 ans.', 'lehiboo-ai-assistant')
            );
        }

        return true;
    }

    /**
     * Validate children ages
     */
    public function validate_children_ages($ages) {
        if (!is_array($ages)) {
            return new WP_Error(
                'invalid_children_ages',
                __('Les âges des enfants doivent être fournis sous forme de liste.', 'lehiboo-ai-assistant')
            );
        }

        foreach ($ages as $age) {
            $age = intval($age);
            if ($age < 0 || $age > 18) {
                return new WP_Error(
                    'invalid_child_age',
                    __('L\'âge de chaque enfant doit être entre 0 et 18 ans.', 'lehiboo-ai-assistant')
                );
            }
        }

        return true;
    }

    /**
     * Filter events by age restrictions
     */
    public function filter_events_by_age($events, $user_age, $has_children = false, $children_ages = array()) {
        if (empty($events)) {
            return array();
        }

        $filtered = array();

        foreach ($events as $event) {
            // Check minimum age requirement
            $min_age = get_post_meta($event->ID, '_event_min_age', true);
            if (!empty($min_age) && $user_age < intval($min_age)) {
                continue; // Skip - user too young
            }

            // Check adult only (18+)
            $adult_only = get_post_meta($event->ID, '_event_adult_only', true);
            if ($adult_only && $user_age < 18) {
                continue; // Skip - adults only
            }

            // Check family-friendly if with children
            if ($has_children) {
                $family_friendly = get_post_meta($event->ID, '_event_family_friendly', true);
                if (!$family_friendly) {
                    continue; // Skip - not family-friendly
                }

                // Check minimum child age if specified
                if (!empty($children_ages)) {
                    $min_child_age = get_post_meta($event->ID, '_event_min_child_age', true);
                    if (!empty($min_child_age)) {
                        $youngest = min($children_ages);
                        if ($youngest < intval($min_child_age)) {
                            continue; // Skip - children too young
                        }
                    }
                }
            }

            // Event passed all age restrictions
            $filtered[] = $event;
        }

        return $filtered;
    }

    /**
     * Get age range label
     */
    public function get_age_range($age) {
        $age = intval($age);

        if ($age < 18) return '0-18';
        if ($age < 25) return '18-25';
        if ($age < 35) return '25-35';
        if ($age < 50) return '35-50';
        if ($age < 65) return '50-65';
        return '65+';
    }

    /**
     * Check if event has age restrictions
     */
    public function has_age_restrictions($event_id) {
        $min_age = get_post_meta($event_id, '_event_min_age', true);
        $adult_only = get_post_meta($event_id, '_event_adult_only', true);

        return !empty($min_age) || $adult_only;
    }

    /**
     * Get age restriction badge for event
     */
    public function get_age_badge($event_id) {
        $adult_only = get_post_meta($event_id, '_event_adult_only', true);
        if ($adult_only) {
            return array(
                'type' => 'warning',
                'icon' => '🔞',
                'text' => '18+ uniquement',
            );
        }

        $min_age = get_post_meta($event_id, '_event_min_age', true);
        if (!empty($min_age)) {
            return array(
                'type' => 'info',
                'icon' => '👧',
                'text' => 'Dès ' . intval($min_age) . ' ans',
            );
        }

        $family_friendly = get_post_meta($event_id, '_event_family_friendly', true);
        if ($family_friendly) {
            return array(
                'type' => 'success',
                'icon' => '👨‍👩‍👧',
                'text' => 'Family-friendly',
            );
        }

        return null;
    }
}
