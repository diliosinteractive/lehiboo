/**
 * Hibons Wallet Service
 * Gestion du portefeuille utilisateur (solde, niveau, XP, streak)
 */

import pg from 'pg';
import logger from '../../utils/logger.js';

const { Pool } = pg;

let pool = null;

/**
 * Initialiser le pool de connexions (appelé depuis index.js)
 */
export function initPool(existingPool) {
  pool = existingPool;
}

/**
 * Vérifier si le service est prêt
 */
export function isReady() {
  return pool !== null;
}

/**
 * Configuration des niveaux
 */
const LEVELS = [
  { level: 1, title: 'Hibou Curieux', xp: 0, dailyBonus: 1.0 },
  { level: 2, title: 'Hibou Explorateur', xp: 100, dailyBonus: 1.1 },
  { level: 3, title: 'Hibou Aventurier', xp: 300, dailyBonus: 1.1, wheelPerWeek: 1 },
  { level: 4, title: 'Hibou Connaisseur', xp: 600, dailyBonus: 1.2 },
  { level: 5, title: 'Hibou Expert', xp: 1000, dailyBonus: 1.2, exclusiveChallenges: true },
  { level: 6, title: 'Hibou VIP', xp: 1500, dailyBonus: 1.3, prioritySupport: true },
  { level: 7, title: 'Hibou Elite', xp: 2200, dailyBonus: 1.3, wheelPerDay: 1 },
  { level: 8, title: 'Hibou Légendaire', xp: 3000, dailyBonus: 1.5, earlyAccess: true },
  { level: 9, title: 'Grand Hibou', xp: 4000, dailyBonus: 1.5, customBadge: true },
  { level: 10, title: 'Maître Hibou', xp: 5500, dailyBonus: 2.0, allPerks: true },
];

/**
 * Obtenir la configuration d'un niveau
 */
export function getLevelConfig(level) {
  return LEVELS.find(l => l.level === level) || LEVELS[0];
}

/**
 * Calculer le niveau en fonction de l'XP
 */
export function calculateLevel(xp) {
  for (let i = LEVELS.length - 1; i >= 0; i--) {
    if (xp >= LEVELS[i].xp) {
      return LEVELS[i];
    }
  }
  return LEVELS[0];
}

/**
 * Obtenir l'XP nécessaire pour le niveau suivant
 */
export function getXpToNextLevel(currentXp) {
  const currentLevel = calculateLevel(currentXp);
  const nextLevel = LEVELS.find(l => l.level === currentLevel.level + 1);
  if (!nextLevel) return null; // Niveau max atteint
  return {
    current: currentXp,
    needed: nextLevel.xp,
    remaining: nextLevel.xp - currentXp,
    progress: ((currentXp - currentLevel.xp) / (nextLevel.xp - currentLevel.xp)) * 100
  };
}

/**
 * Générer un code de parrainage unique
 */
function generateReferralCode() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  let code = '';
  for (let i = 0; i < 8; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return code;
}

/**
 * Obtenir ou créer le wallet d'un utilisateur
 */
