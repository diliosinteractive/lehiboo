<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="event_entry ">
	<div class="event_item type2">
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
				 * Display image author
				 * Hook: el_loop_event_author
				 * @hookeds: el_loop_event_author
				 */
				do_action( 'el_loop_event_author', $args );


				/**
				 * Display favourite
				 * Hook: el_loop_event_favourite
				 * @hookeds: el_loop_event_favourite
				 */
				do_action( 'el_loop_event_favourite', $args );

			 ?>
		</div>

		<div class="event_detail">
			<div class="event-meta">
				<?php
				/**
				 * Display Category
				 * Hook: el_loop_event_cat
				 * @hooked: el_loop_event_cat
				 */
				do_action( 'el_loop_event_cat', $args );

				/**
				 * Display Price
				 * Hook: el_loop_event_price
				 * @hooked: el_loop_event_price
				 */
				do_action( 'el_loop_event_price', $args );
				?>
			</div>
			
			<?php 

				/**
				 * Display Title
				 * Hook: el_loop_event_title
				 * @hooked: el_loop_event_title
				 */
				do_action( 'el_loop_event_title', $args );

				?>

				<?php
				/**
				 * Display excerpt
				 * Hook: el_loop_event_excerpt
				 */
				do_action( 'el_loop_event_excerpt', $args );

			 ?>

			 <div class="event-location-time">
			 	
				<?php
				/**
				 * Display location event
				 * Hook: el_loop_event_location
				 * @Hooked: el_loop_event_location
				 */
				do_action( 'el_loop_event_location', $args );

				/**
				 * Display time event
				 * Hook: el_loop_event_time
				 * @Hooked: el_loop_event_time
				 */
				do_action( 'el_loop_event_time', $args );

				?>

			 </div>

			 <div class="meta-footer">
			 	
			 	<?php


			 	/**
				 * Display Ratting
				 * Hook: el_loop_event_ratting
				 * @hooked: el_loop_event_ratting
				 */
				do_action( 'el_loop_event_ratting', $args );

			 	
			 	/**
			 	 * Display button readmore event
			 	 * Hook: el_loop_event_buttun
			 	 * @Hooked: el_loop_event_button
			 	 */
			 	do_action( 'el_loop_event_button', $args );

			 	
			 	?>

			 </div>

		</div>

	</div>
	
</div>



