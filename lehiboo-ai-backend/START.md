# ⚡ START - Démarrage Ultra-Rapide

Pour lancer le backend en 3 minutes.

---

## 🚀 Les 3 Étapes

### 1. Transférer les Fichiers (FTP)

```
Source : /Users/juba/.../lehiboo-ai-backend/
Destination : /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/
```

---

### 2. SSH + Config + Déployer

```bash
ssh juba@lehiboo.com
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend

# Config
nano .env.production
```

Collez :
```bash
NODE_ENV=production
PORT=3000

OPENROUTER_API_KEY=sk-or-v1-xxxxx
WEATHER_API_KEY=xxxxx
API_KEY=votre-cle

WORDPRESS_URL=https://lehiboo.com
WORDPRESS_API_URL=https://lehiboo.com/wp-json
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

Sauvegarder : `Ctrl+X`, `Y`, `Entrée`

```bash
# Lancer Docker
./scripts/deploy-local.sh
```

Confirmez avec `y`

**Résultat** : Docker tourne sur port 3000 ✅

---

### 3. Configurer Plesk Proxy

**Dans Plesk** :
- Source : `/api-planner/`
- Destination : `http://localhost:3000`

**Ou nginx direct** :
```nginx
location /api-planner/ {
    proxy_pass http://localhost:3000/;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

---

## ✅ Test

```bash
curl https://lehiboo.com/api-planner/health
```

Résultat : `{"status":"ok"}` 🎉

---

## 🎯 WordPress

WP Admin → Le Hiboo → Assistant IA :
- URL : `https://lehiboo.com/api-planner/`
- Clé API : (celle du .env.production)

---

## 📚 Guide Complet

→ [DEPLOY_SIMPLE_PLESK.md](DEPLOY_SIMPLE_PLESK.md)

---

**C'est prêt !** Backend sur port 3000, Plesk gère le proxy 🚀
