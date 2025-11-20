<?php
/**
 * Template Part: Affichage des co-organisateurs d'un événement (front-end public)
 *
 * @param int $event_id ID de l'événement (optionnel, utilise get_the_ID() par défaut)
 */

if ( ! defined( 'ABSPATH' ) ) exit();

$event_id = isset( $event_id ) ? $event_id : get_the_ID();

// Récupérer les co-organisateurs acceptés
$coorganisers = EL_Event_Coorganisation::get_accepted_coorganisers( $event_id );

if ( empty( $coorganisers ) ) {
    return;
}

?>

<div class="el_event_coorganisers_section">
    <h3 class="el_event_coorganisers_title">
        <?php esc_html_e( 'Co-organisé avec', 'eventlist' ); ?>
    </h3>

    <div class="el_event_coorganisers_list">
        <?php foreach ( $coorganisers as $coorg ) :
            $org_data = EL_Coorg_Helpers::get_organisation_data( $coorg->organisation_coorganisatrice_id );

            if ( empty( $org_data ) ) {
                continue;
            }

            // Récupérer l'URL de l'archive de l'organisation
            $org_user = get_userdata( $coorg->organisation_coorganisatrice_id );
            $org_url = $org_user ? get_author_posts_url( $coorg->organisation_coorganisatrice_id ) : '';
        ?>

        <div class="el_event_coorganiser_item">
            <div class="el_coorganiser_content">
                <?php if ( ! empty( $org_data['logo'] ) ) :
                    $logo_url = wp_get_attachment_image_url( $org_data['logo'], 'thumbnail' );
                    if ( $logo_url ) :
                ?>
                    <div class="el_coorganiser_logo">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $org_data['name'] ); ?>">
                    </div>
                <?php
                    endif;
                endif;
                ?>

                <div class="el_coorganiser_info">
                    <h4 class="el_coorganiser_name">
                        <?php if ( $org_url ) : ?>
                            <a href="<?php echo esc_url( $org_url ); ?>">
                                <?php echo esc_html( $org_data['name'] ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $org_data['name'] ); ?>
                        <?php endif; ?>
                    </h4>

                    <?php if ( $coorg->role && $coorg->role !== 'co-organisateur' ) : ?>
                        <span class="el_coorganiser_role">
                            <?php echo EL_Coorg_Helpers::get_role_label( $coorg->role ); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ( ! empty( $org_data['city'] ) ) : ?>
                        <p class="el_coorganiser_location">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <?php echo esc_html( $org_data['city'] ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ( $org_url ) : ?>
                    <div class="el_coorganiser_actions">
                        <a href="<?php echo esc_url( $org_url ); ?>" class="el_coorganiser_link">
                            <?php esc_html_e( 'Voir le profil', 'eventlist' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
</div>
