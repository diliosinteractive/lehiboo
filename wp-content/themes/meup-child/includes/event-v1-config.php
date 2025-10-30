<?php
/**
 * Configuration LeHiboo V1 - Gestion des fonctionnalités actives
 *
 * Ce fichier configure les fonctionnalités activées/désactivées pour la V1
 * en utilisant les filtres WordPress existants dans EventList
 *
 * @package LeHiboo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ===========================================
 * BILLETTERIE - Configuration V1
 * ===========================================
 */

// ✅ ACTIVER "Type: No Seat" - Mode liste d'inscription (V1)
// Ce mode permet de créer des billets/inscriptions sans numérotation de sièges
add_filter( 'el_show_ticket_type_no_seat', '__return_true' );

// ❌ DÉSACTIVER "Type: Simple Seat" - Sièges numérotés (reporté en V2)
add_filter( 'el_show_ticket_type_simple', '__return_false' );

// ❌ DÉSACTIVER "Type: Map" - Plan de salle interactif (reporté en V2)
add_filter( 'el_show_ticket_type_map', '__return_false' );

// ❌ DÉSACTIVER "Créer une billetterie payante" - Fonctionnalité prochainement disponible
add_filter( 'el_show_ticket_paid_ticketing', '__return_false' );

/**
 * ===========================================
 * CALENDRIER - Désactivation récurrence annuelle
 * ===========================================
 */

// Désactiver la récurrence "Yearly" (tous les X ans) - non nécessaire en V1
add_filter( 'el_show_yearly_recurrence', '__return_false' );

/**
 * ===========================================
 * LOCALISATION - Configuration
 * ===========================================
 */

// Afficher l'option "Dans un lieu physique" (activé par défaut)
add_filter( 'el_show_event_type_physical', '__return_true' );

// Afficher l'option "En ligne" (activé par défaut)
add_filter( 'el_show_event_type_online', '__return_true' );

// Afficher l'option "À la maison" (activé par défaut)
add_filter( 'el_show_event_type_home', '__return_true' );

/**
 * ===========================================
 * FONCTIONS HELPER - Formats et valeurs par défaut
 * ===========================================
 */

/**
 * Définir les valeurs par défaut des heures (specs PDF)
 * - Date de début : 00:00 par défaut
 * - Date de fin : 23:45 par défaut
 */
function lehiboo_default_start_time() {
    return '00:00';
}

function lehiboo_default_end_time() {
    return '23:45';
}

/**
 * Indicateur Euro pour les prix (specs PDF)
 *
 * @return string Symbole Euro avec espace
 */
function lehiboo_price_indicator() {
    return '€';
}

/**
 * ===========================================
 * MESSAGES DE VALIDATION
 * ===========================================
 */

/**
 * Messages d'erreur personnalisés FR
 */
function lehiboo_validation_messages() {
    return array(
        'required_field'        => 'Ce champ est obligatoire',
        'invalid_email'         => 'Veuillez saisir une adresse email valide',
        'invalid_url'           => 'Veuillez saisir une URL valide (https://...)',
        'invalid_price'         => 'Veuillez saisir un prix valide (ex: 10.50)',
        'invalid_date'          => 'Veuillez saisir une date valide (JJ/MM/AAAA)',
        'invalid_time'          => 'Veuillez saisir une heure valide (HH:MM)',
        'event_saved'           => 'Événement enregistré avec succès !',
        'ticket_added'          => 'Billet ajouté avec succès',
        'ticket_validated'      => 'Billet validé',
        'slot_added'            => 'Créneau ajouté avec succès',
    );
}

/**
 * ===========================================
 * CONFIGURATION UX - Textes d'aide
 * ===========================================
 */

/**
 * Textes d'aide (tooltips) pour les champs complexes
 */
function lehiboo_get_help_texts() {
    return array(
        'venue_event_type' => 'Indiquez si votre événement se déroule en intérieur, en extérieur ou les deux',
        'venue_pmr'        => 'Indiquez si votre lieu est accessible aux personnes à mobilité réduite (rampes, ascenseurs, etc.)',
        'ticket_colors'    => 'Ces couleurs seront utilisées pour le design de vos billets PDF',
        'booking_before'   => 'Les réservations seront closes X minutes avant le début de l\'activité',
        'disable_dates'    => 'Vous pouvez exclure certaines dates spécifiques de votre récurrence',
    );
}

