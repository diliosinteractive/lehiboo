# 🚀 Quick Start - Le Hiboo AI Backend

Guide de démarrage en **5 minutes** pour lancer le backend IA.

---

## ✅ Prérequis

- **Node.js 18+** installé ([télécharger](https://nodejs.org))
- **Compte OpenRouter** (gratuit) → [s'inscrire](https://openrouter.ai)

Vérifier Node.js :
```bash
node -v
# Doit afficher v18.0.0 ou supérieur
```

---

## 📦 Étape 1 : Installation (30 secondes)

```bash
# Aller dans le dossier backend
cd lehiboo-ai-backend

# Installer les dépendances
npm install
```

Attendez que npm installe tous les packages...

✅ Quand terminé, vous verrez "added X packages"

---

## 🔑 Étape 2 : Obtenir une Clé OpenRouter (2 minutes)

1. **Aller sur** https://openrouter.ai
2. **Cliquer** "Sign Up" (ou "Sign In" si déjà inscrit)
3. **S'inscrire** avec Google/GitHub/Email
4. **Aller** dans "API Keys" (dans le menu)
5. **Cliquer** "Create Key"
6. **Nommer** la clé (ex: "LeHiboo Dev")
7. **Copier** la clé (commence par `sk-or-v1-...`)

⚠️ **Important** : Gardez cette clé en sécurité, ne la partagez jamais !

---

## ⚙️ Étape 3 : Configuration (1 minute)

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Ouvrir .env dans un éditeur
nano .env
# ou
code .env
# ou
open .env
```

**Modifier ces 2 lignes minimum** :

```bash
# Coller votre clé OpenRouter
OPENROUTER_API_KEY=sk-or-v1-VOTRE-CLE-ICI

# Créer une clé API pour WordPress (inventez-la)
API_KEY=mon-api-key-super-secret-123
```

**Sauvegarder** et fermer le fichier.

---

## 🚀 Étape 4 : Lancer le Serveur (10 secondes)

```bash
# Mode développement (avec rechargement auto)
npm run dev
```

Vous devriez voir :
```
🚀 Le Hiboo AI Backend started
  env: development
  host: 0.0.0.0
  port: 3000
  url: http://0.0.0.0:3000

✅ OpenRouter connection successful
```

✅ **Le backend est lancé !**

---

## 🧪 Étape 5 : Tester (1 minute)

### Test 1 : Health Check

Ouvrir un **nouveau terminal** et taper :

```bash
curl http://localhost:3000/health
```

Résultat attendu :
```json
{
  "status": "ok",
  "timestamp": "2025-10-27T23:00:00.000Z",
  "version": "1.0.0"
}
```

✅ Si vous voyez ça, le serveur fonctionne !

### Test 2 : Chat IA

```bash
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer mon-api-key-super-secret-123" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'
```

Résultat attendu :
```json
{
  "success": true,
  "message": "Bonjour ! Je suis l'assistant Le Hiboo 👋 ...",
  "conversationStage": "group_type",
  "quickChips": [...],
  ...
}
```

✅ **L'IA fonctionne !**

---

## 🔗 Étape 6 : Connecter à WordPress (2 minutes)

1. **Aller** dans WordPress Admin
2. **Naviguer** vers **Le Hiboo → Assistant IA**
3. **Remplir** :
   - **Activer l'assistant** : ✅ Coché
   - **URL Backend** : `http://localhost:3000` (dev) ou `https://votre-backend.com` (prod)
   - **Clé API** : `mon-api-key-super-secret-123` (la même que dans .env)
4. **Sauvegarder**

5. **Tester sur le frontend** :
   - Aller sur n'importe quelle page du site
   - Cliquer sur le bouton Le Hiboo (orange, bas droite)
   - Envoyer un message
   - L'IA devrait répondre (plus de mode démo !) 🎉

---

## ✅ Récapitulatif - Vous Avez Maintenant

- ✅ Backend Node.js fonctionnel
- ✅ Connexion OpenRouter active
- ✅ IA conversationnelle opérationnelle
- ✅ Intégration WordPress configurée
- ✅ Chat frontend avec IA réelle

---

## 🎯 Prochaines Étapes Recommandées

### Court Terme

1. **Tester différents modèles**
   ```bash
   # Dans .env, changer :
   DEFAULT_MODEL=openai/gpt-3.5-turbo  # Plus économique
   # ou
   DEFAULT_MODEL=anthropic/claude-3.5-sonnet  # Plus performant (recommandé)
   ```

2. **Personnaliser les prompts**
   - Éditer `src/prompts/system-prompt.yaml`
   - Adapter le ton, les questions, le flow

3. **Monitorer les coûts**
   - Dashboard OpenRouter : https://openrouter.ai/dashboard
   - Coût typique : 0.01-0.02€ par conversation

### Moyen Terme

4. **MCP Tools** - Connecter aux données EventList (Phase suivante)
5. **API Météo** - Suggestions contextuelles
6. **Déployer en production** - Railway/Vercel/VPS

---

## 🐛 Problèmes Courants

### "OpenRouter connection failed"

**Solution** :
```bash
# Vérifier que la clé est correcte
cat .env | grep OPENROUTER_API_KEY

# Tester manuellement
curl https://openrouter.ai/api/v1/models \
  -H "Authorization: Bearer sk-or-v1-VOTRE-CLE"
```

### "Port 3000 already in use"

**Solution** :
```bash
# Changer le port dans .env
PORT=3001

# Relancer
npm run dev
```

### WordPress ne peut pas se connecter

**Solutions** :
1. Vérifier que le backend tourne (`curl http://localhost:3000/health`)
2. Si WordPress est sur un serveur distant, utiliser une URL publique (pas localhost)
3. Déployer le backend (Railway/Vercel) et utiliser l'URL publique

---

## 📊 Vérifier que Tout Fonctionne

| Test | Commande | Résultat Attendu |
|------|----------|------------------|
| Node.js installé | `node -v` | `v18.0.0` ou + |
| Dépendances installées | `ls node_modules` | Dossier rempli |
| Backend démarre | `npm run dev` | "Backend started" |
| Health check | `curl localhost:3000/health` | `{"status":"ok"}` |
| IA répond | Test curl chat | Réponse JSON avec message |
| WordPress connecté | Envoyer message frontend | Réponse IA (pas mode démo) |

---

## 🎓 Ressources

- **README complet** : [README.md](README.md)
- **Configuration détaillée** : [.env.example](.env.example)
- **Documentation OpenRouter** : https://openrouter.ai/docs
- **Documentation AI SDK** : https://sdk.vercel.ai/docs

---

## 🆘 Besoin d'Aide ?

1. Lire [README.md](README.md) pour plus de détails
2. Vérifier les logs : `tail -f logs/app.log`
3. Vérifier la console : Messages d'erreur ?

---

**Temps total** : ~5-10 minutes ⏱️

**Vous êtes prêt !** 🚀
