# 🧭 Guide de Navigation - Documentation Le Hiboo AI

**Vous ne savez pas par où commencer ?** Ce guide vous oriente vers la bonne documentation selon votre besoin.

---

## 🎯 Je Veux...

### 🚀 Démarrer Rapidement (5 minutes)

**→ [lehiboo-ai-backend/QUICK_START.md](lehiboo-ai-backend/QUICK_START.md)**

Guide express pour :
- ✅ Installer le backend
- ✅ Obtenir clés API
- ✅ Lancer le serveur
- ✅ Tester la connexion

**Temps** : 5-10 minutes

---

### 📖 Comprendre le Projet

**→ [README.md](README.md)**

Vue d'ensemble complète :
- Qu'est-ce que Le Hiboo AI Assistant ?
- Fonctionnalités principales
- Technologies utilisées
- Structure du projet
- Quick start général

**Temps** : 10-15 minutes de lecture

---

### 🏗️ Comprendre l'Architecture

**→ [ARCHITECTURE_OVERVIEW.md](ARCHITECTURE_OVERVIEW.md)**

Diagrammes et explications :
- Architecture globale (Frontend → Backend → IA)
- Flow complet d'une conversation
- Data flow events et météo
- Layers de sécurité
- Structure fichiers
- Technologies stack

**Temps** : 15-20 minutes

---

### 🧪 Tester le Système End-to-End

**→ [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md)**

Guide de tests complet :
- Configuration WordPress
- Configuration Backend
- 7 scénarios de test détaillés
- Checklist complète
- Troubleshooting

**Temps** : 1-2 heures pour tests complets

---

### 🚢 Déployer en Production

**→ [lehiboo-ai-backend/DEPLOYMENT_GUIDE.md](lehiboo-ai-backend/DEPLOYMENT_GUIDE.md)**

3 options de déploiement :
- **Railway** (recommandé) - 5 minutes
- **Vercel** - 5 minutes
- **VPS Ubuntu** - 30 minutes

Inclut :
- Configuration production
- Sécurité
- Monitoring
- Coûts estimés

**Temps** : 5-30 minutes selon option

---

### 🛠️ Développer une Nouvelle Fonctionnalité

**→ [CONTRIBUTING.md](CONTRIBUTING.md)**

Standards et workflow :
- Code de conduite
- Standards PHP/JavaScript/CSS
- Structure Git (branches, commits)
- Process Pull Request
- Tests requis
- Documentation obligatoire

**Temps** : 20-30 minutes de lecture

---

### 🔧 Comprendre les MCP Tools

**→ [lehiboo-ai-backend/MCP_TOOLS.md](lehiboo-ai-backend/MCP_TOOLS.md)**

Documentation des 7 tools :
1. search_events
2. get_event_details
3. filter_by_age
4. check_availability
5. calculate_distance
6. suggest_itinerary
7. get_weather

Chaque tool avec :
- Description
- Paramètres
- Exemples d'utilisation
- Cas d'usage

**Temps** : 15 minutes

---

### 📊 Voir l'État du Projet

**→ [lehiboo-ai-backend/STATUS.md](lehiboo-ai-backend/STATUS.md)**

État d'avancement :
- ✅ Complété
- ⏳ En attente
- 📝 Planifié

Métriques et prochaines étapes.

**Temps** : 5 minutes

---

### 📜 Voir l'Historique des Versions

**→ [CHANGELOG.md](CHANGELOG.md)**

Historique complet :
- v0.1.0 → v1.0.0
- Toutes les features ajoutées
- Statistiques (fichiers, lignes, temps)
- Coûts estimés

**Temps** : 10 minutes

---

### 📋 Voir le Récapitulatif Complet

**→ [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)**

Résumé exécutif :
- Composants créés
- Fonctionnalités clés
- Statistiques code
- Technologies
- Coûts
- Documentation
- Prochaines évolutions

**Temps** : 15 minutes

---

### 🔐 Comprendre la Sécurité

**→ [wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md](wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md)**

Sécurité détaillée (800+ lignes) :
- Triple layer protection
- Rate limiting
- XSS/SQL/Prompt injection
- RGPD compliance
- Best practices
- Audit checklist

**Temps** : 30 minutes

---

### 🎨 Comprendre l'Interface Frontend

**→ [wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md](wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md)**

Architecture plugin (1200+ lignes) :
- Structure fichiers
- Flow conversationnel
- Components JavaScript
- Styles CSS
- Event handling
- State management

