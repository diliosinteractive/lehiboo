<?php
if ( !defined( 'ABSPATH' ) ) exit();

$current_user_id = get_current_user_id();
$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

// Récupérer tous les messages de l'organisateur connecté
$args = array(
	'post_type'      => 'organizer_message',
	'post_status'    => 'private',
	'author'         => $current_user_id,
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$messages_query = new WP_Query( $args );
?>

<div class="vendor_wrap">

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="el-notify" id="message-notification" style="display:none;">
			<p class="success status"></p>
			<p class="error status"></p>
		</div>

		<div class="table-list-booking">
			<table>
				<thead class="event_head">
					<tr>
						<td class="status-col"><?php esc_html_e( 'Statut', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'De', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Activité', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Objet', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Date', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Actions', 'eventlist' ); ?></td>
					</tr>
				</thead>
				<tbody class="event_body">
					<?php
					if( $messages_query->have_posts() ) :
						while ( $messages_query->have_posts() ) : $messages_query->the_post();
							$message_id = get_the_ID();

							// Métadonnées
							$from_name = get_post_meta( $message_id, '_from_name', true );
							$from_email = get_post_meta( $message_id, '_from_email', true );
							$subject = get_post_meta( $message_id, '_subject', true );
							$event_id = get_post_meta( $message_id, '_event_id', true );
							$sent_date = get_post_meta( $message_id, '_sent_date', true );
							$is_read = get_post_meta( $message_id, '_is_read', true );

							$event_title = get_the_title( $event_id );
							$message_content = get_the_content();

							// Si pas d'objet (anciens messages), créer un objet par défaut
							if ( empty( $subject ) ) {
								$subject = 'Message concernant: ' . $event_title;
							}

							$row_class = $is_read ? 'message-read' : 'message-unread';
					?>
						<tr class="<?php echo esc_attr( $row_class ); ?> message-row-<?php echo esc_attr( $message_id ); ?>">

							<!-- Statut -->
							<td data-colname="<?php esc_attr_e( 'Statut', 'eventlist' ); ?>" class="status-col">
								<?php if ( ! $is_read ) : ?>
									<span class="status-badge unread"><?php esc_html_e( 'Non lu', 'eventlist' ); ?></span>
								<?php else : ?>
									<span class="status-badge read"><?php esc_html_e( 'Lu', 'eventlist' ); ?></span>
								<?php endif; ?>
							</td>

							<!-- De -->
							<td data-colname="<?php esc_attr_e( 'De', 'eventlist' ); ?>">
								<strong><?php echo esc_html( $from_name ); ?></strong><br>
								<small><?php echo esc_html( $from_email ); ?></small>
							</td>

							<!-- Activité -->
							<td data-colname="<?php esc_attr_e( 'Activité', 'eventlist' ); ?>">
								<?php if ( $event_id && get_post_status( $event_id ) ) : ?>
									<a href="<?php echo esc_url( get_permalink( $event_id ) ); ?>" target="_blank">
										<?php echo esc_html( $event_title ); ?>
									</a>
								<?php elseif ( $event_id == 0 ) : ?>
									<strong style="color: #ff601f;"><?php esc_html_e( 'MA PAGE ORGANISATEUR', 'eventlist' ); ?></strong>
								<?php else : ?>
									<em><?php esc_html_e( 'Activité supprimée', 'eventlist' ); ?></em>
								<?php endif; ?>
							</td>

							<!-- Objet -->
							<td data-colname="<?php esc_attr_e( 'Objet', 'eventlist' ); ?>">
								<?php echo esc_html( $subject ); ?>
							</td>

							<!-- Date -->
							<td data-colname="<?php esc_attr_e( 'Date', 'eventlist' ); ?>">
								<?php
								$date_format = get_option('date_format');
								$time_format = get_option('time_format');
								echo date_i18n( $date_format . ' ' . $time_format, strtotime( $sent_date ) );
								?>
							</td>

							<!-- Actions -->
							<td>
								<div class="wp-button-my-booking">
									<button class="button btn-view-message" data-message-id="<?php echo esc_attr( $message_id ); ?>">
										<?php esc_html_e( 'Voir', 'eventlist' ); ?>
									</button>
								</div>
							</td>

						</tr>

						<!-- Ligne détails (cachée par défaut) -->
						<tr class="message-details" id="message-details-<?php echo esc_attr( $message_id ); ?>" style="display:none;">
							<td colspan="6" class="message-details-cell">
								<div class="message-details-content">
									<div class="message-detail-header">
										<h4><?php esc_html_e( 'Message complet', 'eventlist' ); ?></h4>
									</div>
									<div class="message-detail-info">
										<p><strong><?php esc_html_e( 'De:', 'eventlist' ); ?></strong> <?php echo esc_html( $from_name ); ?> (<?php echo esc_html( $from_email ); ?>)</p>
										<p><strong><?php esc_html_e( 'Activité:', 'eventlist' ); ?></strong>
											<?php if ( $event_id == 0 ) : ?>
												<strong style="color: #ff601f;"><?php esc_html_e( 'MA PAGE ORGANISATEUR', 'eventlist' ); ?></strong>
											<?php else : ?>
												<?php echo esc_html( $event_title ); ?>
											<?php endif; ?>
										</p>
										<p><strong><?php esc_html_e( 'Date:', 'eventlist' ); ?></strong> <?php echo date_i18n( $date_format . ' ' . $time_format, strtotime( $sent_date ) ); ?></p>
										<p><strong><?php esc_html_e( 'Objet:', 'eventlist' ); ?></strong> <?php echo esc_html( $subject ); ?></p>
									</div>
									<div class="message-detail-body">
										<strong><?php esc_html_e( 'Message:', 'eventlist' ); ?></strong>
										<p><?php echo nl2br( esc_html( $message_content ) ); ?></p>
									</div>

									<?php
									// Afficher l'historique des réponses
									$replies = get_post_meta( $message_id, '_replies', true );
									if ( ! empty( $replies ) && is_array( $replies ) ) :
									?>
										<div class="message-replies-section">
											<h5><?php esc_html_e( 'Vos réponses:', 'eventlist' ); ?></h5>
											<?php foreach ( $replies as $index => $reply ) : ?>
												<div class="message-reply-item">
													<div class="reply-header">
														<span class="reply-date">
															<?php echo date_i18n( $date_format . ' ' . $time_format, strtotime( $reply['date'] ) ); ?>
														</span>
														<span class="reply-from">
															<?php echo esc_html( $reply['from_name'] ); ?>
														</span>
													</div>
													<?php if ( ! empty( $reply['subject'] ) ) : ?>
														<div class="reply-subject">
															<strong><?php esc_html_e( 'Objet:', 'eventlist' ); ?></strong> <?php echo esc_html( $reply['subject'] ); ?>
														</div>
													<?php endif; ?>
													<div class="reply-message">
														<?php echo nl2br( esc_html( $reply['message'] ) ); ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<div class="message-detail-actions">
										<?php if ( ! $is_read ) : ?>
											<button type="button" class="button btn-mark-read" data-message-id="<?php echo esc_attr( $message_id ); ?>" data-nonce="<?php echo wp_create_nonce( 'mark_message_read_nonce' ); ?>">
												<?php esc_html_e( 'Marquer comme lu', 'eventlist' ); ?>
											</button>
										<?php endif; ?>
										<button type="button" class="button button-primary btn-reply-message"
											data-message-id="<?php echo esc_attr( $message_id ); ?>"
											data-to-email="<?php echo esc_attr( $from_email ); ?>"
											data-to-name="<?php echo esc_attr( $from_name ); ?>"
											data-subject="Re: <?php echo esc_attr( $subject ); ?>"
											data-event-title="<?php echo esc_attr( $event_title ); ?>">
											<?php esc_html_e( 'Répondre par mail', 'eventlist' ); ?>
										</button>
										<button type="button" class="button btn-close-message">
											<?php esc_html_e( 'Fermer', 'eventlist' ); ?>
										</button>
									</div>
								</div>
							</td>
						</tr>

					<?php endwhile;
					else : ?>
						<tr>
							<td colspan="6" style="text-align:center; padding: 40px;">
								<p><?php esc_html_e( 'Vous n\'avez encore reçu aucun message.', 'eventlist' ); ?></p>
							</td>
						</tr>
					<?php endif; wp_reset_postdata(); ?>

					<!-- Pagination -->
					<?php $total = $messages_query->max_num_pages; ?>
					<?php if ( $total > 1 ) { ?>
						<tr>
							<td colspan="6">
								<?php echo pagination_vendor($total); ?>
							</td>
						</tr>
					<?php } ?>

				</tbody>
			</table>
		</div>

	</div>

</div>

<!-- Modale de réponse par email -->
<div id="reply-modal" class="reply-modal-overlay" style="display:none;">
	<div class="reply-modal-container">
		<div class="reply-modal-header">
			<h3><?php esc_html_e( 'Répondre par email', 'eventlist' ); ?></h3>
			<button type="button" class="reply-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'eventlist' ); ?>">
				<span>&times;</span>
			</button>
		</div>
		<div class="reply-modal-body">
			<form id="reply-message-form" method="post">
				<div class="form-field">
					<label><?php esc_html_e( 'À:', 'eventlist' ); ?></label>
					<input type="text" id="reply_to_display" readonly>
					<input type="hidden" id="reply_to_email" name="to_email">
					<input type="hidden" id="reply_message_id" name="message_id">
				</div>
				<div class="form-field">
					<label for="reply_subject"><?php esc_html_e( 'Objet:', 'eventlist' ); ?> *</label>
					<input type="text" id="reply_subject" name="subject" required>
				</div>
				<div class="form-field">
					<label for="reply_message"><?php esc_html_e( 'Message:', 'eventlist' ); ?> *</label>
					<textarea id="reply_message" name="message" rows="10" required></textarea>
				</div>
				<div class="form-actions">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Envoyer', 'eventlist' ); ?>
					</button>
					<button type="button" class="button reply-modal-cancel">
						<?php esc_html_e( 'Annuler', 'eventlist' ); ?>
					</button>
				</div>
				<div class="reply-status" style="display:none; margin-top: 15px;"></div>
			</form>
		</div>
	</div>
</div>

<style>
/* Messages Styles pour EventList */
.message-unread {
	background: #FFF8F5 !important;
}

.message-unread:hover {
	background: #FFF0E8 !important;
}

.status-col {
	width: 100px;
}

.status-badge {
	display: inline-block;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
}

.status-badge.unread {
	background: #FFE5DD;
	color: #FF5722;
}

.status-badge.read {
	background: #E8F5E9;
	color: #4CAF50;
}

.message-details-cell {
	background: #F9F9F9 !important;
	padding: 20px !important;
}

.message-details-content {
	max-width: 900px;
}

.message-detail-header h4 {
	margin: 0 0 15px 0;
	font-size: 18px;
	color: #333;
}

.message-detail-info {
	margin-bottom: 20px;
	padding-bottom: 15px;
	border-bottom: 1px solid #E0E0E0;
}

.message-detail-info p {
	margin: 5px 0;
	font-size: 14px;
	color: #666;
}

.message-detail-body {
	background: #FFFFFF;
	padding: 20px;
	border-radius: 6px;
	border: 1px solid #E0E0E0;
	margin-bottom: 20px;
	line-height: 1.6;
	color: #333;
}

.message-detail-body strong {
	display: block;
	margin-bottom: 10px;
	color: #333;
	font-size: 14px;
}

.message-detail-body p {
	margin: 0;
	color: #555;
}

/* Styles pour l'historique des réponses */
.message-replies-section {
	margin: 20px 0;
	padding: 20px;
	background: #F5F5F5;
	border-radius: 6px;
	border-left: 4px solid #4CAF50;
}

.message-replies-section h5 {
	margin: 0 0 15px 0;
	font-size: 16px;
	color: #333;
	font-weight: 600;
}

.message-reply-item {
	background: #FFFFFF;
	padding: 15px;
	border-radius: 4px;
	margin-bottom: 12px;
	border: 1px solid #E0E0E0;
}

.message-reply-item:last-child {
	margin-bottom: 0;
}

.reply-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
	padding-bottom: 8px;
	border-bottom: 1px solid #F0F0F0;
}

