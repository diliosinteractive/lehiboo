/**
 * Gestionnaire de médias pour vendors
 * @package EventList
 */

(function($) {
    'use strict';

    if (typeof window.EL_MediaManager === 'undefined') {
        console.error('EL_MediaManager configuration not found');
        return;
    }

    const MediaManager = {

        config: window.EL_MediaManager,
        currentFolder: window.EL_MediaManager.currentFolder,
        currentPage: 1,
        perPage: 24,
        selectedImages: [],
        uploadQueue: [],
        imagesToMove: [],
        targetFolderId: null,
        loadedImages: [], // Images actuellement chargées pour re-render
        pendingUploadFile: null, // Fichier en attente d'upload dans le modal

        /**
         * Initialisation
         */
        init: function() {
            this.bindEvents();
            this.loadImages();
            this.expandActiveFolders();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Upload buttons - Ouvrir le modal d'upload
            $(document).on('click', '.btn_upload_images, .btn_upload_first', function(e) {
                e.preventDefault();
                self.showUploadModal();
            });

            // Modal Upload - Dropzone click
            $(document).on('click', '.modal_upload .upload_dropzone', function(e) {
                if (!$(e.target).is('input') && !$(e.target).closest('.btn_change_image').length) {
                    $('.modal_upload .upload_file_input').click();
                }
            });

            // Modal Upload - Change image button
            $(document).on('click', '.modal_upload .btn_change_image', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('.modal_upload .upload_file_input').click();
            });

            // Modal Upload - Dropzone drag events
            $(document).on('dragover', '.modal_upload .upload_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $(document).on('dragleave', '.modal_upload .upload_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $(document).on('drop', '.modal_upload .upload_dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    self.handleUploadModalFile(files[0]);
                }
            });

            // Modal Upload - File input change
            $(document).on('change', '.modal_upload .upload_file_input', function(e) {
                if (this.files.length > 0) {
                    self.handleUploadModalFile(this.files[0]);
                }
            });

            // Modal Upload - Form submit
            $(document).on('submit', '.upload_form', function(e) {
                e.preventDefault();
                self.submitUploadForm();
            });

            // Modal Upload - Folder selector toggle
            $(document).on('click', '.folder_select_display', function(e) {
                if ($(e.target).closest('.folder_clear_btn').length) return;
                const $dropdown = $(this).siblings('.folder_select_dropdown');
                $dropdown.toggle();
                self.populateFolderDropdown($dropdown);
            });

            // Modal Upload - Folder selector clear
            $(document).on('click', '.folder_clear_btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.setUploadFolder(0, 'Toutes les images', 'home');
            });

            // Modal Upload - Folder dropdown item click
            $(document).on('click', '.folder_select_dropdown .folder_option', function(e) {
                e.preventDefault();
                const folderId = $(this).data('folder-id');
                const folderName = $(this).find('.folder_option_name').text();
                const folderIcon = folderId > 0 ? 'folder' : 'home';
                self.setUploadFolder(folderId, folderName, folderIcon);
                $(this).closest('.folder_select_dropdown').hide();
            });

            // Close folder dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.folder_select_wrapper').length) {
                    $('.folder_select_dropdown').hide();
                }
            });

            // Close upload zone (for progress display)
            $(document).on('click', '.btn_close_upload', function() {
                self.hideUploadZone();
            });

            // Folder toggle
            $(document).on('click', '.folder_toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $item = $(this).closest('.folder_item');
                $item.toggleClass('expanded');
                $item.find('> .folder_children').slideToggle(200);
                $(this).find('i').toggleClass('fa-chevron-right fa-chevron-down');
            });

            // Folder menu
            $(document).on('click', '.btn_folder_menu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $menu = $(this).siblings('.folder_menu');
                $('.folder_menu').not($menu).hide();
                $menu.toggle();
            });

            // Close folder menu on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.folder_actions').length) {
                    $('.folder_menu').hide();
                }
            });

            // Create folder
            $(document).on('click', '.btn_create_folder, .btn_create_first_folder', function(e) {
                e.preventDefault();
                self.showFolderModal();
            });

            // Add subfolder
            $(document).on('click', '.btn_add_subfolder', function(e) {
                e.preventDefault();
                const folderId = $(this).closest('.folder_item').data('folder-id');
                self.showFolderModal(folderId);
            });

            // Edit folder
            $(document).on('click', '.btn_edit_folder', function(e) {
                e.preventDefault();
                const folderId = $(this).closest('.folder_item').data('folder-id');
                self.editFolder(folderId);
            });

            // Delete folder
            $(document).on('click', '.btn_delete_folder', function(e) {
                e.preventDefault();
                const folderId = $(this).closest('.folder_item').data('folder-id');
                self.deleteFolder(folderId);
            });

            // Folder form submit
            $(document).on('submit', '.folder_form', function(e) {
                e.preventDefault();
                self.saveFolder($(this));
            });

            // Color presets
            $(document).on('click', '.color_preset', function(e) {
                e.preventDefault();
                const color = $(this).data('color');
                $('input[name="color"]').val(color);
            });

            // View mode
            $(document).on('click', '.btn_view_grid, .btn_view_list', function() {
                const view = $(this).data('view');
                $('.btn_view_grid, .btn_view_list').removeClass('active');
                $(this).addClass('active');
                $('.media_grid').attr('data-view', view);
                // Re-render images avec le nouveau template
                if (self.loadedImages && self.loadedImages.length > 0) {
                    self.renderImages(self.loadedImages);
                }
            });

            // Search
            let searchTimeout;
            $('.media_search').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val();
                searchTimeout = setTimeout(function() {
                    if (query.length >= 2) {
                        self.searchImages(query);
                    } else if (query.length === 0) {
                        self.loadImages();
                    }
                }, 500);
            });

            // Image selection
            $(document).on('change', '.item_select', function() {
                const id = parseInt($(this).val());
                if ($(this).is(':checked')) {
                    self.selectedImages.push(id);
                } else {
                    self.selectedImages = self.selectedImages.filter(item => item !== id);
                }
                self.updateBulkActions();
            });

            // Bulk actions - Bouton Déplacer
            $(document).on('click', '.btn_bulk_move', function() {
                if (self.selectedImages.length === 0) return;
                self.showMoveModal(self.selectedImages);
            });

            // Bulk actions - Bouton Supprimer
            $(document).on('click', '.btn_bulk_delete', function() {
                if (self.selectedImages.length === 0) return;
                self.showDeleteConfirmModal();
            });

            // Confirmation de suppression
            $(document).on('click', '.btn_delete_confirm', function() {
                self.bulkDelete();
            });

            // Image actions
            $(document).on('click', '.btn_view', function() {
                const id = $(this).closest('[data-id]').data('id');
                self.viewImage(id);
            });

            $(document).on('click', '.btn_edit', function() {
                const $item = $(this).closest('[data-id]');
                const id = $item.data('id');
                // Supporte les deux vues: grille (.media_item_thumb) et liste (.item_list_thumb)
                const imageUrl = $item.find('.media_item_thumb img, .item_list_thumb img').attr('src');
                self.editImage(id, imageUrl);
            });

            $(document).on('click', '.btn_delete', function() {
                const id = $(this).closest('[data-id]').data('id');
                self.deleteImage(id);
            });

            $(document).on('click', '.btn_move', function() {
                const id = $(this).closest('[data-id]').data('id');
                self.showMoveModal([id]);
            });

            // Move confirm
            $(document).on('click', '.btn_move_confirm', function() {
                self.moveImagesToFolder();
            });

            // Modal close
            $(document).on('click', '.modal_close, .modal_overlay, .btn_cancel', function(e) {
                if ($(e.target).is('.modal_overlay') || $(e.target).is('.modal_close') || $(e.target).is('.btn_cancel')) {
                    $(this).closest('.media_modal').fadeOut(200);
                }
            });

            // Escape key to close modals
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.media_modal:visible').fadeOut(200);
                }
            });
        },

        /**
         * Expand active folders in tree
         */
        expandActiveFolders: function() {
            $('.folder_item.active').parents('.folder_item').each(function() {
                $(this).addClass('expanded');
                $(this).find('> .folder_children').show();
                $(this).find('> .folder_header .folder_toggle i')
                    .removeClass('fa-chevron-right').addClass('fa-chevron-down');
            });
        },

        /**
         * Show upload modal
         */
        showUploadModal: function() {
            const $modal = $('.modal_upload');

            // Reset form
            $modal.find('.upload_form')[0].reset();
            $modal.find('.dropzone_preview').hide();
            $modal.find('.dropzone_placeholder').show();
            $modal.find('.preview_image').attr('src', '');
            $modal.find('.btn_submit_upload').prop('disabled', true);
            $modal.find('.upload_progress_bar').hide();
            $modal.find('.progress_fill').css('width', '0%');

            // Reset pending file
            this.pendingUploadFile = null;

            // Set current folder
            const folderId = this.currentFolder || 0;
            const $folderDisplay = $modal.find('.folder_select_display');
            const currentFolderName = folderId > 0 ?
                ($('.folder_item[data-folder-id="' + folderId + '"] .folder_name').first().text() || 'Dossier') :
                'Toutes les images';

            this.setUploadFolder(folderId, currentFolderName, folderId > 0 ? 'folder' : 'home');

            $modal.fadeIn(200);
        },

        /**
         * Hide upload modal
         */
        hideUploadModal: function() {
            $('.modal_upload').fadeOut(200);
            this.pendingUploadFile = null;
        },

        /**
         * Handle file selection in upload modal
         */
        handleUploadModalFile: async function(file) {
            const self = this;
            const $modal = $('.modal_upload');

            // Validate file type
            if (!file.type.match('image.*')) {
                this.showError('Ce fichier n\'est pas une image valide');
                return;
            }

            // Show progress bar for compression
            $modal.find('.upload_progress_bar').show();
            $modal.find('.progress_text').text('Compression en cours...');
            $modal.find('.progress_fill').css('width', '30%');

            let processedFile = file;

            // Compress image if compression library is available
            if (window.EL_MediaCompression) {
                try {
                    console.log('[Upload] Compression de l\'image...');
                    processedFile = await window.EL_MediaCompression.compressImage(file);
                    console.log('[Upload] Compression terminée');
                } catch (error) {
                    console.error('[Upload] Compression error:', error);
                    // Continue with original file
                }
            }

            $modal.find('.progress_fill').css('width', '100%');
            $modal.find('.progress_text').text('Prêt');

            // Store file for upload
            this.pendingUploadFile = processedFile;

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $modal.find('.preview_image').attr('src', e.target.result);
                $modal.find('.dropzone_placeholder').hide();
                $modal.find('.dropzone_preview').show();

                // Enable submit button
                $modal.find('.btn_submit_upload').prop('disabled', false);

                // Set default title from filename (without extension)
                const fileName = file.name.replace(/\.[^/.]+$/, '');
                if (!$modal.find('input[name="title"]').val()) {
                    $modal.find('input[name="title"]').attr('placeholder', fileName);
                }

                // Hide progress bar after a short delay
                setTimeout(function() {
                    $modal.find('.upload_progress_bar').hide();
                }, 500);
            };
            reader.readAsDataURL(processedFile);
        },

        /**
         * Populate folder dropdown
         */
        populateFolderDropdown: function($dropdown) {
            if ($dropdown.children().length > 0) return; // Already populated

            let html = '<div class="folder_option" data-folder-id="0">';
            html += '<span class="folder_option_icon"><i class="fa fa-home"></i></span>';
            html += '<span class="folder_option_name">Toutes les images</span>';
            html += '</div>';

            // Clone folders from sidebar
            $('.folders_tree .folder_item').each(function() {
                const folderId = $(this).data('folder-id');
                if (folderId === -1) return; // Skip "all images"

                const folderName = $(this).find('> .folder_header .folder_name').first().text();
                const folderColor = $(this).find('> .folder_header .folder_icon').css('color') || '#FF6B35';
                const level = $(this).parents('.folder_item').length;
                const indent = level * 20;

                html += '<div class="folder_option" data-folder-id="' + folderId + '" style="padding-left: ' + (12 + indent) + 'px;">';
                html += '<span class="folder_option_icon" style="color: ' + folderColor + ';"><i class="fa fa-folder"></i></span>';
                html += '<span class="folder_option_name">' + folderName + '</span>';
                html += '</div>';
            });

            $dropdown.html(html);
        },

        /**
         * Set upload folder
         */
        setUploadFolder: function(folderId, folderName, iconType) {
            const $modal = $('.modal_upload');
            $modal.find('input[name="folder_id"]').val(folderId);
            $modal.find('.folder_select_display .folder_name').text(folderName);
            $modal.find('.folder_select_display .folder_icon i').attr('class', 'fa fa-' + iconType);
            $modal.find('.folder_select_display').attr('data-folder-id', folderId);
        },

        /**
         * Submit upload form
         */
        submitUploadForm: async function() {
            const self = this;
            const $modal = $('.modal_upload');
            const $form = $modal.find('.upload_form');
            const $submitBtn = $form.find('.btn_submit_upload');

            if (!this.pendingUploadFile) {
                this.showError('Veuillez sélectionner une image');
                return;
            }

            // Disable submit button
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Upload...');

            // Show progress
            $modal.find('.upload_progress_bar').show();
            $modal.find('.progress_text').text('Upload en cours...');
            $modal.find('.progress_fill').css('width', '0%');

            // Get form data
            const title = $form.find('input[name="title"]').val() || '';
            const folderId = $form.find('input[name="folder_id"]').val() || 0;
            const altText = $form.find('input[name="alt_text"]').val() || '';

            // Create FormData
            const formData = new FormData();
            formData.append('files[]', this.pendingUploadFile);
            formData.append('action', 'el_vendor_upload_media');
            formData.append('nonce', this.config.nonce);
            formData.append('folder_id', folderId);
            formData.append('title', title);
            formData.append('alt_text', altText);

            const xhr = new XMLHttpRequest();

            // Progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    $modal.find('.progress_fill').css('width', percent + '%');
                    $modal.find('.progress_text').text('Upload: ' + percent + '%');
                }
            });

            // Complete
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            self.showSuccess(response.data.message);
                            self.hideUploadModal();
                            self.loadImages();
                        } else {
                            self.showError(response.data && response.data.message ? response.data.message : 'Erreur lors de l\'upload');
                            console.error('Upload error response:', response);
                        }
                    } catch (e) {
                        console.error('Upload response parse error:', e, xhr.responseText);
                        self.showError('Erreur serveur lors de l\'upload');
                    }
                } else {
                    console.error('Upload HTTP error:', xhr.status, xhr.responseText);
                    self.showError('Erreur lors de l\'upload (HTTP ' + xhr.status + ')');
                }

                // Reset button
                $submitBtn.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Ajouter l\'image');
                $modal.find('.upload_progress_bar').hide();
            });

            // Error
            xhr.addEventListener('error', function() {
                self.showError('Erreur réseau lors de l\'upload');
                $submitBtn.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Ajouter l\'image');
                $modal.find('.upload_progress_bar').hide();
            });

            xhr.open('POST', this.config.ajaxUrl);
            xhr.send(formData);
        },

        /**
         * Show upload zone (for progress display)
         */
        showUploadZone: function() {
            $('.upload_zone').slideDown(300);
            $('.media_grid_container').slideUp(300);
        },

        /**
         * Hide upload zone
         */
        hideUploadZone: function() {
            $('.upload_zone').slideUp(300);
            $('.media_grid_container').slideDown(300);
            $('.upload_preview').find('.preview_list').empty();
        },

        /**
         * Load images
         */
        loadImages: function(page) {
            const self = this;
            page = page || 1;
            this.currentPage = page;

            this.showLoader();

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_get_images',
                    nonce: this.config.nonce,
                    folder_id: this.currentFolder,
                    page: page,
                    per_page: this.perPage
                },
                success: function(response) {
                    self.hideLoader();
                    if (response.success) {
                        // Stocker les images pour pouvoir re-rendre lors du changement de vue
                        self.loadedImages = response.data.images || [];
                        self.renderImages(self.loadedImages);
                        self.updateImageCount(response.data.total);
                        self.renderPagination(response.data.pages, page);
                    } else {
                        self.showError(response.data.message);
                    }
                },
                error: function() {
                    self.hideLoader();
                    self.showError('Erreur lors du chargement des images');
                }
            });
        },

        /**
         * Render images
         */
        renderImages: function(images) {
            const $grid = $('.media_grid');
            const $empty = $('.media_empty');
            const template = $('#tmpl-media-item').html();
            const isListView = $grid.attr('data-view') === 'list';
            const listTemplate = $('#tmpl-media-item-list').html();

            $grid.empty();

            if (!images || images.length === 0) {
                $grid.hide();
                $empty.show();
                return;
            }

            $empty.hide();
            $grid.show();

            const self = this;
            images.forEach(function(image) {
                const tmpl = isListView ? listTemplate : template;
                // Ajouter un cache buster à l'URL de l'image pour forcer le rechargement après édition
                const thumbUrl = image.thumb || image.url;
                // Utiliser le timestamp d'édition forcé s'il est plus récent
                const serverCacheBuster = image.cache_buster || 0;
                const cacheBuster = (self.lastEditTimestamp && self.lastEditTimestamp > serverCacheBuster * 1000)
                    ? self.lastEditTimestamp
                    : (serverCacheBuster || Date.now());
                const thumbWithCache = thumbUrl + (thumbUrl.indexOf('?') > -1 ? '&' : '?') + 'v=' + cacheBuster;

                let html = tmpl
                    .replace(/\{\{attachment_id\}\}/g, image.attachment_id)
                    .replace(/\{\{folder_id\}\}/g, image.folder_id)
                    .replace(/\{\{thumb\}\}/g, thumbWithCache)
                    .replace(/\{\{title\}\}/g, image.post_title || 'Sans titre')
                    .replace(/\{\{alt_text\}\}/g, image.alt_text || image.post_title || '')
                    .replace(/\{\{size\}\}/g, image.filesize_formatted || '')
                    .replace(/\{\{format\}\}/g, image.format || '')
                    .replace(/\{\{created_at\}\}/g, image.created_at_formatted || '')
                    .replace(/\{\{updated_at\}\}/g, image.updated_at_formatted || '')
                    .replace(/\{\{date\}\}/g, image.created_at_formatted || '');

                $grid.append(html);
            });
        },

        /**
         * Update image count
         */
        updateImageCount: function(count) {
            $('.images_count .count_number').text(count);
            $('#root_count').text('(' + count + ')');
        },

        /**
         * Render pagination
         */
        renderPagination: function(totalPages, currentPage) {
            const $pagination = $('.media_pagination');
            $pagination.empty();

            if (totalPages <= 1) return;

            let html = '<button class="page_btn page_prev" ' + (currentPage === 1 ? 'disabled' : '') + '>«</button>';

            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    html += '<button class="page_btn page_current">' + i + '</button>';
                } else if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 2) {
                    html += '<button class="page_btn" data-page="' + i + '">' + i + '</button>';
                } else if (Math.abs(i - currentPage) === 3) {
                    html += '<span class="page_dots">...</span>';
                }
            }

            html += '<button class="page_btn page_next" ' + (currentPage === totalPages ? 'disabled' : '') + '>»</button>';

            $pagination.html(html);

            const self = this;
            $pagination.find('.page_btn[data-page]').on('click', function() {
                self.loadImages(parseInt($(this).data('page')));
            });

            $pagination.find('.page_prev').on('click', function() {
                if (currentPage > 1) self.loadImages(currentPage - 1);
            });

            $pagination.find('.page_next').on('click', function() {
                if (currentPage < totalPages) self.loadImages(currentPage + 1);
            });
        },

        /**
         * Search images
         */
        searchImages: function(query) {
            const self = this;
            this.showLoader();

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_search_images',
                    nonce: this.config.nonce,
                    search: query,
                    page: 1,
                    per_page: this.perPage
                },
                success: function(response) {
                    self.hideLoader();
                    if (response.success) {
                        // Stocker les images pour pouvoir re-rendre lors du changement de vue
                        self.loadedImages = response.data.images || [];
                        self.renderImages(self.loadedImages);
                        self.updateImageCount(response.data.total);
                        self.renderPagination(response.data.pages, 1);
                    }
                }
            });
        },

        /**
         * Show folder modal
         */
        showFolderModal: function(parentId) {
            const $modal = $('.modal_folder');
            $modal.find('input[name="folder_id"]').val('');
            $modal.find('input[name="parent_id"]').val(parentId || this.currentFolder);
            $modal.find('input[name="name"]').val('');
            $modal.find('textarea[name="description"]').val('');
            $modal.find('input[name="color"]').val('#FF6B35');
            $modal.find('.modal_title').text('Nouveau dossier');
            $modal.find('button[type="submit"]').text('Créer');
            $modal.fadeIn(200);
        },

        /**
         * Edit folder
         */
        editFolder: function(folderId) {
            // TODO: Load folder data and populate form
            this.showFolderModal();
        },

        /**
         * Save folder
         */
        saveFolder: function($form) {
            const self = this;
            const folderId = $form.find('input[name="folder_id"]').val();
            const action = folderId ? 'el_vendor_update_folder' : 'el_vendor_create_folder';
            const data = $form.serializeArray();

            data.push({ name: 'action', value: action });
            data.push({ name: 'nonce', value: this.config.nonce });

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: $.param(data),
                success: function(response) {
                    if (response.success) {
                        self.showSuccess(response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        self.showError(response.data.message);
                    }
                },
                error: function() {
                    self.showError('Erreur lors de la sauvegarde du dossier');
                }
            });
        },

        /**
         * Delete folder
         */
        deleteFolder: function(folderId) {
            if (!confirm(this.config.i18n.confirmDeleteFolder)) return;

            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_delete_folder',
                    nonce: this.config.nonce,
                    folder_id: folderId,
                    move_to_parent: true
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess(response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        self.showError(response.data.message);
                    }
                }
            });
        },

        /**
         * Delete image
         */
        deleteImage: function(attachmentId) {
            if (!confirm(this.config.i18n.confirmDelete)) return;

            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_delete_image',
                    nonce: this.config.nonce,
                    attachment_id: attachmentId
                },
                success: function(response) {
                    if (response.success) {
                        $('[data-id="' + attachmentId + '"]').fadeOut(300, function() {
                            $(this).remove();
                            self.updateImageCount(parseInt($('.count_number').text()) - 1);
                        });
                        self.showSuccess(response.data.message);
                    } else {
                        self.showError(response.data.message);
                    }
                }
            });
        },

        /**
         * View image
         */
        viewImage: function(attachmentId) {
            const $item = $('[data-id="' + attachmentId + '"]');
            const $img = $item.find('img');
            const title = $item.find('.item_title').text();

            $('.modal_viewer .viewer_image').attr('src', $img.attr('src').replace('150x150', 'full'));
            $('.modal_viewer .viewer_title').text(title);
            $('.modal_viewer').fadeIn(200);
        },

        /**
         * Edit image
         */
        editImage: function(attachmentId, imageUrl) {
            const self = this;

            if (!window.EL_MediaEditor) {
                this.showError('L\'éditeur d\'image n\'est pas disponible');
                return;
            }

            // Utiliser l'URL complète plutôt que le thumbnail
            const fullImageUrl = imageUrl.replace(/(-\d+x\d+)(\.[^.]+)$/, '$2');

            // Trouver les données complètes de l'image
            let imageData = null;
            if (this.loadedImages && this.loadedImages.length > 0) {
                imageData = this.loadedImages.find(function(img) {
                    return parseInt(img.attachment_id) === parseInt(attachmentId);
                });
            }

            // Préparer les données pour l'éditeur
            const editorData = imageData ? {
                title: imageData.post_title || '',
                alt_text: imageData.alt_text || '',
                folder_id: imageData.folder_id || 0,
                filesize_formatted: imageData.filesize_formatted || '-',
                format: imageData.format || '-',
                created_at_formatted: imageData.created_at_formatted || '-',
                updated_at_formatted: imageData.updated_at_formatted || '-'
            } : null;

            // Ouvrir l'éditeur depuis l'URL avec les données
            window.EL_MediaEditor.openEditorFromUrl(fullImageUrl, attachmentId, function(response) {
                // Callback après sauvegarde réussie
                self.showSuccess('Image mise à jour avec succès');
                // Forcer un nouveau cache buster pour cette session
                self.lastEditTimestamp = Date.now();
                // Rafraîchir les compteurs si le dossier a changé
                self.refreshFolderCounts();
                // Recharger les images pour afficher la nouvelle version
                setTimeout(function() {
                    self.loadImages();
                }, 500);
            }, editorData);
        },

        /**
         * Show move modal
         */
        showMoveModal: function(imageIds) {
            const self = this;
            const $modal = $('.modal_move');
            const $treeSelect = $modal.find('.folder_tree_select');

            // Clone l'arborescence des dossiers depuis la sidebar
            const $foldersTree = $('.folders_tree').clone();

            // Transformer les liens en boutons sélectionnables
            $foldersTree.find('.folder_link').each(function() {
                const $link = $(this);
                const folderId = $link.closest('.folder_item').data('folder-id');
                const folderName = $link.find('.folder_name').text();
                const folderIcon = $link.find('.folder_icon').html();

                // Remplacer le lien par un div cliquable
                const $selectBtn = $('<div class="folder_select_item" data-folder-id="' + folderId + '"></div>');
                $selectBtn.html($link.html());

                $link.replaceWith($selectBtn);
            });

            // Supprimer les actions de dossiers (edit, delete, etc.)
            $foldersTree.find('.folder_actions').remove();

            // Rendre le contenu HTML
            $treeSelect.html($foldersTree.html());

            // Gérer la sélection
            $treeSelect.off('click', '.folder_select_item').on('click', '.folder_select_item', function(e) {
                e.preventDefault();
                $treeSelect.find('.folder_select_item').removeClass('selected');
                $(this).addClass('selected');
                self.targetFolderId = $(this).closest('.folder_item').data('folder-id');
            });

            // Stocker les IDs des images à déplacer
            this.imagesToMove = imageIds;

            // Afficher le modal
            $modal.fadeIn(200);
        },

        /**
         * Move images to folder
         */
        moveImagesToFolder: function() {
            const self = this;

            if (!this.targetFolderId && this.targetFolderId !== 0) {
                this.showError('Veuillez sélectionner un dossier de destination');
                return;
            }

            if (!this.imagesToMove || this.imagesToMove.length === 0) {
                this.showError('Aucune image sélectionnée');
                return;
            }

            const $btn = $('.btn_move_confirm');
            $btn.prop('disabled', true).text('Déplacement...');

            $.ajax({
                url: window.EL_MediaManager.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_move_images',
                    nonce: window.EL_MediaManager.nonce,
                    attachment_ids: this.imagesToMove,
                    folder_id: this.targetFolderId
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Images déplacées avec succès');
                        $('.modal_move').fadeOut(200);

                        // Naviguer vers le dossier cible et recharger
                        const targetFolder = self.targetFolderId;

                        // Rafraîchir les compteurs des dossiers depuis le serveur
                        self.refreshFolderCounts();

                        setTimeout(function() {
                            // Changer le dossier courant vers le dossier cible
                            self.currentFolder = targetFolder;

                            // Mettre à jour l'UI de la sidebar
                            $('.folder_item').removeClass('active');
                            if (targetFolder === 0 || targetFolder === '0') {
                                $('.folder_item[data-folder-id="0"]').addClass('active');
                            } else {
                                $('.folder_item[data-folder-id="' + targetFolder + '"]').addClass('active');
                            }

                            // Recharger les images du nouveau dossier
                            self.loadImages();
                            self.selectedImages = [];
                            self.imagesToMove = [];
                            self.targetFolderId = null;
                            self.updateBulkActions();
                        }, 500);
                    } else {
                        self.showError(response.data.message || 'Erreur lors du déplacement');
                    }
                },
                error: function() {
                    self.showError('Erreur lors du déplacement des images');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Déplacer');
                }
            });
        },

        /**
         * Update bulk actions visibility
         */
        updateBulkActions: function() {
            if (this.selectedImages.length > 0) {
                $('.bulk_actions').show();
            } else {
                $('.bulk_actions').hide();
            }
        },

        /**
         * Refresh folder counts from server (plus fiable que le calcul côté client)
         */
        refreshFolderCounts: function() {
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'el_vendor_get_folder_counts',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success && response.data.counts) {
                        // Mettre à jour chaque compteur de dossier
                        $.each(response.data.counts, function(folderId, count) {
                            const $countSpan = $('.folder_item[data-folder-id="' + folderId + '"] .folder_count');
                            if ($countSpan.length) {
                                $countSpan.text('(' + count + ')');
                            }
                        });
                        // Mettre à jour le total "Toutes les images"
                        if (response.data.total !== undefined) {
                            $('#all_count').text('(' + response.data.total + ')');
                        }
                    }
                }
            });
        },

        /**
         * Show delete confirmation modal
         */
        showDeleteConfirmModal: function() {
            $('.modal_delete_confirm').fadeIn(200);
        },

        /**
         * Hide delete confirmation modal
         */
        hideDeleteConfirmModal: function() {
            $('.modal_delete_confirm').fadeOut(200);
        },

        /**
         * Bulk delete
         */
        bulkDelete: function() {
            const self = this;
            let completed = 0;
            const totalToDelete = this.selectedImages.length;

            // Fermer le modal de confirmation
            this.hideDeleteConfirmModal();

            this.selectedImages.forEach(function(id) {
                $.ajax({
                    url: self.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_vendor_delete_image',
                        nonce: self.config.nonce,
                        attachment_id: id
                    },
                    success: function() {
                        completed++;
                        if (completed === totalToDelete) {
                            self.showSuccess(totalToDelete + ' image(s) supprimée(s)');
                            // Rafraîchir les compteurs des dossiers depuis le serveur
                            self.refreshFolderCounts();
                            self.selectedImages = [];
                            self.updateBulkActions();
                            self.loadImages();
                        }
                    }
                });
            });
        },

        /**
         * Show loader
         */
        showLoader: function() {
            $('.media_loader').show();
            $('.media_grid, .media_empty').hide();
        },

        /**
         * Hide loader
         */
        hideLoader: function() {
            $('.media_loader').hide();
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            if (window.ToastNotification) {
                window.ToastNotification.success(message);
            } else {
                alert(message);
            }
        },

        /**
         * Show error message
         */
        showError: function(message) {
            if (window.ToastNotification) {
                window.ToastNotification.error(message);
            } else {
                alert(message);
            }
        },

        /**
         * Format file size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    };

    // Init on document ready
    $(document).ready(function() {
        if ($('.vendor_media_manager').length) {
            MediaManager.init();
        }
    });

})(jQuery);
