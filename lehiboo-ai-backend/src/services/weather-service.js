/**
 * Service Météo avec Open-Meteo API
 * API 100% GRATUITE, SANS CLÉ, SANS LIMITE
 * https://open-meteo.com
 *
 * Avantages:
 * - Gratuit illimité
 * - Pas de clé API nécessaire
 * - Excellent pour la France
 * - Open Source
 * - Données de qualité (ECMWF, DWD)
 */

import logger from '../utils/logger.js';

/**
 * Classe pour gérer les requêtes météo avec Open-Meteo
 */
class WeatherService {
  constructor() {
    this.baseUrl = 'https://api.open-meteo.com/v1';
    this.geocodingUrl = 'https://geocoding-api.open-meteo.com/v1';
  }

  /**
   * Convertir un nom de ville en coordonnées GPS
   *
   * @param {string} location - Nom de la ville (ex: "Paris", "Lyon")
   * @returns {Object} Coordonnées {lat, lon, name}
   */
  async geocode(location) {
    try {
      logger.info('Geocoding location', { location });

      const url = `${this.geocodingUrl}/search?name=${encodeURIComponent(
        location
      )}&count=1&language=fr&format=json`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`Geocoding error: ${response.status}`);
      }

      const data = await response.json();

      if (!data.results || data.results.length === 0) {
        logger.warn('Location not found', { location });
        // Fallback sur Paris si localisation introuvable
        return {
          lat: 48.8566,
          lon: 2.3522,
          name: 'Paris (par défaut)',
        };
      }

      const result = data.results[0];

