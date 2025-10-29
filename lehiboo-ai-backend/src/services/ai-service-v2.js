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
 * Charger le system prompt v3 minimal (10x plus court)
 */
async function loadSystemPromptV3() {
  try {
    const promptPath = join(__dirname, '../prompts/system-prompt-v3-minimal.md');
    const content = await readFile(promptPath, 'utf-8');
    logger.info('System prompt v3 minimal loaded', {
      length: content.length,
      lines: content.split('\n').length
    });
    return content;
  } catch (error) {
    logger.error('Failed to load system prompt v3', { error: error.message });
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
    logger.info('🔵 [DEBUG] Generating AI response', {
      conversationId: context.conversationId,
      messageLength: message.length,
      historyLength: context.history?.length || 0,
      userContextKeys: Object.keys(context.userContext || {}),
      currentStage: context.currentStage
    });

    // Log l'historique complet pour debug
    if (context.history && context.history.length > 0) {
      logger.info('🔵 [DEBUG] History received', {
        historyCount: context.history.length,
        history: context.history.map(msg => ({
          role: msg.role,
          contentPreview: msg.content?.substring(0, 100)
        }))
      });
    } else {
      logger.warn('⚠️  [DEBUG] NO HISTORY - First message or history not sent');
    }

    // Log userContext
    if (context.userContext && Object.keys(context.userContext).length > 0) {
      logger.info('🔵 [DEBUG] UserContext received', {
        userContext: context.userContext
      });
    } else {
      logger.warn('⚠️  [DEBUG] NO USERCONTEXT - Starting fresh');
    }

    // Charger le system prompt v3 minimal
    const systemPrompt = await loadSystemPromptV3();

    // Construire les messages avec userContext
    const messages = buildMessages(message, context.history, context.userContext);

    // Log ce qui sera envoyé à OpenAI
    logger.info('🔵 [DEBUG] Messages to OpenAI', {
      messagesCount: messages.length,
      messages: messages.map(msg => ({
        role: msg.role,
        contentPreview: msg.content?.substring(0, 150)
      }))
    });

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

    // Log pour debug : voir ce qui est envoyé à OpenAI
    logger.info('Calling OpenAI API', {
      systemPromptLength: systemPrompt.length,
      messagesCount: messages.length,
      toolsCount: Object.keys(tools).length,
      toolNames: Object.keys(tools),
      lastUserMessage: messages[messages.length - 1]?.content?.substring(0, 200),
      model: config.openai.defaultModel
    });

    // Appel IA avec tools
    const result = await generateText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.7,
      maxTokens: 4000,
      maxSteps: 10 // Augmenté pour permettre tool call + texte
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

    // Extraire userContext depuis les tool results
    let extractedUserContext = { ...context.userContext };

    if (result.toolResults && result.toolResults.length > 0) {
      logger.info('🔵 [DEBUG] Tool results received', {
        count: result.toolResults.length,
        results: result.toolResults.map(tr => ({
          toolName: tr.toolName,
          result: tr.result
        }))
      });

      // Chercher le résultat de collectUserProfile
      const profileResult = result.toolResults.find(tr => tr.toolName === 'collectUserProfile');
      if (profileResult && profileResult.result?.success && profileResult.result?.updatedProfile) {
        extractedUserContext = {
          ...extractedUserContext,
          ...profileResult.result.updatedProfile
        };
        logger.info('🔵 [DEBUG] UserContext extracted from tool', {
          extractedUserContext
        });
      }
    }

    // Parser la réponse (chercher JSON blocks pour metadata)
    const parsed = parseAIResponse(result.text);

    // Générer les quick chips automatiquement selon le contexte
    const autoQuickChips = generateQuickChips(extractedUserContext);

    return {
      success: true,
      message: parsed.cleanText,
      conversationStage: parsed.metadata.stage || context.currentStage || 'greeting',
      userContext: extractedUserContext, // Utiliser le contexte extrait des tools
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
 * Parser la réponse de l'IA pour extraire les métadonnées et quick chips
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

  // Parser les Quick Chips si présents dans le texte
  // Format: [Quick Chips: Option1 | Option2 | Option3]
  const quickChipsRegex = /\[Quick Chips:\s*([^\]]+)\]/gi;
  const quickChipsMatch = quickChipsRegex.exec(text);

  if (quickChipsMatch) {
    const chipsText = quickChipsMatch[1];
    const chipOptions = chipsText.split('|').map(opt => opt.trim());

    // Convertir en format attendu par le frontend
    metadata.quickChips = chipOptions.map(option => {
      // Déterminer le type selon le contexte des options
      let type = 'action';
      let value = option.toLowerCase();

      // Mapping des options connues
      if (['solo', 'couple', 'famille', 'amis'].some(opt => value.includes(opt))) {
        type = 'groupType';
        if (value.includes('solo')) value = 'solo';
        else if (value.includes('couple')) value = 'couple';
        else if (value.includes('famille')) value = 'family';
        else if (value.includes('amis')) value = 'friends';
      } else if (['culture', 'sport', 'gastronomie', 'nature', 'détente', 'detente'].some(opt => value.includes(opt))) {
        type = 'activityType';
        if (value.includes('culture')) value = 'culture';
        else if (value.includes('sport')) value = 'sport';
        else if (value.includes('gastronomie')) value = 'gastronomie';
        else if (value.includes('nature')) value = 'nature';
        else if (value.includes('détente') || value.includes('detente')) value = 'detente';
      } else if (value.includes('weekend') || value.includes('dates') || value.includes('flexible')) {
        type = 'dates';
        if (value.includes('ce weekend')) value = 'thisWeekend';
        else if (value.includes('prochain weekend')) value = 'nextWeekend';
        else if (value.includes('précises')) value = 'specific';
        else if (value.includes('flexible')) value = 'flexible';
      } else if (value.includes('€') || value.includes('euro') || value.includes('budget')) {
        type = 'budgetMax';
        // Extraire le nombre le plus élevé de la fourchette
        if (value.includes('20') && !value.includes('-')) value = '20';
        else if (value.includes('50')) value = '50';
        else if (value.includes('100')) value = '100';
        else if (value.includes('150') || value.includes('plus')) value = '150';
      }

      return {
        text: option,
        value: value,
        type: type
      };
    });

    logger.info('Parsed Quick Chips from AI response', {
      count: metadata.quickChips.length,
      chips: metadata.quickChips
    });
  }

  // Nettoyer le texte des blocks JSON ET des Quick Chips
  let cleanText = text.replace(jsonBlockRegex, '').trim();
  cleanText = cleanText.replace(quickChipsRegex, '').trim();

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
