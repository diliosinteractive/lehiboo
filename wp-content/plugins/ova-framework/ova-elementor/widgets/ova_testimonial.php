<?php
namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_testimonial extends Widget_Base {

	public function get_name() {
		return 'ova_testimonial';
	}

	public function get_title() {
		return __( 'Testimonial', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
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

		$this->start_controls_section(
			'section_version',
			[
				'label' => __( 'Version', 'ova-framework' ),
			]
		);

		$this->add_control(
			'version',
			[
				'label'   => __( 'Version', 'ova-framework' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'version_1',
				'options' => [
					'version_1' => __('Version 1', 'ova-framework'),
					'version_2' => __('Version 2', 'ova-framework'),
				],
			]

		);


		$this->end_controls_section();


		//SECTION CONTENT
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'ova-framework' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label'   => 'Class Icon',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'flaticon-quote-1',
			]
		);


		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'name_author',
			[
				'label'   => 'Name Author',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __('Jonh Doe', 'ova-framework'),
			]
		);

		$repeater->add_control(
			'job',
			[
				'label'   => 'Job',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __('CEO & Fouder Travel', 'ova-framework'),
			]
		);


		$repeater->add_control(
			'image',
			[
				'label'   => 'Image Author',
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'testimonial',
			[
				'label'   => __( 'Testimonial ', 'ova-framework' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Quisque sodales massa in turpis vestibulum consequat. Morbi tincidunt dui non lacinia aliquet. Nulla auctor risus at tempus luctus', 'ova-framework' ),
			]
		);



		$this->add_control(
			'tabs',
			[
				'label'       => 'Item Testimonial',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default' => [
					[
						'name_author' => __('Jonh Doe', 'ova-framework'),
						'job' => __('CEO & Fouder Travel', 'ova-framework'),
						'testimonial' =>  __( 'Quisque sodales massa in turpis vestibulum consequat. Morbi tincidunt dui non lacinia aliquet. Nulla auctor risus at tempus luctus”', 'ova-framework' ),
					],
					[
						'name_author' => __('Gregory Kennedy', 'ova-framework'),
						'job' => __('Developer', 'ova-framework'),
						'testimonial' =>  __( 'Their services are among the best to be honest. Making everything simple and easy, even for beginners and novices like me and my family.', 'ova-framework' ),
					],
					[
						'name_author' => __('Jonh Doe', 'ova-framework'),
						'job' => __('CEO & Fouder Travel', 'ova-framework'),
						'testimonial' =>  __( 'Quisque sodales massa in turpis vestibulum consequat. Morbi tincidunt dui non lacinia aliquet. Nulla auctor risus at tempus luctus”', 'ova-framework' ),
					],
				],
				'title_field' => '{{{ name_author }}}',
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
				'default' => 6000,
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
				'default' => 'yes',
			]
		);

		$this->add_control(
			'dots',
			[
				'label'   => __('Show dot', 'ova-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);


		$this->end_controls_section();
		#########################    END SECTION ADDITIONAL  VERSION 1  #########################

		$this->start_controls_section(
			'section_image',
			[
				'label' => __( 'Image', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_responsive_control(
			'margin_image',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .ova-media' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

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
				'selector' => '{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .desc, {{WRAPPER}} .ova-testimonial.version_2 .item .content .desc',
			]
		);

		$this->add_control(
			'color_desc',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .desc' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .content .desc' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_name',
			[
				'label' => __( 'Name', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'name_typography',
				'selector' => '{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .name-author, {{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .name-author',
			]
		);

		$this->add_control(
			'color_name',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .name-author' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .name-author' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_name',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .name-author' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .name-author' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_job',
			[
				'label' => __( 'Job', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'job_typography',
				'selector' => '{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .job, {{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .job',
			]
		);

		$this->add_control(
			'color_job',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .job' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .job' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_job',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 .wp-testimonial .content .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ova-testimonial.version_2 .item .ova-media .wp-title .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_icon',
			[
				'label' => __( 'Icon', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'version' => 'version_1'
				]
			]
		);

		$this->add_responsive_control(
			'size_icon',
			[
				'label' => __( 'Size', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 > span:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color_icon',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial.version_1 > span:before' => 'color : {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_nav',
			[
				'label' => __( 'Nav', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'size_nav',
			[
				'label' => __( 'Size', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial .owl-nav .owl-next i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ova-testimonial .owl-nav .owl-prev i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color_nav',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial .owl-nav .owl-next i' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-testimonial .owl-nav .owl-prev i' => 'color : {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_dot',
			[
				'label' => __( 'Dot', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'size_dot',
			[
				'label' => __( 'Size', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial .owl-dots .owl-dot span' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color_dot',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial .owl-dots .owl-dot span' => 'background-color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_dot_active',
			[
				'label' => __( 'Color active', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-testimonial .owl-dots .owl-dot.active span' => 'background-color : {{VALUE}};',
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

		$is_rtl         = is_rtl() ? true : false;

		$tabs = $settings['tabs'];

		$version = $settings['version'];

		$data_options['slideBy'] 			= $settings['slides_to_scroll'];
		$data_options['margin'] 			= $settings['margin_items'];
		$data_options['autoplayHoverPause'] = $settings['pause_on_hover'] === 'yes' ? true : false;
		$data_options['loop'] 			 	= $settings['infinite'] === 'yes' ? true : false;
		$data_options['autoplay'] 			= $settings['autoplay'] === 'yes' ? true : false;
		$data_options['autoplayTimeout']	= $data_options['autoplay'] ? $settings['autoplay_speed'] : 3000;
		$data_options['smartSpeed']			= $settings['smartspeed'];
		$data_options['dots']                =  ( $settings['dots'] == 'yes') ? true : false;
		$data_options['nav']            	=  ( $settings['nav'] == 'yes') ? true : false;
		$data_options['rtl']				= $is_rtl;

		?>
		<?php if ( $version === 'version_1') : ?>
			<div class="ova-testimonial <?php echo esc_attr($version) ?>">
				<?php if($settings['icon'] !== '') : ?>
					<span class="<?php echo esc_attr($settings['icon']) ?>"></span>
				<?php endif ?>
				<div class="wp-testimonial  owl-carousel owl-theme owl-loaded" data-options="<?php echo esc_attr(json_encode($data_options)) ?>">
					<?php if(!empty($tabs)) : foreach ($tabs as $item) : ?>
						<div class="item">
							<div class="ova-media">
								<div class="image">
									<img src="<?php echo esc_url($item['image']['url']) ?>" alt="<?php echo esc_attr($item['name_author']) ?>">
								</div>
							</div>
							<div class="content-testimonial">

								<?php if($item['testimonial'] !== '') : ?>
									<p class="desc second_font"><?php echo esc_html($item['testimonial']) ?></p>
								<?php endif ?>
								<?php if($item['name_author'] !== '') : ?>
									<p class="name-author second_font"><?php echo esc_html($item['name_author']) ?></p>
								<?php endif ?>
								<?php if($item['job'] !== '') : ?>
									<p class="job"><?php echo esc_html($item['job']) ?></p>
									<?php endif ?>
								</div>
							</div>
						<?php endforeach; endif; ?>
					</div>
				</div>
			<?php endif ?>
			<?php if( $version  === 'version_2') : ?>
				<div class="ova-testimonial <?php echo esc_attr($version) ?>">

					<div class="wp-testimonial  owl-carousel owl-theme owl-loaded" data-options="<?php echo esc_attr(json_encode($data_options)) ?>">
						<?php if(!empty($tabs)) : foreach ($tabs as $item) : ?>
							<div class="item">
								<div class="content-testimonial">
									<?php if($item['testimonial'] !== '') : ?>
										<p class="desc"><?php echo esc_html($item['testimonial']) ?></p>
									<?php endif ?>
								</div>
								<div class="ova-media">
									<div class="image">
										<img src="<?php echo esc_url($item['image']['url']) ?>" alt="<?php echo esc_attr($item['name_author']) ?>">
									</div>
									<div class="wp-title">
										<?php if($item['name_author'] !== '') : ?>
											<p class="name-author second_font"><?php echo esc_html($item['name_author']) ?></p>
										<?php endif ?>
										<?php if($item['job'] !== '') : ?>
											<p class="job"><?php echo esc_html($item['job']) ?></p>
											<?php endif ?>
										</div>
									</div>
								</div>
							<?php endforeach; endif; ?>
						</div>
					</div>
				<?php endif ?>

				<?php
			}
		}
