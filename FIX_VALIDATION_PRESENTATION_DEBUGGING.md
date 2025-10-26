# Fix - Validation Présentation avec Debugging Complet

**Date** : 2025-10-26
**Version** : profile-presentation-validation.js v1.0.2 + class-el-assets.php
**Contexte** : V1 Le Hiboo - Phase 6
**Type** : Correctif debugging + Fix erreur JavaScript supplémentaire

---

## Problèmes identifiés

### Problème 1 : Le compteur de caractères ne s'affiche pas

**Symptômes** :
- Aucun compteur visible sous le champ "Description" dans la page profil
- La validation des 500 caractères ne fonctionne pas
- Pas d'erreur visible dans la console

**Causes possibles** :
- TinyMCE met du temps à s'initialiser
- Le script s'exécute trop tôt
- Mauvais sélecteur pour le bouton de sauvegarde
- Structure HTML différente de celle attendue

### Problème 2 : Erreur JavaScript `pagenow is not defined`

**Erreur console** :
```
jquery.min.js?ver=3.7.1:2 Uncaught ReferenceError: pagenow is not defined
    at Object.fix_menu (admin.min.js?ver=6.8.3:1:2032)
    at Object.init (admin.min.js?ver=6.8.3:1:1937)
    at HTMLDocument.<anonymous> (admin.min.js?ver=6.8.3:40:40877)
```

**Cause** :
Le script `admin.min.js` est chargé sur le frontend (pages vendor) mais la variable `pagenow` (variable WordPress admin) n'était pas localisée pour le contexte frontend.

---

## Solutions implémentées

### Solution 1 : Système de debugging complet + stratégie multi-tentatives

#### Fichier modifié : [profile-presentation-validation.js](wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js)

**Version** : 1.0.1 → **1.0.2**

#### A. Logs de debugging ajoutés partout

**Au chargement du script** :
```javascript
console.log('Profile Presentation Validation - Script chargé');
console.log('Page profil détectée');
```

**Dans getDescriptionLength()** :
```javascript
console.log('Contenu récupéré depuis TinyMCE:', content.length + ' chars');
console.log('Contenu récupéré depuis textarea:', content.length + ' chars');
console.log('Aucun champ description trouvé');
console.log('Longueur finale après nettoyage:', textOnly.length);
```

**Dans initCharacterCounter()** :
```javascript
console.log('initCharacterCounter() appelé');
console.log('typeof tinymce:', typeof tinymce);
console.log('TinyMCE disponible, recherche éditeur description...');
console.log('Éditeur description:', editor);
console.log('TinyMCE initialisé pour description - mise en place listeners');
console.log('Fallback textarea standard');
console.log('Textarea trouvé:', $textarea.length);
console.log('Aucun éditeur trouvé');
```

**Événements** :
```javascript
console.log('Événement TinyMCE détecté');
console.log('Événement textarea détecté');
console.log('Click intercepté sur bouton save presentation');
```

#### B. Stratégie multi-tentatives (retry logic)

**Avant (v1.0.1)** : Une seule tentative après 2 secondes
```javascript
setTimeout(initCharacterCounter, 2000);
```

**Après (v1.0.2)** : Jusqu'à 10 tentatives espacées de 500ms
```javascript
let initAttempts = 0;
const maxAttempts = 10;

function tryInit() {
    initAttempts++;
    console.log('Tentative d\'initialisation #' + initAttempts);

    if (initCharacterCounter()) {
        console.log('✅ Initialisation réussie');
        return;
    }

    if (initAttempts < maxAttempts) {
        console.log('⏳ Nouvelle tentative dans 500ms...');
        setTimeout(tryInit, 500);
    } else {
        console.error('❌ Échec initialisation après ' + maxAttempts + ' tentatives');
    }
}

// Démarrage
setTimeout(tryInit, 1000);
```

