<?php if( ! defined( 'ABSPATH' ) ) exit();

get_header();

$author_id = get_query_var( 'author' );

// V1 Le Hiboo - Utiliser le nom de l'organisation au lieu du nom d'utilisateur WordPress
// Priorité : org_display_name > display_name > WordPress display_name
$org_display_name = get_user_meta( $author_id, 'org_display_name', true );
$user_display_name = get_user_meta( $author_id, 'display_name', true );
$wp_display_name = get_the_author_meta( 'display_name', $author_id );

$display_name = ! empty( $org_display_name ) ? $org_display_name : ( ! empty( $user_display_name ) ? $user_display_name : $wp_display_name );

// V1 Le Hiboo - Récupérer la description de l'utilisateur
$user_description = get_user_meta( $author_id, 'description', true );

$archive_type = 'type1'; // List card layout (vertical cards with image on top)
$layout_column = 'three-column'; // 3 columns grid

$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

// V1 Le Hiboo - Récupérer les données pour le header
$org_cover_image = get_user_meta( $author_id, 'org_cover_image', true );
$author_id_image = get_user_meta( $author_id, 'author_id_image', true );
if ( $author_id_image ) {
	$avatar_url = wp_get_attachment_image_url($author_id_image, 'medium') ? wp_get_attachment_image_url($author_id_image, 'medium') : get_avatar_url($author_id);
} else {
	$avatar_url = get_avatar_url($author_id);
}

?>

<?php $global_layout = apply_filters( 'meup_theme_sidebar','' ); ?>

<!-- Hero Header Section (Outside main container for full width) -->
<div class="author_hero_header">
	<?php if ( $org_cover_image ) : ?>
		<div class="hero_cover_image">
			<img src="<?php echo esc_url( wp_get_attachment_image_url( $org_cover_image, 'full' ) ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" />
			<div class="hero_overlay"></div>
		</div>
	<?php endif; ?>

	<div class="hero_content">
		<div class="hero_avatar">
			<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" />
			<span class="verified_badge" title="<?php esc_attr_e('Verified Organizer', 'eventlist'); ?>">
				<i class="fas fa-check"></i>
			</span>
		</div>

		<div class="hero_info">
			<h1 class="hero_name"><?php echo esc_html( $display_name ); ?></h1>
			<?php
			$user_job = get_user_meta( $author_id, 'user_job', true );
			if ( $user_job ) : ?>
				<p class="hero_job"><?php echo esc_html( $user_job ); ?></p>
			<?php endif; ?>
			<?php ova_event_author_rating_display_by_id( $author_id ); ?>
		</div>

		<div class="hero_actions">
			<?php if( apply_filters( 'el_single_event_show_send_message_btn', true ) ){ ?>
				<a href="#contact-form" class="btn_primary btn_contact">
					<i class="icon_mail_alt"></i>
					<?php esc_html_e( 'Contact', 'eventlist' ); ?>
				</a>
			<?php } ?>
			<button class="btn_secondary btn_share" aria-label="<?php esc_attr_e('Share Profile', 'eventlist'); ?>">
				<i class="fas fa-share-alt"></i>
				<?php esc_html_e( 'Share', 'eventlist' ); ?>
			</button>
		</div>
	</div>
</div>

