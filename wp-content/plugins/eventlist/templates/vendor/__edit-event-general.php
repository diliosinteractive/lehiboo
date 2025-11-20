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

    <!-- Row 2: Category -->
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

    <!-- Row 6: Co-organizers (Nouveau système) -->
    <div class="vendor_field co_organizers_section">
        <label class="co_organizer_label"><?php esc_html_e( 'Ajouter des co-organisateurs', 'eventlist' ); ?></label>
        <p class="field_help_text">
            <?php esc_html_e( 'Sélectionnez vos partenaires pour les inviter à co-organiser cet événement', 'eventlist' ); ?>
        </p>

        <?php
        // Récupérer les co-organisateurs existants pour cet événement
        $current_coorganisers = array();
        if ( ! empty( $post_id ) ) {
            $current_coorganisers = EL_Event_Coorganisation::get_for_event( $post_id );
        }

        // Récupérer les partenaires acceptés
        $current_user_id = get_current_user_id();
        $accepted_partners = EL_Partnership::get_accepted_partners( $current_user_id );
        ?>

        <!-- Liste des co-organisateurs actuels -->
        <div id="current_coorganisers_list">
            <?php if ( ! empty( $current_coorganisers ) ) : ?>
                <?php foreach ( $current_coorganisers as $coorg ) :
                    $coorg_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_coorganisatrice_id );
                ?>
                    <div class="el_coorg_item" data-coorg-id="<?php echo esc_attr( $coorg->id ); ?>">
                        <div class="el_row">
                            <div class="el_col_6">
                                <strong><?php echo esc_html( $coorg_name ); ?></strong>
                            </div>
                            <div class="el_col_3">
                                <?php echo EL_Coorg_Helpers::get_status_badge( $coorg->statut ); ?>
                            </div>
                            <div class="el_col_3">
                                <button
                                    type="button"
                                    class="el_coorg_remove_btn"
                                    data-coorg-id="<?php echo esc_attr( $coorg->id ); ?>"
                                >
                                    <?php esc_html_e( 'Retirer', 'eventlist' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Ajouter un co-organisateur -->
        <?php if ( ! empty( $accepted_partners ) ) : ?>
            <div class="el_coorg_add_section">
                <select id="el_coorg_select_partner" class="selectpicker">
                    <option value=""><?php esc_html_e( 'Sélectionnez un partenaire...', 'eventlist' ); ?></option>
                    <?php foreach ( $accepted_partners as $partnership ) :
                        // Déterminer l'autre organisation
                        $partner_id = ( $partnership->organisation_principale_id == $current_user_id )
                            ? $partnership->organisation_invitee_id
                            : $partnership->organisation_principale_id;

                        $partner_name = EL_Coorg_Helpers::get_organisation_name( $partner_id );

                        // Vérifier si déjà ajouté
                        $already_added = false;
                        foreach ( $current_coorganisers as $coorg ) {
                            if ( $coorg->organisation_coorganisatrice_id == $partner_id ) {
                                $already_added = true;
                                break;
                            }
                        }

                        if ( ! $already_added ) :
                    ?>
                        <option value="<?php echo esc_attr( $partner_id ); ?>">
                            <?php echo esc_html( $partner_name ); ?>
                        </option>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </select>

                <select id="el_coorg_select_role">
                    <option value="co-organisateur"><?php esc_html_e( 'Co-organisateur', 'eventlist' ); ?></option>
                    <option value="partenaire"><?php esc_html_e( 'Partenaire', 'eventlist' ); ?></option>
                    <option value="sponsor"><?php esc_html_e( 'Sponsor', 'eventlist' ); ?></option>
                </select>

                <button type="button" id="el_coorg_add_btn" class="btn_add_co_organizer">
                    <?php esc_html_e( 'Ajouter', 'eventlist' ); ?>
                </button>
            </div>
        <?php else : ?>
            <p class="el_coorg_no_partners">
                <?php
                printf(
                    esc_html__( 'Vous n\'avez pas encore de partenaires. %sInvitez des partenaires%s pour pouvoir les ajouter comme co-organisateurs.', 'eventlist' ),
                    '<a href="' . home_url( '/vendor/partenariats/' ) . '">',
                    '</a>'
                );
                ?>
            </p>
        <?php endif; ?>
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

    // Ajouter un co-organisateur
    $('#el_coorg_add_btn').on('click', function() {
        const partnerId = $('#el_coorg_select_partner').val();
        const role = $('#el_coorg_select_role').val();
        const eventId = <?php echo ! empty( $post_id ) ? intval( $post_id ) : 0; ?>;

        if (!partnerId) {
            alert('<?php esc_html_e( 'Veuillez sélectionner un partenaire', 'eventlist' ); ?>');
            return;
        }

        if (!eventId) {
            alert('<?php esc_html_e( 'Veuillez d\'abord enregistrer l\'événement avant d\'ajouter des co-organisateurs', 'eventlist' ); ?>');
            return;
        }

        $.ajax({
            url: el_coorg_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'el_add_event_coorganiser',
                nonce: el_coorg_vars.nonce,
                event_id: eventId,
                org_id: partnerId,
                role: role
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php esc_html_e( 'Une erreur s\'est produite', 'eventlist' ); ?>');
            }
        });
    });

    // Retirer un co-organisateur
    $(document).on('click', '.el_coorg_remove_btn', function() {
        const coorgId = $(this).data('coorg-id');

        if (!confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir retirer ce co-organisateur ?', 'eventlist' ); ?>')) {
            return;
        }

        $.ajax({
            url: el_coorg_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'el_remove_event_coorganiser',
                nonce: el_coorg_vars.nonce,
                coorg_id: coorgId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php esc_html_e( 'Une erreur s\'est produite', 'eventlist' ); ?>');
            }
        });
    });
});
</script>
