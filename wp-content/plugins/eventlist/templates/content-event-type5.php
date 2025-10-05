<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="event_entry">
	<div class="event_item type5">
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
				// do_action( 'el_loop_event_share' );

				/**
				 * Display favourite
				 * Hook: el_loop_event_favourite
				 * @hookeds: el_loop_event_favourite
				 */
				// do_action( 'el_loop_event_favourite' );

			 ?>
		</div>

		<div class="event_detail">
			<?php 
			/**
			 * Display Title
			 * Hook: el_loop_event_title
			 * @hooked: el_loop_event_title
			 */
			do_action( 'el_loop_event_title', $args );

			?>
			<div class="el-wp-content">
				<div class="content-event">
					<div class="ova-price">
						<?php
					 	/**
						 * Display Price
						 * Hook: el_loop_event_price
						 * @hooked: el_loop_event_price
						 */
						do_action( 'el_loop_event_price', $args );
						?>
					</div>
					 <div class="event-location-time">
						<?php

						/**
						 * Display location event
						 * Hook: el_loop_event_location
						 * @Hooked: el_loop_event_location
						 */
						do_action( 'el_loop_event_location', $args );

						/**
						 * Display Category
						 * Hook: el_loop_event_cat
						 * @hooked: el_loop_event_cat
						 */
						do_action( 'el_loop_event_cat', $args );

						?>

					</div>
				</div>
				<div class="date-event">
					<?php 
					/**
					 * Display date
					 * Hook: el_loop_event_date
					 * @hooked: el_loop_event_date
					 */
					do_action( 'el_loop_event_date', $args );
					?>
				</div>
			</div>	

		</div>

	</div>
	
</div>



