<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

class EL_Elementor_Name_Event_Slider extends EL_Abstract_Elementor {

	protected $name 	= 'el_name_event_slider';
	protected $title 	= 'Name Event Slider';
	protected $icon 	= 'eicon-post-slider';

	
	public function get_title(){
		return __('Name Event Slider', 'eventlist');
	}

	public function get_script_depends() {
		wp_enqueue_style( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.css' );
		wp_enqueue_script( 'owl-carousel', EL_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
	}
	
	protected function register_controls() {

		$args = array(
			'taxonomy' => 'event_cat',
			'orderby' => 'name',
			'order' => 'ASC'
		);

		$categories=get_categories($args);
		$cate_array = array();
		$arrayCateAll = array( 'all' => 'All categories ' );
		if ($categories) {
			foreach ( $categories as $cate ) {
				$cate_array[$cate->slug] = $cate->cat_name;
			}
		} else {
			$cate_array["No content Category found"] = 0;
		}

		/***** Setting Event *****/
		$this->start_controls_section(
			'section_event',
			[
				'label' => __( 'Event', 'eventlist' ),
			]
		);

		$this->add_control(
			'category',
			[
				'label' => __( 'Category', 'eventlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array_merge($arrayCateAll,$cate_array),
			]
		);

		$this->add_control(
			'filter_event',
			[
				'label' => __( 'Filter events', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' => __( 'All', 'eventlist' ),
					'upcoming' => __( 'Upcoming', 'eventlist' ),
					'opening_upcoming' => __( 'Opening & Upcoming', 'eventlist' ),
					'opening' => __( 'Opening', 'eventlist' ),
					'past'  => __( 'Past', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'total_post',
			[
				'label' => __( 'Total', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 50,
				'step' => 1,
				'default' => 3
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order by', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'id',
				'options' => [
					'id'  => __( 'ID', 'eventlist' ),
					'title' => __( 'Title', 'eventlist' ),
					'start_date' => __( 'Start Date', 'eventlist' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' => __( 'Ascending', 'eventlist' ),
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

		$this->add_responsive_control(
			'width',
			[
				'label' => __( 'Width', 'eventlist' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 370,
				],
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => __( 'Padding', 'eventlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider .wrap_item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'total_text',
			[
				'label' => __( 'Total Text', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 100,
				'step' => 1,
				'default' => 30
			]
		);

		$this->add_control(
			'show_date',
			[
				'label' => __( 'Show Date', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);


		$this->add_control(
			'show_venu',
			[
				'label' => __( 'Show Venue', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Color', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider .item' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'color_date',
			[
				'label' => __( 'Color Date', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider .item .date' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => __( 'Hover', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider .item:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background', 'eventlist' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .el_name_event_slider .wrap_item' => 'Background: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'label' => __( 'Typography', 'eventlist' ),
				'selector' => '{{WRAPPER}} .el_name_event_slider .item',
			]
		);

		$this->end_controls_section();
		/***** Event Setting Event *****/


		/***** Setting Slider *****/
		$this->start_controls_section(
			'section_slider',
			[
				'label' => __( 'Slider', 'eventlist' ),
			]
		);

		$this->add_control(
			'owl_loop',
			[
				'label' => __( 'Infinite Loop', 'eventlist' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplayTimeout',
			[
				'label' => __( 'Autoplay Timeout (ms)', 'eventlist' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3000,
			]
		);


		$this->add_control(
			'owl_autoplay_speed',
			[
				'label' => __( 'Animation Speed (ms)', 'eventlist' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1000,
			]
		);

		$this->end_controls_section();
		/***** End Setting Slider *****/
	}

	protected function render() {

		$settings = $this->get_settings();

		$events = el_get_event_slideshow_simple( $settings['total_post'], $settings['category'], $settings['filter_event'], $settings['orderby'], $settings['order'] );

		if ( empty( $events ) ) {
			return;
		}

		$eids = array();

		if ( $events->have_posts() ) {
			$eids = $events->posts;
		}

		$is_rtl         = is_rtl() ? true : false;

		/* Setting Owl Carousel */
		$autoplay_speed = $settings['owl_autoplay_speed'];
		$autoplayTimeout = $settings['autoplayTimeout'];
		$loop       = ( 'yes' === $settings['owl_loop'] ) ? true : false;
		$mouseDrag      = count($eids) == 1 ? false : true;
		$owl_carousel = [
			'items'           => 1,
			'singleItem'      => 1,
			'autoplaySpeed' => $autoplay_speed,
			'autoplayTimeout' => $autoplayTimeout,
			'autoplay'        => true,
			'loop'            => $loop,
			'nav'             => false,
			'dots'            => false,
			'mouseDrag'       => $mouseDrag,
			'rtl'             => $is_rtl,
		];

		?>
		<div class="el_name_event_slider" >
			<div class="wrap_item owl-carousel owl-theme owl-loaded" data-owl="<?php echo esc_attr( wp_json_encode( $owl_carousel) ); ?>">
				<?php foreach ( $eids as $eid ) { ?>

					<a class="item" href="<?php echo esc_attr( get_the_permalink($eid) ); ?>">

						<span class="title">
							<i class="icon_info_alt"></i>
							<?php echo wp_kses_post( sub_string_word( get_the_title($eid), $settings['total_text'] ) ); ?>
						</span>
						
						<?php if( $settings['show_date'] ){

							$date_start = '';

							$date = get_post_meta( $eid, OVA_METABOX_EVENT.'start_date_str', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'start_date_str', true ) : '';
							$option_calendar = get_post_meta( $eid, OVA_METABOX_EVENT.'option_calendar', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'option_calendar', true ) : '';
							$calendar_recurrence = get_post_meta( $eid, OVA_METABOX_EVENT.'calendar_recurrence', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'calendar_recurrence', true ) : '';
							$arr_start_date = [];
							// die;
							
							/* Date */
							if ($option_calendar == 'auto') {
								if ( $calendar_recurrence ) {
									foreach ( $calendar_recurrence as $value ) {
										if ( ( strtotime($value['date']) - strtotime('today') ) >= 0 ) {
											$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
										}
									}
								}

								if ($arr_start_date != array()) {
									$date_start = date_i18n( get_option( 'date_format' ), min($arr_start_date) );
								} else {
									$date_start = date_i18n( get_option( 'date_format' ), $date );
								}

							} else {

								if ( $date !== '' ) {
									$date_start = date_i18n( get_option( 'date_format' ), $date );
								}
							}

							?>
							<span class="date">
								<?php if ( $date_start ) {
									echo esc_html( $date_start );
								}
								?>
							</span>

						<?php } ?>

						<?php 
						/* Venue */
						if( $settings['show_venu'] ){

							$venue = get_post_meta( $eid, OVA_METABOX_EVENT.'venue', true ) ? get_post_meta( $eid, OVA_METABOX_EVENT.'venue', true ) : '';
							?>

							<?php if ( $venue ) { ?>
								<span class="venue">
									<?php echo esc_html( ' - ' . implode( ', ', $venue ) ); ?>
								</span>
							<?php } ?>


						<?php } ?>
						
					</a>

				<?php }

				?>
			</div>
		</div>
		<?php
		
	}
}
\Elementor\Plugin::instance()->widgets_manager->register( new EL_Elementor_Name_Event_Slider() );
