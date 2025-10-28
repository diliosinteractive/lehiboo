# 🚀 Déploiement Direct sur Serveur Plesk

Guide ultra-simple pour déployer **directement sur votre serveur Plesk** (sans SSH depuis votre machine locale).

---

## 🎯 Votre Situation

Vous êtes **déjà connecté en SSH sur votre serveur Plesk** et vous voulez déployer le backend Docker.

---

## ⚡ Déploiement Rapide (15 minutes)

### Étape 1 : Transférer les Fichiers sur le Serveur

Plusieurs options selon votre préférence :

#### Option A : FTP/SFTP (Recommandé pour vous)

Utilisez FileZilla, Cyberduck, ou l'interface FTP de Plesk :

```
Transférer le dossier:
  lehiboo-ai-backend/

Vers le serveur:
  /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

#### Option B : Git (Si votre projet est sur GitHub)

```bash
# Sur le serveur
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/
git clone https://github.com/votre-repo/lehiboo-ai-backend.git
```

#### Option C : Rsync (Depuis votre machine locale)

```bash
# Depuis votre machine locale
rsync -avz lehiboo-ai-backend/ \
  juba@lehiboo.dilios.me:/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

---

### Étape 2 : Se Connecter au Serveur

```bash
ssh juba@lehiboo.dilios.me
```

---

### Étape 3 : Aller dans le Dossier

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
```

---

### Étape 4 : Configurer les Variables d'Environnement

```bash
# Créer le fichier de configuration
nano .env.production
```

**Collez ce contenu** (remplacez par vos vraies valeurs) :

```bash
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Production Environment Variables
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

NODE_ENV=production
PORT=3000
LOG_LEVEL=info

# ⚠️ IMPORTANT: Remplacez par vos vraies clés API !

# OpenRouter (https://openrouter.ai/keys)
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx

# OpenWeather (https://openweathermap.org/api)
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxx

# Clé API pour WordPress (créez une clé secrète)
API_KEY=votre-cle-api-super-secrete-123

# WordPress URLs (adaptez selon votre domaine)
WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json

# Modèle IA par défaut
DEFAULT_MODEL=anthropic/claude-3.5-sonnet

# Rate Limiting
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=20
```

**Sauvegarder** : `Ctrl+X`, puis `Y`, puis `Entrée`

---

### Étape 5 : Lancer le Déploiement

```bash
./scripts/deploy-local.sh
```

**Ce que fait le script** :
1. ✅ Vérifie Docker et docker-compose
2. ✅ Vérifie le fichier .env.production
3. ✅ Arrête l'ancien container (si existe)
4. ✅ Build la nouvelle image Docker
5. ✅ Démarre le nouveau container
6. ✅ Teste que tout fonctionne

**Durée** : 2-3 minutes

**Vous verrez** :
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    Le Hiboo AI Backend - Déploiement Local
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

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
```

**Tapez `y` puis Entrée**

Le script va alors :
```
📦 Étape 1/5: Arrêt de l'ancien container...
✅ Container arrêté

🧹 Étape 2/5: Nettoyage des anciennes images...
✅ Nettoyage terminé

🔨 Étape 3/5: Build de la nouvelle image Docker...
   (Cela peut prendre 1-2 minutes la première fois...)
[+] Building 45.2s
✅ Image Docker buildée avec succès

🚀 Étape 4/5: Démarrage du nouveau container...
✅ Container démarré

🔍 Étape 5/5: Vérifications...
⏳ Attente du démarrage (10 secondes)...

📊 Status des containers:
NAME                  STATUS              PORTS
lehiboo-ai-backend    Up 10 seconds       0.0.0.0:3000->3000/tcp

📄 Logs récents:
[INFO] Testing OpenRouter connection...
[INFO] ✅ OpenRouter connection successful
[INFO] Testing Weather API connection...
[INFO] ✅ Weather API connection successful
[INFO] 🚀 Le Hiboo AI Backend started
[INFO]   env: production
[INFO]   port: 3000

🧪 Test du health check local...
✅ Backend opérationnel !

Réponse:
{
  "status": "ok",
  "timestamp": "2025-10-28T12:00:00.000Z",
  "version": "1.0.0"
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Déploiement Terminé avec Succès !
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Étape 6 : Configurer le Reverse Proxy Plesk

Le backend tourne maintenant sur `http://localhost:3000` **sur le serveur**.

Pour le rendre accessible depuis Internet via `https://ai.lehiboo.dilios.me` :

#### Dans l'Interface Plesk :

1. **Domaines** → **Ajouter un sous-domaine**
   - Nom : `ai`
   - Domaine parent : `lehiboo.dilios.me`
   - Document root : `/var/www/vhosts/lehiboo.dilios.me/ai` (n'a pas d'importance)

2. **Cliquez sur le sous-domaine** `ai.lehiboo.dilios.me`

3. **Paramètres Apache & nginx** → **Directives nginx supplémentaires**

   Collez ceci :
   ```nginx
   location / {
       proxy_pass http://localhost:3000;
       proxy_http_version 1.1;

       proxy_set_header Host $host;
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $scheme;

       proxy_connect_timeout 60s;
       proxy_send_timeout 60s;
       proxy_read_timeout 60s;

       proxy_buffering off;
   }
   ```

4. **SSL/TLS** → Activer **Let's Encrypt**

5. **OK** pour sauvegarder

6. **Recharger Nginx** :
   ```bash
   # Sur le serveur
   nginx -t
   systemctl reload nginx
   ```

---

### Étape 7 : Vérifier que Tout Fonctionne

#### Test 1 : Health Check depuis Internet

```bash
# Depuis votre machine locale ou n'importe où
curl https://ai.lehiboo.dilios.me/health
```

