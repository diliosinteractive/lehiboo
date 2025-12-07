<?php
/**
 * REST Posts Controller
 * Endpoints pour les articles de blog
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_REST_Posts {

    protected $namespace = 'lehiboo/v2';

    public function register_routes() {
        // List posts (blog articles)
        register_rest_route($this->namespace, '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts'),
            'permission_callback' => '__return_true',
            'args' => $this->get_posts_args(),
        ));

        // Get single post
        register_rest_route($this->namespace, '/posts/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'Post ID',
                ),
            ),
        ));
    }

    /**
     * Get posts list
     */
    public function get_posts($request) {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 3;
        $category = $request->get_param('category');

        // Limit per_page to reasonable values
        $per_page = min(max(1, (int)$per_page), 20);

        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        // Filter by category if provided
        if (!empty($category)) {
            $args['category_name'] = sanitize_text_field($category);
        }

        $query = new WP_Query($args);
        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = $this->format_post(get_post());
            }
            wp_reset_postdata();
        }

        return LMA_Response::paginated(
            $posts,
            (int) $page,
            (int) $per_page,
            (int) $query->found_posts,
            'posts'
        );
    }

    /**
     * Get single post
     */
    public function get_post($request) {
        $id = $request->get_param('id');
        $post = get_post($id);

        if (!$post || $post->post_type !== 'post' || $post->post_status !== 'publish') {
            return LMA_Response::error(
                'post_not_found',
                __('Article non trouvé', 'lehiboo-mobile-api'),
                404
            );
        }

        return LMA_Response::success(array(
            'post' => $this->format_post($post, true),
        ));
    }

    /**
     * Format post for API response
     *
     * @param WP_Post $post
     * @param bool $full Include full content
     * @return array
     */
    private function format_post($post, $full = false) {
        $post_id = $post->ID;

        // Get featured image
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $featured_image = null;
        if ($thumbnail_id) {
            $featured_image = array(
                'thumbnail' => wp_get_attachment_image_url($thumbnail_id, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($thumbnail_id, 'medium'),
                'large' => wp_get_attachment_image_url($thumbnail_id, 'large'),
                'full' => wp_get_attachment_image_url($thumbnail_id, 'full'),
            );
        }

        // Get categories
        $categories = wp_get_post_categories($post_id, array('fields' => 'all'));
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

        // Get tags
        $tags = wp_get_post_tags($post_id);
        $tags_list = array();
        if (!is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $tags_list[] = array(
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                );
            }
        }

        // Get author info
        $author_id = $post->post_author;
        $author = array(
            'id' => (int) $author_id,
            'name' => get_the_author_meta('display_name', $author_id),
            'avatar' => get_avatar_url($author_id, array('size' => 96)),
        );

        // Build response
        $formatted = array(
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'slug' => $post->post_name,
            'excerpt' => wp_strip_all_tags(get_the_excerpt($post)),
            'featured_image' => $featured_image,
            'categories' => $categories_list,
            'tags' => $tags_list,
            'author' => $author,
            'published_at' => get_the_date('c', $post_id),
            'modified_at' => get_the_modified_date('c', $post_id),
            'link' => get_permalink($post_id),
            'reading_time' => $this->calculate_reading_time($post->post_content),
        );

        // Include full content only when requested (single post view)
        if ($full) {
            $formatted['content'] = apply_filters('the_content', $post->post_content);
        }

        return $formatted;
    }

    /**
     * Calculate reading time in minutes
     *
     * @param string $content
     * @return int
     */
    private function calculate_reading_time($content) {
        $word_count = str_word_count(wp_strip_all_tags($content));
        $reading_time = ceil($word_count / 200); // Average reading speed: 200 words/min
        return max(1, $reading_time);
    }

    /**
     * Get arguments for posts endpoint
     */
    private function get_posts_args() {
        return array(
            'page' => array(
                'type' => 'integer',
                'description' => 'Page number',
                'required' => false,
                'default' => 1,
                'minimum' => 1,
            ),
            'per_page' => array(
                'type' => 'integer',
                'description' => 'Number of posts per page',
                'required' => false,
                'default' => 3,
                'minimum' => 1,
                'maximum' => 20,
            ),
            'category' => array(
                'type' => 'string',
                'description' => 'Filter by category slug',
                'required' => false,
            ),
        );
    }
}
