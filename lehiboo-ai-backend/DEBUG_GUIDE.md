# 🔍 Guide de Debug - Conversation Flow

## 🎯 Objectif

Comprendre pourquoi l'IA redemande les infos déjà collectées et n'appelle pas les tools.

## 📋 Checklist de diagnostic

### 1. Le conversationId persiste-t-il ?

**Où regarder** : Console navigateur (F12)

```
🔵 [DEBUG FRONTEND] Initial conversationId: conv_1730192830123_abc123
🔵 [DEBUG FRONTEND] ConversationId après load: conv_1730192830123_abc123
🔵 [DEBUG FRONTEND] Sending to backend: {conversationId: "conv_1730192830123_abc123"}
```

✅ **BON** : Le conversationId est identique à chaque message
❌ **MAUVAIS** : Le conversationId change entre messages → chaque message crée une nouvelle conversation

### 2. L'historique est-il envoyé au backend ?

**Où regarder** : Console navigateur + logs backend

**Console navigateur** :
```
🔵 [DEBUG FRONTEND] Sending to backend: {
  historyLength: 4,  ← Devrait augmenter à chaque message
  ...
}
```

**Logs backend** :
```bash
docker-compose logs -f backend | grep "DEBUG"
```

Chercher :
```
🔵 [DEBUG] History received {historyCount: 4, history: [...]}
```

✅ **BON** : historyLength augmente (0 → 2 → 4 → 6...)
❌ **MAUVAIS** : historyLength reste à 0 → l'historique n'est pas envoyé

### 3. Le backend reçoit-il le userContext ?

**Logs backend** :
```
🔵 [DEBUG] UserContext received {userContext: {groupType: 'solo', age: 25}}
```

OU

```
⚠️  [DEBUG] NO USERCONTEXT - Starting fresh
```

✅ **BON** : Le userContext s'enrichit à chaque message
❌ **MAUVAIS** : "NO USERCONTEXT" à chaque fois → le userContext n'est pas persisté

### 4. L'IA reçoit-elle l'historique complet ?

**Logs backend** :
```
🔵 [DEBUG] Messages to OpenAI {
  messagesCount: 5,  ← Devrait augmenter
  messages: [
    {role: 'user', contentPreview: 'Solo'},
    {role: 'assistant', contentPreview: 'Pour pouvoir...'},
    {role: 'user', contentPreview: '20 ans, Valenciennes...'},
    ...
  ]
}
```

✅ **BON** : messagesCount augmente, les messages passés sont présents
❌ **MAUVAIS** : messagesCount = 1 à chaque fois → l'IA ne voit qu'un message

### 5. L'IA appelle-t-elle les tools ?

**Logs backend** :
```
info: AI response generated {
  toolCallsCount: 1  ← IMPORTANT
}
```

ET

```
info: Tools called by AI {
  tools: [{name: 'collectUserProfile', argsPreview: '{"groupType":"solo"...'}]
}
```

✅ **BON** : toolCallsCount > 0, on voit "collectUserProfile" appelé
❌ **MAUVAIS** : toolCallsCount = 0 → l'IA ignore les tools

## 🔧 Commandes utiles

### Voir tous les logs backend en temps réel
```bash
docker-compose logs -f backend
```

### Filtrer seulement les logs DEBUG
```bash
docker-compose logs -f backend | grep "DEBUG"
```

### Filtrer les warnings
```bash
docker-compose logs -f backend | grep "⚠️"
```

### Voir les dernières 100 lignes
```bash
docker-compose logs --tail=100 backend
```

### Restart backend et suivre les logs
```bash
docker-compose restart backend && docker-compose logs -f backend
```

## 📊 Scénarios de test

### Test 1 : Message simple
```
1. Ouvre le chat (refresh page pour nouveau conversationId)
2. Clique "Solo"
3. Regarde les logs
```

**Attendu** :
```
Frontend:
- conversationId généré
- historyLength: 0
- Envoi: {message: "Solo"}

Backend:
- History received: historyCount = 0 (normal, premier message)
- Messages to OpenAI: messagesCount = 1
- toolCallsCount = 1
- Tools called: collectUserProfile

Frontend:
- Response: userContext = {groupType: 'solo'}
```

### Test 2 : Second message
```
4. Tape "Sport"
5. Regarde les logs
```

**Attendu** :
```
Frontend:
- Même conversationId
- historyLength: 2 (Solo + Réponse IA)
- userContext contient {groupType: 'solo'}

Backend:
- History received: historyCount = 2
- UserContext received: {groupType: 'solo'}
- Messages to OpenAI: messagesCount = 3 (history + new message)
- [CONTEXT: groupType: solo] visible dans le message
- toolCallsCount = 1
- Tools called: collectUserProfile avec activityType
```

