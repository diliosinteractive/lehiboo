# 🚀 Déploiement Plesk - URL /api-planner/

Guide pour déployer le backend sur **`https://lehiboo.com/api-planner/`**

---

## 🎯 Configuration

**URL Backend** : `https://lehiboo.com/api-planner/`
**Domaine** : `lehiboo.com`
**Path** : `/api-planner/`
**Port Docker** : `3000`

---

## ⚡ Déploiement (10 minutes)

### Étape 1 : Transférer les Fichiers

Via FTP/SFTP, transférez vers le serveur :

```
Source (local):
  /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend/

Destination (serveur):
  /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/
```

---

### Étape 2 : SSH et Configuration

```bash
# Se connecter
ssh juba@lehiboo.com

# Aller dans le dossier
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend

# Créer .env.production
nano .env.production
```

**Collez** :
```bash
NODE_ENV=production
PORT=3000
LOG_LEVEL=info

# API Keys
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxx
API_KEY=votre-cle-api-super-secrete-123

# WordPress
WORDPRESS_URL=https://lehiboo.com
WORDPRESS_API_URL=https://lehiboo.com/wp-json

# AI
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20
```

**Sauvegarder** : `Ctrl+X`, `Y`, `Entrée`

---

### Étape 3 : Déployer

```bash
./scripts/deploy-local.sh
```

Confirmez avec `y` quand demandé.

**Résultat** :
```
✅ Déploiement Terminé avec Succès !
📍 Backend accessible localement: http://localhost:3000
```

---

### Étape 4 : Configurer Nginx pour /api-planner/

#### Dans Plesk

1. **Domaines** → Cliquer sur `lehiboo.com`
2. **Paramètres Apache & nginx**
3. **Directives nginx supplémentaires**

**Ajoutez cette configuration** :

```nginx
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Le Hiboo AI Backend - Reverse Proxy vers Docker
# URL: https://lehiboo.com/api-planner/
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

location /api-planner/ {
    # Proxy vers Docker container
    proxy_pass http://localhost:3000/;
    proxy_http_version 1.1;

    # Headers
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;
    proxy_set_header X-Forwarded-Prefix /api-planner;

    # Timeouts
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;

    # Buffers
    proxy_buffering off;
    proxy_request_buffering off;

    # CORS (si nécessaire)
    add_header Access-Control-Allow-Origin "https://lehiboo.com" always;
    add_header Access-Control-Allow-Methods "GET, POST, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Authorization, Content-Type" always;
    add_header Access-Control-Max-Age 3600 always;

    # Handle OPTIONS preflight
    if ($request_method = OPTIONS) {
        return 204;
    }
}

# Health check endpoint (optionnel mais recommandé)
location = /api-planner/health {
    proxy_pass http://localhost:3000/health;
    access_log off;
}
```

4. **OK** pour sauvegarder

---

### Étape 5 : Recharger Nginx

```bash
# Tester la configuration
nginx -t

# Si OK, recharger
systemctl reload nginx
```

---

### Étape 6 : Vérifier

#### Test 1 : Health Check

```bash
# Depuis votre machine locale
curl https://lehiboo.com/api-planner/health
```

**Résultat attendu** :
```json
{
  "status": "ok",
  "timestamp": "2025-10-28T...",
  "version": "1.0.0"
}
```

#### Test 2 : Chat Endpoint

```bash
curl -X POST https://lehiboo.com/api-planner/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer votre-cle-api" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'
```

---

### Étape 7 : Configurer WordPress

**WP Admin** → **Le Hiboo** → **Assistant IA** → **Paramètres** :

```
✅ Activer l'assistant IA

URL Backend:
https://lehiboo.com/api-planner/

Clé API:
votre-cle-api-super-secrete-123
(même que dans .env.production)
```

**Sauvegarder**

---

### Étape 8 : Test Final

1. Ouvrir `https://lehiboo.com`
2. Cliquer sur le chat (bouton orange)
3. Envoyer : `Bonjour`
4. **L'IA doit répondre !** 🎉

---

## 🔧 URLs Importantes

