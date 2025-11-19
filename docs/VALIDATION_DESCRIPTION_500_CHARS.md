# Validation Description 500 Caractères - Le Hiboo V1

## Objectif

Mettre au minimum 500 caractères dans la description pour pouvoir **publier** l'activité.
La validation n'est **pas obligatoire** pour créer ou sauvegarder une fiche activité en brouillon.

## Fonctionnement

### Règles de validation

- **Sauvegarde en brouillon** : Aucune contrainte sur la longueur de la description
- **Sauvegarde en "En attente"** : Aucune contrainte sur la longueur de la description
- **Sauvegarde en "Privé"** : Aucune contrainte sur la longueur de la description
- **Publication (statut "Public")** : Minimum 500 caractères requis dans la description

### Widget de description

Le widget de description reste inchangé :
- Possibilité de mettre des liens URL conservée
- Pas de champ spécifique pour l'URL de l'événement
- Le widget TinyMCE/textarea fonctionne normalement

## Modifications apportées

### 1. Validation côté serveur (PHP)

**Fichier modifié :** `/wp-content/plugins/eventlist/includes/class-el-ajax.php`

**Ligne :** Après ligne 1783 (après gestion du statut "protected")

**Code ajouté :**
```php
// V1 Le Hiboo - Validation minimum 500 caractères pour publication uniquement
if ( $event_status === 'publish' ) {
    // Compter les caractères de la description (en retirant les balises HTML)
    $description_text = strip_tags( $content_event );
    $description_length = mb_strlen( $description_text );

    if ( $description_length < 500 ) {
        wp_send_json( array(
            'status' => 'error_description_too_short',
            'message' => sprintf(
                __( 'La description doit contenir au minimum 500 caractères pour publier l\'activité. Actuellement : %d caractères.', 'eventlist' ),
                $description_length
            ),
            'current_length' => $description_length,
            'required_length' => 500
        ) );
        wp_die();
    }
}
```

**Fonctionnement :**
- Vérifie le statut de l'événement avant sauvegarde
- Si statut = "publish", compte les caractères (sans HTML)
- Si < 500 caractères, retourne une erreur JSON avec le nombre de caractères actuel
- Bloque la publication et retourne un message d'erreur explicite

### 2. Validation côté client (JavaScript)

**Fichier créé :** `/wp-content/plugins/eventlist/assets/js/frontend/event-description-validation.js`

**Fonctionnalités :**

#### a) Compteur de caractères en temps réel
- Affiche un compteur sous le champ de description
- Met à jour en temps réel lors de la saisie
- Deux états visuels :
  - ⚠️ **Rouge** : "Il manque X caractères" (si < 500)
  - ✓ **Vert** : "Description valide (X caractères)" (si ≥ 500)

#### b) Validation avant soumission
- Intercepte le clic sur les boutons "Enregistrer"
- Vérifie le statut sélectionné (publish, pending, draft, private)
- Si statut = "publish" ET description < 500 caractères :
  - Affiche une alerte avec le nombre de caractères manquants
  - Bloque la soumission
  - Scroll automatique vers la section "Présentation"
  - Active l'onglet "Présentation"

#### c) Alerte au changement de statut
- Détecte la sélection du statut "Public"
- Si description < 500 caractères, affiche un avertissement visuel (non bloquant)
- Informe l'utilisateur du nombre de caractères manquants

**Compatibilité :**
- Fonctionne avec TinyMCE (éditeur visuel)
- Fonctionne avec textarea (fallback)
- Compte uniquement le texte, pas les balises HTML

### 3. Enregistrement du script

**Fichier modifié :** `/wp-content/plugins/eventlist/includes/class-el-assets.php`

**Ligne 252 :**
```php
// Event Description Validation (500 chars minimum) - V1 Le Hiboo
wp_enqueue_script('el_event_description_validation', EL_PLUGIN_URI.'assets/js/frontend/event-description-validation.js', array('jquery'),'1.0.0',true );
```

