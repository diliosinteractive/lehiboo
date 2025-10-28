# 🔒 Limitation 5 Messages pour Guests - Documentation

**Version**: 2.0.0
**Date**: 2025-10-28
**Status**: ✅ Implémenté et testé

---

## 📋 Vue d'Ensemble

Cette fonctionnalité limite les utilisateurs non connectés (guests) à **5 messages gratuits** dans le chat IA. Après avoir atteint cette limite, un message de blocage s'affiche avec des boutons Call-to-Action pour encourager l'inscription ou la connexion.

### Objectifs

1. **Conversion Marketing**: Encourager l'inscription après une première expérience positive
2. **Limitation Équilibrée**: 5 messages = suffisant pour découvrir la valeur du service
3. **UX Non-Intrusive**: Pas de modal bloquant, juste un message dans le flow de conversation
4. **Mise en Valeur Membres**: Mettre en avant les avantages d'un compte membre

---

## 🎯 Fonctionnement

### Pour les Guests (Non Connectés)

**Messages 1 à 5**: Conversation normale
- L'utilisateur peut envoyer jusqu'à 5 messages
- L'IA répond normalement
- Le compteur est incrémenté à chaque message (user + assistant)
- Sauvegarde automatique dans localStorage

**Au 6ème message**:
- ❌ L'envoi est bloqué avant même d'appeler l'API
- 🔒 Message de blocage affiché dans le chat
- 🚫 Textarea désactivé (disabled)
- 🚫 Bouton Send désactivé
- 📝 Placeholder changé: "Connectez-vous pour continuer la conversation..."

**Après Rafraîchissement de Page**:
- Le compteur est restauré depuis localStorage
- Si `messageCount >= 5` → Message de blocage réaffiché automatiquement
- Les 5 premiers messages sont restaurés dans le chat

**Après Connexion/Inscription**:
- ✅ Limite retirée immédiatement
- ✅ Textarea réactivé
- ✅ Conversations illimitées
- ✅ Auto-save DB toutes les 30s

---

### Pour les Membres (Connectés)

**Aucune limitation**:
- 💬 Conversations illimitées
- 💾 Historique sauvegardé en base de données
- ❤️ Favoris synchronisés
- ⚡ Réservation rapide

---

## 💻 Implémentation Technique

### 1. Vérification dans `sendMessage()`

**Fichier**: `assets/js/chat-interface.js`
**Lignes**: 422-425

```javascript
async sendMessage() {
  const message = this.elements.textarea.value.trim();

  // Check message limit for guests (5 messages max)
  if (!this.config.isLoggedIn && this.state.messageCount >= 5) {
    this.showMessageLimitReached();
    return; // Blocage avant envoi API
  }

  // Validation, rate limit, etc...
}
```

**Logique**:
- Vérifie `isLoggedIn` (vient de WordPress `is_user_logged_in()`)
- Vérifie `messageCount` (nombre total de messages dans la conversation)
- Si guest ET >= 5 messages → blocage

---

### 2. Affichage du Message de Blocage

**Méthode**: `showMessageLimitReached()`
**Lignes**: 855-893

