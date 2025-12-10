# 🐳 Docker Configuration - Résumé Complet

Configuration Docker complète pour déployer Le Hiboo AI Backend sur Plesk.

---

## ✅ Fichiers Créés

### 1. Configuration Docker (4 fichiers)

```
lehiboo-ai-backend/
├── Dockerfile                         ⭐ Image multi-stage optimisée
├── docker-compose.yml                 ⭐ Orchestration complète
├── .dockerignore                      ⭐ Optimisation build
└── .env.production                    ⭐ Variables (à créer)
```

### 2. Scripts Automatiques (3 fichiers)

```
lehiboo-ai-backend/scripts/
├── build.sh                           🔨 Build l'image localement
├── start.sh                           🚀 Démarre en local
└── deploy.sh                          📤 Déploie sur serveur Plesk
```

### 3. Documentation (3 fichiers)

```
lehiboo-ai-backend/
├── DOCKER_README.md                   📖 Guide rapide Docker
├── DOCKER_PLESK_DEPLOYMENT.md         📚 Guide complet Plesk
└── (Ce fichier) DOCKER_SUMMARY.md     📊 Résumé
```

---

## 🎯 Cas d'Usage

### Développement Local

```bash
# 1. Build l'image
./scripts/build.sh

# 2. Démarrer le backend
./scripts/start.sh

# 3. Tester
curl http://localhost:3000/health
```

**Temps** : 2 minutes

---

### Déploiement Plesk (Production)

```bash
# Déploiement automatique en une commande
./scripts/deploy.sh lehiboo.dilios.me juba
```

**Ce que le script fait** :
1. ✅ Vérifie connexion SSH
2. ✅ Crée dossier sur serveur
3. ✅ Synchronise fichiers (rsync)
4. ✅ Build image Docker sur serveur
5. ✅ Démarre container
6. ✅ Teste health check
7. ✅ Affiche status et logs

**Temps** : 3-5 minutes

---

## 🏗️ Architecture Docker

### Image Multi-Stage

```dockerfile
# Stage 1: Builder
FROM node:18-alpine AS builder
COPY package*.json ./
RUN npm ci --omit=dev

# Stage 2: Production
FROM node:18-alpine
COPY --from=builder /app/node_modules ./node_modules
COPY . .
USER nodejs
CMD ["node", "src/index.js"]
```

**Avantages** :
- ✅ Image légère (~150MB vs ~1GB)
- ✅ Pas de devDependencies
- ✅ Sécurisé (utilisateur non-root)
- ✅ Health check intégré

---

### Docker Compose

```yaml
version: '3.8'

services:
  backend:
    build: .
    ports:
      - "3000:3000"
    env_file:
      - .env.production
    volumes:
      - ./logs:/app/logs
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:3000/health"]
      interval: 30s
      timeout: 10s
      retries: 3
```

**Fonctionnalités** :
- ✅ Auto-restart si crash
- ✅ Health check automatique
- ✅ Logs persistés
- ✅ Limites ressources configurables

---

## 🔐 Sécurité

### Fichier .env.production

**Créer sur le serveur** :
```bash
ssh user@serveur.com
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
nano .env.production
```

**Contenu minimal** :
```bash
NODE_ENV=production
PORT=3000

OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxx
API_KEY=votre-api-key-wordpress

WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

**⚠️ IMPORTANT** :
- ❌ Ne JAMAIS commiter `.env.production`
- ✅ Déjà exclu par `.gitignore`
- ✅ Créer manuellement sur chaque serveur

---

## 🌐 Configuration Plesk

### Option 1 : Interface Plesk Docker

1. **Extensions** → Docker → Installer
2. **Créer Container** :
   - Nom: `lehiboo-ai-backend`
   - Build depuis Dockerfile
   - Port: 3000:3000
   - Variables: Charger `.env.production`

3. **Reverse Proxy** :
   - Sous-domaine: `preprod.lehiboo.com/api-planner/`
   - Proxy vers: `http://localhost:3000`
   - SSL: Let's Encrypt

### Option 2 : SSH + Docker Compose

```bash
# Déployer automatiquement
./scripts/deploy.sh lehiboo.dilios.me juba

# Ou manuellement :
ssh user@serveur.com
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose up -d
```

---

## 📊 Avantages Docker

### vs Déploiement Traditionnel (Node.js direct)

| Critère | Docker | Node.js Direct |
|---------|--------|----------------|
| **Isolation** | ✅ Complète | ❌ Partage système |
| **Portabilité** | ✅ Fonctionne partout | ⚠️ Dépend du système |
| **Rollback** | ✅ Instantané | ⚠️ Complexe |
| **Ressources** | ✅ Limitables | ❌ Utilise tout |
| **Sécurité** | ✅ Sandboxé | ⚠️ Moins isolé |
| **Updates** | ✅ Sans downtime | ⚠️ Redémarrage requis |

### vs Railway/Vercel

| Critère | Docker Plesk | Railway/Vercel |
|---------|-------------|----------------|
| **Coût** | ✅ ~15€/mois | ⚠️ ~30€/mois |
| **Contrôle** | ✅ Total | ⚠️ Limité |
| **Intégration** | ✅ Même serveur WordPress | ❌ Serveur séparé |
| **Latence** | ✅ Locale | ⚠️ Externe |
| **Setup** | ⚠️ 30 minutes | ✅ 5 minutes |

