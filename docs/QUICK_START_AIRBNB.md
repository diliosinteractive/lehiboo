# 🚀 Quick Start - Single Event Airbnb Style

## ✅ Ce qui a été fait

Transformation complète de la page événement en style **Airbnb Experiences** avec:

- ✅ **13 nouveaux templates** créés
- ✅ **4 nouveaux metabox** pour l'admin (FAQ, Inclus, Exigences, Instructions)
- ✅ **Layout 2 colonnes responsive** (mobile → tablet → desktop)
- ✅ **Widget réservation sticky** + CTA mobile flottant
- ✅ **Galerie mosaïque** (1 grande + 4 miniatures + lightbox)
- ✅ **Styles CSS complets** (700+ lignes)
- ✅ **JavaScript interactif** (sticky, accordéons, compteur invités)

---

## 📂 Fichiers Créés

### Templates (Override Theme Child)
```
/wp-content/themes/meup-child/eventlist/templates/
├── content-single-event.php          ← Template principal
└── single/
    ├── meta-line.php                 ← Ville • Catégorie • Durée
    ├── highlights.php                ← À savoir (puces clés)
    ├── gallery-mosaic.php            ← Galerie 1+4 + lightbox
    ├── booking-sticky.php            ← Widget réservation
    ├── booking-mobile-cta.php        ← CTA mobile fixe
    ├── includes.php                  ← Inclus / Non inclus
    ├── requirements.php              ← Conditions requises
    ├── meeting-point.php             ← Point de RDV
    └── faq.php                       ← FAQ accordéons
```

### Backend (Metaboxes)
```
/wp-content/themes/meup-child/includes/
└── event-metabox-extensions.php      ← 4 nouveaux metabox
```

### Assets
```
/wp-content/themes/meup-child/
├── single-event-airbnb.css           ← Styles complets (700 lignes)
└── assets/js/
    └── single-event-airbnb.js        ← JavaScript (400 lignes)
```

### Configuration
```
/wp-content/themes/meup-child/
└── functions.php                     ← Enqueue CSS/JS + include metabox
```

---

## 🎯 Comment Tester

### 1. Vérifier que les fichiers existent

```bash
cd /Users/juba/PhpstormProjects/lehiboo_v1/wp-content/themes/meup-child

# Vérifier templates
ls -la eventlist/templates/content-single-event.php
ls -la eventlist/templates/single/

# Vérifier assets
ls -la single-event-airbnb.css
ls -la assets/js/single-event-airbnb.js

# Vérifier metabox
ls -la includes/event-metabox-extensions.php
```

### 2. Accéder à l'Admin WordPress

**URL**: http://votre-site.local/wp-admin

1. Aller dans **Événements** → Choisir un événement existant
2. Vous devriez voir **4 nouveaux metabox**:
   - FAQ - Questions Fréquentes
   - Ce qui est inclus / Non inclus
   - Conditions requises
   - Instructions point de rendez-vous

### 3. Remplir les Nouveaux Champs

#### FAQ
1. Cliquer "Ajouter une question"
2. Remplir:
   - **Question**: "Que se passe-t-il en cas de pluie ?"
   - **Réponse**: "L'événement est maintenu sauf conditions extrêmes..."
3. Ajouter 2-3 questions
4. Cliquer "Mettre à jour"

#### Inclus / Non inclus
**Inclus** (une ligne par élément):
```
Matériel fourni
Guide professionnel
Repas et boissons
Support photo
```

**Non inclus**:
```
Transport jusqu'au lieu
Assurance personnelle
Pourboires
```

#### Conditions requises
```
Âge minimum: 18 ans
Bonne condition physique
Tenue confortable recommandée
Pièce d'identité obligatoire
```

#### Instructions point de RDV
```
Le point de rendez-vous se trouve à l'entrée principale du parc.
Parking gratuit disponible à 200m.
```

### 4. Voir le Résultat Frontend

1. **Sauvegarder** l'événement
2. Cliquer "Voir l'événement" ou aller sur la page publique
3. **Vider le cache** (Ctrl+Shift+R)

**Vous devriez voir:**
- ✅ Nouvelle mise en page 2 colonnes (desktop)
- ✅ Galerie en mosaïque (1 grande + 4 petites)
- ✅ Widget réservation sticky à droite
- ✅ Section "À savoir" avec puces
- ✅ Section "Inclus / Non inclus"
- ✅ Section "Conditions requises"
- ✅ Section "Point de RDV" avec bouton "Itinéraire"
- ✅ Section "FAQ" avec accordéons
- ✅ CTA mobile en bas (si < 768px)

---

## 📱 Test Responsive

### Desktop (≥1024px)
- Layout 2 colonnes
- Widget réservation sticky (scroll = reste visible)
- Galerie: 1 large à gauche + grid 2x2 à droite

### Tablet (768-1023px)
- Galerie: 1 large + grid 2x2 séparé
- Widget réservation visible mais pas sticky
- Pas de CTA mobile

### Mobile (<768px)
- Tout en colonne unique
- Galerie: stack avec bouton "Voir photos"
- CTA fixe en bas d'écran
- Cliquer CTA → scroll vers calendrier

---

## 🎨 Personnalisation Rapide

### Changer les Couleurs

Éditer `/wp-content/themes/meup-child/single-event-airbnb.css`:

```css
:root {
	--airbnb-primary: #FF385C;     /* Rouge Airbnb → Changez ici */
	--airbnb-dark: #222222;        /* Texte foncé */
	--airbnb-gray: #717171;        /* Texte gris */
	--airbnb-border: #DDDDDD;      /* Bordures */
	--airbnb-radius: 12px;         /* Arrondis boutons */
}
```

### Modifier le Sticky Offset

Si votre header fait 100px au lieu de 80px:

