<?php 
if ( !defined( 'ABSPATH' ) ) exit();

$current_range = isset( $_GET['range'] ) ? $_GET['range'] : '7_day';

$format = el_date_time_format_js();
$first_day = el_first_day_of_week();
$placeholder = gmdate( el_date_time_format_js_reverse($format), current_time('timestamp') );


$range = isset( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7_day';
if ( $range == 'custom' ) {
	$start_date = ( $_GET['start_date'] && isset( $_GET['start_date'] ) ) ? sanitize_text_field( $_GET['start_date'] ) : gmdate( 'Y-m-d', strtotime('-3 years', current_time('timestamp') ) );
	$end_date = ( $_GET['end_date'] && isset( $_GET['end_date'] ) ) ? sanitize_text_field( $_GET['end_date'] ) : gmdate('Y-m-d', current_time('timestamp') );
} else {
	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : gmdate( 'Y-m-d', strtotime('-10 years', current_time('timestamp') ) );
	$end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : gmdate('Y-m-d', current_time('timestamp') );
}

$str_start_date = strtotime($start_date);
$str_end_date = strtotime($end_date);

$day_start_date = ( new DateTime($start_date) )->format('d');
$month_start_date = ( new DateTime($start_date) )->format('m');
$year_start_date = ( new DateTime($start_date) )->format('y');

$day_end_date = ( new DateTime($end_date) )->format('d');
$month_end_date = ( new DateTime($end_date) )->format('m');
$year_end_date = ( new DateTime($end_date) )->format('y');

$month_current_date = ( new DateTime() )->format('m');
$year_current_date = ( new DateTime() )->format('y');

$last_month_current_date = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );

$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
$first_month_current_year = strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) );

$last_month_current_year = strtotime( gmdate( 'Y-12-01', current_time( 'timestamp' ) ) );

$first_day_last_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );

$currency = _el_symbol_price();
$currency_position = EL()->options->general->get( 'currency_position','left' );


