# 🧪 Guide de Test Intégration Complète

**Objectif** : Tester le système complet de bout en bout (Frontend → Backend → WordPress → IA → Météo)

---

## 📋 Prérequis

### 1. Services Externes

- [ ] **Compte OpenRouter** : https://openrouter.ai
  - Clé API obtenue
  - Crédit initial : ~5$ gratuit

- [ ] **Compte OpenWeatherMap** : https://openweathermap.org
  - Clé API obtenue (gratuit)
  - Limite : 1000 appels/jour

### 2. WordPress

- [ ] **EventList plugin** installé et actif
- [ ] **Événements créés** (au moins 5-10 pour tester)
- [ ] **Custom post type** : `event` existe
- [ ] **Taxonomie** : `event_category` existe
- [ ] **Meta fields** configurés (voir liste ci-dessous)

### 3. Backend Node.js

- [ ] **Node.js 18+** installé
- [ ] **Dépendances** : `npm install` exécuté
- [ ] **Fichier .env** configuré

---

## 🔧 Étape 1 : Configuration WordPress

### A. Activer l'API REST EventList

#### Option 1 : Via le plugin principal

Éditer le fichier principal d'EventList (généralement `eventlist.php`) :

```php
// Ajouter cette ligne après les includes existants
require_once plugin_dir_path(__FILE__) . 'eventlist-rest-api-init.php';
```

#### Option 2 : Via Must-Use Plugin

Créer `wp-content/mu-plugins/eventlist-api.php` :

```php
<?php
/**
 * Plugin Name: EventList REST API
 * Description: Active l'API REST pour EventList
 */

require_once WP_PLUGIN_DIR . '/eventlist/includes/class-eventlist-rest-api.php';
```

### B. Vérifier que l'API fonctionne

```bash
# Tester l'endpoint events
curl https://lehiboo.dilios.me/wp-json/eventlist/v1/events

# Résultat attendu : Liste d'événements en JSON
{
  "events": [
    {
      "id": 123,
      "title": {
        "rendered": "Escalade Indoor"
      },
      "start_date": "2025-11-02",
      "price": "35",
      ...
    }
  ],
  "total": 15,
  "pages": 2
}
```

### C. Tester les filtres

```bash
# Par catégorie
curl "https://lehiboo.dilios.me/wp-json/eventlist/v1/events?category=sport"

# Par dates
curl "https://lehiboo.dilios.me/wp-json/eventlist/v1/events?start_date=2025-11-01&end_date=2025-11-30"

# Par localisation
curl "https://lehiboo.dilios.me/wp-json/eventlist/v1/events?location=Paris"

# Combiné
curl "https://lehiboo.dilios.me/wp-json/eventlist/v1/events?category=sport&start_date=2025-11-02&per_page=5"
```

### D. Meta Fields Requis

Pour que l'API fonctionne correctement, ces meta fields doivent exister pour chaque événement :

**Obligatoires** :
- `event_start_date` (DATE) - Date de début (YYYY-MM-DD)
- `event_location` (TEXT) - Localisation
- `event_price` (NUMBER) - Prix

**Recommandés** :
- `event_end_date` (DATE) - Date de fin
- `event_start_time` (TIME) - Heure début (HH:MM)
- `event_end_time` (TIME) - Heure fin
- `event_venue` (TEXT) - Lieu précis
- `event_age_restriction` (TEXT) - "18+" ou vide
- `event_min_age` (NUMBER) - Âge minimum
- `event_max_age` (NUMBER) - Âge maximum
- `event_indoor` (BOOLEAN) - Indoor ?
- `event_outdoor` (BOOLEAN) - Outdoor ?
- `event_family_friendly` (BOOLEAN) - Adapté famille ?
- `event_rating` (NUMBER) - Note (0-5)
- `event_reviews_count` (NUMBER) - Nombre d'avis
- `event_availability` (TEXT) - "available", "full", "limited"
- `event_spots_remaining` (NUMBER) - Places restantes

