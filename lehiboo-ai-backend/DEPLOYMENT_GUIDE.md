# 🚀 Guide de Déploiement Production

Guide complet pour déployer le backend Le Hiboo AI en production.

---

## 📋 Prérequis

- [ ] Tests d'intégration complétés (voir `INTEGRATION_TESTING.md`)
- [ ] Compte OpenRouter avec crédit
- [ ] Compte OpenWeatherMap
- [ ] Domaine configuré (ex: `ai.lehiboo.com`)
- [ ] Choix de plateforme : Railway / Vercel / VPS

---

## 🎯 Option 1 : Railway (Recommandé - Plus Simple)

### Avantages
- ✅ Gratuit pour démarrer ($5 crédit/mois)
- ✅ Déploiement automatique depuis Git
- ✅ Variables d'environnement faciles
- ✅ Logs et monitoring intégrés
- ✅ HTTPS automatique
- ✅ Scale automatique

### Étapes

#### 1. Créer Compte Railway

1. Aller sur https://railway.app
2. S'inscrire avec GitHub
3. Créer nouveau projet : **New Project**
4. Choisir : **Deploy from GitHub repo**

#### 2. Connecter Repository

```bash
# Si pas encore sur Git, initialiser
cd lehiboo-ai-backend
git init
git add .
git commit -m "Initial commit - Le Hiboo AI Backend"

# Créer repo GitHub
# (via GitHub website ou gh CLI)
gh repo create lehiboo-ai-backend --private --source=. --remote=origin --push
```

Sur Railway :
1. Sélectionner le repo `lehiboo-ai-backend`
2. Railway détecte automatiquement Node.js

#### 3. Configurer Variables d'Environnement

Dans Railway Dashboard → Variables :

```bash
# Obligatoires
NODE_ENV=production
PORT=3000
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx
API_KEY=votre-secret-production-key-different-de-dev
WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

# Recommandés
WEATHER_API_KEY=xxxxxxxxxxxxxxxxx
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
MAX_TOKENS=1000
TEMPERATURE=0.7
RATE_LIMIT_MAX_REQUESTS=20
LOG_LEVEL=info

# Optionnels
SENTRY_DSN=https://xxxxx@sentry.io/xxxxx
```

#### 4. Déployer

Railway déploie automatiquement ! 🎉

**URL générée** : `https://lehiboo-ai-backend-production.up.railway.app`

#### 5. Configurer Domaine Custom

Railway Dashboard → Settings → Domains :
1. **Add Domain** : `ai.lehiboo.com`
2. Ajouter CNAME dans DNS :
   ```
   ai.lehiboo.com → xxxxx.up.railway.app
   ```
3. Attendre propagation DNS (5-30 min)
4. ✅ HTTPS automatique activé !

#### 6. Vérifier Déploiement

```bash
# Health check
curl https://ai.lehiboo.com/health

# Résultat attendu :
{
  "status": "ok",
  "timestamp": "...",
  "version": "1.0.0"
}
```

#### 7. Configurer WordPress

```
WP Admin → Le Hiboo → Assistant IA → Paramètres
URL Backend : https://ai.lehiboo.com
Clé API : [même que API_KEY dans Railway]
Sauvegarder
```

✅ **Déploiement Railway Terminé !**

---

## 🎯 Option 2 : Vercel (Alternative)

### Avantages
- ✅ Gratuit (hobby plan)
- ✅ Très rapide (edge network)
- ✅ CI/CD automatique
- ✅ HTTPS automatique

### Limites
- ⚠️ Timeout 10s (Hobby) / 60s (Pro)
- ⚠️ Cold starts possibles
- ⚠️ Pas idéal pour long-polling

### Étapes

#### 1. Installer Vercel CLI

```bash
npm i -g vercel
vercel login
```

#### 2. Déployer

```bash
cd lehiboo-ai-backend
vercel

# Suivre les prompts :
# Set up and deploy? Yes
# Project name? lehiboo-ai-backend
# Deploy? Yes
```

#### 3. Configurer Variables

