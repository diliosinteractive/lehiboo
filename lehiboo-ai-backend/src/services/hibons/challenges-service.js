/**
 * Hibons Challenges Service
 * Gestion des challenges quotidiens, hebdomadaires et sponsorisés
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
 * Templates de challenges quotidiens
 */
const DAILY_CHALLENGE_TEMPLATES = [
  {
    id: 'daily_search',
    name: 'Explorateur du Jour',
    description: 'Effectue 3 recherches',
    icon: 'search',
    condition_type: 'searches',
    condition_value: 3,
    reward_hibons: 30,
    reward_xp: 15
  },
  {
    id: 'daily_view',
    name: 'Curieux',
    description: 'Consulte 5 événements',
    icon: 'eye',
    condition_type: 'events_viewed',
    condition_value: 5,
    reward_hibons: 20,
    reward_xp: 10
  },
  {
    id: 'daily_favorite',
    name: 'Coup de Coeur',
    description: 'Ajoute 1 événement aux favoris',
    icon: 'heart',
    condition_type: 'favorites',
    condition_value: 1,
    reward_hibons: 15,
    reward_xp: 8
  },
  {
    id: 'daily_share',
    name: 'Partageur',
    description: 'Partage 1 événement',
    icon: 'share',
    condition_type: 'shares',
    condition_value: 1,
    reward_hibons: 25,
    reward_xp: 12
  }
];

/**
 * Templates de challenges hebdomadaires
 */
const WEEKLY_CHALLENGE_TEMPLATES = [
  {
    id: 'weekly_booking',
    name: 'Sortie Hebdo',
    description: 'Effectue 1 réservation cette semaine',
    icon: 'ticket',
    condition_type: 'bookings',
    condition_value: 1,
    reward_hibons: 200,
    reward_xp: 50
  },
  {
    id: 'weekly_reviews',
    name: 'Critique de la Semaine',
    description: 'Poste 3 avis cette semaine',
    icon: 'star',
    condition_type: 'reviews',
    condition_value: 3,
    reward_hibons: 150,
    reward_xp: 40
  },
  {
    id: 'weekly_photos',
    name: 'Reporter',
    description: 'Ajoute 5 photos à tes avis',
    icon: 'camera',
    condition_type: 'photos',
    condition_value: 5,
    reward_hibons: 200,
    reward_xp: 50
  },
  {
    id: 'weekly_streak',
    name: 'Assidu',
    description: 'Connecte-toi 7 jours consécutifs',
    icon: 'flame',
    condition_type: 'streak_maintain',
    condition_value: 7,
    reward_hibons: 300,
    reward_xp: 75
  }
];

/**
 * Obtenir les challenges actifs pour un utilisateur
 */
export async function getActiveChallenges(userId, type = null) {
  if (!pool) return [];

  try {
    const now = new Date().toISOString();

    let query = `
      SELECT
        c.id, c.name, c.description, c.icon, c.type,
        c.condition_type, c.condition_value, c.condition_metadata,
        c.reward_hibons, c.reward_xp, c.reward_items,
        c.sponsor_id, c.sponsor_name, c.sponsor_logo,
        c.starts_at, c.ends_at, c.is_featured,
        uc.progress, uc.status, uc.joined_at, uc.completed_at
      FROM hibons_challenges c
      LEFT JOIN hibons_user_challenges uc ON uc.challenge_id = c.id AND uc.user_id = $1
      WHERE c.is_active = TRUE
        AND c.starts_at <= $2
        AND c.ends_at >= $2
    `;
    const params = [userId, now];

    if (type) {
      query += ` AND c.type = $3`;
      params.push(type);
    }

    query += ` ORDER BY c.is_featured DESC, c.ends_at ASC`;

    const result = await pool.query(query, params);

    return result.rows.map(row => ({
      id: row.id,
      name: row.name,
      description: row.description,
      icon: row.icon,
      type: row.type,
      condition: {
        type: row.condition_type,
        target: row.condition_value,
        metadata: row.condition_metadata
      },
      rewards: {
        hibons: row.reward_hibons,
        xp: row.reward_xp,
        items: row.reward_items
      },
      sponsor: row.sponsor_id ? {
        id: row.sponsor_id,
        name: row.sponsor_name,
        logo: row.sponsor_logo
      } : null,
      timing: {
        startsAt: row.starts_at,
        endsAt: row.ends_at,
        timeRemaining: Math.max(0, new Date(row.ends_at) - new Date())
      },
      isFeatured: row.is_featured,
      participation: {
        joined: !!row.joined_at,
        progress: row.progress || 0,
        status: row.status || 'not_joined',
        completedAt: row.completed_at
      }
    }));
  } catch (error) {
    logger.error('Hibons: Error getting active challenges', { error: error.message, userId });
    return [];
  }
}

