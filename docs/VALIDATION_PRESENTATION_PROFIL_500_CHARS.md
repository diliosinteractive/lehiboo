# Validation Présentation Profil Organisateur - 500 Caractères

## Objectif

Exiger un minimum de **500 caractères** dans la présentation de l'organisateur (page profil) pour garantir une qualité de contenu suffisante.

## Contexte

Cette validation s'applique à la **page profil de l'organisateur**, dans l'onglet **"Présentation"**, pour le champ **"Description"**.

Contrairement à la validation pour les événements (qui s'applique uniquement lors de la publication), cette validation s'applique **systématiquement** lors de l'enregistrement de la présentation.

## Fonctionnement

### Règles de validation

- **Minimum requis** : 500 caractères (hors espaces au début et à la fin)
- **Application** : Toujours (pas de distinction brouillon/publication)
- **Blocage** : Empêche la sauvegarde si < 500 caractères
- **URLs** : Toujours bloquées (validation existante conservée)

### Interface utilisateur

Le système affiche :
- **Compteur en temps réel** : Nombre de caractères manquants/actuels
- **Indicateur visuel** :
  - 🔴 Rouge : "Il manque X caractères" (< 500)
  - 🟢 Vert : "Présentation valide (X caractères)" (≥ 500)
- **Alerte au clic** : Message explicite si tentative de sauvegarde < 500 caractères

## Modifications apportées

### 1. Validation côté serveur (PHP)

**Fichier modifié :** [/wp-content/plugins/eventlist/includes/class-el-ajax.php:1226-1238](wp-content/plugins/eventlist/includes/class-el-ajax.php#L1226-L1238)

**Fonction :** `el_update_presentation()`

**Code ajouté :**
```php
// V1 Le Hiboo - Validation minimum 500 caractères pour la présentation
$description_length = mb_strlen( trim( $description ) );
if ( $description_length < 500 ) {
    wp_send_json_error( array(
        'message' => sprintf(
            __( 'La présentation doit contenir au minimum 500 caractères. Actuellement : %d caractères.', 'eventlist' ),
            $description_length
        ),
        'current_length' => $description_length,
        'required_length' => 500
    ) );
    wp_die();
}
```

**Position :** Juste après la validation des URLs (ligne 1224)

**Fonctionnement :**
- Compte les caractères avec `mb_strlen()` (support UTF-8)
- Utilise `trim()` pour retirer les espaces de début/fin
- Retourne une erreur JSON avec message explicite si < 500 caractères
- Bloque l'enregistrement

### 2. Validation côté client (JavaScript)

**Fichier créé :** [/wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js](wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js)

**Fonctionnalités :**

#### a) Compteur de caractères en temps réel
```javascript
function updateCharacterCounter() {
    const currentLength = getDescriptionLength();
    const remaining = MIN_DESCRIPTION_LENGTH - currentLength;
    // Affichage du compteur avec indicateur visuel
}
```

- Se met à jour à chaque frappe (`keyup`, `change`, `input`)
- Affiche le nombre de caractères manquants ou la validation
- Styles visuels différents selon l'état (rouge/vert)

#### b) Validation avant soumission
```javascript
function validateBeforeSubmit() {
    if (currentLength < MIN_DESCRIPTION_LENGTH) {
        alert('La présentation doit contenir au minimum 500 caractères...');
        return false;
    }
    return true;
}
```

- Intercepte le clic sur les boutons de sauvegarde
- Utilise la **capture phase** pour priorité maximale
- Bloque la soumission si < 500 caractères
- Scroll automatique vers le champ description
- Focus sur le champ

#### c) Double sécurité AJAX
```javascript
$(document).ajaxSend(function(_event, jqxhr, settings) {
    if (settings.data.indexOf('el_update_presentation') !== -1) {
        if (window.el_presentation_validation_failed === true) {
            jqxhr.abort(); // Annuler la requête
        }
    }
});
```

- Hook AJAX pour bloquer la requête si validation échouée
- Même approche que pour la validation événement (v1.0.1)

### 3. Enregistrement du script

**Fichier modifié :** [/wp-content/plugins/eventlist/includes/class-el-assets.php:245-246](wp-content/plugins/eventlist/includes/class-el-assets.php#L245-L246)

**Code ajouté :**
```php
// Profile Presentation Validation (500 chars minimum) - V1 Le Hiboo
wp_enqueue_script('el_profile_presentation_validation', EL_PLUGIN_URI.'assets/js/frontend/profile-presentation-validation.js', array('jquery'),'1.0.0',true );
```

**Conditions de chargement :**
- Uniquement sur la page profil
- Paramètre GET : `vendor=profile`

## Différences avec validation événement

| Aspect | Événement | Profil Organisateur |
|--------|-----------|---------------------|
| **Champ** | Description événement | Présentation organisateur |
| **Quand** | Uniquement publication (status="publish") | Toujours à l'enregistrement |
| **Éditeur** | TinyMCE (WYSIWYG) | Textarea simple |
| **URLs** | Autorisées (widget garde liens) | Bloquées (validation existante) |
| **Brouillon** | Autorisé sans validation | Pas de notion de brouillon |

## Fichiers créés/modifiés

### Fichiers modifiés

1. [/wp-content/plugins/eventlist/includes/class-el-ajax.php](wp-content/plugins/eventlist/includes/class-el-ajax.php)
   - Ligne 1226-1238 : Validation serveur dans `el_update_presentation()`

2. [/wp-content/plugins/eventlist/includes/class-el-assets.php](wp-content/plugins/eventlist/includes/class-el-assets.php)
   - Ligne 245-246 : Enregistrement du script

### Fichiers créés

1. [/wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js](wp-content/plugins/eventlist/assets/js/frontend/profile-presentation-validation.js)
   - Script de validation côté client
   - Compteur en temps réel
   - Blocage avant soumission

2. [/VALIDATION_PRESENTATION_PROFIL_500_CHARS.md](VALIDATION_PRESENTATION_PROFIL_500_CHARS.md)
   - Cette documentation

## Tests à effectuer

### Test 1 : Compteur en temps réel

1. Aller sur **Mon compte > Profil**
2. Cliquer sur l'onglet **"Présentation"**
3. Observer le compteur sous le champ "Description"
4. Taper du texte
5. ✅ **Résultat attendu** :
   - Compteur mis à jour en temps réel
   - Rouge avec message "Il manque X caractères" si < 500
   - Vert avec message "Présentation valide" si ≥ 500

### Test 2 : Blocage sauvegarde (< 500 caractères)

1. Remplir la description avec < 500 caractères (ex: 200 caractères)
2. Cliquer sur "Enregistrer"
3. ✅ **Résultat attendu** :
   - Alerte JavaScript : "La présentation doit contenir au minimum 500 caractères..."
   - Indication du nombre manquant
   - Scroll vers le champ description
   - Focus sur le champ
   - **Pas de sauvegarde**

**Console :**
```
Click intercepté sur bouton save presentation
Validation présentation échouée - blocage
Soumission présentation bloquée : description trop courte
```

### Test 3 : Sauvegarde réussie (≥ 500 caractères)

1. Remplir la description avec ≥ 500 caractères
2. Vérifier que le compteur est vert
3. Cliquer sur "Enregistrer"
4. ✅ **Résultat attendu** :
   - Pas d'alerte
   - Sauvegarde réussie
   - Message de succès

**Console :**
```
Click intercepté sur bouton save presentation
Validation présentation réussie
```

### Test 4 : Validation serveur (bypass JavaScript)

1. Désactiver JavaScript dans le navigateur
2. Remplir description < 500 caractères
3. Tenter de sauvegarder
4. ✅ **Résultat attendu** :
   - Erreur retournée par le serveur
   - Message : "La présentation doit contenir au minimum 500 caractères. Actuellement : X caractères."

### Test 5 : Validation URLs (existante)

1. Remplir description ≥ 500 caractères
2. Inclure une URL (http://example.com)
3. Tenter de sauvegarder
4. ✅ **Résultat attendu** :
   - Erreur : "Les liens URL ne sont pas autorisés dans la description"
   - Validation 500 caractères OK, mais validation URL échoue

## Logs de débogage

Le script ajoute des logs dans la console pour faciliter le debugging :

```javascript
console.log('Click intercepté sur bouton save presentation');
console.log('Validation présentation échouée - blocage');
console.log('Validation présentation réussie');
console.log('Soumission présentation bloquée : description trop courte');
```

## Compatibilité

- ✅ WordPress 5.0+
- ✅ jQuery
- ✅ Textarea standard (pas de TinyMCE)
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)
- ✅ Compatible mobile/tablette

## Notes techniques

### Comptage des caractères

- **Côté serveur** : `mb_strlen( trim( $description ) )`
  - Support UTF-8 avec `mb_strlen()`
  - Retrait des espaces début/fin avec `trim()`

- **Côté client** : `$('#description').val().trim().length`
  - Texte brut (pas de HTML car textarea simple)
  - Retrait des espaces début/fin

### Ordre d'exécution des validations

1. **Nonce** : Vérification de sécurité
2. **Rôle** : Vérification vendor
3. **Sanitize** : Nettoyage du contenu
4. **URLs** : Blocage des liens (existant)
5. **500 caractères** : Nouvelle validation ← **AJOUTÉ ICI**
6. **Enregistrement** : Sauvegarde des données

### Gestion des erreurs

Toutes les erreurs utilisent `wp_send_json_error()` avec :
- `message` : Message d'erreur localisé
- `current_length` : Nombre de caractères actuel (validation 500 chars)
- `required_length` : 500 (validation 500 chars)

## Récapitulatif des validations

### Page Profil - Présentation

| Validation | Règle | Moment | Blocage |
|------------|-------|--------|---------|
| **URLs** | Interdites | Toujours | ✅ Oui |
| **500 caractères** | Minimum requis | Toujours | ✅ Oui |

### Page Événement - Description

| Validation | Règle | Moment | Blocage |
|------------|-------|--------|---------|
| **URLs** | Autorisées | - | ❌ Non |
| **500 caractères** | Minimum requis | Publication uniquement | ✅ Oui (si publish) |

## Version

- **Version** : 1.0.0
- **Date** : 2025-10-26
- **Auteur** : V1 Le Hiboo CDC
- **Lié à** : VALIDATION_DESCRIPTION_500_CHARS.md
