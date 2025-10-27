/**
 * Service d'intégration WordPress / EventList
 * Communication avec l'API WordPress pour récupérer les événements
 */

import config from '../config/index.js';
import logger from '../utils/logger.js';

/**
 * Classe pour gérer les requêtes WordPress
 */
class WordPressService {
  constructor() {
    this.baseUrl = config.wordpress.apiUrl;
    this.apiKey = config.wordpress.apiKey;
  }

  /**
   * Effectuer une requête vers l'API WordPress
   */
  async request(endpoint, options = {}) {
    try {
      const url = `${this.baseUrl}${endpoint}`;

      const headers = {
        'Content-Type': 'application/json',
        ...options.headers,
      };

      // Ajouter authentification si disponible
      if (this.apiKey) {
        headers['Authorization'] = `Bearer ${this.apiKey}`;
      }

      logger.debug('WordPress API request', { url, method: options.method || 'GET' });

      const response = await fetch(url, {
        method: options.method || 'GET',
        headers,
        body: options.body ? JSON.stringify(options.body) : undefined,
      });

      if (!response.ok) {
        throw new Error(`WordPress API error: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      logger.error('WordPress API request failed', {
        endpoint,
        error: error.message,
      });
      throw error;
    }
  }

  /**
   * Rechercher des événements
   *
   * @param {Object} filters - Filtres de recherche
   * @param {string} filters.type - Type d'événement (sport, culture, etc.)
   * @param {string} filters.startDate - Date de début (YYYY-MM-DD)
   * @param {string} filters.endDate - Date de fin (YYYY-MM-DD)
   * @param {number} filters.minAge - Age minimum
   * @param {number} filters.maxAge - Age maximum
   * @param {string} filters.location - Localisation
   * @param {number} filters.limit - Nombre max de résultats
   */
  async searchEvents(filters = {}) {
    try {
      logger.info('Searching events', { filters });

      // Construire les paramètres de requête
      const params = new URLSearchParams();

      if (filters.type) params.append('category', filters.type);
      if (filters.startDate) params.append('start_date', filters.startDate);
      if (filters.endDate) params.append('end_date', filters.endDate);
      if (filters.location) params.append('location', filters.location);
      if (filters.limit) params.append('per_page', filters.limit);

      // EventList utilise probablement un custom post type
      // Adapter selon votre setup WordPress
      const endpoint = `/eventlist/v1/events?${params.toString()}`;

      const events = await this.request(endpoint);

      // Filtrer par âge si spécifié
      let filteredEvents = events;
      if (filters.minAge || filters.maxAge) {
        filteredEvents = this.filterEventsByAge(events, filters.minAge, filters.maxAge);
      }

      logger.info('Events found', { count: filteredEvents.length });
      return filteredEvents;
    } catch (error) {
      logger.error('Error searching events', { error: error.message });
      return [];
    }
  }

  /**
   * Obtenir les détails d'un événement
   */
  async getEventDetails(eventId) {
    try {
      logger.info('Fetching event details', { eventId });

      const endpoint = `/eventlist/v1/events/${eventId}`;
      const event = await this.request(endpoint);

      return this.formatEventForAI(event);
    } catch (error) {
      logger.error('Error fetching event details', {
        eventId,
        error: error.message,
      });
      return null;
    }
  }

  /**
   * Filtrer les événements par restrictions d'âge
   */
  filterEventsByAge(events, minAge, maxAge) {
    return events.filter((event) => {
      // Si l'événement a une restriction 18+
      if (event.age_restriction === '18+') {
        return minAge >= 18;
      }

      // Si l'événement a un âge minimum
      if (event.min_age && minAge < event.min_age) {
        return false;
      }

      // Si l'événement a un âge maximum
      if (event.max_age && maxAge > event.max_age) {
        return false;
      }

      return true;
    });
  }

  /**
   * Formater un événement pour l'IA
   * Structure attendue par le frontend
   */
  formatEventForAI(event) {
    return {
      id: event.id.toString(),
      title: event.title?.rendered || event.title || 'Sans titre',
      description: this.stripHtml(event.content?.rendered || event.description || ''),
      image: event.featured_image_url || event.image || '',
      price: this.formatPrice(event.price),
      date: this.formatDate(event.start_date, event.start_time),
      location: this.formatLocation(event.location, event.venue),
      duration: this.calculateDuration(event.start_time, event.end_time),
      rating: event.rating || null,
      reviews: event.reviews_count || null,
      badges: this.generateBadges(event),
      url: event.link || `${config.wordpress.url}/event/${event.id}`,
      availability: event.availability || 'available',
      ageRestriction: event.age_restriction || null,
      category: event.category || event.categories?.[0]?.name || null,
    };
  }

  /**
   * Formater le prix
   */
  formatPrice(price) {
    if (!price || price === 0 || price === '0') {
      return 'Gratuit';
    }

    if (typeof price === 'string') {
      return price;
    }

    return `${price}€/pers`;
  }

  /**
   * Formater la date
   */
  formatDate(date, time) {
    try {
      const dateObj = new Date(date);
      const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
      const dayName = days[dateObj.getDay()];

      if (time) {
        return `${dayName} ${time}`;
      }

      return dateObj.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
      });
    } catch (error) {
      return date;
    }
  }

  /**
   * Formater la localisation
   */
  formatLocation(location, venue) {
    if (venue && location) {
      return `${venue}, ${location}`;
    }
    return venue || location || 'Lieu à confirmer';
  }

  /**
   * Calculer la durée
   */
  calculateDuration(startTime, endTime) {
    if (!startTime || !endTime) {
      return null;
    }

    try {
      const start = new Date(`2000-01-01 ${startTime}`);
      const end = new Date(`2000-01-01 ${endTime}`);
      const diffMs = end - start;
      const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
      const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

      if (diffHours > 0 && diffMinutes > 0) {
        return `${diffHours}h${diffMinutes}`;
      } else if (diffHours > 0) {
        return `${diffHours}h`;
      } else {
        return `${diffMinutes}min`;
      }
    } catch (error) {
      return null;
    }
  }

  /**
   * Générer les badges
   */
  generateBadges(event) {
    const badges = [];

    // Badge indoor/outdoor
    if (event.indoor) {
      badges.push({ type: 'indoor', icon: '🏠', text: 'Indoor' });
    } else if (event.outdoor) {
      badges.push({ type: 'outdoor', icon: '🌳', text: 'Outdoor' });
    }

    // Badge âge
    if (event.age_restriction === '18+') {
      badges.push({ type: 'age', icon: '🔞', text: '18+' });
    } else if (event.family_friendly) {
      badges.push({ type: 'family', icon: '👨‍👩‍👧', text: 'Famille' });
    }

    // Badge type activité
    const categoryIcons = {
      sport: { icon: '⚽', text: 'Sport' },
      culture: { icon: '🎨', text: 'Culture' },
      gastronomie: { icon: '🍽️', text: 'Gastronomie' },
      nature: { icon: '🌳', text: 'Nature' },
      detente: { icon: '🧘', text: 'Détente' },
    };

    if (event.category && categoryIcons[event.category.toLowerCase()]) {
      const cat = categoryIcons[event.category.toLowerCase()];
      badges.push({ type: event.category.toLowerCase(), icon: cat.icon, text: cat.text });
    }

    // Badge popularité
    if (event.popular || (event.reviews_count && event.reviews_count > 100)) {
      badges.push({ type: 'popular', icon: '🔥', text: 'Populaire' });
    }

    // Badge nouveau
    if (event.is_new) {
      badges.push({ type: 'new', icon: '✨', text: 'Nouveau' });
    }

    return badges;
  }

  /**
   * Enlever le HTML d'une chaîne
   */
  stripHtml(html) {
    return html.replace(/<[^>]*>/g, '').trim();
  }

  /**
   * Tester la connexion WordPress
   */
  async testConnection() {
    try {
      await this.request('/');
      logger.info('WordPress connection test successful');
      return true;
    } catch (error) {
      logger.error('WordPress connection test failed', {
        error: error.message,
      });
      return false;
    }
  }
}

// Exporter une instance singleton
export default new WordPressService();
