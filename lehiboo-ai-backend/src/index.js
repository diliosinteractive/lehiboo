/**
 * Le Hiboo AI Backend
 * Serveur Node.js avec AI SDK et OpenAI
 */

import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import rateLimit from 'express-rate-limit';
import config from './config/index.js';
import logger from './utils/logger.js';
import chatRoutes from './routes/chat.js';
import mobileRoutes from './routes/mobile.js';
import { testOpenAIConnection } from './services/ai-service-v2.js';
import weatherService from './services/weather-service.js';
import chatStorage from './services/chat-storage.js';

// Créer l'application Express
const app = express();

// Trust proxy - nécessaire car derrière Plesk reverse proxy
// Permet à express-rate-limit de lire correctement X-Forwarded-For
app.set('trust proxy', 1);

// Middleware de sécurité
app.use(helmet());

// CORS - Autoriser mobile et WordPress
app.use(
  cors({
    origin: (origin, callback) => {
      // Autoriser les requêtes sans origin (apps mobile, Postman, curl)
      if (!origin) {
        return callback(null, true);
      }
      // Autoriser WordPress
      if (config.wordpress.url && origin === config.wordpress.url) {
        return callback(null, true);
      }
      // Autoriser localhost pour dev
      if (origin.includes('localhost') || origin.includes('127.0.0.1')) {
        return callback(null, true);
      }
      // Autoriser tous les sous-domaines lehiboo
      if (origin.includes('lehiboo')) {
        return callback(null, true);
      }
      // Autoriser par défaut (apps mobile n'envoient pas d'origin)
      callback(null, true);
    },
    credentials: true,
  })
);

// Body parser
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Rate limiting global
const limiter = rateLimit({
  windowMs: config.rateLimit.windowMs,
  max: config.rateLimit.maxRequests,
  message: {
    success: false,
    error: 'Too many requests, please try again later.',
  },
  standardHeaders: true,
  legacyHeaders: false,
});

app.use(limiter);

// Logging des requêtes
app.use((req, res, next) => {
  logger.info('Incoming request', {
    method: req.method,
    path: req.path,
    ip: req.ip,
  });
  next();
});

// Routes - Support direct et via proxy /api-planner
app.use('/', chatRoutes);
app.use('/mobile', mobileRoutes);

// Routes avec prefix pour proxy Plesk (preprod.lehiboo.com/api-planner/*)
app.use('/api-planner', chatRoutes);
app.use('/api-planner/mobile', mobileRoutes);

// Route racine
app.get('/', (req, res) => {
  res.json({
    name: 'Le Hiboo AI Backend',
    version: '1.1.0',
    status: 'running',
    endpoints: {
      web: {
        chat: 'POST /chat',
        health: 'GET /health',
        status: 'GET /status'
      },
      mobile: {
        chat: 'POST /mobile/chat',
        search: 'POST /mobile/search',
        categories: 'GET /mobile/categories',
        cities: 'GET /mobile/cities'
      }
    },
  });
});

// 404 Handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    error: 'Not found',
  });
});

// Error Handler
app.use((err, req, res, next) => {
  logger.error('Unhandled error', {
    error: err.message,
    stack: err.stack,
    path: req.path,
  });

  res.status(500).json({
    success: false,
    error: 'Internal server error',
  });
});

// Démarrer le serveur
async function startServer() {
  try {
    // Initialiser le storage (PostgreSQL ou JSON fallback)
    logger.info('Initializing chat storage...');
    const usingPostgres = await chatStorage.initStorage();
    logger.info(usingPostgres ? '✅ PostgreSQL storage ready' : '⚠️  Using JSON file storage (PostgreSQL unavailable)');

    // Tester la connexion OpenAI
    logger.info('Testing OpenAI connection...');
    const openaiOk = await testOpenAIConnection();

    if (!openaiOk) {
      logger.warn('⚠️  OpenAI connection failed - check your API key');
      logger.warn('The server will start anyway, but AI features may not work properly');
    } else {
      logger.info('✅ OpenAI connection successful');
    }

    // Tester la connexion Weather API
    logger.info('Testing Weather API connection...');
    const weatherOk = await weatherService.testConnection();

    if (!weatherOk) {
      logger.warn('⚠️  Weather API connection failed - weather features will be limited');
      logger.warn('Get a free API key at: https://openweathermap.org/api');
    } else {
      logger.info('✅ Weather API connection successful');
    }

    // Démarrer le serveur HTTP
    app.listen(config.port, config.host, () => {
      logger.info(`🚀 Le Hiboo AI Backend started`, {
        env: config.env,
        host: config.host,
        port: config.port,
        url: `http://${config.host}:${config.port}`,
      });

      logger.info('Configuration', {
        model: config.openai.defaultModel,
        rateLimit: `${config.rateLimit.maxRequests} requests per ${config.rateLimit.windowMs}ms`,
        wordpress: config.wordpress.url || 'Not configured',
      });
    });
  } catch (error) {
    logger.error('Failed to start server', {
      error: error.message,
      stack: error.stack,
    });
    process.exit(1);
  }
}

// Gestion propre de l'arrêt
process.on('SIGTERM', () => {
  logger.info('SIGTERM received, shutting down gracefully...');
  process.exit(0);
});

process.on('SIGINT', () => {
  logger.info('SIGINT received, shutting down gracefully...');
  process.exit(0);
});

// Démarrer
startServer();