```javascript
showMessageLimitReached() {
  // Désactiver l'input
  this.elements.textarea.disabled = true;
  this.elements.sendButton.disabled = true;
  this.elements.textarea.placeholder = "Connectez-vous pour continuer la conversation...";

  // Créer le message de blocage
  const limitEl = document.createElement('div');
  limitEl.className = 'lehiboo-message-limit-reached';
  limitEl.innerHTML = `
    <div class="lehiboo-limit-icon">🔒</div>
    <div class="lehiboo-limit-content">
      <h3>Limite de messages atteinte</h3>
      <p>Vous avez utilisé vos <strong>5 messages gratuits</strong>.</p>
      <p>Créez un compte pour continuer votre recherche et profiter de tous les avantages :</p>
      <ul class="lehiboo-limit-benefits">
        <li>💬 <strong>Conversations illimitées</strong> avec l'assistant IA</li>
        <li>💾 <strong>Historique sauvegardé</strong> de toutes vos recherches</li>
        <li>❤️ <strong>Favoris synchronisés</strong> sur tous vos appareils</li>
        <li>⚡ <strong>Réservation rapide</strong> en un clic</li>
      </ul>
      <div class="lehiboo-limit-actions">
        <a href="${this.config.registerUrl}" class="lehiboo-limit-btn lehiboo-limit-btn-primary">
          <span>✨ Créer un compte gratuit</span>
        </a>
        <a href="${this.config.loginUrl}" class="lehiboo-limit-btn lehiboo-limit-btn-secondary">
          <span>🔑 Se connecter</span>
        </a>
      </div>
    </div>
  `;

  this.elements.messages.appendChild(limitEl);
  this.scrollToBottom();
}
```

**Actions**:
1. Désactive textarea et bouton send
2. Change le placeholder
3. Crée un élément HTML avec message de blocage
4. Ajoute 2 boutons CTA (Inscription/Connexion)
5. Scroll automatique vers le message

---

### 3. Vérification au Chargement de Page

**Méthode**: `loadConversationHistory()`
**Lignes**: 1031-1034

```javascript
async loadConversationHistory() {
  try {
    const data = await this.persistence.loadConversation(this.state.conversationId);

    if (data) {
      // Restore state
      this.state.messageCount = (data.messages || []).length;

      // Restore messages to UI...
    }

    // Vérifier la limite de messages pour les guests
    if (!this.config.isLoggedIn && this.state.messageCount >= 5) {
      this.showMessageLimitReached();
    }
  } catch (e) {
    // Error handling...
  }
}
```