/**
 * Rejoindre un challenge
 */
export async function joinChallenge(userId, challengeId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier que le challenge existe et est actif
    const challenge = await pool.query(
      `SELECT * FROM hibons_challenges
       WHERE id = $1 AND is_active = TRUE
         AND starts_at <= NOW() AND ends_at >= NOW()`,
      [challengeId]
    );

    if (challenge.rows.length === 0) {
      return { success: false, error: 'challenge_not_found' };
    }

    const ch = challenge.rows[0];

    // Vérifier le nombre max de participants
    if (ch.max_participants) {
      const count = await pool.query(
        `SELECT COUNT(*) FROM hibons_user_challenges WHERE challenge_id = $1`,
        [challengeId]
      );
      if (parseInt(count.rows[0].count, 10) >= ch.max_participants) {
        return { success: false, error: 'challenge_full' };
      }
    }

    // Vérifier si déjà inscrit
    const existing = await pool.query(
      `SELECT id FROM hibons_user_challenges WHERE user_id = $1 AND challenge_id = $2`,
      [userId, challengeId]
    );

    if (existing.rows.length > 0) {
      return { success: false, error: 'already_joined' };
    }

    // Rejoindre
    await pool.query(
      `INSERT INTO hibons_user_challenges (user_id, challenge_id, progress, target, status, joined_at)
       VALUES ($1, $2, 0, $3, 'active', NOW())`,
      [userId, challengeId, ch.condition_value]
    );

    logger.info('Hibons: User joined challenge', { userId, challengeId });

    return {
      success: true,
      challenge: {
        id: ch.id,
        name: ch.name,
        target: ch.condition_value
      }
    };
  } catch (error) {
    logger.error('Hibons: Error joining challenge', { error: error.message, userId, challengeId });
    return { success: false, error: error.message };
  }
}

/**
 * Mettre à jour la progression d'un challenge
 */
export async function updateChallengeProgress(userId, conditionType, increment = 1) {
  if (!pool) return [];

  try {
    const now = new Date().toISOString();

    // Trouver les challenges actifs de l'utilisateur correspondant au type
    const result = await pool.query(
      `SELECT uc.id, uc.challenge_id, uc.progress, uc.target, c.name, c.reward_hibons, c.reward_xp
       FROM hibons_user_challenges uc
       JOIN hibons_challenges c ON c.id = uc.challenge_id
       WHERE uc.user_id = $1
         AND uc.status = 'active'
         AND c.condition_type = $2
         AND c.starts_at <= $3
         AND c.ends_at >= $3`,
      [userId, conditionType, now]
    );

    const completed = [];

    for (const row of result.rows) {
      const newProgress = Math.min(row.progress + increment, row.target);

      await pool.query(
        `UPDATE hibons_user_challenges
         SET progress = $1, updated_at = NOW()
         WHERE id = $2`,
        [newProgress, row.id]
      );

      // Vérifier si complété
      if (newProgress >= row.target && row.progress < row.target) {
        await pool.query(
          `UPDATE hibons_user_challenges
           SET status = 'completed', completed_at = NOW(), updated_at = NOW()
           WHERE id = $1`,
          [row.id]
        );

        completed.push({
          id: row.challenge_id,
          name: row.name,
          rewards: {
            hibons: row.reward_hibons,
            xp: row.reward_xp
          }
        });

        logger.info('Hibons: Challenge completed', { userId, challengeId: row.challenge_id });
      }
    }

    return completed;
  } catch (error) {
    logger.error('Hibons: Error updating challenge progress', { error: error.message, userId });
    return [];
  }
}

/**
 * Réclamer la récompense d'un challenge
 */
