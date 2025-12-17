/**
 * Hibons Transaction Service
 * Gestion des crédits, débits et historique des transactions
 */

import pg from 'pg';
import crypto from 'crypto';
import logger from '../../utils/logger.js';
import * as walletService from './wallet-service.js';

const { Pool } = pg;

let pool = null;

/**
 * Initialiser le pool de connexions
 */
export function initPool(existingPool) {
  pool = existingPool;
}

/**
 * Configuration des limites quotidiennes par action
 */
const DAILY_LIMITS = {
  SEARCH: 1,           // Première recherche du jour
  VIEW_EVENT: 10,      // Consulter un événement
  FAVORITE: 5,         // Ajouter aux favoris
  SHARE: 3,            // Partager un événement
  REVIEW: 3,           // Poster un avis
  REVIEW_PHOTO: 2,     // Avis avec photo
  REVIEW_VIDEO: 1,     // Avis avec vidéo
  CHAT_FEEDBACK: 5     // Feedback positif chat
};

/**
 * Configuration des récompenses par action
 */
const REWARDS = {
  // Actions quotidiennes
  DAILY_SEARCH: { hibons: 5, xp: 2 },
  VIEW_EVENT: { hibons: 2, xp: 1 },
  FAVORITE: { hibons: 5, xp: 2 },
  SHARE: { hibons: 15, xp: 5 },

  // Contenu
  REVIEW: { hibons: 30, xp: 15 },
  REVIEW_PHOTO: { hibons: 50, xp: 25 },
  REVIEW_VIDEO: { hibons: 100, xp: 50 },
  REVIEW_QUALITY: { hibons: 20, xp: 10 },
  REVIEW_HELPFUL: { hibons: 10, xp: 5 },

  // Réservations
  BOOKING: { hibonsPercent: 5, xp: 25, min: 10, max: 500 },
  CHECKIN: { hibons: 50, xp: 25 },

  // Parrainage
  REFERRAL_REFERRER: { hibons: 500, xp: 100 },
  REFERRAL_REFERRED: { hibons: 200, xp: 50 },

  // Chat
  FIRST_CHAT: { hibons: 30, xp: 15 },
  CHAT_FEEDBACK: { hibons: 10, xp: 5 },

  // Bienvenue
  WELCOME: { hibons: 100, xp: 25 }
};

/**
 * Obtenir ou créer les limites quotidiennes d'un utilisateur
 */
async function getOrCreateDailyLimits(userId) {
  const today = new Date().toISOString().split('T')[0];

  try {
    let result = await pool.query(
      `SELECT * FROM hibons_action_limits WHERE user_id = $1 AND action_date = $2`,
      [userId, today]
    );

    if (result.rows.length > 0) {
      return result.rows[0];
    }

    // Créer les limites pour aujourd'hui
    result = await pool.query(
      `INSERT INTO hibons_action_limits (user_id, action_date, created_at)
       VALUES ($1, $2, NOW())
       RETURNING *`,
      [userId, today]
    );

    return result.rows[0];
  } catch (error) {
    logger.error('Hibons: Error getting daily limits', { error: error.message, userId });
    return null;
  }
}

/**
 * Vérifier si une action peut être récompensée (respect des limites)
 */
export async function canRewardAction(userId, actionType) {
  if (!pool) return { allowed: false, reason: 'service_unavailable' };

  try {
    const limits = await getOrCreateDailyLimits(userId);
    if (!limits) return { allowed: false, reason: 'error' };

    const limitField = actionType.toLowerCase().replace('daily_', '').replace('review_', 'review_');
    const fieldMap = {
      'daily_search': 'searches',
      'search': 'searches',
      'view_event': 'views',
      'favorite': 'favorites',
      'share': 'shares',
      'review': 'reviews',
      'review_photo': 'review_photos',
      'review_video': 'review_videos',
      'chat_feedback': 'chat_feedbacks'
    };

    const dbField = fieldMap[actionType.toLowerCase()] || actionType.toLowerCase();
    const maxLimit = DAILY_LIMITS[actionType.toUpperCase()] || DAILY_LIMITS[actionType.toUpperCase().replace('DAILY_', '')];

    if (!maxLimit) {
      // Pas de limite pour cette action
      return { allowed: true };
    }

    const currentCount = limits[dbField] || 0;

    if (currentCount >= maxLimit) {
      return {
        allowed: false,
        reason: 'daily_limit_reached',
        current: currentCount,
        limit: maxLimit
      };
    }

    return {
      allowed: true,
      current: currentCount,
      limit: maxLimit,
      remaining: maxLimit - currentCount - 1
    };
  } catch (error) {
    logger.error('Hibons: Error checking action limit', { error: error.message, userId, actionType });
    return { allowed: false, reason: 'error' };
  }
}

