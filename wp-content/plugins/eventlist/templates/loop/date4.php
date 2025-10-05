<?php if ( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_id();
$el_get_event_w_d_m = el_get_event_w_d_m( $event_id, 'short' );

if ( $el_get_event_w_d_m ) { ?>
	
	<div class="date-top">
		
			<div class="wp-date">
				<p class="month"><?php echo esc_html($el_get_event_w_d_m['month']); ?></p>
				<p class="day"><?php echo esc_html($el_get_event_w_d_m['day']); ?></p>

				
			</div>
		
	</div>
<?php } ?>