/**
 * Chat Storage Service
 * Stockage persistant des messages de chat (JSON file)
 */

import { readFileSync, writeFileSync, existsSync, mkdirSync, accessSync, constants } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import crypto from 'crypto';
import logger from '../utils/logger.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Chemin du fichier de stockage - configurable via env ou fallback
function getDataDir() {
  // 1. Variable d'environnement explicite
  if (process.env.CHAT_STORAGE_DIR) {
    return process.env.CHAT_STORAGE_DIR;
  }

  // 2. Dossier data dans le projet (dev local)
  const projectDataDir = join(__dirname, '../../data');

  // 3. Vérifier si on peut écrire dans le dossier parent
  try {
    const parentDir = join(__dirname, '../..');
    accessSync(parentDir, constants.W_OK);
    return projectDataDir;
  } catch {
    // 4. Fallback sur /tmp pour Docker/environnements restreints
    logger.warn('Cannot write to project dir, using /tmp for chat storage');
    return '/tmp/lehiboo-chat-data';
  }
}

const DATA_DIR = getDataDir();
const STORAGE_FILE = join(DATA_DIR, 'chat-history.json');

// Cache en mémoire pour les performances
let cache = null;
let persistenceEnabled = true;

/**
 * Initialiser le storage
 */
function initStorage() {
  // Créer le dossier data s'il n'existe pas
  try {
    if (!existsSync(DATA_DIR)) {
      mkdirSync(DATA_DIR, { recursive: true });
      logger.info('Created data directory', { path: DATA_DIR });
    }
  } catch (error) {
    logger.error('Cannot create data directory, running in-memory only', {
      path: DATA_DIR,
      error: error.message
    });
    persistenceEnabled = false;
    cache = { users: {}, version: 1 };
    return;
  }

  // Charger les données existantes ou créer un fichier vide
  if (existsSync(STORAGE_FILE)) {
    try {
      const data = readFileSync(STORAGE_FILE, 'utf-8');
      cache = JSON.parse(data);
      logger.info('Chat history loaded', {
        path: STORAGE_FILE,
        usersCount: Object.keys(cache.users || {}).length,
        totalMessages: Object.values(cache.users || {}).reduce((sum, u) => sum + (u.messages?.length || 0), 0)
      });
    } catch (error) {
      logger.error('Failed to load chat history, creating new', { error: error.message });
      cache = { users: {}, version: 1 };
    }
  } else {
    cache = { users: {}, version: 1 };
    saveStorage();
    logger.info('Created new chat history file', { path: STORAGE_FILE });
  }
}

/**
 * Sauvegarder le storage sur disque
 */
function saveStorage() {
  if (!persistenceEnabled) {
    return; // Mode in-memory uniquement
  }

  try {
    writeFileSync(STORAGE_FILE, JSON.stringify(cache, null, 2));
  } catch (error) {
    logger.error('Failed to save chat history', { error: error.message });
  }
}

/**
 * Obtenir ou créer un utilisateur
 */
function getOrCreateUser(userId) {
  if (!cache) initStorage();

  if (!cache.users[userId]) {
    cache.users[userId] = {
      id: userId,
      messages: [],
      conversations: {},
      createdAt: new Date().toISOString(),
      lastActivity: new Date().toISOString()
    };
    saveStorage();
  }

  return cache.users[userId];
}

/**
 * Sauvegarder un message
 *
 * @param {string} userId - ID de l'utilisateur
 * @param {string} conversationId - ID de la conversation
 * @param {string} role - 'user' ou 'assistant'
 * @param {string} content - Contenu du message
 * @param {Object} metadata - Métadonnées optionnelles (events, searchParams, etc.)
 * @returns {Object} Le message sauvegardé
 */
