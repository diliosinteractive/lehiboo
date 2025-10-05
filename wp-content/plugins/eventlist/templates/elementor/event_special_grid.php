
<?php if( ! defined( 'ABSPATH' ) ) exit();

$column 		= isset( $args['column'] ) ? $args['column'] : 'three_column';
$type_event 	= isset( $args['type_event'] ) ? $args['type_event'] : 'type1';
$total_count 	= isset( $args['total_count'] ) ? $args['total_count'] : '5';

$events = EL_Event::el_get_special_events( $args );

?>
	
<div class="ova-event-special-grid">


	<div class="event_archive <?php echo esc_attr( $type_event ); ?> <?php echo esc_attr( $column ); ?>" >

		<div class="wrap_loader">
			<svg class="loader" width="50" height="50">
				<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
				<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
			</svg>
		</div>
		<?php
		if( $events->have_posts() ) :
			while( $events->have_posts() ) : $events->the_post();
				el_get_template_part( 'content', 'event-'.$type_event, $args );
			endwhile; wp_reset_postdata();
		else :
			?>
			<h3 class="event-notfound"><?php esc_html_e( 'Event not found', 'eventlist' ); ?></h3>
			<?php 
		endif;
		?>
	</div>
	
</div>

<div class="ova-event-pagination">
	<?php if ( $events->have_posts() && $events->max_num_pages > 1 ): ?>
		<?php el_get_template( 'pagination-ajax.php', array( 'events' => $events, 'per_page' => $total_count ) ); ?>
	<?php endif; ?>
</div>
