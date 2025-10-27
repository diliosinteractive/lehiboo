# 🎨 Guide de Personnalisation - Le Hiboo AI Assistant

Guide complet pour personnaliser l'apparence et le comportement du chat.

---

## 🎨 COULEURS & CHARTE GRAPHIQUE

### Couleurs Actuelles (Charte Le Hiboo)

Le plugin utilise la charte graphique de Le Hiboo :

```css
:root {
  --lehiboo-primary: #FF385C;      /* Rouge-orange principal */
  --lehiboo-primary-dark: #E0294A; /* Rouge-orange foncé (hover) */
  --lehiboo-secondary: #FF7A59;    /* Orange secondaire */
}
```

### Personnaliser les Couleurs

**Méthode 1 : Via votre thème enfant (Recommandé)**

Dans `wp-content/themes/votre-theme-enfant/style.css` :

```css
/* Personnalisation Le Hiboo AI */
:root {
  --lehiboo-primary: #VOTRE_COULEUR;
  --lehiboo-primary-dark: #VOTRE_COULEUR_FONCEE;
  --lehiboo-secondary: #VOTRE_COULEUR_SECONDAIRE;
}
```

**Méthode 2 : Directement dans le plugin**

Éditer `assets/css/chat-interface.css` lignes 6-10.

⚠️ **Attention :** Les modifications directes seront écrasées lors des mises à jour du plugin.

---

## 🖼️ IMAGES & AVATARS

### Image Le Hiboo Actuelle

L'image utilisée : `/wp-content/plugins/eventlist/assets/img/unknow_user.png`

### Changer l'Image de l'Assistant

**Option 1 : Remplacer l'image existante**
- Remplacer le fichier `unknow_user.png` par votre image
- Format recommandé : PNG carré (200x200px minimum)
- Fond transparent recommandé

**Option 2 : Utiliser une autre image**

Dans `assets/js/chat-interface.js`, remplacer l'URL :

```javascript
// Ligne 174 - FAB button
fab.innerHTML = '<img src="/VOTRE/CHEMIN/image.png" ...>';

// Ligne 187 - Avatar header
<img src="/VOTRE/CHEMIN/image.png" ...>

// Ligne 498 - Avatar messages
avatar.innerHTML = '<img src="/VOTRE/CHEMIN/image.png" ...>';

// Ligne 632 - Typing indicator
<img src="/VOTRE/CHEMIN/image.png" ...>
```

**Option 3 : Utiliser un emoji/texte**

Remplacer les `<img>` par un emoji ou texte :

```javascript
fab.innerHTML = '🦉';  // ou votre emoji
avatar.innerHTML = '🦉';
```

---

## 📝 TEXTES & MESSAGES

### Modifier le Message d'Accueil

**Méthode 1 : Via WordPress Admin (À venir v1.1)**
- WordPress Admin > AI Assistant > Paramètres > Prompts

**Méthode 2 : Éditer le fichier YAML**

Fichier : `prompts/system-prompt.yaml`

```yaml
conversation_stages:
  greeting:
    prompt: |
      [Votre message d'accueil personnalisé ici]

    quick_chips:
      - "🧍 Solo"
      - "💑 En couple"
      # Modifiez ou ajoutez vos options
```

### Changer le Nom de l'Assistant

Dans `assets/js/chat-interface.js` ligne 190 :

```javascript
<h2 class="lehiboo-chat-title">VOTRE NOM</h2>
```

### Modifier le Sous-titre

Ligne 192 :

```javascript
<p class="lehiboo-chat-subtitle">
  <span class="lehiboo-status-indicator"></span>
  Votre sous-titre personnalisé
</p>
```

---

## 📐 DIMENSIONS & POSITIONNEMENT

### Taille du Chat

Dans `assets/css/chat-interface.css` ligne 62 :

```css
.lehiboo-chat-container {
  width: 400px;          /* Largeur desktop */
  height: 600px;         /* Hauteur desktop */
  /* Modifiez selon vos besoins */
}
```

### Position du Bouton FAB

Ligne 724 :

```css
.lehiboo-chat-fab {
  bottom: var(--spacing-lg);  /* 24px du bas */
  right: var(--spacing-lg);   /* 24px de droite */

  /* Pour positionner à gauche : */
  /* left: var(--spacing-lg); */
  /* right: auto; */
}
```