export async function getOrCreateWallet(userId) {
  if (!pool) {
    logger.warn('Hibons: Pool not initialized');
    return null;
  }

  try {
    // Chercher le wallet existant
    let result = await pool.query(
      `SELECT * FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length > 0) {
      const wallet = result.rows[0];
      // Enrichir avec les infos de niveau
      const levelConfig = getLevelConfig(wallet.level);
      const xpProgress = getXpToNextLevel(wallet.xp);
      return {
        ...wallet,
        levelConfig,
        xpProgress
      };
    }

    // Créer un nouveau wallet
    const referralCode = generateReferralCode();
    result = await pool.query(
      `INSERT INTO hibons_wallets (user_id, referral_code, created_at)
       VALUES ($1, $2, NOW())
       RETURNING *`,
      [userId, referralCode]
    );

    const wallet = result.rows[0];
    logger.info('Hibons: New wallet created', { userId, referralCode });

    return {
      ...wallet,
      levelConfig: getLevelConfig(1),
      xpProgress: getXpToNextLevel(0)
    };
  } catch (error) {
    logger.error('Hibons: Error getting/creating wallet', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir le solde d'un utilisateur
 */
export async function getBalance(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT balance, balance_pending, lifetime_earned, lifetime_spent
       FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) {
      // Créer le wallet si inexistant
      const wallet = await getOrCreateWallet(userId);
      return wallet ? {
        balance: wallet.balance,
        pending: wallet.balance_pending,
        lifetimeEarned: wallet.lifetime_earned,
        lifetimeSpent: wallet.lifetime_spent
      } : null;
    }

    const row = result.rows[0];
    return {
      balance: row.balance,
      pending: row.balance_pending,
      lifetimeEarned: row.lifetime_earned,
      lifetimeSpent: row.lifetime_spent
    };
  } catch (error) {
    logger.error('Hibons: Error getting balance', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir le résumé complet du wallet (pour l'app)
 */
export async function getWalletSummary(userId) {
  if (!pool) return null;

  try {
    const wallet = await getOrCreateWallet(userId);
    if (!wallet) return null;

    // Récupérer les stats supplémentaires
    const [streakInfo, achievementsCount, challengesActive] = await Promise.all([
      getStreakInfo(userId),
      getAchievementsCount(userId),
      getActiveChallengesCount(userId)
    ]);

    return {
      balance: wallet.balance,
      pending: wallet.balance_pending,
      level: wallet.level,
      title: wallet.title,
      xp: wallet.xp,
      xpProgress: wallet.xpProgress,
      levelConfig: wallet.levelConfig,
      streak: {
        current: wallet.current_streak,
        longest: wallet.longest_streak,
        shieldActive: wallet.streak_shield_until && new Date(wallet.streak_shield_until) >= new Date(),
        shieldUntil: wallet.streak_shield_until
      },
      multiplier: {
        value: wallet.multiplier,
        active: wallet.multiplier_expires_at && new Date(wallet.multiplier_expires_at) > new Date(),
        expiresAt: wallet.multiplier_expires_at
      },
      referral: {
        code: wallet.referral_code,
        referredBy: wallet.referred_by
      },
      stats: {
        lifetimeEarned: wallet.lifetime_earned,
        lifetimeSpent: wallet.lifetime_spent,
        achievements: achievementsCount,
        activeChallenges: challengesActive
      },
      lastActivity: wallet.last_activity_date,
      memberSince: wallet.created_at
    };
  } catch (error) {
    logger.error('Hibons: Error getting wallet summary', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir les infos de streak
 */
async function getStreakInfo(userId) {
  try {
    const result = await pool.query(
      `SELECT current_streak, longest_streak, last_activity_date, streak_shield_until
       FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );
    return result.rows[0] || { current_streak: 0, longest_streak: 0 };
  } catch (error) {
    return { current_streak: 0, longest_streak: 0 };
  }
}

/**
 * Compter les achievements débloqués
 */
async function getAchievementsCount(userId) {
  try {
    const result = await pool.query(
      `SELECT COUNT(*) as count FROM hibons_user_achievements
       WHERE user_id = $1 AND unlocked_at IS NOT NULL`,
      [userId]
    );
    return parseInt(result.rows[0]?.count || 0, 10);
  } catch (error) {
    return 0;
  }
}

/**
 * Compter les challenges actifs
 */
async function getActiveChallengesCount(userId) {
  try {
    const result = await pool.query(
      `SELECT COUNT(*) as count FROM hibons_user_challenges
       WHERE user_id = $1 AND status = 'active'`,
      [userId]
    );
    return parseInt(result.rows[0]?.count || 0, 10);
  } catch (error) {
    return 0;
  }
}

/**
 * Mettre à jour le streak de l'utilisateur
 */
