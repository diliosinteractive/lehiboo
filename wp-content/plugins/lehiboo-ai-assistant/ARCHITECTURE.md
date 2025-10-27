# Le Hiboo - Assistant IA Conversationnel
## Architecture Complète & Planification

**Version:** 1.0.0
**Date:** 2025-10-27
**Status:** En développement

---

## 🎯 OBJECTIF

Créer un assistant IA conversationnel qui aide les utilisateurs à trouver l'activité parfaite en posant les bonnes questions et en recommandant des événements personnalisés.

---

## 🔒 SÉCURITÉ - PRIORITÉ ABSOLUE

### 1. Protection Frontend
- ✅ Rate limiting : 10 messages/minute par utilisateur
- ✅ Validation côté client avant envoi
- ✅ Sanitization de tous les inputs
- ✅ Protection XSS (Content Security Policy)
- ✅ HTTPS obligatoire en production
- ✅ Tokens CSRF pour toutes les requêtes

### 2. Protection Backend
- ✅ Rate limiting API : 20 req/min par IP
- ✅ Validation stricte des inputs (Joi/Zod)
- ✅ Sanitization côté serveur
- ✅ Authentication JWT/WordPress
- ✅ Protection injection SQL
- ✅ Timeout requests (30s max)
- ✅ Limitation taille messages (2000 chars)

### 3. Protection WordPress
- ✅ Nonces WordPress
- ✅ Capabilities checks
- ✅ Prepared statements
- ✅ Escape outputs
- ✅ CORS configuré strictement

### 4. Protection IA
- ✅ Prompt injection prevention
- ✅ Content moderation
- ✅ Pas d'exécution code utilisateur
- ✅ Limites tokens (max 4000)
- ✅ Filtering outputs sensibles

### 5. Protection Données
- ✅ RGPD compliant
- ✅ Anonymisation conversations
- ✅ Pas de stockage données sensibles
- ✅ Chiffrement en transit (TLS)
- ✅ Logs anonymisés

---

## 📊 CRITÈRES UTILISATEUR À COLLECTER

### Critères Obligatoires
1. **Âge / Tranche d'âge**
   - Validation : 1-120 ans
   - Format : entier ou tranche (ex: "25-35")
   - Utilisé pour : filtrer événements adaptés, restrictions légales

2. **Type de groupe**
   - Options : Solo, Couple, Famille, Groupe d'amis, Professionnel
   - Validation : enum strict
   - Utilisé pour : taille groupe, type activités

3. **Dates souhaitées**
   - Format : date ou période
   - Validation : date >= aujourd'hui
   - Utilisé pour : disponibilité, météo

### Critères Fortement Recommandés
4. **Budget approximatif**
   - Options : < 50€, 50-100€, 100-200€, 200-500€, > 500€, Flexible
   - Utilisé pour : filtrage événements

5. **Météo considerations**
   - Auto-fetch via API météo
   - Alerte si mauvais temps extérieur
   - Suggestions alternatives indoor

6. **Niveau d'énergie**
   - Options : Détente, Modéré, Actif, Intense
   - Utilisé pour : type activité

7. **Centres d'intérêt**
   - Multi-select : Sport, Culture, Gastronomie, Nature, Art, Tech, etc.
   - Utilisé pour : recommandations

### Critères Optionnels
8. **Localisation préférée**
   - Rayon km depuis position
   - Validation : 1-200 km

9. **Accessibilité**
   - PMR, allergies alimentaires, etc.

10. **Nombre de personnes exact**
    - Si famille/groupe
    - Validation : 1-100

11. **Enfants dans le groupe**
    - Oui/Non + âges si oui
    - Utilisé pour : filtrer activités family-friendly

---

## 🏗️ ARCHITECTURE TECHNIQUE

### Stack Frontend
```
WordPress Theme/Plugin
├── React 18 (ou Vanilla JS moderne)
├── AI SDK (@ai-sdk/react) - Streaming
├── Tailwind CSS / CSS Modules
├── Fetch API moderne
└── LocalStorage (historique conversations)
```

### Stack Backend
```
Node.js API (Separate service)
├── Express.js 4.x ou Fastify 4.x
├── AI SDK Core (@ai-sdk/core)
├── OpenRouter Integration
├── MCP SDK (@modelcontextprotocol/sdk)
├── Rate Limiter (express-rate-limit)
├── Validator (Joi ou Zod)
├── JWT Authentication
├── Winston Logger
└── PM2 (process manager)
```

