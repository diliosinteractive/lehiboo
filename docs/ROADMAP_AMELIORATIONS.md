# 🚀 ROADMAP AMÉLIORATIONS LEHIBOO

**Projet** : Lehiboo.com - Plateforme d'événements et billetterie
**Date création** : 5 octobre 2025
**Statut** : En cours de développement

---

## 📋 PRIORITÉS ACTUELLES

### ✅ EN COURS
- [ ] **Traduction française complète** - Éléments front-end visibles par les clients

---

## 🎯 SEO - PRIORITÉ HAUTE

### 1. Schema.org / JSON-LD pour Événements
**Impact** : ⭐⭐⭐⭐⭐ (Critique pour SEO Google)
**Effort** : 3-4 heures
**Fichiers concernés** :
- `wp-content/plugins/eventlist/includes/event/`
- Créer : `class-el-schema-event.php`

**Actions** :
- [ ] Implémenter JSON-LD Event Schema
- [ ] Propriétés : startDate, endDate, location, offers, performer, organizer
- [ ] EventStatus (EventScheduled, EventCancelled, EventPostponed)
- [ ] EventAttendanceMode (OfflineEventAttendanceMode, OnlineEventAttendanceMode, MixedEventAttendanceMode)
- [ ] AggregateRating si système d'avis
- [ ] Hook sur `wp_head` pour injection

**Référence** : https://schema.org/Event

---

### 2. Activer l'API REST WordPress
**Impact** : ⭐⭐⭐⭐ (SEO + développement futur)
**Effort** : 15 minutes
**Fichier** : `wp-content/plugins/eventlist/includes/class-el-post-types.php`

**Actions** :
- [ ] Ligne 152 : Changer `'show_in_rest' => false` à `'show_in_rest' => true`
- [ ] Tester endpoints : `/wp-json/wp/v2/event`
- [ ] Ajouter custom fields dans REST response
- [ ] Documentation API pour développeurs tiers

---

### 3. URLs Sémantiques Optimisées
**Impact** : ⭐⭐⭐⭐ (SEO + UX)
**Effort** : 2 heures
**Fichier** : `wp-content/plugins/eventlist/includes/class-el-post-types.php`

**Actions** :
- [ ] Modifier structure de rewrite (ligne 147-151)
- [ ] Format proposé : `/evenements/%year%/%monthnum%/%event_cat%/%postname%/`
- [ ] Fallback : `/evenements/%event_cat%/%postname%/`
- [ ] Tester redirections 301
- [ ] Breadcrumbs Schema avec RDFa

---

### 4. Open Graph & Twitter Cards
**Impact** : ⭐⭐⭐⭐ (Partage social)
**Effort** : 1-2 heures

**Actions** :
- [ ] Méta tags OG pour événements
- [ ] og:type = "event"
- [ ] Twitter Cards (summary_large_image)
- [ ] Image par défaut si pas de featured image
- [ ] Hook avec AIOSEO ou custom

---

### 5. Sitemap XML Événements
**Impact** : ⭐⭐⭐ (Indexation Google)
**Effort** : 1 heure

**Actions** :
- [ ] Sitemap dédié `/sitemap-events.xml`
- [ ] Priorité dynamique (événements à venir = 0.9, passés = 0.3)
- [ ] lastmod basé sur date de modification
- [ ] Exclure événements annulés
- [ ] Intégration avec sitemap index WordPress

---

## 🎫 RÉSERVATION EN LIGNE - PRIORITÉ MOYENNE

### 6. Export Calendrier (.ics)
**Impact** : ⭐⭐⭐⭐ (UX + conversion)
**Effort** : 2 heures

**Actions** :
- [ ] Bouton "Ajouter à mon calendrier" sur événements
- [ ] Génération fichier .ics (RFC 5545)
- [ ] Support : Google Calendar, Outlook, Apple Calendar
- [ ] VTIMEZONE pour gestion fuseaux horaires
- [ ] VALARM pour rappels

---

### 7. Email Automation Amélioré
**Impact** : ⭐⭐⭐⭐ (Engagement)
**Effort** : 3 heures
**Fichiers** :
- `wp-content/plugins/eventlist/includes/cron/class-el-cron.php`
- `wp-content/plugins/eventlist/includes/email/`

**Actions** :
- [ ] Reminder J-7 avant événement
- [ ] Reminder J-1 avant événement
- [ ] Reminder H-2 avant événement
- [ ] Email post-événement (feedback/merci)
- [ ] Email si événement modifié/annulé
- [ ] Template emails personnalisables
- [ ] Cron job WP optimisé

---

### 8. Dynamic Pricing (Tarification Dynamique)
**Impact** : ⭐⭐⭐⭐ (Revenus)
**Effort** : 4-5 heures

**Actions** :
- [ ] Early bird pricing (X% avant date limite)
- [ ] Last minute deals (réduction J-3)
- [ ] Bulk pricing (tiers de quantité)
- [ ] Promo codes avec expiration
- [ ] Countdown timer front-end
- [ ] Stock management par tier

---

### 9. UX Checkout Amélioré
**Impact** : ⭐⭐⭐⭐ (Conversion)
**Effort** : 3 heures

**Actions** :
- [ ] Guest checkout simplifié (sans compte)
- [ ] Progress bar multi-step (3 étapes max)
- [ ] Autofill adresse (Google Places API)
- [ ] Validation en temps réel
- [ ] Récapitulatif sticky sidebar
- [ ] Trust badges (paiement sécurisé)
- [ ] Exit-intent popup (abandon panier)

