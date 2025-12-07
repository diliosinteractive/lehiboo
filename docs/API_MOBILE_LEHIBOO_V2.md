# 📱 API Mobile LeHiboo v2
## Documentation complète pour les développeurs Flutter

**Version:** 2.0.0
**Base URL:** `https://lehiboo.com/wp-json/lehiboo/v2`
**Dernière mise à jour:** Décembre 2025

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Authentification](#2-authentification)
3. [Endpoints Client](#3-endpoints-client)
4. [Endpoints Partenaire](#4-endpoints-partenaire)
5. [Gestion des erreurs](#5-gestion-des-erreurs)
6. [Exemples Flutter/Dart](#6-exemples-flutterdart)

---

# 1. Vue d'ensemble

## 1.1 Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                     APP MOBILE FLUTTER                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌─────────────┐                      ┌─────────────┐           │
│   │   CLIENT    │                      │  PARTENAIRE │           │
│   │  (subscriber)                      │(event_manager)          │
│   └──────┬──────┘                      └──────┬──────┘           │
│          │                                    │                   │
│          └────────────┬───────────────────────┘                   │
│                       ▼                                           │
│          ┌────────────────────────┐                              │
│          │  /lehiboo/v2/auth/     │  ◄── Authentification unique │
│          │  login                 │                              │
│          └────────────┬───────────┘                              │
│                       │                                           │
│          ┌────────────┴───────────┐                              │
│          ▼                        ▼                              │
│   ┌─────────────┐          ┌─────────────┐                       │
│   │ Endpoints   │          │ Endpoints   │                       │
│   │ Client      │          │ Partenaire  │                       │
│   │             │          │             │                       │
│   │ • /events   │          │ • /partner/ │                       │
│   │ • /bookings │          │   events    │                       │
│   │ • /me/tickets│         │ • /partner/ │                       │
│   │ • /favorites│          │   scan      │                       │
│   └─────────────┘          └─────────────┘                       │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

## 1.2 Deux profils utilisateur

| Profil | Rôle WordPress | Fonctionnalités |
|--------|----------------|-----------------|
| **Client** | `subscriber` | Consulter activités, réserver, voir ses tickets |
| **Partenaire** | `el_event_manager` | Scanner tickets, gérer ses événements, voir stats |

## 1.3 Headers requis

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  # Pour endpoints authentifiés
```

## 1.4 Format des réponses

**Succès :**
```json
{
  "success": true,
  "data": { ... }
}
```

**Erreur :**
```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Message lisible",
    "details": { ... }  // Optionnel
  }
}
```

---

# 2. Authentification

## 2.1 Inscription Client

Crée un nouveau compte client.

```
POST /auth/register
```

**Headers :** Aucun requis

**Body :**
```json
{
  "email": "jean.dupont@email.com",
  "password": "MonMotDePasse123!",
  "first_name": "Jean",
  "last_name": "Dupont",
  "phone": "+33612345678"
}
```

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| `email` | string | ✅ | Email valide, unique |
| `password` | string | ✅ | Min 8 chars, 1 majuscule, 1 chiffre |
| `first_name` | string | ✅ | Min 2 chars |
| `last_name` | string | ✅ | Min 2 chars |
| `phone` | string | ❌ | Format E.164 (+33...) |

**Réponse 201 Created :**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email": "jean.dupont@email.com",
      "display_name": "Jean Dupont",
      "first_name": "Jean",
      "last_name": "Dupont",
      "role": "subscriber",
      "avatar_url": null
    },
    "tokens": {
      "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "token_type": "Bearer",
      "expires_in": 604800
    }
  }
}
```

**Erreurs possibles :**

| Code | HTTP | Message |
|------|------|---------|
| `email_exists` | 400 | Cet email est déjà utilisé |
| `invalid_email` | 400 | Format d'email invalide |
| `weak_password` | 400 | Le mot de passe doit contenir au moins 8 caractères, 1 majuscule et 1 chiffre |
| `invalid_phone` | 400 | Format de téléphone invalide |

---

## 2.2 Connexion (Client & Partenaire)

Authentifie un utilisateur et retourne les tokens JWT.

```
POST /auth/login
```

**Headers :** Aucun requis

**Body :**
```json
{
  "email": "jean.dupont@email.com",
  "password": "MonMotDePasse123!"
}
```

| Champ | Type | Requis |
|-------|------|--------|
| `email` | string | ✅ |
| `password` | string | ✅ |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email": "jean.dupont@email.com",
      "display_name": "Jean Dupont",
      "first_name": "Jean",
      "last_name": "Dupont",
      "phone": "+33612345678",
      "role": "subscriber",
      "avatar_url": "https://lehiboo.com/wp-content/uploads/avatars/123.jpg",
      "capabilities": {
        "can_book": true,
        "can_scan_tickets": false,
        "can_manage_events": false
      }
    },
    "tokens": {
      "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "token_type": "Bearer",
      "expires_in": 604800
    }
  }
}
```

**Pour un Partenaire (el_event_manager) :**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 456,
      "email": "contact@mjc-valence.fr",
      "display_name": "MJC Valence",
      "role": "el_event_manager",
      "capabilities": {
        "can_book": true,
        "can_scan_tickets": true,
        "can_manage_events": true
      }
    },
    "tokens": { ... },
    "partner_info": {
      "events_count": 12,
      "upcoming_events": 5,
      "organization_name": "MJC de Valence"
    }
  }
}
```

**Erreurs possibles :**

| Code | HTTP | Message |
|------|------|---------|
| `invalid_credentials` | 401 | Email ou mot de passe incorrect |
| `account_disabled` | 403 | Votre compte a été désactivé |
| `too_many_attempts` | 429 | Trop de tentatives, réessayez dans 15 minutes |

---

## 2.3 Rafraîchir le token

Obtient un nouveau access_token sans se reconnecter.

```
POST /auth/refresh
```

**Headers :** Aucun requis

**Body :**
```json
{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 604800
  }
}
```

**Erreurs possibles :**

| Code | HTTP | Message |
|------|------|---------|
| `invalid_refresh_token` | 401 | Refresh token invalide ou expiré |
| `token_revoked` | 401 | Token révoqué, veuillez vous reconnecter |

