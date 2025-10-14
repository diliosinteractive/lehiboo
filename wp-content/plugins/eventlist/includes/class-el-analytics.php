<?php
/**
 * EventList Analytics Class
 * Gestion du tracking et des analytics des événements
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class EL_Analytics {

	protected static $_instance = null;

	/**
	 * Table name for analytics
	 */
	private $table_name;

	/**
	 * Get instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'el_analytics';

		// Hooks
		add_action( 'init', array( $this, 'register_ajax_actions' ) );
		add_action( 'wp_ajax_el_track_event', array( $this, 'track_event' ) );
		add_action( 'wp_ajax_nopriv_el_track_event', array( $this, 'track_event' ) );
		add_filter( 'body_class', array( $this, 'add_event_id_to_body' ) );

		// Admin action to manually create table
		add_action( 'admin_init', array( $this, 'maybe_create_table' ) );
	}

	/**
	 * Check and create table if it doesn't exist (run on admin_init)
	 */
	public function maybe_create_table() {
		global $wpdb;

		// Check if table exists
		$table_name = $wpdb->prefix . 'el_analytics';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );

		// If table doesn't exist, create it
		if ( $table_exists != $table_name ) {
			self::create_table();
		}
	}

	/**
	 * Add event ID to body class for analytics tracking
	 */
	public function add_event_id_to_body( $classes ) {
		if ( is_singular( 'event' ) ) {
			global $post;
			$classes[] = 'event-single';
			// We'll use data attribute instead, added via wp_footer
			add_action( 'wp_footer', array( $this, 'add_event_id_script' ), 1 );
		}
		return $classes;
	}

	/**
	 * Add event ID as data attribute via JavaScript
	 */
	public function add_event_id_script() {
		if ( is_singular( 'event' ) ) {
			global $post;
			?>
			<script>
			document.body.setAttribute('data-event-id', '<?php echo esc_js( $post->ID ); ?>');
			</script>
			<?php
		}
	}

	/**
	 * Create analytics table on plugin activation
	 */
	public static function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'el_analytics';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			event_id bigint(20) NOT NULL,
			event_type varchar(50) NOT NULL,
			user_id bigint(20) DEFAULT NULL,
			session_id varchar(100) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			device_type varchar(20) DEFAULT NULL,
			browser varchar(50) DEFAULT NULL,
			os varchar(50) DEFAULT NULL,
			referrer_url text DEFAULT NULL,
			page_url text DEFAULT NULL,
			city varchar(100) DEFAULT NULL,
			country varchar(100) DEFAULT NULL,
			meta_data longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY event_type (event_type),
			KEY user_id (user_id),
			KEY session_id (session_id),
			KEY created_at (created_at),
			KEY device_type (device_type)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Register AJAX actions
	 */
	public function register_ajax_actions() {
		// Already registered in constructor
	}

	/**
	 * Track an event via AJAX
	 */
	public function track_event() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'el_analytics_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			return;
		}

		$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
		$event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
		$meta_data = isset( $_POST['meta_data'] ) ? $_POST['meta_data'] : array();

		if ( ! $event_id || ! $event_type ) {
			wp_send_json_error( array( 'message' => 'Missing required parameters' ) );
			return;
		}

		// Track the event
		$result = $this->log_event( $event_id, $event_type, $meta_data );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Event tracked successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to track event' ) );
		}
	}

	/**
	 * Log an analytics event
	 *
	 * @param int $event_id Event post ID
	 * @param string $event_type Type of event (view, wishlist_add, contact_click, booking_click, etc.)
	 * @param array $meta_data Additional metadata
	 * @return bool|int Insert ID on success, false on failure
	 */
	public function log_event( $event_id, $event_type, $meta_data = array() ) {
		global $wpdb;

		// Get user information
		$user_id = get_current_user_id();
		$session_id = $this->get_session_id();
		$ip_address = $this->get_client_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

		// Parse device info
		$device_info = $this->parse_user_agent( $user_agent );

		// Get referrer
		$referrer_url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
		$page_url = isset( $_POST['page_url'] ) ? esc_url_raw( $_POST['page_url'] ) :
					( isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : '' );

		// Get location (simplified - in production you'd use a GeoIP service)
		$location = $this->get_location_from_ip( $ip_address );

		// Prepare data
		$data = array(
			'event_id' => $event_id,
			'event_type' => $event_type,
			'user_id' => $user_id ? $user_id : null,
			'session_id' => $session_id,
			'ip_address' => $ip_address,
			'user_agent' => $user_agent,
			'device_type' => $device_info['device_type'],
			'browser' => $device_info['browser'],
			'os' => $device_info['os'],
			'referrer_url' => $referrer_url,
			'page_url' => $page_url,
			'city' => $location['city'],
			'country' => $location['country'],
			'meta_data' => maybe_serialize( $meta_data ),
			'created_at' => current_time( 'mysql' )
		);

		$format = array(
			'%d', // event_id
			'%s', // event_type
			'%d', // user_id
			'%s', // session_id
			'%s', // ip_address
			'%s', // user_agent
			'%s', // device_type
			'%s', // browser
			'%s', // os
			'%s', // referrer_url
			'%s', // page_url
			'%s', // city
			'%s', // country
			'%s', // meta_data
			'%s'  // created_at
		);

		$result = $wpdb->insert( $this->table_name, $data, $format );

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get or create session ID
	 */
	private function get_session_id() {
		if ( isset( $_COOKIE['el_session_id'] ) ) {
			return sanitize_text_field( $_COOKIE['el_session_id'] );
		}

		$session_id = wp_generate_password( 32, false );
		setcookie( 'el_session_id', $session_id, time() + ( 86400 * 30 ), COOKIEPATH, COOKIE_DOMAIN );

		return $session_id;
	}

	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = explode( ',', $_SERVER[ $key ] );
				$ip = trim( $ip[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Parse user agent to extract device, browser, and OS info
	 */
	private function parse_user_agent( $user_agent ) {
		$device_type = 'desktop';
		$browser = 'Unknown';
		$os = 'Unknown';

		// Detect device type
		if ( wp_is_mobile() ) {
			if ( preg_match( '/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $user_agent ) ) {
				$device_type = 'tablet';
			} else {
				$device_type = 'mobile';
			}
		}

		// Detect browser
		if ( strpos( $user_agent, 'Firefox' ) !== false ) {
			$browser = 'Firefox';
		} elseif ( strpos( $user_agent, 'Chrome' ) !== false ) {
			$browser = 'Chrome';
		} elseif ( strpos( $user_agent, 'Safari' ) !== false ) {
			$browser = 'Safari';
		} elseif ( strpos( $user_agent, 'Edge' ) !== false || strpos( $user_agent, 'Edg' ) !== false ) {
			$browser = 'Edge';
		} elseif ( strpos( $user_agent, 'Opera' ) !== false || strpos( $user_agent, 'OPR' ) !== false ) {
			$browser = 'Opera';
		} elseif ( strpos( $user_agent, 'MSIE' ) !== false || strpos( $user_agent, 'Trident' ) !== false ) {
			$browser = 'Internet Explorer';
		}

		// Detect OS
		if ( strpos( $user_agent, 'Windows' ) !== false ) {
			$os = 'Windows';
		} elseif ( strpos( $user_agent, 'Mac' ) !== false ) {
			$os = 'macOS';
		} elseif ( strpos( $user_agent, 'Linux' ) !== false ) {
			$os = 'Linux';
		} elseif ( strpos( $user_agent, 'Android' ) !== false ) {
			$os = 'Android';
		} elseif ( strpos( $user_agent, 'iOS' ) !== false || strpos( $user_agent, 'iPhone' ) !== false || strpos( $user_agent, 'iPad' ) !== false ) {
			$os = 'iOS';
		}

		return array(
			'device_type' => $device_type,
			'browser' => $browser,
			'os' => $os
		);
	}

	/**
	 * Get location from IP (simplified version)
	 * In production, integrate with a GeoIP service like MaxMind
	 */
	private function get_location_from_ip( $ip ) {
		// This is a placeholder. In production, use a proper GeoIP service
		// For now, return empty values
		return array(
			'city' => null,
			'country' => null
		);
	}

	/**
	 * Get analytics for a specific event
	 *
	 * @param int $event_id Event post ID
	 * @param array $date_range Optional date range ['start' => timestamp, 'end' => timestamp]
	 * @return array Analytics data
	 */
	public function get_event_analytics( $event_id, $date_range = null ) {
		global $wpdb;

		$date_filter = '';
		if ( $date_range && isset( $date_range['start'] ) && isset( $date_range['end'] ) ) {
			$date_filter = $wpdb->prepare(
				" AND created_at BETWEEN %s AND %s",
				date( 'Y-m-d 00:00:00', $date_range['start'] ),
				date( 'Y-m-d 23:59:59', $date_range['end'] )
			);
		}

		// Get total views
		$total_views = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'view' $date_filter",
			$event_id
		) );

		// Get unique visitors (by session_id)
		$unique_visitors = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT session_id) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'view' $date_filter",
			$event_id
		) );

		// Get wishlist adds
		$wishlist_adds = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'wishlist_add' $date_filter",
			$event_id
		) );

		// Get contact clicks
		$contact_clicks = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'contact_click' $date_filter",
			$event_id
		) );

		// Get booking button clicks
		$booking_clicks = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'booking_click' $date_filter",
			$event_id
		) );

		// Get share clicks
		$share_clicks = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE event_id = %d AND event_type = 'share_click' $date_filter",
			$event_id
		) );

		// Get device breakdown
		$device_stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT device_type, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id = %d AND event_type = 'view' $date_filter
			GROUP BY device_type",
			$event_id
		), ARRAY_A );

		// Get browser breakdown
		$browser_stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT browser, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id = %d AND event_type = 'view' $date_filter
			GROUP BY browser
			ORDER BY count DESC
			LIMIT 5",
			$event_id
		), ARRAY_A );

		// Get city breakdown
		$city_stats = $wpdb->get_results( $wpdb->prepare(
			"SELECT city, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id = %d AND event_type = 'view' AND city IS NOT NULL $date_filter
			GROUP BY city
			ORDER BY count DESC
			LIMIT 10",
			$event_id
		), ARRAY_A );

		// Calculate conversion rate
		$conversion_rate = 0;
		if ( $unique_visitors > 0 && $booking_clicks > 0 ) {
			$conversion_rate = ( $booking_clicks / $unique_visitors ) * 100;
		}

		// Calculate engagement rate (clicks / views)
		$total_interactions = $wishlist_adds + $contact_clicks + $booking_clicks + $share_clicks;
		$engagement_rate = 0;
		if ( $total_views > 0 ) {
			$engagement_rate = ( $total_interactions / $total_views ) * 100;
		}

		return array(
			'total_views' => intval( $total_views ),
			'unique_visitors' => intval( $unique_visitors ),
			'wishlist_adds' => intval( $wishlist_adds ),
			'contact_clicks' => intval( $contact_clicks ),
			'booking_clicks' => intval( $booking_clicks ),
			'share_clicks' => intval( $share_clicks ),
			'conversion_rate' => round( $conversion_rate, 2 ),
			'engagement_rate' => round( $engagement_rate, 2 ),
			'device_stats' => $device_stats,
			'browser_stats' => $browser_stats,
			'city_stats' => $city_stats
		);
	}

	/**
	 * Get aggregated analytics for vendor's events
	 */
	public function get_vendor_analytics( $vendor_id = null, $date_range = null ) {
		global $wpdb;

		if ( ! $vendor_id ) {
			$vendor_id = get_current_user_id();
		}

		// Get vendor's events
		$events = get_posts( array(
			'post_type'      => 'event',
			'author'         => $vendor_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any'
		) );

		if ( empty( $events ) ) {
			return $this->get_empty_analytics();
		}

		$event_ids = implode( ',', array_map( 'absint', $events ) );

		$date_filter = '';
		if ( $date_range && isset( $date_range['start'] ) && isset( $date_range['end'] ) ) {
			$date_filter = $wpdb->prepare(
				" AND created_at BETWEEN %s AND %s",
				date( 'Y-m-d 00:00:00', $date_range['start'] ),
				date( 'Y-m-d 23:59:59', $date_range['end'] )
			);
		}

		// Aggregate statistics
		$stats = $wpdb->get_row(
			"SELECT
				COUNT(CASE WHEN event_type = 'view' THEN 1 END) as total_views,
				COUNT(DISTINCT CASE WHEN event_type = 'view' THEN session_id END) as unique_visitors,
				COUNT(CASE WHEN event_type = 'wishlist_add' THEN 1 END) as wishlist_adds,
				COUNT(CASE WHEN event_type = 'contact_click' THEN 1 END) as contact_clicks,
				COUNT(CASE WHEN event_type = 'booking_click' THEN 1 END) as booking_clicks,
				COUNT(CASE WHEN event_type = 'share_click' THEN 1 END) as share_clicks
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids) $date_filter",
			ARRAY_A
		);

		// Get device breakdown
		$device_stats = $wpdb->get_results(
			"SELECT device_type, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids) AND event_type = 'view' $date_filter
			GROUP BY device_type",
			ARRAY_A
		);

		// Get browser breakdown
		$browser_stats = $wpdb->get_results(
			"SELECT browser, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids) AND event_type = 'view' $date_filter
			GROUP BY browser
			ORDER BY count DESC
			LIMIT 5",
			ARRAY_A
		);

		// Get city breakdown
		$city_stats = $wpdb->get_results(
			"SELECT city, COUNT(*) as count
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids) AND event_type = 'view' AND city IS NOT NULL $date_filter
			GROUP BY city
			ORDER BY count DESC
			LIMIT 10",
			ARRAY_A
		);

		// Get top events by views
		$top_events = $wpdb->get_results(
			"SELECT event_id, COUNT(*) as views
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids) AND event_type = 'view' $date_filter
			GROUP BY event_id
			ORDER BY views DESC
			LIMIT 5",
			ARRAY_A
		);

		// Enrich top events with post data
		foreach ( $top_events as $key => $event_data ) {
			$post = get_post( $event_data['event_id'] );
			if ( $post ) {
				$top_events[ $key ]['title'] = $post->post_title;
				$top_events[ $key ]['permalink'] = get_permalink( $post->ID );
			}
		}

		// Calculate rates
		$total_views = intval( $stats['total_views'] );
		$unique_visitors = intval( $stats['unique_visitors'] );
		$booking_clicks = intval( $stats['booking_clicks'] );

		$conversion_rate = 0;
		if ( $unique_visitors > 0 && $booking_clicks > 0 ) {
			$conversion_rate = ( $booking_clicks / $unique_visitors ) * 100;
		}

		$total_interactions = intval( $stats['wishlist_adds'] ) + intval( $stats['contact_clicks'] ) +
							  intval( $stats['booking_clicks'] ) + intval( $stats['share_clicks'] );
		$engagement_rate = 0;
		if ( $total_views > 0 ) {
			$engagement_rate = ( $total_interactions / $total_views ) * 100;
		}

		return array(
			'total_views' => $total_views,
			'unique_visitors' => $unique_visitors,
			'wishlist_adds' => intval( $stats['wishlist_adds'] ),
			'contact_clicks' => intval( $stats['contact_clicks'] ),
			'booking_clicks' => intval( $stats['booking_clicks'] ),
			'share_clicks' => intval( $stats['share_clicks'] ),
			'conversion_rate' => round( $conversion_rate, 2 ),
			'engagement_rate' => round( $engagement_rate, 2 ),
			'device_stats' => $device_stats,
			'browser_stats' => $browser_stats,
			'city_stats' => $city_stats,
			'top_events' => $top_events
		);
	}

	/**
	 * Get empty analytics structure
	 */
	private function get_empty_analytics() {
		return array(
			'total_views' => 0,
			'unique_visitors' => 0,
			'wishlist_adds' => 0,
			'contact_clicks' => 0,
			'booking_clicks' => 0,
			'share_clicks' => 0,
			'conversion_rate' => 0,
			'engagement_rate' => 0,
			'device_stats' => array(),
			'browser_stats' => array(),
			'city_stats' => array(),
			'top_events' => array()
		);
	}

	/**
	 * Get temporal analytics data for charts
	 * Returns data grouped by day for the specified date range
	 */
	public function get_vendor_temporal_analytics( $vendor_id = null, $date_range = null ) {
		global $wpdb;

		if ( ! $vendor_id ) {
			$vendor_id = get_current_user_id();
		}

		// Get vendor's events
		$events = get_posts( array(
			'post_type'      => 'event',
			'author'         => $vendor_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any'
		) );

		if ( empty( $events ) ) {
			return array(
				'labels' => array(),
				'views' => array(),
				'booking_clicks' => array(),
				'wishlist_adds' => array(),
				'contact_clicks' => array(),
				'share_clicks' => array()
			);
		}

		$event_ids = implode( ',', array_map( 'absint', $events ) );

		// Default to last 7 days if no range specified
		if ( ! $date_range ) {
			$date_range = array(
				'start' => strtotime( '-6 days', strtotime( 'midnight' ) ),
				'end' => strtotime( 'midnight' )
			);
		}

		// Query data grouped by date
		$query = $wpdb->prepare(
			"SELECT
				DATE(created_at) as date,
				COUNT(CASE WHEN event_type = 'view' THEN 1 END) as views,
				COUNT(CASE WHEN event_type = 'booking_click' THEN 1 END) as booking_clicks,
				COUNT(CASE WHEN event_type = 'wishlist_add' THEN 1 END) as wishlist_adds,
				COUNT(CASE WHEN event_type = 'contact_click' THEN 1 END) as contact_clicks,
				COUNT(CASE WHEN event_type = 'share_click' THEN 1 END) as share_clicks
			FROM {$this->table_name}
			WHERE event_id IN ($event_ids)
			AND created_at BETWEEN %s AND %s
			GROUP BY DATE(created_at)
			ORDER BY date ASC",
			date( 'Y-m-d 00:00:00', $date_range['start'] ),
			date( 'Y-m-d 23:59:59', $date_range['end'] )
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		// Create a complete date range array
		$data = array();
		$current = $date_range['start'];
		while ( $current <= $date_range['end'] ) {
			$date_key = date( 'Y-m-d', $current );
			$data[ $date_key ] = array(
				'views' => 0,
				'booking_clicks' => 0,
				'wishlist_adds' => 0,
				'contact_clicks' => 0,
				'share_clicks' => 0
			);
			$current = strtotime( '+1 day', $current );
		}

		// Fill in actual data
		foreach ( $results as $row ) {
			$date_key = $row['date'];
			if ( isset( $data[ $date_key ] ) ) {
				$data[ $date_key ] = array(
					'views' => intval( $row['views'] ),
					'booking_clicks' => intval( $row['booking_clicks'] ),
					'wishlist_adds' => intval( $row['wishlist_adds'] ),
					'contact_clicks' => intval( $row['contact_clicks'] ),
					'share_clicks' => intval( $row['share_clicks'] )
				);
			}
		}

		// Format for Chart.js
		$labels = array();
		$views = array();
		$booking_clicks = array();
		$wishlist_adds = array();
		$contact_clicks = array();
		$share_clicks = array();

		foreach ( $data as $date => $values ) {
			// Format date for display (e.g., "15 Jan")
			$labels[] = date( 'd M', strtotime( $date ) );
			$views[] = $values['views'];
			$booking_clicks[] = $values['booking_clicks'];
			$wishlist_adds[] = $values['wishlist_adds'];
			$contact_clicks[] = $values['contact_clicks'];
			$share_clicks[] = $values['share_clicks'];
		}

		return array(
			'labels' => $labels,
			'views' => $views,
			'booking_clicks' => $booking_clicks,
			'wishlist_adds' => $wishlist_adds,
			'contact_clicks' => $contact_clicks,
			'share_clicks' => $share_clicks
		);
	}
}

// Initialize
EL_Analytics::instance();
