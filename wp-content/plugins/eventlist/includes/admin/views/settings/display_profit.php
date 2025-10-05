<?php
if ( !defined( 'ABSPATH' ) ) {
	exit();
}

$filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : "";

$date_format = get_option('date_format');
$time_format = get_option('time_format');

$paged = isset($_GET['paged']) ? sanitize_text_field($_GET['paged']) : 1;

$events = get_list_event_close_diplay_profit($filter, $paged);

$active = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : '';

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Manage Payouts', 'eventlist' ); ?></h1>
	<a href="edit.php?post_type=payout_method" class="page-title-action">
		<?php esc_html_e( 'Payout Method', 'eventlist' ); ?>
	</a>

	<hr class="wp-header-end">
	<div class="el-event-profit">
		<div class="filter-status">
			<ul>
				<li><a href="?page=ova_el_display_profit_event" style="<?php echo ($active == '' ? 'color: red;' : ''); ?>"><?php esc_html_e("All", "eventlist") ?></a></li>
				<li><a href="?page=ova_el_display_profit_event&filter=pending" style="<?php echo ($active == 'pending' ? 'color: red;' : ''); ?>"><?php esc_html_e("Pending", "eventlist") ?></a></li>
				<li><a href="?page=ova_el_display_profit_event&filter=paid" style="<?php echo ($active == 'paid' ? 'color: red;' : ''); ?>"><?php esc_html_e("Paided", "eventlist") ?></a></li>
			</ul>
		</div>

		<table class="wp-list-table widefat fixed striped posts ">
			<thead class="event_head">
				<tr>
					<td><?php esc_html_e("Event", "eventlist") ?></td>
					<td><?php esc_html_e("End Date", "eventlist") ?></td>
					<td><?php esc_html_e("Total before tax", "eventlist") ?></td>
					<td><?php esc_html_e("Total after tax", "eventlist") ?></td>
					<td><?php esc_html_e("Profit", "eventlist") ?></td>
					<td><?php esc_html_e("Commission", "eventlist") ?></td>
					<td><?php esc_html_e("Tax", "eventlist") ?></td>
					<td><?php esc_html_e("Status", "eventlist") ?></td>
					<td><?php esc_html_e("Name vendor", "eventlist") ?></td>
					<td><?php esc_html_e("Account Owner", "eventlist") ?></td>
					<td><?php esc_html_e("Account Number", "eventlist") ?></td>
					<td><?php esc_html_e("Bank Name", "eventlist") ?></td>
					<td><?php esc_html_e("Branch", "eventlist") ?></td>
					<td><?php esc_html_e("Created", "eventlist") ?></td>
					<td><?php esc_html_e("Routing Number", "eventlist") ?></td>
					<td><?php esc_html_e("Paypal Account", "eventlist") ?></td>
					<td><?php esc_html_e("Stripe Account", "eventlist") ?></td>
				</tr>
			</thead>
			<tbody class="event_body" id="the-list">
				<?php 
				if($events->have_posts() ) : while ( $events->have_posts() ) : $events->the_post();

					$id_event = get_the_id();
					?>
					<tr class="status-publish hentry">
						<td><a target="_blank" href="<?php echo esc_attr(get_the_permalink()); ?>"><?php echo esc_html(get_the_title()) ?></a></td>
						
						<td>
							<?php $end_date_str = get_post_meta( $id_event, OVA_METABOX_EVENT.'end_date_str', true );
							if (!empty($end_date_str)) {
								echo esc_html( date_i18n( get_option( 'date_format' ), $end_date_str ) );
							}
							?>
						</td>

						<td>
							<?php // Get Total before tax in an Event
								$total_before_tax = get_total_before_tax_by_id_event( $id_event );
								echo esc_html( el_price( $total_before_tax ) );
							 ?>
						</td>

						<td>
							<?php // Get Total after tax in an Event
								$total_after_tax = get_total_after_tax_by_id_event( $id_event );
								echo esc_html( el_price( $total_after_tax ) );
							 ?>
						</td>

						<td>
							<?php // Get Total Profit in an Event
							$profit = get_profit_by_id_event( $id_event );
							echo esc_html( el_price( $profit ) );
							?>
						</td>

						<td>
							<?php // Get Total Commission in an Event
							$commission = get_commission_by_id_event( $id_event );
							echo esc_html( el_price( $commission ) );
							?>
						</td>

						<td>
							<?php // Get Total Tax in an Event
							$total_tax = get_tax_by_id_event( $id_event );
							echo esc_html( el_price( $total_tax ) );
							?>
						</td>
						
						<td>
							<?php
							$status = get_post_meta( $id_event, OVA_METABOX_EVENT . 'status_pay', true );
							switch ( $status ) {
								case "pending" : {
									$html_status_text = __("Pending", "eventlist");
									$html_status_btn = __("Paid", "eventlist");
									$attr_status = 'paid';
									break;
								}

								case "paid" : {
									$html_status_text = __("Paid", "eventlist");
									$html_status_btn = __("Pending", "eventlist");
									$attr_status = 'pending';
									break;
								}

								default: {
									$html_status_text = __("Pending", "eventlist");
									$html_status_btn = __("Paid", "eventlist");
									$attr_status = 'paid';
									break;
								}
							}
							?>
							<span class="text"><?php echo esc_html($html_status_text) ?></span>
							<button class="button-load-ova">
								<div class="submit-load-more">
									<div class="load-more">
										<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
									</div>
								</div>
								<a href="javascript:void(0)" data-status="<?php echo esc_attr($attr_status) ?>"  data-id="<?php echo esc_attr($id_event) ?>" class="update-pay-profit button download-ticket"><?php echo esc_html($html_status_btn) ?></a>
							</button>
						</td>
						
						<td>
							<?php
							$id_author = get_post_field('post_author', $id_event);
							$user_obj = get_userdata($id_author);
							echo esc_html($user_obj->data->user_nicename);
							?>
						</td>
						
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_owner', true)) ?></td>
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_number', true)) ?></td>
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_name', true)) ?></td>
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_branch', true)) ?></td>
						
						<td><?php
						$time_update = get_post_meta($id_event, OVA_METABOX_EVENT . 'date_update', true);

						if (!empty($time_update)) {
							echo esc_html( date_i18n($date_format, $time_update) . ' @ ' . date_i18n($time_format, $time_update) );
						}

						?></td>

						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_routing', true)) ?></td>
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_paypal_email', true)) ?></td>
						<td><?php echo esc_html(get_user_meta($id_author, 'user_bank_stripe_account', true)) ?></td>

					</tr>
				<?php endwhile; else : ?> 
				<td colspan="8"><?php esc_html_e( 'Not Found Events', 'eventlist' ); ?></td> 
				<?php ; endif; wp_reset_postdata(); ?>

				
			</tbody>
		</table>
		<?php 
		$total = $events->max_num_pages;
		if ( $total > 1 ) {
			echo wp_kses_post( pagination_vendor($total, $paged) );
		}
		?>
	</div>
</div>


