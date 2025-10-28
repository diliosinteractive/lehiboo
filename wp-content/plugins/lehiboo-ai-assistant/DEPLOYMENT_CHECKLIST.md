# 🚀 Le Hiboo AI Chat - Deployment Checklist

## ✅ Travail Complété

### Backend (lehiboo-ai-backend)
- ✅ Token budget augmenté (1000 → 4000 tokens dynamiques)
- ✅ Validation UTF-8 ajoutée (sanitizeResponse)
- ✅ Historique glissant optimisé (20 → 12 messages)
- ✅ Extraction automatique du contexte utilisateur
- ✅ Progression automatique des stages
- ✅ Gestion des tokens avec safeguards

### Frontend (WordPress Plugin)
- ✅ Système de persistance hybride (localStorage + DB)
- ✅ Transmission complète de l'historique au backend
- ✅ Modal onboarding (charte Le Hiboo)
- ✅ Système de favoris avec sync
- ✅ Auto-save désactivé pour guests (fix 401 errors)
- ✅ sessionStorage pour dismiss modal
- ✅ Migration automatique localStorage → DB
- ✅ Graceful degradation vers localStorage

### Tables DB Ajoutées
- ✅ `wp_lehiboo_user_conversations`
- ✅ `wp_lehiboo_user_favorites`

### Endpoints REST API Ajoutés
- ✅ `POST /conversation/save`
- ✅ `GET /conversation/load`
- ✅ `GET /conversations`
- ✅ `DELETE /conversation/{id}`
- ✅ `POST /favorites/add`
- ✅ `POST /favorites/remove`
- ✅ `GET /favorites`

---

## 📋 Étapes de Déploiement

### Étape 1: Commit Local ✍️

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1

# Vérifier les modifications
git status

# Backend IA
git add lehiboo-ai-backend/

# Plugin WordPress
git add wp-content/plugins/lehiboo-ai-assistant/

# Commit
git commit -m "Feature: Chat IA V2 - Contexte, persistance hybride, onboarding

✨ Nouvelles fonctionnalités:
- Persistance hybride (localStorage + DB)
- Modal onboarding après 3ème message
- Système de favoris synchronisés
- Extraction automatique du contexte utilisateur
- Migration automatique localStorage → DB

🔧 Améliorations techniques:
- Token budget dynamique (1000→4000)
- Validation UTF-8 pour éviter gibberish
- Historique glissant optimisé (12 messages)
- Auto-save uniquement pour utilisateurs connectés
- Graceful degradation vers localStorage

🐛 Corrections:
- Fix 401 errors (auto-save pour guests désactivé)
- Fix modal qui réapparaît (sessionStorage)
- Fix réponses gibberish (token budget + UTF-8)
- Fix questions répétitives (contexte + historique)

🎨 Design:
- Modal conforme à la charte Le Hiboo
- Gradient orange #ff601f
- Animations et transitions fluides

📁 Fichiers modifiés:
- lehiboo-ai-backend/src/services/ai-service.js
- lehiboo-ai-backend/src/controllers/chat-controller.js
- wp-content/plugins/lehiboo-ai-assistant/lehiboo-ai-assistant.php
- wp-content/plugins/lehiboo-ai-assistant/assets/js/chat-interface.js
- wp-content/plugins/lehiboo-ai-assistant/assets/js/chat-persistence.js (NEW)
- wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-onboarding.css (NEW)

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# Push vers remote
git push origin main
```

---

### Étape 2: Déployer Backend IA 🐳

**Serveur**: `lehiboo.dilios.me` (Docker)

```bash
# Se connecter au serveur
ssh ton-user@lehiboo.dilios.me

# Aller dans le dossier backend
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# Pull les changements
git pull origin main

# Redémarrer le container Docker
docker-compose restart

# Vérifier les logs
docker-compose logs -f --tail=50
```

**Vérification**:
```bash
# Test health check
curl https://lehiboo.dilios.me/api/health

# Devrait retourner:
# {"status":"ok","timestamp":"...","version":"1.0.0"}
```

---

### Étape 3: Déployer Plugin WordPress 📦

**Serveur**: `preprod.lehiboo.com`

#### Option A: Via Git (Recommandé)

```bash
# Se connecter au serveur
ssh ton-user@preprod.lehiboo.com

