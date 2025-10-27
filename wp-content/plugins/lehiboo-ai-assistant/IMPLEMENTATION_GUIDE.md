# 🚀 Guide d'Implémentation - Le Hiboo AI Assistant

**Date de création:** 2025-10-27
**Status:** Phase 1 - Frontend Interface terminée ✅

---

## 📋 RÉCAPITULATIF DU PROJET

### Ce qui a été créé

✅ **Plugin WordPress complet** avec structure professionnelle
✅ **Interface chat frontend moderne** (CSS + JavaScript)
✅ **Système de sécurité robuste** (rate limiting, validation, protection XSS/injection)
✅ **Système de prompts modulaires** (YAML éditables)
✅ **Documentation complète** (Architecture, Sécurité, README)
✅ **Classes PHP** (Security, Rate Limiter, Plugin principal)

### Ce qui reste à implémenter

🔲 **Backend API Node.js** (serveur séparé avec AI SDK + OpenRouter)
🔲 **MCP Server** (connexion à WordPress/EventList)
🔲 **API REST WordPress complète** (endpoint chat fonctionnel)
🔲 **Admin WordPress** (interface configuration)
🔲 **Intégration météo** (OpenWeatherMap API)
🔲 **Tests & déploiement**

---

## 🎯 PROCHAINES ÉTAPES PRIORITAIRES

### ÉTAPE 1: Compléter l'API REST WordPress (1-2h)

#### Fichier à créer: `api/chat-endpoint.php`

```php
<?php
/**
 * Chat REST API Endpoint Handler
 */

if (!defined('ABSPATH')) exit;

class Lehiboo_AI_Chat_Endpoint {

    public function handle_request($request) {
        // 1. Valider la sécurité
        $security = new Lehiboo_AI_Security();
        $rate_limiter = new Lehiboo_AI_Rate_Limiter();

        // 2. Vérifier rate limit
        $rate_check = $rate_limiter->enforce_limit();
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        // 3. Récupérer et valider les données
        $message = $request->get_param('message');
        $conversation_id = $request->get_param('conversationId');
        $user_context = $request->get_param('userContext');

        // 4. Sanitize
        $message = $security->sanitize_message($message);

        // 5. Validate
        $validation = $security->validate_message($message);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // 6. Appeler le backend API Node.js
        $response = $this->call_ai_backend($message, $conversation_id, $user_context);

        // 7. Retourner la réponse
        return rest_ensure_response($response);
    }

    private function call_ai_backend($message, $conversation_id, $user_context) {
        // TODO: Implémenter appel vers backend Node.js
        $backend_url = get_option('lehiboo_ai_backend_url');
        $api_key = get_option('lehiboo_ai_api_key');

        $response = wp_remote_post($backend_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'body' => json_encode(array(
                'message' => $message,
                'conversationId' => $conversation_id,
                'userContext' => $user_context,
            )),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Erreur de connexion au serveur IA');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body;
    }
}
```

**Action:** Créer ce fichier et le compléter

---

### ÉTAPE 2: Créer le Backend API Node.js (4-6h)

#### Structure du projet backend:

```
lehiboo-ai-backend/
├── package.json
├── .env
├── server.js
├── src/
│   ├── routes/
│   │   └── chat.js
│   ├── services/
│   │   ├── ai-service.js
│   │   ├── mcp-service.js
│   │   └── prompt-service.js
│   ├── middleware/
│   │   ├── auth.js
│   │   ├── rate-limit.js
│   │   └── validator.js
│   └── utils/
│       ├── logger.js
│       └── security.js
└── mcp-tools/
    ├── search-events.js
    ├── filter-by-age.js
    └── get-weather.js
```

#### package.json de base:

