# Guide de Remédiation de Sécurité - Le Hiboo

## Contexte de l'Incident

Des processus de cryptomining malveillants ont été détectés sur le serveur :
- `/app/ePXKKgcl` - Cryptominer principal
- `/app/Wxr5Gtq5` - Malware secondaire
- Plusieurs autres processus suspects

Le container Docker `lehiboo-ai-backend` a été compromis.

---

## Étape 1 : Nettoyage Immédiat (SUR LE SERVEUR)

### 1.1 Exécuter le script de nettoyage

```bash
# Copier le script sur le serveur
scp scripts/security-cleanup.sh user@server:/tmp/

# Se connecter au serveur
ssh user@server

# Exécuter le script
chmod +x /tmp/security-cleanup.sh
sudo /tmp/security-cleanup.sh
```

### 1.2 Vérification manuelle

```bash
# Vérifier que les processus sont tués
ps aux | grep -E "ePXK|Wxr5|nRvx|BYcNW"

# Vérifier les connexions réseau
netstat -tulpn | grep ESTABLISHED
```

---

## Étape 2 : Régénérer TOUTES les Clés API

### 2.1 OpenAI API Key

1. Aller sur https://platform.openai.com/api-keys
2. Révoquer TOUTES les clés existantes
3. Créer une nouvelle clé
4. Mettre à jour dans `.env.production`

### 2.2 Générer les nouveaux secrets

```bash
# JWT Secret
openssl rand -base64 64

# API Key
openssl rand -hex 32

# PostgreSQL Password
openssl rand -base64 24
```

### 2.3 Mettre à jour les fichiers de configuration

1. Copier `.env.production.example` vers `.env.production`
2. Remplacer tous les placeholders par les vraies valeurs
3. **NE JAMAIS** committer `.env.production` dans git

---

## Étape 3 : Appliquer le Patch de Sécurité EventList

### Option A : Via mu-plugin (recommandé)

```bash
# Sur le serveur
mkdir -p /var/www/html/wp-content/mu-plugins/
cp scripts/patches/eventlist-ajax-security-patch.php \
   /var/www/html/wp-content/mu-plugins/
```

### Option B : Dans functions.php du thème enfant

```php
// Ajouter à la fin de wp-content/themes/meup-child/functions.php
require_once get_stylesheet_directory() . '/includes/security-ajax-patch.php';
```

Puis copier le fichier :
```bash
cp scripts/patches/eventlist-ajax-security-patch.php \
   wp-content/themes/meup-child/includes/security-ajax-patch.php
```

---

## Étape 4 : Déployer la Configuration Docker Sécurisée

### 4.1 Arrêter le container compromis

```bash
cd /path/to/lehiboo-ai-backend
docker-compose down
```

### 4.2 Reconstruire avec la configuration sécurisée

```bash
# Copier les nouveaux fichiers
cp docker-compose.secure.yml docker-compose.yml.backup
cp Dockerfile.secure Dockerfile.backup

# Créer le dossier nginx si nécessaire
mkdir -p nginx

# Builder la nouvelle image
docker-compose -f docker-compose.secure.yml build --no-cache

# Démarrer
docker-compose -f docker-compose.secure.yml up -d
```

### 4.3 Vérifier le déploiement

```bash
# Status des containers
docker-compose -f docker-compose.secure.yml ps

# Logs
docker-compose -f docker-compose.secure.yml logs -f backend

# Test health
curl http://localhost:3004/health
```

---

## Étape 5 : Configurer le Monitoring

### 5.1 Installer le script de monitoring

```bash
# Copier sur le serveur
sudo cp scripts/security-monitor.sh /usr/local/bin/
sudo chmod +x /usr/local/bin/security-monitor.sh

# Créer le dossier de logs
sudo mkdir -p /var/log/lehiboo-security
```

### 5.2 Ajouter au crontab

```bash
sudo crontab -e

# Ajouter cette ligne (scan toutes les 5 minutes)
*/5 * * * * /usr/local/bin/security-monitor.sh >> /var/log/lehiboo-security/cron.log 2>&1
```

---

## Étape 6 : Sécurisation Serveur

### 6.1 Firewall (UFW)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 3004/tcp
sudo ufw enable
```

### 6.2 Fail2ban

```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 6.3 Mises à jour automatiques

```bash
sudo apt install unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

---

## Étape 7 : Audit WordPress

### 7.1 Scanner avec Wordfence

1. Installer Wordfence depuis le dashboard WordPress
2. Lancer un scan complet
3. Examiner les alertes

### 7.2 Mettre à jour

```bash
# Via WP-CLI
wp core update
wp plugin update --all
wp theme update --all
```

### 7.3 Changer les salts WordPress

1. Aller sur https://api.wordpress.org/secret-key/1.1/salt/
2. Copier les nouveaux salts
3. Remplacer dans `wp-config.php`

---

## Checklist de Vérification

- [ ] Processus malveillants tués
- [ ] Container Docker arrêté et supprimé
- [ ] Clé OpenAI révoquée et régénérée
- [ ] JWT_SECRET régénéré
- [ ] API_KEY régénéré
- [ ] Mot de passe PostgreSQL changé
- [ ] Patch EventList appliqué
- [ ] Configuration Docker sécurisée déployée
- [ ] Monitoring activé
- [ ] Firewall configuré
- [ ] Fail2ban installé
- [ ] WordPress scanné
- [ ] Tous les plugins mis à jour
- [ ] Salts WordPress régénérés

---

## Contacts

En cas de problème :
- Email : dev@lehiboo.com
- Documentation : /docs/

---

## Fichiers Créés

| Fichier | Description |
|---------|-------------|
| `scripts/security-cleanup.sh` | Script de nettoyage et diagnostic |
| `scripts/security-monitor.sh` | Script de monitoring continu |
| `scripts/patches/eventlist-ajax-security-patch.php` | Patch sécurité plugin |
| `lehiboo-ai-backend/docker-compose.secure.yml` | Config Docker sécurisée |
| `lehiboo-ai-backend/Dockerfile.secure` | Image Docker durcie |
| `lehiboo-ai-backend/nginx/nginx.conf` | Config Nginx reverse proxy |
| `lehiboo-ai-backend/nginx/security-headers.conf` | Headers HTTP sécurité |
| `lehiboo-ai-backend/.env.production.example` | Template config prod |
