# Système de Messages pour Partenaires - Le Hiboo

## Vue d'ensemble

Le système de messages permet aux visiteurs de contacter les organisateurs d'événements directement depuis la page de l'événement. Tous les messages sont :
- Envoyés par email à l'organisateur
- Sauvegardés dans la base de données WordPress
- Accessibles via le dashboard partenaire
- Protégés par CAPTCHA Cloudflare Turnstile

## Version

**3.4.0** - Système de messagerie partenaire

## Composants

### 1. Custom Post Type: `organizer_message`

**Fichier**: `/wp-content/themes/meup-child/functions.php` (lignes 221-287)

**Enregistrement**:
```php
register_post_type( 'organizer_message', array(
    'public'          => false,
    'show_ui'         => true,
    'show_in_menu'    => false,
    'capability_type' => 'post',
    'capabilities'    => array(
        'create_posts' => 'do_not_allow', // Empêche création manuelle
    ),
    'supports'        => array( 'title', 'editor', 'author' ),
));
```

**Métadonnées stockées**:
- `_from_name`: Nom de l'expéditeur
- `_from_email`: Email de l'expéditeur
- `_event_id`: ID de l'événement concerné
- `_sent_date`: Date d'envoi (format MySQL)
- `_is_read`: Statut lu/non lu (0 ou 1)
- `_email_sent`: Email envoyé avec succès (0 ou 1)
- `_email_error`: Message d'erreur si échec d'envoi

### 2. Formulaire de Contact

**Fichier**: `/wp-content/themes/meup-child/eventlist/content-single-event.php` (lignes 187-243)

**Champs**:
- Nom (requis)
- Email (requis)
- Message (requis)
- CAPTCHA Cloudflare Turnstile (requis)
- Event ID (hidden)
- Nonce de sécurité

**Ouverture**: Clic sur "Envoyer un message" dans la carte organisateur

**JavaScript**: `/assets/js/single-event-airbnb.js`
- `contactPopup()`: Gestion de l'ouverture/fermeture
- `submitContactForm()`: Soumission AJAX avec validation CAPTCHA

### 3. AJAX Handler

**Fichier**: `/wp-content/themes/meup-child/functions.php` (lignes 90-219)

**Actions WordPress**:
```php
add_action( 'wp_ajax_send_organizer_message', 'handle_send_organizer_message' );
add_action( 'wp_ajax_nopriv_send_organizer_message', 'handle_send_organizer_message' );
```

**Processus**:
1. Vérification du nonce WordPress
2. Sanitization des données (name, email, message)
3. Récupération automatique de l'email organisateur via `post_author`
4. Validation du CAPTCHA Cloudflare Turnstile
5. Sauvegarde du message en base de données
6. Envoi de l'email via `wp_mail()`
7. Retour JSON de succès/erreur

### 4. Dashboard Partenaire - Page Messages

**Fichier Template**: `/wp-content/themes/meup-child/eventlist/vendor/messages.php`

**URL d'accès**: `?vendor=messages` (via shortcode `[el_member_account]`)

**Fonctionnalités**:
- Liste de tous les messages reçus par l'organisateur
- Filtrage par utilisateur connecté (`post_author`)
- Badge avec nombre de messages non lus
- Affichage: nom expéditeur, email, événement, date
- Clic pour voir le message complet
- Bouton "Marquer comme lu"
- Bouton "Répondre" (lien mailto)
- Pagination (20 messages par page)
- State vide si aucun message

**Colonnes du tableau**:
| Statut | De | Activité | Date | Actions |
|--------|-----|----------|------|---------|
| Lu/Non lu | Nom + Email | Lien événement | Date/heure | Bouton "Voir" |

### 5. Menu Sidebar Vendor

**Fichier**: `/wp-content/plugins/eventlist/templates/vendor/sidebar.php` (lignes 77-108)

**Section**: "Communication"

**Lien**:
```html
<li class="menu_vendor_messages">
    <a href="?vendor=messages">
        <i class="icon_mail_alt"></i>
        Messages
        <span class="message_count_badge">3</span>
    </a>
</li>
```

**Badge compteur**: Affiche le nombre de messages non lus en temps réel

### 6. Routing

**Fichier**: `/wp-content/plugins/eventlist/includes/vendor/class-el-vendor.php` (lignes 166-172)

**Case ajouté**:
```php
case 'messages':
    if( el_is_vendor() && apply_filters( 'el_manage_vendor_show_messages', true ) ){
        $template = apply_filters( 'el_shortcode_messages_template_messages', 'vendor/messages.php' );
    }else{
        $template = apply_filters( 'el_shortcode_myaccount_template_profile', 'vendor/profile.php' );
    }
    break;
```

## Sécurité

### 1. WordPress Nonce
- Formulaire: `wp_nonce_field( 'contact_organizer_nonce', 'contact_nonce' )`
- Vérification: `wp_verify_nonce( $_POST['contact_nonce'], 'contact_organizer_nonce' )`

### 2. AJAX Nonce
- Création: `wp_create_nonce( 'el_ajax_nonce' )`
- Localisé dans: `el_ajax_object.nonce`

### 3. Cloudflare Turnstile CAPTCHA
**Site Key**: `0x4AAAAAAB75T9T-6xfs5mqd`
**Secret Key**: `0x4AAAAAAB75T-X7AoX9nIt-M-0G2ndG4zU`

**Validation serveur**:
```php
$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
wp_remote_post( $verify_url, array(
    'body' => array(
        'secret'   => $secret_key,
        'response' => $turnstile_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    )
));
```

### 4. Sanitization
- `sanitize_text_field()` pour nom
- `sanitize_email()` pour email
- `sanitize_textarea_field()` pour message
- `intval()` pour IDs

