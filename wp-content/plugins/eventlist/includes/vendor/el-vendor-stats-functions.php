<?php
/**
 * Vendor Statistics Functions
 * Fonctions pour calculer les KPI du dashboard partenaire
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Récupère les statistiques des participants pour un partenaire
 * @param int $vendor_id ID de l'utilisateur vendeur
 * @param array $date_range Plage de dates ['start' => timestamp, 'end' => timestamp]
 * @return array Statistiques calculées
 */
function el_get_vendor_participant_stats( $vendor_id = null, $date_range = null ) {
	global $wpdb;

	if ( ! $vendor_id ) {
		$vendor_id = get_current_user_id();
	}

	// Récupérer tous les events du vendeur
	$events = get_posts( array(
		'post_type'      => 'event',
		'author'         => $vendor_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'any'
	) );

	if ( empty( $events ) ) {
		return array(
			'total_participants' => 0,
			'age_stats' => array(),
			'gender_stats' => array(),
			'city_stats' => array(),
		);
	}

	$event_ids = implode( ',', array_map( 'absint', $events ) );

	// Query pour récupérer les bookings
	$date_filter = '';
	if ( $date_range && isset( $date_range['start'] ) && isset( $date_range['end'] ) ) {
		$date_filter = $wpdb->prepare(
			" AND b.created_at BETWEEN %s AND %s",
			date( 'Y-m-d 00:00:00', $date_range['start'] ),
			date( 'Y-m-d 23:59:59', $date_range['end'] )
		);
	}

	$query = "SELECT b.id, b.event_id, b.id_calendar, b.data_customers, b.data_checkout_field, b.created_at, b.status
			  FROM {$wpdb->prefix}el_bookings b
			  WHERE b.event_id IN ($event_ids)
			  AND b.status != 'trash'
			  $date_filter
			  ORDER BY b.created_at DESC";

	$bookings = $wpdb->get_results( $query );

	// Initialiser les statistiques
	$stats = array(
		'total_participants' => 0,
		'total_bookings' => count( $bookings ),
		'age_groups' => array(
			'0-17' => 0,
			'18-25' => 0,
			'26-35' => 0,
			'36-50' => 0,
			'51-65' => 0,
			'66+' => 0,
			'unknown' => 0
		),
		'gender_stats' => array(
			'male' => 0,
			'female' => 0,
			'other' => 0,
			'unknown' => 0
		),
		'cities' => array(),
		'avg_age' => 0,
		'total_age_count' => 0,
		'age_sum' => 0
	);

	foreach ( $bookings as $booking ) {
		// Décoder les données des customers
		$data_customers = maybe_unserialize( $booking->data_customers );
		$data_checkout_field = maybe_unserialize( $booking->data_checkout_field );

		if ( ! empty( $data_customers ) && is_array( $data_customers ) ) {
			foreach ( $data_customers as $ticket_customers ) {
				if ( is_array( $ticket_customers ) ) {
					foreach ( $ticket_customers as $customer ) {
						$stats['total_participants']++;

						// Analyser les champs personnalisés du customer
						if ( isset( $customer['checkout_fields'] ) && is_array( $customer['checkout_fields'] ) ) {
							$fields = $customer['checkout_fields'];

							// Traiter la date de naissance
							$birthdate = isset( $fields['date_naissance'] ) ? $fields['date_naissance'] :
										 ( isset( $fields['birthdate'] ) ? $fields['birthdate'] : null );

							if ( $birthdate ) {
								$age = el_calculate_age_from_date( $birthdate );
								if ( $age !== false ) {
									$stats['age_sum'] += $age;
									$stats['total_age_count']++;

									// Catégoriser par groupe d'âge
									if ( $age < 18 ) {
										$stats['age_groups']['0-17']++;
									} elseif ( $age <= 25 ) {
										$stats['age_groups']['18-25']++;
									} elseif ( $age <= 35 ) {
										$stats['age_groups']['26-35']++;
									} elseif ( $age <= 50 ) {
										$stats['age_groups']['36-50']++;
									} elseif ( $age <= 65 ) {
										$stats['age_groups']['51-65']++;
									} else {
										$stats['age_groups']['66+']++;
									}
								} else {
									$stats['age_groups']['unknown']++;
								}
							} else {
								$stats['age_groups']['unknown']++;
							}

							// Traiter le genre
							$gender = isset( $fields['gender'] ) ? strtolower( $fields['gender'] ) :
									  ( isset( $fields['sexe'] ) ? strtolower( $fields['sexe'] ) : null );

							if ( $gender ) {
								if ( in_array( $gender, array( 'male', 'homme', 'm', 'masculin' ) ) ) {
									$stats['gender_stats']['male']++;
								} elseif ( in_array( $gender, array( 'female', 'femme', 'f', 'féminin', 'feminin' ) ) ) {
									$stats['gender_stats']['female']++;
								} else {
									$stats['gender_stats']['other']++;
								}
							} else {
								$stats['gender_stats']['unknown']++;
							}

							// Traiter la ville
							$city = isset( $fields['city'] ) ? trim( $fields['city'] ) :
									( isset( $fields['ville'] ) ? trim( $fields['ville'] ) : null );

							if ( $city && ! empty( $city ) ) {
								if ( ! isset( $stats['cities'][ $city ] ) ) {
									$stats['cities'][ $city ] = 0;
								}
								$stats['cities'][ $city ]++;
							}
						}
					}
				}
			}
		}

		// Traiter aussi les données du booking principal si disponibles
		if ( ! empty( $data_checkout_field ) && is_array( $data_checkout_field ) ) {
			// On ne compte pas deux fois le participant principal
			// Mais on peut extraire des infos supplémentaires si nécessaire
		}
	}

	// Calculer l'âge moyen
	if ( $stats['total_age_count'] > 0 ) {
		$stats['avg_age'] = round( $stats['age_sum'] / $stats['total_age_count'], 1 );
	}

	// Trier les villes par nombre de participants
	arsort( $stats['cities'] );

	return $stats;
}

