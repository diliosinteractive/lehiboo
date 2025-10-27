/**
 * Configuration centralisée pour le backend
 */

import dotenv from 'dotenv';

// Charger les variables d'environnement
dotenv.config();

const config = {
  // Server
  env: process.env.NODE_ENV || 'development',
  port: parseInt(process.env.PORT || '3000', 10),
  host: process.env.HOST || '0.0.0.0',

  // OpenRouter
  openrouter: {
    apiKey: process.env.OPENROUTER_API_KEY || '',
    baseUrl: process.env.OPENROUTER_BASE_URL || 'https://openrouter.ai/api/v1',
    defaultModel: process.env.DEFAULT_MODEL || 'anthropic/claude-3.5-sonnet',
  },

  // WordPress
  wordpress: {
    url: process.env.WORDPRESS_URL || '',
    apiUrl: process.env.WORDPRESS_API_URL || '',
    apiKey: process.env.WORDPRESS_API_KEY || '',
  },

  // Security
  security: {
    jwtSecret: process.env.JWT_SECRET || 'change-this-in-production',
    apiKey: process.env.API_KEY || '',
  },

  // Rate Limiting
  rateLimit: {
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || '60000', 10),
    maxRequests: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS || '20', 10),
  },

  // Logging
  logging: {
    level: process.env.LOG_LEVEL || 'info',
    file: process.env.LOG_FILE || './logs/app.log',
  },

  // Weather API
  weather: {
    apiKey: process.env.WEATHER_API_KEY || '',
    apiUrl: process.env.WEATHER_API_URL || 'https://api.openweathermap.org/data/2.5',
  },

  // MCP
  mcp: {
    enabled: process.env.MCP_ENABLED === 'true',
    serverUrl: process.env.MCP_SERVER_URL || 'http://localhost:3001',
  },

  // Redis
  redis: {
    enabled: process.env.REDIS_ENABLED === 'true',
    url: process.env.REDIS_URL || 'redis://localhost:6379',
  },

  // Monitoring
  sentry: {
    dsn: process.env.SENTRY_DSN || '',
  },
};

// Validation
if (!config.openrouter.apiKey && config.env === 'production') {
  console.error('❌ OPENROUTER_API_KEY is required in production');
  process.exit(1);
}

if (!config.security.apiKey && config.env === 'production') {
  console.error('❌ API_KEY is required in production');
  process.exit(1);
}

export default config;