export async function updateStreak(userId) {
  if (!pool) return null;

  try {
    const today = new Date().toISOString().split('T')[0];

    const result = await pool.query(
      `SELECT current_streak, longest_streak, last_activity_date, streak_shield_until
       FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) {
      await getOrCreateWallet(userId);
      return { streak: 1, isNew: true };
    }

    const wallet = result.rows[0];
    const lastActivity = wallet.last_activity_date;
    let newStreak = wallet.current_streak;
    let lostStreak = false;

    if (!lastActivity) {
      // Premier jour
      newStreak = 1;
    } else {
      const lastDate = new Date(lastActivity);
      const todayDate = new Date(today);
      const diffDays = Math.floor((todayDate - lastDate) / (1000 * 60 * 60 * 24));

      if (diffDays === 0) {
        // Déjà connecté aujourd'hui
        return { streak: newStreak, alreadyUpdated: true };
      } else if (diffDays === 1) {
        // Jour consécutif
        newStreak += 1;
      } else {
        // Streak perdu - vérifier le shield
        if (wallet.streak_shield_until && new Date(wallet.streak_shield_until) >= lastDate) {
          // Shield actif, on garde le streak
          newStreak += 1;
        } else {
          // Streak perdu
          lostStreak = true;
          newStreak = 1;
        }
      }
    }

    // Mettre à jour
    const newLongest = Math.max(newStreak, wallet.longest_streak);
    await pool.query(
      `UPDATE hibons_wallets
       SET current_streak = $1,
           longest_streak = $2,
           last_activity_date = $3,
           streak_shield_until = CASE WHEN streak_shield_until <= $3 THEN NULL ELSE streak_shield_until END,
           updated_at = NOW()
       WHERE user_id = $4`,
      [newStreak, newLongest, today, userId]
    );

    logger.info('Hibons: Streak updated', { userId, newStreak, lostStreak });

    return {
      streak: newStreak,
      longest: newLongest,
      lostStreak,
      isNewRecord: newStreak > wallet.longest_streak
    };
  } catch (error) {
    logger.error('Hibons: Error updating streak', { error: error.message, userId });
    return null;
  }
}

/**
 * Activer le streak shield
 */
export async function activateStreakShield(userId, days = 1) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const shieldUntil = new Date();
    shieldUntil.setDate(shieldUntil.getDate() + days);

    await pool.query(
      `UPDATE hibons_wallets
       SET streak_shield_until = $1, updated_at = NOW()
       WHERE user_id = $2`,
      [shieldUntil.toISOString(), userId]
    );

    logger.info('Hibons: Streak shield activated', { userId, until: shieldUntil });

    return { success: true, shieldUntil };
  } catch (error) {
    logger.error('Hibons: Error activating streak shield', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Activer un multiplicateur temporaire
 */
export async function activateMultiplier(userId, multiplier, durationSeconds) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const expiresAt = new Date();
    expiresAt.setSeconds(expiresAt.getSeconds() + durationSeconds);

    await pool.query(
      `UPDATE hibons_wallets
       SET multiplier = $1, multiplier_expires_at = $2, updated_at = NOW()
       WHERE user_id = $3`,
      [multiplier, expiresAt.toISOString(), userId]
    );

    logger.info('Hibons: Multiplier activated', { userId, multiplier, expiresAt });

    return { success: true, multiplier, expiresAt };
  } catch (error) {
    logger.error('Hibons: Error activating multiplier', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Mettre à jour le niveau en fonction de l'XP
 */
export async function updateLevel(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT xp, level FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) return null;

    const { xp, level: currentLevel } = result.rows[0];
    const newLevelConfig = calculateLevel(xp);

    if (newLevelConfig.level !== currentLevel) {
      await pool.query(
        `UPDATE hibons_wallets
         SET level = $1, title = $2, updated_at = NOW()
         WHERE user_id = $3`,
        [newLevelConfig.level, newLevelConfig.title, userId]
      );

      logger.info('Hibons: Level up!', { userId, newLevel: newLevelConfig.level, newTitle: newLevelConfig.title });

      return {
        leveledUp: true,
        newLevel: newLevelConfig.level,
        newTitle: newLevelConfig.title,
        perks: newLevelConfig
      };
    }

    return { leveledUp: false, level: currentLevel };
  } catch (error) {
    logger.error('Hibons: Error updating level', { error: error.message, userId });
    return null;
  }
}

/**
 * Appliquer un code de parrainage
 */
export async function applyReferralCode(userId, referralCode) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier que l'utilisateur n'a pas déjà un parrain
    const userWallet = await pool.query(
      `SELECT referred_by FROM hibons_wallets WHERE user_id = $1`,
      [userId]
    );

    if (userWallet.rows.length > 0 && userWallet.rows[0].referred_by) {
      return { success: false, error: 'already_referred' };
    }

    // Trouver le parrain
    const referrerResult = await pool.query(
      `SELECT user_id FROM hibons_wallets WHERE referral_code = $1`,
      [referralCode.toUpperCase()]
    );

    if (referrerResult.rows.length === 0) {
      return { success: false, error: 'invalid_code' };
    }

    const referrerId = referrerResult.rows[0].user_id;

    if (referrerId === userId) {
      return { success: false, error: 'self_referral' };
    }

    // Créer ou mettre à jour le wallet avec le parrain
    await getOrCreateWallet(userId);
    await pool.query(
      `UPDATE hibons_wallets SET referred_by = $1, updated_at = NOW() WHERE user_id = $2`,
      [referrerId, userId]
    );

    // Créer l'entrée de parrainage
    await pool.query(
      `INSERT INTO hibons_referrals (referrer_user_id, referred_user_id, status, created_at)
       VALUES ($1, $2, 'pending', NOW())
       ON CONFLICT (referred_user_id) DO NOTHING`,
      [referrerId, userId]
    );

    logger.info('Hibons: Referral code applied', { userId, referrerId, code: referralCode });

    return { success: true, referrerId };
  } catch (error) {
    logger.error('Hibons: Error applying referral code', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir les statistiques de parrainage
 */
export async function getReferralStats(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         w.referral_code,
         COUNT(r.id) as total_referrals,
         SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending,
         SUM(CASE WHEN r.status = 'qualified' THEN 1 ELSE 0 END) as qualified,
         SUM(CASE WHEN r.status = 'rewarded' THEN 1 ELSE 0 END) as rewarded,
         SUM(CASE WHEN r.status = 'rewarded' THEN r.referrer_reward ELSE 0 END) as total_earned
       FROM hibons_wallets w
       LEFT JOIN hibons_referrals r ON r.referrer_user_id = w.user_id
       WHERE w.user_id = $1
       GROUP BY w.referral_code`,
      [userId]
    );

    if (result.rows.length === 0) {
      const wallet = await getOrCreateWallet(userId);
      return {
        code: wallet?.referral_code,
        totalReferrals: 0,
        pending: 0,
        qualified: 0,
        rewarded: 0,
        totalEarned: 0
      };
    }

    const row = result.rows[0];
    return {
      code: row.referral_code,
      totalReferrals: parseInt(row.total_referrals || 0, 10),
      pending: parseInt(row.pending || 0, 10),
      qualified: parseInt(row.qualified || 0, 10),
      rewarded: parseInt(row.rewarded || 0, 10),
      totalEarned: parseInt(row.total_earned || 0, 10)
    };
  } catch (error) {
    logger.error('Hibons: Error getting referral stats', { error: error.message, userId });
    return null;
  }
}

