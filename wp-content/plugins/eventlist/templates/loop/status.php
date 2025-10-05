<?php if ( ! defined( 'ABSPATH' ) ) exit(); ?>
<span class="event-status">
	<?php
	global $event;
	$status = $event->get_status_event();
	?>
	<span class="icon"><i class="fas fa-check"></i></span>
	<?php echo wp_kses_post( $status ); ?>
</span>