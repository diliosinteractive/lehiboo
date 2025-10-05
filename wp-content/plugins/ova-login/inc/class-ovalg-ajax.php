<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists("Ova_Login_Ajax") ) {
	
	class Ova_Login_Ajax {
		public function __construct(){

			$ajax_hooks = [
				'ova_lg_sortable_register_field',
				'ovalg_vendor_approve_show_info',
				'ovalg_vendor_approve_submit',
				'ovalg_vendor_reject_submit',
			];

			foreach ( $ajax_hooks as $hook_name ) {
				add_action( 'wp_ajax_'.$hook_name, array( $this, $hook_name ) );
			}
			
		}

		public function ova_lg_sortable_register_field(){
			if ( ! isset( $_POST['pos'] ) ) {
				wp_die();
			}
			$pos = $_POST['pos'];
			$ova_register_form = get_option( 'ova_register_form', array() );

			foreach ( $pos as $name => $position ) {
				$ova_register_form[$name]['position'] = $position;
			}

			$ova_register_form = ovalg_sortby_position( $ova_register_form );

			update_option( 'ova_register_form', $ova_register_form );

			$list_fields = get_option( 'ova_register_form', array() );

			ovalg_get_list_fields( $list_fields );

			wp_die();
		}

		public function ovalg_vendor_approve_show_info(){
			$post_data = $_POST;

			$nonce 		= isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$user_id 	= isset( $post_data['user_id'] ) ? sanitize_text_field( $post_data['user_id'] ) : '';
			$user 		= get_user_by( 'id', $user_id );

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ovalg_show_info_vendor' ) || ! $user ) {
				wp_die();
			}
			$show_first_name = OVALG_Settings::show_first_name() ? OVALG_Settings::show_first_name() : 'yes';
			$show_last_name = OVALG_Settings::show_last_name() ? OVALG_Settings::show_last_name() : 'yes';
			$show_job 		= OVALG_Settings::show_job() ? OVALG_Settings::show_job() : 'yes';
			$show_website 	= OVALG_Settings::show_website() ? OVALG_Settings::show_website() : 'no';
			$show_address 	= OVALG_Settings::show_address() ? OVALG_Settings::show_address() : 'yes';

			$user_job  		 = get_user_meta( $user_id, 'user_job', true ) ? get_user_meta( $user_id, 'user_job', true ) : '';
			$user_phone      = get_user_meta( $user_id, 'user_phone', true ) ? get_user_meta( $user_id, 'user_phone', true ) : '';
			$user_address    = get_user_meta( $user_id, 'user_address', true ) ? get_user_meta( $user_id, 'user_address', true ) : '';

			$user_meta_field = get_option( 'ova_register_form' );

			ob_start();
			?>
			<table class="form-table">
                <tbody>
                    <tr scope="row">
                        <td><?php esc_html_e( 'Username:','ova-login' ); ?></td>
                        <td><?php echo esc_html( $user->user_login ); ?></td>
                    </tr>

                    <?php if ( $show_first_name === 'yes' ): ?>
                    	<tr scope="row">
	                        <td><?php esc_html_e( 'First Name:','ova-login' ); ?></td>
	                        <td><?php echo esc_html( $user->first_name ); ?></td>
	                    </tr>
                    <?php endif; ?>

                    <?php if ( $show_last_name === 'yes' ): ?>
                    	<tr scope="row">
	                        <td><?php esc_html_e( 'Last Name:','ova-login' ); ?></td>
	                        <td><?php echo esc_html( $user->last_name ); ?></td>
	                    </tr>
                    <?php endif; ?>
                    
                    <tr scope="row">
                        <td><?php esc_html_e( 'Display Name:','ova-login' ); ?></td>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                    </tr>

                    <?php if ( $show_job === 'yes' ):?>
                    	<tr scope="row">
	                        <td><?php esc_html_e( 'Job:','ova-login' ); ?></td>
	                        <td><?php echo esc_html( $user_job ); ?></td>
	                    </tr>
                    <?php endif; ?>

                    <?php if ( $show_website === 'yes' ):?>
                    	<tr scope="row">
	                        <td><?php esc_html_e( 'Website:','ova-login' ); ?></td>
	                        <td><?php echo esc_html( $user->user_url ); ?></td>
	                    </tr>
                    <?php endif; ?>

                    <?php if ( $show_address === 'yes' ):?>
                    	<tr scope="row">
	                        <td><?php esc_html_e( 'Address:','ova-login' ); ?></td>
	                        <td><?php echo esc_html( $user_address ); ?></td>
	                    </tr>
                    <?php endif; ?>

                    <?php if ( ! empty( $user_meta_field ) && is_array( $user_meta_field ) ): ?>
                    	<?php foreach ( $user_meta_field as $name => $field ):
                    		$check_display_field = $field['used_for'] === 'user' ? false : true;
                    	?>
                    		<?php if ( $check_display_field && $field['enabled'] === "on" ):
                    			$user_meta_val = get_user_meta( $user_id, 'ova_'.$name, true );
                    			?>
                    			
                    			<?php switch ( $field['type'] ) {
                    				case 'text':
                    				case 'tel';
                    				case 'email';
                    				case 'textarea';
                    				?>
                    				<tr scope="row">
				                        <td><?php echo esc_html( $field['label'] ); ?></td>
				                        <td><?php echo esc_html( $user_meta_val ); ?></td>
				                    </tr>
                    				<?php
                    					break;
                    				case 'select':
                    				$ova_options_key 	= $field['ova_options_key'];
									$ova_options_text 	= $field['ova_options_text'];
									$selected = $user_meta_val;
									$select_val = '';
									if ( ! empty( $ova_options_key ) && is_array( $ova_options_key ) ) {
										$select_key = array_search( $selected , $ova_options_key);
										$select_val = isset( $ova_options_text[$select_key] ) ? $ova_options_text[$select_key] : '';
									}
                    				?>
                    				<tr scope="row">
				                        <td><?php echo esc_html( $field['label'] ); ?></td>
				                        <td><?php echo esc_html( $select_val ); ?></td>
				                    </tr>
                    				<?php
                    					break;
                    				case 'file':
                    					$attachment_id 	= $user_meta_val;
										$file_name 		= basename( get_attached_file( $attachment_id ) );
										$file_url 		= wp_get_attachment_url( $attachment_id );
										?>
										<tr scope="row">
					                        <td><?php echo esc_html( $field['label'] ); ?></td>
					                        <td>
					                        	<?php if ( $attachment_id && get_post( $attachment_id ) ):
					                        		$mime_type = get_post_mime_type( $attachment_id );
					                        		if ( ! str_contains($mime_type, "image") ) {
														?>
														<span class="file-name"><a href="<?php echo esc_url( $file_url ); ?>" target="_blank"><?php echo esc_html( $file_name ); ?></a></span>
														<?php
													} else {
														$image_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
														?>
														<img class="ova__thumbnail" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $file_name ); ?>">
														<?php
													}
					                        	endif; ?>
					                        </td>
					                    </tr>
										<?php
                    					break;
                    				case 'checkbox':
                    					$ova_checkbox_key 	= $field['ova_checkbox_key'];
										$ova_checkbox_text 	= $field['ova_checkbox_text'];
										$checked = $user_meta_val;
										$check_val = [];
										if ( $checked && $ova_checkbox_key && is_array( $ova_checkbox_key ) && $ova_checkbox_text && is_array( $ova_checkbox_text ) ) {

											if ( is_array( $checked ) ) {
												foreach ($checked as $value) {
													$index = array_search($value, $ova_checkbox_key);
													$check_val[] = isset( $ova_checkbox_text[$index] ) ? $ova_checkbox_text[$index] : '';
												}
											} else {
												$index = array_search($checked, $ova_checkbox_key);
												$check_val[] = isset( $ova_checkbox_text[$index] ) ? $ova_checkbox_text[$index] : '';
											}
											
										}
										?>
										<tr scope="row">
					                        <td><?php echo esc_html( $field['label'] ); ?></td>
					                        <td><?php echo esc_html( implode(", ", $check_val) ); ?></td>
					                    </tr>
										<?php
                    					break;
                    				case 'radio':
                    					$ova_radio_key 	= $field['ova_radio_key'];
										$ova_radio_text = $field['ova_radio_text'];
										$choosed = $user_meta_val;
										$choose_val = '';
										if ( $choosed ) {
											$index = array_search($choosed, $ova_radio_key);
											$choose_val = isset( $ova_radio_text[$index] ) ? $ova_radio_text[$index] : '';
										}
                    					
                    					?>
                    					<tr scope="row">
					                        <td><?php echo esc_html( $field['label'] ); ?></td>
					                        <td><?php echo esc_html( $choose_val ); ?></td>
					                    </tr>
                    					<?php
                    					break;
                    				default:
                    					break;

                    			} ?>

                    		<?php endif; ?>
                    		
                    	<?php endforeach; ?>
                    <?php endif; ?>

                </tbody>
            </table>
			<?php
			echo ob_get_clean();
			wp_die();
		}

		public function ovalg_vendor_approve_submit(){
			$post_data = $_POST;
			
			$nonce 		= isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$user_id 	= isset( $post_data['user_id'] ) ? sanitize_text_field( $post_data['user_id'] ) : '';
			$redirect_url = isset( $post_data['url'] ) ? sanitize_url( $post_data['url'] ) : '';
			$user 		= new WP_User( $user_id );

			if ( ! $nonce || ! $user || ! wp_verify_nonce( $nonce, 'ovalg_vendor_approve_action' ) ) {
				wp_die();
			}

			$redirect_url = add_query_arg( 'action', 'approve', $redirect_url );

			if ( ! update_user_meta( $user_id, 'vendor_status', 'approve' ) ) {
				$redirect_url = add_query_arg( 'status', 'error', $redirect_url );
				$redirect_url = add_query_arg( 'send_mail', 'error', $redirect_url );
			} else {
				$current_time = current_time( 'timestamp' );
				update_user_meta( $user_id, 'update_vendor_time', $current_time);
				$redirect_url = add_query_arg( 'status', 'success', $redirect_url );

				$user->set_role( 'el_event_manager' );

				if ( function_exists('EL') ) {
					$enable_package = EL()->options->package->get( 'enable_package', 'yes' );
					$default_package = EL()->options->package->get( 'package' );
					$current_package = get_user_meta( $user_id, 'package', true );

					if ( ! $current_package && $enable_package == 'yes' && $default_package ){
						$pid = EL_Package::instance()->get_package( $default_package );
						EL_Package::instance()->add_membership( $pid['id'], $user_id, $status = 'new' );
					}
				}

				// send mail
				$user_email = $user->user_email; 
				if ( ! ova_admin_send_mail_vendor_approve( $user_email ) ) {
					$redirect_url = add_query_arg( 'send_mail', 'error', $redirect_url );
				} else {
					$redirect_url = add_query_arg( 'send_mail', 'success', $redirect_url );
				}
			}
			
			$result = $redirect_url;
			echo $result;
			wp_die();
		}

		public function ovalg_vendor_reject_submit(){
			$post_data = $_POST;

			$nonce 		= isset( $post_data['nonce'] ) ? sanitize_text_field( $post_data['nonce'] ) : '';
			$user_id 	= isset( $post_data['user_id'] ) ? sanitize_text_field( $post_data['user_id'] ) : '';
			$mess 		= isset( $post_data['mess'] ) ? sanitize_text_field( $post_data['mess'] ) : '';
			$redirect_url = isset( $post_data['url'] ) ? sanitize_url( $post_data['url'] ) : '';
			$user 		= new WP_User( $user_id );

			if ( ! $nonce || ! $user || ! wp_verify_nonce( $nonce, 'ovalg_vendor_approve_action' ) ) {
				wp_die();
			}

			$redirect_url = add_query_arg( 'action', 'reject', $redirect_url );

			if ( ! update_user_meta( $user_id, 'vendor_status', 'reject' ) ) {
				$redirect_url = add_query_arg( 'status', 'error', $redirect_url );
				$redirect_url = add_query_arg( 'send_mail', 'error', $redirect_url );
			} else {
				$current_time = current_time( 'timestamp' );
				update_user_meta( $user_id, 'update_vendor_time', $current_time);
				$redirect_url = add_query_arg( 'status', 'success', $redirect_url );
				
				$user->set_role('subscriber');
				// send mail
				$user_email = $user->user_email; 
				if ( ! ova_admin_send_mail_vendor_reject( $user_email, $mess ) ) {
					$redirect_url = add_query_arg( 'send_mail', 'error', $redirect_url );
				} else {
					$redirect_url = add_query_arg( 'send_mail', 'success', $redirect_url );
				}
			}
			
			$result = $redirect_url;
			echo $result;
			wp_die();
		}

	}

	new Ova_Login_Ajax();

}