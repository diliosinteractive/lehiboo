# LeHiboo Mobile API v2.0.0

Documentation complète pour les développeurs mobiles (iOS/Android/Flutter)

---

## Table des matières

1. [Configuration de base](#1-configuration-de-base)
2. [Authentification JWT](#2-authentification-jwt)
3. [Endpoints API](#3-endpoints-api)
4. [Codes d'erreur](#4-codes-derreur)
5. [Rate Limiting](#5-rate-limiting)
6. [Exemples de flux](#6-exemples-de-flux)

---

## 1. Configuration de base

### URL de base

```
Production: https://lehiboo.com/wp-json/lehiboo/v2/
```

### Headers requis

| Header | Valeur | Obligatoire |
|--------|--------|-------------|
| `Content-Type` | `application/json` | Oui (POST/PUT) |
| `Accept` | `application/json` | Recommandé |
| `Authorization` | `Bearer {access_token}` | Endpoints authentifiés |
| `User-Agent` | `LeHiboo-Mobile/1.0 (iOS/Android)` | Recommandé |

### Format des réponses

**Succès (200/201)**
```json
{
  "success": true,
  "data": {
    // Données de la réponse
  }
}
```

**Succès paginé**
```json
{
  "success": true,
  "data": {
    "events": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total_items": 25,
      "total_pages": 3,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

**Erreur (4xx/5xx)**
```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Description lisible",
    "details": {
      // Détails optionnels (validation, etc.)
    }
  }
}
```

---

## 2. Authentification JWT

### Principe

L'API utilise un système de **double token JWT** :

| Token | Durée | Usage |
|-------|-------|-------|
| **Access Token** | 7 jours | Autoriser les requêtes API |
| **Refresh Token** | 30 jours | Obtenir un nouveau access token |

### Structure des tokens

**Access Token (payload)**
```json
{
  "iss": "https://lehiboo.com",
  "iat": 1733724000,
  "exp": 1734328800,
  "uid": 123,
  "email": "user@example.com",
  "role": "subscriber",
  "type": "access"
}
```

**Refresh Token (payload)**
```json
{
  "iss": "https://lehiboo.com",
  "iat": 1733724000,
  "exp": 1736316800,
  "uid": 123,
  "type": "refresh",
  "jti": "uuid-unique"
}
```

### Rôles utilisateur

| Rôle | Description | Permissions |
|------|-------------|-------------|
| `subscriber` | Client standard | Réservations, favoris, profil |
| `el_event_manager` | Partenaire/Organisateur | + Scanner billets, stats événements |
| `administrator` | Admin | Accès complet |

### Stockage recommandé (mobile)

```
iOS: Keychain
Android: EncryptedSharedPreferences / Keystore
Flutter: flutter_secure_storage
```

### Gestion de l'expiration

```
1. Vérifier exp du access_token avant chaque requête
2. Si expiré (ou < 5 min restantes) → appeler /auth/refresh
3. Si refresh_token expiré → rediriger vers login
4. Stocker les nouveaux tokens
```

---

## 3. Endpoints API

### 3.1 Authentification (Public)

Ces endpoints ne nécessitent PAS de token.

---

#### POST `/auth/register`

Inscription d'un nouvel utilisateur.

**Body**
```json
{
  "email": "user@example.com",
  "password": "SecurePass123",
  "first_name": "Jean",
  "last_name": "Dupont",
  "phone": "+33612345678"  // Optionnel, format E.164
}
```

**Validation mot de passe**
- Minimum 8 caractères
- Au moins 1 majuscule
- Au moins 1 chiffre

**Réponse (201)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email": "user@example.com",
      "display_name": "Jean Dupont",
      "first_name": "Jean",
      "last_name": "Dupont",
      "role": "subscriber",
      "capabilities": {
        "can_book": true,
        "can_scan_tickets": false
      },
      "created_at": "2025-01-15T10:30:00Z"
    },
    "tokens": {
      "access_token": "eyJhbGciOiJIUzI1NiIs...",
      "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
      "token_type": "Bearer",
      "expires_in": 604800
    }
  }
}
```

**Erreurs possibles**
| Code | Description |
|------|-------------|
| `validation_error` | Email/password invalide |
| `email_exists` | Email déjà utilisé |
| `weak_password` | Mot de passe trop faible |

---

#### POST `/auth/login`

Connexion utilisateur.

**Body**
```json
{
  "email": "user@example.com",
  "password": "SecurePass123"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email": "user@example.com",
      "display_name": "Jean Dupont",
      "avatar_url": "https://...",
      "role": "subscriber",
      "capabilities": {
        "can_book": true,
        "can_scan_tickets": false
      }
    },
    "tokens": {
      "access_token": "eyJhbGciOiJIUzI1NiIs...",
      "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
      "token_type": "Bearer",
      "expires_in": 604800
    }
  }
}
```

**Réponse partenaire** (rôle `el_event_manager`)
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "tokens": { ... },
    "partner_info": {
      "total_events": 5,
      "active_events": 2,
      "total_scans_today": 47
    }
  }
}
```

**Erreurs possibles**
| Code | Description |
|------|-------------|
| `invalid_credentials` | Email ou mot de passe incorrect |
| `account_disabled` | Compte désactivé |

---

#### POST `/auth/refresh`

Rafraîchir le access token.

**Body**
```json
{
  "refresh_token": "eyJhbGciOiJIUzI1NiIs..."
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "token_type": "Bearer",
    "expires_in": 604800
  }
}
```

> **Note**: Un nouveau refresh_token est généré. L'ancien est révoqué.

**Erreurs possibles**
| Code | Description |
|------|-------------|
| `missing_token` | Refresh token manquant |
| `token_expired` | Refresh token expiré |
| `token_invalid` | Token invalide ou corrompu |
| `token_revoked` | Token révoqué (déconnexion) |

---

#### POST `/auth/forgot-password`

Demander un email de réinitialisation.

**Body**
```json
{
  "email": "user@example.com"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Si un compte existe avec cet email, un lien de réinitialisation a été envoyé."
  }
}
```

> **Sécurité**: Même réponse que le compte existe ou non (protection contre l'énumération d'emails)

---

#### POST `/auth/reset-password`

Réinitialiser le mot de passe avec la clé reçue par email.

**Body**
```json
{
  "email": "user@example.com",
  "key": "clé_reçue_par_email",
  "new_password": "NouveauMotDePasse123"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Mot de passe réinitialisé avec succès"
  }
}
```

> **Note**: Tous les refresh tokens de l'utilisateur sont révoqués après réinitialisation.

---

#### POST `/auth/logout`

Déconnexion (révoque tous les tokens).

**Headers**
```
Authorization: Bearer {access_token}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Déconnexion réussie"
  }
}
```

> **Note**: TOUS les refresh tokens de l'utilisateur sont révoqués (déconnexion de tous les appareils).

---

### 3.2 Événements (Public)

Ces endpoints ne nécessitent PAS de token.

---

#### GET `/events`

Liste des événements avec filtres et pagination.

**Paramètres query**
| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page (défaut: 1) |
| `per_page` | int | Résultats par page (défaut: 10, max: 50) |
| `date_from` | string | Date début (YYYY-MM-DD) |
| `date_to` | string | Date fin (YYYY-MM-DD) |
| `city` | string | Filtrer par ville |
| `category` | int | ID catégorie |
| `price_min` | float | Prix minimum |
| `price_max` | float | Prix maximum |
| `free_only` | bool | Événements gratuits uniquement |
| `indoor` | bool | Événements en intérieur |
| `outdoor` | bool | Événements en extérieur |
| `family_friendly` | bool | Adapté aux familles |
| `age_min` | int | Âge minimum |
| `search` | string | Recherche textuelle |
| `include_past` | bool | Inclure événements passés |

**Exemple**
```
GET /events?city=Paris&price_max=50&page=1&per_page=20
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 42,
        "title": "Concert Jazz au Sunset",
        "slug": "concert-jazz-sunset",
        "excerpt": "Une soirée jazz exceptionnelle...",
        "thumbnail": {
          "url": "https://...",
          "width": 800,
          "height": 600
        },
        "date_start": "2025-02-15T20:00:00",
        "date_end": "2025-02-15T23:00:00",
        "location": {
          "venue": "Le Sunset",
          "city": "Paris",
          "address": "60 Rue des Lombards"
        },
        "pricing": {
          "min_price": 25.00,
          "max_price": 45.00,
          "currency": "EUR",
          "is_free": false
        },
        "availability": {
          "total_capacity": 200,
          "spots_remaining": 47,
          "status": "available"
        },
        "categories": [
          { "id": 5, "name": "Musique", "slug": "musique" }
        ],
        "tags": ["jazz", "concert", "live"]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_items": 156,
      "total_pages": 8,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

