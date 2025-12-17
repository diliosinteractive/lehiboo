# API Hibons - Documentation Mobile

## Vue d'ensemble

Le système Hibons est un système de gamification avec monnaie virtuelle pour l'application LeHiboo.

**Base URL**: `/hibons` (ou `/api-planner/hibons` via proxy)

**Authentification**: Header `X-API-Key` requis sur tous les endpoints

**Paramètre commun**: `userId` (query param ou body) requis sur la plupart des endpoints

---

## Conversion

```
100 Hibons = 1 EUR
```

---

## Endpoints

### 1. Wallet (Portefeuille)

#### GET `/hibons/wallet?userId=123`
Obtenir le wallet complet de l'utilisateur.

**Response:**
```json
{
  "success": true,
  "wallet": {
    "balance": 1250,
    "pending": 0,
    "level": 3,
    "title": "Hibou Aventurier",
    "xp": 450,
    "xpProgress": {
      "current": 450,
      "needed": 600,
      "remaining": 150,
      "progress": 50
    },
    "streak": {
      "current": 5,
      "longest": 12,
      "shieldActive": false,
      "shieldUntil": null
    },
    "multiplier": {
      "value": 1.0,
      "active": false,
      "expiresAt": null
    },
    "referral": {
      "code": "ABC12345",
      "referredBy": null
    },
    "stats": {
      "lifetimeEarned": 3200,
      "lifetimeSpent": 1950,
      "achievements": 8,
      "activeChallenges": 3
    }
  }
}
```

#### GET `/hibons/balance?userId=123`
Obtenir uniquement le solde.

**Response:**
```json
{
  "success": true,
  "balance": 1250,
  "pending": 0,
  "lifetimeEarned": 3200,
  "lifetimeSpent": 1950
}
```

---

### 2. Daily Rewards (Récompenses quotidiennes)

#### GET `/hibons/daily-reward?userId=123`
Obtenir l'état du daily reward.

**Response:**
```json
{
  "success": true,
  "canClaim": true,
  "currentDay": 3,
  "lastClaimDate": "2025-12-16",
  "consecutiveWeeks": 1,
  "weeklyMultiplier": 1.1,
  "nextReward": {
    "day": 3,
    "hibons": 25,
    "xp": 10,
    "bonus": null,
    "adjustedHibons": 28
  },
  "sequence": [
    { "day": 1, "hibons": 10, "completed": true, "current": false },
    { "day": 2, "hibons": 15, "completed": true, "current": false },
    { "day": 3, "hibons": 25, "completed": false, "current": true },
    { "day": 4, "hibons": 35, "bonus": { "type": "multiplier", "label": "x1.2 pendant 1h" } },
    { "day": 5, "hibons": 50 },
    { "day": 6, "hibons": 75, "bonus": { "type": "wheel_spin", "label": "Tour de roue gratuit" } },
    { "day": 7, "hibons": 100, "bonus": { "type": "mystery_chest", "label": "Coffre mystère" } }
  ]
}
```

#### POST `/hibons/daily-reward/claim`
Réclamer le daily reward.

**Body:**
```json
{ "userId": 123 }
```

**Response:**
```json
{
  "success": true,
  "day": 3,
  "hibons": 28,
  "xp": 10,
  "bonus": null,
  "consecutiveWeeks": 1,
  "weeklyMultiplier": 1.1,
  "newBalance": 1278,
  "levelUp": null,
  "nextClaimAt": "2025-12-18T00:00:00.000Z"
}
```

**Erreurs possibles:**
- `already_claimed`: Déjà réclamé aujourd'hui

---

### 3. Transactions

#### POST `/hibons/earn`
Créditer des Hibons pour une action utilisateur.

**Body:**
```json
{
  "userId": 123,
  "category": "VIEW_EVENT",
  "referenceType": "event",
  "referenceId": "456"
}
```

**Catégories disponibles:**
| Catégorie | Hibons | XP | Limite/jour |
|-----------|--------|-----|-------------|
| `DAILY_SEARCH` | 5 | 2 | 1 |
| `VIEW_EVENT` | 2 | 1 | 10 |
| `FAVORITE` | 5 | 2 | 5 |
| `SHARE` | 15 | 5 | 3 |
| `REVIEW` | 30 | 15 | 3 |
| `REVIEW_PHOTO` | 50 | 25 | 2 |
| `REVIEW_VIDEO` | 100 | 50 | 1 |
| `BOOKING` | 5% du prix | 25 | - |
| `CHECKIN` | 50 | 25 | - |
| `CHAT_FEEDBACK` | 10 | 5 | 5 |

**Response:**
```json
{
  "success": true,
  "transaction": {
    "id": "uuid",
    "amount": 2,
    "baseAmount": 2,
    "xpEarned": 1
  },
  "newBalance": 1280,
  "levelUp": null
}
```

**Erreurs possibles:**
- `daily_limit_reached`: Limite quotidienne atteinte

#### GET `/hibons/transactions?userId=123&limit=20&type=EARN`
Historique des transactions.

