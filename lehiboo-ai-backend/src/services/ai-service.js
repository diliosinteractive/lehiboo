/**
 * Service IA avec AI SDK et OpenAI
 * Intègre les tools Hedwige pour une expérience fluide
 */

import { openai } from '@ai-sdk/openai';
import { generateText, streamText } from 'ai';
import config from '../config/index.js';
import logger from '../utils/logger.js';
import { loadSystemPrompt } from './prompt-service.js';
import { getToolsDefinitions, executeTool } from '../mcp/tools.js';

// Import des nouveaux tools Hedwige
import { collectUserProfileTool } from '../tools/collect-user-profile.js';
import { searchEventsTool } from '../tools/search-events.js';

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
      model: openai(config.openai.defaultModel),
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
          model: openai(config.openai.defaultModel),
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
        model: config.openai.defaultModel,
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
      model: openai(config.openai.defaultModel),
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
 * Extraire les informations utilisateur du message pour mettre à jour le contexte
 * Exportée pour utilisation dans le controller
 */
export function extractUserInfoFromMessage(message, currentContext, currentStage) {
  const updatedContext = { ...currentContext };
  const lowerMessage = message.toLowerCase().trim();

  // Détection du type de groupe
  const groupPatterns = {
    solo: /\b(solo|seul|moi|individuel)\b/i,
    couple: /\b(couple|deux|mon copain|ma copine|conjoint|mari|femme)\b/i,
    family: /\b(famille|enfants|kids|parent|papa|maman)\b/i,
    friends: /\b(amis|potes|copains|groupe|entre amis)\b/i,
  };

  for (const [type, pattern] of Object.entries(groupPatterns)) {
    if (pattern.test(message) && !updatedContext.groupType) {
      updatedContext.groupType = type;
      break;
    }
  }

  // Détection de l'âge
  const agePatterns = [
    { pattern: /\b(18-25|18 25|18\s*-\s*25)\b/i, value: '18-25' },
    { pattern: /\b(26-35|25-35|26 35|25 35)\b/i, value: '26-35' },
    { pattern: /\b(36-50|35-50|36 50|35 50)\b/i, value: '36-50' },
    { pattern: /\b(50\+|50 plus|plus de 50)\b/i, value: '50+' },
  ];

  for (const { pattern, value } of agePatterns) {
    if (pattern.test(message) && !updatedContext.age) {
      updatedContext.age = value;
      break;
    }
  }

  // Détection des dates
  const datePatterns = {
    'ce-weekend': /\b(ce week-?end|ce we|weekend|week end)\b/i,
    'weekend-prochain': /\b(week-?end prochain|prochain we|next weekend)\b/i,
    'flexible': /\b(flexible|pas de date|n'importe quand)\b/i,
  };

  for (const [type, pattern] of Object.entries(datePatterns)) {
    if (pattern.test(message) && !updatedContext.datePreference) {
      updatedContext.datePreference = type;
      break;
    }
  }

  // Détection des préférences d'activité
  const activityPatterns = {
    sport: /\b(sport|sportif|actif|gym|course|vélo|escalade)\b/i,
    culture: /\b(culture|culturel|musée|exposition|art|galerie)\b/i,
    gastronomie: /\b(gastronomie|restaurant|food|manger|cuisine|culinaire)\b/i,
    nature: /\b(nature|outdoor|plein air|randonnée|balade)\b/i,
    detente: /\b(détente|relaxation|spa|massage|bien-être|wellness)\b/i,
  };

  for (const [type, pattern] of Object.entries(activityPatterns)) {
    if (pattern.test(message)) {
      if (!updatedContext.activityPreferences) {
        updatedContext.activityPreferences = [];
      }
      if (!updatedContext.activityPreferences.includes(type)) {
        updatedContext.activityPreferences.push(type);
      }
    }
  }

  // Détection du budget
  const budgetPatterns = {
    economique: /\b(économique|pas cher|budget serré|petit budget|moins de 50)\b/i,
    modere: /\b(modéré|moyen|raisonnable|50-100|entre 50 et 100)\b/i,
    premium: /\b(premium|luxe|haut de gamme|plus de 100)\b/i,
  };

  for (const [type, pattern] of Object.entries(budgetPatterns)) {
    if (pattern.test(message) && !updatedContext.budget) {
      updatedContext.budget = type;
      break;
    }
  }

  return updatedContext;
}

/**
 * Déterminer le prochain stage basé sur les informations collectées
 */
function determineNextStage(userContext, currentStage) {
  // Flow logique des stages
  const stageFlow = {
    greeting: 'age_collection',
    age_collection: 'dates_weather',
    dates_weather: 'preferences',
    preferences: 'recommendations',
    recommendations: 'booking',
  };

  // Vérifier si on peut sauter des stages si l'info est déjà présente
  if (!userContext.age && currentStage === 'greeting') {
    return 'age_collection';
  }

  if (!userContext.datePreference && userContext.age) {
    return 'dates_weather';
  }

  if (!userContext.activityPreferences && userContext.datePreference) {
    return 'preferences';
  }

  // Si on a toutes les infos nécessaires, aller aux recommandations
  if (
    userContext.groupType &&
    userContext.age &&
    userContext.datePreference &&
    userContext.activityPreferences &&
    userContext.activityPreferences.length > 0
  ) {
    return 'recommendations';
  }

  // Sinon, suivre le flow normal
  return stageFlow[currentStage] || currentStage;
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

  // Extraire les infos du contexte utilisateur depuis les métadonnées IA
  let updatedUserContext = metadata.userContext || context.userContext || {};

  // Déterminer le stage (priorité: metadata > context actuel)
  let detectedStage = metadata.stage || determineNextStage(updatedUserContext, context.currentStage);

  // Log pour debugging
  logger.debug('Parsed AI response', {
    stage: detectedStage,
    userContext: updatedUserContext,
    hasQuickChips: (metadata.quickChips || []).length > 0,
    hasEvents: (metadata.events || []).length > 0,
  });

  return {
    message: cleanText,
    stage: detectedStage,
    userContext: updatedUserContext,
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
