# 🚀 ROADMAP REFONTE - Le Hiboo AI Assistant
## De l'expérience médiocre à l'excellence (inspiré ZENELIA)

**Date**: 2025-10-29
**Objectif**: Passer de 10+ messages à 2-3 messages pour obtenir des résultats pertinents
**Inspiration**: Architecture ZENELI (analyse complète dans `/temp`)

---

## 📊 DIAGNOSTIC: Pourquoi c'est "nul" actuellement

### Problèmes identifiés

1. **❌ Prompt trop faible**
   - Pas de règles strictes de collecte
   - Pas de phases claires
   - Redemande des infos déjà données
   - Aucune notion de complétude (3/6, 5/6...)

2. **❌ Pas de vraie persistance**
   - Seulement localStorage basique
   - Pas de backend conversations
   - Pas de tracking anonyme
   - Perte de contexte entre sessions

3. **❌ Pas d'outils de recherche**
   - L'IA ne peut PAS vraiment chercher d'événements
   - Pas d'accès à la base WordPress
   - Pas de filtrage par budget/dates/âge
   - Invente des résultats ou reste vague

4. **❌ Flow conversationnel lent**
   - 1 question = 1 message
   - 6 infos obligatoires = 6+ messages minimum
   - Utilisateur frustré et abandonne

5. **❌ Pas d'enforcement des contraintes**
   - Budget non respecté
   - Restrictions d'âge ignorées
   - Dates non vérifiées
   - Résultats non pertinents

### Métriques actuelles (estimées)

- **Messages avant résultats**: 10-15
- **Taux d'abandon**: >70%
- **Satisfaction**: 2/5
- **Conversions**: <5%

---

## 🎯 OBJECTIFS DE LA REFONTE

### Métriques cibles

- **Messages avant résultats**: 2-3
- **Taux d'abandon**: <20%
- **Satisfaction**: 4.5/5
- **Conversions**: >25%
- **Temps de réponse**: <2 secondes

### Expérience utilisateur cible

```
[Message 1]
User: Ouvre le chat
Hedwige: Bonjour ! Je suis Hedwige 🦉
         Pour qui ? Quel âge ? Quelle ville ? Quand ?

[Message 2]
User: Couple, 30 ans, Paris, ce weekend
Hedwige: Quel type d'activité et budget max ?

[Message 3]
User: Culture, 50€ max
Hedwige: [Affiche 5 événements culturels < 50€]

→ 3 messages total = SUCCÈS
```

---

## 📋 ROADMAP DÉTAILLÉE

### Phase 1: System Prompt v2.0 ✅ TERMINÉ

**Durée**: 1 jour
**Status**: ✅ Complété (2025-10-29)

**Livrables**:
- ✅ Nouveau prompt 620 lignes (`system-prompt-v2.md`)
- ✅ Règle 6/6 (groupe, âge, ville, dates, activité, budget)
- ✅ Collecte groupée (3-4 infos par message)
- ✅ Mémoire contextuelle stricte
- ✅ Phases claires (greeting → profiling → searching → presenting)
- ✅ Tools définis (collectUserProfile, searchEvents, getWeather, createItinerary)

**Fichiers**:
- `lehiboo-ai-backend/src/prompts/system-prompt-v2.md`

---

### Phase 2: Implémentation des Tools MCP

**Durée**: 3-4 jours
**Priorité**: 🔥 CRITIQUE
**Status**: ⏳ À faire

**Objectif**: Donner à Hedwige les capacités de vraiment chercher des événements

#### Tool 1: `collectUserProfile`

**Fichier**: `lehiboo-ai-backend/src/tools/collect-user-profile.js`

**Responsabilités**:
- Collecter et valider les infos utilisateur
- Calculer complétude (% sur 6 infos obligatoires)
- Retourner missing fields
- Ne jamais écraser, seulement ajouter

