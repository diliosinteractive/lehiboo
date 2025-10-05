
<?php if( ! defined( 'ABSPATH' ) ) exit();

$include_cat 	= $args['include_cat'];
$column 		= $args['column'];
$type_event 	= $args['type_event'];
$type_event 	= ! empty($type_event) ? $type_event : 'type1';

$terms 	= get_term_ids_by_cat_include( $include_cat );
$events = get_list_event_grid_elementor( $args );

?>
<div class="ova-event-grid-no-filter">
	<div class="event_archive <?php echo esc_attr( $type_event ); ?> <?php echo esc_attr( $column ); ?>" >
		<?php
		if( $events->have_posts() ) : 
			while( $events->have_posts() ) : $events->the_post();
				el_get_template_part( 'content', 'event-'.$type_event, $args );
			endwhile; wp_reset_postdata();
		else :
			?>
			<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
			<?php 
		endif;
		?>
	</div>
</div>