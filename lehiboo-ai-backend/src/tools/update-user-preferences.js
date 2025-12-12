/**
 * Tool: updateUserPreferences
 *
 * Permet à l'IA de mettre à jour les préférences utilisateur
 * quand elle détecte de nouvelles informations dans la conversation.
 *
 * Exemples:
 * - "J'aime pas le jazz" → dislikes: ['jazz']
 * - "Je suis végétarien" → dietaryRestrictions: ['végétarien']
 * - "J'adore les musées" → likes: ['musées']
 */

import { z } from 'zod';
import logger from '../utils/logger.js';

/**
 * Schema pour les mises à jour de préférences
 */
export const updatePreferencesSchema = z.object({
  // Ajouter aux likes
  addLikes: z.array(z.string()).optional(),
  // Ajouter aux dislikes
  addDislikes: z.array(z.string()).optional(),
  // Ajouter des restrictions alimentaires
  addDietaryRestrictions: z.array(z.string()).optional(),
  // Ajouter des besoins d'accessibilité
  addAccessibility: z.array(z.string()).optional(),
  // Définir le budget habituel
  setTypicalBudget: z.number().min(0).optional(),
  // Définir le groupe habituel
  setTypicalGroupType: z.enum(['solo', 'couple', 'family', 'friends']).optional(),
  // Raison de la mise à jour (pour le log)
  reason: z.string().optional()
});

/**
 * Exécute la mise à jour des préférences
 *
 * @param {Object} input - Les mises à jour à appliquer
 * @param {Object} existingPreferences - Les préférences existantes
 * @returns {Object} Nouvelles préférences mises à jour
 */
export async function updateUserPreferences(input, existingPreferences = {}) {
  try {
    const validated = updatePreferencesSchema.parse(input);

    // Créer les nouvelles préférences
    const updatedPreferences = {
      likes: [...new Set([
        ...(existingPreferences.likes || []),
        ...(validated.addLikes || [])
      ])],
      dislikes: [...new Set([
        ...(existingPreferences.dislikes || []),
        ...(validated.addDislikes || [])
      ])],
      dietaryRestrictions: [...new Set([
        ...(existingPreferences.dietaryRestrictions || []),
        ...(validated.addDietaryRestrictions || [])
      ])],
      accessibility: [...new Set([
        ...(existingPreferences.accessibility || []),
        ...(validated.addAccessibility || [])
      ])],
      favoriteCities: existingPreferences.favoriteCities || [],
      favoriteCategories: existingPreferences.favoriteCategories || [],
      typicalBudget: validated.setTypicalBudget || existingPreferences.typicalBudget,
      typicalGroupType: validated.setTypicalGroupType || existingPreferences.typicalGroupType
    };

    // Log pour tracking
    const changes = [];
    if (validated.addLikes?.length) changes.push(`+likes: ${validated.addLikes.join(', ')}`);
    if (validated.addDislikes?.length) changes.push(`+dislikes: ${validated.addDislikes.join(', ')}`);
    if (validated.addDietaryRestrictions?.length) changes.push(`+diet: ${validated.addDietaryRestrictions.join(', ')}`);
    if (validated.addAccessibility?.length) changes.push(`+access: ${validated.addAccessibility.join(', ')}`);
    if (validated.setTypicalBudget) changes.push(`budget: ${validated.setTypicalBudget}€`);
    if (validated.setTypicalGroupType) changes.push(`group: ${validated.setTypicalGroupType}`);

    logger.info('User preferences updated', {
      changes,
      reason: validated.reason,
      totalLikes: updatedPreferences.likes.length,
      totalDislikes: updatedPreferences.dislikes.length
    });

    return {
      success: true,
      updatedPreferences,
      changes,
      message: changes.length > 0
        ? `Préférences mises à jour: ${changes.join(', ')}`
        : 'Aucun changement'
    };

  } catch (error) {
    logger.error('Failed to update preferences', { error: error.message });
    return {
      success: false,
      error: error.message,
      message: 'Erreur lors de la mise à jour des préférences'
    };
  }
}

/**
 * Définition du tool pour AI SDK
 */
export const updateUserPreferencesTool = {
  description: `Met à jour les préférences utilisateur quand tu détectes de nouvelles informations.

APPELLE CE TOOL quand l'utilisateur mentionne:
- Ce qu'il aime: "j'adore les musées" → addLikes: ['musées']
- Ce qu'il n'aime pas: "je déteste la foule" → addDislikes: ['foule']
- Restrictions alimentaires: "je suis végétarien" → addDietaryRestrictions: ['végétarien']
- Budget habituel: "je dépense souvent 50€" → setTypicalBudget: 50

Ces préférences sont PERSISTANTES et serviront à personnaliser les futures recommandations.`,
  parameters: updatePreferencesSchema,
  execute: updateUserPreferences
};

export default updateUserPreferencesTool;