// ============================================
// CHAT CREDITS MANAGEMENT
// ============================================

/**
 * Obtenir les crédits de chat disponibles pour un utilisateur
 * Retourne les messages bonus non utilisés et le statut illimité
 */
export async function getChatCredits(userId) {
  if (!pool) return { bonusMessages: 0, unlimited: false, unlimitedUntil: null };

  try {
    // Vérifier le mode illimité actif
    const unlimitedResult = await pool.query(
      `SELECT expires_at FROM hibons_purchases
       WHERE user_id = $1
         AND item_id = 'chat_unlimited_24h'
         AND status = 'completed'
         AND expires_at > NOW()
       ORDER BY expires_at DESC
       LIMIT 1`,
      [userId]
    );

    const unlimited = unlimitedResult.rows.length > 0;
    const unlimitedUntil = unlimited ? unlimitedResult.rows[0].expires_at : null;

    // Compter les messages bonus disponibles
    // Chaque achat stocke credits_remaining dans metadata
    const messagesResult = await pool.query(
      `SELECT COALESCE(SUM((metadata->>'credits_remaining')::int), 0) as total_remaining
       FROM hibons_purchases
       WHERE user_id = $1
         AND item_id IN ('chat_message_1', 'chat_message_5')
         AND status IN ('completed', 'partial')
         AND (metadata->>'credits_remaining')::int > 0`,
      [userId]
    );

    const bonusMessages = parseInt(messagesResult.rows[0]?.total_remaining || 0, 10);

    logger.info('Hibons: Chat credits checked', { userId, bonusMessages, unlimited, unlimitedUntil });

    return {
      bonusMessages,
      unlimited,
      unlimitedUntil
    };
  } catch (error) {
    logger.error('Hibons: Error getting chat credits', { error: error.message, userId });
    return { bonusMessages: 0, unlimited: false, unlimitedUntil: null };
  }
}

