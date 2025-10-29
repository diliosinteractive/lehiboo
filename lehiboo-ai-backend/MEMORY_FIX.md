# Fix Mémoire Conversation - Guide de Test

**Date :** 2025-10-29
**Problème résolu :** L'IA reposait les mêmes questions malgré les réponses données

---

## 🔧 Problème Original

```
User: solo
IA: Pour commencer, c'est pour qui ? (solo/couple/famille/amis)

User: sport
IA: C'est pour quel type de groupe ? ❌ REDEMANDE

User: solo, 40 ans, valenciennes, ce weekend
IA: Quel âge avez-vous ? ❌ REDEMANDE ENCORE
```

**Cause :** Le `userContext` n'était pas injecté dans les messages envoyés à l'IA.

---

## ✅ Solution Implémentée

### Injection du userContext dans buildMessages()

```js
function buildMessages(message, history, userContext) {
  // Si userContext existe, l'injecter dans le message
  if (userContext && Object.keys(userContext).length > 0) {
    const contextInfo = Object.entries(userContext)
      .map(([key, value]) => `${key}: ${value}`)
      .join(', ');

    userMessage = `[CONTEXT: ${contextInfo}]\n\n${message}`;
  }
}
```

### Exemple de message reçu par l'IA

**Message 1 :**
```
User: "Bonjour"
IA reçoit: "Bonjour"
Context: (vide)
```

**Message 2 (après clic "En couple") :**
```
User: "couple"
IA reçoit: "[CONTEXT: groupType: couple]\n\nculture"
Context: {groupType: "couple"}
```

**Message 3 :**
```
User: "culture"
IA reçoit: "[CONTEXT: groupType: couple, activityType: culture]\n\nce weekend"
Context: {groupType: "couple", activityType: "culture"}
```

---

## 🧪 Tests à Effectuer

### Test 1 : Mémoire Basique

**Flow :**
1. Ouvrir chat
2. Cliquer "En couple"
3. IA doit dire : "Super ! En couple ✓"
4. Cliquer "Culture"
5. IA doit dire : "Activité culturelle pour 2 personnes" ✅ (pas redemander groupType)

**Attendu :**
- L'IA mentionne "couple" et "culture" dans sa réponse
- L'IA ne redemande JAMAIS groupType après l'avoir reçu

---

### Test 2 : Mémoire Multi-Infos

**Flow :**
1. Cliquer "En couple"
2. Taper : "40 ans, Valenciennes, ce weekend, sport, 50€"
3. IA doit extraire TOUTES les infos
4. IA ne doit redemander AUCUNE des 6 infos

**Attendu :**
```
IA: "Parfait ! Activité sportive pour 2 personnes à Valenciennes ce weekend,
     budget 50€. Je cherche les meilleures options !"
```

**Logs Console (F12) :**
```
[CONTEXT: groupType: couple, age: 40, location: {...}, dates: {...}, activityType: sport, budgetMax: 50]
```

---

### Test 3 : Continuation

**Flow :**
1. Cliquer "En couple"
2. Cliquer "Culture"
3. Taper : "Paris"
4. IA doit se souvenir de "couple" ET "culture"

**Attendu :**
```
IA: "Culture à Paris pour 2 personnes. Quand souhaitez-vous y aller ?"
```

**PAS :**
```
IA: "C'est pour qui ?" ❌ ÉCHEC
IA: "Quel type d'activité ?" ❌ ÉCHEC
```

---

## 🔍 Debugging

### Vérifier que userContext est envoyé

**Console navigateur (F12) :**
```js
// Dans Network tab, chercher requête POST /chat
// Body devrait contenir:
{
  "message": "culture",
  "conversationId": "...",
  "userContext": {
    "groupType": "couple"  // ← DOIT ÊTRE PRÉSENT
  }
}
```

### Vérifier que backend reçoit le context

**Logs backend :**
```bash
docker-compose logs -f | grep CONTEXT

# Devrait afficher:
info: Chat request received {
  conversationId: 'xxx',
  userContext: { groupType: 'couple' }  // ← DOIT ÊTRE PRÉSENT
}
```

