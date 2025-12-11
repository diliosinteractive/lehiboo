/**
 * Service IA Mobile v2 - Version simplifiee
 *
 * Changements majeurs:
 * - L'IA lance searchEvents rapidement (pas besoin de collecter 6 infos)
 * - Valeurs par defaut intelligentes
 * - Plus de collectUserProfile obligatoire
 * - Prompt plus concis et naturel
 */

import { openai } from '@ai-sdk/openai';
import { generateText } from 'ai';
import { readFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Import du nouveau tool simplifie
import { searchEventsToolV2 } from '../tools/search-events-v2.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Charger le system prompt mobile v2
 */
async function loadMobilePrompt() {
  try {
    const promptPath = join(__dirname, '../prompts/system-prompt-mobile-v2.md');
    const content = await readFile(promptPath, 'utf-8');
    logger.info('Mobile system prompt v2 loaded', { length: content.length });
    return content;
  } catch (error) {
    logger.error('Failed to load mobile prompt v2', { error: error.message });
    throw new Error('Mobile system prompt v2 not found');
  }
}

/**
 * Construire les messages pour l'IA
 * Simplifie: on passe juste l'historique et le message
 */
function buildMessages(currentMessage, history = []) {
  const messages = [];

  // Historique limite aux 10 derniers messages
  const recentHistory = history.slice(-10);
  recentHistory.forEach(msg => {
    messages.push({ role: msg.role, content: msg.content });
  });

  // Message actuel
  messages.push({ role: 'user', content: currentMessage });

  return messages;
}

/**
 * Generer une reponse IA pour mobile
 */
export async function generateMobileResponse(message, context = {}) {
  try {
    logger.info('Mobile AI request v2', {
      conversationId: context.conversationId,
      messageLength: message.length,
      historyLength: context.history?.length || 0
    });

    const systemPrompt = await loadMobilePrompt();
    const messages = buildMessages(message, context.history);

    // Un seul tool: searchEvents (simplifie)
    const tools = {
      searchEvents: {
        description: searchEventsToolV2.description,
        parameters: searchEventsToolV2.parameters,
        execute: searchEventsToolV2.execute
      }
    };

    // Appel IA - temperature plus basse pour etre plus fiable
    const result = await generateText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.7,
      maxTokens: 800, // Reponses plus courtes
      maxSteps: 3
    });

    // Extraire les events des tool results
    let events = [];
    let searchParams = null;
    let searchFilters = null;

    logger.info('Tool results received', {
      hasToolResults: !!result.toolResults,
      toolResultsCount: result.toolResults?.length || 0,
      toolCalls: result.toolCalls?.map(tc => ({ name: tc.toolName, args: tc.args }))
    });

    if (result.toolResults && result.toolResults.length > 0) {
      for (const tr of result.toolResults) {
        logger.info('Processing tool result', {
          toolName: tr.toolName,
          success: tr.result?.success,
          eventsCount: tr.result?.events?.length || 0,
          error: tr.result?.error,
          message: tr.result?.message
        });

        if (tr.toolName === 'searchEvents') {
          if (tr.result?.success) {
            events = tr.result.events || [];
            searchParams = tr.result.searchParams;  // Params bruts pour le front
            searchFilters = tr.result.filtersUsed;  // Info lisible
          } else {
            // Log l'erreur mais continue
            logger.error('searchEvents tool failed', {
              error: tr.result?.error,
              message: tr.result?.message
            });
          }
        }
      }
    }

    // Nettoyer le texte de reponse
    let responseText = result.text || '';

    // Supprimer artifacts techniques
    responseText = responseText
      .replace(/```json[\s\S]*?```/g, '')
      .replace(/\[.*?\]/g, '') // Supprime les [tags] techniques
      .replace(/\{[\s\S]*?\}/g, '') // Supprime les JSON inline
      .trim();

    // Si pas de texte mais des events, generer un message simple
    if (!responseText && events.length > 0) {
      responseText = `J'ai trouve ${events.length} activite${events.length > 1 ? 's' : ''} !`;
    }

    // Si pas de texte du tout, fallback
    if (!responseText) {
      responseText = "Dis-moi ce que tu cherches !";
    }

    return {
      success: true,
      message: responseText,
      events: events.map(e => formatEventForMobile(e)),
      searchParams,   // Params bruts pour mapping front
      searchFilters,  // Info lisible pour debug
      usage: {
        model: config.openai.defaultModel,
        tokens: result.usage?.totalTokens || 0
      }
    };

  } catch (error) {
    logger.error('Mobile AI error v2', { error: error.message, stack: error.stack });

    if (error.message?.includes('rate limit')) {
      throw new Error('Trop de requetes, patiente quelques secondes.');
    }

    throw new Error('Oups, un probleme est survenu. Reessaie !');
  }
}

/**
 * Formater un evenement pour l'affichage mobile
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

export default { generateMobileResponse };
