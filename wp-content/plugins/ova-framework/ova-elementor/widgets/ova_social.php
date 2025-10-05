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
use Elementor\REPEATER;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ova_social extends Widget_Base {

	public function get_name() {
		return 'ova_social';
	}

	public function get_title() {
		return __( 'Social', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-social-icons';
	}
	
	public function get_categories() {
		return [ 'hf' ];
	}

	public function get_keywords() {
		return [ 'social', 'icon', 'link' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_social_icon',
			[
				'label' => __( 'Social Icons', 'ova-framework' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'ova-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'Social Connect',
			]
		);

		$repeater = new Repeater();


		$repeater->add_control(
			'social',
			[
				'label' => __( 'Icon', 'ova-framework' ),
				'type' => Controls_Manager::ICON,
				'label_block' => true,
				'default' => 'fa fa-wordpress',
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __( 'Link', 'ova-framework' ),
				'type' => Controls_Manager::URL,
				'label_block' => true,
				'default' => [
					'is_external' => 'true',
					'url' => '#',
				],
				'placeholder' => __( 'https://your-link.com', 'ova-framework' ),
			]
		);

		$this->add_control(
			'social_icon_list',
			[
				'label' => __( 'Social Icons', 'ova-framework' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'social' => 'fa fa-twitter',
					],
					[
						'social' => 'fa fa-pinterest',
					],
					[
						'social' => 'fa fa-facebook',
					],
					[
						'social' => 'fa fa-instagram',
					],
				],
				'title_field' => '<i class="{{ social }}"></i> {{{ social.replace( \'fa fa-\', \'\' ).replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'ova-framework' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
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
					'{{WRAPPER}} .ova_social ' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .ova_social .content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'view',
			[
				'label' => __( 'View', 'ova-framework' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_social_style',
			[
				'label' => __( 'Icon', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => __( 'Size', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova_social .content a i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label' => __( 'Padding', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .ova-framework-social-icon' => 'padding: {{SIZE}}{{UNIT}};',
				],
				'default' => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
			]
		);

		$icon_spacing = is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};';

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => __( 'Spacing', 'ova-framework' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ova-framework-social-icon:not(:last-child)' => $icon_spacing,
				],
			]
		);


		$this->add_control(
			'icon_color',
			[
				'label' => __( 'Icon Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_social .content a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bg_icon_color',
			[
				'label' => __( 'Background Icon Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_social .content a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border', // We know this mistake - TODO: 'icon_border' (for hover control condition also)
				'selector' => '{{WRAPPER}} .ova-framework-social-icon',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'ova-framework' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ova-framework-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_social_hover',
			[
				'label' => __( 'Icon Hover', 'ova-framework' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'hover_background_color',
			[
				'label' => __( 'Background Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_social .content a:hover' => 'background-color: {{VALUE}}!important;',
				],
			]
		);

		$this->add_control(
			'hover_icon_color',
			[
				'label' => __( 'Icon Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova_social .content a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_border_color',
			[
				'label' => __( 'Border Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'image_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .ova-framework-social-icon:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'ova-framework' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$class_animation = '';

		if ( ! empty( $settings['hover_animation'] ) ) {
			$class_animation = ' elementor-animation-' . $settings['hover_animation'];
		}

		?>
		<div class="ova_social ova-framework-social-icons-wrapper">
			<?php if (!empty($settings['title'])) : ?>
				<h3 class="title second_font"><?php echo esc_html($settings['title']) ?></h3>
			<?php endif ?>
			<div class="content">
				<?php
				foreach ( $settings['social_icon_list'] as $index => $item ) {

					$social = str_replace( 'fa fa-', '', $item['social'] );
					$link_key = 'link_' . $index;

					$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

					if ( $item['link']['is_external'] ) {
						$this->add_render_attribute( $link_key, 'target', '_blank' );
					}

					if ( $item['link']['nofollow'] ) {
						$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
					}
					?>

					<?php
						$color_item = !(empty($item['color_item'])) ? " color: " . $item['color_item'] : '';
						$background_color_item = !(empty($item['background_color_item'])) ? ' background-color: ' . $item['background_color_item'] : '' ;
					?>
					<a class="ova-framework-icon ova-framework-social-icon ova-framework-social-icon-<?php echo esc_attr( $social . $class_animation ); ?>"
						<?php echo $this->get_render_attribute_string( $link_key ); ?>
						rel="nofollow"
						aria-label="<?php esc_attr_e( 'social', 'ova-framework' ); ?>" >
						<i class="<?php echo esc_attr( $item['social'] ); ?>" style=""></i>
					</a>
				<?php } ?>
			</div>
			<!-- end .content -->
		</div>
		<?php
	}
}