---

## 2.4 Mot de passe oublié

Envoie un email de réinitialisation.

```
POST /auth/forgot-password
```

**Body :**
```json
{
  "email": "jean.dupont@email.com"
}
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "message": "Si un compte existe avec cet email, un lien de réinitialisation a été envoyé."
  }
}
```

> Note : Toujours retourner 200 pour éviter l'énumération d'emails.

---

## 2.5 Réinitialiser le mot de passe

Définit un nouveau mot de passe avec le code reçu par email.

```
POST /auth/reset-password
```

**Body :**
```json
{
  "email": "jean.dupont@email.com",
  "code": "ABC123",
  "new_password": "NouveauMotDePasse456!"
}
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "message": "Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter."
  }
}
```

---

## 2.6 Déconnexion

Invalide les tokens côté serveur.

```
POST /auth/logout
```

**Headers :** `Authorization: Bearer {access_token}`

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "message": "Déconnexion réussie"
  }
}
```

---

## 2.7 Structure des tokens JWT

### Access Token (durée : 7 jours)

```json
{
  "iss": "https://lehiboo.com",
  "iat": 1702000000,
  "exp": 1702604800,
  "uid": 123,
  "email": "jean.dupont@email.com",
  "role": "subscriber",
  "type": "access"
}
```

### Refresh Token (durée : 30 jours)

```json
{
  "iss": "https://lehiboo.com",
  "iat": 1702000000,
  "exp": 1704592000,
  "uid": 123,
  "type": "refresh",
  "jti": "unique-token-id"
}
```

---

# 3. Endpoints Client

## 3.1 Événements

### 3.1.1 Liste des événements

```
GET /events
```

**Headers :** Aucun requis (endpoint public)

**Query Parameters :**

| Param | Type | Défaut | Description |
|-------|------|--------|-------------|
| `page` | int | 1 | Numéro de page |
| `per_page` | int | 20 | Items par page (max 100) |
| `search` | string | - | Recherche textuelle |
| `category` | string/int | - | Slug ou ID catégorie |
| `thematique` | string | - | Slug thématique |
| `city` | string | - | Nom de ville |
| `lat` | float | - | Latitude (avec lng) |
| `lng` | float | - | Longitude (avec lat) |
| `radius` | int | 20 | Rayon en km |
| `date_from` | string | - | Date début (YYYY-MM-DD) |
| `date_to` | string | - | Date fin (YYYY-MM-DD) |
| `price_min` | float | - | Prix minimum |
| `price_max` | float | - | Prix maximum |
| `free_only` | bool | false | Uniquement gratuits |
| `indoor` | bool | - | Activités intérieures |
| `outdoor` | bool | - | Activités extérieures |
| `family_friendly` | bool | - | Adapté aux familles |
| `age_min` | int | - | Âge minimum |
| `age_max` | int | - | Âge maximum |
| `orderby` | string | date | `date`, `price`, `rating`, `distance` |
| `order` | string | asc | `asc`, `desc` |

**Exemple de requête :**
```
GET /events?city=Valence&category=famille&date_from=2025-01-15&price_max=50&per_page=10
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 456,
        "title": "Atelier poterie enfants",
        "slug": "atelier-poterie-enfants",
        "excerpt": "Découvrez la poterie en famille dans un cadre convivial",
        "featured_image": {
          "thumbnail": "https://lehiboo.com/.../thumb.jpg",
          "medium": "https://lehiboo.com/.../medium.jpg",
          "large": "https://lehiboo.com/.../large.jpg"
        },
        "category": {
          "id": 12,
          "name": "Famille",
          "slug": "famille",
          "icon": "👨‍👩‍👧"
        },
        "dates": {
          "start_date": "2025-01-20",
          "end_date": "2025-01-20",
          "start_time": "14:00",
          "end_time": "16:30",
          "display": "Sam. 20 janv. 2025 • 14h00-16h30"
        },
        "location": {
          "venue_name": "MJC de Valence",
          "city": "Valence",
          "address": "20 rue des Ormes, 26000 Valence",
          "lat": 44.9333,
          "lng": 4.8917,
          "distance_km": 2.5
        },
        "pricing": {
          "is_free": false,
          "min": 15.00,
          "max": 25.00,
          "currency": "EUR",
          "display": "15€ - 25€"
        },
        "availability": {
          "status": "available",
          "spots_remaining": 8,
          "total_capacity": 20
        },
        "ratings": {
          "average": 4.8,
          "count": 127
        },
        "organizer": {
          "id": 789,
          "name": "MJC de Valence",
          "verified": true
        },
        "tags": ["indoor", "famille", "créatif"],
        "is_favorite": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total_items": 156,
      "total_pages": 16,
      "has_next": true,
      "has_prev": false
    },
    "filters_applied": {
      "city": "Valence",
      "category": "famille",
      "date_from": "2025-01-15",
      "price_max": 50
    }
  }
}
```

---

### 3.1.2 Détail d'un événement

```
GET /events/{id}
```

**Headers :** Optionnel `Authorization: Bearer {token}` (pour voir si favori)

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "title": "Atelier poterie enfants",
    "slug": "atelier-poterie-enfants",
    "excerpt": "Découvrez la poterie en famille",
    "description": "<p>Description HTML complète de l'activité...</p>",
    "featured_image": {
      "thumbnail": "https://...",
      "medium": "https://...",
      "large": "https://...",
      "full": "https://..."
    },
    "gallery": [
      "https://lehiboo.com/.../photo1.jpg",
      "https://lehiboo.com/.../photo2.jpg"
    ],
    "category": {
      "id": 12,
      "name": "Famille",
      "slug": "famille",
      "icon": "👨‍👩‍👧"
    },
    "thematiques": [
      { "id": 5, "name": "Créatif", "slug": "creatif" }
    ],
    "tags": ["poterie", "enfants", "manuel"],
    "dates": {
      "start_date": "2025-01-20",
      "end_date": "2025-01-20",
      "start_time": "14:00",
      "end_time": "16:30",
      "display": "Samedi 20 janvier 2025, 14h00-16h30",
      "duration_minutes": 150,
      "is_recurring": false
    },
    "location": {
      "venue_name": "MJC de Valence",
      "address": "20 rue des Ormes",
      "city": "Valence",
      "postal_code": "26000",
      "country": "France",
      "lat": 44.9333,
      "lng": 4.8917,
      "directions_url": "https://maps.google.com/..."
    },
    "pricing": {
      "is_free": false,
      "currency": "EUR",
      "ticket_types": [
        {
          "id": 1,
          "name": "Enfant (4-12 ans)",
          "price": 15.00,
          "description": "Tarif enfant",
          "available": true,
          "spots_remaining": 5
        },
        {
          "id": 2,
          "name": "Adulte accompagnant",
          "price": 25.00,
          "description": "1 adulte par enfant",
          "available": true,
          "spots_remaining": 3
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
      "age_display": "4-12 ans",
      "family_friendly": true,
      "requirements": "Prévoir des vêtements qui ne craignent pas"
    },
    "environment": {
      "indoor": true,
      "outdoor": false
    },
    "ratings": {
      "average": 4.8,
      "count": 127,
      "distribution": { "5": 98, "4": 22, "3": 5, "2": 1, "1": 1 }
    },
    "reviews": [
      {
        "id": 1001,
        "author": "Marie D.",
        "rating": 5,
        "comment": "Super atelier, les enfants ont adoré !",
        "date": "2025-01-05",
        "verified_booking": true
      }
    ],
    "organizer": {
      "id": 789,
      "name": "MJC de Valence",
      "logo": "https://...",
      "description": "La MJC de Valence propose...",
      "verified": true,
      "contact": {
        "phone": "+33475123456",
        "email": "contact@mjc-valence.fr",
        "website": "https://mjc-valence.fr"
      }
    },
    "booking_info": {
      "mode": "online",
      "cancellation_policy": "Annulation gratuite jusqu'à 48h avant",
      "instant_confirmation": true
    },
    "similar_events": [
      {
        "id": 457,
        "title": "Atelier peinture enfants",
        "thumbnail": "https://...",
        "price_display": "12€",
        "date_display": "Sam. 27 janv."
      }
    ],
    "is_favorite": false,
    "share_url": "https://lehiboo.com/event/atelier-poterie-enfants",
    "created_at": "2025-01-01T10:00:00Z",
    "updated_at": "2025-01-10T15:30:00Z"
  }
}
```

