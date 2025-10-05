<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Color;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;

class EL_Elementor_Event_Special_Grid extends EL_Abstract_Elementor {

	protected $name 	= 'ova_event_special_grid';
	protected $title 	= 'Event Special Grid';
	protected $icon 	= 'eicon-posts-grid';

	public function get_title(){
		return __('Event Special Grid', 'eventlist');
	}

	protected function register_controls(){
		$this->start_controls_section(
			'section_setting',
			[
				'label' => __( 'Settings', 'eventlist' ),
			]
		);

		$this->add_control(
			'heading_setting_post',
			[
				'label' => __( 'Setting Events', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'categories',
			[
				'label' => esc_html__( 'Categories', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => el_get_list_categories(),
				'default' => [],
			]
		);

		$this->add_control(
			'locations',
			[
				'label' => esc_html__( 'Locations', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => el_get_list_locations(),
				'default' => [],
			]
		);

		$this->add_control(
			'filter_event',
			[
				'label' => __( 'Filter events', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' 				=> __( 'All', 'eventlist' ),
					'upcoming' 			=> __( 'Upcoming', 'eventlist' ),
					'selling' 			=> __( 'Selling', 'eventlist' ),
					'upcoming_selling' 	=> __( 'Upcoming & Selling', 'eventlist' ),
					'closed'  			=> __( 'Closed', 'eventlist' ),
					'feature' 			=> __( 'Featured', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'total_count',
			[
				'label' => __( 'Total', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
			]
		);

		$this->add_control(
			'order_by',
			[
				'label' => __( 'Order by', 'eventlist' ),
				'type' 	=> \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' 			=> __( 'Date', 'eventlist' ),
					'id'  			=> __( 'ID', 'eventlist' ),
					'title' 		=> __( 'Title', 'eventlist' ),
					'start_date' 	=> __( 'Start Date', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'eventlist' ),
				'type' 	=> \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' 	=> __( 'Ascending', 'eventlist' ),
					'DESC'  => __( 'Descending', 'eventlist' ),
				],
			]
		);
		

		$this->add_control(
			'heading_setting_layout',
			[
				'label' => __( 'Template', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'type_event',
			[
				'label' => __('Type Event', 'eventlist'),
				'type' => Controls_Manager::SELECT,
				'default' => 'type1',
				'options' => [
					'type1' => __('Type 1', 'eventlist'),
					'type2' => __('Type 2', 'eventlist'),
					'type4' => __('Type 4', 'eventlist'),
					'type5' => __('Type 5', 'eventlist'),
					'type6' => __('Type 6', 'eventlist'),
				]
			]
		);

		$this->add_control(
			'display_img',
			[
				'label' => esc_html__( 'Display Image', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'thumbnail',
				'options' => [
					'thumbnail' => esc_html__( 'Thumbnail', 'eventlist' ),
					'slider' 	=> esc_html__( 'Slider', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'column',
			[
				'label' => __( 'Column', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'three_column',
				'options' => [
					'two_column'  	=> __( '2 Columns', 'eventlist' ),
					'three_column' 	=> __( '3 Columns', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'display_price',
			[
				'label' => esc_html__( 'Display Price', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'min',
				'options' => [
					'min' => esc_html__( 'Min', 'eventlist' ),
					'max' => esc_html__( 'Max', 'eventlist' ),
					'min-max'  => esc_html__( 'Min Max', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'display_date',
			[
				'label' => esc_html__( 'Display Date', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'start',
				'options' => [
					'start' 	=> esc_html__( 'Start Date', 'eventlist' ),
					'start_end' => esc_html__( 'Start - End Date', 'eventlist' ),
				],
			]
		);


		$this->add_control(
			'show_time',
			[
				'label' 		=> esc_html__( 'Show Time', 'eventlist' ),
				'type' 			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on' 		=> esc_html__( 'Show', 'eventlist' ),
				'label_off' 	=> esc_html__( 'Hide', 'eventlist' ),
				'return_value' 	=> 'yes',
				'default' 		=> 'yes',
			]
		);

		

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();
		$template = apply_filters( 'el_elementor_event_special_grid', 'elementor/event_special_grid.php' );

		$column 		= $settings['column'];
		$type_event 	= $settings['type_event'];
		$total_count 	= $settings['total_count'];
		$order_by 		= $settings['order_by'];
		$order 			= $settings['order'];
		$filter_event 	= $settings['filter_event'];
		$display_price 	= $settings['display_price'];
		$display_date 	= $settings['display_date'];
		$show_time 		= $settings['show_time'];
		$categories 	= $settings['categories'];
		$locations 		= $settings['locations'];
		$display_img 	= $settings['display_img'];

	
		?>
		<div class="ova-event-special-grid-wrapper"
		data-display-img="<?php echo esc_attr( $display_img ); ?>"
		data-locations="<?php echo esc_attr( json_encode( $locations ) ); ?>"
		data-categories="<?php echo esc_attr( json_encode( $categories ) ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ); ?>"
		data-display-date="<?php echo esc_attr( $display_date ); ?>"
		data-display-price="<?php echo esc_attr( $display_price ); ?>"
		data-filter-event="<?php echo esc_attr( $filter_event ); ?>"
		data-order="<?php echo esc_attr( $order ); ?>"
		data-order-by="<?php echo esc_attr( $order_by ); ?>"
		data-total-count="<?php echo esc_attr( $total_count ); ?>"
		data-type-event="<?php echo esc_attr( $type_event ); ?>"
		data-column="<?php echo esc_attr( $column ); ?>" >

		<?php el_get_template( $template, $settings ); ?>

		</div>
		<?php
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Event_Special_Grid() );