.reply-date {
	font-size: 12px;
	color: #999;
}

.reply-from {
	font-size: 13px;
	color: #4CAF50;
	font-weight: 600;
}

.reply-subject {
	margin-bottom: 10px;
	font-size: 13px;
	color: #666;
}

.reply-message {
	font-size: 14px;
	line-height: 1.5;
	color: #555;
}

.message-detail-actions {
	display: flex;
	gap: 10px;
}

.wp-button-my-booking {
	display: flex;
	gap: 8px;
}

@media (max-width: 768px) {
	.table-list-booking table thead {
		display: none;
	}

	.table-list-booking table tr {
		display: block;
		margin-bottom: 20px;
		border: 1px solid #DDD;
		padding: 15px;
	}

	.table-list-booking table td {
		display: block;
		text-align: left !important;
		padding: 8px 0 !important;
		border: none !important;
	}

	.table-list-booking table td:before {
		content: attr(data-colname) ": ";
		font-weight: 600;
		margin-right: 8px;
	}
}

/* Reply Modal Styles */
.reply-modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.6);
	z-index: 9999;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
}

.reply-modal-container {
	background: #FFFFFF;
	border-radius: 8px;
	max-width: 700px;
	width: 100%;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.reply-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px 25px;
	border-bottom: 1px solid #E0E0E0;
}

