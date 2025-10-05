<?php if( ! defined( 'ABSPATH' ) ) exit();
	
	$display_price = apply_filters( 'el_event_display_price_opt', EL()->options->event->get('display_price_opt', 'min'), $args );
	
	$price = get_price_ticket_by_id_event( array( 'id_event' => get_the_ID(), 'display_price' => $display_price ) );
?>
<span class="event_loop_price second_font"><?php echo esc_html( $price ); ?></span>