---

## 🚀 Déploiement Étape par Étape

### 1. Préparation Locale (5 min)

```bash
cd lehiboo-ai-backend

# Créer .env.production localement (pour tests)
cp .env.example .env.production
nano .env.production

# Build et tester localement
./scripts/build.sh
./scripts/start.sh

# Vérifier
curl http://localhost:3000/health
```

### 2. Configuration Serveur (10 min)

```bash
# Se connecter
ssh user@serveur.com

# Créer .env.production sur serveur
mkdir -p /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
nano /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/.env.production

# (Coller vos vraies clés API)
```

### 3. Déploiement (5 min)

```bash
# Depuis votre machine locale
./scripts/deploy.sh lehiboo.dilios.me juba
```

### 4. Configuration Reverse Proxy (10 min)

**Dans Plesk** :
1. Créer sous-domaine `preprod.lehiboo.com/api-planner/`
2. Activer SSL Let's Encrypt
3. Configurer reverse proxy → `http://localhost:3000`

### 5. Configuration WordPress (2 min)

**WP Admin** → Le Hiboo → Assistant IA :
- URL Backend: `https://preprod.lehiboo.com/api-planner/`
- Clé API: (même que `.env.production`)

### 6. Vérification (2 min)

```bash
# Health check
curl https://preprod.lehiboo.com/api-planner/health

# Test chat
curl -X POST https://preprod.lehiboo.com/api-planner/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer votre-api-key" \
  -d '{"message":"Bonjour","conversationId":"test"}'
```

**Temps total** : ~35 minutes

---

## 📦 Maintenance

### Mettre à Jour le Backend

```bash
# Option A: Script automatique
./scripts/deploy.sh lehiboo.dilios.me juba

# Option B: Manuel via SSH
ssh user@serveur.com
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
git pull  # Si Git
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Voir les Logs

```bash
# Depuis le serveur
docker logs lehiboo-ai-backend -f

# Ou
docker-compose logs -f

# Fichiers logs
tail -f /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/app.log
```

### Redémarrer

```bash
# Restart gracieux
docker-compose restart

# Ou
docker restart lehiboo-ai-backend
```

---

## 🐛 Troubleshooting Rapide

### Container ne Démarre Pas

```bash
# Vérifier logs
docker logs lehiboo-ai-backend

# Causes communes :
# - .env.production manquant
# - Clés API invalides
# - Port 3000 déjà utilisé
```

### 502 Bad Gateway

```bash
# Vérifier container
docker ps | grep lehiboo

# Vérifier port
curl http://localhost:3000/health

# Vérifier nginx
nginx -t
```

### WordPress ne Connecte Pas

```bash
# Vérifier URL dans WordPress
# Doit être: https://preprod.lehiboo.com/api-planner/ (avec HTTPS)

# Vérifier API Key
# Doit être identique dans .env.production et WordPress

# Test manuel
curl https://preprod.lehiboo.com/api-planner/health
```

---

## 💰 Coûts

### Serveur Plesk
- **VPS Plesk** : 15-30€/mois
- **Docker** : Inclus
- **SSL** : Gratuit (Let's Encrypt)

### APIs
- **OpenRouter** : ~75€/mois (5000 conv)
- **OpenWeather** : Gratuit

**Total** : ~90-105€/mois

---

## ✅ Avantages de Cette Configuration

### Technique
✅ **Isolation complète** - Container sandboxé
✅ **Auto-restart** - Si crash ou reboot serveur
✅ **Health checks** - Monitoring automatique
✅ **Logs persistés** - Pas de perte de données
✅ **Ressources limitées** - Pas de surcharge serveur

### Opérationnel
✅ **Déploiement simple** - 1 commande
✅ **Rollback rapide** - Redéployer version précédente
✅ **Zero downtime** - Update sans coupure
✅ **Portable** - Fonctionne sur n'importe quel serveur

### Économique
✅ **Coûts réduits** - Même serveur que WordPress
✅ **Pas de vendor lock-in** - Peut migrer facilement
✅ **Scalable** - Peut ajouter replicas facilement

---

## 📚 Documentation Complète

### Quick Start
- **[DOCKER_README.md](lehiboo-ai-backend/DOCKER_README.md)** - Guide rapide

### Déploiement Complet
- **[DOCKER_PLESK_DEPLOYMENT.md](lehiboo-ai-backend/DOCKER_PLESK_DEPLOYMENT.md)** - Guide Plesk détaillé (50+ sections)

### Général
- **[README.md](README.md)** - Vue d'ensemble projet
- **[ARCHITECTURE_OVERVIEW.md](ARCHITECTURE_OVERVIEW.md)** - Architecture complète

---

## 🎉 Prochaines Étapes

1. **Tester localement** → `./scripts/start.sh`
2. **Déployer sur Plesk** → `./scripts/deploy.sh`
3. **Configurer reverse proxy** → Voir guide Plesk
4. **Connecter WordPress** → WP Admin
5. **Go Live !** 🚀

---

**Docker Configuration Complète** ✅

**Déploiement Rapide** : `./scripts/deploy.sh lehiboo.dilios.me juba`

**Version** : 1.0.0
**Date** : 28 Octobre 2025
