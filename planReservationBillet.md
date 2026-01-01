Claude’s Plan
Plan de Développement - Refonte Billetterie LeHiboo
Vue d'Ensemble
Objectif : Créer un système de billetterie moderne avec mapping ticket ↔ créneau, résolution des récurrences, et API mobile complète. Périmètre :
Backend (PHP/WordPress)
Frontend Vendor (formulaire création événement)
Frontend Public (réservation)
API Mobile (REST API)
Documentation
Architecture Cible
Structure de Données

// Créneau (calendar) - NOUVEAU FORMAT
$slot = [
    'calendar_id' => 'slot_xxx',      // ID unique généré
    'start_date'  => '2025-01-15',
    'start_time'  => '10:00',
    'end_time'    => '12:00',
    'source'      => 'manual|recurrence', // Origine du créneau
];

// Billet (ticket) - NOUVEAU FORMAT
$ticket = [
    'ticket_id'    => 'tkt_xxx',
    'name_ticket'  => 'Adulte',
    'price_ticket' => 15,
    'qty_ticket'   => 100,
    'slots_mode'   => 'all|selected',  // NOUVEAU
    'slots'        => ['slot_xxx', 'slot_yyy'], // NOUVEAU
];

// Billetterie externe - NOUVEAU FORMAT SIMPLIFIÉ
$external = [
    'enabled'     => true,
    'url'         => 'https://billetweb.fr/...',
    'description' => 'Billets vendus via notre partenaire...',
];
Phase 1 : Core Backend (5 jours)
1.1 Fonctions Helper (el-core-functions.php)
Fichier : /wp-content/plugins/eventlist/includes/el-core-functions.php

// Nouvelles fonctions à créer :
function el_generate_slot_id() {}
function el_resolve_recurrence_to_slots($event_id) {}
function el_get_ticket_slots($event_id, $ticket_id) {}
function el_ticket_available_for_slot($event_id, $ticket_id, $slot_id) {}
function el_get_slot_by_id($event_id, $slot_id) {}
function el_get_tickets_for_slot($event_id, $slot_id) {}
1.2 Résolution Récurrence (NOUVEAU fichier)
Fichier : /wp-content/plugins/eventlist/includes/class-el-recurrence-resolver.php

class EL_Recurrence_Resolver {
    // Génère tous les créneaux réels à partir des paramètres de récurrence
    public static function resolve($event_id) {}

    // Appelé à la sauvegarde de l'événement
    public static function on_event_save($event_id, $recurrence_params) {}
}
Logique :
Récupère ts_start[], ts_end[], recurrence_type, recurrence_days, recurrence_end
Limite : 1 an maximum (évite explosion de données)
Génère un calendar_id unique pour chaque occurrence
Stocke dans ova_mb_event_calendar avec source = 'recurrence'
Si recurrence_end > 1 an, tronquer à 1 an avec warning
1.3 Modification Sauvegarde AJAX
Fichier : /wp-content/plugins/eventlist/includes/class-el-ajax.php Fonction : el_save_edit_event() (ligne 2157) Modifications :
Appeler EL_Recurrence_Resolver::resolve() après sauvegarde calendar
Sanitizer slots_mode et slots[] pour chaque ticket
Valider cohérence : si slots_mode = selected, au moins 1 slot requis
1.4 Modification Validation Booking
Fichier : /wp-content/plugins/eventlist/includes/booking/class-el-booking.php Fonctions à modifier :
validate_before_booking() : Vérifier ticket↔slot compatibilité
add_booking() : Stocker calendar_id dans booking meta
get_number_ticket_rest() : Filtrer par slot si applicable
Phase 2 : UI Vendor (4 jours)
2.1 Formulaire Billetterie
Fichier : /wp-content/themes/meup-child/eventlist/vendor/__edit-event-ticket.php Nouvelle UI par billet :

┌─ Billet: "Adulte" ─────────────────────────────────┐
│ Nom: [___________]  Prix: [___] €  Places: [___]   │
│                                                    │
│ ┌─ Disponible pour ──────────────────────────────┐ │
│ │ ● Tous les créneaux                            │ │
│ │ ○ Créneaux spécifiques :                       │ │
│ │   ☐ Lun 15/01 - 10:00 → 12:00                  │ │
│ │   ☐ Mer 17/01 - 14:00 → 16:00                  │ │
│ └────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────┘
Modifications :
Ligne 283-430 : Ajouter section "Créneaux associés" dans chaque carte
Récupérer créneaux depuis ova_mb_event_calendar (résolus)
Afficher checkboxes pour sélection multiple
2.2 Billetterie Externe Simplifiée
Même fichier : __edit-event-ticket.php Nouvelle UI :