.reply-modal-header h3 {
	margin: 0;
	font-size: 20px;
	color: #333;
}

.reply-modal-close {
	background: none;
	border: none;
	font-size: 32px;
	line-height: 1;
	color: #999;
	cursor: pointer;
	padding: 0;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: color 0.2s;
}

.reply-modal-close:hover {
	color: #333;
}

.reply-modal-body {
	padding: 25px;
}

.reply-modal-body .form-field {
	margin-bottom: 20px;
}

.reply-modal-body .form-field label {
	display: block;
	font-weight: 600;
	margin-bottom: 8px;
	color: #333;
	font-size: 14px;
}

.reply-modal-body .form-field input[type="text"],
.reply-modal-body .form-field textarea {
	width: 100%;
	padding: 10px 15px;
	border: 1px solid #DDD;
	border-radius: 4px;
	font-size: 14px;
	font-family: inherit;
	transition: border-color 0.2s;
}

.reply-modal-body .form-field input[type="text"]:focus,
.reply-modal-body .form-field textarea:focus {
	outline: none;
	border-color: #2271b1;
}

.reply-modal-body .form-field input[readonly] {
	background-color: #F5F5F5;
	color: #666;
}

.reply-modal-body .form-actions {
	display: flex;
	gap: 10px;
	margin-top: 25px;
}

