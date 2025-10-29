/**
 * Service IA v2 avec AI SDK et OpenAI
 * Intègre les tools Hedwige pour une expérience fluide
 *
 * Architecture: System Prompt v2 + Tools (collectUserProfile, searchEvents)
 */

import { openai } from '@ai-sdk/openai';
import { generateText, streamText } from 'ai';
import { readFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Import des tools Hedwige
import { collectUserProfileTool } from '../tools/collect-user-profile.js';
import { searchEventsTool } from '../tools/search-events.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Charger le system prompt v2
 */
async function loadSystemPromptV2() {
  try {
    const promptPath = join(__dirname, '../prompts/system-prompt-v2.md');
    const content = await readFile(promptPath, 'utf-8');
    logger.info('System prompt v2 loaded', { length: content.length });
    return content;
  } catch (error) {
    logger.error('Failed to load system prompt v2', { error: error.message });
    throw new Error('System prompt not found');
  }
}

/**
 * Construire l'historique de conversation avec userContext
 */
function buildMessages(currentMessage, conversationHistory = [], userContext = {}) {
  const messages = [];

  // Ajouter l'historique existant (limité aux 10 derniers messages)
  const MAX_HISTORY = 10;
  const recentHistory = conversationHistory.slice(-MAX_HISTORY);

  recentHistory.forEach(msg => {
    messages.push({
      role: msg.role,
      content: msg.content
    });
  });

  // Ajouter le message actuel avec le contexte utilisateur
  let userMessage = currentMessage;

  // Si on a un userContext, l'injecter dans le message pour que l'IA sache ce qui a déjà été collecté
  if (userContext && Object.keys(userContext).length > 0) {
    const contextInfo = Object.entries(userContext)
      .filter(([key, value]) => value !== undefined && value !== null)
      .map(([key, value]) => {
        if (typeof value === 'object') {
          return `${key}: ${JSON.stringify(value)}`;
        }
        return `${key}: ${value}`;
      })
      .join(', ');

    if (contextInfo) {
      userMessage = `[CONTEXT: ${contextInfo}]\n\n${currentMessage}`;
    }
  }

  messages.push({
    role: 'user',
    content: userMessage
  });

  return messages;
}

/**
 * Générer une réponse IA (avec tools)
 */
export async function generateAIResponse(message, context = {}) {
  try {
    logger.info('Generating AI response with tools', {
      conversationId: context.conversationId,
      messageLength: message.length,
      hasHistory: !!context.history
    });

    // Charger le system prompt v2
    const systemPrompt = await loadSystemPromptV2();

    // Construire les messages avec userContext
    const messages = buildMessages(message, context.history, context.userContext);

    // Définir les tools disponibles
    const tools = {
      collectUserProfile: {
        description: collectUserProfileTool.description,
        parameters: collectUserProfileTool.parameters,
        execute: collectUserProfileTool.execute
      },
      searchEvents: {
        description: searchEventsTool.description,
        parameters: searchEventsTool.parameters,
        execute: searchEventsTool.execute
      }
    };

    // Appel IA avec tools
    const result = await generateText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.7,
      maxTokens: 4000,
      maxSteps: 5 // Permet à l'IA d'appeler plusieurs tools si nécessaire
    });

    logger.info('AI response generated', {
      conversationId: context.conversationId,
      responseLength: result.text.length,
      tokensUsed: result.usage?.totalTokens || 0,
      toolCallsCount: result.toolCalls?.length || 0,
      steps: result.steps?.length || 1
    });

    // Log tool calls pour debug
    if (result.toolCalls && result.toolCalls.length > 0) {
      logger.info('Tools called by AI', {
        tools: result.toolCalls.map(tc => ({
          name: tc.toolName,
          argsPreview: JSON.stringify(tc.args).substring(0, 100)
        }))
      });
    }

    // Parser la réponse (chercher JSON blocks pour metadata)
    const parsed = parseAIResponse(result.text);

    // Générer les quick chips automatiquement selon le contexte
    const autoQuickChips = generateQuickChips(context.userContext || {});

    return {
      success: true,
      message: parsed.cleanText,
      conversationStage: parsed.metadata.stage || context.currentStage || 'greeting',
      userContext: parsed.metadata.userContext || context.userContext || {},
      quickChips: parsed.metadata.quickChips || autoQuickChips,
      events: parsed.metadata.events || [],
      weatherAlert: parsed.metadata.weatherAlert || null,
      usage: {
        model: config.openai.defaultModel,
        tokens: result.usage?.totalTokens || 0,
        toolCalls: result.toolCalls?.length || 0
      },
      toolResults: result.toolResults || [] // Pour debug
    };

  } catch (error) {
    logger.error('Error generating AI response', {
      error: error.message,
      stack: error.stack,
      conversationId: context.conversationId
    });

    // Erreurs spécifiques
    if (error.message?.includes('token') || error.message?.includes('context')) {
      throw new Error('La conversation est trop longue. Veuillez recommencer.');
    }

    if (error.message?.includes('rate limit')) {
      throw new Error('Trop de requêtes. Veuillez patienter quelques instants.');
    }

    if (error.message?.includes('API key')) {
      throw new Error('Erreur de configuration API. Contactez le support.');
    }

    throw new Error('Erreur lors de la génération de la réponse. Veuillez réessayer.');
  }
}

