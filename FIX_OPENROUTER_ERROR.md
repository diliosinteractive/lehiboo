# 🔧 Fix: Erreur OpenRouter "Not Found"

**Date**: 2025-10-28
**Erreur**: `AI_APICallError: Not Found`
**Serveur**: preprod.lehiboo.com

---

## 🔍 Diagnostic

### Erreur dans les Logs

```
error: OpenRouter connection test failed {"error":"Not Found"}
error: Error generating AI response {"error":"Not Found","errorType":"AI_APICallError"}
```

### Causes Possibles

1. **Clé API OpenRouter manquante ou invalide** (PLUS PROBABLE)
2. Nom du modèle incorrect (vérifié: `deepseek/deepseek-chat-v3.1:free` est correct)
3. Compte OpenRouter sans crédits (pas applicable pour modèle gratuit)
4. Rate limit dépassé (peu probable)

---

## ✅ Solution: Vérifier la Clé OpenRouter

### Étape 1: SSH sur le Serveur

```bash
ssh root@ns3843359.ip-54-39-112.net
# ou
ssh root@preprod.lehiboo.com
```

### Étape 2: Vérifier le Fichier .env

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend

# Vérifier si .env existe
ls -la .env

# Lire la clé API (masquée pour sécurité)
cat .env | grep OPENROUTER_API_KEY
```

**Devrait afficher**:
```
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Si vide ou absent**:
```
OPENROUTER_API_KEY=
# ou
# OPENROUTER_API_KEY absent du fichier
```

---

### Étape 3: Obtenir une Clé OpenRouter (si nécessaire)

**C'est GRATUIT et prend 2 minutes** :

1. **Aller sur** [OpenRouter.ai](https://openrouter.ai)
2. **Sign Up** avec Google ou Email
3. **Aller dans Settings** → **Keys**
4. **Create New Key** → Copier la clé (format: `sk-or-v1-...`)

---

### Étape 4: Configurer la Clé dans .env

```bash
# Éditer le fichier .env
nano /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend/.env
```

**Ajouter ou remplacer la ligne**:
```bash
OPENROUTER_API_KEY=sk-or-v1-VOTRE-CLE-OPENROUTER-ICI
```

**Vérifier aussi le modèle**:
```bash
DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free
```

**Sauvegarder**:
- Nano: `Ctrl+O` (sauver) puis `Ctrl+X` (quitter)
- Vim: `Esc` puis `:wq`

---

### Étape 5: Redémarrer le Container Docker

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend

# Redémarrer
docker-compose restart

# Attendre 5 secondes
sleep 5

# Vérifier les logs
docker-compose logs -f
```

**Logs attendus après fix**:
```
info: Testing OpenRouter connection...
info: ✅ OpenRouter connection successful
info: 🚀 Le Hiboo AI Backend started
info: Configuration {"model":"deepseek/deepseek-chat-v3.1:free"}
```

**Si toujours "Not Found"**:
```
error: OpenRouter connection test failed {"error":"Not Found"}
```
→ La clé est invalide, vérifier qu'elle est bien copiée sans espaces

---

### Étape 6: Tester l'API

```bash
# Test health
curl http://localhost:3000/health

# Devrait retourner:
# {"status":"ok","timestamp":"...","version":"1.0.0"}
```

```bash
# Test chat IA
curl -X POST http://localhost:3000/api-planner \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123"
  }'
```

**Réponse attendue**:
```json
{
  "success": true,
  "message": "Bonjour ! Je suis l'assistant Le Hiboo...",
  "conversationStage": "greeting",
  "quickChips": [...]
}
```

**Si erreur "Not Found" persiste**:
```json
{
  "code": "backend_error",
  "message": "Le serveur IA a rencontré une erreur.",
  "data": {"status": 503}
}
```
→ Retour à l'étape 4, vérifier la clé

---

## 🔍 Vérification Clé API

### Test Direct OpenRouter

```bash
# Tester directement l'API OpenRouter avec votre clé
curl https://openrouter.ai/api/v1/chat/completions \
  -H "Authorization: Bearer sk-or-v1-VOTRE-CLE" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "deepseek/deepseek-chat-v3.1:free",
    "messages": [{"role": "user", "content": "Hello"}]
  }'
