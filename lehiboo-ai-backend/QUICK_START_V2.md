# Quick Start - AI Service V2

**Version :** 2.0.0 | **Date :** 2025-10-29 | **Status :** ✅ READY

---

## 🚀 Démarrage Rapide (5 minutes)

### Étape 1 : Configuration OpenAI (1 min)

```bash
# Éditer .env
nano .env

# Ajouter votre clé OpenAI
OPENAI_API_KEY=sk-proj-votre-cle-openai-ici
DEFAULT_MODEL=gpt-4o
```

**Obtenir une clé :** https://platform.openai.com/api-keys

---

### Étape 2 : Configuration WordPress API Key (1 min)

**Option A - WordPress Admin (recommandé) :**
```php
// Dans WordPress admin > Outils > PHP Snippet
update_option('lehiboo_ai_api_key', 'votre-cle-secrete-ici');
```

**Option B - Backend .env :**
```bash
WORDPRESS_API_KEY=votre-cle-secrete-ici
```

---

### Étape 3 : Démarrer le Serveur (1 min)

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend

# Installer les dépendances (si pas déjà fait)
npm install

# Démarrer en mode dev
npm run dev
```

**Logs attendus :**
```
✅ OpenAI connection successful
🚀 Le Hiboo AI Backend started
   Model: gpt-4o
   Port: 3000
```

---

### Étape 4 : Test Rapide (2 min)

**Test 1 - Health Check :**
```bash
curl http://localhost:3000/health
```

**Résultat attendu :**
```json
{"status": "ok", "timestamp": "2025-10-29T...", "version": "1.0.0"}
```

---

**Test 2 - Conversation Complète (3 messages) :**

```bash
# Message 1 - Greeting
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test_001",
    "history": []
  }'
```

**Résultat attendu :**
- ✅ Hedwige se présente : "Je suis Hedwige 🦉"
- ✅ Demande le type de groupe
- ✅ Quick chips affichés : Solo | Couple | Famille | Amis
- ✅ Logs montrent : `toolCallsCount: 1` (collectUserProfile)

---

```bash
# Message 2 - Collecte groupée
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "En couple, 30 ans, Paris, ce weekend",
    "conversationId": "test_001",
    "userContext": {"groupType": "couple"},
    "history": [
      {"role": "user", "content": "Bonjour"},
      {"role": "assistant", "content": "Bonjour ! Je suis Hedwige..."}
    ]
  }'
```

**Résultat attendu :**
- ✅ Hedwige confirme : "Paris ce weekend pour 2 personnes ✓"
- ✅ Demande type d'activité et budget
- ✅ Quick chips : Culture | Sport | Gastronomie | Nature | Détente
- ✅ Logs montrent : `completeness: 67% (4/6)`

---

```bash
# Message 3 - Recherche automatique
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: your-api-key-here" \
  -d '{
    "message": "Culture, 50€ max",
    "conversationId": "test_001",
    "userContext": {
      "groupType": "couple",
      "age": 30,
      "location": {"city": "Paris"},
      "dates": {"type": "thisWeekend"}
    },
    "history": [...]
  }'
```

**Résultat attendu :**
- ✅ Hedwige annonce : "🔍 Hedwige a trouvé 5 activités culturelles..."
- ✅ Events retournés (3-5 max)
- ✅ Tous les events <= 50€ (budget strict)
- ✅ Conseils expert personnalisés
- ✅ Logs montrent : `toolCallsCount: 2` (collectUserProfile + searchEvents)

---

## ✅ Checklist Validation

Si tous ces points sont ✅, le système est opérationnel :

- [ ] Serveur démarre sans erreur
- [ ] Logs montrent "OpenAI connection successful"
- [ ] Health check répond 200 OK
- [ ] Message 1 : Hedwige se présente comme "Hedwige 🦉"
- [ ] Message 2 : Collecte groupée (3-4 infos)
- [ ] Message 3 : Recherche auto avec events
- [ ] Budget 100% respecté (aucun event > budgetMax)
- [ ] Logs montrent 2 tools appelés
- [ ] Temps réponse < 3s par message

---

## 🔍 Debugging Rapide

### Problème 1 : "OpenAI connection failed"
**Cause :** Clé API invalide ou non configurée

**Solution :**
```bash
# Vérifier .env
cat .env | grep OPENAI_API_KEY