---

#### GET `/events/{id}`

Détail complet d'un événement.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "title": "Concert Jazz au Sunset",
    "slug": "concert-jazz-sunset",
    "content": "<p>Description complète HTML...</p>",
    "excerpt": "Une soirée jazz exceptionnelle...",
    "thumbnail": {
      "url": "https://...",
      "width": 800,
      "height": 600
    },
    "gallery": [
      { "url": "https://...", "width": 1200, "height": 800 }
    ],
    "date_start": "2025-02-15T20:00:00",
    "date_end": "2025-02-15T23:00:00",
    "location": {
      "venue": "Le Sunset",
      "city": "Paris",
      "address": "60 Rue des Lombards",
      "latitude": 48.8588,
      "longitude": 2.3488
    },
    "organizer": {
      "id": 15,
      "name": "Jazz Productions",
      "avatar_url": "https://..."
    },
    "pricing": {
      "min_price": 25.00,
      "max_price": 45.00,
      "currency": "EUR",
      "is_free": false
    },
    "ticket_types": [
      {
        "id": 101,
        "name": "Place standard",
        "price": 25.00,
        "description": "Accès concert",
        "available": 30,
        "max_per_order": 10
      },
      {
        "id": 102,
        "name": "Place VIP",
        "price": 45.00,
        "description": "Accès concert + backstage",
        "available": 17,
        "max_per_order": 4
      }
    ],
    "availability": {
      "total_capacity": 200,
      "spots_remaining": 47,
      "status": "available"
    },
    "attributes": {
      "indoor": true,
      "outdoor": false,
      "family_friendly": false,
      "age_min": 18,
      "age_max": null
    },
    "categories": [
      { "id": 5, "name": "Musique", "slug": "musique" }
    ],
    "thematiques": [
      { "id": 12, "name": "Jazz", "slug": "jazz" }
    ],
    "meta": {
      "view_count": 1250,
      "favorites_count": 89
    }
  }
}
```

---

### 3.3 Catégories et Filtres (Public)

---

#### GET `/categories`

Liste des catégories d'événements.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `include_count` | bool | Inclure le nombre d'événements |
| `parent_only` | bool | Catégories parentes uniquement |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 5,
        "name": "Musique",
        "slug": "musique",
        "description": "Concerts et festivals",
        "image_url": "https://...",
        "event_count": 45,
        "parent_id": null,
        "children": [
          { "id": 12, "name": "Jazz", "slug": "jazz", "event_count": 12 }
        ]
      }
    ]
  }
}
```

