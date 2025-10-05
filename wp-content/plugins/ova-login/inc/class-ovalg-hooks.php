<?php defined( 'ABSPATH' ) || exit;



class Ova_Login_Hooks {


	public function __construct(){

		add_action( 'el_update_meta_user', array( $this, 'ovalg_update_meta_user' ), 10 , 2 );
	}


	public function ovalg_update_meta_user( $user_id, $post_data ){


		$ova_register_form = get_option( 'ova_register_form', array() );


		foreach ( $ova_register_form as $key => $value ) {
			$type = $value['type'];

			switch ( $type ) {
				case 'radio':
				case 'checkbox':
				$_v = isset( $post_data['ova_'.$key] ) ? recursive_sanitize_text_field( (array)$post_data['ova_'.$key] ) : [];
				update_user_meta( $user_id, 'ova_'.$key, $_v );
					break;
				
				default:
				$_v = isset( $post_data['ova_'.$key] ) ? sanitize_text_field( $post_data['ova_'.$key] ) : '';
				update_user_meta( $user_id, 'ova_'.$key, $_v );
					break;
			}
		}

	}


}

return new Ova_Login_Hooks();