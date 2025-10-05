<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_icon_box extends Widget_Base {

	public function get_name() {
		return 'ova_icon_box';
	}

	public function get_title() {
		return esc_html__( 'Ova Icon Box', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	public function get_categories() {
		return [ 'ovatheme' ];
	}
	
	// Add Your Controll In This Function
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'ova-framework' ),
			]
		);

		$this->add_control(
			'column',
			[
				'label' => esc_html__( 'Columns', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'three_columns',
				'options' => [
					'two_columns' => esc_html__( '2', 'ova-framework' ),
					'three_columns' => esc_html__( '3', 'ova-framework' ),
					'four_columns'  => esc_html__( '4', 'ova-framework' ),
				],
			]
		);

		$this->add_control(
			'show_number',
			[
				'label' => esc_html__( 'Show Number', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'ova-framework' ),
				'label_off' => esc_html__( 'Hide', 'ova-framework' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'meupicon-search-interface-symbol',
					'library' => 'all',
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__( 'Link', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::URL,
				'options' => [ 'url', 'is_external', 'nofollow' ],
				'default' => [
					'url' => '',
					'is_external' => false,
					'nofollow' => false,
					// 'custom_attributes' => '',
				],
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Choose what to do', 'ova-framework' ),
				'placeholder' => esc_html__( 'Type your title here', 'ova-framework' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'desc',
			[
				'label' => esc_html__( 'Description', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'rows' => 5,
				'default' => esc_html__( 'Easily find your event via search system with multiple params.', 'ova-framework' ),
				'placeholder' => esc_html__( 'Type your description here', 'ova-framework' ),
			]
		);

		$this->add_control(
			'list',
			[
				'label' => esc_html__( 'Items', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'icon' => [
							'value' 	=> 'meupicon-search-interface-symbol',
							'library' 	=> 'all',
						],
						'link' => [
							'url' => '#',
							'is_external' => false,
							'nofollow' => false,
							// 'custom_attributes' => '',
						],
						'title' => esc_html__( 'Choose what to do', 'ova-framework' ),
						'desc' => esc_html__( 'Easily find your event via search system with multiple params.', 'ova-framework' ),
					],
					[
						'icon' => [
							'value' 	=> 'meupicon-shopping-cart',
							'library' 	=> 'all',
						],
						'link' => [
							'url' => '#',
							'is_external' => false,
							'nofollow' => false,
							// 'custom_attributes' => '',
						],
						'title' => esc_html__( 'Booking event that you like', 'ova-framework' ),
						'desc' => esc_html__( 'Choose Ticket add to cart. Support payment via Woocommerce system.', 'ova-framework' ),
					],
					[
						'icon' => [
							'value' 	=> 'meupicon-download',
							'library' 	=> 'all',
						],
						'link' => [
							'url' => '#',
							'is_external' => false,
							'nofollow' => false,
							// 'custom_attributes' => '',
						],
						'title' => esc_html__( 'Get the ticket to attend', 'ova-framework' ),
						'desc' => esc_html__( 'After booking successfully, You will get ticket in email or download in your account', 'ova-framework' ),
					],
				],
			]
		);
		
		$this->end_controls_section();

		/* Items */
		$this->start_controls_section(
				'items_style_section',
				[
					'label' => esc_html__( 'Items', 'ova-framework' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'items_padding',
				[
					'label' => esc_html__( 'Padding', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'items_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'items_border',
					'selector' => '{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box',
				]
			);

			$this->start_controls_tabs(
					'items_style_tabs'
				);

				$this->start_controls_tab(
						'items_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-framework' ),
						]
					);

					$this->add_control(
						'items_bg_color',
						[
							'label' => esc_html__( 'Background', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'items_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-framework' ),
						]
					);

					$this->add_control(
						'items_bg_color_hover',
						[
							'label' => esc_html__( 'Background', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box:hover' => 'background: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/* Number */
		$this->start_controls_section(
				'number_style_section',
				[
					'label' => esc_html__( 'Number', 'ova-framework' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'number_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'number_border',
					'selector' => '{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .number',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'number_typography',
					'selector' => '{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .number',
				]
			);

			$this->add_control(
				'number_bg',
				[
					'label' => esc_html__( 'Background', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .number' => 'background: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'number_color',
				[
					'label' => esc_html__( 'Color', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .number' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		/* Icon */
		$this->start_controls_section(
				'icon_style_section',
				[
					'label' => esc_html__( 'Icon', 'ova-framework' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'icon_margin',
				[
					'label' => esc_html__( 'Margin', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Size', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 5,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs(
					'icon_style_tabs'
				);

				$this->start_controls_tab(
						'icon_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-framework' ),
						]
					);

					$this->add_control(
						'icon_color',
						[
							'label' => esc_html__( 'Color', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon i' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon svg' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon svg path' => 'fill: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'icon_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-framework' ),
						]
					);

					$this->add_control(
						'icon_color_hover',
						[
							'label' => esc_html__( 'Color', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon i:hover' => 'color: {{VALUE}};',
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon svg:hover' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .icon svg:hover path' => 'fill: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/* Title */
		$this->start_controls_section(
				'title_style_section',
				[
					'label' => esc_html__( 'Title', 'ova-framework' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'title_margin',
				[
					'label' => esc_html__( 'Margin', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typography',
					'selector' => '{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .title a',
				]
			);

			$this->start_controls_tabs(
					'title_style_tabs'
				);

				$this->start_controls_tab(
						'title_style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-framework' ),
						]
					);

					$this->add_control(
						'title_color',
						[
							'label' => esc_html__( 'Color', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .title a' => 'color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
						'title_style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-framework' ),
						]
					);

					$this->add_control(
						'title_color_hover',
						[
							'label' => esc_html__( 'Color', 'ova-framework' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .title a:hover' => 'color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		/* Description */
		$this->start_controls_section(
				'desc_style_section',
				[
					'label' => esc_html__( 'Description', 'ova-framework' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'desc_margin',
				[
					'label' => esc_html__( 'Margin', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'desc_typography',
					'selector' => '{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .desc',
				]
			);

			$this->add_control(
				'desc_color',
				[
					'label' => esc_html__( 'Color', 'ova-framework' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-icon-box .ova-icon-box-grid .icon-box .desc' => 'color: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();
	}

	// Render Template Here
	protected function render() {
		$settings 		= $this->get_settings();
		$show_number 	= $settings['show_number'];
		$column 		= $settings['column'];
		?>
		<div class="ova-icon-box">
			<div class="ova-icon-box-grid <?php echo esc_attr($column); ?>">
				<?php if ( $settings['list'] ): ?>
					<?php foreach ( $settings['list'] as $key => $item ): ?>
						<?php
						$number 	= ($key + 1) < 10 ? '0'.($key + 1) : ($key + 1);
						$link_url 	= $item['link']['url'];
						$title 		= $item['title'];
						$desc 		= $item['desc'];
						$nofollow 	= $item['link']['nofollow'] ? 'nofollow' : '';
						$target 	= $item['link']['is_external'] ? '_blank' : '_self';
						?>
						<div class="icon-box">
							<?php if ( $show_number == 'yes' ): ?>
								<div class="number second_font"><?php echo esc_html( $number ); ?></div>
							<?php endif; ?>
							<?php if ( $item['icon'] ): ?>
								<div class="icon">
									<?php \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</div>
							<?php endif; ?>
							<?php if ( $title ): ?>
								<h3 class="title second_font">
									<a href="<?php echo esc_url( $link_url ); ?>"
										title="<?php echo esc_attr( $title ) ?>"
										target="<?php echo esc_attr( $target ); ?>"
										rel="<?php echo esc_attr( $nofollow ); ?>"
										>
										<?php echo esc_html( $title ); ?></a>
								</h3>
							<?php endif; ?>
							<?php if ( $desc ): ?>
								<p class="desc second_font"><?php echo esc_html( $desc ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	
}