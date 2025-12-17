/**
 * Chat Storage Service - PostgreSQL
 * Stockage persistant des messages avec analytics
 */

import pg from 'pg';
import crypto from 'crypto';
import logger from '../utils/logger.js';

const { Pool } = pg;

// Pool de connexions PostgreSQL
let pool = null;
let isConnected = false;

/**
 * Initialiser la connexion PostgreSQL
 * Supporte DATABASE_URL ou paramètres séparés (POSTGRES_HOST, etc.)
 */
export async function initDatabase() {
  const databaseUrl = process.env.DATABASE_URL;
  const pgHost = process.env.POSTGRES_HOST;
  const pgPort = process.env.POSTGRES_PORT || 5432;
  const pgDb = process.env.POSTGRES_DB;
  const pgUser = process.env.POSTGRES_USER;
  const pgPassword = process.env.POSTGRES_PASSWORD;

  // Vérifier si on a une config valide
  const hasUrlConfig = !!databaseUrl;
  const hasParamsConfig = pgHost && pgDb && pgUser;

  if (!hasUrlConfig && !hasParamsConfig) {
    logger.warn('PostgreSQL config not set (need DATABASE_URL or POSTGRES_* params)');
    return false;
  }

  try {
    // Construire la config du pool
    let poolConfig = {
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 5000,
    };

    if (hasParamsConfig) {
      // Utiliser les paramètres séparés (plus robuste pour les mots de passe spéciaux)
      poolConfig.host = pgHost;
      poolConfig.port = parseInt(pgPort, 10);
      poolConfig.database = pgDb;
      poolConfig.user = pgUser;
      poolConfig.password = pgPassword;
      logger.info('Using PostgreSQL with individual parameters', { host: pgHost, port: pgPort, database: pgDb });
    } else {
      // Utiliser DATABASE_URL
      poolConfig.connectionString = databaseUrl;
      logger.info('Using PostgreSQL with DATABASE_URL');
    }

    pool = new Pool(poolConfig);

    // Test connection
    const client = await pool.connect();
    await client.query('SELECT NOW()');
    client.release();

    isConnected = true;
    logger.info('PostgreSQL connected successfully');
    return true;
  } catch (error) {
    logger.error('PostgreSQL connection failed', { error: error.message });
    isConnected = false;
    return false;
  }
}

/**
 * Vérifier si la DB est connectée
 */
export function isDatabaseConnected() {
  return isConnected;
}

/**
 * Obtenir le pool de connexions (pour partage avec autres services)
 */
export function getPool() {
  return pool;
}

/**
 * Obtenir ou créer une conversation
 */
async function getOrCreateConversation(userId, conversationId) {
  if (!isConnected) return null;

  try {
    // Check if conversation exists
    const existing = await pool.query(
      'SELECT id FROM conversations WHERE id = $1',
      [conversationId]
    );

    if (existing.rows.length > 0) {
      return conversationId;
    }

    // Create new conversation
    await pool.query(
      `INSERT INTO conversations (id, user_id, started_at, last_message_at, message_count)
       VALUES ($1, $2, NOW(), NOW(), 0)`,
      [conversationId, userId]
    );

    // Ensure user profile exists
    await pool.query(
      `INSERT INTO user_profiles (user_id, created_at)
       VALUES ($1, NOW())
       ON CONFLICT (user_id) DO NOTHING`,
      [userId]
    );

    return conversationId;
  } catch (error) {
    logger.error('Error creating conversation', { error: error.message });
    return null;
  }
}

/**
 * Sauvegarder un message
 */
export async function saveMessage(userId, conversationId, role, content, metadata = {}) {
  if (!isConnected) {
    logger.warn('PostgreSQL not connected, message not saved');
    return null;
  }

  try {
    // Ensure conversation exists
    await getOrCreateConversation(userId, conversationId);

    const messageId = crypto.randomUUID();

    // Insert message
    await pool.query(
      `INSERT INTO chat_messages (id, user_id, conversation_id, role, content, metadata, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, NOW())`,
      [messageId, userId, conversationId, role, content, JSON.stringify(metadata)]
    );

    // Update conversation stats
    await pool.query(
      `UPDATE conversations
       SET message_count = message_count + 1,
           last_message_at = NOW()
       WHERE id = $1`,
      [conversationId]
    );

    // Update user profile last interaction
    await pool.query(
      `UPDATE user_profiles
       SET last_interaction = NOW(),
           insights = jsonb_set(
             COALESCE(insights, '{}'),
             '{totalSearches}',
             (COALESCE((insights->>'totalSearches')::int, 0) + 1)::text::jsonb
           )
       WHERE user_id = $1`,
      [userId]
    );

    logger.info('Message saved to PostgreSQL', {
      userId,
      conversationId,
      messageId,
      role
    });

    return {
      id: messageId,
      conversationId,
      role,
      content,
      timestamp: new Date().toISOString(),
      ...metadata
    };
  } catch (error) {
    logger.error('Error saving message', { error: error.message });
    return null;
  }
}

