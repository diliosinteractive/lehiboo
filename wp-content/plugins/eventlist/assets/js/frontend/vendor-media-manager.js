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

            // Upload buttons
            $(document).on('click', '.btn_upload_images, .btn_upload_first', function(e) {
                e.preventDefault();
                self.showUploadZone();
            });

            // Dropzone
            const $dropzone = $('.upload_dropzone');

            $dropzone.on('click', function(e) {
                if (!$(e.target).is('input')) {
                    $('.upload_input').click();
                }
            });

            $dropzone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $dropzone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $dropzone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                self.handleFiles(files);
            });

            // File input change
            $('.upload_input').on('change', function(e) {
                self.handleFiles(this.files);
            });

            // Close upload zone
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

            // Bulk actions
            $(document).on('click', '.btn_bulk_apply', function() {
                const action = $('.bulk_action_select').val();
                if (!action || self.selectedImages.length === 0) return;

                if (action === 'move') {
                    self.showMoveModal(self.selectedImages);
                } else if (action === 'delete') {
                    self.bulkDelete();
                }
            });

            // Image actions
            $(document).on('click', '.btn_view', function() {
                const id = $(this).closest('[data-id]').data('id');
                self.viewImage(id);
            });

            $(document).on('click', '.btn_edit', function() {
                const $item = $(this).closest('[data-id]');
                const id = $item.data('id');
                const imageUrl = $item.find('.media_item_thumb img').attr('src');
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
         * Show upload zone
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
            $('.upload_input').val('');
            $('.upload_preview').hide().find('.preview_list').empty();
        },

        /**
         * Handle files selection
         */
        handleFiles: async function(files) {
            if (files.length === 0) return;

            const self = this;
            const $previewList = $('.preview_list');

            $('.upload_preview').show();
            $previewList.empty();

            // Convert FileList to Array
            let fileArray = Array.from(files);

            // OPTIMISATION: Compresser les images avant upload
            if (window.EL_MediaCompression) {
                try {
                    console.log('[Upload] Compression de', fileArray.length, 'image(s)...');

                    // Afficher le statut de compression
                    fileArray.forEach(function(file, index) {
                        const template = $('#tmpl-upload-preview-item').html();
                        const html = template
                            .replace(/\{\{index\}\}/g, index)
                            .replace(/\{\{preview\}\}/g, '')
                            .replace(/\{\{name\}\}/g, file.name)
                            .replace(/\{\{size\}\}/g, self.formatFileSize(file.size));

                        $previewList.append(html);
                        $('[data-file-index="' + index + '"]').find('.preview_status').text('Compression...');
                    });

                    // Compresser toutes les images
                    const compressedFiles = await window.EL_MediaCompression.compressMultiple(fileArray, function(progress) {
                        const index = progress.current - 1;
                        $('[data-file-index="' + index + '"]').find('.preview_status').text(
                            'Compression: ' + progress.percent + '%'
                        );
                    });

                    fileArray = compressedFiles;

                    // Mettre à jour les previews avec les images compressées
                    fileArray.forEach(function(file, index) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('[data-file-index="' + index + '"]').find('img').attr('src', e.target.result);
                            $('[data-file-index="' + index + '"]').find('.preview_size').text(self.formatFileSize(file.size));
                            $('[data-file-index="' + index + '"]').find('.preview_status').text('Prêt');
                        };
                        reader.readAsDataURL(file);
                    });

                } catch (error) {
                    console.error('[Upload] Compression error:', error);
                    // Continuer avec les fichiers originaux en cas d'erreur
                }
            } else {
                // Pas de compression disponible, générer les previews normalement
                fileArray.forEach(function(file, index) {
                    if (!file.type.match('image.*')) {
                        self.showError(file.name + ' n\'est pas une image valide');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const template = $('#tmpl-upload-preview-item').html();
                        const html = template
                            .replace(/\{\{index\}\}/g, index)
                            .replace(/\{\{preview\}\}/g, e.target.result)
                            .replace(/\{\{name\}\}/g, file.name)
                            .replace(/\{\{size\}\}/g, self.formatFileSize(file.size));

                        $previewList.append(html);
                    };
                    reader.readAsDataURL(file);
                });
            }

            this.uploadQueue = fileArray;

            // Start upload
            setTimeout(function() {
                self.uploadFiles();
            }, 1000);
        },

        /**
         * Upload files
         */
        uploadFiles: function() {
            const self = this;
            const formData = new FormData();

            this.uploadQueue.forEach(function(file, index) {
                formData.append('files[' + index + ']', file);
            });

            formData.append('action', 'el_vendor_upload_media');
            formData.append('nonce', this.config.nonce);
            formData.append('folder_id', this.currentFolder);

            const xhr = new XMLHttpRequest();

            // Progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    $('.preview_item').each(function() {
                        $(this).find('.progress_fill').css('width', percent + '%');
                        $(this).find('.progress_percent').text(percent + '%');
                    });
                }
            });

            // Complete
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        self.showSuccess(response.data.message);
                        setTimeout(function() {
                            self.hideUploadZone();
                            self.loadImages();
                        }, 1000);
                    } else {
                        self.showError(response.data.message);
                    }
                } else {
                    self.showError('Erreur lors de l\'upload');
                }
            });

            // Error
            xhr.addEventListener('error', function() {
                self.showError('Erreur réseau lors de l\'upload');
            });

            xhr.open('POST', this.config.ajaxUrl);
            xhr.send(formData);
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
                        self.renderImages(response.data.images);
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

            images.forEach(function(image) {
                const tmpl = isListView ? listTemplate : template;
                let html = tmpl
                    .replace(/\{\{attachment_id\}\}/g, image.attachment_id)
                    .replace(/\{\{folder_id\}\}/g, image.folder_id)
                    .replace(/\{\{thumb\}\}/g, image.thumb || image.url)
                    .replace(/\{\{title\}\}/g, image.post_title || 'Sans titre')
                    .replace(/\{\{size\}\}/g, '')
                    .replace(/\{\{date\}\}/g, '');

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
                        self.renderImages(response.data.images);
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
                alert('L\'éditeur d\'image n\'est pas disponible');
                return;
            }

            // Utiliser l'URL complète plutôt que le thumbnail
            const fullImageUrl = imageUrl.replace(/(-\d+x\d+)(\.[^.]+)$/, '$2');

            // Ouvrir l'éditeur depuis l'URL
            window.EL_MediaEditor.openEditorFromUrl(fullImageUrl, attachmentId, function(response) {
                // Callback après sauvegarde réussie
                self.showSuccess('Image mise à jour avec succès');
                // Recharger les images pour afficher la nouvelle version
                setTimeout(function() {
                    self.loadImages();
                }, 500);
            });
        },

        /**
         * Show move modal
         */
        showMoveModal: function(imageIds) {
            const self = this;
            const $modal = $('.modal_move');
            const $treeSelect = $modal.find('.folder_tree_select');

            console.log('showMoveModal called with imageIds:', imageIds);

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

            console.log('moveImagesToFolder called', {
                imagesToMove: this.imagesToMove,
                targetFolderId: this.targetFolderId
            });

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
                    image_ids: this.imagesToMove,
                    folder_id: this.targetFolderId
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Images déplacées avec succès');
                        $('.modal_move').fadeOut(200);

                        // Recharger les images
                        setTimeout(function() {
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
         * Bulk delete
         */
        bulkDelete: function() {
            if (!confirm('Supprimer ' + this.selectedImages.length + ' images ?')) return;

            const self = this;
            let completed = 0;

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
                        if (completed === self.selectedImages.length) {
                            self.selectedImages = [];
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
            // TODO: Implement toast notification
            alert(message);
        },

        /**
         * Show error message
         */
        showError: function(message) {
            // TODO: Implement toast notification
            alert(message);
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
