<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="event_entry ">
	<div class="event_item type4">
		<?php if ( apply_filters( 'el_ft_show_remove_btn', false ) ): ?>
			<?php do_action( 'el_loop_event_remove', $args ); ?>
		<?php endif; ?>
		<div class="event_thumbnail">
			<?php 
				/**
				 * Display thumbnail
				 * Hook: el_loop_event_thumbnail
				 * @hookeds: el_loop_event_thumbnail - 10
				 */
				do_action( 'el_loop_event_thumbnail', $args );

				/**
				 * Display share
				 * Hook: el_loop_event_share
				 * @hookeds: el_loop_event_share
				 */
				do_action( 'el_loop_event_share', $args );

				/**
				 * Display favourite
				 * Hook: el_loop_event_favourite
				 * @hookeds: el_loop_event_favourite
				 */
				do_action( 'el_loop_event_favourite', $args );

			 ?>
		</div>

		<div class="event_detail">
			<div class="el-wp-content">
				<div class="date-event">
					<?php 
					/**
					 * Display date
					 * Hook: el_loop_event_date_4
					 * @hooked: el_loop_event_date_4
					 */
					do_action( 'el_loop_event_date_4', $args );
					?>
				</div>
				<div class="content-event">
					<?php 
					/**
					 * Display Title
					 * Hook: el_loop_event_title
					 * @hooked: el_loop_event_title
					 */
					do_action( 'el_loop_event_title', $args );

					?>

					 <div class="event-location-time">
					 	
						<?php

						/**
						 * Display time event
						 * Hook: el_loop_event_time
						 * @Hooked: el_loop_event_time
						 */
						do_action( 'el_loop_event_time', $args );

						/**
						 * Display location event
						 * Hook: el_loop_event_location
						 * @Hooked: el_loop_event_location
						 */
						do_action( 'el_loop_event_location', $args );

						?>

					</div>
				</div>
			</div>	

		</div>

	</div>
	
</div>



