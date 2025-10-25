<?php
/**
 * Organizer Contact Tracking System
 *
 * Track when users view phone numbers and addresses of organizers.
 * Provides statistics for organizers in their admin dashboard.
 *
 * @package LeHiboo
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create tracking table on theme activation
 */
function lehiboo_create_organizer_tracking_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'organizer_contact_views';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		organizer_id bigint(20) UNSIGNED NOT NULL,
		viewer_id bigint(20) UNSIGNED DEFAULT NULL,
		viewer_ip varchar(100) DEFAULT NULL,
		contact_type varchar(20) NOT NULL,
		event_id bigint(20) UNSIGNED DEFAULT NULL,
		context varchar(50) DEFAULT NULL,
		viewed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY organizer_id (organizer_id),
		KEY viewer_id (viewer_id),
		KEY contact_type (contact_type),
		KEY event_id (event_id),
		KEY viewed_at (viewed_at)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

// Hook to create table
add_action( 'after_switch_theme', 'lehiboo_create_organizer_tracking_table' );

/**
 * Track contact view (phone or address)
 */
function lehiboo_track_contact_view() {
	check_ajax_referer( 'organizer_tracking_nonce', 'nonce' );

	global $wpdb;
	$table_name = $wpdb->prefix . 'organizer_contact_views';

	$organizer_id = isset( $_POST['organizer_id'] ) ? intval( $_POST['organizer_id'] ) : 0;
	$contact_type = isset( $_POST['contact_type'] ) ? sanitize_text_field( $_POST['contact_type'] ) : '';
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : null;
	$context = isset( $_POST['context'] ) ? sanitize_text_field( $_POST['context'] ) : 'unknown';

	// Validate
	if ( ! $organizer_id || ! in_array( $contact_type, array( 'phone', 'address' ) ) ) {
		wp_send_json_error( array( 'message' => __( 'Données invalides', 'eventlist' ) ) );
		return;
	}

	// Get viewer info
	$viewer_id = is_user_logged_in() ? get_current_user_id() : null;
	$viewer_ip = lehiboo_get_client_ip();

	// Check if already tracked in last 24h (prevent spam)
	$cache_key = "org_view_{$organizer_id}_{$contact_type}_{$viewer_ip}";
	if ( get_transient( $cache_key ) ) {
		// Already tracked recently
		wp_send_json_success( array( 'message' => __( 'Déjà enregistré', 'eventlist' ), 'cached' => true ) );
		return;
	}

	// Insert tracking record
	$inserted = $wpdb->insert(
		$table_name,
		array(
			'organizer_id' => $organizer_id,
			'viewer_id' => $viewer_id,
			'viewer_ip' => $viewer_ip,
			'contact_type' => $contact_type,
			'event_id' => $event_id,
			'context' => $context,
			'viewed_at' => current_time( 'mysql' )
		),
		array(
			'%d', // organizer_id
			'%d', // viewer_id
			'%s', // viewer_ip
			'%s', // contact_type
			'%d', // event_id
			'%s', // context
			'%s'  // viewed_at
		)
	);

	if ( $inserted ) {
		// Set transient to prevent duplicate tracking for 24h
		set_transient( $cache_key, true, DAY_IN_SECONDS );

		wp_send_json_success( array( 'message' => __( 'Vue enregistrée', 'eventlist' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Erreur lors de l\'enregistrement', 'eventlist' ) ) );
	}
}
add_action( 'wp_ajax_track_organizer_contact_view', 'lehiboo_track_contact_view' );
add_action( 'wp_ajax_nopriv_track_organizer_contact_view', 'lehiboo_track_contact_view' );

/**
 * Get client IP address
 */
function lehiboo_get_client_ip() {
	$ip = '';

	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		// Cloudflare
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return sanitize_text_field( $ip );
}

/**
 * Get organizer contact view statistics
 *
 * @param int $organizer_id User ID
 * @param array $args Optional arguments (period, contact_type, group_by)
 * @return array Statistics
 */
