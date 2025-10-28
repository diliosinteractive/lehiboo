# 🐳 Docker V2 Deployment Guide

**Version Backend :** 2.0.0 (OpenAI GPT-4o + Tools)
**Date :** 2025-10-29
**Migration :** OpenRouter → OpenAI

---

## 🚀 Démarrage Rapide Docker (5 minutes)

### 1. Configuration `.env.production`

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend

# Copier depuis .env
cp .env .env.production

# Éditer avec les vraies clés
nano .env.production
```

**Contenu minimal `.env.production` :**
```bash
# Server
NODE_ENV=production
PORT=3000
HOST=0.0.0.0

# OpenAI (V2 - Obligatoire)
OPENAI_API_KEY=sk-proj-votre-cle-openai-ici
DEFAULT_MODEL=gpt-4o

# WordPress
WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json
WORDPRESS_API_KEY=votre-cle-wordpress-api

# API Key pour authentification
API_KEY=votre-cle-api-backend

# Weather (optionnel)
WEATHER_API_KEY=votre-cle-weather

# Rate Limiting
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20

# Logging
LOG_LEVEL=info
```

---

### 2. Build et Démarrage

```bash
# Build l'image Docker
docker-compose build --no-cache

# Démarrer le container
docker-compose up -d

# Vérifier les logs
docker-compose logs -f
```

**Logs attendus :**
```
backend_1  | ✅ OpenAI connection successful
backend_1  | 🚀 Le Hiboo AI Backend started
backend_1  |    Model: gpt-4o
backend_1  |    Port: 3000
backend_1  |    Env: production
```

---

### 3. Test Rapide

```bash
# Health check (port 3004 sur l'hôte)
curl http://localhost:3004/health

# Résultat attendu
{"status":"ok","timestamp":"2025-10-29T...","version":"1.0.0"}
```

---

## 🌐 Déploiement Production Plesk

### Option A : Script Automatique

```bash
# Depuis votre machine locale
cd /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend

# Déployer automatiquement
./scripts/deploy.sh lehiboo.dilios.me juba
```

**Ce que le script fait :**
1. ✅ Vérifie connexion SSH
2. ✅ Synchronise fichiers via rsync
3. ✅ Build l'image sur le serveur
4. ✅ Démarre docker-compose
5. ✅ Teste health check
6. ✅ Affiche logs

---

### Option B : Manuel via SSH

```bash
# 1. Se connecter au serveur
ssh juba@lehiboo.dilios.me

# 2. Aller dans le dossier
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# 3. Créer .env.production
nano .env.production
# (Coller la config ci-dessus avec vraies clés)

# 4. Build et démarrer
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# 5. Vérifier
docker-compose ps
docker-compose logs -f
```

---

## 🔧 Configuration Reverse Proxy Plesk

### 1. Créer Sous-Domaine

**Plesk → Domaines → lehiboo.dilios.me → Sous-domaines**

- Nom : `ai`
- Document root : `/var/www/vhosts/lehiboo.dilios.me/ai.lehiboo.dilios.me`

### 2. Activer SSL

**Plesk → SSL/TLS → Let's Encrypt**

- ✅ Sécuriser le domaine wildcard (*.lehiboo.dilios.me)
- ✅ Ou certificat spécifique pour ai.lehiboo.dilios.me

### 3. Configurer Reverse Proxy

**Plesk → Apache & nginx Settings → Additional nginx directives**

```nginx
location / {
    proxy_pass http://localhost:3004;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}
```

### 4. Tester

```bash
# Health check public
curl https://ai.lehiboo.dilios.me/health

# Test chat
curl -X POST https://ai.lehiboo.dilios.me/chat \
  -H "Content-Type: application/json" \
  -H "x-api-key: votre-api-key" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test_001"
  }'
```

---

## 🎯 Architecture Docker V2

```
┌─────────────────────────────────────────────────┐
│  Plesk Nginx Reverse Proxy                      │
│  https://ai.lehiboo.dilios.me                   │
│  SSL: Let's Encrypt                             │
└────────────┬────────────────────────────────────┘
             │ Port 443 → 3004
             ▼
┌─────────────────────────────────────────────────┐
│  Docker Container: lehiboo-ai-backend           │
│  Image: lehiboo-ai-backend:1.0.0                │
│  Port Mapping: 3004:3000                        │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │  Node.js 18 Alpine                        │  │
│  │  - src/index.js (entrypoint)              │  │
│  │  - ai-service-v2.js (OpenAI GPT-4o)       │  │
│  │  - Tools: collectUserProfile, searchEvents│  │
│  │  - System Prompt V2 (880 lignes)          │  │
│  └───────────────────────────────────────────┘  │
│                                                  │
│  Volumes:                                        │
│  - ./logs → /app/logs (persisté)                │
│  - ./src/prompts → /app/src/prompts (read-only) │
│                                                  │
│  Environment:                                    │
│  - OPENAI_API_KEY (depuis .env.production)      │
│  - WORDPRESS_API_KEY                            │
│  - DEFAULT_MODEL=gpt-4o                         │
│                                                  │
│  Health Check:                                   │
│  - Interval: 30s                                │
│  - Endpoint: /health                            │
│  - Auto-restart si unhealthy                    │
└─────────────────────────────────────────────────┘
```

---

## 📦 Maintenance

### Voir les Logs

```bash
# Temps réel
docker-compose logs -f

# Dernières 100 lignes
docker-compose logs --tail=100

# Logs d'un service spécifique
docker logs lehiboo-ai-backend -f

# Filtrer par mot-clé
docker-compose logs -f | grep "ERROR"
```

---

### Redémarrer

```bash
# Restart gracieux
docker-compose restart

