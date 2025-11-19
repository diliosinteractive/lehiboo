# 🔧 CORRECTION URGENTE - URLs ÉVÉNEMENTS

**Date** : 5 octobre 2025
**Problème** : Les URLs des événements étaient cassées
**Statut** : ✅ CORRIGÉ

---

## ⚠️ PROBLÈME IDENTIFIÉ

### Cause
Les **slugs d'URL** ont été traduits dans le fichier de traduction, ce qui a changé les URLs des événements et des lieux.

**Slugs traduits (ERREUR)** :
- `event` → `événement` ❌
- `venue` → `Lieu` ❌

**Impact** :
- URLs cassées : `/event/nom-evenement/` devenait `/événement/nom-evenement/`
- Pages 404 pour tous les événements
- Pages 404 pour tous les lieux
- Impossibilité d'accéder au contenu

---

## ✅ CORRECTION APPLIQUÉE

### Fichier modifié
`wp-content/plugins/eventlist/languages/eventlist-fr_FR.po`

### Changements effectués

**Ligne 3147 - Slug Event** :
```diff
msgctxt "Event Slug"
msgid "event"
- msgstr "événement"
+ msgstr "event"
```

**Ligne 8819 - Slug Venue** :
```diff
msgctxt "Venue Slug"
msgid "venue"
- msgstr "Lieu"
+ msgstr "venue"
```

### Fichier recompilé
✅ `eventlist-fr_FR.mo` recompilé avec succès
✅ 2,194 messages traduits
✅ 0 erreur de compilation

---

## 📋 SLUGS VÉRIFIÉS - TOUS CORRECTS

Tous les slugs suivants sont **NON TRADUITS** (comme il se doit) :

| Context | Slug | Traduction | Statut |
|---------|------|------------|--------|
| Event Slug | event | event | ✅ |
| Event Category Slug | event_cat | event_cat | ✅ |
| Event Tag Slug | event_tag | event_tag | ✅ |
| Event Location Slug | event_loc | event_loc | ✅ |
| Venue Slug | venue | venue | ✅ |
| Bookings Slug | el_bookings | el_bookings | ✅ |
| Tickets Slug | el_tickets | el_tickets | ✅ |
| Package Slug | package | package | ✅ |
| Payout Slug | payout | payout | ✅ |
| Payout Method Slug | payout_method | payout_method | ✅ |
| Membership Slug | manage_membership | manage_membership | ✅ |

---

## 🚀 ACTIONS REQUISES APRÈS CORRECTION

### 1. Vider tous les caches
**IMPORTANT** : Les caches peuvent conserver les anciennes URLs

**Cache plugin** :
- WP Rocket : Vider le cache + Regenerate Critical CSS
- W3 Total Cache : Supprimer tous les caches
- WP Super Cache : Supprimer le cache

**Cache serveur** :
```bash
# Redis
redis-cli FLUSHALL

# Memcached
echo "flush_all" | nc localhost 11211

# WordPress CLI
wp cache flush
```

### 2. Regénérer les permaliens (CRITIQUE)

**Via l'admin WordPress** :
```
Se connecter → Réglages → Permaliens
Cliquer sur "Enregistrer les modifications" (sans rien changer)
```

Cette action force WordPress à reconstruire les règles de réécriture d'URL.

**Via WP-CLI** (si disponible) :
```bash
wp rewrite flush
```

### 3. Vérifier que les URLs fonctionnent

**Tester ces pages** :
- ✅ Liste événements : `/event/`
- ✅ Événement unique : `/event/nom-evenement/`
- ✅ Catégorie : `/event_cat/categorie/`
- ✅ Lieu : `/venue/nom-lieu/`
- ✅ Archive tag : `/event_tag/tag/`

---

## 📖 RÈGLE IMPORTANTE

### ⚠️ NE JAMAIS TRADUIRE LES SLUGS

**Slugs = Identifiants techniques dans les URLs**

Les slugs doivent **toujours rester en anglais** car :
1. Ils font partie de la structure d'URL
2. Modifier un slug change toutes les URLs du site
3. Casse tous les liens existants (Google, bookmarks, liens externes)
4. Impact SEO majeur (perte de ranking)

**Exemple** :
```php
// Dans le code
'rewrite' => array(
    'slug' => _x('event', 'Event Slug', 'eventlist')
)
```

