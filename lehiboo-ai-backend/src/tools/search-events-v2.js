/**
 * Tool: searchEvents v2
 *
 * Version simplifiee - L'IA peut chercher immediatement avec peu d'infos
 * Tous les parametres sont optionnels avec des valeurs par defaut intelligentes
 *
 * ALIGNEMENT API WORDPRESS:
 * =========================
 * L'API WordPress attend ces parametres:
 * - city, radius, lat, lng
 * - category (slug): sport, culture, gastronomie, nature, detente
 * - thematique (slug): thematiques LeHiboo specifiques
 * - tags (array): mots-cles
 * - maxPrice, freeOnly
 * - startDate, endDate (YYYY-MM-DD)
 * - indoor, outdoor, familyFriendly
 * - limit, sortBy
 */

import { z } from 'zod';
import fetch from 'node-fetch';
import config from '../config/index.js';
import logger from '../utils/logger.js';

// Valeurs par defaut
const DEFAULTS = {
  city: 'Valenciennes',
  radius: 30,
  maxPrice: 500, // Pas de limite reelle
  datesType: 'flexible'
};

/**
 * Schema Zod SIMPLIFIE - tout est optionnel
 */
export const searchEventsSchemaV2 = z.object({
  // HYBRID SEARCH: Recherche par mot-cle dans titre et contenu
  keyword: z.string().optional(),

  // Filtres de base (tous optionnels)
  city: z.string().optional(),
  anyLocation: z.boolean().optional(), // true = ignorer le filtre ville (recherche partout)
  radius: z.number().min(5).max(100).optional(),

  // Coordonnees GPS (pour calcul distance)
  lat: z.number().optional(),
  lng: z.number().optional(),

  // Categorie (slug WordPress: sport, culture, gastronomie, nature, detente)
  category: z.string().optional(),

  // Thematique LeHiboo (slug specifique)
  thematique: z.string().optional(),

  // Type de groupe (info pour l'IA, pas envoye a WordPress)
  groupType: z.enum(['solo', 'couple', 'family', 'friends']).optional(),

  // Tags specifiques (escape game, spa, randonnee, etc.)
  tags: z.array(z.string()).optional(),

  // Dates
  dates: z.enum(['today', 'tomorrow', 'thisWeekend', 'nextWeekend', 'thisWeek', 'nextWeek', 'thisMonth', 'flexible']).optional(),
  specificDate: z.string().optional(), // Format YYYY-MM-DD

  // Budget
  maxPrice: z.number().min(0).optional(),
  freeOnly: z.boolean().optional(),

  // Autres filtres
  indoor: z.boolean().optional(),
  outdoor: z.boolean().optional(),
  familyFriendly: z.boolean().optional(),

  // Tri et limite (max 50 pour carrousel)
  limit: z.number().min(1).max(50).optional(),
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

  logger.info('Calling WordPress Events API v2', {
    url,
    params: searchParams,
    hasApiKey: !!config.wordpress.apiKey,
    apiKeyPrefix: config.wordpress.apiKey?.substring(0, 10) + '...'
  });

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${config.wordpress.apiKey}`
      },
      body: JSON.stringify(searchParams)
    });

    const responseText = await response.text();

    if (!response.ok) {
      logger.error('WordPress API error response', {
        status: response.status,
        statusText: response.statusText,
        body: responseText
      });
      throw new Error(`WordPress API returned ${response.status}: ${responseText}`);
    }

    const data = JSON.parse(responseText);
    logger.info('WordPress API success', {
      eventsCount: data.events?.length || 0,
      totalFound: data.totalFound
    });

    return data;
  } catch (error) {
    logger.error('WordPress API call failed', {
      error: error.message,
      url,
      stack: error.stack
    });
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
  if (filters.maxPrice && event.price <= filters.maxPrice) {
    score += 0.2;
    if (event.price === 0) {
      reasons.push('Gratuit');
    } else if (event.price < filters.maxPrice * 0.5) {
      reasons.push(`Bon prix (${event.price}€)`);
    }
  }

  // Categorie (nouveau format WordPress)
  const eventCat = event.category?.slug || event.category;
  if (filters.category && eventCat === filters.category) {
    score += 0.15;
    reasons.push(`Categorie ${event.category?.name || filters.category}`);
  }

  // Thematique LeHiboo
  if (filters.thematique && event.thematiques?.length > 0) {
    const match = event.thematiques.find(t => t.slug === filters.thematique);
    if (match) {
      score += 0.15;
      reasons.push(`Thematique ${match.name}`);
    }
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

  // Distance
  if (event.location?.distance_km) {
    if (event.location.distance_km <= 10) {
      score += 0.1;
      reasons.push(`Proche (${event.location.distance_km}km)`);
    } else if (event.location.distance_km <= 20) {
      score += 0.05;
    }
  }

  // Note
  const rating = event.rating?.average || event.rating;
  if (rating >= 4.5) {
    score += 0.1;
    reasons.push(`Tres bien note (${rating}/5)`);
  } else if (rating >= 4.0) {
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

    // Si anyLocation=true, on ne met pas de ville par defaut
    const useAnyLocation = validated.anyLocation === true;

    const filters = {
      keyword: validated.keyword,
      city: useAnyLocation ? null : (validated.city || DEFAULTS.city),
      anyLocation: useAnyLocation,
      radius: validated.radius || DEFAULTS.radius,
      lat: validated.lat,
      lng: validated.lng,
      category: validated.category,
      thematique: validated.thematique,
      groupType: validated.groupType,
      maxPrice: validated.freeOnly ? 0 : (validated.maxPrice || DEFAULTS.maxPrice),
      freeOnly: validated.freeOnly,
      tags: validated.tags || [],
      dates: validated.dates || DEFAULTS.datesType,
      indoor: validated.indoor,
      outdoor: validated.outdoor,
      familyFriendly: validated.familyFriendly,
      limit: validated.limit || 10,
      sortBy: validated.sortBy || 'relevance'
    };

    // Convertir dates
    const dateRange = convertDatesToRange(filters.dates, validated.specificDate);

    // Preparer params pour WordPress (alignes avec l'API)
    const searchParams = {
      keyword: filters.keyword,
      city: filters.city,  // Sera null si anyLocation=true
      anyLocation: filters.anyLocation || undefined,
      radius: filters.radius,
      lat: filters.lat,
      lng: filters.lng,
      startDate: dateRange.startDate,
      endDate: dateRange.endDate,
      maxPrice: filters.freeOnly ? undefined : filters.maxPrice,
      freeOnly: filters.freeOnly,
      category: filters.category,
      thematique: filters.thematique,
      tags: filters.tags.length > 0 ? filters.tags : undefined,
      indoor: filters.indoor,
      outdoor: filters.outdoor,
      familyFriendly: filters.familyFriendly,
      limit: filters.limit,
      sortBy: filters.sortBy
    };

    // Nettoyer les undefined ET null (WordPress n'accepte pas null pour city)
    Object.keys(searchParams).forEach(key => {
      if (searchParams[key] === undefined || searchParams[key] === null) {
        delete searchParams[key];
      }
    });

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
    const locationInfo = filters.anyLocation ? 'dans les Hauts-de-France' : `a ${filters.city}`;
    if (eventsWithScores.length === 0) {
      message = `Pas d'activite trouvee ${locationInfo}. Essaie d'elargir ta recherche !`;
    } else {
      message = `${eventsWithScores.length} activite${eventsWithScores.length > 1 ? 's' : ''} trouvee${eventsWithScores.length > 1 ? 's' : ''} ${locationInfo}`;
    }

    return {
      success: true,
      events: eventsWithScores,
      totalFound: apiResponse.totalFound || eventsWithScores.length,
      // Params bruts pour le front (mapping direct vers filtres)
      searchParams: {
        keyword: filters.keyword || null,
        city: filters.city,
        anyLocation: filters.anyLocation || false,
        radius: filters.radius,
        category: filters.category || null,
        thematique: filters.thematique || null,
        tags: filters.tags.length > 0 ? filters.tags : null,
        dates: filters.dates,  // Enum: today, tomorrow, thisWeekend, etc.
        startDate: dateRange.startDate,
        endDate: dateRange.endDate,
        maxPrice: filters.maxPrice === DEFAULTS.maxPrice ? null : filters.maxPrice,
        freeOnly: filters.freeOnly || false,
        indoor: filters.indoor || null,
        outdoor: filters.outdoor || null,
        familyFriendly: filters.familyFriendly || null,
        groupType: filters.groupType || null,
        sortBy: filters.sortBy
      },
      // Info lisible pour debug/display
      filtersUsed: {
        keyword: filters.keyword || null,
        city: filters.anyLocation ? 'partout' : filters.city,
        anyLocation: filters.anyLocation || false,
        radius: filters.radius,
        dates: `${dateRange.startDate} → ${dateRange.endDate}`,
        category: filters.category || null,
        thematique: filters.thematique || null,
        maxPrice: filters.maxPrice === DEFAULTS.maxPrice ? 'illimite' : `${filters.maxPrice}€`,
        freeOnly: filters.freeOnly || false,
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

PARAMETRES DISPONIBLES:
- keyword: Recherche par nom/mot-cle dans le TITRE (ex: "Escape Game", "Laser Game")
- city: Ville (defaut: Valenciennes)
- anyLocation: true pour chercher PARTOUT (ignore le filtre ville). Utilise si "pas a X", "partout", "n'importe ou"

RECHERCHE PAR PROXIMITE (GPS):
- lat: Latitude GPS de l'utilisateur (ex: 50.6292)
- lng: Longitude GPS de l'utilisateur (ex: 3.0573)
- radius: Rayon de recherche en km (5-100, defaut: 30)
→ Utilise ces params quand l'utilisateur dit "autour de moi", "pres de moi", "a X km", "nearby"

- category: Slug categorie (sport, culture, gastronomie, nature, detente)
- thematique: Slug thematique LeHiboo specifique
- tags: Mots-cles pour filtrer par taxonomie ["spa", "randonnee", etc.]
- dates: today, tomorrow, thisWeekend, nextWeekend, thisWeek, nextWeek, thisMonth, flexible
- maxPrice: Budget max en euros
- freeOnly: true pour uniquement gratuit
- indoor/outdoor: true/false
- familyFriendly: true pour famille
- limit: Nombre de resultats (defaut: 10, max: 50) - NE PAS SPECIFIER sauf demande explicite
- sortBy: relevance, price, date, distance

IMPORTANT - RECHERCHE PAR NOM:
Quand l'utilisateur cherche une activite par son nom (ex: "Escape Game", "Laser Game"), utilise le parametre "keyword" pour chercher dans le TITRE.
Le parametre "tags" sert a filtrer par taxonomie, pas pour la recherche textuelle.

IMPORTANT - SUPPRESSION DE FILTRES:
- Si l'utilisateur dit "pas a Lille", "partout", "n'importe ou", "toute la region" → utilise { anyLocation: true }
- Cela SUPPRIME le filtre de ville par defaut

EXEMPLES:
1. "escape game a Lille" → { city: "Lille", keyword: "escape game" }
2. "escape game" (sans ville) → { keyword: "escape game" } (cherche a Valenciennes par defaut)
3. "escape game partout" → { keyword: "escape game", anyLocation: true }
4. "pas a Lille" → { anyLocation: true }
5. "sortie en couple ce weekend" → { groupType: "couple", dates: "thisWeekend" }
6. "resto pas cher" → { category: "gastronomie", maxPrice: 30 }
7. "activite gratuite en famille" → { freeOnly: true, familyFriendly: true }
8. "quoi faire ?" → {} (utilise les valeurs par defaut)
9. "autour de moi" (avec GPS) → { lat: 50.62, lng: 3.05, radius: 30 }
10. "spa a moins de 10km" (avec GPS) → { lat: 50.62, lng: 3.05, radius: 10, keyword: "spa" }`,
  parameters: searchEventsSchemaV2,
  execute: searchEventsV2
};

export default searchEventsToolV2;
