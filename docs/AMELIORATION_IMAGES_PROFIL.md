# Amélioration des images de profil et couverture

## Objectif

Améliorer l'affichage et les indications pour guider les organisateurs dans l'ajout de leurs images :
- **Image de profil** : Logo de l'organisation (pas de photo de personne)
- **Image de couverture** : Image panoramique représentative de l'activité

## Modifications apportées

### 1. Image de profil (Logo organisation)

#### Localisation
**Page** : Mon Compte > Profil > Informations Personnelles

#### Améliorations

**Avant :**
```
Ajouter une image
Recommended size: 400x400px
```

**Après :**
```
Logo de votre organisation ⭐

ℹ️ Ajoutez le logo de votre organisation (pas de photo de personne).
   Cette image sera visible sur votre profil public et sur vos activités.

[Zone d'aperçu avec placeholder ou logo actuel]

🔼 Ajouter un logo

ℹ️ Format recommandé : 400x400px (carré) - PNG ou JPG - Max 2 Mo
```

#### Détails des changements

**Label amélioré :**
- ~~"Image"~~ → **"Logo de votre organisation"**
- Ajout symbole **⭐** (nécessaire pour publier activité)

**Texte d'aide explicite :**
```html
<div class="profile_image_help">
    <p class="help-text">
        <i class="icon_info_alt"></i>
        Ajoutez le logo de votre organisation (pas de photo de personne).
        Cette image sera visible sur votre profil public et sur vos activités.
    </p>
</div>
```

**Placeholder amélioré :**
- Icône image + texte "Aucun logo ajouté" si pas d'image
- Affichage du logo actuel avec bordure et style propre

**Bouton stylisé :**
```html
<button type="button" class="button add_image">
    <i class="icon_upload"></i>
    Ajouter un logo
</button>
```

**Format clarifié :**
```
ℹ️ Format recommandé : 400x400px (carré) - PNG ou JPG - Max 2 Mo
```

---

### 2. Image de couverture

#### Localisation
**Page** : Mon Compte > Profil > Présentation > Image de couverture

#### Améliorations

**Avant :**
```
Image 👁
Format recommandé : 1200x400px
```

**Après :**
```
Image de couverture 👁

ℹ️ Cette image sera affichée en haut de votre profil public.
   Privilégiez une image représentative de votre activité ou de vos locaux.

[Zone d'aperçu panoramique avec placeholder ou image actuelle]

🔼 Ajouter une image de couverture

ℹ️ Format recommandé : 1200x400px (panoramique) - PNG ou JPG - Max 2 Mo
```

#### Détails des changements

**Label amélioré :**
- ~~"Image"~~ → **"Image de couverture"**
- Conservation du symbole **👁** (visible publiquement)

**Texte d'aide explicite :**
```html
<div class="cover_image_help">
    <p class="help-text">
        <i class="icon_info_alt"></i>
        Cette image sera affichée en haut de votre profil public.
        Privilégiez une image représentative de votre activité ou de vos locaux.
    </p>
</div>
```

**Placeholder amélioré :**
```html
<div class="no-cover-placeholder">
    <i class="icon_image"></i>
    <p>Aucune image de couverture</p>
    <small>Ajoutez une image pour personnaliser votre profil</small>
</div>
```

**Format clarifié :**
```
ℹ️ Format recommandé : 1200x400px (panoramique) - PNG ou JPG - Max 2 Mo
```

---

## Styles CSS ajoutés

### Fichier créé
**[/wp-content/plugins/eventlist/assets/css/frontend/vendor/_profile-images.scss](wp-content/plugins/eventlist/assets/css/frontend/vendor/_profile-images.scss)**

### Styles principaux

#### Messages d'aide
```scss
.profile_image_help,
.cover_image_help {
    background: #f8f9fa;
    border-left: 4px solid #0073aa;
    padding: 12px 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}
```

#### Placeholders
```scss
.no-image-placeholder {
    background: #f8f9fa;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;

    i {
        font-size: 48px;
        color: #ccc;
    }
}
```

#### Boutons
```scss
.button.add_image,
.button.add_cover_image {
    background: #0073aa;
    color: #fff;
    padding: 10px 20px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.button.remove_image,
.button.remove_cover_image {
    background: #dc3545;
    color: #fff;
}
```

#### Aperçus d'images
```scss
.image-preview {
    max-width: 200px;
    height: auto;
    border-radius: 8px;
    border: 2px solid #ddd;
    padding: 4px;
}

.preview_cover_image {
    width: 100%;
    height: auto;
    border-radius: 8px;
}
```

---

## Fichiers modifiés

| Fichier | Modifications | Lignes |
|---------|--------------|--------|
| [profile.php](wp-content/plugins/eventlist/templates/vendor/profile.php) | Image profil améliorée | 186-225 |
| [profile.php](wp-content/plugins/eventlist/templates/vendor/profile.php) | Image couverture améliorée | 937-982 |
| [style.scss](wp-content/plugins/eventlist/assets/css/frontend/style.scss) | Import nouveau fichier SCSS | 96 |

## Fichiers créés

| Fichier | Description |
|---------|-------------|
| [_profile-images.scss](wp-content/plugins/eventlist/assets/css/frontend/vendor/_profile-images.scss) | Styles pour images profil/couverture |
| [AMELIORATION_IMAGES_PROFIL.md](AMELIORATION_IMAGES_PROFIL.md) | Cette documentation |

---

## Comparaison avant/après

### Image de profil

| Aspect | Avant | Après |
|--------|-------|-------|
| **Label** | "Image" | "Logo de votre organisation" ⭐ |
| **Aide** | Aucune | Texte explicite avec icône |
| **Placeholder** | Image par défaut | Icône + message clair |
| **Format** | "400x400px" | "400x400px (carré) - PNG ou JPG - Max 2 Mo" |
| **Bouton** | "Add image" | "Ajouter un logo" avec icône |

