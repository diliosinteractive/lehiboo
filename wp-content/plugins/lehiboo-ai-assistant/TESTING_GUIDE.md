# Guide de Test - Le Hiboo AI Assistant

## 🎯 Objectif
Tester l'interface chat immersive en mode démo (sans backend IA).

## ✅ Prérequis
- WordPress installé et fonctionnel
- Plugin **Le Hiboo AI Assistant** activé
- Thème Le Hiboo actif

## 📋 Checklist de Test

### 1. Activation du Plugin

1. Aller dans **WP Admin → Extensions**
2. Activer **Le Hiboo AI Assistant**
3. Vérifier qu'aucune erreur n'apparaît

### 2. Configuration Initiale

1. Aller dans **WP Admin → Le Hiboo → Assistant IA**
2. **Activer le chat** : Cocher "Activer l'assistant"
3. **Sauvegarder les paramètres**

> Le backend IA n'est pas encore configuré, donc le mode démo s'activera automatiquement.

### 3. Test Frontend - Interface Immersive

#### A. Ouverture du Chat
1. Aller sur n'importe quelle page du site frontend
2. Vérifier la présence du **bouton FAB** en bas à droite avec l'image Le Hiboo
3. **Cliquer sur le bouton FAB**

**Résultat attendu** :
- ✅ Le chat s'ouvre en **plein écran** (demi-largeur sur desktop)
- ✅ Un **backdrop semi-transparent** apparaît derrière
- ✅ Le panel glisse depuis la **droite**
- ✅ Le message d'accueil s'affiche avec les quick chips

#### B. Design et Couleurs
Vérifier que :
- ✅ L'**orange Le Hiboo** (#FF601F) est utilisé pour les boutons
- ✅ La **police Montserrat** est chargée et appliquée
- ✅ L'**image Le Hiboo** apparaît dans l'avatar de l'assistant
- ✅ Le **bandeau jaune "Mode Démo"** est visible

#### C. Responsive
1. **Desktop** : Le chat prend 50% de la largeur
2. **Tablet** : Le chat prend 60% de la largeur
3. **Mobile** : Le chat prend 100% de la largeur (plein écran)

### 4. Test du Flow Conversationnel Démo

#### Étape 1 : Type de Groupe
**Action** : Cliquer sur un quick chip (ex: "💑 En couple")

