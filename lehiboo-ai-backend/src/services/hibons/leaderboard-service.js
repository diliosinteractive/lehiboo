/**
 * Hibons Leaderboard Service
 * Gestion des classements (global, hebdomadaire, par ville)
 */

import pg from 'pg';
import logger from '../../utils/logger.js';

const { Pool } = pg;

let pool = null;

/**
 * Initialiser le pool de connexions
 */
export function initPool(existingPool) {
  pool = existingPool;
}

/**
 * Obtenir le classement en temps réel
 */
export async function getLiveLeaderboard(options = {}) {
  if (!pool) return [];

  const { limit = 100, scope = 'global', scopeValue = null } = options;

  try {
    let query = `
      SELECT
        w.user_id,
        w.balance,
        w.level,
        w.title,
        w.lifetime_earned,
        w.current_streak,
        w.created_at as member_since,
        (SELECT COUNT(*) FROM hibons_user_achievements ua
         WHERE ua.user_id = w.user_id AND ua.unlocked_at IS NOT NULL) as achievements
      FROM hibons_wallets w
    `;

    const params = [];
    let paramIndex = 1;

    // TODO: Filtrage par ville si scope = 'city'
    // Nécessite une jointure avec les profils utilisateurs WordPress

    query += ` ORDER BY w.lifetime_earned DESC LIMIT $${paramIndex}`;
    params.push(limit);

    const result = await pool.query(query, params);

    return result.rows.map((row, index) => ({
      rank: index + 1,
      userId: row.user_id,
      balance: row.balance,
      level: row.level,
      title: row.title,
      lifetimeEarned: row.lifetime_earned,
      streak: row.current_streak,
      achievements: parseInt(row.achievements || 0, 10),
      memberSince: row.member_since
    }));
  } catch (error) {
    logger.error('Hibons: Error getting live leaderboard', { error: error.message });
    return [];
  }
}

/**
 * Obtenir la position d'un utilisateur dans le classement
 */
export async function getUserRank(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         user_id,
         lifetime_earned,
         RANK() OVER (ORDER BY lifetime_earned DESC) as rank
       FROM hibons_wallets`,
      []
    );

    const userRow = result.rows.find(r => r.user_id === userId);

    if (!userRow) return null;

    // Trouver les utilisateurs autour
    const userIndex = result.rows.findIndex(r => r.user_id === userId);
    const surrounding = [];

    // 2 avant
    for (let i = Math.max(0, userIndex - 2); i < userIndex; i++) {
      surrounding.push({
        rank: i + 1,
        userId: result.rows[i].user_id,
        lifetimeEarned: result.rows[i].lifetime_earned,
        isCurrentUser: false
      });
    }

    // Utilisateur actuel
    surrounding.push({
      rank: parseInt(userRow.rank, 10),
      userId,
      lifetimeEarned: userRow.lifetime_earned,
      isCurrentUser: true
    });

    // 2 après
    for (let i = userIndex + 1; i <= Math.min(result.rows.length - 1, userIndex + 2); i++) {
      surrounding.push({
        rank: i + 1,
        userId: result.rows[i].user_id,
        lifetimeEarned: result.rows[i].lifetime_earned,
        isCurrentUser: false
      });
    }

    return {
      rank: parseInt(userRow.rank, 10),
      total: result.rows.length,
      percentile: Math.round((1 - (parseInt(userRow.rank, 10) - 1) / result.rows.length) * 100),
      lifetimeEarned: userRow.lifetime_earned,
      surrounding
    };
  } catch (error) {
    logger.error('Hibons: Error getting user rank', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir le classement hebdomadaire
 */
export async function getWeeklyLeaderboard(limit = 100) {
  if (!pool) return [];

  try {
    // Début de la semaine (lundi)
    const today = new Date();
    const dayOfWeek = today.getDay();
    const monday = new Date(today);
    monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
    monday.setHours(0, 0, 0, 0);

    const result = await pool.query(
      `SELECT
         t.user_id,
         SUM(t.amount) as weekly_earned,
         w.level,
         w.title,
         w.current_streak
       FROM hibons_transactions t
       JOIN hibons_wallets w ON w.user_id = t.user_id
       WHERE t.type = 'EARN'
         AND t.status = 'completed'
         AND t.created_at >= $1
       GROUP BY t.user_id, w.level, w.title, w.current_streak
       ORDER BY weekly_earned DESC
       LIMIT $2`,
      [monday.toISOString(), limit]
    );

    return result.rows.map((row, index) => ({
      rank: index + 1,
      userId: row.user_id,
      weeklyEarned: parseInt(row.weekly_earned, 10),
      level: row.level,
      title: row.title,
      streak: row.current_streak
    }));
  } catch (error) {
    logger.error('Hibons: Error getting weekly leaderboard', { error: error.message });
    return [];
  }
}

/**
 * Créer un snapshot du classement (pour historique)
 */
export async function createLeaderboardSnapshot(periodType = 'weekly') {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const today = new Date();
    let periodStart, periodEnd;

    if (periodType === 'daily') {
      periodStart = new Date(today);
      periodStart.setHours(0, 0, 0, 0);
      periodEnd = new Date(periodStart);
      periodEnd.setDate(periodEnd.getDate() + 1);
    } else if (periodType === 'weekly') {
      const dayOfWeek = today.getDay();
      periodStart = new Date(today);
      periodStart.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
      periodStart.setHours(0, 0, 0, 0);
      periodEnd = new Date(periodStart);
      periodEnd.setDate(periodEnd.getDate() + 7);
    } else if (periodType === 'monthly') {
      periodStart = new Date(today.getFullYear(), today.getMonth(), 1);
      periodEnd = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    }

    // Calculer le classement
    let rankings;
    if (periodType === 'all_time') {
      rankings = await getLiveLeaderboard({ limit: 100 });
    } else {
      const result = await pool.query(
        `SELECT
           t.user_id,
           SUM(t.amount) as period_earned,
           w.level,
           w.title
         FROM hibons_transactions t
         JOIN hibons_wallets w ON w.user_id = t.user_id
         WHERE t.type = 'EARN'
           AND t.status = 'completed'
           AND t.created_at >= $1
           AND t.created_at < $2
         GROUP BY t.user_id, w.level, w.title
         ORDER BY period_earned DESC
         LIMIT 100`,
        [periodStart.toISOString(), periodEnd.toISOString()]
      );

      rankings = result.rows.map((row, index) => ({
        rank: index + 1,
        userId: row.user_id,
        earned: parseInt(row.period_earned, 10),
        level: row.level,
        title: row.title
      }));
    }

    // Sauvegarder le snapshot
    await pool.query(
      `INSERT INTO hibons_leaderboard_snapshots (period_type, period_start, period_end, scope, rankings, created_at)
       VALUES ($1, $2, $3, 'global', $4, NOW())
       ON CONFLICT (period_type, period_start, scope, scope_value)
       DO UPDATE SET rankings = $4, created_at = NOW()`,
      [periodType, periodStart.toISOString().split('T')[0], periodEnd.toISOString().split('T')[0], JSON.stringify(rankings)]
    );

    logger.info('Hibons: Leaderboard snapshot created', { periodType, count: rankings.length });

    return { success: true, periodType, count: rankings.length };
  } catch (error) {
    logger.error('Hibons: Error creating leaderboard snapshot', { error: error.message });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir un snapshot historique
 */
export async function getLeaderboardSnapshot(periodType, periodStart) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT rankings, period_start, period_end, created_at
       FROM hibons_leaderboard_snapshots
       WHERE period_type = $1 AND period_start = $2 AND scope = 'global'`,
      [periodType, periodStart]
    );

    if (result.rows.length === 0) return null;

    const row = result.rows[0];
    return {
      periodType,
      periodStart: row.period_start,
      periodEnd: row.period_end,
      rankings: row.rankings,
      createdAt: row.created_at
    };
  } catch (error) {
    logger.error('Hibons: Error getting leaderboard snapshot', { error: error.message });
    return null;
  }
}