### Test 3 : Message complet
```
1. Refresh page (nouveau conversationId)
2. Clique "Solo"
3. Tape "J'ai 20 ans, à Valenciennes, ce weekend, j'aime le sport, pas de budget"
4. Regarde les logs
```

**Attendu** :
```
Backend:
- toolCallsCount = 1
- collectUserProfile appelé avec 5 champs:
  {age: 20, location: {city: 'Valenciennes'}, dates: {type: 'thisWeekend'},
   activityType: 'sport', budgetMax: 150}
- Si completeness = 100% → searchEvents appelé aussi (toolCallsCount = 2)
```

## 🚨 Problèmes courants

### Problème 1 : conversationId change à chaque message
**Symptôme** :
```
Message 1: conv_123_abc
Message 2: conv_456_def  ← DIFFÉRENT
```

**Cause** : Le state n'est pas persisté ou reset
**Fix** : Vérifier chat-interface.js ligne 138 et persistence

### Problème 2 : historyLength toujours 0
**Symptôme** :
```
🔵 [DEBUG FRONTEND] historyLength: 0  ← À chaque message
```

**Cause** : `this.state.messages` n'accumule pas les messages
**Fix** : Vérifier `addMessage()` et que les messages sont bien ajoutés au state

### Problème 3 : "NO HISTORY" côté backend
**Symptôme** :
```
⚠️  [DEBUG] NO HISTORY - First message or history not sent
```

**Cause** : Le frontend n'envoie pas `history` dans le payload
**Fix** : Vérifier sendToAPI() ligne 539 et que history est bien dans le body

### Problème 4 : "NO USERCONTEXT" après plusieurs messages
**Symptôme** :
```
Message 1: UserContext received {groupType: 'solo'}
Message 2: ⚠️  NO USERCONTEXT  ← RESET
```

**Cause** : Le userContext n'est pas renvoyé au backend ou pas updaté
**Fix** : Vérifier ligne 506 que response.userContext merge avec this.state.userContext

### Problème 5 : toolCallsCount = 0
**Symptôme** :
```
info: AI response generated {toolCallsCount: 0}
```

**Cause possible** :
1. System prompt trop long (résolu avec v3)
2. L'IA ne comprend pas les instructions
3. Les tools ne sont pas correctement passés

**Fix** :
- Vérifier que system-prompt-v3-minimal.md est chargé
- Vérifier logs "Calling OpenAI API" pour voir toolsCount
- Tester avec prompt encore plus court si nécessaire

## 📝 Template pour copier les logs

Copie ces sections dans ta réponse :

```
=== CONSOLE NAVIGATEUR (F12) ===

🔵 [DEBUG FRONTEND] Initial conversationId:
🔵 [DEBUG FRONTEND] ConversationId après load:
🔵 [DEBUG FRONTEND] Messages count:
🔵 [DEBUG FRONTEND] Sending to backend:
🔵 [DEBUG FRONTEND] Full history:
🔵 [DEBUG FRONTEND] Response from backend:

=== LOGS BACKEND (docker-compose logs) ===

🔵 [DEBUG] Generating AI response
🔵 [DEBUG] History received
🔵 [DEBUG] UserContext received
🔵 [DEBUG] Messages to OpenAI
🔵 [DEBUG] Calling OpenAI API
info: AI response generated

=== CE QUE L'IA A RÉPONDU ===

[Copie le message de l'IA]

=== COMPORTEMENT OBSERVÉ ===

- conversationId change ? OUI / NON
- historyLength augmente ? OUI / NON
- userContext s'enrichit ? OUI / NON
- toolCallsCount > 0 ? OUI / NON
- L'IA redemande des infos ? OUI / NON
```

## 🎯 Prochaines étapes selon diagnostic

### Si conversationId change
→ Fix persistence du state

### Si historyLength = 0
→ Fix accumulation messages dans state

### Si "NO HISTORY" backend
→ Fix payload sendToAPI()

### Si "NO USERCONTEXT" backend
→ Fix update userContext après response

### Si toolCallsCount = 0
→ Simplifier encore plus le prompt OU forcer tool_choice

## 🆘 Solution de dernier recours : tool_choice

Si l'IA refuse d'appeler les tools malgré tout :

```javascript
// src/services/ai-service-v2.js
const result = await generateText({
  model: openai(config.openai.defaultModel),
  system: systemPrompt,
  messages,
  tools,
  toolChoice: 'required',  // ← FORCER l'appel d'un tool
  temperature: 0.7,
  maxTokens: 4000,
  maxSteps: 5
});
```

Ou spécifier quel tool :
```javascript
toolChoice: {
  type: 'tool',
  toolName: 'collectUserProfile'
}
```

Mais idéalement l'IA devrait appeler les tools naturellement avec le prompt v3.
