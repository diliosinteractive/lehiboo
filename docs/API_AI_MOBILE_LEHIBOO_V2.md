# API AI PETIT BOO Mobile LeHiboo v2
## Documentation complete pour les developpeurs Flutter

**Version:** 2.0.0
**Base URL AI Backend:** `https://preprod.lehiboo.com/api-planner`
**Base URL WordPress:** `https://preprod.lehiboo.com/wp-json/lehiboo/v2`
**Derniere mise a jour:** Decembre 2025

---

## Table des matieres

1. [Architecture](#architecture)
2. [Authentification](#authentification)
3. [API AI Chat](#api-ai-chat)
4. [API Meteo](#api-meteo)
5. [API Recherche](#api-recherche)
6. [API Utilitaires](#api-utilitaires)
7. [Exemples Flutter](#exemples-flutter)
8. [Codes d'erreur](#codes-derreur)

---

## Architecture

### Vue d'ensemble

```
+-------------------+          +----------------------+          +------------------+
|   App Flutter     |  ---->   |  WordPress REST API  |  ---->   |   AI Backend     |
|                   |          |  (Authentification)  |          |  (Node.js/AI)    |
+-------------------+          +----------------------+          +------------------+
        |                               |                                |
        |  1. Login JWT                 |                                |
        |  2. Get API Token             |                                |
        |  3. Chat avec AI  ---------------------------------->          |
        |  4. Recherche events  ------------------------------->         |
        |  5. Meteo  ------------------------------------------>         |
```

### URLs des services

| Service | URL | Description |
|---------|-----|-------------|
| WordPress API | `https://preprod.lehiboo.com/wp-json/lehiboo/v2` | Authentification, token |
| AI Backend | `https://preprod.lehiboo.com/api-planner` | Chat AI, recherche, meteo |

---

## Authentification

### Flux d'authentification

1. **Connexion WordPress** - Obtenir un access_token JWT via l'API LeHiboo v2
2. **Recuperer le token API** - Echanger JWT contre API key pour le backend AI
3. **Utiliser l'API AI** - Toutes les requetes avec `X-API-Key` header

### Etape 1: Connexion WordPress (API LeHiboo v2)

```http
POST https://preprod.lehiboo.com/wp-json/lehiboo/v2/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "mot_de_passe"
}
```

**Reponse:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email": "user@example.com",
      "display_name": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "role": "subscriber"
    },
    "tokens": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "Bearer",
      "expires_in": 604800
    }
  }
}
```

### Etape 2: Recuperer le token API Backend AI

```http
GET https://preprod.lehiboo.com/wp-json/lehiboo/v2/auth/ai-token
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Reponse:**
```json
{
  "success": true,
  "data": {
    "api_key": "lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "backend_url": "https://preprod.lehiboo.com/api-planner",
    "expires_in": 86400,
    "user": {
      "id": 123,
      "email": "user@example.com",
      "display_name": "John Doe"
    }
  }
}
```

### Etape 3: Utiliser l'API AI

Toutes les requetes vers le backend AI doivent inclure le header:

```http
X-API-Key: lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Ou alternativement:**

```http
Authorization: Bearer lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## API AI Chat

### POST /mobile/chat

Endpoint principal de conversation avec l'assistant AI Petit Boo.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/chat`

**Headers:**
```http
Content-Type: application/json
X-API-Key: lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Body:**
```json
{
  "message": "Je cherche une activite sympa ce weekend a Lille",
  "conversationId": "conv_abc123",
  "userContext": {
    "groupType": "couple",
    "location": {
      "city": "Lille",
      "radius": 30
    }
  },
  "history": []
}
```

**Parametres:**

| Parametre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `message` | string | Oui | Message de l'utilisateur (max 1000 caracteres) |
| `conversationId` | string | Non | ID de conversation (genere si absent) |
| `userContext` | object | Non | Contexte utilisateur (preferences, localisation) |
| `history` | array | Non | Historique des messages precedents |

**Structure userContext:**
```json
{
  "groupType": "solo|couple|famille|amis",
  "age": 30,
  "location": {
    "city": "Valenciennes",
    "radius": 20
  },
  "dates": {
    "type": "today|tomorrow|weekend|flexible",
    "specific": "2025-12-15"
  },
  "activityType": "sport|culture|gastronomie|nature|detente|multi",
  "budgetMax": 50,
  "interests": ["escape-game", "cinema", "randonnee"]
}
```

**Reponse:**
```json
{
  "success": true,
  "conversationId": "conv_abc123",
  "message": "Super choix ! Pour un weekend en couple a Lille, j'ai trouve plusieurs activites qui pourraient vous plaire...",
  "userContext": {
    "groupType": "couple",
    "location": {
      "city": "Lille",
      "radius": 30
    },
    "dates": {
      "type": "weekend"
    }
  },
  "events": [
    {
      "id": 1234,
      "title": "Escape Game - Le Mystere du Vieux Lille",
      "description": "Plongez dans une aventure immersive...",
      "image": "https://lehiboo.com/wp-content/uploads/escape.jpg",
      "price": 28,
      "priceLabel": "28E",
      "location": "Lille",
      "date": "Samedi 14 decembre",
      "category": "culture",
      "matchScore": 95,
      "url": "https://lehiboo.com/activite/escape-game-lille"
    }
  ],
  "history": [
    {
      "role": "user",
      "content": "Je cherche une activite sympa ce weekend a Lille",
      "timestamp": "2025-12-10T14:30:00.000Z"
    },
    {
      "role": "assistant",
      "content": "Super choix ! Pour un weekend en couple...",
      "timestamp": "2025-12-10T14:30:01.500Z"
    }
  ],
  "usage": {
    "promptTokens": 1250,
    "completionTokens": 350,
    "totalTokens": 1600
  }
}
```

### Exemples de conversations

**Premiere interaction:**
```json
{
  "message": "Salut !",
  "conversationId": null,
  "userContext": {},
  "history": []
}
```

**Suite de conversation:**
```json
{
  "message": "On est 4 amis et on veut faire un truc fun",
  "conversationId": "conv_abc123",
  "userContext": {
    "location": { "city": "Lille" }
  },
  "history": [
    {"role": "user", "content": "Salut !", "timestamp": "..."},
    {"role": "assistant", "content": "Hey ! Je suis Petit Boo...", "timestamp": "..."}
  ]
}
```

---

## API Meteo

### GET /mobile/weather

Meteo actuelle pour une ville.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/weather?city=Lille`

**Headers:**
```http
X-API-Key: lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Parametres query:**

| Parametre | Type | Defaut | Description |
|-----------|------|--------|-------------|
| `city` | string | Valenciennes | Nom de la ville |

**Reponse:**
```json
{
  "success": true,
  "city": "Lille",
  "current": {
    "temperature": 8,
    "description": "Partiellement nuageux",
    "icon": "cloud_sun",
    "wind": {
      "speed": 15,
      "direction": 270
    },
    "precipitation": 0
  },
  "alert": {
    "type": "rain",
    "icon": "umbrella",
    "message": "Pluie prevue. Je vous suggere des activites indoor !",
    "recommendIndoor": true
  },
  "timestamp": "2025-12-10T14:30:00.000Z"
}
```

**Types d'alertes:**

| Type | Description |
|------|-------------|
| `perfect` | Meteo ideale |
| `rain` | Pluie prevue |
| `storm` | Orage/vents forts |
| `cold` | Temperatures froides |
| `hot` | Forte chaleur |

### GET /mobile/weather/forecast

Previsions meteo sur plusieurs jours.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/weather/forecast?city=Lille&days=7`

**Parametres query:**

| Parametre | Type | Defaut | Description |
|-----------|------|--------|-------------|
| `city` | string | Valenciennes | Nom de la ville |
| `days` | number | 7 | Nombre de jours (max 16) |

**Reponse:**
```json
{
  "success": true,
  "city": "Lille",
  "forecast": [
    {
      "date": "2025-12-11",
      "tempMax": 10,
      "tempMin": 4,
      "precipitation": 2.5,
      "description": "Pluie legere",
      "icon": "rain"
    },
    {
      "date": "2025-12-12",
      "tempMax": 12,
      "tempMin": 6,
      "precipitation": 0,
      "description": "Ciel degage",
      "icon": "sun"
    }
  ]
}
```

---

## API Recherche

### POST /mobile/search

Recherche directe d'activites sans conversation AI.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/search`

**Headers:**
```http
Content-Type: application/json
X-API-Key: lhb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Body:**
```json
{
  "groupType": "amis",
  "activityType": "sport",
  "city": "Lille",
  "radius": 30,
  "dates": "weekend",
  "budgetMax": 50,
  "limit": 10
}
```

**Parametres:**

| Parametre | Type | Defaut | Description |
|-----------|------|--------|-------------|
| `groupType` | string | solo | solo, couple, famille, amis |
| `activityType` | string | - | sport, culture, gastronomie, nature, detente |
| `city` | string | Valenciennes | Ville de recherche |
| `radius` | number | 20 | Rayon en km |
| `dates` | string | flexible | today, tomorrow, weekend, flexible |
| `budgetMax` | number | - | Budget maximum en euros |
| `limit` | number | 10 | Nombre de resultats |

**Reponse:**
```json
{
  "success": true,
  "events": [
    {
      "id": 1234,
      "title": "Paintball Adventure",
      "description": "Session de paintball en equipe...",
      "image": "https://lehiboo.com/wp-content/uploads/paintball.jpg",
      "price": 35,
      "priceLabel": "35E",
      "location": "Villeneuve-d'Ascq",
      "date": "Samedi 14 decembre",
      "category": "sport",
      "matchScore": 92,
      "url": "https://lehiboo.com/activite/paintball-lille"
    }
  ],
  "total": 15,
  "filters": {
    "city": "Lille",
    "activityType": "sport",
    "dates": "weekend",
    "budgetMax": 50
  }
}
```

---

## API Utilitaires

### GET /mobile/categories

Liste des categories d'activites disponibles.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/categories`

**Reponse:**
```json
{
  "success": true,
  "categories": [
    { "id": "sport", "label": "Sport", "icon": "sports_soccer" },
    { "id": "culture", "label": "Culture", "icon": "theater_comedy" },
    { "id": "gastronomie", "label": "Gastronomie", "icon": "restaurant" },
    { "id": "nature", "label": "Nature", "icon": "park" },
    { "id": "detente", "label": "Detente", "icon": "spa" }
  ]
}
```

### GET /mobile/cities

Liste des villes suggerees.

**URL:** `https://preprod.lehiboo.com/api-planner/mobile/cities`

**Reponse:**
```json
{
  "success": true,
  "cities": [
    { "name": "Valenciennes", "region": "Hauts-de-France" },
    { "name": "Lille", "region": "Hauts-de-France" },
    { "name": "Douai", "region": "Hauts-de-France" },
    { "name": "Cambrai", "region": "Hauts-de-France" },
    { "name": "Maubeuge", "region": "Hauts-de-France" },
    { "name": "Denain", "region": "Hauts-de-France" }
  ]
}
```

### GET /health

Verification de l'etat du serveur.

**URL:** `https://preprod.lehiboo.com/api-planner/health`

**Reponse:**
```json
{
  "status": "ok",
  "timestamp": "2025-12-10T14:30:00.000Z",
  "version": "1.1.0"
}
```

---

## Exemples Flutter

### Configuration du service API

```dart
// lib/services/lehiboo_api.dart

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class LehibooApi {
  static const String _wordpressUrl = 'https://lehiboo.com/wp-json';
  static const String _aiBackendUrl = 'https://preprod.lehiboo.com/api-planner';

  String? _jwtToken;
  String? _apiKey;

  // Singleton
  static final LehibooApi _instance = LehibooApi._internal();
  factory LehibooApi() => _instance;
  LehibooApi._internal();

  /// Headers pour les requetes AI Backend
  Map<String, String> get _aiHeaders => {
    'Content-Type': 'application/json',
    if (_apiKey != null) 'X-API-Key': _apiKey!,
  };

  /// Headers pour WordPress
  Map<String, String> get _wpHeaders => {
    'Content-Type': 'application/json',
    if (_jwtToken != null) 'Authorization': 'Bearer $_jwtToken',
  };

  /// Connexion utilisateur
  Future<bool> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$_wordpressUrl/jwt-auth/v1/token'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _jwtToken = data['token'];

        // Sauvegarder le token
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('jwt_token', _jwtToken!);

        // Recuperer la cle API
        await _fetchApiKey();

        return true;
      }
      return false;
    } catch (e) {
      print('Login error: $e');
      return false;
    }
  }

  /// Recuperer la cle API pour le backend AI
  Future<void> _fetchApiKey() async {
    try {
      final response = await http.get(
        Uri.parse('$_wordpressUrl/lehiboo/v1/mobile/token'),
        headers: _wpHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _apiKey = data['api_key'];

        // Sauvegarder
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('api_key', _apiKey!);
      }
    } catch (e) {
      print('Fetch API key error: $e');
    }
  }

  /// Restaurer la session
  Future<bool> restoreSession() async {
    final prefs = await SharedPreferences.getInstance();
    _jwtToken = prefs.getString('jwt_token');
    _apiKey = prefs.getString('api_key');
    return _apiKey != null;
  }

  /// Deconnexion
  Future<void> logout() async {
    _jwtToken = null;
    _apiKey = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('jwt_token');
    await prefs.remove('api_key');
  }
}
```

### Service Chat AI

```dart
// lib/services/chat_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;

class ChatMessage {
  final String role;
  final String content;
  final DateTime timestamp;

  ChatMessage({
    required this.role,
    required this.content,
    required this.timestamp,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      role: json['role'],
      content: json['content'],
      timestamp: DateTime.parse(json['timestamp']),
    );
  }

  Map<String, dynamic> toJson() => {
    'role': role,
    'content': content,
    'timestamp': timestamp.toIso8601String(),
  };
}

class ChatService {
  final LehibooApi _api = LehibooApi();

  String? _conversationId;
  Map<String, dynamic> _userContext = {};
  List<ChatMessage> _history = [];

  List<ChatMessage> get history => _history;

  /// Envoyer un message
  Future<ChatResponse> sendMessage(String message) async {
    try {
      final response = await http.post(
        Uri.parse('${_api._aiBackendUrl}/mobile/chat'),
        headers: _api._aiHeaders,
        body: jsonEncode({
          'message': message,
          'conversationId': _conversationId,
          'userContext': _userContext,
          'history': _history.map((m) => m.toJson()).toList(),
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        // Mettre a jour le contexte
        _conversationId = data['conversationId'];
        _userContext = data['userContext'] ?? {};

        // Mettre a jour l'historique
        _history = (data['history'] as List)
            .map((m) => ChatMessage.fromJson(m))
            .toList();

        return ChatResponse(
          success: true,
          message: data['message'],
          events: (data['events'] as List?)
              ?.map((e) => EventItem.fromJson(e))
              .toList() ?? [],
        );
      }

      return ChatResponse(
        success: false,
        message: 'Erreur de communication',
        events: [],
      );
    } catch (e) {
      return ChatResponse(
        success: false,
        message: 'Erreur: $e',
        events: [],
      );
    }
  }

  /// Nouvelle conversation
  void resetConversation() {
    _conversationId = null;
    _userContext = {};
    _history = [];
  }

  /// Definir le contexte utilisateur
  void setUserContext(Map<String, dynamic> context) {
    _userContext = {..._userContext, ...context};
  }
}

class ChatResponse {
  final bool success;
  final String message;
  final List<EventItem> events;

  ChatResponse({
    required this.success,
    required this.message,
    required this.events,
  });
}

class EventItem {
  final int id;
  final String title;
  final String? description;
  final String? image;
  final double price;
  final String priceLabel;
  final String? location;
  final String? date;
  final String? category;
  final int? matchScore;
  final String? url;

  EventItem({
    required this.id,
    required this.title,
    this.description,
    this.image,
    required this.price,
    required this.priceLabel,
    this.location,
    this.date,
    this.category,
    this.matchScore,
    this.url,
  });

  factory EventItem.fromJson(Map<String, dynamic> json) {
    return EventItem(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      image: json['image'],
      price: (json['price'] ?? 0).toDouble(),
      priceLabel: json['priceLabel'] ?? 'Gratuit',
      location: json['location'],
      date: json['date'],
      category: json['category'],
      matchScore: json['matchScore'],
      url: json['url'],
    );
  }
}
```

### Service Meteo

```dart
// lib/services/weather_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;

class WeatherData {
  final String city;
  final int temperature;
  final String description;
  final String icon;
  final WeatherAlert? alert;

  WeatherData({
    required this.city,
    required this.temperature,
    required this.description,
    required this.icon,
    this.alert,
  });

  factory WeatherData.fromJson(Map<String, dynamic> json) {
    return WeatherData(
      city: json['city'],
      temperature: json['current']['temperature'],
      description: json['current']['description'],
      icon: json['current']['icon'],
      alert: json['alert'] != null
          ? WeatherAlert.fromJson(json['alert'])
          : null,
    );
  }
}

class WeatherAlert {
  final String type;
  final String icon;
  final String message;
  final bool recommendIndoor;

  WeatherAlert({
    required this.type,
    required this.icon,
    required this.message,
    required this.recommendIndoor,
  });

  factory WeatherAlert.fromJson(Map<String, dynamic> json) {
    return WeatherAlert(
      type: json['type'],
      icon: json['icon'],
      message: json['message'],
      recommendIndoor: json['recommendIndoor'] ?? false,
    );
  }
}

class WeatherService {
  final LehibooApi _api = LehibooApi();

  /// Meteo actuelle
  Future<WeatherData?> getCurrentWeather(String city) async {
    try {
      final response = await http.get(
        Uri.parse('${_api._aiBackendUrl}/mobile/weather?city=$city'),
        headers: _api._aiHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return WeatherData.fromJson(data);
      }
      return null;
    } catch (e) {
      print('Weather error: $e');
      return null;
    }
  }

  /// Previsions
  Future<List<ForecastDay>> getForecast(String city, {int days = 7}) async {
    try {
      final response = await http.get(
        Uri.parse('${_api._aiBackendUrl}/mobile/weather/forecast?city=$city&days=$days'),
        headers: _api._aiHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return (data['forecast'] as List)
            .map((f) => ForecastDay.fromJson(f))
            .toList();
      }
      return [];
    } catch (e) {
      print('Forecast error: $e');
      return [];
    }
  }
}

class ForecastDay {
  final String date;
  final int tempMax;
  final int tempMin;
  final double precipitation;
  final String description;
  final String icon;

  ForecastDay({
    required this.date,
    required this.tempMax,
    required this.tempMin,
    required this.precipitation,
    required this.description,
    required this.icon,
  });

  factory ForecastDay.fromJson(Map<String, dynamic> json) {
    return ForecastDay(
      date: json['date'],
      tempMax: json['tempMax'],
      tempMin: json['tempMin'],
      precipitation: (json['precipitation'] ?? 0).toDouble(),
      description: json['description'],
      icon: json['icon'],
    );
  }
}
```

### Widget Chat

```dart
// lib/widgets/chat_screen.dart

import 'package:flutter/material.dart';

class ChatScreen extends StatefulWidget {
  @override
  _ChatScreenState createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final ChatService _chatService = ChatService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  bool _isLoading = false;
  List<EventItem> _suggestedEvents = [];

  @override
  void initState() {
    super.initState();
    // Message d'accueil automatique
    _sendInitialMessage();
  }

  Future<void> _sendInitialMessage() async {
    setState(() => _isLoading = true);

    final response = await _chatService.sendMessage("Bonjour");

    setState(() {
      _isLoading = false;
      _suggestedEvents = response.events;
    });
  }

  Future<void> _sendMessage() async {
    final message = _messageController.text.trim();
    if (message.isEmpty) return;

    _messageController.clear();
    setState(() => _isLoading = true);

    final response = await _chatService.sendMessage(message);

    setState(() {
      _isLoading = false;
      _suggestedEvents = response.events;
    });

    // Scroll to bottom
    _scrollToBottom();
  }

  void _scrollToBottom() {
    Future.delayed(Duration(milliseconds: 100), () {
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Petit Boo'),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: () {
              _chatService.resetConversation();
              setState(() => _suggestedEvents = []);
              _sendInitialMessage();
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Messages
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: EdgeInsets.all(16),
              itemCount: _chatService.history.length + (_isLoading ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == _chatService.history.length && _isLoading) {
                  return _buildTypingIndicator();
                }

                final message = _chatService.history[index];
                return _buildMessageBubble(message);
              },
            ),
          ),

          // Events suggeres
          if (_suggestedEvents.isNotEmpty)
            Container(
              height: 120,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: EdgeInsets.symmetric(horizontal: 16),
                itemCount: _suggestedEvents.length,
                itemBuilder: (context, index) {
                  return _buildEventCard(_suggestedEvents[index]);
                },
              ),
            ),

          // Input
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    final isUser = message.role == 'user';

    return Align(
      alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.only(
          bottom: 8,
          left: isUser ? 50 : 0,
          right: isUser ? 0 : 50,
        ),
        padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isUser ? Colors.blue : Colors.grey[200],
          borderRadius: BorderRadius.circular(16),
        ),
        child: Text(
          message.content,
          style: TextStyle(
            color: isUser ? Colors.white : Colors.black87,
          ),
        ),
      ),
    );
  }

  Widget _buildTypingIndicator() {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.only(bottom: 8),
        padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.grey[200],
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
            SizedBox(width: 8),
            Text('Petit Boo reflechit...'),
          ],
        ),
      ),
    );
  }

  Widget _buildEventCard(EventItem event) {
    return Card(
      margin: EdgeInsets.only(right: 12),
      child: Container(
        width: 200,
        padding: EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              event.title,
              style: TextStyle(fontWeight: FontWeight.bold),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            Spacer(),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  event.priceLabel,
                  style: TextStyle(color: Colors.green),
                ),
                if (event.matchScore != null)
                  Text(
                    '${event.matchScore}%',
                    style: TextStyle(color: Colors.blue),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 4,
            offset: Offset(0, -2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _messageController,
              decoration: InputDecoration(
                hintText: 'Ecris ton message...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
              ),
              onSubmitted: (_) => _sendMessage(),
            ),
          ),
          SizedBox(width: 8),
          IconButton(
            icon: Icon(Icons.send, color: Colors.blue),
            onPressed: _isLoading ? null : _sendMessage,
          ),
        ],
      ),
    );
  }
}
```

---

## Codes d'erreur

### Codes HTTP

| Code | Description | Solution |
|------|-------------|----------|
| 200 | Succes | - |
| 400 | Requete invalide | Verifier les parametres |
| 401 | Non authentifie | Verifier le token JWT |
| 403 | Acces refuse | Verifier la cle API |
| 404 | Non trouve | Verifier l'URL |
| 429 | Trop de requetes | Attendre et reessayer |
| 500 | Erreur serveur | Contacter le support |

### Erreurs API

```json
{
  "success": false,
  "error": "Message requis"
}
```

| Erreur | Description |
|--------|-------------|
| `Message requis` | Le champ message est vide |
| `Message trop long` | Message > 1000 caracteres |
| `API key invalide` | Cle API incorrecte ou expiree |
| `Non authentifie` | Token JWT manquant ou invalide |
| `Erreur lors de la recherche` | Probleme avec la base de donnees |
| `Erreur meteo` | Service meteo indisponible |

---

## Commandes Docker (Serveur)

### Rebuild et redemarrage

```bash
# Se connecter au serveur
ssh user@preprod.lehiboo.com

# Aller dans le dossier
cd /var/www/vhosts/lehiboo.com/lehiboo-ai-backend

# Rebuild
docker compose build --no-cache

# Redemarrer
docker compose up -d

# Voir les logs
docker compose logs -f
```

### Verification sante

```bash
# Health check
curl https://preprod.lehiboo.com/api-planner/health

# Test avec API key
curl -H "X-API-Key: lhb_xxx" https://preprod.lehiboo.com/api-planner/mobile/categories
```

---

## Support

Pour toute question technique:
- **Email:** support@lehiboo.com
- **Documentation:** https://lehiboo.com/docs

---

*Documentation generee le 11 decembre 2025*
