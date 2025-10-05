
<?php if( ! defined( 'ABSPATH' ) ) exit();

$show_all 		= $args['show_all'];
$show_filter 	= $args['show_filter'];
$include_cat 	= $args['include_cat'];
$column 		= $args['column'];
$total_post 	= $args['total_count'];
$order 			= $args['order'];
$order_by 		= $args['order_by'];
$type_event 	= $args['type_event'];
$type_event 	= ! empty( $type_event ) ? $type_event : 'type1';
$filter_event 	= $args['filter_event'];

$display_img = apply_filters( 'el_display_image_thumbnail', EL()->options->event->get('display_image_opt', 'thumbnail'), $args );

$terms = get_term_by_cat_include( $include_cat );

$term_id_filter_string = get_term_id_filter_event_cat_element( $include_cat );
$term_id_filter = get_term_id_filter_event_cat_element( $include_cat, $show_all );
$events = get_list_event_grid_elementor( $args );

?>
<div class="ova-event-grid">
	<?php if( $show_filter == 'yes' ) { ?>
		<div class="ova__fillter_wrap main__clipper">
			<div class="el-button-filter main__scroller">
				<?php if( $show_all === 'yes' ) { ?>
					<button data-type="<?php echo esc_attr( $type_event ) ?>" data-filter="<?php echo esc_attr( 'all' ); ?>" data-order="<?php echo esc_attr( $order ); ?>" data-orderby="<?php echo esc_attr( $order_by ); ?>" data-display-img="<?php echo esc_attr( $display_img ); ?>" data-term_id_filter_string="<?php echo esc_attr( $term_id_filter_string ); ?>" data-number_post="<?php echo esc_attr( $total_post ); ?>" data-column='<?php echo esc_attr( $column ); ?>' data-status="<?php echo esc_attr( $filter_event ) ?>" class="second_font" >
						<?php esc_html_e( 'All', 'eventlist' ); ?>
					</button>
				<?php } 

				if( ! empty( $terms ) ) {
					foreach( $terms as $term ){
						?>
						<button data-type="<?php echo esc_attr( $type_event ) ?>" data-filter="<?php echo esc_attr( $term->term_id ); ?>" data-order="<?php echo esc_attr( $order ); ?>" data-orderby="<?php echo esc_attr( $order_by ); ?>" data-display-img="<?php echo esc_attr( $display_img ); ?>" data-term_id_filter_string="<?php echo esc_attr( $term_id_filter_string ); ?>" data-number_post="<?php echo esc_attr( $total_post ); ?>" data-column='<?php echo esc_attr( $column ); ?>' data-status="<?php echo esc_attr( $filter_event ) ?>" class="second_font" 
							>
							<?php echo esc_html( $term->name ); ?>
						</button>
						<?php
					}
				}
				?>
			</div>
			<div class="main__bar"></div>
		</div>
		<?php
	} 
	?>
	
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