---

## 3.2 Réservations

### 3.2.1 Créer une réservation

```
POST /bookings
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "event_id": 456,
  "tickets": [
    {
      "ticket_type_id": 1,
      "quantity": 2,
      "attendees": [
        { "first_name": "Lucas", "last_name": "Dupont", "age": 8 },
        { "first_name": "Emma", "last_name": "Dupont", "age": 6 }
      ]
    },
    {
      "ticket_type_id": 2,
      "quantity": 1,
      "attendees": [
        { "first_name": "Jean", "last_name": "Dupont" }
      ]
    }
  ],
  "buyer_info": {
    "email": "jean.dupont@email.com",
    "phone": "+33612345678"
  },
  "coupon_code": "PROMO10",
  "notes": "Allergie au latex"
}
```

**Réponse 201 Created :**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1234,
      "reference": "LH-2025-001234",
      "status": "pending_payment",
      "expires_at": "2025-01-15T12:30:00Z"
    },
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants",
      "date": "2025-01-20",
      "time": "14:00 - 16:30",
      "venue": "MJC de Valence"
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
      "discount": {
        "code": "PROMO10",
        "amount": 5.50,
        "type": "percentage",
        "value": 10
      },
      "fees": 0.00,
      "total": 49.50,
      "currency": "EUR"
    },
    "payment": {
      "required": true,
      "methods_available": ["stripe", "paypal"],
      "stripe": {
        "payment_intent_id": "pi_3ABC123",
        "client_secret": "pi_3ABC123_secret_XYZ",
        "publishable_key": "pk_live_xxx"
      }
    }
  }
}
```

---

### 3.2.2 Confirmer une réservation

Après paiement réussi côté client (Stripe).

```
POST /bookings/{id}/confirm
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "payment_intent_id": "pi_3ABC123",
  "payment_method_id": "pm_xxx"
}
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1234,
      "reference": "LH-2025-001234",
      "status": "confirmed",
      "confirmed_at": "2025-01-15T10:35:00Z"
    },
    "tickets": [
      {
        "id": 5678,
        "code": "LH-T-5678-ABCD",
        "qr_code_data": "LH-T-5678-ABCD-SIGNATURE-HASH",
        "qr_code_image": "https://api.lehiboo.com/qr/LH-T-5678-ABCD.png",
        "attendee": {
          "name": "Lucas Dupont",
          "age": 8
        },
        "ticket_type": "Enfant (4-12 ans)",
        "status": "valid"
      },
      {
        "id": 5679,
        "code": "LH-T-5679-EFGH",
        "qr_code_data": "LH-T-5679-EFGH-SIGNATURE-HASH",
        "qr_code_image": "https://api.lehiboo.com/qr/LH-T-5679-EFGH.png",
        "attendee": {
          "name": "Emma Dupont",
          "age": 6
        },
        "ticket_type": "Enfant (4-12 ans)",
        "status": "valid"
      },
      {
        "id": 5680,
        "code": "LH-T-5680-IJKL",
        "qr_code_data": "LH-T-5680-IJKL-SIGNATURE-HASH",
        "qr_code_image": "https://api.lehiboo.com/qr/LH-T-5680-IJKL.png",
        "attendee": {
          "name": "Jean Dupont"
        },
        "ticket_type": "Adulte accompagnant",
        "status": "valid"
      }
    ],
    "downloads": {
      "all_tickets_pdf": "https://api.lehiboo.com/bookings/1234/tickets.pdf",
      "receipt_pdf": "https://api.lehiboo.com/bookings/1234/receipt.pdf"
    },
    "calendar": {
      "google_url": "https://calendar.google.com/calendar/render?action=TEMPLATE&...",
      "ical_url": "https://api.lehiboo.com/bookings/1234/calendar.ics",
      "outlook_url": "https://outlook.live.com/calendar/0/deeplink/compose?..."
    },
    "email_sent": true
  }
}
```

---

### 3.2.3 Mes réservations

```
GET /me/bookings
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Query Parameters :**

