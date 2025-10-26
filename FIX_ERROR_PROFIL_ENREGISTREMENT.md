# Fix - Erreur lors de l'enregistrement du profil

## Problème

Lors de l'enregistrement du formulaire de profil, une erreur JavaScript apparaît dans la console :

```
jquery.min.js?ver=3.7.1:2 Uncaught ReferenceError: el_custom_tax_slug is not defined
    at Object.fix_menu (admin.min.js?ver=6.8.3:1:1975)
    at Object.init (admin.min.js?ver=6.8.3:1:1937)
    at HTMLDocument.<anonymous> (admin.min.js?ver=6.8.3:40:40877)
```

## Cause

Le script `admin.min.js` est chargé sur le frontend (pages vendor) mais la variable JavaScript `el_custom_tax_slug` n'était pas localisée pour le frontend, uniquement pour l'admin.

### Contexte technique

**Dans class-el-admin-assets.php (admin uniquement) :**
```php
wp_enqueue_script('el_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', ...);
wp_localize_script( 'el_admin', 'el_custom_tax_slug', el_get_custom_taxonomy_slug_arr() );
```

**Dans class-el-assets.php (frontend vendor) :**
```php
wp_enqueue_script('el_script_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', ...);
wp_localize_script( 'el_script_admin', 'el_admin_object', array(...) );
// ❌ MANQUE: el_custom_tax_slug
```

Le même script `admin.min.js` est utilisé dans les deux contextes mais avec des handles différents :
- Admin : `el_admin`
- Frontend : `el_script_admin`

La variable `el_custom_tax_slug` était localisée uniquement pour `el_admin`, pas pour `el_script_admin`.

## Solution

### Fichier modifié
**[/wp-content/plugins/eventlist/includes/class-el-assets.php:151-152](wp-content/plugins/eventlist/includes/class-el-assets.php#L151-L152)**

### Code ajouté

```php
// V1 Le Hiboo - Fix: Ajouter el_custom_tax_slug manquant sur frontend
wp_localize_script( 'el_script_admin', 'el_custom_tax_slug', el_get_custom_taxonomy_slug_arr() );
```

### Contexte complet

**Avant :**
```php
/* Script Admin */
if( isset( $_GET['vendor'] ) && $_GET['vendor'] != '' && is_user_logged_in() ){
    wp_enqueue_script('el_script_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', array('jquery'),false,true);
    wp_localize_script( 'el_script_admin', 'el_admin_object', array(
        'media_title'  => esc_html__( 'Select media', 'eventlist' ),
        'media_button' => esc_html__( 'Select', 'eventlist' ),
        'prefix'       => OVA_METABOX_EVENT
    ) );
    // ❌ Variable manquante
}
```

**Après :**
```php
/* Script Admin */
if( isset( $_GET['vendor'] ) && $_GET['vendor'] != '' && is_user_logged_in() ){
    wp_enqueue_script('el_script_admin', EL_PLUGIN_URI.'assets/js/admin/admin.min.js', array('jquery'),false,true);
    wp_localize_script( 'el_script_admin', 'el_admin_object', array(
        'media_title'  => esc_html__( 'Select media', 'eventlist' ),
        'media_button' => esc_html__( 'Select', 'eventlist' ),
        'prefix'       => OVA_METABOX_EVENT
    ) );

    // V1 Le Hiboo - Fix: Ajouter el_custom_tax_slug manquant sur frontend
    wp_localize_script( 'el_script_admin', 'el_custom_tax_slug', el_get_custom_taxonomy_slug_arr() );
    // ✅ Variable ajoutée
}
```

## Explication technique

### Fonction `el_get_custom_taxonomy_slug_arr()`

Cette fonction retourne un tableau contenant les slugs des taxonomies personnalisées :

```php
function el_get_custom_taxonomy_slug_arr() {
    $custom_tax = array();
    // Récupère toutes les taxonomies personnalisées
    // ...
    return $custom_tax;
}
```

### Utilisation dans admin.min.js

Le script `admin.min.js` utilise `el_custom_tax_slug` dans sa fonction `fix_menu()` :

```javascript
fix_menu: function() {
    // Utilise el_custom_tax_slug pour gérer le menu admin
    // ...
}
```

Cette fonction est appelée lors de l'initialisation du script, d'où l'erreur si la variable n'existe pas.

## Impact

### Avant le fix

❌ **Erreur JavaScript** dans la console
❌ **Blocage potentiel** de certaines fonctionnalités JS
❌ **Expérience utilisateur dégradée**

### Après le fix

✅ **Pas d'erreur** dans la console
✅ **Script fonctionne correctement**
✅ **Enregistrement du profil** sans problème

## Test

### Procédure

1. **Se connecter** en tant qu'organisateur
2. **Aller sur** Mon Compte > Profil
3. **Modifier** n'importe quel champ
4. **Enregistrer** le profil
5. **Ouvrir** la console développeur (F12)

### Résultat attendu

**Avant le fix :**
```
❌ Uncaught ReferenceError: el_custom_tax_slug is not defined
```

**Après le fix :**
```
✅ Aucune erreur
✅ Profil enregistré avec succès
```

## Pages concernées

Ce fix s'applique à toutes les pages vendor :

- `?vendor=profile` (Profil)
- `?vendor=create-event` (Créer événement)
- `?vendor=listing-edit` (Modifier événement)
- `?vendor=galerie` (Galerie)
- `?vendor=wallet` (Portefeuille)
- etc.

Toutes les pages où `$_GET['vendor']` est défini.

## Fichiers modifiés

| Fichier | Modifications | Lignes |
|---------|--------------|--------|
| [class-el-assets.php](wp-content/plugins/eventlist/includes/class-el-assets.php) | Ajout localisation `el_custom_tax_slug` | 151-152 |

## Fichiers créés

| Fichier | Description |
|---------|-------------|
| [FIX_ERROR_PROFIL_ENREGISTREMENT.md](FIX_ERROR_PROFIL_ENREGISTREMENT.md) | Cette documentation |

## Notes

### Pourquoi le même script pour admin et frontend ?

Le script `admin.min.js` contient des fonctionnalités partagées entre :
- L'administration WordPress (backend)
- Les pages vendor (frontend pour organisateurs)

C'est une pratique courante pour éviter la duplication de code.

### Pourquoi des handles différents ?

Les handles différents (`el_admin` vs `el_script_admin`) permettent de :
- Éviter les conflits de chargement
- Localiser différemment selon le contexte
- Contrôler précisément où le script est chargé

### Alternative possible

Une alternative serait de :
1. Séparer les scripts admin/frontend
2. Créer un script commun pour les fonctionnalités partagées

Mais cela nécessiterait une refonte plus importante.

## Compatibilité

- ✅ WordPress 5.0+
- ✅ Pas d'impact sur l'admin
- ✅ Pas d'impact sur les autres pages frontend
- ✅ Rétrocompatible

## Version

- **Version** : 1.0.0
- **Date** : 2025-10-26
- **Auteur** : V1 Le Hiboo CDC
- **Type** : Bugfix
- **Priorité** : Haute (bloquant)
