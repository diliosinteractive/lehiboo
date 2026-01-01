<?php
if ( ! defined( 'ABSPATH' ) ) exit();

// Récupérer les activités archivées
$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$archived_events = lehiboo_get_archived_events( null, $paged );
$_prefix = OVA_METABOX_EVENT;
?>

<!-- Custom Assets for Archived Events -->
<link rel="stylesheet" href="<?php echo EL_PLUGIN_URI . 'assets/css/vendor-events-listing.css?v=' . time(); ?>">

<div class="vendor_wrap">

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">

		<?php echo lehiboo_render_vendor_header_menu(); ?>

		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<div class="vendor_listing el-events-listing el-archived-events">

			<div class="archived-header">
				<h2><?php esc_html_e( 'Activités archivées', 'eventlist' ); ?></h2>
				<p class="archived-description">
					<?php esc_html_e( 'Ces activités ont été archivées. Elles ne sont plus visibles par les utilisateurs mais leur historique (réservations, statistiques) est conservé. Vous pouvez les restaurer à tout moment.', 'eventlist' ); ?>
				</p>
			</div>

			<?php if ( $archived_events->have_posts() ) : ?>

			<table class="el-events-listing-table">
				<thead class="event_head">
					<tr>
						<th class="col-image"><?php esc_html_e( 'Image', 'eventlist' ); ?></th>
						<th class="col-event"><?php esc_html_e( 'Événement', 'eventlist' ); ?></th>
						<th class="col-archived-date"><?php esc_html_e( 'Date d\'archivage', 'eventlist' ); ?></th>
						<th class="col-actions"><?php esc_html_e( 'Actions', 'eventlist' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( $archived_events->have_posts() ) : $archived_events->the_post();
						$post_id = get_the_ID();
						$event_title = get_the_title();
						$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' ) ?: EL_PLUGIN_URI . 'assets/img/no-image.png';
						$archived_date = get_post_meta( $post_id, '_archived_date', true );
						$archived_from = get_post_meta( $post_id, '_archived_from_status', true );

						// Infos lieu
						$location_name = get_post_meta( $post_id, $_prefix . 'venue', true );
						$city = get_post_meta( $post_id, $_prefix . 'city', true );
						$location_display = ! empty( $location_name ) ? $location_name : '';
						if ( ! empty( $city ) && ! empty( $location_display ) ) {
							$location_display .= ', ' . $city;
						} elseif ( ! empty( $city ) ) {
							$location_display = $city;
						}

						// Catégories
						$categories = get_the_terms( $post_id, 'event_cat' );
					?>

					<tr class="event-row" data-post-id="<?php echo esc_attr( $post_id ); ?>">
						<!-- Image -->
						<td class="col-image">
							<div class="event-thumbnail">
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $event_title ); ?>">
							</div>
						</td>

						<!-- Événement -->
						<td class="col-event">
							<div class="event-info">
								<h4 class="event-title"><?php echo esc_html( $event_title ); ?></h4>

								<?php if ( ! empty( $location_display ) ) : ?>
								<div class="event-location">
									<i class="icon_pin_alt"></i>
									<span><?php echo esc_html( $location_display ); ?></span>
								</div>
								<?php endif; ?>

								<div class="event-tags">
									<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
										<?php foreach ( $categories as $cat ) : ?>
											<span class="tag tag-category"><?php echo esc_html( $cat->name ); ?></span>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>

								<div class="event-status-actions">
									<span class="status-badge status-archived">
										<?php esc_html_e( 'Archivé', 'eventlist' ); ?>
									</span>
									<?php if ( $archived_from === 'publish' ) : ?>
										<span class="archived-from-badge"><?php esc_html_e( '(était en ligne)', 'eventlist' ); ?></span>
									<?php else : ?>
										<span class="archived-from-badge"><?php esc_html_e( '(était hors ligne)', 'eventlist' ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</td>

						<!-- Date d'archivage -->
						<td class="col-archived-date">
							<?php if ( $archived_date ) : ?>
								<span class="archived-date">
									<?php echo date_i18n( 'j F Y', strtotime( $archived_date ) ); ?>
								</span>
								<span class="archived-time">
									<?php echo date_i18n( 'H:i', strtotime( $archived_date ) ); ?>
								</span>
							<?php else : ?>
								<span class="no-date">-</span>
							<?php endif; ?>
						</td>

						<!-- Actions -->
						<td class="col-actions">
							<div class="action-buttons">
								<button type="button" class="btn-action btn-restore" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-event-title="<?php echo esc_attr( $event_title ); ?>">
									<i class="icon_refresh"></i>
									<?php esc_html_e( 'Restaurer', 'eventlist' ); ?>
								</button>
								<?php wp_nonce_field( 'el_restore_post_nonce', 'el_restore_post_nonce_' . $post_id ); ?>
							</div>
						</td>
					</tr>

					<?php endwhile; wp_reset_postdata(); ?>
				</tbody>
			</table>

			<?php
			// Pagination
			$total = $archived_events->max_num_pages;
			if ( $total > 1 ) : ?>
				<div class="my_list_pagination">
					<?php echo pagination_vendor( $total ); ?>
				</div>
			<?php endif; ?>

			<?php else : ?>
				<div class="no-archived-events">
					<div class="empty-state">
						<i class="icon_archive_alt"></i>
						<h3><?php esc_html_e( 'Aucune activité archivée', 'eventlist' ); ?></h3>
						<p><?php esc_html_e( 'Vous n\'avez pas encore d\'activité archivée.', 'eventlist' ); ?></p>
						<a href="<?php echo add_query_arg( array( 'vendor' => 'listing' ), get_myaccount_page() ); ?>" class="btn-back-to-listing">
							<?php esc_html_e( 'Retour à mes activités', 'eventlist' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>

		</div>

	</div>

</div>

<style>
.el-archived-events .archived-header {
	margin-bottom: 24px;
	padding-bottom: 16px;
	border-bottom: 1px solid #e2e8f0;
}

.el-archived-events .archived-header h2 {
	margin: 0 0 8px 0;
	font-size: 24px;
	font-weight: 600;
	color: #1e293b;
}

.el-archived-events .archived-description {
	margin: 0;
	color: #64748b;
	font-size: 14px;
	line-height: 1.5;
}

.el-archived-events .status-badge.status-archived {
	background-color: #94a3b8;
	color: white;
}

.el-archived-events .archived-from-badge {
	font-size: 12px;
	color: #94a3b8;
	font-style: italic;
}

.el-archived-events .col-archived-date {
	text-align: center;
}

.el-archived-events .archived-date {
	display: block;
	font-weight: 500;
	color: #334155;
}

.el-archived-events .archived-time {
	display: block;
	font-size: 12px;
	color: #94a3b8;
}

.el-archived-events .btn-restore {
	background-color: #10b981 !important;
	border-color: #10b981 !important;
	color: white !important;
}

.el-archived-events .btn-restore:hover {
	background-color: #059669 !important;
	border-color: #059669 !important;
}

.el-archived-events .btn-restore i {
	margin-right: 4px;
}

.el-archived-events .no-archived-events {
	padding: 60px 20px;
	text-align: center;
}

.el-archived-events .empty-state {
	max-width: 400px;
	margin: 0 auto;
}

.el-archived-events .empty-state i {
	font-size: 64px;
	color: #cbd5e1;
	margin-bottom: 16px;
}

.el-archived-events .empty-state h3 {
	margin: 0 0 8px 0;
	font-size: 20px;
	font-weight: 600;
	color: #334155;
}

.el-archived-events .empty-state p {
	margin: 0 0 24px 0;
	color: #64748b;
}

.el-archived-events .btn-back-to-listing {
	display: inline-block;
	padding: 12px 24px;
	background-color: #f97316;
	color: white;
	text-decoration: none;
	border-radius: 8px;
	font-weight: 500;
	transition: background-color 0.2s;
}

.el-archived-events .btn-back-to-listing:hover {
	background-color: #ea580c;
	color: white;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Restaurer une activité
	$(document).on('click', '.btn-restore', function(e) {
		e.preventDefault();

		var $btn = $(this);
		var postId = $btn.data('post-id');
		var eventTitle = $btn.data('event-title');
		var $row = $btn.closest('tr');
		var nonce = $('#el_restore_post_nonce_' + postId).val();

		if (!confirm('Voulez-vous restaurer l\'activité "' + eventTitle + '" ?')) {
			return;
		}

		$btn.prop('disabled', true).addClass('loading');

		$.ajax({
			url: ajax_object.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'el_restore_post',
				data: {
					post_id: postId,
					nonce: nonce
				}
			},
			success: function(response) {
				if (response.success === true || (response.data && response.data.status === 'success')) {
					$row.fadeOut(300, function() {
						$(this).remove();

						// Si plus aucune activité, recharger la page
						if ($('.el-archived-events tbody tr').length === 0) {
							location.reload();
						}
					});

					if (typeof ToastNotification !== 'undefined') {
						ToastNotification.success('Activité restaurée avec succès !');
					}
				} else {
					var errorMessage = (response.data && response.data.message) ? response.data.message : 'Erreur lors de la restauration.';
					if (typeof ToastNotification !== 'undefined') {
						ToastNotification.error(errorMessage);
					} else {
						alert(errorMessage);
					}
					$btn.prop('disabled', false).removeClass('loading');
				}
			},
			error: function() {
				if (typeof ToastNotification !== 'undefined') {
					ToastNotification.error('Erreur lors de la restauration.');
				} else {
					alert('Erreur lors de la restauration.');
				}
				$btn.prop('disabled', false).removeClass('loading');
			}
		});
	});
});
</script>