| Param | Type | Défaut | Description |
|-------|------|--------|-------------|
| `status` | string | all | `all`, `upcoming`, `past`, `cancelled` |
| `page` | int | 1 | Numéro de page |
| `per_page` | int | 20 | Items par page |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "bookings": [
      {
        "id": 1234,
        "reference": "LH-2025-001234",
        "status": "confirmed",
        "event": {
          "id": 456,
          "title": "Atelier poterie enfants",
          "thumbnail": "https://...",
          "date": "2025-01-20",
          "time": "14:00 - 16:30",
          "venue": "MJC de Valence",
          "address": "20 rue des Ormes, 26000 Valence"
        },
        "tickets_count": 3,
        "total_paid": 49.50,
        "currency": "EUR",
        "booked_at": "2025-01-15T10:30:00Z",
        "is_upcoming": true,
        "can_cancel": true,
        "days_until": 5
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_items": 8,
      "total_pages": 1
    },
    "summary": {
      "upcoming": 2,
      "past": 5,
      "cancelled": 1
    }
  }
}
```

---

### 3.2.4 Détail d'une réservation

```
GET /me/bookings/{id}
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "id": 1234,
    "reference": "LH-2025-001234",
    "status": "confirmed",
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants",
      "description": "...",
      "featured_image": "https://...",
      "date": "2025-01-20",
      "time": "14:00 - 16:30",
      "duration_minutes": 150,
      "venue": {
        "name": "MJC de Valence",
        "address": "20 rue des Ormes, 26000 Valence",
        "lat": 44.9333,
        "lng": 4.8917
      },
      "organizer": {
        "name": "MJC de Valence",
        "phone": "+33475123456"
      }
    },
    "tickets": [
      {
        "id": 5678,
        "code": "LH-T-5678-ABCD",
        "qr_code_data": "LH-T-5678-ABCD-SIGNATURE-HASH",
        "qr_code_image": "https://...",
        "attendee": { "name": "Lucas Dupont", "age": 8 },
        "ticket_type": "Enfant (4-12 ans)",
        "price": 15.00,
        "status": "valid"
      }
    ],
    "buyer": {
      "name": "Jean Dupont",
      "email": "jean.dupont@email.com",
      "phone": "+33612345678"
    },
    "pricing": {
      "subtotal": 55.00,
      "discount": 5.50,
      "total": 49.50,
      "currency": "EUR"
    },
    "payment": {
      "method": "stripe",
      "status": "paid",
      "paid_at": "2025-01-15T10:35:00Z",
      "receipt_url": "https://..."
    },
    "notes": "Allergie au latex",
    "booked_at": "2025-01-15T10:30:00Z",
    "confirmed_at": "2025-01-15T10:35:00Z",
    "cancellation": {
      "allowed": true,
      "deadline": "2025-01-18T14:00:00Z",
      "refund_policy": "Remboursement intégral jusqu'à 48h avant"
    },
    "downloads": {
      "tickets_pdf": "https://...",
      "receipt_pdf": "https://..."
    }
  }
}
```

---

### 3.2.5 Annuler une réservation

```
POST /me/bookings/{id}/cancel
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "reason": "Empêchement personnel"
}
```

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1234,
      "reference": "LH-2025-001234",
      "status": "cancelled",
      "cancelled_at": "2025-01-16T09:00:00Z"
    },
    "refund": {
      "eligible": true,
      "amount": 49.50,
      "currency": "EUR",
      "status": "processing",
      "method": "original_payment_method",
      "estimated_date": "2025-01-19"
    }
  }
}
```

---

## 3.3 Tickets

### 3.3.1 Mes tickets

```
GET /me/tickets
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Query Parameters :**

| Param | Type | Défaut | Description |
|-------|------|--------|-------------|
| `status` | string | all | `all`, `valid`, `used`, `expired`, `cancelled` |
| `event_id` | int | - | Filtrer par événement |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": 5678,
        "code": "LH-T-5678-ABCD",
        "status": "valid",
        "qr_code": {
          "data": "LH-T-5678-ABCD-SIGNATURE-HASH",
          "image_url": "https://api.lehiboo.com/qr/LH-T-5678-ABCD.png"
        },
        "attendee": {
          "first_name": "Lucas",
          "last_name": "Dupont",
          "age": 8
        },
        "ticket_type": "Enfant (4-12 ans)",
        "price": 15.00,
        "event": {
          "id": 456,
          "title": "Atelier poterie enfants",
          "thumbnail": "https://...",
          "date": "2025-01-20",
          "time": "14:00",
          "venue": {
            "name": "MJC de Valence",
            "address": "20 rue des Ormes, 26000 Valence",
            "lat": 44.9333,
            "lng": 4.8917
          }
        },
        "booking": {
          "id": 1234,
          "reference": "LH-2025-001234"
        },
        "validity": {
          "valid_from": "2025-01-20T13:00:00Z",
          "valid_until": "2025-01-20T17:00:00Z",
          "is_valid_now": false
        },
        "download_url": "https://api.lehiboo.com/tickets/5678/download.pdf"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_items": 12
    },
    "summary": {
      "valid": 3,
      "used": 8,
      "expired": 1
    }
  }
}
```

---

### 3.3.2 Détail d'un ticket

```
GET /me/tickets/{id}
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

Retourne les mêmes informations que dans la liste avec plus de détails.

---

## 3.4 Favoris

### 3.4.1 Liste des favoris

```
GET /me/favorites
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "favorites": [
      {
        "id": 456,
        "event": {
          "id": 456,
          "title": "Atelier poterie enfants",
          "thumbnail": "https://...",
          "date": "2025-01-20",
          "price_display": "15€ - 25€",
          "location": "Valence",
          "availability": "available"
        },
        "added_at": "2025-01-10T15:00:00Z"
      }
    ],
    "total": 12
  }
}
```

### 3.4.2 Ajouter aux favoris

```
POST /me/favorites
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "event_id": 789
}
```

**Réponse 201 Created :**
```json
{
  "success": true,
  "data": {
    "message": "Ajouté aux favoris",
    "event_id": 789
  }
}
```

### 3.4.3 Retirer des favoris

```
DELETE /me/favorites/{event_id}
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "message": "Retiré des favoris"
  }
}
```

---

## 3.5 Profil utilisateur

### 3.5.1 Mon profil

```
GET /me
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Réponse 200 OK :**
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
      "bookings_total": 8,
      "bookings_upcoming": 2,
      "favorites_count": 12,
      "reviews_count": 3
    },
    "member_since": "2024-06-15T10:00:00Z"
  }
}
```

### 3.5.2 Modifier mon profil

```
PUT /me
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "first_name": "Jean-Pierre",
  "phone": "+33698765432",
  "preferences": {
    "notifications_push": false
  }
}
```

### 3.5.3 Changer mon mot de passe

```
PUT /me/password
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS

