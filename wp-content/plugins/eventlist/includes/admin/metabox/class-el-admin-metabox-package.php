<?php

defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Package extends EL_Abstract_Metabox {
	
	public function __construct(){

		$this->_id = 'metabox_package';
		$this->_title = esc_html__( 'Package Fields','eventlist' );
		$this->_screen = array( 'package' );
		$this->_output = EL_PLUGIN_INC . 'admin/views/metaboxes/metabox-package.php';
		$this->_prefix = OVA_METABOX_EVENT;

		parent::__construct();

		add_action( 'el_mb_proccess_update_meta', array( $this, 'update' ), 10, 2 );

	}

	public function update( $post_id, $post_data ){

		if( empty($post_data) ) exit();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( !isset( $post_data ) )
			return;

		if ( !isset( $post_data['ova_metaboxes'] ) || !wp_verify_nonce( $post_data['ova_metaboxes'], 'ova_metaboxes' ) )
			return;


		foreach ($post_data as $name => $value) {

			if ( strpos( $name, $this->_prefix ) !== 0 ) continue;
			
			update_post_meta( $post_id, $name, $value );
		}

	}

}