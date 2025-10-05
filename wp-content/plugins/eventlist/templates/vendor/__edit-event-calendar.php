<?php if ( ! defined( 'ABSPATH' ) ) exit();

$post_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$_prefix = OVA_METABOX_EVENT;

$time 		= el_calendar_time_format();
$format 	= el_date_time_format_js();
$first_day 	= el_first_day_of_week();

$placeholder_dateformat = el_placeholder_dateformat();
$placeholder_timeformat = el_placeholder_timeformat();

$calendar 			= get_post_meta( $post_id, $_prefix.'calendar', true) ? get_post_meta( $post_id, $_prefix.'calendar', true) : '';
$disable_date 		= get_post_meta( $post_id, $_prefix.'disable_date', true) ? get_post_meta( $post_id, $_prefix.'disable_date', true) : '';
$disable_time_slot 	= get_post_meta( $post_id, $_prefix.'disable_date_time_slot', true) ? get_post_meta( $post_id, $_prefix.'disable_date_time_slot', true) : '';
$schedules_time 	= get_post_meta( $post_id, $_prefix.'schedules_time', true) ? get_post_meta( $post_id, $_prefix.'schedules_time', true) : '';
$option_calendar 	= get_post_meta( $post_id, $_prefix.'option_calendar', true) ? get_post_meta( $post_id, $_prefix.'option_calendar', true) : 'manual';

