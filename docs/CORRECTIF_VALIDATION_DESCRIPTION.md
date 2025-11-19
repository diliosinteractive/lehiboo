# Correctif - Validation Description (Double Alert + Redirection 404)

## Problème identifié

Lors de la tentative de publication d'un événement avec une description < 500 caractères :
1. Une alerte s'affiche correctement
2. Après avoir cliqué sur "OK", une deuxième alerte apparaît
3. La page redirige vers une URL 404 : `https://lehiboo.dilios.me/member-account/undefined#undefined`

## Cause du problème

Le code JavaScript intercepte le clic sur le bouton mais n'empêche pas complètement l'exécution du code AJAX qui suit. Le formulaire utilise probablement un handler AJAX attaché au même bouton qui s'exécute malgré notre `preventDefault()`.

## Solution implémentée (v1.0.1)

### 1. Utilisation de la capture phase

Au lieu d'utiliser la phase de bubbling normale, nous utilisons la **capture phase** pour intercepter l'événement avant tous les autres handlers :

```javascript
button.addEventListener('click', function(e) {
    if (!validateBeforeSubmit()) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    }
}, true); // true = capture phase
```

### 2. Flag global pour bloquer l'AJAX

Ajout d'un flag global `window.el_description_validation_failed` qui est vérifié dans le hook `ajaxSend` :

```javascript
window.el_description_validation_failed = false;

// Dans validateBeforeSubmit()
if (currentLength < MIN_DESCRIPTION_LENGTH) {
    window.el_description_validation_failed = true;
    alert('...');
    return false;
}

// Hook AJAX
$(document).ajaxSend(function(_event, jqxhr, settings) {
    if (settings.data && settings.data.indexOf('el_save_edit_event') !== -1) {
        if (window.el_description_validation_failed === true) {
            jqxhr.abort(); // Annuler la requête
            window.el_description_validation_failed = false;
            return false;
        }
    }
});
```

### 3. Logs de débogage

Ajout de `console.log()` pour faciliter le debugging :
- "Click intercepté sur bouton submit"
- "Validation échouée - blocage du submit"
- "Validation réussie - autorisation du submit"
- "Soumission bloquée : description trop courte"

## Test de la solution

### Procédure de test

1. Ouvrir la console développeur (F12)
2. Aller sur la page de création/édition d'événement
3. Remplir une description < 500 caractères (ex: 200 caractères)
4. Sélectionner le statut "Public"
5. Cliquer sur "Enregistrer"

### Résultat attendu

**Dans la console :**
```
Click intercepté sur bouton submit
Validation échouée - blocage du submit
Soumission bloquée : description trop courte
```

**Dans l'interface :**
- Une seule alerte affichée
- Pas de deuxième alerte
- Pas de redirection
- Scroll vers la section Présentation
- Onglet Présentation activé

### Résultat si validation OK (≥ 500 caractères)

**Dans la console :**
```
Click intercepté sur bouton submit
Validation réussie - autorisation du submit
```

**Dans l'interface :**
- Pas d'alerte
- Sauvegarde normale
- Message de succès

## Détails techniques

### Ordre d'exécution des événements

1. **Capture phase** (de haut en bas du DOM)
   - Notre handler avec `addEventListener(..., true)`
   - ✅ Exécuté EN PREMIER

2. **Target phase**
   - Événement sur l'élément cible

3. **Bubbling phase** (de bas en haut du DOM)
   - Handlers jQuery `.on('click', ...)`
   - Handlers natifs sans capture
   - ❌ Ces handlers sont bloqués par notre `stopPropagation()`

### Pourquoi `ajaxSend` ?

Le hook `ajaxSend` est appelé **avant** chaque requête AJAX. Si la validation a échoué, on appelle `jqxhr.abort()` pour annuler la requête avant qu'elle ne soit envoyée au serveur.

### Méthodes de blocage combinées

| Méthode | Rôle |
|---------|------|
| `e.preventDefault()` | Empêche l'action par défaut du bouton |
| `e.stopPropagation()` | Empêche la propagation aux éléments parents |
| `e.stopImmediatePropagation()` | Empêche les autres handlers sur le même élément |
| `return false` | Double sécurité (équivalent à preventDefault + stopPropagation) |
| `jqxhr.abort()` | Annule la requête AJAX si elle est lancée malgré tout |

## Fichiers modifiés

### event-description-validation.js v1.0.1

**Changements :**
- Ajout de `window.el_description_validation_failed`
- Utilisation de la capture phase (`addEventListener` avec `true`)
- Hook `ajaxSend` pour bloquer les requêtes AJAX
- Ajout de logs de débogage
- Fix du warning ESLint sur `event` non utilisé (renommé en `_event`)

**Lignes modifiées :**
- Ligne 4 : Version 1.0.1
- Ligne 20 : Ajout du flag global
- Ligne 92-107 : Mise à jour de `validateBeforeSubmit()` avec flag
- Ligne 194-212 : Nouvelle méthode d'interception avec capture phase
- Ligne 215-218 : Hook AJAX pour double sécurité

## Notes importantes

### Ne pas retirer les logs en production

Les logs `console.log()` peuvent être utiles pour le debugging en production. Ils n'affectent pas les performances de manière significative et permettent de diagnostiquer rapidement si un problème survient.

### Si le problème persiste

Si le double alert persiste malgré ce correctif, vérifier :

1. **Cache navigateur** : Vider le cache et forcer le rechargement (Ctrl+Shift+R)
2. **Cache WordPress** : Vider le cache du plugin de cache (si installé)
3. **Minification** : Vérifier qu'aucun plugin ne minifie le JS et n'écrase notre fichier
4. **Conflit de scripts** : Désactiver temporairement les autres plugins pour identifier un conflit

### Alternative si le problème continue

Si malgré tout le problème persiste, une alternative serait de **désactiver complètement** le bouton pendant la validation :

```javascript
const $button = $(e.target);
$button.prop('disabled', true);
// ... validation ...
if (validation_failed) {
    $button.prop('disabled', false); // Réactiver
    return false;
}
```

## Version

- **Version** : 1.0.1
- **Date** : 2025-10-26
- **Correctif pour** : Double alert + Redirection 404
