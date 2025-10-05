<?php if( ! defined( 'ABSPATH' ) ) exit(); ?>
<div class="cart-customer-infor">
	<h3 class="cart_title">
		<?php esc_html_e( 'Ticket Receiver', 'eventlist' ); ?>
	</h3>
	<?php 
		$first_name = $last_name = $email = $phone = $address = '';
		if ( is_user_logged_in() ) {
			if ( ! wp_get_current_user()->first_name && ! wp_get_current_user()->last_name ) {
				$first_name = wp_get_current_user()->first_name;
				$last_name 	= wp_get_current_user()->last_name;
			}

			$email 		= wp_get_current_user()->user_email;
			$phone 		= wp_get_current_user()->user_phone;
			$address 	= wp_get_current_user()->user_address;
		}
	?>
	<ul class="info_ticket_receiver">
		
		<!-- First Name -->
		<li>
			<div class="label">
				<i class="fas fa-user"></i>
				<?php esc_html_e( 'First Name','eventlist' ); ?>
			</div>
			<div class="span first_name">
				<?php echo esc_html( $first_name ); ?>
			</div>
		</li>

		<!-- Last Name -->
		<?php if ( apply_filters( 'el_show_last_name_checkout_form', true ) ) { ?>
			<li>
				<div class="label">
					<i class="fas fa-user"></i>
					<?php esc_html_e( 'Last Name','eventlist' ); ?>
				</div>
				<div class="span last_name">
					<?php echo esc_html( $last_name ); ?>
				</div>
			</li>
		<?php } ?>

		<!-- Email -->
		<li>
			<div class="label">
				<i class="far fa-envelope"></i>
				<?php esc_html_e( 'Email','eventlist' ); ?>
			</div>
			<div class="span email">
				<?php echo esc_html( $email ); ?>
			</div>
		</li>

		<!-- Phone -->
		<?php if ( apply_filters( 'el_checkout_show_phone', true ) ): ?>
			<li>
				<div class="label">
					<i class="fas fa-phone-volume"></i>
					<?php esc_html_e( 'Phone','eventlist' ); ?>
				</div>
				<input
					type="hidden"
					name="phone_required"
					value="<?php echo esc_attr( apply_filters( 'el_checkout_required_phone', 'false' ) ); ?>"
					class="phone_required"
				/>
				<div class="span phone">
					<?php echo esc_html( $phone ); ?>
				</div>
			</li>
		<?php endif; ?>

		<!-- Address -->
		<?php if ( apply_filters( 'el_checkout_show_address', true ) ): ?>
			<li>
				<div class="label">
					<i class="fas fa-map-marker-alt"></i>
					<?php esc_html_e( 'Address','eventlist' ); ?>
				</div>
				<input 
					type="hidden"
					name="address_required"
					value="<?php echo esc_attr( apply_filters( 'el_checkout_required_address', 'false' ) ); ?>"
					class="address_required"
				/>
				
				<div class="span address">
					<?php echo esc_html( $address ); ?>
				</div>
			</li>
		<?php endif; ?>


		<?php
			$id_event 			= isset( $_GET['ide'] ) ? $_GET['ide'] : '';
			$list_ckf_output 	= get_option( 'ova_booking_form', array() );
			$terms 				= get_the_terms( $id_event, 'event_cat' );
			$term_id 			= 0;

			if ( $terms && $terms[0] ) {
				$term_id = $terms[0]->term_id;
			}

			$category_ckf_type = get_term_meta( $term_id, '_category_ckf_type', true ) ? get_term_meta( $term_id, '_category_ckf_type', true) : 'all';
			$category_checkout_field = get_term_meta( $term_id, '_category_checkout_field', true) ? get_term_meta( $term_id, '_category_checkout_field', true) : array();
			$list_key_checkout_field = [];

			if ( is_array( $list_ckf_output ) && ! empty( $list_ckf_output ) ) {
				foreach( $list_ckf_output as $key => $field ) {
					if ( array_key_exists('enabled', $field) && $field['enabled'] == 'on' ) {
						if ( $category_ckf_type === 'special' && ! in_array( $key, $category_checkout_field ) ) continue;
						
						$list_key_checkout_field[] = $key;

						if ( array_key_exists( 'required', $field ) && $field['required'] == 'on' ) {
							$class_required = 'required';
						} else {
							$class_required = '';
						}

						$default = $field['default'];

						if ( $field['type'] === 'select' ) {
							$ova_options_key 	= $field['ova_options_key'];
							$ova_options_text 	= $field['ova_options_text'];

							if ( ! empty( $ova_options_key ) && is_array( $ova_options_key ) ) {
								if ( ! $default && $field['required'] === 'on' ) $default = $ova_options_key[0];

								$op_k = array_search( $default, $ova_options_key );

								if ( ! is_bool( $op_k ) ) {
                                    if ( isset( $ova_options_text[$op_k] ) && $ova_options_text[$op_k] ) {
                                    	$default = $ova_options_text[$op_k];
                                    }
                                }
							}
						}

						if ( $field['type'] === 'radio' ) {
							$ova_radio_key 	= $field['ova_radio_key'];
							$ova_radio_text = $field['ova_radio_text'];

							if ( ! empty( $ova_radio_key ) && is_array( $ova_radio_key ) ) {
								if ( ! $default && $field['required'] === 'on' ) $default = $ova_radio_key[0];

								$radio_k = array_search( $default, $ova_radio_key );

								if ( ! is_bool( $radio_k ) ) {
                                    if ( isset( $ova_radio_text[$radio_k] ) && $ova_radio_text[$radio_k] ) {
                                    	$default = $ova_radio_text[$radio_k];
                                    }
                                }
							}
						}

						if ( $field['type'] === 'checkbox' ) {
							$ova_checkbox_key 	= $field['ova_checkbox_key'];
							$ova_checkbox_text 	= $field['ova_checkbox_text'];

							if ( ! empty( $ova_checkbox_key ) && is_array( $ova_checkbox_key ) ) {
								if ( ! $default && $field['required'] === 'on' ) $default = $ova_checkbox_key[0];
								
								$checkbox_k = array_search( $default, $ova_checkbox_key );

								if ( ! is_bool( $checkbox_k ) ) {
                                    if ( isset( $ova_checkbox_text[$checkbox_k] ) && $ova_checkbox_text[$checkbox_k] ) {
                                    	$default = $ova_checkbox_text[$checkbox_k];
                                    }
                                }
							}
						}
				?>
					<li>
						<div class="label">
							<i class="fas fa-plus"></i>
							<?php echo esc_html( $field['label'] ); ?>
						</div>
						<div class="span <?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $default ); ?>
						</div>
					</li>
				<?php
					}//endif
				}//end foreach
			}//end if

		?>
	</ul>
</div>


