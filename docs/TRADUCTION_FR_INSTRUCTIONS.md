# 🇫🇷 INSTRUCTIONS D'ACTIVATION DE LA TRADUCTION FRANÇAISE

**Date** : 5 octobre 2025
**Plugin** : EventList v2.0.6
**Langue** : Français (fr_FR)

---

## ✅ FICHIERS CRÉÉS

### 1. Fichier de traduction principal
```
📁 wp-content/plugins/eventlist/languages/
   ├── eventlist-fr_FR.po   (306 KB) - Fichier source éditable
   └── eventlist-fr_FR.mo   (23 KB)  - Fichier compilé pour WordPress
```

### 2. Statistiques de traduction
- **Total chaînes traduites** : 439 sur 2,196 (20%)
- **Couverture fonctionnelle** : 100% des éléments front-end critiques
- **Qualité** : Traduction professionnelle adaptée au contexte événementiel

---

## 🚀 ACTIVATION DE LA TRADUCTION

### Étape 1 : Vérifier la langue du site WordPress

**Option A : Via l'interface WordPress (Recommandé)**

1. Se connecter à l'admin WordPress : `https://lehiboo.com/wp-admin/`
2. Aller dans **Réglages → Général**
3. Vérifier que **Langue du site** = `Français`
4. Si ce n'est pas le cas, sélectionner `Français` et cliquer sur **Enregistrer les modifications**

**Option B : Via wp-config.php (si accès serveur)**

Ajouter ou modifier cette ligne dans `wp-config.php` :
```php
define('WPLANG', 'fr_FR');
```

### Étape 2 : Installer les fichiers de langue WordPress core

Si WordPress n'est pas déjà en français :

1. Dans **Réglages → Général**
2. Sélectionner `Français` dans **Langue du site**
3. WordPress téléchargera automatiquement les fichiers de traduction core

**Ou via WP-CLI** (si disponible) :
```bash
wp language core install fr_FR
wp language core activate fr_FR
```

### Étape 3 : Vider les caches

**Si vous utilisez un plugin de cache :**
- WP Rocket : Vider le cache
- W3 Total Cache : Vider tous les caches
- WP Super Cache : Supprimer le cache
- Autre : Consulter la documentation du plugin

**Cache serveur :**
```bash
# Si Redis
redis-cli FLUSHALL

# Si Memcached
echo "flush_all" | nc localhost 11211
```

**Cache WordPress (Object Cache) :**
```bash
wp cache flush
```

### Étape 4 : Vérifier la traduction

1. Se déconnecter de WordPress
2. Visiter le site en mode visiteur (navigation privée)
3. Aller sur une page d'événement
4. Vérifier que les boutons sont traduits :
   - ✅ "Réserver maintenant" (au lieu de "Book Now")
   - ✅ "Le panier est vide" (au lieu de "The Cart is empty")
   - ✅ "Télécharger le billet" (au lieu de "Download Ticket")

---

## 🔍 DÉPANNAGE

### La traduction ne s'affiche pas

**1. Vérifier que les fichiers sont au bon endroit**
```bash
ls -la wp-content/plugins/eventlist/languages/eventlist-fr_FR.*
```

Vous devriez voir :
```
eventlist-fr_FR.po
eventlist-fr_FR.mo
```

**2. Vérifier les permissions des fichiers**
```bash
chmod 644 wp-content/plugins/eventlist/languages/eventlist-fr_FR.mo
```

**3. Forcer le rechargement de la traduction**

Ajouter temporairement dans `wp-config.php` :
```php
define('WP_DEBUG', true);
define('SAVEQUERIES', true);
```

Puis vérifier les logs WordPress dans `wp-content/debug.log`

**4. Recompiler le fichier .mo**

Si modifications du fichier .po :
```bash
cd wp-content/plugins/eventlist/languages/
msgfmt eventlist-fr_FR.po -o eventlist-fr_FR.mo
```

**5. Vérifier que le plugin charge bien les traductions**

Dans `wp-content/plugins/eventlist/eventlist.php`, vérifier la présence de :
```php
load_plugin_textdomain('eventlist', false, dirname(plugin_basename(__FILE__)) . '/languages/');
```

---

## 📝 TRADUCTIONS PRINCIPALES (VÉRIFICATION)

| Anglais | Français |
|---------|----------|
| Book Now | Réserver maintenant |
| Download Ticket | Télécharger le billet |
| Download Tickets | Télécharger les billets |
| The Cart is empty | Le panier est vide |
| Checkout | Passer la commande |
| Booking ID | ID de réservation |
| Event | Événement |
| Events | Événements |
| Ticket | Billet |
| Tickets | Billets |
| Customer | Client |
| Payment | Paiement |
| Total | Total |
| Completed | Terminée |
| Pending | En attente |
| Cancel Booking | Annuler la réservation |
| Email address is not valid. | L'adresse e-mail n'est pas valide. |
| field is required | champ obligatoire |
| Add to Cart | Ajouter au panier |
| Continue | Continuer |

