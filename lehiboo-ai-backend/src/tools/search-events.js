/**
 * Tool: searchEvents
 *
 * Cherche des événements dans WordPress via API
 * Filtre par budget STRICT, dates, âge, localisation, catégorie
 * Calcule match score avec raisons
 *
 * LE TOOL LE PLUS IMPORTANT - C'est ici que la magie opère!
 */

import { z } from 'zod';
import fetch from 'node-fetch';
import config from '../config/index.js';
import logger from '../utils/logger.js';

/**
 * Schema Zod pour validation des inputs
 */
export const searchEventsSchema = z.object({
  userProfile: z.object({
    groupType: z.enum(['solo', 'couple', 'family', 'friends']),
    age: z.number().min(1).max(120),
    location: z.object({
      city: z.string().min(1),
      radius: z.number().min(1).max(100).optional().default(20)
    }),
    dates: z.object({
      type: z.enum(['thisWeekend', 'nextWeekend', 'specific', 'flexible']),
      specificDates: z.array(z.string()).optional()
    }),
    activityType: z.enum(['sport', 'culture', 'gastronomie', 'nature', 'detente', 'multi']),
    budgetMax: z.number().min(0)
  }),
  filters: z.object({
    maxPrice: z.number().min(0).optional(), // STRICT from budgetMax
    minPrice: z.number().min(0).optional(),
    indoor: z.boolean().optional(),
    timeOfDay: z.enum(['morning', 'afternoon', 'evening']).optional(),
    difficulty: z.enum(['easy', 'medium', 'hard']).optional(),
    tags: z.array(z.string()).optional()
  }).optional(),
  intent: z.enum(['search', 'compare', 'recommend']).optional().default('search'),
  limit: z.number().min(1).max(20).optional().default(5),
  sortBy: z.enum(['relevance', 'price', 'rating', 'distance']).optional().default('relevance')
});

/**
 * Convertit type de dates en range de dates concrètes
 */
function convertDatesToRange(dates) {
  const today = new Date();
  const dayOfWeek = today.getDay(); // 0 = dimanche, 6 = samedi

  if (dates.type === 'thisWeekend') {
    // Prochain samedi/dimanche
    const daysUntilSaturday = (6 - dayOfWeek + 7) % 7 || 7;
    const saturday = new Date(today);
    saturday.setDate(today.getDate() + daysUntilSaturday);

    const sunday = new Date(saturday);
    sunday.setDate(saturday.getDate() + 1);

    return {
      startDate: saturday.toISOString().split('T')[0],
      endDate: sunday.toISOString().split('T')[0]
    };
  }

  if (dates.type === 'nextWeekend') {
    // Weekend suivant (dans 7-14 jours)
    const daysUntilSaturday = (6 - dayOfWeek + 7) % 7 || 7;
    const saturday = new Date(today);
    saturday.setDate(today.getDate() + daysUntilSaturday + 7);

    const sunday = new Date(saturday);
    sunday.setDate(saturday.getDate() + 1);

    return {
      startDate: saturday.toISOString().split('T')[0],
      endDate: sunday.toISOString().split('T')[0]
    };
  }

  if (dates.type === 'specific' && dates.specificDates && dates.specificDates.length > 0) {
    const sortedDates = [...dates.specificDates].sort();
    return {
      startDate: sortedDates[0],
      endDate: sortedDates[sortedDates.length - 1]
    };
  }

  // Flexible: prochains 30 jours
  const endDate = new Date(today);
  endDate.setDate(today.getDate() + 30);

  return {
    startDate: today.toISOString().split('T')[0],
    endDate: endDate.toISOString().split('T')[0]
  };
}

/**
 * Appelle l'API WordPress pour chercher des événements
 */
async function callWordPressAPI(searchParams) {
  const url = `${config.wordpress.apiUrl}/lehiboo/v1/events/search`;

  logger.info('Calling WordPress Events API', { url, params: searchParams });

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

    const data = await response.json();
    return data;

  } catch (error) {
    logger.error('WordPress API call failed', { error: error.message, url });
    throw error;
  }
}

/**
 * Calcule le match score entre un événement et le profil utilisateur
 */
