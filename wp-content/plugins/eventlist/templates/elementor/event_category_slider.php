<?php if( ! defined( 'ABSPATH' ) ) exit();?>
<?php
$data_options['items']              = $args['item_number'];
$data_options['slideBy']            = $args['slides_to_scroll'];
$data_options['margin']             = $args['margin_items'];
$data_options['autoplayHoverPause'] = $args['pause_on_hover'] === 'yes' ? true : false;
$data_options['loop']               = $args['infinite'] === 'yes' ? true : false;
$data_options['autoplay']           = $args['autoplay'] === 'yes' ? true : false;
$data_options['autoplayTimeout']    = $args['autoplay_speed'];
$data_options['smartSpeed']         = $args['smartspeed'];
$data_options['dots']               = $args['dot_control'] === 'yes' ? true : false;
$data_options['nav']               	= $args['nav_control'] === 'yes' ? true : false;
$data_options['rtl']				= is_rtl() ? true: false;
?>
<div class="el-event-category-slider">
	<div class="container-slider owl-carousel owl-theme" data-options="<?php echo esc_attr( json_encode($data_options) ); ?>">
		<?php if ( $args['list'] ): ?>
			<?php foreach ( $args['list'] as $key => $item ): ?>
				<?php
				$category = $item['category'];
				$filter_event = $item['filter_event'];
				$search_result = $item['search_result'];
				$show_count_event = $item['show_count_event'];

				$term = get_term_cat_event_by_slug_cat($category);
				$catName = $term['cat_name'];
				$catSlug = $term['cat_slug'];
				$link_taxonomy = $term['link'];
				$link = add_query_arg( array('status' => $filter_event), $link_taxonomy );

				if ( $search_result == 'half_map' ) {
					$filter_event = EL()->options->event->get('filter_events', 'all');
					if ( 'opening' === $filter_event ) {
						$filter_event = 'selling';
					} elseif ( 'past' === $filter_event ) {
						$filter_event = 'closed';
					}

					$link_result = $item['link_result']['url'];
					$link = add_query_arg( array('cat' => $catSlug), $link_result );
				}
				?>
				<div class="item">
					<a href="<?php echo esc_url( $link ); ?>" class="el-event-category-item">
						<div class="el-media">
							<?php \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</div>
						<div class="content-cat">
							<h3 class="cate-name second_font">
								<?php echo esc_html($catName) ?>
							</h3>
							<?php
							if ( $show_count_event === 'yes' ) {
								$number_event = get_number_event_by_seting_element_cat($category, $filter_event);
								?>
								<p class="count-event second_font">
									<?php echo esc_html($number_event) ?>
									<span>
										<?php esc_html_e('Events', 'eventlist') ?>
									</span>
								</p>
								<?php
							}
							?>
						</div>
					</a>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>


