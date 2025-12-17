/**
 * Hibons Services Index
 * Point d'entrée pour tous les services de gamification
 */

import * as walletService from './wallet-service.js';
import * as transactionService from './transaction-service.js';
import * as rewardsService from './rewards-service.js';
import * as achievementsService from './achievements-service.js';
import * as challengesService from './challenges-service.js';
import * as leaderboardService from './leaderboard-service.js';
import * as wheelService from './wheel-service.js';
import logger from '../../utils/logger.js';

/**
 * Initialiser tous les services Hibons avec le pool PostgreSQL
 */
export function initHibonsServices(pool) {
  if (!pool) {
    logger.warn('Hibons: No database pool provided, services will be disabled');
    return false;
  }

  walletService.initPool(pool);
  transactionService.initPool(pool);
  rewardsService.initPool(pool);
  achievementsService.initPool(pool);
  challengesService.initPool(pool);
  leaderboardService.initPool(pool);
  wheelService.initPool(pool);

  logger.info('Hibons: All services initialized');
  return true;
}

/**
 * Vérifier si les services sont prêts
 */
export function isReady() {
  return walletService.isReady();
}

export {
  walletService,
  transactionService,
  rewardsService,
  achievementsService,
  challengesService,
  leaderboardService,
  wheelService
};

export default {
  initHibonsServices,
  isReady,
  wallet: walletService,
  transaction: transactionService,
  rewards: rewardsService,
  achievements: achievementsService,
  challenges: challengesService,
  leaderboard: leaderboardService,
  wheel: wheelService
};
