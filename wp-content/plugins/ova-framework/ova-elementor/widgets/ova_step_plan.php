<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_step_plan extends Widget_Base {

	public function get_name() {
		return 'ova_step_plan';
	}

	public function get_title() {
		return __( 'Step plan', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-image-box';
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
			'number',
			[
				'label' => __( 'Number', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => "01",
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('Register an Account','ova-framework'),
			]
		);

		$this->add_control(
			'sub_title',
			[
				'label' => __( 'Sub Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('Proin ut iaculis odio. Etiam lobortis sit amet augue sit amet accumsan. Nulla sed ex placerat, vehicula tellus et, dictum metus. Vivamus pharetra vehicula tortor, eu iaculis leo maximus ut. Curabitur vel feugiat diam. Nunc sed tortor vitae tellus egestas rhoncus in eu ante.','ova-framework'),
			]
		);

		$this->add_control(
			'button',
			[
				'label' => __( 'Button', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('Register an Account','ova-framework'),
			]
		);


		$this->add_control(
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


		$this->end_controls_section();


		$this->start_controls_section(
			'section_number',
			[
				'label' => __( 'Number', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'number_typography',
				'selector' => '{{WRAPPER}} .ova-step-plan .number span',
			]
		);

		$this->add_control(
			'color_number',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .number span' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bg_color_number',
			[
				'label' => __( 'Background color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .number span' => 'background-color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'size_number',
			[
				'label' => __( 'Size Number', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 200,
						'step' => 1,
					]
				],
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .number span' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_media',
			[
				'label' => __( 'Padding', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .number ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


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
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .ova-step-plan .content .title',
			]
		);

		$this->add_control(
			'color_title',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .title' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-step-plan .content .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
		

		$this->start_controls_section(
			'section_sub_title',
			[
				'label' => __( 'Sub Title', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sub_title_typography',
				'selector' => '{{WRAPPER}} .ova-step-plan .content .sub-title',
			]
		);

		$this->add_control(
			'color_sub_title',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .sub-title' => 'color : {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'margin_sub_title',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .sub-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_button',
			[
				'label' => __( 'Button', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .ova-step-plan .content .button',
			]
		);

		$this->add_control(
			'color_button',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .button' => 'color : {{VALUE}};border-color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_button_hover',
			[
				'label' => __( 'Background Color Hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .button:hover' => 'background-color : {{VALUE}};border-color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color_button_hover',
			[
				'label' => __( 'Color Hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .button:hover' => 'color : {{VALUE}};',
				],
			]
		);


		$this->add_responsive_control(
			'margin_button',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding_button',
			[
				'label' => __( 'Padding', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-step-plan .content .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();

		$sub_title = $settings['sub_title'];
		$title = $settings['title'];
		$number = $settings['number'];
		$button = $settings['button'];
		
		?>
		<div class="ova-step-plan">
			<div class="number">
				<span class="second_font"><?php echo esc_html($number) ?></span>
			</div>
			<div class="content">
				<?php if (!empty($title)) : ?>
					<h3 class="title"><?php echo esc_html($title) ?></h3>
				<?php endif ?>
				<?php if (!empty($sub_title)) : ?>
					<p class="sub-title"><?php echo esc_html($sub_title) ?></p>
				<?php endif ?>
				<?php if (!empty($button)) : ?>
					<a href="<?php echo esc_url($settings['link']['url']) ?>" class="button second_font"><?php echo esc_html($button) ?></a>
				<?php endif ?>
			</div>
		</div>
		<?php

	}
}


