<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="el_calendar_export">
	<a href="javascript: void()">
		<i class="icon_calendar"></i>
		<?php esc_html_e('Calendar', 'eventlist') ?>
	</a>
	<div class="el_con_calendar_export">
		<?php
			/**
			 * Hook: el_single_add_calendar
			 * @hooked: el_single_add_calendar
			 */
			do_action( 'el_single_add_calendar' );
		?>

		<?php
			/**
			 * Hook: el_single_export_ical
			 * @hooked: el_single_export_ical
			 */
			do_action( 'el_single_export_ical' );
		?>
	</div>
</div>



<?php

?>