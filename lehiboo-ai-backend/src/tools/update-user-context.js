/**
 * Tool: updateUserContext
 *
 * Permet a l'IA d'extraire et stocker les informations personnelles de l'utilisateur
 * Ces informations sont persistees et renvoyees dans la reponse JSON
 */

import { z } from 'zod';
import logger from '../utils/logger.js';

/**
 * Schema Zod - Toutes les proprietes sont optionnelles
 * L'IA extrait uniquement ce qu'elle detecte dans le message
 */
export const updateUserContextSchema = z.object({
  // Identite
  first_name: z.string().optional().describe("Prenom de l'utilisateur"),
  last_name: z.string().optional().describe("Nom de famille"),
  nickname: z.string().optional().describe("Surnom prefere"),

  // Localisation
  city: z.string().optional().describe("Ville de residence ou de preference"),
  region: z.string().optional().describe("Region (ex: Hauts-de-France)"),

  // Preferences d'activites
  favorite_activities: z.array(z.string()).optional().describe("Activites favorites mentionnees"),
  disliked_activities: z.array(z.string()).optional().describe("Activites que l'utilisateur n'aime pas"),
  favorite_categories: z.array(z.string()).optional().describe("Categories preferees (sport, culture, gastronomie, nature, detente)"),

  // Contexte personnel
  group_type: z.enum(['solo', 'couple', 'family', 'friends']).optional().describe("Avec qui sort generalement"),
  has_children: z.boolean().optional().describe("A des enfants"),
  children_ages: z.array(z.number()).optional().describe("Ages des enfants"),

  // Budget et contraintes
  budget_preference: z.enum(['free', 'low', 'medium', 'high', 'no_limit']).optional().describe("Preference de budget"),
  max_distance: z.number().optional().describe("Distance max acceptable en km"),

  // Gouts et interets
  interests: z.array(z.string()).optional().describe("Interets generaux mentionnes"),
  dietary_preferences: z.array(z.string()).optional().describe("Preferences alimentaires (vegetarien, vegan, etc.)"),

  // Contraintes et accessibilite
  mobility_constraints: z.boolean().optional().describe("Contraintes de mobilite"),
  pet_friendly_needed: z.boolean().optional().describe("Besoin d'endroits acceptant les animaux"),

  // Contexte temporel
  preferred_times: z.array(z.string()).optional().describe("Moments preferes (weekend, soiree, etc.)"),

  // Notes libres
  notes: z.string().optional().describe("Autres informations importantes mentionnees")
});

/**
 * Fonction d'execution du tool
 * Merge les nouvelles infos avec le contexte existant
 */
export async function executeUpdateUserContext(params, existingContext = {}) {
  try {
    logger.info('updateUserContext called', {
      newParams: Object.keys(params),
      existingKeys: Object.keys(existingContext)
    });

    // Merger intelligemment les donnees
    const updatedContext = { ...existingContext };

    for (const [key, value] of Object.entries(params)) {
      if (value === undefined || value === null) continue;

      // Pour les arrays, on merge sans doublons
      if (Array.isArray(value) && Array.isArray(existingContext[key])) {
        updatedContext[key] = [...new Set([...existingContext[key], ...value])];
      } else {
        // Pour les autres valeurs, on remplace
        updatedContext[key] = value;
      }
    }

    // Ajouter un timestamp de derniere mise a jour
    updatedContext._lastUpdated = new Date().toISOString();

    logger.info('userContext updated', {
      updatedKeys: Object.keys(updatedContext).filter(k => !k.startsWith('_'))
    });

    return {
      success: true,
      context: updatedContext,
      message: 'Contexte utilisateur mis a jour'
    };

  } catch (error) {
    logger.error('updateUserContext error', { error: error.message });
    return {
      success: false,
      context: existingContext,
      error: error.message
    };
  }
}

/**
 * Definition du tool pour AI SDK
 */
export const updateUserContextTool = {
  description: `Extrait et sauvegarde les informations personnelles de l'utilisateur detectees dans son message.
QUAND UTILISER: Appelle ce tool DES que l'utilisateur mentionne:
- Son prenom, nom ou surnom ("Je suis Juba", "Appelle-moi Ju")
- Sa ville ou region ("J'habite a Lille", "Je suis de Valenciennes")
- Ses gouts et preferences ("J'adore le spa", "Je n'aime pas le sport")
- Sa situation familiale ("Je suis en couple", "J'ai 2 enfants")
- Ses contraintes ("Budget limite", "Pas trop loin")

IMPORTANT:
- Appelle ce tool AVANT searchEvents si des infos utilisateur sont detectees
- N'extrait QUE les infos explicitement mentionnees, n'invente rien
- Peut etre appele plusieurs fois pour enrichir le contexte`,
  parameters: updateUserContextSchema,
  execute: executeUpdateUserContext
};

export default updateUserContextTool;
