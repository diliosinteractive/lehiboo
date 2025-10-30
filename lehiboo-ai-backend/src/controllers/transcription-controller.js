/**
 * Controller pour la transcription audio
 */

import { transcribeAudio } from '../services/transcription-service.js';
import logger from '../utils/logger.js';
import multer from 'multer';

// Configuration multer pour upload en mémoire
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 25 * 1024 * 1024 // 25MB max (limite Whisper)
  },
  fileFilter: (req, file, cb) => {
    logger.info('🔵 [MULTER] File filter called', {
      fieldname: file.fieldname,
      originalname: file.originalname,
      mimetype: file.mimetype,
      size: file.size
    });

    // Accepter uniquement les fichiers audio
    const allowedTypes = ['audio/webm', 'audio/ogg', 'audio/mp3', 'audio/mpeg', 'audio/mp4', 'audio/wav'];

    if (allowedTypes.includes(file.mimetype) || file.mimetype.startsWith('audio/')) {
      logger.info('🔵 [MULTER] File accepted');
      cb(null, true);
    } else {
      logger.warn('🔵 [MULTER] File rejected - unsupported type');
      cb(new Error('Format audio non supporté'));
    }
  }
});

/**
 * Middleware multer pour upload de fichier audio
 */
export const uploadAudio = (req, res, next) => {
  logger.info('🔵 [MULTER] uploadAudio middleware called', {
    method: req.method,
    path: req.path,
    contentType: req.headers['content-type']
  });

  upload.single('audio')(req, res, (err) => {
    if (err) {
      logger.error('🔵 [MULTER] Upload error', {
        error: err.message,
        code: err.code
      });
      return res.status(400).json({
        success: false,
        error: err.message
      });
    }

    logger.info('🔵 [MULTER] Upload successful', {
      fileReceived: !!req.file,
      fileName: req.file?.originalname,
      fileSize: req.file?.size
    });

    next();
  });
};

/**
 * Endpoint de transcription
 */
export async function handleTranscription(req, res) {
  logger.info('🔵 [CONTROLLER] handleTranscription called', {
    hasFile: !!req.file,
    body: req.body
  });

  try {
    // Vérifier que le fichier a été uploadé
    if (!req.file) {
      logger.warn('🔵 [CONTROLLER] No file in request');
      return res.status(400).json({
        success: false,
        error: 'No audio file provided'
      });
    }

    const { language = 'auto' } = req.body;

    logger.info('Transcription request received', {
      fileSize: req.file.size,
      mimetype: req.file.mimetype,
      originalname: req.file.originalname,
      language
    });

    // Transcrire l'audio
    const result = await transcribeAudio(
      req.file.buffer,
      req.file.originalname,
      language
    );

    if (!result.success) {
      return res.status(500).json({
        success: false,
        error: result.error || 'Transcription failed'
      });
    }

    // Retourner la transcription
    return res.json({
      success: true,
      text: result.text,
      language: result.language,
      duration: result.duration
    });

  } catch (error) {
    logger.error('Error handling transcription', {
      error: error.message,
      stack: error.stack
    });

    return res.status(500).json({
      success: false,
      error: 'Internal server error',
      message: 'Erreur lors de la transcription audio'
    });
  }
}

export default {
  uploadAudio,
  handleTranscription
};