---

#### GET `/thematiques`

Liste des thématiques.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "thematiques": [
      { "id": 1, "name": "Jazz", "slug": "jazz" },
      { "id": 2, "name": "Rock", "slug": "rock" }
    ]
  }
}
```

---

#### GET `/cities`

Liste des villes ayant des événements.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "cities": [
      { "name": "Paris", "event_count": 120 },
      { "name": "Lyon", "event_count": 45 },
      { "name": "Marseille", "event_count": 38 }
    ]
  }
}
```

---

#### GET `/filters`

Options de filtrage disponibles.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "price_range": { "min": 0, "max": 250 },
    "date_range": {
      "earliest": "2025-01-01",
      "latest": "2025-12-31"
    },
    "categories": [...],
    "cities": [...],
    "attributes": {
      "indoor": true,
      "outdoor": true,
      "family_friendly": true
    }
  }
}
```

---

### 3.4 Blog/Articles (Public)

---

#### GET `/posts`

Liste des articles du blog.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page |
| `per_page` | int | Résultats par page |
| `category` | int | ID catégorie blog |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "posts": [
      {
        "id": 101,
        "title": "Les meilleurs événements de l'été",
        "excerpt": "Découvrez notre sélection...",
        "thumbnail": "https://...",
        "author": "Admin",
        "published_at": "2025-01-10T14:00:00Z",
        "categories": [{ "id": 3, "name": "Actualités" }]
      }
    ],
    "pagination": { ... }
  }
}
```