# Ou
docker restart lehiboo-ai-backend

# Redémarrer avec rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

### Mettre à Jour le Code

```bash
# SSH sur le serveur
ssh juba@lehiboo.dilios.me

# Aller dans le dossier
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# Pull les derniers changements
git pull origin main

# Rebuild et redémarrer
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Vérifier
docker-compose ps
curl http://localhost:3004/health
```

---

### Voir l'État des Containers

```bash
# Liste des containers
docker ps

# État détaillé
docker-compose ps

# Utilisation ressources
docker stats lehiboo-ai-backend

# Logs health check
docker inspect lehiboo-ai-backend | grep -A 20 Health
```

---

## 🐛 Troubleshooting

### Container ne Démarre Pas

```bash
# Voir les logs d'erreur
docker-compose logs

# Causes communes:
# 1. .env.production manquant
ls -la .env.production

# 2. Clé OpenAI invalide
docker-compose exec backend printenv | grep OPENAI_API_KEY

# 3. Port 3004 déjà utilisé
lsof -i :3004
```

---

### OpenAI Connection Failed

```bash
# Vérifier la clé dans le container
docker-compose exec backend printenv OPENAI_API_KEY

# Tester depuis le container
docker-compose exec backend node -e "
  fetch('https://api.openai.com/v1/models', {
    headers: { 'Authorization': 'Bearer ' + process.env.OPENAI_API_KEY }
  })
  .then(r => r.json())
  .then(console.log)
  .catch(console.error)
"
```

---

### WordPress API Non Accessible

```bash
# Tester depuis le container
docker-compose exec backend curl -X POST \
  https://lehiboo.dilios.me/wp-json/lehiboo/v1/events/search \
  -H "Authorization: Bearer $WORDPRESS_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"city":"Paris","maxPrice":100,"limit":5}'

# Si erreur 401: Vérifier API key WordPress
# Si erreur 404: Plugin non actif ou endpoint manquant
```

---

### 502 Bad Gateway (Reverse Proxy)

```bash
# 1. Vérifier que le container tourne
docker ps | grep lehiboo-ai-backend

# 2. Vérifier que le port répond
curl http://localhost:3004/health

# 3. Vérifier la config nginx
# Dans Plesk → Apache & nginx Settings
# Vérifier que proxy_pass pointe vers http://localhost:3004

# 4. Tester nginx config
nginx -t

# 5. Redémarrer nginx si besoin
systemctl restart nginx
```

---

### Tools Non Appelés

```bash
# Vérifier system-prompt-v2.md présent
docker-compose exec backend ls -lh /app/src/prompts/system-prompt-v2.md

# Vérifier que tools sont chargés
docker-compose exec backend node -e "
  import('./src/tools/collect-user-profile.js').then(t => console.log('✅ collectUserProfile loaded'));
  import('./src/tools/search-events.js').then(t => console.log('✅ searchEvents loaded'));
"

# Voir les logs de tools
docker-compose logs -f | grep "Tools called"
```

---

## 💰 Coûts Production

### Infrastructure
- **VPS Plesk** : 15-30€/mois
- **Docker** : Inclus
- **SSL** : Gratuit (Let's Encrypt)
- **Total infra** : 15-30€/mois

### APIs (V2 - OpenAI)
- **GPT-4o** : $2.50/1M input + $10/1M output tokens
- **Estimation** : ~$0.05-0.08 par conversation (3 messages)
- **1000 conversations/mois** : ~50-80€
- **OpenWeather** : Gratuit (jusqu'à 60 calls/min)

**Total mensuel** : **65-110€/mois**

---

## ✅ Checklist Déploiement Production

### Avant Déploiement
- [ ] `.env.production` créé avec vraies clés
- [ ] `OPENAI_API_KEY` valide (testé)
- [ ] `WORDPRESS_API_KEY` configurée
- [ ] `API_KEY` sécurisée (>32 caractères)
- [ ] System prompt v2 présent
- [ ] Tools compilent sans erreur
- [ ] Tests locaux Docker OK

### Pendant Déploiement
- [ ] SSH serveur accessible
- [ ] Docker installé sur serveur
- [ ] Fichiers synchronisés (rsync ou git pull)
- [ ] Build image réussie
- [ ] Container démarre
- [ ] Health check OK

### Après Déploiement
- [ ] Reverse proxy configuré
- [ ] SSL actif (https://)
- [ ] Health check public OK
- [ ] Test conversation complète (3 messages)
- [ ] Events retournés respectent budget
- [ ] Logs montrent tools appelés
- [ ] WordPress connecté au backend

---

## 🎯 Prochaines Étapes

Une fois le backend V2 déployé en Docker :

1. **Configurer WordPress** :
   - WP Admin → Le Hiboo → Assistant IA
   - URL Backend : `https://ai.lehiboo.dilios.me`
   - API Key : (même que `.env.production`)

2. **Tester depuis WordPress** :
   - Ouvrir le chat frontend
   - Tester conversation complète
   - Vérifier que Hedwige se présente
   - Vérifier que events sont retournés

3. **Monitorer** :
   - Surveiller `docker-compose logs -f`
   - Vérifier health checks
   - Analyser temps de réponse
   - Tracker coûts OpenAI

4. **Optimiser** :
   - Ajuster system prompt si besoin
   - Affiner match scoring
   - Améliorer quick chips
   - Sprint 2 : Persistance conversations

---

**Version Backend :** 2.0.0
**Docker Image :** lehiboo-ai-backend:1.0.0
**Port :** 3004 (hôte) → 3000 (container)
**Status :** ✅ Prêt pour Production
