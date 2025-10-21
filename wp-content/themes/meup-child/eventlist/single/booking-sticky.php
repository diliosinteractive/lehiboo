<?php
/**
 * Template Part: Booking Sticky Widget
 *
 * Widget de réservation sticky sur desktop:
 * - Prix par personne
 * - Sélecteur de date
 * - Nombre d'invités
 * - Total calculé
 * - CTA "Réserver"
 * - Lien "Contacter l'organisateur"
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;
$event_id = get_the_ID();

// Données de base
$list_type_ticket = get_post_meta( $event_id, OVA_METABOX_EVENT . 'ticket', true );
$seat_option = get_post_meta( $event_id, OVA_METABOX_EVENT . 'seat_option', true );
$start_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'start_date_str', true );
$end_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'end_date_str', true );
$ticket_link = get_post_meta( $event_id, OVA_METABOX_EVENT . 'ticket_link', true );

// Prix (récupérer le prix du premier ticket)
$ticket_price = 0;
$currency = el_get_currency_symbol();

if( !empty($list_type_ticket) && is_array($list_type_ticket) ) {
	$first_ticket = $list_type_ticket[0];
	$ticket_price = isset($first_ticket['ticket_price']) ? floatval($first_ticket['ticket_price']) : 0;
}

// Vérifier disponibilité
$is_available = false;
$booking_url = '#';
$current_time = current_time( 'timestamp' );

if ( $event_id ) {
	$timezone = get_post_meta( $event_id, OVA_METABOX_EVENT . 'time_zone', true );

	if ( $timezone ) {
		$tz_string = el_get_timezone_string( $timezone );
		$datetime = new DateTime('now', new DateTimeZone( $tz_string ) );
		$time_now = $datetime->format('Y-m-d H:i');

		if ( strtotime( $time_now ) ) {
			$current_time = strtotime( $time_now );
		}
	}
}

if ( (!empty($list_type_ticket) && !empty($start_date_str)) || ($seat_option === 'map') ) {
	if ( (int)$end_date_str > $current_time ) {
		$is_available = true;

		// Déterminer l'URL de réservation
		$option_calendar = get_post_meta( $event_id, OVA_METABOX_EVENT . 'option_calendar', true );

		if( $option_calendar == 'manual' ) {
			$list_calendar_ticket = get_post_meta( $event_id, OVA_METABOX_EVENT . 'calendar', true );

			if ( !empty($list_calendar_ticket) && is_array($list_calendar_ticket) ) {
				foreach ( $list_calendar_ticket as $ticket ) {
					$start_time = isset( $ticket['date'] ) ? el_get_time_int_by_date_and_hour( $ticket['date'], $ticket['start_time'] ) : '';
					$end_time = isset( $ticket['end_date'] ) ? el_get_time_int_by_date_and_hour( $ticket['end_date'], $ticket['end_time'] ) : '';
					$number_time = isset( $ticket['book_before_minutes'] ) ? floatval( $ticket['book_before_minutes'] )*60 : 0;

					if ( el_validate_selling_ticket( $start_time, $end_time, $number_time, $event_id ) ) {
						$check_ticket_selling = $event->check_ticket_in_event_selling( $event_id );

						if ( $check_ticket_selling ) {
							$booking_url = add_query_arg( array( 'ide' => $event_id, 'idcal' => $ticket['calendar_id'] ), get_cart_page() );
							break;
						}
					}
				}
			}
		}
	}
}

// Lien externe ?
$is_external = ( $ticket_link == 'ticket_external_link' );
$external_link = get_post_meta( $event_id, OVA_METABOX_EVENT . 'ticket_external_link', true );

if( $is_external && $external_link ) {
	$booking_url = $external_link;
}

// Organisateur
$author_id = get_the_author_meta('ID');
$author_name = get_the_author_meta('display_name', $author_id);
$author_url = get_author_posts_url( $author_id );
?>

<div class="event_booking_sticky" id="booking_sticky_widget" data-price="<?php echo esc_attr( $ticket_price ); ?>">

	<!-- Prix -->
	<div class="booking_price_section">
		<div class="price_display">
			<?php if( $ticket_price > 0 ) : ?>
				<span class="price_amount"><?php echo esc_html( $currency . number_format($ticket_price, 2) ); ?></span>
				<span class="price_label"> / <?php esc_html_e( 'personne', 'eventlist' ); ?></span>
			<?php else : ?>
				<span class="price_amount"><?php esc_html_e( 'Gratuit', 'eventlist' ); ?></span>
			<?php endif; ?>
		</div>

		<!-- Note (si disponible) -->
		<?php if( comments_open( $event_id ) ) :
			$comments_count = get_comments_number( $event_id );
			if( $comments_count > 0 ) :
		?>
			<div class="booking_rating">
				<i class="icon_star"></i>
				<span class="rating_value">5.0</span>
				<span class="rating_count">(<?php echo esc_html( $comments_count ); ?> <?php esc_html_e( 'avis', 'eventlist' ); ?>)</span>
			</div>
		<?php endif; endif; ?>
	</div>

	<!-- Formulaire de réservation -->
	<div class="booking_form_section">

		<!-- Sélecteur de date -->
		<div class="booking_field booking_date_field">
			<label for="booking_date_select"><?php esc_html_e( 'Date', 'eventlist' ); ?></label>
			<div class="booking_date_display" id="booking_date_display">
				<i class="icon_calendar"></i>
				<span><?php esc_html_e( 'Sélectionner une date', 'eventlist' ); ?></span>
			</div>
			<a href="#booking_event" class="btn_select_date">
				<?php esc_html_e( 'Voir le calendrier', 'eventlist' ); ?>
			</a>
		</div>

		<!-- Nombre d'invités -->
		<div class="booking_field booking_guests_field">
			<label for="booking_guests"><?php esc_html_e( 'Invités', 'eventlist' ); ?></label>
			<div class="guests_controls">
				<button type="button" class="guests_btn guests_minus" data-action="decrease">
					<i class="icon_minus"></i>
				</button>
				<input type="number"
				       id="booking_guests"
				       class="guests_input"
				       value="1"
				       min="1"
				       max="20"
				       readonly />
				<button type="button" class="guests_btn guests_plus" data-action="increase">
					<i class="icon_plus"></i>
				</button>
			</div>
		</div>

		<!-- Total calculé -->
		<?php if( $ticket_price > 0 ) : ?>
			<div class="booking_total_section">
				<div class="total_line">
					<span class="total_label">
						<?php echo esc_html( $currency . number_format($ticket_price, 2) ); ?> × <span class="guests_count">1</span> <?php esc_html_e( 'invité(s)', 'eventlist' ); ?>
					</span>
					<span class="total_amount" id="booking_total_amount">
						<?php echo esc_html( $currency . number_format($ticket_price, 2) ); ?>
					</span>
				</div>
			</div>
		<?php endif; ?>

		<!-- CTA Réserver -->
		<?php if( $is_available ) : ?>
			<a href="<?php echo esc_url( $booking_url ); ?>"
			   class="btn_book_now <?php echo $is_external ? 'external_link' : ''; ?>"
			   <?php echo $is_external ? 'target="_blank"' : ''; ?>>
				<?php esc_html_e( 'Réserver maintenant', 'eventlist' ); ?>
			</a>
		<?php else : ?>
			<button class="btn_book_now disabled" disabled>
				<?php esc_html_e( 'Complet', 'eventlist' ); ?>
			</button>
		<?php endif; ?>

		<!-- Lien contact organisateur -->
		<div class="booking_contact_organizer">
			<a href="<?php echo esc_url( $author_url ); ?>" class="contact_link">
				<i class="icon_mail_alt"></i>
				<?php esc_html_e( 'Contacter l\'organisateur', 'eventlist' ); ?>
			</a>
		</div>

	</div>

	<!-- Message d'information -->
	<div class="booking_info_message">
		<i class="icon_info"></i>
		<span><?php esc_html_e( 'Vous ne serez débité qu\'après confirmation', 'eventlist' ); ?></span>
	</div>

</div>
