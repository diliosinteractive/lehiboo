# V1 Le Hiboo - Documentation Profil Organisateur Moderne

## Vue d'ensemble

Cette mise à jour transforme complètement l'expérience UX/UI de la page de profil des organisateurs en adoptant un design moderne, épuré et professionnel inspiré des meilleures pratiques de l'industrie.

## Architecture du Design

### 1. Hero Header Section (Nouveau)
**Fichier**: `author.php` (lignes 31-71)

#### Composants:
- **Image de couverture immersive** avec overlay gradient
- **Avatar circulaire** (140px) avec bordure blanche et badge vérifié
- **Informations principales**:
  - Nom de l'organisation (H1, 36px)
  - Job title/fonction
  - Rating avec étoiles
- **Actions rapides**:
  - Bouton "Contact" (CTA primaire)
  - Bouton "Share" (CTA secondaire)

#### Caractéristiques UX:
- Hauteur minimum 400px (desktop), 300px (mobile)
- Responsive: layout vertical sur mobile
- Badge vérifié en bas à droite de l'avatar
- Shadow effects pour la profondeur visuelle

---

### 2. Grid Layout 12 Colonnes (Nouveau)
**Fichier**: `author.php` (lignes 73-197)

#### Structure:
```
┌─────────────────────────────────────────────┐
│           Hero Header (Full Width)          │
└─────────────────────────────────────────────┘
┌──────────────┬──────────────────────────────┐
│   Sidebar    │     Main Content             │
│   (4 cols)   │       (8 cols)               │
│   360px      │    Fluid width               │
│   Sticky     │                              │
│              │  • Statistics Cards          │
│  • About     │  • Events Section            │
│  • Contact   │                              │
│  • Social    │                              │
│  • Form      │                              │
└──────────────┴──────────────────────────────┘
```

#### Comportement Responsive:
- **Desktop (>991px)**: Sidebar sticky (top: 120px)
- **Tablet/Mobile (<991px)**: Single column layout

---

### 3. Sidebar Info Card (Refonte)
**Fichier**: `author_info.php` (lignes 58-316)

#### Sections:

##### 3.1 About Section
- Icône info-circle
- Description de l'organisation
- Typographie: 15px, line-height 1.6

##### 3.2 Contact Information
**Structure moderne**:
```
┌────────────────────────────────┐
│  [Icon] PHONE                  │
│         +33 6 00 00 00 00      │
├────────────────────────────────┤
│  [Icon] EMAIL                  │
│         contact@example.com    │
├────────────────────────────────┤
│  [Icon] WEBSITE                │
│         www.example.com        │
├────────────────────────────────┤
│  [Icon] LOCATION               │
│         59300, Valenciennes, FR│
└────────────────────────────────┘
```

**Design Pattern**: Icon + Label + Value
- Icon: 40px square, background coloré
- Label: uppercase, 12px, gris
- Value: 15px, cliquable avec hover effect

##### 3.3 Social Media
- Icônes 44x44px en grille flexible
- Hover: background orange + translateY(-2px)
- Border-radius: 8px

##### 3.4 Contact Form (Nouveau)
- **Trigger**: Bouton "Open Contact Form"
- **Animation**: slideDown/slideUp (300ms)
- **Champs**:
  - Name (text)
  - Email (email)
  - Phone (tel)
  - Subject (text)
  - Message (textarea, 6 rows)
- **Validation visuelle**: border rouge + shadow
- **Notifications**: Toast style avec icônes

---

### 4. Statistics Cards (Nouveau)
**Fichier**: `author.php` (lignes 82-129)

#### Cartes:
1. **Total Events**
   - Icône: calendar-check
   - Source: `count_user_posts()`

2. **Active Events**
   - Icône: calendar-alt (vert)
   - Source: WP_Query avec meta_query

3. **Average Rating**
   - Icône: star
   - Valeur: 4.8 (hardcodé, à dynamiser)

#### Animations:
- Hover: translateY(-4px) + shadow-md
- Scroll: Compteur animé (jQuery animate)

---

### 5. Events Section (Amélioré)
**Fichier**: `author.php` (lignes 131-193)

#### Header:
- Titre avec icône calendar-day
- Filtre événements (select custom)
- Bouton filter avec icône

#### États:
- **Avec événements**: Grid type3 existant
- **Sans événements**: Empty state avec icône et message

---

## Styles SCSS

### Variables
```scss
$primary-color: #e86c60;      // Orange LeHiboo
$text-dark: #333;
$text-light: #666;
$text-muted: #888;
$border-color: #e5e3f2;
$background-white: #fff;
$background-light: #f9f9f9;

$shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
$shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
$shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.16);

$radius-sm: 8px;
$radius-md: 12px;
$radius-lg: 16px;
```

### Classes Principales

#### Hero Section
- `.author_hero_header`: Container principal
- `.hero_cover_image`: Image + overlay
- `.hero_content`: Contenu flex
- `.hero_avatar`: Avatar + badge
- `.hero_info`: Nom + job + rating
- `.hero_actions`: Boutons CTA