```json
{
  "name": "lehiboo-ai-backend",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  },
  "dependencies": {
    "@ai-sdk/openai": "^0.0.54",
    "@modelcontextprotocol/sdk": "^0.5.0",
    "ai": "^3.3.0",
    "express": "^4.19.2",
    "express-rate-limit": "^7.1.5",
    "zod": "^3.22.4",
    "winston": "^3.11.0",
    "dotenv": "^16.3.1",
    "cors": "^2.8.5",
    "axios": "^1.6.0"
  },
  "devDependencies": {
    "nodemon": "^3.0.2"
  }
}
```

#### server.js minimal:

```javascript
import express from 'express';
import cors from 'cors';
import { config } from 'dotenv';
import chatRouter from './src/routes/chat.js';
import { errorHandler } from './src/middleware/error-handler.js';
import { setupRateLimiting } from './src/middleware/rate-limit.js';

config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors({
  origin: process.env.ALLOWED_ORIGINS?.split(',') || ['http://localhost'],
  credentials: true
}));
app.use(express.json({ limit: '10kb' }));

// Rate limiting
setupRateLimiting(app);

// Routes
app.use('/api/chat', chatRouter);

app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Error handling
app.use(errorHandler);

app.listen(PORT, () => {
  console.log(`🚀 Le Hiboo AI Backend running on port ${PORT}`);
});
```

#### .env.example:

```env
# API Keys
OPENROUTER_API_KEY=your_openrouter_key
OPENWEATHER_API_KEY=your_openweather_key

# Server Config
PORT=3000
NODE_ENV=development
ALLOWED_ORIGINS=http://localhost,https://lehiboo.com

# WordPress Integration
WORDPRESS_API_URL=https://lehiboo.com/wp-json
WORDPRESS_API_TOKEN=your_wp_token

# AI Config
DEFAULT_AI_MODEL=anthropic/claude-3.5-sonnet
MAX_TOKENS=4000
TEMPERATURE=0.7

# Rate Limiting
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20

# Logging
LOG_LEVEL=info
```

**Action:** Créer cette structure et implémenter le serveur de base

---

### ÉTAPE 3: Implémenter MCP Tools (2-3h)

#### Exemple: search-events.js

```javascript
import { Tool } from '@modelcontextprotocol/sdk';
import axios from 'axios';

export const searchEventsTool = new Tool({
  name: 'search_events',
  description: 'Recherche des événements selon critères multiples',
  inputSchema: {
    type: 'object',
    properties: {
      date_start: { type: 'string', format: 'date' },
      date_end: { type: 'string', format: 'date' },
      categories: { type: 'array', items: { type: 'string' } },
      min_age: { type: 'integer' },
      max_age: { type: 'integer' },
      group_type: {
        type: 'string',
        enum: ['solo', 'couple', 'family', 'friends', 'group']
      },
      budget_min: { type: 'number' },
      budget_max: { type: 'number' },
      indoor_only: { type: 'boolean' },
      max_distance_km: { type: 'number' },
      limit: { type: 'integer', default: 10 }
    },
    required: ['date_start']
  },

  async execute(params) {
    try {
      // Appeler WordPress REST API
      const response = await axios.get(
        `${process.env.WORDPRESS_API_URL}/el/v1/events`,
        {
          params: {
            date_start: params.date_start,
            date_end: params.date_end,
            categories: params.categories?.join(','),
            per_page: params.limit || 10,
            // ... autres paramètres
          },
          headers: {
            'Authorization': `Bearer ${process.env.WORDPRESS_API_TOKEN}`
          }
        }
      );

      // Filtrer selon critères supplémentaires
      let events = response.data;

      if (params.min_age) {
        events = events.filter(e => !e.min_age || e.min_age <= params.min_age);
      }

      if (params.budget_max) {
        events = events.filter(e => e.price <= params.budget_max);
      }

      return {
        success: true,
        events: events,
        count: events.length
      };

    } catch (error) {
      console.error('Error searching events:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }
});
```

**Action:** Créer tous les MCP tools nécessaires (voir ARCHITECTURE.md)

---

### ÉTAPE 4: Intégrer AI SDK + OpenRouter (2-3h)

#### src/services/ai-service.js

