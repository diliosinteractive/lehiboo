<?php if( ! defined( 'ABSPATH' ) ) exit();
	get_header();

	$archive_type = EL_Setting::instance()->event->get( 'archive_type', 'type1' );
	$archive_column = EL_Setting::instance()->event->get( 'archive_column', 'two-column' );

	$archive_type = isset ( $_GET['type_event'] ) ? sanitize_text_field( $_GET['type_event'] ) : $archive_type;

	$layout_column = isset ( $_GET['layout_event'] ) ? sanitize_text_field( $_GET['layout_event'] ) : $archive_column;

	if ( $archive_type === 'type1' ||  $archive_type === 'type2' || $archive_type === 'type3' || $archive_type === 'type4' || $archive_type === 'type5' || $archive_type === 'type6' ) {
		$archive_type = $archive_type;
	}
	
?>
	
	<?php
		
		/**
		 * Hook: el_before_main_content
		 * @hooked: el_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked el_breadcrumb - 20
		 */
		do_action( 'el_before_main_content' );
	?>

		<?php
				/**
				 * Hook: el_archive_description
				 * @hooked  el_taxonomy_archive_description - 10
				 * @hooked  el_event_archive_description - 10
				 */
				do_action( 'el_archive_description' );
		?>
		

			<?php if( have_posts() ): ?>

				<?php
					/**
					 * Hook: el_before_archive_loop
					 * @hooked: 
					 */
					do_action( 'el_before_archive_loop' );
				?>
					
						<div id="el_main_content">
							
							<div class="event_archive <?php echo esc_attr( $archive_type ); ?> <?php echo esc_attr( $layout_column ); ?>">

								<?php while ( have_posts() ) : the_post(); ?>
						
									<?php el_get_template_part( 'content', 'event-'.sanitize_file_name( $archive_type ) ); ?>

								<?php endwhile; wp_reset_query(); // end of the loop. ?>
								
							</div>

						</div>
					
				<?php
					/**
					 * Hook: el_after_archive_loop.
					 *
					 * @hooked el_pagination - 10
					 */
					do_action( 'el_after_archive_loop' );
				?>	
			<?php else : ?>
				<p><?php esc_html_e('Event not found', 'eventlist') ?></p>
			<?php endif; ?>

<?php
	/**
	 * Hook: el_after_main_content.
	 *
	 * @hooked el_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'el_after_main_content' );

?>


<?php

get_footer();