/**
 * Incrémenter le compteur d'action quotidienne
 */
async function incrementActionCount(userId, actionType) {
  const today = new Date().toISOString().split('T')[0];

  const fieldMap = {
    'DAILY_SEARCH': 'searches',
    'SEARCH': 'searches',
    'VIEW_EVENT': 'views',
    'FAVORITE': 'favorites',
    'SHARE': 'shares',
    'REVIEW': 'reviews',
    'REVIEW_PHOTO': 'review_photos',
    'REVIEW_VIDEO': 'review_videos',
    'CHAT_FEEDBACK': 'chat_feedbacks'
  };

  const dbField = fieldMap[actionType.toUpperCase()];
  if (!dbField) return;

  try {
    await pool.query(
      `INSERT INTO hibons_action_limits (user_id, action_date, ${dbField}, created_at)
       VALUES ($1, $2, 1, NOW())
       ON CONFLICT (user_id, action_date)
       DO UPDATE SET ${dbField} = hibons_action_limits.${dbField} + 1, updated_at = NOW()`,
      [userId, today]
    );
  } catch (error) {
    logger.error('Hibons: Error incrementing action count', { error: error.message, userId, actionType });
  }
}

/**
 * Créditer des Hibons à un utilisateur
 */
export async function creditHibons(userId, category, options = {}) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  const {
    amount,           // Montant fixe (si non défini, utilise REWARDS)
    description,
    referenceType,
    referenceId,
    bookingAmount,    // Pour calculer le % sur réservation
    skipLimitCheck = false
  } = options;

  try {
    // Vérifier les limites quotidiennes
    if (!skipLimitCheck && DAILY_LIMITS[category.toUpperCase()]) {
      const canReward = await canRewardAction(userId, category);
      if (!canReward.allowed) {
        return { success: false, error: canReward.reason, details: canReward };
      }
    }

    // Calculer le montant à créditer
    const rewardConfig = REWARDS[category.toUpperCase()];
    let baseAmount = amount;

    if (!baseAmount && rewardConfig) {
      if (rewardConfig.hibonsPercent && bookingAmount) {
        baseAmount = Math.round(bookingAmount * rewardConfig.hibonsPercent / 100);
        baseAmount = Math.max(rewardConfig.min || 0, Math.min(baseAmount, rewardConfig.max || Infinity));
      } else {
        baseAmount = rewardConfig.hibons || 0;
      }
    }

    if (!baseAmount || baseAmount <= 0) {
      return { success: false, error: 'Invalid amount' };
    }

    const xp = rewardConfig?.xp || 0;

    // S'assurer que le wallet existe
    await walletService.getOrCreateWallet(userId);

    // Utiliser la fonction PostgreSQL
    const result = await pool.query(
      `SELECT * FROM credit_hibons($1, $2, $3, $4, $5, $6, $7)`,
      [userId, baseAmount, category.toUpperCase(), description, referenceType, referenceId, xp]
    );

    const txResult = result.rows[0];

    // Incrémenter le compteur d'action si applicable
    if (!skipLimitCheck) {
      await incrementActionCount(userId, category);
    }

    // Vérifier le level up
    const levelUpdate = await walletService.updateLevel(userId);

    logger.info('Hibons: Credit successful', {
      userId,
      category,
      baseAmount,
      finalAmount: txResult.final_amount,
      newBalance: txResult.new_balance
    });

    return {
      success: true,
      transaction: {
        id: txResult.transaction_id,
        amount: txResult.final_amount,
        baseAmount,
        xpEarned: txResult.xp_earned
      },
      newBalance: txResult.new_balance,
      levelUp: levelUpdate?.leveledUp ? levelUpdate : null
    };
  } catch (error) {
    logger.error('Hibons: Error crediting', { error: error.message, userId, category });
    return { success: false, error: error.message };
  }
}

/**
 * Débiter des Hibons d'un utilisateur
 */
