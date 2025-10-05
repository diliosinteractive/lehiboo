<?php if ( ! defined( 'ABSPATH' ) ) exit(); ?>

<?php
$event_id = get_the_id();
$el_get_event_w_d_m = el_get_event_w_d_m( $event_id, 'full' );

if ( $el_get_event_w_d_m) { ?>
	
	<div class="date-top">
		<div class="date-start">
			<div class="wp-date">
				<div class="month"><?php echo esc_html($el_get_event_w_d_m['month']) ?></div>
				<div class="day-week">
					<span class="day"><?php echo esc_html($el_get_event_w_d_m['day']) ?></span>
					<span class="weekday"><?php echo esc_html($el_get_event_w_d_m['weekday']) ?></span>
				</div>
			</div>
		</div>
	</div>

<?php } ?>