```javascript
import { openai } from '@ai-sdk/openai';
import { streamText, tool } from 'ai';
import { loadSystemPrompt } from './prompt-service.js';
import { searchEventsTool } from '../../mcp-tools/search-events.js';
import { filterByAgeTool } from '../../mcp-tools/filter-by-age.js';
import { getWeatherTool } from '../../mcp-tools/get-weather.js';

export class AIService {

  async generateResponse(message, conversationHistory, userContext) {
    // Charger le prompt système
    const systemPrompt = await loadSystemPrompt(userContext);

    // Préparer les messages
    const messages = [
      { role: 'system', content: systemPrompt },
      ...conversationHistory,
      { role: 'user', content: message }
    ];

    // Appeler l'IA avec MCP tools
    const result = await streamText({
      model: openai(process.env.DEFAULT_AI_MODEL, {
        apiKey: process.env.OPENROUTER_API_KEY,
        baseURL: 'https://openrouter.ai/api/v1'
      }),
      messages: messages,
      tools: {
        search_events: searchEventsTool,
        filter_by_age: filterByAgeTool,
        get_weather: getWeatherTool
      },
      maxTokens: parseInt(process.env.MAX_TOKENS),
      temperature: parseFloat(process.env.TEMPERATURE),
    });

    return result;
  }
}
```

**Action:** Implémenter le service IA complet avec streaming

---

### ÉTAPE 5: Admin WordPress (2-3h)

Créer interface admin pour configurer :
- ✅ Activation on/off
- ✅ URL backend API
- ✅ API keys
- ✅ Rate limiting params
- ✅ Édition prompts YAML
- ✅ Visualisation analytics

**Fichier:** `admin/class-admin-settings.php`

---

### ÉTAPE 6: Tests & Déploiement (2-4h)

#### Tests à effectuer:
- [ ] Tests sécurité (XSS, injection, rate limiting)
- [ ] Tests fonctionnels (flow conversation)
- [ ] Tests intégration (WordPress ↔ Backend)
- [ ] Tests performance (charge, temps réponse)
- [ ] Tests mobile (responsive)

#### Déploiement:
1. **Backend Node.js:** Déployer sur Railway, Vercel, ou VPS
2. **Plugin WordPress:** Activer sur production
3. **Monitoring:** Configurer Sentry/logs
4. **DNS/SSL:** Configurer domaine API

---

## 📊 ESTIMATION TEMPS TOTAL

| Phase | Temps estimé | Difficulté |
|-------|-------------|------------|
| ✅ Phase 1: Frontend & Structure | 6-8h | Moyenne |
| 🔲 Phase 2: API REST WordPress | 1-2h | Facile |
| 🔲 Phase 3: Backend Node.js | 4-6h | Moyenne |
| 🔲 Phase 4: MCP Tools | 2-3h | Moyenne |
| 🔲 Phase 5: Intégration IA | 2-3h | Difficile |
| 🔲 Phase 6: Admin WordPress | 2-3h | Facile |
| 🔲 Phase 7: Tests & Debug | 2-4h | Moyenne |
| 🔲 Phase 8: Déploiement | 2-4h | Moyenne |

**TOTAL: 21-33 heures de développement**

---

## 💰 COÛTS ESTIMÉS

### Développement
- **Temps dev:** 21-33h × votre taux horaire

### Services Mensuels (production)
- **OpenRouter (IA):** ~50-200€/mois selon volume
  - Claude 3.5 Sonnet: ~0.015€/conversation
  - GPT-4 Turbo: ~0.01€/conversation
  - Estimation: 5000 conversations/mois = ~75€

- **Hosting Backend Node.js:**
  - Railway: 5-20€/mois
  - Vercel: Gratuit tier dev, 20€+ pro
  - VPS (Hetzner, DigitalOcean): 5-10€/mois

- **OpenWeatherMap API:**
  - Gratuit: 1000 appels/jour
  - Pro: 40€/mois si dépassement

