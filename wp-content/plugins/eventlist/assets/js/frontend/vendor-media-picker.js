/**
 * Media Picker Modal - Sélection d'images depuis le gestionnaire de médias
 * @package EventList
 */

(function($) {
    'use strict';

    window.EL_MediaPicker = {

        isOpen: false,
        mode: 'single', // 'single' ou 'multiple'
        selectedImages: [],
        callback: null,
        currentFolder: 0,
        currentPage: 1,
        perPage: 20,
        loadedImages: [],
        maxSelection: 0, // 0 = illimité
        pendingUploadFile: null, // Fichier en attente d'upload

        /**
         * Ouvrir le picker
         * @param {Object} options - Configuration
         * @param {string} options.mode - 'single' ou 'multiple'
         * @param {Function} options.callback - Fonction appelée avec les images sélectionnées
         * @param {number} options.maxSelection - Nombre max d'images (0 = illimité)
         * @param {Array} options.selected - IDs des images déjà sélectionnées
         * @param {string} options.title - Titre du modal
         */
        open: function(options) {
            const self = this;

            this.mode = options.mode || 'single';
            this.callback = options.callback;
            this.maxSelection = options.maxSelection || 0;
            this.selectedImages = options.selected || [];
            this.currentFolder = 0;
            this.currentPage = 1;

            // Créer le modal s'il n'existe pas
            if ($('#media_picker_modal').length === 0) {
                this.createModal();
            }

            // Configurer le titre
            const title = options.title || (this.mode === 'single' ? 'Choisir une image' : 'Choisir des images');
            $('#media_picker_modal .modal_title').text(title);

            // Configurer le bouton de confirmation
            const btnText = this.mode === 'single' ? 'Sélectionner' : 'Ajouter les images';
            $('#media_picker_modal .btn_confirm_selection').text(btnText);

            // Reset
            $('#media_picker_modal .picker_search').val('');
            this.updateSelectionCount();

            // Ouvrir
            $('#media_picker_modal').fadeIn(200);
            this.isOpen = true;

            // Charger les images
            this.loadImages();
        },

        /**
         * Fermer le picker
         */
        close: function() {
            $('#media_picker_modal').fadeOut(200);
            this.isOpen = false;
            this.selectedImages = [];
            this.callback = null;
        },

        /**
         * Créer le modal HTML
         */
        createModal: function() {
            const modalHtml = `
                <div id="media_picker_modal" class="media_modal modal_picker" style="display: none;">
                    <div class="modal_overlay"></div>
                    <div class="modal_content modal_content_fullscreen">
                        <div class="modal_header">
                            <h3 class="modal_title">Choisir une image</h3>
                            <button type="button" class="modal_close">&times;</button>
                        </div>
                        <div class="modal_body">
                            <div class="picker_layout">
                                <!-- Sidebar Dossiers -->
                                <div class="picker_sidebar">
                                    <div class="picker_folders">
                                        <div class="picker_folder_item active" data-folder-id="0">
                                            <i class="fa fa-home"></i>
                                            <span>Toutes les images</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zone principale -->
                                <div class="picker_main">
                                    <!-- Toolbar -->
                                    <div class="picker_toolbar">
                                        <div class="picker_toolbar_left">
                                            <div class="picker_search_wrapper">
                                                <input type="text" class="picker_search" placeholder="Rechercher...">
                                                <i class="fa fa-search"></i>
                                            </div>
                                        </div>
                                        <div class="picker_toolbar_right">
                                            <button type="button" class="el_button btn_picker_upload">
                                                <i class="fa fa-cloud-upload-alt"></i> Ajouter
                                            </button>
                                            <div class="picker_selection_count">
                                                <span class="selection_text">0 sélectionnée(s)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Zone d'upload cachée -->
                                    <div class="picker_upload_zone" style="display: none;">
                                        <div class="picker_dropzone">
                                            <input type="file" class="picker_file_input" accept="image/*" multiple style="display: none;">
                                            <div class="dropzone_content">
                                                <i class="fa fa-cloud-upload-alt"></i>
                                                <p>Glissez vos images ici ou <span class="browse_link">parcourez</span></p>
                                                <span class="dropzone_hint">JPG, PNG, GIF, WebP - Max 5MB</span>
                                            </div>
                                            <div class="upload_progress" style="display: none;">
                                                <div class="progress_bar"><div class="progress_fill"></div></div>
                                                <span class="progress_text">Upload en cours...</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn_close_upload">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Grille d'images -->
                                    <div class="picker_grid_container">
                                        <div class="picker_loader" style="display: none;">
                                            <i class="fa fa-spinner fa-spin"></i>
                                            <span>Chargement...</span>
                                        </div>
                                        <div class="picker_empty" style="display: none;">
                                            <i class="fa fa-images"></i>
                                            <p>Aucune image dans ce dossier</p>
                                            <button type="button" class="el_button btn_upload_new">
                                                <i class="fa fa-cloud-upload"></i> Ajouter une image
                                            </button>
                                        </div>
                                        <div class="picker_grid"></div>
                                        <div class="picker_pagination"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal_footer">
                            <button type="button" class="el_button btn_cancel">Annuler</button>
                            <button type="button" class="el_button el_button_primary btn_confirm_selection" disabled>
                                Sélectionner
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            this.bindEvents();
            this.loadFolders();
        },

        /**
         * Bind des événements
         */
        bindEvents: function() {
            const self = this;

            // Fermer le modal
            $(document).on('click', '#media_picker_modal .modal_close, #media_picker_modal .modal_overlay, #media_picker_modal .btn_cancel', function(e) {
                if ($(e.target).is('.modal_overlay') || $(e.target).is('.modal_close') || $(e.target).is('.btn_cancel')) {
                    self.close();
                }
            });

            // Clic sur un dossier
            $(document).on('click', '#media_picker_modal .picker_folder_item', function() {
                const folderId = $(this).data('folder-id');
                $('#media_picker_modal .picker_folder_item').removeClass('active');
                $(this).addClass('active');
                self.currentFolder = folderId;
                self.currentPage = 1;
                self.loadImages();
            });

            // Clic sur une image
            $(document).on('click', '#media_picker_modal .picker_image_item', function(e) {
                // Ne pas traiter si c'est un clic sur un bouton d'action
                if ($(e.target).closest('.picker_item_actions').length) return;

                const $item = $(this);
                const imageId = parseInt($item.data('id'));
                const imageData = {
                    id: imageId,
                    url: $item.data('url'),
                    thumb: $item.data('thumb'),
                    title: $item.data('title')
                };

                if (self.mode === 'single') {
                    // Mode simple : une seule sélection
                    $('#media_picker_modal .picker_image_item').removeClass('selected');
                    $item.addClass('selected');
                    self.selectedImages = [imageData];
                } else {
                    // Mode multiple : toggle
                    if ($item.hasClass('selected')) {
                        $item.removeClass('selected');
                        self.selectedImages = self.selectedImages.filter(img => img.id !== imageId);
                    } else {
                        // Vérifier la limite
                        if (self.maxSelection > 0 && self.selectedImages.length >= self.maxSelection) {
                            if (window.ToastNotification) {
                                window.ToastNotification.warning('Vous ne pouvez sélectionner que ' + self.maxSelection + ' image(s)');
                            }
                            return;
                        }
                        $item.addClass('selected');
                        self.selectedImages.push(imageData);
                    }
                }

                self.updateSelectionCount();
            });

            // Recherche
            let searchTimeout;
            $(document).on('input', '#media_picker_modal .picker_search', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val();
                searchTimeout = setTimeout(function() {
                    self.currentPage = 1;
                    if (query.length >= 2) {
                        self.searchImages(query);
                    } else if (query.length === 0) {
                        self.loadImages();
                    }
                }, 500);
            });

            // Confirmer la sélection
            $(document).on('click', '#media_picker_modal .btn_confirm_selection', function() {
                if (self.selectedImages.length > 0 && self.callback) {
                    self.callback(self.selectedImages);
                }
                self.close();
            });

            // Ajouter une nouvelle image - Afficher la zone d'upload
            $(document).on('click', '#media_picker_modal .btn_upload_new, #media_picker_modal .btn_picker_upload', function() {
                self.showUploadZone();
            });

            // Fermer la zone d'upload
            $(document).on('click', '#media_picker_modal .btn_close_upload', function() {
                self.hideUploadZone();
            });

            // Clic sur "parcourez" pour ouvrir le file picker
            $(document).on('click', '#media_picker_modal .browse_link, #media_picker_modal .dropzone_content', function() {
                $('#media_picker_modal .picker_file_input').click();
            });

            // Sélection de fichier
            $(document).on('change', '#media_picker_modal .picker_file_input', function() {
                const files = this.files;
                if (files && files.length > 0) {
                    self.uploadFiles(files);
                }
            });

            // Drag & Drop
            $(document).on('dragover dragenter', '#media_picker_modal .picker_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $(document).on('dragleave dragend', '#media_picker_modal .picker_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $(document).on('drop', '#media_picker_modal .picker_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files && files.length > 0) {
                    self.uploadFiles(files);
                }
            });
        },

        /**
         * Afficher la zone d'upload
         */
        showUploadZone: function() {
            $('#media_picker_modal .picker_upload_zone').slideDown(200);
            $('#media_picker_modal .btn_picker_upload').addClass('active');
        },

        /**
         * Masquer la zone d'upload
         */
        hideUploadZone: function() {
            $('#media_picker_modal .picker_upload_zone').slideUp(200);
            $('#media_picker_modal .btn_picker_upload').removeClass('active');
            // Reset l'input file
            $('#media_picker_modal .picker_file_input').val('');
        },

        /**
         * Uploader des fichiers
         */
        uploadFiles: function(files) {
            const self = this;
            const $dropzone = $('#media_picker_modal .picker_dropzone');
            const $content = $dropzone.find('.dropzone_content');
            const $progress = $dropzone.find('.upload_progress');
            const $progressFill = $progress.find('.progress_fill');
            const $progressText = $progress.find('.progress_text');

            // Valider les fichiers
            const validFiles = [];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!allowedTypes.includes(file.type)) {
                    if (window.ToastNotification) {
                        window.ToastNotification.error('Type de fichier non supporté: ' + file.name);
                    }
                    continue;
                }
                if (file.size > maxSize) {
                    if (window.ToastNotification) {
                        window.ToastNotification.error('Fichier trop volumineux: ' + file.name);
                    }
                    continue;
                }
                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            // Afficher la progression
            $content.hide();
            $progress.show();
            $progressFill.css('width', '0%');
            $progressText.text('Upload en cours... 0/' + validFiles.length);

            // Upload séquentiel
            let uploadedCount = 0;
            const uploadNext = function(index) {
                if (index >= validFiles.length) {
                    // Tous les uploads terminés
                    $progress.hide();
                    $content.show();
                    self.hideUploadZone();
                    self.loadImages(); // Recharger la grille

                    if (window.ToastNotification) {
                        window.ToastNotification.success(uploadedCount + ' image(s) ajoutée(s)');
                    }
                    return;
                }

                const file = validFiles[index];
                const formData = new FormData();
                formData.append('action', 'el_vendor_upload_image');
                formData.append('nonce', window.EL_MediaManager.nonce);
                formData.append('image', file);
                formData.append('folder_id', self.currentFolder);

                $.ajax({
                    url: window.EL_MediaManager.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                const totalPercent = Math.round(((index + (percent / 100)) / validFiles.length) * 100);
                                $progressFill.css('width', totalPercent + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            uploadedCount++;
                        }
                        $progressText.text('Upload en cours... ' + (index + 1) + '/' + validFiles.length);
                        uploadNext(index + 1);
                    },
                    error: function() {
                        $progressText.text('Upload en cours... ' + (index + 1) + '/' + validFiles.length);
                        uploadNext(index + 1);
                    }
                });
            };

            uploadNext(0);
        },

        /**
         * Charger les dossiers
         */
        loadFolders: function() {
            const self = this;

            if (!window.EL_MediaManager) {
                console.warn('EL_MediaManager not available');
                return;
            }

            $.ajax({
                url: window.EL_MediaManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_get_folders',
                    nonce: window.EL_MediaManager.nonce
                },
                success: function(response) {
                    if (response.success && response.data.folders) {
                        self.renderFolders(response.data.folders);
                    }
                }
            });
        },

        /**
         * Rendre les dossiers
         */
        renderFolders: function(folders) {
            const $container = $('#media_picker_modal .picker_folders');

            // Garder "Toutes les images"
            let html = `
                <div class="picker_folder_item active" data-folder-id="0">
                    <i class="fa fa-home"></i>
                    <span>Toutes les images</span>
                </div>
            `;

            // Fonction récursive pour les dossiers imbriqués
            const renderFolder = (folder, level) => {
                const indent = level * 16;
                html += `
                    <div class="picker_folder_item" data-folder-id="${folder.id}" style="padding-left: ${12 + indent}px;">
                        <i class="fa fa-folder" style="color: ${folder.color || '#FF6B35'}"></i>
                        <span>${folder.name}</span>
                        <span class="folder_count">(${folder.image_count || 0})</span>
                    </div>
                `;

                if (folder.children && folder.children.length > 0) {
                    folder.children.forEach(child => renderFolder(child, level + 1));
                }
            };

            folders.forEach(folder => renderFolder(folder, 0));

            $container.html(html);
        },

        /**
         * Charger les images
         */
        loadImages: function() {
            const self = this;

            if (!window.EL_MediaManager) {
                console.warn('EL_MediaManager not available');
                return;
            }

            this.showLoader();

            $.ajax({
                url: window.EL_MediaManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_get_images',
                    nonce: window.EL_MediaManager.nonce,
                    folder_id: this.currentFolder,
                    page: this.currentPage,
                    per_page: this.perPage
                },
                success: function(response) {
                    self.hideLoader();
                    if (response.success) {
                        self.loadedImages = response.data.images || [];
                        self.renderImages(self.loadedImages);
                        self.renderPagination(response.data.pages, self.currentPage);
                    }
                },
                error: function() {
                    self.hideLoader();
                }
            });
        },

        /**
         * Rechercher des images
         */
        searchImages: function(query) {
            const self = this;

            if (!window.EL_MediaManager) return;

            this.showLoader();

            $.ajax({
                url: window.EL_MediaManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_search_images',
                    nonce: window.EL_MediaManager.nonce,
                    search: query,
                    page: 1,
                    per_page: this.perPage
                },
                success: function(response) {
                    self.hideLoader();
                    if (response.success) {
                        self.loadedImages = response.data.images || [];
                        self.renderImages(self.loadedImages);
                        self.renderPagination(response.data.pages, 1);
                    }
                }
            });
        },

        /**
         * Rendre les images
         */
        renderImages: function(images) {
            const $grid = $('#media_picker_modal .picker_grid');
            const $empty = $('#media_picker_modal .picker_empty');
            const self = this;

            $grid.empty();

            if (!images || images.length === 0) {
                $grid.hide();
                $empty.show();
                return;
            }

            $empty.hide();
            $grid.show();

            images.forEach(function(image) {
                const isSelected = self.selectedImages.some(img => img.id === parseInt(image.attachment_id));
                const selectedClass = isSelected ? 'selected' : '';

                const html = `
                    <div class="picker_image_item ${selectedClass}"
                         data-id="${image.attachment_id}"
                         data-url="${image.url}"
                         data-thumb="${image.thumb || image.url}"
                         data-title="${image.post_title || ''}">
                        <div class="picker_image_thumb">
                            <img src="${image.thumb || image.url}" alt="${image.post_title || ''}" loading="lazy">
                            <div class="picker_select_indicator">
                                <i class="fa fa-check"></i>
                            </div>
                        </div>
                        <div class="picker_image_info">
                            <span class="picker_image_title">${image.post_title || 'Sans titre'}</span>
                            <span class="picker_image_size">${image.filesize_formatted || ''}</span>
                        </div>
                    </div>
                `;

                $grid.append(html);
            });
        },

        /**
         * Rendre la pagination
         */
        renderPagination: function(totalPages, currentPage) {
            const $pagination = $('#media_picker_modal .picker_pagination');
            const self = this;

            $pagination.empty();

            if (totalPages <= 1) return;

            let html = '';

            // Bouton précédent
            html += `<button class="page_btn page_prev" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fa fa-chevron-left"></i>
            </button>`;

            // Pages
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    html += `<button class="page_btn page_current">${i}</button>`;
                } else if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
                    html += `<button class="page_btn" data-page="${i}">${i}</button>`;
                } else if (Math.abs(i - currentPage) === 2) {
                    html += '<span class="page_dots">...</span>';
                }
            }

            // Bouton suivant
            html += `<button class="page_btn page_next" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fa fa-chevron-right"></i>
            </button>`;

            $pagination.html(html);

            // Events
            $pagination.find('.page_btn[data-page]').on('click', function() {
                self.currentPage = parseInt($(this).data('page'));
                self.loadImages();
            });

            $pagination.find('.page_prev').on('click', function() {
                if (currentPage > 1) {
                    self.currentPage = currentPage - 1;
                    self.loadImages();
                }
            });

            $pagination.find('.page_next').on('click', function() {
                if (currentPage < totalPages) {
                    self.currentPage = currentPage + 1;
                    self.loadImages();
                }
            });
        },

        /**
         * Mettre à jour le compteur de sélection
         */
        updateSelectionCount: function() {
            const count = this.selectedImages.length;
            const text = count + ' sélectionnée' + (count > 1 ? 's' : '');
            $('#media_picker_modal .selection_text').text(text);

            // Activer/désactiver le bouton de confirmation
            $('#media_picker_modal .btn_confirm_selection').prop('disabled', count === 0);
        },

        /**
         * Afficher le loader
         */
        showLoader: function() {
            $('#media_picker_modal .picker_loader').show();
            $('#media_picker_modal .picker_grid, #media_picker_modal .picker_empty').hide();
        },

        /**
         * Cacher le loader
         */
        hideLoader: function() {
            $('#media_picker_modal .picker_loader').hide();
        }
    };

    // Export global
    window.MediaPicker = window.EL_MediaPicker;

})(jQuery);