**Avantages** :
- ✅ 10 tentatives = 5 secondes de délai maximum
- ✅ Stop automatique dès que l'initialisation réussit
- ✅ Logs clairs pour debugging
- ✅ Message d'erreur si échec après toutes les tentatives

#### C. Retour de `initCharacterCounter()` pour indiquer le succès

**Avant (v1.0.1)** : Fonction void (pas de retour)

**Après (v1.0.2)** : Retourne `true` si succès, `false` si échec
```javascript
function initCharacterCounter() {
    // ...
    if (editor) {
        // Initialisation TinyMCE
        return true;
    }

    // Fallback textarea
    if ($textarea.length) {
        // Initialisation textarea
        return true;
    }

    return false; // Échec
}
```

**Avantage** : Permet au système de retry de savoir s'il doit continuer ou s'arrêter.

#### D. Fix du sélecteur de bouton de sauvegarde

**Avant (v1.0.1)** : Sélecteurs génériques qui ne marchent pas
```javascript
$('.btn_save_presentation, #save_presentation').each(function() {
```

**Après (v1.0.2)** : Sélecteur précis basé sur l'attribut `name`
```javascript
console.log('Recherche bouton de sauvegarde présentation...');
const $saveButton = $('input[name="el_update_presentation"]');
console.log('Bouton trouvé:', $saveButton.length);

$saveButton.each(function() {
```

