<?php
/**
 * Template: Indicateurs (Page teaser - Prochainement)
 * 
 * Affiche une page teaser pour la fonctionnalité Indicateurs
 * qui sera disponible prochainement.
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) exit();
?>

<div class="vendor_wrap">
    <?php echo el_get_template( 'vendor/sidebar.php' ); ?>

    <div class="contents">
        <?php echo lehiboo_render_vendor_header_menu(); ?>
        <div class="vendor_indicateurs_teaser">
            
            <!-- Header -->
            <div class="teaser_header">
                <span class="teaser_badge">
                    <i class="fas fa-lock"></i>
                    <?php esc_html_e( 'Prochainement', 'eventlist' ); ?>
                </span>
                <h1 class="teaser_title">
                    <?php esc_html_e( 'Indicateurs de Performance', 'eventlist' ); ?>
                </h1>
                <p class="teaser_subtitle">
                    <?php esc_html_e( 'Analysez vos performances et optimisez vos activités grâce à des données concrètes.', 'eventlist' ); ?>
                </p>
            </div>

            <!-- Image teaser avec overlay -->
            <div class="teaser_image_wrapper">
                <img 
                    src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/kpi-teaser-dashboard.png" 
                    alt="<?php esc_attr_e( 'Aperçu des indicateurs', 'eventlist' ); ?>"
                    class="teaser_image"
                >
                <div class="teaser_image_overlay">
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <!-- KPIs prévus -->
            <div class="teaser_features">
                <h3><?php esc_html_e( 'Indicateurs disponibles prochainement', 'eventlist' ); ?></h3>
                
                <div class="features_grid">
                    <div class="feature_card">
                        <i class="fas fa-users"></i>
                        <span><?php esc_html_e( 'Nombre de réservations', 'eventlist' ); ?></span>
                    </div>
                    <div class="feature_card">
                        <i class="fas fa-chart-pie"></i>
                        <span><?php esc_html_e( 'Taux de remplissage', 'eventlist' ); ?></span>
                    </div>
                    <div class="feature_card">
                        <i class="fas fa-eye"></i>
                        <span><?php esc_html_e( 'Vues de vos activités', 'eventlist' ); ?></span>
                    </div>
                    <div class="feature_card">
                        <i class="fas fa-heart"></i>
                        <span><?php esc_html_e( 'Favoris', 'eventlist' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="teaser_cta">
                <p><?php esc_html_e( 'Nous travaillons activement sur cette fonctionnalité. Restez connecté !', 'eventlist' ); ?></p>
            </div>

        </div>
    </div>
</div>