---

## 🚀 Étape 2 : Configuration Backend

### A. Fichier .env

Créer `/lehiboo-ai-backend/.env` :

```bash
# ============================================
# API KEYS (OBLIGATOIRES)
# ============================================

OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxx
API_KEY=votre-secret-key-pour-wordpress

# ============================================
# CONFIGURATION SERVEUR
# ============================================

PORT=3000
NODE_ENV=development

# ============================================
# WORDPRESS INTEGRATION
# ============================================

WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

# ============================================
# CONFIGURATION IA
# ============================================

DEFAULT_MODEL=anthropic/claude-3.5-sonnet
MAX_TOKENS=1000
TEMPERATURE=0.7

# ============================================
# RATE LIMITING
# ============================================

RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20

# ============================================
# LOGGING
# ============================================

LOG_LEVEL=info
```

### B. Démarrer le Backend

```bash
cd lehiboo-ai-backend
npm run dev
```

**Résultat attendu** :
```
[INFO] Testing OpenRouter connection...
[INFO] ✅ OpenRouter connection successful
[INFO] Testing Weather API connection...
[INFO] ✅ Weather API connection successful
[INFO] 🚀 Le Hiboo AI Backend started
  env: development
  host: 0.0.0.0
  port: 3000
  url: http://0.0.0.0:3000
```

### C. Tester les Endpoints Backend

```bash
# Health check
curl http://localhost:3000/health

# Résultat attendu :
{
  "status": "ok",
  "timestamp": "2025-10-28T10:00:00.000Z",
  "version": "1.0.0"
}
```

```bash
# Test chat (nécessite API_KEY)
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer votre-secret-key-pour-wordpress" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'

# Résultat attendu : Réponse IA avec quickChips
```

---

## 🔗 Étape 3 : Connecter WordPress → Backend

### A. Configuration Plugin WordPress

```
WP Admin → Le Hiboo → Assistant IA → Paramètres

✅ Activer l'assistant IA
URL Backend : http://localhost:3000 (dev) ou https://votre-backend.com (prod)
Clé API : votre-secret-key-pour-wordpress (même que dans .env)

Sauvegarder
```

### B. Tester la Connexion

Dans les logs du backend (`logs/app.log`), vous devriez voir :

```
[INFO] Incoming request { method: 'POST', path: '/chat', ip: '::ffff:127.0.0.1' }
[INFO] Chat request received { conversationId: 'conv_...', stage: 'greeting' }
```

---

## 🧪 Étape 4 : Tests Bout en Bout

### Test 1 : Mode Démo (Sans Backend)

**Action** :
1. Désactiver URL Backend dans WordPress Settings
2. Frontend → Ouvrir chat
3. Envoyer : "Bonjour"

**Résultat attendu** :
- Message d'accueil avec bandeau "Mode Démo"
- Quick chips : Solo, Couple, Famille, Amis

**Action** :
4. Cliquer "En couple"
5. Envoyer : "30 ans"
6. Cliquer "Ce week-end"
7. Cliquer "Sportif"

**Résultat attendu** :
- 5 event cards démo s'affichent
- Alerte météo simulée visible

✅ **Pass** si tout le flow fonctionne

---

### Test 2 : Backend IA (Sans MCP Tools)

**Action** :
1. Activer URL Backend dans WordPress
2. Frontend → Ouvrir chat
3. Envoyer : "Je cherche une activité"

**Résultat attendu** :
- Réponse de l'IA (Claude/GPT)
- PAS de bandeau "Mode Démo"
- Quick chips contextuels

**Vérifier logs backend** :
```
[INFO] Chat request received
[INFO] Generating AI response
[INFO] AI response generated { tokensUsed: 150 }
```

✅ **Pass** si l'IA répond de manière cohérente

---

### Test 3 : MCP Tool - get_weather