---

### 10. Waiting List (Liste d'Attente)
**Impact** : ⭐⭐⭐ (Revenus perdus)
**Effort** : 2 heures

**Actions** :
- [ ] Formulaire inscription si complet
- [ ] Email automatique si place disponible
- [ ] Timer réservation (15min pour valider)
- [ ] Dashboard vendor pour gérer waiting list

---

### 11. Seat Selection Visuelle
**Impact** : ⭐⭐⭐ (UX premium)
**Effort** : 8-10 heures

**Actions** :
- [ ] SVG map interactive pour salles
- [ ] Catégories de sièges (VIP, Standard, etc.)
- [ ] Verrouillage temporaire pendant sélection
- [ ] Mobile-friendly touch
- [ ] Admin : Éditeur de plan de salle

---

## 🚀 PERFORMANCE - PRIORITÉ MOYENNE

### 12. Lazy Loading Optimisé
**Impact** : ⭐⭐⭐ (Vitesse)
**Effort** : 1 heure

**Actions** :
- [ ] Lazy load images événements (IntersectionObserver)
- [ ] Lazy load Google Maps API
- [ ] Defer JavaScript non-critique
- [ ] Preload critical CSS

---

### 13. Cache & Transients
**Impact** : ⭐⭐⭐⭐ (Performance DB)
**Effort** : 2 heures

**Actions** :
- [ ] Object cache pour requêtes événements
- [ ] Transients API (12h) pour listes
- [ ] Cache invalidation sur update événement
- [ ] Redis/Memcached ready

---

### 14. Images Optimisées
**Impact** : ⭐⭐⭐ (Vitesse + SEO)
**Effort** : 1 heure

**Actions** :
- [ ] WebP avec fallback JPEG
- [ ] Responsive images (srcset)
- [ ] Compression automatique
- [ ] CDN pour assets statiques

---

## 🌍 TRADUCTION - PRIORITÉ ACTUELLE

### 15. Traduction Française Front-End
**Impact** : ⭐⭐⭐⭐⭐ (Critique)
**Effort** : 2-3 heures
**Fichiers** :
- `wp-content/plugins/eventlist/languages/`
- `wp-content/themes/meup/languages/`
- `wp-content/themes/meup-child/languages/`

**Actions** :
- [ ] Générer fichier .pot pour EventList
- [ ] Traduire tous les strings front-end
- [ ] Vérifier templates `wp-content/plugins/eventlist/templates/`
- [ ] Traduction thème MeUp si nécessaire
- [ ] Tester sur tous les templates visibles
- [ ] Format dates en français
- [ ] Devise EUR avec symbole €

**Strings prioritaires** :
- Boutons : "Book Now", "Add to Cart", "Checkout", "Download Ticket"
- Labels formulaires checkout
- Messages erreur/succès
- Navigation événements
- Filtres recherche
- Email templates

---

## 📊 TRACKING & ANALYTICS

### 16. Événements Google Analytics
**Impact** : ⭐⭐⭐ (Data)
**Effort** : 2 heures

**Actions** :
- [ ] GA4 Enhanced Ecommerce
- [ ] Tracking : view_item, add_to_cart, purchase
- [ ] Custom events : ticket_download, event_share
- [ ] Conversion funnels
- [ ] Facebook Pixel events

---

## 🔒 SÉCURITÉ

### 17. Audit Sécurité
**Impact** : ⭐⭐⭐⭐⭐ (Critique)
**Effort** : 3 heures

**Actions** :
- [ ] CSRF tokens sur tous les forms
- [ ] Nonces WordPress
- [ ] Sanitization inputs checkout
- [ ] SQL prepared statements
- [ ] XSS prevention
- [ ] Rate limiting API
- [ ] 2FA pour vendors

---

## 📱 MOBILE

### 18. Progressive Web App (PWA)
**Impact** : ⭐⭐⭐ (Mobile UX)
**Effort** : 4 heures

**Actions** :
- [ ] Service worker
- [ ] Manifest.json
- [ ] Offline mode basic
- [ ] Add to homescreen
- [ ] Push notifications (événements à venir)

---

## 🧪 TESTS & QA

### 19. Tests Automatisés
**Impact** : ⭐⭐⭐ (Qualité)
**Effort** : 5 heures

**Actions** :
- [ ] PHPUnit pour booking logic
- [ ] Tests intégration gateways (sandbox)
- [ ] E2E tests (Playwright/Cypress)
- [ ] Load testing (k6.io)

---

## 📝 NOTES

**Version EventList** : 2.0.6
**Version WordPress** : À vérifier
**PHP Version** : >= 7.1 (recommandé 8.1+)

**Dépendances critiques** :
- WooCommerce (pour packages)
- Elementor (widgets)
- CMB2 (metaboxes)
- All in One SEO Pack

**Compatibilité** :
- Multi-vendeurs ✅
- Multi-devises ❓ (à vérifier)
- WPML ready ❓ (à vérifier)
- Gutenberg ❌ (pas de blocs custom)

---

## 🎯 MÉTRIQUES DE SUCCÈS

- **SEO** : Position moyenne Google pour "événements [ville]"
- **Conversion** : Taux d'abandon panier < 30%
- **Performance** : PageSpeed > 90/100
- **Mobile** : Core Web Vitals "Good"
- **Satisfaction** : NPS > 50

---

**Dernière mise à jour** : 5 octobre 2025
**Prochaine révision** : Après implémentation traduction FR