---

#### GET `/posts/{id}`

Détail d'un article.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "id": 101,
    "title": "Les meilleurs événements de l'été",
    "content": "<p>Contenu HTML complet...</p>",
    "thumbnail": "https://...",
    "author": "Admin",
    "published_at": "2025-01-10T14:00:00Z",
    "categories": [...]
  }
}
```

---

### 3.5 Profil Utilisateur (Authentifié)

**Header requis**: `Authorization: Bearer {access_token}`

---

#### GET `/me`

Récupérer le profil de l'utilisateur connecté.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "email": "user@example.com",
    "display_name": "Jean Dupont",
    "first_name": "Jean",
    "last_name": "Dupont",
    "phone": "+33612345678",
    "bio": "Amateur de jazz...",
    "avatar_url": "https://...",
    "role": "subscriber",
    "capabilities": {
      "can_book": true,
      "can_scan_tickets": false
    },
    "stats": {
      "total_bookings": 12,
      "upcoming_events": 3,
      "favorites_count": 25
    },
    "notification_preferences": {
      "email_booking": true,
      "email_reminder": true,
      "push_enabled": true
    },
    "created_at": "2024-06-15T10:30:00Z"
  }
}
```

---

#### PUT `/me`

Modifier le profil.

**Body**
```json
{
  "first_name": "Jean-Pierre",
  "last_name": "Dupont",
  "phone": "+33612345678",
  "bio": "Passionné de musique live"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    // Profil mis à jour
  }
}
```

---

#### PUT `/me/password`

Changer le mot de passe.

**Body**
```json
{
  "current_password": "AncienMotDePasse123",
  "new_password": "NouveauMotDePasse456"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Mot de passe modifié avec succès"
  }
}
```

---

#### DELETE `/me`

Supprimer le compte (GDPR).

**Body**
```json
{
  "password": "MotDePasse123"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Compte supprimé avec succès"
  }
}
```

---

#### POST `/me/avatar`

Changer l'avatar (upload fichier).

**Headers**
```
Content-Type: multipart/form-data
```

**Body (form-data)**
```
avatar: [fichier image]
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "avatar_url": "https://..."
  }
}
```

---

#### POST `/me/devices`

Enregistrer un appareil pour les notifications push.

**Body**
```json
{
  "device_token": "fcm_token_or_apns_token",
  "device_name": "iPhone 15 Pro",
  "platform": "ios"
}
```

**Réponse (201)**
```json
{
  "success": true,
  "data": {
    "device_id": 456,
    "message": "Appareil enregistré"
  }
}
```

---

#### DELETE `/me/devices/{token}`

Retirer un appareil des notifications.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Appareil retiré"
  }
}
```

---

#### GET `/me/notifications`

Préférences de notifications.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "email_booking_confirmation": true,
    "email_event_reminder": true,
    "email_newsletter": false,
    "push_booking_confirmation": true,
    "push_event_reminder": true,
    "push_promotions": false
  }
}
```

---

#### PUT `/me/notifications`

Modifier les préférences.

**Body**
```json
{
  "email_newsletter": true,
  "push_promotions": true
}
```

---

### 3.6 Favoris (Authentifié)

**Header requis**: `Authorization: Bearer {access_token}`

---

#### GET `/me/favorites`

Liste des événements favoris.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "favorites": [
      {
        "id": 42,
        "title": "Concert Jazz au Sunset",
        "thumbnail": "https://...",
        "date_start": "2025-02-15T20:00:00",
        "location": { "city": "Paris" },
        "pricing": { "min_price": 25.00 },
        "added_at": "2025-01-10T14:30:00Z"
      }
    ]
  }
}
```

---

#### POST `/me/favorites`

Ajouter un événement aux favoris.

**Body**
```json
{
  "event_id": 42
}
```

**Réponse (201)**
```json
{
  "success": true,
  "data": {
    "message": "Ajouté aux favoris",
    "is_favorited": true
  }
}
```

---

#### GET `/me/favorites/{event_id}`

Vérifier si un événement est en favori.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "is_favorited": true
  }
}
```

---

#### DELETE `/me/favorites/{event_id}`

