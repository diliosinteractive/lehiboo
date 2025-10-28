# 🚀 Déploiement Simple - Docker + Plesk Proxy

Guide ultra-simple : lancez Docker sur un port, Plesk gère le proxy automatiquement.

---

## 🎯 Ce Qu'on Va Faire

1. ✅ Transférer les fichiers sur le serveur
2. ✅ Lancer Docker sur un port (ex: 3000)
3. ✅ Configurer le proxy dans Plesk pour pointer `/api-planner/` vers ce port
4. ✅ C'est tout ! Plesk gère le reste (SSL, reverse proxy, etc.)

---

## ⚡ Déploiement (5 minutes)

### Étape 1 : Transférer les Fichiers

Via FTP/SFTP, transférez vers :
```
Destination serveur:
  /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/
```

---

### Étape 2 : Configuration

```bash
# SSH vers le serveur
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

# Clés API
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxx
API_KEY=votre-cle-secrete-123

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

### Étape 3 : Lancer Docker

```bash
./scripts/deploy-local.sh
```

Confirmez avec `y`.

**Résultat** :
```
✅ Déploiement Terminé avec Succès !
📍 Backend accessible localement: http://localhost:3000
```

**Docker tourne maintenant sur le port 3000** ✅

---

### Étape 4 : Configurer le Proxy dans Plesk

Maintenant **vous configurez dans Plesk** pour pointer `/api-planner/` vers le port 3000.

#### Dans l'Interface Plesk :

1. **Domaines** → Cliquer sur `lehiboo.com`
2. Cherchez l'option **"Docker Proxy"** ou **"Reverse Proxy"**
3. Configurez :
   ```
   URL Source : /api-planner/
   Destination : http://localhost:3000
   ```

**OU** si via les paramètres nginx manuellement :

**Paramètres Apache & nginx** → **Directives nginx supplémentaires** :

```nginx
location /api-planner/ {
    proxy_pass http://localhost:3000/;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

4. **Appliquer** / **OK**
5. Nginx se recharge automatiquement

---

### Étape 5 : Test

```bash
# Depuis n'importe où
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

✅ **Ça marche !**

---

## 📝 Résumé Simple

### Ce Que Vous Avez Fait

1. ✅ Fichiers transférés sur le serveur
2. ✅ `.env.production` créé avec vos clés
3. ✅ Docker lancé sur port 3000 : `./scripts/deploy-local.sh`
4. ✅ Plesk configuré pour proxy `/api-planner/` → port 3000
5. ✅ Fini !

### Architecture

```
Internet
    ↓
HTTPS https://lehiboo.com/api-planner/
    ↓
Plesk Proxy (vous gérez ça)
    ↓
http://localhost:3000
    ↓
Docker Container
    ↓
Le Hiboo AI Backend
```

---

## 🎯 Configuration WordPress

**WP Admin** → **Le Hiboo** → **Assistant IA** :
```
URL Backend : https://lehiboo.com/api-planner/
Clé API     : votre-cle-secrete-123
```

**Test** : Ouvrir le chat → Envoyer message → IA répond ! 🎉

---

## 🔧 Commandes Utiles

### Voir les Logs Docker

```bash
ssh juba@lehiboo.com
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose logs -f
```

### Redémarrer Docker

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose restart
```

### Arrêter Docker

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose down
```

### Redéployer (après modification)

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
./scripts/deploy-local.sh
```

### Status

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
docker-compose ps
```

---

## 🔄 Changer de Port (si 3000 occupé)

Si le port 3000 est déjà utilisé :

### 1. Modifier docker-compose.yml

```bash
nano docker-compose.yml
```

Changez :
```yaml
ports:
  - "3001:3000"  # Utilisez 3001 au lieu de 3000
```

### 2. Redéployer

```bash
docker-compose down
docker-compose up -d
```

### 3. Mettre à Jour Plesk

Dans Plesk, changez la destination du proxy :
```
Destination : http://localhost:3001
```

---

## 🐛 Troubleshooting Simple

### Docker ne démarre pas

```bash
# Voir les logs
docker-compose logs

# Causes communes :
# - Port 3000 occupé → Changez le port
# - .env.production manquant → Créez-le
# - Clé API invalide → Vérifiez .env.production
```

### 404 ou 502 depuis Plesk

```bash
# Vérifier que Docker tourne
docker-compose ps
# Doit afficher "Up"

# Vérifier localement sur le serveur
curl http://localhost:3000/health
# Doit retourner {"status":"ok"}

# Si ça marche localement mais pas depuis Internet
# → Problème de config proxy dans Plesk
# → Vérifiez la configuration du proxy
```

---

## 📊 Ports Disponibles

Si besoin de changer de port :

| Port | Usage Typique |
|------|---------------|
| 3000 | **Recommandé** (par défaut) |
| 3001 | Alternative 1 |
| 3002 | Alternative 2 |
| 8080 | Alternative 3 |
| 8000 | Alternative 4 |

Évitez : 80, 443, 22, 21, 3306, 5432 (déjà utilisés)

---

## ✅ Checklist

- [ ] Fichiers transférés : `/var/www/vhosts/lehiboo.com/lehiboo-ai-backend/`
- [ ] `.env.production` créé avec clés API
- [ ] Docker démarré : `./scripts/deploy-local.sh`
- [ ] Container tourne : `docker-compose ps` → "Up"
- [ ] Test local OK : `curl http://localhost:3000/health`
- [ ] Proxy configuré dans Plesk : `/api-planner/` → `localhost:3000`
- [ ] Test externe OK : `curl https://lehiboo.com/api-planner/health`
- [ ] WordPress configuré
- [ ] Chat frontend OK

---

## 💡 Avantages

✅ **Ultra simple** - Juste lancer Docker, Plesk fait le reste
✅ **Flexible** - Changez de port facilement
✅ **Sécurisé** - Docker isolé, Plesk gère SSL/TLS
✅ **Maintenable** - Redéploiement en 1 commande
✅ **Pas de config nginx manuelle** - Plesk s'en occupe

---

## 🎉 C'est Tout !

**3 commandes essentielles** :

```bash
# 1. Déployer
./scripts/deploy-local.sh

# 2. Voir les logs
docker-compose logs -f

# 3. Redémarrer
docker-compose restart
```

**URL Backend** : `https://lehiboo.com/api-planner/`

**Port Docker** : `3000` (ou celui que vous choisissez)

---

**Déploiement Simple Plesk** - Le Hiboo AI Assistant v1.0.0

**Temps total** : 5 minutes ⏱️
