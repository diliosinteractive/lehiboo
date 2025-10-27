# 🦉 Le Hiboo AI Backend

Backend Node.js pour l'assistant conversationnel Le Hiboo, propulsé par **AI SDK** et **OpenRouter**.

## 🚀 Démarrage Rapide

### 1. Installation

```bash
# Installer les dépendances
npm install
```

### 2. Configuration

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Éditer .env et remplir au minimum :
# - OPENROUTER_API_KEY (obligatoire)
# - API_KEY (pour authentification WordPress)
# - WORDPRESS_URL (optionnel)
```

**Obtenir une clé OpenRouter :**
1. Aller sur https://openrouter.ai
2. S'inscrire (gratuit)
3. Créer une clé API
4. Copier la clé dans `.env`

### 3. Lancer le serveur

```bash
# Mode développement (avec nodemon)
npm run dev

# Mode production
npm start
```

Le serveur démarre sur **http://localhost:3000**

### 4. Tester

```bash
# Health check
curl http://localhost:3000/health

# Test chat (nécessite API_KEY)
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'
```

---

## 📁 Structure du Projet

```
lehiboo-ai-backend/
├── src/
│   ├── index.js              # Point d'entrée
│   ├── config/
│   │   └── index.js          # Configuration centralisée
│   ├── controllers/
│   │   └── chat-controller.js # Logique des endpoints
│   ├── routes/
│   │   └── chat.js           # Routes Express
│   ├── services/
│   │   ├── ai-service.js     # Intégration AI SDK + OpenRouter
│   │   └── prompt-service.js # Gestion prompts YAML
│   ├── middleware/
│   │   └── auth.js           # Authentification et validation
│   ├── utils/
│   │   └── logger.js         # Winston logging
│   ├── prompts/
│   │   └── system-prompt.yaml # Prompt système principal
│   └── mcp/                  # MCP Tools (à venir)
├── logs/                     # Logs générés
├── .env                      # Configuration (ne pas commit)
├── .env.example              # Template de configuration
└── package.json
```

---

## 🔧 Configuration Détaillée

### Variables d'Environnement

| Variable | Description | Obligatoire | Défaut |
|----------|-------------|-------------|--------|
| `OPENROUTER_API_KEY` | Clé API OpenRouter | ✅ Oui | - |
| `DEFAULT_MODEL` | Modèle IA à utiliser | ❌ Non | `anthropic/claude-3.5-sonnet` |
| `API_KEY` | Clé pour authentifier WordPress | ✅ Oui | - |
| `PORT` | Port du serveur | ❌ Non | `3000` |
| `WORDPRESS_URL` | URL du site WordPress | ❌ Non | - |
| `RATE_LIMIT_MAX_REQUESTS` | Limite de requêtes | ❌ Non | `20` |
| `LOG_LEVEL` | Niveau de log | ❌ Non | `info` |

### Modèles IA Disponibles (OpenRouter)

| Modèle | Prix | Performance | Use Case |
|--------|------|-------------|----------|
| `anthropic/claude-3.5-sonnet` | 💰 Moyen | ⭐⭐⭐⭐⭐ | Production (recommandé) |
| `openai/gpt-4-turbo` | 💰💰 Cher | ⭐⭐⭐⭐⭐ | Production |
| `openai/gpt-3.5-turbo` | 💰 Économique | ⭐⭐⭐ | Développement |
| `meta-llama/llama-3.1-70b-instruct` | 💰 Économique | ⭐⭐⭐⭐ | Open source |
| `google/gemini-pro` | 💰 Moyen | ⭐⭐⭐⭐ | Alternative |

**Changer de modèle :**
```bash
# Dans .env
DEFAULT_MODEL=openai/gpt-3.5-turbo
```

---

## 📡 API Endpoints

### POST /chat

**Description** : Endpoint principal de conversation

**Headers** :
- `Authorization: Bearer YOUR_API_KEY`
- `Content-Type: application/json`

**Body** :
```json
{
  "message": "Je cherche une activité en couple",
  "conversationId": "conv_12345",
  "userContext": {
    "groupType": "couple"
  },
  "currentStage": "greeting",
  "history": []
}
```

**Response** :
```json
{
  "success": true,
  "message": "Super ! Une activité en couple 👍 ...",
  "conversationStage": "age_collection",
  "userContext": {
    "groupType": "couple"
  },
  "quickChips": [
    {"text": "18-25 ans", "value": "18-25"},
    {"text": "25-35 ans", "value": "25-35"}
  ],
  "events": [],
  "weatherAlert": null,
  "history": [...],
  "usage": {
    "model": "anthropic/claude-3.5-sonnet",
    "tokens": 150
  }
}
```

### GET /health

**Description** : Health check

**Response** :
```json
{
  "status": "ok",
  "timestamp": "2025-10-27T23:00:00.000Z",
  "version": "1.0.0"
}
```

### GET /status

**Description** : Status détaillé (protégé par API key)

**Headers** :
- `Authorization: Bearer YOUR_API_KEY`

**Response** :
```json
{
  "status": "ok",
  "services": {
    "openrouter": "connected",
    "mcp": "pending",
    "weather": "pending"
  },
  "timestamp": "2025-10-27T23:00:00.000Z"
}
```

---

## 🎯 Intégration WordPress

### 1. Configurer l'URL Backend dans WordPress

```
WP Admin → Le Hiboo → Assistant IA → Paramètres
- URL Backend : http://localhost:3000 (dev) ou https://votre-backend.com (prod)
- Clé API : YOUR_API_KEY (même que dans .env)
```

### 2. Le Plugin WordPress Appelle le Backend

Quand un utilisateur envoie un message :
```
User → WordPress Frontend → Plugin PHP → Backend Node.js → OpenRouter → Response
```

Le plugin PHP (`class-chat-handler.php`) appelle automatiquement `/chat`.

---

## 📝 Prompts YAML

Les prompts sont dans `src/prompts/` au format YAML pour faciliter l'édition.

**Éditer le prompt système :**
```bash
# Ouvrir src/prompts/system-prompt.yaml
# Modifier les instructions, stages, exemples...
# Relancer le serveur (le cache se recharge automatiquement)
```

**Structure d'un stage :**
```yaml
stages:
  nom_du_stage:
    description: "Description du stage"
    instructions: |
      Instructions pour l'IA à ce stage
    examples:
      - "Exemple de message"
    quickChips:
      - text: "Option 1"
        value: "valeur1"
    nextStage: "stage_suivant"