/**
 * Streaming AI response (avec tools)
 */
export async function streamAIResponse(message, context = {}) {
  try {
    logger.info('Streaming AI response with tools', {
      conversationId: context.conversationId
    });

    const systemPrompt = await loadSystemPromptV2();
    const messages = buildMessages(message, context.history, context.userContext);

    const tools = {
      collectUserProfile: {
        description: collectUserProfileTool.description,
        parameters: collectUserProfileTool.parameters,
        execute: collectUserProfileTool.execute
      },
      searchEvents: {
        description: searchEventsTool.description,
        parameters: searchEventsTool.parameters,
        execute: searchEventsTool.execute
      }
    };

    const result = await streamText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.7,
      maxTokens: 4000,
      maxSteps: 5,
      onStepFinish: (step) => {
        // Log chaque étape pour debug
        logger.debug('Stream step finished', {
          stepType: step.type,
          hasToolCalls: !!step.toolCalls,
          toolCount: step.toolCalls?.length || 0
        });
      }
    });

    return result.textStream;

  } catch (error) {
    logger.error('Error streaming AI response', {
      error: error.message,
      conversationId: context.conversationId
    });

    throw new Error('Failed to stream AI response');
  }
}

/**
 * Parser la réponse de l'IA pour extraire les métadonnées
 */
function parseAIResponse(text) {
  // Chercher des blocks JSON dans la réponse
  const jsonBlockRegex = /```json\s*([\s\S]*?)\s*```/g;
  let metadata = {};
  let match;

  while ((match = jsonBlockRegex.exec(text)) !== null) {
    try {
      const parsed = JSON.parse(match[1]);
      metadata = { ...metadata, ...parsed };
    } catch (e) {
      logger.warn('Failed to parse JSON block', {
        block: match[1].substring(0, 100)
      });
    }
  }

  // Nettoyer le texte des blocks JSON
  const cleanText = text.replace(jsonBlockRegex, '').trim();

  return {
    cleanText,
    metadata
  };
}

/**
 * Générer les quick chips selon l'état du profil utilisateur
 */
function generateQuickChips(userContext = {}) {
  // Si pas de groupType
  if (!userContext.groupType) {
    return [
      { text: '🧍 Solo', value: 'solo', type: 'groupType' },
      { text: '💑 En couple', value: 'couple', type: 'groupType' },
      { text: '👨‍👩‍👧 En famille', value: 'family', type: 'groupType' },
      { text: '👥 Entre amis', value: 'friends', type: 'groupType' }
    ];
  }

  // Si pas d'activityType
  if (!userContext.activityType) {
    return [
      { text: '🎭 Culture', value: 'culture', type: 'activityType' },
      { text: '⚽ Sport', value: 'sport', type: 'activityType' },
      { text: '🍷 Gastronomie', value: 'gastronomie', type: 'activityType' },
      { text: '🌳 Nature', value: 'nature', type: 'activityType' },
      { text: '💆 Détente', value: 'detente', type: 'activityType' }
    ];
  }

  // Si pas de dates
  if (!userContext.dates) {
    return [
      { text: '📅 Ce weekend', value: 'thisWeekend', type: 'dates' },
      { text: '📅 Prochain weekend', value: 'nextWeekend', type: 'dates' },
      { text: '📅 Dates précises', value: 'specific', type: 'dates' },
      { text: '📅 Flexible', value: 'flexible', type: 'dates' }
    ];
  }

  // Si pas de budgetMax
  if (!userContext.budgetMax) {
    return [
      { text: '💰 Moins de 20€', value: '20', type: 'budgetMax' },
      { text: '💰 20-50€', value: '50', type: 'budgetMax' },
      { text: '💰 50-100€', value: '100', type: 'budgetMax' },
      { text: '💰 Plus de 100€', value: '150', type: 'budgetMax' }
    ];
  }

  // Si profil complet, proposer actions
  return [
    { text: '🔍 Afficher les résultats', value: 'show_results', type: 'action' },
    { text: '🔄 Modifier mes critères', value: 'modify', type: 'action' }
  ];
}

/**
 * Tester la connexion OpenAI
 */
export async function testOpenAIConnection() {
  try {
    const { text } = await generateText({
      model: openai(config.openai.defaultModel),
      messages: [{ role: 'user', content: 'Hello' }],
      maxTokens: 10,
    });

    logger.info('OpenAI connection test successful', { response: text });
    return true;
  } catch (error) {
    logger.error('OpenAI connection test failed', {
      error: error.message,
    });
    return false;
  }
}

export default {
  generateAIResponse,
  streamAIResponse,
  testOpenAIConnection
};