┌─ Mode Billetterie ─────────────────────────────────┐
│ ○ Gérée ici                                        │
│ ● Billetterie externe                              │
│                                                    │
│   Lien : [https://billetweb.fr/...___________]     │
│   Explication : [____________________________]     │
│                 [____________________________]     │
└────────────────────────────────────────────────────┘
2.3 JavaScript Vendor
Fichier : Inline dans __edit-event-ticket.php (BilletterieManager) Modifications :
Gérer toggle slots_mode (all/selected)
Afficher/masquer checkboxes créneaux
Synchroniser avec données formulaire
Valider au moins 1 créneau si mode selected
Phase 3 : Frontend Public (3 jours)
3.1 Sélection Créneau (Calendrier Mini + Dropdown)
Fichier : /wp-content/plugins/eventlist/templates/single/ticket_calendar.php Nouvelle UI :

┌─────────────────────────────────────────────────────┐
│  1. Choisissez votre date :                        │
│  ┌───────────────────────────────┐                 │
│  │     Janvier 2025             │                 │
│  │  Lu Ma Me Je Ve Sa Di        │                 │
│  │     1  2  3  4  5           │                 │
│  │  6  7  8  9 10 11 12        │                 │
│  │ 13 14 [15] 16 17 18 19       │  ← dates dispo  │
│  │ 20 21 22 23 24 25 26        │    surlignées   │
│  └───────────────────────────────┘                 │
│                                                     │
│  2. Choisissez votre horaire :                     │
│  ┌─────────────────────────────────────────────┐   │
│  │ ▼ 10h00 → 12h00 (23 places)                 │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  Billets disponibles :                             │
│  ┌─────────────────────────────────────────────┐   │
│  │ Adulte                      45€   [−] 1 [+] │   │
│  │ Enfant                      25€   [−] 0 [+] │   │
│  └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
Composants :
Mini calendrier (type datepicker) avec dates disponibles surlignées
Dropdown horaires qui se met à jour selon la date sélectionnée
Section billets qui se met à jour selon le créneau sélectionné
3.2 Filtrage Billets par Créneau
Fichier : /wp-content/plugins/eventlist/templates/cart/ticket_type.php Logique :

$selected_slot = $_GET['idcal'] ?? '';
$tickets = get_post_meta($event_id, 'ova_mb_event_ticket', true);

// Filtrer les billets disponibles pour ce créneau
$filtered_tickets = array_filter($tickets, function($t) use ($event_id, $selected_slot) {
    return el_ticket_available_for_slot($event_id, $t['ticket_id'], $selected_slot);
});
3.3 AJAX Mise à Jour
Fichier : /wp-content/plugins/eventlist/includes/class-el-ajax.php Nouvelle fonction :

public static function el_get_tickets_for_slot() {
    $event_id = intval($_POST['event_id']);
    $slot_id = sanitize_text_field($_POST['slot_id']);

    $tickets = el_get_tickets_for_slot($event_id, $slot_id);
    wp_send_json_success($tickets);
}
Phase 4 : API Mobile (3 jours)
4.1 Nouveaux Endpoints
Fichier : /wp-content/plugins/lehiboo-mobile-api/includes/api/class-lma-rest-events.php

// GET /events/{id}/slots
// Retourne tous les créneaux de l'événement
public function get_event_slots($request) {}

// GET /events/{id}/slots/{slot_id}/tickets
// Retourne les billets disponibles pour un créneau
public function get_slot_tickets($request) {}
4.2 Modification Endpoints Existants
Fichier : class-lma-rest-events.php

// GET /events/{id}
// Ajouter dans la réponse :
'slots' => [
    ['id' => 'slot_xxx', 'date' => '2025-01-15', 'start' => '10:00', 'end' => '12:00'],
    ...
],
'tickets' => [
    ['id' => 'tkt_xxx', 'name' => 'Adulte', 'price' => 45, 'slots_mode' => 'all'],
    ...
]
Fichier : class-lma-rest-bookings.php

// POST /bookings
// Ajouter validation :
- Vérifier que le slot_id est valide
- Vérifier que les tickets sont disponibles pour ce slot
- Stocker slot_id dans booking meta
4.3 Format de Réponse

// GET /events/123/slots
{
  "success": true,
  "data": {
    "slots": [
      {
        "id": "slot_abc123",
        "date": "2025-01-15",
        "day_name": "Mercredi",
        "start_time": "10:00",
        "end_time": "12:00",
        "available_tickets": 45,
        "status": "available"
      }
    ]
  }
}

// GET /events/123/slots/slot_abc123/tickets
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": "tkt_001",
        "name": "Adulte",
        "price": 45.00,
        "currency": "EUR",
        "remaining": 23,
        "min_qty": 1,
        "max_qty": 10
      }
    ]
  }
}
Phase 5 : Documentation (1 jour)
5.1 OpenAPI Specification
Fichier : /wp-content/plugins/lehiboo-mobile-api/docs/openapi.json Ajouts :
Schéma Slot
Schéma TicketWithSlots
Endpoints /events/{id}/slots
Endpoints /events/{id}/slots/{slot_id}/tickets
5.2 Documentation Développeur
Fichier : /wp-content/plugins/eventlist/docs/TICKETING.md (NOUVEAU) Contenu :
Architecture billetterie
Structure de données
Flux de réservation
Hooks et filtres disponibles
Exemples de code
5.3 Guide Vendor
Fichier : Documentation inline dans l'interface vendor Contenu :
Tooltips sur les champs
Messages d'aide contextuels
Validation en temps réel
Fichiers Impactés - Résumé
Backend (PHP)
Fichier	Action	Priorité
class-el-recurrence-resolver.php	CRÉER	P1
el-core-functions.php	MODIFIER	P1
class-el-ajax.php	MODIFIER	P1
class-el-booking.php	MODIFIER	P1
class-el-event.php	MODIFIER	P2
Frontend Vendor
Fichier	Action	Priorité
__edit-event-ticket.php	MODIFIER	P1
vendor-event-form.css	MODIFIER	P2
Frontend Public
Fichier	Action	Priorité
ticket_calendar.php	MODIFIER/REMPLACER	P1
ticket_type.php	MODIFIER	P1
script.min.js (source)	MODIFIER	P2
API Mobile
Fichier	Action	Priorité
class-lma-rest-events.php	MODIFIER	P1
class-lma-rest-bookings.php	MODIFIER	P1
openapi.json	MODIFIER	P2
Documentation
Fichier	Action	Priorité
TICKETING.md	CRÉER	P3
openapi.json	MODIFIER	P2
Planning Estimé
Phase	Durée	Dépendances
Phase 1 : Core Backend	5 jours	-
Phase 2 : UI Vendor	4 jours	Phase 1
Phase 3 : Frontend Public	3 jours	Phase 1
Phase 4 : API Mobile	3 jours	Phase 1
Phase 5 : Documentation	1 jour	Phases 1-4
Total	16 jours	
Ordre d'Implémentation Recommandé
Semaine 1 : Phase 1 (Backend Core)
Jour 1-2 : Fonctions helper + Recurrence Resolver
Jour 3-4 : Modification AJAX save
Jour 5 : Modification booking validation
Semaine 2 : Phase 2 + 3 (UI)
Jour 1-2 : UI Vendor (formulaire billetterie)
Jour 3 : UI Vendor (billetterie externe)
Jour 4-5 : Frontend Public (sélection créneau)
Semaine 3 : Phase 4 + 5 (API + Docs)
Jour 1-2 : API Mobile endpoints
Jour 3 : API Mobile modifications booking
Jour 4 : Documentation OpenAPI
Jour 5 : Documentation développeur + QA
Décisions Validées
Décision	Choix	Justification
Limite récurrence	1 an max	Équilibre couverture/performance
UI créneaux public	Calendrier mini + Dropdown	Visuel pour la date, compact pour l'heure
Ordre implémentation	Backend d'abord	Base solide avant UI
Billetterie externe	Lien + description seulement	Pas de gestion si externe
Sièges/places	Reporté Phase 3	Stabiliser billetterie d'abord
Points de Vigilance
Récurrence : Limiter la génération à 1 an max, warning si dépassement
Performance : Indexer calendar_id pour requêtes rapides
Validation : Double-vérifier côté client ET serveur
Calendrier mini : Utiliser Flatpickr (léger) ou le datepicker existant
Tests à Prévoir
Unitaires
el_resolve_recurrence_to_slots() : Différents patterns de récurrence
el_ticket_available_for_slot() : Tous les cas edge
el_generate_slot_id() : Unicité garantie
Intégration
Création événement avec récurrence → vérifier slots générés
Réservation avec mapping → vérifier filtrage billets
API Mobile → vérifier réponses JSON
E2E
Parcours complet vendor : création → publication
Parcours complet client : recherche → réservation → confirmation
App mobile : même parcours via API
User approved the plan



Phase 1.1 : Créer les fonctions helper dans el-core-functions.php

Phase 1.2 : Créer class-el-recurrence-resolver.php

Phase 1.3 : Modifier el_save_edit_event() dans class-el-ajax.php

Phase 1.4 : Modifier validation booking dans class-el-booking.php

Phase 2.1 : Ajouter UI mapping créneaux dans __edit-event-ticket.php

Phase 2.2 : Simplifier billetterie externe

Phase 2.3 : Modifier JavaScript BilletterieManager

Phase 3.1 : Créer UI calendrier mini + dropdown

Phase 3.2 : Implémenter filtrage billets par créneau

Phase 4.1 : Ajouter endpoints API slots

Phase 4.2 : Modifier endpoints bookings pour slots

Phase 5 : Documentation OpenAPI + TICKETING.md

