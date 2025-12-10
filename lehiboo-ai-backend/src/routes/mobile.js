/**
 * Routes Mobile - API simplifiée pour Flutter
 */

import express from 'express';
import {
  handleMobileChat,
  handleMobileSearch,
  handleMobileCategories,
  handleMobileCities
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
router.get('/categories', handleMobileCategories);

/**
 * GET /mobile/cities - Villes suggérées
 */
router.get('/cities', handleMobileCities);

export default router;