### Stack Data & Services
```
Data Layer
├── WordPress REST API v2
├── EventList Plugin Data
├── Custom Tables (analytics)
└── Redis (cache - optionnel)

External Services
├── OpenRouter (multi-model AI)
├── OpenWeatherMap (météo)
└── Google Maps API (distances - optionnel)
```

---

## 📁 STRUCTURE DES FICHIERS

```
wp-content/plugins/lehiboo-ai-assistant/
│
├── lehiboo-ai-assistant.php          # Plugin principal
├── ARCHITECTURE.md                   # Ce fichier
├── README.md                         # Documentation utilisateur
├── SECURITY.md                       # Documentation sécurité
├── .env.example                      # Variables d'environnement
│
├── includes/
│   ├── class-lehiboo-ai.php         # Classe principale
│   ├── class-security.php            # Sécurité & rate limiting
│   ├── class-mcp-server.php          # MCP Server WordPress
│   ├── class-chat-handler.php        # Gestion chat
│   ├── class-prompt-manager.php      # Gestion prompts
│   ├── class-analytics.php           # Analytics conversations
│   ├── class-age-validator.php       # Validation âge & restrictions
│   └── class-weather-api.php         # Intégration météo
│
├── admin/
│   ├── class-admin-settings.php      # Page settings
│   ├── views/
│   │   ├── settings-page.php        # UI settings
│   │   ├── prompts-editor.php       # Éditeur prompts
│   │   ├── analytics-dashboard.php  # Dashboard analytics
│   │   └── security-logs.php        # Logs sécurité
│   └── assets/
│       ├── admin-styles.css
│       └── admin-scripts.js
│
├── assets/
│   ├── js/
│   │   ├── chat-interface.js        # Interface chat (React/Vanilla)
│   │   ├── ai-sdk-integration.js    # Intégration AI SDK
│   │   ├── message-handler.js       # Gestion messages
│   │   ├── quick-chips.js           # Boutons quick reply
│   │   ├── security-client.js       # Rate limiting client
│   │   └── analytics-tracker.js     # Tracking événements
│   │
│   └── css/
│       ├── chat-interface.css       # Styles chat
│       ├── chat-mobile.css          # Responsive mobile
│       └── chat-themes.css          # Thèmes (light/dark)
│
├── prompts/
│   ├── system-prompt.yaml           # Prompt système principal
│   ├── conversation-flow.yaml       # Flow conversation étapes
│   ├── security-rules.yaml          # Règles sécurité IA
│   ├── age-restrictions.yaml        # Restrictions par âge
│   │
│   └── specialized/
│       ├── weekend-planner.yaml     # Planificateur weekend
│       ├── vacation-planner.yaml    # Planificateur vacances
│       ├── family-activities.yaml   # Activités famille
│       ├── solo-adventures.yaml     # Aventures solo
│       └── weather-alternatives.yaml # Alternatives météo
│
├── api/
│   ├── chat-endpoint.php            # Endpoint chat WordPress
│   ├── prompts-api.php              # API prompts (admin)
│   ├── analytics-api.php            # API analytics
│   └── middleware/
│       ├── rate-limiter.php         # Rate limiting
│       ├── auth-middleware.php      # Authentication
│       └── validator.php            # Validation inputs
│
├── mcp-tools/
│   ├── search-events.js             # Tool : chercher événements
│   ├── get-event-details.js         # Tool : détails événement
│   ├── check-availability.js        # Tool : disponibilités
│   ├── get-weather.js               # Tool : météo
│   ├── calculate-distance.js        # Tool : distances
│   ├── filter-by-age.js             # Tool : filtrer par âge
│   └── suggest-itinerary.js         # Tool : suggérer itinéraire
│
├── database/
│   ├── schema.sql                   # Schéma tables custom
│   └── migrations/
│       └── 001_initial.sql
│
└── tests/
    ├── security/
    │   ├── test-rate-limiting.js
    │   ├── test-xss-prevention.js
    │   └── test-injection.js
    ├── integration/
    │   └── test-chat-flow.js
    └── unit/
        └── test-validators.js
```

---

## 🔄 FLOW CONVERSATIONNEL DÉTAILLÉ

### Étape 1 : Accueil (Greeting)
```
IA: "Bonjour ! Je suis l'assistant Le Hiboo 👋
     Je vais t'aider à trouver l'activité parfaite.

     Pour commencer, tu cherches une activité pour :
     [Solo] [En couple] [En famille] [Entre amis] [Groupe/Pro]"
```

