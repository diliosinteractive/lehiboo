/**
 * Service de gestion des prompts YAML
 */

import fs from 'fs/promises';
import path from 'path';
import YAML from 'yaml';
import logger from '../utils/logger.js';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Cache des prompts
const promptCache = new Map();

/**
 * Charger un prompt YAML
 */
async function loadPromptFile(filename) {
  try {
    // Vérifier le cache
    if (promptCache.has(filename)) {
      logger.debug('Loading prompt from cache', { filename });
      return promptCache.get(filename);
    }

    // Charger depuis le fichier
    const filePath = path.join(__dirname, '../prompts', filename);
    const fileContent = await fs.readFile(filePath, 'utf-8');
    const parsed = YAML.parse(fileContent);

    // Mettre en cache
    promptCache.set(filename, parsed);
    logger.debug('Prompt loaded and cached', { filename });

    return parsed;
  } catch (error) {
    logger.error('Error loading prompt file', {
      filename,
      error: error.message,
    });
    return null;
  }
}

/**
 * Charger le prompt système principal
 */
export async function loadSystemPrompt(context = {}) {
  try {
    const mainPrompt = await loadPromptFile('system-prompt.yaml');

    if (!mainPrompt) {
      logger.warn('Using fallback system prompt');
      return getFallbackSystemPrompt();
    }

    // Construire le prompt basé sur le stage
    const stage = context.currentStage || 'greeting';
    let systemText = mainPrompt.system || '';

    // Ajouter les instructions du stage actuel
    if (mainPrompt.stages && mainPrompt.stages[stage]) {
      const stageInstructions = mainPrompt.stages[stage];
      systemText += '\n\n## Current Stage: ' + stage + '\n';
      systemText += stageInstructions.instructions || '';

      // Ajouter les exemples si disponibles
      if (stageInstructions.examples) {
        systemText += '\n\n### Examples:\n';
        stageInstructions.examples.forEach((example) => {
          systemText += `- ${example}\n`;
        });
      }
    }

    // Ajouter le contexte utilisateur
    if (context.userContext && Object.keys(context.userContext).length > 0) {
      systemText += '\n\n## User Context:\n';
      systemText += JSON.stringify(context.userContext, null, 2);
    }

    return systemText;
  } catch (error) {
    logger.error('Error loading system prompt', { error: error.message });
    return getFallbackSystemPrompt();
  }
}

/**
 * Charger un prompt spécialisé
 */
export async function loadSpecializedPrompt(type) {
  const filename = `specialized/${type}.yaml`;
  return await loadPromptFile(filename);
}

/**
 * Prompt système de fallback si YAML non disponible
 */
function getFallbackSystemPrompt() {
  return `Tu es l'assistant conversationnel de Le Hiboo, une plateforme de réservation d'activités et d'événements.

Ton rôle est d'aider les utilisateurs à trouver l'activité parfaite en posant les bonnes questions :
- Type de groupe (solo, couple, famille, amis)
- Âge et restrictions
- Dates souhaitées
- Type d'activité (sport, culture, gastronomie, nature, détente)
- Budget approximatif

Tu dois être :
- Chaleureux et enthousiaste
- Concis dans tes réponses
- Proactif avec des suggestions
- Attentif au contexte météo

Utilise le format JSON pour retourner des métadonnées :
\`\`\`json
{
  "stage": "age_collection",
  "quickChips": [
    {"text": "18-25 ans", "value": "18-25"},
    {"text": "25-35 ans", "value": "25-35"}
  ],
  "userContext": {
    "groupType": "couple"
  }
}
\`\`\`

Commence toujours par un message chaleureux et naturel.`;
}

/**
 * Vider le cache des prompts (utile pour reload)
 */
export function clearPromptCache() {
  promptCache.clear();
  logger.info('Prompt cache cleared');
}

/**
 * Lister tous les prompts disponibles
 */
export async function listAvailablePrompts() {
  try {
    const promptsDir = path.join(__dirname, '../prompts');
    const files = await fs.readdir(promptsDir, { recursive: true });

    const yamlFiles = files.filter((file) => file.endsWith('.yaml') || file.endsWith('.yml'));

    logger.info('Available prompts', { count: yamlFiles.length, files: yamlFiles });
    return yamlFiles;
  } catch (error) {
    logger.error('Error listing prompts', { error: error.message });
    return [];
  }
}
