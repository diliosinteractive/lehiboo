<?php

namespace ova_framework\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ova_images_grid extends Widget_Base {

	public function get_name() {
		return 'ova_images_grid';
	}

	public function get_title() {
		return esc_html__( 'Ova Images Grid', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'gallery',
			[
				'label' => esc_html__( 'Add Images', 'ova-framework' ),
				'type' => \Elementor\Controls_Manager::GALLERY,
				'show_label' => false,
				'default' => [],
			]
		);

		$this->end_controls_section();
	}

	// Render Template Here
	protected function render() {
		$settings = $this->get_settings();

		?>	
		<div class="ova-images-grid">
			<?php if ( $settings['gallery'] ): ?>
					<?php foreach ( $settings['gallery'] as $key => $image ): ?>
						<?php
						$image_id 		= $image['id'];
						$image_url 		= $image['url'];
						$image_alt 		= get_post_meta($image_id, '_wp_attachment_image_alt', true);
						$image_title 	= get_the_title($image_id);
						if ( ! $image_alt ) {
							$image_alt = $image_title;
						}
						$item_number = $key + 1;
						?>
						<div class="grid-item <?php echo esc_attr( "grid-item-".$item_number ); ?>">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
						</div>
					<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	
}
