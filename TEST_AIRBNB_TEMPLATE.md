# 🧪 Test du Template Airbnb

## ✅ Vérification des Fichiers

### 1. Vérifier que tous les fichiers sont au bon endroit

```bash
# Template principal
ls -la /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/eventlist/content-single-event.php

# Templates single
ls -la /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/eventlist/single/

# CSS
ls -la /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/single-event-airbnb.css

# JS
ls -la /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/assets/js/single-event-airbnb.js

# Metabox
ls -la /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/includes/event-metabox-extensions.php
```

## 🔍 Comment Vérifier que ça Fonctionne

### Étape 1: Vider tous les caches

1. **Cache WordPress** (si plugin de cache actif)
   - WP Super Cache → "Supprimer le cache"
   - W3 Total Cache → "Performance" → "Empty All Caches"

2. **Cache navigateur**
   - Chrome/Firefox: `Ctrl+Shift+R` (Windows) ou `Cmd+Shift+R` (Mac)
   - Ou ouvrir en navigation privée

3. **Cache opcode PHP** (si applicable)
   - Redémarrer PHP-FPM ou serveur web

### Étape 2: Voir une page événement

1. Aller sur votre site WordPress en frontend
2. Cliquer sur un événement existant
3. Faire clic droit → "Inspecter" (F12)
4. Chercher dans le code source HTML

**Ce que vous devriez voir:**
```html
<!-- AIRBNB TEMPLATE LOADED -->
<article id="event_123" class="event_single event_single_airbnb">
```

**Si vous voyez ce commentaire** → ✅ Le template est chargé !

**Si vous ne le voyez pas** → ❌ Le template du plugin est encore utilisé

### Étape 3: Vérifier les styles CSS

Dans les DevTools (F12):
1. Onglet "Network"
2. Rafraîchir la page (F5)
3. Chercher `single-event-airbnb.css`

**Vous devriez voir:**
```
single-event-airbnb.css    200    stylesheet    14.6 KB
```

### Étape 4: Vérifier le JavaScript

Dans la Console (F12 → Console):
```javascript
typeof EventAirbnb
```

**Devrait retourner:** `"object"` ✅

## 🐛 Dépannage

### Le template n'est pas chargé

**Solution 1: Vérifier les permissions**
```bash
chmod 644 /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/eventlist/content-single-event.php
chmod 755 /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/eventlist/
```

**Solution 2: Vérifier le chemin**

Le template doit être exactement ici:
```
wp-content/
└── themes/
    └── meup-child/
        └── eventlist/
            ├── content-single-event.php  ← ICI (pas dans /templates/)
            └── single/
                ├── meta-line.php
                ├── highlights.php
                └── ...
```

**Solution 3: Désactiver autres plugins**

Temporairement désactiver:
- Plugins de cache
- Plugins d'optimisation
- Elementor (si conflit)

**Solution 4: Activer WP_DEBUG**

Dans `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Puis vérifier `/wp-content/debug.log`

### Les styles ne s'appliquent pas

**Vérifier dans functions.php:**
```php
// Ligne 23-26 dans functions.php
if( is_singular('event') ) {
    wp_enqueue_style( 'single-event-airbnb', get_stylesheet_directory_uri() . '/single-event-airbnb.css', array('meup-parent-style'), '1.0.0' );
    wp_enqueue_script( 'single-event-airbnb', get_stylesheet_directory_uri() . '/assets/js/single-event-airbnb.js', array('jquery'), '1.0.0', true );
}
```

**Forcer rechargement en changeant version:**
```php
'1.0.0'  →  '1.0.1'  // Changer ici
```

### Les metabox ne s'affichent pas

**Vérifier inclusion dans functions.php:**
```php
// Ligne 27-28
require_once get_stylesheet_directory() . '/includes/event-metabox-extensions.php';
```

**Vérifier erreurs PHP:**
```bash
tail -f /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/debug.log
```

## ✅ Checklist de Validation

- [ ] Commentaire `<!-- AIRBNB TEMPLATE LOADED -->` visible dans le code source
- [ ] Classe `event_single_airbnb` présente sur l'article
- [ ] CSS `single-event-airbnb.css` chargé (Network tab)
- [ ] JS `single-event-airbnb.js` chargé (Network tab)
- [ ] 4 nouveaux metabox visibles dans l'admin
- [ ] Layout 2 colonnes visible sur desktop (≥1024px)
- [ ] Widget réservation sticky au scroll
- [ ] Galerie affiche mosaïque (1+4)
- [ ] Section "À savoir" visible
- [ ] CTA mobile visible < 768px

## 🎯 Test Complet

### Créer un Événement Test

1. **Admin** → Événements → Ajouter
2. Remplir:
   - Titre: "Test Airbnb Template"
   - Contenu: Description longue
   - Date début/fin
   - Adresse + coordonnées GPS
   - Au moins 1 billet avec prix
   - Galerie: Uploader 5+ photos

3. **Remplir les nouveaux metabox:**

   **FAQ:**
   - Question 1: "L'événement est-il accessible aux PMR ?"
     - Réponse: "Oui, le lieu est entièrement accessible..."
   - Question 2: "Que se passe-t-il en cas de pluie ?"
     - Réponse: "L'événement est maintenu..."

   **Inclus:**
   ```
   Matériel fourni
   Guide professionnel
   Repas et boissons
   Support photo
   ```

   **Non inclus:**
   ```
   Transport jusqu'au lieu
   Assurance personnelle
   ```

   **Exigences:**
   ```
   Âge minimum: 18 ans
   Tenue confortable
   Pièce d'identité
   ```

   **Instructions RDV:**
   ```
   Entrée principale, parking gratuit à 200m
   ```

4. **Publier** et voir la page

### Résultat Attendu

Vous devriez voir:

1. ✅ Layout 2 colonnes (desktop)
2. ✅ Galerie mosaïque en haut
3. ✅ Widget réservation sticky à droite
4. ✅ Section "À savoir" avec puces
5. ✅ Section "Inclus / Non inclus" avec ✓ et ✗
6. ✅ Section "Conditions requises"
7. ✅ Section "Point de RDV" avec bouton "Itinéraire"
8. ✅ Section "FAQ" avec accordéons fonctionnels
9. ✅ Sur mobile: CTA fixe en bas

## 🚨 Si Rien ne Fonctionne

### Option: Revenir à l'Ancien Template

Renommer temporairement:
```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child/eventlist
mv content-single-event.php content-single-event.php.backup
```

L'ancien template du plugin sera utilisé.

### Option: Forcer le Template

Ajouter dans `functions.php` (temporaire pour debug):

```php
add_filter( 'el_get_template', function( $template_file, $template_name ) {
    error_log( 'Template requested: ' . $template_name );
    error_log( 'Template file: ' . $template_file );
    return $template_file;
}, 10, 2 );
```

Puis vérifier `debug.log` pour voir les templates chargés.

---

## 📞 Support

Si problème persistant:

1. Vérifier tous les chemins de fichiers
2. Activer WP_DEBUG et vérifier debug.log
3. Tester en désactivant tous les autres plugins
4. Vérifier permissions fichiers (644 pour fichiers, 755 pour dossiers)

---

**Dernière vérification:** 21 Octobre 2025
