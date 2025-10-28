/**
 * Service IA avec AI SDK et OpenRouter
 */

import { createOpenAI } from '@ai-sdk/openai';
import { generateText, streamText } from 'ai';
import config from '../config/index.js';
import logger from '../utils/logger.js';
import { loadSystemPrompt } from './prompt-service.js';
import { getToolsDefinitions, executeTool } from '../mcp/tools.js';

/**
 * Initialiser le client OpenRouter via AI SDK
 */
const openrouter = createOpenAI({
  apiKey: config.openrouter.apiKey,
  baseURL: config.openrouter.baseUrl,
});

/**
 * Générer une réponse IA (non-streaming)
 */
export async function generateAIResponse(message, context = {}) {
  try {
    logger.info('Generating AI response', {
      conversationId: context.conversationId,
      stage: context.currentStage,
      messageLength: message.length,
    });

    // Charger le prompt système
    const systemPrompt = await loadSystemPrompt(context);

    // Construire l'historique de conversation
    const messages = buildConversationHistory(message, context);

    // TODO: Réactiver les MCP Tools une fois convertis en schémas Zod
    // const tools = getToolsDefinitions();
    // const enhancedSystemPrompt = systemPrompt + '\n\n' + generateToolsInstructions(tools);

    // Calculer le budget de tokens dynamiquement
    const tokenBudget = calculateTokenBudget(systemPrompt, messages);

    // Générer la réponse (sans tools pour l'instant)
    let { text, usage, toolCalls } = await generateText({
      model: openrouter(config.openrouter.defaultModel),
      system: systemPrompt,
      messages,
      temperature: 0.7,
      maxTokens: tokenBudget,
      // tools désactivés temporairement - cause erreur Zod schema
    });

    // Exécuter les tool calls si l'IA en a fait
    let toolResults = [];
    if (toolCalls && toolCalls.length > 0) {
      logger.info('AI requested tool calls', {
        count: toolCalls.length,
        tools: toolCalls.map((tc) => tc.toolName),
      });

      toolResults = await Promise.all(
        toolCalls.map(async (toolCall) => {
          const result = await executeTool(toolCall.toolName, toolCall.args);
          return {
            toolName: toolCall.toolName,
            result,
          };
        })
      );

      // Si des tools ont été appelés, régénérer une réponse avec les résultats
      if (toolResults.length > 0) {
        const toolResultsText = toolResults
          .map((tr) => `Tool ${tr.toolName} result: ${JSON.stringify(tr.result)}`)
          .join('\n');

        messages.push({
          role: 'assistant',
          content: `[Tool calls executed]`,
        });

        messages.push({
          role: 'user',
          content: `Here are the tool results:\n${toolResultsText}\n\nNow please provide your response to the user based on these results.`,
        });

        const secondResponse = await generateText({
          model: openrouter(config.openrouter.defaultModel),
          system: systemPrompt,
          messages,
          temperature: 0.7,
          maxTokens: tokenBudget,
        });

        text = secondResponse.text;
        usage = secondResponse.usage;
      }
    }

    // Valider et nettoyer la réponse UTF-8
    text = sanitizeResponse(text);

    logger.info('AI response generated', {
      conversationId: context.conversationId,
      responseLength: text.length,
      tokensUsed: usage?.totalTokens || 0,
    });

    // Parser la réponse pour extraire les métadonnées
    const parsedResponse = parseAIResponse(text, context);

    return {
      success: true,
      message: parsedResponse.message,
      conversationStage: parsedResponse.stage || context.currentStage,
      userContext: parsedResponse.userContext || context.userContext,
      quickChips: parsedResponse.quickChips || [],
      events: parsedResponse.events || [],
      weatherAlert: parsedResponse.weatherAlert || null,
      usage: {
        model: config.openrouter.defaultModel,
        tokens: usage?.totalTokens || 0,
      },
    };
  } catch (error) {
    logger.error('Error generating AI response', {
      error: error.message,
      stack: error.stack,
      conversationId: context.conversationId,
      errorType: error.name,
    });

    // Détection d'erreurs spécifiques
    if (error.message?.includes('token') || error.message?.includes('context')) {
      throw new Error('La conversation est trop longue. Veuillez recommencer une nouvelle conversation.');
    }

    if (error.message?.includes('rate limit')) {
      throw new Error('Trop de requêtes en cours. Veuillez patienter quelques instants.');
    }

    throw new Error('Erreur lors de la génération de la réponse. Veuillez réessayer.');
  }
}

