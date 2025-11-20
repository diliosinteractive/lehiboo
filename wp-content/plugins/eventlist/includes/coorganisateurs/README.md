# Module Co-organisateurs - Le Hiboo V1

## Vue d'ensemble

Le module **Co-organisateurs** permet aux organisations partenaires de :
1. **Établir des partenariats** au niveau compte (organisation ↔ organisation)
2. **Co-organiser des événements** en invitant leurs partenaires sur des événements spécifiques

## Architecture

### 1. Base de données

Deux tables principales :

#### `wp_el_organisation_partnerships`
Stocke les partenariats entre organisations (niveau compte).

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint(20) | ID unique |
| `organisation_principale_id` | bigint(20) | ID de l'organisation qui invite |
| `organisation_invitee_id` | bigint(20) | ID de l'organisation invitée |
| `email_invite` | varchar(255) | Email si l'organisation n'existe pas encore |
| `statut` | varchar(20) | `en_cours`, `acceptee`, `refusee`, `retiree` |
| `date_invitation` | datetime | Date d'envoi de l'invitation |
| `date_reponse` | datetime | Date de réponse |
| `invited_by_user_id` | bigint(20) | ID de l'utilisateur qui a fait l'invitation |
| `can_see_events` | tinyint(1) | Peut voir les événements (V2) |
| `can_edit_some_fields` | tinyint(1) | Peut éditer certains champs (V2) |

#### `wp_el_event_coorganisations`
Stocke les co-organisations d'événements (niveau événement).

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint(20) | ID unique |
| `event_id` | bigint(20) | ID de l'événement |
| `organisation_principale_id` | bigint(20) | ID de l'organisateur principal |
| `organisation_coorganisatrice_id` | bigint(20) | ID du co-organisateur |
| `statut` | varchar(20) | `en_cours`, `acceptee`, `refusee`, `retiree` |
| `date_invitation` | datetime | Date d'envoi de l'invitation |
| `date_reponse` | datetime | Date de réponse |
| `invited_by_user_id` | bigint(20) | ID de l'utilisateur qui a fait l'invitation |
| `role` | varchar(50) | `co-organisateur`, `partenaire`, `sponsor` |
| `can_edit` | tinyint(1) | Peut éditer l'événement (V2) |

### 2. Fichiers du module

```
/wp-content/plugins/eventlist/includes/coorganisateurs/
├── class-el-coorganisateurs.php           # Point d'entrée principal
├── class-el-coorg-database.php            # Gestion des tables BDD
├── class-el-partnership.php               # CRUD partenariats
├── class-el-event-coorganisation.php      # CRUD co-organisations d'événements
├── class-el-coorg-ajax.php                # Handlers AJAX
├── class-el-coorg-notifications.php       # Notifications email
├── class-el-coorg-helpers.php             # Fonctions utilitaires
├── assets/
│   ├── css/
│   │   └── coorganisateurs.css            # Styles
│   └── js/
│       └── coorganisateurs.js             # Scripts
└── README.md                              # Ce fichier
```

### 3. Templates

```
/wp-content/plugins/eventlist/templates/
├── vendor/
│   ├── partenariats.php                   # Dashboard "Mes Partenariats"
│   ├── coorganisations.php                # Dashboard "Événements co-organisés"
│   └── __edit-event-general.php           # Formulaire d'événement (modifié)
└── parts/
    └── event-coorganisers.php             # Affichage public des co-orgs
```

## Utilisation

### Flow 1 : Créer un partenariat

1. **Organisation A** va dans **Dashboard > Mes Partenariats**
2. Clique sur **"Inviter un partenaire"**
3. Recherche l'organisation B ou saisit un email
4. **Organisation B** reçoit une notification email
5. **Organisation B** va dans son Dashboard et **accepte** ou **refuse**
6. Si acceptée, les deux organisations deviennent partenaires

### Flow 2 : Co-organiser un événement

1. **Organisation A** édite ou crée un événement
2. Dans la section **"Ajouter des co-organisateurs"** :
   - Sélectionne un partenaire accepté
   - Choisit le rôle (co-organisateur, partenaire, sponsor)
   - Clique sur **"Ajouter"**
3. **Organisation B** reçoit une notification email
4. **Organisation B** va dans **Dashboard > Événements co-organisés**
5. **Accepte** ou **refuse** l'invitation
6. Si acceptée, B apparaît comme co-organisateur sur la page publique de l'événement

### Flow 3 : Se retirer

#### D'un partenariat
- Dans **Dashboard > Mes Partenariats**
- Clic sur **"Clôturer"** pour mettre fin au partenariat
- Aucun nouveau co-org ne peut être créé, mais les existants restent

#### D'un événement
- Dans **Dashboard > Événements co-organisés**
- Clic sur **"Se retirer"** pour ne plus être co-organisateur

## Hooks AJAX

