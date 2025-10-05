<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="event_date">
	<?php global $event;  ?>
	<?php if( $event->get_event_date() ) : ?>
		<i class="icon_clock_alt"></i>
	<?php endif ?>
	<div class="wp-time-top">
		<?php echo $event->get_event_date( 'full_format' ); ?>
	</div>
</div>