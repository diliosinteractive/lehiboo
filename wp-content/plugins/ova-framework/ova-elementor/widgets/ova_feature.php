<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_feature extends Widget_Base {

	public function get_name() {
		return 'ova_feature';
	}

	public function get_title() {
		return __( 'Feature', 'ova-framework' );
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
			'type',
			[
				'label'   => __( 'Type', 'ova-framework' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => __('Icon', 'ova-framework'),
					'image' => __('Image', 'ova-framework'),
				],
			]
		);

		$this->add_control(
			'icon',
			[
				'label'   => __( 'Class Icons', 'ova-framework' ),
				'type'    => Controls_Manager::ICON,
				'default' => 'flaticon-world',
				'condition' => [
					'type' => 'icon',
				]
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
				'condition' => [
					'type' => 'image',
				]
			]
		);
		

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 5,
				'default' => __('Find What You Want','ova-framework'),
			]
		);

		$this->add_control(
			'sub_title',
			[
				'label' => __( 'Sub Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
				'default' => __('Eros nec hendrerito allis. Quisque id nibh cursus, vehicula mauris.','ova-framework'),
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
					'{{WRAPPER}} .ova-feature' => 'text-align: {{VALUE}}',
				]
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

		$this->add_control(
			'color_media',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-feature .ova-media .wp-media i:before' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-feature .ova-media' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .ova-feature .info .title a',
			]
		);

		$this->add_control(
			'color_title',
			[
				'label' => __( 'Color ', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-feature .info .title a' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-feature .info .title' => 'color : {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_title_hover',
			[
				'label' => __( 'Color hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-feature .info .title a:hover' => 'color : {{VALUE}}!important;',
					'{{WRAPPER}} .ova-feature .content .title:hover' => 'color : {{VALUE}}!important;',
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
					'{{WRAPPER}} .ova-feature .info .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .ova-feature .info .sub-title',
			]
		);

		$this->add_control(
			'color_sub_title',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-feature .info .sub-title' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-feature .info .sub-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();

		$sub_title = $settings['sub_title'];
		$title = $settings['title'];

		$icon = $settings['icon'];
		$image = $settings['image']['url'];
		
		

		?>
		<div class="ova-feature">
			<div class="ova-media">
				<div class="wp-media">
					<?php if ($settings['type'] === 'icon') : ?>
						<i class="<?php echo esc_attr($icon); ?>"></i>
					<?php endif ?>

					<?php if ($settings['type'] === 'image') : ?>
						<img src="<?php echo esc_attr($image) ?>" alt="<?php esc_attr($title) ?>">
					<?php endif ?>
				</div>
			</div>
			<div class="info">
				<?php if ($title) : ?>
					<?php
					if( $settings['link']['url'] ) {
						$title_tag = '<a class="second_font" href="'.$settings['link']['url'].'">'.$title.'</a>';
					} else {
						$title_tag = $title;
					}
					?>
					<h3 class="title"><?php echo wp_kses_post( $title_tag ); ?></h3>
				<?php endif ?>
				<?php if ($sub_title) : ?>
					<p class="sub-title"><?php echo esc_html($sub_title) ?></p>
				<?php endif ?>
			</div>

		</div>
		<?php

	}
}


