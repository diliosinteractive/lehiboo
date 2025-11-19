# context

Produit : **Le Hiboo**  
Module : **Back / interface partenaire – Création & édition d’un événement / activité**  
Technologie actuelle : WordPress (custom post type + module de récurrence + module billet / réservation).  [oai_citation:0‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Les deux sources de vérité sont :
- Doc fonctionnelle : `Back - Créer un événement` (spécifications métier et UX complètes).  [oai_citation:1‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- Slides UI : `FORMULAIRE ACTIVITÉS` (maquettes écrans “Créer une activité”).  [oai_citation:2‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

Gemini Code doit considérer ces docs comme des **spécifications produit** et adapter le code existant en conséquence.

---

# global_objective

Mettre en place (ou refactorer) le module **“Créer / Modifier une activité / événement”** pour les partenaires, avec :

- Un **formulaire multi-blocs** (ancres, pas de multi-steps wizard) :
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
- La **barre de CTA** est **fixe en haut**, toujours visible au scroll.
- Les champs obligatoires pour **créer** vs **publier** doivent être **clairement identifiés** :
  - symbole (ex : `*` + info bulle) pour :
    - *Obligatoire pour créer l’événement*
    - *Nécessaire pour pouvoir publier l’activité*  [oai_citation:4‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- **Par défaut, tous les éléments remplis sont visibles en ligne**, sauf si un comportement contraire est spécifié.
- **Mobile** : la version mobile actuelle ne fonctionne pas. Pour l’instant, priorité desktop ; mais ne pas empêcher une mise en conformité future (structure HTML propre, CSS responsive possible).  [oai_citation:5‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

---

# functional_scope

## 1. Barre de page & CTA

Composant global visible sur toutes les sections de la page :

- Affichage du statut :
  - `Hors ligne` / `En ligne` (toggle, reflète le statut de publication).  [oai_citation:6‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  
- Boutons :
  - `Prévisualiser`  
  - `Enregistrer` (enregistre en brouillon, ne force pas les règles de publication)
  - `Mettre en ligne` (active le statut en ligne SI les prérequis sont remplis, sinon affiche erreurs ciblées).   
- Jauge :
  - “Votre fiche Activité est complétée à X %.”  
  - Le calcul de % est basé sur le remplissage des sections clés (voir section `#completion_logic`).

## 2. Sections / Ancres

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

> NB : la structure exacte des tables / CPT WordPress reste dépendante du code existant ; ici, on décrit les **champs fonctionnels**, à mapper sur les métadonnées / taxonomies WP.

## 1. Informations générales

Champs :

- `nom_activite` (string)
  - Obligatoire pour créer ET pour publier.   
- `categorie` (taxonomy / select)
  - Obligatoire pour créer ET pour publier.
  - Gérée par WP Admin Le Hiboo.
- `type_evenement` (enum / taxonomy)
  - Menu déroulant, choix unique.
  - Valeurs ex : `Animation`, `Avant-première`, `Atelier`, `Compétition`, `Conférence`, etc.  
  - Triées par ordre alphabétique.  
  - Géré par WP Admin.  
  - **Nécessaire pour publier**.  [oai_citation:7‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- `public_vise` (taxonomy multiple, renommé “Public”)
  - Groupes :
    - Grand public : `Petite enfance`, `Jeunesse`, `Adolescence`, `Jeune adulte`, `Adulte`, `Senior`
    - Professionnel : `Chefs d’entreprises`, `RH - Marketing`, `Tech`
  - Sélection d’un “grand groupe” → toutes ses sous-catégories sont auto-sélectionnées.  
  - Au moins un public est **obligatoire pour publier**.
- `thematiques` (multi-select, gérés par WP Admin)
- `evenements_tags` (multi-select, gérés par WP Admin, anciennement “Time / Events”)  
- `saisons` (optionnel, peut être doublon avec événements → à garder souple).
- `emotions` (multi-select, gérées par WP Admin).
- `activites_associees` (liste d’autres activités de l’entité)
  - Recherche par titre, tri des résultats du plus récent au plus ancien.   

## 2. Présentation

- `description` (rich text)
  - Recommandation : minimum 500 caractères (non bloquant mais conseillé).
  - Affichage compteur / jauge pour suivre le nombre de caractères.   
- `image_principale` (image)
  - Format recommandé : à préciser (placeholder dans l’UI).  
  - Si vide au moment de la publication → image par défaut Le Hiboo selon la catégorie.  [oai_citation:8‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  
- `galerie_images` (0..n images)
- `video_url` (string, URL)
  - Lien vers une vidéo hébergée sur une plateforme de streaming (YouTube, Vimeo…).  
  - La vidéo est visible dans la galerie.
- `reseaux_sociaux[]`
  - Chaque entrée : `{ type_reseau, url }`
  - Ex : `Facebook`, `Instagram`, etc.

## 3. Localisation

Cas 1 : **Dans un lieu physique**

- `mode_evenement` = `physique` (par défaut).   
- `source_adresse` :
  - `mon_entite`
  - `entite_co_organisatrice` (V1+ / V2)
  - `nouvelle_adresse`
- Si `mon_entite` ou `entite_co_organisatrice` :
  - Auto-compléter depuis le profil :
    - `nom_lieu`
    - `adresse`
    - `code_postal`
    - `ville`
    - `latitude`
    - `longitude`
    - plus les infos :
      - `type_evenements_organises`
      - `stationnement`, `acces_transports`, `accessibilite_pmr`,
        `restauration_sur_place`, `boisson_sur_place` (modifiables localement pour l’activité).   
- Si `nouvelle_adresse` :
  - `nom_lieu` (string, facultatif)
  - `adresse` :
    - Autocomplete via API `OpenStreetMap` (ou équivalent), suggestion dès quelques caractères.
    - Au choix → auto-remplissage `code_postal`, `ville`, `latitude`, `longitude`.
  - `code_postal` (obligatoire pour publier, mais modifiable)
  - `ville` (obligatoire pour publier, modifiable)
  - `latitude` (obligatoire, texte modifiable)
  - `longitude` (obligatoire, texte modifiable)
  - Affichage carte (si possible) avec le marker.

Cas 2 : **En ligne**

- `mode_evenement` = `online`
- Champs :
  - `url_evenement_en_ligne` (obligatoire pour publier si mode = online)
  - `notes_online` (texte, infos pour accéder à l’activité en ligne).   

## 4. Créneaux (Calendrier)

Paramètre principal :

- `type_occurrence` :
  - `ponctuel_ou_annuel` (cas 1)
  - `recurrent` (cas 2)   

### Cas 1 – Ponctuel / Annuel

Champs :

- `date_debut` (date) – obligatoire  
- `heure_debut` (time) – obligatoire  
- `date_fin` (date) – obligatoire, pré-remplie avec `date_debut` par défaut  
- `heure_fin` (time) – obligatoire  

Button : `+ Ajouter un créneau` → permet:
- de créer plusieurs créneaux indépendants (liste en bas type tableau, modifiable / supprimable).  [oai_citation:9‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  

### Cas 2 – Récurrent

Étapes :

1. **Sélection de période**
   - `periode.date_debut`
   - `periode.date_fin`

2. **Sélection de fréquence**
   - `frequence.type` ∈ { `jour`, `semaine`, `mois` }
   - Si `jour` :
     - “Chaque X jour(s)” (integer `X`)
   - Si `semaine` :
     - “Chaque X semaine(s)” + sélection des jours de semaine avec horaires par jour.   
   - Si `mois` :
     - Dropdown “Le [premier/second/troisième/quatrième/cinquième/dernier] [jour de semaine] de chaque mois”.

3. **Sélection de l’horaire**
   - `heure_debut` / `heure_fin` (on peut en ajouter plusieurs pour une même règle de récurrence).

4. **Désactivation de créneaux**
   - Période à exclure :
     - `date_debut_exclusion`, `date_fin_exclusion`
     - + sélection du créneau à désactiver.
   - Permet plusieurs exclusions.

Sortie attendue :
- Une **liste de créneaux générés** (type listing) avec :
  - Date, heure début, heure fin, boutons **éditer** / **supprimer**.   

## 5. Billetterie

Champs généraux :

- `gratuit_ou_payant` ∈ { `gratuit`, `payant`, `non_defini` }
- `type_entree` (enum, choix unique) :   
  - `acces_libre`
  - `acces_libre_reservation_conseillee`
  - `sur_reservation_obligatoire`
  - `billetterie_sur_place_uniquement`
  - `sur_invitation_uniquement`
  - `non_specifie`
- `email_contact` (pré-rempli depuis entité, modifiable)
- `telephone_contact` (pré-rempli, modifiable)

Mode de réservation :

- `mode_reservation` ∈ { `module_lehiboo`, `lien_externe`, `aucun` }
  - `module_lehiboo` :
    - Gestion interne de billets / inscriptions.
  - `lien_externe` :
    - Champ `url_reservation_externe` (obligatoire dans ce cas).
    - Option `tarifs[]` pour informer les utilisateurs (non connectés au paiement).

### 5.1 Module Le Hiboo – Billets

Pour chaque **billet** configuré avec le module interne :

Champs :

- `nom_billet` (obligatoire)
- `description_billet` (facultatif, affiché sur fiche + PDF billet)   
- `nombre_total_places` (int, facultatif — si vide → illimité)
- `nombre_min_places_par_reservation` (int, par défaut 1, ne peut pas être 0 ni vide)
- `nombre_max_places_par_reservation` (int, facultatif)

Période d’inscription :

2 options :

1. **Jusqu’à X minutes avant le début de l’activité** :
   - `inscription_mode` = `avant_activite`
   - `minutes_avant_debut` (int, par défaut 0)

2. **Période définie** :
   - `inscription_mode` = `periode`
   - `date_ouverture`, `heure_ouverture` (si heure vide → `00:00`)
   - `date_fermeture`, `heure_fermeture` (si heure vide → `23:59`)
   - Impossible d’enregistrer si date/heure manquantes.  [oai_citation:10‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

Actions sur un billet :

- `Sauvegarder ce billet` (création / update)
- `Stopper la réservation` (V2) :
  - Empêche de nouvelles réservations.
  - Bouton devient `Relancer la réservation stoppée`.
- `Supprimer ce billet`.

Vue synthétique (listing) :

- Pour chaque billet :
  - `nom_billet` – `nb_reservations / nb_places_total (ou illimité)` – CTA `modifier` – CTA `supprimer`.

### 5.2 Lien externe + Tarifs

Quand `mode_reservation = lien_externe` :

- `url_reservation_externe` (obligatoire).
- `tarifs[]` informatifs :
  - `nom_tarif` (string)
  - `prix` (numérique, pour l’instant en euros uniquement)
  - `informations` (texte libre).   

## 6. Publication

Champs :

- `visibilite` ∈ {
  - `public`
  - `public_mdp`
  - `prive`
  - `prive_mdp`
}
- Si *_mdp* :
  - `mot_de_passe` (champ texte + toggle afficher/masquer).   

Statut de mise en ligne :

- `statut_en_ligne` ∈ { `hors_ligne`, `en_ligne` }
  - Par défaut `hors_ligne` à la création.
  - `en_ligne` seulement si toutes les conditions de publication sont réunies (voir `#publication_rules`).

---

# publication_rules

Pour pouvoir passer une activité **En ligne** :

### 1. Informations générales

- `nom_activite` renseigné
- `categorie` renseignée
- `type_evenement` renseigné
- `public_vise` contient au moins une valeur

### 2. Présentation

- `description` non vide (pas de minimum bloquant, 500 caractères = recommandation).
- `image_principale` :
  - si vide → image par défaut Le Hiboo selon la catégorie (à gérer côté back ou front).  [oai_citation:11‡2025.10 Back - Créer un événements.pdf](sediment://file_000000005508720aadf15d41aef6b1d2)  

### 3. Localisation

- Si mode = `physique` :
  - `adresse`, `code_postal`, `ville`, `latitude`, `longitude` non vides.
- Si mode = `online` :
  - `url_evenement_en_ligne` non vide.

### 4. Créneaux

- Au moins **1 créneau valide** (ponctuel ou récurrent → liste de créneaux générés non vide).

### 5. Publication

- `visibilite` sélectionnée (par défaut `public` si rien n’est choisi).
- `statut_en_ligne` peut être mis à `en_ligne` uniquement si toutes les règles ci-dessus sont OK.

En cas de tentatives de publication non valide :

- Ne pas publier.
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
- X % = somme des points remplis / somme totale.

---

# organiser_tab (co-organisateurs)

> Fonctionnalité prévue en V1+ / V2 ; implémenter l’architecture et éventuellement un début d’UI, même si toutes les règles ne sont pas encore actives.   

Rôles :

- **Organisateur principal** = entité qui crée l’activité.
- **Co-organisateurs** = une ou plusieurs entités associées à l’activité.

Flow général :

1. L’utilisateur saisit un **nom ou une adresse email** de co-organisateur.
2. Deux cas :
   - **Cas 1 – Entité existe déjà dans la BDD Le Hiboo** :
     - Suggestions dynamiques : “Entité trouvée : [Nom] – Ajouter comme co-organisateur ?”
     - Si ajout :
       - Envoi d’une demande d’approbation :
         - Notification sur le Dashboard de l’autre entité.
         - Notification email.
       - Tant que non accepté → statut = `En cours`.
   - **Cas 2 – Entité inconnue** :
     - Proposition : “Inviter cette entité à rejoindre Le Hiboo pour valider la co-organisation”.
     - Envoi email d’invitation.
     - Tant que l’entité n’a pas créé son compte et accepté → statut = `En cours`.

Statuts d’une demande de co-organisation :

- `en_cours`
- `acceptee`
- `refusee`

Règles :

- L’organisateur principal peut :
  - Supprimer un co-organisateur.
  - Ajouter autant de co-organisateurs que nécessaire.
- V1 : le co-organisateur a accès **visuel** à la fiche activité dans son dashboard.
- V2 : possibilité d’ouvrir certains droits d’édition (à détailler plus tard).

---

# ui_notes

- Les maquettes du PDF `FORMULAIRE ACTIVITÉS` sont la **référence UI** pour :
  - Les labels exacts (ex. “Sélectionnez le lieu où se déroule l’activité”, “Configurez les billets et tarifs pour votre événement”).  [oai_citation:12‡activités.pdf](sediment://file_00000000b6d871f4b375213ee7b12271)  
  - La disposition : menu latéral gauche, contenu à droite, barre de CTA en haut.
  - Les textes d’aide (paragraphes explicatifs sous les titres de sections).
- Le design exact (couleurs, spacing) peut suivre le design system existant, mais :
  - Conserver les CTA orange / vert.
  - Conserver les statuts `Hors ligne` / `En ligne` en haut à droite.

---

# constraints

- **Ne pas casser** :
  - Les autres formulaires / CPT existants coté WordPress.
  - Les API / endpoints de réservation déjà utilisés en production (si existants).
- **Compatibilité** :
  - Conserver les taxonomies / structures WP Admin actuelles (catégories, thématiques, etc.).
- **I18n** :
  - UI partenaire en français. Les clés internes peuvent être en anglais mais doivent rester cohérentes.

---

# implementation_notes_for_gemini

Quand tu modifies le code :

1. **Localiser le module “Créer une activité / événement”** dans le projet.
2. Mapper les sections décrites ci-dessus sur les composants existants :
   - Si des composants sont déjà là (Informations générales, Localisation…), les **mettre à jour** plutôt que les recréer.
3. Pour les nouveaux comportements (ex : nouvelle logique de récurrence), respecter :
   - Les règles de l’ancienne implémentation (ex : format de stockage des meta) si elles sont déjà consommées dans d’autres écrans (planning, listing public, etc.).
4. Ajouter des **tests** (unitaires ou e2e, selon stack) au moins sur :
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
    - Affichage côté front public (détail d’événement).
- Quand tu proposes du code :
  - Garder les noms de champs / labels UI **exactement** comme dans les maquettes et la spec.
  - Expliquer clairement où insérer ou modifier le code (fichiers, composants, fonctions).
- Si une information manque :
  - Ne pas inventer un comportement métier lourd (tarification complexe, multi-devise, etc.).
  - Proposer une implémentation minimale + commentaire `// TODO: préciser avec PO`.