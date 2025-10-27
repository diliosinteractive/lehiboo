# 🔧 MCP Tools - Model Context Protocol

**Status** : ✅ **Implémenté et Intégré**

Les **MCP Tools** permettent à l'IA d'accéder aux données réelles d'EventList WordPress pour générer des recommandations personnalisées basées sur de vrais événements.

---

## 📋 Tools Disponibles

### 1. search_events

**Description** : Rechercher des événements dans la base de données EventList

**Paramètres** :
```json
{
  "type": "sport | culture | gastronomie | nature | detente",
  "startDate": "YYYY-MM-DD",
  "endDate": "YYYY-MM-DD",
  "minAge": 18,
  "maxAge": 65,
  "location": "Paris",
  "limit": 5
}
```

**Exemple d'Utilisation** :
```javascript
{
  toolName: "search_events",
  args: {
    type: "sport",
    startDate: "2025-11-01",
    endDate: "2025-11-03",
    minAge: 25,
    maxAge: 35,
    limit: 5
  }
}
```

**Retour** :
```json
{
  "success": true,
  "count": 3,
  "events": [
    {
      "id": "123",
      "title": "Escalade Indoor",
      "description": "Session d'escalade...",
      "image": "https://...",
      "price": "35€/pers",
      "date": "Samedi 10h-13h",
      "location": "Centre-ville (5 min)",
      "duration": "3h",
      "rating": "4.9",
      "reviews": "234",
      "badges": [
        {"type": "indoor", "icon": "🏠", "text": "Indoor"},
        {"type": "sport", "icon": "💪", "text": "Actif"}
      ],
      "url": "https://lehiboo.com/event/123",
      "availability": "available",
      "ageRestriction": null,
      "category": "sport"
    }
  ]
}
```

---

### 2. get_event_details

**Description** : Obtenir les détails complets d'un événement spécifique

**Paramètres** :
```json
{
  "eventId": "123"
}
```

**Exemple d'Utilisation** :
```javascript
{
  toolName: "get_event_details",
  args: {
    eventId: "456"
  }
}
```

**Retour** :
```json
{
  "success": true,
  "event": {
    "id": "456",
    "title": "Atelier Cuisine Italienne",
    // ... tous les détails
  }
}
```

---

### 3. filter_by_age

**Description** : Filtrer une liste d'événements selon les restrictions d'âge

**Paramètres** :
```json
{
  "eventIds": ["123", "456", "789"],
  "age": 30
}
```

**Exemple d'Utilisation** :
```javascript
{
  toolName: "filter_by_age",
  args: {
    eventIds: ["123", "456", "789"],
    age: 17
  }
}
```

**Retour** :
```json
{
  "success": true,
  "originalCount": 3,
  "filteredCount": 2,
  "events": [
    // Événements adaptés pour 17 ans (pas d'événements 18+)
  ]
}
```

---

### 4. check_availability

**Description** : Vérifier la disponibilité d'un événement pour réservation

**Paramètres** :
```json
{
  "eventId": "123",
  "numberOfPeople": 2
}
```

**Retour** :
```json
{
  "success": true,
  "available": true,
  "spotsLeft": 15,
  "message": "Event is available"
}
```

---

### 5. calculate_distance

**Description** : Calculer la distance approximative entre l'utilisateur et l'événement

**Paramètres** :
```json
{
  "eventId": "123",
  "userLocation": "Paris"
}
```

**Retour** :
```json
{
  "success": true,
  "distance": "5-10 km",
  "duration": "15 min",
  "message": "Distance calculation not yet implemented (placeholder)"
}
```

> ⚠️ **Note** : Nécessite Google Maps API (à implémenter)

---

### 6. suggest_itinerary

**Description** : Créer un itinéraire optimisé à partir de plusieurs événements

**Paramètres** :
```json
{
  "eventIds": ["123", "456", "789"],
  "startDate": "2025-11-01",
  "endDate": "2025-11-03"
}
```