/**
 * Obtenir les statistiques globales du leaderboard
 */
export async function getLeaderboardStats() {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         COUNT(*) as total_users,
         SUM(lifetime_earned) as total_hibons_distributed,
         AVG(lifetime_earned) as avg_hibons_per_user,
         MAX(lifetime_earned) as top_hibons,
         AVG(level) as avg_level,
         MAX(current_streak) as max_streak
       FROM hibons_wallets`
    );

    const row = result.rows[0];

    // Distribution par niveau
    const levelDist = await pool.query(
      `SELECT level, COUNT(*) as count
       FROM hibons_wallets
       GROUP BY level
       ORDER BY level`
    );

    return {
      totalUsers: parseInt(row.total_users || 0, 10),
      totalHibonsDistributed: parseInt(row.total_hibons_distributed || 0, 10),
      avgHibonsPerUser: Math.round(parseFloat(row.avg_hibons_per_user || 0)),
      topHibons: parseInt(row.top_hibons || 0, 10),
      avgLevel: parseFloat(row.avg_level || 1).toFixed(1),
      maxStreak: parseInt(row.max_streak || 0, 10),
      levelDistribution: levelDist.rows.reduce((acc, r) => {
        acc[r.level] = parseInt(r.count, 10);
        return acc;
      }, {})
    };
  } catch (error) {
    logger.error('Hibons: Error getting leaderboard stats', { error: error.message });
    return null;
  }
}

/**
 * Obtenir le top 3 (pour affichage rapide)
 */
export async function getTopThree() {
  if (!pool) return [];

  try {
    const result = await pool.query(
      `SELECT
         user_id,
         level,
         title,
         lifetime_earned,
         current_streak
       FROM hibons_wallets
       ORDER BY lifetime_earned DESC
       LIMIT 3`
    );

    return result.rows.map((row, index) => ({
      rank: index + 1,
      userId: row.user_id,
      level: row.level,
      title: row.title,
      lifetimeEarned: row.lifetime_earned,
      streak: row.current_streak
    }));
  } catch (error) {
    logger.error('Hibons: Error getting top three', { error: error.message });
    return [];
  }
}

export default {
  initPool,
  getLiveLeaderboard,
  getUserRank,
  getWeeklyLeaderboard,
  createLeaderboardSnapshot,
  getLeaderboardSnapshot,
  getLeaderboardStats,
  getTopThree
};
