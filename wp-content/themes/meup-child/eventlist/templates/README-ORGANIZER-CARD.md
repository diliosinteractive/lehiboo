# Bloc Organisateur Optimisé - Documentation

## 📋 Vue d'ensemble

Optimisation UX du bloc partenaire/organisateur sur la page de détail d'activité avec :
- Affichage compact des informations essentielles
- Bouton "En savoir plus" ouvrant un popup avec détails complets
- Bouton "Voir le profil" vers la page organisateur
- Bouton "Contacter l'organisateur" (modal de contact existant)
- Design moderne respectant la charte graphique Le Hiboo

## 🎨 Caractéristiques UX

### Bloc principal (Sidebar)
- **Avatar** : Photo de l'organisateur (64x64px, cercle)
- **Nom** : Nom de l'organisation ou de l'organisateur
- **Localisation** : Ville, Pays
- **Description courte** : Aperçu de 20 mots avec badge design
- **Infos rapides** : Téléphone et Email avec icônes
- **Actions CTA** :
  - "En savoir plus" (ouvre le popup)
  - "Voir le profil" (lien vers page organisateur)
- **Contact** : Bouton principal "Contacter l'organisateur"

### Popup Détails Organisateur
- **Header** : Image de couverture (si disponible) + Avatar + Nom + Localisation
- **Description complète** : Présentation détaillée de l'organisateur
- **Informations de contact** : Téléphone, Email, Site web (si disponibles)
- **Réseaux sociaux** : Liens vers tous les réseaux sociaux
- **CTA final** : "Voir toutes les activités" → Page organisateur

## 📁 Fichiers créés

### 1. Template PHP
**Fichier** : `/wp-content/themes/meup-child/eventlist/templates/author_info.php`

**Rôle** : Override du template original pour afficher le bloc organisateur optimisé.

**Données affichées** :
- Avatar (`author_id_image` ou gravatar)
- Nom organisation (`org_display_name` ou `org_name` ou `display_name`)
- Localisation (`user_city`, `user_country`)
- Description (`description` - tronquée à 20 mots pour aperçu)
- Contact (`user_phone`, `user_email` ou `user_professional_email`)
- Réseaux sociaux (`user_profile_social` ou `social_organizer`)
- Image couverture (`org_cover_image` - popup uniquement)
- Site web (`org_web` ou `user_url`)

### 2. JavaScript
**Fichier** : `/wp-content/themes/meup-child/assets/js/organizer-popup.js`

**Fonctionnalités** :
- Ouverture/fermeture du popup détails organisateur
- Ouverture/fermeture du modal de contact (existant)
- Fermeture avec touche Échap
- Fermeture par clic sur overlay
- Blocage du scroll de la page pendant ouverture
- Animations d'ouverture/fermeture fluides

**Enregistrement** : Ajouté dans `functions.php` ligne 32

### 3. Styles SCSS
**Fichier** : `/wp-content/themes/meup-child/assets/scss/_organizer-card-optimized.scss`

**Variables (Charte graphique Le Hiboo)** :
- `$primary-color: #ff601f` (Orange Le Hiboo)
- `$text-dark: #333`
- `$text-light: #666`
- `$text-muted: #888`
- `$border-color: #e5e3f2`
- `$background-white: #fff`
- `$background-light: #f9f9f9`
- `$shadow-sm, $shadow-md, $shadow-lg`
- `$radius-sm: 8px, $radius-md: 12px, $radius-lg: 16px`

**Sections stylisées** :
- `.organizer_card_optimized` : Bloc principal sidebar
- `.organizer_popup_modal` : Popup détails
- Responsive mobile (< 768px)

### 4. CSS Compilé
**Fichier** : `/wp-content/themes/meup-child/assets/css/organizer-card-optimized.css`

**Intégré dans** : `/wp-content/themes/meup-child/single-event-airbnb.css`

## 🔧 Installation

### Étape 1 : Emplacement du template
Le template override est placé dans :
```
/wp-content/themes/meup-child/eventlist/templates/author_info.php
```

WordPress chargera automatiquement ce template à la place de celui du plugin.

