<?php  if ( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>
	
	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' );
		
		$user_id = wp_get_current_user()->ID;
		if (empty($user_id)) exit();


		// Get all Bookings
		$bookings = EL_Booking::instance()->get_list_bookings( $user_id );
		$number_booking = count($bookings->get_posts());

		$check_payout_method = true;
		$payout_method_user = get_user_meta( $user_id, 'payout_method', true );

		
		
		if(( $payout_method_user ) == '' ){

			$msg = esc_html__('You must Set Payout Method ', 'eventlist');
			$check_payout_method = false;

		}else if(( $payout_method_user ) == 'bank' ) {

			$msg = esc_html__('Bank ', 'eventlist');

		}else if (( $payout_method_user ) == 'paypal' ){

			$msg = esc_html__(' Paypal', 'eventlist');

		}else {

			$title = get_the_title($payout_method_user);

			$msg = sprintf( __( '%s', 'eventlist' ), $title ); 

		}

		$total_profit  = EL_Payout::instance()->get_total_profit( $user_id );
		$total_amount_payout = EL_Payout::instance()->get_total_amount_payout( $user_id );

		$withdrawable = $total_profit - $total_amount_payout > 0 ? $total_profit - $total_amount_payout : 0;   

		?>

		
		<div class="wallet_list">

			<div class="item color_1">
				<div class="wallet_total">

					<i class="icon icon_currency"></i>

					<h4><?php echo esc_html(el_price($withdrawable)) ?></h4>

					<span><?php echo esc_html__( 'Withdrawable Balance', 'eventlist' ); ?></span>

					<br>
					
					<div id="Btn_Withdraw" class="Withdraw">
						<?php echo esc_html__( 'Withdraw', 'eventlist' ); ?>
					</div>

					<div id="myModal" class="modal withdraw_form">
						<div class="modal-content">
							<span class="close">&times;</span>
							<form  class="form-Withdraw">

								<div class="payment_methods_info">
									<?php esc_html_e("Payout methods:",'eventlist'); ?>
									<span>
										<?php echo $msg; ?>
									</span>
								</div>

								<?php if( $check_payout_method ){ ?>
									<div class="fields">

										<input type="text" id="amount" placeholder="<?php esc_attr_e( 'Enter amount', 'eventlist' ); ?>" name="amount" required>
										<input type="submit"  class="el_add_withdrawal" name="el_add_withdrawal" value="<?php esc_attr_e( 'Withdraw', 'eventlist' ); ?>">

									</div>

									<div class="withdraw_balance_info">
										<?php esc_html_e( 'Withdrawable Balance:', 'eventlist' ); ?>
										<span>
											<?php echo el_price( $withdrawable ); ?>
										</span>
									</div>

								<?php }else{ ?>

									<?php $profile_page = add_query_arg( array(  'vendor' => 'profile#author_bank'), get_myaccount_page() ); ?>
									<a class="set_payment_method" href="<?php echo  esc_url( $profile_page ); ?>">
										<?php esc_html_e( 'Set Payout methods', 'eventlist' ); ?>
									</a>

								<?php } ?>

								

								<div class="submit-load-more">
									<div class="load-more">
										<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
									</div>
								</div>

								<input type="hidden" id="check_withdraw" name="check_withdraw" value="<?php echo esc_attr( $withdrawable); ?>">								
								<?php wp_nonce_field( 'el_add_withdrawal_nonce', 'el_add_withdrawal_nonce' ); ?>

							</form>
						</div>
					</div>
					
				</div>
			</div>

			<div class="item color_2">
				<i class="icon icon_wallet"></i>
				<div class="wallet_total">
					<h4>
						<?php echo esc_html(el_price($total_profit)) ?>
					</h4>
					<span>
						<?php echo esc_html__( 'Total Profit', 'eventlist' ); ?>
					</span>
				</div>
				
			</div>

			<div class="item color_3">
				<i class="icon icon_cart_alt"></i>	
				<div class="wallet_total">
					<h4>
						<?php echo esc_html($number_booking) ?>
					</h4>
					<span>
						<?php echo esc_html__( 'Total Booking', 'eventlist' ); ?>
					</span>
				</div>
			</div>

		</div>

		<div class="list-box-wallet">
			<div class="list-payout-history">

				<h4>
					<?php esc_html_e( 'Payout History', 'eventlist' ); ?>
				</h4>

				<table>

					<thead>
						<tr class="title-payout-history">
							<td>
								<?php esc_html_e( 'Amount', 'eventlist' ); ?>
							</td>
							<td>
								<?php esc_html_e( 'Time', 'eventlist' ); ?>
							</td>
							<td>
								<?php esc_html_e( 'Status', 'eventlist' ); ?>
							</td>
							<td>
								<?php esc_html_e( 'Method', 'eventlist' ); ?>
							</td>
						</tr>
					</thead>

					<tbody>
					
					   <?php

					   $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
					   $list_payout = EL_Payout::instance()->get_list_payout($paged, $user_id);
	            
						if($list_payout->have_posts() ) : while ( $list_payout->have_posts() ) : $list_payout->the_post();

							$id_payment = get_the_id();
							$amount = floatval( get_post_meta( $id_payment, OVA_METABOX_EVENT . 'amount', true ) );
							$time =  get_post_meta( $id_payment, OVA_METABOX_EVENT . 'time', true );
							$withdrawal_status =   get_post_meta( $id_payment, OVA_METABOX_EVENT . 'withdrawal_status', true );

							$payout_method =   get_post_meta( $id_payment, OVA_METABOX_EVENT . 'payout_method', true );
						?>
							<tr>
								<td class="price" data-colname="<?php esc_attr_e( 'Amount:', 'eventlist' ); ?>">
									<?php echo el_price( $amount ); ?>
								</td>
								<td class="time" data-colname="<?php esc_attr_e( 'Time:', 'eventlist' ); ?>">
									<?php 
									if( $time ){
										echo date( get_option( 'date_format' ).' '.get_option( 'time_format' ), $time );
									}
									?>	
								</td>
								<td class="status" data-colname="<?php esc_attr_e( 'Status:', 'eventlist' ); ?>">
									<?php echo esc_html( $withdrawal_status ); ?>
								</td>
								<td class="method" data-colname="<?php esc_attr_e( 'Method:', 'eventlist' ); ?>">
									<?php
									if(( $payout_method) == 'bank' ) {

										$method = esc_html__('Bank ', 'eventlist');

									}else if (( $payout_method ) == 'paypal' ){

										$method = esc_html__(' Paypal', 'eventlist');

									}else {

										$title = get_the_title($payout_method);

										$method = sprintf( __( '%s', 'eventlist' ), $title ); 

									}
									?>
									<?php echo ( $method); ?>
								</td>
							</tr>
						<?php endwhile; endif; wp_reset_postdata(); ?>
					</tbody>

					<tfoot>
						<tr>
							<?php $total = $list_payout->max_num_pages; ?>
							<?php if ( $total > 1 ) { ?>
								<td colspan="8">
									<?php echo pagination_vendor($total); ?>
								</td>			
							<?php } ?>

						</tr>
					</tfoot>

				</table>

			</div>

			<div class="list-payment-menthod">

				<div class="payment_method">
					
					<h4>
						<?php esc_html_e( 'Payout methods:', 'eventlist' ); ?>
					</h4>

					<span class="title_payment">
						<?php echo esc_html($msg); ?>
					</span>
				</div>
				
				<?php $profile_page = add_query_arg( array(  'vendor' => 'profile#author_bank'), get_myaccount_page() ); ?>
				<a class="set_payment_method" href="<?php echo  esc_url( $profile_page ); ?>">
					<?php esc_html_e( 'Set Payout methods', 'eventlist' ); ?>
				</a>
		
			</div>

		</div>
	</div>
	
</div>