- **Monitoring (Sentry):** Gratuit tier dev

**TOTAL MENSUEL: 60-270€** selon volume et options

---

## 🎯 CRITÈRES DE SUCCÈS

### Techniques
- ✅ Interface chat réactive < 100ms
- ✅ Réponse IA < 3s (perçue instantanée grâce au streaming)
- ✅ 0 faille de sécurité (tests passed)
- ✅ Uptime > 99.5%
- ✅ Mobile-friendly score > 90

### Business
- 📈 Augmentation conversions: +30-50%
- 📉 Réduction taux rebond: -25%
- ⏱️ Temps recherche utilisateur: -70%
- 💰 Panier moyen: +25% (upsell packages)
- 😊 Satisfaction utilisateurs: > 4.5/5

---

## 🚨 RISQUES & MITIGATIONS

| Risque | Impact | Mitigation |
|--------|--------|------------|
| Coûts IA explosent | 💰💰💰 | Rate limiting strict, cache réponses, alertes budget |
| Faille sécurité | 🔴 Critical | Audits réguliers, tests automatisés, bug bounty |
| Backend down | 🔴 Critical | Monitoring 24/7, fallback message, auto-restart |
| IA répond n'importe quoi | 🟡 Medium | Prompts robustes, validation outputs, feedback loop |
| Mauvaise UX mobile | 🟡 Medium | Tests utilisateurs, A/B testing, iterations |

---

## 📞 SUPPORT & RESSOURCES

### Documentation
- **ARCHITECTURE.md** - Architecture technique complète
- **SECURITY.md** - Guide sécurité détaillé
- **README.md** - Guide utilisateur

### Liens Utiles
- AI SDK Docs: https://sdk.vercel.ai/docs
- MCP Protocol: https://modelcontextprotocol.io
- OpenRouter: https://openrouter.ai/docs
- WordPress REST API: https://developer.wordpress.org/rest-api/

### Code Examples
- AI SDK Examples: https://github.com/vercel/ai/tree/main/examples
- MCP Servers: https://github.com/modelcontextprotocol/servers

---

## ✅ CHECKLIST FINALE AVANT MISE EN PRODUCTION

### Code
- [ ] Tous les fichiers créés et fonctionnels
- [ ] Code commenté et documenté
- [ ] Tests unitaires passent
- [ ] Tests d'intégration passent
- [ ] Pas de console.log en production

### Sécurité
- [ ] HTTPS partout
- [ ] Rate limiting actif
- [ ] Validation inputs complète
- [ ] Headers sécurité configurés
- [ ] Secrets dans variables d'environnement
- [ ] Audit sécurité effectué

### Performance
- [ ] Temps réponse < 3s
- [ ] Interface réactive
- [ ] Cache activé
- [ ] CDN configuré
- [ ] Images optimisées

### Monitoring
- [ ] Logs configurés
- [ ] Sentry/monitoring actif
- [ ] Alertes configurées
- [ ] Dashboard analytics opérationnel

### Documentation
- [ ] README à jour
- [ ] Guide admin rédigé
- [ ] Changelog maintenu
- [ ] Procédures urgence documentées

### Legal
- [ ] Mentions légales RGPD
- [ ] Politique confidentialité
- [ ] CGU mises à jour
- [ ] Consentement cookies

---

## 🎉 CONCLUSION

**Vous avez maintenant:**
✅ Une base solide et professionnelle
✅ Une architecture scalable et sécurisée
✅ Une roadmap claire pour la suite
✅ Toute la documentation nécessaire

**Prochaine action immédiate:**
👉 Commencer par l'ÉTAPE 2 (API REST WordPress)
👉 Puis ÉTAPE 3 (Backend Node.js)

**Besoin d'aide ?**
N'hésitez pas à consulter la documentation ou à demander de l'aide sur les points bloquants.

---

**Bonne chance pour la suite de l'implémentation ! 🚀**

*Fait avec ❤️ pour Le Hiboo*
