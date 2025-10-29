# 🎤 Guide d'intégration du bouton micro (transcription audio)

## ✅ Ce qui est déjà fait

### Backend
- ✅ Service de transcription Whisper (`transcription-service.js`)
- ✅ Controller avec upload multer (`transcription-controller.js`)
- ✅ Route `/transcribe` configurée
- ✅ Dépendances installées (multer, form-data)

### Frontend
- ✅ Module `AudioRecorder` (`audio-recorder.js`)
- ✅ Gestion permissions micro
- ✅ Enregistrement audio avec optimisations

## 📋 TODO : Intégrer le bouton micro dans le chat

### 1. Charger le script audio-recorder.js

Dans le template WordPress (ou dans le fichier qui charge chat-interface.js), ajouter :

```php
// Avant chat-interface.js
wp_enqueue_script(
    'lehiboo-audio-recorder',
    plugins_url('assets/js/audio-recorder.js', __FILE__),
    array(), // Pas de dépendances
    '1.0.0',
    true
);
```

### 2. Ajouter le bouton micro dans le HTML

Dans `chat-interface.js`, méthode `buildHTML()`, ligne ~250, ajouter le bouton micro à côté du bouton send :

```javascript
// Dans la section textarea controls
<div class="lehiboo-textarea-controls">
  <button type="button" class="lehiboo-mic-button" aria-label="Enregistrer un message vocal" title="Enregistrer un message vocal">
    <svg class="lehiboo-mic-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
      <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
      <line x1="12" y1="19" x2="12" y2="23"/>
      <line x1="8" y1="23" x2="16" y2="23"/>
    </svg>
    <span class="lehiboo-recording-indicator">●</span>
  </button>
  <button type="submit" class="lehiboo-send-button">Envoyer</button>
  ...
</div>
```

### 3. Ajouter les styles CSS

Dans `chat-styles.css`, ajouter :

```css
/* Bouton micro */
.lehiboo-mic-button {
  background: none;
  border: none;
  padding: 10px;
  cursor: pointer;
  color: var(--primary-color);
  position: relative;
  transition: all 0.2s ease;
}

.lehiboo-mic-button:hover {
  color: var(--primary-dark);
  transform: scale(1.1);
}

.lehiboo-mic-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.lehiboo-mic-icon {
  width: 20px;
  height: 20px;
}

/* Indicateur d'enregistrement (caché par défaut) */
.lehiboo-recording-indicator {
  display: none;
  position: absolute;
  top: 2px;
  right: 2px;
  color: #ef4444;
  font-size: 12px;
  animation: pulse 1.5s ease-in-out infinite;
}

.lehiboo-mic-button.recording .lehiboo-mic-icon {
  display: none;
}

.lehiboo-mic-button.recording .lehiboo-recording-indicator {
  display: block;
}

.lehiboo-mic-button.recording {
  color: #ef4444;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Toast pour feedback transcription */
.lehiboo-transcription-toast {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  background: #10b981;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  z-index: 10001;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateX(-50%) translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
  }
}
```

### 4. Ajouter la logique dans chat-interface.js

Dans la méthode `cacheElements()`, ajouter :

```javascript
this.elements.micButton = this.elements.widget.querySelector('.lehiboo-mic-button');
```

Dans `attachEventListeners()`, ajouter :

```javascript
// Mic button
if (this.elements.micButton) {
  this.elements.micButton.addEventListener('click', () => this.handleMicClick());
}
```

Ajouter ces nouvelles méthodes dans la classe :