/**
 * Générer une réponse IA en streaming
 */
export async function streamAIResponse(message, context = {}) {
  try {
    logger.info('Streaming AI response', {
      conversationId: context.conversationId,
      stage: context.currentStage,
    });

    const systemPrompt = await loadSystemPrompt(context);
    const messages = buildConversationHistory(message, context);
    const tokenBudget = calculateTokenBudget(systemPrompt, messages);

    const { textStream } = await streamText({
      model: openrouter(config.openrouter.defaultModel),
      system: systemPrompt,
      messages,
      temperature: 0.7,
      maxTokens: tokenBudget,
    });

    return textStream;
  } catch (error) {
    logger.error('Error streaming AI response', {
      error: error.message,
      conversationId: context.conversationId,
    });

    throw new Error('Failed to stream AI response');
  }
}

/**
 * Calculer le budget de tokens disponible pour la réponse
 * Claude 3.5 Sonnet a 200k tokens de contexte, mais on limite pour éviter la troncature
 */
function calculateTokenBudget(systemPrompt, messages) {
  // Estimation grossière: ~4 caractères = 1 token
  const systemPromptTokens = Math.ceil(systemPrompt.length / 4);
  const messagesTokens = messages.reduce((acc, msg) => acc + Math.ceil(msg.content.length / 4), 0);

  const totalInputTokens = systemPromptTokens + messagesTokens;

  // Budget total disponible (conservateur pour éviter rate limits)
  const MAX_CONTEXT = 100000; // 100k tokens au lieu de 200k pour la sécurité
  const MAX_RESPONSE = 4000; // Augmenté de 1000 à 4000

  // Si l'input dépasse déjà 50% du contexte, réduire la réponse
  if (totalInputTokens > MAX_CONTEXT * 0.5) {
    const remaining = MAX_CONTEXT - totalInputTokens;
    const budget = Math.min(remaining * 0.8, MAX_RESPONSE); // Garder 20% de marge
    logger.warn('High token usage detected', {
      systemPromptTokens,
      messagesTokens,
      totalInputTokens,
      calculatedBudget: Math.floor(budget),
    });
    return Math.max(500, Math.floor(budget)); // Minimum 500 tokens
  }

  return MAX_RESPONSE;
}

/**
 * Valider que le texte est UTF-8 valide et le nettoyer si nécessaire
 */
function sanitizeResponse(text) {
  if (!text || typeof text !== 'string') {
    logger.warn('Invalid response text', { type: typeof text });
    return '';
  }

  // Vérifier si le texte contient des caractères de contrôle invalides
  // ou des séquences UTF-8 cassées
  try {
    // Encoder puis décoder pour détecter les problèmes UTF-8
    const encoder = new TextEncoder();
    const decoder = new TextDecoder('utf-8', { fatal: true });
    const encoded = encoder.encode(text);
    const decoded = decoder.decode(encoded);

    // Nettoyer les caractères de contrôle sauf les retours de ligne
    return decoded.replace(/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/g, '');
  } catch (error) {
    logger.error('UTF-8 validation failed, attempting recovery', { error: error.message });

    // Fallback: supprimer les caractères non-ASCII problématiques
    return text
      .split('')
      .filter(char => {
        const code = char.charCodeAt(0);
        // Garder ASCII basique + caractères Unicode valides
        return (code >= 32 && code < 127) || code >= 128;
      })
      .join('');
  }
}

/**
 * Construire l'historique de conversation pour le contexte IA
 * Avec sliding window intelligent pour limiter la taille
 */
