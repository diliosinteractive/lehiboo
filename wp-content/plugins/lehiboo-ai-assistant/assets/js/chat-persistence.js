/**
 * Le Hiboo Chat - Persistence & Onboarding System
 * Système hybride : localStorage (guests) + Database (authenticated users)
 *
 * @version 1.0.0
 */

(function() {
  'use strict';

  /**
   * Persistence Manager
   * Gère la sauvegarde hybride localStorage + DB
   */
  class ChatPersistenceManager {
    constructor(config = {}) {
      this.config = {
        apiEndpoint: config.apiEndpoint || '/wp-json/lehiboo/v1',
        nonce: config.nonce || '',
        userId: config.userId || null,
        isLoggedIn: !!config.userId,
        autoSaveInterval: config.autoSaveInterval || 30000, // 30 secondes
        ...config
      };

      this.autoSaveTimer = null;
    }

    /**
     * Démarrer l'auto-save
     */
    startAutoSave(chatInstance) {
      if (this.autoSaveTimer) {
        clearInterval(this.autoSaveTimer);
      }

      this.autoSaveTimer = setInterval(async () => {
        await this.saveConversation(chatInstance.state);
      }, this.config.autoSaveInterval);

      console.log('[Persistence] Auto-save started');
    }

    /**
     * Arrêter l'auto-save
     */
    stopAutoSave() {
      if (this.autoSaveTimer) {
        clearInterval(this.autoSaveTimer);
        this.autoSaveTimer = null;
      }
    }

    /**
     * Sauvegarder la conversation
     */
    async saveConversation(state) {
      try {
        if (this.config.isLoggedIn) {
          // Utilisateur connecté → DB
          await this.saveToDB(state);
        } else {
          // Guest → localStorage
          this.saveToLocalStorage(state);
        }
        return true;
      } catch (error) {
        console.error('[Persistence] Save error:', error);
        // Fallback vers localStorage en cas d'erreur DB
        this.saveToLocalStorage(state);
        return false;
      }
    }

    /**
     * Charger la conversation
     */
    async loadConversation(conversationId) {
      try {
        if (this.config.isLoggedIn) {
          // Utilisateur connecté → DB
          const data = await this.loadFromDB(conversationId);
          if (data) return data;
        }

        // Fallback ou guest → localStorage
        return this.loadFromLocalStorage();
      } catch (error) {
        console.error('[Persistence] Load error:', error);
        return this.loadFromLocalStorage();
      }
    }

    /**
     * Sauvegarder dans localStorage
     */
    saveToLocalStorage(state) {
      try {
        const data = {
          conversationId: state.conversationId,
          messages: state.messages,
          userContext: state.userContext,
          currentStage: state.currentStage,
          timestamp: new Date().toISOString(),
          userId: this.config.userId || 'guest',
        };

        localStorage.setItem('lehiboo_conversation', JSON.stringify(data));
        console.log('[Persistence] Saved to localStorage');
      } catch (e) {
        console.error('[Persistence] localStorage error:', e);
      }
    }

    /**
     * Charger depuis localStorage
     */
    loadFromLocalStorage() {
      try {
        const stored = localStorage.getItem('lehiboo_conversation');
        if (!stored) return null;

        const data = JSON.parse(stored);

        // Vérifier l'âge (< 7 jours)
        const age = Date.now() - new Date(data.timestamp).getTime();
        if (age > 7 * 24 * 60 * 60 * 1000) {
          localStorage.removeItem('lehiboo_conversation');
          return null;
        }

        console.log('[Persistence] Loaded from localStorage');
        return data;
      } catch (e) {
        console.error('[Persistence] localStorage error:', e);
        return null;
      }
    }

    /**
     * Sauvegarder dans la DB (utilisateurs connectés)
     */
    async saveToDB(state) {
      const response = await fetch(`${this.config.apiEndpoint}/conversation/save`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': this.config.nonce,
        },
        body: JSON.stringify({
          conversationId: state.conversationId,
          messages: state.messages,
          userContext: state.userContext,
          currentStage: state.currentStage,
        }),
      });

      if (!response.ok) {
        throw new Error(`DB save failed: ${response.status}`);
      }

      const result = await response.json();
      console.log('[Persistence] Saved to DB');
      return result;
    }

    /**
     * Charger depuis la DB (utilisateurs connectés)
     */
    async loadFromDB(conversationId) {
      const response = await fetch(
        `${this.config.apiEndpoint}/conversation/load?conversationId=${conversationId}`,
        {
          headers: {
            'X-WP-Nonce': this.config.nonce,
          },
        }
      );

      if (response.status === 404) {
        return null; // Pas de conversation trouvée
      }

      if (!response.ok) {
        throw new Error(`DB load failed: ${response.status}`);
      }

      const result = await response.json();
      console.log('[Persistence] Loaded from DB');
      return result.conversation;
    }

    /**
     * Migrer localStorage → DB (lors de la connexion)
     */
    async migrateLocalStorageToDB() {
      const localData = this.loadFromLocalStorage();
      if (!localData || !this.config.isLoggedIn) {
        return false;
      }

      try {
        await this.saveToDB(localData);
        console.log('[Persistence] Migration localStorage → DB success');

        // Nettoyer localStorage après migration réussie
        localStorage.removeItem('lehiboo_conversation');
        return true;
      } catch (error) {
        console.error('[Persistence] Migration failed:', error);
        return false;
      }
    }

    /**
     * Récupérer toutes les conversations (utilisateurs connectés)
     */
    async getAllConversations() {
      if (!this.config.isLoggedIn) {
        return [];
      }

      try {
        const response = await fetch(`${this.config.apiEndpoint}/conversations`, {
          headers: {
            'X-WP-Nonce': this.config.nonce,
          },
        });

        if (!response.ok) {
          throw new Error(`Failed to load conversations: ${response.status}`);
        }

        const result = await response.json();
        return result.conversations || [];
      } catch (error) {
        console.error('[Persistence] Failed to load conversations:', error);
        return [];
      }
    }

    /**
     * Supprimer une conversation
     */
    async deleteConversation(conversationId) {
      if (!this.config.isLoggedIn) {
        // Guest: supprimer localStorage
        localStorage.removeItem('lehiboo_conversation');
        return true;
      }

      try {
        const response = await fetch(
          `${this.config.apiEndpoint}/conversation/${conversationId}`,
          {
            method: 'DELETE',
            headers: {
              'X-WP-Nonce': this.config.nonce,
            },
          }
        );

        if (!response.ok) {
          throw new Error(`Failed to delete conversation: ${response.status}`);
        }

        return true;
      } catch (error) {
        console.error('[Persistence] Failed to delete conversation:', error);
        return false;
      }
    }
  }

  /**
   * Onboarding Manager
   * Gère le modal d'onboarding marketing
   */
  class OnboardingManager {
    constructor(config = {}) {
      this.config = {
        isLoggedIn: config.isLoggedIn || false,
        loginUrl: config.loginUrl || '/wp-login.php',
        registerUrl: config.registerUrl || '/wp-login.php?action=register',
        ...config
      };

      this.shown = false;
      this.dismissedKey = 'lehiboo_onboarding_dismissed';
    }

    /**
     * Vérifier si le modal doit être affiché
     */
    shouldShow() {
      // Ne pas afficher si déjà connecté
      if (this.config.isLoggedIn) {
        return false;
      }

      // Ne pas afficher si déjà dismissed récemment (< 7 jours)
      const dismissed = localStorage.getItem(this.dismissedKey);
      if (dismissed) {
        const age = Date.now() - parseInt(dismissed);
        if (age < 7 * 24 * 60 * 60 * 1000) {
          return false;
        }
      }

      // Ne pas afficher si déjà affiché dans cette session
      if (this.shown) {
        return false;
      }

      return true;
    }

    /**
     * Afficher le modal d'onboarding après X messages (trigger naturel)
     */
    showAfterMessages(messageCount, chatInstance) {
      // Afficher après le 3ème message
      if (messageCount === 3 && this.shouldShow()) {
        // Attendre 2 secondes après la réponse de l'IA
        setTimeout(() => {
          this.show(chatInstance);
        }, 2000);
      }
    }

    /**
     * Afficher le modal
     */
    show(chatInstance) {
      if (!this.shouldShow()) return;

      this.shown = true;

      // Créer le modal
      const modal = this.createModal(chatInstance);
      document.body.appendChild(modal);

      // Afficher avec animation
      requestAnimationFrame(() => {
        modal.classList.add('active');
      });
    }

    /**
     * Créer le HTML du modal
     */
    createModal(chatInstance) {
      const modal = document.createElement('div');
      modal.className = 'lehiboo-onboarding-modal';
      modal.id = 'lehiboo-onboarding';

      modal.innerHTML = `
        <div class="lehiboo-onboarding-backdrop"></div>
        <div class="lehiboo-onboarding-content">
          <button class="lehiboo-onboarding-close" aria-label="Fermer">×</button>

          <div class="lehiboo-onboarding-header">
            <div class="lehiboo-onboarding-icon">
              <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="20" fill="#FF6B6B" opacity="0.1"/>
                <path d="M24 16v12M24 32h.01" stroke="#FF6B6B" stroke-width="3" stroke-linecap="round"/>
              </svg>
            </div>
            <h2 class="lehiboo-onboarding-title">
              Continuez votre recherche en toute sérénité
            </h2>
            <p class="lehiboo-onboarding-subtitle">
              Créez un compte gratuit pour ne jamais perdre vos conversations
            </p>
          </div>

          <div class="lehiboo-onboarding-benefits">
            <div class="lehiboo-onboarding-benefit">
              <div class="lehiboo-onboarding-benefit-icon">💬</div>
              <div class="lehiboo-onboarding-benefit-text">
                <strong>Historique sauvegardé</strong>
                <span>Retrouvez toutes vos conversations, même en changeant d'appareil</span>
              </div>
            </div>

            <div class="lehiboo-onboarding-benefit">
              <div class="lehiboo-onboarding-benefit-icon">❤️</div>
              <div class="lehiboo-onboarding-benefit-text">
                <strong>Favoris synchronisés</strong>
                <span>Vos activités préférées accessibles partout</span>
              </div>
            </div>

            <div class="lehiboo-onboarding-benefit">
              <div class="lehiboo-onboarding-benefit-icon">⚡</div>
              <div class="lehiboo-onboarding-benefit-text">
                <strong>Réservation express</strong>
                <span>Réservez en 1 clic avec vos informations pré-remplies</span>
              </div>
            </div>

            <div class="lehiboo-onboarding-benefit">
              <div class="lehiboo-onboarding-benefit-icon">🎁</div>
              <div class="lehiboo-onboarding-benefit-text">
                <strong>Offres exclusives</strong>
                <span>Accédez à des réductions réservées aux membres</span>
              </div>
            </div>
          </div>

          <div class="lehiboo-onboarding-actions">
            <button class="lehiboo-onboarding-btn lehiboo-onboarding-btn-primary" data-action="register">
              Créer mon compte gratuit
            </button>
            <button class="lehiboo-onboarding-btn lehiboo-onboarding-btn-secondary" data-action="login">
              J'ai déjà un compte
            </button>
          </div>

          <div class="lehiboo-onboarding-footer">
            <button class="lehiboo-onboarding-dismiss" data-action="dismiss">
              Continuer sans compte
            </button>
            <p class="lehiboo-onboarding-disclaimer">
              Inscription gratuite • Pas de carte bancaire requise
            </p>
          </div>
        </div>
      `;

      // Event listeners
      const backdrop = modal.querySelector('.lehiboo-onboarding-backdrop');
      const closeBtn = modal.querySelector('.lehiboo-onboarding-close');
      const registerBtn = modal.querySelector('[data-action="register"]');
      const loginBtn = modal.querySelector('[data-action="login"]');
      const dismissBtn = modal.querySelector('[data-action="dismiss"]');

      const close = () => {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
      };

      backdrop.addEventListener('click', close);
      closeBtn.addEventListener('click', close);

      registerBtn.addEventListener('click', () => {
        // Sauvegarder la conversation avant de rediriger
        chatInstance.saveConversation();

        // Track analytics
        chatInstance.trackEvent('onboarding_register_click');

        // Rediriger vers inscription
        window.location.href = this.config.registerUrl + '&redirect_to=' + encodeURIComponent(window.location.href);
      });

      loginBtn.addEventListener('click', () => {
        // Sauvegarder la conversation avant de rediriger
        chatInstance.saveConversation();

        // Track analytics
        chatInstance.trackEvent('onboarding_login_click');

        // Rediriger vers connexion
        window.location.href = this.config.loginUrl + '?redirect_to=' + encodeURIComponent(window.location.href);
      });

      dismissBtn.addEventListener('click', () => {
        localStorage.setItem(this.dismissedKey, Date.now().toString());
        chatInstance.trackEvent('onboarding_dismissed');
        close();
      });

      return modal;
    }
  }

  /**
   * Favorites Manager
   * Gère les favoris utilisateur
   */
  class FavoritesManager {
    constructor(config = {}) {
      this.config = {
        apiEndpoint: config.apiEndpoint || '/wp-json/lehiboo/v1',
        nonce: config.nonce || '',
        isLoggedIn: config.isLoggedIn || false,
        ...config
      };

      this.favorites = new Set();
      this.loadFavorites();
    }

    /**
     * Charger les favoris
     */
    async loadFavorites() {
      if (!this.config.isLoggedIn) {
        // Guest: localStorage
        const stored = localStorage.getItem('lehiboo_favorites');
        if (stored) {
          this.favorites = new Set(JSON.parse(stored));
        }
        return;
      }

      // DB
      try {
        const response = await fetch(`${this.config.apiEndpoint}/favorites`, {
          headers: {
            'X-WP-Nonce': this.config.nonce,
          },
        });

        if (response.ok) {
          const result = await response.json();
          this.favorites = new Set(result.favorites.map(f => f.eventId.toString()));
        }
      } catch (error) {
        console.error('[Favorites] Load error:', error);
      }
    }

    /**
     * Ajouter aux favoris
     */
    async addFavorite(eventId, conversationId = null) {
      this.favorites.add(eventId.toString());

      if (!this.config.isLoggedIn) {
        // Guest: localStorage
        localStorage.setItem('lehiboo_favorites', JSON.stringify([...this.favorites]));
        return true;
      }

      // DB
      try {
        const response = await fetch(`${this.config.apiEndpoint}/favorites/add`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': this.config.nonce,
          },
          body: JSON.stringify({
            eventId,
            conversationId,
          }),
        });

        return response.ok;
      } catch (error) {
        console.error('[Favorites] Add error:', error);
        return false;
      }
    }

    /**
     * Retirer des favoris
     */
    async removeFavorite(eventId) {
      this.favorites.delete(eventId.toString());

      if (!this.config.isLoggedIn) {
        // Guest: localStorage
        localStorage.setItem('lehiboo_favorites', JSON.stringify([...this.favorites]));
        return true;
      }

      // DB
      try {
        const response = await fetch(`${this.config.apiEndpoint}/favorites/remove`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': this.config.nonce,
          },
          body: JSON.stringify({
            eventId,
          }),
        });

        return response.ok;
      } catch (error) {
        console.error('[Favorites] Remove error:', error);
        return false;
      }
    }

    /**
     * Vérifier si un événement est favori
     */
    isFavorite(eventId) {
      return this.favorites.has(eventId.toString());
    }

    /**
     * Toggle favori
     */
    async toggleFavorite(eventId, conversationId = null) {
      if (this.isFavorite(eventId)) {
        await this.removeFavorite(eventId);
        return false;
      } else {
        await this.addFavorite(eventId, conversationId);
        return true;
      }
    }
  }

  // Exporter les classes
  window.ChatPersistenceManager = ChatPersistenceManager;
  window.OnboardingManager = OnboardingManager;
  window.FavoritesManager = FavoritesManager;

})();
