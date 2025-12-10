# 🐳 Déploiement Docker sur Plesk

Guide complet pour déployer Le Hiboo AI Backend sur Plesk avec Docker Proxy.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Architecture](#architecture)
3. [Configuration Locale](#configuration-locale)
4. [Déploiement sur Plesk](#déploiement-sur-plesk)
5. [Configuration Reverse Proxy](#configuration-reverse-proxy)
6. [Vérification](#vérification)
7. [Maintenance](#maintenance)
8. [Troubleshooting](#troubleshooting)

---

## ✅ Prérequis

### Sur Votre Machine Locale

- [x] Docker installé (version 20.10+)
- [x] Docker Compose installé (version 2.0+)
- [x] Accès SSH au serveur Plesk
- [x] Git (optionnel, pour cloner le projet)

Vérifier :
```bash
docker --version
docker-compose --version
ssh user@votre-serveur.com
```

### Sur le Serveur Plesk

- [x] Plesk Obsidian 18.0.40+ (avec Docker support)
- [x] Extension Docker activée
- [x] Extension Proxy activée (pour reverse proxy)
- [x] Domaine/sous-domaine configuré (ex: `preprod.lehiboo.com/api-planner/`)

---

## 🏗️ Architecture

```
Internet (HTTPS)
    ↓
Plesk Nginx (Port 443)
    ↓
Plesk Docker Proxy
    ↓
Docker Container (Port 3000)
    ↓
Le Hiboo AI Backend
```

**Avantages** :
- ✅ SSL/TLS géré par Plesk (Let's Encrypt)
- ✅ Reverse proxy automatique
- ✅ Isolation du backend dans Docker
- ✅ Facilité de déploiement et rollback
- ✅ Gestion des ressources (CPU, RAM)

---

## ⚙️ Configuration Locale

### 1. Préparer les Fichiers

```bash
cd lehiboo-ai-backend

# Vérifier que les fichiers Docker existent
ls -la Dockerfile docker-compose.yml .dockerignore
```

### 2. Créer le Fichier .env.production

```bash
# Créer le fichier
nano .env.production
```

**Contenu** (remplacez par vos vraies valeurs) :
```bash
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Production Environment Variables
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

NODE_ENV=production
PORT=3000
LOG_LEVEL=info

# API Keys
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxx
API_KEY=votre-api-key-super-secrete-123

# WordPress
WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

# OpenRouter
DEFAULT_MODEL=anthropic/claude-3.5-sonnet

# Rate Limiting
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20
```

**Sauvegarder** : `Ctrl+X`, `Y`, `Enter`

### 3. Tester Localement (Optionnel mais Recommandé)

```bash
# Build l'image Docker
docker-compose build

# Démarrer le container
docker-compose up -d

# Vérifier les logs
docker-compose logs -f

# Tester le health check
curl http://localhost:3000/health

# Arrêter
docker-compose down
```

---

## 🚀 Déploiement sur Plesk

### Option A : Via Interface Plesk (Recommandé)

#### Étape 1 : Activer Docker dans Plesk

1. **Connexion** : Connectez-vous à Plesk (`https://votre-serveur.com:8443`)
2. **Extensions** : Allez dans `Extensions` → `Docker`
3. **Installer** : Installez l'extension Docker si ce n'est pas fait

#### Étape 2 : Transférer les Fichiers

```bash
# Depuis votre machine locale
# Remplacez user@serveur.com par vos identifiants

# Créer le dossier sur le serveur
ssh user@serveur.com "mkdir -p /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend"

# Transférer tous les fichiers
scp -r . user@serveur.com:/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/

# Ou utiliser rsync (plus rapide)
rsync -avz --exclude 'node_modules' --exclude 'logs' \
  . user@serveur.com:/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

#### Étape 3 : Créer le Container dans Plesk

1. **Docker** : Dans Plesk, allez dans `Docker` → `Conteneurs`
2. **Créer** : Cliquez `Créer un conteneur`
3. **Configuration** :

   **Onglet Général** :
   - **Nom** : `lehiboo-ai-backend`
   - **Image** : `node:18-alpine` (sera reconstruite)

   **Onglet Build** :
   - **Build depuis Dockerfile** : ✅ Activé
   - **Chemin du contexte** : `/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend`
   - **Nom du Dockerfile** : `Dockerfile`

   **Onglet Réseau** :
   - **Port mappings** :
     ```
     Host Port: 3000
     Container Port: 3000
     Protocol: TCP
     ```

   **Onglet Variables d'environnement** :
   - **Fichier .env** : Cochez et sélectionnez `.env.production`
   - Ou ajoutez manuellement :
     ```
     NODE_ENV=production
     PORT=3000
     OPENROUTER_API_KEY=sk-or-v1-xxxxx
     API_KEY=votre-api-key
     ...
     ```

   **Onglet Volumes** :
   - **Logs** :
     ```
     Host Path: /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs
     Container Path: /app/logs
     ```

   **Onglet Ressources** (Recommandé) :
   - **CPU** : 1 CPU
   - **Mémoire** : 512 MB
   - **Restart Policy** : `Unless Stopped`

4. **Créer** : Cliquez sur `Créer et démarrer`

#### Étape 4 : Vérifier le Déploiement

```bash
# Se connecter au serveur
ssh user@serveur.com

# Vérifier que le container tourne
docker ps | grep lehiboo-ai-backend

# Voir les logs
docker logs lehiboo-ai-backend -f

# Tester localement sur le serveur
curl http://localhost:3000/health
```

---

### Option B : Via SSH et Docker Compose

```bash
# 1. Se connecter au serveur
ssh user@serveur.com

# 2. Aller dans le dossier
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# 3. Build et démarrer
docker-compose build
docker-compose up -d

# 4. Vérifier
docker-compose ps
docker-compose logs -f
```

---

## 🌐 Configuration Reverse Proxy

### Méthode 1 : Via Plesk Docker Proxy Extension

1. **Extension Proxy** : Allez dans `Extensions` → `Docker Proxy`
2. **Installer** : Installez si ce n'est pas fait
3. **Créer une règle** :
   - **Domaine** : `preprod.lehiboo.com/api-planner/`
   - **Port** : `3000`
   - **Container** : `lehiboo-ai-backend`
   - **SSL** : ✅ Activer (Let's Encrypt)

### Méthode 2 : Via Configuration Manuelle Nginx

#### Créer un Sous-Domaine

1. **Domaines** : Dans Plesk, allez dans `Domaines` → `Ajouter un sous-domaine`
2. **Nom** : `ai`
3. **Domaine parent** : `lehiboo.dilios.me`
4. **Document root** : `/var/www/vhosts/lehiboo.dilios.me/ai` (n'a pas d'importance)

#### Configurer le Reverse Proxy

1. **Hébergement** : Cliquez sur le sous-domaine `preprod.lehiboo.com/api-planner/`
2. **Paramètres Apache & nginx** : Cliquez sur `Paramètres Apache & nginx`
3. **Directives nginx supplémentaires** :

```nginx
# Reverse proxy vers Docker container
location / {
    proxy_pass http://localhost:3000;
    proxy_http_version 1.1;

    # Headers
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;

    # Timeouts
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;

    # Buffers
    proxy_buffering off;
    proxy_request_buffering off;
}

# Health check endpoint (optionnel)
location /health {
    proxy_pass http://localhost:3000/health;
    access_log off;
}
```

4. **SSL/TLS** : Activer Let's Encrypt
   - Allez dans `SSL/TLS`
   - Cochez `Certificat Let's Encrypt`
   - Cliquez `Installer`

5. **Appliquer** : Cliquez `OK`

#### Recharger Nginx

```bash
# Se connecter au serveur
ssh user@serveur.com

# Tester la configuration
nginx -t

# Recharger
systemctl reload nginx
```

---

## ✅ Vérification

### 1. Vérifier Docker Container

```bash
# Se connecter au serveur
ssh user@serveur.com

# Lister les containers
docker ps

# Devrait afficher :
# CONTAINER ID   IMAGE                        STATUS         PORTS
# xxxxxxxxxx     lehiboo-ai-backend:1.0.0     Up X minutes   0.0.0.0:3000->3000/tcp

# Vérifier les logs
docker logs lehiboo-ai-backend --tail 50

# Health check
docker exec lehiboo-ai-backend curl http://localhost:3000/health
```

### 2. Tester le Reverse Proxy

```bash
# Depuis votre machine locale
curl https://preprod.lehiboo.com/api-planner/health

# Devrait retourner :
# {"status":"ok","timestamp":"2025-10-28T...","version":"1.0.0"}
```

### 3. Tester le Chat Endpoint

```bash
curl -X POST https://preprod.lehiboo.com/api-planner/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer votre-api-key" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'
```

### 4. Configurer WordPress

1. **WP Admin** : Allez dans `Le Hiboo → Assistant IA → Paramètres`
2. **URL Backend** : `https://preprod.lehiboo.com/api-planner/`
3. **Clé API** : `votre-api-key` (même que dans .env.production)
4. **Sauvegarder**

5. **Tester sur le Frontend** :
   - Ouvrir le site Le Hiboo
   - Cliquer sur le chat
   - Envoyer un message
   - L'IA devrait répondre via le backend Docker !

---

## 🔧 Maintenance

### Mettre à Jour le Backend

```bash
# 1. Se connecter au serveur
ssh user@serveur.com

# 2. Aller dans le dossier
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# 3. Pull les changements (si Git)
git pull origin main

# 4. Rebuild l'image
docker-compose build

# 5. Redémarrer le container
docker-compose up -d

# 6. Vérifier
docker-compose logs -f
```

### Voir les Logs

```bash
# Logs en temps réel
docker logs lehiboo-ai-backend -f

# Ou avec docker-compose
docker-compose logs -f

# Logs dans le volume
tail -f /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/app.log
```

### Redémarrer le Container

```bash
# Avec docker-compose
docker-compose restart

# Ou directement
docker restart lehiboo-ai-backend
```

### Arrêter/Démarrer

```bash
# Arrêter
docker-compose down

# Démarrer
docker-compose up -d

# Rebuild complet
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

## 🐛 Troubleshooting

### Container ne Démarre Pas

**Symptôme** : Container crash au démarrage

**Solutions** :
```bash
# Vérifier les logs
docker logs lehiboo-ai-backend

# Erreurs communes :
# - "OPENROUTER_API_KEY is required" → Vérifier .env.production
# - "Port 3000 already in use" → Changer le port
# - "Cannot find module" → Rebuild l'image
```

### Reverse Proxy 502 Bad Gateway

**Symptôme** : Erreur 502 en accédant à `preprod.lehiboo.com/api-planner/`

**Solutions** :
```bash
# 1. Vérifier que le container tourne
docker ps | grep lehiboo-ai-backend

# 2. Vérifier le port mapping
docker port lehiboo-ai-backend

# 3. Tester localement sur le serveur
curl http://localhost:3000/health

# 4. Vérifier nginx
nginx -t
systemctl status nginx

# 5. Vérifier les logs nginx
tail -f /var/log/nginx/error.log
```

### SSL Certificate Error

**Symptôme** : Erreur SSL en accédant au site

**Solutions** :
```bash
# Renouveler le certificat Let's Encrypt
plesk bin extension --exec letsencrypt cli.php -d preprod.lehiboo.com/api-planner/ -m renew

# Ou via interface Plesk :
# Domaines → preprod.lehiboo.com/api-planner/ → SSL/TLS → Renouveler
```

### WordPress ne Peut Pas Connecter

**Symptôme** : WordPress affiche "Backend non disponible"

**Solutions** :
1. **Vérifier URL** : `https://preprod.lehiboo.com/api-planner/` (avec HTTPS)
2. **Vérifier API Key** : Doit être identique dans .env et WordPress
3. **Tester manuellement** :
   ```bash
   curl https://preprod.lehiboo.com/api-planner/health
   ```
4. **Vérifier CORS** : Le backend autorise le domaine WordPress

### High Memory Usage

**Symptôme** : Container consomme trop de RAM

**Solutions** :
```bash
# Limiter la mémoire dans docker-compose.yml
services:
  backend:
    deploy:
      resources:
        limits:
          memory: 512M

# Redémarrer
docker-compose down
docker-compose up -d

# Vérifier l'utilisation
docker stats lehiboo-ai-backend
```

---

## 📊 Monitoring

### Surveiller les Ressources

```bash
# Stats en temps réel
docker stats lehiboo-ai-backend

# Utilisation disque
docker system df
```

### Logs Rotation

Créer `/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/logrotate.conf` :
```
/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 0644 root root
}
```

---

## 💰 Coûts Estimés

### Serveur Plesk
- **VPS Plesk** : 15-30€/mois (selon ressources)
- **Domaine** : Existant
- **SSL** : Gratuit (Let's Encrypt)

### APIs
- **OpenRouter** : ~75€/mois (5000 conversations)
- **OpenWeather** : Gratuit (< 1000 appels/jour)

**Total** : ~90-105€/mois

---

## ✅ Checklist Déploiement

- [ ] Docker installé localement
- [ ] Fichiers Docker créés (Dockerfile, docker-compose.yml, .dockerignore)
- [ ] `.env.production` créé avec vraies clés API
- [ ] Build testé localement
- [ ] Fichiers transférés sur serveur Plesk
- [ ] Container créé et démarré dans Plesk
- [ ] Reverse proxy configuré
- [ ] SSL/TLS activé (Let's Encrypt)
- [ ] Health check OK (`/health`)
- [ ] WordPress configuré avec URL backend
- [ ] Test end-to-end frontend → backend → IA OK
- [ ] Monitoring configuré
- [ ] Logs rotation configurée

---

## 🆘 Support

### Documentation
- **Docker** : [docs.docker.com](https://docs.docker.com)
- **Plesk Docker** : [docs.plesk.com/en-US/obsidian/administrator-guide/docker](https://docs.plesk.com/en-US/obsidian/administrator-guide/docker/)
- **Nginx Reverse Proxy** : [nginx.org/en/docs/http/ngx_http_proxy_module.html](http://nginx.org/en/docs/http/ngx_http_proxy_module.html)

### Contact
- **Email** : dev@lehiboo.com
- **GitHub Issues** : Pour bugs et features

---

**Déploiement Docker sur Plesk** - Le Hiboo AI Assistant v1.0.0

**Date** : 28 Octobre 2025