function buildConversationHistory(currentMessage, context) {
  const messages = [];

  // Ajouter l'historique existant si disponible
  if (context.history && Array.isArray(context.history)) {
    // Sliding window intelligent: garder les N derniers messages
    // mais toujours inclure le premier message (greeting) pour le contexte
    const MAX_HISTORY_MESSAGES = 12; // Réduit de 20 à 12

    if (context.history.length > MAX_HISTORY_MESSAGES) {
      // Garder le premier message (greeting initial)
      const firstMessage = context.history[0];

      // Prendre les derniers messages
      const recentMessages = context.history.slice(-(MAX_HISTORY_MESSAGES - 1));

      // Combiner
      messages.push({
        role: firstMessage.role,
        content: firstMessage.content,
      });

      // Ajouter un marqueur de contexte tronqué
      if (context.history.length > MAX_HISTORY_MESSAGES + 1) {
        messages.push({
          role: 'assistant',
          content: '[Historique précédent résumé pour optimiser le contexte]',
        });
      }

      recentMessages.forEach((msg) => {
        messages.push({
          role: msg.role,
          content: msg.content,
        });
      });

      logger.info('History window applied', {
        originalLength: context.history.length,
        keptLength: messages.length,
      });
    } else {
      // Historique assez court, tout garder
      context.history.forEach((msg) => {
        messages.push({
          role: msg.role,
          content: msg.content,
        });
      });
    }
  }

  // Ajouter le message actuel
  messages.push({
    role: 'user',
    content: currentMessage,
  });

  return messages;
}

/**
 * Parser la réponse de l'IA pour extraire les métadonnées
 * L'IA peut retourner du JSON structuré pour les quick chips, events, etc.
 */
function parseAIResponse(text, context) {
  // Chercher des blocks JSON dans la réponse
  const jsonBlockRegex = /```json\s*([\s\S]*?)\s*```/g;
  let match;
  let metadata = {};

  while ((match = jsonBlockRegex.exec(text)) !== null) {
    try {
      const parsed = JSON.parse(match[1]);
      metadata = { ...metadata, ...parsed };
    } catch (e) {
      logger.warn('Failed to parse JSON block in AI response', { block: match[1] });
    }
  }

  // Nettoyer le texte des blocks JSON
  const cleanText = text.replace(jsonBlockRegex, '').trim();

  // Détection automatique du stage si non fourni
  let detectedStage = context.currentStage;
  if (metadata.stage) {
    detectedStage = metadata.stage;
  } else {
    // Heuristique basique
    if (cleanText.toLowerCase().includes('quel âge') || cleanText.toLowerCase().includes('âge avez-vous')) {
      detectedStage = 'age_collection';
    } else if (cleanText.toLowerCase().includes('quand souhaitez-vous') || cleanText.toLowerCase().includes('quelle date')) {
      detectedStage = 'dates_weather';
    } else if (cleanText.toLowerCase().includes('type d\'activité') || cleanText.toLowerCase().includes('vous intéresse')) {
      detectedStage = 'preferences';
    } else if (cleanText.toLowerCase().includes('recommandations') || cleanText.toLowerCase().includes('voici')) {
      detectedStage = 'recommendations';
    }
  }

  return {
    message: cleanText,
    stage: detectedStage,
    userContext: metadata.userContext || context.userContext || {},
    quickChips: metadata.quickChips || [],
    events: metadata.events || [],
    weatherAlert: metadata.weatherAlert || null,
  };
}

/**
 * Générer les instructions pour les tools (ajouté au prompt système)
 */
function generateToolsInstructions(tools) {
  let instructions = '\n## Available Tools\n\n';
  instructions += 'You have access to the following tools to search for real events:\n\n';

  tools.forEach((tool) => {
    instructions += `### ${tool.name}\n`;
    instructions += `${tool.description}\n\n`;
    instructions += 'Parameters:\n';
    instructions += '```json\n';
    instructions += JSON.stringify(tool.parameters, null, 2);
    instructions += '\n```\n\n';
  });

  instructions += '\n**IMPORTANT**: When you reach the recommendations stage, you MUST use the search_events tool to find real events instead of inventing them.\n';
  instructions += 'Use the user context (age, group type, dates, preferences) to search appropriately.\n';

  return instructions;
}

/**
 * Tester la connexion OpenRouter
 */
export async function testOpenRouterConnection() {
  try {
    const { text } = await generateText({
      model: openrouter(config.openrouter.defaultModel),
      messages: [{ role: 'user', content: 'Hello' }],
      maxTokens: 10,
    });

    logger.info('OpenRouter connection test successful', { response: text });
    return true;
  } catch (error) {
    logger.error('OpenRouter connection test failed', {
      error: error.message,
    });
    return false;
  }
}