### Taille du Bouton FAB

Ligne 719 :

```css
.lehiboo-chat-fab {
  width: 60px;   /* Modifier ici */
  height: 60px;  /* Modifier ici */
}
```

---

## 🎭 COMPORTEMENT

### Modifier le Rate Limiting

**Via WordPress Admin :**
- WordPress Admin > AI Assistant > Paramètres
- Messages max par fenêtre : 10 (défaut)
- Fenêtre de temps : 60 secondes (défaut)

**Ou directement dans le code :**

Fichier : `includes/class-rate-limiter.php` ligne 19 :

```php
$max_requests = intval(get_option('lehiboo_ai_rate_limit_messages', 10));
$window_seconds = intval(get_option('lehiboo_ai_rate_limit_window', 60));
```

### Longueur Maximum Message

**Via WordPress Admin :**
- WordPress Admin > AI Assistant > Paramètres
- Longueur max message : 2000 (défaut)

**Ou dans le code :**

Fichier : `assets/js/chat-interface.js` ligne 62 :

```javascript
static MAX_LENGTH = 2000; // Modifier ici
```

### Auto-ouverture du Chat

Pour ouvrir automatiquement le chat au chargement de la page :

Dans `assets/js/chat-interface.js`, ajouter après ligne 163 :

```javascript
// Send greeting message
this.sendGreeting();

// Auto-open chat (nouveau code)
setTimeout(() => {
  this.openChat();
}, 1000); // Attendre 1 seconde puis ouvrir

this.log('Chat interface initialized successfully');
```

---

## 🔤 TYPOGRAPHIE

### Changer la Police

Dans `assets/css/chat-interface.css` ligne 39 :

```css
:root {
  --font-family: 'Votre Police', -apple-system, BlinkMacSystemFont, sans-serif;
}
```

N'oubliez pas de charger la police dans votre thème :

```css
@import url('https://fonts.googleapis.com/css2?family=Votre+Police&display=swap');
```

### Tailles de Police

Ligne 40-44 :

```css
:root {
  --font-size-xs: 12px;  /* Petits textes */
  --font-size-sm: 14px;  /* Texte standard */
  --font-size-md: 16px;  /* Texte moyen */
  --font-size-lg: 18px;  /* Grands textes */
  --font-size-xl: 20px;  /* Très grands textes */
}
```

---

## 🌙 MODE SOMBRE (Dark Mode)

Le plugin est prêt pour un mode sombre. Ajouter dans votre CSS :

```css
/* Mode sombre */
@media (prefers-color-scheme: dark) {
  :root {
    --lehiboo-white: #1E1E1E;
    --lehiboo-dark: #FFFFFF;
    --lehiboo-gray-100: #2A2A2A;
    --lehiboo-gray-200: #3A3A3A;
    --lehiboo-gray-300: #4A4A4A;
  }

  .lehiboo-chat-container {
    background: #1E1E1E;
    color: #FFFFFF;
  }

  .lehiboo-message-bubble {
    background: #2A2A2A;
    color: #FFFFFF;
  }
}
```

---

## 📱 MOBILE

### Désactiver Fullscreen Mobile

Par défaut, le chat est fullscreen sur mobile. Pour garder le format desktop :

Dans `assets/css/chat-interface.css`, commenter lignes 782-792 :

```css
@media (max-width: 640px) {
  /* Commenter ou supprimer ces lignes */
  /*
  .lehiboo-chat-container {
    bottom: 0;
    right: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    border-radius: 0;
  }
  */
}
```

---

## 🎯 QUICK CHIPS (Boutons Rapides)

### Personnaliser les Options

Dans `prompts/system-prompt.yaml` :

```yaml
conversation_stages:
  greeting:
    quick_chips:
      - "Votre option 1"
      - "Votre option 2"
      - "Votre option 3"
```

### Désactiver les Quick Chips

Dans `assets/css/chat-interface.css`, ajouter :

```css
.lehiboo-quick-chips {
  display: none !important;
}
```

---

## 🔔 ANIMATIONS

### Désactiver les Animations

