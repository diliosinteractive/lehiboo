/**
 * Service IA Mobile v3 - Smart Context & Token Optimization
 *
 * Architecture:
 * - user_context: Mémoire long terme (préférences, profil) - PERSISTANT
 * - history: 4 derniers messages - ÉPHÉMÈRE (sliding window)
 *
 * Le LLM:
 * 1. Lit user_context pour personnaliser
 * 2. Détecte nouvelles préférences dans les messages
 * 3. Retourne user_context mis à jour
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
import { updateUserPreferencesTool } from '../tools/update-user-preferences.js';
import { getProfileSummary, mergeUserContext, createEmptyUserContext } from '../tools/user-profile-schema.js';

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
 * Construire les messages pour l'IA avec Smart Context
 *
 * @param {string} currentMessage - Message actuel
 * @param {Array} history - Historique (sliding window, 4 max)
 * @param {Object} userContext - Contexte utilisateur persistant
 */
function buildMessages(currentMessage, history = [], userContext = null) {
  const messages = [];

  // OPTIMISATION: Historique limité à 4 messages (sliding window)
  const recentHistory = history.slice(-4);
  recentHistory.forEach(msg => {
    messages.push({ role: msg.role, content: msg.content });
  });

  // Construire le message avec le contexte smart
  let enrichedMessage = currentMessage;

  // Ajouter le résumé du profil si disponible (économise tokens vs JSON complet)
  if (userContext) {
    const profileSummary = getProfileSummary(userContext);
    if (profileSummary) {
      enrichedMessage = `[Profil: ${profileSummary}]\n${currentMessage}`;
    }
  }

  messages.push({ role: 'user', content: enrichedMessage });

  return messages;
}

/**
 * Generer une reponse IA pour mobile avec Smart Context
 *
 * @param {string} message - Message utilisateur
 * @param {Object} context - Contexte contenant history et user_context
 * @returns {Object} Réponse avec message, events, et user_context mis à jour
 */
export async function generateMobileResponse(message, context = {}) {
  try {
    // Initialiser ou récupérer le user_context
    let userContext = context.user_context || createEmptyUserContext(context.userId);

    logger.info('Mobile AI request v3', {
      conversationId: context.conversationId,
      messageLength: message.length,
      historyLength: context.history?.length || 0,
      hasUserContext: !!context.user_context,
      preferencesCount: userContext.preferences?.likes?.length || 0
    });

    const systemPrompt = await loadMobilePrompt();
    const messages = buildMessages(message, context.history, userContext);

    // Tools disponibles: searchEvents + updatePreferences
    const tools = {
      searchEvents: {
        description: searchEventsToolV2.description,
        parameters: searchEventsToolV2.parameters,
        execute: searchEventsToolV2.execute
      },
      updateUserPreferences: {
        description: updateUserPreferencesTool.description,
        parameters: updateUserPreferencesTool.parameters,
        execute: async (input) => {
          // Passer les préférences existantes pour merge
          return updateUserPreferencesTool.execute(input, userContext.preferences || {});
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

    // Debug complet de la structure result
    logger.info('AI SDK result structure', {
      hasToolResults: !!result.toolResults,
      toolResultsCount: result.toolResults?.length || 0,
      hasToolCalls: !!result.toolCalls,
      toolCallsCount: result.toolCalls?.length || 0,
      hasSteps: !!result.steps,
      stepsCount: result.steps?.length || 0,
      textLength: result.text?.length || 0
    });

    // Log les tool calls
    if (result.toolCalls?.length > 0) {
      logger.info('Tool calls made', {
        calls: result.toolCalls.map(tc => ({
          name: tc.toolName,
          args: tc.args
        }))
      });
    }

    // Extraire les tool results depuis les steps (AI SDK v3+)
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

    // Fallback: extraire depuis toolResults direct (anciennes versions)
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

    // Extraire les mises à jour de préférences depuis les tool results
    let updatedPreferences = null;
    const allToolResults = [
      ...(result.toolResults || []),
      ...(result.steps?.flatMap(s => s.toolResults || []) || [])
    ];

    for (const tr of allToolResults) {
      if (tr.toolName === 'updateUserPreferences' && tr.result?.success) {
        updatedPreferences = tr.result.updatedPreferences;
        logger.info('Preferences updated by AI', {
          changes: tr.result.changes
        });
      }
    }

    // Mettre à jour le userContext si des préférences ont changé
    if (updatedPreferences) {
      userContext = mergeUserContext(userContext, {
        preferences: updatedPreferences,
        insights: {
          lastInteraction: new Date().toISOString()
        }
      });
    }

    // Mettre à jour les insights (recherche effectuée)
    if (events.length > 0 && searchParams) {
      userContext.insights = {
        ...userContext.insights,
        totalSearches: (userContext.insights?.totalSearches || 0) + 1,
        lastInteraction: new Date().toISOString(),
        recentSearches: [
          {
            query: message.substring(0, 50),
            category: searchParams.activityType,
            city: searchParams.location?.city,
            date: new Date().toISOString()
          },
          ...(userContext.insights?.recentSearches || [])
        ].slice(0, 10)
      };

      // Mettre à jour topCategories
      if (searchParams.activityType) {
        userContext.insights.topCategories = {
          ...userContext.insights.topCategories,
          [searchParams.activityType]: (userContext.insights.topCategories?.[searchParams.activityType] || 0) + 1
        };
      }
    }

    logger.info('Final extraction result', {
      eventsCount: events.length,
      hasSearchParams: !!searchParams,
      hasSearchFilters: !!searchFilters,
      preferencesUpdated: !!updatedPreferences
    });

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
      searchParams,
      searchFilters,
      // IMPORTANT: Retourner le user_context mis à jour pour persistence côté mobile
      user_context: userContext,
      usage: {
        model: config.openai.defaultModel,
        tokens: result.usage?.totalTokens || 0
      }
    };

  } catch (error) {
    logger.error('Mobile AI error v3', { error: error.message, stack: error.stack });

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
  // Extraire l'excerpt depuis excerpt ou description
  const excerpt = event.excerpt || event.description;
  const shortDesc = excerpt?.substring(0, 150) + (excerpt?.length > 150 ? '...' : '') || '';

  return {
    id: event.id,
    title: event.title,
    description: shortDesc,
    // Images: WordPress retourne imageUrl/thumbnailUrl
    image: event.imageUrl || event.thumbnailUrl || event.image || event.thumbnail,
    thumbnail: event.thumbnailUrl || event.imageUrl || event.thumbnail || event.image,
    // Prix
    price: event.price,
    priceLabel: event.priceDisplay || (event.price === 0 ? 'Gratuit' : `${event.price}€`),
    // Location: objet ou string
    location: typeof event.location === 'object'
      ? event.location
      : { city: event.location || event.venue },
    // Dates
    date: event.dates?.display || event.dateLabel || event.date,
    startDate: event.dates?.start || null,
    endDate: event.dates?.end || null,
    // Catégorie et thématiques
    category: event.category,
    thematiques: event.thematiques || [],
    tags: event.tags || [],
    // Score
    matchScore: event.matchScore,
    matchReasons: event.matchReasons?.slice(0, 2) || [],
    // URLs
    url: event.url,
    bookingUrl: event.bookingUrl
  };
}

export default { generateMobileResponse };
