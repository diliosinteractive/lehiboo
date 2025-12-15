<?php
/**
 * REST Categories Controller
 * Endpoints catégories et taxonomies
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Categories {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List categories
        register_rest_route($this->namespace, '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true',
        ));

        // List thematiques
        register_rest_route($this->namespace, '/thematiques', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_thematiques'),
            'permission_callback' => '__return_true',
        ));

        // List cities (from events)
        register_rest_route($this->namespace, '/cities', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cities'),
            'permission_callback' => '__return_true',
        ));

        // Get filter options
        register_rest_route($this->namespace, '/filters', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_filter_options'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Get categories
     */
    public function get_categories($request) {
        $include_count = $request->get_param('include_count') !== 'false';
        $parent_only = $request->get_param('parent_only') === 'true';

        $args = array(
            'taxonomy' => 'event_cat',
            'hide_empty' => false,
        );

        if ($parent_only) {
            $args['parent'] = 0;
        }

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return LMA_Response::error(
                'fetch_error',
                __('Erreur lors de la récupération des catégories', 'lehiboo-mobile-api'),
                500
            );
        }

        $categories = array();

        foreach ($terms as $term) {
            $category = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'parent_id' => $term->parent ?: null,
            );

            if ($include_count) {
                $category['event_count'] = $this->get_upcoming_event_count($term->term_id, 'event_cat');
            }

            // Get category icon/image if exists
            $icon = get_term_meta($term->term_id, 'category_icon', true);
            $image = get_term_meta($term->term_id, 'category_image', true);

            if ($icon) {
                $category['icon'] = $icon;
            }
            if ($image) {
                $category['image'] = wp_get_attachment_url($image);
            }

            // Get children if parent
            if ($term->parent === 0) {
                $children = get_terms(array(
                    'taxonomy' => 'event_cat',
                    'parent' => $term->term_id,
                    'hide_empty' => false,
                ));

                if (!is_wp_error($children) && !empty($children)) {
                    $category['children'] = array_map(function($child) use ($include_count) {
                        $child_data = array(
                            'id' => $child->term_id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        );

                        if ($include_count) {
                            $child_data['event_count'] = $this->get_upcoming_event_count($child->term_id, 'event_cat');
                        }

                        return $child_data;
                    }, $children);
                }
            }

            $categories[] = $category;
        }

        // Sort by name
        usort($categories, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return LMA_Response::success(array(
            'categories' => $categories,
            'count' => count($categories),
        ));
    }

    /**
     * Get thematiques
     */
    public function get_thematiques($request) {
        $include_count = $request->get_param('include_count') !== 'false';
        $sort_by = $request->get_param('sort_by') ?: 'event_count'; // event_count (default) or name

        $terms = get_terms(array(
            'taxonomy' => 'event_thematique',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms)) {
            return LMA_Response::error(
                'fetch_error',
                __('Erreur lors de la récupération des thématiques', 'lehiboo-mobile-api'),
                500
            );
        }

        $thematiques = array();

        foreach ($terms as $term) {
            // Toujours calculer le count pour le tri
            $event_count = $this->get_upcoming_event_count($term->term_id, 'event_thematique');

            $thematique = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'event_count' => $event_count,
            );

            // Get icon if exists
            $icon = get_term_meta($term->term_id, 'thematique_icon', true);
            if ($icon) {
                $thematique['icon'] = $icon;
            }

            // Get image if exists
            $image_data = LMA_Taxonomy_Image::get_image_data($term->term_id);
            if ($image_data) {
                $thematique['image'] = $image_data;
            }

            $thematiques[] = $thematique;
        }

        // Sort by event_count (descending) or by name
        if ($sort_by === 'name') {
            usort($thematiques, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        } else {
            // Default: sort by event_count descending, then by name
            usort($thematiques, function($a, $b) {
                if ($a['event_count'] === $b['event_count']) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $b['event_count'] - $a['event_count'];
            });
        }

        // Remove event_count if not requested
        if (!$include_count) {
            $thematiques = array_map(function($t) {
                unset($t['event_count']);
                return $t;
            }, $thematiques);
        }

        return LMA_Response::success(array(
            'thematiques' => $thematiques,
            'count' => count($thematiques),
        ));
    }

    /**
     * Get cities from event_loc taxonomy
     */
    public function get_cities($request) {
        $include_count = $request->get_param('include_count') !== 'false';
        $hide_empty = $request->get_param('hide_empty') !== 'false';

        // Get locations from event_loc taxonomy (cities are child terms)
        $terms = get_terms(array(
            'taxonomy' => 'event_loc',
            'hide_empty' => $hide_empty,
            'parent' => 0, // Get only top-level (states/regions) first, or remove for all
        ));

        if (is_wp_error($terms)) {
            return LMA_Response::error(
                'fetch_error',
                __('Erreur lors de la récupération des lieux', 'lehiboo-mobile-api'),
                500
            );
        }

        $cities = array();

        // Get all locations (including children which are actual cities)
        $all_terms = get_terms(array(
            'taxonomy' => 'event_loc',
            'hide_empty' => $hide_empty,
        ));

        if (!is_wp_error($all_terms)) {
            foreach ($all_terms as $term) {
                $city = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'parent_id' => $term->parent ?: null,
                );

                if ($include_count) {
                    $city['event_count'] = (int) $term->count;
                }

                $cities[] = $city;
            }
        }

        // Sort by name
        usort($cities, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return LMA_Response::success(array(
            'cities' => $cities,
            'count' => count($cities),
        ));
    }

    /**
     * Get all filter options
     */
    public function get_filter_options($request) {
        global $wpdb;

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        // Get categories (simplified)
        $categories = get_terms(array(
            'taxonomy' => 'event_cat',
            'hide_empty' => false,
            'parent' => 0,
        ));

        $categories_list = array();
        if (!is_wp_error($categories)) {
            foreach ($categories as $cat) {
                $categories_list[] = array(
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                );
            }
        }

        // Get thematiques (simplified)
        $thematiques = get_terms(array(
            'taxonomy' => 'event_thematique',
            'hide_empty' => false,
        ));

        $thematiques_list = array();
        if (!is_wp_error($thematiques)) {
            foreach ($thematiques as $them) {
                $thematiques_list[] = array(
                    'id' => $them->term_id,
                    'name' => $them->name,
                    'slug' => $them->slug,
                );
            }
        }

        // Get cities from event_loc taxonomy
        $location_terms = get_terms(array(
            'taxonomy' => 'event_loc',
            'hide_empty' => false,
        ));

        $cities = array();
        if (!is_wp_error($location_terms)) {
            foreach ($location_terms as $term) {
                $cities[] = $term->name;
            }
            sort($cities);
        }

        // Get price range
        $price_range = $wpdb->get_row($wpdb->prepare(
            "SELECT MIN(CAST(pm.meta_value AS UNSIGNED)) as min_price,
                    MAX(CAST(pm.meta_value AS UNSIGNED)) as max_price
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = %s
             AND p.post_type = 'event'
             AND p.post_status = 'publish'
             AND pm.meta_value != ''
             AND pm.meta_value != '0'",
            $meta_prefix . 'price'
        ));

        return LMA_Response::success(array(
            'categories' => $categories_list,
            'thematiques' => $thematiques_list,
            'cities' => $cities,
            'price_range' => array(
                'min' => $price_range ? (int)$price_range->min_price : 0,
                'max' => $price_range ? (int)$price_range->max_price : 100000,
            ),
            'sort_options' => array(
                array('value' => 'date', 'label' => __('Date', 'lehiboo-mobile-api')),
                array('value' => 'price', 'label' => __('Prix', 'lehiboo-mobile-api')),
                array('value' => 'distance', 'label' => __('Distance', 'lehiboo-mobile-api')),
                array('value' => 'rating', 'label' => __('Note', 'lehiboo-mobile-api')),
            ),
            'additional_filters' => array(
                array('key' => 'free_only', 'label' => __('Gratuit uniquement', 'lehiboo-mobile-api'), 'type' => 'boolean'),
                array('key' => 'family_friendly', 'label' => __('Familial', 'lehiboo-mobile-api'), 'type' => 'boolean'),
                array('key' => 'indoor', 'label' => __('Intérieur', 'lehiboo-mobile-api'), 'type' => 'boolean'),
                array('key' => 'outdoor', 'label' => __('Extérieur', 'lehiboo-mobile-api'), 'type' => 'boolean'),
            ),
        ));
    }

    /**
     * Get upcoming event count for a term
     */
    private function get_upcoming_event_count($term_id, $taxonomy) {
        global $wpdb;

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
             WHERE tt.term_id = %d
             AND tt.taxonomy = %s
             AND p.post_type = 'event'
             AND p.post_status = 'publish'
             AND pm.meta_value >= %d",
            $meta_prefix . 'start_date_str',
            $term_id,
            $taxonomy,
            time()
        ));

        return (int) $count;
    }
}
