/**
 * EventList Vendor Gallery Management
 * Gestion de la galerie d'images du partenaire
 */

(function($) {
    'use strict';

    const VendorGallery = {

        init: function() {
            this.addGalleryImages();
            this.deleteImage();
            this.viewImage();
            this.closeModal();
        },

        /**
         * Ajouter des images à la galerie via le media uploader WordPress
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
                    }
                });

                // Quand des images sont sélectionnées
                frame.on('select', function() {
                    const attachments = frame.state().get('selection').toJSON();
                    const imageIds = attachments.map(function(attachment) {
                        return attachment.id;
                    });

                    // Sauvegarder via AJAX
                    VendorGallery.saveGalleryImages(imageIds);
                });

                frame.open();
            });
        },

        /**
         * Sauvegarder les nouvelles images via AJAX
         */
        saveGalleryImages: function(imageIds) {
            $.ajax({
                url: el_general.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_add_gallery_images',
                    nonce: el_general.ajax_nonce,
                    image_ids: imageIds
                },
                beforeSend: function() {
                    $('.add_gallery_images').prop('disabled', true).html('<i class="icon_loading"></i> ' + 'Ajout en cours...');
                },
                success: function(response) {
                    if (response.success) {
                        // Recharger la page pour afficher les nouvelles images
                        location.reload();
                    } else {
                        alert(response.data.message || 'Erreur lors de l\'ajout des images');
                        $('.add_gallery_images').prop('disabled', false).html('<i class="icon_plus"></i> Ajouter des images');
                    }
                },
                error: function() {
                    alert('Erreur de connexion au serveur');
                    $('.add_gallery_images').prop('disabled', false).html('<i class="icon_plus"></i> Ajouter des images');
                }
            });
        },

        /**
         * Supprimer une image de la galerie
         */
        deleteImage: function() {
            $(document).on('click', '.delete_image_btn', function(e) {
                e.preventDefault();

                if (!confirm('Êtes-vous sûr de vouloir supprimer cette image de votre galerie ?')) {
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
                            item.fadeOut(300, function() {
                                $(this).remove();

                                // Mettre à jour le compteur
                                const currentCount = parseInt($('#gallery_count').text());
                                $('#gallery_count').text(currentCount - 1);

                                // Si plus d'images, afficher l'état vide
                                if ($('.galerie_item').length === 0) {
                                    $('#gallery_items_grid').remove();
                                    $('.galerie_footer').before(`
                                        <div class="galerie_empty" id="gallery_empty_state">
                                            <div class="galerie_empty_icon">
                                                <i class="icon_images"></i>
                                            </div>
                                            <h3>Votre galerie est vide</h3>
                                            <p>Ajoutez des images pour mettre en valeur votre organisation et vos événements.</p>
                                        </div>
                                    `);
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
