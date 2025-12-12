/**
 * Schema du profil utilisateur intelligent
 *
 * Sépare:
 * - currentSearch: données de recherche en cours (éphémère)
 * - preferences: préférences apprises (persistant)
 * - insights: statistiques d'usage (persistant)
 */

import { z } from 'zod';

/**
 * Schema pour la recherche en cours (éphémère)
 */
export const currentSearchSchema = z.object({
  groupType: z.enum(['solo', 'couple', 'family', 'friends']).optional(),
  age: z.number().min(1).max(120).optional(),
  location: z.object({
    city: z.string().min(1),
    radius: z.number().min(1).max(100).optional().default(20),
    coordinates: z.array(z.number()).length(2).optional()
  }).optional(),
  dates: z.object({
    type: z.enum(['thisWeekend', 'nextWeekend', 'specific', 'flexible']),
    specificDates: z.array(z.string()).optional()
  }).optional(),
  activityType: z.enum(['sport', 'culture', 'gastronomie', 'nature', 'detente', 'multi']).optional(),
  budgetMax: z.number().min(0).optional(),
  childrenAges: z.array(z.number()).optional()
});

/**
 * Schema pour les préférences utilisateur (persistant)
 */
export const preferencesSchema = z.object({
  // Ce que l'utilisateur aime
  likes: z.array(z.string()).optional().default([]),
  // Ce que l'utilisateur n'aime pas
  dislikes: z.array(z.string()).optional().default([]),
  // Restrictions alimentaires
  dietaryRestrictions: z.array(z.string()).optional().default([]),
  // Besoins d'accessibilité
  accessibility: z.array(z.string()).optional().default([]),
  // Villes favorites
  favoriteCities: z.array(z.string()).optional().default([]),
  // Catégories préférées
  favoriteCategories: z.array(z.string()).optional().default([]),
  // Budget habituel
  typicalBudget: z.number().optional(),
  // Groupe habituel
  typicalGroupType: z.enum(['solo', 'couple', 'family', 'friends']).optional()
});

/**
 * Schema pour les insights d'usage (persistant)
 */
export const insightsSchema = z.object({
  // Nombre total de recherches
  totalSearches: z.number().default(0),
  // Dernières recherches (résumé)
  recentSearches: z.array(z.object({
    query: z.string(),
    category: z.string().optional(),
    city: z.string().optional(),
    date: z.string()
  })).max(10).default([]),
  // Catégories les plus recherchées
  topCategories: z.record(z.number()).default({}),
  // Villes les plus recherchées
  topCities: z.record(z.number()).default({}),
  // Budget moyen
  averageBudget: z.number().optional(),
  // Première interaction
  firstInteraction: z.string().optional(),
  // Dernière interaction
  lastInteraction: z.string().optional()
});

/**
 * Schema complet du user_context
 */
export const userContextSchema = z.object({
  // Version du schema (pour migrations futures)
  version: z.number().default(1),
  // ID utilisateur WordPress
  userId: z.number().optional(),
  // Recherche en cours (session)
  currentSearch: currentSearchSchema.optional().default({}),
  // Préférences apprises (persistant)
  preferences: preferencesSchema.optional().default({}),
  // Insights d'usage (persistant)
  insights: insightsSchema.optional().default({})
});

/**
 * Crée un user_context vide
 */
export function createEmptyUserContext(userId = null) {
  return {
    version: 1,
    userId,
    currentSearch: {},
    preferences: {
      likes: [],
      dislikes: [],
      dietaryRestrictions: [],
      accessibility: [],
      favoriteCities: [],
      favoriteCategories: []
    },
    insights: {
      totalSearches: 0,
      recentSearches: [],
      topCategories: {},
      topCities: {}
    }
  };
}

/**
 * Fusionne un nouveau context avec un existant
 * Priorité: nouveau > existant
 */
export function mergeUserContext(existing, updates) {
  return {
    version: updates.version || existing.version || 1,
    userId: updates.userId || existing.userId,
    currentSearch: {
      ...existing.currentSearch,
      ...updates.currentSearch
    },
    preferences: {
      likes: [...new Set([...(existing.preferences?.likes || []), ...(updates.preferences?.likes || [])])],
      dislikes: [...new Set([...(existing.preferences?.dislikes || []), ...(updates.preferences?.dislikes || [])])],
      dietaryRestrictions: [...new Set([...(existing.preferences?.dietaryRestrictions || []), ...(updates.preferences?.dietaryRestrictions || [])])],
      accessibility: [...new Set([...(existing.preferences?.accessibility || []), ...(updates.preferences?.accessibility || [])])],
      favoriteCities: [...new Set([...(existing.preferences?.favoriteCities || []), ...(updates.preferences?.favoriteCities || [])])],
      favoriteCategories: [...new Set([...(existing.preferences?.favoriteCategories || []), ...(updates.preferences?.favoriteCategories || [])])],
      typicalBudget: updates.preferences?.typicalBudget || existing.preferences?.typicalBudget,
      typicalGroupType: updates.preferences?.typicalGroupType || existing.preferences?.typicalGroupType
    },
    insights: {
      totalSearches: (existing.insights?.totalSearches || 0) + (updates.insights?.totalSearches || 0),
      recentSearches: [
        ...(updates.insights?.recentSearches || []),
        ...(existing.insights?.recentSearches || [])
      ].slice(0, 10),
      topCategories: mergeCounters(existing.insights?.topCategories, updates.insights?.topCategories),
      topCities: mergeCounters(existing.insights?.topCities, updates.insights?.topCities),
      averageBudget: updates.insights?.averageBudget || existing.insights?.averageBudget,
      firstInteraction: existing.insights?.firstInteraction || updates.insights?.firstInteraction,
      lastInteraction: updates.insights?.lastInteraction || new Date().toISOString()
    }
  };
}

/**
 * Fusionne des compteurs (pour topCategories, topCities)
 */
function mergeCounters(existing = {}, updates = {}) {
  const result = { ...existing };
  for (const [key, count] of Object.entries(updates)) {
    result[key] = (result[key] || 0) + count;
  }
  return result;
}

/**
 * Extrait un résumé compact du profil pour le prompt
 * (économise des tokens)
 */
export function getProfileSummary(userContext) {
  const parts = [];

  // Préférences importantes
  if (userContext.preferences?.likes?.length > 0) {
    parts.push(`Aime: ${userContext.preferences.likes.slice(0, 3).join(', ')}`);
  }
  if (userContext.preferences?.dislikes?.length > 0) {
    parts.push(`N'aime pas: ${userContext.preferences.dislikes.slice(0, 3).join(', ')}`);
  }
  if (userContext.preferences?.favoriteCategories?.length > 0) {
    parts.push(`Catégories favorites: ${userContext.preferences.favoriteCategories.slice(0, 2).join(', ')}`);
  }
  if (userContext.preferences?.typicalBudget) {
    parts.push(`Budget habituel: ${userContext.preferences.typicalBudget}€`);
  }
  if (userContext.preferences?.typicalGroupType) {
    parts.push(`Sort souvent: ${userContext.preferences.typicalGroupType}`);
  }

  return parts.length > 0 ? parts.join(' | ') : null;
}

export default {
  userContextSchema,
  currentSearchSchema,
  preferencesSchema,
  insightsSchema,
  createEmptyUserContext,
  mergeUserContext,
  getProfileSummary
};