# Doit afficher : OPENAI_API_KEY=sk-proj-...
# Si vide ou "sk-your-openai-api-key-here", mettre à jour
```

---

### Problème 2 : "No events found"
**Cause :** WordPress API non accessible ou pas d'events

**Solution :**
```bash
# Tester WordPress API directement
curl -X POST https://preprod.lehiboo.com/wp-json/lehiboo/v1/events/search \
  -H "Authorization: Bearer votre-cle-api" \
  -H "Content-Type: application/json" \
  -d '{"city": "Paris", "maxPrice": 100, "limit": 5}'

# Si erreur 401 : Vérifier WORDPRESS_API_KEY
# Si erreur 404 : Plugin lehiboo-ai-assistant non actif
# Si success: true mais events: [] : Pas d'events dans WordPress
```

---

### Problème 3 : "Tools not called"
**Cause :** System prompt v2 non chargé ou tools mal configurés

**Solution :**
```bash
# Vérifier que system-prompt-v2.md existe
ls -lh src/prompts/system-prompt-v2.md

# Doit afficher : ~70-80KB (880 lignes)

# Vérifier les logs backend
npm run dev

# Chercher dans les logs :
# "System prompt loaded: 880 lines"
# "Tools registered: collectUserProfile, searchEvents"
```

---

### Problème 4 : "Budget not respected"
**Cause :** WordPress API ne filtre pas correctement

**Solution :**
```php
// Dans class-events-api.php, vérifier la meta_query :
$args['meta_query'][] = array(
    'key' => 'event_price',
    'value' => floatval($params['maxPrice']),
    'type' => 'NUMERIC',
    'compare' => '<='  // ← IMPORTANT : Doit être <=
);
```

---

## 📚 Documentation Complète

Pour plus de détails, consulter :

| Fichier | Contenu |
|---------|---------|
| [STATUS_V2.md](./STATUS_V2.md) | État actuel détaillé + checklist |
| [TESTING_V2.md](./TESTING_V2.md) | Guide de test complet |
| [README_V2_MIGRATION.md](./README_V2_MIGRATION.md) | Guide de migration |
| [CHANGELOG_V2.md](./CHANGELOG_V2.md) | Tous les changements |
| [ROADMAP_REFONTE.md](./ROADMAP_REFONTE.md) | Roadmap 3 sprints |

---

## 🚨 Rollback d'Urgence (5 min)

Si problème critique :

```javascript
// 1. src/controllers/chat-controller.js
import { generateAIResponse } from '../services/ai-service.js'; // Ancien

// 2. src/index.js
import { testOpenAIConnection } from '../services/ai-service.js'; // Ancien

// 3. Redémarrer
pm2 restart lehiboo-ai-backend
```

---

## 🎯 Métriques Attendues

| Métrique | V1 (avant) | V2 (après) |
|----------|------------|------------|
| Messages avant résultats | 10-15 | **2-3** ✅ |
| Temps réponse | 3-5s | **2-3s** ✅ |
| Taux recherche réussie | ~30% | **>90%** ✅ |
| Budget respecté | ~60% | **100%** ✅ |

---

## 🏁 Prochaines Étapes

Une fois les tests locaux validés :

1. **Déployer sur preprod** :
   ```bash
   ./scripts/deploy-local.sh
   ```

2. **Tester depuis le frontend WordPress**

3. **Monitorer les logs** :
   ```bash
   docker-compose logs -f
   ```

4. **Analyser les métriques** :
   - Temps réponse moyen
   - Nombre de messages par conversation
   - Taux de recherche réussie
   - Satisfaction utilisateur

5. **Ajuster le system prompt** si nécessaire

6. **Déployer en production** quand validé sur preprod

---

**Temps total : 5 minutes** ⏱️
**Difficulté : Facile** 🟢
**Status : PRÊT** ✅

---

**Version :** 2.0.0
**Date :** 2025-10-29
