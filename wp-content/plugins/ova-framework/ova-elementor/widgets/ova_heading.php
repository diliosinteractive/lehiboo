<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_heading extends Widget_Base {

	public function get_name() {
		return 'ova_heading';
	}

	public function get_title() {
		return __( 'Ova Heading', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-heading';
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
			'title',
			[
				'label' => __( 'Heading Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 5,
				'default' => __('How It Work','ova-framework'),
			]
		);

		$this->add_control(
			'html_tag_title',
			[
				'label' => __( 'HTML Tag Title', 'ova-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => [
					'h1' => "H1",
					'h2' => "H2",
					'h3' => "H3",
					'h4' => "H4",
					'h5' => "H5",
					'h6' => "H6",
					'div' => "div",
					'span' => "Span",
					'p' => "p",
				]
			]
		);

		$this->add_control(
			'sub_title',
			[
				'label' => __( 'Sub Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('You can choose to display featured','ova-framework'),
			]
		);

		$this->add_control(
			'html_tag_sub_title',
			[
				'label' => __( 'HTML Tag Sub Title', 'ova-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'p',
				'options' => [
					'h1' => "H1",
					'h2' => "H2",
					'h3' => "H3",
					'h4' => "H4",
					'h5' => "H5",
					'h6' => "H6",
					'div' => "div",
					'span' => "Span",
					'p' => "p",
				]
			]
		);

		$this->add_control(
			'show_line',
			[
				'label' => __( 'Show Line', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'your-plugin' ),
				'label_off' => __( 'Hide', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);


		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'ova-framework' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ova-framework' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ova-framework' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .ova-heading' => 'text-align: {{VALUE}}',
				]
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
				'selector' => '{{WRAPPER}} .ova-heading .title',
			]
		);

		$this->add_control(
			'color_title',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-heading .title' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-heading .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .ova-heading .sub-title',
			]
		);

		$this->add_control(
			'color_sub_title',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-heading .sub-title' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-heading .sub-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_line',
			[
				'label' => __( 'Line', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

	

		$this->add_control(
			'color_line',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-heading .line' => 'background-color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'width_line',
			[
				'label' => __( 'Width', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-heading .line' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'height_line',
			[
				'label' => __( 'Height', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-heading .line' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_responsive_control(
			'margin_line',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-heading .line' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		


	}

	protected function render() {
		$settings = $this->get_settings();

		$sub_title = $settings['sub_title'];
		$title = $settings['title'];
		$tag_sub_title = $settings['html_tag_sub_title'];
		$tag_title = $settings['html_tag_title'];

		$class_show_line = $settings['show_line'] !== "yes" ? "not-line" : "";
		
		?>
		<div class="ova-heading">
			
			<?php if (!empty($title)) : ?>
				<<?php echo esc_attr($tag_title) ?> class="title second_font"><?php echo esc_html($title) ?></<?php echo esc_attr($tag_title) ?>>
			<?php endif ?>

			<?php if (!empty($sub_title)) : ?>
				<<?php echo esc_attr($tag_sub_title) ?> class="sub-title "><?php echo esc_html($sub_title) ?></<?php echo esc_attr($tag_sub_title) ?>>
			<?php endif ?>

			<span class="line <?php echo esc_attr($class_show_line) ?>"></span>

		</div>
		<?php

	}
}