/**
 * Sauvegarder les impressions d'activités
 */
export async function saveEventImpressions(userId, conversationId, messageId, events) {
  if (!isConnected || !events || events.length === 0) return;

  try {
    const values = events.map((event, index) => [
      userId,
      conversationId,
      messageId,
      event.id,
      event.title?.substring(0, 500),
      event.category,
      event.location?.city || event.city,
      event.price,
      event.dates?.start ? new Date(event.dates.start) : null,
      index + 1, // position
    ]);

    // Batch insert
    for (const v of values) {
      await pool.query(
        `INSERT INTO event_impressions
         (user_id, conversation_id, message_id, event_id, event_title, event_category, event_city, event_price, event_date, position)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)`,
        v
      );
    }

    logger.info('Event impressions saved', {
      userId,
      conversationId,
      count: events.length
    });
  } catch (error) {
    logger.error('Error saving event impressions', { error: error.message });
  }
}

/**
 * Marquer un événement comme cliqué
 */
export async function markEventClicked(userId, eventId, conversationId = null) {
  if (!isConnected) return false;

  try {
    let query = `
      UPDATE event_impressions
      SET clicked = TRUE, clicked_at = NOW()
      WHERE user_id = $1 AND event_id = $2
    `;
    const params = [userId, eventId];

    if (conversationId) {
      query += ' AND conversation_id = $3';
      params.push(conversationId);
    }

    await pool.query(query, params);
    return true;
  } catch (error) {
    logger.error('Error marking event clicked', { error: error.message });
    return false;
  }
}

/**
 * Récupérer l'historique d'un utilisateur
 */
export async function getHistory(userId, options = {}) {
  if (!isConnected) return [];

  const { limit = 50, conversationId, before } = options;

  try {
    let query = `
      SELECT id, conversation_id as "conversationId", role, content, metadata, created_at as timestamp
      FROM chat_messages
      WHERE user_id = $1
    `;
    const params = [userId];
    let paramIndex = 2;

    if (conversationId) {
      query += ` AND conversation_id = $${paramIndex}`;
      params.push(conversationId);
      paramIndex++;
    }

    if (before) {
      query += ` AND created_at < $${paramIndex}`;
      params.push(before);
      paramIndex++;
    }

    query += ` ORDER BY created_at DESC LIMIT $${paramIndex}`;
    params.push(limit);

    const result = await pool.query(query, params);

    // Reverse to get chronological order
    return result.rows.reverse();
  } catch (error) {
    logger.error('Error getting history', { error: error.message });
    return [];
  }
}

/**
 * Récupérer les conversations d'un utilisateur
 */
export async function getConversations(userId) {
  if (!isConnected) return [];

  try {
    const result = await pool.query(
      `SELECT id, message_count as "messageCount", started_at as "startedAt", last_message_at as "lastMessageAt"
       FROM conversations
       WHERE user_id = $1
       ORDER BY last_message_at DESC
       LIMIT 50`,
      [userId]
    );

    return result.rows;
  } catch (error) {
    logger.error('Error getting conversations', { error: error.message });
    return [];
  }
}

/**
 * Supprimer une conversation
 */
export async function deleteConversation(userId, conversationId) {
  if (!isConnected) return false;

  try {
    // CASCADE will delete messages and impressions
    const result = await pool.query(
      'DELETE FROM conversations WHERE id = $1 AND user_id = $2',
      [conversationId, userId]
    );

    logger.info('Conversation deleted', { userId, conversationId });
    return result.rowCount > 0;
  } catch (error) {
    logger.error('Error deleting conversation', { error: error.message });
    return false;
  }
}

/**
 * Effacer tout l'historique d'un utilisateur
 */
export async function clearHistory(userId) {
  if (!isConnected) return false;

  try {
    await pool.query(
      'DELETE FROM conversations WHERE user_id = $1',
      [userId]
    );

    logger.info('History cleared', { userId });
    return true;
  } catch (error) {
    logger.error('Error clearing history', { error: error.message });
    return false;
  }
}

/**
 * Obtenir les stats d'un utilisateur
 */