.reply-status {
	padding: 12px 15px;
	border-radius: 4px;
	font-size: 14px;
}

.reply-status.success {
	background: #E8F5E9;
	color: #2E7D32;
	border: 1px solid #A5D6A7;
}

.reply-status.error {
	background: #FFEBEE;
	color: #C62828;
	border: 1px solid #EF9A9A;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Toggle message details
	$('.btn-view-message').on('click', function() {
		var messageId = $(this).data('message-id');
		var $detailsRow = $('#message-details-' + messageId);
		var $currentRow = $('.message-row-' + messageId);

		// Fermer tous les autres détails
		$('.message-details').not($detailsRow).slideUp(300);

		// Toggle le détail actuel
		$detailsRow.slideToggle(300);

		// Marquer comme lu visuellement si non lu
		if ($currentRow.hasClass('message-unread')) {
			$currentRow.removeClass('message-unread').addClass('message-read');
			$currentRow.find('.status-badge').removeClass('unread').addClass('read')
				.text('<?php esc_html_e( "Lu", "eventlist" ); ?>');
		}
	});

	// Marquer comme lu via AJAX
	$('.btn-mark-read').on('click', function() {
		var $btn = $(this);
		var messageId = $btn.data('message-id');
		var nonce = $btn.data('nonce');
		var originalText = $btn.text();

		// Désactiver le bouton
		$btn.prop('disabled', true).text('<?php esc_html_e( "En cours...", "eventlist" ); ?>');

		$.ajax({
			url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
			type: 'POST',
			data: {
				action: 'mark_message_read',
				message_id: messageId,
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					// Afficher notification succès
					$('#message-notification').show();
					$('#message-notification .success').text(response.data.message).show();
					$('#message-notification .error').hide();

					// Mettre à jour le statut visuellement
					var $row = $('.message-row-' + messageId);
					$row.removeClass('message-unread').addClass('message-read');
					$row.find('.status-badge').removeClass('unread').addClass('read')
						.text('<?php esc_html_e( "Lu", "eventlist" ); ?>');

					// Cacher le bouton
					$btn.fadeOut();

					// Cacher la notification après 3 secondes
					setTimeout(function() {
						$('#message-notification').fadeOut();
					}, 3000);

					// Recharger la page pour mettre à jour le compteur du sidebar
					setTimeout(function() {
						window.location.reload();
					}, 1500);
				} else {
					// Afficher erreur
					$('#message-notification').show();
					$('#message-notification .error').text(response.data.message).show();
					$('#message-notification .success').hide();
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				$('#message-notification').show();
				$('#message-notification .error').text('<?php esc_html_e( "Une erreur est survenue.", "eventlist" ); ?>').show();
				$('#message-notification .success').hide();
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});

	// Bouton Fermer message
	$('.btn-close-message').on('click', function() {
		$(this).closest('.message-details').slideUp(300);
	});

	// Ouvrir la modale de réponse
	$('.btn-reply-message').on('click', function() {
		var $btn = $(this);
		var toEmail = $btn.data('to-email');
		var toName = $btn.data('to-name');
		var subject = $btn.data('subject');
		var messageId = $btn.data('message-id');

		// Remplir le formulaire
		$('#reply_to_display').val(toName + ' <' + toEmail + '>');
		$('#reply_to_email').val(toEmail);
		$('#reply_subject').val(subject);
		$('#reply_message_id').val(messageId);
		$('#reply_message').val('');

		// Afficher la modale
		$('#reply-modal').fadeIn(200);
	});

	// Fermer la modale
	$('.reply-modal-close, .reply-modal-cancel').on('click', function() {
		$('#reply-modal').fadeOut(200);
		$('#reply-message-form')[0].reset();
		$('.reply-status').hide();
	});

	// Fermer si clic en dehors
	$('#reply-modal').on('click', function(e) {
		if ($(e.target).is('#reply-modal')) {
			$(this).fadeOut(200);
			$('#reply-message-form')[0].reset();
			$('.reply-status').hide();
		}
	});

	// Soumettre la réponse
	$('#reply-message-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $submitBtn = $form.find('button[type="submit"]');
		var $statusDiv = $('.reply-status');
		var originalText = $submitBtn.text();

		// Désactiver le bouton
		$submitBtn.prop('disabled', true).text('<?php esc_html_e( "Envoi en cours...", "eventlist" ); ?>');
		$statusDiv.hide().removeClass('success error');

		$.ajax({
			url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
			type: 'POST',
			data: {
				action: 'reply_to_message',
				to_email: $('#reply_to_email').val(),
				subject: $('#reply_subject').val(),
				message: $('#reply_message').val(),
				message_id: $('#reply_message_id').val(),
				nonce: '<?php echo wp_create_nonce( "reply_message_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$statusDiv.addClass('success').html(response.data.message).show();

					// Réinitialiser le formulaire après 2 secondes
					setTimeout(function() {
						$('#reply-modal').fadeOut(200);
						$form[0].reset();
						$statusDiv.hide();
						$submitBtn.prop('disabled', false).text(originalText);
					}, 2000);
				} else {
					$statusDiv.addClass('error').html(response.data.message || '<?php esc_html_e( "Erreur lors de l\'envoi.", "eventlist" ); ?>').show();
					$submitBtn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				$statusDiv.addClass('error').html('<?php esc_html_e( "Une erreur est survenue.", "eventlist" ); ?>').show();
				$submitBtn.prop('disabled', false).text(originalText);
			}
		});
	});
});
</script>
