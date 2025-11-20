<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Informations générales de l'événement
 * Refactored to match "Airbnb" style mockup
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$the_post = get_post( $post_id );
$post_title = empty( $post_id ) ? '' : $the_post->post_title;

// Helper function to get terms safely
function el_get_terms_safe($post_id, $taxonomy) {
    $terms = get_the_terms($post_id, $taxonomy);
    if ($terms && !is_wp_error($terms)) {
        return wp_list_pluck($terms, 'term_id');
    }
    return array();
}

$selected_cats = el_get_terms_safe($post_id, 'event_cat');
$selected_types = el_get_terms_safe($post_id, 'event_type');
$selected_public = el_get_terms_safe($post_id, 'event_public');
$selected_thematiques = el_get_terms_safe($post_id, 'event_thematique');
$selected_tags = el_get_terms_safe($post_id, 'event_tag');
$selected_emotions = el_get_terms_safe($post_id, 'event_emotion');

// Related events (Meta)
$related_events = get_post_meta($post_id, $_prefix . 'related_events', true);

// Co-organizers (Meta - assuming JSON or serialized array)
$co_organizers = get_post_meta($post_id, $_prefix . 'co_organizers', true);
if (!is_array($co_organizers)) $co_organizers = array();

?>

<div class="event_basic_block">
    <h4 class="heading_section">
        <?php esc_html_e( 'Informations générales', 'eventlist' ); ?>
    </h4>
    <p class="field_description">
        <?php esc_html_e( 'Les informations essentielles pour catégoriser votre activité', 'eventlist' ); ?>
    </p>
    
    <!-- Row 1: Name -->
    <div class="vendor_field">
        <label for="name_event">
            <?php esc_html_e( 'Nom de l\'activité', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <input type="text" id="name_event" name="name_event" value="<?php echo esc_attr( $post_title ); ?>" placeholder="<?php esc_html_e( 'Saisir le titre', 'eventlist' ); ?>" required>
    </div>

    <!-- Row 2: Category & Type -->
    <div class="el_row">
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_cat">
                    <?php esc_html_e( 'Catégorie', 'eventlist' ); ?>
                    <span class="el_req">*</span>
                </label>
                <?php
                $selected_cat = !empty($selected_cats) ? $selected_cats[0] : '';
                el_get_taxonomy3('event_cat', 'event_cat', $selected_cat, true); 
                ?>
            </div>
        </div>
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_type">
                    <?php esc_html_e( 'Type d\'événement', 'eventlist' ); ?>
                </label>
                <select name="event_type" id="event_type" class="selectpicker">
                    <option value=""><?php esc_html_e( '--- Sélectionner ---', 'eventlist' ); ?></option>
                    <?php
                    $types = get_terms(array('taxonomy' => 'event_type', 'hide_empty' => false));
                    if (!is_wp_error($types)) {
                        foreach ($types as $term) {
                            $selected = in_array($term->term_id, $selected_types) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Row 3: Public & Themes -->
    <div class="el_row">
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_public">
                    <?php esc_html_e( 'Public visé', 'eventlist' ); ?>
                </label>
                <select name="event_public[]" id="event_public" class="selectpicker" multiple>
                    <?php
                    $publics = get_terms(array('taxonomy' => 'event_public', 'hide_empty' => false, 'parent' => 0));
                    if (!is_wp_error($publics)) {
                        foreach ($publics as $parent) {
                            $children = get_terms(array('taxonomy' => 'event_public', 'hide_empty' => false, 'parent' => $parent->term_id));
                            
                            if (!empty($children) && !is_wp_error($children)) {
                                // If parent has children, create optgroup
                                echo '<optgroup label="' . esc_attr($parent->name) . '">';
                                foreach ($children as $child) {
                                    $selected = in_array($child->term_id, $selected_public) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($child->term_id) . '" ' . $selected . '>' . esc_html($child->name) . '</option>';
                                }
                                echo '</optgroup>';
                            } else {
                                // If no children, show parent as direct option (no optgroup)
                                $selected = in_array($parent->term_id, $selected_public) ? 'selected' : '';
                                echo '<option value="' . esc_attr($parent->term_id) . '" ' . $selected . '>' . esc_html($parent->name) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_thematique"><?php esc_html_e( 'Thématiques', 'eventlist' ); ?></label>
                <select name="event_thematique[]" id="event_thematique" class="selectpicker" multiple>
                    <?php
                    $terms = get_terms(array('taxonomy' => 'event_thematique', 'hide_empty' => false));
                    if (!is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $selected = in_array($term->term_id, $selected_thematiques) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Row 4: Type d'événement & Émotions -->
    <div class="el_row">
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_tag"><?php esc_html_e( 'Type d\'événement', 'eventlist' ); ?></label>
                <select name="event_tag[]" id="event_tag" class="selectpicker" multiple>
                    <?php
                    $terms = get_terms(array('taxonomy' => 'event_tag', 'hide_empty' => false));
                    if (!is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $selected = in_array($term->term_id, $selected_tags) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_emotion"><?php esc_html_e( 'Émotions', 'eventlist' ); ?></label>
                <select name="event_emotion[]" id="event_emotion" class="selectpicker" multiple>
                    <?php
                    $terms = get_terms(array('taxonomy' => 'event_emotion', 'hide_empty' => false));
                    if (!is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $selected = in_array($term->term_id, $selected_emotions) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Row 5: Related Activities -->
    <div class="vendor_field">
        <label for="related_events"><?php esc_html_e( 'Activités à associer', 'eventlist' ); ?></label>
        <select name="<?php echo esc_attr($_prefix . 'related_events'); ?>[]" id="related_events_select" class="selectpicker" multiple data-live-search="true">
             <?php
             // Pre-populate with all published events (simplified for now, usually done via AJAX)
             $all_events = get_posts(array('post_type' => 'event', 'numberposts' => -1, 'post_status' => 'publish'));
             
             // Handle both array and comma-separated string formats
             if (is_array($related_events)) {
                 $related_ids = $related_events;
             } else {
                 $related_ids = !empty($related_events) ? explode(',', $related_events) : array();
             }
             
             foreach ($all_events as $evt) {
                 if ($evt->ID == $post_id) continue; // Skip self
                 $selected = in_array($evt->ID, $related_ids) ? 'selected' : '';
                 echo '<option value="' . esc_attr($evt->ID) . '" ' . $selected . '>' . esc_html($evt->post_title) . '</option>';
             }
             ?>
        </select>
    </div>

    <!-- Row 6: Co-organizers -->
    <div class="vendor_field co_organizers_section">
        <label class="co_organizer_label"><?php esc_html_e( 'Ajouter des co-organisateurs', 'eventlist' ); ?></label>
        
        <div id="co_organizers_list">
            <?php if (!empty($co_organizers)) : ?>
                <?php foreach ($co_organizers as $index => $co) : ?>
                    <div class="co_organizer_item el_row">
                        <div class="el_col_5">
                            <input type="text" name="co_organizers[<?php echo $index; ?>][name]" value="<?php echo esc_attr($co['name']); ?>" placeholder="Nom de l'organisation">
                        </div>
                        <div class="el_col_2 text_center">
                            <span class="role_label"><?php esc_html_e('Son rôle', 'eventlist'); ?></span>
                        </div>
                        <div class="el_col_4">
                            <select name="co_organizers[<?php echo $index; ?>][role]">
                                <option value="co-organisateur" <?php selected($co['role'], 'co-organisateur'); ?>>Co-organisateur</option>
                                <option value="partenaire" <?php selected($co['role'], 'partenaire'); ?>>Partenaire</option>
                                <option value="sponsor" <?php selected($co['role'], 'sponsor'); ?>>Sponsor</option>
                            </select>
                        </div>
                        <div class="el_col_1">
                            <button type="button" class="remove_co_organizer">x</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="add_co_organizer_btn" class="btn_add_co_organizer">
            <?php esc_html_e( 'Ajouter un co-organisateur', 'eventlist' ); ?>
        </button>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Select2 for all selectpickers
    $('.selectpicker').select2({
        width: '100%',
        placeholder: "<?php esc_html_e('Sélectionnez...', 'eventlist'); ?>",
        allowClear: true
    });

    // Co-organizer Repeater Logic
    $('#add_co_organizer_btn').on('click', function() {
        var index = $('#co_organizers_list .co_organizer_item').length;
        var html = `
            <div class="co_organizer_item el_row" style="margin-top: 10px;">
                <div class="el_col_5">
                    <input type="text" name="co_organizers[${index}][name]" placeholder="Nom de l'organisation">
                </div>
                <div class="el_col_2 text_center" style="display:flex;align-items:center;justify-content:center;">
                    <span class="role_label">Son rôle</span>
                </div>
                <div class="el_col_4">
                    <select name="co_organizers[${index}][role]">
                        <option value="co-organisateur">Co-organisateur</option>
                        <option value="partenaire">Partenaire</option>
                        <option value="sponsor">Sponsor</option>
                    </select>
                </div>
                <div class="el_col_1" style="display:flex;align-items:center;">
                    <button type="button" class="remove_co_organizer" style="background:none;border:none;color:red;font-size:18px;cursor:pointer;">x</button>
                </div>
            </div>
        `;
        $('#co_organizers_list').append(html);
    });

    $(document).on('click', '.remove_co_organizer', function() {
        $(this).closest('.co_organizer_item').remove();
    });
});
</script>
