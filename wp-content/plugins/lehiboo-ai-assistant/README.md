# Le Hiboo AI Assistant 🦉

**Version:** 1.0.0
**Requires:** WordPress 5.8+, PHP 7.4+

Assistant conversationnel IA pour aider vos utilisateurs à trouver l'activité parfaite sur Le Hiboo.

---

## 🎯 Fonctionnalités

### Pour les Utilisateurs
- 💬 **Chat conversationnel naturel** - Interface moderne et intuitive
- 🎯 **Recommandations personnalisées** - Basées sur âge, budget, préférences
- 🌤️ **Intégration météo** - Suggestions adaptées aux conditions
- 📅 **Planificateur de weekend** - Création de packages complets
- 👨‍👩‍👧 **Mode famille** - Filtrage activités adaptées aux enfants
- 🔒 **100% sécurisé** - Protection données et anti-spam

### Pour les Admins
- ⚙️ **Prompts éditables** - Personnalisation sans code
- 📊 **Analytics détaillés** - Tracking conversations et conversions
- 🛡️ **Sécurité renforcée** - Rate limiting, validation, logs
- 🎨 **Interface personnalisable** - CSS variables pour branding
- 🔧 **Configuration facile** - Interface admin WordPress

---

## 📦 Installation

### 1. Upload du Plugin

```bash
# Via FTP ou cPanel
wp-content/plugins/lehiboo-ai-assistant/

# Ou via WP-CLI
wp plugin install lehiboo-ai-assistant.zip --activate
```

### 2. Activation

WordPress Admin > Extensions > Activer "Le Hiboo AI Assistant"

### 3. Configuration Initiale

WordPress Admin > Le Hiboo > AI Assistant > Settings

**Configuration minimale requise :**
- ✅ Activer l'assistant : OUI
- ✅ API Backend URL : `https://votre-api.com/chat`
- ✅ API Key (si nécessaire)

---

## 🔧 Configuration

### Settings Principales

#### Sécurité
```yaml
Rate Limiting:
  - Max messages: 10 messages / minute
  - Timeout: 60 secondes
  - Auto-block IP abusives: OUI

Validation:
  - Max message length: 2000 caractères
  - XSS protection: ACTIVÉ
  - Prompt injection detection: ACTIVÉ
```

#### Affichage
```yaml
Interface:
  - Position: Bas-droite (modifiable en CSS)
  - Taille: 400x600px desktop, fullscreen mobile
  - Thème: Light (dark mode disponible)
  - Animation: Activée
```

#### Analytics
```yaml
Tracking:
  - Conversations anonymisées: OUI
  - Rétention données: 90 jours
  - RGPD compliant: OUI
```

---

## 🎨 Personnalisation

### CSS Variables

Modifier les couleurs dans votre thème enfant :

```css
:root {
  --lehiboo-primary: #YOUR_COLOR;
  --lehiboo-secondary: #YOUR_COLOR;
  /* Voir assets/css/chat-interface.css pour toutes les variables */
}
```

### Prompts Personnalisés

Éditer les fichiers YAML :

```yaml
# prompts/system-prompt.yaml
identity:
  name: "Votre Assistant"
  personality: "Votre ton personnalisé..."

# prompts/specialized/weekend-planner.yaml
# Personnaliser le planificateur de weekend
```

**Important:** Toujours tester vos prompts avant mise en production !

---

## 🔌 Intégration Backend (Node.js API)

### Prérequis Backend

```json
{
  "node": ">=18.0.0",
  "dependencies": {
    "@ai-sdk/core": "^0.0.x",
    "@ai-sdk/openai": "^0.0.x",
    "@modelcontextprotocol/sdk": "^0.5.x",
    "express": "^4.18.x",
    "express-rate-limit": "^7.0.x",
    "zod": "^3.22.x",
    "winston": "^3.11.x"
  }
}
```

### Setup API Backend

1. **Créer serveur Node.js séparé**

```javascript
// server.js
const express = require('express');
const { openai } = require('@ai-sdk/openai');
const { streamText } = require('ai');

const app = express();
app.use(express.json());

// Endpoint chat
app.post('/api/chat', async (req, res) => {
  const { message, conversationId, userContext } = req.body;

  // Valider inputs
  // Appeler IA avec MCP tools
  // Streamer réponse

  res.json({ success: true, message: aiResponse });
});

app.listen(3000);
```

2. **Configuration OpenRouter**

```env
OPENROUTER_API_KEY=your_key_here
DEFAULT_MODEL=anthropic/claude-3.5-sonnet
```

3. **Implémenter MCP Server**

Voir `ARCHITECTURE.md` section "MCP Tools Configuration"

---

## 🛡️ Sécurité

### Checklist Sécurité Pré-Production