/**
 * Consommer un crédit de message chat bonus
 * Retourne true si un crédit a été consommé, false sinon
 */
export async function useChatCredit(userId) {
  if (!pool) return false;

  try {
    // Trouver le premier achat avec des crédits disponibles et décrémenter
    const result = await pool.query(
      `UPDATE hibons_purchases
       SET metadata = jsonb_set(
             metadata,
             '{credits_remaining}',
             to_jsonb((metadata->>'credits_remaining')::int - 1)
           ),
           status = CASE
             WHEN (metadata->>'credits_remaining')::int - 1 = 0 THEN 'used'
             ELSE 'partial'
           END,
           used_at = CASE
             WHEN (metadata->>'credits_remaining')::int - 1 = 0 THEN NOW()
             ELSE used_at
           END
       WHERE id = (
         SELECT id FROM hibons_purchases
         WHERE user_id = $1
           AND item_id IN ('chat_message_1', 'chat_message_5')
           AND status IN ('completed', 'partial')
           AND (metadata->>'credits_remaining')::int > 0
         ORDER BY created_at ASC
         LIMIT 1
       )
       RETURNING id, (metadata->>'credits_remaining')::int as remaining`,
      [userId]
    );

    if (result.rows.length > 0) {
      logger.info('Hibons: Chat credit used', {
        userId,
        purchaseId: result.rows[0].id,
        remaining: result.rows[0].remaining
      });
      return true;
    }

    return false;
  } catch (error) {
    logger.error('Hibons: Error using chat credit', { error: error.message, userId });
    return false;
  }
}

/**
 * Ajouter des crédits de message chat (appelé lors de l'achat)
 */
export async function addChatCredits(userId, itemId, quantity) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Enregistrer l'achat avec credits_remaining = quantity
    const result = await pool.query(
      `INSERT INTO hibons_purchases (user_id, item_id, cost, status, metadata, created_at)
       VALUES ($1, $2, 0, 'completed', $3, NOW())
       RETURNING id`,
      [userId, itemId, JSON.stringify({ quantity, credits_remaining: quantity })]
    );

    logger.info('Hibons: Chat credits added', { userId, itemId, quantity, purchaseId: result.rows[0].id });

    return { success: true, purchaseId: result.rows[0].id, quantity };
  } catch (error) {
    logger.error('Hibons: Error adding chat credits', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Activer le mode chat illimité (24h ou autre durée)
 */
export async function activateChatUnlimited(userId, durationSeconds) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const expiresAt = new Date();
    expiresAt.setSeconds(expiresAt.getSeconds() + durationSeconds);

    const result = await pool.query(
      `INSERT INTO hibons_purchases (user_id, item_id, cost, status, expires_at, metadata, created_at)
       VALUES ($1, 'chat_unlimited_24h', 0, 'completed', $2, $3, NOW())
       RETURNING id`,
      [userId, expiresAt.toISOString(), JSON.stringify({ duration: durationSeconds })]
    );

    logger.info('Hibons: Chat unlimited activated', { userId, expiresAt, purchaseId: result.rows[0].id });

    return { success: true, purchaseId: result.rows[0].id, expiresAt };
  } catch (error) {
    logger.error('Hibons: Error activating chat unlimited', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

export default {
  initPool,
  isReady,
  LEVELS,
  getLevelConfig,
  calculateLevel,
  getXpToNextLevel,
  getOrCreateWallet,
  getBalance,
  getWalletSummary,
  updateStreak,
  activateStreakShield,
  activateMultiplier,
  updateLevel,
  applyReferralCode,
  getReferralStats,
  // Chat credits
  getChatCredits,
  useChatCredit,
  addChatCredits,
  activateChatUnlimited
};
