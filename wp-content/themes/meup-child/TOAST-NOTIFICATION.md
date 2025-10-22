# Système de Notifications Toast - V1 Le Hiboo

## 📖 Description

Système de notifications toast moderne et élégant pour remplacer les `alert()` JavaScript par défaut. Les toasts offrent une meilleure expérience utilisateur avec des animations fluides et des messages contextuels.

## ✨ Fonctionnalités

- **4 types de notifications** : Succès, Erreur, Avertissement, Information
- **Animations fluides** : Slide, fade, bounce
- **Responsive** : Adapté mobile et desktop
- **Accessible** : Support clavier et lecteurs d'écran
- **Personnalisable** : Durée, position, couleurs
- **Barre de progression** : Indication visuelle du temps restant
- **Pause au survol** : Le toast reste affiché pendant le survol
- **Mode sombre** : Support automatique selon les préférences système

## 🚀 Installation

Le système est déjà installé et chargé automatiquement sur toutes les pages du site via `functions.php`.

### Fichiers inclus

```
/wp-content/themes/meup-child/
├── assets/
│   ├── js/
│   │   └── toast-notification.js
│   └── css/
│       └── toast-notification.css
```

## 📝 Utilisation

### 1. Méthode basique

```javascript
// Notification de succès
ToastNotification.success('Enregistré avec succès !');

// Notification d'erreur
ToastNotification.error('Une erreur est survenue.');

// Notification d'avertissement
ToastNotification.warning('Attention : vérifiez vos informations.');

// Notification d'information
ToastNotification.info('Votre session expire dans 5 minutes.');
```

### 2. Messages flash (après rechargement de page)

Pour afficher un toast après un rechargement de page (très utile pour les formulaires AJAX qui rechargent la page) :

```javascript
// Stocker le message avant le reload
ToastNotification.setFlashMessage('Profil mis à jour !', 'success');
location.reload();

// Le toast s'affichera automatiquement après le rechargement
```

**Exemple complet avec AJAX :**

```javascript
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            // Stocker le message pour l'afficher après le reload
            ToastNotification.setFlashMessage(
                response.data.message || 'Enregistré avec succès !',
                'success'
            );

            // Recharger la page
            setTimeout(function() {
                location.reload();
            }, 400);
        }
    }
});
```

### 3. Méthode avec options

```javascript
ToastNotification.success('Profil mis à jour !', {
    duration: 6000,          // Durée en ms (0 = infini)
    position: 'top-center',  // Position du toast
    closeButton: true,       // Bouton de fermeture
    progressBar: true,       // Barre de progression
    pauseOnHover: true,      // Pause au survol
    animation: 'bounce'      // Type d'animation
});
```

### 3. Fonction générique

```javascript
// Syntaxe alternative
showToast('Message personnalisé', 'success');
showToast('Erreur critique', 'error', { duration: 0 });
```

## 🎨 Options disponibles

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `duration` | number | 4000 | Durée d'affichage en ms (0 = infini) |
| `position` | string | 'top-right' | Position : top-right, top-left, bottom-right, bottom-left, top-center, bottom-center |
| `closeButton` | boolean | true | Afficher le bouton X |
| `progressBar` | boolean | true | Afficher la barre de progression |
| `pauseOnHover` | boolean | true | Pause au survol |
| `animation` | string | 'slide' | Animation : slide, fade, bounce |

## 💡 Exemples d'intégration

### Dans une réponse AJAX

```javascript
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            ToastNotification.success(response.data.message);
        } else {
            ToastNotification.error(response.data.message);
        }
    },
    error: function() {
        ToastNotification.error('Erreur de connexion. Veuillez réessayer.');
    }
});
```

### Validation de formulaire

```javascript
$('#my-form').on('submit', function(e) {
    e.preventDefault();

    var email = $('#email').val();

    if (!email) {
        ToastNotification.warning('Veuillez renseigner votre email.');
        return false;
    }

    if (!isValidEmail(email)) {
        ToastNotification.error('Email invalide.');
        return false;
    }

    // Soumission...
});
```

### Rétrocompatibilité avec alert()

Pour assurer la compatibilité avec les anciens navigateurs ou si le système toast n'est pas chargé :

```javascript
if (typeof ToastNotification !== 'undefined') {
    ToastNotification.success('Message envoyé !');
} else {
    alert('Message envoyé !');
}
```

## 🎯 Cas d'usage

### ✅ Succès (success)
- Enregistrement réussi
- Envoi de message
- Connexion réussie
- Mise à jour effectuée

### ❌ Erreur (error)
- Échec de validation
- Erreur serveur
- Champs obligatoires manquants
- Timeout de connexion

### ⚠️ Avertissement (warning)
- Champs à vérifier
- Session qui expire
- Limite bientôt atteinte
- Action à confirmer

### ℹ️ Information (info)
- Conseils d'utilisation
- Nouvelles fonctionnalités
- Statut en cours
- Rappels

## 🔧 Méthodes avancées

### Fermer tous les toasts

```javascript
ToastNotification.closeAll();
```

### Référence au toast créé

```javascript
var myToast = ToastNotification.success('Message');

// Fermer manuellement après 2 secondes
setTimeout(function() {
    ToastNotification.hide(myToast);
}, 2000);
```

## 🎨 Personnalisation CSS

Les toasts peuvent être personnalisés via CSS :

```css
/* Changer la couleur de succès */
.toast-success {
    border-left-color: #00ff00;
}

.toast-success .toast-icon {
    color: #00ff00;
}

/* Modifier la position sur mobile */
@media (max-width: 640px) {
    .toast-container {
        bottom: 80px; /* Au-dessus de la barre mobile */
    }
}
```

## 🌐 Support navigateurs

- Chrome/Edge : ✅ Complet
- Firefox : ✅ Complet
- Safari : ✅ Complet
- Mobile iOS/Android : ✅ Complet
- IE11 : ⚠️ Fonctionnel (sans animations avancées)

## 📱 Responsive

- **Desktop** : Position en haut à droite par défaut
- **Mobile** : Position en bas (au-dessus du sticky mobile)
- **Tablette** : Adaptation automatique

## ♿ Accessibilité

- Attributs ARIA appropriés
- Support navigation clavier
- Focus visible sur le bouton fermer
- Respect de `prefers-reduced-motion`

## 🔄 Migrations effectuées

Le système remplace désormais tous les `alert()` dans :

1. ✅ `profile-validation.js` - Validation mot de passe
2. ✅ `single-event-airbnb.js` - Formulaire de contact
3. ✅ `profile-navigation.js` - Sauvegarde formulaires AJAX

## 📌 Notes importantes

- Le système est chargé **globalement** sur tout le site
- **Rétrocompatible** : Fallback sur `alert()` si non disponible
- **Performances** : Pas d'impact, chargement async
- **Cache navigateur** : Version 1.0.0, pensez à vider le cache en développement

## 🐛 Debug

Pour vérifier que le système est chargé :

```javascript
console.log(typeof ToastNotification); // Devrait afficher "object"
```

## 👨‍💻 Auteur

**V1 Le Hiboo** - Système développé pour améliorer l'UX du projet LeHiboo

---

📅 Créé le : 2025-10-22
📝 Version : 1.0.0
🔄 Dernière mise à jour : 2025-10-22
