<?php
if ( ! defined('ABSPATH') ) {
	exit();
}

$type 			= $args['type'];
$column 		= $args['column'];
$date_format 	= $args['date_format'];
$day_of_week_start 	= $args['day_of_week_start'];
$radius 		= $args['radius'];
$radius_unit 	= $args['radius_unit'];
$show_location 	= $args['show_location'];
$show_category 	= $args['show_category'];
$fields 		= $args['fields'];
$search_result 	= $args['search_result'];
$action_url 	= "";
$category_included = $args['category_included'];

$class_hide_all = ( $show_location != "yes" && $show_category != "yes" ) ? "hide_all" : '';
$class_hide_category = $show_category != "yes" ? "hidden" : '';
$class_hide_location = $show_location != "yes" ? "hide_location" : '';

if ( $search_result ) {
	$action_url = get_page_link( $search_result );
}

$map_lat 			= isset( $_GET['map_lat'] ) ? sanitize_text_field( $_GET['map_lat'] ) : '';
$map_lng 			= isset( $_GET['map_lng'] ) ? sanitize_text_field( $_GET['map_lng'] ) : '';
$map_address 		= isset( $_GET['map_address'] ) ? sanitize_text_field( $_GET['map_address'] ) : '';
$event_cat 			= isset( $_GET['cat'] ) ? sanitize_text_field( $_GET['cat'] ) : '';
$event_type 		= isset( $_GET['event_type'] ) ? sanitize_text_field( $_GET['event_type'] ) : '';
$time 				= isset( $_GET['time'] ) ? sanitize_text_field( $_GET['time'] ) : '';
$start_date 		= isset( $_GET["start_date"] ) ? sanitize_text_field( $_GET["start_date"] ) : '';
$end_date 			= isset( $_GET["end_date"] ) ? sanitize_text_field( $_GET["end_date"] ) : '';
$name_venue 		= isset( $_GET["name_venue"] ) ? sanitize_text_field( $_GET["name_venue"] ) : '';
$event_state 		= isset( $_GET["event_state"] ) ? sanitize_text_field( $_GET["event_state"] ) : '';

$restrictions 	= EL()->options->general->get('event_retrict') ? EL()->options->general->get('event_retrict') : array();
$event_bound 	= EL()->options->general->get('event_bound');
$event_lat 		= EL()->options->general->get('event_lat');
$event_lng 		= EL()->options->general->get('event_lng');
$event_radius 	= EL()->options->general->get('event_radius');