# Aller dans le dossier WordPress
cd /var/www/vhosts/preprod.lehiboo.com/httpdocs

# Pull les changements
git pull origin main

# Vérifier que les fichiers sont bien là
ls -la wp-content/plugins/lehiboo-ai-assistant/assets/js/chat-persistence.js
ls -la wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-onboarding.css
```

#### Option B: Via SFTP (Alternative)

1. Ouvrir FileZilla / Transmit / Cyberduck
2. Se connecter à `preprod.lehiboo.com`
3. Aller dans `/var/www/vhosts/preprod.lehiboo.com/httpdocs/wp-content/plugins/`
4. Uploader le dossier `lehiboo-ai-assistant/` (remplacer)

---

### Étape 4: Activer le Plugin ⚡️

**CRITIQUE**: Cette étape crée les tables DB et enregistre les routes REST API.

#### Option A: Via WP-CLI (Recommandé)

```bash
# Sur le serveur
cd /var/www/vhosts/preprod.lehiboo.com/httpdocs

# Désactiver
wp plugin deactivate lehiboo-ai-assistant

# Réactiver (crée les tables + enregistre les routes)
wp plugin activate lehiboo-ai-assistant

# Vérifier les tables créées
wp db query "SHOW TABLES LIKE 'wp_lehiboo_user_%';"

# Devrait afficher:
# wp_lehiboo_user_conversations
# wp_lehiboo_user_favorites
```

#### Option B: Via Admin WordPress

1. Aller sur `https://preprod.lehiboo.com/wp-admin/plugins.php`
2. Trouver "Le Hiboo AI Assistant"
3. Cliquer sur "Désactiver"
4. Cliquer sur "Activer"
5. Vérifier qu'aucune erreur n'apparaît

---

### Étape 5: Vérifications Post-Déploiement ✅

#### 5.1 Vérifier les Tables DB

```bash
# Compter les tables
wp db query "SELECT COUNT(*) as count FROM wp_lehiboo_user_conversations;"
wp db query "SELECT COUNT(*) as count FROM wp_lehiboo_user_favorites;"

# Voir la structure
wp db query "DESCRIBE wp_lehiboo_user_conversations;"
wp db query "DESCRIBE wp_lehiboo_user_favorites;"
```

#### 5.2 Tester les Endpoints REST

```bash
# Health check (pas besoin d'auth)
curl https://preprod.lehiboo.com/wp-json/lehiboo/v1/health

# Conversations (nécessite cookie auth)
# Remplacer xxx et yyy par les valeurs du cookie WordPress
curl -X GET https://preprod.lehiboo.com/wp-json/lehiboo/v1/conversations \
  -H "Cookie: wordpress_logged_in_xxx=yyy" \
  -H "X-WP-Nonce: $(wp eval 'echo wp_create_nonce(\"wp_rest\");')"
```

#### 5.3 Vérifier la Console Browser

1. Ouvrir `https://preprod.lehiboo.com`
2. Ouvrir DevTools (F12)
3. Aller dans l'onglet Console
4. Ouvrir le chat

**Ce que tu devrais voir**:
```
[Persistence] Initializing...
[Persistence] Auto-save disabled for guests (using localStorage only)
[Onboarding] Ready to show after 3rd message
✅ Plus d'erreurs 404/500
```

**Ce que tu NE devrais PAS voir**:
```
❌ 401 Unauthorized on /conversation/save
❌ 500 Internal Server Error on /chat
```

---

## 🧪 Tests Fonctionnels

### Test 1: Guest User Flow

1. **Ouvrir le chat en mode incognito**
   - Aller sur `https://preprod.lehiboo.com`
   - Ouvrir DevTools → Console
   - Cliquer sur le bouton chat

2. **Envoyer 3 messages**
   ```
   Message 1: "Salut"
   Message 2: "Je cherche une activité en couple"
   Message 3: "Pour ce weekend"
   ```

3. **Vérifier modal onboarding**
   - ✅ Modal apparaît après le 3ème message
   - ✅ Design conforme à la charte (gradient orange)
   - ✅ 4 bénéfices affichés avec icônes
   - ✅ Boutons "S'inscrire" et "Se connecter"