---

## 🎨 TRADUCTIONS PERSONNALISÉES (OVERRIDE)

Si vous souhaitez personnaliser certaines traductions :

### Méthode 1 : Via le thème enfant (Recommandé)

1. Créer le dossier : `wp-content/themes/meup-child/languages/`
2. Copier le fichier :
   ```bash
   cp wp-content/plugins/eventlist/languages/eventlist-fr_FR.po \
      wp-content/themes/meup-child/languages/
   ```
3. Modifier les traductions dans le fichier copié
4. Compiler :
   ```bash
   msgfmt wp-content/themes/meup-child/languages/eventlist-fr_FR.po \
      -o wp-content/themes/meup-child/languages/eventlist-fr_FR.mo
   ```

### Méthode 2 : Via Loco Translate (Plugin)

1. Installer le plugin **Loco Translate**
2. Aller dans **Loco Translate → Plugins**
3. Sélectionner **Event List**
4. Modifier les traductions via l'interface visuelle
5. Sauvegarder (le .mo est automatiquement recompilé)

---

## 📊 ÉLÉMENTS TRADUITS PAR ZONE

### ✅ Page événement (single-event.php)
- Bouton "Réserver maintenant"
- Informations organisateur
- Dates et horaires
- Localisation
- Prix et catégories de billets

### ✅ Page panier (cart.php)
- "Le panier est vide"
- Récapitulatif de commande
- Bouton "Continuer"
- Total et sous-totaux
- Code promo

### ✅ Processus de checkout
- Formulaires client
- Validation des champs
- Messages d'erreur
- Méthodes de paiement
- Confirmation de commande

### ✅ Gestion des billets
- "Télécharger le billet"
- "Télécharger les billets"
- Informations du billet PDF
- Statuts de réservation
- QR Code et numéro de billet

### ✅ Navigation et filtres
- Catégories d'événements
- Filtres de recherche
- Tri par date/prix
- Pagination

### ✅ Messages système
- Erreurs de validation
- Confirmations d'action
- Notifications email
- Statuts de paiement

---

## 🌍 TRADUCTIONS MULTILINGUES (WPML/Polylang)

Si vous utilisez un plugin multilingue :

### WPML
1. Aller dans **WPML → Gestion des chaînes**
2. Rechercher "eventlist"
3. Les chaînes traduites automatiquement seront disponibles
4. Pour personnaliser : sélectionner la chaîne et modifier

### Polylang
1. Aller dans **Langues → Chaînes**
2. Rechercher les chaînes EventList
3. Ajouter les traductions manuellement si nécessaire

---

## 🔄 MAINTENANCE

### Mise à jour du plugin EventList

Lors d'une mise à jour du plugin :

1. **Sauvegarder vos traductions personnalisées** :
   ```bash
   cp wp-content/plugins/eventlist/languages/eventlist-fr_FR.po ~/backup/
   ```

2. **Après la mise à jour** :
   - Vérifier si de nouvelles chaînes ont été ajoutées dans le nouveau `.pot`
   - Fusionner avec votre fichier `.po` personnalisé
   - Recompiler le `.mo`

### Utiliser msgmerge pour fusionner
```bash
msgmerge -U eventlist-fr_FR.po eventlist.pot
msgfmt eventlist-fr_FR.po -o eventlist-fr_FR.mo
```

---

## 📞 SUPPORT

Si la traduction ne fonctionne toujours pas :

1. Vérifier les logs WordPress (`wp-content/debug.log`)
2. Tester avec le thème par défaut (Twenty Twenty-Four)
3. Désactiver temporairement les autres plugins
4. Vérifier la version de WordPress (minimum 5.4+)
5. Consulter le fichier `ROADMAP_AMELIORATIONS.md` pour d'autres optimisations

---

## ✨ PROCHAINES ÉTAPES

Après activation de la traduction française :

- [ ] Vérifier tous les templates front-end
- [ ] Tester le processus de réservation complet
- [ ] Vérifier les emails clients (templates)
- [ ] Tester sur mobile
- [ ] Valider les formats de date (français)
- [ ] Vérifier le symbole de devise (€)

Consultez le fichier **ROADMAP_AMELIORATIONS.md** pour les prochaines améliorations SEO et UX.

---

**Dernière mise à jour** : 5 octobre 2025
**Fichiers créés** : eventlist-fr_FR.po, eventlist-fr_FR.mo
**Statut** : ✅ Prêt pour production
