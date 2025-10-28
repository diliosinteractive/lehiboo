# Migration OpenRouter → OpenAI

## Contexte

Migration du backend Le Hiboo AI de OpenRouter vers OpenAI direct suite aux problèmes de fiabilité rencontrés avec OpenRouter.

**Date:** 28 octobre 2025
**Raison:** OpenRouter générait des erreurs "Not Found" persistantes même avec une clé API valide.
**Référence:** Implémentation inspirée du projet ZENELI qui utilise OpenAI avec succès.

---

## Modifications effectuées

### 1. Configuration (`.env`)

**AVANT:**
```env
OPENROUTER_API_KEY=sk-or-v1-your-key-here
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free
```

**APRÈS:**
```env
OPENAI_API_KEY=sk-your-openai-api-key-here
DEFAULT_MODEL=gpt-4o
```

**Action requise:**
- Obtenir une clé API OpenAI sur https://platform.openai.com/api-keys
- Mettre à jour le fichier `.env` sur le serveur avec la nouvelle clé

---

### 2. Fichiers modifiés

#### `src/config/index.js`
- Renommé `openrouter` → `openai`
- Supprimé `baseUrl` (non nécessaire pour OpenAI direct)
- Changé variable d'environnement: `OPENROUTER_API_KEY` → `OPENAI_API_KEY`
- Changé modèle par défaut: `anthropic/claude-3.5-sonnet` → `gpt-4o`

#### `src/services/ai-service.js`
- Changé import: `createOpenAI` → `openai`
- Supprimé initialisation du client OpenRouter personnalisé
- Remplacé 4 occurrences: `openrouter(config.openrouter.defaultModel)` → `openai(config.openai.defaultModel)`
- Renommé fonction: `testOpenRouterConnection()` → `testOpenAIConnection()`

#### `src/index.js`
- Mis à jour commentaire: "OpenRouter" → "OpenAI"
- Changé import: `testOpenRouterConnection` → `testOpenAIConnection`
- Mis à jour logs de démarrage et messages d'erreur
- Changé référence config: `config.openrouter.defaultModel` → `config.openai.defaultModel`

#### `src/controllers/chat-controller.js`
- Mis à jour endpoint status: `openrouter: 'connected'` → `openai: 'connected'`

#### `.env.example`
- Remplacé section OpenRouter par OpenAI
- Ajouté lien vers la plateforme OpenAI
- Mis à jour les exemples de modèles (gpt-4o, gpt-4-turbo, gpt-3.5-turbo)

#### `package.json`
- Description: "OpenRouter" → "OpenAI"
- Keywords: "openrouter" → "openai"

---

## Pattern utilisé (inspiré de ZENELI)

```javascript
import { openai } from "@ai-sdk/openai";
import { streamText } from "ai";

// Simple et direct - pas besoin de client personnalisé
const model = openai("gpt-4o");

// Utilisation dans generateAIResponse
const result = streamText({
  model,
  messages,
  system: systemPrompt,
  temperature: 0.7,
});
```

---

## Déploiement sur le serveur

### Étape 1: Configuration environnement

Se connecter au serveur et mettre à jour `.env`:

```bash
ssh user@lehiboo-preprod
cd /var/www/vhosts/lehiboo.dilios.me/ai-backend
nano .env
```

Modifier:
```env
# Remplacer OPENROUTER_API_KEY par:
OPENAI_API_KEY=sk-proj-votre-cle-openai-ici

# Remplacer le modèle par:
DEFAULT_MODEL=gpt-4o
```

### Étape 2: Déployer le code

Depuis la machine locale:

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend

# Commit les changements
git add .
git commit -m "Migration OpenRouter → OpenAI

- Remplacé OpenRouter par OpenAI direct
- Modèle par défaut: gpt-4o
- Supprimé baseURL personnalisé
- Mis à jour tous les fichiers de config et services"

git push
```

### Étape 3: Redémarrer sur le serveur

```bash
# Sur le serveur
cd /var/www/vhosts/lehiboo.dilios.me/ai-backend
git pull

# Redémarrer l'application (adapter selon votre setup)
pm2 restart lehiboo-ai-backend
# OU
systemctl restart lehiboo-ai-backend
```

### Étape 4: Vérifier

```bash
# Checker les logs
pm2 logs lehiboo-ai-backend
# OU
journalctl -u lehiboo-ai-backend -f

# Vous devriez voir:
# ✅ OpenAI connection successful
# 🚀 Le Hiboo AI Backend started
```

Tester l'API:
```bash
curl https://lehiboo.dilios.me/ai-backend/health
```

---

## Vérifications post-migration

- [ ] Le serveur démarre sans erreur
- [ ] La connexion OpenAI est testée avec succès au démarrage
- [ ] Les requêtes chat fonctionnent depuis le frontend
- [ ] Les logs montrent "OpenAI connection successful"
- [ ] Le modèle gpt-4o génère des réponses correctes

---

## Coûts OpenAI (à titre indicatif)

**gpt-4o** (recommandé):
- Input: $2.50 / 1M tokens
- Output: $10.00 / 1M tokens
- Excellent rapport qualité/prix

**gpt-4-turbo**:
- Input: $10.00 / 1M tokens
- Output: $30.00 / 1M tokens
- Plus puissant mais plus cher

**gpt-3.5-turbo** (économique):
- Input: $0.50 / 1M tokens
- Output: $1.50 / 1M tokens

---

## Rollback (si nécessaire)

Si la migration pose problème, revenir à OpenRouter:

```bash
git revert HEAD
git push
# Puis redémarrer sur le serveur
```

Et restaurer `.env` avec:
```env
OPENROUTER_API_KEY=sk-or-v1-...
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

---

## Fichiers documentation à mettre à jour ultérieurement

Les fichiers suivants contiennent encore des références à OpenRouter mais ne sont pas critiques:

- README.md
- QUICK_START.md
- MODEL_DEEPSEEK.md (obsolète)
- DEPLOYMENT_GUIDE.md
- Autres fichiers .md de documentation

Ces fichiers pourront être mis à jour lors d'une prochaine phase de maintenance.