```

**Si la clé est valide**:
```json
{
  "id": "gen-xxx",
  "model": "deepseek/deepseek-chat-v3.1:free",
  "choices": [{
    "message": {
      "role": "assistant",
      "content": "Hello! How can I help you today?"
    }
  }]
}
```

**Si la clé est invalide**:
```json
{
  "error": {
    "message": "Invalid API key",
    "type": "invalid_request_error"
  }
}
```

---

## 🚨 Autres Problèmes Possibles

### Problème: Variable d'environnement pas chargée

**Vérifier que Docker charge bien .env**:

```bash
# Vérifier docker-compose.yml
cat docker-compose.yml | grep env_file
```

**Devrait contenir**:
```yaml
services:
  lehiboo-ai-backend:
    env_file:
      - .env
```

**Si absent, ajouter**:
```yaml
services:
  lehiboo-ai-backend:
    build: .
    env_file:
      - .env  # ← Ajouter cette ligne
    ports:
      - "3000:3000"
```

Puis redémarrer:
```bash
docker-compose down
docker-compose up -d
```

---

### Problème: Fichier .env pas dans Docker

**Vérifier que .env est copié dans le container**:

```bash
# Entrer dans le container
docker exec -it lehiboo-ai-backend sh

# Vérifier les variables
env | grep OPENROUTER

# Devrait afficher:
# OPENROUTER_API_KEY=sk-or-v1-xxx
# DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free

# Sortir
exit
```

**Si vides**, le problème vient de docker-compose.yml (voir solution ci-dessus).

---

### Problème: Cache Docker

**Rebuild complet du container**:

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend

# Stop
docker-compose down

# Rebuild avec cache forcé
docker-compose build --no-cache

# Restart
docker-compose up -d

# Logs
docker-compose logs -f
```

---

## 📝 Checklist de Résolution

- [ ] SSH sur le serveur
- [ ] Vérifier que `.env` existe
- [ ] Vérifier que `OPENROUTER_API_KEY` est défini
- [ ] Créer une clé OpenRouter si nécessaire
- [ ] Ajouter la clé dans `.env`
- [ ] Vérifier `DEFAULT_MODEL=deepseek/deepseek-chat-v3.1:free`
- [ ] Redémarrer docker: `docker-compose restart`
- [ ] Vérifier les logs: pas d'erreur "Not Found"
- [ ] Tester `/health`: status ok
- [ ] Tester `/api-planner`: réponse AI valide
- [ ] Tester depuis le frontend WordPress

---

## 🎯 Commandes Rapides (Copy-Paste)

```bash
# 1. SSH
ssh root@preprod.lehiboo.com

# 2. Aller dans le dossier
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend

# 3. Vérifier .env
cat .env | grep OPENROUTER

# 4. Si vide, éditer
nano .env

# 5. Ajouter la clé (remplacer VOTRE-CLE):
# OPENROUTER_API_KEY=sk-or-v1-VOTRE-CLE

# 6. Redémarrer
docker-compose restart

# 7. Vérifier logs
docker-compose logs -f | grep -E "OpenRouter|error"

# 8. Tester
curl http://localhost:3000/health
```

---

## 💡 Notes Importantes

1. **Sécurité**: Ne jamais commiter le fichier `.env` dans git (il est dans `.gitignore`)
2. **Gratuit**: DeepSeek est 100% gratuit, pas de limite de crédits
3. **Backup**: Sauvegarder la clé OpenRouter dans un gestionnaire de mots de passe
4. **Production**: Utiliser la même clé sur preprod et production (ou créer 2 clés séparées)

---

## 🔗 Liens Utiles

- **OpenRouter Dashboard**: https://openrouter.ai/keys
- **DeepSeek V3.1 Free**: https://openrouter.ai/deepseek/deepseek-chat-v3.1:free
- **OpenRouter Docs**: https://openrouter.ai/docs
- **Support**: https://discord.gg/openrouter

---

**Auteur**: Claude AI + Juba
**Date**: 2025-10-28
**Status**: Guide de résolution