**CSS** (ligne 391):
```css
.event_booking_sticky_wrapper {
	top: 100px; /* Au lieu de 80px */
}
```

**JavaScript** (ligne 27):
```javascript
var headerHeight = 100; // Au lieu de 80
```

---

## 🐛 Problèmes Courants

### ❌ Le nouveau template ne s'affiche pas

**Solutions:**
1. Vider cache WordPress (si plugin cache)
2. Vider cache navigateur (Ctrl+Shift+R)
3. Vérifier permissions fichiers: `chmod 644 content-single-event.php`
4. Désactiver temporairement autres plugins

### ❌ Les styles ne s'appliquent pas

**Solutions:**
1. Inspecter avec F12 → Network → `single-event-airbnb.css` chargé ?
2. Vérifier `functions.php` ligne 24: `is_singular('event')`
3. Vider cache navigateur
4. Incrémenter version CSS dans `functions.php`: `'1.0.0'` → `'1.0.1'`

### ❌ Les metabox n'apparaissent pas

**Solutions:**
1. Vérifier `functions.php` ligne 27: `require_once ...`
2. Activer `WP_DEBUG` dans `wp-config.php`
3. Vérifier erreurs PHP dans `/wp-content/debug.log`
4. Vider cache objet (Redis/Memcached si utilisé)

### ❌ Le widget n'est pas sticky

**Solutions:**
1. Vérifier largeur écran ≥ 1024px
2. Console navigateur (F12) → Onglet "Console" → Erreurs JS ?
3. jQuery chargé ? Vérifier dans `<head>`
4. Conflit avec autre script ? Désactiver plugins JS

### ❌ FAQ ne s'ouvrent pas

**Solutions:**
1. Console → Erreurs JavaScript ?
2. Vérifier `single-event-airbnb.js` chargé
3. Conflit jQuery ? Vérifier version
4. Désactiver temporairement autres plugins (Elementor, etc.)

---

## 🔍 Vérification Rapide

### Checklist Technique

- [ ] Fichier `content-single-event.php` existe dans `meup-child/eventlist/templates/`
- [ ] 9 fichiers dans `meup-child/eventlist/templates/single/`
- [ ] Fichier `event-metabox-extensions.php` existe
- [ ] CSS `single-event-airbnb.css` existe
- [ ] JS `single-event-airbnb.js` existe
- [ ] `functions.php` modifié (lignes 24-28)
- [ ] Cache vidé (WordPress + navigateur)

### Checklist Visuelle (Frontend)

- [ ] Layout 2 colonnes visible sur desktop
- [ ] Widget réservation reste visible au scroll
- [ ] Galerie affiche 1 grande + 4 miniatures
- [ ] Clic sur image ouvre lightbox
- [ ] Section "À savoir" affichée
- [ ] Section "Inclus/Non inclus" affichée
- [ ] Section "FAQ" avec accordéons fonctionnels
- [ ] CTA mobile visible uniquement < 768px
- [ ] Bouton "Itinéraire" ouvre Google Maps

### Checklist Admin (Backend)

- [ ] Metabox "FAQ" visible dans édition événement
- [ ] Bouton "Ajouter une question" fonctionne
- [ ] Metabox "Inclus/Non inclus" visible
- [ ] Metabox "Conditions requises" visible
- [ ] Metabox "Instructions RDV" visible
- [ ] Sauvegarde fonctionne sans erreur

---

## 📖 Documentation Complète

Pour plus de détails, consultez:

**[IMPLEMENTATION_AIRBNB_SINGLE_EVENT.md](IMPLEMENTATION_AIRBNB_SINGLE_EVENT.md)**

Ce fichier contient:
- Architecture détaillée
- Mapping complet des composants
- Personnalisation avancée
- Dépannage approfondi
- Références techniques

---

## 🎯 Prochaines Étapes Recommandées

### 1. Tester un Événement Réel

- [ ] Remplir tous les nouveaux champs
- [ ] Ajouter 3-5 FAQ
- [ ] Uploader 5+ photos pour la galerie
- [ ] Tester sur mobile/tablet/desktop

### 2. Optimisations (Optionnel)

- [ ] Ajouter Schema.org JSON-LD (SEO)
- [ ] Système de notation visuelle (étoiles)
- [ ] Lazy loading images
- [ ] Animations scroll (AOS.js)

### 3. Intégration avec Roadmap

Ce template répond aux besoins suivants de votre `todo.md`:

- ✅ **SEO** → Prêt pour Schema.org
- ✅ **UX Moderne** → Design Airbnb
- ✅ **Responsive** → Mobile/Tablet/Desktop
- ✅ **Profil Partenaire** → Carte organisateur intégrée

---

## 💡 Astuces

### Désactiver Temporairement le Nouveau Template

Renommer le fichier:
```bash
mv content-single-event.php content-single-event.php.bak
```

L'ancien template du plugin sera utilisé.

### Tester sur un Seul Événement

Ajouter condition dans `functions.php`:

```php
if( is_singular('event') && get_the_ID() == 123 ) { // ID événement test
    wp_enqueue_style( 'single-event-airbnb', ... );
}
```

### Activer le Mode Debug

Dans `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Puis vérifier `/wp-content/debug.log`.

---

## ✅ Résumé

**Vous avez maintenant:**

1. ✅ **Template Airbnb complet** (13 fichiers)
2. ✅ **4 nouveaux metabox** admin
3. ✅ **Design responsive** 3 breakpoints
4. ✅ **JavaScript interactif** (sticky, accordéons, compteur)
5. ✅ **Documentation complète**

**Prêt à utiliser !** 🚀

Pour toute question, consultez `IMPLEMENTATION_AIRBNB_SINGLE_EVENT.md`.

---

**Développé le 21 Octobre 2025 avec Claude Code** 🤖
