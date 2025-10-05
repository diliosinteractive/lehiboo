<?php if( !defined( 'ABSPATH' ) ) exit(); 
global $post;

$time 		= el_calendar_time_format();
$format 	= el_date_time_format_js();
$first_day 	= el_first_day_of_week();

$placeholder_dateformat = el_placeholder_dateformat();
$placeholder_timeformat = el_placeholder_timeformat();

$seat_option 		= $this->get_mb_value( 'seat_option', 'none' );
$value_ticket_map 	= $this->get_mb_value( 'ticket_map' );
$currency 			= _el_symbol_price();

$ticket_link = $this->get_mb_value( 'ticket_link', '' );
$ticket_external_link 		= $this->get_mb_value( 'ticket_external_link', '' );
$ticket_external_link_price = $this->get_mb_value( 'ticket_external_link_price', '' );

$decimal_separator 	= EL()->options->general->get('decimal_separator','.');
$number_decimals 	= EL()->options->general->get('number_decimals','2');
$data_curency = array(
	'decimal_separator' => $decimal_separator,
	'number_decimals' => $number_decimals,
);

// Type seat
$type_seat = [];

$seating_map = $this->get_mb_value('seating_map', '');

?>
<div class="edit_ticket_info">
	<p><?php esc_html_e( 'if you don\'t want to sell ticket, you don\'t need to make ticket', 'eventlist' ); ?></p>
</div>

<?php if ( apply_filters( 'el_show_ticket_link_opt', true ) ): ?>
	<div class="ticket_link">
		<label><strong><?php esc_html_e( 'Buy ticket at', 'eventlist' ); ?>:</strong></label>
		<input
			type="radio"
			value="ticket_internal_link"
			name="<?php echo esc_attr( $this->get_mb_name( 'ticket_link' ) ); ?>"
			<?php if ( $ticket_link == 'ticket_internal_link' || $ticket_link == '') echo esc_attr('checked') ; ?>
		/>
		<span><?php esc_html_e( 'Internal link', 'eventlist' ); ?></span>
		<input
			type="radio"
			value="ticket_external_link"
			name="<?php echo esc_attr( $this->get_mb_name( 'ticket_link' ) ); ?>"
			<?php if ( $ticket_link == 'ticket_external_link') echo esc_attr('checked') ; ?>
		/>
		<span><?php esc_html_e( 'External Link', 'eventlist' ); ?></span>
	</div>
<?php endif; ?>

<?php if ( apply_filters( 'el_show_ticket_external_link_field', true ) ): ?>
	<div class="ticket_external_link">
		<label><?php esc_html_e( 'Insert external link', 'eventlist' ); ?></label>
		<input
			type="text"
			name="<?php echo esc_attr( $this->get_mb_name( 'ticket_external_link' ) ); ?>"
			value="<?php echo esc_url( $ticket_external_link ); ?>"
			placeholder="<?php esc_html_e( 'https://', 'eventlist' ); ?>"
		/>
	</div>
	<div class="ticket_external_link_price">
		<label><?php esc_html_e( 'Price', 'eventlist' ); ?></label>
		<input
			type="text"
			name="<?php echo esc_attr( $this->get_mb_name( 'ticket_external_link_price' ) ); ?>"
			value="<?php echo esc_attr( $ticket_external_link_price ); ?>"
			placeholder="<?php esc_html_e( 'From $30', 'eventlist' ); ?>">
	</div>
<?php endif; ?>