### Étape 2 : Âge & Composition Groupe
```
IA: "Super ! Et pour que je te propose des activités adaptées,
     peux-tu me dire :
     - Ton âge (ou tranche d'âge) ?
     - Si enfants : leurs âges ?"

[Quick input: "18-25" | "25-35" | "35-50" | "50+"]
[Si famille: "Âges des enfants ?"]

→ Validation backend : age >= 1 && age <= 120
→ Stockage : user_age, has_children, children_ages[]
```

### Étape 3 : Dates & Météo
```
IA: "Parfait ! Quand souhaites-tu faire cette activité ?
     [Ce week-end] [Week-end prochain] [Dates précises] [Flexible]"

→ Fetch météo automatique
→ Si pluie/froid :
   "⚠️ La météo annonce de la pluie samedi.
    Je te suggère des activités indoor, ça te va ?"
```

### Étape 4 : Préférences & Budget
```
IA: "Dernières questions pour affiner :
     - Type d'activité recherchée ?
       [Sportif] [Culturel] [Gastronomie] [Nature] [Détente] [Aventure]

     - Niveau d'énergie ?
       [😴 Relax] [😊 Modéré] [💪 Actif] [🔥 Intense]

     - Budget approximatif ?
       [< 50€] [50-100€] [100-200€] [> 200€] [Flexible]"
```

### Étape 5 : Recherche & Recommandations
```
IA: "🔍 Je cherche les meilleures options pour toi..."

[Appel MCP Tools en parallèle]
- search_events(criteria)
- check_availability(dates)
- filter_by_age(user_age, has_children)
- get_weather(location, dates)

IA: "✨ J'ai trouvé 3 activités parfaites pour toi :

     1. 🎨 ATELIER POTERIE - Samedi 14h
        📍 Centre-ville (8 min)
        💰 45€/personne
        👥 Adapté : Couples, Amis
        🌤️ Indoor (parfait vu la météo !)
        ⏱️ Durée : 2h30
        ⭐ 4.8/5 (127 avis)
        [Voir détails] [Réserver]

     2. 🧗 ESCALADE INDOOR - Samedi 10h
        📍 Zone Nord (12 min)
        💰 35€/personne
        👥 Adapté : Solo, Groupe
        🏠 Indoor
        ⏱️ Durée : 3h
        ⭐ 4.9/5 (234 avis)
        [Voir détails] [Réserver]

     3. 🍷 DÉGUSTATION VINS - Dimanche 16h
        📍 Périphérie (15 min)
        💰 60€/personne
        👥 Adapté : Couples (18+ uniquement) ⚠️
        🌤️ Indoor
        ⏱️ Durée : 2h
        ⭐ 4.7/5 (89 avis)
        [Voir détails] [Réserver]

     💡 Tu veux que je te crée un weekend complet ?"

[Autre chose] [Modifier critères] [Créer package weekend]
```

### Étape 6 : Package Weekend (Optionnel)
```
IA: "🎉 Voici ton weekend idéal :

     SAMEDI
     10h00 - Escalade Indoor (3h) - 35€
     13h30 - Déjeuner recommandé : Le Bistrot du Sport - 25€
     15h30 - Temps libre / Détente
     19h00 - Concert Jazz au Bar Central - 20€

     DIMANCHE
     11h00 - Brunch Gastronomique - 35€
     14h00 - Visite Musée d'Art Moderne - 12€
     16h30 - Atelier Poterie - 45€

     TOTAL : ~172€/personne
     TEMPS : 2 jours complets
     📍 Tous les lieux dans un rayon de 15 min

     [Réserver tout] [Modifier] [Partager]"
```

---

## 🛠️ MCP TOOLS CONFIGURATION

### Tool 1: search_events
```json
{
  "name": "search_events",
  "description": "Recherche des événements selon critères multiples",
  "parameters": {
    "type": "object",
    "properties": {
      "date_start": { "type": "string", "format": "date" },
      "date_end": { "type": "string", "format": "date" },
      "categories": { "type": "array", "items": { "type": "string" } },
      "min_age": { "type": "integer" },
      "max_age": { "type": "integer" },
      "group_type": { "type": "string", "enum": ["solo", "couple", "family", "friends", "group"] },
      "budget_min": { "type": "number" },
      "budget_max": { "type": "number" },
      "indoor_only": { "type": "boolean" },
      "energy_level": { "type": "string", "enum": ["relax", "moderate", "active", "intense"] },
      "max_distance_km": { "type": "number" },
      "limit": { "type": "integer", "default": 10 }
    },
    "required": ["date_start"]
  }
}
```

