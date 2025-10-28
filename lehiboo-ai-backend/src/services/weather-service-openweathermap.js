/**
 * Service Météo avec OpenWeatherMap API
 * Fournit des prévisions météo pour aider l'IA à suggérer des activités adaptées
 */

import config from '../config/index.js';
import logger from '../utils/logger.js';

/**
 * Classe pour gérer les requêtes météo
 */
class WeatherService {
  constructor() {
    this.apiKey = config.weather.apiKey;
    this.baseUrl = config.weather.apiUrl || 'https://api.openweathermap.org/data/2.5';
  }

  /**
   * Obtenir la météo actuelle pour une localisation
   *
   * @param {string} location - Nom de la ville ou coordonnées
   * @returns {Object} Données météo
   */
  async getCurrentWeather(location) {
    try {
      logger.info('Fetching current weather', { location });

      const url = `${this.baseUrl}/weather?q=${encodeURIComponent(location)}&appid=${this.apiKey}&units=metric&lang=fr`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`Weather API error: ${response.status}`);
      }

      const data = await response.json();

      return this.formatWeatherData(data);
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
   * @param {number} days - Nombre de jours (max 5 sur tier gratuit)
   * @returns {Array} Prévisions par jour
   */
  async getForecast(location, days = 3) {
    try {
      logger.info('Fetching weather forecast', { location, days });

      // API forecast (5 jours, données toutes les 3h)
      const url = `${this.baseUrl}/forecast?q=${encodeURIComponent(location)}&appid=${this.apiKey}&units=metric&lang=fr`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`Weather API error: ${response.status}`);
      }

      const data = await response.json();

      // Grouper par jour et obtenir une prévision par jour
      const dailyForecasts = this.groupForecastByDay(data.list, days);

