# Configuration Cloudflare Turnstile CAPTCHA

## Obtenir les clés Cloudflare Turnstile

1. **Créer un compte Cloudflare** (si pas déjà fait)
   - Aller sur https://dash.cloudflare.com/sign-up

2. **Accéder à Turnstile**
   - Dans le dashboard Cloudflare, aller dans la section **Turnstile**
   - URL: https://dash.cloudflare.com/?to=/:account/turnstile

3. **Créer un widget**
   - Cliquer sur "Add site"
   - Nom du site: "LeHiboo Contact Form"
   - Domaine: votre-domaine.com (ex: lehiboo.dilios.me)
   - Widget Mode: "Managed" (recommandé)

4. **Récupérer les clés**
   Vous obtiendrez 2 clés:
   - **Site Key** (publique) - commence par `0x4AAAAAAA...`
   - **Secret Key** (privée) - commence par `0x4AAAAAAA...` aussi

## Installation des clés

### 1. Clé publique (Site Key)

**Fichier**: `eventlist/content-single-event.php` (ligne 222)

Remplacer:
```php
<div class="cf-turnstile" data-sitekey="0x4AAAAAAAx9YourSiteKeyHere" data-theme="light"></div>
```

Par:
```php
<div class="cf-turnstile" data-sitekey="VOTRE_SITE_KEY_ICI" data-theme="light"></div>
```

### 2. Clé secrète (Secret Key)

**Fichier**: `functions.php` (ligne 148)

Remplacer:
```php
$secret_key = 'YOUR_SECRET_KEY_HERE';
```

Par:
```php
$secret_key = 'VOTRE_SECRET_KEY_ICI';
```

**⚠️ IMPORTANT**: Ne jamais committer la clé secrète dans Git!

### 3. Optionnel: Passer la Site Key via wp_localize_script

**Fichier**: `functions.php` (ligne 34)

La Site Key est déjà disponible via:
```php
'turnstile_sitekey' => '0x4AAAAAAAx9YourSiteKeyHere'
```

Remplacer par votre vraie clé.

## Options de configuration Turnstile

### Thème
```html
data-theme="light"  <!-- Thème clair (par défaut) -->
data-theme="dark"   <!-- Thème sombre -->
data-theme="auto"   <!-- Automatique selon le système -->
```

### Taille
```html
data-size="normal"    <!-- Taille normale (par défaut) -->
data-size="compact"   <!-- Taille compacte -->
```

### Langue
```html
data-language="fr"    <!-- Français -->
data-language="en"    <!-- Anglais -->
data-language="auto"  <!-- Automatique (par défaut) -->
```

## Test du CAPTCHA

### Mode Test (gratuit, illimité)
Cloudflare propose des clés de test:

**Site Key de test**:
```
1x00000000000000000000AA
```

**Secret Key de test**:
```
1x0000000000000000000000000000000AA
```

Ces clés acceptent toujours le CAPTCHA (utile pour le développement).

### Vérifier que ça fonctionne

1. Ouvrir le formulaire de contact
2. Remplir les champs
3. Le widget Turnstile devrait apparaître
4. Cocher la case (ou validation automatique selon le mode)
5. Envoyer le formulaire
6. Vérifier la console pour les logs

## Dépannage

### Le widget n'apparaît pas
- Vérifier que le script est chargé: `https://challenges.cloudflare.com/turnstile/v0/api.js`
- Vérifier la console pour les erreurs JavaScript
- Vérifier que la Site Key est correcte

### "CAPTCHA invalide"
- Vérifier que la Secret Key est correcte
- Vérifier que le domaine correspond à celui configuré dans Cloudflare
- Vérifier les logs dans le dashboard Cloudflare Turnstile

### Erreur de validation
- Vérifier que `$_POST['cf-turnstile-response']` existe
- Vérifier que l'API Cloudflare répond (logs serveur)
- Tester avec les clés de test d'abord

## Documentation officielle

- **Turnstile Docs**: https://developers.cloudflare.com/turnstile/
- **Dashboard**: https://dash.cloudflare.com/?to=/:account/turnstile
- **API Reference**: https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
