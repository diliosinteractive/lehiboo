# 📱 PROMPT API MOBILE LEHIBOO V2
# Plugin WordPress pour exposer les données à l'application mobile Flutter

---

## 🦉 1. CONTEXTE & ARCHITECTURE EXISTANTE

**Le Hiboo** est une plateforme hyper-locale permettant aux utilisateurs de découvrir, filtrer et réserver des activités locales.

### Architecture existante à respecter :

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ARCHITECTURE LEHIBOO                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐   ┌──────────────────┐   ┌─────────────────────────────┐  │
│  │ App Mobile   │──▶│ lehiboo-mobile-  │──▶│ Plugin EventList            │  │
│  │ Flutter      │   │ api (NOUVEAU)    │   │ (existant)                  │  │
│  │              │   │                  │   │                             │  │
│  │ • Liste      │   │ /lehiboo/v2/     │   │ • CPT: event, el_bookings   │  │
│  │ • Détail     │   │ • /events        │   │ • CPT: el_tickets, venue    │  │
│  │ • Réserver   │   │ • /bookings      │   │ • Gateways paiement         │  │
│  │ • Auth       │   │ • /auth          │   │ • Génération tickets/QR     │  │
│  └──────────────┘   └──────────────────┘   └─────────────────────────────┘  │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │ Plugins existants à NE PAS modifier :                                │   │
│  │ • eventlist/ (CPT, bookings, tickets, paiements)                     │   │
│  │ • lehiboo-ai-assistant/ (chat IA, namespace /lehiboo/v1/)            │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### CPT & Taxonomies EXISTANTS (à réutiliser) :

| CPT Existant | Usage | Plugin source |
|--------------|-------|---------------|
| `event` | Activités/Événements | EventList |
| `el_bookings` | Réservations | EventList |
| `el_tickets` | Tickets avec QR | EventList |
| `venue` | Lieux | EventList |

| Taxonomie Existante | Usage |
|---------------------|-------|
| `event_cat` | Catégories d'événements |
| `event_tag` | Tags libres |
| `event_thematique` | Thématiques LeHiboo |
| `event_public` | Public cible |
| `event_saison` | Saisons |

### Meta fields existants sur `event` :

```php
// Dates & Horaires
'event_start_date', 'event_end_date'
'event_start_time', 'event_end_time'

// Lieu
'event_location', 'event_venue'
'event_lat', 'event_lng'

// Prix
'event_price', 'event_price_min', 'event_price_max'

// Restrictions
'event_age_restriction', 'event_min_age', 'event_max_age'
'event_family_friendly'

// Environnement
'event_indoor', 'event_outdoor'

// Capacité
'event_availability', 'event_spots_remaining'

// Avis
'event_rating', 'event_reviews_count'
```

---

## 🎯 2. OBJECTIFS DU NOUVEAU PLUGIN

Créer un plugin **`lehiboo-mobile-api`** qui :

### ✅ DOIT :
- Exposer une API REST sécurisée sous `/wp-json/lehiboo/v2/`
- Permettre l'authentification JWT pour l'app mobile
- Permettre l'inscription et la connexion des clients
- Lister et filtrer les événements (events)
- Permettre la réservation via les classes existantes d'EventList
- Retourner les tickets de l'utilisateur connecté
- Être 100% compatible avec le système existant

### ❌ NE DOIT PAS :
- Créer de nouveaux CPT (utiliser `event`, `el_bookings`, `el_tickets`)
- Dupliquer la logique de réservation/paiement (utiliser EL_Booking)
- Modifier les plugins existants
- Exposer les fonctionnalités partenaires (phase 2)

---

## 🧱 3. STRUCTURE DU PLUGIN

