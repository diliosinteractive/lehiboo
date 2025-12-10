# 🎯 Déploiement Direct sur Votre Serveur Plesk

Guide complet pour **votre cas d'usage** : vous déployez directement sur le serveur sans passer par SSH depuis votre machine locale.

---

## 📋 Résumé de Votre Setup

**Votre situation** :
- ✅ Vous avez un serveur Plesk avec WordPress déjà installé
- ✅ Domaine : `lehiboo.dilios.me`
- ✅ Vous voulez déployer le backend avec Docker
- ✅ Vous allez lancer le script **directement sur le serveur**

**Ce qu'on va faire** :
1. Transférer les fichiers backend sur le serveur (FTP ou autre)
2. Se connecter en SSH au serveur
3. Configurer les clés API
4. Lancer le script de déploiement
5. Configurer le reverse proxy Plesk
6. Connecter WordPress au backend

---

## 🚀 Processus Complet

### Préparation (Sur Votre Machine Locale)

Vous avez déjà tous les fichiers dans :
```
/Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend/
```

**Fichiers importants** :
- ✅ `Dockerfile` - Configuration Docker
- ✅ `docker-compose.yml` - Orchestration
- ✅ `package.json` - Dépendances Node.js
- ✅ `src/` - Code source
- ✅ `scripts/deploy-local.sh` - **Script de déploiement pour vous**
- ✅ `.env.example` - Template de configuration

---

### Étape 1 : Transférer les Fichiers sur le Serveur

#### Option A : FTP/SFTP (Le Plus Simple)

Utilisez votre client FTP préféré (FileZilla, Cyberduck, etc.) :

**Serveur** : `lehiboo.dilios.me` (ou IP du serveur)
**Utilisateur** : `juba`
**Protocole** : SFTP (port 22) ou FTP

**Transférer** :
```
Source (local):
  /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend/

Destination (serveur):
  /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

**Important** : Transférez **tout le dossier**, y compris les sous-dossiers cachés.

#### Option B : Interface Plesk File Manager

1. Connexion Plesk : `https://lehiboo.dilios.me:8443`
2. **Fichiers** → Naviguer vers `/var/www/vhosts/lehiboo.dilios.me/`
3. **Créer un dossier** : `lehiboo-ai-backend`
4. **Upload** tous les fichiers depuis votre machine

#### Option C : Rsync (Si disponible)

```bash
# Depuis votre machine locale (terminal)
rsync -avz \
  --exclude 'node_modules' \
  --exclude 'logs' \
  /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend/ \
  juba@lehiboo.dilios.me:/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

**Durée** : 30 secondes - 2 minutes selon méthode et connexion.

---

### Étape 2 : Se Connecter au Serveur

```bash
# Depuis votre terminal (macOS)
ssh juba@lehiboo.dilios.me
```

**Si première connexion** :
```
The authenticity of host 'lehiboo.dilios.me' can't be established.
Are you sure you want to continue connecting (yes/no)?
```
→ Tapez `yes`

**Vous verrez** :
```bash
juba@server:~$
```

---

### Étape 3 : Aller dans le Dossier Backend

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
```

**Vérifier que les fichiers sont là** :
```bash
ls -la
```

**Vous devriez voir** :
```
Dockerfile
docker-compose.yml
package.json
src/
scripts/
.env.example
...
```

---

### Étape 4 : Créer le Fichier de Configuration

```bash
# Créer le fichier
nano .env.production
```

**Collez ce contenu** (en remplaçant les valeurs) :

```bash
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Production Environment Variables - Le Hiboo AI Backend
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

# Environment
NODE_ENV=production
PORT=3000
LOG_LEVEL=info

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# API Keys - ⚠️ REMPLACEZ PAR VOS VRAIES CLÉS !
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

# OpenRouter (https://openrouter.ai/keys)
# Créez un compte et générez une clé API
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx

# OpenWeather (https://openweathermap.org/api)
# Gratuit jusqu'à 1000 appels/jour
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxx

# Clé API pour WordPress
# Créez une clé secrète complexe (ex: utilisez un générateur de mot de passe)
API_KEY=votre-cle-api-super-secrete-a-creer-123

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# WordPress Configuration
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

# URL de votre site WordPress (sans slash final)
WORDPRESS_URL=https://lehiboo.dilios.me

# URL de l'API REST WordPress
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# AI Configuration
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

# Modèle IA par défaut (recommandé)
DEFAULT_MODEL=anthropic/claude-3.5-sonnet

# Alternatives :
# DEFAULT_MODEL=openai/gpt-4-turbo
# DEFAULT_MODEL=openai/gpt-3.5-turbo  (moins cher)
# DEFAULT_MODEL=meta-llama/llama-3.1-70b-instruct

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Rate Limiting (Protection contre abus)
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

# Fenêtre de temps en millisecondes (60000 = 1 minute)
RATE_LIMIT_WINDOW_MS=60000

# Nombre maximum de requêtes par fenêtre
RATE_LIMIT_MAX_REQUESTS=20
```

**Sauvegarder** :
- Appuyez sur `Ctrl+X`
- Tapez `Y` (pour Yes)
- Appuyez sur `Entrée`