function calculateMatchScore(event, userProfile) {
  let score = 0;
  const reasons = [];

  // 1. Budget (40% weight) - LE PLUS IMPORTANT
  if (event.price <= userProfile.budgetMax) {
    score += 0.4;
    if (event.price < userProfile.budgetMax * 0.7) {
      reasons.push(`Excellent rapport qualité/prix (${event.price}€ bien sous votre budget de ${userProfile.budgetMax}€)`);
    } else {
      reasons.push(`Dans votre budget (${event.price}€ ≤ ${userProfile.budgetMax}€)`);
    }
  } else {
    // Ne devrait jamais arriver si filtrage API correct
    return { matchScore: 0, matchReasons: ['Prix au-dessus de votre budget'] };
  }

  // 2. Catégorie d'activité (30% weight)
  if (event.category === userProfile.activityType || userProfile.activityType === 'multi') {
    score += 0.3;
    const categoryLabels = {
      sport: 'sportive',
      culture: 'culturelle',
      gastronomie: 'gastronomique',
      nature: 'nature',
      detente: 'détente'
    };
    reasons.push(`Activité ${categoryLabels[event.category]} comme demandé`);
  }

  // 3. Disponibilité dates (15% weight)
  // Simplifié: on assume que l'API a déjà filtré par dates
  score += 0.15;
  reasons.push(`Disponible aux dates souhaitées`);

  // 4. Restrictions d'âge (10% weight)
  if (event.ageRestriction) {
    if (!event.ageRestriction.min || userProfile.age >= event.ageRestriction.min) {
      score += 0.1;
      if (event.ageRestriction.min >= 18) {
        reasons.push(`Activité adulte (18+) adaptée à votre âge`);
      }
    } else {
      return { matchScore: 0, matchReasons: [`Restriction d'âge: minimum ${event.ageRestriction.min} ans`] };
    }
  } else {
    score += 0.1;
  }

  // 5. Note/avis (5% weight)
  if (event.rating >= 4.5) {
    score += 0.05;
    reasons.push(`Note excellente: ${event.rating}/5 (${event.reviews} avis)`);
  } else if (event.rating >= 4.0) {
    score += 0.03;
    reasons.push(`Bonne note: ${event.rating}/5`);
  }

  return {
    matchScore: Math.min(score, 1), // Cap à 1.0
    matchReasons: reasons
  };
}

/**
 * Enrichit les événements avec expertise Hedwige (conseils, astuces)
 * Simule la connaissance locale - en production, vient d'une vraie base de données
 */
function enrichWithExpertise(event, userProfile) {
  // Simuler conseils d'experte selon persona
  const expertTips = [];

  if (userProfile.groupType === 'couple') {
    expertTips.push("Parfait pour un moment à deux");
    if (event.category === 'culture') {
      expertTips.push("Ambiance intimiste idéale pour les couples");
    }
  }

  if (userProfile.groupType === 'family') {
    expertTips.push("Activité adaptée à toute la famille");
    if (event.duration) {
      const hours = parseFloat(event.duration);
      if (hours <= 2) {
        expertTips.push("Durée idéale pour les enfants (pas trop long)");
      }
    }
  }

  if (userProfile.groupType === 'friends') {
    expertTips.push("Activité conviviale entre amis");
    if (event.groupSize && event.groupSize.max >= 6) {
      expertTips.push("Accepte les groupes - parfait pour vous !");
    }
  }

  // Conseils timing (simulé - en prod, vient de vraie data)
  if (event.category === 'culture') {
    expertTips.push("Conseil: Arrivez 15min avant pour profiter du lieu");
  }

  return {
    ...event,
    expertTips
  };
}

/**
 * Tool principal: searchEvents
 */
