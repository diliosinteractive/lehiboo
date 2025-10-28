# 📚 Index des Guides - Le Hiboo AI Backend V2

**Version :** 2.0.0
**Date :** 2025-10-29
**Migration :** OpenRouter → OpenAI GPT-4o

---

## 🚀 Pour Commencer

### [QUICK_START_V2.md](./QUICK_START_V2.md) ⭐ START HERE
**Temps :** 5 minutes | **Lignes :** 320

Le guide le plus rapide pour démarrer avec V2.

**Contenu :**
- Configuration OpenAI en 1 minute
- Configuration WordPress API Key
- Démarrage Docker OU local
- Tests de validation immédiate
- Checklist de vérification
- Debugging rapide

**Commandes clés :**
```bash
# Docker (recommandé)
docker-compose up -d --build
curl http://localhost:3004/health

# Ou local
npm run dev
curl http://localhost:3000/health
```

---

## 📖 Documentation Principale

### [README.md](./README.md)
**Temps :** 15 minutes | **Lignes :** 484

Vue d'ensemble complète du projet V2.

**Contenu :**
- Nouveautés V2 (migration OpenRouter → OpenAI)
- Installation et configuration
- Structure du projet
- API Endpoints détaillés
- Variables d'environnement
- Modèles OpenAI disponibles
- Intégration WordPress
- Déploiement production
- Roadmap Sprint 1, 2, 3

**À lire si :**
- Nouveau sur le projet
- Besoin de comprendre l'architecture globale
- Cherchez les endpoints API

---

## 🐳 Déploiement Docker

### [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md) ⭐ DOCKER
**Temps :** 10 minutes | **Lignes :** 510

Guide complet de déploiement Docker pour V2.

**Contenu :**
- Configuration `.env.production` pour OpenAI
- Démarrage rapide Docker (2 commandes)
- Architecture Docker V2 détaillée
- Déploiement production Plesk
- Configuration reverse proxy
- Maintenance et monitoring
- Troubleshooting Docker spécifique V2
- Coûts OpenAI vs OpenRouter

**Commandes clés :**
```bash
# Local
docker-compose up -d --build

# Production Plesk
./scripts/deploy.sh lehiboo.dilios.me juba
```

**À lire si :**
- Vous déployez en production sur Plesk
- Vous utilisez Docker
- Vous cherchez le troubleshooting Docker

---

### [DOCKER_SUMMARY.md](../DOCKER_SUMMARY.md)
**Temps :** 5 minutes | **Lignes :** 442

Résumé de la configuration Docker initiale (V1 - OpenRouter).

**Note :** Ce guide est pour référence historique. Utilisez [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md) pour V2.

---

## 🧪 Tests et Validation

### [TESTING_V2.md](./TESTING_V2.md)
**Temps :** 20 minutes | **Lignes :** 320

Guide de test complet avec tests unitaires et d'intégration.

**Contenu :**
- Tests unitaires des tools (collectUserProfile, searchEvents)
- Tests d'intégration backend
- Test conversation complète (3 messages)
- Debugging rapide des problèmes courants
- Checklist de validation avant production
- Métriques de performance attendues
- Procédure rollback d'urgence

**À lire si :**
- Vous voulez tester le système avant déploiement
- Vous rencontrez des problèmes
- Vous cherchez le debugging

**Commandes clés :**
```bash
# Test health check
curl http://localhost:3004/health

# Test conversation 3 messages
curl -X POST http://localhost:3004/chat ...
```

---

## 📊 État et Migration

### [STATUS_V2.md](./STATUS_V2.md)
**Temps :** 15 minutes | **Lignes :** 454

État actuel détaillé du système V2.

**Contenu :**
- Implémentation complète (checklist)
- Architecture active en détail
- Performance attendue (métriques)
- Tests à effectuer
- Checklist validation finale
- Points d'attention (API keys, etc.)
- Rollback d'urgence
- Prochains sprints

**À lire si :**
- Vous voulez savoir exactement ce qui est implémenté
- Vous cherchez les métriques de performance
- Vous préparez le déploiement production

---

### [README_V2_MIGRATION.md](./README_V2_MIGRATION.md)
**Temps :** 10 minutes | **Lignes :** 245

Guide de migration de V1 (OpenRouter) vers V2 (OpenAI).

**Contenu :**
- Pourquoi la migration V2
- Changements principaux
- Migration étape par étape
- Exemple conversation 3 messages
- Debugging V2
- Rollback vers V1 si nécessaire

**À lire si :**
- Vous voulez comprendre pourquoi V2
- Vous migrez depuis V1
- Vous cherchez les différences V1 vs V2

---

## 📋 Historique et Roadmap

### [CHANGELOG_V2.md](./CHANGELOG_V2.md)
**Temps :** 20 minutes | **Lignes :** 600+

Documentation complète de tous les changements V2.

**Contenu :**
- Objectif de la migration
- Changements majeurs détaillés
- Nouveaux fichiers créés
- Fichiers modifiés
- Métriques avant/après
- Flow de conversation type
- Checklist avant production

**À lire si :**
- Vous voulez tous les détails de la V2
- Vous cherchez un fichier spécifique
- Vous documentez le projet

---

### [ROADMAP_REFONTE.md](./ROADMAP_REFONTE.md)
**Temps :** 30 minutes | **Lignes :** 1000+

Roadmap complète de la refonte sur 3 sprints.