### Image de couverture

| Aspect | Avant | Après |
|--------|-------|-------|
| **Label** | "Image" | "Image de couverture" 👁 |
| **Aide** | Aucune | Texte explicite sur l'usage |
| **Placeholder** | TODO commenté | Zone avec icône et message |
| **Format** | "1200x400px" | "1200x400px (panoramique) - PNG ou JPG - Max 2 Mo" |
| **Bouton** | "Ajouter une image" | "Ajouter une image de couverture" avec icône |

---

## Bénéfices utilisateur

### Clarté améliorée

✅ **Avant** : "Add image" → Quelle image ? Pour quoi ?
✅ **Après** : "Logo de votre organisation" → Clair et précis

✅ **Avant** : Pas d'indication sur le contenu attendu
✅ **Après** : "Pas de photo de personne" → Évite les erreurs

### Guidance renforcée

✅ **Avant** : Format technique uniquement (400x400px)
✅ **Après** : Format + type + taille max + forme ("carré", "panoramique")

✅ **Avant** : Pas d'explication sur l'usage
✅ **Après** : "Visible sur votre profil public et sur vos activités"

### Expérience visuelle

✅ **Placeholders visuels** au lieu de zones vides
✅ **Icônes explicites** pour guider l'action
✅ **Messages d'aide** stylisés et visibles
✅ **Boutons cohérents** avec le design Le Hiboo

---

## Prochaines étapes (optionnel)

### Cropper automatique

**Non implémenté dans cette version**, mais préparé pour :

1. **Image profil** : Crop automatique en 400x400px
2. **Image couverture** : Crop automatique en 1200x400px

### Librairie recommandée
- [Cropper.js](https://github.com/fengyuanchen/cropperjs)
- Légère, moderne, responsive
- Support des ratios fixes

### Implémentation future
```javascript
// Exemple pour image profil (ratio 1:1)
const cropper = new Cropper(image, {
    aspectRatio: 1,
    viewMode: 1,
    guides: true,
    cropBoxResizable: false,
    cropBoxMovable: true
});

// Exemple pour couverture (ratio 3:1)
const cropper = new Cropper(image, {
    aspectRatio: 3,
    viewMode: 1
});
```

---

## Validation des images

### Recommandations actuelles

**Image profil :**
- Format : Carré (1:1)
- Dimensions : 400x400px
- Types : PNG, JPG
- Taille max : 2 Mo

**Image couverture :**
- Format : Panoramique (3:1)
- Dimensions : 1200x400px
- Types : PNG, JPG
- Taille max : 2 Mo

### Validation côté serveur (existante)

WordPress gère automatiquement :
- ✅ Types de fichiers autorisés
- ✅ Taille maximale de fichier
- ✅ Sécurité de l'upload

---

## Tests recommandés

### Test 1 : Image de profil

1. Aller sur **Mon Compte > Profil**
2. Observer le nouveau label "Logo de votre organisation" ⭐
3. Lire le message d'aide
4. Vérifier le placeholder si pas d'image
5. Cliquer sur "Ajouter un logo"
6. Uploader une image
7. Vérifier l'aperçu
8. Tester "Retirer l'image"

✅ **Résultat attendu** :
- Labels clairs et explicites
- Message d'aide visible
- Placeholder stylisé
- Boutons avec icônes
- Format bien indiqué

### Test 2 : Image de couverture

1. Aller sur **Mon Compte > Profil > Présentation**
2. Observer le nouveau label "Image de couverture"
3. Lire le message d'aide sur l'usage
4. Vérifier le placeholder panoramique
5. Cliquer sur "Ajouter une image de couverture"
6. Uploader une image
7. Vérifier l'aperçu panoramique
8. Tester "Retirer l'image"

✅ **Résultat attendu** :
- Labels clairs
- Message d'aide sur l'usage
- Placeholder panoramique stylisé
- Format 1200x400px bien indiqué

### Test 3 : Responsive

1. Ouvrir sur mobile/tablette
2. Vérifier que les placeholders s'adaptent
3. Vérifier que les boutons restent accessibles
4. Vérifier que les aperçus ne débordent pas

✅ **Résultat attendu** :
- Affichage correct sur tous les écrans
- Textes lisibles
- Boutons cliquables

---

## Notes techniques

### Classes CSS principales

```css
.author_image              /* Container image profil */
.profile_image_help        /* Message d'aide profil */
.cover_image_help          /* Message d'aide couverture */
.no-image-placeholder      /* Placeholder profil */
.no-cover-placeholder      /* Placeholder couverture */
.image-preview-container   /* Container aperçu profil */
.cover-image-container     /* Container aperçu couverture */
.format-info               /* Info format recommandé */
```

### Icônes utilisées

```
icon_info_alt     /* Info */
icon_image        /* Image placeholder */
icon_upload       /* Upload */
icon_close        /* Retirer */
```

---

## Compatibilité

- ✅ WordPress 5.0+
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)
- ✅ Responsive mobile/tablette
- ✅ Compatible avec média uploader WordPress

---

## Version

- **Version** : 1.0.0
- **Date** : 2025-10-26
- **Auteur** : V1 Le Hiboo CDC

---

## Évolutions futures

1. ✨ **Cropper automatique** (recommandé)
2. ✨ **Validation dimensions** (avertissement si trop petite/grande)
3. ✨ **Compression automatique** (optimisation taille fichier)
4. ✨ **Suggestions de dimensions** (recadrage automatique proposé)
5. ✨ **Prévisualisation finale** avant sauvegarde
