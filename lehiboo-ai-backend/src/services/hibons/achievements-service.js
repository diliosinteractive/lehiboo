/**
 * Hibons Achievements Service
 * Gestion des badges, achievements et progression
 */

import pg from 'pg';
import logger from '../../utils/logger.js';
import * as transactionService from './transaction-service.js';

const { Pool } = pg;

let pool = null;

/**
 * Initialiser le pool de connexions
 */
export function initPool(existingPool) {
  pool = existingPool;
}

/**
 * Obtenir tous les achievements disponibles
 */
export async function getAllAchievements(includeSecret = false) {
  if (!pool) return [];

  try {
    let query = `
      SELECT id, name, description, icon, category, rarity,
             condition_type, condition_value, condition_metadata,
             reward_hibons, reward_xp, reward_title, display_order
      FROM hibons_achievements
      WHERE is_active = TRUE
    `;

    if (!includeSecret) {
      query += ` AND is_secret = FALSE`;
    }

    query += ` ORDER BY category, display_order`;

    const result = await pool.query(query);

    return result.rows.map(row => ({
      id: row.id,
      name: row.name,
      description: row.description,
      icon: row.icon,
      category: row.category,
      rarity: row.rarity,
      condition: {
        type: row.condition_type,
        value: row.condition_value,
        metadata: row.condition_metadata
      },
      rewards: {
        hibons: row.reward_hibons,
        xp: row.reward_xp,
        title: row.reward_title
      }
    }));
  } catch (error) {
    logger.error('Hibons: Error getting achievements', { error: error.message });
    return [];
  }
}

/**
 * Obtenir la progression d'un utilisateur sur tous les achievements
 */
export async function getUserAchievements(userId) {
  if (!pool) return [];

  try {
    const result = await pool.query(
      `SELECT
         a.id, a.name, a.description, a.icon, a.category, a.rarity,
         a.condition_type, a.condition_value, a.condition_metadata,
         a.reward_hibons, a.reward_xp, a.reward_title, a.is_secret,
         ua.progress, ua.unlocked_at, ua.claimed_at
       FROM hibons_achievements a
       LEFT JOIN hibons_user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = $1
       WHERE a.is_active = TRUE
       ORDER BY
         CASE WHEN ua.unlocked_at IS NOT NULL AND ua.claimed_at IS NULL THEN 0
              WHEN ua.unlocked_at IS NOT NULL THEN 1
              ELSE 2 END,
         a.category, a.display_order`,
      [userId]
    );

    return result.rows.map(row => ({
      id: row.id,
      name: row.name,
      description: row.is_secret && !row.unlocked_at ? '???' : row.description,
      icon: row.icon,
      category: row.category,
      rarity: row.rarity,
      isSecret: row.is_secret,
      condition: {
        type: row.condition_type,
        target: row.condition_value
      },
      rewards: {
        hibons: row.reward_hibons,
        xp: row.reward_xp,
        title: row.reward_title
      },
      progress: {
        current: row.progress || 0,
        target: row.condition_value,
        percentage: Math.min(100, Math.round(((row.progress || 0) / row.condition_value) * 100))
      },
      status: row.claimed_at ? 'claimed' :
              row.unlocked_at ? 'unlocked' :
              (row.progress || 0) >= row.condition_value ? 'ready' : 'in_progress',
      unlockedAt: row.unlocked_at,
      claimedAt: row.claimed_at
    }));
  } catch (error) {
    logger.error('Hibons: Error getting user achievements', { error: error.message, userId });
    return [];
  }
}

/**
 * Obtenir les achievements débloqués mais non réclamés
 */
export async function getUnclaimedAchievements(userId) {
  if (!pool) return [];

  try {
    const result = await pool.query(
      `SELECT
         a.id, a.name, a.description, a.icon, a.rarity,
         a.reward_hibons, a.reward_xp, a.reward_title,
         ua.unlocked_at
       FROM hibons_user_achievements ua
       JOIN hibons_achievements a ON a.id = ua.achievement_id
       WHERE ua.user_id = $1
         AND ua.unlocked_at IS NOT NULL
         AND ua.claimed_at IS NULL
       ORDER BY ua.unlocked_at DESC`,
      [userId]
    );

    return result.rows;
  } catch (error) {
    logger.error('Hibons: Error getting unclaimed achievements', { error: error.message, userId });
    return [];
  }
}