```
wp-content/plugins/lehiboo-mobile-api/
├── lehiboo-mobile-api.php              # Point d'entrée
├── includes/
│   ├── class-lma-loader.php            # Autoloader
│   ├── class-lma-activator.php         # Activation hooks
│   ├── class-lma-deactivator.php       # Deactivation hooks
│   │
│   ├── auth/
│   │   ├── class-lma-jwt-handler.php   # Gestion JWT (réutilise Firebase JWT)
│   │   ├── class-lma-auth-endpoints.php # Endpoints auth
│   │   └── class-lma-password-reset.php # Reset mot de passe
│   │
│   ├── api/
│   │   ├── class-lma-rest-events.php   # GET /events, GET /events/{id}
│   │   ├── class-lma-rest-bookings.php # POST /bookings, GET /me/bookings
│   │   ├── class-lma-rest-tickets.php  # GET /me/tickets
│   │   ├── class-lma-rest-user.php     # GET/PUT /me
│   │   ├── class-lma-rest-favorites.php # Favoris (bridge avec lehiboo-ai-assistant)
│   │   └── class-lma-rest-categories.php # GET /categories
│   │
│   ├── helpers/
│   │   ├── class-lma-event-formatter.php  # Formatte les events pour l'API
│   │   ├── class-lma-booking-bridge.php   # Bridge vers EL_Booking
│   │   ├── class-lma-ticket-bridge.php    # Bridge vers EL_Ticket
│   │   └── class-lma-validator.php        # Validation des inputs
│   │
│   └── security/
│       ├── class-lma-rate-limiter.php     # Rate limiting API
│       └── class-lma-security.php         # Sanitization, validation
│
└── readme.txt
```

---

## 🔐 4. AUTHENTIFICATION JWT

### 4.1 Réutiliser le système JWT existant

EventList utilise déjà Firebase JWT dans `/eventlist/includes/api/class-el-api.php`.

**Clé secrète existante** : `get_option('serect_key_qrcode')`

### 4.2 Endpoints Auth

#### `POST /wp-json/lehiboo/v2/auth/register`

Inscription d'un nouveau client.

**Request :**
```json
{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "first_name": "Jean",
  "last_name": "Dupont",
  "phone": "+33612345678"
}
```

**Response (201) :**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "display_name": "Jean Dupont",
    "role": "subscriber"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 604800
}
```

**Validations :**
- Email unique et format valide
- Password : min 8 caractères, 1 majuscule, 1 chiffre
- Phone : format E.164 (optionnel)

---

#### `POST /wp-json/lehiboo/v2/auth/login`

Connexion d'un utilisateur existant.

**Request :**
```json
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}
```

**Response (200) :**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "display_name": "Jean Dupont",
    "avatar_url": "https://...",
    "role": "subscriber"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 604800
}
```

---

#### `POST /wp-json/lehiboo/v2/auth/refresh`

Rafraîchir le token JWT.

**Request :**
```json
{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response (200) :**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 604800
}
```

---

#### `POST /wp-json/lehiboo/v2/auth/forgot-password`

Demande de réinitialisation du mot de passe.

**Request :**
```json
{
  "email": "user@example.com"
}
```

**Response (200) :**
```json
{
  "success": true,
  "message": "Un email de réinitialisation a été envoyé."
}
```

---

#### `POST /wp-json/lehiboo/v2/auth/logout`

Déconnexion (invalidation du token côté client).

**Headers :** `Authorization: Bearer {token}`

**Response (200) :**
```json
{
  "success": true,
  "message": "Déconnexion réussie."
}
```

---

## 🎫 5. ENDPOINTS ÉVÉNEMENTS

### 5.1 `GET /wp-json/lehiboo/v2/events`

Liste des événements avec filtres avancés.

**Query Parameters :**