```bash
vercel env add OPENROUTER_API_KEY
vercel env add API_KEY
vercel env add WORDPRESS_URL
vercel env add WORDPRESS_API_URL
vercel env add WEATHER_API_KEY
vercel env add NODE_ENV production
```

#### 4. Redéployer avec Variables

```bash
vercel --prod
```

**URL générée** : `https://lehiboo-ai-backend.vercel.app`

#### 5. Domaine Custom

Vercel Dashboard → Domains → Add :
```
ai.lehiboo.com
```

✅ **Déploiement Vercel Terminé !**

---

## 🎯 Option 3 : VPS (Ubuntu) - Contrôle Total

### Avantages
- ✅ Contrôle complet
- ✅ Pas de timeout
- ✅ Scale manuel
- ✅ Coût prévisible

### Prérequis
- VPS Ubuntu 22.04+ (DigitalOcean, Linode, OVH...)
- Accès SSH root

### Étapes

#### 1. Préparer VPS

```bash
# Se connecter SSH
ssh root@votre-ip

# Update système
apt update && apt upgrade -y

# Installer Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Installer PM2
npm install -g pm2

# Créer user non-root
adduser lehiboo
usermod -aG sudo lehiboo
su - lehiboo
```

#### 2. Déployer Code

```bash
# Cloner repo
cd ~
git clone https://github.com/votre-org/lehiboo-ai-backend
cd lehiboo-ai-backend

# Installer dépendances
npm install --production

# Créer .env
nano .env
# Copier toutes les variables production
# Ctrl+X, Y, Enter pour sauvegarder
```

#### 3. Lancer avec PM2

```bash
# Démarrer
pm2 start src/index.js --name lehiboo-ai

# Sauvegarder config
pm2 save

# Auto-start au boot
pm2 startup
# Copier-coller la commande affichée

# Vérifier
pm2 status
pm2 logs lehiboo-ai
```

#### 4. Nginx Reverse Proxy

```bash
# Installer Nginx
sudo apt install -y nginx

# Créer config
sudo nano /etc/nginx/sites-available/lehiboo-ai
```

**Contenu** :
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
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

```bash
# Activer site
sudo ln -s /etc/nginx/sites-available/lehiboo-ai /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 5. HTTPS avec Let's Encrypt

```bash
# Installer Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtenir certificat
sudo certbot --nginx -d ai.lehiboo.com

# Auto-renewal test
sudo certbot renew --dry-run
```

#### 6. Firewall

```bash
# UFW
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

#### 7. Monitoring

```bash
# Installer htop
sudo apt install -y htop

# Monitoring PM2
pm2 monit
```

✅ **Déploiement VPS Terminé !**

---

## 🔒 Sécurité Production

### 1. Variables d'Environnement

**IMPORTANT** :
```bash
# Générer API_KEY sécurisée
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"

# Utiliser DIFFÉRENTE de dev !
API_KEY=production-key-64-chars-xxxxxxxxxxxxxx
```

### 2. Rate Limiting

```bash
# Adapter selon trafic
RATE_LIMIT_MAX_REQUESTS=20  # Start conservateur
RATE_LIMIT_WINDOW_MS=60000  # 1 minute
```

### 3. CORS

```bash
# Limiter aux domaines autorisés
ALLOWED_ORIGINS=https://lehiboo.dilios.me,https://www.lehiboo.dilios.me
```

### 4. Logs

```bash
# Log level production
LOG_LEVEL=info  # Pas debug !

# Log rotation (VPS)
sudo apt install -y logrotate
```

### 5. Monitoring Erreurs

**Sentry** (recommandé) :
1. Créer compte : https://sentry.io
2. Créer projet Node.js
3. Copier DSN
4. Ajouter variable :
   ```bash
   SENTRY_DSN=https://xxxxx@sentry.io/xxxxx
   ```

---

## 📊 Monitoring Production

### Métriques à Surveiller

**Performance** :
- Temps réponse `/chat` (< 2s)
- Temps réponse `/health` (< 100ms)
- Uptime (> 99.9%)

**Ressources** :
- CPU usage (< 70%)
- RAM usage (< 80%)
- Disk space (> 20% libre)

