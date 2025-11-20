<?php
/**
 * Template: Mes Partenariats
 * Affiche la liste des partenariats (niveau compte)
 */

if ( ! defined( 'ABSPATH' ) ) exit();

$current_user_id = get_current_user_id();
$org_name = EL_Coorg_Helpers::get_organisation_name( $current_user_id );

// Récupérer tous les partenariats
$all_partnerships = EL_Partnership::get_for_organisation( $current_user_id );

// Séparer par statut
$partnerships_en_cours = array_filter( $all_partnerships, function( $p ) { return $p->statut === 'en_cours'; } );
$partnerships_acceptees = array_filter( $all_partnerships, function( $p ) { return $p->statut === 'acceptee'; } );
$partnerships_refusees = array_filter( $all_partnerships, function( $p ) { return $p->statut === 'refusee'; } );
$partnerships_retirees = array_filter( $all_partnerships, function( $p ) { return $p->statut === 'retiree'; } );

?>

<div class="vendor_wrap">

    <?php echo el_get_template( '/vendor/sidebar.php' ); ?>

    <div class="contents">
        <?php echo el_get_template( '/vendor/heading.php' ); ?>

        <div class="el_coorg_partenariats_page">

            <div class="el_coorg_header">
                <h2><?php esc_html_e( 'Mes Partenariats', 'eventlist' ); ?></h2>
                <p class="el_coorg_subtitle">
                    <?php esc_html_e( 'Gérez vos partenariats avec d\'autres organisations', 'eventlist' ); ?>
                </p>
                <button type="button" id="el_coorg_invite_partner_btn" class="el_coorg_btn_primary">
                    <?php esc_html_e( 'Inviter un partenaire', 'eventlist' ); ?>
                </button>
            </div>

            <!-- Modal d'invitation -->
            <div id="el_coorg_invite_modal" class="el_coorg_modal" style="display: none;">
                <div class="el_coorg_modal_content">
                    <span class="el_coorg_modal_close">&times;</span>
                    <h3><?php esc_html_e( 'Inviter un partenaire', 'eventlist' ); ?></h3>

                    <div class="el_coorg_form_group">
                        <label><?php esc_html_e( 'Rechercher une organisation', 'eventlist' ); ?></label>
                        <input
                            type="text"
                            id="el_coorg_search_org"
                            placeholder="<?php esc_attr_e( 'Tapez le nom ou l\'email...', 'eventlist' ); ?>"
                            autocomplete="off"
                        />
                        <div id="el_coorg_search_results" class="el_coorg_search_results"></div>
                    </div>

                    <div class="el_coorg_form_group">
                        <label><?php esc_html_e( 'Ou inviter par email', 'eventlist' ); ?></label>
                        <input
                            type="email"
                            id="el_coorg_invite_email"
                            placeholder="<?php esc_attr_e( 'email@organisation.fr', 'eventlist' ); ?>"
                        />
                        <p class="el_coorg_help_text">
                            <?php esc_html_e( 'Si l\'organisation n\'existe pas encore sur Le Hiboo, un email d\'invitation sera envoyé.', 'eventlist' ); ?>
                        </p>
                    </div>

                    <div class="el_coorg_modal_actions">
                        <button type="button" id="el_coorg_send_invite_btn" class="el_coorg_btn_primary">
                            <?php esc_html_e( 'Envoyer l\'invitation', 'eventlist' ); ?>
                        </button>
                        <button type="button" class="el_coorg_btn_secondary el_coorg_modal_close">
                            <?php esc_html_e( 'Annuler', 'eventlist' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invitations en attente de réponse (reçues) -->
            <?php if ( ! empty( $partnerships_en_cours ) ) :
                $pending_received = array_filter( $partnerships_en_cours, function( $p ) use ( $current_user_id ) {
                    return $p->organisation_invitee_id == $current_user_id;
                });

                if ( ! empty( $pending_received ) ) :
            ?>
            <div class="el_coorg_section">
                <h3><?php esc_html_e( 'Invitations reçues', 'eventlist' ); ?></h3>
                <div class="el_coorg_cards">
                    <?php foreach ( $pending_received as $partnership ) :
                        $inviter_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_principale_id );
                        $inviter_data = EL_Coorg_Helpers::get_organisation_data( $partnership->organisation_principale_id );
                    ?>
                    <div class="el_coorg_card el_coorg_card_pending">
                        <div class="el_coorg_card_header">
                            <h4><?php echo esc_html( $inviter_name ); ?></h4>
                            <?php echo EL_Coorg_Helpers::get_status_badge( 'en_cours' ); ?>
                        </div>
                        <div class="el_coorg_card_body">
                            <p><strong><?php esc_html_e( 'Reçue le :', 'eventlist' ); ?></strong>
                                <?php echo EL_Coorg_Helpers::format_date( $partnership->date_invitation ); ?>
                            </p>
                            <?php if ( ! empty( $inviter_data['city'] ) ) : ?>
                                <p><strong><?php esc_html_e( 'Localisation :', 'eventlist' ); ?></strong>
                                    <?php echo esc_html( $inviter_data['city'] ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="el_coorg_card_actions">
                            <button
                                type="button"
                                class="el_coorg_btn_accept"
                                data-partnership-id="<?php echo esc_attr( $partnership->id ); ?>"
                                data-action="accept_partnership"
                            >
                                <?php esc_html_e( 'Accepter', 'eventlist' ); ?>
                            </button>
                            <button
                                type="button"
                                class="el_coorg_btn_refuse"
                                data-partnership-id="<?php echo esc_attr( $partnership->id ); ?>"
                                data-action="refuse_partnership"
                            >
                                <?php esc_html_e( 'Refuser', 'eventlist' ); ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
                endif;
            endif;
            ?>

            <!-- Partenariats acceptés -->
            <?php if ( ! empty( $partnerships_acceptees ) ) : ?>
            <div class="el_coorg_section">
                <h3><?php esc_html_e( 'Partenaires actifs', 'eventlist' ); ?></h3>
                <div class="el_coorg_table_wrapper">
                    <table class="el_coorg_table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Organisation', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Localisation', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Date partenariat', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Événements co-organisés', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $partnerships_acceptees as $partnership ) :
                                // Déterminer l'autre organisation
                                $other_org_id = ( $partnership->organisation_principale_id == $current_user_id )
                                    ? $partnership->organisation_invitee_id
                                    : $partnership->organisation_principale_id;

                                $other_org_name = EL_Coorg_Helpers::get_organisation_name( $other_org_id );
                                $other_org_data = EL_Coorg_Helpers::get_organisation_data( $other_org_id );

                                $nb_events = EL_Event_Coorganisation::count_events_between_orgs( $current_user_id, $other_org_id );
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $other_org_name ); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    if ( ! empty( $other_org_data['city'] ) ) {
                                        echo esc_html( $other_org_data['city'] );
                                        if ( ! empty( $other_org_data['postcode'] ) ) {
                                            echo ' (' . esc_html( $other_org_data['postcode'] ) . ')';
                                        }
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td><?php echo EL_Coorg_Helpers::format_date( $partnership->date_reponse ); ?></td>
                                <td><?php echo absint( $nb_events ); ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="el_coorg_btn_retire"
                                        data-partnership-id="<?php echo esc_attr( $partnership->id ); ?>"
                                        data-action="retire_partnership"
                                    >
                                        <?php esc_html_e( 'Clôturer', 'eventlist' ); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Invitations envoyées en attente -->
            <?php
            $pending_sent = array_filter( $partnerships_en_cours, function( $p ) use ( $current_user_id ) {
                return $p->organisation_principale_id == $current_user_id;
            });

            if ( ! empty( $pending_sent ) ) :
            ?>
            <div class="el_coorg_section">
                <h3><?php esc_html_e( 'Invitations envoyées (en attente)', 'eventlist' ); ?></h3>
                <div class="el_coorg_table_wrapper">
                    <table class="el_coorg_table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Organisation', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Date d\'invitation', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Statut', 'eventlist' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $pending_sent as $partnership ) :
                                if ( $partnership->organisation_invitee_id ) {
                                    $invitee_name = EL_Coorg_Helpers::get_organisation_name( $partnership->organisation_invitee_id );
                                } else {
                                    $invitee_name = $partnership->email_invite;
                                }
                            ?>
                            <tr>
                                <td><?php echo esc_html( $invitee_name ); ?></td>
                                <td><?php echo EL_Coorg_Helpers::format_date( $partnership->date_invitation ); ?></td>
                                <td><?php echo EL_Coorg_Helpers::get_status_badge( 'en_cours' ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historique (refusés et clôturés) -->
            <?php
            $history = array_merge( $partnerships_refusees, $partnerships_retirees );
            if ( ! empty( $history ) ) :
            ?>
            <div class="el_coorg_section el_coorg_section_collapsed">
                <h3 class="el_coorg_toggle_section">
                    <?php esc_html_e( 'Historique', 'eventlist' ); ?>
                    <span class="el_coorg_toggle_icon">▼</span>
                </h3>
                <div class="el_coorg_section_content" style="display: none;">
                    <div class="el_coorg_table_wrapper">
                        <table class="el_coorg_table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Organisation', 'eventlist' ); ?></th>
                                    <th><?php esc_html_e( 'Date', 'eventlist' ); ?></th>
                                    <th><?php esc_html_e( 'Statut', 'eventlist' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $history as $partnership ) :
                                    $other_org_id = ( $partnership->organisation_principale_id == $current_user_id )
                                        ? $partnership->organisation_invitee_id
                                        : $partnership->organisation_principale_id;

                                    if ( $other_org_id ) {
                                        $other_org_name = EL_Coorg_Helpers::get_organisation_name( $other_org_id );
                                    } else {
                                        $other_org_name = $partnership->email_invite;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $other_org_name ); ?></td>
                                    <td><?php echo EL_Coorg_Helpers::format_date( $partnership->date_reponse ); ?></td>
                                    <td><?php echo EL_Coorg_Helpers::get_status_badge( $partnership->statut ); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Ouvrir le modal d'invitation
    $('#el_coorg_invite_partner_btn').on('click', function() {
        $('#el_coorg_invite_modal').fadeIn();
    });

    // Fermer le modal
    $('.el_coorg_modal_close').on('click', function() {
        $('#el_coorg_invite_modal').fadeOut();
        $('#el_coorg_search_org').val('');
        $('#el_coorg_invite_email').val('');
        $('#el_coorg_search_results').empty();
    });

    // Toggle historique
    $('.el_coorg_toggle_section').on('click', function() {
        $(this).next('.el_coorg_section_content').slideToggle();
        $(this).find('.el_coorg_toggle_icon').text(function(i, text) {
            return text === '▼' ? '▲' : '▼';
        });
    });

    // Recherche d'organisations (autocomplete)
    let searchTimeout;
    $('#el_coorg_search_org').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length < 2) {
            $('#el_coorg_search_results').empty();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: el_coorg_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_search_organisations',
                    nonce: el_coorg_vars.nonce,
                    search: query
                },
                success: function(response) {
                    if (response.success && response.data.organisations.length > 0) {
                        let html = '<ul class="el_coorg_autocomplete">';
                        response.data.organisations.forEach(function(org) {
                            html += '<li data-org-id="' + org.id + '" data-org-name="' + org.name + '">';
                            html += '<strong>' + org.name + '</strong><br>';
                            html += '<small>' + org.email + '</small>';
                            html += '</li>';
                        });
                        html += '</ul>';
                        $('#el_coorg_search_results').html(html);
                    } else {
                        $('#el_coorg_search_results').html('<p class="el_coorg_no_results">' + el_coorg_vars.i18n.no_results + '</p>');
                    }
                }
            });
        }, 300);
    });

    // Sélection d'une organisation dans l'autocomplete
    $(document).on('click', '.el_coorg_autocomplete li', function() {
        const orgId = $(this).data('org-id');
        const orgName = $(this).data('org-name');
        $('#el_coorg_search_org').val(orgName).data('org-id', orgId);
        $('#el_coorg_search_results').empty();
    });

    // Envoyer l'invitation
    $('#el_coorg_send_invite_btn').on('click', function() {
        const orgId = $('#el_coorg_search_org').data('org-id') || 0;
        const email = $('#el_coorg_invite_email').val();

        if (!orgId && !email) {
            alert('Veuillez sélectionner une organisation ou saisir un email');
            return;
        }

        $(this).prop('disabled', true).text(el_coorg_vars.i18n.loading);

        $.ajax({
            url: el_coorg_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'el_invite_partner',
                nonce: el_coorg_vars.nonce,
                org_id: orgId,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#el_coorg_send_invite_btn').prop('disabled', false).text('<?php esc_html_e( 'Envoyer l\'invitation', 'eventlist' ); ?>');
                }
            }
        });
    });

    // Actions sur les partenariats
    $(document).on('click', '[data-action]', function() {
        const action = $(this).data('action');
        const partnershipId = $(this).data('partnership-id');

        let confirmMsg = '';
        if (action === 'retire_partnership') {
            confirmMsg = el_coorg_vars.i18n.confirm_delete;
        }

        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        $.ajax({
            url: el_coorg_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'el_' + action,
                nonce: el_coorg_vars.nonce,
                partnership_id: partnershipId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>
