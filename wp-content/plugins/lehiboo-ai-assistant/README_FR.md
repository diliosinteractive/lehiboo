# 🦉 Le Hiboo - Assistant IA Conversationnel

**Version** : 1.0.0
**Auteur** : Le Hiboo
**Licence** : GPL v2 or later

---

## 📖 Description

Plugin WordPress qui offre un assistant conversationnel IA pour aider les utilisateurs à trouver l'activité parfaite parmi les événements Le Hiboo.

### ✨ Fonctionnalités

- 💬 **Chat immersif** plein écran (demi-largeur desktop, plein écran mobile)
- 🎨 **Design Le Hiboo** avec couleur orange (#FF601F) et police Montserrat
- 🤖 **IA conversationnelle** via OpenRouter (GPT-4, Claude, etc.)
- 🔒 **Sécurité renforcée** : rate limiting, validation, sanitization
- 🌤️ **Intégration météo** pour suggérer alternatives indoor
- 📊 **Analytics anonymisées** (RGPD compliant)
- 🎯 **Recommandations personnalisées** basées sur critères utilisateur
- 📦 **Création de packages** weekend/vacances
- 🎭 **Mode démo** fonctionnel sans backend

### 🎯 Critères Collectés

L'assistant pose les bonnes questions pour comprendre vos besoins :
- Type de groupe (solo, couple, famille, amis)
- Âge / tranche d'âge
- Dates souhaitées
- Type d'activité (sport, culture, gastronomie, nature, détente)
- Niveau d'énergie
- Budget approximatif

---

## 🚀 Installation Rapide

### 1. Installer le Plugin

**Option A** : Via WordPress Admin
```
1. WP Admin → Extensions → Ajouter
2. Uploader le ZIP du plugin
3. Activer
```

**Option B** : Via FTP
```bash
# Copier le dossier dans wp-content/plugins/
cp -r lehiboo-ai-assistant /path/to/wordpress/wp-content/plugins/
```

### 2. Configuration Initiale

1. Aller dans **WP Admin → Le Hiboo → Assistant IA**
2. **Activer le chat** : Cocher "Activer l'assistant IA"
3. **Sauvegarder**

> ⚠️ **Mode Démo** : Le chat fonctionne immédiatement en mode démo avec réponses simulées. Pour activer l'IA réelle, voir la section "Backend IA" ci-dessous.

### 3. Tester

1. Aller sur n'importe quelle page frontend
2. Cliquer sur le **bouton orange Le Hiboo** en bas à droite
3. Le chat s'ouvre en mode immersif !

---

## 🔧 Configuration Backend IA (Optionnel)

Pour activer l'intelligence artificielle réelle (au lieu du mode démo) :

### Prérequis

- Node.js 18+ installé
- Compte OpenRouter (gratuit) : https://openrouter.ai
- Serveur pour héberger le backend Node.js (Railway, Vercel, VPS...)

### Étapes

1. **Créer le serveur backend** (voir `docs/IMPLEMENTATION_GUIDE.md`)
2. **Obtenir une clé API OpenRouter**
3. **Configurer dans WordPress** :
   ```
   WP Admin → Le Hiboo → Assistant IA → Paramètres
   - URL Backend : https://votre-backend.com
   - Clé API : votre-cle-openrouter
   ```
4. **Sauvegarder**

Le mode démo sera automatiquement désactivé et l'IA réelle prendra le relais.

---

## 📂 Structure du Plugin

```
lehiboo-ai-assistant/
├── lehiboo-ai-assistant.php    # Plugin principal
├── includes/                    # Classes PHP
│   ├── class-security.php      # Sécurité
│   ├── class-chat-handler.php  # Gestion chat + mode démo
│   ├── class-rate-limiter.php  # Rate limiting
│   └── ...
├── assets/
│   ├── css/
│   │   └── chat-interface.css  # Styles (Montserrat, #FF601F)
│   └── js/
│       └── chat-interface.js   # Interface interactive
├── prompts/                     # Prompts YAML modulaires
├── admin/                       # Interface admin WordPress
└── docs/                        # Documentation complète
    ├── START_HERE.md           # Commencer ici
    ├── ARCHITECTURE.md         # Architecture technique
    ├── TESTING_GUIDE.md        # Guide de test
    └── ...
```

---

## 🎨 Personnalisation

### Changer les Couleurs

Éditer `assets/css/chat-interface.css` :
```css
:root {
  --lehiboo-primary: #FF601F;      /* Orange principal */
  --lehiboo-primary-dark: #E55519; /* Orange foncé */
  --lehiboo-secondary: #FF7A3D;    /* Orange clair */
}
```

### Changer la Police

Éditer `lehiboo-ai-assistant.php` ligne 234 :
```php
wp_enqueue_style(
    'lehiboo-ai-montserrat',
    'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap'
);
```

### Modifier les Prompts

Les prompts sont dans `prompts/` au format YAML :
- `system-prompt.yaml` : Prompt principal
- `specialized/` : Prompts spécialisés (famille, weekend, etc.)

Vous pouvez les éditer directement ou via l'interface admin (quand activée).

---

## 🔒 Sécurité

### Rate Limiting
- **Client** : 10 messages / 60 secondes
- **Serveur** : 20 requêtes / minute par IP

### Validation
- Longueur max messages : 2000 caractères
- Sanitization XSS
- Protection injection SQL
- Protection prompt injection

### RGPD
- Conversations anonymisées (tranches d'âge, pas âges exacts)
- Pas de stockage données sensibles
- Opt-out facile

---

## 📊 Analytics

Les conversations sont trackées de manière anonyme dans la table `wp_lehiboo_conversations` :

```sql
SELECT
  age_range,
  group_type,
  COUNT(*) as conversations
FROM wp_lehiboo_conversations
GROUP BY age_range, group_type;
```

Dashboard admin disponible dans **WP Admin → Le Hiboo → Analytics**.

---

## 🐛 Dépannage

### Le chat ne s'affiche pas

1. Vérifier que le plugin est **activé**
2. Aller dans **Settings** et cocher "Activer l'assistant"
3. Vider le cache (Ctrl+Shift+R)
4. Vérifier la console JavaScript (F12)

### Erreur "Backend non configuré"

C'est normal ! Le mode démo fonctionne sans backend. Pour activer l'IA :
1. Installer le backend Node.js (voir docs)
2. Configurer l'URL dans Settings

### Les styles ne s'appliquent pas

1. Vérifier que Montserrat charge (F12 → Network → Fonts)
2. Vider cache WordPress
3. Régénérer CSS si thème child utilisé

---

## 📚 Documentation Complète

- **[START_HERE.md](docs/START_HERE.md)** - Point de départ
- **[ARCHITECTURE.md](docs/ARCHITECTURE.md)** - Architecture détaillée
- **[TESTING_GUIDE.md](docs/TESTING_GUIDE.md)** - Guide de test
- **[SECURITY.md](docs/SECURITY.md)** - Guide sécurité
- **[IMPLEMENTATION_GUIDE.md](docs/IMPLEMENTATION_GUIDE.md)** - Développement backend

---

## 🆘 Support

### En cas de problème

1. **Logs WordPress** : `wp-content/debug.log`
2. **Console JavaScript** : F12 dans le navigateur
3. **Settings** : Activer "Mode Debug" dans les paramètres

### Contacts

- GitHub Issues : [À définir]
- Support : [À définir]
- Documentation : https://lehiboo.com/docs

---

## 📝 Changelog

### Version 1.0.0 (2025-10-27)

**✨ Nouvelles Fonctionnalités**
- Interface chat immersive (plein écran, demi-largeur)
- Mode démo fonctionnel sans backend
- Design Le Hiboo (orange #FF601F, Montserrat)
- Flow conversationnel complet
- Event cards avec images
- Rate limiting client + serveur
- Analytics anonymisées
- Responsive design

**🔒 Sécurité**
- Validation inputs
- Sanitization XSS
- Protection injection SQL
- Protection prompt injection
- RGPD compliant

---

## 📄 Licence

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

---

## 🙏 Crédits

- **AI SDK** : Vercel (https://sdk.vercel.ai)
- **OpenRouter** : Multi-model AI provider
- **Icons** : Emojis Unicode
- **Images demo** : Unsplash

---

**Développé avec ❤️ pour Le Hiboo**
