<?php
if ( !defined( 'ABSPATH' ) ) exit();

$format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );

$listing_type 	= isset( $_GET['listing_type'] ) ? sanitize_text_field( $_GET['listing_type'] ) : 'any';
$order 			= isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
$orderby 		= isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'ID';
$cat_selected 	= isset( $_GET['cat'] ) ? $_GET['cat'] : '';
$name_event   	= isset( $_GET['name_event'] ) ? $_GET['name_event'] : '';
$user_id 		= wp_get_current_user()->ID;
$paged 			= ( get_query_var('paged') ) ? get_query_var('paged') : 1;

$listing_events = get_vendor_events( $order, $orderby, $listing_type, $user_id, $paged, $name_event, $cat_selected );

// Initialiser EL_Event_Coorganisation
if ( class_exists( 'EL_Event_Coorganisation' ) ) {
	EL_Event_Coorganisation::init();
}

// Instance Analytics
$analytics = new EL_Analytics();

?>
<tbody class="event_body">

<?php if( $listing_events->have_posts() ) : foreach( $listing_events->posts as $post_id ) :

	setup_postdata( $post_id );
	$_prefix = OVA_METABOX_EVENT;

	// Données de base
	$status_post = get_post_status( $post_id );
	$event_title = get_the_title( $post_id );
	$edit_url = add_query_arg( array( 'vendor' => 'listing-edit', 'id' => $post_id ), get_myaccount_page() );
	$preview_url = get_permalink( $post_id );

	// Image
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : EL_PLUGIN_URI . 'assets/img/placeholder.png';

	// Lieu et Ville
	$venue_name = get_post_meta( $post_id, $_prefix . 'venue', true );
	$city = get_post_meta( $post_id, $_prefix . 'city', true );
	$address = get_post_meta( $post_id, $_prefix . 'address', true );

	// Gérer le cas où address est un tableau
	if ( is_array( $address ) ) {
		$address = isset( $address['address'] ) ? $address['address'] : ( isset( $address[0] ) ? $address[0] : '' );
	}

	$location_display = '';
	if ( !empty( $venue_name ) ) {
		$location_display = is_array( $venue_name ) ? '' : $venue_name;
		if ( !empty( $city ) && !is_array( $city ) ) {
			$location_display .= ', ' . $city;
		}
	} elseif ( !empty( $address ) && is_string( $address ) ) {
		$location_display = $address;
	}

	// Co-organisateurs
	$coorganisers = array();
	if ( class_exists( 'EL_Event_Coorganisation' ) && class_exists( 'EL_Coorg_Helpers' ) ) {
		$coorg_list = EL_Event_Coorganisation::get_accepted_coorganisers( $post_id );
		if ( !empty( $coorg_list ) ) {
			foreach ( $coorg_list as $coorg ) {
				$coorganisers[] = EL_Coorg_Helpers::get_organisation_name( $coorg->organisation_coorganisatrice_id );
			}
		}
	}
	$coorg_display = !empty( $coorganisers ) ? implode( ', ', $coorganisers ) : __( 'aucun', 'eventlist' );

	// Taxonomies (Catégorie, Type, Public)
	$categories = get_the_terms( $post_id, 'event_cat' );
	$types = get_the_terms( $post_id, 'event_tag' );
	$publics = get_the_terms( $post_id, 'event_public' );

	// Créneaux - Récupérer les 3 prochaines dates
	$all_calendars = get_arr_list_calendar_by_id_event( $post_id );
	$upcoming_dates = array();
	$now = time();

	if ( !empty( $all_calendars ) && is_array( $all_calendars ) ) {
		foreach ( $all_calendars as $cal ) {
			$cal_date = isset( $cal['date'] ) ? strtotime( $cal['date'] ) : 0;
			$cal_start_time = isset( $cal['start_time'] ) ? $cal['start_time'] : '';
			$cal_end_time = isset( $cal['end_time'] ) ? $cal['end_time'] : '';

			// Ne garder que les dates futures
			if ( $cal_date >= strtotime( 'today' ) ) {
				$upcoming_dates[] = array(
					'date' => $cal_date,
					'start_time' => $cal_start_time,
					'end_time' => $cal_end_time,
					'raw_date' => isset( $cal['date'] ) ? $cal['date'] : '',
				);
			}
		}
		// Trier par date
		usort( $upcoming_dates, function( $a, $b ) {
			return $a['date'] - $b['date'];
		});
	}

	$total_upcoming = count( $upcoming_dates );
	$display_dates = array_slice( $upcoming_dates, 0, 3 );
	$remaining_dates = $total_upcoming - 3;

	// Billetterie
	$ticket_type = get_post_meta( $post_id, $_prefix . 'ticket_type', true ); // gratuit, payant, ou vide
	$tickets = get_post_meta( $post_id, $_prefix . 'ticket', true );
	$min_price = null;

	if ( !empty( $tickets ) && is_array( $tickets ) ) {
		foreach ( $tickets as $ticket ) {
			$price = isset( $ticket['price'] ) ? floatval( $ticket['price'] ) : 0;
			if ( $min_price === null || $price < $min_price ) {
				$min_price = $price;
			}
		}
	}

	// Vues (Analytics)
	$event_analytics = $analytics->get_event_analytics( $post_id );
	$total_views = isset( $event_analytics['total_views'] ) ? intval( $event_analytics['total_views'] ) : 0;

	// Vérifier s'il y a des interactions (réservations)
	$has_bookings = false;
	$booking_count = 0;
	if ( class_exists( 'EL_Booking' ) ) {
		$booking_count = EL_Booking::instance()->get_number_booking_id_event( $post_id );
		$has_bookings = $booking_count > 0;
	}

	// Statut en ligne / hors ligne
	$is_online = ( $status_post === 'publish' );

	?>

	<tr data-event-id="<?php echo esc_attr( $post_id ); ?>" data-event-title="<?php echo esc_attr( $event_title ); ?>" data-has-interactions="<?php echo $has_bookings || $total_views > 0 ? '1' : '0'; ?>" data-is-online="<?php echo $is_online ? '1' : '0'; ?>">

		<!-- Checkbox -->
		<td class="col-checkbox">
			<div class="check_event">
				<label for="<?php echo esc_attr( 'select-'.$post_id ); ?>" class="el_input_checkbox">
					<input id="<?php echo esc_attr( 'select-'.$post_id ); ?>" type="checkbox" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
					<span class="checkmark"></span>
				</label>
			</div>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
		</td>

		<!-- Image -->
		<td class="col-image">
			<div class="event-thumbnail">
				<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $event_title ); ?>">
			</div>
		</td>

		<!-- Événement -->
		<td class="col-event">
			<div class="event-info">
				<!-- Titre (lien vers edit) -->
				<h4 class="event-title">
					<a href="<?php echo esc_url( $edit_url ); ?>">
						<?php echo esc_html( $event_title ); ?>
					</a>
				</h4>

				<!-- Lieu + Ville -->
				<?php if ( !empty( $location_display ) ) : ?>
				<div class="event-location">
					<i class="icon_pin_alt"></i>
					<span><?php echo esc_html( $location_display ); ?></span>
				</div>
				<?php endif; ?>

				<!-- Co-organisateur -->
				<div class="event-coorg">
					<span class="coorg-label"><?php esc_html_e( 'Co-organisateur :', 'eventlist' ); ?></span>
					<span class="coorg-value"><?php echo esc_html( $coorg_display ); ?></span>
				</div>

				<!-- Tags (Catégorie, Type, Public) -->
				<div class="event-tags">
					<?php if ( !empty( $categories ) && !is_wp_error( $categories ) ) : ?>
						<?php foreach ( $categories as $cat ) : ?>
							<span class="tag tag-category"><?php echo esc_html( $cat->name ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ( !empty( $types ) && !is_wp_error( $types ) ) : ?>
						<?php foreach ( $types as $type ) : ?>
							<span class="tag tag-type"><?php echo esc_html( $type->name ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ( !empty( $publics ) && !is_wp_error( $publics ) ) : ?>
						<?php foreach ( $publics as $public ) : ?>
							<span class="tag tag-public"><?php echo esc_html( $public->name ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<!-- Statut + Bouton Prévisualiser -->
				<div class="event-status-actions">
					<span class="status-badge <?php echo $is_online ? 'status-online' : 'status-offline'; ?>">
						<?php echo $is_online ? esc_html__( 'En ligne', 'eventlist' ) : esc_html__( 'Hors ligne', 'eventlist' ); ?>
					</span>
					<a href="<?php echo esc_url( $preview_url ); ?>" target="_blank" class="btn-preview">
						<?php esc_html_e( 'Prévisualiser', 'eventlist' ); ?>
					</a>
				</div>
			</div>
		</td>

		<!-- Prochaines dates -->
		<td class="col-dates">
			<div class="upcoming-dates">
				<?php if ( !empty( $display_dates ) ) : ?>
					<?php foreach ( $display_dates as $date_item ) : ?>
						<div class="date-item">
							<span class="date-day"><?php echo date_i18n( 'l d M y', $date_item['date'] ); ?></span>
							<span class="date-time"><?php echo esc_html( $date_item['start_time'] ); ?></span>
						</div>
					<?php endforeach; ?>

					<?php if ( $remaining_dates > 0 ) : ?>
						<button type="button" class="btn-more-dates" data-event-id="<?php echo esc_attr( $post_id ); ?>">
							<?php printf( esc_html__( '%d autres dates', 'eventlist' ), $remaining_dates ); ?>
						</button>

						<!-- Données cachées pour la modale -->
						<script type="application/json" class="all-dates-data">
							<?php echo json_encode( $upcoming_dates ); ?>
						</script>
					<?php endif; ?>
				<?php else : ?>
					<span class="no-dates"><?php esc_html_e( 'Aucune date à venir', 'eventlist' ); ?></span>
				<?php endif; ?>
			</div>
		</td>

		<!-- Réservations -->
		<td class="col-reservations">
			<div class="reservations-info">
				<!-- Type de billetterie -->
				<div class="ticket-type-display">
					<?php if ( $ticket_type === 'gratuit' || ( $min_price !== null && $min_price == 0 ) ) : ?>
						<span class="ticket-free"><?php esc_html_e( 'Gratuit', 'eventlist' ); ?></span>
					<?php elseif ( $ticket_type === 'payant' || ( $min_price !== null && $min_price > 0 ) ) : ?>
						<span class="ticket-paid">
							<?php printf( esc_html__( 'À partir de %s €', 'eventlist' ), number_format( $min_price, 2, ',', ' ' ) ); ?>
						</span>
					<?php endif; ?>
				</div>

				<!-- Vues -->
				<div class="stats-item">
					<span class="stats-label"><?php esc_html_e( 'Vues :', 'eventlist' ); ?></span>
					<span class="stats-value"><?php echo number_format( $total_views, 0, ',', ' ' ); ?></span>
				</div>

				<!-- Favoris (prochainement) -->
				<div class="stats-item stats-coming-soon">
					<span class="stats-label"><?php esc_html_e( 'Favoris :', 'eventlist' ); ?></span>
					<span class="stats-value"><?php esc_html_e( 'prochainement', 'eventlist' ); ?></span>
				</div>
			</div>
		</td>

		<!-- Actions -->
		<td class="col-actions">
			<div class="action-buttons">
				<!-- Modifier -->
				<a href="<?php echo esc_url( $edit_url ); ?>" class="btn-action btn-edit">
					<?php esc_html_e( 'Modifier', 'eventlist' ); ?>
				</a>

				<!-- Dupliquer -->
				<button type="button" class="btn-action btn-duplicate" data-post-id="<?php echo esc_attr( $post_id ); ?>">
					<?php esc_html_e( 'Dupliquer', 'eventlist' ); ?>
				</button>
				<?php wp_nonce_field( 'el_duplicate_post_nonce', 'el_duplicate_post_nonce_' . $post_id ); ?>

				<!-- Supprimer -->
				<button type="button" class="btn-action btn-delete" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-event-title="<?php echo esc_attr( $event_title ); ?>" data-has-interactions="<?php echo $has_bookings || $total_views > 0 ? '1' : '0'; ?>" data-is-online="<?php echo $is_online ? '1' : '0'; ?>">
					<?php esc_html_e( 'Supprimer', 'eventlist' ); ?>
				</button>
				<?php wp_nonce_field( 'el_delete_post_nonce', 'el_delete_post_nonce_' . $post_id ); ?>
				<?php wp_nonce_field( 'el_pending_post_nonce', 'el_pending_post_nonce_' . $post_id ); ?>
				<?php wp_nonce_field( 'el_archive_post_nonce', 'el_archive_post_nonce_' . $post_id ); ?>
			</div>
		</td>

	</tr>

<?php endforeach; else : ?>
	<tr>
		<td colspan="6" class="no-events">
			<?php esc_html_e( 'Aucun événement trouvé', 'eventlist' ); ?>
		</td>
	</tr>
<?php endif; wp_reset_postdata(); ?>

</tbody>

</table>

<?php
$total = $listing_events->max_num_pages;
if ( $total > 1 ) : ?>
	<div class="my_list_pagination">
		<?php echo pagination_vendor( $total ); ?>
	</div>
<?php endif; ?>

<!-- Modale pour afficher toutes les dates -->
<div id="el-dates-modal" class="el-modal" style="display: none;">
	<div class="el-modal-overlay"></div>
	<div class="el-modal-content">
		<div class="el-modal-header">
			<h3><?php esc_html_e( 'Toutes les dates', 'eventlist' ); ?></h3>
			<button type="button" class="el-modal-close">&times;</button>
		</div>
		<div class="el-modal-body">
			<div class="dates-list"></div>
		</div>
	</div>
</div>

<!-- Modale de confirmation de suppression -->
<div id="el-delete-modal" class="el-modal" style="display: none;">
	<div class="el-modal-overlay"></div>
	<div class="el-modal-content">
		<div class="el-modal-header">
			<h3><?php esc_html_e( 'Confirmation', 'eventlist' ); ?></h3>
			<button type="button" class="el-modal-close">&times;</button>
		</div>
		<div class="el-modal-body">
			<p class="delete-message"></p>
		</div>
		<div class="el-modal-footer">
			<button type="button" class="btn-modal btn-modal-cancel"><?php esc_html_e( 'Non', 'eventlist' ); ?></button>
			<button type="button" class="btn-modal btn-modal-confirm"><?php esc_html_e( 'Oui', 'eventlist' ); ?></button>
		</div>
	</div>
</div>
