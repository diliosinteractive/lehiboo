<?php if ( !defined( 'ABSPATH' ) ) exit();

global $post;

$id_booking 	= get_the_ID();
$id_event 		= $this->get_mb_value('id_event') ? $this->get_mb_value('id_event') : '';
$id_cal 		= $this->get_mb_value('id_cal') ? $this->get_mb_value('id_cal') : '';
$seat_option 	= get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true ) ? get_post_meta( $id_event, OVA_METABOX_EVENT . 'seat_option', true ) : 'none';
$arr_seat 		= get_post_meta( $id_booking, OVA_METABOX_EVENT . 'arr_seat', true ) ? get_post_meta( $id_booking, OVA_METABOX_EVENT . 'arr_seat', true ) : [];
$arr_area 		= get_post_meta( $id_booking, OVA_METABOX_EVENT . 'arr_area', true ) ? get_post_meta( $id_booking, OVA_METABOX_EVENT . 'arr_area', true ) : [];
$format 		= el_date_time_format_js();
$placeholder 	= date_i18n(el_date_time_format_js_reverse($format), '1577664000' );

// Get all events no pagination
$events 		= el_all_events();
$screen 		= get_current_screen();
$pdf_invoices 	= EL()->options->invoice->get('invoice_mail_enable', 'no' );
$cart 			= $this->get_mb_value( 'cart' );
$extra_service 	= $this->get_mb_value( 'extra_service' );
$transaction_id = get_post_meta( $id_booking, OVA_METABOX_EVENT.'transaction_id', true );

