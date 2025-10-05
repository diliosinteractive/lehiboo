<?php if ( !defined( 'ABSPATH' ) ) exit();

global $post;

$id_payment = get_the_ID();


$format = el_date_time_format_js();
$placeholder = date_i18n(el_date_time_format_js_reverse($format), '1577664000' );


$screen = get_current_screen();

?>
<div class="el_payment_detail">
	

	<div class="ova_row">
		<p class="success status"></p>
		<p class="error status"></p>
	</div>


		

		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Payment ID",  "eventlist" ); ?>: </strong>
				#<?php echo esc_html( $post->ID ); ?>
			</label>
			<br><br>
		</div>

		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Amount",  "eventlist" ); ?>: </strong>
				<input type="text" class="Amount" value="<?php echo esc_attr($this->get_mb_value('amount')); ?>"  name="<?php echo esc_attr($this->get_mb_name('amount')); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
				
			</label>
			<br><br>
		</div>

		<div class="ova_row">
			<label>
				<strong><?php esc_html_e( "Time",  "eventlist" ); ?>: </strong>
				<?php 
					if( $this->get_mb_value('time') ){
						$time = gmdate( get_option( 'date_format' ).' '.get_option( 'time_format' ), $this->get_mb_value('time')  );
						echo esc_html( $time ); 	
					}
				?>
				
			</label>
			<br><br>
		</div>
	
		<div class="ova_row" >
			<label for="">
				<strong>
					<?php esc_html_e( "Profit Status", "eventlist" ); ?>: 
				</strong>
				<?php
				$withdrawal_status = $this->get_mb_value( 'withdrawal_status');
				?>
				<select name="<?php echo esc_attr($this->get_mb_name( 'withdrawal_status' )) ?>">

					<option value="Pending" <?php selected( 'Pending', $withdrawal_status, 'selected' ); ?> ><?php esc_html_e( 'Pending', 'eventlist' ); ?></option>

					<option value="Completed" <?php selected( 'Completed', $withdrawal_status, 'selected' ); ?> ><?php esc_html_e( 'Completed', 'eventlist' ); ?></option>

					<option value="Canceled" <?php selected( 'Canceled', $withdrawal_status, 'selected' ); ?> ><?php esc_html_e( 'Canceled', 'eventlist' ); ?></option>

				</select>
			</label>
			<br><br>
		</div>
		
		<!-- display extra info -->
		<div class="ova_row ova_payout_extra_info">
			<label for="">
				<strong>
					<?php esc_html_e( "Extra Info", "eventlist" ); ?>: 
				</strong>
				<?php $extra_info = $this->get_mb_value( 'extra_info') ?>
				<textarea name="<?php echo esc_attr( $this->get_mb_name( 'extra_info' ) ); ?>" id="<?php echo esc_attr( $this->get_mb_name( 'extra_info' ) ); ?>" cols="30" rows="10"><?php echo esc_html( $extra_info ); ?></textarea>
			</label>
			<br><br>
		</div>

		<div class="ova_row" >
			<label for="">
				<div class="ova_row payout_method">
					<strong><?php esc_html_e( "Payment Method", "eventlist" ); ?>: </strong>

				<?php

				if($this->get_mb_value('payout_method')==''){
					?><strong><?php echo  esc_html__('Bank ', 'eventlist'); ?></strong></div><?php


				

				}elseif ($this->get_mb_value( 'payout_method')=='bank') {
					?>
						<strong><?php echo  esc_html__('Bank ', 'eventlist'); ?></div></strong>
					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Account Owner",  "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_owner')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Account Number", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_number')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Bank Name", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_name')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Branch", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_branch')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Routing Number", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_routing')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "IBAN", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_iban')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Swift Code", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_swift_code')); ?>
						</label>
						<br><br>
					</div>

					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "IFSC Code", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_ifsc_code')); ?>
						</label>
						<br><br>
					</div>
					<?php
				

				}elseif ($this->get_mb_value( 'payout_method')=='paypal'){


					?>
					<strong><?php echo  esc_html__('Paypal ', 'eventlist'); ?></div></strong>
					<div class="ova_row">
						<label>
							<strong><?php esc_html_e( "Paypal Account", "eventlist" ); ?>: </strong>

							<?php echo esc_attr($this->get_mb_value('user_bank_paypal_email')); ?>
						</label>
						<br><br>
					</div>
					<?php




				}{


					$title = get_the_title($this->get_mb_value( 'payout_method'));
					?>
					<strong><?php echo esc_html( $title );  ?></strong>
					<?php

					$data_payout_method_field = $this->get_mb_value('data_payout_method_field'); 

					$data_payout_method_field = ! empty( $data_payout_method_field ) ? json_decode( $data_payout_method_field , true) : [];
					$list_payout_method_field = [];
					
					$list_field = get_post_meta( ($this->get_mb_value( 'payout_method')), 'ova_met_payout_method_group', true);

					?>

					<div class="field_payout_method">
						<?php if(!empty($list_field)) {?>
							<div class="list_field_payout_method">
								<ul>
									<?php
									foreach ($list_field as $field) {

										$label = isset($field['ova_met_label_method']) ? $field['ova_met_label_method'] : '';
										$name = isset($field['ova_met_name_method']) ? $field['ova_met_name_method'] : '';
										$payout_method_field = isset($data_payout_method_field[$name]) ? $data_payout_method_field[$name] : '' ;
										?>

										<li class="vendor_field other_field_method ova_row">

											<div class="label">
												<label for="<?php echo esc_attr( $name ) ?>">
													<strong><?php echo esc_html( $label ); ?>:</strong>
													<?php echo esc_attr($payout_method_field); ?>
												</label>
											</div>

										</li>
										<?php
									}
									?>
								</ul>
							</div>
						<?php }?>
					</div>

					<?php

				}

		?>
	</label>
	<br><br>
</div>

	

	

</div>

<?php wp_nonce_field( 'ova_metaboxes', 'ova_metaboxes' ); ?>