```

---

## 🔒 Sécurité

### Rate Limiting
- **20 requêtes / minute** par défaut
- Configurable via `RATE_LIMIT_MAX_REQUESTS`

### Authentication
- Toutes les routes `/chat` et `/status` nécessitent `Authorization: Bearer API_KEY`
- L'API key doit être identique entre WordPress et le backend

### Validation
- Messages limités à 2000 caractères
- Validation stricte des inputs
- Sanitization XSS

### Logging
- Tous les accès sont loggés
- Logs dans `logs/app.log` et `logs/error.log`
- Niveau configurable via `LOG_LEVEL`

---

## 🚀 Déploiement Production

### Option 1 : Railway

```bash
# 1. Créer compte sur railway.app
# 2. Connecter le repo GitHub
# 3. Configurer les variables d'environnement
# 4. Deploy automatique !
```

Railway détecte automatiquement Node.js et `package.json`.

### Option 2 : Vercel

```bash
# Installer Vercel CLI
npm i -g vercel

# Deploy
vercel

# Configurer les env variables dans le dashboard
```

### Option 3 : VPS (Ubuntu)

```bash
# Sur le serveur
git clone https://github.com/your-repo/lehiboo-ai-backend
cd lehiboo-ai-backend
npm install
cp .env.example .env
# Éditer .env

# Installer PM2
npm i -g pm2

# Lancer avec PM2
pm2 start src/index.js --name lehiboo-ai
pm2 save
pm2 startup
```

**Nginx Reverse Proxy** :
```nginx
server {
    listen 80;
    server_name ai.lehiboo.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

---

## 📊 Monitoring

### Logs

```bash
# Voir les logs en temps réel
tail -f logs/app.log

# Filtrer les erreurs
grep "ERROR" logs/app.log
```

### Coûts OpenRouter

- Tracker sur https://openrouter.ai/dashboard
- Budget alerts disponibles
- Coût typique : ~0.01-0.02€ par conversation (Claude Sonnet)

### Performance

- Temps réponse typique : **1-2 secondes**
- Limiter à 1000 tokens max pour garder < 2s

---

## 🐛 Debugging

### Le serveur ne démarre pas

```bash
# Vérifier Node.js version
node -v  # Doit être >= 18

# Vérifier les logs
cat logs/error.log

# Vérifier .env
cat .env | grep OPENROUTER_API_KEY
```

### "OpenRouter connection failed"

1. Vérifier que `OPENROUTER_API_KEY` est correcte
2. Tester manuellement :
```bash
curl https://openrouter.ai/api/v1/chat/completions \
  -H "Authorization: Bearer $OPENROUTER_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"model":"openai/gpt-3.5-turbo","messages":[{"role":"user","content":"test"}]}'
```

### WordPress ne peut pas se connecter

1. Vérifier que le backend est accessible depuis WordPress
2. Tester :
```bash
curl http://your-backend-url/health
```
3. Vérifier les CORS (autoriser l'origine WordPress)

---

## 📚 Ressources

- **AI SDK Docs** : https://sdk.vercel.ai/docs
- **OpenRouter** : https://openrouter.ai/docs
- **Express** : https://expressjs.com
- **Winston Logger** : https://github.com/winstonjs/winston

---

## 🎯 Roadmap

- [x] Setup serveur Express
- [x] Intégration AI SDK + OpenRouter
- [x] Système prompts YAML
- [x] Authentication & rate limiting
- [ ] MCP Tools pour EventList
- [ ] API Météo
- [ ] Streaming responses
- [ ] Redis cache
- [ ] Monitoring Sentry

---

**Développé avec ❤️ pour Le Hiboo**
