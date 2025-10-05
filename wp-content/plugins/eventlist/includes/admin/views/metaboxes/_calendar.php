<?php if ( ! defined( 'ABSPATH' ) ) exit();

$time 		= el_calendar_time_format();
$format 	= el_date_time_format_js();
$first_day 	= el_first_day_of_week();

$placeholder_dateformat = el_placeholder_dateformat();
$placeholder_timeformat = el_placeholder_timeformat();

$option_calendar 		= $this->get_mb_value( 'option_calendar' ) ? $this->get_mb_value( 'option_calendar' ) : 'manual';
$event_timezone 		= $this->get_mb_value( 'event_timezone' ) ? $this->get_mb_value( 'event_timezone' ) : 'UTC';
$recurrence_bydays 		= $this->get_mb_value( 'recurrence_bydays' ) ? $this->get_mb_value( 'recurrence_bydays' ) : array();
$recurrence_byweekno 	= $this->get_mb_value( 'recurrence_byweekno' ) ? $this->get_mb_value( 'recurrence_byweekno' ) : '1';
$recurrence_byday 		= $this->get_mb_value( 'recurrence_byday' ) ? $this->get_mb_value( 'recurrence_byday' ) : '0';
$recurrence_frequency 	= $this->get_mb_value( 'recurrence_frequency' ) ? $this->get_mb_value( 'recurrence_frequency' ) : 'daily';
$recurrence_days 		= $this->get_mb_value( 'recurrence_days' ) ? $this->get_mb_value( 'recurrence_days' ) : '0';
$ts_start 				= $this->get_mb_value( 'ts_start' ) ? $this->get_mb_value( 'ts_start' ) : [];
$ts_end 				= $this->get_mb_value( 'ts_end' ) ? $this->get_mb_value( 'ts_end' ) : [];

?>

