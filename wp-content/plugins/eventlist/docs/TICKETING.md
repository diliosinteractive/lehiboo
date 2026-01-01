# Billetterie LeHiboo - Documentation Développeur

## Vue d'Ensemble

Le système de billetterie LeHiboo permet de créer des événements avec:
- **Créneaux (slots)**: Dates et horaires spécifiques
- **Billets (tickets)**: Types de billets avec prix et quantités
- **Mapping ticket ↔ slot**: Association entre billets et créneaux disponibles

## Structure de Données

### Créneau (Calendar Slot)

```php
$slot = [
    'calendar_id' => 'slot_xxx',      // ID unique généré
    'date'        => '2025-01-15',    // Date du créneau
    'start_time'  => '10:00',         // Heure de début
    'end_time'    => '12:00',         // Heure de fin
    'end_date'    => '2025-01-15',    // Date de fin (peut être différente)
    'source'      => 'manual',        // 'manual' ou 'recurrence'
    'book_before_minutes' => 60,      // Minutes avant fermeture réservation
];
```

### Billet (Ticket)

```php
$ticket = [
    'ticket_id'    => 'tkt_xxx',       // ID unique généré
    'name_ticket'  => 'Adulte',        // Nom affiché
    'price_ticket' => 15.00,           // Prix en EUR
    'qty_ticket'   => 100,             // Quantité totale
    'desc_ticket'  => 'Description',   // Description (optionnel)
    'min_per_ticket' => 1,             // Minimum par commande
    'max_per_ticket' => 10,            // Maximum par commande
    'slots_mode'   => 'all',           // 'all' = tous créneaux, 'selected' = créneaux spécifiques
    'slots'        => ['slot_xxx'],    // IDs des créneaux (si slots_mode = 'selected')
];
```

### Billetterie Externe

```php
// Meta de l'événement
'ticket_link' => 'ticket_external_link',
'ticket_external_link' => 'https://billetweb.fr/mon-event',
'ticket_external_description' => 'Billets vendus via notre partenaire...',
```

## Fonctions Helper

Fichier: `includes/el-core-functions.php`

### el_generate_slot_id()
Génère un ID unique pour un créneau.
```php
$slot_id = el_generate_slot_id();
// Retourne: "slot_abc123def456"
```

### el_generate_ticket_id()
Génère un ID unique pour un billet.
```php
$ticket_id = el_generate_ticket_id();
// Retourne: "tkt_abc123def456"
```

### el_ticket_available_for_slot($event_id, $ticket_id, $slot_id)
Vérifie si un billet est disponible pour un créneau.
```php
$available = el_ticket_available_for_slot(123, 'tkt_001', 'slot_abc');
// Retourne: true/false
```

### el_get_tickets_for_slot($event_id, $slot_id)
Récupère tous les billets disponibles pour un créneau.
```php
$tickets = el_get_tickets_for_slot(123, 'slot_abc');
// Retourne: array de billets
```

### el_get_slot_by_id($event_id, $slot_id)
Récupère les informations d'un créneau par son ID.
```php
$slot = el_get_slot_by_id(123, 'slot_abc');
// Retourne: array ou null
```

### el_resolve_recurrence_to_slots($event_id, $params)
Génère les créneaux à partir des paramètres de récurrence.
```php
$slots = el_resolve_recurrence_to_slots(123, [
    'recurrence_type' => 'weekly',
    'recurrence_days' => ['monday', 'wednesday'],
    'recurrence_start' => '2025-01-01',
    'recurrence_end' => '2025-06-30',
    'ts_start' => ['10:00'],
    'ts_end' => ['12:00'],
]);
```

## API REST

### GET /lehiboo/v2/events/{id}/slots
Récupère tous les créneaux d'un événement.

**Paramètres:**
- `id` (path, required): ID de l'événement
- `date` (query, optional): Filtrer par date

**Réponse:**
```json
{
  "success": true,
  "data": {
    "event_id": 123,
    "slots": [
      {
        "id": "slot_abc123",
        "date": "2025-01-15",
        "day_name": "Mercredi",
        "start_time": "10:00",
        "end_time": "12:00",
        "available_tickets": 45,
        "tickets_count": 2,
        "status": "available"
      }
    ],
    "available_dates": ["2025-01-15", "2025-01-17", "2025-01-22"]
  }
}
```