**Body :**
```json
{
  "current_password": "AncienMotDePasse123!",
  "new_password": "NouveauMotDePasse456!"
}
```

---

## 3.6 Catégories

### 3.6.1 Liste des catégories

```
GET /categories
```

**Headers :** Aucun requis (endpoint public)

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Sport",
      "slug": "sport",
      "icon": "⚽",
      "image": "https://...",
      "events_count": 45,
      "color": "#FF5722"
    },
    {
      "id": 2,
      "name": "Famille",
      "slug": "famille",
      "icon": "👨‍👩‍👧",
      "events_count": 78,
      "color": "#4CAF50",
      "subcategories": [
        { "id": 21, "name": "0-3 ans", "slug": "0-3-ans", "events_count": 12 },
        { "id": 22, "name": "4-12 ans", "slug": "4-12-ans", "events_count": 45 }
      ]
    }
  ]
}
```

---

# 4. Endpoints Partenaire

Ces endpoints sont accessibles uniquement aux utilisateurs avec le rôle `el_event_manager`.

## 4.1 Mes événements

### 4.1.1 Liste de mes événements

```
GET /partner/events
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS (rôle partenaire)

**Query Parameters :**

| Param | Type | Défaut | Description |
|-------|------|--------|-------------|
| `status` | string | all | `all`, `upcoming`, `past`, `draft` |
| `page` | int | 1 | Numéro de page |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 456,
        "title": "Atelier poterie enfants",
        "thumbnail": "https://...",
        "date": "2025-01-20",
        "time": "14:00 - 16:30",
        "status": "publish",
        "stats": {
          "capacity": 20,
          "booked": 12,
          "checked_in": 0,
          "revenue": 540.00
        },
        "is_upcoming": true
      }
    ],
    "pagination": { ... },
    "summary": {
      "total_events": 12,
      "upcoming": 5,
      "total_bookings": 156,
      "total_revenue": 8750.00
    }
  }
}
```

---

## 4.2 Scanner de tickets

### 4.2.1 Scanner un ticket

Valide un ticket via son QR code.

```
POST /partner/scan
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS (rôle partenaire)

**Body :**
```json
{
  "qr_code": "LH-T-5678-ABCD-SIGNATURE-HASH",
  "event_id": 456
}
```

**Réponse 200 OK - Ticket valide :**
```json
{
  "success": true,
  "data": {
    "status": "valid",
    "message": "Ticket validé avec succès",
    "ticket": {
      "id": 5678,
      "code": "LH-T-5678-ABCD",
      "attendee": {
        "name": "Lucas Dupont",
        "age": 8
      },
      "ticket_type": "Enfant (4-12 ans)",
      "checked_in_at": "2025-01-20T13:55:00Z"
    },
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants",
      "date": "2025-01-20",
      "time": "14:00 - 16:30"
    },
    "booking": {
      "reference": "LH-2025-001234",
      "buyer": "Jean Dupont"
    },
    "stats": {
      "checked_in": 13,
      "total": 20,
      "remaining": 7
    }
  }
}
```

**Réponse 400 - Ticket déjà scanné :**
```json
{
  "success": false,
  "error": {
    "code": "ticket_already_checked",
    "message": "Ce ticket a déjà été scanné",
    "details": {
      "checked_in_at": "2025-01-20T13:45:00Z",
      "attendee": "Lucas Dupont"
    }
  }
}
```

**Réponse 403 - Pas autorisé pour cet événement :**
```json
{
  "success": false,
  "error": {
    "code": "event_not_authorized",
    "message": "Vous n'êtes pas autorisé à scanner les tickets de cet événement"
  }
}
```

**Réponse 404 - QR code invalide :**
```json
{
  "success": false,
  "error": {
    "code": "ticket_not_found",
    "message": "QR code invalide ou ticket introuvable"
  }
}
```

---

### 4.2.2 Historique des scans

```
GET /partner/scan/history
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS (rôle partenaire)

**Query Parameters :**

| Param | Type | Description |
|-------|------|-------------|
| `event_id` | int | Filtrer par événement |
| `date` | string | Date (YYYY-MM-DD) |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "scans": [
      {
        "ticket_id": 5678,
        "ticket_code": "LH-T-5678-ABCD",
        "attendee": "Lucas Dupont",
        "ticket_type": "Enfant",
        "checked_in_at": "2025-01-20T13:55:00Z",
        "scanned_by": "MJC Valence"
      }
    ],
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants"
    },
    "summary": {
      "checked_in": 13,
      "total": 20
    }
  }
}
```

---

## 4.3 Statistiques partenaire

### 4.3.1 Dashboard stats

```
GET /partner/stats
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS (rôle partenaire)

**Query Parameters :**

| Param | Type | Description |
|-------|------|-------------|
| `period` | string | `week`, `month`, `year`, `all` |

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "period": "month",
    "overview": {
      "total_events": 12,
      "total_bookings": 156,
      "total_tickets_sold": 423,
      "total_revenue": 8750.00,
      "currency": "EUR",
      "average_fill_rate": 72
    },
    "trends": {
      "bookings_change": 15.5,
      "revenue_change": 22.3
    },
    "upcoming_events": [
      {
        "id": 456,
        "title": "Atelier poterie enfants",
        "date": "2025-01-20",
        "booked": 12,
        "capacity": 20
      }
    ],
    "recent_bookings": [
      {
        "id": 1234,
        "reference": "LH-2025-001234",
        "event_title": "Atelier poterie",
        "tickets": 3,
        "amount": 49.50,
        "booked_at": "2025-01-15T10:30:00Z"
      }
    ]
  }
}
```