**Action** :
1. Frontend → Chat
2. Envoyer : "Quel temps fera-t-il ce weekend à Paris ?"

**Résultat attendu** :
- L'IA répond avec météo réelle
- Alerte météo contextuelle (☀️ / 🌧️ / etc.)
- Suggestions indoor/outdoor basées sur météo

**Vérifier logs backend** :
```
[INFO] AI requested tool calls { count: 1, tools: ['get_weather'] }
[INFO] MCP Tool: get_weather called { location: 'Paris' }
[INFO] Weather API request { url: 'https://api.openweathermap.org/...' }
[INFO] MCP Tool: get_weather completed
```

✅ **Pass** si météo réelle s'affiche

---

### Test 4 : MCP Tool - search_events

**Action** :
1. Frontend → Chat
2. Envoyer : "Je cherche une activité sportive ce weekend, j'ai 30 ans"

**Résultat attendu** :
- L'IA comprend : sport, ce weekend, âge 30
- Appelle automatiquement `search_events`
- Appelle `get_weather` pour la date
- Retourne événements RÉELS depuis WordPress
- Event cards avec vraies données

**Vérifier logs backend** :
```
[INFO] AI requested tool calls { count: 2, tools: ['get_weather', 'search_events'] }
[INFO] MCP Tool: search_events called { type: 'sport', startDate: '2025-11-02', minAge: 30 }
[INFO] WordPress API request { url: 'https://lehiboo.dilios.me/wp-json/eventlist/v1/events?...' }
[INFO] Events found { count: 3 }
```

**Vérifier event cards** :
- Titre réel depuis WordPress
- Image réelle
- Prix réel
- Date réelle
- Localisation réelle
- Badges corrects (Indoor/Outdoor, Sport, etc.)

✅ **Pass** si événements réels s'affichent

---

### Test 5 : Flow Complet avec Météo

**Scénario** : User cherche activité outdoor mais pluie prévue

**Action** :
1. Envoyer : "Je veux faire une randonnée samedi prochain"

**Résultat attendu SI pluie** :
- IA appelle `get_weather` pour samedi prochain
- Détecte pluie
- Alerte : "🌧️ Pluie prévue. Je suggère indoor..."
- Appelle `search_events` avec `indoor: true`
- Retourne activités indoor alternatives (musées, escape games, etc.)

**Résultat attendu SI beau temps** :
- IA appelle `get_weather`
- Détecte beau temps
- Message : "☀️ Météo idéale !"
- Appelle `search_events` avec activités outdoor
- Retourne randonnées, sports outdoor

✅ **Pass** si l'IA adapte les recommandations à la météo

---

### Test 6 : Filtrage Âge (18+)

**Action** :
1. Envoyer : "Je cherche une activité ce soir, j'ai 17 ans"

**Résultat attendu** :
- IA appelle `search_events` avec `minAge: 17, maxAge: 17`
- OU appelle `filter_by_age` après search
- Les événements 18+ sont EXCLUS
- Seulement événements family-friendly ou sans restriction

**Vérifier** :
- Aucun événement avec badge "🔞 18+"
- Tous événements adaptés mineurs

✅ **Pass** si filtrage âge fonctionne

---

### Test 7 : Création Package Weekend

**Action** :
1. Envoyer : "Créé-moi un package complet pour ce weekend"

**Résultat attendu** :
- IA appelle `search_events` pour plusieurs types
- Appelle `suggest_itinerary` pour optimiser
- Retourne package avec :
  - Samedi matin : Activité 1
  - Samedi après-midi : Activité 2
  - Dimanche matin : Activité 3
  - Dimanche après-midi : Activité 4
- Suggestions restaurants entre activités

✅ **Pass** si package cohérent généré

---

## 📊 Checklist Complète

### Frontend
- [ ] Chat s'ouvre en immersif
- [ ] Backdrop cliquable
- [ ] Charte Le Hiboo (orange + Montserrat)
- [ ] Quick chips fonctionnels
- [ ] Event cards s'affichent
- [ ] Typing indicator
- [ ] Weather alerts
- [ ] Rate limiting (10 msg/min)
- [ ] Responsive (mobile/tablet/desktop)