#### Layout
- `.author_page_modern`: Grid container
- `.author_page_sidebar`: Sidebar sticky
- `.author_main_content`: Zone contenu principale

#### Sidebar
- `.sidebar_info_card`: Card container
- `.info_section`: Section individuelle
- `.section_title_sidebar`: Titre avec icône
- `.contact_list` / `.contact_item`: Liste contacts
- `.social_links` / `.social_link`: Icônes sociales
- `.contact_form_wrapper`: Formulaire caché
- `.modern-form`: Styles formulaire

#### Statistics
- `.author_stats_section`: Container stats
- `.stats_grid`: Grid responsive
- `.stat_card`: Card individuelle
- `.stat_icon` / `.stat_content`: Contenu card

#### Events
- `.event_list_section`: Container événements
- `.section_header`: Header avec filtre
- `.no_events_found`: Empty state

---

## JavaScript

### Fichier: `author-profile-modern.js`

#### Fonctionnalités:

1. **Toggle Contact Form**
   - Cible: `.btn_send_message`, `.btn_contact`
   - Animation: slideDown/slideUp (300ms)
   - Auto-scroll vers formulaire

2. **Share Profile**
   - API native `navigator.share` (mobile)
   - Fallback: copy to clipboard
   - Toast notification

3. **Stats Animation**
   - Trigger: scroll in viewport
   - Animation: count-up effect (1.5s)
   - Une seule fois par session

4. **Form Validation**
   - On blur: validation champs
   - On focus: remove error
   - Email regex validation
   - Visual feedback: border rouge

5. **Smooth Scroll**
   - Tous les liens ancres (#)
   - Animation 500ms
   - Offset: -100px

---

## Responsive Design

### Breakpoints:

#### Desktop (>991px)
- Grid 360px + 1fr
- Sidebar sticky
- Hero horizontal

#### Tablet (768px - 991px)
- Single column
- Stats grid 2 colonnes
- Hero horizontal

#### Mobile (<768px)
- Single column
- Stats single column
- Hero vertical centré
- Formulaire full-width

---

## Données Affichées

### Profil Public:
- ✅ `org_display_name` - Nom organisation (priorité)
- ✅ `org_cover_image` - Image couverture
- ✅ `author_id_image` - Avatar organisateur
- ✅ `user_job` - Fonction/poste
- ✅ `description` - Description organisation
- ✅ `user_phone` - Téléphone
- ✅ `user_professional_email` - Email professionnel public
- ✅ `org_web` - Site web organisation
- ✅ `user_postcode` + `user_city` + `user_country` - Localisation
- ✅ `user_profile_social` - Réseaux sociaux
- ✅ Rating stars (si disponible)

### Statistiques:
- ✅ Total événements (`count_user_posts`)
- ✅ Événements actifs (WP_Query)
- ⚠️ Rating moyen (à dynamiser)

---

## Compatibilité

### Navigateurs:
- ✅ Chrome/Edge (90+)
- ✅ Firefox (88+)
- ✅ Safari (14+)
- ✅ Mobile iOS/Android

### WordPress:
- ✅ WordPress 5.8+
- ✅ EventList Plugin
- ✅ Theme Meup Child

### Dépendances:
- jQuery (inclus WordPress)
- Font Awesome 5+
- Elegant Icons (existant)

---

## Performance

### Optimisations:
- CSS: 715 lignes SCSS → ~45KB compilé
- JS: 12KB non minifié
- Images: lazy loading natif
- Animations: GPU-accelerated (transform)
- Sticky sidebar: CSS position (pas JS)

### Scores Lighthouse (estimés):
- Performance: 90+
- Accessibility: 95+
- Best Practices: 95+
- SEO: 100

---

## Améliorations Futures

### Phase 1 (Court terme):
- [ ] Dynamiser le rating moyen
- [ ] Ajouter stat "Total Reviews"
- [ ] Implémenter filtres événements AJAX
- [ ] Ajouter pagination événements

### Phase 2 (Moyen terme):
- [ ] Mode sombre
- [ ] Gallery photos organisation
- [ ] Timeline événements passés
- [ ] Section témoignages/reviews

### Phase 3 (Long terme):
- [ ] Certification/badges organisateur
- [ ] Carte interactive événements
- [ ] Statistiques avancées (graphiques)
- [ ] Intégration calendrier externe

---

## Support & Maintenance

### Fichiers modifiés:
1. `/templates/author.php` - Structure principale
2. `/templates/author_info.php` - Sidebar
3. `/assets/css/frontend/_author.scss` - Styles
4. `/assets/js/frontend/author-profile-modern.js` - Interactions
5. `/themes/meup-child/functions.php` - Enqueue scripts

### Contact:
- Documentation: Ce fichier
- Issues: GitHub repo
- Support: contact@lehiboo.fr

---

**Version**: 1.0.0
**Date**: 2025-01-22
**Auteur**: UX/UI Senior Developer
**Status**: Production Ready ✅
