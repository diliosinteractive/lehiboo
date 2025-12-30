<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Template: Informations générales de l'événement
 * Refactored to match "Airbnb" style mockup
 */

$post_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : '';
$_prefix = OVA_METABOX_EVENT;

$the_post = get_post( $post_id );
$post_title = empty( $post_id ) ? '' : $the_post->post_title;
$user_id = get_current_user_id();

// Helper function to get terms safely
if ( !function_exists('el_get_terms_safe') ) {
    function el_get_terms_safe($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);
        if ($terms && !is_wp_error($terms)) {
            return wp_list_pluck($terms, 'term_id');
        }
        return array();
    }
}

$selected_cats = el_get_terms_safe($post_id, 'event_cat');
$selected_special = el_get_terms_safe($post_id, 'event_special');
$selected_public = el_get_terms_safe($post_id, 'event_public');
$selected_thematiques = el_get_terms_safe($post_id, 'event_thematique');
$selected_tags = el_get_terms_safe($post_id, 'event_tag');
$selected_emotions = el_get_terms_safe($post_id, 'event_emotion');

// Related events (Meta)
$related_events = get_post_meta($post_id, $_prefix . 'related_events', true);

// Co-organizers (Meta - assuming JSON or serialized array)
$co_organizers = get_post_meta($post_id, $_prefix . 'co_organizers', true);
if (!is_array($co_organizers)) $co_organizers = array();

// Type de lieu - default from org profile
$org_lieu_type = get_user_meta( $user_id, 'org_event_type', true );
$event_lieu_type = get_post_meta( $post_id, $_prefix . 'lieu_type', true );
if ( empty( $event_lieu_type ) && !empty( $org_lieu_type ) ) {
    $event_lieu_type = $org_lieu_type;
}

