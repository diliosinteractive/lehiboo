# ➕ AJOUT TAXONOMIE "PUBLIC" - INSTRUCTIONS

**Date** : 5 octobre 2025
**Taxonomie** : Public (elpublic)
**Statut** : ✅ Code ajouté, activation requise

---

## 📋 CE QUI A ÉTÉ FAIT

### Code ajouté dans `meup-child/functions.php`

**1. Augmentation du nombre de taxonomies personnalisées**
```php
// Augmenter le nombre de taxonomies personnalisées à 3
add_filter( 'option_ova_eventlist_general', function( $options ) {
    if ( is_array( $options ) ) {
        $options['el_total_taxonomy'] = 3;
    }
    return $options;
}, 99 );
```

**2. Déclaration de la 3ème taxonomie**
```php
add_filter( 'register_taxonomy_el_3', function ($params){
    return array(
        'slug' => 'elpublic',
        'name' => esc_html__( 'Public', 'meup-child' )
    );
} );
```

---

## 🚀 ACTIONS REQUISES (IMPORTANTES)

### Étape 1 : Vider les caches

**Cache plugin** :
- WP Rocket : Vider le cache
- W3 Total Cache : Supprimer tous les caches
- WP Super Cache : Supprimer le cache

**Cache objet** (si Redis/Memcached) :
```bash
redis-cli FLUSHALL
# ou
echo "flush_all" | nc localhost 11211
```

### Étape 2 : Regénérer les permaliens (CRITIQUE)

**Via l'admin WordPress** :
```
Se connecter → Réglages → Permaliens
Cliquer sur "Enregistrer les modifications"
```

Cette action force WordPress à :
- Enregistrer la nouvelle taxonomie
- Créer les règles de réécriture d'URL
- Rendre la taxonomie active

### Étape 3 : Vérifier la taxonomie dans le back-office

**Aller dans** :
```
Events → (vérifier la sidebar gauche)
```

**Vous devriez voir** :
- ✅ Categories
- ✅ Tags
- ✅ Locations
- ✅ Job (taxonomie 1)
- ✅ Time (taxonomie 2)
- ✅ **Public** (taxonomie 3) ← Nouveau !

---

## 🔍 SI LA TAXONOMIE N'APPARAÎT PAS

### Solution 1 : Vérifier le nombre de taxonomies dans les réglages

**Aller dans** :
```
Events → Settings → General
```

**Chercher** : "Total Custom Taxonomy"
**Valeur attendue** : `3`

Si la valeur est `2`, cliquer sur "Save Changes" pour forcer la mise à jour.

### Solution 2 : Forcer le rechargement du thème child

**Désactiver/Réactiver le thème child** :
```
Apparence → Thèmes
Activer "MeUp" (thème parent)
Réactiver "MeUp Child"
```

Puis régénérer les permaliens.

### Solution 3 : Vérifier le code

**Fichier** : `wp-content/themes/meup-child/functions.php`

**Vérifier que ces lignes existent** :
```php
add_filter( 'option_ova_eventlist_general', function( $options ) {
    if ( is_array( $options ) ) {
        $options['el_total_taxonomy'] = 3;
    }
    return $options;
}, 99 );

add_filter( 'register_taxonomy_el_3', function ($params){
    return array( 'slug' => 'elpublic', 'name' => esc_html__( 'Public', 'meup-child' ) );
} );
```

### Solution 4 : Debug

**Activer le mode debug** dans `wp-config.php` :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Consulter `wp-content/debug.log` pour voir les erreurs.

---

## 📊 STRUCTURE DES 3 TAXONOMIES

| # | Slug | Nom affiché | Utilisation |
|---|------|-------------|-------------|
| 1 | eljob | Job | Type d'emploi / métier |
| 2 | eltime | Time | Horaire / durée |
| 3 | elpublic | **Public** | Public cible |

---

## 🎯 UTILISATION DE LA TAXONOMIE "PUBLIC"

### Dans le back-office

**Lors de la création/édition d'un événement** :

1. Aller dans `Events → Add New` ou éditer un événement existant
2. Dans la sidebar droite, vous verrez maintenant :
   - Categories
   - Tags
   - Locations
   - Job
   - Time
   - **Public** ← Nouveau !

3. Ajouter des termes pour "Public" :
   - Familles
   - Professionnels
   - Étudiants
   - Seniors
   - Enfants
   - Etc.

### Sur le front-end