export function saveMessage(userId, conversationId, role, content, metadata = {}) {
  if (!cache) initStorage();

  const user = getOrCreateUser(userId);

  const message = {
    id: `msg_${crypto.randomUUID().substring(0, 8)}`,
    conversationId,
    role,
    content,
    timestamp: new Date().toISOString(),
    ...metadata
  };

  // Ajouter aux messages de l'utilisateur
  user.messages.push(message);

  // Tracker la conversation
  if (!user.conversations[conversationId]) {
    user.conversations[conversationId] = {
      id: conversationId,
      startedAt: new Date().toISOString(),
      messageCount: 0
    };
  }
  user.conversations[conversationId].messageCount++;
  user.conversations[conversationId].lastMessageAt = new Date().toISOString();

  // Mettre à jour lastActivity
  user.lastActivity = new Date().toISOString();

  // Limiter à 500 messages par utilisateur (FIFO)
  if (user.messages.length > 500) {
    user.messages = user.messages.slice(-500);
  }

  saveStorage();

  logger.info('Message saved', {
    userId,
    conversationId,
    messageId: message.id,
    role,
    totalMessages: user.messages.length
  });

  return message;
}

/**
 * Récupérer l'historique d'un utilisateur
 *
 * @param {string} userId - ID de l'utilisateur
 * @param {Object} options - Options de filtrage
 * @param {number} options.limit - Nombre max de messages (défaut: 50)
 * @param {string} options.conversationId - Filtrer par conversation
 * @param {string} options.before - Messages avant cette date ISO
 * @returns {Array} Liste des messages
 */
export function getHistory(userId, options = {}) {
  if (!cache) initStorage();

  const { limit = 50, conversationId, before } = options;

  const user = cache.users[userId];

  if (!user || !user.messages.length) {
    return [];
  }

  let messages = [...user.messages];

  // Filtrer par conversation
  if (conversationId) {
    messages = messages.filter(m => m.conversationId === conversationId);
  }

  // Filtrer par date
  if (before) {
    messages = messages.filter(m => new Date(m.timestamp) < new Date(before));
  }

  // Trier par date décroissante et limiter
  messages = messages
    .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
    .slice(0, limit);

  // Remettre dans l'ordre chronologique
  messages.reverse();

  logger.info('History retrieved', {
    userId,
    conversationId,
    count: messages.length,
    limit
  });

  return messages;
}

/**
 * Récupérer les conversations d'un utilisateur
 *
 * @param {string} userId - ID de l'utilisateur
 * @returns {Array} Liste des conversations
 */
export function getConversations(userId) {
  if (!cache) initStorage();

  const user = cache.users[userId];

  if (!user || !user.conversations) {
    return [];
  }

  return Object.values(user.conversations)
    .sort((a, b) => new Date(b.lastMessageAt || b.startedAt) - new Date(a.lastMessageAt || a.startedAt));
}

/**
 * Supprimer une conversation
 *
 * @param {string} userId - ID de l'utilisateur
 * @param {string} conversationId - ID de la conversation
 * @returns {boolean} Succès
 */
export function deleteConversation(userId, conversationId) {
  if (!cache) initStorage();

  const user = cache.users[userId];

  if (!user) {
    return false;
  }

  // Supprimer les messages
  user.messages = user.messages.filter(m => m.conversationId !== conversationId);

  // Supprimer la conversation
  delete user.conversations[conversationId];

  saveStorage();

  logger.info('Conversation deleted', { userId, conversationId });

  return true;
}

/**
 * Effacer tout l'historique d'un utilisateur
 *
 * @param {string} userId - ID de l'utilisateur
 * @returns {boolean} Succès
 */
export function clearHistory(userId) {
  if (!cache) initStorage();

  if (cache.users[userId]) {
    cache.users[userId].messages = [];
    cache.users[userId].conversations = {};
    saveStorage();
    logger.info('History cleared', { userId });
    return true;
  }

  return false;
}

/**
 * Obtenir les stats d'un utilisateur
 *
 * @param {string} userId - ID de l'utilisateur
 * @returns {Object} Statistiques
 */
export function getUserStats(userId) {
  if (!cache) initStorage();

  const user = cache.users[userId];

  if (!user) {
    return null;
  }

  return {
    totalMessages: user.messages.length,
    totalConversations: Object.keys(user.conversations).length,
    firstActivity: user.createdAt,
    lastActivity: user.lastActivity
  };
}

// Initialiser au chargement du module
initStorage();

export default {
  saveMessage,
  getHistory,
  getConversations,
  deleteConversation,
  clearHistory,
  getUserStats
};