<div class="calendar">
	<p><?php esc_html_e( 'Create the time of the event', 'eventlist' ); ?></p>
	<div class="option_calendar">
		<label><?php esc_html_e( 'Calendar Option:', 'eventlist' ); ?></label>
		<span>
			<input 
				type="radio" 
				name="<?php echo esc_attr( $this->get_mb_name( 'option_calendar' ) ); ?>" 
				value="manual" <?php checked( $option_calendar, 'manual' ); ?> />
				<?php esc_html_e( 'Manual', 'eventlist' ); ?>
		</span>
		<span>
			<input 
				type="radio" 
				name="<?php echo esc_attr( $this->get_mb_name( 'option_calendar' ) ); ?>" 
				value="auto" <?php checked( $option_calendar, 'auto' ); ?> />
				<?php esc_html_e( 'Recurring', 'eventlist' ); ?>
		</span>
	</div>
	<input 
		type="hidden" 
		id="event_start_date_str" 
		class="event_start_date_str" 
		value="<?php echo esc_attr( $this->get_mb_value( 'start_date_str' ) ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'start_date_str' ) ); ?>" />
	<input 
		type="hidden" 
		id="event_end_date_str" 
		class="event_end_date_str" 
		value="<?php echo esc_attr( $this->get_mb_value( 'end_date_str' ) ); ?>" 
		name="<?php echo esc_attr( $this->get_mb_name( 'end_date_str' ) ) ?>" />
	
	<!-- Manual Calendar -->
	<div class="manual" style="<?php if ( $option_calendar == 'manual') echo esc_attr('display: block;'); ?>">
		<div class="list_calendar">
			<?php if ( $this->get_mb_value( 'calendar' ) ): ?>
				<?php foreach ( $this->get_mb_value('calendar') as $key => $value ): ?>
					<?php if ( isset( $value['date'] ) && $value['date'] != '' ): ?> 
						<div class="item_calendar">
							<input 
								type="hidden" 
								class="calendar_id" 
								name="<?php echo esc_attr( $this->get_mb_name( 'calendar['.$key.'][calendar_id]' ) ); ?>"
								value="<?php echo esc_attr( isset( $value['calendar_id'] ) ? $value['calendar_id'] : '' ); ?>" />
							<div class="date">
								<label class="label"><?php esc_html_e( 'Start Date:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_date" 
									value="<?php echo esc_attr($value['date']); ?>" 
									name="<?php echo esc_attr( $this->get_mb_name('calendar['.$key.'][date]') ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									<?php if ( $option_calendar == 'manual' ) echo esc_attr('required'); ?> />
							</div>
							<div class="end_date">
								<label class="label"><?php esc_html_e( 'End Date:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_end_date" 
									value="<?php echo esc_attr( isset($value['end_date']) ? $value['end_date'] : ''); ?>" 
									name="<?php echo esc_attr( $this->get_mb_name('calendar['.$key.'][end_date]') ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
									data-format="<?php echo esc_attr( $format ); ?>" 
									data-firstday="<?php echo esc_attr( $first_day ); ?>" 
									<?php if ( $option_calendar == 'manual' ) echo esc_attr('required'); ?> />
							</div>
							<div class="start_time">
								<label class="label"><?php esc_html_e( 'From:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_start_time" 
									value="<?php echo esc_attr($value['start_time']); ?>" 
									name="<?php echo esc_attr( $this->get_mb_name('calendar['.$key.'][start_time]') ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									data-time="<?php echo esc_attr( $time ); ?>"
									<?php if( $option_calendar == 'manual' ) echo esc_attr('required'); ?> />
							</div>
							<div class="end_time">
								<label class="label"><?php esc_html_e( 'To:', 'eventlist' ); ?></label>
								<input 
									type="text" 
									class="calendar_end_time" 
									value="<?php echo esc_attr($value['end_time']); ?>" 
									name="<?php echo esc_attr( $this->get_mb_name('calendar['.$key.'][end_time]') ); ?>" 
									autocomplete="off" autocorrect="off" autocapitalize="none" 
									placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
									data-time="<?php echo esc_attr( $time ); ?>"
									<?php if ( $option_calendar == 'manual' ) echo esc_attr('required'); ?> />
							</div>
							<div class="book_before_minutes">
								<label class="label">
									<?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?>
								</label>
								<input 
									type="number" 
									name="<?php echo esc_attr( $this->get_mb_name( 'calendar['.$key.'][book_before_minutes]' ) ); ?>" 
									class="number_time_book_before"
									<?php $book_before_minutes = !empty($value['book_before_minutes']) ? $value['book_before_minutes'] : 0 ?> 
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
			<div class="submit-load-more">
				<div class="load-more">
					<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
				</div>
			</div>
		</button>
	</div>

	<!-- Auto Calendar -->
	<div class="auto" style="<?php if ( $option_calendar == 'auto' ) echo esc_attr( 'display: block;' ); ?>">
		<input 
			type="hidden" 
			name="<?php echo esc_attr( $this->get_mb_name( 'calendar_recurrence_id' ) ); ?>" 
			class="calendar_recurrence_id" 
			value="<?php echo esc_attr( $this->get_mb_value( 'calendar_recurrence_id' ) ); ?>" />
		<p class="time-range" style="<?php if ( $this->get_mb_value( 'schedules_time' ) ) echo esc_attr( 'display: none;' ); ?>">
			<?php esc_html_e('Events start from','eventlist'); ?>
			<input 
				type="text" 
				class="calendar_recurrence_start_time" 
				value="<?php echo esc_attr( $this->get_mb_value( 'calendar_recurrence_start_time' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_mb_name( 'calendar_recurrence_start_time' ) ); ?>" 
				autocomplete="off" autocorrect="off" autocapitalize="none" 
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
				data-time="<?php echo esc_attr( $time ); ?>"
				<?php if ( ( $option_calendar == 'auto' ) && ! ( $this->get_mb_value( 'schedules_time' ) ) ) echo esc_attr( 'required' ); ?> />
			<?php esc_html_e('to','eventlist'); ?>
			<input 
				type="text" 
				class="calendar_recurrence_end_time" 
				value="<?php echo esc_attr( $this->get_mb_value( 'calendar_recurrence_end_time' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_mb_name( 'calendar_recurrence_end_time' ) ); ?>" 
				autocomplete="off" autocorrect="off" autocapitalize="none" 
				placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
				data-time="<?php echo esc_attr( $time ); ?>"
				<?php if ( ( $option_calendar == 'auto' ) && ! ( $this->get_mb_value( 'schedules_time' ) ) ) echo esc_attr( 'required' ); ?> />
			<span class="calendar_recurrence_book_before">
				<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
				<input 
					type="number" 
					name="<?php echo esc_attr( $this->get_mb_name( 'calendar_recurrence_book_before' ) ); ?>" 
					class="calendar_recurrence_time_book_before"
					<?php $calendar_recurrence_book_before = ! empty( $this->get_mb_value( 'calendar_recurrence_book_before' ) ) ? $this->get_mb_value( 'calendar_recurrence_book_before' ) : 0; ?> 
					value="<?php echo esc_attr( $calendar_recurrence_book_before ); ?>" 
					placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
					autocomplete="off" autocorrect="off" autocapitalize="none" />
			</span>
		</p>
		<div class="event-form-when-wrap" >
			<?php esc_html_e ( 'This event repeats', 'eventlist' ); ?> 
			<select id="recurrence-frequency" name="<?php echo esc_attr( $this->get_mb_name( 'recurrence_frequency' ) ); ?>">
				<option value="daily" <?php selected( $recurrence_frequency, 'daily' ); ?> ><?php esc_html_e( 'Daily', 'eventlist' ); ?></option>
				<option value="weekly" <?php selected( $recurrence_frequency, 'weekly' ); ?> ><?php esc_html_e( 'Weekly', 'eventlist' ); ?></option>
				<option value="monthly" <?php selected( $recurrence_frequency, 'monthly' ); ?> ><?php esc_html_e( 'Monthly', 'eventlist' ); ?></option>
				<option value="yearly" <?php selected( $recurrence_frequency, 'yearly' ); ?> ><?php esc_html_e( 'Yearly', 'eventlist' ); ?></option>
			</select>
			<?php esc_html_e ( 'every', 'eventlist' ); ?>
			<input 
				id="recurrence-interval" 
				name='<?php echo esc_attr( $this->get_mb_name( 'recurrence_interval' ) ); ?>' 
				size='2' 
				value='<?php echo esc_attr( $this->get_mb_value( 'recurrence_interval' ) ); ?>' 
				autocomplete="off" autocorrect="off" autocapitalize="none" />
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
					'0' => esc_html__('Sun', 'eventlist'),
				);

				foreach ( $days_of_the_week as $key => $value ) { ?>
					<div class="ts_recurrence_bydays">
						<label>
							<input 
								type="checkbox" 
								name="<?php echo esc_attr( $this->get_mb_name( 'recurrence_bydays[]' ) ); ?>" 
								value="<?php echo esc_attr( $key ); ?>" <?php if ( in_array( $key, $recurrence_bydays ) ) echo esc_attr('checked'); ?>>
							<?php echo esc_html( $value ); ?>
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
											name="<?php echo esc_attr( $this->get_mb_name( 'ts_start['.$key.']['.$k_ts_start.']' ) ); ?>" 
											autocomplete="off" 
											autocorrect="off" 
											autocapitalize="none" 
											placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
											data-time="<?php echo esc_attr( $time ); ?>" />
										<input 
											type="text" 
											class="calendar_recurrence_ts_end" 
											value="<?php echo esc_attr( $ts_end[$key][$k_ts_start] ); ?>" 
											name="<?php echo esc_attr( $this->get_mb_name( 'ts_end['.$key.']['.$k_ts_start.']' ) ); ?>" 
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
				<?php } ?>
				</div>
			</div>

			<!-- Monthly -->
			<p class="alternate-selector" id="monthly-selector" style="display:inline;">
				<select id="monthly-modifier" name="<?php echo esc_attr( $this->get_mb_name( 'recurrence_byweekno' ) ); ?>">
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
						<option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byweekno, $key ); ?>><?php echo esc_html( $value ) ; ?></option>
					<?php } ?>
				</select>
				<select id="recurrence-weekday" name="<?php echo esc_attr( $this->get_mb_name( 'recurrence_byday' ) ); ?>">
					<?php 
					foreach ( $days_of_the_week as $key => $value ) { ?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected( $recurrence_byday, $key ); ?>><?php echo esc_html( $value ) ; ?></option>
					<?php } ?>
				</select>
				<?php esc_html_e('of each month', 'eventlist' ); ?>
			</p>
			<div class="event-form-recurrence-when">
				<p class="date-range">
					<?php esc_html_e ( 'Recurrences span from ', 'eventlist' ); ?>	
					<input 
						type="text" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						class="calendar_start_date" 
						name="<?php echo esc_attr( $this->get_mb_name( 'calendar_start_date' ) ); ?>" 
						value="<?php echo esc_attr( $this->get_mb_value( 'calendar_start_date' ) ); ?>" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						<?php if ( $option_calendar == 'auto' ) echo esc_attr( 'required' ); ?> />
					<?php esc_html_e('to', 'eventlist' ); ?>
					<input 
						type="text" 
						autocomplete="off" autocorrect="off" autocapitalize="none" 
						class="calendar_end_date" 
						name="<?php echo esc_attr( $this->get_mb_name( 'calendar_end_date' ) ); ?>" 
						value="<?php echo esc_attr( $this->get_mb_value( 'calendar_end_date' ) ); ?>" 
						placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
						data-format="<?php echo esc_attr( $format ); ?>" 
						data-firstday="<?php echo esc_attr( $first_day ); ?>" 
						<?php if($option_calendar == 'auto') echo esc_attr('required'); ?> />
				</p>
			</div>
		</div>
		<div class="schedules_time">
			<div class="wrap_schedules_time">
				<?php if ( $this->get_mb_value( 'schedules_time' ) ): ?>
					<?php foreach ( $this->get_mb_value('schedules_time') as $key => $value ): ?>
							<div class="item_schedules_time"  data-key= '<?php echo esc_attr( $key );?>'>
								<span>
									<?php esc_html_e( 'Form:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="start_time" 
										name="<?php echo esc_attr( $this->get_mb_name( 'schedules_time['.$key.'][start_time]' ) ); ?>"
										value="<?php echo esc_attr( isset( $value['start_time'] ) ? $value['start_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>" 
										<?php if ( $option_calendar == 'auto' ) echo esc_attr( 'required' ); ?> />
								</span>
								<span>
									<?php esc_html_e( 'To:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="end_time" 
										name="<?php echo esc_attr( $this->get_mb_name( 'schedules_time['.$key.'][end_time]' ) ); ?>"
										value="<?php echo esc_attr( isset( $value['end_time'] ) ? $value['end_time'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" 
										data-time="<?php echo esc_attr( $time ); ?>"
										<?php if ( $option_calendar == 'auto' ) echo esc_attr( 'required' ); ?> />
								</span>
								<span class="schedules_time_book_before">
									<label class="label"><?php esc_html_e( 'Booking before x minutes:', 'eventlist' ); ?></label>
									<input 
										type="number" 
										name="<?php echo esc_attr( $this->get_mb_name( 'schedules_time['.$key.'][book_before]' ) ); ?>" 
										class="schedules_time_book"
										value="<?php echo esc_attr( isset( $value['book_before'] ) ? $value['book_before'] : '0' ); ?>"
										placeholder="<?php echo esc_attr( '30', 'eventlist' ); ?>" 
										autocomplete="off" autocorrect="off" autocapitalize="none" />
								</span>
								<button class="button remove_schedules_time"><?php esc_html_e( 'x', 'eventlist' ) ?></button>
							</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="label">
				<label><?php esc_html_e( 'Schedules', 'eventlist' ); ?></label>
				<button class="button add_schedules_time">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
		<div class="disable_date">
			<div class="wrap_disable_date">
				<?php if ( $this->get_mb_value( 'disable_date' ) ): ?>
					<?php foreach ( $this->get_mb_value('disable_date') as $key => $value ): ?>
						<?php if ( $value['start_date'] != ''): ?> 
							<div class="item_disable_date">
								<span>
									<?php esc_html_e( 'Form:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="start_date" 
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date['.$key.'][start_date]' ) ); ?>"
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
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date['.$key.'][end_date]' ) ); ?>"
										value="<?php echo esc_attr( isset( $value['end_date'] ) ? $value['end_date'] : '' ); ?>"
										autocomplete="off" autocorrect="off" autocapitalize="none" 
										placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" 
										data-format="<?php echo esc_attr( $format ); ?>" 
										data-firstday="<?php echo esc_attr( $first_day ); ?>" />
								</span>
								<?php if ( $this->get_mb_value( 'schedules_time' ) ): ?>
									<span class="disable_time">
										<select name="<?php echo esc_attr( $this->get_mb_name( 'disable_date['.$key.'][schedules_time]' ) ); ?>" class="schedules_time">
											<option value=""><?php esc_html_e( 'Choose Schedules Time', 'eventlist' ); ?></option>
											<?php 
											foreach ( $this->get_mb_value('schedules_time') as $key => $value2 ): 
												$disable_time = isset( $value['schedules_time'] ) ? $value['schedules_time'] : '';
											?>
												<option value="<?php echo  esc_attr( $key ); ?>" <?php selected( $key,  $disable_time ); ?>>
												<?php echo esc_html( $value2['start_time'].'-'.$value2['end_time'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>								
									</span>
								<?php endif; ?>
								<button class="button remove_disable_date"><?php esc_html_e( 'x', 'eventlist' ); ?></button>
							</div>
						<?php endif ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="label">
				<label><?php esc_html_e( 'Disable date', 'eventlist' ); ?></label>
				<button class="button add_disable_date">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
		<div class="disable_time_slot">
			<div class="wrap_disable_time_slot">
				<?php if ( $this->get_mb_value( 'disable_date_time_slot' ) ): ?>
					<?php foreach ( $this->get_mb_value('disable_date_time_slot') as $key => $value ): ?>
						<?php if ( $value['start_date'] != ''): ?> 
							<div class="item_disable_time_slot">
								<span>
									<?php esc_html_e( 'Form:', 'eventlist' ); ?>
									<input 
										type="text" 
										class="start_date" 
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date_time_slot['.$key.'][start_date]' ) ); ?>"
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
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date_time_slot['.$key.'][start_time]' ) ); ?>"
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
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date_time_slot['.$key.'][end_date]' ) ); ?>"
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
										name="<?php echo esc_attr( $this->get_mb_name( 'disable_date_time_slot['.$key.'][end_time]' ) ); ?>"
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
				<label><?php esc_html_e( 'Disable Time Slot', 'eventlist' ); ?></label>
				<button class="button add_disable_time_slot">
					<?php esc_html_e( 'Add', 'eventlist' ); ?>
					<div class="submit-load-more">
						<div class="load-more">
							<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
						</div>
					</div>
				</button>
			</div>
		</div>
	</div>
</div>