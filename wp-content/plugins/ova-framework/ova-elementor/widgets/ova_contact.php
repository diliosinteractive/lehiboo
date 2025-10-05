<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_contact extends Widget_Base {

	public function get_name() {
		return 'ova_contact';
	}

	public function get_title() {
		return __( 'Contact', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-email-field';
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
			'type_link',
			[
				'label' => __( 'Type Link', 'ova-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'email',
				'options' => [
					'email' => __('Email', 'ova-framework'),
					'tell' => __('Tell', 'ova-framework'),
					'domain' => __('Domain', 'ova-framework'),
					'none' => __('None', 'ova-framework'),
				]
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
					'is_external' => false,
					'nofollow' => false,
				],
			]
		);

		$this->add_control(
			'address',
			[
				'label' => __( 'Address', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __('contact@domain.com', 'ova-framework'),
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
					'{{WRAPPER}} .ova-contact' => 'text-align: {{VALUE}}',
				]
			]
		);

		$this->add_control(
			'class_icon',
			[
				'label' => __( 'Class icon', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'flaticon-e-mail-envelope',
			]
		);

		$this->end_controls_section();


		//section style image
		$this->start_controls_section(
			'section_icon',
			[
				'label' => __( 'Icon', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'font_size_icon',
			[
				'label' => __( 'Font Size', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
						'step' => 1,
					]
				],
				'selectors' => [
					'{{WRAPPER}} .ova-contact .icon i:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color_icon',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-contact .icon i:before' => 'color : {{VALUE}};'
				],
			]
		);

		$this->add_responsive_control(
			'margin_icon',
			[
				'label' => __( 'Margin', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-contact .icon i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_address',
			[
				'label' => __( 'Address', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'address_typography',
				'selector' => '{{WRAPPER}} .ova-contact .address a, {{WRAPPER}} .ova-contact .address ',
			]
		);

		$this->add_control(
			'color_address',
			[
				'label' => __( 'Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-contact .address a' => 'color : {{VALUE}};',
					'{{WRAPPER}} .ova-contact .address' => 'color : {{VALUE}};',
				],
			]
		);


		$this->add_control(
			'color_address_hover',
			[
				'label' => __( 'Color hover', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-contact .address a:hover' => 'color : {{VALUE}};',
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
					'{{WRAPPER}} .ova-contact .address' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


	}

	protected function render() {
		$settings = $this->get_settings();
		$type_link = $settings['type_link'];
		$link = $settings['link']['url'];
		$address = $settings['address'];

		$icon = $settings['class_icon'];
		switch($type_link) {
			case "email" : {
				$address = "<a href='mailto:".$link."'>".$address."</a>";
				break;
			}
			case "tell" : {
				$address = "<a href='tel:".$link."'>".$address."</a>";
				break;
			}

			case "domain" : {
				$address = "<a href='".$link."'>".$address."</a>";
				break;
			}

			case "none" : {
				$address = $link ? "<a href='".$link."'>".$address."</a>" : $address;
				break;
			}

		}
		
		?>
		<div class="ova-contact">
			<?php if (!empty($icon)) : ?>
				<div class="icon">
					<i class="<?php echo esc_attr($icon) ?>"></i>
				</div>
			<?php endif ?>
			<?php if (!empty($address)) : ?>
				<div class="address"><span><?php echo wp_kses_post( $address ); ?></span></div>
			<?php endif ?>
			
		</div>
		<?php

	}
// end render
}


