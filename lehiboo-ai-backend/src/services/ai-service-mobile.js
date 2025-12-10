/**
 * Service IA Mobile - Version simplifiée pour Flutter
 *
 * Différences avec v2:
 * - Prompt plus naturel et conversationnel
 * - Pas de tool calls obligatoires
 * - Pas de quick chips systématiques
 * - Recherche possible avec moins d'infos (3 minimum au lieu de 6)
 */

import { openai } from '@ai-sdk/openai';
import { generateText } from 'ai';
import { readFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Import des tools
import { collectUserProfileTool } from '../tools/collect-user-profile.js';
import { searchEventsTool } from '../tools/search-events.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Charger le system prompt mobile
 */
async function loadMobilePrompt() {
  try {
    const promptPath = join(__dirname, '../prompts/system-prompt-mobile.md');
    const content = await readFile(promptPath, 'utf-8');
    logger.info('Mobile system prompt loaded', { length: content.length });
    return content;
  } catch (error) {
    logger.error('Failed to load mobile prompt', { error: error.message });
    throw new Error('Mobile system prompt not found');
  }
}

/**
 * Construire les messages pour l'IA
 */
function buildMessages(currentMessage, history = [], userContext = {}) {
  const messages = [];

  // Historique limité aux 8 derniers messages
  const recentHistory = history.slice(-8);
  recentHistory.forEach(msg => {
    messages.push({ role: msg.role, content: msg.content });
  });

  // Ajouter contexte utilisateur de manière discrète (pas visible dans la réponse)
  let userMessage = currentMessage;

  if (userContext && Object.keys(userContext).length > 0) {
    const contextParts = [];
    if (userContext.groupType) contextParts.push(`groupe: ${userContext.groupType}`);
    if (userContext.activityType) contextParts.push(`activité: ${userContext.activityType}`);
    if (userContext.location?.city) contextParts.push(`ville: ${userContext.location.city}`);
    if (userContext.dates?.type) contextParts.push(`dates: ${userContext.dates.type}`);
    if (userContext.budgetMax) contextParts.push(`budget: ${userContext.budgetMax}€`);
    if (userContext.age) contextParts.push(`âge: ${userContext.age}`);

    if (contextParts.length > 0) {
      userMessage = `[Contexte déjà collecté: ${contextParts.join(', ')}]\n\n${currentMessage}`;
    }
  }

  messages.push({ role: 'user', content: userMessage });
  return messages;
}

/**
 * Vérifier si on peut lancer une recherche (minimum 3 infos)
 */
function canSearch(userContext) {
  const hasGroup = !!userContext.groupType;
  const hasLocation = !!(userContext.location?.city);
  const hasDates = !!(userContext.dates?.type);

  // Minimum: groupe + (location OU dates)
  return hasGroup && (hasLocation || hasDates);
}

/**
 * Générer une réponse IA pour mobile
 */
export async function generateMobileResponse(message, context = {}) {
  try {
    logger.info('Mobile AI request', {
      conversationId: context.conversationId,
      messageLength: message.length,
      historyLength: context.history?.length || 0
    });

    const systemPrompt = await loadMobilePrompt();
    const messages = buildMessages(message, context.history, context.userContext);

    // Tools disponibles
    const tools = {
      collectUserProfile: {
        description: 'Enregistre les infos utilisateur extraites du message. Ne pas appeler si aucune nouvelle info.',
        parameters: collectUserProfileTool.parameters,
        execute: async (input) => {
          return collectUserProfileTool.execute(input, context.userContext || {});
        }
      },
      searchEvents: {
        description: 'Cherche des activités. Appeler dès que tu as: groupe + (ville OU dates).',
        parameters: searchEventsTool.parameters,
        execute: searchEventsTool.execute
      }
    };

    // Appel IA - PAS de toolChoice forcé, l'IA décide
    const result = await generateText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.8, // Un peu plus créatif
      maxTokens: 1500,
      maxSteps: 3
    });

    // Extraire userContext des tool results
    let extractedUserContext = { ...context.userContext };
    let events = [];

    if (result.toolResults && result.toolResults.length > 0) {
      for (const tr of result.toolResults) {
        if (tr.toolName === 'collectUserProfile' && tr.result?.success) {
          extractedUserContext = { ...extractedUserContext, ...tr.result.updatedProfile };
        }
        if (tr.toolName === 'searchEvents' && tr.result?.success) {
          events = tr.result.events || [];
        }
      }
    }

    // Nettoyer le texte de réponse
    let responseText = result.text || '';

    // Supprimer les artifacts JSON/markdown si présents
    responseText = responseText
      .replace(/```json[\s\S]*?```/g, '')
      .replace(/\[Quick Chips:[^\]]*\]/gi, '')
      .replace(/\[Contexte[^\]]*\]/gi, '')
      .trim();

    // Si pas de texte mais des events, générer un message
    if (!responseText && events.length > 0) {
      responseText = `J'ai trouvé ${events.length} activité${events.length > 1 ? 's' : ''} pour toi !`;
    }

    // Si toujours pas de texte, fallback
    if (!responseText) {
      responseText = generateFallback(extractedUserContext);
    }

    return {
      success: true,
      message: responseText,
      userContext: extractedUserContext,
      events: events.map(e => formatEventForMobile(e)),
      canSearch: canSearch(extractedUserContext),
      usage: {
        model: config.openai.defaultModel,
        tokens: result.usage?.totalTokens || 0
      }
    };

  } catch (error) {
    logger.error('Mobile AI error', { error: error.message, stack: error.stack });

    if (error.message?.includes('rate limit')) {
      throw new Error('Trop de requêtes, patiente quelques secondes.');
    }

    throw new Error('Oups, un problème est survenu. Réessaie !');
  }
}

/**
 * Formater un événement pour l'affichage mobile
 */
function formatEventForMobile(event) {
  return {
    id: event.id,
    title: event.title,
    description: event.description?.substring(0, 150) + (event.description?.length > 150 ? '...' : ''),
    image: event.image || event.thumbnail,
    price: event.price,
    priceLabel: event.price === 0 ? 'Gratuit' : `${event.price}€`,
    location: event.location?.city || event.venue,
    date: event.dateLabel || event.date,
    category: event.category,
    matchScore: event.matchScore,
    matchReasons: event.matchReasons?.slice(0, 2) || [],
    url: event.url,
    bookingUrl: event.bookingUrl
  };
}

/**
 * Message de fallback si l'IA ne répond pas
 */
function generateFallback(userContext) {
  if (!userContext.groupType) {
    return "C'est pour qui cette sortie ?";
  }
  if (!userContext.activityType) {
    return "Tu cherches quel type d'activité ?";
  }
  if (!userContext.location?.city && !userContext.dates?.type) {
    return "C'est pour où et quand ?";
  }
  if (!userContext.location?.city) {
    return "Dans quelle ville ?";
  }
  if (!userContext.dates?.type) {
    return "C'est pour quand ?";
  }
  return "Dis-moi ce que tu cherches !";
}

export default { generateMobileResponse };
