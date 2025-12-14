/**
 * Controller Mobile - API simplifiée pour Flutter
 */

import { generateMobileResponse } from '../services/ai-service-mobile.js';
import weatherService from '../services/weather-service.js';
import chatStorage from '../services/chat-storage.js';
import logger from '../utils/logger.js';
import crypto from 'crypto';

/**
 * POST /mobile/chat
 * Endpoint principal de chat pour l'app mobile
 * Sauvegarde automatiquement les messages si userId est fourni
 */
export async function handleMobileChat(req, res) {
  try {
    const {
      message,
      conversationId = crypto.randomUUID(),
      userContext: userContextCamel = {},
      user_context: userContextSnake = {},
      history = [],
      userId = null  // ID utilisateur WordPress pour persistence
    } = req.body;

    // Support both camelCase and snake_case from frontend
    const userContext = Object.keys(userContextCamel).length > 0 ? userContextCamel : userContextSnake;

    logger.info('Mobile chat request', {
      conversationId,
      userId,
      messagePreview: message?.substring(0, 50)
    });

    // Générer la réponse (avec extraction d'infos utilisateur)
    const response = await generateMobileResponse(message, {
      conversationId,
      userContext,
      history
    });

    // Récupérer le contexte utilisateur mis à jour par l'IA
    const updatedUserContext = response.userContext || userContext;

    // Sauvegarder les messages si userId est fourni
    if (userId) {
      // Sauvegarder le message utilisateur
      await chatStorage.saveMessage(userId, conversationId, 'user', message);

      // Préparer les événements avec détails complets pour l'historique
      const eventsForHistory = response.events?.map(e => ({
        id: e.id,
        title: e.title,
        image: e.image || e.imageUrl || e.thumbnail,
        price: e.price,
        priceLabel: e.priceDisplay || (e.price === 0 ? 'Gratuit' : `${e.price}€`),
        location: typeof e.location === 'object' ? e.location : { city: e.location || e.venue },
        date: e.dates?.display || e.dateLabel || e.date,
        category: e.category,
        url: e.url
      })) || [];

      // Sauvegarder la réponse assistant avec les métadonnées complètes
      const assistantMessage = await chatStorage.saveMessage(userId, conversationId, 'assistant', response.message, {
        events: eventsForHistory,
        hasSearchResults: eventsForHistory.length > 0,
        searchParams: response.searchParams || null
      });

      // Sauvegarder les impressions d'événements pour analytics (PostgreSQL only)
      if (assistantMessage && response.events?.length > 0) {
        await chatStorage.saveEventImpressions(userId, conversationId, assistantMessage.id, response.events);
      }
    }

    // Mettre à jour l'historique (pour le contexte de la conversation en cours)
    const updatedHistory = [
      ...history.slice(-18), // Garder les 18 derniers + 2 nouveaux = 20 max
      { role: 'user', content: message, timestamp: new Date().toISOString() },
      { role: 'assistant', content: response.message, timestamp: new Date().toISOString() }
    ];

    return res.json({
      success: true,
      conversationId,
      message: response.message,
      events: response.events || [],
      searchParams: response.searchParams || null,
      searchFilters: response.searchFilters,
      history: updatedHistory,
      user_context: updatedUserContext,
      usage: response.usage
    });

  } catch (error) {
    logger.error('Mobile chat error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: error.message || 'Une erreur est survenue'
    });
  }
}

/**
 * POST /mobile/search
 * Recherche directe sans conversation (pour filtres)
 */
