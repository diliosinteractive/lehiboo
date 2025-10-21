<?php
/**
 * Template Part: Mobile Floating CTA
 *
 * Bouton de réservation fixe en bas d'écran (mobile uniquement)
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

global $event;
$event_id = get_the_ID();

// Récupérer le prix
$list_type_ticket = get_post_meta( $event_id, OVA_METABOX_EVENT . 'ticket', true );
$ticket_price = 0;
$currency = el_get_currency_symbol();

if( !empty($list_type_ticket) && is_array($list_type_ticket) ) {
	$first_ticket = $list_type_ticket[0];
	$ticket_price = isset($first_ticket['ticket_price']) ? floatval($first_ticket['ticket_price']) : 0;
}

// Vérifier disponibilité
$start_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'start_date_str', true );
$end_date_str = get_post_meta( $event_id, OVA_METABOX_EVENT . 'end_date_str', true );
$current_time = current_time( 'timestamp' );

$is_available = false;

if ( (!empty($list_type_ticket) && !empty($start_date_str)) ) {
	if ( (int)$end_date_str > $current_time ) {
		$is_available = true;
	}
}
?>

<div class="event_mobile_cta" id="mobile_booking_cta">
	<div class="mobile_cta_inner">

		<!-- Prix -->
		<div class="mobile_cta_price">
			<?php if( $ticket_price > 0 ) : ?>
				<span class="price_amount"><?php echo esc_html( $currency . number_format($ticket_price, 2) ); ?></span>
				<span class="price_label"> / <?php esc_html_e( 'pers.', 'eventlist' ); ?></span>
			<?php else : ?>
				<span class="price_amount"><?php esc_html_e( 'Gratuit', 'eventlist' ); ?></span>
			<?php endif; ?>

			<!-- Note (si disponible) -->
			<?php
			$comments_count = get_comments_number( $event_id );
			if( $comments_count > 0 ) :
			?>
				<span class="mobile_rating">
					<i class="icon_star"></i> 5.0 (<?php echo esc_html( $comments_count ); ?>)
				</span>
			<?php endif; ?>
		</div>

		<!-- Bouton -->
		<div class="mobile_cta_button">
			<?php if( $is_available ) : ?>
				<a href="#booking_event" class="btn_book_mobile" data-scroll-to="#booking_event">
					<?php esc_html_e( 'Réserver', 'eventlist' ); ?>
				</a>
			<?php else : ?>
				<button class="btn_book_mobile disabled" disabled>
					<?php esc_html_e( 'Complet', 'eventlist' ); ?>
				</button>
			<?php endif; ?>
		</div>

	</div>
</div>