/**
 * Réclamer la récompense d'un achievement
 */
export async function claimAchievement(userId, achievementId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier que l'achievement est débloqué et non réclamé
    const result = await pool.query(
      `SELECT ua.*, a.reward_hibons, a.reward_xp, a.reward_title, a.name
       FROM hibons_user_achievements ua
       JOIN hibons_achievements a ON a.id = ua.achievement_id
       WHERE ua.user_id = $1 AND ua.achievement_id = $2`,
      [userId, achievementId]
    );

    if (result.rows.length === 0) {
      return { success: false, error: 'achievement_not_found' };
    }

    const achievement = result.rows[0];

    if (!achievement.unlocked_at) {
      return { success: false, error: 'not_unlocked' };
    }

    if (achievement.claimed_at) {
      return { success: false, error: 'already_claimed' };
    }

    // Marquer comme réclamé
    await pool.query(
      `UPDATE hibons_user_achievements
       SET claimed_at = NOW(), updated_at = NOW()
       WHERE user_id = $1 AND achievement_id = $2`,
      [userId, achievementId]
    );

    // Créditer les récompenses
    let creditResult = null;
    if (achievement.reward_hibons > 0) {
      creditResult = await transactionService.creditHibons(userId, 'ACHIEVEMENT', {
        amount: achievement.reward_hibons,
        description: `Achievement: ${achievement.name}`,
        referenceType: 'achievement',
        referenceId: achievementId,
        skipLimitCheck: true
      });
    }

    // Ajouter l'XP
    if (achievement.reward_xp > 0) {
      await pool.query(
        `UPDATE hibons_wallets SET xp = xp + $1, updated_at = NOW() WHERE user_id = $2`,
        [achievement.reward_xp, userId]
      );
    }

    // Appliquer le titre si disponible
    if (achievement.reward_title) {
      await pool.query(
        `UPDATE hibons_wallets SET title = $1, updated_at = NOW() WHERE user_id = $2`,
        [achievement.reward_title, userId]
      );
    }

    logger.info('Hibons: Achievement claimed', {
      userId,
      achievementId,
      hibons: achievement.reward_hibons,
      xp: achievement.reward_xp
    });

    return {
      success: true,
      achievement: {
        id: achievementId,
        name: achievement.name
      },
      rewards: {
        hibons: achievement.reward_hibons,
        xp: achievement.reward_xp,
        title: achievement.reward_title
      },
      newBalance: creditResult?.newBalance,
      levelUp: creditResult?.levelUp
    };
  } catch (error) {
    logger.error('Hibons: Error claiming achievement', { error: error.message, userId, achievementId });
    return { success: false, error: error.message };
  }
}

/**
 * Mettre à jour la progression d'un achievement
 */
