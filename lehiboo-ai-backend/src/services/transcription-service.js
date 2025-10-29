/**
 * Service de transcription audio avec OpenAI Whisper
 */

import { openai } from '@ai-sdk/openai';
import config from '../config/index.js';
import logger from '../utils/logger.js';
import FormData from 'form-data';
import fetch from 'node-fetch';

/**
 * Transcrire un fichier audio en texte avec Whisper
 *
 * @param {Buffer} audioBuffer - Buffer du fichier audio
 * @param {string} filename - Nom du fichier (avec extension)
 * @param {string} language - Code langue ISO (fr, en, etc.) ou 'auto'
 * @returns {Promise<Object>} {success, text, language, duration}
 */
export async function transcribeAudio(audioBuffer, filename = 'audio.webm', language = 'auto') {
  try {
    logger.info('Transcribing audio file', {
      size: audioBuffer.length,
      filename,
      language
    });

    // Préparer le FormData pour l'API Whisper
    const formData = new FormData();
    formData.append('file', audioBuffer, {
      filename: filename,
      contentType: getContentType(filename)
    });
    formData.append('model', 'whisper-1');

    // Si langue spécifiée (pas auto), l'ajouter
    if (language && language !== 'auto') {
      formData.append('language', language);
    }

    // Appel direct à l'API OpenAI Whisper
    const response = await fetch('https://api.openai.com/v1/audio/transcriptions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${config.openai.apiKey}`,
        ...formData.getHeaders()
      },
      body: formData
    });

    if (!response.ok) {
      const error = await response.text();
      throw new Error(`Whisper API error: ${response.status} - ${error}`);
    }

    const result = await response.json();

    logger.info('Transcription successful', {
      textLength: result.text?.length || 0,
      detectedLanguage: result.language
    });

    return {
      success: true,
      text: result.text,
      language: result.language || language,
      duration: result.duration || null
    };

  } catch (error) {
    logger.error('Transcription failed', {
      error: error.message,
      stack: error.stack
    });

    return {
      success: false,
      error: error.message,
      text: null
    };
  }
}

/**
 * Obtenir le Content-Type selon l'extension du fichier
 */
function getContentType(filename) {
  const ext = filename.split('.').pop().toLowerCase();

  const types = {
    'webm': 'audio/webm',
    'ogg': 'audio/ogg',
    'mp3': 'audio/mpeg',
    'mp4': 'audio/mp4',
    'm4a': 'audio/mp4',
    'wav': 'audio/wav',
    'flac': 'audio/flac'
  };

  return types[ext] || 'audio/webm';
}

/**
 * Tester la connexion Whisper
 */
export async function testWhisperConnection() {
  try {
    // Créer un petit buffer audio de test (silence)
    const testBuffer = Buffer.from([
      0x1a, 0x45, 0xdf, 0xa3, // EBML header for WebM
      0x00, 0x00, 0x00, 0x1f,
      0x42, 0x86, 0x81, 0x01
    ]);

    logger.info('Testing Whisper API connection...');

    const result = await transcribeAudio(testBuffer, 'test.webm', 'fr');

    if (result.success || result.error?.includes('invalid')) {
      // Même si le fichier est invalide, au moins l'API est accessible
      logger.info('✅ Whisper API connection successful');
      return true;
    }

    return false;
  } catch (error) {
    logger.error('❌ Whisper API connection failed', {
      error: error.message
    });
    return false;
  }
}

export default {
  transcribeAudio,
  testWhisperConnection
};