**Conditions de chargement :**
- Uniquement sur les pages de création/édition d'événement
- Paramètres GET : `vendor=create-event` ou `vendor=listing-edit`

## Avantages de cette approche

### Double validation
- **Côté client (JavaScript)** : Feedback immédiat pour l'utilisateur
- **Côté serveur (PHP)** : Sécurité en cas de contournement du JavaScript

### UX optimisée
- Compteur en temps réel
- Alerte visuelle non intrusive
- Blocage uniquement à la soumission si statut = "publish"
- Messages explicites avec nombre de caractères manquants

### Flexibilité
- Sauvegarde en brouillon possible sans contrainte
- Permet de créer progressivement une fiche activité
- Contrainte uniquement lors de la publication finale

### Maintenabilité
- Code modulaire et commenté
- Facilement ajustable (constante MIN_DESCRIPTION_LENGTH)
- Compatible avec les évolutions futures du plugin

## Tests à effectuer

### Test 1 : Sauvegarde en brouillon
1. Créer un événement
2. Remplir le titre et une description de < 500 caractères
3. Sélectionner statut "En attente" ou laisser en brouillon
4. Cliquer sur "Enregistrer"
5. ✅ **Résultat attendu** : Sauvegarde réussie sans erreur

### Test 2 : Publication avec description trop courte
1. Créer/éditer un événement
2. Remplir une description de < 500 caractères (ex: 200 caractères)
3. Sélectionner statut "Public"
4. Cliquer sur "Enregistrer"
5. ✅ **Résultat attendu** :
   - Alerte JavaScript affichée
   - Message : "Il manque X caractères"
   - Scroll vers section Présentation
   - Pas de sauvegarde

### Test 3 : Publication avec description valide
1. Créer/éditer un événement
2. Remplir une description de ≥ 500 caractères
3. Sélectionner statut "Public"
4. Cliquer sur "Enregistrer"
5. ✅ **Résultat attendu** :
   - Compteur vert "Description valide"
   - Sauvegarde réussie
   - Événement publié

### Test 4 : Compteur en temps réel
1. Éditer un événement
2. Aller dans l'onglet "Présentation"
3. Taper du texte dans la description
4. ✅ **Résultat attendu** :
   - Compteur mis à jour en temps réel
   - Passe du rouge au vert à 500 caractères

### Test 5 : Changement de statut
1. Éditer un événement avec description < 500 caractères
2. Aller dans l'onglet "Publication"
3. Sélectionner "Public"
4. ✅ **Résultat attendu** :
   - Avertissement affiché (non bloquant)
   - Message indiquant le nombre de caractères manquants

## Notes techniques

### Comptage des caractères
- Utilise `mb_strlen()` côté serveur pour le support UTF-8
- Utilise `strip_tags()` pour retirer les balises HTML
- Le comptage se fait sur le texte brut (sans formatage)

### Gestion TinyMCE
- Détecte automatiquement si TinyMCE est actif
- Utilise `editor.getContent({format: 'text'})` pour récupérer le texte brut
- Fallback sur textarea si TinyMCE non disponible

### Styles CSS
- Styles inline ajoutés dynamiquement par JavaScript
- Cohérent avec le design existant de Le Hiboo
- Utilise les couleurs standard Bootstrap (warning, success)

## Fichiers modifiés/créés

### Fichiers modifiés
1. `/wp-content/plugins/eventlist/includes/class-el-ajax.php` (ligne ~1785)
2. `/wp-content/plugins/eventlist/includes/class-el-assets.php` (ligne 252)

### Fichiers créés
1. `/wp-content/plugins/eventlist/assets/js/frontend/event-description-validation.js`
2. `/VALIDATION_DESCRIPTION_500_CHARS.md` (cette documentation)

## Compatibilité

- ✅ WordPress 5.0+
- ✅ jQuery
- ✅ TinyMCE 4+
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)
- ✅ Compatible mobile/tablette

## Version

- **Version** : 1.0.0
- **Date** : 2025-10-25
- **Auteur** : V1 Le Hiboo CDC
