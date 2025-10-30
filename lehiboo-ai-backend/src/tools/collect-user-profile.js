/**
 * Tool: collectUserProfile
 *
 * Collecte et valide les informations du profil utilisateur
 * Calcule la complétude du profil (0-100%)
 * Retourne les champs manquants
 *
 * Règle 6/6: groupe, âge, localisation, dates, activité, budget
 */

import { z } from 'zod';

/**
 * Schema Zod pour validation des inputs
 */
export const collectUserProfileSchema = z.object({
  groupType: z.enum(['solo', 'couple', 'family', 'friends']).optional(),
  age: z.number().min(1).max(120).optional(),
  location: z.object({
    city: z.string().min(1),
    radius: z.number().min(1).max(100).optional().default(20),
    coordinates: z.array(z.number()).length(2).optional() // [lat, lng]
  }).optional(),
  dates: z.object({
    type: z.enum(['thisWeekend', 'nextWeekend', 'specific', 'flexible']),
    specificDates: z.array(z.string()).optional() // Format: 'YYYY-MM-DD'
  }).optional(),
  activityType: z.enum(['sport', 'culture', 'gastronomie', 'nature', 'detente', 'multi']).optional(),
  budgetMax: z.number().min(0).optional(),
  childrenAges: z.array(z.number()).optional(), // Si famille
  preferences: z.array(z.string()).optional()    // Optionnel
});

/**
 * Calcule la complétude du profil (0-100%)
 *
 * Les 6 champs obligatoires:
 * - groupType
 * - age
 * - location
 * - dates
 * - activityType
 * - budgetMax
 *
 * @param {Object} profile - Le profil utilisateur
 * @returns {number} Pourcentage de complétude (0-100)
 */
function calculateCompleteness(profile) {
  const requiredFields = [
    'groupType',
    'age',
    'location',
    'dates',
    'activityType',
    'budgetMax'
  ];

  const filledFields = requiredFields.filter(field => {
    const value = profile[field];
    if (value === undefined || value === null) return false;
    if (typeof value === 'object' && Object.keys(value).length === 0) return false;
    return true;
  });

  return Math.round((filledFields.length / requiredFields.length) * 100);
}

/**
 * Identifie les champs manquants
 *
 * @param {Object} profile - Le profil utilisateur
 * @returns {Array<string>} Liste des champs manquants
 */
function getMissingFields(profile) {
  const requiredFields = {
    groupType: 'type de groupe',
    age: 'âge',
    location: 'ville',
    dates: 'dates',
    activityType: "type d'activité",
    budgetMax: 'budget maximum'
  };

  const missing = [];

  for (const [field, label] of Object.entries(requiredFields)) {
    const value = profile[field];
    if (value === undefined || value === null) {
      missing.push(label);
    } else if (typeof value === 'object' && Object.keys(value).length === 0) {
      missing.push(label);
    }
  }

  return missing;
}

/**
 * Formate le type de groupe en français pour l'affichage
 */
function formatGroupType(groupType) {
  const labels = {
    solo: 'solo',
    couple: 'en couple',
    family: 'en famille',
    friends: 'entre amis'
  };
  return labels[groupType] || groupType;
}

/**
 * Formate le type de dates en texte lisible
 */
function formatDatesType(dates) {
  if (!dates) return null;

  const labels = {
    thisWeekend: 'ce weekend',
    nextWeekend: 'le prochain weekend',
    specific: dates.specificDates ? `le ${dates.specificDates.join(', ')}` : 'dates précises',
    flexible: 'dates flexibles'
  };

  return labels[dates.type] || dates.type;
}

/**
 * Formate le type d'activité en français
 */
function formatActivityType(activityType) {
  const labels = {
    sport: 'sportive',
    culture: 'culturelle',
    gastronomie: 'gastronomique',
    nature: 'nature',
    detente: 'détente',
    multi: 'variée'
  };
  return labels[activityType] || activityType;
}

/**
 * Génère un message de feedback intelligent
 */
function generateMessage(profile, completeness, missingFields) {
  if (completeness === 100) {
    // Profil complet !
    const summary = [
      formatGroupType(profile.groupType),
      `${profile.age} ans`,
      profile.location.city,
      formatDatesType(profile.dates),
      `activité ${formatActivityType(profile.activityType)}`,
      `budget max ${profile.budgetMax}€`
    ].join(', ');

    return `Profil complet ✓ (${summary}). Je lance la recherche !`;
  }

  if (completeness >= 67) {
    // Presque complet (4-5/6)
    return `Profil à ${completeness}%. Il manque: ${missingFields.join(', ')}`;
  }

  if (completeness >= 34) {
    // Mi-chemin (2-3/6)
    return `Profil à ${completeness}%. Encore besoin de: ${missingFields.join(', ')}`;
  }

  // Début de collecte (0-1/6)
  return `Profil démarré (${completeness}%). Manque: ${missingFields.join(', ')}`;
}

/**
 * Tool principal: collectUserProfile
 *
 * @param {Object} input - Les données du profil (partielles ou complètes)
 * @param {Object} existingProfile - Le profil utilisateur existant (pour merge)
 * @returns {Object} Résultat avec succès, complétude, profil mis à jour
 */
export async function collectUserProfile(input, existingProfile = {}) {
  try {
    // Valider l'input avec Zod
    const validatedInput = collectUserProfileSchema.parse(input);

    // ✅ MERGE avec le profil existant pour garder la mémoire
    const updatedProfile = {
      ...existingProfile,  // Garde l'ancien contexte
      ...validatedInput    // Ajoute/écrase avec les nouvelles données
    };

    // Calculer complétude
    const completeness = calculateCompleteness(updatedProfile);
    const missingFields = getMissingFields(updatedProfile);

    // Générer message
    const message = generateMessage(updatedProfile, completeness, missingFields);

    // Retourner résultat
    return {
      success: true,
      completeness,
      updatedProfile,
      missingFields,
      message,
      isComplete: completeness === 100
    };

  } catch (error) {
    // Erreur de validation Zod
    if (error.name === 'ZodError') {
      return {
        success: false,
        error: 'Données invalides',
        details: error.errors.map(e => `${e.path.join('.')}: ${e.message}`),
        message: 'Les données fournies ne sont pas valides'
      };
    }

    // Autre erreur
    return {
      success: false,
      error: error.message,
      message: 'Erreur lors de la collecte du profil'
    };
  }
}

/**
 * Définition du tool pour AI SDK
 */
export const collectUserProfileTool = {
  description: `Collecte et valide les informations du profil utilisateur.
Calcule la complétude (0-100%) et retourne les champs manquants.
Appeler SYSTÉMATIQUEMENT après chaque message utilisateur pour tracker la progression.

Les 6 infos obligatoires:
1. groupType: solo | couple | family | friends
2. age: nombre (pour filtrage restrictions 18+)
3. location: { city, radius? }
4. dates: { type: thisWeekend | nextWeekend | specific | flexible }
5. activityType: sport | culture | gastronomie | nature | detente | multi
6. budgetMax: nombre (LIMITE STRICTE en €)

Optionnel:
- childrenAges: array de nombres (si family)
- preferences: array de strings`,
  parameters: collectUserProfileSchema,
  execute: collectUserProfile
};

export default collectUserProfileTool;
