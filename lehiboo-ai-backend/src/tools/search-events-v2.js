/**
 * Tool: searchEvents v2
 *
 * Version simplifiee - L'IA peut chercher immediatement avec peu d'infos
 * Tous les parametres sont optionnels avec des valeurs par defaut intelligentes
 */

import { z } from 'zod';
import fetch from 'node-fetch';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Valeurs par defaut
const DEFAULTS = {
  city: 'Valenciennes',
  radius: 30,
  budgetMax: 500, // Pas de limite reelle
  groupType: 'solo',
  activityType: 'multi',
  datesType: 'flexible'
};

/**
 * Schema Zod SIMPLIFIE - tout est optionnel
 */
export const searchEventsSchemaV2 = z.object({
  // Filtres de base (tous optionnels)
  city: z.string().optional(),
  radius: z.number().min(5).max(100).optional(),

  // Type de groupe
  groupType: z.enum(['solo', 'couple', 'family', 'friends']).optional(),

  // Type d'activite
  activityType: z.enum(['sport', 'culture', 'gastronomie', 'nature', 'detente', 'multi']).optional(),

  // Tags specifiques (escape game, spa, randonnee, etc.)
  tags: z.array(z.string()).optional(),

  // Dates
  dates: z.enum(['today', 'tomorrow', 'thisWeekend', 'nextWeekend', 'thisWeek', 'nextWeek', 'thisMonth', 'flexible']).optional(),
  specificDate: z.string().optional(), // Format YYYY-MM-DD

  // Budget
  budgetMax: z.number().min(0).optional(),
  freeOnly: z.boolean().optional(),

  // Autres filtres
  indoor: z.boolean().optional(),
  outdoor: z.boolean().optional(),

  // Tri et limite
  limit: z.number().min(1).max(20).optional(),
  sortBy: z.enum(['relevance', 'price', 'rating', 'distance', 'date']).optional()
});

/**
 * Convertit les dates en range concret
 */
function convertDatesToRange(datesType, specificDate) {
  const today = new Date();
  const dayOfWeek = today.getDay();

  switch (datesType) {
    case 'today':
      return {
        startDate: today.toISOString().split('T')[0],
        endDate: today.toISOString().split('T')[0]
      };

    case 'tomorrow':
      const tomorrow = new Date(today);
      tomorrow.setDate(today.getDate() + 1);
      return {
        startDate: tomorrow.toISOString().split('T')[0],
        endDate: tomorrow.toISOString().split('T')[0]
      };

    case 'thisWeekend':
      const daysUntilSat = (6 - dayOfWeek + 7) % 7 || 7;
      const saturday = new Date(today);
      saturday.setDate(today.getDate() + daysUntilSat);
      const sunday = new Date(saturday);
      sunday.setDate(saturday.getDate() + 1);
      return {
        startDate: saturday.toISOString().split('T')[0],
        endDate: sunday.toISOString().split('T')[0]
      };

    case 'nextWeekend':
      const daysUntilNextSat = (6 - dayOfWeek + 7) % 7 + 7;
      const nextSaturday = new Date(today);
      nextSaturday.setDate(today.getDate() + daysUntilNextSat);
      const nextSunday = new Date(nextSaturday);
      nextSunday.setDate(nextSaturday.getDate() + 1);
      return {
        startDate: nextSaturday.toISOString().split('T')[0],
        endDate: nextSunday.toISOString().split('T')[0]
      };

    case 'thisWeek':
      const endOfWeek = new Date(today);
      endOfWeek.setDate(today.getDate() + (7 - dayOfWeek));
      return {
        startDate: today.toISOString().split('T')[0],
        endDate: endOfWeek.toISOString().split('T')[0]
      };

    case 'nextWeek':
      const startNextWeek = new Date(today);
      startNextWeek.setDate(today.getDate() + (7 - dayOfWeek) + 1);
      const endNextWeek = new Date(startNextWeek);
      endNextWeek.setDate(startNextWeek.getDate() + 6);
      return {
        startDate: startNextWeek.toISOString().split('T')[0],
        endDate: endNextWeek.toISOString().split('T')[0]
      };

    case 'thisMonth':
      const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      return {
        startDate: today.toISOString().split('T')[0],
        endDate: endOfMonth.toISOString().split('T')[0]
      };

    case 'flexible':
    default:
      // 30 prochains jours
      const endDate = new Date(today);
      endDate.setDate(today.getDate() + 30);
      return {
        startDate: today.toISOString().split('T')[0],
        endDate: endDate.toISOString().split('T')[0]
      };
  }
}

/**
 * Appelle l'API WordPress
 */
async function callWordPressAPI(searchParams) {
  const url = `${config.wordpress.apiUrl}/lehiboo/v1/events/search`;

  logger.info('Calling WordPress Events API v2', { url, params: searchParams });

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${config.wordpress.apiKey}`
      },
      body: JSON.stringify(searchParams)
    });

    if (!response.ok) {
      throw new Error(`WordPress API returned ${response.status}: ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    logger.error('WordPress API call failed', { error: error.message, url });
    throw error;
  }
}

/**
 * Calcule un score de match simplifie
 */
