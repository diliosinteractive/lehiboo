/**
 * Hibons Controller
 * Handlers pour les routes de gamification
 */

import {
  walletService,
  transactionService,
  rewardsService,
  achievementsService,
  challengesService,
  leaderboardService,
  wheelService
} from '../services/hibons/index.js';
import logger from '../utils/logger.js';

// ============================================
// WALLET
// ============================================

export async function getWallet(req, res) {
  try {
    const wallet = await walletService.getWalletSummary(req.userId);

    if (!wallet) {
      return res.status(500).json({ success: false, error: 'Unable to get wallet' });
    }

    res.json({ success: true, wallet });
  } catch (error) {
    logger.error('Hibons Controller: getWallet error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getBalance(req, res) {
  try {
    const balance = await walletService.getBalance(req.userId);

    if (!balance) {
      return res.status(500).json({ success: false, error: 'Unable to get balance' });
    }

    res.json({ success: true, ...balance });
  } catch (error) {
    logger.error('Hibons Controller: getBalance error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// TRANSACTIONS
// ============================================

export async function getTransactions(req, res) {
  try {
    const { limit = 50, offset = 0, type, category, from, to } = req.query;

    const transactions = await transactionService.getTransactionHistory(req.userId, {
      limit: parseInt(limit, 10),
      offset: parseInt(offset, 10),
      type,
      category,
      from,
      to
    });

    res.json({ success: true, transactions });
  } catch (error) {
    logger.error('Hibons Controller: getTransactions error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getTransactionSummary(req, res) {
  try {
    const { days = 30 } = req.query;
    const summary = await transactionService.getTransactionSummary(req.userId, parseInt(days, 10));

    res.json({ success: true, summary });
  } catch (error) {
    logger.error('Hibons Controller: getTransactionSummary error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function earnHibons(req, res) {
  try {
    const { category, amount, description, referenceType, referenceId, bookingAmount } = req.body;

    if (!category) {
      return res.status(400).json({ success: false, error: 'category requis' });
    }

    const result = await transactionService.creditHibons(req.userId, category, {
      amount,
      description,
      referenceType,
      referenceId,
      bookingAmount
    });

    if (!result.success) {
      return res.status(400).json(result);
    }

    // Track pour achievements
    await achievementsService.trackAction(req.userId, category);

    // Update challenges
    await challengesService.updateChallengeProgress(req.userId, category.toLowerCase());

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: earnHibons error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function spendHibons(req, res) {
  try {
    const { amount, category, description, referenceType, referenceId } = req.body;

    if (!amount || !category) {
      return res.status(400).json({ success: false, error: 'amount et category requis' });
    }

    const result = await transactionService.debitHibons(req.userId, amount, category, {
      description,
      referenceType,
      referenceId
    });

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: spendHibons error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getDailyLimits(req, res) {
  try {
    const limits = await transactionService.getDailyLimitsStatus(req.userId);
    res.json({ success: true, limits });
  } catch (error) {
    logger.error('Hibons Controller: getDailyLimits error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// DAILY REWARDS & STREAKS
// ============================================

export async function getDailyRewardStatus(req, res) {
  try {
    const status = await rewardsService.getDailyRewardStatus(req.userId);

    if (!status) {
      return res.status(500).json({ success: false, error: 'Unable to get status' });
    }

    res.json({ success: true, ...status });
  } catch (error) {
    logger.error('Hibons Controller: getDailyRewardStatus error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function claimDailyReward(req, res) {
  try {
    const result = await rewardsService.claimDailyReward(req.userId);

    if (!result.success) {
      return res.status(400).json(result);
    }

    // Check streak achievements
    const wallet = await walletService.getOrCreateWallet(req.userId);
    if (wallet) {
      await achievementsService.checkStreakAchievement(req.userId, wallet.current_streak);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: claimDailyReward error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getStreakInfo(req, res) {
  try {
    const wallet = await walletService.getOrCreateWallet(req.userId);

    if (!wallet) {
      return res.status(500).json({ success: false, error: 'Unable to get streak info' });
    }

    res.json({
      success: true,
      streak: {
        current: wallet.current_streak,
        longest: wallet.longest_streak,
        lastActivity: wallet.last_activity_date,
        shieldActive: wallet.streak_shield_until && new Date(wallet.streak_shield_until) >= new Date(),
        shieldUntil: wallet.streak_shield_until
      }
    });
  } catch (error) {
    logger.error('Hibons Controller: getStreakInfo error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function activateStreakShield(req, res) {
  try {
    const SHIELD_COST = 150;

    // Vérifier le solde
    const balance = await walletService.getBalance(req.userId);
    if (!balance || balance.balance < SHIELD_COST) {
      return res.status(400).json({ success: false, error: 'insufficient_balance', required: SHIELD_COST });
    }

    // Débiter
    const debit = await transactionService.debitHibons(req.userId, SHIELD_COST, 'STREAK_SHIELD', {
      description: 'Protection streak 1 jour'
    });

    if (!debit.success) {
      return res.status(400).json(debit);
    }

    // Activer le shield
    const result = await walletService.activateStreakShield(req.userId, 1);

    res.json({
      success: true,
      shieldUntil: result.shieldUntil,
      newBalance: debit.newBalance
    });
  } catch (error) {
    logger.error('Hibons Controller: activateStreakShield error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// ACHIEVEMENTS
// ============================================

export async function getAllAchievements(req, res) {
  try {
    const achievements = await achievementsService.getAllAchievements(false);
    res.json({ success: true, achievements });
  } catch (error) {
    logger.error('Hibons Controller: getAllAchievements error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getUserAchievements(req, res) {
  try {
    const achievements = await achievementsService.getUserAchievements(req.userId);
    res.json({ success: true, achievements });
  } catch (error) {
    logger.error('Hibons Controller: getUserAchievements error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getUnclaimedAchievements(req, res) {
  try {
    const achievements = await achievementsService.getUnclaimedAchievements(req.userId);
    res.json({ success: true, achievements });
  } catch (error) {
    logger.error('Hibons Controller: getUnclaimedAchievements error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function claimAchievement(req, res) {
  try {
    const { id } = req.params;
    const result = await achievementsService.claimAchievement(req.userId, id);

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: claimAchievement error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getAchievementStats(req, res) {
  try {
    const stats = await achievementsService.getAchievementStats(req.userId);
    res.json({ success: true, stats });
  } catch (error) {
    logger.error('Hibons Controller: getAchievementStats error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// CHALLENGES
// ============================================

export async function getActiveChallenges(req, res) {
  try {
    const { type } = req.query;
    const challenges = await challengesService.getActiveChallenges(req.userId, type);

    // Auto-join daily challenges
    if (!type || type === 'daily') {
      await challengesService.autoJoinDailyChallenges(req.userId);
    }

    res.json({ success: true, challenges });
  } catch (error) {
    logger.error('Hibons Controller: getActiveChallenges error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getUserChallenges(req, res) {
  try {
    const { includeCompleted = true } = req.query;
    const challenges = await challengesService.getUserChallenges(req.userId, includeCompleted !== 'false');
    res.json({ success: true, challenges });
  } catch (error) {
    logger.error('Hibons Controller: getUserChallenges error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function joinChallenge(req, res) {
  try {
    const { id } = req.params;
    const result = await challengesService.joinChallenge(req.userId, id);

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: joinChallenge error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function claimChallengeReward(req, res) {
  try {
    const { id } = req.params;
    const result = await challengesService.claimChallengeReward(req.userId, id);

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: claimChallengeReward error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getChallengeStats(req, res) {
  try {
    const stats = await challengesService.getChallengeStats(req.userId);
    res.json({ success: true, stats });
  } catch (error) {
    logger.error('Hibons Controller: getChallengeStats error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// LEADERBOARD
// ============================================

export async function getLeaderboard(req, res) {
  try {
    const { limit = 100 } = req.query;
    const leaderboard = await leaderboardService.getLiveLeaderboard({ limit: parseInt(limit, 10) });
    res.json({ success: true, leaderboard });
  } catch (error) {
    logger.error('Hibons Controller: getLeaderboard error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getWeeklyLeaderboard(req, res) {
  try {
    const { limit = 100 } = req.query;
    const leaderboard = await leaderboardService.getWeeklyLeaderboard(parseInt(limit, 10));
    res.json({ success: true, leaderboard });
  } catch (error) {
    logger.error('Hibons Controller: getWeeklyLeaderboard error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getUserRank(req, res) {
  try {
    const rank = await leaderboardService.getUserRank(req.userId);

    if (!rank) {
      return res.status(404).json({ success: false, error: 'User not found in leaderboard' });
    }

    res.json({ success: true, ...rank });
  } catch (error) {
    logger.error('Hibons Controller: getUserRank error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getTopThree(req, res) {
  try {
    const top = await leaderboardService.getTopThree();
    res.json({ success: true, top });
  } catch (error) {
    logger.error('Hibons Controller: getTopThree error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// WHEEL
// ============================================

export async function getWheelStatus(req, res) {
  try {
    const status = await wheelService.getWheelStatus(req.userId);

    if (!status) {
      return res.status(500).json({ success: false, error: 'Unable to get wheel status' });
    }

    res.json({ success: true, ...status });
  } catch (error) {
    logger.error('Hibons Controller: getWheelStatus error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function spinWheel(req, res) {
  try {
    const { useFree = true } = req.body;
    const result = await wheelService.spin(req.userId, useFree);

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: spinWheel error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function getSpinHistory(req, res) {
  try {
    const { limit = 20 } = req.query;
    const history = await wheelService.getSpinHistory(req.userId, parseInt(limit, 10));
    res.json({ success: true, history });
  } catch (error) {
    logger.error('Hibons Controller: getSpinHistory error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// REFERRAL
// ============================================

export async function getReferralInfo(req, res) {
  try {
    const stats = await walletService.getReferralStats(req.userId);

    if (!stats) {
      return res.status(500).json({ success: false, error: 'Unable to get referral info' });
    }

    res.json({ success: true, referral: stats });
  } catch (error) {
    logger.error('Hibons Controller: getReferralInfo error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function applyReferralCode(req, res) {
  try {
    const { code } = req.body;

    if (!code) {
      return res.status(400).json({ success: false, error: 'code requis' });
    }

    const result = await walletService.applyReferralCode(req.userId, code);

    if (!result.success) {
      return res.status(400).json(result);
    }

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: applyReferralCode error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// SHOP
// ============================================

export async function getShopItems(req, res) {
  try {
    // Pour l'instant, retourner les items depuis la base
    // TODO: Implémenter shop-service.js si besoin de logique complexe
    res.json({
      success: true,
      items: [
        { id: 'discount_5', name: '-5% sur réservation', cost: 200, type: 'discount' },
        { id: 'discount_10', name: '-10% sur réservation', cost: 400, type: 'discount' },
        { id: 'discount_15', name: '-15% sur réservation', cost: 600, type: 'discount' },
        { id: 'discount_20', name: '-20% sur réservation', cost: 800, type: 'discount' },
        { id: 'chat_message_1', name: '+1 message Petit Boo', cost: 50, type: 'feature' },
        { id: 'chat_message_5', name: '+5 messages Petit Boo', cost: 200, type: 'feature' },
        { id: 'chat_unlimited_24h', name: 'Chat illimité 24h', cost: 300, type: 'feature' },
        { id: 'streak_shield', name: 'Streak Shield (1 jour)', cost: 150, type: 'boost' },
        { id: 'multiplier_1_5', name: 'Boost x1.5 (1h)', cost: 100, type: 'boost' },
        { id: 'multiplier_2', name: 'Boost x2 (30min)', cost: 150, type: 'boost' },
        { id: 'wheel_spin', name: 'Tour de roue', cost: 100, type: 'feature' }
      ]
    });
  } catch (error) {
    logger.error('Hibons Controller: getShopItems error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function purchaseShopItem(req, res) {
  try {
    const { itemId } = req.body;

    if (!itemId) {
      return res.status(400).json({ success: false, error: 'itemId requis' });
    }

    // Items et leurs coûts
    const items = {
      'discount_5': { cost: 200, type: 'discount', value: { percent: 5 } },
      'discount_10': { cost: 400, type: 'discount', value: { percent: 10 } },
      'discount_15': { cost: 600, type: 'discount', value: { percent: 15 } },
      'discount_20': { cost: 800, type: 'discount', value: { percent: 20 } },
      'chat_message_1': { cost: 50, type: 'feature', value: { type: 'chat_message', quantity: 1 } },
      'chat_message_5': { cost: 200, type: 'feature', value: { type: 'chat_message', quantity: 5 } },
      'chat_unlimited_24h': { cost: 300, type: 'feature', value: { type: 'chat_unlimited', duration: 86400 } },
      'streak_shield': { cost: 150, type: 'boost', action: 'activateStreakShield' },
      'multiplier_1_5': { cost: 100, type: 'boost', value: { multiplier: 1.5, duration: 3600 } },
      'multiplier_2': { cost: 150, type: 'boost', value: { multiplier: 2.0, duration: 1800 } },
      'wheel_spin': { cost: 100, type: 'feature', action: 'wheelSpin' }
    };

    const item = items[itemId];
    if (!item) {
      return res.status(400).json({ success: false, error: 'item_not_found' });
    }

    // Débiter
    const debit = await transactionService.debitHibons(req.userId, item.cost, itemId.toUpperCase(), {
      description: `Achat: ${itemId}`
    });

    if (!debit.success) {
      return res.status(400).json(debit);
    }

    // Appliquer l'effet selon le type d'item
    let effectResult = null;

    if (item.action === 'activateStreakShield') {
      effectResult = await walletService.activateStreakShield(req.userId, 1);
    } else if (item.action === 'wheelSpin') {
      effectResult = { message: 'Tour de roue disponible' };
    } else if (item.value?.multiplier) {
      effectResult = await walletService.activateMultiplier(req.userId, item.value.multiplier, item.value.duration);
    } else if (item.value?.type === 'chat_message') {
      // Ajouter des crédits de messages chat
      effectResult = await walletService.addChatCredits(req.userId, itemId, item.value.quantity);
      if (effectResult.success) {
        effectResult.message = `+${item.value.quantity} message(s) Petit Boo ajouté(s)`;
      }
    } else if (item.value?.type === 'chat_unlimited') {
      // Activer le mode chat illimité
      effectResult = await walletService.activateChatUnlimited(req.userId, item.value.duration);
      if (effectResult.success) {
        effectResult.message = `Chat illimité activé jusqu'à ${new Date(effectResult.expiresAt).toLocaleString('fr-FR')}`;
      }
    }

    logger.info('Hibons: Shop item purchased', {
      userId: req.userId,
      itemId,
      cost: item.cost,
      effectType: item.value?.type || item.action
    });

    res.json({
      success: true,
      item: itemId,
      cost: item.cost,
      newBalance: debit.newBalance,
      effect: effectResult
    });
  } catch (error) {
    logger.error('Hibons Controller: purchaseShopItem error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

// ============================================
// ADMIN
// ============================================

export async function generateChallenges(req, res) {
  try {
    const [daily, weekly] = await Promise.all([
      challengesService.generateDailyChallenges(),
      challengesService.generateWeeklyChallenges()
    ]);

    res.json({
      success: true,
      daily,
      weekly
    });
  } catch (error) {
    logger.error('Hibons Controller: generateChallenges error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export async function createLeaderboardSnapshot(req, res) {
  try {
    const { periodType = 'weekly' } = req.body;
    const result = await leaderboardService.createLeaderboardSnapshot(periodType);

    res.json(result);
  } catch (error) {
    logger.error('Hibons Controller: createLeaderboardSnapshot error', { error: error.message });
    res.status(500).json({ success: false, error: 'Internal error' });
  }
}

export default {
  getWallet,
  getBalance,
  getTransactions,
  getTransactionSummary,
  earnHibons,
  spendHibons,
  getDailyLimits,
  getDailyRewardStatus,
  claimDailyReward,
  getStreakInfo,
  activateStreakShield,
  getAllAchievements,
  getUserAchievements,
  getUnclaimedAchievements,
  claimAchievement,
  getAchievementStats,
  getActiveChallenges,
  getUserChallenges,
  joinChallenge,
  claimChallengeReward,
  getChallengeStats,
  getLeaderboard,
  getWeeklyLeaderboard,
  getUserRank,
  getTopThree,
  getWheelStatus,
  spinWheel,
  getSpinHistory,
  getReferralInfo,
  applyReferralCode,
  getShopItems,
  purchaseShopItem,
  generateChallenges,
  createLeaderboardSnapshot
};