```javascript
/**
 * Initialiser l'audio recorder
 */
initAudioRecorder() {
  if (!window.AudioRecorder) {
    console.error('AudioRecorder module not loaded');
    return;
  }

  if (!AudioRecorder.isSupported()) {
    console.warn('Audio recording not supported in this browser');
    if (this.elements.micButton) {
      this.elements.micButton.style.display = 'none';
    }
    return;
  }

  this.audioRecorder = new AudioRecorder();
  this.isRecording = false;
}

/**
 * Gérer le clic sur le bouton micro
 */
async handleMicClick() {
  if (!this.audioRecorder) {
    this.showError('Enregistrement audio non disponible');
    return;
  }

  if (this.isRecording) {
    // Arrêter l'enregistrement
    await this.stopRecording();
  } else {
    // Démarrer l'enregistrement
    await this.startRecording();
  }
}

/**
 * Démarrer l'enregistrement
 */
async startRecording() {
  try {
    await this.audioRecorder.startRecording();
    this.isRecording = true;

    // UI feedback
    this.elements.micButton.classList.add('recording');
    this.elements.micButton.setAttribute('aria-label', 'Arrêter l\'enregistrement');
    this.elements.micButton.title = 'Cliquez pour arrêter';

    this.log('Recording started');
  } catch (error) {
    this.log('Error starting recording:', error);
    this.showError(error.message || 'Impossible de démarrer l\'enregistrement');
  }
}

/**
 * Arrêter l'enregistrement et transcrire
 */
async stopRecording() {
  try {
    this.log('Stopping recording...');

    // UI feedback
    this.elements.micButton.classList.remove('recording');
    this.elements.micButton.setAttribute('aria-label', 'Enregistrer un message vocal');
    this.elements.micButton.title = 'Enregistrer un message vocal';
    this.elements.micButton.disabled = true;

    // Arrêter l'enregistrement
    const audioBlob = await this.audioRecorder.stopRecording();
    this.isRecording = false;

    this.log('Audio blob received, size:', audioBlob.size);

    // Afficher feedback
    this.showTranscriptionToast('Transcription en cours...');

    // Transcrire
    const transcription = await this.transcribeAudio(audioBlob);

    if (transcription.success) {
      // Insérer le texte dans le textarea
      this.elements.textarea.value = transcription.text;
      this.handleTextareaInput({ target: this.elements.textarea });

      this.showTranscriptionToast('Transcription terminée ✓', 'success');

      // Auto-focus sur le textarea
      this.elements.textarea.focus();
    } else {
      throw new Error(transcription.error || 'Transcription failed');
    }

  } catch (error) {
    this.log('Error stopping recording:', error);
    this.showError('Erreur lors de la transcription');
  } finally {
    this.elements.micButton.disabled = false;
  }
}

/**
 * Transcrire un blob audio
 */
async transcribeAudio(audioBlob) {
  try {
    // Préparer le FormData
    const formData = new FormData();
    formData.append('audio', audioBlob, 'recording.webm');
    formData.append('language', 'fr'); // Langue française

    // Envoyer au backend
    const response = await fetch(`${this.config.apiBaseUrl}/transcribe`, {
      method: 'POST',
      headers: {
        'X-API-Key': this.config.apiKey || ''
      },
      body: formData
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const result = await response.json();
    this.log('Transcription result:', result);

    return result;

  } catch (error) {
    this.log('Transcription error:', error);
    return {
      success: false,
      error: error.message
    };
  }
}

/**
 * Afficher un toast de feedback transcription
 */
showTranscriptionToast(message, type = 'info') {
  // Supprimer un éventuel toast existant
  const existing = document.querySelector('.lehiboo-transcription-toast');
  if (existing) {
    existing.remove();
  }

  // Créer le toast
  const toast = document.createElement('div');
  toast.className = 'lehiboo-transcription-toast';
  toast.textContent = message;

  if (type === 'success') {
    toast.style.background = '#10b981';
  } else if (type === 'error') {
    toast.style.background = '#ef4444';
  }

  document.body.appendChild(toast);

  // Auto-supprimer après 3s
  setTimeout(() => {
    toast.remove();
  }, 3000);
}
```

Dans le `constructor` ou `init()`, ajouter :

```javascript
// Initialiser l'audio recorder
this.initAudioRecorder();
```

### 5. Déployer

```bash
# Backend (rebuild obligatoire pour installer multer + form-data)
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com/lehiboo-ai-backend
git pull
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Frontend (refresh cache si besoin)
cd /var/www/vhosts/lehiboo.com/preprod.lehiboo.com
git pull
# Clear cache WordPress si nécessaire
```

### 6. Tester

1. Ouvrir le chat
2. Vérifier que le bouton micro apparaît (si pas de micro ou navigateur incompatible, il sera caché)
3. Cliquer sur le micro → autoriser l'accès micro
4. Parler quelques secondes
5. Re-cliquer pour arrêter
6. Voir "Transcription en cours..."
7. Le texte transcrit apparaît dans le textarea
8. Cliquer "Envoyer" ou modifier le texte

## 🐛 Troubleshooting

### Le bouton n'apparaît pas
- Vérifier que `audio-recorder.js` est bien chargé (console : `window.AudioRecorder`)
- Vérifier le navigateur supporte MediaRecorder

### Erreur "Impossible d'accéder au microphone"
- Vérifier que le site est en HTTPS (requis pour getUserMedia)
- Vérifier les permissions micro dans le navigateur

### Erreur 401 sur /transcribe
- Vérifier que l'API key est bien passée dans le header `X-API-Key`

### Transcription vide ou erreur
- Vérifier que l'audio est > 1 seconde
- Vérifier les logs backend : `docker-compose logs backend | grep transcrib`

## 📚 Références

- [MediaRecorder API](https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder)
- [OpenAI Whisper API](https://platform.openai.com/docs/guides/speech-to-text)
- [getUserMedia](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia)