function calculateMatchScore(event, filters) {
  let score = 0.5; // Score de base
  const reasons = [];

  // Prix
  if (filters.budgetMax && event.price <= filters.budgetMax) {
    score += 0.2;
    if (event.price === 0) {
      reasons.push('Gratuit');
    } else if (event.price < filters.budgetMax * 0.5) {
      reasons.push(`Bon prix (${event.price}€)`);
    }
  }

  // Categorie
  if (filters.activityType && filters.activityType !== 'multi' && event.category === filters.activityType) {
    score += 0.15;
  }

  // Tags
  if (filters.tags && filters.tags.length > 0) {
    const eventTags = event.tags || [];
    const matchingTags = filters.tags.filter(t =>
      eventTags.some(et => et.toLowerCase().includes(t.toLowerCase()))
    );
    if (matchingTags.length > 0) {
      score += 0.1 * matchingTags.length;
      reasons.push(`Correspond a: ${matchingTags.join(', ')}`);
    }
  }

  // Note
  if (event.rating >= 4.5) {
    score += 0.1;
    reasons.push(`Tres bien note (${event.rating}/5)`);
  } else if (event.rating >= 4.0) {
    score += 0.05;
  }

  return {
    matchScore: Math.min(score, 1),
    matchReasons: reasons
  };
}

/**
 * Tool principal: searchEvents v2
 */
export async function searchEventsV2(input) {
  const startTime = Date.now();

  try {
    // Valider et appliquer les defaults
    const validated = searchEventsSchemaV2.parse(input);

    const filters = {
      city: validated.city || DEFAULTS.city,
      radius: validated.radius || DEFAULTS.radius,
      groupType: validated.groupType || DEFAULTS.groupType,
      activityType: validated.activityType || DEFAULTS.activityType,
      budgetMax: validated.freeOnly ? 0 : (validated.budgetMax || DEFAULTS.budgetMax),
      tags: validated.tags || [],
      dates: validated.dates || DEFAULTS.datesType,
      indoor: validated.indoor,
      outdoor: validated.outdoor,
      limit: validated.limit || 6,
      sortBy: validated.sortBy || 'relevance'
    };

    // Convertir dates
    const dateRange = convertDatesToRange(filters.dates, validated.specificDate);

    // Preparer params pour WordPress
    const searchParams = {
      city: filters.city,
      radius: filters.radius,
      startDate: dateRange.startDate,
      endDate: dateRange.endDate,
      maxPrice: filters.budgetMax,
      category: filters.activityType !== 'multi' ? filters.activityType : undefined,
      tags: filters.tags.length > 0 ? filters.tags : undefined,
      indoor: filters.indoor,
      limit: filters.limit,
      sortBy: filters.sortBy
    };

    logger.info('Searching events v2', { filters, searchParams });

    // Appeler WordPress
    const apiResponse = await callWordPressAPI(searchParams);

    if (!apiResponse.success) {
      throw new Error(apiResponse.error || 'WordPress API error');
    }

    // Enrichir avec scores
    const eventsWithScores = (apiResponse.events || []).map(event => {
      const { matchScore, matchReasons } = calculateMatchScore(event, filters);
      return { ...event, matchScore, matchReasons };
    });

    // Trier par score
    if (filters.sortBy === 'relevance') {
      eventsWithScores.sort((a, b) => b.matchScore - a.matchScore);
    }

    const searchTime = Date.now() - startTime;

    // Message de resultat
    let message = '';
    if (eventsWithScores.length === 0) {
      message = `Pas d'activite trouvee a ${filters.city}. Essaie d'elargir ta recherche !`;
    } else {
      message = `${eventsWithScores.length} activite${eventsWithScores.length > 1 ? 's' : ''} trouvee${eventsWithScores.length > 1 ? 's' : ''} a ${filters.city}`;
    }

    return {
      success: true,
      events: eventsWithScores,
      totalFound: apiResponse.totalFound || eventsWithScores.length,
      filtersUsed: {
        city: filters.city,
        radius: filters.radius,
        dates: `${dateRange.startDate} → ${dateRange.endDate}`,
        activityType: filters.activityType,
        budgetMax: filters.budgetMax === DEFAULTS.budgetMax ? 'illimite' : `${filters.budgetMax}€`,
        tags: filters.tags
      },
      searchTime,
      message
    };

  } catch (error) {
    logger.error('searchEventsV2 failed', { error: error.message, stack: error.stack });

    if (error.name === 'ZodError') {
      return {
        success: false,
        error: 'Parametres invalides',
        details: error.errors.map(e => `${e.path.join('.')}: ${e.message}`),
        message: 'Impossible de lancer la recherche avec ces parametres'
      };
    }

    if (error.message.includes('WordPress API')) {
      return {
        success: false,
        error: 'Erreur de connexion',
        message: 'Je ne peux pas acceder aux activites pour le moment. Reessaie dans quelques instants.'
      };
    }

    return {
      success: false,
      error: error.message,
      message: 'Une erreur est survenue'
    };
  }
}

/**
 * Definition du tool pour AI SDK
 */
export const searchEventsToolV2 = {
  description: `Cherche des activites dans les Hauts-de-France.

UTILISE CE TOOL DES QUE POSSIBLE - n'attends pas d'avoir toutes les infos !

Valeurs par defaut si non precise:
- city: Valenciennes
- radius: 30km
- dates: les 30 prochains jours
- budget: illimite
- activityType: tout

Exemples d'appels:
1. "escape game a Lille" → { city: "Lille", tags: ["escape game"] }
2. "sortie en couple ce weekend" → { groupType: "couple", dates: "thisWeekend" }
3. "resto pas cher" → { activityType: "gastronomie", budgetMax: 30 }
4. "quoi faire ?" → {} (utilise tous les defaults)`,
  parameters: searchEventsSchemaV2,
  execute: searchEventsV2
};

export default searchEventsToolV2;