---

### 4.3.2 Stats d'un événement

```
GET /partner/events/{id}/stats
```

**Headers :** `Authorization: Bearer {token}` ✅ REQUIS (rôle partenaire)

**Réponse 200 OK :**
```json
{
  "success": true,
  "data": {
    "event": {
      "id": 456,
      "title": "Atelier poterie enfants"
    },
    "bookings": {
      "total": 12,
      "confirmed": 10,
      "pending": 1,
      "cancelled": 1
    },
    "tickets": {
      "sold": 28,
      "checked_in": 0,
      "by_type": [
        { "type": "Enfant", "sold": 18, "checked_in": 0 },
        { "type": "Adulte", "sold": 10, "checked_in": 0 }
      ]
    },
    "capacity": {
      "total": 40,
      "remaining": 12,
      "fill_rate": 70
    },
    "revenue": {
      "total": 540.00,
      "currency": "EUR",
      "by_ticket_type": [
        { "type": "Enfant", "amount": 270.00 },
        { "type": "Adulte", "amount": 270.00 }
      ]
    },
    "attendees": [
      {
        "booking_ref": "LH-2025-001234",
        "buyer": "Jean Dupont",
        "tickets_count": 3,
        "status": "confirmed"
      }
    ]
  }
}
```

---

# 5. Gestion des erreurs

## 5.1 Codes d'erreur HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide (données manquantes/incorrectes) |
| 401 | Non authentifié (token manquant/invalide) |
| 403 | Interdit (pas les permissions) |
| 404 | Ressource non trouvée |
| 409 | Conflit (ex: email déjà utilisé) |
| 422 | Entité non traitable (validation échouée) |
| 429 | Trop de requêtes (rate limit) |
| 500 | Erreur serveur |

## 5.2 Codes d'erreur applicatifs

### Authentification

| Code | Message |
|------|---------|
| `invalid_credentials` | Email ou mot de passe incorrect |
| `email_exists` | Cet email est déjà utilisé |
| `invalid_email` | Format d'email invalide |
| `weak_password` | Mot de passe trop faible |
| `token_expired` | Token expiré |
| `token_invalid` | Token invalide |
| `account_disabled` | Compte désactivé |
| `too_many_attempts` | Trop de tentatives |

### Réservations

| Code | Message |
|------|---------|
| `event_not_found` | Événement introuvable |
| `event_sold_out` | Événement complet |
| `event_expired` | Événement passé |
| `invalid_ticket_type` | Type de ticket invalide |
| `insufficient_spots` | Places insuffisantes |
| `booking_not_found` | Réservation introuvable |
| `booking_not_yours` | Cette réservation ne vous appartient pas |
| `booking_already_cancelled` | Réservation déjà annulée |
| `cancellation_deadline_passed` | Délai d'annulation dépassé |
| `payment_failed` | Échec du paiement |
| `invalid_coupon` | Code promo invalide |

### Scanner (Partenaire)

| Code | Message |
|------|---------|
| `ticket_not_found` | Ticket introuvable |
| `ticket_already_checked` | Ticket déjà scanné |
| `ticket_cancelled` | Ticket annulé |
| `ticket_expired` | Ticket expiré |
| `event_not_authorized` | Non autorisé pour cet événement |

## 5.3 Rate Limiting

| Endpoint | Limite | Fenêtre |
|----------|--------|---------|
| `/auth/register` | 5 req | 1 heure |
| `/auth/login` | 10 req | 15 min |
| `/auth/forgot-password` | 3 req | 1 heure |
| `/events` (GET) | 100 req | 1 min |
| `/bookings` (POST) | 10 req | 1 min |
| `/partner/scan` | 60 req | 1 min |
| Autres | 60 req | 1 min |

**Headers de réponse rate limit :**
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1702000060
```

---

# 6. Exemples Flutter/Dart

## 6.1 Configuration du client HTTP

```dart
// lib/core/api/api_client.dart

import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  static const String baseUrl = 'https://lehiboo.com/wp-json/lehiboo/v2';

  final Dio _dio;
  final FlutterSecureStorage _storage;

  ApiClient()
    : _dio = Dio(BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      )),
      _storage = const FlutterSecureStorage() {

    // Intercepteur pour ajouter le token
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.read(key: 'access_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (error, handler) async {
        // Refresh token si 401
        if (error.response?.statusCode == 401) {
          final refreshed = await _refreshToken();
          if (refreshed) {
            // Retry la requête
            final opts = error.requestOptions;
            final token = await _storage.read(key: 'access_token');
            opts.headers['Authorization'] = 'Bearer $token';
            final response = await _dio.fetch(opts);
            return handler.resolve(response);
          }
        }
        return handler.next(error);
      },
    ));
  }

  Future<bool> _refreshToken() async {
    try {
      final refreshToken = await _storage.read(key: 'refresh_token');
      if (refreshToken == null) return false;

      final response = await _dio.post('/auth/refresh', data: {
        'refresh_token': refreshToken,
      });

      if (response.data['success']) {
        await _storage.write(
          key: 'access_token',
          value: response.data['data']['access_token'],
        );
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  // GET request
  Future<ApiResponse<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    T Function(Map<String, dynamic>)? fromJson,
  }) async {
    try {
      final response = await _dio.get(path, queryParameters: queryParameters);
      return ApiResponse.success(
        fromJson != null ? fromJson(response.data['data']) : response.data['data'],
      );
    } on DioException catch (e) {
      return ApiResponse.error(_handleError(e));
    }
  }

  // POST request
  Future<ApiResponse<T>> post<T>(
    String path, {
    Map<String, dynamic>? data,
    T Function(Map<String, dynamic>)? fromJson,
  }) async {
    try {
      final response = await _dio.post(path, data: data);
      return ApiResponse.success(
        fromJson != null ? fromJson(response.data['data']) : response.data['data'],
      );
    } on DioException catch (e) {
      return ApiResponse.error(_handleError(e));
    }
  }

  ApiError _handleError(DioException e) {
    if (e.response?.data != null && e.response?.data['error'] != null) {
      final error = e.response!.data['error'];
      return ApiError(
        code: error['code'] ?? 'unknown_error',
        message: error['message'] ?? 'Une erreur est survenue',
        statusCode: e.response?.statusCode ?? 500,
      );
    }
    return ApiError(
      code: 'network_error',
      message: 'Impossible de se connecter au serveur',
      statusCode: 0,
    );
  }
}

