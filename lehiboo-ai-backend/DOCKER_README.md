# 🐳 Docker - Le Hiboo AI Backend

Guide rapide pour utiliser Docker avec Le Hiboo AI Backend.

---

## 📦 Fichiers Docker

```
lehiboo-ai-backend/
├── Dockerfile                    # Image Docker multi-stage
├── docker-compose.yml            # Orchestration
├── .dockerignore                 # Exclusions build
├── .env.production               # Variables (à créer)
└── scripts/
    ├── build.sh                  # Build l'image
    ├── start.sh                  # Démarre en local
    └── deploy.sh                 # Déploie sur serveur
```

---

## 🚀 Quick Start

### 1. Build l'Image

```bash
# Option A: Script automatique
./scripts/build.sh

# Option B: Manuel
docker-compose build
```

### 2. Configurer les Variables

```bash
# Créer .env.production
cp .env.example .env.production

# Éditer avec vos clés API
nano .env.production
```

**Variables obligatoires** :
- `OPENROUTER_API_KEY` - Clé OpenRouter
- `WEATHER_API_KEY` - Clé OpenWeather
- `API_KEY` - Clé secrète WordPress
- `WORDPRESS_URL` - URL WordPress

### 3. Démarrer

```bash
# Option A: Script automatique
./scripts/start.sh

# Option B: Manuel
docker-compose up -d
```

### 4. Vérifier

```bash
# Voir les logs
docker-compose logs -f

# Health check
curl http://localhost:3000/health

# Status
docker-compose ps
```

---

## 🌐 Déploiement Serveur Plesk

### Quick Deploy

```bash
./scripts/deploy.sh lehiboo.dilios.me juba
```

### Guide Complet

Voir [DOCKER_PLESK_DEPLOYMENT.md](DOCKER_PLESK_DEPLOYMENT.md) pour :
- Configuration Plesk Docker
- Configuration Reverse Proxy
- SSL/TLS Let's Encrypt
- Troubleshooting

---

## 📊 Commandes Utiles

### Développement

```bash
# Démarrer
docker-compose up -d

# Arrêter
docker-compose down

# Redémarrer
docker-compose restart

# Rebuild complet
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Logs

```bash
# Logs temps réel
docker-compose logs -f

# Logs d'un service
docker-compose logs -f backend

# Dernières 100 lignes
docker-compose logs --tail 100
```

### Debug

```bash
# Entrer dans le container
docker-compose exec backend sh

# Vérifier les variables d'environnement
docker-compose exec backend env

# Tester depuis le container
docker-compose exec backend curl http://localhost:3000/health
```

### Monitoring

```bash
# Status
docker-compose ps

# Stats ressources
docker stats lehiboo-ai-backend

# Espace disque
docker system df
```

---

## 🔧 Configuration Docker Compose

### Ports

```yaml
ports:
  - "3000:3000"  # Host:Container
```

Change le port hôte si 3000 est déjà utilisé :
```yaml
ports:
  - "3001:3000"
```

### Ressources

Par défaut :
- **CPU** : 1 core max, 0.5 réservé
- **RAM** : 512MB max, 256MB réservé

Ajuster dans `docker-compose.yml` :
```yaml
deploy:
  resources:
    limits:
      cpus: '2.0'
      memory: 1G
```

### Volumes

Les logs sont persistés :
```yaml
volumes:
  - ./logs:/app/logs
```

---

## 🏗️ Architecture Image

### Multi-Stage Build

**Stage 1 (Builder)** :
- Base: `node:18-alpine`
- Installation dépendances production
- npm ci --omit=dev

**Stage 2 (Production)** :
- Base: `node:18-alpine`
- Copie node_modules depuis builder
- Utilisateur non-root (nodejs:1001)
- dumb-init pour gestion signaux

### Optimisations

✅ **Image légère** : Alpine Linux (~150MB)
✅ **Multi-stage** : Exclut dev dependencies
✅ **Non-root user** : Sécurité
✅ **Health check** : Auto-restart si fail
✅ **.dockerignore** : Exclut node_modules, logs

---

## 🔐 Sécurité

### Fichier .env.production

**⚠️ NE JAMAIS COMMITER .env.production**

Le `.gitignore` l'exclut automatiquement :
```
.env
.env.*
!.env.example
```

### Variables Sensibles

Utiliser Docker secrets en production (optionnel) :
```yaml
secrets:
  openrouter_key:
    external: true