**Retour** :
```json
{
  "success": true,
  "itinerary": [
    {
      "day": 1,
      "time": "morning",
      "event": { /* event 123 */ }
    },
    {
      "day": 1,
      "time": "afternoon",
      "event": { /* event 456 */ }
    },
    {
      "day": 2,
      "time": "morning",
      "event": { /* event 789 */ }
    }
  ],
  "totalEvents": 3,
  "totalDays": 2
}
```

---

## 🔄 Flow d'Utilisation

### Exemple : Recommandations Sportives

1. **User** : "Je cherche une activité sportive ce weekend, j'ai 30 ans"

2. **IA collecte le contexte** :
   - Type groupe : solo (inféré)
   - Âge : 30
   - Type activité : sport
   - Dates : ce weekend (2025-11-02 → 2025-11-03)

3. **IA appelle** `search_events` :
   ```javascript
   {
     type: "sport",
     startDate: "2025-11-02",
     endDate: "2025-11-03",
     minAge: 30,
     maxAge: 30,
     limit: 5
   }
   ```

4. **WordPress Service** :
   - Requête vers `/wp-json/eventlist/v1/events?...`
   - Récupère les événements sportifs du weekend
   - Filtre par âge (exclut 18+ si mineur, etc.)
   - Formate pour l'IA

5. **Tool retourne** les événements réels :
   ```json
   {
     "success": true,
     "count": 3,
     "events": [/* 3 events réels */]
   }
   ```

6. **IA génère réponse** avec les vrais événements :
   - Texte conversationnel
   - Events cards avec données réelles
   - Quick chips pour actions suivantes

7. **User voit** des recommandations personnalisées et réelles ! 🎉

---

## 🏗️ Architecture

```
AI Service (ai-service.js)
    ↓
    Calls MCP Tools (mcp/tools.js)
    ↓
    Uses WordPress Service (wordpress-service.js)
    ↓
    Fetches from WordPress API (/wp-json/eventlist/v1/...)
    ↓
    Returns Formatted Events
    ↓
    AI Generates Response with Real Data
```

### Fichiers Impliqués

1. **`src/services/ai-service.js`**
   - Intègre les tools dans le prompt système
   - Détecte les tool calls de l'IA
   - Exécute les tools
   - Régénère réponse avec résultats

2. **`src/mcp/tools.js`**
   - Définit les 6 tools
   - Execute functions
   - getToolsDefinitions()
   - executeTool()

3. **`src/services/wordpress-service.js`**
   - Gère les requêtes WordPress API
   - Formate les événements
   - Filtre par âge
   - Génère les badges

---

## 🔧 Configuration WordPress

### 1. Endpoint API EventList

Le backend s'attend à trouver :
```
GET /wp-json/eventlist/v1/events
GET /wp-json/eventlist/v1/events/{id}
```

### 2. Paramètres de Requête

```
?category=sport
?start_date=2025-11-01
?end_date=2025-11-03
?location=Paris
?per_page=5
```

### 3. Format Réponse Attendu

```json
[
  {
    "id": 123,
    "title": {
      "rendered": "Titre de l'événement"
    },
    "content": {
      "rendered": "<p>Description...</p>"
    },
    "featured_image_url": "https://...",
    "price": "35",
    "start_date": "2025-11-01",
    "start_time": "10:00",
    "end_time": "13:00",
    "location": "Paris",
    "venue": "Salle de Sport",
    "age_restriction": null,
    "min_age": null,
    "max_age": null,
    "indoor": true,
    "outdoor": false,
    "family_friendly": false,
    "category": "sport",
    "rating": "4.9",
    "reviews_count": 234,
    "availability": "available",
    "spots_remaining": 15,
    "link": "https://lehiboo.com/event/123"
  }
]
```

### 4. Créer l'Endpoint (si inexistant)

Si EventList n'expose pas encore d'API REST, créer dans WordPress :