**Traduction correcte** :
```po
msgctxt "Event Slug"
msgid "event"
msgstr "event"  ← Garder l'anglais !
```

**Traduction INCORRECTE** :
```po
msgctxt "Event Slug"
msgid "event"
msgstr "événement"  ← NE JAMAIS FAIRE ÇA !
```

---

## 🔍 COMMENT IDENTIFIER UN SLUG DANS LE FICHIER PO

**Indicateurs** :
1. `msgctxt` contient le mot "Slug"
2. Exemples : "Event Slug", "Category Slug", "Venue Slug"
3. `msgid` est généralement un mot anglais simple

**Action** : Toujours mettre la **même valeur** dans `msgstr` que dans `msgid`

---

## ✅ VÉRIFICATION POST-CORRECTION

### Checklist

- [x] Slug "event" → "event" (corrigé)
- [x] Slug "venue" → "venue" (corrigé)
- [x] Tous les autres slugs vérifiés (OK)
- [x] Fichier .mo recompilé
- [x] Compilation sans erreur
- [ ] Caches vidés (À FAIRE)
- [ ] Permaliens régénérés (À FAIRE)
- [ ] URLs testées (À FAIRE)

### Commandes de test

```bash
# Vérifier que les slugs ne sont pas traduits
grep -A2 "msgctxt.*Slug" wp-content/plugins/eventlist/languages/eventlist-fr_FR.po | grep msgstr

# Tous les msgstr doivent être identiques aux msgid ou des slug techniques
```

---

## 📊 IMPACT SEO

### Avant correction
❌ Toutes les URLs changées → 404
❌ Perte totale de référencement
❌ Backlinks cassés
❌ Google pénalise les 404

### Après correction + flush permaliens
✅ URLs restaurées à leur état original
✅ Pas de redirection nécessaire
✅ SEO intact
✅ Backlinks fonctionnels

**Durée de récupération Google** : 1-7 jours après flush permaliens

---

## 🔄 PRÉVENTION FUTURE

### Si mise à jour du plugin EventList

**Avant de recompiler** :
```bash
# Vérifier que les slugs ne sont PAS traduits
grep -A2 "msgctxt.*Slug" eventlist-fr_FR.po | grep msgstr

# Si un slug est traduit, le corriger AVANT compilation
```

### Si utilisation de Loco Translate

**Attention** : Loco Translate peut proposer de traduire les slugs.
**Toujours refuser** et garder les slugs en anglais.

---

## 📝 NOTES TECHNIQUES

### Pourquoi _x() au lieu de __() ?

```php
_x('event', 'Event Slug', 'eventlist')
```

La fonction `_x()` permet d'avoir un **contexte** :
- `'event'` = texte à traduire
- `'Event Slug'` = contexte (msgctxt)
- `'eventlist'` = text domain

**Avantage** : On peut avoir plusieurs traductions du mot "event" selon le contexte :
- Dans un titre : "event" → "événement"
- Dans un slug URL : "event" → "event" (pas traduit)

---

## 🆘 SI LES URLs SONT ENCORE CASSÉES

### Diagnostic

**1. Vérifier le fichier MO est bien mis à jour**
```bash
ls -lh wp-content/plugins/eventlist/languages/eventlist-fr_FR.mo
# Date doit être récente (après correction)
```

**2. Tester sans cache**
- Navigation privée + Ctrl+F5
- Ou désactiver temporairement le cache plugin

**3. Vérifier les règles de réécriture**
```bash
# Via WP-CLI
wp rewrite list --format=csv
# Doit afficher "event" pas "événement"
```

**4. Debug WordPress**
```php
// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
Consulter `wp-content/debug.log`

### Solution de secours

**Si rien ne fonctionne** :
1. Désactiver le plugin EventList
2. Vider TOUS les caches
3. Réactiver le plugin EventList
4. Aller dans Réglages → Permaliens → Enregistrer

---

## ✅ RÉSUMÉ

**Problème** : Slugs traduits → URLs cassées
**Solution** : Slugs restaurés en anglais + recompilation
**Actions utilisateur** : Vider caches + Regénérer permaliens

**Statut** : ✅ Correction appliquée, prête pour test

---

**Dernière mise à jour** : 5 octobre 2025
**Fichier corrigé** : eventlist-fr_FR.po + eventlist-fr_FR.mo
**Impact** : Critique (URLs cassées → URLs restaurées)