      return dailyForecasts;
    } catch (error) {
      logger.error('Error fetching weather forecast', {
        location,
        error: error.message,
      });
      return [];
    }
  }

  /**
   * Obtenir la météo pour une date spécifique
   *
   * @param {string} location - Nom de la ville
   * @param {string} date - Date au format YYYY-MM-DD
   * @returns {Object} Prévisions pour cette date
   */
  async getWeatherForDate(location, date) {
    try {
      const targetDate = new Date(date);
      const today = new Date();
      const diffDays = Math.ceil((targetDate - today) / (1000 * 60 * 60 * 24));

      if (diffDays < 0) {
        logger.warn('Cannot get weather for past dates', { date });
        return null;
      }

      if (diffDays > 5) {
        logger.warn('Weather forecast only available for next 5 days', { date });
        return null;
      }

      const forecasts = await this.getForecast(location, diffDays + 1);

      // Trouver le forecast pour la date demandée
      const targetForecast = forecasts.find(f => {
        const forecastDate = new Date(f.date);
        return forecastDate.toDateString() === targetDate.toDateString();
      });

      return targetForecast || null;
    } catch (error) {
      logger.error('Error getting weather for date', {
        location,
        date,
        error: error.message,
      });
      return null;
    }
  }

  /**
   * Analyser la météo et générer des alertes/suggestions
   *
   * @param {Object} weather - Données météo
   * @returns {Object} Alerte et recommandations
   */
  analyzeWeather(weather) {
    if (!weather) {
      return null;
    }

    const { temperature, condition, rain, wind, humidity } = weather;

    // Déterminer le type d'alerte
    let alertType = 'perfect';
    let alertMessage = 'Météo idéale ! Toutes les activités outdoor sont recommandées.';
    let alertIcon = '☀️';
    let recommendIndoor = false;

    // Pluie
    if (rain > 5) {
      alertType = 'rain';
      alertIcon = rain > 10 ? '⛈️' : '🌧️';
      alertMessage = rain > 10
        ? 'Attention : fortes pluies prévues. Je privilégie les activités en intérieur pour votre confort.'
        : 'Pluie prévue. Je vous suggère des activités indoor pour rester au sec !';
      recommendIndoor = true;
    }

    // Températures extrêmes
    if (temperature > 32 && !recommendIndoor) {
      alertType = 'hot';
      alertIcon = '🌡️';
      alertMessage = 'Températures élevées prévues. Pensez aux activités aquatiques ou climatisées !';
    } else if (temperature < 5 && !recommendIndoor) {
      alertType = 'cold';
      alertIcon = '❄️';
      alertMessage = 'Températures fraîches. Je recommande des activités en intérieur ou bien équipées !';
      recommendIndoor = true;
    }

    // Vent fort
    if (wind > 40 && !recommendIndoor) {
      alertType = 'windy';
      alertIcon = '💨';
      alertMessage = 'Vents forts prévus. Évitez les activités outdoor exposées.';
      recommendIndoor = true;
    }

    // Neige (si température < 0 et humidité haute)
    if (temperature < 0 && humidity > 80) {
      alertType = 'snow';
      alertIcon = '❄️';
      alertMessage = 'Possibilité de neige ! Parfait pour des activités hivernales ou cosy en intérieur.';
    }

    return {
      type: alertType,
      icon: alertIcon,
      message: alertMessage,
      recommendIndoor,
      temperature,
      condition,
    };
  }

  /**
   * Formater les données météo
   */
  formatWeatherData(data) {
    return {
      temperature: Math.round(data.main.temp),
      feelsLike: Math.round(data.main.feels_like),
      condition: data.weather[0].description,
      conditionCode: data.weather[0].id,
      icon: data.weather[0].icon,
      humidity: data.main.humidity,
      wind: Math.round(data.wind.speed * 3.6), // m/s to km/h
      rain: data.rain?.['1h'] || 0,
      clouds: data.clouds.all,
      visibility: data.visibility / 1000, // meters to km
      sunrise: new Date(data.sys.sunrise * 1000),
      sunset: new Date(data.sys.sunset * 1000),
      location: data.name,
      country: data.sys.country,
      timestamp: new Date(data.dt * 1000),
    };
  }

  /**
   * Grouper les prévisions par jour
   */
  groupForecastByDay(forecasts, maxDays) {
    const dailyMap = new Map();

    forecasts.forEach(forecast => {
      const date = new Date(forecast.dt * 1000);
      const dateKey = date.toDateString();

      if (!dailyMap.has(dateKey)) {
        dailyMap.set(dateKey, []);
      }

      dailyMap.get(dateKey).push(forecast);
    });

    // Prendre la prévision de midi (12h) pour chaque jour, ou la moyenne
    const dailyForecasts = [];
    let count = 0;

    for (const [dateKey, forecasts] of dailyMap) {
      if (count >= maxDays) break;

      // Chercher la prévision la plus proche de 12h
      const noonForecast = forecasts.reduce((closest, forecast) => {
        const forecastDate = new Date(forecast.dt * 1000);
        const hour = forecastDate.getHours();
        const closestHour = new Date(closest.dt * 1000).getHours();

        return Math.abs(hour - 12) < Math.abs(closestHour - 12) ? forecast : closest;
      });

      // Calculer température min/max du jour
      const temps = forecasts.map(f => f.main.temp);
      const minTemp = Math.round(Math.min(...temps));
      const maxTemp = Math.round(Math.max(...temps));

      // Calculer pluie totale du jour
      const totalRain = forecasts.reduce((sum, f) => sum + (f.rain?.['3h'] || 0), 0);

      dailyForecasts.push({
        date: new Date(noonForecast.dt * 1000).toISOString().split('T')[0],
        temperature: Math.round(noonForecast.main.temp),
        minTemp,
        maxTemp,
        condition: noonForecast.weather[0].description,
        conditionCode: noonForecast.weather[0].id,
        icon: noonForecast.weather[0].icon,
        rain: totalRain,
        humidity: noonForecast.main.humidity,
        wind: Math.round(noonForecast.wind.speed * 3.6),
        clouds: noonForecast.clouds.all,
      });

      count++;
    }

    return dailyForecasts;
  }

  /**
   * Tester la connexion à l'API météo
   */
  async testConnection() {
    try {
      if (!this.apiKey) {
        logger.warn('Weather API key not configured');
        return false;
      }

      // Test avec Paris
      const weather = await this.getCurrentWeather('Paris');

      if (weather) {
        logger.info('Weather API connection test successful', {
          location: weather.location,
          temperature: weather.temperature,
        });
        return true;
      }

      return false;
    } catch (error) {
      logger.error('Weather API connection test failed', {
        error: error.message,
      });
      return false;
    }
  }

  /**
   * Obtenir des recommandations d'activités basées sur la météo
   */
  getActivityRecommendations(weather) {
    if (!weather) {
      return {
        indoor: false,
        outdoor: true,
        suggestions: [],
      };
    }

    const analysis = this.analyzeWeather(weather);

    const recommendations = {
      indoor: analysis.recommendIndoor,
      outdoor: !analysis.recommendIndoor,
      suggestions: [],
    };

    // Suggestions basées sur la météo
    if (weather.temperature > 28) {
      recommendations.suggestions.push('Activités aquatiques', 'Musées climatisés', 'Cinéma');
    } else if (weather.temperature < 10) {
      recommendations.suggestions.push('Activités indoor', 'Spa & Détente', 'Ateliers créatifs');
    } else if (weather.rain > 5) {
      recommendations.suggestions.push('Escape games', 'Bowling', 'Cuisine & Dégustation');
    } else {
      recommendations.suggestions.push('Randonnée', 'Sports outdoor', 'Visites guidées');
    }

    return recommendations;
  }
}

// Exporter une instance singleton
export default new WeatherService();
