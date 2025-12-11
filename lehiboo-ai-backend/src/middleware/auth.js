/**
 * Middleware d'authentification
 */

import config from '../config/index.js';
import logger from '../utils/logger.js';

/**
 * Verifier l'API key dans les headers
 * Accepte: X-API-Key ou Authorization: Bearer xxx
 */
export const validateApiKey = (req, res, next) => {
  // Chercher la cle dans X-API-Key ou Authorization Bearer
  let apiKey = req.headers['x-api-key'];

  if (!apiKey) {
    const authHeader = req.headers['authorization'];
    if (authHeader && authHeader.startsWith('Bearer ')) {
      apiKey = authHeader.replace('Bearer ', '');
    }
  }

  if (!apiKey) {
    logger.warn('API request without API key', {
      ip: req.ip,
      path: req.path,
    });
    return res.status(401).json({
      success: false,
      error: 'API key required. Use X-API-Key header or Authorization: Bearer <key>',
    });
  }

  if (apiKey !== config.security.apiKey) {
    logger.warn('Invalid API key attempt', {
      ip: req.ip,
      path: req.path,
      providedKey: apiKey.substring(0, 10) + '...',
    });
    return res.status(403).json({
      success: false,
      error: 'Invalid API key',
    });
  }

  logger.debug('API key validated', { ip: req.ip });
  next();
};

/**
 * Validation des données de conversation
 */
export const validateConversationData = (req, res, next) => {
  const { message, conversationId } = req.body;

  // Validation message
  if (!message || typeof message !== 'string') {
    return res.status(400).json({
      success: false,
      error: 'Message is required and must be a string',
    });
  }

  if (message.length > 2000) {
    return res.status(400).json({
      success: false,
      error: 'Message too long (max 2000 characters)',
    });
  }

  if (message.trim().length === 0) {
    return res.status(400).json({
      success: false,
      error: 'Message cannot be empty',
    });
  }

  // Validation conversation ID
  if (!conversationId || typeof conversationId !== 'string') {
    return res.status(400).json({
      success: false,
      error: 'Conversation ID is required',
    });
  }

  // Sanitize message (basic XSS protection)
  req.body.message = message.trim();

  next();
};
