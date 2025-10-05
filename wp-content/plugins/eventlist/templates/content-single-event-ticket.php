<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<article id="event_<?php the_ID(); ?> " <?php post_class( 'event_single' ); ?>>
	
	<div class="event_entry">

		<div class="event_thumbnail">
			<?php 
				/**
				 * Display thumbnail
				 * Hooked: el_single_event_thumbnail
				 * @hook: el_single_event_thumbnail - 10
				 */
				do_action( 'el_single_event_thumbnail' );

			 ?>
		</div>

		<div class="event_detail">
			<div class="single-event-wp-content">
				<div class="single-event-content">
					<?php 

						/**
						 * Hook: el_single_event_title
						 * @hooked: el_single_event_title
						 */
						do_action( 'el_single_event_title' );

						/**
						 * Hook: el_single_event_ticket_type_ticket
						 * @hooked: el_single_event_ticket_type_ticket
						 */
						do_action( 'el_single_event_ticket_type_ticket' );


					?>
				</div><!-- end single-event-content -->
				<div class="single-event-sidebar">
					<?php
						/**
						 * Hook: el_single_event_ticket_booking_info - 10
						 * @hooked:  el_single_event_ticket_booking_info - 10
						 */
						do_action( 'el_single_event_ticket_booking_info' );

						/**
						 * Hook: el_single_event_ticket_button_discount - 10
						 * @hooked:  el_single_event_ticket_button_discount - 10
						 */
						do_action( 'el_single_event_ticket_button_discount' );

						/**
						 * Hook: el_single_event_ticket_button_next_step - 10
						 * @hooked:  el_single_event_ticket_button_next_step - 10
						 */
						do_action( 'el_single_event_ticket_button_next_step' );
					?>
				</div>
			</div><!-- end wrapper content -->
		</div>
	</div>
</article>
