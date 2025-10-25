# Système de Tracking des Contacts Organisateur

## Vue d'ensemble

Ce système permet de tracker les vues des informations de contact des organisateurs (téléphone et adresse) et fournit des statistiques détaillées dans l'interface admin.

## Fonctionnalités

### Pour les Visiteurs

1. **Boutons de Révélation**
   - Bouton "Voir le numéro" pour révéler le numéro de téléphone
   - Bouton "Voir l'adresse" pour révéler l'adresse complète
   - Animation douce lors de la révélation
   - Tracking automatique lors du clic

2. **Emplacements**
   - Bloc organisateur (sidebar sur page activité)
   - Popup détails organisateur
   - Page profil organisateur

### Pour les Organisateurs

1. **Statistiques Détaillées**
   - Nombre de vues téléphone
   - Nombre de vues adresse
   - Total des vues
   - Visiteurs uniques
   - Filtres par période (7j, 30j, 90j, tout)

2. **Accès Admin**
   - Menu "Mes Statistiques" dans l'admin WordPress
   - Interface visuelle avec cartes statistiques

## Architecture Technique

### Base de Données

**Table:** `wp_organizer_contact_views`

Colonnes:
- `id` - ID unique de la vue
- `organizer_id` - ID de l'organisateur
- `viewer_id` - ID du visiteur (NULL si non connecté)
- `viewer_ip` - IP du visiteur
- `contact_type` - 'phone' ou 'address'
- `event_id` - ID de l'événement (si applicable)
- `context` - Contexte de la vue (single_event_card, single_event_popup, author_page, etc.)
- `viewed_at` - Date/heure de la vue

### Fichiers Modifiés

#### 1. [inc/organizer-tracking.php](inc/organizer-tracking.php)
Gestion complète du système de tracking:
- Création de la table MySQL
- Endpoint AJAX pour enregistrer les vues
- Fonction de récupération des statistiques
- Interface admin pour afficher les stats

#### 2. [assets/js/organizer-contact-reveal.js](assets/js/organizer-contact-reveal.js)
JavaScript pour gérer:
- Révélation du téléphone/adresse au clic
- Appel AJAX pour tracker la vue
- Animation de révélation

#### 3. [eventlist/author_info.php](eventlist/author_info.php)
Template modifié pour afficher:
- Boutons de révélation au lieu d'affichage direct
- Gestion du contexte (page activité vs page organisateur)
- Données tracking (organizer_id, event_id, context)

#### 4. [assets/scss/_organizer-card-optimized.scss](assets/scss/_organizer-card-optimized.scss)
Styles SCSS pour:
- Boutons de révélation
- Animations de révélation
- Responsive design

#### 5. [functions.php](functions.php)
- Inclusion du système de tracking
- Enregistrement des scripts JS
- Localisation des nonces AJAX

## Utilisation

### Côté Développeur

#### Activer/Désactiver le Tracking

```php
// Dans functions.php ou un plugin
add_filter( 'el_show_phone_info', '__return_false' ); // Désactiver téléphone
add_filter( 'el_show_address_info', '__return_false' ); // Désactiver adresse
```

#### Récupérer les Statistiques par Code

```php
// Statistiques 30 derniers jours
$stats = lehiboo_get_organizer_contact_stats( $organizer_id, array(
    'period' => '30',
    'group_by' => 'day'
) );

echo 'Vues téléphone: ' . $stats['totals']['phone_views'];
echo 'Vues adresse: ' . $stats['totals']['address_views'];
echo 'Visiteurs uniques: ' . $stats['totals']['unique_viewers'];
```

#### Personnaliser les Contextes

Les contextes de tracking permettent de savoir où la vue a été enregistrée:

- `single_event_card` - Bloc organisateur sur page activité
- `single_event_popup` - Popup détails sur page activité
- `author_page` - Sidebar sur page profil organisateur
- `author_page_popup` - Popup sur page profil organisateur

### Prévention du Spam

Le système utilise des transients WordPress pour éviter le double comptage:
- Cache de 24h par combinaison (organizer_id + contact_type + IP)
- Empêche les clics multiples d'être comptés plusieurs fois

## Sécurité

1. **Nonces WordPress**
   - Vérification nonce sur toutes les requêtes AJAX
   - Protection CSRF intégrée

2. **Sanitization**
   - Tous les inputs sont sanitizés
   - Protection contre les injections SQL via `wpdb::prepare()`

3. **Anonymisation**
   - Les IPs sont stockées pour prévenir le spam
   - Les visiteurs non connectés restent anonymes (viewer_id NULL)

## Statistiques Admin

### Accès

1. Connectez-vous à WordPress en tant qu'organisateur
2. Menu "Mes Statistiques" dans le sidebar admin
3. Sélectionnez une période

### Métriques Disponibles

- **Vues Téléphone** - Nombre total de révélations du numéro
- **Vues Adresse** - Nombre total de révélations de l'adresse
- **Total Vues** - Somme de toutes les vues
- **Visiteurs Uniques** - Nombre de visiteurs différents (connectés uniquement)

## Débogage

### Activer les Logs JavaScript

```javascript
// Dans organizer-contact-reveal.js
console.log('Contact view tracked:', contactType);
```

### Vérifier les Données

```sql
-- Voir toutes les vues d'un organisateur
SELECT * FROM wp_organizer_contact_views
WHERE organizer_id = 123
ORDER BY viewed_at DESC;

-- Statistiques rapides
SELECT
    contact_type,
    COUNT(*) as total,
    COUNT(DISTINCT viewer_ip) as unique_ips
FROM wp_organizer_contact_views
WHERE organizer_id = 123
GROUP BY contact_type;
```

## Migration/Mise à Jour

La table est créée automatiquement lors du changement de thème via le hook `after_switch_theme`.

Pour recréer la table manuellement :

```php
lehiboo_create_organizer_tracking_table();
```

## Performance

- **Indexes MySQL** - Sur organizer_id, viewer_id, contact_type, event_id, viewed_at
- **Transients** - Cache 24h pour éviter les requêtes répétées
- **AJAX Asynchrone** - Tracking non-bloquant pour l'UX

## Compatibilité

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- Navigateurs modernes (ES6+)

## Support

Pour tout problème ou question :
1. Vérifier les logs JavaScript (Console navigateur)
2. Vérifier les logs PHP (wp-content/debug.log si WP_DEBUG activé)
3. Vérifier la table MySQL existe bien
4. Vérifier les nonces sont bien générés (inspecter el_ajax_object)

## Améliorations Futures

- Export CSV des statistiques
- Graphiques de tendances
- Notifications email aux organisateurs
- Comparaison entre périodes
- Statistiques par événement