export async function updateProgress(userId, conditionType, increment = 1, metadata = {}) {
  if (!pool) return [];

  try {
    // Trouver les achievements concernés par ce type de condition
    const achievements = await pool.query(
      `SELECT id, condition_value, condition_metadata
       FROM hibons_achievements
       WHERE condition_type = $1 AND is_active = TRUE`,
      [conditionType]
    );

    const newlyUnlocked = [];

    for (const achievement of achievements.rows) {
      // Vérifier les métadonnées supplémentaires si nécessaire
      if (achievement.condition_metadata && Object.keys(achievement.condition_metadata).length > 0) {
        let match = true;
        for (const [key, value] of Object.entries(achievement.condition_metadata)) {
          if (metadata[key] !== value) {
            match = false;
            break;
          }
        }
        if (!match) continue;
      }

      // Mettre à jour ou créer la progression
      const result = await pool.query(
        `INSERT INTO hibons_user_achievements (user_id, achievement_id, progress, target, created_at)
         VALUES ($1, $2, $3, $4, NOW())
         ON CONFLICT (user_id, achievement_id)
         DO UPDATE SET
           progress = LEAST(hibons_user_achievements.progress + $3, hibons_user_achievements.target),
           updated_at = NOW()
         RETURNING progress, target, unlocked_at`,
        [userId, achievement.id, increment, achievement.condition_value]
      );

      const progress = result.rows[0];

      // Vérifier si l'achievement vient d'être débloqué
      if (progress.progress >= progress.target && !progress.unlocked_at) {
        await pool.query(
          `UPDATE hibons_user_achievements
           SET unlocked_at = NOW(), updated_at = NOW()
           WHERE user_id = $1 AND achievement_id = $2`,
          [userId, achievement.id]
        );

        // Récupérer les infos complètes
        const achievementInfo = await pool.query(
          `SELECT name, description, icon, rarity, reward_hibons, reward_xp
           FROM hibons_achievements WHERE id = $1`,
          [achievement.id]
        );

        if (achievementInfo.rows.length > 0) {
          newlyUnlocked.push({
            id: achievement.id,
            ...achievementInfo.rows[0]
          });
        }

        logger.info('Hibons: Achievement unlocked', { userId, achievementId: achievement.id });
      }
    }

    // Mettre à jour l'achievement "chasseur de badges"
    if (newlyUnlocked.length > 0) {
      await updateProgress(userId, 'achievements', newlyUnlocked.length);
    }

    return newlyUnlocked;
  } catch (error) {
    logger.error('Hibons: Error updating achievement progress', { error: error.message, userId, conditionType });
    return [];
  }
}

/**
 * Incrémenter un compteur spécifique et mettre à jour les achievements
 */
export async function trackAction(userId, actionType, metadata = {}) {
  if (!pool) return { tracked: false, unlocked: [] };

  try {
    // Mapper l'action vers le type de condition d'achievement
    const actionToCondition = {
      'search': 'searches',
      'view_event': 'events_viewed',
      'favorite': 'favorites',
      'share': 'shares',
      'review': 'reviews',
      'photo': 'photos',
      'video': 'videos',
      'booking': 'bookings',
      'checkin': 'checkins',
      'referral': 'referrals'
    };

    const conditionType = actionToCondition[actionType.toLowerCase()] || actionType.toLowerCase();

    // Mettre à jour la progression
    const unlocked = await updateProgress(userId, conditionType, 1, metadata);

    return {
      tracked: true,
      conditionType,
      unlocked
    };
  } catch (error) {
    logger.error('Hibons: Error tracking action', { error: error.message, userId, actionType });
    return { tracked: false, unlocked: [] };
  }
}

/**
 * Vérifier les achievements secrets
 */
export async function checkSecretAchievements(userId, context = {}) {
  if (!pool) return [];

  const unlocked = [];

  try {
    // Noctambule - réservation après minuit
    if (context.booking && context.hour !== undefined) {
      if (context.hour >= 0 && context.hour < 5) {
        const result = await updateProgress(userId, 'booking_after_midnight', 1);
        unlocked.push(...result);
      }
    }

    // Lève-Tôt - réservation avant 7h
    if (context.booking && context.hour !== undefined) {
      if (context.hour >= 5 && context.hour < 7) {
        const result = await updateProgress(userId, 'booking_before_7am', 1);
        unlocked.push(...result);
      }
    }

    return unlocked;
  } catch (error) {
    logger.error('Hibons: Error checking secret achievements', { error: error.message, userId });
    return [];
  }
}

/**
 * Obtenir les statistiques d'achievements
 */
