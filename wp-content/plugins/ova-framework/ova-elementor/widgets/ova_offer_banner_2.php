<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_offer_banner_2 extends Widget_Base {

	public function get_name() {
		return 'ova_offer_banner_2';
	}

	public function get_title() {
		return __( 'Offer Banner 2', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-image';
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
			'title_1',
			[
				'label' => __( 'title 1', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('2019 Website Event','ova-framework'),
			]
		);

		$this->add_control(
			'title_2',
			[
				'label' => __( 'title 2', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('Web design trends 2019','ova-framework'),
			]
		);

		$this->add_control(
			'sub_title_1',
			[
				'label' => __( 'Sub title 1', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('november 12 -13,2019','ova-framework'),
			]
		);

		$this->add_control(
			'sub_title_2',
			[
				'label' => __( 'Sub title 2', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('The Midway, San Francisco, CA','ova-framework'),
			]
		);


		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();
		
		?>
		<div class="ova-offer-banner2" style="background-image: url(<?php echo esc_url($settings['image']['url']) ?>)">
			<div class="content-1">
				<p class="title-1"><?php echo esc_html($settings['title_1']) ?></p>
				<p class="sub-title-1 second_font"><?php echo esc_html($settings['sub_title_1']) ?></p>
				<p class="sub-title-2 second_font"><?php echo esc_html($settings['sub_title_2']) ?></p>
			</div>
			<div class="content-2">
				<p class="title-2"><?php echo esc_html($settings['title_2']) ?></p>
			</div>
		</div>
		<?php

	}
}


