# Vider le Cache - Page Author Profile

## Modifications CSS Appliquées

Les fichiers CSS ont été recompilés avec les nouvelles modifications :

✅ `margin-bottom: 0` sur `.author_hero_header`
✅ `padding: 40px 20px` sur `.author_page_modern`
✅ `max-width: 1200px` centré
✅ Bloc description déplacé au-dessus des événements

## Comment Vider le Cache

### 1. Cache Navigateur
**Chrome/Edge** : `Cmd + Shift + R` (Mac) ou `Ctrl + Shift + R` (Windows)
**Firefox** : `Cmd + Shift + R` (Mac) ou `Ctrl + F5` (Windows)
**Safari** : `Cmd + Option + E` puis `Cmd + R`

### 2. Cache WordPress

Si vous avez un plugin de cache installé :

#### WP Rocket
- Aller dans **Réglages > WP Rocket**
- Cliquer sur **Vider le cache**

#### W3 Total Cache
- Aller dans **Performance > Dashboard**
- Cliquer sur **Empty all caches**

#### WP Super Cache
- Aller dans **Réglages > WP Super Cache**
- Cliquer sur **Delete Cache**

#### LiteSpeed Cache
- Aller dans **LiteSpeed Cache > Toolbox**
- Cliquer sur **Purge All**

### 3. Cache Serveur (OVH, Cloudflare, etc.)

Si vous avez un CDN ou cache serveur :
- Se connecter au panel
- Vider le cache CDN/Proxy

### 4. Vérification Manuelle

Ouvrez la console développeur (F12) et vérifiez que le fichier CSS contient :

```css
.author_hero_header {
  margin-bottom: 0;
}

.author_page_modern {
  padding: 40px 20px 60px 20px;
  max-width: 1200px;
  margin: 0 auto;
}
```

**URL du CSS** :
`/wp-content/plugins/eventlist/assets/css/frontend/style.css`

### 5. Force Reload CSS (Temporaire)

Si le cache persiste, ajoutez temporairement un paramètre de version :

Dans `functions.php` du child theme, trouvez l'enqueue du CSS eventlist et ajoutez/modifiez la version :

```php
wp_enqueue_style( 'eventlist-style', $url, array(), time() ); // Force reload
```

---

## Checklist de Vérification

- [ ] Vider cache navigateur
- [ ] Vider cache WordPress
- [ ] Vider cache CDN/Serveur
- [ ] Vérifier dans inspect element que le CSS est chargé
- [ ] Vérifier que `margin-bottom: 0` est appliqué
- [ ] Vérifier que la description est au-dessus des événements
- [ ] Vérifier que le contenu central est bien élargi

---

**Date** : 2025-01-22
**Fichiers compilés** : ✅ style.css