export async function getAchievementStats(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         COUNT(DISTINCT a.id) FILTER (WHERE ua.unlocked_at IS NOT NULL) as unlocked,
         COUNT(DISTINCT a.id) FILTER (WHERE ua.claimed_at IS NOT NULL) as claimed,
         COUNT(DISTINCT a.id) as total,
         SUM(CASE WHEN ua.claimed_at IS NOT NULL THEN a.reward_hibons ELSE 0 END) as hibons_earned,
         SUM(CASE WHEN ua.claimed_at IS NOT NULL THEN a.reward_xp ELSE 0 END) as xp_earned
       FROM hibons_achievements a
       LEFT JOIN hibons_user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = $1
       WHERE a.is_active = TRUE`,
      [userId]
    );

    const row = result.rows[0];

    // Compter par catégorie
    const categoryResult = await pool.query(
      `SELECT
         a.category,
         COUNT(DISTINCT a.id) as total,
         COUNT(DISTINCT a.id) FILTER (WHERE ua.unlocked_at IS NOT NULL) as unlocked
       FROM hibons_achievements a
       LEFT JOIN hibons_user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = $1
       WHERE a.is_active = TRUE
       GROUP BY a.category`,
      [userId]
    );

    // Compter par rareté
    const rarityResult = await pool.query(
      `SELECT
         a.rarity,
         COUNT(DISTINCT a.id) as total,
         COUNT(DISTINCT a.id) FILTER (WHERE ua.unlocked_at IS NOT NULL) as unlocked
       FROM hibons_achievements a
       LEFT JOIN hibons_user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = $1
       WHERE a.is_active = TRUE
       GROUP BY a.rarity`,
      [userId]
    );

    return {
      total: parseInt(row.total, 10),
      unlocked: parseInt(row.unlocked, 10),
      claimed: parseInt(row.claimed, 10),
      unclaimed: parseInt(row.unlocked, 10) - parseInt(row.claimed, 10),
      progress: Math.round((parseInt(row.unlocked, 10) / parseInt(row.total, 10)) * 100),
      hibonsEarned: parseInt(row.hibons_earned || 0, 10),
      xpEarned: parseInt(row.xp_earned || 0, 10),
      byCategory: categoryResult.rows.reduce((acc, r) => {
        acc[r.category] = {
          total: parseInt(r.total, 10),
          unlocked: parseInt(r.unlocked, 10)
        };
        return acc;
      }, {}),
      byRarity: rarityResult.rows.reduce((acc, r) => {
        acc[r.rarity] = {
          total: parseInt(r.total, 10),
          unlocked: parseInt(r.unlocked, 10)
        };
        return acc;
      }, {})
    };
  } catch (error) {
    logger.error('Hibons: Error getting achievement stats', { error: error.message, userId });
    return null;
  }
}

/**
 * Vérifier et mettre à jour le streak achievement
 */
export async function checkStreakAchievement(userId, currentStreak) {
  if (!pool) return [];

  try {
    const unlocked = await updateProgress(userId, 'streak', currentStreak, {});

    // Pour le streak, on met à jour avec la valeur absolue (pas un incrément)
    await pool.query(
      `UPDATE hibons_user_achievements ua
       SET progress = $1, updated_at = NOW()
       FROM hibons_achievements a
       WHERE ua.achievement_id = a.id
         AND ua.user_id = $2
         AND a.condition_type = 'streak'
         AND ua.progress < $1`,
      [currentStreak, userId]
    );

    // Vérifier les nouveaux débloquages
    const newUnlocks = await pool.query(
      `SELECT a.id, a.name, a.description, a.icon, a.rarity, a.reward_hibons, a.reward_xp
       FROM hibons_user_achievements ua
       JOIN hibons_achievements a ON a.id = ua.achievement_id
       WHERE ua.user_id = $1
         AND a.condition_type = 'streak'
         AND ua.progress >= ua.target
         AND ua.unlocked_at IS NULL`,
      [userId]
    );

    for (const achievement of newUnlocks.rows) {
      await pool.query(
        `UPDATE hibons_user_achievements
         SET unlocked_at = NOW(), updated_at = NOW()
         WHERE user_id = $1 AND achievement_id = $2`,
        [userId, achievement.id]
      );
      unlocked.push(achievement);
    }

    return unlocked;
  } catch (error) {
    logger.error('Hibons: Error checking streak achievement', { error: error.message, userId });
    return [];
  }
}

export default {
  initPool,
  getAllAchievements,
  getUserAchievements,
  getUnclaimedAchievements,
  claimAchievement,
  updateProgress,
  trackAction,
  checkSecretAchievements,
  getAchievementStats,
  checkStreakAchievement
};