function lehiboo_get_organizer_contact_stats( $organizer_id, $args = array() ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'organizer_contact_views';

	$defaults = array(
		'period' => '30', // Days (or 'all')
		'contact_type' => 'all', // 'phone', 'address', or 'all'
		'group_by' => 'day' // 'day', 'week', 'month', or null
	);
	$args = wp_parse_args( $args, $defaults );

	// Build WHERE clause
	$where = $wpdb->prepare( "WHERE organizer_id = %d", $organizer_id );

	if ( $args['period'] !== 'all' ) {
		$days = intval( $args['period'] );
		$where .= $wpdb->prepare( " AND viewed_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days );
	}

	if ( $args['contact_type'] !== 'all' ) {
		$where .= $wpdb->prepare( " AND contact_type = %s", $args['contact_type'] );
	}

	// Get totals
	$totals = $wpdb->get_row(
		"SELECT
			COUNT(*) as total_views,
			SUM(CASE WHEN contact_type = 'phone' THEN 1 ELSE 0 END) as phone_views,
			SUM(CASE WHEN contact_type = 'address' THEN 1 ELSE 0 END) as address_views,
			COUNT(DISTINCT viewer_id) as unique_viewers,
			COUNT(DISTINCT event_id) as events_count
		FROM $table_name
		$where",
		ARRAY_A
	);

	// Get grouped data if requested
	$grouped_data = array();
	if ( $args['group_by'] ) {
		$date_format = '';
		switch ( $args['group_by'] ) {
			case 'day':
				$date_format = '%Y-%m-%d';
				break;
			case 'week':
				$date_format = '%Y-%u';
				break;
			case 'month':
				$date_format = '%Y-%m';
				break;
		}

		if ( $date_format ) {
			$grouped_data = $wpdb->get_results(
				"SELECT
					DATE_FORMAT(viewed_at, '$date_format') as period,
					contact_type,
					COUNT(*) as views
				FROM $table_name
				$where
				GROUP BY period, contact_type
				ORDER BY period DESC",
				ARRAY_A
			);
		}
	}

	return array(
		'totals' => $totals,
		'grouped' => $grouped_data
	);
}

/**
 * Add contact stats to organizer admin menu
 */
function lehiboo_add_organizer_stats_menu() {
	// Only for organizers (authors)
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	add_menu_page(
		__( 'Statistiques Contact', 'eventlist' ),
		__( 'Mes Statistiques', 'eventlist' ),
		'read',
		'organizer-contact-stats',
		'lehiboo_render_organizer_stats_page',
		'dashicons-chart-line',
		30
	);
}
add_action( 'admin_menu', 'lehiboo_add_organizer_stats_menu' );

/**
 * Render organizer stats page in admin
 */
function lehiboo_render_organizer_stats_page() {
	$organizer_id = get_current_user_id();

	// Get period from query string
	$period = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '30';

	$stats = lehiboo_get_organizer_contact_stats( $organizer_id, array(
		'period' => $period,
		'group_by' => 'day'
	) );

	?>
	<div class="wrap organizer-stats-page">
		<h1><?php _e( 'Statistiques de Contact', 'eventlist' ); ?></h1>

		<div class="stats-period-selector">
			<a href="?page=organizer-contact-stats&period=7" class="<?php echo $period === '7' ? 'current' : ''; ?>">
				<?php _e( '7 jours', 'eventlist' ); ?>
			</a>
			<a href="?page=organizer-contact-stats&period=30" class="<?php echo $period === '30' ? 'current' : ''; ?>">
				<?php _e( '30 jours', 'eventlist' ); ?>
			</a>
			<a href="?page=organizer-contact-stats&period=90" class="<?php echo $period === '90' ? 'current' : ''; ?>">
				<?php _e( '90 jours', 'eventlist' ); ?>
			</a>
			<a href="?page=organizer-contact-stats&period=all" class="<?php echo $period === 'all' ? 'current' : ''; ?>">
				<?php _e( 'Tout', 'eventlist' ); ?>
			</a>
		</div>

		<div class="stats-cards">
			<div class="stat-card">
				<div class="stat-icon phone-icon">
					<i class="dashicons dashicons-phone"></i>
				</div>
				<div class="stat-content">
					<div class="stat-label"><?php _e( 'Vues Téléphone', 'eventlist' ); ?></div>
					<div class="stat-value"><?php echo number_format_i18n( $stats['totals']['phone_views'] ); ?></div>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon address-icon">
					<i class="dashicons dashicons-location-alt"></i>
				</div>
				<div class="stat-content">
					<div class="stat-label"><?php _e( 'Vues Adresse', 'eventlist' ); ?></div>
					<div class="stat-value"><?php echo number_format_i18n( $stats['totals']['address_views'] ); ?></div>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon total-icon">
					<i class="dashicons dashicons-visibility"></i>
				</div>
				<div class="stat-content">
					<div class="stat-label"><?php _e( 'Total Vues', 'eventlist' ); ?></div>
					<div class="stat-value"><?php echo number_format_i18n( $stats['totals']['total_views'] ); ?></div>
				</div>
			</div>

			<div class="stat-card">
				<div class="stat-icon users-icon">
					<i class="dashicons dashicons-groups"></i>
				</div>
				<div class="stat-content">
					<div class="stat-label"><?php _e( 'Visiteurs Uniques', 'eventlist' ); ?></div>
					<div class="stat-value"><?php echo number_format_i18n( $stats['totals']['unique_viewers'] ); ?></div>
				</div>
			</div>
		</div>

		<style>
			.organizer-stats-page {
				max-width: 1200px;
			}
			.stats-period-selector {
				margin: 20px 0;
				background: #fff;
				padding: 15px;
				border-radius: 8px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			.stats-period-selector a {
				margin-right: 15px;
				padding: 8px 16px;
				text-decoration: none;
				border-radius: 4px;
				background: #f0f0f1;
				color: #2271b1;
			}
			.stats-period-selector a.current {
				background: #2271b1;
				color: #fff;
			}
			.stats-cards {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 20px;
				margin-top: 20px;
			}
			.stat-card {
				background: #fff;
				padding: 24px;
				border-radius: 8px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
				display: flex;
				align-items: center;
				gap: 16px;
			}
			.stat-icon {
				width: 60px;
				height: 60px;
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 28px;
			}
			.stat-icon.phone-icon {
				background: #e3f2fd;
				color: #1976d2;
			}
			.stat-icon.address-icon {
				background: #fff3e0;
				color: #f57c00;
			}
			.stat-icon.total-icon {
				background: #f3e5f5;
				color: #7b1fa2;
			}
			.stat-icon.users-icon {
				background: #e8f5e9;
				color: #388e3c;
			}
			.stat-content {
				flex: 1;
			}
			.stat-label {
				font-size: 14px;
				color: #666;
				margin-bottom: 4px;
			}
			.stat-value {
				font-size: 32px;
				font-weight: 600;
				color: #1e1e1e;
			}
		</style>
	</div>
	<?php
}
