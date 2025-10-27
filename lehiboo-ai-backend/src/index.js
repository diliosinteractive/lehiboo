/**
 * Le Hiboo AI Backend
 * Serveur Node.js avec AI SDK et OpenRouter
 */

import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import rateLimit from 'express-rate-limit';
import config from './config/index.js';
import logger from './utils/logger.js';
import chatRoutes from './routes/chat.js';
import { testOpenRouterConnection } from './services/ai-service.js';

// Créer l'application Express
const app = express();

// Middleware de sécurité
app.use(helmet());

// CORS
app.use(
  cors({
    origin: config.wordpress.url || '*',
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

// Routes
app.use('/', chatRoutes);

// Route racine
app.get('/', (req, res) => {
  res.json({
    name: 'Le Hiboo AI Backend',
    version: '1.0.0',
    status: 'running',
    endpoints: {
      chat: 'POST /chat',
      health: 'GET /health',
      status: 'GET /status',
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
    // Tester la connexion OpenRouter
    logger.info('Testing OpenRouter connection...');
    const openrouterOk = await testOpenRouterConnection();

    if (!openrouterOk) {
      logger.warn('OpenRouter connection failed - check your API key');
      if (config.env === 'production') {
        process.exit(1);
      }
    } else {
      logger.info('✅ OpenRouter connection successful');
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
        model: config.openrouter.defaultModel,
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