- [ ] HTTPS activé partout
- [ ] Rate limiting configuré
- [ ] CSP headers actifs
- [ ] Nonces WordPress validés
- [ ] Inputs sanitisés/validés
- [ ] Logs anonymisés
- [ ] Backups automatiques
- [ ] Tests sécurité passés

### Surveillance

```bash
# Logs sécurité
tail -f wp-content/debug.log | grep "Lehiboo AI Security"

# Logs rate limiting
# Voir WordPress Admin > Le Hiboo > Logs
```

### En cas d'incident

1. Désactiver plugin immédiatement
2. Analyser logs sécurité
3. Bloquer IP attaquantes
4. Contacter support

**Contact urgence:** [À DÉFINIR]

---

## 📊 Analytics & Reporting

### Métriques Disponibles

**Engagement:**
- Conversations démarrées
- Durée moyenne conversation
- Messages par conversation
- Taux abandon par étape

**Conversion:**
- Taux recommandations → détails
- Taux détails → réservation
- Valeur moyenne commande
- ROI assistant vs formulaire classique

**Qualité:**
- Pertinence recommandations
- Temps réponse moyen
- Erreurs IA
- Feedback utilisateurs

### Exports

WordPress Admin > Le Hiboo > Analytics > Exporter CSV

---

## 🚀 Optimisation Performance

### Cache Recommandé

```php
// Dans wp-config.php
define('WP_CACHE', true);

// Installer plugin cache (WP Rocket, W3 Total Cache, etc.)
```

### CDN

Configurer CDN pour assets statiques :
- `/assets/css/`
- `/assets/js/`

### Database

```sql
-- Optimiser tables analytics (à faire mensuellement)
OPTIMIZE TABLE wp_lehiboo_conversations;
OPTIMIZE TABLE wp_lehiboo_rate_limits;
```

---

## 🐛 Troubleshooting

### Chat ne s'affiche pas

1. Vérifier que plugin est activé
2. Vérifier JavaScript chargé (Console navigateur)
3. Vérifier CSS chargé
4. Conflits thème ? Tester avec thème par défaut

### Messages n'envoient pas

1. Vérifier Console navigateur pour erreurs
2. Tester endpoint : `/wp-json/lehiboo/v1/health`
3. Vérifier nonce valide
4. Vérifier rate limiting pas atteint

### API Backend ne répond pas

1. Vérifier serveur Node.js actif
2. Vérifier URL API correcte dans settings
3. Vérifier CORS configuré
4. Vérifier logs backend

### Erreur "Rate Limit Exceeded"

Attendre fin fenêtre (60s) ou augmenter limite dans settings.

---

## 📚 Documentation Complète

- **ARCHITECTURE.md** - Architecture technique détaillée
- **SECURITY.md** - Guide sécurité complet
- **prompts/system-prompt.yaml** - Configuration prompts système

---

## 🔄 Mises à Jour

### Changelog

**v1.0.0 (2025-10-27)**
- ✨ Release initiale
- 🔒 Sécurité renforcée
- 🎨 Interface moderne
- 📊 Analytics complets
- 🌤️ Intégration météo

### Roadmap

**v1.1.0 (Q1 2025)**
- 🎤 Mode vocal (Speech-to-Text)
- 🌍 Multilangue (EN, ES)
- 🤖 Fine-tuning IA sur données
- 📱 App mobile dédiée

**v1.2.0 (Q2 2025)**
- 🎁 Système de fidélité intégré
- 🎯 A/B testing prompts automatique
- 📈 Prédictions ML avancées
- 🔗 Intégrations tierces (Stripe, etc.)

---

## 🤝 Support

### Documentation
- GitHub Wiki: https://github.com/lehiboo/ai-assistant/wiki
- Tutoriels vidéo: https://youtube.com/@lehiboo

### Community
- Forum: https://community.lehiboo.com
- Discord: https://discord.gg/lehiboo
- Email: support@lehiboo.com

### Bugs & Feature Requests
GitHub Issues: https://github.com/lehiboo/ai-assistant/issues

---

## 📄 Licence

GPL v2 or later
https://www.gnu.org/licenses/gpl-2.0.html

---

## 👏 Crédits

Développé avec par l'équipe Le Hiboo

**Technologies utilisées:**
- AI SDK by Vercel
- Model Context Protocol (MCP)
- OpenRouter
- WordPress REST API

---

## ⚠️ Avertissement

Ce plugin fait appel à des services IA tiers (OpenRouter, OpenAI, Anthropic, etc.).
Assurez-vous de respecter leurs conditions d'utilisation et leurs tarifs.

**Coûts estimés:** ~0.01-0.02€ par conversation selon le modèle.

---

**Fait avec ❤️ pour améliorer l'expérience utilisateur Le Hiboo**
