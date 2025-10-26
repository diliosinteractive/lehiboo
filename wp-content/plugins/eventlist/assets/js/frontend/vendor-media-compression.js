/**
 * Image Compression & Optimization for Vendor Media Manager
 * Uses browser-image-compression library
 * @package EventList
 */

(function($) {
    'use strict';

    window.EL_MediaCompression = {

        /**
         * Options de compression par défaut
         */
        defaultOptions: {
            maxSizeMB: 2,              // Taille max en MB après compression
            maxWidthOrHeight: 2500,     // Dimension max (width ou height)
            useWebWorker: true,         // Utiliser Web Worker pour ne pas bloquer l'UI
            fileType: 'auto',           // 'auto' pour garder le type original
            initialQuality: 0.8,        // Qualité JPEG/WebP (0.1 à 1.0)
        },

        /**
         * Compresser une image avant upload
         */
        compressImage: async function(file, options) {
            options = $.extend({}, this.defaultOptions, options || {});

            try {
                // Vérifier que la librairie est chargée
                if (typeof imageCompression === 'undefined') {
                    console.warn('browser-image-compression not loaded, using original file');
                    return file;
                }

                const originalSize = file.size;
                const originalDimensions = await this.getImageDimensions(file);

                console.log(`[Compression] Original: ${(originalSize / 1024 / 1024).toFixed(2)}MB, ${originalDimensions.width}x${originalDimensions.height}px`);

                // Compresser l'image
                const compressedFile = await imageCompression(file, options);

                const compressedSize = compressedFile.size;
                const savedSize = originalSize - compressedSize;
                const savedPercent = ((savedSize / originalSize) * 100).toFixed(1);

                console.log(`[Compression] Compressé: ${(compressedSize / 1024 / 1024).toFixed(2)}MB (${savedPercent}% économisé)`);

                // Retourner le fichier compressé
                return compressedFile;

            } catch (error) {
                console.error('[Compression] Error:', error);
                // En cas d'erreur, retourner le fichier original
                return file;
            }
        },

        /**
         * Compresser plusieurs images
         */
        compressMultiple: async function(files, progressCallback) {
            const compressedFiles = [];
            const total = files.length;

            for (let i = 0; i < total; i++) {
                const file = files[i];

                // Callback de progression
                if (progressCallback) {
                    progressCallback({
                        current: i + 1,
                        total: total,
                        fileName: file.name,
                        percent: Math.round(((i + 1) / total) * 100)
                    });
                }

                const compressed = await this.compressImage(file);
                compressedFiles.push(compressed);
            }

            return compressedFiles;
        },

        /**
         * Obtenir les dimensions d'une image
         */
        getImageDimensions: function(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = new Image();

                    img.onload = function() {
                        resolve({
                            width: img.width,
                            height: img.height
                        });
                    };

                    img.onerror = reject;
                    img.src = e.target.result;
                };

                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        },

        /**
         * Convertir une image en WebP (si supporté)
         */
        convertToWebP: async function(file) {
            try {
                // Vérifier le support WebP
                if (!this.isWebPSupported()) {
                    return file;
                }

                const options = $.extend({}, this.defaultOptions, {
                    fileType: 'image/webp'
                });

                return await this.compressImage(file, options);

            } catch (error) {
                console.error('[WebP Conversion] Error:', error);
                return file;
            }
        },

        /**
         * Vérifier le support WebP du navigateur
         */
        isWebPSupported: function() {
            const elem = document.createElement('canvas');

            if (elem.getContext && elem.getContext('2d')) {
                return elem.toDataURL('image/webp').indexOf('data:image/webp') === 0;
            }

            return false;
        },

        /**
         * Créer une preview optimisée (thumbnail)
         */
        createPreview: async function(file, maxSize) {
            maxSize = maxSize || 300;

            try {
                const options = {
                    maxSizeMB: 0.1,
                    maxWidthOrHeight: maxSize,
                    useWebWorker: false,
                    initialQuality: 0.7
                };

                return await this.compressImage(file, options);

            } catch (error) {
                console.error('[Preview] Error:', error);
                return file;
            }
        },

        /**
         * Valider l'image avant compression
         */
        validateImage: async function(file) {
            const errors = [];

            // Vérifier le type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errors.push('Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.');
            }

            // Vérifier la taille (avant compression)
            const maxSizeMB = 50; // 50MB max avant compression
            if (file.size > maxSizeMB * 1024 * 1024) {
                errors.push(`Fichier trop volumineux (${(file.size / 1024 / 1024).toFixed(1)}MB). Maximum ${maxSizeMB}MB.`);
            }

            // Vérifier les dimensions
            try {
                const dimensions = await this.getImageDimensions(file);
                const maxDimension = 10000; // 10000px max

                if (dimensions.width > maxDimension || dimensions.height > maxDimension) {
                    errors.push(`Dimensions trop grandes (${dimensions.width}x${dimensions.height}px). Maximum ${maxDimension}px.`);
                }
            } catch (error) {
                errors.push('Impossible de lire les dimensions de l\'image.');
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        },

        /**
         * Obtenir des infos sur l'image
         */
        getImageInfo: async function(file) {
            try {
                const dimensions = await this.getImageDimensions(file);

                return {
                    name: file.name,
                    size: file.size,
                    sizeFormatted: this.formatFileSize(file.size),
                    type: file.type,
                    width: dimensions.width,
                    height: dimensions.height,
                    aspectRatio: (dimensions.width / dimensions.height).toFixed(2)
                };
            } catch (error) {
                return null;
            }
        },

        /**
         * Formater la taille de fichier
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        /**
         * Estimer la taille après compression
         */
        estimateCompressedSize: async function(file) {
            try {
                // Ratio de compression estimé
                const estimatedRatio = 0.3; // 30% de la taille originale en moyenne

                const dimensions = await this.getImageDimensions(file);
                const currentSize = file.size;

                // Calculer la réduction de dimensions
                const maxDim = Math.max(dimensions.width, dimensions.height);
                const targetDim = this.defaultOptions.maxWidthOrHeight;

                let sizeReduction = 1;
                if (maxDim > targetDim) {
                    const scale = targetDim / maxDim;
                    sizeReduction = scale * scale; // Réduction quadratique
                }

                const estimatedSize = currentSize * sizeReduction * estimatedRatio;

                return {
                    original: currentSize,
                    estimated: Math.round(estimatedSize),
                    savings: Math.round(currentSize - estimatedSize),
                    savingsPercent: Math.round((1 - (estimatedSize / currentSize)) * 100)
                };

            } catch (error) {
                return null;
            }
        }
    };

    // Export global
    window.MediaCompression = window.EL_MediaCompression;

})(jQuery);