<!-- Main Content Container -->
<div class="wrap_site <?php echo esc_attr($global_layout); ?>">
	<div id="main-content" class="main author_main_wrapper">

		<div class="author_page author_page_modern">

			<!-- SIDEBAR GAUCHE -->
			<div class="author_page_sidebar">
				<?php do_action( 'el_author_info' ); ?>
			</div>

			<!-- CONTENU PRINCIPAL DROITE -->
			<div class="author_main_content">

		<!-- Statistics Section -->
		<div class="author_stats_section">
			<?php
			$total_events = count_user_posts( $author_id, 'event' );
			$opening_events = new WP_Query(array(
				'post_type' => 'event',
				'author' => $author_id,
				'meta_query' => array(
					array(
						'key' => 'event_status',
						'value' => 'opening',
					)
				),
				'posts_per_page' => -1
			));
			$opening_count = $opening_events->found_posts;
			wp_reset_postdata();

			// Récupérer les infos pratiques
			$org_video = get_user_meta( $author_id, 'org_video', true );
			$org_event_type = get_user_meta( $author_id, 'org_event_type', true );
			$org_stationnement = get_user_meta( $author_id, 'org_stationnement', true );
			$org_pmr = get_user_meta( $author_id, 'org_pmr', true );
			$org_restauration = get_user_meta( $author_id, 'org_restauration', true );
			$org_boisson = get_user_meta( $author_id, 'org_boisson', true );

			// Récupérer les descriptions complémentaires
			$org_pmr_infos = get_user_meta( $author_id, 'org_pmr_infos', true );
			$org_restauration_infos = get_user_meta( $author_id, 'org_restauration_infos', true );
			$org_boisson_infos = get_user_meta( $author_id, 'org_boisson_infos', true );
			?>
			<div class="stats_grid">
				<div class="stat_card">
					<div class="stat_icon">
						<i class="icon_calendar"></i>
					</div>
					<div class="stat_content">
						<span class="stat_value"><?php echo esc_html( $total_events ); ?></span>
						<span class="stat_label"><?php esc_html_e( 'Total Events', 'eventlist' ); ?></span>
					</div>
				</div>
				<div class="stat_card stat_card_active">
					<div class="stat_icon">
						<i class="icon_calendar"></i>
					</div>
					<div class="stat_content">
						<span class="stat_value"><?php echo esc_html( $opening_count ); ?></span>
						<span class="stat_label"><?php esc_html_e( 'Active Events', 'eventlist' ); ?></span>
					</div>
				</div>
				<div class="stat_card">
					<div class="stat_icon">
						<i class="icon_star"></i>
					</div>
					<div class="stat_content">
						<span class="stat_value">4.8</span>
						<span class="stat_label"><?php esc_html_e( 'Average Rating', 'eventlist' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Events Section -->
		<div class="event_list_section">

			<!-- Description AU-DESSUS du titre événements -->
			<?php if ($user_description) : ?>
				<div class="author_description_block">
					<h2 class="description_title">Description</h2>
					<div class="description_content">
						<?php echo wp_kses_post( wpautop( $user_description ) ); ?>
					</div>

					<!-- Infos Pratiques -->
					<?php if ( ($org_pmr && $org_pmr === 'oui') || ($org_restauration && $org_restauration === 'oui') || ($org_boisson && $org_boisson === 'oui') || $org_stationnement || $org_event_type ) : ?>
						<div class="practical_info">
							<?php if ( $org_pmr && $org_pmr === 'oui' ) : ?>
								<div class="info_item" data-tooltip="<?php echo esc_attr( $org_pmr_infos ? $org_pmr_infos : __( 'Établissement accessible aux personnes à mobilité réduite', 'eventlist' ) ); ?>">
									<i class="fas fa-wheelchair"></i>
									<span><?php esc_html_e( 'Accessibilité PMR', 'eventlist' ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $org_restauration && $org_restauration === 'oui' ) : ?>
								<div class="info_item" data-tooltip="<?php echo esc_attr( $org_restauration_infos ? $org_restauration_infos : __( 'Restauration disponible sur place', 'eventlist' ) ); ?>">
									<i class="fas fa-utensils"></i>
									<span><?php esc_html_e( 'Restauration sur place', 'eventlist' ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $org_boisson && $org_boisson === 'oui' ) : ?>
								<div class="info_item" data-tooltip="<?php echo esc_attr( $org_boisson_infos ? $org_boisson_infos : __( 'Boissons disponibles sur place', 'eventlist' ) ); ?>">
									<i class="fas fa-glass-cheers"></i>
									<span><?php esc_html_e( 'Boisson sur place', 'eventlist' ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $org_stationnement ) : ?>
								<div class="info_item" data-tooltip="<?php echo esc_attr( $org_stationnement ); ?>">
									<i class="fas fa-parking"></i>
									<span><?php esc_html_e( 'Stationnement', 'eventlist' ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $org_event_type ) : ?>
								<?php
								$event_type_label = '';
								if ( $org_event_type === 'interieur' ) {
									$event_type_label = __( 'Événements en intérieur', 'eventlist' );
								} elseif ( $org_event_type === 'exterieur' ) {
									$event_type_label = __( 'Événements en extérieur', 'eventlist' );
								} elseif ( $org_event_type === 'mixte' ) {
									$event_type_label = __( 'Événements en intérieur et extérieur', 'eventlist' );
								}
								?>
								<div class="info_item" data-tooltip="<?php echo esc_attr( $event_type_label ); ?>">
									<i class="fas fa-calendar-alt"></i>
									<span><?php esc_html_e( 'Type d\'événements', 'eventlist' ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="section_header">
				<h2 class="section_title">
					<i class="icon_calendar"></i>
					<?php esc_html_e( 'Événements', 'eventlist' ); ?>
				</h2>
				<div class="filter_wrap">
					<form method="GET" class="filter_form">
						<select name="status" class="form-select">
							<option value="" <?php selected( $status, "" ); ?>><?php esc_html_e( 'Tous les événements', 'eventlist' ); ?></option>
							<option value="opening" <?php selected( $status, "opening" ); ?>><?php esc_html_e( 'En cours', 'eventlist' ); ?></option>
							<option value="upcoming" <?php selected( $status, "upcoming" ); ?>><?php esc_html_e( 'À venir', 'eventlist' ); ?></option>
							<option value="past" <?php selected( $status, "past" ); ?>><?php esc_html_e( 'Terminés', 'eventlist' ); ?></option>
						</select>
						<button class="btn_filter" type="submit">
							<i class="icon_search"></i>
						</button>
					</form>
				</div>
			</div>
		
		
			<?php if( have_posts() ): ?>

				<?php
					/**
					 * Hook: el_before_archive_loop
					 * @hooked:
					 */
					do_action( 'el_before_archive_loop' );
				?>

						<div id="el_main_content">

							<div class="event_archive <?php echo esc_attr( $archive_type ); ?> <?php echo esc_attr( $layout_column ); ?>">

								<?php while ( have_posts() ) : the_post(); ?>

									<?php el_get_template_part( 'content', 'event-'.sanitize_file_name( $archive_type ) ); ?>

								<?php endwhile; wp_reset_query(); // end of the loop. ?>

							</div>

						</div>

				<?php
					/**
					 * Hook: el_after_archive_loop.
					 *
					 * @hooked el_pagination - 10
					 */
					do_action( 'el_after_archive_loop' );
				?>
			<?php else : ?>
				<div class="no_events_found">
					<i class="fas fa-calendar-times"></i>
					<p><?php esc_html_e('No events found', 'eventlist') ?></p>
				</div>
			<?php endif; ?>

		</div><!-- .event_list_section -->

			</div><!-- .author_main_content -->

		</div><!-- .author_page -->

	</div><!-- #main-content -->
</div><!-- .wrap_site -->

<?php

get_footer();