Dans `assets/css/chat-interface.css`, ajouter en haut du fichier :

```css
/* Désactiver toutes les animations */
* {
  animation: none !important;
  transition: none !important;
}
```

### Ralentir les Animations

Modifier les variables de transition ligne 57-59 :

```css
:root {
  --transition-fast: 300ms ease;   /* Au lieu de 150ms */
  --transition-normal: 500ms ease; /* Au lieu de 250ms */
  --transition-slow: 700ms ease;   /* Au lieu de 350ms */
}
```

---

## 🎪 ÉVÉNEMENTS (Event Cards)

### Personnaliser les Cards

Dans `assets/js/chat-interface.js`, méthode `createEventCard()` ligne 531.

Vous pouvez modifier la structure HTML complète des cards.

### Changer les Icônes Météo

Ligne 544-557, remplacer les emojis par vos icônes :

```javascript
<span class="lehiboo-event-card-meta-icon">📅</span> // Date
<span class="lehiboo-event-card-meta-icon">📍</span> // Lieu
<span class="lehiboo-event-card-meta-icon">⏱️</span> // Durée
```

---

## 🔧 AVANCÉ

### Ajouter un Son aux Messages

Dans `assets/js/chat-interface.js`, méthode `addMessage()` ligne 458 :

```javascript
addMessage(message) {
  this.state.messages.push(message);

  // Jouer un son (nouveau code)
  if (message.role === 'assistant') {
    const audio = new Audio('/path/to/notification.mp3');
    audio.volume = 0.3;
    audio.play().catch(() => {}); // Ignorer les erreurs
  }

  const messageEl = this.createMessageElement(message);
  // ... reste du code
}
```

### Intégrer Google Analytics

Dans `assets/js/chat-interface.js`, méthode `trackEvent()` ligne 762 :

```javascript
trackEvent(eventName, data = {}) {
  this.log('Track event:', eventName, data);

  // Google Analytics 4
  if (typeof gtag !== 'undefined') {
    gtag('event', eventName, {
      event_category: 'LeHiboo_Chat',
      ...data
    });
  }

  // Facebook Pixel (optionnel)
  if (typeof fbq !== 'undefined') {
    fbq('trackCustom', 'LeHiboo_' + eventName, data);
  }
}
```

---

## 📋 CHECKLIST PERSONNALISATION

Avant de personnaliser, toujours :

- [ ] Faire une sauvegarde du fichier original
- [ ] Tester en environnement de développement
- [ ] Vérifier la compatibilité mobile
- [ ] Tester avec différents navigateurs
- [ ] Vérifier l'accessibilité (contraste, focus)
- [ ] Documenter vos modifications

---

## 🆘 PROBLÈMES COURANTS

### Les couleurs ne changent pas
→ Vider le cache navigateur (Ctrl+F5)
→ Vérifier que votre CSS se charge après le CSS du plugin

### L'image ne s'affiche pas
→ Vérifier le chemin de l'image (absolu depuis la racine)
→ Vérifier les permissions du fichier (644)
→ Tester l'URL directement dans le navigateur

### Les modifications sont écrasées
→ Utiliser un thème enfant plutôt que modifier le plugin
→ Ou documenter pour réappliquer après mise à jour

---

## 💡 CONSEILS

1. **Thème Enfant** : Toujours personnaliser via un thème enfant
2. **Variables CSS** : Utiliser les variables CSS pour faciliter les modifications
3. **Documentation** : Documenter toutes vos personnalisations
4. **Backups** : Toujours sauvegarder avant modifications
5. **Tests** : Tester sur tous les appareils et navigateurs

---

## 📚 RESSOURCES

- **CSS Variables Guide** : https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties
- **WordPress Child Themes** : https://developer.wordpress.org/themes/advanced-topics/child-themes/
- **Accessibility Guide** : https://www.w3.org/WAI/WCAG21/quickref/

---

**Besoin d'aide ?** Consultez [README.md](./README.md) ou [QUICK_START_GUIDE.md](./QUICK_START_GUIDE.md)

**Suggestions de personnalisation ?** Ouvrez une issue sur GitHub !

---

*Dernière mise à jour : 2025-10-27*
*Version : 1.0.1*
