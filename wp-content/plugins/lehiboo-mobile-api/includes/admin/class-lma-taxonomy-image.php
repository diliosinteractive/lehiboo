<?php
/**
 * Taxonomy Image Support
 * Adds image field to event_thematique taxonomy
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Taxonomy_Image {

    /**
     * Constructor
     */
    public function __construct() {
        // Add fields to taxonomy
        add_action('event_thematique_add_form_fields', array($this, 'add_image_field'));
        add_action('event_thematique_edit_form_fields', array($this, 'edit_image_field'), 10, 2);

        // Save fields
        add_action('created_event_thematique', array($this, 'save_image_field'));
        add_action('edited_event_thematique', array($this, 'save_image_field'));

        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add image column
        add_filter('manage_edit-event_thematique_columns', array($this, 'add_image_column'));
        add_filter('manage_event_thematique_custom_column', array($this, 'render_image_column'), 10, 3);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
            return;
        }

        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'event_thematique') {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'lma-taxonomy-image',
            LMA_PLUGIN_URL . 'assets/js/taxonomy-image.js',
            array('jquery'),
            LMA_VERSION,
            true
        );

        wp_enqueue_style(
            'lma-taxonomy-image',
            LMA_PLUGIN_URL . 'assets/css/taxonomy-image.css',
            array(),
            LMA_VERSION
        );
    }

    /**
     * Add image field to add form
     */
    public function add_image_field() {
        ?>
        <div class="form-field">
            <label for="thematique_image"><?php _e('Image', 'lehiboo-mobile-api'); ?></label>
            <div id="thematique-image-wrapper">
                <img id="thematique-image-preview" src="" style="max-width: 200px; display: none;">
            </div>
            <input type="hidden" name="thematique_image" id="thematique_image" value="">
            <button type="button" class="button" id="upload-thematique-image">
                <?php _e('Sélectionner une image', 'lehiboo-mobile-api'); ?>
            </button>
            <button type="button" class="button" id="remove-thematique-image" style="display: none;">
                <?php _e('Supprimer', 'lehiboo-mobile-api'); ?>
            </button>
            <p class="description"><?php _e('Image affichée dans l\'application mobile', 'lehiboo-mobile-api'); ?></p>
        </div>
        <?php
    }

    /**
     * Add image field to edit form
     */
    public function edit_image_field($term, $taxonomy) {
        $image_id = get_term_meta($term->term_id, 'thematique_image', true);
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="thematique_image"><?php _e('Image', 'lehiboo-mobile-api'); ?></label>
            </th>
            <td>
                <div id="thematique-image-wrapper">
                    <img id="thematique-image-preview" src="<?php echo esc_url($image_url); ?>"
                         style="max-width: 200px; <?php echo !$image_url ? 'display: none;' : ''; ?>">
                </div>
                <input type="hidden" name="thematique_image" id="thematique_image" value="<?php echo esc_attr($image_id); ?>">
                <button type="button" class="button" id="upload-thematique-image">
                    <?php _e('Sélectionner une image', 'lehiboo-mobile-api'); ?>
                </button>
                <button type="button" class="button" id="remove-thematique-image" <?php echo !$image_url ? 'style="display: none;"' : ''; ?>>
                    <?php _e('Supprimer', 'lehiboo-mobile-api'); ?>
                </button>
                <p class="description"><?php _e('Image affichée dans l\'application mobile', 'lehiboo-mobile-api'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save image field
     */
    public function save_image_field($term_id) {
        if (isset($_POST['thematique_image'])) {
            $image_id = absint($_POST['thematique_image']);
            if ($image_id) {
                update_term_meta($term_id, 'thematique_image', $image_id);
            } else {
                delete_term_meta($term_id, 'thematique_image');
            }
        }
    }

    /**
     * Add image column
     */
    public function add_image_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            if ($key === 'name') {
                $new_columns['thematique_image'] = __('Image', 'lehiboo-mobile-api');
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    /**
     * Render image column
     */
    public function render_image_column($content, $column_name, $term_id) {
        if ($column_name === 'thematique_image') {
            $image_id = get_term_meta($term_id, 'thematique_image', true);
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                return '<img src="' . esc_url($image_url) . '" style="max-width: 50px; height: auto; border-radius: 4px;">';
            }
            return '<span style="color: #999;">—</span>';
        }
        return $content;
    }

    /**
     * Get image URL for a term
     */
    public static function get_image_url($term_id, $size = 'medium') {
        $image_id = get_term_meta($term_id, 'thematique_image', true);
        if ($image_id) {
            return wp_get_attachment_image_url($image_id, $size);
        }
        return null;
    }

    /**
     * Get image data for a term
     */
    public static function get_image_data($term_id) {
        $image_id = get_term_meta($term_id, 'thematique_image', true);
        if (!$image_id) {
            return null;
        }

        return array(
            'id' => (int) $image_id,
            'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
            'medium' => wp_get_attachment_image_url($image_id, 'medium'),
            'large' => wp_get_attachment_image_url($image_id, 'large'),
            'full' => wp_get_attachment_image_url($image_id, 'full'),
        );
    }
}
