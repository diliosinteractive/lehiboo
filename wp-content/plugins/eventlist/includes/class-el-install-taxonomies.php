<?php
/**
 * V1 Le Hiboo - Installation des termes par défaut pour les nouvelles taxonomies
 *
 * Ce fichier crée les termes par défaut lors de l'activation ou de la mise à jour du plugin
 */

defined( 'ABSPATH' ) || exit;

class EL_Install_Taxonomies {

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'create_default_terms' ), 20 );
	}

	/**
	 * Créer les termes par défaut pour les nouvelles taxonomies
	 * Cette fonction s'exécute une seule fois après l'installation
	 */
	public static function create_default_terms() {

		// Vérifier si les termes ont déjà été créés
		if ( get_option( 'el_v1_taxonomies_installed' ) ) {
			return;
		}

		// Thématiques
		$thematiques = array(
			'Cuisine & Gastronomie',
			'Art & Culture',
			'Sport & Bien-être',
			'Nature & Environnement',
			'Musique & Concert',
			'Cinéma & Spectacle',
			'Numérique & Technologie',
			'Famille & Enfants',
			'Formation & Éducation',
			'Patrimoine & Histoire',
			'Mode & Design',
			'Littérature & Lecture',
		);

		foreach ( $thematiques as $thematique ) {
			if ( ! term_exists( $thematique, 'event_thematique' ) ) {
				wp_insert_term( $thematique, 'event_thematique' );
			}
		}

		// Événements Spéciaux
		$evenements_speciaux = array(
			'Fête de la musique',
			'Journées du patrimoine',
			'Nuit des musées',
			'Semaine du développement durable',
			'Festival d\'été',
			'Halloween',
			'Marché de Noël',
			'Nouvel An',
			'Carnaval',
			'Fête nationale',
			'Saint-Valentin',
			'Fête des mères',
			'Fête des pères',
		);

		foreach ( $evenements_speciaux as $special ) {
			if ( ! term_exists( $special, 'event_special' ) ) {
				wp_insert_term( $special, 'event_special' );
			}
		}

		// Saisons
		$saisons = array(
			'Printemps',
			'Été',
			'Automne',
			'Hiver',
		);

		foreach ( $saisons as $saison ) {
			if ( ! term_exists( $saison, 'event_saison' ) ) {
				wp_insert_term( $saison, 'event_saison' );
			}
		}

		// Marquer comme installé
		update_option( 'el_v1_taxonomies_installed', true );
	}

	/**
	 * Forcer la création des termes (utile pour débugger)
	 */
	public static function force_create_terms() {
		delete_option( 'el_v1_taxonomies_installed' );
		self::create_default_terms();
	}
}

EL_Install_Taxonomies::init();
