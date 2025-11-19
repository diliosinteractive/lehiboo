<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Informations générales de l'événement
 * Contient: Nom, Catégorie, Type, Public, Thématiques, Tags, Saisons, Émotions, Activités associées
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
$selected_saisons = el_get_terms_safe($post_id, 'event_saison');
$selected_emotions = el_get_terms_safe($post_id, 'event_emotion');

// Related events (Meta)
$related_events = get_post_meta($post_id, $_prefix . 'related_events', true);

?>

<div class="event_basic_block">
    <h4 class="heading_section"><?php esc_html_e( 'Informations générales', 'eventlist' ); ?></h4>
    
    <!-- Nom de l'activité -->
    <div class="vendor_field">
        <label for="name_event">
            <?php esc_html_e( 'Nom de l\'activité', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <input type="text" id="name_event" name="name_event" value="<?php echo esc_attr( $post_title ); ?>" placeholder="<?php esc_html_e( 'Saisir le titre', 'eventlist' ); ?>" required>
    </div>

    <!-- Catégorie -->
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

    <!-- Type d'événement -->
    <div class="vendor_field">
        <label for="event_type">
            <?php esc_html_e( 'Type d\'événement', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <select name="event_type" id="event_type" class="selectpicker" required>
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

    <!-- Public visé -->
    <div class="vendor_field">
        <label for="event_public">
            <?php esc_html_e( 'Public visé', 'eventlist' ); ?>
            <span class="el_req">*</span>
        </label>
        <select name="event_public[]" id="event_public" class="selectpicker" multiple required>
            <?php
            $publics = get_terms(array('taxonomy' => 'event_public', 'hide_empty' => false, 'parent' => 0));
            if (!is_wp_error($publics)) {
                foreach ($publics as $parent) {
                    echo '<optgroup label="' . esc_attr($parent->name) . '">';
                    // Check children
                    $children = get_terms(array('taxonomy' => 'event_public', 'hide_empty' => false, 'parent' => $parent->term_id));
                    if (!empty($children) && !is_wp_error($children)) {
                        foreach ($children as $child) {
                            $selected = in_array($child->term_id, $selected_public) ? 'selected' : '';
                            echo '<option value="' . esc_attr($child->term_id) . '" ' . $selected . '>' . esc_html($child->name) . '</option>';
                        }
                    } else {
                         // If no children, display parent as option (or maybe logic differs)
                         $selected = in_array($parent->term_id, $selected_public) ? 'selected' : '';
                         echo '<option value="' . esc_attr($parent->term_id) . '" ' . $selected . '>' . esc_html($parent->name) . '</option>';
                    }
                    echo '</optgroup>';
                }
            }
            ?>
        </select>
    </div>

    <!-- Thématiques -->
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

    <!-- Événements (Tags) -->
    <div class="vendor_field">
        <label for="event_tag"><?php esc_html_e( 'Événements', 'eventlist' ); ?></label>
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

    <!-- Saisons -->
    <div class="vendor_field">
        <label for="event_saison"><?php esc_html_e( 'Saisons', 'eventlist' ); ?></label>
        <select name="event_saison[]" id="event_saison" class="selectpicker" multiple>
            <?php
            $terms = get_terms(array('taxonomy' => 'event_saison', 'hide_empty' => false));
            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $selected = in_array($term->term_id, $selected_saisons) ? 'selected' : '';
                    echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <!-- Émotions -->
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

    <!-- Activités associées -->
    <div class="vendor_field">
        <label for="related_events"><?php esc_html_e( 'Activités à associer', 'eventlist' ); ?></label>
        <input type="text" id="related_events_search" placeholder="<?php esc_html_e( 'Rechercher une activité...', 'eventlist' ); ?>">
        <div id="related_events_container">
            <!-- Selected related events will appear here -->
            <?php
            if (!empty($related_events)) {
                $related_ids = explode(',', $related_events);
                foreach ($related_ids as $rid) {
                    if ($rid) {
                        echo '<div class="related-event-item" data-id="' . esc_attr($rid) . '">' . get_the_title($rid) . ' <span class="remove-related">x</span></div>';
                    }
                }
            }
            ?>
        </div>
        <input type="hidden" name="<?php echo esc_attr($_prefix . 'related_events'); ?>" id="related_events" value="<?php echo esc_attr($related_events); ?>">
    </div>

    <!-- Co-organisateurs CTA -->
    <div class="vendor_field">
        <p>
            <a href="#organiser_tab" class="button button-secondary"><?php esc_html_e( 'Gérer les co-organisateurs', 'eventlist' ); ?></a>
        </p>
    </div>

</div>