4. **Fermer le modal**
   - Cliquer sur le bouton "Fermer" (X)
   - Rafraîchir la page (F5)
   - ✅ Modal ne réapparaît PAS

5. **Vérifier localStorage**
   - DevTools → Application → Local Storage
   - ✅ `lehiboo_conversation` existe
   - ✅ Contient les 3 messages

6. **Vérifier restauration**
   - Fermer le chat
   - Rouvrir le chat
   - ✅ Les 3 messages sont restaurés

### Test 2: Membre Connecté Flow

1. **Se connecter**
   - Aller sur `https://preprod.lehiboo.com/wp-login.php`
   - Se connecter avec un compte membre

2. **Ouvrir le chat**
   - Cliquer sur le bouton chat
   - ✅ Badge "Membre connecté" visible dans le header

3. **Vérifier migration localStorage → DB**
   - Console devrait afficher:
   ```
   [Persistence] Migrating from localStorage to DB...
   [Persistence] Migration successful
   ```

4. **Envoyer des messages**
   - Envoyer 2-3 messages
   - Attendre 30 secondes
   - Console devrait afficher:
   ```
   [Persistence] Auto-save started (every 30s to DB)
   [Persistence] Saved to DB successfully
   ```

5. **Vérifier sync DB**
   ```bash
   # Sur le serveur
   wp db query "SELECT * FROM wp_lehiboo_user_conversations ORDER BY id DESC LIMIT 1 \G"

   # Devrait afficher la conversation avec les messages en JSON
   ```

6. **Tester restauration DB**
   - Fermer le navigateur complètement
   - Rouvrir et se reconnecter
   - Ouvrir le chat
   - ✅ Conversation restaurée depuis la DB

### Test 3: Favoris

1. **Chercher un événement**
   - Envoyer: "Propose-moi des activités en couple ce weekend"
   - ✅ L'IA répond avec des recommandations

2. **Ajouter aux favoris (si événement cliquable)**
   - Cliquer sur le bouton cœur
   - ✅ Toast "Ajouté aux favoris" apparaît
   - ✅ Cœur devient rouge avec animation

3. **Vérifier sync DB**
   ```bash
   wp db query "SELECT * FROM wp_lehiboo_user_favorites ORDER BY id DESC LIMIT 1 \G"
   ```

4. **Vérifier persistance**
   - Rafraîchir la page
   - Rouvrir le chat
   - ✅ Cœur toujours rouge

### Test 4: Contexte et Intelligence

1. **Conversation naturelle**
   ```
   User: "Salut"
   AI: "Bonjour ! Je suis là pour t'aider..."

   User: "Je cherche une activité en couple"
   AI: [Détecte groupType: "couple"]

   User: "On a 30 ans"
   AI: [Détecte age: "30"]

   User: "Budget 100€ max"
   AI: [Détecte budget: "100"]

   User: "Ce weekend"
   AI: [Détecte datePreference: "weekend"]
   ```

2. **Vérifier extraction contexte**
   - Console → Onglet Network → Filter "chat"
   - Cliquer sur la requête POST /chat
   - Onglet "Payload" → Voir le JSON
   - ✅ `userContext` contient les infos extraites

3. **Vérifier pas de répétitions**
   - ✅ L'IA ne redemande PAS l'âge
   - ✅ L'IA ne redemande PAS le type de groupe
   - ✅ L'IA progresse vers les recommandations

---

## 🐛 Troubleshooting

### Erreur: "wp: command not found"

```bash
# Installer WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
wp --info
```

### Erreur: Tables pas créées après réactivation

```bash
# Forcer la création manuelle
wp db query "CREATE TABLE IF NOT EXISTS wp_lehiboo_user_conversations (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"

wp db query "CREATE TABLE IF NOT EXISTS wp_lehiboo_user_favorites (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    event_id bigint(20) NOT NULL,
    added_from_conversation_id varchar(100) DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_event (user_id, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
```

### Console montre toujours 404/500

```bash
# 1. Vider le cache browser
# Cmd+Shift+R (Mac) ou Ctrl+Shift+R (Windows)

# 2. Flush rewrite rules
wp rewrite flush

# 3. Vérifier que le plugin est activé
wp plugin list | grep lehiboo

# 4. Vérifier les logs PHP
tail -f /var/log/php-error.log

# 5. Activer WP_DEBUG
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw
tail -f wp-content/debug.log
```