**Input Schema**:
```typescript
{
  groupType?: 'solo' | 'couple' | 'family' | 'friends',
  age?: number,
  location?: { city: string, radius?: number },
  dates?: { type: 'thisWeekend' | 'nextWeekend' | 'specific', specific?: string[] },
  activityType?: 'sport' | 'culture' | 'gastronomie' | 'nature' | 'detente',
  budgetMax?: number,
  childrenAges?: number[],
  preferences?: string[]
}
```

**Output Schema**:
```typescript
{
  success: boolean,
  completeness: number,  // 0-100
  updatedProfile: UserProfile,
  missingFields: string[],
  message: string
}
```

**Logique**:
```javascript
function calculateCompleteness(profile) {
  const required = ['groupType', 'age', 'location', 'dates', 'activityType', 'budgetMax'];
  const filled = required.filter(field => profile[field] !== undefined);
  return Math.round((filled.length / required.length) * 100);
}
```

#### Tool 2: `searchEvents` (LE PLUS IMPORTANT)

**Fichier**: `lehiboo-ai-backend/src/tools/search-events.js`

**Responsabilités**:
- Chercher dans WordPress events via API
- Filtrer par budget STRICT (maxPrice)
- Filtrer par dates disponibilité
- Filtrer par âge (restrictions 18+, enfants)
- Filtrer par localisation (ville + rayon)
- Calculer match score avec AI
- Retourner raisons du match

**Input Schema**:
```typescript
{
  userProfile: {
    groupType: string,
    age: number,
    location: { city: string, radius: number },
    dates: { type: string, specific?: string[] },
    activityType: string,
    budgetMax: number
  },
  filters?: {
    maxPrice: number,      // STRICT from budgetMax
    minPrice?: number,
    indoor?: boolean,
    timeOfDay?: 'morning' | 'afternoon' | 'evening',
    difficulty?: 'easy' | 'medium' | 'hard',
    tags?: string[]
  },
  intent: 'search' | 'compare' | 'recommend',
  limit?: number,
  sortBy?: 'relevance' | 'price' | 'rating' | 'distance'
}
```

**Output Schema**:
```typescript
{
  success: boolean,
  events: [
    {
      id: string,
      title: string,
      description: string,
      price: number,
      currency: 'EUR',
      location: { city: string, address: string, distance: number },
      dates: string[],
      duration: string,
      category: string,
      tags: string[],
      rating: number,
      reviews: number,
      imageUrl: string,
      bookingUrl: string,
      ageRestriction?: { min?: number, max?: number },
      groupSize?: { min: number, max: number },
      matchScore: number,  // 0-1 AI confidence
      matchReasons: string[]  // ["Prix dans budget", "Disponible ce weekend", ...]
    }
  ],
  totalFound: number,
  searchMetrics: {
    searchTime: number,
    filtersApplied: string[]
  },
  message: string
}
```