### Vérifier que l'IA reçoit le context

**Logs backend :**
```
debug: Message sent to AI: "[CONTEXT: groupType: couple]\n\nculture"
```

---

## ✅ Checklist de Validation

### Mémoire Fonctionne
- [ ] L'IA ne redemande pas groupType après l'avoir reçu
- [ ] L'IA ne redemande pas age après l'avoir reçu
- [ ] L'IA ne redemande pas location après l'avoir reçu
- [ ] L'IA ne redemande pas dates après l'avoir reçu
- [ ] L'IA ne redemande pas activityType après l'avoir reçu
- [ ] L'IA ne redemande pas budgetMax après l'avoir reçu

### userContext Persisté
- [ ] Console montre userContext enrichi à chaque message
- [ ] Backend logs montrent "Updated userContext.xxx = yyy"
- [ ] Network tab montre userContext dans request body

### Conversation Fluide
- [ ] 5 clics maximum pour avoir des résultats
- [ ] Chaque réponse de l'IA construit sur la précédente
- [ ] L'IA confirme les infos reçues ("couple ✓", "culture ✓")

---

## 📊 Métriques Attendues

| Test | Avant | Après | Status |
|------|-------|-------|--------|
| Redemande groupType | Oui ❌ | Non ✅ | Fixed |
| Redemande age | Oui ❌ | Non ✅ | Fixed |
| Redemande location | Oui ❌ | Non ✅ | Fixed |
| Messages pour résultats | 10-15 | **5** | ✅ |
| userContext persisté | Non | **Oui** | ✅ |

---

## 🐛 Problèmes Courants

### IA redemande quand même

**Cause :** Backend pas redémarré

**Solution :**
```bash
docker-compose down
docker-compose up -d --build
```

---

### userContext vide dans logs

**Cause :** Frontend ne met pas à jour userContext

**Solution :** Vérifier que le fix Quick Chips est bien appliqué
```js
// Dans chat-interface.js
if (chip.type && chip.value) {
  this.state.userContext[chip.type] = chip.value;  // ← DOIT ÊTRE PRÉSENT
}
```

---

### [CONTEXT: ...] visible dans chat

**Cause :** Normale ! C'est injecté dans le message mais l'IA n'est pas censée le montrer

**Solution :** Ajouter instruction dans system prompt :
```
Ne JAMAIS afficher "[CONTEXT: ...]" dans tes réponses.
C'est une info interne pour toi uniquement.
```

---

## 🚀 Déploiement

Une fois validé localement :

```bash
# 1. Pousser les changements (déjà fait)
git push

# 2. Déployer sur serveur
./scripts/deploy.sh lehiboo.dilios.me juba

# 3. Tester sur production
# https://lehiboo.dilios.me
```

---

## 📝 Exemple Conversation Idéale

```
User: [Ouvre chat]
IA: Bonjour ! Je suis Hedwige 🦉. C'est pour qui ?
    [Solo | Couple | Famille | Amis]

User: [Clic "Couple"]
IA: Super ! En couple. Quel type d'activité ?
    [Culture | Sport | Gastronomie | Nature | Détente]

User: [Clic "Culture"]
IA: Activité culturelle pour 2 personnes. Quand ?
    [Ce weekend | Prochain weekend | Dates précises | Flexible]

User: [Clic "Ce weekend"]
IA: Ce weekend à quelle ville ?

User: "Paris"
IA: Paris ce weekend pour 2 personnes, activité culturelle. Budget max ?
    [Moins de 20€ | 20-50€ | 50-100€ | Plus de 100€]

User: [Clic "50-100€"]
IA: 🔍 Hedwige a trouvé 5 activités culturelles à Paris...
    [Events affichés]
```

**Total : 5 interactions, aucune question répétée** ✅

---

**Version :** 2.0.1
**Date :** 2025-10-29
**Status :** ✅ Prêt pour Tests
