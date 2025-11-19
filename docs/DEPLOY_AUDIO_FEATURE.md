# 🚀 Déploiement de la fonctionnalité audio (transcription)

## ✅ Commits à déployer

- **91cbb3ac** - Feat: Transcription audio avec OpenAI Whisper + module enregistrement (backend + audio-recorder.js)
- **925c2ff8** - Feat: Intégration complète du bouton micro dans le chat (frontend UI)
- **ede0fd18** - Docs: Mise à jour AUDIO_INTEGRATION_GUIDE (documentation)

## 📦 Ce qui a été implémenté

### Backend (lehiboo-ai-backend)

1. **Service de transcription** (`src/services/transcription-service.js`)
   - Fonction `transcribeAudio()` qui appelle l'API Whisper d'OpenAI
   - Support multi-formats audio (webm, ogg, mp3, wav, mp4)
   - Optimisé pour la langue française

2. **Controller** (`src/controllers/transcription-controller.js`)
   - Middleware Multer pour upload de fichiers (limite 25MB)
   - Validation des types de fichiers audio
   - Gestion d'erreurs complète

3. **Route** (`src/routes/chat.js`)
   - Nouveau endpoint `POST /transcribe`
   - Authentification via X-WP-Nonce
   - Middleware `uploadAudio` pour traiter le fichier

4. **Dépendances** (`package.json`)
   - `multer@^1.4.5-lts.1` - Upload de fichiers
   - `form-data@^4.0.0` - FormData pour Whisper API

### Frontend (WordPress Plugin)

1. **Module AudioRecorder** (`assets/js/audio-recorder.js`)
   - Classe `AudioRecorder` avec MediaRecorder API
   - Configuration optimisée: 16kHz, echo cancellation, noise suppression
   - Détection du support navigateur
   - Gestion des permissions microphone

2. **Interface Chat** (`assets/js/chat-interface.js`)
   - Bouton micro SVG avec icône et indicateur d'enregistrement
   - 6 nouvelles méthodes:
     * `initAudioRecorder()` - Initialisation
     * `handleMicClick()` - Gestion du clic
     * `startRecording()` - Démarrage enregistrement
     * `stopRecording()` - Arrêt et transcription
     * `transcribeAudio()` - Envoi au backend
     * `showTranscriptionToast()` - Feedback utilisateur
   - Intégration dans `cacheElements()`, `attachEventListeners()`, `init()`

3. **Styles CSS** (`assets/css/chat-interface.css`)
   - Styles du bouton micro avec hover et focus
   - Animation pulse pour l'état recording
   - Toast de transcription avec animation slideUp
   - Layout ajusté pour inclure le bouton

4. **WordPress Plugin** (`lehiboo-ai-assistant.php`)
   - Script `audio-recorder.js` ajouté à `wp_enqueue_scripts`
   - Dépendance configurée pour `chat-interface.js`

## 🔧 Instructions de déploiement

### 1. Backend (preprod.lehiboo.com)

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend

# Pull les derniers changements
git pull origin main

# IMPORTANT: Rebuild Docker pour installer multer et form-data
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Vérifier les logs
docker-compose logs -f backend | grep -E "transcrib|Whisper"
```

**Vérifications backend:**
- ✅ Route `/transcribe` accessible
- ✅ Whisper API key configurée dans `.env`: `OPENAI_API_KEY=sk-...`
- ✅ Dépendances installées: `multer`, `form-data`

### 2. Frontend (WordPress)

```bash
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com

# Pull les derniers changements
git pull origin main

# Vider le cache WordPress si nécessaire
# (depuis wp-admin ou via WP-CLI)
wp cache flush
```

**Vérifications frontend:**
- ✅ Script `audio-recorder.js` chargé dans les DevTools
- ✅ Bouton micro visible dans le chat
- ✅ `window.AudioRecorder` défini dans la console
- ✅ Permissions micro demandées au clic

## 🧪 Plan de test

### Test 1: Vérification visuelle
1. Ouvrir le chat Le Hiboo
2. ✅ Le bouton micro doit apparaître à côté du bouton d'envoi
3. ✅ Le bouton micro doit avoir une icône de micro

### Test 2: Enregistrement basique
1. Cliquer sur le bouton micro
2. ✅ Permission micro demandée (si première fois)
3. ✅ Bouton devient rouge avec animation pulse
4. ✅ Parler quelques secondes
5. Re-cliquer pour arrêter
6. ✅ Toast "Transcription en cours..." s'affiche
7. ✅ Toast "Transcription terminée ✓" apparaît
8. ✅ Texte transcrit dans le textarea
9. ✅ Auto-focus sur le textarea

### Test 3: Gestion d'erreurs
1. **Pas de microphone:** Bouton doit être caché
2. **Permission refusée:** Message d'erreur "Impossible d'accéder au microphone"
3. **Audio trop court (<1s):** Erreur backend, toast rouge
4. **Problème réseau:** Toast "Erreur de transcription"

### Test 4: Vérification backend
```bash
# Logs backend pendant un test d'enregistrement
docker-compose logs -f backend

