/**
 * Le Hiboo AI Assistant - Chat Interface
 * Modern conversational interface with security and accessibility
 *
 * @version 1.0.0
 */

(function() {
  'use strict';

  /**
   * Rate Limiter Client-Side
   */
  class ClientRateLimiter {
    constructor(maxMessages = 10, timeWindow = 60000) {
      this.maxMessages = maxMessages;
      this.timeWindow = timeWindow;
      this.messages = this.loadFromStorage();
    }

    loadFromStorage() {
      try {
        const stored = localStorage.getItem('lehiboo_rate_limit');
        return stored ? JSON.parse(stored) : [];
      } catch (e) {
        return [];
      }
    }

    saveToStorage() {
      try {
        localStorage.setItem('lehiboo_rate_limit', JSON.stringify(this.messages));
      } catch (e) {
        console.warn('Failed to save rate limit data');
      }
    }

    canSendMessage() {
      const now = Date.now();
      this.messages = this.messages.filter(time => now - time < this.timeWindow);

      if (this.messages.length >= this.maxMessages) {
        const oldestMessage = Math.min(...this.messages);
        const waitTime = Math.ceil((this.timeWindow - (now - oldestMessage)) / 1000);
        return {
          allowed: false,
          waitTime,
          message: `Trop de messages. Veuillez attendre ${waitTime}s.`
        };
      }

      this.messages.push(now);
      this.saveToStorage();
      return { allowed: true };
    }
  }

  /**
   * Input Sanitizer & Validator
   */
  class InputValidator {
    static MAX_LENGTH = 2000;
    static DANGEROUS_PATTERNS = [
      /<script/i,
      /javascript:/i,
      /onerror=/i,
      /onclick=/i,
      /<iframe/i,
      /eval\(/i,
      /document\.write/i
    ];

    static sanitize(input) {
      if (typeof input !== 'string') return '';

      // Trim whitespace
      input = input.trim();

      // Basic HTML escape
      const div = document.createElement('div');
      div.textContent = input;
      return div.innerHTML;
    }

    static validate(input) {
      // Empty check
      if (!input || input.trim().length === 0) {
        return { valid: false, error: 'Le message ne peut pas être vide' };
      }

      // Length check
      if (input.length > this.MAX_LENGTH) {
        return {
          valid: false,
          error: `Le message est trop long (max ${this.MAX_LENGTH} caractères)`
        };
      }

      // Dangerous content check
      for (const pattern of this.DANGEROUS_PATTERNS) {
        if (pattern.test(input)) {
          return {
            valid: false,
            error: 'Contenu non autorisé détecté'
          };
        }
      }

      return { valid: true, value: input };
    }
  }

  /**
   * Main Chat Interface Class
   */
  class LehibooChatInterface {
    constructor(config = {}) {
      this.config = {
        apiEndpoint: config.apiEndpoint || '/wp-json/lehiboo/v1/chat',
        apiBaseUrl: config.apiBaseUrl || '/wp-json/lehiboo/v1',
        nonce: config.nonce || '',
        userId: config.userId || null,
        isLoggedIn: config.isLoggedIn || false,
        userDisplayName: config.userDisplayName || '',
        loginUrl: config.loginUrl || '/wp-login.php',
        registerUrl: config.registerUrl || '/wp-login.php?action=register',
        debug: config.debug || false,
        maxRetries: config.maxRetries || 3,
        persistence: config.persistence || {},
        onboarding: config.onboarding || {},
        ...config
      };

      this.state = {
        isOpen: false,
        isTyping: false,
        messages: [],
        conversationId: this.generateConversationId(),
        userContext: {},
        currentStage: 'greeting',
        messageCount: 0
      };

      this.rateLimiter = new ClientRateLimiter();
      this.persistence = new ChatPersistenceManager({
        apiEndpoint: this.config.apiBaseUrl,
        nonce: this.config.nonce,
        userId: this.config.userId,
        isLoggedIn: this.config.isLoggedIn, // Passer explicitement isLoggedIn
        autoSaveInterval: this.config.persistence.autoSaveInterval
      });
      this.onboarding = new OnboardingManager({
        isLoggedIn: this.config.isLoggedIn,
        loginUrl: this.config.loginUrl,
        registerUrl: this.config.registerUrl,
      });
      this.favorites = new FavoritesManager({
        apiEndpoint: this.config.apiBaseUrl,
        nonce: this.config.nonce,
        isLoggedIn: this.config.isLoggedIn,
      });
      this.elements = {};

      this.init();
    }

    /**
     * Initialize the chat interface
     */
    async init() {
      this.log('Initializing Le Hiboo Chat Interface...');

      // Build HTML structure
      this.buildHTML();

      // Cache DOM elements
      this.cacheElements();

      // Attach event listeners
      this.attachEventListeners();

      // Load conversation history (avec persistance)
      await this.loadConversationHistory();

      // Migrer localStorage → DB si utilisateur connecté
      if (this.config.isLoggedIn) {
        await this.persistence.migrateLocalStorageToDB();
      }

      // Démarrer l'auto-save
      if (this.config.persistence.enabled) {
        this.persistence.startAutoSave(this);
      }

      // Send greeting message (si pas de messages chargés)
      if (this.state.messages.length === 0) {
        this.sendGreeting();
      }

      this.log('Chat interface initialized successfully');
    }

    /**
     * Build the HTML structure
     */
    buildHTML() {
      // Create backdrop pour effet immersif
      const backdrop = document.createElement('div');
      backdrop.className = 'lehiboo-chat-backdrop';
      backdrop.setAttribute('aria-hidden', 'true');
      document.body.appendChild(backdrop);

      // Create FAB button avec image Le Hiboo
      const fab = document.createElement('button');
      fab.className = 'lehiboo-chat-fab';
      fab.setAttribute('aria-label', 'Ouvrir l\'assistant Le Hiboo');
      fab.innerHTML = '<img src="/wp-content/plugins/eventlist/assets/img/unknow_user.png" alt="Le Hiboo" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">';
      document.body.appendChild(fab);

      // Create chat container
      const container = document.createElement('div');
      container.className = 'lehiboo-chat-container closed';
      container.setAttribute('role', 'dialog');
      container.setAttribute('aria-label', 'Assistant conversationnel Le Hiboo');

      container.innerHTML = `
        <div class="lehiboo-chat-header">
          <div class="lehiboo-chat-header-content">
            <div class="lehiboo-chat-avatar">
              <img src="/wp-content/plugins/eventlist/assets/img/unknow_user.png" alt="Le Hiboo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            </div>
            <div class="lehiboo-chat-header-text">
              <h2 class="lehiboo-chat-title">
                Le Hiboo Assistant
                ${this.config.isLoggedIn ? '<span class="lehiboo-member-badge">Membre</span>' : ''}
              </h2>
              <p class="lehiboo-chat-subtitle">
                <span class="lehiboo-status-indicator"></span>
                ${this.config.isLoggedIn ? `Bonjour ${this.config.userDisplayName} 👋` : 'En ligne - Prêt à vous aider'}
              </p>
            </div>
          </div>
          <div class="lehiboo-chat-header-actions">
            <button class="lehiboo-icon-button" aria-label="Réduire" data-action="minimize">
              <span aria-hidden="true">−</span>
            </button>
            <button class="lehiboo-icon-button" aria-label="Fermer" data-action="close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
        </div>

        <div class="lehiboo-chat-messages" role="log" aria-live="polite" aria-atomic="false"></div>

        <div class="lehiboo-quick-chips" style="display: none;"></div>

        <div class="lehiboo-chat-input-container">
          <div class="lehiboo-chat-input-wrapper">
            <textarea
              class="lehiboo-chat-textarea"
              placeholder="Écrivez votre message..."
              aria-label="Message"
              rows="1"
              maxlength="${InputValidator.MAX_LENGTH}"
            ></textarea>
            <div class="lehiboo-char-counter">
              <span class="current">0</span> / ${InputValidator.MAX_LENGTH}
            </div>
          </div>
          <button class="lehiboo-send-button" aria-label="Envoyer le message" disabled>
            <span aria-hidden="true">➤</span>
          </button>
        </div>
      `;

      document.body.appendChild(container);
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
      this.elements = {
        backdrop: document.querySelector('.lehiboo-chat-backdrop'),
        fab: document.querySelector('.lehiboo-chat-fab'),
        container: document.querySelector('.lehiboo-chat-container'),
        header: document.querySelector('.lehiboo-chat-header'),
        messages: document.querySelector('.lehiboo-chat-messages'),
        quickChips: document.querySelector('.lehiboo-quick-chips'),
        textarea: document.querySelector('.lehiboo-chat-textarea'),
        charCounter: document.querySelector('.lehiboo-char-counter'),
        sendButton: document.querySelector('.lehiboo-send-button'),
        closeButton: document.querySelector('[data-action="close"]'),
        minimizeButton: document.querySelector('[data-action="minimize"]')
      };
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
      // FAB click
      this.elements.fab.addEventListener('click', () => this.toggleChat());

      // Backdrop click pour fermer
      this.elements.backdrop.addEventListener('click', () => this.closeChat());

      // Close/Minimize buttons
      this.elements.closeButton.addEventListener('click', () => this.closeChat());
      this.elements.minimizeButton.addEventListener('click', () => this.closeChat());

      // Textarea input
      this.elements.textarea.addEventListener('input', (e) => this.handleTextareaInput(e));
      this.elements.textarea.addEventListener('keydown', (e) => this.handleTextareaKeydown(e));

      // Send button
      this.elements.sendButton.addEventListener('click', () => this.sendMessage());

      // ESC key to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.state.isOpen) {
          this.closeChat();
        }
      });

      // Auto-resize textarea
      this.elements.textarea.addEventListener('input', () => {
        this.autoResizeTextarea();
      });
    }

    /**
     * Toggle chat open/closed
     */
    toggleChat() {
      if (this.state.isOpen) {
        this.closeChat();
      } else {
        this.openChat();
      }
    }

    /**
     * Open chat
     */
    openChat() {
      this.state.isOpen = true;
      this.elements.container.classList.remove('closed');
      this.elements.backdrop.classList.add('active');
      this.elements.fab.classList.add('hidden');
      this.elements.textarea.focus();
      this.scrollToBottom();

      // Analytics
      this.trackEvent('chat_opened');
    }

    /**
     * Close chat
     */
    closeChat() {
      this.state.isOpen = false;
      this.elements.container.classList.add('closed');
      this.elements.backdrop.classList.remove('active');
      this.elements.fab.classList.remove('hidden');

      // Analytics
      this.trackEvent('chat_closed');
    }

    /**
     * Handle textarea input
     */
    handleTextareaInput(e) {
      const value = e.target.value;
      const length = value.length;

      // Update character counter
      const currentSpan = this.elements.charCounter.querySelector('.current');
      currentSpan.textContent = length;

      // Update counter color
      this.elements.charCounter.classList.remove('warning', 'error');
      if (length > InputValidator.MAX_LENGTH * 0.9) {
        this.elements.charCounter.classList.add('error');
      } else if (length > InputValidator.MAX_LENGTH * 0.7) {
        this.elements.charCounter.classList.add('warning');
      }

      // Enable/disable send button
      this.elements.sendButton.disabled = length === 0;
    }

    /**
     * Handle textarea keydown (Enter to send)
     */
    handleTextareaKeydown(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!this.elements.sendButton.disabled) {
          this.sendMessage();
        }
      }
    }

    /**
     * Auto-resize textarea
     */
    autoResizeTextarea() {
      const textarea = this.elements.textarea;
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    /**
     * Send message
     */
    async sendMessage() {
      const message = this.elements.textarea.value.trim();

      // Validate input
      const validation = InputValidator.validate(message);
      if (!validation.valid) {
        this.showError(validation.error);
        return;
      }

      // Check rate limit
      const rateLimitCheck = this.rateLimiter.canSendMessage();
      if (!rateLimitCheck.allowed) {
        this.showRateLimitWarning(rateLimitCheck.waitTime);
        return;
      }

      // Sanitize input
      const sanitizedMessage = InputValidator.sanitize(message);

      // Add user message to UI
      this.addMessage({
        role: 'user',
        content: sanitizedMessage,
        timestamp: new Date()
      });

      // Clear textarea
      this.elements.textarea.value = '';
      this.elements.sendButton.disabled = true;
      this.autoResizeTextarea();
      this.handleTextareaInput({ target: this.elements.textarea });

      // Show typing indicator
      this.showTypingIndicator();

      // Send to API
      try {
        const response = await this.sendToAPI(sanitizedMessage);
        this.hideTypingIndicator();

        if (response.success) {
          // Add assistant message
          this.addMessage({
            role: 'assistant',
            content: response.message,
            timestamp: new Date(),
            quickChips: response.quickChips || [],
            events: response.events || []
          });

          // Update conversation state
          if (response.conversationStage) {
            this.state.currentStage = response.conversationStage;
          }

          if (response.userContext) {
            this.state.userContext = { ...this.state.userContext, ...response.userContext };
          }

          // Show weather alert if provided
          if (response.weatherAlert) {
            this.showWeatherAlert(response.weatherAlert);
          }

          // Incrémenter le compteur de messages
          this.state.messageCount++;

          // Save conversation (auto-save gère ça, mais on force ici pour les moments clés)
          await this.saveConversation();

          // Trigger onboarding si nécessaire
          if (this.config.onboarding.enabled) {
            this.onboarding.showAfterMessages(this.state.messageCount, this);
          }

        } else {
          this.showError(response.message || 'Une erreur est survenue');
        }
      } catch (error) {
        this.hideTypingIndicator();
        this.log('Error sending message:', error);
        this.showError('Impossible de se connecter au serveur. Veuillez réessayer.');
      }
    }

    /**
     * Send message to API
     */
    async sendToAPI(message) {
      // Construire l'historique au format attendu par le backend
      const history = this.state.messages
        .filter(msg => msg.role) // Garder seulement user/assistant
        .map(msg => ({
          role: msg.role,
          content: msg.content
        }));

      const response = await fetch(this.config.apiEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': this.config.nonce
        },
        body: JSON.stringify({
          message: message,
          conversationId: this.state.conversationId,
          userContext: this.state.userContext,
          currentStage: this.state.currentStage,
          history: history // ✅ Ajouter l'historique !
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    }

    /**
     * Add message to chat
     */
    addMessage(message) {
      this.state.messages.push(message);

      const messageEl = this.createMessageElement(message);
      this.elements.messages.appendChild(messageEl);

      // Show quick chips if present
      if (message.quickChips && message.quickChips.length > 0) {
        this.showQuickChips(message.quickChips);
      }

      this.scrollToBottom();
    }

    /**
     * Create message element
     */
    createMessageElement(message) {
      const div = document.createElement('div');
      div.className = `lehiboo-message ${message.role}`;

      const avatar = document.createElement('div');
      avatar.className = 'lehiboo-message-avatar';
      if (message.role === 'user') {
        avatar.innerHTML = '👤';
      } else {
        avatar.innerHTML = '<img src="/wp-content/plugins/eventlist/assets/img/unknow_user.png" alt="Le Hiboo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">';
      }
      avatar.setAttribute('aria-hidden', 'true');

      const content = document.createElement('div');
      content.className = 'lehiboo-message-content';

      const bubble = document.createElement('div');
      bubble.className = 'lehiboo-message-bubble';
      bubble.innerHTML = this.formatMessageContent(message.content);

      const meta = document.createElement('div');
      meta.className = 'lehiboo-message-meta';
      meta.innerHTML = `
        <span class="lehiboo-message-time">${this.formatTime(message.timestamp)}</span>
      `;

      content.appendChild(bubble);
      content.appendChild(meta);

      div.appendChild(avatar);
      div.appendChild(content);

      // Add event cards if present
      if (message.events && message.events.length > 0) {
        message.events.forEach(event => {
          const eventCard = this.createEventCard(event);
          content.appendChild(eventCard);
        });
      }

      return div;
    }

    /**
     * Create event card
     */
    createEventCard(event) {
      const card = document.createElement('div');
      card.className = 'lehiboo-event-card';
      const isFavorite = this.favorites.isFavorite(event.id);

      card.innerHTML = `
        <button class="lehiboo-event-card-favorite ${isFavorite ? 'active' : ''}"
                data-event-id="${event.id}"
                aria-label="${isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}">
        </button>
        ${event.image ? `<img src="${event.image}" alt="${event.title}" class="lehiboo-event-card-image">` : ''}
        <div class="lehiboo-event-card-content">
          <div class="lehiboo-event-card-header">
            <h3 class="lehiboo-event-card-title">${event.title}</h3>
            <div class="lehiboo-event-card-price">${event.price}</div>
          </div>

          <div class="lehiboo-event-card-meta">
            <div class="lehiboo-event-card-meta-item">
              <span class="lehiboo-event-card-meta-icon">📅</span>
              <span>${event.date}</span>
            </div>
            <div class="lehiboo-event-card-meta-item">
              <span class="lehiboo-event-card-meta-icon">📍</span>
              <span>${event.location}</span>
            </div>
            <div class="lehiboo-event-card-meta-item">
              <span class="lehiboo-event-card-meta-icon">⏱️</span>
              <span>${event.duration}</span>
            </div>
            ${event.rating ? `
              <div class="lehiboo-event-card-meta-item">
                <span class="lehiboo-event-card-meta-icon">⭐</span>
                <span>${event.rating} (${event.reviews} avis)</span>
              </div>
            ` : ''}
          </div>

          ${event.badges && event.badges.length > 0 ? `
            <div class="lehiboo-event-card-badges">
              ${event.badges.map(badge => `
                <span class="lehiboo-badge ${badge.type}">${badge.icon} ${badge.text}</span>
              `).join('')}
            </div>
          ` : ''}

          <div class="lehiboo-event-card-actions">
            <button class="lehiboo-button lehiboo-button-secondary" onclick="lehibooChat.viewEventDetails('${event.id}')">
              Voir détails
            </button>
            <button class="lehiboo-button lehiboo-button-primary" onclick="lehibooChat.bookEvent('${event.id}')">
              Réserver
            </button>
          </div>
        </div>
      `;

      // Event listener pour le bouton favoris
      const favoriteBtn = card.querySelector('.lehiboo-event-card-favorite');
      if (favoriteBtn) {
        favoriteBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          await this.toggleFavorite(event.id, favoriteBtn);
        });
      }

      return card;
    }

    /**
     * Toggle favorite
     */
    async toggleFavorite(eventId, buttonElement) {
      // Si pas connecté, afficher modal onboarding
      if (!this.config.isLoggedIn) {
        this.onboarding.show(this);
        return;
      }

      // Toggle
      const isNowFavorite = await this.favorites.toggleFavorite(eventId, this.state.conversationId);

      // Update UI
      if (isNowFavorite) {
        buttonElement.classList.add('active', 'animating');
        buttonElement.setAttribute('aria-label', 'Retirer des favoris');
        this.showToast(this.config.i18n.addedToFavorites || 'Ajouté aux favoris ❤️');
      } else {
        buttonElement.classList.remove('active');
        buttonElement.setAttribute('aria-label', 'Ajouter aux favoris');
        this.showToast(this.config.i18n.removedFromFavorites || 'Retiré des favoris');
      }

      // Remove animation class after animation
      setTimeout(() => {
        buttonElement.classList.remove('animating');
      }, 600);

      // Track analytics
      this.trackEvent(isNowFavorite ? 'favorite_added' : 'favorite_removed', { eventId });
    }

    /**
     * Show quick chips
     */
    showQuickChips(chips) {
      this.elements.quickChips.innerHTML = '';

      chips.forEach(chip => {
        const button = document.createElement('button');
        button.className = 'lehiboo-chip';
        button.textContent = chip.text;
        button.setAttribute('aria-label', `Réponse rapide : ${chip.text}`);

        button.addEventListener('click', () => {
          this.elements.textarea.value = chip.value || chip.text;
          this.handleTextareaInput({ target: this.elements.textarea });
          this.sendMessage();
          this.hideQuickChips();
        });

        this.elements.quickChips.appendChild(button);
      });

      this.elements.quickChips.style.display = 'flex';
    }

    /**
     * Hide quick chips
     */
    hideQuickChips() {
      this.elements.quickChips.style.display = 'none';
    }

    /**
     * Show typing indicator
     */
    showTypingIndicator() {
      this.state.isTyping = true;

      const indicator = document.createElement('div');
      indicator.className = 'lehiboo-typing-indicator';
      indicator.innerHTML = `
        <div class="lehiboo-message-avatar">
          <img src="/wp-content/plugins/eventlist/assets/img/unknow_user.png" alt="Le Hiboo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
        </div>
        <div class="lehiboo-typing-bubble">
          <span class="lehiboo-typing-dot"></span>
          <span class="lehiboo-typing-dot"></span>
          <span class="lehiboo-typing-dot"></span>
        </div>
      `;
      indicator.id = 'lehiboo-typing';

      this.elements.messages.appendChild(indicator);
      this.scrollToBottom();
    }

    /**
     * Hide typing indicator
     */
    hideTypingIndicator() {
      this.state.isTyping = false;
      const indicator = document.getElementById('lehiboo-typing');
      if (indicator) {
        indicator.remove();
      }
    }

    /**
     * Show weather alert
     */
    showWeatherAlert(alert) {
      // Remove existing alert
      const existing = document.querySelector('.lehiboo-weather-alert');
      if (existing) existing.remove();

      const alertEl = document.createElement('div');
      alertEl.className = 'lehiboo-weather-alert';
      alertEl.setAttribute('role', 'alert');
      alertEl.innerHTML = `
        <span class="lehiboo-weather-alert-icon">${alert.icon || '🌤️'}</span>
        <span class="lehiboo-weather-alert-text">${alert.message}</span>
        <button class="lehiboo-weather-alert-close" aria-label="Fermer l'alerte">×</button>
      `;

      alertEl.querySelector('.lehiboo-weather-alert-close').addEventListener('click', () => {
        alertEl.remove();
      });

      this.elements.header.after(alertEl);

      // Auto-dismiss after 10 seconds
      setTimeout(() => {
        if (alertEl.parentNode) {
          alertEl.remove();
        }
      }, 10000);
    }

    /**
     * Show error message
     */
    showError(message) {
      const errorEl = document.createElement('div');
      errorEl.className = 'lehiboo-error-message';
      errorEl.setAttribute('role', 'alert');
      errorEl.innerHTML = `
        <span aria-hidden="true">⚠️</span>
        <span>${message}</span>
      `;

      this.elements.messages.appendChild(errorEl);
      this.scrollToBottom();

      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (errorEl.parentNode) {
          errorEl.remove();
        }
      }, 5000);
    }

    /**
     * Show rate limit warning
     */
    showRateLimitWarning(waitTime) {
      const warningEl = document.createElement('div');
      warningEl.className = 'lehiboo-rate-limit-warning';
      warningEl.setAttribute('role', 'alert');
      warningEl.innerHTML = `
        <span aria-hidden="true">⏱️</span>
        <span>Trop de messages envoyés. Veuillez attendre ${waitTime} secondes.</span>
      `;

      this.elements.messages.appendChild(warningEl);
      this.scrollToBottom();

      setTimeout(() => {
        if (warningEl.parentNode) {
          warningEl.remove();
        }
      }, 3000);
    }

    /**
     * Send greeting message
     */
    sendGreeting() {
      setTimeout(() => {
        this.addMessage({
          role: 'assistant',
          content: `Bonjour ! Je suis l'assistant Le Hiboo 👋<br><br>
                    Je vais vous aider à trouver l'activité parfaite.<br><br>
                    Pour commencer, vous cherchez une activité pour :`,
          timestamp: new Date(),
          quickChips: [
            { text: '🧍 Solo', value: 'solo' },
            { text: '💑 En couple', value: 'couple' },
            { text: '👨‍👩‍👧 En famille', value: 'famille' },
            { text: '👥 Entre amis', value: 'amis' }
          ]
        });
      }, 500);
    }

    /**
     * View event details
     */
    viewEventDetails(eventId) {
      this.log('View event details:', eventId);
      // Open event page in new tab
      window.open(`/event/${eventId}`, '_blank');
      this.trackEvent('view_event_details', { eventId });
    }

    /**
     * Book event
     */
    bookEvent(eventId) {
      this.log('Book event:', eventId);
      // Redirect to booking page
      window.location.href = `/event/${eventId}?action=book`;
      this.trackEvent('book_event', { eventId });
    }

    /**
     * Format message content (markdown-lite)
     */
    formatMessageContent(content) {
      // Simple formatting
      return content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>');
    }

    /**
     * Format time
     */
    formatTime(date) {
      if (!(date instanceof Date)) {
        date = new Date(date);
      }
      return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    /**
     * Scroll to bottom
     */
    scrollToBottom() {
      requestAnimationFrame(() => {
        this.elements.messages.scrollTop = this.elements.messages.scrollHeight;
      });
    }

    /**
     * Generate conversation ID
     */
    generateConversationId() {
      return 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Save conversation (utilise le système de persistance hybride)
     */
    async saveConversation() {
      try {
        const saved = await this.persistence.saveConversation(this.state);
        if (saved && this.config.debug) {
          this.log('Conversation saved successfully');
        }
      } catch (e) {
        this.log('Failed to save conversation:', e);
      }
    }

    /**
     * Load conversation history (utilise le système de persistance hybride)
     */
    async loadConversationHistory() {
      try {
        const data = await this.persistence.loadConversation(this.state.conversationId);

        if (!data) {
          this.log('No conversation history found');
          return;
        }

        // Restore state
        this.state.conversationId = data.conversationId;
        this.state.userContext = data.userContext || {};
        this.state.currentStage = data.currentStage || 'greeting';
        this.state.messageCount = (data.messages || []).length;

        // Restore messages to UI
        if (data.messages && data.messages.length > 0) {
          data.messages.forEach(msg => {
            // Ne pas afficher le greeting initial si déjà des messages
            if (msg.role === 'assistant' && msg.content.includes('Pour commencer')) {
              return;
            }

            const messageEl = this.createMessageElement({
              ...msg,
              timestamp: new Date(msg.timestamp || Date.now())
            });
            this.elements.messages.appendChild(messageEl);
          });

          this.scrollToBottom();
        }

        this.log('Conversation history loaded', {
          messageCount: this.state.messageCount,
          stage: this.state.currentStage
        });
      } catch (e) {
        this.log('Failed to load conversation:', e);
      }
    }

    /**
     * Show toast notification
     */
    showToast(message, duration = 3000) {
      // Créer ou récupérer le container de toasts
      let toastContainer = document.querySelector('.lehiboo-toast-container');
      if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'lehiboo-toast-container';
        document.body.appendChild(toastContainer);
      }

      // Créer le toast
      const toast = document.createElement('div');
      toast.className = 'lehiboo-toast';
      toast.textContent = message;
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');

      toastContainer.appendChild(toast);

      // Afficher avec animation
      requestAnimationFrame(() => {
        toast.classList.add('show');
      });

      // Masquer et supprimer
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
          toast.remove();
          // Supprimer le container si vide
          if (toastContainer.children.length === 0) {
            toastContainer.remove();
          }
        }, 300);
      }, duration);
    }

    /**
     * Track analytics event
     */
    trackEvent(eventName, data = {}) {
      // TODO: Implement analytics tracking
      this.log('Track event:', eventName, data);

      // Example: Google Analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', eventName, {
          event_category: 'LeHiboo_Chat',
          ...data
        });
      }
    }

    /**
     * Debug logging
     */
    log(...args) {
      if (this.config.debug) {
        console.log('[LeHiboo Chat]', ...args);
      }
    }
  }

  /**
   * Initialize when DOM is ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChat);
  } else {
    initChat();
  }

  function initChat() {
    // Get config from WordPress localized script
    const config = window.lehibooChatConfig || {};

    // Create global instance
    window.lehibooChat = new LehibooChatInterface(config);
  }

})();
