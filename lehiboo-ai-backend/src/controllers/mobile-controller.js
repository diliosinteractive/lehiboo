/**
 * Controller Mobile - API simplifiée pour Flutter
 */

import { generateMobileResponse } from '../services/ai-service-mobile.js';
import weatherService from '../services/weather-service.js';
import logger from '../utils/logger.js';
import crypto from 'crypto';

/**
 * POST /mobile/chat
 * Endpoint principal de chat pour l'app mobile
 */
export async function handleMobileChat(req, res) {
  try {
    const {
      message,
      conversationId = crypto.randomUUID(),
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

export default {
  handleMobileChat,
  handleMobileSearch,
  handleMobileCategories,
  handleMobileCities,
  handleMobileWeather,
  handleMobileWeatherForecast
};