### 5. Capacités
- Seuls les vendors/partenaires (`el_is_vendor()`) peuvent voir les messages
- Les messages sont en `post_status = 'private'`
- Filtrés par `post_author` (current user)
- Pas de création manuelle (`create_posts => 'do_not_allow'`)

## Email Envoyé

**De**: Nom du site <admin_email>
**Reply-To**: Nom expéditeur <email_expéditeur>
**À**: Email organisateur (récupéré via `get_the_author_meta()`)

**Sujet**: `[LeHiboo] Message concernant: Titre de l'événement`

**Corps**:
```
Vous avez reçu un message concernant votre événement:

Événement: [Titre]
Lien: [URL]

De: [Nom] ([Email])

Message:
[Contenu du message]

---
Cet email a été envoyé via le formulaire de contact de LeHiboo
```

## Filtres WordPress Disponibles

```php
// Afficher/masquer la page messages dans le menu
apply_filters( 'el_manage_vendor_show_messages', true )

// Modifier le template utilisé
apply_filters( 'el_shortcode_messages_template_messages', 'vendor/messages.php' )
```

## Styles CSS

**Fichiers**:
- `/wp-content/themes/meup-child/vendor-messages.css` (styles page messages)
- `/wp-content/themes/meup-child/single-event-airbnb.css` (popup formulaire)

**Classes principales**:
- `.el_vendor_messages_wrapper`: Container principal
- `.vendor_messages_table`: Table des messages
- `.message_row.message_unread`: Message non lu (fond orange léger)
- `.message_row.message_read`: Message lu
- `.message_details`: Row détails (toggleable)
- `.message_count_badge`: Badge compteur dans sidebar

## JavaScript

**Fichier**: `/assets/js/single-event-airbnb.js`

**Fonctions**:
```javascript
EventAirbnb.contactPopup()         // Initialisation popup
EventAirbnb.openContactPopup()     // Ouvrir popup
EventAirbnb.closeContactPopup()    // Fermer popup
EventAirbnb.submitContactForm($form) // Soumission AJAX
```

**Template messages.php**:
```javascript
$('.btn_view_message').on('click', ...) // Toggle détails message
```

## Base de Données

**Table**: `wp_posts` (type: `organizer_message`)
**Table meta**: `wp_postmeta`

**Structure**:
```sql
-- Post
post_type = 'organizer_message'
post_status = 'private'
post_author = [ID organisateur]
post_title = '[Titre événement] - Message de [Nom expéditeur]'
post_content = [Message complet]

-- Meta
_from_name = 'Jean Dupont'
_from_email = 'jean@example.com'
_event_id = 123
_sent_date = '2025-10-21 14:30:00'
_is_read = 0
_email_sent = 1
```

## Workflow Complet

1. **Visiteur** clique sur "Envoyer un message" sur page événement
2. **Popup** s'ouvre avec formulaire
3. **Visiteur** remplit nom, email, message
4. **Visiteur** valide CAPTCHA Cloudflare
5. **Visiteur** clique "Envoyer"
6. **JavaScript** vérifie présence du token CAPTCHA
7. **AJAX** envoie les données vers `admin-ajax.php`
8. **PHP** vérifie nonce WordPress
9. **PHP** récupère et sanitize les données
10. **PHP** récupère l'email organisateur depuis l'auteur de l'événement
11. **PHP** valide le token CAPTCHA auprès de Cloudflare
12. **PHP** sauvegarde le message en base de données
13. **PHP** envoie l'email via `wp_mail()`
14. **PHP** retourne succès/erreur en JSON
15. **JavaScript** affiche message succès et ferme popup
16. **Organisateur** reçoit email ET peut voir le message dans son dashboard
17. **Organisateur** se connecte et va sur "Messages" dans menu
18. **Organisateur** voit la liste avec badge "Non lu"
19. **Organisateur** clique "Voir" pour lire le message
20. **Organisateur** peut marquer comme lu ou répondre via email

## Dépannage

### Le menu "Messages" n'apparaît pas
- Vérifier que `el_is_vendor()` retourne true
- Vérifier le filtre: `add_filter( 'el_manage_vendor_show_messages', '__return_true' )`

### La page messages est vide
- Vérifier que le template existe: `eventlist/vendor/messages.php`
- Vérifier le case dans `class-el-vendor.php`
- Vérifier les permissions du fichier

### Les messages ne s'affichent pas
- Vérifier `post_type = 'organizer_message'`
- Vérifier `post_status = 'private'`
- Vérifier `post_author` correspond à l'utilisateur connecté
- Vérifier avec WP_Query debug: `var_dump( $messages_query->request )`

### L'email n'est pas envoyé
- Tester `wp_mail()` avec un email simple
- Vérifier configuration SMTP du serveur
- Installer plugin SMTP (WP Mail SMTP, etc.)
- Vérifier les logs serveur

### Le CAPTCHA échoue
- Vérifier Site Key dans `functions.php` ligne 34
- Vérifier Secret Key dans `functions.php` ligne 155
- Tester avec les clés de test Cloudflare
- Vérifier que le domaine est autorisé dans Cloudflare

## Versions

- **3.3.0**: Contact popup initial
- **3.3.1**: Fix popup display
- **3.3.2**: Cloudflare Turnstile CAPTCHA
- **3.3.3**: Message history database
- **3.4.0**: Vendor dashboard messages page + menu

## TODO Future

- [ ] Notification email quand nouveau message reçu
- [ ] Système de réponse intégré (sans quitter le dashboard)
- [ ] Archivage de messages
- [ ] Recherche/filtrage par événement
- [ ] Export CSV des messages
- [ ] Statistiques (temps de réponse moyen, etc.)
- [ ] Templates d'emails personnalisables
- [ ] Pièces jointes
- [ ] Marquer comme spam