### Modal ne s'affiche pas

1. **Vérifier console**
   - Erreurs JavaScript ?
   - Fichier chat-onboarding.css chargé ?

2. **Vérifier localStorage**
   ```javascript
   // Dans la console
   localStorage.removeItem('lehiboo_onboarding_dismissed')
   sessionStorage.removeItem('lehiboo_onboarding_dismissed_session')
   location.reload()
   ```

3. **Vérifier config**
   ```javascript
   // Dans la console
   console.log(lehibooChatConfig.onboarding)
   // Devrait afficher: {enabled: true, triggerAfterMessages: 3}
   ```

### Erreur "Anthropic API key not configured"

```bash
# Sur le serveur backend
cd /var/www/vhosts/lehiboo.dilios.me/lehiboo-ai-backend

# Vérifier le .env
cat .env | grep ANTHROPIC_API_KEY

# Si vide, ajouter la clé
echo "ANTHROPIC_API_KEY=sk-ant-xxx" >> .env

# Redémarrer
docker-compose restart
```

---

## 📊 Monitoring Post-Déploiement

### Logs à Surveiller

```bash
# Backend IA (Docker)
docker-compose logs -f lehiboo-ai-backend

# WordPress PHP Errors
tail -f /var/log/php-error.log

# WordPress Debug Log
tail -f wp-content/debug.log

# Apache/Nginx Access
tail -f /var/log/apache2/preprod.lehiboo.com-access.log
```

### Métriques à Vérifier

```bash
# Nombre de conversations créées
wp db query "SELECT COUNT(*) as total FROM wp_lehiboo_user_conversations;"

# Nombre de favoris
wp db query "SELECT COUNT(*) as total FROM wp_lehiboo_user_favorites;"

# Dernières conversations
wp db query "SELECT user_id, conversation_id, created_at FROM wp_lehiboo_user_conversations ORDER BY created_at DESC LIMIT 10;"

# Top utilisateurs
wp db query "SELECT user_id, COUNT(*) as conversations FROM wp_lehiboo_user_conversations GROUP BY user_id ORDER BY conversations DESC LIMIT 10;"
```

---

## ✅ Checklist Finale

- [ ] Backend IA déployé et redémarré
- [ ] Plugin WordPress déployé
- [ ] Plugin désactivé/réactivé
- [ ] Tables DB créées et vérifiées
- [ ] Endpoints REST API fonctionnels
- [ ] Test guest: modal onboarding après 3 messages
- [ ] Test guest: localStorage sauvegarde conversation
- [ ] Test guest: modal ne réapparaît pas après fermeture
- [ ] Test membre: badge "Membre connecté" visible
- [ ] Test membre: migration localStorage → DB
- [ ] Test membre: auto-save toutes les 30s
- [ ] Test membre: restauration depuis DB
- [ ] Test contexte: extraction automatique (âge, type, budget)
- [ ] Test historique: plus de questions répétitives
- [ ] Test réponses: plus de gibberish
- [ ] Console: pas d'erreurs 401/500
- [ ] Design: modal conforme à la charte Le Hiboo

---

## 🎉 Résultat Attendu

**Avant**:
- ❌ Réponses gibberish après 2-3 messages
- ❌ Questions répétitives (âge, préférences)
- ❌ Pas de sauvegarde de conversation
- ❌ Pas d'incitation à s'inscrire
- ❌ Erreurs 401/500 dans console

**Après**:
- ✅ Réponses fluides et cohérentes
- ✅ Contexte extrait automatiquement
- ✅ Conversations sauvegardées (localStorage + DB)
- ✅ Modal onboarding après 3 messages
- ✅ Migration automatique pour membres
- ✅ Système de favoris synchronisé
- ✅ Aucune erreur dans console
- ✅ Design conforme à la charte

---

## 📞 Support

Si problème persistant après déploiement:

1. **Vérifier les logs** (voir section Monitoring)
2. **Tester en local** (vérifier que ça marche en dev)
3. **Comparer les versions** (git diff origin/main)
4. **Rollback si nécessaire** (git revert)

---

**Dernière mise à jour**: 2025-10-28
**Version**: 2.0.0
**Status**: ✅ Prêt pour déploiement
