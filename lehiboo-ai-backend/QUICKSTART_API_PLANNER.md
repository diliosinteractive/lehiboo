# ⚡ Quick Start - URL /api-planner/

Déploiement express sur `https://lehiboo.com/api-planner/`

---

## 🚀 Les 5 Étapes (10 minutes)

### 1. Transférer les Fichiers (FTP)

```
/Users/juba/.../lehiboo-ai-backend/
    ↓ (FTP/SFTP)
/var/www/vhosts/lehiboo.com/lehiboo-ai-backend/
```

### 2. SSH + Config

```bash
ssh juba@lehiboo.com
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend

nano .env.production
```

Collez :
```bash
NODE_ENV=production
PORT=3000

OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxx
WEATHER_API_KEY=xxxxxxxxxxxxx
API_KEY=votre-cle-secrete

WORDPRESS_URL=https://lehiboo.com
WORDPRESS_API_URL=https://lehiboo.com/wp-json
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

Sauvegarder : `Ctrl+X`, `Y`, `Entrée`

### 3. Déployer

```bash
./scripts/deploy-local.sh
```

Confirmez avec `y`

### 4. Nginx (Plesk)

**Domaines** → `lehiboo.com` → **Paramètres nginx**

Ajoutez :
```nginx
location /api-planner/ {
    proxy_pass http://localhost:3000/;
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

Recharger :
```bash
systemctl reload nginx
```

### 5. Test

```bash
curl https://lehiboo.com/api-planner/health
```

Résultat : `{"status":"ok"...}` ✅

---

## 🎯 WordPress

**WP Admin** → Le Hiboo → Assistant IA :
- URL : `https://lehiboo.com/api-planner/`
- Clé API : (même que `.env.production`)

**Tester** : Ouvrir chat → Envoyer message → IA répond ! 🎉

---

## 📖 Doc Complète

→ [DEPLOY_PLESK_API_PLANNER.md](DEPLOY_PLESK_API_PLANNER.md)

---

**URL Backend** : `https://lehiboo.com/api-planner/`

**Durée** : 10 minutes ⏱️