**Contenu :**
- Diagnostic du système V1
- Architecture inspirée de ZENELI
- Sprint 1 : System Prompt + Tools (✅ Terminé)
- Sprint 2 : Persistance conversations
- Sprint 3 : Features avancées (météo, itinéraires)
- Code snippets complets
- Métriques attendues

**À lire si :**
- Vous voulez comprendre la vision complète
- Vous planifiez les prochains sprints
- Vous cherchez l'architecture détaillée

---

## 🔧 Cas d'Usage Rapide

### Je veux démarrer rapidement (5 min)
👉 [QUICK_START_V2.md](./QUICK_START_V2.md)

### Je veux déployer en production (10 min)
👉 [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md)

### Je veux tester le système (20 min)
👉 [TESTING_V2.md](./TESTING_V2.md)

### Je cherche un problème (debugging)
👉 [TESTING_V2.md](./TESTING_V2.md) - Section Debugging

### Je veux comprendre l'architecture
👉 [STATUS_V2.md](./STATUS_V2.md) - Section Architecture
👉 [README.md](./README.md) - Section Structure

### Je veux migrer de V1 à V2
👉 [README_V2_MIGRATION.md](./README_V2_MIGRATION.md)

### Je veux voir tous les changements
👉 [CHANGELOG_V2.md](./CHANGELOG_V2.md)

### Je veux planifier les prochains sprints
👉 [ROADMAP_REFONTE.md](./ROADMAP_REFONTE.md)

---

## 📁 Structure des Guides

```
lehiboo-ai-backend/
├── README.md                      📖 Vue d'ensemble V2
├── GUIDES_INDEX.md                📚 Index (ce fichier)
│
├── 🚀 Démarrage Rapide
│   ├── QUICK_START_V2.md          ⭐ Start here (5 min)
│   └── STATUS_V2.md               📊 État actuel (15 min)
│
├── 🐳 Docker
│   ├── DOCKER_V2_DEPLOYMENT.md    ⭐ Guide Docker V2 (10 min)
│   └── docker-compose.yml         ⚙️ Configuration
│
├── 🧪 Tests
│   └── TESTING_V2.md              🧪 Tests complets (20 min)
│
├── 📋 Migration & Historique
│   ├── README_V2_MIGRATION.md     🔄 Guide migration (10 min)
│   ├── CHANGELOG_V2.md            📝 Tous les changements (20 min)
│   └── ROADMAP_REFONTE.md         🗺️ Roadmap 3 sprints (30 min)
│
└── 📚 Documentation Technique
    ├── src/prompts/system-prompt-v2.md    🧠 Prompt expert (880 lignes)
    ├── src/tools/collect-user-profile.js  🛠️ Tool collecte (320 lignes)
    └── src/tools/search-events.js         🔍 Tool recherche (480 lignes)
```

---

## 🎯 Ordre de Lecture Recommandé

### Pour Développeur qui Découvre le Projet
1. [README.md](./README.md) - Vue d'ensemble (15 min)
2. [QUICK_START_V2.md](./QUICK_START_V2.md) - Démarrer localement (5 min)
3. [STATUS_V2.md](./STATUS_V2.md) - Comprendre l'état actuel (15 min)
4. [TESTING_V2.md](./TESTING_V2.md) - Tester le système (20 min)

**Temps total :** ~1h

---

### Pour DevOps qui Déploie en Production
1. [QUICK_START_V2.md](./QUICK_START_V2.md) - Tester localement (5 min)
2. [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md) - Déployer sur Plesk (10 min)
3. [TESTING_V2.md](./TESTING_V2.md) - Valider le déploiement (20 min)
4. [STATUS_V2.md](./STATUS_V2.md) - Checklist finale (15 min)

**Temps total :** ~50 min

---

### Pour Product Owner qui Veut Comprendre V2
1. [README_V2_MIGRATION.md](./README_V2_MIGRATION.md) - Pourquoi V2 (10 min)
2. [CHANGELOG_V2.md](./CHANGELOG_V2.md) - Changements détaillés (20 min)
3. [STATUS_V2.md](./STATUS_V2.md) - Métriques de performance (15 min)
4. [ROADMAP_REFONTE.md](./ROADMAP_REFONTE.md) - Vision complète (30 min)

**Temps total :** ~1h15

---

## 💡 Tips

### Recherche Rapide
Utilisez Ctrl+F / Cmd+F dans chaque guide pour trouver rapidement :
- Mots-clés : "OpenAI", "Docker", "tools", "budget", etc.
- Commandes : "curl", "docker-compose", "npm", etc.
- Codes d'erreur : "401", "404", "502", etc.

### Favoris à Marquer
- [QUICK_START_V2.md](./QUICK_START_V2.md) - Référence quotidienne
- [TESTING_V2.md](./TESTING_V2.md) - Debugging fréquent
- [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md) - Déploiements

---

## 🆘 Support

### En Cas de Problème
1. **Chercher dans** [TESTING_V2.md](./TESTING_V2.md) - Section Debugging
2. **Vérifier** [STATUS_V2.md](./STATUS_V2.md) - Points d'attention
3. **Consulter** [DOCKER_V2_DEPLOYMENT.md](./DOCKER_V2_DEPLOYMENT.md) - Troubleshooting Docker

### Rollback d'Urgence
Voir [TESTING_V2.md](./TESTING_V2.md) - Section Rollback

---

**Version Guides :** 2.0.0
**Dernière mise à jour :** 2025-10-29
**Total pages documentation :** ~4000 lignes
