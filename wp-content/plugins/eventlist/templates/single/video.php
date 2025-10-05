<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<?php
global $event;

?>
<?php if ( ! empty ( $event->get_link_video() ) && $event->get_link_video() != '#' ) : ?>
	<div class="event-video event_section_white">
		<h3 class="second_font heading"><?php esc_html_e("Video", "eventlist") ?></h3>
		<div id="video-event-single">
			<?php $event->get_video_single_event(); ?>
		</div>
	</div>
<?php endif ?>