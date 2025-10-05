<?php if ( !defined( 'ABSPATH' ) ) exit();

class EL_Admin_Metaboxes {

	public function __construct() {
		
		
		add_action( 'admin_init', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
	}

	/* add metaboxes */

	public function add_meta_boxes() {
		
		new EL_Admin_Metabox_Basic();
		new EL_Admin_Metabox_Booking();
		new EL_Admin_Metabox_Ticket();
		new EL_Admin_Metabox_Package();
		new EL_Admin_Metabox_Membership();
		new EL_Admin_Metabox_Payout();
		
	}

	

	public static function save_post( $post_id, $post, $update ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( !isset( $_POST ) )
			return;

		if ( !isset( $_POST['ova_metaboxes'] ) || !wp_verify_nonce( $_POST['ova_metaboxes'], 'ova_metaboxes' ) )
			return;

		if( isset( $_POST[OVA_METABOX_EVENT.'membership_start_date'] ) ){
			$_POST[OVA_METABOX_EVENT.'membership_start_date'] = strtotime( $_POST[OVA_METABOX_EVENT.'membership_start_date'] );
		}

		if( isset( $_POST[OVA_METABOX_EVENT.'membership_end_date'] ) && $_POST[OVA_METABOX_EVENT.'membership_end_date'] != '-1' ){
			$_POST[OVA_METABOX_EVENT.'membership_end_date'] = strtotime( $_POST[OVA_METABOX_EVENT.'membership_end_date'] );
		}

		do_action( 'el_mb_proccess_update_meta', $post_id, $_POST );
	}

}

new EL_Admin_Metaboxes();