Retirer des favoris.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "message": "Retiré des favoris",
    "is_favorited": false
  }
}
```

---

#### POST `/me/favorites/{event_id}/toggle`

Basculer l'état favori.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "is_favorited": true,
    "message": "Ajouté aux favoris"
  }
}
```

---

### 3.7 Réservations (Authentifié)

**Header requis**: `Authorization: Bearer {access_token}`

---

#### POST `/bookings`

Créer une nouvelle réservation.

**Body**
```json
{
  "event_id": 42,
  "tickets": [
    { "ticket_type_id": 101, "quantity": 2 },
    { "ticket_type_id": 102, "quantity": 1 }
  ],
  "buyer_info": {
    "email": "user@example.com",
    "first_name": "Jean",
    "last_name": "Dupont",
    "phone": "+33612345678"
  },
  "coupon_code": "SUMMER20",
  "notes": "Places côte à côte si possible"
}
```

**Réponse (201)**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1001,
      "reference": "LH-2025-001001",
      "status": "pending_payment",
      "event": {
        "id": 42,
        "title": "Concert Jazz au Sunset",
        "date_start": "2025-02-15T20:00:00"
      },
      "tickets": [
        {
          "ticket_type": "Place standard",
          "quantity": 2,
          "unit_price": 25.00,
          "subtotal": 50.00
        },
        {
          "ticket_type": "Place VIP",
          "quantity": 1,
          "unit_price": 45.00,
          "subtotal": 45.00
        }
      ],
      "created_at": "2025-01-15T10:30:00Z",
      "expires_at": "2025-01-15T10:45:00Z"
    },
    "pricing": {
      "subtotal": 95.00,
      "discount": -19.00,
      "discount_code": "SUMMER20",
      "fees": 5.50,
      "total": 81.50,
      "currency": "EUR"
    },
    "payment_methods": ["stripe", "paypal"]
  }
}
```

**Erreurs possibles**
| Code | Description |
|------|-------------|
| `insufficient_spots` | Places insuffisantes |
| `event_not_found` | Événement introuvable |
| `invalid_coupon` | Code promo invalide |
| `booking_failed` | Erreur création |

---

#### POST `/bookings/{id}/confirm`

Confirmer le paiement d'une réservation.

**Body**
```json
{
  "payment_method": "stripe",
  "payment_intent_id": "pi_xxx"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1001,
      "reference": "LH-2025-001001",
      "status": "confirmed",
      "tickets_generated": 3
    },
    "message": "Réservation confirmée. Vos billets sont disponibles."
  }
}
```

---

#### GET `/me/bookings`

Liste des réservations de l'utilisateur.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `status` | string | `pending`, `confirmed`, `cancelled` |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "bookings": [
      {
        "id": 1001,
        "reference": "LH-2025-001001",
        "status": "confirmed",
        "event": {
          "id": 42,
          "title": "Concert Jazz au Sunset",
          "thumbnail": "https://...",
          "date_start": "2025-02-15T20:00:00",
          "location": { "venue": "Le Sunset", "city": "Paris" }
        },
        "total": 81.50,
        "currency": "EUR",
        "tickets_count": 3,
        "created_at": "2025-01-15T10:30:00Z"
      }
    ]
  }
}
```

---

#### GET `/me/bookings/{id}`

Détail d'une réservation.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "id": 1001,
    "reference": "LH-2025-001001",
    "status": "confirmed",
    "event": { ... },
    "tickets": [
      {
        "id": 2001,
        "ticket_type": "Place standard",
        "price": 25.00,
        "qr_code": "LH-TKT-2001-XXXXX",
        "status": "valid"
      }
    ],
    "buyer_info": {
      "email": "user@example.com",
      "first_name": "Jean",
      "last_name": "Dupont"
    },
    "pricing": {
      "subtotal": 95.00,
      "discount": -19.00,
      "fees": 5.50,
      "total": 81.50
    },
    "payment": {
      "method": "stripe",
      "paid_at": "2025-01-15T10:32:00Z"
    },
    "created_at": "2025-01-15T10:30:00Z"
  }
}
```

---

#### POST `/me/bookings/{id}/cancel`

Annuler une réservation.

**Body**
```json
{
  "reason": "Changement de plan"
}
```

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1001,
      "status": "cancelled",
      "cancelled_at": "2025-01-16T09:00:00Z"
    },
    "refund": {
      "eligible": true,
      "amount": 81.50,
      "status": "processing"
    }
  }
}
```