// Check if optional sections have data (to auto-expand)
$has_optional_data = !empty($selected_thematiques) || !empty($selected_special) || !empty($selected_emotions) || !empty($related_events);

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

    <!-- Row 2: Category & Type d'événement -->
    <div class="el_row">
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_cat">
                    <?php esc_html_e( 'Catégorie', 'eventlist' ); ?>
                    <span class="el_req">*</span>
                </label>
                <select name="event_cat" id="event_cat" class="selectpicker" required data-live-search="true">
                    <option value=""><?php esc_html_e( '--- Sélectionner ---', 'eventlist' ); ?></option>
                    <?php
                    // Récupérer les catégories parentes (niveau 0)
                    $parent_categories = get_terms(array(
                        'taxonomy' => 'event_cat',
                        'hide_empty' => false,
                        'parent' => 0,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));

                    if (!is_wp_error($parent_categories)) {
                        $selected_cat = !empty($selected_cats) ? $selected_cats[0] : '';

                        foreach ($parent_categories as $parent) {
                            // Récupérer les sous-catégories
                            $children = get_terms(array(
                                'taxonomy' => 'event_cat',
                                'hide_empty' => false,
                                'parent' => $parent->term_id,
                                'orderby' => 'name',
                                'order' => 'ASC'
                            ));

                            if (!empty($children) && !is_wp_error($children)) {
                                // Si le parent a des enfants, créer un optgroup
                                echo '<optgroup label="' . esc_attr($parent->name) . '">';
                                foreach ($children as $child) {
                                    $selected = ($child->term_id == $selected_cat) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($child->term_id) . '" ' . $selected . '>' . esc_html($child->name) . '</option>';
                                }
                                echo '</optgroup>';
                            } else {
                                // Si pas d'enfants, afficher le parent comme option directe
                                $selected = ($parent->term_id == $selected_cat) ? 'selected' : '';
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
                <label for="event_tag">
                    <?php esc_html_e( 'Type d\'événement', 'eventlist' ); ?>
                    <span class="required-for-publish" title="<?php esc_attr_e( 'Obligatoire pour la mise en ligne', 'eventlist' ); ?>">**</span>
                </label>
                <select name="event_tag" id="event_tag" class="selectpicker" data-live-search="true">
                    <option value=""><?php esc_html_e( '--- Sélectionner ---', 'eventlist' ); ?></option>
                    <?php
                    // Taxonomie non-hiérarchique - liste simple
                    $terms = get_terms(array(
                        'taxonomy' => 'event_tag',
                        'hide_empty' => false,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    if (!is_wp_error($terms)) {
                        $selected_tag = !empty($selected_tags) ? $selected_tags[0] : '';
                        foreach ($terms as $term) {
                            $selected = ($term->term_id == $selected_tag) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
                <small class="field-note"><?php esc_html_e( 'Obligatoire pour la mise en ligne', 'eventlist' ); ?></small>
            </div>
        </div>
    </div>

    <!-- Row 3: Public visé & Type de lieu -->
    <div class="el_row">
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_public">
                    <?php esc_html_e( 'Public visé', 'eventlist' ); ?>
                    <span class="required-for-publish" title="<?php esc_attr_e( 'Obligatoire pour la mise en ligne', 'eventlist' ); ?>">**</span>
                </label>
                <select name="event_public[]" id="event_public" class="selectpicker" multiple data-placeholder="<?php esc_attr_e( 'Sélection multiple possible', 'eventlist' ); ?>" data-live-search="true">
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
                <small class="field-note"><?php esc_html_e( 'Obligatoire pour la mise en ligne', 'eventlist' ); ?></small>
            </div>
        </div>
        <div class="el_col_6">
            <div class="vendor_field">
                <label for="event_lieu_type">
                    <?php esc_html_e( 'Type de lieu', 'eventlist' ); ?>
                </label>
                <select name="<?php echo esc_attr($_prefix . 'lieu_type'); ?>" id="event_lieu_type" class="selectpicker">
                    <option value=""><?php esc_html_e( '--- Sélectionner ---', 'eventlist' ); ?></option>
                    <option value="interieur" <?php selected($event_lieu_type, 'interieur'); ?>><?php esc_html_e( 'Intérieur', 'eventlist' ); ?></option>
                    <option value="exterieur" <?php selected($event_lieu_type, 'exterieur'); ?>><?php esc_html_e( 'Extérieur', 'eventlist' ); ?></option>
                    <option value="interieur_exterieur" <?php selected($event_lieu_type, 'interieur_exterieur'); ?>><?php esc_html_e( 'Intérieur & Extérieur', 'eventlist' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Toggle: Plus de paramètres -->
    <div class="el_toggle_section <?php echo $has_optional_data ? 'is-open' : ''; ?>">
        <button type="button" class="el_toggle_header" aria-expanded="<?php echo $has_optional_data ? 'true' : 'false'; ?>">
            <span class="el_toggle_title"><?php esc_html_e( 'Plus de paramètres pour référencer votre activité', 'eventlist' ); ?></span>
            <span class="el_toggle_icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="el_toggle_content" <?php echo $has_optional_data ? 'style="display: block;"' : ''; ?>>
            <!-- Thématiques & Événements -->
            <div class="el_row">
                <div class="el_col_6">
                    <div class="vendor_field">
                        <label for="event_thematique"><?php esc_html_e( 'Thématiques', 'eventlist' ); ?></label>
                        <select name="event_thematique[]" id="event_thematique" class="selectpicker" multiple data-placeholder="<?php esc_attr_e( 'Sélection multiple possible', 'eventlist' ); ?>" data-live-search="true">
                            <?php
                            // Récupérer les thématiques parentes (niveau 0)
                            $parent_thematiques = get_terms(array(
                                'taxonomy' => 'event_thematique',
                                'hide_empty' => false,
                                'parent' => 0,
                                'orderby' => 'name',
                                'order' => 'ASC'
                            ));

                            if (!is_wp_error($parent_thematiques)) {
                                foreach ($parent_thematiques as $parent) {
                                    // Récupérer les sous-thématiques
                                    $children = get_terms(array(
                                        'taxonomy' => 'event_thematique',
                                        'hide_empty' => false,
                                        'parent' => $parent->term_id,
                                        'orderby' => 'name',
                                        'order' => 'ASC'
                                    ));

                                    if (!empty($children) && !is_wp_error($children)) {
                                        // Si le parent a des enfants, créer un optgroup
                                        echo '<optgroup label="' . esc_attr($parent->name) . '">';
                                        foreach ($children as $child) {
                                            $selected = in_array($child->term_id, $selected_thematiques) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($child->term_id) . '" ' . $selected . '>' . esc_html($child->name) . '</option>';
                                        }
                                        echo '</optgroup>';
                                    } else {
                                        // Si pas d'enfants, afficher le parent comme option directe
                                        $selected = in_array($parent->term_id, $selected_thematiques) ? 'selected' : '';
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
                        <label for="event_special"><?php esc_html_e( 'Événements', 'eventlist' ); ?></label>
                        <select name="event_special[]" id="event_special" class="selectpicker" multiple data-placeholder="<?php esc_attr_e( 'Sélection multiple possible', 'eventlist' ); ?>">
                            <?php
                            $terms = get_terms(array('taxonomy' => 'event_special', 'hide_empty' => false));
                            if (!is_wp_error($terms)) {
                                foreach ($terms as $term) {
                                    $selected = in_array($term->term_id, $selected_special) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Émotions & Activités à associer -->
            <div class="el_row">
                <div class="el_col_6">
                    <div class="vendor_field">
                        <label for="event_emotion"><?php esc_html_e( 'Émotions', 'eventlist' ); ?></label>
                        <select name="event_emotion[]" id="event_emotion" class="selectpicker" multiple data-placeholder="<?php esc_attr_e( 'Sélection multiple possible', 'eventlist' ); ?>">
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
                <div class="el_col_6">
                    <div class="vendor_field">
                        <label for="related_events"><?php esc_html_e( 'Activités à associer', 'eventlist' ); ?></label>
                        <select name="<?php echo esc_attr($_prefix . 'related_events'); ?>[]" id="related_events_select" class="selectpicker" multiple data-live-search="true" data-placeholder="<?php esc_attr_e( 'Sélection multiple possible', 'eventlist' ); ?>">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle: Co-organisateurs -->
    <?php
    // Récupérer les co-organisateurs existants pour cet événement
    $current_coorganisers = array();
    if ( ! empty( $post_id ) ) {
        $current_coorganisers = EL_Event_Coorganisation::get_for_event( $post_id );
    }

    // Récupérer les partenaires acceptés
    $current_user_id = get_current_user_id();
    $accepted_partners = EL_Partnership::get_accepted_partners( $current_user_id );

    $has_coorg_data = !empty($current_coorganisers);
    ?>
    <div class="el_toggle_section <?php echo $has_coorg_data ? 'is-open' : ''; ?>">
        <button type="button" class="el_toggle_header" aria-expanded="<?php echo $has_coorg_data ? 'true' : 'false'; ?>">
            <span class="el_toggle_title"><?php esc_html_e( 'Ajouter des co-organisateurs', 'eventlist' ); ?></span>
            <span class="el_toggle_icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="el_toggle_content" <?php echo $has_coorg_data ? 'style="display: block;"' : ''; ?>>
            <div class="vendor_field co_organizers_section">
                <p class="field_help_text">
                    <?php esc_html_e( 'Liste des co-organisateurs déjà ajoutés à votre événement', 'eventlist' ); ?>
                </p>

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

                <p class="field_help_text">
                    <?php esc_html_e( 'Sélectionnez vos partenaires pour les inviter à co-organiser cet événement', 'eventlist' ); ?>
                    <br>
                <?php
                    printf(
                        esc_html__( 'Si vous souhaitez ajouter des co-organisateurs, %sInvitez des partenaires%s pour pouvoir les ajouter comme co-organisateurs.', 'eventlist' ),
                        '<a href="' . add_query_arg( array( 'vendor' => 'partenariats' ), get_myaccount_page() ) . '">',
                        '</a>'
                    );
                    ?>
                </p>
                <!-- Ajouter un co-organisateur -->
                <?php if ( ! empty( $accepted_partners ) ) : ?>
                    <div class="el_coorg_add_section">
                        <div class="el_row">
                            <div class="el_col_6">
                                <div class="vendor_field">
                                    <label for="el_coorg_select_partner"><?php esc_html_e( 'Partenaire', 'eventlist' ); ?></label>
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
                                </div>
                            </div>
                            <div class="el_col_4">
                                <div class="vendor_field">
                                    <label for="el_coorg_select_role"><?php esc_html_e( 'Son rôle', 'eventlist' ); ?></label>
                                    <select id="el_coorg_select_role" class="selectpicker">
                                        <option value="co-organisateur"><?php esc_html_e( 'Co-organisateur', 'eventlist' ); ?></option>
                                        <option value="partenaire"><?php esc_html_e( 'Partenaire', 'eventlist' ); ?></option>
                                        <option value="sponsor"><?php esc_html_e( 'Sponsor', 'eventlist' ); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="el_col_2">
                                <div class="vendor_field">
                                    <label>&nbsp;</label>
                                    <button type="button" id="el_coorg_add_btn" class="btn_add_co_organizer">
                                        <?php esc_html_e( 'Ajouter', 'eventlist' ); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="el_coorg_no_partners">
                        <?php
                        printf(
                            esc_html__( 'Vous n\'avez pas encore de partenaires. %sInvitez des partenaires%s pour pouvoir les ajouter comme co-organisateurs.', 'eventlist' ),
                            '<a href="' . add_query_arg( array( 'vendor' => 'partenariats' ), get_myaccount_page() ) . '">',
                            '</a>'
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Select2 for all selectpickers
    $('.selectpicker').each(function() {
        var $this = $(this);
        var placeholder = $this.data('placeholder') || "<?php esc_html_e('Sélectionnez...', 'eventlist'); ?>";
        var liveSearch = $this.data('live-search') || false;
        var isMultiple = $this.attr('multiple') !== undefined;

        var select2Config = {
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumResultsForSearch: liveSearch ? 0 : Infinity
        };

        // Pour les select multiples, ajouter la config spécifique
        if (isMultiple) {
            select2Config.closeOnSelect = false;
            select2Config.templateSelection = function(data) {
                return data.text;
            };
        }

        $this.select2(select2Config);
    });

    // Toggle sections
    $('.el_toggle_header').on('click', function() {
        var $section = $(this).closest('.el_toggle_section');
        var $content = $section.find('.el_toggle_content');
        var isOpen = $section.hasClass('is-open');

        if (isOpen) {
            $content.slideUp(200);
            $section.removeClass('is-open');
            $(this).attr('aria-expanded', 'false');
        } else {
            $content.slideDown(200);
            $section.addClass('is-open');
            $(this).attr('aria-expanded', 'true');
        }
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
