# 🤝 Guide de Contribution - Le Hiboo AI Assistant

Merci de votre intérêt pour contribuer au projet Le Hiboo AI Assistant !

---

## 📋 Table des Matières

1. [Code de Conduite](#code-de-conduite)
2. [Comment Contribuer](#comment-contribuer)
3. [Standards de Code](#standards-de-code)
4. [Structure Git](#structure-git)
5. [Tests](#tests)
6. [Documentation](#documentation)
7. [Pull Requests](#pull-requests)

---

## 🌟 Code de Conduite

### Nos Valeurs

- **Respect** : Traiter tous les contributeurs avec respect
- **Collaboration** : Travailler ensemble pour améliorer le projet
- **Qualité** : Maintenir des standards de code élevés
- **Sécurité** : Prioriser la sécurité et la vie privée des utilisateurs

---

## 💡 Comment Contribuer

### Types de Contributions

#### 1. Rapporter un Bug 🐛

Avant de créer un rapport de bug :
1. Vérifier si le bug n'est pas déjà rapporté
2. Tester sur la dernière version
3. Rassembler les informations de reproduction

**Template de Bug Report** :
```markdown
## Description
[Description claire du bug]

## Étapes de Reproduction
1. Aller à '...'
2. Cliquer sur '...'
3. Voir l'erreur

## Comportement Attendu
[Ce qui devrait se passer]

## Comportement Actuel
[Ce qui se passe réellement]

## Environnement
- OS: [ex: macOS 14.0]
- Browser: [ex: Chrome 120]
- Node.js: [ex: v18.17.0]
- WordPress: [ex: 6.4]

## Logs/Screenshots
[Ajouter logs ou captures d'écran]
```

#### 2. Proposer une Fonctionnalité ✨

**Template de Feature Request** :
```markdown
## Problème à Résoudre
[Quel problème cette fonctionnalité résout-elle ?]

## Solution Proposée
[Description de votre idée]

## Alternatives Considérées
[Autres approches possibles]

## Impact
- Utilisateurs affectés: [tous/certains]
- Complexité: [faible/moyenne/haute]
- Priorité: [faible/moyenne/haute]
```

#### 3. Améliorer la Documentation 📚

La documentation est cruciale ! Vous pouvez :
- Corriger des typos
- Améliorer la clarté
- Ajouter des exemples
- Traduire en d'autres langues

#### 4. Contribuer du Code 💻

Voir la section [Pull Requests](#pull-requests)

---

## 📝 Standards de Code

### PHP (WordPress Plugin)

#### Style Guide

```php
<?php
/**
 * Description de la classe
 *
 * @package LeHiboo_AI_Assistant
 */

class LeHiboo_Chat_Handler {
    /**
     * Description de la méthode
     *
     * @param string $message Le message utilisateur
     * @return array Réponse formatée
     */
    public function handle_message( $message ) {
        // Validation
        if ( empty( $message ) ) {
            return array(
                'success' => false,
                'error'   => 'Message vide',
            );
        }

        // Traitement
        $response = $this->process_message( $message );

        return array(
            'success' => true,
            'data'    => $response,
        );
    }

    /**
     * Méthode privée
     */
    private function process_message( $message ) {
        // Implementation
    }
}
```

**Conventions** :
- ✅ WordPress Coding Standards (WPCS)
- ✅ Indentation : 4 espaces (pas de tabs)
- ✅ Nommage : `snake_case` pour fonctions/variables
- ✅ Classes : `LeHiboo_Class_Name`
- ✅ Hooks : `lehiboo_hook_name`
- ✅ DocBlocks obligatoires

**Vérification** :
```bash
# Installer PHP_CodeSniffer
composer global require "squizlabs/php_codesniffer=*"

# Vérifier
phpcs --standard=WordPress wp-content/plugins/lehiboo-ai-assistant/
```

### JavaScript (Frontend)

#### Style Guide

```javascript
/**
 * Classe gérant l'interface chat
 */
class LeHibooChatInterface {
    /**
     * Constructeur
     * @param {Object} config - Configuration
     */
    constructor(config) {
        this.config = config;
        this.state = {
            isOpen: false,
            messages: [],
        };

        this.init();
    }

    /**
     * Initialisation
     */
    init() {
        this.buildHTML();
        this.attachEvents();
    }

    /**
     * Envoyer un message
     * @param {string} message - Message utilisateur
     * @returns {Promise<Object>} Réponse du backend
     */
    async sendMessage(message) {
        // Validation
        if (!message || message.trim().length === 0) {
            throw new Error('Message vide');
        }

        // Appel API
        const response = await fetch(this.config.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message }),
        });

        return response.json();
    }
}
```

**Conventions** :
- ✅ ES6+ (classes, arrow functions, async/await)
- ✅ Indentation : 2 espaces
- ✅ Nommage : `camelCase`
- ✅ Constantes : `UPPER_SNAKE_CASE`
- ✅ JSDoc pour toutes les fonctions publiques
- ✅ Pas de `var`, utiliser `const`/`let`

**Vérification** :
```bash
# Installer ESLint
npm install -g eslint

# Vérifier
eslint wp-content/plugins/lehiboo-ai-assistant/assets/js/
```

### Node.js (Backend)

#### Style Guide

```javascript
import logger from '../utils/logger.js';

/**
 * Service AI pour générer les réponses
 */
class AIService {
    /**
     * Constructeur
     * @param {Object} config - Configuration AI
     */
    constructor(config) {
        this.config = config;
        this.model = config.defaultModel;
    }

    /**
     * Générer une réponse IA
     * @param {string} message - Message utilisateur
     * @param {Array} history - Historique conversation
     * @param {Object} context - Contexte utilisateur
     * @returns {Promise<Object>} Réponse IA
     */
    async generateResponse(message, history = [], context = {}) {
        try {
            logger.info('Generating AI response', {
                messageLength: message.length
            });

            // Build prompt
            const prompt = this.buildPrompt(message, history, context);

            // Call AI
            const response = await this.callAI(prompt);

            logger.info('AI response generated', {
                tokensUsed: response.usage.tokens
            });

            return response;
        } catch (error) {
            logger.error('AI response generation failed', {
                error: error.message
            });
            throw error;
        }
    }

    /**
     * Construire le prompt
     * @private
     */
    buildPrompt(message, history, context) {
        // Implementation
    }
}

export default AIService;
```

**Conventions** :
- ✅ ES Modules (`import`/`export`)
- ✅ Indentation : 2 espaces
- ✅ Nommage : `camelCase`
- ✅ Classes : `PascalCase`
- ✅ Async/await (pas de callbacks)
- ✅ Error handling avec try/catch
- ✅ Logging avec Winston

**Vérification** :
```bash
# Vérifier avec ESLint
cd lehiboo-ai-backend
npm run lint
```

### CSS

#### Style Guide

```css
/**
 * Interface Chat Immersif
 */

/* Variables globales */
:root {
    --lehiboo-primary: #FF601F;
    --lehiboo-primary-dark: #E55519;
    --lehiboo-secondary: #FF7A3D;
    --font-family: 'Montserrat', sans-serif;
    --transition-speed: 250ms;
}

/* Container principal */
.lehiboo-chat-container {
    position: fixed;
    top: 0;
    right: 0;
    width: 50vw;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
    transform: translateX(100%);
    transition: transform var(--transition-speed) ease;
    z-index: 9999;
}

/* État actif */
.lehiboo-chat-container.active {
    transform: translateX(0);
}

/* Message bubble */
.lehiboo-message {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    animation: fadeIn 300ms ease;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .lehiboo-chat-container {
        width: 100vw;
    }
}
```

**Conventions** :
- ✅ BEM Naming : `.block__element--modifier`
- ✅ CSS Variables pour couleurs/tailles
- ✅ Mobile-first responsive
- ✅ Indentation : 4 espaces
- ✅ Commentaires clairs
- ✅ Animations performantes (transform/opacity)

---

## 🌿 Structure Git

### Branches

```
main           Production (stable)
  └─ develop   Développement (features intégrées)
       ├─ feature/chat-streaming
       ├─ feature/mcp-tools-v2
       ├─ bugfix/rate-limiting
       └─ docs/api-documentation
```

**Conventions de Nommage** :
- `feature/nom-feature` - Nouvelle fonctionnalité
- `bugfix/nom-bug` - Correction de bug
- `hotfix/nom-urgence` - Correction urgente production
- `docs/nom-doc` - Documentation
- `refactor/nom-refactor` - Refactoring
- `test/nom-test` - Ajout de tests

### Commits

**Format** :
```
<type>(<scope>): <description courte>

<description longue (optionnelle)>

<footer (optionnel)>
```

**Types** :
- `feat` - Nouvelle fonctionnalité
- `fix` - Correction de bug
- `docs` - Documentation
- `style` - Formatting (pas de changement code)
- `refactor` - Refactoring
- `perf` - Amélioration performance
- `test` - Ajout/modification tests
- `chore` - Maintenance (dépendances, config)

**Exemples** :
```bash
# Feature
git commit -m "feat(chat): add streaming responses support"

# Bugfix
git commit -m "fix(security): prevent XSS in message input"

# Documentation
git commit -m "docs(readme): update installation instructions"

# Refactor
git commit -m "refactor(ai-service): simplify prompt building logic"
```

### Workflow

```bash
# 1. Créer une branche depuis develop
git checkout develop
git pull origin develop
git checkout -b feature/ma-feature

# 2. Développer et commiter
git add .
git commit -m "feat(scope): description"

# 3. Pousser
git push origin feature/ma-feature

# 4. Créer Pull Request sur GitHub
# develop ← feature/ma-feature

# 5. Après merge, supprimer la branche
git checkout develop
git pull origin develop
git branch -d feature/ma-feature
```

---

## 🧪 Tests

### Tests Requis

#### PHP (WordPress)

```php
<?php
/**
 * Tests pour Chat Handler
 */
class Test_Chat_Handler extends WP_UnitTestCase {
    public function test_handle_message_validates_empty() {
        $handler = new LeHiboo_Chat_Handler();
        $result = $handler->handle_message('');

        $this->assertFalse($result['success']);
        $this->assertEquals('Message vide', $result['error']);
    }

    public function test_handle_message_sanitizes_xss() {
        $handler = new LeHiboo_Chat_Handler();
        $malicious = '<script>alert("XSS")</script>';
        $result = $handler->handle_message($malicious);

        $this->assertFalse($result['success']);
    }
}
```

**Lancer les tests** :
```bash
cd wp-content/plugins/lehiboo-ai-assistant
phpunit
```

#### JavaScript (Frontend)

```javascript
/**
 * Tests pour Chat Interface
 */
describe('LeHibooChatInterface', () => {
    let chat;

    beforeEach(() => {
        chat = new LeHibooChatInterface({
            apiUrl: 'http://localhost/wp-json/lehiboo/v1/chat',
        });
    });

    test('validates message length', () => {
        const longMessage = 'a'.repeat(2001);
        expect(() => chat.validateMessage(longMessage))
            .toThrow('Message trop long');
    });

    test('detects XSS patterns', () => {
        const xss = '<script>alert(1)</script>';
        expect(() => chat.validateMessage(xss))
            .toThrow('Contenu dangereux');
    });
});
```

**Lancer les tests** :
```bash
npm test
```

#### Node.js (Backend)

```javascript
/**
 * Tests pour AI Service
 */
import { describe, test, expect } from 'vitest';
import AIService from '../src/services/ai-service.js';

describe('AIService', () => {
    test('generates response with valid input', async () => {
        const service = new AIService(config);
        const response = await service.generateResponse(
            'Bonjour',
            [],
            {}
        );

        expect(response).toHaveProperty('message');
        expect(response).toHaveProperty('conversationStage');
    });

    test('handles API errors gracefully', async () => {
        const service = new AIService({ ...config, apiKey: 'invalid' });

        await expect(
            service.generateResponse('Test')
        ).rejects.toThrow();
    });
});
```

**Lancer les tests** :
```bash
cd lehiboo-ai-backend
npm test
```

### Coverage Minimum

- **PHP** : 70% couverture
- **JavaScript** : 70% couverture
- **Node.js** : 80% couverture

---

## 📚 Documentation

### Obligatoire pour Chaque PR

1. **README** - Mettre à jour si nouvelle feature
2. **CHANGELOG.md** - Ajouter l'entrée
3. **Code Comments** - DocBlocks/JSDoc
4. **Tests** - Documenter les cas de test

### Format DocBlocks

**PHP** :
```php
/**
 * Description courte de la fonction
 *
 * Description longue optionnelle avec plus de détails
 * sur le comportement et les cas particuliers.
 *
 * @since 1.0.0
 * @param string $param1 Description du paramètre
 * @param int    $param2 Description du paramètre
 * @return array {
 *     @type bool   $success Statut de succès
 *     @type string $message Message de réponse
 * }
 */
function ma_fonction( $param1, $param2 ) {
    // Implementation
}
```

**JavaScript** :
```javascript
/**
 * Description courte de la fonction
 *
 * Description longue optionnelle.
 *
 * @param {string} param1 - Description du paramètre
 * @param {number} param2 - Description du paramètre
 * @returns {Promise<Object>} Résultat avec success et data
 * @throws {Error} Si param1 est vide
 * @example
 * const result = await maFonction('test', 42);
 */
async function maFonction(param1, param2) {
    // Implementation
}
```

---

## 🔀 Pull Requests

### Checklist Avant de Soumettre

- [ ] **Code testé localement** (développement + production)
- [ ] **Tests ajoutés/modifiés** pour couvrir les changements
- [ ] **Documentation mise à jour** (README, CHANGELOG)
- [ ] **Code formaté** selon standards
- [ ] **Commits bien nommés** selon convention
- [ ] **Pas de console.log/var_dump** oubliés
- [ ] **Pas de conflits** avec develop
- [ ] **CI/CD passe** (si configuré)

### Template Pull Request

```markdown
## Description
[Décrire les changements apportés]

## Type de Changement
- [ ] Bug fix (non-breaking change)
- [ ] Nouvelle fonctionnalité (non-breaking change)
- [ ] Breaking change (modifie API existante)
- [ ] Documentation

## Motivation et Contexte
[Pourquoi ce changement est nécessaire ?]

## Comment Tester
1. Étape 1
2. Étape 2
3. Résultat attendu

## Screenshots (si applicable)
[Ajouter des captures d'écran]

## Checklist
- [ ] Code suit les standards du projet
- [ ] Tests ajoutés/modifiés
- [ ] Documentation mise à jour
- [ ] CHANGELOG.md mis à jour
- [ ] Pas de console.log/var_dump
- [ ] Tests passent localement
```

### Process de Review

1. **Soumission** : Créer la PR avec template complété
2. **Review** : Attendre review d'au moins 1 mainteneur
3. **Modifications** : Appliquer les changements demandés
4. **Approbation** : PR approuvée par mainteneur
5. **Merge** : Squash and merge dans develop
6. **Cleanup** : Supprimer la branche

---

## 🚀 Déploiement

### Développement

```bash
# Backend
cd lehiboo-ai-backend
npm run dev

# WordPress
# Activer le plugin dans WP Admin
```

### Staging

```bash
# Déployer sur Railway/Vercel
git push origin develop

# Auto-deploy configuré
```

### Production

```bash
# Merge develop → main
git checkout main
git merge develop
git push origin main

# Tag version
git tag v1.1.0
git push --tags

# Deploy automatique
```

---

## 💬 Communication

### Où Poser des Questions

- **GitHub Issues** : Bugs, features, questions
- **Email** : dev@lehiboo.com
- **Slack** : #lehiboo-ai-dev (si équipe interne)

### Temps de Réponse

- **Issues** : 24-48h
- **PRs** : 48-72h
- **Questions** : 24h

---

## 🏆 Reconnaissance

Tous les contributeurs seront mentionnés dans :
- `CONTRIBUTORS.md`
- Release notes
- README.md (contributeurs majeurs)

---

## 📜 Licence

En contribuant, vous acceptez que vos contributions soient sous la même licence que le projet (voir `LICENSE`).

---

**Merci de contribuer à Le Hiboo AI Assistant !** 🎉

Pour toute question, n'hésitez pas à ouvrir une issue ou contacter l'équipe.

**Équipe Le Hiboo**
dev@lehiboo.com
