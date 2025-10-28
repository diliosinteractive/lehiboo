/**
 * Routes pour le chat
 */

import express from 'express';
import {
  handleChatRequest,
  handleHealthCheck,
  handleStatus,
} from '../controllers/chat-controller.js';
import {
  validateApiKey,
  validateConversationData,
} from '../middleware/auth.js';

const router = express.Router();

/**
 * POST /chat - Endpoint principal de chat
 * POST /api-planner - Alias pour compatibilité avec frontend
 */
router.post('/chat', validateApiKey, validateConversationData, handleChatRequest);
router.post('/api-planner', validateApiKey, validateConversationData, handleChatRequest);

/**
 * GET /health - Health check
 */
router.get('/health', handleHealthCheck);

/**
 * GET /status - Status détaillé (protégé)
 */
router.get('/status', validateApiKey, handleStatus);

export default router;
