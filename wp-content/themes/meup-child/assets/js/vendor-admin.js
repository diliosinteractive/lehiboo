/**
 * Vendor Admin JavaScript
 * Gestion des actions d'approbation/rejet des demandes partenaires
 * @version 1.0.0
 */

(function($) {
	'use strict';

	const VendorAdmin = {

		currentUserId: null,

		/**
		 * Initialisation
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Approve button
			$(document).on('click', '.button_approve', function(e) {
				e.preventDefault();
				const userId = $(this).data('user-id');
				VendorAdmin.confirmApprove(userId);
			});

			// Reject button
			$(document).on('click', '.button_reject', function(e) {
				e.preventDefault();
				const userId = $(this).data('user-id');
				VendorAdmin.showRejectModal(userId);
			});

			// Modal close
			$(document).on('click', '.modal_close, .button_cancel', function() {
				VendorAdmin.closeModal();
			});

			// Confirm reject
			$(document).on('click', '.button_confirm_reject', function() {
				VendorAdmin.confirmReject();
			});

			// Close modal on outside click
			$(document).on('click', '.vendor_modal', function(e) {
				if ($(e.target).hasClass('vendor_modal')) {
					VendorAdmin.closeModal();
				}
			});

			// ESC key to close modal
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape') {
					VendorAdmin.closeModal();
				}
			});
		},

		/**
		 * Confirm approve action
		 */
		confirmApprove: function(userId) {
			if (!confirm('Êtes-vous sûr de vouloir approuver cette demande partenaire ? Un email de confirmation sera envoyé.')) {
				return;
			}

			this.approveVendor(userId);
		},

		/**
		 * Approve vendor via AJAX
		 */
		approveVendor: function(userId) {
			const $button = $('.button_approve[data-user-id="' + userId + '"]');
			const $row = $button.closest('tr');

			// Disable button
			$button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Approbation...');

			$.ajax({
				url: lehiboo_vendor_admin.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_approve_vendor',
					nonce: lehiboo_vendor_admin.nonce,
					user_id: userId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						// Show success message
						VendorAdmin.showNotification('success', response.data.message);

						// Update row status
						$row.find('.status_badge')
							.removeClass('status_pending')
							.addClass('status_approved')
							.text('Approuvé');

						// Remove action buttons
						$row.find('.column_actions').html(
							'<button class="button button_view" onclick="toggleVendorDetails(' + userId + ')">' +
							'<i class="fas fa-eye"></i> Voir détails</button>'
						);

						// Update stats if visible
						VendorAdmin.updateStats();

						// Reload after 2 seconds
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						VendorAdmin.showNotification('error', response.data.message);
						$button.prop('disabled', false).html('<i class="fas fa-check"></i> Approuver');
					}
				},
				error: function(xhr, status, error) {
					console.error('Erreur AJAX:', error);
					VendorAdmin.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$button.prop('disabled', false).html('<i class="fas fa-check"></i> Approuver');
				}
			});
		},

		/**
		 * Show reject modal
		 */
		showRejectModal: function(userId) {
			this.currentUserId = userId;
			$('#reject_reason').val('');
			$('#reject_modal').fadeIn(200);
		},

		/**
		 * Close modal
		 */
		closeModal: function() {
			$('#reject_modal').fadeOut(200);
			this.currentUserId = null;
		},

		/**
		 * Confirm reject action
		 */
		confirmReject: function() {
			const reason = $('#reject_reason').val().trim();
			const userId = this.currentUserId;

			if (!userId) {
				return;
			}

			this.rejectVendor(userId, reason);
		},

		/**
		 * Reject vendor via AJAX
		 */
		rejectVendor: function(userId, reason) {
			const $button = $('.button_confirm_reject');
			const $row = $('.button_reject[data-user-id="' + userId + '"]').closest('tr');

			// Disable button
			$button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Rejet en cours...');

			$.ajax({
				url: lehiboo_vendor_admin.ajax_url,
				type: 'POST',
				data: {
					action: 'lehiboo_reject_vendor',
					nonce: lehiboo_vendor_admin.nonce,
					user_id: userId,
					reason: reason
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						// Close modal
						VendorAdmin.closeModal();

						// Show success message
						VendorAdmin.showNotification('success', response.data.message);

						// Update row status
						$row.find('.status_badge')
							.removeClass('status_pending')
							.addClass('status_rejected')
							.text('Rejeté');

						// Remove action buttons
						$row.find('.column_actions').html(
							'<button class="button button_view" onclick="toggleVendorDetails(' + userId + ')">' +
							'<i class="fas fa-eye"></i> Voir détails</button>'
						);

						// Update stats if visible
						VendorAdmin.updateStats();

						// Reload after 2 seconds
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						VendorAdmin.showNotification('error', response.data.message);
						$button.prop('disabled', false).html('Confirmer le rejet');
					}
				},
				error: function(xhr, status, error) {
					console.error('Erreur AJAX:', error);
					VendorAdmin.showNotification('error', 'Une erreur est survenue. Veuillez réessayer.');
					$button.prop('disabled', false).html('Confirmer le rejet');
				}
			});
		},

		/**
		 * Show notification
		 */
		showNotification: function(type, message) {
			// Remove existing notifications
			$('.admin-notice').remove();

			// Create notification
			const $notice = $('<div class="notice notice-' + type + ' is-dismissible admin-notice"><p>' + message + '</p></div>');

			// Insert after page title
			if ($('.lehiboo_vendor_admin, .lehiboo_vendor_stats').length) {
				$('.admin_page_title').after($notice);
			} else {
				$('.wrap').prepend($notice);
			}

			// Scroll to top
			$('html, body').animate({
				scrollTop: 0
			}, 300);

			// Auto dismiss after 5 seconds
			setTimeout(function() {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			}, 5000);

			// Make dismissible work
			$(document).on('click', '.admin-notice .notice-dismiss', function() {
				$(this).closest('.admin-notice').fadeOut(300, function() {
					$(this).remove();
				});
			});
		},

		/**
		 * Update stats counters
		 */
		updateStats: function() {
			// Decrement pending counter
			const $pendingValue = $('.stat_pending .stat_value');
			const pendingCount = parseInt($pendingValue.text()) - 1;
			$pendingValue.text(Math.max(0, pendingCount));

			// Update title badge
			const $titleBadge = $('.title_badge');
			$titleBadge.text(Math.max(0, pendingCount) + ' en attente');

			// If we have action badges, update them too
			$('.quick_action_btn.pending .action_badge').text(Math.max(0, pendingCount));
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.lehiboo_vendor_admin, .lehiboo_vendor_stats').length) {
			VendorAdmin.init();
		}
	});

	// Make VendorAdmin globally accessible for inline onclick handlers
	window.VendorAdmin = VendorAdmin;

})(jQuery);
