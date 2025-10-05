<?php if( ! defined( 'ABSPATH' ) ) exit();

$filter_event = $args['filter_event'];
$show_count_event = $args['show_count_event'];

$data_options['slideBy'] 			= $args['slides_to_scroll'];
$data_options['margin'] 			= $args['margin_items'];
$data_options['autoplayHoverPause'] = $args['pause_on_hover'] === 'yes' ? true : false;
$data_options['loop'] 			 	= $args['infinite'] === 'yes' ? true : false;
$data_options['autoplay'] 			= $args['autoplay'] === 'yes' ? true : false;
$data_options['autoplayTimeout']	= $data_options['autoplay'] ? $args['autoplay_speed'] : 3000;
$data_options['smartSpeed']			= $args['smartspeed'];
$data_options['dots']               = ( $args['dots'] == 'yes') ? true : false;
$data_options['nav']            	= ( $args['nav'] == 'yes') ? true : false;

$tabs = $args['tabs'];

?>

<div class="el-event-venue">
	<div class="event-venue-slide owl-carousel owl-loaded" data-options="<?php echo esc_attr(json_encode($data_options)) ?>" >
		<?php if( ! empty( $tabs ) ) : foreach ($tabs as $item) : ?>
			<?php
			$id_loc = $item['location'];

			$term 			= get_term_loc_event_by_id_loc( $id_loc );
			$locName 		= $term['loc_name'];
			$loc_slug 		= $term['loc_slug'];
			$link_loc 		= $term['loc_link'];
			$link 			= add_query_arg( array( 'status' => $filter_event ), $link_loc );
			$custom_link 	= $item['custom_link'];
			$target 		= '';

			if ( 'half_map' == $custom_link ) {
				$filter_event = EL()->options->event->get('filter_events', 'all');
				if ( 'opening' === $filter_event ){
					$filter_event = 'selling';
				} elseif ( 'past' === $filter_event ){
					$filter_event = 'closed';
				}

				$link_result = $item['link']['url'];

				if ( $item['link']['is_external'] ) {
					$target = ' target="_blank"';
				}

				$terms  = get_term( $id_loc, 'event_loc' );

				if ( 0 != $terms->parent ) {
					$link = add_query_arg( array( 'event_city' => $loc_slug ), $link_result );
				} else {
					$link = add_query_arg( array( 'event_state' => $loc_slug ), $link_result );
				}
			}

			?>
			<div class="item-venue">
				<div class="el-media">
					<a href="<?php echo esc_url( $link ); ?>"<?php echo esc_attr( $target ); ?> title="<?php echo esc_attr( $locName ); ?>" aria-label="<?php esc_attr_e( 'event venue', 'eventlist' ); ?>" >
						<img src="<?php echo esc_url( $item['image']['url'] ) ?>" alt="<?php echo esc_attr( $locName ); ?>">
					</a>
				</div>
				<div class="el-content">
					<h3 class="venue-name">
						<a class="second_font" href="<?php echo esc_url( $link ); ?>"<?php echo esc_attr( $target ); ?> title="<?php echo esc_attr( $locName ) ?>" aria-label="<?php esc_attr_e( 'event venue', 'eventlist' ); ?>" >
							<?php echo esc_html( $locName ) ?>
						</a>
					</h3>
					<?php
					if ( $show_count_event === 'yes'  ) {
						$number_event = get_number_event_by_seting_element_loc( $id_loc, $filter_event );
						?>
						<p class="count-event"><?php echo esc_html( $number_event ) ?><span><?php esc_html_e('Events', 'eventlist') ?></span></p>
					<?php } ?>
				</div>
			</div>
		<?php endforeach; endif; ?>
	</div>
</div>
