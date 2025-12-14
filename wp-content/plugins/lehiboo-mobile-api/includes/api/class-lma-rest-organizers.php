<?php
/**
 * REST Organizers Controller
 * Endpoints publics pour les profils organisateurs/partenaires
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Organizers {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // Get organizer detail (public)
        register_rest_route($this->namespace, '/organizers/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_organizer'),
            'permission_callback' => '__return_true',
        ));

        // Get organizer events (public)
        register_rest_route($this->namespace, '/organizers/(?P<id>\d+)/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_organizer_events'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * GET /organizers/{id}
     * Récupère le profil complet d'un organisateur
     */
    public function get_organizer($request) {
        $organizer_id = absint($request->get_param('id'));

        $user = get_userdata($organizer_id);

        if (!$user) {
            return LMA_Response::error(
                'organizer_not_found',
                __('Organisateur introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        // Vérifier que c'est bien un vendor/partenaire OU qu'il a publié des événements
        $is_vendor = in_array('el_event_vendor', $user->roles) || in_array('administrator', $user->roles);
        $has_events = count_user_posts($organizer_id, 'event', true) > 0;

        if (!$is_vendor && !$has_events) {
            return LMA_Response::error(
                'not_an_organizer',
                __('Cet utilisateur n\'est pas un organisateur', 'lehiboo-mobile-api'),
                404
            );
        }

        $profile = $this->format_organizer_profile($organizer_id, $user);

        return LMA_Response::success($profile);
    }

    /**
     * GET /organizers/{id}/events
     * Récupère les événements publiés d'un organisateur
     */
    public function get_organizer_events($request) {
        $organizer_id = absint($request->get_param('id'));
        $status = sanitize_text_field($request->get_param('status') ?? 'upcoming');
        $per_page = absint($request->get_param('per_page') ?? 10);
        $page = absint($request->get_param('page') ?? 1);

        $user = get_userdata($organizer_id);

        if (!$user) {
            return LMA_Response::error(
                'organizer_not_found',
                __('Organisateur introuvable', 'lehiboo-mobile-api'),
                404
            );
        }

        $meta_prefix = defined('OVA_METABOX_EVENT') ? OVA_METABOX_EVENT : 'el_';

        $args = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'author' => $organizer_id,
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_key' => $meta_prefix . 'start_date_str',
            'orderby' => 'meta_value_num',
            'order' => $status === 'past' ? 'DESC' : 'ASC',
        );

        // Filtre par date
        $now = time();
        if ($status === 'upcoming') {
            $args['meta_query'] = array(
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $now,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ),
            );
        } elseif ($status === 'past') {
            $args['meta_query'] = array(
                array(
                    'key' => $meta_prefix . 'start_date_str',
                    'value' => $now,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
            );
        }

        // Inclure aussi les événements où l'organisateur est co-organisateur
        if (class_exists('EL_Event_Coorganisation')) {
            $coorg_events = EL_Event_Coorganisation::get_for_organisation($organizer_id, 'acceptee');
            if (!empty($coorg_events)) {
                $coorg_event_ids = array_map(function($c) { return $c->event_id; }, $coorg_events);

                // Fusionner avec les propres événements
                $args_coorg = $args;
                unset($args_coorg['author']);
                $args_coorg['post__in'] = $coorg_event_ids;

                $query_own = new WP_Query($args);
                $query_coorg = new WP_Query($args_coorg);

                // Merger et dédupliquer
                $all_events = array_merge($query_own->posts, $query_coorg->posts);
                $unique_events = array();
                $seen_ids = array();
                foreach ($all_events as $event) {
                    if (!in_array($event->ID, $seen_ids)) {
                        $unique_events[] = $event;
                        $seen_ids[] = $event->ID;
                    }
                }

                // Trier
                usort($unique_events, function($a, $b) use ($meta_prefix, $status) {
                    $date_a = get_post_meta($a->ID, $meta_prefix . 'start_date_str', true);
                    $date_b = get_post_meta($b->ID, $meta_prefix . 'start_date_str', true);
                    return $status === 'past' ? ($date_b - $date_a) : ($date_a - $date_b);
                });

                // Paginer manuellement
                $total = count($unique_events);
                $offset = ($page - 1) * $per_page;
                $paginated = array_slice($unique_events, $offset, $per_page);

                $events = $this->format_events($paginated, $meta_prefix);

                return LMA_Response::success(array(
                    'events' => $events,
                    'pagination' => array(
                        'total' => $total,
                        'per_page' => $per_page,
                        'current_page' => $page,
                        'total_pages' => ceil($total / $per_page),
                    ),
                ));
            }
        }

        // Requête simple (sans co-organisation)
        $query = new WP_Query($args);
        $events = $this->format_events($query->posts, $meta_prefix);

        return LMA_Response::success(array(
            'events' => $events,
            'pagination' => array(
                'total' => $query->found_posts,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $query->max_num_pages,
            ),
        ));
    }

    /**
     * Format le profil complet d'un organisateur
     */
    private function format_organizer_profile($user_id, $user) {
        // Nom public: org_display_name > org_name > display_name
        $org_display_name = get_user_meta($user_id, 'org_display_name', true);
        $org_name = get_user_meta($user_id, 'org_name', true);
        $public_name = !empty($org_display_name) ? $org_display_name : (!empty($org_name) ? $org_name : $user->display_name);

        // Avatar/Logo
        $author_id_image = get_user_meta($user_id, 'author_id_image', true);
        $logo = $author_id_image
            ? wp_get_attachment_image_url($author_id_image, 'medium')
            : get_avatar_url($user_id, array('size' => 300));

        // Logo en plusieurs tailles
        $logo_sizes = null;
        if ($author_id_image) {
            $logo_sizes = array(
                'thumbnail' => wp_get_attachment_image_url($author_id_image, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($author_id_image, 'medium'),
                'large' => wp_get_attachment_image_url($author_id_image, 'large'),
                'full' => wp_get_attachment_image_url($author_id_image, 'full'),
            );
        }

        // Image de couverture
        $org_cover_image = get_user_meta($user_id, 'org_cover_image', true);
        $cover_image = $org_cover_image
            ? wp_get_attachment_image_url($org_cover_image, 'large')
            : null;

        // Contact
        $user_phone = get_user_meta($user_id, 'user_phone', true);
        $user_professional_email = get_user_meta($user_id, 'user_professional_email', true);
        $org_web = get_user_meta($user_id, 'org_web', true);

        // Location
        $user_city = get_user_meta($user_id, 'user_city', true);
        $user_country = get_user_meta($user_id, 'user_country', true);
        $user_postcode = get_user_meta($user_id, 'user_postcode', true);
        $user_address = get_user_meta($user_id, 'user_address', true);

        // Format country name
        $country_names = array(
            'FR' => 'France',
            'BE' => 'Belgique',
            'CH' => 'Suisse',
            'CA' => 'Canada',
            'LU' => 'Luxembourg',
            'MC' => 'Monaco',
        );
        $country_label = isset($country_names[$user_country]) ? $country_names[$user_country] : $user_country;

        // Infos pratiques (Services & Accessibilité)
        $org_pmr = get_user_meta($user_id, 'org_pmr', true);
        $org_restauration = get_user_meta($user_id, 'org_restauration', true);
        $org_boisson = get_user_meta($user_id, 'org_boisson', true);
        $org_stationnement = get_user_meta($user_id, 'org_stationnement', true);
        $org_event_type = get_user_meta($user_id, 'org_event_type', true);

        // Réseaux sociaux
        $user_profile_social = get_user_meta($user_id, 'user_profile_social', true);
        $social_links = array();
        if (!empty($user_profile_social) && is_array($user_profile_social)) {
            foreach ($user_profile_social as $social) {
                if (!empty($social[0])) {
                    // Détecter le type de réseau social à partir de l'URL ou de l'icône
                    $type = $this->detect_social_type($social[0], isset($social[1]) ? $social[1] : '');
                    $social_links[] = array(
                        'type' => $type,
                        'url' => $social[0],
                        'icon' => isset($social[1]) ? $social[1] : null,
                    );
                }
            }
        }

        // Statistiques publiques
        $events_count = count_user_posts($user_id, 'event', true);

        // Partenariats (co-organisations)
        $partnerships = array();
        if (class_exists('EL_Partnership')) {
            $partner_list = EL_Partnership::get_accepted_partners($user_id);
            if (!empty($partner_list)) {
                foreach (array_slice($partner_list, 0, 10) as $partner) {
                    $partner_id = $partner->organisation_id_1 == $user_id
                        ? $partner->organisation_id_2
                        : $partner->organisation_id_1;

                    $partner_name_display = get_user_meta($partner_id, 'org_display_name', true);
                    $partner_org_name = get_user_meta($partner_id, 'org_name', true);
                    $partner_name = !empty($partner_name_display) ? $partner_name_display : (!empty($partner_org_name) ? $partner_org_name : '');

                    if ($partner_name) {
                        $partner_image = get_user_meta($partner_id, 'author_id_image', true);
                        $partnerships[] = array(
                            'id' => $partner_id,
                            'name' => $partner_name,
                            'logo' => $partner_image ? wp_get_attachment_image_url($partner_image, 'thumbnail') : get_avatar_url($partner_id),
                        );
                    }
                }
            }
        }

        // Catégories d'événements les plus fréquentes
        $categories = $this->get_organizer_categories($user_id);

        return array(
            'id' => $user_id,
            'name' => $public_name,
            'description' => get_user_meta($user_id, 'description', true) ?: null,
            'logo' => $logo,
            'logo_sizes' => $logo_sizes,
            'cover_image' => $cover_image,
            'verified' => (bool) get_user_meta($user_id, 'verified', true),
            'contact' => array(
                'phone' => $user_phone ?: null,
                'email' => $user_professional_email ?: null, // Ne pas exposer l'email privé
                'website' => $org_web ?: ($user->user_url ?: null),
            ),
            'location' => array(
                'address' => $user_address ?: null,
                'city' => $user_city ?: null,
                'postcode' => $user_postcode ?: null,
                'country' => $user_country ?: null,
                'country_label' => $country_label ?: null,
                'display' => $this->format_location_display($user_city, $user_postcode, $country_label),
            ),
            'practical_info' => array(
                'pmr' => $org_pmr === 'oui',
                'pmr_infos' => get_user_meta($user_id, 'org_pmr_infos', true) ?: null,
                'restauration' => $org_restauration === 'oui',
                'restauration_infos' => get_user_meta($user_id, 'org_restauration_infos', true) ?: null,
                'boisson' => $org_boisson === 'oui',
                'boisson_infos' => get_user_meta($user_id, 'org_boisson_infos', true) ?: null,
                'stationnement' => $org_stationnement ?: null,
                'event_type' => $org_event_type ?: null,
                'event_type_label' => $this->get_event_type_label($org_event_type),
            ),
            'social_links' => $social_links,
            'stats' => array(
                'total_events' => $events_count,
            ),
            'categories' => $categories,
            'partnerships' => $partnerships,
            'profile_url' => get_author_posts_url($user_id),
            'member_since' => $user->user_registered,
        );
    }

    /**
     * Format la liste des événements
     */
    private function format_events($posts, $meta_prefix) {
        $events = array();

        foreach ($posts as $event) {
            $start_date = get_post_meta($event->ID, $meta_prefix . 'start_date_str', true);
            $end_date = get_post_meta($event->ID, $meta_prefix . 'end_date_str', true);
            $price = floatval(get_post_meta($event->ID, $meta_prefix . 'price', true));

            // Catégorie
            $categories = wp_get_post_terms($event->ID, 'event_cat', array('number' => 1));
            $category = !empty($categories) && !is_wp_error($categories) ? array(
                'id' => $categories[0]->term_id,
                'name' => $categories[0]->name,
                'slug' => $categories[0]->slug,
            ) : null;

            // Location
            $city = get_post_meta($event->ID, $meta_prefix . 'city', true);
            $venue = get_post_meta($event->ID, $meta_prefix . 'venue', true);

            // Image
            $thumbnail_id = get_post_thumbnail_id($event->ID);
            $featured_image = null;
            if ($thumbnail_id) {
                $featured_image = array(
                    'id' => $thumbnail_id,
                    'thumbnail' => get_the_post_thumbnail_url($event->ID, 'thumbnail'),
                    'medium' => get_the_post_thumbnail_url($event->ID, 'medium'),
                    'large' => get_the_post_thumbnail_url($event->ID, 'large'),
                    'full' => get_the_post_thumbnail_url($event->ID, 'full'),
                );
            }

            $events[] = array(
                'id' => $event->ID,
                'title' => $event->post_title,
                'slug' => $event->post_name,
                'excerpt' => wp_trim_words(strip_tags($event->post_content), 20),
                'thumbnail' => get_the_post_thumbnail_url($event->ID, 'medium'),
                'featured_image' => $featured_image,
                'category' => $category,
                'dates' => array(
                    'start' => $start_date ? date('Y-m-d', $start_date) : null,
                    'end' => $end_date ? date('Y-m-d', $end_date) : null,
                    'display' => $start_date ? date_i18n('D j M Y', $start_date) : null,
                ),
                'location' => array(
                    'city' => $city ?: null,
                    'venue' => $venue ?: null,
                ),
                'pricing' => array(
                    'price' => $price,
                    'is_free' => $price == 0,
                    'display' => $price == 0 ? 'Gratuit' : number_format($price, 0, ',', ' ') . ' €',
                ),
                'url' => get_permalink($event->ID),
            );
        }

        return $events;
    }

    /**
     * Récupère les catégories d'événements de l'organisateur
     */
    private function get_organizer_categories($user_id) {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT t.term_id, t.name, t.slug, COUNT(*) as count
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             WHERE p.post_type = 'event'
             AND p.post_status = 'publish'
             AND p.post_author = %d
             AND tt.taxonomy = 'event_cat'
             GROUP BY t.term_id
             ORDER BY count DESC
             LIMIT 5",
            $user_id
        ));

        return array_map(function($row) {
            return array(
                'id' => (int) $row->term_id,
                'name' => $row->name,
                'slug' => $row->slug,
                'count' => (int) $row->count,
            );
        }, $results);
    }

    /**
     * Détecte le type de réseau social
     */
    private function detect_social_type($url, $icon) {
        $url_lower = strtolower($url);
        $icon_lower = strtolower($icon);

        $types = array(
            'facebook' => array('facebook.com', 'fb.com', 'fa-facebook'),
            'instagram' => array('instagram.com', 'fa-instagram'),
            'twitter' => array('twitter.com', 'x.com', 'fa-twitter'),
            'linkedin' => array('linkedin.com', 'fa-linkedin'),
            'youtube' => array('youtube.com', 'youtu.be', 'fa-youtube'),
            'tiktok' => array('tiktok.com', 'fa-tiktok'),
            'whatsapp' => array('whatsapp.com', 'wa.me', 'fa-whatsapp'),
            'telegram' => array('t.me', 'telegram', 'fa-telegram'),
            'snapchat' => array('snapchat.com', 'fa-snapchat'),
            'pinterest' => array('pinterest.com', 'fa-pinterest'),
        );

        foreach ($types as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($url_lower, $pattern) !== false || strpos($icon_lower, $pattern) !== false) {
                    return $type;
                }
            }
        }

        return 'website';
    }

    /**
     * Format l'affichage de la localisation
     */
    private function format_location_display($city, $postcode, $country) {
        $parts = array();

        if ($postcode) {
            $parts[] = $postcode;
        }
        if ($city) {
            $parts[] = $city;
        }
        if ($country) {
            $parts[] = $country;
        }

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Retourne le label du type d'événement
     */
    private function get_event_type_label($type) {
        $labels = array(
            'interieur' => 'Intérieur',
            'exterieur' => 'Extérieur',
            'mix' => 'Intérieur & Extérieur',
        );

        return isset($labels[$type]) ? $labels[$type] : null;
    }
}