**Justification** : Le template [profile.php:1161](wp-content/plugins/eventlist/templates/vendor/profile.php#L1161) utilise :
```html
<input type="submit" name="el_update_presentation" class="button el_submit_btn" value="Enregistrer" />
```

Le sélecteur `input[name="el_update_presentation"]` est le plus fiable.

---

### Solution 2 : Localisation de la variable `pagenow`

#### Fichier modifié : [class-el-assets.php](wp-content/plugins/eventlist/includes/class-el-assets.php)

**Lignes** : 154-155 (ajoutées)

**Code ajouté** :
```php
// V1 Le Hiboo - Fix: Ajouter pagenow manquant sur frontend (simulé pour compatibilité admin.min.js)
wp_localize_script( 'el_script_admin', 'pagenow', 'vendor-profile' );
```

**Contexte** :
- Le script `admin.min.js` est chargé sur le frontend avec le handle `el_script_admin`
- Ce script utilise la variable `pagenow` (variable WordPress admin)
- Cette variable n'existe naturellement que dans le backend WordPress
- Il faut la créer artificiellement pour le frontend

**Valeur choisie** : `'vendor-profile'`
- C'est une valeur arbitraire mais cohérente avec le contexte
- Le script `admin.min.js` utilise cette variable pour des conditions (`if (pagenow == 'xxx')`)
- La valeur `'vendor-profile'` évite les collisions avec les valeurs backend

**Impact** :
- ✅ Plus d'erreur JavaScript
- ✅ Le script `admin.min.js` fonctionne sur le frontend
- ✅ Compatibilité maintenue avec le backend (variable différente)

---

## Récapitulatif des modifications

### 1. profile-presentation-validation.js

| Section | Modification | Lignes |
|---------|-------------|---------|
| **Header** | Version 1.0.1 → 1.0.2 + changelog | 1-10 |
| **Chargement** | Logs au démarrage | 15, 19, 23 |
| **getDescriptionLength()** | Logs pour chaque branche | 41, 47, 49, 55 |
| **initCharacterCounter()** | Logs complets + retour boolean | 162-200 |
| **tryInit()** | Nouvelle fonction avec retry logic | 207-222 |
| **Initialisation** | Remplacement timeout simple par stratégie multi-tentatives | 225-238 |
| **Bouton save** | Fix sélecteur + logs | 242-246 |

### 2. class-el-assets.php

| Section | Modification | Lignes |
|---------|-------------|---------|
| **Localisation pagenow** | Ajout variable manquante | 154-155 |
| **Version script** | 1.0.1 → 1.0.2 | 251 |

---

## Comment utiliser les logs pour debugger

### Étapes de debugging

1. **Ouvrir la console développeur** : F12 → Console

2. **Aller sur la page profil** : `?vendor=profile`

3. **Analyser les logs de chargement** :
   ```
   ✅ Attendu : "Profile Presentation Validation - Script chargé"
   ✅ Attendu : "Page profil détectée"
   ❌ Si absent : Le script ne se charge pas ou la page n'est pas détectée
   ```

4. **Analyser les tentatives d'initialisation** :
   ```
   ✅ Attendu : "Tentative d'initialisation #1"
   ✅ Attendu : "initCharacterCounter() appelé"
   ✅ Attendu : "typeof tinymce: object" OU "typeof tinymce: undefined"
   ```

5. **Si TinyMCE détecté** :
   ```
   ✅ Attendu : "TinyMCE disponible, recherche éditeur description..."
   ✅ Attendu : "Éditeur description: [Object]" (pas null)
   ✅ Attendu : "TinyMCE initialisé pour description - mise en place listeners"
   ✅ Attendu : "✅ Initialisation réussie"
   ```

6. **Si TinyMCE non détecté** :
   ```
   ✅ Attendu : "Fallback textarea standard"
   ✅ Attendu : "Textarea trouvé: 1"
   ✅ Attendu : "✅ Initialisation réussie"
   ```

7. **Si échec complet** :
   ```
   ❌ Problème : "❌ Échec initialisation après 10 tentatives"
   → Le champ n'est pas trouvé ou TinyMCE ne s'initialise jamais
   ```

8. **Vérifier le bouton de sauvegarde** :
   ```
   ✅ Attendu : "Recherche bouton de sauvegarde présentation..."
   ✅ Attendu : "Bouton trouvé: 1"
   ❌ Si "Bouton trouvé: 0" : Le sélecteur ne fonctionne pas
   ```

9. **Taper dans le champ Description** :
   ```
   ✅ Attendu : "Événement TinyMCE détecté" OU "Événement textarea détecté"
   ✅ Attendu : "Contenu récupéré depuis TinyMCE: X chars"
   ✅ Attendu : "Longueur finale après nettoyage: Y"
   ```

10. **Cliquer sur "Enregistrer" avec < 500 chars** :
    ```
    ✅ Attendu : "Click intercepté sur bouton save presentation"
    ✅ Attendu : "Validation présentation échouée - blocage"
    ✅ Attendu : Alerte JavaScript affichée
    ```

---

## Scénarios de debugging

### Scénario 1 : Le script ne se charge pas du tout

**Symptômes** :
- Aucun log dans la console
- Pas de "Profile Presentation Validation - Script chargé"

**Causes possibles** :
1. Le script n'est pas enregistré correctement
2. La condition `$_GET['vendor'] == 'profile'` est fausse
3. Erreur JavaScript qui bloque tout

**Vérifications** :
```javascript
// Dans la console
console.log($('script[src*="profile-presentation-validation"]').length);
// Doit retourner : 1
```

**Solution** :
- Vérifier que vous êtes bien sur `?vendor=profile`
- Vérifier le fichier [class-el-assets.php:238-251](wp-content/plugins/eventlist/includes/class-el-assets.php#L238-L251)
- Vider le cache navigateur (Ctrl+F5)

---

### Scénario 2 : TinyMCE ne se charge jamais

**Symptômes** :
- "typeof tinymce: undefined" dans tous les logs
- Fallback sur textarea
- Textarea introuvable également
- "❌ Échec initialisation après 10 tentatives"

**Causes possibles** :
1. `wp_enqueue_editor()` pas appelé
2. TinyMCE bloqué par un autre script
3. Conflit JavaScript

**Vérifications** :
```javascript
// Dans la console après chargement page
typeof tinymce
// Doit retourner : "object"

tinymce.get('description')
// Doit retourner : [Object Editor] ou null
```

**Solution** :
- Vérifier [class-el-assets.php:240](wp-content/plugins/eventlist/includes/class-el-assets.php#L240) : `wp_enqueue_editor();`
- Attendre 5-10 secondes après chargement de la page
- Vérifier la console pour d'autres erreurs JavaScript

---

### Scénario 3 : TinyMCE existe mais éditeur 'description' non trouvé

**Symptômes** :
- "typeof tinymce: object"
- "Éditeur description: null"
- Échec initialisation

**Causes possibles** :
1. Le champ a un autre ID
2. TinyMCE n'est pas initialisé pour ce champ
3. Le champ est dans un onglet caché

**Vérifications** :
```javascript
// Dans la console
tinymce.editors
// Liste tous les éditeurs TinyMCE

Object.keys(tinymce.editors)
// Affiche les IDs des éditeurs
```

**Solution** :
- Vérifier que l'ID du champ est bien `description` dans [profile.php:1018](wp-content/plugins/eventlist/templates/vendor/profile.php#L1018)
- Cliquer sur l'onglet "Présentation" pour afficher le champ
- Attendre quelques secondes après le clic sur l'onglet

---

### Scénario 4 : Le compteur ne s'affiche pas malgré initialisation réussie

**Symptômes** :
- "✅ Initialisation réussie" dans les logs
- Événements détectés quand on tape
- Mais aucun compteur visible

**Causes possibles** :
1. Le compteur est créé mais caché par CSS
2. Le sélecteur `.vendor_field.wysiwyg` ne fonctionne pas
3. Le DOM ne correspond pas à la structure attendue

**Vérifications** :
```javascript
// Dans la console
$('#presentation-char-counter').length
// Doit retourner : 1

$('#presentation-char-counter').is(':visible')
// Doit retourner : true

$('#presentation-char-counter').html()
// Affiche le contenu HTML du compteur
```

**Solution** :
- Vérifier la structure HTML du champ Description
- Vérifier que `.vendor_field.wysiwyg` existe
- Inspecter avec DevTools pour voir si le compteur est créé mais caché

---

### Scénario 5 : Le bouton de sauvegarde ne déclenche pas la validation

**Symptômes** :
- "Bouton trouvé: 0" dans les logs
- Clic sur "Enregistrer" n'affiche rien dans la console
- Le formulaire se soumet sans validation

**Causes possibles** :
1. Mauvais sélecteur de bouton
2. Le bouton a un autre attribut `name`
3. Le bouton est ajouté dynamiquement après le script

**Vérifications** :
```javascript
// Dans la console
$('input[name="el_update_presentation"]').length
// Doit retourner : 1

$('input[type="submit"]').length
// Compte tous les boutons submit de la page

$('form#el_save_presentation input[type="submit"]').attr('name')
// Affiche le nom du bouton dans le bon formulaire
```

**Solution** :
- Vérifier le template [profile.php:1161](wp-content/plugins/eventlist/templates/vendor/profile.php#L1161)
- Adapter le sélecteur dans le script si nécessaire

---

## Tests à effectuer après corrections

### Test 1 : Vérification erreur `pagenow`

1. Aller sur `?vendor=profile`
2. Ouvrir la console (F12)
3. Recharger la page (Ctrl+F5)
4. **Vérifier** : Aucune erreur "pagenow is not defined"

**Résultat attendu** :
```
✅ Aucune erreur liée à pagenow
```

### Test 2 : Vérification chargement du script

1. Aller sur `?vendor=profile`
2. Ouvrir la console (F12)
3. **Vérifier** : Logs de chargement présents

**Résultat attendu** :
```
Profile Presentation Validation - Script chargé
Page profil détectée
Démarrage tentatives d'initialisation
Tentative d'initialisation #1
```

### Test 3 : Vérification initialisation TinyMCE

1. Aller sur `?vendor=profile`
2. Cliquer sur l'onglet "Présentation"
3. Attendre 2-3 secondes
4. Consulter la console

**Résultat attendu** :
```
initCharacterCounter() appelé
typeof tinymce: object
TinyMCE disponible, recherche éditeur description...
Éditeur description: [Object]
TinyMCE initialisé pour description - mise en place listeners
✅ Initialisation réussie
```

### Test 4 : Vérification compteur visible

1. Aller sur `?vendor=profile` > Présentation
2. **Vérifier visuellement** : Compteur sous le champ Description

**Résultat attendu** :
- ✅ Compteur visible (rouge si < 500, vert si ≥ 500)
- ✅ Mise à jour en temps réel quand on tape

### Test 5 : Vérification validation

1. Mettre 200 caractères dans Description
2. Cliquer sur "Enregistrer"
3. **Vérifier console** :

**Résultat attendu** :
```
Click intercepté sur bouton save presentation
Validation présentation échouée - blocage
Soumission présentation bloquée : description trop courte
```

**Résultat visuel attendu** :
- ✅ Alerte JavaScript affichée
- ✅ Formulaire non soumis

---

## Nettoyage des logs pour production

Une fois le debugging terminé, vous pouvez retirer tous les `console.log()` pour alléger le script :

**Commande de recherche/remplacement** :
- Rechercher : `console\.log\([^)]+\);\n?`
- Remplacer par : ` ` (vide)
- Outil : Éditeur avec regex (VSCode, PhpStorm, etc.)

Ou commenter tous les logs :
```javascript
// console.log('...');
```

**Note** : Garder les logs peut être utile pour le support utilisateur.

---

## Variables localisées pour frontend (récapitulatif)

Dans [class-el-assets.php](wp-content/plugins/eventlist/includes/class-el-assets.php#L145-L155), les variables JavaScript suivantes sont maintenant localisées pour le frontend (`el_script_admin`) :

| Variable | Type | Valeur | Utilisation |
|----------|------|--------|-------------|
| `el_admin_object` | Object | `{media_title, media_button, prefix}` | Upload média |
| `el_custom_tax_slug` | Array | Slugs des taxonomies custom | Menu admin |
| `pagenow` | String | `'vendor-profile'` | Détection page (compatibilité admin.min.js) |

**Importance** : Ces variables sont nécessaires pour que `admin.min.js` fonctionne sur le frontend sans erreurs.

---

## Fichiers modifiés - Résumé

| Fichier | Lignes modifiées | Type de modification |
|---------|-----------------|---------------------|
| [profile-presentation-validation.js](wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js) | 1-10, 15-56, 161-246 | Debugging + retry logic + fix sélecteur |
| [class-el-assets.php](wp-content/plugins/eventlist/includes/class-el-assets.php) | 154-155, 251 | Localisation pagenow + version |

**Total** : 2 fichiers modifiés

---

## Prochaines étapes

1. **Vider le cache navigateur** (Ctrl+F5)
2. **Aller sur `?vendor=profile`**
3. **Ouvrir la console** (F12)
4. **Suivre les logs** pour identifier le problème
5. **Partager les logs** si le problème persiste

**Questions à répondre via les logs** :
- ❓ Le script se charge-t-il ?
- ❓ TinyMCE est-il détecté ?
- ❓ L'éditeur 'description' est-il trouvé ?
- ❓ L'initialisation réussit-elle ?
- ❓ Le compteur est-il créé ?
- ❓ Le bouton de sauvegarde est-il trouvé ?

---

## Conclusion

La version 1.0.2 apporte :
- ✅ **Logs complets** pour diagnostic précis
- ✅ **Stratégie multi-tentatives** (10 essais sur 5 secondes)
- ✅ **Fix sélecteur bouton** (basé sur `name="el_update_presentation"`)
- ✅ **Fix erreur pagenow** (localisation manquante)

**Ces modifications permettent de diagnostiquer pourquoi le compteur ne s'affiche pas.**

Avec les logs, nous pourrons identifier exactement le problème et le corriger.

---

**Document créé le** : 2025-10-26
**Auteur** : V1 Le Hiboo - Développement Claude
