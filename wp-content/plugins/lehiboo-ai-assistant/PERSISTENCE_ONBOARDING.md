# Le Hiboo AI Assistant - Système de Persistance & Onboarding

## 📋 Vue d'ensemble

Système complet de **persistance hybride** (localStorage + Database) avec **onboarding marketing premium** pour maximiser l'engagement et la conversion des utilisateurs.

---

## 🎯 Objectifs

1. **Rétention** : Ne jamais perdre les conversations utilisateur
2. **Conversion** : Inciter à la création de compte via un onboarding stratégique
3. **Expérience fluide** : Transition transparente guest → utilisateur connecté
4. **Engagement** : Système de favoris synchronisé

---

## 🏗️ Architecture

### 1. Persistance Hybride

#### **Pour les Guests (Non connectés)**
- **Stockage** : `localStorage`
- **Durée** : 7 jours
- **Données sauvegardées** :
  - Messages de conversation
  - Contexte utilisateur (âge, préférences, etc.)
  - Stage actuel
  - Favoris temporaires

#### **Pour les Membres (Connectés)**
- **Stockage** : Database WordPress (`wp_lehiboo_user_conversations`)
- **Durée** : Illimitée
- **Données sauvegardées** :
  - Toutes les conversations (jusqu'à 20 dernières)
  - Contexte enrichi
  - Favoris synchronisés multi-appareils

#### **Migration Automatique**
Lors de la connexion/inscription :
1. Détection de données localStorage
2. Migration automatique vers DB
3. Nettoyage localStorage
4. Toast de confirmation

---

### 2. Onboarding Marketing

#### **Trigger Intelligent**
- Affiché après le **3ème message** (moment optimal d'engagement)
- Uniquement pour les guests
- Ne se réaffiche pas pendant 7 jours si dismissed

#### **Proposition de Valeur**
4 bénéfices clés présentés avec icônes :
1. 💬 **Historique sauvegardé** - Conversations accessibles partout
2. ❤️ **Favoris synchronisés** - Activités sauvegardées multi-appareils
3. ⚡ **Réservation express** - 1 clic avec infos pré-remplies
4. 🎁 **Offres exclusives** - Réductions membres

#### **Actions Principales**
- **CTA Primaire** : "Créer mon compte gratuit" (gradient rouge attractif)
- **CTA Secondaire** : "J'ai déjà un compte"
- **Dismiss** : "Continuer sans compte" (discret)

#### **Optimisations UX**
- Animation d'entrée fluide
- Backdrop avec blur pour mise en contexte
- Responsive mobile-first
- Accessibilité ARIA complète

---

### 3. Système de Favoris

#### **Fonctionnement**
- Bouton ❤️ sur chaque event card
- Animation "heartBeat" au clic
- Toast de feedback immédiat

#### **Restrictions**
- **Guest** : Clic → Modal d'onboarding (incitation conversion)
- **Membre** : Fonctionnement complet + sync

#### **Base de données**
Table `wp_lehiboo_user_favorites` :
- `user_id` : ID WordPress
- `event_id` : ID de l'événement
- `added_from_conversation_id` : Tracking origine
- `notes` : Notes personnelles (futur)
- `created_at` : Date d'ajout

---

## 🗄️ Schéma de Base de Données

### `wp_lehiboo_user_conversations`
```sql
CREATE TABLE wp_lehiboo_user_conversations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    conversation_id varchar(100) NOT NULL,
    messages longtext NOT NULL,           -- JSON des messages
    user_context text DEFAULT NULL,       -- JSON du contexte
    current_stage varchar(50) DEFAULT 'greeting',
    last_message_at datetime DEFAULT CURRENT_TIMESTAMP,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY conversation_id (conversation_id)
);
```

### `wp_lehiboo_user_favorites`
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

---

## 📡 API Endpoints

### **Conversations**

#### `POST /wp-json/lehiboo/v1/conversation/save`
Sauvegarde la conversation actuelle
```json
{
  "conversationId": "conv_123",
  "messages": [...],
  "userContext": {...},
  "currentStage": "preferences"
}
```

#### `GET /wp-json/lehiboo/v1/conversation/load?conversationId=conv_123`
Charge une conversation spécifique

#### `GET /wp-json/lehiboo/v1/conversations`
Liste toutes les conversations de l'utilisateur (20 dernières)

#### `DELETE /wp-json/lehiboo/v1/conversation/{id}`
Supprime une conversation

### **Favoris**

#### `POST /wp-json/lehiboo/v1/favorites/add`
```json
{
  "eventId": 456,
  "conversationId": "conv_123",
  "notes": "À faire ce weekend"
}
```

#### `POST /wp-json/lehiboo/v1/favorites/remove`
```json
{
  "eventId": 456
}
```

#### `GET /wp-json/lehiboo/v1/favorites`
Liste tous les favoris de l'utilisateur

---

## 🎨 Composants Frontend

### 1. `ChatPersistenceManager` (chat-persistence.js)
Gère la logique de sauvegarde hybride
- `saveConversation()` - Sauvegarde auto/manuelle
- `loadConversation()` - Chargement au démarrage
- `migrateLocalStorageToDB()` - Migration lors connexion
- `startAutoSave()` - Auto-save toutes les 30s

### 2. `OnboardingManager` (chat-persistence.js)
Gère l'affichage du modal d'onboarding
- `shouldShow()` - Logique de déclenchement
- `showAfterMessages()` - Trigger au 3ème message
- `show()` - Affichage du modal
- `createModal()` - Construction HTML

### 3. `FavoritesManager` (chat-persistence.js)
Gère les favoris utilisateur
- `addFavorite()` - Ajout
- `removeFavorite()` - Suppression
- `toggleFavorite()` - Toggle
- `isFavorite()` - Vérification état
- `loadFavorites()` - Chargement initial

---

## 🎯 Flow Utilisateur

### Scenario 1 : Guest → Inscription

1. **Visite 1** : Guest ouvre le chat
   - Greeting affiché
   - Conversation démarre
   - Sauvegarde auto dans localStorage

2. **Message 3** : Trigger onboarding
   - Modal apparaît avec proposition de valeur
   - Guest continue (dismiss)
   - Conversation continue normalement

3. **Visite 2** : Guest revient
   - Conversation restaurée depuis localStorage
   - Continue là où il s'était arrêté

4. **Clic Favori** : Guest essaie d'ajouter un favori
   - Modal onboarding s'affiche (restriction)
   - Incitation forte à créer compte

5. **Inscription** : Guest clique "Créer mon compte"
   - Redirection vers `/wp-login.php?action=register`
   - Après inscription : retour sur page
   - **Migration automatique** localStorage → DB
   - Toast : "Bienvenue ! Votre conversation a été sauvegardée"

6. **Post-inscription** : Maintenant membre
   - Badge "Membre" dans header
   - Salutation personnalisée
   - Favoris fonctionnels
   - Sync multi-appareils

### Scenario 2 : Membre Connecté

1. **Ouverture Chat** : Auto-chargement depuis DB
2. **Ajout Favoris** : Sync immédiate
3. **Changement Appareil** : Tout est synchronisé
4. **Historique** : Accès aux 20 dernières conversations

---

## 🚀 Optimisations Marketing

### Taux de Conversion Attendus
- **Modal Onboarding** : 15-25% de conversion
- **Clic Favori** : 30-40% de conversion (forte intention)
- **Retour Visiteur** : 50% grâce à la persistance

### Points de Friction Réduits
1. Pas de perte de données
2. Pas de ré-saisie d'informations
3. Transition transparente
4. Bénéfices clairs et immédiats

### Tracking Analytics
Événements trackés :
- `chat_opened`
- `onboarding_shown`
- `onboarding_register_click`
- `onboarding_login_click`
- `onboarding_dismissed`
- `favorite_added`
- `favorite_removed`
- `conversation_loaded`
- `conversation_migrated`

---

## 🛠️ Configuration

### WordPress (lehiboo-ai-assistant.php)

```php
wp_localize_script('lehiboo-ai-chat', 'lehibooChatConfig', array(
    'isLoggedIn' => is_user_logged_in(),
    'userId' => get_current_user_id(),
    'userDisplayName' => wp_get_current_user()->display_name,
    'loginUrl' => wp_login_url(),
    'registerUrl' => wp_registration_url(),
    'persistence' => array(
        'enabled' => true,
        'autoSaveInterval' => 30000, // 30 secondes
    ),
    'onboarding' => array(
        'enabled' => !is_user_logged_in(),
        'triggerAfterMessages' => 3,
    ),
));
```

---

## 📦 Fichiers du Système

```
wp-content/plugins/lehiboo-ai-assistant/
├── assets/
│   ├── js/
│   │   ├── chat-interface.js        # Chat principal (modifié)
│   │   └── chat-persistence.js      # Nouveau - Persistance & Onboarding
│   └── css/
│       └── chat-onboarding.css      # Nouveau - Styles modal & favoris
├── includes/
│   └── (fichiers PHP existants)
├── lehiboo-ai-assistant.php         # Modifié - Ajout endpoints + config
└── PERSISTENCE_ONBOARDING.md        # Ce document
```

---

## 🧪 Tests Recommandés

### Test 1 : Flow Guest
1. Ouvrir en navigation privée
2. Envoyer 3 messages
3. Vérifier apparition modal onboarding
4. Dismiss modal
5. Fermer et rouvrir chat
6. Vérifier que conversation est restaurée

### Test 2 : Migration
1. Commencer en guest (localStorage)
2. S'inscrire
3. Vérifier migration automatique
4. Vérifier présence données en DB

### Test 3 : Favoris
1. En guest : cliquer favori → modal onboarding
2. Se connecter
3. Cliquer favori → ajout avec toast
4. Rafraîchir page → état préservé

### Test 4 : Multi-appareils
1. Se connecter sur Desktop
2. Ajouter conversation + favoris
3. Se connecter sur Mobile
4. Vérifier sync complète

---

## 🎓 Bonnes Pratiques

### Pour les Développeurs

1. **Toujours vérifier `isLoggedIn`** avant DB operations
2. **Fallback localStorage** en cas d'erreur DB
3. **Éviter les saves trop fréquentes** (utiliser auto-save 30s)
4. **Valider données** avant sauvegarde DB
5. **Nettoyer localStorage** après migration réussie

### Pour le Marketing

1. **A/B test** le texte du modal
2. **Tracker** tous les événements
3. **Optimiser timing** de l'onboarding (actuellement 3ème message)
4. **Personnaliser offres** selon contexte utilisateur
5. **Email de relance** si conversation abandonnée

---

## 🔒 Sécurité & RGPD

### Données Guests (localStorage)
- ✅ Stockage local uniquement
- ✅ Pas d'identification personnelle
- ✅ Expiration automatique (7 jours)
- ✅ Effaçable par utilisateur

### Données Membres (Database)
- ✅ Associées au compte WordPress
- ✅ Suppression via compte utilisateur
- ✅ Export possible (RGPD)
- ✅ Chiffrement recommandé (messages sensibles)

### Permissions API
- ✅ Endpoints protégés par `is_user_logged_in()`
- ✅ Vérification `user_id` dans requêtes DB
- ✅ Nonces WordPress pour sécurité
- ✅ Rate limiting existant

---

## 🚦 Déploiement

### Étapes de Déploiement

1. **Désactiver/Réactiver le plugin** pour créer les tables DB
   ```bash
   wp plugin deactivate lehiboo-ai-assistant
   wp plugin activate lehiboo-ai-assistant
   ```

2. **Vérifier les tables** :
   ```sql
   SHOW TABLES LIKE 'wp_lehiboo_user_%';
   ```

3. **Tester en staging** avant production

4. **Monitorer les logs** après déploiement

5. **Configurer analytics** pour tracking

---

## 📈 Métriques à Suivre

### Conversion
- Taux d'affichage modal onboarding
- Taux de clic "Créer compte" depuis modal
- Taux de clic "Créer compte" depuis favori
- Taux de completion inscription

### Engagement
- Nombre de conversations par utilisateur
- Durée moyenne des conversations
- Taux de retour utilisateurs
- Nombre de favoris par utilisateur

### Technique
- Taux de succès migration localStorage → DB
- Temps de chargement conversations
- Erreurs API (save/load)

---

## 🎉 Résultat Final

Un système de chat IA **niveau production** avec :

✅ Persistance robuste (hybride localStorage + DB)
✅ Onboarding marketing optimisé pour conversion
✅ Système de favoris engageant
✅ Migration transparente guest → membre
✅ Sync multi-appareils
✅ UX premium avec animations fluides
✅ Analytics complet
✅ Sécurité & RGPD compliant

**Le tout sans aucune perte de données utilisateur !** 🚀

---

## 📞 Support

Pour toute question :
- Documentation : Ce fichier
- Code source : `assets/js/chat-persistence.js`
- API : `lehiboo-ai-assistant.php` lignes 330-663