?>
<div class="el_booking_detail">
	<div class="ova_row">
		<p class="success status"></p>
		<p class="error status"></p>
	</div>
	<div class="ova_row">
		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Booking ID",  "eventlist" ); ?>: </strong>
				#<?php echo esc_html( $post->ID ); ?>
			</label>
			<br><br>
		</div>
		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Select Event *",  "eventlist" ); ?>: </strong>
				<select name="<?php echo esc_attr( $this->get_mb_name('id_event') ); ?>" class="id_event">
					<option value="">-------------------</option>
					<?php foreach ( $events as $key => $value ) { ?>
						<option value="<?php echo esc_attr( $value->ID ); ?>"<?php selected( $value->ID, $id_event ); ?>>
							<?php echo esc_html( $value->post_title ); ?>
						</option>
					<?php } ?>
				</select>
			</label>
			<br><br>
		</div>
		<div class="ova_row id_cal" data-id_cal="<?php echo esc_attr( $id_cal ); ?>">
			<?php if ( $post->ID ) { ?>
				<?php $list_calendar = get_arr_list_calendar_by_id_event( $id_event ); ?>
				<label>
				<strong><?php esc_html_e( "Event Calendar *",  "eventlist" ); ?>: </strong>
					<select name="<?php echo esc_attr( OVA_METABOX_EVENT.'id_cal' ); ?>" >
						<?php foreach ( $list_calendar as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $value['calendar_id'] ); ?>"<?php selected( $value['calendar_id'], $id_cal ); ?>>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime($value['date']) ) ); ?>
							</option>
						<?php } ?>
					</select>
				</label>
				<br><br>
			<?php } ?>
		</div>
		<div class="ova_row" >
			<label for="">
				<strong><?php esc_html_e( "Status", "eventlist" ); ?>: </strong>
				<?php
					$status_booking = $this->get_mb_value( 'status');
				?>
				<select name="<?php echo esc_attr( $this->get_mb_name( 'status' ) ); ?>">
					<option value="Completed"<?php selected( 'Completed', $status_booking, 'selected' ); ?>>
						<?php esc_html_e( 'Completed', 'eventlist' ); ?>
					</option>
					<option value="Pending"<?php selected( 'Pending', $status_booking, 'selected' ); ?>>
						<?php esc_html_e( 'Pending', 'eventlist' ); ?>
					</option>
					<option value="Canceled"<?php selected( 'Canceled', $status_booking, 'selected' ); ?>>
						<?php esc_html_e( 'Canceled', 'eventlist' ); ?>
					</option>
					<option value="Expired"<?php selected( 'Expired', $status_booking, 'selected' ); ?>>
						<?php esc_html_e( 'Expired', 'eventlist' ); ?>
					</option>
				</select>
			</label>
			<br><br>
		</div>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Name",  "eventlist" ); ?>: </strong>
			<input 
				type="text"
				class="name"
				value="<?php echo esc_attr( $this->get_mb_value('name') ); ?>"
				placeholder="<?php esc_attr_e( 'Customer Name', 'eventlist' ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name('name') ); ?>"
				autocomplete="off" autocorrect="off" autocapitalize="none"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Phone", "eventlist" ); ?>: </strong>
			<input
				type="text"
				class="phone"
				value="<?php echo esc_attr( $this->get_mb_value('phone') ); ?>"
				placeholder="<?php esc_attr_e( '+1 234 567 99', 'eventlist' ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name('phone') ); ?>"
				autocomplete="off" autocorrect="off" autocapitalize="none"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Email *", "eventlist" ); ?>: </strong>
			<input
				type="text"
				class="email"
				value="<?php echo esc_attr( $this->get_mb_value('email') ); ?>"
				placeholder="<?php esc_attr_e( 'example@email.com', 'eventlist' ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name('email') ); ?>"
				autocomplete="off" autocorrect="off" autocapitalize="none"
			/>
		</label>
		<br><br>
	</div>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Address", "eventlist" ); ?>: </strong>
			<input
				type="text"
				class="address"
				value="<?php echo esc_attr( $this->get_mb_value('address') ); ?>"
				placeholder="<?php esc_attr_e( 'New York, NY, USA', 'eventlist' ); ?>"
				name="<?php echo esc_attr( $this->get_mb_name('address') ); ?>"
				autocomplete="off" autocorrect="off" autocapitalize="none"
			/>
		</label>
		<br><br>
	</div>
	<?php
		$id_booking 			= $post->ID;
		$list_ckf_output 		= get_option( 'ova_booking_form', array() );
		$data_checkout_field 	= $this->get_mb_value( 'data_checkout_field' );

		if ( $screen->action == 'add' ) {
			$data_checkout_field = [];

			foreach ( $list_ckf_output as $key_1 => $val ) {
				if ( array_key_exists( 'enabled', $val ) && $val['enabled'] == 'on' ) {
					$data_checkout_field[$key_1] = $val['default'];
				}
			}
		} else {
			$data_checkout_field = ! empty( $data_checkout_field ) ? json_decode( $data_checkout_field , true) : [];
		}

		$list_key_checkout_field 	= [];
		$list_type_checkout_field 	= [];

		if ( is_array( $list_ckf_output ) && ! empty( $list_ckf_output ) ) {
			$special_fields = [ 'textarea', 'select', 'radio', 'checkbox', 'file' ];

			foreach ( $list_ckf_output as $key => $field ) {
				if ( array_key_exists( $key, $data_checkout_field ) && array_key_exists( 'enabled', $field ) && $field['enabled'] == 'on' ) {
					$list_key_checkout_field[] = $key;
					$list_type_checkout_field[$key] = $field['type'];

					if ( array_key_exists( 'required', $field ) && $field['required'] == 'on' ) {
						$class_required = 'required';
					} else {
						$class_required = '';
					}
			?>
				<div class="ova_row">
					<label for="<?php echo esc_attr( $key ); ?>">
						<strong><?php echo esc_html( $field['label'] ); ?>: </strong>
						<?php if ( ! in_array( $field['type'] , $special_fields ) ) { ?>
							<input
								id="<?php echo esc_attr( $key ); ?>"
								type="text"
								name="<?php echo esc_attr( $key ); ?>"
								class="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>"
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								value="<?php echo esc_attr( $data_checkout_field[$key] ); ?>"
							/>
						<?php } ?>

						<?php if ( $field['type'] === 'textarea' ) { ?>
							<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" class=" <?php echo esc_attr( $key ) ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" cols="50" rows="8"><?php echo esc_html( $data_checkout_field[$key] ); ?></textarea>
						<?php } ?>

						<?php if ( $field['type'] === 'select' ) { 
							$ova_options_key = $ova_options_text = [];

							if ( array_key_exists( 'ova_options_key', $field ) ) {
								$ova_options_key = $field['ova_options_key'];
							}

							if ( array_key_exists( 'ova_options_text', $field ) ) {
								$ova_options_text = $field['ova_options_text'];
							}

							?>
							<select
								id="<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( $key ); ?>" 
								class="ova_select <?php echo esc_attr( $key ); ?> <?php echo esc_attr( $field['class'] . ' ' . $class_required ); ?>">
								<?php 

								if ( ! empty( $ova_options_text ) && is_array( $ova_options_text ) ) { 
									foreach ( $ova_options_text as $key_2 => $value ) { 
									?>
										<option value="<?php echo esc_attr( $ova_options_key[$key_2] ); ?>" <?php selected( $ova_options_key[$key_2], $data_checkout_field[$key] ); ?>>
											<?php echo esc_html( $value ); ?>
										</option>
								<?php 
									} //end foreach
								}//end if
							?>
							</select>
						<?php } ?>

						<?php if ( $field['type'] === 'radio' ) {
							$radio_key = $radio_text = [];

							if ( array_key_exists( 'ova_radio_key', $field ) ) {
								$radio_key = $field['ova_radio_key'];
							}

							if ( array_key_exists( 'ova_radio_text', $field ) ) {
								$radio_text = $field['ova_radio_text'];
							}

							if ( ! empty( $radio_key ) && is_array( $radio_key ) ) {
								$default = $data_checkout_field[$key];
							?>
								<div class="el-radio">
							<?php
								foreach ( $radio_key as $k => $val ) {
									$checked = '';
									if ( $default === $val ) $checked = 'checked';
								?>
									<div class="el-ckf-radio <?php echo esc_attr( $class_required ); ?>">
										<input 
											type="radio" 
											id="<?php echo 'el-ckf-radio'.esc_attr( $k ); ?>" 
											name="<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr( $val ); ?>" 
											<?php echo esc_html( $checked ); ?>
										/>
										<label for="<?php echo 'el-ckf-radio'.esc_attr( $k ); ?>">
											<?php echo isset( $radio_text[$k] ) ? esc_html( $radio_text[$k] ) : ''; ?>
										</label>
									</div>
								<?php } ?>
								</div>
						<?php } } ?>

						<?php if ( $field['type'] === 'checkbox' ) {
							$checkbox_key = $checkbox_text = [];

							if ( array_key_exists( 'ova_checkbox_key', $field ) ) {
								$checkbox_key = $field['ova_checkbox_key'];
							}

							if ( array_key_exists( 'ova_checkbox_text', $field ) ) {
								$checkbox_text = $field['ova_checkbox_text'];
							}

							if ( ! empty( $checkbox_key ) && is_array( $checkbox_key ) ) {
								$default = [];

								if ( $data_checkout_field[$key] ) {
									$default = explode( ', ', $data_checkout_field[$key] );
								}
								
							?>
								<div class="el-checkbox">
							<?php
								foreach ( $checkbox_key as $k => $val ) {
									$checked = '';

									if ( in_array( $val, $default ) ) $checked = 'checked';
								?>
									<div class="el-ckf-checkbox <?php echo esc_attr( $class_required ); ?>">
										<input 
											type="checkbox" 
											id="<?php echo esc_attr('el-ckf-checkbox'. $k ); ?>" 
											name="<?php echo esc_attr( $key.'['.$val.']' ); ?>"
											data-name="<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr( $val ); ?>"
											<?php echo esc_html( $checked ); ?>
										/>
										<label for="<?php echo esc_attr( 'el-ckf-checkbox'.$k ); ?>">
											<?php echo isset( $checkbox_text[$k] ) ? esc_html( $checkbox_text[$k] ) : ''; ?>
										</label>
									</div>
								<?php } ?>
								</div>
						<?php } } ?>

						<?php if ( $field['type'] === 'file' ) { ?>
							<span class="el-ckf-file">
								<a href="<?php echo esc_url( $data_checkout_field[$key] ); ?>" target="_blank">
									<?php echo esc_html( wp_basename( $data_checkout_field[$key] ) ); ?>
								</a>
							</span>
						<?php } ?>
					</label>
					<br><br>
				</div>
			<?php
				}//endif
			}//end foreach
		}//end if
	?>
	<input
		type="hidden"
		id="el_meta_booking_list_key_checkout_field"
		value="<?php echo esc_attr( json_encode( $list_key_checkout_field ) ); ?>"
		data-type="<?php echo esc_attr( json_encode( $list_type_checkout_field ) ); ?>"
	/>
	<input
		type="hidden"
		id="bk_data_checkout_field"
		name="<?php echo esc_attr( $this->get_mb_name( 'data_checkout_field' ) ); ?>"
		value="<?php echo esc_attr( json_encode( $data_checkout_field ) ); ?>"
	/>

	<!-- extra services -->
	<?php
	$extra_service_display = el_extra_sv_get_info_booking( $extra_service );
	if ( ! empty( $extra_service_display ) ) {
	?>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Extra Services", "eventlist" ); ?>: </strong>
			<span class="extra-service"><?php echo wp_kses_post( $extra_service_display ); ?></span>
			
		</label>
		<br>
		<br>
	</div>
	<?php } ?>

	<?php if ( $this->get_mb_value( 'title_event' ) == '' || ! empty( $this->get_mb_value('coupon') ) ) { ?>
		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Coupon", "eventlist" ); ?>: </strong>
				<input
					type="text"
					class="coupon"
					placeholder="<?php esc_html_e( 'code', 'eventlist' ); ?>"
					value="<?php echo esc_attr( $this->get_mb_value('coupon') ); ?>"
					name="<?php echo esc_attr( $this->get_mb_name('coupon') ); ?>"
					autocomplete="off" autocorrect="off" autocapitalize="none"
				/>
			</label>
			<br><br>
		</div>
	<?php } ?>
	<div class="ova_row">
		<label>
			<strong><?php esc_html_e( "Payment Method", "eventlist" ); ?>: </strong>
			<?php if ( $this->get_mb_value( 'title_event' ) ) {
				echo $this->get_mb_value( 'payment_method' );
			?>
				<input
					type="hidden"
					class="payment_method"
					value="<?php echo esc_attr( $this->get_mb_value( 'payment_method' ) ); ?>"
					name="<?php echo esc_attr( $this->get_mb_name('payment_method') ); ?>"
					autocomplete="off" autocorrect="off" autocapitalize="none"
				/>
			<?php } else {
				esc_html_e('Manual', 'eventlist'); ?>
				<input
					type="hidden"
					class="payment_method"
					value="<?php echo esc_attr('Manual'); ?>"
					name="<?php echo esc_attr( $this->get_mb_name('payment_method') ); ?>"
					autocomplete="off" autocorrect="off" autocapitalize="none"
				/>
			<?php } ?>
		</label>
		<br>
		<br>
	</div>
	<?php if ( $transaction_id ): ?>
	<div class="ova_row">
		<label><strong><?php esc_html_e( 'Transaction ID:', 'eventlist' ); ?></strong></label>
		<?php echo esc_html( $transaction_id ); ?>
		<br>
		<br>
	</div>
	<?php endif; ?>
	<div class="ova_row">
		<strong><?php esc_html_e( "Cart",  "eventlist" ); ?>: </strong>
		<input
			type="hidden"
			class="seat_option_type"
			name="seat_option_type"
			value="<?php echo esc_attr( $seat_option ); ?>"
		/>
		<input
			type="hidden"
			class="ova_event_id"
			name="ova_event_id"
			value="<?php echo esc_attr( $id_event ); ?>"
		/>
		<table class="cart">
			<thead>
				<?php if ( $seat_option == 'none' ): ?>
					<tr class="seat_option_none">
						<th class="name"><?php esc_html_e( 'Ticket', 'eventlist' ); ?></th>
						<th class="qty"><?php esc_html_e( 'Quantity', 'eventlist' ); ?></th>
						<th class="sub-total"><?php esc_html_e( 'Sub Total', 'eventlist' ); ?></th>
					</tr>
				<?php elseif ( $seat_option == 'simple' ): ?>
					<tr class="seat_option_simple">
						<th class="name"><?php esc_html_e( 'Ticket', 'eventlist' ); ?></th>
						<th class="qty"><?php esc_html_e( 'Quantity', 'eventlist' ); ?></th>
						<th class="sub-total"><?php esc_html_e( 'Sub Total', 'eventlist' ); ?></th>
					</tr>
				<?php else: ?>
					<tr class="seat_option_map">
						<th class="name"><?php esc_html_e( 'Seat Code', 'eventlist' ); ?></th>
						<?php if ( ! empty( $arr_area ) ): ?>
                            <th class="qty"><?php esc_html_e( 'Quantity', 'eventlist' ); ?></th>
                        <?php endif; ?>
						<th class="sub-total"><?php esc_html_e( 'Sub Total', 'eventlist' ); ?></th>
					</tr>
				<?php endif; ?>
			</thead>
			<tbody>
				<?php 
				if ( ! empty( $cart ) && is_array( $cart ) ) {
					$total = 0;
					if ( $seat_option == 'none' ) {
						foreach ( $cart as $key => $item ) {
							$total += $item['qty'] * $item['price'];
							?>
							<tr class="cart-item seat_option_none">
								<td class="name">
									<a href="#" class="delete_item">x</a>
									<input
										type="text"
										class="name"
										value="<?php echo esc_attr( $item['name'] ); ?>"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[name]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none"
										placeholder="<?php esc_html_e( 'ticket name', 'eventlist' ); ?> "
									/>
								</td>
								<td class="qty">
									<input
										type="number"
										class="qty"
										value="<?php echo esc_attr( $item['qty'] ); ?>"
										min="1"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[qty]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="1"
									/>
								</td>
								<td class="sub-total">
									<input
										type="text"
										class="price"
										value="<?php echo esc_attr( $item['qty'] * $item['price'] ); ?>"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[price]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="10.1"
									/>
								</td>
							</tr>
						<?php }
					} elseif ( $seat_option == 'simple' ) {
						foreach ( $cart as $key => $item ) {
							$total += $item['qty'] * $item['price'];

							if ( is_array( $item['seat'] ) ) {
								$list_seat = implode(", ", $item['seat']);
							} else {
								$list_seat = $item['seat'];
							}

							?>
							<tr class="cart-item seat_option_none">
								<td class="name">
									<a href="#" class="delete_item">x</a>
									<input
										type="text"
										class="name"
										value="<?php echo esc_attr( $item['name'] ); ?>"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[name]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none"
										placeholder="<?php esc_html_e( 'seat code', 'eventlist' ); ?> "
									/>
								</td>
								<td class="qty">
									<input
										type="number"
										class="qty"
										value="<?php echo esc_attr( $item['qty'] ); ?>"
										min="1"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[qty]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="1"
									/>
									<input
										type="text"
										class="seat"
										value="<?php echo esc_attr( $list_seat ); ?>"
										placeholder="<?php esc_attr_e('A1, A2, A3, ...', 'eventlist'); ?>"
										name="<?php echo esc_attr($this->get_mb_name('cart').'['.$key.']'.'[seat]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" required
									/>
								</td>
								<td class="sub-total">
									<input
										type="text"
										class="price"
										value="<?php echo esc_attr( $item['qty'] * $item['price'] ); ?>"
										name="<?php echo esc_attr($this->get_mb_name('cart').'['.$key.']'.'[price]'); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="10.5"
									/>
								</td>
							</tr>
						<?php }
					} else {
						foreach ( $cart as $key => $item ) {
							$item_qty = isset( $item['qty'] ) && absint( $item['qty'] ) ? absint( $item['qty'] ) : 1;
							$person_type = ! empty( $item['person_type'] ) ? $item['person_type'] : '';
							$total += $item['price'] * $item_qty;
						?>
							<tr class="cart-item seat_option_map">
								<td class="name">
									<a href="#" class="delete_item">x</a>
									<input
										type="text"
										class="name"
										value="<?php echo esc_attr( $item['id'] ); ?>"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[id]' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none"
									/>

									<?php if ( ! empty( $person_type ) && in_array($item['id'], $arr_seat )  ): ?>
						
										<?php echo esc_html( $person_type ); ?>
										<input type="hidden" name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[person_type]' ); ?>" value="<?php echo esc_attr( $person_type ); ?>" />
								
									<?php endif; ?>
								</td>

								


								<?php if ( ! empty( $arr_area ) ): ?>
									<td class="qty">

										<?php if ( isset( $item['qty'] ) && absint( $item['qty'] ) ): ?>
											<input
												type="number"
												class="qty"
												value="<?php echo esc_attr( $item['qty'] ); ?>"
												min="1"
												name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[qty]'); ?>"
												autocomplete="off" autocorrect="off" autocapitalize="none" placeholder="1"
											/>
										<?php else: ?>

											<?php if ( isset( $item['data_person'] ) && !empty( $item['data_person'] ) ): ?>
												<ul class="person_type_wrap">

													<?php foreach ( $item['data_person'] as $k => $val ): ?>
														<li class="item">
															<span class="person_type"><?php echo esc_html( $val['name'] ); ?>:</span>
															<input type="number" name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[data_person]'.'['.$k.']'.'[qty]' ); ?>" class="person_qty" value="<?php echo esc_attr( $val['qty'] ); ?>" min="0">
															<input type="hidden" name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[data_person]'.'['.$k.']'.'[name]' ); ?>" value="<?php echo esc_attr( $val['name'] ); ?>">
															<input type="hidden" name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[data_person]'.'['.$k.']'.'[price]' ); ?>" value="<?php echo esc_attr( $val['price'] ); ?>">
														</li>
													<?php endforeach; ?>
													
												</ul>
												<input type="hidden" class="person_qty_total" name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[person_qty]' ); ?>" value="<?php echo esc_attr( $item['person_qty'] ); ?>">
											<?php endif; ?>

										<?php endif; ?>
									</td>
								<?php endif; ?>
								<td class="sub-total">
									<input
										type="text"
										class="price"
										value="<?php echo esc_attr( $item['price'] ); ?>"
										name="<?php echo esc_attr( $this->get_mb_name('cart').'['.$key.']'.'[price]' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none"
									/>
								</td>
							</tr>
						<?php }
					}
				}
				?>
			</tbody>
			<tfoot>
				<tr class="cart-total">
					<th colspan="3" class="add_ticket"><a href="#"><?php esc_html_e('Add Item', 'eventlist'); ?></a></th>
				</tr>
				<tr class="cart-total">
					<th><?php esc_html_e('Total before tax', 'eventlist' ); ?></th>
					<td colspan="2" >
						<strong><?php echo wp_kses_post( el_price( $this->get_mb_value( 'total' ) ) ); ?></strong>
						<input
							type="text"
							class="total"
							value="<?php echo esc_attr( $this->get_mb_value( 'total' ) ); ?>"
							name="<?php echo esc_attr( $this->get_mb_name('total') ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							placeholder="<?php esc_html_e( '10.5', 'eventlist' ); ?>"
						/>
					</td>
				</tr>
				<tr class="cart-total">
					<th><?php esc_html_e('Total after tax', 'eventlist' ); ?></th>
					<td colspan="2" >
						<strong><?php echo wp_kses_post( el_price( $this->get_mb_value( 'total_after_tax' ) ) ); ?></strong>
						<input
							type="text"
							class="total_after_tax"
							value="<?php echo esc_attr( $this->get_mb_value( 'total_after_tax' ) ); ?>"
							name="<?php echo esc_attr( $this->get_mb_name('total_after_tax') ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							placeholder="<?php esc_html_e( '10.5', 'eventlist' ); ?>"
						/>
					</td>
				</tr>
			</tfoot>
		</table>
		<br><br>
	</div>
	<div class="ova_row">
		<div class="wp-button">
			<button class="button create-ticket-send-mail" data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_create_send_ticket_nonce' ) ); ?>" data-id-booking="<?php echo esc_attr($post->ID); ?>">
				<?php esc_html_e( "Create And Send Ticket", "eventlist" ); ?>
			</button>
			<?php if ( $pdf_invoices === 'yes' ): ?>
				<button
					class="button create-invoice" 
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_create_invoice_nonce' ) ); ?>" 
					data-booking-id="<?php echo esc_attr( $post->ID ); ?>">
					<?php esc_html_e( "View Invoice", "eventlist" ); ?>
				</button>
				<button
					class="button send-invoice" 
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_send_invoice_nonce' ) ); ?>" 
					data-booking-id="<?php echo esc_attr( $post->ID ); ?>">
					<?php esc_html_e( "Send Invoice", "eventlist" ); ?>
				</button>
			<?php endif; ?>
			<div class="mb-loading">
	            <i class="dashicons-before dashicons-update-alt"></i>
	        </div>
		</div>
		<br><br>
	</div>
</div>

<?php wp_nonce_field( 'ova_metaboxes', 'ova_metaboxes' ); ?>