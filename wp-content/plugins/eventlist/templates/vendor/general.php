<?php 
if ( !defined( 'ABSPATH' ) ) exit();

$current_range = isset( $_GET['range'] ) ? $_GET['range'] : '7_day';

$format = el_date_time_format_js();
$first_day = el_first_day_of_week();
$placeholder = date( el_date_time_format_js_reverse($format), current_time('timestamp') );


?>

<div class="vendor_wrap"> 

	<?php echo el_get_template( '/vendor/sidebar.php' ); ?>

	<div class="contents">
		<?php echo el_get_template( '/vendor/heading.php' ); ?>

		<?php 
			$id_user = get_current_user_id();

			if( EL()->options->package->get('enable_package', 'yes') == 'yes' && el_is_vendor() && ! el_is_administrator() ){ ?>
		
		<?php

			
			$list_membership = EL_Package::instance()->get_info_membership_by_user_id($id_user);

			$membership_id 	= $list_membership['id'];
			$start_date_tmp = $list_membership['start_date_tmp'];
			$posted_event 	= count( EL_Event::get_event_ids_by_membership_id( $membership_id ) );

			$id_package 		= $list_membership['id_package'];
			$total_event 		= get_post_meta( $membership_id, OVA_METABOX_EVENT.'event_limit', true );

			$remaining_event 	= ( $total_event == '-1' ) ? esc_html__( 'Unlimit','eventlist' ) : absint( $total_event ) - $posted_event;
		?>
			<div class="report_membership">
				<h3 class="vendor_report"><?php esc_html_e( 'Membership Report', 'eventlist' ); ?></h3>

				<ul class="mem_report">
					<li>
						<label><?php esc_html_e( 'Status', 'eventlist' ); ?></label>
						<div class="value"><?php echo $list_membership['status']; ?></div>
					</li>
					<li>
						<label><?php esc_html_e( 'Expiration Date', 'eventlist' ); ?></label>
						<div class="value"><?php echo $list_membership['end_date']; ?></div>
					</li>
					<li>
						<label><?php esc_html_e( 'Remaining Events', 'eventlist' ); ?></label>
						<div class="value"><?php echo $remaining_event; ?></div>
					</li>
					<li>
						<label><?php esc_html_e( 'Posted Events', 'eventlist' ); ?></label>
						<div class="value"><?php echo $posted_event; ?></div>
					</li>
				</ul>
			</div>
			<br>
		<?php } ?>

		<!-- KPI Dashboard -->
		<?php echo el_get_template( '/vendor/__dashboard-kpi.php' ); ?>

		<div class="accounting">

			<h3 class="vendor_report">
				<?php esc_html_e( 'Report Sales', 'eventlist' ); ?>
			</h3>

			<ul class="filter">
				<li class="<?php echo ( 'year' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'general', 'range' => 'year' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Year', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( 'last_month' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'general', 'range' => 'last_month' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Last Month', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( 'month' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'general', 'range' => 'month' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'This Month', 'eventlist' ); ?>
					</a>
				</li>

				<li class="<?php echo ( '7_day' === $current_range ) ? 'active' : ''; ?>">
					<a href="<?php echo add_query_arg( array( 'vendor' => 'general', 'range' => '7_day' ), get_myaccount_page() ); ?>">
						<?php esc_html_e( 'Last 7 days', 'eventlist' ); ?>
					</a>
				</li>

				<li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
					<span><?php esc_html_e( 'Custom:', 'eventlist' ); ?></span>
					<form method="GET">
						<div>
							<input type="hidden" name="vendor" value="general" />
							<input type="hidden" name="range" value="custom" />

							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" 
							name="start_date" 
							class="range_datepicker from" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>"  />
							<span>&ndash;</span>

							<input type="text" 
							size="16" 
							value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" 
							name="end_date"
							class="range_datepicker to" 
							autocomplete="off" autocorrect="off" autocapitalize="none" 
							placeholder="<?php echo esc_attr( $placeholder ); ?>" 
							data-format="<?php echo esc_attr( $format ); ?>" 
							data-firstday="<?php echo esc_attr( $first_day ); ?>" />

							<button type="submit" class="button" ><?php esc_html_e( 'Go', 'eventlist' ); ?></button>

						</div>
					</form>
				</li>
			</ul>

			<div class="chart">
				<div class="chart-sidebar">

					<?php
						$chart = el_get_chart( $_GET );
					?>

				</div>
				
				<div id="main_chart" 
					style="width: 100%; height: 400px;" 
					data-chart="<?php echo $chart['chart']; ?>" 
					data-currency_position="<?php echo esc_attr($chart['currency_position']); ?>" 
					data-currency="<?php echo esc_attr($chart['currency']); ?>" 
					data-name_month="<?php echo wp_json_encode($chart['name_month']); ?>" 
					data-monthnames="<?php echo $chart['monthnames'] ?>"
					data-chart_groupby="<?php echo $chart['chart_groupby']; ?>"
					data-timeformat="<?php echo $chart['timeformat']; ?>"
					data-chart_color="<?php echo $chart['chart_color']; ?>"
				>
				</div>
				
			</div>
		</div>
	</div>

</div>

