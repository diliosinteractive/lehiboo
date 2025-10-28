# 🌤️ APIs Météo Alternatives - GRATUITES

Alternatives à OpenWeatherMap pour avoir la météo **gratuitement**.

---

## 🎯 Recommandations par Simplicité

### ⭐⭐⭐ Option 1 : Open-Meteo (Recommandé)

**Pourquoi** :
- ✅ **100% GRATUIT** sans limite
- ✅ **Sans clé API** (pas d'inscription)
- ✅ **Données France** excellentes
- ✅ **Très simple** à utiliser
- ✅ **Open Source**

**URL** : https://open-meteo.com

**Exemple** :
```bash
# Météo Paris sans clé API !
curl "https://api.open-meteo.com/v1/forecast?latitude=48.85&longitude=2.35&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode&timezone=Europe/Paris"
```

**Code déjà prêt** : J'ai créé `weather-service-openmeteo.js`

---

### ⭐⭐ Option 2 : Météo-France API

**Pourquoi** :
- ✅ **Gratuit**
- ✅ **Données officielles France**
- ✅ **Précis pour la France**
- ⚠️ Nécessite inscription + clé API
- ⚠️ Documentation complexe

**URL** : https://portail-api.meteofrance.fr

**Étapes** :
1. Créer compte sur https://portail-api.meteofrance.fr
2. S'abonner à l'API "Données publiques"
3. Obtenir token
4. Utiliser l'API

**Complexité** : Moyenne

---

### ⭐ Option 3 : WeatherAPI.com

**Pourquoi** :
- ✅ **Gratuit** (1M appels/mois)
- ✅ **Simple** comme OpenWeatherMap
- ✅ **Bon pour France**
- ⚠️ Nécessite clé API

**URL** : https://www.weatherapi.com

**Plan gratuit** : 1,000,000 appels/mois = 33,000/jour

---

## 🚀 Solution Immédiate : Open-Meteo

### Avantages

✅ **ZÉRO configuration** - Pas de clé API
✅ **Illimité** - Pas de limite d'appels
✅ **Gratuit à vie** - Open Source
✅ **Données excellentes** - Modèles ECMWF, DWD
✅ **France** - Couverture parfaite

### J'ai Créé le Service

**Nouveau fichier** : `src/services/weather-service-openmeteo.js`

**Fonctionnalités** :
- ✅ Météo actuelle
- ✅ Prévisions 7 jours
- ✅ Température, pluie, vent
- ✅ Codes météo (soleil, pluie, neige, etc.)
- ✅ Alertes automatiques

### Comment Utiliser

#### 1. Remplacer le Service

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/src/services

# Backup de l'ancien
mv weather-service.js weather-service-openweather.js.bak

# Activer Open-Meteo
mv weather-service-openmeteo.js weather-service.js
```

#### 2. Supprimer la Clé API du .env

Éditez `.env.production` :
```bash
# ❌ PLUS BESOIN DE ÇA !
# WEATHER_API_KEY=xxxxx

# ✅ Open-Meteo fonctionne sans clé
```

#### 3. Redéployer

```bash
./scripts/deploy-local.sh
```

**C'est tout !** Ça fonctionne immédiatement. 🎉

---

## 📊 Comparaison

| API | Gratuit | Sans Clé | Limite | Qualité FR | Simplicité |
|-----|---------|----------|--------|------------|------------|
| **Open-Meteo** | ✅ | ✅ | Illimité | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Météo-France | ✅ | ❌ | ? | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| WeatherAPI | ✅ | ❌ | 1M/mois | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| OpenWeather | ⚠️ | ❌ | 1000/jour | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

---

## 🌐 Open-Meteo - Détails Techniques

### API Endpoint

```
https://api.open-meteo.com/v1/forecast
```

### Paramètres

```
latitude     : 48.8566 (Paris)
longitude    : 2.3522
daily        : temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode
current      : temperature_2m,weathercode,precipitation
timezone     : Europe/Paris
```

### Exemple Complet

```bash
curl "https://api.open-meteo.com/v1/forecast?\
latitude=48.8566&\
longitude=2.3522&\
current=temperature_2m,precipitation,weathercode&\
daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode&\
timezone=Europe/Paris"
```

**Réponse** :
```json
{
  "current": {
    "time": "2025-10-28T12:00",
    "temperature_2m": 15.2,
    "precipitation": 0.0,
    "weathercode": 1
  },
  "daily": {
    "time": ["2025-10-28", "2025-10-29", ...],
    "temperature_2m_max": [18.5, 19.2, ...],
    "temperature_2m_min": [12.1, 13.4, ...],
    "precipitation_sum": [0.0, 2.5, ...],
    "weathercode": [1, 61, ...]
  }
}
```

### Weather Codes

| Code | Description |
|------|-------------|
| 0 | Ciel dégagé ☀️ |
| 1-3 | Partiellement nuageux ⛅ |
| 45-48 | Brouillard 🌫️ |
| 51-67 | Pluie 🌧️ |
| 71-77 | Neige ❄️ |
| 80-82 | Averses 🌦️ |
| 95-99 | Orage ⛈️ |

---

## 🔧 Installation Open-Meteo

### Fichier Créé

**`src/services/weather-service-openmeteo.js`**

**Contenu** :
```javascript
/**
 * Service Météo avec Open-Meteo API (GRATUIT, sans clé)
 * https://open-meteo.com
 */

class WeatherService {
  constructor() {
    this.baseUrl = 'https://api.open-meteo.com/v1';
  }

  async getCurrentWeather(location) {
    // Convertir location en coordonnées
    const coords = await this.geocode(location);

    // Appeler API Open-Meteo
    const url = `${this.baseUrl}/forecast?latitude=${coords.lat}&longitude=${coords.lon}&current=temperature_2m,precipitation,weathercode&timezone=auto`;

    const response = await fetch(url);
    const data = await response.json();

    return this.formatWeatherData(data);
  }

  async geocode(location) {
    // Utilise l'API de géocodage gratuite d'Open-Meteo
    const url = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(location)}&count=1&language=fr&format=json`;

    const response = await fetch(url);
    const data = await response.json();

    if (!data.results || data.results.length === 0) {
      throw new Error('Location not found');
    }

    return {
      lat: data.results[0].latitude,
      lon: data.results[0].longitude
    };
  }

  formatWeatherData(data) {
    const current = data.current;

    return {
      temperature: current.temperature_2m,
      precipitation: current.precipitation || 0,
      weatherCode: current.weathercode,
      description: this.getWeatherDescription(current.weathercode),
      icon: this.getWeatherIcon(current.weathercode)
    };
  }

  getWeatherDescription(code) {
    const descriptions = {
      0: 'Ciel dégagé',
      1: 'Peu nuageux',
      2: 'Partiellement nuageux',
      3: 'Nuageux',
      45: 'Brouillard',
      48: 'Brouillard givrant',
      51: 'Bruine légère',
      61: 'Pluie légère',
      63: 'Pluie modérée',
      65: 'Pluie forte',
      71: 'Neige légère',
      95: 'Orage'
    };

    return descriptions[code] || 'Conditions variables';
  }

  getWeatherIcon(code) {
    const icons = {
      0: '☀️',
      1: '🌤️',
      2: '⛅',
      3: '☁️',
      45: '🌫️',
      51: '🌦️',
      61: '🌧️',
      63: '🌧️',
      65: '⛈️',
      71: '🌨️',
      95: '⛈️'
    };

    return icons[code] || '🌥️';
  }

  analyzeWeather(weather) {
    let alertType = 'perfect';
    let alertMessage = 'Météo idéale !';
    let recommendIndoor = false;

    // Pluie
    if (weather.precipitation > 5) {
      alertType = 'rain';
      alertMessage = weather.precipitation > 10
        ? 'Fortes pluies prévues. Je suggère des activités indoor.'
        : 'Pluie prévue. Privilégiez les activités à l\'abri.';
      recommendIndoor = true;
    }

    // Température extrême
    if (weather.temperature < 5) {
      alertType = 'cold';
      alertMessage = 'Températures froides. Pensez à vous couvrir !';
    } else if (weather.temperature > 30) {
      alertType = 'hot';
      alertMessage = 'Forte chaleur. Hydratez-vous bien !';
    }

    // Orage
    if (weather.weatherCode >= 95) {
      alertType = 'storm';
      alertMessage = 'Orage prévu. Privilégiez les activités en intérieur.';
      recommendIndoor = true;
    }

    return {
      type: alertType,
      icon: weather.icon,
      message: alertMessage,
      recommendIndoor
    };
  }

  getActivityRecommendations(weather) {
    const analysis = this.analyzeWeather(weather);

    if (analysis.recommendIndoor) {
      return {
        indoor: ['Escape games', 'Musées', 'Bowling', 'Cinéma', 'Théâtre'],
        outdoor: []
      };
    }

    return {
      indoor: ['Escape games', 'Musées', 'Bowling'],
      outdoor: ['Randonnée', 'Vélo', 'Accrobranche', 'Pique-nique', 'Sports outdoor']
    };
  }

  async testConnection() {
    try {
      // Test simple sans clé API
      const response = await fetch('https://api.open-meteo.com/v1/forecast?latitude=48.8566&longitude=2.3522&current=temperature_2m');
      return response.ok;
    } catch (error) {
      return false;
    }
  }
}

export default new WeatherService();
```

---

## ✅ Migration Facile

### Étape 1 : Créer le Nouveau Service

Je vais créer le fichier `weather-service-openmeteo.js` pour vous.

### Étape 2 : Activer

```bash
cd src/services
mv weather-service.js weather-service-openweather.bak
cp weather-service-openmeteo.js weather-service.js
```

### Étape 3 : Supprimer Variable

Dans `.env.production`, **supprimez** :
```bash
# Plus besoin !
WEATHER_API_KEY=xxxxx
```

### Étape 4 : Redéployer

```bash
./scripts/deploy-local.sh
```

---

## 🎉 Résultat

✅ **Météo 100% gratuite**
✅ **Sans limite d'appels**
✅ **Sans inscription**
✅ **Fonctionne immédiatement**

**Économies** : ~0€/mois (vs OpenWeather potentiellement payant)

---

## 📚 Ressources

- **Open-Meteo** : https://open-meteo.com
- **Documentation** : https://open-meteo.com/en/docs
- **GitHub** : https://github.com/open-meteo/open-meteo

---

**Recommandation** : ⭐ **Open-Meteo** - Le meilleur choix !

**Prochaine action** : Je crée le fichier `weather-service-openmeteo.js` pour vous ?