services:
  backend:
    secrets:
      - openrouter_key
```

---

## 📈 Performance

### Build Cache

Utiliser le cache pour builds rapides :
```bash
# Build avec cache
docker-compose build

# Build sans cache
docker-compose build --no-cache
```

### Layer Caching

Le Dockerfile est optimisé pour cache layers :
1. Copie `package*.json` seul → cache si pas changé
2. npm install → cache si package.json identique
3. Copie code source → invalidé à chaque changement

---

## 🐛 Troubleshooting

### Container ne Démarre Pas

```bash
# Voir les logs détaillés
docker-compose logs backend

# Vérifier la configuration
docker-compose config

# Rebuild complet
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Port Déjà Utilisé

```bash
# Vérifier qui utilise le port 3000
lsof -i :3000  # macOS/Linux
netstat -ano | findstr :3000  # Windows

# Changer le port dans docker-compose.yml
ports:
  - "3001:3000"
```

### Permission Denied

Le container utilise l'utilisateur `nodejs` (UID 1001).

Fixer les permissions logs :
```bash
sudo chown -R 1001:1001 logs/
```

### Out of Memory

Augmenter les limites :
```yaml
deploy:
  resources:
    limits:
      memory: 1G
```

---

## 📦 Image Docker Hub (Optionnel)

### Tag & Push

```bash
# Tag l'image
docker tag lehiboo-ai-backend:1.0.0 username/lehiboo-ai-backend:1.0.0
docker tag lehiboo-ai-backend:1.0.0 username/lehiboo-ai-backend:latest

# Login Docker Hub
docker login

# Push
docker push username/lehiboo-ai-backend:1.0.0
docker push username/lehiboo-ai-backend:latest
```

### Pull sur Serveur

```bash
# Sur le serveur
docker pull username/lehiboo-ai-backend:latest

# Mettre à jour docker-compose.yml
services:
  backend:
    image: username/lehiboo-ai-backend:latest
```

---

## 🔄 CI/CD (Optionnel)

### GitHub Actions

Créer `.github/workflows/docker-publish.yml` :
```yaml
name: Docker Build & Push

on:
  push:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Build Docker image
        run: docker build -t lehiboo-ai-backend .

      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}

      - name: Push to Docker Hub
        run: |
          docker tag lehiboo-ai-backend username/lehiboo-ai-backend:latest
          docker push username/lehiboo-ai-backend:latest
```

---

## 📚 Ressources

### Documentation
- **Docker** : [docs.docker.com](https://docs.docker.com)
- **Docker Compose** : [docs.docker.com/compose](https://docs.docker.com/compose/)
- **Node.js Alpine** : [hub.docker.com/_/node](https://hub.docker.com/_/node)

### Guides Projet
- **Déploiement Plesk** : [DOCKER_PLESK_DEPLOYMENT.md](DOCKER_PLESK_DEPLOYMENT.md)
- **Quick Start** : [QUICK_START.md](QUICK_START.md)
- **Deployment Guide** : [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

---

## ✅ Checklist

### Développement Local
- [ ] Docker & Docker Compose installés
- [ ] Fichier `.env.production` créé
- [ ] Image buildée (`./scripts/build.sh`)
- [ ] Container démarré (`./scripts/start.sh`)
- [ ] Health check OK (`curl http://localhost:3000/health`)
- [ ] Logs OK (`docker-compose logs -f`)

### Production Plesk
- [ ] Fichiers transférés sur serveur
- [ ] `.env.production` configuré sur serveur
- [ ] Container démarré via Plesk ou SSH
- [ ] Reverse proxy configuré
- [ ] SSL/TLS activé (Let's Encrypt)
- [ ] Health check OK depuis Internet
- [ ] WordPress connecté au backend

---

**Docker Configuration** - Le Hiboo AI Backend v1.0.0

**Quick Deploy** : `./scripts/deploy.sh lehiboo.dilios.me juba`