**Résultat attendu** :
- ✅ Le message utilisateur s'affiche à droite
- ✅ L'assistant répond "Super ! Une activité **en couple** 👍"
- ✅ De nouveaux quick chips apparaissent (tranches d'âge)

#### Étape 2 : Âge
**Action** : Cliquer sur "25-35 ans" ou taper "j'ai 30 ans"

**Résultat attendu** :
- ✅ L'assistant demande les dates
- ✅ Quick chips avec options de dates
- ✅ Une **alerte météo** apparaît en haut

#### Étape 3 : Dates
**Action** : Cliquer sur "📅 Ce week-end"

**Résultat attendu** :
- ✅ L'assistant demande le type d'activité
- ✅ Quick chips avec catégories (Sport, Culture, Gastronomie...)

#### Étape 4 : Type d'Activité
**Action** : Cliquer sur "⚽ Sportif" ou taper "quelque chose de sportif"

**Résultat attendu** :
- ✅ L'assistant affiche "🔍 Parfait ! Voici mes meilleures recommandations"
- ✅ **5 event cards** s'affichent avec :
  - Image
  - Titre
  - Prix
  - Date et heure
  - Localisation
  - Durée
  - Note/avis
  - Badges (Indoor, Sport, etc.)
  - Boutons "Voir détails" et "Réserver"

### 5. Test des Interactions

#### Quick Chips
- ✅ Cliquables
- ✅ S'ajoutent au chat quand cliqués
- ✅ Disparaissent après clic

#### Textarea
- ✅ Auto-resize quand on tape
- ✅ Compteur de caractères (max 2000)
- ✅ Bouton envoi activé/désactivé selon contenu
- ✅ Enter pour envoyer (Shift+Enter pour nouvelle ligne)

#### Indicateur de Typing
- ✅ Apparaît quand l'assistant "réfléchit"
- ✅ Animation 3 points
- ✅ Disparaît quand réponse arrive

#### Fermeture
Tester **3 méthodes** :
1. ✅ Cliquer sur le backdrop
2. ✅ Cliquer sur le bouton "×" en haut
3. ✅ Appuyer sur **ESC**

**Résultat** : Le chat se ferme avec animation fluide

### 6. Test de Sécurité (Rate Limiting)

**Action** : Envoyer 11 messages rapidement

**Résultat attendu** :
- ✅ Après 10 messages, un message d'erreur apparaît
- ✅ "Trop de messages envoyés. Veuillez attendre X secondes."
- ✅ Le bouton d'envoi reste désactivé

### 7. Test de Persistance

1. Envoyer quelques messages
2. Fermer le chat
3. Actualiser la page
4. Rouvrir le chat

**Résultat attendu** :
- ✅ L'historique de conversation est conservé (localStorage)
- ✅ Le contexte utilisateur est restauré

### 8. Test Analytics (Admin)

1. Aller dans **WP Admin → PhpMyAdmin**
2. Chercher la table `wp_lehiboo_conversations`

**Résultat attendu** :
- ✅ Les conversations sont trackées
- ✅ Données anonymisées (tranche d'âge, pas âge exact)
- ✅ Compteur de messages

## 🐛 Problèmes Connus & Solutions

### Le chat ne s'affiche pas
**Solutions** :
1. Vérifier que le plugin est **activé**
2. Aller dans les settings et cocher "Activer l'assistant"
3. Vider le cache du navigateur (Ctrl+Shift+R)
4. Vérifier la console JavaScript (F12)

### Les styles ne s'appliquent pas
**Solutions** :
1. Vérifier que Montserrat charge : ouvrir DevTools → Network → Fonts
2. Vider le cache WordPress
3. Régénérer les CSS

### Le backdrop ne fonctionne pas
**Solution** :
- Vérifier que le JavaScript n'a pas d'erreur (F12 → Console)

### Les event cards ne s'affichent pas
**Solution** :
- En mode démo, il faut compléter le flow jusqu'à l'étape "recommendations"
- Vérifier que le stage `recommendations` est bien atteint

## 📊 Métriques de Succès

- ✅ **Temps de chargement** : < 1s pour l'ouverture du chat
- ✅ **Responsive** : Fonctionne sur mobile, tablette, desktop
- ✅ **Accessibilité** : Navigation au clavier possible
- ✅ **UX** : Flow conversationnel fluide et naturel

## 🚀 Prochaines Étapes

Une fois tous les tests validés :
1. Configurer le **backend Node.js** (voir IMPLEMENTATION_GUIDE.md)
2. Connecter à **OpenRouter** pour l'IA réelle
3. Implémenter les **MCP Tools** pour accéder aux données EventList
4. Intégrer l'**API météo** OpenWeatherMap
5. Passer en **production**

## 📝 Rapport de Test

| Fonctionnalité | Status | Notes |
|----------------|--------|-------|
| Activation plugin | ⬜ | |
| Interface immersive | ⬜ | |
| Couleurs Le Hiboo | ⬜ | |
| Police Montserrat | ⬜ | |
| Flow conversationnel | ⬜ | |
| Event cards | ⬜ | |
| Quick chips | ⬜ | |
| Rate limiting | ⬜ | |
| Responsive | ⬜ | |
| Fermeture (ESC/backdrop/×) | ⬜ | |
| Analytics tracking | ⬜ | |
| Persistance localStorage | ⬜ | |

---

**Testeur** : _____________
**Date** : _____________
**Version** : 1.0.0
**Status global** : ⬜ PASS / ⬜ FAIL