**Query params:**
- `limit` (default: 50)
- `offset` (default: 0)
- `type`: `EARN`, `SPEND`, `BONUS`
- `category`: Filtre par catégorie

#### GET `/hibons/limits?userId=123`
Obtenir les limites quotidiennes restantes.

---

### 4. Achievements (Badges)

#### GET `/hibons/achievements`
Liste de tous les achievements disponibles.

#### GET `/hibons/achievements/progress?userId=123`
Progression de l'utilisateur sur les achievements.

**Response:**
```json
{
  "success": true,
  "achievements": [
    {
      "id": "explorer_10",
      "name": "Explorateur",
      "description": "Consulte 10 événements",
      "icon": "compass",
      "category": "explorer",
      "rarity": "common",
      "rewards": { "hibons": 50, "xp": 25 },
      "progress": { "current": 7, "target": 10, "percentage": 70 },
      "status": "in_progress",
      "unlockedAt": null
    },
    {
      "id": "first_review",
      "name": "Critique en Herbe",
      "status": "unlocked",
      "unlockedAt": "2025-12-15T10:30:00Z"
    }
  ]
}
```

#### GET `/hibons/achievements/unclaimed?userId=123`
Achievements débloqués mais non réclamés.

#### POST `/hibons/achievements/:id/claim`
Réclamer la récompense d'un achievement.

**Body:**
```json
{ "userId": 123 }
```

---

### 5. Challenges

#### GET `/hibons/challenges?userId=123&type=daily`
Challenges actifs.

**Query params:**
- `type`: `daily`, `weekly`, `sponsored`

**Response:**
```json
{
  "success": true,
  "challenges": [
    {
      "id": "uuid",
      "name": "Explorateur du Jour",
      "description": "Effectue 3 recherches",
      "type": "daily",
      "condition": { "type": "searches", "target": 3 },
      "rewards": { "hibons": 30, "xp": 15 },
      "timing": {
        "startsAt": "2025-12-17T00:00:00Z",
        "endsAt": "2025-12-18T00:00:00Z",
        "timeRemaining": 43200000
      },
      "participation": {
        "joined": true,
        "progress": 1,
        "status": "active"
      }
    }
  ]
}
```

#### POST `/hibons/challenges/:id/join`
Rejoindre un challenge.

#### POST `/hibons/challenges/:id/claim`
Réclamer la récompense.

---

### 6. Leaderboard (Classement)

#### GET `/hibons/leaderboard?limit=100`
Classement global (par Hibons lifetime).

#### GET `/hibons/leaderboard/weekly?limit=100`
Classement hebdomadaire.

#### GET `/hibons/leaderboard/rank?userId=123`
Position de l'utilisateur.

**Response:**
```json
{
  "success": true,
  "rank": 42,
  "total": 1500,
  "percentile": 97,
  "lifetimeEarned": 3200,
  "surrounding": [
    { "rank": 40, "userId": 100, "lifetimeEarned": 3300 },
    { "rank": 41, "userId": 101, "lifetimeEarned": 3250 },
    { "rank": 42, "userId": 123, "lifetimeEarned": 3200, "isCurrentUser": true },
    { "rank": 43, "userId": 102, "lifetimeEarned": 3150 },
    { "rank": 44, "userId": 103, "lifetimeEarned": 3100 }
  ]
}
```

#### GET `/hibons/leaderboard/top`
Top 3 (pour affichage rapide).

---

### 7. Wheel (Roue de la Fortune)

#### GET `/hibons/wheel?userId=123`
État de la roue.

**Response:**
```json
{
  "success": true,
  "config": [
    { "index": 0, "label": "10 Hibons", "color": "#FF6B6B", "probability": 25 },
    { "index": 1, "label": "25 Hibons", "color": "#4ECDC4", "probability": 20 },
    { "index": 2, "label": "50 Hibons", "color": "#45B7D1", "probability": 15 },
    { "index": 3, "label": "100 Hibons", "color": "#96CEB4", "probability": 8 },
    { "index": 4, "label": "x1.5 (1h)", "color": "#FFEAA7", "probability": 12 },
    { "index": 5, "label": "x2 (30min)", "color": "#DDA0DD", "probability": 8 },
    { "index": 6, "label": "+50 XP", "color": "#F0E68C", "probability": 7 },
    { "index": 7, "label": "JACKPOT 500", "color": "#FFD700", "probability": 5 }
  ],
  "canSpin": true,
  "spinOptions": {
    "hasFreeSpinToday": true,
    "canPaySpin": true,
    "spinCost": 100,
    "minLevelForFree": 3
  },
  "userLevel": 3,
  "balance": 1280
}
```

#### POST `/hibons/wheel/spin`
Tourner la roue.

**Body:**
```json
{
  "userId": 123,
  "useFree": true
}
```

**Response:**
```json
{
  "success": true,
  "spinType": "free",
  "costHibons": 0,
  "result": {
    "segmentIndex": 2,
    "label": "50 Hibons",
    "color": "#45B7D1",
    "reward": {
      "type": "hibons",
      "value": 50,
      "newBalance": 1330
    }
  },
  "canSpinAgain": false
}
```

