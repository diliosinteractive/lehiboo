<?php
/**
 * Cache Helper for Mobile API
 * Uses WordPress Transients for server-side caching
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Cache {

    /**
     * Cache prefix
     */
    const PREFIX = 'lma_cache_';

    /**
     * Cache TTLs (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes - for dynamic data
    const TTL_MEDIUM = 900;     // 15 minutes - for semi-static data
    const TTL_LONG = 3600;      // 1 hour - for static data
    const TTL_VERY_LONG = 86400; // 24 hours - for very static data

    /**
     * Cache groups with their TTLs
     */
    private static $groups = array(
        'events_list' => self::TTL_SHORT,      // Events list changes frequently
        'event_detail' => self::TTL_MEDIUM,    // Single event details
        'categories' => self::TTL_LONG,        // Categories change rarely
        'thematiques' => self::TTL_LONG,       // Thematiques change rarely
        'cities' => self::TTL_LONG,            // Cities change rarely
        'filters' => self::TTL_LONG,           // Filter options
        'organizers' => self::TTL_MEDIUM,      // Organizer profiles
    );

    /**
     * Get cached value
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @return mixed|false Cached value or false if not found/expired
     */
    public static function get($group, $key) {
        if (!self::is_enabled()) {
            return false;
        }

        $cache_key = self::build_key($group, $key);
        $value = get_transient($cache_key);

        // Log cache hit/miss for debugging
        if (defined('WP_DEBUG') && WP_DEBUG && defined('LMA_DEBUG_CACHE') && LMA_DEBUG_CACHE) {
            $status = $value !== false ? 'HIT' : 'MISS';
            error_log(sprintf('[LMA Cache] %s: %s/%s', $status, $group, $key));
        }

        return $value;
    }

    /**
     * Set cached value
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Optional TTL override
     * @return bool Success
     */
    public static function set($group, $key, $value, $ttl = null) {
        if (!self::is_enabled()) {
            return false;
        }

        $cache_key = self::build_key($group, $key);
        $expiration = $ttl ?? self::get_ttl($group);

        return set_transient($cache_key, $value, $expiration);
    }

    /**
     * Delete cached value
     *
     * @param string $group Cache group
     * @param string $key Cache key
     * @return bool Success
     */
    public static function delete($group, $key) {
        $cache_key = self::build_key($group, $key);
        return delete_transient($cache_key);
    }

    /**
     * Clear all cache for a group
     *
     * @param string $group Cache group
     * @return int Number of deleted entries
     */
    public static function clear_group($group) {
        global $wpdb;

        $pattern = '_transient_' . self::PREFIX . $group . '_%';
        $count = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $pattern
        ));

        // Also delete timeout transients
        $pattern_timeout = '_transient_timeout_' . self::PREFIX . $group . '_%';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $pattern_timeout
        ));

        return $count;
    }

    /**
     * Clear all LMA cache
     *
     * @return int Number of deleted entries
     */
    public static function clear_all() {
        global $wpdb;

        $pattern = '_transient_' . self::PREFIX . '%';
        $count = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $pattern
        ));

        // Also delete timeout transients
        $pattern_timeout = '_transient_timeout_' . self::PREFIX . '%';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $pattern_timeout
        ));

        return $count;
    }

    /**
     * Build cache key from group and key
     *
     * @param string $group
     * @param string $key
     * @return string
     */
    private static function build_key($group, $key) {
        // Sanitize and truncate key (transient names max 172 chars)
        $sanitized_key = sanitize_key($key);
        $full_key = self::PREFIX . $group . '_' . $sanitized_key;

        // If too long, hash it
        if (strlen($full_key) > 170) {
            $full_key = self::PREFIX . $group . '_' . md5($key);
        }

        return $full_key;
    }

    /**
     * Get TTL for a group
     *
     * @param string $group
     * @return int TTL in seconds
     */
    private static function get_ttl($group) {
        return self::$groups[$group] ?? self::TTL_MEDIUM;
    }

    /**
     * Check if caching is enabled
     *
     * @return bool
     */
    public static function is_enabled() {
        return get_option('lma_cache_enabled', 'yes') === 'yes';
    }

    /**
     * Generate a cache key from request parameters
     *
     * @param WP_REST_Request $request
     * @param array $params Parameters to include in key
     * @return string
     */
    public static function key_from_request($request, $params = array()) {
        $key_parts = array();

        foreach ($params as $param) {
            $value = $request->get_param($param);
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $key_parts[] = $param . '=' . $value;
            }
        }

        // Add locale if available
        $locale = get_locale();
        if ($locale) {
            $key_parts[] = 'locale=' . $locale;
        }

        return implode('&', $key_parts) ?: 'default';
    }

    /**
     * Invalidate event-related caches
     * Should be called when an event is created/updated/deleted
     *
     * @param int $event_id Optional specific event ID
     */
    public static function invalidate_events($event_id = null) {
        // Clear events list cache (all variations)
        self::clear_group('events_list');

        // Clear specific event if provided
        if ($event_id) {
            self::delete('event_detail', 'event_' . $event_id);
        }

        // Also clear related caches
        self::clear_group('cities');
        self::clear_group('filters');
    }

    /**
     * Invalidate category-related caches
     */
    public static function invalidate_categories() {
        self::clear_group('categories');
        self::clear_group('thematiques');
        self::clear_group('filters');
    }

    /**
     * Invalidate organizer-related caches
     *
     * @param int $user_id Optional specific user ID
     */
    public static function invalidate_organizers($user_id = null) {
        if ($user_id) {
            self::delete('organizers', 'organizer_' . $user_id);
        } else {
            self::clear_group('organizers');
        }
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public static function get_stats() {
        global $wpdb;

        $stats = array();

        foreach (array_keys(self::$groups) as $group) {
            $pattern = '_transient_' . self::PREFIX . $group . '_%';
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ));
            $stats[$group] = intval($count);
        }

        $stats['total'] = array_sum($stats);
        $stats['enabled'] = self::is_enabled();

        return $stats;
    }
}
