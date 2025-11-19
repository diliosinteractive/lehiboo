# 🔧 GUIDE - GESTION AUTOMATIQUE DES TAXONOMIES

**Date** : 5 octobre 2025
**Problème résolu** : Synchronisation automatique du nombre de taxonomies
**Fichier** : `wp-content/themes/meup-child/functions.php`

---

## ❓ POURQUOI CE PROBLÈME EXISTE

### Design défaillant du plugin EventList

Le plugin EventList utilise un système **mal conçu** pour gérer les taxonomies personnalisées :

1. Il demande à l'utilisateur de **déclarer à l'avance** combien de taxonomies il veut
2. Il utilise ce nombre dans une boucle `for` pour générer les taxonomies
3. **MAIS** si le nombre configuré ne correspond pas au nombre réel de filtres déclarés → taxonomies manquantes !

**Exemple du problème** :
```php
// Dans les réglages : Total Custom Taxonomy = 2
// Dans le code : 3 taxonomies déclarées (el_1, el_2, el_3)
// Résultat : Seules el_1 et el_2 sont affichées, el_3 est ignorée ! ❌
```

---

## ✅ SOLUTION IMPLÉMENTÉE

### Synchronisation automatique

Le code dans `functions.php` fait maintenant :

**1. Détection automatique**
```php
// Compte tous les filtres register_taxonomy_el_* déclarés
foreach ( array_keys( $wp_filter ) as $tag ) {
    if ( preg_match( '/^register_taxonomy_el_(\d+)$/', $tag, $matches ) ) {
        $max_taxonomy = max( $max_taxonomy, (int) $matches[1] );
    }
}
```

**2. Synchronisation en base de données**
```php
// Met à jour l'option WordPress automatiquement
$options['el_total_taxonomy'] = $max_taxonomy;
update_option( 'ova_eventlist_general', $options );
```

**3. Exécution au bon moment**
```php
add_action( 'admin_init', 'meup_child_sync_taxonomy_count' );
// S'exécute à chaque chargement de page admin
// Garantit que le nombre est toujours synchronisé
```

---

## 📝 COMMENT AJOUTER UNE NOUVELLE TAXONOMIE

### C'est maintenant ULTRA SIMPLE !

**1. Ajouter une seule ligne dans `functions.php`** :

```php
// Taxonomies existantes
add_filter( 'register_taxonomy_el_1', function ($params){ return array( 'slug' => 'eljob', 'name' => esc_html__( 'Job', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eltime', 'name' => esc_html__( 'Time', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_3', function ($params){ return array( 'slug' => 'elpublic', 'name' => esc_html__( 'Public', 'meup-child' ) ); } );

// AJOUTER ICI :
add_filter( 'register_taxonomy_el_4', function ($params){ return array( 'slug' => 'elniveau', 'name' => esc_html__( 'Niveau', 'meup-child' ) ); } );
add_filter( 'register_taxonomy_el_5', function ($params){ return array( 'slug' => 'eltheme', 'name' => esc_html__( 'Thème', 'meup-child' ) ); } );
```

**2. C'est tout !**

Le nombre total sera **automatiquement détecté et synchronisé** lors du prochain chargement de page admin.

**PAS BESOIN** de :
- ❌ Modifier manuellement le nombre dans `Events → Settings → General`
- ❌ Toucher à la base de données
- ❌ Régénérer les permaliens (sauf première fois)
- ❌ Vider les caches

---

## 🔍 VÉRIFICATION

### Comment savoir si ça fonctionne ?

**1. Aller dans** `Events → Settings → General`

**2. Chercher** "Total Custom Taxonomy"

**3. Vérifier** que le nombre affiché correspond au nombre de lignes `add_filter( 'register_taxonomy_el_*' ...)` dans votre code

**Exemple** :
- Code : 3 lignes `register_taxonomy_el_*`
- Réglage : "Total Custom Taxonomy" = **3** ✅

### Vérifier les taxonomies dans le menu

**Aller dans** `Events → Custom Taxonomy`

**Vous devriez voir** toutes vos taxonomies listées :
- Job
- Time
- Public
- Niveau (si ajoutée)
- Thème (si ajoutée)
- Etc.

---

## 🚨 SI LE NOMBRE NE SE MET PAS À JOUR

### Solutions rapides

**1. Forcer la synchronisation**

Aller sur **n'importe quelle page admin** de WordPress.
Le hook `admin_init` va s'exécuter et synchroniser automatiquement.

**2. Vider le cache d'options**

Si vous utilisez un cache d'objets persistant (Redis, Memcached) :
```bash
wp cache flush
```

**3. Vérifier que le code est bien chargé**

Ajouter temporairement un `die()` dans la fonction :
```php
function meup_child_sync_taxonomy_count() {
    die('La fonction s\'exécute bien !'); // ← Test
    // ... reste du code
}
```

Aller sur une page admin. Si vous voyez le message, le code fonctionne.

**4. Désactiver le cache de requêtes**

Ajouter temporairement dans `wp-config.php` :
```php
define('WP_CACHE', false);
```

---

## 📊 AVANTAGES DE CETTE SOLUTION

### Avant (manuel)