**IA / APIs** :
- Coût OpenRouter (dashboard)
- Quota Weather API (1000/jour gratuit)
- Taux erreur tools (< 1%)

**Business** :
- Conversations / jour
- Taux conversion (chat → booking)
- Satisfaction utilisateur

### Tools Monitoring

#### Railway
- Dashboard intégré : Metrics, Logs
- Alertes email sur erreurs

#### Vercel
- Analytics : Usage, Performance
- Logs real-time

#### VPS
```bash
# PM2 Monitoring
pm2 monit
pm2 logs --lines 100

# Resources
htop
df -h  # Disk

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Alertes

**Sentry** :
- Configure alerts sur erreurs critiques
- Email + Slack notifications

**UptimeRobot** (gratuit) :
- Monitor endpoint `/health` toutes les 5 min
- Alert si down > 2 min

---

## 💰 Coûts Estimés

### Railway
- **Hobby** : $5 crédit/mois (gratuit au début)
- **Pro** : $20/mois (scale illimité)
- **Estimé** : ~$10-15/mois pour 5k conversations

### Vercel
- **Hobby** : Gratuit
- **Pro** : $20/mois (si timeout 60s nécessaire)
- **Estimé** : Gratuit ou $20/mois

### VPS
- **DigitalOcean Droplet** : $6-12/mois (1-2 GB RAM)
- **Linode** : $5-10/mois
- **OVH** : €3-8/mois
- **Estimé** : ~$10/mois

### APIs Externes
- **OpenRouter** : ~$50-100/mois (5k conv à 0.01-0.02€)
- **OpenWeather** : Gratuit (< 1k calls/jour) ou $10/mois (100k)
- **Total APIs** : ~$50-110/mois

**TOTAL ESTIMÉ** : **$60-135/mois** pour 5000 conversations

---

## 🚀 Post-Déploiement

### Checklist

- [ ] Health check répond : `curl https://ai.lehiboo.com/health`
- [ ] WordPress connecté : Settings OK
- [ ] Test conversation complète : Frontend → IA répond
- [ ] MCP Tools fonctionnent : search_events retourne vrais événements
- [ ] Météo fonctionne : Alertes contextuelles
- [ ] Logs accessibles : Pas d'erreurs
- [ ] Monitoring actif : Sentry / UptimeRobot
- [ ] Backup configuré : Code sur Git
- [ ] Documentation à jour : URL production
- [ ] Équipe informée : Accès dashboards

### Tests Post-Deploy

```bash
# 1. Health
curl https://ai.lehiboo.com/health

# 2. Status (avec API key)
curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://ai.lehiboo.com/status

# 3. Chat complet
curl -X POST https://ai.lehiboo.com/chat \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Je cherche une activité sportive",
    "conversationId": "test-prod-123",
    "currentStage": "greeting"
  }'
```

### Go Live ! 🎉

1. **Annoncer** : Email équipe, update docs
2. **Monitorer** : Première heure, surveiller logs
3. **Itérer** : Collecter feedback, ajuster prompts
4. **Scale** : Si succès, upgrade plan

---

## 🔧 Maintenance

### Updates

```bash
# Railway / Vercel : Automatique via Git
git push origin main

# VPS : Manuel
ssh lehiboo@votre-ip
cd ~/lehiboo-ai-backend
git pull
npm install
pm2 restart lehiboo-ai
```

### Rollback

```bash
# Railway / Vercel : Dashboard → Deployments → Rollback

# VPS
git checkout previous-commit
pm2 restart lehiboo-ai
```

### Backup

```bash
# Variables .env : SAUVEGARDER LOCALEMENT (Password manager)
# Code : Git (déjà sauvegardé)
# Logs : Archiver mensuellement
```

---

## 📚 Ressources

- **Railway** : https://docs.railway.app
- **Vercel** : https://vercel.com/docs
- **PM2** : https://pm2.keymetrics.io/docs
- **Nginx** : https://nginx.org/en/docs
- **Sentry** : https://docs.sentry.io
- **OpenRouter** : https://openrouter.ai/docs

---

**Développé avec ❤️ pour Le Hiboo**

**Status** : 🚀 **Prêt pour Production !**
