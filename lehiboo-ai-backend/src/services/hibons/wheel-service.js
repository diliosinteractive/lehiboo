/**
 * Hibons Wheel Service
 * Gestion de la roue de la fortune
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
 * Coût d'un spin payant en Hibons
 */
const SPIN_COST = 100;

/**
 * Niveau minimum pour spin gratuit quotidien
 */
const FREE_SPIN_MIN_LEVEL = 3;

/**
 * Obtenir la configuration de la roue
 */
export async function getWheelConfig() {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT segment_index, label, color, icon, reward_type, reward_value, reward_duration, probability
       FROM hibons_wheel_config
       WHERE is_active = TRUE
       ORDER BY segment_index`
    );

    return result.rows.map(row => ({
      index: row.segment_index,
      label: row.label,
      color: row.color,
      icon: row.icon,
      reward: {
        type: row.reward_type,
        value: row.reward_value,
        duration: row.reward_duration
      },
      probability: parseFloat(row.probability)
    }));
  } catch (error) {
    logger.error('Hibons: Error getting wheel config', { error: error.message });
    return null;
  }
}

/**
 * Vérifier si l'utilisateur peut tourner la roue
 */
export async function canSpin(userId) {
  if (!pool) return { canSpin: false, reason: 'service_unavailable' };

  try {
    const wallet = await walletService.getOrCreateWallet(userId);
    if (!wallet) {
      return { canSpin: false, reason: 'wallet_error' };
    }

    const now = new Date();
    const today = now.toISOString().split('T')[0];

    // Vérifier le spin gratuit quotidien
    let hasFreeSpinToday = false;

    if (wallet.level >= FREE_SPIN_MIN_LEVEL) {
      // Niveau suffisant pour spin gratuit
      const lastFreeSpin = wallet.last_free_spin_at;

      if (!lastFreeSpin) {
        hasFreeSpinToday = true;
      } else {
        const lastSpinDate = new Date(lastFreeSpin).toISOString().split('T')[0];
        hasFreeSpinToday = lastSpinDate !== today;
      }
    }

    // Vérifier si peut payer
    const canPaySpin = wallet.balance >= SPIN_COST;

    return {
      canSpin: hasFreeSpinToday || canPaySpin,
      hasFreeSpinToday,
      canPaySpin,
      spinCost: SPIN_COST,
      balance: wallet.balance,
      level: wallet.level,
      minLevelForFree: FREE_SPIN_MIN_LEVEL,
      reason: !hasFreeSpinToday && !canPaySpin ? 'insufficient_balance' :
              wallet.level < FREE_SPIN_MIN_LEVEL ? 'level_too_low' : null
    };
  } catch (error) {
    logger.error('Hibons: Error checking can spin', { error: error.message, userId });
    return { canSpin: false, reason: 'error' };
  }
}

/**
 * Tourner la roue
 */
export async function spin(userId, useFree = true) {
  if (!pool) return { success: false, error: 'Service unavailable' };

  try {
    // Vérifier si peut tourner
    const spinCheck = await canSpin(userId);

    if (!spinCheck.canSpin) {
      return { success: false, error: spinCheck.reason };
    }

    // Déterminer le type de spin
    let spinType = 'purchased';
    let costHibons = SPIN_COST;

    if (useFree && spinCheck.hasFreeSpinToday) {
      spinType = 'free';
      costHibons = 0;
    } else if (!spinCheck.canPaySpin) {
      return { success: false, error: 'insufficient_balance' };
    }

    // Si payant, débiter
    if (spinType === 'purchased') {
      const debitResult = await transactionService.debitHibons(userId, SPIN_COST, 'WHEEL_SPIN', {
        description: 'Tour de roue de la fortune'
      });

      if (!debitResult.success) {
        return { success: false, error: debitResult.error };
      }
    }

    // Obtenir la configuration de la roue
    const wheelConfig = await getWheelConfig();
    if (!wheelConfig || wheelConfig.length === 0) {
      return { success: false, error: 'wheel_not_configured' };
    }

    // Sélectionner le segment aléatoirement
    const random = Math.random() * 100;
    let cumulative = 0;
    let selectedSegment = wheelConfig[0];

    for (const segment of wheelConfig) {
      cumulative += segment.probability;
      if (random <= cumulative) {
        selectedSegment = segment;
        break;
      }
    }

    // Appliquer la récompense
    let rewardResult = null;

    switch (selectedSegment.reward.type) {
      case 'hibons':
        const creditResult = await transactionService.creditHibons(userId, 'WHEEL_SPIN', {
          amount: selectedSegment.reward.value,
          description: `Roue de la fortune: ${selectedSegment.label}`,
          skipLimitCheck: true
        });
        rewardResult = {
          type: 'hibons',
          value: selectedSegment.reward.value,
          newBalance: creditResult.newBalance
        };
        break;

      case 'xp':
        await pool.query(
          `UPDATE hibons_wallets SET xp = xp + $1, updated_at = NOW() WHERE user_id = $2`,
          [selectedSegment.reward.value, userId]
        );
        const levelUpdate = await walletService.updateLevel(userId);
        rewardResult = {
          type: 'xp',
          value: selectedSegment.reward.value,
          levelUp: levelUpdate?.leveledUp ? levelUpdate : null
        };
        break;

      case 'multiplier':
        await walletService.activateMultiplier(userId, selectedSegment.reward.value / 100, selectedSegment.reward.duration);
        rewardResult = {
          type: 'multiplier',
          value: selectedSegment.reward.value / 100, // 150 -> 1.5
          duration: selectedSegment.reward.duration
        };
        break;

      case 'respin':
        rewardResult = {
          type: 'respin',
          message: 'Tu peux rejouer!'
        };
        break;

      case 'nothing':
        rewardResult = {
          type: 'nothing',
          message: 'Pas de chance cette fois!'
        };
        break;
    }

    // Marquer le spin gratuit comme utilisé
    if (spinType === 'free') {
      await pool.query(
        `UPDATE hibons_wallets SET last_free_spin_at = NOW(), updated_at = NOW() WHERE user_id = $1`,
        [userId]
      );
    }

    // Enregistrer le spin
    await pool.query(
      `INSERT INTO hibons_wheel_spins (user_id, segment_index, reward_type, reward_value, spin_type, cost_hibons, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, NOW())`,
      [userId, selectedSegment.index, selectedSegment.reward.type, selectedSegment.reward.value, spinType, costHibons]
    );

    logger.info('Hibons: Wheel spin completed', {
      userId,
      spinType,
      segment: selectedSegment.index,
      rewardType: selectedSegment.reward.type,
      rewardValue: selectedSegment.reward.value
    });

    return {
      success: true,
      spinType,
      costHibons,
      result: {
        segmentIndex: selectedSegment.index,
        label: selectedSegment.label,
        color: selectedSegment.color,
        icon: selectedSegment.icon,
        reward: rewardResult
      },
      canSpinAgain: selectedSegment.reward.type === 'respin'
    };
  } catch (error) {
    logger.error('Hibons: Error spinning wheel', { error: error.message, userId });
    return { success: false, error: error.message };
  }
}

/**
 * Obtenir l'historique des spins
 */
export async function getSpinHistory(userId, limit = 20) {
  if (!pool) return [];

  try {
    const result = await pool.query(
      `SELECT
         ws.id, ws.segment_index, ws.reward_type, ws.reward_value,
         ws.spin_type, ws.cost_hibons, ws.created_at,
         wc.label, wc.color, wc.icon
       FROM hibons_wheel_spins ws
       LEFT JOIN hibons_wheel_config wc ON wc.segment_index = ws.segment_index
       WHERE ws.user_id = $1
       ORDER BY ws.created_at DESC
       LIMIT $2`,
      [userId, limit]
    );

    return result.rows.map(row => ({
      id: row.id,
      segment: {
        index: row.segment_index,
        label: row.label,
        color: row.color,
        icon: row.icon
      },
      reward: {
        type: row.reward_type,
        value: row.reward_value
      },
      spinType: row.spin_type,
      cost: row.cost_hibons,
      createdAt: row.created_at
    }));
  } catch (error) {
    logger.error('Hibons: Error getting spin history', { error: error.message, userId });
    return [];
  }
}

/**
 * Obtenir les statistiques de la roue
 */
export async function getWheelStats(userId) {
  if (!pool) return null;

  try {
    const result = await pool.query(
      `SELECT
         COUNT(*) as total_spins,
         COUNT(*) FILTER (WHERE spin_type = 'free') as free_spins,
         COUNT(*) FILTER (WHERE spin_type = 'purchased') as paid_spins,
         SUM(cost_hibons) as total_spent,
         SUM(CASE WHEN reward_type = 'hibons' THEN reward_value ELSE 0 END) as total_hibons_won,
         MAX(CASE WHEN reward_type = 'hibons' THEN reward_value ELSE 0 END) as biggest_win
       FROM hibons_wheel_spins
       WHERE user_id = $1`,
      [userId]
    );

    const row = result.rows[0];

    return {
      totalSpins: parseInt(row.total_spins || 0, 10),
      freeSpins: parseInt(row.free_spins || 0, 10),
      paidSpins: parseInt(row.paid_spins || 0, 10),
      totalSpent: parseInt(row.total_spent || 0, 10),
      totalWon: parseInt(row.total_hibons_won || 0, 10),
      biggestWin: parseInt(row.biggest_win || 0, 10),
      netGain: parseInt(row.total_hibons_won || 0, 10) - parseInt(row.total_spent || 0, 10)
    };
  } catch (error) {
    logger.error('Hibons: Error getting wheel stats', { error: error.message, userId });
    return null;
  }
}

/**
 * Obtenir l'état complet de la roue pour un utilisateur
 */
export async function getWheelStatus(userId) {
  if (!pool) return null;

  try {
    const [config, spinCheck, stats] = await Promise.all([
      getWheelConfig(),
      canSpin(userId),
      getWheelStats(userId)
    ]);

    return {
      config,
      canSpin: spinCheck.canSpin,
      spinOptions: {
        hasFreeSpinToday: spinCheck.hasFreeSpinToday,
        canPaySpin: spinCheck.canPaySpin,
        spinCost: SPIN_COST,
        minLevelForFree: FREE_SPIN_MIN_LEVEL
      },
      userLevel: spinCheck.level,
      balance: spinCheck.balance,
      stats
    };
  } catch (error) {
    logger.error('Hibons: Error getting wheel status', { error: error.message, userId });
    return null;
  }
}

export default {
  initPool,
  SPIN_COST,
  FREE_SPIN_MIN_LEVEL,
  getWheelConfig,
  canSpin,
  spin,
  getSpinHistory,
  getWheelStats,
  getWheelStatus
};