❌ Ajouter une taxonomie dans le code
❌ Aller dans Events → Settings → General
❌ Modifier manuellement "Total Custom Taxonomy"
❌ Sauvegarder
❌ Vider les caches
❌ Si oubli → taxonomie invisible !

### Après (automatique)

✅ Ajouter une taxonomie dans le code
✅ **C'est tout !**

---

## 🔧 CODE TECHNIQUE

### Ligne par ligne

```php
add_action( 'admin_init', 'meup_child_sync_taxonomy_count' );
// Hook WordPress qui s'exécute sur CHAQUE page admin
// Garantit la synchronisation fréquente

function meup_child_sync_taxonomy_count() {
    global $wp_filter;
    // Accès à tous les hooks WordPress enregistrés

    $max_taxonomy = 0;

    if ( isset( $wp_filter ) && is_array( $wp_filter ) ) {
        foreach ( array_keys( $wp_filter ) as $tag ) {
            // Parcourir tous les noms de hooks

            if ( preg_match( '/^register_taxonomy_el_(\d+)$/', $tag, $matches ) ) {
                // Chercher les hooks qui correspondent au pattern
                // register_taxonomy_el_1, register_taxonomy_el_2, etc.

                $num = (int) $matches[1];
                // Extraire le numéro (1, 2, 3, ...)

                if ( $num > $max_taxonomy ) {
                    $max_taxonomy = $num;
                    // Garder le plus grand numéro trouvé
                }
            }
        }
    }

    if ( $max_taxonomy > 0 ) {
        $options = get_option( 'ova_eventlist_general', array() );
        // Lire les options du plugin

        if ( ! isset( $options['el_total_taxonomy'] ) ||
             $options['el_total_taxonomy'] != $max_taxonomy ) {
            // Vérifier si mise à jour nécessaire

            $options['el_total_taxonomy'] = $max_taxonomy;
            update_option( 'ova_eventlist_general', $options );
            // Mettre à jour en base de données
        }
    }
}
```

---

## 🎯 CAS D'USAGE

### Exemple 1 : Ajouter 2 nouvelles taxonomies

**Dans `functions.php`, ajouter** :
```php
add_filter( 'register_taxonomy_el_4', function ($params){
    return array( 'slug' => 'elniveau', 'name' => esc_html__( 'Niveau', 'meup-child' ) );
} );

add_filter( 'register_taxonomy_el_5', function ($params){
    return array( 'slug' => 'elduree', 'name' => esc_html__( 'Durée', 'meup-child' ) );
} );
```

**Résultat automatique** :
- Nombre total → 5
- Taxonomies visibles → Job, Time, Public, Niveau, Durée ✅

### Exemple 2 : Supprimer une taxonomie

**Commenter la ligne** dans `functions.php` :
```php
// add_filter( 'register_taxonomy_el_2', function ($params){ return array( 'slug' => 'eltime', 'name' => esc_html__( 'Time', 'meup-child' ) ); } );
```

**Résultat automatique** :
- Nombre total → 2 (el_1 et el_3 restent)
- Taxonomies visibles → Job, Public ✅

---

## ⚠️ LIMITATIONS

### Numérotation discontinue

Si vous commentez `el_2` mais gardez `el_1` et `el_3` :
- Le nombre total sera **3** (le max trouvé)
- La boucle du plugin ira de 1 à 3
- `el_2` sera ignoré car le filtre n'existe pas
- `el_1` et `el_3` fonctionneront normalement ✅

**Bonne pratique** : Toujours numéroter séquentiellement (1, 2, 3, 4...) sans trous.

### Performance

Le hook `admin_init` s'exécute sur **chaque page admin**.

**Impact** :
- 1 requête SQL supplémentaire (get_option)
- 1 requête SQL si mise à jour nécessaire (update_option)
- Négligeable pour un site normal

**Optimisation possible** (si nécessaire) :
```php
// Ajouter un transient pour limiter les vérifications
if ( get_transient( 'meup_taxonomy_synced' ) ) {
    return; // Déjà vérifié récemment
}

// ... code de synchronisation ...

set_transient( 'meup_taxonomy_synced', true, HOUR_IN_SECONDS );
// Vérifie seulement 1x par heure
```

---

## 📚 RÉFÉRENCES

### Hooks WordPress utilisés

- `admin_init` : https://developer.wordpress.org/reference/hooks/admin_init/
- `get_option()` : https://developer.wordpress.org/reference/functions/get_option/
- `update_option()` : https://developer.wordpress.org/reference/functions/update_option/

### Regex utilisée

```regex
/^register_taxonomy_el_(\d+)$/
```

- `^` : Début de chaîne
- `register_taxonomy_el_` : Texte exact
- `(\d+)` : Un ou plusieurs chiffres (capturés)
- `$` : Fin de chaîne

---

## ✅ RÉSUMÉ

**Problème** : Le plugin EventList nécessite de configurer manuellement le nombre de taxonomies

**Solution** : Détection et synchronisation automatique via hook `admin_init`

**Avantage** : Ajout de taxonomies en 1 ligne, zéro configuration manuelle

**Fichier** : `wp-content/themes/meup-child/functions.php`

**Maintenance** : Aucune, totalement automatique

---

**Dernière mise à jour** : 5 octobre 2025
**Statut** : ✅ Fonctionnel et testé
