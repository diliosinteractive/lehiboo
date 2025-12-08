<?php
/**
 * V1 Le Hiboo - Dashboard Partenaires (Admin)
 * Interface admin pour consulter le dashboard des partenaires
 */

defined( 'ABSPATH' ) || exit;

// Récupérer le vendor_id sélectionné
$selected_vendor_id = isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0;
?>
<style>
/* Styles inline pour garantir l'affichage */
.el-admin-vendor-dashboard { max-width: 1400px; }
.el-admin-vendor-dashboard * { box-sizing: border-box; }
.el-vendor-selector-card { background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; margin: 20px 0; }
.el-vendor-selector-row { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.el-vendor-select { min-width: 400px; }
.el-vendor-info-card { background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); color: #fff; padding: 25px; border-radius: 8px; margin: 20px 0; display: flex; align-items: center; gap: 20px; }
.el-vendor-avatar img { border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); }
.el-vendor-details h2 { color: #fff; margin: 0 0 10px; font-size: 24px; }
.el-vendor-details p { margin: 5px 0; opacity: 0.9; }
.el-vendor-details .button { margin-top: 10px; margin-right: 10px; }
.el-date-filter-card { background: #fff; padding: 15px 20px; border: 1px solid #c3c4c7; border-radius: 4px; margin: 20px 0; }
.el-date-filter-row { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.el-custom-dates { display: flex; align-items: center; gap: 10px; }
.el-dashboard-content { background: #fff; border: 1px solid #c3c4c7; border-top: none; padding: 25px; margin-bottom: 20px; }
.el-dashboard-content h3 { margin-top: 0; padding-bottom: 15px; border-bottom: 2px solid #2271b1; font-size: 18px; }
.el-admin-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 25px 0; }
@media (max-width: 1200px) { .el-admin-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .el-admin-kpi-grid { grid-template-columns: 1fr; } }
.el-admin-kpi-card { background: #fff; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #e5e5e5; }
.el-admin-kpi-card .kpi-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.el-admin-kpi-card .kpi-icon .dashicons { font-size: 28px; width: 28px; height: 28px; color: #fff; }
.el-admin-kpi-card .kpi-value { font-size: 32px; font-weight: 700; color: #1d2327; line-height: 1.2; }
.el-admin-kpi-card .kpi-label { font-size: 12px; color: #646970; margin-top: 4px; text-transform: uppercase; }
.el-admin-kpi-card.kpi-primary .kpi-icon, .el-admin-kpi-card.kpi-views .kpi-icon { background: linear-gradient(135deg, #2271b1, #135e96); }
.el-admin-kpi-card.kpi-success .kpi-icon, .el-admin-kpi-card.kpi-bookings .kpi-icon { background: linear-gradient(135deg, #00a32a, #007017); }
.el-admin-kpi-card.kpi-info .kpi-icon { background: linear-gradient(135deg, #72aee6, #4f94d4); }
.el-admin-kpi-card.kpi-warning .kpi-icon, .el-admin-kpi-card.kpi-conversion .kpi-icon { background: linear-gradient(135deg, #dba617, #b88c00); }
.el-admin-kpi-card.kpi-visitors .kpi-icon { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
.el-admin-stats-section { background: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin: 25px 0; }
.el-admin-stats-section h4 { margin: 0 0 20px; font-size: 15px; display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 1px solid #e5e5e5; }
.el-admin-stats-section h4 .dashicons { color: #2271b1; }
.age-avg-badge { display: inline-flex; flex-direction: column; align-items: center; background: linear-gradient(135deg, #2271b1, #135e96); color: #fff; padding: 15px 30px; border-radius: 8px; }
.age-avg-badge strong { font-size: 36px; line-height: 1; }
.age-avg-badge span { font-size: 12px; margin-top: 5px; opacity: 0.9; }
.age-distribution, .el-admin-device-stats, .el-admin-browser-stats, .el-admin-city-stats, .el-admin-cities-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
.age-group-item, .city-item, .device-item, .browser-item { background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #e5e5e5; }
.age-group-header, .city-header, .device-header, .browser-header { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; }
.age-group-label, .city-name, .device-label, .browser-name { font-weight: 600; color: #1d2327; }
.age-group-count, .city-count, .device-count, .browser-count { color: #646970; }
.age-group-bar, .city-bar, .device-bar, .browser-bar { height: 10px; background: #e5e5e5; border-radius: 5px; overflow: hidden; }
.age-group-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, #2271b1, #72aee6); }
.city-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, #9b59b6, #a569bd); }
.device-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, #00a32a, #46b450); }
.browser-fill { height: 100%; border-radius: 5px; background: linear-gradient(90deg, #e67e22, #f39c12); }
.el-admin-gender-stats .gender-chart { display: flex; gap: 20px; flex-wrap: wrap; }
.gender-item { background: #fff; padding: 20px 25px; border-radius: 8px; min-width: 140px; text-align: center; border: 1px solid #e5e5e5; }
.gender-item.gender-male { border-left: 4px solid #2271b1; }
.gender-item.gender-female { border-left: 4px solid #e74c3c; }
.gender-item.gender-other { border-left: 4px solid #9b59b6; }
.gender-label { font-weight: 600; margin-bottom: 5px; font-size: 14px; }
.gender-value { color: #646970; font-size: 13px; }
.el-admin-interactions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
@media (max-width: 1200px) { .el-admin-interactions-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .el-admin-interactions-grid { grid-template-columns: 1fr; } }
.interaction-item { background: #fff; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid #e5e5e5; }
.interaction-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.interaction-icon .dashicons { font-size: 22px; width: 22px; height: 22px; color: #fff; }
.interaction-item.wishlist .interaction-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.interaction-item.contact .interaction-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
.interaction-item.share .interaction-icon { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
.interaction-item.engagement .interaction-icon { background: linear-gradient(135deg, #f39c12, #d68910); }
.interaction-value { font-size: 26px; font-weight: 700; color: #1d2327; }
.interaction-label { font-size: 12px; color: #646970; text-transform: uppercase; }
.el-admin-chart-container { background: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin: 25px 0; }
.el-admin-chart-container h4 { margin: 0 0 20px; font-size: 15px; display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 1px solid #e5e5e5; }
.chart-wrapper { background: #fff; border-radius: 6px; padding: 20px; height: 350px; border: 1px solid #e5e5e5; }
.el-admin-secondary-metrics { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 25px; padding-top: 20px; border-top: 2px solid #e5e5e5; }
.el-admin-secondary-metrics .metric-item { display: flex; flex-direction: column; padding: 15px 25px; background: #f9f9f9; border-radius: 6px; border: 1px solid #e5e5e5; min-width: 200px; }
.el-admin-secondary-metrics .metric-label { font-size: 12px; color: #646970; margin-bottom: 5px; text-transform: uppercase; }
.el-admin-secondary-metrics .metric-value { font-size: 20px; font-weight: 600; color: #1d2327; }
.el-admin-secondary-metrics .metric-warning { color: #d63638; }
.el-no-vendor-selected { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 60px 40px; text-align: center; margin: 20px 0; }
.el-no-vendor-selected .dashicons { font-size: 64px; width: 64px; height: 64px; color: #c3c4c7; margin-bottom: 20px; display: block; }
.el-no-vendor-selected p { font-size: 16px; color: #646970; margin: 0; }
</style>
<?php

// Récupérer la plage de dates
$range = isset( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7_day';
$date_range = el_get_date_range_from_filter( $range, $_GET );

// Onglet actif
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'kpi';

// Récupérer tous les vendors (utilisateurs qui ont des événements)
global $wpdb;
$vendors_query = "
	SELECT DISTINCT u.ID, u.display_name, u.user_email,
		   COUNT(p.ID) as event_count
	FROM {$wpdb->users} u
	INNER JOIN {$wpdb->posts} p ON u.ID = p.post_author
	WHERE p.post_type = 'event'
	AND p.post_status IN ('publish', 'draft', 'pending', 'private')
	GROUP BY u.ID
	ORDER BY u.display_name ASC
";
$vendors = $wpdb->get_results( $vendors_query );

// Récupérer les infos du vendor sélectionné
$selected_vendor = null;
if ( $selected_vendor_id ) {
	$selected_vendor = get_userdata( $selected_vendor_id );
}

// Base URL pour les liens
$base_url = admin_url( 'admin.php?page=el_vendor_dashboard' );

?>

<div class="wrap el-admin-vendor-dashboard">
	<h1><?php esc_html_e( 'Dashboard Partenaires', 'eventlist' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Consultez les statistiques et analytics de chaque partenaire.', 'eventlist' ); ?>
	</p>

	<!-- Sélecteur de partenaire -->
	<div class="el-vendor-selector-card">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="el_vendor_dashboard" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>" />

			<div class="el-vendor-selector-row">
				<label for="vendor_id">
					<strong><?php esc_html_e( 'Sélectionner un partenaire :', 'eventlist' ); ?></strong>
				</label>
				<select name="vendor_id" id="vendor_id" class="el-vendor-select">
					<option value=""><?php esc_html_e( '-- Choisir un partenaire --', 'eventlist' ); ?></option>
					<?php foreach ( $vendors as $vendor ) : ?>
						<option value="<?php echo esc_attr( $vendor->ID ); ?>" <?php selected( $selected_vendor_id, $vendor->ID ); ?>>
							<?php echo esc_html( $vendor->display_name ); ?> (<?php echo esc_html( $vendor->user_email ); ?>) - <?php echo $vendor->event_count; ?> <?php esc_html_e( 'événement(s)', 'eventlist' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Afficher', 'eventlist' ); ?>
				</button>
			</div>
		</form>
	</div>

	<?php if ( $selected_vendor_id && $selected_vendor ) : ?>

		<!-- Informations du partenaire -->
		<div class="el-vendor-info-card">
			<div class="el-vendor-avatar">
				<?php echo get_avatar( $selected_vendor_id, 60 ); ?>
			</div>
			<div class="el-vendor-details">
				<h2><?php echo esc_html( $selected_vendor->display_name ); ?></h2>
				<p>
					<span class="dashicons dashicons-email"></span>
					<?php echo esc_html( $selected_vendor->user_email ); ?>
				</p>
				<p>
					<span class="dashicons dashicons-calendar-alt"></span>
					<?php
					$event_count = count( get_posts( array(
						'post_type' => 'event',
						'author' => $selected_vendor_id,
						'posts_per_page' => -1,
						'fields' => 'ids',
						'post_status' => 'any'
					) ) );
					printf( esc_html__( '%d événement(s) au total', 'eventlist' ), $event_count );
					?>
				</p>
				<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $selected_vendor_id ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Modifier le profil', 'eventlist' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=event&author=' . $selected_vendor_id ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Voir les événements', 'eventlist' ); ?>
				</a>
			</div>
		</div>

		<!-- Filtre de dates -->
		<div class="el-date-filter-card">
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="page" value="el_vendor_dashboard" />
				<input type="hidden" name="vendor_id" value="<?php echo esc_attr( $selected_vendor_id ); ?>" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>" />

				<div class="el-date-filter-row">
					<label><?php esc_html_e( 'Période :', 'eventlist' ); ?></label>

					<select name="range" id="date_range_select">
						<option value="7_day" <?php selected( $range, '7_day' ); ?>><?php esc_html_e( '7 derniers jours', 'eventlist' ); ?></option>
						<option value="month" <?php selected( $range, 'month' ); ?>><?php esc_html_e( 'Ce mois', 'eventlist' ); ?></option>
						<option value="last_month" <?php selected( $range, 'last_month' ); ?>><?php esc_html_e( 'Mois dernier', 'eventlist' ); ?></option>
						<option value="year" <?php selected( $range, 'year' ); ?>><?php esc_html_e( 'Cette année', 'eventlist' ); ?></option>
						<option value="custom" <?php selected( $range, 'custom' ); ?>><?php esc_html_e( 'Personnalisé', 'eventlist' ); ?></option>
					</select>

					<span class="el-custom-dates" style="<?php echo $range !== 'custom' ? 'display:none;' : ''; ?>">
						<input type="date" name="start_date" value="<?php echo isset( $_GET['start_date'] ) ? esc_attr( $_GET['start_date'] ) : ''; ?>" />
						<span><?php esc_html_e( 'au', 'eventlist' ); ?></span>
						<input type="date" name="end_date" value="<?php echo isset( $_GET['end_date'] ) ? esc_attr( $_GET['end_date'] ) : ''; ?>" />
					</span>

					<button type="submit" class="button">
						<?php esc_html_e( 'Appliquer', 'eventlist' ); ?>
					</button>
				</div>
			</form>
		</div>

		<!-- Onglets KPI / Analytics -->
		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'kpi' ), $base_url . '&vendor_id=' . $selected_vendor_id . '&range=' . $range ) ); ?>" class="nav-tab <?php echo $active_tab === 'kpi' ? 'nav-tab-active' : ''; ?>">
				<span class="dashicons dashicons-chart-bar"></span>
				<?php esc_html_e( 'KPI & Performance', 'eventlist' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'analytics' ), $base_url . '&vendor_id=' . $selected_vendor_id . '&range=' . $range ) ); ?>" class="nav-tab <?php echo $active_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
				<span class="dashicons dashicons-chart-line"></span>
				<?php esc_html_e( 'Analytics & Trafic', 'eventlist' ); ?>
			</a>
		</nav>

		<!-- Contenu du dashboard -->
		<div class="el-dashboard-content">
			<?php if ( $active_tab === 'kpi' ) : ?>
				<?php
				// Récupérer les statistiques pour le vendor sélectionné
				$participant_stats = el_get_vendor_participant_stats( $selected_vendor_id, $date_range );
				$financial_stats = el_get_vendor_financial_stats( $selected_vendor_id, $date_range );
				$popular_events = el_get_vendor_popular_events( $selected_vendor_id, 5, $date_range );
				?>

				<div class="el-admin-dashboard-kpi">
					<h3><?php esc_html_e( 'Indicateurs de Performance (KPI)', 'eventlist' ); ?></h3>

					<!-- KPI Principaux -->
					<div class="el-admin-kpi-grid">
						<!-- Total Participants -->
						<div class="el-admin-kpi-card kpi-primary">
							<div class="kpi-icon">
								<span class="dashicons dashicons-groups"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo number_format( $participant_stats['total_participants'], 0, ',', ' ' ); ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Participants', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Chiffre d'affaires -->
						<div class="el-admin-kpi-card kpi-success">
							<div class="kpi-icon">
								<span class="dashicons dashicons-money-alt"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo el_price( $financial_stats['total_revenue'] ); ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Chiffre d\'affaires', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Réservations -->
						<div class="el-admin-kpi-card kpi-info">
							<div class="kpi-icon">
								<span class="dashicons dashicons-calendar-alt"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo $financial_stats['completed_bookings']; ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Réservations confirmées', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Taux de conversion -->
						<div class="el-admin-kpi-card kpi-warning">
							<div class="kpi-icon">
								<span class="dashicons dashicons-chart-pie"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo round( $financial_stats['completion_rate'], 1 ); ?>%</div>
								<div class="kpi-label"><?php esc_html_e( 'Taux de complétion', 'eventlist' ); ?></div>
							</div>
						</div>
					</div>

					<!-- Statistiques d'âge -->
					<?php if ( $participant_stats['total_participants'] > 0 ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-groups"></span>
								<?php esc_html_e( 'Répartition par âge', 'eventlist' ); ?>
							</h4>
							<div class="el-admin-age-stats">
								<?php if ( $participant_stats['avg_age'] > 0 ) : ?>
									<div class="age-avg-badge">
										<strong><?php echo $participant_stats['avg_age']; ?></strong>
										<span><?php esc_html_e( 'Âge moyen', 'eventlist' ); ?></span>
									</div>
								<?php endif; ?>

								<div class="age-distribution">
									<?php
									$total_with_age = $participant_stats['total_participants'] - $participant_stats['age_groups']['unknown'];
									foreach ( $participant_stats['age_groups'] as $age_group => $count ) :
										if ( $age_group === 'unknown' || $count === 0 ) continue;
										$percentage = $total_with_age > 0 ? ( $count / $total_with_age ) * 100 : 0;
									?>
										<div class="age-group-item">
											<div class="age-group-header">
												<span class="age-group-label"><?php echo esc_html( $age_group . ' ans' ); ?></span>
												<span class="age-group-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
											</div>
											<div class="age-group-bar">
												<div class="age-group-fill" style="width: <?php echo $percentage; ?>%;"></div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- Statistiques de genre -->
					<?php if ( $participant_stats['total_participants'] > 0 ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-admin-users"></span>
								<?php esc_html_e( 'Répartition par genre', 'eventlist' ); ?>
							</h4>
							<div class="el-admin-gender-stats">
								<?php
								$total_with_gender = $participant_stats['total_participants'] - $participant_stats['gender_stats']['unknown'];
								$gender_labels = array(
									'male' => __( 'Hommes', 'eventlist' ),
									'female' => __( 'Femmes', 'eventlist' ),
									'other' => __( 'Autre', 'eventlist' )
								);
								?>
								<div class="gender-chart">
									<?php foreach ( $gender_labels as $gender => $label ) :
										$count = $participant_stats['gender_stats'][ $gender ];
										if ( $count === 0 ) continue;
										$percentage = $total_with_gender > 0 ? ( $count / $total_with_gender ) * 100 : 0;
									?>
										<div class="gender-item gender-<?php echo esc_attr( $gender ); ?>">
											<div class="gender-data">
												<div class="gender-label"><?php echo esc_html( $label ); ?></div>
												<div class="gender-value"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- Villes les plus représentées -->
					<?php if ( ! empty( $participant_stats['cities'] ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-location"></span>
								<?php esc_html_e( 'Villes d\'origine des participants', 'eventlist' ); ?>
							</h4>
							<div class="el-admin-cities-stats">
								<?php
								$city_count = 0;
								$max_cities = 10;
								foreach ( $participant_stats['cities'] as $city => $count ) :
									if ( $city_count >= $max_cities ) break;
									$percentage = ( $count / $participant_stats['total_participants'] ) * 100;
									$city_count++;
								?>
									<div class="city-item">
										<div class="city-header">
											<span class="city-name"><?php echo esc_html( $city ); ?></span>
											<span class="city-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
										</div>
										<div class="city-bar">
											<div class="city-fill" style="width: <?php echo $percentage; ?>%;"></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Événements les plus populaires -->
					<?php if ( ! empty( $popular_events ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Événements les plus populaires', 'eventlist' ); ?>
							</h4>
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Réservations', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Billets vendus', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Revenus', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $popular_events as $event ) : ?>
										<tr>
											<td>
												<a href="<?php echo esc_url( get_edit_post_link( $event['event_id'] ) ); ?>">
													<?php echo esc_html( $event['title'] ); ?>
												</a>
											</td>
											<td><?php echo $event['booking_count']; ?></td>
											<td><?php echo $event['total_tickets']; ?></td>
											<td><?php echo el_price( $event['total_revenue'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>

					<!-- Métriques secondaires -->
					<div class="el-admin-secondary-metrics">
						<div class="metric-item">
							<span class="metric-label"><?php esc_html_e( 'Valeur moyenne par réservation', 'eventlist' ); ?></span>
							<span class="metric-value"><?php echo el_price( $financial_stats['avg_booking_value'] ); ?></span>
						</div>
						<div class="metric-item">
							<span class="metric-label"><?php esc_html_e( 'Événements actifs', 'eventlist' ); ?></span>
							<span class="metric-value"><?php echo $financial_stats['unique_events']; ?></span>
						</div>
						<?php if ( $financial_stats['cancelled_bookings'] > 0 ) : ?>
							<div class="metric-item">
								<span class="metric-label"><?php esc_html_e( 'Réservations annulées', 'eventlist' ); ?></span>
								<span class="metric-value metric-warning"><?php echo $financial_stats['cancelled_bookings']; ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>

			<?php elseif ( $active_tab === 'analytics' ) : ?>
				<?php
				// Récupérer les analytics pour le vendor sélectionné
				$analytics = EL_Analytics::instance()->get_vendor_analytics( $selected_vendor_id, $date_range );
				$temporal_data = EL_Analytics::instance()->get_vendor_temporal_analytics( $selected_vendor_id, $date_range );
				?>

				<div class="el-admin-dashboard-analytics">
					<h3><?php esc_html_e( 'Analytics & Statistiques de Visite', 'eventlist' ); ?></h3>

					<!-- KPI Analytics Principaux -->
					<div class="el-admin-kpi-grid">
						<!-- Vues Totales -->
						<div class="el-admin-kpi-card kpi-views">
							<div class="kpi-icon">
								<span class="dashicons dashicons-visibility"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo number_format( $analytics['total_views'], 0, ',', ' ' ); ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Vues totales', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Visiteurs Uniques -->
						<div class="el-admin-kpi-card kpi-visitors">
							<div class="kpi-icon">
								<span class="dashicons dashicons-admin-users"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo number_format( $analytics['unique_visitors'], 0, ',', ' ' ); ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Visiteurs uniques', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Clics Réservation -->
						<div class="el-admin-kpi-card kpi-bookings">
							<div class="kpi-icon">
								<span class="dashicons dashicons-cart"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo number_format( $analytics['booking_clicks'], 0, ',', ' ' ); ?></div>
								<div class="kpi-label"><?php esc_html_e( 'Clics Réserver', 'eventlist' ); ?></div>
							</div>
						</div>

						<!-- Taux de Conversion -->
						<div class="el-admin-kpi-card kpi-conversion">
							<div class="kpi-icon">
								<span class="dashicons dashicons-chart-pie"></span>
							</div>
							<div class="kpi-content">
								<div class="kpi-value"><?php echo $analytics['conversion_rate']; ?>%</div>
								<div class="kpi-label"><?php esc_html_e( 'Taux de conversion', 'eventlist' ); ?></div>
							</div>
						</div>
					</div>

					<!-- Graphique Principal - Évolution Temporelle -->
					<div class="el-admin-chart-container">
						<h4>
							<span class="dashicons dashicons-chart-area"></span>
							<?php esc_html_e( 'Évolution des performances', 'eventlist' ); ?>
						</h4>
						<div class="chart-wrapper">
							<canvas id="el-admin-analytics-chart"
								data-labels="<?php echo esc_attr( json_encode( $temporal_data['labels'] ) ); ?>"
								data-views="<?php echo esc_attr( json_encode( $temporal_data['views'] ) ); ?>"
								data-bookings="<?php echo esc_attr( json_encode( $temporal_data['booking_clicks'] ) ); ?>"
								data-wishlists="<?php echo esc_attr( json_encode( $temporal_data['wishlist_adds'] ) ); ?>"
								data-contacts="<?php echo esc_attr( json_encode( $temporal_data['contact_clicks'] ) ); ?>"
								data-shares="<?php echo esc_attr( json_encode( $temporal_data['share_clicks'] ) ); ?>"
							></canvas>
						</div>
					</div>

					<!-- Interactions -->
					<div class="el-admin-stats-section">
						<h4>
							<span class="dashicons dashicons-admin-comments"></span>
							<?php esc_html_e( 'Interactions des visiteurs', 'eventlist' ); ?>
						</h4>

						<div class="el-admin-interactions-grid">
							<div class="interaction-item wishlist">
								<div class="interaction-icon">
									<span class="dashicons dashicons-heart"></span>
								</div>
								<div class="interaction-data">
									<div class="interaction-value"><?php echo number_format( $analytics['wishlist_adds'], 0, ',', ' ' ); ?></div>
									<div class="interaction-label"><?php esc_html_e( 'Ajouts aux favoris', 'eventlist' ); ?></div>
								</div>
							</div>

							<div class="interaction-item contact">
								<div class="interaction-icon">
									<span class="dashicons dashicons-email"></span>
								</div>
								<div class="interaction-data">
									<div class="interaction-value"><?php echo number_format( $analytics['contact_clicks'], 0, ',', ' ' ); ?></div>
									<div class="interaction-label"><?php esc_html_e( 'Clics Contact', 'eventlist' ); ?></div>
								</div>
							</div>

							<div class="interaction-item share">
								<div class="interaction-icon">
									<span class="dashicons dashicons-share"></span>
								</div>
								<div class="interaction-data">
									<div class="interaction-value"><?php echo number_format( $analytics['share_clicks'], 0, ',', ' ' ); ?></div>
									<div class="interaction-label"><?php esc_html_e( 'Partages', 'eventlist' ); ?></div>
								</div>
							</div>

							<div class="interaction-item engagement">
								<div class="interaction-icon">
									<span class="dashicons dashicons-star-filled"></span>
								</div>
								<div class="interaction-data">
									<div class="interaction-value"><?php echo $analytics['engagement_rate']; ?>%</div>
									<div class="interaction-label"><?php esc_html_e( "Taux d'engagement", 'eventlist' ); ?></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Statistiques Devices -->
					<?php if ( ! empty( $analytics['device_stats'] ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-smartphone"></span>
								<?php esc_html_e( 'Appareils utilisés', 'eventlist' ); ?>
							</h4>

							<div class="el-admin-device-stats">
								<?php
								$total_devices = array_sum( array_column( $analytics['device_stats'], 'count' ) );
								$device_labels = array(
									'mobile' => __( 'Mobile', 'eventlist' ),
									'tablet' => __( 'Tablette', 'eventlist' ),
									'desktop' => __( 'Desktop', 'eventlist' )
								);

								foreach ( $analytics['device_stats'] as $device ) :
									$device_type = $device['device_type'];
									$count = intval( $device['count'] );
									$percentage = $total_devices > 0 ? ( $count / $total_devices ) * 100 : 0;
									$label = isset( $device_labels[ $device_type ] ) ? $device_labels[ $device_type ] : ucfirst( $device_type );
								?>
									<div class="device-item device-<?php echo esc_attr( $device_type ); ?>">
										<div class="device-header">
											<span class="device-label"><?php echo esc_html( $label ); ?></span>
											<span class="device-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
										</div>
										<div class="device-bar">
											<div class="device-fill" style="width: <?php echo $percentage; ?>%;"></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Statistiques Navigateurs -->
					<?php if ( ! empty( $analytics['browser_stats'] ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<?php esc_html_e( 'Navigateurs utilisés', 'eventlist' ); ?>
							</h4>

							<div class="el-admin-browser-stats">
								<?php
								$total_browsers = array_sum( array_column( $analytics['browser_stats'], 'count' ) );
								foreach ( $analytics['browser_stats'] as $browser ) :
									$browser_name = $browser['browser'];
									$count = intval( $browser['count'] );
									$percentage = $total_browsers > 0 ? ( $count / $total_browsers ) * 100 : 0;
								?>
									<div class="browser-item">
										<div class="browser-header">
											<span class="browser-name"><?php echo esc_html( $browser_name ); ?></span>
											<span class="browser-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
										</div>
										<div class="browser-bar">
											<div class="browser-fill" style="width: <?php echo $percentage; ?>%;"></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Statistiques Villes -->
					<?php if ( ! empty( $analytics['city_stats'] ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-location"></span>
								<?php esc_html_e( 'Villes des visiteurs', 'eventlist' ); ?>
							</h4>

							<div class="el-admin-city-stats">
								<?php
								$total_cities = array_sum( array_column( $analytics['city_stats'], 'count' ) );
								foreach ( $analytics['city_stats'] as $city ) :
									$city_name = $city['city'];
									$count = intval( $city['count'] );
									$percentage = $total_cities > 0 ? ( $count / $total_cities ) * 100 : 0;
								?>
									<div class="city-item">
										<div class="city-header">
											<span class="city-name"><?php echo esc_html( $city_name ); ?></span>
											<span class="city-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
										</div>
										<div class="city-bar">
											<div class="city-fill" style="width: <?php echo $percentage; ?>%;"></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Top Événements par Vues -->
					<?php if ( ! empty( $analytics['top_events'] ) ) : ?>
						<div class="el-admin-stats-section">
							<h4>
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Événements les plus consultés', 'eventlist' ); ?>
							</h4>

							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
										<th><?php esc_html_e( 'Vues', 'eventlist' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $analytics['top_events'] as $event ) : ?>
										<tr>
											<td>
												<a href="<?php echo esc_url( get_edit_post_link( $event['event_id'] ) ); ?>">
													<?php echo esc_html( $event['title'] ); ?>
												</a>
											</td>
											<td><?php echo number_format( $event['views'], 0, ',', ' ' ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>

					<!-- Résumé des performances -->
					<div class="el-admin-secondary-metrics">
						<div class="metric-item">
							<span class="metric-label"><?php esc_html_e( 'Taux de rebond estimé', 'eventlist' ); ?></span>
							<span class="metric-value">
								<?php
								$bounce_rate = 100 - $analytics['engagement_rate'];
								echo round( $bounce_rate, 1 );
								?>%
							</span>
						</div>
						<div class="metric-item">
							<span class="metric-label"><?php esc_html_e( 'Vues par visiteur (moy.)', 'eventlist' ); ?></span>
							<span class="metric-value">
								<?php
								$views_per_visitor = $analytics['unique_visitors'] > 0 ?
									$analytics['total_views'] / $analytics['unique_visitors'] : 0;
								echo round( $views_per_visitor, 1 );
								?>
							</span>
						</div>
						<div class="metric-item">
							<span class="metric-label"><?php esc_html_e( 'Taux de mise en favoris', 'eventlist' ); ?></span>
							<span class="metric-value">
								<?php
								$wishlist_rate = $analytics['unique_visitors'] > 0 ?
									( $analytics['wishlist_adds'] / $analytics['unique_visitors'] ) * 100 : 0;
								echo round( $wishlist_rate, 1 );
								?>%
							</span>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

	<?php else : ?>
		<!-- Message si aucun partenaire sélectionné -->
		<div class="el-no-vendor-selected">
			<span class="dashicons dashicons-info"></span>
			<p><?php esc_html_e( 'Veuillez sélectionner un partenaire pour afficher son dashboard.', 'eventlist' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Afficher/masquer les dates personnalisées
	$('#date_range_select').on('change', function() {
		if ($(this).val() === 'custom') {
			$('.el-custom-dates').show();
		} else {
			$('.el-custom-dates').hide();
		}
	});

	// Initialiser Chart.js si disponible et si l'onglet analytics est actif
	if (typeof Chart !== 'undefined' && $('#el-admin-analytics-chart').length) {
		var canvas = document.getElementById('el-admin-analytics-chart');
		var ctx = canvas.getContext('2d');

		var labels = JSON.parse(canvas.dataset.labels || '[]');
		var views = JSON.parse(canvas.dataset.views || '[]');
		var bookings = JSON.parse(canvas.dataset.bookings || '[]');
		var wishlists = JSON.parse(canvas.dataset.wishlists || '[]');
		var contacts = JSON.parse(canvas.dataset.contacts || '[]');
		var shares = JSON.parse(canvas.dataset.shares || '[]');

		new Chart(ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: '<?php esc_html_e( 'Vues', 'eventlist' ); ?>',
						data: views,
						borderColor: '#2271b1',
						backgroundColor: 'rgba(34, 113, 177, 0.1)',
						tension: 0.3,
						fill: true
					},
					{
						label: '<?php esc_html_e( 'Clics Réserver', 'eventlist' ); ?>',
						data: bookings,
						borderColor: '#00a32a',
						backgroundColor: 'rgba(0, 163, 42, 0.1)',
						tension: 0.3,
						fill: true
					},
					{
						label: '<?php esc_html_e( 'Favoris', 'eventlist' ); ?>',
						data: wishlists,
						borderColor: '#dba617',
						backgroundColor: 'rgba(219, 166, 23, 0.1)',
						tension: 0.3,
						fill: true
					},
					{
						label: '<?php esc_html_e( 'Contacts', 'eventlist' ); ?>',
						data: contacts,
						borderColor: '#72aee6',
						backgroundColor: 'rgba(114, 174, 230, 0.1)',
						tension: 0.3,
						fill: true
					},
					{
						label: '<?php esc_html_e( 'Partages', 'eventlist' ); ?>',
						data: shares,
						borderColor: '#9b59b6',
						backgroundColor: 'rgba(155, 89, 182, 0.1)',
						tension: 0.3,
						fill: true
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom'
					}
				},
				scales: {
					y: {
						beginAtZero: true
					}
				}
			}
		});
	}

	// Select2 pour le sélecteur de vendor si disponible
	if (typeof $.fn.select2 !== 'undefined') {
		$('.el-vendor-select').select2({
			placeholder: '<?php esc_html_e( '-- Choisir un partenaire --', 'eventlist' ); ?>',
			allowClear: true,
			width: '400px'
		});
	}
});
</script>