---

### 3.8 Billets (Authentifié)

**Header requis**: `Authorization: Bearer {access_token}`

---

#### GET `/me/tickets`

Liste des billets de l'utilisateur.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `status` | string | `all`, `valid`, `used`, `cancelled` |
| `upcoming` | bool | Événements à venir uniquement |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": 2001,
        "qr_code": "LH-TKT-2001-XXXXX",
        "ticket_type": "Place standard",
        "status": "valid",
        "event": {
          "id": 42,
          "title": "Concert Jazz au Sunset",
          "thumbnail": "https://...",
          "date_start": "2025-02-15T20:00:00",
          "location": { "venue": "Le Sunset", "city": "Paris" }
        },
        "booking_reference": "LH-2025-001001"
      }
    ]
  }
}
```

---

#### GET `/me/tickets/{id}`

Détail d'un billet.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "id": 2001,
    "qr_code": "LH-TKT-2001-XXXXX",
    "qr_code_image": "data:image/png;base64,...",
    "ticket_type": "Place standard",
    "price": 25.00,
    "status": "valid",
    "event": {
      "id": 42,
      "title": "Concert Jazz au Sunset",
      "date_start": "2025-02-15T20:00:00",
      "date_end": "2025-02-15T23:00:00",
      "location": {
        "venue": "Le Sunset",
        "address": "60 Rue des Lombards",
        "city": "Paris"
      }
    },
    "holder": {
      "name": "Jean Dupont",
      "email": "user@example.com"
    },
    "booking": {
      "id": 1001,
      "reference": "LH-2025-001001"
    },
    "scanned_at": null
  }
}
```

---

#### GET `/me/tickets/{id}/download`

Télécharger le billet en PDF.

**Headers de réponse**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="ticket-LH-TKT-2001.pdf"
```

**Réponse**: Fichier PDF binaire

---

### 3.9 Partenaire - Scanner (Authentifié Partenaire)

**Rôle requis**: `el_event_manager` ou `administrator`

**Header requis**: `Authorization: Bearer {access_token}`

---

#### POST `/partner/scan`

Scanner un QR code de billet.

**Body**
```json
{
  "qr_code": "LH-TKT-2001-XXXXX",
  "event_id": 42
}
```

**Réponse succès (200)**
```json
{
  "success": true,
  "data": {
    "status": "valid",
    "message": "Billet validé avec succès",
    "ticket": {
      "id": 2001,
      "ticket_type": "Place standard",
      "holder_name": "Jean Dupont"
    },
    "event": {
      "id": 42,
      "title": "Concert Jazz au Sunset"
    },
    "scan_info": {
      "scanned_at": "2025-02-15T19:45:00Z",
      "scanned_by": "Organisateur"
    }
  }
}
```

**Réponse déjà utilisé (200)**
```json
{
  "success": true,
  "data": {
    "status": "already_used",
    "message": "Ce billet a déjà été scanné",
    "ticket": { ... },
    "original_scan": {
      "scanned_at": "2025-02-15T19:30:00Z"
    }
  }
}
```

**Erreurs possibles**
| Code | Description |
|------|-------------|
| `ticket_not_found` | QR code invalide |
| `ticket_cancelled` | Billet annulé |
| `event_mismatch` | Billet pour un autre événement |
| `insufficient_permissions` | Pas autorisé pour cet événement |

---

#### GET `/partner/tickets/{code}/validate`

Valider un billet sans le scanner (vérification).

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "ticket": {
      "id": 2001,
      "ticket_type": "Place standard",
      "status": "valid",
      "holder_name": "Jean Dupont"
    },
    "event": {
      "id": 42,
      "title": "Concert Jazz au Sunset"
    }
  }
}
```

---

#### GET `/partner/events`