?>
<div class="elementor_search_form_2 <?php echo esc_attr( $type ); ?>">
	<div class="wrapper">
		<form method="GET" class="ova_form" action="<?php echo esc_url( $action_url ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none">
			<div class="ova_search_box">
				<div class="search_box_wrapper <?php echo esc_attr( $class_hide_all . ' ' . $class_hide_location ); ?>"
					data-bound="<?php echo esc_attr( $event_bound ); ?>"
					data-radius="<?php echo esc_attr( $event_radius ); ?>"
					data-lng="<?php echo esc_attr( $event_lng ); ?>"
					data-lat="<?php echo esc_attr( $event_lat ); ?>"
					data-retrict="<?php echo esc_attr( json_encode( $restrictions ) ); ?>" >
					<?php if ( $show_location == "yes" ): ?>
						<div class="ova_control second_font ova_control_address">
							<div class="icon_location">
								<i class="meupicon-pin" aria-hidden="true"></i>
							</div>
							<input type="hidden" name="map_address" value="" />

							<input type="hidden" name="map_lat" value="<?php echo esc_attr( $map_lat ); ?>">
							<input type="hidden" name="map_lng" value="<?php echo esc_attr( $map_lng ); ?>">
							<input type="hidden" name="radius_unit" value="<?php echo esc_attr( $radius_unit ); ?>">
							<input type="hidden" name="radius" value="<?php echo esc_attr( $radius ); ?>">
						</div>
					<?php endif; ?>
					<div class="ova_control second_font ova_control_category <?php echo esc_attr( $class_hide_category ); ?>" data-placeholder="<?php esc_attr_e( 'Select Category', 'eventlist' ); ?>">
						<?php if ( $show_category == "yes" ): ?>
							<div class="select_wrap">
								<i class="icon_folder-alt" aria-hidden="true"></i>
								<?php el_get_custom_taxonomy_dropdown_html('event_cat', 'cat', $event_cat, 'Select Category', 'ova_category', false, $category_included ); ?>
							</div>
						<?php endif; ?>
						<button type="submit" class="ova_submit ova_submit_desktop" aria-label="<?php esc_attr_e( 'Search Event', 'eventlist' ); ?>">
							<i class="meupicon-search" aria-hidden="true"></i>
						</button>
					</div>
				</div>
				<button type="submit" class="ova_submit ova_submit_mobile" aria-label="<?php esc_attr_e( 'Search Event', 'eventlist' ); ?>">
					<i class="meupicon-search" aria-hidden="true"></i>
				</button>
				</div>
			<ul class="ova_form_nav">
				<?php if ( $show_location == "yes" ): ?>
					<li class="item">
						<i class="meupicon-send" aria-hidden="true"></i>
						<a href="#" class="ova_form_link second_font near_me" title="<?php esc_attr_e( 'Near me', 'eventlist' ); ?>"><?php esc_html_e( 'Near me', 'eventlist' ); ?></a>
					</li>
				<?php endif; ?>
				<li class="item">
					<i class="meupicon-zoom-in" aria-hidden="true"></i>
					<a href="#" class="ova_form_link second_font advanced_search" title="<?php esc_attr_e( 'Advanced Search', 'eventlist' ); ?>"><?php esc_html_e( 'Advanced Search', 'eventlist' ); ?></a>
				</li>
			</ul>
			<div class="ova_filter">
				<h3 class="filter_title second_font"><?php esc_html_e( 'Filter:', 'eventlist' ); ?></h3>
				<ul class="filter_fields second_font <?php echo esc_attr( $column ); ?>">
					<?php if ( $fields ): ?>
						<?php foreach ( $fields as $item ): ?>
							<?php switch ( $item['field'] ) {
								case 'time':
								?>
								<li class="field-box">
									<select id="ova_event_time" name="time" class="ova_select2" aria-label="<?php esc_html_e('Select Time', 'eventlist'); ?>" data-placeholder="<?php esc_attr_e( 'All Time', 'eventlist' ); ?>">
										<option value="" <?php selected( $time, "" ); ?> ><?php esc_html_e('All Time', 'eventlist'); ?></option>
											<option value="today" <?php selected( $time, "today" ); ?> ><?php esc_html_e('Today', 'eventlist'); ?></option>
											<option value="tomorrow" <?php selected( $time, "tomorrow" ); ?> ><?php esc_html_e('Tomorrow', 'eventlist'); ?></option>
											<option value="this_week" <?php selected( $time, "this_week" ); ?> ><?php esc_html_e('This Week', 'eventlist'); ?></option>
											<option value="this_week_end" <?php selected( $time, "this_week_end" ); ?> ><?php esc_html_e('This Weekend', 'eventlist'); ?></option>
											<option value="next_week" <?php selected( $time, "next_week" ); ?> ><?php esc_html_e('Next Week', 'eventlist'); ?></option>
											<option value="next_month" <?php selected( $time, "next_month" ); ?> ><?php esc_html_e('Next Month', 'eventlist'); ?></option>
									</select>
								</li>
								<?php
									break;

								case 'start_date':
								?>
								<li class="field-box">
									<input type="text" class="ova_date_time" data-first-day="<?php echo esc_attr( $day_of_week_start ); ?>" placeholder="<?php esc_attr_e('Start date ...', 'eventlist'); ?>" aria-label="<?php esc_html_e('Start date', 'eventlist'); ?>" name="start_date" data-format="<?php echo esc_attr( $date_format ); ?>" value="<?php echo esc_attr($start_date); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
								</li>
								<?php
									break;
								
								case 'end_date':
								?>
								<li class="field-box">
									<input type="text" class="ova_date_time" data-first-day="<?php echo esc_attr( $day_of_week_start ); ?>" placeholder="<?php esc_attr_e('End date ...', 'eventlist'); ?>" aria-label="<?php esc_html_e('End date', 'eventlist'); ?>" name="end_date" data-format="<?php echo esc_attr( $date_format ); ?>" value="<?php echo esc_attr($end_date); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
								</li>
								<?php
									break;

								case 'name_venue':
								?>
								<li class="field-box">
									<input type="text" placeholder="<?php esc_html_e('Venue ...', 'eventlist'); ?>" aria-label="<?php esc_html_e('Venue', 'eventlist'); ?>" name="name_venue" value="<?php echo esc_attr( $name_venue ); ?>" autocomplete="off" autocorrect="off" autocapitalize="none" />
								</li>
								<?php
									break;

								case 'event_state':
								?>
								<li class="field-box">
									<?php el_get_state($event_state); ?>
								</li>
								<?php
									break;

								case 'event_type':
								?>
								<li class="field-box">
									<select id="ova_event_type" name="event_type" class="ova_select2" aria-label="<?php esc_attr_e( 'Select Type', 'eventlist' ); ?>" data-placeholder="<?php esc_attr_e( 'Select Type', 'eventlist' ); ?>">
										<option value="">
											<?php esc_html_e( 'Select Type', 'eventlist' ); ?>
										</option>
										<option value="online" <?php selected( $event_type, "online"); ?> >
											<?php esc_html_e( 'Online', 'eventlist' ); ?>
										</option>
										<option value="classic" <?php selected( $event_type, "classic"); ?> >
											<?php esc_html_e( 'Offline', 'eventlist' ); ?>
										</option>
									</select>
								</li>
								<?php
									break;

								default:
									// code...
									break;
							} ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php

					$list_taxonomy_custom = $args['list_taxonomy_custom'];

					if( $list_taxonomy_custom && is_array( $list_taxonomy_custom ) ) {

						foreach( $list_taxonomy_custom as $taxo ) {
							
							$taxonomy = get_taxonomy( $taxo['taxonomy_custom'] );
							if ( $taxonomy ) {

								$taxonomy_name 	= $taxonomy->label;
								$taxos 			= el_get_taxonomy($taxo['taxonomy_custom']);

								if( $taxo['taxonomy_custom'] ) { 
									$selected_tax = isset( $_GET[$taxo['taxonomy_custom']] ) ? sanitize_text_field( $_GET[$taxo['taxonomy_custom']] ) : '';
									?>
									<li class="field-box">
										<select name="<?php echo esc_attr( $taxo['taxonomy_custom'] ) ?>" class="ova_select2" aria-label="<?php echo sprintf( esc_attr__( 'Select %s', 'eventlist' ), esc_attr( $taxonomy_name ) ); ?>" data-placeholder="<?php echo sprintf( esc_attr__( 'Select %s', 'eventlist' ), esc_attr( $taxonomy_name ) ); ?>">
											<option value="">
												<?php echo sprintf( esc_html__( 'Select %s', 'eventlist' ), esc_attr( $taxonomy_name ) ); ?>
											</option>
											<?php foreach( $taxos as $tax ) { 
												?>
												<option value="<?php echo esc_attr( $tax->slug ); ?>" <?php selected( $selected_tax, $tax->slug ); ?>>
													<?php echo esc_html( $tax->name ); ?>
												</option>
											<?php } ?>
										</select>
									</li>
									<?php
								}
							}
						}
					}
					?>
				</ul>
			</div>
		</form>
	</div>
</div>