**Temps** : 30-40 minutes

---

## 🎓 Parcours de Lecture Recommandés

### Pour un Nouveau Développeur

```
1. README.md (10 min)
   ↓
2. ARCHITECTURE_OVERVIEW.md (15 min)
   ↓
3. lehiboo-ai-backend/QUICK_START.md (5 min + pratique)
   ↓
4. INTEGRATION_TESTING.md (tests)
   ↓
5. CONTRIBUTING.md (avant de coder)
```

**Temps total** : ~1h30 lecture + pratique

---

### Pour un Chef de Projet

```
1. README.md (10 min)
   ↓
2. PROJECT_SUMMARY.md (15 min)
   ↓
3. CHANGELOG.md (10 min)
   ↓
4. lehiboo-ai-backend/DEPLOYMENT_GUIDE.md (coûts & déploiement)
```

**Temps total** : ~45 minutes

---

### Pour un Testeur QA

```
1. README.md (overview)
   ↓
2. INTEGRATION_TESTING.md (guide complet)
   ↓
3. wp-content/plugins/lehiboo-ai-assistant/docs/TESTING_GUIDE.md
   ↓
4. lehiboo-ai-backend/MCP_TOOLS.md (comprendre les tools)
```

**Temps total** : ~1h lecture + 2h tests

---

### Pour un DevOps

```
1. ARCHITECTURE_OVERVIEW.md (comprendre stack)
   ↓
2. lehiboo-ai-backend/DEPLOYMENT_GUIDE.md (déploiement)
   ↓
3. wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md (sécurité)
   ↓
4. .gitignore (comprendre exclusions)
```

**Temps total** : ~1h

---

### Pour un Product Owner

```
1. README.md (overview)
   ↓
2. PROJECT_SUMMARY.md (fonctionnalités + coûts)
   ↓
3. ARCHITECTURE_OVERVIEW.md (section Flow conversation)
   ↓
4. CHANGELOG.md (ce qui a été fait)
```

**Temps total** : ~45 minutes

---

## 📂 Structure Documentation (18 fichiers)

### Racine Projet

```
📁 lehiboo_v1/
├── 📄 README.md                        ⭐ Point d'entrée principal
├── 📄 PROJECT_SUMMARY.md               📊 Résumé exécutif
├── 📄 ARCHITECTURE_OVERVIEW.md         🏗️ Architecture globale
├── 📄 IMPLEMENTATION_COMPLETE.md       ✅ Récap implémentation
├── 📄 INTEGRATION_TESTING.md           🧪 Tests E2E
├── 📄 CHANGELOG.md                     📜 Historique versions
├── 📄 CONTRIBUTING.md                  🤝 Guide contribution
├── 📄 NAVIGATION_GUIDE.md              🧭 Ce fichier
├── 📄 LICENSE                          ⚖️ Licence MIT
└── 📜 install.sh                       🚀 Installation auto
```

### Backend

```
📁 lehiboo-ai-backend/
├── 📄 README.md                        📖 Doc backend (50+ sections)
├── 📄 QUICK_START.md                   ⚡ Démarrage 5 min
├── 📄 STATUS.md                        📊 État projet
├── 📄 MCP_TOOLS.md                     🔧 Doc 7 tools
└── 📄 DEPLOYMENT_GUIDE.md              🚀 Déploiement production
```

### Plugin WordPress

```
📁 wp-content/plugins/lehiboo-ai-assistant/docs/
├── 📄 START_HERE.md                    🎯 Point d'entrée plugin
├── 📄 ARCHITECTURE.md                  🏗️ Archi plugin (1200+ lignes)
├── 📄 TESTING_GUIDE.md                 🧪 Tests plugin
├── 📄 SECURITY.md                      🔐 Sécurité (800+ lignes)
└── 📄 README_FR.md                     🇫🇷 README français
```

---

## 🔍 Index des Fonctionnalités

### Frontend
- **Interface immersive** → [wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md](wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md)
- **Charte Le Hiboo** → [wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-interface.css](wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-interface.css)
- **Event Cards** → [wp-content/plugins/lehiboo-ai-assistant/assets/js/chat-interface.js](wp-content/plugins/lehiboo-ai-assistant/assets/js/chat-interface.js)

