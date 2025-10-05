<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_about_team extends Widget_Base {

	public function get_name() {
		return 'ova_about_team';
	}

	public function get_title() {
		return __( 'Ova About team', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'ovatheme' ];
	}

	public function get_script_depends() {
		return [ 'script-elementor' ];
	}

	protected function register_controls() {


		$this->start_controls_section(
			'section_heading_content',
			[
				'label' => __( 'Content', 'ova-framework' ),
			]
		);


		$this->add_control(
			'image',
			[
				'label'   => __( 'Image', 'ova-framework' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);
		

		$this->add_control(
			'name',
			[
				'label' => __( 'Name', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('Vikas Makwana','ova-framework'),
			]
		);

		$this->add_control(
			'job',
			[
				'label' => __( 'Job', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('Website Designer','ova-framework'),
			]
		);


		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'icon',
			[
				'label' => __( 'Social Icons', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::ICON,
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __( 'Link', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'ova-framework' ),
				'show_external' => false,
				'default' => [
					'url' => '#',
				],
			]
		);


		$this->add_control(
			'tabs',
			[
				'label'       => 'Item',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default' => [
					[
						'icon' => 'fa fa-facebook',
					],
					[
						'icon' => 'fa fa-linkedin',
					],
					[
						'icon' => 'fa fa-instagram',
					],
					[
						'icon' => 'fa fa-twitter',
					],
				],
				'title_field' => '{{{ icon }}}',
			]
		);

		

		$this->end_controls_section();


		$this->start_controls_section(
			'section_media',
			[
				'label' => __( 'Media', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => __( 'Font size icon', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .ova-media .image .social li a i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color_icon',
			[
				'label' => __( 'Color icon', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .ova-media .image .social li a i' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_icon_hover',
			[
				'label' => __( 'Color icon hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .ova-media .image .social li a:hover i' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_media',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .ova-media' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .ova-about-team .content .name',
			]
		);

		$this->add_control(
			'color_name',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .content .name' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-about-team .content .name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .ova-about-team .content .job',
			]
		);

		$this->add_control(
			'color_job',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-about-team .content .job' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-about-team .content .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();
		$tabs = $settings['tabs'];
		
		?>
		<div class="ova-about-team">
			<div class="ova-media">
				<div class="image">
					<img src="<?php echo esc_attr($settings['image']['url']) ?>" alt="<?php echo esc_attr($settings['name']) ?>">
					<ul class="social">
					<?php if (!empty($tabs)) : foreach($tabs as $item) : ?>
						<li>
							<a href="<?php echo esc_url($item['link']['url']) ?>"rel="nofollow" aria-label="<?php esc_attr_e( 'social link', 'ova-framework' ); ?>" >
								<i class="<?php echo esc_attr($item['icon']) ?>"></i>
							</a>
						</li>
					<?php endforeach; endif; ?>
				</ul>
				</div>
				
			</div>
			<div class="content">
				<p class="name second_font"><?php echo esc_html($settings['name']) ?></p>
				<p class="job"><?php echo esc_html($settings['job']) ?></p>
			</div>
			
		</div>
		<?php

	}
}


