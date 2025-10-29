# Fix Quick Chips - Guide de Test

**Date :** 2025-10-29
**Problèmes résolus :** Quick chips qui ne s'affichaient pas / ne changeaient pas

---

## 🔧 Changements Effectués

### 1. Backend (`ai-service-v2.js`)
✅ Génération automatique des quick chips avec champ `type`
✅ Quick chips changent selon userContext (groupType → activityType → dates → budget)
✅ Fix valeurs: `famille` → `family`, `amis` → `friends`

### 2. Frontend (`chat-interface.js`)
✅ Mise à jour automatique du userContext au clic sur quick chip
✅ Greeting naturel généré par le backend (plus de message hardcodé)
✅ Quick chips dynamiques affichés à chaque réponse

---

## 🚀 Tester le Fix

### Étape 1 : Redémarrer le Backend

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/lehiboo-ai-backend

# Si Docker
docker-compose down
docker-compose up -d --build

# Ou si local
npm run dev
```

---

### Étape 2 : Ouvrir le Chat Frontend

1. Ouvrir WordPress : http://lehiboo.dilios.me
2. Ouvrir le chat Hedwige (bouton orange en bas à droite)
3. Ouvrir la **Console du navigateur** (F12 → Console)

---

### Étape 3 : Tester le Flow Complet

#### Message 1 - Greeting Naturel

**Attendu :**
- Hedwige se présente (message naturel, pas toujours le même)
- Quick chips affichés en bas :
  ```
  🧍 Solo | 💑 En couple | 👨‍👩‍👧 En famille | 👥 Entre amis
  ```

**Console devrait montrer :**
```
Incoming message: {...quickChips: [{text: "🧍 Solo", value: "solo", type: "groupType"}, ...]}
```

✅ **Vérifier :** Les boutons ont bien un champ `type: "groupType"`

---

#### Message 2 - Clic sur "En couple"

**Action :** Cliquer sur le bouton "💑 En couple"

**Attendu :**
- Message utilisateur : "couple"
- Hedwige confirme le choix
- **Quick chips changent** :
  ```
  🎭 Culture | ⚽ Sport | 🍷 Gastronomie | 🌳 Nature | 💆 Détente
  ```

**Console devrait montrer :**
```
Updated userContext.groupType = couple
Incoming message: {...quickChips: [{text: "🎭 Culture", value: "culture", type: "activityType"}, ...]}
```

✅ **Vérifier :**
- userContext.groupType est mis à jour
- Les nouveaux boutons ont `type: "activityType"`

---

#### Message 3 - Clic sur "Culture"

**Action :** Cliquer sur le bouton "🎭 Culture"

**Attendu :**
- Message utilisateur : "culture"
- Hedwige confirme
- **Quick chips changent encore** :
  ```
  📅 Ce weekend | 📅 Prochain weekend | 📅 Dates précises | 📅 Flexible
  ```

**Console devrait montrer :**
```
Updated userContext.activityType = culture
Incoming message: {...quickChips: [{text: "📅 Ce weekend", value: "thisWeekend", type: "dates"}, ...]}
```

✅ **Vérifier :**
- userContext.activityType est mis à jour
- Les nouveaux boutons ont `type: "dates"`

---

#### Message 4 - Clic sur "Ce weekend"

**Action :** Cliquer sur "📅 Ce weekend"

**Attendu :**
- Message utilisateur : "thisWeekend"
- Hedwige confirme
- **Quick chips changent** :
  ```
  💰 Moins de 20€ | 💰 20-50€ | 💰 50-100€ | 💰 Plus de 100€
  ```

**Console :**
```
Updated userContext.dates = thisWeekend
Incoming message: {...quickChips: [{text: "💰 Moins de 20€", value: "20", type: "budgetMax"}, ...]}
```

✅ **Vérifier :** type: "budgetMax"

---

#### Message 5 - Clic sur "50-100€"

**Action :** Cliquer sur "💰 50-100€"

**Attendu :**
- Message utilisateur : "100"
- Hedwige annonce la recherche
- **Quick chips changent** :
  ```
  🔍 Afficher les résultats | 🔄 Modifier mes critères
  ```
- **OU** : Affichage direct des événements

**Console :**
```
Updated userContext.budgetMax = 100
Incoming message: {...events: [...], quickChips: [{text: "🔍 Afficher les résultats", ...}]}
```

✅ **Vérifier :**
- userContext.budgetMax = "100"
- Events retournés par le backend
- type: "action"

---

## ✅ Checklist de Validation

### Backend
- [ ] Backend redémarré (docker-compose up -d --build)
- [ ] Logs backend montrent "OpenAI connection successful"
- [ ] Endpoint /health répond OK

### Quick Chips Dynamiques
- [ ] Message 1 : Quick chips groupType affichés
- [ ] Clic "En couple" → Quick chips activityType affichés
- [ ] Clic "Culture" → Quick chips dates affichés
- [ ] Clic "Ce weekend" → Quick chips budgetMax affichés
- [ ] Clic "50-100€" → Quick chips action OU events affichés

### Console Navigateur
- [ ] "Updated userContext.groupType = couple"
- [ ] "Updated userContext.activityType = culture"
- [ ] "Updated userContext.dates = thisWeekend"
- [ ] "Updated userContext.budgetMax = 100"
- [ ] Chaque message backend contient quickChips avec field "type"

### Greeting Naturel
- [ ] Message de greeting varie (pas toujours identique)
- [ ] Hedwige se présente mais message pas figé
- [ ] Pas de message "Bonjour ! Je suis Hedwige..." répété à l'identique

---

## 🐛 Debugging

### Quick Chips ne s'affichent pas

**Vérifier :**
```js
// Console navigateur
console.log(response.quickChips);
// Devrait afficher : [{text: "...", value: "...", type: "..."}, ...]
```

**Si undefined :**
- Backend pas redémarré → `docker-compose restart`
- Erreur backend → Voir logs `docker-compose logs -f`

---

### Quick Chips ne changent pas

**Vérifier :**
```js
// Console navigateur
console.log(chatInterface.state.userContext);
// Devrait afficher : {groupType: "couple", activityType: "culture", ...}
```

**Si vide :**
- Cache navigateur → Hard refresh (Ctrl+Shift+R)
- Vérifier que code frontend est bien chargé

---

### userContext pas mis à jour

**Vérifier dans Console :**
```
Updated userContext.groupType = couple
```

**Si absent :**
- Le chip n'a pas de champ `type`
- Vérifier backend retourne bien `{text, value, type}`
- Hard refresh navigateur

---

### Events pas retournés

**Backend doit :**
1. collectUserProfile → completeness 100%
2. searchEvents → appelé automatiquement

**Vérifier logs backend :**
```
Tools called by AI: [
  {name: "collectUserProfile", ...},
  {name: "searchEvents", ...}
]
```

**Si searchEvents pas appelé :**
- Tool pas complete (< 100%)
- Vérifier que tous les champs sont remplis
- Vérifier system prompt v2 chargé

---

## 📊 Résultat Attendu Final

### Flow Complet (5 clics)

```
1. Ouvrir chat
   → Quick chips: Solo | Couple | Famille | Amis