Liste des événements du partenaire.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `status` | string | `upcoming`, `past`, `all` |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 42,
        "title": "Concert Jazz au Sunset",
        "date_start": "2025-02-15T20:00:00",
        "location": { "venue": "Le Sunset", "city": "Paris" },
        "stats": {
          "total_tickets": 200,
          "sold_tickets": 153,
          "scanned_tickets": 0
        }
      }
    ]
  }
}
```

---

#### GET `/partner/events/{id}/stats`

Statistiques détaillées d'un événement.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "event": {
      "id": 42,
      "title": "Concert Jazz au Sunset"
    },
    "sales": {
      "total_tickets_sold": 153,
      "total_revenue": 4250.00,
      "currency": "EUR",
      "by_ticket_type": [
        { "name": "Place standard", "sold": 120, "revenue": 3000.00 },
        { "name": "Place VIP", "sold": 33, "revenue": 1485.00 }
      ]
    },
    "attendance": {
      "total_capacity": 200,
      "tickets_sold": 153,
      "tickets_scanned": 89,
      "check_in_rate": "58.17%"
    },
    "scans_timeline": [
      { "hour": "19:00", "count": 12 },
      { "hour": "19:30", "count": 45 },
      { "hour": "20:00", "count": 32 }
    ]
  }
}
```

---

#### GET `/partner/events/{id}/attendees`

Liste des participants (billets scannés).

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page |
| `per_page` | int | Résultats par page |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "attendees": [
      {
        "ticket_id": 2001,
        "holder_name": "Jean Dupont",
        "ticket_type": "Place standard",
        "scanned_at": "2025-02-15T19:45:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

#### GET `/partner/dashboard`

Tableau de bord partenaire.

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_events": 5,
      "active_events": 2,
      "total_tickets_sold": 850,
      "total_revenue": 25000.00,
      "currency": "EUR"
    },
    "today": {
      "events_count": 1,
      "expected_attendees": 153,
      "scanned_today": 47
    },
    "upcoming_events": [
      {
        "id": 42,
        "title": "Concert Jazz au Sunset",
        "date_start": "2025-02-15T20:00:00",
        "tickets_sold": 153
      }
    ]
  }
}
```

---

#### GET `/partner/scans`

Historique des scans.

**Paramètres**
| Param | Type | Description |
|-------|------|-------------|
| `event_id` | int | Filtrer par événement |
| `page` | int | Page |
| `per_page` | int | Résultats par page |

**Réponse (200)**
```json
{
  "success": true,
  "data": {
    "scans": [
      {
        "id": 5001,
        "ticket_id": 2001,
        "holder_name": "Jean Dupont",
        "ticket_type": "Place standard",
        "event": {
          "id": 42,
          "title": "Concert Jazz au Sunset"
        },
        "scanned_at": "2025-02-15T19:45:00Z",
        "status": "success"
      }
    ],
    "pagination": { ... }
  }
}
```

---

## 4. Codes d'erreur

### Erreurs HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès interdit |
| 404 | Ressource introuvable |
| 429 | Trop de requêtes |
| 500 | Erreur serveur |

### Codes d'erreur API

| Code | Description | Action recommandée |
|------|-------------|-------------------|
| `validation_error` | Données invalides | Vérifier les champs |
| `missing_params` | Paramètres manquants | Ajouter les params requis |
| `weak_password` | Mot de passe trop faible | Min 8 chars, 1 maj, 1 chiffre |
| `email_exists` | Email déjà utilisé | Utiliser un autre email |
| `invalid_credentials` | Login incorrect | Vérifier email/password |
| `no_token` | Token manquant | Ajouter Authorization header |
| `token_expired` | Token expiré | Appeler /auth/refresh |
| `token_invalid` | Token invalide | Se reconnecter |
| `token_revoked` | Token révoqué | Se reconnecter |
| `user_not_found` | Utilisateur introuvable | Se reconnecter |
| `account_disabled` | Compte désactivé | Contacter support |
| `insufficient_permissions` | Accès refusé | Vérifier le rôle |
| `not_found` | Ressource introuvable | Vérifier l'ID |
| `insufficient_spots` | Plus de places | Réduire la quantité |
| `ticket_not_found` | QR invalide | Vérifier le code |
| `rate_limit_exceeded` | Trop de requêtes | Attendre retry_after |

---

## 5. Rate Limiting

### Limites par endpoint

| Endpoint | Limite | Fenêtre |
|----------|--------|---------|
| `auth/register` | 5 requêtes | 1 heure |
| `auth/login` | 10 requêtes | 15 minutes |
| `auth/forgot-password` | 3 requêtes | 1 heure |
| `partner/scan` | 60 requêtes | 1 minute |
| `bookings` | 10 requêtes | 1 minute |
| Autres endpoints | 60 requêtes | 1 minute |

### Headers de réponse

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1733724060
```