/**
 * Calcule l'âge à partir d'une date de naissance
 * @param string $birthdate Date de naissance
 * @return int|false Âge ou false si invalide
 */
function el_calculate_age_from_date( $birthdate ) {
	if ( empty( $birthdate ) ) {
		return false;
	}

	try {
		$birth = new DateTime( $birthdate );
		$today = new DateTime();
		$age = $today->diff( $birth )->y;

		// Vérifier que l'âge est raisonnable (entre 0 et 120)
		if ( $age >= 0 && $age <= 120 ) {
			return $age;
		}
	} catch ( Exception $e ) {
		return false;
	}

	return false;
}

/**
 * Récupère les statistiques financières pour un partenaire
 * @param int $vendor_id ID de l'utilisateur vendeur
 * @param array $date_range Plage de dates
 * @return array Statistiques financières
 */
function el_get_vendor_financial_stats( $vendor_id = null, $date_range = null ) {
	global $wpdb;

	if ( ! $vendor_id ) {
		$vendor_id = get_current_user_id();
	}

	// Récupérer tous les events du vendeur
	$events = get_posts( array(
		'post_type'      => 'event',
		'author'         => $vendor_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'any'
	) );

	if ( empty( $events ) ) {
		return array(
			'total_revenue' => 0,
			'total_bookings' => 0,
			'avg_booking_value' => 0,
			'completed_bookings' => 0,
			'cancelled_bookings' => 0,
		);
	}

	$event_ids = implode( ',', array_map( 'absint', $events ) );

	// Date filter
	$date_filter = '';
	if ( $date_range && isset( $date_range['start'] ) && isset( $date_range['end'] ) ) {
		$date_filter = $wpdb->prepare(
			" AND b.created_at BETWEEN %s AND %s",
			date( 'Y-m-d 00:00:00', $date_range['start'] ),
			date( 'Y-m-d 23:59:59', $date_range['end'] )
		);
	}

	$query = "SELECT
				COUNT(*) as total_bookings,
				SUM(CASE WHEN b.status = 'completed' OR b.status = 'complete' THEN 1 ELSE 0 END) as completed_bookings,
				SUM(CASE WHEN b.status = 'cancelled' OR b.status = 'trash' THEN 1 ELSE 0 END) as cancelled_bookings,
				SUM(CASE WHEN b.status = 'completed' OR b.status = 'complete' THEN b.total ELSE 0 END) as total_revenue,
				COUNT(DISTINCT b.event_id) as unique_events
			  FROM {$wpdb->prefix}el_bookings b
			  WHERE b.event_id IN ($event_ids)
			  AND b.status != 'trash'
			  $date_filter";

	$results = $wpdb->get_row( $query, ARRAY_A );

	$stats = array(
		'total_revenue' => floatval( $results['total_revenue'] ?? 0 ),
		'total_bookings' => intval( $results['total_bookings'] ?? 0 ),
		'completed_bookings' => intval( $results['completed_bookings'] ?? 0 ),
		'cancelled_bookings' => intval( $results['cancelled_bookings'] ?? 0 ),
		'unique_events' => intval( $results['unique_events'] ?? 0 ),
		'avg_booking_value' => 0,
		'completion_rate' => 0
	);

	// Calculer la valeur moyenne par réservation
	if ( $stats['completed_bookings'] > 0 ) {
		$stats['avg_booking_value'] = $stats['total_revenue'] / $stats['completed_bookings'];
	}

	// Calculer le taux de complétion
	if ( $stats['total_bookings'] > 0 ) {
		$stats['completion_rate'] = ( $stats['completed_bookings'] / $stats['total_bookings'] ) * 100;
	}

	return $stats;
}