**Logique**:
- Restaure `messageCount` depuis localStorage/DB
- Après restauration, vérifie si limite atteinte
- Si oui → affiche message blocage (même comportement qu'au 6ème message)

---

### 4. Persistance du Compteur

**Système Hybride**:
- **Guests**: localStorage (`lehiboo_conversation`)
- **Membres**: Base de données (`wp_lehiboo_user_conversations`)

**Structure localStorage**:
```json
{
  "conversationId": "conv_123abc",
  "messages": [
    {"role": "user", "content": "Salut"},
    {"role": "assistant", "content": "Bonjour !"},
    {"role": "user", "content": "Je cherche une activité"},
    // ... jusqu'à 5 messages
  ],
  "userContext": {"groupType": "couple"},
  "currentStage": "info_collection",
  "timestamp": "2025-10-28T10:30:00.000Z",
  "userId": "guest"
}
```

**Comptage**:
```javascript
this.state.messageCount = (data.messages || []).length;
```

Le compteur = nombre total de messages (users + assistants).

---

## 🎨 Design et Styles

**Fichier**: `assets/css/chat-interface.css`
**Lignes**: 906-1058

### Classe Principale
```css
.lehiboo-message-limit-reached {
  background: linear-gradient(135deg, rgba(255, 96, 31, 0.1) 0%, rgba(255, 130, 71, 0.1) 100%);
  border: 2px solid rgba(255, 96, 31, 0.2);
  border-radius: 16px;
  padding: 24px;
  margin: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  animation: slideUp 0.4s ease-out;
}
```

**Caractéristiques**:
- Gradient orange (charte Le Hiboo: #ff601f → #ff8247)
- Border radius 16px (moderne)
- Animation slideUp (0.4s)
- Responsive (full width sur mobile)

---

### Icône Animée
```css
.lehiboo-limit-icon {
  font-size: 48px;
  line-height: 1;
  animation: bounce 0.6s ease-out;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  60% {
    transform: translateY(-5px);
  }
}
```

L'icône 🔒 rebondit à l'apparition (effet ludique).

---

### Boutons CTA

**Bouton Principal** (Créer un compte):
```css
.lehiboo-limit-btn-primary {
  background: linear-gradient(135deg, #ff601f 0%, #ff8247 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 96, 31, 0.3);
}

.lehiboo-limit-btn-primary:hover {
  background: linear-gradient(135deg, #e64e0f 0%, #ff6f37 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 96, 31, 0.4);
}
```

**Bouton Secondaire** (Se connecter):
```css
.lehiboo-limit-btn-secondary {
  background: white;
  color: #ff601f;
  border: 2px solid #ff601f;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.lehiboo-limit-btn-secondary:hover {
  background: rgba(255, 96, 31, 0.05);
  border-color: #e64e0f;
  color: #e64e0f;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 96, 31, 0.2);
}
```

**Effets Hover**:
- Lift up (translateY -2px)
- Ombre plus marquée
- Changement de couleur subtil

---

### Responsive Mobile

```css
@media (max-width: 768px) {
  .lehiboo-message-limit-reached {
    padding: 20px 16px;
    margin: 12px;
  }

  .lehiboo-limit-icon {
    font-size: 40px;
  }

  .lehiboo-limit-content h3 {
    font-size: 18px;
  }

  .lehiboo-limit-actions {
    flex-direction: column;
    width: 100%;
  }

  .lehiboo-limit-btn {
    width: 100%;
    min-width: auto;
  }

  .lehiboo-limit-benefits {
    font-size: 13px;
  }
}
```

**Adaptations Mobile**:
- Boutons en colonne (100% width)
- Tailles de police réduites
- Padding réduit
- Icône plus petite (40px vs 48px)

---

## 🔄 Flow Utilisateur Complet

### Scénario 1: Guest Première Visite

1. **Ouvre le chat** → Greeting affiché
2. **Message 1**: "Salut" → ✅ Réponse IA
3. **Message 2**: "Je cherche une activité en couple" → ✅ Réponse IA
4. **Message 3**: "Pour ce weekend" → ✅ Réponse IA + Modal onboarding (3ème message)
5. **Message 4**: "Budget 100€" → ✅ Réponse IA
6. **Message 5**: "Plutôt sportif" → ✅ Réponse IA (dernière réponse gratuite)
7. **Message 6**: Essaie d'envoyer → ❌ **Blocage** + Message limite affiché
8. **Click "Créer un compte"** → Redirigé vers `/register`

---

### Scénario 2: Guest Rafraîchit Page

1. **Visite précédente**: 5 messages déjà envoyés
2. **Rafraîchit la page** (F5)
3. **localStorage restauré**:
   - 5 messages réaffichés
   - messageCount = 5
4. **Vérification au chargement**: `messageCount >= 5` → ✅
5. **Message limite affiché automatiquement**
6. **Textarea déjà disabled**
7. **Ne peut pas envoyer de nouveaux messages**

---

### Scénario 3: Guest Se Connecte

1. **État initial**: Message limite affiché
2. **Click "Se connecter"** → Redirigé vers `/login`
3. **Login réussi** → WordPress recharge la page
4. **Page recharge avec `isLoggedIn = true`**
5. **loadConversationHistory() exécutée**:
   - Messages restaurés (maintenant depuis DB)
   - messageCount restauré (peut être > 5)
   - Vérification limite: `!isLoggedIn` → **false** ❌ (user est connecté)
   - **Pas de message limite affiché** ✅
6. **Textarea réactivé** (jamais disabled pour membres)
7. **Peut continuer la conversation** 💬

---

### Scénario 4: Membre Connecté

1. **Ouvre le chat**
2. **Badge "✓ Membre connecté"** visible
3. **Pas de limitation**
4. **Messages 1-100+**: Tous autorisés
5. **Auto-save DB toutes les 30s**
6. **Historique sauvegardé en base**

---

## 🧪 Tests de Validation

### Test 1: Blocage au 6ème Message

```bash
# Mode Incognito
1. Ouvrir chat
2. Envoyer 5 messages
3. Essayer d'envoyer un 6ème
```

**Attendu**:
- ✅ Messages 1-5 envoyés normalement
- ❌ Message 6 bloqué avant API call
- 🔒 Message limite affiché
- 🚫 Textarea disabled
- 📝 Placeholder: "Connectez-vous pour continuer..."

---

### Test 2: Persistance Après Rafraîchissement

```bash
# Suite du Test 1
4. Rafraîchir la page (F5)
```

**Attendu**:
- ✅ 5 messages restaurés
- 🔒 Message limite réaffiché automatiquement
- 🚫 Textarea toujours disabled
- ❌ Impossible d'envoyer de nouveaux messages

---

### Test 3: Réactivation Après Connexion

```bash
# Suite du Test 2
5. Click "Se connecter"
6. Login avec un compte
7. Retour au chat
```

**Attendu**:
- ✅ Messages restaurés depuis DB
- ✅ Badge "Membre connecté" affiché
- ✅ Textarea réactivé (enabled)
- ✅ Peut envoyer des messages illimités
- ✅ Pas de message limite affiché

---

### Test 4: Membre Connecté Dès le Début

```bash
# Mode Normal (déjà connecté)
1. Ouvrir chat
2. Envoyer 10 messages
```

**Attendu**:
- ✅ Tous les messages envoyés normalement
- ✅ Aucun blocage
- ✅ Auto-save DB toutes les 30s

---

### Test 5: Console Logs

```bash
# DevTools → Console
# Mode Guest, 5 messages
```

**Attendu**:
```
[Persistence] Auto-save disabled for guests (using localStorage only)
[Persistence] Saved to localStorage
[Persistence] Saved to localStorage
[Persistence] Saved to localStorage
[Persistence] Saved to localStorage
[Persistence] Saved to localStorage
```

**Pas d'erreurs 401** ✅

---

## 🔧 Configuration

### Modifier la Limite de Messages

**Fichier**: `assets/js/chat-interface.js`

**Changer 5 messages → 10 messages**:
```javascript
// Ligne 422
if (!this.config.isLoggedIn && this.state.messageCount >= 10) {

// Ligne 1032
if (!this.config.isLoggedIn && this.state.messageCount >= 10) {
```

**Changer le texte du message**:
```javascript
// Ligne 872
<p>Vous avez utilisé vos <strong>10 messages gratuits</strong>.</p>
```

---

### Modifier les URLs de Redirection

Les URLs sont configurées dans PHP et passées via `wp_localize_script`:

**Fichier**: `lehiboo-ai-assistant.php`
**Ligne**: 326-327

```php
'loginUrl' => wp_login_url(),
'registerUrl' => wp_registration_url(),
```

**Custom URLs**:
```php
'loginUrl' => home_url('/mon-compte/login'),
'registerUrl' => home_url('/inscription'),
```

---

### Désactiver Complètement la Limitation

**Option 1**: Commentaires dans le code
```javascript
// Ligne 422-425 dans sendMessage()
// if (!this.config.isLoggedIn && this.state.messageCount >= 5) {
//   this.showMessageLimitReached();
//   return;
// }

// Ligne 1031-1034 dans loadConversationHistory()
// if (!this.config.isLoggedIn && this.state.messageCount >= 5) {
//   this.showMessageLimitReached();
// }
```

**Option 2**: Configuration PHP (futur)
```php
'messageLimit' => array(
    'enabled' => false, // Désactiver
    'maxMessages' => 5,
),
```

---

## 📊 Métriques à Suivre

### KPIs Marketing

1. **Taux de Conversion Inscription**:
   - Nombre de clicks "Créer un compte" / Nombre d'affichages message limite

2. **Taux de Conversion Connexion**:
   - Nombre de clicks "Se connecter" / Nombre d'affichages message limite

3. **Distribution Nombre de Messages**:
   - % utilisateurs arrêtés à 1-2 messages
   - % utilisateurs atteignant 5 messages
   - % utilisateurs s'inscrivant après limite

4. **Temps Moyen jusqu'à Limite**:
   - Durée moyenne de conversation avant blocage

### KPIs Techniques

1. **Erreurs Console**:
   - Aucune erreur 401 pour guests ✅
   - Aucune erreur validation ✅

2. **Performance**:
   - Temps chargement localStorage
   - Temps affichage message limite

3. **Taux de Rafraîchissement**:
   - % utilisateurs rafraîchissant après limite
   - % restant bloqués vs s'inscrivant

---

## 🚀 Déploiement

### Étape 1: Push Git

```bash
git push origin main
```

### Étape 2: Déployer sur Serveur

```bash
ssh user@preprod.lehiboo.com
cd /var/www/vhosts/preprod.lehiboo.com/httpdocs
git pull origin main
```

### Étape 3: Vider Cache Browser

**Important**: Les utilisateurs avec cache ancien ne verront pas les changements

```bash
# Chrome/Edge
Cmd+Shift+R (Mac)
Ctrl+Shift+R (Windows)

# Firefox
Cmd+Shift+Delete → Clear Cache
```

### Étape 4: Tester

1. Mode incognito
2. Envoyer 5 messages
3. Vérifier blocage au 6ème
4. Vérifier persistance après F5

---

## 🐛 Troubleshooting

### Problème: Message limite ne s'affiche pas

**Cause**: `isLoggedIn` mal configuré

**Solution**:
```javascript
// DevTools → Console
console.log(lehibooChatConfig.isLoggedIn);
// Devrait afficher: false (guest) ou true (membre)
```

---

### Problème: Textarea toujours disabled pour membres

**Cause**: Vérification `isLoggedIn` incorrecte

**Debug**:
```javascript
// Ligne 1032 chat-interface.js
console.log('isLoggedIn:', this.config.isLoggedIn);
console.log('messageCount:', this.state.messageCount);
```

**Fix**: S'assurer que `isLoggedIn: true` pour membres connectés

---

### Problème: Compteur ne persiste pas

**Cause**: localStorage bloqué (navigation privée stricte)

**Solution**: Fallback déjà implémenté
```javascript
// chat-persistence.js gère automatiquement les erreurs localStorage
```

---

### Problème: Message limite apparaît pour membres

**Cause**: Condition inversée

**Vérifier**:
```javascript
// Doit être !isLoggedIn (avec le !)
if (!this.config.isLoggedIn && this.state.messageCount >= 5)
```

---

## 📝 Notes de Version

### v2.0.0 - 2025-10-28

**Ajouté**:
- ✅ Limitation 5 messages pour guests
- ✅ Message de blocage avec CTA
- ✅ Désactivation input au blocage
- ✅ Persistance compteur localStorage
- ✅ Détection au chargement (refresh)
- ✅ Styles responsive mobile
- ✅ Animations (bounce, slideUp)

**Testé**:
- ✅ Blocage au 6ème message
- ✅ Persistance après refresh
- ✅ Réactivation après connexion
- ✅ Aucune limitation pour membres
- ✅ Responsive mobile

**Connu**:
- ⚠️ localStorage peut être bloqué en navigation privée stricte (Safari)
- ✅ Fallback: Compteur réinitialisé à 0, mais pas de crash

---

## 🔗 Liens Utiles

- **Code Source**: `assets/js/chat-interface.js`
- **Styles**: `assets/css/chat-interface.css`
- **Persistence**: `assets/js/chat-persistence.js`
- **Configuration PHP**: `lehiboo-ai-assistant.php`
- **Documentation Principale**: `CHANGELOG.md`
- **Deployment Guide**: `DEPLOYMENT_CHECKLIST.md`

---

**Auteur**: Claude AI + Juba
**Date**: 2025-10-28
**Version**: 2.0.0
**Status**: ✅ Production Ready
