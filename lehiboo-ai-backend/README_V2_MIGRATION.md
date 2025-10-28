# Migration vers AI Service V2

## Changements majeurs

### Ancien système (ai-service.js)
- System prompt YAML basique
- Pas de tools fonctionnels
- Collecte lente (1 info par message)
- Pas de recherche réelle d'événements

### Nouveau système (ai-service-v2.js)
- System prompt v2.md (880 lignes, expertise Hedwige)
- 2 tools fonctionnels (collectUserProfile, searchEvents)
- Collecte groupée (3-4 infos par message)
- Recherche réelle via WordPress API

## Comment migrer

### Étape 1: Tester le nouveau service

```javascript
// Dans votre code, importer le nouveau service
import { generateAIResponse } from './services/ai-service-v2.js';

// Au lieu de
import { generateAIResponse } from './services/ai-service.js';
```

### Étape 2: Adapter les routes

**Fichier**: `src/routes/chat.js` ou équivalent

```javascript
// Avant
import { generateAIResponse, streamAIResponse } from '../services/ai-service.js';

// Après
import { generateAIResponse, streamAIResponse } from '../services/ai-service-v2.js';
```

### Étape 3: Vérifier le contexte

Le nouveau service attend:

```javascript
const context = {
  conversationId: 'conv_123',
  history: [
    { role: 'user', content: 'Bonjour' },
    { role: 'assistant', content: 'Bonjour ! Je suis Hedwige...' }
  ],
  userContext: {}, // Sera rempli automatiquement par collectUserProfile
  currentStage: 'greeting' // Optionnel, géré automatiquement
};

const response = await generateAIResponse(userMessage, context);
```

### Étape 4: Configurer WordPress API Key

Dans WordPress admin, ajouter l'option:

```php
update_option('lehiboo_ai_api_key', 'votre-cle-secrete-ici');
```

Ou dans `.env` backend:

```env
WORDPRESS_API_KEY=votre-cle-secrete-ici
```

## Déroulement d'une conversation type

### Message 1 - Greeting
```
User: [Ouvre le chat]

Hedwige (tool collectUserProfile appelé automatiquement):
"Bonjour ! Je suis Hedwige 🦉, votre assistante Le Hiboo.

Pour vous trouver l'activité parfaite rapidement, j'ai besoin de savoir :
- C'est pour qui ? (solo / couple / famille / amis)
- Quel âge avez-vous ?
- Quelle ville et rayon de recherche ?
- Quand souhaitez-vous y aller ?"

[Quick Chips: Solo | Couple | Famille | Amis]

→ Tool collectUserProfile retourne: completeness=0%, missingFields=6
```

### Message 2 - Collecte groupée
```
User: "En couple, 30 ans, Paris, ce weekend"

Hedwige (tool collectUserProfile appelé):
"Parfait ! Paris ce weekend pour 2 personnes ✓

Dernières questions :
- Quel type d'activité ? (sport / culture / gastronomie / nature / détente)
- Budget max par personne ?"

[Quick Chips: Culture | Sport | Gastronomie | Nature | Détente]

→ Tool collectUserProfile retourne: completeness=67% (4/6), missingFields=['activityType', 'budgetMax']
```

### Message 3 - Recherche auto
```
User: "Culture, 50€ max"

Hedwige (tools appelés automatiquement):
1. collectUserProfile → completeness=100% (6/6) ✓
2. searchEvents appelé automatiquement → 5 événements trouvés ✓

"🔍 Hedwige a trouvé 5 activités culturelles à Paris ce weekend pour 2 personnes,
   toutes sous 50€ !

[Event Cards affichées]

✨ Mes recommandations de guide locale :

🎭 **Théâtre Mogador (45€)** - Mon coup de cœur pour les couples
   → Spectacle intimiste, ambiance magique
   → Conseil: Arrivez 20min avant pour un verre au bar du théâtre
   → Quartier: Opéra, parfait pour dîner après...

[3 autres activités avec conseils détaillés]"

→ Résultats en 3 messages total ✅
```

## Debugging

### Voir les tool calls

Les logs montrent automatiquement:

```
info: AI response generated {
  conversationId: 'conv_123',
  tokensUsed: 1850,
  toolCallsCount: 2,
  steps: 3
}

info: Tools called by AI {
  tools: [
    { name: 'collectUserProfile', argsPreview: '{"groupType":"couple","age":30,...}' },
    { name: 'searchEvents', argsPreview: '{"userProfile":{"groupType":"couple",...}' }
  ]
}
```

### Tester manuellement un tool

```javascript
import { collectUserProfile } from './tools/collect-user-profile.js';

const result = await collectUserProfile({
  groupType: 'couple',
  age: 30,
  location: { city: 'Paris' },
  dates: { type: 'thisWeekend' },
  activityType: 'culture',
  budgetMax: 50
});

console.log(result);
// {
//   success: true,
//   completeness: 100,
//   message: 'Profil complet ✓ ...',
//   isComplete: true
// }
```

### Tester WordPress API

```bash
curl -X POST https://preprod.lehiboo.com/wp-json/lehiboo/v1/events/search \
  -H "Authorization: Bearer votre-cle-api" \
  -H "Content-Type: application/json" \
  -d '{
    "city": "Paris",
    "maxPrice": 50,
    "category": "culture",
    "startDate": "2025-11-01",
    "endDate": "2025-11-02",
    "limit": 5
  }'
```

## Rollback si problème

Si besoin de revenir à l'ancien système:

```javascript
// Simplement re-importer l'ancien service
import { generateAIResponse } from './services/ai-service.js';
```

L'ancien fichier reste intact.

## Performance attendue

### Avant (ai-service.js)
- Messages avant résultats: 10-15
- Temps réponse moyen: 3-5s
- Taux recherche réussie: ~30%
- Tools calls: 0

### Après (ai-service-v2.js)
- Messages avant résultats: 2-3 ✅
- Temps réponse moyen: 2-3s ✅
- Taux recherche réussie: >90% ✅
- Tools calls: 2-3 par conversation ✅

## Checklist avant prod

- [ ] System prompt v2.md présent et lisible
- [ ] Tools importent correctement (pas d'erreur Zod)
- [ ] WordPress API répond (test curl)
- [ ] API key configurée
- [ ] Logs montrent tool calls
- [ ] Test conversation complète 1-3 messages
- [ ] Events retournés sont corrects
- [ ] Budget respecté (filtrage strict)
- [ ] Backup de l'ancien système fait

## Support

En cas de problème:
1. Vérifier les logs backend (`pm2 logs` ou équivalent)
2. Tester WordPress API directement (curl)
3. Vérifier que system-prompt-v2.md est présent
4. Vérifier les imports tools (erreurs Zod?)
5. Rollback vers ai-service.js si critique

---

Version: 2.0
Date: 2025-10-29