**URLs générées** :
```
https://lehiboo.com/elpublic/familles/
https://lehiboo.com/elpublic/professionnels/
https://lehiboo.com/elpublic/etudiants/
```

**Affichage automatique** :
- Archive de taxonomie (liste des événements par public cible)
- Filtres de recherche (si configuré dans Elementor/widgets)
- Breadcrumbs

---

## 🔧 PERSONNALISATION AVANCÉE

### Changer le nom affiché

**Modifier dans** `functions.php` :
```php
add_filter( 'register_taxonomy_el_3', function ($params){
    return array(
        'slug' => 'elpublic',
        'name' => esc_html__( 'Public cible', 'meup-child' ) // ← Nom modifié
    );
} );
```

### Changer le slug d'URL

**Modifier dans** `functions.php` :
```php
add_filter( 'register_taxonomy_el_3', function ($params){
    return array(
        'slug' => 'public-cible', // ← Slug modifié
        'name' => esc_html__( 'Public', 'meup-child' )
    );
} );
```

**Important** : Après modification du slug, **regénérer les permaliens** !

---

## 🌍 TRADUCTION

### Traduire "Public" en français

La fonction `esc_html__( 'Public', 'meup-child' )` permet la traduction.

**Créer le fichier de traduction** (si nécessaire) :
```
wp-content/themes/meup-child/languages/meup-child-fr_FR.po
```

**Ajouter** :
```po
msgid "Public"
msgstr "Public"
```

Compiler :
```bash
msgfmt meup-child-fr_FR.po -o meup-child-fr_FR.mo
```

---

## ➕ AJOUTER UNE 4ÈME TAXONOMIE

Si vous avez besoin d'une 4ème taxonomie :

**1. Modifier le filtre** dans `functions.php` :
```php
add_filter( 'option_ova_eventlist_general', function( $options ) {
    if ( is_array( $options ) ) {
        $options['el_total_taxonomy'] = 4; // ← Changer à 4
    }
    return $options;
}, 99 );
```

**2. Ajouter le filtre** :
```php
add_filter( 'register_taxonomy_el_4', function ($params){
    return array(
        'slug' => 'elniveau',
        'name' => esc_html__( 'Niveau', 'meup-child' )
    );
} );
```

**3. Regénérer les permaliens**

---

## ✅ CHECKLIST DE VÉRIFICATION

Après avoir suivi les étapes :

- [ ] Code ajouté dans `meup-child/functions.php`
- [ ] Caches vidés (plugin + serveur)
- [ ] Permaliens régénérés (Réglages → Permaliens → Enregistrer)
- [ ] Taxonomie "Public" visible dans Events (sidebar back-office)
- [ ] Peut créer des termes (ex: "Familles", "Professionnels")
- [ ] Termes assignables aux événements
- [ ] URLs de taxonomie fonctionnelles (`/elpublic/terme/`)

---

## 📝 NOTES TECHNIQUES

### Pourquoi le filtre `option_ova_eventlist_general` ?

Le plugin EventList stocke ses réglages dans une option WordPress nommée `ova_eventlist_general`.

En filtrant cette option, on peut modifier dynamiquement le nombre de taxonomies personnalisées sans toucher à la base de données ou aux réglages du plugin.

### Priorité 99

Le filtre utilise la priorité `99` pour s'exécuter **après** les filtres par défaut du plugin, garantissant que notre valeur écrase celle par défaut.

---

## 🆘 SUPPORT

### Si problème persistant

**1. Vérifier les logs** :
```
wp-content/debug.log
```

**2. Tester sans cache** :
- Navigation privée
- Désactiver temporairement le cache plugin

**3. Vérifier les réglages du plugin** :
```
Events → Settings → General → Total Custom Taxonomy
```
Doit afficher `3`

**4. Re-sauvegarder les réglages** :
```
Events → Settings → General
Cliquer sur "Save Changes" même sans modification
```

---

## ✅ RÉSUMÉ

**Problème** : La taxonomie "Public" (el_3) ne s'affichait pas dans le back-office

**Cause** : Le nombre total de taxonomies était limité à 2 par défaut

**Solution** : Ajout d'un filtre pour passer à 3 taxonomies + régénération permaliens

**Statut** : ✅ Code ajouté, nécessite activation utilisateur (flush permaliens)

---

**Dernière mise à jour** : 5 octobre 2025
**Fichier modifié** : `wp-content/themes/meup-child/functions.php`
**Action requise** : Regénérer les permaliens dans WordPress
