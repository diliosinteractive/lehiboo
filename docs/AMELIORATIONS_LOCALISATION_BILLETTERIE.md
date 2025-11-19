# Améliorations Localisation & Billetterie - LeHiboo V1

## 📋 Résumé des modifications

Ce document détaille toutes les améliorations apportées aux sections **Localisation** et **Billetterie** du formulaire de création/modification d'événements pour LeHiboo V1.

---

## 🗺️ Section LOCALISATION

### Modifications apportées au fichier
**Fichier modifié:** `/wp-content/plugins/eventlist/templates/vendor/__edit-event-localisation.php`

### 1. Amélioration du choix de type d'événement

#### ✨ Avant
- 2 options : "Lieu physique" et "En ligne"

#### ✅ Après
- 3 options :
  - **"Dans un lieu physique"** (CAS 1) - Par défaut
  - **"En ligne"** (reste inchangé)
  - **"À la maison"** (CAS 2) - NOUVEAU

### 2. Nouvelle section "À la maison" (CAS 2)

Lorsque l'utilisateur sélectionne "À la maison", une nouvelle section s'affiche avec :

- **Sélection des villes** où l'événement doit être publié
- **Checkbox "Afficher dans toutes les villes"**
  - Si cochée : masque le sélecteur de ville
  - Si non cochée : permet de sélectionner une ou plusieurs villes spécifiques

### 3. Amélioration de la section "Dans un lieu physique" (CAS 1)

#### Nouvelle option de sélection d'adresse

Ajout d'un choix radio pour la source de l'adresse :
- **"Mon adresse d'entité"** - Utilise l'adresse de l'organisation par défaut
- **"Nouvelle adresse"** - Permet de saisir manuellement une nouvelle adresse

#### Champs additionnels facultatifs

Les champs suivants ont été ajoutés pour enrichir les informations du lieu :

1. **Type d'événements organisés** (facultatif)
   - Menu déroulant : Intérieur / Extérieur / Intérieur & Extérieur

2. **Stationnement** (facultatif)
   - Zone de texte pour informations de stationnement
   - Upload d'image pour plan de stationnement

3. **Accès & Transports** (facultatif)
   - Zone de texte pour informations d'accès
   - Upload d'image pour plan d'accès

4. **Accessibilité PMR** (facultatif)
   - Checkbox Oui/Non
   - Zone de texte pour précisions

5. **Restauration sur place** (facultatif)
   - Checkbox Oui/Non
   - Zone de texte pour détails

6. **Boisson sur place** (facultatif)
   - Checkbox Oui/Non
   - Éditeur WYSIWYG pour description détaillée

---

## 🎫 Section BILLETTERIE

### Modifications apportées au fichier
**Fichier modifié:** `/wp-content/plugins/eventlist/templates/vendor/__edit-event-ticket.php`

### 1. Amélioration de la présentation générale

#### ✨ Avant
```
"if you don't want to sell ticket, you don't need to make ticket..."
Buy ticket at: Internal link / External link
```

#### ✅ Après
```
Titre : "Billetterie"

Description améliorée en français :
"Gérez la billetterie (prochainement) ou les inscriptions directement sur LeHiboo,
ou redirigez vos utilisateurs vers une plateforme externe si vous utilisez un outil
tiers pour la billetterie.

Si vous n'avez pas besoin de billetterie ni de liste de participation,
passez simplement cette étape."
```

### 2. Nouveaux boutons de choix visuels

Trois options avec design amélioré (icônes + badges) :

1. **🎫 Créer une billetterie**
   - Badge "(prochainement)"
   - Désactivé pour l'instant (filter `el_show_ticket_paid_ticketing` = false par défaut)

2. **📝 Créer une liste d'inscription** (CAS 1)
   - Remplace "Internal link"
   - Active par défaut

3. **🔗 Utiliser un lien externe** (CAS 2)
   - Remplace "External Link"

### 3. Amélioration du CAS 1 : Liste d'inscription

#### Traductions françaises
- "Paid" → **"Payant"**
- "Free" → **"Gratuit"**
- "Price" → **"Prix"**

#### Indicateur de devise
- Ajout d'un **symbole €** visible à côté du champ prix
- Wrapper CSS `.price_input_wrapper` pour positionner l'indicateur

#### Messages d'erreur en français
- Message de validation du prix traduit et amélioré

### 4. Amélioration du CAS 2 : Lien externe

#### Nouvelle présentation structurée

