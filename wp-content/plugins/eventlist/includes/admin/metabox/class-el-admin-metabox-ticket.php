<?php

defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Ticket extends EL_Abstract_Metabox {
	
	public function __construct(){

		$this->_id = 'metabox_ticket';
		$this->_title = esc_html__( 'Ticket','eventlist' );
		$this->_screen = array( 'el_tickets' );
		$this->_output = EL_PLUGIN_INC . 'admin/views/metaboxes/metabox-ticket.php';
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

		foreach ( $post_data as $name => $value ) {

			if ( $name === 'el_ticket_edit_date_start' ) {
				$date_start = $value;
				$time_start = isset( $post_data['el_ticket_edit_time_start'] ) ? $post_data['el_ticket_edit_time_start'] : '';

				if ( $date_start && $time_start ) {
					update_post_meta( $post_id, $this->_prefix.'date_start', strtotime( $date_start . ' ' . $time_start ) );
					$replace_date_status = absint( get_post_meta( $post_id, $this->_prefix.'replace_date_status', true ) );
					update_post_meta( $post_id, $this->_prefix.'replace_date_status', $replace_date_status + 1 );
				}
			}

			if ( $name === 'el_ticket_edit_date_end' ) {
				$date_end = $value;
				$time_end = isset( $post_data['el_ticket_edit_time_end'] ) ? $post_data['el_ticket_edit_time_end'] : '';

				if ( $date_end && $time_end ) {
					update_post_meta( $post_id, $this->_prefix.'date_end', strtotime( $date_end . ' ' . $time_end ) );
					$replace_date_status = absint( get_post_meta( $post_id, $this->_prefix.'replace_date_status', true ) );
					update_post_meta( $post_id, $this->_prefix.'replace_date_status', $replace_date_status + 1 );
				}
			}

			if ( strpos( $name, $this->_prefix ) !== 0 ) continue;
			
			update_post_meta( $post_id, $name, $value );
		}

	}

}