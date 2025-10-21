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

// Gérer l'action de marquage comme lu
if ( isset( $_POST['mark_as_read'] ) && isset( $_POST['message_id'] ) && wp_verify_nonce( $_POST['message_nonce'], 'mark_message_read' ) ) {
	$message_id = intval( $_POST['message_id'] );
	$message = get_post( $message_id );
	if ( $message && $message->post_author == $current_user_id ) {
		update_post_meta( $message_id, '_is_read', 1 );
		wp_safe_redirect( add_query_arg( array( 'vendor' => 'messages' ), get_myaccount_page() ) );
		exit;
	}
}
?>

<div class="vendor_wrap">

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="table-list-booking">
			<table>
				<thead class="event_head">
					<tr>
						<td class="status-col"><?php esc_html_e( 'Statut', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'De', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Activité', 'eventlist' ); ?></td>
						<td><?php esc_html_e( 'Message', 'eventlist' ); ?></td>
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
							$event_id = get_post_meta( $message_id, '_event_id', true );
							$sent_date = get_post_meta( $message_id, '_sent_date', true );
							$is_read = get_post_meta( $message_id, '_is_read', true );

							$event_title = get_the_title( $event_id );
							$message_content = get_the_content();
							$message_excerpt = wp_trim_words( $message_content, 10, '...' );

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
								<?php else : ?>
									<em><?php esc_html_e( 'Activité supprimée', 'eventlist' ); ?></em>
								<?php endif; ?>
							</td>

							<!-- Message -->
							<td data-colname="<?php esc_attr_e( 'Message', 'eventlist' ); ?>">
								<?php echo esc_html( $message_excerpt ); ?>
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
										<p><strong><?php esc_html_e( 'Activité:', 'eventlist' ); ?></strong> <?php echo esc_html( $event_title ); ?></p>
										<p><strong><?php esc_html_e( 'Date:', 'eventlist' ); ?></strong> <?php echo date_i18n( $date_format . ' ' . $time_format, strtotime( $sent_date ) ); ?></p>
									</div>
									<div class="message-detail-body">
										<?php echo nl2br( esc_html( $message_content ) ); ?>
									</div>
									<div class="message-detail-actions">
										<?php if ( ! $is_read ) : ?>
											<form method="post" style="display:inline;">
												<input type="hidden" name="message_id" value="<?php echo esc_attr( $message_id ); ?>">
												<?php wp_nonce_field( 'mark_message_read', 'message_nonce' ); ?>
												<button type="submit" name="mark_as_read" class="button">
													<?php esc_html_e( 'Marquer comme lu', 'eventlist' ); ?>
												</button>
											</form>
										<?php endif; ?>
										<a href="mailto:<?php echo esc_attr( $from_email ); ?>" class="button">
											<?php esc_html_e( 'Répondre', 'eventlist' ); ?>
										</a>
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
});
</script>