### Tool 2: filter_by_age
```json
{
  "name": "filter_by_age",
  "description": "Filtre les événements selon restrictions d'âge",
  "parameters": {
    "event_ids": { "type": "array", "items": { "type": "integer" } },
    "user_age": { "type": "integer" },
    "has_children": { "type": "boolean" },
    "children_ages": { "type": "array", "items": { "type": "integer" } }
  }
}
```

### Tool 3: get_weather
```json
{
  "name": "get_weather",
  "description": "Récupère prévisions météo pour dates/localisation",
  "parameters": {
    "location": { "type": "string" },
    "date": { "type": "string", "format": "date" },
    "latitude": { "type": "number" },
    "longitude": { "type": "number" }
  }
}
```

### Tool 4: suggest_itinerary
```json
{
  "name": "suggest_itinerary",
  "description": "Crée un itinéraire optimisé avec plusieurs événements",
  "parameters": {
    "event_ids": { "type": "array", "items": { "type": "integer" } },
    "start_date": { "type": "string", "format": "date" },
    "end_date": { "type": "string", "format": "date" },
    "optimize_by": { "type": "string", "enum": ["distance", "time", "cost", "popularity"] }
  }
}
```

---

## 🎨 INTERFACE FRONTEND - SPÉCIFICATIONS

### Composants Principaux
1. **ChatContainer**
   - Auto-scroll
   - Lazy loading messages
   - Virtual scrolling (si +100 messages)

2. **MessageBubble**
   - User messages (droite, bleu)
   - AI messages (gauche, gris clair)
   - System messages (centré, jaune léger)
   - Timestamps
   - Status indicators (sending, sent, error)

3. **TypingIndicator**
   - Animation 3 points
   - Visible pendant streaming

4. **QuickChips**
   - Boutons contextuels
   - Max 4-6 options visibles
   - Scroll horizontal mobile

5. **EventCard** (dans messages)
   - Image
   - Titre + description courte
   - Prix, date, durée
   - Rating
   - Distance
   - Boutons actions
   - Badge restrictions (âge, météo)

6. **ChatInput**
   - Textarea auto-resize
   - Max 2000 chars
   - Counter chars
   - Bouton envoi
   - Bouton micro (voice - phase 2)
   - Disabled pendant AI response

7. **WeatherAlert**
   - Bandeau info météo
   - Dismissible
   - Icône météo

### Responsive Design
- Mobile-first
- Breakpoints : 640px, 768px, 1024px
- Chat fullscreen sur mobile
- Chat popup/drawer sur desktop

### Accessibilité
- ARIA labels complets
- Navigation clavier
- Focus visible
- Screen reader friendly
- Contrast ratio WCAG AA

---

## 📊 ANALYTICS & TRACKING

### Métriques à Tracker
1. **Engagement**
   - Nombre conversations démarrées
   - Durée moyenne conversation
   - Nombre messages par conversation
   - Taux abandon (par étape)

2. **Conversion**
   - Taux de recommandations affichées
   - Taux de clics "Voir détails"
   - Taux de réservations
   - Valeur moyenne commande

3. **Qualité IA**
   - Pertinence recommandations (feedback user)
   - Erreurs IA
   - Temps réponse moyen
   - Coût par conversation

4. **Critères Utilisateurs**
   - Distribution âges
   - Types groupes populaires
   - Budgets moyens
   - Catégories préférées
   - Impact météo sur choix

### Dashboard Admin
- Graphiques temps réel
- Exports CSV
- Filtres date/type
- Heatmap horaires conversation

---

## 🔐 CHECKLIST SÉCURITÉ

### Avant Mise en Production
- [ ] Rate limiting activé (client + serveur)
- [ ] Validation inputs tous endpoints
- [ ] Sanitization outputs
- [ ] HTTPS forcé
- [ ] CORS configuré strict
- [ ] CSP headers configurés
- [ ] JWT secrets forts (256 bits min)
- [ ] Variables environnement sécurisées
- [ ] Logs anonymisés
- [ ] Backup automatique DB
- [ ] Monitoring erreurs (Sentry)
- [ ] Tests sécurité passés
- [ ] Audit code tiers
- [ ] RGPD compliant (mentions légales)
- [ ] Plan incident response

