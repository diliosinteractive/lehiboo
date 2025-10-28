/**
 * Contrôleur pour les endpoints de chat
 */

import { generateAIResponse } from '../services/ai-service-v2.js';
import logger from '../utils/logger.js';

/**
 * Endpoint principal de chat
 */
export async function handleChatRequest(req, res) {
  try {
    const {
      message,
      conversationId,
      userContext = {},
      currentStage = 'greeting',
      history = [],
    } = req.body;

    logger.info('Chat request received', {
      conversationId,
      stage: currentStage,
      messagePreview: message.substring(0, 50),
      historyLength: history.length,
    });

    // Préparer le contexte (le nouveau service v2 gère l'extraction via les tools)
    const context = {
      conversationId,
      userContext,
      currentStage,
      history,
    };

    // Générer la réponse IA
    const response = await generateAIResponse(message, context);

    // Ajouter le message utilisateur et la réponse à l'historique
    const updatedHistory = [
      ...history,
      {
        role: 'user',
        content: message,
        timestamp: new Date().toISOString(),
      },
      {
        role: 'assistant',
        content: response.message,
        timestamp: new Date().toISOString(),
      },
    ];

    // Limiter l'historique aux 20 derniers messages
    if (updatedHistory.length > 20) {
      updatedHistory.splice(0, updatedHistory.length - 20);
    }

    // Retourner la réponse
    return res.json({
      success: true,
      message: response.message,
      conversationStage: response.conversationStage,
      userContext: response.userContext,
      quickChips: response.quickChips,
      events: response.events,
      weatherAlert: response.weatherAlert,
      history: updatedHistory,
      usage: response.usage,
    });
  } catch (error) {
    logger.error('Error handling chat request', {
      error: error.message,
      stack: error.stack,
    });

    return res.status(500).json({
      success: false,
      error: 'Internal server error',
      message: 'Désolé, une erreur est survenue. Veuillez réessayer.',
    });
  }
}

/**
 * Health check endpoint
 */
export async function handleHealthCheck(req, res) {
  return res.json({
    status: 'ok',
    timestamp: new Date().toISOString(),
    version: '1.0.0',
  });
}

/**
 * Status endpoint (pour debug)
 */
export async function handleStatus(req, res) {
  return res.json({
    status: 'ok',
    services: {
      openai: 'connected', // TODO: vérifier réellement
      mcp: 'pending', // TODO: implémenter MCP
      weather: 'pending', // TODO: implémenter Weather
    },
    timestamp: new Date().toISOString(),
  });
}
