<?php if( ! defined( 'ABSPATH' ) ) exit();
	$id_cal = isset($_GET['idcal']) ? $_GET['idcal'] : '';
	get_header();
?>

	<?php
		
		/**
		 * Hook: el_before_main_content
		 * @hooked: el_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked el_breadcrumb - 20
		 */
		remove_action( 'el_before_main_content','el_output_content_wrapper' );
		do_action( 'el_before_main_content' );
	?>	

			<?php if( have_posts() ): ?>
						
				<?php while ( have_posts() ) : the_post(); ?>

					<?php 
					if ( $id_cal !== '' ) {
						el_get_template_part( 'content', 'single-event-ticket' ); 
					} else {
						el_get_template_part( 'content', 'single-event' ); 
					}
					
					?>

				<?php endwhile; // end of the loop. ?>

			<?php endif; ?>

<?php
	/**
	 * Hook: el_after_main_content.
	 *
	 * @hooked el_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	remove_action( 'el_before_main_content','el_output_content_wrapper' );
	do_action( 'el_after_main_content' );

?>

<?php

get_footer();
