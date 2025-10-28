# ⚡ Quick Start Plesk - 5 Minutes

Déploiement **ultra-rapide** directement sur votre serveur Plesk.

---

## 🚀 Les 5 Étapes

### 1. Transférer les Fichiers (2 min)

Via FTP ou votre méthode préférée, transférez le dossier `lehiboo-ai-backend` vers :
```
/var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend/
```

### 2. Se Connecter en SSH (10 sec)

```bash
ssh juba@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
```

### 3. Configurer les Clés API (1 min)

```bash
nano .env.production
```

Collez et modifiez :
```bash
NODE_ENV=production
PORT=3000

# ⚠️ Remplacez par vos vraies clés
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxx
API_KEY=votre-cle-secrete-123

WORDPRESS_URL=https://lehiboo.dilios.me
WORDPRESS_API_URL=https://lehiboo.dilios.me/wp-json
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

**Sauvegarder** : `Ctrl+X`, `Y`, `Entrée`

### 4. Déployer (2 min)

```bash
./scripts/deploy-local.sh
```

Confirmez avec `y` quand demandé.

**Attendez que ça affiche** :
```
✅ Déploiement Terminé avec Succès !
```

### 5. Configurer Reverse Proxy Plesk (5 min)

**Dans Plesk** :

1. **Domaines** → Créer sous-domaine `ai.lehiboo.dilios.me`

2. **Paramètres nginx** → Ajouter :
   ```nginx
   location / {
       proxy_pass http://localhost:3000;
       proxy_http_version 1.1;
       proxy_set_header Host $host;
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $scheme;
   }
   ```

3. **SSL/TLS** → Activer Let's Encrypt

4. **Recharger nginx** :
   ```bash
   systemctl reload nginx
   ```

---

## ✅ Vérification

```bash
# Test
curl https://ai.lehiboo.dilios.me/health
```

**Résultat attendu** :
```json
{"status":"ok","timestamp":"...","version":"1.0.0"}
```

---

## 🎯 Configuration WordPress

**WP Admin** → Le Hiboo → Assistant IA :
- URL Backend : `https://ai.lehiboo.dilios.me`
- Clé API : (même que dans .env.production)

**Tester** : Ouvrir le chat sur le site → Envoyer un message → L'IA répond ! 🎉

---

## 🔧 Commandes Essentielles

```bash
# Logs
docker-compose logs -f

# Redémarrer
docker-compose restart

# Redéployer
./scripts/deploy-local.sh

# Status
docker-compose ps
```

---

## 📚 Documentation Complète

- **Guide détaillé** : [DEPLOY_DIRECT_PLESK.md](DEPLOY_DIRECT_PLESK.md)
- **Troubleshooting** : [DOCKER_PLESK_DEPLOYMENT.md](DOCKER_PLESK_DEPLOYMENT.md)
- **Docker général** : [DOCKER_README.md](DOCKER_README.md)

---

**Temps total** : ~10 minutes ⏱️

**C'est prêt !** 🚀