| Service | URL |
|---------|-----|
| **Backend API** | `https://lehiboo.com/api-planner/` |
| **Health Check** | `https://lehiboo.com/api-planner/health` |
| **Chat Endpoint** | `https://lehiboo.com/api-planner/chat` |
| **WordPress** | `https://lehiboo.com` |
| **WP Admin** | `https://lehiboo.com/wp-admin` |

---

## 📝 Points Importants

### URL avec Slash Final

⚠️ **Important** : Dans la config nginx :
```nginx
location /api-planner/ {
    proxy_pass http://localhost:3000/;
    #                              ↑ slash important !
}
```

Le slash final dans `http://localhost:3000/` est **crucial** :
- ✅ **Avec slash** : `/api-planner/health` → `http://localhost:3000/health`
- ❌ **Sans slash** : `/api-planner/health` → `http://localhost:3000/api-planner/health`

### WordPress Configuration

Dans WordPress, l'URL doit être :
- ✅ `https://lehiboo.com/api-planner/` (avec slash final)
- ❌ `https://lehiboo.com/api-planner` (sans slash)

---

## 🐛 Troubleshooting

### 404 Not Found

**Symptôme** : `https://lehiboo.com/api-planner/health` renvoie 404

**Causes** :
1. Nginx pas rechargé → `systemctl reload nginx`
2. Slash manquant dans proxy_pass
3. Configuration nginx pas sauvegardée

**Solution** :
```bash
# Vérifier la config
nginx -t

# Voir les logs
tail -f /var/log/nginx/error.log

# Recharger
systemctl reload nginx
```

### 502 Bad Gateway

**Symptôme** : Erreur 502

**Vérifications** :
```bash
# Container tourne ?
docker-compose ps

# Test local
curl http://localhost:3000/health

# Logs Docker
docker-compose logs -f
```

### CORS Errors

**Symptôme** : Erreurs CORS dans la console du navigateur

**Solution** : Les headers CORS sont déjà dans la config nginx ci-dessus. Si problème persiste :

```nginx
# Ajouter à la section location /api-planner/
add_header Access-Control-Allow-Credentials true always;
```

---

## 🔄 Maintenance

### Voir les Logs

```bash
ssh juba@lehiboo.com
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose logs -f
```

### Redémarrer

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose restart
```

### Redéployer

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
./scripts/deploy-local.sh
```

---

## 📊 Architecture

```
Internet
    ↓ HTTPS
Nginx (lehiboo.com)
    ↓ /api-planner/*
Reverse Proxy
    ↓ http://localhost:3000/
Docker Container
    ↓
Le Hiboo AI Backend (Node.js)
```

---

## ✅ Checklist

- [ ] Fichiers transférés vers `/var/www/vhosts/lehiboo.com/lehiboo-ai-backend/`
- [ ] `.env.production` créé avec vraies clés API
- [ ] Script déployé : `./scripts/deploy-local.sh`
- [ ] Container démarré : `docker-compose ps`
- [ ] Nginx configuré avec `location /api-planner/`
- [ ] Nginx rechargé : `systemctl reload nginx`
- [ ] Health check OK : `curl https://lehiboo.com/api-planner/health`
- [ ] WordPress configuré avec URL `https://lehiboo.com/api-planner/`
- [ ] Test frontend OK (chat répond)

---

## 💡 Avantages de Cette Configuration

✅ **Pas de sous-domaine** - Tout sur `lehiboo.com`
✅ **URL propre** - `/api-planner/` bien organisé
✅ **SSL automatique** - Utilise le certificat de `lehiboo.com`
✅ **CORS simplifié** - Même domaine = pas de problème CORS
✅ **SEO friendly** - Tout sous le même domaine

---

## 🎉 C'est Prêt !

Votre backend est accessible sur :
**`https://lehiboo.com/api-planner/`**

**Test rapide** :
```bash
curl https://lehiboo.com/api-planner/health
```

---

**Déploiement /api-planner/** - Le Hiboo AI Assistant v1.0.0

**Date** : 28 Octobre 2025