export async function handleMobileSearch(req, res) {
  try {
    const {
      groupType,
      activityType,
      city,
      radius,
      dates,
      budgetMax,
      freeOnly,
      tags,
      limit = 10
    } = req.body;

    logger.info('Mobile search request', { city, activityType, dates, tags });

    // Import du nouveau tool de recherche
    const { searchEventsV2 } = await import('../tools/search-events-v2.js');

    // Appel direct avec les params (le tool gere les defaults)
    const result = await searchEventsV2({
      city,
      radius,
      groupType,
      activityType,
      dates,
      budgetMax,
      freeOnly,
      tags,
      limit
    });

    if (!result.success) {
      return res.status(400).json({
        success: false,
        error: result.error,
        message: result.message
      });
    }

    return res.json({
      success: true,
      events: result.events.map(e => ({
        id: e.id,
        title: e.title,
        description: (e.excerpt || e.description)?.substring(0, 150) || '',
        image: e.imageUrl || e.thumbnailUrl || e.image || e.thumbnail,
        thumbnail: e.thumbnailUrl || e.imageUrl || e.thumbnail || e.image,
        price: e.price,
        priceLabel: e.priceDisplay || (e.price === 0 ? 'Gratuit' : `${e.price}€`),
        location: typeof e.location === 'object' ? e.location : { city: e.location || e.venue },
        date: e.dates?.display || e.dateLabel || e.date,
        startDate: e.dates?.start || null,
        endDate: e.dates?.end || null,
        category: e.category,
        thematiques: e.thematiques || [],
        matchScore: e.matchScore,
        url: e.url
      })),
      total: result.totalFound,
      searchParams: result.searchParams,  // Params bruts pour mapping front
      filtersUsed: result.filtersUsed     // Info lisible
    });

  } catch (error) {
    logger.error('Mobile search error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur lors de la recherche'
    });
  }
}

/**
 * GET /mobile/categories
 * Liste des catégories disponibles
 */
export async function handleMobileCategories(req, res) {
  return res.json({
    success: true,
    categories: [
      { id: 'sport', label: 'Sport', icon: 'sports_soccer' },
      { id: 'culture', label: 'Culture', icon: 'theater_comedy' },
      { id: 'gastronomie', label: 'Gastronomie', icon: 'restaurant' },
      { id: 'nature', label: 'Nature', icon: 'park' },
      { id: 'detente', label: 'Détente', icon: 'spa' }
    ]
  });
}

/**
 * GET /mobile/cities
 * Villes suggérées
 */
export async function handleMobileCities(req, res) {
  return res.json({
    success: true,
    cities: [
      { name: 'Valenciennes', region: 'Hauts-de-France' },
      { name: 'Lille', region: 'Hauts-de-France' },
      { name: 'Douai', region: 'Hauts-de-France' },
      { name: 'Cambrai', region: 'Hauts-de-France' },
      { name: 'Maubeuge', region: 'Hauts-de-France' },
      { name: 'Denain', region: 'Hauts-de-France' }
    ]
  });
}

/**
 * GET /mobile/weather
 * Météo actuelle pour une ville
 */
export async function handleMobileWeather(req, res) {
  try {
    const { city = 'Valenciennes' } = req.query;

    logger.info('Mobile weather request', { city });

    const weather = await weatherService.getCurrentWeather(city);

    if (!weather) {
      return res.status(404).json({
        success: false,
        error: 'Impossible de récupérer la météo'
      });
    }

    // Analyser la météo pour des recommandations
    const analysis = weatherService.analyzeWeather(weather);

    return res.json({
      success: true,
      city: weather.location,
      current: {
        temperature: weather.temperature,
        description: weather.description,
        icon: weather.icon,
        wind: weather.wind,
        precipitation: weather.precipitation
      },
      alert: analysis.type !== 'perfect' ? {
        type: analysis.type,
        icon: analysis.icon,
        message: analysis.message,
        recommendIndoor: analysis.recommendIndoor
      } : null,
      timestamp: weather.timestamp
    });

  } catch (error) {
    logger.error('Mobile weather error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur météo'
    });
  }
}

/**
 * GET /mobile/weather/forecast
 * Prévisions météo sur plusieurs jours
 */
export async function handleMobileWeatherForecast(req, res) {
  try {
    const { city = 'Valenciennes', days = 7 } = req.query;

    logger.info('Mobile weather forecast request', { city, days });

    const forecast = await weatherService.getForecast(city, parseInt(days));

    if (!forecast) {
      return res.status(404).json({
        success: false,
        error: 'Impossible de récupérer les prévisions'
      });
    }

    // Formater les prévisions pour le mobile
    const dailyForecast = forecast.daily.time.map((date, i) => ({
      date,
      tempMax: forecast.daily.temperature_2m_max[i],
      tempMin: forecast.daily.temperature_2m_min[i],
      precipitation: forecast.daily.precipitation_sum[i],
      description: weatherService.getWeatherDescription(forecast.daily.weathercode[i]),
      icon: weatherService.getWeatherIcon(forecast.daily.weathercode[i])
    }));

    return res.json({
      success: true,
      city: forecast.location,
      forecast: dailyForecast
    });

  } catch (error) {
    logger.error('Mobile weather forecast error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur prévisions météo'
    });
  }
}