| Param | Type | Description | Exemple |
|-------|------|-------------|---------|
| `page` | int | Page (défaut: 1) | `1` |
| `per_page` | int | Items par page (défaut: 20, max: 100) | `20` |
| `category` | string/int | Slug ou ID catégorie | `famille` |
| `thematique` | string | Slug thématique | `nature` |
| `city` | string | Ville | `Valence` |
| `lat` | float | Latitude (avec lng + radius) | `44.9333` |
| `lng` | float | Longitude | `4.8917` |
| `radius` | int | Rayon en km (défaut: 20) | `30` |
| `date_from` | string | Date début (YYYY-MM-DD) | `2025-01-15` |
| `date_to` | string | Date fin | `2025-01-31` |
| `price_min` | float | Prix minimum | `0` |
| `price_max` | float | Prix maximum | `50` |
| `free_only` | bool | Uniquement gratuits | `true` |
| `indoor` | bool | Activités indoor | `true` |
| `outdoor` | bool | Activités outdoor | `true` |
| `family_friendly` | bool | Adapté familles | `true` |
| `age_min` | int | Âge minimum | `6` |
| `age_max` | int | Âge maximum | `12` |
| `search` | string | Recherche textuelle | `poterie` |
| `orderby` | string | Tri: `date`, `price`, `rating`, `distance` | `date` |
| `order` | string | `asc` ou `desc` | `asc` |

