<?php
namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_blog_slider extends Widget_Base {

	public function get_name() {
		return 'ova_blog_slider';
	}

	public function get_title() {
		return __( 'Blog Slider', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return [ 'ovatheme' ];
	}

	public function get_script_depends() {
		wp_enqueue_style( 'owl-carousel', OVA_PLUGIN_URI.'assets/libs/owl-carousel/assets/owl.carousel.min.css' );
		wp_enqueue_script( 'owl-carousel', OVA_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true );
		return [ 'script-elementor' ];
	}

	protected function register_controls() {

		$args = array(
			'orderby' => 'name',
			'order' => 'ASC'
		);

		$categories=get_categories($args);
		$cate_array = array();
		$arrayCateAll = array( 'all' => 'All categories ' );
		if ($categories) {
			foreach ( $categories as $cate ) {
				$cate_array[$cate->cat_name] = $cate->slug;
			}
		} else {
			$cate_array["No content Category found"] = 0;
		}




		//SECTION CONTENT
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'ova-framework' ),
			]
		);


		$this->add_control(
			'category',
			[
				'label' => __( 'Category', 'ova-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array_merge($arrayCateAll,$cate_array),
			]
		);

		$this->add_control(
			'total_count',
			[
				'label' => __( 'Total Post', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 5,
			]
		);

		$this->add_control(
			'number_title',
			[
				'label' => __( 'Number Word Title', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_control(
			'number_excerpt',
			[
				'label' => __( 'Number Word Excerpt', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 10,
			]
		);


		$this->add_control(
			'order_by',
			[
				'label' => __('Order By', 'ova-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __('ASC', 'ova-framework'),
					'desc' => __('DESC', 'ova-framework'),
				]
			]
		);

		$this->add_control(
			'show_meta',
			[
				'label' => __( 'Show Meta', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'ova-framework' ),
				'label_off' => __( 'Hide', 'ova-framework' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'ova-framework' ),
				'label_off' => __( 'Hide', 'ova-framework' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);



		$this->add_control(
			'show_excerpt',
			[
				'label' => __( 'Show Excerpt', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'ova-framework' ),
				'label_off' => __( 'Hide', 'ova-framework' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		

		$this->end_controls_section();
		//END SECTION CONTENT

		/*****************************************************************
		START SECTION ADDITIONAL VERSIONT 1 TESTIMONIAL
		******************************************************************/

		$this->start_controls_section(
			'section_additional_options',
			[
				'label' => __( 'Additional Options', 'ova-framework' ),
			]
		);


		$this->add_control(
			'margin_items',
			[
				'label' => __( 'Margin Right Items', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 45,
			]
		);

		$this->add_control(
			'slides_to_scroll',
			[
				'label' => __( 'Slides to Scroll', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'description' => __( 'Set how many slides are scrolled per swipe.', 'ova-framework' ),
				'default' => '1',
			]
		);

		$this->add_control(
			'response_desk',
			[
				'label' => __( 'Number Item Desktop', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '3',
			]
		);

		$this->add_control(
			'response_tablet',
			[
				'label' => __( 'Number Item Tablet', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '2',
			]
		);

		$this->add_control(
			'response_mobile',
			[
				'label' => __( 'Number Item Mobile', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '1',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => __( 'Pause on Hover', 'ova-framework' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'ova-framework' ),
					'no' => __( 'No', 'ova-framework' ),
				],
				'frontend_available' => true,
			]
		);


		$this->add_control(
			'infinite',
			[
				'label' => __( 'Infinite Loop', 'ova-framework' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'ova-framework' ),
					'no' => __( 'No', 'ova-framework' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'ova-framework' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'ova-framework' ),
					'no' => __( 'No', 'ova-framework' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 5000,
				'step' => 500,
				'condition' => [
					'autoplay' => 'yes',
				],
				'frontend_available' => true,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'smartspeed',
			[
				'label'   => __( 'Smart Speed', 'ova-framework' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 500,

			]
		);

		$this->add_control(
			'nav',
			[
				'label' => __('Show Navigation', 'ova-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'dots',
			[
				'label'   => __('Show dot', 'ova-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);


		$this->end_controls_section();
		#########################    END SECTION ADDITIONAL  VERSION 1  #########################


		//section style title
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Title', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_link_typography',
				'selector' => '{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .title h3 a',
			]
		);



		$this->add_control(
			'color_title',
			[
				'label' => __( 'Color Title', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .title h3 a' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_title_hover',
			[
				'label' => __( 'Color title hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .title h3 a:hover' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_title',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		//section style meta
		$this->start_controls_section(
			'section_meta',
			[
				'label' => __( 'Meta', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_link_typography',
				'selector' => '{{WRAPPER}} .ova-blog-slider .item-blog .content .post-meta-blog a',
			]
		);



		$this->add_control(
			'color_meta',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .post-meta-blog a' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .post-meta-blog .author span' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_meta_hover',
			[
				'label' => __( 'Color hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .post-meta-blog a:hover' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_meta',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .post-meta-blog ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//section style meta
		$this->start_controls_section(
			'section_icon',
			[
				'label' => __( 'Icon', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_control(
			'color_icon',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .post-meta-blog i:before' => 'color : {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();

		//section style desc
		$this->start_controls_section(
			'section_desc',
			[
				'label' => __( 'Description', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'selector' => '{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .excerpt p',
			]
		);



		$this->add_control(
			'color_desc',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .excerpt p' => 'color : {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'margin_desc',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-blog-slider .blog-slider .item-blog .content .excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$category = $settings['category'];
		$total_count = $settings['total_count'];
		$order = $settings['order_by'];

		$number_title = $settings['number_title'] ? $settings['number_title'] : 8;

		$args = [];
		if ($category == 'all') {
			$args=[
				'post_type' => 'post',
				'posts_per_page' => $total_count,
				'order' => $order,
			];
		} else {
			$args=[
				'post_type' => 'post', 
				'category_name'=>$category,
				'posts_per_page' => $total_count,
				'order' => $order,
			];
		}

		$blog = new \WP_Query($args);

		$is_rtl         = is_rtl() ? true : false;

		$data_options['slideBy']            = $settings['slides_to_scroll'];
		$data_options['margin']             = $settings['margin_items'];
		$data_options['autoplayHoverPause'] = $settings['pause_on_hover'] === 'yes' ? true : false;
		$data_options['loop']               = $settings['infinite'] === 'yes' ? true : false;
		$data_options['autoplay']           = $settings['autoplay'] === 'yes' ? true : false;
		$data_options['autoplayTimeout']    = $data_options['autoplay'] ? $settings['autoplay_speed'] : 3000;
		$data_options['smartSpeed']         = $settings['smartspeed'];
		$data_options['nav']                = ( $settings['nav'] == 'yes' ) ? true : false;
		$data_options['dots']               = ( $settings['dots'] == 'yes' ) ? true : false;
		$data_options['response_desk']      = $settings['response_desk'];
		$data_options['response_tablet']    = $settings['response_tablet'];
		$data_options['response_mobile']    = $settings['response_mobile'];
		$data_options['rtl']    = $is_rtl;
		?>
		<div class="ova-blog-slider">
			<div class="blog-slider  owl-carousel owl-theme owl-loaded" data-options="<?php echo esc_attr(json_encode($data_options)) ?>">
				<?php
				if($blog->have_posts()) : while($blog->have_posts()) : $blog->the_post();
					$thumbnail_url =wp_get_attachment_image_url(get_post_thumbnail_id() , 'el_img_squa' );
					?>

					<div class="item-blog">
						<?php if (!empty($thumbnail_url)) : ?>
							<div class="ova-media">
								<img src="<?php echo esc_attr($thumbnail_url) ?>" alt="<?php echo esc_attr(get_the_title()) ?>">
							</div>
						<?php endif ?>

						<div class="content">

							<?php if($settings['show_meta']) : ?>
								<div class="post-meta-blog">
									<span class="category">
										<i class="flaticon-gift-box-outline"></i>
										<?php the_category('&sbquo;&nbsp;'); ?>
										<span class="meta-slash">|</span>
									</span>
									<span class="author">
										<span class="left"><i class="flaticon-clock"></i></span>
										<span class="right"><?php the_time( get_option( 'date_format' ) );?></span>
									</span>
								</div>
							<?php endif ?>	

							<?php if($settings['show_title']) : ?>
								<div class="title">
									<h3><a class="second_font" href="<?php echo esc_attr(get_the_permalink()) ?>"><?php echo esc_html(meup_custom_text(get_the_title(), $settings['number_title'])) ?></a></h3>
								</div>
							<?php endif ?>

							<?php if ($settings['show_excerpt']) : ?>
								<div class="excerpt">
									<p><?php echo esc_html(meup_custom_text(get_the_excerpt(), $settings['number_excerpt'])) ?></p>
								</div>
							<?php endif ?>

						</div>
					</div>
					<?php
				endwhile; endif; wp_reset_postdata();
				?>

			</div>
		</div>
		<?php
	}
}
