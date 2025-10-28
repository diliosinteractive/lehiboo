# 🚀 Quick Start - Le Hiboo AI Backend

## ⚡ Démarrage Rapide (5 minutes)

### Étape 1: Obtenir une Clé OpenRouter (GRATUIT)

1. **Aller sur** [OpenRouter.ai](https://openrouter.ai)
2. **Sign Up** (avec Google ou Email)
3. **Aller dans Settings** → **Keys**
4. **Create New Key**
5. **Copier la clé** (format: `sk-or-v1-...`)

### Étape 2: Configuration

```bash
# Dans le dossier lehiboo-ai-backend
cd lehiboo-ai-backend

# Copier .env.example → .env
cp .env.example .env

# Éditer .env
nano .env  # ou vim, ou VSCode
```

**Remplacer la clé**:
```bash
OPENROUTER_API_KEY=sk-or-v1-VOTRE-CLE-ICI
```

**Vérifier le modèle** (devrait déjà être bon):
```bash
DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free
```

### Étape 3: Installer et Démarrer

```bash
# Installer les dépendances
npm install

# Démarrer en mode développement
npm run dev
```

**Output attendu**:
```
🚀 Server started on http://localhost:3000
✅ OpenRouter configured with model: deepseek/deepseek-chat-v3.1:free
```

### Étape 4: Tester

**Test 1: Health Check**
```bash
curl http://localhost:3000/health
```

**Réponse attendue**:
```json
{
  "status": "ok",
  "timestamp": "2025-10-28T10:30:00.000Z",
  "version": "1.0.0"
}
```

**Test 2: Chat IA**
```bash
curl -X POST http://localhost:3000/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Bonjour, je cherche une activité en couple",
    "conversationId": "test-123"
  }'
```

**Réponse attendue**:
```json
{
  "success": true,
  "message": "Bonjour ! Une activité en couple, super ! 💑...",
  "conversationStage": "info_collection",
  "userContext": {
    "groupType": "couple"
  }
}
```

---

## ✅ C'est Tout !

Le backend est maintenant prêt et **100% gratuit** grâce à DeepSeek ! 🎉

---

## 📚 Documentation Complète

- **DeepSeek Guide**: [MODEL_DEEPSEEK.md](MODEL_DEEPSEEK.md)
- **README**: [README.md](README.md)
- **MCP Tools**: [MCP_TOOLS.md](MCP_TOOLS.md)

---

## 🐛 Problème ?

**Erreur "Invalid API Key"**:
```bash
# Vérifier que la clé est bien dans .env
cat .env | grep OPENROUTER_API_KEY
```

**Port 3000 déjà utilisé**:
```bash
# Modifier le port dans .env
PORT=3001
```

**Modèle introuvable**:
```bash
# Vérifier le nom exact (avec :free)
cat .env | grep DEFAULT_MODEL
# Devrait afficher:
# DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free
```

---

**Besoin d'aide ?** Voir [MODEL_DEEPSEEK.md](MODEL_DEEPSEEK.md) section Troubleshooting