/**
 * GET /mobile/chat/history
 * Récupère l'historique de chat d'un utilisateur
 */
export async function handleMobileChatHistory(req, res) {
  try {
    const userId = req.query.userId || req.query.user_id;
    const conversationId = req.query.conversationId || req.query.conversation_id;
    const limit = parseInt(req.query.limit) || 50;

    if (!userId) {
      return res.status(400).json({
        success: false,
        error: 'userId requis'
      });
    }

    logger.info('Chat history request', { userId, conversationId, limit });

    const messages = await chatStorage.getHistory(userId, {
      limit,
      conversationId
    });

    return res.json({
      success: true,
      data: {
        history: messages.map(m => {
          const msg = {
            id: m.id,
            role: m.role,
            content: m.content,
            timestamp: m.timestamp || m.created_at,
            conversationId: m.conversationId || m.conversation_id
          };

          // Inclure les événements pour les messages assistant
          if (m.role === 'assistant') {
            // PostgreSQL: metadata est un objet, JSON: peut être dans les props directes
            const metadata = m.metadata || {};
            if (metadata.events && metadata.events.length > 0) {
              msg.events = metadata.events;
            }
            if (metadata.hasSearchResults !== undefined) {
              msg.hasSearchResults = metadata.hasSearchResults;
            }
          }

          return msg;
        }),
        total: messages.length
      }
    });

  } catch (error) {
    logger.error('Chat history error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur lors de la récupération de l\'historique'
    });
  }
}

/**
 * GET /mobile/chat/conversations
 * Liste les conversations d'un utilisateur
 */
export async function handleMobileChatConversations(req, res) {
  try {
    const userId = req.query.userId || req.query.user_id;

    if (!userId) {
      return res.status(400).json({
        success: false,
        error: 'userId requis'
      });
    }

    logger.info('Chat conversations request', { userId });

    const conversations = await chatStorage.getConversations(userId);

    return res.json({
      success: true,
      data: {
        conversations: conversations.map(c => ({
          id: c.id,
          messageCount: c.messageCount || c.message_count,
          startedAt: c.startedAt || c.started_at,
          lastMessageAt: c.lastMessageAt || c.last_message_at
        }))
      }
    });

  } catch (error) {
    logger.error('Chat conversations error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur lors de la récupération des conversations'
    });
  }
}

/**
 * DELETE /mobile/chat/history
 * Efface l'historique d'un utilisateur
 */
export async function handleMobileChatClear(req, res) {
  try {
    const userId = req.query.userId || req.query.user_id;
    const conversationId = req.query.conversationId || req.query.conversation_id;

    if (!userId) {
      return res.status(400).json({
        success: false,
        error: 'userId requis'
      });
    }

    logger.info('Chat clear request', { userId, conversationId });

    let success;
    if (conversationId) {
      // Supprimer une conversation spécifique
      success = await chatStorage.deleteConversation(userId, conversationId);
    } else {
      // Supprimer tout l'historique
      success = await chatStorage.clearHistory(userId);
    }

    return res.json({
      success,
      message: conversationId
        ? 'Conversation supprimée'
        : 'Historique effacé'
    });

  } catch (error) {
    logger.error('Chat clear error', { error: error.message });

    return res.status(500).json({
      success: false,
      error: 'Erreur lors de la suppression'
    });
  }
}

export default {
  handleMobileChat,
  handleMobileSearch,
  handleMobileCategories,
  handleMobileCities,
  handleMobileWeather,
  handleMobileWeatherForecast,
  handleMobileChatHistory,
  handleMobileChatConversations,
  handleMobileChatClear
};