$calendar_recurrence_id = get_post_meta( $post_id, $_prefix.'calendar_recurrence_id', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_id', true) : '';
$recurrence_bydays 		= get_post_meta( $post_id, $_prefix.'recurrence_bydays', true) ? get_post_meta( $post_id, $_prefix.'recurrence_bydays', true) : array();
$recurrence_byweekno 	= get_post_meta( $post_id, $_prefix.'recurrence_byweekno', true) ? get_post_meta( $post_id, $_prefix.'recurrence_byweekno', true) : '1';
$recurrence_byday 		= get_post_meta( $post_id, $_prefix.'recurrence_byday', true) ? get_post_meta( $post_id, $_prefix.'recurrence_byday', true) : '0';
$recurrence_frequency 	= get_post_meta( $post_id, $_prefix.'recurrence_frequency', true) ? get_post_meta( $post_id, $_prefix.'recurrence_frequency', true) : 'daily';
$recurrence_interval 	= get_post_meta( $post_id, $_prefix.'recurrence_interval', true) ? get_post_meta( $post_id, $_prefix.'recurrence_interval', true) : '';
$recurrence_days 		= get_post_meta( $post_id, $_prefix.'recurrence_days', true) ? get_post_meta( $post_id, $_prefix.'recurrence_days', true) : '0';

$calendar_recurrence_start_time 	= get_post_meta( $post_id, $_prefix.'calendar_recurrence_start_time', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_start_time', true) : '';
$calendar_recurrence_end_time 		= get_post_meta( $post_id, $_prefix.'calendar_recurrence_end_time', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_end_time', true) : '';
$calendar_recurrence_book_before 	= get_post_meta( $post_id, $_prefix.'calendar_recurrence_book_before', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_book_before', true) : '0';
$calendar_start_date 				= get_post_meta( $post_id, $_prefix.'calendar_start_date', true) ? get_post_meta( $post_id, $_prefix.'calendar_start_date', true) : '';
$calendar_end_date 					= get_post_meta( $post_id, $_prefix.'calendar_end_date', true) ? get_post_meta( $post_id, $_prefix.'calendar_end_date', true) : '';

$start_date_str = get_post_meta( $post_id, $_prefix.'start_date_str', true) ? get_post_meta( $post_id, $_prefix.'start_date_str', true) : '';
$end_date_str 	= get_post_meta( $post_id, $_prefix.'end_date_str', true) ? get_post_meta( $post_id, $_prefix.'end_date_str', true) : '';
$ts_start 	= get_post_meta( $post_id, $_prefix.'ts_start', true) ? get_post_meta( $post_id, $_prefix.'ts_start', true) : [];
$ts_end 	= get_post_meta( $post_id, $_prefix.'ts_end', true) ? get_post_meta( $post_id, $_prefix.'ts_end', true) : [];

?>

<div class="calendar">
	<p><?php esc_html_e( 'Create the time of the event', 'eventlist' ); ?></p>
	<div class="option_calendar vendor_field">
		<label><?php esc_html_e( 'Calendar Option:', 'eventlist' ); ?></label>

		<label for="option_calendar_manual" class="el_input_radio" style="min-width:auto;">
			<?php esc_html_e( 'Manual', 'eventlist' ); ?>
			<input 
				type="radio" 
				class="option_calendar"
				id="option_calendar_manual"
				name="<?php echo esc_attr( $_prefix.'option_calendar' ); ?>" 
				value="manual" <?php checked( $option_calendar, 'manual' ); ?>>
			<span class="checkmark"></span>
		</label>

		<label for="option_calendar_auto" class="el_input_radio el_ml_10px" style="min-width:auto;">
			<?php esc_html_e( 'Recurring', 'eventlist' ); ?>
			<input 
				type="radio" 
				class="option_calendar"
				id="option_calendar_auto"
				name="<?php echo esc_attr( $_prefix.'option_calendar' ); ?>" 
				value="auto" <?php checked( $option_calendar, 'auto' ); ?> />
				
			<span class="checkmark"></span>
		</label>
	</div>
	<input 
		type="hidden" 
		class="event_start_date_str" 
		name="<?php echo esc_attr( $_prefix.'start_date_str' ); ?>"
		value="<?php echo esc_attr( $start_date_str ); ?>" />
	<input 
		type="hidden" 
		class="event_end_date_str" 
		name="<?php echo esc_attr( $_prefix.'end_date_str' ); ?>"
		value="<?php echo esc_attr( $end_date_str ); ?>" />
	<div class="manual" style="<?php if ( $option_calendar == 'manual') echo esc_attr('display: block;'); ?>">
		<div class="list_calendar">
			<?php if ( $calendar ): ?>
				<?php foreach ( $calendar as $key => $value ): ?>
					<?php if ( $value['date'] != ''): ?> 
						<div class="item_calendar">
							<input 
								type="hidden" 
								class="calendar_id" 
								name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][calendar_id]' ); ?>"
								value="<?php echo esc_attr( isset( $value['calendar_id'] ) ? $value['calendar_id'] : '' ); ?>" />
							<div class="date">
								<label class="label"><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_date" 
									value="<?php echo esc_attr( $value['date'] ); ?>" 
									name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][date]' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									<?php if ( $option_calendar == 'manual' ) echo esc_attr( 'required' ); ?> />
							</div>
							<div class="end_date">
								<label class="label"><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_end_date" 
									value="<?php echo esc_attr( isset($value['end_date']) ? $value['end_date'] : '' ); ?>" 
									name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_date]' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									<?php if ( $option_calendar == 'manual' ) echo esc_attr( 'required' ); ?> />
							</div>
							<div class="start_time">
								<label class="label"><?php esc_html_e( 'From:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_start_time" 
									value="<?php echo esc_attr( $value['start_time'] ); ?>" 
									name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][start_time]' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									data-time="<?php echo esc_attr( $time ); ?>"
									<?php if ( $option_calendar == 'manual' ) echo esc_attr( 'required' ); ?> />
							</div>
							<div class="end_time">
								<label class="label"><?php esc_html_e( 'To:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_end_time" 
									value="<?php echo esc_attr( $value['end_time'] ); ?>" 
									name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_time]' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									data-time="<?php echo esc_attr( $time ); ?>"
									<?php if ( $option_calendar == 'manual' ) echo esc_attr( 'required' ); ?> />
							</div>
							<div class="book_before_minutes">
								<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
								<input 
									type="number" 
									name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][book_before_minutes]' ); ?>" 
									class="number_time_book_before"
									<?php $book_before_minutes = ! empty( $value['book_before_minutes'] ) ? $value['book_before_minutes'] : 0 ?> 
									value="<?php echo esc_attr( $book_before_minutes ); ?>" 
									placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" />
							</div>
							<button class="button remove_calendar">x</button>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<button class="button add_calendar">
			<?php esc_html_e( 'Add Calendar', 'eventlist' ); ?>
			<div class="submit-load-more sendmail">
				<div class="load-more">
					<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
				</div>
			</div>
		</button>
	</div>
	<div class="auto" style="<?php if ( $option_calendar == 'auto') echo esc_attr('display: block;'); ?>">
		<div class="time-range vendor_field" style="<?php if ( $schedules_time ) echo esc_attr( 'display: none;' ); ?>">
			<label>
				<?php _e('Events start from','eventlist'); ?>
			</label>
			<input 
				type="text" 
				class="calendar_recurrence_start_time" 
				name="<?php echo esc_attr( $_prefix.'calendar_recurrence_start_time' ); ?>" 
				value="<?php echo esc_attr( $calendar_recurrence_start_time ); ?>" 
				autocomplete="off" autocorrect="off" autocapitalize="none" 
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
				data-time="<?php echo esc_attr( $time ); ?>"
				<?php if ( ( $option_calendar == 'auto' ) && ! ( $schedules_time ) ) echo esc_attr( 'required' );?> />
			<?php _e('to','eventlist'); ?>
			<input 
				type="text" 
				class="calendar_recurrence_end_time" 
				name="<?php echo esc_attr( $_prefix.'calendar_recurrence_end_time' ); ?>" 
				value="<?php echo esc_attr( $calendar_recurrence_end_time ); ?>" 
				autocomplete="off" autocorrect="off" autocapitalize="none" 
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
				data-time="<?php echo esc_attr( $time ); ?>"
				<?php if ( ( $option_calendar == 'auto' ) && ! ( $schedules_time ) ) echo esc_attr('required'); ?> />
			<span class="calendar_recurrence_book_before">
				<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
				<input 
					type="number" 
					name="<?php echo esc_attr($_prefix.'calendar_recurrence_book_before' ); ?>" 
					class="calendar_recurrence_time_book_before"
					value="<?php echo esc_attr( $calendar_recurrence_book_before ); ?>" 
					placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" />
			</span>
		</div>
		<div class="event-form-when-wrap vendor_field">
			<label>
				<?php esc_html_e ( 'This event repeats', 'eventlist' ); ?> 
			</label>
			<select id="recurrence-frequency" name="<?php echo esc_attr( $_prefix.'recurrence_frequency' ); ?>">
				<option value="daily" <?php selected( $recurrence_frequency, 'daily' ); ?> ><?php esc_html_e( 'Daily', 'eventlist' ); ?></option>
				<option value="weekly" <?php selected( $recurrence_frequency, 'weekly' ); ?> ><?php esc_html_e( 'Weekly', 'eventlist' ); ?></option>
				<option value="monthly" <?php selected( $recurrence_frequency, 'monthly' ); ?> ><?php esc_html_e( 'Monthly', 'eventlist' ); ?></option>
				<option value="yearly" <?php selected( $recurrence_frequency, 'yearly' ); ?> ><?php esc_html_e( 'Yearly', 'eventlist' ); ?></option>
			</select>
			<?php esc_html_e ( 'every', 'eventlist' )?>
			<input 
				id="recurrence-interval" 
				name='<?php echo esc_attr( $_prefix.'recurrence_interval' ); ?>' 
				size='2' 
				value='<?php echo esc_attr( $recurrence_interval ); ?>' />
			<span class='interval-desc' id="interval-daily-singular"><?php esc_html_e ( 'day', 'eventlist' )?></span>
			<span class='interval-desc' id="interval-daily-plural"><?php esc_html_e ( 'days', 'eventlist' ) ?></span>
			<span class='interval-desc' id="interval-weekly-singular"><?php esc_html_e ( 'week on', 'eventlist' ); ?></span>
			<span class='interval-desc' id="interval-weekly-plural"><?php esc_html_e ( 'weeks on', 'eventlist' ); ?></span>
			<span class='interval-desc' id="interval-monthly-singular"><?php esc_html_e ( 'month on the', 'eventlist' )?></span>
			<span class='interval-desc' id="interval-monthly-plural"><?php esc_html_e ( 'months on the', 'eventlist' )?></span>
			<span class='interval-desc' id="interval-yearly-singular"><?php esc_html_e ( 'year', 'eventlist' )?></span> 
			<span class='interval-desc' id="interval-yearly-plural"><?php esc_html_e ( 'years', 'eventlist' ) ?></span>

			<!-- Weekly -->
			<div class="alternate-selector" id="weekly-selector">
				<div class="ts-weekly">
				<?php 
				$days_of_the_week = array(
					'1' => esc_html__('Mon', 'eventlist'),
					'2' => esc_html__('Tue', 'eventlist'),
					'3' => esc_html__('Wed', 'eventlist'),
					'4' => esc_html__('Thu', 'eventlist'),
					'5' => esc_html__('Fri', 'eventlist'),
					'6' => esc_html__('Sat', 'eventlist'),
					'0' => esc_html__('Sun', 'eventlist')
				);

				foreach ( $days_of_the_week as $key => $value ): ?>
					<div class="ts_recurrence_bydays">
						
						<label for="recurrence_bydays<?php echo $key; ?>" class="el_input_checkbox">
							<?php echo $value; ?>
							<input 
								type="checkbox"
								id="recurrence_bydays<?php echo $key; ?>"
								name="<?php echo esc_attr( $_prefix.'recurrence_bydays[]' ); ?>" 
								value="<?php echo esc_attr($key); ?>" <?php if ( in_array( $key, $recurrence_bydays ) ) echo esc_attr('checked'); ?>>
							<span class="checkmark"></span>
						</label>

						<div class="ts-list">
							<?php if ( isset( $ts_start[$key] ) && ! empty( $ts_start[$key] ) && is_array( $ts_start[$key] ) ):
								foreach ( $ts_start[$key] as $k_ts_start => $v_ts_start ):
									if ( isset( $ts_end[$key][$k_ts_start] ) && $ts_end[$key][$k_ts_start] ):
							?>
									<div class="ts-item" data-key="<?php echo esc_attr( $key ); ?>">
										<input 
											type="text" 
											class="calendar_recurrence_ts_start" 
											value="<?php echo esc_attr( $v_ts_start ); ?>" 
											name="<?php echo esc_attr( $_prefix.'ts_start['.$key.']['.$k_ts_start.']' ); ?>" 
											autocomplete="off" 
											autocorrect="off" 
											autocapitalize="none" 
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
											data-time="<?php echo esc_attr( $time ); ?>" />
										<input 
											type="text" 
											class="calendar_recurrence_ts_end" 
											value="<?php echo esc_attr( $ts_end[$key][$k_ts_start] ); ?>" 
											name="<?php echo esc_attr( $_prefix.'ts_end['.$key.']['.$k_ts_start.']' ); ?>" 
											autocomplete="off" 
											autocorrect="off" 
											autocapitalize="none" 
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
											data-time="<?php echo esc_attr( $time ); ?>" />
										<span class="close">x</span>
									</div>
						<?php endif; endforeach; endif; ?>
						</div>
						<button class="button add_time_slot" data-key="<?php echo esc_attr( $key ); ?>" data-placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>">
							<?php esc_html_e( 'Add Time Slot', 'eventlist' ); ?>
						</button>
					</div>
				<?php endforeach; ?>
				</div>
			</div>

			<!-- Monthly -->
			<p class="alternate-selector" id="monthly-selector" style="display:inline;">
				<select id="monthly-modifier" name="<?php echo esc_attr( $_prefix.'recurrence_byweekno' ); ?>">
					<?php 
					$arr_recurrence_byweekno = array(
						'1'  => esc_html__('first', 'eventlist'),
						'2'  => esc_html__('second', 'eventlist'),
						'3'  => esc_html__('third', 'eventlist'),
						'4'  => esc_html__('fourth', 'eventlist'),
						'5'  => esc_html__('fifth', 'eventlist'),
						'-1' => esc_html__('last', 'eventlist')
					);

					foreach ( $arr_recurrence_byweekno as $key => $value ) { ?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byweekno, $key ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>
				<select id="recurrence-weekday" name="<?php echo esc_attr( $_prefix.'recurrence_byday' ); ?>">
					<?php 
					foreach ( $days_of_the_week as $key => $value ) { ?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byday, $key ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>
				<?php esc_html_e('of each month', 'eventlist' ); ?>
			</p>
			<div class="event-form-recurrence-when">
				<p class="date-range vendor_field">
					<?php esc_html_e ( 'Recurrences span from ', 'eventlist' ); ?>	
					<input 
						type="text" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						class="calendar_start_date calendar_auto_start_date" 
						name="<?php echo esc_attr( $_prefix.'calendar_start_date' ); ?>" 
						value="<?php echo esc_attr( $calendar_start_date ); ?>" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						<?php if ( $option_calendar == 'auto' ) echo esc_attr( 'required' ); ?> />
					<?php esc_html_e('to', 'eventlist' ); ?>
					<input 
						type="text" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						class="calendar_end_date calendar_auto_end_date" 
						name="<?php echo esc_attr( $_prefix.'calendar_end_date' ); ?>" 
						value="<?php echo esc_attr( $calendar_end_date ); ?>" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						<?php if ( $option_calendar == 'auto' ) echo esc_attr('required'); ?> />
				</p>
			</div>
		</div>
		<div class="schedules_time">
			<label>
				<strong>
					<?php esc_html_e( 'Schedules Time', 'eventlist' ); ?>
				</strong>
			</label>
			<div class="wrap_schedules_time">
				<?php if ( $schedules_time ): ?>
					<?php foreach ( $schedules_time as $key => $value ): ?>
						<?php if ( $value['start_time'] != '' ): ?> 
							<div class="item_schedules_time" data-key='<?php echo esc_attr( $key ) ;?>'>
								<span>
									<?php esc_html_e( 'Form:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="start_time" 
										name="<?php echo esc_attr( $_prefix.'schedules_time['.$key.'][start_time]'  ); ?>"
										value="<?php echo esc_attr( isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>" 
										<?php if ( $option_calendar == 'auto' ) echo esc_attr('required'); ?> />
								</span>
								<span>
									<?php esc_html_e( 'To:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="end_time" 
										name="<?php echo esc_attr( $_prefix.'schedules_time['.$key.'][end_time]'  ); ?>"
										value="<?php echo esc_attr( isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>"
										<?php if ( $option_calendar == 'auto' ) echo esc_attr('required'); ?> />
								</span>
									<span class="schedules_time_book_before">
									<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
									<input 
										type="number" 
										name="<?php echo esc_attr(  $_prefix.'schedules_time['.$key.'][book_before]' ); ?>" 
										class="schedules_time_book"
										value="<?php echo esc_attr( isset( $value['book_before'] ) ? $value['book_before'] : '0' ); ?>"
										placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
										autocomplete="off" autocorrect="off" autocapitalize="none" />
								</span>
								<button class="button remove_schedules_time"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="label">
				<button class="button add_schedules_time ">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more sendmail">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
		<div class="disable_date vendor_field">
			<label>
				<strong>
					<?php esc_html_e( 'Disable date', 'eventlist' ); ?>
				</strong>
			</label>
			<div class="wrap_disable_date">
			<?php if ( $disable_date ): ?>
				<?php foreach ( $disable_date as $key => $value ): ?>
					<?php if ( $value['start_date'] != '' ): ?> 
						<div class="item_disable_date">
							<span>
								<?php esc_html_e( 'Form:', 'eventlist' ); ?>
								<input 
									type="text" 
									class="start_date" 
									name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][start_date]' ); ?>"
									value="<?php echo esc_attr( isset( $value['start_date'] ) ? $value['start_date'] : '' ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" />
							</span>
							<span>
								<?php esc_html_e( 'To:', 'eventlist' ); ?>
								<input 
									type="text" 
									class="end_date" 
									name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][end_date]' ); ?>"
									value="<?php echo esc_attr( isset( $value['end_date'] ) ? $value['end_date'] : '' ); ?>"
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" />
							</span>
                            <?php if ( $schedules_time ): ?>
							<span class="disable_time">
								<select name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][schedules_time]' ); ?>" class = "schedules_time">
									<option value=""><?php esc_html_e( 'Choose Schedules Time', 'eventlist' ); ?></option>
									<?php 
									foreach ( $schedules_time as $key => $value2 ):
										$disable_time = isset( $value['schedules_time'] ) ? $value['schedules_time'] : '';
									?>
										<option value="<?php echo $key; ?>" <?php selected( $key, $disable_time ); ?>>
											<?php echo $value2['start_time'].'-'.$value2['end_time'];; ?>
										</option>
									<?php endforeach; ?>
								</select>								
							</span>
							<?php endif; ?>
							<button class="button remove_disable_date"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			</div>
			<div class="label">
				<button class="button add_disable_date">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more sendmail">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
		<div class="disable_time_slot vendor_field">
			<label>
				<strong>
					<?php esc_html_e( 'Disable Time Slot', 'eventlist' ); ?>
				</strong>
			</label>
			<div class="wrap_disable_time_slot">
				<?php if ( $disable_time_slot ): ?>
					<?php foreach ( $disable_time_slot as $key => $value ): ?>
						<?php if ( $value['start_date'] != ''): ?> 
							<div class="item_disable_time_slot">
								<span>
									<?php esc_html_e( 'Form:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="start_date" 
										name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$key.'][start_date]' ); ?>"
										value="<?php echo esc_attr( isset( $value['start_date'] ) ? $value['start_date'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
										data-format="<?php echo esc_attr( $format ); ?>" 
										data-firstday="<?php echo esc_attr( $first_day ); ?>" />
								</span>
								<span>
									<input 
										type="text" 
										class="start_time" 
										name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$key.'][start_time]' ); ?>"
										value="<?php echo esc_attr( isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>" />
								</span>
								<span>
									<?php esc_html_e( 'To:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="end_date" 
										name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$key.'][end_date]' ); ?>"
										value="<?php echo esc_attr( isset( $value['end_date'] ) ? $value['end_date'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
										data-format="<?php echo esc_attr( $format ); ?>" 
										data-firstday="<?php echo esc_attr( $first_day ); ?>" />
								</span>
								<span>
									<input 
										type="text" 
										class="end_time" 
										name="<?php echo esc_attr( $_prefix.'disable_date_time_slot['.$key.'][end_time]' ); ?>"
										value="<?php echo esc_attr( isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>" />
								</span>
								<button class="button remove_disable_time_slot"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
							</div>
						<?php endif ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="label">
				<button class="button add_disable_time_slot">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more sendmail">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
	</div>
</div>