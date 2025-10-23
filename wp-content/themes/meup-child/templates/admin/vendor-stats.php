<?php
/**
 * Template Admin - Statistiques partenaires
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Récupérer les données détaillées
$recent_vendors = new WP_User_Query( array(
	'role' => 'el_event_vendor',
	'number' => 10,
	'orderby' => 'registered',
	'order' => 'DESC'
) );

$recent_approved = new WP_User_Query( array(
	'role' => 'el_event_vendor',
	'meta_query' => array(
		array( 'key' => 'vendor_status', 'value' => 'approved' )
	),
	'number' => 5,
	'orderby' => 'meta_value',
	'meta_key' => 'vendor_approved_date',
	'order' => 'DESC'
) );

// Stats par type d'organisation
$org_types = array( 'association', 'entreprise', 'autoentrepreneur', 'collectivite', 'autre' );
$type_stats = array();
foreach ( $org_types as $type ) {
	$query = new WP_User_Query( array(
		'role' => 'el_event_vendor',
		'meta_query' => array(
			array( 'key' => 'org_type', 'value' => $type )
		)
	) );
	$type_stats[$type] = $query->get_total();
}
?>

<div class="wrap lehiboo_vendor_stats">
	<h1 class="admin_page_title">
		<i class="fas fa-chart-bar"></i>
		Statistiques Partenaires
	</h1>

	<!-- Main Stats Cards -->
	<div class="stats_cards large">
		<div class="stat_card stat_total">
			<div class="stat_icon">
				<i class="fas fa-users"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['total']; ?></div>
				<div class="stat_label">Total Partenaires</div>
				<div class="stat_description">Tous statuts confondus</div>
			</div>
		</div>

		<div class="stat_card stat_pending">
			<div class="stat_icon">
				<i class="fas fa-clock"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['pending']; ?></div>
				<div class="stat_label">En attente</div>
				<div class="stat_description">Demandes à traiter</div>
			</div>
		</div>

		<div class="stat_card stat_approved">
			<div class="stat_icon">
				<i class="fas fa-check-circle"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['approved']; ?></div>
				<div class="stat_label">Approuvés</div>
				<div class="stat_description">Partenaires actifs</div>
			</div>
		</div>

		<div class="stat_card stat_rejected">
			<div class="stat_icon">
				<i class="fas fa-times-circle"></i>
			</div>
			<div class="stat_content">
				<div class="stat_value"><?php echo $stats['rejected']; ?></div>
				<div class="stat_label">Rejetés</div>
				<div class="stat_description">Demandes refusées</div>
			</div>
		</div>
	</div>

	<div class="stats_layout">
		<!-- Left Column -->
		<div class="stats_column">
			<!-- Types d'organisations -->
			<div class="stats_box">
				<h2 class="box_title">
					<i class="fas fa-building"></i>
					Répartition par type
				</h2>
				<div class="type_stats_list">
					<?php
					$type_labels = array(
						'association' => 'Associations',
						'entreprise' => 'Entreprises',
						'autoentrepreneur' => 'Auto-entrepreneurs',
						'collectivite' => 'Collectivités',
						'autre' => 'Autres'
					);
					$type_icons = array(
						'association' => 'fa-hands-helping',
						'entreprise' => 'fa-briefcase',
						'autoentrepreneur' => 'fa-user-tie',
						'collectivite' => 'fa-landmark',
						'autre' => 'fa-question-circle'
					);
					foreach ( $org_types as $type ) {
						$count = $type_stats[$type];
						$percentage = $stats['total'] > 0 ? round( ( $count / $stats['total'] ) * 100 ) : 0;
						?>
						<div class="type_stat_item">
							<div class="type_stat_header">
								<div class="type_stat_info">
									<i class="fas <?php echo $type_icons[$type]; ?>"></i>
									<span class="type_label"><?php echo $type_labels[$type]; ?></span>
								</div>
								<span class="type_count"><?php echo $count; ?></span>
							</div>
							<div class="type_stat_bar">
								<div class="type_stat_fill" style="width: <?php echo $percentage; ?>%;"></div>
							</div>
							<div class="type_stat_percentage"><?php echo $percentage; ?>%</div>
						</div>
					<?php } ?>
				</div>
			</div>

			<!-- Dernières approbations -->
			<div class="stats_box">
				<h2 class="box_title">
					<i class="fas fa-check"></i>
					Dernières approbations
				</h2>
				<?php if ( $recent_approved->get_results() ) : ?>
				<div class="recent_list">
					<?php foreach ( $recent_approved->get_results() as $vendor ) :
						$org_name = get_user_meta( $vendor->ID, 'org_display_name', true );
						$approved_date = get_user_meta( $vendor->ID, 'vendor_approved_date', true );
						$logo_id = get_user_meta( $vendor->ID, 'org_logo_id', true );
						$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
					?>
					<div class="recent_item">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="recent_logo">
						<?php else : ?>
							<div class="recent_logo_placeholder">
								<i class="fas fa-building"></i>
							</div>
						<?php endif; ?>
						<div class="recent_info">
							<strong><?php echo esc_html( $org_name ); ?></strong>
							<span class="recent_date">
								<i class="fas fa-calendar"></i>
								<?php echo date_i18n( 'j M Y', strtotime( $approved_date ) ); ?>
							</span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else : ?>
				<div class="no_data_box">
					<i class="fas fa-inbox"></i>
					<p>Aucune approbation récente</p>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Right Column -->
		<div class="stats_column">
			<!-- Dernières demandes -->
			<div class="stats_box">
				<h2 class="box_title">
					<i class="fas fa-clock"></i>
					Dernières demandes
				</h2>
				<?php if ( $recent_vendors->get_results() ) : ?>
				<div class="recent_list">
					<?php foreach ( $recent_vendors->get_results() as $vendor ) :
						$org_name = get_user_meta( $vendor->ID, 'org_display_name', true );
						$vendor_status = get_user_meta( $vendor->ID, 'vendor_status', true );
						$logo_id = get_user_meta( $vendor->ID, 'org_logo_id', true );
						$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';

						$status_classes = array(
							'pending_approval' => 'status_pending',
							'approved' => 'status_approved',
							'rejected' => 'status_rejected'
						);
						$status_labels = array(
							'pending_approval' => 'En attente',
							'approved' => 'Approuvé',
							'rejected' => 'Rejeté'
						);
						$status_class = $status_classes[$vendor_status] ?? 'status_pending';
						$status_label = $status_labels[$vendor_status] ?? $vendor_status;
					?>
					<div class="recent_item">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="recent_logo">
						<?php else : ?>
							<div class="recent_logo_placeholder">
								<i class="fas fa-building"></i>
							</div>
						<?php endif; ?>
						<div class="recent_info">
							<strong><?php echo esc_html( $org_name ); ?></strong>
							<span class="recent_date">
								<i class="fas fa-calendar"></i>
								<?php echo human_time_diff( strtotime( $vendor->user_registered ), current_time( 'timestamp' ) ); ?> ago
							</span>
						</div>
						<span class="status_badge <?php echo esc_attr( $status_class ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else : ?>
				<div class="no_data_box">
					<i class="fas fa-inbox"></i>
					<p>Aucune demande</p>
				</div>
				<?php endif; ?>
			</div>

			<!-- Taux d'approbation -->
			<div class="stats_box">
				<h2 class="box_title">
					<i class="fas fa-percentage"></i>
					Taux d'approbation
				</h2>
				<?php
				$processed = $stats['approved'] + $stats['rejected'];
				$approval_rate = $processed > 0 ? round( ( $stats['approved'] / $processed ) * 100 ) : 0;
				?>
				<div class="approval_rate_chart">
					<div class="rate_circle">
						<svg viewBox="0 0 100 100">
							<circle cx="50" cy="50" r="40" class="rate_bg"></circle>
							<circle cx="50" cy="50" r="40" class="rate_fill"
								style="stroke-dasharray: <?php echo $approval_rate * 2.51; ?> 251;"></circle>
						</svg>
						<div class="rate_value"><?php echo $approval_rate; ?>%</div>
					</div>
					<div class="rate_legend">
						<div class="rate_legend_item">
							<span class="rate_legend_dot approved"></span>
							<span>Approuvés: <?php echo $stats['approved']; ?></span>
						</div>
						<div class="rate_legend_item">
							<span class="rate_legend_dot rejected"></span>
							<span>Rejetés: <?php echo $stats['rejected']; ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Quick Actions -->
	<div class="quick_actions_box">
		<h2 class="box_title">
			<i class="fas fa-bolt"></i>
			Actions rapides
		</h2>
		<div class="quick_actions">
			<a href="<?php echo admin_url( 'admin.php?page=lehiboo-vendor-applications&status=pending_approval' ); ?>" class="quick_action_btn pending">
				<i class="fas fa-clock"></i>
				<span>Voir les demandes en attente</span>
				<span class="action_badge"><?php echo $stats['pending']; ?></span>
			</a>
			<a href="<?php echo admin_url( 'admin.php?page=lehiboo-vendor-applications&status=approved' ); ?>" class="quick_action_btn approved">
				<i class="fas fa-check-circle"></i>
				<span>Voir les partenaires approuvés</span>
				<span class="action_badge"><?php echo $stats['approved']; ?></span>
			</a>
			<a href="<?php echo admin_url( 'admin.php?page=lehiboo-vendor-applications&status=rejected' ); ?>" class="quick_action_btn rejected">
				<i class="fas fa-times-circle"></i>
				<span>Voir les demandes rejetées</span>
				<span class="action_badge"><?php echo $stats['rejected']; ?></span>
			</a>
		</div>
	</div>
</div>
