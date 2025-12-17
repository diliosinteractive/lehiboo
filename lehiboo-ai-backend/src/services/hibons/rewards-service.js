/**
 * Hibons Rewards Service
 * Gestion des daily rewards, streaks et bonus
 */

import pg from 'pg';
import logger from '../../utils/logger.js';
import * as walletService from './wallet-service.js';
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
 * Configuration des récompenses quotidiennes (séquence 7 jours)
 */
const DAILY_REWARDS_SEQUENCE = [
  { day: 1, hibons: 10, xp: 5, bonus: null },
  { day: 2, hibons: 15, xp: 5, bonus: null },
  { day: 3, hibons: 25, xp: 10, bonus: null },
  { day: 4, hibons: 35, xp: 10, bonus: { type: 'multiplier', value: 1.2, duration: 3600, label: 'x1.2 pendant 1h' } },
  { day: 5, hibons: 50, xp: 15, bonus: null },
  { day: 6, hibons: 75, xp: 20, bonus: { type: 'wheel_spin', label: 'Tour de roue gratuit' } },
  { day: 7, hibons: 100, xp: 30, bonus: { type: 'mystery_chest', label: 'Coffre mystère' } }
];

/**
 * Bonus pour semaines consécutives complétées
 */
const WEEKLY_STREAK_BONUS = {
  2: 1.1,   // +10% sur la semaine
  4: 1.2,   // +20%
  8: 1.3,   // +30%
  12: 1.5   // +50%
};

/**
 * Contenu possible du coffre mystère
 */
const MYSTERY_CHEST_REWARDS = [
  { type: 'hibons', value: 50, probability: 30, label: '50 Hibons' },
  { type: 'hibons', value: 100, probability: 25, label: '100 Hibons' },
  { type: 'hibons', value: 200, probability: 15, label: '200 Hibons' },
  { type: 'hibons', value: 500, probability: 5, label: '500 Hibons' },
  { type: 'multiplier', value: 1.5, duration: 3600, probability: 15, label: 'x1.5 pendant 1h' },
  { type: 'multiplier', value: 2.0, duration: 1800, probability: 5, label: 'x2 pendant 30min' },
  { type: 'xp', value: 100, probability: 5, label: '+100 XP' }
];

/**
 * Obtenir l'état des daily rewards
 */
