<?php

defined( 'ABSPATH' ) || exit;

class EL_Admin_Metabox_Payout extends EL_Abstract_Metabox {
	
	public function __construct(){

		$this->_id = 'metabox_Payout';
		$this->_title = esc_html__( 'Payout','eventlist' );
		$this->_screen = array( 'payout' );
		$this->_output = EL_PLUGIN_INC . 'admin/views/metaboxes/metabox-payout.php';
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
			
			// Update When Admin Canceled Withdraw
			if( $name == $this->_prefix.'withdrawal_status' && $value == 'Canceled' ){

				$id_author = get_post_field('post_author', $post_id);

				$bookings = EL_Booking::instance()->get_list_bookings_profit_wating( $id_author );

				if($bookings->have_posts() ) : while ( $bookings->have_posts() ) : $bookings->the_post();
					update_post_meta( get_the_ID(), $this->_prefix.'profit_status', '' );
				endwhile;endif; wp_reset_postdata();

			}else if( $name == $this->_prefix.'withdrawal_status' && $value == 'Completed' ){

				$id_author = get_post_field('post_author', $post_id);

				$bookings = EL_Booking::instance()->get_list_bookings_profit_wating( $id_author );

				if($bookings->have_posts() ) : while ( $bookings->have_posts() ) : $bookings->the_post();
					update_post_meta( get_the_ID(), $this->_prefix.'profit_status', 'Completed' );
				endwhile;endif; wp_reset_postdata();
				
			}
		}
		

	}




}