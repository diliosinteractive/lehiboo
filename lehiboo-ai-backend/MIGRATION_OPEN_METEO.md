# 🌤️ Migration vers Open-Meteo - API Météo GRATUITE

Guide pour passer à Open-Meteo (100% gratuit, sans clé API).

---

## 🎯 Pourquoi Open-Meteo ?

✅ **100% GRATUIT** sans limite d'appels
✅ **SANS CLÉ API** (pas besoin de s'inscrire)
✅ **ILLIMITÉ** (pas de quota)
✅ **EXCELLENT pour la France** (données ECMWF/DWD)
✅ **OPEN SOURCE**
✅ **Déjà intégré** dans votre backend !

**Économies** : ~0€/mois (vs OpenWeather potentiellement payant)

---

## ⚡ Migration en 2 Minutes

### Étape 1 : Activer Open-Meteo

```bash
# SSH vers le serveur
ssh juba@lehiboo.com
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/src/services

# Backup de l'ancien service
mv weather-service.js weather-service-openweather.bak

# Activer Open-Meteo
cp weather-service-openmeteo.js weather-service.js
```

### Étape 2 : Supprimer la Clé API

Éditez `.env.production` :

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend
nano .env.production
```

**Supprimez ou commentez** :
```bash
# ❌ PLUS BESOIN !
# WEATHER_API_KEY=xxxxxxxxxxxxx
```

**Sauvegarder** : `Ctrl+X`, `Y`, `Entrée`

### Étape 3 : Redéployer

```bash
./scripts/deploy-local.sh
```

Confirmez avec `y`.

**C'est tout !** ✅

---

## 🧪 Vérification

### Test 1 : Health Check

```bash
curl https://lehiboo.com/api-planner/health
```

Devrait afficher :
```json
{
  "status": "ok",
  "timestamp": "...",
  "version": "1.0.0"
}
```

### Test 2 : Logs Docker

```bash
docker-compose logs -f
```

Vous devriez voir :
```
[INFO] Testing Open-Meteo API connection...
[INFO] ✅ Open-Meteo API connection successful
```

**Si vous voyez ça** → Migration réussie ! 🎉

---

## 📊 Comparaison

| Fonctionnalité | OpenWeather | Open-Meteo |
|----------------|-------------|------------|
| **Prix** | Gratuit limité | 100% gratuit |
| **Limite** | 1000/jour | Illimité |
| **Clé API** | Obligatoire | Aucune |
| **Inscription** | Obligatoire | Aucune |
| **Qualité France** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Simplicité** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

**Open-Meteo gagne sur tous les points !** 🏆

---

## 🔧 Configuration

### Avant (OpenWeather)

**.env.production** :
```bash
WEATHER_API_KEY=xxxxxxxxxxxxxxx  # Obligatoire
```

**Limite** : 1000 appels/jour

### Après (Open-Meteo)

**.env.production** :
```bash
# Plus besoin de WEATHER_API_KEY !
```

**Limite** : Aucune ! ∞

---

## 🌍 Fonctionnalités Open-Meteo

### Météo Actuelle

```javascript
const weather = await weatherService.getCurrentWeather('Paris');

// Retourne :
{
  location: 'Paris',
  temperature: 15,
  precipitation: 0.5,
  rain: 0.5,
  wind: {
    speed: 18,      // km/h
    direction: 270  // degrés
  },
  weatherCode: 1,
  description: 'Principalement dégagé',
  icon: '🌤️',
  timestamp: '2025-10-28T12:00'
}
```

### Prévisions

```javascript
const forecast = await weatherService.getForecast('Lyon', 7);

// Retourne :
{
  location: 'Lyon',
  daily: {
    time: ['2025-10-28', '2025-10-29', ...],
    temperature_2m_max: [18, 19, 20, ...],
    temperature_2m_min: [12, 13, 14, ...],
    precipitation_sum: [0, 2.5, 0, ...],
    weathercode: [1, 61, 0, ...],
    windspeed_10m_max: [15, 25, 10, ...]
  }
}
```

### Météo pour une Date

```javascript
const weather = await weatherService.getWeatherForDate('Marseille', '2025-11-05');

// Retourne :
{
  location: 'Marseille',
  date: '2025-11-05',
  temperature: {
    max: 22,
    min: 16
  },
  precipitation: 0,
  weatherCode: 0,
  description: 'Ciel dégagé',
  icon: '☀️'
}
```

---

## 🎯 Weather Codes

| Code | Description | Icon |
|------|-------------|------|
| 0 | Ciel dégagé | ☀️ |
| 1-2 | Peu nuageux | 🌤️⛅ |
| 3 | Nuageux | ☁️ |
| 45-48 | Brouillard | 🌫️ |
| 51-67 | Pluie | 🌧️ |
| 71-77 | Neige | 🌨️❄️ |
| 80-82 | Averses | 🌦️⛈️ |
| 95-99 | Orage | ⛈️ |

---

## 🔍 Alertes Météo

Le service génère automatiquement des alertes :

```javascript
const analysis = weatherService.analyzeWeather(weather);

// Retourne :
{
  type: 'rain',              // Types: perfect, rain, cold, hot, storm
  icon: '🌧️',
  message: 'Pluie prévue. Je vous suggère des activités indoor...',
  recommendIndoor: true
}
```

### Types d'Alertes

| Type | Condition | Message |
|------|-----------|---------|
| `perfect` | Météo idéale | Toutes activités outdoor recommandées |
| `rain` | > 5mm pluie | Suggère activités indoor |
| `storm` | > 10mm ou orage | Recommande fortement indoor |
| `cold` | < 5°C | Pensez à vous couvrir |
| `hot` | > 30°C | Hydratez-vous bien |

---

## 🏃 Recommandations Activités

```javascript
const reco = weatherService.getActivityRecommendations(weather);

// Si mauvais temps :
{
  indoor: ['Escape games', 'Musées', 'Bowling', 'Cinéma', ...],
  outdoor: [],
  message: 'Avec cette météo, je vous recommande des activités à l\'abri.'
}

// Si beau temps :
{
  indoor: ['Escape games', 'Musées', 'Bowling'],
  outdoor: ['Randonnée', 'Vélo', 'Accrobranche', 'Pique-nique', ...],
  message: 'Météo parfaite pour profiter des activités en extérieur !'
}
```

---

## 🐛 Troubleshooting

### API ne répond pas

```bash
# Vérifier la connexion
curl "https://api.open-meteo.com/v1/forecast?latitude=48.8566&longitude=2.3522&current=temperature_2m"
```

**Doit retourner du JSON avec température**

### Logs d'erreur

```bash
docker-compose logs -f | grep -i weather
```

Si vous voyez :
```
✅ Open-Meteo API connection successful
```

**Tout fonctionne !**

---

## 🔄 Rollback (si besoin)

Pour revenir à OpenWeather :

```bash
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend/src/services

# Restaurer l'ancien
mv weather-service.js weather-service-openmeteo.bak
mv weather-service-openweather.bak weather-service.js

# Remettre la clé API dans .env.production
nano ../../.env.production
# Ajouter: WEATHER_API_KEY=xxxxx

# Redéployer
cd ../..
./scripts/deploy-local.sh
```

---

## ✅ Checklist Migration

- [ ] Backup de l'ancien service (`mv weather-service.js weather-service-openweather.bak`)
- [ ] Activation Open-Meteo (`cp weather-service-openmeteo.js weather-service.js`)
- [ ] Suppression `WEATHER_API_KEY` du `.env.production`
- [ ] Redéploiement (`./scripts/deploy-local.sh`)
- [ ] Vérification logs (`docker-compose logs -f`)
- [ ] Test health check (`curl https://lehiboo.com/api-planner/health`)
- [ ] Test frontend (chat répond avec météo)

---

## 🎉 Résultat

✅ **API météo 100% gratuite**
✅ **Sans limite d'appels**
✅ **Sans inscription**
✅ **Meilleures données pour la France**
✅ **Plus simple à maintenir**

**Économies** : Variable selon usage, mais 0€ garanti !

---

## 📚 Ressources

- **Open-Meteo** : https://open-meteo.com
- **Documentation API** : https://open-meteo.com/en/docs
- **GitHub** : https://github.com/open-meteo/open-meteo
- **Guide alternatives** : [API_METEO_ALTERNATIVES.md](API_METEO_ALTERNATIVES.md)

---

**Migration Open-Meteo** - Le Hiboo AI Assistant v1.0.0

**Temps de migration** : 2 minutes ⏱️

**Recommandation** : ⭐⭐⭐⭐⭐ Fortement recommandé !
