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

class ova_instagram extends Widget_Base {

	public function get_name() {
		return 'ova_instagram';
	}

	public function get_title() {
		return __( 'Instagram Footer', 'ova-framework' );
	}

	public function get_icon() {
		return 'eicon-instagram-post';
	}
	
	public function get_categories() {
		return [ 'hf' ];
	}

	public function get_keywords() {
		return [ 'instagram', 'link' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_social_icon',
			[
				'label' => __( 'Instagram', 'ova-framework' ),
			]
		);

		$this->add_control(
			'token',
			[
				'label' => __( 'Token', 'ova-framework' ),
				'type' => Controls_Manager::TEXTAREA,
				'row' => 2,
			]
		);

		$this->add_control(
			'number_photo',
			[
				'label' => __( 'Number photo', 'ova-framework' ),
				'type' => Controls_Manager::NUMBER,
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
					'{{WRAPPER}} .ova-instagram a:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'icon_color',
			[
				'label' => __( 'Icon Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-instagram a:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bg_icon_color',
			[
				'label' => __( 'Background Icon Color', 'ova-framework' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ova-instagram a:after' => 'background-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$token = $settings['token'] ? $settings['token'] : '';
		$number_photo = $settings['number_photo'] ? $settings['number_photo'] : 10;
		$i = 0;

		$result = $obj = array();
		if( $token ){
			$json_link    = "https://graph.instagram.com/me/media?fields=caption,media_url&access_token={$token}";	
			$result = @wp_remote_get( $json_link );
		}
		
		
		
		if( is_array( $result ) && ! is_wp_error( $result ) ) {
			
			$obj = json_decode( str_replace( '%22', '&rdquo;', $result['body'] ), true );	

			
		?>
		<div class="ova-instagram">
			<?php
			if (isset($obj['data'])) {
				foreach ($obj['data'] as $post){
					if( $i == $number_photo ) break;
					$pic_src = $post['media_url'];
					$pic_link = $post['media_url'];
					$alt = $post['caption'];
					?>
					<a href="<?php echo esc_attr($pic_link) ?>">
						<img src="<?php echo esc_attr($pic_src) ?>" alt="<?php echo esc_attr( $alt ); ?>">
					</a>
					<?php
					$i++;
				}
			} else {
				?>
				<p class="error"><?php esc_html_e("No photo", "ova-framework") ?></p>
				<?php
			}
			?>
		</div>
		<?php
		}
	}
}