**Section "Configuration du lien externe"** avec :

1. **Lien URL**
   - Input type `url` pour validation
   - Placeholder "https://"
   - Texte d'aide : "Insérez le lien vers votre billetterie externe"

2. **Tarifs multiples** (NOUVEAU ✨)
   - Widget dynamique pour ajouter plusieurs tarifs
   - Chaque tarif contient :
     - **Nom du tarif** : Ex. "Tarif Adulte", "Tarif Étudiant"
     - **Prix** : Montant en euros
     - **Symbole €** affiché
     - Bouton de suppression
   - Bouton **"+ Ajouter un tarif"** pour ajouter des tarifs supplémentaires

---

## 💻 Fichiers créés

### 1. JavaScript : Interactions dynamiques
**Fichier:** `/wp-content/plugins/eventlist/assets/js/frontend/event-location-ticketing-enhanced.js`

**Fonctionnalités implémentées :**

#### Localisation
- Affichage/masquage automatique des sections selon le type d'événement
- Gestion de la checkbox "Afficher dans toutes les villes"
- Upload d'images pour parking et accès (integration WordPress Media Library)
- Suppression d'images

#### Billetterie
- Affichage/masquage des sections internal/external
- Ajout dynamique de tarifs externes
- Suppression de tarifs externes
- Style actif sur le bouton de choix sélectionné
- Validation du format des prix (chiffres + virgule/point uniquement)

#### Helpers
- Messages de succès/erreur
- Initialisation correcte au chargement de la page

### 2. CSS : Styles visuels
**Fichier:** `/wp-content/plugins/eventlist/assets/css/frontend/event-location-ticketing-enhanced.css`

**Styles implémentés :**

#### Localisation
- Styles pour les 3 types d'événements
- Styles pour les champs additionnels
- Upload d'images avec preview
- Boutons de suppression stylisés

#### Billetterie
- Boutons de choix visuels avec hover et état actif
- Liste de tarifs externes avec design moderne
- Indicateur de devise (€) positionné dans l'input
- Messages de succès/erreur avec animations

#### Responsive
- Adaptations mobile (max-width: 768px)
- Support du dark mode (prefers-color-scheme: dark)

---

## 📝 Intégration WordPress

### Étapes pour activer les nouvelles fonctionnalités

1. **Enqueue du JavaScript**

   Ajouter dans le fichier qui gère les scripts (probablement dans `/wp-content/plugins/eventlist/includes/class-el-scripts.php` ou similaire) :

   ```php
   wp_enqueue_script(
       'el-location-ticketing-enhanced',
       EL_PLUGIN_URI . 'assets/js/frontend/event-location-ticketing-enhanced.js',
       array('jquery', 'wp-util'),
       EL_VERSION,
       true
   );
   ```

2. **Enqueue du CSS**

   ```php
   wp_enqueue_style(
       'el-location-ticketing-enhanced',
       EL_PLUGIN_URI . 'assets/css/frontend/event-location-ticketing-enhanced.css',
       array(),
       EL_VERSION
   );
   ```

3. **Sauvegarder les nouvelles meta données**

   Ajouter dans la fonction de sauvegarde des événements (probablement dans `/wp-content/plugins/eventlist/includes/class-el-ajax.php` ou le handler de formulaire) :

   ```php
   // Type d'événement
   update_post_meta($post_id, $prefix.'event_type', sanitize_text_field($_POST[$prefix.'event_type']));

   // À la maison - villes
   update_post_meta($post_id, $prefix.'show_all_cities', isset($_POST[$prefix.'show_all_cities']) ? '1' : '0');

   // Source d'adresse
   update_post_meta($post_id, $prefix.'address_source', sanitize_text_field($_POST[$prefix.'address_source']));

   // Champs additionnels du lieu
   update_post_meta($post_id, $prefix.'venue_event_type', sanitize_text_field($_POST[$prefix.'venue_event_type']));
   update_post_meta($post_id, $prefix.'venue_parking', sanitize_textarea_field($_POST[$prefix.'venue_parking']));
   update_post_meta($post_id, $prefix.'venue_parking_image', absint($_POST[$prefix.'venue_parking_image']));
   update_post_meta($post_id, $prefix.'venue_access', sanitize_textarea_field($_POST[$prefix.'venue_access']));
   update_post_meta($post_id, $prefix.'venue_access_image', absint($_POST[$prefix.'venue_access_image']));
   update_post_meta($post_id, $prefix.'venue_pmr_accessible', isset($_POST[$prefix.'venue_pmr_accessible']) ? '1' : '0');
   update_post_meta($post_id, $prefix.'venue_pmr_info', sanitize_textarea_field($_POST[$prefix.'venue_pmr_info']));
   update_post_meta($post_id, $prefix.'venue_restaurant_available', isset($_POST[$prefix.'venue_restaurant_available']) ? '1' : '0');
   update_post_meta($post_id, $prefix.'venue_restaurant_info', sanitize_textarea_field($_POST[$prefix.'venue_restaurant_info']));
   update_post_meta($post_id, $prefix.'venue_drinks_available', isset($_POST[$prefix.'venue_drinks_available']) ? '1' : '0');
   update_post_meta($post_id, $prefix.'venue_drinks_info', wp_kses_post($_POST[$prefix.'venue_drinks_info']));

   // Tarifs externes multiples
   if (isset($_POST[$prefix.'ticket_external_prices']) && is_array($_POST[$prefix.'ticket_external_prices'])) {
       $external_prices = array_map(function($item) {
           return array(
               'name' => sanitize_text_field($item['name']),
               'price' => sanitize_text_field($item['price'])
           );
       }, $_POST[$prefix.'ticket_external_prices']);
       update_post_meta($post_id, $prefix.'ticket_external_prices', $external_prices);
   }
   ```

