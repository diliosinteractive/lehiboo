<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EL_Elementor {

	protected  $elements = array();

	public function __construct() {
		
		$this->elements = $this->get_elements();

		// Register Ovatheme Category in Pane
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_ovatheme_category' ) );
		
		add_action( 'elementor/widgets/register', [ $this, 'on_widgets_registered' ] );

	}

	public function get_elements(){

		$folders = array( 'elementor' );
		$elements = array();

		foreach ( $folders as $key => $folder ) {
			$real_folder = EL_PLUGIN_INC . $folder;
			foreach ( (array) glob( $real_folder . '/class-el-' . $folder . '-*.php' ) as $key => $file ) {
				$elements[] = strtolower( str_replace( array("class-", "-"), array("","_"), basename( $file,'.php' ) ) );
			}
		}
		
		return $elements;
	}
	
	public function add_ovatheme_category() {

		\Elementor\Plugin::instance()->elements_manager->add_category(
			OVA_ELEMENTOR_CAT,
			[
				'title' => __( 'Event', 'eventlist' ),
				'icon' => 'fa fa-plug',
			]
		);

	}

	public function on_widgets_registered() {

		// include abstract elementor
		require_once EL_PLUGIN_INC . 'elementor/class-el-abstract-elementor.php';
		
		foreach ($this->elements as $key => $element) {
			require EL_PLUGIN_INC . 'elementor/class-'.str_replace( '_', '-', $element).'.php';
		}
		
	}

}

new EL_Elementor();