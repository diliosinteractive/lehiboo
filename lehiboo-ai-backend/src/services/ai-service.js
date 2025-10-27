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

    // Obtenir les MCP Tools disponibles
    const tools = getToolsDefinitions();

    // Ajouter les tools au prompt système
    const enhancedSystemPrompt = systemPrompt + '\n\n' + generateToolsInstructions(tools);

    // Générer la réponse avec tools
    let { text, usage, toolCalls } = await generateText({
      model: openrouter(config.openrouter.defaultModel),
      system: enhancedSystemPrompt,
      messages,
      temperature: 0.7,
      maxTokens: 1000,
      tools: tools.reduce((acc, tool) => {
        acc[tool.name] = {
          description: tool.description,
          parameters: tool.parameters,
        };
        return acc;
      }, {}),
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
          system: enhancedSystemPrompt,
          messages,
          temperature: 0.7,
          maxTokens: 1000,
        });

        text = secondResponse.text;
        usage = secondResponse.usage;
      }
    }

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
    });

    throw new Error('Failed to generate AI response');
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

    const { textStream } = await streamText({
      model: openrouter(config.openrouter.defaultModel),
      system: systemPrompt,
      messages,
      temperature: 0.7,
      maxTokens: 1000,
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
 * Construire l'historique de conversation pour le contexte IA
 */
function buildConversationHistory(currentMessage, context) {
  const messages = [];

  // Ajouter l'historique existant si disponible
  if (context.history && Array.isArray(context.history)) {
    context.history.forEach((msg) => {
      messages.push({
        role: msg.role,
        content: msg.content,
      });
    });
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
