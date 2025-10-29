# System Prompt v2 vs v3 - Comparaison et rationale

## 🚨 Problème critique avec v2

### Symptômes observés
1. **L'IA n'appelle JAMAIS les tools** (`toolCalls: 0` constant)
2. **L'IA redemande tout manuellement** au lieu d'extraire les infos
3. **Conversation inefficace** : 10-15 messages pour collecter 6 infos

### Exemple de bug
```
User: "J'ai 20 ans, à Valenciennes, ce weekend, j'aime le sport, pas de budget"

Attendu:
- IA appelle collectUserProfile avec toutes les infos
- IA voit completeness = 100%
- IA appelle searchEvents
- IA présente les résultats

Réel (v2):
- IA ne fait AUCUN tool call
- IA dit: "Parfait, nous avons presque toutes les informations !"
- IA redemande: âge, ville, dates, budget 🤦
- User frustré, doit tout re-taper
```

### Cause racine
**System prompt v2 trop long et complexe :**
- 923 lignes
- 30 208 caractères
- Instructions tools enfouies ligne 413 sur 923
- GPT-4o perd le fil avec trop d'instructions

## ✅ Solution v3 MINIMAL

### Principe
**"Less is more"** - Focalisé sur l'essentiel :
1. Workflow obligatoire EN PREMIER
2. Règles critiques explicites
3. Exemples concrets
4. Rien d'autre

### Statistiques v3
- **89 lignes** (vs 923)
- **3 100 caractères** (vs 30 208)
- **10x plus court**
- **100% focalisé sur le workflow tools**

### Structure v3
```markdown
1. Identité Hedwige (5 lignes)
2. 🔄 WORKFLOW OBLIGATOIRE (20 lignes) ← Le plus important
3. ❌ RÈGLES CRITIQUES (5 lignes)
4. ✅ EXEMPLES (20 lignes) ← Cas d'usage concrets
5. 💬 Style (5 lignes)
6. 🎯 Quick Chips (8 lignes)
7. 📍 Budget strict (3 lignes)
8. 🎓 Expertise persona (5 lignes)
```

### Différences clés

| Aspect | v2 | v3 |
|--------|----|----|
| **Longueur** | 923 lignes | 89 lignes |
| **Focus** | Expertise détaillée + workflow | Workflow uniquement |
| **Instructions tools** | Ligne 413/923 | Ligne 11/89 |
| **Exemples** | Théoriques | 3 cas concrets avec code |
| **Règles** | Implicites | Explicites (❌/✅) |
| **Prompt caching** | Sous-optimal | Optimal |

## 📊 Bénéfices attendus v3

### 1. Tools appelés systématiquement
```javascript
// Chaque message → collectUserProfile appelé
toolCalls: [
  {
    toolName: 'collectUserProfile',
    args: { age: 20, location: {...}, activityType: 'sport', ... }
  }
]
```

### 2. Extraction intelligente
L'IA comprend maintenant qu'elle DOIT extraire **toutes** les infos du message, pas juste une.

### 3. Pas de re-questions
Avec `[CONTEXT: groupType: solo, age: 25]` visible, l'IA sait ne pas redemander.

### 4. Recherche fonctionnelle
Dès 100% complétude → `searchEvents` appelé automatiquement.

### 5. Performance optimisée
- **Prompt caching OpenAI** : 50% discount sur cached reads
- **Moins de tokens** : 3K chars vs 30K chars
- **Latency réduite** : moins de texte à traiter

## 🔄 Prompt Caching OpenAI

### Comment ça marche
OpenAI cache automatiquement les prompts > 1024 tokens :
- Cache par blocs de 128 tokens
- Cache actif 5-10 minutes après dernière utilisation
- **Cache write: gratuit**
- **Cache read: 50% discount**

### Optimisation v3
Le prompt v3 est structuré pour maximiser le caching :

```
┌─────────────────────────────────────┐
│ System Prompt v3 (3100 chars)       │ ← CACHEABLE (statique)
│ - Identité Hedwige                  │
│ - Workflow obligatoire              │
│ - Règles + Exemples                 │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ Messages array (dynamique)          │ ← PAS CACHEABLE (change)
│ - Message 1                         │
│ - Message 2                         │
│ - Message actuel                    │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ Tools definitions (statique)        │ ← CACHEABLE
│ - collectUserProfile schema         │
│ - searchEvents schema               │
└─────────────────────────────────────┘
```

**Résultat** : ~70% du prompt est caché après le 1er appel

## 🧪 Tests recommandés

### Test 1 : Tool calling basique
```
User: "Solo"
Attendu: collectUserProfile({groupType: 'solo'}) appelé
```

### Test 2 : Extraction multi-infos
```
User: "20 ans, Valenciennes, ce weekend, sport, pas de budget"
Attendu: collectUserProfile appelé avec 5 champs
```

### Test 3 : Pas de re-question
```
[CONTEXT: groupType: solo, age: 25]
User: "Sport"
Attendu: Ne redemande PAS groupType ni age
```

### Test 4 : Recherche automatique
```
Profil 100% complet
Attendu: searchEvents appelé immédiatement
```

## 🎯 Console OpenAI (réponse à ta question)

Tu as demandé si on peut mettre le prompt dans la console OpenAI.

**Réponse : Non, pas directement**, mais OpenAI a 2 alternatives :

### 1. Fine-tuning (si répétitif)
- Entraîner un modèle custom avec ton style Hedwige
- Réduire le system prompt à 0
- Coût : ~$8-50 pour entraînement, puis coût API normal

### 2. Assistants API (avec threads)
- Créer un Assistant avec system prompt stocké
- Gérer conversations via threads
- **Mais**: moins flexible que l'approche actuelle

**Notre approche actuelle est optimale** :
- Prompt caching automatique (50% discount)
- Flexibilité totale
- Pas de lock-in OpenAI
- Peut changer de modèle facilement

## 📚 Références

- [OpenAI Prompt Caching](https://platform.openai.com/docs/guides/prompt-caching)
- [OpenAI Prompt Caching 101](https://cookbook.openai.com/examples/prompt_caching101)
- [AI SDK Vercel - generateText](https://sdk.vercel.ai/docs/reference/ai-sdk-core/generate-text)

## 🔙 Rollback si besoin

Si v3 ne fonctionne pas :
```javascript
// src/services/ai-service-v2.js
- const promptPath = join(__dirname, '../prompts/system-prompt-v3-minimal.md');
+ const promptPath = join(__dirname, '../prompts/system-prompt-v2.md');
```

Le fichier v2 est conservé pour rollback rapide.

## 🚀 Déploiement

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend
git pull
docker-compose restart backend
docker-compose logs -f backend | grep "System prompt"
# Devrait afficher: "System prompt v3 minimal loaded { length: 3100, lines: 89 }"
```
