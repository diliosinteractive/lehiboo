
<?php if( ! defined( 'ABSPATH' ) ) exit();

$column 			= $args['column'];
$total_post 		= $args['total_count'];
$order 				= $args['order'];
$order_by 			= $args['order_by'];
$show_remove_btn 	= $args['show_remove_btn'];
$type_event 		= $args['type_event'];
$type_event 		= !empty($type_event) ? $type_event : 'type1';
$data_query = array(
	'posts_per_page' 	=> $total_post,
	'order' 			=> $order,
	'orderby' 			=> $order_by,
);
$ova_event_id = 0;
if ( isset( $_COOKIE["ova_event_id"] ) ) {
	$ova_event_id = array_values( $_COOKIE["ova_event_id"] );
}
$events = get_list_event_recent_elementor( $order, $order_by, $total_post , $ova_event_id );
if ( $show_remove_btn == 'yes' ) {
	add_filter( 'el_ft_show_remove_btn', '__return_true' );
}

?>
<div class="ova-event-recent">
	<div class="event_archive <?php echo esc_attr($type_event) ?> <?php echo esc_attr($column) ?>" data-query="<?php echo esc_attr( json_encode( $data_query ) ); ?>" data-event-type="<?php echo esc_attr( $type_event ); ?>">
		<div class="wrap_loader">
			<svg class="loader" width="50" height="50">
				<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
				<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
			</svg>
		</div>
		<?php
		if( $events && $events->have_posts() ) : 
			while( $events->have_posts() ) : $events->the_post();
				el_get_template_part( 'content', 'event-'.$type_event );
			endwhile; wp_reset_postdata();
		else :
			?>
			<h3 class="event-notfound"><?php esc_html_e('Event not found', 'eventlist'); ?></h3>
			<?php 
		endif;
		?>
	</div>
</div>