### Backend
- **AI Service** → [lehiboo-ai-backend/src/services/ai-service.js](lehiboo-ai-backend/src/services/ai-service.js)
- **MCP Tools** → [lehiboo-ai-backend/src/mcp/tools.js](lehiboo-ai-backend/src/mcp/tools.js)
- **Weather Service** → [lehiboo-ai-backend/src/services/weather-service.js](lehiboo-ai-backend/src/services/weather-service.js)
- **Prompts YAML** → [lehiboo-ai-backend/src/prompts/system-prompt.yaml](lehiboo-ai-backend/src/prompts/system-prompt.yaml)

### Sécurité
- **Triple Layer** → [wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md](wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md)
- **Rate Limiting** → [wp-content/plugins/lehiboo-ai-assistant/includes/class-security.php](wp-content/plugins/lehiboo-ai-assistant/includes/class-security.php)
- **Authentication** → [lehiboo-ai-backend/src/middleware/auth.js](lehiboo-ai-backend/src/middleware/auth.js)

### API
- **EventList REST** → [wp-content/plugins/eventlist/includes/class-eventlist-rest-api.php](wp-content/plugins/eventlist/includes/class-eventlist-rest-api.php)
- **WordPress Service** → [lehiboo-ai-backend/src/services/wordpress-service.js](lehiboo-ai-backend/src/services/wordpress-service.js)

---

## ❓ Questions Fréquentes

### Comment installer localement ?
→ [lehiboo-ai-backend/QUICK_START.md](lehiboo-ai-backend/QUICK_START.md) ou `./install.sh`

### Comment déployer en production ?
→ [lehiboo-ai-backend/DEPLOYMENT_GUIDE.md](lehiboo-ai-backend/DEPLOYMENT_GUIDE.md)

### Comment tester le système ?
→ [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md)

### Comment ajouter un nouveau MCP Tool ?
→ [lehiboo-ai-backend/MCP_TOOLS.md](lehiboo-ai-backend/MCP_TOOLS.md) + [CONTRIBUTING.md](CONTRIBUTING.md)

### Comment modifier les prompts IA ?
→ [lehiboo-ai-backend/src/prompts/system-prompt.yaml](lehiboo-ai-backend/src/prompts/system-prompt.yaml)

### Comment personnaliser l'interface ?
→ [wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-interface.css](wp-content/plugins/lehiboo-ai-assistant/assets/css/chat-interface.css)

### Combien ça coûte en production ?
→ [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) section "Coûts" (~$90/mois pour 5k conv)

### Est-ce sécurisé ?
→ [wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md](wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md)

### Est-ce RGPD compliant ?
→ [wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md](wp-content/plugins/lehiboo-ai-assistant/docs/SECURITY.md) section RGPD

### Quelles sont les prochaines étapes ?
→ [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) section "Prochaines Évolutions"

---

## 🆘 Besoin d'Aide ?

### Documentation Non Trouvée
1. Consulter [README.md](README.md) (index général)
2. Utiliser la recherche de fichiers dans votre éditeur
3. Chercher dans [ARCHITECTURE_OVERVIEW.md](ARCHITECTURE_OVERVIEW.md)

### Question Technique
1. Consulter [CONTRIBUTING.md](CONTRIBUTING.md)
2. Voir [wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md](wp-content/plugins/lehiboo-ai-assistant/docs/ARCHITECTURE.md)
3. Contacter : dev@lehiboo.com

### Bug ou Feature Request
1. Vérifier [CHANGELOG.md](CHANGELOG.md) si déjà résolu
2. Créer une issue GitHub
3. Contacter : dev@lehiboo.com

---

## 📊 Statistiques Documentation

- **Total fichiers documentation** : 18 fichiers
- **Total lignes documentation** : ~10,000+ lignes
- **Langues** : Français
- **Formats** : Markdown (.md), YAML (.yaml)
- **Diagrammes** : ASCII art
- **Code exemples** : PHP, JavaScript, CSS, Bash

---

## ✅ Checklist Lecture Rapide

Pour bien démarrer, lisez au minimum :

- [ ] [README.md](README.md) - 10 minutes
- [ ] [lehiboo-ai-backend/QUICK_START.md](lehiboo-ai-backend/QUICK_START.md) - 5 minutes
- [ ] [ARCHITECTURE_OVERVIEW.md](ARCHITECTURE_OVERVIEW.md) - 15 minutes
- [ ] [INTEGRATION_TESTING.md](INTEGRATION_TESTING.md) - Parcourir rapidement

**Temps total minimum** : ~30 minutes de lecture

---

**Navigation Guide** - Le Hiboo AI Assistant v1.0.0

**Dernière mise à jour** : 28 Octobre 2025
