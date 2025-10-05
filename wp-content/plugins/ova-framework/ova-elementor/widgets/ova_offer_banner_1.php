<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_offer_banner_1 extends Widget_Base {

	public function get_name() {
		return 'ova_offer_banner_1';
	}

	public function get_title() {
		return __( 'Offer Banner 1', 'ova-framework' );
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
			'text_1',
			[
				'label' => __( 'Text 1', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('SUPPER','ova-framework'),
			]
		);

		$this->add_control(
			'text_2',
			[
				'label' => __( 'Text 2', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('OFFER','ova-framework'),
			]
		);

		$this->add_control(
			'text_3',
			[
				'label' => __( 'Text 3', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('20','ova-framework'),
			]
		);

		$this->add_control(
			'text_4',
			[
				'label' => __( 'Text 4', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('%','ova-framework'),
			]
		);

		$this->add_control(
			'text_5',
			[
				'label' => __( 'Text 4', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('OFF','ova-framework'),
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();
		
		?>
		<div class="ova-offer-banner1" style="background-image: url(<?php echo esc_url($settings['image']['url']) ?>)">
			<span class="text-1 second_font"><?php echo esc_html($settings['text_1']) ?></span><br/>
			<span class="text-1 text-2 second_font"><?php echo esc_html($settings['text_2']) ?></span><br/>
			<span class="wp-text-345">
				<span class="text-3 second_font"><?php echo esc_html($settings['text_3']) ?></span>
				<span class="wp-text-4">
					<span class="text-4 second_font"><?php echo esc_html($settings['text_4']) ?></span>
					<span class="text-5 second_font"><?php echo esc_html($settings['text_5']) ?></span>
				</span>
			</span>
		</div>
		<?php

	}
}