```php
// Dans le plugin EventList
add_action('rest_api_init', function () {
  register_rest_route('eventlist/v1', '/events', array(
    'methods' => 'GET',
    'callback' => 'get_events_callback',
    'permission_callback' => '__return_true',
  ));
});

function get_events_callback($request) {
  $params = $request->get_params();

  $args = array(
    'post_type' => 'event', // Adapter selon votre CPT
    'posts_per_page' => $params['per_page'] ?? 10,
  );

  // Filtres par meta fields
  if (!empty($params['category'])) {
    $args['tax_query'] = array(/* ... */);
  }

  $events = get_posts($args);

  // Formater en JSON
  return array_map('format_event_for_api', $events);
}
```

---

## 🧪 Testing

### Test 1 : search_events

```bash
# Depuis le backend
curl -X POST http://localhost:3000/chat \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Je cherche une activité sportive ce weekend",
    "conversationId": "test-123",
    "currentStage": "recommendations",
    "userContext": {
      "groupType": "solo",
      "age": 30,
      "interests": ["sport"],
      "dates": "ce-weekend"
    }
  }'
```

**Résultat attendu** :
- L'IA appelle `search_events` automatiquement
- Récupère les vrais événements depuis WordPress
- Retourne une réponse avec event cards réelles

### Test 2 : WordPress Service Direct

```javascript
// Dans Node.js REPL ou test file
import wordpressService from './src/services/wordpress-service.js';

// Test connexion
await wordpressService.testConnection();

// Test search
const events = await wordpressService.searchEvents({
  type: 'sport',
  limit: 3
});

console.log(events);
```

---

## 📊 Logs & Debug

### Activer Logs Debug

```bash
# Dans .env
LOG_LEVEL=debug
```

### Logs MCP Tools

Quand l'IA appelle un tool :
```
[INFO] MCP Tool: search_events called { type: 'sport', limit: 5 }
[INFO] WordPress API request { url: 'https://...', method: 'GET' }
[INFO] Events found { count: 3 }
[INFO] MCP Tool: search_events completed { count: 3 }
[INFO] AI requested tool calls { count: 1, tools: ['search_events'] }
```

---

## 🚀 Améliorations Futures

### Priorité Haute

- [ ] **Implémenter calculate_distance** avec Google Maps API
- [ ] **Cache Redis** pour events populaires (éviter requêtes répétées)
- [ ] **Fallback** si WordPress API down (mode démo)

### Priorité Moyenne

- [ ] **Tool: get_user_location** - Géolocalisation utilisateur
- [ ] **Tool: get_weather_forecast** - Météo précise
- [ ] **Tool: check_event_reviews** - Avis utilisateurs
- [ ] **Tool: suggest_nearby_restaurants** - Restos à proximité

### Priorité Basse

- [ ] **Tool: create_booking** - Réserver directement depuis le chat
- [ ] **Tool: add_to_calendar** - Ajouter au calendrier
- [ ] **Tool: share_event** - Partager par email/SMS

---

## 💡 Tips

### 1. L'IA N'Appelle Pas les Tools

**Solutions** :
- Vérifier que le model supporte function calling (GPT-4, Claude 3+)
- Vérifier le prompt système mentionne les tools
- Essayer un modèle différent
- Regarder les logs : `AI requested tool calls`

### 2. WordPress API Retourne 404

**Solutions** :
- Vérifier l'URL dans `.env` : `WORDPRESS_API_URL`
- Tester manuellement : `curl https://lehiboo.com/wp-json/eventlist/v1/events`
- Créer l'endpoint si inexistant (voir section Configuration)

### 3. Events Mal Formatés

**Solutions** :
- Adapter `formatEventForAI()` dans `wordpress-service.js`
- Vérifier les champs retournés par WordPress
- Ajouter des fallbacks pour champs manquants

---

## 📚 Ressources

- **AI SDK Tools** : https://sdk.vercel.ai/docs/ai-sdk-core/tools-and-tool-calling
- **WordPress REST API** : https://developer.wordpress.org/rest-api/
- **MCP Protocol** : https://modelcontextprotocol.io

---

**Status** : ✅ **Implémenté et Prêt**

Les MCP Tools sont maintenant intégrés dans le backend. L'IA peut rechercher des événements réels et générer des recommandations personnalisées basées sur les vraies données EventList ! 🎉