export async function claimChallengeReward(userId, challengeId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier que le challenge est complété
    const result = await pool.query(
      `SELECT uc.*, c.name, c.reward_hibons, c.reward_xp, c.reward_items
       FROM hibons_user_challenges uc
       JOIN hibons_challenges c ON c.id = uc.challenge_id
       WHERE uc.user_id = $1 AND uc.challenge_id = $2`,
      [userId, challengeId]
    );

    if (result.rows.length === 0) {
      return { success: false, error: 'challenge_not_found' };
    }

    const participation = result.rows[0];

    if (participation.status !== 'completed') {
      return { success: false, error: 'not_completed' };
    }

    if (participation.claimed_at) {
      return { success: false, error: 'already_claimed' };
    }

    // Marquer comme réclamé
    await pool.query(
      `UPDATE hibons_user_challenges
       SET status = 'claimed', claimed_at = NOW(), updated_at = NOW()
       WHERE user_id = $1 AND challenge_id = $2`,
      [userId, challengeId]
    );

    // Créditer les Hibons
    let creditResult = null;
    if (participation.reward_hibons > 0) {
      creditResult = await transactionService.creditHibons(userId, 'CHALLENGE', {
        amount: participation.reward_hibons,
        description: `Challenge: ${participation.name}`,
        referenceType: 'challenge',
        referenceId: challengeId,
        skipLimitCheck: true
      });
    }

    // Ajouter l'XP
    if (participation.reward_xp > 0) {
      await pool.query(
        `UPDATE hibons_wallets SET xp = xp + $1, updated_at = NOW() WHERE user_id = $2`,
        [participation.reward_xp, userId]
      );
    }

    logger.info('Hibons: Challenge reward claimed', { userId, challengeId });

    return {
      success: true,
      challenge: {
        id: challengeId,
        name: participation.name
      },
      rewards: {
        hibons: participation.reward_hibons,
        xp: participation.reward_xp,
        items: participation.reward_items
      },
      newBalance: creditResult?.newBalance,
      levelUp: creditResult?.levelUp
    };
  } catch (error) {
    logger.error('Hibons: Error claiming challenge reward', { error: error.message, userId, challengeId });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir les challenges de l'utilisateur (actifs et récents)
 */
export async function getUserChallenges(userId, includeCompleted = true) {
  if (!pool) return [];

  try {
    let query = `
      SELECT
        c.id, c.name, c.description, c.icon, c.type,
        c.condition_type, c.condition_value,
        c.reward_hibons, c.reward_xp,
        c.sponsor_name, c.sponsor_logo,
        c.starts_at, c.ends_at,
        uc.progress, uc.target, uc.status, uc.joined_at, uc.completed_at, uc.claimed_at
      FROM hibons_user_challenges uc
      JOIN hibons_challenges c ON c.id = uc.challenge_id
      WHERE uc.user_id = $1
    `;

    if (!includeCompleted) {
      query += ` AND uc.status IN ('active', 'completed')`;
    }

    query += ` ORDER BY
      CASE uc.status
        WHEN 'completed' THEN 0
        WHEN 'active' THEN 1
        ELSE 2
      END,
      c.ends_at ASC`;

    const result = await pool.query(query, [userId]);

    return result.rows.map(row => ({
      id: row.id,
      name: row.name,
      description: row.description,
      icon: row.icon,
      type: row.type,
      condition: {
        type: row.condition_type,
        target: row.condition_value
      },
      rewards: {
        hibons: row.reward_hibons,
        xp: row.reward_xp
      },
      sponsor: row.sponsor_name ? {
        name: row.sponsor_name,
        logo: row.sponsor_logo
      } : null,
      timing: {
        startsAt: row.starts_at,
        endsAt: row.ends_at
      },
      progress: {
        current: row.progress,
        target: row.target,
        percentage: Math.round((row.progress / row.target) * 100)
      },
      status: row.status,
      joinedAt: row.joined_at,
      completedAt: row.completed_at,
      claimedAt: row.claimed_at
    }));
  } catch (error) {
    logger.error('Hibons: Error getting user challenges', { error: error.message, userId });
    return [];
  }
}

/**
 * Générer les challenges quotidiens (à exécuter via cron)
 */
export async function generateDailyChallenges() {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Vérifier s'ils existent déjà
    const existing = await pool.query(
      `SELECT id FROM hibons_challenges
       WHERE type = 'daily' AND starts_at >= $1 AND starts_at < $2`,
      [today.toISOString(), tomorrow.toISOString()]
    );

    if (existing.rows.length > 0) {
      return { success: true, message: 'Daily challenges already exist', count: existing.rows.length };
    }

    // Créer les challenges du jour
    for (const template of DAILY_CHALLENGE_TEMPLATES) {
      await pool.query(
        `INSERT INTO hibons_challenges (
           name, description, icon, type, starts_at, ends_at,
           condition_type, condition_value, reward_hibons, reward_xp,
           is_active, created_at
         ) VALUES ($1, $2, $3, 'daily', $4, $5, $6, $7, $8, $9, TRUE, NOW())`,
        [
          template.name,
          template.description,
          template.icon,
          today.toISOString(),
          tomorrow.toISOString(),
          template.condition_type,
          template.condition_value,
          template.reward_hibons,
          template.reward_xp
        ]
      );
    }

    logger.info('Hibons: Daily challenges generated', { count: DAILY_CHALLENGE_TEMPLATES.length });

    return { success: true, count: DAILY_CHALLENGE_TEMPLATES.length };
  } catch (error) {
    logger.error('Hibons: Error generating daily challenges', { error: error.message });
    return { success: false, error: error.message };
  }
}

