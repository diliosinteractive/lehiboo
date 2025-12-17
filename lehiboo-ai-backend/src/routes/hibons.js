/**
 * Routes Hibons - API de gamification
 */

import express from 'express';
import * as hibonsController from '../controllers/hibons-controller.js';
import { validateApiKey } from '../middleware/auth.js';

const router = express.Router();

/**
 * Middleware pour valider userId
 */
const validateUserId = (req, res, next) => {
  const userId = req.query.userId || req.body.userId;

  if (!userId) {
    return res.status(400).json({
      success: false,
      error: 'userId requis'
    });
  }

  req.userId = parseInt(userId, 10);

  if (isNaN(req.userId)) {
    return res.status(400).json({
      success: false,
      error: 'userId invalide'
    });
  }

  next();
};

// ============================================
// WALLET
// ============================================

/**
 * GET /hibons/wallet - Obtenir le wallet complet
 */
router.get('/wallet', validateApiKey, validateUserId, hibonsController.getWallet);

/**
 * GET /hibons/balance - Obtenir le solde
 */
router.get('/balance', validateApiKey, validateUserId, hibonsController.getBalance);

// ============================================
// TRANSACTIONS
// ============================================

/**
 * GET /hibons/transactions - Historique des transactions
 */
router.get('/transactions', validateApiKey, validateUserId, hibonsController.getTransactions);

/**
 * GET /hibons/transactions/summary - Résumé des transactions
 */
router.get('/transactions/summary', validateApiKey, validateUserId, hibonsController.getTransactionSummary);

/**
 * POST /hibons/earn - Créditer des Hibons (pour actions utilisateur)
 */
router.post('/earn', validateApiKey, validateUserId, hibonsController.earnHibons);

/**
 * POST /hibons/spend - Dépenser des Hibons
 */
router.post('/spend', validateApiKey, validateUserId, hibonsController.spendHibons);

/**
 * GET /hibons/limits - Limites quotidiennes
 */
router.get('/limits', validateApiKey, validateUserId, hibonsController.getDailyLimits);

// ============================================
// DAILY REWARDS & STREAKS
// ============================================

/**
 * GET /hibons/daily-reward - État du daily reward
 */
router.get('/daily-reward', validateApiKey, validateUserId, hibonsController.getDailyRewardStatus);

/**
 * POST /hibons/daily-reward/claim - Réclamer le daily reward
 */
router.post('/daily-reward/claim', validateApiKey, validateUserId, hibonsController.claimDailyReward);

/**
 * GET /hibons/streak - Info streak
 */
router.get('/streak', validateApiKey, validateUserId, hibonsController.getStreakInfo);

/**
 * POST /hibons/streak/shield - Activer le streak shield
 */
router.post('/streak/shield', validateApiKey, validateUserId, hibonsController.activateStreakShield);

// ============================================
// ACHIEVEMENTS
// ============================================

/**
 * GET /hibons/achievements - Tous les achievements
 */
router.get('/achievements', validateApiKey, hibonsController.getAllAchievements);

/**
 * GET /hibons/achievements/progress - Progression utilisateur
 */
router.get('/achievements/progress', validateApiKey, validateUserId, hibonsController.getUserAchievements);

/**
 * GET /hibons/achievements/unclaimed - Achievements à réclamer
 */
router.get('/achievements/unclaimed', validateApiKey, validateUserId, hibonsController.getUnclaimedAchievements);

/**
 * POST /hibons/achievements/:id/claim - Réclamer un achievement
 */
router.post('/achievements/:id/claim', validateApiKey, validateUserId, hibonsController.claimAchievement);

/**
 * GET /hibons/achievements/stats - Statistiques achievements
 */
router.get('/achievements/stats', validateApiKey, validateUserId, hibonsController.getAchievementStats);

// ============================================
// CHALLENGES
// ============================================

/**
 * GET /hibons/challenges - Challenges actifs
 */
router.get('/challenges', validateApiKey, validateUserId, hibonsController.getActiveChallenges);

/**
 * GET /hibons/challenges/my - Mes challenges
 */
router.get('/challenges/my', validateApiKey, validateUserId, hibonsController.getUserChallenges);

/**
 * POST /hibons/challenges/:id/join - Rejoindre un challenge
 */
router.post('/challenges/:id/join', validateApiKey, validateUserId, hibonsController.joinChallenge);

/**
 * POST /hibons/challenges/:id/claim - Réclamer récompense challenge
 */
router.post('/challenges/:id/claim', validateApiKey, validateUserId, hibonsController.claimChallengeReward);

/**
 * GET /hibons/challenges/stats - Statistiques challenges
 */
router.get('/challenges/stats', validateApiKey, validateUserId, hibonsController.getChallengeStats);

// ============================================
// LEADERBOARD
// ============================================

/**
 * GET /hibons/leaderboard - Classement global
 */
router.get('/leaderboard', validateApiKey, hibonsController.getLeaderboard);

/**
 * GET /hibons/leaderboard/weekly - Classement hebdomadaire
 */
router.get('/leaderboard/weekly', validateApiKey, hibonsController.getWeeklyLeaderboard);

/**
 * GET /hibons/leaderboard/rank - Ma position
 */
router.get('/leaderboard/rank', validateApiKey, validateUserId, hibonsController.getUserRank);

/**
 * GET /hibons/leaderboard/top - Top 3
 */
router.get('/leaderboard/top', validateApiKey, hibonsController.getTopThree);

// ============================================
// WHEEL (ROUE DE LA FORTUNE)
// ============================================

/**
 * GET /hibons/wheel - État de la roue
 */
router.get('/wheel', validateApiKey, validateUserId, hibonsController.getWheelStatus);

/**
 * POST /hibons/wheel/spin - Tourner la roue
 */
router.post('/wheel/spin', validateApiKey, validateUserId, hibonsController.spinWheel);

/**
 * GET /hibons/wheel/history - Historique des spins
 */
router.get('/wheel/history', validateApiKey, validateUserId, hibonsController.getSpinHistory);

// ============================================
// REFERRAL (PARRAINAGE)
// ============================================

/**
 * GET /hibons/referral - Mon code et stats de parrainage
 */
router.get('/referral', validateApiKey, validateUserId, hibonsController.getReferralInfo);

/**
 * POST /hibons/referral/apply - Appliquer un code parrain
 */
router.post('/referral/apply', validateApiKey, validateUserId, hibonsController.applyReferralCode);

// ============================================
// SHOP
// ============================================

/**
 * GET /hibons/shop - Catalogue du shop
 */
router.get('/shop', validateApiKey, hibonsController.getShopItems);

/**
 * POST /hibons/shop/purchase - Acheter un item
 */
router.post('/shop/purchase', validateApiKey, validateUserId, hibonsController.purchaseShopItem);

// ============================================
// ADMIN / CRON (à sécuriser davantage en prod)
// ============================================

/**
 * POST /hibons/admin/generate-challenges - Générer challenges quotidiens/hebdo
 */
router.post('/admin/generate-challenges', validateApiKey, hibonsController.generateChallenges);

/**
 * POST /hibons/admin/snapshot-leaderboard - Créer snapshot leaderboard
 */
router.post('/admin/snapshot-leaderboard', validateApiKey, hibonsController.createLeaderboardSnapshot);

export default router;
