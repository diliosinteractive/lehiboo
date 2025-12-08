/**
 * Vendor Events Listing - JavaScript
 * Gère les interactions sur la liste des événements côté partenaire
 */

jQuery(document).ready(function($) {

    /* ==========================================================================
       1. Modale des dates
       ========================================================================== */

    // Ouvrir la modale des dates
    $(document).on('click', '.btn-more-dates', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $row = $btn.closest('tr');
        var $dataScript = $row.find('.all-dates-data');

        if ($dataScript.length) {
            try {
                var allDates = JSON.parse($dataScript.html());
                var $modal = $('#el-dates-modal');
                var $datesList = $modal.find('.dates-list');

                // Construire la liste des dates
                var html = '<table class="dates-table"><thead><tr><th>Date</th><th>Début</th><th>Fin</th></tr></thead><tbody>';

                allDates.forEach(function(item) {
                    var dateObj = new Date(item.date * 1000);
                    var formattedDate = dateObj.toLocaleDateString('fr-FR', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    html += '<tr>';
                    html += '<td>' + formattedDate + '</td>';
                    html += '<td>' + (item.start_time || '-') + '</td>';
                    html += '<td>' + (item.end_time || '-') + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
                $datesList.html(html);

                // Afficher la modale
                $modal.fadeIn(200);
            } catch (e) {
                console.error('Erreur parsing dates:', e);
            }
        }
    });


    /* ==========================================================================
       2. Fermeture des modales
       ========================================================================== */

    // Fermer avec le bouton X
    $(document).on('click', '.el-modal-close', function() {
        $(this).closest('.el-modal').fadeOut(200);
    });

    // Fermer en cliquant sur l'overlay
    $(document).on('click', '.el-modal-overlay', function() {
        $(this).closest('.el-modal').fadeOut(200);
    });

    // Fermer avec Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.el-modal:visible').fadeOut(200);
        }
    });

    // Bouton Annuler dans la modale de suppression
    $(document).on('click', '.btn-modal-cancel', function() {
        $(this).closest('.el-modal').fadeOut(200);
    });


    /* ==========================================================================
       3. Logique de suppression
       ========================================================================== */

    var currentDeleteData = null;

    // Ouvrir la modale de suppression
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var postId = $btn.data('post-id');
        var eventTitle = $btn.data('event-title');
        var hasInteractions = $btn.data('has-interactions') === 1 || $btn.data('has-interactions') === '1';
        var isOnline = $btn.data('is-online') === 1 || $btn.data('is-online') === '1';

        currentDeleteData = {
            postId: postId,
            eventTitle: eventTitle,
            hasInteractions: hasInteractions,
            isOnline: isOnline,
            $row: $btn.closest('tr')
        };

        var $modal = $('#el-delete-modal');
        var $message = $modal.find('.delete-message');
        var $confirmBtn = $modal.find('.btn-modal-confirm');

        // Déterminer le message et l'action
        if (!hasInteractions) {
            // Pas d'interactions -> on peut supprimer
            $message.html('Êtes-vous certain de vouloir supprimer la fiche "<strong>' + eventTitle + '</strong>" ?');
            $confirmBtn.data('action', 'delete');
            $confirmBtn.show();
        } else if (hasInteractions && isOnline) {
            // Interactions + en ligne -> proposer mise hors ligne
            $message.html('Cette page a des interactions avec des utilisateurs. Vous ne pouvez pas la supprimer. Par contre, vous pouvez la mettre hors ligne.<br><br>Souhaitez-vous mettre l\'activité "<strong>' + eventTitle + '</strong>" hors ligne ?');
            $confirmBtn.data('action', 'offline');
            $confirmBtn.show();
        } else {
            // Interactions + déjà hors ligne -> juste info
            $message.html('Cette page a des interactions avec des utilisateurs. Vous ne pouvez pas la supprimer.');
            $confirmBtn.hide();
        }

        $modal.fadeIn(200);
    });

    // Confirmer l'action de suppression/mise hors ligne
    $(document).on('click', '.btn-modal-confirm', function() {
        if (!currentDeleteData) return;

        var action = $(this).data('action');
        var $modal = $('#el-delete-modal');

        if (action === 'delete') {
            // Supprimer l'événement
            deleteEvent(currentDeleteData.postId, currentDeleteData.$row);
        } else if (action === 'offline') {
            // Mettre hors ligne (pending)
            setEventOffline(currentDeleteData.postId, currentDeleteData.$row);
        }

        $modal.fadeOut(200);
        currentDeleteData = null;
    });

    // Fonction pour supprimer un événement
    function deleteEvent(postId, $row) {
        var nonce = $('#el_delete_post_nonce_' + postId).val();

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_trash_post',
                data: {
                    post_id: postId,
                    el_trash_post_nonce: nonce
                }
            },
            success: function(response) {
                if (response === true || response.status === 'success') {
                    // Supprimer la ligne du tableau
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });

                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement supprimé avec succès !');
                    }
                } else {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error('Erreur lors de la suppression.');
                    } else {
                        alert('Erreur lors de la suppression.');
                    }
                }
            },
            error: function() {
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error('Erreur lors de la suppression.');
                } else {
                    alert('Erreur lors de la suppression.');
                }
            }
        });
    }

    // Fonction pour mettre un événement hors ligne
    function setEventOffline(postId, $row) {
        var nonce = $('#el_pending_post_nonce_' + postId).val();

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_pending_post',
                data: {
                    post_id: postId,
                    el_pending_post_nonce: nonce
                }
            },
            success: function(response) {
                if (response === true || response.status === 'success') {
                    // Mettre à jour le badge de statut
                    var $badge = $row.find('.status-badge');
                    $badge.removeClass('status-online').addClass('status-offline');
                    $badge.text('Hors ligne');

                    // Mettre à jour les données
                    $row.data('is-online', '0');
                    $row.find('.btn-delete').data('is-online', '0');

                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Événement mis hors ligne !');
                    }
                } else {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error('Erreur lors de la mise hors ligne.');
                    } else {
                        alert('Erreur lors de la mise hors ligne.');
                    }
                }
            },
            error: function() {
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error('Erreur lors de la mise hors ligne.');
                } else {
                    alert('Erreur lors de la mise hors ligne.');
                }
            }
        });
    }


    /* ==========================================================================
       4. Duplication
       ========================================================================== */

    $(document).on('click', '.btn-duplicate', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var postId = $btn.data('post-id');
        var nonce = $('#el_duplicate_post_nonce_' + postId).val();

        // Confirm action
        if (!confirm('Voulez-vous dupliquer cette activité ?')) {
            return;
        }

        // Disable button during request
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_duplicate_post',
                data: {
                    post_id: postId,
                    el_duplicate_post_nonce: nonce
                }
            },
            success: function(response) {
                $btn.prop('disabled', false).removeClass('loading');

                if (response.status === 'success' && response.href) {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success('Activité dupliquée avec succès !');
                    }
                    // Redirect to the new duplicated event
                    setTimeout(function() {
                        window.location.href = response.href;
                    }, 1000);
                } else if (response.status === 'error') {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(response.msg || 'Une erreur est survenue lors de la duplication.');
                    } else {
                        alert(response.msg || 'Une erreur est survenue lors de la duplication.');
                    }
                    // If there's a redirect URL (e.g., for package upgrade), offer to redirect
                    if (response.url && confirm(response.msg)) {
                        window.location.href = response.url;
                    }
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).removeClass('loading');
                console.error('Duplicate error:', error);
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error('Erreur lors de la duplication. Veuillez réessayer.');
                } else {
                    alert('Erreur lors de la duplication. Veuillez réessayer.');
                }
            }
        });
    });

});
