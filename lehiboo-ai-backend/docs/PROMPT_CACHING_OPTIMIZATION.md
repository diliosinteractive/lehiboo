# Optimisation Prompt Caching OpenAI

## Vue d'ensemble

Le **Prompt Caching** d'OpenAI permet de réduire les coûts de **50%** sur les tokens en cache.

### Fonctionnement Automatique

- ✅ **Activé automatiquement** pour les prompts >1024 tokens
- ✅ **Pas de code spécial** nécessaire avec Vercel AI SDK
- ✅ Cache persiste **5-10 minutes** (jusqu'à 1h en off-peak)
- ✅ Compatible avec **gpt-4o** et **gpt-4o-mini**

## Notre Architecture Optimisée

### Structure des Messages (Optimal pour le Cache)

```javascript
// 1. STATIC CONTENT (cacheable) ✅
system: systemPrompt,          // ~750 tokens - CACHE
tools: { collectUserProfile, searchEvents },  // ~500 tokens - CACHE

// 2. VARIABLE CONTENT (non-cacheable)
messages: [
  { role: 'user', content: '[CONTEXT: ...] message' },
  { role: 'assistant', content: '...' },
  // ...history
]
```

### Pourquoi ça marche ?

**Total tokens > 1024** dès le 2ème-3ème message :
- System prompt v6 : ~750 tokens
- Tools definition : ~500 tokens
- Conversation history (2-3 msgs) : ~300+ tokens
- **TOTAL : ~1550+ tokens** → Cache activé ✅

## Metrics de Cache

### Logs Disponibles

Chaque requête API log maintenant :

```javascript
{
  "promptTokens": 1850,        // Total tokens prompt
  "completionTokens": 180,     // Tokens réponse
  "cachedTokens": 1250,        // Tokens servis depuis cache ✅
  "cacheHitRate": "67.6%",     // % de tokens en cache
  "costSaving": "~0.0016$",    // Économies réalisées
  "tokensUsed": 2030           // Total facturé
}
```

### Interprétation

**1er message d'une conversation** :
```javascript
cacheHitRate: "0%"  // Normal - pas encore de cache
```

**2ème+ message (< 5-10 min après)** :
```javascript
cacheHitRate: "60-80%"  // Excellent - cache actif ✅
// Seuls les nouveaux messages user sont "uncached"
```

**Si cacheHitRate reste à 0% après 3+ messages** :
- Cache expiré (>10 min d'inactivité)
- Ou prompt/tools ont changé entre les requêtes

## Économies Réalisées

### Calcul des Coûts

**Sans cache** (ancienne version v5) :
- Prompt : 3500 tokens × $0.0025/1k = $0.00875 par message
- 100 messages/jour = **$0.875/jour** = **~$26/mois**

**Avec cache + v6 optimisé** :
- 1er message : 1550 tokens × $0.0025/1k = $0.00388
- 2ème+ message : 300 tokens non-cachés × $0.0025/1k = $0.00075
- Moyenne (80% cache hit) : **$0.00130 par message**
- 100 messages/jour = **$0.13/jour** = **~$4/mois**

**Économie : ~85%** 🎉

### Optimisations Cumulées

| Version | Tokens/msg | Coût/100msg | Économie |
|---------|------------|-------------|----------|
| v5 Original | ~8000 | $2.00 | - |
| v6 Compact | ~3000 | $0.75 | -62% |
| v6 + Cache (80%) | ~600 effectifs | **$0.15** | **-92%** |

## Bonnes Pratiques

### ✅ À Faire

1. **Structure optimale déjà en place** :
   - System prompt en premier (static)
   - Tools definition constante (static)
   - Messages variables à la fin

2. **Monitorer les metrics** :
   - Vérifier `cacheHitRate` dans les logs Docker
   - Alerter si <50% après 3+ messages

3. **Maintenir la cohérence** :
   - Ne pas modifier le system prompt entre les messages
   - Garder les tools definitions stables

### ❌ À Éviter

1. **Changer le prompt dynamiquement** :
   ```javascript
   // ❌ Casse le cache à chaque message
   system: `${basePrompt}\nDate: ${new Date()}`

   // ✅ Mettre les données variables dans les messages
   messages: [{ role: 'user', content: `[CONTEXT: date=${date}] ${msg}` }]
   ```

2. **Réorganiser l'ordre des tools** :
   - L'objet `tools` doit rester dans le même ordre

3. **Ignorer les metrics** :
   - Surveiller `cacheHitRate` pour détecter les problèmes

## Vérification en Production

### Commande de Monitoring

```bash
# Voir les metrics de cache en temps réel
docker-compose logs -f lehiboo-ai-backend | grep "cacheHitRate"

# Exemple de sortie attendue :
# "cacheHitRate": "0%"      ← 1er message (normal)
# "cacheHitRate": "68.5%"   ← 2ème message (excellent ✅)
# "cacheHitRate": "72.3%"   ← 3ème message (excellent ✅)
```

### Indicateurs de Santé

- ✅ **Bon** : cacheHitRate >60% dès le 2ème message
- ⚠️ **Attention** : cacheHitRate 30-60% (cache partiel)
- ❌ **Problème** : cacheHitRate 0% après 3+ messages

## Références

- [OpenAI Prompt Caching Documentation](https://platform.openai.com/docs/guides/prompt-caching)
- [Vercel AI SDK - Prompt Caching](https://sdk.vercel.ai/docs/ai-sdk-core/prompts#prompt-caching)