| Action | Description | Paramètres |
|--------|-------------|-----------|
| `el_search_organisations` | Recherche d'organisations (autocomplete) | `search` |
| `el_invite_partner` | Invite un partenaire | `org_id`, `email` |
| `el_accept_partnership` | Accepte une invitation de partenariat | `partnership_id` |
| `el_refuse_partnership` | Refuse une invitation de partenariat | `partnership_id` |
| `el_retire_partnership` | Clôture un partenariat | `partnership_id` |
| `el_add_event_coorganiser` | Ajoute un co-org à un événement | `event_id`, `org_id`, `role` |
| `el_remove_event_coorganiser` | Retire un co-org d'un événement | `coorg_id` |
| `el_accept_event_coorganisation` | Accepte une invitation de co-org | `coorg_id` |
| `el_refuse_event_coorganisation` | Refuse une invitation de co-org | `coorg_id` |
| `el_retire_event_coorganisation` | Se retire d'un événement | `coorg_id` |

## Classes principales

### EL_Partnership

```php
// Créer une invitation
EL_Partnership::create_invitation( $org_principale_id, $org_invitee_id, $email, $invited_by_user_id );

// Récupérer les partenaires acceptés
$partners = EL_Partnership::get_accepted_partners( $org_id );

// Accepter / Refuser / Retirer
EL_Partnership::accept( $partnership_id );
EL_Partnership::refuse( $partnership_id );
EL_Partnership::retire( $partnership_id );
```

### EL_Event_Coorganisation

```php
// Créer une invitation de co-org
EL_Event_Coorganisation::create_invitation( $event_id, $org_principale_id, $org_coorg_id, $role );

// Récupérer les co-orgs d'un événement
$coorganisers = EL_Event_Coorganisation::get_for_event( $event_id );

// Récupérer les co-orgs acceptés (affichage public)
$accepted = EL_Event_Coorganisation::get_accepted_coorganisers( $event_id );

// Accepter / Refuser / Retirer
EL_Event_Coorganisation::accept( $coorg_id );
EL_Event_Coorganisation::refuse( $coorg_id );
EL_Event_Coorganisation::retire( $coorg_id );
```

### EL_Coorg_Helpers

```php
// Récupérer le nom d'une organisation
$name = EL_Coorg_Helpers::get_organisation_name( $user_id );

// Récupérer les données complètes
$data = EL_Coorg_Helpers::get_organisation_data( $user_id );

// Badge de statut
echo EL_Coorg_Helpers::get_status_badge( 'acceptee' );

// Vérifier un partenariat accepté
$has_partnership = EL_Coorg_Helpers::has_accepted_partnership( $org_id_1, $org_id_2 );
```

## Affichage public

Pour afficher les co-organisateurs sur une page publique d'événement :

```php
<?php
// Dans votre template d'événement
echo el_get_template( '/parts/event-coorganisers.php', array( 'event_id' => get_the_ID() ) );
?>
```

Ou directement :

```php
<?php
$event_id = get_the_ID();
$coorganisers = EL_Event_Coorganisation::get_accepted_coorganisers( $event_id );

if ( ! empty( $coorganisers ) ) {
    foreach ( $coorganisers as $coorg ) {
        $org_name = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_coorganisatrice_id );
        echo '<div>' . esc_html( $org_name ) . '</div>';
    }
}
?>
```

## Notifications email

Le module envoie automatiquement des emails pour :

1. **Invitation de partenariat** (organisation existante)
2. **Invitation de partenariat** (nouvelle organisation)
3. **Partenariat accepté**
4. **Partenariat refusé**
5. **Invitation de co-organisation d'événement**
6. **Co-organisation acceptée**
7. **Co-organisation refusée**

Les templates d'emails sont dans [`class-el-coorg-notifications.php`](class-el-coorg-notifications.php).

## Installation

Le module est automatiquement activé lors de l'activation du plugin EventList.

Les tables sont créées via :
```php
register_activation_hook( __FILE__, 'el_plugin_activate' );
```

Pour forcer la création des tables :
```php
EL_Coorg_Database::create_tables();
```

## Pages requises

Le module nécessite que les pages suivantes soient accessibles :

- `/vendor/partenariats/` - Dashboard des partenariats
- `/vendor/coorganisations/` - Dashboard des co-organisations
- `/inscription-partenaire/` - Inscription d'un nouveau partenaire

## Statuts

### Partenariats
- `en_cours` - Invitation envoyée, en attente
- `acceptee` - Partenariat actif
- `refusee` - Invitation refusée
- `retiree` - Partenariat clôturé

### Co-organisations
- `en_cours` - Invitation envoyée, en attente
- `acceptee` - Co-organisation active
- `refusee` - Invitation refusée
- `retiree` - Co-organisateur s'est retiré

## Roadmap V2

- [ ] Permissions granulaires (édition de champs spécifiques)
- [ ] Relancer une invitation en attente
- [ ] Statistiques sur les partenariats
- [ ] Export des partenariats en CSV
- [ ] Filtres et recherche avancés
- [ ] Historique complet des actions
- [ ] API REST pour intégrations tierces

## Support

Pour toute question ou problème, consultez le code source ou contactez l'équipe de développement.

---

**Version:** 1.0.0
**Date:** 2025-01-20
**Auteur:** Le Hiboo Development Team
