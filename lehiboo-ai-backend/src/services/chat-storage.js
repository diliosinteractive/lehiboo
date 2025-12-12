/**
 * Chat Storage Service
 * Wrapper qui utilise PostgreSQL si disponible, sinon fallback JSON
 */

import * as pgStorage from './chat-storage-pg.js';
import * as jsonStorage from './chat-storage-json.js';
import logger from '../utils/logger.js';

let usePostgres = false;

/**
 * Initialiser le storage (appelé au démarrage)
 */
export async function initStorage() {
  // Essayer PostgreSQL d'abord
  const pgConnected = await pgStorage.initDatabase();

  if (pgConnected) {
    usePostgres = true;
    logger.info('Using PostgreSQL for chat storage');
  } else {
    usePostgres = false;
    jsonStorage.initStorage();
    logger.info('Using JSON file for chat storage (PostgreSQL unavailable)');
  }

  return usePostgres;
}

/**
 * Sauvegarder un message
 */
export function saveMessage(userId, conversationId, role, content, metadata = {}) {
  if (usePostgres) {
    return pgStorage.saveMessage(userId, conversationId, role, content, metadata);
  }
  return jsonStorage.saveMessage(userId, conversationId, role, content, metadata);
}

/**
 * Sauvegarder les impressions d'activités (PostgreSQL only)
 */
export function saveEventImpressions(userId, conversationId, messageId, events) {
  if (usePostgres) {
    return pgStorage.saveEventImpressions(userId, conversationId, messageId, events);
  }
  // JSON storage doesn't support detailed event impressions
  return Promise.resolve();
}

/**
 * Marquer un événement comme cliqué (PostgreSQL only)
 */
export function markEventClicked(userId, eventId, conversationId = null) {
  if (usePostgres) {
    return pgStorage.markEventClicked(userId, eventId, conversationId);
  }
  return Promise.resolve(false);
}

/**
 * Récupérer l'historique
 */
export function getHistory(userId, options = {}) {
  if (usePostgres) {
    return pgStorage.getHistory(userId, options);
  }
  return jsonStorage.getHistory(userId, options);
}

/**
 * Récupérer les conversations
 */
export function getConversations(userId) {
  if (usePostgres) {
    return pgStorage.getConversations(userId);
  }
  return jsonStorage.getConversations(userId);
}

/**
 * Supprimer une conversation
 */
export function deleteConversation(userId, conversationId) {
  if (usePostgres) {
    return pgStorage.deleteConversation(userId, conversationId);
  }
  return jsonStorage.deleteConversation(userId, conversationId);
}

/**
 * Effacer l'historique
 */
export function clearHistory(userId) {
  if (usePostgres) {
    return pgStorage.clearHistory(userId);
  }
  return jsonStorage.clearHistory(userId);
}

/**
 * Stats utilisateur
 */
export function getUserStats(userId) {
  if (usePostgres) {
    return pgStorage.getUserStats(userId);
  }
  return jsonStorage.getUserStats(userId);
}

/**
 * Profil utilisateur (PostgreSQL only)
 */
export function getUserProfile(userId) {
  if (usePostgres) {
    return pgStorage.getUserProfile(userId);
  }
  return Promise.resolve(null);
}

export function updateUserProfile(userId, preferences = {}, insights = {}) {
  if (usePostgres) {
    return pgStorage.updateUserProfile(userId, preferences, insights);
  }
  return Promise.resolve(false);
}

/**
 * Logger une recherche (PostgreSQL only)
 */
export function logSearch(userId, conversationId, searchParams, resultsCount, responseTimeMs) {
  if (usePostgres) {
    return pgStorage.logSearch(userId, conversationId, searchParams, resultsCount, responseTimeMs);
  }
  return Promise.resolve();
}

/**
 * Analytics (PostgreSQL only)
 */
export function getPopularEvents(limit = 20) {
  if (usePostgres) {
    return pgStorage.getPopularEvents(limit);
  }
  return Promise.resolve([]);
}

export function getCategoryPerformance() {
  if (usePostgres) {
    return pgStorage.getCategoryPerformance();
  }
  return Promise.resolve([]);
}

/**
 * Vérifier si PostgreSQL est utilisé
 */
export function isUsingPostgres() {
  return usePostgres;
}

export default {
  initStorage,
  saveMessage,
  saveEventImpressions,
  markEventClicked,
  getHistory,
  getConversations,
  deleteConversation,
  clearHistory,
  getUserStats,
  getUserProfile,
  updateUserProfile,
  logSearch,
  getPopularEvents,
  getCategoryPerformance,
  isUsingPostgres
};