---

## 🚀 ROADMAP DÉVELOPPEMENT

### Phase 1 : MVP (2 semaines)
**Semaine 1**
- [x] Architecture & planification
- [ ] Setup plugin WordPress
- [ ] Interface chat frontend basique
- [ ] Intégration AI SDK
- [ ] 1 flow conversationnel simple
- [ ] Connexion OpenRouter

**Semaine 2**
- [ ] MCP Server basique
- [ ] 3 MCP tools essentiels
- [ ] Système sécurité basique
- [ ] Validation âge
- [ ] Tests unitaires critiques
- [ ] Déploiement staging

### Phase 2 : Features Avancées (2 semaines)
**Semaine 3**
- [ ] Système prompts modulaires complet
- [ ] Intégration météo
- [ ] Quick chips dynamiques
- [ ] EventCards enrichies
- [ ] Admin prompts editor
- [ ] Analytics basiques

**Semaine 4**
- [ ] Weekend planner
- [ ] Itinéraires optimisés
- [ ] Filtres avancés (accessibilité, etc.)
- [ ] Dashboard analytics complet
- [ ] A/B testing prompts
- [ ] Documentation complète

### Phase 3 : Premium (2 semaines)
**Semaine 5**
- [ ] Vacation planner
- [ ] Mode vocal (Speech-to-Text)
- [ ] Multilangue (FR/EN)
- [ ] Intégration paiement direct
- [ ] Notifications push
- [ ] Partage social

**Semaine 6**
- [ ] IA fine-tuning sur données
- [ ] Recommandations ML avancées
- [ ] Cache intelligent Redis
- [ ] Performance optimisation
- [ ] Tests charge
- [ ] Mise en production

---

## 💰 COÛTS ESTIMÉS

### Services Mensuels
- **OpenRouter (IA)** : ~50-200€/mois (selon volume)
  - GPT-4 Turbo : ~0.01€ / conversation
  - Claude 3.5 : ~0.015€ / conversation
  - Estimation : 5000 conversations/mois = ~50-75€

- **OpenWeatherMap** : Gratuit (1000 calls/jour) ou 40€/mois (pro)
- **Hosting Backend Node.js** : 10-50€/mois (Railway, Vercel, VPS)
- **Redis (optionnel)** : 10€/mois
- **Monitoring (Sentry)** : Gratuit tier dev

**Total estimé** : 70-300€/mois selon volume

### ROI Attendu
- Augmentation conversions : +30-50%
- Réduction support client : -40%
- Temps recherche utilisateur : -70%
- Panier moyen : +25% (upsell packages)

---

## 📚 RÉFÉRENCES & RESSOURCES

### Documentation Technique
- AI SDK : https://sdk.vercel.ai/docs
- MCP Protocol : https://modelcontextprotocol.io
- OpenRouter : https://openrouter.ai/docs
- WordPress REST API : https://developer.wordpress.org/rest-api/

### Design Inspiration
- Intercom chat
- ChatGPT interface
- Drift conversational marketing
- Airbnb search experience

### Sécurité
- OWASP Top 10 : https://owasp.org/www-project-top-ten/
- WordPress Security Guide
- Rate Limiting Best Practices

---

## 📝 NOTES IMPORTANTES

1. **Priorité Absolue** : Sécurité avant features
2. **UX First** : Interface doit être intuitive même pour 70+ ans
3. **Performance** : < 2s réponse IA perçue (streaming aide)
4. **Mobile** : 70% trafic mobile → Mobile First obligatoire
5. **Accessibilité** : WCAG AA minimum
6. **RGPD** : Anonymisation conversations, opt-out facile
7. **Monitoring** : Logs détaillés mais anonymes
8. **Coûts IA** : Monitoring quotidien, alertes si dépassement

---

## 🆘 SUPPORT & CONTACT

### En cas de problème technique
1. Vérifier logs : `/wp-content/debug.log`
2. Vérifier logs Node.js : `pm2 logs lehiboo-ai`
3. Monitoring : Sentry dashboard
4. Tests sécurité : `npm run test:security`

### Contacts
- Développeur principal : [À définir]
- Support OpenRouter : support@openrouter.ai
- Urgences sécurité : [À définir]

---

**Dernière mise à jour** : 2025-10-27
**Version document** : 1.0.0