// Response wrapper
class ApiResponse<T> {
  final T? data;
  final ApiError? error;
  final bool isSuccess;

  ApiResponse.success(this.data) : error = null, isSuccess = true;
  ApiResponse.error(this.error) : data = null, isSuccess = false;
}

class ApiError {
  final String code;
  final String message;
  final int statusCode;

  ApiError({required this.code, required this.message, required this.statusCode});
}
```

---

## 6.2 Service d'authentification

```dart
// lib/features/auth/data/auth_repository.dart

import '../models/user.dart';
import '../models/auth_tokens.dart';

class AuthRepository {
  final ApiClient _api;
  final FlutterSecureStorage _storage;

  AuthRepository(this._api) : _storage = const FlutterSecureStorage();

  /// Inscription
  Future<ApiResponse<User>> register({
    required String email,
    required String password,
    required String firstName,
    required String lastName,
    String? phone,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      '/auth/register',
      data: {
        'email': email,
        'password': password,
        'first_name': firstName,
        'last_name': lastName,
        if (phone != null) 'phone': phone,
      },
    );

    if (response.isSuccess) {
      final data = response.data!;
      await _saveTokens(AuthTokens.fromJson(data['tokens']));
      return ApiResponse.success(User.fromJson(data['user']));
    }

    return ApiResponse.error(response.error);
  }

  /// Connexion
  Future<ApiResponse<User>> login({
    required String email,
    required String password,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      '/auth/login',
      data: {
        'email': email,
        'password': password,
      },
    );

    if (response.isSuccess) {
      final data = response.data!;
      await _saveTokens(AuthTokens.fromJson(data['tokens']));
      return ApiResponse.success(User.fromJson(data['user']));
    }

    return ApiResponse.error(response.error);
  }

  /// Déconnexion
  Future<void> logout() async {
    await _api.post('/auth/logout');
    await _storage.deleteAll();
  }

  /// Mot de passe oublié
  Future<ApiResponse<void>> forgotPassword(String email) async {
    return _api.post('/auth/forgot-password', data: {'email': email});
  }

  /// Vérifier si connecté
  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: 'access_token');
    return token != null;
  }

  /// Récupérer l'utilisateur courant
  Future<ApiResponse<User>> getCurrentUser() async {
    return _api.get('/me', fromJson: User.fromJson);
  }

  Future<void> _saveTokens(AuthTokens tokens) async {
    await _storage.write(key: 'access_token', value: tokens.accessToken);
    await _storage.write(key: 'refresh_token', value: tokens.refreshToken);
  }
}

// Models
class User {
  final int id;
  final String email;
  final String displayName;
  final String firstName;
  final String lastName;
  final String? phone;
  final String role;
  final String? avatarUrl;
  final UserCapabilities capabilities;

  User({
    required this.id,
    required this.email,
    required this.displayName,
    required this.firstName,
    required this.lastName,
    this.phone,
    required this.role,
    this.avatarUrl,
    required this.capabilities,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
    id: json['id'],
    email: json['email'],
    displayName: json['display_name'],
    firstName: json['first_name'],
    lastName: json['last_name'],
    phone: json['phone'],
    role: json['role'],
    avatarUrl: json['avatar_url'],
    capabilities: UserCapabilities.fromJson(json['capabilities']),
  );

  bool get isPartner => role == 'el_event_manager';
  bool get isClient => role == 'subscriber';
}

class UserCapabilities {
  final bool canBook;
  final bool canScanTickets;
  final bool canManageEvents;

  UserCapabilities({
    required this.canBook,
    required this.canScanTickets,
    required this.canManageEvents,
  });

  factory UserCapabilities.fromJson(Map<String, dynamic> json) => UserCapabilities(
    canBook: json['can_book'] ?? false,
    canScanTickets: json['can_scan_tickets'] ?? false,
    canManageEvents: json['can_manage_events'] ?? false,
  );
}

class AuthTokens {
  final String accessToken;
  final String refreshToken;
  final int expiresIn;

  AuthTokens({
    required this.accessToken,
    required this.refreshToken,
    required this.expiresIn,
  });

  factory AuthTokens.fromJson(Map<String, dynamic> json) => AuthTokens(
    accessToken: json['access_token'],
    refreshToken: json['refresh_token'],
    expiresIn: json['expires_in'],
  );
}
```

---

## 6.3 Service des événements

```dart
// lib/features/events/data/events_repository.dart

class EventsRepository {
  final ApiClient _api;

  EventsRepository(this._api);

  /// Liste des événements avec filtres
  Future<ApiResponse<EventsListResponse>> getEvents({
    int page = 1,
    int perPage = 20,
    String? search,
    String? category,
    String? city,
    double? lat,
    double? lng,
    int? radius,
    DateTime? dateFrom,
    DateTime? dateTo,
    double? priceMin,
    double? priceMax,
    bool? freeOnly,
    bool? indoor,
    bool? outdoor,
    bool? familyFriendly,
    String orderBy = 'date',
    String order = 'asc',
  }) async {
    final queryParams = <String, dynamic>{
      'page': page,
      'per_page': perPage,
      if (search != null) 'search': search,
      if (category != null) 'category': category,
      if (city != null) 'city': city,
      if (lat != null) 'lat': lat,
      if (lng != null) 'lng': lng,
      if (radius != null) 'radius': radius,
      if (dateFrom != null) 'date_from': _formatDate(dateFrom),
      if (dateTo != null) 'date_to': _formatDate(dateTo),
      if (priceMin != null) 'price_min': priceMin,
      if (priceMax != null) 'price_max': priceMax,
      if (freeOnly != null) 'free_only': freeOnly,
      if (indoor != null) 'indoor': indoor,
      if (outdoor != null) 'outdoor': outdoor,
      if (familyFriendly != null) 'family_friendly': familyFriendly,
      'orderby': orderBy,
      'order': order,
    };

    return _api.get(
      '/events',
      queryParameters: queryParams,
      fromJson: EventsListResponse.fromJson,
    );
  }

