
<?php if( ! defined( 'ABSPATH' ) ) exit();

$column 		= $args['column'];
$show_filter 	= $args['show_filter'];
$show_button 	= $args['show_button'] !== "yes" ? "hide_element" : "";
$radius 		= $args['radius'];
$total_post 	= $args['total_count'];
$order 			= $args['order'];
$order_by 		= $args['order_by'];
$type_event 	= $args['type_event'];
$type_event 	= !empty($type_event) ? $type_event : 'type1';
$filter_event 		= $args['filter_event'];
$title 				= $args['title'];
$events_location 	= get_list_event_near_location_elementor( $order, $order_by, $filter_event );
$data_event 		= get_list_event_data( $events_location );
$data_query = array(
	'posts_per_page' 	=> $total_post,
	'order' 			=> $order,
	'orderby' 			=> $order_by,
	'filter_event'		=> $filter_event
);
$event_category_ids = $args['event_categories'];
$time_category_id 	= $args['time_categories'];

$restrictions 	= EL()->options->general->get('event_retrict') ? EL()->options->general->get('event_retrict') : array();
$event_bound 	= EL()->options->general->get('event_bound');
$event_lat 		= EL()->options->general->get('event_lat');
$event_lng 		= EL()->options->general->get('event_lng');
$event_radius 	= EL()->options->general->get('event_radius');

?>
<div class="ova-event-near-me">
	<div class="heading">
		<?php if ( $title ): ?>
			<h3 class="title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>
		<div class="title-location second_font">
			<i class="meupicon-arrow-down-sign-to-navigate" aria-hidden="true"></i>
			<h3 class="location-name"></h3>
			<input type="hidden" id="ova-event-lat" />
			<input type="hidden" id="ova-event-lng" />
			<input type="hidden" id="ova-event-status" />
			<div class="ova-event-popup">
				<div class="search-box">
					<div class="search">
						<span class="icon"><i class="fas fa-angle-up" aria-hidden="true"></i></span>

						<div class="el_place_autocomplete_container"
						data-retrict="<?php echo esc_attr( json_encode( $restrictions ) ); ?>"
						data-bound="<?php echo esc_attr( $event_bound ); ?>"
						data-lat="<?php echo esc_attr( $event_lat ); ?>"
						data-lng="<?php echo esc_attr( $event_lng ); ?>"
						data-radius="<?php echo esc_attr( $event_radius ); ?>"
						></div>
					</div>
					<ul class="ova-event-nav">
						<li>
							<a href="#" class="event-link curent-location" title="<?php esc_attr_e( 'Use my current location', 'eventlist' ); ?>">
								<span class="icon"><i class="fas fa-location-arrow" aria-hidden="true"></i></span>
								<span class="text"><?php esc_html_e( 'Use my current location', 'eventlist' ); ?></span>
							</a>
						</li>
						<li>
							<a href="#" class="event-link online-event" title="<?php esc_attr_e( 'Browse online events', 'eventlist' ); ?>">
								<span class="icon"><i class="far fa-play-circle" aria-hidden="true"></i></span>
								<span class="text"><?php esc_html_e( 'Browse online events', 'eventlist' ); ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
	</div>
	<?php if ( $show_filter == 'yes' ): ?>
		<div class="cate-el-wrapper main__clipper">
			<ul class="ova-event-categories main__scroller">
				<li class="item">
					<a href="#" class="category-link second_font active" data-event="category" data-id="0" title="<?php esc_attr_e( 'All', 'eventlist' ); ?>"><?php esc_html_e( 'All', 'eventlist' ); ?></a>
				</li>
				<?php if ( $time_category_id ): ?>
					<?php foreach ($time_category_id as $category_id): ?>
						<?php
						$category_name = get_list_event_time_categories()[$category_id];
						?>
						<li class="item">
							<a href="#" class="category-link second_font" data-event="time" data-id="<?php echo esc_attr( $category_id ); ?>" title="<?php echo esc_attr( $category_name ); ?>"><?php echo esc_html( $category_name ); ?></a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ( $event_category_ids ): ?>
					<?php foreach ($event_category_ids as $category_id): ?>
						<li class="item">
							<a href="#" class="category-link second_font" data-event="category" data-id="<?php echo esc_attr( $category_id ); ?>" title="<?php echo esc_attr( get_term( $category_id )->name ); ?>"><?php echo esc_html( get_term( $category_id )->name ); ?></a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
			<div class="main__bar"></div>
		</div>
	<?php endif; ?>
	<div class="event_archive <?php echo esc_attr($type_event) ?> <?php echo esc_attr($column) ?>" data-event="<?php echo esc_attr( json_encode( $data_event ) ); ?>" data-radius="<?php echo esc_attr( $radius ); ?>" data-query="<?php echo esc_attr( json_encode( $data_query ) ); ?>" data-event-type="<?php echo esc_attr( $type_event ); ?>">
		<div class="wrap_loader">
			<svg class="loader" width="50" height="50">
				<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
				<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
			</svg>
		</div>
	</div>
	<div class="ova-event-autocomplete <?php echo esc_attr( $show_button ); ?>">
		<p class="place-content">
			<span class="arrow-icon"><i class="fas fa-angle-up" aria-hidden="true"></i></span>
			<input type="text" class="place-name" placeholder="<?php esc_attr_e( 'Enter location', 'eventlist' ); ?>" aria-label="<?php esc_attr_e( 'Enter location', 'eventlist' ); ?>" />
			<span class="place-icon"><i class="meupicon-pin" aria-hidden="true"></i></span>
		</p>
		<div class="search-box">
			<div class="search">
				<span class="icon"><i class="fas fa-angle-up" aria-hidden="true"></i></span>
				<input type="text" id="ova-event-location" placeholder="<?php esc_attr_e( 'Enter location', 'eventlist' ); ?>" class="search-input" aria-label="<?php esc_attr_e( 'Enter location', 'eventlist' ); ?>" data-restrictions="<?php echo esc_attr( json_encode($restrictions) ); ?>" data-bound="<?php echo esc_attr( $event_bound ); ?>" data-bound-lat="<?php echo esc_attr( $event_lat ); ?>" data-bound-lng="<?php echo esc_attr( $event_lng ); ?>" data-bound-radius="<?php echo esc_attr( $event_radius ); ?>" />
			</div>
			<ul class="ova-event-nav">
				<li>
					<a href="#" class="event-link curent-location" title="<?php esc_attr_e( 'Use my current location', 'eventlist' ); ?>">
						<span class="icon"><i class="fas fa-location-arrow" aria-hidden="true"></i></span>
						<span class="text"><?php esc_html_e( 'Use my current location', 'eventlist' ); ?></span>
					</a>
				</li>
				<li>
					<a href="#" class="event-link online-event" title="<?php esc_attr_e( 'Browse online events', 'eventlist' ); ?>">
						<span class="icon"><i class="far fa-play-circle" aria-hidden="true"></i></span>
						<span class="text"><?php esc_html_e( 'Browse online events', 'eventlist' ); ?></span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>