---

## 🎨 Charte graphique respectée

- ✅ Utilisation des classes CSS existantes (`el_input_radio`, `el_input_checkbox`, `vendor_field`, etc.)
- ✅ Icônes emoji pour une meilleure lisibilité
- ✅ Design cohérent avec le reste du formulaire
- ✅ Couleur principale #FF5722 pour les accents
- ✅ Responsive design mobile-first
- ✅ Animations subtiles pour une meilleure UX

---

## 🧪 Tests recommandés

### Localisation
- [ ] Sélection "Dans un lieu physique" → Affiche la section physique
- [ ] Sélection "En ligne" → Masque les sections physique et maison
- [ ] Sélection "À la maison" → Affiche la section maison
- [ ] Checkbox "Afficher dans toutes les villes" → Masque/affiche le sélecteur
- [ ] Upload d'image parking → Fonctionne correctement
- [ ] Upload d'image accès → Fonctionne correctement
- [ ] Suppression des images → Fonctionne correctement
- [ ] Sauvegarde de tous les champs → Les données sont enregistrées

### Billetterie
- [ ] Sélection "Liste d'inscription" → Affiche la section interne
- [ ] Sélection "Lien externe" → Affiche la section externe
- [ ] Symbole € visible sur le prix → Affiché correctement
- [ ] Ajout d'un tarif externe → Fonctionne
- [ ] Suppression d'un tarif externe → Fonctionne
- [ ] Validation du format prix → Accepte seulement chiffres et point/virgule
- [ ] Sauvegarde des tarifs multiples → Les données sont enregistrées

---

## 📦 Fichiers modifiés/créés

### Modifiés
1. `/wp-content/plugins/eventlist/templates/vendor/__edit-event-localisation.php`
2. `/wp-content/plugins/eventlist/templates/vendor/__edit-event-ticket.php`

### Créés
1. `/wp-content/plugins/eventlist/assets/js/frontend/event-location-ticketing-enhanced.js`
2. `/wp-content/plugins/eventlist/assets/css/frontend/event-location-ticketing-enhanced.css`

---

## 🚀 Prochaines étapes recommandées

1. **Intégrer les scripts dans WordPress** (voir section Intégration)
2. **Ajouter la sauvegarde des meta données** dans le handler de formulaire
3. **Tester toutes les fonctionnalités** (checklist ci-dessus)
4. **Ajuster le CSS** si nécessaire selon la charte visuelle finale
5. **Traduire les nouvelles chaînes** dans le fichier `.po/.pot` si nécessaire
6. **Documenter pour l'équipe** de développement

---

## 📞 Support

Pour toute question ou problème concernant ces améliorations :
- Vérifier que jQuery et wp-util sont bien chargés
- Vérifier que les filtres WordPress (`el_show_event_type_home`, `el_show_ticket_paid_ticketing`) sont correctement configurés
- Consulter la console JavaScript pour d'éventuelles erreurs
- Vérifier que les permissions d'upload WordPress sont correctes pour les images

---

**Date de création :** $(date +%d/%m/%Y)
**Version LeHiboo :** V1
**Développeur :** Claude Code Assistant
