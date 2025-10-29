/**
 * Audio Recorder Module
 * Enregistre l'audio du microphone et retourne un blob audio
 */

class AudioRecorder {
  constructor() {
    this.mediaRecorder = null;
    this.audioChunks = [];
    this.stream = null;
    this.isRecording = false;
  }

  /**
   * Vérifier si le navigateur supporte l'enregistrement audio
   */
  static isSupported() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
  }

  /**
   * Démarrer l'enregistrement
   */
  async startRecording() {
    if (!AudioRecorder.isSupported()) {
      throw new Error('Votre navigateur ne supporte pas l\'enregistrement audio');
    }

    if (this.isRecording) {
      console.warn('Recording already in progress');
      return;
    }

    try {
      // Demander l'accès au microphone
      this.stream = await navigator.mediaDevices.getUserMedia({
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          sampleRate: 16000 // Whisper préfère 16kHz
        }
      });

      // Créer le MediaRecorder
      const mimeType = this.getSupportedMimeType();
      this.mediaRecorder = new MediaRecorder(this.stream, {
        mimeType: mimeType
      });

      this.audioChunks = [];

      // Collecter les données audio
      this.mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          this.audioChunks.push(event.data);
        }
      };

      // Démarrer l'enregistrement
      this.mediaRecorder.start();
      this.isRecording = true;

      console.log('🎤 Recording started with', mimeType);
    } catch (error) {
      console.error('Error starting recording:', error);
      throw new Error('Impossible d\'accéder au microphone. Vérifiez les permissions.');
    }
  }

  /**
   * Arrêter l'enregistrement et retourner le blob audio
   */
  async stopRecording() {
    if (!this.isRecording || !this.mediaRecorder) {
      throw new Error('No recording in progress');
    }

    return new Promise((resolve, reject) => {
      this.mediaRecorder.onstop = () => {
        // Créer le blob audio
        const mimeType = this.mediaRecorder.mimeType;
        const audioBlob = new Blob(this.audioChunks, { type: mimeType });

        // Nettoyer
        this.cleanup();

        console.log('🎤 Recording stopped, blob size:', audioBlob.size, 'bytes');
        resolve(audioBlob);
      };

      this.mediaRecorder.onerror = (error) => {
        console.error('MediaRecorder error:', error);
        this.cleanup();
        reject(error);
      };

      // Arrêter l'enregistrement
      this.mediaRecorder.stop();
      this.isRecording = false;
    });
  }

  /**
   * Annuler l'enregistrement en cours
   */
  cancelRecording() {
    if (this.mediaRecorder && this.isRecording) {
      this.mediaRecorder.stop();
      this.isRecording = false;
      this.cleanup();
      console.log('🎤 Recording cancelled');
    }
  }

  /**
   * Nettoyer les ressources
   */
  cleanup() {
    if (this.stream) {
      this.stream.getTracks().forEach(track => track.stop());
      this.stream = null;
    }
    this.audioChunks = [];
  }

  /**
   * Obtenir le type MIME supporté par le navigateur
   */
  getSupportedMimeType() {
    const types = [
      'audio/webm;codecs=opus',
      'audio/webm',
      'audio/ogg;codecs=opus',
      'audio/mp4',
      'audio/mpeg'
    ];

    for (const type of types) {
      if (MediaRecorder.isTypeSupported(type)) {
        return type;
      }
    }

    // Fallback
    return 'audio/webm';
  }

  /**
   * Obtenir la durée d'enregistrement actuelle (en secondes)
   */
  getRecordingDuration() {
    // Cette méthode pourrait être améliorée avec un timer
    return 0;
  }
}

// Export pour utilisation
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AudioRecorder;
} else {
  window.AudioRecorder = AudioRecorder;
}