if ( $range == '7_day' ) {
	$chart_interval = absint( ceil( max( 0, ( $str_end_date - strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

} elseif ($range == 'month') {
	$chart_interval = absint( ceil( max( 0, ( $str_end_date - strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

} elseif ($range == 'last_month') {
	$chart_interval = absint( floor( max( 0, ( strtotime( gmdate( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) ) - strtotime( gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

} elseif ($range == 'year') {
	$chart_interval = ( new DateTime() )->format('m');

} elseif ($range == 'custom') {
	$chart_interval = absint( ceil( max( 0, ( $str_end_date - $str_start_date ) / ( 60 * 60 * 24 ) ) ) );
}

// day, this month, last month, year
if ( $range != 'custom' ) {

	if ( $range == 'year' ) {
		$chart_groupby = 'month';
		$i = $chart_interval;
	} else {
		$chart_groupby = 'day';
		$i = $chart_interval + 1;
	}

	while ( $i > 0  ) {
		$i--;
		if ( $range == 'last_month' ) {
			$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', strtotime( '-1 DAY', $first_day_current_month ) ) );
			$before = $after;

		} elseif ( $range == 'year' ) {
			$after = gmdate('Y-m-01',  strtotime( ('-' . $i . ' Month'), $last_month_current_date ) );
			$before = gmdate( "Y-m-t", strtotime( $after ) );

		} else {
			$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) );
			$before = $after;
		}

		// Query Booking
		$total_user_subscriber = report_users_get_total_user_registered( 'subscriber', $after, $before );
		$data_total_user_subscriber[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber );

		$total_user_event_manager = report_users_get_total_user_registered( 'el_event_manager', $after, $before );
		$data_total_user_event_manager[] = report_users_get_data_total_user_registered( $after, $total_user_event_manager );
		// $total_user_registered[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber, $total_user_role_event_manager );

	}
}

// Custom
if ( $range == 'custom' && $chart_interval >= 100 ) {
	$chart_groupby = 'month';
	$count_month = 0;
	while ( ($str_start_date = strtotime("+1 MONTH", $str_start_date) ) <= $str_end_date) {
		$count_month++;
	}

	$m = ($count_month + 1);

	while ( $m >= 0 ) {
		if ( $m == $count_month + 1 ) {
			$after = gmdate( ( $year_start_date . '-'. $month_start_date .'-' . $day_start_date ) );
			$after = gmdate('Y-m-d',strtotime( $after ) );
			$before = gmdate( "Y-m-t", strtotime( $after ) );

		} elseif ( ( $m > 0 ) && ( $m <= $count_month ) ) {
			$after = gmdate('Y-m-01',  strtotime( ('-' .($m). ' month'), $last_month_current_date ) );
			$before = gmdate( "Y-m-t", strtotime( $after ) );

		} elseif ( $m == 0 ) {
			$after = gmdate( ( $year_end_date . '-'. $month_end_date .'-01' ) );
			$after = gmdate('Y-m-d',strtotime( $after ) );
			$before = gmdate('Y-m-d', $str_end_date);
		}

		// Query Booking
		$total_user_subscriber = report_users_get_total_user_registered( 'subscriber', $after, $before );
		$data_total_user_subscriber[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber );

		$total_user_event_manager = report_users_get_total_user_registered( 'el_event_manager', $after, $before );
		$data_total_user_event_manager[] = report_users_get_data_total_user_registered( $after, $total_user_event_manager );
		// $total_user_registered[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber, $total_user_role_event_manager );

		$m --;
	}
} elseif ( $range == 'custom' && $chart_interval < 100 ) {
	$chart_groupby = 'day';
	$i = $chart_interval;
	while ( $i >= 0  ) {
		$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', $str_end_date ) );
		$before = $after;

		// Query Booking
		$total_user_subscriber = report_users_get_total_user_registered( 'subscriber', $after, $before );
		$data_total_user_subscriber[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber );

		$total_user_event_manager = report_users_get_total_user_registered( 'el_event_manager', $after, $before );
		$data_total_user_event_manager[] = report_users_get_data_total_user_registered( $after, $total_user_event_manager );
		// $total_user_registered[] = report_users_get_data_total_user_registered( $after, $total_user_subscriber, $total_user_event_manager );

		$i--;
	}
}

// Return data chart
$data_total_user_subscriber = wp_json_encode( $data_total_user_subscriber );
$data_total_user_event_manager = wp_json_encode( $data_total_user_event_manager );

$name_month = array_reduce(range(1,12),function($rslt,$m){ $rslt[$m] = date_i18n('M',mktime(0,0,0,$m,10)); return $rslt; });

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Report Users', 'eventlist' ); ?>
	</h1>
	<a href="admin.php?page=ova_el_display_report_sales" class="page-title-action"><?php esc_html_e( 'Report Sales', 'eventlist' ); ?></a>

	<hr class="wp-header-end">
	<div class="vendor_wrap"> 

		<div class="report_user">
			

			<div class="accounting">
				<ul class="filter">
					<li class="<?php echo ( 'year' === $current_range ) ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ova_el_display_report_user', 'range' => 'year' ), admin_url('admin.php') ) ); ?>">
							<?php esc_html_e( 'Year', 'eventlist' ); ?>
						</a>
					</li>

					<li class="<?php echo ( 'last_month' === $current_range ) ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ova_el_display_report_user', 'range' => 'last_month' ), admin_url('admin.php') ) ); ?>">
							<?php esc_html_e( 'Last Month', 'eventlist' ); ?>
						</a>
					</li>

					<li class="<?php echo ( 'month' === $current_range ) ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ova_el_display_report_user', 'range' => 'month' ), admin_url('admin.php') ) ); ?>">
							<?php esc_html_e( 'This Month', 'eventlist' ); ?>
						</a>
					</li>

					<li class="<?php echo ( '7_day' === $current_range ) ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ova_el_display_report_user', 'range' => '7_day' ), admin_url('admin.php') ) ); ?>">
							<?php esc_html_e( 'Last 7 days', 'eventlist' ); ?>
						</a>
					</li>

					<li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
						<span><?php esc_html_e( 'Custom:', 'eventlist' ); ?></span>
						<form method="GET">
							<div>

								<input type="hidden" name="page" value="ova_el_display_report_user" />
								<input type="hidden" name="range" value="custom" />

								<input type="text" 
								size="16" 
								value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" 
								name="start_date" 
								class="range_datepicker from" 
								autocomplete="nope" autocorrect="off" autocapitalize="none" 
								placeholder="<?php echo esc_attr( $placeholder ); ?>" 
								data-format="<?php echo esc_attr( $format ); ?>" 
								data-firstday="<?php echo esc_attr( $first_day ); ?>" />
								<span>&ndash;</span>

								<input type="text" 
								size="16" 
								value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" 
								name="end_date"
								class="range_datepicker to" 
								autocomplete="nope" autocorrect="off" autocapitalize="none" 
								placeholder="<?php echo esc_attr( $placeholder ); ?>" 
								data-format="<?php echo esc_attr( $format ); ?>" 
								data-firstday="<?php echo esc_attr( $first_day ); ?>" />

								<button type="submit" class="button" ><?php esc_html_e( 'Go', 'eventlist' ); ?></button>

							</div>
						</form>
					</li>
				</ul>

				<div 
				id="chart_user" 
				style="width: 100%; height: 400px;" 
				data-subscriber="<?php echo esc_attr($data_total_user_subscriber); ?>" 
				data-manager="<?php echo esc_attr($data_total_user_event_manager); ?>" 
				data-currency_position="<?php echo esc_attr($currency_position); ?>" 
				data-currency="<?php echo esc_attr($currency); ?>" 
				data-name_month="<?php echo wp_json_encode($name_month); ?>" ></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var data_subcriber = $('#chart_user').data('subscriber');
		var data_manager = $('#chart_user').data('manager');
		var currency = $('#chart_user').data('currency');
		var currency_position = $('#chart_user').data('currency_position');
		var name_month = $('#chart_user').data('name_month');

		var dataset = [
		{ label: "Subscriber", data: data_subcriber },
		{ label: "Event Manager", data: data_manager }
		];

		var options = {
			lines: { show: true, lineWidth: 2, fill: false },
			points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
			legend: { show: false },
			colors: [ '#03dbfc', '#e86c60' ],
			grid: {
				color: '#aaa',
				borderColor: 'transparent',
				borderWidth: 0,
				hoverable: true
			},
			xaxes: [ {
				color: '#aaa',
				position: "bottom",
				tickColor: 'transparent',
				mode: "time",
				timeformat: "<?php echo ( 'day' === $chart_groupby ) ? '%d %b' : '%b'; ?>",
				monthNames: JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $name_month ) ) ); ?>' ) ),
				minTickSize: [1, "<?php echo esc_js( $chart_groupby ); ?>"],
				tickLength: 1,
				font: {
					color: "#aaa"
				}
			} ],
			yaxes: [
			{
				min: 0,
				minTickSize: 1,
				tickDecimals: 0,
				color: '#d4d9dc',
				font: { color: "#aaa" }
			},
			{
				position: "right",
				min: 0,
				tickDecimals: 2,
				alignTicksWithAxis: 1,
				color: 'transparent',
				font: { color: "#aaa" }
			}
			],
			yaxis: {
				axisLabel: '%',
				axisLabelFontSizePixels: 12,
				tickFormatter: function (val, axis) {
					return val;
				},
			}
		};

		if ($('#chart_user').length > 0) {
			$.plot("#chart_user", dataset, options);
		}

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px 0",
			"background-color": "#fee",
			opacity: 0.80,
			width: '110px',
			'text-align': 'center'
		}).appendTo("body");

		$("#chart_user").bind("plothover", function (event, pos, item) {
			if (!pos.x || !pos.y) {
				return;
			}

			if (item) {
				var x = item.datapoint[0].toFixed(0);
				var y = item.datapoint[1].toFixed(0);
				let date = new Date( parseInt(x) );

				var data_month_php = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $name_month ) ) ); ?>' ) );

				var monthName = data_month_php[date.getMonth()];

				var dayName = date.getDate();

				if (item.seriesIndex == 0) {
					$("#tooltip").html( dayName + " " + monthName + ": " + y + " Users" ).css({top: item.pageY-40, left: item.pageX-55}).fadeIn(200);
				} else {
					$("#tooltip").html( dayName + " " + monthName + ": " + y + " Vendors" ).css({top: item.pageY-40, left: item.pageX-55}).fadeIn(200);
				}
				
			} else {
				$("#tooltip").hide();
			}
		});
	});
</script>