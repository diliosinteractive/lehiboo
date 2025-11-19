# context

Produit : **Le Hiboo**  
Module : **Back / interface partenaire – Création & édition d’un événement / activité**  
Technologie actuelle : WordPress (custom post type + module de récurrence + module billet / réservation).  [oai_citation:0‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Les deux sources de vérité sont :
- Doc fonctionnelle : `2025.10 Back - Créer un événement.pdf` (spécifications métier et UX complètes).  [oai_citation:1‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- Slides UI : `activités.pdf` (maquettes écrans “Créer une activité”).  [oai_citation:2‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Gemini Code doit considérer ces docs comme des **spécifications produit** et adapter le code existant en conséquence.

---

# ui_slides_reference

Les visuels des écrans sont disponibles dans :  
`instructionactivites/slides/`

## Liste des slides (numérotées selon les écrans)

1. **Informations générales**  
   `instructionactivites/slides/activités (1).jpg`

2. **Présentation – Description + images**  
   `instructionactivites/slides/activités (2).jpg`

3. **Présentation – Vidéo + réseaux sociaux**  
   `instructionactivites/slides/activités (3).jpg`

4. **Localisation – Adresse physique complète (entité existante)**  
   `instructionactivites/slides/activités (4).jpg`

5. **Localisation – Adresse physique – sélection source adresse**  
   `instructionactivites/slides/activités (5).jpg`

6. **Localisation – Infos complémentaires lieu (type d’événements, stationnement, accessibilité, restauration, boissons, accès & transports)**  
   `instructionactivites/slides/activités (6).jpg`

7. **Localisation – Mode “En ligne” (URL + notes)**  
   `instructionactivites/slides/activités (7).jpg`

8. **Créneaux – Ponctuel ou annuel (date/heure début/fin, ajouter un créneau)**  
   `instructionactivites/slides/activités (8).jpg`

9. **Créneaux – Récurrent (période, fréquence jour, horaire, désactivation de créneaux)**  
   `instructionactivites/slides/activités (9).jpg`

10. **Créneaux – Récurrent (fréquence semaine – jours + horaires)**  
    `instructionactivites/slides/activités (10).jpg`

11. **Créneaux – Récurrent (fréquence semaine – exemple lundi 10:00–11:00)**  
    `instructionactivites/slides/activités (11).jpg`

12. **Créneaux – Récurrent (fréquence mois – ex. premier lundi de chaque mois + horaire + désactivation)**  
    `instructionactivites/slides/activités (12).jpg`

13. **Créneaux – Listing des créneaux générés + filtre par date**  
    `instructionactivites/slides/activités (13).jpg`

14. **Billetterie – Paramètres généraux (gratuit/payant, type d’entrée, contacts, mode réservation)**  
    `instructionactivites/slides/activités (14).jpg`

15. **Billetterie – Module de participation + (V2) créneaux associés**  
    `instructionactivites/slides/activités (15).jpg`

16. **Billetterie – Formulaire de billet (nom, description, capacités, période d’inscription, stopper la réservation)**  
    `instructionactivites/slides/activités (16).jpg`

17. **Billetterie – Lien externe + tarifs (nom, prix, informations)**  
    `instructionactivites/slides/activités (17).jpg`

18. **Publication – Visibilité (public/privé + mot de passe) & statut (hors ligne / en ligne)**  
    `instructionactivites/slides/activités (18).jpg`

---

# global_objective

Mettre en place (ou refactorer) le module **“Créer / Modifier une activité / événement”** pour les partenaires, avec :

- Un **formulaire multi-blocs** (ancres, pas de wizard multi-étapes) :
  - Informations générales  
  - Présentation  
  - Localisation  
  - Créneaux (calendrier / récurrence)  
  - Billetterie  
  - Publication  
  - Organisateur / Co-organisateurs (onglet séparé ou bloc dédié)   
- Une **barre d’actions flottante** avec CTA :
  - `Prévisualiser`
  - `Enregistrer`
  - `Mettre en ligne`
- Une **jauge de complétion** montrant le % de fiche complétée.  [oai_citation:3‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Objectif UX : permettre à un partenaire de :
1. Créer une fiche activité en mode **brouillon** rapidement (juste les champs obligatoires de création).  
2. Compléter ensuite les champs nécessaires à la **mise en ligne**.  
3. Gérer les créneaux récurrents, la billetterie, et la publication de manière simple et explicite.

---

# ux_principles

- **Tout dans un seul écran / formulaire**, avec navigation par **ancres** dans un menu latéral (comme dans les maquettes “Créer une activité”).  
  → Voir slide `instructionactivites/slides/activités (1).jpg`.  [oai_citation:4‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  
- La **barre de CTA** (`Hors ligne / Enregistré / Prévisualiser / Enregistrer / En ligne`) est **fixe en haut**, toujours visible au scroll.
- Les champs obligatoires pour **créer** vs **publier** doivent être **clairement identifiés** :
  - symbole (ex : `*` + info bulle) pour :
    - *Obligatoire pour créer l’événement*  
    - *Nécessaire pour pouvoir publier l’activité*  [oai_citation:5‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- **Par défaut, tous les éléments remplis sont visibles en ligne**, sauf si un comportement contraire est spécifié.
- **Mobile** : la version mobile actuelle ne fonctionne pas. Pour l’instant, priorité desktop ; prévoir une structure HTML/CSS permettant une mise en conformité responsive plus tard.  [oai_citation:6‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

---

# functional_scope

## 1. Barre de page & CTA

Voir slides :  
- `instructionactivites/slides/activités (1).jpg` (Hors ligne, Prévisualiser, Enregistré)  
- `instructionactivites/slides/activités (18).jpg` (En ligne)  

Composant global visible sur toutes les sections de la page :

- Affichage du statut :
  - `Hors ligne` / `En ligne` (toggle, reflète le statut de publication).  [oai_citation:7‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  
- Boutons :
  - `Prévisualiser`  
  - `Enregistrer` (enregistre en brouillon, ne force pas les règles de publication)  
  - `Mettre en ligne` (active le statut en ligne SI les prérequis sont remplis, sinon affiche erreurs ciblées).   
- Jauge :
  - “Votre fiche Activité est complétée à X %.”  
  - Le calcul de % est basé sur le remplissage des sections clés (voir section `#completion_logic`).

## 2. Sections / Ancres

Voir slide `instructionactivites/slides/activités (1).jpg` (menu latéral).  

- Menu latéral gauche (ou équivalent) avec ces entrées :
  - `Informations générales`
  - `Présentation`
  - `Localisation`
  - `Créneaux`
  - `Billetterie`
  - `Publication`
  - (Optionnel / onglet séparé) `Organisateur / Co-organisateurs`   

Chaque entrée fait scroller vers le bloc correspondant.

---

# data_model

> NB : la structure exacte des tables / CPT WordPress reste dépendante du code existant ; ici, on décrit les **champs fonctionnels**, à mapper sur les métadonnées / taxonomies WP.  [oai_citation:8‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

## 1. Informations générales

Voir slide `instructionactivites/slides/activités (1).jpg`.  [oai_citation:9‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

**Fichier template :** `templates/vendor/__edit-event-general.php`

Champs :

- `nom_activite` (string)
  - **Variable Code :** `name_event` (Input), `post_title` (WP)
  - Label : “Nom de l’activité *”
  - Obligatoire pour créer ET pour publier.   
- `categorie` (taxonomy / select)
  - **Variable Code :** `event_cat` (Taxonomy)
  - Label : “Catégorie *”
  - Obligatoire pour créer ET pour publier.
  - Gérée par WP Admin Le Hiboo.
- `type_evenement` (enum / taxonomy)
  - **Variable Code :** `event_type` (Taxonomy custom à vérifier/créer) ou Meta `event_format`. *Note: Ne pas confondre avec la meta `event_type` (physique/online).*
  - Label : “Type d’événement *”
  - Menu déroulant, choix unique.
  - Valeurs ex : `Animation`, `Avant-première`, `Atelier`, `Compétition`, `Conférence`, etc.  
  - Triées par ordre alphabétique.  
  - Géré par WP Admin.  
  - **Nécessaire pour publier**.  [oai_citation:10‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- `public_vise` (taxonomy multiple, renommé “Public visé” ou “Public”)
  - **Variable Code :** `event_public` (Taxonomy custom probable)
  - Groupes :
    - Grand public : `Petite enfance`, `Jeunesse`, `Adolescence`, `Jeune adulte`, `Adulte`, `Senior`
    - Professionnel : `Chefs d’entreprises`, `RH - Marketing`, `Tech`
  - Sélection d’un “grand groupe” → toutes ses sous-catégories sont auto-sélectionnées.  
  - Au moins un public est **obligatoire pour publier**.   
- `thematiques` (multi-select, gérées par WP Admin)
  - **Variable Code :** `event_thematique` (Taxonomy)
  - Label : “Thématiques”
- `evenements_tags` (multi-select, gérées par WP Admin)
  - **Variable Code :** `event_tag` (Taxonomy)
  - Label : “Événements”
- `saisons` (optionnel, peut être doublon avec événements → à garder souple).
  - **Variable Code :** `event_saison` (Taxonomy)
- `emotions` (multi-select, gérées par WP Admin)
  - **Variable Code :** `event_emotion` (Taxonomy probable)
  - Label : “Émotions”
- `activites_associees` (liste d’autres activités de l’entité)
  - **Variable Code :** Meta `related_events` (à implémenter)
  - Label : “Activités à associer”
  - Recherche par titre, tri des résultats du plus récent au plus ancien.   

Module Co-organisateurs (CTA bas de section) : voir aussi `#organiser_tab`.

---

## 2. Présentation

Voir slides :  
- `instructionactivites/slides/activités (2).jpg` (Description + images)  
- `instructionactivites/slides/activités (3).jpg` (Vidéo + réseaux sociaux)  [oai_citation:11‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

**Fichier template :** `templates/vendor/__edit-event-presentation.php`

- Titre de l’étape : **Présentation**  
- Description : “Présentez votre événement avec du texte, des images et une vidéo.”   

Champs :

- `description` (rich text)
  - **Variable Code :** `content_event` (WP Editor)
  - Recommandation : minimum 500 caractères (non bloquant).
  - Afficher un compteur / jauge de caractères.  
  - Texte d’aide :  
    “Pour garantir une description complète et percutante, nous vous conseillons vivement d'atteindre un minimum de 500 caractères. Plus votre description sera détaillée, plus elle sera efficace.”  [oai_citation:12‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- `image_principale` (image)
  - **Variable Code :** `img_thumbnail` (Meta `_thumbnail_id`)
  - Label : “Image de présentation”
  - Indiquer le format idéal (placeholder).
  - Si vide au moment de la publication → image par défaut Le Hiboo selon la catégorie.   
- `galerie_images` (0..n images)
  - **Variable Code :** `event_gallery` (Meta)
  - Label : “Image Galerie”
  - Widget d’import d’images + formats acceptés.
- `video_url` (string, URL)
  - **Variable Code :** `event_video` (Meta - **MANQUANT dans le template actuel, à ajouter**)
  - Section “Page Vidéo”
  - Label : “Lien URL d’une vidéo sur une plateforme streaming.”
  - Texte d’aide : “La vidéo sera visible dans la galerie d’image.”
- `reseaux_sociaux[]`
  - **Variable Code :** `social_organizer` (Meta array: `link_social`, `icon_social`)
  - Section “Réseaux sociaux”
  - Chaque entrée : `{ type_reseau, url }`
  - UI : select réseau + champ URL (ex : Facebook, Instagram…).

---

## 3. Localisation

### 3.1 Choix du mode : Physique / En ligne

Voir slides :  
- `instructionactivites/slides/activités (4).jpg`  
- `instructionactivites/slides/activités (5).jpg`  
- `instructionactivites/slides/activités (7).jpg`  [oai_citation:13‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

**Fichier template :** `templates/vendor/__edit-event-localisation.php`

- Titre de l’étape : **Localisation**  
- Description : “Sélectionnez le lieu où se déroule l’activité”.   

Champs :

- `mode_evenement` (radio)
  - **Variable Code :** `event_type` (Meta)
  - Label : “L’événement se déroule”
  - Valeurs :
    - `physique` → `classic` (Code: `classic`)
    - `online` → `online` (Code: `online`)

### 3.2 Source de l’adresse (mode physique)

Voir slides :  
- `instructionactivites/slides/activités (4).jpg` (exemple avec entité existante)  
- `instructionactivites/slides/activités (5).jpg` (nouvelle adresse)  [oai_citation:14‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Champs :

- `source_adresse` (radio)
  - **Variable Code :** `address_source` (Meta)
  - Label : “Veuillez choisir la source de l'adresse pour cette localisation :”
  - Valeurs :
    - `mon_entite` → `entity` (Code: `entity`)
    - `entite_co_organisatrice` (à activer V1+ / V2)
    - `nouvelle_adresse` → `new` (Code: `new`)

#### Si `source_adresse = mon_entite` ou `entite_co_organisatrice`

- Auto-compléter depuis le profil de l’entité sélectionnée :
  - `nom_lieu`
  - `adresse`
  - `code_postal`
  - `ville`
  - `latitude`
  - `longitude`
- Possibilité de modifier ces champs pour l’activité.

#### Si `source_adresse = nouvelle_adresse`

- `nom_lieu` (string, facultatif)
  - **Variable Code :** `add_venue` (Meta)
- `adresse` :
  - **Variable Code :** `map_address` / `address`
  - Autocomplete via API `OpenStreetMap` (ou équivalent), suggestions dès quelques caractères.
  - Sélection dans la liste → auto-remplissage :
    - `code_postal`
    - `ville`
    - `latitude`
    - `longitude`
- `code_postal` (obligatoire pour publier, modifiable)
- `ville` (obligatoire pour publier, modifiable)
  - **Variable Code :** `el_city` (Taxonomy term slug?)
- `latitude` (obligatoire, champ texte modifiable)
  - **Variable Code :** `map_lat`
- `longitude` (obligatoire, champ texte modifiable)
  - **Variable Code :** `map_lng`
- Affichage d’une carte (si disponible) avec marker.  [oai_citation:15‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

### 3.3 Informations complémentaires du lieu

Voir slide `instructionactivites/slides/activités (6).jpg`.  [oai_citation:16‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Ces champs sont auto-complétés si on sélectionne une entité, mais doivent être modifiables pour l’activité :

- `type_evenements_organises` (select ou tags)
  - **Variable Code :** `venue_event_type` (Meta: `indoor`, `outdoor`, `both`)
  - Label : “Type d’événements organisés”
  - Texte d’aide : informer si les événements sont plutôt en intérieur, extérieur, ou les deux.
- `stationnement` (textarea)
  - **Variable Code :** `venue_parking` (Meta)
  - Texte d’aide : donner informations sur le stationnement, possibilité d’importer une image de plan.
- `acces_transports` (textarea)
  - **Variable Code :** `venue_access` (Meta)
  - Texte d’aide : “Donnez aux visiteurs toutes les informations pour accéder au lieu.”
- `accessibilite_pmr` (checkbox + textarea)
  - **Variable Code :** `venue_pmr_accessible` (Meta checkbox), `venue_pmr_info` (Meta textarea)
  - Checkbox : “Accessible PMR”
  - Notes : texte libre.
- `restauration_sur_place` (checkbox + textarea)
  - **Variable Code :** `venue_restaurant_available`, `venue_restaurant_info`
- `boisson_sur_place` (checkbox + textarea)
  - **Variable Code :** `venue_drinks_available`, `venue_drinks_info`

### 3.4 Mode “En ligne”

Voir slide `instructionactivites/slides/activités (7).jpg`.  [oai_citation:17‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Si `mode_evenement = online` :

- `url_evenement_en_ligne` (string, URL)
  - **Variable Code :** `event_online_url` (Meta - **MANQUANT, à ajouter**)
  - Label : “Lien URL de l’événement”
  - **Obligatoire pour publier** dans ce mode.
- `notes_online` (textarea)
  - **Variable Code :** `event_online_notes` (Meta - **MANQUANT, à ajouter**)
  - Label : “Notes”
  - Texte d’aide : “Information pour accéder à l’activité en ligne”.

---

## 4. Créneaux (Calendrier)

Voir slides :  
- `instructionactivites/slides/activités (8).jpg` (Ponctuel)  
- `instructionactivites/slides/activités (9).jpg` (Récurrent – jour)  
- `instructionactivites/slides/activités (10).jpg` (Récurrent – semaine)  
- `instructionactivites/slides/activités (11).jpg` (Récurrent – semaine, exemple)  
- `instructionactivites/slides/activités (12).jpg` (Récurrent – mois)  
- `instructionactivites/slides/activités (13).jpg` (Listing créneaux)  [oai_citation:18‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

**Fichier template :** `templates/vendor/__edit-event-calendar.php`

Titre : **Créneaux**  
Description : “Paramétrez un créneau ou une période pour l’événement.”   

### 4.1 Type d’occurrence

- `type_occurrence` (radio)
  - **Variable Code :** `option_calendar` (Meta)
  - Label : “Sélectionnez si l’événement est * :”
  - Valeurs :
    - `ponctuel_ou_annuel` → `manual` (Code: `manual`)
    - `recurrent` → `auto` (Code: `auto`)

### 4.2 Cas 1 – Ponctuel / Annuel

Champs (par créneau) :

- `date_debut` (date) – obligatoire  
  - **Variable Code :** `calendar[x][date]`
- `heure_debut` (time) – obligatoire  
  - **Variable Code :** `calendar[x][start_time]`
- `date_fin` (date) – obligatoire, pré-remplie avec `date_debut` par défaut  
  - **Variable Code :** `calendar[x][end_date]`
- `heure_fin` (time) – obligatoire  
  - **Variable Code :** `calendar[x][end_time]`

Bouton : `+ Ajouter un créneau`  
→ crée un ou plusieurs créneaux indépendants.  
Listing des créneaux créés (voir slide 13) :  
- Date
- Heure de début
- Heure de fin
- Boutons éditer / supprimer  
- Case à cocher pour actions groupées éventuelles.   

### 4.3 Cas 2 – Récurrent

Étapes logiques :

1. **Sélection de période**
   - `periode.date_debut` → `calendar_start_date`
   - `periode.date_fin` → `calendar_end_date`

2. **Sélection de fréquence**
   - `frequence.type` ∈ { `jour`, `semaine`, `mois` }
     - **Variable Code :** `recurrence_frequency` (daily, weekly, monthly)

   - Si `jour` :
     - “Chaque X jour(s)”
       - `frequence.intervalle_jours` (int, par défaut 1) → `recurrence_interval`

   - Si `semaine` :
     - “Chaque X semaine(s)”
       - `frequence.intervalle_semaines` (int) → `recurrence_interval`
     - Liste des jours de semaine avec possibilité de définir un ou plusieurs créneaux par jour :
       - **Variable Code :** `recurrence_bydays[]` (checkboxes 0-6)
       - Pour chaque jour :
         - checkbox actif
         - `heure_debut`, `heure_fin` → `ts_start[day][]`, `ts_end[day][]`
         - bouton `Ajouter` pour ajouter un créneau supplémentaire ce même jour.

   - Si `mois` :
     - Dropdown :
       - “Le [premier / second / troisième / quatrième / cinquième / dernier] [lundi / mardi / … / dimanche] de chaque mois”
       - **Variable Code :** `recurrence_byweekno` (1, 2, 3, 4, -1), `recurrence_byday` (0-6)
     - Possibilité d’ajouter plusieurs règles mensuelles.
     - Pour chaque règle, on configure ensuite un ou plusieurs horaires (`heure_debut` / `heure_fin`).

3. **Sélection de l’horaire**
   - Pour chaque règle (jour/semaine/mois), un bloc :
     - `heure_debut`
     - `heure_fin`
     - bouton `Ajouter` pour ajouter un nouvel horaire.

4. **Désactivation de créneaux**
   - Section “Désactivez un créneau :”
   - Champs :
     - `date_debut_exclusion` → `disable_date[x][start_date]`
     - `date_fin_exclusion` → `disable_date[x][end_date]`
     - `selection_creneau` (dropdown des créneaux logiques existants)
   - Bouton `Ajouter` → ajoute une règle d’exclusion (plusieurs possibles).   

5. **Listing final des créneaux**
   - Générer une table de tous les créneaux calculés (dates + heures) à partir des règles.
   - Afficher ce listing (voir slide 13).
   - Doit permettre édition / suppression d’un créneau spécifique.

---

## 5. Billetterie

Voir slides :  
- `instructionactivites/slides/activités (14).jpg` (écran principal billetterie)  
- `instructionactivites/slides/activités (15).jpg` (module de participation + créneaux associés V2)  
- `instructionactivites/slides/activités (16).jpg` (formulaire billet)  
- `instructionactivites/slides/activités (17).jpg` (lien externe + tarifs)  [oai_citation:19‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

**Fichier template :** `templates/vendor/__edit-event-ticket.php`

Titre : **Billetterie**  
Description : “Configurez les billets et les tarifs pour votre événement.”   

### 5.1 Paramètres généraux

Champs :

- `gratuit_ou_payant` (radio)
  - **Variable Code :** `ticket_global_type` (Meta - **MANQUANT, à ajouter**). *Note: Le code actuel gère le gratuit/payant par billet individuel.*
  - Label : “Sélectionnez si l’événement est :”
  - Valeurs : `Gratuit`, `Payant`
  - Par défaut : aucun coché.   
- `type_entree` (select)
  - **Variable Code :** `ticket_entry_type` (Meta - **MANQUANT, à ajouter**)
  - Label : “Sélectionnez le type d’entrée :”
  - Valeurs :
    - `acces_libre` – “Accès libre”
    - `acces_libre_reservation_conseillee` – “Accès libre avec réservation conseillée”
    - `sur_reservation_obligatoire` – “Sur réservation obligatoire”
    - `billetterie_sur_place_uniquement` – “Billetterie sur place uniquement”
    - `sur_invitation_uniquement` – “Sur invitation uniquement”
    - `non_specifie` – “Non spécifié”
- `email_contact`
  - **Variable Code :** `mail_organizer` (Meta - déjà présent dans Présentation, à synchroniser ?)
  - Pré-rempli avec l’email de contact de l’entité.
  - Modifiable.
- `telephone_contact`
  - **Variable Code :** `phone_organizer` (Meta)
  - Pré-rempli avec le téléphone de contact de l’entité.
  - Modifiable.   

Texte d’introduction :

> “Gérez la billetterie (prochainement) ou les inscriptions directement sur LeHiboo, ou redirigez vos utilisateurs vers une plateforme externe si vous utilisez un outil tiers pour la billetterie.”

### 5.2 Choix du mode de réservation

- `mode_reservation` (radio)
  - **Variable Code :** `ticket_link` (Meta)
  - Valeurs :
    - `module_lehiboo` → `ticket_internal_link` (Code: `ticket_internal_link`)
    - `lien_externe` → `ticket_external_link` (Code: `ticket_external_link`)
  - Par défaut : aucun coché.   

---

### 5.3 Mode `module_lehiboo` – Billets

Lorsque `mode_reservation = module_lehiboo`, afficher la section “billets”.

> NOTE V2 : champ “Créneaux associés” permettant de préciser sur quels créneaux le billet est valable (tous les créneaux ou un sous-ensemble). Pour l’instant, par défaut, un billet est applicable à tous les créneaux comme dans le WordPress actuel.   

Champs par billet (Meta array `ticket`):

- `nom_billet` (string)
  - **Variable Code :** `ticket[x][name_ticket]`
  - Label : “Nom du billet *”
  - Obligatoire.
- `description_billet` (textarea)
  - **Variable Code :** `ticket[x][desc_ticket]`
  - Label / aide :  
    “Cette description sera affichée sur la page de l’activité au niveau du billet, et également sur la version PDF du billet.”
  - Facultatif.
- `nombre_total_places` (int)
  - **Variable Code :** `ticket[x][number_total_ticket]`
  - Label : “Nombre total de places”
  - Peut être vide → illimité.
- `nombre_min_places_par_reservation` (int)
  - **Variable Code :** `ticket[x][number_min_ticket]`
  - Label : “Nombre minimum de places autorisé par réservation”
  - Par défaut : 1
  - Ne peut pas être 0 ni vide.
- `nombre_max_places_par_reservation` (int)
  - **Variable Code :** `ticket[x][number_max_ticket]`
  - Label : “Nombre maximum de places autorisé par réservation”
  - Facultatif (peut être vide).   

Période d’inscription :

Deux modes (radio ou switch) :

1. **Mode 1 – Jusqu’à X minutes avant le début de l’activité**
   - Texte : “Les réservations sont ouvertes jusqu’à [X] minute(s) avant le début de l’activité”
   - Champ `minutes_avant_debut` (int, par défaut 0).
   - **Variable Code :** `ticket[x][book_before_minutes]` (à vérifier si présent par billet ou global)

2. **Mode 2 – Période définie**
   - Texte : “Les réservations sont ouvertes à partir du [date_ouverture + heure_ouverture] jusqu’au [date_fermeture + heure_fermeture]”
   - Champs :
     - `date_ouverture`, `heure_ouverture` (si heure vide → `00:00`) → `ticket[x][start_ticket_date]`, `ticket[x][start_ticket_time]`
     - `date_fermeture`, `heure_fermeture` (si heure vide → `23:59`) → `ticket[x][close_ticket_date]`, `ticket[x][close_ticket_time]`
   - Si ce mode est activé → on ne peut pas sauvegarder si les dates (et heures implicites ou saisies) sont manquantes.  [oai_citation:20‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Actions :

- `Sauvegarder ce billet` (create/update)
  - Conditions minimales pour sauvegarder :
    - `nom_billet`
    - `nombre_min_places_par_reservation`
    - Un mode de période d’inscription sélectionné (et valide).
- `Stopper la réservation` (V2)
  - Met un flag `reservation_stoppee = true`.
  - Quand stoppé, le bouton devient “Relancer la réservation stoppée”.
- `Supprimer` (icône ou bouton)
  - Demander confirmation.
- `Ajouter un autre billet`
  - Ré-affiche un formulaire vierge pour un nouveau billet.

Listing des billets (vue synthétique) :

- Pour chaque billet :
  - `nom_billet`
  - `nb_reservations / nb_places_total` (ou “illimité” si nombre total vide)
  - Bouton `modifier`
  - Bouton `supprimer`   

---

### 5.4 Mode `lien_externe` – URL + Tarifs

Lorsque `mode_reservation = lien_externe` :

- Champ `url_reservation_externe` (string, URL)
  - **Variable Code :** `ticket_external_link` (Meta)
  - Label : “Lien URL de réservation”
  - Texte d’aide : “Insérez le lien vers votre billetterie ou autre type de réservation externe”.
  - **Obligatoire** dans ce mode.

- Section `tarifs[]` :
  - **Variable Code :** `ticket_external_prices` (Meta array)

  Pour chaque tarif :

  - `nom_tarif` (string)
    - **Variable Code :** `ticket_external_prices[x][name]`
    - Label : “Nom du tarif”
  - `prix` (number)
    - **Variable Code :** `ticket_external_prices[x][price]`
    - Label : “Prix”
    - Monnaie : euros uniquement pour l’instant.
  - `informations` (textarea)
    - **Variable Code :** `ticket_external_prices[x][info]` (à ajouter)
    - Label : “Informations”
    - Texte d’aide : “Type de public pour ce tarif” (exemple).  

  Actions :
  - `Ajouter un tarif`
  - `Supprimer` un tarif existant.   

---

## 6. Publication

Voir slides :  
- `instructionactivites/slides/activités (20).jpg` n’existe pas – mais la logique est visible dans :  
  `instructionactivites/slides/activités (18).jpg` (état En ligne / mot de passe).  [oai_citation:21‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Titre : **Publication**  
Description : “Choisissez la visibilité et le statut de votre événement”.   

### 6.1 Visibilité

Champs :

- `visibilite` (radio)
  - Titre : “Cet événement est :”
  - Valeurs :
    - `public` – “Public : Référencé et accessible à tout le monde sur Le Hiboo.”
    - `public_mdp` – “Public et protégé par mot de passe : Référencé, mais accessible uniquement via un mot de passe sur Le Hiboo.”
    - `prive` – “Privé : Non référencé sur Le Hiboo. Lien URL de la page à partager.”
    - `prive_mdp` – “Privé et protégé par mot de passe : Non référencé sur Le Hiboo, mais accessible uniquement via un mot de passe.”   

- Si `visibilite` ∈ { `public_mdp`, `prive_mdp` } :
  - Champ `mot_de_passe` (string)
  - Option pour afficher/masquer le mot de passe.

### 6.2 Statut en ligne

- `statut_en_ligne` (radio ou toggle)
  - Titre : “Cet événement est :”
  - Valeurs :
    - `hors_ligne` – “Hors ligne”
    - `en_ligne` – “En ligne”
  - Par défaut : `hors_ligne` dès la création.  
  - Quand on coche “En ligne”, l’activité bascule en ligne **uniquement** si toutes les règles de publication sont respectées (voir `#publication_rules`).   

---

# publication_rules

Pour pouvoir passer une activité **En ligne** :

### 1. Informations générales

- `nom_activite` renseigné  
- `categorie` renseignée  
- `type_evenement` renseigné  
- `public_vise` contient au moins une valeur.   

### 2. Présentation

- `description` non vide (aucun minimum bloquant, 500 caractères = recommandation).  
- `image_principale` :
  - si vide → image par défaut Le Hiboo selon la catégorie (gérée côté back ou front).  [oai_citation:22‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

### 3. Localisation

- Si `mode_evenement = physique` :
  - `adresse` non vide
  - `code_postal` non vide
  - `ville` non vide
  - `latitude` non vide
  - `longitude` non vide
- Si `mode_evenement = online` :
  - `url_evenement_en_ligne` non vide.   

### 4. Créneaux

- Au moins **1 créneau valide** (ponctuel ou récurrent → liste finale de créneaux non vide).

### 5. Publication

- `visibilite` sélectionnée (par défaut, on peut pré-sélectionner `public` si rien n’est choisi).  
- `statut_en_ligne` ne peut être mis à `en_ligne` que si tous les points ci-dessus sont OK.

En cas de tentative de publication non valide :

- Ne pas basculer en ligne.  
- Afficher une liste d’erreurs ciblées, idéalement section par section (ex : “Informations générales → Type d’événement manquant”).

---

# completion_logic

La jauge “Votre fiche Activité est complétée à X %” est **indicative**.

Proposition d’implémentation (ajustable) :

- Pondérer les sections :

  - Informations générales : 25 %  
  - Présentation : 20 %  
  - Localisation : 20 %  
  - Créneaux : 20 %  
  - Billetterie : 10 %  
  - Publication : 5 %

- À l’intérieur de chaque section, distribuer le % sur les champs clés (y compris certains facultatifs jugés importants, comme image principale, description, etc.).  

X % = somme des points remplis / somme totale.

---

# organiser_tab (co-organisateurs)

> Fonctionnalité prévue en V1+ / V2 ; implémenter l’architecture et éventuellement un début d’UI, même si toutes les règles ne sont pas encore actives.  [oai_citation:23‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Une partie de l’UI est déjà visible en bas de la section “Informations générales” :  
→ voir slide `instructionactivites/slides/activités (1).jpg`.  [oai_citation:24‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Rôles :

- **Organisateur principal** = entité qui crée l’activité.  
- **Co-organisateurs** = une ou plusieurs entités associées à l’activité.

Flow général :

1. L’utilisateur saisit un **nom ou une adresse email** de co-organisateur.
2. Deux cas :

   - **Cas 1 – Entité existe déjà dans la BDD Le Hiboo** :
     - Suggestions dynamiques :  
       “Entité trouvée : [Nom] – Ajouter comme co-organisateur ?”
     - Si ajout :
       - L’autre entité reçoit :
         - une demande d’approbation dans son Dashboard  
         - une notification email
       - Tant que l’autre entité n’a pas approuvé → statut de la demande = `en_cours`.  [oai_citation:25‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

   - **Cas 2 – Entité inconnue** :
     - Message : “Inviter cette entité à rejoindre Le Hiboo pour valider la co-organisation”.
     - L’autre entité reçoit une invitation par email.
     - Elle doit créer son compte et accepter la demande → passe en `acceptee`.

3. Statuts d’une demande de co-organisation :

   - `en_cours`  
   - `acceptee`  
   - `refusee`

Règles :

- L’organisateur principal peut :
  - Supprimer un co-organisateur.
  - Ajouter autant de co-organisateurs que nécessaire.
- V1 : le co-organisateur a accès **visuel** à la fiche Activité dans son dashboard.  
- V2 : possibilité d’ouvrir certains droits d’édition (à spécifier plus tard).  [oai_citation:26‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

---

# ui_notes

- Les maquettes du PDF `activités.pdf` sont la **référence UI** pour :
  - Les labels exacts.
  - La disposition : menu latéral gauche, contenu à droite, barre de CTA en haut.
  - Les textes d’aide visibles à l’écran.  [oai_citation:27‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  
- Le design exact (couleurs, spacing) doit suivre le design system existant, mais :
  - Conserver les CTA orange / vert.
  - Conserver les statuts `Hors ligne` / `En ligne` en haut à droite.
- Les champs non visibles dans les slides mais décrits dans `Back - Créer un événement` restent prioritaires en tant que **source métier**.  [oai_citation:28‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

---

# constraints

- **Ne pas casser** :
  - Les autres formulaires / CPT existants côté WordPress.
  - Les APIs / endpoints de réservation déjà utilisés en production (si existants).
- **Compatibilité** :
  - Conserver les taxonomies / structures WP Admin actuelles (catégories, thématiques, etc.).
- **I18n** :
  - UI partenaire en français.  
  - Les clés internes peuvent être en anglais mais doivent rester cohérentes.

---

# implementation_notes_for_gemini

Quand tu modifies le code :

1. Localiser le module “Créer une activité / événement” dans le projet.
2. Mapper les sections décrites ci-dessus sur les composants existants :
   - Si des composants sont déjà là (Informations générales, Localisation…), les **mettre à jour** plutôt que les recréer.
3. Pour les nouveaux comportements (ex : logique de récurrence revue), respecter :
   - Les règles de l’ancienne implémentation (format de stockage des meta) si elles sont déjà consommées dans d’autres écrans (planning, listing public, etc.).
4. Ajouter des tests au moins sur :
   - La validation des conditions de publication.
   - La logique de génération de créneaux récurrents.
   - La validation billetterie (obligation du lien externe / des champs billets).

---

# gemini_rules

- Tu agis comme un **assistant développeur senior** sur ce projet.  
- Toujours :
  - Lire et respecter ce fichier comme **source de vérité fonctionnelle**.  
  - Vérifier les impacts sur :
    - Création  
    - Edition  
    - Affichage côté front public (fiche activité).  
- Quand tu proposes du code :
  - Garder les noms de champs / labels UI **exactement** comme dans les maquettes et la spec.  
  - Expliquer clairement où insérer ou modifier le code (fichiers, composants, fonctions).  
- Si une information manque :
  - Ne pas inventer un comportement métier lourd (tarification complexe, multi-devise, etc.).  
  - Proposer une implémentation minimale + commentaire `// TODO: préciser avec PO`.  