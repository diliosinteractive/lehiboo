/**
 * EventList Vendor Gallery Management
 * Affichage et gestion de la médiathèque WordPress du partenaire
 */

(function($) {
    'use strict';

    const VendorGallery = {

        init: function() {
            this.addGalleryImages();
            this.deleteImage();
            this.viewImage();
            this.editImage();
            this.closeModal();
        },

        /**
         * Ajouter des images via le media uploader WordPress
         * Les images sont automatiquement ajoutées à la médiathèque
         */
        addGalleryImages: function() {
            let frame;

            $(document).on('click', '.add_gallery_images', function(e) {
                e.preventDefault();

                const button = $(this);

                // Si le media frame existe déjà, le réouvrir
                if (frame) {
                    frame.open();
                    return;
                }

                // Créer un nouveau media frame
                frame = wp.media({
                    title: button.data('uploader-title'),
                    button: {
                        text: button.data('uploader-button-text')
                    },
                    multiple: true,  // Permettre la sélection multiple
                    library: {
                        type: 'image'  // Seulement les images
                    },
                    state: 'upload'  // Ouvrir directement l'onglet téléverser
                });

                // Quand des images sont sélectionnées/uploadées
                frame.on('select', function() {
                    // Les images sont automatiquement ajoutées à la médiathèque WordPress
                    // On recharge la page pour les afficher
                    location.reload();
                });

                // Après fermeture du uploader
                frame.on('close', function() {
                    const selection = frame.state().get('selection');
                    if (selection && selection.length > 0) {
                        location.reload();
                    }
                });

                frame.open();
            });
        },

        /**
         * Supprimer définitivement une image de WordPress
         */
        deleteImage: function() {
            $(document).on('click', '.delete_image_btn', function(e) {
                e.preventDefault();

                if (!confirm('⚠️ Attention : Cette action est irréversible.\n\nVoulez-vous vraiment supprimer définitivement cette image de WordPress ?\n\nElle sera supprimée de tous vos événements et pages qui l\'utilisent.')) {
                    return;
                }

                const button = $(this);
                const imageId = button.data('image-id');
                const item = button.closest('.galerie_item');

                $.ajax({
                    url: el_general.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'el_delete_gallery_image',
                        nonce: el_general.ajax_nonce,
                        image_id: imageId
                    },
                    beforeSend: function() {
                        item.css('opacity', '0.5');
                        button.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            // Retirer l'élément avec animation
                            item.fadeOut(400, function() {
                                $(this).remove();

                                // Si plus d'images sur la page, recharger
                                if ($('.galerie_item').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            alert(response.data.message || 'Erreur lors de la suppression');
                            item.css('opacity', '1');
                            button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Erreur de connexion au serveur');
                        item.css('opacity', '1');
                        button.prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Visualiser une image en plein écran
         */
        viewImage: function() {
            $(document).on('click', '.view_image_btn', function(e) {
                e.preventDefault();

                const imageUrl = $(this).data('image-url');
                $('#gallery_modal_image').attr('src', imageUrl);
                $('#gallery_image_modal').fadeIn(300);
                $('body').css('overflow', 'hidden');
            });
        },

        /**
         * Modifier les métadonnées d'une image via le media frame WordPress
         */
        editImage: function() {
            $(document).on('click', '.edit_image_btn', function(e) {
                e.preventDefault();

                const imageId = $(this).data('image-id');

                // Créer un media frame pour éditer l'image
                const frame = wp.media({
                    title: 'Modifier les métadonnées',
                    multiple: false,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: 'Mettre à jour'
                    }
                });

                // Pré-sélectionner l'image
                frame.on('open', function() {
                    const selection = frame.state().get('selection');
                    const attachment = wp.media.attachment(imageId);
                    attachment.fetch();
                    selection.add(attachment);
                });

                // Après mise à jour
                frame.on('select', function() {
                    // Recharger la page pour afficher les modifications
                    location.reload();
                });

                frame.open();
            });
        },

        /**
         * Fermer la modal
         */
        closeModal: function() {
            $(document).on('click', '.gallery_modal_close, .gallery_modal_overlay', function() {
                $('#gallery_image_modal').fadeOut(300);
                $('body').css('overflow', 'auto');
            });

            // Fermer avec la touche Escape
            $(document).on('keyup', function(e) {
                if (e.key === 'Escape' && $('#gallery_image_modal').is(':visible')) {
                    $('#gallery_image_modal').fadeOut(300);
                    $('body').css('overflow', 'auto');
                }
            });
        }
    };

    // Initialiser au chargement du DOM
    $(document).ready(function() {
        VendorGallery.init();
    });

})(jQuery);
