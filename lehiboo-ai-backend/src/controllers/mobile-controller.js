/**
 * Controller Mobile - API simplifiée pour Flutter
 */

import { generateMobileResponse } from '../services/ai-service-mobile.js';
import logger from '../utils/logger.js';
import { v4 as uuidv4 } from 'uuid';

/**
 * POST /mobile/chat
 * Endpoint principal de chat pour l'app mobile
 */
export async function handleMobileChat(req, res) {
  try {
    const {
      message,
      conversationId = uuidv4(),
      userContext = {},
      history = []
    } = req.body;

    logger.info('Mobile chat request', {
      conversationId,
      messagePreview: message?.substring(0, 50)
    });

    // Générer la réponse
    const response = await generateMobileResponse(message, {
      conversationId,
      userContext,
      history
    });

    // Mettre à jour l'historique
    const updatedHistory = [
      ...history.slice(-18), // Garder les 18 derniers + 2 nouveaux = 20 max
      { role: 'user', content: message, timestamp: new Date().toISOString() },
      { role: 'assistant', content: response.message, timestamp: new Date().toISOString() }
    ];

    return res.json({
      success: true,
      conversationId,
      message: response.message,
      userContext: response.userContext,
      events: response.events || [],
      history: updatedHistory,
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
      groupType = 'solo',
      activityType,
      city = 'Valenciennes',
      radius = 20,
      dates = 'flexible',
      budgetMax,
      limit = 10
    } = req.body;

    logger.info('Mobile search request', { city, activityType, dates });

    // Import dynamique du tool de recherche
    const { searchEvents } = await import('../tools/search-events.js');

    // Construire le profil utilisateur
    const userProfile = {
      groupType,
      age: 30, // Défaut
      location: { city, radius },
      dates: { type: dates },
      activityType: activityType || 'multi',
      budgetMax: budgetMax || 999
    };

    const result = await searchEvents({
      userProfile,
      limit,
      sortBy: 'relevance'
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
        description: e.description?.substring(0, 150),
        image: e.image || e.thumbnail,
        price: e.price,
        priceLabel: e.price === 0 ? 'Gratuit' : `${e.price}€`,
        location: e.location?.city || e.venue,
        date: e.dateLabel || e.date,
        category: e.category,
        matchScore: e.matchScore,
        url: e.url
      })),
      total: result.totalFound,
      filters: { city, activityType, dates, budgetMax }
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

export default {
  handleMobileChat,
  handleMobileSearch,
  handleMobileCategories,
  handleMobileCities
};