/**
 * ===========================================
 * PLACEHOLDERS AMÉLIORÉS
 * ===========================================
 */

/**
 * Placeholders personnalisés pour améliorer l'UX
 */
function lehiboo_get_placeholders() {
    return array(
        'ticket_name'           => 'Nom du tarif (ex: Tarif Étudiant, Tarif Adulte +18 ans)',
        'venue_name'            => 'Nom du lieu (ex: Maison des Associations, Salle Polyvalente)',
        'venue_parking'         => 'Décrivez les options de stationnement disponibles...',
        'venue_access'          => 'Indiquez comment accéder au lieu (métro, bus, parking...)...',
        'venue_pmr_info'        => 'Détaillez les aménagements PMR (rampes, ascenseur, toilettes adaptées...)...',
        'venue_restaurant_info' => 'Décrivez la restauration disponible sur place...',
        'external_ticket_url'   => 'https://www.votre-billetterie.com/evenement',
        'price_name'            => 'Nom du tarif (ex: Tarif Adulte)',
        'price_amount'          => 'Prix en euros (ex: 15.00)',
    );
}

/**
 * ===========================================
 * STATS & COMPTEURS
 * ===========================================
 */

/**
 * Compter le nombre de billets configurés pour un événement
 *
 * @param int $post_id ID de l'événement
 * @return int Nombre de billets
 */
function lehiboo_count_tickets( $post_id ) {
    $tickets = get_post_meta( $post_id, OVA_METABOX_EVENT . 'ticket', true );
    if ( ! $tickets || ! is_array( $tickets ) ) {
        return 0;
    }

    $count = 0;
    foreach ( $tickets as $ticket ) {
        if ( isset( $ticket['name_ticket'] ) && ! empty( $ticket['name_ticket'] ) ) {
            $count++;
        }
    }

    return $count;
}

/**
 * Compter le nombre de créneaux configurés pour un événement
 *
 * @param int $post_id ID de l'événement
 * @return int Nombre de créneaux
 */
function lehiboo_count_slots( $post_id ) {
    $calendar = get_post_meta( $post_id, OVA_METABOX_EVENT . 'calendar', true );
    if ( ! $calendar || ! is_array( $calendar ) ) {
        return 0;
    }

    $count = 0;
    foreach ( $calendar as $slot ) {
        if ( isset( $slot['date'] ) && ! empty( $slot['date'] ) ) {
            $count++;
        }
    }

    return $count;
}

/**
 * Vérifier si une section est complète
 *
 * @param int $post_id ID de l'événement
 * @param string $section Section à vérifier (general, localisation, calendar, ticket)
 * @return bool Section complète ou non
 */
function lehiboo_is_section_complete( $post_id, $section ) {
    $prefix = OVA_METABOX_EVENT;

    switch ( $section ) {
        case 'general':
            $title = get_the_title( $post_id );
            $category = wp_get_post_terms( $post_id, 'event_cat' );
            return ! empty( $title ) && ! empty( $category );

        case 'localisation':
            $event_type = get_post_meta( $post_id, $prefix . 'event_type', true );
            if ( $event_type === 'classic' ) {
                $address = get_post_meta( $post_id, $prefix . 'address', true );
                return ! empty( $address );
            }
            return ! empty( $event_type );

        case 'calendar':
            $calendar = get_post_meta( $post_id, $prefix . 'calendar', true );
            $option_calendar = get_post_meta( $post_id, $prefix . 'option_calendar', true );

            if ( $option_calendar === 'auto' ) {
                $start_date = get_post_meta( $post_id, $prefix . 'calendar_start_date', true );
                $end_date = get_post_meta( $post_id, $prefix . 'calendar_end_date', true );
                return ! empty( $start_date ) && ! empty( $end_date );
            } else {
                return is_array( $calendar ) && count( $calendar ) > 0;
            }

        case 'ticket':
            // Optionnel, donc toujours valide
            return true;

        default:
            return false;
    }
}

/**
 * Calculer le pourcentage de complétion de l'événement
 *
 * @param int $post_id ID de l'événement
 * @return int Pourcentage (0-100)
 */
function lehiboo_event_completion_percentage( $post_id ) {
    $sections = array( 'general', 'localisation', 'calendar', 'ticket' );
    $completed = 0;

    foreach ( $sections as $section ) {
        if ( lehiboo_is_section_complete( $post_id, $section ) ) {
            $completed++;
        }
    }

    return round( ( $completed / count( $sections ) ) * 100 );
}