**API WordPress à utiliser**:
```javascript
// GET /wp-json/lehiboo/v1/events/search
const response = await fetch(`${config.wordpress.apiUrl}/lehiboo/v1/events/search`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${config.wordpress.apiKey}`
  },
  body: JSON.stringify({
    city: 'Paris',
    radius: 20,
    startDate: '2025-11-01',
    endDate: '2025-11-02',
    maxPrice: 50,
    category: 'culture',
    minAge: 18,
    limit: 5
  })
});
```

**Match Score Calculation**:
```javascript
function calculateMatchScore(event, userProfile) {
  let score = 0;
  const reasons = [];

  // Budget (40% weight)
  if (event.price <= userProfile.budgetMax) {
    score += 0.4;
    reasons.push(`Prix dans votre budget (${event.price}€ < ${userProfile.budgetMax}€)`);
  }

  // Category match (30% weight)
  if (event.category === userProfile.activityType) {
    score += 0.3;
    reasons.push(`Activité ${userProfile.activityType} comme demandé`);
  }

  // Dates availability (20% weight)
  if (isAvailableOnDates(event, userProfile.dates)) {
    score += 0.2;
    reasons.push(`Disponible ${formatDates(userProfile.dates)}`);
  }

  // Rating (10% weight)
  if (event.rating >= 4.5) {
    score += 0.1;
    reasons.push(`Note excellente (${event.rating}/5)`);
  }

  return { matchScore: score, matchReasons: reasons };
}
```

#### Tool 3: `getWeather`

**Fichier**: `lehiboo-ai-backend/src/tools/get-weather.js`

**Responsabilités**:
- Appeler API météo (OpenWeatherMap ou similar)
- Retourner prévisions pour dates données
- Générer alertes si mauvais temps

**Input Schema**:
```typescript
{
  location: string,  // 'Paris' ou coordonnées
  dates: string[]    // ['2025-11-01', '2025-11-02']
}
```

**Output Schema**:
```typescript
{
  success: boolean,
  forecasts: [
    {
      date: string,
      condition: 'sunny' | 'cloudy' | 'rain' | 'storm' | 'snow',
      temp: number,
      precipitation: number  // %
    }
  ],
  alert?: {
    icon: string,  // '🌧️', '⛈️', '❄️'
    message: string
  }
}
```

#### Tool 4: `createItinerary`

**Fichier**: `lehiboo-ai-backend/src/tools/create-itinerary.js`

**Responsabilités**:
- Créer package weekend/séjour
- Optimiser ordre géographique
- Suggérer restaurants entre activités
- Calculer coût total

**Input Schema**:
```typescript
{
  eventIds: string[],
  startDate: string,
  endDate: string,
  preferences: {
    includeRestaurants: boolean,
    pacePreference: 'relaxed' | 'moderate' | 'intense'
  }
}
```

**Output Schema**:
```typescript
{
  success: boolean,
  itinerary: {
    day1: [
      { time: '10:00', event: {...}, type: 'activity' },
      { time: '13:00', suggestion: 'Restaurant...', type: 'meal' },
      { time: '15:00', event: {...}, type: 'activity' }
    ],
    day2: [...]
  },
  totalCost: number,
  totalDuration: string
}
```

**Implémentation**:
```javascript
// 1. Créer tools definitions dans AI SDK format
// 2. Enregistrer tools dans streamText()
// 3. Implémenter logique backend pour chaque tool
// 4. Connecter avec WordPress API
// 5. Tester avec conversations réelles
```

---

### Phase 3: Persistance Conversations Backend

**Durée**: 2-3 jours
**Priorité**: 🔥 HAUTE
**Status**: ⏳ À faire

**Objectif**: Sauvegarder conversations, restaurer contexte, tracking anonyme

#### Architecture Backend

**Stack**:
- **Auth anonyme**: Fingerprint + session token
- **Storage**: Base de données (MySQL WordPress ou MongoDB)
- **API**: REST endpoints pour CRUD conversations

**Tables/Collections nécessaires**:

```sql
-- Table: lehiboo_conversations
CREATE TABLE lehiboo_conversations (
  id VARCHAR(36) PRIMARY KEY,
  anonymous_user_id VARCHAR(100) NOT NULL,
  fingerprint VARCHAR(100),
  version VARCHAR(10) DEFAULT '2.0',
  context JSON,  -- { userProfile, recommendedEvents, stage }
  metadata JSON, -- { device, browser, source }
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_anonymous_user (anonymous_user_id),
  INDEX idx_created_at (created_at)
);

-- Table: lehiboo_messages
CREATE TABLE lehiboo_messages (
  id VARCHAR(36) PRIMARY KEY,
  conversation_id VARCHAR(36) NOT NULL,
  role ENUM('user', 'assistant', 'system') NOT NULL,
  content TEXT,
  parts JSON,  -- Pour messages avec tool calls
  has_tool_call BOOLEAN DEFAULT FALSE,
  tool_names JSON,
  has_error BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES lehiboo_conversations(id) ON DELETE CASCADE,
  INDEX idx_conversation (conversation_id),
  INDEX idx_created_at (created_at)
);
```

#### API Endpoints

**Fichier**: `lehiboo-ai-backend/src/routes/conversations.js`

```javascript
// POST /conversations
// Create new conversation
router.post('/', async (req, res) => {
  const { anonymousUserId, fingerprint } = req.body;
  const conversation = await db.conversations.create({
    id: generateUUID(),
    anonymousUserId,
    fingerprint,
    context: {},
    metadata: { userAgent: req.headers['user-agent'] }
  });
  res.json({ success: true, conversation });
});