**Response (200) :**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "title": "Atelier poterie enfants",
      "slug": "atelier-poterie-enfants",
      "excerpt": "Découvrez la poterie en famille",
      "description": "<p>Description complète...</p>",
      "featured_image": {
        "thumbnail": "https://...",
        "medium": "https://...",
        "large": "https://...",
        "full": "https://..."
      },
      "gallery": [
        "https://...",
        "https://..."
      ],
      "category": {
        "id": 12,
        "name": "Famille",
        "slug": "famille",
        "icon": "👨‍👩‍👧"
      },
      "thematiques": [
        {
          "id": 5,
          "name": "Créatif",
          "slug": "creatif"
        }
      ],
      "tags": ["poterie", "enfants", "manuel"],
      "dates": {
        "start": "2025-01-20",
        "end": "2025-01-20",
        "start_time": "14:00",
        "end_time": "16:30",
        "formatted": "Samedi 20 janvier 2025, 14h-16h30",
        "is_recurring": false,
        "occurrences": []
      },
      "location": {
        "venue_name": "MJC de Valence",
        "address": "20 rue des Ormes, 26000 Valence",
        "city": "Valence",
        "postal_code": "26000",
        "lat": 44.9333,
        "lng": 4.8917,
        "distance_km": 2.5
      },
      "pricing": {
        "is_free": false,
        "price_min": 15.00,
        "price_max": 25.00,
        "currency": "EUR",
        "price_display": "15€ - 25€",
        "tickets_types": [
          {
            "id": 1,
            "name": "Enfant (4-12 ans)",
            "price": 15.00
          },
          {
            "id": 2,
            "name": "Adulte accompagnant",
            "price": 25.00
          }
        ]
      },
      "availability": {
        "status": "available",
        "total_capacity": 20,
        "spots_remaining": 8,
        "percentage_filled": 60
      },
      "restrictions": {
        "age_min": 4,
        "age_max": 12,
        "family_friendly": true,
        "age_display": "4-12 ans"
      },
      "environment": {
        "indoor": true,
        "outdoor": false,
        "type": "indoor"
      },
      "ratings": {
        "average": 4.8,
        "count": 127,
        "distribution": {
          "5": 98,
          "4": 22,
          "3": 5,
          "2": 1,
          "1": 1
        }
      },
      "organizer": {
        "id": 789,
        "name": "MJC de Valence",
        "logo": "https://...",
        "verified": true
      },
      "booking": {
        "mode": "online",
        "url": null,
        "phone": null,
        "email": null
      },
      "meta": {
        "duration_minutes": 150,
        "difficulty": "facile",
        "equipment_provided": true
      },
      "created_at": "2025-01-01T10:00:00Z",
      "updated_at": "2025-01-10T15:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total_items": 156,
    "total_pages": 8,
    "has_next": true,
    "has_prev": false
  },
  "filters_applied": {
    "city": "Valence",
    "category": "famille",
    "date_from": "2025-01-15"
  }
}
```

---

### 5.2 `GET /wp-json/lehiboo/v2/events/{id}`

Détail complet d'un événement.

**Response (200) :** Même structure que ci-dessus avec champs additionnels :

```json
{
  "success": true,
  "data": {
    // ... tous les champs de la liste +
    "full_description": "<p>Description HTML complète...</p>",
    "similar_events": [
      { "id": 457, "title": "...", "thumbnail": "..." },
      { "id": 458, "title": "...", "thumbnail": "..." }
    ],
    "reviews": [
      {
        "id": 1,
        "author": "Marie D.",
        "rating": 5,
        "comment": "Super atelier !",
        "date": "2025-01-05"
      }
    ],
    "organizer_contact": {
      "phone": "+33475123456",
      "email": "contact@mjc-valence.fr",
      "website": "https://mjc-valence.fr"
    }
  }
}
```

---

## 🎟️ 6. ENDPOINTS RÉSERVATIONS

### 6.1 `POST /wp-json/lehiboo/v2/bookings`

Créer une nouvelle réservation.

**Headers :** `Authorization: Bearer {token}` (OBLIGATOIRE)

**Request :**
```json
{
  "event_id": 456,
  "tickets": [
    {
      "ticket_type_id": 1,
      "quantity": 2,
      "attendees": [
        {
          "first_name": "Lucas",
          "last_name": "Dupont",
          "age": 8
        },
        {
          "first_name": "Emma",
          "last_name": "Dupont",
          "age": 6
        }
      ]
    },
    {
      "ticket_type_id": 2,
      "quantity": 1,
      "attendees": [
        {
          "first_name": "Jean",
          "last_name": "Dupont"
        }
      ]
    }
  ],
  "buyer_info": {
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@email.com",
    "phone": "+33612345678"
  },
  "payment_method": "stripe",
  "coupon_code": "PROMO10",
  "notes": "Allergies : gluten"
}
```

**Response (201) - Réservation créée, en attente de paiement :**
```json
{
  "success": true,
  "data": {
    "booking_id": 1234,
    "status": "pending_payment",
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants",
      "date": "2025-01-20",
      "time": "14:00 - 16:30"
    },
    "tickets_summary": [
      {
        "type": "Enfant (4-12 ans)",
        "quantity": 2,
        "unit_price": 15.00,
        "subtotal": 30.00
      },
      {
        "type": "Adulte accompagnant",
        "quantity": 1,
        "unit_price": 25.00,
        "subtotal": 25.00
      }
    ],
    "pricing": {
      "subtotal": 55.00,
      "discount": 5.50,
      "coupon_applied": "PROMO10",
      "total": 49.50,
      "currency": "EUR"
    },
    "payment": {
      "method": "stripe",
      "client_secret": "pi_xxx_secret_xxx",
      "publishable_key": "pk_live_xxx"
    },
    "expires_at": "2025-01-15T12:30:00Z"
  }
}
```

---

### 6.2 `POST /wp-json/lehiboo/v2/bookings/{id}/confirm`

Confirmer une réservation après paiement réussi.

**Headers :** `Authorization: Bearer {token}`

**Request :**
```json
{
  "payment_intent_id": "pi_xxx",
  "payment_method_id": "pm_xxx"
}
```

**Response (200) :**
```json
{
  "success": true,
  "data": {
    "booking_id": 1234,
    "status": "confirmed",
    "confirmation_number": "LH-2025-001234",
    "tickets": [
      {
        "ticket_id": 5678,
        "ticket_code": "LH-T-5678-ABCD",
        "qr_code_url": "https://api.lehiboo.com/qr/LH-T-5678-ABCD",
        "attendee": "Lucas Dupont",
        "type": "Enfant (4-12 ans)"
      },
      {
        "ticket_id": 5679,
        "ticket_code": "LH-T-5679-EFGH",
        "qr_code_url": "https://api.lehiboo.com/qr/LH-T-5679-EFGH",
        "attendee": "Emma Dupont",
        "type": "Enfant (4-12 ans)"
      },
      {
        "ticket_id": 5680,
        "ticket_code": "LH-T-5680-IJKL",
        "qr_code_url": "https://api.lehiboo.com/qr/LH-T-5680-IJKL",
        "attendee": "Jean Dupont",
        "type": "Adulte accompagnant"
      }
    ],
    "pdf_url": "https://api.lehiboo.com/bookings/1234/tickets.pdf",
    "calendar_links": {
      "google": "https://calendar.google.com/...",
      "ical": "https://api.lehiboo.com/bookings/1234/calendar.ics"
    }
  }
}
```

---

### 6.3 `GET /wp-json/lehiboo/v2/me/bookings`

Liste des réservations de l'utilisateur connecté.

**Headers :** `Authorization: Bearer {token}`

**Query Parameters :**
- `status` : `all`, `upcoming`, `past`, `cancelled` (défaut: `all`)
- `page`, `per_page`

**Response (200) :**
```json
{
  "success": true,
  "data": [
    {
      "booking_id": 1234,
      "confirmation_number": "LH-2025-001234",
      "status": "confirmed",
      "event": {
        "id": 456,
        "title": "Atelier poterie enfants",
        "thumbnail": "https://...",
        "date": "2025-01-20",
        "time": "14:00 - 16:30",
        "location": "MJC de Valence"
      },
      "tickets_count": 3,
      "total_paid": 49.50,
      "currency": "EUR",
      "booked_at": "2025-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total_items": 5,
    "total_pages": 1
  }
}
```

---

### 6.4 `POST /wp-json/lehiboo/v2/bookings/{id}/cancel`

Annuler une réservation.

**Headers :** `Authorization: Bearer {token}`

**Request :**
```json
{
  "reason": "Empêchement personnel"
}
```

**Response (200) :**
```json
{
  "success": true,
  "data": {
    "booking_id": 1234,
    "status": "cancelled",
    "refund": {
      "eligible": true,
      "amount": 49.50,
      "status": "processing",
      "estimated_date": "2025-01-18"
    }
  }
}
```

---

## 🎫 7. ENDPOINTS TICKETS

### 7.1 `GET /wp-json/lehiboo/v2/me/tickets`

Liste des tickets de l'utilisateur.

**Headers :** `Authorization: Bearer {token}`

**Query Parameters :**
- `status` : `all`, `valid`, `used`, `expired` (défaut: `all`)
- `event_id` : Filtrer par événement

**Response (200) :**
```json
{
  "success": true,
  "data": [
    {
      "ticket_id": 5678,
      "ticket_code": "LH-T-5678-ABCD",
      "status": "valid",
      "qr_code_data": "LH-T-5678-ABCD-SIGNATURE",
      "qr_code_url": "https://api.lehiboo.com/qr/LH-T-5678-ABCD",
      "attendee": {
        "first_name": "Lucas",
        "last_name": "Dupont",
        "age": 8
      },
      "ticket_type": "Enfant (4-12 ans)",
      "event": {
        "id": 456,
        "title": "Atelier poterie enfants",
        "date": "2025-01-20",
        "time": "14:00",
        "location": {
          "name": "MJC de Valence",
          "address": "20 rue des Ormes, 26000 Valence",
          "lat": 44.9333,
          "lng": 4.8917
        }
      },
      "booking": {
        "id": 1234,
        "confirmation_number": "LH-2025-001234"
      },
      "validity": {
        "valid_from": "2025-01-20T13:00:00Z",
        "valid_until": "2025-01-20T17:00:00Z"
      },
      "pdf_url": "https://api.lehiboo.com/tickets/5678/download.pdf"
    }
  ],
  "meta": {
    "current_page": 1,
    "total_items": 3
  }
}
```

---

### 7.2 `GET /wp-json/lehiboo/v2/me/tickets/{id}`

Détail d'un ticket spécifique.

**Headers :** `Authorization: Bearer {token}`

**Response (200) :** Même structure avec tous les détails.

---

## 👤 8. ENDPOINTS UTILISATEUR

### 8.1 `GET /wp-json/lehiboo/v2/me`

Profil de l'utilisateur connecté.

**Headers :** `Authorization: Bearer {token}`

**Response (200) :**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "email": "jean.dupont@email.com",
    "first_name": "Jean",
    "last_name": "Dupont",
    "display_name": "Jean Dupont",
    "phone": "+33612345678",
    "avatar_url": "https://...",
    "role": "subscriber",
    "preferences": {
      "notifications_email": true,
      "notifications_push": true,
      "newsletter": false,
      "language": "fr"
    },
    "stats": {
      "bookings_count": 5,
      "upcoming_events": 2,
      "favorites_count": 12
    },
    "created_at": "2024-06-15T10:00:00Z"
  }
}
```

---

### 8.2 `PUT /wp-json/lehiboo/v2/me`

Mettre à jour le profil.

**Headers :** `Authorization: Bearer {token}`

**Request :**
```json
{
  "first_name": "Jean",
  "last_name": "Dupont",
  "phone": "+33698765432",
  "preferences": {
    "notifications_push": false
  }
}
```

---

### 8.3 `PUT /wp-json/lehiboo/v2/me/password`

Changer le mot de passe.

**Headers :** `Authorization: Bearer {token}`

**Request :**
```json
{
  "current_password": "OldPass123!",
  "new_password": "NewSecurePass456!"
}
```

---

## ❤️ 9. ENDPOINTS FAVORIS

Bridge avec la table `wp_lehiboo_user_favorites` existante.

### 9.1 `GET /wp-json/lehiboo/v2/me/favorites`

**Headers :** `Authorization: Bearer {token}`

**Response (200) :**
```json
{
  "success": true,
  "data": [
    {
      "event_id": 456,
      "event": {
        "id": 456,
        "title": "Atelier poterie enfants",
        "thumbnail": "https://...",
        "date": "2025-01-20",
        "price_display": "15€ - 25€",
        "location": "Valence"
      },
      "added_at": "2025-01-10T15:00:00Z"
    }
  ],
  "meta": {
    "total_items": 12
  }
}
```

### 9.2 `POST /wp-json/lehiboo/v2/me/favorites`

**Request :**
```json
{
  "event_id": 789
}
```

### 9.3 `DELETE /wp-json/lehiboo/v2/me/favorites/{event_id}`

---

## 📂 10. ENDPOINTS CATÉGORIES

### 10.1 `GET /wp-json/lehiboo/v2/categories`

**Response (200) :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Sport",
      "slug": "sport",
      "description": "Activités sportives",
      "icon": "⚽",
      "image": "https://...",
      "events_count": 45,
      "children": []
    },
    {
      "id": 2,
      "name": "Famille",
      "slug": "famille",
      "icon": "👨‍👩‍👧",
      "events_count": 78,
      "children": [
        {
          "id": 21,
          "name": "0-3 ans",
          "slug": "0-3-ans",
          "events_count": 12
        }
      ]
    }
  ]
}
```

### 10.2 `GET /wp-json/lehiboo/v2/thematiques`

Même structure pour les thématiques LeHiboo.

---

## 🔒 11. SÉCURITÉ

### 11.1 Rate Limiting

| Endpoint | Limite | Fenêtre |
|----------|--------|---------|
| `/auth/register` | 5 | 1 heure |
| `/auth/login` | 10 | 15 min |
| `/auth/forgot-password` | 3 | 1 heure |
| `/events` (GET) | 100 | 1 min |
| `/bookings` (POST) | 10 | 1 min |
| Autres | 60 | 1 min |

### 11.2 Validation des entrées

- Sanitization de toutes les entrées (SQL injection, XSS)
- Validation des types (email, phone, dates)
- Vérification des permissions (user ne peut voir que SES bookings/tickets)

### 11.3 Headers de sécurité

```php
// Tous les endpoints
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS pour l'app mobile
header('Access-Control-Allow-Origin: *'); // ou domaines spécifiques
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
```

---

## 🔗 12. INTÉGRATION AVEC EVENTLIST

### 12.1 Bridge Booking

```php
// Dans class-lma-booking-bridge.php
class LMA_Booking_Bridge {