export async function getDailyRewardStatus(userId) {
  if (!pool) return null;

  try {
    const today = new Date().toISOString().split('T')[0];

    // Obtenir ou créer l'état des daily rewards
    let result = await pool.query(
      `SELECT * FROM hibons_daily_rewards WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) {
      // Créer l'entrée
      result = await pool.query(
        `INSERT INTO hibons_daily_rewards (user_id, sequence_day, created_at)
         VALUES ($1, 0, NOW())
         RETURNING *`,
        [userId]
      );
    }

    const dailyReward = result.rows[0];
    const lastClaim = dailyReward.last_claim_date;
    const sequenceDay = dailyReward.sequence_day || 0;
    const consecutiveWeeks = dailyReward.consecutive_weeks || 0;

    // Déterminer si peut réclamer aujourd'hui
    let canClaim = false;
    let nextDay = sequenceDay;

    if (!lastClaim) {
      // Jamais réclamé
      canClaim = true;
      nextDay = 1;
    } else {
      const lastClaimDate = new Date(lastClaim);
      const todayDate = new Date(today);
      const diffDays = Math.floor((todayDate - lastClaimDate) / (1000 * 60 * 60 * 24));

      if (diffDays === 0) {
        // Déjà réclamé aujourd'hui
        canClaim = false;
        nextDay = sequenceDay;
      } else if (diffDays === 1) {
        // Jour suivant consécutif
        canClaim = true;
        nextDay = sequenceDay >= 7 ? 1 : sequenceDay + 1;
      } else {
        // Séquence perdue
        canClaim = true;
        nextDay = 1;
      }
    }

    // Obtenir la récompense du prochain jour
    const nextReward = DAILY_REWARDS_SEQUENCE.find(r => r.day === nextDay) || DAILY_REWARDS_SEQUENCE[0];

    // Calculer le bonus de semaines consécutives
    let weeklyMultiplier = 1.0;
    for (const [weeks, mult] of Object.entries(WEEKLY_STREAK_BONUS).reverse()) {
      if (consecutiveWeeks >= parseInt(weeks)) {
        weeklyMultiplier = mult;
        break;
      }
    }

    return {
      canClaim,
      currentDay: canClaim ? nextDay : sequenceDay,
      lastClaimDate: lastClaim,
      consecutiveWeeks,
      weeklyMultiplier,
      nextReward: canClaim ? {
        ...nextReward,
        adjustedHibons: Math.round(nextReward.hibons * weeklyMultiplier)
      } : null,
      sequence: DAILY_REWARDS_SEQUENCE.map(r => ({
        ...r,
        completed: r.day < nextDay || (r.day === nextDay && !canClaim),
        current: r.day === nextDay && canClaim
      })),
      claimsThisMonth: dailyReward.claims_this_month || 0
    };
  } catch (error) {
    logger.error('Hibons: Error getting daily reward status', { error: error.message, userId });
    return null;
  }
}

/**
 * Réclamer la récompense quotidienne
 */
export async function claimDailyReward(userId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    const status = await getDailyRewardStatus(userId);

    if (!status) {
      return { success: false, error: 'Unable to get status' };
    }

    if (!status.canClaim) {
      return {
        success: false,
        error: 'already_claimed',
        nextClaimAt: getNextMidnight()
      };
    }

    const today = new Date().toISOString().split('T')[0];
    const reward = status.nextReward;
    const newDay = status.currentDay;

    // Vérifier si on complète une semaine
    let newConsecutiveWeeks = status.consecutiveWeeks;
    if (newDay === 7) {
      newConsecutiveWeeks += 1;
    } else if (newDay === 1 && status.consecutiveWeeks > 0) {
      // Reset si on revient au jour 1 (sauf si c'est le premier jour)
      const lastClaim = status.lastClaimDate ? new Date(status.lastClaimDate) : null;
      if (lastClaim) {
        const daysSinceLastClaim = Math.floor((new Date(today) - lastClaim) / (1000 * 60 * 60 * 24));
        if (daysSinceLastClaim > 1) {
          newConsecutiveWeeks = 0;
        }
      }
    }

    // Reset du compteur mensuel si nécessaire
    const currentMonth = new Date().toISOString().slice(0, 7);
    const monthReset = status.monthResetDate;
    let claimsThisMonth = status.claimsThisMonth;
    if (!monthReset || !monthReset.startsWith(currentMonth)) {
      claimsThisMonth = 0;
    }

    // Mettre à jour l'état
    await pool.query(
      `UPDATE hibons_daily_rewards
       SET sequence_day = $1,
           last_claim_date = $2,
           consecutive_weeks = $3,
           claims_this_month = $4,
           month_reset_date = $5,
           updated_at = NOW()
       WHERE user_id = $6`,
      [newDay, today, newConsecutiveWeeks, claimsThisMonth + 1, today, userId]
    );

    // Créditer les Hibons
    const creditResult = await transactionService.creditHibons(userId, 'DAILY_REWARD', {
      amount: reward.adjustedHibons,
      description: `Récompense quotidienne - Jour ${newDay}`,
      skipLimitCheck: true
    });

    // Mettre à jour le streak
    await walletService.updateStreak(userId);

    // Traiter les bonus spéciaux
    let bonusResult = null;
    if (reward.bonus) {
      bonusResult = await processDailyBonus(userId, reward.bonus);
    }

    logger.info('Hibons: Daily reward claimed', {
      userId,
      day: newDay,
      hibons: reward.adjustedHibons,
      bonus: reward.bonus?.type
    });

    return {
      success: true,
      day: newDay,
      hibons: reward.adjustedHibons,
      xp: reward.xp,
      bonus: bonusResult,
      consecutiveWeeks: newConsecutiveWeeks,
      weeklyMultiplier: status.weeklyMultiplier,
      newBalance: creditResult.newBalance,
      levelUp: creditResult.levelUp,
      nextClaimAt: getNextMidnight()
    };
  } catch (error) {
    logger.error('Hibons: Error claiming daily reward', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Traiter les bonus spéciaux des daily rewards
 */
async function processDailyBonus(userId, bonus) {
  switch (bonus.type) {
    case 'multiplier':
      const multResult = await walletService.activateMultiplier(userId, bonus.value, bonus.duration);
      return {
        type: 'multiplier',
        value: bonus.value,
        duration: bonus.duration,
        expiresAt: multResult.expiresAt,
        label: bonus.label
      };

    case 'wheel_spin':
      // Marquer qu'un spin gratuit est disponible
      await pool.query(
        `UPDATE hibons_wallets SET last_free_spin_at = NULL, updated_at = NOW() WHERE user_id = $1`,
        [userId]
      );
      return {
        type: 'wheel_spin',
        label: bonus.label,
        message: 'Tu as gagné un tour de roue gratuit!'
      };

    case 'mystery_chest':
      return await openMysteryChest(userId);

    default:
      return null;
  }
}

/**
 * Ouvrir un coffre mystère
 */
export async function openMysteryChest(userId) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Sélectionner une récompense aléatoire
    const random = Math.random() * 100;
    let cumulative = 0;
    let selectedReward = MYSTERY_CHEST_REWARDS[0];

    for (const reward of MYSTERY_CHEST_REWARDS) {
      cumulative += reward.probability;
      if (random <= cumulative) {
        selectedReward = reward;
        break;
      }
    }

    // Appliquer la récompense
    let result = null;

    switch (selectedReward.type) {
      case 'hibons':
        const creditResult = await transactionService.creditHibons(userId, 'BONUS', {
          amount: selectedReward.value,
          description: 'Coffre mystère',
          skipLimitCheck: true
        });
        result = {
          type: 'hibons',
          value: selectedReward.value,
          newBalance: creditResult.newBalance
        };
        break;

      case 'multiplier':
        await walletService.activateMultiplier(userId, selectedReward.value, selectedReward.duration);
        result = {
          type: 'multiplier',
          value: selectedReward.value,
          duration: selectedReward.duration
        };
        break;

      case 'xp':
        await pool.query(
          `UPDATE hibons_wallets SET xp = xp + $1, updated_at = NOW() WHERE user_id = $2`,
          [selectedReward.value, userId]
        );
        await walletService.updateLevel(userId);
        result = {
          type: 'xp',
          value: selectedReward.value
        };
        break;
    }

    logger.info('Hibons: Mystery chest opened', { userId, reward: selectedReward.type, value: selectedReward.value });

    return {
      success: true,
      type: 'mystery_chest',
      reward: {
        ...result,
        label: selectedReward.label
      }
    };
  } catch (error) {
    logger.error('Hibons: Error opening mystery chest', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir l'heure du prochain minuit (pour le prochain claim)
 */
function getNextMidnight() {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);
  return tomorrow.toISOString();
}

/**
 * Vérifier et mettre à jour le streak bonus basé sur les semaines
 */
export async function checkWeeklyStreakBonus(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT consecutive_weeks FROM hibons_daily_rewards WHERE user_id = $1`,
      [userId]
    );

    if (result.rows.length === 0) return { multiplier: 1.0, weeks: 0 };

    const weeks = result.rows[0].consecutive_weeks || 0;
    let multiplier = 1.0;

    for (const [w, mult] of Object.entries(WEEKLY_STREAK_BONUS).reverse()) {
      if (weeks >= parseInt(w)) {
        multiplier = mult;
        break;
      }
    }

    return {
      weeks,
      multiplier,
      nextMilestone: getNextWeeklyMilestone(weeks)
    };
  } catch (error) {
    logger.error('Hibons: Error checking weekly streak bonus', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir le prochain palier de semaines
 */
function getNextWeeklyMilestone(currentWeeks) {
  const milestones = Object.keys(WEEKLY_STREAK_BONUS).map(Number).sort((a, b) => a - b);

  for (const milestone of milestones) {
    if (currentWeeks < milestone) {
      return {
        weeks: milestone,
        multiplier: WEEKLY_STREAK_BONUS[milestone],
        remaining: milestone - currentWeeks
      };
    }
  }

  return null; // Tous les paliers atteints
}

/**
 * Obtenir les stats de récompenses d'un utilisateur
 */
export async function getRewardsStats(userId) {
  if (!pool) return null;

  try {
    const [dailyStatus, weeklyBonus, wallet] = await Promise.all([
      getDailyRewardStatus(userId),
      checkWeeklyStreakBonus(userId),
      walletService.getOrCreateWallet(userId)
    ]);

    return {
      dailyReward: dailyStatus,
      weeklyStreak: weeklyBonus,
      currentStreak: {
        days: wallet?.current_streak || 0,
        longest: wallet?.longest_streak || 0,
        shieldActive: wallet?.streak_shield_until && new Date(wallet.streak_shield_until) >= new Date()
      },
      multiplier: {
        active: wallet?.multiplier_expires_at && new Date(wallet.multiplier_expires_at) > new Date(),
        value: wallet?.multiplier || 1.0,
        expiresAt: wallet?.multiplier_expires_at
      }
    };
  } catch (error) {
    logger.error('Hibons: Error getting rewards stats', { error: error.message, userId });
    return null;
  }
}

/**
 * Configuration exportée
 */
export const CONFIG = {
  DAILY_REWARDS_SEQUENCE,
  WEEKLY_STREAK_BONUS,
  MYSTERY_CHEST_REWARDS
};

export default {
  initPool,
  CONFIG,
  getDailyRewardStatus,
  claimDailyReward,
  openMysteryChest,
  checkWeeklyStreakBonus,
  getRewardsStats
};