# Devrait afficher:
# - "Transcription request received { fileSize: xxx, mimetype: 'audio/webm' }"
# - "Transcribing audio file { size: xxx, language: 'fr' }"
# - "Transcription result: { success: true, text: '...' }"
```

## 🐛 Troubleshooting

### Le bouton micro n'apparaît pas
- Vérifier dans DevTools Console: `window.AudioRecorder`
- Si `undefined`: Script non chargé, vérifier `wp_enqueue_script` dans plugin PHP
- Vider le cache navigateur et WordPress

### Erreur 404 sur /transcribe
- Backend non déployé ou route manquante
- Vérifier `docker-compose logs backend | grep "POST /transcribe"`

### Erreur 401 sur /transcribe
- Problème de nonce WordPress
- Vérifier que le header `X-WP-Nonce` est envoyé
- Vérifier `this.config.nonce` dans chat-interface.js

### Transcription vide
- Audio trop court (< 1 seconde)
- Pas de parole détectée
- Vérifier qualité du micro

### Erreur OpenAI Whisper
- Vérifier API Key: `OPENAI_API_KEY` dans `.env`
- Vérifier logs: `docker-compose logs backend | grep Whisper`
- Vérifier quota OpenAI (https://platform.openai.com/usage)

## 📊 Monitoring post-déploiement

### Métriques à surveiller
- Taux d'utilisation du bouton micro (analytics frontend)
- Taux de succès/échec des transcriptions (logs backend)
- Durée moyenne des transcriptions
- Coût Whisper API (OpenAI dashboard)

### Logs à monitorer
```bash
# Backend - Erreurs de transcription
docker-compose logs backend | grep -E "Transcription.*error|Whisper.*error"

# Backend - Stats d'utilisation
docker-compose logs backend | grep "Transcription request received" | wc -l
```

## 💰 Coûts estimés

**OpenAI Whisper API:**
- Prix: $0.006 / minute d'audio
- Estimation: ~100 transcriptions/jour de 30s = 50 minutes/jour
- **Coût journalier: ~$0.30 (9€/mois)**

## 🎯 Prochaines optimisations possibles

1. **Cache côté serveur:** Éviter de retranscrire le même audio
2. **Compression audio:** Réduire la taille avant envoi (opus codec)
3. **Limitation rate:** Max 10 transcriptions/heure par utilisateur
4. **Analytics:** Tracker l'utilisation et la satisfaction
5. **Support multi-langues:** Détection automatique de la langue

## ✅ Checklist de déploiement

### Backend
- [ ] `git pull` sur le serveur backend
- [ ] `docker-compose build --no-cache`
- [ ] `docker-compose up -d`
- [ ] Vérifier logs: pas d'erreurs au démarrage
- [ ] Tester route: `curl -X POST https://preprod.lehiboo.com/api/transcribe` (devrait retourner 400 "No audio file")

### Frontend
- [ ] `git pull` sur le serveur WordPress
- [ ] Vider cache WordPress
- [ ] Vérifier script chargé dans DevTools
- [ ] Tester bouton micro visible

### Tests fonctionnels
- [ ] Enregistrement de 5 secondes → transcription OK
- [ ] Enregistrement en français → texte correct
- [ ] Gestion permission refusée → erreur claire
- [ ] Toast "Transcription en cours" visible
- [ ] Texte inséré dans textarea

### Validation finale
- [ ] Tester sur Chrome (Desktop + Mobile)
- [ ] Tester sur Safari (Desktop + Mobile)
- [ ] Tester sur Firefox (Desktop)
- [ ] Vérifier logs backend: pas d'erreurs

## 📞 Contacts en cas de problème

- **Backend/API:** Vérifier logs Docker et OpenAI dashboard
- **Frontend:** DevTools Console + Network tab
- **Documentation:** AUDIO_INTEGRATION_GUIDE.md

---

**Date de création:** 2025-10-29
**Version:** 1.0.0
**Commits:** 91cbb3ac, 925c2ff8, ede0fd18