    /**
     * Créer une réservation via le système EventList existant
     */
    public static function create_booking($event_id, $tickets_data, $buyer_info, $user_id) {
        // Utiliser la classe EL_Booking existante
        if (class_exists('EL_Booking')) {
            $booking = new EL_Booking();

            // Préparer les données au format EventList
            $booking_data = self::format_for_eventlist($event_id, $tickets_data, $buyer_info);

            // Créer la réservation
            $booking_id = $booking->create_booking($booking_data);

            return $booking_id;
        }

        return new WP_Error('eventlist_missing', 'EventList plugin required');
    }
}
```

### 12.2 Bridge Ticket

```php
// Dans class-lma-ticket-bridge.php
class LMA_Ticket_Bridge {

    /**
     * Récupérer les tickets d'un utilisateur
     */
    public static function get_user_tickets($user_id, $args = []) {
        global $wpdb;

        // Query sur le CPT el_tickets
        $query_args = [
            'post_type' => 'el_tickets',
            'meta_query' => [
                [
                    'key' => 'ticket_user_id',
                    'value' => $user_id
                ]
            ]
        ];

        // ... filtres additionnels

        return get_posts($query_args);
    }
}
```

---

## 📱 13. CODES D'ERREUR

| Code | HTTP | Message |
|------|------|---------|
| `auth_invalid_credentials` | 401 | Email ou mot de passe incorrect |
| `auth_token_expired` | 401 | Token expiré, veuillez vous reconnecter |
| `auth_token_invalid` | 401 | Token invalide |
| `auth_email_exists` | 400 | Cet email est déjà utilisé |
| `validation_error` | 400 | Données invalides |
| `event_not_found` | 404 | Événement non trouvé |
| `event_sold_out` | 400 | Événement complet |
| `booking_not_found` | 404 | Réservation non trouvée |
| `booking_already_cancelled` | 400 | Réservation déjà annulée |
| `payment_failed` | 400 | Échec du paiement |
| `rate_limit_exceeded` | 429 | Trop de requêtes |
| `server_error` | 500 | Erreur serveur |

**Format d'erreur standard :**
```json
{
  "success": false,
  "error": {
    "code": "auth_invalid_credentials",
    "message": "Email ou mot de passe incorrect",
    "details": null
  }
}
```

---

## 🚀 14. PRIORITÉS D'IMPLÉMENTATION

### Phase 1 - MVP (Semaine 1-2)
1. ✅ Structure du plugin
2. ✅ Auth : register, login, JWT
3. ✅ Events : liste avec filtres basiques, détail
4. ✅ Sécurité : rate limiting, validation

### Phase 2 - Réservation (Semaine 3-4)
5. ✅ Bookings : création, confirmation
6. ✅ Bridge avec EL_Booking
7. ✅ Intégration Stripe
8. ✅ Tickets : liste, détail, QR

### Phase 3 - Complétion (Semaine 5)
9. ✅ Favoris (bridge avec table existante)
10. ✅ Profil utilisateur
11. ✅ Catégories & thématiques
12. ✅ Tests & documentation

---

## 📝 15. NOTES IMPORTANTES

1. **Ne pas modifier EventList** - Utiliser des bridges pour interagir
2. **Réutiliser le JWT existant** - Clé dans `serect_key_qrcode`
3. **Namespace /lehiboo/v2/** - /v1/ est réservé au chat IA
4. **Tables existantes** - Utiliser `wp_lehiboo_user_favorites` pour les favoris
5. **Gateways paiement** - Utiliser ceux d'EventList (Stripe, PayPal)
6. **Pas de partenaires** - Phase 2, uniquement côté client pour l'instant
