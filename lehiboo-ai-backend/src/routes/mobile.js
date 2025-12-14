/**
 * Routes Mobile - API simplifiée pour Flutter
 */

import express from 'express';
import {
  handleMobileChat,
  handleMobileSearch,
  handleMobileCategories,
  handleMobileCities,
  handleMobileWeather,
  handleMobileWeatherForecast,
  handleMobileChatHistory,
  handleMobileChatConversations,
  handleMobileChatClear,
  handleMobileChatQuota
} from '../controllers/mobile-controller.js';
import { validateApiKey } from '../middleware/auth.js';

const router = express.Router();

/**
 * Validation simplifiée pour mobile
 */
const validateMobileChat = (req, res, next) => {
  const { message } = req.body;

  if (!message || typeof message !== 'string' || message.trim().length === 0) {
    return res.status(400).json({
      success: false,
      error: 'Message requis'
    });
  }

  if (message.length > 1000) {
    return res.status(400).json({
      success: false,
      error: 'Message trop long (max 1000 caractères)'
    });
  }

  req.body.message = message.trim();
  next();
};

/**
 * POST /mobile/chat - Chat conversationnel
 */
router.post('/chat', validateApiKey, validateMobileChat, handleMobileChat);

/**
 * POST /mobile/search - Recherche directe (sans conversation)
 */
router.post('/search', validateApiKey, handleMobileSearch);

/**
 * GET /mobile/categories - Liste des catégories
 */
router.get('/categories', validateApiKey, handleMobileCategories);

/**
 * GET /mobile/cities - Villes suggérées
 */
router.get('/cities', validateApiKey, handleMobileCities);

/**
 * GET /mobile/weather - Météo actuelle
 * Query params: city (default: Valenciennes)
 */
router.get('/weather', validateApiKey, handleMobileWeather);

/**
 * GET /mobile/weather/forecast - Prévisions météo
 * Query params: city, days (default: 7)
 */
router.get('/weather/forecast', validateApiKey, handleMobileWeatherForecast);

/**
 * GET /mobile/chat/history - Historique des messages
 * Query params: userId (requis), conversationId (optionnel), limit (default: 50)
 */
router.get('/chat/history', validateApiKey, handleMobileChatHistory);

/**
 * GET /mobile/chat/conversations - Liste des conversations d'un utilisateur
 * Query params: userId (requis)
 */
router.get('/chat/conversations', validateApiKey, handleMobileChatConversations);

/**
 * DELETE /mobile/chat/history - Supprimer l'historique
 * Query params: userId (requis), conversationId (optionnel - si absent, supprime tout)
 */
router.delete('/chat/history', validateApiKey, handleMobileChatClear);

/**
 * GET /mobile/chat/quota - Vérifier le quota de messages
 * Query params: userId (requis)
 * Response: { limit, used, remaining, is_limit_reached, reset_date }
 */
router.get('/chat/quota', validateApiKey, handleMobileChatQuota);

export default router;
