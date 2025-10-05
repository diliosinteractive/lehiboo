<?php defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Membership extends EL_Abstract_Metabox {
	
	public function __construct(){

		$this->_id = 'metabox_membership';
		$this->_title = esc_html__( 'Membership Fields','eventlist' );
		$this->_screen = array( 'manage_membership' );
		$this->_output = EL_PLUGIN_INC . 'admin/views/metaboxes/metabox-membership.php';
		$this->_prefix = OVA_METABOX_EVENT;

		parent::__construct();

		// Override do_action
		add_action( 'el_mb_proccess_update_meta', array( $this, 'update' ), 10, 2 );

	}

	public function update( $post_id, $post_data ){

		if( empty($post_data) ) exit();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( !isset( $post_data ) )
			return;

		foreach ($post_data as $name => $value) {

			if ( strpos( $name, $this->_prefix ) !== 0 ) continue;
			
			update_post_meta( $post_id, $name, $value );
		}
	}

}