export async function getUserStats(userId) {
  if (!isConnected) return null;

  try {
    const result = await pool.query(
      `SELECT * FROM user_stats WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) {
      return {
        totalMessages: 0,
        totalConversations: 0,
        uniqueEventsShown: 0,
        totalClicks: 0
      };
    }

    return result.rows[0];
  } catch (error) {
    logger.error('Error getting user stats', { error: error.message });
    return null;
  }
}

/**
 * Obtenir/mettre à jour le profil utilisateur
 */
export async function getUserProfile(userId) {
  if (!isConnected) return null;

  try {
    const result = await pool.query(
      'SELECT * FROM user_profiles WHERE user_id = $1',
      [userId]
    );

    return result.rows[0] || null;
  } catch (error) {
    logger.error('Error getting user profile', { error: error.message });
    return null;
  }
}

export async function updateUserProfile(userId, preferences = {}, insights = {}) {
  if (!isConnected) return false;

  try {
    await pool.query(
      `INSERT INTO user_profiles (user_id, preferences, insights, updated_at)
       VALUES ($1, $2, $3, NOW())
       ON CONFLICT (user_id) DO UPDATE
       SET preferences = COALESCE(user_profiles.preferences, '{}') || $2,
           insights = COALESCE(user_profiles.insights, '{}') || $3,
           updated_at = NOW()`,
      [userId, JSON.stringify(preferences), JSON.stringify(insights)]
    );

    return true;
  } catch (error) {
    logger.error('Error updating user profile', { error: error.message });
    return false;
  }
}

/**
 * Logger une recherche
 */
export async function logSearch(userId, conversationId, searchParams, resultsCount, responseTimeMs) {
  if (!isConnected) return;

  try {
    await pool.query(
      `INSERT INTO search_logs (user_id, conversation_id, search_params, results_count, response_time_ms)
       VALUES ($1, $2, $3, $4, $5)`,
      [userId, conversationId, JSON.stringify(searchParams), resultsCount, responseTimeMs]
    );
  } catch (error) {
    logger.error('Error logging search', { error: error.message });
  }
}

/**
 * Analytics: Top activités
 */
export async function getPopularEvents(limit = 20) {
  if (!isConnected) return [];

  try {
    const result = await pool.query(
      'SELECT * FROM popular_events LIMIT $1',
      [limit]
    );
    return result.rows;
  } catch (error) {
    logger.error('Error getting popular events', { error: error.message });
    return [];
  }
}

/**
 * Analytics: Performance par catégorie
 */
export async function getCategoryPerformance() {
  if (!isConnected) return [];

  try {
    const result = await pool.query('SELECT * FROM category_performance');
    return result.rows;
  } catch (error) {
    logger.error('Error getting category performance', { error: error.message });
    return [];
  }
}

/**
 * Compter les messages utilisateur sur une période (pour quota)
 * @param {string} userId - ID utilisateur
 * @param {number} days - Nombre de jours (défaut: 7)
 * @returns {object} { count, resetDate }
 */
export async function getUserMessageCountForPeriod(userId, days = 7) {
  if (!isConnected) {
    return { count: 0, resetDate: null };
  }

  try {
    // Calculer le début de la période (semaine glissante)
    const periodStart = new Date();
    periodStart.setDate(periodStart.getDate() - days);

    // Compter les messages "user" (pas les réponses assistant) sur la période
    const result = await pool.query(
      `SELECT COUNT(*) as count, MIN(created_at) as oldest_message
       FROM chat_messages
       WHERE user_id = $1
         AND role = 'user'
         AND created_at >= $2`,
      [userId, periodStart.toISOString()]
    );

    const count = parseInt(result.rows[0]?.count || 0, 10);

    // Calculer la date de reset (quand le plus ancien message de la période expirera)
    let resetDate = null;
    if (result.rows[0]?.oldest_message) {
      resetDate = new Date(result.rows[0].oldest_message);
      resetDate.setDate(resetDate.getDate() + days);
    } else {
      // Si aucun message, reset dans 7 jours
      resetDate = new Date();
      resetDate.setDate(resetDate.getDate() + days);
    }

    logger.info('User message count for period', {
      userId,
      days,
      count,
      resetDate: resetDate.toISOString()
    });

    return {
      count,
      resetDate: resetDate.toISOString()
    };
  } catch (error) {
    logger.error('Error getting user message count', { error: error.message });
    return { count: 0, resetDate: null };
  }
}

/**
 * Fermer les connexions
 */
export async function closeDatabase() {
  if (pool) {
    await pool.end();
    isConnected = false;
    logger.info('PostgreSQL connection closed');
  }
}

export default {
  initDatabase,
  isDatabaseConnected,
  getPool,
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
  getUserMessageCountForPeriod,
  closeDatabase
};