### GET /lehiboo/v2/events/{id}/slots/{slot_id}/tickets
Récupère les billets disponibles pour un créneau.

**Paramètres:**
- `id` (path, required): ID de l'événement
- `slot_id` (path, required): ID du créneau

**Réponse:**
```json
{
  "success": true,
  "data": {
    "event_id": 123,
    "slot_id": "slot_abc123",
    "slot": {
      "date": "2025-01-15",
      "start_time": "10:00",
      "end_time": "12:00"
    },
    "tickets": [
      {
        "id": "tkt_001",
        "name": "Adulte",
        "description": "",
        "price": 15.00,
        "price_formatted": "15,00 €",
        "currency": "EUR",
        "remaining": 23,
        "min_qty": 1,
        "max_qty": 10
      }
    ]
  }
}
```

### POST /lehiboo/v2/bookings
Créer une réservation avec slot_id.

**Corps de la requête:**
```json
{
  "event_id": 123,
  "slot_id": "slot_abc123",
  "tickets": [
    {"ticket_type_id": "tkt_001", "quantity": 2}
  ],
  "buyer_info": {
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean@example.com"
  }
}
```

## Actions AJAX (WordPress)

### el_get_tickets_for_slot
Récupère les billets pour un créneau (appelé depuis le frontend public).

```javascript
$.ajax({
    url: el_ajax.url,
    type: 'POST',
    data: {
        action: 'el_get_tickets_for_slot',
        event_id: 123,
        slot_id: 'slot_abc123'
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data.tickets);
        }
    }
});
```

## Hooks et Filtres

### Actions

```php
// Après génération des créneaux récurrents
do_action('el_after_resolve_recurrence', $event_id, $generated_slots);

// Après sauvegarde d'un événement avec billetterie
do_action('el_after_save_ticketing', $event_id, $tickets, $calendar);
```

### Filtres

```php
// Modifier les billets disponibles pour un créneau
$tickets = apply_filters('el_tickets_for_slot', $tickets, $event_id, $slot_id);

// Modifier la limite de récurrence (défaut: 1 an)
$limit = apply_filters('el_recurrence_max_days', 365);
```

## Frontend Vendor

Le formulaire de création d'événement inclut maintenant une section de mapping créneau ↔ billet.

**Fichier:** `themes/meup-child/eventlist/vendor/__edit-event-ticket.php`

Chaque billet affiche:
- Radio "Tous les créneaux" / "Créneaux spécifiques"
- Checkboxes pour sélection des créneaux (si mode "spécifiques")

## Frontend Public

La page de réservation utilise un calendrier mini avec dropdown.

**Fichier:** `themes/meup-child/eventlist/single/ticket_calendar.php`

Flux utilisateur:
1. Sélection de la date (calendrier mini)
2. Sélection de l'horaire (dropdown)
3. Sélection des billets (chargés via AJAX selon le créneau)
4. Redirection vers le panier

## Migration

Pour les événements existants sans `slots_mode`:
- Par défaut, tous les billets sont disponibles pour tous les créneaux
- Le champ `slots_mode` vaut `'all'` par défaut

Pour ajouter le mapping aux anciens billets:
```php
$tickets = get_post_meta($event_id, 'ova_mb_event_ticket', true);
foreach ($tickets as &$ticket) {
    if (!isset($ticket['slots_mode'])) {
        $ticket['slots_mode'] = 'all';
        $ticket['slots'] = [];
    }
}
update_post_meta($event_id, 'ova_mb_event_ticket', $tickets);
```

## Validation

### Côté serveur

La validation est effectuée dans:
- `class-el-ajax.php` : `el_get_tickets_for_slot()`
- `class-lma-rest-bookings.php` : `create_booking()`

Vérifications:
1. Le slot existe
2. Le billet est disponible pour ce slot (`el_ticket_available_for_slot`)
3. Il reste des places (`get_number_ticket_rest`)

### Côté client

Le JavaScript vérifie:
1. Au moins un billet sélectionné
2. Quantité dans les limites min/max
3. Slot sélectionné avant affichage des billets