export async function debitHibons(userId, amount, category, options = {}) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  const { description, referenceType, referenceId } = options;

  try {
    // Vérifier le solde
    const balance = await walletService.getBalance(userId);
    if (!balance || balance.balance < amount) {
      return {
        success: false,
        error: 'insufficient_balance',
        currentBalance: balance?.balance || 0,
        required: amount
      };
    }

    // Utiliser la fonction PostgreSQL
    const result = await pool.query(
      `SELECT * FROM debit_hibons($1, $2, $3, $4, $5, $6)`,
      [userId, amount, category, description, referenceType, referenceId]
    );

    const txResult = result.rows[0];

    logger.info('Hibons: Debit successful', {
      userId,
      category,
      amount,
      newBalance: txResult.new_balance
    });

    return {
      success: true,
      transaction: {
        id: txResult.transaction_id,
        amount: -amount
      },
      newBalance: txResult.new_balance
    };
  } catch (error) {
    // Vérifier si c'est une erreur de solde insuffisant
    if (error.message.includes('Insufficient balance')) {
      return { success: false, error: 'insufficient_balance' };
    }
    logger.error('Hibons: Error debiting', { error: error.message, userId, category });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir l'historique des transactions
 */
export async function getTransactionHistory(userId, options = {}) {
  if (!pool) return [];

  const { limit = 50, offset = 0, type, category, from, to } = options;

  try {
    let query = `
      SELECT
        id,
        type,
        category,
        amount,
        balance_after,
        base_amount,
        multiplier_applied,
        xp_earned,
        description,
        reference_type,
        reference_id,
        status,
        created_at
      FROM hibons_transactions
      WHERE user_id = $1
    `;
    const params = [userId];
    let paramIndex = 2;

    if (type) {
      query += ` AND type = $${paramIndex}`;
      params.push(type.toUpperCase());
      paramIndex++;
    }

    if (category) {
      query += ` AND category = $${paramIndex}`;
      params.push(category.toUpperCase());
      paramIndex++;
    }

    if (from) {
      query += ` AND created_at >= $${paramIndex}`;
      params.push(from);
      paramIndex++;
    }

    if (to) {
      query += ` AND created_at <= $${paramIndex}`;
      params.push(to);
      paramIndex++;
    }

    query += ` ORDER BY created_at DESC LIMIT $${paramIndex} OFFSET $${paramIndex + 1}`;
    params.push(limit, offset);

    const result = await pool.query(query, params);

    return result.rows.map(row => ({
      id: row.id,
      type: row.type,
      category: row.category,
      amount: row.amount,
      balanceAfter: row.balance_after,
      baseAmount: row.base_amount,
      multiplier: row.multiplier_applied,
      xpEarned: row.xp_earned,
      description: row.description,
      referenceType: row.reference_type,
      referenceId: row.reference_id,
      status: row.status,
      createdAt: row.created_at
    }));
  } catch (error) {
    logger.error('Hibons: Error getting transaction history', { error: error.message, userId });
    return [];
  }
}

/**
 * Obtenir le résumé des transactions sur une période
 */
export async function getTransactionSummary(userId, days = 30) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         SUM(CASE WHEN type = 'EARN' THEN amount ELSE 0 END) as total_earned,
         SUM(CASE WHEN type = 'SPEND' THEN ABS(amount) ELSE 0 END) as total_spent,
         SUM(CASE WHEN type = 'BONUS' THEN amount ELSE 0 END) as total_bonus,
         COUNT(CASE WHEN type = 'EARN' THEN 1 END) as earn_count,
         COUNT(CASE WHEN type = 'SPEND' THEN 1 END) as spend_count,
         SUM(xp_earned) as total_xp
       FROM hibons_transactions
       WHERE user_id = $1
         AND created_at >= NOW() - INTERVAL '1 day' * $2
         AND status = 'completed'`,
      [userId, days]
    );

    const row = result.rows[0];
    return {
      period: `${days} days`,
      earned: parseInt(row.total_earned || 0, 10),
      spent: parseInt(row.total_spent || 0, 10),
      bonus: parseInt(row.total_bonus || 0, 10),
      net: parseInt(row.total_earned || 0, 10) - parseInt(row.total_spent || 0, 10) + parseInt(row.total_bonus || 0, 10),
      earnTransactions: parseInt(row.earn_count || 0, 10),
      spendTransactions: parseInt(row.spend_count || 0, 10),
      xpEarned: parseInt(row.total_xp || 0, 10)
    };
  } catch (error) {
    logger.error('Hibons: Error getting transaction summary', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir les catégories de gains les plus fréquentes
 */
export async function getTopEarnCategories(userId, limit = 5) {
  if (!pool) return [];

  try {
    const result = await pool.query(
      `SELECT
         category,
         COUNT(*) as count,
         SUM(amount) as total
       FROM hibons_transactions
       WHERE user_id = $1 AND type = 'EARN' AND status = 'completed'
       GROUP BY category
       ORDER BY total DESC
       LIMIT $2`,
      [userId, limit]
    );

    return result.rows.map(row => ({
      category: row.category,
      count: parseInt(row.count, 10),
      total: parseInt(row.total, 10)
    }));
  } catch (error) {
    logger.error('Hibons: Error getting top earn categories', { error: error.message, userId });
    return [];
  }
}

/**
 * Créditer le bonus de bienvenue (une seule fois)
 */
export async function creditWelcomeBonus(userId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier si déjà crédité
    const existing = await pool.query(
      `SELECT id FROM hibons_transactions
       WHERE user_id = $1 AND category = 'WELCOME' AND status = 'completed'`,
      [userId]
    );

    if (existing.rows.length > 0) {
      return { success: false, error: 'already_credited' };
    }

    return await creditHibons(userId, 'WELCOME', {
      description: 'Bonus de bienvenue',
      skipLimitCheck: true
    });
  } catch (error) {
    logger.error('Hibons: Error crediting welcome bonus', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Traiter la récompense de parrainage
 */
export async function processReferralReward(referrerId, referredId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier le statut du parrainage
    const referral = await pool.query(
      `SELECT * FROM hibons_referrals
       WHERE referrer_user_id = $1 AND referred_user_id = $2`,
      [referrerId, referredId]
    );

    if (referral.rows.length === 0) {
      return { success: false, error: 'referral_not_found' };
    }

    if (referral.rows[0].status === 'rewarded') {
      return { success: false, error: 'already_rewarded' };
    }

    // Créditer le parrain
    await creditHibons(referrerId, 'REFERRAL_REFERRER', {
      description: `Parrainage de l'utilisateur #${referredId}`,
      referenceType: 'referral',
      referenceId: String(referredId),
      skipLimitCheck: true
    });

    // Créditer le filleul
    await creditHibons(referredId, 'REFERRAL_REFERRED', {
      description: 'Bonus de parrainage',
      referenceType: 'referral',
      referenceId: String(referrerId),
      skipLimitCheck: true
    });

    // Mettre à jour le statut
    await pool.query(
      `UPDATE hibons_referrals
       SET status = 'rewarded', rewarded_at = NOW()
       WHERE referrer_user_id = $1 AND referred_user_id = $2`,
      [referrerId, referredId]
    );

    logger.info('Hibons: Referral reward processed', { referrerId, referredId });

    return { success: true };
  } catch (error) {
    logger.error('Hibons: Error processing referral reward', { error: error.message, referrerId, referredId });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir les limites quotidiennes restantes
 */
export async function getDailyLimitsStatus(userId) {
  if (!pool) return null;

  try {
    const limits = await getOrCreateDailyLimits(userId);
    if (!limits) return null;

    return {
      date: limits.action_date,
      actions: {
        search: { used: limits.searches || 0, limit: DAILY_LIMITS.SEARCH, remaining: Math.max(0, DAILY_LIMITS.SEARCH - (limits.searches || 0)) },
        viewEvent: { used: limits.views || 0, limit: DAILY_LIMITS.VIEW_EVENT, remaining: Math.max(0, DAILY_LIMITS.VIEW_EVENT - (limits.views || 0)) },
        favorite: { used: limits.favorites || 0, limit: DAILY_LIMITS.FAVORITE, remaining: Math.max(0, DAILY_LIMITS.FAVORITE - (limits.favorites || 0)) },
        share: { used: limits.shares || 0, limit: DAILY_LIMITS.SHARE, remaining: Math.max(0, DAILY_LIMITS.SHARE - (limits.shares || 0)) },
        review: { used: limits.reviews || 0, limit: DAILY_LIMITS.REVIEW, remaining: Math.max(0, DAILY_LIMITS.REVIEW - (limits.reviews || 0)) },
        reviewPhoto: { used: limits.review_photos || 0, limit: DAILY_LIMITS.REVIEW_PHOTO, remaining: Math.max(0, DAILY_LIMITS.REVIEW_PHOTO - (limits.review_photos || 0)) },
        reviewVideo: { used: limits.review_videos || 0, limit: DAILY_LIMITS.REVIEW_VIDEO, remaining: Math.max(0, DAILY_LIMITS.REVIEW_VIDEO - (limits.review_videos || 0)) },
        chatFeedback: { used: limits.chat_feedbacks || 0, limit: DAILY_LIMITS.CHAT_FEEDBACK, remaining: Math.max(0, DAILY_LIMITS.CHAT_FEEDBACK - (limits.chat_feedbacks || 0)) }
      }
    };
  } catch (error) {
    logger.error('Hibons: Error getting daily limits status', { error: error.message, userId });
    return null;
  }
}

export default {
  initPool,
  REWARDS,
  DAILY_LIMITS,
  canRewardAction,
  creditHibons,
  debitHibons,
  getTransactionHistory,
  getTransactionSummary,
  getTopEarnCategories,
  creditWelcomeBonus,
  processReferralReward,
  getDailyLimitsStatus
};