// GET /conversations/:id
// Load conversation with messages
router.get('/:id', async (req, res) => {
  const conversation = await db.conversations.findById(req.params.id);
  const messages = await db.messages.findByConversationId(req.params.id);
  res.json({ success: true, conversation, messages });
});

// POST /conversations/:id/messages
// Save new messages
router.post('/:id/messages', async (req, res) => {
  const { messages, startIndex } = req.body;
  const saved = await db.messages.bulkCreate(messages.map(msg => ({
    ...msg,
    conversationId: req.params.id
  })));
  res.json({ success: true, saved: saved.length });
});

// PUT /conversations/:id/context
// Update conversation context
router.put('/:id/context', async (req, res) => {
  const { context } = req.body;
  await db.conversations.update(req.params.id, { context });
  res.json({ success: true });
});

// GET /conversations
// List user conversations
router.get('/', async (req, res) => {
  const { anonymousUserId } = req.query;
  const conversations = await db.conversations.findByUser(anonymousUserId);
  res.json({ success: true, conversations });
});

// DELETE /conversations/:id
// Delete conversation
router.delete('/:id', async (req, res) => {
  await db.conversations.delete(req.params.id);
  res.json({ success: true });
});
```

---

### Phase 4: Frontend Zustand Persistence

**Durée**: 2 jours
**Priorité**: 🔥 HAUTE
**Status**: ⏳ À faire

**Objectif**: Gérer état conversations côté client avec persistance localStorage + backend

#### Architecture Frontend

**Stack actuel WordPress**:
- Vanilla JS (pas de React)
- Pas de Zustand possible (React-only)
- Alternative: Custom state manager

**Solution**: Custom State Manager Pattern

**Fichier**: `wp-content/plugins/lehiboo-ai-assistant/assets/js/conversation-store.js`

```javascript
/**
 * Conversation Store - Vanilla JS State Manager
 * Inspired by Zustand patterns
 */

class ConversationStore {
  constructor() {
    this.state = {
      currentConversation: null,
      conversations: [],
      isLoading: false,
      error: null,
      isSaving: false
    };

    this.listeners = [];
    this.lastSavedMessageCount = 0;
    this.anonymousUserId = this.loadAnonymousUserId();
  }

  // Anonymous auth
  async initializeAnonymousAuth() {
    let fingerprint = localStorage.getItem('lehiboo_fingerprint');
    if (!fingerprint) {
      fingerprint = await this.generateFingerprint();
      localStorage.setItem('lehiboo_fingerprint', fingerprint);
    }

    let sessionToken = localStorage.getItem('lehiboo_session_token');
    if (!sessionToken) {
      sessionToken = await this.createAnonymousSession(fingerprint);
      localStorage.setItem('lehiboo_session_token', sessionToken);
    }

    this.anonymousUserId = fingerprint;
    return { fingerprint, sessionToken };
  }

  // Create conversation
  async createConversation() {
    this.setState({ isLoading: true });

    try {
      const response = await fetch('/wp-json/lehiboo/v1/conversations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('lehiboo_session_token')}`
        },
        body: JSON.stringify({
          anonymousUserId: this.anonymousUserId,
          fingerprint: localStorage.getItem('lehiboo_fingerprint')
        })
      });

      const data = await response.json();
      this.setState({
        currentConversation: data.conversation,
        isLoading: false
      });

