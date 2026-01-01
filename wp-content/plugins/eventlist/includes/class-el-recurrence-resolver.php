<?php
/**
 * EventList Recurrence Resolver
 *
 * Résout les paramètres de récurrence en créneaux réels
 * V1 Le Hiboo - Mapping Ticket ↔ Créneau
 *
 * @package EventList
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class EL_Recurrence_Resolver
 *
 * Génère les créneaux réels à partir des paramètres de récurrence
 * Limite : 1 an maximum pour éviter l'explosion de données
 */
class EL_Recurrence_Resolver {

	/**
	 * Limite maximale de génération en jours
	 */
	const MAX_DAYS_LIMIT = 365;

	/**
	 * Préfixe des métadonnées
	 */
	const META_PREFIX = 'ova_mb_event_';

	/**
	 * Résout les créneaux récurrents pour un événement
	 *
	 * @param int $event_id ID de l'événement
	 * @return array Liste des créneaux générés
	 */
	public static function resolve( $event_id ) {
		$event_id = absint( $event_id );

		if ( ! $event_id ) {
			return array();
		}

		$option_calendar = get_post_meta( $event_id, self::META_PREFIX . 'option_calendar', true );

		// Si mode manuel, ne rien faire - les créneaux existent déjà
		if ( $option_calendar !== 'auto' ) {
			return array();
		}

		// Récupérer les paramètres de récurrence
		$params = self::get_recurrence_params( $event_id );

		if ( empty( $params['recurrence_days'] ) || empty( $params['ts_start'] ) ) {
			return array();
		}

		// Générer les créneaux
		$slots = self::generate_slots( $params );

		if ( empty( $slots ) ) {
			return array();
		}

		// Sauvegarder les créneaux générés
		self::save_resolved_slots( $event_id, $slots );

		return $slots;
	}

	/**
	 * Récupère les paramètres de récurrence depuis les métadonnées
	 *
	 * @param int $event_id ID de l'événement
	 * @return array Paramètres de récurrence
	 */
	public static function get_recurrence_params( $event_id ) {
		$prefix = self::META_PREFIX;

		return array(
			'start_date'      => get_post_meta( $event_id, $prefix . 'calendar_start_date', true ) ?: '',
			'end_date'        => get_post_meta( $event_id, $prefix . 'calendar_recurrence_end_time', true ) ?: '',
			'recurrence_days' => get_post_meta( $event_id, $prefix . 'recurrence_days', true ) ?: '',
			'ts_start'        => get_post_meta( $event_id, $prefix . 'ts_start', true ) ?: array(),
			'ts_end'          => get_post_meta( $event_id, $prefix . 'ts_end', true ) ?: array(),
		);
	}

	/**
	 * Génère les créneaux à partir des paramètres
	 *
	 * @param array $params Paramètres de récurrence
	 * @return array Liste des créneaux générés
	 */
	public static function generate_slots( $params ) {
		$slots = array();

		// Parser les dates
		$start_date = ! empty( $params['start_date'] ) ? strtotime( $params['start_date'] ) : time();
		$end_date   = ! empty( $params['end_date'] ) ? strtotime( $params['end_date'] ) : null;

		// Appliquer la limite de 1 an
		$max_end_date = strtotime( '+' . self::MAX_DAYS_LIMIT . ' days', $start_date );

		if ( $end_date === null || $end_date > $max_end_date ) {
			$end_date = $max_end_date;
			// Note: On pourrait logger un warning ici si on dépasse la limite
		}

		// Parser les jours de récurrence
		$recurrence_days = self::parse_recurrence_days( $params['recurrence_days'] );

		if ( empty( $recurrence_days ) ) {
			return array();
		}

		// Parcourir chaque jour dans la plage
		$current_date = $start_date;

		while ( $current_date <= $end_date ) {
			$day_of_week = (int) date( 'w', $current_date ); // 0 = dimanche, 6 = samedi
			$date_str    = date( 'Y-m-d', $current_date );

			// Vérifier si ce jour est dans les jours de récurrence
			if ( in_array( $day_of_week, $recurrence_days, true ) ) {
				// Récupérer les créneaux horaires pour ce jour
				$day_key = (string) $day_of_week;
				$day_slots = self::get_time_slots_for_day( $params, $day_key );

				foreach ( $day_slots as $time_slot ) {
					$slot_id = self::generate_unique_slot_id( $date_str, $time_slot['start'], $time_slot['end'] );

					$slots[] = array(
						'calendar_id' => $slot_id,
						'start_date'  => $date_str,
						'start_time'  => $time_slot['start'],
						'end_time'    => $time_slot['end'],
						'source'      => 'recurrence',
						'day_of_week' => $day_of_week,
					);
				}
			}

			// Jour suivant
			$current_date = strtotime( '+1 day', $current_date );
		}

		return $slots;
	}

	/**
	 * Parse les jours de récurrence depuis différents formats
	 *
	 * @param mixed $recurrence_days Jours (string "1,3,5" ou array)
	 * @return array Liste des numéros de jours (0-6)
	 */
	public static function parse_recurrence_days( $recurrence_days ) {
		if ( empty( $recurrence_days ) ) {
			return array();
		}

		// Si c'est déjà un array
		if ( is_array( $recurrence_days ) ) {
			return array_map( 'intval', $recurrence_days );
		}

		// Si c'est une string (ex: "1,3,5" ou "135")
		if ( is_string( $recurrence_days ) ) {
			// Essayer de parser comme liste séparée par virgules
			if ( strpos( $recurrence_days, ',' ) !== false ) {
				return array_map( 'intval', explode( ',', $recurrence_days ) );
			}

			// Sinon, chaque caractère est un jour
			$days = array();
			for ( $i = 0; $i < strlen( $recurrence_days ); $i++ ) {
				$day = intval( $recurrence_days[ $i ] );
				if ( $day >= 0 && $day <= 6 ) {
					$days[] = $day;
				}
			}
			return array_unique( $days );
		}

		return array();
	}

