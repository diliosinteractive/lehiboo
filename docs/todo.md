PLAN DE DÉVELOPPEMENT GLOBAL - V1 ESPACE PARTENAIRE LE HIBOO
Vue d'ensemble
Le plan est structuré en 5 parties principales + 3 modules transversaux, avec environ 21 tâches majeures. Estimation : 80-120 heures de développement.
PARTIE 1 : PAGE DE CONNEXION (/member-login/)
Objectifs
Restructurer la page avec deux blocs distincts
Traduire entièrement en français
Améliorer l'UX (boutons, images, modale)
Tester "Mot de passe oublié" et "Se souvenir de moi"
Fichiers à modifier
templates/vendor/login.php (template principal)
assets/css/frontend/vendor/_login.scss (si existe, sinon créer)
includes/user/class-el-user.php (logique de connexion)
Fonctions WP : wp_login_form(), wp_lostpassword_url()
Actions techniques
Modifier login.php pour structurer en 2 colonnes (Connexion | Inscription)
Remplacer tous les labels anglais par français
Modifier le style du bouton principal (fond orange #FF6B35, texte blanc)
Ajouter icône X pour fermer la modale (si applicable)
Remplacer l'image de fond par un visuel aligné à la charte Le Hiboo
Vérifier le workflow "Mot de passe oublié" (redirection, email)
Dépendances
Aucune (peut être fait en premier)
PARTIE 2 : STRUCTURE GÉNÉRALE DU PROFIL
Objectifs
Remplacer la navigation horizontale (onglets) par un menu vertical à gauche
Créer une nouvelle structure visuelle avec sidebar + contenu
Réorganiser les onglets selon les spécifications
Fichiers à modifier
templates/vendor/profile.php (refonte complète)
assets/css/frontend/vendor/_my-profile.scss (refonte complète)
assets/css/frontend/vendor/_sidebar.scss (déjà modifié)
Structure cible
┌────────────────────────────────────────┐
│ [Photo Profil]  Nom Utilisateur       │
├───────────────┬────────────────────────┤
│ 📄 Info Perso │                        │
│ 🏢 Org.       │   CONTENU DE L'ONGLET  │
│ 📝 Présenta.  │                        │
│ 📍 Localisa.  │                        │
│ 🔒 Mot passe  │                        │
└───────────────┴────────────────────────┘
Actions techniques
Créer un conteneur flex avec sidebar gauche (250px fixe) + contenu fluide
Transformer ul.vendor_tab en navigation verticale avec icônes
Implémenter le système de navigation (hash-based ou AJAX)
Ajouter les nouveaux onglets : Mon Organisation, Présentation
Renommer "Adresse" en "Localisation"
Masquer conditionnellement les onglets selon le rôle (client vs partenaire)
Dépendances
Doit être fait avant les onglets individuels
PARTIE 3 : ONGLETS DU PROFIL (DÉTAILS)
3.1 - Onglet : Informations Personnelles
Objectifs :
Traduire tous les champs en français
Transformer "Poste" en menu déroulant administrable
Rendre l'email modifiable
Ajouter un champ "Statut du compte" (lecture seule)
Supprimer "Adresse" et "Description" (déplacés vers Organisation/Présentation)
Ajouter icônes d'information (?) et astérisques rouges (*) pour champs obligatoires
Fichiers :
templates/vendor/profile.php (section #author_profile)
Créer : includes/admin/class-el-admin-settings-poste.php (gérer options du menu déroulant)
Ajouter user meta : user_status_compte (géré côté admin)
Actions :
Traduire : First Name → Prénom, Last Name → Nom, Display Name → Nom affiché, etc.
Remplacer <input type="text" name="user_job"> par <select name="user_job">
Alimenter le select depuis une option WordPress (get_option('el_poste_options'))
Créer une page de settings dans l'admin WP pour gérer les postes
Rendre le champ email modifiable (retirer disabled)
Ajouter un champ lecture seule : "Statut du compte" (affiche get_user_meta($user_id, 'account_status'))
Masquer les champs "Adresse" et "Description" (déplacés vers autres onglets)
3.2 - Onglet : Mon Organisation (NOUVEAU)
Objectifs :
Créer un nouvel onglet pour centraliser les infos administratives de l'entité
Ajouter 6 nouveaux champs (nom, rôle, statut juridique, type, SIREN, date création)
Fichiers à créer/modifier :
templates/vendor/profile.php (ajouter section #author_organisation)
Créer : includes/admin/class-el-admin-settings-organisation.php (gérer les options)
User meta à ajouter : org_name, org_role, org_statut_juridique, org_type_structure, org_siren, org_date_creation
Nouveaux champs :
Champ	Type	Obligatoire	Source données
Nom de l'organisation	Text	✅	-
Rôle de l'organisation	Checkbox multiple	✅	Option WP (ex: Organisateur, Lieu, Prestataire)
Statut juridique	Select	✅	Option WP (ex: Association, SARL, SAS, Auto-entrepreneur)
Type de structure	Checkbox multiple	✅	Option WP (ex: Culturel, Sportif, Éducatif)
SIREN	Text (9 chiffres)	✅	-
Date de création	Date picker	❌	-
Actions :
Créer le template HTML pour l'onglet
Créer les meta keys user (préfixe : org_)
Créer une page de settings admin pour gérer les options des menus déroulants
Ajouter validation SIREN (9 chiffres uniquement)
Ajouter un datepicker pour "Date de création"
Implémenter la sauvegarde AJAX
3.3 - Onglet : Présentation (NOUVEAU - Profil Public)
Objectifs :
Créer un onglet dédié aux éléments visibles sur la fiche publique
Déplacer la "Description" depuis Informations Personnelles
Ajouter galerie, horaires, réseaux sociaux, vidéo, accessibilité, etc.
Fichiers :
templates/vendor/profile.php (ajouter section #author_presentation)
Utiliser les user meta existants : description, user_profile_social
Ajouter nouveaux meta : org_horaires, org_web, org_video_youtube, org_stationnement, org_acces_transports, org_accessibilite_pmr, org_restauration, org_boisson
Champs :
Champ	Type	Note
Description	WYSIWYG simple (sans liens)	Déplacé depuis Info Perso
Image à la une	Upload	Renommer depuis "Image à la une"
Galerie	Multi-upload	Activer
Horaires	Custom widget	Jours + heures ouverture/fermeture
Page Web	URL	Facultatif
Réseaux sociaux	Repeater	Limiter à : Facebook, Instagram, LinkedIn, TikTok, YouTube, Twitter
Vidéo de présentation	URL YouTube	Facultatif
Stationnement	Textarea	Facultatif
Accès & Transports	Textarea	Facultatif
Accessibilité PMR	Oui/Non + Textarea conditionnel	-
Restauration sur place	Oui/Non + Textarea conditionnel	-
Boisson sur place	Oui/Non + Textarea conditionnel	-
Actions :
Créer le template avec note introductive : "Les informations de cette section seront visibles sur votre profil public."
Déplacer la description (réutiliser le champ existant)
Créer un module "Horaires" (7 jours × 2 créneaux possibles)
Limiter les icônes de réseaux sociaux à 6 plateformes
Ajouter validation URL pour Page Web et Vidéo
Implémenter les champs conditionnels (Oui/Non → Textarea)
3.4 - Onglet : Localisation
Objectifs :
Simplifier le widget de localisation
Implémenter l'autocomplétion Google Maps
Pré-remplir automatiquement Ville, Code postal, Pays, GPS
Fichiers :
templates/vendor/profile.php (section #author_localisation)
assets/js/frontend/place-autocomplete-element.js (modifier/créer)
Utiliser l'API Google Maps Places Autocomplete
Champs :
Champ	Source	Modifiable
Adresse complète	Saisie + Autocomplete	✅
Ville	Auto-rempli	✅
Code postal	Auto-rempli	✅
Pays	Auto-rempli	✅
Coordonnées GPS	Auto-rempli	✅
Actions :
Intégrer Google Places Autocomplete sur le champ "Adresse"
Utiliser les callbacks JS pour extraire les composantes d'adresse
Pré-remplir les champs (mais laisser modifiables manuellement)
Stocker les coordonnées GPS dans user_latitude, user_longitude
Gérer le cas "adresse non trouvée" (saisie manuelle)
3.5 - Onglet : Mot de Passe
Objectifs :
Traduire l'interface en français
Ne pas déconnecter l'utilisateur après modification
Ajouter une note sur les prérequis du mot de passe
Afficher une notification de succès
Fichiers :
templates/vendor/profile.php (section #author_password)
includes/class-el-ajax.php (handler el_save_password)
Actions :
Traduire : Old Password → Ancien mot de passe, etc.
Modifier la logique AJAX pour ne pas appeler wp_logout()
Ajouter un message informatif : "Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial."
Afficher une notification de succès persistante (au lieu de redirection)
3.6 - Workflow d'Upload d'Images
Objectifs :
Traduire "Téléverser" → "Importer"
Désactiver la modification des métadonnées (Titre, Légende, Alt)
Supprimer le champ "URL du fichier"
Corriger le bug d'enregistrement de l'image de profil
Fichiers :
assets/js/frontend/script.min.js (code de l'uploader WordPress)
templates/vendor/profile.php (boutons d'upload)
Actions :
Modifier les labels dans wp.media.editor.open() (uploader-title, uploader-button-text)
Désactiver les onglets de métadonnées dans la modale
Masquer le champ "URL du fichier" avec CSS
Débugger la sauvegarde de author_id_image (vérifier le hook de sauvegarde)
PARTIE 4 : CRÉATION D'ÉVÉNEMENT
4.1 - Améliorations Globales
Objectifs :
Rendre les boutons d'action sticky (toujours visibles)
Désactiver "Mettre en ligne" tant que les champs obligatoires ne sont pas remplis
Améliorer la visibilité du bouton "Prévisualiser"
Fichiers :
templates/vendor/edit-event.php (boutons)
assets/css/frontend/vendor/_create-event.scss
assets/js/frontend/script.min.js (validation)
Actions :
Créer une barre sticky en haut ou bas de page avec les 3 boutons
Implémenter une validation JS en temps réel des champs obligatoires
Désactiver visuellement le bouton "Mettre en ligne" (opacité + cursor:not-allowed)
Ajouter un compteur de champs manquants (ex: "5 champs requis restants")
4.2 - Onglet : Informations de Base
Objectifs :
Ajouter limite de 100 caractères au nom de l'événement
Remplacer "Étiquettes" par 3 taxonomies distinctes : Thématiques, Événements Spéciaux, Saisons
Supprimer le champ "Heure Zone"
Fichiers :
includes/admin/views/metaboxes/_basic.php
includes/class-el-post-types.php (enregistrer nouvelles taxonomies)
Nouvelles taxonomies :
event_thematique (Cuisine, Numérique, Art, Sport, Nature, etc.)
event_special (Fête de la musique, Journées du patrimoine, Halloween, etc.)
event_saison (Printemps, Été, Automne, Hiver)
Actions :
Limiter le champ "Nom de l'événement" à 100 caractères (attribut maxlength)
Enregistrer les 3 nouvelles taxonomies (hiérarchiques, sélection multiple)
Remplacer le champ event_tag par 3 champs séparés (checkboxes)
Supprimer le champ "Time Zone" (ova_mb_event_time_zone)
Créer une interface admin pour gérer les termes de chaque taxonomie
4.3 - Onglet : Présentation (événement) (NOUVEAU)
Objectifs :
Créer un nouvel onglet regroupant Description, Image, Galerie, Page Web, Réseaux sociaux, Vidéo
Pré-remplir les champs avec les données de l'organisation (modifiables)
Fichiers :
includes/admin/views/metaboxes/_presentation.php (créer)
includes/admin/metabox/class-el-admin-metabox-presentation.php (créer)
includes/admin/class-el-admin-metaboxes.php (enregistrer)
Champs :
Champ	Type	Obligatoire	Pré-rempli depuis
Description	WYSIWYG	✅ (min 500 caractères)	user_meta: description
Image à la une	Upload	✅	user_meta: org_image
Galerie	Multi-upload	❌	-
Page Web	URL	❌	user_meta: org_web
Réseaux sociaux	Repeater	❌	user_meta: user_profile_social
Vidéo YouTube	URL	❌	user_meta: org_video_youtube
Actions :
Créer la classe de metabox en étendant EL_Abstract_Metabox
Créer le fichier de vue avec les champs
Implémenter la logique de pré-remplissage (récupérer les user meta de l'auteur)
Ajouter une validation côté JS : description minimum 500 caractères
Enregistrer la metabox dans class-el-admin-metaboxes.php
4.4 - Onglet : Localisation (événement)
Objectifs :
Proposer 3 options : Localisation de l'organisation (défaut), Autre lieu partenaire, Nouvelle adresse
Ajouter une case "L'événement se déroule en ligne / à la maison"
Fichiers :
includes/admin/views/metaboxes/_location.php (modifier ou créer)
Utiliser le champ existant ova_mb_event_venue_id
Logique :
[Radio] Localisation de mon organisation (sélectionné par défaut)
[Radio] Sélectionner un autre lieu partenaire
    → [Select] Liste des venues (post_type: venue)
[Radio] Nouvelle adresse
    → [Input] Adresse avec autocomplétion Google Maps
    → Champs auto-remplis : Ville, CP, Pays, GPS

[Checkbox] Événement en ligne / à la maison
Actions :
Ajouter 3 boutons radio : org_location, other_venue, custom_location
Afficher conditionnellement les champs selon le choix (JS)
Pré-remplir "Localisation de mon organisation" avec les user meta de l'auteur
Créer un select des venues (query post_type=venue)
Ajouter l'autocomplétion Google Maps pour "Nouvelle adresse"
Ajouter une checkbox "En ligne" qui désactive tous les champs d'adresse
4.5 - Onglet : Billetterie (Simplifié pour V1)
Objectifs :
Simplifier drastiquement l'interface
Proposer 3 choix clairs : Pas d'inscription, Inscription gratuite, Lien externe
Supprimer les options complexes (types de sièges, sections, etc.)
Fichiers :
includes/admin/views/metaboxes/_ticket.php (refonte complète)
includes/admin/metabox/class-el-admin-metabox-ticket.php (simplifier)
Structure cible :
Billetterie :
[Radio] Pas d'inscription nécessaire (défaut)
[Radio] Inscription gratuite sur Le Hiboo
    → Nom du billet : [Input]
    → Nombre total de places : [Number]
    → Nombre max de places par réservation : [Number]
[Radio] Lien vers une billetterie externe
    → URL de la billetterie : [Input]
    → Texte du bouton : [Input] (ex: "Réserver sur Fnac Spectacles")
    → Module tarifs :
        [Repeater] Nom du tarif : [Input] | Prix : [Number] €
Actions :
Remplacer l'interface actuelle par 3 boutons radio
Afficher conditionnellement les champs selon le choix
Supprimer les champs : ticket_type, seat_type, section, map
Créer un repeater simple pour afficher les tarifs (billetterie externe uniquement)
Valider les URLs (billetterie externe)
4.6 - Onglet : Calendrier
Objectifs :
Traduire intégralement l'interface en français
Proposer un choix initial : "Événement ponctuel" (défaut) ou "Événement récurrent"
Intégrer une vue calendrier en temps réel pour visualiser les créneaux ajoutés
Fichiers :
includes/admin/views/metaboxes/_calendar.php (traduire + améliorer UX)
templates/vendor/create-tickets-calendar.php (ajouter vue calendrier)
assets/libs/fullcalendar/ (utiliser la lib existante)
Structure cible :
[Radio] Événement ponctuel
    → Date : [Date picker]
    → Heure de début : [Time picker]
    → Heure de fin : [Time picker]

[Radio] Événement récurrent
    → Date de début : [Date picker]
    → Date de fin : [Date picker]
    → Récurrence : [Select] Quotidien, Hebdomadaire, Mensuel
    → Jours concernés : [Checkboxes] Lun, Mar, Mer, Jeu, Ven, Sam, Dim

[Vue Calendrier] (FullCalendar)
    Affiche en temps réel les créneaux ajoutés
Actions :
Traduire tous les labels : Start Date → Date de début, etc.
Ajouter un choix radio en haut pour sélectionner ponctuel/récurrent
Afficher conditionnellement les champs
Intégrer FullCalendar avec les données en temps réel
Permettre la suppression de créneaux depuis le calendrier
PARTIE 5 : FONCTIONNALITÉS POST-V1
Objectifs
Désactiver les fonctionnalités non prioritaires pour la V1
Les rendre accessibles via des filtres pour activation future
Fonctionnalités à désactiver :
Coupons de réduction : Masquer l'onglet "Coupons" dans la création d'événement
Staff Member : Masquer l'onglet "Staff"
Services Supplémentaires : Garder pour tests mais non affiché par défaut
Abonnement Partenaire : Supprimer toute référence aux packages payants
Grille tarifaire : Reporter (sauf pour billetterie externe - voir 4.5)
Actions :
Ajouter des filtres dans includes/admin/class-el-admin-metaboxes.php :
if (apply_filters('el_v1_show_coupons', false)) {
    new EL_Admin_Metabox_Coupon();
}
Masquer les onglets dans templates/vendor/edit-event.php avec des conditions
Supprimer les références aux packages dans templates/vendor/sidebar.php
Ajouter une constante define('EL_V1_MODE', true); pour contrôler globalement
MODULES TRANSVERSAUX
T1 - Taxonomies Personnalisées
Objectifs :
Créer 3 nouvelles taxonomies pour remplacer event_tag
Les rendre administrables depuis l'admin WordPress
Actions :
Enregistrer les taxonomies dans includes/class-el-post-types.php :
event_thematique (hiérarchique)
event_special (non-hiérarchique)
event_saison (non-hiérarchique)
Créer des termes par défaut lors de l'activation du plugin
Ajouter les taxonomies dans le menu admin WP (sous "Événements")
T2 - Interface Admin (Options)
Objectifs :
Créer des pages de settings pour gérer les options des menus déroulants
Pages à créer :
Postes (pour "Informations Personnelles")
Rôles d'organisation (pour "Mon Organisation")
Statuts juridiques (pour "Mon Organisation")
Types de structure (pour "Mon Organisation")
Actions :
Créer includes/admin/class-el-admin-settings-organisation.php
Utiliser l'API Settings de WordPress
Créer une page dans le menu admin : "EventList > Réglages Partenaire"
Implémenter un repeater pour ajouter/supprimer des options
T3 - Compilation des Assets
Objectifs :
Compiler les fichiers SCSS modifiés
Minifier les JS si nécessaire
Optimiser les images
Actions :
Installer les dépendances Node.js (si build system existe)
Compiler assets/css/frontend/style.scss → style.css
Vérifier que tous les nouveaux fichiers SCSS sont importés
Minifier si applicable
Tester le responsive design
ORDRE DE DÉVELOPPEMENT RECOMMANDÉ
Phase 1 : Fondations (Semaines 1-2)
✅ Analyse complète (FAIT)
🔧 Partie 2 : Refonte structure profil (navigation verticale)
🔧 T2 : Créer l'interface admin pour les options
🔧 T1 : Créer les nouvelles taxonomies
Phase 2 : Profil Partenaire (Semaines 3-4)
🔧 Partie 3.1 : Onglet Informations Personnelles
🔧 Partie 3.2 : Onglet Mon Organisation (NOUVEAU)
🔧 Partie 3.3 : Onglet Présentation (NOUVEAU)
🔧 Partie 3.4 : Onglet Localisation (améliorer)
🔧 Partie 3.5 : Onglet Mot de Passe (améliorer)
🔧 Partie 3.6 : Workflow d'upload d'images
Phase 3 : Création d'Événement (Semaines 5-6)
🔧 Partie 4.1 : Boutons d'action (sticky, états)
🔧 Partie 4.2 : Onglet Informations de Base
🔧 Partie 4.3 : Onglet Présentation (événement) (NOUVEAU)
🔧 Partie 4.4 : Onglet Localisation (événement)
🔧 Partie 4.5 : Onglet Billetterie (simplifier)
🔧 Partie 4.6 : Onglet Calendrier (améliorer)
Phase 4 : Finitions (Semaine 7)
🔧 Partie 1 : Page de connexion (refonte)
🔧 Partie 5 : Désactiver fonctionnalités post-V1
🔧 T3 : Compiler et optimiser les assets
✅ Tests : Validation complète