### Réponse 429 (limite dépassée)

```json
{
  "success": false,
  "error": {
    "code": "rate_limit_exceeded",
    "message": "Trop de requêtes. Réessayez dans 45 secondes.",
    "details": {
      "retry_after": 45
    }
  }
}
```

**Header**: `Retry-After: 45`

---

## 6. Exemples de flux

### Flux d'authentification complet

```
1. INSCRIPTION
   POST /auth/register
   Body: { email, password, first_name, last_name }
   → Stocker access_token + refresh_token

2. CONNEXION (si déjà inscrit)
   POST /auth/login
   Body: { email, password }
   → Stocker access_token + refresh_token

3. REQUÊTES AUTHENTIFIÉES
   GET /me/bookings
   Header: Authorization: Bearer {access_token}

4. REFRESH TOKEN (avant expiration)
   POST /auth/refresh
   Body: { refresh_token }
   → Remplacer les tokens stockés

5. DÉCONNEXION
   POST /auth/logout
   Header: Authorization: Bearer {access_token}
   → Supprimer les tokens stockés
```

### Flux de réservation complet

```
1. PARCOURIR LES ÉVÉNEMENTS
   GET /events?city=Paris

2. VOIR DÉTAIL ÉVÉNEMENT
   GET /events/42
   → Récupérer ticket_types disponibles

3. AJOUTER AUX FAVORIS (optionnel)
   POST /me/favorites
   Body: { event_id: 42 }

4. CRÉER RÉSERVATION
   POST /bookings
   Body: {
     event_id: 42,
     tickets: [{ ticket_type_id: 101, quantity: 2 }],
     buyer_info: { email, first_name, last_name }
   }
   → Obtenir booking_id et pricing

5. PAYER ET CONFIRMER
   POST /bookings/{id}/confirm
   Body: { payment_method: "stripe", payment_intent_id: "pi_xxx" }

6. RÉCUPÉRER MES BILLETS
   GET /me/tickets

7. TÉLÉCHARGER PDF
   GET /me/tickets/{id}/download
```

### Flux Scanner (Partenaire)

```
1. CONNEXION PARTENAIRE
   POST /auth/login
   → Vérifier role = "el_event_manager"

2. VOIR MES ÉVÉNEMENTS
   GET /partner/events?status=upcoming

3. SCANNER UN BILLET
   POST /partner/scan
   Body: { qr_code: "LH-TKT-2001-XXXXX", event_id: 42 }
   → status: "valid" | "already_used" | erreur

4. VOIR STATISTIQUES
   GET /partner/events/42/stats

5. VOIR HISTORIQUE SCANS
   GET /partner/scans?event_id=42
```

---

## Annexes

### A. Validation des données

| Champ | Règles |
|-------|--------|
| `email` | Format email valide |
| `password` | Min 8 chars, 1 majuscule, 1 chiffre |
| `phone` | Format E.164 (ex: +33612345678) |
| `first_name` | 2-50 caractères |
| `last_name` | 2-50 caractères |

### B. Formats de date

Toutes les dates sont en **ISO 8601** :
- `2025-02-15T20:00:00` (local)
- `2025-02-15T20:00:00Z` (UTC)

### C. Devises

Format prix : nombre décimal avec 2 décimales
- `"price": 25.00`
- `"currency": "EUR"`

### D. Statuts de réservation

| Statut | Description |
|--------|-------------|
| `pending_payment` | En attente de paiement |
| `confirmed` | Payé et confirmé |
| `cancelled` | Annulé |
| `refunded` | Remboursé |

### E. Statuts de billet

| Statut | Description |
|--------|-------------|
| `valid` | Utilisable |
| `used` | Déjà scanné |
| `cancelled` | Annulé |
| `expired` | Événement passé |

---

## Support

Pour toute question technique sur l'API :
- Email : support@lehiboo.com
- Documentation : Cette page

---

*Documentation générée pour LeHiboo Mobile API v2.0.0*
