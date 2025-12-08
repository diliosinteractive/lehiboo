/**
 * Image Editor for Vendor Media Manager
 * Uses Cropper.js
 * @package EventList
 */

(function($) {
    'use strict';

    window.EL_MediaEditor = {

        currentCropper: null,
        currentFile: null,
        currentImage: null,

        /**
         * Ouvrir l'éditeur d'image depuis un fichier
         */
        openEditor: function(file, callback) {
            const self = this;
            this.currentFile = file;
            this.callback = callback;

            // Créer le reader pour charger l'image
            const reader = new FileReader();

            reader.onload = function(e) {
                self.showEditorModal(e.target.result);
            };

            reader.readAsDataURL(file);
        },

        /**
         * Ouvrir l'éditeur depuis une URL (pour éditer une image existante)
         */
        openEditorFromUrl: function(imageUrl, attachmentId, callback, imageData) {
            const self = this;
            this.currentFile = null;
            this.currentAttachmentId = attachmentId;
            this.currentImageData = imageData || {};
            this.callback = callback;
            this.showEditorModal(imageUrl);
        },

        /**
         * Afficher le modal d'édition
         */
        showEditorModal: function(imageDataUrl) {
            const self = this;

            // Créer le modal s'il n'existe pas
            if ($('#media_editor_modal').length === 0) {
                this.createEditorModal();
            }

            // Réinitialiser le bouton de sauvegarde à son état initial
            const $btn = $('#media_editor_modal .btn_apply_edit');
            $btn.prop('disabled', false).text('Appliquer');

            // Charger l'image
            const $img = $('#editor_image');
            $img.attr('src', imageDataUrl);

            // Remplir les champs de métadonnées si disponibles
            if (this.currentImageData && this.currentAttachmentId) {
                $('#editor_title').val(this.currentImageData.title || '');
                $('#editor_alt_text').val(this.currentImageData.alt_text || '');
                $('#editor_folder_id').val(this.currentImageData.folder_id || 0);

                // Afficher les infos de fichier
                $('.editor_filesize').text(this.currentImageData.filesize_formatted || '-');
                $('.editor_format').text(this.currentImageData.format || '-');
                $('.editor_created').text(this.currentImageData.created_at_formatted || '-');
                $('.editor_updated').text(this.currentImageData.updated_at_formatted || '-');

                // Afficher la section métadonnées
                $('.editor_metadata_section').show();
            } else {
                // Cacher la section métadonnées pour les nouveaux fichiers
                $('.editor_metadata_section').hide();
            }

            // Afficher le modal
            $('#media_editor_modal').fadeIn(200);

            // Initialiser Cropper après que l'image soit chargée
            $img.off('load').on('load', function() {
                self.initCropper();
            });
        },

        /**
         * Créer le modal d'édition
         */
        createEditorModal: function() {
            // Récupérer la liste des dossiers depuis le DOM
            let foldersOptions = '<option value="0">Toutes les images</option>';
            $('.folders_tree .folder_item').each(function() {
                const folderId = $(this).data('folder-id');
                if (folderId && folderId !== -1 && folderId !== 0) {
                    const folderName = $(this).find('> .folder_header .folder_name').first().text();
                    const level = $(this).parents('.folder_item').length;
                    const indent = level > 0 ? '&nbsp;&nbsp;'.repeat(level) + '└ ' : '';
                    foldersOptions += '<option value="' + folderId + '">' + indent + folderName + '</option>';
                }
            });

            const modalHtml = `
                <div id="media_editor_modal" class="media_modal modal_editor" style="display: none;">
                    <div class="modal_overlay"></div>
                    <div class="modal_content modal_content_large">
                        <div class="modal_header">
                            <h3 class="modal_title">Éditer l'image</h3>
                            <button type="button" class="modal_close">&times;</button>
                        </div>
                        <div class="modal_body">
                            <div class="editor_container">
                                <div class="editor_image_wrapper">
                                    <img id="editor_image" src="" alt="Image à éditer">
                                </div>
                                <div class="editor_toolbar">
                                    <div class="toolbar_group">
                                        <button type="button" class="editor_btn" data-action="rotate-left" title="Rotation -90°">
                                            <i class="fa fa-rotate-left"></i>
                                        </button>
                                        <button type="button" class="editor_btn" data-action="rotate-right" title="Rotation +90°">
                                            <i class="fa fa-rotate-right"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar_group">
                                        <button type="button" class="editor_btn" data-action="flip-h" title="Miroir horizontal">
                                            <i class="fa fa-arrows-h"></i>
                                        </button>
                                        <button type="button" class="editor_btn" data-action="flip-v" title="Miroir vertical">
                                            <i class="fa fa-arrows-v"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar_group">
                                        <button type="button" class="editor_btn" data-action="zoom-in" title="Zoom +">
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                        <button type="button" class="editor_btn" data-action="zoom-out" title="Zoom -">
                                            <i class="fa fa-search-minus"></i>
                                        </button>
                                        <button type="button" class="editor_btn" data-action="reset" title="Réinitialiser">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </div>
                                    <div class="toolbar_group">
                                        <label class="toolbar_label">
                                            <span>Ratio:</span>
                                            <select class="aspect_ratio_select">
                                                <option value="free">Libre</option>
                                                <option value="1">1:1 (Carré)</option>
                                                <option value="1.3333">4:3</option>
                                                <option value="1.7778">16:9</option>
                                                <option value="0.75">3:4 (Portrait)</option>
                                            </select>
                                        </label>
                                    </div>
                                </div>

                                <!-- Section Métadonnées -->
                                <div class="editor_metadata_section" style="display: none;">
                                    <div class="editor_metadata_form">
                                        <div class="editor_field">
                                            <label for="editor_title">Titre :</label>
                                            <input type="text" id="editor_title" name="title" class="editor_input">
                                        </div>
                                        <div class="editor_field">
                                            <label for="editor_folder_id">Dossier :</label>
                                            <select id="editor_folder_id" name="folder_id" class="editor_select">
                                                ${foldersOptions}
                                            </select>
                                        </div>
                                        <div class="editor_field">
                                            <label for="editor_alt_text">Texte alternatif :</label>
                                            <input type="text" id="editor_alt_text" name="alt_text" class="editor_input">
                                        </div>

                                        <!-- Bloc d'informations en lecture seule -->
                                        <div class="editor_info_block">
                                            <div class="editor_info_row">
                                                <div class="editor_info">
                                                    <span class="info_label">Poids :</span>
                                                    <span class="info_value editor_filesize">-</span>
                                                </div>
                                                <div class="editor_info">
                                                    <span class="info_label">Format :</span>
                                                    <span class="info_value editor_format">-</span>
                                                </div>
                                            </div>
                                            <div class="editor_info_row">
                                                <div class="editor_info">
                                                    <span class="info_label">Date de création :</span>
                                                    <span class="info_value editor_created">-</span>
                                                </div>
                                                <div class="editor_info">
                                                    <span class="info_label">Date de mise à jour :</span>
                                                    <span class="info_value editor_updated">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal_footer">
                            <button type="button" class="el_button btn_cancel">Annuler</button>
                            <button type="button" class="el_button el_button_primary btn_apply_edit">Appliquer</button>
                        </div>
                    </div>
                </div>
            `;

            // Insérer dans .vendor_media_manager si disponible, sinon dans body
            if ($('.vendor_media_manager').length) {
                $('.vendor_media_manager').append(modalHtml);
            } else {
                $('body').append(modalHtml);
            }

            // Bind events
            this.bindEditorEvents();
        },

        /**
         * Initialiser Cropper.js
         */
        initCropper: function() {
            const self = this;
            const $img = $('#editor_image')[0];

            // Détruire l'ancien cropper s'il existe
            if (this.currentCropper) {
                this.currentCropper.destroy();
            }

            // Vérifier que Cropper est chargé
            if (typeof Cropper === 'undefined') {
                console.error('Cropper.js not loaded');
                return;
            }

            // Initialiser Cropper
            this.currentCropper = new Cropper($img, {
                viewMode: 1,
                dragMode: 'move',
                aspectRatio: NaN, // Libre par défaut
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                responsive: true,
                checkOrientation: true,
                background: false,
            });
        },

        /**
         * Bind des événements de l'éditeur
         */
        bindEditorEvents: function() {
            const self = this;

            // Actions toolbar
            $(document).on('click', '.editor_btn', function() {
                const action = $(this).data('action');
                self.executeAction(action);
            });

            // Changement de ratio
            $(document).on('change', '.aspect_ratio_select', function() {
                const ratio = $(this).val();
                if (self.currentCropper) {
                    if (ratio === 'free') {
                        self.currentCropper.setAspectRatio(NaN);
                    } else {
                        self.currentCropper.setAspectRatio(parseFloat(ratio));
                    }
                }
            });

            // Appliquer les modifications
            $(document).on('click', '.btn_apply_edit', function() {
                self.applyEdit();
            });

            // Fermer le modal
            $(document).on('click', '#media_editor_modal .modal_close, #media_editor_modal .btn_cancel', function() {
                self.closeEditor();
            });
        },

        /**
         * Exécuter une action
         */
        executeAction: function(action) {
            if (!this.currentCropper) return;

            switch (action) {
                case 'rotate-left':
                    this.currentCropper.rotate(-90);
                    break;
                case 'rotate-right':
                    this.currentCropper.rotate(90);
                    break;
                case 'flip-h':
                    this.currentCropper.scaleX(-this.currentCropper.getData().scaleX || -1);
                    break;
                case 'flip-v':
                    this.currentCropper.scaleY(-this.currentCropper.getData().scaleY || -1);
                    break;
                case 'zoom-in':
                    this.currentCropper.zoom(0.1);
                    break;
                case 'zoom-out':
                    this.currentCropper.zoom(-0.1);
                    break;
                case 'reset':
                    this.currentCropper.reset();
                    break;
            }
        },

        /**
         * Appliquer les modifications
         */
        applyEdit: function() {
            if (!this.currentCropper) return;

            const self = this;

            // Obtenir le canvas de l'image éditée
            const canvas = this.currentCropper.getCroppedCanvas({
                maxWidth: 4000,
                maxHeight: 4000,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Convertir en Blob
            canvas.toBlob(function(blob) {
                // Si c'est une image existante (depuis URL), uploader via AJAX
                if (self.currentAttachmentId && !self.currentFile) {
                    self.uploadEditedImage(blob);
                } else {
                    // Sinon, c'est un nouveau fichier avant upload
                    const fileName = self.currentFile.name;
                    const file = new File([blob], fileName, {
                        type: self.currentFile.type,
                        lastModified: Date.now()
                    });

                    // Callback avec le fichier édité
                    if (self.callback) {
                        self.callback(file);
                    }

                    self.closeEditor();
                }
            }, self.currentFile ? self.currentFile.type : 'image/jpeg', 0.92);
        },

        /**
         * Uploader l'image éditée pour remplacer l'originale
         */
        uploadEditedImage: function(blob) {
            const self = this;

            // Récupérer les métadonnées du formulaire
            const title = $('#editor_title').val();
            const altText = $('#editor_alt_text').val();
            const folderId = $('#editor_folder_id').val();

            // Créer FormData
            const formData = new FormData();
            formData.append('action', 'el_vendor_update_image');
            formData.append('nonce', window.EL_MediaManager.nonce);
            formData.append('attachment_id', this.currentAttachmentId);
            formData.append('title', title);
            formData.append('alt_text', altText);
            formData.append('folder_id', folderId);
            formData.append('image', blob, 'edited-image.jpg');

            // Désactiver le bouton de sauvegarde
            const $btn = $('#media_editor_modal .btn_apply_edit');
            $btn.prop('disabled', true).text('Sauvegarde...');

            $.ajax({
                url: window.EL_MediaManager.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Callback avec succès
                        if (self.callback) {
                            self.callback(response.data);
                        }
                        self.closeEditor();
                    } else {
                        var errorMsg = response.data.message || 'Impossible de sauvegarder l\'image';
                        if (window.ToastNotification) {
                            window.ToastNotification.error(errorMsg);
                        } else {
                            alert('Erreur: ' + errorMsg);
                        }
                        $btn.prop('disabled', false).text('Appliquer');
                    }
                },
                error: function() {
                    if (window.ToastNotification) {
                        window.ToastNotification.error('Erreur lors de la sauvegarde de l\'image');
                    } else {
                        alert('Erreur lors de la sauvegarde de l\'image');
                    }
                    $btn.prop('disabled', false).text('Appliquer');
                }
            });
        },

        /**
         * Fermer l'éditeur
         */
        closeEditor: function() {
            if (this.currentCropper) {
                this.currentCropper.destroy();
                this.currentCropper = null;
            }

            $('#media_editor_modal').fadeOut(200);
            this.currentFile = null;
            this.callback = null;
        }
    };

    // Export global
    window.MediaEditor = window.EL_MediaEditor;

})(jQuery);