      return {
        lat: result.latitude,
        lon: result.longitude,
        name: result.name,
        country: result.country,
      };
    } catch (error) {
      logger.error('Geocoding failed', {
        location,
        error: error.message,
      });

      // Fallback sur Paris
      return {
        lat: 48.8566,
        lon: 2.3522,
        name: 'Paris (par défaut)',
      };
    }
  }

  /**
   * Obtenir la météo actuelle pour une localisation
   *
   * @param {string} location - Nom de la ville
   * @returns {Object} Données météo actuelles
   */
  async getCurrentWeather(location) {
    try {
      logger.info('Fetching current weather', { location });

      // Convertir la ville en coordonnées
      const coords = await this.geocode(location);

      // Appeler l'API Open-Meteo
      const url = `${this.baseUrl}/forecast?` +
        `latitude=${coords.lat}&` +
        `longitude=${coords.lon}&` +
        `current=temperature_2m,precipitation,rain,weathercode,windspeed_10m,winddirection_10m&` +
        `timezone=auto`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`Weather API error: ${response.status}`);
      }

      const data = await response.json();

      return this.formatWeatherData(data, coords);
    } catch (error) {
      logger.error('Error fetching current weather', {
        location,
        error: error.message,
      });
      return null;
    }
  }

  /**
   * Obtenir les prévisions météo sur plusieurs jours
   *
   * @param {string} location - Nom de la ville
   * @param {number} days - Nombre de jours (max 16)
   * @returns {Object} Prévisions météo
   */
  async getForecast(location, days = 7) {
    try {
      logger.info('Fetching weather forecast', { location, days });

      // Convertir la ville en coordonnées
      const coords = await this.geocode(location);

      // Appeler l'API Open-Meteo
      const url = `${this.baseUrl}/forecast?` +
        `latitude=${coords.lat}&` +
        `longitude=${coords.lon}&` +
        `daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode,windspeed_10m_max&` +
        `forecast_days=${days}&` +
        `timezone=auto`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`Weather API error: ${response.status}`);
      }

      const data = await response.json();

      return this.formatForecastData(data, coords);
    } catch (error) {
      logger.error('Error fetching weather forecast', {
        location,
        error: error.message,
      });
      return null;
    }
  }

  /**
   * Obtenir la météo pour une date spécifique
   *
   * @param {string} location - Nom de la ville
   * @param {string} date - Date au format YYYY-MM-DD
   * @returns {Object} Données météo pour cette date
   */
  async getWeatherForDate(location, date) {
    try {
      const forecast = await this.getForecast(location, 16);

      if (!forecast || !forecast.daily) {
        return null;
      }

      // Trouver l'index de la date demandée
      const dateIndex = forecast.daily.time.indexOf(date);

      if (dateIndex === -1) {
        logger.warn('Date not found in forecast', { location, date });
        return null;
      }

      // Extraire les données pour cette date
      return {
        location: forecast.location,
        date: date,
        temperature: {
          max: forecast.daily.temperature_2m_max[dateIndex],
          min: forecast.daily.temperature_2m_min[dateIndex],
        },
        precipitation: forecast.daily.precipitation_sum[dateIndex],
        weatherCode: forecast.daily.weathercode[dateIndex],
        description: this.getWeatherDescription(forecast.daily.weathercode[dateIndex]),
        icon: this.getWeatherIcon(forecast.daily.weathercode[dateIndex]),
      };
    } catch (error) {
      logger.error('Error fetching weather for date', {
        location,
        date,
        error: error.message,
      });
      return null;
    }
  }

  /**
   * Formater les données météo actuelles
   *
   * @param {Object} data - Données brutes de l'API
   * @param {Object} coords - Coordonnées de la localisation
   * @returns {Object} Données météo formatées
   */
  formatWeatherData(data, coords) {
    const current = data.current;

    return {
      location: coords.name,
      temperature: Math.round(current.temperature_2m),
      precipitation: current.precipitation || 0,
      rain: current.rain || 0,
      wind: {
        speed: Math.round(current.windspeed_10m * 3.6), // Conversion m/s → km/h
        direction: current.winddirection_10m,
      },
      weatherCode: current.weathercode,
      description: this.getWeatherDescription(current.weathercode),
      icon: this.getWeatherIcon(current.weathercode),
      timestamp: current.time,
    };
  }

  /**
   * Formater les données de prévisions
   *
   * @param {Object} data - Données brutes de l'API
   * @param {Object} coords - Coordonnées de la localisation
   * @returns {Object} Prévisions formatées
   */
  formatForecastData(data, coords) {
    const daily = data.daily;

    return {
      location: coords.name,
      daily: {
        time: daily.time,
        temperature_2m_max: daily.temperature_2m_max.map(Math.round),
        temperature_2m_min: daily.temperature_2m_min.map(Math.round),
        precipitation_sum: daily.precipitation_sum,
        weathercode: daily.weathercode,
        windspeed_10m_max: daily.windspeed_10m_max.map((speed) => Math.round(speed * 3.6)),
      },
    };
  }

  /**
   * Obtenir la description textuelle d'un code météo
   *
   * @param {number} code - Code météo WMO
   * @returns {string} Description en français
   */
  getWeatherDescription(code) {
    const descriptions = {
      0: 'Ciel dégagé',
      1: 'Principalement dégagé',
      2: 'Partiellement nuageux',
      3: 'Nuageux',
      45: 'Brouillard',
      48: 'Brouillard givrant',
      51: 'Bruine légère',
      53: 'Bruine modérée',
      55: 'Bruine dense',
      56: 'Bruine verglaçante légère',
      57: 'Bruine verglaçante dense',
      61: 'Pluie légère',
      63: 'Pluie modérée',
      65: 'Pluie forte',
      66: 'Pluie verglaçante légère',
      67: 'Pluie verglaçante forte',
      71: 'Neige légère',
      73: 'Neige modérée',
      75: 'Neige forte',
      77: 'Grains de neige',
      80: 'Averses légères',
      81: 'Averses modérées',
      82: 'Averses violentes',
      85: 'Averses de neige légères',
      86: 'Averses de neige fortes',
      95: 'Orage',
      96: 'Orage avec grêle légère',
      99: 'Orage avec grêle forte',
    };

    return descriptions[code] || 'Conditions variables';
  }

  /**
   * Obtenir l'icône emoji pour un code météo
   *
   * @param {number} code - Code météo WMO
   * @returns {string} Emoji représentant la météo
   */
  getWeatherIcon(code) {
    const icons = {
      0: '☀️',
      1: '🌤️',
      2: '⛅',
      3: '☁️',
      45: '🌫️',
      48: '🌫️',
      51: '🌦️',
      53: '🌦️',
      55: '🌧️',
      61: '🌧️',
      63: '🌧️',
      65: '⛈️',
      71: '🌨️',
      73: '🌨️',
      75: '❄️',
      80: '🌦️',
      81: '🌧️',
      82: '⛈️',
      95: '⛈️',
      96: '⛈️',
      99: '⛈️',
    };

    return icons[code] || '🌥️';
  }

  /**
   * Analyser la météo et générer des alertes
   *
   * @param {Object} weather - Données météo formatées
   * @returns {Object} Analyse avec type d'alerte et recommandations
   */
  analyzeWeather(weather) {
    let alertType = 'perfect';
    let alertMessage = 'Météo idéale ! Toutes les activités outdoor sont recommandées.';
    let alertIcon = weather.icon;
    let recommendIndoor = false;

    // Analyse de la pluie
    const rain = weather.rain || weather.precipitation;

    if (rain > 5) {
      alertType = rain > 10 ? 'storm' : 'rain';
      alertIcon = rain > 10 ? '⛈️' : '🌧️';
      alertMessage = rain > 10
        ? 'Attention : fortes pluies prévues. Je vous suggère des activités en intérieur pour rester au sec.'
        : 'Pluie prévue. Je vous suggère des activités indoor ou équipez-vous bien !';
      recommendIndoor = true;
    }

    // Analyse de la température
    if (weather.temperature < 5) {
      alertType = 'cold';
      alertIcon = '🥶';
      alertMessage = 'Températures froides. Pensez à vous couvrir chaudement !';
      if (weather.temperature < 0) {
        alertMessage = 'Gel prévu. Privilégiez les activités en intérieur ou équipez-vous contre le froid.';
        recommendIndoor = true;
      }
    } else if (weather.temperature > 30) {
      alertType = 'hot';
      alertIcon = '🌡️';
      alertMessage = 'Forte chaleur prévue. Hydratez-vous bien et évitez les efforts prolongés en plein soleil.';
    }

    // Analyse du vent
    if (weather.wind && weather.wind.speed > 60) {
      alertType = 'storm';
      alertIcon = '💨';
      alertMessage = 'Vents forts prévus. Certaines activités outdoor peuvent être dangereuses.';
      recommendIndoor = true;
    }

    // Analyse du code météo (orage, neige forte)
    if (weather.weatherCode >= 95) {
      alertType = 'storm';
      alertIcon = '⛈️';
      alertMessage = 'Orage prévu. Je vous recommande fortement des activités en intérieur.';
      recommendIndoor = true;
    } else if (weather.weatherCode >= 75 && weather.weatherCode <= 77) {
      alertType = 'cold';
      alertIcon = '❄️';
      alertMessage = 'Fortes chutes de neige prévues. Privilégiez les activités indoor ou équipez-vous bien.';
      recommendIndoor = true;
    }

    return {
      type: alertType,
      icon: alertIcon,
      message: alertMessage,
      recommendIndoor,
    };
  }

  /**
   * Obtenir des recommandations d'activités selon la météo
   *
   * @param {Object} weather - Données météo formatées
   * @returns {Object} Recommandations indoor/outdoor
   */
  getActivityRecommendations(weather) {
    const analysis = this.analyzeWeather(weather);

    if (analysis.recommendIndoor) {
      return {
        indoor: [
          'Escape games',
          'Musées et expositions',
          'Bowling',
          'Cinéma',
          'Théâtre',
          'Piscine couverte',
          'Ateliers créatifs',
          'Karting indoor',
        ],
        outdoor: [],
        message: 'Avec cette météo, je vous recommande des activités à l\'abri.',
      };
    }

    // Météo favorable aux activités outdoor
    return {
      indoor: [
        'Escape games',
        'Musées',
        'Bowling',
      ],
      outdoor: [
        'Randonnée',
        'Vélo',
        'Accrobranche',
        'Pique-nique',
        'Sports outdoor',
        'Parc d\'attractions',
        'Activités nautiques',
        'Escalade outdoor',
      ],
      message: 'Météo parfaite pour profiter des activités en extérieur !',
    };
  }

  /**
   * Tester la connexion à l'API
   *
   * @returns {boolean} true si la connexion fonctionne
   */
  async testConnection() {
    try {
      logger.info('Testing Open-Meteo API connection...');

      // Test simple avec les coordonnées de Paris
      const url = `${this.baseUrl}/forecast?latitude=48.8566&longitude=2.3522&current=temperature_2m`;

      const response = await fetch(url);

      if (response.ok) {
        logger.info('✅ Open-Meteo API connection successful');
        return true;
      }

      logger.error('❌ Open-Meteo API connection failed', {
        status: response.status,
      });
      return false;
    } catch (error) {
      logger.error('❌ Open-Meteo API connection error', {
        error: error.message,
      });
      return false;
    }
  }
}

export default new WeatherService();
