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

$recurrence_bydays 		= get_post_meta( $post_id, $_prefix.'recurrence_bydays', true) ? get_post_meta( $post_id, $_prefix.'recurrence_bydays', true) : array();
$recurrence_byweekno 	= get_post_meta( $post_id, $_prefix.'recurrence_byweekno', true) ? get_post_meta( $post_id, $_prefix.'recurrence_byweekno', true) : '1';
$recurrence_byday 		= get_post_meta( $post_id, $_prefix.'recurrence_byday', true) ? get_post_meta( $post_id, $_prefix.'recurrence_byday', true) : '0';
$recurrence_frequency 	= get_post_meta( $post_id, $_prefix.'recurrence_frequency', true) ? get_post_meta( $post_id, $_prefix.'recurrence_frequency', true) : 'daily';
$recurrence_interval 	= get_post_meta( $post_id, $_prefix.'recurrence_interval', true) ? get_post_meta( $post_id, $_prefix.'recurrence_interval', true) : '1';

$calendar_recurrence_start_time 	= get_post_meta( $post_id, $_prefix.'calendar_recurrence_start_time', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_start_time', true) : '';
$calendar_recurrence_end_time 		= get_post_meta( $post_id, $_prefix.'calendar_recurrence_end_time', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_end_time', true) : '';
$calendar_recurrence_book_before 	= get_post_meta( $post_id, $_prefix.'calendar_recurrence_book_before', true) ? get_post_meta( $post_id, $_prefix.'calendar_recurrence_book_before', true) : '0';
$calendar_start_date 				= get_post_meta( $post_id, $_prefix.'calendar_start_date', true) ? get_post_meta( $post_id, $_prefix.'calendar_start_date', true) : '';
$calendar_end_date 					= get_post_meta( $post_id, $_prefix.'calendar_end_date', true) ? get_post_meta( $post_id, $_prefix.'calendar_end_date', true) : '';

$ts_start 	= get_post_meta( $post_id, $_prefix.'ts_start', true) ? get_post_meta( $post_id, $_prefix.'ts_start', true) : [];
$ts_end 	= get_post_meta( $post_id, $_prefix.'ts_end', true) ? get_post_meta( $post_id, $_prefix.'ts_end', true) : [];

?>

<div class="calendar event_basic_block">
	<h4 class="heading_section"><?php esc_html_e( 'Dates et Horaires', 'eventlist' ); ?></h4>
	<p class="field_description">
		<?php esc_html_e( 'Définissez quand votre activité aura lieu.', 'eventlist' ); ?>
	</p>
	
	<div class="option_calendar vendor_field">
		<label class="label"><strong><?php esc_html_e( 'Type de calendrier', 'eventlist' ); ?></strong></label>
		<div class="calendar_type_options el_row">
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($option_calendar == 'manual') ? 'active' : ''; ?>" for="option_calendar_manual">
					<input type="radio" class="option_calendar" id="option_calendar_manual" name="<?php echo esc_attr( $_prefix.'option_calendar' ); ?>" value="manual" <?php checked( $option_calendar, 'manual' ); ?>>
					<span class="radio_content">
						<i class="icon_calendar"></i>
						<span class="radio_title"><?php esc_html_e( 'Date unique / Ponctuel', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Une ou plusieurs dates spécifiques', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
			<div class="el_col_6">
				<label class="el_card_radio <?php echo ($option_calendar == 'auto') ? 'active' : ''; ?>" for="option_calendar_auto">
					<input type="radio" class="option_calendar" id="option_calendar_auto" name="<?php echo esc_attr( $_prefix.'option_calendar' ); ?>" value="auto" <?php checked( $option_calendar, 'auto' ); ?> />
					<span class="radio_content">
						<i class="icon_refresh"></i>
						<span class="radio_title"><?php esc_html_e( 'Récurrent', 'eventlist' ); ?></span>
						<span class="radio_desc"><?php esc_html_e( 'Se répète (ex: tous les lundis)', 'eventlist' ); ?></span>
					</span>
				</label>
			</div>
		</div>
	</div>

	<!-- Manual Mode (Single/Multiple Dates) -->
	<div class="manual" style="<?php if ( $option_calendar == 'manual') echo esc_attr('display: block;'); ?>">
		<hr class="el_separator">
		<h5 class="heading_section"><?php esc_html_e( 'Vos dates', 'eventlist' ); ?></h5>

		<div class="list_calendar">
			<?php if ( $calendar ): ?>
				<?php foreach ( $calendar as $key => $value ): ?>
					<?php if ( $value['date'] != ''): ?> 
						<div class="item_calendar vendor_field" style="background: #F7F7F7; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
							<input type="hidden" class="calendar_id" name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][calendar_id]' ); ?>" value="<?php echo esc_attr( isset( $value['calendar_id'] ) ? $value['calendar_id'] : '' ); ?>" />
							
							<div class="el_row">
								<div class="el_col_4">
									<label><?php esc_html_e( 'Date de début', 'eventlist' ); ?></label>
									<div class="el_input_icon_wrapper">
										<i class="icon_calendar"></i>
										<input type="text" class="calendar_date" value="<?php echo esc_attr( $value['date'] ); ?>" name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][date]' ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
									</div>
								</div>
								<div class="el_col_2">
									<label><?php esc_html_e( 'Heure début', 'eventlist' ); ?></label>
									<input type="text" class="calendar_start_time" value="<?php echo esc_attr( $value['start_time'] ); ?>" name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][start_time]' ); ?>" placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" />
								</div>
								<div class="el_col_4">
									<label><?php esc_html_e( 'Date de fin', 'eventlist' ); ?></label>
									<div class="el_input_icon_wrapper">
										<i class="icon_calendar"></i>
										<input type="text" class="calendar_end_date" value="<?php echo esc_attr( isset($value['end_date']) ? $value['end_date'] : '' ); ?>" name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_date]' ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
									</div>
								</div>
								<div class="el_col_2">
									<label><?php esc_html_e( 'Heure fin', 'eventlist' ); ?></label>
									<input type="text" class="calendar_end_time" value="<?php echo esc_attr( $value['end_time'] ); ?>" name="<?php echo esc_attr( $_prefix.'calendar['.$key.'][end_time]' ); ?>" placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" />
								</div>
							</div>

							<div style="text-align: right; margin-top: 8px;">
								<button class="button remove_calendar" style="color: #FF5A5F; background: none; border: none; cursor: pointer; font-size: 13px; font-weight: 600;">
									<i class="icon_close"></i> <?php esc_html_e( 'Supprimer cette date', 'eventlist' ); ?>
								</button>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<button class="button add_calendar el_btn_add">
			<i class="icon_plus"></i> <?php esc_html_e( 'Ajouter une date', 'eventlist' ); ?>
		</button>
	</div>

	<!-- Recurring Mode -->
	<div class="auto" style="<?php if ( $option_calendar == 'auto') echo esc_attr('display: block;'); ?>">
		<hr class="el_separator">
		<h5 class="heading_section"><?php esc_html_e( 'Configuration de la récurrence', 'eventlist' ); ?></h5>
		
		<div class="vendor_field">
			<label><?php esc_html_e ( 'Période de récurrence', 'eventlist' ); ?></label>
			<div class="el_row">
				<div class="el_col_6">
					<div class="el_input_icon_wrapper">
						<i class="icon_calendar"></i>
						<input type="text" class="calendar_start_date calendar_auto_start_date" name="<?php echo esc_attr( $_prefix.'calendar_start_date' ); ?>" value="<?php echo esc_attr( $calendar_start_date ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
					</div>
				</div>
				<div class="el_col_6">
					<div class="el_input_icon_wrapper">
						<i class="icon_calendar"></i>
						<input type="text" class="calendar_end_date calendar_auto_end_date" name="<?php echo esc_attr( $_prefix.'calendar_end_date' ); ?>" value="<?php echo esc_attr( $calendar_end_date ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="vendor_field">
			<label><?php esc_html_e ( 'Répéter', 'eventlist' ); ?></label>
			<div class="recurrence_settings el_row" style="align-items: center;">
				<div class="el_col_4">
					<select id="recurrence-frequency" name="<?php echo esc_attr( $_prefix.'recurrence_frequency' ); ?>" class="selectpicker">
						<option value="daily" <?php selected( $recurrence_frequency, 'daily' ); ?> ><?php esc_html_e( 'Chaque jour', 'eventlist' ); ?></option>
						<option value="weekly" <?php selected( $recurrence_frequency, 'weekly' ); ?> ><?php esc_html_e( 'Chaque semaine', 'eventlist' ); ?></option>
						<option value="monthly" <?php selected( $recurrence_frequency, 'monthly' ); ?> ><?php esc_html_e( 'Chaque mois', 'eventlist' ); ?></option>
					</select>
				</div>
				<div class="el_col_2" style="text-align: center;">
					<span><?php esc_html_e ( 'tous les', 'eventlist' )?></span>
				</div>
				<div class="el_col_2">
					<input id="recurrence-interval" name='<?php echo esc_attr( $_prefix.'recurrence_interval' ); ?>' type="number" min="1" value='<?php echo esc_attr( $recurrence_interval ); ?>' />
				</div>
				<div class="el_col_4">
					<span class='interval-desc' id="interval-daily-singular"><?php esc_html_e ( 'jour', 'eventlist' )?></span>
					<span class='interval-desc' id="interval-daily-plural"><?php esc_html_e ( 'jours', 'eventlist' ) ?></span>
					<span class='interval-desc' id="interval-weekly-singular"><?php esc_html_e ( 'semaine le', 'eventlist' ); ?></span>
					<span class='interval-desc' id="interval-weekly-plural"><?php esc_html_e ( 'semaines le', 'eventlist' ); ?></span>
					<span class='interval-desc' id="interval-monthly-singular"><?php esc_html_e ( 'mois le', 'eventlist' )?></span>
					<span class='interval-desc' id="interval-monthly-plural"><?php esc_html_e ( 'mois le', 'eventlist' )?></span>
				</div>
			</div>
		</div>

		<!-- Weekly Days Selection -->
		<div class="alternate-selector vendor_field" id="weekly-selector">
			<label><?php esc_html_e( 'Jours de la semaine', 'eventlist' ); ?></label>
			<div class="ts-weekly el_row">
			<?php 
			$days_of_the_week = array(
				'1' => esc_html__('Lun', 'eventlist'),
				'2' => esc_html__('Mar', 'eventlist'),
				'3' => esc_html__('Mer', 'eventlist'),
				'4' => esc_html__('Jeu', 'eventlist'),
				'5' => esc_html__('Ven', 'eventlist'),
				'6' => esc_html__('Sam', 'eventlist'),
				'0' => esc_html__('Dim', 'eventlist')
			);

			foreach ( $days_of_the_week as $key => $value ): ?>
				<div class="ts_recurrence_bydays el_col_12" style="margin-bottom: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 16px;">
					<div style="display: flex; align-items: center; justify-content: space-between;">
						<label for="recurrence_bydays<?php echo $key; ?>" class="el_input_checkbox" style="font-weight: 600;">
							<input type="checkbox" id="recurrence_bydays<?php echo $key; ?>" name="<?php echo esc_attr( $_prefix.'recurrence_bydays[]' ); ?>" value="<?php echo esc_attr($key); ?>" <?php if ( in_array( $key, $recurrence_bydays ) ) echo esc_attr('checked'); ?>>
							<span class="checkmark"></span>
							<?php echo $value; ?>
						</label>
						
						<button class="button add_time_slot" data-key="<?php echo esc_attr( $key ); ?>" data-placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" style="font-size: 12px; padding: 4px 8px;">
							+ <?php esc_html_e( 'Ajouter un horaire', 'eventlist' ); ?>
						</button>
					</div>
					
					<!-- Time slots for specific day -->
					<div class="ts-list" style="margin-top: 8px; padding-left: 28px;">
						<?php if ( isset( $ts_start[$key] ) && ! empty( $ts_start[$key] ) && is_array( $ts_start[$key] ) ):
							foreach ( $ts_start[$key] as $k_ts_start => $v_ts_start ):
								if ( isset( $ts_end[$key][$k_ts_start] ) && $ts_end[$key][$k_ts_start] ): ?>
								<div class="ts-item" data-key="<?php echo esc_attr( $key ); ?>" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
									<input type="text" class="calendar_recurrence_ts_start" value="<?php echo esc_attr( $v_ts_start ); ?>" name="<?php echo esc_attr( $_prefix.'ts_start['.$key.']['.$k_ts_start.']' ); ?>" placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" style="width: 100px;" />
									<span>-</span>
									<input type="text" class="calendar_recurrence_ts_end" value="<?php echo esc_attr( $ts_end[$key][$k_ts_start] ); ?>" name="<?php echo esc_attr( $_prefix.'ts_end['.$key.']['.$k_ts_start.']' ); ?>" placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>" data-time="<?php echo esc_attr( $time ); ?>" style="width: 100px;" />
									<span class="close" style="cursor: pointer; color: red;">x</span>
								</div>
						<?php endif; endforeach; endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>

	</div>
	
	<!-- Excluded Dates (Common) -->
	<div class="disable_date vendor_field event_basic_block">
		<hr class="el_separator">
		<h5 class="heading_section"><?php esc_html_e( 'Dates exclues', 'eventlist' ); ?></h5>
		<div class="wrap_disable_date">
		<?php if ( $disable_date ): ?>
			<?php foreach ( $disable_date as $key => $value ): ?>
				<?php if ( $value['start_date'] != '' ): ?> 
					<div class="item_disable_date el_row" style="align-items: center; margin-bottom: 10px;">
						<div class="el_col_5">
							<input type="text" class="start_date" name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][start_date]' ); ?>" value="<?php echo esc_attr( isset( $value['start_date'] ) ? $value['start_date'] : '' ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
						</div>
						<div class="el_col_5">
							<input type="text" class="end_date" name="<?php echo esc_attr( $_prefix.'disable_date['.$key.'][end_date]' ); ?>" value="<?php echo esc_attr( isset( $value['end_date'] ) ? $value['end_date'] : '' ); ?>" placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>" data-format="<?php echo esc_attr( $format ); ?>" data-firstday="<?php echo esc_attr( $first_day ); ?>" />
						</div>
						<div class="el_col_2">
							<button class="button remove_disable_date" style="color: red; background: none; border: none;">x</button>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		</div>
		<button class="button add_disable_date el_btn_add">
			<i class="icon_plus"></i> <?php esc_html_e( 'Ajouter une exclusion', 'eventlist' ); ?>
		</button>
	</div>

</div>