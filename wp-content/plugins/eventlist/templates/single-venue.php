<?php if( ! defined( 'ABSPATH' ) ) exit();
get_header();


$archive_type = EL_Setting::instance()->event->get( 'archive_type' );
$archive_column = EL_Setting::instance()->event->get( 'archive_column' );

$archive_type_get_url = isset ( $_GET['type_event'] ) ? $_GET['type_event'] : '';
$layout_column = isset ( $_GET['layout_event'] ) ? $_GET['layout_event'] : $archive_column;

if ( $archive_type_get_url == 'type1' ||  $archive_type_get_url == 'type2' ) {
	$archive_type = $archive_type_get_url;
}

$venue = get_the_title();
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$events = get_list_event_by_title_venue( $venue,  $paged );

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
		remove_action( 'el_taxonomy_archive_description','el_archive_description' );
		do_action( 'el_archive_description' );
?>

<?php if( $events->have_posts() ): ?>

	<?php
		/**
		 * Hook: el_before_archive_loop
		 * @hooked: 
		 */
		do_action( 'el_before_archive_loop' );
	?>

	<div id="el_main_content">
							
		<div class="event_archive <?php echo esc_attr( $archive_type ); ?> <?php echo esc_attr( $layout_column ); ?>">
			<?php while ( $events->have_posts() ) : $events->the_post(); ?>
	
				<?php el_get_template_part( 'content', 'event-'.sanitize_file_name( $archive_type ) ); ?>

			<?php endwhile; wp_reset_query(); // end of the loop. ?>
			
		</div>

	</div>
	<?php 
		$total = $events->max_num_pages;
		if ( $total > 1 ) {
			echo wp_kses_post( pagination_vendor($total) );
		}
	?>

	<?php
		/**
		 * Hook: el_after_archive_loop.
		 *
		 * @hooked el_pagination - 10
		 */
		do_action( 'el_after_archive_loop' );
	?>
	<?php else: ?>
	
	<h3 class="not-found-event"><?php esc_html_e('Not found events', 'eventlist') ?></h3>

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
?>