### Étape 2 : Enregistrement du JavaScript
Dans [functions.php:32](/wp-content/themes/meup-child/functions.php#L32), le script est enregistré :
```php
wp_enqueue_script( 'organizer-popup', get_stylesheet_directory_uri() . '/assets/js/organizer-popup.js', array('jquery'), '1.0.0', true );
```

### Étape 3 : Styles
Les styles sont automatiquement chargés via `single-event-airbnb.css` (ligne 28 de functions.php).

## 🎯 Utilisation dans le template

Le bloc organisateur est appelé dans [content-single-event.php:103](/wp-content/themes/meup-child/eventlist/content-single-event.php#L103) :
```php
<div class="event_organizer_card">
    <?php do_action( 'el_author_info' ); ?>
</div>
```

## 🔄 Hook WordPress utilisé

Le hook `el_author_info` est enregistré dans :
- **Fichier** : `/wp-content/plugins/eventlist/includes/el-template-hooks.php:403`
- **Fonction** : `el_author_info()` dans `/wp-content/plugins/eventlist/includes/el-template-functions.php:620`

## 📱 Responsive

### Desktop (> 768px)
- Bloc sidebar : 360px max-width
- Popup : 700px max-width, 90vh max-height
- Grid 2 colonnes pour les actions CTA

### Mobile (< 768px)
- Bloc sidebar : full width
- Avatar réduit à 56x56px
- Grid 1 colonne pour les actions CTA
- Popup : 95% width, 95vh height
- Padding réduits

## 🎨 Design System

### Couleurs
- **Primary** : `#ff601f` (Orange Le Hiboo)
- **Hover Primary** : `#f54700` (darken 8%)
- **Background Light** : `#f9f9f9`
- **Border** : `#e5e3f2`
- **Text Dark** : `#333`
- **Text Light** : `#666`
- **Text Muted** : `#888`

### Shadows
- **Small** : `0 2px 8px rgba(0,0,0,0.08)`
- **Medium** : `0 4px 16px rgba(0,0,0,0.12)`
- **Large** : `0 8px 32px rgba(0,0,0,0.16)`

### Border Radius
- **Small** : `8px`
- **Medium** : `12px`
- **Large** : `16px`

### Transitions
- Durée : `0.3s`
- Easing : `ease`
- Effects : `transform`, `box-shadow`, `color`, `background`

## 🧩 Compatibilité

### Dépendances
- **jQuery** : Requis pour les interactions
- **WordPress** : Compatible 5.0+
- **EventList Plugin** : Version compatible avec hooks `el_author_info`

### Navigateurs
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile Safari iOS 14+
- Chrome Mobile Android 90+

## 🔍 Données utilisateur utilisées

### Méta utilisateur
- `author_id_image` : Avatar personnalisé
- `display_name` : Nom d'affichage
- `description` : Bio/Description
- `user_phone` : Téléphone
- `user_email` : Email principal
- `user_professional_email` : Email professionnel
- `user_profile_social` : Réseaux sociaux (array)
- `user_city` : Ville
- `user_country` : Pays (code ISO)

### Méta organisation
- `org_name` : Nom organisation
- `org_display_name` : Nom public organisation (prioritaire)
- `org_cover_image` : Image de couverture
- `org_web` : Site web organisation

### Méta event (si `info_organizer` = 'checked')
- `name_organizer` : Nom organisateur custom
- `phone_organizer` : Téléphone custom
- `mail_organizer` : Email custom
- `social_organizer` : Réseaux sociaux custom (array)

## 🐛 Débogage

### Problème : Le popup ne s'ouvre pas
**Solution** :
1. Vérifier que jQuery est chargé
2. Vérifier la console JavaScript pour erreurs
3. S'assurer que `organizer-popup.js` est bien chargé
4. Vérifier que l'ID `#open_organizer_details_popup` existe

### Problème : Les styles ne s'appliquent pas
**Solution** :
1. Vider le cache WordPress
2. Recompiler le SCSS : `sass _organizer-card-optimized.scss:../css/organizer-card-optimized.css --style compressed`
3. Vérifier que `single-event-airbnb.css` contient les styles compilés

### Problème : Le modal de contact ne s'ouvre pas
**Solution** :
1. Vérifier que le script `author-profile-modern.js` est chargé
2. Vérifier la console pour erreurs AJAX
3. S'assurer que le nonce est valide

## 📝 Notes de version

### Version 1.0.0
- Création du bloc organisateur optimisé
- Popup détails avec informations complètes
- Intégration du modal de contact existant
- Design responsive mobile-first
- Respect complet de la charte graphique Le Hiboo

## 🚀 Améliorations futures possibles

1. **Statistiques organisateur** : Ajouter nombre d'activités, avis, etc.
2. **Badge vérifié** : Icône pour organisateurs vérifiés
3. **Galerie** : Slider de photos dans le popup
4. **Partage** : Bouton de partage du profil organisateur
5. **Animation** : Transitions plus élaborées (GSAP)

## 👨‍💻 Maintenance

### Recompilation SCSS
```bash
cd /wp-content/themes/meup-child/assets/scss
sass _organizer-card-optimized.scss:../css/organizer-card-optimized.css --style compressed
cat ../css/organizer-card-optimized.css >> ../../single-event-airbnb.css
```

### Mise à jour template
Après modification de `author_info.php`, vider le cache WordPress.

### Mise à jour JavaScript
Après modification de `organizer-popup.js`, vider le cache et incrémenter le numéro de version dans `functions.php`.

---

**Développé par** : Claude Code
**Date** : 2025-10-24
**Charte graphique** : Le Hiboo
**Framework** : WordPress + EventList Plugin