      localStorage.setItem('lehiboo_current_conversation_id', data.conversation.id);
      return data.conversation;
    } catch (error) {
      this.setState({ error: error.message, isLoading: false });
      throw error;
    }
  }

  // Load conversation
  async loadConversation(conversationId) {
    this.setState({ isLoading: true });

    try {
      const response = await fetch(`/wp-json/lehiboo/v1/conversations/${conversationId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('lehiboo_session_token')}`
        }
      });

      const data = await response.json();
      this.setState({
        currentConversation: data.conversation,
        isLoading: false
      });

      return { conversation: data.conversation, messages: data.messages };
    } catch (error) {
      this.setState({ error: error.message, isLoading: false });
      throw error;
    }
  }

  // Save messages (only new ones)
  async saveMessages(messages) {
    if (messages.length <= this.lastSavedMessageCount) {
      return; // Nothing new to save
    }

    const newMessages = messages.slice(this.lastSavedMessageCount);
    this.setState({ isSaving: true });

    try {
      const response = await fetch(
        `/wp-json/lehiboo/v1/conversations/${this.state.currentConversation.id}/messages`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('lehiboo_session_token')}`
          },
          body: JSON.stringify({
            messages: newMessages,
            startIndex: this.lastSavedMessageCount
          })
        }
      );

      const data = await response.json();
      this.lastSavedMessageCount = messages.length;
      this.setState({ isSaving: false });

      return data;
    } catch (error) {
      this.setState({ error: error.message, isSaving: false });
      throw error;
    }
  }

  // Update context
  async updateContext(context) {
    try {
      await fetch(
        `/wp-json/lehiboo/v1/conversations/${this.state.currentConversation.id}/context`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('lehiboo_session_token')}`
          },
          body: JSON.stringify({ context })
        }
      );
    } catch (error) {
      console.error('Failed to update context:', error);
    }
  }

  // State management
  setState(partial) {
    this.state = { ...this.state, ...partial };
    this.notify();
  }

  subscribe(listener) {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter(l => l !== listener);
    };
  }

  notify() {
    this.listeners.forEach(listener => listener(this.state));
  }
}

// Singleton instance
window.conversationStore = new ConversationStore();
```

**Intégration dans chat-interface.js**:

```javascript
// Initialiser au démarrage
async initialize() {
  await window.conversationStore.initializeAnonymousAuth();

  const lastConversationId = localStorage.getItem('lehiboo_current_conversation_id');
  if (lastConversationId) {
    const { messages } = await window.conversationStore.loadConversation(lastConversationId);
    this.restoreMessages(messages);
  } else {
    await window.conversationStore.createConversation();
  }
}

// Sauvegarder après chaque réponse IA
async handleAIResponse(response) {
  // ... traitement de la réponse

  // Auto-save
  await window.conversationStore.saveMessages(this.state.messages);
}
```

---

### Phase 5: Intégration WordPress Events API

**Durée**: 2 jours
**Priorité**: 🔥 HAUTE
**Status**: ⏳ À faire

**Objectif**: Connecter les tools à la vraie base WordPress d'événements

#### Endpoints WordPress à créer

**Fichier**: `wp-content/plugins/lehiboo-ai-assistant/includes/api/events-api.php`

```php
<?php

class LehibooEventsAPI {

  public function register_routes() {
    register_rest_route('lehiboo/v1', '/events/search', [
      'methods' => 'POST',
      'callback' => [$this, 'search_events'],
      'permission_callback' => [$this, 'check_api_key']
    ]);
  }

