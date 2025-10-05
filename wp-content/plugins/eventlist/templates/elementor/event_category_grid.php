<?php if( ! defined( 'ABSPATH' ) ) exit();?>
<?php
$column = $args['column'];
?>
<div class="el-event-category-grid">
	<div class="grid-item <?php echo esc_attr( $column ); ?>">
		<?php if ( $args['list'] ): ?>
			<?php foreach ( $args['list'] as $key => $item ): ?>
				<?php
				$category = $item['category'];
				$filter_event = $item['filter_event'];
				$search_result = $item['search_result'];

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
				<a href="<?php echo esc_url( $link ); ?>" class="el-event-category-item">
					<div class="el-media">
						<?php \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</div>
					<h3 class="cate-name second_font">
						<?php echo esc_html($catName); ?>
					</h3>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>


