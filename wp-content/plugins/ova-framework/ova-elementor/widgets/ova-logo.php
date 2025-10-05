<?php
namespace ova_framework\Widgets;
use Elementor;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ova_logo extends Widget_Base {

	public function get_name() {
		return 'ova_logo';
	}

	public function get_title() {
		return __( 'Logo', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-logo';
	}

	public function get_categories() {
		return [ 'hf' ];
	}

	public function get_keywords() {
		return [ 'image', 'photo', 'visual' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_image',
			[
				'label' => __( 'Image', 'ova-framework' ),
			]
		);

		$this->add_control(
			'desk_logo',
			[
				'label' => __( 'Desktop Logo', 'ova-framework' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'desk_logo',
				'default' => 'full',
				'separator' => 'none',
			]
		);

		$this->add_control(
			'desk_w',
			[
				'label' => __( 'Desktop Logo Width', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 130,
				],
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.desk-logo' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'desk_h',
			[
				'label' => __( 'Desktop Logo Height', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.desk-logo' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);



		$this->add_control(
			'mobile_logo',
			[
				'label' => __( 'Mobile Logo Retina', 'ova-framework' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'mobile_w',
			[
				'label' => __( 'Mobile Logo Width', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 130,
				],
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.mobile-logo' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'mobile_h',
			[
				'label' => __( 'Mobile Logo Height', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.mobile-logo' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'mobile_logo',
				'default' => 'full',
				'separator' => 'none',
			]
		);


		$this->add_control(
			'fixed_logo',
			[
				'label' => __( 'Header Fixed Logo Retina', 'ova-framework' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'fixed_logo',
				'default' => 'full',
				'separator' => 'none',
			]
		);

		$this->add_control(
			'fixed_w',
			[
				'label' => __( 'Fixed Logo Width', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 130,
				],
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.logo-fixed' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'fixed_h',
			[
				'label' => __( 'Fixed Logo Height', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
						'step' => 1,
					]
				],
				
				'selectors' => [
					'{{WRAPPER}} .ova_logo img.logo-fixed' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'ova-framework' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __( 'Left', 'ova-framework' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ova-framework' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => __( 'Right', 'ova-framework' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container' => 'justify-content: {{VALUE}};',
				],
			]
		);



		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['desk_logo']['url'] ) ) {
			return;
		} ?>

			<a class="ova_logo" href="<?php echo esc_url(home_url('/')); ?>">
				<img src="<?php echo esc_url( $settings['desk_logo']['url'] ); ?>" alt="<?php bloginfo('name');  ?>" class="desk-logo d-none d-xl-block" />
				<img src="<?php echo esc_url( $settings['mobile_logo']['url'] ); ?>" alt="<?php bloginfo('name');  ?>" class="mobile-logo d-block d-xl-none" />

				<img src="<?php echo esc_url( $settings['fixed_logo']['url'] ); ?>" alt="<?php bloginfo('name');  ?>" class="logo-fixed" />
			</a>

		<?php
	}
	
}
