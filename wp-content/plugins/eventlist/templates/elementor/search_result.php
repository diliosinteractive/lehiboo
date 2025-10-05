<?php 

$archive_type 	= isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : EL_Setting::instance()->event->get( 'archive_type', 'type1' );
$archive_column = isset( $args['column'] ) ? sanitize_text_field( $args['column'] ) : EL_Setting::instance()->event->get( 'archive_column', 'two-column' );

$archive_type 	= isset ( $_GET['type_event'] ) ? sanitize_text_field( $_GET['type_event'] ) : $archive_type;

$layout_column 	= isset ( $_GET['layout_event'] ) ? sanitize_text_field( $_GET['layout_event'] ) : $archive_column;

if ( $archive_type === 'type1' ||  $archive_type === 'type2' || $archive_type === 'type3' || $archive_type === 'type4' || $archive_type === 'type5' ) {
	$archive_type = $archive_type;
}

?>

<div class="event_archive <?php echo esc_attr($archive_type . ' '. $archive_column); ?> " >

	<?php

	
	$event = el_search_event($_GET);

	if($event->have_posts() ) {

		while ( $event->have_posts() ) : $event->the_post();

			el_get_template_part( 'content', 'event-'.$archive_type );

		endwhile;

		wp_reset_postdata(); 

		do_action( 'el_after_archive_loop' );

	} else { ?>

		<div class="not_found_event"> <?php esc_html_e( 'Not found event', 'eventlist' ); ?> </div>

	<?php } ?>

</div>

<?php 
$total = $event->max_num_pages;
if ( $total > 1 ) {  ?>
	<div class="my_list_pagination">
		<?php echo wp_kses_post( pagination_vendor($total) ); ?>
	</div>
	<?php } ?>