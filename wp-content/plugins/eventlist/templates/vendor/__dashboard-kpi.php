<?php
/**
 * Template pour afficher les KPI du dashboard partenaire
 */
if ( ! defined( 'ABSPATH' ) ) exit();

// Récupérer la plage de dates si définie
$range = isset( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7_day';

// Calculer les dates de début et fin en fonction de la plage
$date_range = el_get_date_range_from_filter( $range, $_GET );

// Récupérer les statistiques
$vendor_id = get_current_user_id();
$participant_stats = el_get_vendor_participant_stats( $vendor_id, $date_range );
$financial_stats = el_get_vendor_financial_stats( $vendor_id, $date_range );
$popular_events = el_get_vendor_popular_events( $vendor_id, 5, $date_range );

?>

<div class="el-dashboard-kpi">
	<h3 class="vendor_report"><?php esc_html_e( 'Indicateurs de Performance (KPI)', 'eventlist' ); ?></h3>

	<!-- KPI Principaux -->
	<div class="kpi-grid">
		<!-- Total Participants -->
		<div class="kpi-card kpi-primary">
			<div class="kpi-icon">
				<i class="icon_group"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo number_format( $participant_stats['total_participants'], 0, ',', ' ' ); ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Participants', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Chiffre d'affaires -->
		<div class="kpi-card kpi-success">
			<div class="kpi-icon">
				<i class="icon_currency"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo el_price( $financial_stats['total_revenue'] ); ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Chiffre d\'affaires', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Réservations -->
		<div class="kpi-card kpi-info">
			<div class="kpi-icon">
				<i class="icon_calendar"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo $financial_stats['completed_bookings']; ?></div>
				<div class="kpi-label"><?php esc_html_e( 'Réservations confirmées', 'eventlist' ); ?></div>
			</div>
		</div>

		<!-- Taux de conversion -->
		<div class="kpi-card kpi-warning">
			<div class="kpi-icon">
				<i class="icon_percent"></i>
			</div>
			<div class="kpi-content">
				<div class="kpi-value"><?php echo round( $financial_stats['completion_rate'], 1 ); ?>%</div>
				<div class="kpi-label"><?php esc_html_e( 'Taux de complétion', 'eventlist' ); ?></div>
			</div>
		</div>
	</div>

	<!-- Statistiques d'âge -->
	<?php if ( $participant_stats['total_participants'] > 0 ) : ?>
		<div class="kpi-section">
			<h4 class="kpi-section-title">
				<i class="icon_calendar"></i>
				<?php esc_html_e( 'Répartition par âge', 'eventlist' ); ?>
			</h4>
			<div class="kpi-age-stats">
				<div class="age-overview">
					<?php if ( $participant_stats['avg_age'] > 0 ) : ?>
						<div class="age-avg">
							<span class="age-value"><?php echo $participant_stats['avg_age']; ?></span>
							<span class="age-label"><?php esc_html_e( 'Âge moyen', 'eventlist' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

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
		<div class="kpi-section">
			<h4 class="kpi-section-title">
				<i class="icon_profile"></i>
				<?php esc_html_e( 'Répartition par genre', 'eventlist' ); ?>
			</h4>
			<div class="kpi-gender-stats">
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
							<div class="gender-icon">
								<?php if ( $gender === 'male' ) : ?>
									<i class="icon_profile"></i>
								<?php elseif ( $gender === 'female' ) : ?>
									<i class="icon_profile"></i>
								<?php else : ?>
									<i class="icon_profile"></i>
								<?php endif; ?>
							</div>
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
		<div class="kpi-section">
			<h4 class="kpi-section-title">
				<i class="icon_pin_alt"></i>
				<?php esc_html_e( 'Villes d\'origine des participants', 'eventlist' ); ?>
			</h4>
			<div class="kpi-cities-stats">
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
		<div class="kpi-section">
			<h4 class="kpi-section-title">
				<i class="icon_star"></i>
				<?php esc_html_e( 'Événements les plus populaires', 'eventlist' ); ?>
			</h4>
			<div class="kpi-popular-events">
				<table class="popular-events-table">
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
								<td class="event-title">
									<a href="<?php echo esc_url( $event['permalink'] ); ?>" target="_blank">
										<?php echo esc_html( $event['title'] ); ?>
									</a>
								</td>
								<td class="event-bookings"><?php echo $event['booking_count']; ?></td>
								<td class="event-tickets"><?php echo $event['total_tickets']; ?></td>
								<td class="event-revenue"><?php echo el_price( $event['total_revenue'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Métriques secondaires -->
	<div class="kpi-secondary-metrics">
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