/**
 * Générer les challenges hebdomadaires (à exécuter via cron)
 */
export async function generateWeeklyChallenges() {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Lundi de cette semaine
    const today = new Date();
    const dayOfWeek = today.getDay();
    const monday = new Date(today);
    monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
    monday.setHours(0, 0, 0, 0);

    // Dimanche soir
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    sunday.setHours(23, 59, 59, 999);

    // Vérifier s'ils existent déjà
    const existing = await pool.query(
      `SELECT id FROM hibons_challenges
       WHERE type = 'weekly' AND starts_at >= $1 AND starts_at <= $2`,
      [monday.toISOString(), sunday.toISOString()]
    );

    if (existing.rows.length > 0) {
      return { success: true, message: 'Weekly challenges already exist', count: existing.rows.length };
    }

    // Créer les challenges de la semaine
    for (const template of WEEKLY_CHALLENGE_TEMPLATES) {
      await pool.query(
        `INSERT INTO hibons_challenges (
           name, description, icon, type, starts_at, ends_at,
           condition_type, condition_value, reward_hibons, reward_xp,
           is_active, created_at
         ) VALUES ($1, $2, $3, 'weekly', $4, $5, $6, $7, $8, $9, TRUE, NOW())`,
        [
          template.name,
          template.description,
          template.icon,
          monday.toISOString(),
          sunday.toISOString(),
          template.condition_type,
          template.condition_value,
          template.reward_hibons,
          template.reward_xp
        ]
      );
    }

    logger.info('Hibons: Weekly challenges generated', { count: WEEKLY_CHALLENGE_TEMPLATES.length });

    return { success: true, count: WEEKLY_CHALLENGE_TEMPLATES.length };
  } catch (error) {
    logger.error('Hibons: Error generating weekly challenges', { error: error.message });
    return { success: false, error: error.message };
  }
}

/**
 * Auto-join aux challenges quotidiens (optionnel)
 */
export async function autoJoinDailyChallenges(userId) {
  if (!pool) return { success: false, joined: [] };

  try {
    const dailyChallenges = await getActiveChallenges(userId, 'daily');
    const joined = [];

    for (const challenge of dailyChallenges) {
      if (!challenge.participation.joined) {
        const result = await joinChallenge(userId, challenge.id);
        if (result.success) {
          joined.push(challenge.id);
        }
      }
    }

    return { success: true, joined };
  } catch (error) {
    logger.error('Hibons: Error auto-joining daily challenges', { error: error.message, userId });
    return { success: false, joined: [] };
  }
}

/**
 * Obtenir les statistiques de challenges
 */
export async function getChallengeStats(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         COUNT(*) FILTER (WHERE status = 'completed' OR status = 'claimed') as completed,
         COUNT(*) FILTER (WHERE status = 'claimed') as claimed,
         COUNT(*) FILTER (WHERE status = 'active') as active,
         SUM(CASE WHEN uc.claimed_at IS NOT NULL THEN c.reward_hibons ELSE 0 END) as hibons_earned
       FROM hibons_user_challenges uc
       JOIN hibons_challenges c ON c.id = uc.challenge_id
       WHERE uc.user_id = $1`,
      [userId]
    );

    const row = result.rows[0];

    return {
      completed: parseInt(row.completed || 0, 10),
      claimed: parseInt(row.claimed || 0, 10),
      active: parseInt(row.active || 0, 10),
      hibonsEarned: parseInt(row.hibons_earned || 0, 10)
    };
  } catch (error) {
    logger.error('Hibons: Error getting challenge stats', { error: error.message, userId });
    return null;
  }
}

export default {
  initPool,
  DAILY_CHALLENGE_TEMPLATES,
  WEEKLY_CHALLENGE_TEMPLATES,
  getActiveChallenges,
  joinChallenge,
  updateChallengeProgress,
  claimChallengeReward,
  getUserChallenges,
  generateDailyChallenges,
  generateWeeklyChallenges,
  autoJoinDailyChallenges,
  getChallengeStats
};