  public function search_events($request) {
    $params = $request->get_json_params();

    $args = [
      'post_type' => 'event',
      'posts_per_page' => $params['limit'] ?? 5,
      'meta_query' => []
    ];

    // Filter by city
    if (!empty($params['city'])) {
      $args['meta_query'][] = [
        'key' => 'event_city',
        'value' => $params['city'],
        'compare' => 'LIKE'
      ];
    }

    // Filter by max price (STRICT)
    if (!empty($params['maxPrice'])) {
      $args['meta_query'][] = [
        'key' => 'event_price',
        'value' => $params['maxPrice'],
        'type' => 'NUMERIC',
        'compare' => '<='
      ];
    }

    // Filter by dates
    if (!empty($params['startDate'])) {
      $args['meta_query'][] = [
        'key' => 'event_start_date',
        'value' => $params['startDate'],
        'type' => 'DATE',
        'compare' => '>='
      ];
    }

    // Filter by category
    if (!empty($params['category'])) {
      $args['tax_query'] = [
        [
          'taxonomy' => 'event_category',
          'field' => 'slug',
          'terms' => $params['category']
        ]
      ];
    }

    // Filter by age restriction
    if (!empty($params['minAge'])) {
      $args['meta_query'][] = [
        'key' => 'event_min_age',
        'value' => $params['minAge'],
        'type' => 'NUMERIC',
        'compare' => '<='
      ];
    }

    $query = new WP_Query($args);
    $events = [];

    foreach ($query->posts as $post) {
      $events[] = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => wp_trim_words($post->post_content, 30),
        'price' => (float) get_post_meta($post->ID, 'event_price', true),
        'currency' => 'EUR',
        'location' => [
          'city' => get_post_meta($post->ID, 'event_city', true),
          'address' => get_post_meta($post->ID, 'event_address', true)
        ],
        'dates' => [
          get_post_meta($post->ID, 'event_start_date', true)
        ],
        'duration' => get_post_meta($post->ID, 'event_duration', true),
        'category' => wp_get_post_terms($post->ID, 'event_category', ['fields' => 'slugs'])[0] ?? '',
        'rating' => (float) get_post_meta($post->ID, 'event_rating', true),
        'reviews' => (int) get_post_meta($post->ID, 'event_reviews_count', true),
        'imageUrl' => get_the_post_thumbnail_url($post->ID, 'large'),
        'bookingUrl' => get_permalink($post->ID),
        'ageRestriction' => [
          'min' => (int) get_post_meta($post->ID, 'event_min_age', true)
        ]
      ];
    }

    return new WP_REST_Response([
      'success' => true,
      'events' => $events,
      'totalFound' => $query->found_posts
    ], 200);
  }

  public function check_api_key($request) {
    $api_key = $request->get_header('Authorization');
    $expected_key = 'Bearer ' . get_option('lehiboo_ai_api_key');
    return $api_key === $expected_key;
  }
}

add_action('rest_api_init', function() {
  $api = new LehibooEventsAPI();
  $api->register_routes();
});
```

---

### Phase 6: Testing et Optimisation

**Durée**: 2 jours
**Priorité**: 🔥 MOYENNE
**Status**: ⏳ À faire

**Tests à effectuer**:

1. **Test de collecte groupée**
   - Vérifier que Hedwige demande 3-4 infos à la fois
   - Vérifier qu'elle ne redemande jamais une info déjà donnée
   - Vérifier la complétude (3/6, 5/6, 6/6)

2. **Test de recherche**
   - Vérifier budget strict (jamais au-dessus)
   - Vérifier filtrage par dates
   - Vérifier filtrage par âge (18+)
   - Vérifier match scores et raisons

3. **Test de persistance**
   - Créer conversation
   - Rafraîchir page
   - Vérifier restauration messages
   - Vérifier contexte préservé

4. **Test de performance**
   - Mesurer temps de réponse
   - Vérifier streaming fluide
   - Vérifier pas de lag UI

5. **Test d'abandon**
   - Mesurer taux d'abandon
   - Identifier points de friction
   - Optimiser flow

---

## 📦 STRUCTURE FINALE DES FICHIERS

```
lehiboo_v1/
├── lehiboo-ai-backend/
│   ├── src/
│   │   ├── prompts/
│   │   │   ├── system-prompt.yaml (ancien)
│   │   │   └── system-prompt-v2.md (nouveau)
│   │   ├── tools/
│   │   │   ├── collect-user-profile.js
│   │   │   ├── search-events.js
│   │   │   ├── get-weather.js
│   │   │   └── create-itinerary.js
│   │   ├── routes/
│   │   │   ├── chat.js
│   │   │   └── conversations.js (nouveau)
│   │   ├── services/
│   │   │   ├── ai-service.js
│   │   │   └── conversation-service.js (nouveau)
│   │   └── utils/
│   │       └── fingerprint.js (nouveau)
│   └── ROADMAP_REFONTE.md
│
└── wp-content/
    └── plugins/
        └── lehiboo-ai-assistant/
            ├── assets/
            │   └── js/
            │       ├── chat-interface.js
            │       └── conversation-store.js (nouveau)
            └── includes/
                └── api/
                    ├── events-api.php (nouveau)
                    └── conversations-api.php (nouveau)