2. Clic "En couple"
   → Quick chips: Culture | Sport | Gastronomie | Nature | Détente

3. Clic "Culture"
   → Quick chips: Ce weekend | Prochain weekend | Dates précises | Flexible

4. Clic "Ce weekend"
   → Quick chips: Moins de 20€ | 20-50€ | 50-100€ | Plus de 100€

5. Clic "50-100€"
   → Events affichés OU Quick chips: Afficher résultats | Modifier critères
```

**Total : 5 clics pour avoir des résultats** ✅

**vs Avant : 10-15 messages texte** ❌

---

## 🎯 Métriques de Succès

| Métrique | Avant | Après | Status |
|----------|-------|-------|--------|
| Clics pour résultats | 10-15 messages | **5 clics** | ✅ |
| Quick chips dynamiques | Non (toujours identiques) | **Oui** | ✅ |
| Greeting naturel | Hardcodé, répétitif | **Varié** | ✅ |
| userContext mis à jour | Non | **Oui** | ✅ |
| Type tracking | Non | **Oui** (groupType, etc.) | ✅ |

---

## 🚀 Déploiement Production

Une fois validé en local :

```bash
# 1. Pousser les changements
git push

# 2. Déployer sur serveur Plesk
./scripts/deploy.sh lehiboo.dilios.me juba

# 3. Tester sur production
# https://lehiboo.dilios.me
```

---

**Version :** 2.0.1
**Date :** 2025-10-29
**Status :** ✅ Prêt pour Tests