---

### Étape 5 : Lancer le Déploiement

```bash
./scripts/deploy-local.sh
```

**Le script va** :
1. Vérifier Docker et docker-compose
2. Vérifier votre fichier .env.production
3. Vous demander confirmation
4. Arrêter l'ancien container (si existe)
5. Build l'image Docker (1-2 minutes)
6. Démarrer le nouveau container
7. Tester que tout fonctionne

**Vous verrez** :

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    Le Hiboo AI Backend - Déploiement Local
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📂 Dossier actuel: /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

✅ Docker détecté: Docker version 24.0.0
✅ docker-compose détecté: docker-compose version 2.20.0
✅ Fichier .env.production trouvé
✅ Variables d'environnement OK

📊 Configuration:
  - Node Env: production
  - Port: 3000
  - WordPress: https://lehiboo.dilios.me
  - Model: anthropic/claude-3.5-sonnet

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Êtes-vous prêt à déployer ? (y/N)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**→ Tapez `y` puis Entrée**

Le script continue :

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    Démarrage du Déploiement
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📦 Étape 1/5: Arrêt de l'ancien container...
ℹ️  Aucun container en cours d'exécution

🧹 Étape 2/5: Nettoyage des anciennes images...
✅ Nettoyage terminé

🔨 Étape 3/5: Build de la nouvelle image Docker...
   (Cela peut prendre 1-2 minutes la première fois...)

[+] Building 45.2s (12/12) FINISHED
 => [internal] load build definition from Dockerfile
 => => transferring dockerfile: 1.23kB
 => [internal] load .dockerignore
 => [builder 1/4] FROM docker.io/library/node:18-alpine
 => [builder 2/4] WORKDIR /app
 => [builder 3/4] COPY package*.json ./
 => [builder 4/4] RUN npm ci --omit=dev
 => [stage-1 1/3] COPY --from=builder /app/node_modules ./node_modules
 => [stage-1 2/3] COPY . .
 => [stage-1 3/3] RUN mkdir -p logs
 => exporting to image
 => => exporting layers
 => => writing image sha256:abc123...
 => => naming to docker.io/library/lehiboo-ai-backend:1.0.0

✅ Image Docker buildée avec succès

🚀 Étape 4/5: Démarrage du nouveau container...
Creating lehiboo-ai-backend ... done
✅ Container démarré

🔍 Étape 5/5: Vérifications...
⏳ Attente du démarrage (10 secondes)...

📊 Status des containers:
NAME                  STATUS              PORTS
lehiboo-ai-backend    Up 10 seconds       0.0.0.0:3000->3000/tcp

📄 Logs récents (dernières 20 lignes):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
backend_1  | [INFO] Starting Le Hiboo AI Backend...
backend_1  | [INFO] Environment: production
backend_1  | [INFO] Port: 3000
backend_1  | [INFO] Testing OpenRouter connection...
backend_1  | [INFO] ✅ OpenRouter connection successful
backend_1  | [INFO] Testing Weather API connection...
backend_1  | [INFO] ✅ Weather API connection successful
backend_1  | [INFO] 🚀 Le Hiboo AI Backend started successfully
backend_1  | [INFO]   env: production
backend_1  | [INFO]   host: 0.0.0.0
backend_1  | [INFO]   port: 3000
backend_1  | [INFO]   url: http://0.0.0.0:3000
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🧪 Test du health check local...
✅ Backend opérationnel !

