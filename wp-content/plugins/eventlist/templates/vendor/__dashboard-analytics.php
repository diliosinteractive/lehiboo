<?php
/**
 * Template pour afficher les analytics du dashboard partenaire
 */
if ( ! defined( 'ABSPATH' ) ) exit();

// Récupérer la plage de dates si définie
$range = isset( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7_day';
$date_range = el_get_date_range_from_filter( $range, $_GET );

// Récupérer les analytics
$vendor_id = get_current_user_id();
$analytics = EL_Analytics::instance()->get_vendor_analytics( $vendor_id, $date_range );

// Récupérer les données temporelles pour les graphiques
$temporal_data = EL_Analytics::instance()->get_vendor_temporal_analytics( $vendor_id, $date_range );

?>

<div class="el-dashboard-analytics">
	<h3 class="vendor_report"><?php esc_html_e( 'Analytics & Statistiques de Visite', 'eventlist' ); ?></h3>

	<!-- KPI Analytics Principaux -->
	<div class="analytics-kpi-grid">
		<!-- Vues Totales -->
		<div class="analytics-kpi-card kpi-views">
			<div class="kpi-icon">
				<i class="icon_eye"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo number_format( $analytics['total_views'], 0, ',', ' ' ); ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Vues totales', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Visiteurs Uniques -->
		<div class="analytics-kpi-card kpi-visitors">
			<div class="kpi-icon">
				<i class="icon_profile"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo number_format( $analytics['unique_visitors'], 0, ',', ' ' ); ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Visiteurs uniques', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Clics Réservation -->
		<div class="analytics-kpi-card kpi-bookings">
			<div class="kpi-icon">
				<i class="icon_cart"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo number_format( $analytics['booking_clicks'], 0, ',', ' ' ); ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Clics Réserver', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Taux de Conversion -->
		<div class="analytics-kpi-card kpi-conversion">
			<div class="kpi-icon">
				<i class="icon_percent"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo $analytics['conversion_rate']; ?>%</div>
				<div class="kpi-label"><?php esc_html_e( 'Taux de conversion', 'eventlist' ); ?></div>
			</div>
		</div>
	</div>

	<!-- Graphique Principal - Évolution Temporelle -->
	<div class="analytics-chart-container">
		<h4 class="analytics-section-title">
			<i class="icon_clock_alt"></i>
			<?php esc_html_e( 'Évolution des performances', 'eventlist' ); ?>
		</h4>
		<div class="chart-wrapper">
			<canvas id="el-analytics-main-chart"
				data-labels="<?php echo esc_attr( json_encode( $temporal_data['labels'] ) ); ?>"
				data-views="<?php echo esc_attr( json_encode( $temporal_data['views'] ) ); ?>"
				data-bookings="<?php echo esc_attr( json_encode( $temporal_data['booking_clicks'] ) ); ?>"
				data-wishlists="<?php echo esc_attr( json_encode( $temporal_data['wishlist_adds'] ) ); ?>"
				data-contacts="<?php echo esc_attr( json_encode( $temporal_data['contact_clicks'] ) ); ?>"
				data-shares="<?php echo esc_attr( json_encode( $temporal_data['share_clicks'] ) ); ?>"
			></canvas>
		</div>
	</div>

	<!-- Graphiques de Distribution (Devices & Browsers) -->
	<div class="analytics-charts-grid">
		<!-- Graphique Devices -->
		<?php if ( ! empty( $analytics['device_stats'] ) ) : ?>
			<div class="chart-card">
				<h4 class="analytics-section-title">
					<i class="icon_mobile"></i>
					<?php esc_html_e( 'Distribution par appareil', 'eventlist' ); ?>
				</h4>
				<div class="chart-wrapper">
					<canvas id="el-analytics-devices-chart"
						data-labels="<?php echo esc_attr( json_encode( array_column( $analytics['device_stats'], 'device_type' ) ) ); ?>"
						data-values="<?php echo esc_attr( json_encode( array_column( $analytics['device_stats'], 'count' ) ) ); ?>"
					></canvas>
				</div>
			</div>
		<?php endif; ?>

		<!-- Graphique Browsers -->
		<?php if ( ! empty( $analytics['browser_stats'] ) ) : ?>
			<div class="chart-card">
				<h4 class="analytics-section-title">
					<i class="icon_globe"></i>
					<?php esc_html_e( 'Distribution par navigateur', 'eventlist' ); ?>
				</h4>
				<div class="chart-wrapper">
					<canvas id="el-analytics-browsers-chart"
						data-labels="<?php echo esc_attr( json_encode( array_column( $analytics['browser_stats'], 'browser' ) ) ); ?>"
						data-values="<?php echo esc_attr( json_encode( array_column( $analytics['browser_stats'], 'count' ) ) ); ?>"
					></canvas>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Interactions -->
	<div class="analytics-section">
		<h4 class="analytics-section-title">
			<i class="icon_cursor"></i>
			<?php esc_html_e( 'Interactions des visiteurs', 'eventlist' ); ?>
		</h4>

		<div class="interactions-grid">
			<div class="interaction-item">
				<div class="interaction-icon wishlist">
					<i class="icon_heart"></i>
				</div>
				<div class="interaction-data">
					<div class="interaction-value"><?php echo number_format( $analytics['wishlist_adds'], 0, ',', ' ' ); ?></div>
					<div class="interaction-label"><?php esc_html_e( 'Ajouts aux favoris', 'eventlist' ); ?></div>
				</div>
			</div>

			<div class="interaction-item">
				<div class="interaction-icon contact">
					<i class="icon_mail"></i>
				</div>
				<div class="interaction-data">
					<div class="interaction-value"><?php echo number_format( $analytics['contact_clicks'], 0, ',', ' ' ); ?></div>
					<div class="interaction-label"><?php esc_html_e( 'Clics Contact', 'eventlist' ); ?></div>
				</div>
			</div>

			<div class="interaction-item">
				<div class="interaction-icon share">
					<i class="icon_share"></i>
				</div>
				<div class="interaction-data">
					<div class="interaction-value"><?php echo number_format( $analytics['share_clicks'], 0, ',', ' ' ); ?></div>
					<div class="interaction-label"><?php esc_html_e( 'Partages', 'eventlist' ); ?></div>
				</div>
			</div>

			<div class="interaction-item">
				<div class="interaction-icon engagement">
					<i class="icon_star"></i>
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
		<div class="analytics-section">
			<h4 class="analytics-section-title">
				<i class="icon_mobile"></i>
				<?php esc_html_e( 'Appareils utilisés', 'eventlist' ); ?>
			</h4>

			<div class="device-stats">
				<?php
				$total_devices = array_sum( array_column( $analytics['device_stats'], 'count' ) );
				$device_labels = array(
					'mobile' => __( 'Mobile', 'eventlist' ),
					'tablet' => __( 'Tablette', 'eventlist' ),
					'desktop' => __( 'Desktop', 'eventlist' )
				);
				$device_icons = array(
					'mobile' => 'icon_mobile',
					'tablet' => 'icon_tablet',
					'desktop' => 'icon_desktop'
				);

				foreach ( $analytics['device_stats'] as $device ) :
					$device_type = $device['device_type'];
					$count = intval( $device['count'] );
					$percentage = $total_devices > 0 ? ( $count / $total_devices ) * 100 : 0;
					$label = isset( $device_labels[ $device_type ] ) ? $device_labels[ $device_type ] : ucfirst( $device_type );
					$icon = isset( $device_icons[ $device_type ] ) ? $device_icons[ $device_type ] : 'icon_mobile';
				?>
					<div class="device-item device-<?php echo esc_attr( $device_type ); ?>">
						<div class="device-header">
							<div class="device-icon">
								<i class="<?php echo esc_attr( $icon ); ?>"></i>
							</div>
							<div class="device-info">
								<span class="device-label"><?php echo esc_html( $label ); ?></span>
								<span class="device-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
							</div>
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
		<div class="analytics-section">
			<h4 class="analytics-section-title">
				<i class="icon_globe"></i>
				<?php esc_html_e( 'Navigateurs utilisés', 'eventlist' ); ?>
			</h4>

			<div class="browser-stats">
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
		<div class="analytics-section">
			<h4 class="analytics-section-title">
				<i class="icon_pin_alt"></i>
				<?php esc_html_e( 'Villes des visiteurs', 'eventlist' ); ?>
			</h4>

			<div class="city-stats-analytics">
				<?php
				$total_cities = array_sum( array_column( $analytics['city_stats'], 'count' ) );
				foreach ( $analytics['city_stats'] as $city ) :
					$city_name = $city['city'];
					$count = intval( $city['count'] );
					$percentage = $total_cities > 0 ? ( $count / $total_cities ) * 100 : 0;
				?>
					<div class="city-analytics-item">
						<div class="city-analytics-header">
							<span class="city-analytics-name"><?php echo esc_html( $city_name ); ?></span>
							<span class="city-analytics-count"><?php echo $count; ?> (<?php echo round( $percentage, 1 ); ?>%)</span>
						</div>
						<div class="city-analytics-bar">
							<div class="city-analytics-fill" style="width: <?php echo $percentage; ?>%;"></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Top Événements par Vues -->
	<?php if ( ! empty( $analytics['top_events'] ) ) : ?>
		<div class="analytics-section">
			<h4 class="analytics-section-title">
				<i class="icon_star"></i>
				<?php esc_html_e( 'Événements les plus consultés', 'eventlist' ); ?>
			</h4>

			<div class="top-events-analytics">
				<table class="top-events-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
							<th><?php esc_html_e( 'Vues', 'eventlist' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $analytics['top_events'] as $event ) : ?>
							<tr>
								<td class="event-title">
									<a href="<?php echo esc_url( $event['permalink'] ); ?>" target="_blank">
										<?php echo esc_html( $event['title'] ); ?>
									</a>
								</td>
								<td class="event-views"><?php echo number_format( $event['views'], 0, ',', ' ' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Résumé des performances -->
	<div class="analytics-summary">
		<div class="summary-item">
			<span class="summary-label"><?php esc_html_e( 'Taux de rebond estimé', 'eventlist' ); ?></span>
			<span class="summary-value">
				<?php
				$bounce_rate = 100 - $analytics['engagement_rate'];
				echo round( $bounce_rate, 1 );
				?>%
			</span>
		</div>
		<div class="summary-item">
			<span class="summary-label"><?php esc_html_e( 'Vues par visiteur (moy.)', 'eventlist' ); ?></span>
			<span class="summary-value">
				<?php
				$views_per_visitor = $analytics['unique_visitors'] > 0 ?
					$analytics['total_views'] / $analytics['unique_visitors'] : 0;
				echo round( $views_per_visitor, 1 );
				?>
			</span>
		</div>
		<div class="summary-item">
			<span class="summary-label"><?php esc_html_e( 'Taux de mise en favoris', 'eventlist' ); ?></span>
			<span class="summary-value">
				<?php
				$wishlist_rate = $analytics['unique_visitors'] > 0 ?
					( $analytics['wishlist_adds'] / $analytics['unique_visitors'] ) * 100 : 0;
				echo round( $wishlist_rate, 1 );
				?>%
			</span>
		</div>
	</div>
</div>