---

### 8. Streak (Série)

#### GET `/hibons/streak?userId=123`
Info streak actuel.

#### POST `/hibons/streak/shield`
Activer le streak shield (coût: 150 Hibons).

**Body:**
```json
{ "userId": 123 }
```

---

### 9. Referral (Parrainage)

#### GET `/hibons/referral?userId=123`
Mon code et stats de parrainage.

**Response:**
```json
{
  "success": true,
  "referral": {
    "code": "ABC12345",
    "totalReferrals": 3,
    "pending": 1,
    "qualified": 0,
    "rewarded": 2,
    "totalEarned": 1000
  }
}
```

#### POST `/hibons/referral/apply`
Appliquer un code parrain.

**Body:**
```json
{
  "userId": 123,
  "code": "XYZ98765"
}
```

**Récompenses:**
- Parrain: 500 Hibons
- Filleul: 200 Hibons

---

### 10. Shop (Boutique)

#### GET `/hibons/shop`
Catalogue des items.

**Response:**
```json
{
  "success": true,
  "items": [
    { "id": "discount_5", "name": "-5% sur réservation", "cost": 200, "type": "discount" },
    { "id": "discount_10", "name": "-10% sur réservation", "cost": 400, "type": "discount" },
    { "id": "discount_15", "name": "-15% sur réservation", "cost": 600, "type": "discount" },
    { "id": "discount_20", "name": "-20% sur réservation", "cost": 800, "type": "discount" },
    { "id": "chat_message_1", "name": "+1 message Petit Boo", "cost": 50, "type": "feature" },
    { "id": "chat_message_5", "name": "+5 messages Petit Boo", "cost": 200, "type": "feature" },
    { "id": "chat_unlimited_24h", "name": "Chat illimité 24h", "cost": 300, "type": "feature" },
    { "id": "streak_shield", "name": "Streak Shield (1 jour)", "cost": 150, "type": "boost" },
    { "id": "multiplier_1_5", "name": "Boost x1.5 (1h)", "cost": 100, "type": "boost" },
    { "id": "multiplier_2", "name": "Boost x2 (30min)", "cost": 150, "type": "boost" },
    { "id": "wheel_spin", "name": "Tour de roue", "cost": 100, "type": "feature" }
  ]
}
```

#### POST `/hibons/shop/purchase`
Acheter un item.

**Body:**
```json
{
  "userId": 123,
  "itemId": "discount_10"
}
```

---

## Niveaux

| Niveau | Titre | XP requis | Avantages |
|--------|-------|-----------|-----------|
| 1 | Hibou Curieux | 0 | - |
| 2 | Hibou Explorateur | 100 | Daily +10% |
| 3 | Hibou Aventurier | 300 | 1 spin gratuit/semaine |
| 4 | Hibou Connaisseur | 600 | Daily +20% |
| 5 | Hibou Expert | 1000 | Challenges exclusifs |
| 6 | Hibou VIP | 1500 | Daily +30% |
| 7 | Hibou Elite | 2200 | 1 spin gratuit/jour |
| 8 | Hibou Légendaire | 3000 | Daily +50% |
| 9 | Grand Hibou | 4000 | Badge custom |
| 10 | Maître Hibou | 5500 | Daily x2 |

---

## Codes d'erreur

| Code | Description |
|------|-------------|
| `already_claimed` | Daily reward déjà réclamé |
| `daily_limit_reached` | Limite quotidienne atteinte |
| `insufficient_balance` | Solde insuffisant |
| `not_unlocked` | Achievement pas encore débloqué |
| `already_joined` | Déjà inscrit au challenge |
| `challenge_full` | Challenge complet |
| `invalid_code` | Code parrainage invalide |
| `already_referred` | Utilisateur déjà parrainé |
| `self_referral` | Auto-parrainage interdit |

---

## Intégration recommandée

### Au lancement de l'app
1. `GET /hibons/wallet` - Charger le wallet complet
2. `GET /hibons/daily-reward` - Vérifier si daily disponible
3. `GET /hibons/achievements/unclaimed` - Badges à réclamer
4. `GET /hibons/challenges?type=daily` - Challenges du jour

### Actions à tracker
Appeler `POST /hibons/earn` pour ces événements:
- `VIEW_EVENT` : Quand l'utilisateur consulte un événement
- `FAVORITE` : Quand il ajoute un favori
- `SHARE` : Quand il partage un événement
- `DAILY_SEARCH` : À la première recherche du jour
- `CHAT_FEEDBACK` : Quand il donne un pouce haut à Petit Boo

### Flow booking
1. Créer la réservation normalement
2. Après confirmation: `POST /hibons/earn` avec `category: "BOOKING"` et `bookingAmount: <prix>`
3. Au check-in (scan QR): `POST /hibons/earn` avec `category: "CHECKIN"`

---

## Questions?

Contactez l'équipe backend pour toute question.
