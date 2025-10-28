# Guide de Test - AI Service V2

## Pré-requis

### 1. Configuration OpenAI

Mettre à jour `.env` avec votre clé OpenAI :

```bash
OPENAI_API_KEY=sk-proj-votre-cle-openai-ici
DEFAULT_MODEL=gpt-4o
```

### 2. Configuration WordPress API Key

Dans WordPress admin, exécuter :

```php
update_option('lehiboo_ai_api_key', 'votre-cle-secrete-ici');
```

OU ajouter dans `.env` :

```bash
WORDPRESS_API_KEY=votre-cle-secrete-ici
```

---

## Tests Unitaires des Tools

### Test 1: collectUserProfile Tool

```bash
node -e "
import('./src/tools/collect-user-profile.js').then(async ({ collectUserProfile }) => {
  const result = await collectUserProfile({
    groupType: 'couple',
    age: 30,
    location: { city: 'Paris', radius: 20 },
    dates: { type: 'thisWeekend' },
    activityType: 'culture',
    budgetMax: 50
  });
  console.log('✅ collectUserProfile test:');
  console.log(JSON.stringify(result, null, 2));
});
"
```

**Résultat attendu :**
```json
{
  "success": true,
  "completeness": 100,
  "isComplete": true,
  "message": "Profil complet ✓ (6/6) - Prêt pour la recherche !"
}
```

---

### Test 2: searchEvents Tool

```bash
node -e "
import('./src/tools/search-events.js').then(async ({ searchEvents }) => {
  const result = await searchEvents({
    userProfile: {
      groupType: 'couple',
      age: 30,
      location: { city: 'Paris', radius: 20 },
      dates: { type: 'thisWeekend' },
      activityType: 'culture',
      budgetMax: 50
    },
    limit: 5
  });
  console.log('✅ searchEvents test:');
  console.log(JSON.stringify(result, null, 2));
});
"
```

**Résultat attendu :**
```json
{
  "success": true,
  "events": [
    {
      "id": "123",
      "title": "Théâtre Mogador",
      "price": 45,
      "category": "culture",
      "matchScore": 0.9
    }
  ],
  "totalFound": 5
}
```

---

## Tests d'Intégration

### Test 3: Backend Health Check

```bash
npm run dev
```

Dans un autre terminal :

```bash
curl http://localhost:3000/health
```

**Résultat attendu :**
```json
{
  "status": "ok",
  "timestamp": "2025-10-29T...",
  "version": "1.0.0"
}
```

---

### Test 4: Conversation Complète (3 messages)

#### Message 1 - Greeting

```bash
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test_conv_001",
    "history": []
  }'
```

**Résultat attendu :**
- Hedwige se présente comme "Hedwige 🦉"
- Demande le type de groupe
- Retourne des quickChips: Solo | Couple | Famille | Amis
- Tool `collectUserProfile` appelé automatiquement

---

#### Message 2 - Collecte Groupée

```bash
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "En couple, 30 ans, Paris, ce weekend",
    "conversationId": "test_conv_001",
    "userContext": {
      "groupType": "couple"
    },
    "history": [...]
  }'
```

**Résultat attendu :**
- Hedwige confirme les infos reçues
- Demande le type d'activité et le budget
- Retourne des quickChips: Culture | Sport | Gastronomie | Nature | Détente
- Tool `collectUserProfile` retourne `completeness: 67%` (4/6 champs)

---

#### Message 3 - Recherche Automatique

```bash
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "Culture, 50€ max",
    "conversationId": "test_conv_001",
    "userContext": {
      "groupType": "couple",
      "age": 30,
      "location": { "city": "Paris" },
      "dates": { "type": "thisWeekend" }
    },
    "history": [...]
  }'
```

**Résultat attendu :**
- Tool `collectUserProfile` retourne `completeness: 100%` (6/6 champs)
- Tool `searchEvents` appelé automatiquement
- Hedwige retourne 3-5 événements avec :
  - Event Cards (id, title, image, price, date, location)
  - Conseils personnalisés de guide locale
  - Prix strictement <= 50€
- `usage.toolCalls: 2` dans les logs

---

## Tests de Validation

### ✅ Checklist Avant Prod

- [ ] System prompt v2 présent et lisible
- [ ] Tools importent correctement (pas d'erreur Zod)
- [ ] WordPress API répond (test curl direct)
- [ ] API key configurée dans WordPress
- [ ] Logs montrent tool calls
- [ ] Test conversation complète 1-3 messages
- [ ] Events retournés sont corrects
- [ ] Budget respecté (filtrage strict maxPrice <= budgetMax)
- [ ] Hedwige se présente comme "Hedwige 🦉"
- [ ] Collecte groupée fonctionne (3-4 infos par message)
- [ ] Backup de l'ancien système fait

---

## Debugging

### Voir les logs détaillés

```bash
npm run dev
```

Les logs montreront automatiquement :

```
info: AI response generated {
  conversationId: 'test_conv_001',
  tokensUsed: 1850,
  toolCallsCount: 2,
  steps: 3
}

info: Tools called by AI {
  tools: [
    { name: 'collectUserProfile', argsPreview: '{"groupType":"couple",...}' },
    { name: 'searchEvents', argsPreview: '{"userProfile":{"groupType":"couple",...}' }
  ]
}
```

---

### Test WordPress API Directement

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

**Résultat attendu :**
```json
{
  "success": true,
  "events": [...],
  "totalFound": 5
}
```

---

## Métriques de Performance Attendues

### Avant (ai-service.js)
- Messages avant résultats: **10-15** ❌
- Temps réponse moyen: **3-5s** ⚠️
- Taux recherche réussie: **~30%** ❌
- Tools calls: **0** ❌

### Après (ai-service-v2.js)
- Messages avant résultats: **2-3** ✅
- Temps réponse moyen: **2-3s** ✅
- Taux recherche réussie: **>90%** ✅
- Tools calls: **2-3 par conversation** ✅

---

## Rollback d'Urgence

Si problème critique, revenir à l'ancien système :

```javascript
// Dans src/controllers/chat-controller.js
import { generateAIResponse } from '../services/ai-service.js'; // Ancien service
```

Et restaurer `.env` :

```bash
OPENROUTER_API_KEY=sk-or-v1-...
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

---

## Support

En cas de problème :

1. **Vérifier les logs backend** : `npm run dev` ou `pm2 logs`
2. **Tester WordPress API directement** : curl avec Authorization header
3. **Vérifier que system-prompt-v2.md est présent** : `ls -lh src/prompts/system-prompt-v2.md`
4. **Vérifier les imports tools** : Erreurs Zod dans les logs ?
5. **Rollback vers ai-service.js** si critique

---

Version: 2.0
Date: 2025-10-29
