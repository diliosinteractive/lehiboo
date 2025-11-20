<?php
/**
 * Template: Événements co-organisés
 * Affiche la liste des événements où l'organisation est co-organisatrice
 */

if ( ! defined( 'ABSPATH' ) ) exit();

$current_user_id = get_current_user_id();
$org_name = EL_Coorg_Helpers::get_organisation_name( $current_user_id );

// Récupérer toutes les co-organisations où je suis co-organisateur
$all_coorganisations = EL_Event_Coorganisation::get_for_organisation( $current_user_id );

// Séparer par statut
$coorganisations_en_cours = array_filter( $all_coorganisations, function( $c ) { return $c->statut === 'en_cours'; } );
$coorganisations_acceptees = array_filter( $all_coorganisations, function( $c ) { return $c->statut === 'acceptee'; } );
$coorganisations_refusees = array_filter( $all_coorganisations, function( $c ) { return $c->statut === 'refusee'; } );
$coorganisations_retirees = array_filter( $all_coorganisations, function( $c ) { return $c->statut === 'retiree'; } );

?>

<div class="vendor_wrap">

    <?php echo el_get_template( '/vendor/sidebar.php' ); ?>

    <div class="contents">
        <?php echo el_get_template( '/vendor/heading.php' ); ?>

        <div class="el_coorg_coorganisations_page">

            <div class="el_coorg_header">
                <h2><?php esc_html_e( 'Événements co-organisés', 'eventlist' ); ?></h2>
                <p class="el_coorg_subtitle">
                    <?php esc_html_e( 'Gérez les événements que vous co-organisez avec vos partenaires', 'eventlist' ); ?>
                </p>
            </div>

            <!-- Invitations en attente -->
            <?php if ( ! empty( $coorganisations_en_cours ) ) : ?>
            <div class="el_coorg_section">
                <h3><?php esc_html_e( 'Invitations en attente', 'eventlist' ); ?></h3>
                <div class="el_coorg_cards">
                    <?php foreach ( $coorganisations_en_cours as $coorg ) :
                        $event = get_post( $coorg->event_id );
                        if ( ! $event ) continue;

                        $organizer_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_principale_id );
                        $organizer_data = EL_Coorg_Helpers::get_organisation_data( $coorg->organisation_principale_id );

                        // Récupérer les dates de l'événement
                        $event_date_start = get_post_meta( $event->ID, OVA_METABOX_EVENT . 'date_start_time', true );
                        $event_date_end = get_post_meta( $event->ID, OVA_METABOX_EVENT . 'date_end_time', true );
                        $event_address = get_post_meta( $event->ID, OVA_METABOX_EVENT . 'address', true );
                    ?>
                    <div class="el_coorg_card el_coorg_card_pending">
                        <div class="el_coorg_card_header">
                            <h4><?php echo esc_html( $event->post_title ); ?></h4>
                            <?php echo EL_Coorg_Helpers::get_status_badge( 'en_cours' ); ?>
                        </div>
                        <div class="el_coorg_card_body">
                            <p><strong><?php esc_html_e( 'Organisateur principal :', 'eventlist' ); ?></strong>
                                <?php echo esc_html( $organizer_name ); ?>
                            </p>
                            <p><strong><?php esc_html_e( 'Rôle proposé :', 'eventlist' ); ?></strong>
                                <?php echo EL_Coorg_Helpers::get_role_label( $coorg->role ); ?>
                            </p>
                            <?php if ( $event_date_start ) : ?>
                                <p><strong><?php esc_html_e( 'Date :', 'eventlist' ); ?></strong>
                                    <?php echo date_i18n( get_option( 'date_format' ), $event_date_start ); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ( $event_address ) : ?>
                                <p><strong><?php esc_html_e( 'Lieu :', 'eventlist' ); ?></strong>
                                    <?php echo esc_html( $event_address ); ?>
                                </p>
                            <?php endif; ?>
                            <p><strong><?php esc_html_e( 'Invitation reçue le :', 'eventlist' ); ?></strong>
                                <?php echo EL_Coorg_Helpers::format_date( $coorg->date_invitation ); ?>
                            </p>
                        </div>
                        <div class="el_coorg_card_actions">
                            <button
                                type="button"
                                class="el_coorg_btn_accept"
                                data-coorg-id="<?php echo esc_attr( $coorg->id ); ?>"
                                data-action="accept_event_coorganisation"
                            >
                                <?php esc_html_e( 'Accepter', 'eventlist' ); ?>
                            </button>
                            <button
                                type="button"
                                class="el_coorg_btn_refuse"
                                data-coorg-id="<?php echo esc_attr( $coorg->id ); ?>"
                                data-action="refuse_event_coorganisation"
                            >
                                <?php esc_html_e( 'Refuser', 'eventlist' ); ?>
                            </button>
                            <a
                                href="<?php echo get_permalink( $event->ID ); ?>"
                                target="_blank"
                                class="el_coorg_btn_secondary"
                            >
                                <?php esc_html_e( 'Voir l\'événement', 'eventlist' ); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Événements co-organisés (acceptés) -->
            <?php if ( ! empty( $coorganisations_acceptees ) ) : ?>
            <div class="el_coorg_section">
                <h3><?php esc_html_e( 'Mes événements co-organisés', 'eventlist' ); ?></h3>
                <div class="el_coorg_table_wrapper">
                    <table class="el_coorg_table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Organisateur principal', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Mon rôle', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Date début', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Lieu', 'eventlist' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $coorganisations_acceptees as $coorg ) :
                                $event = get_post( $coorg->event_id );
                                if ( ! $event ) continue;

                                $organizer_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_principale_id );
                                $event_date_start = get_post_meta( $event->ID, OVA_METABOX_EVENT . 'date_start_time', true );
                                $event_address = get_post_meta( $event->ID, OVA_METABOX_EVENT . 'address', true );
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $event->post_title ); ?></strong>
                                    <?php if ( $event->post_status === 'draft' ) : ?>
                                        <span class="el_coorg_badge el_coorg_badge_draft"><?php esc_html_e( 'Brouillon', 'eventlist' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $organizer_name ); ?></td>
                                <td><?php echo EL_Coorg_Helpers::get_role_label( $coorg->role ); ?></td>
                                <td>
                                    <?php
                                    if ( $event_date_start ) {
                                        echo date_i18n( get_option( 'date_format' ), $event_date_start );
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $event_address ? esc_html( $event_address ) : '—'; ?></td>
                                <td>
                                    <a
                                        href="<?php echo get_permalink( $event->ID ); ?>"
                                        target="_blank"
                                        class="el_coorg_btn_view"
                                    >
                                        <?php esc_html_e( 'Voir', 'eventlist' ); ?>
                                    </a>
                                    <button
                                        type="button"
                                        class="el_coorg_btn_retire"
                                        data-coorg-id="<?php echo esc_attr( $coorg->id ); ?>"
                                        data-action="retire_event_coorganisation"
                                    >
                                        <?php esc_html_e( 'Se retirer', 'eventlist' ); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Événements passés/refusés -->
            <?php
            $history = array_merge( $coorganisations_refusees, $coorganisations_retirees );
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
                                    <th><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
                                    <th><?php esc_html_e( 'Organisateur principal', 'eventlist' ); ?></th>
                                    <th><?php esc_html_e( 'Date', 'eventlist' ); ?></th>
                                    <th><?php esc_html_e( 'Statut', 'eventlist' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $history as $coorg ) :
                                    $event = get_post( $coorg->event_id );
                                    $event_title = $event ? $event->post_title : __( '(événement supprimé)', 'eventlist' );
                                    $organizer_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_principale_id );
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $event_title ); ?></td>
                                    <td><?php echo esc_html( $organizer_name ); ?></td>
                                    <td><?php echo EL_Coorg_Helpers::format_date( $coorg->date_reponse ); ?></td>
                                    <td><?php echo EL_Coorg_Helpers::get_status_badge( $coorg->statut ); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( empty( $all_coorganisations ) ) : ?>
            <div class="el_coorg_empty_state">
                <p><?php esc_html_e( 'Vous n\'avez pas encore d\'événements co-organisés.', 'eventlist' ); ?></p>
                <p><?php esc_html_e( 'Lorsqu\'un partenaire vous invitera à co-organiser un événement, vous verrez les invitations ici.', 'eventlist' ); ?></p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle historique
    $('.el_coorg_toggle_section').on('click', function() {
        $(this).next('.el_coorg_section_content').slideToggle();
        $(this).find('.el_coorg_toggle_icon').text(function(i, text) {
            return text === '▼' ? '▲' : '▼';
        });
    });

    // Actions sur les co-organisations
    $(document).on('click', '[data-action]', function() {
        const action = $(this).data('action');
        const coorgId = $(this).data('coorg-id');

        let confirmMsg = '';
        if (action === 'retire_event_coorganisation') {
            confirmMsg = '<?php esc_html_e( 'Êtes-vous sûr de vouloir vous retirer de cet événement ?', 'eventlist' ); ?>';
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
                coorg_id: coorgId
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