```

---

## 🎯 PRIORISATION DES TÂCHES

### Sprint 1 (1 semaine) - FONDATIONS

1. **Tool searchEvents** (2 jours) 🔥🔥🔥
2. **Tool collectUserProfile** (1 jour) 🔥🔥🔥
3. **WordPress Events API** (2 jours) 🔥🔥
4. **Intégration tools dans AI service** (1 jour) 🔥🔥

**Livrable**: Hedwige peut vraiment chercher des événements

### Sprint 2 (1 semaine) - PERSISTANCE

1. **Backend conversations API** (2 jours) 🔥🔥
2. **Frontend conversation store** (2 jours) 🔥🔥
3. **Anonymous auth** (1 jour) 🔥
4. **Tests persistance** (1 jour)

**Livrable**: Conversations sauvegardées et restaurées

### Sprint 3 (3 jours) - POLISH

1. **Tool getWeather** (1 jour)
2. **Tool createItinerary** (1 jour)
3. **Tests end-to-end** (1 jour)

**Livrable**: Expérience complète et fluide

---

## 📊 MÉTRIQUES DE SUCCÈS

### Avant refonte (baseline)

- Messages avant résultats: 10-15
- Taux d'abandon: ~70%
- Temps moyen session: 2-3 min
- Satisfaction: 2/5
- Conversions: <5%

### Après refonte (target)

- Messages avant résultats: 2-3 ✅
- Taux d'abandon: <20% ✅
- Temps moyen session: <1 min ✅
- Satisfaction: 4.5/5 ✅
- Conversions: >25% ✅

### KPIs à tracker

1. **Complétude du profil**
   - % d'users qui atteignent 6/6
   - Temps moyen pour atteindre 6/6

2. **Qualité des résultats**
   - % de recherches avec résultats
   - Nombre moyen de résultats par recherche
   - % de résultats respectant le budget

3. **Engagement**
   - Taux de clic sur événements
   - Taux de réservation
   - Taux de retour (conversations multiples)

4. **Performance technique**
   - Temps de réponse API
   - Taux d'erreur
   - Uptime

---

## 🚀 DÉPLOIEMENT

### Environnements

1. **Local** - Développement
2. **Preprod** - Tests utilisateurs
3. **Production** - Lancement

### Procédure de déploiement

```bash
# 1. Backend (Node.js)
cd lehiboo-ai-backend
git pull
npm install
npm run build
pm2 restart lehiboo-ai-backend

# 2. Frontend (WordPress)
cd wp-content/plugins/lehiboo-ai-assistant
git pull
# Activer plugin si nécessaire

# 3. Vérifications
curl https://preprod.lehiboo.com/wp-json/lehiboo/v1/events/search
curl https://preprod.lehiboo.com/ai-backend/health
```

---

## 📚 DOCUMENTATION

### Pour les développeurs

- `system-prompt-v2.md`: Prompt complet avec exemples
- `ROADMAP_REFONTE.md`: Ce document
- `/temp/zeneli-analysis.md`: Analyse ZENELI complète

### Pour les utilisateurs

- Guide d'utilisation Hedwige (à créer)
- FAQ (à créer)
- Vidéo démo (à créer)

---

## ✅ NEXT STEPS IMMÉDIATS

1. **Valider la roadmap** avec l'équipe
2. **Commencer Sprint 1**: Implémenter `searchEvents`
3. **Créer base de données** conversations
4. **Configurer environnement** de test
5. **Recruter beta testeurs**

---

**Dernière mise à jour**: 2025-10-29
**Version**: 1.0
**Auteur**: Claude Code + Juba
**Inspiration**: ZENELI (571 lignes system prompt, architecture Zustand, AI SDK v5)