**Résultat attendu** :
```json
{
  "status": "ok",
  "timestamp": "2025-10-28T12:00:00.000Z",
  "version": "1.0.0"
}
```

#### Test 2 : Chat Endpoint

```bash
curl -X POST https://ai.lehiboo.dilios.me/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer votre-cle-api" \
  -d '{
    "message": "Bonjour",
    "conversationId": "test-123",
    "currentStage": "greeting"
  }'
```

---

### Étape 8 : Configurer WordPress

1. **WP Admin** → **Le Hiboo** → **Assistant IA** → **Paramètres**

2. **Remplir** :
   - ✅ **Activer l'assistant IA**
   - **URL Backend** : `https://ai.lehiboo.dilios.me`
   - **Clé API** : `votre-cle-api` (même que dans .env.production)

3. **Sauvegarder**

4. **Tester sur le Frontend** :
   - Ouvrir le site Le Hiboo
   - Cliquer sur le bouton chat (orange en bas à droite)
   - Envoyer un message
   - L'IA devrait répondre ! 🎉

---

## 🔧 Commandes Utiles

### Voir les Logs en Temps Réel

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose logs -f
```

**Sortir** : `Ctrl+C`

### Redémarrer le Backend

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose restart
```

### Arrêter le Backend

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose down
```

### Redémarrer Complètement (Rebuild)

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Voir le Status

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
docker-compose ps
```

### Test Local

```bash
# Sur le serveur
curl http://localhost:3000/health
```

---

## 🔄 Mettre à Jour le Backend

Quand vous modifiez le code :

### Option 1 : Script Automatique

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
./scripts/deploy-local.sh
```

### Option 2 : Manuel

```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# 1. Transférer les nouveaux fichiers (FTP, Git, etc.)
# Ou si Git:
git pull

# 2. Redéployer
docker-compose down
docker-compose build
docker-compose up -d

# 3. Vérifier
docker-compose logs -f
```

---

## 🐛 Problèmes Courants

### Container ne démarre pas

**Symptôme** :
```bash
docker-compose ps
# Status: Exited (1)
```

**Solution** :
```bash
# Voir les logs
docker-compose logs

# Causes communes:
# - .env.production manquant ou mal configuré
# - Clés API invalides
# - Port 3000 déjà utilisé
```

### 502 Bad Gateway

**Symptôme** : Erreur 502 en accédant à `https://ai.lehiboo.dilios.me`

**Solutions** :
```bash
# 1. Vérifier que le container tourne
docker-compose ps

# 2. Vérifier localement
curl http://localhost:3000/health

# 3. Vérifier nginx
nginx -t
systemctl status nginx

# 4. Vérifier les logs nginx
tail -f /var/log/nginx/error.log
```

### Port 3000 déjà utilisé

**Symptôme** :
```
Error: bind: address already in use
```

**Solution 1** : Trouver et arrêter le processus
```bash
# Trouver ce qui utilise le port 3000
lsof -i :3000

# Arrêter le processus
kill -9 <PID>
```

**Solution 2** : Changer le port dans docker-compose.yml
```yaml
ports:
  - "3001:3000"  # Utiliser 3001 à la place
```

Puis mettre à jour le reverse proxy nginx pour pointer vers `localhost:3001`

---

## 📊 Surveillance

### Ressources Utilisées

```bash
# Stats en temps réel
docker stats lehiboo-ai-backend
```

### Espace Disque

```bash
# Voir l'espace utilisé par Docker
docker system df

# Nettoyer si besoin
docker system prune -a
```

### Logs Persistants

Les logs sont sauvegardés dans :
```
/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/
```

```bash
# Voir les logs de l'app
tail -f /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/app.log

# Voir les erreurs
tail -f /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/logs/error.log
```

---

## ✅ Checklist Complète

### Installation Initiale
- [ ] Fichiers transférés sur serveur
- [ ] .env.production créé avec vraies clés API
- [ ] Script exécuté : `./scripts/deploy-local.sh`
- [ ] Container démarré (docker-compose ps)
- [ ] Health check OK : `curl http://localhost:3000/health`
- [ ] Reverse proxy configuré dans Plesk
- [ ] SSL Let's Encrypt activé
- [ ] Health check externe OK : `curl https://ai.lehiboo.dilios.me/health`
- [ ] WordPress configuré
- [ ] Test frontend → backend → IA OK

### Maintenance Régulière
- [ ] Vérifier les logs : `docker-compose logs`
- [ ] Vérifier l'espace disque : `docker system df`
- [ ] Vérifier les ressources : `docker stats`
- [ ] Nettoyer si besoin : `docker system prune`

---

## 💰 Avantages de Cette Approche

✅ **Simple** - Tout sur le même serveur que WordPress
✅ **Économique** - Pas de serveur externe (Railway/Vercel)
✅ **Rapide** - Latence minimale (localhost)
✅ **Contrôle total** - Vous gérez tout
✅ **Isolation** - Docker sépare tout proprement

---

## 🎉 Vous Êtes Prêt !

Le backend tourne maintenant sur votre serveur Plesk avec Docker.

**URL Backend** : `https://ai.lehiboo.dilios.me`
**Container** : `lehiboo-ai-backend`
**Logs** : `docker-compose logs -f`

**Pour redéployer après une modification** :
```bash
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
./scripts/deploy-local.sh
```

---

**Déploiement Direct Plesk** - Le Hiboo AI Assistant v1.0.0

**Questions ?** Consultez [DOCKER_README.md](DOCKER_README.md) ou [DOCKER_PLESK_DEPLOYMENT.md](DOCKER_PLESK_DEPLOYMENT.md)