	/**
	 * Récupère les créneaux horaires pour un jour donné
	 *
	 * @param array $params Paramètres de récurrence
	 * @param string $day_key Clé du jour (0-6)
	 * @return array Liste des créneaux horaires
	 */
	public static function get_time_slots_for_day( $params, $day_key ) {
		$time_slots = array();

		$ts_start = isset( $params['ts_start'][ $day_key ] ) ? $params['ts_start'][ $day_key ] : array();
		$ts_end   = isset( $params['ts_end'][ $day_key ] ) ? $params['ts_end'][ $day_key ] : array();

		if ( ! is_array( $ts_start ) ) {
			$ts_start = array( $ts_start );
		}
		if ( ! is_array( $ts_end ) ) {
			$ts_end = array( $ts_end );
		}

		foreach ( $ts_start as $index => $start_time ) {
			if ( empty( $start_time ) ) {
				continue;
			}

			$end_time = isset( $ts_end[ $index ] ) ? $ts_end[ $index ] : '';

			$time_slots[] = array(
				'start' => self::normalize_time( $start_time ),
				'end'   => self::normalize_time( $end_time ),
			);
		}

		return $time_slots;
	}

	/**
	 * Normalise une heure au format HH:MM
	 *
	 * @param string $time Heure à normaliser
	 * @return string Heure normalisée
	 */
	public static function normalize_time( $time ) {
		if ( empty( $time ) ) {
			return '';
		}

		// Déjà au bon format
		if ( preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			return $time;
		}

		// Essayer de parser
		$timestamp = strtotime( $time );
		if ( $timestamp !== false ) {
			return date( 'H:i', $timestamp );
		}

		return $time;
	}

	/**
	 * Génère un ID unique pour un créneau
	 *
	 * @param string $date Date
	 * @param string $start_time Heure de début
	 * @param string $end_time Heure de fin
	 * @return string ID unique
	 */
	public static function generate_unique_slot_id( $date, $start_time, $end_time ) {
		$base = $date . '_' . $start_time . '_' . $end_time;
		return 'slot_' . substr( md5( $base ), 0, 12 );
	}

	/**
	 * Sauvegarde les créneaux résolus dans les métadonnées
	 *
	 * @param int $event_id ID de l'événement
	 * @param array $slots Créneaux à sauvegarder
	 */
	public static function save_resolved_slots( $event_id, $slots ) {
		// Récupérer les créneaux manuels existants (ne pas les écraser)
		$existing_calendar = get_post_meta( $event_id, self::META_PREFIX . 'calendar', true );

		if ( ! is_array( $existing_calendar ) ) {
			$existing_calendar = array();
		}

		// Filtrer pour garder uniquement les créneaux manuels
		$manual_slots = array_filter( $existing_calendar, function( $slot ) {
			return ! isset( $slot['source'] ) || $slot['source'] !== 'recurrence';
		});

		// Combiner manuels + récurrents
		$all_slots = array_merge( array_values( $manual_slots ), $slots );

		// Sauvegarder
		update_post_meta( $event_id, self::META_PREFIX . 'calendar', $all_slots );

		// Aussi sauvegarder dans calendar_recurrence pour compatibilité
		update_post_meta( $event_id, self::META_PREFIX . 'calendar_recurrence', $slots );
	}

	/**
	 * Hook à appeler lors de la sauvegarde d'un événement
	 *
	 * @param int $event_id ID de l'événement
	 */
	public static function on_event_save( $event_id ) {
		// Vérifier que c'est bien un événement
		if ( get_post_type( $event_id ) !== 'event' ) {
			return;
		}

		// Résoudre les créneaux
		self::resolve( $event_id );
	}

	/**
	 * Supprime les créneaux récurrents d'un événement
	 *
	 * @param int $event_id ID de l'événement
	 */
	public static function clear_recurrence_slots( $event_id ) {
		$calendar = get_post_meta( $event_id, self::META_PREFIX . 'calendar', true );

		if ( ! is_array( $calendar ) ) {
			return;
		}

		// Garder uniquement les créneaux manuels
		$manual_slots = array_filter( $calendar, function( $slot ) {
			return ! isset( $slot['source'] ) || $slot['source'] !== 'recurrence';
		});

		update_post_meta( $event_id, self::META_PREFIX . 'calendar', array_values( $manual_slots ) );
		delete_post_meta( $event_id, self::META_PREFIX . 'calendar_recurrence' );
	}

	/**
	 * Vérifie si un événement utilise la récurrence
	 *
	 * @param int $event_id ID de l'événement
	 * @return bool
	 */
	public static function is_recurrence_enabled( $event_id ) {
		$option = get_post_meta( $event_id, self::META_PREFIX . 'option_calendar', true );
		return $option === 'auto';
	}

	/**
	 * Compte le nombre de créneaux qui seraient générés
	 *
	 * @param int $event_id ID de l'événement
	 * @return int Nombre de créneaux
	 */
	public static function count_potential_slots( $event_id ) {
		$params = self::get_recurrence_params( $event_id );
		$slots = self::generate_slots( $params );
		return count( $slots );
	}
}
