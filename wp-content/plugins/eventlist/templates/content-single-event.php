<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>

<article id="event_<?php the_ID(); ?> " <?php post_class( 'event_single' ); ?>>
	
		
		<?php
			/**
			 * Display thumbnail
			 * Hooked: el_single_event_banner
			 * @hook: el_single_event_banner - 10
			 */
			//do_action( 'el_single_event_banner' );
		?>
		
		<?php if ( ! post_password_required( get_the_ID() ) ): ?>
			<div class="wrap_event_top">
				<div class="event_top">
					
					<div class="event_info">
						
							<?php 
							/**
							 * Display date
							 * Hook: el_loop_event_date
							 * @hooked: el_loop_event_date
							 */
							do_action( 'el_loop_event_date' );
							?>
						<div class="content-top">
							<?php 
								/**
								 * Hook: el_single_event_title
								 * @hooked: el_single_event_title
								 */
								do_action( 'el_single_event_title' );

								/**
								 * Hook: el_single_event_date
								 * @hooked: el_single_event_date
								 */
								do_action( 'el_single_event_date' );

								/**
								 * Hook: el_single_event_address
								 * @hooked: el_single_event_address
								 */
								do_action( 'el_single_event_address' );
							 ?>
						</div>
					</div>

					<div class="act_gr">

						<?php
							/**
							 * Hook: el_single_act_booking
							 * @hooked: el_single_act_booking
							 */
							do_action( 'el_single_act_booking' );
						?>
						<div class="act_share">
							
							<?php
								/**
								 * Hook: el_single_share_social
								 * @hooked: el_single_share_social
								 */
								do_action( 'el_single_share_social' );
							?>

							<?php
								/**
								 * Hook: el_single_report
								 * @hooked: el_single_report
								 */
								do_action( 'el_single_report' );
							?>

							<?php
								/**
								 * Hook: el_single_calenda_export
								 * @hooked: el_single_calenda_export
								 */
								do_action( 'el_single_calenda_export' );
							?>
							
						</div>

					</div>

				</div>
			</div>

		<?php endif; ?>

		<div class="wrap_main_content">
			<div class="main_content">
				
					<div class="event-content">
						
						<?php
                        /**
                         * Hook: el_single_event_video - 10
                         * @hooked:  el_single_event_video - 10
                         */
                        do_action( 'el_single_event_video' );

                        /**
                         * Hook: el_single_event_gallery - 10
                         * @hooked:  el_single_event_gallery - 10
                         */
                        do_action( 'el_single_event_gallery' );

						/**
							 * Hook: el_single_event_content - 10
							 * @hooked:  el_single_event_content - 10
							 */
						do_action( 'el_single_event_content' ); ?>

						<?php if ( ! post_password_required( get_the_ID() ) ):
						/**
							 * Hook: el_single_event_ticket_info - 10
							 * @hooked:  el_single_event_ticket_info - 10
							 */
						do_action( 'el_single_event_ticket_info' );

						/**
							 * Hook: el_single_event_ticket_calendar - 10
							 * @hooked:  el_single_event_ticket_calendar - 10
							 */
						do_action( 'el_single_event_ticket_calendar' );


						?>

						<?php if( is_singular('event') && comments_open( get_the_ID() ) ) { ?>

							<?php 
							/**
							 * Hook: el_single_event_comment - 10
							 * @hooked:  el_single_event_comment - 10
							 */
							do_action( 'el_single_event_comment' );
							?>
							
						<?php } ?>

					<?php endif; ?>

					</div><!-- end single-event-content -->

					<div class="event-sidebar">
						<?php if ( ! post_password_required( get_the_ID() ) ): ?>

							<div class="event-sidebar-content">

								<?php 
									/**
									 * Hook: el_single_event_policy - 10
									 * @hooked:  el_single_event_policy - 10
									 */
									do_action( 'el_single_event_policy' );	
								?>

                                <?php
                                /**
                                 * Hook: el_author_info - 10
                                 * @hooked:  el_author_info - 10
                                 */
                                do_action( 'el_author_info' );
                                ?>

								<?php 
									/**
									 * Hook: el_single_event_map - 10
									 * @hooked:  el_single_event_map - 10
									 */
									do_action( 'el_single_event_map' );	
								 ?>

								<?php 
									/**
									 * Hook: el_single_event_tag - 10
									 * @hooked:  el_single_event_tag - 10
									 */
									do_action( 'el_single_event_tag' );
								?>

								<?php 
									/**
									 * Hook: el_single_event_taxonomy - 10
									 * @hooked:  el_single_event_taxonomy - 10
									 */
									do_action( 'el_single_event_taxonomy' );	
								 ?>

								<?php if(is_active_sidebar('single-event-sidebar')){ ?>
								        <aside id="event-sidebar" class="event_sidebar">
								        	<div class="content-sidebar">
								        		<?php  dynamic_sidebar('single-event-sidebar'); ?>
								        	</div>
								        </aside>
								<?php } ?>

							</div>

						<?php endif; ?>

					</div>
				<!-- </div> -->
				<!-- end wrapper content -->
				
			</div>
			<?php if ( ! post_password_required( get_the_ID() ) ):
			
		
				/**
				 * Hook: el_single_event_related - 10
				 * @hooked:  el_single_event_related - 10
				 */
				do_action('el_single_event_related');
		
			

			endif; ?>

		</div>
	
</article>