export async function searchEvents(input) {
  const startTime = Date.now();

  try {
    // Valider l'input
    const validated = searchEventsSchema.parse(input);
    const { userProfile, filters = {}, intent, limit, sortBy } = validated;

    // Convertir dates en range
    const dateRange = convertDatesToRange(userProfile.dates);

    // Préparer paramètres pour WordPress API
    const searchParams = {
      city: userProfile.location.city,
      radius: userProfile.location.radius || 20,
      startDate: dateRange.startDate,
      endDate: dateRange.endDate,
      maxPrice: filters.maxPrice || userProfile.budgetMax, // STRICT
      minPrice: filters.minPrice,
      category: userProfile.activityType !== 'multi' ? userProfile.activityType : undefined,
      minAge: userProfile.age, // Pour filtrer restrictions d'âge
      indoor: filters.indoor,
      tags: filters.tags,
      limit: intent === 'compare' ? 6 : intent === 'recommend' ? 8 : limit,
      sortBy
    };

    logger.info('Searching events', { userProfile, searchParams });

    // Appeler WordPress API
    const apiResponse = await callWordPressAPI(searchParams);

    if (!apiResponse.success) {
      throw new Error(apiResponse.error || 'WordPress API returned error');
    }

    // Calculer match scores pour chaque événement
    const eventsWithScores = apiResponse.events.map(event => {
      const { matchScore, matchReasons } = calculateMatchScore(event, userProfile);
      const enrichedEvent = enrichWithExpertise(event, userProfile);

      return {
        ...enrichedEvent,
        matchScore,
        matchReasons
      };
    });

    // Trier par match score (si sortBy = relevance)
    if (sortBy === 'relevance') {
      eventsWithScores.sort((a, b) => b.matchScore - a.matchScore);
    }

    // Filtrer events avec score trop bas
    const filteredEvents = eventsWithScores.filter(e => e.matchScore >= 0.5);

    const searchTime = Date.now() - startTime;

    // Construire message de résumé
    let message = '';
    if (filteredEvents.length === 0) {
      message = `Aucune activité trouvée à ${userProfile.location.city} avec ces critères.
Suggestions: augmenter le budget, élargir le rayon, ou choisir d'autres dates.`;
    } else {
      const categoryLabel = {
        sport: 'sportives',
        culture: 'culturelles',
        gastronomie: 'gastronomiques',
        nature: 'nature',
        detente: 'détente',
        multi: 'variées'
      }[userProfile.activityType];

      message = `${filteredEvents.length} activité${filteredEvents.length > 1 ? 's' : ''} ${categoryLabel} trouvée${filteredEvents.length > 1 ? 's' : ''} à ${userProfile.location.city}`;
    }

    return {
      success: true,
      events: filteredEvents,
      totalFound: apiResponse.totalFound || filteredEvents.length,
      searchMetrics: {
        searchTime,
        filtersApplied: [
          `maxPrice: ${searchParams.maxPrice}€`,
          `city: ${searchParams.city}`,
          `dates: ${searchParams.startDate} → ${searchParams.endDate}`,
          searchParams.category && `category: ${searchParams.category}`
        ].filter(Boolean)
      },
      message
    };

  } catch (error) {
    logger.error('searchEvents failed', { error: error.message, stack: error.stack });

    // Erreur de validation Zod
    if (error.name === 'ZodError') {
      return {
        success: false,
        error: 'Profil utilisateur incomplet ou invalide',
        details: error.errors.map(e => `${e.path.join('.')}: ${e.message}`),
        message: 'Impossible de lancer la recherche: profil utilisateur incomplet'
      };
    }

    // Erreur API WordPress
    if (error.message.includes('WordPress API')) {
      return {
        success: false,
        error: 'Erreur de connexion à la base de données d\'événements',
        message: 'Désolé, je ne peux pas accéder aux événements pour le moment. Réessayez dans quelques instants.'
      };
    }

    // Autre erreur
    return {
      success: false,
      error: error.message,
      message: 'Une erreur est survenue lors de la recherche'
    };
  }
}

/**
 * Définition du tool pour AI SDK
 */
export const searchEventsTool = {
  description: `Cherche des événements dans la base WordPress.
UTILISER IMMÉDIATEMENT quand le profil est complet (6/6 infos collectées).

Filtre STRICT par:
- Budget MAX (jamais au-dessus!)
- Dates disponibilité
- Âge minimum requis
- Localisation (ville + rayon)
- Catégorie activité

Retourne 3-8 événements avec:
- Match score (0-1)
- Raisons du match
- Conseils d'experte Hedwige
- Détails complets (prix, horaires, lieu, etc.)

Le budget est une LIMITE STRICTE, pas une suggestion.`,
  parameters: searchEventsSchema,
  execute: searchEvents
};

export default searchEventsTool;
