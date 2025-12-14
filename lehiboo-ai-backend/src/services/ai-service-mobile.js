/**
 * Service IA Mobile v2 - Optimisé tokens
 *
 * Optimisations appliquées:
 * - Modèle: gpt-4o-mini (16x moins cher)
 * - Historique: 4 derniers messages max
 */

import { openai } from '@ai-sdk/openai';
import { generateText } from 'ai';
import { readFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Import des tools
import { searchEventsToolV2 } from '../tools/search-events-v2.js';
import { updateUserContextTool, executeUpdateUserContext } from '../tools/update-user-context.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Charger le system prompt mobile
 */
async function loadMobilePrompt() {
  try {
    const promptPath = join(__dirname, '../prompts/system-prompt-mobile-v2.md');
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
 * Optimisé: historique limité à 4 messages
 */
function buildMessages(currentMessage, history = []) {
  const messages = [];

  // Historique limité à 4 messages (économise tokens)
  const recentHistory = history.slice(-4);
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
    const userContext = context.userContext || {};

    logger.info('Mobile AI request', {
      conversationId: context.conversationId,
      messageLength: message.length,
      historyLength: context.history?.length || 0,
      userContextKeys: Object.keys(userContext).filter(k => !k.startsWith('_'))
    });

    let systemPrompt = await loadMobilePrompt();

    // Injecter le contexte utilisateur existant dans le prompt
    if (Object.keys(userContext).filter(k => !k.startsWith('_')).length > 0) {
      const contextInfo = Object.entries(userContext)
        .filter(([k, v]) => !k.startsWith('_') && v !== undefined && v !== null)
        .map(([k, v]) => `- ${k}: ${JSON.stringify(v)}`)
        .join('\n');

      systemPrompt = `${systemPrompt}\n\n## Contexte utilisateur connu\n\nTu te souviens de ces infos sur l'utilisateur:\n${contextInfo}\n\nUtilise ces infos pour personnaliser tes reponses (ex: "Salut ${userContext.first_name || 'toi'} !").`;
    }

    const messages = buildMessages(message, context.history);

    // Variable pour stocker le contexte mis a jour
    let updatedUserContext = { ...userContext };

    // Tools avec contexte utilisateur
    const tools = {
      searchEvents: {
        description: searchEventsToolV2.description,
        parameters: searchEventsToolV2.parameters,
        execute: searchEventsToolV2.execute
      },
      updateUserContext: {
        description: updateUserContextTool.description,
        parameters: updateUserContextTool.parameters,
        execute: async (params) => {
          const result = await executeUpdateUserContext(params, updatedUserContext);
          if (result.success) {
            updatedUserContext = result.context;
          }
          return result;
        }
      }
    };

    // Appel IA
    const result = await generateText({
      model: openai(config.openai.defaultModel),
      system: systemPrompt,
      messages,
      tools,
      temperature: 0.7,
      maxTokens: 800,
      maxSteps: 3
    });

    // Extraire les events des tool results
    let events = [];
    let searchParams = null;
    let searchFilters = null;

    // Debug structure
    logger.info('AI SDK result structure', {
      hasToolResults: !!result.toolResults,
      toolResultsCount: result.toolResults?.length || 0,
      hasToolCalls: !!result.toolCalls,
      toolCallsCount: result.toolCalls?.length || 0,
      hasSteps: !!result.steps,
      stepsCount: result.steps?.length || 0,
      textLength: result.text?.length || 0
    });

    // Log tool calls
    if (result.toolCalls?.length > 0) {
      logger.info('Tool calls made', {
        calls: result.toolCalls.map(tc => ({
          name: tc.toolName,
          args: tc.args
        }))
      });
    }

    // Extraire depuis les steps (AI SDK v3+)
    if (result.steps && result.steps.length > 0) {
      for (const step of result.steps) {
        if (step.toolResults && step.toolResults.length > 0) {
          for (const tr of step.toolResults) {
            logger.info('Step tool result', {
              toolName: tr.toolName,
              resultKeys: Object.keys(tr.result || {}),
              success: tr.result?.success,
              eventsCount: tr.result?.events?.length || 0
            });

            if (tr.toolName === 'searchEvents' && tr.result?.success) {
              events = tr.result.events || [];
              searchParams = tr.result.searchParams;
              searchFilters = tr.result.filtersUsed;
              logger.info('Events extracted from step', { count: events.length });
            }
          }
        }
      }
    }

    // Fallback: extraire depuis toolResults direct
    if (events.length === 0 && result.toolResults && result.toolResults.length > 0) {
      for (const tr of result.toolResults) {
        logger.info('Direct tool result', {
          toolName: tr.toolName,
          resultKeys: Object.keys(tr.result || {}),
          success: tr.result?.success,
          eventsCount: tr.result?.events?.length || 0,
          error: tr.result?.error
        });

        if (tr.toolName === 'searchEvents') {
          if (tr.result?.success) {
            events = tr.result.events || [];
            searchParams = tr.result.searchParams;
            searchFilters = tr.result.filtersUsed;
            logger.info('Events extracted from toolResults', { count: events.length });
          } else {
            logger.error('searchEvents tool failed', {
              error: tr.result?.error,
              message: tr.result?.message
            });
          }
        }
      }
    }

    logger.info('Final extraction result', {
      eventsCount: events.length,
      hasSearchParams: !!searchParams,
      hasSearchFilters: !!searchFilters
    });

    // Nettoyer le texte de reponse
    let responseText = result.text || '';

    // Supprimer artifacts techniques
    responseText = responseText
      .replace(/```json[\s\S]*?```/g, '')
      .replace(/\[.*?\]/g, '')
      .replace(/\{[\s\S]*?\}/g, '')
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
      searchParams,
      searchFilters,
      userContext: updatedUserContext,
      usage: {
        model: config.openai.defaultModel,
        tokens: result.usage?.totalTokens || 0
      }
    };

  } catch (error) {
    logger.error('Mobile AI error', { error: error.message, stack: error.stack });

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
  const excerpt = event.excerpt || event.description;
  const shortDesc = excerpt?.substring(0, 150) + (excerpt?.length > 150 ? '...' : '') || '';

  return {
    id: event.id,
    title: event.title,
    description: shortDesc,
    image: event.imageUrl || event.thumbnailUrl || event.image || event.thumbnail,
    thumbnail: event.thumbnailUrl || event.imageUrl || event.thumbnail || event.image,
    price: event.price,
    priceLabel: event.priceDisplay || (event.price === 0 ? 'Gratuit' : `${event.price}€`),
    location: typeof event.location === 'object'
      ? event.location
      : { city: event.location || event.venue },
    date: event.dates?.display || event.dateLabel || event.date,
    startDate: event.dates?.start || null,
    endDate: event.dates?.end || null,
    category: event.category,
    thematiques: event.thematiques || [],
    tags: event.tags || [],
    matchScore: event.matchScore,
    matchReasons: event.matchReasons?.slice(0, 2) || [],
    url: event.url,
    bookingUrl: event.bookingUrl
  };
}

export default { generateMobileResponse };