/**
 * Récupère les événements les plus populaires d'un partenaire
 * @param int $vendor_id ID de l'utilisateur vendeur
 * @param int $limit Nombre d'événements à retourner
 * @param array $date_range Plage de dates
 * @return array Liste des événements avec leurs statistiques
 */
function el_get_vendor_popular_events( $vendor_id = null, $limit = 5, $date_range = null ) {
	global $wpdb;

	if ( ! $vendor_id ) {
		$vendor_id = get_current_user_id();
	}

	// Récupérer tous les events du vendeur
	$events = get_posts( array(
		'post_type'      => 'event',
		'author'         => $vendor_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'any'
	) );

	if ( empty( $events ) ) {
		return array();
	}

	$event_ids = implode( ',', array_map( 'absint', $events ) );

	// Date filter
	$date_filter = '';
	if ( $date_range && isset( $date_range['start'] ) && isset( $date_range['end'] ) ) {
		$date_filter = $wpdb->prepare(
			" AND b.created_at BETWEEN %s AND %s",
			date( 'Y-m-d 00:00:00', $date_range['start'] ),
			date( 'Y-m-d 23:59:59', $date_range['end'] )
		);
	}

	$query = "SELECT
				b.event_id,
				COUNT(*) as booking_count,
				SUM(b.total) as total_revenue,
				SUM(b.qty) as total_tickets
			  FROM {$wpdb->prefix}el_bookings b
			  WHERE b.event_id IN ($event_ids)
			  AND (b.status = 'completed' OR b.status = 'complete')
			  $date_filter
			  GROUP BY b.event_id
			  ORDER BY booking_count DESC
			  LIMIT " . absint( $limit );

	$results = $wpdb->get_results( $query );

	$popular_events = array();
	foreach ( $results as $result ) {
		$event = get_post( $result->event_id );
		if ( $event ) {
			$popular_events[] = array(
				'event_id' => $result->event_id,
				'title' => $event->post_title,
				'booking_count' => intval( $result->booking_count ),
				'total_revenue' => floatval( $result->total_revenue ),
				'total_tickets' => intval( $result->total_tickets ),
				'permalink' => get_permalink( $result->event_id )
			);
		}
	}

	return $popular_events;
}

/**
 * Formatte les statistiques pour l'affichage
 * @param array $stats Statistiques brutes
 * @return array Statistiques formatées
 */
function el_format_vendor_stats( $stats ) {
	$currency_symbol = _el_symbol_price();

	return array(
		'total_revenue' => el_price( $stats['total_revenue'] ?? 0 ),
		'total_bookings' => number_format( $stats['total_bookings'] ?? 0, 0, ',', ' ' ),
		'avg_booking_value' => el_price( $stats['avg_booking_value'] ?? 0 ),
		'completion_rate' => round( $stats['completion_rate'] ?? 0, 1 ) . '%',
	);
}

/**
 * Calcule la plage de dates à partir du filtre
 * @param string $range Type de plage (7_day, month, last_month, year, custom)
 * @param array $get Paramètres GET
 * @return array|null Plage de dates ['start' => timestamp, 'end' => timestamp] ou null
 */
function el_get_date_range_from_filter( $range, $get = array() ) {
	$current_time = current_time( 'timestamp' );

	switch ( $range ) {
		case '7_day':
			return array(
				'start' => strtotime( '-6 days', strtotime( 'midnight', $current_time ) ),
				'end' => strtotime( 'midnight', $current_time )
			);

		case 'month':
			return array(
				'start' => strtotime( gmdate( 'Y-m-01', $current_time ) ),
				'end' => $current_time
			);

		case 'last_month':
			$first_day_current_month = strtotime( gmdate( 'Y-m-01', $current_time ) );
			return array(
				'start' => strtotime( gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) ),
				'end' => strtotime( gmdate( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) )
			);

		case 'year':
			return array(
				'start' => strtotime( gmdate( 'Y-01-01', $current_time ) ),
				'end' => $current_time
			);

		case 'custom':
			$start_date = isset( $get['start_date'] ) ? sanitize_text_field( $get['start_date'] ) : null;
			$end_date = isset( $get['end_date'] ) ? sanitize_text_field( $get['end_date'] ) : null;

			if ( $start_date && $end_date ) {
				return array(
					'start' => strtotime( $start_date ),
					'end' => strtotime( $end_date )
				);
			}
			return null;

		default:
			return null;
	}
}
