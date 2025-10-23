# 📝 Système d'Inscription Double Niveau - Le Hiboo

## ✅ Phase 1 : Page de Sélection du Type de Compte - TERMINÉE

### 🎯 Fichiers créés

1. **Template de page** : `page-templates/template-register-choice.php`
2. **Styles CSS** : `assets/css/register-choice.css`
3. **Dossier images** : `assets/images/`

### 📋 Comment utiliser

1. **Créer la page dans WordPress** :
   - Aller dans **Pages → Ajouter**
   - Titre : "Inscription" ou "Créer un compte"
   - Dans "Attributs de page" → Template : Sélectionner **"Choix Type d'Inscription"**
   - Publier la page

2. **Ajouter les images** :
   - Placer deux images dans `/assets/images/` :
     - `register-vendor.jpg` (image pour organisateurs)
     - `register-customer.jpg` (image pour utilisateurs)
   - Dimensions recommandées : 800x600px minimum

3. **Tester** :
   - Visiter la page créée
   - Vous devriez voir les deux cartes côte à côte
   - Les boutons redirigent vers la même page avec `?type=vendor` ou `?type=customer`

### 🎨 Design

- Design moderne inspiré d'Airbnb
- Responsive (mobile-first)
- Animations fluides au hover
- Gradient colorés pour différencier les types

### 🔄 Prochaines étapes

- Phase 2 : Formulaire utilisateur simple (en cours de développement...)
- Phase 3 : Formulaire partenaire complet
- Phase 4 : Système d'approbation
- Phase 5 : Interface admin

---

## ✅ Phase 2 : Formulaire Utilisateur Simple - TERMINÉE

### 🎯 Fichiers créés

1. **Template formulaire** : `templates/register-customer.php`
2. **Styles CSS** : `assets/css/register-customer.css`
3. **JavaScript** : `assets/js/register-customer.js`
4. **Handler AJAX** : Ajouté dans `functions.php`

### 📋 Fonctionnalités

**Formulaire utilisateur avec :**
- Prénom et nom
- Email
- Mot de passe avec indicateur de force
- Confirmation du mot de passe
- Acceptation des CGU (requis)
- Newsletter (optionnel)
- Bouton "Retour au choix"

**Sécurité :**
- Validation client ET serveur
- Nonce WordPress
- Sanitization de tous les inputs
- Vérification email unique
- Mot de passe minimum 8 caractères

**Expérience utilisateur :**
- Indicateur de force du mot de passe (faible/moyen/fort)
- Toggle visibilité mot de passe
- Validation en temps réel
- Messages d'erreur clairs
- Animations fluides
- Design responsive

**Intégration OTP :**
- Création automatique du code OTP après inscription
- Envoi email avec code 6 chiffres
- Chargement dynamique du formulaire OTP
- Connexion automatique après vérification
- Réutilise le système OTP existant

### 🔄 Workflow complet

1. Utilisateur clique sur "S'inscrire en tant qu'Utilisateur"
2. Redirection vers `?type=customer`
3. Affichage du formulaire d'inscription
4. Remplissage des champs
5. Validation et soumission AJAX
6. Création du compte (rôle: `subscriber`)
7. Génération et envoi du code OTP
8. Affichage du formulaire de vérification OTP
9. Vérification du code
10. Connexion automatique
11. Redirection vers l'accueil

### 🎨 Design

- Design cohérent avec la page de choix
- Gradient bleu pour le thème utilisateur
- Card blanche avec shadow moderne
- Icônes Font Awesome
- Animations entrée/sortie
- Liste des avantages en bas du formulaire

### 🔧 Utilisation

Le formulaire s'affiche automatiquement quand l'URL contient `?type=customer`.

Exemple : `https://votresite.com/inscription?type=customer`

---

## 🚧 Phase 3 : Formulaire Partenaire Complet - EN COURS

À venir...
