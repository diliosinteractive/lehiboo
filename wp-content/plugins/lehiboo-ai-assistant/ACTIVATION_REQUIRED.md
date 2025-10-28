# ⚠️ ACTIVATION REQUISE - Le Hiboo AI Assistant

## 🚨 Erreurs Actuelles (Normales)

Tu vois ces erreurs dans la console :
```
404 - /wp-json/lehiboo/v1/conversation/load
500 - /wp-json/lehiboo/v1/conversation/save
```

**C'EST NORMAL !** Les nouveaux endpoints et tables DB ne sont pas encore créés.

---

## ✅ Solution : Réactiver le Plugin

### Sur Preprod/Production

#### Option 1 : Via WP-CLI (Recommandé)
```bash
ssh ton-user@preprod.lehiboo.com
cd /var/www/vhosts/preprod.lehiboo.com/httpdocs

# Désactiver
wp plugin deactivate lehiboo-ai-assistant

# Réactiver (crée les tables DB + enregistre les routes REST)
wp plugin activate lehiboo-ai-assistant

# Vérifier les tables créées
wp db query "SHOW TABLES LIKE 'wp_lehiboo_user_%';"
```

#### Option 2 : Via l'Admin WordPress
1. Aller sur `https://preprod.lehiboo.com/wp-admin/plugins.php`
2. Désactiver "Le Hiboo AI Assistant"
3. Réactiver "Le Hiboo AI Assistant"
4. Vérifier que tout fonctionne

---

## 📋 Tables DB Créées

Après réactivation, ces 3 tables seront créées :

### 1. `wp_lehiboo_user_conversations`
```sql
CREATE TABLE wp_lehiboo_user_conversations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    conversation_id varchar(100) NOT NULL,
    messages longtext NOT NULL,
    user_context text DEFAULT NULL,
    current_stage varchar(50) DEFAULT 'greeting',
    last_message_at datetime DEFAULT CURRENT_TIMESTAMP,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY conversation_id (conversation_id)
);
```

### 2. `wp_lehiboo_user_favorites`
```sql
CREATE TABLE wp_lehiboo_user_favorites (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    event_id bigint(20) NOT NULL,
    added_from_conversation_id varchar(100) DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_event (user_id, event_id)
);
```

### 3. `wp_lehiboo_conversations` (Analytics - déjà existante)
### 4. `wp_lehiboo_rate_limits` (Rate limiting - déjà existante)

---

## 🔌 Nouveaux Endpoints REST API

Après réactivation, ces endpoints seront disponibles :

- `POST /wp-json/lehiboo/v1/conversation/save`
- `GET /wp-json/lehiboo/v1/conversation/load?conversationId=xxx`
- `GET /wp-json/lehiboo/v1/conversations`
- `DELETE /wp-json/lehiboo/v1/conversation/{id}`
- `POST /wp-json/lehiboo/v1/favorites/add`
- `POST /wp-json/lehiboo/v1/favorites/remove`
- `GET /wp-json/lehiboo/v1/favorites`

---

## ✅ Vérification Post-Activation

### 1. Vérifier les Tables
```bash
wp db query "SELECT COUNT(*) as count FROM wp_lehiboo_user_conversations;"
wp db query "SELECT COUNT(*) as count FROM wp_lehiboo_user_favorites;"
```

### 2. Tester les Endpoints
```bash
# Health check (devrait marcher même avant)
curl https://preprod.lehiboo.com/wp-json/lehiboo/v1/health

# Conversations (nécessite auth)
curl -X GET https://preprod.lehiboo.com/wp-json/lehiboo/v1/conversations \
  -H "Cookie: wordpress_logged_in_xxx=yyy"
```

### 3. Vérifier la Console Browser
Après réactivation, tu devrais voir :
```
[Persistence] Auto-save started
✅ Plus d'erreurs 404/500
```

---

## 🎯 Comportement Actuel (Avant Activation)

### Guest (Non connecté)
- ✅ Chat fonctionne
- ✅ localStorage sauvegarde la conversation
- ✅ Historique restauré
- ⚠️ Pas de sync DB (normal)

### Membre (Connecté)
- ✅ Chat fonctionne
- ⚠️ Fallback vers localStorage (car DB pas dispo)
- ⚠️ Erreurs 404/500 dans console (ignorées gracieusement)
- ⚠️ Migration échoue (normal, tables pas créées)

### Après Activation
- ✅ Tout fonctionne parfaitement
- ✅ DB save/load pour membres
- ✅ Migration localStorage → DB
- ✅ Favoris synchronisés

---

## 🚀 Checklist de Déploiement

### Étape 1 : Commit & Push (Local)
```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1

# Backend IA
cd lehiboo-ai-backend
git add .
git commit -m "Fix: Chat IA - contexte, historique, tokens"
git push origin main

# Plugin WordPress
cd ../
git add wp-content/plugins/lehiboo-ai-assistant/
git commit -m "Feature: Persistance hybride + onboarding + favoris"
git push origin main
```

### Étape 2 : Déployer Backend (Serveur)
```bash
ssh ton-user@lehiboo.dilios.me
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend
git pull origin main
docker-compose restart
```

### Étape 3 : Déployer Plugin WordPress (Serveur)
```bash
# Via Git sur le serveur
cd /var/www/vhosts/preprod.lehiboo.com/httpdocs
git pull origin main

# OU via FTP/SFTP si pas de Git sur serveur
# Upload le dossier wp-content/plugins/lehiboo-ai-assistant/
```

### Étape 4 : Réactiver Plugin ✨
```bash
# Via WP-CLI
wp plugin deactivate lehiboo-ai-assistant
wp plugin activate lehiboo-ai-assistant

# OU via Admin WordPress
# https://preprod.lehiboo.com/wp-admin/plugins.php
```

### Étape 5 : Tester 🧪
1. Ouvrir le chat en guest
2. Envoyer 3 messages → Voir modal onboarding
3. Fermer/Rouvrir → Conversation restaurée
4. Se connecter
5. Vérifier badge "Membre"
6. Cliquer un favori → Toast "Ajouté aux favoris"
7. Rafraîchir page → Favori toujours actif

---

## 🐛 Troubleshooting

### Erreur : "wp: command not found"
```bash
# Installer WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

### Erreur : Tables pas créées après réactivation
```bash
# Forcer la création manuelle
wp db query < /path/to/create_tables.sql
```

### Console montre toujours 404/500
1. Vider le cache browser (Cmd+Shift+R sur Mac)
2. Vérifier que le plugin est bien activé
3. Flush rewrite rules : `wp rewrite flush`

---

## 📞 Support

Si ça ne fonctionne toujours pas après réactivation :
1. Vérifier les logs PHP : `/var/log/php-error.log`
2. Vérifier les logs WordPress : `wp-content/debug.log`
3. Activer WP_DEBUG dans `wp-config.php`

---

## ✅ Résumé

**Avant Activation** :
- Chat fonctionne mais pas de sync DB
- Erreurs 404/500 ignorées gracieusement
- localStorage fonctionne pour tout le monde

**Après Activation** :
- ✨ Tout fonctionne à 100%
- ✨ Sync DB pour membres
- ✨ Favoris synchronisés
- ✨ Onboarding marketing actif
- ✨ Migration automatique

**Action Requise** : Désactiver/Réactiver le plugin une seule fois !