Réponse:
{
  "status": "ok",
  "timestamp": "2025-10-28T12:30:00.000Z",
  "version": "1.0.0"
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Déploiement Terminé avec Succès !
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📍 Backend accessible localement:
   http://localhost:3000

🌐 Si reverse proxy configuré, accessible via:
   https://preprod.lehiboo.com/api-planner/
```

**Parfait ! Le backend tourne maintenant.** ✅

---

### Étape 6 : Configurer le Reverse Proxy dans Plesk

Le backend est accessible sur `http://localhost:3000` **depuis le serveur**.

Pour le rendre accessible depuis Internet via `https://preprod.lehiboo.com/api-planner/` :

#### 6.1 Créer le Sous-Domaine

1. Ouvrir Plesk : `https://lehiboo.dilios.me:8443`
2. **Domaines** → **Ajouter un sous-domaine**
3. Remplir :
   - **Nom du sous-domaine** : `ai`
   - **Domaine parent** : `lehiboo.dilios.me`
   - **Document root** : `/var/www/vhosts/lehiboo.dilios.me/ai` (peu importe, pas utilisé)
4. **OK**

#### 6.2 Configurer le Proxy Nginx

1. Cliquer sur le sous-domaine `preprod.lehiboo.com/api-planner/`
2. **Paramètres Apache & nginx**
3. Descendre jusqu'à **Directives nginx supplémentaires**
4. Coller :

```nginx
location / {
    proxy_pass http://localhost:3000;
    proxy_http_version 1.1;

    # Headers
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;

    # Timeouts
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;

    # Buffers
    proxy_buffering off;
    proxy_request_buffering off;
}
```

5. **OK**

#### 6.3 Activer SSL/TLS

1. Toujours sur `preprod.lehiboo.com/api-planner/`
2. **SSL/TLS**
3. Cocher **Certificat Let's Encrypt**
4. **Installer** ou **Renouveler**
5. Attendre 30 secondes

#### 6.4 Recharger Nginx

```bash
# Toujours connecté en SSH sur le serveur
nginx -t
systemctl reload nginx
```

---

### Étape 7 : Vérifier que Tout Fonctionne

#### Test depuis Internet

```bash
# Depuis votre machine locale (nouveau terminal)
curl https://preprod.lehiboo.com/api-planner/health
```

**Résultat attendu** :
```json
{
  "status": "ok",
  "timestamp": "2025-10-28T12:30:00.000Z",
  "version": "1.0.0"
}
```

**Si erreur 502** → Voir section Troubleshooting plus bas

---

### Étape 8 : Configurer WordPress

1. Ouvrir WordPress Admin : `https://lehiboo.dilios.me/wp-admin`
2. **Le Hiboo** → **Assistant IA** → **Paramètres**
3. Remplir :
   - ✅ **Activer l'assistant IA**
   - **URL Backend** : `https://preprod.lehiboo.com/api-planner/`
   - **Clé API** : Copier la valeur de `API_KEY` depuis `.env.production`
4. **Sauvegarder**

#### Test Final

1. Ouvrir le site Le Hiboo : `https://lehiboo.dilios.me`
2. Cliquer sur le bouton chat orange (en bas à droite)
3. Le chat s'ouvre (mode immersif)
4. Envoyer un message : `Bonjour`
5. **L'IA doit répondre !** 🎉

**Si l'IA répond** → ✅ **Tout fonctionne parfaitement !**

---

## 🎉 C'est Terminé !

Votre backend Le Hiboo AI est maintenant :
- ✅ Déployé sur votre serveur Plesk
- ✅ Tournant dans un container Docker
- ✅ Accessible via `https://preprod.lehiboo.com/api-planner/`
- ✅ Connecté à WordPress
- ✅ Prêt à recevoir des utilisateurs

---

## 🔧 Commandes Utiles au Quotidien

### Voir les Logs en Temps Réel

```bash
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose logs -f
```

**Sortir** : `Ctrl+C`

### Redémarrer le Backend

```bash
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose restart
```

### Redéployer (après modification)

```bash
# 1. Transférer les nouveaux fichiers (FTP)
# 2. Puis :
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
./scripts/deploy-local.sh
```

### Status des Containers

```bash
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose ps
```

---

## 🐛 Troubleshooting

### Erreur 502 Bad Gateway

**Symptôme** : `https://preprod.lehiboo.com/api-planner/` renvoie une erreur 502

**Vérifications** :

```bash
# 1. Container tourne ?
docker-compose ps
# Devrait afficher "Up"

# 2. Test local sur serveur
curl http://localhost:3000/health
# Devrait retourner {"status":"ok"...}

# 3. Nginx config OK ?
nginx -t
# Devrait afficher "syntax is ok"

# 4. Logs nginx
tail -f /var/log/nginx/error.log
```

**Solution** : Si le container tourne et le test local fonctionne, c'est un problème nginx → Revérifier la config du reverse proxy

### Container ne Démarre Pas

```bash
# Voir les logs détaillés
docker-compose logs

# Causes communes :
# - Clé API invalide → Vérifier .env.production
# - Port 3000 occupé → Changer le port
```

### WordPress ne Connecte Pas

**Vérifier** :
1. URL dans WordPress : `https://preprod.lehiboo.com/api-planner/` (avec HTTPS, sans slash final)
2. Clé API : Identique dans `.env.production` et WordPress
3. Test manuel :
   ```bash
   curl https://preprod.lehiboo.com/api-planner/health
   ```

---

## 📚 Documentation Complète

- **Quick Start** : [QUICKSTART_PLESK.md](lehiboo-ai-backend/QUICKSTART_PLESK.md)
- **Guide Détaillé** : [DEPLOY_DIRECT_PLESK.md](lehiboo-ai-backend/DEPLOY_DIRECT_PLESK.md)
- **Docker Général** : [DOCKER_README.md](lehiboo-ai-backend/DOCKER_README.md)
- **Plesk Complet** : [DOCKER_PLESK_DEPLOYMENT.md](lehiboo-ai-backend/DOCKER_PLESK_DEPLOYMENT.md)

---

## ✅ Checklist Finale

- [ ] Fichiers transférés sur serveur
- [ ] .env.production créé avec vraies clés
- [ ] Script exécuté : `./scripts/deploy-local.sh`
- [ ] Backend opérationnel : `curl http://localhost:3000/health`
- [ ] Sous-domaine créé dans Plesk
- [ ] Reverse proxy configuré
- [ ] SSL Let's Encrypt activé
- [ ] Nginx rechargé
- [ ] Test externe OK : `curl https://preprod.lehiboo.com/api-planner/health`
- [ ] WordPress configuré
- [ ] Test frontend OK (chat répond)

---

**Félicitations !** Votre backend IA est en production ! 🚀

**Questions ?** Consultez la documentation ou contactez dev@lehiboo.com