### WordPress
- [ ] Plugin activé
- [ ] Settings configurées
- [ ] API REST accessible (`/wp-json/eventlist/v1/events`)
- [ ] Événements retournés en JSON
- [ ] Filtres fonctionnent (category, dates, location)
- [ ] Meta fields présents

### Backend
- [ ] Serveur démarre sans erreur
- [ ] OpenRouter connexion OK
- [ ] Weather API connexion OK
- [ ] Logs Winston fonctionnent
- [ ] Rate limiting actif
- [ ] CORS configuré

### IA
- [ ] Répond de manière conversationnelle
- [ ] Détecte intents (sport, couple, dates...)
- [ ] Appelle tools automatiquement
- [ ] Génère quickChips contextuels
- [ ] Respecte le flow (6 stages)

### MCP Tools
- [ ] `search_events` appelle WordPress API
- [ ] `get_event_details` retourne détails
- [ ] `filter_by_age` filtre correctement
- [ ] `get_weather` retourne météo réelle
- [ ] `suggest_itinerary` optimise packages
- [ ] Events formatés correctement (badges, prix, etc.)

### Météo
- [ ] API OpenWeatherMap connexion OK
- [ ] Météo actuelle fonctionne
- [ ] Prévisions 5 jours fonctionnent
- [ ] Alertes générées (pluie, chaleur, etc.)
- [ ] Recommandations indoor/outdoor
- [ ] Affichage alertes frontend

---

## 🐛 Troubleshooting

### Problème : WordPress API retourne 404

**Solutions** :
1. Vérifier que `class-eventlist-rest-api.php` est chargé
2. Vider cache WordPress (permalinks)
3. Réenregistrer permalinks : Settings → Permalinks → Save
4. Vérifier custom post type : doit être `public` et `show_in_rest`

### Problème : Backend ne trouve pas les événements

**Solutions** :
1. Tester manuellement : `curl https://lehiboo.dilios.me/wp-json/eventlist/v1/events`
2. Vérifier `WORDPRESS_API_URL` dans `.env`
3. Vérifier CORS (voir logs browser F12)
4. Adapter `wordpress-service.js` si format différent

### Problème : IA n'appelle pas les tools

**Solutions** :
1. Vérifier model supporte function calling (Claude 3+, GPT-4+)
2. Regarder logs : `AI requested tool calls`
3. Tester avec modèle différent : `DEFAULT_MODEL=openai/gpt-4-turbo`
4. Vérifier prompts mentionnent les tools

### Problème : Météo ne fonctionne pas

**Solutions** :
1. Vérifier `WEATHER_API_KEY` dans `.env`
2. Tester manuellement : `curl "https://api.openweathermap.org/data/2.5/weather?q=Paris&appid=YOUR_KEY"`
3. Vérifier quota (1000 calls/jour gratuit)
4. Regarder logs : `Weather API connection test`

---

## 📈 Métriques de Succès

Un système **correctement configuré** affiche :

- ✅ **Temps réponse** : 1-2s pour recommandations
- ✅ **Précision** : Événements correspondent aux critères
- ✅ **Météo** : Alertes contextuelles correctes
- ✅ **UX** : Flow conversationnel naturel
- ✅ **Filtres** : Âge, dates, type respectés
- ✅ **0 erreur** dans les logs

---

## 🎯 Prochaine Étape

Une fois tous les tests **PASS** ✅ :

1. **Déployer backend** : Railway/Vercel
2. **Configurer production** : HTTPS, domaine, monitoring
3. **Optimiser** : Cache Redis, compression
4. **Go Live** ! 🚀

---

**Temps estimé tests complets** : 1-2 heures
**Résultat attendu** : Système 100% fonctionnel end-to-end
