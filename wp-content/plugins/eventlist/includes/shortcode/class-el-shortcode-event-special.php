<?php defined( 'ABSPATH' ) || exit;

class El_Shortcode_Event_Special extends EL_Shortcode {

	public $shortcode = 'el_event_special';

	public function __construct() {
		parent::__construct();
	}

	function add_shortcode( $args, $content = null ) {

		$type_event 	= EL()->options->event->get( 'archive_type', 'type1' );
		$total_count 	= EL()->options->event->get( 'listing_posts_per_page', '5' );;
		$column 		= EL()->options->event->get( 'archive_column', 'three_column' );
		$order_by 		= EL()->options->event->get( 'archive_order_by', 'date' );
		$order 			= EL()->options->event->get( 'archive_order', 'DESC' );
		$filter_event 	= EL()->options->event->get( 'filter_events', 'all' );
		$display_price 	= EL()->options->event->get( 'display_price_opt', 'min' );
		$display_date 	= EL()->options->event->get( 'display_date_opt', 'start' );
		$show_time 		= EL()->options->event->get( 'show_hours_archive', 'yes' );
		$display_img 	= EL()->options->event->get( 'display_image_opt', 'thumbnail' );
		
		$args = shortcode_atts( array(
			'type_event' 	=> $type_event,
			'column' 		=> $column,
			'total_count' 	=> $total_count,
			'order_by' 		=> $order_by,
			'order' 		=> $order,
			'filter_event' 	=> $filter_event,
			'display_price' => $display_price,
			'display_date' 	=> $display_date,
			'show_time' 	=> $show_time,
			'categories' 	=> '',
			'locations' 	=> '',
			'display_img' 	=> $display_img,
		), $args );

		if ( ! empty( $args['categories'] ) ) {
			$categories = array_map('trim', explode(',', $args['categories'] ) );
			$args['categories'] = $categories;
		} else {
			$args['categories'] = [];
		}

		if ( ! empty( $args['locations'] ) ) {
			$locations = array_map('trim', explode(',', $args['locations'] ) );
			$args['locations'] = $locations;
		} else {
			$args['locations'] = [];
		}
	
		$template = apply_filters( 'el_shortcode_event_special_template', 'shortcode/event_special_grid.php' );

		ob_start();

		?>
		<div class="ova-event-special-grid-wrapper"
		data-display-img="<?php echo esc_attr( $display_img ); ?>"
		data-locations="<?php echo esc_attr( json_encode( $args['locations'] ) ); ?>"
		data-categories="<?php echo esc_attr( json_encode( $args['categories'] ) ); ?>"
		data-show-time="<?php echo esc_attr( $args['show_time'] ); ?>"
		data-display-date="<?php echo esc_attr( $args['display_date'] ); ?>"
		data-display-price="<?php echo esc_attr( $args['display_price'] ); ?>"
		data-filter-event="<?php echo esc_attr( $args['filter_event'] ); ?>"
		data-order="<?php echo esc_attr( $args['order'] ); ?>"
		data-order-by="<?php echo esc_attr( $args['order_by'] ); ?>"
		data-total-count="<?php echo esc_attr( $args['total_count'] ); ?>"
		data-type-event="<?php echo esc_attr( $args['type_event'] ); ?>"
		data-column="<?php echo esc_attr( $args['column'] ); ?>" >
		<?php
		el_get_template( $template, $args );
		?>
		</div>
		<?php

		return ob_get_clean();
	}

}

new El_Shortcode_Event_Special();