  /// Détail d'un événement
  Future<ApiResponse<Event>> getEvent(int id) async {
    return _api.get('/events/$id', fromJson: Event.fromJson);
  }

  String _formatDate(DateTime date) =>
    '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
}

// Models
class Event {
  final int id;
  final String title;
  final String slug;
  final String excerpt;
  final String? description;
  final EventImages images;
  final EventCategory category;
  final EventDates dates;
  final EventLocation location;
  final EventPricing pricing;
  final EventAvailability availability;
  final EventRatings? ratings;
  final EventOrganizer organizer;
  final List<String> tags;
  final bool isFavorite;

  Event({
    required this.id,
    required this.title,
    required this.slug,
    required this.excerpt,
    this.description,
    required this.images,
    required this.category,
    required this.dates,
    required this.location,
    required this.pricing,
    required this.availability,
    this.ratings,
    required this.organizer,
    required this.tags,
    required this.isFavorite,
  });

  factory Event.fromJson(Map<String, dynamic> json) => Event(
    id: json['id'],
    title: json['title'],
    slug: json['slug'],
    excerpt: json['excerpt'],
    description: json['description'],
    images: EventImages.fromJson(json['featured_image']),
    category: EventCategory.fromJson(json['category']),
    dates: EventDates.fromJson(json['dates']),
    location: EventLocation.fromJson(json['location']),
    pricing: EventPricing.fromJson(json['pricing']),
    availability: EventAvailability.fromJson(json['availability']),
    ratings: json['ratings'] != null ? EventRatings.fromJson(json['ratings']) : null,
    organizer: EventOrganizer.fromJson(json['organizer']),
    tags: List<String>.from(json['tags'] ?? []),
    isFavorite: json['is_favorite'] ?? false,
  );
}

// ... autres models (EventImages, EventCategory, etc.)
```

---

## 6.4 Scanner de tickets (Partenaire)

```dart
// lib/features/partner/data/scanner_repository.dart

class ScannerRepository {
  final ApiClient _api;

  ScannerRepository(this._api);

  /// Scanner un ticket
  Future<ApiResponse<ScanResult>> scanTicket({
    required String qrCode,
    required int eventId,
  }) async {
    return _api.post(
      '/partner/scan',
      data: {
        'qr_code': qrCode,
        'event_id': eventId,
      },
      fromJson: ScanResult.fromJson,
    );
  }

  /// Historique des scans
  Future<ApiResponse<ScanHistory>> getScanHistory({
    int? eventId,
    DateTime? date,
  }) async {
    return _api.get(
      '/partner/scan/history',
      queryParameters: {
        if (eventId != null) 'event_id': eventId,
        if (date != null) 'date': _formatDate(date),
      },
      fromJson: ScanHistory.fromJson,
    );
  }
}

class ScanResult {
  final String status; // 'valid', 'already_checked', 'invalid'
  final String message;
  final TicketInfo? ticket;
  final ScanStats? stats;

  ScanResult({
    required this.status,
    required this.message,
    this.ticket,
    this.stats,
  });

  factory ScanResult.fromJson(Map<String, dynamic> json) => ScanResult(
    status: json['status'],
    message: json['message'],
    ticket: json['ticket'] != null ? TicketInfo.fromJson(json['ticket']) : null,
    stats: json['stats'] != null ? ScanStats.fromJson(json['stats']) : null,
  );

  bool get isValid => status == 'valid';
  bool get isAlreadyChecked => status == 'already_checked';
}
```

---

## 6.5 Widget d'écran de connexion

```dart
// lib/features/auth/presentation/login_screen.dart

import 'package:flutter/material.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Connexion')),
      body: SafeArea(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Logo
                Image.asset('assets/logo.png', height: 80),
                SizedBox(height: 48),

                // Email
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: InputDecoration(
                    labelText: 'Email',
                    prefixIcon: Icon(Icons.email),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer votre email';
                    }
                    if (!value.contains('@')) {
                      return 'Email invalide';
                    }
                    return null;
                  },
                ),
                SizedBox(height: 16),

                // Password
                TextFormField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: 'Mot de passe',
                    prefixIcon: Icon(Icons.lock),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer votre mot de passe';
                    }
                    return null;
                  },
                ),
                SizedBox(height: 8),

                // Forgot password
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: () => Navigator.pushNamed(context, '/forgot-password'),
                    child: Text('Mot de passe oublié ?'),
                  ),
                ),
                SizedBox(height: 24),

                // Error message
                if (_errorMessage != null)
                  Container(
                    padding: EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      _errorMessage!,
                      style: TextStyle(color: Colors.red.shade700),
                    ),
                  ),
                SizedBox(height: 16),

                // Login button
                ElevatedButton(
                  onPressed: _isLoading ? null : _handleLogin,
                  style: ElevatedButton.styleFrom(
                    padding: EdgeInsets.symmetric(vertical: 16),
                  ),
                  child: _isLoading
                    ? SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : Text('Se connecter'),
                ),
                SizedBox(height: 16),

                // Register link
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('Pas encore de compte ?'),
                    TextButton(
                      onPressed: () => Navigator.pushNamed(context, '/register'),
                      child: Text('S\'inscrire'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final authRepo = context.read<AuthRepository>();
    final result = await authRepo.login(
      email: _emailController.text.trim(),
      password: _passwordController.text,
    );

    setState(() => _isLoading = false);

    if (result.isSuccess) {
      final user = result.data!;

      // Navigation selon le rôle
      if (user.isPartner) {
        Navigator.pushReplacementNamed(context, '/partner/dashboard');
      } else {
        Navigator.pushReplacementNamed(context, '/home');
      }
    } else {
      setState(() => _errorMessage = result.error!.message);
    }
  }
}
```

---

C'est tout ! Cette documentation couvre l'ensemble de l'API mobile LeHiboo v2.

Pour toute question : api@lehiboo.com