<?php if ( apply_filters( 'el_show_ticket_internal_link_field', true ) ): ?>
	<div class="ticket_internal_link">
		<!-- Seat Option -->
		<div class="wrap_seat_option">
			<label class="label"><strong><?php esc_html_e( 'Type:', 'eventlist' ); ?></strong></label>
			<div class="radio_seat_option">
				<span>
					<input
						type="radio"
						name="<?php echo esc_attr( $this->get_mb_name( 'seat_option' ) ); ?>"
						class="seat_option"
						value="<?php echo esc_attr('none'); ?>"
						<?php if ( $seat_option == 'none' ||  $seat_option == '') echo esc_attr('checked') ; ?>
					/>
					<?php esc_html_e( 'No Seat', 'eventlist' ); ?>
				</span>
				<span>
					<input
						type="radio"
						name="<?php echo esc_attr( $this->get_mb_name( 'seat_option' ) ); ?>"
						class="seat_option"
						value="<?php echo esc_attr('simple'); ?>"
						<?php if ( $seat_option == 'simple') echo esc_attr('checked') ; ?>
					/>
					<?php esc_html_e( 'Simple Seat', 'eventlist' ); ?>
				</span>
				<span>
					<input
						type="radio"
						name="<?php echo esc_attr( $this->get_mb_name( 'seat_option' ) ); ?>"
						class="seat_option"
						value="<?php echo esc_attr('map'); ?>"
						<?php if ( $seat_option == 'map') echo esc_attr('checked') ; ?>
					/>
					<?php esc_html_e( 'Map', 'eventlist' ); ?>
				</span>
			</div>
		</div>

		<?php
		$class_active = $seat_option == 'simple' ? 'is-active' : '';
		?>

		<div class="seating_map_wrapper <?php echo esc_attr( $class_active ); ?>">
			<label>
				<strong>
					<?php esc_html_e( 'Global Regional Image', 'eventlist' ); ?>
				</strong>
			</label>
			<div class="el-add-image-wrap">
	    		<div class="image-wrap">
	    			<?php if ( $seating_map ): ?>
	    				<div class="item">
	    					<img src="<?php echo esc_attr( wp_get_attachment_url( $seating_map ) ); ?>" class="image" />
	        				<input type="hidden" name="image" value="<?php echo esc_html( $seating_map ); ?>"/>
	    				</div>
	    				<a href="#" class="el_remove_seating_map">
	    					<span class="dashicons dashicons-no"></span>
	    				</a>
	    			<?php endif; ?>
	    		</div>
	            
	            <a href="#" class="button button-secondary el_add_image"><?php esc_html_e( 'Choose image', 'eventlist' ); ?></a>
	    	</div>
		</div>

		

		<!-- Ticket items -->
		<div class="wrap">
			<!-- None - Simple -->
			<div class="ticket_none_simple" style="<?php echo esc_attr( $seat_option !== 'map' ? 'display: block;' : 'display: none;' ); ?>">
				<?php if ( $this->get_mb_value( 'ticket' ) ):
					foreach ( $this->get_mb_value( 'ticket' ) as $key => $value ): 
						/* Check Name Ticket */
						if ( isset( $value['name_ticket'] ) ): ?>
							<div class="ticket_item" data-prefix="<?php echo esc_attr( OVA_METABOX_EVENT ); ?>">
								<!-- Headding Ticket -->
								<div class="heading_ticket">
									<div class="left">
										<i class="fas fa-ticket-alt"></i>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][name_ticket]' ) ); ?>"
											id="name_ticket"
											value="<?php echo esc_attr( $value['name_ticket'] ); ?>"
											placeholder="<?php esc_attr_e( 'Click to edit ticket name', 'eventlist' ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="right">
										<!-- <i class="dashicons dashicons-move move_ticket"></i> -->
										<i class="fas fa-edit edit_ticket"></i>
										<i class="fas fa-trash delete_ticket"></i>
									</div>
								</div>
								<!-- Content Ticket -->
								<div class="content_ticket">
									<!-- ID Ticket -->
									<div class="id_ticket">
										<label><strong><?php esc_html_e( 'SKU: *', 'eventlist' ); ?></strong></label>
										<input
											type="text"
											id="ticket_id"
											name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][ticket_id]' ) ); ?>"
											value="<?php echo esc_attr( isset( $value['ticket_id'] ) ? $value['ticket_id'] : '' ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
										<span><?php esc_html_e( 'Auto render if empty', 'eventlist' ); ?></span>
									</div>
									<!-- Top Ticket -->
									<div class="top_ticket">
										<div class="col_price_ticket col">
											<?php
												if ( ! isset( $value['type_price'] ) ) {
													$value['type_price'] = 'paid';
												}
											?>
											<div class="top">
												<span>
													<strong><?php esc_html_e( 'Price', 'eventlist' ); ?></strong>
												</span>
												<div class="radio_type_price" data-type-price="<?php echo esc_attr( $value['type_price'] ); ?>">

													<label for="type_price_paid<?php echo esc_attr( $key ); ?>" class="el_input_radio">
														<?php esc_html_e( 'Paid', 'eventlist' ); ?>
														<input
															type="radio"
															name="<?php echo esc_attr($this->get_mb_name( 'ticket['.$key.'][type_price]' )); ?>"
															id="type_price_paid<?php echo esc_attr( $key ); ?>"
															class="type_price"
															value="<?php echo esc_attr('paid'); ?>"
															<?php checked( $value['type_price'], 'paid', 'checked' ); ?>
														/>
														
														<span class="checkmark el_bg_white"></span>
													</label>


													<label for="type_price_free<?php echo esc_attr( $key ); ?>" class="el_input_radio el_ml_10px">
														<?php esc_html_e( 'Free', 'eventlist' ); ?>
														<input
															type="radio"
															name="<?php echo esc_attr($this->get_mb_name( 'ticket['.$key.'][type_price]' )); ?>"
															id="type_price_free<?php echo esc_attr( $key ); ?>"
															class="type_price"
															value="<?php echo esc_attr('free'); ?>" <?php checked( $value['type_price'], 'free', 'checked' ); ?>
														/>
														
														<span class="checkmark el_bg_white"></span>
													</label>
												</div>
											</div>
											<div class="ova_wrap_price_ticket" data-curency="<?php echo esc_attr( json_encode( $data_curency ) ); ?>">
												<?php
												$price_ticket = !empty($value['price_ticket']) ? $value['price_ticket'] : '0';
												$price_ticket = str_replace(".", $decimal_separator, $price_ticket);
												?>
												<input
													type="text"
													name="<?php echo esc_attr($this->get_mb_name( 'ticket['.$key.'][price_ticket]' )); ?>"

													class="price_ticket"
													value="<?php echo esc_attr( $price_ticket ); ?>"
													<?php if ( $value['type_price'] == 'free' ) echo esc_attr('disabled'); ?>
													placeholder ="<?php esc_attr_e( '0', 'eventlist' ); ?>"
													autocomplete="off" autocorrect="off" autocapitalize="none"
												/>
												<span class="ova_price_ticket_err">
													<?php echo sprintf( esc_html__( 'Please enter a value with one monetary decimal point ( %s ) without thousand separators and currency symbols.', 'eventlist' ), esc_html($decimal_separator) ); ?>
												</span>
											</div>
										</div>
										<?php $class_active = $seat_option == 'none' ? 'is-active' : ''; ?>
										<div class="col_total_number_ticket col <?php echo esc_attr( $class_active ); ?>">
											<div class="top">
												<strong><?php esc_html_e( 'Total ', 'eventlist' ); ?></strong>
												<span><?php esc_html_e( 'number of tickets', 'eventlist' ); ?></span>
											</div>
											<input
												type="number"
												name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][number_total_ticket]' ) ); ?>"
												id="number_total_ticket"
												<?php $number_total_ticket = !empty($value['number_total_ticket']) ? $value['number_total_ticket'] : 1; ?>
												value="<?php echo esc_attr( $number_total_ticket ); ?>"
												placeholder="<?php echo esc_attr( '10', 'eventlist' ); ?>"
											/>
										</div>
										<div class="col_min_number_ticket col">
											<div class="top">
												<strong><?php esc_html_e( 'Minimum ', 'eventlist' ); ?></strong>
												<span>
													<?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?>
												</span>
											</div>
											<input
												type="number"
												name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][number_min_ticket]' ) ); ?>"
												id="number_min_ticket"
												<?php $number_min_ticket = !empty( $value['number_min_ticket'] ) ? $value['number_min_ticket'] : 1; ?>
												value="<?php echo esc_attr( $number_min_ticket ); ?>"
												placeholder="<?php echo esc_attr( '1', 'eventlist' ); ?>"
												autocomplete="off" autocorrect="off" autocapitalize="none" 
											/>
										</div>
										<div class="col_max_number_ticket col">
											<div class="top">
												<strong><?php esc_html_e( 'Maximum ', 'eventlist' ); ?></strong>
												<span>
													<?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?>
												</span>
											</div>
											<input
												type="number"
												name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][number_max_ticket]' ) ); ?>"
												id="number_max_ticket"
												<?php $number_max_ticket = ! empty( $value['number_max_ticket'] ) ? $value['number_max_ticket'] : 1; ?>
												value="<?php echo esc_attr( $number_max_ticket ); ?>"
												placeholder="<?php echo esc_attr( '10', 'eventlist' ); ?>"
												autocomplete="off" autocorrect="off" autocapitalize="none"
											/>
										</div>
									</div>
									<!-- Middle Ticket -->
									<div class="middle_ticket">
										<div class="date_ticket">
											<div class="start_date">
												<span>
													<?php esc_html_e( 'Start date for selling tickets', 'eventlist' ); ?>
												</span>
												<div>
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][start_ticket_date]' ) ); ?>"
														class="start_ticket_date"
														value="<?php echo esc_attr( $value['start_ticket_date'] ); ?>"
														data-format="<?php echo esc_attr( $format ); ?>"
														data-firstday="<?php echo esc_attr( $first_day ); ?>"
														placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
													<input
														type="text" 
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][start_ticket_time]' ) ); ?>"
														id="start_ticket_time"
														class="start_ticket_time"
														value="<?php echo esc_attr( $value['start_ticket_time'] ); ?>"
														data-time="<?php echo esc_attr($time); ?>"
														placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
												</div>
											</div>
											<div class="end_date">
												<span>
													<?php esc_html_e( 'End date for selling tickets', 'eventlist' ); ?>
												</span>
												<div>
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][close_ticket_date]' ) ); ?>"
														class="close_ticket_date"
														value="<?php echo esc_attr( $value['close_ticket_date'] ); ?>"
														data-format="<?php echo esc_attr( $format ); ?>"
														data-firstday="<?php echo esc_attr( $first_day ); ?>"
														placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][close_ticket_time]' ) ); ?>"
														id="close_ticket_time"
														class="close_ticket_time"
														value="<?php echo esc_attr( $value['close_ticket_time'] ); ?>"
														data-time="<?php echo esc_attr($time); ?>"
														placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
												</div>
											</div>
										</div>
										<div class="wrap_color_ticket">
											<div>
												<div class="span9">
													<span><?php esc_html_e( 'Ticket border color', 'eventlist' ); ?></span>
													<small>
														<?php esc_html_e( '(Color border in ticket)', 'eventlist' ); ?>
													</small>
												</div>
												<div class="span3">
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][color_ticket]' ) ); ?>"
														id="color_ticket"
														class="color_ticket"
														value="<?php echo esc_attr( $value['color_ticket'] ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
												</div>
											</div>
											<div>
												<div class="span9">
													<span><?php esc_html_e( 'Ticket label color', 'eventlist' ); ?></span>
													<small>
														<?php esc_html_e( '(Color label in ticket)', 'eventlist' ); ?>
													</small>
												</div>
												<div class="span3">
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][color_label_ticket]' ) ); ?>"
														id="color_label_ticket"
														class="color_label_ticket"
														value="<?php echo esc_attr( $value['color_label_ticket'] ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
												</div>
											</div>
											<div>
												<div class="span9">
													<span><?php esc_html_e( 'Ticket content color', 'eventlist' ); ?></span>
													<small>
														<?php esc_html_e( '(Color content in ticket)', 'eventlist' ); ?>
													</small>
												</div>
												<div class="span3">
													<input
														type="text"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][color_content_ticket]' ) ); ?>"
														id="color_content_ticket"
														class="color_content_ticket"
														value="<?php echo esc_attr( $value['color_content_ticket'] ); ?>"
														autocomplete="off" autocorrect="off" autocapitalize="none"
													/>
												</div>
											</div>
										</div>
									</div>
									<!-- Bottom Ticket -->
									<div class="bottom_ticket">
										<div class="title_add_desc">
											<small class="text_title">
												<?php esc_html_e( 'Description display at frontend and PDF Ticket', 'eventlist' ); ?>
												<i class="arrow_triangle-down"></i>
											</small>
											<div>
												<small>
													<?php esc_html_e( 'Description limited 230 character in ticket', 'eventlist' ); ?>
												</small>
											</div>
										</div>
										<div class="content_desc">
											<textarea name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][desc_ticket]' ) ); ?>" id="desc_ticket" cols="30" rows="5"><?php echo esc_attr( $value['desc_ticket'] ); ?></textarea>
											<div class="image_ticket" data-index="<?php echo esc_attr( $key ); ?>">
												<div class="add_image_ticket">
													<input
														type="hidden"
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][image_ticket]' ) ); ?>"
														class="image_ticket"
														value="<?php echo esc_attr( isset( $value['image_ticket'] ) ) ? esc_attr( $value['image_ticket'] ) : ''; ?>"
													/>
													<?php if ( isset($value['image_ticket']) && $value['image_ticket'] != '' ): ?>
														<img class="image-preview-ticket" src="<?php echo esc_url(wp_get_attachment_url( $value['image_ticket'] ) ); ?>" alt="<?php esc_attr_e( 'image ticket', 'eventlist' ); ?>">
													<?php else: ?>
														<i class="icon_plus_alt2"></i>
														<?php esc_html_e('Add ticket logo (.jpg, .png)', 'eventlist'); ?>
														<br/>
														<span>
															<?php esc_html_e( 'Recommended size: 130x50px','eventlist' ); ?>
														</span>
													<?php endif; ?>
												</div>
												<div class="remove_image_ticket">
													<?php if ( isset( $value['image_ticket'] ) && $value['image_ticket'] != '' ): ?>
														<span><?php esc_html_e( 'x', 'eventlist' ); ?></span>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<div class="private_desc_ticket">
											<div class="title_add_desc">
												<small class="text_title">
													<?php esc_html_e( 'Private Description in Ticket - Only see when bought ticket', 'eventlist' ); ?>
													<i class="arrow_triangle-down"></i>
												</small>
											</div>
											<textarea name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][private_desc_ticket]' ) ); ?>" id="private_desc_ticket" cols="30" rows="5"><?php echo isset( $value['private_desc_ticket'] ) ? esc_attr( $value['private_desc_ticket'] ) : ''; ?></textarea>
										</div>
										<div class="setting_ticket_online">
											<div class="title_add_desc">
												<small class="text_title">
													<?php esc_html_e( 'These info only display in mail', 'eventlist' ); ?>
													<i class="arrow_triangle-down"></i>
												</small>
											</div>
											<div class="online_field link">
												<label><?php esc_html_e( 'Link', 'eventlist' ); ?></label>
												<input
													type="text"
													id="online_link"
													name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][online_link]' ) ); ?>"
													value="<?php echo isset( $value['online_link'] ) ? esc_attr( $value['online_link'] ) : ''; ?>"
												/>
											</div>
											<div class="online_field password">
												<label><?php esc_html_e( 'Password', 'eventlist' ); ?></label>
												<input
													type="text"
													id="online_password"
													name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][online_password]' ) ); ?>"
													value="<?php echo isset( $value['online_link'] ) ? esc_attr( $value['online_password'] ) : ''; ?>"
												/>
											</div>
											<div class="online_field other">
												<label><?php esc_html_e( 'Other info', 'eventlist' ); ?></label>
												<input
													type="text"
													id="online_other"
													name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][online_other]' ) ); ?>"
													value="<?php echo isset( $value['online_link'] ) ? esc_attr( $value['online_other'] ) : ''; ?>"
												/>
											</div>
										</div>
									</div>
									<!-- Seat List -->

									<?php
									$ticket = $this->get_mb_name('ticket');
									
									$setup_mode = isset( $value['setup_mode'] ) ? $value['setup_mode'] : 'manually';
									$class_active = $seat_option == 'simple' ? "is-active" : '';
									?>
									<div class="wrap_seat_list <?php echo esc_attr( $class_active ); ?>">

										<div class="seat_setup_wrap">

											<label>
												<strong><?php esc_html_e( 'Setup Mode', 'eventlist' ); ?></strong>
											</label>

											<label for="setup_mode_manually_<?php echo esc_attr( $key ); ?>">
												<input type="radio"
												id="setup_mode_manually_<?php echo esc_attr( $key ); ?>"
												class="setup_mode_input"
												<?php checked( $setup_mode, 'manually' ); ?>
												name="<?php echo esc_attr( $ticket.'['.$key.'][setup_mode]' ); ?>"
												value="manually" checked />
												<?php esc_html_e( 'Manually', 'eventlist' ); ?>
											</label>

											<label for="setup_mode_automatic_<?php echo esc_attr( $key ); ?>">
												<input type="radio"
												class="setup_mode_input"
												<?php checked( $setup_mode, 'automatic' ); ?>
												id="setup_mode_automatic_<?php echo esc_attr( $key ); ?>"
												name="<?php echo esc_attr( $ticket.'['.$key.'][setup_mode]' ); ?>"
												value="automatic" />
												<?php esc_html_e( 'Automatic', 'eventlist' ); ?>
											</label>

										</div>


										<div class="seat_code_wrap">
											<label class="label">
												<strong><?php esc_html_e( 'Seat Code List:', 'eventlist' ); ?></strong>
											</label>


											<div class="seat_code_container">
												<?php
												$class_active = $setup_mode == 'manually' ? 'is-active' : '';
												$required = ( $setup_mode == 'manually' && $seat_option == 'simple' ) ? 'required' : '';
												?>
												<div class="seat_code_manually <?php echo esc_attr( $class_active ); ?>">

													<textarea
														name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][seat_list]' ) ); ?>"
														id="seat_list" class="seat_list" cols="30" rows="5"
														<?php echo esc_attr( $required ); ?>
														placeholder="<?php echo esc_attr( 'A1, B2, C3, ...' ); ?>"><?php echo isset( $value['seat_list'] ) ? esc_html( $value['seat_list'] ) : ''; ?></textarea>
												</div>
												<?php
												unset( $class_active );
												$class_active = $setup_mode == 'automatic' ? 'is-active' : '';
												$seat_code_setup = isset( $value['seat_code_setup'] ) ? $value['seat_code_setup'] : [];
												?>
												<div class="seat_code_automatic <?php echo esc_attr( $class_active ); ?>">

													<ul class="seat_code_setup"
													data-key="<?php echo esc_attr( $key ); ?>"
													data-ticket="<?php echo esc_attr( $ticket ); ?>">
														<?php if ( ! empty( $seat_code_setup ) ): ?>
															<?php foreach ( $seat_code_setup as $_k => $_val ): ?>
																<?php include EL_PLUGIN_INC."admin/views/metaboxes/html-seat-code-setup-item.php"; ?>
															<?php endforeach; ?>
														<?php endif; ?>
													</ul>

													<a href="#"
													data-nonce="<?php echo esc_attr( wp_create_nonce('el_add_seat_code_row') ); ?>"
													class="button button-secondary add_seat_code_row">
														<?php esc_html_e( 'Add Seat', 'eventlist' ); ?>
													</a>
												</div>
											</div>

										</div>



										
									</div>


									<!-- The customer choose seat -->
									<div class="wrap_setup_seat" data-setup-seat="<?php echo esc_attr( $value['setup_seat'] ); ?>" style="<?php if ( $this->get_mb_value( 'seat_option' ) == 'simple' ) echo esc_attr('display: flex;'); ?>">
										<label class="label" for="setup_seat">
											<strong><?php esc_html_e( 'The customer choose seat:', 'eventlist' ); ?></strong>
										</label>

										<label for="setup_seat_yes<?php echo esc_attr( $key ); ?>" class="el_input_radio">
											<?php esc_html_e( 'Yes', 'eventlist' ); ?>
											<input
												type="radio"
												name="<?php echo esc_attr($this->get_mb_name('ticket['.$key.'][setup_seat]')); ?>"
												id="setup_seat_yes<?php echo esc_attr( $key ); ?>"
												class="setup_seat"
												value="yes"
												<?php if ( isset( $value['setup_seat'] ) ) checked( $value['setup_seat'], 'yes', 'checked' ); ?>
											/>
											
											<span class="checkmark el_bg_white"></span>
										</label>


										<label for="setup_seat_no<?php echo esc_attr( $key ); ?>" class="el_input_radio">
											<?php esc_html_e( 'No', 'eventlist' ); ?>
											<input
												type="radio"
												name="<?php echo esc_attr($this->get_mb_name('ticket['.$key.'][setup_seat]')); ?>"
												id="setup_seat_no<?php echo esc_attr( $key ); ?>"
												class="setup_seat"
												value="no"
												<?php if ( isset( $value['setup_seat'] ) ) checked( $value['setup_seat'], 'no', 'checked' ); ?>
											/>
											
											<span class="checkmark el_bg_white"></span>
										</label>
										
									</div>
									<div class="seat_map_ticket" data-index="<?php echo esc_attr( $key ); ?>" style="<?php if ( $this->get_mb_value( 'seat_option' ) == 'simple' ) echo esc_attr('display: flex;'); ?>">
										<label class="label">
											<strong>
												<?php esc_html_e( 'Sub-Regional Image:', 'eventlist' ); ?>
											</strong>
										</label>
										<div class="image_ticket_seat_map">
											<div class="add_seat_map_ticket">
												<input
													type="hidden"
													name="<?php echo esc_attr( $this->get_mb_name( 'ticket['.$key.'][seat_map_ticket]' ) ); ?>"
													class="seat_map_ticket"
													value="<?php echo esc_attr( isset( $value['seat_map_ticket'] ) ) ? esc_attr( $value['seat_map_ticket'] ) : ''; ?>"
												/>
												<?php if ( isset( $value['seat_map_ticket'] ) && $value['seat_map_ticket'] != '' ): ?>
													<img class="image-preview-ticket" src="<?php echo esc_url( wp_get_attachment_url( $value['seat_map_ticket'] ) ); ?>" alt="<?php esc_attr_e( 'Seat Map Image', 'eventlist' ); ?>">
												<?php else: ?>
													<i class="icon_plus_alt2"></i>
													<?php esc_html_e('Add image (.jpg, .png)', 'eventlist') ?>
												<?php endif; ?>
											</div>
											<div class="remove_seat_map_ticket">
												<?php if ( isset( $value['seat_map_ticket'] ) && $value['seat_map_ticket'] != '' ): ?>
													<span><?php esc_html_e( 'x', 'eventlist' ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</div>
									<!-- Save Ticket -->
									<a href="#" class="save_ticket"><?php esc_html_e('Done', 'eventlist') ?></a>
								</div>
							</div>
						<?php endif;
					endforeach;
				endif; ?>
			</div>
			<!-- Map -->
			<div class="ticket_map" style="<?php echo esc_attr( $seat_option == 'map' ? 'display: block;' : 'display: none;' ); ?>">
				<div class="top_content">
					<div class="short_code_map item-col">
						<label for="short_code_map"><?php esc_html_e( 'Map Shortcode', 'eventlist' ); ?></label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[short_code_map]' ) ); ?>"
							id="short_code_map"
							class="short_code_map"
							value="<?php echo esc_attr( isset( $value_ticket_map['short_code_map'] ) ? $value_ticket_map['short_code_map'] : '' ); ?>"
							placeholder="<?php echo esc_attr( '[short_code_map]', 'eventlist' ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
						/>
					</div>
					<div class="col_min_number_ticket item-col">
						<div class="top">
							<strong><?php esc_html_e( 'Minimum ', 'eventlist' ); ?></strong>
							<span><?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?></span>
						</div>
						<input
							type="number" 
							name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[number_min_ticket]' ) ); ?>"
							id="number_min_ticket_map"
							value="<?php echo esc_attr( isset( $value_ticket_map['number_min_ticket'] ) ? $value_ticket_map['number_min_ticket'] : 1 ); ?>"
							placeholder="<?php echo esc_attr( '1', 'eventlist' ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
							min= "1"
						/>
					</div>
					<div class="col_max_number_ticket item-col">
						<div class="top">
							<strong><?php esc_html_e( 'Maximum ', 'eventlist' ); ?></strong>
							<span><?php esc_html_e( 'number of tickets for one purchase', 'eventlist' ); ?></span>
						</div>
						<input
							type="number"
							name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[number_max_ticket]' ) ); ?>"
							id="number_max_ticket_map"
							class="number_max_ticket_map"
							value="<?php echo esc_attr( isset( $value_ticket_map['number_max_ticket'] ) ? $value_ticket_map['number_max_ticket'] : 1 ); ?>"
							placeholder="<?php echo esc_attr( '10', 'eventlist' ); ?>"
							autocomplete="off" autocorrect="off" autocapitalize="none"
						/>
					</div>
				</div>
				<hr>
				<div class="middle_content">
					<div class="date_ticket ova_row">
						<div class="start_date">
							<span><?php esc_html_e( 'Start date for selling tickets', 'eventlist' ); ?></span>
							<div>
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[start_ticket_date]' ) ); ?>"
									class="start_ticket_date_map"
									value="<?php echo esc_attr( isset( $value_ticket_map['start_ticket_date'] ) ? $value_ticket_map['start_ticket_date'] : '' ); ?>"
									data-format="<?php echo esc_attr( $format ); ?>"
									data-firstday="<?php echo esc_attr( $first_day ); ?>"
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[start_ticket_time]' ) ); ?>"
									class="start_ticket_time_map"
									value="<?php echo esc_attr( isset( $value_ticket_map['start_ticket_time'] ) ? $value_ticket_map['start_ticket_time'] : '' ); ?>"
									data-time="<?php echo esc_attr( $time ); ?>"
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
							</div>
						</div>
						<div class="end_date">
							<span><?php esc_html_e( 'End date for selling tickets', 'eventlist' ); ?></span>
							<div>
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[close_ticket_date]' ) ); ?>" 
									class="close_ticket_date_map"
									value="<?php echo esc_attr( isset( $value_ticket_map['close_ticket_date'] ) ? $value_ticket_map['close_ticket_date'] : '' ); ?>"
									data-format="<?php echo esc_attr( $format ); ?>"
									data-firstday="<?php echo esc_attr( $first_day ); ?>"
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[close_ticket_time]' ) ); ?>"
									class="close_ticket_time_map"
									value="<?php echo esc_attr( isset( $value_ticket_map['close_ticket_time'] ) ? $value_ticket_map['close_ticket_time'] : '' ); ?>"
									data-time="<?php echo esc_attr( $time ); ?>"
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
							</div>
						</div>
					</div>
					<div class="wrap_color_ticket ova_row">
						<div>
							<div class="span9">
								<span><?php esc_html_e( 'Ticket border color', 'eventlist' ); ?></span>
								<small><?php esc_html_e( '(Color border in ticket)', 'eventlist' ); ?></small>
							</div>
							<div class="span3">
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[color_ticket]' ) ); ?>"
									id="color_ticket_map"
									class="color_ticket_map"
									value="<?php echo ( isset( $value_ticket_map['color_ticket'] ) && $value_ticket_map['color_ticket'] ) ? esc_attr($value_ticket_map['color_ticket']) : ''; ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
							</div>
						</div>
						<div>
							<div class="span9">
								<span><?php esc_html_e( 'Ticket label color', 'eventlist' ); ?></span>
								<small><?php esc_html_e( '(Color label in ticket)', 'eventlist' ); ?></small>
							</div>
							<div class="span3">
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[color_label_ticket]' ) ); ?>"
									id="color_label_ticket_map"
									class="color_label_ticket_map"
									value="<?php echo isset( $value_ticket_map['color_label_ticket'] ) && $value_ticket_map['color_label_ticket'] ? esc_attr( $value_ticket_map['color_label_ticket'] ) : '' ; ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
							</div>
						</div>
						<div>
							<div class="span9">
								<span><?php esc_html_e( 'Ticket content color', 'eventlist' ); ?></span>
								<small><?php esc_html_e( '(Color content in ticket)', 'eventlist' ); ?></small>
							</div>
							<div class="span3">
								<input
									type="text"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[color_content_ticket]' ) ); ?>"
									id="color_content_ticket_map"
									class="color_content_ticket_map"
									value="<?php echo isset( $value_ticket_map['color_content_ticket'] ) && $value_ticket_map['color_content_ticket'] ? esc_attr( $value_ticket_map['color_content_ticket'] ) : ''; ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none"
								/>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<!-- Bottom Ticket -->
				<div class="bottom_ticket">
					<div class="title_add_desc">
						<small class="text_title">
							<?php esc_html_e( 'Description display at frontend and PDF Ticket', 'eventlist' ); ?>
							<i class="arrow_triangle-down"></i>
						</small>
						<div>
							<small><?php esc_html_e( 'Description limited 230 character in ticket', 'eventlist' ); ?></small>
						</div>
					</div>
					<div class="content_desc">
						<textarea name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[desc_ticket]' ) ); ?>" class="desc_ticket_map" cols="30" rows="5"><?php echo esc_attr( isset( $value_ticket_map['desc_ticket'] ) ? $value_ticket_map['desc_ticket'] : '' ) ; ?></textarea>
						<div class="image_ticket_map">
							<div class="add_image_ticket_map">
								<input
									type="hidden"
									name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[image_ticket]' ) ); ?>"
									class="map_image_ticket"
									value="<?php echo esc_attr( isset( $value_ticket_map['image_ticket'] ) ) ? esc_attr( $value_ticket_map['image_ticket'] ) : ''; ?>"
								/>
								<?php if ( isset( $value_ticket_map['image_ticket'] ) && $value_ticket_map['image_ticket'] != '' ): ?>
									<img  class="image-preview-ticket-map" src="<?php echo esc_url( wp_get_attachment_url( $value_ticket_map['image_ticket'] ) ); ?>" alt="<?php esc_attr_e( 'image ticket', 'eventlist' ); ?>">
								<?php else: ?>
									<i class="icon_plus_alt2"></i>
									<?php esc_html_e('Add ticket logo (.jpg, .png)', 'eventlist'); ?>
									<br/><span><?php esc_html_e( 'Recommended size: 130x50px','eventlist' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="remove_image_ticket_map">
								<?php if ( isset( $value_ticket_map['image_ticket'] ) && $value_ticket_map['image_ticket'] != '' ): ?>
									<span><?php esc_html_e( 'x', 'eventlist' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="private_desc_ticket">
						<div class="title_add_desc">
							<small class="text_title">
								<?php esc_html_e( 'Private Description in Ticket - Only see when bought ticket', 'eventlist' ); ?>
								<i class="arrow_triangle-down"></i>
							</small>
						</div>
						<textarea name="<?php echo esc_attr( $this->get_mb_name( 'ticket_map[private_desc_ticket_map]' ) ); ?>" class="private_desc_ticket_map" cols="30" rows="5"><?php echo esc_attr( isset( $value_ticket_map['private_desc_ticket_map'] ) ? $value_ticket_map['private_desc_ticket_map'] : '' ) ; ?></textarea>
					</div>
				</div>
				<hr>
				<div class="container_desc_seat_map">
					<p style="font-weight: bold"><?php esc_html_e('Add description to these seat type:', 'eventlist'); ?></p>
					<div class="wrap_desc_seat_map" data-currency="<?php echo esc_attr( $currency ); ?>" data-label="<?php esc_attr_e( 'price', 'eventlist' ); ?>">
						<?php if ( isset( $value_ticket_map['desc_seat'] ) && $value_ticket_map['desc_seat'] ):
							foreach ( $value_ticket_map['desc_seat'] as $key => $value):
								if ( isset( $value['map_type_seat'] ) && $value['map_type_seat'] ) {
									array_push( $type_seat, $value['map_type_seat'] );
								}
							?>
								<div class="item_desc_seat" data-prefix="<?php echo esc_attr( OVA_METABOX_EVENT ); ?>">
									<div class="item-col">
										<label><?php esc_html_e( 'Type Seat:', 'eventlist' ) ?></label>
										<input
											type="text"
											class="map_type_seat"
											value="<?php echo esc_attr( isset( $value['map_type_seat'] ) ? $value['map_type_seat'] : '' ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[desc_seat]['.$key.'][map_type_seat]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( 'Standard', 'eventlist' ); ?>"
										/>
									</div>
									<div class="item-col">
										<label>
											<?php esc_html_e( 'Price', 'eventlist' ) ?><?php echo esc_html( ' ('. $currency .'):' ); ?>
										</label>
										<input
											type="text"
											class="map_price_type_seat"
											value="<?php echo esc_attr( isset( $value['map_price_type_seat'] ) ? $value['map_price_type_seat'] : '' ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[desc_seat]['.$key.'][map_price_type_seat]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( '50.00', 'eventlist' ); ?>"
										/>
									</div>
									<div class="item-col">
										<label><?php esc_html_e( 'Description:', 'eventlist' ) ?></label>
										<input
											type="text"
											class="map_desc_type_seat"
											value="<?php echo esc_attr( isset( $value['map_desc_type_seat'] ) ? $value['map_desc_type_seat'] : '' ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[desc_seat]['.$key.'][map_desc_type_seat]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( 'Description of type seat', 'eventlist' ); ?>"
										/>
									</div>
									<div class="item-col">
										<label><?php esc_html_e( 'Color:', 'eventlist' ) ?></label>
										<input
											type="text"
											class="map_color_type_seat"
											value="<?php echo esc_attr( isset( $value['map_color_type_seat'] ) ? $value['map_color_type_seat'] : '#fff' ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[desc_seat]['.$key.'][map_color_type_seat]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( '#ffffff', 'eventlist' ); ?>"
										/>
									</div>
									<a href="#" class="button remove_desc_seat_map">
										<?php esc_html_e( 'x', 'eventlist' ); ?>
									</a>
								</div>
							<?php endforeach;
						endif; ?>
					</div>
					<button class="button add_desc_seat_map">
						<?php esc_html_e( 'Add description seat', 'eventlist' ); ?>
						<div class="submit-load-more">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>
					</button>
				</div>
				<hr>
				<div class="container_seat_map">
					<ul class="type_map">
						<li class="active" data-type="seat">
							<?php esc_html_e( 'Seat', 'eventlist' ); ?>
						</li>
						<li data-type="area">
							<?php esc_html_e( 'Area', 'eventlist' ); ?>
						</li>
					</ul>
					<div class="wrap_seat_map">

						<div class="person_type_seat_wrap">
							<p class="mb-2"><strong><?php esc_html_e( 'Person Type:', 'eventlist' ); ?></strong></p>
							<ul class="person_type_seat">
								<?php if ( isset( $value_ticket_map['person_type_seat'] ) && $value_ticket_map['person_type_seat'] ): ?>
									<?php $person_type = json_decode( $value_ticket_map['person_type_seat'] ); ?>
									<?php foreach ( $person_type as $key => $value ): ?>
										<li class="item">
	                                        <input type="text" class="pertype_seat" required onpaste="return false;" value="<?php echo esc_attr( $value ); ?>" data-slug="<?php echo esc_attr( $key ); ?>">
	                                        <button type="button" class="button remove_pertype_seat">x</button>
	                                    </li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
							<a href="#" class="button button-secondary add_pertype_seat">
								<?php esc_html_e( 'Add person type', 'eventlist' ); ?>
							</a>
							<input type="hidden" id="person_type_seat_data" name="<?php echo esc_attr( $this->get_mb_name('ticket_map[person_type_seat]') ); ?>" value="" />
						</div>

						<p style="font-weight: bold"><?php esc_html_e('Add Seat:', 'eventlist'); ?></p>
						<?php if ( isset( $value_ticket_map['seat'] ) && $value_ticket_map['seat'] ):
							foreach ( $value_ticket_map['seat'] as $key => $value ): ?>
								<div class="item_seat" data-prefix="<?php echo esc_attr( OVA_METABOX_EVENT ); ?>">
									<div class="name_seat_map">
										<label><?php esc_html_e( 'Seat:', 'eventlist' ) ?></label>
										<input
											type="text"
											class="map_name_seat"
											value="<?php echo esc_attr( $value['id'] ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][id]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( 'A1, A2, A3, ...', 'eventlist' ); ?>"
										/>
									</div>
									<div class="price_seat_map">
										<label>
											<?php esc_html_e( 'Price:', 'eventlist' ) ?><?php echo esc_html( ' ('. $currency .'):' ); ?>
										</label>
										<input
											type="text"
											class="map_price_seat"
											value="<?php echo esc_attr( $value['price'] ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][price]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( '50.00', 'eventlist' ); ?>"
										/>
									</div>
									<?php $person_price = isset( $value['person_price'] ) ? $value['person_price'] : ""; ?>
										<input type="hidden" class="person_price" name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][person_price]') ); ?>" value="<?php echo esc_attr( $person_price ); ?>">
									<div class="type_seat_map">
										<label><?php esc_html_e( 'Type Seat:', 'eventlist' ); ?></label>
										<select
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][type_seat]') ); ?>"
											class="select_type_seat"
											data-default="<?php esc_attr_e( 'Select Type Seat', 'eventlist' ); ?>">
											<option value=""><?php esc_html_e( 'Select Type Seat', 'eventlist' ); ?></option>
											<?php if ( ! empty( $type_seat ) ):
												$val_type_seat = isset( $value['type_seat'] ) ? $value['type_seat'] : '';

												foreach ( $type_seat as $v_type_seat ):
											?>
													<option value="<?php echo esc_attr( $v_type_seat ); ?>"<?php selected( $v_type_seat, $val_type_seat ); ?>>
														<?php echo esc_html( $v_type_seat ); ?>
													</option>
											<?php endforeach; endif; ?>
										</select>
									</div>
									<div class="map_seat_start_date">
										<label><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][start_date]') ); ?>"
											class="seat_start_date"
											value="<?php echo isset( $value['start_date'] ) && $value['start_date'] ? esc_attr( $value['start_date'] ) : ''; ?>"
											data-format="<?php echo esc_attr( $format ); ?>"
											data-firstday="<?php echo esc_attr( $first_day ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_seat_start_time">
										<label><?php esc_html_e( 'Start Time:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][start_time]') ); ?>"
											class="seat_start_time"
											value="<?php echo isset( $value['start_time'] ) && $value['start_time'] ? esc_attr( $value['start_time'] ) : ''; ?>"
											data-time="<?php echo esc_attr( $time ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_seat_end_date">
										<label><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][end_date]') ); ?>"
											class="seat_end_date"
											value="<?php echo isset( $value['end_date'] ) && $value['end_date'] ? esc_attr( $value['end_date'] ) : ''; ?>"
											data-format="<?php echo esc_attr( $format ); ?>"
											data-firstday="<?php echo esc_attr( $first_day ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_seat_end_time">
										<label><?php esc_html_e( 'End Time:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[seat]['.$key.'][end_time]') ); ?>"
											class="seat_end_time"
											value="<?php echo isset( $value['end_time'] ) && $value['end_time'] ? esc_attr( $value['end_time'] ) : ''; ?>"
											data-time="<?php echo esc_attr( $time ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<a href="#" class="button remove_seat_map"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
								</div>
							<?php endforeach;
						endif; ?>
					</div>
					<button class="button add_seat_map">
						<?php esc_html_e( 'Add new seat', 'eventlist' ); ?>
						<div class="submit-load-more">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>
					</button>
					<div class="wrap_area_map" style="display: none;">

						<div class="person_type_wrapper pb-3">
							<p class="mb-2"><?php esc_html_e( 'Person Type:', 'eventlist' ); ?></p>
							<ul class="person_type_list list-unstyled">
								<?php if ( isset( $value_ticket_map['person_type'] ) && $value_ticket_map['person_type'] ): ?>
									<?php $person_type = json_decode( $value_ticket_map['person_type'] ); ?>
									<?php foreach ( $person_type as $key => $value ): ?>
										<li class="item">
	                                        <input type="text" class="person_type" onpaste="return false;" value="<?php echo esc_attr( $value ); ?>" data-slug="<?php echo esc_attr( $key ); ?>">
	                                        <button type="button" class="button remove_person_type">x</button>
	                                    </li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
							<?php $person_type_val = isset( $value_ticket_map['person_type'] ) ? $value_ticket_map['person_type'] : '' ; ?>
							<input type="hidden" id="data_person_type" name="<?php echo esc_attr( $this->get_mb_name('ticket_map[person_type]') ); ?>" value="<?php echo esc_attr( json_encode( $person_type_val ) ); ?>">
							<button type="button" class="button add_person_type"><?php esc_html_e( 'Add person type', 'eventlist' ); ?></button>
						</div>

						<p style="font-weight: bold"><?php esc_html_e('Add Area:', 'eventlist'); ?></p>
						<?php if ( isset( $value_ticket_map['area'] ) && $value_ticket_map['area'] ):
							foreach ( $value_ticket_map['area'] as $key => $value ): ?>
								<div class="item_area" data-prefix="<?php echo esc_attr( OVA_METABOX_EVENT ); ?>">
									<div class="name_area_map">
										<label><?php esc_html_e( 'Area:', 'eventlist' ) ?></label>
										<input
											type="text"
											class="map_name_area"
											value="<?php echo esc_attr( $value['id'] ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][id]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( 'insert only an area', 'eventlist' ); ?>"
										/>
									</div>
									<div class="price_area_map">
										<label>
											<?php esc_html_e( 'Price:', 'eventlist' ) ?><?php echo esc_html( ' ('. $currency .'):' ); ?>
										</label>
										<input
											type="text"
											class="map_price_area"
											value="<?php echo esc_attr( $value['price'] ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][price]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( '50.00', 'eventlist' ); ?>"
										/>
									</div>
									<?php $person_price = isset( $value['person_price'] ) ? $value['person_price'] : ""; ?>
										<input type="hidden" class="person_price" name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][person_price]') ); ?>" value="<?php echo esc_attr( $person_price ); ?>">
									<div class="qty_area_map">
										<label><?php esc_html_e( 'Quantity:', 'eventlist' ) ?></label>
										<input
											type="number"
											class="map_qty_area"
											value="<?php echo esc_attr( $value['qty'] ); ?>"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][qty]') ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
											placeholder="<?php echo esc_attr( '100', 'eventlist' ); ?>"
											min="0"
										/>
									</div>
									<div class="type_area_map">
										<label><?php esc_html_e( 'Type Seat:', 'eventlist' ); ?></label>
										<select
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][type_seat]') ); ?>"
											class="select_type_area"
											data-default="<?php esc_attr_e( 'Select Type Seat', 'eventlist' ); ?>">
											<option value=""><?php esc_html_e( 'Select Type Seat', 'eventlist' ); ?></option>
											<?php if ( ! empty( $type_seat ) ):
												$val_type_seat = isset( $value['type_seat'] ) ? $value['type_seat'] : '';

												foreach ( $type_seat as $v_type_seat ):
											?>
													<option value="<?php echo esc_attr( $v_type_seat ); ?>"<?php selected( $v_type_seat, $val_type_seat ); ?>>
														<?php echo esc_html( $v_type_seat ); ?>
													</option>
											<?php endforeach; endif; ?>
										</select>
									</div>
									<div class="map_area_start_date">
										<label><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][start_date]') ); ?>"
											class="area_start_date"
											value="<?php echo isset( $value['start_date'] ) && $value['start_date'] ? esc_attr( $value['start_date'] ) : ''; ?>"
											data-format="<?php echo esc_attr( $format ); ?>"
											data-firstday="<?php echo esc_attr( $first_day ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_area_start_time">
										<label><?php esc_html_e( 'Start Time:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][start_time]') ); ?>"
											class="area_start_time"
											value="<?php echo isset( $value['start_time'] ) && $value['start_time'] ? esc_attr( $value['start_time'] ) : ''; ?>"
											data-time="<?php echo esc_attr( $time ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_area_end_date">
										<label><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][end_date]') ); ?>"
											class="area_end_date"
											value="<?php echo isset( $value['end_date'] ) && $value['end_date'] ? esc_attr( $value['end_date'] ) : ''; ?>"
											data-format="<?php echo esc_attr( $format ); ?>"
											data-firstday="<?php echo esc_attr( $first_day ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<div class="map_area_end_time">
										<label><?php esc_html_e( 'End Time:', 'eventlist' ); ?></label>
										<input
											type="text"
											name="<?php echo esc_attr( $this->get_mb_name('ticket_map[area]['.$key.'][end_time]') ); ?>"
											class="area_end_time"
											value="<?php echo isset( $value['end_time'] ) && $value['end_time'] ? esc_attr( $value['end_time'] ) : ''; ?>"
											data-time="<?php echo esc_attr( $time ); ?>"
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
											autocomplete="off" autocorrect="off" autocapitalize="none"
										/>
									</div>
									<a href="#" class="button remove_area_map"><?php esc_html_e( 'x', 'eventlist' ); ?></a>
								</div>
							<?php endforeach;
						endif; ?>
					</div>
					<button class="button add_area_map" style="display: none;">
						<?php esc_html_e( 'Add new area', 'eventlist' ); ?>
						<div class="submit-load-more">
							<div class="load-more">
								<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
							</div>
						</div>
					</button>
				</div>
			</div>
		</div>
		<button class="button add_ticket" data-event_id="<?php echo esc_attr( $post->ID ); ?>" data-seat_option="<?php echo esc_attr( $this->get_mb_value( 'seat_option' ) ); ?>" style="<?php echo esc_attr( $seat_option !== 'map' ? 'display: block;' : 'display: none;' ); ?>">
			<?php esc_html_e( 'Add new ticket', 'eventlist' ); ?>
			<div class="submit-load-more">
				<div class="load-more">
					<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
				</div>
			</div>
		</button>
	</div>
<?php endif; ?>